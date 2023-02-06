<?php

namespace App\Console\Commands;

use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class EmailSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:email:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send email_sendlist data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 是否开启自动发送邮件队列
        if (config('shop.send_mail_on') == 'off') {
            return false;
        }

        $count = DB::table('email_sendlist')->count('id');
        //发送列表为空
        if (empty($count)) {
            return false;
        }

        DB::table('email_sendlist')->orderBy('id')->chunk(100, function ($list) {
            foreach ($list as $row) {
                if (!is_null($row)) {
                    //发送列表不为空，邮件地址为空
                    $email = $row->email ?? '';
                    if (empty($email)) {
                        DB::table('email_sendlist')->where('id', $row->id)->delete();
                        continue;
                    }

                    //查询相关模板
                    $rt = DB::table('mail_templates')->where('template_id', $row->template_id)->first();
                    if (empty($rt)) {
                        continue;
                    }

                    //如果是模板，则将已存入email_sendlist的内容作为邮件内容
                    //否则即是杂志，将mail_templates调出的内容作为邮件内容
                    if ($rt->type == 'template') {
                        $rt->template_content = stripslashes($row->email_content);
                    } else {
                        $rt->template_content = stripslashes($rt->template_content);
                    }

                    if ($row->email && $rt->template_id && $rt->template_content) {
                        list($name) = explode('@', $row->email);
                        if (CommonRepository::sendEmail($name, $row->email, $rt->template_subject, $rt->template_content, $rt->is_html)) {
                            //发送成功 从列表中删除
                            DB::table('email_sendlist')->where('id', $row->id)->delete();
                        } else {
                            //发送出错
                            if ($row->error && $row->error < 3) {
                                $time = TimeRepository::getGmTime();
                                $extra = [
                                    'pri' => 0,
                                    'last_send' => $time,
                                ];
                                DB::table('email_sendlist')->where('id', $row->id)->increment('error', 1, $extra);
                            } else {
                                //将出错超次的纪录删除
                                DB::table('email_sendlist')->where('id', $row->id)->delete();
                            }
                        }
                    } else {
                        //无效的邮件队列
                        DB::table('email_sendlist')->where('id', $row->id)->delete();
                    }
                }
            }
        });

    }
}
