<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Article;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 网店信息管理页面
 */
class ShopinfoController extends InitController
{
    public function index()
    {
        /*------------------------------------------------------ */
        //-- 文章列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['shop_info']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['shopinfo_add'], 'href' => 'shopinfo.php?act=add']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('list', $this->shopinfo_article_list());


            return $this->smarty->display('shopinfo_list.htm');
        }

        /*------------------------------------------------------ */
        //-- 查询
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $this->smarty->assign('list', $this->shopinfo_article_list());

            return make_json_result($this->smarty->fetch('shopinfo_list.htm'));
        }

        /*------------------------------------------------------ */
        //-- 添加新文章
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            /* 权限判断 */
            admin_priv('shopinfo_manage');

            /* 创建 html editor */
            create_html_editor('FCKeditor1');

            /* 初始化 */
            $article['article_type'] = 0;

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['shopinfo_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['shopinfo_list'], 'href' => 'shopinfo.php?act=list']);
            $this->smarty->assign('form_action', 'insert');


            return $this->smarty->display('shopinfo_info.htm');
        }
        if ($_REQUEST['act'] == 'insert') {
            /* 权限判断 */
            admin_priv('shopinfo_manage');

            $title = isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '';
            $editor = isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '';

            /* 判断是否重名 */
            $is_only = Article::where('title', $title)->count();

            if ($is_only > 0) {
                return sys_msg(sprintf($GLOBALS['_LANG']['title_exist'], stripslashes($title)), 1);
            }

            /* 插入数据 */
            $add_time = TimeRepository::getGmTime();
            Article::insert([
                'title' => $title,
                'cat_id' => 0,
                'content' => $editor,
                'add_time' => $add_time
            ]);

            $link[0]['text'] = $GLOBALS['_LANG']['continue_add'];
            $link[0]['href'] = 'shopinfo.php?act=add';

            $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[1]['href'] = 'shopinfo.php?act=list';

            admin_log($_POST['title'], 'add', 'shopinfo');
            return sys_msg($GLOBALS['_LANG']['articleadd_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 文章编辑
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit') {
            /* 权限判断 */
            admin_priv('shopinfo_manage');

            $article_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 取得文章数据 */
            $article = Article::where('article_id', $article_id);
            $article = BaseRepository::getToArrayFirst($article);

            /* 创建 html editor */
            create_html_editor('FCKeditor1', $article['content']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['article_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['shopinfo_list'], 'href' => 'shopinfo.php?act=list']);
            $this->smarty->assign('article', $article);
            $this->smarty->assign('form_action', 'update');
            return $this->smarty->display('shopinfo_info.htm');
        }
        if ($_REQUEST['act'] == 'update') {
            /* 权限判断 */
            admin_priv('shopinfo_manage');

            $article_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $title = isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '';
            $old_title = isset($_POST['old_title']) && !empty($_POST['old_title']) ? addslashes($_POST['old_title']) : '';
            $editor = isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '';

            /* 检查重名 */
            if ($title != $old_title) {
                $is_only = Article::where('title', $title)->where('article_id', '<>', $article_id)->count();

                if ($is_only > 0) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['title_exist'], stripslashes($title)), 1);
                }
            }

            /* 更新数据 */
            $cur_time = TimeRepository::getGmTime();

            $res = Article::where('article_id', $article_id)->update([
                'title' => $title,
                'content' => $editor,
                'add_time' => $cur_time
            ]);

            if ($res > 0) {
                admin_log($title, 'edit', 'shopinfo');
            }

            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'shopinfo.php?act=list';

            return sys_msg(sprintf($GLOBALS['_LANG']['articleedit_succeed'], $title), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑文章主题
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit_title') {
            $check_auth = check_authz_json('shopinfo_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $title = json_str_iconv(trim($_POST['val']));

            /* 检查文章标题是否有重名 */
            $count = Article::where('title', $title)->where('article_id', $id);

            if ($count == 0) {
                $res = Article::where('article_id', $id)->update([
                    'title' => $title
                ]);

                if ($res > 0) {
                    admin_log($title, 'edit', 'shopinfo');
                }

                return make_json_result(stripslashes($title));
            } else {
                return make_json_error(sprintf($GLOBALS['_LANG']['title_exist'], $title));
            }
        }

        /*------------------------------------------------------ */
        //-- 删除文章
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('shopinfo_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            /* 获得文章主题 */
            $title = Article::where('article_id', $id)->value('title');

            $res = Article::where('article_id', $id)->delete();

            if ($res > 0) {
                admin_log(addslashes($title), 'remove', 'shopinfo');
            }

            $url = 'shopinfo.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }

    /* 获取网店信息文章数据 */
    private function shopinfo_article_list()
    {
        $res = Article::where('cat_id', 0)->orderBy('article_id');

        $list = [];
        if ($res) {
            foreach ($res as $rows) {
                $rows['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $rows['add_time']);

                $list[] = $rows;
            }
        }

        return $list;
    }
}
