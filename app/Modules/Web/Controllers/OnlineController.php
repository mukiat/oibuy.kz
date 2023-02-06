<?php

namespace App\Modules\Web\Controllers;

use App\Models\SellerShopinfo;
use App\Models\Users;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Goods\GoodsService;
use App\Services\Merchant\MerchantCommonService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * 在线客服
 */
class OnlineController extends InitController
{
    protected $goodsService;
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        GoodsService $goodsService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->goodsService = $goodsService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }

    public function index()
    {
        $act = addslashes(request()->input('act', ''));

        // 仓库ID
        $warehouse_id = $this->warehouseId();
        // 省份ID
        $area_id = $this->areaId();
        // 城市ID
        $area_city = $this->areaCity();
        // 买家ID
        $user_id = session('user_id', 0);

        assign_template();

        // 预处理店铺及商品信息
        $goods_id = (int)request()->input('goods_id', 0);
        $ru_id = (int)request()->input('ru_id', 0);
        if ($goods_id > 0) {
            $where = [
                'goods_id' => $goods_id,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];
            $goods = $this->goodsService->getGoodsInfo($where);
            $ru_id = $goods['user_id'];
        }

        /**
         * 店铺客服设置
         *      1、默认统一客服（按照自营店铺的客服模式）
         *      2、商家自行客服
         *
         * 客服模式（顺序优先级）
         *      1、店铺自定义外部客服链接地址
         *      2、店铺设置QQ客服
         *      3、店铺使用商城客服
         *
         * 店铺客服设置为"商家自行客服"，客服接待的店铺ID为店铺ID，否则为自营店铺ID(ru_id=0)
         */

        // 通过ru_id获取到店铺信息;
        $shop_information = $this->merchantCommonService->getShopName($ru_id);
        // 商家自行设置的客服类型
        $customer_service_type = $shop_information['kf_type'];

        // 是否统一客服类型
        $enable_type = config('shop.enable_customer_service');
        if ($enable_type == 0 || $ru_id == 0) {
            $customer_service_type = config('shop.customer_service_type');
        }

        // 客服模式
        if ($customer_service_type == 2) {
            /**
             * 处理QQ客服
             */
            if (empty($shop_information['kf_qq'])) {
                return '没有配置客服QQ';
            }
            //跳转qq链接
            $url = 'http://wpa.qq.com/msgrd?v=3&uin=' . trim($shop_information['kf_qq']) . '&site=qq&menu=yes';
            return "<script>window.location.href='{$url}';</script>";
        } elseif ($customer_service_type == 3) {
            /**
             * 处理自定义客服链接
             */
            $url = DscRepository::getServiceUrl($ru_id);
            if (empty($url)) {
                return '没有配置自定义客服URL';
            }
            $url = str_replace('&amp;', '&', $url);
            // 存在自定义客服链接,直接跳转
            return "<script>window.location.href='{$url}';</script>";
        }

        /**
         * 最后处理商城自有在线客服
         */
        if (class_exists('App\\Modules\\Chat\\Controllers\\IndexController')) {
            if (empty($user_id)) {
                return "<script>window.location.href='user.php';</script>";
            }

            load_helper('code');
            $dbhash = md5(config('app.key'));
            $user_token = [
                'user_id' => $user_id,
                'user_name' => session('user_name', ''),
                'hash' => md5(session('user_name', '') . date('YmdH') . $dbhash)
            ];

            $token = StrRepository::random();
            Cache::put($token, encrypt($user_token), Carbon::now()->addMinutes(1));

            return redirect()->route('kefu.index.index', ['t' => $token, 'ru_id' => $ru_id, 'goods_id' => $goods_id]);
        }

        if ($act == 'service') {
            $IM_menu = url('/') . '/online.php?act=service_menu';

            $basic_info = SellerShopinfo::where('ru_id', $ru_id)->first();
            $basic_info = $basic_info ? $basic_info->toArray() : [];

            if ($basic_info) {
                IM($basic_info['kf_appkey'], $basic_info['kf_secretkey']);

                if (empty($basic_info['kf_logo']) || $basic_info['kf_logo'] == 'http://') {
                    $basic_info['kf_logo'] = 'http://dsc-kf.oss-cn-shanghai.aliyuncs.com/dsc_kf/p16812444.jpg';
                }

                $this->smarty->assign('kf_appkey', $basic_info['kf_appkey']);
                $this->smarty->assign('kf_touid', $basic_info['kf_touid']);
                $this->smarty->assign('kf_logo', $basic_info['kf_logo']);
                $this->smarty->assign('kf_welcome_msg', $basic_info['kf_welcome_msg']);
            }

            //判断用户是否登入,登入了就登入登入用户,未登入就登入匿名用户;
            if ($user_id) {
                $user_info = Users::where('user_id', $user_id)->first();
                $user_info = $user_info ? $user_info->toArray() : [];

                $user_info['user_id'] = 'dsc' . $user_id;
                if (empty($user_info['user_picture'])) {
                    $user_logo = $this->dscRepository->getImagePath('dsc_kf/dsc_kf_user_logo.jpg');
                } else {
                    $user_logo = $this->dscRepository->getImagePath($user_info['user_picture']);
                }
            } else {
                $user_info['user_id'] = $user_id;
                $user_logo = $this->dscRepository->getImagePath('dsc_kf/dsc_kf_user_logo.jpg');
            }

            $this->smarty->assign('user_id', $user_info['user_id'] ?? 0);
            $this->smarty->assign('user_logo', $user_logo);
            $this->smarty->assign('IM_menu', $IM_menu);
            $this->smarty->assign('goods_id', $goods_id);

            return $this->smarty->display('chats.dwt');
        }

        /**
         * 左侧菜单
         */
        if ($act == 'service_menu') {
            return $this->smarty->display('chats_menu.dwt');
        }

        /*
         * 右侧菜单
         */
        if ($act == 'history') {
            $request = dsc_decode(request()->input('q', ''), true);

            $itemId = $request['itemsId'][0];//商品ID;
            $url = url('/') . '/';
            echo $current_url = request()->server('SERVER_NAME') . request()->server('REQUEST_URI');
            die;

            $where = [
                'goods_id' => $itemId,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];
            $goodsInfo = $this->goodsService->getGoodsInfo($where);

            echo <<<HTML
    {
    "code": "200",
    "desc": "powered by dscmall",
    "itemDetail": [
            {
                "userid": "{$request['userid']}",
                "itemid": "{$itemId}",
                "itemname": "{$goodsInfo['goods_name']}",
                "itempic": "{$url}{$goodsInfo['goods_thumb']}",
                "itemprice": "{$goodsInfo['shop_price']}",
                "itemurl": "{$current_url}",
                "extra": {}
            }
        ]
    }
HTML;
        }
    }
}
