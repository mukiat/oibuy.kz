<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\AdminUser;
use App\Models\Comment;
use App\Models\DiscussCircle;
use App\Models\Goods;
use App\Models\GoodsGallery;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\DiscussCircle\DiscussCircleManageService;
use App\Services\Merchant\MerchantCommonService;

/**
 * 用户评论管理程序
 */
class DiscussCircleController extends InitController
{
    protected $merchantCommonService;
    protected $discussCircleManageService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        DiscussCircleManageService $discussCircleManageService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->discussCircleManageService = $discussCircleManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        load_helper('goods', 'admin');

        $image = new Image(['bgcolor' => config('shop.bgcolor')]);

        /* act操作项的初始化 */
        $act = request()->get('act', 'list');

        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        /*------------------------------------------------------ */
        //-- 获取没有回复的评论列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            /* 检查权限 */
            admin_priv('discuss_circle');

            $this->smarty->assign('ur_here', __('admin::common.discuss_circle'));
            $this->smarty->assign('full_page', 1);

            $list = $this->discussCircleManageService->getDiscussList($adminru['ru_id']);

            $this->smarty->assign('discuss_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $this->smarty->assign('action_link', ['text' => __('admin::discuss_circle.discuss_add'), 'href' => 'discuss_circle.php?act=add']);

            return $this->smarty->display('discuss_list.dwt');
        }


