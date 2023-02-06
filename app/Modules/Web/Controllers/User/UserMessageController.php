<?php

namespace App\Modules\Web\Controllers\User;

use App\Api\Foundation\Components\ApiResponse;
use App\Models\AdminUser;
use App\Models\Feedback;
use App\Modules\Web\Controllers\InitController;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Article\ArticleCommonService;
use App\Services\Category\CategoryService;
use App\Services\Comment\CommentService;
use App\Services\Common\CommonService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderService;
use App\Services\User\UserCommonService;

class UserMessageController extends InitController
{
    use ApiResponse;

    protected $dscRepository;
    protected $userCommonService;
    protected $articleCommonService;
    protected $commonService;
    protected $orderService;
    protected $merchantCommonService;
    protected $commentService;
    protected $categoryService;

    public function __construct(
        DscRepository $dscRepository,
        UserCommonService $userCommonService,
        ArticleCommonService $articleCommonService,
        CommonService $commonService,
        OrderService $orderService,
        MerchantCommonService $merchantCommonService,
        CommentService $commentService,
        CategoryService $categoryService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->userCommonService = $userCommonService;
        $this->articleCommonService = $articleCommonService;
        $this->commonService = $commonService;
        $this->orderService = $orderService;
        $this->merchantCommonService = $merchantCommonService;
        $this->commentService = $commentService;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $this->dscRepository->helpersLang(['user']);

        $user_id = session('user_id', 0);

        $action = addslashes(trim(request()->input('act', 'default')));
        $action = $action ? $action : 'default';

        $not_login_arr = $this->userCommonService->notLoginArr('message');

        $ui_arr = $this->userCommonService->uiArr('message');

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

            if ($user_id) {
                //验证邮箱
                if (isset($user_default_info['is_validated']) && !$user_default_info['is_validated'] && config('shop.user_login_register') == 1) {
                    $Location = url('/') . '/' . 'user.php?act=user_email_verify';
                    return dsc_header('location:' . $Location);
                }
            }

            $count = AdminUser::where('ru_id', $user_id)->count();
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
        $wholesaleUse = $this->commonService->judgeWholesaleUse($user_id);
        $wholesale_use = $supplierEnabled && $wholesaleUse ? 1 : 0;

        $this->smarty->assign('wholesale_use', $wholesale_use);
        $this->smarty->assign('shop_can_comment', config('shop.shop_can_comment'));

        /* ------------------------------------------------------ */
        //-- 显示留言列表
        /* ------------------------------------------------------ */
        if ($action == 'message_list') {
            // 是否启用留言板功能
            if (config('shop.message_board') == 0) {
                return redirect()->route('user');
            }

            /* 跳转H5 start */
            $Loaction = dsc_url('/#/user/message');
            $uachar = $this->dscRepository->getReturnMobile($Loaction);

            if ($uachar) {
                return $uachar;
            }
            /* 跳转H5 end */

            load_helper('clips');

            $this->smarty->assign('user_info', $user_default_info);
            $this->smarty->assign('upload_size_limit', upload_size_limit(1));

            $is_order = (int)request()->input('is_order', 0);
            $page = (int)request()->input('page', 1);
            $order_id = (int)request()->input('order_id', 0);

            $order_info = [];

            /* 获取用户留言的数量 */
            if ($is_order) {
                if ($order_id > 0) {
                    $where = [
                        'order_id' => $order_id
                    ];
                    $order_info = $this->orderService->getOrderInfo($where);

                    if (!empty($order_info)) {
                        if ($order_info['user_id'] != $user_id) {
                            return show_message(trans('user.unauthorized_access'), '', 'user.php', 'error');
                        }

                        $order_info['url'] = 'user_order.php?act=order_detail&order_id=' . $order_id;
                    }
                }

                $record_count = Feedback::where('parent_id', 0)->where('user_id', $user_id);

                if ($order_id > 0) {
                    $record_count = $record_count->where('order_id', $order_id);
                }

                $record_count = $record_count->whereHasIn('getOrder', function ($query) {
                    $query->where('main_count', 0);
                });
            } else {
                $record_count = Feedback::where('parent_id', 0)->where('user_id', $user_id)->where('order_id', 0);
            }

            $record_count = $record_count->count();

            /* 验证码相关设置 */
            if ((intval(config('shop.captcha')) & CAPTCHA_MESSAGE) && gd_version() > 0) {
                $this->smarty->assign('enabled_captcha', 1);
                $this->smarty->assign('rand', mt_rand());
            }

            //判断是否有订单留言
            $this->smarty->assign('is_have_order', $record_count);

            if ($is_order) {
                $act = ['act' => $action . '&is_order=1'];
            } else {
                $act = ['act' => $action];
            }

            if ($order_id != '') {
                $act['order_id'] = $order_id;
            }

            $pager = get_pager('user_message.php', $act, $record_count, $page, 5);
            $this->smarty->assign('is_order', $is_order);
            $message_list = get_message_list($user_id, $pager['size'], $pager['start'], $order_id, $is_order);
            $this->smarty->assign('message_list', $message_list);
            $this->smarty->assign('pager', $pager);
            $this->smarty->assign('order_info', $order_info);
            return $this->smarty->display('user_clips.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 我的评论列表
        /* ------------------------------------------------------ */
        elseif ($action == 'comment_list') {

            // 是否启用评论
            if (config('shop.shop_can_comment') == 0) {
                return redirect()->route('user');
            }

            /* 跳转H5 start */
            $Loaction = dsc_url('/#/user/comment');
            $uachar = $this->dscRepository->getReturnMobile($Loaction);

            if ($uachar) {
                return $uachar;
            }
            /* 跳转H5 end */

            //评论标识
            $sign = (int)request()->input('sign', 0); // 0：待评论 1：已评价/追评
            $page = (int)request()->input('page', 1);
            $size = 10;

            $record_count = $this->commentService->getUserOrderCommentCount($user_id, $sign);

            if ($sign > 0) {
                $action = $action . "&sign=" . $sign;
            }

            $pager = get_pager('user_message.php', ['act' => $action], $record_count, $page, $size);

            $comment_list = $this->commentService->getUserOrderCommentList($user_id, $sign, 0, $page, $size);

            //评价条数
            $signNum0 = $this->commentService->getUserOrderCommentCount($user_id, 0);
            $signNum1 = $this->commentService->getUserOrderCommentCount($user_id, 1);

            $this->smarty->assign('comment_list', $comment_list);
            $this->smarty->assign('pager', $pager);
            $this->smarty->assign('sign', $sign);
            $this->smarty->assign('signNum0', $signNum0);
            $this->smarty->assign('signNum1', $signNum1);
            $this->smarty->assign('sessid', SESS_ID);

            //剔除未保存晒单图
            $this->commentService->deleteCommentImgList($user_id);

            return $this->smarty->display('user_clips.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 订单列表 待评价
        /* ------------------------------------------------------ */
        elseif ($action == 'commented_view') {

            $order_id = (int)request()->input('order_id', 0);
            $sign = (int)request()->input('sign', 0);

            /* 验证码相关设置 */
            if ((intval(config('shop.captcha')) & CAPTCHA_COMMENT) && gd_version() > 0) {
                $this->smarty->assign('enabled_captcha', 1);
                $this->smarty->assign('rand', mt_rand());
            }

            //订单商品晒单列表
            $order_goods = $this->commentService->getUserOrderCommentList($user_id, $sign, $order_id);

            $this->smarty->assign('order_goods', $order_goods);
            $this->smarty->assign('order_id', $order_id);

            $this->smarty->assign('sessid', SESS_ID);
            $this->smarty->assign('sign', $sign);
            return $this->smarty->display('user_clips.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 删除留言
        /* ------------------------------------------------------ */
        elseif ($action == 'del_msg') {
            $id = (int)request()->input('id', 0);

            $is_order = (int)request()->input('is_order', 0);
            $order_id = (int)request()->input('order_id', 0);

            if ($id > 0) {
                $row = Feedback::where('msg_id', $id)->first();
                $row = $row ? $row->toArray() : [];

                if ($row && $row['user_id'] == $user_id) {
                    /* 验证通过，删除留言，回复，及相应文件 */
                    if ($row['message_img']) {
                        $filename = storage_public(DATA_DIR . '/feedbackimg/' . $row['message_img']);
                        dsc_unlink($filename);
                    }

                    Feedback::where('msg_id', $id)->orWhere('parent_id', $id)->delete();
                }
            }
            if ($is_order) {
                return dsc_header("Location: user_message.php?act=message_list&is_order=1&order_id=$order_id\n");
            } else {
                return dsc_header("Location: user_message.php?act=message_list&order_id=$order_id\n");
            }
        } elseif ($action === 'notification') {
            $this->smarty->assign('token', $this->JWTEncode(['user_id' => $user_id]));

            return $this->smarty->display('user_clips.dwt');
        }
    }
}
