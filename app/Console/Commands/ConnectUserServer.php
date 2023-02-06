<?php

namespace App\Console\Commands;

use App\Models\ConnectUser;
use App\Modules\Wechat\Models\WechatUser;
use Illuminate\Console\Command;

class ConnectUserServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:connect:user {action=openid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'connect user command';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 缓存操作
     *
     * @throws \Exception
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action == 'openid') {
            ConnectUser::query()->where('connect_code', 'sns_wechat')
                ->chunkById(5, function ($list) {

                    foreach ($list as $key => $value) {
                        $value = collect($value)->toArray();
                        $profile = $value['profile'] ? unserialize($value['profile']) : [];

                        if ($profile && (!isset($profile['openid']) || empty($profile['openid']))) {

                            $wechatUser = WechatUser::select('ect_uid', 'openid')
                                ->where('ect_uid', $value['user_id'])
                                ->where('unionid', $value['open_id'])
                                ->first();
                            $wechatUser = $wechatUser ? $wechatUser->toArray() : [];

                            if ($wechatUser && !empty($wechatUser['openid'])) {
                                $profile['openid'] = $wechatUser['openid'];

                                $upProfile = serialize($profile);
                                $res = ConnectUser::where('id', $value['id'])->update([
                                    'profile' => $upProfile
                                ]);

                                if ($res > 0) {
                                    info('-------', [$value['user_id'], $profile['openid']]);
                                    dump("执行成功-----【" . $value['user_id'] . "---" . $profile['openid'] . "】");
                                }
                            }
                        }

                        sleep(0.5);
                    }
                });
        }
    }
}