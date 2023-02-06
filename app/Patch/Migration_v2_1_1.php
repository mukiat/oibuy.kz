<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_1_1
{
    public function run()
    {
        try {
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 更新版本
     *
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.1.1'
        ]);

        $this->clearCache();
    }

    /**
     * @throws Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}