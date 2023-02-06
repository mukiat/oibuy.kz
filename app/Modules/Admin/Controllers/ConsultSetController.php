<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\DscRepository;
use App\Services\ShopConfig\ShopConfigService;
use Illuminate\Http\Request;

/**
 * Class ConsultSetController
 * @package App\Modules\Admin\Controllers
 */
class ConsultSetController extends BaseController
{
    protected $ru_id = 0;

    protected $dscRepository;
    protected $shopConfigService;

    public function __construct(
        DscRepository $dscRepository,
        ShopConfigService $shopConfigService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->shopConfigService = $shopConfigService;
    }

    /**
     * 咨询设置
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function consult_set(Request $request)
    {
        $group_list = $this->shopConfigService->getShopConfig('consult_set');

        $consult_set = [];
        $code_arr = [
            'custom_jump_logo',
            'kefu_logo',
            'consult_share_img'
        ];
        $adminLogo = [
            'custom_jump_logo',
            'kefu_logo',
            'consult_share_img'
        ];
        if ($group_list) {
            $lang = trans('admin/consult_set');
            foreach ($group_list as $key => $item) {
                $item['name'] = isset($lang['cfg_name'][$item['code']]) ? $lang['cfg_name'][$item['code']] : $item['code'];
                $item['desc'] = isset($lang['cfg_desc'][$item['code']]) ? $lang['cfg_desc'][$item['code']] : '';

                if ($item['store_range']) {
                    $item['store_options'] = explode(',', $item['store_range']);
                    foreach ($item['store_options'] as $k => $v) {
                        $item['display_options'][$k] = isset($lang['cfg_range'][$item['code']][$v]) ? $lang['cfg_range'][$item['code']][$v] : $v;
                    }
                }

                if (in_array($item['code'], ['consult_kefu_url', 'custom_jump_url'])) {
                    $item['value'] = html_out($item['value']);
                }

                if ($item['type'] == 'file' && in_array($item['code'], $code_arr) && $item['value']) {
                    $item['del_img'] = 1;

                    if (strpos($item['value'], '../') === false) {
                        if (in_array($item['code'], $adminLogo)) {
                            if (!empty($item['value'])) {
                                $item['value'] = $this->dscRepository->getImagePath('assets/' . $item['value']);
                            } else {
                                $item['value'] = $this->dsc->url() . 'assets/' . $item['value'];
                            }
                        } else {
                            $item['value'] = $this->dscRepository->getImagePath($item['value']);
                        }
                    }
                } else {
                    $item['del_img'] = 0;
                }

                $consult_set[] = $item;
            }
        }

        $this->assign('consult_set', $consult_set);
        $this->assign('ur_here', trans('admin/common.06_consult_set'));
        return $this->display();
    }


}
