<?php

namespace App\Services\Other;

use App\Libraries\Image;
use App\Models\GalleryAlbum;
use App\Models\PicAlbum;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantDataHandleService;

class GalleryAlbumManageService
{
    protected $commonManageService;

    protected $dscRepository;

    public function __construct(
        CommonManageService $commonManageService,
        DscRepository $dscRepository
    )
    {
        $this->commonManageService = $commonManageService;

        $this->dscRepository = $dscRepository;
    }

    /**
     * 相册子分类
     *
     * @param int $album_id
     * @param int $type
     * @return bool|int|mixed|string
     */
    public function getGalleryChild($album_id = 0, $type = 0)
    {
        $seller = $this->commonManageService->getAdminIdSeller();

        $child_arr = '';
        if ($album_id > 0) {
            if ($type == 1) {
                $child_arr = $album_id;
            }

            $child_list = GalleryAlbum::where('parent_album_id', $album_id)
                ->where('suppliers_id', $seller['suppliers_id']);
            $child_list = BaseRepository::getToArrayGet($child_list);

            if (!empty($child_list)) {
                foreach ($child_list as $k => $v) {
                    $child_arr .= "," . $v['album_id'];
                    $child_tree = $this->getGalleryChild($v['album_id']);
                    if ($child_tree) {
                        $child_arr .= "," . $child_tree;
                    }
                }
            }
        }

        $child_arr = $this->dscRepository->delStrComma($child_arr);
        return $child_arr;
    }

    /**
     * 获取相册列表
     *
     * @return array
     */
    public function getGalleryAlbumList($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGalleryAlbumList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $seller = $this->commonManageService->getAdminIdSeller();

        /* 筛选信息 */
        $filter['keywords'] = isset($_REQUEST['keywords']) && !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';
        $filter['album_mame'] = isset($_REQUEST['album_mame']) && !empty($_REQUEST['album_mame']) ? trim($_REQUEST['album_mame']) : '';
        $filter['parent_id'] = isset($_REQUEST['parent_id']) && !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
        $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;

        /* 拼装筛选 */

        $row = GalleryAlbum::where('parent_album_id', $filter['parent_id'])
            ->where('ru_id', $ru_id)
            ->where('suppliers_id', $seller['suppliers_id']);

        if ($filter['album_mame']) {
            $row = $row->where('album_mame', 'like', '%' . mysql_like_quote($filter['album_mame']) . '%');
        }

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->withCount('picAlbum as pic_album_count');

        /* 获活动数据 */
        $res = $res->orderBy('sort_order', 'ASC');

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $k => $v) {
                $res[$k]['shop_name'] = $merchantList[$v['ru_id']]['shop_name'] ?? '';
                $res[$k]['album_cover'] = $v['album_cover'] ? $this->dscRepository->getImagePath($v['album_cover']) : '';
                $res[$k]['gallery_count'] = $v['pic_album_count'];
            }
        }

        $arr = [
            'album_list' => $res,
            'filter' => $filter,
            'page_count' => $filter['page_count'],
            'record_count' => $filter['record_count']
        ];

        return $arr;
    }

    /**
     * 上传文件
     *
     * @param $upload
     * @param string $file
     * @return bool|string
     */
    public function uploadAlbumFile($upload)
    {
        $file_dir = storage_public(DATA_DIR . "/gallery_album");
        if (!file_exists($file_dir)) {
            if (!make_dir($file_dir)) {
                /* 创建目录失败 */
                return false;
            }
        }

        $filename = Image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
        $path = storage_public(DATA_DIR . "/gallery_album/" . $filename);

        if (move_upload_file($upload['tmp_name'], $path)) {
            return DATA_DIR . "/gallery_album/" . $filename;
        } else {
            return false;
        }
    }

    /**
     * 获取相册图片
     *
     * @param int $album_id
     * @return array
     */
    public function getPicAlbumList($album_id = 0)
    {
        $filter['album_id'] = isset($_REQUEST['album_id']) && !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : $album_id;

        $row = PicAlbum::where('album_id', $filter['album_id']);

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        /* 查询数据 */
        $res = $res->withCount('getGoods as goods_count');

        $res = $res->withCount('getGoodsGallery as gallery_count');

        $res = $res->orderBy('pic_id', 'DESC');

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $v) {

                //图片是否引用
                if ($v['goods_count'] > 0 || $v['gallery_count'] > 0) {
                    $res[$k]['verific_pic'] = 1;
                } else {
                    $res[$k]['verific_pic'] = 0;
                }

                if (isset($v['pic_file']) && $v['pic_file']) {
                    $res[$k]['pic_file'] = $this->dscRepository->getImagePath(ltrim($v['pic_file'], '/'));
                }

                if ($v['pic_size'] > 0) {
                    $res[$k]['pic_size'] = number_format($v['pic_size'] / 1024, 2) . 'k';
                }
            }
        }

        return [
            'pic_list' => $res,
            'filter' => $filter,
            'page_count' => $filter['page_count'],
            'record_count' => $filter['record_count']
        ];
    }
}
