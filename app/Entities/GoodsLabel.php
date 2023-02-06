<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsLabel
 */
class GoodsLabel extends Model
{
    protected $table = 'goods_label';

    public $timestamps = false;

    protected $fillable = [
        'label_name',
        'label_image',
        'sort',
        'merchant_use',
        'status',
        'label_url',
        'add_time',
        'type',
        'start_time',
        'end_time',
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getLabelName()
    {
        return $this->label_name;
    }

    /**
     * @return mixed
     */
    public function getLabelImage()
    {
        return $this->label_image;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return mixed
     */
    public function getMerchantUse()
    {
        return $this->merchant_use;
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
    public function getLabelUrl()
    {
        return $this->label_url;
    }

    /**
     * @return mixed
     */
    public function getAddTime()
    {
        return $this->add_time;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setLabelName($value)
    {
        $this->label_name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setLabelImage($value)
    {
        $this->label_image = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSort($value)
    {
        $this->sort = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMerchantUse($value)
    {
        $this->merchant_use = $value;
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
    public function setLabelUrl($value)
    {
        $this->label_url = $value;
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
}
