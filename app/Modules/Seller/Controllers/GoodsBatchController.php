<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Image;
use App\Models\Brand;
use App\Models\Goods;
use App\Models\GoodsGallery;
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
        load_helper('goods', 'seller');
        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "goods");

        $adminru = get_admin_ru_id();
        $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
        $this->smarty->assign('current', basename(PHP_SELF, '.php'));


        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '15_batch_edit']);

        /* ------------------------------------------------------ */
        //-- 批量上传
        /* ------------------------------------------------------ */

        if ($_REQUEST['act'] == 'add') {
            /* 检查权限 */
            admin_priv('goods_batch');

            //页面分菜单 by wu start
            $tab_menu = [];
            if (admin_priv('goods_batch')) {
                $tab_menu[] = ['curr' => 0, 'text' => $GLOBALS['_LANG']['15_batch_edit'], 'href' => 'goods_batch.php?act=select'];
                $tab_menu[] = ['curr' => 1, 'text' => $GLOBALS['_LANG']['13_batch_add'], 'href' => 'goods_batch.php?act=add'];
            }
            if (admin_priv('goods_export')) {
                $tab_menu[] = ['curr' => 0, 'text' => $GLOBALS['_LANG']['14_goods_export'], 'href' => 'goods_export.php?act=goods_export'];
            }
            $this->smarty->assign('tab_menu', $tab_menu);
            //页面分菜单 by wu end

            $this->smarty->assign('current', '15_batch_edit');

            $lang_list = [
                'UTF8' => $GLOBALS['_LANG']['charset']['utf8'],
                'GB2312' => $GLOBALS['_LANG']['charset']['zh_cn'],
                'BIG5' => $GLOBALS['_LANG']['charset']['zh_tw'],
            ];

            /* 取得可选语言 */
            $download_list = $this->dscRepository->getDdownloadTemplate(resource_path('lang'));

            $data_format_array = [
                'dscmall' => $GLOBALS['_LANG']['export_dscmall'],
            ];
            $this->smarty->assign('data_format', $data_format_array);
            $this->smarty->assign('lang_list', $lang_list);
            $this->smarty->assign('download_list', $download_list);

            set_default_filter(0, 0, $adminru['ru_id']); //by wu
            set_seller_default_filter(0, 0, $adminru['ru_id']); //by wu

            /* 参数赋值 */
            $ur_here = $GLOBALS['_LANG']['13_batch_add'];
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_batch_add.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量上传：处理
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'upload') {
            /* 检查权限 */
            admin_priv('goods_batch');

            $max_id = $this->db->getOne("SELECT MAX(goods_id) FROM " . $this->dsc->table('goods'));

            $generate_goods_sn = $this->goodsManageService->generate_goods_sn($max_id);

            $sn_prefix = $GLOBALS['_CFG']['sn_prefix'];
            if ($sn_prefix) {
                $generate_goods_sn = str_replace($sn_prefix, '', $generate_goods_sn);
            }

            $generate_goods_sn = intval($generate_goods_sn);

            /* 将文件按行读入数组，逐行进行解析 */
            $line_number = 0;
            $goods_list = [];
            $field_list = array_keys($GLOBALS['_LANG']['upload_goods']); // 字段列表
            $data = file($_FILES['file']['tmp_name']);
            $data_cat = e(request()->get('data_cat', ''));
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
            $title_list = $GLOBALS['_LANG']['upload_goods'];
            unset($title_list['original_img']);
            unset($title_list['goods_img']);
            unset($title_list['goods_thumb']);
            unset($title_list['keywords']);
            unset($title_list['goods_brief']);
            unset($title_list['goods_desc']);
            unset($title_list['is_best']);
            unset($title_list['is_new']);
            unset($title_list['is_hot']);
            unset($title_list['is_on_sale']);
            unset($title_list['is_alone_sale']);
            unset($title_list['warn_number']);

            $this->smarty->assign('title_list', $title_list);

            // 显示的字段列表
            $this->smarty->assign('field_show', ['goods_name' => true, 'goods_sn' => true, 'brand_name' => true, 'market_price' => true, 'shop_price' => true]);

            /* 参数赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['goods_upload_confirm']);

            // 批量上传选择分类传参
            $cat_id = request()->get('cat', 0);
            $this->smarty->assign('cat_id', $cat_id);
            $user_cat = request()->get('user_cat', 0);
            $this->smarty->assign('user_cat', $user_cat);


            /* 显示模板 */

            return $this->smarty->display('goods_batch_confirm.dwt');
        }

        /*------------------------------------------------------ */
        //-- 异步处理上传
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'creat') {
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

                if ($result['list']) {
                    $result['list']['user_id'] = $adminru['ru_id'];

                    $goods = [
                        $result['list']
                    ];
                    $this->batchGoods($goods, $adminru);
                }

                $result['filter_page'] = $goods_list['filter']['page'] - 1;
            }


            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 批量修改：选择商品
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'select') {
            /* 检查权限 */
            admin_priv('goods_batch');

            $this->smarty->assign('current', '15_batch_edit');

            //页面分菜单 by wu start
            $tab_menu = [];
            if (admin_priv('goods_batch')) {
                $tab_menu[] = ['curr' => 1, 'text' => $GLOBALS['_LANG']['15_batch_edit'], 'href' => 'goods_batch.php?act=select'];
                $tab_menu[] = ['curr' => 0, 'text' => $GLOBALS['_LANG']['13_batch_add'], 'href' => 'goods_batch.php?act=add'];
            }
            if (admin_priv('goods_export')) {
                $tab_menu[] = ['curr' => 0, 'text' => $GLOBALS['_LANG']['14_goods_export'], 'href' => 'goods_export.php?act=goods_export'];
            }
            $this->smarty->assign('tab_menu', $tab_menu);
            //页面分菜单 by wu end

            set_default_filter(0, 0, $adminru['ru_id']); //设置默认筛选

            /* 参数赋值 */
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $ur_here = $GLOBALS['_LANG']['15_batch_edit'];
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_batch_select.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量修改：修改
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'edit') {
            /* 检查权限 */
            admin_priv('goods_batch');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);

            /* 取得商品列表 */
            if ($_POST['select_method'] == 'cat') {
                $goods_ids = $_POST['goods_ids'];
            } else {
                $goods_sns = str_replace("\n", ',', str_replace("\r", '', $_POST['sn_list']));
                $sql = "SELECT DISTINCT goods_id FROM " . $this->dsc->table('goods') .
                    " WHERE goods_sn " . db_create_in($goods_sns) . " AND user_id = '$adminru[ru_id]' ";
                $col = $this->db->getCol($sql);
                $goods_ids = $col ? join(',', $col) : '';
            }

            $goods_list = [];
            if($goods_ids){
                $sql = "SELECT DISTINCT goods_id, goods_sn, goods_name, market_price, shop_price, goods_number, integral, give_integral, brand_id, is_real,model_attr FROM " . $this->dsc->table('goods') . " WHERE goods_id " . db_create_in($goods_ids) . " AND user_id = '$adminru[ru_id]' ";

                $goods_list = $this->db->getAll($sql);

                /* 获取商品对应的品牌列表 by wu */
                foreach ($goods_list as $key => $val) {
                    $goods_list[$key]['brand_list'] = get_brand_list($val['goods_id']);
                    /* 取编辑商品的货品列表 */
                    if ($val['model_attr'] == 1) {
                        $table_products = "products_warehouse";
                    } elseif ($val['model_attr'] == 2) {
                        $table_products = "products_area";
                    } else {
                        $table_products = "products";
                    }
                    $sql = "SELECT * FROM " . $this->dsc->table($table_products) . "WHERE goods_id = '" . $val['goods_id'] . "'";
                    $product_list = $this->db->getAll($sql);

                    if (!empty($product_list)) {
                        $_product_list = [];
                        foreach ($product_list as $value) {
                            $goods_attr = product_goods_attr_list($value['goods_id']);
                            $_goods_attr_array = explode('|', $value['goods_attr']);
                            if (is_array($_goods_attr_array)) {
                                $_temp = [];
                                foreach ($_goods_attr_array as $_goods_attr_value) {
                                    $_temp[] = $goods_attr[$_goods_attr_value];
                                }
                                $value['goods_attr'] = implode('，', $_temp);
                            }

                            $_product_list[] = $value;
                        }
                        //释放资源
                        $goods_list[$key]['product_list'] = $_product_list;
                    }
                }
            }

            $this->smarty->assign('goods_list', $goods_list);

            /* 取得会员价格 */
            $member_price_list = [];
            $sql = "SELECT DISTINCT goods_id, user_rank, user_price FROM " . $this->dsc->table('member_price') . $where;
            $res = $this->db->query($sql);
            foreach ($res as $row) {
                $member_price_list[$row['goods_id']][$row['user_rank']] = $row['user_price'];
            }
            $this->smarty->assign('member_price_list', $member_price_list);

            /* 取得会员等级 */
            $sql = "SELECT rank_id, rank_name, discount " .
                "FROM " . $this->dsc->table('user_rank') .
                " ORDER BY discount DESC";
            $this->smarty->assign('rank_list', $this->db->getAll($sql));

            /* 取得品牌列表 */
            $this->smarty->assign('brand_list', get_brand_list());

            /* 赋值编辑方式 */
            $this->smarty->assign('edit_method', $_POST['edit_method']);

            /* 参数赋值 */
            $ur_here = $GLOBALS['_LANG']['15_batch_edit'];
            $this->smarty->assign('ur_here', $ur_here);

            /* 显示模板 */

            return $this->smarty->display('goods_batch_edit.dwt');
        }

        /*------------------------------------------------------ */
        //-- 批量修改：提交
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'update') {
            /* 检查权限 */
            admin_priv('goods_batch');
            $warehouse_id = !empty($_REQUEST['warehouse_id']) ? $_REQUEST['warehouse_id'] : [];
            $area_id = !empty($_REQUEST['area_id']) ? $_REQUEST['area_id'] : [];
            $grade_rank = get_seller_grade_rank($adminru['ru_id']);
            if ($_POST['edit_method'] == 'each') {

                $brand_id = request()->get('brand_id', []);

                // 循环更新每个商品
                if (!empty($_POST['goods_id'])) {
                    foreach ($_POST['goods_id'] as $goods_id) {

                        $goodsInfo = Goods::select('goods_id', 'goods_name', 'model_attr')->where('goods_id', $goods_id);
                        $goodsInfo = BaseRepository::getToArrayFirst($goodsInfo);

                        $goods_name = $goodsInfo['goods_name'] ?? '';
                        $model_attr = $goodsInfo['model_attr'] ?? 0;

                        //如果存在货品则处理货品
                        if (!empty($_POST['product_number'][$goods_id])) {
                            $_POST['goods_number'][$goods_id] = 0;
                            foreach ($_POST['product_number'][$goods_id] as $key => $value) {
                                if ($model_attr == 1) {
                                    $table_products = "products_warehouse";
                                    $table_where = " AND warehouse_id = '" . $warehouse_id[$key] . "'";
                                } elseif ($model_attr == 2) {
                                    $table_products = "products_area";
                                    $table_where = " AND area_id = '" . $area_id[$key] . "'";
                                } else {
                                    $table_products = "products";
                                    $table_where = '';
                                }
                                $sql = "UPDATE" . $this->dsc->table($table_products) . "SET product_number = '$value' WHERE goods_id = '$goods_id' AND product_id = " . $key . $table_where;
                                $this->db->query($sql);
                                $_POST['goods_number'][$goods_id] += $value;
                            }
                        }

                        //计算实际积分购买金额 start
                        $goods_info = get_table_date('goods', "goods_id='$goods_id'", ['shop_price', 'promote_price']);
                        if ($goods_info['promote_price'] > 0) {
                            if ($goods_info['promote_price'] < $goods_info['shop_price']) {
                                $shop_price = $goods_info['promote_price'];
                            } else {
                                $shop_price = $goods_info['shop_price'];
                            }
                        } else {
                            $shop_price = $goods_info['shop_price'];
                        }
                        $pay = floor($shop_price * $grade_rank['pay_integral']);
                        if ($_POST['integral'][$goods_id] > $pay) {
                            $_POST['integral'][$goods_id] = $pay;
                        }
                        //计算实际积分购买金额 end

                        if ($brand_id[$goods_id]) {
                            $goods['brand_id'] = intval($brand_id[$goods_id]);

                            $brandList = BrandDataHandleService::goodsBrand($goods['brand_id'], ['brand_id', 'brand_name']);
                            $brand_name = $brandList[$goods['brand_id']]['brand_name'] ?? '';

                            if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                                $goods_name = StrRepository::replaceFirst($goods_name, $brand_name);
                                $goods_name = StrRepository::replaceFirst($goods_name, config('app.replace_symbol'));
                                $goods_name = $brand_name . config('app.goods_symbol') . $goods_name;
                            }
                        }

                        //计算实际赠送消费积分 start
                        $goods_info = get_table_date('goods', "goods_id='$goods_id'", ['shop_price', 'promote_price']);
                        if ($goods_info['promote_price'] > 0) {
                            if ($goods_info['promote_price'] < $goods_info['shop_price']) {
                                $shop_price = $goods_info['promote_price'];
                            } else {
                                $shop_price = $goods_info['shop_price'];
                            }
                        } else {
                            $shop_price = $goods_info['shop_price'];
                        }
                        $give = floor($shop_price * $grade_rank['give_integral']);
                        if ($_POST['give_integral'][$goods_id] > $give) {
                            $_POST['give_integral'][$goods_id] = $give;
                        }
                        //计算实际赠送消费积分 end

                        // 更新商品
                        $goods = [
                            'goods_name' => $goods_name,
                            'market_price' => floatval($_POST['market_price'][$goods_id]),
                            'shop_price' => floatval($_POST['shop_price'][$goods_id]),
                            'integral' => intval($_POST['integral'][$goods_id]),
                            'give_integral' => intval($_POST['give_integral'][$goods_id]),
                            'goods_number' => intval($_POST['goods_number'][$goods_id]),
                            'brand_id' => intval($_POST['brand_id'][$goods_id]),
                            'last_update' => gmtime(),
                        ];
                        $this->db->autoExecute($this->dsc->table('goods'), $goods, 'UPDATE', "goods_id = '$goods_id'");

                        // 更新会员价格
                        if (!empty($_POST['rank_id'])) {
                            foreach ($_POST['rank_id'] as $rank_id) {
                                if (trim($_POST['member_price'][$goods_id][$rank_id]) == '') {
                                    /* 为空时不做处理 */
                                    continue;
                                }

                                $rank = [
                                    'goods_id' => $goods_id,
                                    'user_rank' => $rank_id,
                                    'user_price' => floatval($_POST['member_price'][$goods_id][$rank_id]),
                                ];
                                $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('member_price') . " WHERE goods_id = '$goods_id' AND user_rank = '$rank_id'";
                                if ($this->db->getOne($sql) > 0) {
                                    if ($rank['user_price'] < 0) {
                                        $this->db->query("DELETE FROM " . $this->dsc->table('member_price') . " WHERE goods_id = '$goods_id' AND user_rank = '$rank_id'");
                                    } else {
                                        $this->db->autoExecute($this->dsc->table('member_price'), $rank, 'UPDATE', "goods_id = '$goods_id' AND user_rank = '$rank_id'");
                                    }
                                } else {
                                    if ($rank['user_price'] >= 0) {
                                        $this->db->autoExecute($this->dsc->table('member_price'), $rank, 'INSERT');
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // 循环更新每个商品
                if (!empty($_POST['goods_id'])) {
                    foreach ($_POST['goods_id'] as $goods_id) {
                        // 更新商品
                        $goods = [];
                        if (trim($_POST['market_price'] != '')) {
                            $goods['market_price'] = floatval($_POST['market_price']);
                        }
                        if (trim($_POST['shop_price']) != '') {
                            $goods['shop_price'] = floatval($_POST['shop_price']);
                        }
                        if (trim($_POST['integral']) != '') {
                            $goods['integral'] = intval($_POST['integral']);
                            //计算实际积分购买金额 start
                            $goods_info = get_table_date('goods', "goods_id='$goods_id'", ['shop_price', 'promote_price']);
                            if (isset($goods['shop_price'])) {
                                $goods_info['shop_price'] = $goods['shop_price'];
                            }
                            if ($goods_info['promote_price'] > 0) {
                                if ($goods_info['promote_price'] < $goods_info['shop_price']) {
                                    $shop_price = $goods_info['promote_price'];
                                } else {
                                    $shop_price = $goods_info['shop_price'];
                                }
                            } else {
                                $shop_price = $goods_info['shop_price'];
                            }
                            $pay = floor($shop_price * $grade_rank['pay_integral']);
                            if ($goods['integral'] > $pay) {
                                $goods['integral'] = $pay;
                            }
                            //计算实际积分购买金额 end
                        }
                        if (trim($_POST['give_integral']) != '') {
                            $goods['give_integral'] = intval($_POST['give_integral']);
                            //计算实际赠送消费积分 start
                            $goods_info = get_table_date('goods', "goods_id='$goods_id'", ['shop_price', 'promote_price']);
                            if (isset($goods['shop_price'])) {
                                $goods_info['shop_price'] = $goods['shop_price'];
                            }
                            if ($goods_info['promote_price'] > 0) {
                                if ($goods_info['promote_price'] < $goods_info['shop_price']) {
                                    $shop_price = $goods_info['promote_price'];
                                } else {
                                    $shop_price = $goods_info['shop_price'];
                                }
                            } else {
                                $shop_price = $goods_info['shop_price'];
                            }
                            $give = floor($shop_price * $grade_rank['give_integral']);
                            if ($goods['give_integral'] > $give) {
                                $goods['give_integral'] = $give;
                            }
                            //计算实际赠送消费积分 end
                        }
                        if (trim($_POST['goods_number']) != '') {
                            $goods['goods_number'] = intval($_POST['goods_number']);
                        }
                        if ($_POST['brand_id'] > 0) {
                            $goods['brand_id'] = $_POST['brand_id'];
                        }
                        if (!empty($goods)) {
                            $this->db->autoExecute($this->dsc->table('goods'), $goods, 'UPDATE', "goods_id = '$goods_id'");
                        }

                        // 更新会员价格
                        if (!empty($_POST['rank_id'])) {
                            foreach ($_POST['rank_id'] as $rank_id) {
                                if (trim($_POST['member_price'][$rank_id]) != '') {
                                    $rank = [
                                        'goods_id' => $goods_id,
                                        'user_rank' => $rank_id,
                                        'user_price' => floatval($_POST['member_price'][$rank_id]),
                                    ];

                                    $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('member_price') . " WHERE goods_id = '$goods_id' AND user_rank = '$rank_id'";
                                    if ($this->db->getOne($sql) > 0) {
                                        if ($rank['user_price'] < 0) {
                                            $this->db->query("DELETE FROM " . $this->dsc->table('member_price') . " WHERE goods_id = '$goods_id' AND user_rank = '$rank_id'");
                                        } else {
                                            $this->db->autoExecute($this->dsc->table('member_price'), $rank, 'UPDATE', "goods_id = '$goods_id' AND user_rank = '$rank_id'");
                                        }
                                    } else {
                                        if ($rank['user_price'] >= 0) {
                                            $this->db->autoExecute($this->dsc->table('member_price'), $rank, 'INSERT');
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
            return sys_msg($GLOBALS['_LANG']['batch_edit_ok'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 下载文件
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'download') {
            /* 检查权限 */
            admin_priv('goods_batch');

            // 文件标签
            // Header("Content-type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=goods_list.csv");

            $charset = request()->get('charset', '');

            // 下载
            if (isset($GLOBALS['_LANG']['upload_goods'])) {
                /* 创建字符集转换对象 */
                if ($charset == 'zh-CN' || $charset == 'zh-TW') {
                    $to_charset = $charset == 'zh-CN' ? 'GB2312' : 'BIG5';
                    echo dsc_iconv(EC_CHARSET, $to_charset, join(',', $GLOBALS['_LANG']['upload_goods']));
                } else {
                    echo join(',', $GLOBALS['_LANG']['upload_goods']);
                }
            } else {
                echo 'error: ' . $GLOBALS['_LANG']['upload_goods'] . ' not exists';
            }
        }

        /*------------------------------------------------------ */
        //-- 取得商品
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'get_goods') {
            $filter = app(\StdClass::class);

            $filter->cat_id = intval($_GET['cat_id']);
            $filter->brand_id = intval($_GET['brand_id']);
            $filter->real_goods = -1;
            $arr = get_goods_list($filter);

            return make_json_result($arr);
        }
    }

    /**
     * 批量添加商品
     *
     * @param array $checked
     * @param array $adminru
     */
    private function batchGoods($checked = [], $adminru = [])
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
        $user_cat = request()->get('user_cat', 0);

        $goods_sn = BaseRepository::getKeyPluck($checked, 'goods_sn');
        $goods_name = BaseRepository::getKeyPluck($checked, 'goods_name');

        $goodsCheckedList = [];
        if ($goods_sn && $goods_name) {
            $goodsCheckedList = Goods::where(function ($query) use ($goods_sn, $goods_name, $cat, $user_cat) {
                if ($goods_sn) {
                    $query = $query->whereIn('goods_sn', $goods_sn);
                }

                if ($goods_name) {
                    $query->orWhere(function ($query) use ($goods_name, $cat, $user_cat) {
                        $query = $query->whereIn('goods_name', $goods_name);

                        if ($cat || $user_cat) {
                            $query->where(function ($query) use ($cat, $user_cat) {
                                if ($cat) {
                                    $query = $query->where('cat_id', $cat);
                                }

                                if ($user_cat) {
                                    $query->orWhere('user_cat', $user_cat);
                                }
                            });
                        }
                    });
                }
            });

            $goodsCheckedList = $goodsCheckedList->where('user_id', $adminru['ru_id']);
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

                    if ($user_cat) {
                        $sql['where'][] = [
                            'name' => 'user_cat',
                            'value' => $user_cat
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
                    'user_cat' => $user_cat,
                    'add_time' => TimeRepository::getGmTime(),
                    'last_update' => TimeRepository::getGmTime()
                ];

                foreach ($field_list as $field) {

                    if (isset($value[$field])) {
                        $field_arr[$field] = $value[$field];
                    } else {
                        continue;
                    }

                    /* 虚拟商品处理 */
                    if ($field == 'goods_class' && $field_arr[$field] == G_CARD) {
                        $field_arr['extension_code'] = 'virtual_card';
                        continue;
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
                }

                /* 如果是虚拟商品，库存为0 */
                if ($field_arr['is_real'] == 0) {
                    $field_arr['goods_number'] = 0;
                }

                if ($field_arr && $field_arr['goods_name']) {
                    if (isset($field_arr['goods_name']) && $field_arr['goods_name']) {

                        if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                            $field_arr['goods_name'] = StrRepository::replaceFirst($field_arr['goods_name'], $brand_name);
                            $field_arr['goods_name'] = StrRepository::replaceFirst($field_arr['goods_name'], config('app.replace_symbol'));
                            $field_arr['goods_name'] = $brand_name . config('app.goods_symbol') . $field_arr['goods_name'];
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

                    $field_arr['user_id'] = $adminru['ru_id'];

                    /* 审核状态 */
                    if (empty(config('shop.review_goods'))) {
                        $field_arr['review_status'] = 5;
                    }

                    $new_goods = BaseRepository::getArrayfilterTable($field_arr, 'goods');

                    try {
                        $goods_id = Goods::insertGetId($new_goods);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $max_id = $goods_id + 1;

                    if (empty($field_arr['goods_sn'])) {
                        $goods_sn = $this->goodsManageService->generateGoodSn($max_id);

                        Goods::where('goods_id', $goods_id)->update([
                            'goods_sn' => $goods_sn
                        ]);
                    } else {
                        $same_goods_sn = Goods::where('goods_id', '<>', $goods_id)->where('user_id', $adminru['ru_id'])->where('goods_sn', $field_arr['goods_sn'])->count();

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
