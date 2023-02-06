<?php

namespace App\Jobs\Export;

use App\Dsctrait\Exportable;
use App\Models\Users;
use App\Models\ValueCard;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\User\UserDataHandleService;
use App\Services\ValueCard\ValueCardDataHandleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 储值卡导出
 * Class ValueCardExport
 * @package App\Jobs\Export
 */
class ValueCardExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Exportable;

    /**
     * @var array
     */
    private $filter;

    /**
     * 任务可以执行的最大秒数 (超时时间)。
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public $tries = 2;

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
        $dir = 'export/value_card/' . $this->filter['ru_id'];
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $name = $this->filter['file_name'];
        $head = [
            ['column_name' => __('admin/common.record_id')],
            ['column_name' => __('admin/value_card.value_card_sn')],
            ['column_name' => __('admin/value_card.value_card_password')],
            ['column_name' => __('admin/value_card.value_card_type')],
            ['column_name' => __('admin/value_card.value_card_value')],
            ['column_name' => __('admin/value_card.value_card_money')],
            ['column_name' => __('admin/value_card.vc_dis')],
            ['column_name' => __('admin/value_card.bind_user')],
            ['column_name' => __('admin/value_card.bind_time')],
            ['column_name' => __('admin/value_card.card_use_status')]
        ];

        $total = $this->getExportCount();

        $size = 10000;
        $total_page = (int)ceil($total / $size); //计算总分页数

        $data = [];
        for($page = 1; $page <= $total_page; $page++){
            // 插入导出记录表
            $this->filter['request_id'] = DB::table('export_history')->insertGetId([
                'ru_id' => $this->filter['ru_id'],
                'type' => $this->filter['type'],
                'file_name' => $this->filter['file_name'] . '_' . $page,
                'file_type' => 'xls',
                'download_params' => json_encode($this->filter),
                'created_at' => Carbon::now(),
            ]);

            $data = $this->getExportData($page, $size);

            $fields = isset($data[0]) ? array_keys($data[0]) : [];
            $options = [
                'savePath' => Storage::path($dir),
            ];

            $this->fileWrite($name . '_' . $page, $head, $fields, $data, $options);


            // 更新下载记录
            DB::table('export_history')
                ->where('id', $this->filter['request_id'])
                ->update([
                    'download_url' => $dir . '/' . $name . '_' . $page,
                    'updated_at' => Carbon::now()
                ]);
        }

    }

    /**
     * 获取导出数据
     * @return array
     */
    private function getExportData($page, $size)
    {
        $time = TimeRepository::getGmTime();
        $filter = $this->filter;

        $row = ValueCard::whereRaw(1);

        if ($filter['status'] == 0) {
            //失效
            $row = $row->where('use_status', 0);
        } elseif ($filter['status'] == 1) {
            //正常
            $row = $row->where('use_status', 1)
                ->where('end_time', '>', $time);
        } elseif ($filter['status'] == 2) {
            //过期
            $row = $row->where('end_time', '<', $time)
                ->where('end_time', '>', 0);
        }

        if ($filter['keywords']) {
            $row = $row->where(function ($query) use ($filter) {
                $keywords = e($filter['keywords']);

                $user_id = Users::query()->where('user_name', 'like', '%' . $keywords . '%')
                    ->pluck('user_id');
                $user_id = BaseRepository::getToArray($user_id);

                $query = $query->where('value_card_sn', 'like', '%' . $keywords . '%');

                if ($user_id) {
                    $query->orWhereIn('user_id', $user_id);
                }
            });
        }

        if ($filter['tid']) {
            $row = $row->where('tid', $filter['tid']);
        }

        $row = $row->skip(($page - 1) * $size);

        $row = $row->take($size);

        $res = BaseRepository::getToArrayGet($row);

        $arr = [];
        if ($res) {
            $user_id = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);

            $card_id = BaseRepository::getKeyPluck($res, 'tid');
            $cardList = ValueCardDataHandleService::getValueCardTypeDataList($card_id, ['id', 'name', 'add_time']);

            foreach ($res as $key => $val) {
                if (!empty($val['user_id']) && $time > $val['end_time']) {
                    // 增加已过期状态
                    $use_status = 2;
                } else {
                    $use_status = $val['use_status'];
                }

                $arr[$key]['vid'] = $val['vid'];
                $arr[$key]['value_card_sn'] = $val['value_card_sn'];
                $arr[$key]['value_card_password'] = $val['value_card_password'];
                $arr[$key]['name'] = $cardList[$val['tid']]['name'] ?? '';
                $arr[$key]['vc_value'] = $val['vc_value'];
                $arr[$key]['card_money'] = $val['card_money'];
                $arr[$key]['vc_dis'] = $val['vc_dis'] == 1 ? trans('admin::common.wu') : intval($val['vc_dis'] * 10) . trans('admin::value_card.percent');
                $arr[$key]['user_name'] = $userList[$val['user_id']]['user_name'] ?? '';
                $arr[$key]['bind_time'] = $val['bind_time'] > 0 ? TimeRepository::getLocalDate(config('shop.time_format'), $val['bind_time']) : trans('admin::value_card.no_use');
                $arr[$key]['use_status'] = trans('admin::value_card.card_use.'.$use_status);
            }
        }

        return $arr;
    }

    private function getExportCount(){
        $time = TimeRepository::getGmTime();
        $filter = $this->filter;

        $row = ValueCard::whereRaw(1);

        if ($filter['status'] == 0) {
            //失效
            $row = $row->where('use_status', 0);
        } elseif ($filter['status'] == 1) {
            //正常
            $row = $row->where('use_status', 1)
                ->where('end_time', '>', $time);
        } elseif ($filter['status'] == 2) {
            //过期
            $row = $row->where('end_time', '<', $time)
                ->where('end_time', '>', 0);
        }

        if ($filter['keywords']) {
            $row = $row->where(function ($query) use ($filter) {
                $keywords = e($filter['keywords']);

                $user_id = Users::query()->where('user_name', 'like', '%' . $keywords . '%')
                    ->pluck('user_id');
                $user_id = BaseRepository::getToArray($user_id);

                $query = $query->where('value_card_sn', 'like', '%' . $keywords . '%');

                if ($user_id) {
                    $query->orWhereIn('user_id', $user_id);
                }
            });
        }

        if ($filter['tid']) {
            $row = $row->where('tid', $filter['tid']);
        }

        $res = $row->count();
        return $res;
    }
}
