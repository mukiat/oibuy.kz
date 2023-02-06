<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\MerchantsGrade;
use App\Models\MerchantsPrivilege;
use App\Models\MerchantsShopInformation;
use App\Models\MerchantsStepsFields;
use App\Models\SellerGrade;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\PersonalMerchants\PersonalMerchantsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class MerchantsController
 * @package App\Api\Controllers
 */
class MerchantsController extends Controller
{
    protected $step_id = 0; // 标识步骤
    protected $merchantCommonService;
    protected $dscRepository;
    protected $commonRepository;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        CommonRepository $commonRepository
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->commonRepository = $commonRepository;
    }

    /**
     * 入驻商家信息
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        $user_id = $this->uid;

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $this->step_id = 1;

        // 验证商家是否申请   审核状态：  merchants_audit   0 正在审核， 1 审核通过， 2 审核未通过
        $shop = $this->merchantCommonService->getMerchantsShopInformation($user_id);
        if (!empty($shop)) {
            $step_id = $request->input('step_id', 6);
            $shop['step_id'] = $step_id;
            $result['shop'] = $shop;
        }

        // 验证PC商家入驻申请流程 - 公司信息认证
        $steps = $this->merchantCommonService->getMerchantsStepsFields($user_id);

        if (!empty($steps)) {
            $this->step_id = 2;
        }

        $result['step_id'] = $this->step_id;

        //处理图片
        $steps['legal_person_fileImg'] = isset($steps['legal_person_fileImg']) && !empty($steps['legal_person_fileImg']) ? $this->dscRepository->getImagePath($steps['legal_person_fileImg']) : '';
        $steps['license_fileImg'] = isset($steps['license_fileImg']) && !empty($steps['license_fileImg']) ? $this->dscRepository->getImagePath($steps['license_fileImg']) : '';
        $steps['id_card_img_one_fileImg'] = isset($steps['id_card_img_one_fileImg']) && !empty($steps['id_card_img_one_fileImg']) ? $this->dscRepository->getImagePath($steps['id_card_img_one_fileImg']) : '';
        $steps['id_card_img_two_fileImg'] = isset($steps['id_card_img_two_fileImg']) && !empty($steps['id_card_img_two_fileImg']) ? $this->dscRepository->getImagePath($steps['id_card_img_two_fileImg']) : '';
        $steps['id_card_img_three_fileImg'] = isset($steps['id_card_img_three_fileImg']) && !empty($steps['id_card_img_three_fileImg']) ? $this->dscRepository->getImagePath($steps['id_card_img_three_fileImg']) : '';

        $result['steps'] = empty($steps) ? '' : $steps;

        // 是否存在跨境
        if (CROSS_BORDER === true) {
            $result['cross_border_version'] = true;
        }

        return $this->succeed($result);
    }

    /**
     * 入驻须知
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function guide(Request $request)
    {
        $this->step_id = $request->input('step', 1);

        $user_id = $this->uid;

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = $this->merchantCommonService->getMerchantsStepsProcess($this->step_id);
        $result['step_id'] = $this->step_id;

        $result['is_permer'] = 0;
        if (PERSONAL_MERCHANTS === true) { // 个人入驻
            $permer = PersonalMerchantsService::permerExists();
            if (!empty($permer)) {
                $result['is_permer'] = 1;
            }
        }

        return $this->succeed($result);
    }

    /**
     * 查看申请信息
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function applyInfo(Request $request)
    {
        $user_id = $this->uid;

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        // 获取会员申请入驻商家信息   审核状态：  merchants_audit   0 正在审核， 1 审核通过， 2 审核未通过
        $shop = $this->merchantCommonService->getMerchantsShopInformation($user_id);
        if (!empty($shop)) {

            $shop['rz_shopName'] = $shop['rz_shop_name'];
            $shop['hopeLoginName'] = $shop['hope_login_name'];

            $result['shop'] = $shop;
            // 获得店铺入驻流程扩展信息
            $steps = $this->merchantCommonService->getMerchantsStepsFields($user_id);
            $result['steps'] = empty($steps) ? '' : $steps;
            $result['steps']['legal_person_fileImg'] = $result['steps']['legal_person_fileImg'] ? $this->dscRepository->getImagePath($result['steps']['legal_person_fileImg']) : '';
            $result['steps']['license_fileImg'] = $result['steps']['license_fileImg'] ? $this->dscRepository->getImagePath($result['steps']['license_fileImg']) : '';
            $result['steps']['id_card_img_one_fileImg'] = $result['steps']['id_card_img_one_fileImg'] ? $this->dscRepository->getImagePath($result['steps']['id_card_img_one_fileImg']) : '';
            $result['steps']['id_card_img_three_fileImg'] = $result['steps']['id_card_img_three_fileImg'] ? $this->dscRepository->getImagePath($result['steps']['id_card_img_three_fileImg']) : '';
            $result['steps']['id_card_img_two_fileImg'] = $result['steps']['id_card_img_two_fileImg'] ? $this->dscRepository->getImagePath($result['steps']['id_card_img_two_fileImg']) : '';
            // 经营类目
            $category_info = get_fine_category_info(0, $user_id); // 详细类目
            //格式化详细类目
            $category = [];
            foreach ($category_info as $k => $v) {
                $category[$v['parent_id']]['parent_name'] = $v['parent_name'];
                $category[$v['parent_id']]['cat_name'] .= isset($category[$v['parent_id']]['cat_name']) ? '、' . $v['cat_name'] : $v['cat_name'];
            }

            $result['category'] = empty($category) ? '' : $category;
        }
        // 是否存在跨境
        if (CROSS_BORDER === true) {
            $result['cross_border_version'] = true;
        }

        return $this->succeed($result);
    }

    /**
     * 同意协议
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function agree(Request $request)
    {
        // 数据验证
        $this->validate($request, [
            'agree' => 'required|integer'
        ]);

        $user_id = $this->uid;
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $agree = $request->input('agree', 0); // 同意协议
        if (empty($agree)) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.please_agree_agreement')]);
        }

        $data['agreement'] = $agree;

        $data['fid'] = $request->input('fid', 0);

        $data['contactXinbie'] = $request->input('contactXinbie', ''); // 性别
        $data['contactName'] = $request->input('contactName', ''); // 姓名
        $data['contactPhone'] = $request->input('contactPhone', ''); // 手机号
        $data['contactEmail'] = $request->input('contactEmail', ''); // 邮箱
        $data['license_adress'] = $request->input('license_adress', '');

        $data['company'] = $request->input('companyName', ''); // 公司名称
        $data['legal_person_fileImg'] = $request->input('legal_person_fileImg', ''); // 身份证照片
        $data['license_fileImg'] = $request->input('license_fileImg', ''); // 公司营业执照
        $data['company_contactTel'] = $request->input('company_contactTel', ''); // 公司联系电话

        $province_region_id = $request->input('province_region_id', 0);
        $city_region_id = $request->input('city_region_id', 0);
        $district_region_id = $request->input('district_region_id', 0);
        if (!empty($province_region_id)) {
            $data['company_located'] = $province_region_id . ',' . $city_region_id . ',' . $district_region_id;
        }
        if (empty($data['contactName'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.msg_shop_owner_notnull')]);
        }
        if (empty($data['contactPhone'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.mobile_not_null')]);
        }
        if ($data['contactPhone'] && !CommonRepository::getMatchPhone($data['contactPhone'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.mobile_phone_invalid')]);
        }
        $data['user_id'] = $user_id;
        $data['is_personal'] = 0;

        // 图片处理
        $file_arr = [
            'legal_person_fileImg' => $data['legal_person_fileImg'],
            'license_fileImg' => $data['license_fileImg'],
        ];
        $file_arr = $this->dscRepository->transformOssFile($file_arr);
        $data['legal_person_fileImg'] = $file_arr['legal_person_fileImg'];
        $data['license_fileImg'] = $file_arr['license_fileImg'];

        if (!empty($data['fid'])) {
            $fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
            if (!empty($fields)) {
                $legal_person_fileImg_path = $this->dscRepository->editUploadImage($fields['legal_person_fileImg']);
                $license_fileImg_path = $this->dscRepository->editUploadImage($fields['license_fileImg']);

                // 删除原图片
                if ($data['legal_person_fileImg'] && $legal_person_fileImg_path != $data['legal_person_fileImg']) {
                    $legal_person_fileImg_path = strpos($legal_person_fileImg_path, 'no_image') == false ? $legal_person_fileImg_path : ''; // 不删除默认空图片
                    $this->remove($legal_person_fileImg_path);
                }
                if ($data['license_fileImg'] && $license_fileImg_path != $data['license_fileImg']) {
                    $license_fileImg_path = strpos($license_fileImg_path, 'no_image') == false ? $license_fileImg_path : ''; // 不删除默认空图片
                    $this->remove($license_fileImg_path);
                }
            }

            // 更新申请进度
            $this->merchantCommonService->updateMerchantsStepsFields($data['fid'], $user_id, $data);

            $result = ['code' => 0, 'msg' => lang('common.update_Success')];
        } else {
            // 新增申请进度
            $this->merchantCommonService->createMerchantsStepsFields($data);
            $result = ['code' => 0, 'msg' => lang('common.Submit_Success')];
        }

        // 是否存在跨境
        if (CROSS_BORDER === true) {
            $huoyuan = $request->input('huoyuan', '');  // cbec
            if (!empty($huoyuan)) {
                $cross = 'App\\Custom\\CrossBorder\\Controllers\\WebController';
                if (class_exists($cross)) {
                    // 更新货源信息
                    app($cross)->updateSource($user_id, $huoyuan);
                }
            }
        }

        return $this->succeed($result);
    }

    /**
     * 入驻店铺信息
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function shop(Request $request)
    {
        $user_id = $this->uid;

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $this->step_id = 3;

        // 验证商家是否申请   审核状态：  merchants_audit   0 正在审核， 1 审核通过， 2 审核未通过
        $shop = $this->merchantCommonService->getMerchantsShopInformation($user_id);
        if (!empty($shop)) {
            $shop['step_id'] = $this->step_id;
            $result['shop'] = $shop;
        }

        if ($this->step_id > 1 && $this->step_id < 4) {
            //删除商家入驻流程填写分类临时信息
            $this->merchantCommonService->deleleMerchantsCategoryTemporarydate($user_id);
        }
        // 顶级分类
        $category = get_first_cate_list(0, 0);
        foreach ($category as $key => $value) {
            $category[$key]['cat_name'] = !empty($value['cat_alias_name']) ? $value['cat_alias_name'] : $value['cat_name'];
            $category[$key]['childCate'] = get_first_cate_list($value['cat_id'], 0);
            if (!empty($category[$key]['childCate'])) {
                foreach ($category[$key]['childCate'] as $k => $v) {
                    $category[$key]['childCate'][$k]['cat_name'] = !empty($v['cat_alias_name']) ? $v['cat_alias_name'] : $v['cat_name'];
                }
            }
        }

        $result['step_id'] = $this->step_id;
        $result['category'] = $category;

        return $this->succeed($result);
    }

    /**
     * 提交入驻店铺信息
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function add_shop(Request $request)
    {
        $user_id = $this->uid;

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $request->input('data', []);

        if (empty($data['rz_shop_name'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.msg_shop_name_notnull')]);
        }
        if (empty($data['hope_login_name'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.msg_login_shop_name_notnull')]);
        }
        if (empty($data['shoprz_type'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.msg_shoprz_type_notnull')]);
        }
        if (empty($data['shop_category_main'])) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.msg_shop_category_main_notnull')]);
        }

        $data['user_id'] = $user_id;

        // 检查店铺期望名是否使用
        $check_shopname = $this->merchantCommonService->checkMerchantsShopName($user_id, $data['rz_shop_name']);
        if ($check_shopname) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants_steps_action.Settled_Prompt')]);
        }

        // 检查店铺登陆用户名是否使用
        $check_loginname = $this->merchantCommonService->checkMerchantsHopeLoginName($user_id, $data['hope_login_name']);
        if ($check_loginname) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants_steps_action.Settled_Prompt_name')]);
        }

        // 更新子分类
        $catId_array = get_catId_array($user_id);
        $data['user_shop_main_category'] = implode('-', $catId_array);

        // 新增入驻商家信息
        $res = $this->merchantCommonService->createMerchantsShopInformation($data);
        if ($res == true) {

            /**
             * 设置审核店铺功能
             */
            if (in_array(config('shop.seller_review'), [0, 1, 2])) {

                $ec_rz_shopName = $data['rz_shop_name'];
                $ec_hopeLoginName = $data['hope_login_name'];

                if (in_array(config('shop.seller_review'), [0, 1])) {
                    $sellerData['check_sellername'] = 1;
                    $sellerData['review_status'] = 3;
                    $sellerData['shopname_audit'] = 1;

                    $sellerInfo = SellerShopinfo::select('id', 'shop_name')->where('ru_id', $user_id);
                    $sellerInfo = BaseRepository::getToArrayFirst($sellerInfo);

                    if ($sellerInfo) {
                        SellerShopinfo::where('ru_id', $user_id)->update($sellerData);
                    } else {
                        $sellerData['ru_id'] = $user_id;
                        SellerShopinfo::insert($sellerData);
                    }
                }

                if (in_array(config('shop.seller_review'), [0, 2])) {

                    MerchantsShopInformation::where('user_id', $user_id)->update([
                        'merchants_audit' => 1,
                        'review_goods' => 0
                    ]);

                    $grade_id = SellerGrade::query()->orderBy('seller_temp')->value('id');
                    $grade_id = $grade_id ? $grade_id : 0;

                    MerchantsGrade::insert([
                        'ru_id' => $user_id,
                        'grade_id' => $grade_id,
                        'add_time' => TimeRepository::getGmTime(),
                        'year_num' => 1,
                        'amount' => 0
                    ]);

                    //添加管理员
                    $pwd = config('shop.merchants_prefix') . $user_id;

                    // 生成hash
                    $GLOBALS['user'] = init_users();
                    $password = $GLOBALS['user']->hash_password($pwd);

                    //入驻默认初始权限
                    $action_list = MerchantsPrivilege::where('grade_id', $grade_id)->value('action_list');
                    $action_list = $action_list ? $action_list : '';

                    $res = AdminUser::select('nav_list')->where('action_list', 'all');
                    $row = BaseRepository::getToArrayFirst($res);

                    $merchantsStepsFields = MerchantsStepsFields::where('user_id', $user_id);
                    $merchantsStepsFields = BaseRepository::getToArrayFirst($merchantsStepsFields);

                    $other = [
                        'user_name' => $ec_hopeLoginName,
                        'password' => $password,
                        'email' => $merchantsStepsFields['contactEmail'] ?? '',
                        'nav_list' => $row['nav_list'],
                        'action_list' => $action_list,
                        'ru_id' => $user_id
                    ];
                    AdminUser::insert($other);

                    $mobile = $merchantsStepsFields['contactPhone'] ?? '';
                    if (!empty($mobile)) {

                        /* 如果需要，发短信 */
                        if (config('shop.sms_seller_signin') == 1) {

                            $time = TimeRepository::getGmTime();

                            //短信接口参数
                            $smsParams = [
                                'seller_name' => $ec_rz_shopName,
                                'sellername' => $ec_rz_shopName,
                                'login_name' => $ec_hopeLoginName ? htmlspecialchars($ec_hopeLoginName) : '',
                                'loginname' => $ec_hopeLoginName ? htmlspecialchars($ec_hopeLoginName) : '',
                                'password' => $pwd ? htmlspecialchars($pwd) : '',
                                'admin_name' => $ec_hopeLoginName ? $ec_hopeLoginName : '',
                                'adminname' => $ec_hopeLoginName ? $ec_hopeLoginName : '',
                                'edit_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', $time),
                                'edittime' => TimeRepository::getLocalDate('Y-m-d H:i:s', $time),
                                'mobile_phone' => $mobile ? $mobile : '',
                                'mobilephone' => $mobile ? $mobile : ''
                            ];

                            $this->commonRepository->smsSend($mobile, $smsParams, 'sms_seller_signin', false);
                        }
                    }
                }
            }

            // 更新临时类目表 商家入驻流程填写分类临时信息
            $this->merchantCommonService->updateMerchantsCategoryTemporarydate($user_id);

            // 成功入驻 等待审核
            $result = ['code' => 0, 'msg' => lang('merchants_steps.merchants_step_complete_one')];
        } else {
            $result = ['code' => 1, 'msg' => lang('common.Submit_fail')];
        }

        return $this->succeed($result);
    }

    /**
     * 获取下级类目
     * @param Request $request
     * @return JsonResponse
     */
    public function get_child_cate(Request $request)
    {
        $cat_id = $request->input('cat_id', 0);

        $childCate = [];
        if ($cat_id > 0) {
            $childCate = get_first_cate_list($cat_id, 0);
            if (!empty($childCate)) {
                foreach ($childCate as $key => $value) {
                    $childCate[$key]['cat_name'] = !empty($value['cat_alias_name']) ? $value['cat_alias_name'] : $value['cat_name'];
                }
            }
        }

        $result['childCate'] = collect($childCate)->values()->all();

        return $this->succeed($result);
    }

    /**
     * 添加详细类目 - 二级类目数据插入临时数据表
     * @param Request $request
     * @return JsonResponse
     */
    public function add_child_cate(Request $request)
    {
        $data = $request->input('data', []);
        $user_id = $this->uid;

        $this->merchantCommonService->deleleMerchantsCategoryTemporarydateAll($user_id);

        $category_info = [];
        foreach ($data as $k => $v) {
            if (!empty($v[0])) {
                // 删除主分类下子分类
                $this->merchantCommonService->deleleMerchantsCategoryTemporarydateByCateid($v[0], $user_id);
            }

            if (!empty($v[1])) {
                $category_info = get_fine_category_info($v[1], $user_id);
            } else {
                $category_info = get_fine_category_info(0, $user_id);
            }
        }

        $result['category_info'] = collect($category_info)->values()->all();

        return $this->succeed($result);
    }

    /**
     * 删除详细类目
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function delete_child_cate(Request $request)
    {
        $ct_id = $request->input('ct_id', 0);

        if ($ct_id > 0) {
            $catParent = get_temporarydate_ctId_catParent($ct_id);
            if ($catParent && $catParent['num'] == 1) {
                // 删除商家入驻流程分类资质信息
                $this->merchantCommonService->deleteMerchantsDtFile($catParent['parent_id']);
            }

            // 删除商家入驻流程填写分类临时信息
            $this->merchantCommonService->deleleMerchantsCategoryTemporarydateByCtid($ct_id);

            $result = ['code' => 0, 'msg' => lang('common.delete_success'), 'ct_id' => $ct_id];
        } else {
            $result = ['code' => 1, 'msg' => lang('common.Submit_fail')];
        }

        return $this->succeed($result);
    }

    /**
     * 等待审核
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function audit(Request $request)
    {
        $user_id = $this->uid;

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $this->step_id = 6;

        // 验证商家是否申请  审核状态：  merchants_audit   0 正在审核， 1 审核通过， 2 审核未通过
        $shop = $this->merchantCommonService->getMerchantsShopInformation($user_id);
        if (!empty($shop)) {

            $shop['rz_shopName'] = $shop['rz_shop_name'];

            $shop['step_id'] = $this->step_id;
            $result['shop'] = $shop;

            return $this->succeed($result);
        }

        return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
    }

    /**
     * 同意协议
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function agree_personal(Request $request)
    {
        // 数据验证
        $this->validate($request, [
            'agree' => 'required|integer'
        ]);

        $user_id = $this->uid;
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $agree = $request->input('agree', 0); // 同意协议
        if (empty($agree)) {
            return $this->succeed(['code' => 1, 'msg' => lang('merchants.please_agree_agreement')]);
        }

        $data['agreement'] = $agree;

        $data['fid'] = $request->input('fid', 0);

        $data['name'] = $request->input('name', ''); // 真实姓名
        $data['id_card'] = $request->input('id_card', ''); // 身份证号
        $data['business_address'] = $request->input('business_address', ''); // 经营地址
        $data['business_category'] = $request->input('business_category', ''); // 经营类目
        $data['id_card_img_one_fileImg'] = $request->input('id_card_img_one_fileImg', ''); //身份证正面
        $data['id_card_img_two_fileImg'] = $request->input('id_card_img_two_fileImg', ''); // 身份证反面
        $data['id_card_img_three_fileImg'] = $request->input('id_card_img_three_fileImg', ''); // 手持身份证上半身照
        $data['commitment_fileImg'] = $request->input('commitment_fileImg', ''); // 个人承诺书
        $data['mobile'] = $request->input('mobile', ''); // 联系电话

        $data['is_personal'] = 1;

        $data['user_id'] = $user_id;

        // 图片处理
        $file_arr = [
            'id_card_img_one_fileImg' => $data['id_card_img_one_fileImg'],
            'id_card_img_two_fileImg' => $data['id_card_img_two_fileImg'],
            'id_card_img_three_fileImg' => $data['id_card_img_three_fileImg'],
            'commitment_fileImg' => $data['commitment_fileImg'],
        ];
        $file_arr = $this->dscRepository->transformOssFile($file_arr);
        $data['id_card_img_one_fileImg'] = $file_arr['id_card_img_one_fileImg'];
        $data['id_card_img_two_fileImg'] = $file_arr['id_card_img_two_fileImg'];
        $data['id_card_img_three_fileImg'] = $file_arr['id_card_img_three_fileImg'];
        $data['commitment_fileImg'] = $file_arr['commitment_fileImg'];

        if (!empty($data['fid'])) {
            $fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
            if (!empty($fields)) {
                $id_card_img_one_fileImg = $this->dscRepository->editUploadImage($fields['id_card_img_one_fileImg']);
                $id_card_img_two_fileImg = $this->dscRepository->editUploadImage($fields['id_card_img_two_fileImg']);
                $id_card_img_three_fileImg = $this->dscRepository->editUploadImage($fields['id_card_img_three_fileImg']);
                $commitment_fileImg = $this->dscRepository->editUploadImage($fields['commitment_fileImg']);

                // 删除原图片
                if ($data['id_card_img_one_fileImg'] && $id_card_img_one_fileImg != $data['id_card_img_one_fileImg']) {
                    $id_card_img_one_fileImg = strpos($id_card_img_one_fileImg, 'no_image') == false ? $id_card_img_one_fileImg : ''; // 不删除默认空图片
                    $this->remove($id_card_img_one_fileImg);
                }
                if ($data['id_card_img_two_fileImg'] && $id_card_img_two_fileImg != $data['id_card_img_two_fileImg']) {
                    $id_card_img_two_fileImg = strpos($id_card_img_two_fileImg, 'no_image') == false ? $id_card_img_two_fileImg : ''; // 不删除默认空图片
                    $this->remove($id_card_img_two_fileImg);
                }
                if ($data['id_card_img_three_fileImg'] && $id_card_img_three_fileImg != $data['id_card_img_three_fileImg']) {
                    $id_card_img_three_fileImg = strpos($id_card_img_three_fileImg, 'no_image') == false ? $id_card_img_three_fileImg : ''; // 不删除默认空图片
                    $this->remove($id_card_img_three_fileImg);
                }
                if ($data['commitment_fileImg'] && $commitment_fileImg != $data['commitment_fileImg']) {
                    $commitment_fileImg = strpos($commitment_fileImg, 'no_image') == false ? $commitment_fileImg : ''; // 不删除默认空图片
                    $this->remove($commitment_fileImg);
                }
            }

            // 更新申请进度
            $this->merchantCommonService->updateMerchantsStepsFields($data['fid'], $user_id, $data);

            $result = ['code' => 0, 'msg' => lang('common.update_Success')];
        } else {
            // 新增申请进度
            $this->merchantCommonService->createMerchantsStepsFields($data);
            $result = ['code' => 0, 'msg' => lang('common.Submit_Success')];
        }

        return $this->succeed($result);
    }
}
