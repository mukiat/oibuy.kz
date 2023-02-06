<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_5_0
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        $this->change_comment_add_comment_id();
    }

    /**
     * 评价表 增加追评父级评价id
     */
    protected function change_comment_add_comment_id()
    {
        $tableName = 'comment';
        if (Schema::hasTable($tableName)) {
            if (!Schema::hasColumn($tableName, 'add_comment_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('add_comment_id')->unsigned()->default(0)->comment('追评父级评价id,关联comment表comment_id');
                });
            }
        }
    }

    private function seed()
    {
        // 增加商品评价模块分组
        $this->add_goods_comment_group();

        $this->delete_merchants_yop();
        $this->add_copy_right();

        /* 显示微信分享商城log */
        ShopConfig::where('code', 'wap_logo')->update([
            'type' => 'file'
        ]);

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.5.0'
        ]);
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

    private function add_copy_right()
    {
        $copyright_img = ShopConfig::where('code', 'copyright_img')->count();
        if (empty($copyright_img)) {
            $parent_id = DB::table('shop_config')->where('code', 'copyright_set')->value('id');
            $rows = [
                'parent_id' => $parent_id,
                'code' => 'copyright_img',
                'type' => 'file',
                'store_range' => '',
                'store_dir' => 'images/common/',
                'value' => '',
                'sort_order' => 2,
                'shop_group' => ''
            ];
            ShopConfig::insertGetId($rows);
            ShopConfig::where('code', 'copyright_text')->update(['sort_order' => 3]);
            ShopConfig::where('code', 'copyright_link')->update(['sort_order' => 4]);
        }

        $wxapp_config = ShopConfig::where('code', 'wxapp_config')->count();
        if (file_exists(MOBILE_WXAPP) && empty($wxapp_config)) {
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
        }

        $app_config = ShopConfig::where('code', 'app_config')->count();
        if (file_exists(MOBILE_APP) && empty($app_config)) {
            // 增加App配置
            $appId = ShopConfig::insertGetId([
                'parent_id' => 0,
                'code' => 'app_config',
                'type' => 'hidden',
                'store_range' => '',
                'store_dir' => '',
                'value' => '',
                'sort_order' => 1,
                'shop_group' => ''
            ]);

            ShopConfig::insert([
                [
                    'parent_id' => $appId,
                    'code' => 'app_top_img',
                    'type' => 'file',
                    'store_range' => '',
                    'store_dir' => 'images/common/',
                    'value' => '',
                    'sort_order' => 1,
                    'shop_group' => 'app_config'
                ],
                [
                    'parent_id' => $appId,
                    'code' => 'app_top_url',
                    'type' => 'text',
                    'store_range' => '',
                    'store_dir' => '',
                    'value' => '',
                    'sort_order' => 2,
                    'shop_group' => 'app_config'
                ]
            ]);
        }
    }

    /**
     * 删除已弃用的权限
     */
    protected static function delete_merchants_yop()
    {
        DB::table('admin_action')->where('action_code', 'merchants_yop')->delete();
    }

    /**
     * @throws Exception
     */
    private function clean()
    {
        cache()->flush();
    }
}
