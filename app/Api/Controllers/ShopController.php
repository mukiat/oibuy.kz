<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\CouponsUser;
use App\Models\SellerFollowList;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\CouponsService;
use App\Services\Category\CategoryService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Store\StoreStreetMobileService;
use Endroid\QrCode\Exception\InvalidPathException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ShopController
 * @package App\Api\Controllers
 */
class ShopController extends Controller
{
    protected $storeStreetMobileService;
    protected $categoryService;
    protected $couponsService;
    protected $dscRepository;

    public function __construct(
        StoreStreetMobileService $storeStreetMobileService,
        CategoryService $categoryService,
        CouponsService $couponsService,
        DscRepository $dscRepository
    )
    {
        $this->storeStreetMobileService = $storeStreetMobileService;
        $this->categoryService = $categoryService;
        $this->couponsService = $couponsService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 店铺分类列表
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function catList(Request $request)
    {
        $cat_id = $request->input('cat_id', 0);

        $data = $this->categoryService->getCategoryChild($cat_id);

        return $this->succeed($data);
    }

    /**
     * 分类店铺列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function catShopList(Request $request)
    {
        $cat_id = $request->input('cat_id', 0);
        $city_id = $request->input('city_id', 0) ?? 0;
        $size = $request->input('size', 10);
        $page = $request->input('page', 1);
        $sort = $request->input('sort', 'goods_id');
        $order = $request->input('order', 'DESC');
        $keywords = $request->input('keywords', '');

        $lat = $request->get('lat', 0);
        $lng = $request->get('lng', 0);

        $data = $this->storeStreetMobileService->getCatStoreList($cat_id, $this->warehouse_id, $this->area_id, $this->area_city, $size, $page, $sort, $order, $this->uid, $lat, $lng, $city_id, $keywords);

        return $this->succeed($data);
    }

    /**
     * 店铺商品列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function shopGoodsList(Request $request)
    {
        $ru_id = $request->input('store_id', 0);
        $cat_id = $request->input('cat_id', 0);

        $size = $request->input('size', 10);
        $page = $request->input('page', 1);
        $sort = $request->input('sort', 'goods_id');
        $order = $request->input('order', 'DESC');
        $type = $request->input('type', '');
        $store_best = $request->input('store_best', -1);

        $keywords = $request->input('keywords', '');
        $brand_id = $request->input('brand_id', 0);

        // 扩展其他字段
        $where_ext = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];

        if (in_array($store_best, [0, 1])) {
            $where_ext['store_best'] = $store_best;
        }

        // 筛选属性
        $filter_attr = [];

        if ($cat_id == 0) {
            $children = 0;
        } else {
            $children = $this->categoryService->getMerchantsCatListChildren($cat_id);
        }

        $data = $this->storeStreetMobileService->getStoreGoodsList($this->uid, $ru_id, $children, $keywords, $brand_id, $size, $page, $sort, $order, $filter_attr, $where_ext, $type);

        return $this->succeed($data);
    }

    /**
     * 店铺详情
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function shopDetail(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);
        $platform = e($request->input('platform', 'H5')); // 来源 H5或小程序 MP-WEIXIN

        $user_id = $this->authorization();

        $data = $this->storeStreetMobileService->StoreDetail($ru_id, $user_id, $platform);

        if (isset($data['shop_qrcode_file']) && $data['shop_qrcode_file']) {
            // 同步镜像上传到OSS
            $this->dscRepository->getOssAddFile([$data['shop_qrcode_file']]);
        }

        // 商家敏感信息脱敏
        $white_list = [
            'company_name',
            'business_license_id',
            'legal_person',
            'license_comp_adress',
            'registered_capital',
            'business_term',
            'busines_scope',
            'company_adress',
            'license_fileImg'
        ];

        foreach ($data['basic_info'] as $k => $v) {
            if (!in_array($k, $white_list)) {
                unset($data['basic_info'][$k]);
            }
        }

        $data['basic_info']['license_fileImg'] = $this->dscRepository->getImagePath($data['basic_info']['license_fileImg']);

        $drp_show_price = config('shop.drp_show_price') ?? 0;
        $drpUserAudit = cache('drp_user_audit_' . $this->uid) ?? 0;
        $drpUserAudit = $drp_show_price ? $drpUserAudit : 1;

        $data['is_ru_tel'] = $drpUserAudit ? 1 : 0;

        return $this->succeed($data);
    }

    /**
     * 店铺品牌
     * @param Request $request
     * @return JsonResponse
     */
    public function shopBrand(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);

        $data = $this->storeStreetMobileService->StoreBrand($ru_id);

        return $this->succeed($data);
    }

    /**
     * 附近地图
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function map(Request $request)
    {
        $lat = $request->get('lat', '31.22928');
        $lng = $request->get('lng', '121.40966');

        $data = $this->storeStreetMobileService->StoreMap($lat, $lng);

        return $this->succeed($data);
    }

    /**
     * 店铺优惠券
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function coupons(Request $request)
    {
        $ru_id = $request->get('ru_id', 0);

        $user_id = $this->authorization();

        $cou_data = $this->couponsService->getCouponsList([1, 2, 3, 4, 5], '', 'cou_id', 'desc', 0, 10, []);

        if (!empty($cou_data) && isset($cou_data)) {
            foreach ($cou_data as $key => $value) {
                if ($value['ru_id'] != $ru_id) {
                    unset($cou_data[$key]);
                    continue;
                }

                $rec_id = CouponsUser::where('is_delete', 0)->where('user_id', $user_id)->where('cou_id', $value['cou_id'])->value('uc_id');
                if (isset($rec_id) && !empty($rec_id)) {
                    unset($cou_data[$key]);
                    continue;
                }

                $cou_data[$key]['cou_start_time'] = TimeRepository::getLocalDate("Y-m-d", $value['cou_start_time']);
                $cou_data[$key]['cou_end_time'] = TimeRepository::getLocalDate("Y-m-d", $value['cou_end_time']);
            }
        }

        return $this->succeed($cou_data);
    }

    /**
     * 店铺关注二维码
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function follow(Request $request)
    {
        $ru_id = $request->get('ru_id', 0);
        $id = $request->get('fid', 0);

        $row = SellerFollowList::where('seller_id', $ru_id)->where('id', $id);
        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {

            $click_count = $row['click_count'] + 1;
            SellerFollowList::where('id', $row['id'])->update([
                'click_count' => $click_count
            ]);

            $row['qr_code'] = $this->dscRepository->getImagePath($row['qr_code']);
            $row['cover_pic'] = $this->dscRepository->getImagePath($row['cover_pic']);
            $row['click_count'] = $click_count;
        }

        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);
        $row['merchant'] = $merchantList[$ru_id] ?? [];

        return $this->succeed($row);
    }
}
