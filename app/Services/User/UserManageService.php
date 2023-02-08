<?php

namespace App\Services\User;

use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\User\UsersRepository;
use App\Services\Merchant\MerchantDataHandleService;

class UserManageService
{
    protected $commonRepository;
    protected $dscRepository;

    public function __construct(
        CommonRepository $commonRepository,
        DscRepository $dscRepository
    )
    {
        $this->commonRepository = $commonRepository;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 检查指定用户是否存
     *
     * @param array $field
     * @param array $val
     * @param int $user_id
     * @return mixed
     */
    public function checkFieldName($field = [], $val = [], $user_id = 0)
    {
        $row = Users::whereRaw(1);

        if ($user_id > 0) {
            $row = $row->where('user_id', '<>', $user_id);
        }

        foreach ($field as $k => $v) {
            $row = $row->where($v, $val[$k]);
        }

        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }

    /**
     * 判断用户是否存在[用户名或手机号]
     *
     * @param string $name
     * @param int $user_id
     * @return mixed
     */
    public function exitUser($name = '', $user_id = 0)
    {
        $name = e($name);

        /* 是否手机 */
        $is_phone = is_phone_number($name);

        $fieldVal = [
            $name
        ];

        $useRow = Users::whereRaw(1);

        $is_name = 1;
        if ($is_phone) {
            $useRow = $useRow->where('mobile_phone', $name);

            $field = [
                'user_name'
            ];
        } else {
            $is_name = 2;

            $useRow = $useRow->where('user_name', $name);

            $field = [
                'mobile_phone'
            ];
        }

        if ($user_id > 0) {
            $useRow = $useRow->where('user_id', '<>', $user_id);
        }

        $useRow = BaseRepository::getToArrayFirst($useRow);

        if (empty($useRow)) {
            if ($is_name > 0 && $field) {
                $useRow = $this->checkFieldName($field, $fieldVal, $user_id);
            }
        }

        return $useRow;
    }

    public function user_list($adminru = [])
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_users';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['rank'] = empty($_REQUEST['rank']) ? 0 : intval($_REQUEST['rank']);
        $filter['rank_id'] = empty($_REQUEST['rank_id']) ? 0 : intval($_REQUEST['rank_id']);
        $filter['pay_points_gt'] = empty($_REQUEST['pay_points_gt']) ? 0 : intval($_REQUEST['pay_points_gt']);
        $filter['pay_points_lt'] = empty($_REQUEST['pay_points_lt']) ? 0 : intval($_REQUEST['pay_points_lt']);
        $filter['mobile_phone'] = empty($_REQUEST['mobile_phone']) ? 0 : addslashes($_REQUEST['mobile_phone']);
        $filter['email'] = empty($_REQUEST['email']) ? 0 : addslashes($_REQUEST['email']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'user_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['checkboxes'] = empty($_REQUEST['checkboxes']) ? '' : $_REQUEST['checkboxes'];

        // 店铺查询 start
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? e($_REQUEST['store_keyword']) : '';
        $filter['store_type'] = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;
        $filter['ru_id'] = $adminru['ru_id'] ?? 0;
        // 店铺查询 end

        // 总数
        $filter['record_count'] = UsersRepository::instance()->user_total($filter);

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $start = $filter['start'] ?? 0;
        $limit = $filter['page_size'] ?? 15;
        $user_list = UsersRepository::instance()->user_list($filter, $start, $limit);

        if (!empty($user_list)) {

            $ru_id = BaseRepository::getKeyPluck($user_list, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id, 1);

            $user_rank = BaseRepository::getKeyPluck($user_list, 'user_rank');
            $rank_list = UsersRepository::instance()->setModel('App\Models\UserRank')->whereInExtend('rank_id', $user_rank, ['rank_name']);

            foreach ($user_list as $key => $value) {

                $user_list[$key]['ru_name'] = $merchantList[$value['user_id']] ?: trans('admin::users.mall_user');
                $user_list[$key]['rank_name'] = $rank_list[$value['user_rank']]['rank_name'] ?: trans('admin::users.not_rank');

                $user_list[$key]['reg_time'] = TimeRepository::getLocalDate(config('shop.date_format'), $value['reg_time']);
                $user_list[$key]['user_picture'] = $this->dscRepository->getImagePath($value['user_picture']);

                if (config('shop.show_mobile') == 0) {
                    $user_list[$key]['mobile_phone'] = $this->dscRepository->stringToStar($value['mobile_phone']);
                    $user_list[$key]['user_name'] = $this->dscRepository->stringToStar($value['user_name']);
                    $user_list[$key]['email'] = $this->dscRepository->stringToStar($value['email']);
                }
            }
        }

        return ['user_list' => $user_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
