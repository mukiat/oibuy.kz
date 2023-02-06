<?php

namespace App\Manager\ExpressTrace\Contract;

/**
 * Interface TraceInterface
 * @package App\Manager\ExpressTrace\Contract
 */
interface TraceInterface
{
    /**
     * 快递跟踪记录查询
     * @return mixed
     */
    public function query();

    /**
     * 快递轨迹地图
     * @return mixed
     */
    public function mapTrack();
}
