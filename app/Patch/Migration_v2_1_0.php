<?php

namespace App\Patch;

use App\Repositories\Common\CommonRepository;
use App\Models\ShopConfig;
use App\Repositories\Common\TimeRepository;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;


class Migration_v2_1_0
{
    public function run()
    {
        try {

            /* 操作字段 */
            CommonRepository::tableField();

            // 自定义工具栏
            $this->touch_nav();
            // 门店隐私保护协议
            $this->store_privacy_agreement();

            $this->add_h5_index_pro();

            $this->add_price_style_choose();

            $this->seller_commission_bill();
            $this->seller_bill_order();

            $this->shopConfig();

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function seller_commission_bill()
    {
        $table_name = 'seller_commission_bill';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'return_rate_fee')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('return_rate_fee', 10, 2)->default(0)->comment('跨境税费退款金额');
            });
        }
    }

    public function seller_bill_order()
    {
        $table_name = 'seller_bill_order';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'return_rate_fee')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('return_rate_fee', 10, 2)->default(0)->comment('跨境税费退款金额');
            });
        }
    }

    /**
     * 自定义工具栏
     */
    private function touch_nav()
    {
        $tableName = 'touch_nav';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'device')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('device', 20)->default('')->comment('客户端：h5, wxapp, app');
            });
        }

        if (!Schema::hasColumn($tableName, 'parent_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('parent_id')->unsigned()->default(0)->index()->comment('父级导航id，对应本表的id字段');
            });
        }

        if (!Schema::hasColumn($tableName, 'page')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('page', 20)->default('')->comment('页面标识，如user');
            });
        }

        if (Schema::hasColumn($tableName, 'id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->increments('id')->comment('自增ID')->change();
            });
        }
        if (Schema::hasColumn($tableName, 'vieworder')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('vieworder')->unsigned()->default(0)->comment('排序')->change();
            });
        }

        // 增加 vieworder 索引
        if (!DB::table($tableName)->hasIndex('dsc_touch_nav_vieworder_index')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->index('vieworder');
            });
        }
    }


    /**
     * 门店隐私保护协议
     */
    private function store_privacy_agreement()
    {
        // 门店隐私保护协议
        $result = DB::table('article')->where('cat_id', '-3')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'cat_id' => '-3',
                    'title' => '门店隐私保护协议',
                    'content' => '<p>以此声明对本站用户隐私保护的许诺。随着本站服务范围的扩大，会随时更新隐私声明。我们欢迎您随时查看隐私声明。详细隐私政策，您可参考《隐私声明》。</p>
    <p>本网站非常重视对用户隐私权的保护，用户的邮件及手机号等个人资料为用户重要隐私，本站承诺不会将个人资料用作它途；承诺不会在未获得用户许可的情况下擅自将用户的个人资料信息出租或出售给任何第三方，但以下情况除外：</p>
    <p>A、用户同意让第三方共享资料；</p>
    <p>B、用户为享受产品和服务同意公开其个人资料；</p>
    <p>C、本站发现用户违反了本站服务条款或本站其它使用规定。</p>
    <h5>使用说明</h5>
    <p>用户可以通过设定的密码来保护账户和资料安全。用户应当对其密码的保密负全部责任。请不要和他人分享此信息。如果您使用的是公共电脑，请在离开电脑时退出本网站、以保证您的信息不被后来的使用者获取。</p>
    <h5>服务条款说明</h5>
    <p>接受本网站的用户同时受本站用户协议的约束。</p>',
                    'add_time' => TimeRepository::getGmTime(),
                    'article_type' => 0,
                    'is_open' => 1,
                ]
            ];
            DB::table('article')->insert($rows);
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

        $parent_id = ShopConfig::where('code', 'wap')->where('type', 'group')->value('id');
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
     * 更新版本
     *
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.1.0'
        ]);

        $this->clearCache();
    }

    /**
     * @throws Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}
