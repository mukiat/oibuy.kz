<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->update_desc();
        $this->delete_post();
    }

    /**
     * 更新支付宝在线申请地址
     */
    private function update_desc()
    {
        $count = DB::table('payment')->where('pay_code', 'alipay')->count();

        if ($count > 0) {
            // 默认数据
            $pay_desc =  '支付宝网站(www.alipay.com) 是国内先进的网上支付平台。';
            DB::table('payment')->where('pay_code', 'alipay')->where('pay_desc', '<>', $pay_desc)->update(['pay_desc' =>$pay_desc]);
        }
    }

    /**
     * 删除邮局汇款
     */
    private function delete_post()
    {
        DB::table('payment')->where('pay_code', 'post')->where('enabled', '0')->delete();
    }
}
