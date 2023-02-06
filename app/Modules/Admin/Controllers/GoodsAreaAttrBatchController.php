<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Goods;
use App\Models\RegionWarehouse;
use App\Models\WarehouseAreaAttr;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Goods\GoodsAreaAttrBatchManageService;
use App\Services\Merchant\MerchantCommonService;

/**
 * 商品批量上传、修改
 */
class GoodsAreaAttrBatchController extends InitController
{
    protected $dscRepository;
    protected $merchantCommonService;
    protected $goodsAreaAttrBatchManageService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        GoodsAreaAttrBatchManageService $goodsAreaAttrBatchManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsAreaAttrBatchManageService = $goodsAreaAttrBatchManageService;
    }

    public function index()
    {
        load_helper('goods', 'admin');
        $this->smarty->assign('action_type', "goods_warehouse_batch");
        $act = request()->get('act', '');
        /*------------------------------------------------------ */
        //-- 批量上传
        /*------------------------------------------------------ */

        if ($act == 'add') {
            $this->smarty->assign('primary_cat', __('admin::common.18_batch_manage'));
            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'area_attr_batch']);

            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);
            $attr_name = request()->get('attr_name', '');

            if ($goods_id > 0) {
                $this->smarty->assign('action_link', ['text' => __('admin::common.goto_goods'), 'href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=']);
            }

            $lang_list = [
                'UTF8' => __('admin::common.charset.utf8'),
                'GB2312' => __('admin::common.charset.zh_cn'),
                'BIG5' => __('admin::common.charset.zh_tw'),
            ];

            /* 取得可选语言 */
            $download_list = $this->dscRepository->getDdownloadTemplate(resource_path('lang'));

            $this->smarty->assign('lang_list', $lang_list);
            $this->smarty->assign('download_list', $download_list);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('attr_name', $attr_name);

            $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
            $this->smarty->assign('goods_name', $goods_name);

            /* 参数赋值 */
            $ur_here = __('admin::common.13_batch_add');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */


            $this->smarty->assign('current', 'attr_batch');
            return $this->smarty->display('goods_area_attr_batch.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量上传：处理
        /*------------------------------------------------------ */

        elseif ($act == 'upload') {
            /* 检查权限 */
            admin_priv('goods_manage');
            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'area_attr_batch']);

            $goods_list = [];
            $file = request()->file('file');
            //ecmoban模板堂 --zhuo start 仓库
            if ($file && $file->isValid()) {
                $line_number = 0;
                $field_list = array_keys(__('admin::goods_atea_attr_batch.upload_area_attr')); // 字段列表
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

                    $goods_list = get_goods_bacth_area_attr_list($goods_list);
                }
            }

            session([
                'goods_list' => $goods_list
            ]);

            $this->smarty->assign('full_page', 2);
            $this->smarty->assign('page', 1);

            /* 显示模板 */
            $this->smarty->assign('ur_here', __('admin::common.13_batch_add'));
            return $this->smarty->display('goods_area_attr_batch_add.dwt');
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
                $other['area_id'] = $result['list']['area_id'];
                $other['goods_attr_id'] = $result['list']['goods_attr_id'];
                $other['attr_price'] = $result['list']['attr_price'];

                //查询数据是否已经存在;
                $res = WarehouseAreaAttr::where('goods_id', $result['list']['goods_id'])
                    ->where('area_id', $result['list']['area_id'])
                    ->where('goods_attr_id', $result['list']['goods_attr_id'])->count();

                if ($res > 0) {
                    $res = WarehouseAreaAttr::whereRaw(1);
                    if (empty($result['list']['goods_id'])) {
                        $res = $res->where('admin_id', session('admin_id'));
                    }

                    $res->where('goods_id', $result['list']['goods_id'])
                        ->where('area_id', $result['list']['area_id'])
                        ->where('goods_attr_id', $result['list']['goods_attr_id'])
                        ->update($other);

                    $result['status_lang'] = '<span style="color: red;">' . __('admin::common.upload_date_success') . '</span>';
                } else {
                    $other['admin_id'] = session('admin_id');

                    WarehouseAreaAttr::insert($other);
                    $result['status_lang'] = '<span style="color: red;">' . __('admin::common.upload_date_success') . '</span>';
                }
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 下载文件
        /*------------------------------------------------------ */

        elseif ($act == 'download') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);
            $attr_name = request()->get('attr_name', '');

            $goods_attr_list = $this->goodsAreaAttrBatchManageService->getGoodsAttrList($goods_id);

            // 文件标签
            // Header("Content-type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            Header("Content-Disposition: attachment; filename=area_attr_info_list" . $goods_id . ".csv");
            $charset = request()->get('charset', '');
            // 下载
            if ($charset != config('shop.lang')) {
                $lang_file = '../languages/' . $charset . '/admin/goods_area_attr_batch.php';
                if (file_exists($lang_file)) {
                    $upload_area_attr = __('admin::goods_atea_attr_batch.upload_area_attr');
                    unset($upload_area_attr);
                    require($lang_file);
                }
            }
            if (__('admin::goods_atea_attr_batch.upload_area_attr')) {
                /* 创建字符集转换对象 */
                if ($charset == 'zh-CN' || $charset == 'zh-TW') {
                    $to_charset = $charset == 'zh-CN' ? 'GB2312' : 'BIG5';
                    $data = join(',', __('admin::goods_atea_attr_batch.upload_area_attr')) . "\t\n";

                    $area_date = ['region_name'];
                    $where = "region_type = 1";
                    $area_info = RegionWarehouse::select('region_name')->where('region_type', 1);
                    $area_info = BaseRepository::getToArrayGet($area_info);

                    if ($goods_id) {
                        $goods_info = get_admin_goods_info($goods_id);
                    } else {
                        $adminru = get_admin_ru_id();

                        $goods_info['user_id'] = $adminru['ru_id'];
                        $goods_info['shop_name'] = $this->merchantCommonService->getShopName($adminru['ru_id'], 1);
                    }

                    if (count($area_info) > 0) {
                        for ($i = 0; $i < count($area_info); $i++) {
                            $data .= "" . ',';
                            $data .= "" . ',';
                            $data .= "" . ',';
                            $data .= "" . ',';
                            $data .= "" . ',';
                            $data .= "" . ',';
                            $data .= "" . "\t\n";

                            if ($goods_attr_list) {
                                for ($j = 0; $j < count($goods_attr_list); $j++) {
                                    $data .= $goods_id . ',';
                                    $data .= $goods_info['goods_name'] . ',';
                                    $data .= $goods_info['shop_name'] . ',';
                                    $data .= $goods_info['user_id'] . ',';
                                    $data .= $area_info[$i]['region_name'] . ',';

                                    $attr_price = !empty($goods_attr_list[$j]['get_goods_warehouse_area_attr']['attr_price']) ? $goods_attr_list[$j]['get_goods_warehouse_area_attr']['attr_price'] : 0;

                                    $data .= $goods_attr_list[$j]['attr_value'] . ',';
                                    $data .= $attr_price . "\t\n";
                                }
                            }
                        }
                    }

                    echo dsc_iconv(EC_CHARSET, $to_charset, $data);
                } else {
                    echo join(',', __('admin::goods_atea_attr_batch.upload_area_attr'));
                }
            } else {
                echo 'error: ' . __('admin::goods_atea_attr_batch.upload_area_attr') . ' not exists';
            }
        }
    }
}
