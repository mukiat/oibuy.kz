<?php

namespace App\Services\Express;

use App\Models\Express;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

/**
 * Class ExpressService
 * @package App\Services\Express
 */
class ExpressService
{
    /**
     * 快递跟踪列表
     * @return array
     */
    public function expressList()
    {
        $model = Express::query();

        $model = $model->limit(100)
            ->orderBy('id', 'DESC')
            ->get();

        $list = $model ? $model->toArray() : [];

        if (!empty($list)) {
            foreach ($list as $k => $value) {
                $list[$k]['express_configure'] = empty($value['express_configure']) ? '' : unserialize($value['express_configure']);
            }
        }

        return $list;
    }

    /**
     * 更新
     * @param string $code
     * @param array $data
     * @return bool
     */
    public function update($code = '', $data = [])
    {
        if (empty($code) || empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'express');

        $data['update_time'] = TimeRepository::getGmTime();

        return Express::where('code', $code)->update($data);
    }

    /**
     * 添加
     * @param string $code
     * @param array $data
     * @return bool
     */
    public function create($code = '', $data = [])
    {
        if (empty($code) || empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'express');

        $count = $this->expressCount($code);
        if (empty($count)) {
            $data['code'] = $code;
            $data['add_time'] = TimeRepository::getGmTime();
            return Express::create($data);
        }

        return false;
    }

    /**
     * 设置默认
     * @param string $code
     * @return bool
     */
    public function setDefault($code = '')
    {
        if (empty($code)) {
            return false;
        }

        $data['default'] = 0;
        return Express::where('code', '<>', $code)->update($data);
    }

    /**
     * 查询是否存在
     * @param string $code
     * @return mixed
     */
    public function expressCount($code = '')
    {
        if (empty($code)) {
            return false;
        }

        $count = Express::query()->where('code', $code)->count();

        return $count;
    }

    /**
     * 查询单条
     * @param string $code
     * @return array
     */
    public function expressInfo($code = '')
    {
        if (empty($code)) {
            return [];
        }

        $model = Express::query()->where('code', $code)->first();

        $info = $model ? $model->toArray() : [];

        if (!empty($info)) {
            $info['express_configure'] = empty($info['express_configure']) ? [] : \Opis\Closure\unserialize($info['express_configure']);
        }

        return $info;
    }

    /**
     * 获取快递跟踪配置
     * @param string $code
     * @return array|mixed
     */
    public function getExpressConfigure($code = '')
    {
        $info = $this->expressInfo($code);

        return $info['express_configure'] ?? [];
    }

    /**
     * 卸载删除
     * @param string $code
     * @return bool
     */
    public function uninstall($code = '')
    {
        if (empty($code)) {
            return false;
        }

        $model = Express::where('code', $code);

        if ($model) {
            $res = $model->delete();

            return $res;
        }

        return false;
    }

    /**
     * 旧快递跟踪配置
     * @return array
     */
    public function oldExpressConfig()
    {
        return ExpressCommonService::oldConfig();
    }


}
