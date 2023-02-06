<?php

namespace App\Services\Message;

use App\Models\AdminMessage;
use App\Modules\Admin\Services\AdminUser\AdminUserDataHandleService;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Commission\CommissionService;
use App\Services\Common\CommonManageService;
use App\Services\Order\OrderService;

class MessageManageService
{
    protected $orderService;
    protected $commissionService;
    protected $dscRepository;
    protected $commonManageService;
    protected $admin_id = 0;

    public function __construct(
        OrderService $orderService,
        CommissionService $commissionService,
        DscRepository $dscRepository,
        CommonManageService $commonManageService
    )
    {
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->dscRepository = $dscRepository;
        $this->commonManageService = $commonManageService;
    }

    /**
     * 获取管理员留言列表
     *
     * @return array
     */
    public function getMessageList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getMessageList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 后台管理员ID */
        $this->admin_id = $this->commonManageService->getAdminId();

        /* 查询条件 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sent_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['msg_type'] = empty($_REQUEST['msg_type']) ? 0 : intval($_REQUEST['msg_type']);

        /* 查询条件 */
        $res = AdminMessage::whereRaw(1);
        switch ($filter['msg_type']) {
            case 1:
                // 所有收到的留言
                $res = $res->where('receiver_id', $this->admin_id);
                break;
            case 2:
                // 所有发送的留言
                $res = $res->where('sender_id', $this->admin_id)->where('deleted', 0);
                break;
            case 3:
                // 未阅读的留言
                $res = $res->where('readed', 0)->where('deleted', 0)->where(function ($query) {
                    $query->where('sender_id', $this->admin_id)->orWhere('receiver_id', $this->admin_id);
                });
                break;
            case 4:
                // 已阅读的留言
                $res = $res->where('readed', 1)->where('deleted', 0)->where(function ($query) {
                    $query->where('sender_id', $this->admin_id)->orWhere('receiver_id', $this->admin_id);
                });
                break;
            default:
                $res = $res->where(function ($query) {
                    $query->where('sender_id', $this->admin_id)->orWhere('receiver_id', $this->admin_id);
                })->where('deleted', 0);
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->with(['getAdminUser' => function ($query) {
            $query->select('user_id', 'user_name');
        }]);
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $sender_id = BaseRepository::getKeyPluck($row, 'sender_id');
            $receiver_id = BaseRepository::getKeyPluck($row, 'receiver_id');

            $userIdList = BaseRepository::getArrayMerge($sender_id, $receiver_id);
            $adminList = AdminUserDataHandleService::getAdminUserDataList($userIdList, ['user_id', 'user_name']);

            foreach ($row as $key => $val) {
                $row[$key]['sender_name'] = $adminList[$val['sender_id']]['user_name'] ?? '';
                $row[$key]['receiver_name'] = $adminList[$val['receiver_id']]['user_name'] ?? '';

                $row[$key]['sent_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['sent_time']);
                $row[$key]['read_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['read_time']);
            }
        }

        $arr = ['item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
