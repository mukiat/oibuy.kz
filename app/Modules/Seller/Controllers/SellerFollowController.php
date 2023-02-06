<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Image;
use App\Models\SellerFollowList;
use App\Repositories\Common\DscRepository;
use App\Services\Common\CommonManageService;
use App\Services\Message\SellerFollowManageService;
use Illuminate\Support\Facades\Validator;


class SellerFollowController extends InitController
{
    protected $dscRepository;
    protected $sellerFollowManageService;
    protected $commonManageService;

    public function __construct(
        DscRepository $dscRepository,
        SellerFollowManageService $sellerFollowManageService,
        CommonManageService $commonManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->sellerFollowManageService = $sellerFollowManageService;
        $this->commonManageService = $commonManageService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = addslashes(trim(request()->input('act', 'list')));
        $act = $act ? $act : 'list';

        $adminru = $this->commonManageService->getAdminIdSeller();

        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        $this->smarty->assign('menu_select', ['action' => '19_merchants_store', 'current' => '02_seller_follow']);

        /*------------------------------------------------------ */
        //-- 获取所有二维码关注列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            /* 权限的判断 */
            admin_priv('seller_follow');

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['seller_follow']);

            /* 按钮 */
            $this->smarty->assign('action_link', ['href' => 'seller_follow.php?act=add', 'text' => $GLOBALS['_LANG']['follow_add'], 'class' => 'icon-plus']);

            $page = request()->get('page', 1);

            $this->smarty->assign('full_page', 1);

            $follow_list = $this->sellerFollowManageService->getSellerFollowList($adminru);

