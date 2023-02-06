<?php

namespace App\Macros\Builder;

use App\Kernel\Macros\Builder\DBExtend as base;

/**
 * Class DBExtend
 * @package App\Macros\Builder
 * @method comments() 执行语句 添加表注释
 * @method key_name() 获取索引名
 * @method hasIndex() 判断表是否存在索引
 */
class DBExtend extends base
{
    /**
     * 添加表注释
     *
     * exp:  DB::table('users')->comments('table comment');
     */

    /**
     * 判断表是否存在索引
     *
     * exp:  DB::table('users')->hasIndex('key_name');
     */
}
