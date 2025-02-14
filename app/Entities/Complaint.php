<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Complaint
 */
class Complaint extends Model
{
    protected $table = 'complaint';

    protected $primaryKey = 'complaint_id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'order_sn',
        'user_id',
        'user_name',
        'ru_id',
        'shop_name',
        'title_id',
        'complaint_content',
        'add_time',
        'complaint_handle_time',
        'admin_id',
        'appeal_messg',
        'appeal_time',
        'end_handle_time',
        'end_admin_id',
        'end_handle_messg',
        'complaint_state',
        'complaint_active'
    ];

    protected $guarded = [];


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
    public function getOrderSn()
    {
        return $this->order_sn;
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
    public function getUserName()
    {
        return $this->user_name;
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
    public function getShopName()
    {
        return $this->shop_name;
    }

    /**
     * @return mixed
     */
    public function getTitleId()
    {
        return $this->title_id;
    }

    /**
     * @return mixed
     */
    public function getComplaintContent()
    {
        return $this->complaint_content;
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
    public function getComplaintHandleTime()
    {
        return $this->complaint_handle_time;
    }

    /**
     * @return mixed
     */
    public function getAdminId()
    {
        return $this->admin_id;
    }

    /**
     * @return mixed
     */
    public function getAppealMessg()
    {
        return $this->appeal_messg;
    }

    /**
     * @return mixed
     */
    public function getAppealTime()
    {
        return $this->appeal_time;
    }

    /**
     * @return mixed
     */
    public function getEndHandleTime()
    {
        return $this->end_handle_time;
    }

    /**
     * @return mixed
     */
    public function getEndAdminId()
    {
        return $this->end_admin_id;
    }

    /**
     * @return mixed
     */
    public function getEndHandleMessg()
    {
        return $this->end_handle_messg;
    }

    /**
     * @return mixed
     */
    public function getComplaintState()
    {
        return $this->complaint_state;
    }

    /**
     * @return mixed
     */
    public function getComplaintActive()
    {
        return $this->complaint_active;
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
    public function setOrderSn($value)
    {
        $this->order_sn = $value;
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
    public function setUserName($value)
    {
        $this->user_name = $value;
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
    public function setShopName($value)
    {
        $this->shop_name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTitleId($value)
    {
        $this->title_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setComplaintContent($value)
    {
        $this->complaint_content = $value;
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
    public function setComplaintHandleTime($value)
    {
        $this->complaint_handle_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAdminId($value)
    {
        $this->admin_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAppealMessg($value)
    {
        $this->appeal_messg = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAppealTime($value)
    {
        $this->appeal_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEndHandleTime($value)
    {
        $this->end_handle_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEndAdminId($value)
    {
        $this->end_admin_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEndHandleMessg($value)
    {
        $this->end_handle_messg = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setComplaintState($value)
    {
        $this->complaint_state = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setComplaintActive($value)
    {
        $this->complaint_active = $value;
        return $this;
    }
}
