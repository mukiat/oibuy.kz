<?php

namespace App\Modules\Web\Controllers;

use App\Exceptions\HttpException;
use App\Extensions\File;
use App\Libraries\CaptchaVerify;
use App\Libraries\Image;
use App\Models\CollectBrand;
use App\Models\CollectGoods;
use App\Models\CommentSeller;
use App\Models\Complaint;
use App\Models\ComplaintImg;
use App\Models\ComplainTitle;
use App\Models\ComplaintTalk;
use App\Models\GoodsReport;
use App\Models\GoodsReportImg;
use App\Models\OrderDelayed;
use App\Models\OrderInfo;
use App\Models\Users;
use App\Proxy\ShippingProxy;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Activity\AuctionService;
use App\Services\Activity\SnatchService;
use App\Services\Cart\CartCommonService;
use App\Services\Comment\CommentService;
use App\Services\Common\CommonService;
use App\Services\Flow\FlowMobileService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderService;
use App\Services\Seckill\SeckillInsertService;
use App\Services\User\UserCommonService;
use App\Services\User\UserInsertService;
use App\Services\User\UserOrderService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;

/**
 * 会员中心异步操作
 */
class AjaxUserController extends InitController
{
    protected $commentService;
    protected $userService;
    protected $dscRepository;
    protected $userInsertService;
    protected $seckillInsertService;
    protected $auctionService;
    protected $snatchService;
    protected $userCommonService;
    protected $cartCommonService;
    protected $userOrderService;
    protected $orderService;
    protected $commonService;
    protected $shippingProxy;

    public function __construct(
        CommentService $commentService,
        UserService $userService,
        DscRepository $dscRepository,
        UserInsertService $userInsertService,
        SeckillInsertService $seckillInsertService,
        AuctionService $auctionService,
        SnatchService $snatchService,
        UserCommonService $userCommonService,
        CartCommonService $cartCommonService,
        UserOrderService $userOrderService,
        OrderService $orderService,
        CommonService $commonService,
        ShippingProxy $shippingProxy
    )
    {
        $this->commentService = $commentService;
        $this->userService = $userService;
        $this->dscRepository = $dscRepository;
        $this->userInsertService = $userInsertService;
        $this->seckillInsertService = $seckillInsertService;
        $this->auctionService = $auctionService;
        $this->snatchService = $snatchService;
        $this->userCommonService = $userCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->userOrderService = $userOrderService;
        $this->orderService = $orderService;
        $this->commonService = $commonService;
        $this->shippingProxy = $shippingProxy;
    }

