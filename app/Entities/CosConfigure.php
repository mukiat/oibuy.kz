<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CosConfigure
 */
class CosConfigure extends Model
{
    protected $table = 'cos_configure';

    public $timestamps = false;

    protected $fillable = [
        'bucket',
        'app_id',
        'secret_id',
        'secret_key',
        'is_cname',
        'endpoint',
        'regional',
        'port',
        'is_use'
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @return mixed
     */
    public function getSecretId()
    {
        return $this->secret_id;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * @return mixed
     */
    public function getIsCname()
    {
        return $this->is_cname;
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return mixed
     */
    public function getRegional()
    {
        return $this->regional;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return mixed
     */
    public function getIsUse()
    {
        return $this->is_use;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBucket($value)
    {
        $this->bucket = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function setAppId($value)
    {
        $this->app_id = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function setSecretId($value)
    {
        $this->secret_id = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function setSecretKey($value)
    {
        $this->secret_key = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setKeyid($value)
    {
        $this->keyid = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setKeysecret($value)
    {
        $this->keysecret = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsCname($value)
    {
        $this->is_cname = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEndpoint($value)
    {
        $this->endpoint = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setRegional($value)
    {
        $this->regional = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPort($value)
    {
        $this->port = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsUse($value)
    {
        $this->is_use = $value;
        return $this;
    }
}
