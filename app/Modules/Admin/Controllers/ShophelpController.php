<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Article;
use App\Models\ArticleCat;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 帮助信息管理程序
 */
class ShophelpController extends InitController
{
    public function index()
    {
        /*------------------------------------------------------ */
        //-- 列出所有文章分类
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list_cat') {
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['article_add'], 'href' => 'shophelp.php?act=add']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cat_list']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('list', $this->get_shophelp_list());


            return $this->smarty->display('shophelp_cat_list.htm');
        }

        /*------------------------------------------------------ */
        //-- 分类下的文章
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list_article') {
            $cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['article_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['article_add'], 'href' => 'shophelp.php?act=add&cat_id=' . $cat_id]);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('cat', article_cat_list($cat_id, true, 'cat_id', 0, "onchange=\"location.href='?act=list_article&cat_id='+this.value\""));
            $this->smarty->assign('list', $this->shophelp_article_list($cat_id));


            return $this->smarty->display('shophelp_article_list.htm');
        }

        /*------------------------------------------------------ */
        //-- 查询分类下的文章
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query_art') {
            $cat_id = intval($_GET['cat']);

            $this->smarty->assign('list', $this->shophelp_article_list($cat_id));
            return make_json_result($this->smarty->fetch('shophelp_article_list.htm'));
        }

        /*------------------------------------------------------ */
        //-- 查询
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $this->smarty->assign('list', $this->get_shophelp_list());

            return make_json_result($this->smarty->fetch('shophelp_cat_list.htm'));
        }

        /*------------------------------------------------------ */
        //-- 添加文章
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            /* 权限判断 */
            admin_priv('shophelp_manage');

            $cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

            /* 创建 html editor */
            create_html_editor('FCKeditor1');

            if (empty($cat_id)) {
                $selected = 0;
            } else {
                $selected = $cat_id;
            }
            $cat_list = article_cat_list($selected, true, 'cat_id', 0);
            $cat_list = str_replace('select please', $GLOBALS['_LANG']['select_plz'], $cat_list);
            $this->smarty->assign('cat_list', $cat_list);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['article_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['cat_list'], 'href' => 'shophelp.php?act=list_cat']);
            $this->smarty->assign('form_action', 'insert');
            return $this->smarty->display('shophelp_info.htm');
        }
        if ($_REQUEST['act'] == 'insert') {
            /* 权限判断 */
            admin_priv('shophelp_manage');

            $title = isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '';
            $cat_id = isset($_POST['cat_id']) && !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
            $article_type = isset($_POST['article_type']) && !empty($_POST['article_type']) ? intval($_POST['article_type']) : 0;
            $editor = isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '';

            /* 判断是否重名 */
            /*检查名称是否重复*/
            $is_only = Article::where('title', $title)->count();

            if ($is_only > 0) {
                return sys_msg($GLOBALS['_LANG']['title_exist'], 1);
            }

            /* 插入数据 */
            $add_time = TimeRepository::getGmTime();
            Article::insert([
                'title' => $title,
                'cat_id' => $cat_id,
                'article_type' => $article_type,
                'content' => $editor,
                'add_time' => $add_time,
                'author' => '_SHOPHELP'
            ]);

            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'shophelp.php?act=list_article&cat_id=' . $cat_id;
            $link[1]['text'] = $GLOBALS['_LANG']['continue_add'];
            $link[1]['href'] = 'shophelp.php?act=add&cat_id=' . $cat_id;

            admin_log($_POST['title'], 'add', 'shophelp');
            return sys_msg($GLOBALS['_LANG']['articleadd_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑文章
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit') {
            /* 权限判断 */
            admin_priv('shophelp_manage');

            $article_id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0;

            /* 取文章数据 */
            $article = Article::where('article_id', $article_id);
            $article = BaseRepository::getToArrayFirst($article);

            /* 创建 html editor */
            create_html_editor('FCKeditor1', $article['content']);

            $this->smarty->assign('cat_list', article_cat_list($article['cat_id'], true, 'cat_id', 0));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['article_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['article_list'], 'href' => 'shophelp.php?act=list_article&cat_id=' . $article['cat_id']]);
            $this->smarty->assign('article', $article);
            $this->smarty->assign('form_action', 'update');


            return $this->smarty->display('shophelp_info.htm');
        }

        if ($_REQUEST['act'] == 'update') {
            /* 权限判断 */
            admin_priv('shophelp_manage');

            $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0;
            $title = isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '';
            $old_title = isset($_POST['old_title']) && !empty($_POST['title']) ? addslashes($_POST['old_title']) : '';
            $cat_id = isset($_POST['cat_id']) && !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
            $article_type = isset($_POST['article_type']) && !empty($_POST['article_type']) ? intval($_POST['article_type']) : 0;
            $editor = isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '';

            /* 检查重名 */
            if ($title != $old_title) {
                $is_only = Article::where('title', $title)->where('article_id', '<>', $id)->count();

                if ($is_only > 0) {
                    return sys_msg($GLOBALS['_LANG']['articlename_exist'], 1);
                }
            }

            $res = Article::where('article_id', $id)->update([
                'title' => $title,
                'cat_id' => $cat_id,
                'article_type' => $article_type,
                'content' => $editor
            ]);

            /* 更新 */
            if ($res) {
                admin_log($title, 'edit', 'shophelp');
            }

            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'shophelp.php?act=list_article&cat_id=' . $cat_id;

            return sys_msg(sprintf($GLOBALS['_LANG']['articleedit_succeed'], $title), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑分类的名称
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit_catname') {
            $check_auth = check_authz_json('shophelp_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $cat_name = json_str_iconv(trim($_POST['val']));

            $is_only = ArticleCat::where('cat_name', $cat_name)->where('cat_id', '<>', $id)->count();

            /* 检查分类名称是否重复 */
            if ($is_only > 0) {
                return make_json_error(sprintf($GLOBALS['_LANG']['catname_exist'], $cat_name));
            } else {
                $res = ArticleCat::where('cat_id', $id)->update([
                    'cat_name' => $cat_name
                ]);

                if ($res) {
                    admin_log($cat_name, 'edit', 'shophelpcat');
                }

                return make_json_result(stripslashes($cat_name));
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑分类的排序
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit_cat_order') {
            $check_auth = check_authz_json('shophelp_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $order = json_str_iconv(trim($_POST['val']));

            /* 检查输入的值是否合法 */
            if (!preg_match("/^[0-9]+$/", $order)) {
                return make_json_result('', sprintf($GLOBALS['_LANG']['enter_int'], $order));
            } else {
                ArticleCat::where('cat_id', $id)->update([
                    'sort_order' => $order
                ]);

                return make_json_result(stripslashes($order));
            }
        }

        /*------------------------------------------------------ */
        //-- 删除分类
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('shophelp_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            /* 非空的分类不允许删除 */
            $count = Article::where('cat_id', $id)->count();

            if ($count > 0) {
                return make_json_error(sprintf($GLOBALS['_LANG']['not_emptycat']));
            } else {
                $res = ArticleCat::where('cat_id', $id)->delete();

                if ($res > 0) {
                    admin_log('', 'remove', 'shophelpcat');
                }
            }

            $url = 'shophelp.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 删除分类下的某文章
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove_art') {
            $check_auth = check_authz_json('shophelp_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            $res = Article::where('article_id', $id)->delete();

            if ($res > 0) {
                /* 清除缓存 */
                admin_log('', 'remove', 'shophelp');
            } else {
                return make_json_error(sprintf($GLOBALS['_LANG']['remove_fail']));
            }

            $cat_id = Article::where('article_id', $id)->value('cat_id');
            $cat_id = $cat_id ? $cat_id : 0;

            $url = 'shophelp.php?act=query_art&cat=' . $cat_id . '&' . str_replace('act=remove_art', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 添加一个新分类
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'add_catname') {
            $check_auth = check_authz_json('shophelp_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $cat_name = trim($_POST['cat_name']);

            if (!empty($cat_name)) {
                $count = ArticleCat::where('cat_name', $cat_name)->count();

                if ($count > 0) {
                    return make_json_error($GLOBALS['_LANG']['catname_exist']);
                } else {
                    $res = ArticleCat::insert([
                        'cat_name' => $cat_name,
                        'cat_type' => 0
                    ]);

                    if ($res) {
                        admin_log($cat_name, 'add', 'shophelpcat');
                    }

                    return dsc_header("Location: shophelp.php?act=query\n");
                }
            } else {
                return make_json_error($GLOBALS['_LANG']['js_languages']['no_catname']);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑文章标题
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit_title') {
            $check_auth = check_authz_json('shophelp_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $title = json_str_iconv(trim($_POST['val']));

            /* 检查文章标题是否有重名 */
            $count = Article::where('article_id', $id)->count();
            if ($count > 0) {
                return make_json_error(sprintf($GLOBALS['_LANG']['articlename_exist'], $title));
            } else {
                $res = Article::where('article_id', $id)->update([
                    'title' => $title
                ]);

                if ($res) {
                    admin_log($title, 'edit', 'shophelp');
                }

                return make_json_result(stripslashes($title));
            }
        }
    }

    /* 获得网店帮助文章分类 */
    private function get_shophelp_list()
    {
        $list = [];

        $res = ArticleCat::where('cat_type', 0)
            ->withCount('getArticleFirst as num');

        $res = $res->orderBy('sort_order');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $rows) {
                $list[] = $rows;
            }
        }

        return $list;
    }

    /* 获得网店帮助某分类下的文章 */
    private function shophelp_article_list($cat_id)
    {
        $res = Article::where('cat_id', $cat_id)->orderBy('article_type', 'desc');

        $res = BaseRepository::getToArrayGet($res);

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
