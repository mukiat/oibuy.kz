<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsVideo
 */
class GoodsVideo extends Model
{
    protected $table = 'goods_video';

    protected $primaryKey = 'video_id';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'goods_video',
        'look_num'
    ];

    protected $guarded = [];


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
    public function getGoodsVideo()
    {
        return $this->goods_video;
    }

    

    /**
     * @return mixed
     */
    public function getLookNum()
    {
        return $this->look_num;
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
    public function setGoodsVideo($value)
    {
        $this->goods_video = $value;
        return $this;
    }
    

    /**
     * @param $value
     * @return $this
     */
    public function setLookNum($value)
    {
        $this->look_num = $value;
        return $this;
    }

}
