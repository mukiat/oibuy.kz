<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\ValueCardType;
use App\Models\ValueCard;
use App\Repositories\Common\BaseRepository;

class ValueCardSeeder extends Seeder
{
    public function run()
    {
        /* 同步储值卡信息 */
        $value_card = Storage::disk('local')->exists('seeder/vcDis.lock.php');
        if (!$value_card) {
            $this->vcDis();

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/vcDis.lock.php', $data);
        }

        /* 同步储值卡信息 */
        $value_card = Storage::disk('local')->exists('seeder/useCondition.lock.php');
        if (!$value_card) {
            $this->useCondition();

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/useCondition.lock.php', $data);
        }
    }

    private function vcDis()
    {
        ValueCardType::query()->chunkById(5, function ($list) {
            foreach ($list as $key => $row) {
                $row = collect($row)->toArray();

                $res = ValueCard::where('tid', $row['id'])->update([
                    'vc_dis' => $row['vc_dis']
                ]);

                if ($res > 0) {
                    dump("已同步储值卡折扣");
                }
            }
        });
    }

    private function useCondition()
    {
        ValueCardType::query()->where('use_condition', 2)->chunkById(5, function ($list) {
            foreach ($list as $key => $row) {
                $row = collect($row)->toArray();

                $use_merchants = BaseRepository::getExplode($row['use_merchants']);

                if (empty($use_merchants) || !in_array(0, $use_merchants)) {
                    $use_merchants = BaseRepository::getArrayCollapse([$use_merchants, [0]]);
                    $use_merchants = BaseRepository::getImplode($use_merchants);

                    ValueCardType::where('id', $row['id'])->update([
                        'use_merchants' => $use_merchants
                    ]);
                }
            }
        });
    }
}