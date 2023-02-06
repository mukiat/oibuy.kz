<?php

namespace App\Modules\Web\Controllers;

use App\Models\BonusType;
use App\Models\UserBonus;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\BonusService;
use App\Services\Article\ArticleCommonService;
use App\Services\Merchant\MerchantCommonService;
use Illuminate\Http\Request;

/**
 * 红包前台文件
 */
class BonusController extends InitController
{
    protected $bonusService;
    protected $dscRepository;
    protected $merchantCommonService;
    protected $articleCommonService;

    public function __construct(
        BonusService $bonusService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        ArticleCommonService $articleCommonService
    )
    {
        $this->bonusService = $bonusService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->articleCommonService = $articleCommonService;
    }

    public function index(Request $request)
    {
        load_helper('publicfunc');

        $type_id = $request->get('id', 0);

        /* 跳转H5 start */
        $loaction = dsc_url('/#/bonus?id=' . $type_id);
        $uachar = $this->dscRepository->getReturnMobile($loaction);
        if ($uachar) {
            return $uachar;
        }
        /* 跳转H5 end */

        $user_id = session('user_id', 0);
        $act = addslashes(trim(request()->input('act', '')));

        if (empty($user_id)) {
            //会员登录返回领取红包页面
            $back_act = urlencode('bonus.php?act=bonus_info&id=' . $type_id);
            $Location = $request->root() . '/user.php?back_act=' . $back_act;
            return dsc_header('location:' . $Location);
        }

        /* ------------------------------------------------------ */
        //-- 红包领取页
        /* ------------------------------------------------------ */
        if ($act == 'bonus_info') {

            /* 模板赋值 */
            assign_template();
            $position = assign_ur_here();
            $this->smarty->assign('page_title', $position['title']);    // 页面标题
            $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置

            $this->smarty->assign('feed_url', ($GLOBALS['_CFG']['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL
            $this->smarty->assign('helps', $this->articleCommonService->getShopHelp());       // 网店帮助

            /* 获取数据 */
            $bonus_info = $this->bonusService->getBonusInfo($type_id);

            $bonus_info['use_start_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $bonus_info['use_start_date']);
            $bonus_info['use_end_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $bonus_info['use_end_date']);
            $bonus_info['type_money_formatted'] = $this->dscRepository->getPriceFormat($bonus_info['type_money']);
            $bonus_info['min_goods_amount_formatted'] = $this->dscRepository->getPriceFormat($bonus_info['min_goods_amount']);
            $bonus_info['shop_name'] = $this->merchantCommonService->getShopName($bonus_info['user_id'], 1); //店铺名称
            $this->smarty->assign('bonus_info', $bonus_info);

            $time = TimeRepository::getGmTime();

            $receive = 0;
            if ($time > $bonus_info['send_start_date'] && $time < $bonus_info['send_end_date']) {
                $receive = 1;
            }

            $this->smarty->assign('receive', $receive);

            /* 是否领过 */
            $bonus_id = UserBonus::where('bonus_type_id', $type_id)->where('user_id', $user_id)->value('bonus_id');
            $this->smarty->assign('exist', $bonus_id);

            /* 剩余数量 */
            $left = UserBonus::select('bonus_id')->where('bonus_type_id', $type_id)->where('user_id', 0)->count();
            $this->smarty->assign('left', $left);

            /* 显示模板 */
            return $this->smarty->display('bonus.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 领取红包
        /* ------------------------------------------------------ */
        if ($act == 'get_bonus') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $type_id = intval(request()->input('type_id', 0));

            $bonus_info = BonusType::where('type_id', $type_id);
            $bonus_info = BaseRepository::getToArrayFirst($bonus_info);

            if (empty($bonus_info)) {
                $bonus_info = [
                    'date_type' => 0,
                    'valid_period' => 0,
                    'use_start_date' => '',
                    'use_end_date' => '',
                ];
            }

            if (empty(session('user_id'))) {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['please_login'];
            } else {
                $bonus_id = UserBonus::where('bonus_type_id', $type_id)->where('user_id', $user_id)->value('bonus_id');
                if (!empty($bonus_id)) {
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['already_got'];
                } else {
                    $bonus_id = UserBonus::where('bonus_type_id', $type_id)->where('user_id', 0)->value('bonus_id');
                    if (empty($bonus_id)) {
                        $result['error'] = 1;
                        $result['message'] = $GLOBALS['_LANG']['no_bonus'];
                    } else {
                        $data = [
                            'user_id' => session('user_id', 0),
                            'bind_time' => gmtime(),
                            'date_type' => $bonus_info['date_type'],
                        ];
                        if ($bonus_info['valid_period'] > 0) {
                            $data['start_time'] = $data['bind_time'];
                            $data['end_time'] = $data['bind_time'] + $bonus_info['valid_period'] * 3600 * 24;
                        } else {
                            $data['start_time'] = $bonus_info['use_start_date'];
                            $data['end_time'] = $bonus_info['use_end_date'];
                        }

                        UserBonus::where('bonus_id', $bonus_id)
                            ->update($data);
                        $result['message'] = $GLOBALS['_LANG']['get_success'];
                    }
                }
            }
            return response()->json($result);
        }
    }
}
