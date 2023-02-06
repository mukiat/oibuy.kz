<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryService;
use App\Services\Visual\VisualService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class VisualController
 * @package App\Api\Controllers
 */
class VisualController extends Controller
{
    protected $visualService;
    protected $dscRepository;

    public function __construct(
        VisualService $visualService,
        DscRepository $dscRepository
    )
    {
        $this->visualService = $visualService;
        $this->dscRepository = $dscRepository;
    }

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * APP
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $data = $this->visualService->index();

        return $this->succeed($data);
    }

    /**
     * 默认
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function default(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);
        $type = $request->input('type', 'index');
        $device = $request->input('device'); // 设备 h5 app wxapp
        $device = is_null($device) ? 'h5' : $device;

        $drp_user_audit = CommonRepository::drp_user_audit($this->uid);

        $cache_id = md5(serialize($request->all()));
        $data = cache()->remember('visual.default' . $cache_id . $drp_user_audit, config('shop.cache_time', 3600), function () use ($ru_id, $type, $device) {
            return $this->visualService->default($ru_id, $type, $device);
        });

        return $this->succeed($data);
    }

    /**
     * app广告
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function appnav(Request $request)
    {
        $data = $this->visualService->appNav();

        return $this->succeed($data);
    }

    /**
     * 公告
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function article(Request $request)
    {
        $cat_id = (int)$request->input('cat_id', 0);
        $num = (int)$request->input('num', 10);

        $drp_user_audit = CommonRepository::drp_user_audit($this->uid);

        $cache_id = md5(serialize($request->all()));
        $data = cache()->remember('visual.article' . $cache_id . $drp_user_audit, config('shop.cache_time', 3600), function () use ($cat_id, $num) {
            return $this->visualService->article($cat_id, $num);
        });

        return $this->succeed($data);
    }

    /**
     * 分类商品
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function product(Request $request)
    {
        $number = $request->input('number', 10);
        $type = $request->input('type');
        $ru_id = $request->input('ru_id', 0);
        $cat_id = $request->input('cat_id', 0);
        $brand_id = $request->input('brand_id', 0);

        $drp_user_audit = CommonRepository::drp_user_audit($this->uid);

        $cache_id = md5(serialize($request->all()));
        $data = cache()->remember('visual.product' . $cache_id . $drp_user_audit, config('shop.cache_time', 3600), function () use ($cat_id, $type, $ru_id, $number, $brand_id) {
            $visual_roduct = $this->visualService->product($this->uid, $cat_id, $type, $ru_id, $number, $brand_id, $this->warehouse_id, $this->area_id, $this->area_city, 1);
            return $visual_roduct;
        });

        return $this->succeed($data);
    }

    /**
     * 选中的商品
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function checked(Request $request)
    {
        $goods_id = $request->input('goods_id', 0);
        $ru_id = $request->input('ru_id', 0);

        $drp_user_audit = CommonRepository::drp_user_audit($this->uid);

        $cache_id = md5(serialize($request->all()));
        $data = cache()->remember('visual.checked' . $cache_id . $drp_user_audit, config('shop.cache_time', 3600), function () use ($goods_id, $ru_id) {
            return $this->visualService->checked($goods_id, $ru_id, $this->warehouse_id, $this->area_id, $this->area_city, $this->uid, 1);
        });

        return $this->succeed($data);
    }

    /**
     * 秒杀商品
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function seckill(Request $request)
    {
        $number = 10;
        if ($request->exists('number')) {
            $number = (int)$request->input('number', 10);
        } elseif ($request->exists('num')) {
            $number = (int)$request->input('num', 10);
        }

        $data = $this->visualService->seckill($number);

        return $this->succeed($data);
    }

    /**
     * 店铺街
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request)
    {
        $childrenNumber = (int)$request->input('childrenNumber', 0);
        $number = (int)$request->input('number', 10);

        if (empty($childrenNumber)) {
            $childrenNumber = 1000;
        }

        if (empty($number)) {
            $childrenNumber = 10;
        }

        $data = $this->visualService->store($childrenNumber, $number);

        return $this->succeed($data);
    }

    /**
     * 店铺街详情
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storein(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);

        $data = $this->visualService->storeIn($ru_id, $this->uid);

        return $this->succeed($data);
    }

    /**
     * 店铺街底部
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function storedown(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);

        $drp_user_audit = CommonRepository::drp_user_audit($this->uid);

        $cache_id = md5(serialize($request->all()));
        $data = cache()->remember('visual.storedown' . $cache_id . $drp_user_audit, config('shop.cache_time', 3600), function () use ($ru_id) {
            return $this->visualService->storeDown($ru_id);
        });

        return $this->succeed($data);
    }

    /**
     * 店铺街关注
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function addcollect(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);

        if (!$this->uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->visualService->addCollect($ru_id, $this->uid);

        return $this->succeed($data);
    }

    /**
     * 显示页面
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function view(Request $request)
    {
        $id = $request->input('id', 0);
        $type = $request->input('type', 'index');
        $default = $request->input('default', 0);
        $ru_id = $request->input('ru_id', 0);
        $number = $request->input('number', 10);
        $page_id = $request->input('page_id', 0);
        $device = $request->input('device', 'h5'); // 设备 h5 app wxapp

        $drp_user_audit = CommonRepository::drp_user_audit($this->uid);

        $cache_id = md5(serialize($request->all()));
        $data = cache()->remember('visual.view' . $ru_id . $id . $device . '.' . $cache_id . $drp_user_audit, config('shop.cache_time', 3600), function () use ($id, $type, $default, $ru_id, $number, $page_id, $device) {
            return $this->visualService->view($id, $type, $default, $ru_id, $number, $page_id, $device);
        });

        return $this->succeed($data);
    }

    /**
     * 首页可视化顶级分类
     *
     * @return JsonResponse
     */
    public function visualCategory()
    {
        $data = $this->visualService->getCategory();
        return $this->succeed($data);
    }

    /**
     * 首页二级分类
     *
     * @param Request $request
     * @param CategoryService $categoryService
     * @return JsonResponse
     * @throws Exception
     */
    public function visualSecondCategory(Request $request, CategoryService $categoryService)
    {
        $cat_id = $request->input('cat_id', 0);

        $data = ['category' => [], 'brand' => []];
        if (empty($cat_id)) {
            return $this->succeed($data);
        }

        $children = $categoryService->getCatListChildren($cat_id);

        $brand_data = $this->visualService->getCategoryBrandList($children);

        $category_data = $this->visualService->getSecondCategory($cat_id);

        $data['category'] = $category_data;
        $data['brand'] = $brand_data;
        return $this->succeed($data);
    }

    /**
     * 首页秒杀
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function visualSeckill(Request $request)
    {
        $id = $request->input('id', 0);
        $tomorrow = $request->get('tomorrow', 0);
        $data = $this->visualService->visualSeckill($id, $tomorrow);
        return $this->succeed($data);
    }

    /**
     * 拼团商品
     *
     * @return JsonResponse
     */
    public function visualTeamGoods()
    {
        $data = $this->visualService->getTeamGoods();
        return $this->succeed($data);
    }
}
