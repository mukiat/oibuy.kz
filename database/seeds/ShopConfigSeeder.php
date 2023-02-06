<?php

use App\Models\ShopConfig;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class ShopConfigSeeder extends Seeder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * DeleteFileSeeder constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }


    /**
     * Run the database seeds.
     *
     * @throws Exception
     */
    public function run()
    {
        /* PC登录右侧 */
        $this->PcLoginRight();

        $this->update();
        $this->cloudIsOpen();
        $this->crossBorder();

        // 是否启用支付密码
        $this->usePaypwd();
        // 新增商店设置所在区域
        $this->add_shop_district();

        /* 跨境订单提交文章ID */
        $this->CrossBorderArticleId();

        /* 确认收货发短信开关 */
        $this->AffirmReceivedSmsSwitch();

        // v1.4.4 隐藏原短信配置
        $this->hiddenSms();

        // 更新商家入驻流程公司名称字段
        $this->updateMerchantField();


        // 是否启用支付手续费
        $this->usePayfee();

        // 增加支付模块分组
        $this->add_pay_group();

        //版权设置
        $this->add_copyright_set_group();

        //添加客服LOGO
        $this->add_kefu_login_log_group();

        if (file_exists(SUPPLIERS)) {
            //供应链设置
            $this->add_suppliers_set_group();
        }

        //咨询设置
        $this->add_consult_set();

        /* 兼容接口 */
        $categoryClass = '\\App\\Plugins\\Hyperf\\Category\\CategoryApi';

        if (class_exists($categoryClass)) {
            $this->businessDispose();
        }

        // app下载地址
        $this->appDownloadUrl();

        // 更新字段值
        $this->changeShowOrderType();

        // H5首页顶部推荐广告位自定义设置
        $this->add_h5_index_pro();

        // 价格样式选择项
        $this->add_price_style_choose();

        // 起始页客户服务显示
        $this->add_start_customer_service();

        // 会员余额提现设置项
        $this->add_balance_withdrawal();

        // 新增自定义客服链接
        $this->add_start_service_url();

        // 增加退款模块分组
        $this->add_return_group();

        // 商品相册图片是否保留原名称
        $this->add_upload_use_original_name();

        // 增加商品评价模块分组
        $this->add_goods_comment_group();

        $this->clearCache();

    }

    private function updateMerchantField()
    {
        $textFields = DB::table('merchants_steps_fields_centent')->where('id', 19)->value('textFields');

        DB::table('merchants_steps_fields_centent')->where('id', 19)->update(
            ['textFields' => str_replace('companyName', 'company', $textFields)]
        );
    }

    /**
     * 更新
     *
     * @throws Exception
     */
    private function update()
    {
        /* 去除复杂重写 */
        $count = ShopConfig::where('code', 'rewrite')->count();
        if ($count > 0) {
            // 默认数据
            $rows = [
                'store_range' => '0,1'
            ];
            ShopConfig::where('code', 'rewrite')->update($rows);
        }

        /* 电子面单开关 */
        $config_id = ShopConfig::where('code', 'extend_basic')->value('id');
        $config_id = $config_id ? $config_id : 0;

        $other = [
            'parent_id' => $config_id,
            'type' => 'select',
            'store_range' => '0,1',
            'value' => 0,
            'sort_order' => 1
        ];
        $count = ShopConfig::where('code', 'tp_api')->count();
        if ($count > 0) {
            ShopConfig::where('code', 'tp_api')->update($other);
        } else {
            $other['code'] = 'tp_api';
            ShopConfig::insert($other);
        }

        /* 去除发票类型税率 */
        $count = ShopConfig::where('code', 'invoice_type')->count();
        if ($count > 0) {
            /* 删除 */
            ShopConfig::where('code', 'invoice_type')->delete();
        }

        /* 隐藏是否启用首页可视化配置 */
        $count = ShopConfig::where('code', 'openvisual')->where('type', 'select')->count();
        if ($count > 0) {
            // 默认数据
            $rows = [
                'type' => 'hidden'
            ];
            ShopConfig::where('code', 'openvisual')->update($rows);
        }

        /* 去除头部右侧翻转效果图片配置 */
        $count = ShopConfig::where('code', 'site_commitment')->count();
        if ($count > 0) {
            /* 删除 */
            ShopConfig::where('code', 'site_commitment')->delete();
        }

        /* 等级积分清零开关 */
        $other = [
            'parent_id' => $config_id,
            'type' => 'hidden',
            'store_range' => '0,1',
            'value' => 0,
            'sort_order' => 1
        ];
        $count = ShopConfig::where('code', 'open_user_rank_set')->count();
        if (!$count) {
            $other['code'] = 'open_user_rank_set';
            ShopConfig::insert($other);
        }

        /* 等级积分清零时间 */
        $other = [
            'parent_id' => $config_id,
            'type' => 'hidden',
            'store_range' => '',
            'value' => 12,
            'sort_order' => 1
        ];
        $count = ShopConfig::where('code', 'clear_rank_point')->count();
        if (!$count) {
            $other['code'] = 'clear_rank_point';
            ShopConfig::insert($other);
        }

        $count = ShopConfig::where('code', 'cloud_storage')->count();
        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'cloud_storage',
                'type' => 'select',
                'store_range' => '0,1,2',
                'sort_order' => 1,
                'value' => 0,
                'shop_group' => 'cloud'
            ]);
        }

        ShopConfig::where('code', 'open_oss')->update([
            'shop_group' => 'cloud'
        ]);

        ShopConfig::where('code', 'addon')->update([
            'shop_group' => 'ecjia'
        ]);

        /* 过滤词开关 */
        $count = ShopConfig::where('code', 'filter_words_control')->count();
        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            // 默认数据
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'filter_words_control',
                'value' => 1,
                'type' => 'hidden',
                'shop_group' => 'filter_words'
            ]);
        }

        // 隐藏网站域名
        $count = ShopConfig::where('code', 'site_domain')->count();
        if ($count > 0) {
            ShopConfig::where('code', 'site_domain')
                ->update([
                    'type' => 'hidden',
                    'value' => ''
                ]);
        }


        /* 修正语言包 */
        ShopConfig::where('code', 'lang')
            ->update([
                'value' => 'zh-CN'
            ]);

        $count = ShopConfig::where('code', 'show_mobile')->count();

        if ($count < 1) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');

            /* 是否显示手机号码 */
            ShopConfig::where('code', 'show_mobile')
                ->insert([
                    'parent_id' => $parent_id,
                    'code' => 'show_mobile',
                    'value' => 1,
                    'type' => 'hidden'
                ]);
        }

        $count = ShopConfig::where('code', 'area_pricetype')->count();

        /* 商品设置地区模式时 */
        if ($count < 1) {
            $parent_id = ShopConfig::where('code', 'goods_base')->value('id');

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'area_pricetype',
                'type' => 'select',
                'store_range' => '0,1',
                'shop_group' => 'goods'
            ]);
        } else {
            ShopConfig::where('code', 'area_pricetype')
                ->update([
                    'type' => 'select'
                ]);
        }

        $count = ShopConfig::where('code', 'appkey')->count();
        if ($count == 0) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');

            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'appkey',
                'type' => 'hidden'
            ];
            ShopConfig::where('code', 'rewrite')->update($rows);
        }

        /* 去除一步购物 */
        $count = ShopConfig::where('code', 'one_step_buy')->count();
        if ($count > 0) {
            /* 删除 */
            ShopConfig::where('code', 'one_step_buy')->delete();
        }

        /* 253创蓝短信 */
        /* 253创蓝短信 用户名*/
        $count = ShopConfig::where('code', 'chuanglan_account')->count();
        if ($count == 0) {
            $parent_id = ShopConfig::where('code', 'sms')->value('id');
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'chuanglan_account',
                'type' => 'text',
            ];
            ShopConfig::insert($rows);
        }
        /* 253创蓝短信 密码*/
        $count = ShopConfig::where('code', 'chuanglan_password')->count();
        if ($count == 0) {
            $parent_id = ShopConfig::where('code', 'sms')->value('id');
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'chuanglan_password',
                'type' => 'text',
            ];
            ShopConfig::insert($rows);
        }
        /* 253创蓝短信 请求地址*/
        $count = ShopConfig::where('code', 'chuanglan_api_url')->count();
        if ($count == 0) {
            $parent_id = ShopConfig::where('code', 'sms')->value('id');
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'chuanglan_api_url',
                'type' => 'text',
            ];
            ShopConfig::insert($rows);
        }
        /* 253创蓝短信 签名*/
        $count = ShopConfig::where('code', 'chuanglan_signa')->count();
        if ($count == 0) {
            $parent_id = ShopConfig::where('code', 'sms')->value('id');
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'chuanglan_signa',
                'type' => 'text',
            ];
            ShopConfig::insert($rows);
        }
        /* 253创蓝短信  end*/

        $count = ShopConfig::where('code', 'oss_network')->count();

        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'oss_network',
                'type' => 'hidden',
                'store_range' => '0,1',
                'sort_order' => 1,
                'value' => 1
            ]);
        }

        /* 隐私 start*/
        $count = ShopConfig::where('code', 'privacy')->count();

        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'shop_info')->value('id');

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'privacy',
                'type' => 'text',
                'store_range' => '',
                'sort_order' => 1,
                'value' => ''
            ]);
        }
        /* 隐私 end*/

        ShopConfig::where('code', 'kuaidi100_key')
            ->update([
                'type' => 'hidden'
            ]);

        /**
         * 新增分类商品属性筛选类型选择
         *
         * value：1-属性只要符合存在，2-属性要全部同时符合存在
         */
        $count = ShopConfig::where('code', 'cat_attr_search')->count();

        if (empty($count)) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            ShopConfig::insert([
                'code' => 'cat_attr_search',
                'parent_id' => $parent_id,
                'type' => 'hidden',
                'value' => '1'
            ]);
        }

        /**
         * 新增商品评论开关
         *
         * value：1-开启，0-关闭
         */
        $count = ShopConfig::where('code', 'shop_can_comment')->count();

        if (empty($count)) {
            $parent_id = ShopConfig::where('code', 'goods_base')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            ShopConfig::insert([
                'code' => 'shop_can_comment',
                'parent_id' => $parent_id,
                'type' => 'select',
                'store_range' => '1,0',
                'value' => '1',
                'shop_group' => 'goods'
            ]);
        }

        /* 开启IP库存类型选择（默认IP库） */
        $type = ShopConfig::where('code', 'ip_type')->value('type');
        if ($type && $type == 'hidden') {
            ShopConfig::where('code', 'ip_type')->update([
                'type' => 'select',
                'store_range' => '0,1',
                'value' => 0
            ]);
        }

        // 移除gzip
        ShopConfig::whereIn('code', ['enable_gzip', 'skype', 'ym', 'msn'])
            ->update([
                'type' => 'hidden'
            ]);

        /**
         * 新增商品价格/库存类型
         *
         * value：1-开启，0-关闭
         */
        $count = ShopConfig::where('code', 'goods_stock_model')->count();

        if (empty($count)) {
            $parent_id = ShopConfig::where('code', 'goods_base')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            ShopConfig::insert([
                'code' => 'goods_stock_model',
                'parent_id' => $parent_id,
                'type' => 'select',
                'store_range' => '1,0',
                'value' => '0',
                'shop_group' => 'goods'
            ]);
        }

        /* 小程序 */
        if (file_exists(MOBILE_WXAPP)) {
            $wxapp_config = ShopConfig::where('code', 'wxapp_config')->count();
            if (empty($wxapp_config)) {
                // 增加微信小程序配置
                $wxappId = ShopConfig::insertGetId([
                    'parent_id' => 0,
                    'code' => 'wxapp_config',
                    'type' => 'hidden',
                    'store_range' => '',
                    'store_dir' => '',
                    'value' => '',
                    'sort_order' => 1,
                    'shop_group' => ''
                ]);

                ShopConfig::insert([
                    [
                        'parent_id' => $wxappId,
                        'code' => 'wxapp_top_img',
                        'type' => 'file',
                        'store_range' => '',
                        'store_dir' => 'images/common/',
                        'value' => '',
                        'sort_order' => 1,
                        'shop_group' => 'wxapp_config'
                    ],
                    [
                        'parent_id' => $wxappId,
                        'code' => 'wxapp_top_url',
                        'type' => 'text',
                        'store_range' => '',
                        'store_dir' => '',
                        'value' => '',
                        'sort_order' => 2,
                        'shop_group' => 'wxapp_config'
                    ]
                ]);
            } else {
                ShopConfig::where('code', 'wxapp_top_img')->update([
                    'type' => 'file'
                ]);

                ShopConfig::where('code', 'wxapp_top_url')->update([
                    'type' => 'text'
                ]);
            }
        }

        /**
         * 自动确认收货配置
         */
        $count = ShopConfig::where('code', 'auto_delivery_time')->count();

        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'pay')->value('id');
            $parent_id = $parent_id ? $parent_id : 942;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'auto_delivery_time',
                'type' => 'text',
                'store_range' => '',
                'sort_order' => 1,
                'value' => 15
            ]);
        }
    }

    /**
     * 新增贡云启用开关
     */
    private function cloudIsOpen()
    {
        $result = ShopConfig::where('code', 'cloud_is_open')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'cloud_is_open',
                    'value' => 0,
                    'type' => 'hidden',
                    'shop_group' => 'cloud_api'
                ]
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 新增支付密码启用开关 (购物流程配置)
     */
    private function usePaypwd()
    {
        $result = ShopConfig::where('code', 'use_paypwd')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'shopping_flow')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'use_paypwd',
                    'value' => 1,
                    'type' => 'select',
                    'store_range' => '1,0',
                    'sort_order' => 1,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 新增商店设置所在区域
     */
    private function add_shop_district()
    {
        $result = ShopConfig::where('code', 'shop_district')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'shop_info')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 1;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'shop_district',
                    'value' => '',
                    'type' => 'manual',
                    'store_range' => '',
                    'sort_order' => 0,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);

            // 修改选择地区配置排序
            $where = [
                'shop_name',
                'shop_title',
                'shop_desc',
                'shop_keywords',
                'shop_country',
                'shop_province',
                'shop_city'
            ];
            ShopConfig::whereIn('code', $where)->update(['sort_order' => 0]);
        }
    }

    /**
     * 清除缓存
     *
     * @throws Exception
     */
    protected function clearCache()
    {
        cache()->forget('shop_config');
    }

    /**
     * 跨境配置
     */
    protected function crossBorder()
    {
        $result = ShopConfig::where('code', 'limited_amount')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'shop_info')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'limited_amount',
                    'value' => '1000',
                    'type' => 'text',
                    'sort_order' => '1'
                ], [
                    'parent_id' => $parent_id,
                    'code' => 'duty_free',
                    'value' => '0',
                    'type' => 'hidden',
                    'sort_order' => '1'
                ],
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 跨境订单提交文章ID
     */
    private function CrossBorderArticleId()
    {
        /* 跨境订单提交文章ID */
        $count = ShopConfig::where('code', 'cross_border_article_id')->count();

        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'shopping_flow')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'cross_border_article_id',
                'value' => '0',
                'type' => 'text'
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 确认收货发短信开关
     */
    private function AffirmReceivedSmsSwitch()
    {
        $parent_id = ShopConfig::where('code', 'sms')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;

        /* 确认收货发短信开关 */
        $count = ShopConfig::where('code', 'sms_order_received')->count();

        if ($count <= 0) {

            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'sms_order_received',
                'type' => 'select',
                'store_range' => '1,0',
                'value' => '0',
                'sort_order' => '13',
                'shop_group' => 'sms'
            ];
            ShopConfig::insert($rows);
        }

        /* 商家确认收货发短信开关 */
        $count = ShopConfig::where('code', 'sms_shop_order_received')->count();

        if ($count <= 0) {
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'sms_shop_order_received',
                'type' => 'select',
                'store_range' => '1,0',
                'value' => '0',
                'sort_order' => '13',
                'shop_group' => 'sms'
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 我要开店
     */
    private function PcLoginRight()
    {
        $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;

        $count = ShopConfig::where('code', 'login_right')->count();

        if ($count <= 0) {
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'login_right',
                'type' => 'text',
                'value' => '我要开店',
                'sort_order' => '1'
            ];
            ShopConfig::insert($rows);
        }

        $count = ShopConfig::where('code', 'login_right_link')->count();

        if ($count <= 0) {
            // 默认数据
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'login_right_link',
                'type' => 'text',
                'value' => 'merchants.php',
                'sort_order' => '1'
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * v1.4.4 隐藏旧短信配置
     */
    private function hiddenSms()
    {
        $code_arr = [
            // 互亿
            'sms_ecmoban_user',
            'sms_ecmoban_password',
            // 阿里大于
            'ali_appkey',
            'ali_secretkey',
            // 阿里云
            'access_key_id',
            'access_key_secret',
            // 模板堂
            'dsc_appkey',
            'dsc_appsecret',
            // 华为云
            'huawei_sms_key',
            'huawei_sms_secret',
            // 创蓝
            'chuanglan_account',
            'chuanglan_password',
            'chuanglan_api_url',
            'chuanglan_signa',
        ];
        ShopConfig::where('type', '<>', 'hidden')->where(function ($query) use ($code_arr) {
            $query->whereIn('code', $code_arr)->orWhere('code', 'sms_type');
        })->update(['type' => 'hidden']);
    }

    /**
     * 1.5.3 新增支付手续费启用开关 (购物流程配置)
     */
    private function usePayfee()
    {
        $result = ShopConfig::where('code', 'use_pay_fee')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'shopping_flow')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'use_pay_fee',
                    'value' => 0,
                    'type' => 'select',
                    'store_range' => '1,0',
                    'sort_order' => 2,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 增加支付模块分组
     */
    protected function add_pay_group()
    {
        $parent_id = DB::table('shop_config')->where('code', 'pay')->where('type', 'group')->value('id');
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'pay',
                'value' => 0,
                'type' => 'group',
                'store_range' => '',
                'sort_order' => 2,
                'shop_group' => ''
            ];
            $parent_id = ShopConfig::insertGetId($rows);
        }

        // 转移至支付模块下
        $parent_id = !empty($parent_id) ? $parent_id : 0;
        if ($parent_id > 0) {
            $pay_code = [
                'use_integral',
                'use_pay_fee',
                'use_bonus',
                'use_surplus',
                'use_value_card',
                'use_coupons',
                'use_paypwd',
                'pay_effective_time'
            ];
            ShopConfig::whereIn('code', $pay_code)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
        }
    }

    /**
     * @throws Exception
     */
    private function businessDispose()
    {
        $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');

        if (CROSS_BORDER === true) { // 跨境多商户
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'cross_border')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'cross_border')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'cross_border',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_DRP)) { // 微分销模块
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_drp')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_drp')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_drp',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_WECHAT)) { // 微商城目录
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_wechat')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_wechat')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_wechat',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_TEAM)) { // 拼团目录
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_team')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_team')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_team',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_BARGAIN)) { // 砍价目录
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_bargain')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_bargain')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_bargain',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_KEFU)) { // 客服目录
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_kefu')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_kefu')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_kefu',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_APP)) { // APP目录
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_app')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_app')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_app',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(MOBILE_GROUPBUY)) { // 社区团购
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'mobile_groupbuy')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'mobile_groupbuy')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'mobile_groupbuy',
                'type' => 'hidden',
                'value' => $value
            ]);
        }

        if (file_exists(SUPPLIERS)) { // 供应链后台目录
            $value = 1;
        } else {
            $value = 0;
        }

        $count = ShopConfig::where('code', 'suppliers')->count();

        if ($count > 0) {
            ShopConfig::where('code', 'suppliers')->update([
                'value' => $value
            ]);
        } else {
            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'suppliers',
                'type' => 'hidden',
                'value' => $value
            ]);
        }
    }

    /**
     * 增加客服LOGO设置
     */
    protected function add_kefu_login_log_group()
    {
        $parent_id = ShopConfig::where('code', 'basic_logo')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;
        if ($parent_id > 0) {
            //是否显示版权
            $result = ShopConfig::where('code', 'kefu_login_log')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'kefu_login_log',
                        'value' => '',
                        'type' => 'file',
                        'store_range' => '',
                        'store_dir' => 'chat/images/',
                        'sort_order' => 1,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 增加版权设置分组
     */
    protected function add_copyright_set_group()
    {
        $parent_id = DB::table('shop_config')->where('code', 'copyright_set')->where('type', 'group')->value('id');
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'copyright_set',
                'value' => 0,
                'type' => 'group',
                'store_range' => '',
                'sort_order' => 2,
                'shop_group' => ''
            ];
            $parent_id = DB::table('shop_config')->insertGetId($rows);
        }

        if ($parent_id > 0) {
            //是否显示版权
            $result = ShopConfig::where('code', 'show_copyright')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'show_copyright',
                        'value' => 1,
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 2,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }

            $result = ShopConfig::where('code', 'copyright_text')->count();
            if (empty($result)) {
                // 版权内容
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'copyright_text',
                        'value' => '由大商创提供技术支持',
                        'type' => 'text',
                        'store_range' => '',
                        'sort_order' => 2,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }

            $result = ShopConfig::where('code', 'copyright_link')->count();
            if (empty($result)) {
                // 跳转网址
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'copyright_link',
                        'value' => config('app.url') ? config('app.url') : 'www.dscmall.cn',
                        'type' => 'text',
                        'store_range' => '',
                        'sort_order' => 2,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 供应链设置
     */
    protected function add_suppliers_set_group()
    {
        $result = ShopConfig::where('code', 'suppliers_set')->where('type', 'group')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => 0,
                    'code' => 'suppliers_set',
                    'value' => 0,
                    'type' => 'group',
                    'store_range' => '',
                    'sort_order' => 2,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }

        $parent_id = ShopConfig::where('code', 'suppliers_set')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;
        if ($parent_id > 0) {
            //PC端模块LOGO
            $result = ShopConfig::where('code', 'suppliers_pc_log')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'suppliers_pc_log',
                        'value' => '',
                        'type' => 'file',
                        'store_range' => '',
                        'store_dir' => 'suppliers/images/',
                        'sort_order' => 1,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }

            //后台登录页LOGO
            $result = ShopConfig::where('code', 'suppliers_login_log')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'suppliers_login_log',
                        'value' => '',
                        'type' => 'file',
                        'store_range' => '',
                        'store_dir' => 'suppliers/images/',
                        'sort_order' => 1,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }

            //后台LOGO
            $result = ShopConfig::where('code', 'suppliers_admin_log')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'suppliers_admin_log',
                        'value' => '',
                        'type' => 'file',
                        'store_range' => '',
                        'store_dir' => 'suppliers/images/',
                        'sort_order' => 1,
                        'shop_group' => ''
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 咨询设置
     */
    protected function add_consult_set()
    {
        $result = ShopConfig::where('code', 'consult_set')->where('type', 'hidden')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => 0,
                    'code' => 'consult_set',
                    'value' => 0,
                    'type' => 'hidden',
                    'store_range' => '',
                    'sort_order' => 1,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }

        // 咨询设置
        $parent_id = ShopConfig::where('code', 'consult_set')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;

        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'consult_set_state')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'consult_set_state',
                        'value' => '1',
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 1,
                        'shop_group' => 'consult_set'
                    ]
                ];
                ShopConfig::insert($rows);
            }

            // 自定义跳转logo
            $result = ShopConfig::where('code', 'custom_jump_logo')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'custom_jump_logo',
                        'store_dir' => 'admin/images/',
                        'value' => '',
                        'type' => 'file',
                        'sort_order' => 2,
                        'shop_group' => 'consult_set'
                    ]
                ];
                ShopConfig::insert($rows);
            }

            // 自定义跳转url
            $result = ShopConfig::where('code', 'custom_jump_url')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'custom_jump_url',
                        'value' => '',
                        'type' => 'text',
                        'sort_order' => 3,
                        'shop_group' => 'consult_set'
                    ]
                ];
                ShopConfig::insert($rows);
            }

            // 客服LOGO
            $result = ShopConfig::where('code', 'kefu_logo')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'kefu_logo',
                        'store_dir' => 'admin/images/',
                        'value' => '',
                        'type' => 'file',
                        'sort_order' => 4,
                        'shop_group' => 'consult_set'
                    ]
                ];
                ShopConfig::insert($rows);
            }

            $count = ShopConfig::where('code', 'kefu_url')->where('shop_group', 'consult_set')->count();
            //客服链接
            if ($count > 0) {
                ShopConfig::where('code', 'kefu_url')->where('shop_group', 'consult_set')->update([
                    'code' => 'consult_kefu_url'
                ]);
            } else {
                $count = ShopConfig::where('code', 'consult_kefu_url')->where('shop_group', 'consult_set')->count();
                if ($count > 0) {
                    ShopConfig::where('code', 'consult_kefu_url')->where('shop_group', 'consult_set')->update([
                        'sort_order' => 6
                    ]);
                }
                if (empty($count)) {
                    $rows = [
                        [
                            'parent_id' => $parent_id,
                            'code' => 'consult_kefu_url',
                            'value' => '',
                            'type' => 'text',
                            'sort_order' => 5,
                            'shop_group' => 'consult_set'
                        ]
                    ];
                    ShopConfig::insert($rows);
                }
            }
        }
    }

    /**
     * app下载地址
     */
    private function appDownloadUrl()
    {
        $result = ShopConfig::where('code', 'wap_app_ios')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'wap_app')->value('parent_id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'wap_app_ios',
                    'value' => '',
                    'type' => 'text',
                ]
            ];
            ShopConfig::insert($rows);
        }

        $result = ShopConfig::where('code', 'wap_app_android')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'wap_app')->value('parent_id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'wap_app_android',
                    'value' => '',
                    'type' => 'text',
                ]
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 更新字段值
     *
     * @throws \Exception
     */
    private function changeShowOrderType()
    {
        $count = ShopConfig::where('code', 'show_order_type')->count();
        if ($count > 0) {
            // 默认数据
            $rows = [
                'store_range' => '0,1'
            ];
            ShopConfig::where('code', 'show_order_type')->update($rows);
        }

        $count = ShopConfig::where('code', 'wap')->where('type', 'hidden')->count();
        if ($count > 0) {
            ShopConfig::where('code', 'wap')->update([
                'type' => 'group'
            ]);
            ShopConfig::where('code', 'wap_logo')->update([
                'type' => 'file'
            ]);
            ShopConfig::where('code', 'wap_index_pro')->update([
                'type' => 'select'
            ]);
        }

        // 更新安装时间
        $install_date = ShopConfig::where('code', 'install_date')->value('value');
        if (empty($install_date)) {
            ShopConfig::where('code', 'install_date')->update([
                'value' => time(),
            ]);
        }
    }

    /**
     * H5首页顶部推荐广告位自定义设置
     *
     * @throws \Exception
     */
    private function add_h5_index_pro()
    {
        ShopConfig::where('code', 'wap')->update([
            'type' => 'group'
        ]);

        ShopConfig::where('code', 'show_order_type')->update([
            'value' => 0
        ]);

        ShopConfig::where('code', 'wap_index_pro')->update([
            'type' => 'select'
        ]);

        $parent_id = ShopConfig::where('code', 'wap')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'h5_index_pro_image')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'h5_index_pro_image',
                        'store_dir' => 'admin/images/',
                        'value' => '',
                        'type' => 'file',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }

            $result = ShopConfig::where('code', 'h5_index_pro_title')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'h5_index_pro_title',
                        'value' => '大商创多用户商城系统APP',
                        'type' => 'text',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }

            $result = ShopConfig::where('code', 'h5_index_pro_small_title')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'h5_index_pro_small_title',
                        'value' => '拥有更加流畅丝滑的购物体验',
                        'type' => 'text',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }

        }

        $result = ShopConfig::where('code', 'use_lbs')->where('type', 'select')->count();
        if ($result > 0) {
            ShopConfig::where('code', 'use_lbs')->update([
                'type' => 'hidden'
            ]);
        }

        $result = ShopConfig::where('code', 'wap_app')->where('type', 'text')->count();
        if ($result > 0) {
            ShopConfig::where('code', 'wap_app')->update([
                'type' => 'hidden'
            ]);
        }

        ShopConfig::where('code', 'user_helpart')->update([
            'type' => 'hidden'
        ]);

        ShopConfig::where('code', 'cart_confirm')->update([
            'type' => 'hidden'
        ]);

        ShopConfig::where('code', 'cron_method')->update([
            'type' => 'hidden'
        ]);
    }

    /**
     * 价格样式选择项
     *
     * @throws \Exception
     */
    private function add_price_style_choose()
    {
        $parent_id = ShopConfig::where('code', 'display')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'price_format')->where('shop_group', 'goods')->count();
            if ($result > 0) {
                // 商品金额输入
                ShopConfig::where('code', 'price_format')->update([
                    'parent_id' => $parent_id,
                    'value' => 0,
                    'store_range' => '0,2,3',  // 0保留2位小数, 2保留1位小数 , 3不保留小数
                    'sort_order' => 2,
                    'shop_group' => ''
                ]);
            }
            // 货币格式
            $result = ShopConfig::where('code', 'currency_format')->count();
            if ($result > 0) {
                ShopConfig::where('code', 'currency_format')->update([
                    'value' => '¥',
                    'sort_order' => 2
                ]);
            }

            $result = ShopConfig::where('code', 'is_shop_currency_format')->count();
            if (!empty($result)) {
                $currency_format = ShopConfig::where('code', 'is_show_currency_format')->count();
                if (empty($currency_format)) {
                    ShopConfig::where('code', 'is_shop_currency_format')->update([
                        'code' => 'is_show_currency_format'
                    ]);
                }
            } else {
                // 是否显示货币格式
                $result = ShopConfig::where('code', 'is_show_currency_format')->count();
                if (empty($result)) {
                    $rows = [
                        [
                            'parent_id' => $parent_id,
                            'code' => 'is_show_currency_format',
                            'value' => '1',
                            'type' => 'select',
                            'store_range' => '1,0',
                            'sort_order' => 1
                        ]
                    ];
                    ShopConfig::insert($rows);
                }
            }

            // 金额显示样式
            $result = ShopConfig::where('code', 'price_style')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'price_style',
                        'value' => '1',
                        'type' => 'select',
                        'store_range' => '1,2,3,4',
                        'sort_order' => 2
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 起始页客户服务显示
     *
     * @throws \Exception
     */
    private function add_start_customer_service()
    {
        $parent_id = ShopConfig::where('code', 'display')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'enable_customer_service')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'enable_customer_service',
                        'value' => '1',
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 2
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 会员余额提现设置项
     *
     * @throws \Exception
     */
    private function add_balance_withdrawal()
    {
        $parent_id = ShopConfig::where('code', 'basic')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'user_balance_withdrawal')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'user_balance_withdrawal',
                        'value' => '1',
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 新增自定义客服链接
     *
     * @throws \Exception
     */
    private function add_start_service_url()
    {
        $parent_id = ShopConfig::where('code', 'shop_info')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'service_url')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'service_url',
                        'value' => '',
                        'type' => 'text',
                        'store_range' => '',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 增加退款模块分组
     */
    protected static function add_return_group()
    {
        $parent_id = DB::table('shop_config')->where('code', 'return')->where('type', 'group')->value('id');
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'return',
                'value' => 0,
                'type' => 'group',
                'store_range' => '',
                'sort_order' => 50,
                'shop_group' => ''
            ];
            $parent_id = DB::table('shop_config')->insertGetId($rows);
        }

        if ($parent_id > 0) {
            // 转移至退款模块

            // 发货日期起可退换货时间
            DB::table('shop_config')->where('code', 'sign')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'return', 'sort_order' => 1]);
            //  被拒退换货激活设置
            DB::table('shop_config')->where('code', 'activation_number_type')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'return', 'sort_order' => 2]);

            // 商家退款审批
            $result = DB::table('shop_config')->where('code', 'seller_return_check')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'seller_return_check',
                        'value' => 1,
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 3,
                        'shop_group' => 'return'
                    ]
                ];
                DB::table('shop_config')->insert($rows);
            }

            // 商家退款 优先退款方式
            $result = DB::table('shop_config')->where('code', 'precedence_return_type')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'precedence_return_type',
                        'value' => 6,
                        'type' => 'options',
                        'store_range' => '1,2,6',
                        'sort_order' => 4,
                        'shop_group' => 'return'
                    ]
                ];
                DB::table('shop_config')->insert($rows);
            }

        }

    }

    /**
     * 商品相册图片是否保留原名称
     *
     * @throws \Exception
     */
    private function add_upload_use_original_name()
    {
        $parent_id = ShopConfig::where('code', 'goods_picture')->where('shop_group', 'goods')->value('id'); // 商品图片设置

        if ($parent_id > 0) {

            $result = ShopConfig::where('code', 'upload_use_original_name')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'upload_use_original_name',
                        'value' => 0,
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 1,
                        'shop_group' => 'goods',
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 增加商品评价模块分组
     */
    protected static function add_goods_comment_group()
    {
        $parent_id = DB::table('shop_config')->where('code', 'goods_comment')->where('type', 'group')->where('shop_group', 'goods')->value('id');
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'goods_comment',
                'value' => 0,
                'type' => 'group',
                'store_range' => '',
                'sort_order' => 2,
                'shop_group' => 'goods'
            ];
            $parent_id = DB::table('shop_config')->insertGetId($rows);
        }

        $parent_id = !empty($parent_id) ? $parent_id : 0;
        if ($parent_id > 0) {
            // 转移至商品评价模块下
            DB::table('shop_config')->where('code', 'shop_can_comment')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'goods', 'sort_order' => 1]);
            DB::table('shop_config')->where('code', 'comment_check')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'goods', 'sort_order' => 2]);

            // 自动评价 开关
            $result = DB::table('shop_config')->where('code', 'auto_evaluate')->count();
            if (empty($result)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'auto_evaluate',
                    'value' => '1',
                    'type' => 'select',
                    'store_range' => '1,0',
                    'sort_order' => 2,
                    'shop_group' => 'goods'
                ];
                DB::table('shop_config')->insert($rows);
            }

            // 自动评价 时间
            $result = DB::table('shop_config')->where('code', 'auto_evaluate_time')->count();
            if (empty($result)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'auto_evaluate_time',
                    'value' => '60',
                    'type' => 'text',
                    'store_range' => '',
                    'sort_order' => 3,
                    'shop_group' => 'goods'
                ];
                DB::table('shop_config')->insert($rows);
            }

            // 自动评价 内容
            $result = DB::table('shop_config')->where('code', 'auto_evaluate_content')->count();
            if (empty($result)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'auto_evaluate_content',
                    'value' => '此用户没有填写评价',
                    'type' => 'textarea',
                    'store_range' => '',
                    'sort_order' => 4,
                    'shop_group' => 'goods'
                ];
                DB::table('shop_config')->insert($rows);
            }

            // 追加评价 开关
            $result = DB::table('shop_config')->where('code', 'add_evaluate')->count();
            if (empty($result)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'add_evaluate',
                    'value' => '1',
                    'type' => 'select',
                    'store_range' => '1,0',
                    'sort_order' => 5,
                    'shop_group' => 'goods'
                ];
                DB::table('shop_config')->insert($rows);
            }

            // 可追加评价时间
            $result = DB::table('shop_config')->where('code', 'add_evaluate_time')->count();
            if (empty($result)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'add_evaluate_time',
                    'value' => '180',
                    'type' => 'text',
                    'store_range' => '',
                    'sort_order' => 6,
                    'shop_group' => 'goods'
                ];
                DB::table('shop_config')->insert($rows);
            }

        }

    }
}
