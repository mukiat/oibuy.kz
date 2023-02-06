<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplatesSeeder extends Seeder
{

    private $prefix;

    public function __construct()
    {
        $this->prefix = config('database.connections.mysql.prefix');
    }
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // php artisan db:seed --class=EmailTemplatesSeeder
        // 新增重置邮箱
        $this->resetTemplates();
    }

    /**
     * 重置密码
     */
    protected function resetTemplates()
    {
        $table = "INSERT INTO `" . $this->prefix . "mail_templates`";

        $sql = $table . ' ( `template_code`, `is_html`, `template_subject`, `template_content`, `last_modify`, `last_send`, `type`) VALUES
( \'reset_password\', 1, \'密码重置\', \'{$user_name}您好！<br>\n<br>\n您已经进行了密码重置的操作<br>\n<br>\n验证码{$code}，用于密码找回，如非本人操作，请及时检查账户安全！\', 1194824789, 0, \'template\')';
        DB::statement($sql);
    }

   
}
