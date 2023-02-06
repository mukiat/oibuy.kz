<?php

namespace App\Modules\Web\Controllers\User;

use App\Modules\Web\Controllers\InitController;
use App\Models\AdminUser;
use App\Models\ZcFocus;
use App\Models\ZcProject;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Article\ArticleCommonService;
use App\Services\Category\CategoryService;
use App\Services\Common\CommonService;
use App\Services\CrowdFund\CrowdFundService;
use App\Services\User\UserCommonService;

class UserCrowdfundController extends InitController
{
    protected $dscRepository;
    protected $userCommonService;
    protected $articleCommonService;
    protected $commonRepository;
    protected $commonService;
    protected $crowdFundService;
    protected $categoryService;

    public function __construct(
        DscRepository $dscRepository,
        UserCommonService $userCommonService,
        ArticleCommonService $articleCommonService,
        CommonRepository $commonRepository,
        CommonService $commonService,
        CrowdFundService $crowdFundService,
        CategoryService $categoryService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->userCommonService = $userCommonService;
        $this->articleCommonService = $articleCommonService;
        $this->commonRepository = $commonRepository;
        $this->commonService = $commonService;
        $this->crowdFundService = $crowdFundService;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        /* 跳转H5 start */
        $Loaction = dsc_url('/#/crowdfunding/order');
        $uachar = $this->dscRepository->getReturnMobile($Loaction);

        if ($uachar) {
            return $uachar;
        }
        /* 跳转H5 end */

        $this->dscRepository->helpersLang(['user']);

        $user_id = session('user_id', 0);

        $action = addslashes(trim(request()->input('act', 'default')));
        $action = $action ? $action : 'default';

        $not_login_arr = $this->userCommonService->notLoginArr('crowdfund');

        $ui_arr = $this->userCommonService->uiArr('crowdfund');

        /* 未登录处理 */
        $requireUser = $this->userCommonService->requireLogin(session('user_id'), $action, $not_login_arr, $ui_arr);
        $action = $requireUser['action'];
        $require_login = $requireUser['require_login'];

        if ($require_login == 1) {
            //未登录提交数据。非正常途径提交数据！
            return dsc_header('location:' . $this->dscRepository->dscUrl('user.php'));
        }

        $this->smarty->assign('use_value_card', config('shop.use_value_card')); //获取是否使用储值卡

        /* 区分登录注册底部样式 */
        $footer = $this->userCommonService->userFooter();
        if (in_array($action, $footer)) {
            $this->smarty->assign('footer', 1);
        }

        $is_apply = $this->userCommonService->merchantsIsApply($user_id);
        $this->smarty->assign('is_apply', $is_apply);

        $user_default_info = $this->userCommonService->getUserDefault($user_id);
        $this->smarty->assign('user_default_info', $user_default_info);

        $affiliate = config('shop.affiliate') ? unserialize(config('shop.affiliate')) : [];
        $this->smarty->assign('affiliate', $affiliate);
        // 分销验证
        $is_drp = file_exists(MOBILE_DRP) ? 1 : 0;
        $this->smarty->assign('is_dir', $is_drp);

        /* 如果是显示页面，对页面进行相应赋值 */
        if (in_array($action, $ui_arr)) {
            assign_template();
            $position = assign_ur_here(0, $GLOBALS['_LANG']['user_core']);
            $this->smarty->assign('page_title', $position['title']); // 页面标题
            $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
            $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版
            $this->smarty->assign('ur_here', $position['ur_here']);

            $this->smarty->assign('car_off', config('shop.anonymous_buy'));

            /* 是否显示积分兑换 */
            if (!empty(config('shop.points_rule')) && unserialize(config('shop.points_rule'))) {
                $this->smarty->assign('show_transform_points', 1);
            }

            $this->smarty->assign('helps', $this->articleCommonService->getShopHelp());        // 网店帮助
            $this->smarty->assign('data_dir', DATA_DIR);   // 数据目录
            $this->smarty->assign('action', $action);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);

            $info = $user_default_info;

            if ($user_id) {
                //验证邮箱
                if (isset($info['is_validated']) && !$info['is_validated'] && config('shop.user_login_register') == 1) {
                    $Location = url('/') . '/' . 'user.php?act=user_email_verify';
                    return dsc_header('location:' . $Location);
                }
            }

            $count = AdminUser::where('ru_id', session('user_id'))->count();
            if ($count) {
                $is_merchants = 1;
            } else {
                $is_merchants = 0;
            }

            $this->smarty->assign('is_merchants', $is_merchants);
            $this->smarty->assign('shop_reg_closed', config('shop.shop_reg_closed'));

            $this->smarty->assign('filename', 'user');
        } else {
            if (!in_array($action, $not_login_arr) || $user_id == 0) {
                $referer = '?back_act=' . urlencode(request()->server('REQUEST_URI'));
                $back_act = $this->dscRepository->dscUrl('user.php' . $referer);
                return dsc_header('location:' . $back_act);
            }
        }

