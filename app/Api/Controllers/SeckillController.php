<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Repositories\Common\DscEncryptRepository;
use App\Services\Activity\SeckillService;
use App\Services\Seckill\SeckillGoodsAttrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class SeckillController
 * @package App\Api\Controllers
 */
class SeckillController extends Controller
{
    protected $seckillService;

    /**
     * SeckillController constructor.
     * @param SeckillService $seckillService
     */
    public function __construct(
        SeckillService $seckillService
    )
    {
        $this->seckillService = $seckillService;
    }

    /**
     * 秒杀商品列表
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'id' => 'required|integer',
            'page' => 'required|integer',
            'size' => 'required|integer',
            'tomorrow' => 'required|integer',
        ]);

        $id = $request->get('id', 0); // 秒杀时间段id
        $page = $request->get('page', 1);
        $size = $request->get('size', 5);
        $tomorrow = $request->get('tomorrow', 0);

        $goodsList = $this->seckillService->seckill_goods_results($id, $page, $size, $tomorrow);

        return $this->succeed($goodsList);
    }

    /**
     * 返回时间列表
     * @param Request $request
     * @return JsonResponse
     */
    public function time(Request $request)
    {
        // 时间列表
        $list['list'] = $this->seckillService->getSeckillTime();

        // 秒杀广告位
        $list['banner_ads'] = $this->seckillService->seckill_ads('seckill', 6);

        return $this->succeed($list);
    }

    /**
     * 返回秒杀商品详情
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function detail(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'seckill_id' => 'required|integer',
            'tomorrow' => 'integer',
        ]);

        $seckill_id = $request->input('seckill_id', 0);
        $tomorrow = $request->input('tomorrow', 0);

        $uid = $this->authorization();

        $data = $this->seckillService->seckill_detail_mobile($uid, $seckill_id, $tomorrow);

        if (empty($data)) {
            return $this->responseNotFound();
        }

        return $this->succeed($data);
    }

    /**
     * 获得切换秒杀属性价格
     *
     * @param Request $request
     * @param SeckillGoodsAttrService $seckillGoodsAttrService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function attr_price(Request $request, SeckillGoodsAttrService $seckillGoodsAttrService)
    {
        $this->validate($request, [
            'goods_id' => 'required|integer',
            'num' => 'required|integer',
            'store_id' => 'integer',
            'seckill_goods_id' => 'required|integer',
        ]);

        $goods_id = (int)$request->input('goods_id', 0);
        $num = (int)$request->input('num', 0);
        $store_id = (int)$request->input('store_id', 0);
        $attr_id = $request->input('attr_id', []);
        $attr_id = DscEncryptRepository::filterValInt($attr_id);

        $province_id = intval(request()->get('province_id', 0));
        $city_id = intval(request()->get('city_id', 0));
        $district_id = intval(request()->get('district_id', 0));
        $street = intval(request()->get('street', 0));

        $region = [1, $province_id, $city_id, $district_id, $street];

        // 秒杀商品id
        $seckill_goods_id = (int)$request->input('seckill_goods_id', 0);

        $data = $seckillGoodsAttrService->goodsPropertiesPrice($this->uid, $goods_id, $attr_id, $num, $this->warehouse_id, $this->area_id, $this->area_city, $store_id, $region, $seckill_goods_id);

        return $this->succeed($data);
    }

    /**
     * 秒杀商品购买
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function buy(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'sec_goods_id' => 'required|integer',
            'number' => 'required|integer'
        ]);

        if (empty($this->uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $seckill_goods_id = (int)$request->input('sec_goods_id', 0);
        $number = (int)$request->input('number', 1);

        /* 取得规格 */
        $spec = $request->input('spec', []);

        $data = $this->seckillService->getSeckillBuy($this->uid, $seckill_goods_id, $number, $spec, $this->warehouse_id, $this->area_id, $this->area_city);

        return $this->succeed($data);
    }
}
