<?php

namespace App\Services\Navigator;

use App\Repositories\Common\DscRepository;
use App\Repositories\Touch\TouchNavRepository;

/**
 * Class TouchNavService
 * @package App\Services\Navigator
 */
class TouchNavService
{
    protected $dscRepository;
    protected $touchNavRepository;

    public function __construct(
        DscRepository $dscRepository,
        TouchNavRepository $touchNavRepository
    )
    {
        $this->dscRepository = $dscRepository;
        $this->touchNavRepository = $touchNavRepository;
    }

    /**
     * 自定义工具栏 列表
     * @param string $device
     * @param string $page_flag
     * @param int $top_id
     * @return array
     */
    public function getTouchNav($device = '', $page_flag = '', $top_id = 0)
    {
        $result = $this->touchNavRepository->getTouchNav($device, $page_flag, $top_id);

        $list = [];
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $list[$key]['top_id'] = $top_id = $val['id'] ?? 0;
                $list[$key]['name'] = $val['name'] ?? '';

                $child_nav = $val['child_nav'] ?? [];
                if (!empty($child_nav)) {
                    foreach ($child_nav as $k => $item) {
                        $item['pic'] = $this->dscRepository->getImagePath($item['pic'] ?? '');

                        $item['url'] = $item['url'] ?? '';
                        $item['device'] = $item['device'] ?? '';
                        if (!empty($item['url']) && $item['device'] == 'h5' && stripos(substr($item['url'], 0, 4), 'http') === false) {
                            $item['url'] = dsc_url($item['url']); // 增加域名
                        }

                        $child_nav[$k] = $item;
                    }
                }

                $list[$key]['child_nav'] = $child_nav ?? [];
            }
        }

        return $list;
    }
}