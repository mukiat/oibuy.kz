<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\Brand;
use App\Models\Goods;
use App\Models\GoodsGallery;
use App\Models\GoodsLib;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Goods\GoodsManageService;

/**
 * 商品库商品批量上传、修改
 */
class GoodsLibBatchController extends InitController
{
    protected $dscRepository;
    protected $goodsManageService;

    public function __construct(
        DscRepository $dscRepository,
        GoodsManageService $goodsManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->goodsManageService = $goodsManageService;
    }

    public function index()
    {
        load_helper('goods', 'admin');
        $act = request()->get('act', '');

        /*------------------------------------------------------ */
        //-- 批量上传
        /*------------------------------------------------------ */

        if ($act == 'add') {
            /* 检查权限 */
            admin_priv('goods_lib_list');

            $lang_list = [
                'UTF8' => __('admin::common.charset.utf8'),
                'GB2312' => __('admin::common.charset.zh_cn'),
                'BIG5' => __('admin::common.charset.zh_tw'),
            ];

            /* 取得可选语言 */
            $download_list = $this->dscRepository->getDdownloadTemplate(resource_path('lang'));

            $data_format_array = [
                'dscmall' => __('admin::goods_lib_batch.export_dscmall'),
            ];
            $this->smarty->assign('data_format', $data_format_array);
            $this->smarty->assign('lang_list', $lang_list);
            $this->smarty->assign('download_list', $download_list);
            $goods_id = 0;
            set_default_filter($goods_id, 0, 0, 0, 'goods_lib_cat'); //设置默认筛选

            /* 参数赋值 */
            $ur_here = __('admin::goods_lib_batch.goods_lib_batch_add');
            $this->smarty->assign('ur_here', $ur_here);
            $this->smarty->assign('action_link', ['href' => 'goods_lib.php?act=list', 'text' => __('admin::common.01_goods_list')]);

            /* 显示模板 */

            return $this->smarty->display('goods_lib_batch_add.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量上传：处理
        /*------------------------------------------------------ */

        elseif ($act == 'upload') {
            /* 检查权限 */
            admin_priv('goods_lib_list');

            /* 将文件按行读入数组，逐行进行解析 */
            $line_number = 0;
            $arr = [];
            $goods_list = [];
            $field_list = array_keys(__('admin::goods_lib_batch.upload_goods_lib')); // 字段列表
            $file = request()->file('file');
            $data = file($file->getRealPath());
            $data_cat = request()->get('data_cat', '');
            $charset = request()->get('charset', '');
            if ($data_cat == 'dscmall') {
                foreach ($data as $line) {
                    // 跳过第一行
                    if ($line_number == 0) {
                        $line_number++;
                        continue;
                    }

                    // 转换编码
                    if (($charset != 'UTF8') && (strpos(strtolower(EC_CHARSET), 'utf') === 0)) {
                        $line = dsc_iconv($charset, 'UTF8', $line);
                    }

                    // 初始化
                    $arr = [];
                    $buff = '';
                    $quote = 0;
                    $len = strlen($line);


                    for ($i = 0; $i < $len; $i++) {
                        $char = $line[$i];


                        if ('\\' == $char) {
                            $i++;
                            $char = $line[$i];

                            switch ($char) {
                                case '"':
                                    $buff .= '"';
                                    break;
                                case '\'':
                                    $buff .= '\'';
                                    break;
                                case ',':
                                    $buff .= ',';
                                    break;
                                default:
                                    $buff .= '\\' . $char;
                                    break;
                            }
                        } elseif ('"' == $char) {
                            if (0 == $quote) {
                                $quote++;
                            } else {
                                $quote = 0;
                            }
                        } elseif (',' == $char) {
                            if (0 == $quote) {
                                if (!isset($field_list[count($arr)])) {
                                    continue;
                                }
                                $field_name = $field_list[count($arr)];
                                $arr[$field_name] = trim($buff);
                                $buff = '';
                                $quote = 0;
                            } else {
                                $buff .= $char;
                            }
                        } else {
                            $buff .= $char;
                        }

                        if ($i == $len - 1) {
                            if (!isset($field_list[count($arr)])) {
                                continue;
                            }
                            $field_name = $field_list[count($arr)];
                            $arr[$field_name] = trim($buff);
                        }
                    }
                    $goods_list[] = $arr;
                }
            }
            session(['goods_list' => $goods_list]);

            $this->smarty->assign('goods_class', __('admin::goods_lib_batch.g_class'));
            //$this->smarty->assign('goods_list', $goods_list);
            $this->smarty->assign('page', 1);


            // 字段名称列表
            $this->smarty->assign('title_list', __('admin::goods_lib_batch.upload_goods_lib'));

            // 显示的字段列表
            $this->smarty->assign('field_show', ['goods_name' => true, 'goods_sn' => true, 'brand_name' => true, 'market_price' => true, 'shop_price' => true]);

            /* 参数赋值 */
            $this->smarty->assign('ur_here', __('admin::goods_lib_batch.goods_upload_confirm'));

            /* 显示模板 */

            return $this->smarty->display('goods_lib_batch_confirm.dwt');
        } /*异步处理上传*/
        elseif ($act == 'creat') {
            $result = ['list' => [], 'is_stop' => 0];
            $page = request()->get('page', 1);
            $page_size = request()->get('page_size', 1);
            if (session()->has('goods_list')) {
                $goods_list = session('goods_list');
            } else {
                $goods_list = [];
            }
            if ($goods_list) {
                $goods_list = $this->dsc->page_array($page_size, $page, $goods_list);

                $result['list'] = isset($goods_list['list']) && $goods_list['list'] ? $goods_list['list'][0] : [];

                if (isset($result['list']['goods_brief']) && $result['list']['goods_brief']) {
                    $result['list']['goods_brief'] = strip_tags($result['list']['goods_brief']);
                    $result['list']['goods_brief'] = htmlspecialchars_decode($result['list']['goods_brief']);
                    $result['list']['goods_brief'] = str_replace('"', "'", $result['list']['goods_brief']);
                }

                if (isset($result['list']['goods_desc']) && $result['list']['goods_desc']) {
                    $result['list']['goods_desc'] = strip_tags($result['list']['goods_desc']);
                    $result['list']['goods_desc'] = htmlspecialchars_decode($result['list']['goods_desc']);
                    $result['list']['goods_desc'] = str_replace('"', "'", $result['list']['goods_desc']);
                }

                $result['page'] = $goods_list['filter']['page'] + 1;
                $result['page_size'] = $goods_list['filter']['page_size'];
                $result['record_count'] = $goods_list['filter']['record_count'];
                $result['page_count'] = $goods_list['filter']['page_count'];

                $result['is_stop'] = 1;
                if ($page >= $goods_list['filter']['page_count']) {
                    $result['is_stop'] = 0;
                }
                $result['filter_page'] = $goods_list['filter']['page'] - 1;
            }


            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 批量上传：�        �库
        /*------------------------------------------------------ */

        elseif ($act == 'insert') {
            /* 检查权限 */
            admin_priv('goods_lib_list');
            $checked = request()->get('checked', []);
            if ($checked) {
                $image = new Image([config('shop.bgcolor')]);

                /* 字段默认值 */
                $default_value = [
                    'brand_id' => 0,
                    'goods_weight' => 0,
                    'market_price' => 0,
                    'shop_price' => 0,
                    'is_real' => 1,
                    'is_on_sale' => 1,
                    'goods_type' => 0,
                ];

                /* 查询品牌列表 */
                $brand_list = [];
                $res = Brand::select('brand_id', 'brand_name');
                $res = BaseRepository::getToArrayGet($res);
                foreach ($res as $row) {
                    $brand_list[$row['brand_name']] = $row['brand_id'];
                }

                /* 字段列表 */
                $field_list = array_keys(__('admin::goods_lib_batch.upload_goods_lib'));
                $field_list[] = 'goods_class'; //实体或虚拟商品

                /* 获取商品good id */
                $max_id = Goods::max('goods_id');
                $max_id = $max_id ? $max_id + 1 : 1;
                /* 循环插入商品数据 */

                foreach ($checked as $key => $value) {
                    // 合并
                    $field_arr = [
                        'lib_cat_id' => request()->get('cat', 0),
                        'add_time' => gmtime(),
                        'last_update' => gmtime(),
                    ];
                    foreach ($field_list as $field) {
                        // 转换编码
                        $field_value = request()->get($field, []);
                        $field_value = $field_value[$value] ?? '';

                        /* 虚拟商品处理 */
                        if ($field == 'goods_class') {
                            $field_value = intval($field_value);
                            if ($field_value == G_CARD) {
                                $field_arr['extension_code'] = 'virtual_card';
                            }
                            continue;
                        }

                        // 如果字段值为空，且有默认值，取默认值
                        $field_arr[$field] = !isset($field_value) && isset($default_value[$field]) ? $default_value[$field] : $field_value;

                        // 特殊处理
                        if (!empty($field_value)) {

                            // 图片路径
                            if (in_array($field, ['original_img', 'goods_img', 'goods_thumb'])) {
                                if (strpos($field_value, '|;') > 0) {
                                    $field_value = explode(':', $field_value);
                                    $field_value = $field_value['0'];
                                    @copy(storage_public('images/' . $field_value . '.tbi'), storage_public('images/' . $field_value . '.jpg'));
                                    if (is_file(storage_public('images/' . $field_value . '.jpg'))) {
                                        $field_arr[$field] = IMAGE_DIR . '/' . $field_value . '.jpg';
                                    }
                                } else {
                                    $field_arr[$field] = IMAGE_DIR . '/' . $field_value;
                                }
                            } // 品牌
                            elseif ($field == 'brand_name') {
                                if (isset($brand_list[$field_value])) {
                                    $field_arr['brand_id'] = $brand_list[$field_value];
                                } else {
                                    $data = [
                                        'brand_name' => addslashes($field_value)
                                    ];
                                    $brand_id = Brand::insertGetId($data);

                                    $brand_list[$field_value] = $brand_id;
                                    $field_arr['brand_id'] = $brand_id;
                                }
                            } // 数值型
                            elseif (in_array($field, ['goods_weight', 'market_price', 'shop_price'])) {
                                $field_arr[$field] = floatval($field_value);
                            } // bool型
                            elseif (in_array($field, ['is_on_sale', 'is_real'])) {
                                $field_arr[$field] = intval($field_value) > 0 ? 1 : 0;
                            }
                        }

                        if ($field == 'is_real') {
                            $goods_class = request()->get('goods_class');
                            $field_arr[$field] = intval($goods_class[$key]);
                        }
                    }

                    if (empty($field_arr['goods_sn'])) {
                        $field_arr['goods_sn'] = $this->goodsManageService->generateGoodSn($max_id);
                    }

                    if ($field_arr && $field_arr['goods_name']) {
                        $field_arrs = [
                            'lib_cat_id' => $field_arr['lib_cat_id'],
                            'add_time' => $field_arr['add_time'],
                            'last_update' => $field_arr['last_update'],
                            'goods_name' => $field_arr['goods_name'],
                            'goods_sn' => $field_arr['goods_sn'],
                            'brand_id' => $field_arr['brand_id'] ?? 0,
                            'market_price' => $field_arr['market_price'],
                            'shop_price' => $field_arr['shop_price'],
                            'original_img' => $field_arr['original_img'],
                            'goods_img' => $field_arr['goods_img'],
                            'goods_thumb' => $field_arr['goods_thumb'],
                            'keywords' => $field_arr['keywords'],
                            'goods_brief' => $field_arr['goods_brief'],
                            'goods_desc' => $field_arr['goods_desc'],
                            'goods_weight' => $field_arr['goods_weight'],
                            'is_on_sale' => $field_arr['is_on_sale'],
                            'is_real' => 1,
                            'extension_code' => $field_arr['extension_code'] ?? 0,
                        ];
                        $goods_lib_id = GoodsLib::insertGetId($field_arrs);

                        $max_id = $goods_lib_id + 1;

                        /* 如果图片不为空,修改商品图片，插入商品相册*/
                        if (!empty($field_arr['original_img']) || !empty($field_arr['goods_img']) || !empty($field_arr['goods_thumb'])) {
                            $goods_img = '';
                            $goods_thumb = '';
                            $original_img = '';
                            $goods_gallery = [];
                            $goods_gallery['goods_id'] = $goods_lib_id;

                            if (!empty($field_arr['original_img'])) {
                                //设置商品相册原图和商品相册图
                                if (config('shop.auto_generate_gallery')) {
                                    $ext = substr($field_arr['original_img'], strrpos($field_arr['original_img'], '.'));
                                    $img = dirname($field_arr['original_img']) . '/' . $image->random_filename() . $ext;
                                    $gallery_img = dirname($field_arr['original_img']) . '/' . $image->random_filename() . $ext;
                                    @copy(storage_public($field_arr['original_img']), storage_public($img));
                                    @copy(storage_public($field_arr['original_img']), storage_public($gallery_img));
                                    $goods_gallery['img_original'] = $this->goodsManageService->reformatImageName('gallery', $goods_gallery['goods_id'], $img, 'source');
                                }
                                //设置商品原图
                                if (config('shop.retain_original_img')) {
                                    $original_img = $this->goodsManageService->reformatImageName('goods', $goods_gallery['goods_id'], $field_arr['original_img'], 'source');
                                } else {
                                    @unlink(storage_public($field_arr['original_img']));
                                }
                            }

                            if (!empty($field_arr['goods_img'])) {
                                //设置商品相册图
                                if (config('shop.auto_generate_gallery') && !empty($gallery_img)) {
                                    $goods_gallery['img_url'] = $this->goodsManageService->reformatImageName('gallery', $goods_gallery['goods_id'], $gallery_img, 'goods');
                                }
                                //设置商品图
                                $goods_img = $this->goodsManageService->reformatImageName('goods', $goods_gallery['goods_id'], $field_arr['goods_img'], 'goods');
                            }

                            if (!empty($field_arr['goods_thumb'])) {
                                //设置商品相册缩略图
                                if (config('shop.auto_generate_gallery')) {
                                    $ext = substr($field_arr['goods_thumb'], strrpos($field_arr['goods_thumb'], '.'));
                                    $gallery_thumb = dirname($field_arr['goods_thumb']) . '/' . $image->random_filename() . $ext;
                                    @copy(storage_public($field_arr['goods_thumb']), storage_public($gallery_thumb));
                                    $goods_gallery['thumb_url'] = $this->goodsManageService->reformatImageName('gallery_thumb', $goods_gallery['goods_id'], $gallery_thumb, 'thumb');
                                }
                                //设置商品缩略图
                                $goods_thumb = $this->goodsManageService->reformatImageName('goods_thumb', $goods_gallery['goods_id'], $field_arr['goods_thumb'], 'thumb');
                            }

                            //修改商品图
                            $data = [
                                'goods_img' => $goods_img,
                                'goods_thumb' => $goods_thumb,
                                'original_img' => $original_img
                            ];
                            Goods::where('goods_id', $goods_gallery['goods_id'])->update($data);
                            //添加商品相册图
                            if (config('shop.auto_generate_gallery')) {
                                GoodsGallery::insert($goods_gallery);
                            }
                        }
                    }
                }
            }

            // 记录日志
            admin_log('', 'batch_upload', 'goods');

            /* 显示提示信息，返回商品列表 */
            $link[] = ['href' => 'goods_lib.php?act=list', 'text' => __('admin::common.01_goods_list')];
            return sys_msg(__('admin::goods_lib_batch.batch_upload_ok'), 0, $link);
        }


        /*------------------------------------------------------ */
        //-- 下载文件
        /*------------------------------------------------------ */

        elseif ($act == 'download') {
            /* 检查权限 */
            admin_priv('goods_lib_list');
            $upload_goods_lib = __('admin::goods_lib_batch.upload_goods_lib');
            // 文件标签
            // Header("Content-type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=goods_list.csv");
            $charset = request()->get('charset', '');
            // 下载
            if ($charset != config('shop.lang')) {
                $lang_file = '../languages/' . $charset . '/admin/goods_batch.php';
                if (file_exists($lang_file)) {
                    unset($upload_goods_lib);
                    require($lang_file);
                }
            }

            if ($upload_goods_lib) {
                /* 创建字符集转换对象 */
                if ($charset == 'zh-CN' || $charset == 'zh-TW') {
                    $to_charset = $charset == 'zh-CN' ? 'GB2312' : 'BIG5';
                    echo dsc_iconv(EC_CHARSET, $to_charset, join(',', $upload_goods_lib));
                } else {
                    echo join(',', $upload_goods_lib);
                }
            } else {
                echo 'error: ' . $upload_goods_lib . ' not exists';
            }
        }

        /*------------------------------------------------------ */
        //-- 取得商品
        /*------------------------------------------------------ */

        elseif ($act == 'get_goods') {
            $filter = app(\StdClass::class);

            $filter->cat_id = request()->get('cat_id', 0);
            $filter->brand_id = request()->get('brand_id', 0);
            $filter->real_goods = -1;
            $arr = get_goods_list($filter);

            return make_json_result($arr);
        }
    }
}
