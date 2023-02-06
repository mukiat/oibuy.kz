<?php

namespace App\Modules\Admin\Controllers;

use App\Services\App\AppManageService;
use App\Services\Common\ConfigManageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class AppController
 * @package App\Modules\Admin\Controllers
 */
class AppController extends BaseController
{
    // 分页数量
    protected $page_num = 10;

    protected $appManageService;

    protected $configManageService;

    /**
     * AppController constructor.
     * @param AppManageService $appManageService
     * @param ConfigManageService $configManageService
     */
    public function __construct(
        AppManageService $appManageService,
        ConfigManageService $configManageService
    ) {
        $this->appManageService = $appManageService;
        $this->configManageService = $configManageService;
    }

    protected function initialize()
    {
        parent::initialize();

        L(lang('admin/app'));
        $this->assign('lang', L());

        // 初始化
        $this->init_params();
    }

    /**
     * app配置
     */
    public function index()
    {
        // 权限
        //$this->admin_priv('app_config');

        return $this->display();
    }

    /**
     * app 广告位管理
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ad_position_list(Request $request)
    {
        $position_id = $request->input('position_id', 0); // 广告位id
        $keywords = $request->input('search_keyword', ''); // 搜索广告位

        // 分页
        $filter['position_id'] = $position_id;
        $offset = $this->pageLimit(route('admin/app/ad_position_list', $filter), $this->page_num);

        $list = $this->appManageService->adPositionList($position_id, $keywords, $offset);
        $position_list = $list['list'] ?? [];
        $total = $list['total'] ?? 0;

        $this->assign('position_list', $position_list);
        $this->assign('page', $this->pageShow($total));
        return $this->display();
    }

    /**
     * 广告位信息
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ad_position_info(Request $request)
    {
        $position_id = $request->input('position_id', 0); // 广告位id

        $position_info = $this->appManageService->adPositionInfo($position_id);

        $this->assign('position_info', $position_info);
        return $this->display();
    }

    /**
     * 添加广告位
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function update_position(Request $request)
    {
        //数据验证
        $messages = [
            'required' => lang('admin/app.position_name_empty'),
        ];
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|string'
        ], $messages);

        $errors = $validator->errors();

        if ($errors && $errors->has('position_name')) {
            return $this->message($errors->first('position_name'), null, 2);
        }

        $res = $this->appManageService->updateAdPostion($request->all());

        if ($res) {
            return $this->message(lang('admin/app.update') . lang('admin/common.success'), route('admin/app/ad_position_list'));
        }

        return $this->message(lang('admin/app.update') . lang('admin/common.fail'), route('admin/app/ad_position_list'));
    }

    /**
     * 删除广告位
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_position(Request $request)
    {
        $position_id = $request->input('position_id', 0);

        // 查询广告位下是否有广告 如果有不能删除
        $exist = $this->appManageService->checkAd($position_id);
        if ($exist == true) {
            return response()->json(['error' => 1, 'msg' => lang('admin/app.forbid_delete_adp')]);
        }

        $res = $this->appManageService->deleteAdPosition($position_id);

        if ($res) {
            $json_result = ['error' => 0, 'msg' => lang('admin/app.delete') . lang('admin/common.success')];
        } else {
            $json_result = ['error' => 1, 'msg' => lang('admin/app.delete') . lang('admin/common.fail')];
        }

        return response()->json($json_result);
    }

    /**
     * app 广告列表管理
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ads_list(Request $request)
    {
        $position_id = $request->input('position_id', 0); // 广告位id
        $keywords = $request->input('search_keyword', ''); // 搜索广告

        // 分页
        $filter['position_id'] = $position_id;
        $offset = $this->pageLimit(route('admin/app/ads_list', $filter), $this->page_num);

        $list = $this->appManageService->adList($position_id, $keywords, $offset);
        $ad_list = $list['list'] ?? [];
        $total = $list['total'] ?? 0;

        $this->assign('ad_list', $ad_list);
        $this->assign('position_id', $position_id);
        $this->assign('page', $this->pageShow($total));
        return $this->display();
    }

    /**
     * 广告信息
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ads_info(Request $request)
    {
        $ad_id = $request->input('ad_id', 0); // 广告id
        $position_id = $request->input('position_id', 0); // 广告位id

        // 广告位
        $list = $this->appManageService->adPositionList();
        $position_list = $list['list'] ?? [];

        if (!empty($position_list)) {
            foreach ($position_list as $k => $value) {
                // 格式化广告位名称
                $position_list[$k]['position_name_format'] = addslashes($value['position_name']) . ' [' . $value['ad_width'] . 'x' . $value['ad_height'] . ']';
            }
        }

        // 广告
        $ads_info = $this->appManageService->adInfo($ad_id);

        // 远程图片
        $url_src = $ads_info['url_src'] ?? '';

        $this->assign('position_list', $position_list);
        $this->assign('position_id', $position_id);
        $this->assign('ads_info', $ads_info);
        $this->assign('url_src', $url_src);
        return $this->display();
    }

    /**
     * 添加广告
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function update_ads(Request $request)
    {
        //数据验证
        $messages = [
            'required' => lang('admin/app.ad_name_empty'),
        ];
        $validator = Validator::make($request->all(), [
            'ad_name' => 'required|string'
        ], $messages);

        $errors = $validator->errors();
        if ($errors && $errors->has('ad_name')) {
            return $this->message($errors->first('ad_name'), null, 2);
        }

        $data = $request->all();

        // 图片类型
        if (isset($data['media_type']) && $data['media_type'] == 0) {
            // 广告图片处理
            // 远程图片链接
            $img_url = $request->input('img_url');
            if (!is_null($img_url)) {
                $data['ad_code'] = $img_url;
            } else {
                $pic_path = $request->input('file_path');

                $file = $request->file('pic');
                if ($file && $file->isValid()) {
                    // 验证文件大小
                    if ($file->getSize() > 2 * 1024 * 1024) {
                        return $this->message(lang('file.file_size_limit'), null, 2);
                    }
                    // 验证文件格式
                    if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png'])) {
                        return $this->message(lang('file.not_file_type'), null, 2);
                    }
                    $result = $this->upload('data/attached/app', true);
                    if ($result['error'] > 0) {
                        return $this->message($result['message'], null, 2);
                    }

                    $data['ad_code'] = 'data/attached/app/' . $result['file_name'];
                } else {
                    $data['ad_code'] = $pic_path;
                }

                // 路径转换
                if (strtolower(substr($data['ad_code'], 0, 4)) == 'http') {
                    $data['ad_code'] = str_replace(url('/'), '', $data['ad_code']);
                }
                if (strtolower(substr($pic_path, 0, 4)) == 'http') {
                    // 编辑时 删除原图片
                    $pic_path = str_replace(url('/'), '', $pic_path);
                }
                $data['ad_code'] = str_replace('storage/', '', ltrim($data['ad_code'], '/'));
                $pic_path = str_replace('storage/', '', ltrim($pic_path, '/'));
            }

            if (!empty($data['ad_id'])) {
                // 删除原图片
                if ($data['ad_code'] && isset($pic_path) && $pic_path != $data['ad_code']) {
                    $pic_path = strpos($pic_path, 'no_image') == false ? $pic_path : ''; // 不删除默认空图片
                    $this->remove($pic_path);
                }
            }
        }

        // 文字类型
        if (isset($data['media_type']) && $data['media_type'] == 3) {
            // TODO
        }

        $res = $this->appManageService->updateAd($data);

        if ($res) {
            return $this->message(lang('admin/app.update') . lang('admin/common.success'), route('admin/app/ads_list'));
        }

        return $this->message(lang('admin/app.update') . lang('admin/common.fail'), route('admin/app/ads_list'));
    }

    /**
     * 修改广告状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function change_ad_status(Request $request)
    {
        $ad_id = $request->input('ad_id', 0);
        $status = $request->input('status', 0);

        $res = $this->appManageService->updateAdStatus($ad_id, $status);

        if ($res) {
            $json_result = ['error' => 0, 'msg' => lang('admin/app.update') . lang('admin/common.success')];
        } else {
            $json_result = ['error' => 1, 'msg' => lang('admin/app.update') . lang('admin/common.fail')];
        }

        return response()->json($json_result);
    }

    /**
     * 删除广告
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_ad(Request $request)
    {
        $ad_id = $request->input('ad_id', 0);

        $res = $this->appManageService->deleteAd($ad_id);

        if ($res) {
            $json_result = ['error' => 0, 'msg' => lang('admin/app.delete') . lang('admin/common.success')];
        } else {
            $json_result = ['error' => 1, 'msg' => lang('admin/app.delete') . lang('admin/common.fail')];
        }

        return response()->json($json_result);
    }

    /**
     * 获取配置信息
     */
    public function setting(Request $request)
    {
        $group_list = $this->configManageService->getSettingGroups('app_config');

        $this->assign('group_list', current($group_list)['vars']);

        return $this->display();
    }

