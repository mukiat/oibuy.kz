<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\Region;
use App\Repositories\Common\BaseRepository;
use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;

/**
 * Class RegionController
 * @package App\Api\Controllers
 */
class RegionController extends Controller
{
    /**
     * 返回地区列表
     * @param Request $request
     * @return array|CacheManager|mixed
     * @throws Exception
     */
    public function index(Request $request)
    {
        $region = (int)$request->get('region', 0);
        $level = $request->get('level', 1);

        $cache_name = 'region_' . $region;
        $result = cache($cache_name);
        $result = !is_null($result) ? $result : [];

        if (empty($result)) {
            $list = Region::where('parent_id', $region);
            $list = BaseRepository::getToArrayGet($list);

            foreach ($list as $key => $value) {
                $result[$key]['id'] = $value['region_id'];
                $result[$key]['name'] = $value['region_name'];
                $result[$key]['level'] = $level + 1;
            }
            cache()->forever($cache_name, $result);
        }

        return $result;
    }
}
