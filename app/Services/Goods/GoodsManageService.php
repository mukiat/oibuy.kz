<?php

namespace App\Services\Goods;

use App\Libraries\Image;
use App\Models\BargainGoods;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CollectGoods;
use App\Models\Comment;
use App\Models\DiscussCircle;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsArticle;
use App\Models\GoodsAttr;
use App\Models\GoodsCat;
use App\Models\GoodsExtend;
use App\Models\GoodsGallery;
use App\Models\GoodsKeyword;
use App\Models\GoodsLibGallery;
use App\Models\GoodsTransport;
use App\Models\GroupGoods;
use App\Models\KeywordList;
use App\Models\LinkAreaGoods;
use App\Models\LinkGoods;
use App\Models\MemberPrice;
use App\Models\MerchantsCategory;
use App\Models\OrderInfo;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsChangelog;
use App\Models\ProductsWarehouse;
use App\Models\SeckillGoods;
use App\Models\Tag;
use App\Models\TeamGoods;
use App\Models\VirtualCard;
use App\Models\WarehouseAreaAttr;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseAttr;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Category\CategoryDataHandleService;
use App\Services\Category\CategoryService;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCategoryDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class GoodsManageService
{
    protected $categoryService;
    protected $image;
    protected $dscRepository;
    protected $commonManageService;
    protected $merchantCommonService;

    public function __construct(
        CategoryService $categoryService,
        Image $image,
        DscRepository $dscRepository,
        CommonManageService $commonManageService,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->categoryService = $categoryService;
        $this->image = $image;
        $this->dscRepository = $dscRepository;
        $this->commonManageService = $commonManageService;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 取得推荐类型列表
     *
     * @return array 推荐类型列表
     * @throws \Exception
     */
    public static function getIntroList()
    {
        $list = [
            'is_best' => lang('manage/goods.is_best'),
            'is_new' => lang('manage/goods.is_new'),
            'is_hot' => lang('manage/goods.is_hot'),
            'is_promote' => lang('manage/goods.is_promote'),
            'store_best' => lang('manage/goods.store_best'),
            'store_new' => lang('manage/goods.store_new'),
            'store_hot' => lang('manage/goods.store_hot'),
            'all_type' => lang('manage/goods.all_type')
        ];

        if (file_exists(MOBILE_DRP)) {
            $list['is_distribution'] = lang('manage/goods.is_distribution');
        }

        return $list;
    }

    /**
     * 取得重量单位列表
     *
     * @return array
     * @throws \Exception
     */
    public function getUnitList()
    {
        return [
            '1' => lang('manage/goods.unit_kg'),
            '0.001' => lang('manage/goods.unit_g')
        ];
    }

    /**
     * 获取一层分类
     *
     * @param int $cat_id
     * @param int $cat_level
     * @param array $seller_shop_cat
     * @param string $id
     * @param string $onchange
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed|string
     * @throws \Exception
     */
    public function catListOne($cat_id = 0, $cat_level = 0, $seller_shop_cat = [], $id = 'cat_list', $onchange = 'catList')
    {
        if ($cat_id == 0) {
            $arr = $this->categoryService->catList($cat_id, 0, 0, 'category', $seller_shop_cat);
            return $arr;
        } else {
            $arr = $this->categoryService->catList($cat_id);

            foreach ($arr as $key => $value) {
                if ($key == $cat_id) {
                    unset($arr[$cat_id]);
                }
            }

            // 拼接字符串
            $str = '';
            if ($arr) {
                $cat_level++;

                $str .= "<select name='catList" . $cat_level . "' id='" . $id . $cat_level . "' onchange='" . $onchange . "(this.value, " . $cat_level . ")' class='select'>";
                $str .= "<option value='0'>全部分类</option>";

                foreach ($arr as $key1 => $value1) {
                    $str .= "<option value='" . $value1['cat_id'] . "'>" . $value1['cat_name'] . "</option>";
                }
                $str .= "</select>";
            }

            return $str;
        }
    }

    /**
     * 格式化商品图片名称（按目录存储）
     *
     * @param $type
     * @param $goods_id
     * @param $source_img
     * @param string $position
     * @param string $dir
     * @param string $up_type
     * @return bool|string
     */
    public function reformatImageName($type, $goods_id, $source_img, $position = '', $dir = IMAGE_DIR, $up_type = '')
    {
        $time = TimeRepository::getGmTime();

        // 如果配置项使用原图名称
        if (config('shop.upload_use_original_name', 0) > 0) {
            $subject = pathinfo($source_img, PATHINFO_FILENAME);
            $pos = mb_strrpos($subject, '-');
            $rand_name = $pos ? mb_substr($subject, 0, $pos) : $subject;
        } else {
            $rand_name = $time . sprintf("%03d", mt_rand(1, 999));
        }

        $img_ext = substr($source_img, strrpos($source_img, '.'));

        if ($up_type == 'album') {
            if (!file_exists(storage_public($dir))) {
                make_dir(storage_public($dir));
            }

            if (!file_exists(storage_public($dir . '/original_img'))) {
                make_dir(storage_public($dir . '/original_img'));
            }

            if (!file_exists(storage_public($dir . '/thumb_img'))) {
                make_dir(storage_public($dir . '/thumb_img'));
            }

            if (!file_exists(storage_public($dir . '/images'))) {
                make_dir(storage_public($dir . '/images'));
            }
        } else {
            $sub_dir = TimeRepository::getLocalDate('Ym', $time);

            if (!file_exists(storage_public($dir . '/' . $sub_dir))) {
                make_dir(storage_public($dir . '/' . $sub_dir));
            }

            if (!file_exists(storage_public($dir . '/' . $sub_dir . '/source_img'))) {
                make_dir(storage_public($dir . '/' . $sub_dir . '/source_img'));
            }

            if (!file_exists(storage_public($dir . '/' . $sub_dir . '/goods_img'))) {
                make_dir(storage_public($dir . '/' . $sub_dir . '/goods_img'));
            }

            if (!file_exists(storage_public($dir . '/' . $sub_dir . '/thumb_img'))) {
                make_dir(storage_public($dir . '/' . $sub_dir . '/thumb_img'));
            }
        }

        switch ($type) {
            case 'goods':
                $img_name = $goods_id . '_G_' . $rand_name;
                break;
            case 'goods_thumb':
                $img_name = $goods_id . '_thumb_G_' . $rand_name;
                break;
            case 'source':
                $img_name = $goods_id . '_S_' . $rand_name;
                break;
            case 'source_thumb':
                $img_name = $goods_id . '_thumb_S_' . $rand_name;
                break;
            case 'gallery':
                $img_name = $goods_id . '_P_' . $rand_name;
                break;
            case 'gallery_thumb':
                $img_name = $goods_id . '_thumb_P_' . $rand_name;
                break;
        }

        if (strpos($source_img, 'temp') !== false) {
            $ex_img = explode('temp', $source_img);
            $source_img = "temp" . $ex_img[1];
        }

        if ($up_type == 'album') {
            if ($position == 'source') {
                if ($this->moveImageFile($source_img, storage_public($dir . '/images/' . $img_name . $img_ext))) {
                    return $dir . '/images/' . $img_name . $img_ext;
                }
            } elseif ($position == 'thumb') {
                if ($this->moveImageFile($source_img, storage_public($dir . '/thumb_img/' . $img_name . $img_ext))) {
                    return $dir . '/thumb_img/' . $img_name . $img_ext;
                }
            } else {
                if ($this->moveImageFile($source_img, storage_public($dir . '/original_img/' . $img_name . $img_ext))) {
                    return $dir . '/original_img/' . $img_name . $img_ext;
                }
            }
        } else {
            if ($position == 'source') {
                if ($this->moveImageFile($source_img, storage_public($dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext))) {
                    return $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext;
                }
            } elseif ($position == 'thumb') {
                if ($this->moveImageFile($source_img, storage_public($dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext))) {
                    return $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext;
                }
            } else {
                if ($this->moveImageFile($source_img, storage_public($dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext))) {
                    return $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext;
                }
            }
        }

        return false;
    }

    /**
     * @param $source
     * @param $dest
     * @return bool
     */
    public function moveImageFile($source, $dest)
    {
        if (@copy($source, $dest)) {
            if (file_exists($source)) {
                @unlink($source);
            }
            return true;
        }
        return false;
    }

    /**
     * 相册统计
     *
     * @param int $goods_id
     * @param int $is_lib
     * @return mixed
     */
    public function getGoodsGalleryCount($goods_id = 0, $is_lib = 0)
    {
        if ($is_lib == 1) {
            $res = GoodsLibGallery::whereRaw(1);
        } elseif ($is_lib == 2) {
            $res = \App\Modules\Suppliers\Models\SuppliersGoodsGallery::whereRaw(1);
        } else {
            $res = GoodsGallery::whereRaw(1);
        }

        $res = $res->where('goods_id', $goods_id);

        $count = $res->count();

        return $count;
    }

    /**
     * 为某商品生成唯一的货号
     * @param int $goods_id 商品编号
     * @return  string  唯一的货号
     */
    public function generateGoodSn($goods_id, $is_table = 0)
    {
        $goods_sn = config('shop.sn_prefix') . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;

        if ($is_table == 1) {
            $sn_list = \App\Modules\Suppliers\Models\Wholesale::whereRaw(1);
        } elseif ($is_table == 2) {
            $sn_list = \App\Modules\Cgroup\Models\GroupbuyGoods::whereRaw(1);
        } else {
            $sn_list = Goods::whereRaw(1);
        }

        $goods_sn = mysql_like_quote($goods_sn);
        $sn_list = $sn_list->where('goods_sn', 'like', $goods_sn . '%')
            ->where('goods_id', '<>', $goods_id)
            ->orderByRaw('LENGTH(goods_sn) desc');
        $sn_list = BaseRepository::getToArrayGet($sn_list);
        $sn_list = BaseRepository::getKeyPluck($sn_list, 'goods_sn');

        if ($goods_sn && in_array($goods_sn, $sn_list)) {
            $max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;
            $new_sn = $goods_sn . mt_rand(0, $max);
            while (in_array($new_sn, $sn_list)) {
                $new_sn = $goods_sn . mt_rand(0, $max);
            }
            $goods_sn = $new_sn;
        }

        return $goods_sn;
    }

    /**
     * 添加商品相册
     * 保存某商品的相册图片
     *
     * @param $goods_id
     * @param $image_files
     * @param $image_descs
     * @param $image_urls
     * @param int $single_id
     * @param int $files_type
     * @param $is_ajax
     * @param int $gallery_count
     * @param int $is_lib
     * @param string $htm_maxsize
     */
    public function handleGalleryImageAdd($goods_id, $image_files, $image_descs, $image_urls, $single_id = 0, $files_type = 0, $is_ajax, $gallery_count = 0, $is_lib = 0, $htm_maxsize = '2M')
    {
        $admin_id = get_admin_id();
        $admin_temp_dir = "seller";
        $admin_temp_dir = storage_public("temp" . '/' . $admin_temp_dir . '/' . "admin_" . $admin_id);

        // 如果目标目录不存在，则创建它
        if (!file_exists($admin_temp_dir)) {
            make_dir($admin_temp_dir);
        }
        $thumb_img_id = [];

        $img_url = '';
        $thumb_url = '';
        $img_original = '';

        /* 是否处理缩略图 */
        $proc_thumb = (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) ? false : true;
        foreach ($image_descs as $key => $img_desc) {
            /* 是否成功上传 */
            $flag = false;
            if (isset($image_files['error'])) {
                if ($image_files['error'][$key] == 0) {
                    $flag = true;
                }
            } else {
                if ($image_files['tmp_name'][$key] != 'none' && $image_files['tmp_name'][$key]) {
                    $flag = true;
                }
            }
            if ($flag) {
                $upload = [
                    'name' => $image_files['name'][$key],
                    'type' => $image_files['type'][$key],
                    'tmp_name' => $image_files['tmp_name'][$key],
                    'size' => $image_files['size'][$key],
                ];
                if (isset($image_files['error'])) {
                    $upload['error'] = $image_files['error'][$key];
                }
                $img_original = $this->image->upload_image($upload, ['type' => 1]);
                if ($img_original === false) {
                    if ($is_ajax == 'ajax') {
                        $result['error'] = '1';
                        $result['massege'] = sprintf($GLOBALS['_LANG']['img_url_too_big'], $key + 1, $htm_maxsize);
                        return;
                    } else {
                        return sys_msg($this->image->error_msg(), 1, [], false);
                    }
                } else {
                    $img_original = storage_public($img_original);
                }
                $img_url = $img_original;

                // 生成缩略图
                if ($proc_thumb) {
                    $thumb_url = $this->image->make_thumb(['img' => $img_original, 'type' => 1], config('shop.thumb_width'), config('shop.thumb_height'));
                    $thumb_url = is_string($thumb_url) ? $thumb_url : '';
                } else {
                    $thumb_url = $img_original;
                }

                // 如果服务器支持GD 则添加水印
                if ($proc_thumb && gd_version() > 0) {
                    $pos = strpos(basename($img_original), '.');
                    $newname = dirname($img_original) . '/' . $this->image->random_filename() . substr(basename($img_original), $pos);
                    copy($img_original, $newname);
                    $img_url = $newname;

                    $this->image->add_watermark($img_url, '', config('shop.watermark'), config('shop.watermark_place'), config('shop.watermark_alpha'));
                }

                /* 重新格式化图片名称 */
                if ($goods_id == 0) {
                    $img_original = $this->reformatImageName('gallery', $single_id, $img_original, 'source');
                    $img_url = $this->reformatImageName('gallery', $single_id, $img_url, 'goods');
                    $thumb_url = $this->reformatImageName('gallery_thumb', $single_id, $thumb_url, 'thumb');
                } else {
                    $img_original = $this->reformatImageName('gallery', $goods_id, $img_original, 'source');
                    $img_url = $this->reformatImageName('gallery', $goods_id, $img_url, 'goods');
                    $thumb_url = $this->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                }

                $other = [
                    'goods_id' => $goods_id,
                    'img_url' => $img_url,
                    'img_desc' => $gallery_count,
                    'thumb_url' => $thumb_url,
                    'img_original' => $img_original
                ];

                if ($is_lib != 2) {
                    if ($files_type == 0) {
                        $other['single_id'] = $single_id;
                    } elseif ($files_type = 1) {
                        $other['dis_id'] = $single_id;
                    }
                }

                if ($is_lib == 1) {
                    $thumb_img_id[] = GoodsLibGallery::insertGetId($other);
                } elseif ($is_lib == 2) {
                    $thumb_img_id[] = \App\Modules\Suppliers\Models\SuppliersGoodsGallery::insertGetId($other);
                } else {
                    $thumb_img_id[] = GoodsGallery::insertGetId($other);
                }

                /* 不保留商品原图的时候删除原图 */
                if ($proc_thumb && !config('shop.retain_original_img') && !empty($img_original)) {
                    if ($is_lib) {
                        $res = GoodsLibGallery::whereRaw(1);
                    } elseif ($is_lib == 2) {
                        $res = \App\Modules\Suppliers\Models\SuppliersGoodsGallery::whereRaw(1);
                    } else {
                        $res = GoodsGallery::whereRaw(1);
                    }

                    $res->where('goods_id', $goods_id)->update([
                        'img_original' => ''
                    ]);

                    dsc_unlink(storage_public($img_original));
                }
            } elseif (!empty($image_urls[$key]) && ($image_urls[$key] != $GLOBALS['_LANG']['img_file']) && ($image_urls[$key] != 'http://') && (strpos($image_urls[$key], 'http://') !== false || strpos($image_urls[$key], 'https://') !== false)) {
                if ($this->dscRepository->getHttpBasename($image_urls[$key], $admin_temp_dir)) {
                    $image_url = trim($image_urls[$key]);
                    //定义原图路径
                    $down_img = $admin_temp_dir . "/" . basename($image_url);

                    $img_wh = $this->image->get_width_to_height($down_img, config('shop.image_width'), config('shop.image_height'));
                    $image_width = isset($img_wh['image_width']) ? $img_wh['image_width'] : config('shop.image_width');
                    $image_height = isset($img_wh['image_height']) ? $img_wh['image_height'] : config('shop.image_height');

                    $goods_img = $this->image->make_thumb(['img' => $down_img, 'type' => 1], $image_width, $image_height);

                    // 生成缩略图
                    if ($proc_thumb) {
                        $thumb_url = $this->image->make_thumb(['img' => $down_img, 'type' => 1], config('shop.thumb_width'), config('shop.thumb_height'));
                        $thumb_url = $this->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                    } else {
                        $thumb_url = $this->image->make_thumb(['img' => $down_img, 'type' => 1]);
                        $thumb_url = $this->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                    }

                    $img_original = $this->reformatImageName('gallery', $goods_id, $down_img, 'source');
                    $img_url = $this->reformatImageName('gallery', $goods_id, $goods_img, 'goods');

                    $other = [
                        'goods_id' => $goods_id,
                        'img_url' => $img_url,
                        'img_desc' => $gallery_count,
                        'thumb_url' => $thumb_url,
                        'img_original' => $img_original
                    ];

                    if ($is_lib != 2) {
                        if ($files_type == 0) {
                            $other['single_id'] = $single_id;
                        } elseif ($files_type = 1) {
                            $other['dis_id'] = $single_id;
                        }
                    }

                    if ($is_lib == 1) {
                        $thumb_img_id[] = GoodsLibGallery::insertGetId($other);
                    } elseif ($is_lib == 2) {
                        $thumb_img_id[] = \App\Modules\Suppliers\Models\SuppliersGoodsGallery::insertGetId($other);
                    } else {
                        $thumb_img_id[] = GoodsGallery::insertGetId($other);
                    }

                    @unlink($down_img);
                }
            }

            $this->dscRepository->getOssAddFile([$img_url, $thumb_url, $img_original]);
        }

        if ($is_lib == 2) {
            if (!empty(session('thumb_img_id' . session('supply_id')))) {
                $thumb_img_id = array_merge($thumb_img_id, session('thumb_img_id' . session('supply_id')));
            }
            session()->put('thumb_img_id' . session('supply_id'), $thumb_img_id);
        } else {
            $id = session()->has('seller_id') && session('seller_id') ? session('seller_id') : session('admin_id', 0); // 商家后台兼容用

            if (!empty(session('thumb_img_id' . $id))) {
                $thumb_img_id = array_merge($thumb_img_id, session('thumb_img_id' . $id));
            }

            session()->put('thumb_img_id' . $id, $thumb_img_id);
        }


    }


    /**
     * 从回收站删除多个商品
     *
     * @param int $goods_id
     * @throws \OSS\Core\OssException
     */
    public function deleteGoods($goods_id = 0)
    {
        if (empty($goods_id)) {
            return;
        }

        /* 取得有效商品id */
        $goods_id = BaseRepository::getExplode($goods_id);
        $goods = Goods::whereIn('goods_id', $goods_id)
            ->where('is_delete', 1);
        $goods = BaseRepository::getToArrayGet($goods);
        $goods_id = BaseRepository::getKeyPluck($goods, 'goods_id');

        if (empty($goods_id)) {
            return;
        }

        /* 删除商品图片和轮播图片文件 */
        if ($goods) {
            $goods_thumb = BaseRepository::getKeyPluck($goods, 'goods_thumb');
            $goods_img = BaseRepository::getKeyPluck($goods, 'goods_img');
            $original_img = BaseRepository::getKeyPluck($goods, 'original_img');
            $goods_video = BaseRepository::getKeyPluck($goods, 'goods_video');

            $img = [
                $goods_thumb,
                $goods_img,
                $original_img,
                $goods_video
            ];

            $img = BaseRepository::getFlatten($img);

            $this->dscRepository->getOssDelFile($img);

            dsc_unlink($img, storage_public());
        }

        /* 删除商品 */
        Goods::whereIn('goods_id', $goods_id)->delete();

        /* 删除商品的货品记录 */
        Products::whereIn('goods_id', $goods_id)->delete();

        /* 删除商品相册的图片文件 */
        $goodsGallery = GoodsGallery::whereIn('goods_id', $goods_id);
        $goodsGallery = BaseRepository::getToArrayGet($goodsGallery);

        if ($goodsGallery) {
            $img_url = BaseRepository::getKeyPluck($goodsGallery, 'img_url');
            $thumb_url = BaseRepository::getKeyPluck($goodsGallery, 'thumb_url');
            $img_original = BaseRepository::getKeyPluck($goodsGallery, 'img_original');

            $img = [
                $img_url,
                $thumb_url,
                $img_original
            ];

            $img = BaseRepository::getFlatten($img);

            $this->dscRepository->getOssDelFile($img);

            dsc_unlink($img, storage_public());
        }

        /* 删除商品相册 */
        GoodsGallery::whereIn('goods_id', $goods_id)->delete();

        /* 删除相关表记录 */
        CollectGoods::whereIn('goods_id', $goods_id)->delete();
        GoodsArticle::whereIn('goods_id', $goods_id)->delete();
        GoodsAttr::whereIn('goods_id', $goods_id)->delete();
        GoodsCat::whereIn('goods_id', $goods_id)->delete();
        MemberPrice::whereIn('goods_id', $goods_id)->delete();

        GroupGoods::where(function ($query) use ($goods_id) {
            $query->whereIn('goods_id', $goods_id);
        })->orWhere(function ($query) use ($goods_id) {
            $query->whereIn('parent_id', $goods_id);
        })->delete();

        LinkGoods::where(function ($query) use ($goods_id) {
            $query->whereIn('goods_id', $goods_id);
        })->orWhere(function ($query) use ($goods_id) {
            $query->whereIn('link_goods_id', $goods_id);
        })->delete();

        Tag::whereIn('goods_id', $goods_id)->delete();
        Comment::where('comment_type', 0)->whereIn('id_value', $goods_id)->delete();
        Cart::whereIn('goods_id', $goods_id)->delete();
        PresaleActivity::whereIn('goods_id', $goods_id)->delete();

        WarehouseGoods::whereIn('goods_id', $goods_id)->delete();
        WarehouseAttr::whereIn('goods_id', $goods_id)->delete();
        WarehouseAreaGoods::whereIn('goods_id', $goods_id)->delete();
        WarehouseAreaAttr::whereIn('goods_id', $goods_id)->delete();
        ProductsWarehouse::whereIn('goods_id', $goods_id)->delete();
        ProductsArea::whereIn('goods_id', $goods_id)->delete();

        //清楚商品零时货品表数据
        ProductsChangelog::whereIn('goods_id', $goods_id)->delete();

        //删除讨论圈记录
        DiscussCircle::whereIn('goods_id', $goods_id)->delete();

        /* 删除相应虚拟商品记录 */
        VirtualCard::whereIn('goods_id', $goods_id)->delete();

        // 删除视频号商品
        if (config('shop.wxapp_shop_status')) {
            $WxappGoodsId = \App\Modules\WxMedia\Models\WxappGoodsExtension::query()->whereIn('goods_id', $goods_id)->pluck('goods_id');
            $WxappGoodsId = BaseRepository::getToArray($WxappGoodsId);

            if ($WxappGoodsId > 0) {
                $WxConfig = app(\App\Modules\Wxapp\Services\WxappConfigService::class)->get_config();

                $WxappConfig = [
                    'appid' => $WxConfig['wx_appid'] ?? '',
                    'secret' => $WxConfig['wx_appsecret'] ?? '',
                ];

                app(\App\Modules\WxMedia\Services\WxappMediaGoodsService::class)->delMediaGoods($WxappGoodsId, 0, $WxappConfig);
            }
        }

        /* 清除缓存 */
        clear_cache_files();
    }


    /**
     * 检测商品是否有货品
     *
     * @access      public
     * @params      integer     $goods_id       商品id
     * @params      string      $where     sql条件
     * @return      string number               -1，错误；1，存在；0，不存在
     */
    public function checkGoodsProductExist($object, $goods_id, $where = [])
    {
        //$goods_id不能为空
        if (empty($goods_id)) {
            return 0;
        }

        $object = $object->where('goods_id', $goods_id);

        if ($where) {
            foreach ($where as $key => $val) {
                $object = $object->where($where[$key], $where[$val]);
            }
        }

        $count = $object->count();

        if ($count > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 为某商品生成唯一的货号
     * @param int $goods_id 商品编号
     * @return  string  唯一的货号
     */
    public function generate_goods_sn($goods_id)
    {
        $goods_sn = config('shop.sn_prefix') . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;

        $sn_list = Goods::where('goods_id', '<>', $goods_id)
            ->where('goods_sn', 'like', '%' . $goods_sn . '%')
            ->orderByRaw('LENGTH(goods_sn) DESC')
            ->pluck('goods_sn');

        $sn_list = $sn_list ? $sn_list->toArray() : [];

        if (!empty($sn_list) && in_array($goods_sn, $sn_list)) {
            $max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;
            $new_sn = $goods_sn . mt_rand(0, $max);
            while (in_array($new_sn, $sn_list)) {
                $new_sn = $goods_sn . mt_rand(0, $max);
            }
            $goods_sn = $new_sn;
        }

        return $goods_sn;
    }


    /**
     * 获取商品订单是否存在
     * @param int $goods_id
     * @return mixed
     */
    public function getOrderGoodsCout($goods_id = 0)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        $res = OrderInfo::whereHasIn('getOrderGoods', function ($query) use ($goods_id) {
            $query->whereIn('goods_id', $goods_id);
        });

        $order_count = $res->count();
        return $order_count;
    }

    /**
     * 列表链接
     * @param bool $is_add 是否添加（插入）
     * @param string $extension_code 虚拟商品扩展代码，实体商品为空
     * @return  array('href' => $href, 'text' => $text)
     */
    public function listLink($is_add = true, $extension_code = '')
    {
        $href = 'goods.php?act=list';
        if (!empty($extension_code)) {
            $href .= '&extension_code=' . $extension_code;
        }
        if (!$is_add) {
            $href .= '&' . list_link_postfix();
        }

        if ($extension_code == 'virtual_card') {
            $text = $GLOBALS['_LANG']['50_virtual_card_list'];
        } else {
            $text = $GLOBALS['_LANG']['01_goods_list'];
        }

        return ['href' => $href, 'text' => $text];
    }

    /**
     * 添加链接
     * @param string $extension_code 虚拟商品扩展代码，实体商品为空
     * @return  array('href' => $href, 'text' => $text)
     */
    public function addLink($extension_code = '')
    {
        $href = 'goods.php?act=add';
        if (!empty($extension_code)) {
            $href .= '&extension_code=' . $extension_code;
        }

        if ($extension_code == 'virtual_card') {
            $text = $GLOBALS['_LANG']['51_virtual_card_add'];
        } else {
            $text = $GLOBALS['_LANG']['02_goods_add'];
        }

        return ['href' => $href, 'text' => $text];
    }

    /**
     * 商品是否可操作
     *
     * @param int $goods_id
     * @param int $ru_id
     * @return boolean
     */
    public function goodsCanHandle($goods_id = 0, $ru_id = 0)
    {
        if ($goods_id > 0) {
            $user_id = Goods::where('goods_id', $goods_id)->value('user_id');

            if ($user_id !== null) {
                if ($user_id == $ru_id) { //
                    return true;
                }
            }
        } elseif ($goods_id == 0) { // 添加暂时不判断 给平台用的方法
            if ($ru_id == 0) {
                return true;
            }
        } else { // 小于0 返回false

        }

        return false;
    }

    /**
     * 取得某商品的会员价格列表
     * @param int $goods_id 商品编号
     * @return  array   会员价格列表 user_rank => user_price
     */
    public function get_member_price_list($goods_id = 0)
    {
        if (empty($goods_id)) {
            return [];
        }

        /* 取得会员价格 */
        $price_list = [];

        $model = MemberPrice::query()->where('goods_id', $goods_id)->get();

        $res = $model ? $model->toArray() : [];

        if (!empty($res)) {
            foreach ($res as $row) {
                // 处理百分比
                if (isset($row['percentage']) && $row['percentage'] == 1) {
                    $row['user_price'] = $row['user_price'] . '%';
                }

                $price_list[$row['user_rank']] = $row['user_price'];
            }
        }

        return $price_list;
    }

    /**
     * 保存某商品的会员价格
     *
     * @param int $goods_id 商品编号
     * @param array $rank_list 等级列表
     * @param array $price_list 价格列表
     * @param int $is_discount 参与会员特价权益：0 否，1 是，默认 是
     * @return bool
     */
    public function handle_member_price($goods_id = 0, $rank_list = [], $price_list = [], $is_discount = 1)
    {
        if (empty($goods_id) || empty($rank_list)) {
            return false;
        }

        /* 循环处理每个会员等级 */
        foreach ($rank_list as $key => $rank) {
            /* 会员等级对应的价格 */
            $price = $price_list[$key] ?? 0;

            $insertData = $updateData = ['user_price' => -1, 'percentage' => 0];

            // 处理百分比
            if (stripos($price, '%') !== false) {
                $price = rtrim($price, '%');
                $updateData['percentage'] = $insertData['percentage'] = 1;
            }

            // 插入或更新记录
            $count = MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank)->count();

            if ($count > 0) {
                /* 如果会员价格是小于等于0则删除原来价格，不是则更新为新的价格 */
                if ($price <= 0 || $is_discount == 0) {
                    MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank)->delete();
                } else {
                    $updateData['user_price'] = $price;
                    MemberPrice::where('goods_id', $goods_id)->where('user_rank', $rank)->update($updateData);
                }
            } else {
                if ($price == -1 || empty($price) || $is_discount == 0) {
                    continue;
                } else {
                    $insertData['goods_id'] = $goods_id;
                    $insertData['user_rank'] = $rank;
                    $insertData['user_price'] = $price;
                    MemberPrice::insert($insertData);
                }
            }
        }

        return true;
    }

    /**
     * 获得商品列表
     *
     * @param int $is_delete
     * @param int $real_goods
     * @param string $conditions
     * @param int $review_status
     * @param int $real_division
     * @return array
     * @throws \Exception
     */
    public function getGoodsList($is_delete = 0, $real_goods = 1, $conditions = '', $review_status = 0, $real_division = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGoodsList' . '-' . $is_delete . '-' . $real_goods . '-' . $review_status;
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $seller = $this->commonManageService->getAdminIdSeller();
        $day = TimeRepository::getLocalGetDate();
        $today = TimeRepository::getLocalMktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['intro_type'] = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
        $filter['is_promote'] = empty($_REQUEST['is_promote']) ? 0 : intval($_REQUEST['is_promote']);
        $filter['stock_warning'] = empty($_REQUEST['stock_warning']) ? 0 : intval($_REQUEST['stock_warning']);
        $filter['cat_type'] = !isset($_REQUEST['cat_type']) && empty($_REQUEST['cat_type']) ? '' : addslashes($_REQUEST['cat_type']);
        $filter['brand_id'] = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
        $filter['brand_keyword'] = empty($_REQUEST['brand_keyword']) ? '' : trim($_REQUEST['brand_keyword']);
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        $filter['suppliers_id'] = isset($_REQUEST['suppliers_id']) ? (empty($_REQUEST['suppliers_id']) ? '' : trim($_REQUEST['suppliers_id'])) : '';
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? (int)$_REQUEST['seller_list'] : 0;  //商家和自营订单标识
        $filter['warn_number'] = empty($_REQUEST['warn_number']) ? '' : intval($_REQUEST['warn_number']);//库存预警
        $filter['is_show'] = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : -1; //是否显示

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        if (isset($_REQUEST['review_status'])) {
            $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
        } else {
            $filter['review_status'] = $review_status;
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['extension_code'] = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
        $filter['is_delete'] = $is_delete;
        $filter['real_goods'] = $real_goods;

        $row = Goods::where('is_delete', $is_delete);

        if ($filter['cat_type'] == 'seller' && $filter['cat_id'] > 0) {
            $user_cat = $this->categoryService->getMerchantsCatListChildren($filter['cat_id']);
            $row = $row->whereIn('user_cat', $user_cat);
        }

        if ($filter['brand_keyword']) {
            $brand_id = Brand::select('brand_id')->where('brand_name', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['brand_keyword']) . '%')
                ->pluck('brand_id');
            $filter['brand_id'] = BaseRepository::getToArray($brand_id);
        }

        if ($filter['brand_id']) {
            if (!is_array($filter['brand_id'])) {
                $filter['brand_id'] = array($filter['brand_id']);
            }
            $row = $row->whereIn('brand_id', $filter['brand_id']);
        }

        /* 库存警告 */
        if ($filter['warn_number'] || $filter['stock_warning']) {
            $row = $row->where(function ($query) {
                $query->whereColumn('goods_number', '<=', 'warn_number')
                    ->orWhereHasIn('getProducts', function ($query) {
                        $query->whereColumn('product_number', '<=', 'product_warn_number');
                    })
                    ->orWhereHasIn('getProductsWarehouse', function ($query) {
                        $query->whereColumn('product_number', '<=', 'product_warn_number');
                    })
                    ->orWhereHasIn('getProductsArea', function ($query) {
                        $query->whereColumn('product_number', '<=', 'product_warn_number');
                    });
            });
        }

        if (request()->get('act') == 'is_sale' || request()->get('is_on_sale') == 1) {
            $is_on_sale = 1;
        } elseif (request()->get('act') == 'on_sale' || (!is_null(request()->get('is_on_sale')) && request()->get('is_on_sale') == 0)) {
            $is_on_sale = 0;
        }

        /* 上架下架 */
        if (isset($is_on_sale) && $is_on_sale != -1) {
            $filter['is_on_sale'] = !empty($is_on_sale) ? 1 : 0;
            $row = $row->where('is_on_sale', $filter['is_on_sale']);
        } else {
            if ($filter['extension_code']) {
                $row = $row->where('extension_code', $filter['extension_code']);
            }
        }

        /**
         * 0 : 自营
         * 1 : 店铺
         * 3 : 主订单
         */
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? intval($_REQUEST['seller_list']) : 0;

        if (!empty($filter['seller_list'])) {
            //优化提高查询性能，原始条件是 where('user_id', '>', 0);
            $row = CommonRepository::constantMaxId($row, 'user_id');
        } else {
            $row = $row->where('user_id', $seller['ru_id']);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
        $filter['store_type'] = isset($_REQUEST['store_type']) ? trim($_REQUEST['store_type']) : '';

        if ($filter['store_search'] > -1) {
            if ($seller['ru_id'] == 0) {
                if ($filter['store_search'] > 0) {
                    if ($filter['store_search'] == 1) {
                        $row = $row->where('user_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1 && $filter['store_search'] < 4) {
                        $row = $row->where(function ($query) use ($filter) {
                            $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                                if ($filter['store_search'] == 2) {
                                    $query->where('rz_shop_name', 'LIKE', '%' . $this->dscRepository->mysqlLikeQuote($filter['store_keyword']) . '%');
                                } elseif ($filter['store_search'] == 3) {
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . $this->dscRepository->mysqlLikeQuote($filter['store_keyword']) . '%');
                                    if ($filter['store_type']) {
                                        $query->where('shop_name_suffix', $filter['store_type']);
                                    }
                                }
                            });
                        });
                    } elseif ($filter['store_search'] == 4) {
                        $row = $row->where('user_id', 0);
                    }
                }
            }
        }

        /* 推荐类型 */
        switch ($filter['intro_type']) {
            case 'is_best':
                $row = $row->where('is_best', 1);
                break;
            case 'is_hot':
                $row = $row->where('is_hot', 1);
                break;
            case 'is_new':
                $row = $row->where('is_new', 1);
                break;
            case 'is_promote':
                $row = $row->where('is_promote', 1)
                    ->where('promote_price', '>', 0);
                break;
            case 'is_distribution':
                $row = $row->where('is_distribution', 1);
                break;
            case 'all_type':
                $row = $row->where(function ($query) use ($today) {
                    $query = $query->orWhere('is_best', 1)
                        ->orWhere('is_hot', 1)
                        ->orWhere('is_new', 1);

                    $query->orWhere(function ($query) use ($today) {
                        $query->where('is_promote', 1)
                            ->where('promote_price', '>', 0)
                            ->where('promote_start_date', '<=', $today)
                            ->where('promote_end_date', '>=', $today);
                    });
                });
        }

        /* 关键字 */
        if (!empty($filter['keyword'])) {
            $row = $row->where(function ($query) use ($filter) {
                $query->where('goods_sn', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['keyword']) . '%')
                    ->orWhere('goods_name', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['keyword']) . '%')
                    ->orWhere('bar_code', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['keyword']) . '%');
            });
        }

        if ($real_goods > -1 && $real_division == 0 && $is_delete == 0 && !isset($_REQUEST['is_on_sale'])) {
            $row = $row->where('is_real', $real_goods);
        }

        /* 供货商 */
        if (!empty($filter['suppliers_id'])) {
            $row = $row->where('suppliers_id', $filter['suppliers_id']);
        }

        /*平台后台--审核商品状态 */
        if ($seller['ru_id'] == 0) {
            if ($filter['review_status'] > 0) {
                if ($filter['review_status'] == 3) {
                    $row = $row->whereIn('review_status', [3, 4, 5]);
                } else {
                    $row = $row->where('review_status', $filter['review_status']);
                }
            } else {
                $row = $row->whereIn('review_status', [1, 2, 3, 4, 5]);
            }
        }

        /* 是否显示筛选 */
        if ($filter['is_show'] >= 0) {
            $row = $row->where('is_show', $filter['is_show']);
        }

        /* 起始页通过商品一览点击进入自营/商家商品判断条件 */
        if (!empty($_REQUEST['self']) && $_REQUEST['self'] == 1) {
            $row = $row->where('user_id', 0);
            $filter['self'] = 1;
        } elseif (!empty($_REQUEST['merchants']) && $_REQUEST['merchants'] == 1) {
            $row = CommonRepository::constantMaxId($row, 'user_id');
            $filter['merchants'] = 1;
        }

        /* 供货商 */
        if (!empty($filter['suppliers_id'])) {
            $row = $row->where('suppliers_id', $filter['suppliers_id']);
        }


        if ($filter['cat_type'] != 'seller' && $filter['cat_id']) {
            $children = $this->categoryService->getCatListChildren($filter['cat_id']);

            $row = $row->where(function ($query) use ($children) {
                $query->whereIn('cat_id', $children)
                    ->orWhere(function ($query) use ($children) {
                        $query->whereHasIn('getGoodsCat', function ($query) use ($children) {
                            $query->whereIn('cat_id', $children);
                        });
                    });
            });
        }

        $res = $record_count = $row;
        /* 记录总数 */
        $filter['record_count'] = $record_count->count('goods_id');

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $select = [
            'goods_id', 'goods_thumb', 'goods_name', 'user_id', 'brand_id', 'goods_type', 'goods_sn', 'shop_price',
            'is_on_sale', 'is_best', 'is_new', 'is_show', 'is_hot', 'sort_order', 'goods_number', 'integral', 'commission_rate',
            'is_promote', 'model_price', 'model_inventory', 'model_attr', 'review_status', 'review_content', 'store_best',
            'store_new', 'store_hot', 'is_real', 'is_shipping', 'stages', 'goods_thumb',
            'is_alone_sale', 'is_xiangou', 'promote_end_date', 'xiangou_end_date', 'bar_code', 'freight', 'tid'
        ];

        if (file_exists(MOBILE_DRP)) {
            $select = BaseRepository::getArrayPush($select, 'is_distribution');
        }

        $res = $res->addSelect($select);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);
        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($goods_id, '*', 'goods_id');

            $goodsAttrList = self::goodsAttrList($goodsAttrList);
            $extendList = GoodsDataHandleService::goodsExtendList($goods_id);

            $tid = BaseRepository::getKeyPluck($res, 'tid');
            $goodsTransportList = GoodsDataHandleService::getGoodsTransportDataList($tid);

            $brand_id = BaseRepository::getKeyPluck($res, 'brand_id');
            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);

            $seller_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

            foreach ($res as $key => $val) {

                $shop_information = $merchantList[$val['user_id']] ?? []; //通过ru_id获取到店铺信息;

                /* 获取商品属性数量 */
                $attrList = $goodsAttrList[$val['goods_id']] ?? [];
                $res[$key]['is_attr'] = BaseRepository::getArrayCount($attrList);

                $res[$key]['user_name'] = $shop_information['shop_name'] ?? ''; //店铺名称
                $res[$key]['brand_name'] = $brandList[$val['brand_id']]['brand_name'] ?? '';

                if ($res[$key]['goods_type'] == 0 && $res[$key]['is_attr'] > 0) {
                    Products::where('goods_id', $val['goods_id'])->delete();
                    ProductsArea::where('goods_id', $val['goods_id'])->delete();
                    ProductsWarehouse::where('goods_id', $val['goods_id'])->delete();
                    GoodsAttr::where('goods_id', $val['goods_id'])->delete();
                }

                if ($res[$key]['freight'] == 2) {
                    $res[$key]['transport'] = $goodsTransportList[$val['tid']] ?? [];
                }

                $res[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);

                //商品扩展信息
                $res[$key]['goods_extend'] = $extendList[$val['goods_id']] ?? [];

                $res[$key]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $val['goods_id']], $val['goods_name']);
            }
        }

        return ['goods' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 获取商品属性列表
     *
     * @param array $goodsAttrList
     * @return array
     */
    protected static function goodsAttrList($goodsAttrList = [])
    {
        $arr = [];
        if ($goodsAttrList) {
            $list = BaseRepository::getArrayCollapse($goodsAttrList);
            $attr_id = BaseRepository::getKeyPluck($list, 'attr_id');

            $attributeList = GoodsDataHandleService::getAttributeDataList($attr_id, null, ['attr_id', 'attr_type']);

            $sql = [
                'where' => [
                    [
                        'name' => 'attr_type',
                        'value' => 0,
                        'condition' => '<>'
                    ]
                ]
            ];
            $attributeList = BaseRepository::getArraySqlGet($attributeList, $sql);

            $attr_id = BaseRepository::getKeyPluck($attributeList, 'attr_id');

            if ($attr_id) {
                foreach ($goodsAttrList as $key => $row) {

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'attr_id',
                                'value' => $attr_id
                            ]
                        ]
                    ];
                    $arr[$key] = BaseRepository::getArraySqlGet($row, $sql, 1);
                }
            }
        }

        return $arr;
    }

    /**
     * 获取商品详情
     *
     * @param int $goods_id
     * @return array
     */
    public function getGoodsDetail($goods_id = 0)
    {
        $goodsinfo = [];

        if ($goods_id > 0) {
            $row = Goods::where('goods_id', $goods_id);

            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {
                if (isset($row['user_cat']) && !empty($row['user_cat'])) {
                    $cat_info = MerchantsCategory::catInfo($row['user_cat']);
                    $cat_info = BaseRepository::getToArrayFirst($cat_info);

                    $cat_info['is_show_merchants'] = $cat_info['is_show'];
                    $row['user_cat_name'] = $cat_info['cat_name'];
                }
                $row['category'] = get_every_category($row['cat_id']);
                $row['goods_video_path'] = $this->dscRepository->getImagePath($row['goods_video']);
                // 店铺名称
                $row['shop_name'] = $this->merchantCommonService->getShopName($row['user_id'], 1);
                // 供应商名称
                $row['suppliers_name'] = '';
                if ($row['suppliers_id'] > 0 && file_exists(SUPPLIERS)) {
                    $row['suppliers_name'] = \App\Modules\Suppliers\Models\Suppliers::where('suppliers_id', $row['suppliers_id'])->value('suppliers_name');
                }
                // 品牌名称
                $row['brand_name'] = '';
                if ($row['brand_id'] > 0) {
                    $row['brand_name'] = Brand::where('brand_id', $row['brand_id'])->value('brand_name');
                }
                // 固定运费价格格式化
                $row['shipping_fee'] = $this->dscRepository->getPriceFormat($row['shipping_fee']); //店铺名称
                // 运费模板
                if ($row['freight'] == 2) {
                    $row['goods_transport'] = GoodsTransport::where('tid', $row['tid'])->where('ru_id', $row['user_id'])->value('title');
                    $row['goods_transport'] = $row['goods_transport'] ?? '';
                }
                // 退货标识
                $cause_list = ['0', '1', '2', '3'];
                $goods_cause = [];
                if (!empty($row['goods_cause'])) {
                    $goods_cause = array_intersect(explode(',', $row['goods_cause']), $cause_list);
                }
                $row['goods_cause'] = $goods_cause;
                // 商品主图
                $row['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                //
                $row['member_price'] = $this->get_member_price_list($row['goods_id']);
                // 商品服务
                $row['goods_extend'] = ['is_reality' => 0, 'is_return' => 0, 'is_fast' => 0];
                $goods_extend = GoodsExtend::where('goods_id', $row['goods_id']);
                $row['goods_extend'] = BaseRepository::getToArrayFirst($goods_extend);
                // 促销价
                $row['promote_price_formated'] = $this->dscRepository->getPriceFormat($row['promote_price']);
                $row['promote_start_end'] = $this->startEndFormat($row['promote_start_date'], $row['promote_end_date']);
                // 限购
                $row['xiangou_start_end'] = $this->startEndFormat($row['xiangou_start_date'], $row['xiangou_end_date']);
                // 最小起订量
                $row['minimum_start_end'] = $this->startEndFormat($row['minimum_start_date'], $row['minimum_end_date']);
                // 关联商品
                $row['link_goods'] = LinkGoods::where('link_goods_id', $row['goods_id'])->count();
                // 关联文章
                $row['link_article'] = GoodsArticle::where('goods_id', $row['goods_id'])->count();
                // 关联地区
                $row['link_area'] = LinkAreaGoods::where('goods_id', $row['goods_id'])->count();
                // 商品配件
                $row['link_group'] = GroupGoods::where('parent_id', $row['goods_id'])->where('group_id', 1)->count();
                // 商品相册
                $row['goods_gallery'] = app(GoodsGalleryService::class)->getGoodsGallery($goods_id);
                // 商品规格
                $product_list = get_goods_product_list($row['goods_id'], $row['model_attr'], 0, 0, 0, false);
                $row['product_list'] = $this->productListFormat($product_list);
                // 本店价
                $row['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                // 市场价
                $row['market_price_formated'] = $this->dscRepository->getPriceFormat($row['market_price']);
                // 成本价
                $row['cost_price_formated'] = $this->dscRepository->getPriceFormat($row['cost_price']);
                // 商家信息
                $shopinfo = $this->merchantCommonService->getShopName($row['user_id']);
                $shopinfo['logo_thumb'] = $this->dscRepository->getImagePath($shopinfo['logo_thumb']);
                $shopinfo['mobile_hide'] = !empty($shopinfo['mobile']) ? substr_replace($shopinfo['mobile'], '****', 3, 4) : '';
                $row['shopinfo'] = $shopinfo;
                // 商品二维码
                $row['goods_qrcode'] = app(GoodsService::class)->getGoodsQrcode($row);
                // 商品分期数
                $row['stages'] = !empty($row['stages']) ? $this->stagesFormat($row['stages']) : '';
                // 商品PC链接
                $row['goods_url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                // 商品PC、H5详情
                if (config('shop.open_oss') == 1) {
                    $bucket_info = $this->dscRepository->getBucketInfo();
                    $endpoint = $bucket_info['endpoint'];
                } else {
                    $endpoint = url('/');
                }
                if (!empty($row['goods_desc'])) {
                    $desc_preg = get_goods_desc_images_preg($endpoint, $row['goods_desc']);
                    $row['goods_desc'] = $desc_preg['goods_desc'];
                }

                if (!empty($row['desc_mobile'])) {
                    $desc_preg = get_goods_desc_images_preg($endpoint, $row['desc_mobile']);
                    $row['desc_mobile'] = $desc_preg['goods_desc'];
                }

                $goodsinfo = $row;
            }
        }

        return $goodsinfo;
    }

    /**
     * 格式化时间段
     *
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @return string 格式化时间段
     */
    private function startEndFormat($start, $end, $format = 'Y-m-d H:i:s', $link = '~')
    {
        $start_time = TimeRepository::getLocalDate($format, $start);
        $end_time = TimeRepository::getLocalDate($format, $end);

        return $start_time && $end_time ? $start_time . $link . $end_time : '';
    }

    /**
     * 格式化货品列表
     *
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @return string 格式化时间段
     */
    private function productListFormat($product_list = [])
    {
        $new_list = [];
        if ($product_list) {
            foreach ($product_list as $k => $v) {
                $product_list[$k]['product_price_formated'] = $this->dscRepository->getPriceFormat($v['product_price']);
                $product_list[$k]['product_cost_price_formated'] = $this->dscRepository->getPriceFormat($v['product_cost_price']);
                $product_list[$k]['product_promote_price_formated'] = $this->dscRepository->getPriceFormat($v['product_promote_price']);
                $product_list[$k]['product_market_price_formated'] = $this->dscRepository->getPriceFormat($v['product_market_price']);
            }

            $new_list = $product_list;
        }

        return $new_list;
    }

    /**
     * 格式化分期数
     *
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @return string 格式化时间段
     */
    private function stagesFormat($stages = '')
    {
        $string = '';

        if (!empty($stages)) {
            $stages = unserialize($stages);
            if (!empty($stages)) {
                foreach ($stages as $k => $val) {
                    $string .= __('admin::goods.by_stages_type.' . $k) . '&nbsp;';
                }
            }
        }

        return trim($string);
    }

    /**
     * 验证商品参与活动信息
     *
     * @param int $goods_id
     * @param int $ru_id
     * @return array
     * @throws \Exception
     */
    public function goodsAddActivity($goods_id = 0, $ru_id = 0)
    {
        $arr = [];
        if (!empty($goods_id)) {
            $snatch = [];
            $group = [];
            $auction = [];
            $package = [];
            $seckill = [];
            $presale = [];
            $team = [];
            $bargain = [];

            $goods_id = BaseRepository::getExplode($goods_id);

            $res = GoodsActivity::whereIn('goods_id', $goods_id);
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {

                $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goodsId
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);

                if ($res) {
                    foreach ($res as $key => $data) {
                        switch ($data['act_type']) {
                            case GAT_SNATCH: //夺宝奇兵
                                $arr['url'] = 'snatch.php?act=edit&id=' . $data['act_id'];
                                $arr['activity_name'] = lang('admin::goods.snatch_activity');
                                $team[] = $arr;
                                break;

                            case GAT_GROUP_BUY: //团购
                                $arr['url'] = 'group_buy.php?act=edit&id=' . $data['act_id'];
                                $arr['activity_name'] = lang('admin::goods.group_buy_activity');
                                $team[] = $arr;
                                break;

                            case GAT_AUCTION: //拍卖
                                $arr['url'] = 'auction.php?act=edit&id=' . $data['act_id'];
                                $arr['activity_name'] = lang('admin::goods.auction_activity');
                                $team[] = $arr;
                                break;
                        }
                    }
                }
            }

            //礼包
            $res = GoodsActivity::whereHasIn('getPackageGoods', function ($query) use ($goods_id) {
                $query->whereIn('goods_id', $goods_id);
            });
            $res = BaseRepository::getToArrayGet($res);
            if ($res) {

                $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goodsId
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);

                if ($res) {
                    foreach ($res as $data) {
                        switch ($data['act_type']) {
                            case GAT_PACKAGE: //礼包
                                $arr['url'] = 'package.php?act=edit&id=' . $data['act_id'];
                                $arr['activity_name'] = lang('admin::goods.package_activity');
                                $package[] = $arr;
                                break;
                        }
                    }
                }
            }

            // 秒杀
            $res = SeckillGoods::whereIn('goods_id', $goods_id);
            $res = BaseRepository::getToArrayGet($res);
            if ($res) {

                $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goodsId
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);

                if ($res) {
                    foreach ($res as $key => $val) {
                        $arr['url'] = 'seckill.php?act=edit&sec_id=' . $val['sec_id'];
                        $arr['activity_name'] = lang('admin::goods.seckill_activity');
                        $seckill[] = $arr;
                    }
                }
            }

            // 预售
            $res = PresaleActivity::whereIn('goods_id', $goods_id);
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {

                $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goodsId
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);

                if ($res) {
                    foreach ($res as $key => $val) {
                        $arr['url'] = 'presale.php?act=edit&id=' . $val['act_id'];
                        $arr['activity_name'] = lang('admin::goods.presale_activity');
                        $presale[] = $arr;
                    }
                }
            }

            // 拼团活动
            if (file_exists(MOBILE_TEAM)) {
                $res = TeamGoods::whereIn('goods_id', $goods_id)->where('is_team', 1);
                $res = BaseRepository::getToArrayGet($res);
                if ($res) {

                    $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                    $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                    $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $goodsId
                            ]
                        ]
                    ];
                    $res = BaseRepository::getArraySqlGet($res, $sql);

                    if ($res) {
                        foreach ($res as $key => $val) {
                            $arr['url'] = route('admin/team/addgoods', ['id' => $val['id']]);
                            if ($ru_id > 0) {
                                $arr['url'] = 'team.php?act=edit&id=' . $val['id'];
                            }
                            $arr['activity_name'] = lang('admin::goods.team_activity');
                            $team[] = $arr;
                        }
                    }
                }
            }

            // 砍价
            if (file_exists(MOBILE_BARGAIN)) {
                $res = BargainGoods::where('goods_id', $goods_id)->where('status', 0)->where('is_delete', 0);
                $res = BaseRepository::getToArrayGet($res);

                if ($res) {

                    $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                    $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                    $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $goodsId
                            ]
                        ]
                    ];
                    $res = BaseRepository::getArraySqlGet($res, $sql);

                    if ($res) {
                        foreach ($res as $key => $val) {
                            $arr['url'] = route('admin/bargain/addgoods', ['id' => $val['id']]);
                            if ($ru_id > 0) {
                                $arr['url'] = 'bargain.php?act=edit&id=' . $val['id'];
                            }
                            $arr['activity_name'] = lang('admin::goods.bargain_activity');
                            $bargain[] = $arr;
                        }
                    }
                }
            }

            $arr = array_merge($snatch, $group, $auction, $package, $seckill, $presale, $team, $bargain);

        }

        return $arr;
    }


    /**
     * 后台商品关键词管理
     *
     * @param int $ru_id
     * @return array
     */
    public function getGoodsKeywordList($ru_id = 0)
    {
        $param_str = '';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        $filter['ru_id'] = request()->has('ru_id') ? (int)request()->input('ru_id', 0) : $ru_id;

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $row = KeywordList::where('ru_id', $filter['ru_id']);

        if ($filter['keyword']) {
            $row = $row->where('name', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['keyword']) . '%');
        }

        $res = $record_count = $row;

        /* 记录总数 */
        $filter['record_count'] = $record_count->count('id');

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);
        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            if ($filter['ru_id'] > 0) {
                $cat_id = BaseRepository::getKeyPluck($res, 'cat_id');
                $catList = MerchantCategoryDataHandleService::getCategoryDataList($cat_id, ['cat_id', 'cat_name']);
            } else {
                $cat_id = BaseRepository::getKeyPluck($res, 'cat_id');
                $catList = CategoryDataHandleService::getCategoryDataList($cat_id, ['cat_id', 'cat_name']);
            }

            foreach ($res as $key => $val) {
                $res[$key]['cat_name'] = $catList[$val['cat_id']]['cat_name'] ?? '';
                $res[$key]['update_time'] = $val['update_time'] ? TimeRepository::getLocalDate(config('shop.time_format'), $val['update_time']) : '';
                $res[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['add_time']);
            }
        }

        return ['keyword' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 添加编辑选择关键词
     *
     * @param int $ru_id
     * @return array
     */
    public function getGoodsSelectKeyword($ru_id = 0)
    {
        $filter['goods_id'] = (int)request()->input('goods_id', 0);
        $filter['category_id'] = request()->input('category_id', 0);
        $filter['keyword'] = request()->input('keyword', '');
        $filter['ru_id'] = request()->has('ru_id') ? (int)request()->input('ru_id', 0) : $ru_id;

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['full_page'] = 1;
        if (request()->exists('keyword')) {
            $filter['full_page'] = 0;
        }

        $res = KeywordList::where('ru_id', $filter['ru_id']);

        if ($filter['keyword']) {
            $res = $res->where('name', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['keyword']) . '%');
        }

        if ($filter['category_id']) {
            $res = $res->where('cat_id', $filter['category_id']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goodsList = GoodsKeyword::where('goods_id', $filter['goods_id']);
            $goodsList = BaseRepository::getToArrayGet($goodsList);

            foreach ($res as $key => $val) {
                $sql = [
                    'where' => [
                        [
                            'name' => 'keyword_id',
                            'value' => $val['id']
                        ],
                        [
                            'name' => 'goods_id',
                            'value' => $filter['goods_id']
                        ]
                    ]
                ];
                $keyword = BaseRepository::getArraySqlFirst($goodsList, $sql);

                $res[$key]['is_checked'] = 0;
                if ($keyword && !empty($filter['goods_id'])) {
                    $res[$key]['is_checked'] = 1;
                }
            }
        }

        return ['keyword_list' => $res, 'filter' => $filter, 'page_count' => 0, 'record_count' => 0];
    }

    /**
     * 商品详情关键词信息
     *
     * @param array $goods
     * @return array
     */
    public function getGoodsKeywordInfo($goods = [])
    {
        $goods_id = $goods['goods_id'] ?? 0;

        $res = GoodsKeyword::where('goods_id', $goods_id)
            ->with([
                'getKeywordList'
            ]);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            /* 新数据 */
            foreach ($res as $key => $val) {

                $keyword = $val['get_keyword_list'] ?? [];

                $res[$key]['name'] = $keyword['name'] ?? '';

                unset($res[$key]['get_keyword_list']);
            }
        } else {
            /* 兼容数据 */
            $keywords = isset($goods['keywords']) && !empty($goods['keywords']) ? BaseRepository::getExplode($goods['keywords'], ' ') : [];
            $res = [];
            foreach ($keywords as $key => $val) {
                $res[$key]['id'] = 0;
                $res[$key]['goods_id'] = $goods_id;
                $res[$key]['name'] = $val;
            }
        }

        return $res;
    }

}
