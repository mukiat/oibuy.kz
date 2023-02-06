<?php

namespace App\Repositories\Common;

use App\Kernel\Repositories\Common\CommonRepository as Base;
use App\Mail\SendViews;
use App\Models\Sms;
use App\Models\Users;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class CommonRepository
 * @method static getManageIsOnly($object, $where = []) 查询是否已存在值
 * @method static shippingInstance($shipping_code = '') 返回配送方式实例
 * @method static paymentInstance($pay_code = '') 返回支付方式实例
 * @method static getComboGodosAttr($attr_array = [], $goods_attr_id = 0) 组合购买商品属性
 * @method static dscCheckReferer($str = '') 检查地址访问来源是否一致，返回true|false
 * @method static getMatchEmail($email = '') 判断是否邮箱
 * @method static getMatchPhone($phone = '') 判断是否手机
 * @method static getMatchTime($time = '') 检查是否为一个合法的时间格式
 * @method static notifyPay() 处理异步操作
 * @method static htmlPages($count = 0, $page = 1) 创建分页的列表
 * @method static htmlRadios($name = '', $options = [], $checked = 0) 单选表单
 * @method static getAdminPathType($type = -1, $start = 1) 判断所属后台
 * @method static createUeditorEditor($input_name, $input_value = '', $input_height = 486, $type = 0) 生成百度编辑器
 * @method static tableField() 更新字段
 * @method static collateOrderValueCard($value_cart_list = [], $orderValueCard = 0, $goods_value_card = 0, $order_id = 0, $field = 'rec_id') 核对均摊储值卡商品金额是否大于订单储值卡金额
 * @method static collateOrderValueCardDiscount($value_card_discount_list = [], $orderValueCardDiscount = 0, $goods_value_card_discount = 0, $order_id = 0, $field = 'rec_id') 核对均摊储值卡商品折扣金额是否大于订单储值卡金额
 * @method static collateCartGoodsCoupons($coupons_list = [], $orderCoupons = 0, $goods_coupons = 0) 核对均摊优惠券购物车商品金额是否大于使用优惠券金额
 * @method static collateCartGoodsBonus($coupons_list = [], $orderCoupons = 0, $goods_coupons = 0) 核对均摊红包购物车商品金额是否大于使用红包金额
 * @method static xmlEncode($data = [], $root = 'dsc', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')  生成XML格式
 * @method static xmlDecode($xml = '') XML数据解码数组
 * @method static isConciseWxapp() 简洁版小程序
 * @package App\Repositories\Common
 */
class CommonRepository extends Base
{

    /**
     * 发送短信
     *
     * @param int $mobile
     * @param string $content
     * @param string $send_time
     * @param bool $msg
     * @param array $sms_list
     * @return bool
     * @throws \Exception
     */
    public function smsSend($mobile = 0, $content = '', $send_time = '', $msg = true, $sms_list = [])
    {
        $sms = Sms::query()->get();
        $sms = $sms ? $sms->toArray() : [];

        if (empty($sms_list)) {
            if (!empty($sms)) {
                $sms_list = parent::sms_list($sms);
                $sms_list['is_sms'] = count($sms);
            } else {
                $sms_list = parent::Chuanglan();
            }
        }

        return parent::smsSend($mobile, $content, $send_time, $msg, $sms_list);
    }

    /**
     * 发送邮件
     *
     * @param string $name 接收人姓名
     * @param string $email 接收人邮件地址
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @param int $type 0 普通邮件， 1 HTML邮件
     * @return bool
     * @throws \Exception
     */
    public static function sendEmail($name = '', $email = '', $subject = '', $content = '', $type = 0)
    {
        if (empty($email)) {
            return false;
        }

        if (empty(config('shop.smtp_user', '')) || empty(config('shop.smtp_pass', ''))) {
            return false;
        }

        // 验证邮箱地址格式
        if (!self::getMatchEmail($email)) {
            return false;
        }

        /**
         * to 方法接受 邮件地址、用户实例或用户集合
         * $email = collect(['email' => 'test@126.com'])->all(); 或 $email = 'test@126.com';
         */

        // 发送邮件内容
        $mailContent = [
            'template_subject' => $subject,
            'template_content' => $content,
            'is_html' => $type
        ];

        try {
            Mail::to($email)->send(new SendViews($mailContent));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        // 返回的一个错误数组，利用此可以判断是否发送成功
        if (count(Mail::failures()) < 1) {
            // 发送成功
            return true;
        } else {
            // 发送失败
            return false;
        }
    }

    /**
     * 判断是否支持供应链
     */
    public static function judgeSupplierEnabled()
    {
        if (is_dir(app_path('Modules/Suppliers'))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 保存分销推荐人uid 适用于api
     *
     * @param int $uid
     * @param int $parent_id
     * @return bool
     * @throws \Exception
     */
    public static function setDrpShopAffiliate($uid = 0, $parent_id = 0)
    {
        if (empty($uid) || empty($parent_id)) {
            return false;
        }

        if (file_exists(MOBILE_DRP)) {
            // 开启分销
            $drp_config = \App\Modules\Drp\Services\Drp\DrpConfigService::drpConfig('drp_affiliate_on');
            $drp_affiliate = $drp_config['value'] ?? 0;
            if ($drp_affiliate == 1) {
                $affiliate = config('shop.affiliate');
                $config = !empty($affiliate) ? unserialize($affiliate) : [];

                if (!empty($config['config']['expire'])) {
                    if ($config['config']['expire_unit'] == 'hour') {
                        $expiresAt = Carbon::now()->addHours($config['config']['expire']);
                    } elseif ($config['config']['expire_unit'] == 'day') {
                        $expiresAt = Carbon::now()->addDays($config['config']['expire']);
                    } elseif ($config['config']['expire_unit'] == 'week') {
                        $expiresAt = Carbon::now()->addWeeks($config['config']['expire']);
                    } else {
                        $expiresAt = Carbon::now()->addDays(1);
                    }

                    cache()->put('dscmall_affiliate_drp_id' . $uid, intval($parent_id), $expiresAt);
                } else {
                    // 过期时间为 1 天
                    cache()->put('dscmall_affiliate_drp_id' . $uid, intval($parent_id), Carbon::now()->addDays(1));
                }
            }
        }
    }

    /**
     * 获取分销推荐uid 适用于api
     *
     * @param int $uid
     * @param int $forget
     * @return \Illuminate\Cache\CacheManager|int|mixed
     * @throws \Exception
     */
    public static function getDrpShopAffiliate($uid = 0, $forget = 0)
    {
        $parent_id = cache('dscmall_affiliate_drp_id' . $uid);

        if (file_exists(MOBILE_DRP) && !is_null($parent_id)) {
            $parent_id = intval($parent_id);

            // 检查是否分销商
            $count = \App\Modules\Drp\Models\DrpShop::where('user_id', $parent_id)->where('audit', 1)->count();

            if ($forget > 0) {
                cache()->forget('dscmall_affiliate_drp_id' . $uid); // 失效
            }

            if ($count > 0) {
                return $parent_id;
            }
        }

        return 0;
    }

    /**
     * 保存分销推荐人uid 适用于web
     *
     * @param int $uid
     * @return bool
     * @throws \Exception
     */
    public static function setDrpAffiliate($uid = 0)
    {
        if (empty($uid)) {
            return false;
        }

        if (file_exists(MOBILE_DRP)) {

            $shopConfig = config('shop');

            // 开启分销
            $drp_config = \App\Modules\Drp\Services\Drp\DrpConfigService::drpConfig('drp_affiliate_on');
            $drp_affiliate = $drp_config['value'] ?? 0;
            if ($drp_affiliate == 1) {
                $config = $shopConfig['affiliate'] ? unserialize($shopConfig['affiliate']) : [];

                if (!empty($config['config']['expire'])) {
                    if ($config['config']['expire_unit'] == 'hour') {
                        $minutes = $config['config']['expire'] * 60; // 小时
                    } elseif ($config['config']['expire_unit'] == 'day') {
                        $minutes = $config['config']['expire'] * 24 * 60;// 天
                    } elseif ($config['config']['expire_unit'] == 'week') {
                        $minutes = $config['config']['expire'] * 7 * 24 * 60; // 周
                    } else {
                        $minutes = 24 * 60;// 天
                    }
                    // 过期时间（以分钟为单位）
                    cookie()->queue('dscmall_affiliate_drp_id', intval($uid), $minutes);
                } else {
                    // 过期时间（以分钟为单位）
                    $minutes = 24 * 60;
                    cookie()->queue('dscmall_affiliate_drp_id', intval($uid), $minutes);
                }
            }
        }
    }

    /**
     * 获取分销推荐uid 适用于 web
     *
     * @return \Illuminate\Cache\CacheManager|int|mixed
     * @throws \Exception
     */
    public static function getDrpAffiliate()
    {
        $uid = request()->cookie('dscmall_affiliate_drp_id');

        if (file_exists(MOBILE_DRP) && !is_null($uid) && $uid > 0) {
            $uid = intval($uid);

            // 检查是否分销商
            $count = \App\Modules\Drp\Models\DrpShop::where('user_id', $uid)->where('audit', 1)->count();

            if ($count > 0) {
                return $uid;
            }
        }

        cookie()->queue(cookie()->forget('dscmall_affiliate_drp_id'));
        return 0;
    }

    /**
     * 保存会员推荐uid
     *
     * @param int $uid
     * @return bool
     * @throws \Exception
     */
    public static function setUserAffiliate($uid = 0)
    {
        if (empty($uid)) {
            return false;
        }

        $shopConfig = config('shop');

        $config = $shopConfig['affiliate'] ? unserialize($shopConfig['affiliate']) : [];

        if (!empty($uid) && $config['on'] == 1) {
            if (!empty($config['config']['expire'])) {
                if ($config['config']['expire_unit'] == 'hour') {
                    $minutes = $config['config']['expire'] * 60; // 小时
                } elseif ($config['config']['expire_unit'] == 'day') {
                    $minutes = $config['config']['expire'] * 24 * 60;// 天
                } elseif ($config['config']['expire_unit'] == 'week') {
                    $minutes = $config['config']['expire'] * 7 * 24 * 60; // 周
                } else {
                    $minutes = 24 * 60;// 天
                }

                // 过期时间（以分钟为单位）
                cookie()->queue('dscmall_affiliate_uid', intval($uid), $minutes);
            } else {
                // 过期时间（以分钟为单位）
                $minutes = 24 * 60;
                cookie()->queue('dscmall_affiliate_uid', intval($uid), $minutes);
            }
        }
    }

    /**
     * 获取会员推荐uid
     *
     * @return int
     */
    public static function getUserAffiliate()
    {
        $uid = request()->cookie('dscmall_affiliate_uid');

        if (!is_null($uid) && $uid > 0) {
            $uid = intval($uid);

            $count = Users::where('user_id', $uid)->count();

            if ($count > 0) {
                return $uid;
            }
        }

        cookie()->queue(cookie()->forget('dscmall_affiliate_uid'));
        return 0;
    }

    /**
     * 查询票税金额
     *
     * @param int $goods_price
     * @param string $inv_content
     * @return float|int
     */
    public static function orderInvoiceTotal($goods_price = 0, $inv_content = '')
    {
        $commonRep = new self();
        $invoice = $commonRep->getInvoiceList(config('shop.invoice_type'), 1, $inv_content);

        $tax = 0;
        if ($invoice) {
            $rate = floatval($invoice['rate']) / 100;
            if ($rate > 0) {
                $tax = $rate * $goods_price;
            }
        }

        return $tax;
    }

    /**
     * 获取票税列表
     *
     * @param $invoice
     * @param int $order_type
     * @param string $inv_content
     * @return array
     */
    public static function getInvoiceList($invoice, $order_type = 0, $inv_content = '')
    {
        $arr = [];
        if (isset($invoice['type']) && $invoice['type']) {
            $type = array_values($invoice['type']);
            $rate = array_values($invoice['rate']);

            for ($i = 0; $i < count($type); $i++) {
                if ($order_type == 1) {
                    if ($type[$i] == $inv_content) {
                        $arr['type'] = $type[$i];
                        $arr['rate'] = $rate[$i];
                    }
                } else {
                    $arr[$i]['type'] = $type[$i];
                    $arr[$i]['rate'] = $rate[$i];
                }
            }
        }

        return $arr;
    }

    /**
     * 关键词分词
     * @param string $insert_keyword
     * @param int $top 搜索权重
     * @return array|string
     */
    public static function scwsWord($insert_keyword = '', $top = 20)
    {
        if (empty($insert_keyword)) {
            return '';
        }

        $keyword_arr = [];

        // 关键词分词 权重综合最大前20
        $scws_res = scws($insert_keyword, $top);

        if ($scws_res) {
            $keyword_arr = explode(',', $scws_res);
        }

        // 按空格分词
        if ($insert_keyword && strpos($insert_keyword, ' ') !== false) {
            $insert_keyword_arr = explode(' ', $insert_keyword);
            $keyword_arr = array_merge($insert_keyword_arr, $keyword_arr);
        }

        // 插入原关键词
        array_push($keyword_arr, $insert_keyword);

        // 数组排重
        $keyword_arr = !empty($keyword_arr) ? array_unique($keyword_arr) : [];

        if ($keyword_arr && count($keyword_arr) > 1) {
            foreach ($keyword_arr as $ak => $av) {
                if (is_numeric($av)) {
                    unset($keyword_arr[$ak]);
                }
            }
        }

        return !empty($keyword_arr) ? array_values($keyword_arr) : [];
    }

    /**
     * 自动确认收货的条件
     *
     * @param int $order_status
     * @param int $shipping_status
     * @param int $pay_status
     * @return bool
     */
    public static function orderDeliveryCondition($order_status = 0, $shipping_status = 0, $pay_status = 0)
    {
        $order = [
            OS_CONFIRMED,
            OS_SPLITED,
            OS_SPLITING_PART,
            OS_RETURNED_PART,
            OS_ONLY_REFOUND
        ];

        $pay = [
            PS_PAYED,
            PS_REFOUND_PART
        ];

        $shipping = [
            SS_SHIPPED,
            SS_SHIPPED_PART,
            OS_SHIPPED_PART
        ];

        return (in_array($order_status, $order)) && (in_array($shipping_status, $shipping)) && (in_array($pay_status, $pay));
    }

    /**
     * 后台管理员ID
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed|string
     */
    public static function getAdminName()
    {
        $self = explode("/", substr(PHP_SELF, 1));
        $count = count($self);

        $admin_name = '';
        if ($count > 1) {

            $commonRep = new self();

            $real_path = $self[$count - 2];
            if ($real_path == ADMIN_PATH) {
                $admin_name = session('admin_name');
            } elseif ($real_path == SELLER_PATH) {
                $admin_name = session('seller_name');
            } elseif ($real_path == SUPPLLY_PATH) {
                $supplierEnabled = $commonRep->judgeSupplierEnabled();
                if ($supplierEnabled) {
                    $admin_name = session('supply_name');
                }
            }
        }

        return $admin_name;
    }

    /**
     * 优惠券类型格式化
     *
     * @param int $cou_type
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     * @throws \Exception
     */
    public static function couTypeFormat($cou_type = 0)
    {
        switch ($cou_type) {
            case VOUCHER_ALL:
                return lang('common.lang_goods_coupons.all_pay');
                break;
            case VOUCHER_USER:
                return lang('common.lang_goods_coupons.user_pay');
                break;
            case VOUCHER_SHOPING:
                return lang('common.lang_goods_coupons.goods_pay');
                break;
            case VOUCHER_LOGIN:
                return lang('common.lang_goods_coupons.reg_pay');
                break;
            case VOUCHER_SHIPPING:
                return lang('common.lang_goods_coupons.free_pay');
                break;
            default:
                return lang('common.lang_goods_coupons.not_pay');
                break;
        }
    }

    /**
     * 使用限制条件格式化
     *
     * @param $conditon
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     * @throws \Exception
     */
    public static function conditionFormat($conditon)
    {
        switch ($conditon) {
            case 1:
                return lang('common.spec_cat');
                break;
            case 2:
                return lang('common.spec_goods');
                break;
            case 0:
                return lang('common.all_goods');
            default:
                return 'N/A';
                break;
        }
    }

    /**
     * 生成XML格式
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id 数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     *
     * xmlEncode($data = [], $root = 'dsc', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')  生成XML格式
     */

    /**
     * 创建分页的列表
     *
     * @param int $count
     * @param int $page
     * @return string
     */
    public static function pages($count = 0, $page = 1)
    {

        $str = '';
        $len = 10;

        if (empty($page)) {
            $page = 1;
        }

        if (!empty($count)) {
            $step = 1;
            $str .= "<option value='1'>1</option>";

            for ($i = 2; $i < $count; $i += $step) {
                $step = ($i >= $page + $len - 1 || $i <= $page - $len + 1) ? $len : 1;
                $str .= "<option value='$i'";
                $str .= $page == $i ? " selected='true'" : '';
                $str .= ">$i</option>";
            }

            if ($count > 1) {
                $str .= "<option value='$count'";
                $str .= $page == $count ? " selected='true'" : '';
                $str .= ">$count</option>";
            }
        }

        return $str;
    }

    /**
     * 优化提高查询性能
     *
     * @param $query
     * @param string $field
     * @return mixed
     */
    public static function constantMaxId($query = null, $field = '')
    {
        if (config('shop.app_field') == 1) {

            if (stripos('user_id', $field) !== false || stripos('ru_id', $field) !== false) {
                $query = $query->whereBetween($field, [1, config('app.seller_user')]);
            }

            if (stripos('order_id', $field) !== false) {
                $query = $query->whereBetween($field, [1, config('app.order_id')]);
            }

            if (stripos('rec_id', $field) !== false) {
                $query = $query->whereBetween($field, [1, config('app.rec_id')]);
            }

            // parent_id 为 评论comment_id
            if (stripos('comment_parent_id', $field) !== false) {
                $query = $query->whereBetween('parent_id', [1, config('app.comment_id')]);
            }
        } else {
            // parent_id 为 评论comment_id
            if (stripos('comment_parent_id', $field) !== false) {
                $query = $query->where('parent_id', '>', 0);
            } else {
                $query = $query->where($field, '>', 0);
            }
        }

        return $query;
    }

    /**
     * 创建一个JSON格式的数据
     *
     * @param string $content
     * @param int $error
     * @param string $message
     * @param array $append
     * @return \Illuminate\Http\JsonResponse
     */
    public static function makeJsonResult($content = '', $error = 0, $message = '', $append = [])
    {
        $res = ['error' => $error, 'message' => $message, 'content' => $content];

        if (!empty($append)) {
            foreach ($append as $key => $val) {
                $res[$key] = $val;
            }
        }

        return response()->json($res);
    }

    /**
     * 生成优惠券码
     *
     * @return string
     */
    public static function couponSn()
    {
        list($m, $s) = explode(' ', microtime());
        return $s . substr($m, 2, -2) . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    }

    /**
     * 是否分销商用户
     *
     * @param int $uid
     * @return \Illuminate\Cache\CacheManager|int|mixed
     * @throws \Exception
     */
    public static function drp_user_audit($uid = 0)
    {
        $drp_show_price = config('shop.drp_show_price') ?? 0;
        $drpUserAudit = cache('drp_user_audit_' . $uid) ?? 0;
        $drp_user_audit = $drp_show_price ? $drpUserAudit : 1;

        return $drp_user_audit;
    }
}
