<?php

namespace App\Services\User;

use App\Exceptions\HttpException;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\OrderReturnExtend;
use App\Models\ReturnAction;
use App\Models\ReturnCause;
use App\Models\ReturnGoods;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderReturnRepository;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsMobileService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Merchant\ShopAddressService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderRefoundService;
use App\Services\Order\OrderStatusService;
use App\Services\Package\PackageGoodsService;
use App\Services\Shipping\ShippingService;
use Illuminate\Support\Facades\DB;

/**
 * 退换货
 * Class RefoundService
 * @package App\Services\User
 */
class RefoundService
{
    protected $goodsService;
    protected $commonRepository;
    protected $jigonManageService;
    protected $dscRepository;
    protected $merchantCommonService;
    protected $goodsCommonService;
    protected $orderRefoundService;
    protected $orderCommonService;
    protected $packageGoodsService;
    protected $shopAddressService;
    protected $goodsAttrService;

    public function __construct(
        GoodsMobileService $goodsService,
        CommonRepository $commonRepository,
        JigonManageService $jigonManageService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        GoodsCommonService $goodsCommonService,
        OrderRefoundService $orderRefoundService,
        OrderCommonService $orderCommonService,
        PackageGoodsService $packageGoodsService,
        ShopAddressService $shopAddressService,
        GoodsAttrService $goodsAttrService
    )
    {
        //加载外部类
        $files = [
            'clips',
            'common',
            'time',
            'main',
            'order',
            'function',
            'base',
            'goods',
            'ecmoban'
        ];
        load_helper($files);

        $this->goodsService = $goodsService;
        $this->commonRepository = $commonRepository;
        $this->jigonManageService = $jigonManageService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsCommonService = $goodsCommonService;
        $this->orderRefoundService = $orderRefoundService;
        $this->orderCommonService = $orderCommonService;
        $this->packageGoodsService = $packageGoodsService;
        $this->shopAddressService = $shopAddressService;
        $this->goodsAttrService = $goodsAttrService;
    }

