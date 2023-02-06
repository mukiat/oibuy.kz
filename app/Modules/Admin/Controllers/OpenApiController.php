<?php

namespace App\Modules\Admin\Controllers;

use App\Models\OpenApi;
use App\Plugins\Dscapi\config\ApiConfig;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\OpenApi\OpenApiManageService;
use App\Services\User\UserDataHandleService;

/**
 * 商品分类管理程序
 */
class OpenApiController extends InitController
{
    protected $apiConfig;
    protected $openApiManageService;
    protected $dscRepository;

    public function __construct(
        ApiConfig $apiConfig,
        OpenApiManageService $openApiManageService,
        DscRepository $dscRepository
    )
    {
        $this->apiConfig = $apiConfig->getConfig();

        $this->openApiManageService = $openApiManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        } else {
            $_REQUEST['act'] = trim($_REQUEST['act']);
        }

        /* 检查权限 */
        admin_priv('open_api');

        /* 区分平台和商家api接口 */
        $type = request()->input('type', 0);
        $this->smarty->assign('type', $type);

        if ($type == 1) {
            $current = 'seller_api';
        } elseif ($type == 2) {
            $current = 'user_api';
        } else {
            $current = 'self_api';
        }

        $this->smarty->assign('menu_select', ['action' => '01_system', 'current' => $current]);
        /*------------------------------------------------------ */
        //-- 挨批列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['open_api']);
            $this->smarty->assign('form_act', 'insert');

            $open_api_list = $this->openApiManageService->openApiList();

            $this->smarty->assign('open_api_list', $open_api_list['open_api_list']);
            $this->smarty->assign('filter', $open_api_list['filter']);
            $this->smarty->assign('record_count', $open_api_list['record_count']);
            $this->smarty->assign('page_count', $open_api_list['page_count']);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_openapi_add'], 'href' => 'open_api.php?act=add&type=' . $open_api_list['filter']['type']]);

