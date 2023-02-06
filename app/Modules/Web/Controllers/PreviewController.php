<?php

namespace App\Modules\Web\Controllers;

use App\Libraries\QRCode;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryService;
use App\Services\Comment\CommentService;
use App\Services\Merchant\MerchantCommonService;

class PreviewController extends InitController
{
    public $province_id;
    public $city_id;
    public $merchant_id;
    public $shop_id;
    public $temp_code;
    public $preview;
    public $tem;
    public $mershop_info;
    public $smarty;
    public $url;
    public $articleCommonService;
    protected $merchantCommonService;
    protected $commentService;
    protected $dscRepository;
    protected $categoryService;
    public $warehouse_id;
    public $area_id;
    public $area_city;

    public function __construct(
        CategoryService $categoryService,
        MerchantCommonService $merchantCommonService,
        CommentService $commentService,
        DscRepository $dscRepository
    )
    {
        $this->categoryService = $categoryService;
        $this->merchantCommonService = $merchantCommonService;
        $this->commentService = $commentService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        /**
         * @param $warehouse_id 仓库ID
         * @param $area_id 省份ID
         * @param $area_city 城市ID
         */
        $warehouse_id = $this->warehouse_id;
        $area_id = $this->area_id;
        $area_city = $this->area_city;
        /* End */

        //商家不存则跳转回首页
        if (($this->merchant_id == 0 || $this->shop_id < 1) && $this->temp_code == '') {
            return dsc_header("Location: index.php\n");
        }

        //判断店铺是否关闭
        if ($this->preview == 0) {
            if ($this->mershop_info['shop_close'] == 0) {
                //关闭则跳转首页
                return dsc_header("Location: index.php\n");
            }
        }

        //如果审核通过，判断店铺是否存在模板，不存在 导入默认模板
        $tpl_dir = storage_public(DATA_DIR . '/seller_templates/seller_tem_' . $this->merchant_id); //获取店铺模板目录
        $tpl_arr = get_dir_file_list($tpl_dir);
        if (empty($tpl_arr)) {
            $new_suffix = get_new_dir_name($this->merchant_id);
            $dir = storage_public(DATA_DIR . "/seller_templates/seller_tem/bucket_tpl"); //原目录
            $file = $tpl_dir . "/" . $new_suffix; //目标目录
            if (!empty($new_suffix)) {
                //新建目录
                if (!is_dir($dir)) {
                    make_dir($dir);
                }
                recurse_copy($dir, $file, 1);
                $result['error'] = 0;
            }

            SellerShopinfo::where('ru_id', $this->merchant_id)->update(['seller_templates' => $new_suffix]);
        }

        $basic_info = SellerShopinfo::where('ru_id', $this->merchant_id);

        $basic_info = $basic_info->with([
            'getSellerQrcode',
            'getMerchantsStepsFields'
        ]);

        $basic_info = BaseRepository::getToArrayFirst($basic_info);

        if (empty($basic_info)) {
            //关闭则跳转首页
            return dsc_header("Location: index.php\n");
        }

        if (empty($this->tem)) {
            /* 获取默认模板 */
            $tem = $basic_info['seller_templates'];
        } else {
            $tem = $this->tem;
        }

        /**
         * 店铺可视化
         */
        get_down_sellertemplates($this->merchant_id, $tem, $basic_info['seller_templates_time']);

        $pc_page = get_seller_templates($this->merchant_id, 1, $tem, $this->preview);//获取页面内容
        $pc_page['out'] = str_replace('../' . DATA_DIR . '/', DATA_DIR . "/", $pc_page['out'], $i);

        //OSS文件存储ecmoban模板堂 --zhuo start
        if (config('shop.open_oss') == 1) {
            $bucket_info = $this->dscRepository->getBucketInfo();
            $endpoint = $bucket_info['endpoint'];
        } else {
            $endpoint = url('/');
        }

        if ($pc_page['out']) {
            $desc_preg = get_goods_desc_images_preg($endpoint, $pc_page['out']);
            $pc_page['out'] = $desc_preg['goods_desc'];
        }

        $pc_page['temp'] = $tem;
        assign_template('', [], $this->merchant_id);
        $shop_name = $this->merchantCommonService->getShopName($this->merchant_id, 1); //店铺名称
        $grade_info = get_seller_grade($this->merchant_id); //等级信息
        $position = assign_ur_here(0, $shop_name);
        $this->smarty->assign('page_title', $position['title']);    // 页面标题
        $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置
        $this->smarty->assign('helps', $this->articleCommonService->getShopHelp());       // 网店帮助
        $this->smarty->assign('pc_page', $pc_page);
        $build_uri = [
            'urid' => $this->merchant_id,
            'append' => $shop_name
        ];

        $domain_url = $this->merchantCommonService->getSellerDomainUrl($this->merchant_id, $build_uri);
        $merchants_url = $domain_url['domain_name'];
        $this->smarty->assign('merchants_url', $merchants_url);  //网站域名

        $merchants_goods_comment = [];
        if ($this->merchant_id > 0) {
            $merchants_goods_comment = $this->commentService->getMerchantsGoodsComment($this->merchant_id); //商家所有商品评分类型汇总
        }

        $this->smarty->assign('merch_cmt', $merchants_goods_comment);
        $this->smarty->assign('shop_name', $shop_name);
        $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
        $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版

        $store_category = get_user_store_category($this->merchant_id); //店铺导航栏
        $this->smarty->assign('store_category', $store_category);

        //商家二维码 by wu start
        $basic_info = isset($basic_info['get_seller_qrcode']) && $basic_info['get_seller_qrcode'] ? array_merge($basic_info, $basic_info['get_seller_qrcode']) : $basic_info;
        $basic_info = isset($basic_info['get_merchants_steps_fields']) && $basic_info['get_merchants_steps_fields'] ? array_merge($basic_info, $basic_info['get_merchants_steps_fields']) : $basic_info;

        $logo = $basic_info && isset($basic_info['qrcode_thumb']) ? str_replace('../', '', $basic_info['qrcode_thumb']) : '';
        $logo = $this->merchantCommonService->getLogoPath($logo);
        $url = $this->url;
        $data = $url . "mobile/#/shopHome/" . $this->merchant_id;

        $seller_qrcode = IMAGE_DIR . "/seller_imgs/seller_qrcode/seller_qrcode_" . $this->merchant_id . ".png";
        $filename = storage_public($seller_qrcode);

        if (!is_dir($filename)) {
            $filenameUrl = $this->dscRepository->getImagePath($seller_qrcode);
            $this->dscRepository->getHttpBasename($filenameUrl, storage_public(IMAGE_DIR . "/seller_imgs/seller_qrcode/"));
        }

        $linkExists = $this->dscRepository->remoteLinkExists($logo);

        if (!$linkExists) {
            $logo = null;
        }

        if (!file_exists($filename)) {
            QRCode::png($data, $filename, $logo);
        }

        $this->dscRepository->getOssAddFile([$seller_qrcode]);

        $this->smarty->assign('seller_qrcode_img', $this->dscRepository->getImagePath($seller_qrcode));

        if ($basic_info) {
            $this->smarty->assign('seller_qrcode_text', $basic_info['shop_name']);
            //商家二维码 by wu end

            // 统一客服
            if (config('shop.customer_service') == 0) {
                $shop_info = SellerShopinfo::where('ru_id', 0)->first();
                $shop_info = $shop_info ? $shop_info->toArray() : [];

                $basic_info['kf_qq'] = $shop_info['kf_qq'] ?? '';
                $basic_info['kf_ww'] = $shop_info['kf_ww'] ?? '';
            }

            $chat = $this->dscRepository->chatQq($basic_info);
            $basic_info['kf_qq'] = $chat['kf_qq'];
            $basic_info['kf_ww'] = $chat['kf_ww'];
        }

        if (config('shop.customer_service') == 0) {
            $im_merchant_id = 0;
        } else {
            $im_merchant_id = $this->merchant_id;
        }

        /*  @author-bylu 判断当前商家是否允许"在线客服" start */
        $shop_information = $this->merchantCommonService->getShopName($im_merchant_id);

        //判断当前商家是平台,还是�        �驻商家 bylu
        if ($im_merchant_id == 0) {
            //判断平台是否开启了IM在线客服
            $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
            if ($kf_im_switch) {
                $shop_information['is_dsc'] = true;
            } else {
                $shop_information['is_dsc'] = false;
            }
        } else {
            $shop_information['is_dsc'] = false;
        }

        $this->smarty->assign('shop_information', $shop_information);

        $cat_list = $this->categoryService->getMerchantsCatList(0, $this->merchant_id);
        $this->smarty->assign('cat_store_list', $cat_list);
        $this->smarty->assign('basic_info', $basic_info);  //店铺详细信息
        $this->smarty->assign('grade_info', $grade_info);
        $this->smarty->assign('site_domain', url('/'));  //网站域名
        $this->smarty->assign('merchant_id', $this->merchant_id);
        $this->smarty->assign('warehouse_id', $warehouse_id);
        $this->smarty->assign('area_id', $area_id);
        $this->smarty->assign('area_city', $area_city);
        $this->smarty->assign('temp_code', $this->temp_code);

        //获取seo start
        $seo = get_seo_words('shop');

        if ($seo) {
            foreach ($seo as $key => $value) {
                $seo[$key] = str_replace(['{sitename}', '{key}', '{shopname}', '{description}'], [config('shop.shop_name'), $basic_info['shop_keyword'], $basic_info['shop_title'], $basic_info['street_desc']], $value);
            }
        }

        if (isset($seo['keywords']) && !empty($seo['keywords'])) {
            $this->smarty->assign('keywords', htmlspecialchars($seo['keywords']));
        } else {
            $this->smarty->assign('keywords', htmlspecialchars(config('shop.shop_keywords')));
        }

        if (isset($seo['description']) && !empty($seo['description'])) {
            $this->smarty->assign('description', htmlspecialchars($seo['description']));
        } else {
            $this->smarty->assign('description', htmlspecialchars(config('shop.shop_desc')));
        }

        if (isset($seo['title']) && !empty($seo['title'])) {
            $this->smarty->assign('page_title', htmlspecialchars($seo['title']));
        } else {
            $this->smarty->assign('page_title', $position['title']);
        }
        //获取seo end

        return $this->smarty->display('preview.dwt');
    }
}
