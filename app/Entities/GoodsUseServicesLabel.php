<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsUseLabel
 */
class GoodsUseServicesLabel extends Model
{
    protected $table = 'goods_use_services_label';

    public $timestamps = false;

    protected $fillable = [
        'label_id',
        'goods_id',
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getLabelId()
    {
        return $this->label_id;
    }

    /**
     * @return mixed
     */
    public function getGoodsId()
    {
        return $this->goods_id;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setLabelId($value)
    {
        $this->label_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setGoodsId($value)
    {
        $this->goods_id = $value;
        return $this;
    }
}
