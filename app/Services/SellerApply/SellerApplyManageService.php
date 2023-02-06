<?php

namespace App\Services\SellerApply;

use App\Models\Payment;
use App\Models\SellerApplyInfo;
use App\Models\SellerGrade;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class SellerApplyManageService
{
    protected $commonManageService;
    protected $merchantCommonService;

    public function __construct(
        CommonManageService $commonManageService,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->commonManageService = $commonManageService;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 下载列表
     *
     * @param $result
     * @return bool|string
     */
    public function downloadApplyList($result)
    {
        if (empty($result)) {
            return StrRepository::i($GLOBALS['_LANG']['not_fuhe_date']);
        }

        $data = StrRepository::i($GLOBALS['_LANG']['download_apply_notic'] . "\n");
        $count = count($result);
        for ($i = 0; $i < $count; $i++) {
            $apply_sn = StrRepository::i('#' . $result[$i]['apply_sn']); //订单号前加'#',避免被四舍五入 by wu
            $shop_name = StrRepository::i($result[$i]['shop_name']);
            $grade_name = StrRepository::i($result[$i]['grade_name']);
            $total_amount = StrRepository::i($result[$i]['total_amount']);
            $refund_price = StrRepository::i($result[$i]['refund_price']);
            $pay_name = StrRepository::i($result[$i]['pay_name']);
            $add_time = StrRepository::i($result[$i]['add_time']);
            $status_paid = StrRepository::i($result[$i]['status_paid'] . "，" . $result[$i]['status_apply']);
            $data .= $apply_sn . ',' . $shop_name . ',' . $grade_name . ',' .
                $total_amount . ',' . $refund_price . ',' . $pay_name . ',' .
                $add_time . ',' . $status_paid . "\n";
        }
        return $data;
    }

    /**
     * 分页
     *
     * @return array
     */
    public function getPzdList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getPzdList';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /*筛选信息*/
        $filter['apply_sn'] = empty($_REQUEST['apply_sn']) ? '' : trim($_REQUEST['apply_sn']);
        $grade_name = empty($_REQUEST['grade_name']) ? '' : $_REQUEST['grade_name'];
        $filter['pay_starts'] = isset($_REQUEST['pay_starts']) ? intval($_REQUEST['pay_starts']) : -1;
        $filter['apply_starts'] = isset($_REQUEST['apply_starts']) ? intval($_REQUEST['apply_starts']) : -1;
        $filter['valid'] = isset($_REQUEST['valid']) ? intval($_REQUEST['valid']) : -1;
        $filter['ru_id'] = isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
        if ($grade_name) {
            $filter['grade_id'] = SellerGrade::where('grade_name', 'LIKE', '%' . $grade_name . '%')->value('id');
            $filter['grade_id'] = $filter['grade_id'] ? $filter['grade_id'] : 0;
        }

        $filter['sort_by'] = addslashes(trim(request()->input('sort_by', 'add_time')));
        $filter['sort_order'] = addslashes(trim(request()->input('sort_order', 'DESC')));

        /*拼装筛选*/
        $res = SellerApplyInfo::whereRaw(1);
        if ($filter['apply_sn']) {
            $res = $res->where('apply_sn', 'LIKE', '%' . mysql_like_quote($filter['apply_sn']) . '%');
        }
        if (isset($filter['grade_id']) && !empty($filter['grade_id'])) {
            $res = $res->where('grade_id', $filter['grade_id']);
        }
        if ($filter['pay_starts'] != -1) {
            $res = $res->where('pay_status', $filter['pay_status']);
        }
        if ($filter['apply_starts'] != -1) {
            $res = $res->where('apply_status', $filter['apply_starts']);
        }
        if ($filter['ru_id'] > 0) {
            $res = $res->where('ru_id', $filter['ru_id']);
        }
        if ($filter['valid'] != -1) {
            $res = $res->where('valid', $filter['valid']);
        }

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 获活动数据 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $k => $v) {
                $row[$k]['shop_name'] = $merchantList[$v['ru_id']]['shop_name'] ?? '';

                $row[$k]['grade_name'] = SellerGrade::where('id', $v['grade_id'])->value('grade_name');
                $row[$k]['grade_name'] = $row[$k]['grade_name'] ? $row[$k]['grade_name'] : '';

                $row[$k]['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['add_time']);
                if ($v['pay_id'] > 0) {
                    $row[$k]['pay_name'] = Payment::where('pay_id', $v['pay_id'])->value('pay_name');
                    $row[$k]['pay_name'] = $row[$k]['pay_name'] ? $row[$k]['pay_name'] : '';
                }

                /*判断支付状态*/
                switch ($v['pay_status']) {
                    case 0:
                        $row[$k]['status_paid'] = $GLOBALS['_LANG']['no_paid'];
                        break;
                    case 1:
                        $row[$k]['status_paid'] = $GLOBALS['_LANG']['paid'];
                        break;
                    case 2:
                        $row[$k]['status_paid'] = $GLOBALS['_LANG']['return_paid'];
                        break;
                }
                /*判断申请状态*/
                switch ($v['apply_status']) {
                    case '0':
                        $row[$k]['status_apply'] = $GLOBALS['_LANG']['not_audited'];
                        break;
                    case '1':
                        $row[$k]['status_apply'] = $GLOBALS['_LANG']['audited_adopt'];
                        break;
                    case '2':
                        $row[$k]['status_apply'] = $GLOBALS['_LANG']['audited_not_adopt'];
                        break;
                    case '3':
                        $row[$k]['status_apply'] = "<span style='color:red'>" . $GLOBALS['_LANG']['invalid'] . "</span>";
                        break;
                }
            }
        }

        $arr = ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
