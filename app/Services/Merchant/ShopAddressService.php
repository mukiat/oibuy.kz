<?php

namespace App\Services\Merchant;

use App\Models\ShopAddress;

/**
 * Class ShopAddressService
 * @package App\Services\Merchant
 */
class ShopAddressService
{
    /**
     * @var ShopAddress
     */
    private $shopAddress;

    /**
     * ShopAddressService constructor.
     * @param ShopAddress $shopAddress
     */
    public function __construct(ShopAddress $shopAddress)
    {
        $this->shopAddress = $shopAddress;
    }

    /**
     * 获取店铺全部地址
     * @param int $ru_id
     * @return array
     */
    public function getAddressByRuID($ru_id)
    {
        $result = $this->shopAddress->where('ru_id', $ru_id)->paginate();

        return collect($result)->toArray();
    }

    /**
     * 获取店铺一条地址
     * @param int $ru_id
     * @param int $address_id
     * @return array
     */
    public function getAddressById($ru_id, $address_id)
    {
        $result = $this->shopAddress->where('ru_id', $ru_id)->where('id', $address_id)->first();

        return collect($result)->toArray();
    }

    /**
     * 保存店铺地址
     * @param $address
     * @return mixed
     */
    public function createAddress($address)
    {
        return $this->shopAddress->insertGetId($address);
    }

    /**
     * 保存店铺地址
     * @param int $ru_id
     * @param int $address_id
     * @param array $address
     * @return mixed
     */
    public function updateAddress($ru_id, $address_id, $address)
    {
        return $this->shopAddress->where('ru_id', $ru_id)->where('id', $address_id)->update($address);
    }

    /**
     * 移除店铺地址
     * @param int $ru_id
     * @param array $address_id
     * @return int
     */
    public function removeAddressByIds($ru_id, $address_id)
    {
        return $this->shopAddress->where('ru_id', $ru_id)->whereIn('id', $address_id)->delete();
    }
}
