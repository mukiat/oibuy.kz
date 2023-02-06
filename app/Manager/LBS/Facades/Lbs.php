<?php

namespace App\Manager\LBS\Facades;

use App\Manager\LBS\LbsManager;
use Illuminate\Support\Facades\Facade;

/**
 * Class Lbs
 *
 * @method static ip($ip)
 * @method static location2address($lat, $lng)
 * @method static address2location($address)
 * @method static district($id)
 *
 * @package App\Manager\LBS\Facades
 */
class Lbs extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LbsManager::class;
    }
}
