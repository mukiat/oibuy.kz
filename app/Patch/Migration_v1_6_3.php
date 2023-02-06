<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_6_3
{
    public function run()
    {
        try {
            $this->goods();
            $this->seckill_goods();
            $this->shopConfig();
            $this->appDownloadUrl();
            $this->changeShowOrderType();
            $this->add_copyright_set_group();//版权设置
            $this->add_kefu_login_log_group();//添加客服LOGO			
            if (file_exists(SUPPLIERS)) {
                $this->add_suppliers_set_group();//供应链设置
            }
            $this->addConsultSet(); // 咨询设置

            $this->user_membership_card();
            $this->user_membership_card_rights();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**修改商品红包字段类型
     * @return bool
     */
    public function goods()
    {
        $tableName = 'goods';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (Schema::hasColumn($tableName, 'bonus_type_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('bonus_type_id')->default('')->change();
            });
        }
    }

    public function seckill_goods()
    {

        $tableName = 'seckill_goods';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (!Schema::hasColumn($tableName, 'seckill_volume')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('seckill_volume')->default(0)->comment('秒杀商品销量');
            });
        }
    }

    /**
     * 更新版本
     *
     * @throws \Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.6.3'
        ]);

        $this->clearCache();
    }

    /**
     * @throws \Exception
     */
    private function clearCache()
    {
        cache()->flush();
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
        $result = ShopConfig::where('code', 'copyright_set')->where('type', 'group')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => 0,
                    'code' => 'copyright_set',
                    'value' => 0,
                    'type' => 'group',
                    'store_range' => '',
                    'sort_order' => 2,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }

        $parent_id = ShopConfig::where('code', 'copyright_set')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;
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
    protected function addConsultSet()
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
                        'value' => 'https://www.dscmall.cn/topic/songshouquan.html',
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

        $this->clearCache();
    }

    public function user_membership_card()
    {
        $name = 'user_membership_card';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name', 60)->default('')->comment('权益卡名称');
            $table->tinyInteger('type')->unsigned()->default(1)->comment('权益卡类型： 1 普通权益卡 2 分销权益卡');
            $table->string('description')->default('')->comment('权益卡说明');
            $table->string('background_img')->default('')->comment('权益卡背景图');
            $table->string('background_color')->default('')->comment('权益卡背景颜色');
            $table->text('receive_value')->comment('权益卡领取条件配置,序列化');
            $table->string('expiry_type')->default('forever')->comment('过期时间类型： forever(永久), days(多少天数), timespan(时间间隔)');
            $table->string('expiry_date')->default('')->comment('过期时间');
            $table->integer('enable')->unsigned()->default(0)->comment('权益卡状态：0 关闭 1 开启');
            $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('修改时间');
            $table->integer('sort')->unsigned()->default(50)->comment('排序');
            $table->integer('user_rank_id')->unsigned()->default(0)->index()->comment('用户等级ID，关联user_rank.rank_id');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '权益卡表'");
    }

    public function user_membership_card_rights()
    {
        $name = 'user_membership_card_rights';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('rights_id')->unsigned()->default(0)->index()->comment('权益id,关联 user_membership');
            $table->text('rights_configure')->comment('权益配置,序列化');
            $table->integer('membership_card_id')->unsigned()->default(0)->index()->comment('权益卡id,关联 user_membership_card');
            $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('修改时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '权益卡权益绑定关系表'");
    }
}
