<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\AdminAction;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Migration_v2_7_1
{
    public function run()
    {
        try {
            $this->migration();

            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());

            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        $this->coupons();
        $this->order_info();
        $this->coupons_user();

        if (file_exists(WXAPP_MEDIA)) {
            $this->admin_action();
        }

        $this->seller_follow_list();
        $this->add_seller_follow_list();

        $this->merchants_shop_information();
    }

    public function coupons()
    {
        $name = 'coupons';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'receive_start_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('receive_start_time')->default(0)->comment("领券开始时间");
            });
        }

        if (!Schema::hasColumn($name, 'receive_end_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('receive_end_time')->default(0)->comment("领券结束时间");
            });
        }

        if (!Schema::hasColumn($name, 'status')) {
            Schema::table($name, function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->index('status')->comment('状态 1 未生效，编辑中 2 生效 3 已过期 4 已作废');
            });
        }

        if (!Schema::hasColumn($name, 'promoter_id')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('promoter_id')->default(0)->comment('视频号推广员自增ID');
            });
        }

        if (!Schema::hasColumn($name, 'valid_day_num')) {
            Schema::table($name, function (Blueprint $table) {
                $table->smallInteger('valid_day_num')->default(1)->comment('领取有效天数');
            });
        }

        if (!Schema::hasColumn($name, 'valid_type')) {
            Schema::table($name, function (Blueprint $table) {
                $table->tinyInteger('valid_type')->default(1)->comment('有效期类型 1 按有效区间 2 按领取有效天数');
            });
        }
    }

    public function order_info()
    {
        $name = 'order_info';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'pay_code')) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('pay_code')->default('')->comment("支付编码");
            });
        }
    }

    public function coupons_user()
    {
        $name = 'coupons_user';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'valid_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('valid_time')->default(0)->comment("领取有效截止时间");
            });
        }

        if (!Schema::hasColumn($name, 'status')) {
            Schema::table($name, function (Blueprint $table) {
                $table->tinyInteger('status')->default(2)->index('status')->comment('状态 1 未生效，编辑中 2 生效 3 已过期 4 已作废');
            });
        }

        if (!Schema::hasColumn($name, 'add_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('add_time')->default(0)->comment("领取时间");
            });
        }
    }

    public function seller_follow_list()
    {
        $name = 'seller_follow_list';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name', 60)->default('')->comment('名称');
            $table->string('desc', 100)->default('')->comment('描述');
            $table->integer('seller_id')->default(0)->index('seller_id')->comment('店铺ID');
            $table->string('qr_code')->default('')->comment('二维码');
            $table->string('cover_pic')->default('')->comment('封面图');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . $name . "` comment '店铺二维码关注'");
    }

    public function add_seller_follow_list()
    {
        $name = 'seller_follow_list';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'link_url')) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('link_url')->default('')->comment("外部链接");
            });
        }

        if (!Schema::hasColumn($name, 'click_count')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('click_count')->default(0)->comment("访问量");
            });
        }
    }

    public function merchants_shop_information()
    {
        $name = 'merchants_shop_information';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'collect_count')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('collect_count')->default(0)->comment('店铺关注数量');
            });
        }
    }

    public function admin_action()
    {
        $action_id = AdminAction::where('action_code', 'seller_follow')->count('action_id');

        if (empty($action_id)) {

            $parent_id = AdminAction::where('action_code', 'seller_store_setup')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_follow',
                'seller_show' => 1
            ]);
        }
    }

    private function seed()
    {
        $parent_id = ShopConfig::where('code', 'favourable_show')->value('id');
        $parent_id = $parent_id ? $parent_id : 0;
        if (empty($parent_id)) {
            $parent_id = ShopConfig::query()->insertGetId([
                'parent_id' => 0,
                'code' => 'favourable_show',
                'type' => 'group',
                'shop_group' => 'favourable'
            ]);
        }
        ShopConfig::whereIn('code', ['use_bonus', 'use_coupons', 'use_value_card'])->update([
            'parent_id' => $parent_id,
            'shop_group' => 'favourable'
        ]);

        ShopConfig::where('code', 'use_bonus')->update([
            'store_range' => '0,1'
        ]);

        ShopConfig::where('code', 'favourable_use_open')->delete();

        $update_coupon = config('shop.update_coupon') ?? 0;
        $update_coupon = (int)$update_coupon;

        if ($update_coupon == 0) {
            $installLockFile = Storage::disk('local')->exists('seeder/install.lock.php');
            if ($installLockFile) {

                $id = ShopConfig::where('code', 'update_coupon')->value('id');
                $id = $id ? $id : 0;

                if (empty($id)) {
                    $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');

                    ShopConfig::insert([
                        'parent_id' => $parent_id,
                        'code' => 'update_coupon',
                        'type' => 'hidden',
                        'value' => 1
                    ]);
                } else {
                    ShopConfig::where('id', $id)->update([
                        'value' => 1
                    ]);
                }

                $dbRaw = [
                    'status' => COUPON_STATUS_EFFECTIVE,
                    'receive_start_time' => "cou_start_time",
                    'receive_end_time' => "cou_end_time"
                ];
                $dbRaw = BaseRepository::getDbRaw($dbRaw);

                Coupons::query()->update($dbRaw);

                CouponsUser::query()->update([
                    'status' => COUPON_STATUS_EFFECTIVE
                ]);

                $this->updateCoupons();
            }
        }

        //购买分销权益卡显示店铺价格
        $id = ShopConfig::where('code', 'drp_show_price')->value('id');
        $id = $id ? $id : 0;
        if (empty($id) && file_exists(MOBILE_DRP)) {

            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::query()->insertGetId([
                'parent_id' => $parent_id,
                'code' => 'drp_show_price',
                'type' => 'select',
                'store_range' => '0,1',
                'value' => 0
            ]);
        }

        $this->updateAdminAction();

        // 推荐注册赠送优惠券 配置
        $config_count = DB::table('shop_config')->where('code', 'affiliate_coupons')->count();
        if (empty($config_count)) {
            $parent_id = DB::table('shop_config')->where('code', 'hidden')->value('id');
            $parent_id = $parent_id ?? 0;

            $value = [
                'give_parent' => 1, // 上级是否可获得优惠券 0 否，1 是
                'give_register' => 0, // 注册人是否赠送优惠券 0 否，1 是
                'give_coupons_id' => 0, // 选择可赠送的优惠券id
            ];
            $row = [
                'parent_id' => $parent_id,
                'code' => 'affiliate_coupons',
                'type' => 'hidden',
                'value' => json_encode($value),
            ];
            DB::table('shop_config')->insert($row);
        }

        // 推荐注册赠送优惠券 开发菜单权限 code
        $count = DB::table('admin_action')->where('action_code', 'affiliate_coupons')->count();
        if (empty($count)) {
            // 父级菜单id  同 推荐分成父级菜单
            $parent_id = DB::table('admin_action')->where('action_code', 'sys_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'affiliate_coupons',
                'seller_show' => 0, // 是否控制商家分配权限 0 否 1 是
            ]);
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.7.1'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }

    /**
     * 更新领取可使用有效天数
     */
    private function updateCoupons()
    {
        Coupons::query()->select('cou_id', 'valid_day_num', 'cou_start_time', 'cou_end_time')->chunkById(5, function ($list) {
            foreach ($list as $key => $val) {
                $val = collect($val)->toArray();

                $cou_time = $val['cou_end_time'] - $val['cou_start_time'];
                $valid_day_num = $cou_time / (24 * 3600);
                $valid_day_num = (int)round($valid_day_num);
                $valid_day_num = $valid_day_num <= 0 ? 1 : $valid_day_num;
                $valid_day_num = $valid_day_num > 7 ? 7 : $valid_day_num;

                if (empty($val['valid_day_num'])) {
                    Coupons::where('cou_id', $val['cou_id'])->update([
                        'valid_day_num' => $valid_day_num
                    ]);

                    CouponsUser::where('valid_time', 0)->update([
                        'add_time' => $val['cou_start_time'],
                        'valid_time' => $val['cou_end_time']
                    ]);
                }
            }
        });
    }

    // 更新优化权限
    protected function updateAdminAction()
    {
        Artisan::call('db:seed', ['--class' => \AdminActionSeeder::class, '--force' => true]);

        if (file_exists(MOBILE_APP)) {
            Artisan::call('db:seed', ['--class' => \AppModuleSeeder::class, '--force' => true]);
        }

        if (file_exists(MOBILE_WXAPP)) {
            Artisan::call('db:seed', ['--class' => \WeappModuleSeeder::class, '--force' => true]);
        }

        if (file_exists(MOBILE_DRP)) {
            Artisan::call('db:seed', ['--class' => \DrpModuleSeeder::class, '--force' => true]);
        }

        if (CROSS_BORDER == true) {
            Artisan::call('db:seed', ['--class' => \CrossBorderSeeder::class, '--force' => true]);
        }

    }
}
