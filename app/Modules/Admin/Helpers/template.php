<?php

use App\Repositories\Common\StrRepository;

/**
 * 管理中心模版相关公用函数库
 */


/* 可以设置内容的模板 */
$template_files = [
    'index.blade.php',
    'article.blade.php',
    'article_cat.blade.php',
    'brand.blade.php',
    'category.blade.php',
    'user_clips.blade.php',
    'compare.blade.php',
    'gallery.blade.php',
    'goods.blade.php',
    'group_buy_goods.blade.php',
    'group_buy_flow.blade.php',
    'group_buy_list.blade.php',
    'user_passport.blade.php',
    'pick_out.blade.php',
    'receive.blade.php',
    'respond.blade.php',
    'search.blade.php',
    'flow.blade.php',
    'snatch.blade.php',
    'user.blade.php',
    'tag_cloud.blade.php',
    'user_transaction.blade.php',
    'style.css',
    'auction_list.blade.php',
    'auction.blade.php',
    'message_board.blade.php',
    'exchange_list.blade.php',

    //商家入驻 start
    'merchants.blade.php',
    'merchants_steps.blade.php',
    'merchants_store.blade.php',
    'merchants_index.blade.php',
    //商家入驻 end
];

/* 每个模板允许设置的库项目 */
$page_libs = [
    'article' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/comments.blade.php' => 0,
        '/library/goods_related.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 3,
        '/library/history.blade.php' => 0,
    ],
    'article_cat' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/recommend_promotion.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/vote_list.blade.php' => 0,
        '/library/article_category_tree.blade.php' => 0,
    ],
    'brand' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/goods_list.blade.php' => 0,
        '/library/pages.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/vote_list.blade.php' => 0,
    ],
    'category' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/goods_list.blade.php' => 0,
        '/library/pages.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 3,
        '/library/brands.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/vote_list.blade.php' => 0
    ],
    'compare' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
    ],
    'flow' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 3,
    ],
    'index' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/new_articles.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/invoice_query.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_new.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/recommend_promotion.blade.php' => 4,
        '/library/group_buy.blade.php' => 3,
        '/library/auction.blade.php' => 3,
        '/library/brands.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/order_query.blade.php' => 0,
        '/library/email_list.blade.php' => 0,
        '/library/vote_list.blade.php' => 0
    ],
    'goods' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 4,
        '/library/goods_attrlinked.blade.php' => 0,
        //ecmoban模板堂 --zhuo start
        '/library/history_goods.blade.php' => 0,
        '/library/recommend_best_goods.blade.php' => 3,
        '/library/recommend_new_goods.blade.php' => 3,
        '/library/recommend_hot_goods.blade.php' => 3,
        //ecmoban模板堂 --zhuo end
        '/library/goods_fittings.blade.php' => 0,
        '/library/goods_gallery.blade.php' => 0,
        '/library/goods_tags.blade.php' => 0,
        '/library/comments.blade.php' => 0,
        '/library/bought_goods.blade.php' => 0,
        '/library/bought_note_guide.blade.php' => 0,
        '/library/goods_related.blade.php' => 0,
        '/library/goods_article.blade.php' => 0,
        '/library/relatetag.blade.php' => 0,
    ],
    'search_result' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/search_result.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/search_advanced.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/pages.blade.php' => 0,
    ],
    'tag_cloud' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_new.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/recommend_promotion.blade.php' => 3,
    ],
    'group_buy_goods' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/goods_related.blade.php' => 0,
        '/library/brands.blade.php' => 3,
    ],
    'group_buy_list' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/brands.blade.php' => 3,
    ],
    'search' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
    ],
    'snatch_list' => [
        '/library/ur_here.blade.php' => 0,
        '/library/snatch_hot.blade.php' => 3,
    ],
    'snatch' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
    ],
    'auction_list' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/auction_hot.blade.php' => 3,
    ],
    'auction' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
    ],
    'message_board' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/message_list.blade.php' => 10,
    ],
    'exchange_list' => [
        '/library/ur_here.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/pages.blade.php' => 0,
        '/library/exchange_hot.blade.php' => 3,
        '/library/exchange_best.blade.php' => 5, //ecmoban模板堂 --zhuo
        '/library/exchange_list.blade.php' => 0,
    ],
    'merchants' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/new_articles.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/invoice_query.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_new.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/recommend_promotion.blade.php' => 4,
        '/library/group_buy.blade.php' => 3,
        '/library/auction.blade.php' => 3,
        '/library/brands.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/order_query.blade.php' => 0,
        '/library/email_list.blade.php' => 0,
        '/library/vote_list.blade.php' => 0
    ],
    'merchants_steps' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/new_articles.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/invoice_query.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_new.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/recommend_promotion.blade.php' => 4,
        '/library/group_buy.blade.php' => 3,
        '/library/auction.blade.php' => 3,
        '/library/brands.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/order_query.blade.php' => 0,
        '/library/email_list.blade.php' => 0,
        '/library/vote_list.blade.php' => 0
    ],
    'merchants_store' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/goods_list.blade.php' => 0,
        '/library/pages.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 3,
        '/library/brands.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/vote_list.blade.php' => 0
    ],
    'merchants_index' => [
        '/library/ur_here.blade.php' => 0,
        '/library/search_form.blade.php' => 0,
        '/library/member.blade.php' => 0,
        '/library/category_tree.blade.php' => 0,
        '/library/top10.blade.php' => 0,
        '/library/history.blade.php' => 0,
        '/library/recommend_best.blade.php' => 3,
        '/library/recommend_hot.blade.php' => 3,
        '/library/goods_list.blade.php' => 0,
        '/library/pages.blade.php' => 0,
        '/library/recommend_promotion.blade.php' => 3,
        '/library/brands.blade.php' => 3,
        '/library/promotion_info.blade.php' => 0,
        '/library/cart.blade.php' => 0,
        '/library/vote_list.blade.php' => 0
    ],
];

