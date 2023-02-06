<?php

namespace App\Services\Merchant;

use App\Models\CollectStore;
use App\Models\MerchantsShopInformation;
use App\Models\MerchantsStepsFields;
use App\Models\SellerCommissionBill;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class MerchantDataHandleService
{
    /**
     * 入驻店铺列表信息
     *
     * @param array $seller_id
     * @param array $data
     * @return array
     */
    public static function MerchantsShopInformationDataList($seller_id = [], $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = MerchantsShopInformation::select($data)->addSelect('user_id')->whereIn('user_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['user_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 店铺设置列表信息
     *
     * @param array $seller_id
     * @param array $data
     * @return array
     */
    public static function SellerShopinfoDataList($seller_id = [], $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = SellerShopinfo::select($data)->addSelect('ru_id')->whereIn('ru_id', $seller_id);

        $res = $res->with(['getMerchantsShopInformation' => function ($query) {
            $query->select('user_id', 'self_run');
        }]);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $row = collect($row)->merge($row['get_merchants_shop_information'])->except('get_merchants_shop_information')->all();
                $arr[$row['ru_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 店铺设置列表信息
     *
     * @param array $seller_id
     * @param array $data
     * @return array
     */
    public static function getSellerShopInfoShippingList($seller_id = [], $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = SellerShopinfo::select($data)->addSelect('ru_id')->whereIn('ru_id', $seller_id);

        $res = $res->with([
            'getShipping' => function ($query) {
                $query->select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $row = collect($row)->merge($row['get_shipping'])->except('get_shipping')->all();
                $arr[$row['ru_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 获取设置客服类型
     *
     * @return array|\Illuminate\Cache\CacheManager|int|mixed
     * @throws \Exception
     */
    public static function kfImSwitch()
    {
        $kf_im_switch = cache('kf_im_switch');
        $kf_im_switch = !is_null($kf_im_switch) ? $kf_im_switch : [];

        if (empty($kf_im_switch)) {
            $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
            $kf_im_switch = $kf_im_switch ? $kf_im_switch : 0;

            $cache_time = 60 * 60 * 24;
            cache()->put('kf_im_switch', $kf_im_switch, $cache_time);
        }

        return $kf_im_switch;
    }

    /**
     * 获取会员账单确认收货订单记录信息列表
     *
     * @param $seller_id
     * @param array $data
     * @return array
     */
    public static function getSellerCommissionBillDataList($seller_id, $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = SellerCommissionBill::select($data)->whereIn('seller_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['seller_id']][] = $row;
            }
        }

        return $arr;
    }

    /**
     * 会员入驻流程信息
     *
     * @param $seller_id
     * @param array $data
     * @return array
     */
    public static function getMerchantsStepsFieldsDataList($seller_id, $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = MerchantsStepsFields::select($data)
            ->whereIn('user_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['user_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 商家店铺信息列表
     *
     * @param array $seller_id
     * @param int $type
     * @param array $shopInfoList
     * @param array $merchantShopList
     * @return array
     * @throws \Exception
     */
    public static function getMerchantInfoDataList($seller_id = [], $type = 0, $shopInfoList = [], $merchantShopList = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $merSelf = new self();
        $shopInfoList = empty($shopInfoList) ? $merSelf->SellerShopinfoDataList($seller_id) : $shopInfoList;
        $merchantShopList = empty($merchantShopList) ? $merSelf->MerchantsShopInformationDataList($seller_id) : $merchantShopList;

        $cache_id = md5(serialize($seller_id)) . $type;
        $arr = cache()->remember('get_merchant_info_data_list' . $cache_id, config('shop.cache_time'), function () use ($seller_id, $type, $shopInfoList, $merchantShopList) {
            $arr = [];

            $crossCountryList = [];
            if (CROSS_BORDER === true) { // 跨境多商户
                $crossCountryIdList = BaseRepository::getKeyPluck($shopInfoList, 'cross_country_id');
                $crossCountryList = app(\App\Custom\CrossBorder\Services\CountryCrossDataHandleService::class)->getCountryDataList($crossCountryIdList, ['id', 'country_name', 'country_icon']);

                $crossWarehouseIdList = BaseRepository::getKeyPluck($shopInfoList, 'cross_warehouse_id');
                $crossWarehouseList = app(\App\Custom\CrossBorder\Services\CrossWarehouseDataHandleService::class)->getCrossWarehouseDataList($crossWarehouseIdList, ['id', 'name']);
            }

            foreach ($seller_id as $k => $v) {
                $shopinfo = $shopInfoList[$v] ?? [];

                if ($shopinfo) {
                    $shop_information = $merchantShopList[$v] ?? [];

                    $shop_information = BaseRepository::getArrayMerge($shopinfo, $shop_information);

                    $shop_information['shoprz_brand_name'] = isset($shop_information['shoprz_brand_name']) ? $shop_information['shoprz_brand_name'] : '';
                    $shop_information['rz_shop_name'] = isset($shop_information['rz_shop_name']) ? $shop_information['rz_shop_name'] : '';
                    $shop_information['self_run'] = isset($shop_information['self_run']) ? $shop_information['self_run'] : '';
                    $shop_information['shop_name_suffix'] = isset($shop_information['shop_name_suffix']) ? $shop_information['shop_name_suffix'] : '';
                    $shop_information['shop_close'] = isset($shop_information['shop_close']) ? $shop_information['shop_close'] : '';
                    $shop_information['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : 0;

                    $shopinfo['self_run'] = $shop_information['self_run']; //自营店铺传值

                    if (empty($shop_information)) {
                        $shop_information['shop_name'] = $shopinfo['shop_name'];
                    }

                    if ($type == 3) { //搜索店铺
                        $shop_information['shop_name'] = $shop_information['shoprz_brand_name'];
                        $shop_information['rz_shop_name'] = str_replace([lang('merchants.flagship_store'), lang('merchants.specialty_store'), lang('merchants.franchise_store')], '', $shop_information['rz_shop_name']);
                    }
                    if (isset($shopinfo['shopname_audit']) && $shopinfo['shopname_audit'] == 1) {
                        $check_sellername = $shopinfo['check_sellername'] ?? 0;

                        if ($check_sellername == 1) { //期望店铺名称
                            $shop_name = $shop_information['rz_shop_name'];
                        } elseif ($check_sellername == 2) {
                            $shop_name = $shopinfo['shop_name'];
                        } else {
                            if ($v > 0) {
                                if (!empty($shop_information['shoprz_brand_name'])) {
                                    $shop_information['shop_name'] = $shop_information['shoprz_brand_name'] . $shop_information['shop_name_suffix'];
                                    $shop_name = $shop_information['shop_name'];
                                } else {
                                    $shop_name = $shop_information['rz_shop_name'];
                                }
                            } else {
                                $shop_name = $shopinfo['shop_name'];
                            }
                        }
                    } else {
                        $shop_name = $shop_information['rz_shop_name']; //默认店铺名称
                    }

                    if ($type == 1) {
                        $arr[$v] = e($shop_name);
                    } elseif ($type == 2) {
                        $arr[$v] = $shopinfo;
                    } elseif ($type == 3) {
                        if (isset($shop_information['shop_name_suffix']) && !empty($shop_information['shop_name_suffix'])) {
                            if (strpos($shop_name, $shop_information['shop_name_suffix']) === false && $shopinfo['check_sellername'] == 0) {
                                $shop_name .= $shop_information['shop_name_suffix'];
                            }
                        }

                        $res = [
                            'shop_name' => e($shop_name),
                            'shop_name_suffix' => isset($shop_information['shop_name_suffix']) ?: '',
                            'shopinfo' => $shopinfo,
                            'shop_information' => $shop_information
                        ];

                        $arr[$v] = $res;
                    } else {
                        $shop_information['shop_name'] = e($shop_name);

                        if (empty($shop_information['shop_name'])) {
                            $shop_information['shop_name'] = $shop_information['rz_shop_name'];
                        }

                        $arr[$v] = $shop_information;
                    }

                    if ($type != 1) {
                        $crossCountry = $crossCountryList[$shopinfo['cross_country_id']] ?? [];

                        $arr[$v]['country_name'] = $crossCountry['country_name'] ?? '';
                        if ($crossCountry) {
                            $arr[$v]['country_icon'] = $crossCountry['country_icon'] ? app(DscRepository::class)->getImagePath($crossCountry['country_icon']) : '';
                        } else {
                            $arr[$v]['country_icon'] = '';
                        }

                        $crossWarehouse = $crossWarehouseList[$shopinfo['cross_warehouse_id']] ?? [];
                        $arr[$v]['cross_warehouse_name'] = $crossWarehouse['name'] ?? '';
                    }
                } else {
                    if ($type == 1) {
                        $arr[$v] = '';
                    } else {

                        $shop_information = $merchantShopList[$v] ?? [];

                        $arr[$v] = [
                            'shoprz_brand_name' => $shop_information['shoprz_brand_name'] ?? '',
                            'rz_shop_name' => $shop_information['rz_shop_name'] ?? '',
                            'self_run' => $shop_information['self_run'] ?? '',
                            'shop_name_suffix' => $shop_information['shop_name_suffix'] ?? '',
                            'shop_close' => $shop_information['shop_close'] ?? '',
                            'is_im' => $shop_information['is_im'] ?? '',
                            'country_name' => '',
                            'country_icon' => '',
                            'cross_warehouse_name' => '',
                            'ru_id' => 0,
                            'shop_name' => $shop_information['rz_shop_name'] ?? ''
                        ];
                    }
                }
            }

            return $arr;
        });

        return $arr;
    }

    /**
     * 店铺关注列表
     *
     * @param array $user_id
     * @param array $seller_id
     * @param array $data
     * @return array
     */
    public static function getCollectStoreDataList($user_id = [], $seller_id = [], $data = [])
    {
        $user_id = BaseRepository::getExplode($user_id);
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($user_id) || empty($seller_id)) {
            return [];
        }

        $user_id = $user_id ? array_unique($user_id) : [];
        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = CollectStore::select($data)->whereIn('ru_id', $seller_id)->whereIn('user_id', $user_id);
        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }
}
