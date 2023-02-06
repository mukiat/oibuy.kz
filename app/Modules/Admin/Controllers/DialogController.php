<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\File;
use App\Extensions\Pinyin;
use App\Extensions\SharePoster;
use App\Libraries\Exchange;
use App\Libraries\Image;
use App\Models\Article;
use App\Models\ArticleCat;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\BrandExtend;
use App\Models\Cart;
use App\Models\Category;
use App\Models\ExchangeGoods;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsArticle;
use App\Models\GoodsAttr;
use App\Models\GoodsCat;
use App\Models\GoodsConsumption;
use App\Models\GoodsLabel;
use App\Models\GoodsGallery;
use App\Models\GoodsKeyword;
use App\Models\GoodsLibGallery;
use App\Models\GoodsServicesLabel;
use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\GoodsTransportTpl;
use App\Models\GoodsUseLabel;
use App\Models\KeywordList;
use App\Models\MerchantsRegionArea;
use App\Models\Nav;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsChangelog;
use App\Models\ProductsWarehouse;
use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Models\TemplateMall;
use App\Models\VolumePrice;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Article\ArticleCatManageService;
use App\Services\Cart\CartCommonService;
use App\Services\Category\CategoryManageService;
use App\Services\Category\CategoryService;
use App\Services\Dialog\DialogManageService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsManageService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Store\StoreCommonService;
use Illuminate\Support\Facades\Storage;

/**
 * 管理中心品牌管理
 */
class DialogController extends InitController
{
    protected $dscRepository;
    protected $categoryService;
    protected $goodsManageService;
    protected $categoryManageService;
    protected $goodsAttrService;
    protected $goodsCommonService;
    protected $goodsWarehouseService;
    protected $dialogManageService;
    protected $cartCommonService;
    protected $articleCatManageService;

    public function __construct(
        DialogManageService $dialogManageService,
        DscRepository $dscRepository,
        CategoryService $categoryService,
        GoodsManageService $goodsManageService,
        CategoryManageService $categoryManageService,
        GoodsAttrService $goodsAttrService,
        GoodsCommonService $goodsCommonService,
        GoodsWarehouseService $goodsWarehouseService,
        CartCommonService $cartCommonService,
        ArticleCatManageService $articleCatManageService
    )
    {
        $this->dialogManageService = $dialogManageService;
        $this->dscRepository = $dscRepository;
        $this->categoryService = $categoryService;
        $this->goodsManageService = $goodsManageService;
        $this->categoryManageService = $categoryManageService;
        $this->goodsAttrService = $goodsAttrService;
        $this->goodsCommonService = $goodsCommonService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->cartCommonService = $cartCommonService;
        $this->articleCatManageService = $articleCatManageService;
    }

    public function index()
    {
        load_helper('goods', 'admin');

        $image = app(Image::class, [config('shop.bgcolor')]);

        load_helper('visual');

        $admin_id = get_admin_id();
        $adminru = get_admin_ru_id();

        $act = addslashes(trim(request()->input('act', '')));

        /*------------------------------------------------------ */
        //-- 仓库弹窗
        /*------------------------------------------------------ */
        if ($act == 'dialog_warehouse') {
            $result = ['content' => '', 'sgs' => ''];
            $temp = request()->get('temp', '');
            $user_id = request()->get('user_id', 0);
            $goods_id = request()->get('goods_id', 0);
            $this->smarty->assign("temp", $temp);
            $result['sgs'] = $temp;

            $grade_rank = get_seller_grade_rank($user_id);
            $this->smarty->assign('grade_rank', $grade_rank);
            $this->smarty->assign('integral_scale', config('shop.integral_scale'));

            $warehouse_list = get_warehouse_list();
            $this->smarty->assign('warehouse_list', $warehouse_list);

            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('goods_id', $goods_id);

            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        } //扩展分类
        elseif ($act == 'extension_category') {
            $result = ['content' => '', 'sgs' => ''];
            $temp = request()->get('temp', '');
            $this->smarty->assign("temp", $temp);
            $other_catids = request()->get('other_catids', '');
            $result['sgs'] = $temp;

            $goods_id = request()->get('goods_id', 0);

            $goods = get_admin_goods_info($goods_id);
            $goods['user_id'] = isset($goods['user_id']) ? $goods['user_id'] : $adminru['ru_id'];

            /* 商家入驻分类 */
            if ($goods['user_id']) {
                $seller_shop_cat = seller_shop_cat($goods['user_id']);
            }

            /* 取得分类 */
            $level_limit = 3;
            $category_level = [];
            for ($i = 1; $i <= $level_limit; $i++) {
                $category_list = [];
                if ($i == 1) {
                    if ($goods['user_id']) {
                        $category_list = get_category_list(0, 0, $seller_shop_cat, $goods['user_id'], $i);
                    } else {
                        $category_list = get_category_list();
                    }
                }
                $this->smarty->assign('cat_level', $i);
                $this->smarty->assign('category_list', $category_list);
                $category_level[$i] = $this->smarty->fetch('library/get_select_category.lbi');
            }
            $this->smarty->assign('category_level', $category_level);
            /* 取得已存在的扩展分类 */
            if ($goods_id > 0 || $other_catids) {
                $res = GoodsCat::whereRaw(1);
                if ($other_catids && $goods_id == 0) {
                    $other_catids = BaseRepository::getExplode($other_catids);
                    $res = $res->whereIn('cat_id', $other_catids)
                        ->where('goods_id', '0');
                } elseif ($goods_id > 0) {
                    $res = $res->where('goods_id', $goods_id);
                }
                $other_cat1 = BaseRepository::getToArrayGet($res);

                $other_category = [];
                if ($other_cat1) {
                    foreach ($other_cat1 as $key => $val) {
                        $other_category[$key]['cat_id'] = $val['cat_id'];
                        $other_category[$key]['cat_name'] = get_every_category($val['cat_id']);
                    }
                }

                $this->smarty->assign('other_category', $other_category);
            }

            $this->smarty->assign('goods_id', $goods_id);
            $result['content'] = $this->smarty->fetch('library/extension_category.lbi');
            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 添加属性图片 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'add_attr_img') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $goods_name = request()->get('goods_name', '');
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $goods_attr_name = request()->get('goods_attr_name', '');

            $goods_date = ['goods_name'];
            $goods_info = get_table_date('goods', "goods_id = '$goods_id'", $goods_date);
            if (!isset($goods_info['goods_name'])) {
                $goods_info['goods_name'] = $goods_name;
            }

            $goods_attr_date = ['attr_img_flie, attr_img_site, attr_checked, attr_gallery_flie'];
            $goods_attr_info = get_table_date('goods_attr', "goods_id = '$goods_id' and attr_id = '$attr_id' and goods_attr_id = '$goods_attr_id'", $goods_attr_date);

            if ($goods_attr_info) {
                if ($goods_attr_info['attr_img_flie']) {
                    $goods_attr_info['attr_img_flie'] = $this->dscRepository->getImagePath($goods_attr_info['attr_img_flie']);
                }

                if ($goods_attr_info['attr_img_site']) {
                    $goods_attr_info['attr_img_site'] = $this->dscRepository->getImagePath($goods_attr_info['attr_img_site']);
                }

                if ($goods_attr_info['attr_gallery_flie']) {
                    $goods_attr_info['attr_gallery_flie'] = $this->dscRepository->getImagePath($goods_attr_info['attr_gallery_flie']);
                }
            }

            $attr_date = ['attr_name'];
            $attr_info = get_table_date('attribute', "attr_id = '$attr_id'", $attr_date);

            $this->smarty->assign('goods_info', $goods_info);
            $this->smarty->assign('attr_info', $attr_info);
            $this->smarty->assign('goods_attr_info', $goods_attr_info);
            $this->smarty->assign('goods_attr_name', $goods_attr_name);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('attr_id', $attr_id);
            $this->smarty->assign('goods_attr_id', $goods_attr_id);
            $this->smarty->assign('form_action', 'insert_attr_img');

            $result['content'] = $this->smarty->fetch('library/goods_attr_img_info.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 添加属性图片插入数据 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'insert_attr_img') {
            load_helper('goods');

            $result = ['error' => 0, 'message' => '', 'content' => '', 'is_checked' => 0];

            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_name = request()->get('goods_attr_name', '');
            $img_url = request()->get('img_url', '');

            /* 允许上传的文件类型 */
            $allow_file_types = '|GIF|JPG|JPEG|PNG|';

            $attr_img_flie = request()->file('attr_img_flie');
            if ($attr_img_flie && $attr_img_flie->isValid()) {
                $other['attr_img_flie'] = get_upload_pic('attr_img_flie');
                $this->dscRepository->getOssAddFile([$other['attr_img_flie']]);
            } else {
                $other['attr_img_flie'] = '';
            }

            $goods_attr_info = GoodsAttr::where('goods_id', $goods_id)
                ->where('attr_id', $attr_id)
                ->where('goods_attr_id', $goods_attr_id);
            $goods_attr_info = BaseRepository::getToArrayFirst($goods_attr_info);

            if (empty($other['attr_img_flie'])) {
                $other['attr_img_flie'] = $goods_attr_info['attr_img_flie'];
            } else {
                @unlink(storage_public($goods_attr_info['attr_img_flie']));
            }

            $other['attr_img_site'] = request()->get('attr_img_site', '');
            $other['attr_checked'] = request()->get('attr_checked', 0);

            if ($img_url) {
                $gallery_flie = explode('/storage/', $img_url);

                if (count($gallery_flie) > 1) {
                    $other['attr_gallery_flie'] = $gallery_flie[1];
                } else {
                    $other['attr_gallery_flie'] = $img_url;
                }
            }

            if ($other['attr_checked'] == 1) {
                GoodsAttr::where('goods_id', $goods_id)
                    ->where('attr_id', $attr_id)
                    ->update(['attr_checked' => 0]);
                $result['is_checked'] = 1;
            }

            $other = BaseRepository::recursiveNullVal($other);

            GoodsAttr::where('goods_id', $goods_id)
                ->where('attr_id', $attr_id)
                ->where('goods_attr_id', $goods_attr_id)
                ->update($other);

            $result['goods_attr_id'] = $goods_attr_id;

            $goods = get_admin_goods_info($goods_id);

            /* 同步前台商品详情价格与商品列表价格一致 start */
            if ($other['attr_checked'] == 1) {
                if (config('shop.add_shop_price') == 0 && $goods['model_attr'] == 0) {
                    $properties = $this->goodsAttrService->getGoodsProperties($goods_id);  // 获得商品的规格和属性
                    $spe = !empty($properties['spe']) ? array_values($properties['spe']) : $properties['spe'];

                    $arr = [];
                    $goodsAttrId = '';
                    if ($spe) {
                        foreach ($spe as $key => $val) {
                            if ($val['values']) {
                                if ($val['is_checked']) {
                                    $arr[$key]['values'] = get_goods_checked_attr($val['values']);
                                } else {
                                    $arr[$key]['values'] = $val['values'][0];
                                }
                            }

                            if ($arr[$key]['values']['id']) {
                                $goodsAttrId .= $arr[$key]['values']['id'] . ",";
                            }
                        }

                        $goodsAttrId = $this->dscRepository->delStrComma($goodsAttrId);
                    }

                    $time = gmtime();
                    if (!empty($goodsAttrId)) {
                        $products = $this->goodsWarehouseService->getWarehouseAttrNumber($goods_id, $goodsAttrId, 0, 0, 0, $goods['model_attr']);

                        if ($products) {
                            $products['product_market_price'] = isset($products['product_market_price']) ? $products['product_market_price'] : 0;
                            $products['product_price'] = isset($products['product_price']) ? $products['product_price'] : 0;
                            $products['product_promote_price'] = isset($products['product_promote_price']) ? $products['product_promote_price'] : 0;

                            if ($goods['promote_price'] > 0) {
                                $promote_price = $this->goodsCommonService->getBargainPrice($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
                            } else {
                                $promote_price = 0;
                            }

                            if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date']) {
                                $promote_price = $products['product_promote_price'];
                            }

                            $other = [
                                'product_id' => $products['product_id'],
                                'product_price' => $products['product_price'],
                                'product_promote_price' => $promote_price
                            ];

                            Goods::where('goods_id', $goods_id)->update($other);
                        }
                    }
                }
            } else {
                if ($goods['model_attr'] > 0) {
                    $goods_other = [
                        'product_table' => '',
                        'product_id' => 0,
                        'product_price' => 0,
                        'product_promote_price' => 0
                    ];
                    Goods::where('goods_id', $goods_id)->update($goods_other);
                }
            }
            /* 同步前台商品详情价格与商品列表价格一致 end */

            clear_cache_files();
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除属性图片 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'drop_attr_img') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_name = request()->get('goods_attr_name', '');

            $attr_img_flie = GoodsAttr::where('goods_attr_id', $goods_attr_id)->value('attr_img_flie');
            $attr_img_flie = $attr_img_flie ? $attr_img_flie : '';

            $this->dscRepository->getOssDelFile([$attr_img_flie]);

            @unlink(storage_public($attr_img_flie));
            $other['attr_img_flie'] = '';
            GoodsAttr::where('goods_attr_id', $goods_attr_id)->update($other);

            $result['goods_attr_id'] = $goods_attr_id;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 选择属性图片 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'choose_attrImg') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $admin_id = get_admin_id();

            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $on_img_id = request()->get('img_id', 0);

            $attr_gallery_flie = GoodsAttr::where('goods_attr_id', $goods_attr_id)
                ->where('goods_id', $goods_id)
                ->value('attr_gallery_flie');
            $attr_gallery_flie = $attr_gallery_flie ? $attr_gallery_flie : '';

            $thumb_img_id = session()->has('thumb_img_id' . $admin_id) ? session('thumb_img_id' . $admin_id) : 0; //处理添加商品时相册图片串图问题   by kong
            $res = GoodsGallery::whereRaw(1);
            if (empty($goods_id) && $thumb_img_id) {
                $thumb_img_id = BaseRepository::getExplode($thumb_img_id);
                $res = $res->where('goods_id', '0')
                    ->whereIn('img_id', $thumb_img_id);
            } else {
                $res = $res->where('goods_id', $goods_id);
            }

            /* 删除数据 */
            $img_list = BaseRepository::getToArrayGet($res);
            $str = "<ul>";
            foreach ($img_list as $idx => $row) {
                $row['thumb_url'] = $this->dscRepository->getImagePath($row['thumb_url']); //处理图片地址
                if ($attr_gallery_flie == $row['img_url']) {
                    $str .= '<li id="gallery_' . $row['img_id'] . '" onClick="gallery_on(this,' . $row['img_id'] . ',' . $goods_id . ',' . $goods_attr_id . ')" class="on"><img src="' . $row['thumb_url'] . '" width="87" /><i><img src="' . asset('assets/admin/images/gallery_yes.png') . '" width="30" height="30"></i></li>';
                } else {
                    $str .= '<li id="gallery_' . $row['img_id'] . '" onClick="gallery_on(this,' . $row['img_id'] . ',' . $goods_id . ',' . $goods_attr_id . ')"><img src="' . $row['thumb_url'] . '" width="87" /><i><img src="' . asset('assets/admin/images/gallery_yes.png') . '" width="30" height="30"></i></li>';
                }
            }
            $str .= "</ul>";

