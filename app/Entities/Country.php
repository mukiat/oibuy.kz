<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 */
class Country extends Model
{
    protected $table = 'country';

    public $timestamps = false;

    protected $fillable = [
        'country_name',
        'country_icon',
        'add_time'
    ];

    protected $guarded = [];

    
    /**
     * @return mixed
     */
    public function getCountryName()
    {
        return $this->country_name;
    }

    /**
     * @return mixed
     */
    public function getCountryIcon()
    {
        return $this->country_icon;
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
    public function setCountryName($value)
    {
        $this->country_name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCountryIcon($value)
    {
        $this->country_icon = $value;
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