    /**
     * 退换货商品列表
     * @param int $order_id
     * @param int $user_id
     * @return array
     * @throws HttpException
     */
    public function getGoodsOrder($order_id = 0, $user_id = 0)
    {
        /* 订单信息 */
        $order = order_info($order_id);

        if (empty($order)) {
            throw new HttpException(trans('user.order_exist'), 1);
        }

        if ($user_id > 0 && $order['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        /* 订单商品 */
        $goods_list = order_goods($order_id);

        if ($goods_list) {
            $rec_id_drp = 0;
            if (file_exists(MOBILE_DRP)) {
                if (isset($order['user_id']) && !empty($order['user_id'])) {
                    $rec_id_drp = $this->getOrderGoodsDrp($order['user_id']);
                }
            }

            $orderGoodsAttrIdList = BaseRepository::getKeyPluck($goods_list, 'goods_attr_id');
            $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
            $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

            $productsGoodsAttrList = [];
            if ($orderGoodsAttrIdList) {
                $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            $order['all_refound'] = 0;
            foreach ($goods_list as $key => $value) {
                $buy_drp_show = 0;
                if (isset($value['extension_code']) && $value['extension_code'] == 'package_buy') {
                    $is_package_buy = 1;
                } else {
                    $is_package_buy = 0;
                }
                if ($is_package_buy == 0) {
                    if (isset($value['rec_id']) && !empty($rec_id_drp) && in_array($value['rec_id'], $rec_id_drp)) {
                        $buy_drp_show = 1;
                    }
                    $order['goods'][$key]['goods_name'] = $value['goods_name'] ?? '';
                    $order['goods'][$key]['goods_id'] = $value['goods_id'] ?? 0;
                    $order['goods'][$key]['goods_thumb'] = $this->dscRepository->getImagePath($value['get_goods']['goods_thumb'] ?? '');
                    $order['goods'][$key]['goods_cause'] = $this->goodsCommonService->getGoodsCause($value['get_goods']['goods_cause'] ?? '', $order, $value, $buy_drp_show);
                    $order['goods'][$key]['goods_cause_formated'] = lang('user.no_support_return');
                    if (!empty($order['goods'][$key]['goods_cause'])) {
                        $order['goods'][$key]['goods_cause_formated'] = lang('user.only_support') . '：' . implode('、', ArrRepository::pluck($order['goods'][$key]['goods_cause'], 'lang'));
                    }

                    $goods_attr_id = $value['goods_attr_id'] ?? '';
                    $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                    $order['goods'][$key]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, order['goods'][$key]['goods_thumb']);

                    $price[] = $value['subtotal'];
                    $order['goods'][$key]['market_price'] = $this->dscRepository->getPriceFormat($value['market_price'], false);
                    $order['goods'][$key]['goods_number'] = $value['goods_number'];
                    $order['goods'][$key]['goods_price'] = $this->dscRepository->getPriceFormat($value['goods_price'], false);
                    $order['goods'][$key]['subtotal'] = $this->dscRepository->getPriceFormat($value['subtotal'], false);
                    $order['goods'][$key]['is_refound'] = get_is_refound($value['rec_id']);   //判断是否退换货过
                    $order['goods'][$key]['goods_attr'] = str_replace(' ', '&nbsp;&nbsp;&nbsp;&nbsp;', $value['goods_attr']);
                    $order['goods'][$key]['rec_id'] = $value['rec_id'];

                    $order['goods'][$key]['extension_code'] = $value['extension_code'];

                    if ($value['is_gift'] > 0) {
                        $order['all_refound'] = 1;
                    }
                }
            }
        }

        return $order;
    }


    /**
     * 退换货列表
     *
     * @param int $user_id
     * @param int $order_id
     * @param int $start
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function getRefoundList($user_id = 0, $order_id = 0, $start = 1, $size = 10)
    {
        //判断是否支持激活
        $activation_number_type = (int)config('shop.activation_number_type', 0);
        $activation_number_type = $activation_number_type > 0 ? $activation_number_type : 2;

        $res = OrderReturn::where('user_id', $user_id);

        if ($order_id > 0) {
            $res = $res->where('order_id', $order_id);
        }

        // 检测商品是否存在
        $res = $res->whereHasIn('getGoods', function ($query) {
            $query->where('goods_id', '>', 0);
        });

        $res = $res->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb', 'goods_name');
            },
            'getOrderGoods' => function ($query) {
                $query->select('rec_id', 'extension_code');
            },
            'getReturnGoods' => function ($query) {
                $query->select('ret_id', 'return_number');
            }
        ]);

        $start = ($start - 1) * $size;
        if ($start > 0) {
            $res = $res->skip($start);
        }
        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = $res->orderBy('ret_id', 'desc')->get();

        $res = $res ? $res->toArray() : [];

        $goods_list = [];

        if ($res) {
            foreach ($res as $row) {
                $row['goods_name'] = $row['get_goods']['goods_name'];
                $row['goods_id'] = $row['get_goods']['goods_id'];
                $row['goods_thumb'] = $this->dscRepository->getImagePath($row['get_goods']['goods_thumb']);
                $row['apply_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['apply_time']);
                $row['should_return'] = $this->dscRepository->getPriceFormat($row['should_return']);

                // 是否可取消申请
                $row['refound_cancel'] = OrderStatusService::refound_cancel($row);

                $row['order_status'] = '';
                if ($row['return_status'] == 0 && $row['refound_status'] == 0) {
                    //  提交退换货后的状态 由用户寄回
                    $row['order_status'] .= "<span>" . trans('user.user_return') . "</span>";
                } elseif ($row['return_status'] == 1) {
                    //退换商品收到
                    $row['order_status'] .= "<span>" . trans('user.get_goods') . "</span>";
                } elseif ($row['return_status'] == 2) {
                    //换货商品寄出 （分单）
                    $row['order_status'] .= "<span>" . trans('user.send_alone') . "</span>";
                } elseif ($row['return_status'] == 3) {
                    //换货商品寄出
                    $row['order_status'] .= "<span>" . trans('user.send') . "</span>";
                } elseif ($row['return_status'] == 4) {
                    //完成
                    $row['order_status'] .= "<span>" . trans('user.complete') . "</span>";
                } elseif ($row['return_status'] == 6) {
                    //被拒
                    $row['order_status'] .= "<span>" . trans('user.rf.' . $row['return_status']) . "</span>";
                } else {
                    //其他
                }

                // 0 维修- 1 退货（款）-2 换货 3 仅退款状态
                if ($row['return_type'] == 0) {
                    if ($row['return_status'] == 4) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_MAINTENANCE);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOMAINTENANCE);
                    }
                } elseif ($row['return_type'] == 1) {
                    if ($row['refound_status'] == 1) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_REFOUND);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOREFOUND);
                    }
                } elseif ($row['return_type'] == 2) {
                    if ($row['return_status'] == 4) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_EXCHANGE);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOEXCHANGE);
                    }
                } elseif ($row['return_type'] == 3) {
                    if ($row['refound_status'] == 1) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_REFOUND);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOREFOUND);
                    }
                }

                if ($row['return_status'] == 6) {
                    $row['reimburse_status'] = trans('user.rf.' . $row['return_status']);
                }

                $row['activation_type'] = 0;
                //判断是否支持激活
                if ($row['return_status'] == 6) {
                    if ($row['activation_number'] < $activation_number_type) {
                        $row['activation_type'] = 1;
                    }
                    $row['agree_apply'] = -1; // 可激活时 不显示待同意 状态
                }

                if (isset($row['get_goods']['extension_code']) && $row['get_goods']['extension_code'] == 'package_buy') {
                    $is_package_buy = 1;
                } else {
                    $is_package_buy = 0;
                }

                if ($is_package_buy == 0) {
                    $goods_list[] = $row;
                }
            }
        }

        return $goods_list;
    }


    /**
     * 退换货申请
     *
     * @param int $user_id
     * @param int $order_id
     * @param array $rec_ids
     * @return array
     * @throws \Exception
     */
    public function applyReturn($user_id = 0, $order_id = 0, $rec_ids = [])
    {
        if (empty($user_id) || empty($order_id)) {
            return [];
        }

        if (empty($rec_ids)) {
            return ['error' => 1, 'msg' => trans('user.please_select_goods')];
        }

        /* 根据订单id或订单号查询订单信息 */
        $order = order_info($order_id);

        if (empty($order)) {
            return [];
        }

        if ($order['user_id'] != $user_id) {
            return ['error' => 1, 'msg' => trans('user.unauthorized_access')];
        }

        // 检查订单商品是否包含赠品  有赠品仅支持整单退
        $gift_count = OrderGoods::where('order_id', $order_id)->where('is_gift', '>', 0)->count();
        if ($gift_count > 0) {
            $select_count = count($rec_ids);
            $goods_count = OrderGoods::where('order_id', $order_id)->count();
            if ($select_count < $goods_count) {
                return ['error' => 1, 'msg' => trans('user.only_can_return_all')];
            }
        }

        $info['order'] = $order;

        /* 退货权限：订单状态 已发货、未退货 */
        $info['return_allowable'] = 0;
        if ($order['order_status'] != OS_RETURNED && $order['shipping_status'] > SS_UNSHIPPED) {
            $info['return_allowable'] = 1;
        }

        $info['consignee'] = [
            'country' => $order['country'],
            'province' => $order['province'],
            'city' => $order['city'],
            'district' => $order['district'],
            'address' => $order['address'],
            'mobile' => $order['mobile'],
            'consignee' => $order['consignee'],
            'user_id' => $order['user_id'],
            'region' => $order['region']
        ];

        // 退换货原因
        $info['parent_cause'] = OrderReturnRepository::getReturnCause(0, 1);

        //第一个购买成为分销商订单产品ID
        if (file_exists(MOBILE_DRP)) {
            $rec_id_drp = $this->getOrderGoodsDrp($user_id);
        } else {
            $rec_id_drp = [];
        }

        // 初始化值
        $buy_drp_show = 0;
        $info['return_goods_num'] = 0; // 可退换货数量
        $info['img_list'] = []; // 退换货商品图片列表

        //支持多商品申请退换货
        $cause_arr = [];

        $error = [];
        foreach ($rec_ids as $k => $rec_id) {
            // 判断退换货是否已申请
            $is_refound = get_is_refound($rec_id);

            /* 订单商品 */
            $goods_info = $this->rec_goods($rec_id);
            if ($is_refound == 1) {
                return ['error' => 1, 'msg' => $goods_info['goods_name'] . ' ' . str_replace(['[', ']'], '', trans('user.Have_applied'))];
            }

            if (is_null($goods_info['goods_cause']) || $goods_info['goods_cause'] == '') {
                $error[] = $rec_id;
            }

            if (empty($cause_arr)) {
                $cause_arr = explode(",", $goods_info['goods_cause']);
            }
            $cause_arr_next = explode(",", $goods_info['goods_cause']);
            if ($cause_arr) {
                $cause_arr = array_intersect($cause_arr, $cause_arr_next); //比较数组返回交集
            }

            $info['goods_list'][] = $goods_info;

            // 可退换货数量
            $info['return_goods_num'] += $goods_info['goods_number'];

            if (!empty($rec_id_drp) && in_array($rec_id, $rec_id_drp)) {
                $buy_drp_show = 1;
            }

            //图片列表
            $where = [
                'user_id' => $user_id,
                'rec_id' => $rec_id
            ];
            $img_list = $this->orderRefoundService->getReturnImagesList($where);
            if (!empty($img_list)) {
                array_push($info['img_list'], $img_list);
            }
        }

        if (!empty($error)) {
            return ['error' => 1, 'msg' => trans('user.nonsupport_return_goods')];
        }

        // 退换货服务类型
        $cause_str = implode(",", $cause_arr);
        $goods_cause = $this->goodsCommonService->getGoodsCause($cause_str, $order, [], $buy_drp_show);
        if (empty($goods_cause)) {
            return ['error' => 1, 'msg' => trans('user.no_support_return')];
        }

        $info['goods_cause'] = empty($goods_cause) ? [] : collect($goods_cause)->values()->all();

        // 是否显示 退换货商品可退数量修改框，若是批量且数量不相同 不显示
        $info['show_return_number'] = !empty($rec_ids) && count($rec_ids) > 1 ? 0 : 1;
        // 退换货最多可上传图片数量
        $info['return_pictures'] = config('shop.return_pictures', 10);

        return $info;
    }

    /**
     * 获取订单里某个商品
     *
     * @param int $rec_id
     * @return mixed
     * @throws \Exception
     */
    protected function rec_goods($rec_id = 0)
    {
        $res = OrderGoods::where('rec_id', $rec_id);
        $res = BaseRepository::getToArrayFirst($res);

        if (empty($res)) {
            return [];
        }

        $productsGoodsAttrList = [];
        if ($res['goods_attr_id']) {
            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($res['goods_attr_id'], ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        $subtotal = $res['goods_price'] * $res['goods_number'];

        if ($res['extension_code'] == 'package_buy') {
            $res['package_goods_list'] = $this->packageGoodsService->getPackageGoods($res['goods_id']);
        }

        $res['market_price_formated'] = $this->dscRepository->getPriceFormat($res['market_price']);
        $res['goods_price_formated'] = $this->dscRepository->getPriceFormat($res['goods_price']);
        $res['shop_price_formated'] = $this->dscRepository->getPriceFormat($res['goods_price']);
        $res['subtotal'] = $this->dscRepository->getPriceFormat($subtotal);
        $res['attr_name'] = $res['goods_attr'] ?? '';

        $res['formated_goods_coupons'] = $this->dscRepository->getPriceFormat($res['goods_coupons']);
        $res['formated_goods_bonus'] = $this->dscRepository->getPriceFormat($res['goods_bonus']);
        $res['formated_goods_favourable'] = $this->dscRepository->getPriceFormat($res['goods_favourable']);
        $res['formated_value_card_discount'] = $this->dscRepository->getPriceFormat($res['value_card_discount']);
        $res['formated_should_return'] = $this->dscRepository->getPriceFormat($subtotal - $res['goods_coupons'] - $res['goods_bonus'] - $res['goods_favourable'] - $res['value_card_discount']);

        $goods = Goods::where('goods_id', $res['goods_id'])->select('goods_cause', 'user_id', 'goods_img', 'goods_thumb');
        $goods = BaseRepository::getToArrayFirst($goods);

        $res['goods_cause'] = $goods['goods_cause'] ?? '';

        /* 修正商品图片 */
        $res['goods_img'] = $this->dscRepository->getImagePath($goods['goods_img']);
        $res['goods_thumb'] = $this->dscRepository->getImagePath($goods['goods_thumb']);

        $goods_attr_id = $res['goods_attr_id'];
        $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
        $res['goods_img'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $res['goods_img']);

        $merchant = MerchantDataHandleService::getMerchantInfoDataList([$goods['user_id']]);

        // 店铺名称
        $res['shop_name'] = $merchant[$goods['user_id']]['shop_name'] ?? '';

        $basic_info = SellerShopinfo::where('ru_id', $goods['user_id']);
        $basic_info = BaseRepository::getToArrayFirst($basic_info);

        $chat = $this->dscRepository->chatQq($basic_info);
        $res['kf_type'] = $chat['kf_type'];
        $res['kf_qq'] = $chat['kf_qq'];
        $res['kf_ww'] = $chat['kf_ww'];

        return $res;
    }

    /**
     * 获取退换货详情
     *
     * @param int $user_id
     * @param int $ret_id
     * @return array|mixed
     * @throws \Exception
     */
    public function returnDetail($user_id = 0, $ret_id = 0)
    {
        $order = $this->return_order_info($ret_id, '', 0, $user_id);

        if (empty($order)) {
            return [];
        }

        /* 对发货号处理 */
        if (!empty($order['out_invoice_no'])) {
            // 商家寄出地址
            if ($order['out_shipping_name'] == '999') {
                // 其他快递
                $order['out_invoice_no_btn'] = "https://m.kuaidi100.com/result.jsp?nu=" . $order['out_invoice_no'];
            } else {
                $shipping_code = Shipping::where(['shipping_id' => $order['out_shipping_name']])->value('shipping_code');

                $shipping = CommonRepository::shippingInstance($shipping_code);
                if (!is_null($shipping)) {
                    $code_name = $shipping->get_code_name();
                    $order['out_invoice_no_btn'] = route('tracker.query', [
                        'company' => $order['out_shipp_shipping'],
                        'type' => $code_name,
                        'postid' => $order['out_invoice_no']
                    ]);
                }
            }
        }
        if (!empty($order['back_invoice_no'])) {
            // 用户寄出地址
            if ($order['back_shipping_name'] == '999') {
                // 其他快递
                $order['back_invoice_no_btn'] = "https://m.kuaidi100.com/result.jsp?nu=" . $order['back_invoice_no'];
            } else {
                $shipping_code = Shipping::where(['shipping_id' => $order['back_shipping_name']])->value('shipping_code');

                $shipping = CommonRepository::shippingInstance($shipping_code);
                if (!is_null($shipping)) {
                    $code_name = $shipping->get_code_name();
                    $order['back_invoice_no_btn'] = route('tracker.query', [
                        'company' => $order['back_other_shipping'],
                        'type' => $code_name,
                        'postid' => $order['back_invoice_no']
                    ]);
                }
            }
        }

        // 退换货可用配送列表
        $shipping_list = app(ShippingService::class)->returnShippingList();

        if ($shipping_list) {
            foreach ($shipping_list as $key => $val) {
                $shipping_cfg = unserialize_config($val['configure']);
                $shipping_fee = 0;

                $shipping_list[$key]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($shipping_fee);
                $shipping_list[$key]['shipping_fee'] = $shipping_fee;
                $shipping_list[$key]['free_money'] = $this->dscRepository->getPriceFormat($shipping_cfg['free_money']);
                if (isset($val['insure']) && !empty($val['insure'])) {
                    $shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? $this->dscRepository->getPriceFormat($val['insure']) : $val['insure'];
                }
            }

            $order['shipping_list'] = $shipping_list ? array_values($shipping_list) : [];
        }

        $order['status'] = $order['return_status'];
        $order['refound'] = $order['refound_status'];

        //获取退换货扩展信息
        $aftersn = OrderReturnExtend::where('ret_id', $ret_id)->value('aftersn');

        //获取退换货扩展信息 如果存在贡云退换货信息  获取退换货地址
        $order['cloud_return_info'] = !empty($aftersn) ? $this->jigonManageService->jigonRefundAddress(['afterSn' => $aftersn]) : [];

        // 查询商家地址库
        $shopAddress = $this->shopAddressService->getAddressByRuID($order['ru_id']);
        $order['shop_address'] = [];
        foreach ($shopAddress['data'] as $item) {
            // 匹配退换货地址
            if ($item['type'] == 1) {
                $order['shop_address'] = $item;
            }
        }

        // 在线退款查询
        $this->refundCheck($order['return_sn']);

        return $order;
    }

    /**
     * 退货单信息
     *
     * @param int $ret_id
     * @param string $order_sn
     * @param int $order_id
     * @param int $user_id
     * @return mixed
     * @throws \Exception
     */
    public function return_order_info($ret_id = 0, $order_sn = '', $order_id = 0, $user_id = 0)
    {
        $ret_id = intval($ret_id);
        if ($ret_id > 0) {
            $where = [
                'ret_id' => $ret_id,
                'user_id' => $user_id
            ];

            $select = ['rec_id', 'ret_id', 'goods_id', 'return_number', 'refound'];
            if (file_exists(MOBILE_DRP)) {
                array_push($select, 'membership_card_discount_price');
            }

            $res = ReturnGoods::select($select)
                ->whereHasIn('getOrderReturn', function ($query) use ($where) {
                    $query = $query->where('ret_id', $where['ret_id']);

                    if ($where['user_id'] > 0) {
                        $query->where('user_id', $where['user_id']);
                    }
                });

            $res = $res->with([
                'getOrderReturn',
                'getGoods' => function ($query) {
                    $query->select('goods_id', 'goods_thumb', 'goods_name', 'shop_price', 'user_id AS ru_id');
                },
                'getOrderReturnExtend' => function ($query) {
                    $query->select('ret_id', 'return_number');
                }
            ]);

            $res = $res->orderBy('rg_id', 'DESC');

            $res = BaseRepository::getToArrayFirst($res);
            $res = BaseRepository::getArrayMerge($res, $res['get_order_return'] ?? []);
            $res = BaseRepository::getArrayMerge($res, $res['get_goods'] ?? []);
            $res = BaseRepository::getArrayMerge($res, $res['get_order_return_extend'] ?? []);

            if ($res) {
                $order = OrderInfo::select('order_id', 'order_sn', 'add_time', 'chargeoff_status', 'goods_amount', 'discount', 'chargeoff_status as order_chargeoff_status', 'is_zc_order', 'country', 'province', 'city', 'district', 'street')
                    ->where('order_id', $res['order_id'])
                    ->with([
                        'getDeliveryOrder' => function ($query) {
                            $query->select('delivery_id', 'order_id', 'delivery_sn', 'update_time', 'how_oos', 'shipping_fee', 'insure_fee', 'invoice_no');
                        },
                        'getRegionProvince' => function ($query) {
                            $query->select('region_id', 'region_name');
                        },
                        'getRegionCity' => function ($query) {
                            $query->select('region_id', 'region_name');
                        },
                        'getRegionDistrict' => function ($query) {
                            $query->select('region_id', 'region_name');
                        },
                        'getRegionStreet' => function ($query) {
                            $query->select('region_id', 'region_name');
                        }
                    ]);

                $order = BaseRepository::getToArrayFirst($order);
                $order = BaseRepository::getArrayMerge($order, $order['get_delivery_order']);

                $res = BaseRepository::getArrayMerge($res, $order);

                if ($res && $res['chargeoff_status'] != 0) {
                    $res['chargeoff_status'] = $res['order_chargeoff_status'] ? $res['order_chargeoff_status'] : 0;
                }
            }

            $order = $res;
        } else {
            $order = OrderReturn::whereRaw(1);
            if ($order_id) {
                $order = $order->where('order_id', $order_id);
            } else {
                $order = $order->where('order_sn', $order_sn);
            }

            if ($user_id > 0) {
                $order = $order->where('user_id', $user_id);
            }

            $order = $order->with([
                'getReturnGoods' => function ($query) {
                    $query->select('ret_id', 'return_number', 'refound');
                },
                'getRegionProvince' => function ($query) {
                    $query->select('region_id', 'region_name');
                },
                'getRegionCity' => function ($query) {
                    $query->select('region_id', 'region_name');
                },
                'getRegionDistrict' => function ($query) {
                    $query->select('region_id', 'region_name');
                },
                'getRegionStreet' => function ($query) {
                    $query->select('region_id', 'region_name');
                }
            ]);
            $order = BaseRepository::getToArrayFirst($order);
        }

        if ($order) {
            if (!isset($order['goods_coupons'])) {
                $order['goods_coupons'] = $order['get_order_return']['goods_coupons'] ?? 0;
            }

            if (!isset($order['goods_bonus'])) {
                $order['goods_bonus'] = $order['get_order_return']['goods_bonus'] ?? 0;
            }

            if (!isset($order['goods_favourable'])) {
                $order['goods_favourable'] = $order['get_order_return']['goods_favourable'] ?? 0;
            }

            if (!isset($order['value_card_discount'])) {
                $order['value_card_discount'] = $order['get_order_return']['value_card_discount'] ?? 0;
            }

            $order['formated_goods_coupons'] = $this->dscRepository->getPriceFormat($order['goods_coupons']);
            $order['formated_goods_bonus'] = $this->dscRepository->getPriceFormat($order['goods_bonus']);
            $order['formated_goods_favourable'] = $this->dscRepository->getPriceFormat($order['goods_favourable']);
            $order['formated_value_card_discount'] = $this->dscRepository->getPriceFormat($order['value_card_discount']);

            if ($order['discount'] > 0) {
                $discount_percent = $order['discount'] / $order['goods_amount'];
                $order['discount_percent_decimal'] = number_format($discount_percent, 2, '.', '');
                $order['discount_percent'] = $order['discount_percent_decimal'] * 100;
            } else {
                $order['discount_percent_decimal'] = 0;
                $order['discount_percent'] = 0;
            }

            $order['attr_val'] = is_null($order['attr_val']) ? '' : (is_string($order['attr_val']) ? $order['attr_val'] : unserialize($order['attr_val']));
            $order['apply_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['apply_time']);
            $order['formated_return_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['return_time']);
            $order['formated_add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['add_time']);
            $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

            if ($ret_id > 0) {
                $return_number = $order['return_number'] ?? 0;
            } else {
                $return_goods = $order['get_return_goods'] ?? [];
                $return_number = $order['return_number'] = $return_goods['return_number'] ?? 0;
            }

            //获取订单商品总数
            $all_goods_number = OrderGoods::selectRaw("SUM(goods_number) AS goods_number")->where('order_id', $order['order_id'])->value('goods_number');

            //如果订单只有一个商品  折扣金额为全部折扣  否则按折扣比例计算
            if ($order['goods_favourable'] > 0) {
                $order['discount_amount'] = $order['goods_favourable'];
            } else {
                if ($return_number == $all_goods_number) {
                    $order['discount_amount'] = number_format($order['discount']);
                } else {
                    $order['discount_amount'] = number_format($order['should_return'] * $order['discount_percent_decimal'], 2, '.', ''); //折扣金额
                }
            }


            $order['should_return'] = number_format($order['should_return'], 2, '.', '');

            $return_amount = $order['should_return'] + $order['return_shipping_fee'] - $order['discount_amount'];
            $should_return = $order['should_return'];

            if (CROSS_BORDER === true) { // 跨境多商户
                $order['formated_return_rate_price'] = $this->dscRepository->getPriceFormat($order['return_rate_price'], false);
                $should_return = $order['should_return'] + $order['return_rate_price'];
                $return_amount = $order['should_return'] + $order['return_shipping_fee'] + $order['return_rate_price'] - $order['discount_amount'];
            }

            $return_amount = $return_amount - $order['goods_bonus'] - $order['goods_coupons'] - $order['value_card_discount'];

            // 订单退款 不退开通会员权益卡购买金额
            if (file_exists(MOBILE_DRP) && $ret_id > 0) {
                $order['membership_card_discount_price_formated'] = $this->dscRepository->getPriceFormat($order['membership_card_discount_price'], false);
            }
            $order['formated_goods_amount'] = $this->dscRepository->getPriceFormat($order['should_return']);
            $order['formated_discount_amount'] = $this->dscRepository->getPriceFormat($order['discount_amount']);
            $order['formated_should_return'] = $this->dscRepository->getPriceFormat($should_return - $order['discount_amount'] - $order['goods_bonus'] - $order['goods_coupons'] - $order['value_card_discount']);
            $order['formated_return_shipping_fee'] = $this->dscRepository->getPriceFormat($order['return_shipping_fee']);
            $order['formated_return_amount'] = $this->dscRepository->getPriceFormat($return_amount);
            $order['formated_actual_return'] = $this->dscRepository->getPriceFormat($order['actual_return']);

            $order['return_status1'] = $order['return_status'];
            if ($order['return_status'] < 0) {
                $order['return_status1'] = lang('user.only_return_money');
            } else {
                $order['return_status1'] = lang('user.rf.' . $order['return_status']);
            }
            $order['shop_price'] = $this->dscRepository->getPriceFormat($order['shop_price']);

            //修正退货单状态
            if ($order['return_type'] == 0) {
                if ($order['return_status'] == 4) {
                    $order['refound_status'] = FF_MAINTENANCE;
                } else {
                    $order['refound_status'] = FF_NOMAINTENANCE;
                }
            } elseif ($order['return_type'] == 1) {
                if ($order['refound_status'] == 1) {
                    $order['refound_status'] = FF_REFOUND;
                } else {
                    $order['refound_status'] = FF_NOREFOUND;
                }
            } elseif ($order['return_type'] == 2) {
                if ($order['return_status'] == 4) {
                    $order['refound_status'] = FF_EXCHANGE;
                } else {
                    $order['refound_status'] = FF_NOEXCHANGE;
                }
            } elseif ($order['return_type'] == 3) {
                if ($order['refound_status'] == 1) {
                    $order['refound_status'] = FF_REFOUND;
                } else {
                    $order['refound_status'] = FF_NOREFOUND;
                }
            }
            $order['refound_status1'] = lang('user.ff.' . $order['refound_status']);

            /* 取得区域名 */
            $province = $order['get_region_province']['region_name'] ?? '';
            $city = $order['get_region_city']['region_name'] ?? '';
            $district = $order['get_region_district']['region_name'] ?? '';
            $street = $order['get_region_street']['region_name'] ?? '';
            $order['address_detail'] = $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $order['address'];

            $order['goods_thumb'] = $this->dscRepository->getImagePath($order['goods_thumb']);

            // 退换货原因
            $parent_id = ReturnCause::where('cause_id', $order['cause_id'])->value('parent_id');
            $parent = ReturnCause::where('cause_id', $parent_id)->value('cause_name');

            $child = ReturnCause::where('cause_id', $order['cause_id'])
                ->value('cause_name');
            if ($parent) {
                $order['return_cause'] = $parent . "-" . $child;
            } else {
                $order['return_cause'] = $child;
            }

            if ($order['return_status'] == REFUSE_APPLY) {
                $order['action_note'] = ReturnAction::where('ret_id', $order['ret_id'])
                    ->where('return_status', REFUSE_APPLY)
                    ->orderBy('log_time', 'DESC')
                    ->value('action_note');
            }

            if ($order['back_shipping_name']) {
                if ($order['back_shipping_name'] == '999') {
                    $order['back_shipp_shipping'] = $order['back_other_shipping'];
                } else {
                    $order['back_shipp_shipping'] = get_shipping_name($order['back_shipping_name']);
                }
            }
            if ($order['out_shipping_name']) {
                if ($order['out_shipping_name'] == '999') {
                    $order['out_shipp_shipping'] = '其他快递';
                } else {
                    $order['out_shipp_shipping'] = get_shipping_name($order['out_shipping_name']);
                }
            }

            //下单，商品单价
            $goods_price = OrderGoods::where('order_id', $order['order_id'])
                ->where('goods_id', $order['goods_id'])
                ->value('goods_price');
            $order['goods_price'] = $this->dscRepository->getPriceFormat($goods_price);

            // 取得退换货商品客户上传图片凭证
            $where = [
                'user_id' => $order['user_id'],
                'rec_id' => $order['rec_id']
            ];
            $order['img_list'] = $this->orderRefoundService->getReturnImagesList($where);

            $order['img_count'] = count($order['img_list']);

            //IM or 客服
            if (config('shop.customer_service') == 0) {
                $ru_id = 0;
            } else {
                $ru_id = $order['ru_id'];
            }

            $merchantList = MerchantDataHandleService::getMerchantInfoDataList([$ru_id]);

            $shop_information = $merchantList[$ru_id] ?? []; //通过ru_id获取到店铺信息;
            $order['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : 0; //平台是否允许商家使用"在线客服";

            $order['shop_name'] = $shop_information['shop_name'] ?? '';
        }

        return $order;
    }

    /**
     * 提交退换货
     *
     * @param int $user_id
     * @param array $request_info
     * @return array
     * @throws \Exception
     */
    public function submitReturn($user_id = 0, $request_info = [])
    {
        DB::beginTransaction(); // 开启事务
        try {
            $this->orderRefoundService->submitReturn($user_id, $request_info, trans('common.buyer'));
        } catch (HttpException $httpException) {
            DB::rollBack(); // 回滚事务
            return ['code' => $httpException->getCode(), 'msg' => $httpException->getMessage()];
        }

        DB::commit(); // 提交事务

        /* 更新会员订单信息 */
        $this->orderCommonService->getUserOrderNumServer($user_id);

        //视频号订单退换货事件
        event(new \App\Events\OrderRefoundEvent($request_info, []));

        return ['code' => 0, 'msg' => trans('user.Apply_Success_Prompt')];
    }

    /**
     * 在线退款查询（第三方在线支付 微信支付）
     *
     * @param string $return_sn
     * @return bool
     */
    public function refundCheck($return_sn = '')
    {
        if (empty($return_sn)) {
            return false;
        }

        /**
         * 已支付 未退款的 可以手动查询、 退款申请时有异步通知
         */
        $model = OrderReturn::where('return_sn', $return_sn)->where('agree_apply', 1);
        $model = $model->with([
            'orderInfo' => function ($query) {
                $query->select('order_id', 'order_sn', 'pay_id', 'pay_status', 'money_paid', 'referer');
            }
        ]);
        $model = $model->first();
        $return_order = $model ? $model->toArray() : [];

        if ($return_order) {
            $return_order = collect($return_order)->merge($return_order['order_info'])->except('order_info')->all();
            if ($return_order['pay_status'] == PS_PAYED && $return_order['refound_status'] == 0) {
                $payment_info = payment_info($return_order['pay_id']);

                if ($payment_info && strpos($payment_info['pay_code'], 'pay_') === false) {
                    $payObject = CommonRepository::paymentInstance($payment_info['pay_code']);
                    if (!is_null($payObject) && is_callable([$payObject, 'refundQuery'])) {
                        // 退款查询参数 $return_order['order_id']

                        $res = $payObject->refundQuery($return_order);
                        if ($res) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * 编辑退换货快递信息
     *
     * @param int $user_id
     * @param int $ret_id
     * @param int $back_shipping_name
     * @param string $back_other_shipping
     * @param string $back_invoice_no
     * @return array
     */
    public function editExpress($user_id = 0, $ret_id = 0, $back_shipping_name = 0, $back_other_shipping = '', $back_invoice_no = '')
    {
        try {
            $this->orderRefoundService->editExpress($user_id, $ret_id, $back_shipping_name, $back_other_shipping, $back_invoice_no);
        } catch (HttpException $httpException) {
            return ['code' => $httpException->getCode(), 'msg' => $httpException->getMessage()];
        }

        return ['code' => 0, 'msg' => trans('user.edit_shipping_success')];
    }

    /**
     * 取消退换货订单
     *
     * @param $user_id
     * @param $ret_id
     * @return array|bool
     */
    public function cancel_return($user_id = 0, $ret_id = 0)
    {
        try {
            $this->orderRefoundService->cancelReturnOrder($user_id, $ret_id);
        } catch (HttpException $httpException) {
            return ['code' => $httpException->getCode(), 'msg' => $httpException->getMessage()];
        }

        /* 更新会员订单信息 */
        $this->orderCommonService->getUserOrderNumServer($user_id);

        return ['code' => 0, 'msg' => 'success'];
    }

    /**
     * 退换货订单确认收货
     *
     * @param int $user_id
     * @param int $ret_id
     * @return array
     */
    public function affirmReceivedOrderReturn($user_id = 0, $ret_id = 0)
    {
        try {
            $this->orderRefoundService->receivedReturnOrder($user_id, $ret_id);
        } catch (HttpException $httpException) {
            return ['code' => $httpException->getCode(), 'msg' => $httpException->getMessage()];
        }

        /* 更新会员订单信息 */
        $this->orderCommonService->getUserOrderNumServer($user_id);

        return ['code' => 0, 'msg' => lang('user.received') . lang('admin/common.success')];
    }

    /**
     * 激活退换货订单
     *
     * @param $user_id
     * @param int $ret_id
     * @return mixed
     */
    public function activationReturnOrder($user_id = 0, $ret_id = 0)
    {
        try {
            $this->orderRefoundService->activeReturnOrder($user_id, $ret_id);
        } catch (HttpException $httpException) {
            return ['code' => 1, 'msg' => $httpException->getMessage()];
        }

        return ['code' => 0, 'msg' => lang('user.activation_return') . lang('admin/common.success')];
    }

    /**
     * 删除已完成退换货订单
     *
     * @param int $user_id
     * @param int $ret_id
     * @return array
     */
    public function deleteReturnOrder($user_id = 0, $ret_id = 0)
    {
        try {
            $this->orderRefoundService->deleteReturnOrder($user_id, $ret_id);
        } catch (HttpException $httpException) {
            return ['code' => 1, 'msg' => $httpException->getMessage()];
        }

        return ['code' => 1, 'msg' => 'fail'];
    }

    /**
     * 通过user_id获取用户购买的第一个分销商商品的rec_id
     * @param int $user_id
     * @return array
     */
    public function getOrderGoodsDrp($user_id = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        //membership_card_id 升级分销商商品标记
        $model = OrderGoods::where('membership_card_id', '>', 0);
        $model = $model->whereHasIn('getOrder', function ($query) {
            $query->where('pay_status', PS_PAYED);
        });
        $model = $model->where('user_id', $user_id);
        $model = $model->select('rec_id', 'user_id', 'membership_card_id', 'order_id');
        $res = $model->orderBy('rec_id', 'asc')->get();
        $res = $res ? $res->toArray() : [];
        if (empty($res)) {
            return [];
        }

        $list = [];
        foreach ($res as $key => $val) {
            if (!isset($list[$val['membership_card_id']])) {
                $list[$val['membership_card_id']] = $val['rec_id'];
            }
        }
        return $list ?? [];
    }
}
