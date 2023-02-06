<?php

namespace App\Services\Shipping;

use App\Models\AreaRegion;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportTpl;
use App\Models\Shipping;
use App\Models\ShippingArea;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\StrRepository;

/**
 * 配送方式管理
 * Class ShippingManageService
 * @package App\Services\Shipping
 */
class ShippingManageService
{
    /**
     * 是否安装配送方式
     * @param string $shipping_code
     * @return int
     */
    public static function checkShipping($shipping_code = '')
    {
        if (empty($shipping_code)) {
            return 0;
        }

        $count = Shipping::where('shipping_code', $shipping_code)->count('shipping_id');
        return $count ?? 0;
    }

    /**
     * 安装配送方式
     * @param string $shipping_code
     * @return bool
     */
    public static function installShipping($shipping_code = '')
    {
        if (empty($shipping_code)) {
            return false;
        }

        /* 检查该配送方式是否已经安装 */
        $count = self::checkShipping($shipping_code);
        if ($count > 0) {
            /* 该配送方式已经安装过, 将该配送方式的状态设置为 enable */
            Shipping::where('shipping_code', $shipping_code)->update(['enabled' => 1]);
            return true;
        } else {
            $shipping_name = StrRepository::studly($shipping_code);
            $modules = plugin_path('Shipping/' . $shipping_name . '/config.php');

            if (file_exists($modules)) {
                $data = include_once($modules);

                $other = [
                    'shipping_code' => $shipping_code,
                    'shipping_name' => $GLOBALS['_LANG'][$shipping_code],
                    'shipping_desc' => $GLOBALS['_LANG'][$data['desc']],
                    'insure' => $data['insure'] ?? 0,
                    'support_cod' => $data['cod'] ?? 0,
                    'enabled' => 1,
                    'print_bg' => !empty($data['print_bg']) ? addslashes($data['print_bg']) : '',
                    'config_lable' => !empty($data['config_lable']) ? addslashes($data['config_lable']) : '',
                    'print_model' => $data['print_model'] ?? '',
                ];
                return Shipping::query()->insert($other);
            }

            return false;
        }
    }

    /**
     * 卸载配送方式
     * @param int $shipping_id
     * @param array $row
     * @return bool
     */
    public static function uninstallShipping($shipping_id = 0, $row = [])
    {
        if (empty($shipping_id)) {
            return false;
        }

        /* 删除 shipping_fee 以及 shipping 表中的数据 */
        if ($row) {
            $shippingArea = ShippingArea::select('shipping_area_id')->where('shipping_id', $shipping_id);
            $shippingArea = BaseRepository::getToArrayGet($shippingArea);
            $shippingArea = BaseRepository::getKeyPluck($shippingArea, 'shipping_area_id');

            if (!empty($shippingArea)) {
                AreaRegion::whereIn('shipping_area_id', $shippingArea)->delete();
            }

            ShippingArea::where('shipping_id', $shipping_id)->delete();
            Shipping::where('shipping_id', $shipping_id)->delete();

            GoodsTransportExpress::where('shipping_id', $shipping_id)->delete();
            GoodsTransportTpl::where('shipping_id', $shipping_id)->delete();

            //删除上传的非默认快递单
            if (!empty($row['print_bg']) && !self::is_print_bg_default($row['print_bg'])) {
                @unlink(storage_public($row['print_bg']));
            }

            return true;
        }

        return false;
    }

    /**
     * 判断是否为默认安装快递单背景图片
     *
     * @param string $print_bg 快递单背景图片路径名
     * @return  Bool
     */
    public static function is_print_bg_default($print_bg = '')
    {
        $_bg = basename($print_bg);

        $_bg_array = explode('.', $_bg);

        if (count($_bg_array) != 2) {
            return false;
        }

        if (strpos('|' . $_bg_array[0], 'dly_') != 1) {
            return false;
        }

        $_bg_array[0] = ltrim($_bg_array[0], 'dly_');
        $list = explode('|', SHIP_LIST);

        if (in_array($_bg_array[0], $list)) {
            return true;
        }

        return false;
    }
}