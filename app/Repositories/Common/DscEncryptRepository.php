<?php

namespace App\Repositories\Common;

use App\Repositories\Encryption\DscEncrypt as base;

/**
 * Class DscEncryptRepository
 * @package App\Repositories\Common
 * @method static order_fifo($user_id = 0, $cart_value = '', $time = 0) 订单库存队列（数据库版）先进先出，控制商品超卖问题
 * @method static SolveDealInsert($user_id = 0, $cart_value = '', $time = 0) 增加队列记录[添加数据，处理并发(库存)问题]
 * @method static changeImagesPath($img = '', $urlImage = '') 判断图片是否含有http
 * @method static filterValInt($val = '', $int = true) 过滤多参数值
 * @method static allAttrList($all_attr_list = []) 分类/搜索页属性剔除重复
 */
class DscEncryptRepository extends base
{

}
