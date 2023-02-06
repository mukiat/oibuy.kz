<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\FriendLink;
use App\Models\SearchKeyword;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Keywords\KeywordsManageService;

/**
 * 友情链接管理
 */
class KeywordsManageController extends InitController
{
    protected $keywordsManageService;
    protected $dscRepository;

    public function __construct(
        KeywordsManageService $keywordsManageService,
        DscRepository $dscRepository
    ) {
        $this->keywordsManageService = $keywordsManageService;
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

        /*------------------------------------------------------ */
        //-- 用户检索记录列表页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['list_link']);
            $this->smarty->assign('full_page', 1);

            /* 获取友情链接数据 */
            $keywords_list = $this->keywordsManageService->getKeywordsList();

            $this->smarty->assign('keywords_list', $keywords_list['list']);
            $this->smarty->assign('filter', $keywords_list['filter']);
            $this->smarty->assign('record_count', $keywords_list['record_count'] ? $keywords_list['record_count'] : 0);
            $this->smarty->assign('page_count', $keywords_list['page_count']);

            $sort_flag = sort_flag($keywords_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('keywords_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            /* 获取友情链接数据 */
            $keywords_list = $this->keywordsManageService->getKeywordsList();

            $this->smarty->assign('keywords_list', $keywords_list['list']);
            $this->smarty->assign('filter', $keywords_list['filter']);
            $this->smarty->assign('record_count', $keywords_list['record_count'] ? $keywords_list['record_count'] : 0);
            $this->smarty->assign('page_count', $keywords_list['page_count']);

            $sort_flag = sort_flag($keywords_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                mb_convert_encoding($this->smarty->fetch('keywords_list.dwt'), 'UTF-8', 'UTF-8'),
                '',
                ['filter' => $keywords_list['filter'], 'page_count' => $keywords_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 删除友情        链接
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {

            $id = intval($_GET['id']);

            SearchKeyword::where('keyword_id', $id)->delete();

            $url = 'keywords_manage.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}
