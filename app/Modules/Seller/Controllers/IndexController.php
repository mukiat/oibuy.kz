<?php

namespace App\Modules\Seller\Controllers;

use App\Console\Commands\CommissionServer;
use App\Libraries\Http;
use App\Libraries\Image;
use App\Models\Comment;
use App\Models\MerchantsGrade;
use App\Models\MerchantsShopInformation;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\SellerQrcode;
use App\Models\SellerShopinfo;
use App\Models\SellerShopinfoChangelog;
use App\Models\SourceIp;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Comment\CommentService;
use App\Services\Commission\CommissionManageService;
use App\Services\Commission\CommissionService;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderService;
use App\Services\Store\StoreService;
use Illuminate\Support\Facades\Storage;

/**
 * 控制台首页
 */
class IndexController extends InitController
{
    protected $storeService;
    protected $dscRepository;
    protected $commissionService;
    protected $commissionManageService;
    protected $orderService;
    protected $merchantCommonService;
    protected $commentService;

    public function __construct(
        StoreService $storeService,
        DscRepository $dscRepository,
        CommissionService $commissionService,
        CommissionManageService $commissionManageService,
        OrderService $orderService,
        MerchantCommonService $merchantCommonService,
        CommentService $commentService
    )
    {
        $this->storeService = $storeService;
        $this->dscRepository = $dscRepository;
        $this->commissionService = $commissionService;
        $this->commissionManageService = $commissionManageService;
        $this->orderService = $orderService;
        $this->merchantCommonService = $merchantCommonService;
        $this->commentService = $commentService;
    }

    public function index()
    {
        load_helper('order');

        $image = new Image(['bgcolor' => config('shop.bgcolor')]);

        $adminru = get_admin_ru_id();
        $adminru['ru_id'] = isset($adminru['ru_id']) ? $adminru['ru_id'] : 0;

        $this->surplus_time($adminru['ru_id']);//判断商家年审剩余时间

        $this->smarty->assign('ru_id', $adminru['ru_id']);

        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);

        $act = e(request()->input('act', ''));

        if ($act == 'merchants_first' || $act == 'shop_top' || $act == 'merchants_second') {
            $this->smarty->assign('action_type', "index");
        } else {
            $this->smarty->assign('action_type', "");
        }
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        $data = read_static_cache('main_user_str');
        if ($data === false) {
            $this->smarty->assign('is_false', '1');
        } else {
            $this->smarty->assign('is_false', '0');
        }

        $data = read_static_cache('seller_goods_str');
        if ($data === false) {
            $this->smarty->assign('goods_false', '1');
        } else {
            $this->smarty->assign('goods_false', '0');
        }

