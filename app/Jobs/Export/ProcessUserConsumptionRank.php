<?php

namespace App\Jobs\Export;

use App\Dsctrait\Exportable;
use App\Extensions\File;
use App\Models\OrderInfo;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Services\Order\OrderService;
use App\Services\User\UserDataHandleService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 导出会员消费排行
 * Class ProcessUserConsumptionRank
 * @package App\Jobs
 */
class ProcessUserConsumptionRank implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Exportable;

    /**
     * 任务可以执行的最大秒数 (超时时间)。 如果在任务类中指定，优先级将会高于命令行
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3590;

    /**
     * The number of seconds to wait before retrying the job.
     * 注意：--timeout 的值应该比你在 retry_after 中配置的值至少短几秒。这会确保处理器永远会在一个任务被重试之前中止。如果你的 --timeout 值比 retry_after 的值长的话，你的任务可能会被执行两次。
     * @var int
     */
    public $retryAfter = 3600;

    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public $tries = 2;

    /**
     * @var array
     */
    private $requestData;

    /**
     * Create a new job instance.
     * @param $requestData
     */
    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $filter = $this->requestData;

        $limit = $filter['page_size'] = 100; // 每次固定查询条数 尽量不要太大，减少数据库查询压力

        // 订单总数
        $total = $this->exportTotal($filter);

        /* page 总数 */
        $page_count = (!empty($total) && $total > 0) ? ceil($total / $limit) : 0;
        $page_count = intval($page_count);// 获取总页数

        /**
         * 导出当前分页订单csv文件
         */
        if ($this->requestData['file_name']) {
            $head = [
                ['column_name' => trans('admin::common.record_id')],
                ['column_name' => trans('admin::user_stats.user_name')],
                ['column_name' => trans('admin::user_stats.user_sale_stats.2'), 'num' => 1],
                ['column_name' => trans('admin::user_stats.user_sale_stats.3'), 'num' => 1],
                ['column_name' => trans('admin::user_stats.user_sale_stats.4'), 'num' => 1],
                ['column_name' => trans('admin::user_stats.user_sale_stats.5'), 'num' => 1],
                ['column_name' => trans('admin::user_stats.user_sale_stats.6'), 'num' => 1],
                ['column_name' => trans('admin::user_stats.user_sale_stats.7'), 'num' => 1]
            ];

            // 需要导出字段 须和查询数据里的字段名保持一致
            $fields = [
                'user_id', // 会员id
                'user_name', // 会员名称
                'total_num',
                'total_fee',
                'valid_num',
                'valid_fee',
                'return_num',
                'return_fee',
            ];

            // 初始化导出文件
            $dir = 'export/user_consumption_rank/' . $this->requestData['admin_id'] ?? 0;
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
            }
            $disk = File::diskFile();
            if ($disk != 'public') {
                if (!Storage::disk($disk)->exists($dir)) {
                    Storage::disk($disk)->makeDirectory($dir);
                }
            }

            $options = [
                'savePath' => Storage::path($dir)
            ];

            /**
             * 循环处理订单数据
             */
            $yield_order = null;

            for ($page = 1; $page <= $page_count; $page++) {
                $start = ($page - 1) * $limit;
                $list[] = $this->exportDataList($filter, $start, $limit);// 获取订单数组
                $yield_order = self::yieldData($list);
            }

            // 已知总数 计算 按多少等分  如：总数 1000 按每5000等分 可分 1
            $average = ($total > 0) ? (int)ceil($total / 5000) : 1; // 修改此处的分母值,可控制导出Excel的每页最大行数
            $chunk = ($total > 0) ? (int)ceil($total / $average) : 0;

            // 待存excel数据
            $excelData = [];
            // 平分数量
            $section = [];
            $max = 0;
            // 每次拆分区间数量(向上取整)
            $sec_chunk = $chunk > 0 ? (int)ceil($chunk / $limit) : 0;
            for ($i = 1; $i <= $average; $i++) {
                $excelData[$i] = [];
                // 最小值与最大值 区间范围
                $section[$i] = self::section($max, $sec_chunk);
            }

            // 迭代器遍历 按平分数量合并数组
            foreach ($yield_order as $key => $value) {
                for ($i = 1; $i <= $average; $i++) {
                    if ($key >= $section[$i]['min'] && $key < $section[$i]['max'] && isset($excelData[$i])) {
                        array_push($excelData[$i], $value);
                    }
                }
            }

            // 生成excel表格
            if (!empty($excelData)) {
                foreach ($excelData as $page => $data) {

                    $out_title = $this->requestData['file_name'] . '_' . $page;

                    if ($page > 1) {
                        // 插入导出记录表
                        $this->requestData['request_id'] = DB::table('export_history')->insertGetId([
                            'ru_id' => $this->requestData['admin_id'] ?? 0,
                            'type' => $this->requestData['type'] ?? 'user_consumption_rank',
                            'file_name' => $out_title,
                            'file_type' => 'xls',
                            'download_params' => json_encode($this->requestData),
                            'created_at' => Carbon::now(),
                        ]);
                    }

                    // 导出 Excel 文件
                    $data = array_flatten($data, 1);
                    $this->fileWrite($out_title, $head, $fields, $data, $options);

                    // 上传Excel文件至 OSS
                    File::ossMirror($dir . '/' . $out_title . '.xls');

                    // 更新下载记录
                    DB::table('export_history')
                        ->where('id', $this->requestData['request_id'])
                        ->update([
                            'download_url' => $dir . '/' . $out_title,
                            'updated_at' => Carbon::now()
                        ]);
                }
            }
        }
    }

    /**
     * 任务失败的处理过程
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // 任务失败
        info($exception->getMessage());
    }

    /**
     * 总数
     * @param array $filter
     * @return int
     */
    protected function exportTotal($filter = [])
    {
        $row = OrderInfo::query()->distinct()->select('user_id')->where('main_count', 0);

        if ($filter['start_date']) {
            $row = $row->where('add_time', '>=', $filter['start_date']);
        }

        if ($filter['end_date']) {
            $row = $row->where('add_time', '<=', $filter['end_date']);
        }

        if ($filter['keywords']) {
            $keywords = e($filter['keywords']);
            $userIdList = Users::query()->where('user_name', 'like', $keywords)->pluck('user_id');
            $userIdList = BaseRepository::getToArray($userIdList);
            if ($userIdList) {
                $row = $row->whereIn('user_id', $userIdList);
            }
        }

        return $row->count('user_id');
    }

    /**
     * 列表
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @return array
     */
    protected function exportDataList($filter = [], $start = 0, $limit = 100)
    {
        $row = OrderInfo::query()->distinct()->select('user_id')->where('main_count', 0);

        $row = $row->withCount([
            //下单数量
            'getUserOrder as total_num' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("count(order_id) as total_num"));
            },
            //下单金额
            'getUserOrder as total_fee' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("SUM( goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee ) AS total_fee"));
            },
            //有效下单量
            'getUserOrder as valid_num' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("COUNT(DISTINCT IF((order_status!=" . OS_INVALID . " AND order_status!=" . OS_CANCELED . "), order_id, NULL)) as valid_num"));
            },
            //有效下单金额
            'getUserOrder as valid_fee' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("SUM(IF((order_status!=" . OS_INVALID . " AND order_status!=" . OS_CANCELED . "), " . OrderService::orderAmountField() . ", 0)) as valid_fee"));
            },
            //退款订单数量
            'getUserOrderReturn as return_num' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('return_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("COUNT(ret_id) as return_num"))->where('refound_status', 1)->whereIn('return_type', [1, 3]); //退换货， 已退款
            },
            //退款订单金额
            'getUserOrderReturn as return_fee' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('return_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("SUM(actual_return) as return_fee"))->where('refound_status', 1)->whereIn('return_type', [1, 3]); //退换货， 已退款
            }
        ]);

        if ($filter['start_date'] && $filter['end_date']) {
            $row = $row->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
        }

        if ($filter['keywords']) {
            $keywords = e($filter['keywords']);
            $userIdList = Users::query()->where('user_name', 'like', $keywords)->pluck('user_id');
            $userIdList = BaseRepository::getToArray($userIdList);
            if ($userIdList) {
                $row = $row->whereIn('user_id', $userIdList);
            }
        }

        // 分页查询
        $row = $row->skip($start);
        if ($limit > 0) {
            $row = $row->take($limit);
        }

        $row = $row->orderBy($filter['sort_by'], $filter['sort_order']);

        $res = BaseRepository::getToArrayGet($row);

        if ($res) {
            $userIdList = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = UserDataHandleService::userDataList($userIdList, ['user_id', 'user_name', 'nick_name', 'mobile_phone']);

            foreach ($res as $key => $value) {
                $res[$key]['total_fee'] = $value['total_fee'] ?? 0;
                $res[$key]['valid_fee'] = $value['valid_fee'] ?? 0;
                $res[$key]['return_fee'] = $value['return_fee'] ?? 0;

                $res[$key]['total_num'] = $value['total_num'] ?? 0;
                $res[$key]['valid_num'] = $value['valid_num'] ?? 0;
                $res[$key]['return_num'] = $value['return_num'] ?? 0;

                $res[$key]['user_name'] = $userList[$value['user_id']]['user_name'] ?? '';
                $res[$key]['nick_name'] = $userList[$value['user_id']]['nick_name'] ?? '';
                $res[$key]['mobile_phone'] = $userList[$value['user_id']]['mobile_phone'] ?? '';
            }
        }

        return $res;
    }

    /**
     * @param $data
     * @return \Generator
     */
    public static function yieldData($data)
    {
        foreach ($data as $value) {
            yield $value;
        }
    }

    /**
     * @param int $max
     * @param int $chunk
     * @return array|int[]
     */
    public static function section(&$max = 0, $chunk = 0)
    {
        $min = $max;
        $max = $min + $chunk;

        return ['min' => $min, 'max' => $max];
    }
}