<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderRuIdSeeder extends Seeder
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
        $this->mainOrder();
        $this->order();
    }

    /**
     * 更新订单子订单数量
     */
    private function mainOrder()
    {
        $sql = "SELECT o.order_id, o.order_sn, (SELECT COUNT(*) FROM " . $this->prefix . "order_info AS oi2 WHERE oi2.main_order_id = o.order_id) as main_count FROM " . $this->prefix . "order_info AS o " .
            " WHERE 1 having main_count > 0";
        $order_list = DB::select($sql);

        if ($order_list) {
            foreach ($order_list as $key => $val) {
                DB::table('order_info')->where('order_id', $val->order_id)->update([
                    'main_count' => $val->main_count
                ]);

                dump("【" . $val->order_id . "】更新订单子订单数量");
                sleep(0.5);
            }
        }
    }

    /**
     * 更新商家订单ru_id值
     */
    private function order()
    {
        $no_main_order = " AND (SELECT COUNT(*) FROM " . $this->prefix . "order_info AS oi2 WHERE oi2.main_order_id = o.order_id) = 0";  //主订单下有子订单时，则主订单不显示
        $sql = "SELECT o.order_id, o.order_sn FROM " . $this->prefix . "order_info AS o " .
            " WHERE (SELECT COUNT(*) FROM " . $this->prefix . "order_goods AS og WHERE o.order_id = og.order_id AND og.ru_id > 0) > 0 AND ru_id = 0 " . $no_main_order;
        $order_list = DB::select($sql);

        if ($order_list) {
            foreach ($order_list as $key => $val) {
                $ru_id = DB::table('order_goods')->where('order_id', $val->order_id)->value('ru_id');

                DB::table('order_info')->where('order_id', $val->order_id)->update([
                    'ru_id' => $ru_id
                ]);

                dump("【" . $val->order_id . "】更新商家订单ru_id值");
                sleep(0.5);
            }
        }
    }
}
