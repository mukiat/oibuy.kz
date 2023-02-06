<?php

namespace App\Modules\Admin\Controllers;

use App\Models\KdniaoCustomerAccount;
use App\Models\KdniaoEorderConfig;
use App\Models\Region;
use App\Models\Shipping;
use App\Models\ShippingDate;
use App\Models\ShippingTpl;
use App\Plugins\TpApi\Kdniao;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Shipping\ShippingManageService;
use App\Services\Store\StoreService;

/**
 * 配送方式管理程序
 */
class ShippingController extends InitController
{
    protected $storeService;
    protected $dscRepository;

    public function __construct(
        StoreService $storeService,
        DscRepository $dscRepository
    )
    {
        $this->storeService = $storeService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $adminru = get_admin_ru_id();

        $act = e(request()->input('act', 'list'));

        /*------------------------------------------------------ */
        //-- 配送方式列表
        /*------------------------------------------------------ */

        if ($act == 'list') {
            $this->smarty->assign('menu_select', ['action' => '01_system', 'current' => '03_shipping_list']);

            $modules = $this->dscRepository->readModules(plugin_path('Shipping'));

            foreach ($modules as $i => $module) {

                /* 检查该插件是否已经安装 */
                $row = Shipping::where('shipping_code', $modules[$i]['code'])->orderBy('shipping_order');
                $row = BaseRepository::getToArrayFirst($row);

                if ($row) {
                    /* 插件已经安装了，获得名称以及描述 */
                    $modules[$i]['id'] = $row['shipping_id'];
                    $modules[$i]['name'] = $row['shipping_name'];
                    $modules[$i]['desc'] = $row['shipping_desc'];
                    $modules[$i]['insure_fee'] = $row['insure'];
                    $modules[$i]['cod'] = $row['support_cod'];
                    $modules[$i]['shipping_order'] = $row['shipping_order'];
                    $modules[$i]['install'] = 1;

                    if (isset($modules[$i]['insure']) && ($modules[$i]['insure'] === false)) {
                        $modules[$i]['is_insure'] = 0;
                    } else {
                        $modules[$i]['is_insure'] = 1;
                    }
                } else {
                    $modules[$i]['name'] = $GLOBALS['_LANG'][$modules[$i]['code']];
                    $modules[$i]['desc'] = $GLOBALS['_LANG'][$modules[$i]['desc']];
                    $modules[$i]['insure_fee'] = empty($modules[$i]['insure']) ? 0 : $modules[$i]['insure'];
                    $modules[$i]['shipping_order'] = 0;
                    $modules[$i]['install'] = 0;
                }

                // 不显示上门自提，去上门自提设置里启用
                if ($modules[$i]['code'] === 'cac') {
                    unset($modules[$i]);
                }
            }

            /* 获取商家设置的配送方式 */
            $seller_shopinfo = $this->storeService->getShopInfo($adminru['ru_id']);

            if (!$seller_shopinfo && $adminru['ru_id']) {
                $modules = [];
            }

            $this->smarty->assign('ru_id', $adminru['ru_id']); //商家id by wu
            $this->smarty->assign('seller_shopinfo', $seller_shopinfo); //商家信息 by wu
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_shipping_list']);

            $date = array_column($modules, 'shipping_order');

            array_multisort($date, SORT_ASC, $modules);
            $this->smarty->assign('modules', $modules);

            return $this->smarty->display('shipping_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 配送时间列表
        /*------------------------------------------------------ */
        elseif ($act == 'date_list') {
            admin_priv('shipping_date_list');

            $this->smarty->assign('menu_select', ['action' => '01_system', 'current' => 'shipping_date_list']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['self_lift_time']);
            $this->smarty->assign('action_link', ['href' => 'shipping.php?act=date_add', 'text' => $GLOBALS['_LANG']['add_self_lift_time']]);

            $shipping_date = $this->shipping_date_list();

            $this->smarty->assign('shipping_date', $shipping_date);


            return $this->smarty->display('shipping_date_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加配送时间
        /*------------------------------------------------------ */
        elseif ($act == 'date_add') {
            admin_priv('shipping_date_message');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_self_lift_time']);
            $this->smarty->assign('action_link', ['href' => 'shipping.php?act=date_list', 'text' => $GLOBALS['_LANG']['self_lift_time_list']]);
            $this->smarty->assign('act', 'date_insert');


            return $this->smarty->display('shipping_date_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 插入配送时间
        /*------------------------------------------------------ */
        elseif ($act == 'date_insert') {
            admin_priv('shipping_date_message');

            $shipping_date_start = empty($_POST['shipping_date_start']) ? '0:00' : $_POST['shipping_date_start'];
            $shipping_date_end = empty($_POST['shipping_date_end']) ? '0:00' : $_POST['shipping_date_end'];
            $later_day = empty($_POST['later_day']) ? '0' : $_POST['later_day'];

            $other = [
                'start_date' => $shipping_date_start,
                'end_date' => $shipping_date_end,
                'select_day' => $later_day
            ];
            $id = ShippingDate::insertGetId($other);

            if (!empty($id)) {
                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['back_continue_add'];
                $link[0]['href'] = 'shipping.php?act=date_add';

                $link[1]['text'] = $GLOBALS['_LANG']['self_lift_time_list'];
                $link[1]['href'] = 'shipping.php?act=date_list';

                return sys_msg($GLOBALS['_LANG']['add_success'], 0, $link);
            } else {
                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['back_again_add'];
                $link[0]['href'] = 'javascript:history.back(-1)';

                $link[1]['text'] = $GLOBALS['_LANG']['self_lift_time_list'];
                $link[1]['href'] = 'shipping.php?act=date_list';

                return sys_msg($GLOBALS['_LANG']['add_success'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑配送时间
        /*------------------------------------------------------ */
        elseif ($act == 'date_edit') {
            admin_priv('shipping_date_message');

            $shipping_id = empty($_REQUEST['sid']) ? '0' : $_REQUEST['sid'];

            if (empty($shipping_id)) {
                return dsc_header("location: shipping.php?act=date_list\n");
            }

            $shipping_date = ShippingDate::where('shipping_date_id', $shipping_id);
            $shipping_date = BaseRepository::getToArrayFirst($shipping_date);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_self_lift_time']);
            $this->smarty->assign('action_link', ['href' => 'shipping.php?act=date_list', 'text' => $GLOBALS['_LANG']['self_lift_time_list']]);
            $this->smarty->assign('act', 'date_update');
            $this->smarty->assign('id', $shipping_id);
            $this->smarty->assign('shipping_date', $shipping_date);

            return $this->smarty->display('shipping_date_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新配送时间
        /*------------------------------------------------------ */
        elseif ($act == 'date_update') {
            admin_priv('shipping_date_message');

            $shipping_date_start = empty($_POST['shipping_date_start']) ? '0:00' : $_POST['shipping_date_start'];
            $shipping_date_end = empty($_POST['shipping_date_end']) ? '0:00' : $_POST['shipping_date_end'];
            $later_day = empty($_POST['later_day']) ? 0 : $_POST['later_day'];
            $shipping_id = empty($_POST['id']) ? 0 : intval($_POST['id']);

            if (empty($shipping_id)) {
                return dsc_header("location: shipping.php?act=date_list\n");
            }

            $other = [
                'start_date' => $shipping_date_start,
                'end_date' => $shipping_date_end,
                'select_day' => $later_day
            ];
            ShippingDate::where('shipping_date_id', $shipping_id)
                ->update($other);

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_again_edit'];
            $link[0]['href'] = 'javascript:history.back(-1)';

            $link[1]['text'] = $GLOBALS['_LANG']['back_list_page'];
            $link[1]['href'] = 'shipping.php?act=date_list';

            return sys_msg($GLOBALS['_LANG']['add_success'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除配送时间
        /*------------------------------------------------------ */
        elseif ($act == 'date_remove') {
            admin_priv('shipping_date_message');

            $shipping_id = empty($_REQUEST['sid']) ? '0' : intval($_REQUEST['sid']);

            if (empty($shipping_id)) {
                return dsc_header("location: shipping.php?act=date_list\n");
            }

            $delete = ShippingDate::where('shipping_date_id', $shipping_id)->delete();

            if ($delete) {
                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['back_list_page'];
                $link[0]['href'] = 'shipping.php?act=date_list';

                return sys_msg($GLOBALS['_LANG']['carddrop_succeed'], 0, $link);
            } else {
                $link[0]['text'] = $GLOBALS['_LANG']['back_list_page'];
                $link[0]['href'] = 'shipping.php?act=date_list';

                return sys_msg($GLOBALS['_LANG']['carddrop_fail'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 安装配送方式
        /*------------------------------------------------------ */

        elseif ($act == 'install') {
            admin_priv('ship_manage');

            $code = request()->input('code', '');

            $res = ShippingManageService::installShipping($code);
            if ($res) {
                /* 记录管理员操作 */
                admin_log(addslashes($GLOBALS['_LANG'][$code]), 'install', 'shipping');

                /* 提示信息 */
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'shipping.php?act=list'];
                return sys_msg(sprintf($GLOBALS['_LANG']['install_succeess'], $GLOBALS['_LANG'][$code]), 0, $lnk);
            }

            return sys_msg($GLOBALS['_LANG']['add_fail'], 1);
        }

        /*------------------------------------------------------ */
        //-- 卸载配送方式
        /*------------------------------------------------------ */
        elseif ($act == 'uninstall') {
            admin_priv('ship_manage');

            $code = request()->input('code', '');

            /* 获得该配送方式的ID */
            $row = Shipping::where('shipping_code', $code);
            $row = BaseRepository::getToArrayFirst($row);

            $shipping_id = $row['shipping_id'] ?? 0;
            $shipping_name = $row['shipping_name'] ?? '';

            /* 删除 shipping_fee 以及 shipping 表中的数据 */
            $res = ShippingManageService::uninstallShipping($shipping_id, $row);

            if ($res) {
                //记录管理员操作
                admin_log(addslashes($shipping_name), 'uninstall', 'shipping');

                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'shipping.php?act=list'];
                return sys_msg(sprintf($GLOBALS['_LANG']['uninstall_success'], $shipping_name), 0, $lnk);
            }

            return sys_msg($GLOBALS['_LANG']['add_fail'], 1);
        }

        /*------------------------------------------------------ */
        //-- 模板Flash编辑器
        /*------------------------------------------------------ */

        elseif ($act == 'print_index') {
            //检查登录权限
            admin_priv('ship_manage');

            $shipping_id = request()->input('shipping', 0);

            /* 检查该插件是否已经安装 取值 */
            $row = Shipping::where('shipping_id', $shipping_id);
            $row = BaseRepository::getToArrayFirst($row);

            $ship_tpl = [];
            if ($row) {
                $ship_tpl = ShippingTpl::where('shipping_id', $shipping_id)->where('ru_id', $adminru['ru_id']);
                $ship_tpl = BaseRepository::getToArrayFirst($ship_tpl);

                if ($ship_tpl) {
                    $ship_tpl['shipping_print'] = !empty($ship_tpl['shipping_print']) ? $ship_tpl['shipping_print'] : '';
                    $ship_tpl['print_bg'] = empty($ship_tpl['print_bg']) ? '' : $this->dscRepository->getImagePath($ship_tpl['print_bg']);
                }
            }

            $this->smarty->assign('shipping', $ship_tpl);
            $this->smarty->assign('shipping_id', $shipping_id);
            $this->smarty->assign('pint_url', $this->dscRepository->getImagePath('data/print/pint.swf'));

            return $this->smarty->display('print_index.dwt');
        }

        /*------------------------------------------------------ */
        //-- 模板Flash编辑器
        /*------------------------------------------------------ */

        elseif ($act == 'recovery_default_template') {
            /* 检查登录权限 */
            admin_priv('ship_manage');

            $shipping_id = request()->input('shipping', 0);

            /* 取配送代码 */
            $code = Shipping::where('shipping_id', $shipping_id)->value('shipping_code');
            $code = $code ? $code : '';

            if ($code) {
                $shipping_name = StrRepository::studly($code);
                $modules = plugin_path('Shipping/' . $shipping_name . '/config.php');

                if (file_exists($modules)) {
                    $modules = include_once($modules);

                    /* 恢复默认 */
                    $other = [
                        'print_bg' => addslashes($modules['print_bg']),
                        'config_lable' => addslashes($modules['config_lable'])
                    ];
                    ShippingTpl::where('shipping_id', $shipping_id)
                        ->where('ru_id', $adminru['ru_id'])
                        ->update($other);
                }
            }

            $url = "shipping.php?act=edit_print_template&shipping=$shipping_id";
            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 模板Flash编辑器 上传图片
        /*------------------------------------------------------ */

        elseif ($act == 'print_upload') {
            //检查登录权限
            admin_priv('ship_manage');

            //设置上传文件类型
            $allow_suffix = ['jpg', 'png', 'jpeg'];

            $shipping_id = request()->input('shipping', 0);

            $src = '';
            //接收上传文件
            if (!empty($_FILES['bg']['name'])) {
                if (!get_file_suffix($_FILES['bg']['name'], $allow_suffix)) {
                    echo '<script language="javascript">';
                    echo 'parent.alert("' . sprintf($GLOBALS['_LANG']['js_languages']['upload_falid'], implode(',', $allow_suffix)) . '");';
                    echo '</script>';
                }

                $name = TimeRepository::getLocalDate('Ymd');
                for ($i = 0; $i < 6; $i++) {
                    $name .= chr(mt_rand(97, 122));
                }
                $bg_name = explode('.', $_FILES['bg']['name']);
                $name .= '.' . end($bg_name);
                $target = storage_public('images/receipt/' . $name);

                if (move_upload_file($_FILES['bg']['tmp_name'], $target)) {
                    $src = 'images/receipt/' . $name;

                    // 上传oss
                    $this->dscRepository->getOssAddFile([$src]);
                }
            }

            //保存
            $other = [
                'print_bg' => $src,
                'shipping_id' => $shipping_id
            ];
            ShippingTpl::where('shipping_id', $shipping_id)
                ->where('ru_id', $adminru['ru_id'])
                ->update($other);

            echo '<script language="javascript">';
            echo 'parent.call_flash("bg_add", "' . $this->dscRepository->getImagePath($src) . '");';
            echo '</script>';
        }

        /*------------------------------------------------------ */
        //-- 模板Flash编辑器 删除图片
        /*------------------------------------------------------ */

        elseif ($act == 'print_del') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $shipping_id = request()->input('shipping', 0);
            $shipping_id = json_str_iconv($shipping_id);

            /* 检查该插件是否已经安装 取值 */
            $row = ShippingTpl::select('print_bg')->where('shipping_id', $shipping_id);
            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {
                if ($row['print_bg'] != '' && !ShippingManageService::is_print_bg_default($row['print_bg'])) {
                    // 删除oss
                    $this->dscRepository->getOssDelFile([$row['print_bg']]);
                    dsc_unlink(storage_public($row['print_bg']));
                }

                ShippingTpl::where('shipping_id', $shipping_id)
                    ->where('ru_id', $adminru['ru_id'])
                    ->update(['print_bg' => '']);
            } else {
                return make_json_error($GLOBALS['_LANG']['js_languages']['upload_del_falid']);
            }

            return make_json_result($shipping_id);
        }

        /*------------------------------------------------------ */
        //-- 编辑配送方式名称
        /*------------------------------------------------------ */

        elseif ($act == 'edit_name') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $id = json_str_iconv(trim($_POST['id']));
            $val = json_str_iconv(trim($_POST['val']));

            /* 检查名称是否为空 */
            if (empty($val)) {
                return make_json_error($GLOBALS['_LANG']['no_shipping_name']);
            }

            $count = Shipping::where('shipping_name', $val)
                ->where('shipping_id', '<>', $id)
                ->count();

            /* 检查名称是否重复 */
            if ($count > 0) {
                return make_json_error($GLOBALS['_LANG']['repeat_shipping_name']);
            }

            /* 更新支付方式名称 */
            Shipping::where('shipping_code', $id)->update(['shipping_name' => $val]);

            return make_json_result(stripcslashes($val));
        }

        /*------------------------------------------------------ */
        //-- 编辑配送方式描述
        /*------------------------------------------------------ */

        elseif ($act == 'edit_desc') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $id = json_str_iconv(trim($_POST['id']));
            $val = json_str_iconv(trim($_POST['val']));

            /* 更新描述 */
            Shipping::where('shipping_code', $id)->update(['shipping_desc' => $val]);

            return make_json_result(stripcslashes($val));
        }

        /*------------------------------------------------------ */
        //-- 修改配送方式保价费
        /*------------------------------------------------------ */

        elseif ($act == 'edit_insure') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $id = json_str_iconv(trim($_POST['id']));
            $val = json_str_iconv(trim($_POST['val']));
            if (empty($val)) {
                $val = 0;
            } else {
                $val = make_semiangle($val); //全角转半角
                if (strpos($val, '%') === false) {
                    $val = floatval($val);
                } else {
                    $val = floatval($val) . '%';
                }
            }

            $shipping_name = StrRepository::studly($id);
            $modules = plugin_path('Shipping/' . $shipping_name . '/config.php');

            if (file_exists($modules)) {
                $modules = include_once($modules);

                if (isset($modules['insure']) && $modules['insure'] === false) {
                    return make_json_error($GLOBALS['_LANG']['not_support_insure']);
                }
            }

            /* 更新保价费用 */
            Shipping::where('shipping_code', $id)->update(['insure' => $val]);
            return make_json_result(stripcslashes($val));
        }

        /*------------------------------------------------------ */
        //-- 修改配送方式排序
        /*------------------------------------------------------ */

        elseif ($act == 'edit_order') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $code = json_str_iconv(trim($_POST['id']));
            $order = intval($_POST['val']);

            /* 更新排序 */
            Shipping::where('shipping_code', $code)->update(['shipping_order' => $order]);

            return make_json_result(stripcslashes($order));
        }

        /*------------------------------------------------------ */
        //-- 编辑打印模板
        /*------------------------------------------------------ */

        elseif ($act == 'edit_print_template') {
            admin_priv('ship_manage');

            $shipping_id = request()->input('shipping', 0);

            /* 检查该插件是否已经安装 */
            $row = Shipping::where('shipping_id', $shipping_id);
            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {
                $ship_tpl = ShippingTpl::where('shipping_id', $shipping_id)
                    ->where('ru_id', $adminru['ru_id']);
                $ship_tpl = BaseRepository::getToArrayFirst($ship_tpl);

                if (!$ship_tpl) {
                    ShippingTpl::insert([
                        'shipping_id' => $shipping_id,
                        'ru_id' => $adminru['ru_id'],
                        'print_bg' => '',
                        'update_time' => TimeRepository::getGmTime()
                    ]);
                }
                $ship_tpl['shipping_print'] = !empty($ship_tpl['shipping_print']) ? $ship_tpl['shipping_print'] : '';
                $ship_tpl['print_bg'] = !empty($ship_tpl['print_bg']) ? $this->dscRepository->getImagePath($ship_tpl['print_bg']) : '';
                $ship_tpl['print_model'] = empty($ship_tpl['print_model']) ? 1 : $ship_tpl['print_model']; //兼容以前版本

                $this->smarty->assign('shipping', $ship_tpl);
            } else {
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'shipping.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['no_shipping_install'], 0, $lnk);
            }

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_shipping_list'] . ' - ' . $row['shipping_name'] . ' - ' . $GLOBALS['_LANG']['shipping_print_template']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['03_shipping_list'], 'href' => 'shipping.php?act=list']);
            $this->smarty->assign('shipping_id', $shipping_id);

            return $this->smarty->display('shipping_template.dwt');
        }

        /*------------------------------------------------------ */
        //-- 编辑打印模板
        /*------------------------------------------------------ */

        elseif ($act == 'do_edit_print_template') {
            /* 检查权限 */
            admin_priv('ship_manage');

            /* 参数处理 */
            $print_model = !empty($_POST['print_model']) ? intval($_POST['print_model']) : 0;
            $shipping_id = !empty($_REQUEST['shipping']) ? intval($_REQUEST['shipping']) : 0;
            $_POST['config_lable'] = !empty($_POST['config_lable']) ? rtrim($_POST['config_lable'], "||,||") : '';

            /* 处理不同模式编辑的表单 */
            if ($print_model == 2) {
                //所见即所得模式
                ShippingTpl::where('shipping_id', $shipping_id)
                    ->where('ru_id', $adminru['ru_id'])
                    ->update([
                        'config_lable' => $_POST['config_lable'],
                        'print_model' => $print_model
                    ]);
            } elseif ($print_model == 1) {
                //代码模式
                $template = !empty($_POST['shipping_print']) ? $_POST['shipping_print'] : '';
                ShippingTpl::where('shipping_id', $shipping_id)
                    ->where('ru_id', $adminru['ru_id'])
                    ->update([
                        'shipping_print' => $template,
                        'print_model' => $print_model
                    ]);
            }

            /* 记录管理员操作 */
            admin_log(addslashes($_POST['shipping_name']), 'edit', 'shipping');

            $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'shipping.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit_template_success'], 0, $lnk);
        } elseif ($act == 'shipping_priv') {
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            return make_json_result('');
        }

        /*------------------------------------------------------ */
        //-- 快递鸟账号设置
        /*------------------------------------------------------ */

        elseif ($act == 'account_setting') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $shipping_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
            $shipping_info = get_shipping_info($shipping_id, $adminru['ru_id']);
            $shipping_spec = get_shipping_spec($shipping_info['shipping_code']);
            $this->smarty->assign('shipping_info', $shipping_info);
            $this->smarty->assign('shipping_spec', $shipping_spec);
            $html = $this->smarty->fetch('library/kdniao_account.lbi');

            return make_json_result($html);
        }

        /*------------------------------------------------------ */
        //-- 快递鸟账号保存
        /*------------------------------------------------------ */

        elseif ($act == 'account_save') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $data = [];
            $data['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
            $data['shipper_code'] = empty($_REQUEST['shipper_code']) ? '' : trim($_REQUEST['shipper_code']);
            $data['customer_name'] = empty($_REQUEST['customer_name']) ? '' : trim($_REQUEST['customer_name']);
            $data['customer_pwd'] = empty($_REQUEST['customer_pwd']) ? '' : trim($_REQUEST['customer_pwd']);
            $data['month_code'] = empty($_REQUEST['month_code']) ? '' : trim($_REQUEST['month_code']);
            $data['send_site'] = empty($_REQUEST['send_site']) ? '' : trim($_REQUEST['send_site']);
            $data['pay_type'] = empty($_REQUEST['pay_type']) ? 1 : intval($_REQUEST['pay_type']);
            $data['template_size'] = empty($_REQUEST['template_size']) ? '' : trim($_REQUEST['template_size']);
            $data['ru_id'] = $adminru['ru_id'];

            if (get_shipping_conf($data['shipping_id'], $data['ru_id'])) {
                $shipping_id = $data['shipping_id'];

                unset($data['shipping_id']);
                unset($data['ru_id']);

                KdniaoEorderConfig::where('shipping_id', $shipping_id)
                    ->where('ru_id', $adminru['ru_id'])
                    ->update($data);
            } else {
                KdniaoEorderConfig::insert($data);
            }

            if ($data['customer_name'] != '' && $data['customer_pwd'] != '') {
                return 1;
            } else {
                return 2;
            }
        }

        /* ------------------------------------------------------ */
        //-- 快递鸟客户号申请
        /* ------------------------------------------------------ */
        elseif ($act == 'account_apply') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $shipping_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
            $shipping_info = get_shipping_info($shipping_id, $adminru['ru_id']);
            $shipping_spec = get_shipping_spec($shipping_info['shipping_code']);
            $customer_account = get_kdniao_customer_account($shipping_id, $adminru['ru_id']);
            $this->smarty->assign('shipping_info', $shipping_info);
            $this->smarty->assign('shipping_spec', $shipping_spec);
            $this->smarty->assign('customer_account', $customer_account);

            $province_list = get_regions(1, 1);
            $this->smarty->assign('province_list', $province_list);
            $html = $this->smarty->fetch('library/kdniao_account_apply.lbi');

            return make_json_result($html);
        }

        /* ------------------------------------------------------ */
        //-- 快递鸟客户号提交
        /* ------------------------------------------------------ */
        elseif ($act == 'account_submit') {
            /* 检查权限 */
            $check_auth = check_authz_json('ship_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $data = [];
            $data['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
            $data['dsc_province'] = empty($_REQUEST['dsc_province']) ? 0 : intval($_REQUEST['dsc_province']);
            $data['dsc_city'] = empty($_REQUEST['dsc_city']) ? 0 : intval($_REQUEST['dsc_city']);
            $data['dsc_district'] = empty($_REQUEST['dsc_district']) ? 0 : intval($_REQUEST['dsc_district']);
            $data['shipper_code'] = empty($_REQUEST['shipper_code']) ? '' : trim($_REQUEST['shipper_code']);
            $data['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
            $data['station_name'] = empty($_REQUEST['station_name']) ? '' : trim($_REQUEST['station_name']);
            $data['station_code'] = empty($_REQUEST['station_code']) ? '' : trim($_REQUEST['station_code']);
            $data['apply_id'] = empty($_REQUEST['apply_id']) ? '' : trim($_REQUEST['apply_id']);
            $data['company'] = empty($_REQUEST['company']) ? '' : trim($_REQUEST['company']);
            $data['name'] = empty($_REQUEST['name']) ? '' : trim($_REQUEST['name']);
            $data['mobile'] = empty($_REQUEST['mobile']) ? '' : trim($_REQUEST['mobile']);
            $data['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
            $data['ru_id'] = $adminru['ru_id'];
            if (get_kdniao_customer_account($data['shipping_id'], $data['ru_id'])) {
                $shipping_id = $data['shipping_id'];

                unset($data['shipping_id']);
                unset($data['ru_id']);

                KdniaoCustomerAccount::where('shipping_id', $shipping_id)
                    ->where('ru_id', $adminru['ru_id'])
                    ->update($data);
            } else {
                KdniaoCustomerAccount::insert($data);
            }

            //调用申请接口
            $format_data = [];
            $format_data['ShipperCode'] = $data['shipper_code'];
            $format_data['StationCode'] = $data['station_code'];
            $format_data['StationName'] = $data['station_name'];
            $format_data['ApplyID'] = $data['apply_id'];
            $format_data['Company'] = $data['company'];

            $CityName = Region::where('region_id', $data['dsc_city'])->value('region_name');
            $format_data['CityName'] = $CityName ? $CityName : '';

            $format_data['Name'] = $data['name'];
            $format_data['CityCode'] = isset($_REQUEST['city_code']) ? $_REQUEST['city_code'] : '';
            $format_data['Tel'] = $data['tel'];

            $ExpAreaName = Region::where('region_id', $data['dsc_district'])->value('region_name');
            $format_data['ExpAreaName'] = $ExpAreaName;

            $format_data['Mobile'] = $data['mobile'];
            $format_data['ExpAreaCode'] = isset($_REQUEST['exp_area_code']) ? $_REQUEST['exp_area_code'] : '';

            $ProvinceName = Region::where('region_id', $data['dsc_province'])->value('region_name');
            $format_data['ProvinceName'] = $ProvinceName ? $ProvinceName : '';

            $format_data['Address'] = $data['address'];
            $format_data['ProvinceCode'] = isset($_REQUEST['province_code']) ? $_REQUEST['province_code'] : '';
            $jsonParam = json_encode($format_data, JSON_UNESCAPED_UNICODE);
            $kdniao = Kdniao::getInstance($GLOBALS['_CFG']['kdniao_client_id'], $GLOBALS['_CFG']['kdniao_appkey']);
            $jsonResult = $kdniao->applyCustomerAccount($jsonParam);
            $result = dsc_decode($jsonResult, true);

            if ($result['ResultCode'] != 100) {
                return make_json_error($result['Reason']);
            } else {
                return make_json_result($result['Reason']);
            }
        }
    }


    private function shipping_date_list()
    {
        $res = ShippingDate::query();
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $row) {
                $arr[] = $row;
            }
        }

        return $arr;
    }
}
