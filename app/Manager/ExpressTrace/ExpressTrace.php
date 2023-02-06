<?php

namespace App\Manager\ExpressTrace;

use App\Models\Express;

/**
 * Class ExpressTrace
 * @package App\Manager\ExpressTrace
 */
class ExpressTrace
{
    /**
     * @var array
     */
    protected $strategies = [];

    /**
     * @param string $company 快递公司类型
     * @param string $number 快递单号
     * @param array $payload 可选参数
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function query(string $company, string $number, array $payload = [])
    {
        return $this->getHandler()->query($company, $number, $payload);
    }

    /**
     * @param string $company
     * @param string $number
     * @param array $payload
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function mapTrack(string $company, string $number, array $payload = [])
    {
        return $this->getHandler()->mapTrack($company, $number, $payload);
    }

    /**
     * 获取处理者对象
     * @return ExpressTraceFactory|mixed
     */
    public function getHandler()
    {
        $config = self::getConfig();
        $type = isset($config['type']) ? $config['type'] : 'none';

        if (!isset($this->strategies[$type])) {
            $this->strategies[$type] = new ExpressTraceFactory($type, $config);
        }

        return $this->strategies[$type];
    }

    /**
     * 获取配置
     * @return array
     */
    protected static function getConfig()
    {
        return cache()->remember('express_config', 3600, function () {
            $data = [];
            $model = Express::query()->where('enable', 1)->where('default', 1)->first();
            $config = collect($model)->toArray();
            if (!empty($config['express_configure'])) {
                $express_configure = unserialize($config['express_configure']);
                foreach ($express_configure as $val) {
                    $data[$val['name']] = $val['value'];
                }
                $data['type'] = $config['code'];
            }

            return $data;
        });
    }
}
