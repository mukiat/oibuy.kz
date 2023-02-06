<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Libraries\Pinyin;
use App\Models\Attribute;
use App\Models\BookingGoods;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CollectGoods;
use App\Models\Comment;
use App\Models\DiscussCircle;
use App\Models\GalleryAlbum;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsArticle;
use App\Models\GoodsAttr;
use App\Models\GoodsCat;
use App\Models\GoodsChangeLog;
use App\Models\GoodsExtend;
use App\Models\GoodsGallery;
use App\Models\GoodsInventoryLogs;
use App\Models\GoodsKeyword;
use App\Models\GoodsTransport;
use App\Models\GoodsType;
use App\Models\GoodsUseLabel;
use App\Models\GroupGoods;
use App\Models\KeywordList;
use App\Models\LinkDescGoodsid;
use App\Models\LinkDescTemporary;
use App\Models\LinkGoods;
use App\Models\LinkGoodsDesc;
use App\Models\MemberPrice;
use App\Models\MerchantsCategory;
use App\Models\PicAlbum;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsChangelog;
use App\Models\ProductsWarehouse;
use App\Models\RegionWarehouse;
use App\Models\SaleNotice;
use App\Models\Tag;
use App\Models\UserRank;
use App\Models\VirtualCard;
use App\Models\WarehouseAreaAttr;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseAttr;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\FileSystemsRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Brand\BrandService;
use App\Services\Cart\CartCommonService;
use App\Services\Common\CommonManageService;
use App\Services\Common\ConfigManageService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsFittingService;
use App\Services\Goods\GoodsManageService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Store\StoreCommonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 商品管理程序
 */
class GoodsController extends InitController
{
    protected $brandService;
    protected $goodsManageService;
    protected $commonRepository;
    protected $commonManageService;
    protected $goodsAttrService;
    protected $goodsCommonService;
    protected $configManageService;
    protected $goodsWarehouseService;
    protected $dscRepository;
    protected $storeCommonService;
    protected $cartCommonService;
    protected $goodsFittingService;

    public function __construct(
        BrandService $brandService,
        GoodsManageService $goodsManageService,
        CommonRepository $commonRepository,
        CommonManageService $commonManageService,
        GoodsAttrService $goodsAttrService,
        GoodsCommonService $goodsCommonService,
        ConfigManageService $configManageService,
        GoodsWarehouseService $goodsWarehouseService,
        DscRepository $dscRepository,
        StoreCommonService $storeCommonService,
        CartCommonService $cartCommonService,
        GoodsFittingService $goodsFittingService
    )
    {
        $this->brandService = $brandService;
        $this->goodsManageService = $goodsManageService;
        $this->commonRepository = $commonRepository;
        $this->commonManageService = $commonManageService;
        $this->goodsAttrService = $goodsAttrService;
        $this->goodsCommonService = $goodsCommonService;
        $this->configManageService = $configManageService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->dscRepository = $dscRepository;
        $this->storeCommonService = $storeCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->goodsFittingService = $goodsFittingService;
    }

    public function index()
    {
        load_helper('goods', 'admin');

        $image = new Image(['bgcolor' => config('shop.bgcolor')]);

        /* 管理员ID */
        $admin_id = get_admin_id();

        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        $this->smarty->assign('review_goods', config('shop.review_goods'));
        //ecmoban模板堂 --zhuo end

        //商品佣金设置权限
        $commission_setting = admin_priv('commission_setting', '', false);
        $this->smarty->assign('commission_setting', $commission_setting);

        //商品积分比例设置
        $precent = 0;
        if (!empty(config('shop.integral_percent'))) {
            $precent = config('shop.integral_percent') / 100;
        }
        $this->smarty->assign('precent', $precent);

        $act = e(request()->get('act'));

        /*------------------------------------------------------ */
        //-- 商品列表，商品回收站
        /*------------------------------------------------------ */
        if (in_array($act, ['list', 'trash', 'is_sale', 'on_sale'])) {

            admin_priv('goods_manage');

            get_del_goodsimg_null();
            get_del_goods_gallery();
            get_updel_goods_attr();
            get_del_goods_video();

            //清楚商品零时货品表数据
            ProductsChangelog::where('goods_id', 0)->where('admin_id', $admin_id)->delete();
            $cat_id = request()->get('cat_id', 0);
            $code = request()->get('extension_code', '');
            $suppliers_id = request()->get('suppliers_id', '');
            $is_on_sale = request()->get('is_on_sale', '');

            $handler_list = [];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=card', 'title' => __('admin::goods.card'), 'icon' => 'icon-credit-card'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=replenish', 'title' => __('admin::goods.replenish'), 'icon' => 'icon-plus-sign'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=batch_card_add', 'title' => __('admin::goods.batch_card_add'), 'icon' => 'icon-paste'];

            $current = '01_goods_list';

            $current_list = [
                'list' => '50_virtual_card_list',
                'trash' => '11_goods_trash',
                'review_status' => '01_review_status',
                'is_sale' => '19_is_sale',
                'on_sale' => '20_is_sale'
            ];

            if (!empty($current_list[$act])) {
                if ($act == 'list') {
                    if (isset($handler_list[$code])) {
                        $current = $current_list[$act];
                        $this->smarty->assign('add_handler', $handler_list[$code] ?? '');
                    }
                } else {
                    $current = $current_list[$act];
                }
            }

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => $current]);
            $this->smarty->assign('is_on_sale', $is_on_sale);
            $this->smarty->assign('suppliers_id', $suppliers_id);

            /* 供货商名 */
            $suppliers_list_name = suppliers_list_name();
            $suppliers_exists = empty($suppliers_list_name) ? 0 : 1;
            $this->smarty->assign('suppliers_exists', $suppliers_exists);
            $this->smarty->assign('suppliers_list_name', $suppliers_list_name);
            unset($suppliers_list_name, $suppliers_exists);

            /* 模板赋值 */
            $goods_ur = ['' => __('admin::common.01_goods_list'), 'virtual_card' => __('admin::common.50_virtual_card_list')];
            $ur_here = ($act != 'trash') ? $goods_ur[$code] : __('admin::common.11_goods_trash');
            $this->smarty->assign('ur_here', $ur_here);

            $action_link = ($act == 'list') ? $this->goodsManageService->addLink($code) : ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
            $this->smarty->assign('action_link', $action_link);

            //ecmoban模板堂 --zhuo start
            $action_link2 = ($act == 'list') ? ['href' => 'goods.php?act=add_desc', 'text' => __('admin::goods.lab_goods_desc')] : '';
            $this->smarty->assign('action_link2', $action_link2);
            //ecmoban模板堂 --zhuo start

            $this->smarty->assign('code', $code);

            $this->smarty->assign('brand_list', get_brand_list());

            // 活动推荐类型
            $intro_list = $this->goodsManageService->getIntroList();
            $this->smarty->assign('intro_list', $intro_list);
            $this->smarty->assign('lang', BaseRepository::getArrayCollapse([__('admin::common'), __('admin::goods')]));
            $this->smarty->assign('list_type', $act == 'list' ? 'goods' : 'trash');
            $this->smarty->assign('use_storage', empty(config('shop.use_storage')) ? 0 : 1);

            $suppliers_list = suppliers_list_info(' is_check = 1 ');
            $suppliers_list_count = count($suppliers_list);
            $this->smarty->assign('suppliers_list', ($suppliers_list_count == 0 ? 0 : $suppliers_list)); // 取供货商列表

            $review_status = 0;
            if (in_array($act, ['list', 'is_sale', 'on_sale'])) {
                $review_status = 3;
            }

