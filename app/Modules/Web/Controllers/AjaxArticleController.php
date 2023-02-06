<?php

namespace App\Modules\Web\Controllers;

use App\Repositories\Common\BaseRepository;
use App\Services\Article\ArticleService;

class AjaxArticleController extends InitController
{
    protected $articleService;

    public function __construct(
        ArticleService $articleService
    ) {
        $this->articleService = $articleService;
    }

    public function index()
    {
        if ($this->checkReferer() === false) {
            return response()->json(['error' => 1, 'message' => 'referer error']);
        }

        $result = ['error' => 0, 'message' => '', 'content' => ''];

        //jquery Ajax跨域
        $is_jsonp = intval(request()->input('is_jsonp', 0));

        $act = addslashes(trim(request()->input('act', '')));
        /*------------------------------------------------------ */
        //-- 首页文章列表检测
        /*------------------------------------------------------ */
        if ($act == 'checked_article_cat') {
            $result = ['error' => 0, 'content' => '', 'message' => ''];

            $cat_ids = trim(request()->input('cat_ids', ''));

            $index_article_cat_arr = BaseRepository::getExplode($cat_ids);

            $index_article_arr = cache()->remember('index_article_arr.' . $cat_ids, config('shop.cache_time'), function () use ($index_article_cat_arr) {
                $index_article_arr = [];

                //首页文章栏目
                if (!empty($index_article_cat_arr)) {
                    foreach ($index_article_cat_arr as $key => $val) {
                        $index_article_arr[] = $this->articleService->getAssignArticles($val, 3);
                    }
                }

                return $index_article_arr;
            });

            $this->smarty->assign('index_article_cat', $index_article_arr);
            $result['content'] = $this->smarty->fetch('library/index_article_cat.lbi');
        }

        if ($is_jsonp) {
            $jsoncallback = request()->input('jsoncallback', '');
            return $jsoncallback . "(" . response()->json($result) . ")";
        } else {
            return response()->json($result);
        }
    }
}
