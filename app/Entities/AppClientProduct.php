<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ad
 */
class AppClientProduct extends Model
{
    protected $table = 'app_client_product';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'version_id',
        'update_desc',
        'download_url',
        'is_show',
        'update_time',
        'create_time'
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return mixed
     */
    public function getVersionId()
    {
        return $this->version_id;
    }

    /**
     * @return mixed
     */
    public function getUpdateDesc()
    {
        return $this->update_desc;
    }

    /**
     * @return mixed
     */
    public function getDownloadUrl()
    {
        return $this->download_url;
    }

    /**
     * @return mixed
     */
    public function getIsShow()
    {
        return $this->is_show;
    }

    /**
     * @return mixed
     */
    public function getUpdateTime()
    {
        return $this->update_time;
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
    public function setClientId($value)
    {
        $this->client_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setVersionId($value)
    {
        $this->version_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setUpdateDesc($value)
    {
        $this->update_desc = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDownloadUrl($value)
    {
        $this->download_url = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsShow($value)
    {
        $this->is_show = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setUpdateTime($value)
    {
        $this->update_time = $value;
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