            if ($act == 'is_sale') {
                $is_on_sale = 1;
            } elseif ($act == 'on_sale') {
                $is_on_sale = 0;
            }
            $is_delete = $act == 'list' || $act == 'is_sale' || $act == 'on_sale' ? 0 : 1;
            $real_goods = ($act == 'list') ? (($code == '') ? 1 : 0) : -1;
            $param_str = '-' . $is_delete . '-' . $real_goods . '-' . $review_status;
            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()), 'list', $param_str);

            $goods_list = $this->goodsManageService->getGoodsList($act == 'list' || $act == 'is_sale' || $act == 'on_sale' ? 0 : 1, ($act == 'list') ? (($code == '') ? 1 : 0) : -1, '', $review_status);
            $this->smarty->assign('goods_list', $goods_list['goods']);
            $this->smarty->assign('filter', $goods_list['filter']);
            $this->smarty->assign('record_count', $goods_list['record_count']);
            $this->smarty->assign('page_count', $goods_list['page_count']);
            $this->smarty->assign('full_page', 1);

            /* 排序标记 */
            $sort_flag = sort_flag($goods_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            /* 获取商品类型存在规格的类型 */
            $specifications = get_goods_type_specifications();
            $this->smarty->assign('specifications', $specifications);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $this->smarty->assign('nowTime', TimeRepository::getGmTime());

            set_default_filter(); //设置默认筛选

            /* 起始页通过商品一览点击进入自营/商家商品判断条件 */
            if (request()->get('self', 0)) {
                $this->smarty->assign('self', 1);
            } elseif (request()->get('merchants', 0)) {
                $this->smarty->assign('merchants', 1);
            }

            $this->smarty->assign('cfg', config('shop'));

            $this->smarty->assign('transport_list', get_table_date("goods_transport", "ru_id='{$adminru['ru_id']}'", ['tid, title'], 1)); //商品运费 by wu

            // 商品金额输入
            $this->smarty->assign('price_format', config('shop.price_format'));

            /* 显示商品列表页面 */
            $htm_file = ($act == 'list' || $act == 'is_sale' || $act == 'on_sale') ? 'goods_list.dwt' : (($act == 'trash') ? 'goods_trash.dwt' : 'group_list.dwt');
            return $this->smarty->display($htm_file);
        }

        /*------------------------------------------------------ */
        //-- 添加新商品 编辑商品
        /*------------------------------------------------------ */

        elseif (in_array($act, ['add', 'edit', 'copy'])) {
            $goods_id = request()->get('goods_id', 0);

            get_del_goodsimg_null();
            get_del_goods_gallery();
            get_del_update_goods_null(); //删除商品相关表goods_id值为0的信息
            get_del_goods_video();

            session()->forget('label_use_id' . session('admin_id'));
            session()->forget('services_label_use_id' . session('admin_id'));


            //获取人气组合 by kong
            if (config('shop.group_goods')) {
                $group_goods_arr = explode(',', config('shop.group_goods'));
                $arr = [];
                foreach ($group_goods_arr as $k => $v) {
                    $arr[$k + 1] = $v;
                }
                $this->smarty->assign('group_goods_arr', $arr);
            }
            $is_add = $act == 'add'; // 添加还是编辑的标识
            $is_copy = $act == 'copy'; //是否复制
            $this->smarty->assign('is_copy', $is_copy);

            $code = request()->get('extension_code', '');

            get_updel_goods_attr();

            $properties = request()->get('properties', 0);
            $this->smarty->assign('properties', $properties);

            /* 删除未绑定仓库 by kong */
            WarehouseGoods::where('goods_id', 0)->orWhere('goods_id', '')->delete();
            /* 删除未绑定地区 by kong */
            WarehouseAreaGoods::where('goods_id', 0)->orWhere('goods_id', '')->delete();

            if ($code == 'virual_card') {
                admin_priv('virualcard'); // 检查权限
            } else {
                admin_priv('goods_manage'); // 检查权限
            }

            /* 供货商名 */
            $suppliers_list_name = suppliers_list_name();
            $suppliers_exists = empty($suppliers_list_name) ? 0 : 1;
            $this->smarty->assign('suppliers_exists', $suppliers_exists);
            $this->smarty->assign('suppliers_list_name', $suppliers_list_name);
            unset($suppliers_list_name, $suppliers_exists);

            /* 如果是安全模式，检查目录是否存在 */
            if (ini_get('safe_mode') == 1 && (!file_exists('../' . IMAGE_DIR . '/' . date('Ym')) || !is_dir('../' . IMAGE_DIR . '/' . date('Ym')))) {
                if (@!mkdir('../' . IMAGE_DIR . '/' . date('Ym'), 0777)) {
                    $warning = sprintf(__('admin::goods.safe_mode_warning'), '../' . IMAGE_DIR . '/' . date('Ym'));
                    $this->smarty->assign('warning', $warning);
                }
            } /* 如果目录存在但不可写，提示用户 */ elseif (file_exists('../' . IMAGE_DIR . '/' . date('Ym')) && FileSystemsRepository::fileModeInfo('../' . IMAGE_DIR . '/' . date('Ym')) < 2) {
                $warning = sprintf(__('admin::goods.not_writable_warning'), '../' . IMAGE_DIR . '/' . date('Ym'));
                $this->smarty->assign('warning', $warning);
            }

            //清楚商品零时货品表数据
            $res = ProductsChangelog::where('goods_id', $goods_id);
            if (empty($goods_id)) {
                $res = $res->where('admin_id', $admin_id);
            }
            $res = $res->delete();

            /* 取得商品信息 */
            if ($is_add) {
                $goods = [
                    'goods_id' => 0,
                    'user_id' => $adminru['ru_id'],
                    'goods_desc' => '',
                    'goods_shipai' => '',
                    'goods_video' => '',
                    //'cat_id'        => $last_choose[0],
                    'freight' => 2,
                    'cat_id' => 0,
                    'brand_id' => 0,
                    'is_on_sale' => 1,
                    'is_alone_sale' => 1,
                    'is_show' => 1,
                    'is_shipping' => 0,
                    'other_cat' => [], // 扩展分类
                    'goods_type' => 0, // 商品类型
                    'shop_price' => 0,
                    'promote_price' => 0,
                    'market_price' => 0,
                    'integral' => 0,
                    'goods_number' => config('shop.default_storage'),
                    'warn_number' => 1,
                    'promote_start_date' => TimeRepository::getLocalDate(config('shop.time_format')),
                    'promote_end_date' => TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getLocalStrtoTime('+1 month')),
                    'goods_weight' => 0,
                    'give_integral' => 0,
                    'rank_integral' => 0,
                    'user_cat' => 0,
                    'goods_unit' => '',
                    'goods_cause' => 0,
                    'is_real' => $code == 'virual_card' ? 0 : 1,
                    'goods_extend' => ['is_reality' => 0, 'is_return' => 0, 'is_fast' => 0]//by wang
                ];

                if ($code != '') {
                    $goods['goods_number'] = 0;
                }

                /* 关联商品 */
                $link_goods_list = [];
                $res = LinkGoods::where('admin_id', session('admin_id'));
                $res = $res->where(function ($query) {
                    $query->where('goods_id', 0)->orWhere('link_goods_id', 0);
                });
                $res->delete();

                /* 组合商品 */
                $group_goods_list = [];
                GroupGoods::where('parent_id', 0)->where('admin_id', session('admin_id'))->delete();

                /* 关联文章 */
                $goods_article_list = [];
                GoodsArticle::where('goods_id', 0)->where('admin_id', session('admin_id'))->delete();


                /* 属性 */
                GoodsAttr::where('goods_id', 0)->where('admin_id', $admin_id)->delete();
                /* 图片列表 */
                $img_list = [];
            } else {
                /* 商品信息 */
                $goods = get_admin_goods_info($goods_id);
                if (empty($goods)) {
                    $link[] = ['href' => 'goods.php?act=list', 'text' => __('admin::goods.back_goods_list')];
                    return sys_msg(__('admin::goods.lab_not_goods'), 0, $link);
                }

                // 获取商品活动信息
                if ($act == 'edit') {
                    $goods_activity = $this->goodsManageService->goodsAddActivity($goods_id);
                    $this->smarty->assign('goods_activity', $goods_activity);
                }


                /* 退换货标志列表 */
                $cause_list = ['0', '1', '2', '3'];

                /* 判断商品退换货理由 */
                if (!is_null($goods['goods_cause'])) {
                    $res = array_intersect(explode(',', $goods['goods_cause']), $cause_list);
                } else {
                    $res = [];
                }

                if ($res) {
                    $this->smarty->assign('is_cause', $res);
                } else {
                    $res = [];
                    $this->smarty->assign('is_cause', $res);
                }

                $this->smarty->assign('cause_list', $cause_list);

                //图片显示
                $goods['goods_thumb'] = $this->dscRepository->getImagePath($goods['goods_thumb']);

                /* 虚拟卡商品复制时, 将其库存置为0 */
                if ($is_copy && $code != '') {
                    $goods['goods_number'] = 0;
                }

                if (empty($goods)) {
                    /* 默认值 */
                    $goods = [
                        'goods_id' => 0,
                        'user_id' => $adminru['ru_id'],
                        'goods_desc' => '',
                        'goods_shipai' => '',
                        'goods_video' => '',
                        'cat_id' => 0,
                        'is_on_sale' => 1,
                        'is_alone_sale' => 1,
                        'is_show' => 1,
                        'is_shipping' => 0,
                        'other_cat' => [], // 扩展分类
                        'goods_type' => 0, // 商品类型
                        'shop_price' => 0,
                        'promote_price' => 0,
                        'market_price' => 0,
                        'integral' => 0,
                        'goods_number' => 1,
                        'warn_number' => 1,
                        'promote_start_date' => TimeRepository::getLocalDate(config('shop.time_format')),
                        'promote_end_date' => TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getLocalStrtoTime('+1 month')),
                        'goods_weight' => 0,
                        'give_integral' => 0,
                        'rank_integral' => 0,
                        'user_cat' => 0,
                        'is_real' => $code == 'virual_card' ? 0 : 1,
                        'goods_extend' => ['is_reality' => 0, 'is_return' => 0, 'is_fast' => 0]
                    ];
                }

                $goods['goods_extend'] = get_goods_extend($goods['goods_id']);

                /* 获取商品类型存在规格的类型 */
                $specifications = get_goods_type_specifications();
                if ($goods['goods_type'] > 0) {
                    $goods['specifications_id'] = isset($specifications[$goods['goods_type']]) ? $specifications[$goods['goods_type']] : 0;
                } else {
                    $goods['specifications_id'] = 0;
                }
                $_attribute = get_goods_specifications_list($goods['goods_id']);
                $goods['_attribute'] = empty($_attribute) ? '' : 1;

                /* 根据商品重量的单位重新计算 */
                if ($goods['goods_weight'] > 0) {
                    $goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
                }

                if (!empty($goods['goods_brief'])) {
                    $goods['goods_brief'] = $goods['goods_brief'];
                }
                if (!empty($goods['keywords'])) {
                    $goods['keywords'] = $goods['keywords'];
                }

                //ecmoban模板堂 --zhuo start 限购
                /* 如果不是限购，处理限购日期 */
                if (isset($goods['is_xiangou']) && $goods['is_xiangou'] == '0') {
                    unset($goods['xiangou_start_date']);
                    unset($goods['xiangou_end_date']);
                } else {
                    $goods['xiangou_start_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $goods['xiangou_start_date']);
                    $goods['xiangou_end_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $goods['xiangou_end_date']);
                }
                //ecmoban模板堂 --zhuo end 限购

                //如果不是最小起订量，处理起订量日期
                if (isset($goods['is_minimum']) && $goods['is_minimum'] == '0') {
                    unset($goods['minimum_start_date']);
                    unset($goods['minimum_end_date']);
                } else {
                    $goods['minimum_start_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $goods['minimum_start_date']);
                    $goods['minimum_end_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $goods['minimum_end_date']);
                }

                //@author guan 晒单评论 start
                if (!empty($goods['goods_product_tag'])) {
                    $goods['goods_product_tag'] = $goods['goods_product_tag'];
                }
                //@author guan  晒单评论 end

                //商品标签 liu
                if (!empty($goods['goods_tag'])) {
                    $goods['goods_tag'] = $goods['goods_tag'];
                }

                /* 如果不是促销，处理促销日期 */
                if (isset($goods['is_promote']) && $goods['is_promote'] == '0') {
                    unset($goods['promote_start_date']);
                    unset($goods['promote_end_date']);
                } else {
                    $goods['promote_start_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $goods['promote_start_date']);
                    $goods['promote_end_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $goods['promote_end_date']);
                }

                //获取拓展分类id数组
                $other_cat_list1 = [];
                $res = GoodsCat::select('cat_id')->where('goods_id', $goods_id);
                $other_cat1 = BaseRepository::getToArrayGet($res);
                $other_cat1 = BaseRepository::getFlatten($other_cat1);

                $other_catids = '';

                if ($other_cat1) {
                    foreach ($other_cat1 as $key => $val) {
                        $other_catids .= $val . ",";
                    }
                }

                $other_catids = substr($other_catids, 0, -1);
                $this->smarty->assign('other_catids', $other_catids);

                /* 如果是复制商品，处理 */
                if ($act == 'copy') {
                    if ($goods['original_img']) {
                        $originalImg = explode('storage/', $goods['original_img']);
                        $goods['copy_original_img'] = count($originalImg) > 1 ? $originalImg[1] : $goods['original_img'];
                    }

                    if ($goods['goods_img']) {
                        $goodsImg = explode('storage/', $goods['goods_img']);
                        $goods['copy_goods_img'] = count($goodsImg) > 1 ? $goodsImg[1] : $goods['goods_img'];
                    }

                    if ($goods['goods_thumb']) {
                        $goodsThumb = explode('storage/', $goods['goods_thumb']);
                        $goods['copy_goods_thumb'] = count($goodsThumb) > 1 ? $goodsThumb[1] : $goods['goods_thumb'];
                    }

                    // 商品信息
                    $goods['goods_id'] = 0;
                    $goods['goods_sn'] = '';

                    // 扩展分类不变
                    // 关联商品
                    $res = LinkGoods::where('admin_id', session('admin_id'));
                    $res = $res->where(function ($query) {
                        $query->where('goods_id', 0)->orWhere('link_goods_id', 0);
                    });
                    $res->delete();

                    $res = LinkGoods::selectRaw("'0' AS goods_id, link_goods_id, is_double, '" . session('admin_id') . "' AS admin_id");
                    $res = $res->where('goods_id', $goods_id);
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        LinkGoods::insert($row);
                    }

                    $res = LinkGoods::selectRaw("goods_id, '0' AS link_goods_id, is_double, '" . session('admin_id') . "' AS admin_id");
                    $res = $res->where('link_goods_id', $goods_id);
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        LinkGoods::insert($row);
                    }

                    // 配件
                    GroupGoods::where('parent_id', 0)->where('admin_id', session('admin_id'))->delete();

                    $res = GroupGoods::selectRaw(" 0 AS parent_id, goods_id, goods_price, '" . session('admin_id') . "' AS admin_id ");
                    $res = $res->where('parent_id', $goods_id);
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        GroupGoods::insert($row);
                    }

                    // 关联文章
                    GoodsArticle::where('goods_id', 0)->where('admin_id', session('admin_id'))->delete();

                    $res = GoodsArticle::selectRaw(" 0 AS goods_id, article_id, '" . session('admin_id') . "' AS admin_id ");
                    $res = $res->where('goods_id', $goods_id);
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        GoodsArticle::insert($row);
                    }

                    // 商品属性
                    GoodsAttr::where('goods_id', 0)->where('admin_id', $admin_id)->delete();

                    $res = GoodsAttr::selectRaw(" 0 AS goods_id, attr_id, attr_value, attr_price ");
                    $res = $res->where('goods_id', $goods_id);
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        $row['admin_id'] = $admin_id;
                        GoodsAttr::insert(addslashes_deep($row));
                    }
                }

                $link_goods_list = get_linked_goods($goods['goods_id']); // 关联商品
                $group_goods_list = get_group_goods($goods['goods_id']); // 配件
                $goods_article_list = get_goods_articles($goods['goods_id']);   // 关联文章

                /* 图片列表 */
                $res = GoodsGallery::where('goods_id', $goods_id)->orderBy('img_desc');
                $img_list = BaseRepository::getToArrayGet($res);

                /* 格式化相册图片路径 */
                foreach ($img_list as $key => $gallery_img) {
                    $img_list[$key] = $gallery_img;

                    if (!empty($gallery_img['external_url'])) {
                        $img_list[$key]['img_url'] = $gallery_img['external_url'];
                        $img_list[$key]['thumb_url'] = $gallery_img['external_url'];
                    } else {
                        $gallery_img['thumb_url'] = $this->dscRepository->getImagePath($gallery_img['thumb_url']);

                        $img_list[$key]['thumb_url'] = $gallery_img['thumb_url'];
                    }
                }

                $img_desc = [];
                foreach ($img_list as $k => $v) {
                    $img_desc[] = $v['img_desc'];
                }
                if ($act == 'copy') {
                    $img_list = [];
                }

                @$img_default = min($img_desc);
                $min_img_id = GoodsGallery::where('goods_id', $goods_id)
                    ->where('img_desc', $img_default)
                    ->orderBy('img_desc')->value('img_id');
                $this->smarty->assign('min_img_id', $min_img_id);

                // 活动标签
                $label_list = $this->goodsCommonService->getGoodsLabel($goods_id);
                $this->smarty->assign('label_list', $label_list);
                $this->smarty->assign('label_count', count($label_list));

                // 服务标签
                $label_list = $this->goodsCommonService->getGoodsServicesLabel($goods_id);
                $this->smarty->assign('services_label_list', $label_list);
                $this->smarty->assign('services_label_count', count($label_list));
            }

            //ecmoban模板堂 --zhuo start
            if ($adminru['ru_id'] > 0) {
                $goods['user_id'] = $adminru['ru_id'];
            }

            $goods['keyword_list'] = $this->goodsManageService->getGoodsKeywordInfo($goods);

            $warehouse_list = get_warehouse_region();
            $this->smarty->assign('warehouse_list', $warehouse_list);
            $this->smarty->assign('count_warehouse', count($warehouse_list));

            $warehouse_goods_list = get_warehouse_goods_list($goods_id);
            $this->smarty->assign('warehouse_goods_list', $warehouse_goods_list);

            $warehouse_area_goods_list = get_warehouse_area_goods_list($goods_id);
            $this->smarty->assign('warehouse_area_goods_list', $warehouse_area_goods_list);

            $area_count = get_all_warehouse_area_count();
            $this->smarty->assign('area_count', $area_count);

            $areaRegion_list = $this->commonManageService->getAreaRegionList();
            $this->smarty->assign('areaRegion_list', $areaRegion_list);
            $this->smarty->assign('area_goods_list', get_area_goods($goods_id));

            $consumption_list = $this->cartCommonService->getGoodsConList($goods_id, 'goods_consumption'); //满减订单金额
            $this->smarty->assign('consumption_list', $consumption_list);

            $group_goods = $this->goodsFittingService->getCfgGroupGoods();
            $this->smarty->assign('group_list', $group_goods);

            $this->smarty->assign('ru_id', $adminru['ru_id']);
            //ecmoban模板堂 --zhuo end

            /* 拆分商品名称样式 */
            $goods_name_style = explode('+', empty($goods['goods_name_style']) ? '+' : $goods['goods_name_style']);

            if (config('shop.open_oss') == 1) {
                $bucket_info = $this->dscRepository->getBucketInfo();
                $endpoint = $bucket_info['endpoint'];
            } else {
                $endpoint = url('/');
            }

            if ($goods['goods_desc']) {
                $desc_preg = get_goods_desc_images_preg($endpoint, $goods['goods_desc']);
                $goods['goods_desc'] = $desc_preg['goods_desc'];
            }

            if (isset($goods['desc_mobile']) && !empty($goods['desc_mobile'])) {
                $desc_preg = get_goods_desc_images_preg($endpoint, $goods['desc_mobile']);
                $goods['desc_mobile'] = $desc_preg['goods_desc'];
            }

            /* 创建 html editor */
            create_html_editor('goods_desc', $goods['goods_desc']);
            create_html_editor2('goods_shipai', 'goods_shipai', $goods['goods_shipai']);

            /*  @author-bylu 处理分期数据 start */
            $stages = '';
            if (!empty($goods['stages'])) {
                $stages = unserialize($goods['stages']);
            }
            /*  @author-bylu 处理分期数据 end */

            $grade_rank = get_seller_grade_rank($goods['user_id']);
            $this->smarty->assign('grade_rank', $grade_rank);
            $this->smarty->assign('integral_scale', config('shop.integral_scale'));

            if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status')) {
                //商品资质
                $goods_qualifications = \App\Modules\WxMedia\Models\WxappGoodsQualifications::query()->where('goods_id', $goods_id);
                $goods_qualifications = BaseRepository::getToArrayGet($goods_qualifications);

                if (!empty($goods_qualifications)) {
                    foreach ($goods_qualifications as $k => $val) {
                        $goods_qualifications[$k]['qualifications_pic'] = $this->dscRepository->getImagePath($val['qualifications_pic']);
                    }
                    $goods['goods_qualifications'] = $goods_qualifications;
                }
                $wx_goods_extend = \App\Modules\WxMedia\Models\WxappGoodsExtension::query()->where('goods_id', $goods_id);
                $wx_goods_extend = BaseRepository::getToArrayFirst($wx_goods_extend);
                $goods['media_goods_category'] = $wx_goods_extend['wxapp_cat_id'] ?? 0;
                $goods['media_goods_brand'] = $wx_goods_extend['wxapp_brand_id'] ?? 0;

                if (file_exists(WXAPP_MEDIA_CONCISE)) {
                    $WxappMediaGoodsQrcod = \App\Modules\WxMedia\Models\WxappMediaGoodsQrcod::query()
                        ->where('goods_id', $goods_id)
                        ->orderBy('sort', 'asc');
                    $WxappMediaGoodsQrcod = BaseRepository::getToArrayGet($WxappMediaGoodsQrcod);

                    if (!empty($WxappMediaGoodsQrcod)) {
                        foreach ($WxappMediaGoodsQrcod as $k => $val) {
                            $WxappMediaGoodsQrcod[$k]['qrcod_pic'] = $this->dscRepository->getImagePath($val['qrcod_pic']);
                        }
                        $goods['media_goods_qrcod'] = $WxappMediaGoodsQrcod;
                    }
                }

                if (empty($wx_goods_extend)) {
                    $goods['sync_media'] = 0;
                } else {
                    $goods['sync_media'] = 1;
                }
                $this->smarty->assign('wxapp_shop_status', 1);
            }

            /* 模板赋值 */
            $this->smarty->assign('code', $code);
            $this->smarty->assign('ur_here', $is_add ? (empty($code) ? __('admin::common.02_goods_add') : __('admin::common.51_virtual_card_add')) : ($act == 'edit' ? __('admin::goods.edit_goods') : __('admin::goods.copy_goods')));
            $this->smarty->assign('action_link', $this->goodsManageService->listLink($is_add, $code));
            $this->smarty->assign('goods', $goods);
            $this->smarty->assign('stages', $stages); //分期期数数据 bylu;
            $this->smarty->assign('goods_name_color', $goods_name_style[0]);
            $this->smarty->assign('goods_name_style', $goods_name_style[1]);

            $brand_info = $this->brandService->getBrandInfo($goods['brand_id']);
            $brand_name = $brand_info['brand_name'] ?? '';
            $this->smarty->assign('brand_name', $brand_name);

            $unit_list = $this->goodsManageService->getUnitList();
            $this->smarty->assign('unit_list', $unit_list);
            $this->smarty->assign('user_rank_list', get_user_rank_list());
            $this->smarty->assign('weight_unit', $is_add ? '1' : ($goods['goods_weight'] >= 1 ? '1' : '0.001'));
            $this->smarty->assign('cfg', config('shop'));
            $this->smarty->assign('form_act', $is_add ? 'insert' : ($act == 'edit' ? 'update' : 'insert'));
            if ($act == 'add' || $act == 'edit') {
                $this->smarty->assign('is_add', true);
            }
            if (!$is_add) {
                // 获取会员价格
                $member_price_list = $this->goodsManageService->get_member_price_list($goods_id);
                $this->smarty->assign('member_price_list', $member_price_list);
            }
            $this->smarty->assign('link_goods_list', $link_goods_list);
            $this->smarty->assign('group_goods_list', $group_goods_list);
            $this->smarty->assign('goods_article_list', $goods_article_list);
            $this->smarty->assign('img_list', $img_list);
            $this->smarty->assign('goods_name_limit_length', sprintf(__('admin::goods.goods_name_limit_length'), config('shop.goods_name_length')));
            $this->smarty->assign('name_max_length', config('shop.goods_name_length'));

            //获取分类数组
            //获取属性分类id
            $type_c_id = GoodsType::where('cat_id', $goods['goods_type'])->value('c_id');
            $type_c_id = $type_c_id ? $type_c_id : 0;
            $type_level = get_type_cat_arr(0, 0, 0, $goods['user_id']);
            $this->smarty->assign('type_level', $type_level);
            $cat_tree = get_type_cat_arr($type_c_id, 2, 0, $goods['user_id']);
            $cat_tree1 = ['checked_id' => $cat_tree['checked_id']];

            $goods_type_list = goods_type_list($goods['goods_type'], $goods['goods_id'], 'array', $type_c_id);
            $this->smarty->assign('goods_type_list', $goods_type_list);

            if ($cat_tree['checked_id'] > 0) {
                $cat_tree1 = get_type_cat_arr($cat_tree['checked_id'], 2, 0, $goods['user_id']);
            }
            $this->smarty->assign("type_c_id", $type_c_id);
            $this->smarty->assign("cat_tree", $cat_tree);
            $this->smarty->assign("cat_tree1", $cat_tree1);

            $cat_name = GoodsType::where('cat_id', $goods['goods_type'])->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';
            $this->smarty->assign('goods_type_name', $cat_name);
            $this->smarty->assign('gd', gd_version());
            $this->smarty->assign('thumb_width', config('shop.thumb_width'));
            $this->smarty->assign('thumb_height', config('shop.thumb_height'));

            $volume_price_list = $this->goodsCommonService->getVolumePriceList($goods_id);
            $this->smarty->assign('volume_price_list', $volume_price_list);

            /* 商家入驻分类 */
            if ($goods['user_id']) {
                $seller_shop_cat = seller_shop_cat($goods['user_id']);
            } else {
                $seller_shop_cat = [];
            }

            /* 获取下拉列表 by wu start */
            //设置商品分类
            $level_limit = 3;
            $category_level = [];

            if ($act == 'add') {
                for ($i = 1; $i <= $level_limit; $i++) {
                    $category_list = [];
                    if ($i == 1) {
                        $category_list = get_category_list();
                    }
                    $this->smarty->assign('cat_level', $i);
                    $this->smarty->assign('category_list', $category_list);
                    $category_level[$i] = $this->smarty->fetch('library/get_select_category.lbi');
                }
            }

            if ($act == 'edit' || $act == 'copy') {
                $parent_cat_list = get_select_category($goods['cat_id'], 1, true);

                for ($i = 1; $i <= $level_limit; $i++) {
                    $category_list = [];
                    if (isset($parent_cat_list[$i])) {
                        $category_list = get_category_list($parent_cat_list[$i], 0, $seller_shop_cat, $goods['user_id'], $i);
                    } elseif ($i == 1) {
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
            }

            $this->smarty->assign('category_level', $category_level);
            /* 获取下拉列表 by wu end */

            set_default_filter($goods_id, 0, $goods['user_id']); //设置默认筛选

            //获取下拉列表 by wu start
            $cat_info = MerchantsCategory::catInfo($goods['user_cat'])->first();
            $cat_info = $cat_info ? $cat_info->toArray() : [];
            $cat_info['is_show_merchants'] = $cat_info ? $cat_info['is_show'] : 0;

            set_seller_default_filter(0, $goods['user_cat'], $goods['user_id']); //设置默认筛选
            //获取下拉列表 by wu end

            get_recently_used_category($admin_id); //获取常见分类

            $res = GoodsTransport::select('tid', 'title');
            $res = $res->where('ru_id', $goods['user_id']);
            $transport_list = BaseRepository::getToArrayGet($res);

            $this->smarty->assign('transport_list', $transport_list); //商品运费 by wu

            $this->smarty->assign('user_id', $goods['user_id']);

            if (file_exists(MOBILE_DRP)) {
                //判断是否分销商家
                $is_dis = is_distribution($adminru['ru_id']);
                $this->smarty->assign('is_dis', $is_dis);
                // 是否有分销模块
                $this->smarty->assign('is_dir', 1);
            } else {
                $this->smarty->assign('is_dir', 0);
            }

            /* 当前时间输出出来 模板里面时间选择插件用到 */
            $now = TimeRepository::getLocalDate('Y-m-d H:i:s');
            $this->smarty->assign('now', $now);

            if (CROSS_BORDER === true) {
                // 跨境多商户
                $admin = app(CrossBorderService::class)->adminExists();

                if (!empty($admin)) {
                    $admin->smartyAssign();
                }
            }
            // 商品金额输入
            $price_format = config('shop.price_format') ?? 0;
            $this->smarty->assign('price_format', $price_format);

            $filter = ['audit_status' => 1];

            if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status')) {
                // 视频号
                $wxapp_shop_brand = app(\App\Modules\WxMedia\Services\WxappShopBrandService::class)->get_brand_list([], $filter);
                $wxapp_shop_brand = $wxapp_shop_brand['list'] ?? [];

                $filter = [
                    'is_selected' => 1,
                    'goods_show' => 1
                ];
                $wxapp_shop_cat = app(\App\Modules\WxMedia\Services\WxappShopCatService::class)->getCategoryList([], $filter);
                $wxapp_shop_cat = $wxapp_shop_cat['list'] ?? [];

                $this->smarty->assign('wxapp_shop_brand', $wxapp_shop_brand);
                $this->smarty->assign('wxapp_shop_cat', $wxapp_shop_cat);
            }

            /* 显示商品信息页面 */

            return $this->smarty->display('goods_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加新商品 编辑商品
        /*------------------------------------------------------ */

        elseif ($act == 'review_status') {

            $code = e(request()->get('extension_code', ''));

            $handler_list = [];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=card', 'title' => __('admin::goods.card'), 'icon' => 'icon-credit-card'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=replenish', 'title' => __('admin::goods.replenish'), 'icon' => 'icon-plus-sign'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=batch_card_add', 'title' => __('admin::goods.batch_card_add'), 'icon' => 'icon-paste'];

            if ($act == 'list' && isset($handler_list[$code])) {
                $this->smarty->assign('add_handler', $handler_list[$code]);
                $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '50_virtual_card_list']);
            } elseif ($act == 'trash') {
                $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '11_goods_trash']);
            } elseif ($act == 'review_status') {
                admin_priv('review_status');
                $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '01_review_status']);
            } else {
                $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '01_goods_list']);
            }

            $type = request()->get('type', 'not_audit');
            if ($type == 'not_pass') {
                $status = 2;
                $this->smarty->assign('ur_here', __('admin::goods.lab_review_not_pass'));
            } else {
                $status = 1;
                $this->smarty->assign('ur_here', __('admin::goods.lab_review_not_audit'));
            }

            $goods_list = $this->goodsManageService->getGoodsList(0, 1, '', $status, 1);

            $this->smarty->assign('goods_list', $goods_list['goods']);
            $this->smarty->assign('filter', $goods_list['filter']);
            $this->smarty->assign('record_count', $goods_list['record_count']);
            $this->smarty->assign('page_count', $goods_list['page_count']);
            $this->smarty->assign('full_page', 1);

            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()));

            $is_on_sale = 0;
            $suppliers_id = 0;
            $this->smarty->assign('is_on_sale', $is_on_sale);
            $this->smarty->assign('suppliers_id', $suppliers_id);

            /* 供货商名 */
            $suppliers_list_name = suppliers_list_name();
            $suppliers_exists = empty($suppliers_list_name) ? 0 : 1;
            $this->smarty->assign('suppliers_exists', $suppliers_exists);
            $this->smarty->assign('suppliers_list_name', $suppliers_list_name);
            unset($suppliers_list_name, $suppliers_exists);

            $this->smarty->assign('brand_list', get_brand_list());
            $this->smarty->assign('store_brand', get_store_brand_list()); //商家品牌

            $intro_list = $this->goodsManageService->getIntroList();
            $this->smarty->assign('intro_list', $intro_list);
            $this->smarty->assign('lang', BaseRepository::getArrayCollapse([__('admin::goods'), __('admin::common')]));
            $this->smarty->assign('type', $type);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            set_default_filter(); //设置默认筛选

            /* 排序标记 */
            $sort_flag = sort_flag($goods_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $this->smarty->assign('cfg', config('shop'));

            /* 显示商品信息页面 */

            return $this->smarty->display('goods_review_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'review_query') {

            $is_delete = intval(request()->get('is_delete', 0));
            $code = e(request()->get('extension_code', ''));
            $review_status = request()->get('review_status', 0);
            $goods_list = $this->goodsManageService->getGoodsList(0, 1, '', $review_status);

            $handler_list = [];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=card', 'title' => __('admin::goods.card'), 'img' => 'icon_send_bonus.gif'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=replenish', 'title' => __('admin::goods.replenish'), 'img' => 'icon_add.gif'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=batch_card_add', 'title' => __('admin::goods.batch_card_add'), 'img' => 'icon_output.gif'];

            if (isset($handler_list[$code])) {
                $this->smarty->assign('add_handler', $handler_list[$code]);
            }
            $this->smarty->assign('code', $code);
            $this->smarty->assign('goods_list', $goods_list['goods']);
            $this->smarty->assign('filter', $goods_list['filter']);
            $this->smarty->assign('record_count', $goods_list['record_count']);
            $this->smarty->assign('page_count', $goods_list['page_count']);
            $this->smarty->assign('list_type', $is_delete ? 'trash' : 'goods');
            $this->smarty->assign('use_storage', empty(config('shop.use_storage')) ? 0 : 1);

            /* 排序标记 */
            $sort_flag = sort_flag($goods_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            /* 获取商品类型存在规格的类型 */
            $specifications = get_goods_type_specifications();
            $this->smarty->assign('specifications', $specifications);

            $this->smarty->assign('nowTime', TimeRepository::getGmTime());

            return make_json_result(
                $this->smarty->fetch("goods_review_list.dwt"),
                '',
                ['filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 获取分类列表
        /*------------------------------------------------------ */
        elseif ($act == 'get_select_category_pro') {
            $goods_id = request()->get('goods_id', 0);
            $cat_id = request()->get('cat_id', 0);
            $cat_level = request()->get('cat_level', 0);
            $table = request()->get('table', 'category');
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $g_user_id = 0;
            if ($goods_id) {
                $g_user_id = Goods::where('goods_id', $goods_id)->value('user_id');
                $g_user_id = $g_user_id ?? 0;
                $seller_shop_cat = seller_shop_cat($g_user_id);
            } else {
                $seller_shop_cat = [];
            }

            $this->smarty->assign('cat_id', $cat_id);
            $this->smarty->assign('cat_level', $cat_level + 1);
            $this->smarty->assign('table', $table);
            $this->smarty->assign('category_list', get_category_list($cat_id, 2, $seller_shop_cat, $g_user_id, $cat_level + 1, $table));
            $result['content'] = $this->smarty->fetch('library/get_select_category.lbi');
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 设置常用分类
        /* ------------------------------------------------------ */
        elseif ($act == 'set_common_category_pro') {
            $cat_id = request()->get('cat_id', 0);
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $level_limit = 3;
            $category_level = [];
            $parent_cat_list = get_select_category($cat_id, 1, true);

            for ($i = 1; $i <= $level_limit; $i++) {
                $category_list = [];
                if (isset($parent_cat_list[$i])) {
                    $category_list = get_category_list($parent_cat_list[$i]);
                } elseif ($i == 1) {
                    $category_list = get_category_list();
                }
                $this->smarty->assign('cat_level', $i);
                $this->smarty->assign('category_list', $category_list);
                $category_level[$i] = $this->smarty->fetch('library/get_select_category.lbi');
            }

            $this->smarty->assign('cat_id', $cat_id);
            $result['content'] = $category_level;
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 处理扩展分类删除或者添加
        /* ------------------------------------------------------ */
        elseif ($act == 'deal_extension_category') {
            $goods_id = request()->get('goods_id', 0);
            $cat_id = request()->get('cat_id', 0);
            $type = request()->get('type', '');
            $other_catids = request()->get('other_catids', '');
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            if ($type == "add") {
                // 插入记录
                $data = [
                    'goods_id' => $goods_id,
                    'cat_id' => $cat_id
                ];
                GoodsCat::insert($data);
                if ($other_catids == '') {
                    $other_catids = $cat_id;
                } else {
                    $other_catids = $other_catids . "," . $cat_id;
                }
            } elseif ($type == "delete") {
                GoodsCat::where('goods_id', $goods_id)->where('cat_id', $cat_id)->delete();
                $other_catids = str_replace(',' . $cat_id, '', $other_catids);
            }
            $result['content'] = $other_catids;
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 获取商品模式列表
        /* ------------------------------------------------------ */
        elseif ($act == 'goods_model_list') {
            $goods_id = request()->get('goods_id', 0);
            $user_id = request()->get('user_id', 0);
            $model = request()->get('model', 0);
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            if ($model == 1) {
                $warehouse_goods_list = get_warehouse_goods_list($goods_id);
                $this->smarty->assign('warehouse_goods_list', $warehouse_goods_list);
            } elseif ($model == 2) {
                $warehouse_area_goods_list = get_warehouse_area_goods_list($goods_id);
                $this->smarty->assign('warehouse_area_goods_list', $warehouse_area_goods_list);
            }

            $this->smarty->assign('area_pricetype', intval(config('shop.area_pricetype')));
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('model', $model);

            $result['content'] = $this->smarty->fetch('library/goods_model_list.lbi');
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 切换商品类型
        /* ------------------------------------------------------ */
        elseif ($act == 'get_attribute') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('goods_id', 0);
            $goods_type = request()->get('goods_type', 0);
            $model = request()->get('modelAttr', -1);
            $warehouse_id = request()->get('warehouse_id', 0);
            $area_id = request()->get('area_id', 0);
            $area_city = request()->get('area_city', 0);

            $result = ['error' => 0, 'message' => '', 'content' => ''];

            //判断是否是贡云商品
            $cloud_count = Goods::where('goods_id', $goods_id)->value('cloud_id');
            $cloud_count = $cloud_count ? $cloud_count : 0;
            $this->smarty->assign('cloud_count', $cloud_count);

            $attribute = set_goods_attribute($goods_type, $goods_id, $model);

            $result['goods_attribute'] = $attribute['goods_attribute'];
            $result['goods_attr_gallery'] = $attribute['goods_attr_gallery'];
            $result['model'] = $model;
            $result['goods_id'] = $goods_id;
            $result['is_spec'] = $attribute['is_spec'];

            $result['region'] = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'city_id' => $area_city
            ];

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 设置属性表格
        /* ------------------------------------------------------ */
        elseif ($act == 'set_attribute_table' || $act == 'goods_attribute_query') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('goods_id', 0);
            $goods_type = request()->get('goods_type', 0);
            $attr_id_arr = request()->get('attr_id', '');
            $attr_id_arr = BaseRepository::getExplode($attr_id_arr);
            $attr_value_arr = request()->get('attr_value', '');
            $attr_value_arr = BaseRepository::getExplode($attr_value_arr);
            $goods_model = request()->get('goods_model', 0); //商品模式
            $warehouse_id = request()->get('warehouse_id', 0); //仓库id
            $region_id = request()->get('region_id', 0); //地区id
            $city_id = request()->get('city_id', 0); //地区id
            $search_attr = request()->get('search_attr', '');
            $search_attr = $search_attr == 'null' ? '' : $search_attr;

            $result = ['error' => 0, 'message' => '', 'content' => ''];

            //判断是否是贡云商品
            $cloud_count = Goods::where('goods_id', $goods_id)->value('cloud_id');
            $cloud_count = $cloud_count ? $cloud_count : 0;
            $this->smarty->assign('cloud_count', $cloud_count);

            /* ajax分页 start */
            $filter['goods_id'] = $goods_id;
            $filter['goods_type'] = $goods_type;
            $filter['attr_id'] = request()->get('attr_id');
            $filter['attr_value'] = request()->get('attr_value');
            $filter['goods_model'] = $goods_model;
            $filter['search_attr'] = $search_attr;
            $filter['warehouse_id'] = $warehouse_id;
            $filter['region_id'] = $region_id;
            $filter['city_id'] = $city_id;

            /* ajax分页 end */
            if ($search_attr) {
                $search_attr = explode(',', $search_attr);
            } else {
                $search_attr = [];
            }

            $group_attr = [
                'goods_id' => $goods_id,
                'goods_type' => $goods_type,
                'attr_id' => empty($attr_id_arr) ? '' : implode(',', $attr_id_arr),
                'attr_value' => empty($attr_value_arr) ? '' : implode(',', $attr_value_arr),
                'goods_model' => $goods_model,
                'region_id' => $region_id,
                'city_id' => $city_id
            ];

            $result['group_attr'] = json_encode($group_attr, JSON_UNESCAPED_UNICODE);

            //商品模式
            if ($goods_model == 0) {
                $model_name = "";
            } elseif ($goods_model == 1) {
                $model_name = __('admin::goods.warehouse');
            } elseif ($goods_model == 2) {
                $model_name = __('admin::goods.region');
            }

            if (config('shop.area_pricetype') == 1) {
                $region_name = RegionWarehouse::where('region_id', $city_id)->value('region_name');
            } else {
                $region_name = RegionWarehouse::where('region_id', $region_id)->value('region_name');
            }
            $region_name = $region_name ? $region_name : '';
            $this->smarty->assign('region_name', $region_name);
            $this->smarty->assign('goods_model', $goods_model);
            $this->smarty->assign('model_name', $model_name);

            //商品基本信息
            $res = Goods::select('market_price', 'shop_price', 'model_attr')->where('goods_id', $goods_id);
            $goods_info = BaseRepository::getToArrayFirst($res);
            $this->smarty->assign('goods_info', $goods_info);

            //将属性归类
            $attr_arr = [];
            if ($attr_id_arr) {
                foreach ($attr_id_arr as $key => $val) {
                    $attr_arr[$val][] = $attr_value_arr[$key];
                }
            }

            $attr_spec = [];
            $attribute_array = [];

            if (count($attr_arr) > 0) {
                //属性数据
                $i = 0;
                foreach ($attr_arr as $key => $val) {
                    $res = Attribute::select('attr_name', 'attr_type')->where('attr_id', $key);
                    $attr_info = BaseRepository::getToArrayFirst($res);

                    $attribute_array[$i]['attr_id'] = $key;
                    $attribute_array[$i]['attr_name'] = $attr_info['attr_name'];
                    $attribute_array[$i]['attr_value'] = $val;
                    /* 处理属性图片 start */
                    $attr_values_arr = [];
                    foreach ($val as $k => $v) {
                        $v = trim($v);

                        $where_select = [
                            'attr_id' => $key,
                            'attr_value' => $v,
                            'goods_id' => $goods_id
                        ];
                        $data = $this->goodsAttrService->getGoodsAttrId($where_select, [1, 2], 1);

                        if (!$data) {
                            $max_goods_attr_id = GoodsAttr::max('goods_attr_id');
                            $max_goods_attr_id = $max_goods_attr_id ? $max_goods_attr_id : 0;
                            $attr_sort = $max_goods_attr_id + 1;

                            $data = [
                                'goods_id' => $goods_id,
                                'attr_id' => $key,
                                'attr_value' => $v,
                                'attr_sort' => $attr_sort,
                                'admin_id' => session('admin_id')
                            ];
                            $insert_id = GoodsAttr::insertGetId($data);
                            $data['goods_attr_id'] = $insert_id;
                            $data['attr_type'] = $attr_info['attr_type'];
                            $data['attr_sort'] = $attr_sort;
                        }
                        $data['attr_id'] = $key;
                        $data['attr_value'] = $v;
                        $data['is_selected'] = 1;
                        $attr_values_arr[] = $data;
                    }

                    $attr_spec[$i] = $attribute_array[$i];
                    $attr_spec[$i]['attr_values_arr'] = $attr_values_arr;

                    $attribute_array[$i]['attr_values_arr'] = $attr_values_arr;

                    if ($attr_info['attr_type'] == 2) {
                        unset($attribute_array[$i]);
                    }
                    /* 处理属性图片 end */
                    $i++;
                }

                //删除复选属性后重设键名
                $new_attribute_array = [];
                foreach ($attribute_array as $key => $val) {
                    $new_attribute_array[] = $val;
                }
                $attribute_array = $new_attribute_array;

                //删除复选属性
                $attr_arr = get_goods_unset_attr($goods_id, $attr_arr);

                //将属性组合
                if (count($attr_arr) == 1) {
                    foreach (reset($attr_arr) as $key => $val) {
                        $attr_group[][] = $val;
                    }
                } else {
                    $attr_group = attr_group($attr_arr);
                }
                delete_invalid_goods_attr($attr_group, $goods_id, $goods_model, $region_id, $city_id);//去除无效的属性  值针对属性零时表
                //搜索筛选
                if (!empty($attr_group) && !empty($search_attr)) {
                    foreach ($attr_group as $k => $v) {
                        $array_intersect = array_intersect($search_attr, $v);//获取查询出的属性与搜索数组的差集
                        if (empty($array_intersect)) {
                            unset($attr_group[$k]);
                        }
                    }
                }
                /* ajax分页 start */
                $filter['page'] = $page = request()->get('page', 1);
                $filter['page_size'] = request()->get('page_size', 15);
                $products_list = $this->dsc->page_array($filter['page_size'], $filter['page'], $attr_group, 0, $filter);

                $filter = $products_list['filter'] ?? [];
                $attr_group = $products_list['list'] ?? [];
                /* ajax分页 end */

                //取得组合补充数据
                foreach ($attr_group as $key => $val) {
                    $group = [];

                    //组合信息
                    $attr_info = [];
                    foreach ($val as $k => $v) {
                        if ($v) {
                            $attr_info[$k]['attr_id'] = $attribute_array[$k]['attr_id'];

                            $where_select = [
                                'goods_id' => $goods_id,
                                'attr_id' => $attribute_array[$k]['attr_id'],
                                'attr_value' => $v,
                            ];

                            if (empty($goods_id)) {
                                $admin_id = get_admin_id();
                                $where_select['admin_id'] = $admin_id;
                            }

                            $goods_attr_info = $this->goodsAttrService->getGoodsAttrId($where_select, 1);
                            $goods_attr_id = $goods_attr_info['goods_attr_id'] ?? 0;

                            $attr_info[$k]['goods_attr_id'] = $goods_attr_id;
                            $attr_info[$k]['attr_value'] = $v;
                        }
                    }

                    //货品信息
                    $product_info = get_product_info_by_attr($goods_id, $attr_info, $goods_model, $region_id, 0, $city_id);
                    if (!empty($product_info)) {
                        $group = $product_info;
                        $group['changelog'] = 0;
                    } else {
                        $product_info = get_product_info_by_attr($goods_id, $attr_info, $goods_model, $region_id, 1, $city_id); //获取属性表零时数据
                        if ($product_info) {
                            $group = $product_info;
                        } else {
                            $group = insert_attr_changelog($goods_id, $attr_info, $goods_model, $warehouse_id, $region_id, $city_id);//录入新的零时表数据，且取出
                        }
                        $group['changelog'] = 1;
                    }
                    $group['attr_info'] = $attr_info;

                    if ($group) {
                        $attr_group[$key] = $group;
                    } else {
                        $attr_group = [];
                    }
                }

                $this->smarty->assign('attr_group', $attr_group);
                $this->smarty->assign('attribute_array', $attribute_array);

                /* ajax分页 start */
                $this->smarty->assign('filter', $filter);

                $filter['page'] = $filter['page'] ?? 1;

                $page_count_arr = seller_page($products_list, $filter['page']);
                $this->smarty->assign('page_count_arr', $page_count_arr);
                if ($act == 'set_attribute_table') {
                    $this->smarty->assign('full_page', 1);
                } else {
                    $this->smarty->assign('group_attr', $result['group_attr']);
                    $this->smarty->assign('add_shop_price', config('shop.add_shop_price'));
                    $this->smarty->assign('goods_attr_price', config('shop.goods_attr_price'));
                    return make_json_result($this->smarty->fetch('library/goods_attribute_query.lbi'), '', ['filter' => $products_list['filter'], 'page_count' => $products_list['page_count']]);
                }
                /* ajax分页 end */
            }

            $this->smarty->assign('group_attr', $result['group_attr']);
            $this->smarty->assign('add_shop_price', config('shop.add_shop_price'));
            $this->smarty->assign('goods_attr_price', config('shop.goods_attr_price'));

            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('goods_type', $goods_type);

            $result['content'] = $this->smarty->fetch('library/attribute_table.lbi');

            /* 处理属性图片 start */
            $this->smarty->assign('attr_spec', $attr_spec);
            $result['goods_attr_gallery'] = $this->smarty->fetch('library/goods_attr_gallery.lbi');
            /* 处理属性图片 end */

            $result['region'] = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $region_id,
                'city_id' => $city_id
            ];

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 插入关联商品描述，多商品相同描述内容
        /*------------------------------------------------------ */
        elseif ($act == 'add_desc') {
            admin_priv('goods_manage');

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'add_desc']);

            $action_link = ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
            $this->smarty->assign('action_link', $action_link);
            $this->smarty->assign('ur_here', __('admin::goods.lab_goods_desc'));

            LinkDescTemporary::where('ru_id', $adminru['ru_id'])->delete();
            //创建编辑器
            create_html_editor2('goods_desc', 'goods_desc', '');

            $desc_list = get_link_goods_desc_list($adminru['ru_id']);

            $this->smarty->assign('form_act', 'insert_link_desc');

            $this->smarty->assign('desc_list', $desc_list['desc_list']);
            $this->smarty->assign('filter', $desc_list['filter']);
            $this->smarty->assign('record_count', $desc_list['record_count']);
            $this->smarty->assign('page_count', $desc_list['page_count']);
            $this->smarty->assign('full_page', 1);

            set_default_filter(); //设置默认筛选

            /* 显示商品信息页面 */

            return $this->smarty->display('goods_desc.dwt');
        }

        /*------------------------------------------------------ */
        //-- 插�        ��        �联商品描述，多商品�        �同描述内        容 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'desc_list') {
            admin_priv('goods_manage');

            $type = addslashes(request()->get('type', ''));

            if (!empty($type) && $type == 'seller') {
                $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'seller_desc_list']);
            } else {
                $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'desc_list']);
            }

            $action_link = ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
            $this->smarty->assign('action_link', $action_link);
            $this->smarty->assign('ur_here', __('admin::goods.lab_goods_desc'));

            $desc_list = get_link_goods_desc_list($adminru['ru_id']);
            $this->smarty->assign('desc_list', $desc_list['desc_list']);
            $this->smarty->assign('filter', $desc_list['filter']);
            $this->smarty->assign('record_count', $desc_list['record_count']);
            $this->smarty->assign('page_count', $desc_list['page_count']);
            $this->smarty->assign('full_page', 1);

            //分页
            $page = request()->get('page', 1);
            $page_count_arr = seller_page($desc_list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            /* 显示商品信息页面 */

            return $this->smarty->display('goods_desc_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'desc_query') {
            $desc_list = get_link_goods_desc_list($adminru['ru_id']);
            $this->smarty->assign('desc_list', $desc_list['desc_list']);
            $this->smarty->assign('filter', $desc_list['filter']);
            $this->smarty->assign('record_count', $desc_list['record_count']);
            $this->smarty->assign('page_count', $desc_list['page_count']);

            //分页
            $page = request()->get('page', 1);
            $page_count_arr = seller_page($desc_list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            return make_json_result(
                $this->smarty->fetch('goods_desc_list.dwt'),
                '',
                ['filter' => $desc_list['filter'], 'page_count' => $desc_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 插入关联商品描述数据，多商品同描述内容
        /*------------------------------------------------------ */
        elseif ($act == 'edit_link_desc') {
            admin_priv('goods_manage');

            $id = request()->get('id', 0);

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => 'add_desc']);
            $this->smarty->assign('ur_here', __('admin::goods.desc_edit'));

            if ($id) {
                $res = LinkGoodsDesc::select('ru_id', 'goods_id')->where('id', $id);
                $row = BaseRepository::getToArrayFirst($res);
                $ru_id = $row['ru_id'];
            } else {
                $ru_id = $adminru['ru_id'];
            }

            LinkDescTemporary::where('ru_id', $ru_id)->delete();
            if ($ru_id) {
                $other['goods_id'] = $row['goods_id'];
                $other['ru_id'] = $ru_id;
                LinkDescTemporary::insert($other);
            }

            $action_link = ['href' => 'goods.php?act=add_desc', 'text' => __('admin::common.go_back')];
            $this->smarty->assign('action_link', $action_link);
            $action_link2 = ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
            $this->smarty->assign('action_link2', $action_link2);

            $res = LinkGoodsDesc::where('id', $id);
            $goods_desc = BaseRepository::getToArrayFirst($res);

            $link_goods_list = get_linked_goods_desc($id);

            if (config('shop.open_oss') == 1) {
                $bucket_info = $this->dscRepository->getBucketInfo();
                $endpoint = $bucket_info['endpoint'];
            } else {
                $endpoint = url('/');
            }

            if ($goods_desc['goods_desc']) {
                $desc_preg = get_goods_desc_images_preg($endpoint, $goods_desc['goods_desc']);
                $goods_desc['goods_desc'] = $desc_preg['goods_desc'];
            }

            //创建编辑器
            create_html_editor2('goods_desc', 'goods_desc', $goods_desc['goods_desc']);

            $this->smarty->assign('goods', $goods_desc);
            $this->smarty->assign('link_goods_list', $link_goods_list);
            $this->smarty->assign('form_act', 'update_link_desc');

            set_default_filter(); //设置默认筛选

            /* 显示商品信息页面 */

            return $this->smarty->display('goods_desc.dwt');
        }

        /*------------------------------------------------------ */
        //-- 插�        ��        �联商品描述，多商品�        �同描述内        容 ecmoban模板堂 --zhuo(移除)
        /*------------------------------------------------------ */
        elseif ($act == 'add_link_desc') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $linked_array = dsc_decode(request()->get('add_ids', ''), true);
            $linked_goods = dsc_decode(request()->get('JSON', ''), true);
            $id = $linked_goods[0];

            get_add_edit_link_desc($linked_array, 0, $id);

            $ru_id = $adminru['ru_id'];
            if ($id) {
                $ru_id = LinkGoodsDesc::where('id', $id)->value('ru_id');
                $ru_id = $ru_id ? $ru_id : 0;
            }
            $linked_goods = get_linked_goods_desc(0, $ru_id);

            $options = [];
            foreach ($linked_goods as $val) {
                $options[] = ['value' => $val['goods_id'],
                    'text' => $val['goods_name'],
                    'data' => ''];
            }

            clear_cache_files();
            return make_json_result($options);
        }

        /*------------------------------------------------------ */
        //-- 删除�        �联商品描述，多商品�        �同描述内        容 ecmoban模板堂 --zhuo(移除)
        /*------------------------------------------------------ */
        elseif ($act == 'drop_link_desc') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $drop_goods = dsc_decode(request()->get('drop_ids', ''), true);
            $linked_goods = dsc_decode(request()->get('JSON', ''), true);
            $id = $linked_goods[0];

            get_add_edit_link_desc($drop_goods, 1, $id);

            $ru_id = $adminru['ru_id'];
            if ($id) {
                $ru_id = LinkGoodsDesc::where('id', $id)->value('ru_id');
                $ru_id = $ru_id ? $ru_id : 0;
            }
            $linked_goods = get_linked_goods_desc(0, $ru_id);

            $options = [];
            foreach ($linked_goods as $val) {
                $options[] = [
                    'value' => $val['goods_id'],
                    'text' => $val['goods_name'],
                    'data' => ''];
            }

            if (empty($linked_goods)) {
                LinkDescTemporary::where('ru_id', $adminru['ru_id'])->delete();
            }

            clear_cache_files();
            return make_json_result($options);
        }

        /*------------------------------------------------------ */
        //-- 插�        ��        �联商品描述数据，多商品�        �同描述内        容 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'insert_link_desc' || $act == 'update_link_desc') {
            $desc_name = request()->get('desc_name', '');
            $goods_desc = request()->get('goods_desc', '');
            $id = request()->get('id', 0);
            $ru_id = request()->get('ru_id', $adminru['ru_id']);

            $review_status = request()->get('review_status', 1);
            $review_content = request()->get('review_content', '');

            $goods_id = LinkDescTemporary::where('ru_id', $ru_id)->value('goods_id');
            $goods_id = $goods_id ? $goods_id : 0;

            $other = [
                'ru_id' => $ru_id,
                'desc_name' => $desc_name,
                'goods_desc' => $goods_desc
            ];

            if ($goods_id) {
                $other['goods_id'] = $goods_id;
            }

            if ($ru_id) {
                $other['review_status'] = $review_status;
                $other['review_content'] = $review_content;
            } else {
                $other['review_status'] = 3;
            }

            if (!empty($desc_name)) {
                LinkDescGoodsid::where('d_id', $id)->delete();

                if ($id > 0) {
                    LinkGoodsDesc::where('id', $id)->update($other);
                    $link_cnt = __('admin::common.edit_success');
                } else {
                    $id = LinkGoodsDesc::insertGetId($other);
                    $link_cnt = __('admin::common.add_success');
                }
            } else {
                $link_cnt = __('admin::goods.confirm_batch_delete');
            }

            if (!empty($goods_id)) {
                get_add_desc_goodsId($goods_id, $id);
            }

            if ($id > 0) {
                $link[0] = ['text' => __('admin::common.go_back'), 'href' => "goods.php?act=edit_link_desc&id=" . $id];
            }

            $link[1] = ['text' => __('admin::goods.add_relation_desc'), 'href' => "goods.php?act=add_desc"];
            $link[2] = ['text' => __('admin::common.01_goods_list'), 'href' => 'goods.php?act=list'];
            return sys_msg($link_cnt, 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 插�        ��        �联商品描述数据，多商品�        �同描述内        容 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'del_link_desc') {
            $id = request()->get('id', 0);

            LinkGoodsDesc::where('id', $id)->delete();

            LinkDescGoodsid::where('d_id', $id)->delete();

            $link[0] = ['text' => __('admin::goods.lab_add_desc'), 'href' => "goods.php?act=add_desc"];
            $link[1] = ['text' => __('admin::goods.lab_desc_list'), 'href' => "goods.php?act=desc_list"];
            $link[2] = ['text' => __('admin::common.01_goods_list'), 'href' => 'goods.php?act=list'];
            return sys_msg(__('admin::goods.lab_dellink_desc'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 插入商品 更新商品
        /*------------------------------------------------------ */

        elseif ($act == 'insert' || $act == 'update') {
            $code = request()->get('extension_code', '');
            $goods_sn = e(request()->get('goods_sn', ''));

            if ($code == 'virtual_card') {
                admin_priv('virualcard'); // 检查权限
            } else {
                admin_priv('goods_manage'); // 检查权限
            }

            /* 检查货号是否重复 */
            if ($goods_sn) {
                $goods_id = request()->get('goods_id', 0);
                if ($goods_id) {
                    $goods = get_admin_goods_info($goods_id);
                    $seller_id = $goods['user_id'];
                } else {
                    $seller_id = $adminru['ru_id'];
                }

                $res = Goods::where('goods_sn', $goods_sn)
                    ->where('is_delete', 0)
                    ->where('goods_id', '<>', $goods_id)
                    ->where('user_id', $seller_id)
                    ->count();
                if ($res > 0) {
                    return sys_msg(__('admin::goods.goods_sn_exists'), 1, [], false);
                }
            }

            /* 插入还是更新的标识 */
            $is_insert = $act == 'insert';

            $original_img = request()->get('original_img', '');
            $goods_img = request()->get('goods_img', '');
            $goods_thumb = request()->get('goods_thumb', '');

            /* 商品外链图 start */
            $is_img_url = request()->get('is_img_url', 0);
            $goods_img_url = request()->get('goods_img_url', '');

            if (!empty($goods_img_url) && ($goods_img_url != 'http://') && (strpos($goods_img_url, 'http://') !== false || strpos($goods_img_url, 'https://') !== false) && $is_img_url == 1) {
                $admin_temp_dir = "seller";
                $admin_temp_dir = storage_public("temp" . '/' . $admin_temp_dir . '/' . "admin_" . $admin_id);

                if (!file_exists($admin_temp_dir)) {
                    make_dir($admin_temp_dir);
                }
                if ($this->dscRepository->getHttpBasename($goods_img_url, $admin_temp_dir)) {
                    $original_img = $admin_temp_dir . "/" . basename($goods_img_url);
                }
                if ($original_img === false) {
                    return sys_msg($image->error_msg(), 1, [], false);
                }

                $goods_img = $original_img;   // 商品图片

                /* 复制一份相册图片 */
                /* 添加判断是否自动生成相册图片 */
                if (config('shop.auto_generate_gallery')) {
                    $img = $original_img;   // 相册图片
                    $pos = strpos(basename($img), '.');
                    $newname = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
                    if (!copy($img, $newname)) {
                        return sys_msg('fail to copy file: ' . realpath('../' . $img), 1, [], false);
                    }
                    $img = $newname;

                    $gallery_img = $img;
                    $gallery_thumb = $img;
                }

                // 如果系统支持GD，缩放商品图片，且给商品图片和相册图片加水印
                if ($image->gd_version() > 0) {
                    if (empty($is_url_goods_img)) {
                        $img_wh = $image->get_width_to_height($goods_img, config('shop.image_width'), config('shop.image_height'));
                        $image_width = isset($img_wh['image_width']) ? $img_wh['image_width'] : config('shop.image_width');
                        $image_height = isset($img_wh['image_height']) ? $img_wh['image_height'] : config('shop.image_height');

                        // 如果设置大小不为0，缩放图片
                        $goods_img = $image->make_thumb(['img' => $goods_img, 'type' => 1], $image_width, $image_height);
                        if ($goods_img === false) {
                            return sys_msg($image->error_msg(), 1, [], false);
                        }

                        $gallery_img = $image->make_thumb(['img' => $gallery_img, 'type' => 1], $image_width, $image_height);
                        if ($gallery_img === false) {
                            return sys_msg($image->error_msg(), 1, [], false);
                        }

                        // 加水印
                        if (intval(config('shop.watermark_place')) > 0 && !empty(config('shop.watermark'))) {
                            if ($image->add_watermark($goods_img, '', config('shop.watermark'), config('shop.watermark_place'), config('shop.watermark_alpha')) === false) {
                                return sys_msg($image->error_msg(), 1, [], false);
                            }
                            /* 添加判断是否自动生成相册图片 */
                            if (config('shop.auto_generate_gallery')) {
                                if ($image->add_watermark($img, '', config('shop.watermark'), config('shop.watermark_place'), config('shop.watermark_alpha')) === false) {
                                    return sys_msg($image->error_msg(), 1, [], false);
                                }
                            }
                        }
                    }

                    // 相册缩略图
                    /* 添加判断是否自动生成相册图片 */
                    if (config('shop.auto_generate_gallery')) {
                        if (config('shop.thumb_width') != 0 || config('shop.thumb_height') != 0) {
                            $gallery_thumb = $image->make_thumb(['img' => $img, 'type' => 1], config('shop.thumb_width'), config('shop.thumb_height'));
                            if ($gallery_thumb === false) {
                                return sys_msg($image->error_msg(), 1, [], false);
                            }
                        }
                    }
                }

                // 未上传，如果自动选择生成，且上传了商品图片，生成所略图
                if (!empty($original_img)) {
                    // 如果设置缩略图大小不为0，生成缩略图
                    if (config('shop.thumb_width') != 0 || config('shop.thumb_height') != 0) {
                        $goods_thumb = $image->make_thumb(['img' => $original_img, 'type' => 1], config('shop.thumb_width'), config('shop.thumb_height'));
                        if ($goods_thumb === false) {
                            return sys_msg($image->error_msg(), 1, [], false);
                        }
                    } else {
                        $goods_thumb = $original_img;
                    }
                }
            }
            /* 商品外链图 end */

            /* 如果没有输入商品货号则自动生成一个商品货号 */
            if (empty($goods_sn)) {
                if ($is_insert) {
                    $max_id = Goods::max('goods_id');
                    $max_id = $max_id ? $max_id : 0;
                    $max_id = $max_id + 1;
                } else {
                    $max_id = request()->get('goods_id');
                }
                $goods_sn = $this->goodsManageService->generateGoodSn($max_id);
            }

            $goods_img_id = request()->get('img_id', ''); //相册

            /* 处理商品数据 */
            $keyword_id = request()->get('keyword_id', []); //关键词
            $shop_price = request()->get('shop_price', 0);
            $shop_price = floatval($shop_price);
            $market_price = request()->get('market_price', 0);
            $market_price = floatval($market_price);
            $promote_price = request()->get('promote_price', 0);
            $promote_price = floatval($promote_price);
            $cost_price = request()->get('cost_price', 0);
            $cost_price = floatval($cost_price);

            $is_promote = request()->get('is_promote', 0);

            $promote_start_date = ($is_promote && request()->get('promote_start_date', 0)) ? TimeRepository::getLocalStrtoTime(request()->get('promote_start_date')) : 0;
            $promote_end_date = ($is_promote && request()->get('promote_end_date', 0)) ? TimeRepository::getLocalStrtoTime(request()->get('promote_end_date')) : 0;
            $weight_unit = request()->get('weight_unit', 0);
            $goods_weight = request()->get('goods_weight', 0);
            $goods_weight = $goods_weight * $weight_unit;
            $is_best = request()->get('is_best', 0) ? 1 : 0;
            $is_new = request()->get('is_new', 0) ? 1 : 0;
            $is_hot = request()->get('is_hot', 0) ? 1 : 0;
            $is_on_sale = request()->get('is_on_sale', 0) ? 1 : 0;
            $is_show = request()->get('is_show', 0) ? 1 : 0;
            $is_alone_sale = request()->get('is_alone_sale', 0) ? 1 : 0;
            $is_shipping = request()->get('is_shipping', 0) ? 1 : 0;
            $goods_number = request()->get('goods_number', 0);
            $warn_number = request()->get('warn_number', 0);
            $goods_type = request()->get('goods_type', 0);
            $give_integral = request()->get('give_integral', -1);
            $rank_integral = request()->get('rank_integral', -1);
            $suppliers_id = request()->get('suppliers_id', 0);
            $commission_rate = request()->get('commission_rate', 0);
            $old_commission_rate = request()->get('old_commission_rate', 0);
            $goods_shipai = request()->get('goods_shipai', '');
            $is_volume = request()->get('is_volume', 0);
            $is_fullcut = request()->get('is_fullcut', 0);
            $review_status = request()->get('review_status', 5);
            $review_content = request()->get('review_content', '');
            $goods_video = request()->get('goods_video', '');

            /* 微分销 */
            $is_distribution = request()->get('is_distribution', 0); //如果选择商品分销则判断分销佣金百分比是否在0-100之间 如果不是则设置无效 liu  dis
            $dis_commission = request()->get('dis_commission', 0);
            $is_discount = (int)request()->input('is_discount', 0); // 是否参与会员特价权益

            $bar_code = request()->get('bar_code', '');
            $goods_name_color = request()->get('goods_name_color', '');
            $goods_name_style = request()->get('goods_name_style', '');
            $goods_name_style = $goods_name_color . '+' . $goods_name_style;
            $other_catids = request()->get('other_catids', '');

            $catgory_id = request()->get('cat_id', '');

            $measure_unit = Category::where('cat_id', $catgory_id)->value('measure_unit');
            $goods_unit = request()->get('goods_unit', $measure_unit ? $measure_unit : '个');//商品单位

            if (CROSS_BORDER === true) { // 跨境多商户
                $free_rate = request()->get('free_rate', 0);
            }
            //常用分类 by wu
            if (empty($catgory_id)) {
                $catgory_id = request()->get('recently_used_category', 0);
            }

            $brand_id = request()->get('brand_id', '');

            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);
            $brand_name = $brandList[$brand_id]['brand_name'] ?? '';

            //ecmoban模板堂 --zhuo
            $store_category = request()->get('store_category', 0);
            if ($store_category > 0) {
                $catgory_id = $store_category;
            }

            if ($is_insert) {
                insert_recently_used_category($catgory_id, $admin_id);
            }

            $stages = request()->get('is_stages', 0) ? serialize(request()->get('stages_num')) : ''; //分期期数;
            $stages_rate = request()->get('stages_rate', 0); //分期费率;

            $model_price = request()->get('model_price', 0);
            $model_inventory = request()->get('model_inventory', 0);
            $model_attr = request()->get('model_attr', 0);

            //ecmoban模板堂 --zhuo start 限购
            $xiangou_num = request()->get('xiangou_num', 0);
            $is_xiangou = empty($xiangou_num) ? 0 : 1;
            $xiangou_start_date = ($is_xiangou && request()->get('xiangou_start_date', 0)) ? TimeRepository::getLocalStrtoTime(request()->get('xiangou_start_date')) : 0;
            $xiangou_end_date = ($is_xiangou && request()->get('xiangou_end_date', 0)) ? TimeRepository::getLocalStrtoTime(request()->get('xiangou_end_date')) : 0;
            //ecmoban模板堂 --zhuo end 限购

            // 最小起订量
            $minimum = request()->get('minimum', 0);
            $is_minimum = request()->get('is_minimum', 0);
            $is_minimum = empty($minimum) || empty($is_minimum) ? 0 : 1;
            $minimum = empty($minimum) || empty($is_minimum) ? 0 : $minimum;
            $minimum_start_date = ($is_minimum && request()->get('minimum_start_date', 0)) ? TimeRepository::getLocalStrtoTime(request()->get('minimum_start_date')) : 0;
            $minimum_end_date = ($is_minimum && request()->get('minimum_end_date', 0)) ? TimeRepository::getLocalStrtoTime(request()->get('minimum_end_date')) : 0;

            //ecmoban模板堂 --zhuo start 促销满减
            $cfull = request()->get('cfull', []);
            $creduce = request()->get('creduce', []);
            $c_id = request()->get('c_id', []);

            $sfull = request()->get('sfull', []);
            $sreduce = request()->get('sreduce', []);
            $s_id = request()->get('s_id', []);

            $largest_amount = request()->get('largest_amount', 0);
            $largest_amount = floatval($largest_amount);
            //ecmoban模板堂 --zhuo end 促销满减

            $group_number = request()->get('group_number', 0);

            $store_new = request()->get('store_new', 0) ? 1 : 0;
            $store_hot = request()->get('store_hot', 0) ? 1 : 0;
            $store_best = request()->get('store_best', 0) ? 1 : 0;

            $goods_name = request()->input('goods_name', '');
            $goods_name = mb_substr($goods_name, 0, config('shop.goods_name_length')); // 商品名称截取

            if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                $goods_name = StrRepository::replaceFirst($goods_name, $brand_name);
                $goods_name = StrRepository::replaceFirst($goods_name, config('app.replace_symbol'));
                $goods_name = $brand_name . config('app.goods_symbol') . $goods_name;
            }

            //by guan start
            $pin = new Pinyin();
            $pinyin = $pin->Pinyin($goods_name, 'UTF8');
            //by guan end

            $user_cat = request()->get('user_cat', 0);

            $freight = request()->get('freight', 0);
            $shipping_fee = request()->get('shipping_fee', 0) && $freight == 1 ? floatval(request()->get('shipping_fee')) : '0.00';
            $tid = request()->get('tid', 0) && $freight == 2 ? intval(request()->get('tid')) : 0;

            $goods_cause = "";
            $cause = request()->get('return_type', []);

            if ($cause) {
                for ($i = 0; $i < count($cause); $i++) {
                    if ($i == 0) {
                        $goods_cause = $cause[$i];
                    } else {
                        $goods_cause = $goods_cause . "," . $cause[$i];
                    }
                }
            }

            $time = TimeRepository::getGmTime();

            $keywords = request()->get('keywords', '');
            $goods_brief = request()->get('goods_brief', '');
            $seller_note = request()->get('seller_note', '');
            $integral = request()->get('integral', '');
            $goods_desc = request()->get('goods_desc', '');
            $desc_mobile = request()->get('desc_mobile', '');
            $goods_product_tag = request()->get('goods_product_tag', '');
            $goods_tag = request()->get('goods_tag', '');
            $goods_shipai = request()->get('goods_shipai', '');

            /* 入库 */
            if ($is_insert) {
                $other = [
                    'goods_name' => $goods_name,
                    'goods_name_style' => $goods_name_style,
                    'goods_sn' => $goods_sn,
                    'goods_video' => $goods_video ?? '',
                    'bar_code' => $bar_code ?? '',
                    'cat_id' => $catgory_id,
                    'user_cat' => $user_cat,
                    'brand_id' => $brand_id,
                    'shop_price' => $shop_price,
                    'market_price' => $market_price,
                    'cost_price' => $cost_price,
                    'is_promote' => $is_promote,
                    'promote_price' => $promote_price,
                    'promote_start_date' => $promote_start_date,
                    'promote_end_date' => $promote_end_date,
                    'goods_img' => $goods_img,
                    'goods_thumb' => $goods_thumb,
                    'original_img' => $original_img,
                    'keywords' => $keywords ?? '',
                    'goods_brief' => $goods_brief ?? '',
                    'seller_note' => $seller_note ?? '',
                    'goods_weight' => $goods_weight,
                    'goods_number' => $goods_number,
                    'warn_number' => $warn_number,
                    'integral' => $integral,
                    'give_integral' => $give_integral,
                    'is_best' => $is_best,
                    'is_new' => $is_new,
                    'is_hot' => $is_hot,
                    'is_on_sale' => $is_on_sale,
                    'is_show' => $is_show,
                    'is_alone_sale' => $is_alone_sale,
                    'is_shipping' => $is_shipping,
                    'goods_desc' => $goods_desc ?? '',
                    'desc_mobile' => $desc_mobile ?? '',
                    'add_time' => $time,
                    'last_update' => $time,
                    'goods_type' => $goods_type,
                    'rank_integral' => $rank_integral,
                    'suppliers_id' => $suppliers_id,
                    'goods_shipai' => $goods_shipai,
                    'user_id' => $adminru['ru_id'],
                    'model_price' => $model_price,
                    'model_inventory' => $model_inventory,
                    'model_attr' => $model_attr,
                    'review_status' => $review_status,
                    'commission_rate' => $commission_rate,
                    'group_number' => $group_number ?? '',
                    'store_new' => $store_new,
                    'store_hot' => $store_hot,
                    'store_best' => $store_best,
                    'goods_cause' => $goods_cause,
                    'goods_product_tag' => $goods_product_tag ?? '',
                    'goods_tag' => $goods_tag ?? '',
                    'is_volume' => $is_volume,
                    'is_fullcut' => $is_fullcut,
                    'is_xiangou' => $is_xiangou,
                    'xiangou_num' => $xiangou_num,
                    'xiangou_start_date' => $xiangou_start_date,
                    'xiangou_end_date' => $xiangou_end_date,
                    'largest_amount' => $largest_amount,
                    'pinyin_keyword' => $pinyin,
                    'stages' => $stages,
                    'stages_rate' => $stages_rate ?? '',
                    'goods_unit' => $goods_unit ?? '',
                    'freight' => $freight,
                    'shipping_fee' => $shipping_fee,
                    'tid' => $tid,
                    'is_minimum' => $is_minimum,
                    'minimum' => $minimum,
                    'minimum_start_date' => $minimum_start_date,
                    'minimum_end_date' => $minimum_end_date,
                    'is_discount' => $is_discount
                ];

                if (CROSS_BORDER === true) { // 跨境多商户
                    $other['free_rate'] = $free_rate ?? '';
                }

                if (file_exists(MOBILE_DRP)) {
                    $other['is_distribution'] = $is_distribution;
                    $other['dis_commission'] = $dis_commission ?? '';
                }

                if (!empty($code)) {
                    $other['is_real'] = 0;
                    $other['extension_code'] = $code ?? '';
                }

                $other = BaseRepository::recursiveNullVal($other);
                $goods_id = Goods::insertGetId($other);

                // 视频号商品二维码
                $sync_media = request()->get('sync_media', 0);
                if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status') && $sync_media == 1 && file_exists(WXAPP_MEDIA_CONCISE)) {
                    \App\Modules\WxMedia\Models\WxappMediaGoodsQrcod::query()->where('goods_id', 0)
                        ->where('admin_id', session('admin_id'))
                        ->update([
                            'goods_id' => $goods_id
                        ]);
                }

                $keywordList = KeywordList::select('id', 'name')->whereIn('id', $keyword_id);
                $keywordList = BaseRepository::getToArrayGet($keywordList);
                $list = BaseRepository::getColumn($keywordList, 'name', 'id');
                $nameList = $list ? implode($list, ' ') : '';

                if ($nameList) {
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

                //库存日志
                $not_number = !empty($goods_number) ? 1 : 0;
                $number = "+ " . $goods_number;
                $use_storage = 7;
            } else {
                $goods_id = request()->get('goods_id', 0);

                $review_goods = isset($review_goods) ? $review_goods : '';

                get_goods_file_content($goods_id, config('shop.goods_file'), $adminru['ru_id'], $review_goods); //编辑商品需审核通过

                $goodsInfo = get_admin_goods_info($goods_id);

                $other = [
                    'goods_name' => $goods_name,
                    'goods_name_style' => $goods_name_style,
                    'goods_sn' => $goods_sn,
                    'bar_code' => $bar_code,
                    'cat_id' => $catgory_id,
                    'brand_id' => $brand_id,
                    'shop_price' => $shop_price,
                    'market_price' => $market_price,
                    'cost_price' => $cost_price,
                    'is_promote' => $is_promote,
                    'is_volume' => $is_volume,
                    'is_fullcut' => $is_fullcut,
                    'commission_rate' => $commission_rate,
                    'model_price' => $model_price,
                    'model_inventory' => $model_inventory,
                    'model_attr' => $model_attr,
                    'largest_amount' => $largest_amount,
                    'group_number' => $group_number,
                    'store_new' => $store_new,
                    'store_hot' => $store_hot,
                    'store_best' => $store_best,
                    'goods_unit' => $goods_unit,
                    'is_xiangou' => $is_xiangou,
                    'xiangou_num' => $xiangou_num,
                    'xiangou_start_date' => $xiangou_start_date,
                    'xiangou_end_date' => $xiangou_end_date,
                    'goods_product_tag' => $goods_product_tag,
                    'goods_tag' => $goods_tag,
                    'pinyin_keyword' => $pinyin,
                    'stages' => $stages,
                    'stages_rate' => $stages_rate,
                    'goods_cause' => $goods_cause,
                    'user_cat' => $user_cat,
                    'freight' => $freight,
                    'shipping_fee' => $shipping_fee,
                    'tid' => $tid,
                    'promote_price' => $promote_price,
                    'promote_start_date' => $promote_start_date,
                    'suppliers_id' => $suppliers_id,
                    'promote_end_date' => $promote_end_date,
                    'is_minimum' => $is_minimum,
                    'minimum' => $minimum,
                    'minimum_start_date' => $minimum_start_date,
                    'minimum_end_date' => $minimum_end_date,
                    'is_discount' => $is_discount
                ];

                if (CROSS_BORDER === true) { // 跨境多商户
                    $other['free_rate'] = $free_rate;
                }

                /* 微分销 */
                if (file_exists(MOBILE_DRP)) {
                    $other['dis_commission'] = $dis_commission;
                    $other['is_distribution'] = $is_distribution;
                }

                /* 如果有上传图片，需要更新数据库 */
                if ($goods_img) {
                    $other['goods_img'] = $goods_img;
                    $other['original_img'] = $original_img;
                }

                if ($goods_thumb) {
                    $other['goods_thumb'] = $goods_thumb;
                }

                if (!empty($code)) {
                    $other['is_real'] = 0;
                    $other['extension_code'] = $code;
                }

                if ($goodsInfo['user_id'] > 0) {
                    $other['review_status'] = $review_status;
                    $other['review_content'] = $review_content;
                }

                $other['keywords'] = $keywords;
                $other['goods_brief'] = $goods_brief;
                $other['seller_note'] = $seller_note;
                $other['goods_weight'] = $goods_weight;
                $other['goods_number'] = $goods_number;
                $other['warn_number'] = $warn_number;
                $other['integral'] = $integral;
                $other['give_integral'] = $give_integral;
                $other['rank_integral'] = $rank_integral;
                $other['is_best'] = $is_best;
                $other['is_new'] = $is_new;
                $other['is_hot'] = $is_hot;
                $other['is_on_sale'] = $is_on_sale;
                $other['is_show'] = $is_show;
                $other['is_alone_sale'] = $is_alone_sale;
                $other['is_shipping'] = $is_shipping;
                $other['goods_desc'] = $goods_desc;
                $other['desc_mobile'] = $desc_mobile;
                $other['goods_shipai'] = $goods_shipai;
                $other['last_update'] = $time;
                $other['goods_type'] = $goods_type;

                //商品操作日志     更新前数据
                $goods_info = Goods::where('goods_id', $goods_id);
                $goods_info = BaseRepository::getToArrayFirst($goods_info);

                Goods::where('goods_id', $goods_id)->update($other);

                /* 更新预售商品名称 */
                PresaleActivity::where('goods_id', $goods_id)->update(['goods_name' => $goods_name]);

                //库存日志
                if ($goods_number > $goodsInfo['goods_number']) {
                    $not_number = $goods_number - $goodsInfo['goods_number'];
                    $not_number = !empty($not_number) ? 1 : 0;
                    $number = $goods_number - $goodsInfo['goods_number'];
                    $number = "+ " . $number;
                    $use_storage = 13;
                } else {
                    $not_number = $goodsInfo['goods_number'] - $goods_number;
                    $not_number = !empty($not_number) ? 1 : 0;
                    $number = $goodsInfo['goods_number'] - $goods_number;
                    $number = "- " . $number;
                    $use_storage = 8;
                }

                //商品操作日志 记录更新前与更新后数据
                $user_rank = request()->get('user_rank', []);
                $user_price = request()->get('user_price', []);
                $member_price = [];
                if ($user_price) {
                    $member_price = array_combine($user_rank, $user_price);
                }
                $volume_price = [];
                if (request()->get('is_volume', 0)) {
                    $volume_price = array_combine(request()->get('volume_number'), request()->get('volume_price'));
                }

                $extendParam = [
                    'logs_change_new' => [
                        'shop_price' => $shop_price,
                        'shipping_fee' => $shipping_fee,
                        'promote_price' => $promote_price,
                        'member_price' => empty($member_price) ? '' : serialize($member_price),
                        'volume_price' => empty($volume_price) ? '' : serialize($volume_price),
                        'give_integral' => $give_integral,
                        'rank_integral' => $rank_integral,
                        'goods_weight' => $goods_weight,
                        'is_on_sale' => $is_on_sale,
                    ],
                    'admin_id' => session('admin_id')
                ];

                event(new \App\Events\GoodsEditEvent('change_log', $goods_info, $extendParam));
            }

            if (cache()->has('get_brands_list0')) {
                cache()->forget('get_brands_list0');
            }

            if (cache()->has('get_brands_list' . $catgory_id)) {
                cache()->forget('get_brands_list' . $catgory_id);
            }

            if ($is_insert) {
                if ($other_catids) {
                    $other_catids = $this->dscRepository->delStrComma($other_catids);
                    $other_catids = BaseRepository::getExplode($other_catids);
                    GoodsCat::where('goods_id', 0)->whereIn('cat_id', $other_catids)->update(['goods_id' => $goods_id]);
                }

                /**
                 * 创建视频图片目录，并移动视频位置
                 */
                $video_path = storage_public(DATA_DIR . '/uploads/goods/' . $goods_id . "/");
                if (!file_exists($video_path)) {
                    make_dir($video_path);
                }

                $re_path = DATA_DIR . "/uploads/goods/0/";
                $new_goods_video = str_replace($re_path, '', $goods_video);

                if (strpos($new_goods_video, 'data/uploads/goods') !== false) {
                    $copy_goods_video = explode('/', $new_goods_video);
                    $new_goods_video = $copy_goods_video[count($copy_goods_video) - 1];
                }

                if (file_exists(storage_public($goods_video)) && $goods_video) {
                    if (move_upload_file(storage_public($goods_video), $video_path . $new_goods_video)) {
                        $goods_video = DATA_DIR . '/uploads/goods/' . $goods_id . "/" . $new_goods_video;
                        Goods::where('goods_id', $goods_id)->update(['goods_video' => $goods_video]);
                    }
                }

                $act_copy = request()->get('act_copy', 0);

                if ($act_copy == 1) {
                    $images_dir = TimeRepository::getLocalDate('Ym');

                    $goodsCopy = [];
                    if ($original_img) {
                        $copy_original_img = explode('source_img/', $original_img);

                        if (count($copy_original_img) > 1 && is_file(storage_public($original_img))) {
                            if (!file_exists(storage_public(IMAGE_DIR . '/' . $images_dir . '/source_img/'))) {
                                Storage::makeDirectory(IMAGE_DIR . '/' . $images_dir . '/source_img/');
                            }

                            $copyoriginalimg = IMAGE_DIR . '/' . $images_dir . '/source_img/' . $goods_id . "_copy_" . $copy_original_img[1];

                            Storage::copy($original_img, $copyoriginalimg);

                            $goodsCopy['original_img'] = $copyoriginalimg;
                        }
                    }

                    if ($goods_img) {
                        $copy_goods_img = explode('goods_img/', $goods_img);

                        if (count($copy_goods_img) > 1 && is_file(storage_public($goods_img))) {
                            if (!file_exists(storage_public(IMAGE_DIR . '/' . $images_dir . '/goods_img/'))) {
                                Storage::makeDirectory(IMAGE_DIR . '/' . $images_dir . '/goods_img/');
                            }

                            $copygoodsimg = IMAGE_DIR . '/' . $images_dir . '/goods_img/' . $goods_id . "_copy_" . $copy_goods_img[1];

                            Storage::copy($goods_img, $copygoodsimg);

                            $goodsCopy['goods_img'] = $copygoodsimg;
                        }
                    }

                    if ($goods_thumb) {
                        $copy_goods_thumb = explode('thumb_img/', $goods_thumb);

                        if (count($copy_goods_thumb) > 1 && is_file(storage_public($goods_thumb))) {
                            if (!file_exists(storage_public(IMAGE_DIR . '/' . $images_dir . '/thumb_img/'))) {
                                Storage::makeDirectory(IMAGE_DIR . '/' . $images_dir . '/thumb_img/');
                            }

                            $copygoodsthumb = IMAGE_DIR . '/' . $images_dir . '/thumb_img/' . $goods_id . "_copy_" . $copy_goods_thumb[1];

                            Storage::copy($goods_thumb, $copygoodsthumb);

                            $goodsCopy['goods_thumb'] = $copygoodsthumb;
                        }
                    }

                    if ($goodsCopy) {
                        Goods::where('goods_id', $goods_id)->update($goodsCopy);
                    }
                }
            } else {
                /**
                 * 更新购物车
                 * $freight
                 * $tid
                 * $shipping_fee
                 */
                $data = [
                    'goods_name' => $goods_name,
                    'freight' => $freight,
                    'tid' => $tid,
                    'shipping_fee' => $shipping_fee
                ];
                Cart::where('goods_id', $goods_id)->update($data);

                /**
                 * 更新购物车
                 * 应结佣金比例
                 * $commission_rate
                 */
                if ($old_commission_rate <> $commission_rate) {
                    Cart::where('ru_id', session('admin_id'))
                        ->where('goods_id', $goods_id)
                        ->where('is_real', 1)
                        ->where('is_gift', 0)
                        ->update(['commission_rate' => $commission_rate]);
                }

                // 下架商品 设置购物车商品失效且取消勾选
                if ($is_on_sale == 0) {
                    Cart::where('goods_id', $goods_id)->where('extension_code', '<>', 'presale')->update(['is_invalid' => 1, 'is_checked' => 0]);
                }

                // 更新购物车 是否免运费
                Cart::where('goods_id', $goods_id)->where('extension_code', '<>', 'package_buy')->update(['is_shipping' => $is_shipping]);
            }

            //by wang start
            if ($goods_id) {
                //商品扩展信息
                $is_reality = request()->get('is_reality', 0);
                $is_return = request()->get('is_return', 0);
                $is_fast = request()->get('is_fast', 0);
                $extend = GoodsExtend::where('goods_id', $goods_id)->count();
                if ($extend > 0) {
                    //跟新商品扩展信息
                    $data = [
                        'is_reality' => $is_reality,
                        'is_return' => $is_return,
                        'is_fast' => $is_fast
                    ];
                    GoodsExtend::where('goods_id', $goods_id)->update($data);
                } else {
                    //插入商品扩展信息
                    $data = [
                        'goods_id' => $goods_id,
                        'is_reality' => $is_reality,
                        'is_return' => $is_return,
                        'is_fast' => $is_fast
                    ];
                    GoodsExtend::insert($data);
                }

                get_updel_goods_attr($goods_id);
            }
            //by wang end

            //扩展信息 by wu start
            $extend_arr = [];
            $extend_arr['width'] = request()->get('width', ''); //宽度
            $extend_arr['height'] = request()->get('height', ''); //高度
            $extend_arr['depth'] = request()->get('depth', ''); //深度
            $extend_arr['origincountry'] = request()->get('origincountry', ''); //产国
            $extend_arr['originplace'] = request()->get('originplace', ''); //产地
            $extend_arr['assemblycountry'] = request()->get('assemblycountry', ''); //组装国
            $extend_arr['barcodetype'] = request()->get('barcodetype', ''); //条码类型
            $extend_arr['catena'] = request()->get('catena', ''); //产品系列
            $extend_arr['isbasicunit'] = request()->get('isbasicunit', 0); //是否是基本单元
            $extend_arr['packagetype'] = request()->get('packagetype', ''); //包装类型
            $extend_arr['grossweight'] = request()->get('grossweight', ''); //毛重
            $extend_arr['netweight'] = request()->get('netweight', ''); //净重
            $extend_arr['netcontent'] = request()->get('netcontent', ''); //净含量
            $extend_arr['licensenum'] = request()->get('licensenum', ''); //生产许可证
            $extend_arr['healthpermitnum'] = request()->get('healthpermitnum', ''); //卫生许可证
            $this->db->autoExecute($this->dsc->table('goods_extend'), $extend_arr, "UPDATE", "goods_id = '$goods_id'");
            //扩展信息 by wu end

            //库存日志
            if ($not_number) {
                $logs_other = [
                    'goods_id' => $goods_id,
                    'order_id' => 0,
                    'use_storage' => $use_storage,
                    'admin_id' => session('admin_id'),
                    'number' => $number,
                    'model_inventory' => $model_inventory,
                    'model_attr' => $model_attr,
                    'add_time' => $time
                ];

                $logs_other = BaseRepository::recursiveNullVal($logs_other);
                GoodsInventoryLogs::insert($logs_other);
            }

            //消费满N金额减N减额
            get_goods_payfull($is_fullcut, $cfull, $creduce, $c_id, $goods_id, 'goods_consumption');
            //消费满N金额减N减运费
            //get_goods_payfull($sfull, $sreduce, $s_id, $goods_id, 'goods_conshipping', 1);

            /* 记录日志 */
            if ($is_insert) {
                //ecmoban模板堂 --zhuo start 仓库
                if ($model_price == 1) {
                    $warehouse_id = request()->get('warehouse_id', '');

                    if ($warehouse_id) {
                        $warehouse_id = BaseRepository::getExplode($warehouse_id);
                        $data = ['goods_id' => $goods_id];
                        WarehouseGoods::whereIn('w_id', $warehouse_id)->update($data);
                    }
                } elseif ($model_price == 2) {
                    $warehouse_area_id = request()->get('warehouse_area_id', '');

                    if ($warehouse_area_id) {
                        $warehouse_area_id = BaseRepository::getExplode($warehouse_area_id);
                        $data = ['goods_id' => $goods_id];
                        WarehouseAreaGoods::whereIn('a_id', $warehouse_area_id)->update($data);
                    }
                }
                //ecmoban模板堂 --zhuo end 仓库

                admin_log($goods_name, 'add', 'goods');
            } else {
                admin_log($goods_name, 'edit', 'goods');
                //by li start
                $shop_price_format = price_format($shop_price);
                //降价通知
                $res = SaleNotice::where('goods_id', $goods_id)->where('status', '!=', 1)->with([
                    'getUsers'
                ]);
                $notice_list = BaseRepository::getToArrayGet($res);

                foreach ($notice_list as $key => $val) {
                    //查询会员名称 by wu
                    $user_name = $val['get_users']['user_name'] ?? '';

                    if ($user_name) {
                        //短信发送
                        $send_ok = 0;
                        if ($shop_price <= $val['hopeDiscount'] && $val['cellphone'] && config('shop.sms_price_notice') == '1') {

                            //短信接口参数
                            $smsParams = [
                                'user_name' => $user_name,
                                'username' => $user_name,
                                'goodsname' => $this->dscRepository->subStr($goods_name, 20),
                                'goodsprice' => $shop_price,
                                'mobile_phone' => $val['cellphone'],
                                'mobilephone' => $val['cellphone']
                            ];

                            $res = $this->commonRepository->smsSend($val['cellphone'], $smsParams, 'sms_price_notic', false);

                            //记录日志
                            $send_type = 2;
                            if ($res === true) {
                                $data = [
                                    'status' => 1,
                                    'send_type' => 2
                                ];
                                SaleNotice::where('goods_id', $goods_id)
                                    ->where('user_id', $val['user_id'])
                                    ->update($data);
                                $send_ok = 1;
                                notice_log($goods_id, $val['cellphone'], $send_ok, $send_type);
                            } else {
                                $data = [
                                    'status' => 3,
                                    'send_type' => 2
                                ];
                                SaleNotice::where('goods_id', $goods_id)
                                    ->where('user_id', $val['user_id'])
                                    ->update($data);

                                $send_ok = 0;
                                notice_log($goods_id, $val['cellphone'], $send_ok, $send_type);
                            }
                        }

                        //当短信发送失败，邮件发送
                        if ($send_ok == 0 && $shop_price <= $val['hopeDiscount'] && $val['email']) {
                            /* 设置留言回复模板所需要的内容信息 */
                            $template = get_mail_template('sale_notice');

                            $this->smarty->assign('user_name', $user_name);
                            $this->smarty->assign('goods_name', $goods_name);
                            $this->smarty->assign('goods_link', $this->dsc->url() . "goods.php?id=" . $goods_id);
                            $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));

                            $content = $this->smarty->fetch('str:' . $template['template_content']);

                            $send_type = 1;
                            /* 发送邮件 */
                            if (CommonRepository::sendEmail($user_name, $val['email'], $template['template_subject'], $content, $template['is_html'])) {
                                $data = [
                                    'status' => 1,
                                    'send_type' => 1
                                ];
                                SaleNotice::where('goods_id', $goods_id)
                                    ->where('user_id', $val['user_id'])
                                    ->update($data);
                                $send_ok = 1;
                                notice_log($goods_id, $val['email'], $send_ok, $send_type);
                            } else {
                                $data = [
                                    'status' => 3,
                                    'send_type' => 1
                                ];
                                SaleNotice::where('goods_id', $goods_id)
                                    ->where('user_id', $val['user_id'])
                                    ->update($data);

                                $send_ok = 0;
                                notice_log($goods_id, $val['email'], $send_ok, $send_type);
                            }
                        }
                    }
                }
                //by li end
            }
            $attr_id_list = request()->get('attr_id_list', []);
            $attr_value_list = request()->get('attr_value_list', []);
            $attr_price_list = request()->get('attr_price_list', []);
            $attr_sort_list = request()->get('attr_sort_list', []);
            /* 处理属性 */
            if ($attr_id_list || (empty($attr_id_list) && empty($attr_value_list))) {
                // 取得原有的属性值
                $goods_attr_list = [];

                $res = Attribute::select('attr_id', 'attr_index')->where('cat_id', $goods_type);
                $attr_res = BaseRepository::getToArrayGet($res);
                $attr_list = [];
                foreach ($attr_res as $row) {
                    $attr_list[$row['attr_id']] = $row['attr_index'];
                }

                $res = GoodsAttr::where('goods_id', $goods_id);
                $res = $res->with(['getGoodsAttribute' => function ($query) {
                    $query->select('attr_id', 'attr_type');
                }]);
                $res = BaseRepository::getToArrayGet($res);


                foreach ($res as $key => $row) {
                    $row['attr_type'] = 0;
                    if (isset($row['get_goods_attribute']) && !empty($row['get_goods_attribute'])) {
                        $res[$key]['attr_type'] = $row['get_goods_attribute']['attr_type'];
                    }
                    $goods_attr_list[$row['attr_id']][$row['attr_value']] = ['sign' => 'delete', 'goods_attr_id' => $row['goods_attr_id']];
                }

                // 循环现有的，根据原有的做相应处理
                if ($attr_id_list) {
                    foreach ($attr_id_list as $key => $attr_id) {
                        $attr_value = $attr_value_list[$key];
                        $attr_price = $attr_price_list[$key];
                        $attr_sort = isset($attr_sort_list[$key]) ? $attr_sort_list[$key] : ''; //ecmoban模板堂 --zhuo
                        if (!empty($attr_value)) {
                            if (isset($goods_attr_list[$attr_id][$attr_value])) {
                                // 如果原来有，标记为更新
                                $goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                                $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                                $goods_attr_list[$attr_id][$attr_value]['attr_sort'] = $attr_sort;
                            } else {
                                // 如果原来没有，标记为新增
                                $goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                                $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                                $goods_attr_list[$attr_id][$attr_value]['attr_sort'] = $attr_sort;
                            }
                        }
                    }
                }

                $gallery_attr_id = request()->get('gallery_attr_id', []);
                $gallery_attr_value_arr = request()->get('gallery_attr_value', []);
                $gallery_attr_price_arr = request()->get('gallery_attr_price', []);
                $gallery_attr_sort_arr = request()->get('gallery_attr_sort', []);
                // 循环现有的，根据原有的做相应处理
                if ($gallery_attr_id) {
                    foreach ($gallery_attr_id as $key => $attr_id) {
                        $gallery_attr_value = $gallery_attr_value_arr[$key] ?? '';
                        $gallery_attr_price = $gallery_attr_price_arr[$key] ?? 0;
                        $gallery_attr_sort = $gallery_attr_sort_arr[$key] ?? 0;
                        if (!empty($gallery_attr_value)) {
                            if (isset($goods_attr_list[$attr_id][$gallery_attr_value])) {
                                // 如果原来有，标记为更新
                                $goods_attr_list[$attr_id][$gallery_attr_value]['sign'] = 'update';
                                $goods_attr_list[$attr_id][$gallery_attr_value]['attr_price'] = $gallery_attr_price;
                                $goods_attr_list[$attr_id][$gallery_attr_value]['attr_sort'] = $gallery_attr_sort;
                            } else {
                                // 如果原来没有，标记为新增
                                $goods_attr_list[$attr_id][$gallery_attr_value]['sign'] = 'insert';
                                $goods_attr_list[$attr_id][$gallery_attr_value]['attr_price'] = $gallery_attr_price;
                                $goods_attr_list[$attr_id][$gallery_attr_value]['attr_sort'] = $gallery_attr_sort;
                            }
                        }
                    }
                }

                /* 插入、更新、删除数据 */
                foreach ($goods_attr_list as $attr_id => $attr_value_list) {
                    foreach ($attr_value_list as $attr_value => $info) {
                        $info['attr_price'] = $info['attr_price'] ?? 0;
                        $info['attr_sort'] = $info['attr_sort'] ?? '';
                        if ($info['sign'] == 'insert') { //ecmoban模板堂 --zhuo attr_sort
                            $data = [
                                'attr_id' => $attr_id,
                                'goods_id' => $goods_id,
                                'attr_value' => $attr_value,
                                'attr_price' => $info['attr_price'],
                                'attr_sort' => $info['attr_sort']
                            ];
                            GoodsAttr::insert($data);
                        } elseif ($info['sign'] == 'update') { //ecmoban模板堂 --zhuo attr_sort
                            $data = [
                                'attr_price' => $info['attr_price'],
                                'attr_sort' => $info['attr_sort']
                            ];
                            GoodsAttr::where('goods_attr_id', $info['goods_attr_id'])->update($data);
                        } else {
                            if ($model_attr == 1) {
                                $prod = ProductsWarehouse::where('goods_id', $goods_id);
                            } elseif ($model_attr == 2) {
                                $prod = ProductsArea::where('goods_id', $goods_id);
                            } else {
                                $prod = Products::where('goods_id', $goods_id);
                            }

                            $prod = $prod->whereRaw("FIND_IN_SET('" . $info['goods_attr_id'] . "', REPLACE(goods_attr, '|', ','))");

                            $prod->delete();

                            GoodsAttr::where('goods_attr_id', $info['goods_attr_id'])->delete();
                        }
                    }
                }
            }

            /* 处理会员价格 */
            $user_rank = request()->get('user_rank', '');
            $user_price = request()->get('user_price', '');
            if ($user_rank && $user_price) {

                $seller_id = Goods::where('goods_id', $goods_id)->value('user_id');
                $seller_id = $seller_id ? $seller_id : 0;

                if ($seller_id > 0) {
                    $this->goodsManageService->handle_member_price($goods_id, $user_rank, $user_price, $is_discount);
                } else {
                    $this->goodsManageService->handle_member_price($goods_id, $user_rank, $user_price);
                }
            }

            /* 处理优惠价格 */
            $volume_number = request()->get('volume_number', '');
            $volume_price = request()->get('volume_price', '');
            if ($volume_number && $volume_price) {
                handle_volume_price($goods_id, $is_volume, $volume_number, $volume_price, request()->get('id', 0));
            }

            /* 处理扩展分类 */
            $other_cat = request()->get('other_cat', '');
            if ($other_cat) {
                handle_other_cat($goods_id, array_unique($other_cat));
            }

            if ($is_insert) {
                /* 处理关联商品 */
                handle_link_goods($goods_id);

                /* 处理组合商品 */
                handle_group_goods($goods_id);

                /* 处理关联文章 */
                handle_goods_article($goods_id);

                /* 处理关联地区 add by qin */
                handle_goods_area($goods_id);

                /* 处理相册图片 by wu */

                $thumb_img_id = session()->has('thumb_img_id' . session('admin_id')) ? session('thumb_img_id' . session('admin_id')) : 0;//处理添加商品时相册图片串图问题   by kong

                if (isset($thumb_img_id) && $thumb_img_id != '') {
                    $thumb_img_id = BaseRepository::getExplode($thumb_img_id);
                    $data = ['goods_id' => $goods_id];
                    GoodsGallery::where('goods_id', 0)->whereIn('img_id', $thumb_img_id)->update($data);
                }

                session()->forget('thumb_img_id' . session('admin_id'));

                // 处理活动标签
                $label_use_id = session()->has('label_use_id' . session('admin_id')) ? session('label_use_id' . session('admin_id')) : [];

                if (!empty($label_use_id)) {
                    $label_use_id = BaseRepository::getExplode($label_use_id);
                    $data = ['goods_id' => $goods_id];
                    GoodsUseLabel::query()->where('goods_id', 0)->whereIn('id', $label_use_id)->update($data);
                }

                session()->forget('label_use_id' . session('admin_id'));

                // 处理服务标签
                $services_label_use_id = session()->has('services_label_use_id' . session('admin_id')) ? session('services_label_use_id' . session('admin_id')) : [];

                if (!empty($services_label_use_id)) {
                    $services_label_use_id = BaseRepository::getExplode($services_label_use_id);
                    $data = ['goods_id' => $goods_id];
                    GoodsUseLabel::query()->where('goods_id', 0)->whereIn('id', $services_label_use_id)->update($data);
                }

                session()->forget('services_label_use_id' . session('admin_id'));
            }

            /* 如果有图片，把商品图片加入图片相册 */
            if (request()->get('goods_img_url', '') && $is_img_url == 1) {
                /* 重新格式化图片名称 */
                $original_img = $this->goodsManageService->reformatImageName('goods', $goods_id, $original_img, 'source');
                $goods_img = $this->goodsManageService->reformatImageName('goods', $goods_id, $goods_img, 'goods');
                $goods_thumb = $this->goodsManageService->reformatImageName('goods_thumb', $goods_id, $goods_thumb, 'thumb');

                // 处理商品图片
                $data = [
                    'goods_thumb' => $goods_thumb,
                    'goods_img' => $goods_img,
                    'original_img' => $original_img
                ];
                Goods::where('goods_id', $goods_id)->update($data);
                if (isset($img)) {
                    // 重新格式化图片名称
                    if (empty($is_url_goods_img)) {
                        $img = $this->goodsManageService->reformatImageName('gallery', $goods_id, $img, 'source');
                        $gallery_img = $this->goodsManageService->reformatImageName('gallery', $goods_id, $gallery_img, 'goods');
                    } else {
                        $img = $original_img;
                        $gallery_img = $goods_img;
                    }

                    $gallery_thumb = $this->goodsManageService->reformatImageName('gallery_thumb', $goods_id, $gallery_thumb, 'thumb');

                    $data = [
                        'goods_id' => $goods_id,
                        'img_url' => $gallery_img,
                        'thumb_url' => $gallery_thumb,
                        'img_original' => $img
                    ];
                    GoodsGallery::insert($data);
                }

                $this->dscRepository->getOssAddFile([$goods_img, $goods_thumb, $original_img, $gallery_img, $gallery_thumb, $img]);
            } else {
                $this->dscRepository->getOssAddFile([$goods_img, $goods_thumb, $original_img]);
            }

            /** ************* 处理货品数据 start ************** */
            $products_changelog_res = ProductsChangelog::select(
                'goods_attr',
                'product_sn',
                'bar_code',
                'product_number',
                'product_price',
                'product_market_price',
                'product_promote_price',
                'product_warn_number',
                'warehouse_id',
                'area_id',
                'admin_id',
                'sku_weight'
            );

            $goods_model = request()->get('goods_model', 0);
            $warehouse = request()->get('warehouse', 0);
            $region = request()->get('region', 0);
            $city_id = request()->get('city_region', 0);
            $arrt_page_count = request()->get('arrt_page_count', 1); //属性分页

            $region_id = 0;
            $res = Products::whereRaw(1);

            if ($goods_model == 1) {
                //数据表
                $res = ProductsWarehouse::whereRaw(1);
                //地区id
                $region_id = $warehouse;
                //补充筛选

                $products_changelog_res = $products_changelog_res->where('warehouse_id', $warehouse);
            } elseif ($goods_model == 2) {
                $res = ProductsArea::whereRaw(1);
                $region_id = $region;

                $products_changelog_res = $products_changelog_res->where('area_id', $region);

                if (config('shop.area_pricetype')) {
                    $products_changelog_res = $products_changelog_res->where('city_id', $city_id);
                }
            }

            if ($is_insert) {
                $res->where('goods_id', 0)->where('admin_id', $admin_id)->update(['goods_id' => $goods_id]);
            }

            $product['goods_id'] = $goods_id;
            $product['attr'] = request()->get('attr', []);
            $product['product_id'] = request()->get('product_id', []);
            $product['product_sn'] = request()->get('product_sn', []);
            $product['product_number'] = request()->get('product_number', []);
            $product['product_price'] = request()->get('product_price', []); //货品价格
            $product['product_market_price'] = request()->get('product_market_price', []); //货品市场价格
            $product['product_promote_price'] = request()->get('product_promote_price', []); //货品促销价格
            $product['product_warn_number'] = request()->get('product_warn_number', []); //警告库存
            $product['sku_weight'] = request()->get('sku_weight', []); //货品重量
            $product['bar_code'] = request()->get('product_bar_code', []); //货品条形码
            $changelog_product_id = request()->get('changelog_product_id', []); //货品零时表product_id

            /* 是否存在商品id */
            if (empty($product['goods_id'])) {
                return sys_msg(__('admin::common.sys.wrong') . __('admin::goods.cannot_found_goods'), 1, [], false);
            }

            /* 取出商品信息 */
            $goods_res = Goods::select('goods_sn', 'goods_name', 'goods_type', 'shop_price', 'model_inventory', 'model_attr', 'goods_video', 'user_id');
            $goods_res = $goods_res->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($goods_res);
            /* 货号 */
            if (empty($product['product_sn'])) {
                $product['product_sn'] = [];
            }

            foreach ($product['product_sn'] as $key => $value) {
                // 初始化
                $res = Products::whereRaw(1);
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::whereRaw(1);
                }

                //过滤
                $product['product_number'][$key] = trim($product['product_number'][$key]); //库存
                $product['product_id'][$key] = isset($product['product_id'][$key]) && !empty($product['product_id'][$key]) ? intval($product['product_id'][$key]) : 0; //货品ID

                $logs_other = [
                    'goods_id' => $goods_id,
                    'order_id' => 0,
                    'admin_id' => session('admin_id'),
                    'model_inventory' => $goods['model_inventory'],
                    'model_attr' => $goods['model_attr'],
                    'add_time' => $time
                ];

                if ($goods_model == 1) {
                    $logs_other['warehouse_id'] = $warehouse;
                    $logs_other['area_id'] = 0;
                    $logs_other['city_id'] = 0;
                } elseif ($goods_model == 2) {
                    $logs_other['warehouse_id'] = $warehouse;
                    $logs_other['area_id'] = $region;
                    $logs_other['city_id'] = $city_id;
                } else {
                    $logs_other['warehouse_id'] = 0;
                    $logs_other['area_id'] = 0;
                    $logs_other['city_id'] = 0;
                }

                if ($product['product_id'][$key]) {

                    /* 货品库存 */
                    $goods_product = get_product_info($product['product_id'][$key], 'product_number', $goods_model);

                    $add_number = true;
                    if ($goods_product['product_number'] != $product['product_number'][$key]) {
                        if ($goods_product['product_number'] > $product['product_number'][$key]) {
                            $number = $goods_product['product_number'] - $product['product_number'][$key];

                            if ($number == 0) {
                                $add_number = false;
                            }

                            $number = "- " . $number;
                            $logs_other['use_storage'] = 10;
                        } else {
                            $number = $product['product_number'][$key] - $goods_product['product_number'];

                            if ($number == 0) {
                                $add_number = false;
                            }

                            $number = "+ " . $number;
                            $logs_other['use_storage'] = 11;
                        }

                        if ($add_number == true) {
                            $logs_other['number'] = $number;
                            $logs_other['product_id'] = $product['product_id'][$key];
                            GoodsInventoryLogs::insert($logs_other);
                        }
                    }

                    if (empty($value)) {
                        $product_sn = $goods['goods_sn'] . "g_p" . $product['product_id'][$key];
                    } else {
                        $product_sn = $value;
                    }

                    $data = [
                        'product_number' => $product['product_number'][$key],
                        'product_market_price' => $product['product_market_price'][$key],
                        'product_price' => $product['product_price'][$key],
                        'product_promote_price' => $product['product_promote_price'][$key],
                        'product_warn_number' => $product['product_warn_number'][$key],
                        'sku_weight' => $product['sku_weight'][$key],
                        'product_sn' => $product_sn
                    ];
                    $res->where('product_id', $product['product_id'][$key])->update($data);
                } else {
                    $number = 0;
                    //获取规格在商品属性表中的id
                    foreach ($product['attr'] as $attr_key => $attr_value) {
                        /* 检测：如果当前所添加的货品规格存在空值或0 */
                        if (empty($attr_value[$key])) {
                            continue 2;
                        }

                        $is_spec_list[$attr_key] = 'true';

                        $value_price_list[$attr_key] = $attr_value[$key] . chr(9) . ''; //$key，当前

                        $id_list[$attr_key] = $attr_key;
                    }
                    $goods_attr_id = handle_goods_attr($product['goods_id'], $id_list, $is_spec_list, $value_price_list);

                    /* 是否为重复规格的货品 */
                    $goods_attr = $this->goodsAttrService->sortGoodsAttrIdArray($goods_attr_id);

                    if (!empty($goods_attr['sort'])) {
                        $goods_attr = implode('|', $goods_attr['sort']);
                    } else {
                        $goods_attr = "";
                    }

                    if (check_goods_attr_exist($goods_attr, $product['goods_id'], 0, $region_id)) { //by wu
                        continue;
                    }

                    /* 插入货品表 */
                    $product_other = [
                        'goods_id' => $product['goods_id'],
                        'goods_attr' => $goods_attr,
                        'product_sn' => $value,
                        'product_number' => $product['product_number'][$key] ?? 0,
                        'product_price' => $product['product_price'][$key] ?? 0,
                        'product_market_price' => $product['product_market_price'][$key] ?? 0,
                        'product_promote_price' => $product['product_promote_price'][$key] ?? 0,
                        'product_warn_number' => $product['product_warn_number'][$key] ?? 0,
                        'sku_weight' => $product['sku_weight'][$key] ?? 0,
                        'bar_code' => $product['bar_code'][$key] ?? ''
                    ];

                    if ($goods_model == 1) {
                        $product_other['warehouse_id'] = $warehouse;
                    } elseif ($goods_model == 2) {
                        $product_other['area_id'] = $region;
                        $product_other['city_id'] = $city_id;
                    }

                    $product_id = $res->insertGetId($product_other);

                    if (!$product_id) {
                        continue;
                    } else {
                        //货品号为空 自动补货品号
                        if (empty($value)) {
                            $data = ['product_sn' => $goods['goods_sn'] . "g_p" . $product_id];
                            $res->where('product_id', $product_id)->update($data);
                        }

                        //库存日志
                        if ($product['product_number'][$key] != 0) {
                            $number = "+ " . $product['product_number'][$key];
                            $logs_other['use_storage'] = 9;
                            $logs_other['product_id'] = $product_id;
                            $logs_other['number'] = $number;
                            GoodsInventoryLogs::insert($logs_other);
                        }
                    }
                }
            }
            //插入货品零时表数据
            $products_changelog_res = $products_changelog_res->where('admin_id', $admin_id);
            if ($is_insert) {
                $products_changelog_res = $products_changelog_res->where('goods_id', 0);
            } else {
                $products_changelog_res = $products_changelog_res->where('goods_id', $goods_id);
            }

            if (!empty($changelog_product_id)) {
                $changelog_product_id = BaseRepository::getExplode($changelog_product_id);
                $products_changelog_res = $products_changelog_res->whereNotIn('product_id', $changelog_product_id);
            }

            $products_changelog = BaseRepository::getToArrayGet($products_changelog_res);

            if (!empty($products_changelog)) {
                foreach ($products_changelog as $k => $v) {
                    if (check_goods_attr_exist($v['goods_attr'], $product['goods_id'], 0, $region_id)) { //检测货品是否存在
                        continue;
                    }
                    $number = 0;
                    $logs_other = [
                        'goods_id' => $goods_id,
                        'order_id' => 0,
                        'admin_id' => $admin_id,
                        'model_inventory' => $goods['model_inventory'],
                        'model_attr' => $goods['model_attr'],
                        'add_time' => $time
                    ];

                    if ($goods_model == 1) {
                        $logs_other['warehouse_id'] = $warehouse;
                        $logs_other['area_id'] = 0;
                        $logs_other['city_id'] = 0;
                        $data['warehouse_id'] = $warehouse;
                    } elseif ($goods_model == 2) {
                        $logs_other['warehouse_id'] = $warehouse;
                        $logs_other['area_id'] = $region;
                        $logs_other['city_id'] = $city_id;
                        $data['area_id'] = $region;
                        $data['city_id'] = $city_id;
                    } else {
                        $logs_other['warehouse_id'] = 0;
                        $logs_other['area_id'] = 0;
                        $logs_other['city_id'] = 0;
                    }
                    /* 插入货品表 */
                    $data = [
                        'goods_id' => $product['goods_id'],
                        'goods_attr' => $v['goods_attr'],
                        'product_sn' => $v['product_sn'],
                        'product_number' => $v['product_number'],
                        'product_price' => $v['product_price'],
                        'product_market_price' => $v['product_market_price'],
                        'product_promote_price' => $v['product_promote_price'],
                        'product_warn_number' => $v['product_warn_number'],
                        'sku_weight' => $v['sku_weight'],
                        'admin_id' => $v['admin_id'],
                        'bar_code' => $v['bar_code']
                    ];

                    $product_id = $res->insertGetId($data);
                    if (!$product_id) {
                        continue;
                    } else {

                        //货品号为空 自动补货品号
                        if (empty($v['product_sn'])) {
                            $data = ['product_sn' => $goods['goods_sn'] . "g_p" . $product_id];
                            $res->where('product_id', $product_id)->update($data);
                        }

                        //库存日志
                        if ($v['product_number'] != 0) {
                            $number = "+ " . $v['product_number'];
                            $logs_other['use_storage'] = 9;
                            $logs_other['product_id'] = $product_id;
                            $logs_other['number'] = $number;
                            GoodsInventoryLogs::insert($logs_other);
                        }
                    }
                }
            }

            /**
             * 删除商品视频session记录
             */
            if (!empty($goods_video) && $goods['goods_video'] == $goods_video) {
                session()->forget("goods_video_" . $goods_id . "_" . $admin_id);

                $list = session("goods_video_id_list", []);
                $list = BaseRepository::getArrayUnique($list);
                $list = BaseRepository::getArrayExcept($list, [$goods_id]);
                session("goods_video_id_list", $list);
            }

            //清楚商品零时货品表数据
            $pcl_del_res = ProductsChangelog::where('goods_id', $goods_id);
            if (empty($goods_id)) {
                $pcl_del_res = $pcl_del_res->where('admin_id', $admin_id);
            }
            $pcl_del_res->delete();

            /*************** 处理货品数据 end ***************/

            /* 同步前台商品详情价格与商品列表价格一致 start */
            $goods = get_admin_goods_info($goods_id);
            if (config('shop.add_shop_price') == 0 && $goods['model_attr'] == 0) {
                load_helper('goods');

                $properties = $this->goodsAttrService->getGoodsProperties($goods_id, 0, 0, 0, '', 0);  // 获得商品的规格和属性
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

                    $time = TimeRepository::getGmTime();
                    if (!empty($goodsAttrId)) {
                        $products = $this->goodsWarehouseService->getWarehouseAttrNumber($goods_id, $goodsAttrId, 0, 0, 0, $goods['model_attr']);

                        if ($products) {
                            $products['product_market_price'] = isset($products['product_market_price']) ? $products['product_market_price'] : 0;
                            $products['product_price'] = isset($products['product_price']) ? $products['product_price'] : 0;
                            $products['product_promote_price'] = isset($products['product_promote_price']) ? $products['product_promote_price'] : 0;

                            $promote_price = 0;
                            if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date']) {
                                $promote_price = $goods['promote_price'];
                            }

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

            if ($is_insert) {
                get_del_update_goods_null($goods_id, 1);
            } else {
                if ($goods_type == 0 && $goods_id > 0) {
                    Products::where('goods_id', $goods_id)->delete();

                    ProductsArea::where('goods_id', $goods_id)->delete();

                    ProductsWarehouse::where('goods_id', $goods_id)->delete();

                    GoodsAttr::where('goods_id', $goods_id)->delete();
                }

                // 当属性超过1个 去除非组合属性的货品
                if (isset($goods_attr_id) && count($goods_attr_id) > 1) {
                    $count = count($goods_attr_id) - 1;
                    $not_like = '%';
                    for ($i = 0; $i < $count; $i++) {
                        $not_like .= '|%';
                    }
                    if ($count > 0) {
                        Products::where('goods_id', $goods_id)->where('goods_attr', 'not like', $not_like)->delete();

                        ProductsArea::where('goods_id', $goods_id)->where('goods_attr', 'not like', $not_like)->delete();

                        ProductsWarehouse::where('goods_id', $goods_id)->where('goods_attr', 'not like', $not_like)->delete();
                    }
                }
            }

            if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status')) {
                // 视频号商品资质
                $sync_media = request()->get('sync_media', 0);
                $media_goods_category = request()->get('media_goods_category', 0);
                $media_goods_brand = request()->get('media_goods_brand', 0);
                if ($sync_media == 1) {
                    // 小程序配置信息
                    $wxapp = app(\App\Modules\Wxapp\Services\WxappConfigService::class)->get_config();

                    if (empty($wxapp) || (isset($wxapp['status']) && $wxapp['status'] == 0)) {
                        return sys_msg(lang('wxapp::admin/wxapp.open_wxapp'), 1, [['href' => route('admin/wxapp/index'), 'text' => lang('wxapp::admin/wxapp.open_wxapp')]]);
                    }

                    // 小程序实例
                    $config = [
                        'appid' => $wxapp['wx_appid'] ?? '',
                        'secret' => $wxapp['wx_appsecret'] ?? '',
                    ];

                    $data = app(\App\Modules\WxMedia\Services\WxappShopGoodsService::class)->submitGoodsAudit($goods_id, $config, $media_goods_category, $media_goods_brand);

                    if ($data['error'] > 0) {
                        return sys_msg($data['msg'], 1, [['href' => 'goods.php?act=list', 'text' => __('admin::goods.back_goods_list')]]);
                    } else {
                        $content = [
                            'goods_id' => $goods_id,
                            'wxapp_cat_id' => $media_goods_category,
                            'wxapp_brand_id' => $media_goods_brand,
                            'review_status' => 2,
                            'review_content' => ''
                        ];
                        \App\Modules\WxMedia\Models\WxappGoodsExtension::query()->updateOrCreate(['goods_id' => $goods_id], $content);
                    }
                } else {
                    \App\Modules\WxMedia\Models\WxappGoodsExtension::query()->where('goods_id', $goods_id)->delete();
                    \App\Modules\WxMedia\Models\WxappGoodsQualifications::query()->where('goods_id', $goods_id)->delete();

                    if (file_exists(WXAPP_MEDIA_CONCISE)) {
                        \App\Modules\WxMedia\Models\WxappMediaGoodsQrcod::query()->where('goods_id', $goods_id)->delete();
                    }
                }
            }

            /* 清空缓存 */
            clear_cache_files();

            /* 提示页面 */
            $link = [];

            if ($code == 'virtual_card') {
                $link[1] = ['href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id, 'text' => __('admin::goods.add_replenish')];
            }
            if ($is_insert) {
                $link[2] = $this->goodsManageService->addLink($code);
            }
            $link[3] = $this->goodsManageService->listLink($is_insert, $code);

            for ($i = 0; $i < count($link); $i++) {
                $key_array[] = $i;
            }
            krsort($link);
            $link = array_combine($key_array, $link);

            return sys_msg($is_insert ? __('admin::goods.add_goods_ok') : __('admin::goods.edit_goods_ok'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 批量操作
        /*------------------------------------------------------ */

        elseif ($act == 'batch') {
            $code = request()->get('extension_code', '');

            /* 取得要操作的商品编号 */
            $checkboxes = request()->get('checkboxes', '');
            $goods_id = !empty($checkboxes) ? join(',', $checkboxes) : 0;
            $type = request()->get('type', '');
            if ($type) {
                /* 放入回收站 */
                if ($type == 'trash') {
                    /* 检查权限 */
                    admin_priv('remove_back');

                    $is_promotion = is_promotion($goods_id);

                    if ($is_promotion) {
                        foreach ($is_promotion as $res) {
                            $res[$res['type']]['goods_sn'] = isset($res[$res['type']]['goods_sn']) ? $this->dscRepository->delStrComma($res[$res['type']]['goods_sn']) : '';

                            switch ($res['type']) {
                                case 'snatch': //夺宝奇兵
                                    return sys_msg(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_snatch'), 0);
                                    break;

                                case 'group_buy': //团购
                                    return sys_msg(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_group_buy'), 0);
                                    break;

                                case 'auction': //拍卖
                                    return sys_msg(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_auction'), 0);
                                    break;

                                case 'package': //礼包
                                    return sys_msg(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_package'), 0);
                                    break;
                            }
                        }
                    }

                    $seckill = is_seckill($goods_id);
                    if ($seckill) {
                        return sys_msg(__('admin::goods.del_goods_sn') . $seckill . __('admin::goods.del_seckill'), 0); // 秒杀
                    }

                    $presale = is_presale($goods_id);
                    if ($presale) {
                        return sys_msg(__('admin::goods.del_goods_sn') . $presale . __('admin::goods.del_presale'), 0); // 预售
                    }

                    $goods_arr = BaseRepository::getExplode($goods_id);

                    foreach ($goods_arr as $k => $gid) {
                        $order_count = $this->goodsManageService->getOrderGoodsCout($gid);
                        if ($order_count > 0) {
                            return sys_msg(__('admin::goods.del_goods_fail'));
                        }
                    }

                    update_goods($goods_id, 'is_delete', '1');

                    /* 记录日志 */
                    admin_log('', 'batch_trash', 'goods');
                } /* 上架 */
                elseif ($type == 'on_sale') {
                    /* 检查权限 */
                    admin_priv('goods_manage');

                    $is_presale = is_presale($goods_id);
                    if (!empty($is_presale)) {
                        return sys_msg($is_presale . __('admin::goods.del_presale'));
                    }

                    update_goods($goods_id, 'is_on_sale', '1');
                } /* 下架 */
                elseif ($type == 'not_on_sale') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_on_sale', '0');
                } /* 设为精品 */
                elseif ($type == 'best') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_best', '1');
                } /* 取消精品 */
                elseif ($type == 'not_best') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_best', '0');
                } /* 设为新品 */
                elseif ($type == 'new') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_new', '1');
                } /* 取消新品 */
                elseif ($type == 'not_new') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_new', '0');
                } /* 设为热销 */
                elseif ($type == 'hot') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_hot', '1');
                } /* 取消热销 */
                elseif ($type == 'not_hot') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_hot', '0');
                } /* 显示 */
                elseif ($type == 'show') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_show', '1');
                } /* 取消热销 */
                elseif ($type == 'not_show') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'is_show', '0');
                } /* 转移到分类 */
                elseif ($type == 'move_to') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'cat_id', request()->get('target_cat'));
                } /* 转移到供货商 */
                elseif ($type == 'suppliers_move_to') {
                    /* 检查权限 */
                    admin_priv('goods_manage');
                    update_goods($goods_id, 'suppliers_id', request()->get('suppliers_id'));
                } /* 还原 */
                elseif ($type == 'restore') {
                    /* 检查权限 */
                    admin_priv('remove_back');

                    update_goods($goods_id, 'is_delete', '0');

                    /* 记录日志 */
                    admin_log('', 'batch_restore', 'goods');
                } /* 删除 */
                elseif ($type == 'drop') {
                    /* 检查权限 */
                    admin_priv('remove_back');

                    $order_count = $this->goodsManageService->getOrderGoodsCout($goods_id);

                    if ($order_count > 0) {
                        return sys_msg(__('admin::goods.del_goods_fail'));
                    }

                    $this->goodsManageService->deleteGoods($goods_id);

                    /* 记录日志 */
                    admin_log('', 'batch_remove', 'goods');
                } /* 审核商品 ecmoban模板堂 --zhuo */
                elseif ($type == 'review_to') {
                    /* 检查权限 */
                    admin_priv('review_status');

                    $review_status = request()->get('review_status', 0);
                    $review_content = request()->get('review_content', '');

                    update_goods($goods_id, 'review_status', $review_status, $review_content);

                    /* 记录日志 */
                    admin_log('', 'review_to', 'goods');
                } /* 运费模板 */
                elseif ($type == 'goods_transport') {
                    /* 检查权限 */
                    admin_priv('goods_manage');

                    $data = [];
                    $data['freight'] = 2;
                    $data['tid'] = request()->get('tid', 0);
                    $goods_id = BaseRepository::getExplode($goods_id);
                    Goods::whereIn('goods_id', $goods_id)->where('user_id', $adminru['ru_id'])->update($data);

                    /**
                     * 更新购物车
                     * $freight
                     * $tid
                     * $shipping_fee
                     */
                    $data = [
                        'freight' => $data['freight'],
                        'tid' => $data['tid']
                    ];
                    Cart::whereIn('goods_id', $goods_id)->where('ru_id', $adminru['ru_id'])->update($data);

                    /* 记录日志 */
                    admin_log('', 'batch_edit', 'goods_transport');
                } //批量设置退换货
                elseif ($type == 'return_type') {
                    //修改退换货标识
                    $goods_id = BaseRepository::getExplode($goods_id);
                    $data = ['goods_cause' => '0,1,2,3'];
                    Goods::whereIn('goods_id', $goods_id)->update($data);
                    //查找商品拓展
                    if (!empty($goods_id)) {
                        foreach ($goods_id as $v) {
                            $goods_extend = GoodsExtend::where('goods_id', $v)->count();
                            if ($goods_extend > 0) {
                                $data = ['is_return' => 1];
                                GoodsExtend::where('goods_id', $v)->update($data);
                            } else {
                                $data = [
                                    'goods_id' => $v,
                                    'is_return' => 1
                                ];
                                GoodsExtend::insert($data);
                            }
                        }
                    }
                }
            }

            /* 清除缓存 */
            clear_cache_files();

            if (in_array($type, ['drop', 'restore'])) {
                $link[] = ['href' => 'goods.php?act=trash', 'text' => __('admin::common.11_goods_trash')];
            } else {
                $link[] = $this->goodsManageService->listLink(false, $code);
            }
            return sys_msg(__('admin::goods.batch_handle_ok'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 显示图片
        /*------------------------------------------------------ */

        elseif ($act == 'show_image') {
            $img_url = request()->get('img_url', '');
            if (strpos($img_url, 'http://') !== 0) {
                $img_url = '../' . $img_url;
            }

            $this->smarty->assign('img_url', $img_url);
            return $this->smarty->display('goods_show_image.dwt');
        }

        /*------------------------------------------------------ */
        //-- 修改商品名称
        /*------------------------------------------------------ */
        elseif ($act == 'edit_goods_name') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $goods_name = json_str_iconv(request()->get('val', ''));
            $goods_name = mb_substr($goods_name, 0, config('shop.goods_name_length')); // 商品名称截取

            $brand_id = Goods::where('goods_id', $goods_id)->value('brand_id');
            $brand_id = $brand_id ? $brand_id : 0;

            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);
            $brand_name = $brandList[$brand_id]['brand_name'] ?? '';

            if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                $goods_name = StrRepository::replaceFirst($goods_name, $brand_name);
                $goods_name = StrRepository::replaceFirst($goods_name, config('app.replace_symbol'));
                $goods_name = $brand_name . config('app.goods_symbol') . $goods_name;
            }

            $res = Goods::where('goods_id', $goods_id)
                ->update([
                    'goods_name' => $goods_name,
                    'last_update' => TimeRepository::getGmTime(),
                ]);

            $data = [
                'goods_name' => $goods_name
            ];

            Cart::where('goods_id', $goods_id)->update($data);

            if ($res) {
                /* 更新预售商品名称 */
                PresaleActivity::where('goods_id', $goods_id)->update([
                    'goods_name' => $goods_name
                ]);
            }

            return make_json_result(stripslashes($goods_name));
        }

        /*------------------------------------------------------ */
        //-- 修改商品货号
        /*------------------------------------------------------ */
        elseif ($act == 'edit_goods_sn') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $goods_sn = json_str_iconv(trim(request()->get('val', '')));

            $goods_info = get_admin_goods_info($goods_id);

            /* 检查是否重复 */
            $is_only = Goods::where('goods_sn', $goods_sn)
                ->where('goods_id', '<>', $goods_id)
                ->where('user_id', $goods_info['user_id'])
                ->count();
            if ($is_only > 0) {
                return make_json_error(__('admin::goods.goods_sn_exists'));
            }

            $ru_id = $adminru['ru_id'];
            $res = Products::where('product_sn', $goods_sn);
            $res = $res->where(function ($query) use ($ru_id) {
                $query->whereHasIn('getGoods', function ($query) use ($ru_id) {
                    $query->where('user_id', $ru_id);
                });
            });
            $res = $res->count();
            if ($res > 0) {
                return make_json_error(__('admin::goods.goods_sn_exists'));
            }
            $data = [
                'goods_sn' => $goods_sn,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result(stripslashes($goods_sn));
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品条形码
        /*------------------------------------------------------ */
        elseif ($act == 'edit_goods_bar_code') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $bar_code = json_str_iconv(trim(request()->get('val', '')));

            $goods_info = get_admin_goods_info($goods_id);

            /* 检查是否重复 */
            $is_only = Goods::where('bar_code', $bar_code)
                ->where('goods_id', '<>', $goods_id)
                ->where('user_id', $goods_info['user_id'])
                ->count();

            if ($is_only > 0) {
                return make_json_error(__('admin::goods.goods_bar_code_exists'));
            }

            $exists = Products::whereHasIn('getGoods', function ($query) use ($goods_info) {
                $query->where('user_id', $goods_info['user_id']);
            });

            $exists = $exists->where('bar_code', $bar_code);

            $exists = $exists->count();

            if ($exists > 0) {
                return make_json_error(__('admin::goods.goods_bar_code_exists'));
            }

            Goods::where('goods_id', $goods_id)->update([
                'bar_code' => $bar_code
            ]);

            return make_json_result(stripslashes($bar_code));
        }

        /*------------------------------------------------------ */
        //-- 更新微信商品二维码
        /*------------------------------------------------------ */
        elseif ($act == 'edit_goods_qrcode_sort') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = json_str_iconv(trim(request()->get('val', 0)));

            if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status') && file_exists(WXAPP_MEDIA_CONCISE)) {
                \App\Modules\WxMedia\Models\WxappMediaGoodsQrcod::where('id', $id)->update([
                    'sort' => $val
                ]);
            }

            return make_json_result(stripslashes($val));
        }

        /*------------------------------------------------------ */
        //-- 判断商品货号
        /*------------------------------------------------------ */
        elseif ($act == 'check_goods_sn') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('goods_id', 0);
            $goods_sn = htmlspecialchars(json_str_iconv(request()->get('goods_sn', '')));

            if (!empty($goods_sn)) {
                $goods_info = get_admin_goods_info($goods_id);
                $seller_id = !isset($goods_info['user_id']) && empty($goods_info['user_id']) ? $adminru['ru_id'] : $goods_info['user_id'];

                /* 检查是否重复 */
                $is_only = Goods::where('goods_sn', $goods_sn)
                    ->where('goods_id', '<>', $goods_id)
                    ->where('user_id', $seller_id)
                    ->count();
                if ($is_only > 0) {
                    return make_json_error(__('admin::goods.goods_sn_exists'));
                }
                if (!empty($goods_sn)) {
                    $res = Products::where('product_sn', $goods_sn);
                    $res = $res->whereHasIn('getGoods', function ($query) use ($seller_id) {
                        $query->where('user_id', $seller_id);
                    });
                    $product_id = $res->count();
                    if ($product_id > 0) {
                        return make_json_error(__('admin::goods.goods_sn_exists'));
                    }
                }
                return make_json_result('');
            }
        }
        /*------------------------------------------------------ */
        //-- 判断商品货品货号
        /*------------------------------------------------------ */
        elseif ($act == 'check_products_goods_sn') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('goods_id', 0);
            $goods_sn = json_str_iconv(request()->get('goods_sn', ''));
            $products_sn = explode('||', $goods_sn);
            if (!is_array($products_sn)) {
                return make_json_result('');
            } else {
                $int_arry = [];
                foreach ($products_sn as $val) {
                    if (empty($val)) {
                        continue;
                    }
                    if (is_array($int_arry)) {
                        if (in_array($val, $int_arry)) {
                            return make_json_error($val . __('admin::goods.goods_sn_exists'));
                        }
                    }
                    $int_arry[] = $val;
                    $goods_sn = DB::table('goods')->where('goods_sn', $val)->count('goods_id');
                    if ($goods_sn > 0) {
                        return make_json_error($val . __('admin::goods.goods_sn_exists'));
                    }
                    $products_sn = DB::table('products')->where('product_sn', $val)->count('goods_id');
                    if ($products_sn > 0) {
                        return make_json_error($val . __('admin::goods.goods_sn_exists'));
                    }
                }
            }
            /* 检查是否重复 */
            return make_json_result('');
        }

        /*------------------------------------------------------ */
        //-- 修改商品价格
        /*------------------------------------------------------ */
        elseif ($act == 'edit_goods_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = (int)request()->get('id', 0);
            $goods_price = floatval(request()->get('val', 0));
            $price_rate = floatval(config('shop.market_price_rate') * $goods_price);

            if (empty($goods_price) || $goods_price < 0) {
                return make_json_error(__('admin::goods.shop_price_invalid'));
            }

            $goods_info = Goods::query()->where('goods_id', $goods_id)->first();
            $goods_info = $goods_info ? $goods_info->toArray() : [];

            if (empty($goods_info)) {
                return make_json_error(__('admin::goods.goods_not_exist'));
            }

            if ($goods_price == $goods_info['shop_price']) {
                return make_json_result(number_format($goods_price, 2, '.', ''));
            } else {
                // 价格输入验证 0保留2位小数, 2保留1位小数, 3不保留小数
                if (config('shop.price_format') == 3) {
                    if (preg_match("/^\d+$/", $goods_price) == 0) {
                        return make_json_error(__('admin::goods.js_languages.price_format_not_decimal'));
                    }
                }

                if (config('shop.price_format') == 2) {
                    if (preg_match("/^[0-9]+(.[0-9]{1})?$/", $goods_price) == 0) {
                        return make_json_error(__('admin::goods.js_languages.price_format_support_1_decimal'));
                    }
                }

                if (config('shop.price_format') == 0) {
                    if (preg_match("/^[0-9]+(.[0-9]{1,2})?$/", $goods_price) == 0) {
                        return make_json_error(__('admin::goods.js_languages.price_format_support_2_decimal'));
                    }
                }

                $data = [
                    'shop_price' => $goods_price,
                    'market_price' => $price_rate,
                    'last_update' => TimeRepository::getGmTime()
                ];
                $res = Goods::where('goods_id', $goods_id)->update($data);
                if ($res > 0) {
                    // 商品操作日志 更新前与更新后数据
                    $extendParam = [
                        'logs_change_new' => [
                            'shop_price' => $goods_price,
                        ],
                        'admin_id' => session('admin_id')
                    ];
                    event(new \App\Events\GoodsEditEvent('change_log', $goods_info, $extendParam));

                    clear_cache_files();
                    return make_json_result(number_format($goods_price, 2, '.', ''));
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品库存数量
        /*------------------------------------------------------ */
        elseif ($act == 'edit_goods_number') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $goods_num = intval(request()->get('val', ''));

            if ($goods_num < 0 || $goods_num == 0 && request()->get('val', '') != "$goods_num") {
                return make_json_error(__('admin::goods.goods_number_error'));
            }

            $object = Products::whereRaw(1);
            $exist = $this->goodsManageService->checkGoodsProductExist($object, $goods_id);

            if ($exist == 1) {
                return make_json_error(__('admin::common.sys.wrong') . __('admin::goods.cannot_goods_number'));
            }

            //库存日志
            $goodsInfo = get_admin_goods_info($goods_id);

            $add_number = true;
            if ($goods_num != $goodsInfo['goods_number']) {
                if ($goods_num > $goodsInfo['goods_number']) {
                    $number = $goods_num - $goodsInfo['goods_number'];

                    if ($number == 0) {
                        $add_number = false;
                    }

                    $number = "+ " . $number;
                    $use_storage = 13;
                } else {
                    $number = $goodsInfo['goods_number'] - $goods_num;

                    if ($number == 0) {
                        $add_number = false;
                    }

                    $number = "- " . $number;
                    $use_storage = 8;
                }

                if ($add_number == true) {
                    $logs_other = [
                        'goods_id' => $goods_id,
                        'order_id' => 0,
                        'use_storage' => $use_storage,
                        'admin_id' => session('admin_id'),
                        'number' => $number,
                        'model_inventory' => $goodsInfo['model_inventory'],
                        'model_attr' => $goodsInfo['model_attr'],
                        'add_time' => TimeRepository::getGmTime()
                    ];

                    GoodsInventoryLogs::insert($logs_other);
                }
            }

            $data = [
                'goods_number' => $goods_num,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($goods_num);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品佣金比例
        /*------------------------------------------------------ */
        elseif ($act == 'edit_commission_rate') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $commission_rate = floatval(trim(request()->get('val', '')));

            $data = ['commission_rate' => $commission_rate];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                $goods = get_admin_goods_info($goods_id);

                $data = ['commission_rate' => $commission_rate];
                Cart::where('ru_id', $goods['user_id'])
                    ->where('goods_id', $goods_id)
                    ->where('is_real', 1)
                    ->where('is_gift', 0)
                    ->update($data);
                clear_cache_files();
                return make_json_result($commission_rate);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改上架状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_on_sale') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = (int)request()->get('id', 0);
            $on_sale = (int)request()->get('val', 0);

            $model = Goods::query()->where('goods_id', $goods_id)->first();

            if ($model) {

                if ($on_sale == 1) {
                    //是否参加预售活动
                    $presale = PresaleActivity::where('goods_id', $goods_id)->value('act_id');
                    if (!empty($presale)) {
                        return make_json_error(__('admin::goods.presale_error'), 0);
                    }
                }

                //验证商品是否参与活动
//                $is_promotion = is_promotion($goods_id);
//                // 验证返回提示
//                if ($is_promotion) {
//                    $return_prompt = is_promotion_error($is_promotion);
//                    return $return_prompt;
//                }

                // 检测是否加入指定购买成为分销商商品
                if (file_exists(MOBILE_DRP) && $on_sale == 1) {
                    if ($model->membership_card_id > 0) {
                        Cart::where('goods_id', $goods_id)->delete();
                        return make_json_error(lang('manage/goods.membership_card_goods_notice'), 0);
                    }
                }

                $data = [
                    'is_on_sale' => $on_sale,
                    'last_update' => TimeRepository::getGmTime()
                ];
                $res = $model->update($data);
                if ($res > 0) {
                    // 下架商品 设置购物车商品失效且取消勾选
                    if ($on_sale == 0) {
                        Cart::where('goods_id', $goods_id)->where('extension_code', '<>', 'presale')->update(['is_invalid' => 1, 'is_checked' => 0]);
                    } else {
                        $res = PresaleActivity::where('goods_id', $goods_id)->count();
                        if ($res > 0) {
                            PresaleActivity::where('goods_id', $goods_id)->delete();
                            Cart::where('goods_id', $goods_id)->delete();
                        }
                    }

                    clear_cache_files();
                    return make_json_result($on_sale);
                }
            }

            return make_json_error('invalid params');
        }

        /*------------------------------------------------------ */
        //-- 修改商品显示状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_show') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = (int)request()->get('id', 0);
            $is_show = (int)request()->get('val', 0);

            //验证商品是否参与活动
//            $is_promotion = is_promotion($goods_id);
//            // 验证返回提示
//            if ($is_promotion) {
//                $return_prompt = is_promotion_error($is_promotion);
//                return $return_prompt;
//            }

            $model = Goods::where('goods_id', $goods_id);

            $data = [
                'is_show' => $is_show,
                'last_update' => TimeRepository::getGmTime()
            ];

            $model->update($data);

            return make_json_result($is_show);
        }

        /*------------------------------------------------------ */
        //-- 修改相册排序
        /*------------------------------------------------------ */
        elseif ($act == 'edit_img_desc') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $img_id = request()->get('id', 0);
            $img_desc = intval(request()->get('val', ''));

            $data = ['img_desc' => $img_desc];
            $res = GoodsGallery::where('img_id', $img_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($img_desc);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改精品推荐状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_best') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $is_best = intval(request()->get('val', ''));

            $data = [
                'is_best' => $is_best,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($is_best);
            }
        } elseif ($act == 'main_dsc') {

        }
        /*------------------------------------------------------ */
        //-- 修改新品推荐状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_new') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $is_new = intval(request()->get('val', ''));

            $data = [
                'is_new' => $is_new,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($is_new);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改热销推荐状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_hot') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $is_hot = intval(request()->get('val', ''));

            $data = [
                'is_hot' => $is_hot,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($is_hot);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改店铺精品推荐状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_store_best') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $store_best = intval(request()->get('val', ''));

            $data = [
                'store_best' => $store_best,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($store_best);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改店铺新品推荐状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_store_new') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $store_new = intval(request()->get('val', ''));

            $data = [
                'store_new' => $store_new,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($store_new);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改店铺热销推荐状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_store_hot') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $store_hot = intval(request()->get('val', ''));

            $data = [
                'store_hot' => $store_hot,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($store_hot);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改正品保证状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_reality') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = intval(request()->get('val', ''));

            $data = ['is_reality' => $val];
            $res = GoodsExtend::where('goods_id', $id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($val);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改包        退服务状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_return') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = intval(request()->get('val', ''));

            $data = ['is_return' => $val];
            $res = GoodsExtend::where('goods_id', $id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($val);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改闪速�        �送状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_fast') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = intval(request()->get('val', ''));

            $data = ['is_fast' => $val];
            $res = GoodsExtend::where('goods_id', $id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($val);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改是否为�        �运费商品状态 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_shipping') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $is_shipping = intval(request()->get('val', ''));
            $data = [
                'is_shipping' => $is_shipping,
                'last_update' => TimeRepository::getGmTime()
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($is_shipping);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品排序
        /*------------------------------------------------------ */
        elseif ($act == 'edit_sort_order') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);
            $sort_order = intval(request()->get('val', ''));
            $weights = get_goods_weights($goods_id);

            $data = [
                'sort_order' => $sort_order,
                'last_update' => TimeRepository::getGmTime(),
                'weights' => $weights + $sort_order
            ];
            $res = Goods::where('goods_id', $goods_id)->update($data);

            if ($res > 0) {
                clear_cache_files();
                return make_json_result($sort_order);
            }
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $is_delete = request()->get('is_delete', 0);
            $code = request()->get('extension_code', '');
            $review_status = request()->get('review_status', 0);
            $goods_list = $this->goodsManageService->getGoodsList($is_delete, ($code == '') ? 1 : 0, '', $review_status);

            $handler_list = [];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=card', 'title' => __('admin::goods.card'), 'img' => 'icon_send_bonus.gif'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=replenish', 'title' => __('admin::goods.replenish'), 'img' => 'icon_add.gif'];
            $handler_list['virtual_card'][] = ['url' => 'virtual_card.php?act=batch_card_add', 'title' => __('admin::goods.batch_card_add'), 'img' => 'icon_output.gif'];

            if (isset($handler_list[$code])) {
                $this->smarty->assign('add_handler', $handler_list[$code]);
            }
            $this->smarty->assign('code', $code);
            $this->smarty->assign('goods_list', $goods_list['goods']);
            $this->smarty->assign('filter', $goods_list['filter']);
            $this->smarty->assign('record_count', $goods_list['record_count']);
            $this->smarty->assign('page_count', $goods_list['page_count']);
            $this->smarty->assign('list_type', $is_delete ? 'trash' : 'goods');
            $this->smarty->assign('use_storage', empty(config('shop.use_storage')) ? 0 : 1);

            /* 排序标记 */
            $sort_flag = sort_flag($goods_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            /* 获取商品类型存在规格的类型 */
            $specifications = get_goods_type_specifications();
            $this->smarty->assign('specifications', $specifications);

            $tpl = $is_delete ? 'goods_trash.dwt' : 'goods_list.dwt';

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $this->smarty->assign('nowTime', TimeRepository::getGmTime());

            $transport_list = GoodsTransport::select('tid', 'title')->where('ru_id', $adminru['ru_id']);
            $transport_list = BaseRepository::getToArrayGet($transport_list);

            $this->smarty->assign('transport_list', $transport_list); //商品运费

            set_default_filter(); //设置默认筛选

            return make_json_result(
                $this->smarty->fetch($tpl),
                '',
                ['filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 放入回收站
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {

            /* 检查权限 */
            $check_auth = check_authz_json('remove_back');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_id = request()->get('id', 0);

            $goods_can_handle = $this->goodsManageService->goodsCanHandle($goods_id, $adminru['ru_id']);
            if ($goods_can_handle != true) { // 只能操作当前后台的商品
                return make_json_error(__('admin::goods.del_current_goods_fail'));
            }

            $order_count = $this->goodsManageService->getOrderGoodsCout($goods_id);

            if ($order_count > 0) {
                return make_json_error(__('admin::goods.del_goods_fail'));
            }

            $res = Goods::select('goods_id', 'user_id');
            $res = $res->with([
                'getGoodsActivity'
            ]);
            $res = $res->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($res);

            //参与商品活动的商品无法加入回收站
            if (isset($goods['get_goods_activity']) && !empty($goods['get_goods_activity'])) {
                return make_json_error(__('admin::goods.remove_goods_activity_isset'));
            }

            if ($adminru['ru_id'] > 0 && $adminru['ru_id'] != $goods['user_id']) {
                return make_json_error(__('admin::goods.illegal_handle_error'));
            }

            $is_promotion = is_promotion($goods_id);

            if ($is_promotion) {
                foreach ($is_promotion as $res) {
                    $res[$res['type']]['goods_sn'] = isset($res[$res['type']]['goods_sn']) ? $this->dscRepository->delStrComma($res[$res['type']]['goods_sn']) : '';

                    switch ($res['type']) {
                        case 'snatch': //夺宝奇兵
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_snatch'), 0);
                            break;

                        case 'group_buy': //团购
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_group_buy'), 0);
                            break;

                        case 'auction': //拍卖
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_auction'), 0);
                            break;

                        case 'package': //礼包
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_package'), 0);
                            break;

                        case 'seckill': //秒杀
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_seckill'), 0);
                            break;

                        case 'presale': //预售
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_presale'), 0);
                            break;

                        case 'team': //拼团
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_team'), 0);
                            break;

                        case 'bargain': //砍价
                            return make_json_error(__('admin::goods.del_goods_sn') . $res[$res['type']]['goods_sn'] . __('admin::goods.del_bargain'), 0);
                            break;
                    }
                }
            }

            $data = ['is_delete' => 1];
            $res = Goods::where('goods_id', $goods)->update($data);
            if ($res > 0) {
                clear_cache_files();

                // 设置购物车商品无效
                Cart::where('goods_id', $goods_id)->update(['is_invalid' => 1, 'is_checked' => 0]);

                $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
                $goods_name = $goods_name ? $goods_name : '';

                admin_log(addslashes($goods_name), 'trash', 'goods'); // 记录日志

                $url = 'goods.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

                return dsc_header("Location: $url\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 还原回收站中的商品
        /*------------------------------------------------------ */

        elseif ($act == 'restore_goods') {
            $goods_id = request()->get('id', 0);

            $check_auth = check_authz_json('remove_back');
            if ($check_auth !== true) {
                return $check_auth;
            } // 检查权限

            $data = [
                'is_delete' => 0,
                'add_time' => TimeRepository::getGmTime()
            ];
            Goods::where('goods_id', $goods_id)->update($data);
            clear_cache_files();

            $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
            $goods_name = $goods_name ? $goods_name : '';

            // 还原购物车商品
            Cart::where('goods_id', $goods_id)->where('is_invalid', 1)->update(['is_invalid' => 0]);

            admin_log(addslashes($goods_name), 'restore', 'goods'); // 记录日志

            $url = 'goods.php?act=query&' . str_replace('act=restore_goods', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 彻底删除商品
        /*------------------------------------------------------ */
        elseif ($act == 'drop_goods') {
            // 检查权限
            $check_auth = check_authz_json('remove_back');
            if ($check_auth !== true) {
                return $check_auth;
            }

            // 取得参数
            $goods_id = request()->get('id', 0);
            if ($goods_id <= 0) {
                return make_json_error('invalid params');
            }

            /* 取得商品信息 */
            $res = Goods::select(
                'goods_id',
                'goods_name',
                'is_delete',
                'is_real',
                'goods_thumb',
                'user_id',
                'goods_img',
                'original_img',
                'goods_video',
                'goods_desc'
            );
            $res = $res->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($res);
            if (empty($goods)) {
                return make_json_error(__('admin::goods.goods_not_exist'));
            }

            if ($adminru['ru_id'] > 0 && $adminru['ru_id'] != $goods['user_id']) {
                return make_json_error(__('admin::goods.illegal_handle_error'));
            }

            if ($goods['is_delete'] != 1) {
                return make_json_error(__('admin::goods.goods_not_in_recycle_bin'));
            }

            if ($goods['goods_desc']) {
                $desc_preg = get_goods_desc_images_preg('', $goods['goods_desc']);
                get_desc_images_del($desc_preg['images_list']);
            }

            $arr = [];
            /* 删除商品图片和轮播图片 */
            if (!empty($goods['goods_thumb']) && strpos($goods['goods_thumb'], "data/gallery_album") === false) {
                $arr[] = $goods['goods_thumb'];
                dsc_unlink(storage_public($goods['goods_thumb']));
            }
            if (!empty($goods['goods_img']) && strpos($goods['goods_img'], "data/gallery_album") === false) {
                $arr[] = $goods['goods_img'];
                dsc_unlink(storage_public($goods['goods_img']));
            }
            if (!empty($goods['original_img']) && strpos($goods['original_img'], "data/gallery_album") === false) {
                $arr[] = $goods['original_img'];
                dsc_unlink(storage_public($goods['original_img']));
            }

            /* 删除视频 */
            if (!empty($goods['goods_video'])) {
                $arr[] = $goods['goods_video'];
                dsc_unlink(storage_public($goods['goods_video']));

                $video_path = storage_public(DATA_DIR . '/uploads/goods/' . $goods['goods_id']);
                if (file_exists($video_path)) {
                    rmdir($video_path);
                }
            }

            if (!empty($arr)) {
                $this->dscRepository->getOssDelFile($arr);
            }

            /* 删除商品 */
            Goods::where('goods_id', $goods_id)->delete();
            //删除商品扩展信息by wang
            GoodsExtend::where('goods_id', $goods_id)->delete();

            /* 删除商品的货品记录 */
            Products::where('goods_id', $goods_id)->delete();

            ProductsWarehouse::where('goods_id', $goods_id)->delete();

            ProductsArea::where('goods_id', $goods_id)->delete();

            //清楚商品零时货品表数据
            ProductsChangelog::where('goods_id', $goods_id)->delete();

            //删除讨论圈记录
            DiscussCircle::where('goods_id', $goods_id)->delete();

            // 删除视频号商品
            if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status')) {
                $WxappGoodsCount = \App\Modules\WxMedia\Models\WxappGoodsExtension::where('goods_id', $goods_id)->count('id');

                if ($WxappGoodsCount > 0) {
                    $WxConfig = app(\App\Modules\Wxapp\Services\WxappConfigService::class)->get_config();

                    $WxappConfig = [
                        'appid' => $WxConfig['wx_appid'] ?? '',
                        'secret' => $WxConfig['wx_appsecret'] ?? '',
                    ];

                    app(\App\Modules\WxMedia\Services\WxappMediaGoodsService::class)->delMediaGoods($goods_id, 0, $WxappConfig);
                }
            }

            /* 记录日志 */
            admin_log(addslashes($goods['goods_name']), 'remove', 'goods');

            /* 删除商品相册 */
            $res = GoodsGallery::select('img_url', 'thumb_url', 'img_original');
            $res = $res->where('goods_id', $goods_id);
            $res = BaseRepository::getToArrayGet($res);

            foreach ($res as $row) {
                $arr = [];
                if (!empty($row['img_url']) && strpos($row['img_url'], "data/gallery_album") === false) {
                    $arr[] = $row['img_url'];
                    @unlink('../' . $row['img_url']);
                }
                if (!empty($row['thumb_url']) && strpos($row['thumb_url'], "data/gallery_album") === false) {
                    $arr[] = $row['thumb_url'];
                    @unlink('../' . $row['thumb_url']);
                }
                if (!empty($row['img_original']) && strpos($row['img_original'], "data/gallery_album") === false) {
                    $arr[] = $row['img_original'];
                    @unlink('../' . $row['img_original']);
                }
                if (!empty($arr)) {
                    $this->dscRepository->getOssDelFile($arr);
                }
            }

            GoodsGallery::where('goods_id', $goods_id)->delete();

            /* 删除相关表记录 */
            CollectGoods::where('goods_id', $goods_id)->delete();

            GoodsArticle::where('goods_id', $goods_id)->delete();

            GoodsAttr::where('goods_id', $goods_id)->delete();

            GoodsCat::where('goods_id', $goods_id)->delete();

            MemberPrice::where('goods_id', $goods_id)->delete();

            GroupGoods::where('parent_id', $goods_id)->delete();

            GroupGoods::where('goods_id', $goods_id)->delete();

            LinkGoods::where('goods_id', $goods_id)->delete();

            LinkGoods::where('link_goods_id', $goods_id)->delete();

            Tag::where('goods_id', $goods_id)->delete();

            Comment::where('comment_type', 0)->where('id_value', $goods_id)->delete();

            CollectGoods::where('goods_id', $goods_id)->delete();

            BookingGoods::where('goods_id', $goods_id)->delete();

            GoodsActivity::where('goods_id', $goods_id)->delete();

            Cart::where('goods_id', $goods_id)->delete();


            WarehouseGoods::where('goods_id', $goods_id)->delete();

            WarehouseAttr::where('goods_id', $goods_id)->delete();

            WarehouseAreaGoods::where('goods_id', $goods_id)->delete();

            WarehouseAreaAttr::where('goods_id', $goods_id)->delete();

            /* 如果不是实体商品，删除相应虚拟商品记录 */
            if ($goods['is_real'] != 1) {
                $res = VirtualCard::where('goods_id', $goods_id)->delete();
                if ($res < 0 && $this->db->errno() != 1146) {
                    return $this->db->error();
                }
            }

            clear_cache_files();
            $url = 'goods.php?act=query&' . str_replace('act=drop_goods', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 删除图片
        /*------------------------------------------------------ */
        elseif ($act == 'drop_image') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $img_id = request()->get('img_id', 0);

            /* 删除图片文件 */
            $res = GoodsGallery::select('img_url', 'thumb_url', 'img_original');
            $res = $res->where('img_id', $img_id);
            $row = BaseRepository::getToArrayFirst($res);

            $img_url = storage_public($row['img_url']);
            $thumb_url = storage_public($row['thumb_url']);
            $img_original = storage_public($row['img_original']);

            $arr = [];
            if ($row['img_url'] != '' && strpos($row['img_url'], "data/gallery_album") === false) {
                $arr[] = $row['img_url'];
                dsc_unlink($img_url);
            }
            if ($row['thumb_url'] != '' && strpos($row['img_url'], "data/gallery_album") === false) {
                $arr[] = $row['thumb_url'];
                dsc_unlink($thumb_url);
            }
            if ($row['img_original'] != '' && strpos($row['img_url'], "data/gallery_album") === false) {
                $arr[] = $row['img_original'];
                dsc_unlink($img_original);
            }

            if (!empty($arr)) {
                $this->dscRepository->getOssDelFile($arr);
            }

            /* 删除数据 */
            GoodsGallery::where('img_id', $img_id)->delete();
            clear_cache_files();
            return make_json_result($img_id);
        }

        /*------------------------------------------------------ */
        //-- 删除仓库库存 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'drop_product') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('product_id', 0);
            $group_attr = request()->get('group_attr', '');
            $group_attr = dsc_decode($group_attr, true);

            $res = Products::where('product_id', $product_id);
            if ($group_attr['goods_model'] == 1) {
                $res = ProductsWarehouse::where('product_id', $product_id);
                $res = $res->select('warehouse_id');
            } elseif ($group_attr['goods_model'] == 2) {
                $res = ProductsArea::where('product_id', $product_id);
                $res = $res->select('area_id', 'city_id');
            }

            $product = BaseRepository::getToArrayFirst($res);

            $group_attr['warehouse_id'] = $product['warehouse_id'] ?? 0;
            $group_attr['area_id'] = $product['area_id'] ?? 0;
            $group_attr['city_id'] = $product['city_id'] ?? 0;

            /* 删除数据 */
            $res->delete();
            clear_cache_files();
            return make_json_result_too($product_id, 0, '', $group_attr);
        }

        /*------------------------------------------------------ */
        //-- 删除仓库库存 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'drop_warehouse') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('w_id', 0);

            /* 删除数据 */
            WarehouseGoods::where('w_id', $w_id)->delete();
            clear_cache_files();
            return make_json_result($w_id);
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库库存 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_number') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $region_number = intval(request()->get('val', ''));

            $res = WarehouseGoods::select('goods_id', 'region_number', 'region_id');
            $res = $res->where('w_id', $w_id);
            $warehouse_goods = BaseRepository::getToArrayFirst($res);

            $goodsInfo = get_admin_goods_info($warehouse_goods['goods_id']);

            //库存日志
            $add_number = true;
            if ($region_number != $warehouse_goods['region_number']) {
                if ($region_number > $warehouse_goods['region_number']) {
                    $number = $region_number - $warehouse_goods['region_number'];

                    if ($number == 0) {
                        $add_number = false;
                    }

                    $number = "+ " . $number;
                    $use_storage = 13;
                } else {
                    $number = $warehouse_goods['region_number'] - $region_number;

                    if ($number == 0) {
                        $add_number = false;
                    }

                    $number = "- " . $number;
                    $use_storage = 8;
                }

                if ($add_number == true) {
                    $logs_other = [
                        'goods_id' => $warehouse_goods['goods_id'],
                        'order_id' => 0,
                        'use_storage' => $use_storage,
                        'admin_id' => session('admin_id'),
                        'number' => $number,
                        'model_inventory' => $goodsInfo['model_inventory'],
                        'model_attr' => $goodsInfo['model_attr'],
                        'warehouse_id' => $warehouse_goods['region_id'],
                        'add_time' => TimeRepository::getGmTime()
                    ];

                    GoodsInventoryLogs::insert($logs_other);
                }
            }

            $data = ['region_number' => $region_number];
            $res = WarehouseGoods::where('w_id', $w_id)->update($data);
            if ($res) {
                clear_cache_files();
                return make_json_result($region_number);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库编号 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_sn') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $region_sn = addslashes(trim(request()->get('val', '')));

            $data = ['region_sn' => $region_sn];
            $res = WarehouseGoods::where('w_id', $w_id)->update($data);
            if ($res) {
                clear_cache_files();
                return make_json_result($region_sn);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库价格 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $warehouse_price = floatval(request()->get('val', ''));

            $data = ['warehouse_price' => $warehouse_price];
            $res = WarehouseGoods::where('w_id', $w_id)->update($data);

            if ($res) {
                clear_cache_files();
                return make_json_result($warehouse_price);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库促销价格 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_promote_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $warehouse_promote_price = floatval(request()->get('val', ''));

            $data = ['warehouse_promote_price' => $warehouse_promote_price];
            WarehouseGoods::where('w_id', $w_id)->update($data);

            return make_json_result($warehouse_promote_price);
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库赠送消费积分数 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_give_integral') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $give_integral = floatval(request()->get('val', ''));

            $data = ['give_integral' => $give_integral];
            $res = WarehouseGoods::where('w_id', $w_id)->update($data);

            $goods = WarehouseGoods::select('w_id', 'user_id', 'warehouse_price', 'warehouse_promote_price')->where('w_id', $w_id);
            $goods = BaseRepository::getToArrayFirst($res);

            if ($goods['user_id']) {
                if ($goods['warehouse_promote_price']) {
                    if ($goods['warehouse_promote_price'] < $goods['warehouse_price']) {
                        $shop_price = $goods['warehouse_promote_price'];
                    } else {
                        $shop_price = $goods['warehouse_price'];
                    }
                } else {
                    $shop_price = $goods['warehouse_price'];
                }

                $grade_rank = get_seller_grade_rank($goods['user_id']);
                $give = floor($shop_price * $grade_rank['give_integral']);

                if ($give_integral > $give) {
                    return make_json_error(sprintf(__('admin::goods.goods_give_integral'), $give));
                }
            }

            if ($res) {
                clear_cache_files();
                return make_json_result($give_integral);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库赠送等级积分数 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_rank_integral') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $rank_integral = floatval(request()->get('val', ''));

            $data = ['rank_integral' => $rank_integral];
            $res = WarehouseGoods::where('w_id', $w_id)->update($data);

            $goods = WarehouseGoods::select('w_id', 'user_id', 'warehouse_price', 'warehouse_promote_price')->where('w_id', $w_id);
            $goods = BaseRepository::getToArrayFirst($goods);


            if ($goods['user_id']) {
                if ($goods['warehouse_promote_price']) {
                    if ($goods['warehouse_promote_price'] < $goods['warehouse_price']) {
                        $shop_price = $goods['warehouse_promote_price'];
                    } else {
                        $shop_price = $goods['warehouse_price'];
                    }
                } else {
                    $shop_price = $goods['warehouse_price'];
                }

                $grade_rank = get_seller_grade_rank($goods['user_id']);
                $rank = floor($shop_price * $grade_rank['rank_integral']);

                if ($rank_integral > $rank) {
                    return make_json_error(sprintf(__('admin::goods.goods_rank_integral'), $rank));
                }
            }

            if ($res) {
                clear_cache_files();
                return make_json_result($rank_integral);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库积分购买金额 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_pay_integral') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $w_id = request()->get('id', 0);
            $pay_integral = floatval(request()->get('val', ''));

            $data = ['pay_integral' => $pay_integral];
            $res = WarehouseGoods::where('w_id', $w_id)->update($data);

            $goods = WarehouseGoods::select('w_id', 'user_id', 'warehouse_price', 'warehouse_promote_price')->where('w_id', $w_id);
            $goods = BaseRepository::getToArrayFirst($goods);

            if ($goods['user_id']) {
                if ($goods['warehouse_promote_price']) {
                    if ($goods['warehouse_promote_price'] < $goods['warehouse_price']) {
                        $shop_price = $goods['warehouse_promote_price'];
                    } else {
                        $shop_price = $goods['warehouse_price'];
                    }
                } else {
                    $shop_price = $goods['warehouse_price'];
                }

                $grade_rank = get_seller_grade_rank($goods['user_id']);
                $pay = floor($shop_price * $grade_rank['pay_integral']);

                if ($pay_integral > $pay) {
                    return make_json_error(sprintf(__('admin::goods.goods_pay_integral'), $pay));
                }
            }

            if ($res) {
                clear_cache_files();
                return make_json_result($pay_integral);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区编号 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_sn') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $region_sn = addslashes(trim(request()->get('val', '')));

            $data = ['region_sn' => $region_sn];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);
            if ($res) {
                clear_cache_files();
                return make_json_result($region_sn);
            }
        }

        /*------------------------------------------------------ */
        //-- 删除仓库地区价格 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'drop_warehouse_area') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('a_id', 0);

            /* 删除数据 */
            WarehouseAreaGoods::where('a_id', $a_id)->delete();
            clear_cache_files();
            return make_json_result($a_id);
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区价格 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $region_price = floatval(request()->get('val', ''));

            $data = ['region_price' => $region_price];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);
            if ($res) {
                clear_cache_files();
                return make_json_result($region_price);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区库存 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_number') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $region_number = floatval(request()->get('val', ''));

            $res = WarehouseAreaGoods::select('goods_id', 'region_number', 'region_id', 'city_id');
            $res = $res->where('a_id', $a_id);
            $area_goods = BaseRepository::getToArrayFirst($res);

            $goodsInfo = get_admin_goods_info($area_goods['goods_id']);

            //库存日志
            if ($region_number != $area_goods['region_number']) {
                $add_number = true;
                if ($region_number > $area_goods['region_number']) {
                    $number = $region_number - $area_goods['region_number'];

                    if ($number == 0) {
                        $add_number = false;
                    }

                    $number = "+ " . $number;
                    $use_storage = 13;
                } else {
                    $number = $area_goods['region_number'] - $region_number;

                    if ($number == 0) {
                        $add_number = false;
                    }

                    $number = "- " . $number;
                    $use_storage = 8;
                }

                if ($add_number == true) {
                    $logs_other = [
                        'goods_id' => $area_goods['goods_id'],
                        'order_id' => 0,
                        'use_storage' => $use_storage,
                        'admin_id' => session('admin_id'),
                        'number' => $number,
                        'model_inventory' => $goodsInfo['model_inventory'],
                        'model_attr' => $goodsInfo['model_attr'],
                        'area_id' => $area_goods['region_id'],
                        'city_id' => $area_goods['city_id'],
                        'add_time' => TimeRepository::getGmTime()
                    ];

                    GoodsInventoryLogs::insert($logs_other);
                }
            }

            $data = ['region_number' => $region_number];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);
            if ($res) {
                clear_cache_files();
                return make_json_result($region_number);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区促销价格 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_promote_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $region_promote_price = floatval(request()->get('val', ''));

            $data = ['region_promote_price' => $region_promote_price];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);
            if ($res) {
                clear_cache_files();
                return make_json_result($region_promote_price);
            }
        }

        /*------------------------------------------------------ */
        //-- 查询该仓库的地区列表 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_area_list') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $key = request()->get('key', 0);
            $goods_id = request()->get('goods_id', 0);
            $ru_id = request()->get('ru_id', 0);
            $type = request()->get('type', 1);

            if ($id > 0) {
                $area_list = get_warehouse_area_list($id, $type, $goods_id, $ru_id);
                $this->smarty->assign('area_list', $area_list);
                $this->smarty->assign('warehouse_id', $id);
                $this->smarty->assign('akey', $key);
                $this->smarty->assign('goods_id', $goods_id);
                $this->smarty->assign('user_id', $ru_id);
                $this->smarty->assign('type', $type);
                $this->smarty->assign('area_pricetype', config('shop.area_pricetype'));

                $result['error'] = 0;
                $result['key'] = $key;
                $result['html'] = $this->smarty->fetch('library/warehouse_area_list.lbi');
            } else {
                $result['key'] = $key;
                $result['error'] = 1;
            }

            return make_json_result($result);
        }

        /* ------------------------------------------------------ */
        //-- 查询该仓库的地区列表 //ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_warehouse_area_city') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $key = request()->get('key', 0);
            $goods_id = request()->get('goods_id', 0);
            $ru_id = request()->get('ru_id', 0);
            $type = request()->get('type', 1);

            if ($id > 0) {
                $area_list = get_warehouse_area_list($id, $type, $goods_id, $ru_id);
                $this->smarty->assign('area_list', $area_list);
                $this->smarty->assign('warehouse_id', $id);
                $this->smarty->assign('key', $key);
                $this->smarty->assign('goods_id', $goods_id);
                $this->smarty->assign('user_id', $ru_id);
                $this->smarty->assign('type', $type);

                $result['error'] = 0;
                $result['key'] = $key;
                $result['html'] = $this->smarty->fetch('library/warehouse_area_city.lbi');
            } else {
                $result['key'] = $key;
                $result['error'] = 1;
            }

            return make_json_result($result);
        }

        /* ------------------------------------------------------ */
        //-- 查询该仓库的地区列表 //ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'city_region') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $warehouse_id = request()->get('warehouse_id', 0);
            $area_id = request()->get('area_id', 0);
            $city_id = request()->get('city_id', 0);
            $onload = request()->get('onload', 0);

            if ($area_id > 0) {
                $res = RegionWarehouse::select('region_id', 'region_name')->where('parent_id', $area_id);
                $city_list = BaseRepository::getToArrayGet($res);
                $this->smarty->assign('city_list', $city_list);

                $this->smarty->assign('city_id', $city_id);

                $result['error'] = 0;

                $this->smarty->assign('area_id', $area_id);
                $this->smarty->assign('onload', $onload);
                $result['html'] = $this->smarty->fetch('library/goods_city_list.lbi');
            } else {
                $result['error'] = 1;
            }

            $result['warehouse_id'] = $warehouse_id;
            $result['area_id'] = $area_id;

            if ($city_id > 0) {
                $result['city_id'] = $city_id;
            } else {
                $result['city_id'] = $city_list[0]['region_id'] ?? 0;
            }

            return make_json_result($result);
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区赠送消费积分数 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_give_integral') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $give_integral = floatval(request()->get('val', ''));

            $data = ['give_integral' => $give_integral];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);

            $goods = WarehouseAreaGoods::select('a_id', 'user_id', 'region_price', 'region_promote_price')->where('a_id', $a_id);
            $goods = BaseRepository::getToArrayFirst($goods);

            if ($goods['user_id']) {
                if ($goods['region_promote_price']) {
                    if ($goods['region_promote_price'] < $goods['region_price']) {
                        $shop_price = $goods['region_promote_price'];
                    } else {
                        $shop_price = $goods['region_price'];
                    }
                } else {
                    $shop_price = $goods['region_price'];
                }

                $grade_rank = get_seller_grade_rank($goods['user_id']);
                $give = floor($shop_price * $grade_rank['give_integral']);

                if ($give_integral > $give) {
                    return make_json_error(sprintf(__('admin::goods.goods_give_integral'), $give));
                }
            }

            if ($res) {
                clear_cache_files();
                return make_json_result($give_integral);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区赠送等级积分数 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_rank_integral') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $rank_integral = floatval(request()->get('val', ''));

            $data = ['rank_integral' => $rank_integral];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);

            $goods = WarehouseAreaGoods::select('a_id', 'user_id', 'region_price', 'region_promote_price')->where('a_id', $a_id);
            $goods = BaseRepository::getToArrayFirst($goods);

            if ($goods['user_id']) {
                if ($goods['region_promote_price']) {
                    if ($goods['region_promote_price'] < $goods['region_price']) {
                        $shop_price = $goods['region_promote_price'];
                    } else {
                        $shop_price = $goods['region_price'];
                    }
                } else {
                    $shop_price = $goods['region_price'];
                }

                $grade_rank = get_seller_grade_rank($goods['user_id']);
                $rank = floor($shop_price * $grade_rank['rank_integral']);

                if ($rank_integral > $rank) {
                    return make_json_error(sprintf(__('admin::goods.goods_rank_integral'), $rank));
                }
            }

            if ($res) {
                clear_cache_files();
                return make_json_result($rank_integral);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区积分购买金额 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_pay_integral') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $pay_integral = floatval(request()->get('val', ''));

            $data = ['pay_integral' => $pay_integral];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);

            $goods = WarehouseAreaGoods::select('a_id', 'user_id', 'region_price', 'region_promote_price')->where('a_id', $a_id);
            $goods = BaseRepository::getToArrayFirst($goods);

            if ($goods['user_id']) {
                if ($goods['region_promote_price']) {
                    if ($goods['region_promote_price'] < $goods['region_price']) {
                        $shop_price = $goods['region_promote_price'];
                    } else {
                        $shop_price = $goods['region_price'];
                    }
                } else {
                    $shop_price = $goods['region_price'];
                }

                $grade_rank = get_seller_grade_rank($goods['user_id']);
                $pay = floor($shop_price * $grade_rank['pay_integral']);

                if ($pay_integral > $pay) {
                    return make_json_error(sprintf(__('admin::goods.goods_pay_integral'), $pay));
                }
            }

            if ($res) {
                clear_cache_files();
                return make_json_result($pay_integral);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改商品仓库地区排序 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_region_sort') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $a_id = request()->get('id', 0);
            $region_sort = floatval(request()->get('val', ''));

            $data = ['region_sort' => $region_sort];
            $res = WarehouseAreaGoods::where('a_id', $a_id)->update($data);

            if ($res) {
                clear_cache_files();
                return make_json_result($region_sort);
            }
        }

        /*------------------------------------------------------ */
        //-- 货品列表
        /*------------------------------------------------------ */
        elseif ($act == 'product_list') {
            admin_priv('goods_manage');

            /* 是否存在商品id */
            $goods_id = request()->get('goods_id', 0);
            if (empty($goods_id)) {
                $link[] = ['href' => 'goods.php?act=list', 'text' => __('admin::goods.cannot_found_goods')];
                return sys_msg(__('admin::goods.cannot_found_goods'), 1, $link);
            }

            /* 取出商品信息 */
            $res = Goods::select('goods_sn', 'goods_name', 'goods_type', 'shop_price', 'model_attr');
            $res = $res->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($res);
            if (empty($goods)) {
                $link[] = ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')];
                return sys_msg(__('admin::goods.cannot_found_goods'), 1, $link);
            }
            $this->smarty->assign('sn', sprintf(__('admin::goods.good_goods_sn'), $goods['goods_sn']));
            $this->smarty->assign('price', sprintf(__('admin::goods.good_shop_price'), $goods['shop_price']));
            $this->smarty->assign('goods_name', sprintf(__('admin::goods.products_title'), $goods['goods_name']));
            $this->smarty->assign('goods_sn', sprintf(__('admin::goods.products_title_2'), $goods['goods_sn']));
            $this->smarty->assign('model_attr', $goods['model_attr']);


            /* 获取商品规格列表 */
            $attribute = get_goods_specifications_list($goods_id);
            if (empty($attribute)) {
                $link[] = ['href' => 'goods.php?act=edit&goods_id=' . $goods_id, 'text' => __('admin::goods.edit_goods')];
                return sys_msg(__('admin::goods.not_exist_goods_attr'), 1, $link);
            }
            foreach ($attribute as $attribute_value) {
                //转换成数组
                $_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
                $_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
                $_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
            }
            $attribute_count = count($_attribute);

            $this->smarty->assign('attribute_count', $attribute_count);
            $this->smarty->assign('attribute_count_3', ($attribute_count + 3));
            $this->smarty->assign('attribute', $_attribute);
            $this->smarty->assign('product_sn', $goods['goods_sn'] . '_');
            $this->smarty->assign('product_number', config('shop.default_storage'));

            /* 取商品的货品 */
            $product = product_list($goods_id, '');

            $this->smarty->assign('ur_here', __('admin::common.18_product_list'));
            $this->smarty->assign('action_link', ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')]);
            $this->smarty->assign('product_list', $product['product']);
            $this->smarty->assign('product_null', empty($product['product']) ? 0 : 1);
            $this->smarty->assign('use_storage', empty(config('shop.use_storage')) ? 0 : 1);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('filter', $product['filter']);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('product_php', 'goods.php');
            $this->smarty->assign('batch_php', 'goods_produts_batch.php');//默认属性批量设置 bylu

            /* 显示商品列表页面 */


            return $this->smarty->display('product_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 货品排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'product_query') {
            /* 是否存在商品id */
            if (request()->get('goods_id', 0)) {
                $goods_id = request()->get('goods_id', 0);
            } else {
                return make_json_error(__('admin::common.sys.wrong') . __('admin::goods.cannot_found_goods'));
            }

            /* 取出商品信息 */
            $res = Goods::select('goods_sn', 'goods_name', 'goods_type', 'shop_price');
            $res = $res->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($res);
            if (empty($goods)) {
                return make_json_error(__('admin::common.sys.wrong') . __('admin::goods.cannot_found_goods'));
            }
            $this->smarty->assign('sn', sprintf(__('admin::goods.good_goods_sn'), $goods['goods_sn']));
            $this->smarty->assign('price', sprintf(__('admin::goods.good_shop_price'), $goods['shop_price']));
            $this->smarty->assign('goods_name', sprintf(__('admin::goods.products_title'), $goods['goods_name']));
            $this->smarty->assign('goods_sn', sprintf(__('admin::goods.products_title_2'), $goods['goods_sn']));


            /* 获取商品规格列表 */
            $attribute = get_goods_specifications_list($goods_id);
            if (empty($attribute)) {
                return make_json_error(__('admin::common.sys.wrong') . __('admin::goods.cannot_found_goods'));
            }
            foreach ($attribute as $attribute_value) {
                //转换成数组
                $_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
                $_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
                $_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
            }
            $attribute_count = count($_attribute);

            $this->smarty->assign('attribute_count', $attribute_count);
            $this->smarty->assign('attribute', $_attribute);
            $this->smarty->assign('attribute_count_3', ($attribute_count + 10));
            $this->smarty->assign('product_sn', $goods['goods_sn'] . '_');
            $this->smarty->assign('product_number', config('shop.default_storage'));

            /* 取商品的货品 */
            $product = product_list($goods_id, '');

            $this->smarty->assign('ur_here', __('admin::common.18_product_list'));
            $this->smarty->assign('action_link', ['href' => 'goods.php?act=list', 'text' => __('admin::common.01_goods_list')]);
            $this->smarty->assign('product_list', $product['product']);
            $this->smarty->assign('use_storage', empty(config('shop.use_storage')) ? 0 : 1);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('filter', $product['filter']);

            $this->smarty->assign('product_php', 'goods.php');

            /* 排序标记 */
            $sort_flag = sort_flag($product['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('product_info.dwt'),
                '',
                ['filter' => $product['filter'], 'page_count' => $product['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 货品删除
        /*------------------------------------------------------ */
        elseif ($act == 'product_remove') {
            /* 检查权限 */
            $check_auth = check_authz_json('remove_back');
            if ($check_auth !== true) {
                return $check_auth;
            }

            //ecmoban模板堂 --zhuo satrt
            $id_val = request()->get('id');
            $id_val = explode(',', $id_val);
            $product_id = intval($id_val[0]);
            $warehouse_id = intval($id_val[1]);
            //ecmoban模板堂 --zhuo end

            /* 是否存在商品id */
            if (empty($product_id)) {
                return make_json_error(__('admin::goods.product_id_null'));
            } else {
                $product_id = intval($product_id);
            }

            /* 货品库存 */
            $product = get_product_info($product_id, 'product_number, goods_id');

            /* 删除货品 */
            $result = Products::where('product_id', $product_id)->delete();
            if ($result) {
                $url = 'goods.php?act=product_query&' . str_replace('act=product_remove', '', request()->server('QUERY_STRING'));

                return dsc_header("Location: $url\n");
            }
        }

        /*------------------------------------------------------ */
        //-- 修改货品市场价
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_market_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');
            $market_price = floatval(request()->get('val', ''));
            $goods_model = request()->get('goods_model', 0);
            $changelog = request()->get('changelog', 0);
            $res = Products::where('product_id', $product_id);
            if ($changelog == 1) {
                $res = ProductsChangelog::where('product_id', $product_id);
            } else {
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::where('product_id', $product_id);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::where('product_id', $product_id);
                }
            }
            /* 修改 */
            $data = ['product_market_price' => $market_price];
            $result = $res->update($data);

            if ($result) {
                clear_cache_files();
                return make_json_result($market_price);
            }
        }
        /*------------------------------------------------------ */
        //-- 批量修改货品数据
        /*------------------------------------------------------ */
        elseif ($act == 'synchronization_attr') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['content' => '', 'error' => 0];

            $goods_id = request()->get('goods_id', 0);
            $goods_model = request()->get('model', 0);
            $warehouse_id = request()->get('warehouse_id', 0);
            $area_id = request()->get('area_id', 0);
            $area_city = request()->get('area_city', 0);
            $changelog = request()->get('changelog', 0);
            $field = request()->get('field', '');
            $val = request()->get('val', 0);
            $obj_arr = ['product_market_price', 'product_price', 'product_promote_price'];

            if (in_array($field, $obj_arr)) {
                $val = floatval($val);
            }

            if ($field) {
                $res = ProductsChangelog::where('goods_id', $goods_id);
                if (empty($goods_id)) {
                    $res = $res->where('admin_id', $admin_id);
                }

                if ($goods_model == 1) {
                    $res = $res->where('warehouse_id', $warehouse_id);
                } elseif ($goods_model == 2) {
                    $res = $res->where('area_id', $area_id);
                    if (config('shop.area_pricetype')) {
                        $res = $res->where('city_id', $area_city);
                    }
                }

                /* 修改临时表 */
                $data = [$field => $val];
                $res->update($data);

                $res = Products::where('goods_id', $goods_id);
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::where('goods_id', $goods_id);
                    $res = $res->where('warehouse_id', $warehouse_id);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::where('goods_id', $goods_id);
                    $res = $res->where('area_id', $area_id);
                    if (config('shop.area_pricetype')) {
                        $res = $res->where('city_id', $area_city);
                    }
                }
                if (empty($goods_id)) {
                    $res = $res->where('admin_id', $admin_id);
                }

                /* 修改 */
                $res = $res->update($data);
                if ($res > 0) {
                    clear_cache_files();
                }
            } else {
                $result['error'] = 1;
            }
            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 修改货品销售价格
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');
            $product_price = floatval(request()->get('val', ''));
            $goods_model = request()->get('goods_model', 0);
            $changelog = request()->get('changelog', 0);

            if ($changelog == 1) {
                $table = "products_changelog";
                $res = ProductsChangelog::whereRaw(1);
            } else {
                if ($goods_model == 1) {
                    $table = "products_warehouse";
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $table = "products_area";
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $table = "products";
                    $res = Products::whereRaw(1);
                }
            }

            if (config('shop.goods_attr_price') == 1 && $changelog == 0) {
                $goods_id = $res->where('product_id', $product_id)->value('goods_id');
                $goods_id = $goods_id ? $goods_id : 0;

                //验证商品是否参与活动
//                $is_promotion = is_promotion($goods_id);
//                // 验证返回提示
//                if ($is_promotion) {
//                    $return_prompt = is_promotion_error($is_promotion);
//                    return $return_prompt;
//                }


                $goods_other = [
                    'product_table' => $table,
                    'product_price' => $product_price,
                ];

                Goods::where('goods_id', $goods_id)
                    ->where('product_id', $product_id)
                    ->where('product_table', $table)
                    ->update($goods_other);
            }

            /* 修改 */
            $data = ['product_price' => $product_price];
            $result = $res->where('product_id', $product_id)->update($data);
            if ($result) {
                clear_cache_files();
                return make_json_result($product_price);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改货品成本价格
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_cost_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');
            $product_cost_price = floatval(request()->get('val', ''));
            $goods_model = request()->get('goods_model', 0);

            $changelog = request()->get('changelog', 0);

            if ($changelog == 1) {
                $table = "products_changelog";
                $res = ProductsChangelog::whereRaw(1);
            } else {
                $table = "products";
                $res = Products::whereRaw(1);
            }

            if (config('shop.goods_attr_price') == 1 && $changelog == 0) {
                $goods_id = $res->where('product_id', $product_id)->value('goods_id');
                $goods_id = $goods_id ? $goods_id : 0;

                //验证商品是否参与活动
//                $is_promotion = is_promotion($goods_id);
//                // 验证返回提示
//                if ($is_promotion) {
//                    $return_prompt = is_promotion_error($is_promotion);
//                    return $return_prompt;
//                }

                $goods_other = [
                    'product_table' => $table,
                    'cost_price' => $product_cost_price,
                ];

                Goods::where('goods_id', $goods_id)
                    ->where('product_id', $product_id)
                    ->where('product_table', $table)
                    ->update($goods_other);
            }

            /* 修改 */
            $data = ['product_cost_price' => $product_cost_price];
            $result = $res->where('product_id', $product_id)->update($data);
            if ($result) {
                clear_cache_files();
            }

            return make_json_result($product_cost_price);
        }

        /*------------------------------------------------------ */
        //-- 修改货品促销价格
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_promote_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');
            $promote_price = floatval(request()->get('val', ''));
            $goods_model = request()->get('goods_model', 0);
            $changelog = request()->get('changelog', 0);

            if ($changelog == 1) {
                $table = "products_changelog";
                $res = ProductsChangelog::whereRaw(1);
            } else {
                if ($goods_model == 1) {
                    $table = "products_warehouse";
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $table = "products_area";
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $table = "products";
                    $res = Products::whereRaw(1);
                }
            }

            if (config('shop.goods_attr_price') == 1 && $changelog == 0) {
                $goods_id = $res->where('product_id', $product_id)->value('goods_id');
                $goods_id = $goods_id ? $goods_id : 0;

                $goods_other = [
                    'product_table' => $table,
                    'product_promote_price' => $promote_price,
                ];

                Goods::where('goods_id', $goods_id)
                    ->where('product_id', $product_id)
                    ->where('product_table', $table)
                    ->update($goods_other);
            }

            /* 修改 */
            $data = ['product_promote_price' => $promote_price];
            $result = $res->where('product_id', $product_id)->update($data);
            if ($result) {
                clear_cache_files();
                return make_json_result($promote_price);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改货品库存
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_number') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');
            $product_number = floatval(request()->get('val', ''));
            $goods_model = request()->get('goods_model', 0);
            $changelog = request()->get('changelog', 0);

            if ($product_id && $changelog == 0) {
                if ($goods_model == 1) {
                    $filed = ", warehouse_id";
                } elseif ($goods_model == 2) {
                    $filed = ", area_id";
                } else {
                    $filed = "";
                }

                /* 货品库存 */
                $product = get_product_info($product_id, 'product_number, goods_id' . $filed, $goods_model);

                //验证商品是否参与活动
//                $is_promotion = is_promotion($product['goods_id']);
//                // 验证返回提示
//                if ($is_promotion) {
//                    $return_prompt = is_promotion_error($is_promotion);
//                    return $return_prompt;
//                }

                if ($product['product_number'] != $product_number) {
                    $add_number = true;
                    if ($product['product_number'] > $product_number) {
                        $number = $product['product_number'] - $product_number;

                        if ($number == 0) {
                            $add_number = false;
                        }

                        $number = "- " . $number;
                        $log_use_storage = 10;
                    } else {
                        $number = $product_number - $product['product_number'];

                        if ($number == 0) {
                            $add_number = false;
                        }

                        $number = "+ " . $number;
                        $log_use_storage = 11;
                    }

                    if ($add_number == true) {
                        //库存日志
                        $logs_other = [
                            'goods_id' => $product['goods_id'],
                            'order_id' => 0,
                            'use_storage' => $log_use_storage,
                            'admin_id' => session('admin_id'),
                            'number' => $number,
                            'model_inventory' => $goods_model,
                            'model_attr' => $goods_model,
                            'product_id' => $product_id,
                            'warehouse_id' => $product['warehouse_id'] ?? 0,
                            'area_id' => $product['area_id'] ?? 0,
                            'city_id' => $product['city_id'] ?? 0,
                            'add_time' => TimeRepository::getGmTime()
                        ];

                        GoodsInventoryLogs::insert($logs_other);
                    }
                }
            }

            if ($changelog == 1) {
                $res = ProductsChangelog::whereRaw(1);
            } else {
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $res = Products::whereRaw(1);
                }
            }

            /* 修改货品库存 */
            $data = ['product_number' => $product_number];
            $result = $res->where('product_id', $product_id)->update($data);

            if ($result) {
                clear_cache_files();
                return make_json_result($product_number);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改货品预警库存
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_warn_number') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');
            $product_warn_number = floatval(request()->get('val', ''));
            $goods_model = request()->get('goods_model', 0);
            $changelog = request()->get('changelog', 0);

            if ($changelog == 1) {
                $res = ProductsChangelog::whereRaw(1);
            } else {
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $res = Products::whereRaw(1);
                }
            }

            /* 修改货品库存 */
            $data = ['product_warn_number' => $product_warn_number];
            $result = $res->where('product_id', $product_id)->update($data);

            if ($result) {
                clear_cache_files();
                return make_json_result($product_warn_number);
            }
        }
        /* ------------------------------------------------------ */
        //-- 修改货品重量
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_sku_weight') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');

            $sku_weight = json_str_iconv(trim(request()->get('val', ''))) ?? 0.000;
            $goods_model = request()->get('goods_model', 0);
            $warehouse_id = request()->get('warehouse_id', 0);
            $area_id = request()->get('area_id', 0);
            $area_city = request()->get('area_city', 0);

            $changelog = request()->get('changelog', 0);

            if ($changelog == 1) {
                $res = ProductsChangelog::whereRaw(1);
            } else {
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $res = Products::whereRaw(1);
                }
            }

            /* 修改 */
            $data = ['sku_weight' => $sku_weight];
            $result = $res->where('product_id', $product_id)->update($data);

            if ($result) {
                clear_cache_files();
                return make_json_result($sku_weight);
            }
        }
        /* ------------------------------------------------------ */
        //-- 修改货品号
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_product_sn') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');

            $product_sn = json_str_iconv(trim(request()->get('val', '')));
            $product_sn = (__('admin::common.n_a') == $product_sn) ? '' : $product_sn;
            $goods_model = request()->get('goods_model', 0);
            $warehouse_id = request()->get('warehouse_id', 0);
            $area_id = request()->get('area_id', 0);
            $area_city = request()->get('area_city', 0);

            if (check_product_sn_exist($product_sn, $product_id, $adminru['ru_id'], $goods_model, $warehouse_id, $area_id, $area_city)) {
                return make_json_error(__('admin::common.sys.wrong') . __('admin::goods.exist_same_product_sn'));
            }

            $changelog = request()->get('changelog', 0);

            if ($changelog == 1) {
                $res = ProductsChangelog::whereRaw(1);
            } else {
                if ($goods_model == 1) {
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods_model == 2) {
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $res = Products::whereRaw(1);
                }
            }

            /* 修改 */
            $data = ['product_sn' => $product_sn];
            $result = $res->where('product_id', $product_id)->update($data);

            if ($result) {
                clear_cache_files();
                return make_json_result($product_sn);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改货品条形码
        /*------------------------------------------------------ */
        elseif ($act == 'edit_product_bar_code') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $product_id = request()->get('id');

            $bar_code = json_str_iconv(trim(request()->get('val', '')));
            $bar_code = (__('admin::common.n_a') == $bar_code) ? '' : $bar_code;
            $goods_model = request()->get('goods_model', 0);
            $warehouse_id = request()->get('warehouse_id', 0);
            $area_id = request()->get('area_id', 0);
            $area_city = request()->get('area_city', 0);

            if (!empty($bar_code)) {
                if (check_product_bar_code_exist($bar_code, $product_id, $adminru['ru_id'], $goods_model, $warehouse_id, $area_id, $area_city)) {
                    return make_json_error(__('admin::common.sys.wrong') . __('admin::goods.exist_same_bar_code'));
                }

                $changelog = request()->get('changelog', 0);

                if ($changelog == 1) {
                    $res = ProductsChangelog::whereRaw(1);
                } else {
                    if ($goods_model == 1) {
                        $res = ProductsWarehouse::whereRaw(1);
                    } elseif ($goods_model == 2) {
                        $res = ProductsArea::whereRaw(1);
                    } else {
                        $res = Products::whereRaw(1);
                    }
                }

                /* 修改 */
                $data = ['bar_code' => $bar_code];
                $result = $res->where('product_id', $product_id)->update($data);

                if ($result) {
                    clear_cache_files();
                    return make_json_result($bar_code);
                }
            } else {
                clear_cache_files();
                return make_json_result('N/A');
            }
        }

        /*------------------------------------------------------ */
        //-- 修改属性排序
        /*------------------------------------------------------ */
        elseif ($act == 'edit_attr_sort') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_attr_id = request()->get('id');
            $attr_sort = intval(request()->get('val', ''));

            /* 修改 */
            $data = ['attr_sort' => $attr_sort];
            $result = GoodsAttr::where('goods_attr_id', $goods_attr_id)->update($data);

            if ($result) {
                clear_cache_files();
                return make_json_result($attr_sort);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改属性价格
        /*------------------------------------------------------ */
        elseif ($act == 'edit_attr_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $goods_attr_id = request()->get('id');
            $attr_price = floatval(request()->get('val', ''));

            /* 修改 */
            $data = ['attr_price' => $attr_price];
            $result = GoodsAttr::where('goods_attr_id', $goods_attr_id)->update($data);
            if ($result) {
                clear_cache_files();
                return make_json_result($attr_price);
            }
        }

        /*------------------------------------------------------ */
        //-- 单个添加商品仓库 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'addWarehouse') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];
            $ware_name = request()->get('ware_name', '');
            $ware_number = request()->get('ware_number', 0);
            $ware_price = request()->get('ware_price', 0);
            $ware_price = floatval($ware_price);
            $ware_promote_price = request()->get('ware_promote_price', 0);
            $ware_promote_price = floatval($ware_promote_price);
            $give_integral = request()->get('give_integral', 0);
            $rank_integral = request()->get('rank_integral', 0);
            $pay_integral = request()->get('pay_integral', 0);
            $goods_id = request()->get('goods_id', 0);
            $user_id = request()->get('user_id', 0);

            if (empty($ware_name)) {
                $result['error'] = '1';
                $result['massege'] = __('admin::goods.select_warehouse');
            } else {
                $w_id = WarehouseGoods::where('goods_id', $goods_id)
                    ->where('region_id', $ware_name)
                    ->where('user_id', $user_id)
                    ->value('w_id');
                $w_id = $w_id ? $w_id : 0;

                $add_time = TimeRepository::getGmTime();
                if ($w_id > 0) {
                    $result['error'] = '1';
                    $result['massege'] = __('admin::goods.warehouse_goods_stock_exi');
                } else {
                    if ($ware_number == 0) {
                        $result['error'] = '1';
                        $result['massege'] = __('admin::goods.warehouse_stock_not_0');
                    } elseif ($ware_price == 0) {
                        $result['error'] = '1';
                        $result['massege'] = __('admin::goods.warehouse_price_not_0');
                    } else {
                        $goodsInfo = get_admin_goods_info($goods_id);
                        $goodsInfo['user_id'] = !empty($goodsInfo['user_id']) ? $goodsInfo['user_id'] : $adminru['ru_id'];

                        //库存日志
                        $number = "+ " . $ware_number;
                        $use_storage = 13;

                        if ($ware_number != 0) {
                            $logs_other = [
                                'goods_id' => $goods_id,
                                'order_id' => 0,
                                'use_storage' => $use_storage,
                                'admin_id' => session('admin_id'),
                                'number' => $number,
                                'model_inventory' => $goodsInfo['model_inventory'],
                                'model_attr' => $goodsInfo['model_attr'],
                                'product_id' => 0,
                                'warehouse_id' => $ware_name,
                                'area_id' => 0,
                                'add_time' => $add_time
                            ];

                            GoodsInventoryLogs::insert($logs_other);
                        }

                        $data = [
                            'goods_id' => $goods_id,
                            'region_id' => $ware_name,
                            'region_number' => $ware_number,
                            'warehouse_price' => $ware_price,
                            'warehouse_promote_price' => $ware_promote_price,
                            'give_integral' => $give_integral,
                            'rank_integral' => $rank_integral,
                            'pay_integral' => $pay_integral,
                            'user_id' => $goodsInfo['user_id'],
                            'add_time' => $add_time
                        ];
                        $res = WarehouseGoods::insert($data);

                        if ($res > 0) {
                            $result['error'] = '2';
                            $get_warehouse_goods_list = get_warehouse_goods_list($goods_id);
                            $warehouse_id = '';
                            if (!empty($get_warehouse_goods_list)) {
                                foreach ($get_warehouse_goods_list as $k => $v) {
                                    $warehouse_id .= $v['w_id'] . ",";
                                }
                            }
                            $warehouse_id = substr($warehouse_id, 0, strlen($warehouse_id) - 1);
                            $this->smarty->assign("warehouse_id", $warehouse_id);
                            $this->smarty->assign("warehouse_goods_list", $get_warehouse_goods_list);
                            $result['content'] = $this->smarty->fetch('library/goods_warehouse.dwt');
                        }
                    }
                }
            }
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 批量添加商品仓库 ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'addBatchWarehouse') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];

            $ware_name = request()->get('ware_name', '');
            $ware_name = BaseRepository::getExplode($ware_name);
            $ware_number = request()->get('ware_number', '');
            $ware_number = BaseRepository::getExplode($ware_number);
            $ware_price = request()->get('ware_price', '');
            $ware_price = BaseRepository::getExplode($ware_price);
            $ware_promote_price = request()->get('ware_promote_price', '');
            $ware_promote_price = BaseRepository::getExplode($ware_promote_price);
            $goods_id = request()->get('goods_id', 0);
            if (empty($ware_name)) {
                $result['error'] = '1';
                $result['massege'] = __('admin::goods.select_warehouse');
            } else {
                $add_time = TimeRepository::getGmTime();
                $goodsInfo = get_admin_goods_info($goods_id);
                $goodsInfo['user_id'] = !empty($goodsInfo['user_id']) ? $goodsInfo['user_id'] : $adminru['ru_id'];

                for ($i = 0; $i < count($ware_name); $i++) {
                    if (!empty($ware_name[$i])) {
                        if ($ware_number[$i] == 0) {
                            $ware_number[$i] = 1;
                        }
                        $w_id = WarehouseGoods::where('goods_id', $goods_id)
                            ->where('region_id', $ware_name[$i])
                            ->value('w_id');
                        $w_id = $w_id ? $w_id : 0;

                        if ($w_id > 0) {
                            $result['error'] = '1';
                            $result['massege'] = __('admin::goods.warehouse_goods_stock_exi');
                            break;
                        } else {
                            $ware_number[$i] = intval($ware_number[$i]);
                            $ware_price[$i] = floatval($ware_price[$i]);

                            //库存日志
                            $number = "+ " . $ware_number[$i];
                            $use_storage = 13;

                            if ($ware_number[$i] > 0) {
                                $logs_other = [
                                    'goods_id' => $goods_id,
                                    'order_id' => 0,
                                    'use_storage' => $use_storage,
                                    'admin_id' => session('admin_id'),
                                    'number' => $number,
                                    'model_inventory' => 1,
                                    'model_attr' => 1,
                                    'product_id' => 0,
                                    'warehouse_id' => $ware_name[$i],
                                    'area_id' => 0,
                                    'add_time' => $add_time
                                ];
                                GoodsInventoryLogs::insert($logs_other);
                            }

                            $other = [
                                'goods_id' => $goods_id,
                                'region_id' => $ware_name[$i],
                                'region_number' => $ware_number[$i],
                                'warehouse_price' => floatval($ware_price[$i]),
                                'warehouse_promote_price' => floatval($ware_promote_price[$i]),
                                'user_id' => $goodsInfo['user_id'],
                                'add_time' => $add_time
                            ];
                            WarehouseGoods::insert($other);

                            $get_warehouse_goods_list = get_warehouse_goods_list($goods_id);
                            $warehouse_id = '';
                            if (!empty($get_warehouse_goods_list)) {
                                foreach ($get_warehouse_goods_list as $k => $v) {
                                    $warehouse_id .= $v['w_id'] . ",";
                                }
                            }

                            $warehouse_id = substr($warehouse_id, 0, strlen($warehouse_id) - 1);
                            $this->smarty->assign("warehouse_id", $warehouse_id);
                            $this->smarty->assign("warehouse_goods_list", $get_warehouse_goods_list);
                        }
                    } else {
                        $result['error'] = '1';
                        $result['massege'] = __('admin::goods.select_warehouse');
                    }
                }
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 仓库信息列表 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'goods_warehouse') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];

            $goods_id = request()->get('goods_id', 0);

            $warehouse_goods_list = get_warehouse_goods_list($goods_id);
            $this->smarty->assign('warehouse_goods_list', $warehouse_goods_list);
            $this->smarty->assign('is_list', 1);

            $result['content'] = $this->smarty->fetch('goods_warehouse.dwt');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 仓库信息列表 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'goods_region') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];

            $goods_id = request()->get('goods_id', 0);

            $warehouse_area_goods_list = get_warehouse_area_goods_list($goods_id);
            $this->smarty->assign('warehouse_area_goods_list', $warehouse_area_goods_list);
            $this->smarty->assign('is_list', 1);

            $result['content'] = $this->smarty->fetch('goods_region.dwt');
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 添加商品地区 ecmoban模板堂 --zhuo
        /* ------------------------------------------------------ */
        elseif ($act == 'addRegion') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];
            $warehouse_area_name = request()->get('warehouse_area_name', '');
            $area_name = request()->get('warehouse_area_list', '');
            $area_city = request()->get('warehouse_area_city', 0);
            $region_number = request()->get('region_number', 0);
            $region_price = request()->get('region_price', 0);
            $region_promote_price = request()->get('region_promote_price', 0);
            $give_integral = request()->get('give_integral', 0);
            $rank_integral = request()->get('rank_integral', 0);
            $pay_integral = request()->get('pay_integral', 0);
            $goods_id = request()->get('goods_id', 0);
            if (empty($area_name)) {
                $result['error'] = '1';
                $result['massege'] = __('admin::goods.select_region_alt');
            } else {
                if ($region_number == 0) {
                    $result['error'] = '1';
                    $result['massege'] = __('admin::goods.region_stock_not_0');
                } elseif ($region_price == 0) {
                    $result['error'] = '1';
                    $result['massege'] = __('admin::goods.region_price_not_0');
                } else {
                    $add_time = TimeRepository::getGmTime();

                    $res = WarehouseAreaGoods::whereRaw(1);
                    if (config('shop.area_pricetype') == 1) {
                        $res = $res->where('city_id', $area_city);
                    }

                    $a_id = $res->where('goods_id', $goods_id)
                        ->where('region_id', $area_name)
                        ->value('a_id');
                    $a_id = $a_id ? $a_id : 0;

                    if ($a_id > 0) {
                        $result['error'] = '1';
                        $result['massege'] = __('admin::goods.region_goods_stock_exi');
                    } else {
                        $goodsInfo = get_admin_goods_info($goods_id);
                        $goodsInfo['user_id'] = !empty($goodsInfo['user_id']) ? $goodsInfo['user_id'] : $adminru['ru_id'];

                        //库存日志
                        $number = "+ " . $region_number;
                        $use_storage = 13;

                        if ($region_number > 0) {
                            $logs_other = [
                                'goods_id' => $goods_id,
                                'order_id' => 0,
                                'use_storage' => $use_storage,
                                'admin_id' => session('admin_id'),
                                'number' => $number,
                                'model_inventory' => 2,
                                'model_attr' => 2,
                                'product_id' => 0,
                                'warehouse_id' => 0,
                                'area_id' => $area_name,
                                'add_time' => $add_time
                            ];

                            if (config('shop.area_pricetype') == 1) {
                                $area_other['city_id'] = $area_city;
                            }

                            GoodsInventoryLogs::insert($logs_other);
                        }

                        $other = [
                            'goods_id' => $goods_id,
                            'region_id' => $area_name,
                            'region_number' => $region_number,
                            'region_price' => floatval($region_price),
                            'region_promote_price' => floatval($region_promote_price),
                            'give_integral' => $give_integral,
                            'rank_integral' => $rank_integral,
                            'pay_integral' => $pay_integral,
                            'user_id' => $goodsInfo['user_id'],
                            'add_time' => $add_time
                        ];

                        if (config('shop.area_pricetype') == 1) {
                            $area_other['city_id'] = $area_city;
                        }

                        WarehouseAreaGoods::insert($other);

                        $result['error'] = '2';
                        $warehouse_area_goods_list = get_warehouse_area_goods_list($goods_id);
                        $warehouse_id = '';
                        if (!empty($warehouse_area_goods_list)) {
                            foreach ($warehouse_area_goods_list as $k => $v) {
                                $warehouse_id .= $v['a_id'] . ",";
                            }
                        }
                        $warehouse_area_id = substr($warehouse_id, 0, strlen($warehouse_id) - 1);
                        $this->smarty->assign("warehouse_area_id", $warehouse_area_id);
                        $this->smarty->assign("warehouse_area_goods_list", $warehouse_area_goods_list);

                        $this->smarty->assign("goods", $goodsInfo);

                        $result['content'] = $this->smarty->fetch('library/goods_region.dwt');
                    }
                }
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 批量添加商品地区 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'addBatchRegion') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];
            $warehouse_area_name = request()->get('warehouse_area_name', '');
            $warehouse_area_name = BaseRepository::getExplode($warehouse_area_name);
            $area_name = request()->get('warehouse_area_list', '');
            $area_name = BaseRepository::getExplode($area_name);
            $area_city = request()->get('warehouse_area_city', '');
            $area_city = BaseRepository::getExplode($area_city);
            $ware_number = request()->get('ware_number', '');
            $ware_number = BaseRepository::getExplode($ware_number);
            $region_number = request()->get('region_number', '');
            $region_number = BaseRepository::getExplode($region_number);
            $region_price = request()->get('region_price', '');
            $region_price = BaseRepository::getExplode($region_price);
            $region_promote_price = request()->get('region_promote_price', '');
            $region_promote_price = BaseRepository::getExplode($region_promote_price);
            $goods_id = request()->get('goods_id', 0);
            $ware_price = request()->get('ware_price', '');
            $ware_price = BaseRepository::getExplode($ware_price);

            if (config('shop.area_pricetype') == 1) {
                $area_pricetype = $area_city;
            } else {
                $area_pricetype = $area_name;
            }

            if (empty($area_name)) {
                $result['error'] = '1';
                $result['massege'] = __('admin::goods.select_region_alt');
            } else {
                if (empty($region_number)) {
                    $result['error'] = '1';
                    $result['massege'] = __('admin::goods.region_stock_not_0');
                } elseif (empty($region_price)) {
                    $result['error'] = '1';
                    $result['massege'] = __('admin::goods.region_price_not_0');
                } else {
                    $add_time = TimeRepository::getGmTime();
                    $goodsInfo = get_admin_goods_info($goods_id);
                    $goodsInfo['user_id'] = !empty($goodsInfo['user_id']) ? $goodsInfo['user_id'] : $adminru['ru_id'];

                    for ($i = 0; $i < count($area_name); $i++) {
                        if (!empty($area_pricetype[$i])) {
                            $res = WarehouseAreaGoods::whereRaw(1);
                            if (config('shop.area_pricetype') == 1) {
                                $area_city[$i] = $area_city[$i] ?? 0;
                                $res = $res->where('city_id', $area_city[$i]);
                            }

                            $a_id = $res->where('goods_id', $goods_id)
                                ->where('region_id', $area_name[$i])
                                ->value('a_id');
                            $a_id = $a_id ? $a_id : 0;

                            if ($a_id > 0) {
                                $result['error'] = '1';
                                $result['massege'] = __('admin::goods.region_goods_stock_exi');
                                break;
                            } else {
                                $ware_number[$i] = isset($ware_number[$i]) && !empty($ware_number[$i]) ? intval($ware_number[$i]) : 0;
                                $ware_price[$i] = isset($ware_price[$i]) && !empty($ware_price[$i]) ? floatval($ware_price[$i]) : 0;
                                $region_promote_price[$i] = isset($region_promote_price[$i]) && !empty($region_promote_price[$i]) ? floatval($region_promote_price[$i]) : 0;

                                //库存日志
                                $number = "+ " . $ware_number[$i];
                                $use_storage = 13;

                                if ($ware_number[$i] > 0) {
                                    $logs_other = [
                                        'goods_id' => $goods_id,
                                        'order_id' => 0,
                                        'use_storage' => $use_storage,
                                        'admin_id' => session('admin_id'),
                                        'number' => $number,
                                        'model_inventory' => $goodsInfo['model_inventory'],
                                        'model_attr' => $goodsInfo['model_attr'],
                                        'product_id' => 0,
                                        'warehouse_id' => 0,
                                        'area_id' => $area_name[$i],
                                        'add_time' => $add_time
                                    ];

                                    if (config('shop.area_pricetype') == 1) {
                                        $logs_other['city_id'] = $area_city[$i];
                                    }

                                    GoodsInventoryLogs::insert($logs_other);
                                }

                                $area_other = [
                                    'goods_id' => $goods_id,
                                    'region_id' => $area_name[$i],
                                    'region_number' => $region_number[$i],
                                    'region_price' => $region_price[$i],
                                    'region_promote_price' => $region_promote_price[$i],
                                    'user_id' => $goodsInfo['user_id'],
                                    'add_time' => $add_time
                                ];

                                if (config('shop.area_pricetype') == 1) {
                                    $area_other['city_id'] = $area_city[$i];
                                }

                                WarehouseAreaGoods::insert($area_other);

                                $get_warehouse_area_goods_list = get_warehouse_area_goods_list($goods_id);
                                $warehouse_id = '';
                                if (!empty($get_warehouse_area_goods_list)) {
                                    foreach ($get_warehouse_area_goods_list as $k => $v) {
                                        $warehouse_id .= $v['a_id'] . ",";
                                    }
                                }
                                $warehouse_area_id = substr($warehouse_id, 0, strlen($warehouse_id) - 1);
                                $this->smarty->assign("warehouse_area_id", $warehouse_area_id);
                                $this->smarty->assign("warehouse_area_goods_list", $get_warehouse_area_goods_list);

                                $this->smarty->assign("goods", $goodsInfo);
                            }
                        } else {
                            $result['error'] = '1';
                            $result['massege'] = __('admin::goods.select_region_alt');
                            break;
                        }
                    }
                }
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 上传商品相册 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'addImg') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];
            $goods_id = request()->get('goods_id_img', '');
            $img_desc = request()->get('img_desc', '');
            $img_file = request()->get('img_file', '');
            $php_maxsize = ini_get('upload_max_filesize');
            $htm_maxsize = '2M';

            if ($_FILES['img_url']) {
                foreach ($_FILES['img_url']['error'] as $key => $value) {
                    if ($value == 0) {
                        if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
                            $result['error'] = '1';
                            $result['massege'] = sprintf(__('admin::goods.invalid_img_url'), $key + 1);
                        } else {
                            $goods_pre = 1;
                        }
                    } elseif ($value == 1) {
                        $result['error'] = '1';
                        $result['massege'] = sprintf(__('admin::goods.img_url_too_big'), $key + 1, $php_maxsize);
                    } elseif ($_FILES['img_url']['error'] == 2) {
                        $result['error'] = '1';
                        $result['massege'] = sprintf(__('admin::goods.img_url_too_big'), $key + 1, $htm_maxsize);
                    }
                }
            }

            $this->goodsManageService->handleGalleryImageAdd($goods_id, $_FILES['img_url'], $img_desc, $img_file, '', '', 'ajax');

            clear_cache_files();
            $res = GoodsGallery::whereRaw(1);
            if ($goods_id > 0) {
                /* 图片列表 */
                $res = $res->where('goods_id', $goods_id)->orderBy('img_desc', 'ASC');
            } else {
                $img_id = session('thumb_img_id' . session('admin_id'));
                if ($img_id) {
                    $img_id = BaseRepository::getExplode($img_id);
                    $res = $res->whereIn('img_id', $img_id);
                }
                $res = $res->where('goods_id', '');
            }
            $img_list = BaseRepository::getToArrayGet($res);
            /* 格式化相册图片路径 */
            foreach ($img_list as $key => $gallery_img) {
                $gallery_img[$key]['thumb_url'] = $this->dscRepository->getImagePath((empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']));
            }

            $goods['goods_id'] = $goods_id;
            $this->smarty->assign('img_list', $img_list);
            $img_desc = [];
            foreach ($img_list as $k => $v) {
                $img_desc[] = $v['img_desc'];
            }
            $img_default = min($img_desc);

            $min_img_id = GoodsGallery::where('goods_id', $goods_id)
                ->where('img_desc', $img_default)
                ->orderBy('img_desc')
                ->value('img_id');
            $min_img_id = $min_img_id ? $min_img_id : 0;

            $this->smarty->assign('min_img_id', $min_img_id);
            $this->smarty->assign('goods', $goods);
            $result['error'] = '2';
            $result['content'] = $this->smarty->fetch('library/gallery_img.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 修改默认相册 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'img_default') {
            $result = ['content' => '', 'error' => 0, 'massege' => '', 'img_id' => ''];
            $img_id = request()->get('img_id', 0);

            $admin_id = get_admin_id();

            if ($img_id > 0) {
                $res = GoodsGallery::select('goods_id', 'img_desc')->where('img_id', $img_id);
                $goods_gallery = BaseRepository::getToArrayFirst($res);

                $goods_id = $goods_gallery['goods_id'];
                /*获取最小的排序*/
                $least_img_desc = GoodsGallery::where('goods_id', $goods_id)->min('img_desc');
                $least_img_desc = $least_img_desc ? $least_img_desc : 1;
                /*排序互换*/
                $data = ['img_desc' => $goods_gallery['img_desc']];
                GoodsGallery::where('img_desc', $least_img_desc)
                    ->where('goods_id', $goods_id)
                    ->update($data);

                $data = ['img_desc' => $least_img_desc];
                $res = GoodsGallery::where('img_id', $img_id)->update($data);
                if (isset($res)) {
                    $res = GoodsGallery::whereRaw(1);
                    if (empty($goods_id) && session()->has('thumb_img_id' . $admin_id) && session('thumb_img_id' . $admin_id)) {
                        $img_id_attr = BaseRepository::getExplode(session('thumb_img_id' . $admin_id));
                        $res = $res->whereIn('img_id', $img_id_attr);
                    } else {
                        $res = $res->where('goods_id', $goods_id);
                    }

                    $res = $res->orderBy('img_desc', 'ASC');
                    $img_list = BaseRepository::getToArrayGet($res);
                    /* 格式化相册图片路径 */
                    foreach ($img_list as $key => $gallery_img) {
                        $img_list[$key] = $gallery_img;
                        if (!empty($gallery_img['external_url'])) {
                            $img_list[$key]['img_url'] = $gallery_img['external_url'];
                            $img_list[$key]['thumb_url'] = $gallery_img['external_url'];
                        } else {
                            $img_list[$key]['thumb_url'] = $this->dscRepository->getImagePath((empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']));
                        }
                    }

                    $img_desc = [];

                    if (!empty($img_list)) {
                        foreach ($img_list as $k => $v) {
                            $img_desc[] = $v['img_desc'];
                        }
                    }
                    if (!empty($img_desc)) {
                        $img_default = min($img_desc);
                    }

                    $min_img_id = GoodsGallery::where('goods_id', $goods_id)
                        ->where('img_desc', $img_default)
                        ->orderBy('img_desc')
                        ->value('img_id');
                    $min_img_id = $min_img_id ? $min_img_id : 0;

                    $this->smarty->assign('min_img_id', $min_img_id);
                    $this->smarty->assign('img_list', $img_list);
                    $result['error'] = 1;
                    $result['content'] = $this->smarty->fetch('library/gallery_img.lbi');
                } else {
                    $result['error'] = 2;
                    $result['massege'] = __('admin::goods.modify_failure');
                }
            }
            return response()->json($result);
        } // mobile商品详情 添加图片 qin
        elseif ($act == 'gallery_album_dialog') {
            $result = ['error' => 0, 'message' => '', 'log_type' => '', 'content' => ''];
            $content = request()->get('content', '');
            // 获取相册信息 qin
            $res = GalleryAlbum::where('ru_id', 0)->orderBy('sort_order');
            $gallery_album_list = BaseRepository::getToArrayGet($res);

            $this->smarty->assign('gallery_album_list', $gallery_album_list);

            $log_type = request()->get('log_type', 'image');
            $result['log_type'] = $log_type;
            $this->smarty->assign('log_type', $log_type);

            $res = PicAlbum::where('ru_id', 0);
            $res = BaseRepository::getToArrayGet($res);

            $this->smarty->assign('pic_album', $res);
            $this->smarty->assign('content', $content);
            $result['content'] = $this->smarty->fetch('library/album_dialog.lbi');

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 扫码�        �库 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'scan_code') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'massege' => '', 'content' => ''];

            $bar_code = request()->get('bar_code', '');
            $config = get_scan_code_config($adminru['ru_id']);
            $data = get_jsapi(['appkey' => $config['js_appkey'], 'barcode' => $bar_code]);

            if ($data['status'] != 0) {
                $result['error'] = 1;
                $result['message'] = $data['msg'];
            } else {
                //重量（用毛重）
                $goods_weight = 0;
                if (strpos($data['result']['grossweight'], __('admin::goods.unit_kg')) !== false) {
                    $goods_weight = floatval(str_replace(__('admin::goods.unit_kg'), '', $data['result']['grossweight']));
                } elseif (strpos($data['result']['grossweight'], __('admin::goods.unit_g')) !== false) {
                    $goods_weight = floatval(str_replace(__('admin::goods.unit_kg'), '', $data['result']['grossweight'])) / 1000;
                }
                //详情
                $goods_desc = "";
                if (!empty($data['result']['description'])) {
                    create_html_editor('goods_desc', trim($data['result']['description']));
                    $goods_desc = $this->smarty->get_template_vars('FCKeditor');
                }

                //初始商品信息
                $goods_info = [];
                $goods_info['goods_name'] = isset($data['result']['name']) ? trim($data['result']['name']) : ''; //名称
                $goods_info['goods_name'] .= isset($data['result']['type']) ? trim($data['result']['type']) : ''; //规格
                $goods_info['shop_price'] = isset($data['result']['price']) ? floatval($data['result']['price']) : '0.00'; //价格
                $goods_info['goods_img_url'] = isset($data['result']['pic']) ? trim($data['result']['pic']) : ''; //价格
                $goods_info['goods_desc'] = $goods_desc; //描述
                $goods_info['goods_weight'] = $goods_weight; //重量
                $goods_info['keywords'] = isset($data['result']['keyword']) ? trim($data['result']['keyword']) : ''; //关键词
                $goods_info['width'] = isset($data['result']['width']) ? trim($data['result']['width']) : ''; //宽度
                $goods_info['height'] = isset($data['result']['height']) ? trim($data['result']['height']) : ''; //高度
                $goods_info['depth'] = isset($data['result']['depth']) ? trim($data['result']['depth']) : ''; //深度
                $goods_info['origincountry'] = isset($data['result']['origincountry']) ? trim($data['result']['origincountry']) : ''; //产国
                $goods_info['originplace'] = isset($data['result']['originplace']) ? trim($data['result']['originplace']) : ''; //产地
                $goods_info['assemblycountry'] = isset($data['result']['assemblycountry']) ? trim($data['result']['assemblycountry']) : ''; //组装国
                $goods_info['barcodetype'] = isset($data['result']['barcodetype']) ? trim($data['result']['barcodetype']) : ''; //条码类型
                $goods_info['catena'] = isset($data['result']['catena']) ? trim($data['result']['catena']) : ''; //产品系列
                $goods_info['isbasicunit'] = isset($data['result']['isbasicunit']) ? intval($data['result']['isbasicunit']) : 0; //是否是基本单元
                $goods_info['packagetype'] = isset($data['result']['packagetype']) ? trim($data['result']['packagetype']) : ''; //包装类型
                $goods_info['grossweight'] = isset($data['result']['grossweight']) ? trim($data['result']['grossweight']) : ''; //毛重
                $goods_info['netweight'] = isset($data['result']['netweight']) ? trim($data['result']['netweight']) : ''; //净重
                $goods_info['netcontent'] = isset($data['result']['netcontent']) ? trim($data['result']['netcontent']) : ''; //净含量
                $goods_info['licensenum'] = isset($data['result']['licensenum']) ? trim($data['result']['licensenum']) : ''; //生产许可证
                $goods_info['healthpermitnum'] = isset($data['result']['healthpermitnum']) ? trim($data['result']['healthpermitnum']) : ''; //卫生许可证
                $result['goods_info'] = $goods_info;
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 一键同步OSS图片 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'img_file_list') {
            admin_priv('goods_manage');

            $this->smarty->assign('ur_here', __('admin::goods.lab_img_file_list'));

            $file_dir = ['common', 'qrcode', 'upload'];
            $file_list = get_img_file_list(0, $file_dir);

            $this->smarty->assign('file_list', $file_list['list']);
            $this->smarty->assign('filter', $file_list['filter']);
            $this->smarty->assign('record_count', $file_list['record_count']);
            $this->smarty->assign('page_count', $file_list['page_count']);
            $this->smarty->assign('sort_order_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('is_detection', 1);

            return $this->smarty->display('img_file_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 一键同步OSS图片 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'file_list_query') {
            $this->smarty->assign('ur_here', __('admin::goods.lab_img_file_list'));

            $file_dir = ['common', 'qrcode', 'upload'];
            $file_list = get_img_file_list(0, $file_dir);

            $this->smarty->assign('file_list', $file_list['list']);
            $this->smarty->assign('filter', $file_list['filter']);
            $this->smarty->assign('record_count', $file_list['record_count']);
            $this->smarty->assign('page_count', $file_list['page_count']);
            $this->smarty->assign('sort_order_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            $this->smarty->assign('is_detection', 1);

            return make_json_result($this->smarty->fetch('img_file_list.dwt'), '', ['filter' => $file_list['filter'], 'page_count' => $file_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 一键同步OSS图片 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'goods_img_list') {
            admin_priv('goods_manage');

            dsc_unlink(storage_public(DATA_DIR . "/sc_file/goods_images_file.php"));

            $type = request()->get('type', 0);  //类型

            $this->smarty->assign('ur_here', __('admin::goods.lab_img_file_list'));

            $file_dir = ['goods_img', 'source_img', 'thumb_img'];
            $file_list = get_goods_img_list(0, $file_dir);

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('type', $type);

            return $this->smarty->display('goods_img_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 一键同步OSS图片 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_img_list') {
            $type = request()->get('type', 0);  //类型
            $page = request()->get('page', 1);
            $page_size = request()->get('page_size', 1);

            $file_dir = ['goods_img', 'source_img', 'thumb_img'];
            $file_list = get_goods_img_list(0, $file_dir);

            if ($file_list['list']) {
                $this->dscRepository->getOssAddFile($file_list['list']);
            }

            $result['page'] = $file_list['filter']['page'] + 1;
            $result['page_size'] = $file_list['filter']['page_size'];
            $result['record_count'] = $file_list['filter']['record_count'];
            $result['page_count'] = $file_list['filter']['page_count'];

            $result['is_stop'] = 1;
            if ($page > $file_list['filter']['page_count']) {
                $result['is_stop'] = 0;

                //删除缓存文件
                dsc_unlink(storage_public(DATA_DIR . "/sc_file/goods_images_file.php"));

                /* 重新查一次 */
                $list = get_goods_img_list(0, $file_dir, 1);
                $result['record_count'] = $list['filter']['record_count'];
            } else {
                $result['filter_page'] = $file_list['filter']['page'];
            }

            $result['type'] = $type;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 查看日志 by liu
        /*------------------------------------------------------ */
        elseif ($act == 'view_log') {
            /* 权限的判断 */
            admin_priv('goods_manage');

            $this->smarty->assign('ur_here', __('admin::goods.view_log'));
            //$this->smarty->assign('ip_list', $ip_list);
            $this->smarty->assign('full_page', 1);
            $goods_id = request()->get('id', 0);

            $action_link = ['href' => 'goods.php?act=list', 'text' => ''];
            $this->smarty->assign('action_link', $action_link);

            // $log_list = get_goods_inventory_logs($adminru['ru_id']);
            $log_list = get_goods_change_logs($goods_id);
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('log_list', $log_list['list']);
            $this->smarty->assign('filter', $log_list['filter']);
            $this->smarty->assign('record_count', $log_list['record_count']);
            $this->smarty->assign('page_count', $log_list['page_count']);

            $sort_flag = sort_flag($log_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('goods_view_logs.dwt');
        }

        /* ------------------------------------------------------ */
        //-- view_detail 会员价 阶梯价 查看详情
        /* ------------------------------------------------------ */
        elseif ($act == 'view_detail') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $log_id = request()->get('log_id', 0);
            $step = request()->get('step', '');
            if ($step == 'member') {
                $res = GoodsChangeLog::where('log_id', $log_id)->value('member_price');
                $res = $res ? $res : '';

                $res = $res ? unserialize($res) : [];
                $member_price = [];
                if ($res) {
                    foreach ($res as $k => $v) {
                        $member_price[$k]['rank_name'] = UserRank::where('rank_id', $k)->value('rank_name');
                        $member_price[$k]['rank_name'] = $member_price[$k]['rank_name'] ? $member_price[$k]['rank_name'] : '';
                        $member_price[$k]['member_price'] = $v;
                    }
                }
                $this->smarty->assign('res', $member_price);
            } elseif ($step == 'volume') {
                $res = GoodsChangeLog::where('log_id', $log_id)->value('volume_price');
                $res = $res ? $res : '';

                $res = $res ? unserialize($res) : [];
                $volume_price = [];
                if ($res) {
                    foreach ($res as $k => $v) {
                        $volume_price[$k]['volume_num'] = $k;
                        $volume_price[$k]['volume_price'] = $v;
                    }
                }
                $this->smarty->assign('res', $volume_price);
            }

            $this->smarty->assign('step', $step);

            $result['content'] = $this->smarty->fetch('library/view_detail_list.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'view_query') {
            $goods_id = request()->get('goodsId', 0);
            $log_list = get_goods_change_logs($goods_id);

            $this->smarty->assign('log_list', $log_list['list']);
            $this->smarty->assign('filter', $log_list['filter']);
            $this->smarty->assign('record_count', $log_list['record_count']);
            $this->smarty->assign('page_count', $log_list['page_count']);

            $sort_flag = sort_flag($log_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('goods_view_logs.dwt'),
                '',
                ['filter' => $log_list['filter'], 'page_count' => $log_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 批量删除日志记录
        /*------------------------------------------------------ */
        elseif ($act == 'log_batch_drop') {
            admin_priv('goods_manage');

            $count = 0;
            $checkboxes = request()->get('checkboxes', []);
            $goods_id = request()->get('goods_id', 0);

            foreach ($checkboxes as $key => $id) {
                $result = GoodsChangeLog::where('log_id', $id)->delete();

                $count++;
            }
            if ($result) {
                admin_log('', 'remove', 'goods_change_log');

                if ($goods_id) {
                    $step = '&id=' . $goods_id;
                }

                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'goods.php?act=view_log' . $step];
                return sys_msg(sprintf(__('admin::goods.batch_drop_success'), $count), 0, $link);
            }
        }

        /* ------------------------------------------------------ */
        //-- 商品设置
        /* ------------------------------------------------------ */
        elseif ($act == 'step_up') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $this->dscRepository->helpersLang('shop_config', 'admin');

            $this->smarty->assign('ur_here', __('admin::common.001_goods_setting'));

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '001_goods_setting']);

            $group_list = $this->configManageService->getSettingGroups('goods');

            $this->smarty->assign('group_list', $group_list);

            return $this->smarty->display('goods_step_up.dwt');
        }
        /* ------------------------------------------------------ */
        //-- 商品�        �件设置
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_gorup_type') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }


            $result = ['error' => '', 'message' => ''];

            $id = request()->get('id', 0);
            $group_id = request()->get('group_id', 0);

            $data = ['group_id' => $group_id];
            GroupGoods::where('id', $id)->update($data);

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 商品配件价格
        /*------------------------------------------------------ */
        elseif ($act == 'edit_gorup_price') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $id = request()->get('id', 0);
            $sec_price = floatval(request()->get('val', ''));

            $data = ['goods_price' => $sec_price];
            $res = GroupGoods::where('id', $id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($sec_price);
            }
        }
        /*------------------------------------------------------ */
        //-- 删除商品�        �件
        /*------------------------------------------------------ */
        elseif ($act == 'remove_group_type') {
            $check_auth = check_authz_json('goods_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => '', 'message' => ''];
            $id = request()->get('id', 0);
            GroupGoods::where('id', $id)->delete();
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 计算分销佣金
        /*------------------------------------------------------ */
        elseif ($act == 'set_drp_money') {

            $result = ['error' => 0, 'message' => ''];

            if (file_exists(MOBILE_DRP)) {

                $shop_price = request()->input('shop_price', 0);
                $dis_commission = request()->input('dis_commission', 0);

                /**
                 * 验证商品分销比例计算的结果值 不能小于0.01
                 */
                $setmoney = app(\App\Modules\Drp\Services\Drp\DrpGoodsService::class)->check_goods_dis_commission($shop_price, $dis_commission);

                if ($setmoney && $setmoney >= 0.01) {
                    $result['error'] = 0;
                    $result['message'] = __('admin::goods.set_drp_money_success');
                    return response()->json($result);
                } else {
                    $result['error'] = 1;
                    $result['message'] = __('admin::goods.set_drp_money');
                    return response()->json($result);
                }
            }

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 验证商品是否参与活动
        /*------------------------------------------------------ */
        elseif ($act == 'is_goods_add_activity') {

            $result = ['error' => 0, 'message' => ''];

            $goods_id = request()->input('goods_id', 0);

            $is_promotion = is_promotion($goods_id);
            // 验证返回提示
            if ($is_promotion) {
                $return_prompt = is_promotion_error($is_promotion);
                return $return_prompt;
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 商品审核详情页
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'goods_audit_detail') {
            /* 检查权限 */
            admin_priv('goods_manage');

            $goods_id = request()->get('goods_id', 0);
            $goods_info = $this->goodsManageService->getGoodsDetail($goods_id);

            $this->smarty->assign('action_link', ['text' => __('admin::common.01_goods_list'), 'href' => 'javascript:;']);
            $this->smarty->assign('lang', BaseRepository::getArrayCollapse([__('admin::goods'), __('admin::common')]));
            $this->smarty->assign('ur_here', __('admin::goods.goods_detail'));
            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('info', $goods_info);

            return $this->smarty->display('goods_audit_detail.dwt');
        }

        /*------------------------------------------------------ */
        //-- 审核详页弹窗
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'audit_detail_extend') {
            /* 检查权限 */
            admin_priv('goods_manage');
            $result = ['error' => 0, 'message' => '', 'content' => '', 'title' => ''];

            $goods_id = request()->get('goods_id', 0);
            $val = request()->get('val', '');
            $content = '';

            switch ($val) {
                case 'user': // 会员价
                    $rank_list = get_user_rank_list();
                    $price_list = $this->goodsManageService->get_member_price_list($goods_id);

                    $this->smarty->assign('rank_list', $rank_list);
                    $this->smarty->assign('price_list', $price_list);

                    $result['title'] = __('admin::common.view') . __('admin::goods.user_price');
                    break;

                case 'volume': // 阶梯价
                    $volume_price_list = $this->goodsCommonService->getVolumePriceList($goods_id);
                    $this->smarty->assign('volume_price_list', $volume_price_list);

                    $result['title'] = __('admin::common.view') . __('admin::goods.step_price');
                    break;

                case 'fullcut': // 满减价
                    $consumption_list = $this->cartCommonService->getGoodsConList($goods_id, 'goods_consumption'); //满减订单金额
                    $this->smarty->assign('consumption_list', $consumption_list);

                    $result['title'] = __('admin::common.view') . __('admin::goods.is_fullcut_price');
                    break;

                case 'keywords': // 商品关键词
                    $keywords = Goods::where('goods_id', $goods_id)->value('keywords');
                    $keywords = !empty($keywords) ? trim($keywords) : '';
                    $this->smarty->assign('keywords', $keywords);

                    $result['title'] = __('admin::common.view') . __('admin::goods.goods_keywords');
                    break;

                case 'tag': // 评论标签
                    $goods_product_tag = Goods::where('goods_id', $goods_id)->value('goods_product_tag');
                    $goods_product_tag = !empty($goods_product_tag) ? trim($goods_product_tag) : '';
                    $this->smarty->assign('goods_product_tag', $goods_product_tag);

                    $result['title'] = __('admin::common.view') . __('admin::goods.comment_tag');
                    break;

                case 'goodstag': // 服务承诺标签
                    $goods_tag = Goods::where('goods_id', $goods_id)->value('goods_tag');
                    $goods_tag = !empty($goods_tag) ? trim($goods_tag) : '';
                    $this->smarty->assign('goods_tag', $goods_tag);

                    $result['title'] = __('admin::common.view') . __('admin::goods.service_commitment_tag');
                    break;

                default:
                    $result['error'] = '403';
                    $result['message'] = 'not found';
                    break;
            }

            $this->smarty->assign('extend', $val); // 模板传值
            $result['content'] = $this->smarty->fetch('library/goods_audit_detail_extend.lbi');

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 是否预售商品
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'check_presale') {
            /* 检查权限 */
            admin_priv('goods_manage');
            $result = ['error' => 0];

            $goods_id = request()->get('goods_id', 0);
            $content = '';

            $presale = PresaleActivity::where('goods_id', $goods_id)->value('act_id');

            if (!empty($presale)) {
                $result = ['error' => 1];
            }

            return response()->json($result);
        }
    }
}
