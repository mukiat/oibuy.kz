<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Image;
use App\Models\Comment;
use App\Models\DiscussCircle;
use App\Models\Goods;
use App\Models\Users;
use App\Models\GoodsGallery;
use App\Models\AdminUser;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;

/**
 * 用户评论管理程序
 */
class DiscussCircleController extends InitController
{
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    ) {
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        load_helper('goods', 'seller');

        $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);
        $menus = session()->has('menus') ? session('menus') : '';
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "goods");
        /* act操作项的初始化 */
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        } else {
            $_REQUEST['act'] = trim($_REQUEST['act']);
        }

        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'discuss_circle']);
        /*------------------------------------------------------ */
        //-- 获取没有回复的评论列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            /* 检查权限 */
            admin_priv('discuss_circle');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('current', 'discuss_circle_list');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['discuss_circle']);
            $this->smarty->assign('full_page', 1);

            $list = $this->get_discuss_list($adminru['ru_id']);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $this->smarty->assign('discuss_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['discuss_add'],
                'href' => 'discuss_circle.php?act=add', 'class' => 'icon-plus']);


            return $this->smarty->display('discuss_list.dwt');
        }


        /*------------------------------------------------------ */
        //-- 主题添加页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            admin_priv('discuss_circle');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['discuss_add']);
            $this->smarty->assign('action_link', ['href' => 'discuss_circle.php?act=list', 'text' => $GLOBALS['_LANG']['discuss_circle'], 'class' => 'icon-reply']);
            $this->smarty->assign('action', 'add');

            $this->smarty->assign('act', 'insert');
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            create_ueditor_editor('content', '', 360);

            return $this->smarty->display('discuss_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 主题添加的处理
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'insert') {
            $goods_id = !empty($_POST['goods_id']) ? trim($_POST['goods_id']) : 0;
            $dis_title = !empty($_POST['dis_title']) ? trim($_POST['dis_title']) : '';
            $dis_text = !empty($_POST['content']) ? trim($_POST['content']) : '';
            $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
            $discuss_type = !empty($_POST['discuss_type']) ? intval($_POST['discuss_type']) : 0;

            $user = Users::select('user_id', 'user_name')->where('user_name', $user_name);
            $user = BaseRepository::getToArrayFirst($user);

            if ($user['user_id'] <= 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['type_name_exist'], 0, $link);
            }

            $add_time = gmtime();

            if (isset($_FILES['img_url']) && $_FILES['img_url']) {
                foreach ($_FILES['img_url']['error'] as $key => $value) {
                    if ($value == 0) {
                        if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
                            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                            return sys_msg($GLOBALS['_LANG']['invalid_img_url'], 0, $link);
                        }
                    } elseif ($value == 1) {
                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                        return sys_msg($GLOBALS['_LANG']['img_url_too_big'], 0, $link);
                    } elseif ($_FILES['img_url']['error'] == 2) {
                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                        return sys_msg($GLOBALS['_LANG']['img_url_too_big'], 0, $link);
                    }
                }

                // 相册图片
                foreach ($_FILES['img_url']['tmp_name'] as $key => $value) {
                    if ($value != 'none') {
                        if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
                            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                            return sys_msg($GLOBALS['_LANG']['invalid_img_url'], 0, $link);
                        }
                    }
                }
            }

            /* 插入数据库。 */
            $other = [
                'goods_id' => $goods_id,
                'user_id' => $user['user_id'],
                'order_id' => 0,
                'dis_type' => $discuss_type,
                'dis_title' => $dis_title,
                'dis_text' => $dis_text,
                'add_time' => $add_time,
                'user_name' => $user['user_name']
            ];
            $dis_id = DiscussCircle::insertGetId($other);

            /* 处理相册图片 */
            if (isset($_FILES['img_url'])) {
                if (!empty($dis_id)) {
                    handle_gallery_image(0, $_FILES['img_url'], $_POST['img_desc'], $_POST['img_file'], 0, $dis_id, 'true');
                } else {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                    return sys_msg($GLOBALS['_LANG']['dis_error'], 0, $link);
                }
            }

            /* 记录管理员操作 */
            admin_log($dis_title, 'add', 'discussinsert');

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['discuss_add'];
            $link[0]['href'] = 'discuss_circle.php?act=add';

            $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[1]['href'] = 'discuss_circle.php?act=list';

            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $dis_title . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }


        /*------------------------------------------------------ */
        //-- 主题修改的处理
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'update') {
            $dis_id = !empty($_POST['dis_id']) ? trim($_POST['dis_id']) : 0;

            if (empty($dis_id)) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['discuss_exits'], 0, $link);
            }

            $dis_title = !empty($_POST['dis_title']) ? trim($_POST['dis_title']) : '';
            $dis_text = !empty($_POST['content']) ? trim($_POST['content']) : '';
            $old_img_desc = !empty($_POST['old_img_desc']) ? $_POST['old_img_desc'] : '';
            $front_cover = !empty($_POST['front_cover']) ? $_POST['front_cover'] : 0;
            $discuss_type = !empty($_POST['discuss_type']) ? $_POST['discuss_type'] : 1;
            $add_time = gmtime();

            /* 插入数据库。 */
            $data = [
                'dis_title' => $dis_title,
                'dis_text' => $dis_text,
                'add_time' => $add_time,
                'dis_type' => $discuss_type,
            ];
            DiscussCircle::where('dis_id', $dis_id)->update($data);

            /* 记录管理员操作 */
            admin_log($dis_title, 'add', 'discussinsert');

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['discuss_edit'];
            $link[0]['href'] = "discuss_circle.php?act=reply&id=$dis_id";

            $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[1]['href'] = 'discuss_circle.php?act=list';

            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $dis_title . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        } elseif ($_REQUEST['act'] == 'search_goods') {
            $check_auth = check_authz_json('discuss_circle');
            if ($check_auth !== true) {
                return $check_auth;
            }


            $filter = dsc_decode($_GET['JSON']);
            $arr = get_goods_list($filter);
            if (empty($arr)) {
                $arr[0] = [
                    'goods_id' => 0,
                    'goods_name' => ''
                ];
            }

            return make_json_result($arr);
        }

        /*------------------------------------------------------ */
        //-- 翻页、搜索、排序
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'query') {
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

            $list = $this->get_discuss_list($adminru['ru_id']);

            //分页
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

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
        if ($_REQUEST['act'] == 'reply') {
            /* 检查权限 */
            admin_priv('discuss_circle');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);

            /* 获取评论详细信息并进行字符处理 */
            $discuss_info = DiscussCircle::where('dis_id', $_REQUEST['id']);
            $discuss_info = BaseRepository::getToArrayFirst($discuss_info);

            $discuss_info['dis_title'] = str_replace('\r\n', '<br />', htmlspecialchars($discuss_info['dis_title']));
            $discuss_info['dis_title'] = nl2br(str_replace('\n', '<br />', $discuss_info['dis_title']));
            $discuss_info['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $discuss_info['add_time']);

            //取得商品名称
            $goods = Goods::select('goods_name', 'original_img')->where('goods_id', $discuss_info['goods_id']);
            $goods = BaseRepository::getToArrayFirst($goods);

            $discuss_info['original_img'] = isset($goods['original_img']) && $goods['original_img'] ? $this->dscRepository->getImagePath($goods['original_img']) : '';
            $discuss_info['goods_name'] = $goods['goods_name'];

            //取得图片地址
            $imgs = GoodsGallery::where('dis_id', $discuss_info['dis_id']);
            $imgs = BaseRepository::getToArrayGet($imgs);

            /* 获取管理员的用户名和Email地址 */
            $admin_info = AdminUser::select('user_name', 'email')->where('user_id', session('seller_id'));
            $admin_info = BaseRepository::getToArrayFirst($admin_info);

            /* 模板赋值 */
            $this->smarty->assign('imgs', $imgs);
            $this->smarty->assign('msg', $discuss_info); //评论信息
            $this->smarty->assign('admin_info', $admin_info);   //管理员信息
            $this->smarty->assign('act', 'update');  //评论的对象

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['discuss_info']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['discuss_circle'],
                'href' => 'discuss_circle.php?act=list', 'class' => 'icon-reply']);

            create_ueditor_editor('content', $discuss_info['dis_text'], 360);

            return $this->smarty->display('discuss_info.dwt');
        }
        /*------------------------------------------------------ */
        //-- 处理 回复用户评论
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'action') {
            admin_priv('discuss_circle');

            /* 获取IP地址 */
            $ip = $this->dscRepository->dscIp();

            /* 获得评论是否有回复 */
            $reply_info = Comment::select('comment_id', 'content', 'parent_id')->where('parent_id', $_REQUEST['comment_id']);
            $reply_info = BaseRepository::getToArrayFirst($reply_info);

            if (!empty($reply_info['content'])) {
                /* 更新回复的内容 */
                $data = [
                    'email' => $_POST['email'],
                    'user_name' => $_POST['user_name'],
                    'content' => $_POST['content'],
                    'add_time' => TimeRepository::getGmTime(),
                    'ip_address' => $ip,
                    'status' => 0
                ];
                Comment::where('comment_id', $reply_info['comment_id'])->update($data);
            } else {
                /* 插入回复的评论内容 */
                $data = [
                    'comment_type' => $_POST['comment_type'],
                    'id_value' => $_POST['id_value'],
                    'email' => $_POST['email'],
                    'user_name' => session('seller_name'),
                    'content' => $_POST['content'],
                    'add_time' => TimeRepository::getGmTime(),
                    'ip_address' => $ip,
                    'status' => 0,
                    'parent_id' => intval($_POST['comment_id']),
                ];
                Comment::insert($data);
            }

            /* 更新当前的评论状态为已回复并且可以显示此条评论 */
            Comment::where('comment_id', $_POST['comment_id'])->update(['status' => 1]);

            /* 邮件通知处理流程 */
            if (!empty($_POST['send_email_notice']) or isset($_POST['remail'])) {
                //获取邮件中的必要内容
                $comment_info = Comment::select('user_name', 'email', 'content')->where('comment_id', $_REQUEST['comment_id']);
                $comment_info = BaseRepository::getToArrayFirst($comment_info);

                /* 设置留言回复模板所需要的内容信息 */
                $template = get_mail_template('recomment');

                $this->smarty->assign('user_name', $comment_info['user_name']);
                $this->smarty->assign('recomment', $_POST['content']);
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

            return dsc_header("Location: comment_manage.php?act=reply&id=$_REQUEST[comment_id]&send_ok=$send_ok\n");
        }
        /*------------------------------------------------------ */
        //-- 更新评论的状态为显示或者        禁止
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'check') {
            if ($_REQUEST['check'] == 'allow') {
                /* 允许评论显示 */
                Comment::where('comment_id', $_REQUEST['id'])->update(['status' => 1]);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=reply&id=$_REQUEST[id]\n");
            } else {
                /* 禁止评论显示 */
                Comment::where('comment_id', $_REQUEST['id'])->update(['status' => 0]);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: comment_manage.php?act=reply&id=$_REQUEST[id]\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 删除某一条评论
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('discuss_circle');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 0;
            $dis_id = isset($_GET['dis_id']) && !empty($_GET['dis_id']) ? intval($_GET['dis_id']) : 0;

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
        if ($_REQUEST['act'] == 'batch_drop') {
            admin_priv('discuss_circle');
            $dis_id = isset($_POST['dis_id']) ? trim($_POST['dis_id']) : 0;
            $action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'remove';

            if ($dis_id > 0) {
                $href = "discuss_circle.php?act=user_reply&id=" . $dis_id;
                $back_list = $GLOBALS['_LANG']['discuss_user_reply'];
            } else {
                $href = "discuss_circle.php?act=list";
                $back_list = $GLOBALS['_LANG']['back_list'];
            }

            if (isset($_POST['checkboxes'])) {
                switch ($action) {
                    case 'remove':
                        $checkboxes = BaseRepository::getExplode($_POST['checkboxes']);
                        DiscussCircle::whereIn('dis_id', $checkboxes)->delete();
                        break;

                    default:
                        break;
                }

                clear_cache_files();
                $action = ($action == 'remove') ? 'remove' : 'edit';
                admin_log('', $action, 'adminlog');

                $link[] = ['text' => $back_list, 'href' => $href];
                return sys_msg(sprintf($GLOBALS['_LANG']['batch_drop_success'], count($_POST['checkboxes'])), 0, $link);
            } else {
                /* 提示信息 */
                $link[] = ['text' => $back_list, 'href' => $href];
                return sys_msg($GLOBALS['_LANG']['no_select_discuss'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 获取没有回复的评论列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'user_reply') {
            /* 检查权限 */
            admin_priv('discuss_circle');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['discuss_user_reply']);
            $this->smarty->assign('full_page', 1);

            $list = $this->get_discuss_user_reply_list();

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
        if ($_REQUEST['act'] == 'discuss_reply_query') {
            $list = $this->get_discuss_user_reply_list();

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

    /**
     * 获取讨论列表
     * 
     * @param $ru_id
     * @return array
     * @throws \Exception
     */
    private function get_discuss_list($ru_id)
    {
        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $res = DiscussCircle::query();

        if (!empty($filter['keywords'])) {
            $res = $res->where(function ($query) use ($filter) {
                $query->where('dis_title', 'like', '%' . mysql_like_quote($filter['keywords']) . '%')
                    ->orWhereHasIn('getGoods', function ($query) use ($filter) {
                        $query->where('goods_name', 'like', '%' . mysql_like_quote($filter['keywords']) . '%');
                    });
            });
        }

        if ($ru_id > 0) {
            $res = $res->whereHasIn('getGoods', function ($query) use ($ru_id) {
                $query->where('user_id', $ru_id);
            });
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 获取评论数据 */
        $arr = [];
        $res = $res->with('getGoods')->orderBy($filter['sort_by'], $filter['sort_order'])->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        foreach ($res as $row) {
            $row = collect($row)->merge($row['get_goods'])->except('get_goods')->all();

            switch ($row['review_status']) {
                case 1:
                    $row['lang_review_status'] = $GLOBALS['_LANG']['not_audited'];
                    break;

                case 2:
                    $row['lang_review_status'] = $GLOBALS['_LANG']['audited_not_adopt'];
                    break;

                case 3:
                    $row['lang_review_status'] = $GLOBALS['_LANG']['audited_yes_adopt'];
                    break;
            }

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                $row['user_name'] = $this->dscRepository->stringToStar($row['user_name']);
            }

            $row['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $row['shop_name'] = $this->merchantCommonService->getShopName($row['ru_id'], 1);
            $arr[] = $row;
        }

        $filter['keywords'] = stripslashes($filter['keywords']);
        $arr = ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 获取讨论列表
     * @access  public
     * @return  array
     */
    private function get_discuss_user_reply_list()
    {
        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        if (isset($_REQUEST['dis_id']) && $_REQUEST['dis_id']) {
            $filter['dis_id'] = empty($_REQUEST['dis_id']) ? 0 : trim($_REQUEST['dis_id']);
        } else {
            $filter['dis_id'] = empty($_REQUEST['id']) ? 0 : trim($_REQUEST['id']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'dc.add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $res = DiscussCircle::query();

        if (!empty($filter['keywords'])) {
            $res = $res->where('dis_text', 'like', '%' . mysql_like_quote($filter['keywords']) . '%');
        }

        $res = $res->where('parent_id', $filter['dis_id']);
        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 获取评论数据 */
        $arr = [];
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        foreach ($res as $row) {
            $row['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['add_time']);

            $users = Users::select('user_name')
                        ->whereHasIn('getDiscussCircle', function ($query) use ($row) {
                            $query->where('dis_id', $row['quote_id']);
                        });
            $users = BaseRepository::getToArrayFirst($users);
            $row['quote_name'] = $users['user_name'] ?? '';

            $arr[] = $row;
        }

        $filter['keywords'] = stripslashes($filter['keywords']);
        $arr = ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'dis_id' => $filter['dis_id']];

        return $arr;
    }
}
