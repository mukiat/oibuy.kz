<?php

namespace App\Services\Common;

use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 * Class ConfigManageService
 * @package App\Services\Common
 */
class ConfigManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获得基本设置信息
     *
     * @param array $groups 需要获得的设置组
     * @param array $excludes 不需要获得的设置组
     *
     * @return  array
     */
    public function get_settings($groups = null, $excludes = null)
    {
        /**
         * 不显示的设置组
         */
        $shop_group = [
            'seller',
            'complaint_conf',
            'report_conf',
            'sms',
            'goods',
            'order_delay',
            'cloud',
            'ecjia',
            'return',
            'pc_config',
            'pc_goods_config',
            'cgroup',
            'favourable'
        ];
        /* 取出全部数据：分组和变量 */
        $item_list = ShopConfig::whereNotIn('shop_group', $shop_group)->where('type', '<>', 'hidden');

        if (!empty($groups)) {
            foreach ($groups as $key => $val) {
                $item_list = $item_list->where(function ($query) use ($val) {
                    $query->where('id', $val)->orWhere('parent_id', $val);
                });
            }
        }

        if (!empty($excludes)) {
            foreach ($excludes as $key => $val) {
                $item_list = $item_list->where(function ($query) use ($val) {
                    $query->where('parent_id', '<>', $val)->where('id', '<>', $val);
                });
            }
        }

        /* 不显示code 配置 */
        $filter_item = [
            'seller',
            'sms',
            'hidden',
            'goods',
            'text',
            'cloud_api',
            'cloud',
            'filter_words',
            'mobile',
            'order_delay',
            'report_conf',
            'tpapi',
            'shop_app_description',
            'shop_app_icon',
            'shop_ipad_download',
            'shop_iphone_download',
            'shop_touch_url',
            'shop_pc_url',
            'shop_android_download',
            'suppliers_set',
            'return',
            'pc_config',
            'pc_goods_config',
            'cgroup'
        ];
        $item_list = $item_list->whereNotIn('code', $filter_item);

        $item_list = $item_list->orderBy('parent_id')->orderBy('sort_order')->orderBy('id');

        $item_list = BaseRepository::getToArrayGet($item_list);

        $group_list = [];
        $code_arr = [
            'shop_logo',
            'no_picture',
            'watermark',
            'shop_slagon',
            'wap_logo',
            'two_code_logo',
            'ectouch_qrcode',
            'ecjia_qrcode',
            'index_down_logo',
            'user_login_logo',
            'login_logo_pic',
            'business_logo',
            'admin_login_logo',
            'admin_logo',
            'seller_login_logo',
            'seller_logo',
            'stores_login_logo',
            'stores_logo',
            'order_print_logo',
            'icp_file',
            'kefu_login_log',
            'h5_index_pro_image',
            'pc_download_img',
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
            'h5_index_pro_image'
        ];

        foreach ($item_list as $key => $item) {
            if (!in_array($item['code'], $filter_item)) {
                $pid = $item['parent_id'];
                $item['name'] = isset($GLOBALS['_LANG']['cfg_name'][$item['code']]) ? $GLOBALS['_LANG']['cfg_name'][$item['code']] : $item['code'];
                $item['desc'] = isset($GLOBALS['_LANG']['cfg_desc'][$item['code']]) ? $GLOBALS['_LANG']['cfg_desc'][$item['code']] : '';

                if ($item['code'] == 'sms_shop_mobile') {
                    $item['url'] = 1;
                }
                if ($pid == 0) {
                    /* 分组 */
                    if ($item['type'] == 'group') {
                        $group_list[$item['id']] = $item;
                    }
                } else {
                    /* 变量 */
                    if (isset($group_list[$pid])) {
                        if ($item['store_range']) {
                            $item['store_options'] = explode(',', $item['store_range']);

                            foreach ($item['store_options'] as $k => $v) {
                                $item['display_options'][$k] = isset($GLOBALS['_LANG']['cfg_range'][$item['code']][$v]) ?
                                    $GLOBALS['_LANG']['cfg_range'][$item['code']][$v] : $v;
                            }
                        }

                        if ($item) {
                            if ($item['type'] == 'file' && in_array($item['code'], $code_arr) && $item['value']) {
                                $item['del_img'] = 1;

                                if (strpos($item['value'], '../') === false) {
                                    if (in_array($item['code'], $adminLogo)) {
                                        if (!empty($item['value'])) {
                                            $item['value'] = $this->dscRepository->getImagePath('assets/' . $item['value']);
                                        } else {
                                            $item['value'] = asset('assets/' . $item['value']);
                                        }
                                    } else {
                                        $item['value'] = $this->dscRepository->getImagePath($item['value']);
                                    }
                                }
                            } else {
                                $item['del_img'] = 0;
                            }

                            if ($item['code'] == 'stats_code') {
                                $item['value'] = html_out($item['value']);
                            }
                        }

                        $group_list[$pid]['vars'][] = $item;
                    }
                }
            }
        }

        return $group_list;
    }

    /**
     * 获得设置分组
     *
     * @param string $shop_group
     * @return array
     */
    public function getSettingGroups($shop_group = '')
    {
        $group_list = [];
        $list = $this->getUpSettings($shop_group);

        //将设置规组
        if ($list) {
            foreach ($list as $key => $val) {
                $group_list[$val['parent_id']]['vars'][] = $val;
            }
        }

        //补全组信息
        if ($group_list) {
            foreach ($group_list as $key => $val) {
                $data = ShopConfig::where('id', $key);
                $data = BaseRepository::getToArrayFirst($data);

                //处理数据
                $data['name'] = isset($GLOBALS['_LANG']['cfg_name'][$data['code']]) ? $GLOBALS['_LANG']['cfg_name'][$data['code']] : $data['code'];
                $data['desc'] = isset($GLOBALS['_LANG']['cfg_desc'][$data['code']]) ? $GLOBALS['_LANG']['cfg_desc'][$data['code']] : '';

                //合并数据
                $data = array_merge($data, $val);
                $group_list[$key] = $data;
            }
        }

        return $group_list;
    }

    /**
     * 获得设置信息
     *
     * @param string $shop_group 需要获得的设置组
     * @return array
     */
    public function getUpSettings($shop_group = '')
    {
        /* 取出全部数据：分组和变量 */
        $item_list = ShopConfig::where('parent_id', '>', 0)
            ->where('type', '<>', 'hidden');

        if (!empty($shop_group)) {
            $item_list = $item_list->where('shop_group', $shop_group);
        }

        $item_list = $item_list->orderBy('sort_order')->orderBy('id');
        $item_list = BaseRepository::getToArrayGet($item_list);

        /* 图片配置code */
        $code_arr = [
            'shop_logo',
            'no_picture',
            'watermark',
            'shop_slagon',
            'wap_logo',
            'two_code_logo',
            'ectouch_qrcode',
            'ecjia_qrcode',
            'index_down_logo',
            'user_login_logo',
            'login_logo_pic',
            'business_logo',
            'no_brand',
            'pc_download_img',
            'copyright_img',
            'wxapp_top_img',
            'app_top_img',
        ];

        $group_list = [];
        if ($item_list) {
            foreach ($item_list as $key => $item) {
                $item['name'] = isset($GLOBALS['_LANG']['cfg_name'][$item['code']]) ? $GLOBALS['_LANG']['cfg_name'][$item['code']] : $item['code'];
                $item['desc'] = isset($GLOBALS['_LANG']['cfg_desc'][$item['code']]) ? $GLOBALS['_LANG']['cfg_desc'][$item['code']] : '';

                if ($item['code'] == 'sms_shop_mobile') {
                    $item['url'] = 1;
                }

                if ($item['store_range']) {
                    $item['store_options'] = explode(',', $item['store_range']);

                    foreach ($item['store_options'] as $k => $v) {
                        $item['display_options'][$k] = isset($GLOBALS['_LANG']['cfg_range'][$item['code']][$v]) ?
                            $GLOBALS['_LANG']['cfg_range'][$item['code']][$v] : $v;
                    }
                }
                if ($item) {
                    if ($item['type'] == 'file' && in_array($item['code'], $code_arr) && $item['value']) {
                        $item['del_img'] = 1;

                        $item['value'] = $this->dscRepository->getImagePath($item['value']);
                    } else {
                        $item['del_img'] = 0;
                    }
                }
                $group_list[] = $item;
            }
        }

        return $group_list;
    }
}