            $result['content'] = $str;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 选择属性图片 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'insert_gallery_attr') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $gallery_id = request()->get('gallery_id', 0);

            if (!empty($gallery_id)) {
                $img = GoodsGallery::where('img_id', $gallery_id)->first();
                $result['img_id'] = $img['img_id'];
                $result['img_url'] = $img['img_url'];

                GoodsAttr::where('goods_attr_id', $goods_attr_id)->where('goods_id', $goods_id)->update(['attr_gallery_flie' => $img['img_url']]);
            } else {
                $result['error'] = 1;
            }

            $result['goods_attr_id'] = $goods_attr_id;

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 智能权重
        /* ------------------------------------------------------ */
        elseif ($act == 'manual_intervention') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $goods = get_admin_goods_info($goods_id, ['goods_id', 'goods_name', 'sort_order', 'weights']);
            $this->smarty->assign('goods', $goods);

            $manual_intervention = get_manual_intervention($goods_id);
            $this->smarty->assign('manual_intervention', $manual_intervention);

            $result['content'] = $this->smarty->fetch('library/manual_intervention.lbi');

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加仓库价格 //ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'add_goods_model_price') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $warehouse_id = 0;
            $area_id = 0;

            $goods = get_goods_model($goods_id);
            $this->smarty->assign('goods', $goods);

            $warehouse_list = get_warehouse_list();

            if ($warehouse_list) {
                $warehouse_id = $warehouse_list[0]['region_id'];
                $area_id = RegionWarehouse::where('parent_id', $warehouse_list[0]['region_id'])->value('region_id');
                $area_id = $area_id ? $area_id : 0;
            }

            $this->smarty->assign('warehouse_list', $warehouse_list);
            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            $list = get_goods_warehouse_area_list($goods_id, $goods['model_attr'], $warehouse_id);

            $this->smarty->assign('warehouse_area_list', $list['list']);
            $this->smarty->assign('warehouse_area_filter', $list['filter']);
            $this->smarty->assign('warehouse_area_record_count', $list['record_count']);
            $this->smarty->assign('warehouse_area_page_count', $list['page_count']);
            $this->smarty->assign('query', $list['query']);
            $this->smarty->assign('full_page', 1);

            $result['content'] = $this->smarty->fetch('library/goods_price_list.lbi');

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加仓库价格
        /* ------------------------------------------------------ */
        elseif ($act == 'goods_wa_query') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $list = get_goods_warehouse_area_list();

            $this->smarty->assign('warehouse_area_list', $list['list']);
            $this->smarty->assign('warehouse_area_filter', $list['filter']);
            $this->smarty->assign('warehouse_area_record_count', $list['record_count']);
            $this->smarty->assign('warehouse_area_page_count', $list['page_count']);
            $this->smarty->assign('query', $list['query']);

            $goods = get_goods_model($list['filter']['goods_id']);
            $this->smarty->assign('goods', $goods);

            return make_json_result($this->smarty->fetch('library/goods_price_list.lbi'), '', ['pb_filter' => $list['filter'], 'pb_page_count' => $list['page_count'], 'class' => "goodslistDiv"]);
        }

        /* ------------------------------------------------------ */
        //-- 添加仓库属性价格 //ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'add_warehouse_price') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $goods_attr_name = request()->get('goods_attr_name', '');

            $action_link = ['href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=', 'text' => __('admin::goods.goods_info')];

            if (empty($goods_attr_id)) {
                $goods_attr_id = get_goods_attr_nameId($goods_id, $attr_id, $goods_attr_name); //获取商品的属性ID
            }

            if (empty($attr_id)) {
                $attr_id = get_goods_attr_nameId($goods_id, $goods_attr_id, $goods_attr_name, 'attr_id', 1);
            }

            $goods_date = ['goods_name'];
            $goods_info = get_table_date('goods', "goods_id = '$goods_id'", $goods_date);

            $attr_date = ['attr_name'];
            $attr_info = get_table_date('attribute', "attr_id = '$attr_id'", $attr_date);

            $warehouse_area_list = get_fine_warehouse_all(0, $goods_id, $goods_attr_id);

            $this->smarty->assign('goods_info', $goods_info);
            $this->smarty->assign('attr_info', $attr_info);
            $this->smarty->assign('goods_attr_name', $goods_attr_name);
            $this->smarty->assign('warehouse_area_list', $warehouse_area_list);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('attr_id', $attr_id);
            $this->smarty->assign('goods_attr_id', $goods_attr_id);
            $this->smarty->assign('form_action', 'insert_warehouse_price');
            $this->smarty->assign('action_link', $action_link);

            $result['content'] = $this->smarty->fetch('library/goods_warehouse_price_info.lbi');

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加仓库属性价格 //ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'insert_warehouse_price') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', '');
            $warehouse_name = request()->get('warehouse_name', '');
            $goods_attr_name = request()->get('goods_attr_name', '');

            get_warehouse_area_attr_price_insert($warehouse_name, $goods_id, $goods_attr_id, 'warehouse_attr');

            $result['goods_attr_id'] = $goods_attr_id;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除商品勾选属性 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'del_goods_attr') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];
            $goods_id = request()->get('goods_id', 0);
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $attr_value = request()->get('attr_value', '');
            $goods_model = request()->get('goods_model', 0);//商品模式
            $region_id = request()->get('region_id', 0); //地区id

            $goodsAttrDelete = GoodsAttr::whereRaw(1);
            if ($goods_attr_id) {
                $goodsAttrDelete = $goodsAttrDelete->where('goods_attr_id', $goods_attr_id);
            } else {
                $goodsAttrDelete = $goodsAttrDelete->where('goods_id', $goods_id)
                    ->where('attr_value', $attr_value)
                    ->where('attr_id', $attr_id)
                    ->where('admin_id', $admin_id);
            }
            $res = ProductsChangelog::whereRaw(1);
            //判断商品类型
            if ($goods_model == 1) {
                $res = $res->where('warehouse_id', $region_id);
                $products = ProductsWarehouse::where('goods_id', $goods_id)
                    ->where('warehouse_id', $region_id);
            } elseif ($goods_model == 2) {
                $res = $res->where('area_id', $region_id);
                $products = ProductsArea::where('goods_id', $goods_id)
                    ->where('area_id', $region_id);
            } else {
                $products = Products::where('goods_id', $goods_id);
            }
            //删除相关货品
            if (!empty($products)) {
                $products = BaseRepository::getToArrayGet($products);
                foreach ($products as $k => $v) {
                    if ($v['goods_attr']) {
                        $goods_attr = explode('|', $v['goods_attr']);
                        if (in_array($goods_attr_id, $goods_attr)) {
                            Products::where('product_id', $v['product_id'])
                                ->where('goods_id', $goods_id)->delete();
                        }
                    }
                }
            }
            $admin_id = get_admin_id();
            //删除零时货品表
            $res = $res->where('admin_id', $admin_id)
                ->where('goods_id', $goods_id);
            $products_changelog = BaseRepository::getToArrayGet($res);

            if (!empty($products_changelog)) {
                foreach ($products_changelog as $k => $v) {
                    if ($v['goods_attr']) {
                        $goods_attr = explode('|', $v['goods_attr']);
                        if (in_array($goods_attr_id, $goods_attr)) {
                            ProductsChangelog::where('product_id', $v['product_id'])->where('goods_id', $goods_id)->delete();
                        }
                    }
                }
            }
            $goodsAttrDelete->delete();

            $goods_info = get_admin_goods_info($goods_id);

            if (!empty($goods_info['model_attr']) && $goods_info['model_attr'] == 1) {
                $prod = ProductsWarehouse::where('goods_id', $goods_id);
            } elseif (!empty($goods_info['model_attr']) && $goods_info['model_attr'] == 2) {
                $prod = ProductsArea::where('goods_id', $goods_id);
            } else {
                $prod = Products::where('goods_id', $goods_id);
            }

            $prod = $prod->whereRaw("FIND_IN_SET('$goods_attr_id', REPLACE(goods_attr, '|', ','))");

            $prod->delete();

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加地区属性价格 //ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'add_area_price') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);
            $goods_attr_name = request()->get('goods_attr_name', '');

            $action_link = ['href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=', 'text' => __('admin::goods.goods_info')];

            if (empty($goods_attr_id)) {
                $goods_attr_id = get_goods_attr_nameId($goods_id, $attr_id, $goods_attr_name); //获取商品的属性ID
            }

            if (empty($attr_id)) {
                $attr_id = get_goods_attr_nameId($goods_id, $goods_attr_id, $goods_attr_name, 'attr_id', 1);
            }

            $goods_date = ['goods_name'];
            $goods_info = get_table_date('goods', "goods_id = '$goods_id'", $goods_date);

            $attr_date = ['attr_name'];
            $attr_info = get_table_date('attribute', "attr_id = '$attr_id'", $attr_date);

            $warehouse_area_list = get_fine_warehouse_area_all(0, $goods_id, $goods_attr_id);

            $this->smarty->assign('goods_info', $goods_info);
            $this->smarty->assign('attr_info', $attr_info);
            $this->smarty->assign('goods_attr_name', $goods_attr_name);
            $this->smarty->assign('warehouse_area_list', $warehouse_area_list);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('attr_id', $attr_id);
            $this->smarty->assign('goods_attr_id', $goods_attr_id);
            $this->smarty->assign('form_action', 'insert_area_price');
            $this->smarty->assign('action_link', $action_link);

            $result['content'] = $this->smarty->fetch('library/goods_area_price_info.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 添加地区属性价格 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'insert_area_price') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', '');
            $area_name = request()->get('area_name', '');

            get_warehouse_area_attr_price_insert($area_name, $goods_id, $goods_attr_id, 'warehouse_area_attr');

            $result['goods_attr_id'] = $goods_attr_id;

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加商品SKU/库存
        /* ------------------------------------------------------ */
        elseif ($act == 'add_sku') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $user_id = request()->get('user_id', 0);
            $warehouse_id = 0;
            $area_id = 0;
            $city_id = 0;

            $goods = get_goods_model($goods_id);

            $warehouse_list = get_warehouse_list();
            if ($warehouse_list) {
                $warehouse_id = $warehouse_list[0]['region_id'];
                $area_id = RegionWarehouse::where('parent_id', $warehouse_list[0]['region_id'])->value('region_id');
                $area_id = $area_id ? $area_id : 0;

                $city_id = RegionWarehouse::where('parent_id', $area_id)->value('region_id');
                $city_id = $city_id ? $city_id : 0;
            }

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);
            $this->smarty->assign('city_id', $city_id);

            $this->smarty->assign('goods', $goods);

            $this->smarty->assign('warehouse_list', $warehouse_list);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('user_id', $user_id);

            $this->smarty->assign('goods_attr_price', config('shop.goods_attr_price'));

            $product_list = get_goods_product_list($goods_id, $goods['model_attr'], $warehouse_id, $area_id, $city_id);
            $this->smarty->assign('product_list', $product_list['product_list']);
            $this->smarty->assign('sku_filter', $product_list['filter']);
            $this->smarty->assign('sku_record_count', $product_list['record_count']);
            $this->smarty->assign('sku_page_count', $product_list['page_count']);
            $this->smarty->assign('query', $product_list['query']);
            $this->smarty->assign('full_page', 1);

            $result['content'] = $this->smarty->fetch('library/goods_attr_list.lbi');
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加商品SKU/库存
        /* ------------------------------------------------------ */
        elseif ($act == 'sku_query') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $this->smarty->assign('goods_attr_price', config('shop.goods_attr_price'));

            $product_list = get_goods_product_list();

            $this->smarty->assign('product_list', $product_list['product_list']);
            $this->smarty->assign('sku_filter', $product_list['filter']);
            $this->smarty->assign('sku_record_count', $product_list['record_count']);
            $this->smarty->assign('sku_page_count', $product_list['page_count']);
            $this->smarty->assign('query', $product_list['query']);

            $product_list['filter']['goods_id'] = $product_list['filter']['goods_id'] ?? 0;
            $product_list['filter']['model'] = $product_list['filter']['model'] ?? 0;
            $product_list['filter']['warehouse_id'] = $product_list['filter']['warehouse_id'] ?? 0;
            $product_list['filter']['area_id'] = $product_list['filter']['area_id'] ?? 0;

            $goods = [
                'goods_id' => $product_list['filter']['goods_id'],
                'model_attr' => $product_list['filter']['model'],
                'warehouse_id' => $product_list['filter']['warehouse_id'],
                'area_id' => $product_list['filter']['area_id']
            ];
            $this->smarty->assign('goods', $goods);

            return make_json_result($this->smarty->fetch('library/goods_attr_list.lbi'), '', ['pb_filter' => $product_list['filter'], 'pb_page_count' => $product_list['page_count'], 'class' => "attrlistDiv"]);
        }

        /* ------------------------------------------------------ */
        //-- 添加商品SKU/库存
        /* ------------------------------------------------------ */
        elseif ($act == 'add_attr_sku') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $product_id = request()->get('product_id', 0);

            $goods_info = get_admin_goods_info($goods_id);

            $this->smarty->assign('product_id', $product_id);

            $editInput = "";
            $method = "";
            $filed = "";
            if ($goods_info['model_attr'] == 1) {
                $filed = ", warehouse_id";
                $method = "insert_warehouse_price";
            } elseif ($goods_info['model_attr'] == 2) {
                $filed = ", area_id";
                $method = "insert_area_price";
            } else {
                $editInput = "edit_attr_price";
            }

            /* 货品库存 */
            $product = get_product_info($product_id, 'product_id, product_number, goods_id, product_sn, goods_attr' . $filed, $goods_info['model_attr'], 1);
            $this->smarty->assign('goods_info', $goods_info);
            $this->smarty->assign('product', $product);
            $this->smarty->assign('editInput', $editInput);
            $this->smarty->assign('method', $method);

            $warehouse_id = isset($product['warehouse_id']) && !empty($product['warehouse_id']) ? $product['warehouse_id'] : 0;
            $area_id = isset($product['area_id']) && !empty($product['area_id']) ? $product['area_id'] : 0;

            if (!empty($warehouse_id)) {
                $warehouse_area_id = $warehouse_id;
            } elseif (!empty($area_id)) {
                $warehouse_area_id = $area_id;
            }

            $warehouse = RegionWarehouse::select('region_id', 'regionId', 'region_name', 'parent_id')->where('region_id', $warehouse_area_id)->first();
            $warehouse = $warehouse ? $warehouse->toArray() : [];

            if (isset($warehouse['parent_id']) && $warehouse['parent_id']) {
                $warehouse_name = RegionWarehouse::where('region_id', $warehouse['parent_id'])->value('region_name');
                $warehouse_name = $warehouse_name ? $warehouse_name : '';
                $warehouse['area_name'] = $warehouse['region_name'];
                $warehouse['region_name'] = $warehouse_name;
            }

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);
            $this->smarty->assign('warehouse', $warehouse);

            $result['method'] = $method;
            $result['content'] = $this->smarty->fetch('library/goods_list_product.lbi');
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 页面加载动作
        /* ------------------------------------------------------ */
        elseif ($act == 'getload_url') {
            $this->smarty->assign("temp", "load_url");

            return $this->smarty->display("library/dialog.lbi");
        }

        /*------------------------------------------------------ */
        //-- 升级弹窗
        /*------------------------------------------------------ */
        elseif ($act == 'dialog_upgrade') {
            $result = ['content' => '', 'sgs' => ''];

            $this->smarty->assign("cat_belongs", config('shop.cat_belongs'));
            $this->smarty->assign("brand_belongs", config('shop.brand_belongs'));
            $result['content'] = $this->smarty->fetch('library/upgrade.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 商品单选复选属性手工录入
        /*------------------------------------------------------ */
        elseif ($act == 'attr_input_type') {
            $result = ['content' => '', 'sgs' => ''];

            $attr_id = request()->get('attr_id', 0);
            $goods_id = request()->get('goods_id', 0);

            $this->smarty->assign('attr_id', $attr_id);
            $this->smarty->assign('goods_id', $goods_id);

            $goods_attr = get_dialog_goods_attr_type($attr_id, $goods_id);
            $this->smarty->assign('goods_attr', $goods_attr);

            $result['content'] = $this->smarty->fetch('library/attr_input_type.lbi');
            return response()->json($result);
        }


        /*------------------------------------------------------ */
        //-- 商品单选复选属性手工录入
        /*------------------------------------------------------ */
        elseif ($act == 'insert_attr_input') {
            $result = ['content' => '', 'sgs' => ''];

            $attr_id = request()->get('attr_id', 0);
            $goods_id = request()->get('goods_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', []);
            $attr_value_list = request()->get('attr_value_list', []);
            $attr_id_val = request()->get('attr_id_val', '');
            $goods_attr_id = !empty($attr_id_val) ? explode(',', $attr_id_val) : $goods_attr_id;
            $value_list_val = request()->get('value_list_val', '');
            $attr_value_list = !empty($value_list_val) ? explode(',', $value_list_val) : $attr_value_list;
            $res = GoodsAttr::whereRaw(1);

            if ($goods_id) {
                $res = $res->where('goods_id', $goods_id);
            } else {
                $res = $res->where('goods_id', '0')
                    ->where('admin_id', $admin_id);
            }

            /* 插入、更新、删除数据 */
            if ($attr_value_list) {
                foreach ($attr_value_list as $key => $attr_value) {
                    if ($attr_value) {
                        $attr_value = trim($attr_value);

                        if (isset($goods_attr_id[$key]) && $goods_attr_id[$key]) {
                            GoodsAttr::where('goods_attr_id', $goods_attr_id[$key])->update(['attr_value' => $attr_value]);
                        } else {
                            $max_attr_sort = $res->where('attr_id', $attr_id)->max('attr_sort');

                            if ($max_attr_sort) {
                                $key = $max_attr_sort + 1;
                            } else {
                                $key += 1;
                            }

                            $count = GoodsAttr::where('attr_value', $attr_value)
                                ->where('attr_id', $attr_id)
                                ->where('goods_id', $goods_id)
                                ->count();

                            if ($count < 1) {
                                $other = [
                                    'attr_id' => $attr_id,
                                    'goods_id' => $goods_id,
                                    'attr_value' => $attr_value,
                                    'attr_sort' => $key,
                                    'admin_id' => $admin_id
                                ];
                                GoodsAttr::insert($other);
                            }
                        }
                    }
                }
            }

            $result['attr_id'] = $attr_id;
            $result['goods_id'] = $goods_id;

            $goods_attr = get_dialog_goods_attr_type($attr_id, $goods_id);
            $this->smarty->assign('goods_attr', $goods_attr);
            $this->smarty->assign('attr_id', $attr_id);

            $result['content'] = $this->smarty->fetch('library/attr_input_type_list.lbi');

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 商品单选复选属性手工录入
        /*------------------------------------------------------ */
        elseif ($act == 'del_input_type') {
            $result = ['content' => '', 'sgs' => ''];

            $goods_id = request()->get('goods_id', 0);
            $attr_id = request()->get('attr_id', 0);
            $goods_attr_id = request()->get('goods_attr_id', 0);

            $sql = "DELETE FROM " . $this->dsc->table('goods_attr') . " WHERE goods_attr_id = '$goods_attr_id'";
            $this->db->query($sql);

            if ($goods_id > 0) {
                $goods_info = get_admin_goods_info($goods_id);

                if ($goods_info['model_attr'] == 1) {
                    $prod = ProductsWarehouse::where('goods_id', $goods_id);
                } elseif ($goods_info['model_attr'] == 2) {
                    $prod = ProductsArea::where('goods_id', $goods_id);
                } else {
                    $prod = Products::where('goods_id', $goods_id);
                }

                $prod = $prod->whereRaw("FIND_IN_SET('$goods_attr_id', REPLACE(goods_attr, '|', ','))");

                $prod->delete();

                $goods_attr = get_dialog_goods_attr_type($attr_id, $goods_id);
            } else {
                $goods_attr = [];
            }

            $this->smarty->assign('goods_attr', $goods_attr);
            $this->smarty->assign('attr_id', $attr_id);

            $result['attr_id'] = $attr_id;

            $result['attr_content'] = $this->smarty->fetch('library/attr_input_type_list.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除商品优惠阶梯价格
        /*------------------------------------------------------ */
        elseif ($act == 'del_volume') {
            $result = ['content' => '', 'sgs' => ''];

            $goods_id = request()->get('goods_id', 0);
            $volume_id = request()->get('id', 0);

            VolumePrice::where('id', $volume_id)->delete();

            $volume_price_list = $this->goodsCommonService->getVolumePriceList($goods_id);
            if (!$volume_price_list) {
                Goods::where('goods_id', $goods_id)->update(['is_volume' => '0']);
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除批发商品优惠阶梯价格
        /*------------------------------------------------------ */
        elseif ($act == 'del_wholesale_volume') {
            $result = ['content' => '', 'sgs' => ''];

            if (!file_exists(SUPPLIERS)) {
                return response()->json(['error' => 1]);
            }

            $volume_id = (int)request()->get('id', 0);

            \App\Modules\Suppliers\Models\WholesaleVolumePrice::where('id', $volume_id)->delete();

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除满立减优惠价格
        /*------------------------------------------------------ */
        elseif ($act == 'del_cfull') {
            $result = ['content' => '', 'sgs' => ''];

            $goods_id = request()->get('goods_id', 0);
            $volume_id = request()->get('id', 0);
            GoodsConsumption::where('id', $volume_id)->delete();
            $consumption_list = $this->cartCommonService->getGoodsConList($goods_id, 'goods_consumption'); //满减订单金额
            if (!$consumption_list) {
                Goods::where('goods_id', $goods_id)->update(['is_fullcut' => '0']);
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 添加商品图片外链地址
        /*------------------------------------------------------ */
        elseif ($act == 'add_external_url') {
            $result = ['content' => '', 'sgs' => '', 'error' => 0];

            $goods_id = request()->get('goods_id', 0);

            $this->smarty->assign('goods_id', $goods_id);
            $result['content'] = $this->smarty->fetch('library/external_url_list.lbi');

            $result['goods_id'] = $goods_id;
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 插入商品图片外链地址
        /*------------------------------------------------------ */
        elseif ($act == 'insert_external_url') {
            $result = ['content' => '', 'sgs' => '', 'error' => 0];
            $is_lib = request()->get('is_lib', 0);

            $goods_id = request()->get('goods_id', 0);
            $external_url_list = request()->get('external_url_list', []);

            /* 是否处理缩略图 */
            $proc_thumb = (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) ? false : true;

            //当前域名协议
            $http = $this->dsc->http();

            if ($external_url_list) {
                /* 商品库和普通商品相册 */
                if ($is_lib) {
                    $desc = GoodsLibGallery::where('goods_id', $goods_id)->max('img_desc');
                } else {
                    $desc = GoodsGallery::where('goods_id', $goods_id)->max('img_desc');
                }
                $desc = $desc ? $desc : 0;
                $admin_id = get_admin_id();
                $admin_temp_dir = "seller";
                $admin_temp_dir = storage_public("temp" . '/' . $admin_temp_dir . '/' . "admin_" . $admin_id);

                // 如果目标目录不存在，则创建它
                if (!file_exists($admin_temp_dir)) {
                    make_dir($admin_temp_dir);
                }

                foreach ($external_url_list as $key => $image_urls) {
                    if ($image_urls) {
                        if (!empty($image_urls) && ($image_urls != __('admin::goods.img_file')) && ($image_urls != 'http://') && (strpos($image_urls, 'http://') !== false || strpos($image_urls, 'https://') !== false)) {
                            if ($this->dscRepository->getHttpBasename($image_urls, $admin_temp_dir)) {
                                $image_url = trim($image_urls);
                                //定义原图路径
                                $down_img = $admin_temp_dir . "/" . basename($image_url);

                                $img_wh = $image->get_width_to_height($down_img, config('shop.image_width'), config('shop.image_height'));
                                $image_width = isset($img_wh['image_width']) ? $img_wh['image_width'] : config('shop.image_width');
                                $image_height = isset($img_wh['image_height']) ? $img_wh['image_height'] : config('shop.image_height');

                                $goods_img = $image->make_thumb(['img' => $down_img, 'type' => 1], $image_width, $image_height);

                                // 生成缩略图
                                if ($proc_thumb) {
                                    $thumb_url = $image->make_thumb(['img' => $down_img, 'type' => 1], config('shop.thumb_width'), config('shop.thumb_height'));
                                    $thumb_url = $this->goodsManageService->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                                } else {
                                    $thumb_url = $image->make_thumb(['img' => $down_img, 'type' => 1]);
                                    $thumb_url = $this->goodsManageService->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                                }

                                $img_original = $this->goodsManageService->reformatImageName('gallery', $goods_id, $down_img, 'source');
                                $img_url = $this->goodsManageService->reformatImageName('gallery', $goods_id, $goods_img, 'goods');

                                $desc += 1;

                                $other = [
                                    'goods_id' => $goods_id,
                                    'img_url' => $img_url,
                                    'img_desc' => $desc,
                                    'thumb_url' => $thumb_url,
                                    'img_original' => $img_original
                                ];

                                if ($is_lib) {
                                    $insert_id = GoodsLibGallery::insertGetId($other);
                                } else {
                                    $insert_id = GoodsGallery::insertGetId($other);
                                }

                                $thumb_img_id[] = $insert_id;
                                @unlink($down_img);
                            }
                        }
                        $this->dscRepository->getOssAddFile([$img_url, $thumb_url, $img_original]);
                    }
                }
                if (session()->has('thumb_img_id' . session('admin_id')) && !empty(session('thumb_img_id' . session('admin_id')))) {
                    $thumb_img_id = array_merge($thumb_img_id, session('thumb_img_id' . session('admin_id')));
                }

                session([
                    'thumb_img_id' . session('admin_id') => $thumb_img_id
                ]);
            }

            /* 图片列表 */

            if ($is_lib) {
                $res = GoodsLibGallery::where('goods_id', $goods_id);
            } else {
                $res = GoodsGallery::where('goods_id', $goods_id);
            }
            $img_id = session('thumb_img_id' . session('admin_id'));
            if ($img_id && $goods_id == 0) {
                $img_id = BaseRepository::getExplode($img_id);
                $res = $res->whereIn('img_id', $img_id);
            }
            $res = $res->orderBy('img_desc');
            $img_list = BaseRepository::getToArrayGet($res);

            /* 格式化相册图片路径 */
            if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0)) {
                foreach ($img_list as $key => $gallery_img) {
                    $img_list[$key] = $gallery_img;
                    //图片显示
                    $img_list[$key]['img_url'] = $this->dscRepository->getImagePath($gallery_img['img_original']);
                    $img_list[$key]['thumb_url'] = $this->dscRepository->getImagePath($gallery_img['thumb_url']);
                }
            } else {
                foreach ($img_list as $key => $gallery_img) {
                    $img_list[$key] = $gallery_img;

                    if (!empty($gallery_img['external_url'])) {
                        $img_list[$key]['img_url'] = $gallery_img['external_url'];
                        $img_list[$key]['thumb_url'] = $gallery_img['external_url'];
                    } else {
                        $img_list[$key]['thumb_url'] = $this->dscRepository->getImagePath($gallery_img['thumb_url']);
                    }
                }
            }

            $this->smarty->assign('img_list', $img_list);
            $this->smarty->assign('goods_id', $goods_id);
            $result['content'] = $this->smarty->fetch('library/gallery_img.lbi');

            $result['goods_id'] = $goods_id;
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 编辑商品图片外链地址
        /*------------------------------------------------------ */
        elseif ($act == 'insert_gallery_url') {
            $result = ['content' => '', 'sgs' => '', 'error' => 0];

            //当前域名协议
            $http = $this->dsc->http();

            $goods_id = request()->get('goods_id', 0);
            $img_id = request()->get('img_id', 0);
            $external_url = request()->get('external_url', '');

            if (!empty($external_url)) {
                // 验证商品图片外链格式
                $res = $this->dscRepository->checkImageUrl($external_url);
                if ($res == false) {
                    $result['error'] = 2;
                    $result['img_id'] = $img_id;
                    $result['msg'] = lang('common.invalid_upload_image_type');
                    return response()->json($result);
                }

                $imgId = GoodsGallery::where('external_url', $external_url)
                    ->where('goods_id', $goods_id)
                    ->where('img_id', '<>', $img_id)
                    ->value('img_id');
                $imgId = $imgId ? $imgId : 0;
                if ($imgId) {
                    $result['error'] = 1;
                } else {
                    GoodsGallery::where('img_id', $img_id)->update(['external_url' => $external_url]);
                }
            } else {
                GoodsGallery::where('img_id', $img_id)->update(['external_url' => '']);
            }

            $result['img_id'] = $img_id;
            if (!empty($external_url)) {
                $result['external_url'] = $external_url;
            } else {
                $thumb_url = GoodsGallery::where('img_id', $img_id)->value('thumb_url');
                $thumb_url = $thumb_url ? $thumb_url : '';
                $thumb_url = $this->dscRepository->getImagePath($thumb_url);

                $result['external_url'] = $thumb_url;
            }

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 添加图片
        /*------------------------------------------------------ */
        elseif ($act == 'pic_album') {
            $result = ['content' => '', 'sgs' => ''];
            $album_id = request()->get('id', 0);
            $this->smarty->assign('album_id', $album_id);
            $this->smarty->assign('temp', $act);

            $album_info = get_goods_gallery_album(2, $album_id, ['suppliers_id', 'ru_id']);
            $album_info['ru_id'] = isset($album_info['ru_id']) ? $album_info['ru_id'] : 0;
            $album_info['suppliers_id'] = isset($album_info['suppliers_id']) ? $album_info['suppliers_id'] : 0;

            $cat_select = gallery_cat_list(0, 0, false, 0, true, $album_info['ru_id'], $album_info['suppliers_id']);

            /* 简单处理缩进 */
            foreach ($cat_select as $k => $v) {
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cat_select[$k]['name'] = $level . $v['name'];
                }
            }

            $this->smarty->assign('cat_select', $cat_select);
            $album_mame = get_goods_gallery_album(2, $album_id);
            $this->smarty->assign('album_mame', $album_mame['album_mame']);

            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 转移相册
        /*------------------------------------------------------ */
        elseif ($act == 'album_move') {
            $result = ['content' => '', 'pic_id' => '', 'old_album_id' => ''];
            $pic_id = request()->get('pic_id', 0);
            $temp = !empty($act) ? $act : '';
            $this->smarty->assign("temp", $temp);

            /*获取全部相册*/
            $cat_select = gallery_cat_list(0, 0, false, 0, true);

            /* 简单处理缩进 */
            foreach ($cat_select as $k => $v) {
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cat_select[$k]['name'] = $level . $v['name'];
                }
            }

            $this->smarty->assign('cat_select', $cat_select);

            /*获取该图片所属相册*/
            $album_id = gallery_pic_album(0, $pic_id, ['album_id']);
            $this->smarty->assign('album_id', $album_id);

            $result['pic_id'] = $pic_id;
            $result['old_album_id'] = $album_id;
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除属性图片 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'update_review_status') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $goods_id = request()->get('goods_id', 0);
            $other['review_status'] = request()->get('review_status', 2);
            $other['review_content'] = request()->get('review_content', '');
            $type = request()->get('type', 'not_audit');

            $this->db->autoExecute($this->dsc->table('goods'), $other, "UPDATE", "goods_id = '$goods_id'");

            // 审核通过更新购物车
            if ($other['review_status'] > 2) {
                Cart::where('goods_id', $goods_id)->update(['is_invalid' => 0]);
            }

            $result['goods_id'] = $goods_id;
            $result['type'] = $type;

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 专题可视化 广告图轮播 //by kong
        /*------------------------------------------------------ */
        elseif ($act == 'shop_banner') {
            $result = ['content' => '', 'sgs' => '', 'mode' => ''];
            $this->smarty->assign("temp", "shop_banner");

            $result = ['content' => '', 'mode' => ''];

            $lift = request()->get('lift', '');
            $result['hierarchy'] = request()->get('hierarchy', 0);
            $inid = request()->get('inid', ''); //div标识
            $is_vis = request()->get('is_vis', 0);
            $image_type = request()->get('image_type', 0);
            //可视化入口
            if ($is_vis == 0) {
                $uploadImage = request()->get('uploadImage', 0);
                $titleup = request()->get('titleup', 0);
                $result['suffix'] = request()->get('suffix', '');//模板名称
                $result['mode'] = request()->get('mode', '');

                /* 处理数组 */
                $spec_attr = strip_tags(urldecode(request()->get('spec_attr', '')));
                $spec_attr = json_str_iconv($spec_attr);
                $spec_attr = !empty($spec_attr) ? stripslashes($spec_attr) : '';
                if (!empty($spec_attr)) {
                    $spec_attr = dsc_decode($spec_attr, true);
                }
                $default = '';
                if ($result['mode'] == 'lunbo') {
                    $default = 'shade';
                } elseif ($result['mode'] == 'advImg1') {
                    $default = 'yesSlide';
                }
                $spec_attr['is_title'] = isset($spec_attr['is_title']) ? $spec_attr['is_title'] : 0;
                $spec_attr['slide_type'] = isset($spec_attr['slide_type']) ? $spec_attr['slide_type'] : $default;
                $spec_attr['target'] = isset($spec_attr['target']) ? addslashes($spec_attr['target']) : '_blank';
                $pic_src = (isset($spec_attr['pic_src']) && $spec_attr['pic_src'] != ',') ? $spec_attr['pic_src'] : [];
                $link = (!empty($spec_attr['link']) && $spec_attr['link'] != ',') ? explode(',', $spec_attr['link']) : [];
                $sort = (isset($spec_attr['sort']) && $spec_attr['sort'] != ',') ? $spec_attr['sort'] : [];
                $pic_number = request()->get('pic_number', 0);
                $bg_color = isset($spec_attr['bg_color']) ? $spec_attr['bg_color'] : [];
                $title = (!empty($spec_attr['title']) && $spec_attr['title'] != ',') ? $spec_attr['title'] : [];
                $subtitle = (!empty($spec_attr['subtitle']) && $spec_attr['subtitle'] != ',') ? $spec_attr['subtitle'] : [];
                $result['diff'] = request()->get('diff', 0);
                $count = COUNT($pic_src); //数组长度

                /* 合并数组 */
                $arr = [];
                for ($i = 0; $i < $count; $i++) {
                    if ($pic_src[$i]) {
                        if (strpos($pic_src[$i], 'storage/') === false && (strpos($pic_src[$i], 'http://') === false && strpos($pic_src[$i], 'https://') === false)) {
                            $pic_image = $this->dscRepository->getImagePath($pic_src[$i]);
                        } else {
                            $pic_image = $pic_src[$i];
                        }

                        $arr[$i + 1]['pic_src'] = $pic_image;
                        if (isset($link[$i]) && $link[$i]) {
                            $arr[$i + 1]['link'] = str_replace(['＆'], '&', $link[$i]);
                        } else {
                            $arr[$i + 1]['link'] = isset($link[$i]) ? $link[$i] : '';
                        }
                        $arr[$i + 1]['sort'] = isset($sort[$i]) ? $sort[$i] : '';
                        $arr[$i + 1]['title'] = isset($title[$i]) ? $title[$i] : '';
                        $arr[$i + 1]['bg_color'] = isset($bg_color[$i]) ? $bg_color[$i] : '';
                        $arr[$i + 1]['subtitle'] = isset($subtitle[$i]) ? $subtitle[$i] : '';
                    }
                }
                $this->smarty->assign('banner_list', $arr);
            }

            $cat_select = gallery_cat_list(0, 0, false, 0, true);

            /* 简单处理缩进 */
            $i = 0;
            $default_album = 0;
            foreach ($cat_select as $k => $v) {
                if ($v['level'] == 0 && $i == 0) {
                    $i++;
                    $default_album = $v['album_id'];
                }
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cat_select[$k]['name'] = $level . $v['name'];
                }
            }
            if ($default_album > 0) {
                $pic_list = getAlbumList($default_album);

                $this->smarty->assign('pic_list', $pic_list['list']);
                $this->smarty->assign('filter', $pic_list['filter']);
                $this->smarty->assign('album_id', $default_album);
            }
            $this->smarty->assign('cat_select', $cat_select);

            $this->smarty->assign('is_vis', $is_vis);
            //可视化入口
            if ($is_vis == 0) {
                if ($result['mode'] == 'fh-haohuo' || $result['mode'] == 'h-phb') {
                    $titleup = 1;
                    $this->smarty->assign('hierarchy', $result['hierarchy']);
                    $this->smarty->assign('lift', $lift);
                }

                $this->smarty->assign('pic_number', $pic_number);
                $this->smarty->assign('mode', $result['mode']);
                $this->smarty->assign('spec_attr', $spec_attr);
                $this->smarty->assign('uploadImage', $uploadImage);
                $this->smarty->assign('titleup', $titleup);
                $this->smarty->assign('suffix', $result['suffix']);
                $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            } else {
                $this->smarty->assign('image_type', 0);
                $this->smarty->assign('log_type', 'image');
                $this->smarty->assign('image_type', $image_type);
                $this->smarty->assign('inid', $inid);

                $result['content'] = $this->smarty->fetch('library/album_dialog.lbi');
            }

            return response()->json($result);
        } /*添加相册*/
        elseif ($act == 'add_albun_pic') {
            $result = ['content' => '', 'pic_id' => '', 'old_album_id' => ''];
            $temp = !empty($act) ? $act : '';
            $this->smarty->assign("temp", $temp);
            $album_info = [
                'ru_id' => 0,
                'suppliers_id' => 0
            ];
            $cat_select = gallery_cat_list(0, 0, false, 0, true, $album_info['ru_id'], $album_info['suppliers_id']);
            /* 简单处理缩进 */
            foreach ($cat_select as $k => $v) {
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cat_select[$k]['name'] = $level . $v['name'];
                }
            }
            $this->smarty->assign('cat_select', $cat_select);
            $this->smarty->assign("album_info", $album_info);
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }
        /*--------------------------------------------------------*/
        //商品模块弹窗
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_info') {
            $result = ['content' => '', 'mode' => ''];
            /* 处理数组 */
            $search_type = request()->get('search_type', '');
            $goods_id = intval(request()->get('goods_id', 0));
            $cat_id = request()->get('cat_id', 0);
            $goods_type = request()->get('goods_type', 0);
            $good_number = request()->get('good_number', 0);
            $suffix = request()->get('suffix', '');//模板名称
            $spec_attr = request()->get('spec_attr', '');
            if ($spec_attr) {
                $spec_attr = strip_tags(urldecode($spec_attr));
                $spec_attr = json_str_iconv($spec_attr);
                $spec_attr = !empty($spec_attr) ? stripslashes($spec_attr) : '';
            }
            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode(stripslashes($spec_attr), true);
            }

            $spec_attr['ru_id'] = (isset($spec_attr['ru_id']) && $spec_attr['ru_id'] != 'undefined') ? intval($spec_attr['ru_id']) : -1;

            $spec_attr['is_title'] = isset($spec_attr['is_title']) ? $spec_attr['is_title'] : 0;
            $spec_attr['itemsLayout'] = isset($spec_attr['itemsLayout']) ? $spec_attr['itemsLayout'] : 'row4';
            $result['mode'] = request()->get('mode', '');
            $result['diff'] = request()->get('diff', '');
            $lift = request()->get('lift', '');

            //取得商品列表
            if (isset($spec_attr['goods_ids']) && !empty($spec_attr['goods_ids'])) {
                $spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']); //重置数据
            }
            if (isset($spec_attr['goods_ids']) && !empty($spec_attr['goods_ids'])) {
                $goods_info = BaseRepository::getExplode($spec_attr['goods_ids']);
                $res = Goods::select('goods_name', 'goods_id', 'goods_thumb', 'original_img', 'shop_price')->whereRaw(1);
                if (!empty($goods_info)) {
                    $res = $res->where('is_on_sale', '1')
                        ->where('is_delete', '0')
                        ->whereIn('goods_id', $goods_info);

                    if ($spec_attr['ru_id'] != '-1') {
                        $res = $res->where('user_id', $spec_attr['ru_id']);
                    }

                    //ecmoban模板堂 --zhuo start
                    if (config('shop.review_goods') == 1) {
                        $res = $res->whereIn('review_status', [3, 4, 5]);
                    }
                    //ecmoban模板堂 --zhuo end

                    if ($search_type == 'package') {
                        $res = $res->where('model_attr', 0); // 超值礼包只支持普通货品模式
                    }

                    $goods_list = BaseRepository::getToArrayGet($res);
                    foreach ($goods_list as $k => $v) {
                        $goods_list[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
                        $goods_list[$k]['shop_price'] = price_format($v['shop_price']);
                    }

                    $this->smarty->assign('goods_list', $goods_list);
                    $this->smarty->assign('goods_count', count($goods_list));
                }
            }

            if ($goods_id && $spec_attr['ru_id'] == -1) {
                $goods_info = get_admin_goods_info($goods_id);
                $adminru['ru_id'] = $goods_info['user_id'];
            } elseif ($spec_attr['ru_id'] > -1) {
                $adminru['ru_id'] = $spec_attr['ru_id'];
            }

            /* 取得分类列表 */
            //获取下拉列表 by wu start
            set_default_filter(0, $cat_id, $adminru['ru_id']); //设置默认筛选

            $seller_shop_cat = seller_shop_cat($adminru['ru_id']);
            $select_category_html = insert_select_category(0, 0, 0, 'cat_id', 0, 'category', $seller_shop_cat);
            $store_list = app(StoreCommonService::class)->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);
            $this->smarty->assign('parent_category', get_every_category($cat_id)); //上级分类导航
            $this->smarty->assign('select_category_html', $select_category_html);
            $this->smarty->assign('arr', $spec_attr);
            $this->smarty->assign("temp", "goods_info");
            $this->smarty->assign("goods_type", $goods_type);
            $this->smarty->assign("mode", $result['mode']);
            $this->smarty->assign("cat_id", $cat_id);
            $this->smarty->assign("lift", $lift);
            $this->smarty->assign("good_number", $good_number);
            $this->smarty->assign("search_type", $search_type);
            $this->smarty->assign("goods_id", $goods_id);
            $this->smarty->assign("suffix", $suffix);

            $user_fav_type = $spec_attr['user_fav_type'] ?? -1;
            $this->smarty->assign("user_fav_type", $user_fav_type);

            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        }
        /*--------------------------------------------------------*/
        //自定义模块弹窗
        /*--------------------------------------------------------*/
        elseif ($act == 'custom') {
            $result = ['content' => '', 'mode' => ''];
            $custom_content = unescape(request()->get('custom_content', ''));
            $custom_content = !empty($custom_content) ? stripslashes($custom_content) : '';
            $suffix = request()->get('suffix', '');//模板名称
            $result['mode'] = request()->get('mode', '');
            $result['diff'] = request()->get('diff', 0);
            $lift = request()->get('lift', '');

            if (config('shop.open_oss') == 1) {
                $bucket_info = $this->dscRepository->getBucketInfo();
                $endpoint = $bucket_info['endpoint'];
            } else {
                $endpoint = url('/');
            }

            if ($custom_content) {
                $desc_preg = get_goods_desc_images_preg($endpoint, $custom_content);
                $custom_content = $desc_preg['goods_desc'];
            }

            /* 创建 百度编辑器 wang 商家入驻 */
            create_ueditor_editor('custom_content', $custom_content, 486);

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("lift", $lift);
            $this->smarty->assign("suffix", $suffix);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        //首页广告位
        /*--------------------------------------------------------*/
        elseif ($act == 'home_adv') {
            load_helper('goods');
            $result = ['content' => '', 'mode' => ''];

            $cat_id = request()->get('cat_id', 0);
            $result['suffix'] = request()->get('suffix', '');//模板名称
            $result['diff'] = request()->get('diff', 0);
            $result['hierarchy'] = request()->get('hierarchy', 0);
            $result['activity_dialog'] = request()->get('activity_dialog', 0);
            $lift = request()->get('lift', '');
            $masterTitle = unescape(request()->get('masterTitle', ''));
            $spec_attr = stripslashes(request()->get('spec_attr', ''));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            } else {
                $spec_attr['needColor'] = '';
            }

            $result['mode'] = request()->get('mode', '');
            $needColor = '';
            //处理标题颜色
            $needColor = isset($spec_attr['needColor']) ? $spec_attr['needColor'] : '';
            unset($spec_attr['needColor']);

            //获取品牌列表
            if ($result['mode'] == 'h-brand') {
                //重置选择的品牌
                $spec_attr['brand_ids'] = resetBarnd($spec_attr['brand_ids'], 'brand', 'brand_id');

                $brand = $this->dialogManageService->getBrandList($spec_attr['brand_ids']);
                $this->smarty->assign('filter', $brand['filter']);
                $this->smarty->assign('recommend_brands', $brand['list']);
            } elseif ($result['mode'] == 'h-promo') {
                $spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']);//重置选择商品

                $time = gmtime();
                $where = [
                    'rs_id' => $adminru['rs_id'],
                    'time' => $time
                ];
                $list = get_home_adv_goods_list($where);

                $goods_list = $list['list'];

                $goods_ids = explode(',', $spec_attr['goods_ids']);
                if (!empty($goods_list)) {
                    foreach ($goods_list as $key => $val) {
                        $goods_list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);

                        if ($val['promote_price'] > 0 && $time >= $val['promote_start_date'] && $time <= $val['promote_end_date']) {
                            $goods_list[$key]['promote_price'] = price_format($this->goodsCommonService->getBargainPrice($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']));
                        } else {
                            $goods_list[$key]['promote_price'] = '';
                        }
                        $goods_list[$key]['shop_price'] = price_format($val['shop_price']);
                        if (!empty($goods_ids)) {
                            if ($val['goods_id'] > 0 && in_array($val['goods_id'], $goods_ids)) {
                                $goods_list[$key]['is_selected'] = 1;
                            }
                        }
                    }
                }
                $this->smarty->assign('filter', $list['filter']);
                $this->smarty->assign('goods_list', $goods_list);
                $this->smarty->assign('goods_count', count($goods_list));
            } elseif ($result['mode'] == 'h-sepmodule') {
                $spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']);//重置选择商品
                $res = Goods::select(
                    'promote_start_date',
                    'promote_end_date',
                    'promote_price',
                    'goods_name',
                    'goods_id',
                    'goods_thumb',
                    'shop_price',
                    'market_price',
                    'original_img'
                )
                    ->whereRaw(1);

                $time = gmtime();

                if (!empty($spec_attr['PromotionType'])) {
                    if ($spec_attr['PromotionType'] == 'exchange') {
                        $spec_attr['goods_ids'] = BaseRepository::getExplode($spec_attr['goods_ids']);
                        $spec_attr['goods_ids'] = ExchangeGoods::select('goods_id')
                            ->whereIn('goods_id', $spec_attr['goods_ids'])
                            ->where('review_status', 3)
                            ->where('is_exchange', 1);
                        $spec_attr['goods_ids'] = BaseRepository::getToArrayGet($spec_attr['goods_ids']);
                        $spec_attr['goods_ids'] = BaseRepository::getFlatten($spec_attr['goods_ids']);

                        $res = $res->with(['getExchangeGoods' => function ($query) {
                            $query->select('goods_id', 'exchange_integral')
                                ->where('review_status', 3)
                                ->where('is_exchange', 1);
                        }]);
                        $res = $res->where('is_delete', 0);
                    } elseif ($spec_attr['PromotionType'] == 'presale') {
                        $spec_attr['goods_ids'] = BaseRepository::getExplode($spec_attr['goods_ids']);
                        $spec_attr['goods_ids'] = PresaleActivity::select('goods_id')
                            ->whereIn('goods_id', $spec_attr['goods_ids'])
                            ->where('start_time', '<=', $time)
                            ->where('end_time', '>=', $time)
                            ->where('is_finished', 0);
                        $spec_attr['goods_ids'] = BaseRepository::getToArrayGet($spec_attr['goods_ids']);
                        $spec_attr['goods_ids'] = BaseRepository::getFlatten($spec_attr['goods_ids']);

                        $res = $res->with(['getPresaleActivity' => function ($query) use ($time) {
                            $query->select('goods_id', 'act_id', 'act_name', 'end_time', 'start_time')
                                ->where('review_status', 3)
                                ->where('start_time', '<=', $time)
                                ->where('end_time', '>=', $time)
                                ->where('is_finished', 0);
                        }]);
                        $res = $res->where('goods_id', '<>', '');
                    } elseif ($spec_attr['PromotionType'] == 'is_new') {
                        $spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']); //重置选择商品
                        if ($spec_attr['goods_ids']) {
                            $spec_attr['goods_ids'] = explode(',', $spec_attr['goods_ids']);
                        }

                        $res = $res->where('is_new', 1)
                            ->where('is_on_sale', 1)
                            ->where('is_delete', 0);
                    } elseif ($spec_attr['PromotionType'] == 'is_best') {
                        $spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']); //重置选择商品

                        if ($spec_attr['goods_ids']) {
                            $spec_attr['goods_ids'] = explode(',', $spec_attr['goods_ids']);
                        }

                        $res = $res->where('is_best', 1)
                            ->where('is_on_sale', 1)
                            ->where('is_delete', 0);
                    } elseif ($spec_attr['PromotionType'] == 'is_hot') {
                        $spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']); //重置选择商品、
                        if ($spec_attr['goods_ids']) {
                            $spec_attr['goods_ids'] = explode(',', $spec_attr['goods_ids']);
                        }

                        $res = $res->where('is_hot', 1)
                            ->where('is_on_sale', 1)
                            ->where('is_delete', 0);
                    } else {
                        if ($spec_attr['PromotionType'] == 'snatch') {
                            $act_type = GAT_SNATCH;
                        } elseif ($spec_attr['PromotionType'] == 'auction') {
                            $act_type = GAT_AUCTION;
                        } elseif ($spec_attr['PromotionType'] == 'group_buy') {
                            $act_type = GAT_GROUP_BUY;
                        }

                        $spec_attr['goods_ids'] = BaseRepository::getExplode($spec_attr['goods_ids']);
                        $spec_attr['goods_ids'] = GoodsActivity::select('goods_id')
                            ->whereIn('goods_id', $spec_attr['goods_ids'])
                            ->where('start_time', '<=', $time)
                            ->where('end_time', '>=', $time)
                            ->where('is_finished', 0)
                            ->where('act_type', $act_type);
                        $spec_attr['goods_ids'] = BaseRepository::getToArrayGet($spec_attr['goods_ids']);
                        $spec_attr['goods_ids'] = BaseRepository::getFlatten($spec_attr['goods_ids']);

                        $res = $res->with(['getGoodsActivity' => function ($query) use ($time, $act_type) {
                            $query->select('goods_id', 'act_id', 'act_name', 'end_time', 'start_time', 'ext_info')
                                ->where('review_status', 3)
                                ->where('start_time', '<=', $time)
                                ->where('end_time', '>=', $time)
                                ->where('is_finished', 0)
                                ->where('act_type', $act_type);
                        }]);
                        $res = $res->where('goods_id', '<>', '');
                    }

                    if (!$spec_attr['goods_ids']) {
                        $spec_attr['goods_ids'] = [];
                    }

                    $goods_ids = BaseRepository::getExplode($spec_attr['goods_ids']);

                    $res = $res->whereIn('goods_id', $goods_ids);

                    $goods_list = BaseRepository::getToArrayGet($res);

                    if (!empty($goods_list)) {
                        foreach ($goods_list as $key => $val) {
                            if (isset($val['get_goods_activity']) && !empty($val['get_goods_activity'])) {
                                $goods_list[$key]['act_id'] = $val['get_goods_activity']['act_id'];
                                $goods_list[$key]['act_name'] = $val['get_goods_activity']['act_name'];
                                $goods_list[$key]['end_time'] = $val['get_goods_activity']['end_time'];
                                $goods_list[$key]['start_time'] = $val['get_goods_activity']['start_time'];
                                $goods_list[$key]['ext_info'] = $val['get_goods_activity']['ext_info'];
                                $val = $goods_list[$key];
                            } elseif (isset($val['get_presale_activity']) && !empty($val['get_presale_activity'])) {
                                $goods_list[$key]['act_id'] = $val['get_presale_activity']['act_id'];
                                $goods_list[$key]['act_name'] = $val['get_presale_activity']['act_name'];
                                $goods_list[$key]['end_time'] = $val['get_presale_activity']['end_time'];
                                $goods_list[$key]['start_time'] = $val['get_presale_activity']['start_time'];
                                $val = $goods_list[$key];
                            } elseif (isset($val['get_exchange_goods']) && !empty($val['get_exchange_goods'])) {
                                $goods_list[$key]['exchange_integral'] = $val['get_exchange_goods']['exchange_integral'];
                                $val = $goods_list[$key];
                            }

                            $goods_list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);

                            $goods_list[$key]['is_selected'] = 1;
                            if ($val['promote_price'] > 0 && $time >= $val['promote_start_date'] && $time <= $val['promote_end_date']) {
                                $goods_list[$key]['promote_price'] = price_format($this->goodsCommonService->getBargainPrice($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']));
                            } else {
                                $goods_list[$key]['promote_price'] = '';
                            }
                            $goods_list[$key]['shop_price'] = price_format($val['shop_price']);
                            if (!empty($spec_attr['PromotionType']) && $spec_attr['PromotionType'] != 'exchange') {
                                $goods_list[$key]['goods_name'] = !empty($val['act_name']) ? $val['act_name'] : $val['goods_name'];
                                if ($spec_attr['PromotionType'] == 'auction') {
                                    $ext_info = unserialize($val['ext_info']);
                                    $auction = array_merge($val, $ext_info);
                                    $goods_list[$key]['promote_price'] = price_format($auction['start_price']);
                                } elseif ($spec_attr['PromotionType'] == 'group_buy') {
                                    $ext_info = unserialize($val['ext_info']);
                                    $group_buy = array_merge($val, $ext_info);
                                    $goods_list[$key]['promote_price'] = price_format($group_buy['price_ladder'][0]['price']);
                                }
                            }
                            if ($spec_attr['PromotionType'] == 'exchange') {
                                $goods_list[$key]['url'] = $this->dscRepository->buildUri('exchange_goods', ['gid' => $val['goods_id']], $val['goods_name']);
                                $goods_list[$key]['exchange_integral'] = __('admin::common.label_integral') . $val['exchange_integral'];
                            }
                            $goods_list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);

                            if (!empty($goods_ids)) {
                                if ($val['goods_id'] > 0 && in_array($val['goods_id'], $goods_ids)) {
                                    $goods_list[$key]['is_selected'] = 1;
                                }
                            }
                        }
                    }

                    $this->smarty->assign('goods_list', $goods_list);
                    $this->smarty->assign('goods_count', count($goods_list));
                }
                $this->smarty->assign('activity_dialog', $result['activity_dialog']);
            } elseif ($result['mode'] == 'h-seckill') {

                //验证存储的秒杀id
                if (!empty($spec_attr['goods_ids'])) {
                    foreach ($spec_attr['goods_ids'] as $k => $v) {
                        $spec_attr['goods_ids'][$k] = resetBarnd($v, 'seckill');
                    }
                }

                $time_bucket = !empty($spec_attr['time_bucket']) ? intval($spec_attr['time_bucket']) : 0;
                $now = gmtime();
                //获取秒杀时间段
                $sql = " SELECT id, title, begin_time, end_time FROM " . $GLOBALS['dsc']->table('seckill_time_bucket') . " ORDER BY begin_time ASC ";
                $stb = $this->db->getAll($sql);
                if ($stb) {
                    foreach ($stb as $k => $v) {
                        $v['local_end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']);
                        $arr[$k]['id'] = $v['id'];
                        $arr[$k]['title'] = $v['title'];
                        $arr[$k]['status'] = false;
                        $arr[$k]['is_end'] = false;
                        $arr[$k]['soon'] = false;
                        $begin_time = TimeRepository::getLocalStrtoTime($v['begin_time']);
                        $end_time = TimeRepository::getLocalStrtoTime($v['end_time']);

                        if ($begin_time < $now && $end_time > $now) {
                            $arr[$k]['status'] = true;
                        }
                        if ($end_time < $now) {
                            $arr[$k]['is_end'] = true;
                        }

                        $arr[$k]['goods_ids'] = isset($spec_attr['goods_ids'][$v['id']]) ? $spec_attr['goods_ids'][$v['id']] : '';
                    }
                }

                $this->smarty->assign('seckill_time_bucket', $arr);
                $this->smarty->assign('time_bucket', $time_bucket);
            }

            set_default_filter(0, $cat_id); //设置默认筛选
            $this->smarty->assign('parent_category', get_every_category($cat_id)); //上级分类导航

            $seller_shop_cat = seller_shop_cat($adminru['ru_id']);
            $select_category_html = insert_select_category(0, 0, 0, 'cat_id', 0, 'category', $seller_shop_cat);

            if (!empty($spec_attr)) {
                $spec_attr['goods_ids'] = isset($spec_attr['goods_ids']) && $spec_attr['goods_ids'] ? implode(',', $spec_attr['goods_ids']) : '';
            }

            $this->smarty->assign('select_category_html', $select_category_html);
            $this->smarty->assign('temp', $result['mode']);
            $this->smarty->assign('lift', $lift);
            $this->smarty->assign('needColor', $needColor);
            $this->smarty->assign('spec_attr', $spec_attr);
            $this->smarty->assign('hierarchy', $result['hierarchy']);
            $this->smarty->assign('masterTitle', $masterTitle);
            $this->smarty->assign('suffix', $result['suffix']);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        } //异步促销商品
        elseif ($act == 'changedpromotegoods') {
            load_helper('goods');
            $result = ['content' => '', 'mode' => ''];
            //传值
            $cat_id = request()->get('cat_id', 0);
            $keyword = request()->get('keyword', '');
            $goods_ids = request()->get('goods_ids', []);
            $PromotionType = request()->get('PromotionType', '');
            $recommend = request()->get('recommend', 0);
            $brand_id = request()->get('brand_id', 0);
            $type = request()->get('type', 0);
            $time_bucket = request()->get('time_bucket', 0);//秒杀时间段id
            $temp = request()->get('temp', '');
            $activity_dialog = request()->get('activity_dialog', 0);

            $time = gmtime();

            //  商品筛选  start
            $where = [
                'rs_id' => $adminru['rs_id'],
                'cat_id' => $cat_id,
                'temp' => $temp,
                'type' => $type,
                'goods_ids' => $goods_ids,
                'brand_id' => $brand_id,
                'promotion_type' => $PromotionType,
                'time' => $time,
                'keyword' => $keyword,
                'time_bucket' => $time_bucket
            ];
            $list = get_visual_promote_goods($where);

            $goods_list = $list['list'];
            $filter = $list['filter'];
            $filter['temp'] = $temp;
            $filter['time_bucket'] = $time_bucket;
            $filter['cat_id'] = $cat_id;
            $filter['keyword'] = $keyword;
            $filter['PromotionType'] = $PromotionType;
            $goods_ids = explode(',', $goods_ids);

            if (!empty($goods_list)) {
                foreach ($goods_list as $key => $val) {
                    if ($temp == 'h-seckill') {
                        $goods_list[$key]['promote_price'] = price_format($val['sec_price']); //秒杀价格
                        $goods_list[$key]['goods_id'] = $val['id']; //秒杀价格
                        $val['goods_id'] = $val['id'];
                    } else {
                        if ($val['promote_price'] > 0 && $time >= $val['promote_start_date'] && $time <= $val['promote_end_date']) {
                            $goods_list[$key]['promote_price'] = price_format($this->goodsCommonService->getBargainPrice($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']));
                        } else {
                            $goods_list[$key]['promote_price'] = '';
                        }
                        $goods_list[$key]['shop_price'] = price_format($val['shop_price']);
                    }
                    if (!empty($PromotionType) && $PromotionType != 'exchange') {
                        $goods_list[$key]['goods_name'] = !empty($val['act_name']) ? $val['act_name'] : $val['goods_name'];
                        if ($PromotionType == 'auction') {
                            $ext_info = unserialize($val['ext_info']);
                            $auction = array_merge($val, $ext_info);
                            $goods_list[$key]['promote_price'] = price_format($auction['start_price']);
                        } elseif ($PromotionType == 'group_buy') {
                            $ext_info = unserialize($val['ext_info']);
                            $group_buy = array_merge($val, $ext_info);
                            $goods_list[$key]['promote_price'] = price_format($group_buy['price_ladder'][0]['price']);
                        }
                    }
                    if ($PromotionType == 'exchange') {
                        $goods_list[$key]['exchange_integral'] = __('admin::common.label_integral') . $val['exchange_integral'];
                    }
                    $goods_list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);

                    if (!empty($goods_ids)) {
                        if ($val['goods_id'] > 0 && in_array($val['goods_id'], $goods_ids)) {
                            $goods_list[$key]['is_selected'] = 1;
                        }
                    }
                }
            }
            $this->smarty->assign('goods_count', count($goods_list));
            $this->smarty->assign('goods_list', $goods_list);
            $this->smarty->assign('filter', $filter);
            $this->smarty->assign('PromotionType', $PromotionType);
            $this->smarty->assign('action', "changedpromotegoods");
            $this->smarty->assign('url', "dialog.php");
            $this->smarty->assign('temp', "goods_list");
            $this->smarty->assign('recommend', $recommend);
            $this->smarty->assign('activity_dialog', $activity_dialog);
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        } //品牌分页回调
        elseif ($act == 'brand_query') {
            $result = ['content' => '', 'mode' => ''];
            $brand_ids = request()->get('brand_ids', '');
            $brand = $this->dialogManageService->getBrandList($brand_ids);
            $this->smarty->assign('filter', $brand['filter']);
            $this->smarty->assign('recommend_brands', $brand['list']);
            $this->smarty->assign('temp', 'brand_query');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        //首页楼层
        /*--------------------------------------------------------*/
        elseif ($act == 'homeFloor') {
            $result = ['content' => '', 'mode' => ''];
            $result['act'] = $act;
            $lift = request()->get('lift', '');
            $suffix = request()->get('suffix', '');//模板名称
            $result['hierarchy'] = request()->get('hierarchy', '');
            $result['diff'] = request()->get('diff', 0);
            $result['mode'] = request()->get('mode', '');
            $spec_attr = stripslashes(request()->get('spec_attr', ''));

            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);
            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }

            //处理图片链接
            if ($spec_attr['leftBannerLink']) {
                foreach ($spec_attr['leftBannerLink'] as $k => $v) {
                    $spec_attr['leftBannerLink'][$k] = str_replace(['＆'], '&', $v);
                }
            }
            if ($spec_attr['rightAdvLink']) {
                foreach ($spec_attr['rightAdvLink'] as $k => $v) {
                    $spec_attr['rightAdvLink'][$k] = str_replace(['＆'], '&', $v);
                }
            }
            if ($spec_attr['leftAdvLink']) {
                foreach ($spec_attr['leftAdvLink'] as $k => $v) {
                    $spec_attr['leftAdvLink'][$k] = str_replace(['＆'], '&', $v);
                }
            }

            //验证品牌
            $spec_attr['brand_ids'] = resetBarnd($spec_attr['brand_ids'], 'brand');
            $brand_ids = !empty($spec_attr['brand_ids']) ? trim($spec_attr['brand_ids']) : '';
            $cat_id = !empty($spec_attr['cat_id']) ? intval($spec_attr['cat_id']) : 0;
            $parent = '';
            $spec_attr['catChild'] = '';
            $spec_attr['Selected'] = '';
            if ($cat_id > 0) {
                $parent = Category::catInfo($spec_attr['cat_id'])->first();
                $parent = $parent ? $parent->toArray() : [];

                if ($parent['parent_id'] > 0) {
                    $spec_attr['catChild'] = $this->categoryService->catList($parent['parent_id']);
                    $spec_attr['Selected'] = $parent['parent_id'];
                } else {
                    $spec_attr['catChild'] = $this->categoryService->catList($spec_attr['cat_id']);
                    $spec_attr['Selected'] = $cat_id;
                }

                $spec_attr['juniorCat'] = $this->categoryService->catList($cat_id);
            }
            $arr = [];
            //处理商品id和分类id关系
            if (isset($spec_attr['cateValue']) && $spec_attr['cateValue']) {
                foreach ($spec_attr['cateValue'] as $k => $v) {
                    $arr[$k]['cat_id'] = $v;
                    $arr[$k]['cat_goods'] = $spec_attr['cat_goods'][$k];
                }
            }
            $spec_attr['catInfo'] = $arr;

            //处理标题特殊字符
            if (isset($spec_attr['rightAdvTitle']) && $spec_attr['rightAdvTitle']) {
                foreach ($spec_attr['rightAdvTitle'] as $k => $v) {
                    if ($v) {
                        $spec_attr['rightAdvTitle'][$k] = $v;
                    }
                }
            }

            if (isset($spec_attr['rightAdvSubtitle']) && $spec_attr['rightAdvSubtitle']) {
                foreach ($spec_attr['rightAdvSubtitle'] as $k => $v) {
                    if ($v) {
                        $spec_attr['rightAdvSubtitle'][$k] = $v;
                    }
                }
            }

            //获取楼层模板广告模式数组
            $floor_style = [];
            $floor_style = get_floor_style($result['mode']);

            //获取分类
            $cat_list = $this->categoryService->catList();

            //初始化模块图片数量
            $imgNumberArr = getAdvNum($result['mode']);
            $imgNumberArr = json_encode($imgNumberArr);
            $this->smarty->assign('cat_list', $cat_list);
            $this->smarty->assign('temp', $act);
            $this->smarty->assign('mode', $result['mode']);
            $this->smarty->assign('lift', $lift);
            $this->smarty->assign('spec_attr', $spec_attr);
            $this->smarty->assign('hierarchy', $result['hierarchy']);
            $this->smarty->assign('floor_style', $floor_style);
            $this->smarty->assign('imgNumberArr', $imgNumberArr);
            $this->smarty->assign('suffix', $suffix);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        } //cms频道头部广告
        elseif ($act == 'edit_cmsAdv') {
            $result = ['content' => '', 'mode' => ''];
            $spec_attr = [];
            $spec_attr['needColor'] = '';
            $result['diff'] = request()->get('diff', 0);
            $result['hierarchy'] = request()->get('hierarchy', 0);
            $lift = request()->get('lift', '');
            $masterTitle = unescape(request()->get('masterTitle', ''));
            $spec_attr = stripslashes(request()->get('spec_attr', ''));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);
            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            $result['mode'] = request()->get('mode', '');

            //处理图片链接
            if (isset($spec_attr['leftBannerLink']) && $spec_attr['leftBannerLink']) {
                foreach ($spec_attr['leftBannerLink'] as $k => $v) {
                    $spec_attr['leftBannerLink'][$k] = str_replace(['＆'], '&', $v);
                }
            }
            if (isset($spec_attr['rightAdvLink']) && $spec_attr['rightAdvLink']) {
                foreach ($spec_attr['rightAdvLink'] as $k => $v) {
                    $spec_attr['rightAdvLink'][$k] = str_replace(['＆'], '&', $v);
                }
            }
            //处理标题特殊字符
            if (isset($spec_attr['leftBannerTitle']) && $spec_attr['leftBannerTitle']) {
                foreach ($spec_attr['leftBannerTitle'] as $k => $v) {
                    if ($v) {
                        $spec_attr['leftBannerTitle'][$k] = $v;
                    }
                }
            }
            if (isset($spec_attr['leftAdvTitle']) && $spec_attr['leftAdvTitle']) {
                foreach ($spec_attr['leftAdvTitle'] as $k => $v) {
                    if ($v) {
                        $spec_attr['leftAdvTitle'][$k] = $v;
                    }
                }
            }
            //获取楼层模板广告模式数组
            $floor_style = [];
            $floor_style = get_floor_style($result['mode']);

            $imgNumberArr = getAdvNum($result['mode']);
            $imgNumberArr = json_encode($imgNumberArr);
            $this->smarty->assign('imgNumberArr', $imgNumberArr);
            $this->smarty->assign('floor_style', $floor_style);
            $this->smarty->assign('temp', $act);
            $this->smarty->assign('mode', $result['mode']);
            $this->smarty->assign('lift', $lift);
            $this->smarty->assign('spec_attr', $spec_attr);
            $this->smarty->assign('hierarchy', $result['hierarchy']);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        } //CMS频道资讯
        elseif ($act == 'edit_cmsarti') {
            $result = ['content' => '', 'mode' => ''];
            $result['diff'] = request()->get('diff', 0);
            $spec_attr = stripslashes(request()->get('spec_attr', 0));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            $result['mode'] = request()->get('mode', '');

            //处理数组
            $arr = [];
            if (!empty($spec_attr['sort'])) {
                foreach ($spec_attr['sort'] as $k => $v) {
                    $arr[$k]['cat_id'] = $k;
                    $arr[$k]['article_id'] = $spec_attr['article_id'][$k];
                    $res = ArticleCat::where('cat_id', $k);
                    $res = BaseRepository::getToArrayFirst($res);
                    $arr[$k]['cat_name'] = $res['cat_name'];
                    $arr[$k]['sort'] = $spec_attr['sort'][$k];
                    $arr[$k]['def_article_id'] = $spec_attr['def_article_id'][$k];
                    $sort_vals[$k] = isset($spec_attr['sort'][$k]) ? $spec_attr['sort'][$k] : 0;
                }
            }

            $cat_select = $this->articleCatManageService->cmsArticleCatList();
            $this->smarty->assign('cat_select', $cat_select);//获取文章分类
            $this->smarty->assign('temp', $act);
            $this->smarty->assign('mode', $result['mode']);
            $this->smarty->assign('spec_attr', $arr);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        } //CMS频道  商品
        elseif ($act == 'edit_cmsgoods') {
            $result = ['content' => '', 'mode' => ''];
            $result['diff'] = request()->get('diff', 0);
            $cat_id = request()->get('cat_id', 0);
            $spec_attr = stripslashes(request()->get('spec_attr', 0));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            $result['mode'] = request()->get('mode', '');
            if (isset($spec_attr['goods_ids']) && !empty($spec_attr['goods_ids'])) {
                $spec_attr['goods_ids'] = implode(',', $spec_attr['goods_ids']);
            }
            if (isset($spec_attr['article_ids']) && !empty($spec_attr['article_ids'])) {
                $spec_attr['article_ids'] = implode(',', $spec_attr['article_ids']);
            }

            /* 取得分类列表 */
            //获取下拉列表 by wu start
            set_default_filter(0, $cat_id, $adminru['ru_id']); //设置默认筛选

            $this->smarty->assign('parent_category', get_every_category($spec_attr['cat_id'])); //上级分类导航

            $seller_shop_cat = seller_shop_cat($adminru['ru_id']);
            $select_category_html = insert_select_category(0, 0, 0, 'cat_id', 0, 'category', $seller_shop_cat);

            $this->smarty->assign('select_category_html', $select_category_html);

            $this->smarty->assign('cat_select', article_cat_list_new(0, 0, true, 1));//获取文章分类

            $this->smarty->assign('temp', $act);
            $this->smarty->assign('mode', $result['mode']);
            $this->smarty->assign('spec_attr', $spec_attr);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        }
        /*--------------------------------------------------------*/
        //耧播右侧
        /*--------------------------------------------------------*/
        elseif ($act == 'vipEdit') {
            $result = ['content' => '', 'mode' => ''];
            $result['act'] = $act;
            $result['suffix'] = request()->get('suffix', '');//模板名称

            $spec_attr = stripslashes(request()->get('spec_attr', 0));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            $index_article_cat = isset($spec_attr['index_article_cat']) ? trim($spec_attr['index_article_cat']) : '';
            $quick_url = isset($spec_attr['quick_url']) ? explode(',', $spec_attr['quick_url']) : [];
            $quick_name = isset($spec_attr['quick_name']) ? $spec_attr['quick_name'] : [];
            $style_icon = isset($spec_attr['style_icon']) ? $spec_attr['style_icon'] : [];
            //获取快捷入口数组
            $count = COUNT($quick_url);//数组长度
            /*合并数组*/
            $arr = [];
            for ($i = 0; $i < $count; $i++) {
                $arr[$i]['quick_url'] = $quick_url[$i];
                $arr[$i]['quick_name'] = $quick_name[$i];
                $arr[$i]['style_icon'] = $style_icon[$i];
                switch ($i) {
                    case 0:
                        $arr[$i]['zh_cn'] = __('admin::common.num_1');
                        break;

                    case 1:
                        $arr[$i]['zh_cn'] = __('admin::common.num_2');
                        break;

                    case 2:
                        $arr[$i]['zh_cn'] = __('admin::common.num_3');
                        break;

                    case 3:
                        $arr[$i]['zh_cn'] = __('admin::common.num_4');
                        break;

                    case 4:
                        $arr[$i]['zh_cn'] = __('admin::common.num_5');
                        break;

                    case 5:
                        $arr[$i]['zh_cn'] = __('admin::common.num_6');
                        break;
                }
            }
            $this->smarty->assign('temp', $act);
            $this->smarty->assign('quick', $arr);
            $this->smarty->assign('index_article_cat', $index_article_cat);
            $this->smarty->assign('suffix', $result['suffix']);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        //头部导航
        /*--------------------------------------------------------*/
        elseif ($act == 'nav_mode') {
            $result = ['content' => '', 'mode' => ''];
            $result['mode'] = request()->get('mode', '');
            $result['topic'] = request()->get('topic', 0);
            $suffix = request()->get('suffix', '');//模板名称
            $this->smarty->assign('temp', $result['mode']);

            $spec_attr = stripslashes(request()->get('spec_attr', 0));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            /* 获取导航数据 */
            $res = Nav::where('type', 'middle');
            $res = BaseRepository::getToArrayGet($res);
            $this->smarty->assign('system', $res);
            $this->smarty->assign('topic_type', $result['topic']);
            $this->smarty->assign('navigator', $spec_attr);
            $this->smarty->assign('suffix', $suffix);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        }
        /*--------------------------------------------------------*/
        //模板信息弹框
        /*--------------------------------------------------------*/
        elseif ($act == 'template_information') {
            $result = ['content' => '', 'mode' => ''];
            $code = request()->get('code', '');
            $check = request()->get('check', 0);
            $action = request()->get('action', '');
            $temp_id = request()->get('temp_id', 0);
            $template_type = request()->get('template_type', '');
            $template_mall_info = [];
            $theme = config('shop.template');
            if ($temp_id > 0 && $template_type == 'seller') {
                $res = TemplateMall::where('temp_id', $temp_id);
                $template_mall_info = BaseRepository::getToArrayFirst($res);
                $theme = '';
            }
            if ($code) {
                $template_info = get_seller_template_info($code, 0, $theme);
                $this->smarty->assign('template', $template_info);
            }
            $this->smarty->assign('template_mall_info', $template_mall_info);
            $this->smarty->assign('template_type', $template_type);
            $this->smarty->assign('code', $code);
            $this->smarty->assign("temp", $act);
            $this->smarty->assign("check", $check);
            $this->smarty->assign("temp_id", $temp_id);
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 地图弹出窗口  by kong
        /*------------------------------------------------------ */
        elseif ($act == 'getmap_html') {
            $result = ['content' => '', 'sgs' => ''];
            $temp = !empty($act) ? $act : '';
            $this->smarty->assign("temp", $temp);
            $result['sgs'] = $temp;
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        //头部模块弹窗
        /*--------------------------------------------------------*/
        elseif ($act == 'header') {
            $result = ['content' => '', 'mode' => ''];
            $arr = [];
            $this->smarty->assign("temp", $act);
            $spec_attr = stripslashes(request()->get('spec_attr', 0));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            $spec_attr['header_type'] = isset($spec_attr['header_type']) ? $spec_attr['header_type'] : 'defalt_type';
            $custom_content = unescape(request()->get('custom_content', ''));
            $custom_content = !empty($custom_content) ? stripslashes($custom_content) : '';
            $result['mode'] = request()->get('mode', '');
            $spec_attr['suffix'] = request()->get('suffix', '');

            /* 创建 百度编辑器 wang 商家入驻 */
            create_ueditor_editor('custom_content', $custom_content, 486);

            $this->smarty->assign('content', $spec_attr);
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        }
        /*--------------------------------------------------------*/
        //导航模块弹窗
        /*--------------------------------------------------------*/
        elseif ($act == 'navigator') {
            $result = ['content' => '', 'mode' => ''];
            $topic_type = request()->get('topic_type', '');
            /*处理数组*/
            $spec_attr['target'] = '';
            $spec_attr = stripslashes(request()->get('spec_attr', 0));
            $spec_attr = urldecode($spec_attr);
            $spec_attr = json_str_iconv($spec_attr);

            if (!empty($spec_attr)) {
                $spec_attr = dsc_decode($spec_attr, true);
            }
            $result['diff'] = request()->get('diff', 0);
            unset($spec_attr['target']);
            $navigator = $spec_attr;

            $spec_attr['target'] = isset($spec_attr['target']) ? $spec_attr['target'] : '_blank';
            $this->smarty->assign("temp", $act);
            $this->smarty->assign('attr', $spec_attr);
            $result['mode'] = request()->get('mode', '');
            $result['content'] = $this->smarty->fetch('library/shop_banner.lbi');
            return response()->json($result);
        } //商家订单列表导出弹窗
        elseif ($act == 'merchant_download') {
            $result = ['content' => ''];
            $page_count = request()->get('page_count', 0);//总页数
            $filename = request()->get('filename', '');//处理导出数据的文件
            $fileaction = request()->get('fileaction', '');//处理导出数据的入口
            $lastfilename = request()->get('lastfilename', '');//最后处理导出的文件
            $lastaction = request()->get('lastaction', '');//最后处理导出的程序入口

            $this->smarty->assign('page_count', $page_count);
            $this->smarty->assign('filename', $filename);
            $this->smarty->assign('fileaction', $fileaction);
            $this->smarty->assign('lastfilename', $lastfilename);
            $this->smarty->assign('lastaction', $lastaction);

            session()->forget('merchants_download_content');//初始化导出对象
            $result['content'] = $this->smarty->fetch('library/merchant_download.lbi');
            return response()->json($result);
        } //包邮券设置不包邮地区弹窗
        elseif ($act == 'set_free_shipping') {
            $result = ['content' => ''];
            $region_ids = request()->get('region_ids', '');
            $region_ids = !empty($region_ids) ? explode(',', $region_ids) : [];
            $res = MerchantsRegionArea::whereRaw(1);
            $region_list = BaseRepository::getToArrayGet($res);
            $count = count($region_list);
            for ($i = 0; $i < $count; $i++) {
                $region_list[$i]['add_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $region_list[$i]['add_time']);
                $area = $this->dialogManageService->ajaxGetAreaList($region_list[$i]['ra_id'], $region_ids);
                $region_list[$i]['area_list'] = $area;
            }
            $this->smarty->assign('region_list', $region_list);
            $this->smarty->assign('temp', 'set_free_shipping');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        } //商品详情页  添加属性分类，添加类型，添加属性弹窗
        elseif ($act == 'add_goods_type_cat') {
            $result = ['content' => ''];

            $type = request()->get('type', '');
            $goods_id = request()->get('goods_id', 0);
            if ($goods_id > 0) {
                $user_id = Goods::where('goods_id', $goods_id)->value('user_id');
                $user_id = $user_id ? $user_id : 0;
            } else {
                $user_id = $adminru['ru_id'];
            }
            if ($type == 'add_goods_type_cat' || $type == 'add_goods_type') {
                $cat_level = get_type_cat_arr(0, 0, 0, $user_id);
                $this->smarty->assign("cat_level", $cat_level);
            } elseif ($type == 'attribute_add') {
                $this->dscRepository->helpersLang('attribute', 'admin');

                $this->smarty->assign('lang', BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog'), __('admin::attribute')]));

                $add_edit_cenetent = __('admin::common.temporary_not_attr_power');
                $goods_type = request()->get('goods_type', 0);
                $attr = [
                    'attr_id' => 0,
                    'cat_id' => $goods_type,
                    'attr_cat_type' => 0, //by zhang
                    'attr_name' => '',
                    'attr_input_type' => 0,
                    'attr_index' => 0,
                    'attr_values' => '',
                    'attr_type' => 0,
                    'is_linked' => 0,
                ];
                $this->smarty->assign('attr', $attr);
                $this->smarty->assign('attr_groups', get_attr_groups($attr['cat_id']));
                /* 取得商品分类列表 */
                $this->smarty->assign('goods_type_list', goods_type_list($attr['cat_id']));
            }
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('temp', $type);
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }
        /* -------------------------------------------------------- */
        // ajax添加品牌
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxBrand') {
            $this->dscRepository->helpersLang('brand', 'admin');

            $result = ['content' => '', 'mode' => ''];

            $this->smarty->assign('is_need', config('shop.template') == 'ecmoban_dsc2017' ? 1 : 0);
            $this->smarty->assign("temp", $act);
            $this->smarty->assign("lang", BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog'), __('admin::brand')]));
            $this->smarty->assign('form_action', 'brand_insert');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加品牌操作
        /* -------------------------------------------------------- */
        elseif ($act == 'brand_insert') {
            $result = ['content' => '', 'message' => '', 'error' => 0];

            $data['is_show'] = request()->get('is_show', 0);
            $data['brand_desc'] = request()->get('brand_desc', '');
            $data['brand_name'] = request()->get('brand_name', '');
            $data['brand_letter'] = request()->get('brand_letter', '');
            $data['brand_first_char'] = strtoupper(request()->get('brand_first_char', ''));
            $data['site_url'] = sanitize_url(request()->get('site_url', ''));
            $data['sort_order'] = request()->get('sort_order', 50);

            $data['brand_letter'] = $data['brand_letter'] ?? '';
            $data['site_url'] = $data['site_url'] ?? '';
            $data['sort_order'] = $data['sort_order'] ?? '';

            $is_only = Brand::where('brand_name', $data['brand_name'])->count();
            if ($is_only > 0) {
                $result['error'] = 2;
                $result['message'] = __('admin::dialog.brand_name_repeat');
                return response()->json($result);
            }

            /* 处理图片 */
            $brand_logo = request()->file('brand_logo');
            $data['brand_logo'] = $img_name = $brand_logo && $brand_logo->isValid() ? basename($image->upload_image($brand_logo, 'brandlogo')) : 0;
            $this->dscRepository->getOssAddFile([DATA_DIR . '/brandlogo/' . $img_name]);

            /* 品牌专区大图 by wu start */
            $index_img = request()->file('index_img');
            $data['index_img'] = $index_img = $index_img && $index_img->isValid() ? basename($image->upload_image($index_img, 'indeximg')) : 0;
            $this->dscRepository->getOssAddFile([DATA_DIR . '/indeximg/' . $index_img]);
            /* 品牌专区大图 by wu end */

            /* 品牌背景图 start */
            $brand_bg = request()->file('brand_bg');
            $data['brand_bg'] = $brand_bg = $brand_bg && $brand_bg->isValid() ? basename($image->upload_image($brand_bg, 'brandbg')) : 0;
            $this->dscRepository->getOssAddFile([DATA_DIR . '/brandbg/' . $brand_bg]);
            /* 品牌背景图 end */

            /* 插入数据 */
            $brand_id = Brand::insertGetId($data);
            if ($brand_id > 0) {
                $is_recommend = request()->get('is_recommend', 0);
                $other = ['brand_id' => $brand_id, 'is_recommend' => $is_recommend];
                BrandExtend::insert($other);

                admin_log($data['brand_name'], 'add', 'brand');

                /* 清除缓存 */
                clear_cache_files();
                $result['message'] = __('admin::dialog.add_brand_success');
            } else {
                $result['error'] = 1;
                $result['message'] = __('admin::dialog.add_brand_fail');
            }
            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加活动标签
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxLabel') {

            $result = ['content' => '', 'mode' => ''];

            $filter_list = GoodsLabel::query()->where('status', 1)->where('type', 0)->select('id', 'label_name', 'label_image');
            $filter_list = BaseRepository::getToArrayGet($filter_list);

            if ($filter_list) {
                foreach ($filter_list as $k => $list) {
                    $filter_list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($list['label_image']);
                }
            }

            $goods_id = (int)request()->input('goods_id', 0);
            $label_list = $this->goodsCommonService->getGoodsLabelForAdmin($goods_id, session('label_use_id' . session('admin_id')));

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("filter_list", $filter_list);
            $this->smarty->assign("label_list", $label_list);
            $this->smarty->assign('form_action', 'label_insert');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加服务标签
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxServicesLabel') {

            $result = ['content' => '', 'mode' => ''];

            $filter_list = GoodsServicesLabel::query()->where('status', 1)->where('label_code', '<>', 'no_reason_return')->select('id', 'label_name', 'label_image');
            $filter_list = BaseRepository::getToArrayGet($filter_list);

            if ($filter_list) {
                foreach ($filter_list as $k => $list) {
                    $filter_list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($list['label_image']);
                }
            }

            $goods_id = (int)request()->input('goods_id', 0);
            $label_list = $this->goodsCommonService->getGoodsServicesLabelForAdmin($goods_id, session('services_label_use_id' . session('admin_id')));

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("filter_list", $filter_list);
            $this->smarty->assign("label_list", $label_list);
            $this->smarty->assign('form_action', 'services_label_insert');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加商品关键词
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxGoodsKeyword') {

            $list_div = "listDiv2";
            $result = ['content' => '', 'error' => '', 'class' => $list_div];

            $this->smarty->assign("temp", $act);

            $keywordList = $this->goodsManageService->getGoodsSelectKeyword($adminru['ru_id']);
            $this->smarty->assign("keyword_list", $keywordList['keyword_list']);
            $this->smarty->assign('filter', $keywordList['filter']);
            $this->smarty->assign('record_count', $keywordList['record_count']);
            $this->smarty->assign('page_count', $keywordList['page_count']);
            $this->smarty->assign('full_page', $keywordList['filter']['full_page']);
            $this->smarty->assign("goods_id", $keywordList['filter']['goods_id']);
            $this->smarty->assign("class", $list_div);

            $lang = BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog'), lang('admin/goods_keyword')]);
            $this->smarty->assign("lang", $lang);
            $this->smarty->assign('form_action', 'goods_keyword_insert');

            set_default_filter(); //设置默认筛选

            $result['filter'] = $keywordList['filter'];
            $result['page_count'] = $keywordList['page_count'];
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加商品关键词操作
        /* -------------------------------------------------------- */
        elseif ($act == 'goods_keyword_insert') {
            $result = ['content' => '', 'message' => '', 'error' => 0, 'keywords' => ''];

            $goods_id = (int)request()->input('goods_id', 0);
            $keyword_id = request()->input('keyword_id', []);
            $keyword_id = DscEncryptRepository::filterValInt($keyword_id);
            $keyword_id = BaseRepository::getExplode($keyword_id);

            $keywordList = KeywordList::select('id', 'name')->whereIn('id', $keyword_id);
            $keywordList = BaseRepository::getToArrayGet($keywordList);

            $insertKeywordList = $keywordList;
            $updateKeywordList = [];

            $keyList = BaseRepository::getKeyPluck($keywordList, 'id');
            $goodsKeyList = GoodsKeyword::select('keyword_id')->where('goods_id', $goods_id)->pluck('keyword_id');
            $goodsKeyList = BaseRepository::getToArray($goodsKeyList);

            $goodsDiffKeyword = BaseRepository::getArrayDiff($goodsKeyList, $keyword_id);

            if ($goodsKeyList && !empty($goods_id)) {
                $keyListDiff = BaseRepository::getArrayDiff($keyList, $goodsKeyList);
                $keyListDiff = array_values($keyListDiff);
                $keyListIntersect = BaseRepository::getArrayIntersect($keyList, $goodsKeyList);
                $keyListIntersect = array_values($keyListIntersect);

                if ($keyListDiff) {
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'id',
                                'value' => $keyListDiff
                            ]
                        ]
                    ];
                    $insertKeywordList = BaseRepository::getArraySqlGet($keywordList, $sql);
                } else {
                    $insertKeywordList = [];
                }

                /* 预留 */
                if ($keyListIntersect) {
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'id',
                                'value' => $keyListIntersect
                            ]
                        ]
                    ];
                    $updateKeywordList = BaseRepository::getArraySqlGet($keywordList, $sql);
                }
            }

            $list = BaseRepository::getColumn($insertKeywordList, 'name', 'id');
            $nameList = $list ? implode($list, ' ') : '';

            if ($nameList && !empty($goods_id)) {
                $time = TimeRepository::getGmTime();
                $arr = [];
                foreach ($list as $key => $val) {
                    $arr[$key]['keyword_id'] = $key;
                    $arr[$key]['goods_id'] = $goods_id;
                    $arr[$key]['add_time'] = $time;
                }

                $arr = array_values($arr);

                GoodsKeyword::insert($arr);
            }

            if (empty($goods_id)) {
                $keyword_list = $keywordList;
            } else {
                if ($goodsDiffKeyword) {
                    GoodsKeyword::where('goods_id', $goods_id)->whereIn('keyword_id', $goodsDiffKeyword)->delete();
                }

                $goods = Goods::select('goods_id', 'keywords', 'user_id')->where('goods_id', $goods_id);
                $goods = BaseRepository::getToArrayFirst($goods);

                $keyword_list = $this->goodsManageService->getGoodsKeywordInfo($goods);
            }

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("keyword_list", $keyword_list);

            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            $list = BaseRepository::getColumn($keyword_list, 'name', 'id');
            $nameList = $list ? implode($list, ' ') : '';
            $result['keywords'] = $nameList;
            $result['message'] = __("admin::goods.goods_keyword_succed");

            if (!empty($goods_id)) {
                Goods::where('goods_id', $goods_id)->update([
                    'keywords' => $nameList
                ]);
            }

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax删除商品关键词
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxGoodsDelKeyword') {
            $id = (int)request()->input('id', 0);
            $goods_id = (int)request()->input('goods_id', 0);
            $name = addslashes(request()->input('name', ''));

            $goods = Goods::select('goods_id', 'keywords', 'user_id')->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($goods);
            $this->smarty->assign("temp", $act);

            $count = GoodsKeyword::where('goods_id', $goods_id)->count('id');

            if ($count > 0) {
                GoodsKeyword::where('goods_id', $goods_id)->where('id', $id)->delete();

                $keyword_list = $this->goodsManageService->getGoodsKeywordInfo($goods);
            } else {

                $keyword_list = $this->goodsManageService->getGoodsKeywordInfo($goods);

                $sql = [
                    'where' => [
                        [
                            'name' => 'name',
                            'value' => $name,
                            'condition' => '<>'
                        ]
                    ]
                ];
                $keyword_list = BaseRepository::getArraySqlGet($keyword_list, $sql);
            }

            $this->smarty->assign("keyword_list", $keyword_list);
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            $list = BaseRepository::getColumn($keyword_list, 'name', 'id');
            $nameList = $list ? implode($list, ' ') : '';
            $result['keywords'] = $nameList;

            Goods::where('goods_id', $goods_id)->update([
                'keywords' => $nameList
            ]);

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加运费模板
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxTransport') {
            $this->dscRepository->helpersLang('goods_transport', 'admin');

            load_helper('order');

            $result = ['content' => '', 'mode' => ''];

            $tid = request()->get('tid', 0);
            $ru_id = request()->get('ru_id', 0);

            $shipping_id = 0;

            $transport_info = [];
            $shipping_tpl = [];
            if ($tid) {
                $form_action = 'transport_update';

                $trow = get_goods_transport($tid);

                if ($tid > 0) {
                    $transport_info = $trow;
                    $shipping_tpl = get_transport_shipping_list($tid, $ru_id);
                }
            } else {
                $form_action = 'transport_insert';

                GoodsTransportTpl::where('admin_id', $admin_id)->where('tid', 0)->delete();
            }

            $this->smarty->assign('shipping_tpl', $shipping_tpl);
            $this->smarty->assign('tid', $tid);
            $this->smarty->assign('ru_id', $ru_id);
            $this->smarty->assign('transport_info', $transport_info);
            $this->smarty->assign('transport_area', $this->dialogManageService->getTransportArea($tid));
            $this->smarty->assign('transport_express', $this->dialogManageService->getTransportExpress($tid));

            //快递列表
            $shipping_list = shipping_list();
            foreach ($shipping_list as $key => $val) {
                //剔除手机快递
                if (substr($val['shipping_code'], 0, 5) == 'ship_') {
                    unset($shipping_list[$key]);
                    continue;
                }
                /* 剔除上门自提 */
                if ($val['shipping_id'] == 17) {
                    unset($shipping_list[$key]);
                }
            }
            $this->smarty->assign('shipping_list', $shipping_list);

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("lang", BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog'), __('admin::goods_transport')]));
            $this->smarty->assign('form_action', $form_action);
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加运费模板操作
        /* -------------------------------------------------------- */
        elseif ($act == 'transport_insert' || $act == 'transport_update') {
            $result = ['content' => '', 'message' => '', 'error' => 0];

            $data = [];
            $data['tid'] = request()->get('tid', 0);
            $data['ru_id'] = request()->get('ru_id', 0);
            $data['type'] = request()->get('type', 0);
            $data['title'] = request()->get('title', '');
            $data['freight_type'] = request()->get('freight_type', 0);
            $data['update_time'] = gmtime();
            $data['free_money'] = request()->get('free_money', 0);
            $data['shipping_title'] = request()->get('shipping_title', 0);
            $sprice = request()->get('sprice', []);
            $shipping_fee = request()->get('shipping_fee', []);
            $s_tid = $data['tid'];
            $res = GoodsTransportTpl::whereRaw(1);
            if ($act == 'transport_update') {

                if ($data['freight_type'] == 0) {
                    // 自定义运费模板
                    $extend = GoodsTransportExtend::where('tid', $s_tid)->first();
                    if (empty($extend) || empty($extend->top_area_id)) {
                        // 地区不能为空
                        return response()->json(['error' => 1, 'message' => '配送地区不能为空']);
                    }
                    $express = GoodsTransportExpress::where('tid', $s_tid)->first();
                    if (empty($express) || empty($express->shipping_id)) {
                        // 快递方式不能为空
                        return response()->json(['error' => 1, 'message' => '快递方式不能为空']);
                    }

                } else {
                    // 快递模板
                    $tpl = GoodsTransportTpl::where('tid', $s_tid)->first();
                    if (empty($tpl) || empty($tpl->shipping_id)) {
                        // 配送方式不能为空
                        return response()->json(['error' => 1, 'message' => '配送方式且运费模板不能为空']);
                    }
                }

                $result['message'] = __('admin::dialog.edit_freight_template_success');
                GoodsTransport::where('tid', $data['tid'])->update($data);
                $tid = $s_tid;

                $res = $res->where('tid', $tid);
            } else {
                $result['message'] = __('admin::dialog.edit_freight_template_success');
                $tid = GoodsTransport::insertGetId($data);
                if ($tid > 0) {
                    GoodsTransportExtend::where('tid', 0)->where('admin_id', $admin_id)->update(['tid' => $tid]);
                    GoodsTransportExpress::where('tid', 0)->where('admin_id', $admin_id)->update(['tid' => $tid]);
                }

                $res = $res->where('admin_id', $admin_id)->where('tid', 0);
            }

            //处理运费模板
            if ($data['freight_type'] > 0) {
                $sessionTid = session()->has($s_tid) ? session($s_tid) : [];

                if (!isset($sessionTid['tpl_id']) && empty($sessionTid['tpl_id'])) {
                    $tpl_id = BaseRepository::getToArrayGet($res->select('id'));
                    $tpl_id = BaseRepository::getFlatten($tpl_id);
                } else {
                    if (isset($sessionTid['tpl_id'])) {
                        $tpl_id = BaseRepository::getExplode($sessionTid['tpl_id']);
                    } else {
                        $tpl_id = [];
                    }
                }
                if (!empty($tpl_id)) {
                    GoodsTransportTpl::where('admin_id', $admin_id)->where('tid', 0)->whereIn('id', $tpl_id)->update(['tid' => $tid]);

                    session()->forget($s_tid . '.tpl_id');
                }
            }

            //处理地区数据
            if (count($sprice) > 0) {
                foreach ($sprice as $key => $val) {
                    $info = [];
                    $info['sprice'] = $val;
                    GoodsTransportExtend::where('id', $key)->update($info);
                }
            }

            //处理快递数据
            if (count($shipping_fee) > 0) {
                foreach ($shipping_fee as $key => $val) {
                    $info = [];
                    $info['shipping_fee'] = $val;
                    GoodsTransportExpress::where('id', $key)->update($info);
                }
            }

            $this->smarty->assign("temp", "transport_reload");
            $transportInfo = GoodsTransport::select('tid', 'title')->where('tid', $tid);
            $transportInfo = BaseRepository::getToArrayFirst($transportInfo);

            $this->smarty->assign("transportInfo", $transportInfo);
            $this->smarty->assign('transport_list', get_table_date("goods_transport", "ru_id='{$data['ru_id']}'", ['tid, title'], 1));

            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加文章
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxArticle') {
            $this->dscRepository->helpersLang('article', 'admin');

            $result = ['content' => '', 'mode' => ''];

            /* 创建 html editor */
            create_html_editor('FCKeditor1');

            /* 初始化 */
            $article = [];
            $article['is_open'] = 1;

            /* 取得分类、品牌 */
            set_default_filter(); //设置默认筛选

            /* 清理关联商品 */
            GoodsArticle::where('article_id', 0)->delete();

            $this->smarty->assign('filter_category_list', get_category_list());

            $id = request()->get('id', 0);
            if ($id) {
                $this->smarty->assign('cur_id', $id);
            }
            $this->smarty->assign('article', $article);
            $this->smarty->assign('cat_select', article_cat_list_new(0));
            $this->smarty->assign("temp", $act);
            $this->smarty->assign("lang", BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog'), __('admin::article')]));
            $this->smarty->assign('form_action', 'article_insert');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加文章操作
        /* -------------------------------------------------------- */
        elseif ($act == 'article_insert') {
            $result = ['content' => '', 'message' => '', 'error' => 0];

            $target_select = request()->get('target_select', []);
            $data['title'] = request()->get('title', '');
            $data['file_url'] = request()->get('file_url', '');
            $data['content'] = request()->get('FCKeditor1', '');
            $data['cat_id'] = request()->get('article_cat', 0);
            $data['article_type'] = request()->get('article_type', 0);
            $data['is_open'] = request()->get('is_open', 0);
            $data['author'] = request()->get('author', '');
            $data['author_email'] = request()->get('author_email', '');
            $data['keywords'] = request()->get('keywords', '');
            $data['link'] = request()->get('link_url', '');
            $data['description'] = request()->get('description', '');
            $data['add_time'] = gmtime();

            /* 检查是否重复 */
            $is_only = Article::where('title', $data['title'])->where('cat_id', $data['cat_id'])->count();

            if ($is_only > 0) {
                $result['error'] = 2;
                $result['message'] = __('admin::dialog.article_title_repeat');
                return response()->json($result);
            }

            /* 允许上传的文件类型 */
            $allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|';

            /* 取得文件地址 */
            $file_url = '';
            $file = request()->file('file');
            if ($file && $file->isValid()) {
                // 检查文件格式
                if (!check_file_type($file->getRealPath(), $file->getClientOriginalName(), $allow_file_types)) {
                    return sys_msg(__('admin::article.article_title_repeat'));
                }

                // 复制文件
                $file_dir = storage_public(DATA_DIR . "/gallery_album/");
                if (!file_exists($file_dir)) {
                    make_dir($file_dir);
                }

                $filename = Image::random_filename() . substr($file->getClientOriginalName(), strpos($file->getClientOriginalName(), '.'));
                $dir = DATA_DIR . "/gallery_album/" . $filename;
                $path = storage_public($dir);

                //组合路径，并删除storage前的路径
                if (move_upload_file($file->getRealPath(), $path)) {
                    $file_url = $dir;
                }
            }

            if ($file_url == '') {
                $file_url = $data['file_url'];
                $data['open_type'] = 0;
            } else {
                $data['open_type'] = $data['content'] == '' ? 1 : 2;
                $this->dscRepository->getOssAddFile([$file_url]);
            }
            $data['file_url'] = $file_url;
            $article_id = Article::insertGetId($data);

            if ($article_id > 0) {
                /* 处理关联商品 */

                foreach ($target_select as $k => $val) {
                    $other = ['goods_id' => $val, 'article_id' => $article_id];
                    GoodsArticle::insert($other);
                }

                admin_log($data['title'], 'add', 'article');

                clear_cache_files(); // 清除相关的缓存文件
                $result['message'] = __('admin::dialog.add_article_success');
            } else {
                $result['error'] = 1;
                $result['message'] = __('admin::dialog.add_article_fail');
            }

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加地区
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxArea') {
            $this->dscRepository->helpersLang('area_manage', 'admin');

            $exc = new Exchange($this->dsc->table('region'), $db, 'region_id', 'region_name');
            $result = ['content' => '', 'mode' => ''];
            $is_ajax = 1;

            /* 取得参数：上级地区id */
            $region_id = request()->get('pid', 0);
            $this->smarty->assign('parent_id', $region_id);

            /* 取得列表显示的地区的类型 */
            if ($region_id == 0) {
                $region_type = 0;
            } else {
                $region_type = $exc->get_name($region_id, 'region_type') + 1;
            }
            $this->smarty->assign('region_type', $region_type);

            /* 获取地区列表 */
            $region_arr = area_list($region_id);
            $this->smarty->assign('region_arr', $region_arr);
            $area_top = '-';
            /* 当前的地区名称 */
            if ($region_id > 0) {
                $area_name = $exc->get_name($region_id);
                $area_top = $area_name;
                if ($region_arr) {
                    $area = $region_arr[0]['type'];
                }
            } else {
                $area = __('admin::common.country');
            }
            $this->smarty->assign('area_top', $area_top);
            $this->smarty->assign('area_here', $area);

            $action_link = '';
            /* 返回上一级的链接 */
            if ($region_id > 0) {
                $parent_id = $exc->get_name($region_id, 'parent_id');
                $is_ajax = 0;
                $action_link = ['text' => __('admin::warehouse.back_page'), 'href' => 'javascript:;', 'pid' => $parent_id, 'type' => 1];
            }

            $this->smarty->assign('is_ajax', $is_ajax);

            if ($action_link) {
                $this->smarty->assign('action_link', $action_link);
            }

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("lang", BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog')]));
            $this->smarty->assign('form_action', 'area_insert');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // 授权认证
        /* -------------------------------------------------------- */
        elseif ($act == 'empower') {
            $result['content'] = $this->smarty->fetch('library/empower.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // 授权认证
        /* -------------------------------------------------------- */
        elseif ($act == 'submit_empower') {
            $AppKey = request()->get('AppKey', '');

            $time = TimeRepository::getGmTime();
            $activate_time = TimeRepository::getLocalDate("Y-m-d H:i:s", $time);

            $res = $this->dscRepository->dscEmpower($AppKey, $activate_time);

            return response()->json($res);
        }

        /* -------------------------------------------------------- */
        // ajax添加地区操作
        /* -------------------------------------------------------- */
        elseif ($act == 'area_insert') {
            $result = ['content' => '', 'message' => '', 'error' => 0];

            $parent_id = request()->get('parent_id', 0);
            $region_name = json_str_iconv(request()->get('region_name', 0));
            $region_type = request()->get('region_type', 0);

            /* 获取地区列表 */
            $region_arr = area_list($parent_id);
            $this->smarty->assign('region_arr', $region_arr);
            $area_top = '-';
            /* 当前的地区名称 */
            if ($parent_id > 0) {
                $area_name = Region::where('region_id', $parent_id)->value('region_name');
                $area_name = $area_name ? $area_name : '';
                $area_top = $area_name;
                if ($region_arr) {
                    $area = $region_arr[0]['type'];
                }
            } else {
                $area = __('admin::common.country');
            }
            $this->smarty->assign('area_top', $area_top);
            $this->smarty->assign('area_here', $area);

            if (empty($region_name)) {
                $result['error'] = 2;
                $result['message'] = __('admin::dialog.region_name_not_null');
                return response()->json($result);
            }

            /* 查看区域是否重复 */
            $is_only = Region::where('region_name', $region_name)->where('parent_id', $parent_id)->count();
            if ($is_only > 0) {
                $result['error'] = 2;
                $result['message'] = __('admin::dialog.region_name_repeat');
                return response()->json($result);
            }

            $data = ['parent_id' => $parent_id, 'region_name' => $region_name, 'region_type' => $region_type];
            $res = Region::insert($data);
            if ($res > 0) {
                admin_log($region_name, 'add', 'area');

                /* 获取地区列表 */
                $region_arr = area_list($parent_id);

                foreach ($region_arr as $k => $v) {
                    $region_arr[$k]['parent_name'] = Region::where('region_id', $v['parent_id'])->value('region_name');
                    $region_arr[$k]['parent_name'] = $region_arr[$k]['parent_name'] ? $region_arr[$k]['parent_name'] : '';
                }

                $this->smarty->assign('region_arr', $region_arr);

                $this->smarty->assign('region_type', $region_type);

                $this->smarty->assign("temp", "ajaxArea");
                $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            } else {
                $result['error'] = 1;
                $result['message'] = __('admin::dialog.add_region_fail');
            }

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加分类
        /* -------------------------------------------------------- */
        elseif ($act == 'ajaxCate') {
            $this->dscRepository->helpersLang('category', 'admin');

            $result = ['content' => '', 'mode' => ''];

            $parent_id = request()->get('parent_id', 0);
            if (!empty($parent_id)) {
                set_default_filter(0, $parent_id); //设置默认筛选
                $this->smarty->assign('parent_category', get_every_category($parent_id)); //上级分类
                $this->smarty->assign('parent_id', $parent_id); //上级分类
            } else {
                set_default_filter(); //设置默认筛选
            }
            //属性分类
            $type_level = get_type_cat_arr(0, 0, 0, $adminru['ru_id']);
            $this->smarty->assign('type_level', $type_level);

            //获取属性列表
            $res = Attribute::whereHasIn('goodsType', function ($query) {
                $query->where('enabled', '1');
            })->orderBy('cat_id')
                ->orderBy('sort_order');
            $arr = BaseRepository::getToArrayGet($res);
            $list = [];

            foreach ($arr as $val) {
                $list[$val['cat_id']][] = [$val['attr_id'] => $val['attr_name']];
            }

            /* 模板赋值 */
            $this->smarty->assign('goods_type_list', goods_type_list(0)); // 取得商品类型
            $this->smarty->assign('attr_list', $list); // 取得商品属性
            $this->smarty->assign('cat_info', ['is_show' => 1]);
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            $this->smarty->assign("temp", $act);
            $this->smarty->assign("lang", BaseRepository::getArrayCollapse([__('admin::common'), __('admin::dialog'), __('admin::category')]));
            $this->smarty->assign('form_action', 'cate_insert');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // ajax添加分类操作
        /* -------------------------------------------------------- */
        elseif ($act == 'cate_insert') {
            $result = ['content' => '', 'message' => '', 'error' => 0];
            $exc = new Exchange($this->dsc->table("category"), $db, 'cat_id', 'cat_name');

            /* 初始化变量 */
            $cat['cat_id'] = request()->get('cat_id', 0);
            $cat['parent_id'] = request()->get('parent_id', 0);
            $cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;

            if ($cat['level'] > 1 && $adminru['ru_id'] == 0) {
                $link[0]['text'] = __('admin::common.go_back');

                if ($cat['cat_id'] > 0) {
                    $link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
                } else {
                    $link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
                }

                $result['error'] = 2;
                $result['message'] = __('admin::common.cat_prompt_notic_one');

                return response()->json($result);
            }

            //ecmoban模板堂 --zhuo start
            if ($cat['level'] < 2 && $adminru['ru_id'] > 0) {
                $link[0]['text'] = __('admin::common.go_back');

                if ($cat['cat_id'] > 0) {
                    $link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
                } else {
                    $link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
                }

                $result['error'] = 2;
                $result['message'] = __('admin::common.cat_prompt_notic_two');

                return response()->json($result);
            }
            //ecmoban模板堂 --zhuo end

            //上传分类菜单图标 by wu start
            $cat_icon = request()->file('cat_icon');
            if ($cat_icon && $cat_icon->isValid()) {
                if ($cat_icon->getClientSize() > 200000) {
                    $result['error'] = 2;
                    $result['message'] = __('admin::common.cat_prompt_file_size');

                    return response()->json($result);
                }

                $icon_name = explode('.', $cat_icon->getClientOriginalName());
                $key = count($icon_name);
                $type = $icon_name[$key - 1];

                if ($type != 'jpg' && $type != 'png' && $type != 'gif' && $type != 'jpeg') {
                    $result['error'] = 2;
                    $result['message'] = __('admin::common.cat_prompt_file_type');

                    return response()->json($result);
                }
                $imgNamePrefix = time() . mt_rand(1001, 9999);
                //文件目录
                $imgDir = storage_public("images/cat_icon");
                if (!file_exists($imgDir)) {
                    mkdir($imgDir);
                }
                //保存文件
                $imgName = $imgDir . "/" . $imgNamePrefix . '.' . $type;
                $saveDir = "images/cat_icon" . "/" . $imgNamePrefix . '.' . $type;
                move_uploaded_file($cat_icon->getRealPath(), $imgName);
                $cat['cat_icon'] = $saveDir;
                $this->dscRepository->getOssAddFile([$cat['cat_icon']]); //oss存储图片
            }
            //上传分类菜单图标 by wu end

            //上传手机菜单图标 by kong start
            $touch_icon = request()->file('touch_icon');
            if ($touch_icon && $touch_icon->isValid()) {
                if ($touch_icon->getClientSize() > 200000) {
                    $result['error'] = 2;
                    $result['message'] = __('admin::common.cat_prompt_file_size');

                    return response()->json($result);
                }

                $icon_name = explode('.', $touch_icon->getClientOriginalName());
                $key = count($icon_name);
                $type = $icon_name[$key - 1];

                if ($type != 'jpg' && $type != 'png' && $type != 'gif' && $type != 'jpeg') {
                    $result['error'] = 2;
                    $result['message'] = __('admin::common.cat_prompt_file_type');

                    return response()->json($result);
                }
                $touch_iconPrefix = time() . mt_rand(1001, 9999);
                //文件目录
                $touch_iconDir = "../" . DATA_DIR . "/touch_icon";
                if (!file_exists($touch_iconDir)) {
                    mkdir($touch_iconDir);
                }
                //保存文件
                $touchimgName = $touch_iconDir . "/" . $touch_iconPrefix . '.' . $type;
                $touchsaveDir = DATA_DIR . "/touch_icon" . "/" . $touch_iconPrefix . '.' . $type;
                move_uploaded_file($touch_icon->getRealPath(), $touchimgName);
                $cat['touch_icon'] = $touchsaveDir;
                $this->dscRepository->getOssAddFile([$cat['touch_icon']]); //oss存储图片
            }

            //上传手机菜单图标 by kong end

            //佣金比率 by wu
            $cat['commission_rate'] = request()->get('commission_rate', 0);
            if ($cat['commission_rate'] > 100 || $cat['commission_rate'] < 0) {
                $result['error'] = 2;
                $result['message'] = __('admin::common.commission_rate_prompt');

                return response()->json($result);
            }

            $cat['style_icon'] = request()->get('style_icon', 'other'); //分类菜单图标
            $cat['sort_order'] = request()->get('sort_order', 0);
            $cat['keywords'] = request()->get('keywords', '');
            $cat['cat_desc'] = request()->get('cat_desc', '');
            $cat['measure_unit'] = request()->get('measure_unit', '');
            $cat['cat_name'] = request()->get('cat_name', '');
            $cat['cat_alias_name'] = request()->get('cat_alias_name', '');

            //by guan start
            $pin = new Pinyin();
            $pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');
            $cat['pinyin_keyword'] = $pinyin;
            //by guan end

            $cat['show_in_nav'] = request()->get('show_in_nav', 0);
            $cat['style'] = request()->get('style', '');
            $cat['is_show'] = request()->get('is_show', 0);

            /* by zhou */
            $cat['is_top_show'] = request()->get('is_top_show', 0);
            $cat['is_top_style'] = request()->get('is_top_style', 0);
            $cat['top_style_tpl'] = request()->get('top_style_tpl', 0); //顶级分类页模板 by wu

            /* by zhou */
            $cat['grade'] = request()->get('grade', 0);
            $filter_attr = request()->get('filter_attr', []);
            $cat['filter_attr'] = !empty($filter_attr) ? implode(',', array_unique(array_diff($filter_attr, [0]))) : 0;

            $cat['cat_recommend'] = request()->get('cat_recommend', []);

            if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
                /* 同级别下不能有重复的分类名称 */
                $result['error'] = 2;
                $result['message'] = __('admin::dialog.cate_name_not_repeat');

                return response()->json($result);
            }

            if ($cat['grade'] > 10 || $cat['grade'] < 0) {
                /* 价格区间数超过范围 */
                $result['error'] = 2;
                $result['message'] = __('admin::dialog.price_section_range');

                return response()->json($result);
            }

            /* 入库的操作 */
            $cat_name = explode(',', $cat['cat_name']);

            if (count($cat_name) > 1) {
                $cat['is_show_merchants'] = request()->get('is_show_merchants', 0);

                $this->categoryManageService->getBacthCategory($cat_name, $cat);

                clear_cache_files();    // 清除缓存
            } else {
                if ($this->db->autoExecute($this->dsc->table('category'), $cat) !== false) {
                    $cat_id = $this->db->insert_id();

                    if ($cat['show_in_nav'] == 1) {
                        $vieworder = $this->db->getOne("SELECT max(vieworder) FROM " . $this->dsc->table('nav') . " WHERE type = 'middle'");
                        $vieworder += 2;
                        //显示在自定义导航栏中
                        $sql = "INSERT INTO " . $this->dsc->table('nav') .
                            " (name,ctype,cid,ifshow,vieworder,opennew,url,type)" .
                            " VALUES('" . $cat['cat_name'] . "', 'c', '" . $this->db->insert_id() . "','1','$vieworder','0', '" . $this->dscRepository->buildUri('category', ['cid' => $cat_id], $cat['cat_name']) . "','middle')";
                        $this->db->query($sql);
                    }

                    admin_log($cat_name, 'add', 'category');   // 记录管理员操作

                    $dt_list = request()->get('document_title', []);
                    $dt_id = request()->get('dt_id', []);
                    $this->categoryManageService->setDocumentTitleInsertUpdate($dt_list, $cat_id, $dt_id);

                    clear_cache_files();    // 清除缓存
                }
            }

            $level_limit = 3;
            $category_level = [];

            for ($i = 1; $i <= $level_limit; $i++) {
                $category_list = [];
                if ($i == 1) {
                    $category_list = get_category_list();
                }
                $this->smarty->assign('cat_level', $i);
                $this->smarty->assign('category_list', $category_list);
                $category_level[$i] = $this->smarty->fetch('library/get_select_category.lbi');
            }

            $this->smarty->assign('category_level', $category_level);
            $this->smarty->assign("temp", "cate_reload");
            $result['message'] = __('admin::dialog.cat_add_success');
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }

        /* -------------------------------------------------------- */
        // 上传视频
        /* -------------------------------------------------------- */
        elseif ($act == 'video_box') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $this->smarty->assign("temp", "video_box_load");
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');

            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        // 商品审核
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_review_status') {
            $result = array('error' => 0, 'message' => '', 'content' => '');

            $seller_list = request()->get('seller_list', 0);  //商家和自营订单标识
            $review_status = request()->get('review_status', 3);   //审核状态
            $res = Goods::where('is_delete', 0);
            if ($seller_list) {
                $res = CommonRepository::constantMaxId($res, 'user_id');
            } else {
                $res = $res->where('user_id', 0);
            }

            $review_status = BaseRepository::getExplode($review_status);
            $count = $res->whereIn('review_status', $review_status);
            $count = $count->count();

            $result['count'] = $count;

            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        // 普通商品
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_ordinary') {
            $result = array('error' => 0, 'message' => '', 'content' => '');

            $seller_list = request()->get('seller_list', 0);  //商家和自营订单标识
            $res = Goods::where('review_status', '>=', 3)->where('extension_code', '')->where('is_real', 1)->where('is_delete', 0);
            if ($seller_list) {
                $res = CommonRepository::constantMaxId($res, 'user_id');
            } else {
                $res = $res->where('user_id', 0);
            }

            $result['count'] = $res->count();

            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        // 虚拟商品
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_virtual_card') {
            $result = array('error' => 0, 'message' => '', 'content' => '');

            $seller_list = request()->get('seller_list', 0);  //商家和自营订单标识
            $res = Goods::where('extension_code', 'virtual_card')->where('is_real', 0)->where('is_delete', 0);

            if (config('shop.review_goods') == 1) {
                $res = $res->whereIn('review_status', [3, 4, 5]);
            }

            if ($seller_list) {
                $res = CommonRepository::constantMaxId($res, 'user_id');
            } else {
                $res = $res->where('user_id', 0);
            }

            $result['count'] = $res->count();

            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        // 审核商品状态
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_status') {
            $result = array('error' => 0, 'message' => '', 'content' => '');

            $review_status = request()->get('review_status', 1);   //商品审核状态
            $seller_list = request()->get('seller_list', 0);  //商家和自营订单标识
            $res = Goods::where('review_status', $review_status);
            if ($seller_list) {
                $res = CommonRepository::constantMaxId($res, 'user_id');
            } else {
                $res = $res->where('user_id', 0);
            }

            $result['count'] = $res->count();

            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        // 回收站商品
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_delete') {
            $result = array('error' => 0, 'message' => '', 'content' => '');

            $seller_list = request()->get('seller_list', 0);  //商家和自营订单标识
            $res = Goods::where('is_delete', 1);
            if ($seller_list) {
                $res = CommonRepository::constantMaxId($res, 'user_id');
            } else {
                $res = $res->where('user_id', 0);
            }

            $result['count'] = $res->count();

            return response()->json($result);
        }

        /*--------------------------------------------------------*/
        // 商品上架状态
        /*--------------------------------------------------------*/
        elseif ($act == 'goods_sale') {
            $result = array('error' => 0, 'message' => '', 'content' => '');

            $is_on_sale = request()->get('is_on_sale', 0);  //商品审核状态
            $seller_list = request()->get('seller_list', 0);  //商家和自营订单标识
            $res = Goods::where('is_on_sale', $is_on_sale)->where('is_delete', 0);

            if (config('shop.review_goods') == 1) {
                $res = $res->whereIn('review_status', [3, 4, 5]);
            }

            if ($seller_list) {
                $res = CommonRepository::constantMaxId($res, 'user_id');
            } else {
                $res = $res->where('user_id', 0);
            }

            $result['count'] = $res->count();

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 删除商品二维码图片
        /*------------------------------------------------------ */
        elseif ($act == 'remove_goods_qrcode') {
            $imgList = [];

            /**
             * 获取商品生成的二维码
             */
            $dir = 'images/weixin_img/';
            $themes = storage_public($dir);
            if (is_dir($themes)) {
                $path = Storage::disk('public')->files($dir);
                $path = array_values(array_diff($path, ['..', '.'])); // 过滤

                $ext = ['png', 'jpg', 'jpeg'];
                foreach ($path as $item) {
                    $extensions = strtolower(pathinfo($item, PATHINFO_EXTENSION)); // 文件扩展名
                    if (in_array($extensions, $ext)) {
                        $imgList[] = $item; //把符合条件的文件存入数组
                    }
                }
            }

            if (!empty($imgList)) {
                // 分块处理 每次1000
                foreach (collect($imgList)->chunk(1000) as $chunk) {
                    $chunk = $chunk ? $chunk->toArray() : [];
                    File::remove($chunk);
                }

                $res_pc = true;
            } else {
                $res_pc = false;
            }

            /**
             * 删除商品海报图片（手机端）
             */
            $res_mobile = SharePoster::removeShareImages();

            if ($res_mobile || $res_pc) {
                return response()->json(['error' => 0, 'message' => trans('common.delete_success')]);
            }

            return response()->json(['error' => 1, 'message' => 'empty']);
        }
    }
}
