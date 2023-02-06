<?php

namespace App\Services\Oss;

use App\Models\OssConfigure;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class OssConfigureManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 返回bucket列表数据
     *
     * @return array
     */
    public function bucketList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'bucketList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['record_count'] = OssConfigure::count();
        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = OssConfigure::orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $bucket_list = BaseRepository::getToArrayGet($res);

        $count = count($bucket_list);

        for ($i = 0; $i < $count; $i++) {
            $regional = substr($bucket_list[$i]['regional'], 0, 2);

            $http = $this->dscRepository->dscHttp();

            $oss_network = config('shop.oss_network');
            if ($oss_network == 2) {
                $outside_site = $http . $bucket_list[$i]['bucket'] . ".oss-accelerate.aliyuncs.com";
                $inside_site = $outside_site;
            } else {
                if ($regional == 'us' || $regional == 'ap') {
                    $outside_site = $http . $bucket_list[$i]['bucket'] . ".oss-" . $bucket_list[$i]['regional'] . ".aliyuncs.com";
                    $inside_site = $http . $bucket_list[$i]['bucket'] . ".oss-" . $bucket_list[$i]['regional'] . "-internal.aliyuncs.com";
                } else {
                    $outside_site = $http . $bucket_list[$i]['bucket'] . ".oss-cn-" . $bucket_list[$i]['regional'] . ".aliyuncs.com";
                    $inside_site = $http . $bucket_list[$i]['bucket'] . ".oss-cn-" . $bucket_list[$i]['regional'] . "-internal.aliyuncs.com";
                }
            }

            $bucket_list[$i]['outside_site'] = $outside_site;
            $bucket_list[$i]['inside_site'] = $inside_site;

            if ($bucket_list[$i]['regional'] == 'shanghai') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_1'];
            } elseif ($bucket_list[$i]['regional'] == 'hangzhou') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_2'];
            } elseif ($bucket_list[$i]['regional'] == 'shenzhen') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_3'];
            } elseif ($bucket_list[$i]['regional'] == 'beijing') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_4'];
            } elseif ($bucket_list[$i]['regional'] == 'qingdao') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_5'];
            } elseif ($bucket_list[$i]['regional'] == 'hongkong') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_6'];
            } elseif ($bucket_list[$i]['regional'] == 'us-west-1') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_7'];
            } elseif ($bucket_list[$i]['regional'] == 'ap-southeast-1') {
                $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG']['regional_name_8'];
            }
        }

        $arr = ['bucket_list' => $bucket_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
