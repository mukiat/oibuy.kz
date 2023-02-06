<?php

namespace App\Providers;

use Illuminate\Mail\MailServiceProvider as ServiceProvider;

class MailServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 定义全局邮件配置
        $config = config('shop');

        if (!empty($config) && !empty($config['smtp_user'])) {
            $email_cfg = [
                'driver' => 'smtp', // log 可代替真实发送，邮件驱动将所有邮件消息写入日志文件以供检查
                'host' => $config['smtp_host'],
                'port' => $config['smtp_port'],
                'from' => [
                    'address' => $config['smtp_mail'],
                    'name' => $config['shop_name'],
                ],
                'encryption' => intval($config['smtp_ssl']) > 0 ? 'ssl' : null,
                'username' => $config['smtp_user'],
                'password' => $config['smtp_pass'],
            ];

            config(['mail' => array_merge(config('mail'), $email_cfg)]);
        }
    }
}