    public function index()
    {
        if ($this->checkReferer() === false) {
            return response()->json(['error' => 1, 'message' => 'referer error']);
        }

        /**
         * Start
         *
         * @param $warehouse_id 仓库ID
         * @param $area_id 省份ID
         * @param $area_city 城市ID
         */
        $warehouse_id = $this->warehouseId();
        $area_id = $this->areaId();
        $area_city = $this->areaCity();
        /* End */

        /* 载入语言文件 */
        $this->dscRepository->helpersLang('user');
        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        load_helper(['clips', 'order', 'transaction', 'payment']);

        $user_id = session('user_id', 0);

        $result = ['error' => 0, 'message' => '', 'content' => ''];

        //jquery Ajax跨域
        $is_jsonp = intval(request()->input('is_jsonp', 0));
        $act = e(trim(request()->input('act', '')));

        /*------------------------------------------------------ */
        //-- 我的评价 弹窗
        /*------------------------------------------------------ */
        if ($act == 'comments_form') {
            $rec_id = intval(request()->input('rec_id', 0));
            $sign = intval(request()->input('sign', 0));

            $is_add_evaluate = (int)request()->input('is_add_evaluate', 0); // 0：首次评论 1：追加评论

            /* 验证码相关设置 */
            if ((intval(config('shop.captcha')) & CAPTCHA_COMMENT) && gd_version() > 0) {
                $this->smarty->assign('enabled_captcha', 1);
                $this->smarty->assign('rand', mt_rand());
            }

            try {
                $data = $this->commentService->getOrderGoods($rec_id, $user_id, $is_add_evaluate);
            } catch (HttpException $httpException) {
                return response()->json(['error' => 1, 'message' => $httpException->getMessage()]);
            }

            $shop_info = [];
            $ru_id = $data['ru_id'] ?? 0;
            $degree_count = $data['degree_count'] ?? 0; // 同一商家订单仅一次满意度评价
            if ($ru_id > 0 && $degree_count == 0) {
                // 商家满意度评价
                $shop_information = app(MerchantCommonService::class)->getShopName($ru_id);
                if ($shop_information) {
                    $domain_url = app(MerchantCommonService::class)->getSellerDomainUrl($ru_id, ['urid' => $ru_id, 'append' => $shop_information['shop_name']]);

                    $shop_info = [
                        'logo_thumb' => $this->dscRepository->getImagePath(substr($shop_information['logo_thumb'], 3)),
                        'seller_url' => $domain_url,
                        'shop_name' => $shop_information['shop_name'],
                        'kf_tel' => $shop_information['kf_tel'],
                        'merchants_goods_comment' => $this->commentService->getMerchantsGoodsComment($ru_id), //商家所有商品评分类型汇总
                    ];

                    //商家总和评分
                    $shop_info['seller_score'] = 5;
                    $seller_row = CommentSeller::selectRaw("SUM(service_rank) + SUM(desc_rank) + SUM(delivery_rank) + SUM(sender_rank) AS sum_rank, count(*) as num")
                        ->where('ru_id', $ru_id)
                        ->first();
                    $seller_row = $seller_row ? $seller_row->toArray() : [];
                    if ($seller_row && $seller_row['num']) {
                        $shop_info['seller_score'] = ($seller_row['sum_rank'] / $seller_row['num']) / 4;
                    }
                }
            }

            $this->smarty->assign('item', $data);
            $this->smarty->assign('shop_info', $shop_info);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('sessid', SESS_ID);
            $this->smarty->assign('sign', $sign);
            $this->smarty->assign('is_add_evaluate', $is_add_evaluate);

            //剔除未保存晒单图
            $this->commentService->deleteCommentImgList($user_id);

            $result['content'] = $this->smarty->fetch('library/comments_form.lbi');
        }

        /*------------------------------------------------------ */
        //-- 上传会员图片
        /*------------------------------------------------------ */
        elseif ($act == 'upload_user_picture') {
            $filename = request()->input('image', '');

            if ($filename && $user_id > 0) {
                $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

                $filename_cropper = storage_public(DATA_DIR . "/images_user/cropper/" . $user_id . "_cropper.jpg"); //剪裁后未压缩的图片
                $route = storage_public(DATA_DIR . "/images_user/");

                $filename_arr = $filename ? explode(',', $filename) : [];

                if (!empty($filename_arr)) {
                    if (!file_exists(storage_public(DATA_DIR . "/images_user/cropper/"))) {
                        make_dir(storage_public(DATA_DIR . "/images_user/cropper/"));
                    }

                    $somecontent1 = base64_decode($filename_arr[1]);

                    if (@$handle = fopen($filename_cropper, "w+")) {
                        if (!fwrite($handle, $somecontent1) == false) {
                            fclose($handle);
                        }
                    }

                    $filename_120 = $image->make_thumb($filename_cropper, 120, 120, $route, '', $user_id . "_120.jpg"); //48*48头像小图
                    $filename_48 = $image->make_thumb($filename_cropper, 48, 48, $route, '', $user_id . "_48.jpg"); //48*48头像小图
                    $filename_24 = $image->make_thumb($filename_cropper, 24, 24, $route, '', $user_id . "_24.jpg"); //24*24头像小图

                    $data_path = storage_public();
                    $filename_120 = $filename_120 ? str_replace($data_path, '', $filename_120) : '';
                    $filename_48 = $filename_48 ? str_replace($data_path, '', $filename_48) : '';
                    $filename_24 = $filename_24 ? str_replace($data_path, '', $filename_24) : '';

                    dsc_unlink($filename_cropper);

                    $parent['user_picture'] = $filename_120;
                    Users::where('user_id', $user_id)->update($parent);

                    $this->dscRepository->getOssAddFile([$filename_120, $filename_48, $filename_24]);

                    $result['file'] = $this->dscRepository->getImagePath($filename_120);
                    $result['result'] = $GLOBALS['_LANG']['upload_success'];
                    $result['error'] = 'ok';

                    //记录会员操作日志
                    $this->userCommonService->usersLogChange($user_id, USER_PICT);
                } else {
                    $result['result'] = $GLOBALS['_LANG']['unknown_error'];
                }
            } else {
                $result['result'] = $GLOBALS['_LANG']['overdue_login'];
            }
        }

        /*------------------------------------------------------ */
        //-- 切换举报主题
        /*------------------------------------------------------ */
        elseif ($act == 'checked_report_title') {
            $type_id = intval(request()->input('type_id', 0));

            $report_title = get_goods_report_title($type_id);
            $result = '<li><a href="javascript:void(0);" data-value="">' . $GLOBALS['_LANG']['Please_select'] . '</a></li>';
            if ($report_title) {
                foreach ($report_title as $k => $v) {
                    $result .= '<li><a href="javascript:void(0);" data-value="' . $v['title_id'] . '">' . $v['title_name'] . '</a></li>';
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 上传举报证据
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_report_img') {
            $goods_id = intval(request()->input('goods_id', 0));
            $img_file = isset($_FILES['file']) ? $_FILES['file'] : [];

            if ($user_id > 0) {
                $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

                $img_file = $image->upload_image($img_file, 'report_img/' . date('Ym')); //原图
                if ($img_file === false) {
                    $result['error'] = 1;
                    $result['message'] = $image->error_msg();
                    return response()->json($result);
                }

                $this->dscRepository->getOssAddFile([$img_file]);

                $report = [
                    'goods_id' => $goods_id,
                    'user_id' => $user_id,
                    'img_file' => $img_file
                ];

                $img_count = GoodsReportImg::where('user_id', $user_id)->where('goods_id', $goods_id)->count();

                if ($img_count < 5 && $img_file) {
                    GoodsReportImg::insert($report);
                } else {
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['report_img_number'];
                    return response()->json($result);
                }
            } else {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['overdue_login'];
            }

            $reportOther = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'report_id' => 0
            ];

            $img_list = $this->userService->getGoodsReportImgList($reportOther);

            $this->smarty->assign('img_list', $img_list);
            $this->smarty->assign('report', 1);
            $result['content'] = $this->smarty->fetch("library/comment_image.lbi");
        }

        /*------------------------------------------------------ */
        //-- 删除图片
        /*------------------------------------------------------ */
        elseif ($act == 'del_reportpic') {
            $img_id = intval(request()->input('re_imgId', 0));
            $goods_id = intval(request()->input('goods_id', 0));
            $order_id = intval(request()->input('order_id', 0));
            $complaint = intval(request()->input('complaint', 0));

            if ($user_id > 0 || $img_id > 0) {
                //订单投诉
                if ($complaint > 0) {
                    $report = 2;
                    //获取投诉相册

                    $complaintOther = [
                        'user_id' => $user_id,
                        'order_id' => $order_id
                    ];

                    $img_list = $this->userService->getComplaintImgList($complaintOther);
                } else {
                    $report = 1;
                    //商品举报

                    $reportOther = [
                        'user_id' => $user_id,
                        'goods_id' => $goods_id
                    ];

                    $img_list = $this->userService->getGoodsReportImgList($reportOther);
                }

                if (!empty($img_list)) {
                    foreach ($img_list as $key => $val) {
                        if ($img_id == $val['id']) {
                            if ($complaint > 0) {
                                ComplaintImg::where('img_id', $img_id)->delete();
                            } else {
                                GoodsReportImg::where('img_id', $img_id)->delete();
                            }

                            unset($img_list[$key]);

                            $this->dscRepository->getOssDelFile([$val['img_file']]);

                            dsc_unlink(storage_public($val['img_file']));
                        }
                    }
                }

                $this->smarty->assign('img_list', $img_list);
                $this->smarty->assign('report', $report);
                $result['content'] = $this->smarty->fetch("library/comment_image.lbi");
            } else {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['overdue_login'];
            }
        }

        /*------------------------------------------------------ */
        //-- 切换举报状态
        /*------------------------------------------------------ */
        elseif ($act == 'check_report_state') {
            $report_id = intval(request()->input('report_id', 0));
            $state = intval(request()->input('state', 0));
            if ($user_id > 0) {
                GoodsReport::where('report_id', $report_id)->update(['report_state' => $state]);
            } else {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['overdue_login'];
            }
        }

        /*------------------------------------------------------ */
        //-- 获取举报类型描述
        /*------------------------------------------------------ */
        elseif ($act == 'complaint_title_desc') {
            $title_id = intval(request()->input('title_id', 0));
            if ($user_id > 0) {
                $result['content'] = ComplainTitle::where('title_id', $title_id)->value('title_desc');
            } else {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['overdue_login'];
            }
        }

        /*------------------------------------------------------ */
        //-- 上传交易纠纷图片
        /*------------------------------------------------------ */
        elseif ($act == 'complaint_img') {
            $order_id = intval(request()->input('order_id', 0));
            $img_file = isset($_FILES['file']) ? $_FILES['file'] : [];

            if ($user_id > 0) {
                $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

                $img_file = $image->upload_image($img_file, 'complaint_img/' . date('Ym')); //原图
                if ($img_file === false) {
                    $result['error'] = 1;
                    $result['message'] = $image->error_msg();
                    return response()->json($result);
                }

                $this->dscRepository->getOssAddFile([$img_file]);

                $report = [
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                    'img_file' => $img_file
                ];

                $img_count = ComplaintImg::where('user_id', $user_id)->where('order_id', $order_id)->count();

                if ($img_count < 5 && $img_file) {
                    ComplaintImg::insert($report);
                } else {
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['report_img_number'];
                    return response()->json($result);
                }
            } else {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['overdue_login'];
            }

            $complaintOther = [
                'user_id' => $user_id,
                'order_id' => $order_id,
                'complaint_id' => 0
            ];

            $img_list = $this->userService->getComplaintImgList($complaintOther);

            $this->smarty->assign('img_list', $img_list);
            $this->smarty->assign('report', 2);
            $result['content'] = $this->smarty->fetch("library/comment_image.lbi");
        }

        /*------------------------------------------------------ */
        //-- 发布聊天
        /*------------------------------------------------------ */
        elseif ($act == 'talk_release') {
            $complaint_id = intval(request()->input('complaint_id', 0));
            $talk_content = trim(request()->input('talk_content', ''));
            $type = intval(request()->input('type', 0));

            //执行操作类型  1、刷新，0入库
            if ($type == 0) {
                $complaint_talk = [
                    'complaint_id' => $complaint_id,
                    'talk_member_id' => $user_id,
                    'talk_member_name' => session('user_name', ''),
                    'talk_member_type' => 1,
                    'talk_content' => $talk_content,
                    'talk_time' => gmtime(),
                    'view_state' => 'user'
                ];

                ComplaintTalk::insert($complaint_talk);
            }

            $talk_list = checkTalkView($complaint_id, 'user');
            $this->smarty->assign('talk_list', $talk_list);
            $result['content'] = $this->smarty->fetch("library/talk_list.lbi");
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除订单投诉
        /*------------------------------------------------------ */
        elseif ($act == 'del_compalint') {
            $complaint_id = intval(request()->input('compalint_id', 0));
            if ($user_id > 0) {
                //删除相关图片
                del_complaint_img($complaint_id);

                del_complaint_img($complaint_id, 'appeal_img');

                //删除相关聊天
                del_complaint_talk($complaint_id);

                Complaint::where('complaint_id', $complaint_id)->delete();
            } else {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['overdue_login'];
            }
        }

        /*------------------------------------------------------ */
        //-- 订单分页查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_order_gotopage') {
            $id = json_str_iconv(request()->input('id', []));
            $page = intval(request()->input('page', 1));
            $show_type = intval(request()->input('type', 0));

            if ($id) {
                $id = explode("=", $id);
            }

            $user_id = $id[0];

            $id = isset($id[1]) ? explode("|", $id[1]) : '';
            $order = $this->dscRepository->getStrArray1($id);

            if ($show_type == 1) {
                $order->action = "order_recycle";
            } else {
                $order->action = "order_list";
            }

            $size = 10;
            $where = [
                'user_id' => $user_id,
                'show_type' => $show_type,
                'is_zc_order' => 0,
                'page' => $page,
                'size' => $size
            ];

            $record_count = $this->userOrderService->getUserOrdersCount($where, $order);

            $where['record_count'] = $record_count;
            $orders = $this->userOrderService->getUserOrdersList($where, $order);

            $lang = array_merge(lang('user'), $GLOBALS['_LANG']);

            $this->smarty->assign('lang', $lang);
            $this->smarty->assign('orders', $orders);
            $this->smarty->assign('action', $order->action);
            $this->smarty->assign('open_delivery_time', config('shop.open_delivery_time'));

            $result['content'] = $this->smarty->fetch("library/user_order_list.lbi");
        }

        /*------------------------------------------------------ */
        //-- 拍卖订单分页查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_auction_order_gotopage') {
            $id = json_str_iconv(request()->input('id', []));
            $page = intval(request()->input('page', 1));
            $type = 0;

            if ($id) {
                $id = explode("=", $id);
            }

            $order = (object)[];
            if (count($id) > 1) {
                $user_id = $id[0];

                $id = explode("|", $id[1]);
                $order = $this->dscRepository->getStrArray1($id);

                $record_count = OrderInfo::where('main_count', 0)
                    ->searchKeyword($order)
                    ->where('user_id', $user_id)
                    ->where('is_delete', $type)
                    ->where('extension_code', 'auction');

                $record_count = $record_count->count();
            } else {
                $user_id = $id[0];

                $record_count = OrderInfo::where('main_count', 0)
                    ->where('user_id', $user_id)
                    ->where('is_delete', $type)
                    ->where('extension_code', 'auction');

                $record_count = $record_count->count();
            }

            $order->action = "auction";
            $size = 10;
            $where = [
                'user_id' => $user_id,
                'show_type' => $type,
                'is_zc_order' => 0,
                'page' => $page,
                'size' => $size
            ];

            $where['record_count'] = $record_count;
            $orders = $this->userOrderService->getUserOrdersList($where, $order);

            $lang = array_merge(lang('user'), $GLOBALS['_LANG']);
            $this->smarty->assign('lang', $lang);
            $this->smarty->assign('orders', $orders);
            $this->smarty->assign('action', $order->action);
            $this->smarty->assign('open_delivery_time', config('shop.open_delivery_time'));

            $result['content'] = $this->smarty->fetch("library/user_order_list.lbi");
        }

        /* ------------------------------------------------------ */
        //-- 加载会员信息栏
        /* ------------------------------------------------------ */
        elseif ($act == 'getUserInfo') {
            $temp = trim(request()->input('temp', ''));

            $seckillid = stripslashes(request()->input('seckillid', ''));
            $seckillid = json_str_iconv($seckillid);
            if (!empty($seckillid)) {
                $seckillid = dsc_decode($seckillid, true);
            }

            $brand_id = trim(request()->input('brand_id', ''));
            $brand_id = DscEncryptRepository::filterValInt($brand_id);

            $this->smarty->assign('login_right_link', config('shop.login_right_link') ?? '');
            $this->smarty->assign('login_right', config('shop.login_right') ?? '');
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('info', $this->userCommonService->getUserDefault($user_id));
            $this->smarty->assign('site_domain', config('shop.site_domain'));
            $this->smarty->assign('shop_name', config('shop.shop_name'));

            $this->smarty->assign('temp', $temp);
            if ($temp == 'backup_festival_1') {
                $arr['num'] = 29;
            } else {
                $arr['num'] = 17;
            }

            $result['brand_list'] = $this->userInsertService->insertRecommendBrands($arr, $brand_id);
            $result['seckill_goods'] = $this->seckillInsertService->insertIndexSeckillGoods($seckillid, $temp);
            $result['content'] = $this->smarty->fetch('library/user_info.lbi');
        }

        /*------------------------------------------------------ */
        //-- 拍卖活动列表查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_auction_gotopage') {
            $id = json_str_iconv(request()->input('id', []));
            $page = intval(request()->input('page', 1));

            if ($id) {
                $id = explode("=", $id);
            }

            $user_id = $id[0];

            if (count($id) == 1) {
                $auction = '';
            } else {
                $id = explode("|", $id[1]);
                $auction = $this->dscRepository->getStrArray1($id);
                $auction->action = "auction_list";
            }

            $all_auction = $this->auctionService->getAllAuction($user_id, $auction);

            $auction_list = $this->auctionService->getAuctionGoodsList($user_id, $all_auction, $page, $auction);

            $this->smarty->assign('lang', lang('common'));
            $this->smarty->assign('auction_list', $auction_list);
            $this->smarty->assign('action', 'auction_list');

            $result['content'] = $this->smarty->fetch("library/user_auction_list.lbi");
        }

