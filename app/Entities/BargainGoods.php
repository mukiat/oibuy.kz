<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BargainGoods
 */
class BargainGoods extends Model
{
    protected $table = 'bargain_goods';

    public $timestamps = false;

    protected $fillable = [
        'bargain_name',
        'goods_id',
        'start_time',
        'end_time',
        'add_time',
        'goods_price',
        'min_price',
        'max_price',
        'target_price',
        'total_num',
        'is_hot',
        'is_audit',
        'isnot_aduit_reason',
        'bargain_desc',
        'status',
        'is_delete'
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getBargainName()
    {
        return $this->bargain_name;
    }

    /**
     * @return mixed
     */
    public function getGoodsId()
    {
        return $this->goods_id;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @return mixed
     */
    public function getAddTime()
    {
        return $this->add_time;
    }

    /**
     * @return mixed
     */
    public function getGoodsPrice()
    {
        return $this->goods_price;
    }

    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        return $this->min_price;
    }

    /**
     * @return mixed
     */
    public function getMaxPrice()
    {
        return $this->max_price;
    }

    /**
     * @return mixed
     */
    public function getTargetPrice()
    {
        return $this->target_price;
    }

    /**
     * @return mixed
     */
    public function getTotalNum()
    {
        return $this->total_num;
    }

    /**
     * @return mixed
     */
    public function getIsHot()
    {
        return $this->is_hot;
    }

    /**
     * @return mixed
     */
    public function getIsAudit()
    {
        return $this->is_audit;
    }

    /**
     * @return mixed
     */
    public function getIsnotAduitReason()
    {
        return $this->isnot_aduit_reason;
    }

    /**
     * @return mixed
     */
    public function getBargainDesc()
    {
        return $this->bargain_desc;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getIsDelete()
    {
        return $this->is_delete;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBargainName($value)
    {
        $this->bargain_name = $value;
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

    /**
     * @param $value
     * @return $this
     */
    public function setStartTime($value)
    {
        $this->start_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEndTime($value)
    {
        $this->end_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAddTime($value)
    {
        $this->add_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setGoodsPrice($value)
    {
        $this->goods_price = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMinPrice($value)
    {
        $this->min_price = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMaxPrice($value)
    {
        $this->max_price = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTargetPrice($value)
    {
        $this->target_price = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTotalNum($value)
    {
        $this->total_num = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsHot($value)
    {
        $this->is_hot = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsAudit($value)
    {
        $this->is_audit = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsnotAduitReason($value)
    {
        $this->isnot_aduit_reason = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBargainDesc($value)
    {
        $this->bargain_desc = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setStatus($value)
    {
        $this->status = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsDelete($value)
    {
        $this->is_delete = $value;
        return $this;
    }
}
