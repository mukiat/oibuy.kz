<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\File;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class GoodsLabelController
 * @package App\Modules\Admin\Controllers
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
    }

    /**
     * 商品标签列表
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\JsonResponse
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
        $offset = $this->pageLimit(route('admin/goodslabel/list', $filter), $this->page_num);

        // 列表
        $result = $this->goodsLabelService->getLabelList($filter, $offset);

        $list = $result['list'] ?? [];
        $total = $result['total'] ?? 0;

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('label_list', $list);
        $this->assign('filter', $filter);
        $this->assign('type', $type);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('admin.goodslabel.library.list_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display();
    }

    /**
     * 更新标签启用状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_status(Request $request)
    {
        $status = $request->get('val', 0); // 更新值
        $id = $request->get('id', 0); // 标签ID

        $status = $this->goodsLabelService->labelUpdateStatus(['status' => $status], $id);

        return $status === false ? response()->json(['error' => 1, 'msg' => trans('admin/goods_label.failed_update')]) : response()->json(['error' => 0]);
    }

    /**
     * 添加\编辑标签
     */
    public function update(Request $request)
    {
        // 提交处理
        if ($request->isMethod('POST')) {

            // 数据验证
            $rules = [
                'data.label_name' => 'required|string|max:34',
                'data.start_time' => 'required_if:data.type,1',
                'data.end_time' => 'required_if:data.type,1',
                'data.sort' => 'required|string'
            ];
            $messages = [
                'data.label_name.required' => trans('admin/goods_label.label_name_not_null'),
                'data.start_time.required_if' => trans('admin/goods_label.start_time_required'),
                'data.end_time.required_if' => trans('admin/goods_label.end_time_required'),
                'data.sort.required' => trans('admin/goods_label.sort_not_null'),
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            // 返回错误
            if ($validator->fails()) {
                return $this->message($validator->errors()->first(), null, 2);
            }

            $id = (int)$request->input('id', 0); // 存在则编辑 否则创建

            $data = $request->input('data');

            $type = $data['type'] ?? 0;

            if ($this->goodsLabelService->labelExists($data['label_name'], $id)) {
                return sys_msg(trans('admin/goods_label.label_name_exists'), 1);
            }

            // 图片处理
            $label_image = $request->file('label_image');

            if ($label_image && $label_image->isValid()) {
                $result = File::upload('data/goods/label', true, null, false);

                if ($result['error'] > 0) {
                    return $this->message($result['message'], null, 2);
                }

                $data['label_image'] = 'data/goods/label/' . $result['file_name'];
            } else {
                $data['label_image'] = $request->input('file_path');
            }

            if (empty($data['label_image'])) {
                return sys_msg(trans('admin/goods_label.label_image_not_null'), 1);
            }

            // oss图片处理
            $file_arr = [
                'label_image' => $data['label_image']
            ];

            $file_arr = app(DscRepository::class)->transformOssFile($file_arr);
            $data['label_image'] = $file_arr['label_image'];

            if ($type == 1) {
                // 处理时间
                $data['start_time'] = TimeRepository::getLocalStrtoTime($data['start_time']);
                $data['end_time'] = TimeRepository::getLocalStrtoTime($data['end_time']);
            }

            if (!empty($id)) {
                // 编辑
                $this->goodsLabelService->goodsLabelUpdate($id, $data);

                $message = trans('admin/goods_label.edit_success_notice');
            } else {
                // 添加
                $this->goodsLabelService->goodsLabelInstall($data);

                $message = trans('admin/goods_label.success_notice');
            }

            $link[] = ['href' => route('admin/goodslabel/list', ['type' => $type]), 'text' => trans('admin/goods_label.goods_label_list')];
            return sys_msg($message, 0, $link);
        }

        $id = (int)$request->input('id', 0); // 存在则编辑 否则创建
        $type = (int)$request->input('type', 0);

        // 编辑时默认传参
        $label_info = ['merchant_use' => 1, 'status' => 1]; // 默认值

        if ($id > 0) {
            $label_info = $this->goodsLabelService->getLabelInfo($id);
        }

        $this->assign('label_info', $label_info);
        $this->assign('id', $id);
        $this->assign('type', $type);
        return $this->display();
    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function drop(Request $request)
    {
        $id = $request->get('id', 0);

        if ($id > 0) {
            $is_delete = $this->goodsLabelService->labelDrop($id);
            if ($is_delete) {
                return response()->json(['error' => 0, 'msg' => trans('admin/goods_label.success_drop_notice')]);
            }
        }

        return response()->json(['error' => 1, 'msg' => trans('admin/goods_label.failed_drop_notice')]);
    }

    /**
     * 批量操作
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batch(Request $request)
    {
        if ($request->isMethod('POST')) {

            $handler = e($request->input('handler', '')); // 操作

            $label_list = $request->input('id');

            if (empty($label_list)) {
                return response()->json(['error' => 1, 'msg' => trans('admin/goods_label.attradd_failed')]);
            }

            if ($handler == 'use') {
                $this->goodsLabelService->batchUpdate($label_list, ['status' => 1]);
            } elseif ($handler == 'no_use') {
                $this->goodsLabelService->batchUpdate($label_list, ['status' => 0]);
            } elseif ($handler == 'drop') {
                // 批量删除
                $this->goodsLabelService->batchDrop($label_list);
            }

            return response()->json(['error' => 0, 'msg' => trans('admin/goods_label.batch_success_notice')]);
        }
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

        $ru_id = (int)$request->input('ru_id', -1); // 搜索商家
        $goods_keywords = e($request->input('goods_keywords', '')); // 搜索商品

        // 分页
        $filter['label_id'] = $label_id;
        $filter['type'] = $type;
        $filter['ru_id'] = $ru_id;
        $filter['goods_keywords'] = $goods_keywords;
        $offset = $this->pageLimit(route('admin/goodslabel/bind_goods', $filter), $this->page_num);

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

        // 筛选商家列表
        $seller_list = $this->goodsLabelService->seller_list();
        $this->assign('seller_list', $seller_list);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('admin.goodslabel.library.bind_goods_query');
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
        $filter['ru_id'] = 0;
        $offset = $this->pageLimit(route('admin/goodslabel/select_goods', $filter), $this->page_num);

        $result = $this->goodsLabelService->goodsListSearch($keywords, $cat_id, $brand_id, $offset, $filter);

        $total = $result['total'] ?? 0;
        $goods_list = $result['list'] ?? [];

        $this->assign('goods', $goods_list);
        $this->assign('filter', $filter);

        $cate_filter = set_default_filter_new(); //设置默认 分类，品牌列表 筛选

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
