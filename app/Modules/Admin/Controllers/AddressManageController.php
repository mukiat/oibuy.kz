<?php

namespace App\Modules\Admin\Controllers;

use App\Services\Merchant\ShopAddressService;
use App\Services\Region\RegionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Class AddressManageController
 * @package App\Modules\Admin\Controllers
 */
class AddressManageController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        $this->init_params();
    }

    /**
     * @param Request $request
     * @param ShopAddressService $shopAddressService
     * @return string
     */
    public function index(Request $request, ShopAddressService $shopAddressService)
    {
        $this->pageLimit(route('address_manage.index'), $this->page_num);

        $result = $shopAddressService->getAddressByRuID($this->ru_id);

        $page = $this->pageShow($result['total']);
        $this->assign('page', $page);
        $this->assign('list', $result['data']);

        return $this->display('admin.address_manage.list');
    }

    /**
     * 显示添加表单
     * @param ShopAddressService $shopAddressService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(ShopAddressService $shopAddressService)
    {
        $shopAddress = $shopAddressService->getAddressByRuID($this->ru_id);

        if ($shopAddress['total'] >= 2) {
            return $this->message(trans('admin/address_manage.address_create_failure'), null, 3);
        }

        $this->assign('shop_address', $shopAddress['data']);

        return $this->display('admin.address_manage.create');
    }

    /**
     * 保存地址
     * @param Request $request
     * @param ShopAddressService $shopAddressService
     * @param RegionService $regionService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function store(Request $request, ShopAddressService $shopAddressService, RegionService $regionService)
    {
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
            return $this->message($error, null, 2);
        }

        // 处理地区信息
        $province = $regionService->getRegionInfo($request->get('province_id'));
        $city = $regionService->getRegionInfo($request->get('city_id'));
        $district = $regionService->getRegionInfo($request->get('district_id'));

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

            $shopAddressService->createAddress($data);
        }

        return redirect()->route('address_manage.index');
    }

    public function edit(Request $request, $id, ShopAddressService $shopAddressService)
    {
        $shopAddress = $shopAddressService->getAddressById($this->ru_id, $id);

        $this->assign('address', $shopAddress);

        return $this->display('admin.address_manage.edit');
    }

    /**
     * @param Request $request
     * @param ShopAddressService $shopAddressService
     * @param RegionService $regionService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function update(Request $request, ShopAddressService $shopAddressService, RegionService $regionService)
    {
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
        $province = $regionService->getRegionInfo($request->get('province_id'));
        $city = $regionService->getRegionInfo($request->get('city_id'));
        $district = $regionService->getRegionInfo($request->get('district_id'));

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

        $shopAddressService->updateAddress($this->ru_id, $id, $data);

        return redirect()->route('address_manage.index');
    }

    /**
     * 移除地址
     * @param Request $request
     * @param ShopAddressService $shopAddressService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, ShopAddressService $shopAddressService)
    {
        $id = $request->get('id');
        $id = is_array($id) ? $id : [$id];

        $shopAddressService->removeAddressByIds($this->ru_id, $id);

        return redirect()->route('address_manage.index');
    }
}
