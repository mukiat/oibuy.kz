<?php

namespace App\Modules\Admin\Controllers;

use App\Entities\Article;
use App\Models\AdminUser;
use App\Models\Comment;
use App\Models\CommentImg;
use App\Models\Goods;
use App\Models\IntelligentWeight;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Comment\CommentManageService;
use App\Services\Store\StoreCommonService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * 用户评论管理程序
 */
class CommentManageController extends InitController
{
    protected $commentManageService;
    protected $dscRepository;
    protected $storeCommonService;

    public function __construct(
        DscRepository $dscRepository,
        CommentManageService $commentManageService,
        StoreCommonService $storeCommonService
    )
    {
        $this->commentManageService = $commentManageService;
        $this->dscRepository = $dscRepository;
        $this->storeCommonService = $storeCommonService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = e(request()->get('act', 'list'));

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
            admin_priv('comment_priv');

            //商家单个权限 ecmoban模板堂 start
            $comment_edit_delete = get_merchants_permissions($this->action_list, 'comment_edit_delete');
            $this->smarty->assign('comment_edit_delete', $comment_edit_delete); //退换货权限
            //商家单个权限 ecmoban模板堂 end

            $this->smarty->assign('ur_here', __('admin::common.05_comment_manage'));
            $this->smarty->assign('full_page', 1);

            $list = $this->commentManageService->getCommentList($adminru['ru_id']);

            $this->smarty->assign('comment_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);
            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()));


