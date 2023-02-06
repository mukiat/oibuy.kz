<?php

namespace App\Modules\Web\Controllers;

use App\Repositories\Common\TimeRepository;
use App\Services\Cron\CronArtisanService;

/**
 * 计划任务统一入口
 * Class CronController
 * @package App\Http\Controllers
 */
class CronController extends InitController
{
    protected $cronArtisanService;

    public function __construct(
        CronArtisanService $cronArtisanService
    ) {
        $this->cronArtisanService = $cronArtisanService;
    }

    /**
     *  执行计划任务
     */
    public function index()
    {
        $timestamp = TimeRepository::getGmTime();

        $refer = request()->server('HTTP_REFERER');

        $cron_code = request()->input('code', '');

        if (isset($set_modules)) {
            $set_modules = false;
            unset($set_modules);
        }

        // 获得需要执行的计划任务数据
        $this->cronArtisanService->cronList($timestamp, $refer, $cron_code);
    }
}
