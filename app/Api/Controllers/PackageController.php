<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Services\Activity\PackageService;
use App\Services\Common\AreaService;
use Exception;
use Illuminate\Http\Request;

/**
 * Class PackageController
 * @package App\Api\Controllers
 */
class PackageController extends Controller
{
    /**
     * @var AreaService
     */
    protected $areaService;

    /**
     * @var PackageService
     */
    protected $packageService;

    /**
     * PackageController constructor.
     * @param AreaService $areaService
     * @param PackageService $packageService
     */
    public function __construct(AreaService $areaService, PackageService $packageService)
    {
        $this->areaService = $areaService;
        $this->packageService = $packageService;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function index(Request $request)
    {
        $condition = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
            'user_id' => $this->uid
        ];

        $page = $request->get('page', 1);

        return $this->packageService->getPackageList($condition, (int) $page);
    }
}
