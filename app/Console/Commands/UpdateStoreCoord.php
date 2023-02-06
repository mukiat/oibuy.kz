<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStoreCoord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update:store_coord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        DB::table('offline_store')->where('longitude', '')->orWhere('latitude', '')->select('id', 'province', 'city', 'district', 'street', 'stores_address')->orderBy('id')->chunk(100, function ($list) {
            foreach ($list as $row) {
                if (in_array($row->province, ['110000', '120000', '310000', '500000', '710000', '810000', '820000'])) {
                    $region_ids = [$row->province, $row->district, $row->street];
                } else {
                    $region_ids = [$row->province, $row->city, $row->district, $row->street];
                }
                $regin_name = DB::table('region')->whereIn('region_id', $region_ids)->orderBy('region_id')->pluck('region_name');
                $regin_name = $regin_name ? $regin_name->toArray() : [];
                if (!empty($regin_name)) {
                    $address = ($regin_name[0] ?? '') . ($regin_name[1] ?? '') . ($regin_name[2] ?? '') . ($regin_name[3] ?? '') . $row->stores_address ?? '';
                    $store_longitude = app('lbs')->address2location($address);
                    if (!empty($store_longitude)) {
                        DB::table('offline_store')->where('id', $row->id)->update(['longitude' => $store_longitude['lng'], 'latitude' => $store_longitude['lat']]);
                        $this->info('SUCCESS FOR ' . $row->id);
                    }
                }
            }
        });
    }
}
