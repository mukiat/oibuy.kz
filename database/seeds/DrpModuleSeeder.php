<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 分销模块数据填充
 * Class DrpModuleSeeder
 */
class DrpModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->drpConfig();
        $this->article();
        $this->wechatTemplate();
        $this->adminAction();

        $this->upgradeTov1_4();
        $this->upgradeTov1_4_1();
        $this->upgradeTov1_4_2();
        $this->upgradeTov1_4_3();
        $this->upgradeTov1_4_4();

        $this->upgradeTov1_4_6();

        $this->admin_action_upgrade();

        $this->drp_config_upgrade();

        // 增加自定义文字设置
        $this->add_custom_text();

        $this->drp_config_hidden();
    }

    private function drpConfig()
    {
        $result = DB::table('drp_config')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'code' => 'notice',
                    'type' => 'textarea',
                    'store_range' => '',
                    'value' => "亲，您的佣金由三部分组成：\r\n1.我的下线购买分销商品，我所获得的佣金（即本一级分销佣金）\r\n2.下级分店的下线会员购买分销商品，我所获得的佣金（即二级分销佣金）\r\n3.下级分店发展的分店的下线会员购买分销商品，我所获得的佣金（即三级分店佣金）。",
                    'name' => '温馨提示',
                    'warning' => '申请成为分销商时，提示用户需要注意的信息',
                ],
                [
                    'code' => 'novice',
                    'type' => 'textarea',
                    'store_range' => '',
                    'value' => "1、开微店收入来源之一：您已成功注册微店，已经取得整个商城的商品销售权，只要您的下线会员购买分销商品，即可获得“一级分销佣金”。\r\n2、开微店收入来源之二：邀请您的朋友注册微店，他就会成为你的下级分销商，他的下线会员购买分销商品，您即可获得“二级分销佣金”。\r\n3、开微店收入来源之三：您的下级分销商邀请他的朋友注册微店后，他朋友的下线会员购买分销商品，您即可获得“三级分销佣金”。",
                    'name' => '新手必读',
                    'warning' => '分销商申请成功后，用户要注意的事项',
                ],
                [
                    'code' => 'withdraw',
                    'type' => 'textarea',
                    'store_range' => '',
                    'value' => '可提现金额为交易成功后7天且为提现范围内的金额',
                    'name' => '提现提示',
                    'warning' => '申请提现时，少于该值将无法提现',
                ],
                [
                    'code' => 'draw_money',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '10',
                    'name' => '提现金额',
                    'warning' => '申请提现时，少于该值将无法提现',
                ],
                [
                    'code' => 'issend',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '1',
                    'name' => '消息推送',
                    'warning' => '申请店铺成功时,推送消息到微信',
                ],
                [
                    'code' => 'isbuy',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '1',
                    'name' => '购买成为分销商',
                    'warning' => '是否开启购买成为分销商,默认申请成为分销商',
                ],
                [
                    'code' => 'buy_money',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '100',
                    'name' => '购买金额',
                    'warning' => '购买金额达到该数值,才能成为分销商',
                ],
                [
                    'code' => 'isdrp',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '1',
                    'name' => '商品分销模式',
                    'warning' => '是否开启分销模式,默认分销模式。控制商品详情页‘我要分销’按钮',
                ],
                [
                    'code' => 'ischeck',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '1',
                    'name' => '分销商审核',
                    'warning' => '成为分销商,是否需要审核',
                ],
                [
                    'code' => 'drp_affiliate',
                    'type' => '',
                    'store_range' => '',
                    'value' => 'a:3:{s:6:"config";a:5:{s:6:"expire";i:0;s:11:"expire_unit";s:3:"day";s:3:"day";s:1:"8";s:15:"level_point_all";s:2:"8%";s:15:"level_money_all";s:2:"1%";}s:4:"item";a:3:{i:0;a:2:{s:11:"level_point";s:3:"60%";s:11:"level_money";s:3:"60%";}i:1;a:2:{s:11:"level_point";s:3:"30%";s:11:"level_money";s:3:"30%";}i:2;a:2:{s:11:"level_point";s:3:"10%";s:11:"level_money";s:3:"10%";}}s:2:"on";i:1;}',
                    'name' => '三级分销比例',
                    'warning' => '',
                ],
                [
                    'code' => 'custom_distributor',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '代言人',
                    'name' => '自定义“分销商”名称',
                    'warning' => '替换设定的分销商名称',
                ],
                [
                    'code' => 'custom_distribution',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '代言',
                    'name' => '自定义“分销”名称',
                    'warning' => '替换设定的分销名称',
                ],
                [
                    'code' => 'commission',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '0',
                    'name' => '是否显示佣金比例',
                    'warning' => '控制店铺页面是否显示佣金比例',
                ],
                [
                    'code' => 'is_buy_money',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '0',
                    'name' => '累计消费金额',
                    'warning' => '是否开启购物累计消费金额满足设置才能开店',
                ],
                [
                    'code' => 'buy',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '200',
                    'name' => '设置累计消费金额',
                    'warning' => '设置会员累计消费金额',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        $result = DB::table('drp_config')->where('code', 'count_commission')->count();
        if (empty($result)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'count_commission',
                    'type' => 'radio',
                    'store_range' => '0,1,2',
                    'value' => '2',
                    'name' => '按时间统计分销商佣金排行',
                    'warning' => '按时间统计分销商佣金进行分销商排行，可以按 周，月，年 排行',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        $result_register = DB::table('drp_config')->where('code', 'register')->count();
        if (empty($result_register)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'register',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '0',
                    'name' => '开启分销商店铺自动注册',
                    'warning' => '开启分销商店铺自动注册后，授权登录，关注商城会自动创建一个分销商店铺',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        $result_isdistributionr = DB::table('drp_config')->where('code', 'isdistribution')->count();
        if (empty($result_isdistributionr)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'isdistribution',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => '1',
                    'name' => '分销分成模式',
                    'warning' => '开启分成模式从上级分销商开始分成，否则从当前下单分销商分成',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        $articlecatid = DB::table('drp_config')->where('code', 'articlecatid')->count();
        if (empty($articlecatid)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'articlecatid',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '1000',
                    'name' => '指定分销文章分类',
                    'warning' => '分销店铺中心新手必看指定文章分类下的文章',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }


        $agreement_id = DB::table('drp_config')->where('code', 'agreement_id')->count();
        if (empty($agreement_id)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'agreement_id',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '6',
                    'name' => '指定高级用户协议文章',
                    'warning' => '大商创高级用户正式协议',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }
    }

    private function article()
    {
        $result = DB::table('article_cat')->where('cat_id', 1000)->count();
        if (empty($result)) {
            $cats = [
                [
                    'cat_id' => 1000,
                    'cat_name' => '微分销',
                    'cat_type' => 1,
                    'keywords' => '分销',
                    'show_in_nav' => 1,
                ]
            ];
            DB::table('article_cat')->insert($cats);

            $articles = [
                [
                    'cat_id' => 1000,
                    'title' => '什么是微分销？',
                    'content' => '微分销是一体化微信分销交易平台，基于朋友圈传播，帮助企业打造“企业微商城+粉丝微店+员工微店”的多层级微信营销模式，轻松带领千万微信用户一起为您的商品进行宣传及销售。',
                    'keywords' => '分销',
                    'is_open' => 1,
                    'add_time' => '1467962482'
                ],
                [
                    'cat_id' => 1000,
                    'title' => '如何申请成为分销商？',
                    'content' => '关注微信公众号，进入会员中心点击我的微店。申请后，等待管理员审核通过，即可拥有自己的微店，坐等佣金收入分成！',
                    'keywords' => '分销',
                    'is_open' => 1,
                    'add_time' => '1467962482'
                ]
            ];
            DB::table('article')->insert($articles);
        }

        // 微分销文章分类 不能被删除
        DB::table('article_cat')->where('cat_id', 1000)->update(['cat_type' => 2]);
    }

    private function wechatTemplate()
    {
        $result = DB::table('wechat_template')->where('code', 'OPENTM207126233')->first();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'wechat_id' => 1,
                    'code' => 'OPENTM207126233',
                    'template' => '{{first.DATA}}\r\n分销商名称：{{keyword1.DATA}}\r\n分销商电话：{{keyword2.DATA}}\r\n申请时间：{{keyword3.DATA}}\r\n{{remark.DATA}}',
                    'title' => '分销商申请成功'
                ]
            ];
            DB::table('wechat_template')->insert($rows);
        }

        $result_2 = DB::table('wechat_template')->where('code', 'OPENTM202967310')->first();
        if (empty($result_2)) {
            // 插入新数据
            $rows = [
                [
                    'wechat_id' => 1,
                    'code' => 'OPENTM202967310',
                    'template' => '{{first.DATA}}会员编号：{{keyword1.DATA}}加入时间：{{keyword2.DATA}}{{remark.DATA}}',
                    'title' => '新会员加入通知'
                ]
            ];
            DB::table('wechat_template')->insert($rows);
        }
        $result_3 = DB::table('wechat_template')->where('code', 'OPENTM220197216')->first();
        if (!empty($result_3)) {
            // 删除旧数据
            DB::table('wechat_template')->where('code', 'OPENTM220197216')->delete();
        }
    }

    private function adminAction()
    {
        $result = DB::table('admin_action')->where('action_code', 'drp')->count();
        if (empty($result)) {
            // 默认数据
            $row = [
                'parent_id' => 0,
                'action_code' => 'drp',
                'seller_show' => 0
            ];
            $action_id = DB::table('admin_action')->insertGetId($row);

            // 默认数据
            $rows = [
                [
                    'parent_id' => $action_id,
                    'action_code' => 'drp_config',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'drp_shop',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'drp_list',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'drp_order_list',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'drp_set_config',
                    'seller_show' => 0
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }
    }

    /**
     * 升级 v1.4.0
     */
    protected function upgradeTov1_4()
    {
        /* 更新分销商配置分组 start */

        // 更新排序
        DB::table('drp_config')->update(['sort_order' => '50']);

        // 隐藏 原购买成为分销商、累计消费金额、分销商审核 开关配置
        DB::table('drp_config')->whereIn('code', ['isbuy', 'is_buy_money', 'buy_money', 'buy'])->update(['type' => 'hidden']);

        /* 新增分销广告 start */
        $this->addDrpTouchAdPosition();
        /* 新增分销广告 end */
    }

    /**
     * 分销广告位
     */
    private function addDrpTouchAdPosition()
    {
        $result = DB::table('touch_ad_position')->where('ad_type', 'drp')->count();
        if (empty($result)) {
            // 默认数据
            $row = [
                'position_name' => '分销-banner广告位',
                'ad_width' => '360',
                'ad_height' => '168',
                'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}' . "\n" . '',
                'theme' => 'ecmoban_dsc2017',
                'tc_type' => 'banner',
                'ad_type' => 'drp'
            ];
            $position_id = DB::table('touch_ad_position')->insertGetId($row);
            if ($position_id > 0) {
                $this->addTouchAd($position_id);
            }
        }
    }

    private function addTouchAd($position_id = 0)
    {
        $result = DB::table('touch_ad')->where('position_id', $position_id)->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'position_id' => $position_id,
                    'media_type' => '0',
                    'ad_name' => '分销广告r-01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1509663779787829146.jpg',
                    'start_time' => '1569219147',
                    'end_time' => '1609434061',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ]
            ];
            DB::table('touch_ad')->insert($rows);
        }
    }

    /**
     * 升级 v1.4.1
     */
    protected function upgradeTov1_4_1()
    {
        // 更新排序
        DB::table('drp_config')->update(['sort_order' => '50']);

        // 还原1.4版本隐藏的 分销商审核字段
        DB::table('drp_config')->whereIn('code', ['ischeck'])->update(['type' => 'radio']);
        // 隐藏原 分销比例设置
        DB::table('drp_config')->whereIn('code', ['drp_affiliate'])->update(['type' => 'hidden']);


        // 分销配置重新分组： 空 基本配置、 show 显示配置、 scale 结算配置、qrcode 分享配置、message 消息配置

        DB::table('drp_config')->whereIn('code', ['register', 'ischeck'])->update(['group' => '']);

        DB::table('drp_config')->whereIn('code', ['notice', 'novice', 'isdrp', 'custom_distributor', 'custom_distribution', 'commission', 'agreement_id', 'count_commission', 'articlecatid'])->update(['group' => 'show']);

        DB::table('drp_config')->whereIn('code', ['withdraw', 'draw_money'])->update(['group' => 'scale']);

        DB::table('drp_config')->whereIn('code', ['issend'])->update(['group' => 'message']);


        // 新增配置
        $drp_config = DB::table('drp_config')->where('code', 'drp_affiliate')->value('value');
        if ($drp_config) {
            $drp_config = unserialize($drp_config);
        }
        // 开启VIP分销
        $drp_affiliate_on = DB::table('drp_config')->where('code', 'drp_affiliate_on')->count();
        if (empty($drp_affiliate_on)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'drp_affiliate_on',
                    'type' => 'radio',
                    'store_range' => '0,1',
                    'value' => $drp_config['on'] ?? 1,
                    'name' => '开启VIP分销',
                    'warning' => '如果关闭则不会计算分销佣金',
                    'sort_order' => '0',
                    'group' => '',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        // 佣金结算时间
        $settlement_time = DB::table('drp_config')->where('code', 'settlement_time')->count();
        if (empty($settlement_time)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'settlement_time',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => $drp_config['config']['day'] ?? 1,
                    'name' => '佣金分成时间',
                    'warning' => '设置会员确认收货后，X天后，生成分成订单。单位：天，默认1天',
                    'sort_order' => '50',
                    'group' => 'scale',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        // 佣金结算时机
        $settlement_type = DB::table('drp_config')->where('code', 'settlement_type')->count();
        if (empty($settlement_type)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'settlement_type',
                    'type' => 'radio',
                    'store_range' => '0,1', // 0 禁用即手动 、1 启用即自动
                    'value' => '0',
                    'name' => '是否自动分佣',
                    'warning' => '设置禁用即手动，则订单确认收货后，过了结算分成时间，生成的分成订单需手动点击分成;<br/>设置启用即自动，则订单确认收货后，过了结算分成时间，系统将会自动分成',
                    'sort_order' => '51',
                    'group' => 'scale',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        //会员分销权益卡管理
        $count = DB::table('admin_action')->where('action_code', 'drpcard_manage')->count();
        if ($count <= 0) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'drpcard_manage',
                'seller_show' => '0'
            ]);
        }

        DB::table('drp_config')->where('code', ['drp_affiliate_on'])->update(['sort_order' => '0']);
    }

    /**
     * 升级 v1.4.2
     */
    protected function upgradeTov1_4_2()
    {
        // 新增配置 注册分成锁定模式
        $drp_affiliate_mode = DB::table('drp_config')->where('code', 'drp_affiliate_mode')->count();
        if (empty($drp_affiliate_mode)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'drp_affiliate_mode',
                    'type' => 'radio',
                    'store_range' => '0,1', // 0 禁用 1 启用
                    'value' => 1,
                    'name' => '注册锁定模式',
                    'warning' => '注册锁定模式, 默认启用 即注册+分享必须同一人',
                    'sort_order' => '50',
                    'group' => '',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }
    }

    /**
     * 升级 v1.4.3
     */
    protected function upgradeTov1_4_3()
    {
        // 新增配置 客户关系有效期
        $children_expiry_days = DB::table('drp_config')->where('code', 'children_expiry_days')->count();
        if (empty($children_expiry_days)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'children_expiry_days',
                    'type' => 'text',
                    'store_range' => '', // 0永久有效，数值固定天数有效
                    'value' => 7,
                    'name' => '客户关系有效期',
                    'warning' => "有效期天数，0永久有效，到期后关系解绑；若会员在有效期内，每次访问带参数的链接或重新扫码，自动更新有效期为x天（不累加）。",
                    'sort_order' => '50',
                    'group' => '',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }
    }


    /**
     * 升级 v1.4.4
     */
    protected function upgradeTov1_4_4()
    {
        // 还原分销内购模式
        DB::table('drp_config')->where('code', ['isdistribution'])->update(['type' => 'radio', 'store_range' => '0,1,2', 'warning' => '']);
        DB::table('drp_config')->where('code', ['drp_affiliate_mode'])->update(['warning' => '', 'name' => '分销商业绩归属']);

        // 更新旧分销商申请时间
        DB::table('drp_shop')->where('apply_time', '')->where('create_time', '<>', '')
            ->chunkById(1000, function ($users) {
                foreach ($users as $user) {
                    DB::table('drp_shop')
                        ->where('id', $user->id)
                        ->update(['apply_time' => $user->create_time]);
                }
            });
    }

    /**
     * 升级 v1.4.6
     */
    protected function upgradeTov1_4_6()
    {
        // 更新分销权益卡购买指定商品 is_show
        DB::table('goods')->where('membership_card_id', '>', 0)->where('is_show', 0)->orderBy('goods_id')
            ->chunk(1000, function ($goods) {
                foreach ($goods as $val) {
                    DB::table('goods')
                        ->where('goods_id', $val->goods_id)
                        ->update(['is_show' => 1, 'is_on_sale' => 0]);
                }
            });


        // 同步更新默认分成时间至少1天
        DB::table('drp_config')->where('code', 'settlement_time')->where('value', '<=', 0)->update(['value' => 1]);
    }

    /**
     * 升级 增加分销权限
     */
    protected function admin_action_upgrade()
    {
        $count = DB::table('admin_action')->where('action_code', 'drp')->count();
        if (!empty($count)) {
            $action_code = [
                'drp',
                'drp_config',
                'drp_shop',
                'drp_list',
                'drp_order_list',
                'drp_set_config'
            ];
            DB::table('admin_action')->whereIn('action_code', $action_code)->update(['seller_show' => '0']);
        }

        // v1.5 增加 微店权限
        $result = DB::table('admin_action')->where('action_code', 'drp_store')->count();
        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');
            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_store',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // v1.5 增加 微店审核权限
        $result = DB::table('admin_action')->where('action_code', 'drp_store_check')->count();
        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');
            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_store_check',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // drp_set_config 无用
        DB::table('admin_action')->where('action_code', 'drp_set_config')->delete();

        // v1.5 增加 佣金账单权限控制
        $drp_commission_bills = DB::table('admin_action')->where('action_code', 'drp_commission_bills')->count();
        if (empty($drp_commission_bills)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');
            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_commission_bills',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // v2.0.0 增加 佣金提现权限
        $result = DB::table('admin_action')->where('action_code', 'drp_transfer_log')->count();
        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');
            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_transfer_log',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // 分销统计权限
        $result = DB::table('admin_action')->where('action_code', 'drp_count')->count();
        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');
            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_count',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

    }

    /**
     * 升级 增加分销配置
     */
    protected function drp_config_upgrade()
    {
        // v2.0.0 是否开启提现 配置
        $withdraw_switch = DB::table('drp_config')->where('code', 'withdraw_switch')->count();
        if (empty($withdraw_switch)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'withdraw_switch',
                    'type' => 'radio',
                    'store_range' => '0,1', // 0 关闭、1 开启
                    'value' => '0',
                    'name' => '开启佣金提现',
                    'warning' => '是否开启佣金线上提现，例如微信企业付款提现',
                    'sort_order' => '49',
                    'group' => 'scale',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }


        DB::table('drp_config')->where('code', 'qrcode')->update(['group' => 'qrcode']);

        // v2.3.0 增加二维码跳转设置
        $qrcode_link_type = DB::table('drp_config')->where('code', 'qrcode_link_type')->count();
        if (empty($qrcode_link_type)) {
            // 插入新数据
            $rows = [
                [
                    'code' => 'qrcode_link_type',
                    'type' => 'radio',
                    'store_range' => '0,1,2', // 0 微信公众号、1 购买权益卡、2 分销店铺
                    'value' => '1',
                    'name' => '二维码跳转设置',
                    'warning' => '二维码跳转设置，可设置微信公众号、高级会员开通页、分销店铺页',
                    'sort_order' => '1',
                    'group' => 'qrcode',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

    }

    /**
     * 新增自定义文本 配置
     */
    protected function add_custom_text()
    {
        // 分销中心页面
        $page_drp_info = DB::table('drp_config')->where('code', 'page_drp_info')->count();
        if (empty($page_drp_info)) {
            $rows = [
                [
                    'code' => 'page_drp_info',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '',
                    'name' => '分销中心',
                    'warning' => '分销中心页面自定义文本',
                    'sort_order' => '50',
                    'group' => 'custom',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        // 我的微店页面
        $page_drp_store = DB::table('drp_config')->where('code', 'page_drp_store')->count();
        if (empty($page_drp_store)) {
            $rows = [
                [
                    'code' => 'page_drp_store',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '',
                    'name' => '我的微店',
                    'warning' => '我的微店页面自定义文本',
                    'sort_order' => '50',
                    'group' => 'custom',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        // 我的团队页面
        $page_drp_team = DB::table('drp_config')->where('code', 'page_drp_team')->count();
        if (empty($page_drp_team)) {
            $rows = [
                [
                    'code' => 'page_drp_team',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '',
                    'name' => '我的团队',
                    'warning' => '我的团队页面自定义文本',
                    'sort_order' => '50',
                    'group' => 'custom',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        // 分销奖励页面
        $page_drp_order = DB::table('drp_config')->where('code', 'page_drp_order')->count();
        if (empty($page_drp_order)) {
            $rows = [
                [
                    'code' => 'page_drp_order',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '',
                    'name' => '分销奖励',
                    'warning' => '分销奖励页面自定义文本',
                    'sort_order' => '50',
                    'group' => 'custom',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }

        // 会员中心页面 高级权益卡模块
        $page_user_drp = DB::table('drp_config')->where('code', 'page_user_drp')->count();
        if (empty($page_user_drp)) {
            $rows = [
                [
                    'code' => 'page_user_drp',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '',
                    'name' => '会员中心',
                    'warning' => '会员中心页面高级权益卡模块自定义文本',
                    'sort_order' => '50',
                    'group' => 'custom',
                ]
            ];
            DB::table('drp_config')->insert($rows);
        }
    }

    /**
     * 隐藏原分销无用配置
     */
    protected function drp_config_hidden()
    {
        // 隐藏原分销无用配置 店铺自动开启、
        DB::table('drp_config')->whereIn('code', ['register', 'custom_distributor', 'custom_distribution'])->update(['type' => 'hidden']);
    }
}
