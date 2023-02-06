<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Api\Transformers\GoodsTransformer;
use App\Models\GoodsAttr;
use App\Models\Region;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Comment\CommentService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsFittingService;
use App\Services\Goods\GoodsMobileService;
use App\Services\Goods\GoodsProdutsService;
use App\Services\Goods\GoodsService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Store\StoreService;
use App\Services\User\UserCommonService;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class GoodsController
 * @package App\Api\Controllers
 */
class GoodsController extends Controller
{
    protected $goodsMobileService;
    protected $goodsTransformer;
    protected $galleryService;
    protected $storeService;
    protected $userCommonService;
    protected $discountService;
    protected $dscRepository;
    protected $goodsAttrService;
    protected $goodsWarehouseService;
    protected $goodsFittingService;
    protected $goodsService;
    protected $commentService;

    public function __construct(
        GoodsMobileService $goodsMobileService,
        GoodsTransformer $goodsTransformer,
        StoreService $storeService,
        UserCommonService $userCommonService,
        DscRepository $dscRepository,
        GoodsAttrService $goodsAttrService,
        GoodsWarehouseService $goodsWarehouseService,
        GoodsFittingService $goodsFittingService,
        GoodsService $goodsService
    )
    {
        //加载外部类
        $files = [
            'clips',
            'order',
        ];
        load_helper($files);

        $this->goodsMobileService = $goodsMobileService;
        $this->goodsTransformer = $goodsTransformer;
        $this->storeService = $storeService;
        $this->userCommonService = $userCommonService;
        $this->dscRepository = $dscRepository;
        $this->goodsAttrService = $goodsAttrService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->goodsFittingService = $goodsFittingService;
        $this->goodsService = $goodsService;
    }

    /**
     * 商品详情跳转
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        return $this->show($request);
    }

    /**
     * 商品详情
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'goods_id' => 'required|integer'
        ]);

        $goods_id = (int)$request->input('goods_id', 0);

        // 获取推荐人id
        $parent_id = (int)$request->input('parent_id', 0);


        $arr = [
            'goods_id' => $goods_id,
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
            'is_delete' => 0
        ];
        $arr['uid'] = $this->uid;

        $data = $this->goodsMobileService->getGoodsInfo($arr);

        if (empty($data)) {
            return $this->responseNotFound();
        }

        /* 检测是否秒杀商品 */
        $sec_goods_id = $this->goodsMobileService->getIsSeckill($arr['goods_id']);
        $data['seckill_id'] = $sec_goods_id ? $sec_goods_id : 0;

        $data['store_count'] = 0;

        // 促销信息
        $data['goods_promotion'] = $this->goodsMobileService->getPromotionInfo($arr['uid'], $data['goods_id'], $data['user_id'], $data);

        // 计算最优价格
        $data['best_price'] = $this->goodsMobileService->getBestPrice($this->uid, $data);

        //是否是视频直播商品
        if (config('shop.wxapp_shop_status')) {
            $data['media_type'] = app(\App\Modules\WxMedia\Services\WxappMediaGoodsService::class)->get_goods_media_type($arr['goods_id']);
        } else {
            $data['media_type'] = 0;
        }

        // 会员等级
        $user_rank = $this->userCommonService->getUserRankByUid($this->uid);
        $data['rank_name'] = $user_rank['rank_name'];
        $data['rank_discount'] = $user_rank['discount'];

        if ($user_rank) {
            $user_rank['discount'] = $user_rank['discount'] / 100;
        } else {
            $user_rank['rank_id'] = 1;
            $user_rank['discount'] = 1;
        }

        // 组合套餐
        $group_count = get_group_goods_count($data['goods_id']);
        if ($group_count) {

            $fittings_list = $this->goodsFittingService->getGoodsFittings([$data['goods_id']], $arr['warehouse_id'], $arr['area_id'], 0, '', 0, [], $arr['uid'], $user_rank);

            if (!empty($fittings_list)) {
                // 节省金额最高 排序最前
                $fittings_list = get_array_sort($fittings_list, 'spare_price_ori', 'DESC');

                $fittings_list = array_values($fittings_list); // 重新对数组键排序 值不变

                $data['fittings'] = $fittings_list;
            }
        }

