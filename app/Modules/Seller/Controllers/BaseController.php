<?php

namespace App\Modules\Seller\Controllers;

use App\Models\AdminUser;
use App\Repositories\Common\DscRepository;
use App\Services\Common\CommonManageService;
use Illuminate\Http\Request;

class BaseController extends InitController
{
    protected $ru_id = 0;
    protected $page_num = 10;
    protected $seller = [];
    protected $privilege_seller = [];
    protected $menu = [];
    protected $menu_select = [];
    protected $seller_info = [];

    protected function initialize()
    {
        parent::initialize();

        load_helper(['base', 'mobile']);

        // 加载公共语言包
        L(trans('admin/common'));
        L(trans('admin/wechat'));
        $this->assign('lang', L());

        // 查询商家管理员
        $seller = app(CommonManageService::class)->getSellerInfo();

        if (!empty($seller) && $seller['ru_id'] > 0) {
            $this->ru_id = $seller['ru_id'];
        }
        $this->seller = $seller; // 用于插件
        $this->assign('admin_info', $seller);
        $this->assign('ru_id', $this->ru_id);
        $this->assign('seller_name', $seller['user_name'] ?? '');

        //判断编辑个人资料权限
        $privilege_seller = 0;
        if (isset($seller['action_list']) && in_array('privilege_seller', explode(',', $seller['action_list']))) {
            $privilege_seller = 1;
        }
        $this->privilege_seller = $privilege_seller;
        $this->assign('privilege_seller', $privilege_seller);

        $menu = cache('seller_menu');
        if (is_null($menu)) {
            // 商家菜单列表
            $menu = set_seller_menu();
            foreach ($menu as $k => $v) {
                $menu[$k]['url'] = '../' . $v['url'];
                foreach ($v['children'] as $j => $val) {
                    $menu[$k]['children'][$j]['url'] = '../' . $val['url'];
                }
            }
            cache()->forever('seller_menu', $menu);
        }

        $this->menu = $menu;
        $this->assign('seller_menu', $menu);

        //商家后台logo
        $seller_logo = config('shop.seller_logo');
        if (!empty($seller_logo)) {
            $seller_logo = app(DscRepository::class)->getImagePath('assets/' . $seller_logo);
        } else {
            $seller_logo = __TPL__ . '/images/seller_logo.png';
        }
        $this->assign('seller_logo', $seller_logo);

        $this->seller_info = $seller;
        $this->assign('seller_info', $seller);
    }

    /**
     * 消息提示跳转页
     */
    protected function message()
    {
        $url = null;
        $type = '1';
        $seller = true;
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
    public function seller_admin_priv($priv_str)
    {
        $seller_id = (int)request()->session()->get('seller_id', 0);
        $action_list = AdminUser::where('user_id', $seller_id)->value('action_list');

        if ($action_list == 'all') {
            return true;
        }

        if (strpos(',' . $action_list . ',', ',' . $priv_str . ',') === false) {
            return $this->message(trans('admin/common.priv_error'), null, 2, true);
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
    protected function init_params()
    {
        $page_num = request()->cookie('page_size');
        $this->page_num = is_null($page_num) ? 10 : $page_num;
        $this->assign('page_num', $this->page_num);
    }

    /**
     * 匹配选择的菜单(商家后台)
     * @param string $url
     * @param array $list
     * @return array
     */
    protected function get_menu_arr($url = '', $list = [])
    {
        static $menu_arr = [];
        static $menu_key = null;
        foreach ($list as $key => $val) {
            if (is_array($val)) {
                $menu_key = $key;
                $this->get_menu_arr($url, $val);
            } else {
                if ($val == $url || strpos($url, $val) !== false) {
                    $menu_arr['action'] = $menu_key;
                    $menu_arr['current'] = $key;

                    // 当前模块主菜单语言包
                    $menu_arr['action_label'] = $GLOBALS['_LANG'][$menu_key] ?? '';
                    // 当前选择菜单语言包(包含子菜单)
                    $menu_arr['label'] = $GLOBALS['_LANG'][$key] ?? $menu_arr['action_label'];

                    // 当前主菜单与子菜单匹配  截取返回字符串最后一个下划线 _ 前面的字符: $key = 01_wechat_admin  $key_2 = 01_wechat
                    $key_2 = substr($key, 0, strrpos($key, "_"));
                    $menu_arr['current_2'] = $key_2;
                }
            }
        }

        return $menu_arr;
    }
}
