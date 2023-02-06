<?php

namespace App\Repositories\User;


use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

class UsersIdRepository
{

    /**
     * 获取会员ID [PC、手机通用]
     *
     * @return int|mixed
     */
    public static function getUsersId()
    {
        $self = new UsersIdRepository();
        $user_id = (int)session('user_id', 0);
        $user_id = $user_id ? $user_id : $self->dscAuthorization();

        $current_path = $self->current_path();

        if (in_array($current_path, [ADMIN_PATH, SELLER_PATH, STORES_PATH, SUPPLLY_PATH])) {
            $user_id = 0;
        }

        return $user_id;
    }

    /**
     * 通过JWT解密用户数据
     * @param $token
     * @return mixed
     */
    private function dscJWTDecode($token)
    {
        $key = config('jwt.key');

        try {
            $data = JWT::decode($token, $key, ['HS256']);

            return collect($data)->get('body');
        } catch (\Exception $e) {
            if (config('app.debug')) {
                Log::error('JWTDecode: ' . $e->getMessage());
            }
            return 0;
        }
    }

    /**
     * 返回用户数据的属性
     * @param null $token
     * @param string $item
     * @param string $header
     * @return mixed
     */
    private function dscAuthorization($token = null, $item = 'user_id', $header = 'token')
    {
        if (request()->hasHeader($header)) {
            $token = request()->header($header);
        } elseif (request()->has($header)) {
            $token = request()->get($header);
        }

        if (is_null($token)) {
            return 0;
        }

        $payload = $this->dscJWTDecode($token);

        return collect($payload)->get($item);
    }

    /**
     * 地址栏路径
     *
     * @return string
     */
    public static function current_path()
    {
        $current = url()->current();
        $current = explode('/', $current);

        $array_flip = array_flip($current);
        $adminCurrent = $current[$array_flip[ADMIN_PATH]];
        $sellerCurrent = $current[$array_flip[SELLER_PATH]];
        $storeCurrent = $current[$array_flip[STORES_PATH]];
        $supplierCurrent = $current[$array_flip[SUPPLLY_PATH]];

        if ($adminCurrent == ADMIN_PATH) {
            return $adminCurrent;
        } elseif ($sellerCurrent == SELLER_PATH) {
            return $sellerCurrent;
        } elseif ($storeCurrent == STORES_PATH) {
            return $storeCurrent;
        } elseif ($supplierCurrent == SUPPLLY_PATH) {
            return $supplierCurrent;
        }

        return '';
    }
}