            $page_count_arr = seller_page($follow_list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('follow_list', $follow_list['list']);
            $this->smarty->assign('filter', $follow_list['filter']);
            $this->smarty->assign('record_count', $follow_list['record_count']);
            $this->smarty->assign('page_count', $follow_list['page_count']);

            $sort_flag = sort_flag($follow_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return $this->smarty->display('seller_follow_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $page = request()->get('page', 1);

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['seller_follow']);

            $follow_list = $this->sellerFollowManageService->getSellerFollowList($adminru);
            $page_count_arr = seller_page($follow_list, $page);

            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('follow_list', $follow_list['list']);
            $this->smarty->assign('filter', $follow_list['filter']);
            $this->smarty->assign('record_count', $follow_list['record_count']);
            $this->smarty->assign('page_count', $follow_list['page_count']);

            $sort_flag = sort_flag($follow_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('seller_follow_list.dwt'),
                '',
                ['filter' => $follow_list['filter'], 'page_count' => $follow_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 添加 编辑关注店铺二维码 页面
        /*------------------------------------------------------ */
        elseif ($act == 'add' || $act == 'edit') {
            admin_priv('seller_follow');

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);

            $this->smarty->assign('ur_here', $act == 'add' ? $GLOBALS['_LANG']['follow_add'] : $GLOBALS['_LANG']['follow_edit']);
            $this->smarty->assign('action_link', ['href' => 'seller_follow.php?act=list', 'text' => $GLOBALS['_LANG']['seller_follow'], 'class' => 'icon-reply']);

            $this->smarty->assign('form_act', $act == 'add' ? 'insert' : 'update');
            $this->smarty->assign('action', $act);

            $id = intval(request()->input('id', 0));

            $info = $this->sellerFollowManageService->getSellerFollowInfo($id);
            $this->smarty->assign('info', $info);

            return $this->smarty->display('seller_follow_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加 编辑关注店铺二维码 提交
        /*------------------------------------------------------ */
        elseif ($act == 'insert' || $act == 'update') {
            admin_priv('seller_follow');

            $id = intval(request()->input('id', 0));
            $name = e(request()->input('name', ''));
            $link_url = e(request()->input('link_url', ''));
            $desc = e(request()->input('desc', ''));

            // 数据验证
            $messages = [
                'name.required' => $GLOBALS['_LANG']['seller_follow_name_required'],
            ];
            $validator = Validator::make(request()->all(), [
                'name' => 'required|string|max:34',
            ], $messages);

            // 返回错误
            if ($validator->fails()) {
                $link[] = ['text' => $GLOBALS['_LANG']['seller_follow'], 'href' => 'seller_follow.php?act=add'];
                return sys_msg($validator->errors()->first(), 1, $link);
            }

            $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

            /* 处理图片 */
            $cover_pic_file = request()->file('cover_pic'); // 封面图
            $cover_pic = '';
            if (!empty($cover_pic_file) && $cover_pic_file->isValid()) {
                $cover_pic_upload = $image->upload_image($cover_pic_file, 'seller_follow');
                if ($cover_pic_upload == false) {
                    return sys_msg($image->error_msg);
                }
                $cover_pic = DATA_DIR . '/seller_follow/' . basename($cover_pic_upload);
                $this->dscRepository->getOssAddFile([$cover_pic]);
            }

            $qr_code_file = request()->file('qr_code'); // 二维码
            $qr_code = '';
            if (!empty($qr_code_file) && $qr_code_file->isValid()) {
                $qr_code_upload = $image->upload_image($qr_code_file, 'seller_follow');
                if ($qr_code_upload == false) {
                    return sys_msg($image->error_msg);
                }
                $qr_code = DATA_DIR . '/seller_follow/' . basename($qr_code_upload);
                $this->dscRepository->getOssAddFile([$qr_code]);
            }

            // 新增
            if ($act == 'insert') {
                $link[] = ['text' => $GLOBALS['_LANG']['follow_add'], 'href' => 'seller_follow.php?act=add'];
                /*检查视频号名称是否重复*/
                $is_only = SellerFollowList::where('name', $name)->count();
                if ($is_only > 0) {
                    return sys_msg($GLOBALS['_LANG']['seller_follow_name_exist'], 1, $link);
                }

                if (empty($cover_pic)) {
                    return sys_msg($GLOBALS['_LANG']['seller_follow_cover_pic_required'], 1, $link);
                }

                if (empty($qr_code)) {
                    return sys_msg($GLOBALS['_LANG']['seller_follow_qr_code_required'], 1, $link);
                }

                $values = [
                    'seller_id' => $adminru['ru_id'],
                    'name' => $name,
                    'desc' => $desc,
                    'cover_pic' => $cover_pic,
                    'qr_code' => $qr_code,
                    'link_url' => $link_url
                ];

                $this->sellerFollowManageService->insertSellerFollow($values);

                // 管理员日志
                admin_log($name, 'add', 'seller_follow');

                $link[] = ['text' => $GLOBALS['_LANG']['seller_follow'], 'href' => 'seller_follow.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['follow_add'] . $GLOBALS['_LANG']['success'], 0, $link);
            }

            // 编辑
            if ($act == 'update' && $id > 0) {
                /*检查视频号名称是否重复*/
                $is_only = SellerFollowList::where('id', '<>', $id)->where('name', $name)->count();
                if ($is_only > 0) {
                    $link[] = ['text' => $GLOBALS['_LANG']['seller_follow'], 'href' => 'seller_follow.php?act=edit&id=' . $id];
                    return sys_msg($GLOBALS['_LANG']['seller_follow_name_exist'], 1, $link);
                }

                $where = [
                    'id' => $id,
                    'seller_id' => $adminru['ru_id'],
                ];
                $values = [
                    'name' => $name,
                    'link_url' => $link_url,
                    'desc' => $desc,
                ];

                if (!empty($cover_pic)) {
                    // 删除原图
                    $old_cover_pic = SellerFollowList::where('id', $id)->value('cover_pic');
                    if ($old_cover_pic && stripos(substr($old_cover_pic, 0, 4), 'http') === false) {
                        $this->dscRepository->getOssDelFile([$old_cover_pic]);
                        @unlink(storage_public($old_cover_pic));
                    }

                    $values['cover_pic'] = $cover_pic;
                }
                if (!empty($qr_code)) {
                    // 删除原图
                    $old_qr_code = SellerFollowList::where('id', $id)->value('qr_code');
                    if ($old_qr_code && stripos(substr($old_qr_code, 0, 4), 'http') === false) {
                        $this->dscRepository->getOssDelFile([$old_qr_code]);
                        @unlink(storage_public($old_qr_code));
                    }

                    $values['qr_code'] = $qr_code;
                }

                $res = $this->sellerFollowManageService->updateSellerFollow($where, $values);
                if ($res) {
                    // 管理员日志
                    admin_log($name, 'edit', 'seller_follow');
                }

                $link[] = ['text' => $GLOBALS['_LANG']['seller_follow'], 'href' => 'seller_follow.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['follow_edit'] . $GLOBALS['_LANG']['success'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 删除关注店铺二维码
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('seller_follow');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);

            $info = SellerFollowList::where('id', $id)->first();
            if ($info) {
                // 删除本地图片
                if ($info->cover_pic && stripos(substr($info->cover_pic, 0, 4), 'http') === false) {
                    $this->dscRepository->getOssDelFile([$info->cover_pic]);
                    @unlink(storage_public($info->cover_pic));
                }
                if ($info->qr_code && stripos(substr($info->qr_code, 0, 4), 'http') === false) {
                    $this->dscRepository->getOssDelFile([$info->qr_code]);
                    @unlink(storage_public($info->qr_code));
                }

                SellerFollowList::where('id', $id)->delete();

                // 管理员日志
                admin_log($info->name, 'remove', 'seller_follow');
            }

            $url = 'seller_follow.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }
    }
}
