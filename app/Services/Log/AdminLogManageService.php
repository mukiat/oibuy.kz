<?php

namespace App\Services\Log;

use App\Models\AdminLog;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;

class AdminLogManageService
{
    protected $commonManageService;
    protected $dscRepository;

    public function __construct(
        CommonManageService $commonManageService,
        DscRepository $dscRepository
    ) {
        $this->commonManageService = $commonManageService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 查询IP地址列表
     *
     * @return array
     */
    public function getLogIp()
    {
        $res = AdminLog::whereRaw(1);
        $res = BaseRepository::getToArrayGet($res);
        $res = BaseRepository::getKeyPluck($res, 'ip_address');

        $ip_list = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $ip_list[$row] = $row;
            }
        }

        return $ip_list;
    }

    /* 获取管理员操作记录 */
    public function getAdminLogs()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getAdminLogs';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $seller = $this->commonManageService->getAdminIdSeller();

        $user_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $admin_ip = isset($_REQUEST['ip']) && !empty($_REQUEST['ip']) ? $_REQUEST['ip'] : '';

        $filter = array();
        $filter['sort_by'] = !empty($_REQUEST['sort_by']) ? trim($_REQUEST['sort_by']) : 'log_id';
        $filter['sort_order'] = !empty($_REQUEST['sort_order']) ? trim($_REQUEST['sort_order']) : 'DESC';
        $filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $row = AdminLog::whereRaw(1);

        if (!empty($user_id)) {
            $row = $row->where('user_id', $user_id);
        } elseif (!empty($admin_ip)) {
            $row = $row->where('ip_address', $admin_ip);
        }

        /* 操作时间 */
        if (!empty($filter['start_time']) || !empty($filter['end_time'])) {
            $start_time = TimeRepository::getLocalStrtoTime($filter['start_time']);
            $end_time = TimeRepository::getLocalStrtoTime($filter['end_time']);
            $row = $row->where('log_time', '>', $start_time)
                ->where('log_time', '<', $end_time);
        }

        $where = [
            'filter' => $filter,
            'seller' => $seller
        ];
        $row = $row->whereHasIn('adminUser', function ($query) use ($where) {
            if ($where['seller']['ru_id'] > 0 && $where['seller']['suppliers_id'] == 0) {
                $query = $query->where('suppliers_id', 0)->where('ru_id', $where['seller']['ru_id']);
            }
            if ($where['seller']['ru_id'] > 0 && $where['seller']['suppliers_id'] > 0) {
                $query = $query->where('suppliers_id', $where['seller']['suppliers_id'])->where('ru_id', $where['seller']['ru_id']);
            }

            if ($where['filter']['keywords']) {
                $keywords = mysql_like_quote($where['filter']['keywords']);
                $query->where('user_name', 'like', '%' . $keywords . '%');
            }
        });

        $res = $record_count = $row;

        /* 获得总记录数据 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获取管理员日志记录 */
        $res = $res->with([
            'adminUser'
        ]);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            foreach ($res as $key => $rows) {
                $rows['user_name'] = $rows['admin_user']['user_name'] ?? '';
                $rows['log_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $rows['log_time']);

                $list[] = $rows;
            }
        }

        return [
            'list' => $list,
            'filter' => $filter,
            'page_count' => $filter['page_count'],
            'record_count' => $filter['record_count']
        ];
    }

    /**
     * 批量删除
     * 按日期删除日志
     *
     * @param int $log_date
     * @return mixed
     */
    public function getAdminLogAatchDrop($log_date = 0)
    {
        $time = TimeRepository::getGmTime();

        $del = AdminLog::whereRaw(1);

        $log_time = 0;
        if ($log_date == 1) {
            $log_time = $time - (3600 * 24 * 7);
        } elseif ($log_date == 2) {
            $log_time = $time - (3600 * 24 * 30);
        } elseif ($log_date == 3) {
            $log_time = $time - (3600 * 24 * 90);
        } elseif ($log_date == 4) {
            $log_time = $time - (3600 * 24 * 180);
        } elseif ($log_date == 5) {
            $log_time = $time - (3600 * 24 * 365);
        }

        if ($log_time) {
            return $del->where('log_time', '<=', $log_time)->delete();
        } else {
            return false;
        }
    }

    /**
     * 删除日志
     *
     * @param string $log_id
     * @return mixed
     */
    public function getAdminLogIdDel($log_id = '')
    {
        $log_id = BaseRepository::getExplode($log_id);
        if ($log_id) {
            if (is_array($log_id)) {
                $del = AdminLog::whereIn('log_id', $log_id);
            } else {
                $del = AdminLog::where('log_id', $log_id);
            }

            return $del->delete();
        }

        return false;
    }
}
