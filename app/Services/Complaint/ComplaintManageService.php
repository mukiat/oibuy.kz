<?php

namespace App\Services\Complaint;

use App\Models\AdminUser;
use App\Models\AppealImg;
use App\Models\Complaint;
use App\Models\ComplaintImg;
use App\Models\ComplainTitle;
use App\Models\ComplaintTalk;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 后台用户评论
 * Class Comment
 *
 * @package App\Services
 */
class ComplaintManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取投诉类型列表
     *
     * @access  public
     * @return  array
     */
    public function get_complaint_title_list()
    {
        /* 初始化分页参数 */
        $filter = [];
        /* 查询记录总数，计算分页数 */
        $filter['record_count'] = ComplainTitle::count();
        $filter = page_and_size($filter);
        /* 查询记录 */
        $list = BaseRepository::getToArrayGet(ComplainTitle::orderBy('title_id', 'DESC')->offset($filter['start'])->limit($filter['page_size']));
        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    //获取纠纷列表
    public function get_complaint_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_complaint_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $where = ' 1 ';
        /* 初始化分页参数 */
        $filter = [];
        $filter['handle_type'] = !empty($_REQUEST['handle_type']) ? $_REQUEST['handle_type'] : '-1';
        $filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        $adminru = get_admin_ru_id();
        if ($adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end

        if ($filter['merchant_id'] > 0) {
            $where .= " AND ru_id = '". $filter['merchant_id'] ."' ";
        }

        if ($filter['keywords']) {
            $where .= " AND (user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%' OR order_sn LIKE '%" . mysql_like_quote($filter['keywords']) . "%')";
        }
        if ($filter['handle_type'] != '-1') {
            $handle_type = $filter['handle_type'];
            if ($filter['handle_type'] == 6) {
                $where .= " AND complaint_state <> 4 ";
            } else {
                if ($filter['handle_type'] == 5) {
                    $handle_type = 0;
                }
                $where .= " AND complaint_state = '" . $handle_type . "'";
            }
        }

        $res = Complaint::whereRaw($where)->whereHasIn('getOrder');

        /* 查询记录总数，计算分页数 */
        $filter['record_count'] = $res->count('complaint_id');
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->offset($filter['start'])
            ->limit($filter['page_size'])
            ->orderBy('add_time', 'DESC');
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $rows) {
                $rows['admin_name'] = AdminUser::where('user_id', $rows['admin_id'])->value('user_name');
                if ($rows['title_id'] > 0) {
                    $rows['title_name'] = ComplainTitle::where('title_id', $rows['title_id'])->value('title_name');
                }
                if ($rows['add_time'] > 0) {
                    $rows['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $rows['add_time']);
                }

                //获取举报图片列表
                $img_list = BaseRepository::getToArrayGet((ComplaintImg::where('complaint_id', $rows['complaint_id'])->orderBy('img_id', 'DESC')));
                if (!empty($img_list)) {
                    foreach ($img_list as $k => $v) {
                        $img_list[$k]['img_file'] = $this->dscRepository->getImagePath($v['img_file']);
                    }
                }
                $rows['img_list'] = $img_list;

                //申诉图片列表
                $appeal_img = BaseRepository::getToArrayGet(AppealImg::where('complaint_id', $rows['complaint_id'])->orderBy('img_id', 'DESC'));
                if (!empty($appeal_img)) {
                    foreach ($appeal_img as $k => $v) {
                        $appeal_img[$k]['img_file'] = $this->dscRepository->getImagePath($v['img_file']);
                    }
                }
                $rows['appeal_img'] = $appeal_img;
                $rows['has_talk'] = 0;
                //获取是否存在未读信息
                if ($rows['complaint_state'] > 1) {
                    $talk_list = BaseRepository::getToArrayGet(ComplaintTalk::where('complaint_id', $rows['complaint_id'])->orderBy('talk_time', 'DESC'));
                    if ($talk_list) {
                        foreach ($talk_list as $k => $v) {
                            if ($v['view_state']) {
                                $view_state = explode(',', $v['view_state']);
                                if (!in_array('admin', $view_state)) {
                                    $rows['has_talk'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                    $rows['user_name'] = $this->dscRepository->stringToStar($rows['user_name']);
                }

                $arr[] = $rows;
            }
        }

        return ['list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
