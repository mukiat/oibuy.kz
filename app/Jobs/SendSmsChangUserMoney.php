<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;

class SendSmsChangUserMoney implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id;

    /**
     * 会员修改余额发送短信.
     * SendSmsChangUserMoney constructor.
     * @param $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     * @param CommonRepository $commonRepository
     * @return bool
     * @throws \Exception
     */
    public function handle(CommonRepository $commonRepository)
    {
        $user_id = $this->user_id;
        if (empty($user_id)) {
            return false;
        }

        $user_info = Users::where('user_id', $user_id)->select('mobile_phone', 'user_name', 'user_money');
        $user_info = BaseRepository::getToArrayFirst($user_info);


        if ($user_info['mobile_phone'] != '') {
            //短信接口参数
            $smsParams = [
                'user_name' => $user_info['user_name'],
                'username' => $user_info['user_name'],
                'user_money' => $user_info['user_money'],
                'usermoney' => $user_info['user_money'],
                'add_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', TimeRepository::getGmTime()),
                'addtime' => TimeRepository::getLocalDate('Y-m-d H:i:s', TimeRepository::getGmTime()),
            ];
            return $commonRepository->smsSend($user_info['mobile_phone'], $smsParams, 'sms_change_user_money', false);
        }
    }
}
