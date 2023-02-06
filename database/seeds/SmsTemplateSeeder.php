<?php

use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->smsShopConfig();
        $this->delFile();
        $this->update_temp_content();
    }

    /**
     * 添加短信配置
     */
    public function smsShopConfig()
    {
        $parent_id = DB::table('shop_config')->where('code', 'sms')->value('id');

        $sms_key = DB::table('shop_config')->where('code', 'huawei_sms_key')->count();
        if ($sms_key <= 0) {
            DB::table('shop_config')->insert([
                'parent_id' => $parent_id,
                'code' => 'huawei_sms_key',
                'type' => 'text',
                'sort_order' => 10,
                'shop_group' => 'sms'
            ]);
        }

        $sms_secret = DB::table('shop_config')->where('code', 'huawei_sms_secret')->count();
        if ($sms_secret <= 0) {
            DB::table('shop_config')->insert([
                'parent_id' => $parent_id,
                'code' => 'huawei_sms_secret',
                'type' => 'text',
                'sort_order' => 10,
                'shop_group' => 'sms'
            ]);
        }
    }

    /**
     * 修复结尾符号为英文句号的数据
     */
    public function update_temp_content()
    {
        $sms_order_placed = DB::table('sms_template')->where('send_time', 'sms_order_placed')->value('temp_content');
        if (!empty($sms_order_placed)) {
            $new_content = str_replace('.', '。', $sms_order_placed);
            DB::table('sms_template')->where('send_time', 'sms_order_placed')->update(['temp_content' => $new_content]);
        }

        $sms_order_payed = DB::table('sms_template')->where('send_time', 'sms_order_payed')->value('temp_content');
        if (!empty($sms_order_payed)) {
            $new_content = str_replace('.', '。', $sms_order_payed);
            DB::table('sms_template')->where('send_time', 'sms_order_payed')->update(['temp_content' => $new_content]);
        }

        $sms_order_shipped = DB::table('sms_template')->where('send_time', 'sms_order_shipped')->value('temp_content');
        if (!empty($sms_order_shipped)) {
            $new_content = str_replace('.', '。', $sms_order_shipped);
            DB::table('sms_template')->where('send_time', 'sms_order_shipped')->update(['temp_content' => $new_content]);
        }

        $store_order_code = DB::table('sms_template')->where('send_time', 'store_order_code')->value('temp_content');
        if (!empty($store_order_code)) {
            $new_content = str_replace('.', '。', $store_order_code);
            DB::table('sms_template')->where('send_time', 'store_order_code')->update(['temp_content' => $new_content]);
        }

        $sms_order_received = DB::table('sms_template')->where('send_time', 'sms_order_received')->value('temp_content');
        if (!empty($sms_order_received)) {
            $new_content = str_replace('.', '。', $sms_order_received);
            DB::table('sms_template')->where('send_time', 'sms_order_received')->update(['temp_content' => $new_content]);
        }

        $sms_shop_order_received = DB::table('sms_template')->where('send_time', 'sms_shop_order_received')->value('temp_content');
        if (!empty($sms_shop_order_received)) {
            $new_content = str_replace('.', '。', $sms_shop_order_received);
            DB::table('sms_template')->where('send_time', 'sms_shop_order_received')->update(['temp_content' => $new_content]);
        }
    }

    /**
     * 删除文件
     */
    protected function delFile()
    {
        $list = [
            //数据迁移文件
            base_path('database/migrations/2018_10_19_095059_create_alidayu_configure_table.php'),
            base_path('database/migrations/2018_10_19_095059_create_alitongxin_configure_table.php'),

            //数据模型文件
            app_path('Entities/AlidayuConfigure.php'),
            app_path('Entities/AlitongxinConfigure.php'),
            app_path('Models/AlidayuConfigure.php'),
            app_path('Models/AlitongxinConfigure.php'),

            //后台文件
            app_path('Modules/Admin/Controllers/AlidayuConfigureController.php'),
            app_path('Modules/Admin/Controllers/AlitongxinConfigureController.php'),
            app_path('Modules/Admin/Controllers/HuyiConfigureController.php'),
            app_path('Modules/Admin/Views/alidayu_configure_info.dwt'),
            app_path('Modules/Admin/Views/alidayu_configure_list.dwt'),
            app_path('Modules/Admin/Views/alitongxin_configure_info.dwt'),
            app_path('Modules/Admin/Views/alitongxin_configure_list.dwt'),
            app_path('Modules/Admin/Views/huyi_configure_info.dwt'),
            app_path('Modules/Admin/Views/huyi_configure_list.dwt'),
            app_path('Modules/Admin/Languages/zh_cn/alidayu_configure.php'),
            app_path('Modules/Admin/Languages/zh_cn/alitongxin_configure.php'),
        ];

        $filesystem = new Filesystem();

        foreach ($list as $k => $v) {
            if ($filesystem->isFile($v)) {
                $filesystem->delete($v);
            }
        }
    }
}
