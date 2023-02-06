<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CrossWarehouse
 */
class CrossWarehouse extends Model
{
    protected $table = 'cross_warehouse';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'add_time'
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
    public function getAddTime()
    {
        return $this->add_time;
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
    public function setAddTime($value)
    {
        $this->add_time = $value;
        return $this;
    }
}