        //判断是否支持退货服务
        $is_return_service = 0;
        if (isset($data['goods_cause']) && $data['goods_cause']) {
            $goods_cause = explode(',', $data['goods_cause']);

            $fruit1 = [1, 2, 3]; //退货，换货，仅退款
            $intersection = array_intersect($fruit1, $goods_cause); //判断商品是否设置退货相关
            if (!empty($intersection)) {
                $is_return_service = 1;
            }
        }

        //判断是否设置包退服务  如果设置了退换货标识，没有设置包退服务  那么修正包退服务为已选择
        if ($is_return_service == 1 && isset($data['goods_extends']['is_return']) && !$data['goods_extends']['is_return']) {
            $data['goods_extend']['is_return'] = 1;
        }

        $sellerInfo = isset($data['get_seller_shop_info']) && $data['get_seller_shop_info'] ? $data['get_seller_shop_info'] : [];

        // 商家敏感数据脱敏
        if (isset($sellerInfo['mobile'])) {
            unset($sellerInfo['mobile']);
            unset($sellerInfo['seller_email']);
        }

        $data['basic_info'] = $sellerInfo;
        if ($sellerInfo) {
            $province = Region::where('region_id', $sellerInfo['province'])->value('region_name');
            $province = $province ? $province : '';

            $city = Region::where('region_id', $sellerInfo['city'])->value('region_name');
            $city = $city ? $city : '';

            $data['basic_info']['province_name'] = $province;
            $data['basic_info']['city_name'] = $city;
        }

        // 更新商品点击量
        $this->goodsMobileService->updateGoodsClick($arr['goods_id']);

        // 整站可评论 店铺可评论 则商品可评论
        $data['shop_can_comment'] = $sellerInfo && $sellerInfo['shop_can_comment'] == 1 && config('shop.shop_can_comment') == 1 ? 1 : 0;

        // 关联文章
        $data['goods_article_list'] = $this->goodsService->getLinkedArticles($data['goods_id']);

        // 小程序
        $data['live'] = [];
        if (file_exists(MOBILE_WXAPP)) {
            // 是否直播中
            $cache_id = 'live_list_goods_id';
            $result = cache($cache_id);
            if (!is_null($result)) {
                foreach ($result as $key => $val) {
                    if ($val['goods_id'] == $data['goods_id']) {
                        $data['live'] = [
                            'roomid' => $val['roomid'],
                            'goods_id' => $val['goods_id'],
                            'start_time' => $val['start_time'],
                            'end_time' => $val['end_time']
                        ];
                    }
                }
            }
        }