            /* 列表页面 */
            return $this->smarty->display('openapi_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- api列表
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $open_api_list = $this->openApiManageService->openApiList();

            $this->smarty->assign('open_api_list', $open_api_list['open_api_list']);
            $this->smarty->assign('filter', $open_api_list['filter']);
            $this->smarty->assign('record_count', $open_api_list['record_count']);
            $this->smarty->assign('page_count', $open_api_list['page_count']);

            $sort_flag = sort_flag($open_api_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('openapi_list.dwt'), '', ['filter' => $open_api_list['filter'], 'page_count' => $open_api_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 添加api
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_openapi_list'], 'href' => 'open_api.php?act=list&type=' . $type]);

            $api_list = $this->apiConfigList($type);

            if ($type == 1) {
                $sellerList = $this->openApiManageService->getSellerList();
                $this->smarty->assign('seller_list', $sellerList);
            } elseif ($type == 2) {
                $userList = $this->openApiManageService->getUserList();
                $this->smarty->assign('user_list', $userList);
            }

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['open_api']);
            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('api_list', $api_list);

            /* 列表页面 */
            return $this->smarty->display('openapi_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 编辑api
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit') {
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_openapi_list'], 'href' => 'open_api.php?act=list&type=' . $type]);

            $api = OpenApi::where('id', $id);
            $api = BaseRepository::getToArrayFirst($api);

            if ($type == 1) {
                $sellerList = MerchantDataHandleService::getMerchantInfoDataList([$api['ru_id'] ?? 0]);
                $api['shop_name'] = $sellerList[$api['ru_id'] ?? 0]['shop_name'] ?? '';
            } elseif ($type == 2) {
                $userList = UserDataHandleService::userDataList([$api['user_id'] ?? 0], ['user_id', 'user_name']);
                $api['user_name'] = $userList[$api['user_id'] ?? 0]['user_name'] ?? '';
            }

            $this->smarty->assign('api', $api);

            $action_code = isset($api['action_code']) && !empty($api['action_code']) ? explode(",", $api['action_code']) : '';

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['open_api']);
            $this->smarty->assign('form_act', 'update');

            $api_list = $this->apiConfigList($type);

            $api_data = $this->openApiManageService->getApiData($api_list, $action_code);
            $this->smarty->assign('api_list', $api_data);

            /* 列表页面 */
            return $this->smarty->display('openapi_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加api
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

            $other['name'] = empty($_POST['name']) ? '' : trim($_POST['name']);
            $other['app_key'] = empty($_POST['app_key']) ? '' : trim($_POST['app_key']);
            $other['is_open'] = empty($_POST['is_open']) ? 0 : intval($_POST['is_open']);
            $other['action_code'] = empty($_POST['action_code']) ? '' : implode(",", $_POST['action_code']);

            if ($id) {
                OpenApi::where('id', $id)->update($other);
                $href = 'open_api.php?act=edit&id=' . $id . '&type=' . $type;

                $lang_name = $GLOBALS['_LANG']['edit_success'];
            } else {

                $ru_id = request()->input('user_id', 0);

                if ($type > 0 && empty($ru_id)) {
                    $select_null = '';
                    if ($type == 1) {
                        $select_null = $GLOBALS['_LANG']['seller_select_null'];
                    } elseif ($type == 2) {
                        $select_null = $GLOBALS['_LANG']['user_select_null'];
                    }

                    $href = 'open_api.php?act=list&type=' . $type;
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => $href];
                    return sys_msg($select_null, 0, $link);
                } else {
                    if ($type == 1) {
                        $other['ru_id'] = $ru_id ? $ru_id : 0;
                    } elseif ($type == 2) {
                        $other['user_id'] = $ru_id ? $ru_id : 0;
                    }
                }

                $other['add_time'] = gmtime();
                OpenApi::insert($other);
                $href = 'open_api.php?act=list&type=' . $type;
                $lang_name = $GLOBALS['_LANG']['add_success'];
            }

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => $href];
            return sys_msg(sprintf($lang_name, htmlspecialchars(stripslashes($other['name']))), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 批量删除
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'batch_remove') {
            if (isset($_REQUEST['checkboxes'])) {
                $checkboxes = BaseRepository::getExplode($_REQUEST['checkboxes']);
                OpenApi::whereIn('id', $checkboxes)->delete();

                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'open_api.php?act=list&type=' . $type];
                return sys_msg($GLOBALS['_LANG']['remove_success'], 0, $link);
            } else {

                /* 提示信息 */
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'open_api.php?act=list&type=' . $type];
                return sys_msg($GLOBALS['_LANG']['no_select_user'], 0, $lnk);
            }
        }

        /*------------------------------------------------------ */
        //-- 删除api
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
            $name = OpenApi::where('id', $id)->value('name');
            $name = $name ? $name : '';

            OpenApi::where('id', $id)->delete();

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'open_api.php?act=list&type=' . $type];
            return sys_msg(sprintf($GLOBALS['_LANG']['remove_success'], $name), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- appKey
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'app_key') {
            $check_auth = check_authz_json('open_api');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $guid = sc_guid();
            $result['app_key'] = $guid;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 搜索店铺
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'search_seller') {
            $check_auth = check_authz_json('open_api');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $sellerList = $this->openApiManageService->getSellerList();
            $this->smarty->assign('seller_list', $sellerList);

            $result['content'] = $this->smarty->fetch('library/search_seller_list.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 搜索会员
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'search_user') {
            $check_auth = check_authz_json('open_api');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $userList = $this->openApiManageService->getUserList();
            $this->smarty->assign('user_list', $userList);

            $result['content'] = $this->smarty->fetch('library/search_user_list.lbi');
            return response()->json($result);
        }
    }

    /**
     * api接口地址列表
     *
     * @param int $type
     * @return array
     */
    private function apiConfigList($type = 0)
    {
        $sellerApi = [];
        if ($type == 1) {
            $sellerApi = [
                'category_seller', 'goods', 'product', 'order', 'goodstype'
            ];
        } elseif ($type == 2) {
            $sellerApi = [
                'user', 'order'
            ];
        }

        $lang = config('shop.lang') ?? '';
        $apiConfig = $this->apiConfig[$lang];

        foreach ($apiConfig as $key => $row) {
            if ($sellerApi && !in_array($row['cat'], $sellerApi)) {
                unset($apiConfig[$key]);
            }
        }

        $apiConfig = array_values($apiConfig);

        return $apiConfig;
    }
}