    /**
     * 客户端管理
     */
    public function client_manage(Request $request)
    {
        $client_list = $this->appManageService->clientList();
        $this->assign('client_list', $client_list);

        return $this->display();
    }

    /**
     * 添加产品
     */
    public function client_product(Request $request)
    {
        $data = request()->input('data', []);
        $client_id = request()->input('client_id', 0);

        if (!empty($data)) {
            if (empty($data['name'])) {
                return response()->json(['status' => 0, 'msg' => lang('admin/app.client_name') . lang('admin/app.not_null')]);
            }

            // 是否重复
            if ($this->appManageService->checkClient($data, $client_id)) {
                return response()->json(['status' => 0, 'msg' => lang('admin/app.duplicate_client_name_or_appid')]);
            }
            $data['id'] = $client_id;
            $this->appManageService->updateAppClient($data);

            return response()->json(['status' => 1, 'msg' => lang('admin/app.handler') . lang('admin/common.success')]);
        } else {
            $client = ['id' => 0, 'name' => '', 'appid' => ''];
            $action_lang = lang('admin/app.add_client');
            if ($client_id > 0) {
                $action_lang = lang('admin/app.edit_client');
                $info = $this->appManageService->clientInfo($client_id);
                if (!empty($info)) {
                    $client = $info;
                }
            }
            $this->assign('client_action', $action_lang);
            $this->assign('client', $client);
        }

        return $this->display();
    }

