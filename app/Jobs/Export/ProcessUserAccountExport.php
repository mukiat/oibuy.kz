<?php

namespace App\Jobs\Export;

use App\Dsctrait\Exportable;
use App\Extensions\File;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
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
 * 导出充值或提现申请
 * Class ProcessUserAccountExport
 * @package App\Jobs\Export
 */
class ProcessUserAccountExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Exportable;

    /**
     * @var array
     */
    private $requestData;

    /**
     * Create a new job instance.
     *
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
     */
    public function handle()
    {
        $filter = $this->requestData;

        $data = $this->accountList($filter);
        $page_count = isset($data['page_count']) ? intval($data['page_count']) : 0;//总页数

        /**
         * 循环处理数据
         */
        $exportData = $data['list'];
        for ($page = 2; $page <= $page_count; $page++) {
            $filter['page'] = $page;
            $data = $this->accountList($filter);
            $exportData = array_merge($exportData, $data['list']);
        }

        $this->exportFile($exportData);
    }

    /**
     * @param $request
     * @return array
     * @throws Exception
     */
    private function accountList($request)
    {
        /* 过滤列表 */
        $filter['user_id'] = intval($request['user_id']);
        $filter['keywords'] = empty($request['keywords']) ? '' : trim($request['keywords']);
        if (isset($request['is_ajax']) && $request['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['process_type'] = isset($request['process_type']) ? intval($request['process_type']) : 0;
        $filter['pay_id'] = empty($request['pay_id']) ? 0 : intval($request['pay_id']);
        $filter['is_paid'] = isset($request['is_paid']) ? intval($request['is_paid']) : -1;
        $filter['sort_by'] = empty($request['sort_by']) ? 'add_time' : trim($request['sort_by']);
        $filter['sort_order'] = empty($request['sort_order']) ? 'DESC' : trim($request['sort_order']);
        $filter['start_date'] = empty($request['start_date']) ? '' : TimeRepository::getLocalStrtoTime($request['start_date']);
        $filter['end_date'] = empty($request['end_date']) ? '' : (TimeRepository::getLocalStrtoTime($request['end_date']) + 86400);
        $filter['add_start_date'] = empty($request['add_start_date']) ? '' : (strpos($request['add_start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($request['add_start_date']) : $request['add_start_date']);
        $filter['add_end_date'] = empty($request['add_end_date']) ? '' : (strpos($request['add_end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($request['add_end_date']) : $request['add_end_date']);

        $where = " 1";
        if ($filter['user_id'] > 0) {
            $where .= " AND user_id = '{$filter['user_id']}' ";
        }
        if ($filter['process_type'] != -1) {
            $where .= " AND process_type = '{$filter['process_type']}' ";
        } else {
            $where .= " AND process_type " . db_create_in([SURPLUS_SAVE, SURPLUS_RETURN]);
        }
        if ($filter['pay_id'] > 0) {
            $where .= " AND pay_id = '{$filter['pay_id']}' ";
        }
        if ($filter['is_paid'] != -1) {
            $where .= " AND is_paid = '{$filter['is_paid']}' ";
        }

        if ($filter['add_start_date']) {
            $where .= " AND add_time >= '{$filter['add_start_date']}'";
        }
        if ($filter['add_end_date']) {
            $where .= " AND add_time <= '{$filter['add_end_date']}'";
        }

        if ($filter['keywords']) {
            $keywords = e($filter['keywords']);

            $user_id = Users::query()->select('user_id')->where(function ($query) use ($keywords) {
                $query->where('user_name', 'like', '%' . $keywords . '%')
                    ->orWhere('email', 'like', '%' . $keywords . '%')
                    ->orWhere('mobile_phone', 'like', '%' . $keywords . '%');
            });
            $user_id = $user_id->pluck('user_id');
            $user_id = BaseRepository::getToArray($user_id);

            if (!empty($user_id)) {
                $where .= " AND user_id IN (". implode(',', $user_id) .")";
            }
        }

        /* 　时间过滤　 */
        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $where .= "AND paid_time >= " . $filter['start_date'] . " AND paid_time < '" . $filter['end_date'] . "'";
        }

        $filter['record_count'] = DB::table('user_account')->whereRaw($where)->count();

        /* 分页大小 */
        $filter['page_size'] = $request['page_size'];
        $filter['page_count'] = ceil($filter['record_count'] / $request['page_size']);
        $filter['start'] = ($request['page'] - 1) * $filter['page_size'];

        /* 查询数据 */
        $filter['keywords'] = stripslashes($filter['keywords']);

        $collection = DB::table('user_account')->select('*')->whereRaw($where)
            ->join('user_account_fields as uaf', 'user_account.id', '=', 'uaf.account_id')
            ->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size'])
            ->get();
        $collection = BaseRepository::getToArray($collection);

        $list = [];
        if ($collection) {
            $user_id = BaseRepository::getKeyPluck($collection, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);
            $realList = UserDataHandleService::getUsersRealDataList($user_id, ['user_id', 'real_name']);

            foreach ($collection as $key => $value) {
                $list[$key] = collect($value)->toArray();
                $user = $userList[$list[$key]['user_id']] ?? [];
                $real = $realList[$list[$key]['user_id']] ?? [];

                $list[$key]['user_name'] = $user['user_name'] ?? '';

                if (empty($list[$key]['real_name'])) {
                    // 银行卡提现 取实名信息
                    $list[$key]['withdraw_type'] = $list[$key]['withdraw_type'] ?? 0;
                    if ($list[$key]['withdraw_type'] == 0) {
                        $list[$key]['real_name'] = $real['real_name'] ?? '';
                    } else {
                        $list[$key]['real_name'] = $user['user_name'];
                    }
                }

                $list[$key]['is_paid'] = $list[$key]['is_paid'] ? trans('admin/user_account.completed') : '';
                $list[$key]['surplus_amount'] = app(DscRepository::class)->getPriceFormat(abs($list[$key]['amount']), false);
                $list[$key]['add_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $list[$key]['add_time']);
                $list[$key]['process_type_name'] = trans('admin::user_account.surplus_type_' . $list[$key]['process_type']);
                $list[$key]['withdraw_type_name'] = trans('admin::user_account.withdraw_type_' . $list[$key]['withdraw_type']);
            }
        }

        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * @param $exportData
     */
    private function exportFile($exportData)
    {
        $head = [
            ['column_name' => trans('admin/user_account.id')],
            ['column_name' => trans('admin/user_account.user_name')],
            ['column_name' => trans('admin/user_account.real_name')],
            ['column_name' => trans('admin/user_account.add_time')],
            ['column_name' => trans('admin/user_account.type')],
            ['column_name' => trans('admin/user_account.withdraw_mothed')],
            ['column_name' => trans('admin/user_account.amount'), 'num' => 1],
            ['column_name' => trans('admin/user_account.deposit_fee'), 'num' => 1],
            ['column_name' => trans('admin/user_account.payment')],
            ['column_name' => trans('admin/user_account.user_note')],
            ['column_name' => trans('admin/user_account.is_paid')],
            ['column_name' => trans('admin/user_account.admin_user')],
            ['column_name' => trans('admin/user_account.admin_note')],
        ];

        $fields = [
            'id', // 编号
            'user_name', // 会员名称
            'real_name', // 真实姓名
            'add_date', // 操作日期
            'process_type_name', // 类型
            'withdraw_type_name', // 提现类型
            'amount', // 金额
            'deposit_fee', // 手续费
            'payment', // 支付方式
            'user_note', // 会员描述
            'is_paid', // 到款状态
            'admin_user', // 操作员
            'admin_note', // 管理员备注
        ];

        if ($this->requestData['process_type'] == 0) {
            unset($head[2]);unset($fields[2]);
            unset($head[5]);unset($fields[5]);
        }

        if ($this->requestData['process_type'] == 1) {
            unset($head[8]);unset($fields[8]); // 提现不显示支付方式数据
        }

        // 初始化导出文件
        $dir = 'export/user_account/' . $this->requestData['admin_id'] ?? 0;
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $out_title = $this->requestData['file_name'];

        $options = [
            'savePath' => Storage::path($dir),
        ];

        // 导出 Excel 文件
        $this->fileWrite($out_title, $head, $fields, $exportData, $options);

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
