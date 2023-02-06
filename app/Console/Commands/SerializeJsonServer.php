<?php

namespace App\Console\Commands;

use App\Models\ConnectUser;
use App\Models\GoodsTransportTpl;
use App\Models\Payment;
use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SerializeJsonServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:field:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compatible with Java, serialize to convert JSON format';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $this->connect_user();
        $this->payment();
        $this->goods_transport_tpl();

        $this->shop_config();
    }

    /**
     * 增加第三方登录字段
     */
    private function connect_user()
    {
        $name = 'connect_user';
        if (Schema::hasTable($name)) {
            if (!Schema::hasColumn($name, 'profile_json')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->text('profile_json')->after('profile')->comment('json 用户信息');
                });
            }

            $this->profile_json();
        }
    }

    private function profile_json()
    {
        ConnectUser::query()->select('id', 'profile')->where('profile_json', '')->chunkById(5, function ($list) {
            foreach ($list as $k => $v) {

                $profile = $v['profile'] ? unserialize($v['profile']) : '';
                $profile = $profile ? json_encode($profile) : '';

                $res = ConnectUser::where('id', $v['id'])->update([
                    'profile_json' => $profile
                ]);

                if ($res > 0) {
                    dump("更新成功， id：" . $v['id']);
                }
            }
        });
    }

    private function shop_config()
    {
        if (file_exists(MOBILE_GROUPBUY)) {
            $config = ShopConfig::select('id')->where('code', 'recruit_leader_json');
            $config = BaseRepository::getToArrayFirst($config);

            if (empty($config)) {

                $parent_id = ShopConfig::where('code', 'recruit_leader')->value('parent_id');
                $parent_id = $parent_id ? $parent_id : 0;

                $value = ShopConfig::where('code', 'recruit_leader')->value('value');
                $value = $value ? unserialize($value) : '';
                $value = json_encode($value);

                $id = ShopConfig::insertGetId([
                    'parent_id' => $parent_id,
                    'code' => 'recruit_leader_json',
                    'type' => 'hidden',
                    'value' => $value,
                    'shop_group' => 'leader'
                ]);

                dump('【' . $id . '】recruit_leader_json 添加成功');
            }
        }
    }

    private function payment()
    {
        $name = 'payment';
        if (Schema::hasTable($name)) {
            if (!Schema::hasColumn($name, 'pay_config_json')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->text('pay_config_json')->after('pay_config')->comment('json 支付方式的配置信息，包括商户号和密钥什么的');
                });
            }

            $this->pay_config_json();
        }
    }

    private function pay_config_json()
    {
        Payment::query()->select('pay_id', 'pay_config')->where('pay_config_json', '')->chunkById(5, function ($list) {
            foreach ($list as $k => $v) {

                $pay_config = $v['pay_config'] ? unserialize($v['pay_config']) : '';
                $pay_config = $pay_config ? json_encode($pay_config) : '';

                $res = Payment::where('pay_id', $v['pay_id'])->update([
                    'pay_config_json' => $pay_config
                ]);

                if ($res > 0) {
                    dump("支付配置信息更新成功， id：" . $v['pay_id']);
                }
            }
        });
    }

    private function goods_transport_tpl()
    {
        $name = 'goods_transport_tpl';
        if (Schema::hasTable($name)) {
            if (!Schema::hasColumn($name, 'configure_json')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->text('configure_json')->after('configure')->comment('json 该配送区域的费用配置信息');
                });
            }

            $this->goods_transport_tpl_json();
        }
    }

    private function goods_transport_tpl_json()
    {
        GoodsTransportTpl::query()->select('id', 'configure')->where('configure_json', '')->chunkById(5, function ($list) {
            foreach ($list as $k => $v) {

                $configure = $v['configure'] ? unserialize($v['configure']) : '';
                $configure = $configure ? json_encode($configure) : '';

                $res = GoodsTransportTpl::where('id', $v['id'])->update([
                    'configure_json' => $configure
                ]);

                if ($res > 0) {
                    dump("运费配置信息更新成功， id：" . $v['id']);
                }
            }
        });
    }
}