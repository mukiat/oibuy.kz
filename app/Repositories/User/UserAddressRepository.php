<?php

namespace App\Repositories\User;

use App\Models\UserAddress;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;


class UserAddressRepository
{
    /**
     * 获取用户默认收货地址
     *
     * @param int $user_id
     * @param array $columns
     * @return array
     */
    public static function getDefaultAddress($user_id = 0, $columns = [])
    {
        if (empty($user_id)) {
            return [];
        }

        $address_id = Users::where('user_id', $user_id)->value('address_id');
        $address = [];
        if ($address_id) {
            $address = UserAddress::where('address_id', $address_id);

            if (!empty($columns)) {
                $address = $address->select($columns);
            }

            $address = BaseRepository::getToArrayFirst($address);
        }

        return $address;
    }
}