/* 动态库项目 */
$dyna_libs = [
    'cat_goods',
    'brand_goods',
    'cat_articles',
    'ad_position',
];

///* 插件的 library */
//$sql = 'SELECT code, library FROM ' . $this->dsc->table('plugins') . " WHERE assign = 1 AND library > ''";
//$res = $this->db->query($sql);
//
//foreach ($res as $row )
//{
//    include_once('../plugins/' . $row['code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php');
//
//    $page_libs['index'][] = $row['library'];
//}

/**
 * 获得模版的信息
 *
 * @access  private
 * @param string $template_name 模版名
 * @param string $template_style 模版风格名
 * @return  array
 */
function get_template_info($template_name, $template_style = '')
{
    if (empty($template_style) || $template_style == '') {
        $template_style = '';
    }

    $info = [];
    $ext = ['png', 'gif', 'jpg', 'jpeg'];

    $info['code'] = $template_name;
    $info['screenshot'] = '';
    $info['stylename'] = $template_style;

    if ($template_style == '') {
        foreach ($ext as $val) {
            $screenshot_file = public_path('themes/' . $template_name . "/images/screenshot.$val");

            if (file_exists($screenshot_file)) {
                $info['screenshot'] = $screenshot_file;

                break;
            }
        }
    } else {
        foreach ($ext as $val) {
            $screenshot_file = public_path('themes/' . $template_name . "/images/screenshot_$template_style.$val");

            if (file_exists()) {
                $info['screenshot'] = $screenshot_file;

                break;
            }
        }
    }

    $css_path = public_path('themes/' . $template_name . '/style.css');
    if ($template_style != '') {
        $css_path = public_path('themes/' . $template_name . "/style_$template_style.css");
    }
    if (file_exists($css_path) && !empty($template_name)) {
        $arr = array_slice(file($css_path), 0, 11);

        $template_name = isset($arr[1]) ? explode(': ', $arr[1]) : [];
        $template_uri = isset($arr[2]) ? explode(': ', $arr[2]) : [];
        $template_desc = isset($arr[3]) ? explode(': ', $arr[3]) : [];
        $template_version = isset($arr[4]) ? explode(': ', $arr[4]) : [];
        $template_author = isset($arr[5]) ? explode(': ', $arr[5]) : [];
        $author_uri = isset($arr[6]) ? explode(': ', $arr[6]) : [];
        $logo_filename = isset($arr[7]) ? explode(': ', $arr[7]) : [];
        $business_logo_filename = isset($arr[8]) ? explode(': ', $arr[8]) : [];
        $template_type = isset($arr[9]) ? explode(': ', $arr[9]) : [];


        $info['name'] = isset($template_name[1]) ? trim($template_name[1]) : '';
        $info['uri'] = isset($template_uri[1]) ? trim($template_uri[1]) : '';
        $info['desc'] = isset($template_desc[1]) ? trim($template_desc[1]) : '';
        $info['version'] = isset($template_version[1]) ? trim($template_version[1]) : '';
        $info['author'] = isset($template_author[1]) ? trim($template_author[1]) : '';
        $info['author_uri'] = isset($author_uri[1]) ? trim($author_uri[1]) : '';
        $info['logo'] = isset($logo_filename[1]) ? trim($logo_filename[1]) : '';
        $info['business_logo'] = isset($business_logo_filename[1]) ? trim($business_logo_filename[1]) : '';
        $info['type'] = isset($template_type[1]) ? trim($template_type[1]) : '';
    } else {
        $info['name'] = '';
        $info['uri'] = '';
        $info['desc'] = '';
        $info['version'] = '';
        $info['author'] = '';
        $info['author_uri'] = '';
        $info['logo'] = '';
        $info['business_logo'] = '';
    }

    return $info;
}

/**
 * 获得模版文件中的编辑区域及其内容
 *
 * @access  public
 * @param string $tmp_name 模版名称
 * @param string $tmp_file 模版文件名称
 * @return  array
 */
