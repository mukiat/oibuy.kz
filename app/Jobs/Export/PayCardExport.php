<?php

namespace App\Jobs\Export;

use App\Dsctrait\Exportable;
use App\Models\PayCard;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 充值卡导出
 * Class PayCardExport
 * @package App\Jobs\Export
 */
class PayCardExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Exportable;

    /**
     * @var array
     */
    private $filter;

    /**
     * Create a new job instance.
     *
     * @param $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dir = 'export/pay_card/' . $this->filter['ru_id'];
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $name = $this->filter['file_name'];
        $head = [
            ['column_name' => __('admin/common.record_id')],
            ['column_name' => __('admin/pay_card.bonus_sn')],
            ['column_name' => __('admin/pay_card.bonus_psd')],
            ['column_name' => __('admin/pay_card.bonus_type')],
            ['column_name' => __('admin/pay_card.type_money')],
            ['column_name' => __('admin/pay_card.use_enddate')],
            ['column_name' => __('admin/pay_card.user_id')],
            ['column_name' => __('admin/pay_card.used_time')]
        ];

        $data = $this->getExportData();
        $fields = isset($data[0]) ? array_keys($data[0]) : [];
        $options = [
            'savePath' => Storage::path($dir),
        ];

        $this->fileWrite($name, $head, $fields, $data, $options);

        // 更新下载记录
        DB::table('export_history')
            ->where('id', $this->filter['request_id'])
            ->update([
                'download_url' => $dir . '/' . $name,
                'updated_at' => Carbon::now()
            ]);
    }

    /**
     * @return array
     */
    private function getExportData()
    {
        $id = (int)$this->filter['id'];

        $res = PayCard::whereRaw(1);
        if ($id > 0) {
            $res = $res->where('c_id', $id);
        }
        $res = $res->with(['getPayCardType', 'getUsers']);
        $row = BaseRepository::getToArrayGet($res);

        $arr = [];
        foreach ($row as $key => $val) {
            $val['type_name'] = $val['get_pay_card_type']['type_name'] ?? '';
            $val['type_money'] = $val['get_pay_card_type']['type_money'] ?? '';
            $val['use_end_date'] = $val['get_pay_card_type']['use_end_date'] ?? '';

            $val['user_name'] = $val['get_users']['user_name'] ?? '';
            $val['email'] = $val['get_users']['email'] ?? '';

            $arr[$key]['id'] = $val['id'];
            $arr[$key]['card_number'] = $val['card_number'];
            $arr[$key]['card_psd'] = $val['card_psd'];
            $arr[$key]['type_name'] = $val['type_name'];
            $arr[$key]['type_money'] = $val['type_money'];
            $arr[$key]['use_end_date'] = $val['use_end_date'] == 0 ?
                trans('admin::pay_card.no_use') : TimeRepository::getLocalDate(config('shop.date_format'), $val['use_end_date']);
            $arr[$key]['user_name'] = !empty($val['user_name']) ? $val['user_name'] : trans('admin::pay_card.no_use');
            $arr[$key]['used_time'] = $val['used_time'] == 0 ?
                trans('admin::pay_card.no_use') : TimeRepository::getLocalDate(config('shop.date_format'), $val['used_time']);
        }

        return $arr;
    }
}
