<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VirtualCardSnSeeder extends Seeder
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
        $this->virtualCardSn();
    }

    /**
     * 更新
     */
    private function virtualCardSn()
    {
        $sql = "SELECT card_id, card_sn FROM " . $this->prefix . "virtual_card";
        $virtual_card_list = DB::select($sql);

        if ($virtual_card_list) {
            foreach ($virtual_card_list as $key => $val) {
                DB::table('virtual_card')->where('card_id', $val->card_id)->update([
                    'card_sn' => $this->dsc_decrypt($val->card_sn)
                ]);
            }
        }
    }

    /**
     * 解密函数
     * @param string $str 加密后的字符串
     * @param string $key 密钥
     * @return  string  加密前的字符串
     */
    private function dsc_decrypt($str)
    {
        $key = defined('AUTH_KEY') ? AUTH_KEY : 'this is a key';
        $coded = '';
        $keylength = strlen($key);
        $str = base64_decode($str);

        for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength) {
            $coded .= substr($str, $i, $keylength) ^ $key;
        }

        return $coded;
    }
}
