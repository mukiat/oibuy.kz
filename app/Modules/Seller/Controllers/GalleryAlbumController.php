<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\FileUpload;
use App\Libraries\Image;
use App\Models\GalleryAlbum;
use App\Models\Goods;
use App\Models\PicAlbum;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Other\GalleryAlbumManageService;

/**
 * 图片库管理
 */
class GalleryAlbumController extends InitController
{
    protected $goodsManageService;
    protected $galleryAlbumManageService;
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        GoodsManageService $goodsManageService,
        GalleryAlbumManageService $galleryAlbumManageService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->goodsManageService = $goodsManageService;
        $this->galleryAlbumManageService = $galleryAlbumManageService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $act = e(trim(request()->input('act', '')));

        $adminru = get_admin_ru_id();

        $this->smarty->assign("priv_ru", 1);
        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'gallery_album']);
        /* 允许上传的文件类型 */
        $allow_file_types = '|GIF|JPG|PNG|JPEG|';

        /* -------------------------------------------------------- */
        // 图片库列表
        /* -------------------------------------------------------- */
        if ($act == 'list') {
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['gallery_album']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['add_album'], 'href' => 'gallery_album.php?act=add', 'class' => 'icon-plus']);

            $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);

            if ($parent_id > 0) {
                $parent_album_id = GalleryAlbum::where('album_id', $parent_id)->value('parent_album_id');
                $parent_album_id = $parent_album_id ?? 0;

                $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['return_to_superior'], 'href' => 'gallery_album.php?act=list&parent_id=' . $parent_album_id]);
            }

            $offline_store = $this->getGalleryAlbumList($adminru['ru_id']);

            $this->smarty->assign('gallery_album', $offline_store['pzd_list']);
            $this->smarty->assign('filter', $offline_store['filter']);
            $this->smarty->assign('record_count', $offline_store['record_count']);
            $this->smarty->assign('page_count', $offline_store['page_count']);
            $this->smarty->assign('full_page', 1);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($offline_store, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            return $this->smarty->display("gallery_album.dwt");
        } elseif ($act == 'query') {
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

            $offline_store = $this->getGalleryAlbumList();
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('gallery_album', $offline_store['pzd_list']);
            $this->smarty->assign('filter', $offline_store['filter']);
            $this->smarty->assign('record_count', $offline_store['record_count']);
            $this->smarty->assign('page_count', $offline_store['page_count']);
            //分页
            $page_count_arr = seller_page($offline_store, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            //跳转页面
            return make_json_result($this->smarty->fetch('gallery_album.dwt'), '', ['filter' => $offline_store['filter'], 'page_count' => $offline_store['page_count']]);
        } elseif ($act == 'add' || $act == 'edit') {
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_album']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['gallery_album'], 'href' => 'gallery_album.php?act=list', 'class' => 'icon-reply']);

            $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
            $album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $album_info = [];
            if ($act == 'add') {
                $cat_select = gallery_cat_list(0, 0, false, 0, true, $adminru['ru_id']);

                /* 简单处理缩进 */
                foreach ($cat_select as $k => $v) {
                    if ($v['level']) {
                        $level = str_repeat('&nbsp;', $v['level'] * 4);
                        $cat_select[$k]['name'] = $level . $v['name'];
                    }
                }
                $album_info['parent_album_id'] = $parent_id;
                $album_info['ru_id'] = $adminru['ru_id'];
                $this->smarty->assign('cat_select', $cat_select);
            } else {
                $cat_parent_id = $cat_info['parent_id'] ?? 0;
                $cat_select = gallery_cat_list(0, $cat_parent_id, false, 0, true, $adminru['ru_id']);
                $cat_child = get_cat_child($album_id);
                /* 简单处理缩进 */
                foreach ($cat_select as $k => $v) {
                    if ($v['level']) {
                        $level = str_repeat('&nbsp;', $v['level'] * 4);
                        $cat_select[$k]['name'] = $level . $v['name'];
                    }
                    if (!empty($cat_child) && in_array($v['album_id'], $cat_child)) {
                        unset($cat_select[$k]);
                    }
                }
                $this->smarty->assign('cat_select', $cat_select);
            }

            if ($album_id > 0) {
                $album_info = get_goods_gallery_album(2, $album_id);
            }
            $this->smarty->assign("album_info", $album_info);
            $form_action = ($act == 'add') ? "insert" : "update";
            $this->smarty->assign("form_action", $form_action);

            return $this->smarty->display("gallery_album_info.dwt");
        } elseif ($act == 'insert' || $act == 'update') {
            $album_mame = isset($_REQUEST['album_mame']) ? addslashes($_REQUEST['album_mame']) : '';
            $album_desc = isset($_REQUEST['album_desc']) ? addslashes($_REQUEST['album_desc']) : '';
            $sort_order = isset($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
            $parent_id = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;

            if ($act == 'insert') {
                /*检查是否重复*/
                $res = GalleryAlbum::where('album_mame', $album_mame)->where('ru_id', $adminru['ru_id'])->count();
                if ($res > 0) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['title_exist'], stripslashes($album_mame)), 1);
                }
                /* 取得文件地址 */
                $file_url = '';
                if ((isset($_FILES['album_cover']['error']) && $_FILES['album_cover']['error'] == 0) || (!isset($_FILES['album_cover']['error']) && isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] != 'none')) {
                    // 检查文件格式
                    if (!check_file_type($_FILES['album_cover']['tmp_name'], $_FILES['album_cover']['name'], $allow_file_types)) {
                        return sys_msg($GLOBALS['_LANG']['invalid_file']);
                    }

                    // 复制文件
                    $res = $this->upload_article_file($_FILES['album_cover']);
                    if ($res != false) {
                        $file_url = $res;
                    }
                }
                if ($file_url == '') {
                    $file_url = $_POST['file_url'];
                }

                $this->dscRepository->getOssAddFile([$file_url]);

                $data = [
                    'parent_album_id' => $parent_id,
                    'album_mame' => $album_mame,
                    'album_cover' => $file_url,
                    'album_desc' => $album_desc,
                    'sort_order' => $sort_order,
                    'add_time' => TimeRepository::getGmTime(),
                    'ru_id' => $adminru['ru_id']
                ];

                $data = BaseRepository::recursiveNullVal($data);

                GalleryAlbum::insertGetId($data);

                $link[0]['text'] = $GLOBALS['_LANG']['continue_add_album'];
                $link[0]['href'] = 'gallery_album.php?act=add';

                $link[1]['text'] = $GLOBALS['_LANG']['bank_list'];
                $link[1]['href'] = 'gallery_album.php?act=list';

                return sys_msg($GLOBALS['_LANG']['add_succeed'], 0, $link);
            } else {
                $album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
                /*检查是否重复*/
                $res = GalleryAlbum::where('album_mame', $album_mame)->where('ru_id', $adminru['ru_id'])->where('album_id', '<>', $album_id)->count();
                if ($res > 0) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['title_exist'], stripslashes($album_mame)), 1);
                }
                /* 取得文件地址 */
                $file_url = '';
                if ((isset($_FILES['album_cover']['error']) && $_FILES['album_cover']['error'] == 0) || (!isset($_FILES['album_cover']['error']) && isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] != 'none')) {
                    // 检查文件格式
                    if (!check_file_type($_FILES['album_cover']['tmp_name'], $_FILES['album_cover']['name'], $allow_file_types)) {
                        return sys_msg($GLOBALS['_LANG']['invalid_file']);
                    }

                    // 复制文件
                    $res = $this->upload_article_file($_FILES['album_cover']);
                    if ($res != false) {
                        $file_url = $res;
                    }
                }
                if ($file_url == '') {
                    $file_url = $_POST['file_url'];
                }

                $this->dscRepository->getOssAddFile([$file_url]);

                /* 如果 file_url 跟以前不一样，且原来的文件是本地文件，删除原来的文件 */
                $old_url = get_goods_gallery_album(0, $album_id, ['album_cover']);
                if ($old_url != '' && $old_url != $file_url && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) {
                    @unlink(storage_public($old_url));
                    $del_arr_img[] = $old_url;

                    $this->dscRepository->getOssDelFile($del_arr_img);
                }

                $data = [
                    'album_mame' => $album_mame,
                    'album_cover' => $file_url,
                    'album_desc' => $album_desc,
                    'sort_order' => $sort_order,
                    'parent_album_id' => $parent_id
                ];

                $data = BaseRepository::recursiveNullVal($data);

                GalleryAlbum::where('album_id', $album_id)->update($data);

                $link[0]['text'] = $GLOBALS['_LANG']['bank_list'];
                $link[0]['href'] = 'gallery_album.php?act=list';

                return sys_msg($GLOBALS['_LANG']['edit_succeed'], 0, $link);
            }
        } /*查看图片*/
        elseif ($act == 'view') {
            $album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $album_mame = GalleryAlbum::where('suppliers_id', 0)->where('album_id', $album_id)->value('album_mame');
            $album_mame = $album_mame ?? '';

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', sprintf($GLOBALS['_LANG']['view_pic'], stripslashes($album_mame)));
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['upload_image'], 'spec' => "ectype='addpic_album'"]);
            $this->smarty->assign('album_id', $album_id);
            $cat_select = gallery_cat_list(0, 0, false, 0, true, $adminru['ru_id']);


            /* 简单处理缩进 */
            foreach ($cat_select as $k => $v) {
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cat_select[$k]['name'] = $level . $v['name'];
                }
            }
            $this->smarty->assign('cat_select', $cat_select);

            $album_list = $this->galleryAlbumManageService->getPicAlbumList($album_id);

            $this->smarty->assign('pic_album', $album_list['pic_list']);
            $this->smarty->assign('filter', $album_list['filter']);
            $this->smarty->assign('record_count', $album_list['record_count']);
            $this->smarty->assign('page_count', $album_list['page_count']);
            $this->smarty->assign('full_page', 1);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($album_list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            return $this->smarty->display("pic_album.dwt");
        } elseif ($act == 'pic_query') {
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
            $album_id = isset($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
            $album_list = $this->galleryAlbumManageService->getPicAlbumList($album_id);
            $this->smarty->assign('pic_album', $album_list['pic_list']);
            $this->smarty->assign('filter', $album_list['filter']);
            $this->smarty->assign('record_count', $album_list['record_count']);
            $this->smarty->assign('page_count', $album_list['page_count']);
            //分页
            $page_count_arr = seller_page($album_list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            //跳转页面
            return make_json_result($this->smarty->fetch('pic_album.dwt'), '', ['filter' => $album_list['filter'], 'page_count' => $album_list['page_count']]);
        } /*删除*/
        elseif ($act == 'remove') {
            load_helper('visual');
            $album_id = intval($_GET['id']);

            //获取下级相册数量
            $album_count = GalleryAlbum::where('parent_album_id', $album_id)->count();
            //存在下级相册 不让删除
            if ($album_count > 0) {
                return make_json_error($GLOBALS['_LANG']['no_last_album_no_remove']);
            } else {

                /* 删除原来的文件 */
                $old_url = get_goods_gallery_album(0, $album_id, ['album_cover']);
                if ($old_url != '' && @strpos($old_url, 'http://') === false && @strpos($old_url, 'https://') === false) {
                    @unlink(storage_public($old_url));
                    $del_arr_img[] = $old_url;

                    $this->dscRepository->getOssDelFile($del_arr_img);
                }
                //删除该相册目录下的所以图片
                $dir = storage_public('data/gallery_album/' . $album_id);//模板目录
                $rmdir = getDelDirAndFile($dir);
                //删除图片数据库
                PicAlbum::where('album_id', $album_id)->delete();
                GalleryAlbum::where('album_id', $album_id)->delete();

                $url = 'gallery_album.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            }
        } /*删除图片*/
        elseif ($act == 'pic_remove') {
            $result = ['error' => '', 'content' => '', 'url' => ''];
            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            /* 删除原来的文件 */

            $pic_info = gallery_pic_album(2, $id, ['pic_file', 'pic_thumb', 'pic_image', 'album_id']);

            $arr_img = [];
            /* 删除原图 */
            if ($pic_info['pic_file'] != '' && @strpos($pic_info['pic_file'], 'http://') === false && @strpos($pic_info['pic_file'], 'https://') === false) {
                $arr_img[] = $pic_info['pic_file'];
            }

            /*删除缩略图*/
            if ($pic_info['pic_thumb'] != '' && @strpos($pic_info['pic_thumb'], 'http://') === false && @strpos($pic_info['pic_thumb'], 'https://') === false) {
                $arr_img[] = $pic_info['pic_thumb'];
            }

            /*删除图*/
            if ($pic_info['pic_image'] != '' && @strpos($pic_info['pic_image'], 'http://') === false && @strpos($pic_info['pic_image'], 'https://') === false) {
                $arr_img[] = $pic_info['pic_image'];
            }

            /* 删除OSS图片 */
            $this->dscRepository->getOssDelFile($arr_img);

            /* 删除本地图片 */
            if ($arr_img) {
                foreach ($arr_img as $key => $val) {
                    $arr_img[$key] = storage_public($val);
                }

                dsc_unlink($arr_img);
            }

            if (PicAlbum::where('pic_id', $id)->delete()) {
                $result['error'] = 0;
                $result['id'] = $id;
            } else {
                $result['error'] = 1;
                $result['content'] = $GLOBALS['_LANG']['system_error_retry'];
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 上传图片
        /*------------------------------------------------------ */
        elseif ($act == 'upload_pic') {
            $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);
            load_helper('goods', 'seller');

            $result = ['error' => 0, 'pic' => '', 'name' => ''];

            $album_id = isset($_REQUEST['album_id']) && !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;

            if (empty($album_id)) {
                return sys_msg($GLOBALS['_LANG']['upload_format_error']);
            }

            $bucket_info = $this->dscRepository->getBucketInfo();

            $extname = '';
            if (isset($_FILES['file']['name']) && $_FILES['file']['name']) {
                $extname = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
            }

            if ($extname == 'mp4') {
                $video_path = storage_public(DATA_DIR . '/uploads/pic_album/' . $album_id . "/");

                if (!file_exists($video_path)) {
                    make_dir($video_path);
                }

                $videoOther = [
                    'isRandName' => true,
                    'allowType' => ['mp4'],
                    'FilePath' => $video_path,
                    'MAXSIZE' => 20000000
                ];

                $upload = new FileUpload($videoOther);

                $pic_id = 0;
                if ($upload->uploadFile('file')) {

                    $video = DATA_DIR . "/uploads/pic_album/" . $album_id . "/" . $upload->getNewFileName();

                    $image_name = explode('.', $_FILES["file"]["name"]);
                    $pic_name = $image_name['0']; //文件名称
                    $pic_size = intval($_FILES['file']['size']); //视频大小
                    $add_time = TimeRepository::getGmTime(); //上传时间

                    //入库
                    $other = [
                        'ru_id' => $adminru['ru_id'],
                        'album_id' => $album_id,
                        'pic_name' => $pic_name,
                        'pic_file' => $video,
                        'pic_size' => $pic_size,
                        'add_time' => $add_time
                    ];
                    $pic_id = PicAlbum::insertGetId($other);

                    $arr_img = [$video];
                    $this->dscRepository->getOssAddFile($arr_img);

                    $result['picid'] = $pic_id;
                } else {
                    $result['error'] = 1;
                    $result['massege'] = $upload->getErrorMsg();
                }

                if ($pic_id) {

                    /* 删除本地视频 start */
                    if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
                        dsc_unlink(storage_public($video));
                    }
                    /* 删除本地视频 start */
                }
            } else {
                $path_images = storage_public(DATA_DIR . "/gallery_album/" . $album_id . '/images/');
                $path_original_img = storage_public(DATA_DIR . "/gallery_album/" . $album_id . '/original_img/');
                $path_thumb_img = storage_public(DATA_DIR . "/gallery_album/" . $album_id . '/thumb_img/');

                if (!file_exists($path_images)) {
                    make_dir($path_images);
                }

                if (!file_exists($path_original_img)) {
                    make_dir($path_original_img);
                }

                if (!file_exists($path_thumb_img)) {
                    make_dir($path_thumb_img);
                }

                $goods_thumb = '';  // 初始化商品缩略图
                $original_img = '';  // 初始化原始图片
                $old_original_img = '';  // 初始化原始图片旧图

                /* 取得文件地址 */
                $file_url = '';
                $pic_name = '';
                $pic_size = 0;
                $proc_thumb = (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) ? false : true;
                if ((isset($_FILES['file']['error']) && $_FILES['file']['error'] == 0) || (!isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none')) {

                    // 检查文件格式
                    if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types)) {
                        return sys_msg($GLOBALS['_LANG']['invalid_file']);
                    }
                    $image_name = explode('.', $_FILES["file"]["name"]);
                    $pic_name = $image_name['0']; //文件名称
                    $pic_size = intval($_FILES['file']['size']); //图片大小
                    $dir = DATA_DIR . "/gallery_album/" . $album_id . "/original_img";
                    $original_img = $image->upload_image($_FILES['file'], $dir); // 原始图片
                    $original_img = storage_public($original_img);
                    $images = $original_img;   // 商品图片
                    if ($proc_thumb && $image->gd_version() > 0 && $image->check_img_function($_FILES['file']['type'])) {
                        if ($GLOBALS['_CFG']['thumb_width'] != 0 || $GLOBALS['_CFG']['thumb_height'] != 0) {
                            $goods_thumb = $image->make_thumb($original_img, $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
                            if ($goods_thumb === false) {
                                return sys_msg($image->error_msg(), 1, [], false);
                            }
                        } else {
                            $goods_thumb = $original_img;
                        }
                        // 如果设置大小不为0，缩放图片
                        if ($GLOBALS['_CFG']['image_width'] != 0 || $GLOBALS['_CFG']['image_height'] != 0) {
                            $images = $image->make_thumb($original_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
                        } else {
                            $images = $original_img;
                        }
                        if (intval($GLOBALS['_CFG']['watermark_place']) > 0 && !empty($GLOBALS['_CFG']['watermark'])) {
                            if ($image->add_watermark($images, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false) {
                                return sys_msg($image->error_msg(), 1, [], false);
                            }
                        }
                    }

                    // 复制文件
                    list($width, $height, $type, $attr) = getimagesize($original_img); //获取规格
                    $pic_spec = $width . 'x' . $height; //图片规格
                    $add_time = TimeRepository::getGmTime(); //上传时间

                    $path_album = DATA_DIR . "/gallery_album/" . $album_id;

                    $images = $this->goodsManageService->reformatImageName('gallery', $album_id, $images, 'source', $path_album, 'album');
                    $original_img = $this->goodsManageService->reformatImageName('gallery', $album_id, $original_img, 'goods', $path_album, 'album');
                    $goods_thumb = $this->goodsManageService->reformatImageName('goods_thumb', $album_id, $goods_thumb, 'thumb', $path_album, 'album');

                    $result['data'] = [
                        'original_img' => $original_img,
                        'goods_thumb' => $goods_thumb
                    ];

                    $result['pic'] = $this->dscRepository->getImagePath($original_img);

                    //入库
                    $other = [
                        'ru_id' => $adminru['ru_id'],
                        'album_id' => $album_id,
                        'pic_name' => $pic_name,
                        'pic_file' => $original_img,
                        'pic_size' => $pic_size,
                        'pic_spec' => $pic_spec,
                        'add_time' => $add_time,
                        'pic_thumb' => $goods_thumb,
                        'pic_image' => $images
                    ];

                    $pic_id = PicAlbum::insertGetId($other);
                    if ($pic_id > 0) {
                        $arr_img = [
                            $original_img,
                            $goods_thumb,
                            $images
                        ];
                        $this->dscRepository->getOssAddFile($arr_img);
                    }

                    $result['picid'] = $pic_id ?? 0;
                } else {
                    $result['error'] = 1;
                    $result['massege'] = $GLOBALS['_LANG']['upload_format_error'];
                }
            }

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 上传视频
        /* ------------------------------------------------------ */
        elseif ($act == 'goods_video') {
            $result = ['error' => 0, 'goods_id' => 0, 'massege' => '', 'goods_video' => '', 'goods_video_path' => ''];

            $goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

            $result['goods_id'] = $goods_id;

            $bucket_info = $this->dscRepository->getBucketInfo();

            if ($_FILES['file']['name']) {
                $extname = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
            }

            $video_path = storage_public(DATA_DIR . '/uploads/goods/' . $goods_id . "/");

            if (!file_exists($video_path)) {
                make_dir($video_path);
            }

            if (isset($extname) && $extname == 'mp4') {
                $videoOther = [
                    'isRandName' => true,
                    'allowType' => ['mp4'],
                    'FilePath' => $video_path,
                    'MAXSIZE' => 20000000
                ];

                $upload = new FileUpload($videoOther);

                if ($upload->uploadFile('file')) {

                    $goods_video = DATA_DIR . "/uploads/goods/" . $goods_id . "/" . $upload->getNewFileName();

                    $goodsOther = [$goods_video];
                    $this->dscRepository->getOssAddFile($goodsOther);

                    if ($goods_id) {
                        $video_old = Goods::where('goods_id', $goods_id)->value('goods_video');
                        $video_old = $video_old ?? '';
                        dsc_unlink($video_old);

                        $arr[] = $video_old;
                        $this->dscRepository->getOssDelFile($arr);

                        Goods::where('goods_id', $goods_id)->update(['goods_video' => $goods_video]);
                    } else {
                        $admin_id = get_admin_id();
                        $video[] = $goods_video;
                        session()->put("goods_video_" . $goods_id . "_" . $admin_id, $video);

                        $list = session("goods_video_id_list", []);
                        $list = BaseRepository::getArrayUnique($list);

                        $list = BaseRepository::getArrayPush($list, $goods_id);
                        session()->put("goods_video_id_list", $list);
                    }

                    $result['goods_video'] = $goods_video;
                    $result['goods_video_path'] = !empty($goods_video) ? $this->dscRepository->getImagePath($goods_video) : '';
                } else {
                    $result['error'] = 1;
                    $result['massege'] = $upload->getErrorMsg();
                }

                if ($goods_id) {
                    /* 删除本地视频 start */
                    if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
                        dsc_unlink($goods_video);
                    }
                    /* 删除本地视频 start */
                }
            } else {
                $result['error'] = 2;
                $result['massege'] = $GLOBALS['_LANG']['upload_format_error'];
            }

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 删除视频
        /* ------------------------------------------------------ */
        elseif ($act == 'del_video') {
            $result = ['error' => 0, 'goods_id' => 0, 'massege' => '', 'goods_video' => ''];

            $goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

            if ($goods_id) {
                $sql = "SELECT goods_video FROM" . $GLOBALS['dsc']->table('goods') . " WHERE goods_id = '$goods_id'";
                $video_old = $GLOBALS['db']->getOne($sql, true);

                dsc_unlink(storage_public($video_old));
                $arr[] = $video_old;
                $this->dscRepository->getOssDelFile($arr);

                Goods::where('goods_id', $goods_id)->update(['goods_video' => '']);
            } else {
                get_del_goods_video();
            }

            return response()->json($result);
        } /*图片批量操作*/
        elseif ($act == 'batch') {
            $checkboxes = !empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : [];
            $old_album_id = isset($_REQUEST['old_album_id']) ? intval($_REQUEST['old_album_id']) : 0;
            $album_id = isset($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
            $type = isset($_REQUEST['type']) ? addslashes($_REQUEST['type']) : '';

            if (!empty($checkboxes)) {
                $pic_arr = BaseRepository::getExplode($checkboxes);
                if ($type == 'remove') {
                    /* 获取所以图片 */
                    $pic_info = PicAlbum::select('pic_file', 'pic_thumb', 'pic_image')->where('ru_id', $adminru['ru_id'])->whereIn('pic_id', $pic_arr);
                    $pic_info = BaseRepository::getToArrayGet($pic_info);
                    /* 存在图片  删除 */
                    if (!empty($pic_info)) {
                        foreach ($pic_info as $v) {
                            if ($v['pic_file'] != '' && @strpos($v['pic_file'], 'http://') === false && @strpos($v['pic_file'], 'https://') === false) {
                                dsc_unlink(storage_public($v['pic_file']));
                                $arr_img[] = $v['pic_file'];
                            }

                            /* 删除缩略图 */
                            if ($v['pic_thumb'] != '' && @strpos($v['pic_thumb'], 'http://') === false && @strpos($v['pic_thumb'], 'https://') === false) {
                                dsc_unlink(storage_public($v['pic_thumb']));
                                $arr_img[] = $v['pic_thumb'];
                            }

                            /* 删除缩略图 */
                            if ($v['pic_image'] != '' && @strpos($v['pic_image'], 'http://') === false && @strpos($v['pic_image'], 'https://') === false) {
                                dsc_unlink(storage_public($v['pic_image']));
                                $arr_img[] = $v['pic_image'];
                            }

                            $this->dscRepository->getOssDelFile($arr_img);
                        }
                    }
                    /* 删除活动 */
                    if (PicAlbum::where('ru_id', $adminru['ru_id'])->whereIn('pic_id', $pic_arr)->delete()) {
                        $link[] = ['text' => $GLOBALS['_LANG']['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id];
                        return sys_msg($GLOBALS['_LANG']['delete_succeed'], 0, $link);
                    }
                } else {
                    /* 转移相册 */
                    if ($album_id > 0) {
                        $update = PicAlbum::where('ru_id', $adminru['ru_id'])->whereIn('pic_id', $pic_arr)->update(['album_id' => $album_id]);
                        if ($update > 0) {
                            $link[] = ['text' => $GLOBALS['_LANG']['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id];
                            return sys_msg($GLOBALS['_LANG']['remove_succeed'], 0, $link);
                        }
                    } else {
                        $link[] = ['text' => $GLOBALS['_LANG']['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id];
                        return sys_msg($GLOBALS['_LANG']['album_fail'], 1, $link);
                    }
                }
            } else {
                $link[] = ['text' => $GLOBALS['_LANG']['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id];
                return sys_msg($GLOBALS['_LANG']['handle_fail'], 1, $link);
            }
        } //转移相册弹窗
        elseif ($act == 'move_pic') {
            $album_id = !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
            $inherit = !empty($_REQUEST['inherit']) ? intval($_REQUEST['inherit']) : 0;
            $cat_select = gallery_cat_list(0, 0, false, 0, true, $adminru['ru_id']);

            /* 简单处理缩进 */
            foreach ($cat_select as $k => $v) {
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cat_select[$k]['name'] = $level . $v['name'];
                }
            }
            $this->smarty->assign('cat_select', $cat_select);

            $this->smarty->assign('form_act', 'submit_pic');
            $this->smarty->assign('action_type', 'move_pic');
            $this->smarty->assign('album_id', $album_id);
            $this->smarty->assign('inherit', $inherit);
            $html = $this->smarty->fetch("category_move.dwt");

            clear_cache_files();
            return make_json_result($html);
        } //转移相册操作
        elseif ($act == 'submit_pic') {
            $album_id = !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;//操作相册
            $inherit = !empty($_REQUEST['inherit']) ? intval($_REQUEST['inherit']) : 0;//子相册是否继承
            $target_album_id = !empty($_REQUEST['target_album_id']) ? intval($_REQUEST['target_album_id']) : 0;//目标相册
            $cat_select = $album_id;
            if ($inherit == 1) {
                $cat_select = $this->getgallery_child($album_id, 1);
            }

            $cat_select = BaseRepository::getExplode($cat_select);
            $res = PicAlbum::where('ru_id', $adminru['ru_id'])->whereIn('album_id', $cat_select)->update(['album_id' => $target_album_id]);

            $parent_album_id = GalleryAlbum::where('album_id', $album_id)->where('ru_id', $adminru['ru_id'])->value('parent_album_id');
            $parent_album_id = $parent_album_id ?? 0;
            $link[] = ['text' => $GLOBALS['_LANG']['bank_list'], 'href' => 'gallery_album.php?act=list&parent_id=' . $parent_album_id];
            return sys_msg($GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }
    }

    private function getgallery_child($album_id = 0, $type = 0)
    {
        $adminru = get_admin_ru_id();
        $child_arr = '';
        if ($album_id > 0) {
            if ($type == 1) {
                $child_arr = $album_id;
            }
            $child_list = GalleryAlbum::select('album_id')->where('parent_album_id', $album_id)->where('ru_id', $adminru['ru_id']);
            $child_list = BaseRepository::getToArrayGet($child_list);

            if (!empty($child_list)) {
                foreach ($child_list as $k => $v) {
                    $child_arr .= "," . $v['album_id'];
                    $child_tree = $this->getgallery_child($v['album_id']);
                    if ($child_tree) {
                        $child_arr .= "," . $child_tree;
                    }
                }
            }
        }
        $child_arr = $this->dscRepository->delStrComma($child_arr);
        return $child_arr;
    }

    /*获取相册列表*/
    private function getGalleryAlbumList($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGalleryAlbumList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 筛选信息 */
        $filter['album_mame'] = empty($_REQUEST['album_mame']) ? '' : trim($_REQUEST['album_mame']);
        $filter['parent_id'] = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
        $filter['keywords'] = isset($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';
        $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;

        /* 拼装筛选 */
        $row = GalleryAlbum::query();
        $row = $row->where('suppliers_id', 0)->where('parent_album_id', $filter['parent_id']);
        if ($filter['album_mame']) {
            $row = $row->where('album_mame', 'like', '%' . mysql_like_quote($filter['album_mame']) . '%');
        }
        if ($filter['ru_id'] > 0) {
            $row->where('ru_id', $filter['ru_id']);
        }

        //管理员查询的权限 -- 店铺查询 end
        $filter['record_count'] = $row->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获活动数据 */
        $row = $row->select('album_id', 'ru_id', 'album_mame', 'album_cover', 'album_desc', 'sort_order')->orderBy('sort_order', 'ASC')->offset($filter['start'])->limit($filter['page_size']);

        $row = BaseRepository::getToArrayGet($row);

        $filter['keywords'] = stripslashes($filter['keywords']);

        foreach ($row as $k => $v) {
            if ($v['ru_id'] > 0) {
                $row[$k]['shop_name'] = $this->merchantCommonService->getShopName($v['ru_id'], 1);
            } else {
                $row[$k]['shop_name'] = $GLOBALS['_LANG']['self'];
            }

            $row[$k]['album_cover'] = $this->dscRepository->getImagePath($v['album_cover']);
            $row[$k]['gallery_count'] = PicAlbum::where('album_id', $v['album_id'])->count();
        }
        $arr = ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }

    /* 上传文件 */
    private function upload_article_file($upload, $file = '')
    {
        $file_dir = storage_public(DATA_DIR . "/gallery_album/");
        if (!file_exists($file_dir)) {
            if (!make_dir($file_dir)) {
                /* 创建目录失败 */
                return false;
            }
        }

        $filename = Image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
        $dir = DATA_DIR . "/gallery_album/" . $filename;
        $path = storage_public($dir);

        //组合路径，并删除storage前的路径
        if (move_upload_file($upload['tmp_name'], $path)) {
            return $dir;
        } else {
            return false;
        }
    }
}
