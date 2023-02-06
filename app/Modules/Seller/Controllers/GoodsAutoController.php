<?php

namespace App\Modules\Seller\Controllers;

use App\Models\AutoManage;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Cron\CronService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 商品上下架
 * Class GoodsAutoController
 * @package App\Modules\Seller\Controllers
 */
class GoodsAutoController extends InitController
{
    protected $merchantCommonService;
    protected $cronService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        CronService $cronService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->cronService = $cronService;
    }

    public function index()
    {
        $act = e(request()->get('act', ''));

        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "goods");
        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $this->smarty->assign('thisfile', 'goods_auto.php');
        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'goods_auto']);
        if ($act == 'list') {
            admin_priv('goods_auto');

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('current', 'goods_auto_list');
            $goodsdb = $this->get_auto_goods($adminru['ru_id']);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($goodsdb, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $crons_enable = $this->cronService->getManageOpen();

            $this->smarty->assign('crons_enable', $crons_enable);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', __('admin::common.goods_auto'));
            $this->smarty->assign('cfg_lang', config('shop.lang'));
            $this->smarty->assign('goodsdb', $goodsdb['goodsdb']);
            $this->smarty->assign('filter', $goodsdb['filter']);
            $this->smarty->assign('record_count', $goodsdb['record_count']);
            $this->smarty->assign('page_count', $goodsdb['page_count']);

            $this->smarty->assign('now_time', TimeRepository::getLocalDate('Y-m-d'));

            return $this->smarty->display('goods_auto.dwt');
        } elseif ($act == 'query') {
            admin_priv('goods_auto');

            $goodsdb = $this->get_auto_goods($adminru['ru_id']);

            //分页
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($goodsdb, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $this->smarty->assign('goodsdb', $goodsdb['goodsdb']);
            $this->smarty->assign('filter', $goodsdb['filter']);
            $this->smarty->assign('cfg_lang', config('shop.lang'));
            $this->smarty->assign('record_count', $goodsdb['record_count']);
            $this->smarty->assign('page_count', $goodsdb['page_count']);

            $sort_flag = sort_flag($goodsdb['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('goods_auto.dwt'), '', ['filter' => $goodsdb['filter'], 'page_count' => $goodsdb['page_count']]);
        } elseif ($act == 'del') {
            admin_priv('goods_auto');

            $goods_id = (int)request()->get('goods_id', 0);
            AutoManage::where('item_id', $goods_id)->where('type', 'goods')->delete();

            $links[] = ['text' => __('admin::common.goods_auto'), 'href' => 'goods_auto.php?act=list'];
            return sys_msg(__('admin::goods_auto.edit_ok'), 0, $links);
        } elseif ($act == 'edit_starttime') {
            $check_auth = check_authz_json('goods_auto');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->get('id');
            $val = request()->get('val');

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($val))) {
                return make_json_error('error');
            }

            $time = local_strtotime($val);
            if ($id <= 0 || $val == '0000-00-00' || $time <= 0) {
                return make_json_error('error');
            }

            $count = AutoManage::where('item_id', $id)->where('type', 'goods')->count();
            if ($count > 0) {
                AutoManage::where('item_id', $id)->where('type', 'goods')->update(['starttime' => (string)$time]);
            } else {
                AutoManage::insert(['item_id' => $id, 'type' => 'goods', 'starttime' => (string)$time]);
            }

            clear_cache_files();
            return make_json_result(stripslashes($_POST['val']), '', ['act' => 'goods_auto', 'id' => $id]);
        } elseif ($act == 'edit_endtime') {
            $check_auth = check_authz_json('goods_auto');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->get('id');
            $val = request()->get('val');

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($val))) {
                return make_json_error('error');
            }

            $time = local_strtotime(trim($val));
            if ($id <= 0 || $val == '0000-00-00' || $time <= 0) {
                return make_json_error('error');
            }

            $count = AutoManage::where('item_id', $id)->where('type', 'goods')->count();
            if ($count > 0) {
                AutoManage::where('item_id', $id)->where('type', 'goods')->update(['endtime' => (string)$time]);
            } else {
                AutoManage::insert(['item_id' => $id, 'type' => 'goods', 'endtime' => (string)$time]);
            }

            clear_cache_files();
            return make_json_result(stripslashes($val), '', ['act' => 'goods_auto', 'id' => $id]);
        } //批量上架
        elseif ($act == 'batch_start') {
            admin_priv('goods_auto');

            $checkboxes = request()->get('checkboxes', []);

            if (!$checkboxes || !is_array($checkboxes)) {
                return sys_msg(__('admin::goods_auto.no_select_goods'), 1);
            }

            $date = request()->get('date', 0);
            if ($date == '0000-00-00') {
                $date = 0;
            } else {
                $date = local_strtotime(trim($date));
            }

            $is_presale = is_presale($checkboxes);
            if (!empty($is_presale)) {
                return sys_msg($is_presale . __('seller::goods.del_presale'));
            }

            foreach ($checkboxes as $id) {
                $count = AutoManage::where('item_id', $id)->where('type', 'goods')->count();
                if ($count > 0) {
                    AutoManage::where('item_id', $id)->where('type', 'goods')->update(['starttime' => (string)$date]);
                } else {
                    AutoManage::insert(['item_id' => $id, 'type' => 'goods', 'starttime' => (string)$date]);
                }
            }

            $lnk[] = ['text' => __('admin::goods_auto.back_list'), 'href' => 'goods_auto.php?act=list'];
            return sys_msg(__('admin::goods_auto.batch_start_succeed'), 0, $lnk);
        } //批量下架
        elseif ($act == 'batch_end') {
            admin_priv('goods_auto');

            $checkboxes = request()->get('checkboxes', []);

            if (!$checkboxes || !is_array($checkboxes)) {
                return sys_msg(__('admin::goods_auto.no_select_goods'), 1);
            }

            $date = request()->get('date', 0);
            if ($date == '0000-00-00') {
                $date = 0;
            } else {
                $date = local_strtotime(trim($date));
            }

            foreach ($checkboxes as $id) {
                $count = AutoManage::where('item_id', $id)->where('type', 'goods')->count();
                if ($count > 0) {
                    AutoManage::where('item_id', $id)->where('type', 'goods')->update(['endtime' => (string)$date]);
                } else {
                    AutoManage::insert(['item_id' => $id, 'type' => 'goods', 'endtime' => (string)$date]);
                }
            }

            $lnk[] = ['text' => __('admin::goods_auto.back_list'), 'href' => 'goods_auto.php?act=list'];
            return sys_msg(__('admin::goods_auto.batch_end_succeed'), 0, $lnk);
        }
    }

    private function get_auto_goods($ru_id = 0)
    {
        $where = ' WHERE g.is_delete <> 1 ';

        //ecmoban模板堂 --zhuo start
        if ($ru_id > 0) {
            $where .= " and g.user_id = '$ru_id' ";
        }
        //ecmoban模板堂 --zhuo end

        $goods_name = e(request()->input('goods_name', ''));
        if (!empty($goods_name)) {
            $goods_name = trim($goods_name);
            $where .= " AND g.goods_name LIKE '%$goods_name%'";
            $filter['goods_name'] = $goods_name;
        }

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_auto_goods';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'last_update' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('goods') . " g" . $where;
        $filter['record_count'] = $this->db->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 查询 */
        $sql = "SELECT g.*,a.starttime,a.endtime FROM " . $this->dsc->table('goods') . " g LEFT JOIN " . $this->dsc->table('auto_manage') . " a ON g.goods_id = a.item_id AND a.type='goods'" . $where .
            " ORDER by goods_id, " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
            " LIMIT " . $filter['start'] . ",$filter[page_size]";

        $query = $this->db->query($sql);

        $goodsdb = [];

        if ($query) {

            $ru_id = BaseRepository::getKeyPluck($query, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($query as $rt) {
                if (!empty($rt['starttime'])) {
                    $rt['starttime'] = TimeRepository::getLocalDate('Y-m-d', $rt['starttime']);
                }
                if (!empty($rt['endtime'])) {
                    $rt['endtime'] = TimeRepository::getLocalDate('Y-m-d', $rt['endtime']);
                }

                $rt['user_name'] =$merchantList[$rt['user_id']]['shop_name'] ?? '';

                $goodsdb[] = $rt;
            }
        }

        $arr = ['goodsdb' => $goodsdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
