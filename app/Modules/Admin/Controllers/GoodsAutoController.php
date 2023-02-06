<?php

namespace App\Modules\Admin\Controllers;

use App\Models\AutoManage;
use App\Repositories\Common\TimeRepository;
use App\Services\Cron\CronService;
use App\Services\Goods\GoodsAutoManageService;
use App\Services\Merchant\MerchantCommonService;

/**
 * 商品上下架
 * Class GoodsAutoController
 * @package App\Modules\Admin\Controllers
 */
class GoodsAutoController extends InitController
{
    protected $merchantCommonService;
    protected $goodsAutoManageService;
    protected $cronService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        GoodsAutoManageService $goodsAutoManageService,
        CronService $cronService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsAutoManageService = $goodsAutoManageService;
        $this->cronService = $cronService;
    }

    public function index()
    {
        $act = e(request()->get('act', ''));

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

            $goodsdb = $this->goodsAutoManageService->getAutoGoods($adminru['ru_id']);

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

            $goodsdb = $this->goodsAutoManageService->getAutoGoods($adminru['ru_id']);
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

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                return make_json_error('error');
            }

            $time = TimeRepository::getLocalStrtoTime($val);
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
            return make_json_result(stripslashes($val), '', ['act' => 'goods_auto', 'id' => $id]);
        } elseif ($act == 'edit_endtime') {
            $check_auth = check_authz_json('goods_auto');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->get('id');
            $val = request()->get('val');

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                return make_json_error('error');
            }

            $time = TimeRepository::getLocalStrtoTime($val);
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
            if ($date != '0000-00-00') {
                $date = TimeRepository::getLocalStrtoTime($date);
            }

            $is_presale = is_presale($checkboxes);
            if(!empty($is_presale)){
                return sys_msg($is_presale . __('admin::goods.del_presale'));
            }

            foreach ($checkboxes as $id) {
                $count = AutoManage::where('item_id', $id)->where('type', 'goods')->count();
                if ($count > 0) {
                    AutoManage::where('item_id', $id)->where('type', 'goods')->update(['starttime' => $date]);
                } else {
                    AutoManage::insert(['item_id' => $id, 'type' => 'goods', 'starttime' => $date]);
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
            if ($date != '0000-00-00') {
                $date = TimeRepository::getLocalStrtoTime($date);
            }

            foreach ($checkboxes as $id) {
                $count = AutoManage::where('item_id', $id)->where('type', 'goods')->count();
                if ($count > 0) {
                    AutoManage::where('item_id', $id)->where('type', 'goods')->update(['endtime' => $date]);
                } else {
                    AutoManage::insert(['item_id' => $id, 'type' => 'goods', 'endtime' => $date]);
                }
            }

            $lnk[] = ['text' => __('admin::goods_auto.back_list'), 'href' => 'goods_auto.php?act=list'];
            return sys_msg(__('admin::goods_auto.batch_end_succeed'), 0, $lnk);
        }
    }
}
