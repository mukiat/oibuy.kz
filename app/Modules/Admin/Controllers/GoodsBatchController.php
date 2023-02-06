<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\Brand;
use App\Models\Goods;
use App\Models\GoodsGallery;
use App\Models\MemberPrice;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\UserRank;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Goods\GoodsManageService;

/**
 * 商品批量上传、修改
 */
class GoodsBatchController extends InitController
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
            admin_priv('goods_batch');

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '13_batch_add']);

            $lang_list = [
                'UTF8' => __('admin::common.charset.utf8'),
                'GB2312' => __('admin::common.charset.zh_cn'),
                'BIG5' => __('admin::common.charset.zh_tw'),
            ];

            /* 取得可选语言 */
            $download_list = $this->dscRepository->getDdownloadTemplate(resource_path('lang'));

            $data_format_array = [
                'dscmall' => __('admin::goods_batch.export_dscmall'),
            ];
            $this->smarty->assign('data_format', $data_format_array);
            $this->smarty->assign('lang_list', $lang_list);
            $this->smarty->assign('download_list', $download_list);
            $goods_id = '';
            set_default_filter($goods_id); //设置默认筛选

            /* 参数赋值 */
            $ur_here = __('admin::common.13_batch_add');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_batch_add.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量上传：处理
        /*------------------------------------------------------ */

        elseif ($act == 'upload') {
            /* 检查权限 */
            admin_priv('goods_batch');

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '13_batch_add']);

            /* 将文件按行读入数组，逐行进行解析 */
            $line_number = 0;
            $goods_list = [];
            $field_list = array_keys(__('admin::goods_batch.upload_goods')); // 字段列表
            $file = request()->file('file');
            $data = file($file->getRealPath());
            $data_cat = request()->get('data_cat', '');
            $charset = 'GB2312';

            if (empty($data_cat)) {
                return sys_msg(trans('admin/common.please_select') . trans('admin::goods_batch.export_format'));
            }

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

                    if ($line) {
                        $line = explode(',', $line);
                        foreach ($line as $l => $v) {
                            $v = trim($v, "'");
                            $v = trim($v, '"');
                            $line[$l] = str_replace(["'", '"', '\/'], '', $v);
                        }
                        $line = implode(',', $line);
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
            $this->smarty->assign('page', 1);

            // 字段名称列表
            $this->smarty->assign('title_list', __('admin::goods_batch.upload_goods'));

            // 显示的字段列表
            $this->smarty->assign('field_show', ['goods_name' => true, 'goods_sn' => true, 'brand_name' => true, 'market_price' => true, 'shop_price' => true]);

            /* 参数赋值 */
            $this->smarty->assign('ur_here', __('admin::goods_batch.goods_upload_confirm'));
            /* 显示模板 */

            return $this->smarty->display('goods_batch_confirm.dwt');
        }

        /*------------------------------------------------------ */
        //-- 异步处理上传
        /*------------------------------------------------------ */
        elseif ($act == 'creat') {
            $result = ['list' => [], 'is_stop' => 0];
            $page = request()->get('page', 1);
            $page_size = request()->get('page_size', 1);

            $goods_list = session()->has('goods_list') ? session('goods_list') : [];

            if ($goods_list) {
                //response()->json和json_encode()都不支持gbk,会报转换异常错误
                $goods_list = $this->dscRepository->gbkToUtf8($goods_list);
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

                $goods = [
                    $result['list']
                ];
                $this->batchGoods($goods, 1);

                $result['filter_page'] = $goods_list['filter']['page'] - 1;
            }


            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 批量修改：选择商品
        /*------------------------------------------------------ */

        elseif ($act == 'select') {
            /* 检查权限 */
            admin_priv('goods_batch');

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '15_batch_edit']);
            $goods_id = '';
            set_default_filter($goods_id); //设置默认筛选

            /* 参数赋值 */
            $ur_here = __('admin::common.15_batch_edit');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_batch_select.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量修改：修改
        /*------------------------------------------------------ */

        elseif ($act == 'edit') {
            /* 检查权限 */
            admin_priv('goods_batch');
            $select_method = request()->get('select_method', '');
            $sn_list = request()->get('sn_list', '');

            /* 取得商品列表 */
            $goods_res = Goods::select(
                'goods_id',
                'goods_sn',
                'goods_name',
                'market_price',
                'shop_price',
                'cost_price',
                'goods_number',
                'integral',
                'give_integral',
                'brand_id',
                'is_real',
                'model_attr'
            );
            $mp_res = MemberPrice::select('goods_id', 'user_rank', 'user_price');
            if ($select_method == 'cat') {
                $goods_ids = BaseRepository::getExplode($_POST['goods_ids']);
                $goods_res = $goods_res->whereIn('goods_id', $goods_ids)->where('user_id', 0);
                $mp_res = $mp_res->whereIn('goods_id', $goods_ids);
            } else {
                $goods_sns = BaseRepository::getExplode($sn_list, "\r\n");
                $goods_sns = BaseRepository::getArrayUnique($goods_sns);

                $res = Goods::select('goods_id')->whereIn('goods_sn', $goods_sns)->where('user_id', 0);
                $result = BaseRepository::getToArrayGet($res);
                $result = BaseRepository::getFlatten($result);

                $goods_ids = '';
                if ($result) {
                    $goods_ids = join(',', $result);
                }

                $goods_ids = BaseRepository::getExplode($goods_ids);
                $goods_res = $goods_res->whereIn('goods_id', $goods_ids);
                $mp_res = $mp_res->whereIn('goods_id', $goods_ids);
            }
            $goods_list = BaseRepository::getToArrayGet($goods_res);

            /* 获取商品对应的品牌列表 by wu */
            foreach ($goods_list as $key => $val) {
                $goods_list[$key]['brand_list'] = get_brand_list($val['goods_id']);
                /* 取编辑商品的货品列表 */
                $res = Products::where('goods_id', $val['goods_id']);
                if ($val['model_attr'] == 1) {
                    $res = ProductsWarehouse::where('goods_id', $val['goods_id']);
                } elseif ($val['model_attr'] == 2) {
                    $res = ProductsArea::where('goods_id', $val['goods_id']);
                }
                $product_list = BaseRepository::getToArrayGet($res);

                if (!empty($product_list)) {
                    $_product_list = [];
                    foreach ($product_list as $value) {
                        $goods_attr = product_goods_attr_list($value['goods_id']);
                        $_goods_attr_array = explode('|', $value['goods_attr']);
                        $_temp = [];
                        if ($_goods_attr_array && is_array($_goods_attr_array)) {
                            foreach ($_goods_attr_array as $_goods_attr_value) {
                                if (isset($goods_attr[$_goods_attr_value])) {
                                    $_temp[] = $goods_attr[$_goods_attr_value];
                                }
                            }
                            $value['goods_attr'] = implode('，', $_temp);
                        }

                        $_product_list[] = $value;
                    }
                    //释放资源
                    $goods_list[$key]['product_list'] = $_product_list;
                }
            }

            $this->smarty->assign('goods_list', $goods_list);

            /* 取得会员价格 */
            $member_price_list = [];

            $res = BaseRepository::getToArrayGet($mp_res);

            foreach ($res as $row) {
                $member_price_list[$row['goods_id']][$row['user_rank']] = $row['user_price'];
            }
            $this->smarty->assign('member_price_list', $member_price_list);

            /* 取得会员等级 */

            $res = UserRank::orderBy('discount', 'DESC');
            $user_rangk_list = BaseRepository::getToArrayGet($res);

            $this->smarty->assign('rank_list', $user_rangk_list);

            /* 赋值编辑方式 */
            $this->smarty->assign('edit_method', $_POST['edit_method']);

            /* 参数赋值 */
            $ur_here = __('admin::common.15_batch_edit');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_batch_edit.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量修改：提交
        /*------------------------------------------------------ */

        elseif ($act == 'update') {
            /* 检查权限 */
            admin_priv('goods_batch');
            $warehouse_id = request()->get('warehouse_id', []);
            $area_id = request()->get('area_id', []);
            $edit_method = request()->get('edit_method', '');
            $goods_ids = request()->get('goods_id', []);
            $rank_ids = request()->get('rank_id', []);
            $member_price = request()->get('member_price', []);

            if ($edit_method == 'each') {
                // 循环更新每个商品
                $product_number = request()->get('product_number', []);
                $goods_number = request()->get('goods_number', []);
                $market_price = request()->get('market_price', []);
                $shop_price = request()->get('shop_price', []);
                $cost_price = request()->get('cost_price', []);
                $integral = request()->get('integral', []);
                $give_integral = request()->get('give_integral', []);
                $brand_id = request()->get('brand_id', []);
                $product_market_price = request()->get('product_market_price', []);
                $product_cost_price = request()->get('product_cost_price', []);
                $product_price = request()->get('product_price', []);

                if ($goods_ids) {
                    foreach ($goods_ids as $goods_id) {
                        $goodsInfo = Goods::select('goods_id', 'goods_name', 'model_attr')->where('goods_id', $goods_id);
                        $goodsInfo = BaseRepository::getToArrayFirst($goodsInfo);

                        $goods_name = $goodsInfo['goods_name'] ?? '';
                        $model_attr = $goodsInfo['model_attr'] ?? 0;

                        //如果存在货品则处理货品
                        if (!empty($product_number[$goods_id])) {
                            $goods_number[$goods_id] = 0;
                            foreach ($product_number[$goods_id] as $key => $value) {
                                $res = Products::whereRaw(1);
                                if ($model_attr == 1) {
                                    if (isset($warehouse_id[$key]) && !empty($warehouse_id[$key])) {
                                        $res = ProductsWarehouse::where('warehouse_id', $warehouse_id[$key]);
                                    } else {
                                        continue;
                                    }
                                } elseif ($model_attr == 2) {
                                    if (isset($area_id[$key]) && !empty($area_id[$key])) {
                                        $res = ProductsArea::where('area_id', $area_id[$key]);
                                    } else {
                                        continue;
                                    }
                                }

                                $data = ['product_number' => $value];
                                $res = $res->where('goods_id', $goods_id)->where('product_id', $key);
                                $res->update($data);

                                $goods_number[$goods_id] += $value;
                            }
                        }

                        if (!empty($product_market_price[$goods_id])) {
                            foreach ($product_market_price[$goods_id] as $key => $value) {
                                $res = Products::whereRaw(1);
                                if ($model_attr == 1) {
                                    if (isset($warehouse_id[$key]) && !empty($warehouse_id[$key])) {
                                        $res = ProductsWarehouse::where('warehouse_id', $warehouse_id[$key]);
                                    } else {
                                        continue;
                                    }
                                } elseif ($model_attr == 2) {
                                    if (isset($area_id[$key]) && !empty($area_id[$key])) {
                                        $res = ProductsArea::where('area_id', $area_id[$key]);
                                    } else {
                                        continue;
                                    }
                                }

                                $data = ['product_market_price' => $value];
                                $res = $res->where('goods_id', $goods_id)->where('product_id', $key);
                                $res->update($data);
                            }
                        }

                        if (!empty($product_cost_price[$goods_id])) {
                            foreach ($product_cost_price[$goods_id] as $key => $value) {
                                $res = Products::whereRaw(1);
                                if ($model_attr == 1) {
                                    if (isset($warehouse_id[$key]) && !empty($warehouse_id[$key])) {
                                        $res = ProductsWarehouse::where('warehouse_id', $warehouse_id[$key]);
                                    } else {
                                        continue;
                                    }
                                } elseif ($model_attr == 2) {
                                    if (isset($area_id[$key]) && !empty($area_id[$key])) {
                                        $res = ProductsArea::where('area_id', $area_id[$key]);
                                    } else {
                                        continue;
                                    }
                                }

                                $data = ['product_cost_price' => $value];
                                $res = $res->where('goods_id', $goods_id)->where('product_id', $key);
                                $res->update($data);
                            }
                        }

                        if (!empty($product_price[$goods_id])) {
                            foreach ($product_price[$goods_id] as $key => $value) {
                                $res = Products::whereRaw(1);
                                if ($model_attr == 1) {
                                    if (isset($warehouse_id[$key]) && !empty($warehouse_id[$key])) {
                                        $res = ProductsWarehouse::where('warehouse_id', $warehouse_id[$key]);
                                    } else {
                                        continue;
                                    }
                                } elseif ($model_attr == 2) {
                                    if (isset($area_id[$key]) && !empty($area_id[$key])) {
                                        $res = ProductsArea::where('area_id', $area_id[$key]);
                                    } else {
                                        continue;
                                    }
                                }

                                $data = ['product_price' => $value];
                                $res = $res->where('goods_id', $goods_id)->where('product_id', $key);
                                $res->update($data);
                            }
                        }

                        // 更新商品
                        $goods = [
                            'last_update' => gmtime(),
                        ];

                        if ($market_price[$goods_id]) {
                            $goods['market_price'] = floatval($market_price[$goods_id]);
                        }

                        if ($shop_price[$goods_id]) {
                            $goods['shop_price'] = floatval($shop_price[$goods_id]);
                        }

                        if ($cost_price[$goods_id]) {
                            $goods['cost_price'] = floatval($cost_price[$goods_id]);
                        }

                        if ($integral[$goods_id]) {
                            $goods['integral'] = intval($integral[$goods_id]);
                        }

                        if ($give_integral[$goods_id]) {
                            $goods['give_integral'] = intval($give_integral[$goods_id]);
                        }

                        if ($goods_number[$goods_id] >= 0) {
                            $goods['goods_number'] = intval($goods_number[$goods_id]);
                        }

                        if ($brand_id[$goods_id]) {
                            $goods['brand_id'] = intval($brand_id[$goods_id]);

                            $brandList = BrandDataHandleService::goodsBrand($goods['brand_id'], ['brand_id', 'brand_name']);
                            $brand_name = $brandList[$goods['brand_id']]['brand_name'] ?? '';

                            if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                                $goods_name = StrRepository::replaceFirst($goods_name, $brand_name);
                                $goods_name = StrRepository::replaceFirst($goods_name, config('app.replace_symbol'));
                                $goods['goods_name'] = $brand_name . config('app.goods_symbol') . $goods_name;
                            }
                        }

                        Goods::where('goods_id', $goods_id)->update($goods);

                        // 更新会员价格
                        if ($rank_ids) {
                            foreach ($rank_ids as $rank_id) {
                                if (trim($member_price[$goods_id][$rank_id]) == '') {
                                    /* 为空时不做处理 */
                                    continue;
                                }

                                $rank = [
                                    'goods_id' => $goods_id,
                                    'user_rank' => $rank_id,
                                    'user_price' => floatval($member_price[$goods_id][$rank_id]),
                                ];

                                $res = MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank_id)->count();
                                if ($res > 0) {
                                    if ($rank['user_price'] < 0) {
                                        MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank_id)->delete();
                                    } else {
                                        MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank_id)->update($rank);
                                    }
                                } else {
                                    if ($rank['user_price'] >= 0) {
                                        MemberPrice::insert($rank);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // 提示失败
                    $link[] = ['href' => 'goods_batch.php?act=select', 'text' => __('admin::common.15_batch_edit')];
                    return sys_msg(__('admin::goods_batch.batch_edit_null'), 1, $link);
                }
            } else {
                // 循环更新每个商品
                if ($goods_ids) {
                    foreach ($goods_ids as $goods_id) {
                        // 更新商品
                        $goods = [];
                        if (request()->has('market_price') && !is_null(request()->get('market_price'))) {
                            $goods['market_price'] = floatval(request()->get('market_price'));
                        }
                        if (request()->has('shop_price') && !is_null(request()->get('shop_price'))) {
                            $goods['shop_price'] = floatval(request()->get('shop_price'));
                        }
                        if (request()->has('cost_price') && !is_null(request()->get('cost_price'))) {
                            $goods['cost_price'] = floatval(request()->get('cost_price'));
                        }
                        if (request()->has('integral') && !is_null(request()->get('integral'))) {
                            $goods['integral'] = intval(request()->get('integral'));
                        }
                        if (request()->has('give_integral') && !is_null(request()->get('give_integral'))) {
                            $goods['give_integral'] = intval(request()->get('give_integral'));
                        }
                        if (request()->has('goods_number') && !is_null(request()->get('goods_number'))) {
                            $goods['goods_number'] = intval(request()->get('goods_number'));
                        }
                        if (request()->get('brand_id', 0) > 0 && !is_null(request()->get('brand_id'))) {
                            $goods['brand_id'] = request()->get('brand_id', 0);
                        }

                        if (!empty($goods)) {
                            Goods::where('goods_id', $goods_id)->update($goods);
                        }

                        // 更新会员价格
                        if ($rank_ids) {
                            foreach ($rank_ids as $rank_id) {
                                if (trim($member_price[$rank_id]) != '') {
                                    $rank = [
                                        'goods_id' => $goods_id,
                                        'user_rank' => $rank_id,
                                        'user_price' => floatval($member_price[$rank_id]),
                                    ];

                                    $res = MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank_id)->count();
                                    if ($res > 0) {
                                        if ($rank['user_price'] < 0) {
                                            MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank_id)->delete();
                                        } else {
                                            MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank_id)->update($rank);
                                        }
                                    } else {
                                        if ($rank['user_price'] >= 0) {
                                            MemberPrice::insert($rank);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // 记录日志
            admin_log('', 'batch_edit', 'goods');

            // 提示成功
            $link[] = ['href' => 'goods_batch.php?act=select', 'text' => $GLOBALS['_LANG']['15_batch_edit']];
            return sys_msg(__('admin::goods_batch.batch_edit_ok'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 下载文件
        /*------------------------------------------------------ */

        elseif ($act == 'download') {
            /* 检查权限 */
            admin_priv('goods_batch');

            // 文件标签
            // Header("Content-type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=goods_list.csv");
            $charset = request()->get('charset', '');
            // 下载
            if (__('admin::goods_batch.upload_goods')) {
                /* 创建字符集转换对象 */
                if ($charset == 'zh-CN' || $charset == 'zh-TW') {
                    $to_charset = $charset == 'zh-CN' ? 'GB2312' : 'BIG5';
                    echo dsc_iconv(EC_CHARSET, $to_charset, join(',', __('admin::goods_batch.upload_goods')));
                } else {
                    echo join(',', __('admin::goods_batch.upload_goods'));
                }
            } else {
                echo 'error: ' . __('admin::goods_batch.upload_goods') . ' not exists';
            }
        }

        /*------------------------------------------------------ */
        //-- 取得商品
        /*------------------------------------------------------ */

        elseif ($act == 'get_goods') {
            $filter = app(\StdClass::class);

            $filter->cat_id = request()->get('cat_id');
            $filter->brand_id = request()->get('brand_id');
            $filter->real_goods = -1;
            $arr = get_goods_list($filter);

            return make_json_result($arr);
        }
    }

    /**
     * 批量添加商品
     *
     * @param array $checked
     * @param int $type
     */
    private function batchGoods($checked = [], $type = 0)
    {

        $image = new Image(['bgcolor' => config('shop.bgcolor')]);

        /* 字段默认值 */
        $default_value = [
            'brand_id' => 0,
            'goods_number' => 0,
            'goods_weight' => 0,
            'market_price' => 0,
            'shop_price' => 0,
            'warn_number' => 0,
            'is_real' => 1,
            'is_on_sale' => 1,
            'is_alone_sale' => 1,
            'integral' => 0,
            'is_best' => 0,
            'is_new' => 0,
            'is_hot' => 0,
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
        $field_list = array_keys(__('admin::goods_batch.upload_goods'));
        $field_list[] = 'goods_class'; //实体或虚拟商品
        $cat = request()->get('cat', 0);

        $goods_sn = BaseRepository::getKeyPluck($checked, 'goods_sn');
        $goods_name = BaseRepository::getKeyPluck($checked, 'goods_name');

        $goodsCheckedList = [];
        if ($goods_sn && $goods_name) {
            $goodsCheckedList = Goods::where(function ($query) use ($goods_sn, $goods_name, $cat) {
                if ($goods_sn) {
                    $query = $query->whereIn('goods_sn', $goods_sn);
                }

                if ($goods_name) {
                    $query->orWhere(function ($query) use ($goods_name, $cat) {
                        $query = $query->whereIn('goods_name', $goods_name);

                        if ($cat) {
                            $query->where('cat_id', $cat);
                        }
                    });
                }
            });

            $goodsCheckedList = $goodsCheckedList->where('user_id', 0);
            $goodsCheckedList = BaseRepository::getToArrayGet($goodsCheckedList);
        }

        /* 循环插入商品数据 */
        foreach ($checked as $key => $value) {

            $goodsCheckedSn = [];
            $goodsCheckedName = [];
            if ($goodsCheckedList) {

                $goodsChecked = [];
                if (isset($value['goods_sn']) && !empty($value['goods_sn'])) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'goods_sn',
                                'value' => $value['goods_sn']
                            ]
                        ]
                    ];
                    $goodsCheckedSn = BaseRepository::getArraySqlFirst($goodsCheckedList, $sql);
                    $goodsCheckedSn = $goodsCheckedSn ? $goodsCheckedSn : [];
                }

                if ($goodsChecked && !empty($value['goods_name'])) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'goods_name',
                                'value' => $value['goods_name']
                            ]
                        ]
                    ];

                    if ($cat) {
                        $sql['where'][] = [
                            'name' => 'cat_id',
                            'value' => $cat
                        ];
                    }

                    $goodsCheckedName = BaseRepository::getArraySqlFirst($goodsCheckedList, $sql);
                    $goodsCheckedName = $goodsCheckedName ? $goodsCheckedName : [];
                }
            }

            $is_add = empty($goodsCheckedSn) && empty($goodsCheckedName) ? true : false;

            if ($is_add) {
                // 合并
                $field_arr = [
                    'cat_id' => $cat,
                    'add_time' => TimeRepository::getGmTime(),
                    'last_update' => TimeRepository::getGmTime()
                ];

                foreach ($field_list as $field) {

                    if ($type == 1) {
                        if (isset($value[$field])) {
                            $field_value = $field_arr[$field] = $value[$field];
                        } else {
                            continue;
                        }

                        /* 虚拟商品处理 */
                        if ($field == 'goods_class' && $field_arr[$field] == G_CARD) {
                            $field_arr['extension_code'] = 'virtual_card';
                            continue;
                        }
                    } else {
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
                    }

                    // 特殊处理
                    if (isset($field_value) && !empty($field_value)) {

                        // 如果字段值为空，且有默认值，取默认值
                        $field_arr[$field] = !isset($field_value) && isset($default_value[$field]) ? $default_value[$field] : $field_value;

                        // 图片路径
                        if (in_array($field, ['original_img', 'goods_img', 'goods_thumb'])) {
                            if (strpos($field_value, '|;') > 0) {
                                $field_value = explode(':', $field_value);
                                $field_value = $field_value['0'];
                                @copy(storage_public('images/' . $field_value . '.tbi'), storage_public('images/' . $field_value . '.jpg'));
                                if (is_file(storage_public('images/' . $field_value . '.jpg'))) {
                                    $field_arr[$field] = storage_public(IMAGE_DIR . '/' . $field_value . '.jpg');
                                }
                            } else {
                                $field_arr[$field] = storage_public(IMAGE_DIR . '/' . $field_value);
                            }
                        } // 品牌
                        elseif ($field == 'brand_name') {
                            if (isset($brand_list[$field_value])) {
                                $field_arr['brand_id'] = $brand_list[$field_value];
                            } else {
                                $data = ['brand_name' => addslashes($field_value)];
                                $brand_id = Brand::insertGetId($data);
                                $brand_list[$field_value] = $brand_id;
                                $field_arr['brand_id'] = $brand_id;
                            }
                        } // 整数型
                        elseif (in_array($field, ['goods_number', 'warn_number', 'integral'])) {
                            $field_arr[$field] = intval($field_value);
                        } // 数值型
                        elseif (in_array($field, ['goods_weight', 'market_price', 'shop_price'])) {
                            $field_arr[$field] = floatval($field_value);
                        } // bool型
                        elseif (in_array($field, ['is_best', 'is_new', 'is_hot', 'is_on_sale', 'is_alone_sale', 'is_real'])) {
                            $field_arr[$field] = intval($field_value) > 0 ? 1 : 0;
                        }
                    }

                    if ($type == 0) {
                        if ($field == 'is_real') {
                            $field_arr[$field] = intval($_POST['goods_class'][$key]);
                        }
                    }
                }

                /* 如果是虚拟商品，库存为0 */
                if ($field_arr['is_real'] == 0) {
                    $field_arr['goods_number'] = 0;
                }

                if ($field_arr && $field_arr['goods_name']) {
                    if (isset($field_arr['goods_name']) && $field_arr['goods_name']) {

                        if (isset($field_arr['brand_id']) && !empty($field_arr['brand_id'])) {
                            $brandList = BrandDataHandleService::goodsBrand($field_arr['brand_id'], ['brand_id', 'brand_name']);
                            $brand_name = $brandList[$field_arr['brand_id']]['brand_name'] ?? '';

                            if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                                $field_arr['goods_name'] = StrRepository::replaceFirst($field_arr['goods_name'], $brand_name);
                                $field_arr['goods_name'] = StrRepository::replaceFirst($field_arr['goods_name'], config('app.replace_symbol'));
                                $field_arr['goods_name'] = $brand_name . config('app.goods_symbol') . $field_arr['goods_name'];
                            }
                        }

                        $field_arr['goods_name'] = addslashes($field_arr['goods_name']);
                    }

                    if (isset($field_arr['goods_brief']) && $field_arr['goods_brief']) {
                        $field_arr['goods_brief'] = str_replace("'", '"', $field_arr['goods_brief']);
                        $field_arr['goods_brief'] = stripcslashes($field_arr['goods_brief']);
                    }

                    if (isset($field_arr['goods_desc']) && $field_arr['goods_desc']) {
                        $field_arr['goods_desc'] = str_replace("'", '"', $field_arr['goods_desc']);
                        $field_arr['goods_desc'] = stripcslashes($field_arr['goods_desc']);
                    }

                    $field_arr['user_id'] = 0;
                    $field_arr['review_status'] = 5;

                    $new_goods = BaseRepository::getArrayfilterTable($field_arr, 'goods');
                    try {
                        $goods_id = Goods::insertGetId($new_goods);
                    } catch (\Exception $e) {
                        if ($type == 1) {
                            continue;
                        } else {
                            $error_no = (stripos($e->getMessage(), '1062 Duplicate entry') !== false) ? 1062 : $e->getCode();

                            if ($error_no > 0 && $error_no != 1062) {
                                die($e->getMessage());
                            }
                        }

                        $goods_id = 0;
                    }

                    $max_id = $goods_id + 1;

                    if (empty($field_arr['goods_sn'])) {
                        $goods_sn = $this->goodsManageService->generateGoodSn($max_id);

                        Goods::where('goods_id', $goods_id)->update([
                            'goods_sn' => $goods_sn
                        ]);
                    } else {
                        $same_goods_sn = Goods::where('goods_id', '<>', $goods_id)->where('user_id', 0)->where('goods_sn', $field_arr['goods_sn'])->count();
                        if ($same_goods_sn > 0) {
                            $goods_sn = $this->goodsManageService->generateGoodSn($max_id);

                            Goods::where('goods_id', $goods_id)->update([
                                'goods_sn' => $goods_sn
                            ]);
                        }
                    }

                    /* 如果图片不为空,修改商品图片，插入商品相册*/
                    if (!empty($field_arr['original_img']) || !empty($field_arr['goods_img']) || !empty($field_arr['goods_thumb'])) {
                        $goods_img = '';
                        $goods_thumb = '';
                        $original_img = '';
                        $goods_gallery = [];
                        $goods_gallery['goods_id'] = $goods_id;
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
                            'original_img' => $original_img,
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
    }
}
