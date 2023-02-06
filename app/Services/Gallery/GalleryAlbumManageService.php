<?php

namespace App\Services\Gallery;

use App\Libraries\Image;
use App\Models\GalleryAlbum;
use App\Models\PicAlbum;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Merchant\MerchantDataHandleService;

/**
 *
 * Class GalleryAlbumManageService
 * @package App\Services\Gallery
 */
class GalleryAlbumManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    public function getGalleryChild($album_id = 0, $type = 0)
    {
        $child_arr = '';
        if ($album_id > 0) {
            if ($type == 1) {
                $child_arr = $album_id;
            }
            $res = GalleryAlbum::where('parent_album_id', $album_id);
            $child_list = BaseRepository::getToArrayGet($res);
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
        return $this->dscRepository->delStrComma($child_arr);
    }

    /**
     * 获取相册列表
     *
     * @param int $ru_id
     * @return array
     * @throws \Exception
     */
    public function getGalleryAlbumList($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGalleryAlbumList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /*筛选信息*/
        $filter['album_mame'] = empty($_REQUEST['album_mame']) ? '' : trim($_REQUEST['album_mame']);
        $filter['parent_id'] = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;

        /*拼装筛选*/
        $res = GalleryAlbum::whereRaw(1);

        if ($filter['album_mame']) {
            $res = $res->where('album_mame', 'LIKE', '%' . mysql_like_quote($filter['album_mame']) . '%');
        }

        $res = $res->where('parent_album_id', $filter['parent_id']);
        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] > -1) {
            if ($filter['ru_id'] == 0) {
                if ($filter['store_search'] > 0) {
                    $filter['store_type'] = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($filter['store_search'] == 1) {
                        $res = $res->where('ru_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1) {
                        $res = $res->where(function ($query) use ($filter) {
                            $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                                if ($filter['store_search'] == 2) {
                                    $query = $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                }
                                if ($filter['store_search'] == 3) {
                                    if ($filter['store_type']) {
                                        $query = $query->where('shop_name_suffix', $filter['store_type']);
                                    }
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                }
                            });
                        });
                    }
                } else {
                    $res = $res->where('ru_id', 0);
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        if ($filter['seller_list'] == 2) {
            //区分商家和自营
            $res = $res->where('ru_id', 0)->where('suppliers_id', '>', 0);
        } else {
            //区分商家和自营
            if (!empty($filter['seller_list'])) {
                $res = $res->where('ru_id', '>', 0)->where('suppliers_id', 0);
            } else {
                $res = $res->where('ru_id', 0)->where('suppliers_id', 0);
            }
        }

        $filter['record_count'] = $res->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获活动数据 */

        $res = $res->orderBy('ru_id', 'ASC')->orderBy('sort_order', 'ASC')->offset($filter['start'])->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $k => $v) {
                if ($v['ru_id'] > 0) {
                    $row[$k]['shop_name'] = $merchantList[$v['ru_id']]['shop_name'] ?? '';
                } else {
                    if ($v['suppliers_id'] > 0) {
                        $row[$k]['shop_name'] = get_table_date('suppliers', "suppliers_id='" . $v['suppliers_id'] . "'", ['suppliers_name'], 2);
                    } else {
                        $row[$k]['shop_name'] = $GLOBALS['_LANG']['self'];
                    }
                }

                $row[$k]['album_cover'] = $this->dscRepository->getImagePath($v['album_cover']);
                $row[$k]['gallery_count'] = PicAlbum::where('album_id', $v['album_id'])->count();
            }
        }

        $arr = ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }

    /**
     * 上传文件
     *
     * @param $upload
     * @param string $file
     * @return bool|string
     */
    public function uploadArticleFile($upload, $file = '')
    {
        $file_dir = storage_public(DATA_DIR . "/gallery_album/");
        if (!file_exists($file_dir)) {
            if (!make_dir($file_dir)) {
                /* 创建目录失败 */
                return false;
            }
        }

        $filename = Image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
        $dir = DATA_DIR . "/gallery_album/" . $filename;
        $path = storage_public($dir);

        //组合路径，并删除storage前的路径
        if (move_upload_file($upload['tmp_name'], $path)) {
            return $dir;
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
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getPicAlbumList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['album_id'] = isset($_REQUEST['album_id']) && !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : $album_id;

        $row = PicAlbum::where('album_id', $filter['album_id']);

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

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