        /*------------------------------------------------------ */
        //-- 主题添加页面
        /*------------------------------------------------------ */
        if ($act == 'add') {
            admin_priv('discuss_circle');

            /* 创建 html editor */
            create_html_editor('content');
            $this->smarty->assign('lang', BaseRepository::getArrayCollapse([__('admin::common'), __('admin::discuss_circle')]));
            $this->smarty->assign('ur_here', __('admin::discuss_circle.discuss_add'));
            $this->smarty->assign('action_link', ['href' => 'discuss_circle.php?act=list', 'text' => __('admin::common.discuss_circle')]);
            $this->smarty->assign('action', 'add');

            $this->smarty->assign('act', 'insert');
            $this->smarty->assign('cfg_lang', config('shop.lang'));

            return $this->smarty->display('discuss_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 主题添加的处理
        /*------------------------------------------------------ */
        if ($act == 'insert') {
            $data['goods_id'] = request()->get('goods_id', 0);
            $data['goods_id'] = $data['goods_id'] ?? 0;
            $data['dis_title'] = request()->get('dis_title', '');
            $data['dis_title'] = $data['dis_title'] ?? '';
            $data['dis_text'] = request()->get('content', '');
            $data['user_name'] = request()->get('user_name', '');
            $data['dis_type'] = request()->get('discuss_type', 0);
            $img_desc = request()->get('img_desc', 0);
            $img_file = request()->get('img_file', 0);

            $res = Users::where('user_name', $data['user_name'])->select('user_id', 'user_name');
            $user = BaseRepository::getToArrayFirst($res);

            if (empty($data['goods_id'])) {
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::discuss_circle.select_goods'), 2, $link);
            }

            if (count($user) <= 0) {
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::discuss_circle.type_name_exist'), 0, $link);
            }

            $data['add_time'] = gmtime();
            $data['user_id'] = $user['user_id'];

            $_FILES['img_url'] = isset($_FILES['img_url']) ? $_FILES['img_url'] : '';

            if ($_FILES['img_url']) {
                foreach ($_FILES['img_url']['error'] as $key => $value) {
                    if ($value == 0) {
                        if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
                            $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                            return sys_msg(__('admin::discuss_circle.invalid_img_url'), 0, $link);
                        }
                    } elseif ($value == 1) {
                        $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                        return sys_msg(__('admin::discuss_circle.img_url_too_big'), 0, $link);
                    } elseif ($_FILES['img_url']['error'] == 2) {
                        $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                        return sys_msg(__('admin::discuss_circle.img_url_too_big'), 0, $link);
                    }
                }

                // 相册图片
                foreach ($_FILES['img_url']['tmp_name'] as $key => $value) {
                    if ($value != 'none') {
                        if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
                            $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                            return sys_msg(__('admin::discuss_circle.invalid_img_url'), 0, $link);
                        }
                    }
                }
            }

            /* 插入数据库。 */
            $dis_id = DiscussCircle::insertGetId($data);

            /* 处理相册图片 */
            if ($_FILES['img_url']) {
                if (!empty($dis_id)) {
                    handle_gallery_image(0, $_FILES['img_url'], $img_desc, $img_file, 0, $dis_id, 'true');
                } else {
                    $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                    return sys_msg(__('admin::discuss_circle.dis_error'), 0, $link);
                }
            }

            /* 记录管理员操作 */
            admin_log($data['dis_title'], 'add', 'discussinsert');

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = __('admin::discuss_circle.discuss_add');
            $link[0]['href'] = 'discuss_circle.php?act=add';

            $link[1]['text'] = __('admin::discuss_circle.back_list');
            $link[1]['href'] = 'discuss_circle.php?act=list';

            return sys_msg(__('admin::common.add') . "&nbsp;" . $data['dis_title'] . "&nbsp;" . __('admin::common.attradd_succed'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 主题修改的处理
        /*------------------------------------------------------ */
        if ($act == 'update') {
            $dis_id = request()->get('dis_id', 0);

            if (empty($dis_id)) {
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::discuss_circle.discuss_exits'), 0, $link);
            }

            $data['dis_title'] = request()->get('dis_title', '');
            $data['dis_title'] = $data['dis_title'] ?? '';
            $data['dis_text'] = request()->get('content', '');
            $data['dis_type'] = request()->get('discuss_type', 1);
            $data['review_status'] = request()->get('review_status', 1);
            $data['review_content'] = request()->get('review_content', '');

            $data['add_time'] = gmtime();

            /* 插入数据库。 */
            DiscussCircle::where('dis_id', $dis_id)->update($data);
            /* 记录管理员操作 */
            admin_log($data['dis_title'], 'add', 'discussinsert');

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = __('admin::discuss_circle.discuss_edit');
            $link[0]['href'] = "discuss_circle.php?act=reply&id=$dis_id";

            $link[1]['text'] = __('admin::discuss_circle.back_list');
            $link[1]['href'] = 'discuss_circle.php?act=list';

            return sys_msg(__('admin::common.edit') . "&nbsp;" . $data['dis_title'] . "&nbsp;" . __('admin::common.attradd_succed'), 0, $link);
        }


        /*------------------------------------------------------ */
        //-- 翻页、搜索、排序
        /*------------------------------------------------------ */
        if ($act == 'query') {
            $list = $this->discussCircleManageService->getDiscussList($adminru['ru_id']);

            $this->smarty->assign('discuss_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('discuss_list.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 回复用户评论(同时查看评论详情        )
        /*------------------------------------------------------ */
        if ($act == 'reply') {
            /* 检查权限 */
            admin_priv('discuss_circle');

            $id = request()->get('id', 0);

            $discuss_info = [];
            $id_value = [];

            /* 获取评论详细信息并进行字符处理 */
            $res = DiscussCircle::where('dis_id', $id);
            $discuss_info = BaseRepository::getToArrayFirst($res);

            if (empty($discuss_info)) {
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::discuss_circle.discuss_exits'), 0, $link);
            }

            $discuss_info['dis_title'] = str_replace('\r\n', '<br />', htmlspecialchars($discuss_info['dis_title']));
            $discuss_info['dis_title'] = nl2br(str_replace('\n', '<br />', $discuss_info['dis_title']));
            $discuss_info['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $discuss_info['add_time']);

            //取得商品名称
            $goods = Goods::where('goods_id', $discuss_info['goods_id'])->select('goods_name', 'original_img');
            $goods = BaseRepository::getToArrayFirst($goods);

            $discuss_info['original_img'] = isset($goods['original_img']) ? $this->dscRepository->getImagePath($goods['original_img']) : '';
            $discuss_info['goods_name'] = $goods['goods_name'];

            //取得图片地址
            $imgs = GoodsGallery::where('dis_id', $discuss_info['dis_id']);
            $imgs = BaseRepository::getToArrayFirst($imgs);

            /* 获取管理员的用户名和Email地址 */
            $admin_info = AdminUser::where('user_id', session('admin_id'))->select('user_name', 'email');
            $admin_info = BaseRepository::getToArrayFirst($admin_info);

            /* 取得评论的对象(文章或者商品) */
            $id_value = Goods::where('goods_id', $discuss_info['goods_id'])->value('goods_name');

            /* 创建 html editor */
            $content = isset($discuss_info['dis_text']) ? $discuss_info['dis_text'] : '';
            create_html_editor('content', $content);


            $this->smarty->assign('imgs', $imgs);
            $this->smarty->assign('msg', $discuss_info); //评论信息
            $this->smarty->assign('admin_info', $admin_info);   //管理员信息
            $this->smarty->assign('act', 'update');  //评论的对象
            $this->smarty->assign('action', 'relpy');  // 仅查看

            $this->smarty->assign('ur_here', __('admin::discuss_circle.discuss_info'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.discuss_circle'),
                'href' => 'discuss_circle.php?act=list']);

            /* 页面显示 */

            return $this->smarty->display('discuss_info.dwt');
        }
        /*------------------------------------------------------ */
        //-- 处理 回复用户评论
        /*------------------------------------------------------ */
        if ($act == 'action') {
            admin_priv('discuss_circle');

            /* 获取IP地址 */
            $ip = $this->dscRepository->dscIp();

            $comment_id = request()->get('comment_id', 0);
            $data['email'] = request()->get('email', '');
            $data['content'] = request()->get('content', '');
            $data['add_time'] = gmtime();
            $data['ip_address'] = $ip;
            $data['status'] = 0;

            $send_email_notice = request()->get('send_email_notice', '');
            $remail = request()->get('remail', '');

            /* 获得评论是否有回复 */
            $res = Comment::where('parent_id', $comment_id)->select('comment_id', 'content', 'parent_id');
            $reply_info = BaseRepository::getToArrayFirst($res);

            if (!empty($reply_info['content'])) {
                $data['user_name'] = request()->get('user_name', '');
                /* 更新回复的内容 */
                Comment::where('comment_id', $reply_info['comment_id'])->update($data);
            } else {
                $data['comment_type'] = request()->get('comment_type', 0);
                $data['id_value'] = request()->get('id_value', 0);
                $data['user_name'] = session()->has('admin_name') ? trim(session('admin_name')) : '';
                $data['parent_id'] = $comment_id;

                /* 插入回复的评论内容 */
                Comment::insert($data);
            }

            /* 更新当前的评论状态为已回复并且可以显示此条评论 */
            $update_data = ['status' => 1];
            Comment::where('comment_id', $comment_id)->update($update_data);

            /* 邮件通知处理流程 */
            if (!empty($send_email_notice) or isset($remail)) {
                //获取邮件中的必要内容
                $res = Comment::where('comment_id', $comment_id)->select('user_name', 'email', 'content');
                $comment_info = BaseRepository::getToArrayFirst($res);

                /* 设置留言回复模板所需要的内容信息 */
                $template = get_mail_template('recomment');

                $this->smarty->assign('user_name', $comment_info['user_name']);
                $this->smarty->assign('recomment', $data['content']);
                $this->smarty->assign('comment', $comment_info['content']);
                $this->smarty->assign('shop_name', "<a href='" . $this->dsc->url() . "'>" . config('shop.shop_name') . '</a>');
                $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), gmtime()));

                $content = $this->smarty->fetch('str:' . $template['template_content']);

                /* 发送邮件 */
                if (CommonRepository::sendEmail($comment_info['user_name'], $comment_info['email'], $template['template_subject'], $content, $template['is_html'])) {
                    $send_ok = 0;
                } else {
                    $send_ok = 1;
                }
            }

            /* 清除缓存 */
            clear_cache_files();

            /* 记录管理员操作 */
            admin_log(addslashes(__('admin::discuss_circle.reply')), 'edit', 'users_comment');

            return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id&send_ok=$send_ok\n");
        }
        /*------------------------------------------------------ */
        //-- 更新评论的状态为显示或者        禁止
        /*------------------------------------------------------ */
        if ($act == 'check') {
            $comment_id = request()->get('id', 0);
            $check = request()->get('check', '');
            if ($check == 'allow') {
                /* 允许评论显示 */
                $data = ['status' => 1];
                Comment::where('comment_id', $comment_id)->update($data);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id\n");
            } else {
                /* 禁止评论显示 */
                $data = ['status' => 0];
                Comment::where('comment_id', $comment_id)->update($data);
                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 删除某一条评论
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('discuss_circle');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $dis_id = request()->get('dis_id', 0);

            DiscussCircle::where('dis_id', $id)->delete();
            admin_log('', 'remove', 'ads');

            if ($dis_id) {
                $query = "discuss_reply_query";
            } else {
                $query = "query";
            }
            $url = 'discuss_circle.php?act=' . $query . '&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 批量删除
        /*------------------------------------------------------ */
        if ($act == 'batch') {
            admin_priv('discuss_circle');

            $dis_id = request()->get('dis_id', 0);
            $checkboxes = request()->get('checkboxes', []);
            $type = request()->get('type', '');
            if (empty($checkboxes) || !is_array($checkboxes)) {
                return sys_msg(__('admin::discuss_circle.not_select_date'), 1);
            }

            $checkboxes = BaseRepository::getExplode($checkboxes);

            if ($type) {
                // 删除
                if ($type == 'batch_remove') {
                    DiscussCircle::whereIn('dis_id', $checkboxes)->delete();
                    clear_cache_files();

                    $action = ($type == 'batch_remove') ? 'batch_remove' : 'edit';
                    admin_log('', $action, 'adminlog');

                    if ($dis_id > 0) {
                        $href = "discuss_circle.php?act=user_reply&id=" . $dis_id;
                        $back_list = __('admin::discuss_circle.discuss_user_reply');
                    } else {
                        $href = "discuss_circle.php?act=list";
                        $back_list = __('admin::discuss_circle.back_list');
                    }

                    $link[] = ['text' => $back_list, 'href' => $href];
                    return sys_msg(sprintf(__('admin::discuss_circle.batch_drop_success'), count($checkboxes)), 0, $link);
                } // 审核
                elseif ($type == 'review_to') {

                    // review_status = 2审核未通过 3审核通过
                    $review_status = request()->get('review_status', 0);

                    $data = ['review_status' => $review_status];
                    $res = DiscussCircle::whereIn('dis_id', $checkboxes)->update($data);
                    if ($res) {
                        if ($dis_id > 0) {
                            $href = "discuss_circle.php?act=user_reply&id=" . $dis_id;
                            $back_list = __('admin::discuss_circle.discuss_user_reply');
                        } else {
                            $href = "discuss_circle.php?act=list";
                            $back_list = __('admin::discuss_circle.back_list');
                        }

                        $link[] = ['text' => $back_list, 'href' => $href];
                        return sys_msg(__('admin::discuss_circle.back_list'), 0, $link);
                    }
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 获取没有回复的评论列表
        /*------------------------------------------------------ */
        if ($act == 'user_reply') {
            /* 检查权限 */
            admin_priv('discuss_circle');

            $this->smarty->assign('ur_here', __('admin::discuss_circle.discuss_user_reply'));
            $this->smarty->assign('full_page', 1);

            $list = $this->discussCircleManageService->getDiscussUserReplyList();

            $this->smarty->assign('reply_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('dis_id', $list['dis_id']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('discuss_user_reply.dwt');
        }

        /*------------------------------------------------------ */
        //-- 翻页、搜索、排序
        /*------------------------------------------------------ */
        if ($act == 'discuss_reply_query') {
            $list = $this->discussCircleManageService->getDiscussUserReplyList();

            $this->smarty->assign('reply_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('dis_id', $list['dis_id']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('discuss_user_reply.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }
    }
}
