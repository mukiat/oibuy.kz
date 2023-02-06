<?php

namespace App\Modules\Admin\Controllers;

use App\Jobs\Export\ValueCardExport;
use App\Libraries\Exchange;
use App\Models\OrderInfo;
use App\Models\Users;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\ValueCardType;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\ValueCardManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderDataHandleService;
use App\Services\User\UserDataHandleService;
use App\Services\ValueCard\ValueCardDataHandleService;

/**
 * 储值卡的处理
 */
class ValueCardController extends InitController
{
    protected $merchantCommonService;
    protected $valueCardManageService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        ValueCardManageService $valueCardManageService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->valueCardManageService = $valueCardManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = e(request()->input('act', 'list'));

        /* 初始化$exc对象 */
        $exc = new Exchange($this->dsc->table('value_card_type'), $this->db, 'id', 'name');

        $adminru = get_admin_ru_id();

        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        /* ------------------------------------------------------ */
        //-- 储值卡类型列表页面
        /* ------------------------------------------------------ */
        if ($act == 'list') {
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['vc_type_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['vc_type_add'], 'href' => 'value_card.php?act=vc_type_add']);
            $this->smarty->assign('full_page', 1);

            $list = $this->vc_type_list();

            $this->smarty->assign('value_card_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('vc_type_list.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 翻页、排序
        /* ------------------------------------------------------ */
        if ($act == 'query') {
            $list = $this->vc_type_list();
            $this->smarty->assign('value_card_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('vc_type_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]);
        }

        /* ------------------------------------------------------ */
        //-- 查看储值卡列表页
        /* ------------------------------------------------------ */
        if ($act == 'vc_list') {
            $id = isset($_REQUEST['tid']) ? intval($_REQUEST['tid']) : 0;
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['export_vc_list'], 'href' => 'value_card.php?act=export_vc_list&id=' . $id]);
            $vc_list = $this->vc_list();
            $this->smarty->assign('value_card_list', $vc_list['item']);
            $this->smarty->assign('filter', $vc_list['filter']);
            $this->smarty->assign('record_count', $vc_list['record_count']);
            $this->smarty->assign('page_count', $vc_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['value_card_list']);
            $this->smarty->assign('current_url', urlencode(request()->getRequestUri()));

            return $this->smarty->display('value_card_view.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 翻页、排序
        /* ------------------------------------------------------ */
        if ($act == 'vc_query') {
            $vc_list = $this->vc_list();
            $this->smarty->assign('value_card_list', $vc_list['item']);
            $this->smarty->assign('filter', $vc_list['filter']);
            $this->smarty->assign('record_count', $vc_list['record_count']);
            $this->smarty->assign('page_count', $vc_list['page_count']);

            $sort_flag = sort_flag($vc_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('value_card_view.dwt'), '', ['filter' => $vc_list['filter'], 'page_count' => $vc_list['page_count']]);
        }

        /* ------------------------------------------------------ */
        //-- 查看储值卡列表页
        /* ------------------------------------------------------ */
        if ($act == 'vc_log_list') {

            $vc_log_list = $this->vc_use_log_list();

            $this->smarty->assign('log_list', $vc_log_list['item']);
            $this->smarty->assign('filter', $vc_log_list['filter']);
            $this->smarty->assign('record_count', $vc_log_list['record_count']);
            $this->smarty->assign('page_count', $vc_log_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['vc_log_list']);

            return $this->smarty->display('value_card_use_log.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 翻页、排序
        /* ------------------------------------------------------ */
        if ($act == 'vc_log_query') {

            $vc_log_list = $this->vc_use_log_list();

            $this->smarty->assign('log_list', $vc_log_list['item']);
            $this->smarty->assign('filter', $vc_log_list['filter']);
            $this->smarty->assign('record_count', $vc_log_list['record_count']);
            $this->smarty->assign('page_count', $vc_log_list['page_count']);

            $sort_flag = sort_flag($vc_log_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('value_card_use_log.dwt'), '', ['filter' => $vc_log_list['filter'], 'page_count' => $vc_log_list['page_count']]);
        }

        /* ------------------------------------------------------ */
        //-- 添加储值卡类型页面
        /* ------------------------------------------------------ */
        if ($act == 'vc_type_add' || $act == 'vc_type_edit') {
            $row = [];
            if ($act == 'vc_type_add') {
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['vc_type_add']);
                $this->smarty->assign('form_act', 'insert');
            } else {
                $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
                $sql = " SELECT * FROM " . $this->dsc->table('value_card_type') . " WHERE id = '$id' ";
                $row = $this->db->getRow($sql);
                $row['vc_dis'] = intval($row['vc_dis'] * 100);

                //指定分类
                if ($row['use_condition'] == 1) {
                    $row['cats'] = get_choose_cat($row['spec_cat']);
                } //指定商品
                elseif ($row['use_condition'] == 2) {
                    $row['goods'] = get_choose_goods($row['spec_goods']);
                }

                if ($row['use_merchants'] == 'all') {
                    $row['use_merchants'] = 0;
                } elseif ($row['use_merchants'] == 'self') {
                    $row['use_merchants'] = 1;
                } else {
                    $row['selected_merchants'] = $row['use_merchants'];
                    $row['use_merchants'] = 2;
                }

                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['vc_type_edit']);
                $this->smarty->assign('form_act', 'update');
            }

            $this->smarty->assign('vc', $row);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('action_link', ['href' => 'value_card.php?act=list', 'text' => $GLOBALS['_LANG']['vc_type_list']]);
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            set_default_filter(); //设置默认筛选

            return $this->smarty->display('vc_type_info.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 储值卡类型添加的处理
        /* ------------------------------------------------------ */
        if ($act == 'insert' || $act == 'update') {
            /* 过滤数据 */

            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $vc_desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
            $vc_limit = isset($_POST['limit']) ? intval($_POST['limit']) : 1;
            $vc_value = isset($_POST['value']) ? intval($_POST['value']) : 0;
            $vc_dis = !empty($_POST['is_vc_dis']) ? (!empty($_POST['vc_dis']) ? intval($_POST['vc_dis']) / 100 : 1) : 1;
            $vc_indate = !empty($_POST['indate']) ? intval($_POST['indate']) : 36;
            $use_condition = isset($_POST['use_condition']) ? intval($_POST['use_condition']) : 0;
            $use_merchants = isset($_POST['use_merchants']) ? intval($_POST['use_merchants']) : '';
            $spec_cat = !empty($_POST['vc_cat']) && $use_condition == 1 ? implode(',', array_unique($_POST['vc_cat'])) : '';
            $spec_goods = !empty($_POST['vc_goods']) && $use_condition == 2 ? implode(',', array_unique($_POST['vc_goods'])) : '';
            $prefix = isset($_POST['prefix']) ? trim($_POST['prefix']) : 0;
            $is_rec = isset($_POST['is_rec']) ? intval($_POST['is_rec']) : 0;
            $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            if ($use_merchants == 0) {
                $use_merchants = 'all';
            } elseif ($use_merchants == 1) {
                $use_merchants = 'self';
            } elseif ($use_merchants == 2) {
                $use_merchants = isset($_POST['selected_merchants']) ? trim($_POST['selected_merchants']) : '';
            }

            if ($id > 0) {
                $sql = " UPDATE " . $this->dsc->table('value_card_type') . " SET " .
                    " name = '$name', " .
                    " vc_desc = '$vc_desc', " .
                    " vc_limit = '$vc_limit', " .
                    " vc_value = '$vc_value', " .
                    " vc_prefix = '$prefix', " .
                    " vc_dis = '$vc_dis', " .
                    " vc_indate = '$vc_indate', " .
                    " use_condition = '$use_condition', " .
                    " use_merchants = '$use_merchants', " .
                    " spec_goods = '$spec_goods', " .
                    " spec_cat = '$spec_cat', " .
                    // " begin_time = '$begin_time', ".
                    // " end_time = '$end_time', ".
                    " is_rec = '$is_rec' " .
                    " WHERE id = '$id' ";

                $this->db->query($sql);
                $notice = lang('admin/value_card.edit_type_success');
            } else {
                $time = gmtime();

                $count = ValueCardType::where('name', $name)->where('add_time', $time)->count();

                if ($count == 0) {
                    $value_card = [
                        'name' => $name,
                        'vc_desc' => $vc_desc,
                        'vc_limit' => $vc_limit,
                        'vc_value' => $vc_value,
                        'vc_prefix' => $prefix,
                        'vc_dis' => $vc_dis,
                        'vc_indate' => $vc_indate,
                        'use_condition' => $use_condition,
                        'use_merchants' => $use_merchants,
                        'spec_goods' => $spec_goods,
                        'spec_cat' => $spec_cat,
                        // 'begin_time'	=> $begin_time,
                        // 'end_time'		=> $end_time,
                        'is_rec' => $is_rec
                    ];

                    $this->valueCardManageService->ValueCardTypeInsert($value_card, $time);
                }

                $notice = lang('admin/value_card.add_type_success');
            }
            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'value_card.php?act=list';

            return sys_msg($notice, 0, $link);
        }

        /* ------------------------------------------------------ */
        //-- 删除储值卡类型
        /* ------------------------------------------------------ */
        if ($act == 'remove') {
            $id = intval($_GET['id']);

            //检查是否存在已绑定用户的储值卡 如果有则无法删除
            $sql = " SELECT COUNT(*) FROM " . $this->dsc->table('value_card') . " WHERE tid = '$id' AND user_id > 0 ";
            $row = $this->db->getOne($sql);
            if ($row > 0) {
                return make_json_error($GLOBALS['_LANG']['notice_remove_type_error']);
            } else {
                $exc->drop($id);
                $sql = " DELETE FROM " . $this->dsc->table('value_card') . " WHERE tid = '$id' ";
                $this->db->query($sql);

                $url = 'value_card.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 批量操作
        /*------------------------------------------------------ */
        elseif ($act == 'batch') {
            /* 取得要操作的记录编号 */
            if (empty($_POST['checkboxes'])) {
                return sys_msg($GLOBALS['_LANG']['no_record_selected']);
            } else {
                $ids = $_POST['checkboxes'];
                //检查是否存在已绑定用户的储值卡 如果有则无法删除
                $sql = " SELECT COUNT(*) FROM " . $this->dsc->table('value_card') . " WHERE tid" . db_create_in($ids) . " AND user_id > 0 ";
                $row = $this->db->getOne($sql);
                if (isset($_POST['drop'])) {
                    if ($row > 0) {
                        $links[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'value_card.php?act=list&' . list_link_postfix()];
                        return sys_msg($GLOBALS['_LANG']['notice_remove_type_error'], 1, $links);
                    } else {
                        /* 删除记录 */
                        $sql = "DELETE FROM " . $this->dsc->table('value_card_type') .
                            " WHERE id " . db_create_in($ids);
                        $res = $this->db->query($sql);
                        if ($res) {
                            $sql = " DELETE FROM " . $this->dsc->table('value_card') . " WHERE tid " . db_create_in($ids);
                            $this->db->query($sql);
                        }

                        /* 记日志 */
                        admin_log('', 'batch_remove', 'value_card');

                        /* 清除缓存 */
                        clear_cache_files();

                        $links[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'value_card.php?act=list&' . list_link_postfix()];
                        return sys_msg($GLOBALS['_LANG']['batch_drop_ok'], 0, $links);
                    }
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 删除储值卡
        /* ------------------------------------------------------ */
        if ($act == 'remove_vc') {
            $id = intval($_GET['id']);

            //检查是否存在已绑定用户的储值卡 如果有则无法删除
            $sql = " SELECT user_id FROM " . $this->dsc->table('value_card') . " WHERE vid = '$id' ";
            $row = $this->db->getOne($sql);
            if ($row > 0) {
                return make_json_error($GLOBALS['_LANG']['notice_remove_vc_error']);
            } else {
                $sql = " DELETE FROM " . $this->dsc->table('value_card') . " WHERE vid = '$id' ";
                $this->db->query($sql);

                $url = 'value_card.php?act=vc_query&' . str_replace('act=remove_vc', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            }
        }

        /* ------------------------------------------------------ */
        //-- 储值卡发放详情        页
        /* ------------------------------------------------------ */
        if ($act == 'send') {
            $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;

            $this->smarty->assign('type_id', $id);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['value_card_send']);
            return $this->smarty->display('value_card_send.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 导出储值卡
        /* ------------------------------------------------------ */
        if ($act == 'export_vc_list') {
            $filter['id'] = request()->get('id', 0);
            $filter['status'] = request()->get('status', -1);
            $filter['keywords'] = request()->get('keywords', '');

            $filter['ru_id'] = $adminru['ru_id'];
            $filter['type'] = 'value_card';
            $filter['page_size'] = 100;
            $filter['file_name'] = date('YmdHis') . rand(1000, 9999);

            ValueCardExport::dispatch($filter);

            return response()->json('OK');
        }
        /* ------------------------------------------------------ */
        //-- 储值卡发放操作
        /* ------------------------------------------------------ */
        if ($act == 'send_value_card') {
            @set_time_limit(0);

            /* 储值卡类型和生成的数量的处理 */
            $tid = $_POST['type_id'] ? intval($_POST['type_id']) : 0;
            $send_sum = !empty($_POST['send_num']) ? intval($_POST['send_num']) : 1;
            $card_type = intval($_POST['card_type']);
            $password_type = intval($_POST['password_type']);

            $sql = " SELECT vc_value, vc_prefix, vc_dis FROM " . $this->dsc->table('value_card_type') . " WHERE id = '$tid' ";
            $row = $this->db->getRow($sql);
            $vc_prefix = $row['vc_prefix'] ? trim($row['vc_prefix']) : '';
            $prefix_len = strlen($vc_prefix);
            $length = $prefix_len + $card_type;

            $vc_dis = $row['vc_dis'] ?? 0;
            $vc_dis = $vc_dis / 100;

            /* 生成储值卡序列号 */
            $num = $this->db->getOne(" SELECT MAX(SUBSTRING(value_card_sn,$prefix_len+1)) FROM " . $this->dsc->table('value_card') . " WHERE tid = '$tid' AND LENGTH(value_card_sn) = '$length' ");
            $num = $num ? intval($num) : 1;

            for ($i = 0, $j = 0; $i < $send_sum; $i++) {
                $value_card_sn = $vc_prefix . str_pad($num + $i + 1, $card_type, '0', STR_PAD_LEFT);
                $value_card_password = strtoupper(mc_random($password_type));

                $other = [
                    'tid' => $tid,
                    'value_card_sn' => $value_card_sn,
                    'value_card_password' => $value_card_password,
                    'vc_value' => $row['vc_value'] ?? 0,
                    'card_money' => $row['vc_value'] ?? 0,
                    'vc_dis' => $row['vc_dis'] ?? 0
                ];
                ValueCard::insert($other);

                $j++;
            }

            /* 记录管理员操作 */
            admin_log($tid, 'add', 'value_card');

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'value_card.php?act=list';
            return sys_msg($GLOBALS['_LANG']['creat_value_card'] . $j . $GLOBALS['_LANG']['value_card_num'], 0, $link);
        }

        /* ------------------------------------------------------ */
        //--  指定可使用的储值卡的店铺
        /* ------------------------------------------------------ */
        if ($act == 'select_merchants') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];
            $selected = !empty($_GET['selected']) ? trim($_GET['selected']) : '';
            $sql = " SELECT user_id as  ru_id FROM " . $this->dsc->table('merchants_shop_information') . "  WHERE merchants_audit = 1 and shop_close = 1  ";
            $shop_ids = $this->db->getAll($sql);

            $self = [
                [
                    'ru_id' => 0
                ]
            ];
            $shop_ids = ArrRepository::getArrCollapse([$shop_ids, $self]);

            $can_choice = [];
            if ($shop_ids) {
                foreach ($shop_ids as $k => $v) {
                    $can_choice[$k]['ru_id'] = $v['ru_id'];
                    $can_choice[$k]['rz_shop_name'] = $this->merchantCommonService->getShopName($v['ru_id'], 1);
                }
            }

            $is_choice = explode(',', $selected);

            $this->smarty->assign('can_choice', $can_choice);
            $this->smarty->assign('is_choice', $is_choice);
            $result['content'] = $GLOBALS['smarty']->fetch('library/merchants_list.lbi');
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //--  储值卡前缀是否重复
        /* ------------------------------------------------------ */
        elseif ($act == 'code_notice') {
            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $prefix = isset($_REQUEST['prefix']) && !empty($_REQUEST['prefix']) ? trim($_REQUEST['prefix']) : '';

            /* 检查是否重复 */
            $is_only = ValueCardType::where('vc_prefix', $prefix);

            if ($id > 0) {
                $is_only = $is_only->where('id', '<>', $id);
            }

            $is_only = $is_only->count();

            if ($is_only > 0) {
                $error = false;
            } else {
                $error = true;
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //--  设置储值卡是否可用
        /* ------------------------------------------------------ */
        elseif ($act == 'use_status') {

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $status = isset($_REQUEST['status']) && !empty($_REQUEST['status']) ? intval($_REQUEST['status']) : 0;

            ValueCard::where('vid', $id)->update([
                'use_status' => $status
            ]);

            $url = 'value_card.php?act=vc_query&' . str_replace('act=use_status', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }

        /* ------------------------------------------------------ */
        //--  批量操作储值卡片
        /* ------------------------------------------------------ */
        elseif ($act == 'handle_value_card') {

            $vid = request()->input('checkboxes', []);
            $vid = BaseRepository::getExplode($vid);

            $tid = request()->input('tid', 0);

            $update = 0;
            if ($vid) {

                if (request()->has('invalid_vc')) {
                    $update = ValueCard::whereIn('vid', $vid)->update([
                        'use_status' => 0
                    ]);
                } elseif (request()->has('use_vc')) {
                    $update = ValueCard::whereIn('vid', $vid)->update([
                        'use_status' => 1
                    ]);
                } elseif (request()->has('remove_vc')) {
                    $update = ValueCard::whereIn('vid', $vid)->where('user_id', 0)->delete();
                }
            }

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'value_card.php?act=vc_list&tid=' . $tid;

            if ($update > 0) {
                $bacth_handle = $GLOBALS['_LANG']['batch_handle_succeed'];
            } else {
                $bacth_handle = $GLOBALS['_LANG']['batch_handle_fail'];
            }

            return sys_msg($bacth_handle, 0, $link);
        }
    }

    /**
     * 储值卡类型列表
     * @access  public
     * @return array
     */
    private function vc_type_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'vc_type_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        /* 查询条件 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = " WHERE 1 ";

        $where .= (!empty($filter['keyword'])) ? " AND (ggt.gift_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%')" : '';

        $sql = " SELECT COUNT(*) FROM " . $this->dsc->table('value_card_type') . " AS t " . $where;
        $filter['record_count'] = $this->db->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $sql = "SELECT * FROM " . $this->dsc->table('value_card_type') . " AS t" . " $where ORDER BY $filter[sort_by] $filter[sort_order]";

        $arr = [];
        $filter['start'] = $filter['start'] ?? 0;
        $res = $this->db->selectLimit($sql, $filter['page_size'], $filter['start']);

        foreach ($res as $row) {
            $array = [$GLOBALS['_LANG']['all_goods'], $GLOBALS['_LANG']['spec_cat'], $GLOBALS['_LANG']['spec_goods']];
            $row['use_condition'] = $array[$row['use_condition']];
            $row['vc_indate'] = $row['vc_indate'] . $GLOBALS['_LANG']['months'];
            $row['vc_dis'] = $row['vc_dis'] * 100 . '%';
            $row['send_amount'] = $this->send_amount($row['id']);
            $row['use_amount'] = $this->use_amount($row['id']);
            $arr[] = $row;
        }
        $arr = ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 储值卡列表
     * @access  public
     * @return void
     */
    private function vc_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'vc_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['tid'] = empty($_REQUEST['tid']) ? 0 : trim($_REQUEST['tid']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'vid' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['value_card_type'] = empty($_REQUEST['value_card_type']) ? 0 : intval($_REQUEST['value_card_type']);
        $filter['use_status'] = isset($_REQUEST['use_status']) ? intval($_REQUEST['use_status']) : -1;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : e(trim($_REQUEST['keywords']));

        $time = TimeRepository::getGmTime();

        $row = ValueCard::whereRaw(1);

        if ($filter['use_status'] == 0) {
            //失效
            $row = $row->where('use_status', 0);
        } elseif ($filter['use_status'] == 1) {
            //正常
            $row = $row->where('use_status', 1)
                ->where('end_time', '>', $time);
        } elseif ($filter['use_status'] == 2) {
            //过期
            $time = TimeRepository::getGmTime();
            $row = $row->where('end_time', '<', $time)
                ->where('end_time', '>', 0);
        }

        if ($filter['keywords']) {
            $row = $row->where(function ($query) use ($filter) {
                $keywords = $this->dscRepository->mysqlLikeQuote($filter['keywords']);

                $user_id = Users::query()->where('user_name', 'like', '%' . $keywords . '%')
                    ->pluck('user_id');
                $user_id = BaseRepository::getToArray($user_id);

                $query = $query->where('value_card_sn', 'like', '%' . $keywords . '%');

                if ($user_id) {
                    $query = $query->orWhereIn('user_id', $user_id);
                }

                $orderIdList = OrderInfo::where('order_sn', 'like', '%' . $filter['keywords'] . '%')->pluck('order_id');
                $orderIdList = BaseRepository::getToArray($orderIdList);

                if ($orderIdList) {
                    $vcIdList = ValueCardRecord::query()->whereIn('order_id', $orderIdList)->pluck('vc_id');
                    $vcIdList = BaseRepository::getToArray($vcIdList);

                    $query->orWhereIn('vid', $vcIdList);
                }
            });
        }

        if ($filter['tid']) {
            $row = $row->where('tid', $filter['tid']);
        }

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count('vid');

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $time = TimeRepository::getGmTime();

            $user_id = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);

            $card_id = BaseRepository::getKeyPluck($res, 'tid');
            $cardList = ValueCardDataHandleService::getValueCardTypeDataList($card_id, ['id', 'name', 'add_time']);

            foreach ($res as $key => $val) {

                if (!empty($val['user_id']) && $time > $val['end_time']) {
                    $res[$key]['use_status'] = 2;
                }

                $res[$key]['name'] = $cardList[$val['tid']]['name'] ?? '';
                $res[$key]['user_name'] = $userList[$val['user_id']]['user_name'] ?? '';

                $res[$key]['bind_time'] = $val['bind_time'] > 0 ? TimeRepository::getLocalDate(config('shop.date_format'), $val['bind_time']) : $GLOBALS['_LANG']['no_use'];

                if (config('shop.show_mobile') == 0 && !empty($res[$key]['user_name'])) {
                    $res[$key]['user_name'] = $this->dscRepository->stringToStar($res[$key]['user_name']);
                }
                $res[$key]['vc_dis'] = $val['vc_dis'] == 1 ? $GLOBALS['_LANG']['wu'] : intval($val['vc_dis'] * 10) . $GLOBALS['_LANG']['percent'];
            }
        }

        $arr = ['item' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 储值卡使用记录列表
     * @return array
     */
    private function vc_use_log_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'vc_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['vc_id'] = empty($_REQUEST['vc_id']) ? 0 : intval($_REQUEST['vc_id']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'record_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $row = ValueCardRecord::where('vc_id', $filter['vc_id']);

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count('rid');

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            $order_id = BaseRepository::getKeyPluck($res, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'order_sn']);

            $vc_id = BaseRepository::getKeyPluck($res, 'vc_id');
            $vcList = ValueCardDataHandleService::getValueCardDataList($vc_id, ['vid', 'user_id', 'value_card_sn']);
            $user_id = BaseRepository::getKeyPluck($vcList, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);

            foreach ($res as $key => $val) {

                $vcInfo = $vcList[$val['vc_id']] ?? [];
                $user_id = $vcInfo['user_id'] ?? 0;

                $res[$key]['value_card_sn'] = $vcInfo['value_card_sn'] ?? 'N/A';
                $res[$key]['user_name'] = $userList[$user_id]['user_name'] ?? 'N/A';
                $res[$key]['order_sn'] = $orderList[$val['order_id']]['order_sn'] ?? 'N/A';

                if ($val['add_val'] > 0) {
                    $res[$key]['use_val_format'] = $this->dscRepository->getPriceFormat(0);
                } else {
                    $res[$key]['use_val_format'] = $this->dscRepository->getPriceFormat("-" . $val['use_val']);
                }

                if ($val['add_val'] > 0) {
                    $res[$key]['add_val_format'] = $this->dscRepository->getPriceFormat($val['add_val']);
                } else {
                    $res[$key]['add_val_format'] = $this->dscRepository->getPriceFormat(0);
                }

                $res[$key]['add_val_format'] = $this->dscRepository->getPriceFormat("+" . $val['add_val']);
                $res[$key]['record_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $val['record_time']);

                if (config('shop.show_mobile') == 0 && !empty($res[$key]['user_name'])) {
                    $res[$key]['user_name'] = $this->dscRepository->stringToStar($res[$key]['user_name']);
                }
            }
        }

        $arr = ['item' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /*
     * 已发放储值卡数量
     */

    private function send_amount($id)
    {
        $sql = " SELECT COUNT(*) FROM " . $this->dsc->table('value_card') . " WHERE tid = '$id' ";
        return $this->db->getOne($sql);
    }

    /*
     * 已使用储值卡数量
     */

    private function use_amount($id)
    {
        $sql = " SELECT COUNT(*) FROM " . $this->dsc->table('value_card') . " WHERE tid = '$id' AND user_id > 0 AND bind_time > 0 ";
        return $this->db->getOne($sql);
    }
}
