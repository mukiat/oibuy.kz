<?php

namespace App\Modules\Seller\Controllers;

use App\Services\Merchant\ShopAddressService;
use App\Services\Region\RegionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Class AddressManageController
 * @package App\Modules\Seller\Controllers
 */
class AddressManageController extends BaseController
{
    /**
     * @var ShopAddressService
     */
    private $shopAddressService;

    /**
     * @var RegionService
     */
    private $regionService;

    /**
     * AddressManageController constructor.
     * @param ShopAddressService $shopAddressService
     * @param RegionService $regionService
     */
    public function __construct(ShopAddressService $shopAddressService, RegionService $regionService)
    {
        $this->shopAddressService = $shopAddressService;
        $this->regionService = $regionService;
    }

    protected function initialize()
    {
        parent::initialize();

        // 商家后台当前模块左侧选择菜单（子菜单）
        $child_menu = [
            '19_merchants_store' => [
                'address_manage' => 'address_manage?act=list',
                'address_manage_01' => 'address_manage?act=create',
                'address_manage_02' => 'address_manage?act=edit',
            ]
        ];

        // 商家后台子菜单语言包 用于当前位置显示
        $child_menu_lang = [
            'address_manage' => trans('admin/address_manage.address_manage'),
            'address_manage_01' => trans('admin/address_manage.create_address'),
            'address_manage_02' => trans('admin/address_manage.edit_address'),
        ];

        // 合并菜单语言包
        $GLOBALS['_LANG'] = array_merge($GLOBALS['_LANG'], $child_menu_lang);

        // 合并左侧菜单
        $left_menu = array_merge($GLOBALS['modules'], $child_menu);

        // 匹配选择的菜单列表
        $uri = request()->getRequestUri();
        $uri = ltrim($uri, '/');

        $menu_select = $this->get_menu_arr($uri, $left_menu);
        $this->assign('menu_select', $menu_select);

        // 当前位置
        $postion = ['ur_here' => $menu_select['label'] ?? ''];
        $this->assign('postion', $postion);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        /* act操作项的初始化 */
        $act = addslashes(trim($request->input('act', 'list')));

        if ($act == 'list') {
            $this->pageLimit(route('seller/address_manage', ['act' => 'list']), $this->page_num);

            $result = $this->shopAddressService->getAddressByRuID($this->ru_id);

            $page = $this->pageShow($result['total']);
            $this->assign('page', $page);
            $this->assign('list', $result['data']);

            return $this->display('seller.address_manage.index');
        }

        /**
         * 添加地址库的表单
         */
        if ($act == 'create') {
            $shopAddress = $this->shopAddressService->getAddressByRuID($this->ru_id);

            if ($shopAddress['total'] >= 2) {
                return $this->message(trans('admin/address_manage.address_create_failure'), null, 3, true);
            }

            $this->assign('shop_address', $shopAddress['data']);

            return $this->display('seller.address_manage.create');
        }

        if ($act == 'store') {
            $validator = Validator::make($request->all(), [
                'contact' => 'required',
                'mobile' => 'required',
                'district_id' => 'required',
                'address' => 'required',
                'type' => 'required',
            ], [
                'contact.required' => trans('admin/address_manage.contact_required'),
                'mobile.required' => trans('admin/address_manage.mobile_required'),
                'district_id.required' => trans('admin/address_manage.district_id_required'),
                'address.required' => trans('admin/address_manage.address_required'),
                'type.required' => trans('admin/address_manage.type_required'),
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return $this->message($error, null, 2, true);
            }

            // 处理地区信息
            $province = $this->regionService->getRegionInfo($request->get('province_id'));
            $city = $this->regionService->getRegionInfo($request->get('city_id'));
            $district = $this->regionService->getRegionInfo($request->get('district_id'));

            $addressType = $request->get('type');
            foreach ($addressType as $type) {
                $data = [
                    'ru_id' => $this->ru_id,
                    'contact' => $request->get('contact'),
                    'mobile' => $request->get('mobile'),
                    'province_id' => $request->get('province_id'),
                    'province' => $province['region_name'],
                    'city_id' => $request->get('city_id'),
                    'city' => $city['region_name'],
                    'district_id' => $request->get('district_id'),
                    'district' => $district['region_name'],
                    'address' => $request->get('address'),
                    'zip_code' => $request->get('zip_code') ?:'',
                    'type' => $type,
                    'created_at' => Carbon::now()
                ];

                $this->shopAddressService->createAddress($data);
            }

            return redirect()->route('seller/address_manage', ['act' => 'list']);
        }

        /**
         * 编辑地址库的表单
         */
        if ($act == 'edit') {
            $shopAddress = $this->shopAddressService->getAddressById($this->ru_id, $request->get('id'));

            if (empty($shopAddress)) {
                return $this->message(trans('admin/address_manage.address_does_not_exist'), null, 3, true);
            }

            $this->assign('address', $shopAddress);

            return $this->display('seller.address_manage.edit');
        }

        /**
         * 更新地址库
         */
        if ($act == 'update') {
            $validator = Validator::make($request->all(), [
                'contact' => 'required',
                'mobile' => 'required',
                'district_id' => 'required',
                'address' => 'required',
            ], [
                'contact.required' => trans('admin/address_manage.contact_required'),
                'mobile.required' => trans('admin/address_manage.mobile_required'),
                'district_id.required' => trans('admin/address_manage.district_id_required'),
                'address.required' => trans('admin/address_manage.address_required'),
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return $this->message($error, null, 2);
            }

            // 处理地区信息
            $province = $this->regionService->getRegionInfo($request->get('province_id'));
            $city = $this->regionService->getRegionInfo($request->get('city_id'));
            $district = $this->regionService->getRegionInfo($request->get('district_id'));

            $data = [
                'contact' => $request->get('contact'),
                'mobile' => $request->get('mobile'),
                'province_id' => $request->get('province_id'),
                'province' => $province['region_name'],
                'city_id' => $request->get('city_id'),
                'city' => $city['region_name'],
                'district_id' => $request->get('district_id'),
                'district' => $district['region_name'],
                'address' => $request->get('address'),
                'zip_code' => $request->get('zip_code') ?:'',
                'updated_at' => Carbon::now()
            ];

            $id = $request->get('id');

            $this->shopAddressService->updateAddress($this->ru_id, $id, $data);

            return redirect()->route('seller/address_manage', ['act' => 'list']);
        }

        /**
         * 删除地址库
         */
        if ($act == 'delete') {
            $id = $request->get('id');
            $id = is_array($id) ? $id : [$id];

            $this->shopAddressService->removeAddressByIds($this->ru_id, $id);

            return redirect()->route('seller/address_manage', ['act' => 'list']);
        }
    }
}
