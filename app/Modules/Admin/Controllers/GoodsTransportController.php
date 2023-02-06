<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Goods;
use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\GoodsTransportTpl;
use App\Models\Region;
use App\Models\Shipping;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Goods\GoodsTransportManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Store\StoreCommonService;

/**
 * 管理中心商品运费模板
 */
class GoodsTransportController extends InitController
{
    protected $merchantCommonService;

    protected $goodsTransportManageService;
    protected $dscRepository;
    protected $storeCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        GoodsTransportManageService $goodsTransportManageService,
        DscRepository $dscRepository,
        StoreCommonService $storeCommonService
    )
    {
        $this->merchantCommonService = $merchantCommonService;

        $this->goodsTransportManageService = $goodsTransportManageService;
        $this->dscRepository = $dscRepository;
        $this->storeCommonService = $storeCommonService;
    }

    public function index()
    {
        load_helper('order');

        $admin_id = get_admin_id();
        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $tid = request()->get('tid', 0);
        if ($tid) {
            $trow = get_goods_transport($tid);
            $adminru['ru_id'] = $trow['ru_id'];
        }

        $act = request()->get('act', '');

        /* ------------------------------------------------------ */
        //-- 列表
        /* ------------------------------------------------------ */
        if ($act == 'list') {
            $this->smarty->assign('menu_select', ['action' => '01_system', 'current' => '04_shipping_transport']);

            $ru_id = $adminru['ru_id'];
            $transport_list = $this->goodsTransportManageService->getTransportList();
            $this->smarty->assign('transport_list', $transport_list['list']);
            $this->smarty->assign('filter', $transport_list['filter']);
            $this->smarty->assign('record_count', $transport_list['record_count']);
            $this->smarty->assign('page_count', $transport_list['page_count']);

            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()));

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', __('admin::goods_transport.goods_transport'));

            return $this->smarty->display('goods_transport_list.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 排序、分页、查询
        /* ------------------------------------------------------ */
        elseif ($act == 'query') {
            $ru_id = $adminru['ru_id'];
            $transport_list = $this->goodsTransportManageService->getTransportList();
            $this->smarty->assign('transport_list', $transport_list['list']);
            $this->smarty->assign('filter', $transport_list['filter']);
            $this->smarty->assign('record_count', $transport_list['record_count']);
            $this->smarty->assign('page_count', $transport_list['page_count']);

            return make_json_result($this->smarty->fetch('goods_transport_list.dwt'), '', ['filter' => $transport_list['filter'], 'page_count' => $transport_list['page_count']]);
        }

        /* ------------------------------------------------------ */
        //-- 添加、编辑
        /* ------------------------------------------------------ */
        elseif ($act == 'add' || $act == 'edit') {
            $tid = request()->get('tid', 0);

            $ru_id = GoodsTransport::where('tid', $tid)->value('ru_id');
            $ru_id = $ru_id ? $ru_id : 0;

            if (empty($tid)) {
                $ru_id = $adminru['ru_id'];
            }

            $transport_info = [];
            $shipping_tpl = [];
            if ($act == 'add') {
                $form_action = 'insert';

                GoodsTransportTpl::where('tid', 0)->where('admin_id', $admin_id)->delete();
            } else {
                $form_action = 'update';
                if ($tid > 0) {
                    $transport_info = $trow;
                    $shipping_tpl = get_transport_shipping_list($tid, $ru_id);

                    if ($transport_info['freight_type'] == 1) {
                        // 快递模板 配送方式id
                        $transport_info['shipping_id'] = GoodsTransportTpl::where('tid', $transport_info['tid'])->value('shipping_id');
                    }
                }
            }

            $this->smarty->assign('shipping_tpl', $shipping_tpl);
            $this->smarty->assign('form_action', $form_action);
            $this->smarty->assign('tid', $tid);
            $this->smarty->assign('transport_info', $transport_info);
            $this->smarty->assign('transport_area', $this->goodsTransportManageService->getTransportArea($tid));
            $this->smarty->assign('transport_express', $this->goodsTransportManageService->getTransportExpress($tid));

            $row = [
                'shipping_code' => ''
            ];

            //快递列表
            $shipping_list = shipping_list();
            foreach ($shipping_list as $key => $val) {
                //剔除手机快递
                if (substr($row['shipping_code'], 0, 5) == 'ship_') {
                    unset($shipping_list[$key]);
                    continue;
                }
                /* 剔除上门自提 */
                if ($val['shipping_code'] == 'cac') {
                    unset($shipping_list[$key]);
                }
            }

            $this->smarty->assign('shipping_list', $shipping_list);

            $this->smarty->assign('ur_here', __('admin::goods_transport.transport_info'));
            $this->smarty->assign('action_link', ['href' => 'goods_transport.php?act=list', 'text' => __('admin::goods_transport.goods_transport')]);

            return $this->smarty->display('goods_transport_info.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 处理
        /* ------------------------------------------------------ */
        elseif ($act == 'insert' || $act == 'update') {
            $data = [];
            $data['tid'] = request()->get('tid', 0);
            $data['ru_id'] = $adminru['ru_id'];
            $data['type'] = request()->get('type', 0);
            $data['title'] = request()->get('title', '');
            $data['freight_type'] = request()->get('freight_type', 0);
            $data['update_time'] = gmtime();
            $data['free_money'] = request()->get('free_money', 0);
            $data['shipping_title'] = request()->get('shipping_title', 0);

            $s_tid = $data['tid'];

            //处理模板数据
            $res = GoodsTransportTpl::select('id');
            if ($act == 'update') {

                // 验证
                $links = [['href' => 'goods_transport.php?act=edit&tid='. $s_tid, 'text' => __('admin::common.go_back')]];

                if ($data['freight_type'] == 0) {
                    // 自定义运费模板
                    $extend = GoodsTransportExtend::where('tid', $s_tid)->first();
                    if (empty($extend) || empty($extend->top_area_id)) {
                        // 地区不能为空
                        return sys_msg('配送地区不能为空', 1, $links);
                    }
                    $express = GoodsTransportExpress::where('tid', $s_tid)->first();
                    if (empty($express) || empty($express->shipping_id)) {
                        // 快递方式不能为空
                        return sys_msg('快递方式不能为空', 1, $links);
                    }

                } else {
                    // 快递模板
                    $tpl = GoodsTransportTpl::where('tid', $s_tid)->first();
                    if (empty($tpl) || empty($tpl->shipping_id)) {
                        // 配送方式不能为空
                        return sys_msg('配送方式且运费模板不能为空', 1, $links);
                    }
                }

                $msg = lang('admin/goods_transport.edit_success');
                GoodsTransport::where('tid', $data['tid'])->update($data);
                $tid = $s_tid;

                $res = $res->where('tid', $tid);
            } else {
                $msg = lang('admin/goods_transport.add_success');
                $data = BaseRepository::recursiveNullVal($data);
                $tid = GoodsTransport::insertGetId($data);

                $gte_data = ['tid' => $tid];
                GoodsTransportExtend::where('tid', 0)->where('admin_id', $admin_id)->update($gte_data);
                GoodsTransportExpress::where('tid', 0)->where('admin_id', $admin_id)->update($gte_data);

                $res = $res->where('admin_id', $admin_id)->where('tid', 0);
            }

            //处理运费模板
            if ($data['freight_type'] > 0) {
                if (!session()->has($s_tid . '.tpl_id') && empty(session($s_tid . '.tpl_id'))) {
                    $tpl_id = BaseRepository::getToArrayGet($res);
                    $tpl_id = BaseRepository::getFlatten($tpl_id);
                } else {
                    $tpl_id = session($s_tid . '.tpl_id');
                }

                if (!empty($tpl_id)) {
                    $tpl_id = BaseRepository::getExplode($tpl_id);
                    $gtt_data = ['tid' => $tid];
                    GoodsTransportTpl::where('admin_id', $admin_id)->where('tid', 0)->whereIn('id', $tpl_id)->update($gtt_data);

                    session()->forget($s_tid . '.tpl_id');
                }
            }

            $sprice = request()->get('sprice', []);
            $shipping_fee = request()->get('shipping_fee', []);

            //处理地区数据
            if (count($sprice) > 0) {
                foreach ($sprice as $key => $val) {
                    $info = [];
                    $info['sprice'] = $val;
                    GoodsTransportExtend::where('id', $key)->update($info);
                }
            }

            //处理快递数据
            if (count($shipping_fee) > 0) {
                foreach ($shipping_fee as $key => $val) {
                    $info = [];
                    $info['shipping_fee'] = $val;
                    GoodsTransportExpress::where('id', $key)->update($info);
                }
            }

            $links = [
                ['href' => 'goods_transport.php?act=list', 'text' => __('admin::goods_transport.back_list')]
            ];
            return sys_msg($msg, 0, $links);
        }

        /* ------------------------------------------------------ */
        //-- 删除
        /* ------------------------------------------------------ */
        elseif ($act == 'remove') {
            /*$check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }*/

            $id = request()->get('id', 0);
            //查询是否存在已绑定模板的商品
            $goods_count = Goods::where('tid', $id)->count();
            if (!empty($goods_count)) {
                $result['error'] = 1;
                $result['message'] = __('admin::goods_transport.delete_transport_error');
                return response()->json($result);
            }
            GoodsTransport::where('tid', $id)->delete();

            //删除拓展数据
            GoodsTransportExtend::where('tid', $id)->delete();

            GoodsTransportExpress::where('tid', $id)->delete();

            GoodsTransportTpl::where('tid', $id)->delete();

            $data = ['tid' => 0];
            Goods::where('tid', $id)->update($data);

            $url = 'goods_transport.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }

        /* ------------------------------------------------------ */
        //-- 批量删除
        /* ------------------------------------------------------ */
        elseif ($act == 'batch_drop') {
            $checkboxes = request()->get('checkboxes', []);
            if ($checkboxes) {
                $del_count = 0;
                foreach ($checkboxes as $key => $id) {
                    $id = !empty($id) ? intval($id) : 0;

                    GoodsTransport::where('tid', $id)->delete();

                    //删除拓展数据
                    GoodsTransportExtend::where('tid', $id)->delete();

                    GoodsTransportExpress::where('tid', $id)->delete();

                    GoodsTransportTpl::where('tid', $id)->delete();

                    $data = ['tid' => 0];
                    Goods::where('tid', $id)->update($data);

                    $del_count++;
                }

                $links[] = ['text' => __('admin::goods_transport.back_list'), 'href' => 'goods_transport.php?act=list'];
                return sys_msg(sprintf(__('admin::goods_transport.batch_drop_success'), $del_count), 0, $links);
            } else {
                $links[] = ['text' => __('admin::goods_transport.back_list'), 'href' => 'goods_transport.php?act=list'];
                return sys_msg(__('admin::group_buy.no_select_group_buy'), 0, $links);
            }
        }

        /* ------------------------------------------------------ */
        //-- 修改标题
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_title') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $title = json_str_iconv(request()->get('val', ''));

            $data = [
                'title' => $title,
                'update_time' => gmtime()
            ];
            $res = GoodsTransport::where('tid', $id)->update($data);
            if ($res > 0) {
                return make_json_result(stripslashes($title));
            }
        }

        /* ------------------------------------------------------ */
        //-- 添加地区
        /* ------------------------------------------------------ */
        elseif ($act == 'add_area') {
            $data = [];
            $data['tid'] = request()->get('tid', 0);
            $data['ru_id'] = $adminru['ru_id'];
            $data['admin_id'] = $admin_id;

            GoodsTransportExtend::insert($data);

            $this->smarty->assign('transport_area', $this->goodsTransportManageService->getTransportArea($data['tid']));
            $html = $this->smarty->fetch('library/goods_transport_area.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 删除地区
        /* ------------------------------------------------------ */
        elseif ($act == 'drop_area') {
            $id = request()->get('id', 0);
            $tid = GoodsTransportExtend::where('id', $id)->value('tid');
            $tid = $tid ? $tid : 0;

            GoodsTransportExtend::where('id', $id)->delete();

            $this->smarty->assign('transport_area', $this->goodsTransportManageService->getTransportArea($tid));
            $html = $this->smarty->fetch('library/goods_transport_area.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 编辑地区
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_area') {
            $id = request()->get('id', 0);

            $transportExtend = GoodsTransportExtend::where('id', $id)->select('tid', 'top_area_id', 'area_id')->first();
            $tid = $transportExtend['tid'] ?? 0;
            //已选省份
            $province_selected = $transportExtend['top_area_id'] ?? '';
            $province_selected = explode(',', $province_selected);
            //已选城市
            $city_selected = $transportExtend['area_id'] ?? '';
            $city_selected = explode(',', $city_selected);
            //除自己以外的被选城市

            $city_disabled = GoodsTransportExtend::where('tid', $tid)
                ->where('id', '<>', $id);

            if ($tid == 0) {
                $city_disabled = $city_disabled->where('admin_id', $admin_id);
            }

            $city_disabled = BaseRepository::getToArrayGet($city_disabled);
            $city_disabled = BaseRepository::getKeyPluck($city_disabled, 'area_id');

            $list = [];
            foreach ($city_disabled as $key => $row) {
                $list[] = $row ? explode(',', $row) : [];
            }

            $city_disabled = BaseRepository::getFlatten($list);
            $city_disabled = $city_disabled ? array_unique($city_disabled) : [];

            //地区列表
            $province = get_regions(1, 1); //省
            foreach ($province as $key => $val) {
                $child_num = 0; //自选城市
                $other_num = 0; //他选城市
                $province[$key]['is_selected'] = in_array($val['region_id'], $province_selected) ? 1 : 0;
                $city = get_regions(2, $val['region_id']); //市
                foreach ($city as $k => $v) {
                    $city[$k]['is_selected'] = $city_selected && in_array($v['region_id'], $city_selected) ? 1 : 0;
                    $city[$k]['is_disabled'] = $city_disabled && in_array($v['region_id'], $city_disabled) ? 1 : 0;
                    $child_num += $city_selected && in_array($v['region_id'], $city_selected) ? 1 : 0;
                    $other_num += $city_disabled && in_array($v['region_id'], $city_disabled) ? 1 : 0;
                }
                $province[$key]['child'] = $city;
                $province[$key]['child_num'] = $child_num;
                $province[$key]['is_disabled'] = (count($city) == ($child_num + $other_num)) ? 1 : 0;
            }

            $this->smarty->assign('id', $id);
            $this->smarty->assign('area_map', $province);
            $html = $this->smarty->fetch('library/goods_transport_area_list.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 保存地区
        /* ------------------------------------------------------ */
        elseif ($act == 'save_area') {
            $id = request()->get('id', 0);
            $tid = GoodsTransportExtend::where('id', $id)->value('tid');
            $tid = $tid ? $tid : 0;
            $data = [];
            $data['area_id'] = request()->get('area_id', 0);
            $data['top_area_id'] = request()->get('top_area_id', '');

            GoodsTransportExtend::where('id', $id)->update($data);

            $this->smarty->assign('transport_area', $this->goodsTransportManageService->getTransportArea($tid));
            $html = $this->smarty->fetch('library/goods_transport_area.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 保存价格
        /* ------------------------------------------------------ */
        elseif ($act == 'save_sprice') {
            $id = request()->get('id', 0);

            $data = [];
            $data['sprice'] = request()->get('sprice', '');

            $return = GoodsTransportExtend::where('id', $id)->update($data);

            return make_json_result($return);
        }

        /* ------------------------------------------------------ */
        //-- 保存快递方式价格
        /* ------------------------------------------------------ */
        elseif ($act == 'save_shipping_fee') {
            $id = request()->get('id', 0);
            $data = [];
            $data['shipping_fee'] = request()->get('sprice', '');

            $return = GoodsTransportExpress::where('id', $id)->update($data);

            return make_json_result($return);
        }

        /* ------------------------------------------------------ */
        //-- 添加快递
        /* ------------------------------------------------------ */
        elseif ($act == 'add_express') {
            $data = [];
            $data['tid'] = request()->get('tid', 0);
            $data['ru_id'] = $adminru['ru_id'];
            $data['admin_id'] = $admin_id;

            GoodsTransportExpress::insert($data);

            $this->smarty->assign('transport_express', $this->goodsTransportManageService->getTransportExpress($data['tid']));
            $html = $this->smarty->fetch('library/goods_transport_express.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 删除快递
        /* ------------------------------------------------------ */
        elseif ($act == 'drop_express') {
            $id = request()->get('id', 0);
            $tid = GoodsTransportExpress::where('id', $id)->value('tid');
            $tid = $tid ? $tid : 0;

            GoodsTransportExpress::where('id', $id)->delete();

            $this->smarty->assign('transport_express', $this->goodsTransportManageService->getTransportExpress($tid));
            $html = $this->smarty->fetch('library/goods_transport_express.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 编辑快递
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_express') {
            $id = (int)request()->get('id', 0);
            $transportExpress = GoodsTransportExpress::where('id', $id)->first();

            $tid = $transportExpress['tid'] ?? 0;
            $seller_id = $transportExpress['ru_id'] ?? 0;

            //已选快递
            $express_selected = $transportExpress['shipping_id'] ?? '';
            $express_selected = $express_selected ? explode(',', $express_selected) : [];
            //除自己以外的被选快递
            $res = GoodsTransportExpress::query()->where('tid', $tid)->where('id', '<>', $id);
            if ($tid == 0) {
                $res = $res->where('admin_id', $admin_id);
            }

            $express_disabled = $res->pluck('shipping_id')->map(function ($item) {
                return explode(',', $item);
            });
            $express_disabled = $express_disabled->flatten()->unique()->all();

            //快递列表
            $is_cac = true;
            $shipping_list = shipping_list($is_cac);
            foreach ($shipping_list as $k => $v) {
                if ($seller_id > 0 && $is_cac == true) {
                    /* 剔除上门自提 */
                    if ($v['shipping_code'] == 'cac') {
                        unset($shipping_list[$k]);
                        continue;
                    }
                }

                $shipping_list[$k]['is_selected'] = $express_selected && in_array($v['shipping_id'], $express_selected) ? 1 : 0;
                $shipping_list[$k]['is_disabled'] = $express_disabled && in_array($v['shipping_id'], $express_disabled) ? 1 : 0;
            }

            $this->smarty->assign('id', $id);
            $this->smarty->assign('shipping_list', $shipping_list);
            $html = $this->smarty->fetch('library/goods_transport_express_list.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 保存快递
        /* ------------------------------------------------------ */
        elseif ($act == 'save_express') {
            $id = request()->get('id', 0);
            $tid = GoodsTransportExpress::where('id', $id)->value('tid');
            $tid = $tid ? $tid : 0;
            $data = [];
            $data['shipping_id'] = request()->get('shipping_id', '');

            GoodsTransportExpress::where('id', $id)->update($data);

            $this->smarty->assign('transport_express', $this->goodsTransportManageService->getTransportExpress($tid));
            $html = $this->smarty->fetch('library/goods_transport_express.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 获取快递模板信息
        /* ------------------------------------------------------ */
        elseif ($act == 'get_shipping_tem') {
            $shipping_id = request()->get('shipping_id', 0);
            $tid = request()->get('tid', 0);
            $id = request()->get('id', 0);

            $res = GoodsTransportTpl::whereRaw(1);
            if (!empty($id)) {
                $res = $res->where('id', $id);
            } else {
                $res = $res->where('tid', $tid)
                    ->where('shipping_id', $shipping_id)
                    ->where('user_id', $adminru['ru_id'])
                    ->where('id', 0);
            }

            //处理配置信息
            $res = $res->with(['getShipping' => function ($query) {
                $query->select('shipping_id', 'shipping_name', 'shipping_code', 'support_cod');
            }]);

            $row = BaseRepository::getToArrayFirst($res);

            if (!empty($row)) {
                $row['shipping_name'] = '';
                $row['shipping_code'] = '';
                $row['support_cod'] = '';
                if (isset($row['get_shipping']) && !empty($row['get_shipping'])) {
                    $row['shipping_name'] = $row['get_shipping']['shipping_name'];
                    $row['shipping_code'] = $row['get_shipping']['shipping_code'];
                    $row['support_cod'] = $row['get_shipping']['support_cod'];
                }

                if ($row['shipping_code']) {
                    include_once(plugin_path('Shipping/' . StrRepository::studly($row['shipping_code']) . '/config.php'));
                }

                $fields = unserialize($row['configure']);
                /* 如果配送方式支持货到付款并且没有设置货到付款支付费用，则加入货到付款费用 */
                if ($row['support_cod'] && $fields[count($fields) - 1]['name'] != 'pay_fee') {
                    $fields[] = ['name' => 'pay_fee', 'value' => 0];
                }

                foreach ($fields as $key => $val) {
                    /* 替换更改的语言项 */
                    if ($val['name'] == 'basic_fee') {
                        $val['name'] = 'base_fee';
                    }
                    if ($val['name'] == 'item_fee') {
                        $item_fee = 1;
                    }
                    if ($val['name'] == 'fee_compute_mode') {
                        $this->smarty->assign('fee_compute_mode', $val['value']);
                        unset($fields[$key]);
                    } else {
                        $fields[$key]['name'] = $val['name'];
                        $fields[$key]['label'] = $lang[$val['name']];
                    }
                }

                if (empty($item_fee)) {
                    $field = ['name' => 'item_fee', 'value' => '0', 'label' => __('admin::goods_transport.item_fee')];
                    array_unshift($fields, $field);
                }
                $this->smarty->assign('shipping_area', $row);
            } else {
                $res = Shipping::where('shipping_id', $shipping_id);
                $shipping = BaseRepository::getToArrayFirst($res);
                $modules = [];
                if ($shipping['shipping_code']) {
                    $modules = include_once(plugin_path('Shipping/' . StrRepository::studly($shipping['shipping_code']) . '/config.php'));
                }

                $fields = [];
                if ($modules && $modules['configure']) {
                    foreach ($modules['configure'] as $key => $val) {
                        $fields[$key]['name'] = $val['name'];
                        $fields[$key]['value'] = $val['value'];
                        $fields[$key]['label'] = $lang[$val['name']];
                    }
                }

                $count = count($fields);
                $fields[$count]['name'] = "free_money";
                $fields[$count]['value'] = "0";
                $fields[$count]['label'] = __('admin::goods_transport.free_money');

                /* 如果支持货到付款，则允许设置货到付款支付费用 */
                if ($modules && $modules['cod']) {
                    $count++;
                    $fields[$count]['name'] = "pay_fee";
                    $fields[$count]['value'] = "0";
                    $fields[$count]['label'] = __('admin::goods_transport.pay_fee');
                }

                $shipping_area['shipping_id'] = 0;
                $shipping_area['free_money'] = 0;
                $this->smarty->assign('shipping_area', ['shipping_id' => $shipping_id, 'shipping_code' => $shipping['shipping_code']]);
            }
            $this->smarty->assign('fields', $fields);

            $return_data = isset($return_data) ? $return_data : '';

            $this->smarty->assign('return_data', $return_data);
            /* 获得该区域下的所有地区 */
            $regions = [];
            if (!empty($row['region_id'])) {
                $region_id = BaseRepository::getExplode($row['region_id']);
                $res = Region::whereIn('region_id', $region_id);
                $res = BaseRepository::getToArrayGet($res);
                foreach ($res as $arr) {
                    $regions[$arr['region_id']] = $arr['region_name'];
                }
            }

            $this->smarty->assign('shipping_info', shipping_info($shipping_id, ['shipping_name']));
            $Province_list = get_regions(1, 1);
            $this->smarty->assign('Province_list', $Province_list);
            $this->smarty->assign('regions', $regions);
            $this->smarty->assign('tpl_info', $row);
            $this->smarty->assign('tid', $tid);
            $this->smarty->assign('shipping_id', $shipping_id);
            $this->smarty->assign('id', $id);
            $html = $this->smarty->fetch('library/shipping_tab.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 获取地区列表
        /* ------------------------------------------------------ */
        elseif ($act == 'the_national') {
            $tid = request()->get('tid', 0);
            $shipping_id = request()->get('shipping_id', 0);

            $regions = get_the_national();

            $res = GoodsTransportTpl::select('region_id')
                ->where('user_id', $adminru['ru_id'])
                ->where('tid', $tid)
                ->where('shipping_id', $shipping_id);
            $region_list = BaseRepository::getToArrayGet($res);
            $region_list = BaseRepository::getFlatten($region_list);
            $region_list = $region_list ? $region_list : [];

            $res = Region::select('region_id');
            $region = BaseRepository::getToArrayGet($res);
            $region = BaseRepository::getFlatten($region);
            $region = $region ? $region : [];

            $assoc = [];
            if ($region && $region_list) {
                $assoc = array_intersect($region, $region_list);
            }

            if ($assoc) {
                $regions = [];
            }

            $this->smarty->assign('regions', $regions);
            $html = $this->smarty->fetch('library/shipping_the_national.lbi');
            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 添加运费快递模板地区
        /* ------------------------------------------------------ */
        elseif ($act == 'add_shipping_tpl') {
            $result = ['error' => 0, 'message' => ''];
            $regions = request()->get('regions', []);
            $rId = empty($regions) ? '' : implode(',', $regions);
            $tid = request()->get('tid', 0);
            $id = request()->get('id', 0);
            $regionId = $rId;
            $shipping_id = request()->get('shipping_id', 0);

            $ru_id = GoodsTransport::where('tid', $tid)->value('ru_id');
            $ru_id = $ru_id ? $ru_id : 0;

            if (empty($tid)) {
                $ru_id = $adminru['ru_id'];
            }

            $tpl_id = [];
            if ($shipping_id == 0 || empty($regionId)) {
                $result['error'] = 1;
                $result['message'] = __('admin::goods_transport.info_fill_complete');
                return response()->json($result);
            } else {
                $shipping_code = Shipping::where('shipping_id', $shipping_id)->value('shipping_code');
                $shipping_code = $shipping_code ? $shipping_code : '';

                $shipping_code = StrRepository::studly($shipping_code);
                $plugin = plugin_path('Shipping/' . $shipping_code . "/config.php");

                if (!file_exists($plugin)) {
                    $add_to_mess = __('admin::goods_transport.not_find_plugin');
                    $result['error'] = 1;
                    $result['message'] = $add_to_mess;
                    return response()->json($result);
                } else {
                    $modules = include_once($plugin);
                }
                $config = [];

                if ($modules && $modules['configure']) {
                    foreach ($modules['configure'] as $key => $val) {
                        $config[$key]['name'] = $val['name'];
                        $config[$key]['value'] = request()->get($val['name'], '');
                    }
                }

                $count = count($config);
                $config[$count]['name'] = 'free_money';
                $config[$count]['value'] = request()->get('free_money', '');
                $count++;
                $config[$count]['name'] = 'fee_compute_mode';
                $config[$count]['value'] = request()->get('fee_compute_mode', '');
                /* 如果支持货到付款，则允许设置货到付款支付费用 */
                if ($modules['cod']) {
                    $count++;
                    $config[$count]['name'] = 'pay_fee';
                    $config[$count]['value'] = make_semiangle(request()->get('pay_fee', ''));
                }

                $other['tid'] = $tid;
                $other['shipping_id'] = $shipping_id;
                $other['region_id'] = $regionId;
                $other['configure'] = serialize($config);

                if (config('shop.json_field') == 1) {
                    $other['configure_json'] = $config ? json_encode($config) : '';
                }

                $other['user_id'] = $ru_id;
                $other['tpl_name'] = request()->get('tpl_name', '');

                $res = GoodsTransportTpl::where('id', $id)->count();
                if ($res > 0) {
                    GoodsTransportTpl::where('id', $id)->update($other);
                } else {
                    $other['admin_id'] = $admin_id;
                    $other = BaseRepository::recursiveNullVal($other);
                    $tpl_id[] = GoodsTransportTpl::insertGetId($other);
                }
                if ($regionId) {
                    $result['region_list'] = $this->goodsTransportManageService->getAreaList($regionId);
                }
            }

            if ($tpl_id && session()->has($tid . '.tpl_id') && !empty(session($tid . '.tpl_id'))) {
                $tpl_id = array_merge($tpl_id, session($tid . '.tpl_id'));
            }

            session()->put($tid . '.tpl_id', $tpl_id);

            $shipping_tpl = get_transport_shipping_list($tid, $ru_id);
            $this->smarty->assign('shipping_tpl', $shipping_tpl);
            $html = $this->smarty->fetch('library/goods_transport_tpl.lbi');
            $result['content'] = $html;

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 删除运费快递模板配送方式
        /* ------------------------------------------------------ */
        elseif ($act == 'drop_shipping') {
            $result = ['error' => 0, 'message' => ''];

            $tid = request()->get('tid', 0);
            $id = request()->get('id', 0);

            GoodsTransportTpl::where('id', $id)->delete();

            $shipping_tpl = get_transport_shipping_list($tid, $adminru['ru_id']);
            $this->smarty->assign('shipping_tpl', $shipping_tpl);
            $html = $this->smarty->fetch('library/goods_transport_tpl.lbi');
            $result['content'] = $html;

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 查询运费快递模板配送方式地区是否存在
        /* ------------------------------------------------------ */
        elseif ($act == 'select_area') {
            $result = ['error' => 0, 'message' => ''];

            $tid = request()->get('tid', 0);
            $shipping_id = request()->get('shipping_id', 0);
            $region_id = request()->get('region_id', 0);

            $parent_id = region_parent($region_id);
            $region_children = region_children($region_id);

            $region = $region_id . "," . $parent_id . "," . $region_children;
            $region = $this->dscRepository->delStrComma($region);

            $res = GoodsTransportTpl::select('region_id')
                ->where('user_id', $adminru['ru_id'])
                ->where('tid', $tid)
                ->where('shipping_id', $shipping_id);
            $region_list = BaseRepository::getToArrayGet($res);
            $region_list = BaseRepository::getFlatten($region_list);
            $region = !empty($region) ? explode(",", $region) : [];

            $assoc = [];
            if ($region && $region_list) {
                $assoc = array_intersect($region, $region_list);
            }

            if ($assoc) {
                $result['error'] = 1;
            }

            return response()->json($result);
        }
    }
}
