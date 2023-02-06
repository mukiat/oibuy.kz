<?php


use App\Libraries\Image;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsFittingService;
use App\Services\Goods\GoodsManageService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 取得会员等级列表
 * @return  array   会员等级列表
 */
function get_user_rank_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['dsc']->table('user_rank') .
        " ORDER BY min_points";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得某商品的会员价格列表
 * @param int $goods_id 商品编号
 * @return  array   会员价格列表 user_rank => user_price
 */
function get_member_price_list($goods_id)
{
    /* 取得会员价格 */
    $price_list = [];
    $sql = "SELECT user_rank, user_price FROM " .
        $GLOBALS['dsc']->table('member_price') .
        " WHERE goods_id = '$goods_id'";
    $res = $GLOBALS['db']->query($sql);
    foreach ($res as $row) {
        $price_list[$row['user_rank']] = $row['user_price'];
    }

    return $price_list;
}

/**
 * 插入或更新商品属性
 *
 * @param int $goods_id 商品编号
 * @param array $id_list 属性编号数组
 * @param array $is_spec_list 是否规格数组 'true' | 'false'
 * @param array $value_price_list 属性值数组
 * @return  array                       返回受到影响的goods_attr_id数组
 */
function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list)
{
    $goods_attr_id = [];

    /* 循环处理每个属性 */
    foreach ($id_list as $key => $id) {
        $is_spec = $is_spec_list[$key];
        if ($is_spec == 'false') {
            $value = $value_price_list[$key];
            $price = '';
        } else {
            $value_list = [];
            $price_list = [];
            if ($value_price_list[$key]) {
                $vp_list = explode(chr(13), $value_price_list[$key]);
                foreach ($vp_list as $v_p) {
                    $arr = explode(chr(9), $v_p);
                    $value_list[] = $arr[0];
                    $price_list[] = $arr[1];
                }
            }
            $value = join(chr(13), $value_list);
            $price = join(chr(13), $price_list);
        }

        // 插入或更新记录
        $sql = "SELECT goods_attr_id FROM " . $GLOBALS['dsc']->table('goods_attr') . " WHERE goods_id = '$goods_id' AND attr_id = '$id' AND attr_value = '$value' LIMIT 0, 1";
        $result_id = $GLOBALS['db']->getOne($sql);
        if (!empty($result_id)) {
            $sql = "UPDATE " . $GLOBALS['dsc']->table('goods_attr') . "
                    SET attr_value = '$value'
                    WHERE goods_id = '$goods_id'
                    AND attr_id = '$id'
                    AND goods_attr_id = '$result_id'";

            $goods_attr_id[$id] = $result_id;
        } else {
            $sql = "INSERT INTO " . $GLOBALS['dsc']->table('goods_attr') . " (goods_id, attr_id, attr_value, attr_price) " .
                "VALUES ('$goods_id', '$id', '$value', '$price')";
        }

        $GLOBALS['db']->query($sql);

        if (isset($goods_attr_id[$id]) && empty($goods_attr_id[$id])) {
            $goods_attr_id[$id] = $GLOBALS['db']->insert_id();
        }
    }

    return $goods_attr_id;
}

/**
 * 保存某商品的会员价格
 * @param int $goods_id 商品编号
 * @param array $rank_list 等级列表
 * @param array $price_list 价格列表
 * @return  void
 */
