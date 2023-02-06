<?php

namespace App\Modules\Admin\Controllers;

use App\Modules\Admin\Services\AffiliateCouponsService;
use Illuminate\Http\Request;


class ConfigController extends BaseController
{
    /**
     * 推荐注册送优惠券
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Support\Facades\View|\Illuminate\View\View|string
     */
    public function affiliate_coupons(Request $request)
    {
        // 提交处理
        if ($request->isMethod('POST')) {
            $data = $request->input('data', '');

            if (empty($data)) {
                return $this->message(trans('admin/common.empty'), null, 2);
            }

            AffiliateCouponsService::saveShopConfig('affiliate_coupons', $data);

            // 清除配置缓存
            cache()->forget('shop_config');

            return $this->message(trans('admin/common.editor') . trans('admin/common.success'), route('admin.affiliate_coupons'));
        }

        // 查找配置
        $config = AffiliateCouponsService::getShopConfig();
        // 选择优惠券列表
        $select_coupons_list = AffiliateCouponsService::selectCouponsList(1, 50);

        $this->assign('data', $config);
        $this->assign('select_coupons_list', $select_coupons_list);
        $this->assign('page_title', trans('admin/affiliate_coupons.page_title'));
        return $this->display('admin.config.affiliate_coupons');
    }
}
