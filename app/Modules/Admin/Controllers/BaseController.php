<?php

namespace App\Modules\Admin\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;

class BaseController extends InitController
{
    protected $ru_id = 0;
    protected $page_num = 10;

    protected function initialize()
    {
        parent::initialize();

        load_helper(['base', 'mobile']);

        // 加载公共语言包
        L(trans('admin/common'));
    }

    /**
     * 消息提示跳转页
     */
    public function message()
    {
        $url = null;
        $type = '1';
        $seller = false;
        $waitSecond = 2;
        if (func_num_args() === 0) {
            $msg = request()->session()->get('msg', '');
            $type = request()->session()->get('type', 1);
            $url = request()->session()->get('url', null);
        } else {
            $argments = func_get_args();

            $msg = isset($argments['0']) ? $argments['0'] : '';
            $url = isset($argments['1']) ? $argments['1'] : $url;
            $type = isset($argments['2']) ? $argments['2'] : $type;
            $seller = isset($argments['3']) ? $argments['3'] : $seller;
            $waitSecond = isset($argments['4']) ? $argments['4'] : $waitSecond;
        }

        if (is_null($url)) {
            $url = 'javascript:history.back();';
        }
        if ($type == '2') {
            $title = trans('error_information');
        } else {
            $title = trans('prompt_information');
        }

        $data = [
            'title' => $title,
            'message' => $msg,
            'type' => $type,
            'url' => $url,
            'second' => $waitSecond,
        ];
        $this->assign('data', $data);

        $tpl = ($seller == true) ? 'seller/base.seller_message' : 'admin/base.message';
        return $this->display($tpl);
    }

    /**
     * 判断管理员对某一个操作是否有权限。
     *
     * 根据当前对应的action_code，然后再和用户session里面的action_list做匹配，以此来决定是否可以继续执行。
     * @param string $priv_str 操作对应的priv_str
     * @return true/false
     */
    public function admin_priv($priv_str = '')
    {
        $admin_id = (int)request()->session()->get('admin_id', 0);
        $action_list = AdminUser::where('user_id', $admin_id)->value('action_list');

        if ($action_list == 'all') {
            return true;
        }

        if (strpos(',' . $action_list . ',', ',' . $priv_str . ',') === false) {
            return $this->message(trans('admin/common.priv_error'), null, 2);
        } else {
            return true;
        }
    }

    /**
     * 修改分页数量
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse
     */
    public function set_page(Request $request)
    {
        if ($request->isMethod('POST')) {
            //修改每页数量
            $page_num = (int)$request->input('page_num', 0);
            if ($page_num > 0) {
                cookie()->queue('page_size', $page_num, 24 * 60 * 30);
                return response()->json(['status' => 1]);
            }

            return false;
        }
    }

    /**
     * 处理公共参数 如分页
     */
    public function init_params()
    {
        $page_num = request()->cookie('page_size');
        $this->page_num = is_null($page_num) ? 10 : $page_num;
        $this->assign('page_num', $this->page_num);
    }

    /**
     * ajax 点击分类获取下级分类列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select_category(Request $request)
    {
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        $cat_id = $request->input('cat_id', 0);

        //上级分类列表
        $parent_cat_list = get_select_category($cat_id, 1, true);
        $filter_category_navigation = get_array_category_info($parent_cat_list);
        $cat_nav = "";
        if ($filter_category_navigation) {
            foreach ($filter_category_navigation as $key => $val) {
                if ($key == 0) {
                    $cat_nav .= $val['cat_name'];
                } elseif ($key > 0) {
                    $cat_nav .= " > " . $val['cat_name'];
                }
            }
        } else {
            $cat_nav = trans('admin/common.please_category');
        }
        $result['cat_nav'] = $cat_nav;

        //分类级别
        $filter_category_level = count($parent_cat_list);
        if ($filter_category_level <= 3) {
            $filter_category_list = get_category_list($cat_id, 2);
        } else {
            $filter_category_list = get_category_list($cat_id, 0);
            $filter_category_level -= 1;
        }
        $this->assign('filter_category_level', $filter_category_level); //分类等级
        $this->assign('filter_category_navigation', $filter_category_navigation);
        $this->assign('filter_category_list', $filter_category_list);

        $data = compact('filter_category_navigation', 'filter_category_list', 'filter_category_level');
        $result['content'] = view('admin.base.select_category', $data)->render();

        return response()->json($result);
    }

    /**
     * ajax 点击获取品牌列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search_brand(Request $request)
    {
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        $goods_id = $request->input('goods_id', 0);

        $filter_brand_list = search_brand_list($goods_id);
        $this->assign('filter_brand_list', $filter_brand_list);

        $data = compact('filter_brand_list');
        $result['content'] = view('admin.base.select_brand_list', $data)->render();

        return response()->json($result);
    }
}
