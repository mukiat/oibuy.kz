<?php

namespace App\Services\Merchant;

use App\Models\Goods;
use App\Models\SellerShopwindow;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Commission\CommissionService;
use App\Services\Order\OrderService;

class MerchantsCustomManageService
{
    protected $merchantCommonService;
    protected $orderService;
    protected $commissionService;
    protected $dscRepository;

    public function __construct(
        OrderService $orderService,
        MerchantCommonService $merchantCommonService,
        CommissionService $commissionService,
        DscRepository $dscRepository
    ) {
        $this->merchantCommonService = $merchantCommonService;
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->dscRepository = $dscRepository;
    }


    /**
     * 获取橱窗列表
     *
     * @access  public
     * @return  array
     */
    public function getSellerCustom($seller_theme)
    {
        $adminru = get_admin_ru_id();

        $res = SellerShopwindow::where('ru_id', $adminru['ru_id'])
            ->where('seller_theme', $seller_theme)
            ->where('win_type', 0);
        $win_list = BaseRepository::getToArrayGet($res);

        foreach ($win_list as $key => $val) {
            $win_list[$key]['seller_theme'] = lang('admin/merchants_custom.shop_models') . substr($val['seller_theme'], -1);
            $win_list[$key]['win_type_name'] = $val['win_type'] > 0 ? lang('admin/merchants_custom.commodity_cabinet') : lang('admin/merchants_custom.custom_content');
        }

        return $win_list;
    }

    //获取某橱窗信息
    public function getWinInfo($id)
    {
        $adminru = get_admin_ru_id();

        $res = SellerShopwindow::where('id', $id)->where('ru_id', $adminru['ru_id']);
        $res = BaseRepository::getToArrayFirst($res);
        return $res;
    }

    //获取橱窗商品
    public function getWinGoods($id)
    {
        $adminru = get_admin_ru_id();

        $res = SellerShopwindow::where('id', $id)->where('ru_id', $adminru['ru_id']);
        $win_info = BaseRepository::getToArrayFirst($res);

        if (!empty($win_info)) {
            if ($win_info['id'] > 0) {
                $goods_ids = $win_info['win_goods'];
                $goods = [];
                if ($goods_ids) {
                    $goods_ids = BaseRepository::getExplode($goods_ids);
                    $res = Goods::where('user_id', $adminru['ru_id'])->whereIn('goods_id', $goods_ids);
                    $goods = BaseRepository::getToArrayGet($res);
                }
                return $goods;
            }
        } else {
            return 'no_cc';
        }
    }

    //店铺橱窗N种样式
    public function winGoodsTypeList($type = 0)
    {
        $arr = [];
        for ($i = 1; $i <= $type; $i++) {
            $arr[$i]['value'] = $i;
            $arr[$i]['name'] = lang('admin/merchants_custom.style') . $i;
        }

        return $arr;
    }
}