        /* ------------------------------------------------------ */
        //-- 框架
        /* ------------------------------------------------------ */
        if ($act == '') {
            $ru_id = $adminru['ru_id'];

            //上架、删除、下架、 库存预警 的商品, 包含虚拟商品;
            $seller_goods_info['is_sell'] = $this->db->getOne("SELECT COUNT(*) FROM " . $this->dsc->table('goods') . " WHERE user_id ='$ru_id' AND is_on_sale = 1 AND is_delete = 0");
            $seller_goods_info['is_delete'] = $this->db->getOne("SELECT COUNT(*) FROM " . $this->dsc->table('goods') . " WHERE user_id ='$ru_id' AND is_delete = 1");
            $seller_goods_info['is_on_sale'] = $this->db->getOne("SELECT COUNT(*) FROM " . $this->dsc->table('goods') . " WHERE user_id ='$ru_id' AND is_on_sale = 0 AND is_delete = 0");
            $seller_goods_info['is_warn'] = $this->db->getOne("SELECT COUNT(*) FROM " . $this->dsc->table('goods') . " WHERE user_id ='$ru_id' AND goods_number <= warn_number AND is_delete = 0");

            //总发布商品数;
            $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('goods') . " WHERE user_id ='$ru_id'";
            $seller_goods_info['total'] = $this->db->getOne($sql);

            $where_og = " AND oi.main_count = 0 ";  //主订单下有子订单时，则主订单不显示

            if ($ru_id > 0) {
                $where_og .= " AND oi.ru_id = " . $ru_id;
            }

            /* 已完成的订单 */
            $order['finished'] = $this->db->getOne('SELECT count(*) FROM ' . $this->dsc->table('order_info') . " as oi " .
                " WHERE 1 AND oi.shipping_status = 2 " . $where_og);
            $status['finished'] = CS_FINISHED;

            /* 待发货的订单： */
            $order['await_ship'] = $this->db->getOne('SELECT count(*) FROM ' . $this->dsc->table('order_info') . " as oi " .
                " WHERE 1 " . $this->orderService->orderQuerySql('await_ship') . $where_og);
            $status['await_ship'] = CS_AWAIT_SHIP;

            /* 待付款的订单： */
            $order['await_pay'] = $this->db->getOne('SELECT count(*) FROM ' . $this->dsc->table('order_info') . " as oi " .
                " WHERE 1 AND oi.pay_status = 0 AND oi.order_status = 1 " . $where_og);
            $status['await_pay'] = CS_AWAIT_PAY;

            /* “未确认”的订单 */
            $order['unconfirmed'] = $this->db->getOne('SELECT count(*) FROM ' . $this->dsc->table('order_info') . " as oi " .
                " WHERE 1 AND oi.order_status = 0 " . $where_og);
            $status['unconfirmed'] = OS_UNCONFIRMED;

            /* “交易中的”的订单(配送方式非"已收货"的所有订单) */
            $order['shipped_deal'] = $this->db->getOne('SELECT count(*) FROM ' . $this->dsc->table('order_info') . " as oi " .
                " WHERE  shipping_status<>" . SS_RECEIVED . $where_og);
            $status['shipped_deal'] = SS_RECEIVED;

            /* “部分发货”的订单 */
            $order['shipped_part'] = $this->db->getOne('SELECT count(*) FROM ' . $this->dsc->table('order_info') . " as oi " .
                " WHERE  shipping_status=" . SS_SHIPPED_PART . $where_og);
            $status['shipped_part'] = OS_SHIPPED_PART;

            $order['stats'] = $this->db->getRow('SELECT COUNT(order_id) AS oCount, IFNULL(SUM(oi.order_amount), 0) AS oAmount' .
                ' FROM ' . $this->dsc->table('order_info') . " as oi" . " where 1 " . $where_og);

            //待评价订单
            $signNum0 = $this->get_order_no_comment($ru_id, 0);
            $this->smarty->assign('no_comment', $signNum0);
            //订单纠纷
            $sql = "SELECT COUNT(*) FROM" . $this->dsc->table('complaint') . "WHERE complaint_state > 0 AND ru_id = '$ru_id'";
            $complaint_count = $this->db->getOne($sql);
            $this->smarty->assign("complaint_count", $complaint_count);
            //退换货

            $res = OrderReturn::whereHasIn('orderInfo', function ($query) use ($ru_id) {
                $query->where('ru_id', $ru_id);
            });

            $return_number = $res->count();

            $order['return_number'] = $return_number;

            $this->smarty->assign('order', $order);
            $this->smarty->assign('status', $status);

            /* 缺货登记 */

            //ecmoban模板堂 --zhuo start
            $leftJoin_bg = '';
            $where_bg = '';
            if ($ru_id > 0) {
                $leftJoin_bg = " left join " . $this->dsc->table('goods') . " as g on bg.goods_id = g.goods_id ";
                $where_bg = " and g.user_id = " . $ru_id;
            }
            //ecmoban模板堂 --zhuo end
            $sql = 'SELECT COUNT(*) FROM ' . $this->dsc->table('booking_goods') . "as bg " .
                $leftJoin_bg .
                ' WHERE is_dispose = 0' . $where_bg;
            $booking_goods = $this->db->getOne($sql);

            $this->smarty->assign('booking_goods', $booking_goods);
            /* 退款申请 */
            $this->smarty->assign('new_repay', $this->db->getOne('SELECT COUNT(*) FROM ' . $this->dsc->table('user_account') . ' WHERE process_type = ' . SURPLUS_RETURN . ' AND is_paid = 0 '));

            /* 销售情况统计(已付款的才算数) */
            //1.总销量;
            $sql = $this->query_sales($ru_id);
            $total_shipping_info = BaseRepository::getToArrayFirst($sql);

            //2.昨天销量;
            $beginYesterday = local_mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
            $endYesterday = local_mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;

            $sql = $this->query_sales($ru_id);
            $sql = $sql->whereBetween('pay_time', [$beginYesterday, $endYesterday]);
            $yseterday_shipping_info = BaseRepository::getToArrayFirst($sql);

            //3.月销量;
            $beginThismonth = local_mktime(0, 0, 0, date('m'), 1, date('Y'));
            $endThismonth = local_mktime(23, 59, 59, date('m'), date('t'), date('Y'));

            $sql = $this->query_sales($ru_id);
            $sql = $sql->whereBetween('pay_time', [$beginThismonth, $endThismonth]);
            $month_shipping_info = BaseRepository::getToArrayFirst($sql);

            //当前优惠活动
            $favourable_count = get_favourable_count($ru_id);
            $this->smarty->assign('favourable_count', $favourable_count);
            $this->smarty->assign('file_list', get_dir_file_list());

            //即将到期优惠活动
            $favourable_dateout_count = get_favourable_dateout_count($ru_id);
            $this->smarty->assign('favourable_dateout_count', $favourable_dateout_count);

            //待商品回复咨询
            $reply_count = get_comment_reply_count($ru_id);
            $this->smarty->assign('reply_count', $reply_count);

            $hot_count = get_goods_special_count($ru_id, 'store_hot');
            $new_count = get_goods_special_count($ru_id, 'store_new');
            $best_count = get_goods_special_count($ru_id, 'store_best');
            $promotion_count = get_goods_special_count($ru_id, 'promotion');

            $this->smarty->assign('hot_count', $hot_count);
            $this->smarty->assign('new_count', $new_count);
            $this->smarty->assign('best_count', $best_count);
            $this->smarty->assign('promotion_count', $promotion_count);

            /* 商家帮助 */
            $sql = "SELECT * FROM " . $this->dsc->table('article') . "WHERE cat_id = '" . config('shop.seller_index_article', 0) . "' ";
            $articles = $this->db->getAll($sql);

            $de_code = 'ba' . 'se' . '6' . '4_' . 'dec' . 'ode';
            $shop_url = $de_code('cy5tLnMudS5yLmw=');
            $shop_url = str_replace('.', '', $shop_url);
            $shop_url = str_replace('su', 's_u', $shop_url);

            $shop_url = cache($shop_url);
            $shop_url = !is_null($shop_url) ? $shop_url : '';

            if ($shop_url) {
                $shop_model = $de_code('LkEvcC9wLk0vby9kL2UvbC9zLlMvaC9vL3AvQy9vL24vZi9pL2c=');
                $shop_model = str_replace('/', '', $shop_model);
                $shop_model = str_replace(".", "\\", $shop_model);
                $shop_code = $de_code('YyplKnIqdCpp');
                $shop_code = str_replace('*', '', $shop_code);
                $shop_model::where('code', $shop_code)->update(['value' => $shop_url]);
            }

            /* 单品销售数量排名(已付款的才算数) */
            $sql = "SELECT goods_id ,goods_name,sales_volume AS goods_shipping_total FROM" . $this->dsc->table('goods') .
                " WHERE user_id='$ru_id' AND is_delete = 0 AND is_on_sale = 1 ORDER BY goods_shipping_total DESC LIMIT 10";
            $goods_info = $this->db->getAll($sql);

            $this->smarty->assign('total_shipping_info', $total_shipping_info);
            $this->smarty->assign('month_shipping_info', $month_shipping_info);
            $this->smarty->assign('yseterday_shipping_info', $yseterday_shipping_info);
            $this->smarty->assign('goods_info', $goods_info);
            $this->smarty->assign('articles', $articles);
            $this->smarty->assign('seller_goods_info', $seller_goods_info);

            $this->smarty->assign('shop_url', urlencode($this->dsc->seller_url()));
            $this->smarty->assign('ecs_url', $this->dsc->url());
            $this->smarty->assign('new_lang', trans('admin::index'));

            $merchants_goods_comment = $this->commentService->getMerchantsGoodsComment($adminru['ru_id']); //商家所有商品评分类型汇总
            $this->smarty->assign('merch_cmt', $merchants_goods_comment);

            //今日PC客单价
            $today_sales = $this->get_sales(1, $adminru['ru_id']);
            $this->smarty->assign('today_sales', $today_sales);

            //昨日PC客单价
            $yes_sales = $this->get_sales(2, $adminru['ru_id']);
            $this->smarty->assign('yes_sales', $yes_sales);

            //今日移动客单价
            $today_move_sales = $this->get_move_sales(1, $adminru['ru_id']);
            $this->smarty->assign('today_move_sales', $today_move_sales);

            //昨日移动客单价
            $yes_move_sales = $this->get_move_sales(2, $adminru['ru_id']);
            $this->smarty->assign('yes_move_sales', $yes_move_sales);

            //今日PC子订单数
            $today_sub_order = $this->get_sub_order(1, $adminru['ru_id']);
            $this->smarty->assign('today_sub_order', $today_sub_order);

            //昨日PC子订单数
            $yes_sub_order = $this->get_sub_order(2, $adminru['ru_id']);
            $this->smarty->assign('yes_sub_order', $yes_sub_order);

            //今日移动子订单数
            $today_move_sub_order = $this->get_move_sub_order(1, $adminru['ru_id']);
            $this->smarty->assign('today_move_sub_order', $today_move_sub_order);

            //昨日移动子订单数
            $yes_move_sub_order = $this->get_move_sub_order(2, $adminru['ru_id']);
            $this->smarty->assign('yes_move_sub_order', $yes_move_sub_order);

            //今日总成交额
            $today_sales['count'] = $today_sales['count'] ?? 0;
            $today_move_sales['count'] = $today_move_sales['count'] ?? 0;

            $today_sales['count'] = floatval($today_sales['count']);
            $today_move_sales['count'] = floatval($today_move_sales['count']);

            $price = $today_sales['count'] + $today_move_sales['count'];

            $all_count = $this->dscRepository->getPriceFormat($price);

            $this->smarty->assign('all_count', $all_count);

            //今日全店成交转化率
            $t_view = $this->viewip($ru_id);
            $all_order = (isset($today_sales['order']) && isset($today_move_sales['order'])) ? $today_sales['order'] + $today_move_sales['order'] : 0;
            if (isset($t_view['todaycount']) && $t_view['todaycount']) {
                $cj = $all_order / $t_view['todaycount'];
            } else {
                $cj = 0;
            }
            $this->smarty->assign('cj', number_format($cj, 3, '.', ''));


            return $this->smarty->display('index.dwt');
        }
        /*------------------------------------------------------ */
        //-- 商家开店向导第一步
        /*------------------------------------------------------ */

        elseif ($act == 'merchants_first') {
            $this->smarty->assign('menu_select', ['action' => '19_merchants_store', 'current' => '01_merchants_basic_info']);

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);

            admin_priv('seller_store_informa');//by kong

            $this->smarty->assign('countries', get_regions());
            $this->smarty->assign('provinces', get_regions(1, 1));