function get_template_region($tmp_name, $tmp_file, $lib = true)
{
    $file = '../themes/' . $tmp_name . '/' . $tmp_file;

    /* 判断文件是否存在 by wu */
    if (!file_exists($file)) {
        return sys_msg($GLOBALS['_LANG']['have_no_file']);
    }

    /* 将模版文件的内容读入内存 */
    $content = file_get_contents($file);

    /* 获得所有编辑区域 */
    static $regions = [];

    if (empty($regions)) {
        $matches = [];
        $result = preg_match_all('/(<!--\\s*TemplateBeginEditable\\sname=")([^"]+)("\\s*-->)/', $content, $matches, PREG_SET_ORDER);

        if ($result && $result > 0) {
            foreach ($matches as $key => $val) {
                if ($val[2] != 'doctitle' && $val[2] != 'head') {
                    $regions[] = $val[2];
                }
            }
        }
    }

    if (!$lib) {
        return $regions;
    }

    $libs = [];
    /* 遍历所有编辑区 */
    foreach ($regions as $key => $val) {
        $matches = [];
        $pattern = '/(<!--\\s*TemplateBeginEditable\\sname="%s"\\s*-->)(.*?)(<!--\\s*TemplateEndEditable\\s*-->)/s';

        if (preg_match(sprintf($pattern, $val), $content, $matches)) {
            /* 找出该编辑区域内所有库项目 */
            $lib_matches = [];

            $result = preg_match_all(
                '/([\s|\S]{0,20})(<!--\\s#BeginLibraryItem\\s")([^"]+)("\\s-->)/',
                $matches[2],
                $lib_matches,
                PREG_SET_ORDER
            );
            $i = 0;
            if ($result && $result > 0) {
                foreach ($lib_matches as $k => $v) {
                    $v[3] = strtolower($v[3]);
                    $libs[] = ['library' => $v[3], 'region' => $val, 'lib' => basename(substr($v[3], 0, strpos($v[3], '.'))), 'sort_order' => $i];
                    $i++;
                }
            }
        }
    }

    return $libs;
}

/**
 * 将插件library从默认模板中移动到指定模板中
 *
 * @access  public
 * @param string $tmp_name 模版名称
 * @param string $msg 如果出错，保存错误信息，否则为空
 * @return  Boolen
 */
function move_plugin_library($tmp_name, &$msg)
{
    $sql = 'SELECT code, library FROM ' . $GLOBALS['dsc']->table('plugins') . " WHERE library > ''";
    $rec = $GLOBALS['db']->query($sql);
    $return_value = true;
    $target_dir = plugin_path('themes/' . $tmp_name);
    $source_dir = plugin_path('themes/' . $GLOBALS['_CFG']['template']);

    if ($rec) {
        foreach ($rec as $row) {
            //先移动，移动失败试则拷贝
            if (!@rename($source_dir . $row['library'], $target_dir . $row['library'])) {
                $dir = plugin_path('plugins/' . StrRepository::studly($row['code']) . '/temp');
                if (!file_exists($dir)) {
                    make_dir($dir);
                }

                if (!@copy(plugin_path('plugins/' . StrRepository::studly($row['code']) . '/temp' . $row['library'], $target_dir . $row['library']))) {
                    $return_value = false;
                    $msg .= "\n moving " . $row['library'] . ' failed';
                }
            }
        }
    }
}

/**
 * 获得指定库项目在模板中的设置内容
 *
 * @access  public
 * @param string $lib 库项目
 * @param array $libs 包含设定内容的数组
 * @return  void
 */
function get_setted($lib, &$arr)
{
    $options = ['region' => '', 'sort_order' => 0, 'display' => 0];

    foreach ($arr as $key => $val) {
        if ($lib == $val['library']) {
            $options['region'] = $val['region'];
            $options['sort_order'] = $val['sort_order'];
            $options['display'] = 1;

            break;
        }
    }

    return $options;
}

/**
 * 从相应模板xml文件中获得指定模板文件中的可编辑区信息
 *
 * @access  public
 * @param string $curr_template 当前模板文件名
 * @param array $curr_page_libs 缺少xml文件时的默认编辑区信息数组
 * @return  array   $edit_libs        返回可编辑的库文件数组
 */
function get_editable_libs($curr_template, $curr_page_libs)
{
    $vals = [];
    $edit_libs = [];

    $path = storage_public('themes/' . $GLOBALS['_CFG']['template'] . '/libs.xml');

    if ($xml_content = @file_get_contents($path)) {
        $p = xml_parser_create();                                                   //把xml解析到数组
        xml_parse_into_struct($p, $xml_content, $vals, $index);
        xml_parser_free($p);

        $i = 0;
        for (; $i < sizeof($vals); $i++) {                                      //找到相应模板文件的位置
            if ($vals[$i]['tag'] == 'FILE' && isset($vals[$i]['attributes'])) {
                if ($vals[$i]['attributes']['NAME'] == $curr_template . '.blade.php') {
                    break;
                }
            }
        }

        while ($vals[++$i]['tag'] != 'FILE' || !isset($vals[$i]['attributes'])) {     //读出可编辑区库文件名称，放到一个数组中
            if ($vals[$i]['tag'] == 'LIB') {
                $edit_libs[] = $vals[$i]['value'];
            }
        }
    }

    return $edit_libs;
}