    /**
     * 删除客户端
     */
    public function del_client(Request $request)
    {
        $client_id = $request->input('client_id', 0);

        $res = $this->appManageService->deleteClient($client_id);

        if ($res) {
            return $this->message(lang('admin/app.delete') . lang('admin/common.success'), route('admin/app/client_manage'));
        } else {
            return $this->message(lang('admin/app.delete') . lang('admin/common.fail'), null, 2);
        }
    }

    /**
     * 产品列表
     */
    public function client_product_list(Request $request)
    {
        $client_id = $request->input('client_id', 0);

        $act = $request->input('act', '');
        $is_ajax = $request->input('is_ajax', 0);

        if (!empty($act) && $act == 'change_show' && $is_ajax == 1) {
            $arr['is_show'] = $request->input('val', 0);
            $arr['product_id'] = $request->input('id', 0);

            $this->appManageService->updateAppClientProduct($arr);
            return response()->json(['content' => $arr['is_show'], 'error' => 0, 'msg' => '']);
        }

        // 分页
        $filter['client_id'] = $client_id;
        $offset = $this->pageLimit(route('admin/app/client_product_list', $filter), $this->page_num);

        $list = $this->appManageService->clientProductList($client_id, $offset);
        $product_list = $list['list'] ?? [];
        $total = $list['total'] ?? 0;

        $client_list = $this->appManageService->clientList();
        $this->assign('client_list', $client_list);
        $this->assign('client_product_list', $product_list);
        $this->assign('page', $this->pageShow($total));
        $this->assign('client_id', $client_id);

        $client_name = $this->appManageService->getClientName($client_id);
        $this->assign('client_name', $client_name);

        return $this->display();
    }

    /**
     * 产品详情
     */
    public function client_product_info(Request $request)
    {
        $data = request()->input('data', []);
        $client_id = request()->input('client_id', 0);
        $product_id = request()->input('product_id', 0);

        if ($client_id == 0) {
            return $this->message(lang('admin/app.miss_params'), null, 2);
        }

        if (!empty($data)) {
            if (empty($data['version_id'])) {
                return $this->message(lang('admin/app.version_code') . lang('admin/app.not_null'), null, 2);
            }

            if (empty($data['download_url'])) {
                return $this->message(lang('admin/app.download_url') . lang('admin/app.not_null'), null, 2);
            }

            if (empty($data['update_desc'])) {
                return $this->message(lang('admin/app.update_desc') . lang('admin/app.not_null'), null, 2);
            }

            // 是否重复
            if ($this->appManageService->checkClientProduct($data, $client_id, $product_id)) {
                return $this->message(lang('admin/app.duplicate_version_code_or_download_url'), null, 2);
            }

            $data['client_id'] = $client_id;
            $data['product_id'] = $product_id;

            $this->appManageService->updateAppClientProduct($data);

            return $this->message(lang('admin/app.handler') . lang('admin/common.success'), route('admin/app/client_product_list', ['client_id' => $client_id]));
        } else {
            $product_info = ['update_time' => $this->appManageService->getNowTime()];
            $action_lang = lang('admin/app.add_product');

            if ($product_id > 0) {
                $action_lang = lang('admin/app.edit_product');
                $info = $this->appManageService->clientProductInfo($product_id);
                if (!empty($info)) {
                    $product_info = $info;
                }
            }

            $client_name = $this->appManageService->getClientName($client_id);
            $this->assign('client_name', $client_name);
            $this->assign('product_action', $action_lang);
            $this->assign('product_info', $product_info);
            $this->assign('client_id', $client_id);
        }

        return $this->display();
    }

    /**
     * 删除客户端
     */
    public function del_client_product(Request $request)
    {
        $client_id = $request->input('client_id', 0);
        $product_id = $request->input('product_id', 0);

        $res = $this->appManageService->deleteClientProduct($product_id);

        if ($res) {
            $json_result = ['error' => 0, 'msg' => lang('admin/app.delete') . lang('admin/common.success')];
        } else {
            $json_result = ['error' => 1, 'msg' => lang('admin/app.delete') . lang('admin/common.fail')];
        }

        return response()->json($json_result);
    }
}
