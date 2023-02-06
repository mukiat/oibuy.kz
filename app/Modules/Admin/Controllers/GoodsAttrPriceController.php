<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Attribute;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GoodsType;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Goods\GoodsAttrPriceManageService;

/**
 * 属性价格批量上传 修改
 */
class GoodsAttrPriceController extends InitController
{
    protected $dscRepository;
    protected $goodsAttrPriceManageService;

    public function __construct(
        DscRepository $dscRepository,
        GoodsAttrPriceManageService $goodsAttrPriceManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->goodsAttrPriceManageService = $goodsAttrPriceManageService;
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
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);
            $goods_type = request()->get('goods_type', '');

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
            $this->smarty->assign('goods_type', $goods_type);

            $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
            $this->smarty->assign('goods_name', $goods_name);

            /* 参数赋值 */
            $ur_here = __('admin::common.13_batch_add');
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_attr_price_batch.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量上传：处理
        /*------------------------------------------------------ */

        elseif ($act == 'upload') {
            /* 检查权限 */
            admin_priv('goods_manage');

            //ecmoban模板堂 --zhuo start 仓库
            if ($_FILES['file']['name']) {
                $line_number = 0;
                $arr = [];
                $goods_list = [];
                $field_list = array_keys(__('admin::goods_attr_price.upload_area_attr')); // 字段列表
                $charset = 'GB2312';
                $data = file($_FILES['file']['tmp_name']);

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

                    // 更新属性价格
                    for ($i = 0; $i < count($goods_list); $i++) {
                        $goods_id = Goods::where('goods_sn', $goods_list[$i]['goods_sn'])->value('goods_id');

                        $goods_attr = explode('-', $goods_list[$i]['goods_attr']);
                        $attr_value = $goods_attr[2];

                        $res = Attribute::where('attr_name', $goods_attr[1]);
                        $cat_name = $goods_attr[0];
                        $res = $res->whereHasIn('goodsType', function ($query) use ($cat_name) {
                            $query->where('cat_name', $cat_name);
                        });
                        $attr_id = $res->value('attr_id');

                        $goods_attr_id = GoodsAttr::where('goods_id', $goods_id)
                            ->where('attr_id', $attr_id)
                            ->where('attr_value', $attr_value)
                            ->value('goods_attr_id');
                        if ($goods_attr_id > 0) {
                            $attr_res['attr_sort'] = $goods_list[$i]['attr_sort'];
                            $attr_res['attr_price'] = $goods_list[$i]['attr_price'];

                            GoodsAttr::where('goods_attr_id', $goods_attr_id)->update($attr_res);
                        } else {
                            $goods_type_id = GoodsType::where('cat_name', $goods_attr[0])->value('cat_id');
                            $data = ['goods_type' => $goods_type_id];
                            Goods::where('goods_id', $goods_id)->update($data);

                            $attr_res['goods_id'] = $goods_id;
                            $attr_res['attr_id'] = $attr_id;
                            $attr_res['attr_value'] = $attr_value;

                            $attr_res['attr_sort'] = $goods_list[$i]['attr_sort'];
                            $attr_res['attr_price'] = $goods_list[$i]['attr_price'];

                            GoodsAttr::insert($attr_res);
                        }
                    }

                    $link[] = ['href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=', 'text' => __('admin::common.03_goods_edit')];
                    $link[] = ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
                    return sys_msg(__('admin::goods_area_batch.save_products'), 0, $link);
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 下载文件
        /*------------------------------------------------------ */

        elseif ($act == 'download') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
            $goods_type = isset($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : '';
            // 文件标签
            // Header("Content-type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            Header("Content-Disposition: attachment; filename=goods_attr_batch.csv");
            $charset = request()->get('charset', '');
            // 下载
            if ($charset != config('shop.lang')) {
                $lang_file = '../languages/' . $charset . '/admin/goods_area_attr_batch.php';
                if (file_exists($lang_file)) {
                    $upload_area_attr = __('admin::goods_attr_price.upload_area_attr');
                    unset($upload_area_attr);
                    require($lang_file);
                }
            }
            if (__('admin::goods_attr_price.upload_area_attr')) {
                /* 创建字符集转换对象 */
                if ($charset == 'zh-CN' || $charset == 'zh-TW') {
                    $to_charset = $charset == 'zh-CN' ? 'GB2312' : 'BIG5';

                    if (config('shop.goods_attr_price') == 1) {
                        $data = join(',', __('admin::goods_attr_price.upload_goods_attr')) . "\t\n";

                        $res = $this->goodsAttrPriceManageService->getGoodsLattrList($goods_id, $goods_type);

                        if (count($res) > 0) {
                            for ($i = 0; $i < count($res); $i++) {
                                $data .= join(',', [$res[$i]['goods_sn'], $res[$i]['attr_all_value'], $res[$i]['attr_price'], $res[$i]['attr_price']]) . "\t\n";
                            }
                        }
                    } else {
                        $data = join(',', __('admin::goods_attr_price.upload_area_attr')) . "\t\n";

                        $res = $this->goodsAttrPriceManageService->getGoodsLattrList($goods_id, $goods_type);

                        if (count($res) > 0) {
                            for ($i = 0; $i < count($res); $i++) {
                                $data .= join(',', [$res[$i]['goods_sn'], $res[$i]['attr_all_value'], $res[$i]['attr_sort'], $res[$i]['attr_price']]) . "\t\n";
                            }
                        }
                    }

                    echo dsc_iconv(EC_CHARSET, $to_charset, $data);
                } else {
                    echo join(',', __('admin::goods_attr_price.upload_area_attr'));
                }
            } else {
                echo 'error: ' . __('admin::goods_attr_price.upload_area_attr') . ' not exists';
            }
        }
    }
}