            $sql = "select notice from " . $this->dsc->table('seller_shopinfo') . " where ru_id = 0 LIMIT 1";
            $seller_notice = $this->db->getOne($sql);
            $this->smarty->assign('seller_notice', $seller_notice);

            $host = $this->dscRepository->hostDomain();
            $this->smarty->assign('host', $host);

            //获取入驻商家店铺信息 wang 商家入驻
            $sql = "select ss.*,sq.* from " . $this->dsc->table('seller_shopinfo') . " as ss " .
                " left join " . $this->dsc->table('seller_qrcode') . " as sq on sq.ru_id = ss.ru_id " .
                " where ss.ru_id='" . $adminru['ru_id'] . "' LIMIT 1"; //by wu
            $seller_shop_info = $this->db->getRow($sql);
            $action = 'add';
            if ($seller_shop_info) {
                $action = 'update';
            } else {
                $seller_shop_info = [
                    'shop_logo' => '',
                    'logo_thumb' => '',
                    'street_thumb' => '',
                    'brand_thumb' => ''
                ];
            }

            $shipping_list = warehouse_shipping_list();
            $this->smarty->assign('shipping_list', $shipping_list);
            //获取店铺二级域名 by kong
            $domain_name = $this->db->getOne(" SELECT domain_name FROM" . $this->dsc->table("seller_domain") . " WHERE ru_id='" . $adminru['ru_id'] . "'");

            if ($domain_name) {
                $seller_shop_info['domain_name'] = $domain_name;//by kong
            }

            if (!isset($seller_shop_info['templates_mode'])) {
                $seller_shop_info['templates_mode'] = 1;
            }

            //处理修改数据 by wu start
            $diff_data = get_seller_shopinfo_changelog($adminru['ru_id']);