        $supplierEnabled = CommonRepository::judgeSupplierEnabled();
        $wholesaleUse = $this->commonService->judgeWholesaleUse(session('user_id'));
        $wholesale_use = $supplierEnabled && $wholesaleUse ? 1 : 0;

        $this->smarty->assign('wholesale_use', $wholesale_use);
        $this->smarty->assign('shop_can_comment', config('shop.shop_can_comment'));

        /* ------------------------------------------------------ */
        //-- 众筹列表页
        /* ------------------------------------------------------ */
        if ($action == 'crowdfunding') {

            $time = TimeRepository::getGmTime();

            //取出当前用户关注的众筹项目列表;
            $zc_focus_list = $this->crowdFundService->getUserZcProjectList($user_id);

            //计算剩余天数、完成度;
            if ($zc_focus_list) {
                foreach ($zc_focus_list as $k => &$v) {
                    $v['surplus_time'] = ceil(($v['end_time'] - $time) / 86400);
                    $v['complete'] = round($v['join_money'] / $v['amount'] * 100, 2);
                }
            }

            //取出当前用户支持的众筹项目列表;
            $zc_support_list = $this->crowdFundService->getUserZcGoodsList($user_id);

            $zc_support_list_yes_pay = [];
            $zc_support_list_no_pay = [];

            //计算剩余天数、完成度;
            if ($zc_support_list) {
                foreach ($zc_support_list as $k => &$v) {
                    $v['surplus_time'] = ceil(($v['end_time'] - $time) / 86400);
                    $v['surplus_time'] = $v['surplus_time'] > 0 ? $v['surplus_time'] : 0;
                    $v['complete'] = round($v['join_money'] / $v['amount'] * 100, 2);

                    //分离已支付,未支付;
                    if (isset($v['pay_status']) && $v['pay_status'] == PS_PAYED) {
                        $zc_support_list_yes_pay[] = $v;
                    } else {
                        $zc_support_list_no_pay[] = $v;
                    }
                }
            }

            $this->smarty->assign('zc_focus_list', $zc_focus_list); //关注列表
            $this->smarty->assign('zc_support_list', $zc_support_list); //当前用户所有支持众筹
            $this->smarty->assign('zc_support_list_yes_pay', $zc_support_list_yes_pay); //已支付
            $this->smarty->assign('zc_support_list_no_pay', $zc_support_list_no_pay); //未支付;
            return $this->smarty->display('user_transaction.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 取消众筹关注
        /* ------------------------------------------------------ */
        elseif ($action == 'delete_zc_focus') {
            $pid = (int)request()->input('rec_id', 0);

            ZcFocus::where('pid', $pid)->delete();
            $res = ZcProject::where('id', $pid)->decrement('focus_num', 1);

            if ($res) {
                return dsc_header("location:user_crowdfund.php?act=crowdfunding");
            } else {
                return show_message($GLOBALS['_LANG']['process_false'], $GLOBALS['_LANG']['back_page_up'], 'user_crowdfund.php?act=crowdfunding');
            }
        }
    }
}
