<?php

namespace App\Services\Article;

use App\Models\ArticleCat;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class ArticleCatManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取文章分类
     *
     * @return array
     */
    public function getArticleCatList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getArticleCatList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $cat_back = 0;
        $filter['cat_id'] = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

        $row = ArticleCat::whereRaw(1);

        if ($filter['cat_id'] > 0) {
            $row = $row->where('parent_id', $filter['cat_id']);
            $cat_back = 1;
        } else {
            $row = $row->where('parent_id', 0);
        }

        $res = $record_count = $row;

        /* 记录总数 */
        $filter['record_count'] = $record_count->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->groupBy('cat_id')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('parent_id', 'ASC');

        $start = ($filter['page'] - 1) * $filter['page_size'];
        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $cat) {
                $res[$key]['type_name'] = $GLOBALS['_LANG']['type_name'][$cat['cat_type']];
                $res[$key]['url'] = $this->dscRepository->buildUri('article', ['acid' => $cat['cat_id']], $cat['cat_name']);
                $res[$key]['add_child'] = "articlecat.php?act=add&cat_id=" . $cat['cat_id'] . "";
                $res[$key]['child_url'] = "articlecat.php?act=list&cat_id=" . $cat['cat_id'];
                $res[$key]['cat_type'] = $cat['cat_type'];
            }
        }

        $arr = ['result' => $res, 'filter' => $filter, 'cat_back' => $cat_back, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }

    /**
     * 文章可视化分类列表
     *
     * @param int $parent_id
     * @return string
     */
    public function cmsArticleCatList($parent_id = 0)
    {
        $res = ArticleCat::where('parent_id', $parent_id);
        $res = BaseRepository::getToArrayGet($res);

        $select = '';
        if ($res) {
            foreach ($res as $var) {
                $select .= '<li><a href="javascript:;" cat_type="' . $var['cat_type'] . '" data-value="' . $var['cat_id'] . '" ';
                $select .= ' cat_type="' . $var['cat_type'] . '" class="ftx-01">';
                $select .= htmlspecialchars(addslashes(str_replace("\r\n", "", $var['cat_name']))) . '</a></li>';
            }
        }

        return $select;
    }
}