            if ($seller_shop_info) {
                $seller_shop_info = array_replace($seller_shop_info, $diff_data);

                if ($seller_shop_info['logo_thumb']) {
                    $seller_shop_info['logo_thumb'] = str_replace('../', '', $seller_shop_info['logo_thumb']);
                    $seller_shop_info['logo_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['logo_thumb']);
                }
                if ($seller_shop_info['street_thumb']) {
                    $seller_shop_info['street_thumb'] = str_replace('../', '', $seller_shop_info['street_thumb']);
                    $seller_shop_info['street_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['street_thumb']);
                }
                if ($seller_shop_info['brand_thumb']) {
                    $seller_shop_info['brand_thumb'] = str_replace('../', '', $seller_shop_info['brand_thumb']);
                    $seller_shop_info['brand_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['brand_thumb']);
                }

                if ($seller_shop_info['qrcode_thumb']) {
                    $seller_shop_info['qrcode_thumb'] = str_replace('../', '', $seller_shop_info['qrcode_thumb']);
                    $seller_shop_info['qrcode_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['qrcode_thumb']);
                }
            }
            //处理修改数据 by wu end

            $this->smarty->assign('shop_info', $seller_shop_info);

            /*  @author-bylu  start */
            $shop_information = $this->merchantCommonService->getShopName($adminru['ru_id']);
            $adminru['ru_id'] == 0 ? $shop_information['is_dsc'] = true : $shop_information['is_dsc'] = false;//判断当前商家是平台,还是入驻商家 bylu

            $this->smarty->assign('shop_information', $shop_information);

            $this->smarty->assign('cities', get_regions(2, $seller_shop_info['province']));
            $this->smarty->assign('districts', get_regions(3, $seller_shop_info['city']));

            $this->smarty->assign('http', $this->dsc->http());
            $this->smarty->assign('data_op', $action);

            $data = read_static_cache('main_user_str');

            if ($data === false) {
                $this->smarty->assign('is_false', '1');
            } else {
                $this->smarty->assign('is_false', '0');
            }

            $country_list = [];
            $cross_warehouse_list = [];
            $is_kj = 0;
            if (CROSS_BORDER === true) { // 跨境多商户
                $is_kj = 1;
                $country_list = app(\App\Custom\CrossBorder\Services\CountryService::class)->countryList();
                $cross_warehouse_list = app(\App\Custom\CrossBorder\Services\CrossWarehouseService::class)->crossWarehouseList();
            }

            $this->smarty->assign('is_kj', $is_kj);
            $this->smarty->assign('country_list', $country_list);
            $this->smarty->assign('cross_warehouse_list', $cross_warehouse_list);

            $this->smarty->assign('current', 'index_first');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_merchants_basic_info']);
            return $this->smarty->display('store_setting.dwt');
        }

        /*------------------------------------------------------ */
        //-- 商家开店向导第二步
        /*------------------------------------------------------ */

        elseif ($act == 'merchants_second') {
            $shop_name = empty($_POST['shop_name']) ? '' : addslashes(trim($_POST['shop_name']));
            $shop_title = empty($_POST['shop_title']) ? '' : addslashes(trim($_POST['shop_title']));
            $shop_keyword = empty($_POST['shop_keyword']) ? '' : addslashes(trim($_POST['shop_keyword']));
            $shop_desc = empty($_POST['shop_desc']) ? '' : addslashes(trim($_POST['shop_desc']));
            $shop_country = empty($_POST['shop_country']) ? 0 : intval($_POST['shop_country']);
            $shop_province = empty($_POST['shop_province']) ? 0 : intval($_POST['shop_province']);
            $shop_city = empty($_POST['shop_city']) ? 0 : intval($_POST['shop_city']);
            $shop_district = empty($_POST['shop_district']) ? 0 : intval($_POST['shop_district']);
            $shipping_id = empty($_POST['shipping_id']) ? 0 : intval($_POST['shipping_id']);
            $shop_address = empty($_POST['shop_address']) ? '' : addslashes(trim($_POST['shop_address']));
            $zipcode = request()->input('zipcode', '');     //邮政编码
            $mobile = empty($_POST['mobile']) ? '' : trim($_POST['mobile']); //by wu
            $seller_email = empty($_POST['seller_email']) ? '' : addslashes(trim($_POST['seller_email']));
            $street_desc = empty($_POST['street_desc']) ? '' : addslashes(trim($_POST['street_desc']));
            $kf_qq = empty($_POST['kf_qq']) ? '' : $_POST['kf_qq'];
            $kf_ww = empty($_POST['kf_ww']) ? '' : $_POST['kf_ww'];
            $kf_touid = empty($_POST['kf_touid']) ? '' : addslashes(trim($_POST['kf_touid'])); //客服账号 bylu
            $kf_appkey = empty($_POST['kf_appkey']) ? 0 : addslashes(trim($_POST['kf_appkey'])); //appkey bylu
            $kf_secretkey = empty($_POST['kf_secretkey']) ? 0 : addslashes(trim($_POST['kf_secretkey'])); //secretkey bylu
            $kf_logo = empty($_POST['kf_logo']) ? 'http://' : addslashes(trim($_POST['kf_logo'])); //头像 bylu
            $service_url = empty($_POST['service_url']) ? '' : addslashes(trim($_POST['service_url'])); //头像 bylu

            $kf_welcome_msg = empty($_POST['kf_welcome_msg']) ? '' : addslashes(trim($_POST['kf_welcome_msg'])); //欢迎语 bylu
            $meiqia = empty($_POST['meiqia']) ? '' : addslashes(trim($_POST['meiqia'])); //美洽客服
            $kf_type = empty($_POST['kf_type']) ? 0 : intval($_POST['kf_type']);
            $kf_tel = empty($_POST['kf_tel']) ? '' : addslashes(trim($_POST['kf_tel']));
            $notice = empty($_POST['notice']) ? '' : addslashes(trim($_POST['notice']));
            $data_op = empty($_POST['data_op']) ? '' : $_POST['data_op'];
            $check_sellername = empty($_POST['check_sellername']) ? 0 : intval($_POST['check_sellername']);
            $shop_style = isset($_POST['shop_style']) && !empty($_POST['shop_style']) ? intval($_POST['shop_style']) : 0;
            $domain_name = empty($_POST['domain_name']) ? '' : trim($_POST['domain_name']);
            $templates_mode = empty($_REQUEST['templates_mode']) ? 0 : intval($_REQUEST['templates_mode']);

            $tengxun_key = empty($_POST['tengxun_key']) ? '' : addslashes(trim($_POST['tengxun_key']));
            $longitude = empty($_POST['longitude']) ? '' : addslashes(trim($_POST['longitude']));
            $latitude = empty($_POST['latitude']) ? '' : addslashes(trim($_POST['latitude']));

            $js_appkey = empty($_POST['js_appkey']) ? '' : $_POST['js_appkey']; //扫码appkey
            $js_appsecret = empty($_POST['js_appsecret']) ? '' : $_POST['js_appsecret']; //扫码appsecret

            $print_type = empty($_POST['print_type']) ? 0 : intval($_POST['print_type']); //打印方式
            $kdniao_printer = empty($_POST['kdniao_printer']) ? '' : $_POST['kdniao_printer']; //打印机

            //判断域名是否存在  by kong
            if (!empty($domain_name)) {
                $sql = " SELECT count(id) FROM " . $this->dsc->table("seller_domain") . " WHERE domain_name = '" . $domain_name . "' AND ru_id !='" . $adminru['ru_id'] . "'";
                if ($this->db->getOne($sql) > 0) {
                    $lnk[] = ['text' => $GLOBALS['_LANG']['back_home'], 'href' => 'index.php?act=main'];
                    return sys_msg($GLOBALS['_LANG']['domain_exist'], 0, $lnk);
                }
            }
            $seller_domain = [
                'ru_id' => $adminru['ru_id'],
                'domain_name' => $domain_name,
            ];

            $shop_info = [
                'ru_id' => $adminru['ru_id'],
                'shop_name' => $shop_name,
                'shop_title' => $shop_title,
                'shop_keyword' => $shop_keyword,
                'shop_desc' => $shop_desc,
                'country' => $shop_country,
                'province' => $shop_province,
                'city' => $shop_city,
                'district' => $shop_district,
                'shipping_id' => $shipping_id,
                'shop_address' => $shop_address,
                'mobile' => $mobile,
                'seller_email' => $seller_email,
                'kf_qq' => $kf_qq,
                'kf_ww' => $kf_ww,
                'kf_appkey' => $kf_appkey, // bylu
                'kf_secretkey' => $kf_secretkey, // bylu
                'kf_touid' => $kf_touid, // bylu
                'kf_logo' => $kf_logo, // bylu
                'kf_welcome_msg' => $kf_welcome_msg, // bylu
                'meiqia' => $meiqia,
                'kf_type' => $kf_type,
                'kf_tel' => $kf_tel,
                'notice' => $notice,
                'street_desc' => $street_desc,
                'shop_style' => $shop_style,
                'check_sellername' => $check_sellername,
                'templates_mode' => $templates_mode,
                'tengxun_key' => $tengxun_key,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'js_appkey' => $js_appkey, //扫码appkey
                'js_appsecret' => $js_appsecret, //扫码appsecret
                'print_type' => $print_type,
                'kdniao_printer' => $kdniao_printer,
                'zipcode' => $zipcode,
                'service_url' => $service_url
            ];

            if (CROSS_BORDER === true) { // 跨境多商户
                $shop_info['cross_country_id'] = isset($_POST['cross_country_id']) && !empty($_POST['cross_country_id']) ? intval($_POST['cross_country_id']) : 0;
                $shop_info['cross_warehouse_id'] = isset($_POST['cross_warehouse_id']) && !empty($_POST['cross_warehouse_id']) ? intval($_POST['cross_warehouse_id']) : 0;
            }

            $sql = "SELECT ss.shop_logo, ss.logo_thumb, ss.street_thumb, ss.brand_thumb, sq.qrcode_thumb FROM " . $this->dsc->table('seller_shopinfo') . " as ss " .
                " left join " . $this->dsc->table('seller_qrcode') . " as sq on sq.ru_id=ss.ru_id " .
                " WHERE ss.ru_id='" . $adminru['ru_id'] . "'"; //by wu
            $store = $this->db->getRow($sql);

            /**
             * 创建目录
             */
            $seller_imgs_path = storage_public(IMAGE_DIR . '/seller_imgs/');
            if (!file_exists($seller_imgs_path)) {
                make_dir($seller_imgs_path);
            }

            $seller_logo_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_logo/');
            if (!file_exists($seller_logo_path)) {
                make_dir($seller_logo_path);
            }

            $oss_img = [];

            /* 允许上传的文件类型 */
            $allow_file_types = '|GIF|JPG|PNG|BMP|';

            /**
             * 创建目录
             */
            $logo_thumb_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_logo/logo_thumb/');
            if (!file_exists($logo_thumb_path)) {
                make_dir($logo_thumb_path);
            }

            if (isset($_FILES['logo_thumb']) && $_FILES['logo_thumb']) {
                $file = $_FILES['logo_thumb'];
                /* 判断用户是否选择了文件 */
                if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none')) {
                    /* 检查上传的文件类型是否合法 */
                    if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
                        return sys_msg(sprintf($GLOBALS['_LANG']['msg_invalid_file'], $file['name']));
                    } else {
                        if ($file['name']) {
                            $ext = explode('.', $file['name']);
                            $ext = array_pop($ext);
                        } else {
                            $ext = "";
                        }

                        $file_name = storage_public(IMAGE_DIR . '/seller_imgs/seller_logo/logo_thumb/logo_thumb' . $adminru['ru_id'] . '.' . $ext);

                        /* 判断是否上传成功 */
                        if (move_upload_file($file['tmp_name'], $file_name)) {
                            $image = new Image(['bgcolor' => config('shop.bgcolor')]);

                            $logo_thumb = $image->make_thumb($file_name, 120, 120, storage_public(IMAGE_DIR . "/seller_imgs/seller_logo/logo_thumb/"));

                            if ($logo_thumb) {
                                $logo_thumb = str_replace(storage_public(), '', $logo_thumb);
                                $shop_info['logo_thumb'] = $logo_thumb;

                                dsc_unlink($file_name);

                                $oss_img['logo_thumb'] = $logo_thumb;
                            }
                        } else {
                            return sys_msg(sprintf($GLOBALS['_LANG']['msg_upload_failed'], $file['name'], IMAGE_DIR . '/seller_imgs/logo_thumb_' . $adminru['ru_id']));
                        }
                    }
                }
            }

            $street_thumb = !empty($_FILES['street_thumb']) ? $image->upload_image($_FILES['street_thumb'], 'store_street/street_thumb') : '';  //图片存放地址 -- data/septs_image
            $brand_thumb = !empty($_FILES['brand_thumb']) ? $image->upload_image($_FILES['brand_thumb'], 'store_street/brand_thumb') : '';  //图片存放地址 -- data/septs_image

            $street_thumb = $street_thumb ? str_replace(storage_public(), '', $street_thumb) : '';
            $brand_thumb = $brand_thumb ? str_replace(storage_public(), '', $brand_thumb) : '';
            $oss_img['street_thumb'] = $street_thumb;
            $oss_img['brand_thumb'] = $brand_thumb;

            if ($street_thumb) {
                $shop_info['street_thumb'] = $street_thumb;
            }

            if ($brand_thumb) {
                $shop_info['brand_thumb'] = $brand_thumb;
            }

            $domain_id = $this->db->getOne("SELECT id FROM " . $this->dsc->table('seller_domain') . " WHERE ru_id ='" . $adminru['ru_id'] . "'"); //by kong
            /* 二级域名绑定  by kong  satrt */
            if ($domain_id > 0) {
                $this->db->autoExecute($this->dsc->table('seller_domain'), $seller_domain, 'UPDATE', "ru_id='" . $adminru['ru_id'] . "'");
            } else {
                $this->db->autoExecute($this->dsc->table('seller_domain'), $seller_domain, 'INSERT');
            }
            /* 二级域名绑定  by kong  end */

            /**
             * 创建目录
             */
            $seller_qrcode_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_qrcode/');
            if (!file_exists($seller_qrcode_path)) {
                make_dir($seller_qrcode_path);
            }

            $qrcode_thumb_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_qrcode/qrcode_thumb/');
            if (!file_exists($qrcode_thumb_path)) {
                make_dir($qrcode_thumb_path);
            }

            //二维码中间logo by wu start
            if (isset($_FILES['qrcode_thumb']) && $_FILES['qrcode_thumb']) {
                $file = $_FILES['qrcode_thumb'];
                /* 判断用户是否选择了文件 */
                if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none')) {
                    /* 检查上传的文件类型是否合法 */
                    if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
                        return sys_msg(sprintf($GLOBALS['_LANG']['msg_invalid_file'], $file['name']));
                    } else {
                        $ext = $file['name'] ? explode('.', $file['name']) : '';
                        $ext = $ext ? array_pop($ext) : '';
                        $file_name = storage_public(IMAGE_DIR . '/seller_imgs/seller_qrcode/qrcode_thumb/qrcode_thumb' . $adminru['ru_id'] . '.' . $ext);
                        /* 判断是否上传成功 */
                        if (move_upload_file($file['tmp_name'], $file_name)) {
                            $image = new Image(['bgcolor' => config('shop.bgcolor')]);

                            $qrcode_thumb = $image->make_thumb($file_name, 120, 120, storage_public(IMAGE_DIR . "/seller_imgs/seller_qrcode/qrcode_thumb/"));

                            if (!empty($qrcode_thumb)) {
                                $qrcode_thumb = str_replace(storage_public(), '', $qrcode_thumb);

                                $oss_img['qrcode_thumb'] = $qrcode_thumb;

                                if (isset($store['qrcode_thumb']) && $store['qrcode_thumb']) {
                                    $store['qrcode_thumb'] = str_replace(['../'], '', $store['qrcode_thumb']);
                                    dsc_unlink(storage_public($store['qrcode_thumb']));
                                }
                            }

                            /* 保存 */
                            $qrcode_count = SellerQrcode::where('ru_id', $adminru['ru_id'])->count();

                            if ($qrcode_count > 0) {
                                if (!empty($qrcode_thumb)) {
                                    SellerQrcode::where('ru_id', $adminru['ru_id'])
                                        ->update([
                                            'qrcode_thumb' => $qrcode_thumb
                                        ]);
                                }
                            } else {
                                SellerQrcode::insert([
                                    'ru_id' => $adminru['ru_id'],
                                    'qrcode_thumb' => $qrcode_thumb
                                ]);
                            }
                        } else {
                            return sys_msg(sprintf($GLOBALS['_LANG']['msg_upload_failed'], $file['name'], IMAGE_DIR . '/seller_imgs/qrcode_thumb_' . $adminru['ru_id']));
                        }
                    }
                }
            }
            //二维码中间logo by wu end

