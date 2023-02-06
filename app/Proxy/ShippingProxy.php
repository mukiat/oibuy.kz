<?php

namespace App\Proxy;

use App\Manager\ExpressTrace\Exception\InvalidArgumentException;
use App\Manager\ExpressTrace\ExpressTrace;

/**
 * Class ShippingProxy
 * @package App\Proxy
 */
class ShippingProxy
{
    /**
     * @var ExpressTrace
     */
    protected $expressTrace;

    /**
     * ShippingProxy constructor.
     * @param ExpressTrace $expressTrace
     */
    public function __construct(ExpressTrace $expressTrace)
    {
        $this->expressTrace = $expressTrace;
    }

    /**
     * 快递跟踪
     * @param string $com
     * @param string $num
     * @param array $payload
     * @return string
     * @throws InvalidArgumentException
     */
    public function getExpress($com = '', $num = '', $payload = [])
    {
        return $this->expressTrace->query($com, $num, $payload);
    }

    /**
     * 地图跟踪
     * @param string $com
     * @param string $num
     * @param array $payload
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function mapTrack($com = '', $num = '', $payload = [])
    {
        return $this->expressTrace->mapTrack($com, $num, $payload);
    }
}
