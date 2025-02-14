<?php

use App\Models\OrderInfo;
use App\Repositories\Common\DscRepository;

function get_file_get_contents($url, $currentPage = 1)
{
    $content = getSslPage($url);//页面内容
    if (strpos($url, '.tmall.') !== false) {
        preg_match("/itemId=(.+?)\&shopId/", $content, $userNumId);
        if (preg_match("/seller_num_id=(.+?)\&isFromDetail/", $content)) {
            preg_match("/seller_num_id=(.+?)\&isFromDetail/", $content, $sellerId);
        } else {
            preg_match("/userid=(.+?)\;/", $content, $sellerId);
        }
        $userNumId = stripslashes(trim($userNumId[1]));
        $sellerId = stripslashes(trim($sellerId[1]));
        $comment_url = 'http://rate.tmall.com/list_detail_rate.htm?itemId=' . $userNumId . '&sellerId=' . $sellerId . '&content=1&currentPage=' . $currentPage;

        $pageContents = trim(getSslPage($comment_url));
        $pageContents = iconv('GB2312', 'UTF-8', $pageContents);
        //获取评论内容
        preg_match_all('/\"rateContent\"\:\"(.*?)\"\,\"/i', $pageContents, $match1);
        //获取用户列表
        preg_match_all('/displayUserNick\"\:\"(.*?)\",\"/i', $pageContents, $match2);
        //获取评论时间
        preg_match_all('/rateDate\"\:\"(.*?)\",\"/i', $pageContents, $match3);
        //获取评论用户id
        preg_match_all('/\"id\"\:(.*?),\"/i', $pageContents, $match4);

        $list['taobao'] = 0;
    } else {
        preg_match("/userNumId=(.*?)\&/", $content, $userNumId);
        preg_match("/auctionNumId=(.*?)\&/", $content, $auctionNumId);

        $userNumId = $userNumId[1];
        $auctionNumId = $auctionNumId[1];

        $comment_url = 'http://rate.taobao.com/feedRateList.htm?callback=jsonp_reviews_list&userNumId=' . $userNumId . '&auctionNumId=' . $auctionNumId . '&currentPageNum=' . $currentPage;

        $pageContents = trim(getSslPage($comment_url));
        $pageContents = iconv('gbk', 'UTF-8', $pageContents);
        //$pageContents = iconv('GB2312', 'GBK', $pageContents);
        //获取评论内容
        preg_match_all('/\,\"content\"\:\"(.*?)\"\,\"/i', $pageContents, $match1);
        //获取用户列表
        preg_match_all('/\"nick\"\:\"(.*?)\"\,\"nickUrl/i', $pageContents, $match2);
        //获取评论时间
        preg_match_all('/date\"\:\"(.*?)\",\"/i', $pageContents, $match3);
        //获取评论用户id
        preg_match_all('/\"rateId\"\:(.*?),\"/i', $pageContents, $match4);

        $list['taobao'] = 1;
    }

    $list['comment_list'] = $match1[1];
    $list['user_list'] = $match2[1];
    $list['dateList'] = $match3[1];
    $list['userId'] = $match4[1];

    return $list;
}

//将二维数组组合
function get_array_merge($arr = [])
{
    $arr_lenght = count($arr['comment_list']);

    $new_arr = [];
    for ($i = 0; $i <= $arr_lenght - 1; $i++) {
        if ($arr['taobao']) {
            $dateList = str_replace(["年", "月", "日"], '-', $arr['dateList'][$i]);
            $dateList_left = substr($dateList, 0, -7);
            $dateList_right = str_replace("-", "", strrchr($dateList, '-')) . ":00";

            $new_arr[$i] = $arr['comment_list'][$i] . "|---|" . $arr['user_list'][$i] . "|---|" . $dateList_left . $dateList_right . "|---|" . $arr['userId'][$i];
        } else {
            $new_arr[$i] = $arr['comment_list'][$i] . "|---|" . $arr['user_list'][$i] . "|---|" . $arr['dateList'][$i] . "|---|" . $arr['userId'][$i];
        }
        $merge_array[$i] = explode("|---|", $new_arr[$i]);
    }
    return $merge_array;
}

function get_tao_list($tao_list = [], $goods_name = '', $goods_id = '')
{
    $arr = [];
    if ($tao_list) {
        foreach ($tao_list as $key => $row) {
            $key = $key + 1;
            $arr[$key]['contents'] = $row[0];
            $arr[$key]['user_name'] = $row[1];
            $arr[$key]['add_time'] = $row[2];
            $arr[$key]['user_id'] = $row[3];
            $arr[$key]['goods_name'] = $goods_name;
            $arr[$key]['id_value'] = $goods_id;
        }
    }

    return $arr;
}

function curl_redir_exec($ch, $url)
{
    curl_setopt($ch, CURLOPT_URL, $url);
    static $curl_loops = 0;
    static $curl_max_loops = 20;
    if ($curl_loops++ >= $curl_max_loops) {
        $curl_loops = 0;
        return false;
    }
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    list($header, $data) = explode("\n\n", $data, 2);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        $matches = [];
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = @parse_url(trim(array_pop($matches)));
        if (!$url) {
            //couldn't process the url to redirect to
            $curl_loops = 0;
            return $data;
        }
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        if (!$url['scheme']) {
            $url['scheme'] = $last_url['scheme'];
        }
        if (!$url['host']) {
            $url['host'] = $last_url['host'];
        }
        if (!$url['path']) {
            $url['path'] = $last_url['path'];
        }
        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
        curl_setopt($ch, CURLOPT_URL, $new_url);
        //debug('Redirecting to', $new_url);
        return curl_redir_exec($ch, $new_url);
    } else {
        $curl_loops = 0;
        return $data;
    }
}

function getSslPage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/*获取订单商品*/
function get_order_goods_list($order_id)
{
    /* 取得订单商品 */
    $goods_list = [];
    $sql = "SELECT o.*, g.goods_thumb, g.brand_id, g.user_id AS ru_id, g.goods_number AS storage, o.goods_attr " .
        "FROM " . $GLOBALS['dsc']->table('order_goods') . " AS o " .
        "LEFT JOIN " . $GLOBALS['dsc']->table('goods') . " AS g ON o.goods_id = g.goods_id " .
        "WHERE o.order_id = '{$order_id}' ";
    $res = $GLOBALS['db']->query($sql);
    foreach ($res as $row) {
        /* 虚拟商品支持 */
        if ($row['is_real'] == 0) {
            /* 取得语言项 */
            $filename = app_path('Plugins/' . $row['extension_code'] . '/Languages/common_' . $GLOBALS['_CFG']['lang'] . '.php');
            if (file_exists($filename)) {
                include_once($filename);
                if (!empty($GLOBALS['_LANG'][$row['extension_code'] . '_link'])) {
                    $order_sn = OrderInfo::where('order_id', $order_id)->value('order_sn');
                    $order_sn = $order_sn ? $order_sn : '';

                    $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'] . '_link'], $row['goods_id'], $order_sn);
                }
            }
        }
        $row['formated_subtotal'] = app(DscRepository::class)->getPriceFormat($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price'] = app(DscRepository::class)->getPriceFormat($row['goods_price']);
        $_goods_thumb = app(DscRepository::class)->getImagePath($row['goods_thumb']);
        $row['goods_thumb'] = $_goods_thumb;
        $row['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
        $goods_list[] = $row;
    }
    return $goods_list;
}
