<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Exchange;
use App\Models\Users;
use App\Models\UserBonus;
use App\Models\BonusType;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\CouponsRegion;
use App\Models\Goods;
use App\Models\OrderInfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\CouponsService;
use App\Services\Merchant\MerchantCommonService;

/**
 * 优惠券类型的处理
 */
class CouponsController extends InitController
{
    protected $couponsService;
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        CouponsService $couponsService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->couponsService = $couponsService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        } else {
            $_REQUEST['act'] = trim($_REQUEST['act']);
        }

        /* 初始化$exc对象 */
        $exc = new Exchange($this->dsc->table('bonus_type'), $this->db, 'type_id', 'type_name');
        $cou = new Exchange($this->dsc->table('coupons'), $this->db, 'cou_id', 'cou_name');
        $adminru = get_admin_ru_id();
        //ecmoban模板堂 --zhuo start
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        $this->smarty->assign('ru_id', $adminru['ru_id']); //bylu
        //ecmoban模板堂 --zhuo end

        $this->smarty->assign('menu_select', ['action' => '02_promotion', 'current' => '17_coupons']);

        /*------------------------------------------------------ */
        //-- 优惠券类型列表页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            admin_priv('coupons_manage');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cou_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['continus_add'], 'href' => 'coupons.php?act=add', 'class' => 'icon-plus']);
            $this->smarty->assign('full_page', 1);

            //取出当前商家发放的优惠券
            $list = get_coupons_type_info('1,2,3,4,5,6,7', $adminru['ru_id']);

            $this->smarty->assign('cou_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            //商家后台分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            $this->smarty->assign('url', $this->dsc->seller_url());

            return $this->smarty->display('coupons_type.dwt');
        }

        /*------------------------------------------------------ */
        //-- 翻页、排序
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'query') {
            //取出当前商家发放的优惠券
            if (isset($_POST['cou_type']) && $_POST['cou_type']) {
                //这里是类型搜索
                $list = get_coupons_type_info($_POST['cou_type'], $adminru['ru_id']);
            } else {
                $list = get_coupons_type_info('1,2,3,4,7', $adminru['ru_id']);
            }

            //查看优惠券详情列表
            $cou_id = isset($_REQUEST['coupons_one_list']) ? intval($_REQUEST['coupons_one_list']) : 0;
            if ($cou_id) {
                $list = $this->get_coupons_info2($cou_id);

                $this->smarty->assign('coupons_list', $list['item']);
                $this->smarty->assign('filter', $list['filter']);
                $this->smarty->assign('record_count', $list['record_count']);
                $this->smarty->assign('page_count', $list['page_count']);
                $this->smarty->assign('cou_id', $cou_id);

                //商家后台分页
                $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
                $page_count_arr = seller_page($list, $page);
                $this->smarty->assign('page_count_arr', $page_count_arr);

                $sort_flag = sort_flag($list['filter']);
                $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
                $this->smarty->assign('url', $this->dsc->seller_url());
                return make_json_result(
                    $this->smarty->fetch('coupons_list.dwt'),
                    '',
                    ['filter' => $list['filter'], 'page_count' => $list['page_count']]
                );
            }

            $this->smarty->assign('cou_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            //商家后台分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('coupons_type.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }


        /*------------------------------------------------------ */
        //-- 编辑优惠券类型名称
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit_type_name') {
            $check_auth = check_authz_json('coupons_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = json_str_iconv(trim($_POST['val']));

            $coupons = Coupons::where('cou_id', $id);
            $coupons = BaseRepository::getToArrayFirst($coupons);

            /* 检查优惠券类型名称是否重复 */
            $is_only = Coupons::where('cou_name', $val)->where('ru_id', $coupons['ru_id'])->where('cou_id', $id)->count();
            if ($is_only > 0) {
                return make_json_error($GLOBALS['_LANG']['title_exist']);
            } else {
                Coupons::where('cou_id', $id)->update(['cou_name' => $val]);
                return make_json_result(stripslashes($val));
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑优惠券金额
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'edit_type_money') {
            $check_auth = check_authz_json('coupons_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = floatval($_POST['val']);

            /* 检查优惠券类型名称是否重复 */
            if ($val <= 0) {
                return make_json_error($GLOBALS['_LANG']['type_money_error']);
            } else {
                $exc->edit("type_money='$val'", $id);

                return make_json_result(number_format($val, 2));
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑订单下限
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'edit_min_amount') {
            $check_auth = check_authz_json('coupons_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = floatval($_POST['val']);

            if ($val < 0) {
                return make_json_error($GLOBALS['_LANG']['min_amount_empty']);
            } else {
                $exc->edit("min_amount='$val'", $id);

                return make_json_result(number_format($val, 2));
            }
        }

        /*------------------------------------------------------ */
        //-- 删除优惠券类型
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('coupons_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            $cou_arr = Coupons::select('ru_id')->where('cou_id', $id);
            $cou_arr = BaseRepository::getToArrayFirst($cou_arr);
            if ($cou_arr['ru_id'] != $adminru['ru_id']) {
                $url = 'bonus.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            }

            $exc->drop($id);

            /* 更新商品信息 */
            Goods::where('bonus_type_id', $id)->update('bonus_type_id', 0);

            /* 删除用户的优惠券 */
            UserBonus::where('bonus_type_id', $id)->delete();

            $url = 'bonus.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 优惠券类型添加页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            admin_priv('coupons_manage');
            $cou_type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
            $this->smarty->assign('cou_type', $cou_type);
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_coupons']);
            $this->smarty->assign('action_link', ['href' => 'coupons.php?act=list', 'text' => $GLOBALS['_LANG']['coupons_list'], 'class' => 'icon-reply']);
            $this->smarty->assign('action', 'add');

            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            $next_month = local_strtotime('+1 months');
            $bonus_arr['send_start_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s');
            $bonus_arr['use_start_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s');
            $bonus_arr['send_end_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $next_month);
            $bonus_arr['use_end_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $next_month);

            $cou_arr = [
                'ru_id' => $adminru['ru_id']
            ];
            $this->smarty->assign('cou', $cou_arr);

            $rank_list = get_rank_list();
            $rank_list = $this->get_rank_arr($rank_list);

            $this->smarty->assign('rank_list', $rank_list);

            set_default_filter(0, 0, $adminru['ru_id']); //设置默认筛选

            if (GROUPBUY_LEADER == true) {
                $this->smarty->assign('is_cgroup', 1);
            } else {
                $this->smarty->assign('is_cgroup', 0);
            }

            $this->smarty->assign('is_status', 1);

            return $this->smarty->display('coupons_type_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 优惠券类型添加的处理
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'insert') {
            $cou_name = isset($_POST['cou_name']) ? addslashes($_POST['cou_name']) : '';

            $status = empty($_POST['status']) ? 1 : $_POST['status'];
            $status = (int)$status;

            $valid_type = empty($_POST['valid_type']) ? 1 : $_POST['valid_type'];
            $valid_type = (int)$valid_type;

            $valid_day_num = empty($_POST['valid_day_num']) ? 1 : $_POST['valid_day_num']; //领取后有效天数

            /* 获得日期信息 */
            $cou_start_time = TimeRepository::getLocalStrtoTime($_POST['cou_start_time']);//优惠券有效期起始时间;
            $cou_end_time = TimeRepository::getLocalStrtoTime($_POST['cou_end_time']);//优惠券有效期结束时间;

            $receive_start_time = TimeRepository::getLocalStrtoTime($_POST['receive_start_time']);//优惠券领取起始时间;
            $receive_start_time = empty($receive_start_time) ? $cou_start_time : $receive_start_time;
            $receive_end_time = TimeRepository::getLocalStrtoTime($_POST['receive_end_time']);//优惠券领取结束时间;
            $receive_end_time = ($receive_end_time > $cou_end_time && $cou_end_time > 0) || empty($receive_end_time) ? $cou_end_time : $receive_end_time;

            $cou_add_time = TimeRepository::getGmTime();//添加时间;

            $cou_user_num = empty($_POST['cou_user_num']) ? 1 : $_POST['cou_user_num'];

            $cou_goods = '';
            $spec_cat = '';
            $usableCouponGoods = empty($_POST['usableCouponGoods']) ? 1 : $_POST['usableCouponGoods'];
            if (!empty($_POST['cou_goods']) && $usableCouponGoods == 2) {
                $cou_goods = implode(',', array_unique($_POST['cou_goods']));
            } elseif (!empty($_POST['vc_cat']) && $usableCouponGoods == 3) {
                $spec_cat = implode(',', array_unique($_POST['vc_cat']));
            }

            $cou_ok_goods = '';
            $cou_ok_cat = '';
            $buyableCouponGoods = empty($_POST['buyableCouponGoods']) ? 1 : $_POST['buyableCouponGoods'];
            if (!empty($_POST['cou_ok_goods']) && $buyableCouponGoods == 2) {
                $cou_ok_goods = implode(',', array_unique($_POST['cou_ok_goods']));
            } elseif (!empty($_POST['vc_ok_cat']) && $buyableCouponGoods == 3) {
                $cou_ok_cat = implode(',', array_unique($_POST['vc_ok_cat']));
            } else {
                $cou_ok_goods = 0;
                $cou_ok_cat = '';
            }

            /*检查名称是否重复*/
            if ($_POST['cou_type'] == VOUCHER_SHOP_CONLLENT) {
                $count = Coupons::where('cou_type', VOUCHER_SHOP_CONLLENT)->where('ru_id', $adminru['ru_id'])->count();

                /*检查名称是否重复*/
                if ($count > 1) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['coupon_store_exist'], stripslashes($cou_name)), 1);
                }
            }

            /*检查名称是否重复*/
            $is_only = Coupons::where('cou_name', $cou_name)->where('ru_id', $adminru['ru_id'])->count();

            if ($is_only > 0) {
                return sys_msg(sprintf($GLOBALS['_LANG']['title_exist'], stripslashes($cou_name)), 1);
            }

            $cou_type = isset($_POST['cou_type']) ? intval($_POST['cou_type']) : 0;

            //注册送,全场送 默认所有会员等级可参加;
            if ($cou_type == 1 || $cou_type == 3) {
                $rank_list = get_rank_list();
                $cou_ok_user = '';
                foreach ($rank_list as $k => $v) {
                    $cou_ok_user .= $k . ',';
                }
                $cou_ok_user = substr($cou_ok_user, 0, -1);
            } else {
                if ($cou_type == VOUCHER_USER) {
                    $cou_ok_user = empty($_POST['cou_user_four']) ? '0' : implode(',', array_unique($_POST['cou_user_four']));
                } else {
                    $cou_ok_user = empty($_POST['cou_ok_user']) ? '0' : implode(',', array_unique($_POST['cou_ok_user']));
                }
            }

            /* 插入数据库。 */
            $data = [
                'cou_name' => $cou_name,
                'cou_total' => $_POST['cou_total'] ?? 0,
                'cou_man' => $_POST['cou_man'] ?? 0,
                'cou_money' => $_POST['cou_money'] ?? 0,
                'cou_user_num' => $cou_user_num,
                'cou_goods' => $cou_goods,
                'spec_cat' => $spec_cat,
                'cou_start_time' => $cou_start_time,
                'cou_end_time' => $cou_end_time,
                'receive_start_time' => $receive_start_time,
                'receive_end_time' => $receive_end_time,
                'cou_type' => $_POST['cou_type'],
                'cou_get_man' => $_POST['cou_get_man'] ?? 0,
                'cou_ok_user' => $cou_ok_user,
                'cou_ok_goods' => $cou_ok_goods,
                'cou_ok_cat' => $cou_ok_cat,
                'cou_intro' => $_POST['cou_intro'] ?? '',
                'cou_add_time' => $cou_add_time,
                'ru_id' => $adminru['ru_id'],
                'cou_title' => $_POST['cou_title'] ?? '',
                'status' => $status,
                'valid_day_num' => $valid_day_num,
                'valid_type' => $valid_type
            ];

            $cou_id = Coupons::insertGetId($data);
            //包邮券，不包邮地区
            if ($cou_type == 5) {
                $region_list = !empty($_REQUEST['free_value']) ? trim($_REQUEST['free_value']) : '';

                $data = [
                    'cou_id' => $cou_id,
                    'region_list' => $region_list
                ];
                CouponsRegion::insert($data);
            }
            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'coupons.php?act=list';

            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $cou_name . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 优惠券类型编辑页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit') {
            admin_priv('coupons_manage');

            /* 获取优惠券类型数据 bulu */
            $cou_id = !empty($_GET['cou_id']) ? intval($_GET['cou_id']) : 1;
            $cou_arr = Coupons::where('cou_id', $cou_id);
            $cou_arr = BaseRepository::getToArrayFirst($cou_arr);

            if ($cou_arr['ru_id'] != $adminru['ru_id']) {
                $Loaction = "coupons.php?act=list";
                return dsc_header("Location: $Loaction\n");
            }

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);
            $cou_arr['cou_start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $cou_arr['cou_start_time']);
            $cou_arr['cou_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $cou_arr['cou_end_time']);
            $cou_arr['receive_start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $cou_arr['receive_start_time']);
            $cou_arr['receive_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $cou_arr['receive_end_time']);

            //允许领取优惠券的会员级别; bylu
            $rank_list = get_rank_list();
            $rank_list = $this->get_rank_arr($rank_list, $cou_arr['cou_ok_user']);
            $this->smarty->assign('rank_list', $rank_list);

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cou_edit']);
            $this->smarty->assign('action_link', ['href' => 'coupons.php?act=list&' . list_link_postfix(), 'text' => $GLOBALS['_LANG']['cou_list'], 'class' => 'icon-reply']);
            $this->smarty->assign('form_act', 'update');

            //可使用优惠券的条件 start
            //指定分类
            if ($cou_arr['spec_cat']) {
                $cou_arr['cats'] = get_choose_cat($cou_arr['spec_cat']);
            } //指定商品
            elseif ($cou_arr['cou_goods']) {
                $cou_arr['goods'] = get_choose_goods($cou_arr['cou_goods']);
            }
            //可使用优惠券的条件 end

            //可获得优惠券的条件 start
            //指定分类
            if ($cou_arr['cou_ok_cat']) {
                $cou_arr['ok_cat'] = get_choose_cat($cou_arr['cou_ok_cat']);
            } //指定商品
            elseif ($cou_arr['cou_ok_goods']) {
                $cou_arr['ok_goods'] = get_choose_goods($cou_arr['cou_ok_goods']);
            }
            //可获得优惠券的条件 end

            if ($cou_arr['cou_type'] == VOUCHER_SHIPPING) {
                $region_arr = $this->couponsService->getCouponsRegionList($cou_id);
                $cou_arr['free_value'] = $region_arr['free_value'];
                $cou_arr['free_value_name'] = $region_arr['free_value_name'];
            }
            $this->smarty->assign('cou', $cou_arr);
            $this->smarty->assign('cou_act', 'edit');
            $this->smarty->assign('cou_type', $cou_arr['cou_type']);

            set_default_filter(0, 0, $adminru['ru_id']); //设置默认筛选

            if (GROUPBUY_LEADER == true) {
                $this->smarty->assign('is_cgroup', 1);
            } else {
                $this->smarty->assign('is_cgroup', 0);
            }

            return $this->smarty->display('coupons_type_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 优惠券类型编辑的处理
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'update') {
            $cou_name = isset($_POST['cou_name']) ? addslashes($_POST['cou_name']) : '';
            $type_name = isset($_POST['type_name']) ? addslashes($_POST['type_name']) : '';
            $cou_id = empty($_REQUEST['cou_id']) ? '' : intval($_REQUEST['cou_id']);

            /* 获得日期信息 */
            $cou_start_time = local_strtotime($_POST['cou_start_time']);//优惠券有效期起始时间;
            $cou_end_time = local_strtotime($_POST['cou_end_time']);//优惠券有效期结束时间;

            $receive_start_time = TimeRepository::getLocalStrtoTime($_POST['receive_start_time']);//优惠券领取起始时间;
            $receive_end_time = TimeRepository::getLocalStrtoTime($_POST['receive_end_time']);//优惠券领取结束时间;
            $receive_end_time = ($receive_end_time > $cou_end_time && $cou_end_time > 0) || empty($receive_end_time) ? $cou_end_time : $receive_end_time;

            $valid_day_num = empty($_POST['valid_day_num']) ? 1 : $_POST['valid_day_num']; //领取后有效天数

            $status = empty($_POST['status']) ? 1 : $_POST['status'];
            $status = (int)$status;

            $valid_type = empty($_POST['valid_type']) ? 1 : $_POST['valid_type'];
            $valid_type = (int)$valid_type;

            $cou_add_time = TimeRepository::getGmTime();//添加时间;

            $cou_user_num = empty($_POST['cou_user_num']) ? 1 : $_POST['cou_user_num'];

            $cou_goods = '';
            $spec_cat = '';
            $usableCouponGoods = empty($_POST['usableCouponGoods']) ? 1 : $_POST['usableCouponGoods'];
            if (!empty($_POST['cou_goods']) && $usableCouponGoods == 2) {
                $cou_goods = implode(',', array_unique($_POST['cou_goods']));
            } elseif (!empty($_POST['vc_cat']) && $usableCouponGoods == 3) {
                $spec_cat = implode(',', array_unique($_POST['vc_cat']));
            }

            $cou_ok_goods = '';
            $cou_ok_cat = '';
            $buyableCouponGoods = empty($_POST['buyableCouponGoods']) ? 1 : $_POST['buyableCouponGoods'];
            if (!empty($_POST['cou_ok_goods']) && $buyableCouponGoods == 2) {
                $cou_ok_goods = implode(',', array_unique($_POST['cou_ok_goods']));
            } elseif (!empty($_POST['vc_ok_cat']) && $buyableCouponGoods == 3) {
                $cou_ok_cat = implode(',', array_unique($_POST['vc_ok_cat']));
            } else {
                $cou_ok_goods = 0;
                $cou_ok_cat = '';
            }

            /*检查名称是否重复*/
            $is_only = Coupons::where('cou_name', $cou_name)->where('ru_id', $adminru['ru_id'])->where('cou_id', '<>', $cou_id)->count();
            if ($is_only > 0) {
                return sys_msg(sprintf($GLOBALS['_LANG']['title_exist'], stripslashes($cou_name)), 1);
            }

            $cou_type = isset($_POST['cou_type']) ? intval($_POST['cou_type']) : 0;

            //注册送,全场送 默认所有会员等级可参加;
            if ($cou_type == VOUCHER_LOGIN || $cou_type == VOUCHER_ALL) {
                $rank_list = get_rank_list();
                $cou_ok_user = '';
                foreach ($rank_list as $k => $v) {
                    $cou_ok_user .= $k . ',';
                }
                $cou_ok_user = substr($cou_ok_user, 0, -1);
            } else {
                if ($cou_type == VOUCHER_USER) {
                    $cou_ok_user = empty($_POST['cou_user_four']) ? '0' : implode(',', array_unique($_POST['cou_user_four']));
                } else {
                    $cou_ok_user = empty($_POST['cou_ok_user']) ? '0' : implode(',', array_unique($_POST['cou_ok_user']));
                }
            }

            /* 更新数据 */
            $record = [
                'cou_name' => $cou_name,
                'cou_title' => $_POST['cou_title'] ?? '',
                'cou_total' => $_POST['cou_total'] ?? 0,
                'cou_man' => $_POST['cou_man'] ?? 0,
                'cou_money' => $_POST['cou_money'] ?? 0,
                'cou_user_num' => $cou_user_num,
                'cou_goods' => $cou_goods,
                'spec_cat' => $spec_cat,
                'cou_start_time' => $cou_start_time,
                'cou_end_time' => $cou_end_time,
                'receive_start_time' => $receive_start_time,
                'receive_end_time' => $receive_end_time,
                'cou_type' => $cou_type,
                'cou_get_man' => $_POST['cou_get_man'] ?? 0,
                'cou_ok_user' => $cou_ok_user,
                'cou_ok_goods' => $cou_ok_goods,
                'cou_ok_cat' => $cou_ok_cat,
                'cou_intro' => $_POST['cou_intro'] ?? '',
                'cou_add_time' => $cou_add_time,
                'status' => $status,
                'valid_day_num' => $valid_day_num,
                'valid_type' => $valid_type
            ];

            $record['review_status'] = 1;

            Coupons::where('cou_id', $cou_id)->update($record);
            //录入包邮券，不包邮地区
            if ($cou_type == VOUCHER_SHIPPING) {
                $region_list = !empty($_REQUEST['free_value']) ? trim($_REQUEST['free_value']) : '';

                $data = [
                    'cou_id' => $cou_id,
                    'region_list' => $region_list
                ];

                CouponsRegion::updateOrCreate(['cou_id' => $cou_id], $data);
            }
            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'coupons.php?act=list';

            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $type_name . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }
        /*------------------------------------------------------ */
        //-- 修改秒杀优惠券排序 bylu
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'change_order') {
            $cou_id = $_REQUEST['cou_id'];
            $cou_order = $_REQUEST['cou_order'];

            $update = Coupons::where('cou_id', $cou_id)->update(['cou_order' => $cou_order]);
            if ($update > 0) {
                echo $data = 'ok';
            }
        }

        /*------------------------------------------------------ */
        //-- 优惠券发送页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'send') {
            admin_priv('coupons_manage');

            /* 取得参数 */
            $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';


            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['send_bonus']);
            $this->smarty->assign('action_link', ['href' => 'bonus.php?act=list', 'text' => $GLOBALS['_LANG']['04_bonustype_list']]);

            if ($_REQUEST['send_by'] == SEND_BY_USER) {
                $this->smarty->assign('id', $id);
                $this->smarty->assign('ranklist', get_rank_list());

                return $this->smarty->display('bonus_by_user.dwt');
            } elseif ($_REQUEST['send_by'] == SEND_BY_GOODS) {
                /* 查询此优惠券类型信息 */
                $bonus_type = BonusType::select('type_id', 'type_name')->where('type_id', $_REQUEST['id']);
                $bonus_type = BaseRepository::getToArrayFirst($bonus_type);

                /* 查询优惠券类型的商品列表 */
                $goods_list = get_bonus_goods($_REQUEST['id']);

                /* 查询其他优惠券类型的商品 */
                $res = Goods::select('goods_id')->where('bonus_type_id', '>', 0)->where('bonus_type_id', '<>', $_REQUEST['id']);
                $res = BaseRepository::getToArrayGet($res);
                $other_goods_list = collect($res)->pluck('goods_id')->all();
                $this->smarty->assign('other_goods', join(',', $other_goods_list));

                /* 获取下拉列表 by wu start */
                $select_category_html = '';
                $select_category_html .= insert_select_category(0, 0, 0, 'cat_id', 1);
                $this->smarty->assign('select_category_html', $select_category_html);

                /* 模板赋值 */
                $this->smarty->assign('brand_list', get_brand_list());

                $this->smarty->assign('bonus_type', $bonus_type);
                $this->smarty->assign('goods_list', $goods_list);

                return $this->smarty->display('bonus_by_goods.dwt');
            } elseif ($_REQUEST['send_by'] == SEND_BY_PRINT) {
                $this->smarty->assign('type_list', get_bonus_type());

                return $this->smarty->display('bonus_by_print.dwt');
            }
        }

        /*------------------------------------------------------ */
        //-- 处理优惠券的发送页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'send_by_user') {
            $user_list = [];
            $start = empty($_REQUEST['start']) ? 0 : intval($_REQUEST['start']);
            $limit = empty($_REQUEST['limit']) ? 10 : intval($_REQUEST['limit']);
            $validated_email = empty($_REQUEST['validated_email']) ? 0 : intval($_REQUEST['validated_email']);
            $send_count = 0;

            if (isset($_REQUEST['send_rank'])) {
                /* 按会员等级来发放优惠券 */
                $rank_id = intval($_REQUEST['rank_id']);

                if ($rank_id > 0) {
                    /* 特殊会员组处理 */
                    $send_count = Users::where('user_rank', $rank_id)->count();
                    $res = Users::where('user_rank', $rank_id);

                    if ($validated_email) {
                        $res = $res->where('is_validated', 1);
                    }

                    $res = $res->select('user_id', 'email', 'user_name')
                        ->offset($start)
                        ->limit($limit);

                    $user_list = BaseRepository::getToArrayGet($res);
                    $count = count($user_list);
                }
            } elseif (isset($_REQUEST['send_user'])) {

                /* 按会员列表发放优惠券 */
                /* 如果是空数组，直接返回 */
                if (empty($_REQUEST['user'])) {
                    return sys_msg($GLOBALS['_LANG']['send_user_empty'], 1);
                }

                $user_array = (is_array($_REQUEST['user'])) ? $_REQUEST['user'] : explode(',', $_REQUEST['user']);
                $send_count = count($user_array);

                $id_array = array_slice($user_array, $start, $limit);

                /* 根据会员ID取得用户名和邮件地址 */
                $user_list = Users::select('user_id', 'email', 'user_name')->whereIn('user_id', $id_array);
                $user_list = BaseRepository::getToArrayGet($user_list);
                $count = count($user_list);
            }

            /* 发送优惠券 */
            $loop = 0;
            $bonus_type = bonus_type_info($_REQUEST['id']);

            $tpl = get_mail_template('send_bonus');
            $today = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime());

            foreach ($user_list as $key => $val) {
                /* 发送邮件通知 */
                $this->smarty->assign('user_name', $val['user_name']);
                $this->smarty->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
                $this->smarty->assign('send_date', $today);
                $this->smarty->assign('sent_date', $today);
                $this->smarty->assign('count', 1);
                $this->smarty->assign('money', $this->dscRepository->getPriceFormat($bonus_type['type_money']));

                $content = $this->smarty->fetch('str:' . $tpl['template_content']);

                if (add_to_maillist($val['user_name'], $val['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                    /* 向会员优惠券表录入数据 */
                    $data = [
                        'bonus_type_id' => $_REQUEST['id'],
                        'bonus_sn' => 0,
                        'user_id' => $val['user_id'],
                        'used_time' => 0,
                        'order_id' => 0,
                        'emailed' => BONUS_MAIL_SUCCEED,
                    ];
                } else {
                    /* 邮件发送失败，更新数据库 */
                    $data = [
                        'bonus_type_id' => $_REQUEST['id'],
                        'bonus_sn' => 0,
                        'user_id' => $val['user_id'],
                        'used_time' => 0,
                        'order_id' => 0,
                        'emailed' => BONUS_MAIL_FAIL,
                    ];
                }
                UserBonus::insert($data);

                if ($loop >= $limit) {
                    break;
                } else {
                    $loop++;
                }
            }

            //admin_log(addslashes($GLOBALS['_LANG']['send_bonus']), 'add', 'bonustype');
            if ($send_count > ($start + $limit)) {
                /*  */
                $href = "bonus.php?act=send_by_user&start=" . ($start + $limit) . "&limit=$limit&id=$_REQUEST[id]&";

                if (isset($_REQUEST['send_rank'])) {
                    $href .= "send_rank=1&rank_id=$rank_id";
                }

                if (isset($_REQUEST['send_user'])) {
                    $href .= "send_user=1&user=" . implode(',', $user_array);
                }

                $link[] = ['text' => $GLOBALS['_LANG']['send_continue'], 'href' => $href];
            }

            $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'bonus.php?act=list'];

            return sys_msg(sprintf($GLOBALS['_LANG']['sendbonus_count'], $count), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 发送邮件
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'send_mail') {
            /* 取得参数：优惠券id */
            $bonus_id = intval($_REQUEST['bonus_id']);
            if ($bonus_id <= 0) {
                return 'invalid params';
            }

            /* 取得优惠券信息 */
            load_helper('order');
            $bonus = bonus_info($bonus_id);
            if (empty($bonus)) {
                return sys_msg($GLOBALS['_LANG']['bonus_not_exist']);
            }

            /* 发邮件 */
            $count = send_bonus_mail($bonus['bonus_type_id'], [$bonus_id]);

            $link[0]['text'] = $GLOBALS['_LANG']['back_bonus_list'];
            $link[0]['href'] = 'bonus.php?act=bonus_list&bonus_type=' . $bonus['bonus_type_id'];

            return sys_msg(sprintf($GLOBALS['_LANG']['success_send_mail'], $count), 0, $link);
        }


        /*------------------------------------------------------ */
        //-- 搜索商品
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'get_goods_list') {
            $filters = dsc_decode($_GET['JSON']);

            $arr = get_goods_list($filters);
            $opt = [];

            foreach ($arr as $key => $val) {
                $opt[] = ['value' => $val['goods_id'],
                    'text' => $val['goods_name'],
                    'data' => $val['shop_price']];
            }

            return make_json_result($opt);
        }

        /*------------------------------------------------------ */
        //-- 添加发放优惠券的商品
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add_bonus_goods') {
            $check_auth = check_authz_json('coupons_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $add_ids = dsc_decode($_GET['add_ids'], true);
            $args = dsc_decode($_GET['JSON'], true);
            $type_id = $args[0];

            foreach ($add_ids as $key => $val) {
                Goods::where('goods_id', $val)->update(['bonus_type_id' => $type_id]);
            }

            /* 重新载入 */
            $arr = get_bonus_goods($type_id);
            $opt = [];

            foreach ($arr as $key => $val) {
                $opt[] = ['value' => $val['goods_id'],
                    'text' => $val['goods_name'],
                    'data' => ''];
            }

            return make_json_result($opt);
        }

        /*------------------------------------------------------ */
        //-- 删除发放优惠券的商品
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'drop_bonus_goods') {
            $check_auth = check_authz_json('coupons_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $drop_goods = dsc_decode($_GET['drop_ids'], true);
            $drop_goods_ids = BaseRepository::getExplode($drop_goods);
            $arguments = dsc_decode($_GET['JSON'], true);
            $type_id = $arguments[0];

            Goods::where('bonus_type_id', $type_id)->whereIn('goods_id', $drop_goods_ids)->update(['bonus_type_id' => 0]);

            /* 重新载入 */
            $arr = get_bonus_goods($type_id);
            $opt = [];

            foreach ($arr as $key => $val) {
                $opt[] = ['value' => $val['goods_id'],
                    'text' => $val['goods_name'],
                    'data' => ''];
            }

            return make_json_result($opt);
        }

        /*------------------------------------------------------ */
        //-- 搜索用户
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'search_users') {
            $keywords = json_str_iconv(trim($_GET['keywords']));

            $row = Users::select('user_id', 'user_name')->where('user_name', 'like', '%' . mysql_like_quote($keywords) . '%')->orWhere('user_id', 'like', '%' . mysql_like_quote($keywords) . '%');

            $row = BaseRepository::getToArrayFirst($row);

            return make_json_result($row);
        }

        /*------------------------------------------------------ */
        //-- 优惠券列表
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'coupons_list') {
            $cou_id = intval($_GET['cou_id']);

            $list = $this->get_coupons_info2($cou_id);
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['coupons_list']);
            $this->smarty->assign('action_link', ['href' => 'coupons.php?act=list', 'text' => $GLOBALS['_LANG']['coupons_list'], 'class' => 'icon-reply']);

            $this->smarty->assign('coupons_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            //商家后台分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('coupons_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 会员优惠券列表翻页、排序(用于ajax返回) bylu
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'user_query_coupons') {
            $cou_id = intval($_GET['cou_id']);

            $list = $this->get_coupons_info($cou_id);


            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['coupons_list']);
            $this->smarty->assign('action_link', ['href' => 'coupons.php?act=list', 'text' => $GLOBALS['_LANG']['coupons_list']]);

            $this->smarty->assign('coupons_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('coupons_list.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 删除优惠券
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove_coupons') {
            $cou_id = intval($_GET['cou_id']);
            if ($cou_id) {
                if (Coupons::where('cou_id', $cou_id)->delete()) {
                    return 'ok';
                }
            }

            $uc_id = intval($_GET['id']);
            if ($uc_id) {
                $cou_id = CouponsUser::where('is_delete', 0)->where('uc_id', $uc_id)->value('cou_id');
                $cou_id = $cou_id ?? 0;
                CouponsUser::where('is_delete', 0)->where('uc_id', $uc_id)->update(['is_delete' => 1]);
                $url = "coupons.php?act=user_query_coupons&cou_id={$cou_id}" . str_replace('act=remove_coupons', '', request()->server('QUERY_STRING'));

                return dsc_header("Location: $url\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 批量操作
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'batch') {
            /* 检查权限 */
            admin_priv('coupons_manage');

            /* 去掉参数：优惠券类型 */
            $bonus_type_id = intval($_REQUEST['bonus_type']);

            /* 取得选中的优惠券id */
            if (isset($_POST['checkboxes'])) {
                $bonus_id_list = BaseRepository::getExplode($_POST['checkboxes']);

                /* 删除优惠券 */
                if (isset($_POST['drop'])) {
                    UserBonus::whereIn('bonus_id', $bonus_id_list)->delete();

                    admin_log(count($bonus_id_list), 'remove', 'userbonus');

                    clear_cache_files();

                    $link[] = ['text' => $GLOBALS['_LANG']['back_bonus_list'],
                        'href' => 'bonus.php?act=bonus_list&bonus_type=' . $bonus_type_id];
                    return sys_msg(sprintf($GLOBALS['_LANG']['batch_drop_success'], count($bonus_id_list)), 0, $link);
                } /* 发邮件 */
                elseif (isset($_POST['mail'])) {
                    $count = send_bonus_mail($bonus_type_id, $bonus_id_list);
                    $link[] = ['text' => $GLOBALS['_LANG']['back_bonus_list'],
                        'href' => 'bonus.php?act=bonus_list&bonus_type=' . $bonus_type_id];
                    return sys_msg(sprintf($GLOBALS['_LANG']['success_send_mail'], $count), 0, $link);
                }
            } else {
                return sys_msg($GLOBALS['_LANG']['no_select_bonus'], 1);
            }
        }
    }

    /**
     * 获取优惠券列表
     * @access  public
     * @return void
     */
    private function get_coupons_list($ru_id = '')
    {
        /* 获得所有优惠券的发放数量 */
        $filter['record_count'] = Coupons::query()->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        $res = Coupons::query()->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);
        $arr = [];

        foreach ($res as $row) {
            $row['cou_type'] = $row['cou_type'] == VOUCHER_LOGIN ? $GLOBALS['_LANG']['coupons_type_01'] : ($row['cou_type'] == VOUCHER_SHOPING ? $GLOBALS['_LANG']['coupons_type_02'] : ($row['cou_type'] == VOUCHER_ALL ? $GLOBALS['_LANG']['coupons_type_03'] : ($row['cou_type'] == VOUCHER_USER ? $GLOBALS['_LANG']['coupons_type_04'] : '')));
            $row['user_name'] = $this->merchantCommonService->getShopName($row['ru_id'], 1);//优惠券所属商家;
            $row['cou_start_time'] = TimeRepository::getLocalDate('Y-m-d', $row['cou_start_time']);
            $row['cou_end_time'] = TimeRepository::getLocalDate('Y-m-d', $row['cou_end_time']);
            $row['cou_is_use'] = $row['cou_is_use'] == 0 ? $GLOBALS['_LANG']['no_use'] : '<span style=color:red;>' . $GLOBALS['_LANG']['already_used'] . '</span>';
            $row['cou_is_time'] = $row['cou_is_time'] == 0 ? $GLOBALS['_LANG']['no_overdue'] : '<span style=color:red;>' . $GLOBALS['_LANG']['no_overdue'] . '</span>';

            $arr[] = $row;
        }

        $arr = ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /***获取用户优惠券发放领取情况
     * @param $cou_id 优惠券ID
     * @return array
     */
    private function get_coupons_info($cou_id)
    {
        $filter['record_count'] = CouponsUser::where('is_delete', 0)->where('cou_id', $cou_id)->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        $row = CouponsUser::where('is_delete', 0)->where('cou_id', $cou_id)->orderBy('uc_id', 'ASC');
        $row = BaseRepository::getToArrayGet($row);

        foreach ($row as $key => $val) {
            //使用时间
            if ($val['is_use_time']) {
                $row[$key]['is_use_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $val['is_use_time']);
            } else {
                $row[$key]['is_use_time'] = '';
            }
            //订单号
            if ($val['order_id']) {
                $row[$key]['order_sn'] = $this->db->getOne("SELECT order_sn FROM" . $this->dsc->table('order_info') . " WHERE order_id=" . $val['order_id']);
            }

            //所属会员
            if ($val['user_id']) {
                $row[$key]['user_name'] = $this->db->getOne("SELECT user_name FROM" . $this->dsc->table('users') . " WHERE user_id=" . $val['user_id']);
            }
        }

        $arr = ['item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    private function get_coupons_info2($cou_id)
    {
        $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('coupons_user') . " WHERE cou_id ='" . $cou_id . "' ";

        $filter['record_count'] = $this->db->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        $sql = "SELECT * FROM" . $this->dsc->table('coupons_user') . "WHERE cou_id='" . $cou_id . "' ORDER BY uc_id ASC LIMIT {$filter['start']} , {$filter['page_size']}";
        $row = $this->db->getAll($sql);

        foreach ($row as $key => $val) {
            //使用时间
            if ($val['is_use_time']) {
                $row[$key]['is_use_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $val['is_use_time']);
            } else {
                $row[$key]['is_use_time'] = '';
            }
            //订单号
            if ($val['order_id']) {
                $row[$key]['order_sn'] = OrderInfo::where('order_id', $val['order_id'])->value('order_sn');
                $row[$key]['order_sn'] = $row[$key]['order_sn'] ?? '';
            }

            //所属会员
            if ($val['user_id']) {
                $row[$key]['user_name'] = Users::where('user_id', $val['user_id'])->value('user_name');
                $row[$key]['user_name'] = $row[$key]['user_name'] ?? '';
            }
        }

        $arr = ['item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /* 等级重组 */
    private function get_rank_arr($rank_list, $cou_ok_user = '')
    {
        $cou_ok_user = !empty($cou_ok_user) ? explode(",", $cou_ok_user) : [];

        $arr = [];
        if ($rank_list) {
            foreach ($rank_list as $key => $row) {
                $arr[$key]['rank_id'] = $key;
                $arr[$key]['rank_name'] = $row;

                if ($cou_ok_user && in_array($key, $cou_ok_user)) {
                    $arr[$key]['is_checked'] = 1;
                } else {
                    $arr[$key]['is_checked'] = 0;
                }
            }
        }

        return $arr;
    }
}
