<?php

namespace App\Libraries;

/**
 * Google sitemap 类
 */
class GoogleSitemap
{
    public $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    public $charset = "UTF-8";
    public $footer = "\t</urlset>\n";
    public $items = [];

    /**
     * 增加一个新的子项
     * @access   public
     * @param google_sitemap  item    $new_item
     */
    public function add_item($new_item)
    {
        $this->items[] = $new_item;
    }

    /**
     * 生成XML文档
     * @access    public
     * @param string $file_name 如果提供了文件名则生成文件，否则返回字符串.
     * @return [void|string]
     */
    public function build($file_name = null)
    {
        $map = $this->header . "\n";

        foreach ($this->items as $item) {
            $item->loc = htmlentities($item->loc, ENT_QUOTES);
            $map .= "\t\t<url>\n\t\t\t<loc>$item->loc</loc>\n";

            // lastmod
            if (!empty($item->lastmod)) {
                $map .= "\t\t\t<lastmod>$item->lastmod</lastmod>\n";
            }

            // changefreq
            if (!empty($item->changefreq)) {
                $map .= "\t\t\t<changefreq>$item->changefreq</changefreq>\n";
            }

            // priority
            if (!empty($item->priority)) {
                $map .= "\t\t\t<priority>$item->priority</priority>\n";
            }

            $map .= "\t\t</url>\n\n";
        }

        $map .= $this->footer . "\n";

        if (!is_null($file_name)) {
            return file_put_contents($file_name, $map);
        } else {
            return $map;
        }
    }
}
