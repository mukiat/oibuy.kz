<?php

namespace App\Repositories\Seller;

use App\Models\MerchantsShopInformation;
use App\Models\SellerShopinfo;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * Class SellerShopinfoRepository
 * @package App\Repositories\Seller
 */
class SellerShopinfoRepository
{
    /**
     * 获取店铺名称 包含平台
     *
     * @param int $ru_id
     * @param int $type
     * @return mixed|string
     * @throws \Exception
     */
    public static function getShopName($ru_id = 0, $type = 0)
    {
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id, $type);
        $merchant = $merchantList[$ru_id] ?? [];

        return $merchant['shop_name'] ?? '';
    }

    /**
     * 商家名称列表
     * @param int $limit
     * @return array
     */
    public static function getShopList($limit = 100)
    {
        $model = SellerShopinfo::query()->select('ru_id', 'shop_name', 'shopname_audit', 'check_sellername');
        $model = $model->limit($limit)
            ->orderBy('ru_id', 'ASC')
            ->get();
        $result = $model ? $model->toArray() : [];

        $list = [];
        if (!empty($result)) {
            foreach ($result as $k => $item) {
                $list[$k]['ru_id'] = $ru_id = $item['ru_id'] ?? 0;

                if ($ru_id == 0) {
                    $list[$k]['shop_name'] = $item['shop_name'] ?? '';
                } else {
                    $list[$k]['shop_name'] = self::getShopName($ru_id);
                }
            }
        }

        return $list;
    }

    /**
     * 通过user_id获取店铺信息
     * @param int $user_id
     * @return array
     */
    public function get_merchants_for_user($user_id = 0)
    {
        if (empty($user_id)) {
            return [];
        }
        $model = MerchantsShopInformation::where('user_id', $user_id);
        $model = $model->with([
            'sellershopinfo'
        ]);
        $result = $model->first();
        return $result ? $result->toArray() : [];
    }

    /**
     * 更新小商店开关
     * @param int $ru_id
     * @param int $switch_config
     * @return bool
     */
    public function update_switch_config($ru_id = 0, $switch_config = 0)
    {
        if (empty($ru_id)) {
            return false;
        }

        if (file_exists(MOBILE_WXSHOP)) {
            return SellerShopinfo::where('ru_id', $ru_id)->update(['switch_config' => $switch_config]);
        }
    }
}