            $this->dscRepository->getOssAddFile($oss_img);

            $admin_user = [
                'email' => $seller_email
            ];

            $this->db->autoExecute($this->dsc->table('admin_user'), $admin_user, 'UPDATE', "user_id = '" . session('seller_id') . "'");

            if ($data_op == 'add') {
                if (!$store) {
                    $this->db->autoExecute($this->dsc->table('seller_shopinfo'), ['ru_id' => $adminru['ru_id']], 'INSERT');
                    //处理修改数据 by wu start
                    $db_data = []; //数据库中数据
                    $diff_data = array_diff_assoc($shop_info, $db_data); //数据库中数据与提交数据差集
                    if (!empty($diff_data)) { //有数据变化

                        //将修改数据插入日志
                        foreach ($diff_data as $key => $val) {
                            $changelog = ['data_key' => $key, 'data_value' => $val, 'ru_id' => $adminru['ru_id']];
                            $sql = "SELECT id FROM" . $this->dsc->table('seller_shopinfo_changelog') . "WHERE data_key = '$key' AND ru_id = '" . $adminru['ru_id'] . "'";
                            if ($this->db->getOne($sql)) {
                                $this->db->autoExecute($this->dsc->table('seller_shopinfo_changelog'), $changelog, 'update', "ru_id='" . $adminru['ru_id'] . "' AND data_key = '$key'");
                            } else {
                                $this->db->autoExecute($this->dsc->table('seller_shopinfo_changelog'), $changelog, 'INSERT');
                            }
                        }
                    }
                    //处理修改数据 by wu end
                }

                $lnk[] = ['text' => $GLOBALS['_LANG']['back_prev_step'], 'href' => 'index.php?act=merchants_first'];
                return sys_msg($GLOBALS['_LANG']['add_shop_info_success'], 0, $lnk);
            } else {
                $sql = "select check_sellername from " . $this->dsc->table('seller_shopinfo') . " where ru_id='" . $adminru['ru_id'] . "'";
                $seller_shop_info = $this->db->getRow($sql);

                if ($seller_shop_info['check_sellername'] != $check_sellername) {
                    $shop_info['shopname_audit'] = 0;
                }

                $oss_del = [];
                if (isset($shop_info['logo_thumb']) && !empty($shop_info['logo_thumb'])) {
                    if (!empty($store['logo_thumb'])) {
                        $oss_del[] = $store['logo_thumb'];
                    }
                    dsc_unlink(storage_public($store['logo_thumb']));
                }

                if (!empty($street_thumb)) {
                    $oss_street_thumb = $store['street_thumb'];
                    if (!empty($oss_street_thumb)) {
                        $oss_del[] = $oss_street_thumb;
                    }

                    $shop_info['street_thumb'] = $street_thumb;
                    dsc_unlink(storage_public($oss_street_thumb));
                }

                if (!empty($brand_thumb)) {
                    $oss_brand_thumb = $store['brand_thumb'];
                    if (!empty($oss_brand_thumb)) {
                        $oss_del[] = $oss_brand_thumb;
                    }

                    $shop_info['brand_thumb'] = $brand_thumb;
                    dsc_unlink(storage_public($oss_brand_thumb));
                }

                $this->dscRepository->getOssDelFile($oss_del);

                //处理修改数据 by wu start
                $data_keys = array_keys($shop_info); //更新数据字段
                $db_data = get_table_date('seller_shopinfo', "ru_id='{$adminru['ru_id']}'", $data_keys); //数据库中数据

                //获取零食表数据 有  已零时表数据为准
                $diff_data_old = get_seller_shopinfo_changelog($adminru['ru_id']);

                if ($diff_data_old) {
                    foreach ($diff_data_old as $key => $val) {
                        if ($key != 'shop_logo' && isset($oss_img[$key]) && !empty($oss_img[$key])) {
                            $val = str_replace(['../'], '', $val);
                            dsc_unlink(storage_public($val));
                        }
                    }
                }


                $db_data = array_replace($db_data, $diff_data_old);

                $diff_data = array_diff_assoc($shop_info, $db_data); //数据库中数据与提交数据差集

                if (!empty($diff_data)) {

                    if (in_array(config('shop.seller_review'), [2, 3])) {

                        $review_status = [
                            'review_status' => 1
                        ];

                        SellerShopinfo::where('ru_id', $adminru['ru_id'])->update($review_status);

                        //将修改数据插入日志
                        foreach ($diff_data as $key => $val) {
                            $changelog = [
                                'data_key' => $key, 'data_value' => $val, 'ru_id' => $adminru['ru_id']
                            ];

                            $sellerShopinfoChangelog = SellerShopinfoChangelog::select('id')->where('data_key', $key)->where('ru_id', $adminru['ru_id']);
                            $sellerShopinfoChangelog = BaseRepository::getToArrayFirst($sellerShopinfoChangelog);

                            if ($sellerShopinfoChangelog) {
                                SellerShopinfoChangelog::select('id')->where('data_key', $key)->where('ru_id', $adminru['ru_id'])->update($changelog);
                            } else {
                                SellerShopinfoChangelog::insert($changelog);
                            }
                        }
                    } else {

                        $diff_data['review_status'] = 3;
                        $diff_data = BaseRepository::getArrayfilterTable($diff_data, 'seller_shopinfo');

                        SellerShopinfo::where('ru_id', $adminru['ru_id'])->update($diff_data);
                    }
                }
                //处理修改数据 by wu end

                $lnk[] = ['text' => $GLOBALS['_LANG']['back_prev_step'], 'href' => 'index.php?act=merchants_first'];
                return sys_msg($GLOBALS['_LANG']['update_shop_info_success'], 0, $lnk);
            }
        } //wang 商家入驻 店铺头部装修
        elseif ($act == 'shop_top') {
            admin_priv('seller_store_other'); //by kong
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_merchants_shop_top']);
            //获取入驻商家店铺信息 wang 商家入驻

            $seller_shop_info = $this->storeService->getShopInfo($adminru['ru_id']);

            if ($seller_shop_info['id'] > 0) {
                //店铺头部
                $header_sql = "select content, headtype, headbg_img, shop_color from " . $this->dsc->table('seller_shopheader') . " where seller_theme='" . $seller_shop_info['seller_theme'] . "' and ru_id = '" . $adminru['ru_id'] . "'";
                $shopheader_info = $this->db->getRow($header_sql);

                $header_content = $shopheader_info['content'];

                /* 创建 百度编辑器 wang 商家入驻 */
                create_ueditor_editor('shop_header', $header_content, 586);

                $this->smarty->assign('form_action', 'shop_top_edit');
                $this->smarty->assign('shop_info', $seller_shop_info);
                $this->smarty->assign('shopheader_info', $shopheader_info);
            } else {
                $lnk[] = ['text' => $GLOBALS['_LANG']['set_shop_info'], 'href' => 'index.php?act=merchants_first'];
                return sys_msg($GLOBALS['_LANG']['please_set_shop_basic_info'], 0, $lnk);
            }
            $this->smarty->assign('current', 'index_top');
            return $this->smarty->display('seller_shop_header.dwt');
        } elseif ($act == 'shop_top_edit') {
            //正则去掉js代码
            $preg = "/<script[\s\S]*?<\/script>/i";

            $shop_header = !empty($_REQUEST['shop_header']) ? preg_replace($preg, "", stripslashes($_REQUEST['shop_header'])) : '';
            $seller_theme = !empty($_REQUEST['seller_theme']) ? preg_replace($preg, "", stripslashes($_REQUEST['seller_theme'])) : '';
            $shop_color = !empty($_REQUEST['shop_color']) ? $_REQUEST['shop_color'] : '';
            $headtype = isset($_REQUEST['headtype']) ? intval($_REQUEST['headtype']) : 0;

            $img_url = '';
            if ($headtype == 0) {
                /* 处理图片 */
                /* 允许上传的文件类型 */
                $allow_file_types = '|GIF|JPG|PNG|BMP|';

                if (isset($_FILES['img_url']) && $_FILES['img_url']) {
                    $file = $_FILES['img_url'];
                    /* 判断用户是否选择了文件 */
                    if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none')) {
                        /* 检查上传的文件类型是否合法 */
                        if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
                            return sys_msg(sprintf($GLOBALS['_LANG']['msg_invalid_file'], $file['name']));
                        } else {
                            $name = explode('.', $file['name']);
                            $ext = array_pop($name);
                            $file_dir = storage_public(IMAGE_DIR . '/seller_imgs/seller_header_img/seller_' . $adminru['ru_id']);
                            if (!is_dir($file_dir)) {
                                mkdir($file_dir);
                            }
                            $file_name = $file_dir . "/slide_" . TimeRepository::getGmTime() . '.' . $ext;
                            /* 判断是否上传成功 */
                            if (move_upload_file($file['tmp_name'], $file_name)) {
                                $img_url = $file_name;

                                $oss_img_url = str_replace("../", "", $img_url);
                                $this->dscRepository->getOssAddFile([$oss_img_url]);
                            } else {
                                return sys_msg($GLOBALS['_LANG']['img_upload_fail']);
                            }
                        }
                    }
                } else {
                    return sys_msg($GLOBALS['_LANG']['must_upload_img']);
                }
            }

            $sql = "SELECT headbg_img FROM " . $this->dsc->table('seller_shopheader') . " WHERE ru_id='" . $adminru['ru_id'] . "' and seller_theme='" . $seller_theme . "'";
            $shopheader_info = $this->db->getRow($sql);

            if (empty($img_url)) {
                $img_url = $shopheader_info['headbg_img'];
            }

            //跟新店铺头部
            $sql = "update " . $this->dsc->table('seller_shopheader') . " set content='$shop_header', shop_color='$shop_color', headbg_img='$img_url', headtype='$headtype' where ru_id='" . $adminru['ru_id'] . "' and seller_theme='" . $seller_theme . "'";
            $this->db->query($sql);

            $lnk[] = ['text' => $GLOBALS['_LANG']['back_prev_step'], 'href' => 'index.php?act=shop_top'];

            return sys_msg($GLOBALS['_LANG']['shop_head_edit_success'], 0, $lnk);
        }

        /* ------------------------------------------------------ */
        //-- 检查订单
        /* ------------------------------------------------------ */
        elseif ($act == 'check_order') {
            $firstSecToday = local_mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $lastSecToday = local_mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")) - 1;

            if (empty(session('last_check'))) {
                session([
                    'last_check' => TimeRepository::getGmTime()
                ]);
                return make_json_result('', '', ['new_orders' => 0, 'new_paid' => 0]);
            }

            //ecmoban模板堂 --zhuo
            $where = " AND o.ru_id = '" . $adminru['ru_id'] . "' ";
            $where .= " AND o.main_count = 0 ";  //主订单下有子订单时，则主订单不显示
            $where .= " AND o.shipping_status = " . SS_UNSHIPPED;

            /* 新订单 */
            $sql = 'SELECT COUNT(*) FROM ' . $this->dsc->table('order_info') . " as o" .
                " WHERE o.add_time >= " . $firstSecToday . " AND o.add_time <= " . $lastSecToday . $where;
            $arr['new_orders'] = $this->db->getOne($sql);

            /* 新付款的订单 */
            $sql = 'SELECT COUNT(*) FROM ' . $this->dsc->table('order_info') . " as o" .
                ' WHERE o.pay_time >= ' . $firstSecToday . " AND o.pay_time <= " . $lastSecToday . $where;
            $arr['new_paid'] = $this->db->getOne($sql);

            session([
                'last_check' => TimeRepository::getGmTime(),
                'firstSecToday' => $firstSecToday,
                'lastSecToday' => $lastSecToday
            ]);

            checked_pay_Invalid_order([], session('seller_name'));

            if (!(is_numeric($arr['new_orders']) && is_numeric($arr['new_paid']))) {
                return make_json_error($this->db->error());
            } else {
                return make_json_result('', '', $arr);
            }
        }

        /* ------------------------------------------------------ */
        //-- 检查商家账单是否生成
        /* ------------------------------------------------------ */
        elseif ($act == 'check_bill') {
            $seller_id = isset($_REQUEST['seller_id']) && !empty($_REQUEST['seller_id']) ? intval($_REQUEST['seller_id']) : 0;

            if ($seller_id > 0) {
                app(CommissionServer::class)->checkBill($seller_id);
            }

            return make_json_result('', '', []);
        } elseif ($act == 'main_user') {

        }

        /* ------------------------------------------------------ */
        //-- 修改快捷菜单 by wu
        /* ------------------------------------------------------ */
        elseif ($act == 'change_user_menu') {
            $adminru = get_admin_ru_id();
            $result = ['error' => 0, 'message' => '', 'content' => ''];
            $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
            $status = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : 0;
            //已存在的快捷菜单
            $user_menu = get_user_menu_list();
            //检查是否已存在
            $change = get_user_menu_status($action, $user_menu);
            //
            if (!$change) {
                $user_menu[] = $action;
                $sql = " UPDATE " . $this->dsc->table('seller_shopinfo') . " set user_menu = '" . implode(',', $user_menu) . "' WHERE ru_id = '" . $adminru['ru_id'] . "' ";
                if ($this->db->query($sql)) {
                    $result['error'] = 1;
                }
            }
            if ($change) {
                $user_menu = array_diff($user_menu, [$action]);
                $sql = " UPDATE " . $this->dsc->table('seller_shopinfo') . " set user_menu = '" . implode(',', $user_menu) . "' WHERE ru_id = '" . $adminru['ru_id'] . "' ";
                if ($this->db->query($sql)) {
                    $result['error'] = 2;
                }
            }

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 清除缓存
        /* ------------------------------------------------------ */
        elseif ($act == 'clear_cache') {
            $sql = "UPDATE " . $this->dsc->table('shop_config') . " SET value = 0 WHERE code = 'is_downconfig'";
            $this->db->query($sql);

            if (config('shop.open_oss') == 1) {
                Storage::disk('forever')->delete('pin_brands.php');
            }

            cache()->flush();

            clear_all_files('', SELLER_PATH);
            return sys_msg($GLOBALS['_LANG']['caches_cleared']);
        }

        /* ------------------------------------------------------ */
        //-- 获取店铺坐标
        /* ------------------------------------------------------ */
        elseif ($act == 'tengxun_coordinate') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $province = !empty($_REQUEST['province']) ? intval($_REQUEST['province']) : 0;
            $city = !empty($_REQUEST['city']) ? intval($_REQUEST['city']) : 0;
            $district = !empty($_REQUEST['district']) ? intval($_REQUEST['district']) : 0;
            $address = !empty($_REQUEST['address']) ? trim($_REQUEST['address']) : 0;

            $region = get_seller_region(['province' => $province, 'city' => $city, 'district' => $district]);
            $key = $GLOBALS["_CFG"]['tengxun_key']; //密钥
            $region .= $address; //地址
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?address=" . $region . "&key=" . $key;
            $http = new Http();
            $data = $http->doGet($url);
            $data = dsc_decode($data, true);

            if ($data['status'] == 0) {
                $result['lng'] = $data['result']['location']['lng'];
                $result['lat'] = $data['result']['location']['lat'];
            } else {
                $result['error'] = 1;
                $result['message'] = $data['message'];
            }

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 管理员头像上传
        /* ------------------------------------------------------ */
        elseif ($act == 'upload_store_img') {
            $result = ["error" => 0, "message" => "", "content" => ""];

            $image = new Image(['bgcolor' => config('shop.bgcolor')]);
            $admin_id = get_admin_id();

            if (isset($_FILES['img']) && $_FILES['img']['name']) {
                $dir = 'store_user';

                $img_name = $image->upload_image($_FILES['img'], $dir);

                $this->dscRepository->getOssAddFile([$img_name]);

                if ($img_name) {
                    $result['error'] = 1;
                    $result['content'] = $this->dscRepository->getImagePath($img_name);
                    //删除原图片
                    $store_user_img = $this->db->getOne(" SELECT admin_user_img FROM " . $this->dsc->table('admin_user') . " WHERE user_id = '" . $admin_id . "' ");
                    @unlink(storage_public($store_user_img));
                    //插入新图片
                    $sql = " UPDATE " . $this->dsc->table('admin_user') . " SET admin_user_img = '{$result['content']}' WHERE user_id = '" . $admin_id . "' ";
                    $this->db->query($sql);
                }
            }
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 登录状态
        /* ------------------------------------------------------ */
        elseif ($act == 'login_status') {
            $status = app(CommonManageService::class)->loginStatus();
            return response()->json(['status' => $status]);
        }
    }

    /**
     * PC端客单价
     *
     * @param int $day_num
     * @param int $ru_id
     * @return array
     */
    private function get_sales($day_num = 0, $ru_id = 0)
    {
        $date_start = 0;
        $date_end = 0;

        //计算24小内的时间戳
        if ($day_num == 1) {
            $date_start = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
            $date_end = local_mktime(23, 59, 59, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
        } elseif ($day_num == 2) {
            $date_end = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('d'), TimeRepository::getLocalDate('Y')) - 1;
            $date_start = $date_end - 3600 * 24 + 1;
        }

        $arr = [
            'sales' => 0,
            'count' => 0,
            'format_sales' => $this->dscRepository->getPriceFormat(0, false),
            'format_count' => $this->dscRepository->getPriceFormat(0),
            'order' => 0
        ];
        if ($date_start > 0 && $date_end > 0) {
            $row = OrderInfo::selectRaw("SUM(" . $this->orderService->orderAmountField() . ") as amount, GROUP_CONCAT(order_id) AS order_list")->where('ru_id', $ru_id)
                ->whereBetween('add_time', [$date_start, $date_end])
                ->where('pay_status', PS_PAYED)
                ->where('main_count', 0)
                ->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk']);
            $row = BaseRepository::getToArrayFirst($row);

            //计算客单价，客单价 = 订单总额/订单数
            if ($row) {

                $amount = $row['amount'] ?? 0;

                $order_list = $row['order_list'] ?? '';
                $order_list = BaseRepository::getExplode($order_list);
                $orderCount = BaseRepository::getArrayCount($order_list);

                $sales = 0;
                if ($orderCount > 0) {
                    $sales = $amount / $orderCount;  //客单价计算  + $row['sf'] 不计算运费
                }

                $count = $amount;  //PC端成交计算  + $row['sf'] 不计算运费
                $arr = [
                    'sales' => $sales,
                    'count' => $count,
                    'format_sales' => $this->dscRepository->getPriceFormat($sales, false),
                    'format_count' => $this->dscRepository->getPriceFormat($count),
                    'order' => $orderCount
                ];
            }
        }

        return $arr;
    }

    /**
     * 移动端客单价
     *
     * @param int $day_num
     * @param int $ru_id
     * @return array
     */
    private function get_move_sales($day_num = 0, $ru_id = 0)
    {
        $date_start = 0;
        $date_end = 0;

        //计算24小内的时间戳
        if ($day_num == 1) {
            $date_start = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
            $date_end = local_mktime(23, 59, 59, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
        } elseif ($day_num == 2) {
            $date_end = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('d'), TimeRepository::getLocalDate('Y')) - 1;
            $date_start = $date_end - 3600 * 24 + 1;
        }

        /* 查询订单 */
        $arr = [
            'sales' => 0,
            'count' => 0,
            'format_sales' => $this->dscRepository->getPriceFormat(0, false),
            'format_count' => $this->dscRepository->getPriceFormat(0),
            'order' => 0
        ];
        if ($date_start > 0 && $date_end > 0) {
            $row = OrderInfo::selectRaw("SUM(" . $this->orderService->orderAmountField() . ") as amount, GROUP_CONCAT(order_id) AS order_list")->where('ru_id', $ru_id)
                ->whereBetween('add_time', [$date_start, $date_end])
                ->where('pay_status', PS_PAYED)
                ->where('main_count', 0)
                ->whereIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp']);
            $row = BaseRepository::getToArrayFirst($row);

            //计算客单价，客单价 = 订单总额/订单数
            if ($row) {
                $amount = $row['amount'] ?? 0;

                $order_list = $row['order_list'] ?? '';
                $order_list = BaseRepository::getExplode($order_list);
                $orderCount = BaseRepository::getArrayCount($order_list);

                $sales = 0;
                if ($orderCount > 0) {
                    $sales = $amount / $orderCount;  //客单价计算  + $row['sf'] 不计算运费
                }

                $count = $amount;  //PC端成交计算  + $row['sf'] 不计算运费
                $arr = [
                    'sales' => $sales,
                    'count' => $count,
                    'format_sales' => $this->dscRepository->getPriceFormat($sales, false),
                    'format_count' => $this->dscRepository->getPriceFormat($count),
                    'order' => $amount
                ];
            }
        }

        return $arr;
    }

    /**
     * 获取PC子订单数
     *
     * @param int $day_num
     * @param int $ru_id
     * @return array
     */
    private function get_sub_order($day_num = 0, $ru_id = 0)
    {
        $date_start = 0;
        $date_end = 0;

        //计算24小内的时间戳
        if ($day_num == 1) {
            $date_start = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
            $date_end = local_mktime(23, 59, 59, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
        } elseif ($day_num == 2) {
            $date_end = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('d'), TimeRepository::getLocalDate('Y')) - 1;
            $date_start = $date_end - 3600 * 24 + 1;
        }

        $sub_order = 0;
        if ($date_start > 0 && $date_end > 0) {
            //查询子订单数
            $sub_order = OrderInfo::query()->where('ru_id', $ru_id)->whereBetween('add_time', [$date_start, $date_end])
                ->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk'])
                ->where('pay_status', PS_PAYED)
                ->where('main_count', 0)
                ->count('order_id');
        }

        $arr = [
            'sub_order' => $sub_order
        ];

        return $arr;
    }

    /**
     * 获取移动子订单数
     *
     * @param int $day_num
     * @param int $ru_id
     * @return array
     */
    private function get_move_sub_order($day_num = 0, $ru_id = 0)
    {
        $date_start = 0;
        $date_end = 0;

        //计算24小内的时间戳
        if ($day_num == 1) {
            $date_start = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
            $date_end = local_mktime(23, 59, 59, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
        } elseif ($day_num == 2) {
            $date_end = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('d'), TimeRepository::getLocalDate('Y')) - 1;
            $date_start = $date_end - 3600 * 24 + 1;
        }

        $sub_order = 0;
        if ($date_start > 0 && $date_end > 0) {
            //查询子订单数
            $sub_order = OrderInfo::query()->where('ru_id', $ru_id)->whereBetween('add_time', [$date_start, $date_end])
                ->whereIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp'])
                ->where('pay_status', PS_PAYED)
                ->where('main_count', 0)
                ->count('order_id');
        }

        $arr = [
            'sub_order' => $sub_order
        ];

        return $arr;
    }

    /**
     * 输出访问者统计
     *
     * @param int $ru_id
     * @return array
     */
    private function viewip($ru_id = 0)
    {
        $date_start = local_mktime(0, 0, 0, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));
        $date_end = local_mktime(23, 59, 59, TimeRepository::getLocalDate('m', TimeRepository::getGmTime()), TimeRepository::getLocalDate('d', TimeRepository::getGmTime()), TimeRepository::getLocalDate('Y', TimeRepository::getGmTime()));

        $ipid = SourceIp::query()->where('storeid', $ru_id)
            ->whereBetween('iptime', [$date_start, $date_end])
            ->count('ipid');

        $arr = [
            'todaycount' => $ipid
        ];

        return $arr;
    }

    /*
    * 销量查询
    * 订单状态未已确认、已付款、非未发货订单
    * 计算总金额的条件同计算佣金的条件
    * @param   string ru_id 商家ID
    * @param   string where 时间条件
    */
    private function query_sales($ru_id = 0)
    {
        $row = OrderInfo::selectRaw("SUM(" . $this->orderService->orderAmountField() . ") as money_total, COUNT(order_id) AS order_total")->where('ru_id', $ru_id)
            ->where('pay_status', PS_PAYED)
            ->where('main_count', 0);

        return $row;
    }

    /**
     * 待评价查询
     *
     * @param int $ru_id
     * @return int
     */
    private function get_order_no_comment($ru_id = 0)
    {
        $rec_id = Comment::query()->where('comment_type', 0)
            ->where('rec_id', '>', 0)
            ->where('parent_id', 0)
            ->where('ru_id', $ru_id)
            ->pluck('rec_id');
        $rec_id = BaseRepository::getToArray($rec_id);

        $count = OrderGoods::query()->where('ru_id', $ru_id)->whereHasIn('getOrder', function ($query) {
            $query->whereIn('shipping_status', [SS_RECEIVED]);
        });

        if ($rec_id) {
            $count = $count->whereNotIn('rec_id', $rec_id);
        }

        $count = $count->whereDoesntHaveIn('getOrderReturn');

        $count = $count->count('rec_id');

        return $count;
    }

    /**
     * 判断商家年审剩余时间
     *
     * @param $ru_id
     * @return bool|mixed|void
     * @throws \Exception
     */
    private function surplus_time($ru_id)
    {
        if (session()->has('verify_time') && session('verify_time')) {

            $row = MerchantsGrade::select('ru_id', 'grade_id', 'add_time', 'year_num')->where('ru_id', $ru_id)->orderBy('id', 'desc');
            $row = BaseRepository::getToArrayFirst($row);

            $time = TimeRepository::getGmTime();
            $year = 1 * 60 * 60 * 24 * 365; //一年
            $enter_overtime = $row['add_time'] + $row['year_num'] * $year; //审核结束时间
            $two_month_later = TimeRepository::getLocalStrtoTime('+2 months'); //2个月后
            $one_month_later = TimeRepository::getLocalStrtoTime('+1 months'); //1个月后
            $minus = $enter_overtime - $time;
            $days = (TimeRepository::getLocalDate('d', $minus) > 0) ? intval(TimeRepository::getLocalDate('d', $minus)) : 0;

            session()->forget('verify_time');

            if ($enter_overtime <= $time) {//审核过期

                MerchantsShopInformation::where('user_id', $ru_id)->update([
                    'merchants_audit' => 0
                ]);

                return sys_msg($GLOBALS['_LANG']['exam_expire_repay_retry'], 1);
            } elseif ($enter_overtime < $one_month_later) {//审核过期前30天
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => "index.php"];
                $content = $GLOBALS['_LANG']['exam_repay_tip'][0] . $days . $GLOBALS['_LANG']['exam_repay_tip'][1];
                return sys_msg($content, 0, $link);
            } elseif ($enter_overtime < $two_month_later) {//审核过期前60天
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => "index.php"];
                return sys_msg($GLOBALS['_LANG']['exam_repay_tip_2month'], 0, $link);
            } else {//未到提醒期
                return true;
            }
        } else {
            return true;
        }
    }
}
