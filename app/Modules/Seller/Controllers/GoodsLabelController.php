<?php

namespace App\Modules\Seller\Controllers;

use App\Services\Goods\GoodsLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class GoodsLabelController
 * @package App\Modules\Seller\Controllers
 */
class GoodsLabelController extends BaseController
{
    protected $goodsLabelService;

    public function __construct(
        GoodsLabelService $goodsLabelService
    )
    {
        $this->goodsLabelService = $goodsLabelService;
    }

    protected function initialize()
    {
        parent::initialize();

        // 初始化 每页分页数量
        $this->init_params();

        $left_menu = $GLOBALS['modules'];

        // 匹配选择的菜单列表
        $uri = request()->getRequestUri();
        $uri = ltrim($uri, '/');
        $menu_select = $this->get_menu_arr($uri, $left_menu);
        $this->assign('menu_select', $menu_select);

        // 当前位置
        $postion = ['ur_here' => $menu_select['label'] ?? ''];
        $this->assign('postion', $postion);
    }

    /**
     * 商品标签列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 0); // 标签类型 0 普通 1 悬浮

        $keywords = e($request->input('keywords', '')); // 搜索条件
        $status = $request->input('status', -1);

        // 分页
        $filter['type'] = $type;
        $filter['keywords'] = $keywords;
        $filter['status'] = $status;

        $filter['ru_id'] = $this->ru_id;
        $filter['status'] = 1;
        $filter['merchant_use'] = 1;
        $offset = $this->pageLimit(route('seller/goodslabel/list', $filter), $this->page_num);

        // 列表
        $result = $this->goodsLabelService->getLabelList($filter, $offset);

        $list = $result['list'] ?? [];
        $total = $result['total'] ?? 0;

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('label_list', $list);
        $this->assign('filter', $filter);
        $this->assign('type', $type);
        $this->assign('ru_id', $this->ru_id);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('seller.goodslabel.library.list_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display();
    }

    /**
     * 添加标签绑定商品
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function bind_goods(Request $request)
    {
        $handler = e($request->input('handler', '')); // 操作
        if ($request->isMethod('POST') && $handler == 'import') {
            // 数据验证
            $rules = [
                'label_id' => 'required|integer',
            ];
            $messages = [
                'label_id_required' => trans('admin/goods_label.label_id_required'),
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            // 返回错误
            if ($validator->fails()) {
                return response()->json(['error' => 1, 'msg' => $validator->errors()->first()]);
            }

            // 提交绑定
            $label_id = (int)$request->input('label_id', 0); // 标签id
            $type = (int)$request->input('type', 0); // 标签类型 0 普通 1 悬浮

            $goods_id = e($request->input('goods_id', '')); // 1,2,3

            if (!empty($goods_id)) {
                // 商品id转数组
                $goods_id_arr = is_array($goods_id) ? $goods_id : explode(',', $goods_id);

                $result = $this->goodsLabelService->bind_goods_to_label($label_id, $goods_id_arr);

                if ($result) {
                    return response()->json(['error' => 0, 'msg' => trans('admin/common.attradd_succed')]);
                }
            }

            return response()->json(['error' => 1, 'msg' => trans('admin/common.attradd_failed')]);
        }

        // 商品打标
        $label_id = (int)$request->input('label_id', 0); // 标签id
        $type = (int)$request->input('type', 0); // 标签类型 0 普通 1 悬浮

        $ru_id = $this->ru_id; // 当前商家id
        $goods_keywords = e($request->input('goods_keywords', '')); // 搜索商品

        // 分页
        $filter['label_id'] = $label_id;
        $filter['type'] = $type;
        $filter['ru_id'] = $ru_id;
        $filter['goods_keywords'] = $goods_keywords;
        $offset = $this->pageLimit(route('seller/goodslabel/bind_goods', $filter), $this->page_num);

        // 列表
        $result = $this->goodsLabelService->bindGoodsList($label_id, $filter, $offset);

        $list = $result['list'] ?? [];
        $total = $result['total'] ?? 0;

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('filter', $filter);

        $this->assign('ru_id', $ru_id);
        $this->assign('type', $type);
        $this->assign('label_id', $label_id);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('seller.goodslabel.library.bind_goods_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display();
    }

    /**
     * 解除标签商品绑定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unbind_goods(Request $request)
    {
        if ($request->isMethod('POST')) {

            // 数据验证
            $rules = [
                'label_id' => 'required|integer',
            ];
            $messages = [
                'label_id_required' => trans('admin/goods_label.label_id_required'),
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            // 返回错误
            if ($validator->fails()) {
                return response()->json(['error' => 1, 'msg' => $validator->errors()->first()]);
            }

            $label_id = (int)$request->input('label_id', 0); // 标签id
            $goods_id = $request->input('goods_id');

            if (!empty($goods_id)) {
                // 商品id转数组
                $goods_id_arr = is_array($goods_id) ? $goods_id : explode(',', $goods_id);

                $result = $this->goodsLabelService->unbind_goods_to_label($label_id, $goods_id_arr);

                if ($result) {
                    return response()->json(['error' => 0, 'msg' => trans('admin/common.attradd_succed')]);
                }
            }

            return response()->json(['error' => 1, 'msg' => trans('admin/common.attradd_failed')]);
        }
    }

    /**
     * 选择待打标商品
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function select_goods(Request $request)
    {
        load_helper(['base', 'mobile']);

        // 搜索
        $keywords = $request->input('keyword', '');

        $cat_id = $request->input('category_id', 0);
        $brand_id = $request->input('brand_id', 0);

        // 已选择商品id
        $select_goods_id = $request->input('select_goods_id', '');

        // 当前标签id
        $label_id = (int)$request->input('label_id', 0);
        $type = (int)$request->input('type', 0);

        $this->page_num = 24;

        // 筛选
        $filter['cat_id'] = $cat_id;
        $filter['brand_id'] = $brand_id;
        $filter['keyword'] = $keywords;
        $filter['select_goods_id'] = $select_goods_id;
        $filter['label_id'] = $label_id;
        $filter['type'] = $type;
        $filter['ru_id'] = $this->ru_id;
        $offset = $this->pageLimit(route('seller/goodslabel/select_goods', $filter), $this->page_num);

        $result = $this->goodsLabelService->goodsListSearch($keywords, $cat_id, $brand_id, $offset, $filter);

        $total = $result['total'] ?? 0;
        $goods_list = $result['list'] ?? [];

        $this->assign('goods', $goods_list);
        $this->assign('filter', $filter);

        $cate_filter = set_default_filter_new(0, 0, $this->ru_id); //设置默认 分类，品牌列表 筛选

        $this->assign('filter_category_level', 1); //分类等级 默认1
        $this->assign('filter_category_navigation', $cate_filter['filter_category_navigation']);
        $this->assign('filter_category_list', $cate_filter['filter_category_list']);
        $this->assign('filter_brand_list', $cate_filter['filter_brand_list']);
        $this->assign('cat_type_show', $cate_filter['cat_type_show']);

        $this->assign('page', $this->pageShow($total));
        $this->assign('page_num', $this->page_num);
        $this->assign('page_title', trans('admin/common.select_goods'));
        return $this->display();
    }
}
