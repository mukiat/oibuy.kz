<?php

namespace App\Repositories\Common;

use App\Kernel\Repositories\Common\ArrRepository as Base;

/**
 * Class ArrRepository
 * @method static getArrCollapse($list = []) 将多个数组合并成一个数组
 * @method static getSearchArray($list, $keyword, $searchStr = []) 搜索数组
 * @method static getDiffArrayByFilter($arr1 = [], $arr2 = []) 对两个二维数组取差集
 * @method static getDiffArrayByPk($arr1 = [], $arr2 = [], $pk = 'pid') 根据唯一字段对两个二维数组取差集
 * @method static where($arr = [], $fun = '', $is = 'yes') 使用给定闭包返回的结果过滤数组
 * @method static get($list = [], $key = '', $default = 0) 根据指定key值从数组中获取值
 * @method static pluck($list = [], $key = '') 从数组中检索给定键的所有值
 * @method static flatten($list = []) 将多维数组中数组的值取出平铺为一维数组
 * @method static collapse($list = []) 将多个数组合并为一个数组
 * @method static combine($key = [], $value = []) 两个数组组合
 * @method static getArrayUnset($list = []) 删除数组空值的行数
 * @method static jsonHtml($path = '', $data = [], $json = []) 返回json html内容
 * @method static display($template, $data = []) 显示模板
 * @method static searchKeywordBrand($arr_keyword = [], $brands_list = []) 处理搜索关键词与品牌名称一致，则返回对应品牌
 * @package App\Repositories\Common
 */
class ArrRepository extends Base
{

    // where 方法示例
    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500, null]
     * ];
     * $filtered = ArrRepository::where($array, 'is_string');
     * 结果集：[
     *      1 => '200',
     *      3 => '400'
     * ]
     */

    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500, null]
     * ];
     * $filtered = ArrRepository::where($array, 'is_null', 'no');
     * 结果集：[
     *      0 => 100
     *      1 => '200',
     *      2 => 300,
     *      3 => '400',
     *      4 => 500
     * ]
     */

    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500],
     *      'condition' => [
     *      'name' => 'gt',
     *      ‘value' => 120,
     *    ]
     * ];
     * $filtered = ArrRepository::where($array);
     * 结果集：[
     *      1 => '200',
     *      2 => 300,
     *      3 => '400',
     *      4 => 500
     * ]
     */

    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500],
     *      'condition' => [
     *      'name' => 'gt_eq',
     *      ‘value' => 300,
     *    ]
     * ];
     * $filtered = ArrRepository::where($array);
     * 结果集：[
     *      2 => 300,
     *      3 => '400',
     *      4 => 500
     * ]
     */

    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500],
     *      'condition' => [
     *      'name' => 'lt',
     *      ‘value' => 120,
     *    ]
     * ];
     * $filtered = ArrRepository::where($array);
     * 结果集：[
     *      0 => 100
     * ]
     */

    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500],
     *      'condition' => [
     *      'name' => 'lt_eq',
     *      ‘value' => 200,
     *    ]
     * ];
     * $filtered = ArrRepository::where($array);
     * 结果集：[
     *      0 => 100,
     *      1 => '200'
     * ]
     */

    /**
     * $array = [
     *    'list' => [100, '200', 300, '400', 500],
     *      'condition' => [
     *      'name' => 'between',
     *      ‘value' => [150, 390],
     *    ]
     * ];
     * $filtered = ArrRepository::where($array);
     * 结果集：[
     *      1 => '200',
     *      2 => 300
     * ]
     */
}
