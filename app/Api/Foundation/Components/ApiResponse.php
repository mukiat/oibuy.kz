<?php

namespace App\Api\Foundation\Components;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Trait ApiResponse
 * @package App\Api\Foundation\Components
 */
trait ApiResponse
{
    /**
     * 通过JWT加密用户数据
     * @param null $payload
     * @return string
     */
    public function JWTEncode($payload = null)
    {
        $key = config('jwt.key');

        $payload = $this->getPayload($payload);

        return JWT::encode($payload, $key);
    }

    /**
     * 通过JWT解密用户数据
     * @param $token
     * @return mixed
     */
    public function JWTDecode($token)
    {
        $key = config('jwt.key');

        try {
            $data = JWT::decode($token, $key, ['HS256']);

            return collect($data)->get('body');
        } catch (Exception $e) {
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
    public function authorization($token = null, $item = 'user_id', $header = 'token')
    {
        if (request()->hasHeader($header)) {
            $token = request()->header($header);
        } elseif (request()->has($header)) {
            $token = request()->get($header);
        }

        if (is_null($token)) {
            return 0;
        }

        $payload = $this->JWTDecode($token);

        return collect($payload)->get($item);
    }

    /**
     * 设置JWT数据的有效期
     * @param null $data
     * @return array
     */
    protected function getPayload($data = null)
    {
        $jwt = config('jwt');

        $jwt['payload']['exp'] = Carbon::now()->addDays($jwt['expires'])->timestamp;

        return array_merge($jwt['payload'], ['body' => $data]);
    }
}