            return $this->smarty->display('comment_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 翻页、搜索、排序
        /*------------------------------------------------------ */
        if ($act == 'query') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $list = $this->commentManageService->getCommentList($adminru['ru_id']);

            $this->smarty->assign('comment_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            //商家单个权限 ecmoban模板堂 start
            $comment_edit_delete = get_merchants_permissions($this->action_list, 'comment_edit_delete');
            $this->smarty->assign('comment_edit_delete', $comment_edit_delete); //退换货权限
            //商家单个权限 ecmoban模板堂 end

            return make_json_result($this->smarty->fetch('comment_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 修改状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_status') {
            $check_auth = check_authz_json('comment_priv');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $comment_id = request()->get('id', 0);
            $status = request()->get('val', 0);
            $res = Comment::where('comment_id', $comment_id)->update(['status' => $status]);
            if ($res) {
                $goods_id = Comment::where('comment_id', $comment_id)->value('id_value');
                $goods_id = $goods_id ? $goods_id : 0;
                if ($status == 1) {
                    IntelligentWeight::where('goods_id', $goods_id)->increment('goods_comment_number');
                } else {
                    IntelligentWeight::where('goods_id', $goods_id)->where('goods_comment_number', '>', 0)->decrement('goods_comment_number');
                }
                update_goods_weights($goods_id); // 更新权重值

                clear_cache_files();
                return make_json_result($status);
            }
        }
        /*------------------------------------------------------ */
        //-- 回复用户评论(同时查看评论详情        )
        /*------------------------------------------------------ */
        elseif ($act == 'reply') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $comment_id = request()->get('id', 0);
            $send_ok = addslashes(request()->get('send_ok', ''));

            /* 获取评论详细信息并进行字符处理 */
            $comment_info = Comment::where('comment_id', $comment_id)->first();
            $comment_info = $comment_info ? $comment_info->toArray() : [];

            $comment_info['content'] = str_replace('\r\n', '<br />', htmlspecialchars($comment_info['content']));
            $comment_info['content'] = nl2br(str_replace('\n', '<br />', $comment_info['content']));
            $comment_info['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $comment_info['add_time']);

            //晒单图片
            $img_list = BaseRepository::getToArrayGet(CommentImg::where('comment_id', $comment_id));
            if ($img_list) {
                foreach ($img_list as $key => $row) {
                    $img_list[$key]['comment_img'] = $this->dscRepository->getImagePath($row['comment_img']);
                    $img_list[$key]['img_thumb'] = $this->dscRepository->getImagePath($row['img_thumb']);
                }
            }

            $comment_info['img_list'] = $img_list;

            /* 获取管理员的用户名和Email地址 */
            $admin_info = AdminUser::where('user_id', session('admin_id'));
            $admin_info = BaseRepository::getToArrayFirst($admin_info);

            /* 获得评论回复内容 */
            $reply_info = Comment::where('parent_id', $comment_id)
                ->where('single_id', '0')
                ->where('dis_id', '0')
                ->where('user_id', session('admin_id'))
                ->where('user_name', $admin_info['user_name'])
                ->where('ru_id', $adminru['ru_id']);

            $reply_info = BaseRepository::getToArrayFirst($reply_info);

            if (empty($reply_info)) {
                $reply_info['content'] = '';
                $reply_info['add_time'] = '';
            } else {
                $reply_info['content'] = nl2br(htmlspecialchars($reply_info['content']));
                $reply_info['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $reply_info['add_time']);
            }

            /* 取得评论的对象(文章或者商品) */
            if ($comment_info['comment_type'] == 0) {
                $id_value = Goods::where('goods_id', $comment_info['id_value'])->value('goods_name');
            } else {
                $id_value = Article::where('article_id', $comment_info['id_value'])->value('title');
            }
            $id_value = $id_value ? $id_value : '';

            if ($comment_info && config('shop.show_mobile') == 0) {
                $comment_info['email'] = $this->dscRepository->stringToStar($comment_info['email']);
                $comment_info['user_name'] = $this->dscRepository->stringToStar($comment_info['user_name']);
            }

            if ($admin_info && config('shop.show_mobile') == 0) {
                $admin_info['email'] = $this->dscRepository->stringToStar($admin_info['email']);
            }

            /* 模板赋值 */
            $this->smarty->assign('msg', $comment_info); //评论信息
            $this->smarty->assign('admin_info', $admin_info);   //管理员信息
            $this->smarty->assign('reply_info', $reply_info);   //回复的内容
            $this->smarty->assign('id_value', $id_value);  //评论的对象
            $this->smarty->assign('send_fail', !empty($send_ok));

            $this->smarty->assign('ur_here', __('admin::comment_manage.comment_info'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.05_comment_manage'), 'href' => 'comment_manage.php?act=list']);

            /* 页面显示 */

            return $this->smarty->display('comment_info.dwt');
        }
        /*------------------------------------------------------ */
        //-- 处理 回复用户评论
        /*------------------------------------------------------ */
        elseif ($act == 'action') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $email = request()->get('email', '');
            $remail = request()->get('remail', '');
            $user_name = request()->get('user_name', '');
            $content = request()->get('content', '');
            $send_email_notice = request()->get('send_email_notice', '');
            $comment_type = request()->get('comment_type', 3);//评论类型3代表管理员回复
            $id_value = request()->get('id_value', 0);

            /* 获取管理员的用户名和Email地址 */
            $admin_info = AdminUser::where('user_id', session('admin_id'))->first();

            /* 获取IP地址 */
            $ip = $this->dscRepository->dscIp();

            $comment_id = request()->get('comment_id', 0);
            $comment_info = Comment::where('comment_id', $comment_id)->where('ru_id', $adminru['ru_id'])->first();

            /* 获得评论是否有回复 */
            $reply_info = Comment::where('parent_id', $comment_info['comment_id'])
                ->where('single_id', '0')
                ->where('dis_id', '0')
                ->where('user_id', $admin_info['user_id'])
                ->where('ru_id', $comment_info['ru_id'])->first();

            if (!empty($reply_info['content']) && $adminru['ru_id'] == $comment_info['ru_id']) {
                /* 更新回复的内容 */
                $other = [
                    'email' => $email,
                    'user_name' => $user_name,
                    'content' => $content,
                    'add_time' => gmtime(),
                    'ip_address' => $ip,
                    'status' => '0'
                ];
                $other = BaseRepository::recursiveNullVal($other);
                Comment::where('comment_id', $reply_info['comment_id'])->update($other);
            } elseif ($adminru['ru_id'] == $comment_info['ru_id']) {
                /* 插入回复的评论内容  by kong */
                $other = [
                    'comment_type' => $comment_type,
                    'id_value' => $id_value,
                    'email' => $email,
                    'user_name' => session('admin_name'),
                    'content' => $content,
                    'add_time' => gmtime(),
                    'ip_address' => $ip,
                    'status' => '0',
                    'parent_id' => $comment_id,
                    'user_id' => $admin_info['user_id'],
                    'ru_id' => $adminru['ru_id'],
                ];
                $other = BaseRepository::recursiveNullVal($other);
                Comment::insert($other);
            } else {
                return sys_msg(__('admin::common.priv_error'));
            }
            /* 更新当前的评论状态为已回复并且可以显示此条评论 */
            Comment::where('comment_id', $comment_id)->update(['status' => '1']);
            $send_ok = 0;
            /* 邮件通知处理流程 */
            if (!empty($send_email_notice) || (isset($remail) && !empty($remail))) {
                //获取邮件中的必要内容
                $comment_info = Comment::where('comment_id', $comment_id)->first();

                /* 设置留言回复模板所需要的内容信息 */
                $template = get_mail_template('recomment');

                $this->smarty->assign('user_name', $comment_info['user_name']);
                $this->smarty->assign('recomment', $content);
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
            admin_log(addslashes(__('admin::comment_manage.reply')), 'edit', 'users_comment');

            return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id&send_ok=$send_ok\n");
        }
        /*------------------------------------------------------ */
        //-- 删除某一条评论
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('comment_priv');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $comment_id = (int)request()->get('id', 0);

            if (empty($comment_id)) {
                return make_json_error('delete fail');
            }

            //获取删除评论信息
            $comment = Comment::where('comment_id', $comment_id)->select('comment_id', 'comment_type', 'id_value', 'rec_id', 'user_id', 'add_comment_id')->first();
            $comment = $comment ? $comment->toArray() : [];

            if ($comment) {
                Comment::where('comment_id', $comment_id)->delete();
                Comment::where('parent_id', $comment_id)->delete();

                /* 删除该评论的图片 */
                $img = BaseRepository::getToArrayGet(CommentImg::where('comment_id', $comment_id));
                if ($img) {
                    for ($i = 0; $i < count($img); $i++) {
                        @unlink(storage_public($img[$i]['comment_img']));
                        @unlink(storage_public($img[$i]['img_thumb']));
                        $this->dscRepository->getOssDelFile([$img[$i]['comment_img'], $img[$i]['img_thumb']]);
                    }
                }
                CommentImg::where('comment_id', $comment_id)->delete();

                // 更新订单商品评价状态
                if (isset($comment['comment_type']) && $comment['comment_type'] == 0 && !empty($comment['rec_id']) && !empty($comment['user_id'])) {
                    // 删除首评 => 修改为 订单商品评价状态为3 评价软删除 前台不显示 ; 删除追评 状态保持不变（不可再追评）
                    if (isset($comment['add_comment_id']) && $comment['add_comment_id'] == 0) {
                        DB::table('order_goods')->where('rec_id', $comment['rec_id'])->where('user_id', $comment['user_id'])->update(['is_comment' => 3]);
                        // 删除首评同时删除追评
                        Comment::where('add_comment_id', $comment_id)->delete();
                    }
                }

                //获取商品的评论数
                $goods_id = $comment['id_value'] ?? 0;
                if ($goods_id) {
                    //更新评论数量
                    Goods::where('goods_id', $goods_id)->where('comments_number', '>', 0)->decrement('comments_number');
                    IntelligentWeight::where('goods_id', $goods_id)->where('goods_comment_number', '>', 0)->decrement('goods_comment_number');
                    update_goods_weights($goods_id); // 更新权重值
                }

                // 记录操作日志
                admin_log('', 'remove', 'users_comment');

                // 更新订单统计
                Artisan::call('app:user:order');
            }

            $url = 'comment_manage.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 批量删除用户评论
        /*------------------------------------------------------ */
        elseif ($act == 'batch') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $action = request()->get('sel_action', 'deny');
            $checkboxes = request()->get('checkboxes', []);
            if ($checkboxes) {
                $checkboxes = BaseRepository::getExplode($checkboxes);

                switch ($action) {
                    case 'remove':

                        $comment_list = Comment::whereIn('comment_id', $checkboxes)->select('comment_id', 'comment_type', 'id_value', 'rec_id', 'user_id', 'add_comment_id')->get();
                        $comment_list = $comment_list ? $comment_list->toArray() : [];
                        if ($comment_list) {
                            foreach ($comment_list as $comment) {
                                $comment_id = $comment['comment_id'] ?? 0;
                                if ($comment_id) {
                                    // 更新订单商品评价状态
                                    if (isset($comment['comment_type']) && $comment['comment_type'] == 0 && !empty($comment['rec_id']) && !empty($comment['user_id'])) {
                                        // 删除首评 => 修改为 订单商品评价状态为3 评价软删除 前台不显示 ; 删除追评 状态保持不变（不可再追评）
                                        if (isset($comment['add_comment_id']) && $comment['add_comment_id'] == 0) {
                                            DB::table('order_goods')->where('rec_id', $comment['rec_id'])->where('user_id', $comment['user_id'])->update(['is_comment' => 3]);
                                            // 删除首评同时删除追评
                                            Comment::where('add_comment_id', $comment_id)->delete();
                                        }
                                    }
                                }
                            }
                        }

                        $img = CommentImg::select('comment_img', 'img_thumb')->whereIn('comment_id', $checkboxes);
                        $img = BaseRepository::getToArrayGet($img);

                        if ($img) {
                            for ($i = 0; $i < count($img); $i++) {
                                @unlink(storage_public($img[$i]['comment_img']));
                                @unlink(storage_public($img[$i]['img_thumb']));
                                $this->dscRepository->getOssDelFile([$img[$i]['comment_img'], $img[$i]['img_thumb']]);
                            }
                        }

                        CommentImg::whereIn('comment_id', $checkboxes)->delete();
                        Comment::whereIn('comment_id', $checkboxes)->delete();
                        Comment::whereIn('parent_id', $checkboxes)->delete();

                        break;

                    case 'allow':
                        Comment::whereIn('comment_id', $checkboxes)->update(['status' => '1']);
                        break;

                    case 'deny':
                        Comment::whereIn('comment_id', $checkboxes)->update(['status' => '0']);
                        break;

                    default:
                        break;
                }

                $action = ($action == 'remove') ? 'remove' : 'edit';
                admin_log('', $action, 'users_comment');

                // 更新订单统计
                Artisan::call('app:user:order');

                clear_cache_files();

                $link[] = ['text' => __('admin::comment_manage.back_list'), 'href' => 'comment_manage.php?act=list'];
                return sys_msg(sprintf(__('admin::comment_manage.batch_drop_success'), count($checkboxes)), 0, $link);
            } else {
                /* 提示信息 */
                $link[] = ['text' => __('admin::comment_manage.back_list'), 'href' => 'comment_manage.php?act=list'];
                return sys_msg(__('admin::comment_manage.no_select_comment'), 0, $link);
            }
        }
    }
}
