<?php

namespace App\Modules\Admin\Controllers;

use App\Models\TouchTopic;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Store\StoreCommonService;

/**
 * 专题管理
 */
class TouchTopicController extends InitController
{
    protected $dscRepository;
    protected $merchantCommonService;
    protected $storeCommonService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        StoreCommonService $storeCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->storeCommonService = $storeCommonService;
    }

    public function index()
    {

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
        /* 配置风格颜色选项 */
        $topic_style_color = [
            '0' => '008080',
            '1' => '008000',
            '2' => 'ffa500',
            '3' => 'ff0000',
            '4' => 'ffff00',
            '5' => '9acd32',
            '6' => 'ffd700'
        ];
        $allow_suffix = ['gif', 'jpg', 'png', 'jpeg', 'bmp', 'swf'];

        /*------------------------------------------------------ */
        //-- 专题列表页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            admin_priv('topic_manage');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['09_topic']);

            $this->smarty->assign('full_page', 1);
            $list = $this->get_topic_list();

            $this->smarty->assign('topic_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);


            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['topic_add'], 'href' => 'touch_topic.php?act=add']);
            return $this->smarty->display('touch_topic_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加/编辑专题
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
            admin_priv('topic_manage');

            $isadd = $_REQUEST['act'] == 'add';
            $this->smarty->assign('isadd', $isadd);
            $topic_id = empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['09_topic']);
            $this->smarty->assign('action_link', $this->list_link($isadd));

            set_default_filter(); //设置默认筛选
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);
            $this->smarty->assign('topic_style_color', $topic_style_color);

            $width_height = $this->get_toppic_width_height();
            if (isset($width_height['pic']['width']) && isset($width_height['pic']['height'])) {
                $this->smarty->assign('width_height', sprintf($GLOBALS['_LANG']['tips_width_height'], $width_height['pic']['width'] . 'px', $width_height['pic']['height'] . 'px'));
            }
            if (isset($width_height['title_pic']['width']) && isset($width_height['title_pic']['height'])) {
                $this->smarty->assign('title_width_height', sprintf($GLOBALS['_LANG']['tips_title_width_height'], $width_height['title_pic']['width'] . 'px', $width_height['title_pic']['height'] . 'px'));
            }
            if (!$isadd) {
                $sql = "SELECT * FROM " . $this->dsc->table('touch_topic') . " WHERE topic_id = '$topic_id'";
                $topic = $this->db->getRow($sql);
                $topic['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $topic['start_time']);
                $topic['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $topic['end_time']);
                //by wu start
                $topic['topic_data'] = [];
                $topic_data = @unserialize($topic['data']);
                if ($topic_data) {
                    foreach ($topic_data as $key => $val) {
                        foreach ($val as $k => $v) {
                            $goods_info = explode("|", $v);
                            $topic['topic_data'][$key][$k] = ['value' => $goods_info[1], 'text' => $goods_info[0]];
                        }
                    }
                }
                //by wu end

                create_html_editor('topic_intro', $topic['intro']);


                $topic['data'] = addcslashes($topic['data'], "'");
                $topic['data'] = json_encode(@unserialize($topic['data']));
                $topic['data'] = addcslashes($topic['data'], "'");

                if (empty($topic['topic_img']) && empty($topic['htmls'])) {
                    $topic['topic_type'] = 0;
                } elseif ($topic['htmls'] != '') {
                    $topic['topic_type'] = 2;
                } elseif (preg_match('/.swf$/i', $topic['topic_img'])) {
                    $topic['topic_type'] = 1;
                } else {
                    $topic['topic_type'] = '';
                }

                $this->smarty->assign('topic', $topic);
                $this->smarty->assign('act', "update");
            } else {
                $topic = ['title' => '', 'topic_type' => 0, 'url' => 'http://'];

                $topic['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', time());
                $topic['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', time() + 4 * 86400);

                $this->smarty->assign('topic', $topic);
                create_html_editor('topic_intro');
                $this->smarty->assign('act', "insert");
            }

            return $this->smarty->display('touch_topic_edit.dwt');
        }

        /*------------------------------------------------------ */
        //-- 插�        �/更新专题数据
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
            admin_priv('topic_manage');

            $is_insert = $_REQUEST['act'] == 'insert';
            $topic_id = empty($_POST['topic_id']) ? 0 : intval($_POST['topic_id']);
            $topic_type = empty($_POST['topic_type']) ? 0 : intval($_POST['topic_type']);

            $topic_img = '';
            $htmls = '';
            switch ($topic_type) {
                case '0':
                case '1':

                    // 主图上传
                    if ($_FILES['topic_img']['name'] && $_FILES['topic_img']['size'] > 0) {
                        /* 检查文件合法性 */
                        if (!get_file_suffix($_FILES['topic_img']['name'], $allow_suffix)) {
                            return sys_msg($GLOBALS['_LANG']['invalid_type']);
                        }

                        /* 处理 */
                        $name = TimeRepository::getLocalDate('Ymd');
                        for ($i = 0; $i < 6; $i++) {
                            $name .= chr(mt_rand(97, 122));
                        }
                        $topic_img_ex = explode('.', $_FILES['topic_img']['name']);
                        $name .= '.' . end($topic_img_ex);
                        $target = storage_public(DATA_DIR . '/afficheimg/' . $name);

                        if (move_upload_file($_FILES['topic_img']['tmp_name'], $target)) {
                            $topic_img = DATA_DIR . '/afficheimg/' . $name;
                        }
                    } elseif (!empty($_REQUEST['url'])) {
                        /* 来自互联网图片 不可以是服务器地址 */
                        if (strstr($_REQUEST['url'], 'http') && !strstr($_REQUEST['url'], request()->server('SERVER_NAME'))) {
                            /* 取互联网图片至本地 */
                            $topic_img = $this->get_url_image($_REQUEST['url']);
                        } else {
                            return sys_msg($GLOBALS['_LANG']['web_url_no']);
                        }
                    }
                    unset($name, $target);

                    $topic_img = empty($topic_img) ? $_POST['img_url'] : $topic_img;
                    $htmls = '';

                    break;

                case '2':

                    $htmls = $_POST['htmls'];

                    $topic_img = '';

                    break;
            }

            // 标题图上传
            if ($_FILES['title_pic']['name'] && $_FILES['title_pic']['size'] > 0) {
                /* 检查文件合法性 */
                if (!get_file_suffix($_FILES['title_pic']['name'], $allow_suffix)) {
                    return sys_msg($GLOBALS['_LANG']['invalid_type']);
                }

                /* 处理 */
                $name = TimeRepository::getLocalDate('Ymd');
                for ($i = 0; $i < 6; $i++) {
                    $name .= chr(mt_rand(97, 122));
                }
                $title_pic_ex = explode('.', $_FILES['title_pic']['name']);
                $name .= '.' . end($title_pic_ex);
                $target = storage_public(DATA_DIR . '/afficheimg/' . $name);

                if (move_upload_file($_FILES['title_pic']['tmp_name'], $target)) {
                    $title_pic = DATA_DIR . '/afficheimg/' . $name;
                }
            } elseif (!empty($_REQUEST['title_url'])) {
                /* 来自互联网图片 不可以是服务器地址 */
                if (strstr($_REQUEST['title_url'], 'http') && !strstr($_REQUEST['title_url'], request()->server('SERVER_NAME'))) {
                    /* 取互联网图片至本地 */
                    $title_pic = $this->get_url_image($_REQUEST['title_url']);
                } else {
                    return sys_msg($GLOBALS['_LANG']['web_url_no']);
                }
            }
            unset($name, $target);

            $title_pic = empty($title_pic) ? $_POST['title_img_url'] : $title_pic;


            $start_time = TimeRepository::getLocalStrtoTime($_POST['start_time']);
            $end_time = TimeRepository::getLocalStrtoTime($_POST['end_time']);

            $this->dscRepository->getOssAddFile([$topic_img, $title_pic]);

            $tmp_data = dsc_decode($_POST['topic_data'], true);
            $data = serialize($tmp_data);
            $base_style = $_POST['base_style'];
            $keywords = $_POST['keywords'];
            $description = $_POST['description'];

            $record = [
                'title' => $_POST['topic_name'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'data' => $data,
                'intro' => $_POST['topic_intro'],
                'template' => $_POST['topic_template_file'],
                'css' => $_POST['topic_css'],
                'base_style' => $base_style,
                'htmls' => $htmls,
                'keywords' => $keywords,
                'description' => $description
            ];

            if ($topic_img) {
                $record['topic_img'] = $topic_img;
            }

            if ($title_pic) {
                $record['title_pic'] = $title_pic;
            }

            if ($is_insert) {
                $record['user_id'] = $adminru['ru_id'];
                $record['review_status'] = 3;
                $this->db->autoExecute($this->dsc->table('touch_topic'), $record, 'INSERT');
            } else {
                if (isset($_POST['review_status'])) {
                    $review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
                    $review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';

                    $record['review_status'] = $review_status;
                    $record['review_content'] = $review_content;
                }

                $this->db->autoExecute($this->dsc->table('touch_topic'), $record, 'UPDATE', "topic_id = '$topic_id'");
            }

            clear_cache_files();

            $links[] = ['href' => 'touch_topic.php', 'text' => $GLOBALS['_LANG']['back_list']];
            return sys_msg($GLOBALS['_LANG']['succed'], 0, $links);
        } elseif ($_REQUEST['act'] == 'get_goods_list') {
            $filters = dsc_decode($_GET['JSON']);

            $arr = get_goods_list($filters);
            $opt = [];

            foreach ($arr as $key => $val) {
                $opt[] = ['value' => $val['goods_id'],
                    'text' => $val['goods_name']];
            }
            return make_json_result($opt);
        } elseif ($_REQUEST["act"] == "delete") {
            admin_priv('topic_manage');

            //删除图片
            $this->dscRepository->getDelBatch($_POST['checkboxes'], intval($_GET['id']), ['topic_img', 'title_pic'], 'topic_id', TouchTopic::whereRaw(1), 1);

            $sql = "DELETE FROM " . $this->dsc->table('touch_topic') . " WHERE ";
            if (!empty($_POST['checkboxes'])) {
                $sql .= db_create_in($_POST['checkboxes'], 'topic_id');
            } elseif (!empty($_GET['id'])) {
                $_GET['id'] = intval($_GET['id']);
                $sql .= "topic_id = '$_GET[id]'";
            }

            $this->db->query($sql);

            clear_cache_files();

            if (!empty($_REQUEST['is_ajax'])) {
                $url = 'touch_topic.php?act=query&' . str_replace('act=delete', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            }

            $links[] = ['href' => 'touch_topic.php', 'text' => $GLOBALS['_LANG']['back_list']];
            return sys_msg($GLOBALS['_LANG']['succed'], 0, $links);
        } elseif ($_REQUEST["act"] == "query") {
            $topic_list = $this->get_topic_list();
            $this->smarty->assign('topic_list', $topic_list['item']);
            $this->smarty->assign('filter', $topic_list['filter']);
            $this->smarty->assign('record_count', $topic_list['record_count']);
            $this->smarty->assign('page_count', $topic_list['page_count']);
            $this->smarty->assign('use_storage', empty($GLOBALS['_CFG']['use_storage']) ? 0 : 1);

            /* 排序标记 */
            $sort_flag = sort_flag($topic_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $tpl = 'touch_topic_list.dwt';
            return make_json_result($this->smarty->fetch($tpl), '', ['filter' => $topic_list['filter'], 'page_count' => $topic_list['page_count']]);
        }
    }

    /**
     * 获取专题列表
     * @access  public
     * @return void
     */
    private function get_topic_list()
    {
        $adminru = get_admin_ru_id();

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_topic_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 't.topic_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

        $where = "1";
        $where .= (!empty($filter['keywords'])) ? " AND t.title like '%" . mysql_like_quote($filter['keywords']) . "%'" : '';

        if ($adminru['ru_id'] > 0) {
            $where .= " AND t.user_id = '" . $adminru['ru_id'] . "' ";
        }

        if ($filter['review_status']) {
            $where .= " AND t.review_status = '" . $filter['review_status'] . "' ";
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        $store_where = '';
        $store_search_where = '';
        if ($filter['store_search'] > -1) {
            if ($adminru['ru_id'] == 0) {
                if ($filter['store_search'] > 0) {
                    $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($store_type) {
                        $store_search_where = "AND msi.shop_name_suffix = '$store_type'";
                    }

                    if ($filter['store_search'] == 1) {
                        $where .= " AND t.user_id = '" . $filter['merchant_id'] . "' ";
                    } elseif ($filter['store_search'] == 2) {
                        $store_where .= " AND msi.rz_shop_name LIKE '%" . mysql_like_quote($filter['store_keyword']) . "%'";
                    } elseif ($filter['store_search'] == 3) {
                        $store_where .= " AND msi.shoprz_brand_name LIKE '%" . mysql_like_quote($filter['store_keyword']) . "%' " . $store_search_where;
                    }

                    if ($filter['store_search'] > 1) {
                        $where .= " AND (SELECT msi.user_id FROM " . $this->dsc->table('merchants_shop_information') . ' as msi ' .
                            " WHERE msi.user_id = t.user_id $store_where) > 0 ";
                    }
                } else {
                    $where .= " AND t.user_id = 0";
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('touch_topic') . " AS t " . " WHERE $where";
        $filter['record_count'] = $this->db->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $sql = "SELECT t.* FROM " . $this->dsc->table('touch_topic') . " AS t " . " WHERE $where ORDER BY $filter[sort_by] $filter[sort_order]";

        $query = $this->db->selectLimit($sql, $filter['page_size'], $filter['start']);

        $res = [];

        if ($query) {

            $ru_id = BaseRepository::getKeyPluck($query, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($query as $topic) {
                $topic['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $topic['start_time']);
                $topic['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $topic['end_time']);
                $topic['url'] = $this->dsc->url() . 'topic.php?topic_id=' . $topic['topic_id'];
                $topic['ru_name'] = $merchantList[$topic['user_id']]['shop_name'] ?? [];
                $res[] = $topic;
            }
        }


        $arr = ['item' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 列表链接
     * @param bool $is_add 是否添加（插入）
     * @param string $text 文字
     * @return  array('href' => $href, 'text' => $text)
     */
    private function list_link($is_add = true, $text = '')
    {
        $href = 'touch_topic.php?act=list';
        if (!$is_add) {
            $href .= '&' . list_link_postfix();
        }
        if ($text == '') {
            $text = $GLOBALS['_LANG']['topic_list'];
        }

        return ['href' => $href, 'text' => $text];
    }

    private function get_toppic_width_height()
    {
        $width_height = [];

        $file_path = storage_public('themes/' . $GLOBALS['_CFG']['template'] . '/topic.dwt');
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return $width_height;
        }

        $string = file_get_contents($file_path);

        $pattern_width = '/var\s*topic_width\s*=\s*"(\d+)";/';
        $pattern_height = '/var\s*topic_height\s*=\s*"(\d+)";/';
        preg_match($pattern_width, $string, $width);
        preg_match($pattern_height, $string, $height);
        if (isset($width[1])) {
            $width_height['pic']['width'] = $width[1];
        }
        if (isset($height[1])) {
            $width_height['pic']['height'] = $height[1];
        }
        unset($width, $height);

        $pattern_width = '/TitlePicWidth:\s{1}(\d+)/';
        $pattern_height = '/TitlePicHeight:\s{1}(\d+)/';
        preg_match($pattern_width, $string, $width);
        preg_match($pattern_height, $string, $height);
        if (isset($width[1])) {
            $width_height['title_pic']['width'] = $width[1];
        }
        if (isset($height[1])) {
            $width_height['title_pic']['height'] = $height[1];
        }

        return $width_height;
    }

    private function get_url_image($url)
    {
        $ext = strtolower(end(explode('.', $url)));
        if ($ext != "gif" && $ext != "jpg" && $ext != "png" && $ext != "bmp" && $ext != "jpeg") {
            return $url;
        }

        $name = TimeRepository::getLocalDate('Ymd');
        for ($i = 0; $i < 6; $i++) {
            $name .= chr(mt_rand(97, 122));
        }
        $name .= '.' . $ext;

        $tmp_file = DATA_DIR . '/afficheimg/' . $name;
        $filename = storage_public($tmp_file);

        $img = file_get_contents($url);

        $fp = @fopen($filename, "a");
        fwrite($fp, $img);
        fclose($fp);

        return $tmp_file;
    }
}
