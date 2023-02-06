<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SellerFollowList
 */
class SellerFollowList extends Model
{
    protected $table = 'seller_follow_list';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'desc',
        'seller_id',
        'qr_code',
        'cover_pic'
    ];

    protected $guarded = [];

    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @return mixed
     */
    public function getSellerId()
    {
        return $this->seller_id;
    }

    /**
     * @return mixed
     */
    public function getQrCode()
    {
        return $this->qr_code;
    }

    /**
     * @return mixed
     */
    public function getCoverPic()
    {
        return $this->cover_pic;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setName($value)
    {
        $this->name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDesc($value)
    {
        $this->desc = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSellerId($value)
    {
        $this->seller_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setQrCode($value)
    {
        $this->qr_code = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCoverPic($value)
    {
        $this->cover_pic = $value;
        return $this;
    }
}