        /**
         * 商品分销
         */
        if (file_exists(MOBILE_DRP)) {
            // 是否有分销模块
            $data['is_drp'] = 1;
            $data['membership_card_id'] = $data['membership_card_id'] ?? 0;

            $user_id = $this->uid;
            if (isset($data['membership_card_id']) && $data['membership_card_id'] > 0) {
                // 是否已购买成为分销商商品 若已购买显示已下架
                $data['is_buy_drp'] = app(\App\Modules\Drp\Services\Distribute\DistributeService::class)->is_buy_goods($user_id, $goods_id);
                $data['is_on_sale'] = empty($data['is_buy_drp']) ? 1 : 0;
            }

            $drpService = app(\App\Modules\Drp\Services\Drp\DrpService::class);
            $drp_config = $drpService->drpConfig();
            // 是否显示分销，控制前端页面是否显示分销模块
            $data['is_show_drp'] = (int)$drp_config['isdrp']['value'] ?? 0;

            // 记录推荐人id
            if ($parent_id > 0) {
                $drp_affiliate = $drp_config['drp_affiliate_on']['value'] ?? 0;
                // 开启分销
                if ($drp_affiliate == 1) {
                    // 分销内购模式
                    $isdistribution = $drp_config['isdistribution']['value'] ?? 0;
                    if ($isdistribution == 2) {
                        /**
                         *  2. 自动模式
                         *  mode 1: 业绩归属 上级分销商 + 自己（条件：推荐自己微店内商品或自己推荐的链接）
                         *  mode 0：业绩归属 推荐人或上级分销商 + 自己（条件：推荐自己微店内商品或自己推荐的链接）
                         */
                        // 推荐自己微店内商品或自己推荐的链接
                        $is_drp_type = $drpService->isDrpTypeGoods($user_id, $goods_id, $data['cat_id']);

                        // 分销业绩归属模式
                        $drp_affiliate_mode = $drp_config['drp_affiliate_mode']['value'] ?? 0;
                        if ($drp_affiliate_mode == 1) {
                            if ($is_drp_type === true || $parent_id == $user_id) {
                                CommonRepository::setDrpShopAffiliate($user_id, $parent_id);
                            }
                        } else {
                            if ($parent_id > 0 || $is_drp_type === true || $parent_id == $user_id) {
                                CommonRepository::setDrpShopAffiliate($user_id, $parent_id);
                            }
                        }
                    } else {
                        CommonRepository::setDrpShopAffiliate($user_id, $parent_id);
                    }
                }

                //如有上级推荐人（分销商），且关系在有效期内，更新推荐时间 1.4.3
                $drpService->updateBindTime($user_id, $parent_id);
            }

            // 计算用户购买此商品的预计佣金（分享可赠）
            if ($data['is_distribution'] > 0 || $data['membership_card_id'] > 0) {
                $data['commission_money'] = app(\App\Modules\Drp\Services\Distribute\DistributeGoodsService::class)->calculate_goods_commossion($user_id, $data);

                $data['commission_money'] = empty($data['commission_money']) ? 0 : $this->dscRepository->getPriceFormat($data['commission_money']);
            }
        } else {
            $data['is_drp'] = 0;// 是否有分销模块
            $data['is_show_drp'] = 0;
        }

        // 评论接入到show
        $this->commentService = app(CommentService::class);
        $data['comment_title'] = $this->commentService->goodsCommentCount($goods_id);
        $data['comment_list'] = $this->commentService->GoodsComment(0, $goods_id);

