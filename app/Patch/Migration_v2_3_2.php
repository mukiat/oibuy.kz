<?php

namespace App\Patch;

use App\Contracts\MigrationContract;
use App\Events\RunOtherModulesSeederEvent;
use App\Models\AdminAction;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_3_2 implements MigrationContract
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 数据库迁移
     * @return mixed|void
     */
    public function migration()
    {
        // 店铺收发货地址
        $tableName = 'shop_address';
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id')->comment('编号');
                $table->unsignedInteger('ru_id')->comment('店铺ID');
                $table->string('contact')->comment('联系人');
                $table->string('mobile')->comment('联系电话');
                $table->string('province_id')->comment('省ID');
                $table->string('province')->comment('省');
                $table->string('city_id')->comment('市ID');
                $table->string('city')->comment('市');
                $table->string('district_id')->comment('区、县ID');
                $table->string('district')->comment('区、县');
                $table->string('address')->comment('详细地址');
                $table->string('zip_code')->comment('邮编');
                $table->unsignedTinyInteger('type')->comment('地址类型：发货地址1，退货地址2');
                $table->timestamps();
            });
        }
    }

    /**
     * 数据库填充
     * @return mixed|void
     */
    public function seed()
    {
        // 会员余额充值设置项
        $this->add_balance_recharge();

        // 增加短信验证码有效期
        $this->validity_of_sms();

        //管理员权限添加
        $this->adminAction();

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.3.2'
        ]);
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function clean()
    {
        // 独立PC模块 删除文件
        $this->moveFile();

        cache()->flush();
    }

    /**
     * 会员余额充值设置项
     */
    private function add_balance_recharge()
    {
        $parent_id = ShopConfig::where('code', 'basic')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'user_balance_recharge')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'user_balance_recharge',
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
     * 增加短信验证码有效期
     */
    private function validity_of_sms()
    {
        $parent_id = ShopConfig::where('code', 'sms')->value('id');

        $result = ShopConfig::where('code', 'sms_validity')->count();
        if (empty($result)) {
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'sms_validity',
                    'value' => '',
                    'type' => 'text',
                    'store_range' => '',
                    'sort_order' => 50,
                    'shop_group' => 'sms',
                ]
            ];
            ShopConfig::insert($rows);
        }
    }

    /**
     * 独立PC模块 删除文件
     */
    protected function moveFile()
    {
        $filesystem = new Filesystem();

        $files = $filesystem->files(app_path('Http/Controllers'));

        $not_move = [
            0 => "ApiController.php",
            1 => "BarcodegenController.php",
            2 => "CalendarController.php",
            3 => "CaptchaVerifyController.php",
            4 => "Controller.php",
            5 => "EditorController.php",
            6 => "GetAjaxContentController.php",
            7 => "InitController.php",
            8 => "PluginController.php",
            9 => "PmController.php",
            10 => "QrcodeController.php",
            11 => "RegionController.php",
            12 => "RegionGoodsController.php",
            13 => "RespondController.php",
            14 => "SdController.php",
            15 => "TrackerController.php",
            16 => "DownloadController.php",
        ];
        foreach ($files as $file) {
            // 删除文件
            if ($filesystem->isFile($file)) {
                $file_name = $file->getFilename();
                if (!in_array($file_name, $not_move)) {
                    $filesystem->delete($file);
                }
            }
        }

        /**
         * 删除文件或目录
         */
        $list = [
            app_path('Http/Controllers/Install'),
            app_path('Http/Controllers/Mobile'),
            app_path('Modules/User'),
            resource_path('mobile'),
            resource_path('mobile.blade.php'),
        ];

        foreach ($list as $k => $v) {
            // 删除文件
            if ($filesystem->isFile($v)) {
                $filesystem->delete($v);
            }

            // 删除目录下所有文件
            if ($filesystem->isDirectory($v)) {
                $filesystem->deleteDirectory($v);
            }
        }
    }

    protected function adminAction()
    {
        if (file_exists(MOBILE_WXSHOP)) {
            $this->wxshop();
        }

        // 增加地址库权限
        $address_manage = DB::table('admin_action')->where('action_code', 'address_manage')->count();
        if ($address_manage == 0) {
            $parent_id = DB::table('admin_action')->where('action_code', 'sys_manage')->value('action_id');
            DB::table('admin_action')->insertGetId([
                'parent_id' => $parent_id,
                'action_code' => 'address_manage',
                'relevance' => '',
                'seller_show' => 1,
            ]);
        }
    }

    private function wxshop()
    {
        /* 微信小商店 start */
        $parent_id = AdminAction::where('action_code', 'wxshop')->value('action_id');
        $parent_id = $parent_id ? $parent_id : 0;
        if (empty($parent_id)) {
            $parent_id = AdminAction::insertGetId([
                'parent_id' => 0,
                'action_code' => 'wxshop',
                'seller_show' => 0
            ]);
        }

        //服务商设置
        $action_id = AdminAction::where('action_code', 'wxshop_config')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'wxshop_config',
                'seller_show' => 0
            ]);
        }

        //小商店列表
        $action_id = AdminAction::where('action_code', 'wxshop_seller')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'wxshop_seller',
                'seller_show' => 0
            ]);
        }

        /* 微信小商店(商户后台) start */
        $parent_id = AdminAction::where('action_code', 'seller_wxshop')->value('action_id');
        $parent_id = $parent_id ? $parent_id : 0;
        if (empty($parent_id)) {
            $parent_id = AdminAction::insertGetId([
                'parent_id' => 0,
                'action_code' => 'seller_wxshop',
                'seller_show' => 1
            ]);
        }

        //商户后台  微信小商店
        $action_id = AdminAction::where('action_code', 'seller_wxshop_audit')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_wxshop_audit',
                'seller_show' => 1
            ]);
        } else {
            if (!empty($parent_id)) {
                AdminAction::where('action_code', 'seller_wxshop_audit')->update([
                    'parent_id' => $parent_id
                ]);
            }
        }

        //商户后台  小商店商品
        $action_id = AdminAction::where('action_code', 'seller_wxshop_goods')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_wxshop_goods',
                'seller_show' => 1
            ]);
        } else {
            if (!empty($parent_id)) {
                AdminAction::where('action_code', 'seller_wxshop_goods')->update([
                    'parent_id' => $parent_id
                ]);
            }
        }

        //商户后台  商品类目
        $action_id = AdminAction::where('action_code', 'seller_wxshop_category')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_wxshop_category',
                'seller_show' => 1
            ]);
        } else {
            if (!empty($parent_id)) {
                AdminAction::where('action_code', 'seller_wxshop_category')->update([
                    'parent_id' => $parent_id
                ]);
            }
        }

        //商户后台  商品品牌
        $action_id = AdminAction::where('action_code', 'seller_wxshop_brand')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_wxshop_brand',
                'seller_show' => 1
            ]);
        } else {
            if (!empty($parent_id)) {
                AdminAction::where('action_code', 'seller_wxshop_brand')->update([
                    'parent_id' => $parent_id
                ]);
            }
        }

        //商户后台  运费模板
        $action_id = AdminAction::where('action_code', 'seller_wxshop_template_freight')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_wxshop_template_freight',
                'seller_show' => 1
            ]);
        } else {
            if (!empty($parent_id)) {
                AdminAction::where('action_code', 'seller_wxshop_template_freight')->update([
                    'parent_id' => $parent_id
                ]);
            }
        }
        /* 微信小商店 end */
    }
}
