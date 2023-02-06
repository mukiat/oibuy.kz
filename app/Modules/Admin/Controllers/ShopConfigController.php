<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Http;
use App\Models\MerchantsStepsFields;
use App\Models\Region;
use App\Models\SellerShopinfo;
use App\Models\ShopConfig;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\ConfigManageService;
use App\Services\Common\ConfigService;
use Illuminate\Support\Str;

/**
 * 管理中心商店设置
 */
class ShopConfigController extends InitController
{
    protected $dscRepository;
    protected $commonRepository;
    protected $configManageService;

    public function __construct(
        DscRepository $dscRepository,
        CommonRepository $commonRepository,
        ConfigManageService $configManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->commonRepository = $commonRepository;
        $this->configManageService = $configManageService;
    }

    public function index()
    {
        $act = e(request()->input('act', ''));

        /*------------------------------------------------------ */
        //-- 列表编辑 ?act=list_edit
        /*------------------------------------------------------ */
        if ($act == 'list_edit') {
            /* 检查权限 */
            admin_priv('shop_config');

            /* 可选语言 */
            $dirs = glob(resource_path('lang/*'), GLOB_ONLYDIR);

            $lang_list = [];
            $lang_array = ['zh-CN', 'zh-TW', 'en'];
            foreach ($dirs as $dir) {
                $lang = basename($dir);
                if (in_array($lang, $lang_array)) {
                    $lang_list[] = $lang;
                }
            }

            $this->smarty->assign('lang_list', $lang_list);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['01_shop_config']);

            $group_list = $this->configManageService->get_settings(null, ['5']);
            $this->smarty->assign('group_list', $group_list);
            $this->smarty->assign('countries', get_regions());

            if (strpos(strtolower(request()->server('SERVER_SOFTWARE')), 'iis') !== false) {
                $rewrite_confirm = $GLOBALS['_LANG']['rewrite_confirm_iis'];
            } else {
                $rewrite_confirm = $GLOBALS['_LANG']['rewrite_confirm_apache'];
            }
            $this->smarty->assign('rewrite_confirm', $rewrite_confirm);

            if ($GLOBALS['_CFG']['shop_country'] > 0) {
                $this->smarty->assign('provinces', get_regions(1, $GLOBALS['_CFG']['shop_country']));
                if ($GLOBALS['_CFG']['shop_province']) {
                    $this->smarty->assign('cities', get_regions(2, $GLOBALS['_CFG']['shop_province']));
                }
                if ($GLOBALS['_CFG']['shop_city']) {
                    $this->smarty->assign('districts', get_regions(3, $GLOBALS['_CFG']['shop_city']));
                }
            }
            $this->smarty->assign('cfg', $GLOBALS['_CFG']);

            $invoice_list = CommonRepository::getInvoiceList($GLOBALS['_CFG']['invoice_type']);
            $this->smarty->assign('invoice_list', $invoice_list); //发票类型及税率

            $cross_source = ConfigService::cross_source();
            $this->smarty->assign('cross_source', $cross_source);

            if (CROSS_BORDER === true) { // 跨境多商户
                $this->smarty->assign('is_kj', 1);
            } else {
                $this->smarty->assign('is_kj', 0);
            }

            return $this->smarty->display('shop_config.dwt');
        }

