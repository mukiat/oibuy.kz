<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShopAddress
 * @package App\Entities
 */
class ShopAddress extends Model
{
    /**
     * @var string
     */
    protected $table = 'shop_address';

    /**
     * @var string
     */
    protected $primaryKey = 'id';
}
