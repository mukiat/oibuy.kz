<?php

namespace App\Modules\Admin\Controllers;

use App\Models\GoodsInventoryLogs;
use App\Models\RegionWarehouse;
use App\Repositories\Common\BaseRepository;
use App\Services\Goods\GoodsInventoryLogsManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Store\StoreCommonService;


/**
 * 商品库存日志
 * Class GoodsInventoryLogsController
 * @package App\Modules\Admin\Controllers
 */
class GoodsInventoryLogsController extends InitController
{
    protected $merchantCommonService;

    protected $goodsInventoryLogsManageService;
    protected $storeCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        GoodsInventoryLogsManageService $goodsInventoryLogsManageService,
        StoreCommonService $storeCommonService
    )
    {
        $this->merchantCommonService = $merchantCommonService;

        $this->goodsInventoryLogsManageService = $goodsInventoryLogsManageService;
        $this->storeCommonService = $storeCommonService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = e(request()->get('act', 'list'));

        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $step = e(request()->get('step', ''));

        /*------------------------------------------------------ */
        //-- 获取所有日志列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            if ($step == 'put') {
                /* 权限的判断 */
                admin_priv('storage_put');

                $storage = "-" . __('admin::common.01_goods_storage_put');
                $this->smarty->assign('step', 'put');
            } else {
                /* 权限的判断 */
                admin_priv('storage_out');

                $storage = "-" . __('admin::common.02_goods_storage_out');
                $this->smarty->assign('step', 'out');
            }

            $this->smarty->assign('ur_here', __('admin::common.13_goods_inventory_logs') . $storage);
            $ip_list = isset($ip_list) ? $ip_list : '';
            $this->smarty->assign('ip_list', $ip_list);
            $this->smarty->assign('full_page', 1);

            $log_list = $this->goodsInventoryLogsManageService->getGoodsInventoryLogs($adminru['ru_id']);

            $this->smarty->assign('log_list', $log_list['list']);
            $this->smarty->assign('filter', $log_list['filter']);
            $this->smarty->assign('record_count', $log_list['record_count']);
            $this->smarty->assign('page_count', $log_list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $warehouse_list = get_warehouse_list_goods();
            $this->smarty->assign('warehouse_list', $warehouse_list); //仓库列表

            $sort_flag = sort_flag($log_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('goods_inventory_logs.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $log_list = $this->goodsInventoryLogsManageService->getGoodsInventoryLogs($adminru['ru_id']);

            $this->smarty->assign('log_list', $log_list['list']);
            $this->smarty->assign('filter', $log_list['filter']);
            $this->smarty->assign('record_count', $log_list['record_count']);
            $this->smarty->assign('page_count', $log_list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $warehouse_list = get_warehouse_list_goods();
            $this->smarty->assign('warehouse_list', $warehouse_list); //仓库列表

            $sort_flag = sort_flag($log_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('goods_inventory_logs.dwt'),
                '',
                ['filter' => $log_list['filter'], 'page_count' => $log_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 查询仓库地区
        /*------------------------------------------------------ */
        elseif ($act == 'search_area') {

            if ($step == 'put') {
                /* 权限的判断 */
                $check_auth = check_authz_json('storage_put');
            } else {
                /* 权限的判断 */
                $check_auth = check_authz_json('storage_out');
            }
            if ($check_auth !== true) {
                return $check_auth;
            }

            $warehouse_id = (int)request()->get('warehouse_id', 0);

            $res = RegionWarehouse::where('region_type', 1)->where('parent_id', $warehouse_id);
            $region_list = BaseRepository::getToArrayGet($res);
            $select = '';
            $select .= '<div class="cite">' . __('admin::common.please_select') . '</div><ul>';
            if ($region_list) {
                foreach ($region_list as $key => $row) {
                    $select .= '<li><a href="javascript:;" data-value="' . $row['region_id'] . '" class="ftx-01">' . $row['region_name'] . '</a></li>';
                }
            }
            $select .= '</ul><input name="area_id" type="hidden" value="" id="area_id_val">';

            $result = $select;

            return make_json_result($result);
        }

        /*------------------------------------------------------ */
        //-- 批量删除日志记录
        /*------------------------------------------------------ */
        elseif ($act == 'batch_drop') {
            if ($step == 'put') {
                /* 权限的判断 */
                admin_priv('storage_put');
            } else {
                /* 权限的判断 */
                admin_priv('storage_out');
            }

            $count = 0;
            $checkboxes = request()->get('checkboxes', []);

            foreach ($checkboxes as $key => $id) {
                GoodsInventoryLogs::where('id', $id)->delete();

                $count++;
            }

            if ($count) {
                admin_log('', 'remove', 'goods_inventory_logs');
            }

            if ($step) {
                $step = '&step=' . $step;
            }

            $link[] = ['text' => __('admin::common.go_back'), 'href' => 'goods_inventory_logs.php?act=list' . $step];
            return sys_msg(sprintf(__('admin::goods_inventory_logs.batch_drop_success'), $count), 0, $link);
        }
    }
}
