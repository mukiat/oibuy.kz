<?php

namespace App\Manager\ExpressTrace;

use App\Manager\ExpressTrace\Exception\InvalidArgumentException;
use Illuminate\Support\Str;

/**
 * Class ExpressTraceFactory
 * @package App\Manager\ExpressTrace
 */
class ExpressTraceFactory
{
    /**
     * @var string 适配器类型
     */
    private $type;

    /**
     * @var string 适配器配置
     */
    private $config;

    /**
     * ExpressTraceFactory constructor.
     * @param $type
     * @param $config
     */
    public function __construct($type, $config)
    {
        $this->type = $type;
        $this->config = $config;
    }

    /**
     * @param $company
     * @param $number
     * @param $payload
     * @return string
     * @throws InvalidArgumentException
     */
    public function query($company, $number, $payload)
    {
        $gateway = $this->createGateway($this->type);

        return $gateway->query($company, $number, $payload);
    }

    /**
     * @param $company
     * @param $number
     * @param array $payload
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function mapTrack($company, $number, $payload = [])
    {
        $gateway = $this->createGateway($this->type);

        return $gateway->mapTrack($company, $number, $payload);
    }

    /**
     * 获取驱动实例
     *
     * @param $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function createGateway($name)
    {
        $className = $this->formatGatewayClassName($name);

        return $this->makeGateway($className, $this->config);
    }

    /**
     * 获取驱动名称
     *
     * @param $name
     * @return string
     */
    protected function formatGatewayClassName($name)
    {
        if (class_exists($name)) {
            return $name;
        }

        $name = Str::studly(str_replace(['-', '_', ''], '', $name));

        return __NAMESPACE__ . '\\Adaptor\\' . $name;
    }

    /**
     * 创建驱动实例
     *
     * @param $gateway
     * @param $config
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function makeGateway($gateway, $config)
    {
        if (!class_exists($gateway)) {
            throw new InvalidArgumentException(\sprintf('Class "%s" is a invalid express gateway.', $gateway));
        }

        return new $gateway($config);
    }
}
