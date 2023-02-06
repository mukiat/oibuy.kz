<?php

namespace App\Api\Foundation\Components;

use App\Models\Users;
use Illuminate\Http\JsonResponse;

/**
 * Trait HttpResponse
 * @package App\Api\Foundation\Components
 */
trait HttpResponse
{
    /**
     * @var int
     */
    protected $errorCode = 0;

    /**
     * @return int
     */
    protected function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param $errorCode
     * @return $this
     */
    protected function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param array $header 发送的Header信息
     * @param array $extend 扩展参数
     * @return JsonResponse
     */
    protected function succeed($data, array $header = [], $extend = [])
    {
        return $this->response([
            'status' => 'success',
            'data' => $data,
            'extend' => $extend,
        ])->withHeaders($header);
    }

    /**
     * 返回异常数据到客户端
     * @param $message
     * @return JsonResponse
     */
    protected function failed($message)
    {
        return $this->response([
            'status' => 'failed',
            'errors' => [
                'code' => $this->getErrorCode(),
                'message' => $message,
            ],
        ]);
    }

    /**
     * 返回 Not Found 异常
     * @param string $message
     * @return JsonResponse
     */
    protected function responseNotFound($message = 'Not Found')
    {
        return $this->setErrorCode(404)->failed($message);
    }

    /**
     * 返回 Json 数据格式
     * @param $data
     * @return JsonResponse
     */
    protected function response($data)
    {
        // 客户端设备唯一ID
        $client_hash = request()->header('X-Client-Hash');

        if (is_null($client_hash) || empty($client_hash)) {
            $client_hash = session()->getId();
        }

        return response()->json($data)->withHeaders([
            'X-Client-Hash' => $client_hash
        ]);
    }

    /**
     * 检查用户是否存在
     * @param int $user_id
     * @return bool
     */
    protected function checkUserExist($user_id = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        $user = Users::query()->select('user_id')->where('user_id', $user_id);

        // 用户存在返回 true
        if ($user->count() > 0) {
            return true;
        }
        return false;
    }
}
