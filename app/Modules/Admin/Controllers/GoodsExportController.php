<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Phpzip;
use App\Models\GoodsAttr;
use App\Models\Region;
use App\Repositories\Common\BaseRepository;
use App\Services\Goods\GoodsExportManageService;

/**
 *
 */
class GoodsExportController extends InitController
{
    protected $goodsExportManageService;

    public function __construct(
        GoodsExportManageService $goodsExportManageService
    )
    {
        $this->goodsExportManageService = $goodsExportManageService;
    }

    public function index()
    {
        load_helper('goods', 'admin');

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '14_goods_export']);
        $act = request()->get('act', '');

        if ($act == 'goods_export') {
            /* 检查权限 */
            admin_priv('goods_export');

            $this->smarty->assign('ur_here', __('admin::common.14_goods_export'));
            $this->smarty->assign('goods_type_list', goods_type_list(0, 0, 'array'));
            $goods_fields = my_array_merge(__('admin::common.custom'), $this->goodsExportManageService->getAttributes());
            $data_format_array = [
                'dscmall' => __('admin::goods_export.export_dscmall')
            ];
            $this->smarty->assign('data_format', $data_format_array);
            $this->smarty->assign('goods_fields', $goods_fields);

            $goods_id = '';
            set_default_filter($goods_id); //设置默认筛选

            return $this->smarty->display('goods_export.dwt');
        } elseif ($act == 'act_export_dscmall') {
            /* 检查权限 */
            admin_priv('goods_export');


            $zip = new Phpzip;

            /* 设置最长执行时间为5分钟 */
            @set_time_limit(300);


            $result = ['error' => 0, 'mark' => 0, 'message' => '', 'content' => '', 'done' => 2];
            $result['page_size'] = request()->get('page_size', 10);
            $result['page'] = request()->get('page', 1);
            $result['total'] = request()->get('total', 1);

            if (request()->isMethod('post')) {
                $where = $this->goodsExportManageService->getExportWhereSql(request()->all());
            } else {
                $filter = dsc_decode(request()->get('filter', ''));
                $arr = $this->goodsExportManageService->getExportStepWhereSql($filter);
                $where = $arr['where'];
            }

            $page_size = 50; // 默认50张/页
            $sql = "SELECT count(*) FROM " . $this->dsc->table('goods') . " AS g LEFT JOIN " . $this->dsc->table('brand') . " AS b " .
                "ON g.brand_id = b.brand_id" . $where;
            $count = $this->db->getOne($sql);

            /* 页数在许可范围内 */
            if ($result['page'] <= ceil($count / $result['page_size'])) {
                $start_time = gmtime(); //开始执行时间

                $sql = "SELECT g.*, b.brand_name as brandname " .
                    " FROM " . $this->dsc->table('goods') . " AS g LEFT JOIN " . $this->dsc->table('brand') . " AS b " .
                    "ON g.brand_id = b.brand_id" . $where;
                $res = $this->db->SelectLimit($sql, $result['page_size'], ($result['page'] - 1) * $result['page_size']);

                /* csv文件数组 */
                $goods_value = [];
                $goods_value['goods_name'] = '""';
                $goods_value['goods_sn'] = '""';
                $goods_value['brand_name'] = '""';
                $goods_value['market_price'] = 0;
                $goods_value['shop_price'] = 0;
                $goods_value['cost_price'] = 0;
                $goods_value['integral'] = 0;
                $goods_value['original_img'] = '""';
                $goods_value['goods_img'] = '""';
                $goods_value['goods_thumb'] = '""';
                $goods_value['keywords'] = '""';
                $goods_value['goods_brief'] = '""';
                $goods_value['goods_desc'] = '""';
                $goods_value['goods_weight'] = 0;
                $goods_value['goods_number'] = 0;
                $goods_value['warn_number'] = 0;
                $goods_value['is_best'] = 0;
                $goods_value['is_new'] = 0;
                $goods_value['is_hot'] = 0;
                $goods_value['is_on_sale'] = 1;
                $goods_value['is_alone_sale'] = 1;
                $goods_value['is_real'] = 1;
                $content = pack('H*', 'EFBBBF') . '"' . implode('","', __('admin::goods_export.dscmall')) . "\"\n";

                foreach ($res as $row) {
                    $goods_value['goods_name'] = '"' . $row['goods_name'] . '"';
                    $goods_value['goods_sn'] = '"' . $row['goods_sn'] . '"';
                    $goods_value['brand_name'] = '"' . $row['brandname'] . '"';
                    $goods_value['market_price'] = $row['market_price'];
                    $goods_value['shop_price'] = $row['shop_price'];
                    $goods_value['cost_price'] = $row['cost_price'];
                    $goods_value['integral'] = $row['integral'];
                    $goods_value['original_img'] = '"' . $row['original_img'] . '"';
                    $goods_value['goods_img'] = '"' . $row['goods_img'] . '"';
                    $goods_value['goods_thumb'] = '"' . $row['goods_thumb'] . '"';
                    $goods_value['keywords'] = '"' . $row['keywords'] . '"';
                    $goods_value['goods_brief'] = '"' . htmlspecialchars($row['goods_brief']) . '"';
                    $goods_value['goods_desc'] = '"' . preg_replace("/\r\n/", '', str_replace('\r\n', '', htmlspecialchars($row['goods_desc']))) . '"';
                    $goods_value['goods_weight'] = $row['goods_weight'];
                    $goods_value['goods_number'] = $row['goods_number'];
                    $goods_value['warn_number'] = $row['warn_number'];
                    $goods_value['is_best'] = $row['is_best'];
                    $goods_value['is_new'] = $row['is_new'];
                    $goods_value['is_hot'] = $row['is_hot'];
                    $goods_value['is_on_sale'] = $row['is_on_sale'];
                    $goods_value['is_alone_sale'] = $row['is_alone_sale'];
                    $goods_value['is_real'] = $row['is_real'];

                    $content .= implode(",", $goods_value) . "\n";

                    /* 压缩图片 */
                    if (!empty($row['goods_img']) && is_file(storage_path($row['goods_img']))) {
                        $zip->add_file(file_get_contents(storage_path($row['goods_img'])), $row['goods_img']);
                    }
                    if (!empty($row['original_img']) && is_file(storage_path($row['original_img']))) {
                        $zip->add_file(file_get_contents(storage_path($row['original_img'])), $row['original_img']);
                    }
                    if (!empty($row['goods_thumb']) && is_file(storage_path($row['goods_thumb']))) {
                        $zip->add_file(file_get_contents(storage_path($row['goods_thumb'])), $row['goods_thumb']);
                    }
                }
                $charset = request()->get('charset', 'UTF8');

                $zip->add_file(dsc_iconv(EC_CHARSET, $charset, $content), 'goods_list.csv');

                $filename = "goods_list.zip";
                return response()->streamDownload(function () use ($zip) {
                    echo $zip->file();
                }, $filename);
            }
        } elseif ($act == 'act_export_step_search') {
            /* 检查权限 */
            admin_priv('goods_export');
            /* 设置最长执行时间为5分钟 */
            @set_time_limit(300);


            $filter = dsc_decode(request()->get('filter', ''));
            $arr = $this->goodsExportManageService->getExportStepWhereSql($filter);
            $where = $arr['where'];
            $page_size = 50; // 默认50张/页
            $sql = "SELECT count(*) FROM " . $this->dsc->table('goods') . " AS g LEFT JOIN " . $this->dsc->table('brand') . " AS b " .
                "ON g.brand_id = b.brand_id" . $where;
            $count = $this->db->getOne($sql);
            $start = request()->get('start', 0);
            if ($start == 1) {
                $title = __('admin::common.goods_manage_date');

                $silent = isset($silent) ? $silent : '';
                $data_cat = isset($data_cat) ? $data_cat : '';

                $result = ['error' => 0, 'mark' => 0, 'message' => '', 'content' => '', 'done' => 1, 'title' => $title, 'page_size' => $page_size,
                    'page' => 1, 'total' => 1, 'silent' => $silent, 'data_cat' => $data_cat,
                    'row' => ['new_page' => sprintf(__('admin::common.page_format'), 1),
                        'new_total' => sprintf(__('admin::common.total_format'), ceil($count / $page_size)),
                        'new_time' => __('admin::common.wait'),
                        'cur_id' => 'time_1']];
                $result['total_page'] = ceil($count / $page_size);
                $result['filter'] = $arr['filter'];
                clear_cache_files();
                return response()->json($result);
            } else {
                $result = ['error' => 0, 'mark' => 0, 'message' => '', 'content' => '', 'done' => 2];
                $result['page_size'] = request()->get('page_size', 50);
                $result['page'] = request()->get('page', 1);
                $result['total'] = request()->get('total', 1);
                $result['total_page'] = ceil($count / $result['page_size']);
                $result['row'] = ['new_page' => sprintf(__('admin::common.page_format'), 1),
                    'new_total' => sprintf(__('admin::common.total_format'), ceil($count / $page_size)),
                    'new_time' => __('admin::common.wait'),
                    'cur_id' => 'time_1'];

                /* 页数在许可范围内 */
                if ($result['page'] <= ceil($count / $result['page_size'])) {
                    $start_time = gmtime(); //开始执行时间
                    $end_time = gmtime();
                    $result['row']['pre_id'] = 'time_' . $result['total'];
                    $result['row']['pre_time'] = ($end_time > $start_time) ? $end_time - $start_time : 1;
                    $result['row']['pre_time'] = sprintf(__('admin::common.time_format'), $result['row']['pre_time']);
                    $result['row']['cur_id'] = 'time_' . ($result['total'] + 1);
                    $result['page']++; // 新行
                    $result['row']['new_page'] = sprintf(__('admin::common.page_format'), $result['page']);
                    $result['row']['new_total'] = sprintf(__('admin::common.total_format'), ceil($count / $result['page_size']));
                    $result['row']['new_time'] = __('admin::common.wait');
                    $result['total']++;
                    /* 清除缓存 */
                    $result['filter'] = $arr['filter'];
                    clear_cache_files();
                    return response()->json($result);
                } else {
                    $result['mark'] = 1;
                    $result['content'] = __('admin::common.download_success');
                    return response()->json($result);
                }
            }
        } /* 处理Ajax调用 */
        elseif ($act == 'get_goods_fields') {
            $cat_id = request()->get('cat_id', 0);
            $goods_fields = my_array_merge(__('admin::common.custom'), $this->get_attributes($cat_id));
            return make_json_result($goods_fields);
        } elseif ($act == 'act_export_custom') {
            /* 检查输出列 */
            $custom_goods_export = request()->get('custom_goods_export', '');
            if (empty($custom_goods_export)) {
                return sys_msg(__('admin::goods_export.custom_goods_field_not_null'), 1, [], false);
            }

            /* 检查权限 */
            admin_priv('goods_export');

            $zip = new Phpzip;

            $where = $this->goodsExportManageService->getExportWhereSql(request()->all());

            $sql = "SELECT g.*, b.brand_name as brandname " .
                " FROM " . $this->dsc->table('goods') . " AS g LEFT JOIN " . $this->dsc->table('brand') . " AS b " .
                "ON g.brand_id = b.brand_id" . $where;

            $res = $this->db->query($sql);

            $goods_fields = explode(',', $custom_goods_export);
            $goods_field_name = $this->goodsExportManageService->setGoodsFieldName($goods_fields, __('admin::common.custom'));

            /* csv文件数组 */
            $goods_field_value = [];
            foreach ($goods_fields as $field) {
                if ($field == 'market_price' || $field == 'shop_price' || $field == 'integral' || $field == 'goods_weight' || $field == 'goods_number' || $field == 'warn_number' || $field == 'is_best' || $field == 'is_new' || $field == 'is_hot') {
                    $goods_field_value[$field] = 0;
                } elseif ($field == 'is_on_sale' || $field == 'is_alone_sale' || $field == 'is_real') {
                    $goods_field_value[$field] = 1;
                } else {
                    $goods_field_value[$field] = '""';
                }
            }

            $content = '"' . implode('","', $goods_field_name) . "\"\n";
            foreach ($res as $row) {
                $goods_value = $goods_field_value;
                isset($goods_value['goods_name']) && ($goods_value['goods_name'] = '"' . $row['goods_name'] . '"');
                isset($goods_value['goods_sn']) && ($goods_value['goods_sn'] = '"' . $row['goods_sn'] . '"');
                isset($goods_value['brand_name']) && ($goods_value['brand_name'] = $row['brandname']);
                isset($goods_value['market_price']) && ($goods_value['market_price'] = $row['market_price']);
                isset($goods_value['shop_price']) && ($goods_value['shop_price'] = $row['shop_price']);
                isset($goods_value['cost_price']) && ($goods_value['cost_price'] = $row['cost_price']);
                isset($goods_value['integral']) && ($goods_value['integral'] = $row['integral']);
                isset($goods_value['original_img']) && ($goods_value['original_img'] = '"' . $row['original_img'] . '"');
                isset($goods_value['keywords']) && ($goods_value['keywords'] = '"' . $row['keywords'] . '"');
                isset($goods_value['goods_brief']) && ($goods_value['goods_brief'] = '"' . $this->goodsExportManageService->replaceSpecialChar($row['goods_brief']) . '"');
                isset($goods_value['goods_desc']) && ($goods_value['goods_desc'] = '"' . $this->goodsExportManageService->replaceSpecialChar($row['goods_desc']) . '"');
                isset($goods_value['goods_weight']) && ($goods_value['goods_weight'] = $row['goods_weight']);
                isset($goods_value['goods_number']) && ($goods_value['goods_number'] = $row['goods_number']);
                isset($goods_value['warn_number']) && ($goods_value['warn_number'] = $row['warn_number']);
                isset($goods_value['is_best']) && ($goods_value['is_best'] = $row['is_best']);
                isset($goods_value['is_new']) && ($goods_value['is_new'] = $row['is_new']);
                isset($goods_value['is_hot']) && ($goods_value['is_hot'] = $row['is_hot']);
                isset($goods_value['is_on_sale']) && ($goods_value['is_on_sale'] = $row['is_on_sale']);
                isset($goods_value['is_alone_sale']) && ($goods_value['is_alone_sale'] = $row['is_alone_sale']);
                isset($goods_value['is_real']) && ($goods_value['is_real'] = $row['is_real']);

                $res = GoodsAttr::select('attr_id', 'attr_value')->where('goods_id', $row['goods_id']);
                $query = BaseRepository::getToArrayGet($res);
                foreach ($query as $attr) {
                    if (in_array($attr['attr_id'], $goods_fields)) {
                        $goods_value[$attr['attr_id']] = '"' . $attr['attr_value'] . '"';
                    }
                }

                $content .= implode(",", $goods_value) . "\n";

                /* 压缩图片 */
                if (!empty($row['goods_img']) && is_file(storage_public($row['goods_img']))) {
                    $zip->add_file(file_get_contents(storage_public($row['goods_img'])), $row['goods_img']);
                }
            }
            $charset = request()->get('charset_custom', 'UTF8');
            $zip->add_file(dsc_iconv(EC_CHARSET, $charset, $content), 'goods_list.csv');

            $filename = "goods_list.zip";
            return response()->streamDownload(function () use ($zip) {
                echo $zip->file();
            }, $filename);
        } elseif ($act == 'get_goods_list') {
            $filters = dsc_decode(request()->get('JSON', ''));
            $arr = get_goods_list($filters);
            $opt = [];

            foreach ($arr as $key => $val) {
                $opt[] = ['goods_id' => $val['goods_id'],
                    'goods_name' => $val['goods_name']
                ];
            }
            return make_json_result($opt);
        } elseif ($act == 'act_export_taobao V4.6') {
            /* 检查权限 */
            admin_priv('goods_export');

            $zip = new Phpzip;

            $where = $this->goodsExportManageService->getExportWhereSql(request()->all());

            $goods_class = request()->get('goods_class', 0);
            $post_express = floatval(request()->get('post_express', 0));
            $express = floatval(request()->get('express', 0));
            $ems = floatval(request()->get('ems', 0));

            $shop_province = '""';
            $shop_city = '""';
            if (config('shop.shop_province') || config('shop.shop_city')) {
                $region_id_attr = BaseRepository::getExplode(config('shop.shop_province') . "',  '" . config('shop.shop_city'));
                $res = Region::select('region_id', 'region_name')->whereIn('region_id', $region_id_attr);
                $arr = BaseRepository::getToArrayGet($res);
                if ($arr) {
                    if (count($arr) == 1) {
                        if ($arr[0]['region_id'] == config('shop.shop_province')) {
                            $shop_province = '"' . $arr[0]['region_name'] . '"';
                        } else {
                            $shop_city = '"' . $arr[0]['region_name'] . '"';
                        }
                    } else {
                        if ($arr[0]['region_id'] == config('shop.shop_province')) {
                            $shop_province = '"' . $arr[0]['region_name'] . '"';
                            $shop_city = '"' . $arr[1]['region_name'] . '"';
                        } else {
                            $shop_province = '"' . $arr[1]['region_name'] . '"';
                            $shop_city = '"' . $arr[0]['region_name'] . '"';
                        }
                    }
                }
            }

            $sql = "SELECT g.goods_id, g.goods_name, g.shop_price, g.goods_number, g.goods_desc, g.goods_img " .
                " FROM " . $this->dsc->table('goods') . " AS g " . $where;

            $res = $this->db->query($sql);

            /* csv文件数组 */
            $goods_value = ['goods_name' => '', 'goods_class' => $goods_class, 'shop_class' => 0, 'new_level' => 0, 'province' => $shop_province, 'city' => $shop_city, 'sell_type' => '"b"', 'shop_price' => 0, 'add_price' => 0, 'goods_number' => 0, 'die_day' => 14, 'load_type' => 1, 'post_express' => $post_express, 'ems' => $ems, 'express' => $express, 'pay_type' => '', 'allow_alipay' => '', 'invoice' => 0, 'repair' => 0, 'resend' => 1, 'is_store' => 0, 'window' => 0, 'add_time' => '"1980-1-1  0:00:00"', 'story' => '', 'goods_desc' => '', 'goods_img' => '', 'goods_attr' => '', 'group_buy' => '', 'group_buy_num' => '', 'template' => 0, 'discount' => 0, 'modify_time' => '"2011-5-1  0:00:00"', 'upload_status' => 100, 'img_status' => 1, 'img_status' => '', 'rebate_proportion' => 0, 'new_goods_img' => '', 'video' => '', 'marketing_property_mix' => '', 'user_input_ID_numbers' => '', 'input_user_name_value' => '', 'sellers_code' => '', 'another_of_marketing_property' => '', 'charge_type' => '0', 'treasure_number' => '', 'ID_number' => '',];

            $content = implode("\t", __("admin::common.taobao46")) . "\n";

            foreach ($res as $row) {

                /* 压缩图片 */
                if (!empty($row['goods_img']) && is_file(storage_public($row['goods_img']))) {
                    $row['new_goods_img'] = preg_replace("/(^images\/)+(.*)(.gif|.jpg|.jpeg|.png)$/", "\${2}.tbi", $row['goods_img']);
                    $new_goods_img = storage_public("images/" . $row['new_goods_img']);
                    @copy(storage_public($row['goods_img']), $new_goods_img);
                    if (is_file($new_goods_img)) {
                        $zip->add_file(file_get_contents($new_goods_img), $row['new_goods_img']);
                        unlink($new_goods_img);
                    }
                }
                $goods_value['goods_name'] = '"' . $row['goods_name'] . '"';
                $goods_value['shop_price'] = $row['shop_price'];
                $goods_value['goods_number'] = $row['goods_number'];
                $goods_value['goods_desc'] = $this->goodsExportManageService->replaceSpecialChar($row['goods_desc']);
                if (!empty($row['new_goods_img'])) {
                    $row['new_goods_img'] = str_ireplace('/', '\\', $row['new_goods_img'], $row['new_goods_img']);
                    $row['new_goods_img'] = str_ireplace('.tbi', '', $row['new_goods_img'], $row['new_goods_img']);
                    $goods_value['new_goods_img'] = '"' . $row['new_goods_img'] . ':0:0:|;' . '"';
                }

                $content .= implode("\t", $goods_value) . "\n";
            }
            if (EC_CHARSET != 'utf-8') {
                $content = dsc_iconv(EC_CHARSET, 'utf-8', $content);
            }
            $zip->add_file("\xFF\xFE" . $this->goodsExportManageService->utf82u2($content), 'goods_list.csv');

            $filename = "goods_list.zip";
            return response()->streamDownload(function () use ($zip) {
                echo $zip->file();
            }, $filename);
        }
    }
}
