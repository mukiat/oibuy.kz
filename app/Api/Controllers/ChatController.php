<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\Users;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Merchant\MerchantCommonService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class ChatController
 * @package App\Api\Controllers
 */
class ChatController extends Controller
{
    /**
     * @var DscRepository
     */
    protected $dscRepository;

    /**
     * @var MerchantCommonService
     */
    protected $merchantCommonService;

    /**
     * ChatController constructor.
     * @param DscRepository $dscRepository
     * @param MerchantCommonService $merchantCommonService
     */
    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 客服链接
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        $shop_id = $request->get('shop_id', 0);
        $goods_id = $request->get('goods_id', 0);
        $type = $request->get('type', '');

        if ($type != 'weixin') {
            // 通过ru_id获取到店铺信息;
            $shop_information = $this->merchantCommonService->getShopName($shop_id);
            // 商家自行设置的客服类型
            $customer_service_type = $shop_information['kf_type'];

            // 是否统一客服类型
            $enable_type = config('shop.enable_customer_service');
            if ($enable_type == 0 || $shop_id == 0) {
                $customer_service_type = config('shop.customer_service_type');
            }

            // 客服模式
            if ($customer_service_type == 2) {
                /**
                 * 处理QQ客服
                 */
                if (empty($shop_information['kf_qq'])) {
                    return $this->succeed(['url' => '', 'error' => lang('user.qq_not_configured')]);
                }
                //返回qq链接
                $url = 'http://wpa.qq.com/msgrd?v=3&uin=' . trim($shop_information['kf_qq']) . '&site=qq&menu=yes';
                return $this->succeed(['url' => $url]);
            } elseif ($customer_service_type == 3) {
                /**
                 * 处理自定义客服链接
                 */
                $service_url = DscRepository::getServiceUrl($shop_id);
                if (empty($service_url)) {
                    return $this->succeed(['url' => '', 'error' => lang('user.url_not_configured')]);
                }
                // 存在自定义客服链接,直接返回
                return $this->succeed(['url' => $service_url]);
            }
        }

        // 自有客服
        $user_id = $this->uid;
        $user = Users::where('user_id', $user_id)->first();
        $user = $user ? $user->toArray() : [];

        if (empty($user)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $user_name = $user['user_name'] ?? '';

        $user_token = [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'hash' => md5($user_name . date('YmdH') . md5(config('app.key')))
        ];

        $token = StrRepository::random();
        Cache::put($token, encrypt($user_token), Carbon::now()->addMinutes(1));

        // app 客服
        if (!empty($type) && ($type == 'app' || $type == 'weixin')) {
            return $this->succeed(['t' => $token, 'ru_id' => $shop_id, 'goods_id' => $goods_id]);
        }

        // 自有客服
        if (class_exists('App\\Modules\\Chat\\Controllers\\IndexController')) {
            $chat_url = route('kefu.index.index', ['t' => $token, 'ru_id' => $shop_id, 'goods_id' => $goods_id]);

            return $this->succeed(['url' => $chat_url]);
        }

        return $this->succeed(['url' => '', 'error' => lang('user.customer_service_not_configured')]);
    }
}
