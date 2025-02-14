<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AutoSms
 */
class AutoSms extends Model
{
    protected $table = 'auto_sms';

    protected $primaryKey = 'item_id';

    public $timestamps = false;

    protected $fillable = [
        'item_type',
        'user_id',
        'ru_id',
        'order_id',
        'add_time'
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getItemType()
    {
        return $this->item_type;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getRuId()
    {
        return $this->ru_id;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->order_id;
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
    public function setItemType($value)
    {
        $this->item_type = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setUserId($value)
    {
        $this->user_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setRuId($value)
    {
        $this->ru_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setOrderId($value)
    {
        $this->order_id = $value;
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
