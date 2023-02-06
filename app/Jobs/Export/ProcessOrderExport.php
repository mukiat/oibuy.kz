<?php

namespace App\Jobs\Export;

use App\Dsctrait\Exportable;
use App\Extensions\File;
use App\Repositories\Common\BaseRepository;
use App\Services\Order\OrderExportService;
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
 * 订单导出
 * Class ProcessOrderExport
 * @package App\Jobs\Export
 */
class ProcessOrderExport implements ShouldQueue
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
     * @param OrderExportService $orderExportService
     * @return void
     * @throws \Exception
     */
    public function handle(OrderExportService $orderExportService)
    {
        $filter = $this->requestData;

        $limit = $filter['page_size'] = 100; // 每次固定查询条数 尽量不要太大，减少数据库查询压力

        // 订单总数
        $total = $orderExportService->exportOrderTotal($filter);

        /* page 总数 */
        $page_count = (!empty($total) && $total > 0) ? ceil($total / $limit) : 0;
        $page_count = intval($page_count);// 获取订单总页数

        /**
         * 导出当前分页订单csv文件
         */
        if ($this->requestData['field_name']) {
            // 需要导出字段 须和查询数据里的字段名保持一致
            $fields = BaseRepository::getExplode($this->requestData['field_name']);
            // 金额类型字段
            $num = [
                'goods_price',
                'goods_number',
                'goods_amount',
                'cost_price',
                'tax',
                'shipping_fee',
                'insure_fee',
                'pay_fee',
                'rate_fee',
                'total_fee',
                'discount',
                'total_fee_order',
                'bonus',
                'coupons',
                'value_card',
                'money_paid',
                'order_amount',
                'total_cost_price',
                'drp_money',
                'surplus',
                'integral',
                'integral_money'
            ];

            $except_merge_column = ['goods_name', 'goods_attr', 'goods_sn', 'goods_price', 'goods_number', 'cost_price', 'drp_money'];

            //供应链
            if (file_exists(SUPPLIERS)) {
                $except_merge_column = BaseRepository::getArrayPush($except_merge_column, 'suppliers_name');
            }

            $head = [];
            $except_merge = [];
            foreach ($fields as $val) {
                $arr = [];
                $arr['column_name'] = trans('admin/order_export.' . $val);
                if (in_array($val, $num)) {
                    $arr['num'] = 1;
                }
                $head[] = $arr;

                if (in_array($val, $except_merge_column)) {
                    $except_merge[] = $arr['column_name'];
                }
            }

            // 初始化导出文件
            $dir = 'export/order/' . $this->requestData['admin_id'] ?? 0;
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
                'savePath' => Storage::path($dir),
                'except_merge_column' => $except_merge, // 不进行单元格合并的字段 对应 column_name
            ];

            /**
             * 循环处理订单数据
             */
            $yield_order = null;

            for ($page = 1; $page <= $page_count; $page++) {
                $start = ($page - 1) * $limit;
                $order[] = $orderExportService->exportOrderList($filter, $start, $limit);// 获取订单数组
                $yield_order = self::yieldData($order);
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
                            'type' => 'order',
                            'file_name' => $out_title,
                            'file_type' => 'xls',
                            'download_params' => json_encode($this->requestData),
                            'created_at' => Carbon::now(),
                        ]);
                    }

                    // 导出 Excel 文件
                    $data = array_flatten($data, 1);// 订单多商品数据合并
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