        /*------------------------------------------------------ */
        //-- 夺宝奇兵列表查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_snatch_gotopage') {
            $id = json_str_iconv(request()->input('id', []));
            $page = intval(request()->input('page', 1));

            if ($id) {
                $id = explode("=", $id);
            }

            $user_id = $id[0];

            //区分全部夺宝订单还是有状态的夺宝订单
            if (count($id) == 1) {
                $snatch = '';
            } else {
                $id = explode("|", $id[1]);
                $snatch = $this->dscRepository->getStrArray1($id);
                $snatch->action = "snatch_list";
            }

            $all_snatch = $this->snatchService->getAllSnatch($user_id, $snatch);

            $snatch_list = $this->snatchService->getSnatchGoodsList($user_id, $all_snatch, $page, $snatch);


            $this->smarty->assign('lang', lang('common'));
            $this->smarty->assign('snatch_list', $snatch_list);
            $this->smarty->assign('action', 'snatch_list');

            $result['content'] = $this->smarty->fetch("library/user_snatch_list.lbi");
        }

        /* ------------------------------------------------------ */
        //-- 验证用户是否注册
        /* ------------------------------------------------------ */
        elseif ($act == 'is_registered') {
            load_helper('passport');
            $username = json_str_iconv(trim(request()->input('username', '')));
            $password = json_str_iconv(request()->input('password', ''));

            if ($GLOBALS['user']->check_user($username, $password)) {
                $error = false;
            } else {
                $error = true;
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //-- 验证用户第三登录绑定用户是否存在
        /* ------------------------------------------------------ */
        elseif ($act == 'is_user') {
            load_helper('passport');

            $result = ['result' => '', 'error' => 0, 'mode' => 0];

            $username = json_str_iconv(trim(request()->input('username', '')));
            $password = json_str_iconv(request()->input('password', ''));

            if ($GLOBALS['user']->check_user($username, $password)) {
                $result['result'] = 'true';
            } else {
                $result['result'] = 'false';
            }
        }

        /* ------------------------------------------------------ */
        //-- 验证登录验证码是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'is_login_captcha') {
            load_helper('passport');

            $result = ['result' => 'true', 'error' => 0, 'mode' => 0];

            $captcha_str = addslashes(trim(request()->input('captcha', '')));

            $captcha = intval(config('shop.captcha'));
            if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && session('login_fail') > 2)) && gd_version() > 0) {
                $verify = app(CaptchaVerify::class);
                $captcha_code = $verify->check($captcha_str, 'captcha_login');

                if (!$captcha_code) {
                    $result['result'] = 'false';
                    $result['message'] = $GLOBALS['_LANG']['invalid_captcha'];
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 验证注册验证码是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'is_register_captcha') {
            load_helper('passport');

            $result = ['result' => '', 'error' => 0, 'mode' => 0];

            $captcha = trim(request()->input('captcha', ''));
            /* 验证码检查 */
            if ((intval(config('shop.captcha')) & CAPTCHA_REGISTER) && gd_version() > 0) {
                $seKey = 'mobile_phone';
                $verify = app(CaptchaVerify::class);
                $captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

                if (!$captcha_code) {
                    $result['result'] = 'false';
                    $result['message'] = $GLOBALS['_LANG']['invalid_captcha'];
                } else {
                    $result['result'] = 'true';
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 验证注册短信验证码是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'is_mobile_code') {
            load_helper('passport');

            $result = ['result' => '', 'error' => 0, 'mode' => 0];

            $mobile_code = trim(request()->input('mobile_code', ''));

            if ($mobile_code != session('sms_mobile_code')) {
                $result['result'] = 'false';
            } else {
                $result['result'] = 'true';
            }
        }

        /* ------------------------------------------------------ */
        //-- 手机号是否被注册
        /* ------------------------------------------------------ */
        elseif ($act == 'is_mobile_phone') {
            load_helper('passport');

            $phone = json_str_iconv(trim(request()->input('phone', '')));

            if ($GLOBALS['user']->check_mobile_phone($phone)) {
                $result['error'] = 'false';
            } else {
                $result['error'] = 'true';
            }
        }

        /* ------------------------------------------------------ */
        //-- 验证用户邮箱地址是否被注册
        /* ------------------------------------------------------ */
        elseif ($act == 'check_email') {
            load_helper('passport');

            $email = trim(request()->input('email', ''));

            if ($GLOBALS['user']->check_email($email)) {
                $error = false;
            } else {
                $error = true;
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //-- 验证手机注册是否已存在
        /* ------------------------------------------------------ */
        elseif ($act == 'check_phone') {
            load_helper('passport');

            $mobile_phone = trim(request()->input('mobile_phone', ''));
            if ($GLOBALS['user']->check_mobile_phone($mobile_phone)) {
                $error = false;
            } else {
                $error = true;
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //-- 手机注册  验证短信验证码是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'code_notice') {
            load_helper('passport');

            $error = true;

            $mobile_code = trim(request()->input('mobile_code', ''));

            $sms_security_code = session('sms_mobile_code', '');

            if (!empty($mobile_code)) {
                if ($mobile_code != $sms_security_code) {
                    $error = false;
                }
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //-- AJAX验证验证码是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'checkd_email_send_code') {
            $code = request()->input('send_code', '');
            if (session('user_email_verify') == $code) {
                $error = true;
            } else {
                $error = false;
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //-- 验证邮箱注册验证码是否是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'captchas') {
            $captcha = trim(request()->input('captcha', ''));
            if ((intval(config('shop.captcha'))) && gd_version() > 0) {
                if (empty($captcha)) {
                    $result['error'] = 1;  // 为空
                } else {
                    $seKey = 'register_email';
                    $verify = app(CaptchaVerify::class);
                    $captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

                    if (!$captcha_code) {
                        $result['error'] = 2;  // 验证码错误
                    } else {
                        $result['error'] = 3;  // 验证码正确
                    }
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 邮箱验证找回密码验证码是否是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'captchas_pass') {
            $captcha = trim(request()->input('captcha', ''));

            $error = true;

            if (intval(config('shop.captcha')) && gd_version() > 0) {
                if (!empty($captcha)) {
                    $seKey = request()->input('seKey', '');

                    $verify = app(CaptchaVerify::class);
                    $captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

                    if (!$captcha_code) {
                        $error = false;  // 验证码错误
                    } else {
                        $error = true;  // 验证码正确
                    }
                }
            }

            return response()->json($error);
        }

        /* ------------------------------------------------------ */
        //-- 验证手机注册验证码是否是否正确
        /* ------------------------------------------------------ */
        elseif ($act == 'phone_captcha') {
            $captcha = trim(request()->input('captcha', ''));

            load_helper('passport');

            $seKey = 'mobile_phone';
            $verify = app(CaptchaVerify::class);
            $captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

            if (!$captcha_code) {
                $error = false;  // 验证码错误
            } else {
                $error = true;  // 验证码正确
            }

            return response()->json($error);
        }

        /*------------------------------------------------------ */
        //-- 验证支付密码
        /*------------------------------------------------------ */
        elseif ($act == 'pay_pwd') {
            $result = ['error' => 0, 'err_msg' => '', 'content' => ''];

            $_POST = get_request_filter($_POST, 1);

            $pay_pwd = addslashes(trim(request()->input('pay_pwd', '')));

            $users_paypwd = $this->userService->getPaypwd(session('user_id'));

            if (config('shop.use_paypwd') == 1) {
                // 加密
                $ec_salt = $users_paypwd ? $users_paypwd['ec_salt'] : 0;
                $new_password = md5(md5($pay_pwd) . $ec_salt);

                if (empty($pay_pwd)) {
                    $result['error'] = 1;
                } elseif (isset($users_paypwd['pay_password']) && $new_password != $users_paypwd['pay_password']) {
                    $result['error'] = 2;
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 处理会员的登录
        /* ------------------------------------------------------ */
        elseif ($act == 'act_login') {
            $result = $this->userCommonService->actLogin();
        }

        /* ------------------------------------------------------ */
        //-- 处理 ajax 的登录请求
        /* ------------------------------------------------------ */
        elseif ($act == 'signin') {
            $_POST = get_request_filter($_POST, 1);

            $username = json_str_iconv(trim(request()->input('username', '')));
            $password = trim(request()->input('password', ''));

            $result = ['error' => 0, 'content' => ''];

            $captcha = intval(config('shop.captcha'));
            if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && session('login_fail') > 2)) && gd_version() > 0) {
                if (empty($captcha)) {
                    $result['error'] = 1;
                    $result['content'] = $GLOBALS['_LANG']['invalid_captcha'];
                    return response()->json($result);
                }

                $captcha_str = trim(request()->input('captcha', ''));

                $verify = app(CaptchaVerify::class);
                $captcha_code = $verify->check($captcha_str, 'captcha_login');

                if (!$captcha_code) {
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['invalid_captcha'];
                    return response()->json($result);
                }
            }

            if ($GLOBALS['user']->login($username, $password)) {
                $this->userCommonService->updateUserInfo();
                $this->cartCommonService->recalculatePriceCart();
                $this->smarty->assign('user_info', get_user_info());
                $ucdata = empty($GLOBALS['user']->ucdata) ? "" : $GLOBALS['user']->ucdata;
                $result['ucdata'] = $ucdata;
                $result['content'] = $this->smarty->fetch('library/member_info.lbi');
            } else {
                session()->increment('login_fail');
                if (session('login_fail') > 2) {
                    $this->smarty->assign('enabled_captcha', 1);
                    $result['html'] = $this->smarty->fetch('library/member_info.lbi');
                }
                $result['error'] = 1;
                $result['content'] = $GLOBALS['_LANG']['login_failure'];
            }
        }

        /* ------------------------------------------------------ */
        //-- 添加一个红包
        /* ------------------------------------------------------ */
        elseif ($act == 'act_add_bonus') {
            load_helper('transaction');

            $result = ['message' => '', 'result' => '', 'error' => 0];

            $bns = json_str_iconv(request()->input('bns', ''));
            $bns = dsc_decode($bns);

            $bouns_sn = intval($bns->bonus_sn);
            $password = compile_str($bns->password);
            $captcha_str = isset($bns->captcha) ? trim($bns->captcha) : '';

            if (gd_version() > 0) {
                $verify = app(CaptchaVerify::class);
                $captcha_code = $verify->check($captcha_str, 'bonus');

                /* 检查验证码 */
                if (!$captcha_code) {
                    $result['error'] = 3;
                    $result['message'] = $GLOBALS['_LANG']['invalid_captcha'];
                }
            }

            if ($result['error'] != 3) {
                if (empty($user_id)) {
                    $result['error'] = 2;
                    $result['message'] = $GLOBALS['_LANG']['not_login'];
                } else {
                    if (add_bonus($user_id, $bouns_sn, $password)) {
                        $result['message'] = $GLOBALS['_LANG']['add_bonus_sucess'];
                    } else {
                        $result['error'] = 1;
                        $result['message'] = $GLOBALS['_LANG']['add_bonus_false'];
                    }
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 添加一张储值卡
        /* ------------------------------------------------------ */
        elseif ($act == 'add_value_card') {
            load_helper('transaction');

            $result = ['message' => '', 'result' => '', 'error' => 0];

            $vc = json_str_iconv(request()->input('vc', ''));
            $vc = dsc_decode($vc);

            $value_card_sn = trim($vc->value_card_sn);
            $password = compile_str($vc->password);
            $captcha_str = isset($vc->captcha) ? trim($vc->captcha) : '';

            if (gd_version() > 0) {
                $verify = app(CaptchaVerify::class);
                $captcha_code = $verify->check($captcha_str, 'value_card');

                /* 检查验证码 */
                if (!$captcha_code) {
                    $result['error'] = 3;
                    $result['message'] = $GLOBALS['_LANG']['invalid_captcha'];
                }
            }

            if ($result['error'] != 3) {
                if (empty($user_id)) {
                    $result['error'] = 2;
                } else {
                    $result['error'] = 1;
                    switch (add_value_card($user_id, $value_card_sn, $password)) {
                        case 1:
                            $result['message'] = $GLOBALS['_LANG']['vc_use_expire'];
                            break;
                        case 2:
                            $result['message'] = $GLOBALS['_LANG']['vc_is_used'];
                            break;
                        case 3:
                            $result['message'] = $GLOBALS['_LANG']['vc_is_used_by_other'];
                            break;
                        case 4:
                            $result['message'] = $GLOBALS['_LANG']['vc_not_exist'];
                            break;
                        case 5:
                            $result['message'] = $GLOBALS['_LANG']['vc_limit_expire'];
                            break;
                        case 6:
                            $result['message'] = $GLOBALS['_LANG']['vc_use_invalid'];
                            break;
                        default:
                            $result['error'] = 0;
                            $result['message'] = $GLOBALS['_LANG']['add_value_card_sucess'];
                            break;
                    }
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 使用一张充值卡
        /* ------------------------------------------------------ */
        elseif ($act == 'use_pay_card') {
            load_helper('transaction');

            $result = ['message' => '', 'result' => '', 'error' => 0];

            $pc = json_str_iconv(request()->input('pc', ''));
            $pc = dsc_decode($pc);

            $pay_card_sn = trim($pc->pay_card_sn);
            $password = compile_str($pc->password);
            $vid = trim($pc->vid);
            $captcha_str = isset($pc->captcha) ? trim($pc->captcha) : '';
            if (gd_version() > 0) {
                $verify = app(CaptchaVerify::class);
                $captcha_code = $verify->check($captcha_str, 'pay_card');
                /* 检查验证码 */
                if (!$captcha_code) {
                    $result['error'] = 3;
                    $result['message'] = $GLOBALS['_LANG']['invalid_captcha'];
                }
            }

            if ($result['error'] != 3) {
                if (empty($user_id)) {
                    $result['error'] = 2;
                    $result['message'] = $GLOBALS['_LANG']['not_login'];
                } else {
                    if (use_pay_card($user_id, $vid, $pay_card_sn, $password)) {
                        $result['message'] = $GLOBALS['_LANG']['use_pay_card_sucess'];
                    } else {
                        $result['error'] = 1;
                        $result['message'] = $GLOBALS['_LANG']['pc_not_exist'];
                    }
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 异步延长收货时间
        /* ------------------------------------------------------ */
        elseif ($act == 'apply_delivery') {
            $result = ['err_msg' => '', 'result' => '', 'error' => 0];

            $order_id = intval(request()->input('order_id', 0));

            if ($order_id) {
                //判断开关config['open_order_delay']
                if (config('shop.open_order_delay') != 1) {
                    $result = ['err_msg' => lang('user.order_delayed_wrong'), 'result' => '', 'error' => 1];
                } else {
                    //判断订单状态
                    $where = [
                        'order_id' => $order_id,
                        'user_id' => session('user_id'),
                    ];
                    $order = $this->orderService->getOrderInfo($where);
                    if (($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED) && $order['shipping_status'] == SS_SHIPPED) { //发货状态
                        //判断时间config['order_delay_day']

                        $auto_delivery_time = config('shop.auto_delivery_time') ?? 0;
                        $auto_delivery_time = $auto_delivery_time > 0 && $auto_delivery_time > $order['auto_delivery_time'] ? $auto_delivery_time : $order['auto_delivery_time'];
                        $auto_delivery_time = $auto_delivery_time * 24 * 3600;

                        $delivery_time = $order['shipping_time'] + $auto_delivery_time;
                        $noTime = gmtime();
                        $order_delay_day = (config('shop.order_delay_day') > 0) ? intval(config('shop.order_delay_day')) : 3;//如无配置，最多可提前3天申请
                        $order_delay_num = (config('shop.order_delay_num') > 0) ? intval(config('shop.order_delay_num')) : 3;//如无配置，最多可申请3次
                        if (($noTime < $delivery_time && ($delivery_time - $noTime) / 86400 <= $order_delay_day)) {
                            //判断该单号申请次数,如无配置，最多可申请3次，config['order_delay_num'] review_status=1通过审核 2未通过
                            $apply_count = OrderDelayed::where('order_id', $order_id)->count();
                            if ($apply_count < $order_delay_num) {
                                $apply_data = [
                                    'order_id' => $order_id,
                                    'apply_time' => gmtime(),
                                    'apply_day' => 1
                                ];
                                // 判断是否有未审核的申请
                                $no_review = OrderDelayed::where('order_id', $order_id)->where('review_status', 0)->count();
                                if ($no_review > 0) {
                                    $result = ['err_msg' => $GLOBALS['_LANG']['order_delayed_repeat'], 'result' => '', 'error' => 3];
                                    return response()->json($result);
                                }

                                $delayed_id = OrderDelayed::insertGetId($apply_data);
                                if ($delayed_id) {
                                    $result = ['err_msg' => $GLOBALS['_LANG']['order_delayed_success'], 'result' => '', 'error' => 0];
                                }
                            } else {
                                $result = ['err_msg' => sprintf($GLOBALS['_LANG']['order_delayed_beyond'], $order_delay_num), 'result' => '', 'error' => 2];
                            }
                        } else {
                            $result = ['err_msg' => sprintf(lang('user.order_delay_day_desc'), $order_delay_num), 'result' => '', 'error' => 5];
                        }
                    } else {
                        $result = ['err_msg' => $GLOBALS['_LANG']['order_delayed_wrong'], 'result' => '', 'error' => 4];
                    }
                }
            } else {
                $result = ['err_msg' => $GLOBALS['_LANG']['order_delayed_wrong'], 'result' => '', 'error' => 1];
            }
        }

        /* ------------------------------------------------------ */
        //-- Ajax取消/关注
        /* ------------------------------------------------------ */
        elseif ($act == 'ajax_BatchCancelFollow') {
            $result = ['err_msg' => '', 'result' => '', 'error' => 0];

            $type = intval(request()->input('type', 0));

            $goods_id = trim(request()->input('goods_id', ''));
            $goods_id = $goods_id && !is_array($goods_id) ? explode(",", $goods_id) : $goods_id;

            if ($type == 0) {
                $is_attention = 1;
            } elseif ($type == 1) {
                $is_attention = 0;
            }

            if (!empty($goods_id)) {
                if ($type == 0 || $type == 1) {
                    CollectGoods::whereIn('goods_id', $goods_id)->update(['is_attention', $is_attention]);
                } elseif ($type == 2) {
                    CollectGoods::whereIn('goods_id', $goods_id)->delete();
                }
            }

            $result['goods_id'] = $goods_id;
        }

        /* ------------------------------------------------------ */
        //-- Ajax删除关注的品牌/关注
        /* ------------------------------------------------------ */
        elseif ($act == 'ajax_BrandBatchCancel') {
            $result = ['err_msg' => '', 'result' => '', 'error' => 0];
            $brands_rec_id = trim(request()->input('brands_rec_id', ''));
            $brands_rec_id = $brands_rec_id && !is_array($brands_rec_id) ? explode(",", $brands_rec_id) : $brands_rec_id;

            if (!empty($brands_rec_id)) {
                CollectBrand::whereIn('rec_id', $brands_rec_id)->delete();
            }

            $result['brands_rec_id'] = $brands_rec_id;
        }

        /* ------------------------------------------------------ */
        //-- 快递查询
        /* ------------------------------------------------------ */
        elseif ($act == 'query_express') {
            $express_no = request()->input('nu', '');
            $expressid = request()->input('com', '');
            $payload = request()->input('payload', []);
            $order_id = request()->input('order_id', 0);

            if (!empty($order_id)) {
                $payload['order_id'] = $order_id;
                $mobile = DB::table('order_info')->where('order_id', $payload['order_id'])->value('mobile');
                $payload['mobile'] = !empty($mobile) ? $mobile : '';
            }

            $shipping_info = $this->shippingProxy->getExpress($expressid, $express_no, $payload);

            if ($shipping_info['error'] > 0) {
                $data[0]['time'] = date('Y-m-d');
                $data[0]['context'] = $shipping_info['data'];
            } else {
                $data = $shipping_info['data'];
            }

            $express_info = '<table style="border:1px; solid #90BFFF; width:100%;border-collapse:collapse;border-spacing:0; float:left;">';
            foreach ($data['traces'] as $key => $val) {
                $express_info .= '<tr style="height:20px;">';
                $express_info .= "<td style='text-align:right;width:140px;'>$val[time]</td>";
                $express_info .= "<td>&nbsp;&nbsp;|&nbsp;&nbsp;</td>";
                $express_info .= "<td style='text-align:left;'>$val[context]</td>";
                $express_info .= '</tr>';
            }
            $express_info .= '</table>';

            exit($express_info);
        }

        /* ------------------------------------------------------ */
        //-- 再买一次
        /* ------------------------------------------------------ */
        elseif ($act == 'buyagain') {
            $result = ['err_msg' => '', 'result' => '', 'error' => 0];
            $order_id = intval(request()->input('order_id', 0));

            if ($order_id && session('user_id')) {
                $result = app(FlowMobileService::class)->BuyAgain(session('user_id'), $order_id, 'pc');
            }
        }
        /* ------------------------------------------------------ */
        //-- 上传支付凭证
        /* ------------------------------------------------------ */
        elseif ($act == 'upload_pay_document') {

            $result = ['err_msg' => '', 'result' => '', 'error' => 0];

            $order_id = intval(request()->input('order_id', 0));
            $user_id = intval(request()->input('user_id', 0));

            $order = DB::table('order_info')->where('order_id', $order_id)->select('user_id', 'order_id')->first();

            if (empty($order)) {
                $result['error'] = 1;
                $result['err_msg'] = trans('user.order_exist');
                return response()->json($result);
            }

            if ($order->user_id != $user_id) {
                $result['error'] = 1;
                $result['err_msg'] = trans('user.unauthorized_access');
                return response()->json($result);
            }

            // upload_pay_document 上传
            $upload_pay_document = request()->file('file');
            if ($upload_pay_document && $upload_pay_document->isValid()) {
                // 验证文件大小
                if ($upload_pay_document->getSize() > 2 * 1024 * 1024) {
                    $result['error'] = 1;
                    $result['err_msg'] = trans('file.file_size_limit');
                    return response()->json($result);
                }
                // 验证文件格式
                if (!in_array($upload_pay_document->getClientMimeType(), ['image/jpeg', 'image/png', 'application/pdf'])) {
                    $result['error'] = 1;
                    $result['err_msg'] = trans('file.not_file_type');
                    return response()->json($result);
                }
                $upload_res = File::upload('uploads/image', true);
                if ($upload_res['error'] > 0) {
                    $result['error'] = 1;
                    $result['err_msg'] = $upload_res['message'];
                    return response()->json($result);
                }
                $data['pay_document'] = 'uploads/image/' . $upload_res['file_name'];
            } else {
                $data['pay_document'] = $order->pay_document;;
            }

            if (!empty($data['pay_document'])) {
                // oss图片处理
                $file_arr = [
                    'pay_document' => $data['pay_document'],
                ];
                $file_arr = $this->dscRepository->transformOssFile($file_arr);
                $pay_document = $file_arr['pay_document'];

                $file_path = DB::table('order_info_bank_transfer')->where('order_id', $order_id)->value('pay_document');

                $where = [
                    'user_id' => $user_id,
                    'order_id' => $order_id
                ];
                DB::table('order_info_bank_transfer')->updateOrInsert($where, ['pay_document' => $pay_document]);

                if ($file_path) {
                    // 删除原图片
                    if ($pay_document && $file_path && $file_path != $pay_document) {
                        $file_path = (stripos($file_path, 'no_image') !== false || stripos($file_path, 'assets') !== false) ? '' : $file_path; // 不删除默认空图片
                        File::remove($file_path);
                    }
                }

                $result['error'] = 0;
                $result['err_msg'] = trans('common.upload_success');
                return response()->json($result);
            }

            $result['error'] = 1;
            return response()->json($result);
        }

        return response()->json($result);
    }

}
