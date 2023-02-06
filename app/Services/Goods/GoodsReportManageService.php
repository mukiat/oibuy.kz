<?php

namespace App\Services\Goods;

use App\Models\AdminUser;
use App\Models\Goods;
use App\Models\GoodsReport;
use App\Models\GoodsReportImg;
use App\Models\GoodsReportTitle;
use App\Models\GoodsReportType;
use App\Modules\Admin\Services\AdminUser\AdminUserDataHandleService;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserDataHandleService;

class GoodsReportManageService
{
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }
    
    /**
     * 投诉列表
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsReport()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGoodsReport';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $res = GoodsReport::whereRaw(1);
        /* 初始化分页参数 */
        $filter = [];
        $filter['handle_type'] = !empty($_REQUEST['handle_type']) ? $_REQUEST['handle_type'] : '-1';
        $filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';

        if ($filter['keywords']) {
            $keywords = $filter['keywords'];
            $res = $res->where(function ($query) use ($keywords) {
                $query->whereHasIn('getUsers', function ($query) use ($keywords) {
                    $query->where('user_name', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('nick_name', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('goods_name', 'LIKE', '%' . $keywords . '%');
                });
            });
        }
        if ($filter['handle_type'] != '-1') {
            if ($filter['handle_type'] == 6) {
                $res = $res->where('report_state', 0);
            } else {
                $res = $res->where('report_state', '>', 0);
            }
        }
        /* 查询记录总数，计算分页数 */

        $filter['record_count'] = $res->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询记录 */
        $res = $res->with(['getUsers' => function ($query) {
            $query->select('user_id', 'user_name');
        }]);

        $res = $res->orderBy('add_time', 'DESC')->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $user_id = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id']);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $admin_id = BaseRepository::getKeyPluck($res, 'admin_id');
            $adminList = AdminUserDataHandleService::getAdminUserDataList($admin_id, ['user_id', 'user_name']);

            $report_id = BaseRepository::getKeyPluck($res, 'report_id');
            $goodsReportImgList = GoodsReportDataHandleService::getGoodsReportImgReportIdDataList($report_id);

            foreach ($res as $rows) {

                $user = $userList[$rows['user_id']] ?? [];

                $rows['user_name'] = $user['user_name'] ?? '';

                if (config('shop.show_mobile') == 0) {
                    $rows['user_name'] = $this->dscRepository->stringToStar($rows['user_name']);
                }

                $rows['goods_image'] = $this->dscRepository->getImagePath($rows['goods_image']);
                $rows['admin_name'] = $adminList[$rows['admin_id']]['user_name'] ?? '';

                if ($rows['title_id'] > 0) {
                    $rows['title_name'] = GoodsReportTitle::where('title_id', $rows['title_id'])->value('title_name');
                    $rows['title_name'] = $rows['title_name'] ? $rows['title_name'] : '';
                }

                if ($rows['type_id'] > 0) {
                    $rows['type_name'] = GoodsReportType::where('type_id', $rows['type_id'])->value('type_name');
                    $rows['type_name'] = $rows['type_name'] ? $rows['type_name'] : '';
                }

                if ($rows['add_time'] > 0) {
                    $rows['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $rows['add_time']);
                }

                if ($rows['handle_time'] > 0) {
                    $rows['handle_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $rows['handle_time']);
                }

                $rows['url'] = $this->dscRepository->buildUri('goods', ['gid' => $rows['goods_id']], $rows['goods_name']);

                $user_id = $goodsList[$rows['goods_id']]['user_id'] ?? 0;
                $rows['shop_name'] = $merchantList[$user_id]['shop_name'] ?? '';

                //获取举报图片列表
                $sql = [
                    'where' => [
                        [
                            'name' => 'report_id',
                            'value' => $rows['report_id']
                        ]
                    ]
                ];
                $img_list = BaseRepository::getArraySqlGet($goodsReportImgList, $sql);

                if (!empty($img_list)) {
                    foreach ($img_list as $k => $v) {
                        $img_list[$k]['img_file'] = $this->dscRepository->getImagePath($v['img_file']);
                    }
                }

                $rows['img_list'] = $img_list;
                $arr[] = $rows;
            }
        }

        return ['list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 商品举报类型
     *
     * @return array
     */
    public function getGoodsReportTypeList()
    {
        /* 初始化分页参数 */
        $filter = [];
        /* 查询记录总数，计算分页数 */
        $filter['record_count'] = GoodsReportType::count();
        $filter = page_and_size($filter);

        /* 查询记录 */
        $res = GoodsReportType::orderBy('type_id', 'DESC')
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $list = BaseRepository::getToArrayGet($res);

        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 商品举报主题
     *
     * @return array
     */
    public function getGoodsReportTitleList()
    {
        /* 初始化分页参数 */
        $filter = [];
        /* 查询记录总数，计算分页数 */
        $filter['record_count'] = GoodsReportTitle::count();
        $filter = page_and_size($filter);

        /* 查询记录 */

        $res = GoodsReportTitle::orderBy('type_id', 'DESC')->offset($filter['start'])->limit($filter['page_size']);
        $list = BaseRepository::getToArrayGet($res);

        if ($list) {
            foreach ($list as $k => $v) {
                if ($v['type_id'] > 0) {
                    $list[$k]['type_name'] = GoodsReportType::where('type_id', $v['type_id'])->value('type_name');
                    $list[$k]['type_name'] = $list[$k]['type_name'] ? $list[$k]['type_name'] : '';
                }
            }
        }
        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