        /*------------------------------------------------------ */
        //-- 邮件服务器设置
        /*------------------------------------------------------ */
        elseif ($act == 'mail_settings') {
            /* 检查权限 */
            admin_priv('mail_settings');

            $arr = $this->configManageService->get_settings([5]);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['01_mail_settings']);
            $this->smarty->assign('cfg', $arr[5]['vars']);
            return $this->smarty->display('shop_config_mail_settings.dwt');
        }

        /*------------------------------------------------------ */
        //-- 退款设置
        /*------------------------------------------------------ */
        elseif ($act == 'return_config') {
            /* 检查权限 */
            admin_priv('shop_config');

            $this->dscRepository->helpersLang('shop_config', 'admin');

            $this->assign('ur_here', trans('admin::common.order_word') . ' - ' . trans('admin::common.11_return_config'));
            // 操作提示
            $this->assign('explanation_tips', trans('admin::shop_config.operation_prompt_content.shop_config_return'));

            //页面切换菜单
            $tab_menu = [];
            $tab_menu[] = ['curr' => 0, 'text' => trans('admin::common.11_back_cause'), 'href' => 'order.php?act=back_cause_list'];
            $this->assign('tab_menu', $tab_menu);

            $shop_group = 'return';
            $group_list = $this->configManageService->getSettingGroups($shop_group);

            // 标识设置类型(固定)
            $this->assign('type', 'shop_config_return');

            $this->assign('group_list', $group_list);
            $this->assign('shop_group', $shop_group);
            return $this->display('admin.shop_config.shop_config');
        }

        /*------------------------------------------------------ */
        //-- PC基本设置
        /*------------------------------------------------------ */
        elseif ($act == 'pc_shop_config') {
            /* 检查权限 */
            admin_priv('shop_config');

            $this->dscRepository->helpersLang('shop_config', 'admin');

            $shop_group = request()->input('shop_group', 'pc_config');

            $ur_here = $shop_group == 'pc_goods_config' ? trans('admin::common.02_pc_goods_config') : trans('admin::common.01_pc_shop_config');
            $this->assign('ur_here', trans('admin::common.pc_01_setting') . ' - ' . $ur_here);
            // 操作提示
            $this->assign('explanation_tips', trans('admin/shop_config.pc_shop_config_tips'));

            // 配置分组
            $group_list = $this->configManageService->getSettingGroups($shop_group);

            // 标识设置类型(固定)
            $this->assign('type', 'pc_shop_config');

            $this->assign('group_list', $group_list);
            $this->assign('shop_group', $shop_group);
            $this->assign('callback', $shop_group);
            return $this->display('admin.shop_config.shop_config');
        }


        /*------------------------------------------------------ */
        //-- 提交   ?act=post
        /*------------------------------------------------------ */
        elseif ($act == 'post') {
            /* 检查权限 */
            admin_priv('shop_config');

            $type = e(request()->input('type', ''));

            /* 允许上传的文件类型 */
            $allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|CERT|';

            /* 保存变量值 */
            $post_value = request()->post('value');

            $arr = [];

            $res = ShopConfig::select('id', 'value');
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $row) {
                    $arr[$row['id']] = $row['value'] ?? '';
                }
            }

            if (CROSS_BORDER === true) { // 跨境多商户

                $old_cross_source = request()->post('old_cross_source');
                $old_cross_source = ArrRepository::getArrayUnset($old_cross_source);

                $cross_source = request()->post('cross_source');
                $cross_source = ArrRepository::getArrayUnset($cross_source);

                $sourceIntersect = BaseRepository::getArrayDiff($old_cross_source, $cross_source);
                $sourceIntersect = ArrRepository::getArrayUnset($sourceIntersect);

                if ($sourceIntersect) {
                    foreach ($sourceIntersect as $skey => $sval) {
                        if (isset($cross_source[$skey]) && !empty($cross_source[$skey])) {
                            MerchantsStepsFields::where('source', $sval)->update([
                                'source' => $cross_source[$skey]
                            ]);
                        }
                    }
                }

                $cross_source = BaseRepository::getImplode($cross_source);

                $cross_source_id = request()->post('cross_source_id', 0);

                ShopConfig::where('id', $cross_source_id)->update([
                    'value' => $cross_source
                ]);
            }

            foreach ($post_value as $key => $val) {
                $val = html_in($val);
                if ($arr && $arr[$key] != $val) {
                    ShopConfig::where('id', $key)->update(['value' => $val]);
                }
            }

            /* 处理上传文件 */
            $file_var_list = [];
            $res = ShopConfig::where('parent_id', '>', 0)->where('type', 'file');
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $row) {
                    if (strpos($row['store_dir'], '../') !== false) {
                        $row = str_replace('../', '', $row);
                    }
                    $file_var_list[$row['code']] = $row;
                }
            }

            foreach ($_FILES as $code => $file) {
                if (!file_exists(storage_public($file_var_list[$code]['store_dir']))) {
                    make_dir(storage_public($file_var_list[$code]['store_dir']));
                }

                /* 判断用户是否选择了文件 */
                if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none')) {
                    /* 检查上传的文件类型是否合法 */
                    if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
                        return sys_msg(sprintf($GLOBALS['_LANG']['msg_invalid_file'], $file['name']));
                    } else {
                        $code_store_dir = [
                            'shop_logo',
                            'ecjia_qrcode',
                            'ectouch_qrcode',
                            'index_down_logo',
                            'user_login_logo',
                            'login_logo_pic',
                            'admin_login_logo',
                            'admin_logo',
                            'seller_login_logo',
                            'seller_logo',
                            'stores_login_logo',
                            'stores_logo',
                            'order_print_logo',
                            'kefu_login_log',
                            'h5_index_pro_image',
                            'custom_jump_logo',
                            'kefu_logo',
                            'consult_share_img',
                            'copyright_img',
                            'wxapp_top_img',
                            'app_top_img',
                        ];

                        $adminLogo = [
                            'admin_login_logo',
                            'admin_logo',
                            'seller_login_logo',
                            'seller_logo',
                            'stores_login_logo',
                            'stores_logo',
                            'order_print_logo',
                            'kefu_login_log',
                            'h5_index_pro_image',
                            'custom_jump_logo',
                            'kefu_logo',
                            'consult_share_img',
                        ];

                        $dir_name = '';
                        if ($code == 'business_logo') {
                            load_helper('template', 'admin');
                            $info = get_template_info($GLOBALS['_CFG']['template']);

                            $file_name = str_replace('{$template}', $GLOBALS['_CFG']['template'], $file_var_list[$code]['store_dir']) . $info['business_logo'];
                        } elseif ($code == 'watermark') {
                            $ext = !empty($file['name']) ? explode('.', $file['name']) : '';
                            $ext = !empty($ext) ? array_pop($ext) : '';
                            $file_name = storage_public($file_var_list[$code]['store_dir'] . 'watermark.' . $ext);
                            $dir_name = $file_var_list[$code]['store_dir'] . 'watermark.' . $ext;
                            if (file_exists($file_var_list[$code]['value'])) {
                                @unlink($file_var_list[$code]['value']);
                            }
                        } elseif ($code == 'wap_logo') {
                            $ext = !empty($file['name']) ? explode('.', $file['name']) : '';
                            $ext = !empty($ext) ? array_pop($ext) : '';

                            $file_name = storage_public($file_var_list[$code]['store_dir'] . $code . "." . $ext);
                            $dir_name = $file_var_list[$code]['store_dir'] . $code . "." . $ext;

                            if (file_exists($file_var_list[$code]['value'])) {
                                @unlink($file_var_list[$code]['value']);
                            }
                        } elseif ($code == 'two_code_logo') {
                            $ext = !empty($file['name']) ? explode('.', $file['name']) : '';
                            $ext = !empty($ext) ? array_pop($ext) : '';

                            $file_name = storage_public($file_var_list[$code]['store_dir'] . $code . "." . $ext);
                            $dir_name = $file_var_list[$code]['store_dir'] . $code . "." . $ext;

                            if (file_exists($file_var_list[$code]['value'])) {
                                @unlink($file_var_list[$code]['value']);
                            }
                        } elseif (in_array($code, $code_store_dir)) {
                            $ext = !empty($file['name']) ? explode('.', $file['name']) : '';
                            $ext = !empty($ext) ? array_pop($ext) : '';

                            if (in_array($code, $adminLogo)) {
                                if (!file_exists(storage_public('/assets/' . $file_var_list[$code]['store_dir']))) {
                                    make_dir(storage_public('/assets/' . $file_var_list[$code]['store_dir']));
                                }
                                $file_name = storage_public('/assets/' . $file_var_list[$code]['store_dir'] . $code . "." . $ext);
                            } else {
                                if (!file_exists(storage_public($file_var_list[$code]['store_dir']))) {
                                    make_dir(storage_public($file_var_list[$code]['store_dir']));
                                }

                                $file_name = storage_public($file_var_list[$code]['store_dir'] . $code . "." . $ext);
                            }

                            $dir_name = $file_var_list[$code]['store_dir'] . $code . "." . $ext;

                            if (file_exists($file_var_list[$code]['value'])) {
                                @unlink(storage_public($file_var_list[$code]['value']));
                            }
                        } else {
                            $file_name = storage_public($file_var_list[$code]['store_dir'] . $file['name']);
                            $dir_name = $file_var_list[$code]['store_dir'] . $file['name'];
                        }

                        /* 判断是否上传成功 */
                        if (move_upload_file($file['tmp_name'], $file_name)) {
                            $file_name = $dir_name;
                            $olde_value = ShopConfig::where('code', $code)->value('value');
                            $olde_value = $olde_value ? $olde_value : '';

                            if ($file_name) {
                                $oss_file_name = str_replace(['../'], '', $file_name);
                                if ($olde_value != $file_name && $olde_value != "../images/errorImg.png" && $olde_value != '' && strpos($olde_value, 'http://') === false && strpos($olde_value, 'https://') === false) {
                                    $oss_olde_file = str_replace(['../'], '', $olde_value);
                                    $this->dscRepository->getOssDelFile([$oss_olde_file]);
                                    //做判断，判断文件是否存在，如果存在则删除

                                    $oss_olde_file = $this->dscRepository->getImagePath($oss_olde_file);
                                    dsc_unlink($oss_olde_file);
                                }

                                if (in_array($code, $adminLogo)) {
                                    $oss_file_name = 'assets/' . $oss_file_name;
                                }

                                $this->dscRepository->getOssAddFile([$oss_file_name]);
                            }

                            ShopConfig::where('code', $code)->update([
                                'value' => $file_name . '?' . Str::random(6)
                            ]);
                        } else {
                            return sys_msg(sprintf($GLOBALS['_LANG']['msg_upload_failed'], $file['name'], $file_var_list[$code]['store_dir']));
                        }
                    }
                }
            }

            $invoice_type = request()->has('invoice_type') ? request()->input('invoice_type') : [];
            $invoice_rate = request()->has('invoice_rate') ? request()->input('invoice_rate') : [];
            $invoice_list = $this->get_post_invoice($invoice_type, $invoice_rate);

            /* 处理发票类型及税率 */
            if (!empty($invoice_list['type'])) {
                $invoice = [
                    'type' => $invoice_list['type'],
                    'rate' => $invoice_list['rate']
                ];

                ShopConfig::where('code', 'invoice_type')->update([
                    'value' => serialize($invoice)
                ]);
            }

            if (empty($invoice_list['type']) && empty($invoice_list['rate'])) {
                ShopConfig::where('code', 'invoice_type')->update([
                    'value' => ''
                ]);
            }

            /* 记录日志 */
            admin_log('', 'edit', 'shop_config');

            /* 清除缓存 */
            cache()->forget('shop_config');

            /* 当自定义客服时，更新自营客服设置 */
            if (config('shop.customer_service_type') == 3) {
                SellerShopinfo::where('ru_id', 0)->update([
                    'kf_im_switch' => 1
                ]);

                cache()->forget('kf_im_switch');
            }

            $shop_url = urlencode($this->dsc->url());

            $code = ['shop_name', 'shop_title', 'shop_desc', 'shop_keywords', 'shop_address', 'icp_number', 'qq', 'ww', 'service_phone', 'msn', 'service_email', 'sms_shop_mobile', 'lang', 'certi', 'shop_country', 'shop_province', 'shop_city', 'shop_district', 'shop_address'];
            $row = ShopConfig::select('id', 'code', 'value')->whereIn('code', $code);
            $row = BaseRepository::getToArrayGet($row);

            $row = get_cfg_val($row);

            // 不需要修改自营设置的分组 同时跳转回原分组设置页面
            $back_array = ['mail_setting', 'seller_setup', 'report_conf', 'complaint_conf', 'sms_setup', 'cloud_setup', 'goods_setup', 'order_delay', 'consult_set', 'shop_config_return'];

            if (!in_array($type, $back_array)) {
                // 同步修改自营设置
                $update_arr = [
                    //'mobile' => $row[sms_shop_mobile], //手机
                    'seller_email' => isset($post_value[114]) ? $post_value[114] : '', //邮箱
                    'kf_qq' => isset($post_value[109]) ? $post_value[109] : '', //QQ
                    'kf_ww' => isset($post_value[110]) ? $post_value[110] : '', //旺旺
                    'shop_title' => isset($post_value[102]) ? $post_value[102] : '', //商店标题
                    'shop_keyword' => isset($post_value[104]) ? $post_value[104] : '', //商店关键字
                    'country' => $row['shop_country'] ?? 0, //国家
                    'province' => $row['shop_province'] ?? 0, //省份
                    'city' => $row['shop_city'] ?? 0, //城市
                    'district' => $row['shop_district'] ?? 0, //区域
                    'shop_address' => $row['shop_address'] ?? '', //详细地址
                    'kf_tel' => isset($post_value[115]) ? $post_value[115] : '', //客服电话
                    'notice' => isset($post_value[121]) ? $post_value[121] : '', //店铺公告
                ];
                foreach ($update_arr as $key => $val) {
                    $val = html_in($val);
                    SellerShopinfo::where('ru_id', 0)->update([$key => $val]);
                }
            }

            if (in_array($type, $back_array)) {
                $back = '';
                $href = '';
                $sys_msg = '';

                /* 邮箱设置 */
                if ($type == 'mail_setting') {
                    $back = $GLOBALS['_LANG']['back_mail_settings'];
                    $href = 'shop_config.php?act=mail_settings';
                    $sys_msg = $GLOBALS['_LANG']['mail_save_success'];
                } /* 店铺设置 */
                elseif ($type == 'seller_setup') {
                    $back = $GLOBALS['_LANG']['back_seller_settings'];
                    $href = 'merchants_steps.php?act=step_up';
                    $sys_msg = $GLOBALS['_LANG']['seller_save_success'];
                } /* 短信设置 */
                elseif ($type == 'sms_setup') {
                    $back = $GLOBALS['_LANG']['back_sms_settings'];
                    $href = 'sms_setting.php?act=step_up';
                    $sys_msg = $GLOBALS['_LANG']['sms_success'];
                } /* 文件存储设置 */
                elseif ($type == 'cloud_setup') {
                    $back = $GLOBALS['_LANG']['back_cloud_settings'];
                    $href = 'cloud_setting.php?act=step_up';
                    $sys_msg = $GLOBALS['_LANG']['cloud_success'];
                } /* 举报设置 */
                elseif ($type == 'report_conf') {
                    $back = $GLOBALS['_LANG']['report_conf'];
                    $href = 'goods_report.php?act=report_conf';
                    $sys_msg = $GLOBALS['_LANG']['report_conf_success'];
                } /* 投诉设置 */
                elseif ($type == 'complaint_conf') {
                    $back = $GLOBALS['_LANG']['complain_conf'];
                    $href = 'complaint.php?act=complaint_conf';
                    $sys_msg = $GLOBALS['_LANG']['complain_conf_success'];
                } /* 商品设置 */
                elseif ($type == 'goods_setup') {
                    $back = $GLOBALS['_LANG']['goods_setup'];
                    $href = 'goods.php?act=step_up';
                    $sys_msg = $GLOBALS['_LANG']['goods_setup_success'];
                } elseif ($type == 'order_delay') {
                    /**
                     * 订单延迟
                     */
                    $back = $GLOBALS['_LANG']['order_delay_conf'];
                    $href = 'order_delay.php?act=complaint_conf';
                    $sys_msg = $GLOBALS['_LANG']['order_delay_success'];
                } elseif ($type == 'consult_set') {
                    /**
                     * 首页咨询设置
                     */
                    $back = lang('admin/common.go_back');
                    $href = route('admin/consult_set');
                    $sys_msg = lang('admin/common.save_success');
                }
            } else {
                $back = $GLOBALS['_LANG']['back_shop_config'];
                $href = 'shop_config.php?act=list_edit';
                $sys_msg = $GLOBALS['_LANG']['save_success'];
            }

            // 返回上一页
            $callback = urldecode(request()->get('callback'));
            if (!empty($callback)) {
                $links[] = ['text' => trans('admin/common.go_back'), 'href' => $callback];
                return sys_msg(trans('admin/common.save_success'), 0, $links);
            }

            $links[] = ['text' => $back, 'href' => $href];
            return sys_msg($sys_msg, 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 发送测试邮件
        /*------------------------------------------------------ */
        elseif ($act == 'send_test_email') {
            /* 检查权限 */
            $check_auth = check_authz_json('shop_config');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $email = e(request()->input('email', ''));

            if (CommonRepository::sendEmail('', $email, $GLOBALS['_LANG']['test_mail_title'], $GLOBALS['_LANG']['cfg_name']['email_content'], 0)) {
                return make_json_result('', $GLOBALS['_LANG']['sendemail_success'] . $email);
            } else {
                return make_json_error('fail');
            }
        }

        /*------------------------------------------------------ */
        //-- 删除上传文件
        /*------------------------------------------------------ */
        elseif ($act == 'del') {
            /* 检查权限 */
            $check_auth = check_authz_json('shop_config');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 取得参数 */
            $code = e(request()->input('code', ''));

            $filename = $GLOBALS['_CFG'][$code];

            if (isset($filename) && !empty($filename)) {
                $oss_file_name = str_replace(['../'], '', $filename);
                $this->dscRepository->getOssDelFile([$oss_file_name]);
            }

            //删除文件
            if (in_array($code, ['admin_login_logo', 'admin_logo', 'seller_login_logo', 'seller_logo', 'stores_login_logo', 'stores_logo', 'order_print_logo', 'kefu_login_log'])) {
                dsc_unlink(public_path('/assets/' . $filename));
            } else {
                dsc_unlink(storage_public($filename));
            }

            ShopConfig::where('code', $code)->update([
                'value' => ''
            ]);

            /* 记录日志 */
            admin_log($code, 'edit', 'shop_config');

            //跳转链接
            $shop_group = ShopConfig::where('code', $code)->value('shop_group');
            $shop_group = $shop_group ? $shop_group : '';

            switch ($shop_group) {
                case 'goods':
                    $text = $GLOBALS['_LANG']['goods_setup'];
                    $href = 'goods.php?act=step_up';
                    $sys_msg = $GLOBALS['_LANG']['goods_setup_success'];
                    break;
                default:
                    $text = $GLOBALS['_LANG']['back_shop_config'];
                    $href = 'shop_config.php?act=list_edit';
                    $sys_msg = $GLOBALS['_LANG']['save_success'];
            }

            // 返回上一页
            $callback = urldecode(request()->get('callback'));
            if (!empty($callback)) {
                $links[] = ['text' => trans('admin/common.go_back'), 'href' => $callback];
                return sys_msg(trans('admin/common.save_success'), 0, $links);
            }

            $links[] = ['text' => $text, 'href' => $href];
            return sys_msg($sys_msg, 0, $links);
        }
    }

    //过滤提交票税信息
    private function get_post_invoice($type, $rate)
    {
        if ($type) {
            for ($i = 0; $i < count($type); $i++) {
                if (empty($type[$i]) && empty($rate[$i])) {
                    unset($type[$i]);
                    unset($rate[$i]);
                } else {
                    $rate[$i] = round(floatval($rate[$i]), 2);
                }
            }
        } else {
            $type = [];
            $rate = [];
        }

        return ['type' => $type, 'rate' => $rate];
    }
}
