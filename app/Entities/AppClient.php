<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ad
 */
class AppClient extends Model
{
    protected $table = 'app_client';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'appid',
        'create_time'
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
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * @return mixed
     */
    public function getCreateTime()
    {
        return $this->create_time;
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
    public function setAppid($value)
    {
        $this->appid = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCreateTime($value)
    {
        $this->create_time = $value;
        return $this;
    }
}
