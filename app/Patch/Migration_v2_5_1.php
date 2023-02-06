<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_5_1
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {

    }

    private function seed()
    {
        ShopConfig::where('code', 'cache_time')->update([
            'type' => 'text'
        ]);

        ShopConfig::where('code', 'wap_logo')->update([
            'type' => 'file'
        ]);

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.5.1'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }
}
