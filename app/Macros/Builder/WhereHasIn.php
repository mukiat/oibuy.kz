<?php

namespace App\Macros\Builder;

use App\Kernel\Macros\Builder\WhereHasIn as base;

/**
 * Class WhereHasIn
 * @package App\Macros\Builder
 * @method whereHasIn 进行whereHasIn方式构造查询对象并返回
 */
class WhereHasIn extends base
{
    /**
     * exp:  OrderGoods::whereHasIn('getOrder', function ($query) {}); // 默认true：[false 主表主键 rec_idtrue 指定关联表主键 order_id]
     */
}