function handle_member_price($goods_id, $rank_list, $price_list)
{
    /* 循环处理每个会员等级 */
    foreach ($rank_list as $key => $rank) {
        /* 会员等级对应的价格 */
        $price = $price_list[$key];

        // 插入或更新记录
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['dsc']->table('member_price') .
            " WHERE goods_id = '$goods_id' AND user_rank = '$rank'";
        if ($GLOBALS['db']->getOne($sql) > 0) {
            /* 如果会员价格是小于0则删除原来价格，不是则更新为新的价格 */
            if ($price < 0) {
                $sql = "DELETE FROM " . $GLOBALS['dsc']->table('member_price') .
                    " WHERE goods_id = '$goods_id' AND user_rank = '$rank' LIMIT 1";
            } else {
                $sql = "UPDATE " . $GLOBALS['dsc']->table('member_price') .
                    " SET user_price = '$price' " .
                    "WHERE goods_id = '$goods_id' " .
                    "AND user_rank = '$rank' LIMIT 1";
            }
        } else {
            if ($price == -1) {
                $sql = '';
            } else {
                $sql = "INSERT INTO " . $GLOBALS['dsc']->table('member_price') . " (goods_id, user_rank, user_price) " .
                    "VALUES ('$goods_id', '$rank', '$price')";
            }
        }

        if ($sql) {
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 保存某商品的扩展分类
 * @param int $goods_id 商品编号
 * @param array $cat_list 分类编号数组
 * @return  void
 */
function handle_other_cat($goods_id, $cat_list)
{
    /* 查询现有的扩展分类 */
    $sql = "SELECT cat_id FROM " . $GLOBALS['dsc']->table('goods_cat') .
        " WHERE goods_id = '$goods_id'";
    $exist_list = $GLOBALS['db']->getCol($sql);

    /* 删除不再有的分类 */
    $delete_list = array_diff($exist_list, $cat_list);
    if ($delete_list) {
        $sql = "DELETE FROM " . $GLOBALS['dsc']->table('goods_cat') .
            " WHERE goods_id = '$goods_id' " .
            "AND cat_id " . db_create_in($delete_list);
        $GLOBALS['db']->query($sql);
    }

    /* 添加新加的分类 */
    $add_list = array_diff($cat_list, $exist_list, [0]);
    foreach ($add_list as $cat_id) {
        // 插入记录
        $sql = "INSERT INTO " . $GLOBALS['dsc']->table('goods_cat') .
            " (goods_id, cat_id) " .
            "VALUES ('$goods_id', '$cat_id')";
        $GLOBALS['db']->query($sql);
    }
}

/**
 * 保存某商品的关联商品
 * @param int $goods_id
 * @return  void
 */
function handle_link_goods($goods_id)
{
    $sql = "UPDATE " . $GLOBALS['dsc']->table('link_goods') . " SET " .
        " goods_id = '$goods_id' " .
        " WHERE goods_id = '0'" .
        " AND admin_id = '" . session('admin_id') . "'";
    $GLOBALS['db']->query($sql);

    $sql = "UPDATE " . $GLOBALS['dsc']->table('link_goods') . " SET " .
        " link_goods_id = '$goods_id' " .
        " WHERE link_goods_id = '0'" .
        " AND admin_id = '" . session('admin_id') . "'";
    $GLOBALS['db']->query($sql);
}

/**
 * 保存某商品的配件
 * @param int $goods_id
 * @return  void
 */
function handle_group_goods($goods_id)
{
    $sql = "UPDATE " . $GLOBALS['dsc']->table('group_goods') . " SET " .
        " parent_id = '$goods_id' " .
        " WHERE parent_id = '0'" .
        " AND admin_id = '" . session('admin_id') . "'";
    $GLOBALS['db']->query($sql);
}

/**
 * 保存某商品的关联文章
 * @param int $goods_id
 * @return  void
 */
function handle_goods_article($goods_id)
{
    $sql = "UPDATE " . $GLOBALS['dsc']->table('goods_article') . " SET " .
        " goods_id = '$goods_id' " .
        " WHERE goods_id = '0'" .
        " AND admin_id = '" . session('admin_id') . "'";
    $GLOBALS['db']->query($sql);
}

/**
 * 保存某商品的关联地区 create by qin
 * @param int $goods_id
 * @return  void
 */
function handle_goods_area($goods_id)
{
    $sql = "UPDATE " . $GLOBALS['dsc']->table('link_area_goods') . " SET " .
        " goods_id = '$goods_id' " .
        " WHERE goods_id = '0'" .
        " AND ru_id = (SELECT ru_id FROM " . $GLOBALS['dsc']->table('admin_user') . "  WHERE user_id = '" . session('admin_id') . "') ";
    $GLOBALS['db']->query($sql);
}

/**
 * 保存某商品的相册图片
 * @param int $goods_id
 * @param array $image_files
 * @param array $image_descs
 * @return  void
 */
function handle_gallery_image($goods_id, $image_files, $image_descs, $image_urls, $single_id = 0, $files_type = 0)
{
    $GLOBALS['image'] = new Image();

    if ($files_type == 0) {
        $files_type = 'single_id';
    } elseif ($files_type = 1) {
        $files_type = 'dis_id';
    }

    $admin_id = get_admin_id();
    $admin_temp_dir = "seller";
    $admin_temp_dir = storage_public("temp" . '/' . $admin_temp_dir . '/' . "admin_" . $admin_id);

    // 如果目标目录不存在，则创建它
    if (!file_exists($admin_temp_dir)) {
        make_dir($admin_temp_dir);
    }

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
            $img_original = app(Image::class)->upload_image($upload, ['type' => 1]);
            if ($img_original === false) {
                return sys_msg(app(Image::class)->error_msg(), 1, [], false);
            }
            $img_url = $img_original;

            // 生成缩略图
            if ($proc_thumb) {
                $thumb_url = app(Image::class)->make_thumb(['img' => $img_original, 'type' => 1], $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
                $thumb_url = is_string($thumb_url) ? $thumb_url : '';
            } else {
                $thumb_url = $img_original;
            }

            // 如果服务器支持GD 则添加水印
            if ($proc_thumb && gd_version() > 0) {
                $pos = strpos(basename($img_original), '.');
                $newname = dirname($img_original) . '/' . app(Image::class)->random_filename() . substr(basename($img_original), $pos);
                copy($img_original, $newname);
                $img_url = $newname;

                app(Image::class)->add_watermark($img_url, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']);
            }

            /* 重新格式化图片名称 */
            if ($goods_id == 0) {
                $img_original = app(GoodsManageService::class)->reformatImageName('gallery', $single_id, $img_original, 'source');
                $img_url = app(GoodsManageService::class)->reformatImageName('gallery', $single_id, $img_url, 'goods');
                $thumb_url = app(GoodsManageService::class)->reformatImageName('gallery_thumb', $single_id, $thumb_url, 'thumb');
            } else {
                $img_original = app(GoodsManageService::class)->reformatImageName('gallery', $goods_id, $img_original, 'source');
                $img_url = app(GoodsManageService::class)->reformatImageName('gallery', $goods_id, $img_url, 'goods');
                $thumb_url = app(GoodsManageService::class)->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
            }

            $sql = "INSERT INTO " . $GLOBALS['dsc']->table('goods_gallery') . " (goods_id, img_url, img_desc, thumb_url, img_original, " . $files_type . ") " .
                "VALUES ('$goods_id', '$img_url', '0', '$thumb_url', '$img_original', '$single_id')";
            $GLOBALS['db']->query($sql);
            $thumb_img_id[] = $GLOBALS['db']->insert_id();
            /* 不保留商品原图的时候删除原图 */
            if ($proc_thumb && !$GLOBALS['_CFG']['retain_original_img'] && !empty($img_original)) {
                $GLOBALS['db']->query("UPDATE " . $GLOBALS['dsc']->table('goods_gallery') . " SET img_original='' WHERE `goods_id`='$goods_id'");
                @unlink('../' . $img_original);
            }
        } elseif (!empty($image_urls[$key]) && ($image_urls[$key] != $GLOBALS['_LANG']['img_file']) && ($image_urls[$key] != 'http://') && (strpos($image_urls[$key], 'http://') !== false || strpos($image_urls[$key], 'https://') !== false)) {
            if (app(DscRepository::class)->getHttpBasename($image_urls[$key], $admin_temp_dir)) {
                $image_url = trim($image_urls[$key]);
                //定义原图路径
                $down_img = $admin_temp_dir . "/" . basename($image_url);

                $img_wh = app(Image::class)->get_width_to_height($down_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
                $GLOBALS['_CFG']['image_width'] = isset($img_wh['image_width']) ? $img_wh['image_width'] : $GLOBALS['_CFG']['image_width'];
                $GLOBALS['_CFG']['image_height'] = isset($img_wh['image_height']) ? $img_wh['image_height'] : $GLOBALS['_CFG']['image_height'];

                $goods_img = app(Image::class)->make_thumb(['img' => $down_img, 'type' => 1], $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);

                // 生成缩略图
                if ($proc_thumb) {
                    $thumb_url = app(Image::class)->make_thumb(['img' => $down_img, 'type' => 1], $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
                    $thumb_url = app(GoodsManageService::class)->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                } else {
                    $thumb_url = app(Image::class)->make_thumb(['img' => $down_img, 'type' => 1]);
                    $thumb_url = app(GoodsManageService::class)->reformatImageName('gallery_thumb', $goods_id, $thumb_url, 'thumb');
                }

                $img_original = app(GoodsManageService::class)->reformatImageName('gallery', $goods_id, $down_img, 'source');
                $img_url = app(GoodsManageService::class)->reformatImageName('gallery', $goods_id, $goods_img, 'goods');

                $sql = "INSERT INTO " . $GLOBALS['dsc']->table('goods_gallery') . " (goods_id, img_url, img_desc, thumb_url, img_original, " . $files_type . ") " .
                    "VALUES ('$goods_id', '$img_url', '0', '$thumb_url', '$img_original', '$single_id')";
                $GLOBALS['db']->query($sql);
                $thumb_img_id[] = $GLOBALS['db']->insert_id();
                @unlink($down_img);
            }
        }

        if ($img_url) {
            app(DscRepository::class)->getOssAddFile([$img_url, $thumb_url, $img_original]);
        }
    }
}

/**
 * 修改商品某字段值
 * @param string $goods_id 商品编号，可以为多个，用 ',' 隔开
 * @param string $field 字段名
 * @param string $value 字段值
 * @return  bool
 */
function update_goods($goods_id, $field, $value, $content = '', $type = '')
{ //ecmoban模板堂 --zhuo  $content = ''
    if ($goods_id) {
        /* 清除缓存 */
        clear_cache_files();

        $date = ['model_attr'];

        $where = "goods_id = '$goods_id'";
        $model_attr = get_table_date('goods', $where, $date, 2);

        //ecmoban模板堂 --zhuo start
        $table = "goods";
        if ($type == 'updateNum') {
            if ($model_attr == 1) {
                $table = "warehouse_goods";
                $field = 'region_number';
            } elseif ($model_attr == 2) {
                $table = "warehouse_area_goods";
                $field = 'region_number';
            }
        }

        if ($value == 2 && !empty($content)) {
            $content = "review_content = '$content', ";
        }
        //ecmoban模板堂 --zhuo end

        if ($field == 'is_on_sale') {
            if ($value == 1) {
                $sql = "SELECT act_id FROM " . $GLOBALS['dsc']->table('presale_activity') . " WHERE goods_id " . db_create_in($goods_id);
                if ($GLOBALS['db']->getOne($sql, true)) {
                    $GLOBALS['db']->query("DELETE FROM " . $GLOBALS['dsc']->table('presale_activity') . " WHERE goods_id " . db_create_in($goods_id));
                    $GLOBALS['db']->query("DELETE FROM " . $GLOBALS['dsc']->table('cart') . " WHERE goods_id " . db_create_in($goods_id));
                }
            } else {
                $sql = "DELETE FROM " . $GLOBALS['dsc']->table('cart') . " WHERE goods_id " . db_create_in($goods_id);
                $GLOBALS['db']->query($sql);
            }
        }
        if ($field == 'review_status' && $value < 3) {
            $sql = "DELETE FROM " . $GLOBALS['dsc']->table('cart') . " WHERE goods_id " . db_create_in($goods_id);
            $GLOBALS['db']->query($sql);
        }

        $sql = "UPDATE " . $GLOBALS['dsc']->table($table) .
            " SET $field = '$value' , " . $content . " last_update = '" . gmtime() . "' " .
            "WHERE goods_id " . db_create_in($goods_id);
        return $GLOBALS['db']->query($sql);
    } else {
        return false;
    }
}

/**
 * 商品货号是否重复
 *
 * @param string $goods_sn 商品货号；请在传入本参数前对本参数进行SQl脚本过滤
 * @param int $goods_id 商品id；默认值为：0，没有商品id
 * @return  bool                        true，重复；false，不重复
 */
function check_goods_sn_exist($goods_sn, $goods_id = 0)
{
    $goods_sn = trim($goods_sn);
    $goods_id = intval($goods_id);
    if (strlen($goods_sn) == 0) {
        return true;    //重复
    }

    if (empty($goods_id)) {
        $sql = "SELECT goods_id FROM " . $GLOBALS['dsc']->table('goods') . "
                WHERE goods_sn = '$goods_sn'";
    } else {
        $sql = "SELECT goods_id FROM " . $GLOBALS['dsc']->table('goods') . "
                WHERE goods_sn = '$goods_sn'
                AND goods_id <> '$goods_id'";
    }

    $res = $GLOBALS['db']->getOne($sql);

    if (empty($res)) {
        return false;    //不重复
    } else {
        return true;    //重复
    }
}

/**
 * 取得通用属性和某分类的属性，以及某商品的属性值
 * @param int $cat_id 分类编号
 * @param int $goods_id 商品编号
 * @return  array   规格与属性列表
 */
function get_attr_list($cat_id, $goods_id = 0)
{
    if (empty($cat_id)) {
        return [];
    }

    // 查询属性值及商品的属性值
    $sql = "SELECT a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values, v.attr_value, v.attr_price, v.attr_sort, v.attr_checked " .
        "FROM " . $GLOBALS['dsc']->table('attribute') . " AS a " .
        "LEFT JOIN " . $GLOBALS['dsc']->table('goods_attr') . " AS v " .
        "ON v.attr_id = a.attr_id AND v.goods_id = '$goods_id' " .
        "WHERE a.cat_id = " . intval($cat_id) . " OR a.cat_id = 0 " .
        "ORDER BY a.sort_order, a.attr_id, v.goods_attr_id";

    $row = $GLOBALS['db']->GetAll($sql);

    return $row;
}

/**
 * 获取商品类型中包含规格的类型列表
 *
 * @access  public
 * @return  array
 */
function get_goods_type_specifications()
{
    // 查询
    $sql = "SELECT DISTINCT cat_id
            FROM " . $GLOBALS['dsc']->table('attribute') . "
            WHERE attr_type = 1";
    $row = $GLOBALS['db']->GetAll($sql);

    $return_arr = [];
    if (!empty($row)) {
        foreach ($row as $value) {
            $return_arr[$value['cat_id']] = $value['cat_id'];
        }
    }
    return $return_arr;
}

/**
 * 获得指定商品相关的商品
 *
 * @access  public
 * @param integer $goods_id
 * @return  array
 */
function get_linked_goods($goods_id)
{
    $sql = "SELECT lg.link_goods_id AS goods_id, g.goods_name, lg.is_double " .
        "FROM " . $GLOBALS['dsc']->table('link_goods') . " AS lg, " .
        $GLOBALS['dsc']->table('goods') . " AS g " .
        "WHERE lg.goods_id = '$goods_id' " .
        "AND lg.link_goods_id = g.goods_id ";
    if ($goods_id == 0) {
        $sql .= " AND lg.admin_id = '" . session('admin_id') . "'";
    }

    $sql .= " ORDER BY sort ASC ";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row as $key => $val) {
        $linked_type = $val['is_double'] == 0 ? "单向关联" : "双向关联";

        //$row[$key]['goods_name'] = $val['goods_name'] . " -- [$linked_type]";
        $row[$key]['goods_name'] = "<span class='blue'>[$linked_type] </span>" . $val['goods_name'];

        unset($row[$key]['is_double']);
    }

    return $row;
}

/**
 * 获得指定商品的配件
 *
 * @access  public
 * @param integer $goods_id
 * @return  array
 */
function get_group_goods($goods_id)
{
    $sql = "SELECT gg.id, gg.goods_id, gg.group_id, g.goods_name ,gg.goods_price,g.shop_price " .
        "FROM " . $GLOBALS['dsc']->table('group_goods') . " AS gg, " .
        $GLOBALS['dsc']->table('goods') . " AS g " .
        "WHERE gg.parent_id = '$goods_id' " .
        "AND gg.goods_id = g.goods_id ";
    if ($goods_id == 0) {
        $sql .= " AND gg.admin_id = '" . session('admin_id') . "'";
    }
    $sql .= " order by gg.group_id asc, g.goods_id asc"; //by mike add
    $res = $GLOBALS['db']->getAll($sql);

    $group_goods = app(GoodsFittingService::class)->getCfgGroupGoods();

    $arr = [];
    foreach ($res as $key => $row) {
        $arr[$key] = $row;
        if ($group_goods) {
            foreach ($group_goods as $gkey => $group) {
                if ($row['group_id'] == $gkey) {
                    $arr[$key]['group_name'] = $group;
                }
            }
        }
    }

    return $arr;
}

/**
 * 获得商品的关联文章
 *
 * @access  public
 * @param integer $goods_id
 * @return  array
 */
function get_goods_articles($goods_id)
{
    $sql = "SELECT g.article_id, a.title " .
        "FROM " . $GLOBALS['dsc']->table('goods_article') . " AS g, " .
        $GLOBALS['dsc']->table('article') . " AS a " .
        "WHERE g.goods_id = '$goods_id' " .
        "AND g.article_id = a.article_id ";
    if ($goods_id == 0) {
        $sql .= " AND g.admin_id = '" . session('admin_id') . "'";
    }
    $row = $GLOBALS['db']->getAll($sql);

    return $row;
}

/**
 * 获得商品的货品总库存
 *
 * @access      public
 * @params      integer     $goods_id       商品id
 * @params      string      $conditions     sql条件，AND语句开头
 * @return      string number
 */
function product_number_count($goods_id, $conditions = '', $warehouse_id = 0)
{
    //判断商品类型 by wu
    $goods_model = $GLOBALS['db']->getOne(" SELECT model_price FROM " . $GLOBALS['dsc']->table("goods") . " WHERE goods_id = '$goods_id' ");
    if ($goods_model == 1) {
        $table = "products_warehouse";
    } elseif ($goods_model == 2) {
        $table = "products_area";
    } else {
        $table = "products";
    }

    if (empty($goods_id)) {
        return -1;  //$goods_id不能为空
    }

    $sql = "SELECT product_number
            FROM " . $GLOBALS['dsc']->table($table) . "
            WHERE goods_id = '$goods_id'
            " . $conditions;
    $nums = $GLOBALS['db']->getOne($sql);
    $nums = empty($nums) ? 0 : $nums;

    return $nums;
}

/**
 * 获得商品的规格属性值列表
 *
 * @access      public
 * @params      integer         $goods_id
 * @return      array
 */
function product_goods_attr_list($goods_id)
{
    if (empty($goods_id)) {
        return [];  //$goods_id不能为空
    }

    $sql = "SELECT goods_attr_id, attr_value FROM " . $GLOBALS['dsc']->table('goods_attr') . " WHERE goods_id = '$goods_id'";
    $results = $GLOBALS['db']->getAll($sql);

    $return_arr = [];
    foreach ($results as $value) {
        $return_arr[$value['goods_attr_id']] = $value['attr_value'];
    }

    return $return_arr;
}

/**
 * 获得商品已添加的规格列表
 *
 * @access      public
 * @params      integer         $goods_id
 * @return      array
 */
function get_goods_specifications_list($goods_id)
{
    $where = "";
    $admin_id = get_admin_id();
    if (empty($goods_id)) {
        if ($admin_id) {
            $where .= " AND admin_id = '$admin_id'";
        } else {
            return [];  //$goods_id不能为空
        }
    }

    $sql = "SELECT g.goods_attr_id, g.attr_value, g.attr_id, a.attr_name
            FROM " . $GLOBALS['dsc']->table('goods_attr') . " AS g
                LEFT JOIN " . $GLOBALS['dsc']->table('attribute') . " AS a
                    ON a.attr_id = g.attr_id
            WHERE goods_id = '$goods_id'
            AND a.attr_type = 1" . $where .
        " ORDER BY a.sort_order, a.attr_id, g.goods_attr_id";
    $results = $GLOBALS['db']->getAll($sql);

    return $results;
}

/**
 * 获得商品的货品列表
 *
 * @access  public
 * @params  integer $goods_id
 * @params  string  $conditions
 * @return  array
 */
function product_list($goods_id, $conditions = '')
{
    // 如果存在最后一次过滤条件并且使用 重置 REQUEST
    $param_str = 'product_list-' . $goods_id;
    $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

    $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

    $day = getdate();
    $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $filter['goods_id'] = $goods_id;
    $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
    $filter['stock_warning'] = empty($_REQUEST['stock_warning']) ? 0 : intval($_REQUEST['stock_warning']);

    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
        $filter['keyword'] = json_str_iconv($filter['keyword']);
    }
    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'product_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['extension_code'] = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
    $filter['page_count'] = isset($filter['page_count']) ? $filter['page_count'] : 1;

    $where = '';

    /* 库存警告 */
    if ($filter['stock_warning']) {
        $where .= ' AND goods_number <= warn_number ';
    }

    /* 关键字 */
    if (!empty($filter['keyword'])) {
        $where .= " AND (product_sn LIKE '%" . $filter['keyword'] . "%')";
    }

    $where .= $conditions;

    /* 记录总数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['dsc']->table('products') . " AS p WHERE goods_id = $goods_id $where";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    // 存储最后一次过滤条件
    app(DscRepository::class)->setSessionFilter($filter, $param_str);

    $sql = "SELECT product_id, goods_id, goods_attr, product_sn, bar_code, product_price, product_number
            FROM " . $GLOBALS['dsc']->table('products') . " AS g
            WHERE goods_id = $goods_id $where
            ORDER BY $filter[sort_by] $filter[sort_order]";

    $filter['keyword'] = stripslashes($filter['keyword']);

    $row = $GLOBALS['db']->getAll($sql);

    /* 处理规格属性 */
    $goods_attr = product_goods_attr_list($goods_id);
    foreach ($row as $key => $value) {
        $_goods_attr_array = explode('|', $value['goods_attr']);
        if (is_array($_goods_attr_array)) {
            $_temp = '';
            foreach ($_goods_attr_array as $_goods_attr_value) {
                $_temp[] = $goods_attr[$_goods_attr_value];
            }
            $row[$key]['goods_attr'] = $_temp;
        }
    }

    return ['product' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
}

/**
 * 取货品信息
 *
 * @access  public
 * @param int $product_id 货品id
 * @param int $filed 字段
 * @param int $is_attr 属性组
 * @return  array
 */
function get_product_info($product_id, $filed = '', $goods_model = 0, $is_attr = 0)
{
    $return_array = [];

    if (empty($product_id)) {
        return $return_array;
    }

    $filed = trim($filed);
    if (empty($filed)) {
        $filed = '*';
    }

    if ($goods_model == 1) {
        $table = "products_warehouse";
    } elseif ($goods_model == 2) {
        $table = "products_area";
    } else {
        $table = "products";
    }

    $sql = "SELECT $filed FROM  " . $GLOBALS['dsc']->table($table) . " WHERE product_id = '$product_id'";
    $return_array = $GLOBALS['db']->getRow($sql);

    if ($is_attr == 1) {
        if ($return_array['goods_attr']) {
            $goods_attr_id = str_replace("|", ",", $return_array['goods_attr']);
            $return_array['goods_attr'] = get_product_attr_list($goods_attr_id, $return_array['goods_id'], $goods_model, $return_array['warehouse_id'], $return_array['area_id'], $return_array['city_id']);
        }
    }

    return $return_array;
}

/**
 * @param int $goods_attr_id
 * @param int $goods_id
 * @param int $goods_model
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @return mixed
 */
function get_product_attr_list($goods_attr_id = 0, $goods_id = 0, $goods_model = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
    $leftJion = '';
    if ($goods_model == 1) {
        $where = " AND wa.goods_id = ga.goods_id AND warehouse_id = '$warehouse_id' ";
        $leftJion = ' LEFT JOIN ' . $GLOBALS['dsc']->table('warehouse_attr') . ' AS wa ON wa.goods_attr_id = ga.goods_attr_id ' . $where;
        $select = ", wa.attr_price AS attr_price, warehouse_id, wa.id";
    } elseif ($goods_model == 2) {
        $where = " AND waa.goods_id = ga.goods_id AND area_id = '$area_id' ";

        if ($GLOBALS['_CFG']['area_pricetype'] == 1) {
            $where .= " AND city_id = '$area_city'";
        }

        $leftJion = ' LEFT JOIN ' . $GLOBALS['dsc']->table('warehouse_area_attr') . ' AS waa ON waa.goods_attr_id = ga.goods_attr_id ' . $where;
        $select = ", waa.attr_price AS attr_price, area_id, waa.id";
    } else {
        $select = ", ga.attr_price AS attr_price";
    }

    $sql = "SELECT  ga.goods_attr_id, ga.attr_id, ga.attr_value $select FROM  " . $GLOBALS['dsc']->table('goods_attr') . " AS ga " .
        ' LEFT JOIN ' . $GLOBALS['dsc']->table('attribute') . ' AS a ON a.attr_id = ga.attr_id ' .
        $leftJion .
        " WHERE ga.goods_attr_id IN($goods_attr_id) AND ga.goods_id = '$goods_id'" .
        ' ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id';
    $res = $GLOBALS['db']->getAll($sql);

    return $res;
}

/**
 * 检查单个商品是否存在规格
 *
 * @param int $goods_id 商品id
 * @return  bool                          true，存在；false，不存在
 */
function check_goods_specifications_exist($goods_id)
{
    $goods_id = intval($goods_id);

    $sql = "SELECT COUNT(a.attr_id)
            FROM " . $GLOBALS['dsc']->table('attribute') . " AS a, " . $GLOBALS['dsc']->table('goods') . " AS g
            WHERE a.cat_id = g.goods_type
            AND g.goods_id = '$goods_id'";

    $count = $GLOBALS['db']->getOne($sql);

    if ($count > 0) {
        return true;    //存在
    } else {
        return false;    //不存在
    }
}

/**
 * 商品的货品规格是否存在
 *
 * @param string $goods_attr 商品的货品规格
 * @param string $goods_id 商品id
 * @param int $product_id 商品的货品id；默认值为：0，没有货品id
 * @return  bool                          true，重复；false，不重复
 */
function check_goods_attr_exist($goods_attr, $goods_id, $product_id = 0, $region_id = 0)
{
    //判断商品类型 by wu
    $where_products = "";
    $goods_model = $GLOBALS['db']->getOne(" SELECT model_price FROM " . $GLOBALS['dsc']->table("goods") . " WHERE goods_id = '$goods_id' ");
    if ($goods_model == 1) {
        $table = "products_warehouse";
        $where_products .= " AND warehouse_id = '$region_id' ";
    } elseif ($goods_model == 2) {
        $table = "products_area";
        $where_products .= " AND area_id = '$region_id' ";
    } else {
        $table = "products";
    }

    $goods_id = intval($goods_id);
    if (strlen($goods_attr) == 0 || empty($goods_id)) {
        return true;    //重复
    }

    $attr_arr = is_array($goods_attr) ? $goods_attr : explode('|', $goods_attr);

    $set = '';
    foreach ($attr_arr as $key => $val) {
        $set .= " AND FIND_IN_SET('$val', REPLACE(goods_attr, '|', ',')) ";
    }

    if (empty($product_id)) {
        $sql = "SELECT product_id FROM " . $GLOBALS['dsc']->table($table) . "
                WHERE 1 " . $set . "
                AND goods_id = '$goods_id'" . $where_products;
    } else {
        $sql = "SELECT product_id FROM " . $GLOBALS['dsc']->table($table) . "
                WHERE 1 " . $set . "
                AND goods_id = '$goods_id'
                AND product_id <> '$product_id'" . $where_products;
    }

    $res = $GLOBALS['db']->getOne($sql);

    if (empty($res)) {
        return false;    //不重复
    } else {
        return true;    //重复
    }
}

/**
 * 商品的货品货号是否重复
 *
 * @param $product_sn 商品的货品货号；请在传入本参数前对本参数进行SQl脚本过滤
 * @param int $product_id 商品的货品id；默认值为：0，没有货品id
 * @param int $ru_id 商品模式；0为默认，1为仓库，2为地区
 * @param int $goods_model
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @return bool true，重复；false，不重复
 */
function check_product_sn_exist($product_sn, $product_id = 0, $ru_id = 0, $goods_model = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
    $where = '';

    //判断商品类型 by wu
    if ($goods_model == 1) {
        $table = "products_warehouse";
        $where .= " AND warehouse_id = '$warehouse_id'";
    } elseif ($goods_model == 2) {
        $table = "products_area";
        $where .= " AND area_id = '$area_id'";

        if ($GLOBALS['_CFG']['area_pricetype'] == $area_city) {
            $where .= " AND city_id = '$area_city'";
        }
    } else {
        $table = "products";
    }

    $product_sn = trim($product_sn);
    $product_id = intval($product_id);
    if (strlen($product_sn) == 0) {
        return true;    //重复
    }

    $sql = "SELECT g.goods_id FROM " . $GLOBALS['dsc']->table('goods') . " AS g WHERE g.goods_sn='$product_sn' AND g.user_id = '$ru_id'";
    if ($GLOBALS['db']->getOne($sql)) {
        return true;    //重复
    }

    $where .= " AND (SELECT g.user_id FROM " . $GLOBALS['dsc']->table('goods') . " AS g WHERE g.goods_id = p.goods_id LIMIT 1) = '$ru_id'";

    if (empty($product_id)) {
        $sql = "SELECT p.product_id FROM " . $GLOBALS['dsc']->table($table) . " AS p " . "
                WHERE p.product_sn = '$product_sn'" . $where;
    } else {
        $sql = "SELECT p.product_id FROM " . $GLOBALS['dsc']->table($table) . " AS p " . "
                WHERE p.product_sn = '$product_sn'
                AND p.product_id <> '$product_id'" . $where;
    }

    $res = $GLOBALS['db']->getOne($sql);

    if (empty($res)) {
        return false;    //不重复
    } else {
        return true;    //重复
    }
}

/**
 * 商品的货品货号是否重复
 *
 * @param string $product_bar_code 商品的货品货号；请在传入本参数前对本参数进行SQl脚本过滤
 * @param int $product_id 商品的货品id；默认值为：0，没有货品id
 * @param int $goods_model 商品模式；0为默认，1为仓库，2为地区
 * @return  bool                          true，重复；false，不重复
 */
function check_product_bar_code_exist($product_bar_code, $product_id = 0, $ru_id = 0, $goods_model = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
    $where = '';

    //判断商品类型 by wu
    if ($goods_model == 1) {
        $table = "products_warehouse";
        $where .= " AND warehouse_id = '$warehouse_id'";
    } elseif ($goods_model == 2) {
        $table = "products_area";
        $where .= " AND area_id = '$area_id'";

        if ($GLOBALS['_CFG']['area_pricetype'] == $area_city) {
            $where .= " AND city_id = '$area_city'";
        }
    } else {
        $table = "products";
    }

    $product_bar_code = trim($product_bar_code);
    $product_id = intval($product_id);
    if (strlen($product_bar_code) == 0) {
        return true;    //重复
    }

    if (!empty($product_id)) {
        $sql = "SELECT g.user_id FROM " . $GLOBALS['dsc']->table($table) . " AS p, " .
            $GLOBALS['dsc']->table('goods') . " AS g" .
            " WHERE p.goods_id = g.goods_id AND p.product_id = '$product_id'";
        $ru_id = $GLOBALS['db']->getOne($sql, true);
    } else {
        $ru_id = 0;
    }

    $sql = "SELECT g.goods_id FROM " . $GLOBALS['dsc']->table('goods') . " AS g WHERE g.bar_code = '$product_bar_code' AND g.user_id = '$ru_id'";
    if ($GLOBALS['db']->getOne($sql)) {
        return true;    //重复
    }

    $where .= " AND (SELECT g.user_id FROM " . $GLOBALS['dsc']->table('goods') . " AS g WHERE g.goods_id = p.goods_id LIMIT 1) = '$ru_id'";

    if (empty($product_id)) {
        $sql = "SELECT p.product_id FROM " . $GLOBALS['dsc']->table($table) . " AS p " . "
                WHERE p.bar_code = '$product_bar_code'" . $where;
    } else {
        $sql = "SELECT p.product_id FROM " . $GLOBALS['dsc']->table($table) . " AS p " . "
                WHERE p.bar_code = '$product_bar_code'
                AND p.product_id <> '$product_id'" . $where;
    }

    $res = $GLOBALS['db']->getOne($sql);

    if (empty($res)) {
        return false;    //不重复
    } else {
        return true;    //重复
    }
}

//获取最近使用分类 by wu
function get_recently_used_category($admin_id = 0)
{
    $sql = " SELECT recently_cat FROM " . $GLOBALS['dsc']->table("admin_user") . " WHERE user_id = '$admin_id' LIMIT 5 ";
    $recently_cat = $GLOBALS['db']->getOne($sql);
    if (empty($recently_cat)) {
        $recently_used_category = [];
    } else {
        $cat_list = explode(',', $recently_cat);
        $cat_list = array_slice($cat_list, -5);//取最新的五条记录
        $recently_used_category = [];
        foreach ($cat_list as $key => $val) {
            $recently_used_category[$val] = get_every_category($val);
        }
    }

    $GLOBALS['smarty']->assign('recently_used_category', $recently_used_category);

    return true;
}

/*
 * 一键同步OSS图片
 */
function get_img_file_list($is_ajax = 0, $file_dir)
{
    $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;  //默认第一页
    $page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15; //每页10条

    $dir = storage_public(IMAGE_DIR);
    $file_list = scandir($dir);

    $arr = [];
    if ($file_list) {
        foreach ($file_list as $key => $row) {
            if (!is_dir($row) && app(DscRepository::class)->strLen($row) == 6 && !in_array($row, $file_dir)) {
                $arr[$key]['file_name'] = $dir . "/" . $row;
                $arr[$key]['goods_img'] = $GLOBALS['dsc']->get_file_list($dir . "/" . $row . "/goods_img");
                $arr[$key]['source_img'] = $GLOBALS['dsc']->get_file_list($dir . "/" . $row . "/source_img");
                $arr[$key]['thumb_img'] = $GLOBALS['dsc']->get_file_list($dir . "/" . $row . "/thumb_img");
            }
        }

        $arr = $GLOBALS['dsc']->page_array($page_size, $page, $arr);
    }

    return $arr;
}

/*
 * 一键同步OSS图片
 */
function get_goods_img_list($is_ajax = 0, $file_dir, $is_sel = 0)
{
    $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;  //默认第一页
    $page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 100; //每页10条

    $file_parent_dir1 = ['common', 'qrcode', 'upload'];
    $file_parent_dir2 = ['File', 'Flash', 'Image', 'Image', 'Media'];

    $images_file = read_static_cache('goods_images_file');
    $dir = storage_public(IMAGE_DIR);

    if ($images_file === false) {
        $arr = get_img_merge_func($dir, $file_parent_dir1, $file_parent_dir2, $file_dir);

        if (empty($is_sel)) {
            write_static_cache('goods_images_file', $arr);
        }
    } else {
        if ($is_sel) {
            $arr = get_img_merge_func($dir, $file_parent_dir1, $file_parent_dir2, $file_dir);
        } else {
            $arr = $images_file;
        }
    }

    $arr = $GLOBALS['dsc']->page_array($page_size, $page, $arr);
    return $arr;
}

/* 目录信息 */
function get_img_merge_func($dir, $file_parent_dir1, $file_parent_dir2, $file_dir)
{
    $arr = [];

    $dir = storage_public(IMAGE_DIR);
    $arr1 = get_img_file($dir, $file_parent_dir1, $file_dir);
    $arr2 = get_img_file($dir . "/upload", $file_parent_dir2, [], 8);

    if ($arr1 && $arr2) {
        $arr = array_merge($arr1, $arr2);
    } elseif ($arr1) {
        $arr = $arr1;
    } elseif ($arr2) {
        $arr = $arr2;
    }

    return $arr;
}

/**
 *
 * $dir 路径地址
 * $file_parent_dir 数组（排除目录）
 * $file_dir 数组（查询的目录）
 * $strlen 字符串长度
 */
function get_img_file($dir, $file_parent_dir, $file_dir, $strlen = 6)
{
    $file_list = scandir($dir);
    $arr = [];
    if ($file_list) {
        foreach ($file_list as $key => $row) {
            if (!is_dir($row) && app(DscRepository::class)->strLen($row) == $strlen && !in_array($row, $file_parent_dir)) {
                $arr[$key]['file_name'] = $dir . "/" . $row;

                if ($file_dir) {
                    foreach ($file_dir as $ikey => $irow) {
                        if (!is_dir($irow) && file_exists($arr[$key]['file_name'] . "/" . $irow)) {
                            $child = scandir($arr[$key]['file_name'] . "/" . $irow);
                            foreach ($child as $iikey => $iirow) {
                                if (!is_dir($iirow)) {
                                    $path = str_replace(storage_public(), '', $arr[$key]['file_name']);
                                    $arr[$key][$irow]['img_list'][$iikey] = $path . "/" . $irow . "/" . $iirow;
                                }

                                if (isset($arr[$key][$irow]['img_list'][$iikey]) && ($arr[$key][$irow]['img_list'][$iikey] == '.' || $arr[$key][$irow]['img_list'][$iikey] == '..')) {
                                    unset($arr[$key][$irow]['img_list'][$iikey]);
                                }
                            }
                        }
                    }

                    unset($arr[$key]['file_name']);
                } else {
                    $path = str_replace(storage_public(), '', $arr[$key]['file_name']);

                    $arr[$key] = scandir($arr[$key]['file_name']);
                    foreach ($arr[$key] as $ikey => $irow) {
                        if (!is_dir($irow)) {
                            $arr[$key][$ikey] = $path . "/" . $irow;
                        }

                        if (isset($arr[$key][$ikey]) && ($arr[$key][$ikey] == '.' || $arr[$key][$ikey] == '..')) {
                            unset($arr[$key][$ikey]);
                        }
                    }
                }
            }
        }
    }

    if ($arr) {
        $arr = arr_foreach($arr);
    }

    return $arr;
}

/**
 * 获得秒杀活动的商品
 *
 * @access  public
 * @param int $sec_id
 * @param int $tb_id
 * @return  array
 */
function get_add_seckill_goods($sec_id = 0, $tb_id = 0)
{
    $filter['sec_id'] = $sec_id;
    $filter['tb_id'] = $tb_id;

    // 如果存在最后一次过滤条件并且使用 重置 REQUEST
    $param_str = 'getWarehouseList';
    $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

    $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

    $where = " where 1 ";
    $where .= " AND sg.sec_id = '$sec_id' AND sg.tb_id = '$tb_id' ";
    $sql = " SELECT COUNT(*) FROM " . $GLOBALS['dsc']->table('seckill_goods') . " AS sg LEFT JOIN " .
        $GLOBALS['dsc']->table('goods') . " AS g ON sg.goods_id = g.goods_id  " . $where .
        " ORDER BY sg.tb_id ASC, sg.goods_id ASC ";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    // 存储最后一次过滤条件
    app(DscRepository::class)->setSessionFilter($filter, $param_str);

    /* 获活动数据 */
    $sql = " SELECT sg.id, sg.sec_id, sg.tb_id, sg.sec_num, sg.sec_limit, sg.sec_price, g.goods_name, g.shop_price FROM " .
        $GLOBALS['dsc']->table('seckill_goods') . " AS sg LEFT JOIN " . $GLOBALS['dsc']->table('goods') . " AS g ON sg.goods_id = g.goods_id  " . $where .
        " ORDER BY sg.tb_id ASC, sg.goods_id ASC LIMIT " . $filter['start'] . ", " . $filter['page_size'];

    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row as $key => $val) {
        $row[$key]['shop_price'] = price_format($val['shop_price']);
    }

    $sql = " SELECT GROUP_CONCAT(sg.goods_id) FROM " . $GLOBALS['dsc']->table('seckill_goods') . " AS sg " . $where;
    $cat_goods = $GLOBALS['db']->getOne($sql);

    $arr = ['seckill_goods' => $row, 'filter' => $filter, 'cat_goods' => $cat_goods, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    return $arr;
}

/*
*
*/
function insert_recently_used_category($cat_id, $admin_id)
{
    $sql = " SELECT recently_cat FROM " . $GLOBALS['dsc']->table('admin_user') . " WHERE user_id = '$admin_id' ";
    $recently_cat = $GLOBALS['db']->getOne($sql);

    $arr = [];
    if ($recently_cat) {
        $arr = explode(',', $recently_cat);
    }

    if ($arr && count($arr) >= 5) {
        array_pop($arr);
    }

    if (empty($arr)) {
        $str = $cat_id;
    } else {
        array_unshift($arr, $cat_id);
        $arr = array_unique($arr);
        $str = implode(',', $arr);
    }

    $sql = "UPDATE " . $GLOBALS['dsc']->table('admin_user') .
        " SET recently_cat = '$str' " .
        "WHERE user_id = '$admin_id' ";
    $GLOBALS['db']->query($sql);
}

/**
 * 获得商品库商品列表
 *
 * @param int $real_goods
 * @param string $conditions
 * @param int $review_status
 * @param int $real_division
 * @return array
 * @throws Exception
 */
function lib_goods_list($real_goods = 1, $conditions = '', $review_status = 0, $real_division = 0)
{
    $adminru = get_admin_ru_id();

    // 如果存在最后一次过滤条件并且使用 重置 REQUEST
    $param_str = 'lib_goods_list-' . $real_goods . '-' . $review_status;
    $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

    $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

    $day = getdate();
    $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
    $filter['intro_type'] = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
    $filter['brand_id'] = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
    $filter['brand_keyword'] = empty($_REQUEST['brand_keyword']) ? '' : trim($_REQUEST['brand_keyword']);
    $filter['cat_type'] = !isset($_REQUEST['cat_type']) && empty($_REQUEST['cat_type']) ? '' : addslashes($_REQUEST['cat_type']);
    $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);

    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
        $filter['keyword'] = json_str_iconv($filter['keyword']);
    }

    if (isset($_REQUEST['review_status'])) {
        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']); //ecmoban模板堂 --zhuo
    } else {
        $filter['review_status'] = $review_status;
    }

    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'g.goods_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['extension_code'] = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
    $filter['real_goods'] = $real_goods;

    $where = 1;

    if ($adminru['ru_id'] > 0) {
        $where .= "  AND g.is_on_sale = 1 ";
    }

    if ($filter['brand_keyword']) {
        $filter['brand_id'] = $GLOBALS['db']->getAll("SELECT GROUP_CONCAT(brand_id) AS brand_id FROM " . $GLOBALS['dsc']->table('brand') . " WHERE brand_name LIKE '%" . $filter['brand_keyword'] . "%' ");
        $where .= " AND (g.brand_id = '" . db_create_in($filter['brand_id']) . "')";
    }

    if ($filter['brand_id']) {
        $where .= " AND (g.brand_id = '" . $filter['brand_id'] . "')";
    }

    /* 扩展 */
    if ($filter['extension_code']) {
        $where .= " AND g.extension_code='$filter[extension_code]'";
    }

    /* 关键字 */
    if (!empty($filter['keyword'])) {
        $where .= " AND (g.goods_sn LIKE '%" . mysql_like_quote($filter['keyword']) . "%' OR g.goods_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%'" . ")";
        //ecmoban模板堂 --zhuo end
    }

    if ($real_goods > -1 && $real_division == 0) {
        $where .= " AND g.is_real='$real_goods'";
    }

    $where .= $conditions;

    if ($filter['cat_id'] > 0) {
        $children = CategoryService::getGoodsLibCatChildren($filter['cat_id']);
        if ($children) {
            $where = " AND g.cat_id IN(" . $children . ")";
        }
    }

    /* 记录总数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['dsc']->table('goods_lib') . " AS g " .
        " WHERE $where ";

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    // 存储最后一次过滤条件
    app(DscRepository::class)->setSessionFilter($filter, $param_str);

    $sql = " SELECT g.goods_id, g.lib_cat_id, g.goods_thumb, g.from_seller, g.goods_name, g.brand_id, g.goods_type, g.goods_sn, g.shop_price, g.sort_order, g.is_real, g.bar_code, g.is_on_sale, go.freight, go.tid " .
        " FROM " . $GLOBALS['dsc']->table('goods_lib') . " AS g " .
        " LEFT JOIN " . $GLOBALS['dsc']->table('goods') . " AS go ON g.goods_id=go.goods_id " .
        " WHERE  $where" .
        " ORDER BY $filter[sort_by] $filter[sort_order] " .
        " LIMIT " . $filter['start'] . ",$filter[page_size]";

    $filter['keyword'] = stripslashes($filter['keyword']);

    $row = $GLOBALS['db']->getAll($sql);

    if ($row) {

        $seller_id = BaseRepository::getKeyPluck($row, 'from_seller');
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

        $count = count($row);
        for ($i = 0; $i < $count; $i++) {

            $shop_information = $merchantList[$row[$i]['from_seller']] ?? []; //通过ru_id获取到店铺信息;

            $row[$i]['lib_cat_name'] = lib_cat_name($row[$i]['lib_cat_id']);

            if ($row[$i]['freight'] == 2) {
                $row[$i]['transport'] = get_goods_transport_info($row[$i]['tid']);
            }

            $row[$i]['shop_name'] = $shop_information['shop_name'] ?? ''; //店铺名称

            //图片显示
            $row[$i]['goods_thumb'] = app(DscRepository::class)->getImagePath($row[$i]['goods_thumb']);

            //商品扩展信息
            $row[$i]['goods_extend'] = get_goods_extend($row[$i]['goods_id']);
        }
    }

    return ['goods' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
}

/*
 * 指定分类ID返回分类名称
 */

function lib_cat_name($cat_id)
{
    $sql = " SELECT cat_name FROM " . $GLOBALS['dsc']->table('goods_lib_cat') . " WHERE cat_id = '$cat_id' ";
    return $GLOBALS['db']->getOne($sql);
}
