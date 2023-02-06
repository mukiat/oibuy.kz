<?php

namespace App\Modules\Seller\Controllers;

use App\Models\AdminUser;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentImg;
use App\Models\Goods;
use App\Models\GoodsGallery;
use App\Models\IntelligentWeight;
use App\Models\MerchantsShopInformation;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\Single;
use App\Models\SingleSunImages;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * 用户评论管理程序
 */
class CommentManageController extends InitController
{
    protected $merchantCommonService;
    protected $dscRepository;
    protected $commonRepository;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        CommonRepository $commonRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = e(request()->get('act', 'list'));

        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "goods");
        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $user_action_list = get_user_action_list(session('seller_id'));

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '05_comment_manage']);

        /*------------------------------------------------------ */
        //-- 获取没有回复的评论列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            /* 检查权限 */
            admin_priv('comment_priv');

            //商家单个权限 ecmoban模板堂 start
            $comment_edit_delete = get_merchants_permissions($user_action_list, 'comment_edit_delete');
            $this->smarty->assign('comment_edit_delete', $comment_edit_delete); //退换货权限
            //商家单个权限 ecmoban模板堂 end
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['05_comment_manage']);
            $this->smarty->assign('full_page', 1);

            $list = $this->get_comment_list($adminru['ru_id']);

            $this->smarty->assign('comment_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('comment_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 翻页、搜索、排序
        /*------------------------------------------------------ */
        if ($act == 'query') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

            $list = $this->get_comment_list($adminru['ru_id']);

            //分页
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $this->smarty->assign('comment_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            //商家单个权限 ecmoban模板堂 start
            $comment_edit_delete = get_merchants_permissions($user_action_list, 'comment_edit_delete');
            $this->smarty->assign('comment_edit_delete', $comment_edit_delete); //退换货权限
            //商家单个权限 ecmoban模板堂 end

            return make_json_result($this->smarty->fetch('comment_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 删除图片 by guan
        /*------------------------------------------------------ */
        elseif ($act == 'drop_single_image') {
            $check_auth = check_authz_json('single_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);

            /* 删除图片文件 */
            $row = SingleSunImages::select('img_file')->where('id', $img_id);
            $row = BaseRepository::getToArrayFirst($row);

            if ($row['img_file'] != '' && is_file('../' . $row['img_file'])) {
                @unlink('../' . $row['img_file']);
            }

            /* 删除数据 */
            SingleSunImages::where('id', $img_id)->delete();

            clear_cache_files();
            return make_json_result($img_id);
        }


        /*------------------------------------------------------ */
        //-- 晒单状态为显示或者        禁止
        /*------------------------------------------------------ */
        elseif ($act == 'single_check') {
            /* 检查权限 */
            admin_priv('single_manage');

            $order_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
            $integ = isset($_REQUEST['integ']) && !empty($_REQUEST['integ']) ? floatval($_REQUEST['integ']) : 0;
            $user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            if ($_REQUEST['check'] == 'allow') {
                OrderGoods::where('order_id', $order_id)->where('goods_id', $goods_id)->update(['is_single' => 2, 'order_id' => $order_id]);
                Single::where('order_id', $order_id)->update(['is_audit' => 1, 'integ' => $integ]);

                if ($integ) {
                    log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = $integ, $GLOBALS['_LANG']['show_img_reward']);
                }

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=single_list\n");
            } else {
                OrderGoods::where('order_id', $order_id)->where('order_id', $order_id)->where('goods_id', $goods_id)->update(['is_single' => 3]);
                Single::where('order_id', $order_id)->update(['is_audit' => 0, 'integ' => -$integ]);

                if (!empty($_REQUEST['integ'])) {
                    log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = -$integ, $GLOBALS['_LANG']['show_img_no_reduce_intergral']);
                }

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=single_list\n");
            }
        }
        //@author guan end

        /*------------------------------------------------------ */
        //-- 回复用户评论(同时查看评论详情        )
        /*------------------------------------------------------ */
        elseif ($act == 'reply') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $comment_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $send_ok = isset($_REQUEST['send_ok']) && !empty($_REQUEST['send_ok']) ? addslashes($_REQUEST['send_ok']) : '';

            /* 获取评论详细信息并进行字符处理 */
            $comment_info = Comment::where('comment_id', $comment_id);
            $comment_info = BaseRepository::getToArrayFirst($comment_info);

            $comment_info['content'] = str_replace('\r\n', '<br />', htmlspecialchars($comment_info['content']));
            $comment_info['content'] = nl2br(str_replace('\n', '<br />', $comment_info['content']));
            $comment_info['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $comment_info['add_time']);
            //晒单图片
            $img_list = CommentImg::select('comment_img', 'img_thumb')->where('comment_id', $comment_id);
            $img_list = BaseRepository::getToArrayGet($img_list);

            if ($img_list) {
                foreach ($img_list as $key => $row) {
                    $img_list[$key]['comment_img'] = $this->dscRepository->getImagePath($row['comment_img']);
                    $img_list[$key]['img_thumb'] = $this->dscRepository->getImagePath($row['img_thumb']);
                }
            }
            $comment_info['img_list'] = $img_list;

            /* 获取管理员的用户名和Email地址 */
            $admin_info = AdminUser::select('user_name', 'email')->where('user_id', session('seller_id'));
            $admin_info = BaseRepository::getToArrayFirst($admin_info);

            /* 获得评论回复内容 */
            $reply_info = Comment::where('parent_id', $comment_id)
                ->where('single_id', 0)
                ->where('dis_id', 0)
                ->where('user_id', session('seller_id'))
                ->where('user_name', $admin_info['user_name'])
                ->where('ru_id', $adminru['ru_id']);

            $reply_info = BaseRepository::getToArrayFirst($reply_info);

            if (empty($reply_info)) {
                $reply_info['content'] = '';
                $reply_info['add_time'] = '';
            } else {
                $reply_info['content'] = nl2br(htmlspecialchars($reply_info['content']));
                $reply_info['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $reply_info['add_time']);
            }

            /* 取得评论的对象(文章或者商品) */
            if ($comment_info['comment_type'] == 0) {
                $id_value = Goods::where('goods_id', $comment_info['id_value'])->value('goods_name');
            } else {
                $id_value = Article::where('article_id', $comment_info['id_value'])->value('title');
            }

            /* 模板赋值 */
            $this->smarty->assign('msg', $comment_info); //评论信息
            $this->smarty->assign('admin_info', $admin_info);   //管理员信息
            $this->smarty->assign('reply_info', $reply_info);   //回复的内容
            $this->smarty->assign('id_value', $id_value);  //评论的对象
            $this->smarty->assign('send_fail', !empty($send_ok));
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['comment_info']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['05_comment_manage'], 'href' => 'comment_manage.php?act=list', 'class' => 'icon-reply']);

            /* 页面显示 */

            return $this->smarty->display('comment_info.dwt');
        }
        /*------------------------------------------------------ */
        //-- 处理 回复用户评论
        /*------------------------------------------------------ */
        elseif ($act == 'action') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $email = isset($_REQUEST['email']) && !empty($_REQUEST['email']) ? addslashes($_REQUEST['email']) : '';
            $remail = isset($_REQUEST['remail']) && !empty($_REQUEST['remail']) ? addslashes($_REQUEST['remail']) : '';
            $user_name = isset($_REQUEST['user_name']) && !empty($_REQUEST['user_name']) ? addslashes($_REQUEST['user_name']) : '';
            $content = isset($_REQUEST['content']) && !empty($_REQUEST['content']) ? addslashes($_REQUEST['content']) : '';
            $send_email_notice = isset($_REQUEST['send_email_notice']) && !empty($_REQUEST['send_email_notice']) ? addslashes($_REQUEST['send_email_notice']) : '';
            $comment_type = isset($_REQUEST['comment_type']) && !empty($_REQUEST['comment_type']) ? intval($_REQUEST['comment_type']) : 3;//评论类型3代表管理员回复
            $id_value = isset($_REQUEST['id_value']) && !empty($_REQUEST['id_value']) ? intval($_REQUEST['id_value']) : 0;

            /* 获取管理员的用户名和Email地址 */
            $admin_info = AdminUser::select('user_id', 'ru_id')->where('user_id', session('seller_id'));
            $admin_info = BaseRepository::getToArrayFirst($admin_info);

            /* 获取IP地址 */
            $ip = $this->dscRepository->dscIp();

            $comment_id = isset($_REQUEST['comment_id']) && !empty($_REQUEST['comment_id']) ? intval($_REQUEST['comment_id']) : 0;
            $comment_info = Comment::select('comment_id', 'ru_id')->where('comment_id', $comment_id)->where('ru_id', $adminru['ru_id']);
            $comment_info = BaseRepository::getToArrayFirst($comment_info);

            /* 获得评论是否有回复 */
            $reply_info = Comment::select('comment_id', 'content', 'parent_id', 'ru_id')
                ->where('parent_id', $comment_info['comment_id'])
                ->where('single_id', 0)
                ->where('dis_id', 0)
                ->where('user_id', $admin_info['user_id'])
                ->where('ru_id', $comment_info['ru_id']);

            $reply_info = BaseRepository::getToArrayFirst($reply_info);

            if (!empty($reply_info['content']) && $adminru['ru_id'] == $comment_info['ru_id']) {
                /* 更新回复的内容 */
                $data = [
                    'email' => $email,
                    'user_name' => $user_name,
                    'content' => $content,
                    'add_time' => TimeRepository::getGmTime(),
                    'ip_address' => $ip,
                    'status' => 0,
                ];
                Comment::where('comment_id', $reply_info['comment_id'])->update($data);
            } elseif ($adminru['ru_id'] == $comment_info['ru_id']) {
                /* 插入回复的评论内容 评论类型3为管理员评论 by kong*/
                $data = [
                    'comment_type' => 3,
                    'id_value' => $id_value,
                    'email' => $email,
                    'user_name' => session('seller_name'),
                    'content' => $content,
                    'add_time' => TimeRepository::getGmTime(),
                    'ip_address' => $ip,
                    'status' => 0,
                    'parent_id' => $comment_id,
                    'user_id' => $admin_info['user_id'],
                    'ru_id' => $adminru['ru_id'],
                ];
                Comment::insert($data);
            } else {
                return sys_msg($GLOBALS['_LANG']['priv_error']);
            }

            /* 更新当前的评论状态为已回复并且可以显示此条评论 */
            Comment::where('comment_id', $comment_id)->update(['status' => 1]);

            $send_ok = 1;
            /* 邮件通知处理流程 */
            if (!empty($send_email_notice) || (isset($remail) && !empty($remail))) {
                //获取邮件中的必要内容
                $comment_info = Comment::select('user_name', 'email', 'content')->where('comment_id', $comment_id);
                $comment_info = BaseRepository::getToArrayFirst($comment_info);

                /* 设置留言回复模板所需要的内容信息 */
                $template = get_mail_template('recomment');

                $this->smarty->assign('user_name', $comment_info['user_name']);
                $this->smarty->assign('recomment', $content);
                $this->smarty->assign('comment', $comment_info['content']);
                $this->smarty->assign('shop_name', "<a href='" . $this->dsc->seller_url() . "'>" . $GLOBALS['_CFG']['shop_name'] . '</a>');
                $this->smarty->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()));

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
            admin_log(addslashes($GLOBALS['_LANG']['reply']), 'edit', 'users_comment');

            return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id&send_ok=$send_ok\n");
        }
        /*------------------------------------------------------ */
        //-- 更新评论的状态为显示或者        禁止
        /*------------------------------------------------------ */
        elseif ($act == 'check') {
            /* 检查权限 */
            admin_priv('comment_priv');

            $comment_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            if ($_REQUEST['check'] == 'allow') {
                /* 允许评论显示 */
                Comment::where('comment_id', $comment_id)->update(['status' => 1]);

                $goods_id = Comment::where('comment_id', $comment_id)->value('id_value');

                $count = Comment::where('id_value', $goods_id)->where('comment_type', 0)->where('status', 1)->where('parent_id', 0)->count();

                Goods::where('goods_id', $goods_id)->update(['comments_number' => $count]);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id\n");
            } else {
                /* 禁止评论显示 */
                Comment::where('comment_id', $comment_id)->update(['status' => 0]);

                $goods_id = Comment::where('comment_id', $comment_id)->value('id_value');

                $count = Comment::where('id_value', $goods_id)->where('comment_type', 0)->where('status', 1)->where('parent_id', 0)->count();

                Goods::where('goods_id', $goods_id)->update(['comments_number' => $count]);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=reply&id=$comment_id\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 删除某一条晒单 @author guan
        /*------------------------------------------------------ */
        elseif ($act == 'single_remove') {
            $check_auth = check_authz_json('single_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $single_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $order_id = Single::where('single_id', $single_id)->value('order_id');
            OrderInfo::where('order_id', $order_id)->update(['is_single' => 4]);

            if (Single::where('single_id', $single_id)->delete()) {
                GoodsGallery::where('single_id', $single_id)->delete();
            }

            admin_log('', 'single_remove', 'ads');

            $url = 'comment_manage.php?act=single_query&' . str_replace('act=single_remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
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
                        Comment::whereIn('comment_id', $checkboxes)->update(['status' => 1]);
                        break;

                    case 'deny':
                        Comment::whereIn('comment_id', $checkboxes)->update(['status' => 0]);
                        break;

                    default:
                        break;
                }

                $action = ($action == 'remove') ? 'remove' : 'edit';
                admin_log('', $action, 'users_comment');

                // 更新订单统计
                Artisan::call('app:user:order');

                clear_cache_files();

                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'comment_manage.php?act=list'];
                return sys_msg(sprintf($GLOBALS['_LANG']['batch_drop_success'], count($checkboxes)), 0, $link);
            } else {
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'comment_manage.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['no_select_comment'], 0, $link);
            }
        }
    }

    /**
     * 获取评论列表
     *
     * @param int $ru_id
     * @return array
     * @throws \Exception
     */
    private function get_comment_list($ru_id = 0)
    {
        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : addslashes(trim($_REQUEST['keywords']));
        $filter['reply'] = empty($_REQUEST['reply']) ? 0 : intval($_REQUEST['reply']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $user_id = MerchantsShopInformation::where('shoprz_brand_name', 'like', '%' . mysql_like_quote($filter['keywords']) . '%')->orWhere('shop_name_suffix', 'like', '%' . mysql_like_quote($filter['keywords']) . '%')->value('user_id');

        if (empty($user_id)) {
            $user_id = 0;
        }

        $filter['ru_id'] = $user_id;

        $row = Comment::query();

        if (!empty($filter['keywords'])) {
            $row = $row->where(function ($query) use ($filter) {
                $query = $query->where('content', 'like', '%' . mysql_like_quote($filter['keywords']) . '%');

                if ($filter['ru_id'] > 0) {
                    $query->orWhereIn('ru_id', $filter['ru_id']);
                }
            });
        }

        if ($ru_id > 0) {
            $row = $row->where('ru_id', $ru_id);
        }

        $row = $row->where(function ($query) {
            $query->where('parent_id', 0)->orWhere(function ($query) {
                $query = CommonRepository::constantMaxId($query, 'comment_parent_id');
                CommonRepository::constantMaxId($query, 'user_id');
            });
        });

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 获取评论数据 */
        $arr = [];

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);

        $res = BaseRepository::getToArrayGet($res);

        foreach ($res as $row) {
            if ($row['comment_type'] == 2) {
                $goods_name = Goods::where('goods_id', $row['id_value'])->value('goods_name');
                $row['title'] = $goods_name . "<br/><font style='color:#1b9ad5;'>(" . $GLOBALS['_LANG']['goods_user_reply'] . ")</font>";
            } elseif ($row['comment_type'] == 3) {
                $row['title'] = Goods::where('goods_id', $row['id_value'])->value('goods_name');
            } else {
                if ($row['comment_type'] == 0) {
                    $row['title'] = Goods::where('goods_id', $row['id_value'])->value('goods_name');
                } else {
                    $row['title'] = Article::where('article_id', $row['id_value'])->value('title');
                }
            }

            $row['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $row['ru_name'] = $this->merchantCommonService->getShopName($row['ru_id'], 1); //ecmoban模板堂 --zhuo

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                $row['email'] = $this->dscRepository->stringToStar($row['email']);
                $row['user_name'] = $this->dscRepository->stringToStar($row['user_name']);
            }

            $arr[] = $row;
        }

        $filter['keywords'] = stripslashes($filter['keywords']);
        $arr = ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

}
