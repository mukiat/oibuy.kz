<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Goods;
use App\Models\RegionWarehouse;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 * 商品批量上传、修改
 */
class GoodsWarehouseBatchController extends InitController
{
    protected $dscRepository;

    /**
     * GoodsWarehouseBatchController constructor.
     * @param DscRepository $dscRepository
     */
    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        load_helper('goods', 'admin');
        $act = request()->get('act', '');
        $upload_warehouse = __('admin::goods_warehouse_batch.upload_warehouse');

        /* ------------------------------------------------------ */
        //-- 批量上传
        /* ------------------------------------------------------ */

        if ($act == 'add') {
            /* 检查权限 */
            admin_priv('goods_manage');
            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'warehouse_batch']);
            $goods_id = request()->get('goods_id', 0);

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

            $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
            $goods_name = $goods_name ? $goods_name : '';

            $this->smarty->assign('goods_name', $goods_name);

            /* 参数赋值 */
            $ur_here = __('admin::common.13_batch_add');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */
            return $this->smarty->display('goods_warehouse_batch_add.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 批量上传：处理
        /* ------------------------------------------------------ */
        elseif ($act == 'upload') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);

            $file = request()->file('file');

            //ecmoban模板堂 --zhuo start 仓库
            if ($file && $file->isValid()) {
                $line_number = 0;
                $goods_list = [];
                $field_list = array_keys($upload_warehouse); // 字段列表
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

                    $goods_list = get_goods_bacth_warehouse_list($goods_list);
                    get_insert_bacth_warehouse($goods_list);

                    $link[] = ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
                    if ($goods_id) {
                        $link[] = ['href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=', 'text' => __('admin::common.03_goods_edit')];
                        $link[] = ['href' => 'goods_warehouse_batch.php?act=add&goods_id=' . $goods_id, 'text' => __('admin::common.back_warehouse_batch_list')];
                    } else {
                        $link[] = ['href' => 'goods_warehouse_batch.php?act=add', 'text' => __('admin::common.back_warehouse_batch_list')];
                    }

                    return sys_msg(__('admin::goods_warehouse_attr_batch.save_products'), 0, $link);
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 下载文件
        /* ------------------------------------------------------ */
        elseif ($act == 'download') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);

            // 文件标签
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            Header("Content-Disposition: attachment; filename=warehouse_info_list.csv");

            $lang = request()->get('lang', '');

            // 下载
            if ($lang != config('shop.lang')) {
                $lang_file = app_path('Modules/Admin/Languages/zh-CN/goods_warehouse_batch.php');

                if (file_exists($lang_file)) {
                    unset($upload_warehouse);
                    require($lang_file);
                }
            }
            // 下载
            if ($lang != config('shop.lang')) {
                $upload_warehouse = __('admin::goods_warehouse_batch.upload_warehouse', [], $lang);
            }

            if ($upload_warehouse) {
                /* 创建字符集转换对象 */
                if ($lang == 'zh-CN' || $lang == 'zh-TW') {
                    $to_charset = $lang == 'zh-CN' ? 'GB2312' : 'BIG5';
                    $data = join(',', $upload_warehouse) . "\n";

                    $goods = Goods::where('goods_id', $goods_id);
                    $goods = BaseRepository::getToArrayFirst($goods);

                    $area_info = RegionWarehouse::where('region_type', 0);
                    $area_info = BaseRepository::getToArrayGet($area_info);
                    $area_info = $this->get_list_download($goods, $area_info);

                    if (count($area_info) > 0) {
                        for ($i = 0; $i < count($area_info); $i++) {
                            $data .= $area_info[$i]['goods_id'] . ",";
                            $data .= $area_info[$i]['goods_name'] . ",";
                            $data .= $area_info[$i]['area_name'] . ",";
                            $data .= $area_info[$i]['number'] . ",";
                            $data .= $area_info[$i]['price'] . ",";
                            $data .= $area_info[$i]['promote_price'] . ",";
                            $data .= $area_info[$i]['give_integral'] . ",";
                            $data .= $area_info[$i]['rank_integral'] . ",";
                            $data .= $area_info[$i]['pay_integral'] . "\n";
                        }
                    }

                    echo dsc_iconv(EC_CHARSET, $to_charset, $data);
                } else {
                    echo join(',', $upload_warehouse);
                }
            } else {
                echo 'error: ' . $upload_warehouse . ' not exists';
            }
        }
    }

    private function get_list_download($goods = [], $area_info = [])
    {
        if (count($area_info) > 0) {
            $arr = [];

            for ($i = 0; $i < count($area_info); $i++) {
                $arr[$i]['goods_id'] = $goods['goods_id'] ?? 0;
                $arr[$i]['goods_name'] = $goods['goods_name'] ?? '';
                $arr[$i]['area_name'] = $area_info[$i]['region_name'];
                $arr[$i]['number'] = '';
                $arr[$i]['minnumber'] = '';
                $arr[$i]['price'] = '';
                $arr[$i]['promote_price'] = '';
                $arr[$i]['give_integral'] = '';
                $arr[$i]['rank_integral'] = '';
                $arr[$i]['pay_integral'] = '';
            }

            return $arr;
        } else {
            return [];
        }
    }
}
