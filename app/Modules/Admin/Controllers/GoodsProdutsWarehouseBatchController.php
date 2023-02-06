<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Goods;
use App\Models\ProductsWarehouse;
use App\Repositories\Common\DscRepository;
use App\Services\Goods\GoodsProdutsWarehouseBatchManageService;
use App\Services\Merchant\MerchantCommonService;

/**
 * 商品批量上传、修改
 */
class GoodsProdutsWarehouseBatchController extends InitController
{
    protected $dscRepository;
    protected $merchantCommonService;
    protected $goodsProdutsWarehouseBatchManageService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        GoodsProdutsWarehouseBatchManageService $goodsProdutsWarehouseBatchManageService
    ) {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsProdutsWarehouseBatchManageService = $goodsProdutsWarehouseBatchManageService;
    }

    public function index()
    {
        load_helper('goods', 'admin');
        $act = request()->get('act', '');

        /* ------------------------------------------------------ */
        //-- 批量上传
        /* ------------------------------------------------------ */

        if ($act == 'add') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'produts_warehouse_batch']);
            $goods_id = request()->get('goods_id', 0);
            $model = request()->get('model', 0);
            $warehouse_id = request()->get('warehouse_id', 0);

            if ($goods_id > 0) {
                $this->smarty->assign('action_link', ['text' => __('admin::common.back_goods_qua_desc'), 'href' => 'goods.php?act=product_list&goods_id=' . $goods_id]);
            }

            $lang_list = [
                'UTF8' => __('admin::common.charset.utf8'),
                'GB2312' => __('admin::common.charset.zh_cn'),
                'BIG5' => __('admin::common.charset.zh_tw'),
            ];

            /* 取得可选语言 */
            $download_list = $this->dscRepository->getDdownloadTemplate(resource_path('lang'));

            $this->smarty->assign('use_lang', config('shop.lang'));//现在使用的语言包名
            $this->smarty->assign('lang_list', $lang_list);
            $this->smarty->assign('download_list', $download_list);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('model', $model);
            $this->smarty->assign('warehouse_id', $warehouse_id);

            $attribute_list = $this->goodsProdutsWarehouseBatchManageService->getAttributeList($goods_id);
            $this->smarty->assign('attribute_list', $attribute_list);

            $goods_date = ['goods_name'];
            $where = "goods_id = '$goods_id'";
            $goods_name = get_table_date('goods', $where, $goods_date, 2);
            $this->smarty->assign('goods_name', $goods_name);

            /* 参数赋值 */
            $ur_here = __('admin::common.13_batch_add');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_produts_warehouse_batch.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 批量上传：处理
        /* ------------------------------------------------------ */
        elseif ($act == 'upload') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'produts_area_batch']);

            $attr_names = [];
            $goods_list = [];
            $file = request()->file('file');
            //ecmoban模板堂 --zhuo start 仓库
            if ($file && $file->isValid()) {

                //获得属性的个数 bylu;
                $attr_names = file($file->getRealPath());
                $attr_names = explode(',', $attr_names[0]);
                if (config('shop.goods_attr_price') == 1) {
                    if (config('shop.add_shop_price') == 1) {
                        $end = -7 + 2;
                    } else {
                        $end = -7;
                    }
                } else {
                    $end = -4;
                }

                $attr_names = array_slice($attr_names, 6, $end);
                foreach ($attr_names as $k => $v) {
                    $attr_names[$k] = dsc_iconv('GBK', 'UTF8', $v);
                }
                $attr_num = count($attr_names);

                $line_number = 0;
                $upload_product = __('admin::goods_produts_batch.upload_product');
                $field_list = array_keys($upload_product); // 字段列表
                for ($i = 0; $i < $attr_num; $i++) {
                    $field_list[] = 'goods_attr' . $i;
                }

                if (config('shop.goods_attr_price') == 1) {
                    if (config('shop.add_shop_price') == 0) {
                        $field_list[] = 'product_market_price';
                    }
                    $field_list[] = 'product_price';
                    if (config('shop.add_shop_price') == 0) {
                        $field_list[] = 'product_promote_price';
                    }
                }

                $field_list[] = 'product_number';
                $field_list[] = 'product_warn_number';
                $field_list[] = 'product_sn';
                $field_list[] = 'bar_code';

                $charset = 'GB2312';
                $data = file($file->getRealPath());

                if (count($data) > 0) {
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

                    //格式化商品数据 bylu;
                    $goods_list = get_produts_warehouse_list2($goods_list, $attr_num);
                }
            }

            session([
                'goods_list' => $goods_list
            ]);

            $this->smarty->assign('full_page', 2);
            $this->smarty->assign('page', 1);
            $this->smarty->assign('attr_names', $attr_names); //属性名称;
            $this->smarty->assign('cfg', config('shop'));

            /* 显示模板 */

            $this->smarty->assign('ur_here', __('admin::common.13_batch_add'));
            return $this->smarty->display('goods_produts_warehouse_batch_add.dwt');
        }


        /* ------------------------------------------------------ */
        //-- 动态添加数据�        �库;
        /* ------------------------------------------------------ */
        elseif ($act == 'ajax_insert') {
            /* 检查权限 */
            admin_priv('goods_manage');


            $result = ['list' => [], 'is_stop' => 0];
            $page = request()->get('page', 1);
            $page_size = request()->get('page_size', 1);

            /* 设置最长执行时间为5分钟 */
            @set_time_limit(300);

            if (session()->has('goods_list') && session('goods_list')) {
                $goods_list = session('goods_list');

                $goods_list = $this->dsc->page_array($page_size, $page, $goods_list);

                $result['list'] = isset($goods_list['list']) && $goods_list['list'] ? $goods_list['list'][0] : [];

                $result['page'] = $goods_list['filter']['page'] + 1;
                $result['page_size'] = $goods_list['filter']['page_size'];
                $result['record_count'] = $goods_list['filter']['record_count'];
                $result['page_count'] = $goods_list['filter']['page_count'];

                $result['is_stop'] = 1;
                if ($page > $goods_list['filter']['page_count']) {
                    $result['is_stop'] = 0;
                }

                $other['goods_id'] = $result['list']['goods_id'];
                $other['goods_attr'] = $result['list']['goods_attr'];

                if (config('shop.goods_attr_price') == 1) {
                    if (config('shop.add_shop_price') == 0) {
                        $other['product_market_price'] = $result['list']['product_market_price'];
                    }
                    $other['product_price'] = $result['list']['product_price'];
                    if (config('shop.add_shop_price') == 0) {
                        $other['product_promote_price'] = $result['list']['product_promote_price'];
                    }
                }

                $other['product_number'] = $result['list']['product_number'];
                $other['product_warn_number'] = $result['list']['product_warn_number'];

                if ($result['list']['product_sn']) {
                    $other['product_sn'] = $result['list']['product_sn'];
                }

                if ($result['list']['bar_code']) {
                    $other['bar_code'] = $result['list']['bar_code'];
                }

                $other['warehouse_id'] = $result['list']['warehouse_id'];

                $res = ProductsWarehouse::whereRaw(1);
                if (!empty($result['list']['goods_attr'])) {
                    $goods_attr = explode("|", $result['list']['goods_attr']);

                    //获取货品信息
                    foreach ($goods_attr as $key => $val) {
                        $res = $res->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr, '|', ','))");
                    }

                    //查询数据是否已经存在;
                    $product_id = $res->where('goods_id', $result['list']['goods_id'])
                        ->where('warehouse_id', $result['list']['warehouse_id'])
                        ->value('product_id');

                    if ($product_id) {
                        ProductsWarehouse::where('product_id', $product_id)->update($other);

                        if (config('shop.goods_attr_price') == 1) {
                            $goods_other = [
                                'product_price' => $result['list']['product_price'],
                                'product_promote_price' => $result['list']['product_promote_price']
                            ];
                            Goods::where('goods_id', $result['list']['goods_id'])
                                ->where('product_id', $product_id)
                                ->where('product_table', 'products_warehouse')
                                ->update($goods_other);
                        }

                        $result['status_lang'] = '<span style="color: red;">' . __('admin::common.upload_date_success') . '</span>';
                    } else {
                        $admin_id = get_admin_id();
                        $other['admin_id'] = $admin_id;

                        $product_id = ProductsWarehouse::insertGet($other);

                        if ($product_id) {
                            $result['status_lang'] = '<span style="color: red;">' . __('admin::common.add_date_success') . '</span>';
                        } else {
                            $result['status_lang'] = '<span style="color: red;">' . __('admin::common.add_date_fail') . '</span>';
                        }
                    }
                }
            }
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 下载文件
        /* ------------------------------------------------------ */
        elseif ($act == 'download') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);
            $model = request()->get('model', 0);
            $warehouse_id = request()->get('warehouse_id', 0);
            $goods_attr = request()->get('goods_attr', '');
            $goods_attr = explode(',', $goods_attr);

            // 文件标签
            // Header("Content-type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            Header("Content-Disposition: attachment; filename=goods_produts_warehouse_list" . $goods_id . "_" . "$warehouse_id" . ".csv");

            // 下载
            $lang = request()->get('lang', '');
            if ($lang != config('shop.lang')) {
                $upload_product = __('admin::goods_produts_warehouse_batch.upload_product', [], $lang);
            } else {
                $upload_product = __('admin::goods_produts_warehouse_batch.upload_product');
            }

            if (is_array($upload_product)) {
                /* 创建字符集转换对象 */
                if ($lang == 'zh-CN' || $lang == 'zh-TW') {
                    $to_charset = $lang == 'zh-CN' ? 'GB2312' : 'BIG5';
                    $data = join(',', $upload_product);

                    /* 获取商品规格列表 */
                    $attribute = get_goods_specifications_list($goods_id);
                    if (empty($attribute)) {
                        $link[] = ['href' => 'goods.php?act=edit&goods_id=' . $goods_id, 'text' => __('admin::goods_produts_warehouse_batch.edit_goods')];
                        return sys_msg(__('admin::goods.not_exist_goods_attr'), 1, $link);
                    }
                    foreach ($attribute as $attribute_value) {
                        //转换成数组
                        $_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
                        $_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
                        $_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
                    }

                    //获取属性名称 bylu;
                    foreach ($_attribute as $k => $v) {
                        $data .= ',' . $v['attr_name'];
                    }

                    if (config('shop.goods_attr_price') == 1) {
                        if (config('shop.add_shop_price') == 0) {
                            $data .= "," . __('admin::goods_produts_warehouse_batch.market_price');
                        }
                        $data .= "," . __('admin::goods_produts_warehouse_batch.product_price');
                        if (config('shop.add_shop_price') == 0) {
                            $data .= "," . __('admin::goods_produts_warehouse_batch.promote_price');
                        }
                    }

                    $data .= "," . __('admin::goods_produts_warehouse_batch.product_number');
                    $data .= "," . __('admin::goods_produts_warehouse_batch.product_warn_number');
                    $data .= "," . __('admin::goods_produts_warehouse_batch.product_sn');
                    $data .= "," . __('admin::goods_produts_warehouse_batch.bar_code') . "\t\n";

                    if ($goods_id) {
                        $goods_info = get_admin_goods_info($goods_id);
                        $goods_info['shop_name'] = $this->merchantCommonService->getShopName($goods_info['user_id'], 1);
                    } else {
                        $adminru = get_admin_ru_id();

                        $goods_info['user_id'] = $adminru['ru_id'];
                        $goods_info['shop_name'] = $this->merchantCommonService->getShopName($adminru['ru_id'], 1);
                    }

                    $area_date = ['region_name'];

                    $warehouse_where = '';
                    if ($warehouse_id > 0) {
                        $warehouse_where = " and region_id = " . $warehouse_id;
                    }

                    $where = "region_type = 0" . $warehouse_where;
                    $warehouse_info = get_table_date('region_warehouse', $where, $area_date, 1);

                    $attr_info = $this->goodsProdutsWarehouseBatchManageService->getListDownload($goods_info['goods_sn'], $warehouse_info, $_attribute, count($_attribute), $model);

                    if ($attr_info) {
                        if (count($attr_info) > 1) {
                            foreach ($attr_info as $k => $v) {
                                $data .= $goods_id . ',';
                                $data .= $goods_info['goods_name'] . ',';
                                $data .= $goods_info['goods_sn'] . ',';
                                $data .= $goods_info['shop_name'] . ',';
                                $data .= $goods_info['user_id'] . ',';
                                $data .= $attr_info[$k]['region_name'] . ',';
                                $data .= implode(',', $v['attr_value']) . ',';

                                if (config('shop.goods_attr_price') == 1) {
                                    if (config('shop.add_shop_price') == 0) {
                                        $data .= $attr_info[$k]['product_market_price'] . ',';
                                    }
                                    $data .= $attr_info[$k]['product_price'] . ',';
                                    if (config('shop.add_shop_price') == 0) {
                                        $data .= $attr_info[$k]['product_promote_price'] . ',';
                                    }
                                }

                                $data .= $attr_info[$k]['product_number'] . ',';
                                $data .= $attr_info[$k]['product_warn_number'] . ',';
                                $data .= $attr_info[$k]['product_sn'] . ',';
                                $data .= $attr_info[$k]['bar_code'] . "\t\n";
                            }
                        } else {
                            $attr_value = $attr_info[0]['attr_value'];

                            $data .= $goods_id . ',';
                            $data .= $goods_info['goods_name'] . ',';
                            $data .= $goods_info['goods_sn'] . ',';
                            $data .= $goods_info['shop_name'] . ',';
                            $data .= $goods_info['user_id'] . ',';
                            $data .= $attr_info[0]['region_name'] . ',';

                            foreach ($attr_value as $key => $value) {
                                $data .= $value . ',';
                            }

                            if (config('shop.goods_attr_price') == 1) {
                                if (config('shop.add_shop_price') == 0) {
                                    $data .= $attr_info[0]['product_market_price'] . ',';
                                }
                                $data .= $attr_info[0]['product_price'] . ',';
                                if (config('shop.add_shop_price') == 0) {
                                    $data .= $attr_info[0]['product_promote_price'] . ',';
                                }
                            }

                            $data .= $attr_info[0]['product_number'] . ',';
                            $data .= $attr_info[0]['product_warn_number'] . ',';
                            $data .= $attr_info[0]['product_sn'] . ',';
                            $data .= $attr_info[0]['bar_code'] . "\t\n";
                        }
                    }

                    echo dsc_iconv(EC_CHARSET, $to_charset, $data);
                } else {
                    echo join(',', $upload_product);
                }
            } else {
                echo 'error: ' . $upload_product . ' not exists';
            }
        }
    }
}
