<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\AreaRegion;
use App\Models\Region;
use App\Models\Shipping;
use App\Models\ShippingArea;
use App\Models\ShippingPoint;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Shipping\ShippingManageService;

/**
 * 配送区域管理程序
 */
class ShippingAreaController extends InitController
{
    private $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);
        $adminru = get_admin_ru_id();

        $act = e(request()->input('act', 'list'));

        /*------------------------------------------------------ */
        //-- �        �送区域列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            admin_priv('shiparea_manage');

            $shipping_id = intval(request()->input('shipping', 0));
            $shipping_code = e(request()->input('shipping_code', ''));

            //上门取货
            if (!empty($shipping_id)) {
                $shipping_code = Shipping::where('shipping_id', $shipping_id)->value('shipping_code');
                $shipping_code = $shipping_code ?? '';
            }

            if (!empty($shipping_code)) {
                $shipping_id = Shipping::where('shipping_code', $shipping_code)->value('shipping_id');
                $shipping_id = $shipping_id ?? 0;
            }

            $list = $this->get_shipping_area_list($shipping_id, $adminru['ru_id']);

            /* 自提点名称 */
            if (!empty($list) && $shipping_code == 'cac') {
                foreach ($list as $key => $val) {
                    $name = ShippingPoint::select('name')->where('shipping_area_id', $val['shipping_area_id']);
                    $name = BaseRepository::getToArrayGet($name);

                    $list[$key]['name'] = $name;
                }
            }
            $this->smarty->assign('areas', $list);

            if ($shipping_code == 'cac') {
                $this->smarty->assign('ur_here', trans('admin::common.17_shipping_cac'));
            } else {
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_shipping_list'] . ' - ' . $GLOBALS['_LANG']['shipping_area_list']);
            }

            $this->smarty->assign('action_link', ['href' => 'shipping_area.php?act=add&shipping=' . $shipping_id, 'text' => $GLOBALS['_LANG']['new_area']]);
            $this->smarty->assign('action_link1', ['href' => 'shipping.php?act=list', 'text' => $GLOBALS['_LANG']['area_shipping']]);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('shipping_code', $shipping_code);
            return $this->smarty->display('shipping_area_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 新建送区域
        /*------------------------------------------------------ */

        elseif ($act == 'add' && !empty($_REQUEST['shipping'])) {
            admin_priv('shiparea_manage');

            $shipping_id = isset($_REQUEST['shipping']) && !empty($_REQUEST['shipping']) ? intval($_REQUEST['shipping']) : 0;

            $shipping = Shipping::where('shipping_id', $shipping_id);
            $shipping = BaseRepository::getToArrayFirst($shipping);

            $modules = include_once(plugin_path('Shipping/' . StrRepository::studly($shipping['shipping_code']) . '/config.php'));

            $fields = [];
            if (isset($modules['configure']) && $modules['configure']) {
                foreach ($modules['configure'] as $key => $val) {
                    $fields[$key]['name'] = $val['name'];
                    $fields[$key]['value'] = $val['value'];
                    $fields[$key]['label'] = $GLOBALS['_LANG'][$val['name']];
                }
            }

            $count = count($fields);
            $fields[$count]['name'] = "free_money";
            $fields[$count]['value'] = "0";
            $fields[$count]['label'] = $GLOBALS['_LANG']["free_money"];

            /* 如果支持货到付款，则允许设置货到付款支付费用 */
            if ($modules['cod']) {
                $count++;
                $fields[$count]['name'] = "pay_fee";
                $fields[$count]['value'] = "0";
                $fields[$count]['label'] = $GLOBALS['_LANG']['pay_fee'];
            }

            $shipping_area['shipping_id'] = 0;
            $shipping_area['free_money'] = 0;

            $this->smarty->assign('ur_here', $shipping['shipping_name'] . ' - ' . $GLOBALS['_LANG']['new_area']);
            $this->smarty->assign('shipping_area', ['shipping_id' => $shipping_id, 'shipping_code' => $shipping['shipping_code']]);
            $this->smarty->assign('fields', $fields);
            $this->smarty->assign('form_action', 'insert');
            $this->smarty->assign('countries', get_regions());
            $this->smarty->assign('province_all', get_regions(1, 1));
            $this->smarty->assign('default_country', $GLOBALS['_CFG']['shop_country']);

            return $this->smarty->display('shipping_area_info.dwt');
        } elseif ($act == 'insert') {
            admin_priv('shiparea_manage');

            $shipping_id = isset($_POST['shipping']) && !empty($_POST['shipping']) ? intval($_POST['shipping']) : 0;
            $shipping_area_name = isset($_POST['shipping_area_name']) && !empty($_POST['shipping_area_name']) ? addslashes(trim($_POST['shipping_area_name'])) : '';

            /* 检查同类型的配送方式下有没有重名的配送区域 */
            $count = ShippingArea::where('shipping_id', $shipping_id)
                ->where('shipping_area_name', $shipping_area_name)
                ->where('ru_id', $adminru['ru_id'])
                ->count();

            if ($count > 0) {
                return sys_msg($GLOBALS['_LANG']['repeat_area_name'], 1);
            } else {
                $shipping_code = Shipping::where('shipping_id', $shipping_id)->value('shipping_code');
                $shipping_code = $shipping_code ? $shipping_code : '';

                $shipping_name = StrRepository::studly($shipping_code);
                $plugin = plugin_path('Shipping/' . $shipping_name . '/config.php');

                if (!file_exists($plugin)) {
                    return sys_msg($GLOBALS['_LANG']['not_find_plugin'], 1);
                } else {
                    $modules = include_once($plugin);
                }

                $config = [];
                if ($modules) {
                    foreach ($modules['configure'] as $key => $val) {
                        $config[$key]['name'] = $val['name'];
                        $config[$key]['value'] = $_POST[$val['name']];
                    }
                }

                $count = count($config);
                $config[$count]['name'] = 'free_money';
                $config[$count]['value'] = empty($_POST['free_money']) ? '' : $_POST['free_money'];
                $count++;
                $config[$count]['name'] = 'fee_compute_mode';
                $config[$count]['value'] = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];
                /* 如果支持货到付款，则允许设置货到付款支付费用 */
                if (isset($modules['cod']) && $modules['cod']) {
                    $count++;
                    $config[$count]['name'] = 'pay_fee';
                    $config[$count]['value'] = make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
                }

                $other = [
                    'shipping_area_name' => $shipping_area_name,
                    'shipping_id' => $shipping_id,
                    'configure' => serialize($config),
                    'ru_id' => $adminru['ru_id']
                ];
                $new_id = ShippingArea::insertGetId($other);

                if ($shipping_code == "cac") {
                    //上门取货添加所辖区域
                    $district = isset($_POST['district']) ? intval($_POST['district']) : 0;

                    if ($district == 0) {
                        return sys_msg($GLOBALS['_LANG']['select_jurisdiction_region'], 1);
                    }

                    $other = [
                        'shipping_area_id' => $new_id,
                        'region_id' => $district
                    ];
                    AreaRegion::insert($other);
                } else {
                    /* 添加选定的城市和地区 */
                    if (isset($_POST['regions']) && is_array($_POST['regions'])) {
                        foreach ($_POST['regions'] as $key => $val) {
                            $other = [
                                'shipping_area_id' => $new_id,
                                'region_id' => $val,
                                'ru_id' => $adminru['ru_id']
                            ];
                            AreaRegion::insert($other);
                        }
                    }
                }

                /* 自提点名称，地址，电话 */
                $point_name = isset($_POST['point_name']) ? $_POST['point_name'] : [];
                $user_name = isset($_POST['user_name']) ? $_POST['user_name'] : [];
                $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : [];
                $address = isset($_POST['address']) ? $_POST['address'] : [];
                $anchor = isset($_POST['anchor']) ? $_POST['anchor'] : [];
                $line = isset($_POST['line']) ? $_POST['line'] : [];

                if ($point_name) {
                    foreach ($point_name as $key => $val) {
                        if (empty($val)) {
                            continue;
                        }

                        $upload = [
                            'name' => $_FILES['img_url']['name'][$key],
                            'type' => $_FILES['img_url']['type'][$key],
                            'tmp_name' => $_FILES['img_url']['tmp_name'][$key],
                            'size' => $_FILES['img_url']['size'][$key],
                        ];
                        if (isset($_FILES['img_url']['error'])) {
                            $upload['error'] = $_FILES['img_url']['error'][$key];
                        }

                        $map_img = $image->upload_image($upload, 'map_img');

                        $other = [
                            'shipping_area_id' => $new_id,
                            'name' => $val,
                            'user_name' => $user_name[$key] ?? '',
                            'mobile' => $mobile[$key] ?? '',
                            'address' => $address[$key] ?? '',
                            'img_url' => $map_img,
                            'anchor' => $anchor[$key] ?? '',
                            'line' => $line[$key] ?? ''
                        ];
                        ShippingPoint::insert($other);
                    }

                    admin_log($_POST['shipping_area_name'], 'add', 'shipping_area');
                }

                $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'shipping_area.php?act=list&shipping=' . $shipping_id];
                $lnk[] = ['text' => $GLOBALS['_LANG']['add_continue'], 'href' => 'shipping_area.php?act=add&shipping=' . $shipping_id];
                return sys_msg($GLOBALS['_LANG']['add_area_success'], 0, $lnk, true, true);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑�        �送区域
        /*------------------------------------------------------ */

        elseif ($act == 'edit') {
            admin_priv('shiparea_manage');

            $shipping_area_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            $row = ShippingArea::where('shipping_area_id', $shipping_area_id)
                ->where('ru_id', $adminru['ru_id']);
            $row = $row->with([
                'getShipping' => function ($query) {
                    $query->select('shipping_id', 'shipping_name', 'shipping_code', 'support_cod');
                }
            ]);

            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {
                $row = BaseRepository::getArrayCollapse([$row, $row['get_shipping']]);
                $row = BaseRepository::getArrayExcept($row, 'get_shipping');
            }

            /* 自提点信息 */
            if (!empty($row) && $row['shipping_code'] == "cac") {
                $point = ShippingPoint::where('shipping_area_id', $row['shipping_area_id']);
                $point = BaseRepository::getToArrayGet($point);

                $row['point'] = $point;
                $row['count_point'] = count($row['point']);

                //格式化地图图片
                foreach ($row['point'] as $key => $val) {
                    $row['point'][$key]['img_url'] = $this->dscRepository->getImagePath($val['img_url']);
                }
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
                    $fields[$key]['label'] = $GLOBALS['_LANG'][$val['name']];
                }
            }

            if (empty($item_fee)) {
                $field = ['name' => 'item_fee', 'value' => '0', 'label' => empty($GLOBALS['_LANG']['item_fee']) ? '' : $GLOBALS['_LANG']['item_fee']];
                array_unshift($fields, $field);
            }

            /* 获得该区域下的所有地区 */
            $res = AreaRegion::where('shipping_area_id', $shipping_area_id)
                ->where('ru_id', $adminru['ru_id']);

            $res = $res->with([
                'getRegion' => function ($query) {
                    $query->select('region_id', 'region_name');
                }
            ]);

            $res = BaseRepository::getToArrayGet($res);

            $regions = [];
            if ($res) {
                foreach ($res as $arr) {
                    $regions[$arr['region_id']] = $arr['get_region']['region_name'] ?? '';
                }
            }

            //省份城市区域---上门取货
            $region_id = AreaRegion::where('shipping_area_id', $shipping_area_id)->value('region_id');
            $region_id = $region_id ? $region_id : 0;

            $district = [];
            $district_all = [];
            $city = [];
            $city_all = [];
            $province = [];
            $province_all = [];
            if ($region_id && $row['shipping_code'] == "cac") {
                //区域
                $district = Region::where('region_id', $region_id);
                $district = BaseRepository::getToArrayFirst($district);
                $district_parent_id = $district['parent_id'] ?? 0;

                $district_all = Region::where('parent_id', $district_parent_id);
                $district_all = BaseRepository::getToArrayGet($district_all);

                //城市
                $city = Region::where('region_id', $district_parent_id);
                $city = BaseRepository::getToArrayFirst($city);
                $city_parent_id = $city['parent_id'] ?? 0;

                $city_all = Region::where('parent_id', $city_parent_id);
                $city_all = BaseRepository::getToArrayGet($city_all);

                //省份
                $province = Region::where('region_id', $city_parent_id);
                $province = BaseRepository::getToArrayFirst($province);
                $province_parent_id = $province['parent_id'] ?? 0;

                $province_all = Region::where('parent_id', $province_parent_id);
                $province_all = BaseRepository::getToArrayGet($province_all);
            }

            $this->smarty->assign('ur_here', $row['shipping_name'] . ' - ' . $GLOBALS['_LANG']['edit_area']);
            $this->smarty->assign('id', $_REQUEST['id']);
            $this->smarty->assign('fields', $fields);
            $this->smarty->assign('shipping_area', $row);
            $this->smarty->assign('regions', $regions);
            $this->smarty->assign('form_action', 'update');
            $this->smarty->assign('countries', get_regions());
            $this->smarty->assign('district', $district);
            $this->smarty->assign('district_all', $district_all);
            $this->smarty->assign('city', $city);
            $this->smarty->assign('city_all', $city_all);
            $this->smarty->assign('province', $province);
            $this->smarty->assign('province_all', $province_all);
            $this->smarty->assign('default_country', 1);
            return $this->smarty->display('shipping_area_info.dwt');
        } elseif ($act == 'update') {
            admin_priv('shiparea_manage');

            $shipping_area_name = isset($_POST['shipping_area_name']) && !empty($_POST['shipping_area_name']) ? addslashes($_POST['shipping_area_name']) : '';
            $shipping_area_id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0;
            $shipping_id = isset($_POST['shipping']) && !empty($_POST['shipping']) ? intval($_POST['shipping']) : 0;

            /* 检查同类型的配送方式下有没有重名的配送区域 */
            $count = ShippingArea::where('shipping_id', $shipping_id)
                ->where('shipping_area_name', $shipping_area_name)
                ->where('shipping_area_id', '<>', $shipping_area_id)
                ->where('ru_id', $adminru['ru_id'])
                ->count();

            if ($count > 0) {
                return sys_msg($GLOBALS['_LANG']['repeat_area_name'], 1);
            } else {
                $shipping_code = Shipping::where('shipping_id', $shipping_id)->value('shipping_code');
                $shipping_code = $shipping_code ? $shipping_code : '';

                $shipping_name = StrRepository::studly($shipping_code);
                $plugin = plugin_path('Shipping/' . $shipping_name . '/config.php');

                if (!file_exists($plugin)) {
                    return sys_msg($GLOBALS['_LANG']['not_find_plugin'], 1);
                } else {
                    $modules = include_once($plugin);
                }

                $config = [];
                if ($modules) {
                    foreach ($modules['configure'] as $key => $val) {
                        $config[$key]['name'] = $val['name'];
                        $config[$key]['value'] = $_POST[$val['name']];
                    }
                }

                $count = count($config);
                $config[$count]['name'] = 'free_money';
                $config[$count]['value'] = empty($_POST['free_money']) ? '' : $_POST['free_money'];
                $count++;
                $config[$count]['name'] = 'fee_compute_mode';
                $config[$count]['value'] = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];
                if (isset($modules['cod']) && $modules['cod']) {
                    $count++;
                    $config[$count]['name'] = 'pay_fee';
                    $config[$count]['value'] = make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
                }

                $other = [
                    'shipping_area_name' => $shipping_area_name,
                    'configure' => serialize($config)
                ];
                ShippingArea::where('shipping_area_id', $shipping_area_id)
                    ->where('ru_id', $adminru['ru_id'])
                    ->update($other);

                /* 自提点名称，地址，电话 */
                $point_id = isset($_POST['point_id']) && !empty($_POST['point_id']) ? addslashes_deep($_POST['point_id']) : [];
                $point_name = isset($_POST['point_name']) ? $_POST['point_name'] : [];
                $user_name = isset($_POST['user_name']) ? $_POST['user_name'] : [];
                $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : [];
                $address = isset($_POST['address']) ? $_POST['address'] : [];
                $old_map_img = isset($_POST['map_img']) ? $_POST['map_img'] : [];
                $anchor = isset($_POST['anchor']) ? $_POST['anchor'] : [];
                $line = isset($_POST['line']) ? $_POST['line'] : [];

                if ($point_name && $mobile && $address) {
                    foreach ($point_name as $key => $val) {
                        if (empty($val)) {
                            continue;
                        }

                        $upload = [
                            'name' => $_FILES['img_url']['name'][$key],
                            'type' => $_FILES['img_url']['type'][$key],
                            'tmp_name' => $_FILES['img_url']['tmp_name'][$key],
                            'size' => $_FILES['img_url']['size'][$key],
                        ];
                        if (isset($_FILES['img_url']['error'])) {
                            $upload['error'] = $_FILES['img_url']['error'][$key];
                        }

                        $map_img = $image->upload_image($upload, 'map_img');
                        if (!$map_img && $old_map_img[$key]) {
                            $map_img = $old_map_img[$key];
                        }
                        if ($_POST['point_id'][$key]) {
                            $other = [
                                'name' => $point_name[$key],
                                'user_name' => $user_name[$key],
                                'mobile' => $mobile[$key],
                                'address' => $address[$key],
                                'img_url' => $map_img,
                                'anchor' => $anchor[$key],
                                'line' => $line[$key]
                            ];
                            ShippingPoint::where('id', $point_id[$key])
                                ->update($other);
                        } else {
                            $other = [
                                'shipping_area_id' => $shipping_area_id,
                                'name' => $point_name[$key],
                                'user_name' => $user_name[$key],
                                'mobile' => $mobile[$key],
                                'address' => $address[$key],
                                'img_url' => $map_img,
                                'anchor' => $anchor[$key],
                                'line' => $line[$key]
                            ];
                            ShippingPoint::insert($other);
                        }
                    }
                }

                admin_log($_POST['shipping_area_name'], 'edit', 'shipping_area');

                /* 过滤掉重复的region */
                $selected_regions = [];
                if (isset($_POST['regions'])) {
                    foreach ($_POST['regions'] as $region_id) {
                        $selected_regions[$region_id] = $region_id;
                    }
                }

                // 查询所有区域 region_id => parent_id
                $sql = "SELECT region_id, parent_id FROM " . $this->dsc->table('region');
                $res = $this->db->query($sql);

                $region_list = [];
                foreach ($res as $row) {
                    $region_list[$row['region_id']] = $row['parent_id'];
                }

                // 过滤掉上级存在的区域
                foreach ($selected_regions as $region_id) {
                    $id = $region_id;
                    if ($region_list) {
                        while ($region_list[$id] != 0) {
                            $id = $region_list[$id];
                            if (isset($selected_regions[$id])) {
                                unset($selected_regions[$region_id]);
                                break;
                            }
                        }
                    }
                }

                /* 清除原有的城市和地区 */
                AreaRegion::where('shipping_area_id', $shipping_area_id)->where('ru_id', $adminru['ru_id'])->delete();

                if ($shipping_code == "cac") {
                    //上门取货添加所辖区域
                    $district = isset($_POST['district']) ? intval($_POST['district']) : 0;

                    if ($district == 0) {
                        return sys_msg(lang('admin/shipping_area.selection_area'), 1);
                    }

                    $other = [
                        'shipping_area_id' => $shipping_area_id,
                        'region_id' => $district,
                        'ru_id' => $adminru['ru_id']
                    ];
                    AreaRegion::insert($other);
                } else {
                    /* 添加选定的城市和地区 */
                    if ($selected_regions) {
                        foreach ($selected_regions as $key => $val) {
                            $other = [
                                'shipping_area_id' => $shipping_area_id,
                                'region_id' => $val,
                                'ru_id' => $adminru['ru_id']
                            ];
                            AreaRegion::insert($other);
                        }
                    }
                }

                $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'shipping_area.php?act=list&shipping=' . $shipping_id];

                return sys_msg($GLOBALS['_LANG']['edit_area_success'], 0, $lnk, true, true);
            }
        }

        /* ------------------------------------------------------ */
        //-- 上门取货设置 安装或卸载
        /* ------------------------------------------------------ */
        elseif ($act == 'setting') {

            $shipping_code = e(request()->input('shipping_code', 'cac'));

            // 提交
            if (request()->isMethod('POST')) {
                $enabled = intval(request()->input('enabled', 0)); // 0 卸载 1 安装

                if ($enabled == 1) {
                    $res = ShippingManageService::installShipping($shipping_code);
                } else {
                    /* 获得该配送方式的ID */
                    $row = Shipping::where('shipping_code', $shipping_code);
                    $row = BaseRepository::getToArrayFirst($row);

                    $shipping_id = $row['shipping_id'] ?? 0;

                    $res = ShippingManageService::uninstallShipping($shipping_id, $row);
                }

                return response()->json(['error' => 0, 'msg' => 'success']);
            }

            $this->assign('ur_here', trans('admin::common.17_shipping_cac'));

            // 是否安装上门取货
            $shipping = Shipping::where('shipping_code', $shipping_code)->select('shipping_name', 'enabled')->first();
            $shipping = $shipping ? $shipping->toArray() : [];

            $enabled = $shipping['enabled'] ?? 0;
            $this->assign('enabled', $enabled);
            return $this->display('admin.shipping_area.shipping_setting');
        }

        /*------------------------------------------------------ */
        //-- 批量删除�        �送区域
        /*------------------------------------------------------ */
        elseif ($act == 'multi_remove') {
            admin_priv('shiparea_manage');

            if (isset($_POST['checkboxes']) && count($_POST['checkboxes']) > 0) {
                $i = 0;
                foreach ($_POST['checkboxes'] as $v) {
                    ShippingArea::where('shipping_area_id', $v)
                        ->where('ru_id', $adminru['ru_id'])
                        ->delete();

                    $i++;
                }

                /* 记录管理员操作 */
                admin_log('', 'batch_remove', 'shipping_area');
            }
            /* 返回 */
            $links[0] = ['href' => 'shipping_area.php?act=list&shipping=' . intval($_REQUEST['shipping']), 'text' => $GLOBALS['_LANG']['go_back']];
            return sys_msg($GLOBALS['_LANG']['remove_success'], 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 编辑�        �送区域名称
        /*------------------------------------------------------ */

        elseif ($act == 'edit_area') {
            /* 检查权限 */
            $check_auth = check_authz_json('shiparea_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $id = intval($_POST['id']);
            $val = json_str_iconv(trim($_POST['val']));

            /* 取得该区域所属的配送id */
            $shipping_id = ShippingArea::where('shipping_area_id', $id)->value('shipping_id');
            $shipping_id = $shipping_id ? $shipping_id : 0;

            /* 检查是否有重复的配送区域名称 */
            $count = ShippingArea::where('shipping_area_name', $val)
                ->where('shipping_area_id', '<>', $id)
                ->where('shipping_id', $shipping_id)
                ->where('ru_id', $adminru['ru_id'])
                ->count();

            if ($count > 0) {
                return make_json_error($GLOBALS['_LANG']['repeat_area_name']);
            }

            /* 更新名称 */
            ShippingArea::where('shipping_area_id', $id)->update([
                'shipping_area_name' => $val
            ]);

            /* 记录日志 */
            admin_log($val, 'edit', 'shipping_area');

            /* 返回 */
            return make_json_result(stripcslashes($val));
        }

        /*------------------------------------------------------ */
        //-- 删除配送区域
        /*------------------------------------------------------ */
        elseif ($act == 'remove_area') {
            $check_auth = check_authz_json('shiparea_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            $shippingArea = ShippingArea::where('shipping_area_id', $id);
            $shippingArea = BaseRepository::getToArrayFirst($shippingArea);
            $name = $shippingArea['shipping_area_name'] ?? '';
            $shipping_id = $shippingArea['shipping_id'] ?? 0;

            ShippingArea::where('shipping_area_id', $id)->where('ru_id', $adminru['ru_id'])->delete();
            AreaRegion::where('shipping_area_id', $id)->where('ru_id', $adminru['ru_id'])->delete();

            admin_log($name, 'remove', 'shipping_area');

            //上门取货
            $shipping_code = Shipping::where('shipping_id', $shipping_id)->value('shipping_code');
            $shipping_code = $shipping_code ? $shipping_code : '';

            $list = $this->get_shipping_area_list($shipping_id, $adminru['ru_id']);

            /* 自提点名称 */
            if (!empty($list) && $shipping_code == "cac") {
                foreach ($list as $key => $val) {
                    $name = ShippingPoint::where('shipping_area_id', $val['shipping_area_id']);
                    $name = BaseRepository::getToArrayGet($name);

                    $list[$key]['name'] = $name;
                }
            }

            $this->smarty->assign('areas', $list);
            $this->smarty->assign('shipping_code', $shipping_code);
            return make_json_result($this->smarty->fetch('shipping_area_list.dwt'));
        }

        /*------------------------------------------------------ */
        //-- 删除自提点
        /*------------------------------------------------------ */

        elseif ($act == 'remove_point') {
            $check_auth = check_authz_json('shiparea_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }


            $id = intval($_GET['id']);

            $name = ShippingArea::where('shipping_area_id', $id)->value('shipping_area_name');

            $res = ShippingPoint::where('id', $id)->delete();

            if ($res) {
                $data = ['error' => 2, 'message' => $GLOBALS['_LANG']['carddrop_succeed'], 'content' => ''];
                admin_log($name, 'remove', 'shipping_area');
            } else {
                $data = ['error' => 0, 'message' => $GLOBALS['_LANG']['carddrop_fail'], 'content' => ''];
            }

            return response()->json($data);
        }
    }

    /**
     * 取得配送区域列表
     *
     * @param int $shipping_id
     * @param int $ru_id
     * @return array
     */
    private function get_shipping_area_list($shipping_id = 0, $ru_id = 0)
    {
        $res = ShippingArea::where('ru_id', $ru_id);

        if ($shipping_id > 0) {
            $res = $res->where('shipping_id', $shipping_id);
        }

        $res = $res->with([
            'getAreaRegionList' => function ($query) {
                $query->with([
                    'getRegion'
                ]);
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            foreach ($res as $row) {
                $regionList = $row['get_area_region_list'] ?? [];

                $regions = [];
                if ($regionList) {
                    foreach ($regionList as $key => $val) {
                        $regions[$key]['region_name'] = $val['get_region']['region_name'] ?? '';
                    }
                }

                $regions = BaseRepository::getKeyPluck($regions, 'region_name');
                $regions = $regions ? array_unique($regions) : [];
                $regions = $regions ? implode(',', $regions) : '';

                $row['shipping_area_regions'] = empty($regions) ?
                    '<a href="shipping_area.php?act=edit&amp;id=' . $row['shipping_area_id'] .
                    '" style="color:red">' . $GLOBALS['_LANG']['empty_regions'] . '</a>' : $regions;
                $list[] = $row;
            }
        }

        return $list;
    }
}
