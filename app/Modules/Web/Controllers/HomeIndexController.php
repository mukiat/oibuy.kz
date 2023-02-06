<?php

namespace App\Modules\Web\Controllers;

use App\Repositories\Common\DscRepository;
use App\Services\Article\ArticleCommonService;

/**
 * 可视化首页文件
 */
class HomeIndexController extends InitController
{
    public $suffix;
    public $preview;
    public $dir;
    public $warehouse_id;
    public $area_id;
    public $area_city;
    public $dscRepository;
    public $articleCommonService;

    public function __construct(
        DscRepository $dscRepository,
        ArticleCommonService $articleCommonService
    ) {
        $this->dscRepository = $dscRepository;
        $this->articleCommonService = $articleCommonService;
    }

    public function index()
    {
        $user_id = session('user_id', 0);

        $real_ip = $this->dscRepository->dscIp();
        $cache_id = sprintf('%X', crc32(session('user_rank') . '-' . $real_ip . '-' . config('shop.lang') . '-' . $this->suffix . '-' . $this->preview));

        if ($this->suffix || $this->preview) {
            $cache_time = 0;
        } else {
            $cache_time = config('shop.cache_time');
        }

        $content = cache()->remember('index.dwt' . $cache_id, $cache_time, function () use ($user_id) {
            assign_template();

            $position = assign_ur_here();
            $GLOBALS['smarty']->assign('ur_here', $position['ur_here']);  // 当前位置

            //获取seo start
            $seo = get_seo_words('index');

            if ($seo) {
                foreach ($seo as $key => $value) {
                    $seo[$key] = str_replace(['{sitename}', '{key}', '{description}'], [$position['title'], config('shop.shop_keywords'), config('shop.shop_desc')], $value);
                }
            }

            if (isset($seo['keywords']) && !empty($seo['keywords'])) {
                $GLOBALS['smarty']->assign('keywords', htmlspecialchars($seo['keywords']));
            } else {
                $GLOBALS['smarty']->assign('keywords', htmlspecialchars(config('shop.shop_keywords')));
            }

            if (isset($seo['description']) && !empty($seo['description'])) {
                $GLOBALS['smarty']->assign('description', htmlspecialchars($seo['description']));
            } else {
                $GLOBALS['smarty']->assign('description', htmlspecialchars(config('shop.shop_desc')));
            }

            if (isset($seo['title']) && !empty($seo['title'])) {
                $GLOBALS['smarty']->assign('page_title', htmlspecialchars($seo['title']));
            } else {
                $GLOBALS['smarty']->assign('page_title', $position['title']);
            }
            //获取seo end


            /* meta information */
            $GLOBALS['smarty']->assign('flash_theme', config('shop.flash_theme'));  // Flash轮播图片模板

            $GLOBALS['smarty']->assign('feed_url', (config('shop.rewrite') == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

            $GLOBALS['smarty']->assign('warehouse_id', $this->warehouse_id);
            $GLOBALS['smarty']->assign('area_id', $this->area_id);
            $GLOBALS['smarty']->assign('area_city', $this->area_city);

            $GLOBALS['smarty']->assign('helps', $this->articleCommonService->getShopHelp());       // 网店帮助

            /*重写图片链接*/
            $replace_data = [
                'http://localhost/ecmoban_dsc2.0.5_20170518/',
                'http://localhost/ecmoban_dsc2.2.6_20170727/',
                'http://localhost/ecmoban_dsc2.3/',
                'http://localhost/dsc30/'
            ];

            //获取首页可视化模板
            $page = get_html_file($this->dir . "/pc_html.php");
            $nav_page = get_html_file($this->dir . '/nav_html.php');
            $topBanner = get_html_file($this->dir . '/topBanner.php');

            $topBanner = str_replace($replace_data, $GLOBALS['dsc']->url(), $topBanner);
            $page = str_replace($replace_data, $GLOBALS['dsc']->url(), $page);

            if (config('shop.open_oss') == 1) {
                $bucket_info = $this->dscRepository->getBucketInfo();
                $endpoint = $bucket_info['endpoint'];
            } else {
                $endpoint = url('/');
            }

            if ($page) {
                $desc_preg = get_goods_desc_images_preg($endpoint, $page);
                $page = $desc_preg['goods_desc'];
            }

            if ($topBanner) {
                $desc_preg = get_goods_desc_images_preg($endpoint, $topBanner);
                $topBanner = $desc_preg['goods_desc'];
            }

            $pc_page['tem'] = $this->suffix;
            $GLOBALS['smarty']->assign('pc_page', $pc_page);
            $GLOBALS['smarty']->assign('nav_page', $nav_page);
            $GLOBALS['smarty']->assign('page', $page);
            $GLOBALS['smarty']->assign('topBanner', $topBanner);
            $GLOBALS['smarty']->assign('user_id', $user_id);

            $GLOBALS['smarty']->assign('site_domain', url('/') . '/');

            $bg_image = getleft_attr("content", 0, $pc_page['tem'], config('shop.template'));
            $GLOBALS['smarty']->assign('bg_image', $bg_image);

            return $GLOBALS['smarty']->display('homeindex.dwt');
        });

        return $content;
    }
}
