<?php

namespace App\Services\Navigator;

use App\Repositories\Common\DscRepository;
use App\Repositories\Touch\TouchNavRepository;

/**
 * Class TouchNavManageService
 * @package App\Services\Navigator
 */
class TouchNavManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 工具栏列表
     *
     * @param string $device
     * @param int $parent_id
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public function getList($device = '', $parent_id = 0, $offset = [], $filter = [])
    {
        $result = TouchNavRepository::getList($device, $parent_id, $offset, $filter);

        $total = $result['total'] ?? 0;
        $list = $result['list'] ?? [];

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                if (!empty($val['parent_nav'])) {
                    $list[$key]['parent_name'] = $val['parent_nav']['name'] ?? '';
                }

                $list[$key]['pic'] = $this->dscRepository->getImagePath($val['pic'] ?? '');
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 添加工具栏
     *
     * @param array $data
     * @return bool
     */
    public function createNav($data = [])
    {
        return TouchNavRepository::create($data);
    }

    /**
     * 更新工具栏
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateNav($id = 0, $data = [])
    {
        return TouchNavRepository::update($id, $data);
    }

    /**
     * 检查是否有子级
     *
     * @param int $id
     * @return bool
     */
    public function check($id = 0)
    {
        return TouchNavRepository::check($id);
    }

    /**
     * 删除工具栏
     *
     * @param int $id
     * @return bool
     */
    public function deleteNav($id = 0)
    {
        return TouchNavRepository::delete($id);
    }

    /**
     * 工具栏信息
     *
     * @param int $id
     * @return array
     */
    public function navInfo($id = 0)
    {
        $result = TouchNavRepository::info($id);

        if (!empty($result)) {
            $result['pic'] = $this->dscRepository->getImagePath($result['pic'] ?? '');
        }

        return $result;
    }

    /**
     * 工具栏分类列表
     *
     * @param string $device
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public function getParentList($device = '', $offset = [], $filter = [])
    {
        return TouchNavRepository::getParentList($device, $offset, $filter);
    }

    /**
     * 工具栏分类
     *
     * @param string $device
     * @param int $limit
     * @return array
     */
    public function parentNav($device = 'h5', $limit = 100)
    {
        return TouchNavRepository::parentNav($device, $limit);
    }

    /**
     * 选择常用页面链接 客户端 h5,wxapp,app
     * @return array
     */
    public static function devicePageUrl()
    {
        $list = TouchNavRepository::device_page_url();
        if (!empty($list)) {
            $list = collect($list)->map(function ($item) {
                $item['url'] = $item['url'] ?? '';
                if (!empty($item['url']) && stripos(substr($item['url'], 0, 4), 'http') !== false) {
                    $item['url'] = str_replace(dsc_url('/'), '', $item['url']); // 去除域名
                }
                return $item;
            })->values()->all();
        }

        return $list;
    }

}