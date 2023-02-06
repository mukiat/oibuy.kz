<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Api\Transformers\AddressTransformer;
use App\Models\UserAddress;
use App\Services\User\UserAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class AddressController
 * @package App\Api\Controllers
 */
class AddressController extends Controller
{
    protected $userAddressService;

    public function __construct(
        UserAddressService $userAddressService
    )
    {
        $this->userAddressService = $userAddressService;
    }

    /**
     * 返回所有收货地址列表
     *
     * @param Request $request
     * @param AddressTransformer $addressTransformer
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request, AddressTransformer $addressTransformer)
    {
        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        //收货地址列表
        $page = $request->input('page', 1);
        $size = $request->input('size', 50);

        $offset = [
            'start' => ($page - 1) * $size,
            'limit' => $size
        ];
        $addressList = $this->userAddressService->getUserAddressList($user_id, $offset);

        if (!empty($addressList)) {
            $addressList = $addressTransformer->transformCollection($addressList);
            $addressList = collect($addressList)->sortByDesc('is_checked')->values()->all();
        }

        return $this->succeed($addressList);
    }

    /**
     * 添加收货地址
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'consignee' => 'required|string',
            'country' => 'required|integer',
            'province' => 'required|integer',
            'city' => 'required|integer',
            'district' => 'required|integer',
            'address' => 'required|string',
            'mobile' => 'required|size:11'
        ]);

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        //整合参数
        $address = [
            'address_id' => (int)$request->get('address_id', 0),
            'consignee' => addslashes($request->get('consignee')),
            'mobile' => (int)$request->get('mobile'),
            'country' => (int)$request->get('country'),
            'province' => (int)$request->get('province'),
            'city' => (int)$request->get('city'),
            'district' => (int)$request->get('district'),
            'street' => (int)$request->get('street', 0),
            'address' => addslashes($request->get('address')),
            'zipcode' => addslashes($request->get('zipcode')),
            'user_id' => $user_id,
        ];

        // 收货地址不能超过50
        $address_count = UserAddress::where('user_id', $user_id)->count('address_id');
        if ($address_count > config('app.address_count', 50)) {
            return $this->setErrorCode(12)->failed(sprintf(lang('js_languages.js_languages.common.add_address_10'), config('app.address_count', 50)));
        }

        $result = $this->userAddressService->updateAddress($address);

        return $this->succeed($result);
    }

    /**
     * 收货地址详情
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'address_id' => 'required|integer'
        ]);

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = $this->userAddressService->getUserAddressInfo($request->get('address_id'), $user_id);

        return $this->succeed($result);
    }

    /**
     * 更新收货地址
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'address_id' => 'required|integer',
            'consignee' => 'required|string',
            'country' => 'required|integer',
            'province' => 'required|integer',
            'city' => 'required|integer',
            'district' => 'required|integer',
            'street' => 'required|integer',
            'address' => 'required|string',
            'mobile' => 'required|size:11'
        ]);

        //返回会员id
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $address_id = (int)$request->get('address_id', 0);

        //校验收货地址是否为当前用户的信息
        $address = $this->userAddressService->getUserAddressInfo($address_id, $user_id);
        if (empty($address)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        //整合参数
        $address = [
            'address_id' => $address_id,
            'consignee' => addslashes($request->get('consignee')),
            'mobile' => (int)$request->get('mobile'),
            'country' => (int)$request->get('country'),
            'province' => (int)$request->get('province'),
            'city' => (int)$request->get('city'),
            'district' => (int)$request->get('district'),
            'street' => (int)$request->get('street', 0),
            'address' => addslashes($request->get('address')),
            'zipcode' => addslashes($request->get('zipcode')),
            'user_id' => $user_id,
        ];

        $result = $this->userAddressService->updateAddress($address);

        return $this->succeed($result);
    }

    /**
     * 删除收货地址
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function destroy(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'address_id' => 'required|integer'
        ]);

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $address_id = (int)$request->get('address_id', 0);

        $result = $this->userAddressService->dropConsignee($address_id, $user_id);

        cache()->forget('flow_consignee_' . $user_id);

        return $this->succeed($result);
    }

    /**
     * 设置默认收货地址
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setDefault(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'address_id' => 'required|integer'
        ]);

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $address_id = (int)$request->get('address_id');
        $this->userAddressService->setDefaultAddress($user_id, $address_id);

        $result = $this->userAddressService->getUpdateFlowConsignee($address_id, $user_id);

        return $this->succeed($result);
    }

    /**
     * 同步微信收货地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function wximport(Request $request)
    {
        //数据验证
        $this->validate($request, []);

        $result = $this->userAddressService->wximportInfo($request->all());

        return $this->succeed($result);
    }

    /**
     * 匹配用户收货地址
     * @param Request $request
     * @return JsonResponse
     */
    public function match(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'province' => 'required|integer',
            'city' => 'required|integer',
            'district' => 'required|integer',
            'street' => 'required|integer',
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $area_cookie = [
            'province' => $request->input('province', 0),
            'city' => $request->input('city', 0),
            'district' => $request->input('district', 0),
            'street' => $request->input('street', 0),
        ];

        // 匹配用户收货地址
        $result = $this->userAddressService->match_user_consignee($user_id, $area_cookie);

        return $this->succeed($result);
    }
}
