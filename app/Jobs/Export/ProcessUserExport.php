<?php

namespace App\Jobs\Export;

use App\Dsctrait\Exportable;
use App\Extensions\File;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\User\UsersRepository;
use App\Services\Merchant\MerchantDataHandleService;
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
 * 会员列表导出
 * Class ProcessUserExport
 * @package App\Jobs\Export
 */
class ProcessUserExport implements ShouldQueue
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
            $column_str = trans('admin::common.user_date_notic');
            $column = explode(',', $column_str);
            $head = [];
            foreach ($column as $key => $val) {
                $head[$key]['column_name'] = $val;
                if (in_array($key, [7, 8, 9, 11])) {
                    $head[$key]['num'] = 1;
                }
            }

            // 需要导出字段 须和查询数据里的字段名保持一致
            $fields = [
                'user_id', // 会员id
                'user_name', // 会员名称
                'nick_name', // 会员昵称
                'ru_name',
                'mobile_phone',
                'email',
                'is_validated_format',
                'user_money',
                'frozen_money',
                'rank_points',
                'rank_name',
                'pay_points',
                'reg_time',
            ];

            // 初始化导出文件
            $dir = 'export/user_list/' . $this->requestData['admin_id'] ?? 0;
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
                            'type' => $this->requestData['type'] ?? 'user',
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
        return UsersRepository::instance()->user_total($filter);
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
        $user_list = UsersRepository::instance()->user_list($filter, $start, $limit);

        if ($user_list) {
            $ru_id = BaseRepository::getKeyPluck($user_list, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id, 1);

            $user_rank = BaseRepository::getKeyPluck($user_list, 'user_rank');
            $rank_list = UsersRepository::instance()->setModel('App\Models\UserRank')->whereInExtend('rank_id', $user_rank, ['rank_name']);

            foreach ($user_list as $key => $value) {
                $user_list[$key]['ru_name'] = $merchantList[$value['user_id']] ?: trans('admin::users.mall_user');
                $user_list[$key]['rank_name'] = $rank_list[$value['user_rank']]['rank_name'] ?: trans('admin::users.not_rank');

                $user_list[$key]['reg_time'] = TimeRepository::getLocalDate(config('shop.date_format'), $value['reg_time']);
                $user_list[$key]['user_picture'] = app(DscRepository::class)->getImagePath($value['user_picture']);

                $user_list[$key]['nick_name'] = StrRepository::filterSpecialCharacters($value['nick_name']); // 过滤昵称特殊字符
                $user_list[$key]['is_validated_format'] = $value['is_validated'] == 1 ? trans('admin/common.yes') : trans('admin/common.no');
            }
        }

        return $user_list;
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

