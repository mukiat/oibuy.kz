<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Common\CommonRepository;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $install_exists = Storage::disk('local')->exists('seeder/install.lock.php');
        if (!$install_exists) {
            $this->call([
                InstallSeeder::class,               //安装配置数据
                InstallDemoSeeder::class,           //安装测试数据
                RegionSeeder::class,                //地区数据
                RegionBackupSeeder::class,          //地区备份数据
                RegionWarehouseSeeder::class        //仓库地区数据
            ]);

            /* 标准版 */
            $this->call([
                ConfigModuleSeeder::class,
                MobileModuleSeeder::class,
                StoreModuleSeeder::class, // 门店
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/install.lock.php', $data);
        }

        $this->call([
            PaymentTableSeeder::class,          //支付方式
            AdminActionSeeder::class,           //后台权限
            ShopConfigSeeder::class,            //商城配置信息
            SmsTemplateSeeder::class,           //优化短信配置
            DeleteFileSeeder::class,            //删除多余文件
            CommissionSeeder::class,            //账单结算记录
            ArticleSeeder::class,               //更新文章
            OrderDeliverySeeder::class,         //更新确认收货
            BookingGoodsSeeder::class,          //更新会员信息
            OrderGoodsSeeder::class,            //更新订单商品信息
            ValueCardSeeder::class
        ]);

        /* 微商城 */
        $wechat = Storage::disk('local')->exists('seeder/wechat.lock.php');

        if (!$wechat && file_exists(MOBILE_WECHAT)) {
            $this->call([
                WechatModuleSeeder::class,
                KefuModuleSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/wechat.lock.php', $data);
        }


        /* 微分销 */
        $drp = Storage::disk('local')->exists('seeder/drp.lock.php');
        if (!$drp && file_exists(MOBILE_DRP)) {
            $this->call([
                DrpModuleSeeder::class,
                TeamModuleSeeder::class,
                BargainModuleSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/drp.lock.php', $data);
        }

        /* 小程序 */
        $wxapp = Storage::disk('local')->exists('seeder/wxapp.lock.php');

        if (!$wxapp && file_exists(MOBILE_WXAPP)) {
            $this->call([
                WeappModuleSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/wxapp.lock.php', $data);
        }

        /* APP */
        $app = Storage::disk('local')->exists('seeder/app.lock.php');
        if (!$app && file_exists(MOBILE_APP)) {
            $this->call([
                AppModuleSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/app.lock.php', $data);
        }

        /* 供应链 */
        $supp = Storage::disk('local')->exists('seeder/suppliers.lock.php');
        if (!$supp && file_exists(SUPPLIERS)) {
            $this->call([
                SuppliersModuleSeeder::class,
                WholesaleActionSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/suppliers.lock.php', $data);
        }

        /* 社团 */
        $cgroup = Storage::disk('local')->exists('seeder/cgroup.lock.php');

        if (!$cgroup && file_exists(MOBILE_GROUPBUY)) {
            $this->call([
                CgroupModuleSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/cgroup.lock.php', $data);
        }

        /* 更新虚拟卡卡号 */
        $orderruid = Storage::disk('local')->exists('seeder/virtualcard.lock.php');
        if (!$orderruid) {
            $this->call([
                VirtualCardSnSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/virtualcard.lock.php', $data);
        }

        /* 更新老版本手机端首页可视化 */
        $touchPageView = Storage::disk('local')->exists('seeder/touch_view.lock.php');

        if (!$touchPageView) {
            $this->call([
                TouchPageViewSeeder::class
            ]);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/touch_view.lock.php', $data);
        }

        // 升级版本时 执行的seeder
        $this->upgrade($install_exists);

        /* 操作升级字段信息 */
        CommonRepository::tableField();
    }

    /**
     * 升级版本时 执行的seeder
     * @param bool $install_exists
     */
    protected function upgrade($install_exists = false)
    {
        // 首次安装不执行; 已安装且未升级 每个版本执行一次
        $lock_file = 'upgrade_' . VERSION . '.lock';

        $upgrade_exists = Storage::disk('local')->exists('seeder/' . $lock_file);
        if ($install_exists && !$upgrade_exists) {

            // 标准
            $seederArr = [
                MobileModuleSeeder::class, // h5端
                StoreModuleSeeder::class, // 门店
                TouchPageViewSeeder::class,
            ];

            $data = '大商创x https://www.dscmall.cn/';

            if (file_exists(MOBILE_WECHAT)) {
                $data .= "\n" . '微信通 upgraded';

                $seederArr[] = WechatModuleSeeder::class;
                $seederArr[] = KefuModuleSeeder::class;
            }

            if (file_exists(MOBILE_DRP)) {
                $data .= "\n" . '微分销 upgraded';

                $seederArr[] = DrpModuleSeeder::class;
                $seederArr[] = TeamModuleSeeder::class;
                $seederArr[] = BargainModuleSeeder::class;
            }

            if (file_exists(MOBILE_WXAPP)) {
                $data .= "\n" . '小程序 upgraded';

                $seederArr[] = WeappModuleSeeder::class;
            }

            if (file_exists(MOBILE_APP)) {
                $data .= "\n" . 'App upgraded';

                $seederArr[] = AppModuleSeeder::class;
            }

            if (file_exists(MOBILE_GROUPBUY)) {
                $data .= "\n" . '社区团购 upgraded';

                $seederArr[] = CgroupModuleSeeder::class;
            }

            if (file_exists(SUPPLIERS)) {
                $data .= "\n" . '供应链 upgraded';

                $seederArr[] = SuppliersModuleSeeder::class;
                $seederArr[] = WholesaleActionSeeder::class;
            }

            $this->call($seederArr);

            Storage::disk('local')->put('seeder/' . $lock_file, $data);
        }
    }
}