        return $this->succeed($data);
    }

    /**
     * 获得促销商品
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function promoteGoods(Request $request)
    {
        $arr = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];
        $arr['uid'] = $this->uid;

        $data = $this->goodsMobileService->getPromoteGoods($arr);

        return $this->succeed($data);
    }

    /**
     * 获得推荐商品
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function recommendGoods(Request $request)
    {
        $arr = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];

        $arr['uid'] = $this->uid;

        $data = $this->goodsMobileService->getRecommendGoods($arr);

        return $this->succeed($data);
    }

    /**
     * 获得切换属性价格
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function AttrPrice(Request $request)
    {
        $this->validate($request, [
            'goods_id' => 'required|integer',
            'num' => 'required|integer',
            'store_id' => 'integer',
        ]);

        $goods_id = (int)$request->input('goods_id', 0);
        $num = (int)$request->input('num', 0);
        $store_id = (int)$request->input('store_id', 0);
        $attr_id = $request->input('attr_id', []);
        $attr_id = DscEncryptRepository::filterValInt($attr_id);

        $province_id = intval(request()->get('province_id', 0));
        $city_id = intval(request()->get('city_id', 0));
        $district_id = intval(request()->get('district_id', 0));
        $street = intval(request()->get('street', 0));

        $region = [1, $province_id, $city_id, $district_id, $street];

        $data = $this->goodsMobileService->goodsPropertiesPrice($this->uid, $goods_id, $attr_id, $num, $this->warehouse_id, $this->area_id, $this->area_city, $store_id, $region);

        return $this->succeed($data);
    }

    /**
     * 猜你喜欢
     *
     * @param Request $request
     * @param SessionRepository $sessionRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function GoodsGuess(Request $request, SessionRepository $sessionRepository)
    {
        $user_id = $this->uid;
        $warehouse_id = $this->warehouse_id;
        $area_id = $this->area_id;
        $area_city = $this->area_city;
        $session_id = $sessionRepository->realCartMacIp();
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 12);

        $data = $this->goodsMobileService->getUserOrderGoodsGuess($user_id, $session_id, $warehouse_id, $area_id, $area_city, $page, $size);

        return $this->succeed($data);
    }

    /**
     * 关联商品
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function LinkGoods(Request $request)
    {
        $goods_id = $request->input('goods_id', 0);
        $size = $request->input('size', 30);
        $user_id = $this->uid;
        $warehouse_id = $this->warehouse_id;
        $area_id = $this->area_id;
        $area_city = $this->area_city;

        $data = $this->goodsMobileService->getLinkGoods($goods_id, $user_id, $size, $warehouse_id, $area_id, $area_city);

        return $this->succeed($data);
    }

    /**
     * 生成商品分享海报 H5
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function shareposter(Request $request)
    {
        // 数据验证
        $this->validate($request, [
            'goods_id' => 'required|integer',
            'code_url' => 'required|string',
            'share_type' => 'filled|integer',
            'extension_code' => 'filled|string',
            'thumb' => 'filled|string',
            'title' => 'filled|string',
            'price' => 'filled|string',
        ]);

        $goods_id = (int)$request->input('goods_id', 0);
        $share_type = (int)$request->input('share_type', 0); // 分享类型: 0 分享,1 分销
        // 活动商品标识：默认为空 普通商品, team拼团, bargain砍价, exchange积分商城, seckill秒杀, group_buy团购, presale预售, auction拍卖,crowdfunding微筹
        $extension_code = e($request->input('extension_code', ''));
        $code_url = e($request->input('code_url', '')); // 二维码链接
        $thumb = e($request->input('thumb', '')); // 缩略图
        $title = e($request->input('title', '')); // 标题
        $price = e($request->input('price', '')); // 价格

        //获取会员id
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $url = $this->goodsMobileService->createSharePoster($user_id, $goods_id, $extension_code, $code_url, $thumb, $title, $price, $share_type);

        return $this->succeed($url);
    }

    /**
     * 组合套餐 配件
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Fittings(Request $request)
    {
        $result = [
            'error' => 0,
            'goods' => '',       // 商品信息
            'fittings' => '',    // 配件
            'comboTab' => '',    // 配件类型
        ];
        // 数据验证
        $this->validate($request, [
            'goods_id' => 'required|integer'
        ]);

        $goods_id = (int)$request->input('goods_id', 0);

        // 清空配件购物车
        $this->goodsMobileService->clearCartCombo($this->uid, $goods_id);

        // 主商品信息
        $arr = [
            'goods_id' => $goods_id,
            'warehouse_id' => (int)$this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];
        $arr['uid'] = $this->uid;

        $goods = $this->goodsMobileService->getGoodsInfo($arr);

        if (empty($goods)) {
            return $this->responseNotFound();
        } else {
            $result['goods'] = $goods; // 商品信息

            // 组合套餐
            $group_count = get_group_goods_count($goods_id);
            if ($group_count) {
                // 配件类型
                $comboTabIndex = $this->goodsFittingService->getCfgGroupGoods();
                $comboTab = [];
                foreach ($comboTabIndex as $key => $row) {
                    $val = $key - 1;
                    $comboTab[$val]['group_id'] = $key;
                    $comboTab[$val]['text'] = $row;
                }

                $result['comboTab'] = $comboTab;
                // 会员等级
                $user_rank = $this->userCommonService->getUserRankByUid($arr['uid']);
                if ($user_rank) {
                    $user_rank['discount'] = $user_rank['discount'] / 100;
                } else {
                    $user_rank['rank_id'] = 1;
                    $user_rank['discount'] = 1;
                }

                $fittings_list = $this->goodsFittingService->getGoodsFittings([$goods_id], $arr['warehouse_id'], $arr['area_id'], 0, '', 0, [], $arr['uid'], $user_rank);

                // 节省金额最高 排序最前
                $fittings_list = get_array_sort($fittings_list, 'spare_price_ori', 'DESC');

                $fittings_list = array_values($fittings_list); // 重新对数组键排序 值不变

                foreach ($fittings_list as $ke => $val) {
                    /*获取商品属性*/
                    $row = [];
                    $row['attr'] = $this->goodsAttrService->goodsAttr($val['goods_id']);

                    if ($row['attr']) {
                        $row['attr_name'] = '';
                        $goods_attr_id = [];
                        foreach ($row['attr'] as $k => $v) {
                            $select_key = 0;
                            foreach ($v['attr_key'] as $key => $val) {
                                if ($val['attr_checked'] == 1) {
                                    $select_key = $key;
                                    break;
                                }
                            }

                            //默认选择第一个属性为checked
                            if ($select_key == 0) {
                                $row['attr'][$k]['attr_key'][0]['attr_checked'] = 1;
                            }

                            $goods_attr_id[] = $v['attr_key'][$select_key]['goods_attr_id'];

                            if ($row['attr_name']) {
                                $row['attr_name'] = $row['attr_name'] . '' . $v['attr_key'][$select_key]['attr_value'];
                            } else {
                                $row['attr_name'] = $v['attr_key'][$select_key]['attr_value'];
                            }
                        }

                        $fittings_list[$ke]['attr'] = $row['attr'];

                        $attr_id = BaseRepository::getKeyPluck($row['attr'], 'attr_id');
                        $fittings_list[$ke]['attr_id'] = BaseRepository::getImplode($attr_id);
                        $fittings_list[$ke]['attr_name'] = $row['attr_name'];
                        $fittings_list[$ke]['goods_attr_id'] = BaseRepository::getImplode($goods_attr_id);
                    }
                    unset($fittings_list[$ke]['properties']);
                }
                $result['fittings'] = $fittings_list;
            }
        }

        return $this->succeed($result);
    }

    /**
     * 组合套餐 配件[属性选择]
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function FittingPrice(Request $request)
    {
        $goods_id = (int)$request->input('goods_id', 0); // 商品id

        $type = (int)$request->input('type', 0);    // type 1 主件 0 配件
        $load_type = $request->input('onload', ''); // 首次加载
        $group = $request->input('group', '');

        $uid = $this->uid;

        $attr = trim($group['attr']);
        $attr_id = !empty($attr) ? explode(',', $attr) : [];

        if (empty($attr_id)) {
            return $this->succeed([]);
        }

        $number = (int)$group['number'];
        $group_name = trim($group['group_name']);

        $group_id = trim($group['group_id']);
        $group_id = addslashes_deep($group_id);

        $parent_id = (int)$group['fittings_goods']; // 主商品id

        if (empty($goods_id)) {
            /* 重新获取商品ID */
            $goodsAttr = GoodsAttr::where('goods_attr_id', $attr_id[0]);
            $goods_id = $goodsAttr->value('goods_id');
        }

        // 主商品信息
        $arr = [
            'goods_id' => $goods_id,
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];
        $arr['uid'] = $uid;

        $goods = $this->goodsMobileService->getGoodsInfo($arr);

        if (empty($goods)) {
            return $this->responseNotFound();
        } else {
            if ($number == 0) {
                $res['number'] = $number = 1;
            } else {
                $res['number'] = $number;
            }
            // 库存
            $attr_number = $this->goodsWarehouseService->goodsAttrNumber($goods_id, $goods['model_attr'], $attr_id, $this->warehouse_id, $this->area_id, $this->area_city);
            $res['attr_number'] = $attr_number;

            // 限制用户购买的数量
            $res['limit_number'] = $attr_number < $number ? ($attr_number ? $attr_number : 1) : $number;

            $res['shop_price'] = StrRepository::priceFormat($goods['shop_price']);
            $res['market_price'] = $goods['market_price'];
            // 属性价格
            $res['spec_price'] = app(GoodsProdutsService::class)->goodsPropertyPrice($goods_id, $attr_id, $this->warehouse_id, $this->area_id, $this->area_city);
            $res['spec_price_formated'] = $this->dscRepository->getPriceFormat($res['spec_price'], true);
            // 最终价格
            $res['result'] = $this->goodsMobileService->getFinalPrice($uid, $goods_id, $number, true, $attr_id, $this->warehouse_id, $this->area_id, $this->area_city);
            $res['goods_price_formated'] = $this->dscRepository->getPriceFormat($res['result'], true);
        }

        // 会员等级
        $user_rank = $this->userCommonService->getUserRankByUid($arr['uid']);
        if ($user_rank) {
            $user_rank['discount'] = $user_rank['discount'] / 100;
        } else {
            $user_rank['rank_id'] = 1;
            $user_rank['discount'] = 1;
        }
        // 组合套餐 返回区间价格
        // 1 首次加载
        if ($type == 1 && $load_type == 'onload') {
            $group_count = get_group_goods_count($goods_id);
            if ($group_count > 0) {
                $fittings_list = $this->goodsFittingService->getGoodsFittings([$goods_id], $arr['warehouse_id'], $arr['area_id'], 0, '', 0, [], $arr['uid'], $user_rank);
                if ($fittings_list) {
                    $fittings_attr = $attr_id;
                    $goods_fittings = $this->goodsFittingService->getGoodsFittingsInfo($goods_id, $this->warehouse_id, $this->area_id, $this->area_city, '', 1, '', $fittings_attr, $arr['uid'], $user_rank);

                    if (is_array($fittings_list)) {
                        foreach ($fittings_list as $vo) {
                            $fittings_index[$vo['group_id']] = $vo['group_id'];//关联数组
                        }
                    }
                    ksort($fittings_index);//重新排序

                    $merge_fittings = $this->goodsFittingService->getMergeFittingsArray($fittings_index, $fittings_list); //配件商品重新分组

                    $fitts = $this->goodsFittingService->getFittingsArrayList($merge_fittings, $goods_fittings);
                    for ($i = 0; $i < count($fitts); $i++) {
                        $fittings_interval = $fitts[$i]['fittings_interval'];

                        $res['fittings_interval'][$i]['fittings_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['fittings_min']) . "-" . number_format($fittings_interval['fittings_max'], 2, '.', '');
                        $res['fittings_interval'][$i]['market_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['market_min']) . "-" . number_format($fittings_interval['market_max'], 2, '.', '');

                        if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
                            $res['fittings_interval'][$i]['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_minPrice']);
                        } else {
                            $res['fittings_interval'][$i]['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_minPrice']) . "-" . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
                        }

                        $res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
                    }
                }
            }
        } else {
            // 切换属性
            $combo_goods = $this->goodsFittingService->getCartComboGoodsList($goods_id, $parent_id, $group_id, $uid);

            if ($combo_goods['combo_number'] > 0) {
                $goods_info = $this->goodsFittingService->getGoodsFittingsInfo($parent_id, $this->warehouse_id, $this->area_id, $this->area_city, $group_id, 0, '', [], $uid, $user_rank);
                $fittings = $this->goodsFittingService->getGoodsFittings([$parent_id], $this->warehouse_id, $this->area_id, $this->area_city, $group_id, 1, [], $uid, $user_rank);
            } else {
                $goods_info = $this->goodsFittingService->getGoodsFittingsInfo($parent_id, $this->warehouse_id, $this->area_id, $this->area_city, '', 1, '', [], $uid, $user_rank);
                $fittings = $this->goodsFittingService->getGoodsFittings([$parent_id], $this->warehouse_id, $this->area_id, $this->area_city, '', 1, [], $uid, $user_rank);
            }

            $fittings = array_merge($goods_info, $fittings);
            $fittings = array_values($fittings);

            $fittings_interval = $this->goodsFittingService->getChooseGoodsComboCart($fittings);
            if ($combo_goods['combo_number'] > 0) {
                // 配件商品没有属性时
                $res['fittings_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['all_price_ori']);
                $res['market_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['all_market_price']);
                $res['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_price_amount']);
            } else {
                $res['fittings_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['fittings_min']) . "-" . number_format($fittings_interval['fittings_max'], 2, '.', '');
                $res['market_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['market_min']) . "-" . number_format($fittings_interval['market_max'], 2, '.', '');

                if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
                    $res['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_minPrice']);
                } else {
                    $res['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_minPrice']) . "-" . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
                }
            }
        }

        $goodsGroup = explode('_', $group_id);
        $res['groupId'] = $goodsGroup[2];

        if ($attr_id) {
            // 属性图片
            $attr_img = $this->goodsMobileService->getAttrImgFlie($goods_id, $attr_id);
            if (!empty($attr_img['attr_img_flie'])) {
                $res['attr_img'] = $this->dscRepository->getImagePath($attr_img['attr_img_flie']);
            }

            $res['attr_name'] = $this->goodsMobileService->getAttrName($goods_id, $attr_id);
        }

        $res['goods_id'] = $goods_id;
        $res['parent_id'] = $parent_id;
        $res['group_name'] = $group_name;
        $res['load_type'] = $load_type;
        $res['region_id'] = $this->warehouse_id;
        $res['area_id'] = $this->area_id;
        $res['area_city'] = $this->area_city;

        // 点击切换属性 保存到 临时表
        if (($type == 1 && $load_type != 1) || $type == 0) {
            $prod_attr = [];
            if (!empty($attr_id)) {
                $prod_attr = $attr_id;
            }


            if (is_spec($prod_attr) && !empty($attr_id)) {
                $product_info = $this->goodsAttrService->getProductsInfo($goods_id, $prod_attr, $this->warehouse_id, $this->area_id, $this->area_city);
            }

            $goods_attr = get_goods_attr_info_new($attr_id, 'pice');

            // 主件商品
            if ($type == 1) {
                $goods_price = $res['result'];
            } else {
                // 配件商品价格
                $goods_price = $this->goodsMobileService->groupGoodsInfo($goods_id, $parent_id);
                if (config('shop.add_shop_price') == 1) {
                    $goods_price = $goods_price + $res['spec_price'];
                }
            }

            // 更新信息
            $cart_combo_data = array(
                'goods_attr_id' => implode(',', $attr_id),
                'product_id' => $product_info['product_id'] ?? 0,
                'goods_attr' => addslashes($goods_attr),
                'goods_price' => $goods_price
            );

            $this->goodsMobileService->updateCartCombo($uid, $group_id, $goods_id, $cart_combo_data);
        }

        return $this->succeed($res);
    }

    /**
     * 商品视频列表
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function goodsVideo(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'page' => 'required|integer',
            'size' => 'required|integer',
        ]);

        $page = (int)$request->input('page');
        $size = (int)$request->input('size');

        $uid = $this->uid;
        $where = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];

        $goods_video_list = $this->goodsMobileService->getVideoList($size, $page, $uid, $where);

        return $this->succeed($goods_video_list);
    }

    /**
     * 商品视频详情
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function videoinfo(Request $request)
    {
        $goods_id = (int)$request->input('goods_id', 0);
        $uid = $this->uid;
        $where = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
        ];

        $video_info = $this->goodsMobileService->getVideoInfo($goods_id, $uid, $where);

        return $this->succeed($video_info);
    }

    /**
     * 更新商品视频点击量
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function videolooknum(Request $request)
    {
        $goods_id = (int)$request->input('goods_id', 0);

        $result = $this->goodsMobileService->getVideoLookNum($goods_id);

        return $this->succeed($result);
    }

    /**
     * 商品列表
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function typeList(Request $request)
    {
        $filter['user_id'] = $this->uid;
        $filter['page'] = $request->input('page', 1);
        $filter['size'] = $request->input('size', 10);
        $filter['type'] = $request->input('type', '');
        $filter['warehouse_id'] = $this->warehouse_id;
        $filter['area_id'] = $this->area_id;
        $filter['area_city'] = $this->area_city;

        $result = $this->goodsMobileService->getGoodsTypeList($filter);

        return $this->succeed($result);
    }
}
