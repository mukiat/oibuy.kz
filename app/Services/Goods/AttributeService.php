<?php

namespace App\Services\Goods;

use App\Models\Attribute;
use App\Repositories\Common\BaseRepository;

class AttributeService
{
    /**
     * 获取属性信息
     *
     * @param int $attr_id
     * @return mixed
     */
    public function getAttributeInfo($attr_id = 0)
    {
        $row = Attribute::where('attr_id', $attr_id);
        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }
}
