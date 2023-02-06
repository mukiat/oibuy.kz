<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\AdminAction;
use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Goods;
use App\Models\MerchantsAccountLog;
use App\Models\MerchantsCategoryTemporarydate;
use App\Models\MerchantsDtFile;
use App\Models\MerchantsGrade;
use App\Models\MerchantsPercent;
use App\Models\MerchantsPrivilege;
use App\Models\MerchantsServer;
use App\Models\MerchantsShopBrand;
use App\Models\MerchantsShopBrandfile;
use App\Models\MerchantsShopInformation;
use App\Models\MerchantsStepsFields;
use App\Models\OrderInfo;
use App\Models\PresaleActivity;
use App\Models\Region;
use App\Models\SellerApplyInfo;
use App\Models\SellerDomain;
use App\Models\SellerGrade;
use App\Models\SellerQrcode;
use App\Models\SellerShopbg;
use App\Models\SellerShopheader;
use App\Models\SellerShopinfo;
use App\Models\SellerShopinfoChangelog;
use App\Models\SellerShopslide;
use App\Models\SellerShopwindow;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Rules\PasswordRule;
use App\Services\Comment\CommentService;
use App\Services\Commission\CommissionService;
use App\Services\Common\ConfigService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Flow\FlowUserService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Merchant\MerchantsUsersListManageService;
use App\Services\Order\OrderCommonService;
use App\Services\PersonalMerchants\PersonalMerchantsService;
use App\Services\Store\StoreCommonService;
use App\Services\Store\StoreService;
use Illuminate\Support\Facades\Validator;

/**
 * 会员管理程序
 */
class MerchantsUsersListController extends InitController
{
    protected $storeService;
    protected $commonRepository;
    protected $merchantCommonService;
    protected $commentService;
    protected $dscRepository;
    protected $merchantsUsersListManageService;
    protected $storeCommonService;
    protected $flowUserService;

    public function __construct(
        StoreService $storeService,
        CommonRepository $commonRepository,
        MerchantCommonService $merchantCommonService,
        CommentService $commentService,
        DscRepository $dscRepository,
        MerchantsUsersListManageService $merchantsUsersListManageService,
        StoreCommonService $storeCommonService,
        FlowUserService $flowUserService
    )
    {
        $this->storeService = $storeService;
        $this->commonRepository = $commonRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->commentService = $commentService;
        $this->dscRepository = $dscRepository;
        $this->merchantsUsersListManageService = $merchantsUsersListManageService;
        $this->storeCommonService = $storeCommonService;
        $this->flowUserService = $flowUserService;
    }

    public function index()
    {
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        $act = e(request()->input('act', ''));

        /*------------------------------------------------------ */
        //-- 申请流程列表
        /*------------------------------------------------------ */

        if ($act == 'list') {
            /* 检查权限 */
            admin_priv('users_merchants');

            $this->smarty->assign('menu_select', ['action' => '17_merchants', 'current' => '02_merchants_users_list']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['02_merchants_users_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_merchants_user_add'], 'href' => 'merchants_users_list.php?act=add_shop']);
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['02_initialize_seller_rank'], 'href' => 'merchants_users_list.php?act=create_initialize_rank']);

            $users_list = $this->merchantsUsersListManageService->stepsUsersList($adminru);

            $is_permer = 0;
            if (PERSONAL_MERCHANTS === true) { // 个人入驻
                $permer = PersonalMerchantsService::permerExists();
                if (!empty($permer)) {
                    $is_permer = 1;
                    foreach ($users_list['users_list'] as $k => $v) {
                        $users_list['users_list'][$k]['is_personal'] = $permer->getPersonal($v['user_id']);
                    }
                }
            }

            $this->smarty->assign('is_permer', $is_permer);
            $this->smarty->assign('users_list', $users_list['users_list']);
            $this->smarty->assign('filter', $users_list['filter']);
            $this->smarty->assign('record_count', $users_list['record_count']);
            $this->smarty->assign('page_count', $users_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('sort_user_id', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            //获取未审核商家
            $shop_account = MerchantsShopInformation::where('merchants_audit', 0)->count();
            $this->smarty->assign('shop_account', $shop_account);

            /* 未审核店铺信息 */
            $res = MerchantsShopInformation::query()->whereHasIn('getUsers', function ($query) use ($users_list) {
                if (isset($users_list['filter']) && !empty($users_list['filter']['user_name'])) {
                    $query->where('user_name', $users_list['filter']['user_name']);
                }
            });
            $res = $res->whereHasIn('getSellerShopinfo', function ($query) {
                $query->where('review_status', 1);
            });
            $shopinfo_account = $res->count();

            $this->smarty->assign('shopinfo_account', $shopinfo_account);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            return $this->smarty->display('merchants_users_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- ajax判断商家名称是否重复 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'check_shop_name') {

            //已设置的shop_name,店铺名如何处理的
            $shop_name = request()->input('shop_name', '');
            $adminru = request()->input('user_id', 0);

            $res = MerchantsShopInformation::where('rz_shop_name', $shop_name);
            $shop_info = BaseRepository::getToArrayFirst($res);

            if (!empty($shop_info) && $shop_info['user_id'] != $adminru) {
                $data['error'] = 1;
            } else {
                $data['error'] = 2;
            }

            return response()->json($data);
        }

        /*------------------------------------------------------ */
        //-- ajax返回申请流程列表
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $users_list = $this->merchantsUsersListManageService->stepsUsersList($adminru);
            $this->smarty->assign('users_list', $users_list['users_list']);
            $this->smarty->assign('filter', $users_list['filter']);
            $this->smarty->assign('record_count', $users_list['record_count']);
            $this->smarty->assign('page_count', $users_list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($users_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('merchants_users_list.dwt'), '', ['filter' => $users_list['filter'], 'page_count' => $users_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 店铺详细信息
        /*------------------------------------------------------ */
        if ($act == 'add_shop' || $act == 'edit_shop' || $act == 'copy_shop') {
            /* 检查权限 */
            admin_priv('users_merchants');

            $user_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $admin_id = $adminru['user_id'];
            /*删除未绑定品牌 by kong*/
            //如果平台后台有多个用户添加了品牌或者可经营类目,会出现不属于当前用户的数据
            //解决方案:在新增品牌或者可经营类目的时候如果商户ID是0,就把商户ID默认为当前登录的管理员ID
            MerchantsShopBrand::where(function ($query) use ($admin_id) {
                $query->where('user_id', 0)
                    ->where('admin_id', $admin_id);
            })->orWhere(function ($query) use ($admin_id) {
                $query->where('user_id', '')
                    ->where('admin_id', $admin_id);
            })->delete();
            /*删除未绑定可经营类目*/
            MerchantsCategoryTemporarydate::where(function ($query) use ($admin_id) {
                $query->where('user_id', 0)
                    ->where('admin_id', $admin_id);
            })->orWhere(function ($query) use ($admin_id) {
                $query->where('user_id', '')
                    ->where('admin_id', $admin_id);
            })->delete();

            if (CROSS_BORDER === true) { // 跨境多商户
                $admin = app(CrossBorderService::class)->adminExists();

                if (!empty($admin)) {
                    $admin->smartyAssignSource($user_id);
                }
            }

            $shopInfo_list = $this->merchantsUsersListManageService->getStepsUserShopInfoList($user_id, 0, $act);

            if (PERSONAL_MERCHANTS === true) { // 个人企业
                $permer = PersonalMerchantsService::permerExists();
                if (!empty($permer)) {
                    $is_permer = 1;
                    $is_personal = $permer->getPersonal($user_id);

                    if ($is_personal > 0) {
                        $personal = $permer->getStepsUserShopInfoPersonal($user_id, 0, $act);
                        $pass_arr = [1, 2, 8];
                        foreach ($shopInfo_list as $k => $v) {
                            if (in_array($v['sp_id'], $pass_arr)) {
                                unset($shopInfo_list[$k]);
                            }
                        }
                        array_unshift($shopInfo_list, $personal);
                    }
                }
            }

            $this->smarty->assign('shopInfo_list', $shopInfo_list);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_merchants_users_list'], 'href' => 'merchants_users_list.php?act=list' . '&' . list_link_postfix()]);

            /*获取商家等级  by kong  start*/
            $res = SellerGrade::whereRaw(1);
            $seller_grade_list = BaseRepository::getToArrayGet($res);
            $this->smarty->assign("seller_grade_list", $seller_grade_list);

            /*获取当前商家等级 by kong*/
            $res = MerchantsGrade::where('ru_id', $user_id);
            $res = $res->with(['getSellerGrade' => function ($query) {
                $query->select('id', 'grade_name');
            }]);
            $grade = BaseRepository::getToArrayFirst($res);
            $grade['grade_name'] = '';
            if (isset($grade['get_seller_grade']) && !empty(isset($grade['get_seller_grade']))) {
                $grade['grade_name'] = $grade['get_seller_grade']['grade_name'];
            }

            $this->smarty->assign("grade", $grade);

            $category_info = get_fine_category_info(0, $user_id); // 详细类目
            $this->smarty->assign('category_info', $category_info);

            $permanent_list = get_category_permanent_list($user_id);// 一级类目证件
            $this->smarty->assign('permanent_list', $permanent_list);

            $consignee = [
                'province' => '',
                'city' => '',
            ];

            $country_list = get_regions_steps();
            $province_list = get_regions_steps(1, 1);
            $city_list = get_regions_steps(2, $consignee['province']);
            $district_list = get_regions_steps(3, $consignee['city']);

            $res = MerchantsShopInformation::where('user_id', $user_id);
            $merchants = BaseRepository::getToArrayFirst($res);

            $this->smarty->assign('merchants', $merchants);

            $sn = 0;
            $this->smarty->assign('country_list', $country_list);
            $this->smarty->assign('province_list', $province_list);
            $this->smarty->assign('city_list', $city_list);
            $this->smarty->assign('district_list', $district_list);
            $this->smarty->assign('consignee', $consignee);
            $this->smarty->assign('sn', $sn);

            if ($act == 'copy_shop') {
                $user_id = 0;
                $this->smarty->assign('copy_action', $act);
            }

            $this->smarty->assign('user_id', $user_id);

            if ($act == 'edit_shop') {
                $seller_shopinfo = $this->merchantCommonService->getShopName($user_id, 2);
                $this->smarty->assign('seller_shopinfo', $seller_shopinfo);
                $this->smarty->assign('form_action', 'update_shop');

                $cross_source = ConfigService::cross_source();
                $this->smarty->assign('cross_source', $cross_source);
            } else {
                $res = Users::whereDoesntHave('getMerchantsShopInformation')
                    ->limit(20);
                $user_list = BaseRepository::getToArrayGet($res);

                $this->smarty->assign('user_list', $user_list);
                $this->smarty->assign('form_action', 'insert_shop');
            }

            $this->smarty->assign('brand_ajax', 1);

            return $this->smarty->display('merchants_users_shopInfo.dwt');
        }

        /*------------------------------------------------------ */
        //-- 修改是否显示店铺街
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_street') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $shop_id = request()->input('id', 0);
            $is_street = request()->input('val', 0);

            if ($shop_id > 0) {
                $data = ['is_street' => $is_street];
                $res = MerchantsShopInformation::where('shop_id', $shop_id)->update($data);
                if ($res > 0) {
                    clear_cache_files();
                    return make_json_result($is_street);
                }
            }

            return make_json_error('invalid params');
        }

        /*------------------------------------------------------ */
        //-- 修改是否显示"在线客服" bylu
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_is_IM') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $shop_id = request()->input('id', 0);
            $is_IM = request()->input('val', 0);

            if ($shop_id > 0) {
                $data = ['is_im' => $is_IM];
                $res = MerchantsShopInformation::where('shop_id', $shop_id)->update($data);
                if ($res > 0) {
                    clear_cache_files();
                    return make_json_result($is_IM);
                }
            }

            return make_json_error('invalid params');
        }

        /*------------------------------------------------------ */
        //-- 更新申请商家信息
        /*------------------------------------------------------ */
        elseif ($act == 'insert_shop' || $act == 'update_shop') {
            /* 检查权限 */
            admin_priv('users_merchants');

            $copy_action = isset($_REQUEST['copy_action']) ? trim($_REQUEST['copy_action']) : 'update_shop';
            $brand_copy_id = isset($_REQUEST['brand_copy_id']) ? $_REQUEST['brand_copy_id'] : [];

            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            $merchants_audit = isset($_REQUEST['merchants_audit']) ? intval($_REQUEST['merchants_audit']) : -1;
            $merchants_allow = isset($_REQUEST['merchants_allow']) ? intval($_REQUEST['merchants_allow']) : -1;
            $merchants_message = isset($_REQUEST['merchants_message']) ? trim($_REQUEST['merchants_message']) : '';
            $review_goods = isset($_REQUEST['review_goods']) ? intval($_REQUEST['review_goods']) : 0;
            $shopname_audit = isset($_REQUEST['shopname_audit']) ? intval($_REQUEST['shopname_audit']) : 1; //审核使用店铺名称类型
            $old_merchants_audit = isset($_REQUEST['old_merchants_audit']) ? intval($_REQUEST['old_merchants_audit']) : 0; // by kong grade

            //获取默认等级
            $default_grade = SellerGrade::where('is_default', 1)->value('id');
            $default_grade = $default_grade ? $default_grade : 0;
            $grade_id = isset($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) : $default_grade;
            $year_num = isset($_REQUEST['year_num']) ? intval($_REQUEST['year_num']) : 1;
            $self_run = isset($_REQUEST['self_run']) ? intval($_REQUEST['self_run']) : 0; //自营店铺
            $shop_close = isset($_REQUEST['shop_close']) ? intval($_REQUEST['shop_close']) : 1;

            if ($user_id == 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'merchants_users_list.php?act=add_shop'];
                $centent = $GLOBALS['_LANG']['user_select_please'];
                return sys_msg($centent, 0, $link);
            }

            $form = $this->merchantsUsersListManageService->getAdminStepsTitleInsertForm($user_id);
            $parent = get_setps_form_insert_date($form['formName']);
            /* 判断审核状态是否改变 by kong grade */
            if ($old_merchants_audit != $merchants_audit) {
                //判断原来是否存在等级
                $grade = MerchantsGrade::where('ru_id', $user_id)->count();

                if ($merchants_audit == 1) {
                    if ($grade > 0) {
                        $data = [
                            'grade_id' => $grade_id,
                            'year_num' => $year_num
                        ];
                        MerchantsGrade::where('ru_id', $user_id)->update($data);
                    } else {
                        $add_time = gmtime();
                        $data = [
                            'ru_id' => $user_id,
                            'grade_id' => $grade_id,
                            'add_time' => $add_time,
                            'year_num' => $year_num
                        ];

                        MerchantsGrade::insert($data);
                    }
                    /* 跟新商家权限 */
                    $action_list = AdminUser::where('ru_id', $user_id)->value('action_list');
                    $action_list = $action_list ? $action_list : '';
                    if (empty($action_list)) {
                        $action_list = MerchantsPrivilege::where('grade_id', $grade_id)->value('action_list');
                        $action_list = $action_list ? $action_list : '';
                        $action = [
                            'action_list' => $action_list
                        ];
                        AdminUser::where('ru_id', $user_id)->update($action);
                    }
                } else {
                    if ($grade > 0) {
                        //审核未通过是删除该商家等级
                        MerchantsGrade::where('ru_id', $user_id)->delete();
                    }
                }
            }

            if (CROSS_BORDER === true) { // 跨境多商户
                $parent['source'] = isset($_REQUEST['huoyuan']) ? trim($_REQUEST['huoyuan']) : SOURCE_DOMESTIC;
            }

            $res = MerchantsShopInformation::where('user_id', $user_id);
            $shop_info = BaseRepository::getToArrayFirst($res);

            $shop_info['allow_number'] = $shop_info['allow_number'] ?? 0;

            $allow_number = $shop_info['allow_number'];

            if ($act == 'update_shop') { //更新数据

                if ($merchants_audit != 1) {
                    //审核未通过下架商家所有商品
                    $data = ['is_on_sale' => 0];
                    Goods::where('user_id', $user_id)->update($data);
                }

                //店铺关闭时，重新审核商家所有商品
                if ($shop_close != 1) {
                    //设置未审核
                    $data = ['review_status' => 1];
                    PresaleActivity::where('user_id', $user_id)->update($data);

                    //设置未审核
                    $data = ['review_status' => 1];
                    Goods::where('user_id', $user_id)->update($data);
                } else {
                    $shop_info['review_goods'] = $shop_info['review_goods'] ?? 0;
                    if ($GLOBALS['_CFG']['review_goods'] == 0 || $shop_info['review_goods'] == 0) {
                        //设置未审核
                        $data = ['review_status' => 3];
                        PresaleActivity::where('user_id', $user_id)->update($data);

                        //设置已审核通过
                        $data = ['review_status' => 3];
                        Goods::where('user_id', $user_id)->update($data);
                    }
                }

                $fid = MerchantsStepsFields::where('user_id', $user_id)->value('fid');
                $fid = $fid ? $fid : 0;

                $parent = BaseRepository::recursiveNullVal($parent);

                if ($fid > 0) {
                    MerchantsStepsFields::where('user_id', $user_id)->update($parent);
                } else {
                    $parent['user_id'] = $user_id;
                    MerchantsStepsFields::insert($parent);
                }
            } else { //插入数据
                $parent['user_id'] = $user_id;
                $parent['agreement'] = 1;

                $fid = MerchantsStepsFields::where('user_id', $user_id)->value('fid');
                $fid = $fid ? $fid : 0;

                if ($fid > 0) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'merchants_users_list.php?act=add_shop'];
                    $centent = $GLOBALS['_LANG']['insert_fail'];
                    return sys_msg($centent, 0, $link);
                } else {
                    $parent = BaseRepository::recursiveNullVal($parent);
                    MerchantsStepsFields::insert($parent);
                }
            }

            if ($merchants_audit >= 0) {
                $info['merchants_audit'] = $merchants_audit;
            }
            $info['review_goods'] = $review_goods;
            $info['self_run'] = $self_run;

            if ($merchants_allow == 1) {
                $info['steps_audit'] = 0;
                $info['allow_number'] = $allow_number + 1;
            } else {
                $ec_hopeLoginName = isset($_REQUEST['ec_hopeLoginName']) ? trim($_REQUEST['ec_hopeLoginName']) : '';
                $adminId = AdminUser::where('user_name', $ec_hopeLoginName)->where('ru_id', '<>', $user_id)->count();

                if ($adminId > 0) {
                    if ($act == 'update_shop') {
                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'merchants_users_list.php?act=edit_shop&id=' . $user_id];
                    } else {
                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'merchants_users_list.php?act=add_shop'];
                    }

                    return sys_msg($GLOBALS['_LANG']['adminId_have'], 0, $link);
                }

                // 编辑审核状态 改变值
                if ($merchants_audit >= 0) {
                    $info['steps_audit'] = 1;
                }
            }

            $info['merchants_message'] = $merchants_message;
            $info['shop_close'] = $shop_close;

            MerchantsShopInformation::where('user_id', $user_id)->update($info);

            if ($shop_info['merchants_audit'] != $info['merchants_audit']) {

                if ($info['merchants_audit'] == 1) {
                    $audit_log = 'merchants_audit_one';
                } elseif ($info['merchants_audit'] == 2) {
                    $audit_log = 'merchants_audit_two';
                } else {
                    $audit_log = 'merchants_audit_zero';
                }

                $audit_log = $merchants_allow == 1 ? 'merchants_audit_three' : $audit_log;

                admin_log($GLOBALS['_LANG']['record_id'] . "【" . $user_id . "】", 'edit_merchants_audit', $audit_log);
            }

            $seller_shopinfo = [
                'shopname_audit' => $shopname_audit,
                'shop_close' => $shop_close
            ];

            $shopinfo = $this->storeService->getShopInfo($user_id);

            if ($shopinfo) {
                SellerShopinfo::where('ru_id', $user_id)->update($seller_shopinfo);
            } else {
                if ($merchants_audit == 1) {
                    $field = MerchantsStepsFields::where('contactPhone', '<>', '')->count();
                    if ($field > 0) {
                        $steps_fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
                        $seller_shopinfo['mobile'] = $steps_fields['contactPhone'];
                    }

                    $field = MerchantsStepsFields::where('contactEmail', '<>', '')->count();
                    if ($field > 0) {
                        $steps_fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
                        $seller_shopinfo['seller_email'] = $steps_fields['contactEmail'];
                    }
                    $field = MerchantsStepsFields::where('company_adress', '<>', '')->count();
                    if ($field > 0) {
                        $steps_fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
                        $seller_shopinfo['shop_address'] = $steps_fields['company_adress'];
                    }

                    $field = MerchantsStepsFields::where('company_located', '<>', '')->count();
                    if ($field > 0) {
                        $steps_fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);

                        if ($steps_fields['company_located']) {
                            $region = explode(",", $steps_fields['company_located']);

                            $seller_shopinfo['country'] = $region[0];
                            $seller_shopinfo['province'] = $region[1];
                            $seller_shopinfo['city'] = $region[2];
                            $seller_shopinfo['district'] = $region[3];
                        }
                    }

                    $field = MerchantsStepsFields::where('company', '<>', '')->count();
                    if ($field > 0) {
                        $steps_fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
                        $seller_shopinfo['shop_name'] = $steps_fields['company'];
                    }

                    $field = MerchantsStepsFields::where('company_contactTel', '<>', '')->count();
                    if ($field > 0) {
                        $steps_fields = $this->merchantCommonService->getMerchantsStepsFields($user_id);
                        $seller_shopinfo['kf_tel'] = $steps_fields['company_contactTel'];
                    }

                    $seller_shopinfo['ru_id'] = $user_id;
                    $seller_shopinfo['templates_mode'] = 1;
                } else {
                    $seller_shopinfo['ru_id'] = $user_id;
                }


                SellerShopinfo::query()->where('ru_id', $user_id)->updateOrInsert($seller_shopinfo);
            }

            if ($merchants_audit == 1) {
                //如果审核通过，判断店铺是否存在模板，不存在 导入默认模板
                $tpl_dir = storage_public(DATA_DIR . '/seller_templates/seller_tem_' . $user_id); //获取店铺模板目录
                $tpl_arr = get_dir_file_list($tpl_dir);
                if (empty($tpl_arr)) {
                    load_helper('visual');
                    $new_suffix = get_new_dir_name($user_id);
                    $dir = storage_public(DATA_DIR . "/seller_templates/seller_tem/bucket_tpl"); //原目录
                    $file = $tpl_dir . "/" . $new_suffix; //目标目录
                    if (!empty($new_suffix)) {
                        //新建目录
                        if (!is_dir($file)) {
                            make_dir($file);
                        }
                        recurse_copy($dir, $file, 1);
                        $result['error'] = 0;
                    }
                    SellerShopinfo::where('ru_id', $user_id)->update(['seller_templates' => $new_suffix]);
                }

                $href = 'merchants_users_list.php?act=allot&user_id=' . $user_id;
            } else {
                $href = 'merchants_users_list.php?act=list' . '&' . list_link_postfix();
            }

            if ($review_goods == 0 && $shop_close == 1) {
                $goods_date['review_status'] = 3;
                Goods::where('user_id', $user_id)->update($goods_date);
            }

            //复制店铺时  品牌入库
            if ($copy_action == 'copy_shop') {
                $brand_copy_id = BaseRepository::getExplode($brand_copy_id);
                $data = ['user_id' => $user_id];
                MerchantsShopBrand::whereIn('bid', $brand_copy_id)->update($data);
            }

            if ($act == 'update_shop') {
                $centent = $GLOBALS['_LANG']['update_success'];
            } else {
                $centent = $GLOBALS['_LANG']['insert_success'];
            }

            $count = MerchantsServer::where('user_id', $user_id)->count();

            if ($count <= 0) {
                $percent_id = MerchantsPercent::where('percent_value', 100)->value('percent_id');
                $percent_id = $percent_id ? $percent_id : 0;
                if (!$percent_id) {
                    $percent_value = MerchantsPercent::selectRaw('max(percent_value) as percent')->value('percent');
                    $percent_id = MerchantsPercent::where('percent_value', $percent_value)->value('percent_id');
                }

                $other = [
                    'user_id' => $user_id,
                    'suppliers_percent' => $percent_id ?? 0,
                    'cycle' => 3
                ];
                MerchantsServer::insert($other);
            }
            $Shopinfo_cache_name = 'SellerShopinfo_' . $user_id;

            cache()->forget($Shopinfo_cache_name);

            $cache_id = 'get_merchant_info_data_list' . md5(serialize($user_id)) . 0;
            cache()->forget($cache_id);

            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($user_id);

            /* 提示信息 */
            $href = 'merchants_users_list.php?act=allot&id=' . $user_id . '&login_name=' . $merchantList[$user_id]['shop_name'] ?? '';
            $link[] = ['text' => $GLOBALS['_LANG']['setup_seller_allot'], 'href' => $href];
            return sys_msg($centent, 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 插入默认模板
        /*------------------------------------------------------ */
        elseif ($act == 'seller_shop_tem') {

            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $tpl_dir = storage_public(DATA_DIR . '/seller_templates/seller_tem_' . $user_id); //获取店铺模板目录
            $tpl_arr = get_dir_file_list($tpl_dir);
            if (empty($tpl_arr)) {
                load_helper('visual');
                $new_suffix = get_new_dir_name($user_id);
                Import_temp('bucket_tpl', $new_suffix, $user_id);

                $data = ['seller_templates' => $new_suffix];
                SellerShopinfo::where('ru_id', $user_id)->update($data);
            }
            // 插入移动端默认模板
            $this->merchantCommonService->importMobileTemplate($user_id);

            $href = 'merchants_users_list.php?act=list&user_id=' . $user_id;

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => $href];
            return sys_msg($GLOBALS['_LANG']['update_success'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 商家分派权限
        /*------------------------------------------------------ */
        elseif ($act == 'allot') {
            /* 检查权限 */
            admin_priv('users_merchants');

            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            $login_name = request()->get('login_name');

            $user_id = $user_id > 0 ? $user_id : $id;

            /* 恢复商家默认权限 by wu start */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['allot_priv']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['restore_default_priv'], 'href' => 'merchants_users_list.php?act=restore_default_priv&user_id=' . $user_id]);
            /* 恢复商家默认权限 by wu end */

            $res = MerchantsShopInformation::where('user_id', $user_id);
            $merchants = BaseRepository::getToArrayFirst($res);

            if (empty($merchants['hope_login_name'])) {
                $user_name = Users::where('user_id', $user_id)->value('user_name');
            } else {
                $user_name = $merchants['hope_login_name'];
            }

            //添加管理员 --start
            $pwd = $GLOBALS['_CFG']['merchants_prefix'] . $user_id;

            // 生成hash
            $GLOBALS['user'] = init_users();
            $password = $GLOBALS['user']->hash_password($pwd);

            /* 获取商家等级 by kong grade */
            $res = MerchantsGrade::where('ru_id', $user_id);
            $merchants_grade = BaseRepository::getToArrayFirst($res);

            $grade_id = $merchants_grade['grade_id'] > 0 ? $merchants_grade['grade_id'] : 0;

            //入驻默认初始权限
            $action_list = MerchantsPrivilege::where('grade_id', $grade_id)->value('action_list');
            $action_list = $action_list ? $action_list : '';

            $res = AdminUser::where('action_list', 'all');
            $row = BaseRepository::getToArrayFirst($res);

            $res = AdminUser::where('ru_id', $user_id)->where('user_name', $login_name)->where('parent_id', 0)->where('suppliers_id', 0);
            $rows = BaseRepository::getToArrayFirst($res);

            if (isset($rows['action_list'])) {
                $action_list = $rows['action_list'];
            }

            $adminId = AdminUser::where('ru_id', $user_id)->where('suppliers_id', 0)->value('user_id');
            $adminId = $adminId ? $adminId : 0;

            if ($adminId > 0) {
                AdminUser::where('ru_id', $user_id)->where('parent_id', 0)->where('suppliers_id', 0)
                    ->update([
                        'user_name' => $user_name,
                        'nav_list' => $row['nav_list'],
                        'action_list' => $action_list
                    ]);
            } else {
                $other = [
                    'user_name' => $user_name,
                    'password' => $password,
                    'nav_list' => $row['nav_list'],
                    'action_list' => $action_list,
                    'ru_id' => $user_id
                ];
                AdminUser::insert($other);
            }
            //添加管理员 --end
            $res = AdminUser::where('user_name', $user_name)->where('suppliers_id', 0);
            $user_priv = BaseRepository::getToArrayFirst($res);

            $admin_id = $user_priv['user_id'] ?? 0;
            $priv_str = $user_priv['action_list'] ?? '';

            /* 取得当前管理员用户名 */
            $current_admin_name = AdminUser::where('user_id', session('admin_id'))->value('user_name');
            $current_admin_name = $current_admin_name ? $current_admin_name : '';

            //商家名称
            if (empty($adminId)) {
                $shop_name = $this->merchantCommonService->getShopName($user_id, 1);

                $contactPhone = MerchantsStepsFields::where('user_id', $user_id)->value('contactPhone');
                if (!empty($contactPhone)) {
                    $shopinfo['mobile'] = $contactPhone;

                    /* 如果需要，发短信 */
                    if ($adminru['ru_id'] == 0 && $GLOBALS['_CFG']['sms_seller_signin'] == '1' && $shopinfo['mobile'] != '') {

                        //短信接口参数
                        $smsParams = [
                            'seller_name' => $shop_name,
                            'sellername' => $shop_name,
                            'login_name' => $user_name ? htmlspecialchars($user_name) : '',
                            'loginname' => $user_name ? htmlspecialchars($user_name) : '',
                            'password' => $pwd ? htmlspecialchars($pwd) : '',
                            'admin_name' => $current_admin_name ? $current_admin_name : '',
                            'adminname' => $current_admin_name ? $current_admin_name : '',
                            'edit_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', gmtime()),
                            'edittime' => TimeRepository::getLocalDate('Y-m-d H:i:s', gmtime()),
                            'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '',
                            'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : ''
                        ];

                        $this->commonRepository->smsSend($shopinfo['mobile'], $smsParams, 'sms_seller_signin', false);
                    }
                }

                $seller_step_email = config('shop.seller_step_email') ?? 0;
                $contactEmail = MerchantsStepsFields::where('user_id', $user_id)->value('contactEmail');
                if ($seller_step_email == 1 && !empty($contactEmail)) {

                    $shopinfo['seller_email'] = $contactEmail;

                    /* 发送邮件 */
                    $template = get_mail_template('seller_signin');
                    if ($template['template_content'] != '') {
                        if ($shopinfo['seller_email']) {
                            $this->smarty->assign('shop_name', $shop_name);
                            $this->smarty->assign('seller_name', $user_name);
                            $this->smarty->assign('seller_psw', $pwd);
                            $this->smarty->assign('site_name', $GLOBALS['_CFG']['shop_name']);
                            $this->smarty->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()));
                            $content = $this->smarty->fetch('str:' . $template['template_content']);

                            CommonRepository::sendEmail($user_name, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
                        }
                    }
                }
            }


            /* 获取权限的分组数据 */
            $res = AdminAction::where('parent_id', 0)->where('seller_show', 1);
            $res = BaseRepository::getToArrayGet($res);
            $priv_arr = [];
            foreach ($res as $rows) {
                $priv_arr[$rows['action_id']] = $rows;
            }

            if ($priv_arr) {
                /* 按权限组查询底级的权限名称 */
                $res = AdminAction::whereIn('parent_id', array_keys($priv_arr))->where('seller_show', 1);
                $result = BaseRepository::getToArrayGet($res);

                foreach ($result as $priv) {
                    if ($priv["action_code"] == 'post_setting_manage' && empty(config('shop.open_community_post'))) {
                        continue;
                    }
                    $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
                }

                // 将同一组的权限使用 "," 连接起来，供JS全选
                foreach ($priv_arr as $action_id => $action_group) {
                    if (isset($action_group['priv']) && $action_group['priv']) {
                        $priv_arr[$action_id]['priv_list'] = join(',', @array_keys($action_group['priv']));

                        foreach ($action_group['priv'] as $key => $val) {
                            $priv_arr[$action_id]['priv'][$key]['cando'] = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
                        }
                    }
                }
            }

            $this->smarty->assign('priv_arr', $priv_arr);
            $this->smarty->assign('form_action', 'update_allot');
            $this->smarty->assign('admin_id', $admin_id);
            $this->smarty->assign('user_id', $user_id);

            if (!empty($user_priv['user_name'])) {
                $user_name = $user_priv['user_name'];
            }
            $this->smarty->assign('user_name', $user_name);

            //链接基本信息
            $this->smarty->assign('users', get_table_date('merchants_shop_information', "user_id='$user_id'", ['user_id', 'hope_login_name', 'merchants_audit']));
            $this->smarty->assign('menu_select', ['action' => 'seller_shopinfo', 'current' => 'allot']);

            return $this->smarty->display('merchants_user_allot.dwt');
        }

        /*------------------------------------------------------ */
        //-- 恢复商家默认权限 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'restore_default_priv') {
            /* 检查权限 */
            admin_priv('users_merchants');

            $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            if ($user_id > 0) {
                //获取管理员id
                $adminId = AdminUser::where('ru_id', $user_id)
                    ->where('parent_id', 0)
                    ->value('user_id');
                $adminId = $adminId ? $adminId : 0;

                //获取商家等级
                $grade_id = MerchantsGrade::where('ru_id', $user_id)->value('grade_id');
                $grade_id = $grade_id ? $grade_id : 0;

                //入驻默认初始权限
                $action_list = MerchantsPrivilege::where('grade_id', $grade_id)->value('action_list');
                $action_list = $action_list ? $action_list : '';

                //更新权限
                $data = ['action_list' => $action_list];
                AdminUser::where('user_id', $adminId)->update($data);

                $update_success = $GLOBALS['_LANG']['update_success'];
            } else {
                $update_success = $GLOBALS['_LANG']['update_fail'];
            }
            $href = "merchants_users_list.php?act=list" . '&' . list_link_postfix();
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => $href];
            return sys_msg($update_success, 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 修改商家密码和权限
        /*------------------------------------------------------ */
        elseif ($act == 'update_allot') {
            /* 检查权限 */
            admin_priv('users_merchants');

            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            $login_name = isset($_REQUEST['login_name']) ? trim($_REQUEST['login_name']) : '';
            $ec_salt = rand(1, 9999);

            $seller_psw = '';

            $login_password = !empty($_REQUEST['login_password']) ? trim($_REQUEST['login_password']) : ''; //默认密码

            if (!empty($login_password)) {
                $seller_psw = $login_password;

                // 数据验证
                $validator = Validator::make(request()->all(), [
                    'login_password' => ['filled', 'different:login_name', new PasswordRule()], // 密码
                ], [
                    'login_password.filled' => lang('user.user_pass_empty'),
                    'login_password.different' => lang('user.user_pass_same')
                ]);

                // 返回错误
                if ($validator->fails()) {
                    $error = $validator->errors()->first();
                    return sys_msg($error, 1);
                }

                // 生成hash密码
                $GLOBALS['user'] = init_users();
                $au_data['password'] = $GLOBALS['user']->hash_password($login_password);

                $au_data['ec_salt'] = $ec_salt;
                $au_data['login_status'] = '';
            }

            if (!empty($login_name)) {
                $res = AdminUser::where('user_name', $login_name)
                    ->where('ru_id', '<>', $user_id)
                    ->count();
                if ($res < 1) {
                    $data = ['hope_login_name' => $login_name];
                    MerchantsShopInformation::where('user_id', $user_id)->update($data);

                    $seller_name = $login_name;
                    $au_data['user_name'] = $login_name;
                } else {
                    return sys_msg($GLOBALS['_LANG']['login_name_existent'], 1);
                }
            } else {
                return sys_msg($GLOBALS['_LANG']['login_name_not_null'], 1);
            }

            /* 更新管理员的权限 */
            $act_list = implode(',', $_POST['action_code']);

            $au_data['action_list'] = $act_list;
            AdminUser::where('ru_id', $user_id)
                ->where('parent_id', 0)
                ->where('suppliers_id', 0)
                ->update($au_data);

            /* 取得当前管理员用户名 */
            $adminUser = AdminUser::select('user_name', 'password')->where('user_id', session('admin_id'));
            $adminUser = BaseRepository::getToArrayFirst($adminUser);
            $current_admin_name = $adminUser['user_name'];

            //商家名称
            $shop_name = $this->merchantCommonService->getShopName($user_id, 1);

            $res = SellerShopinfo::select('mobile', 'seller_email')->where('ru_id', $user_id);
            $shopinfo = BaseRepository::getToArrayFirst($res);

            if (empty($shopinfo['mobile'])) {
                $shopinfo['mobile'] = MerchantsStepsFields::where('user_id', $user_id)->value('contactPhone');;
            }

            if ($seller_name && $seller_psw) {
                /* 如果需要，发短信 */
                if ($GLOBALS['_CFG']['sms_seller_signin'] == '1' && $shopinfo['mobile'] != '') {
                    //阿里大鱼短信接口参数
                    $smsParams = [
                        'seller_name' => $shop_name,
                        'sellername' => $shop_name,
                        'login_name' => $seller_name ? htmlspecialchars($seller_name) : '',
                        'loginname' => $seller_name ? htmlspecialchars($seller_name) : '',
                        'password' => $seller_psw ? htmlspecialchars($seller_psw) : '',
                        'admin_name' => $current_admin_name ? $current_admin_name : '',
                        'adminname' => $current_admin_name ? $current_admin_name : '',
                        'edit_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', gmtime()),
                        'edittime' => TimeRepository::getLocalDate('Y-m-d H:i:s', gmtime()),
                        'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '',
                        'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : ''
                    ];

                    $this->commonRepository->smsSend($shopinfo['mobile'], $smsParams, 'sms_seller_signin', false);
                }

                /* 记录管理员操作 */
                admin_log(addslashes($current_admin_name), 'edit', 'merchants_users_list');

                /* 发送邮件 */
                $template = get_mail_template('seller_signin');

                if ($template['template_content'] != '' && !empty(config('shop.smtp_pass'))) {
                    if (empty($shopinfo['seller_email'])) {
                        $shopinfo['seller_email'] = MerchantsStepsFields::where('user_id', $user_id)->value('contactEmail');
                    }

                    $seller_step_email = config('shop.seller_step_email') ?? 0;
                    if ($seller_step_email == 1 && $shopinfo['seller_email']) {
                        $this->smarty->assign('shop_name', $shop_name);
                        $this->smarty->assign('seller_name', $seller_name);
                        $this->smarty->assign('seller_psw', $seller_psw);
                        $this->smarty->assign('site_name', $GLOBALS['_CFG']['shop_name']);
                        $this->smarty->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()));
                        $content = $this->smarty->fetch('str:' . $template['template_content']);

                        CommonRepository::sendEmail($seller_name, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
                    }
                }
            }

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => "merchants_users_list.php?act=list" . '&' . list_link_postfix()];
            return sys_msg($GLOBALS['_LANG']['update_success'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除申请商家
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            /* 检查权限 */
            admin_priv('users_merchants_drop');

            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            /*
            * 如果存在供应商
            */
            if (file_exists(SUPPLIERS)) {
                $count = \App\Modules\Suppliers\Models\Suppliers::where('user_id', $id)->count();
                if ($count > 0) {
                    /* 提示信息 */
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'merchants_users_list.php?act=list' . '&' . list_link_postfix()];
                    return sys_msg(lang('admin/common.is_suppliers'), 0, $link);
                }
            }

            MerchantsShopInformation::where('user_id', $id)->delete();

            MerchantsStepsFields::where('user_id', $id)->delete();

            //删除店铺背景
            SellerShopbg::where('ru_id', $id)->delete();

            //删除店铺橱窗
            SellerShopwindow::where('ru_id', $id)->delete();

            //删除店铺头部
            SellerShopheader::where('ru_id', $id)->delete();

            //删除店铺轮播图
            SellerShopslide::where('ru_id', $id)->delete();

            //删除店铺基本信息
            SellerShopinfo::where('ru_id', $id)->delete();

            //删除店铺二级域名
            SellerDomain::where('ru_id', $id)->delete();

            //删除商家管理员身份
            AdminUser::where('ru_id', $id)->delete();

            //删除店铺申请等级
            SellerApplyInfo::where('ru_id', $id)->delete();

            if ($GLOBALS['_CFG']['delete_seller'] && $id) {
                get_seller_delete_goods_list($id); //删除商家商品

                get_delete_seller_info('merchants_category', "user_id = '$id'"); //删除商家店铺分类
            }

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'merchants_users_list.php?act=list' . '&' . list_link_postfix()];
            return sys_msg($GLOBALS['_LANG']['carddrop_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 查找二级类目
        /*------------------------------------------------------ */
        elseif ($act == 'addChildCate') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $filter = dsc_decode($_GET['JSON']);

            $cate_list = get_first_cate_list($filter->cat_id, 0, [], $filter->user_id);
            $this->smarty->assign('cate_list', $cate_list);
            $this->smarty->assign('cat_id', $filter->cat_id);

            return make_json_result($this->smarty->fetch('merchants_cate_list.dwt'));
        }

        /*------------------------------------------------------ */
        //-- 添加二级类目
        /*------------------------------------------------------ */
        elseif ($act == 'addChildCate_checked') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $cat_id = request()->input('cat_id', 0);
            $cat_id = strip_tags(urldecode($cat_id));
            $cat_id = json_str_iconv($cat_id);

            $cat = dsc_decode($cat_id);

            $child_category = get_child_category($cat->cat_id);
            $category_info = get_fine_category_info($child_category['cat_id'], $cat->user_id);
            $this->smarty->assign('category_info', $category_info);

            return make_json_result($this->smarty->fetch("merchants_cate_checked_list.dwt"));

            $permanent_list = get_category_permanent_list($cat->user_id);
            $this->smarty->assign('permanent_list', $permanent_list);
            return make_json_result($this->smarty->fetch("merchants_steps_catePermanent.dwt"));
        }

        /*------------------------------------------------------ */
        //-- 删除二级类目
        /*------------------------------------------------------ */
        elseif ($act == 'deleteChildCate_checked') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $ct_id = isset($_REQUEST['ct_id']) ? intval($_REQUEST['ct_id']) : '';
            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $catParent = get_temporarydate_ctId_catParent($ct_id);
            if ($catParent['num'] == 1) {
                MerchantsDtFile::where('cat_id', $catParent['parent_id'])->delete();
            }

            MerchantsCategoryTemporarydate::where('ct_id', $ct_id)->delete();

            $category_info = get_fine_category_info(0, $user_id);
            $this->smarty->assign('category_info', $category_info);
            return make_json_result($this->smarty->fetch("merchants_cate_checked_list.dwt"));

            $permanent_list = get_category_permanent_list($user_id);
            $this->smarty->assign('permanent_list', $permanent_list);
            return make_json_result($this->smarty->fetch("merchants_steps_catePermanent.dwt"));
        }

        /*------------------------------------------------------ */
        //-- 删除品牌
        /*------------------------------------------------------ */
        elseif ($act == 'deleteBrand') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }


            $filter = dsc_decode($_GET['JSON']);
            $brand_list = [];
            if (!empty($filter)) {
                MerchantsShopBrand::where('bid', $filter->ct_id)->delete();

                $brand_list = get_septs_shop_brand_list($filter->user_id); //品牌列表
            }
            $this->smarty->assign('brand_list', $brand_list);
            $html = '';
            foreach ($brand_list as $value) {
                $html .= '<li>';
                $html .= '<a href="javascript:;" data-value="' . $value['brandName'] . '" class="ftx-01">' . $value['brandName'] . '</a>';
                $html .= '</li>';
            }
            $append['brank_html'] = $html;
            return make_json_result($this->smarty->fetch('merchants_steps_brank_list.dwt'), '', $append);
        }

        /*------------------------------------------------------ */
        //-- 编辑品牌
        /*------------------------------------------------------ */
        elseif ($act == 'brand_edit') {
            $b_fid = isset($_REQUEST['del_bFid']) ? intval($_REQUEST['del_bFid']) : 0;
            if ($b_fid > 0) {
                MerchantsShopBrandfile::where('b_fid', $b_fid)->delete();
            }

            $ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? intval($_REQUEST['ec_shop_bid']) : 0;
            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            $brandView = isset($_REQUEST['brandView']) ? $_REQUEST['brandView'] : '';

            $shopInfo_list = $this->merchantsUsersListManageService->getStepsUserShopInfoList($user_id, $ec_shop_bid);
            $this->smarty->assign('shopInfo_list', $shopInfo_list);

            $category_info = get_fine_category_info(0, $user_id); // 详细类目
            $this->smarty->assign('category_info', $category_info);

            $permanent_list = get_category_permanent_list($user_id);// 一级类目证件
            $this->smarty->assign('permanent_list', $permanent_list);

            $country_list = get_regions_steps();
            $province_list = get_regions_steps(1);
            $city_list = get_regions_steps(2);
            $district_list = get_regions_steps(3);

            $sn = 0;
            $this->smarty->assign('country_list', $country_list);
            $this->smarty->assign('province_list', $province_list);
            $this->smarty->assign('city_list', $city_list);
            $this->smarty->assign('district_list', $district_list);
            $this->smarty->assign('sn', $sn);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('brandView', $brandView);
            $this->smarty->assign('ec_shop_bid', $ec_shop_bid);
            $this->smarty->assign('form_action', 'update_shop');


            return $this->smarty->display('merchants_users_shopInfo.dwt');
        } elseif ($act == 'addBrand') {
            load_helper('order');

            $title = '';
            $result = ['content' => ''];
            $b_fid = isset($_REQUEST['del_bFid']) ? intval($_REQUEST['del_bFid']) : 0;
            if ($b_fid > 0) {
                MerchantsShopBrandfile::where('b_fid', $b_fid)->delete();
            }

            $ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? intval($_REQUEST['ec_shop_bid']) : 0;
            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            $brandView = isset($_REQUEST['brandView']) ? $_REQUEST['brandView'] : '';
            $shopInfo_list = $this->merchantsUsersListManageService->getStepsUserShopInfoList($user_id, $ec_shop_bid);

            foreach ($shopInfo_list as $k => $v) {
                foreach ($v['steps_title'] as $key => $val) {
                    if ($val['steps_style'] == 3 && $val['fields_titles'] == $GLOBALS['_LANG']['new_brand_info']) {
                        $title = $val;
                    }
                }
            }

            $this->smarty->assign("title", $title);
            $this->smarty->assign('shopInfo_list', $shopInfo_list);
            $category_info = get_fine_category_info(0, $user_id); // 详细类目
            $this->smarty->assign('category_info', $category_info);

            $permanent_list = get_category_permanent_list($user_id);// 一级类目证件
            $this->smarty->assign('permanent_list', $permanent_list);

            $consignee = $this->flowUserService->getConsignee($user_id);

            /* 初始化地区ID */
            $consignee['country'] = !isset($consignee['country']) && empty($consignee['country']) ? 0 : intval($consignee['country']);
            $consignee['province'] = !isset($consignee['province']) && empty($consignee['province']) ? 0 : intval($consignee['province']);
            $consignee['city'] = !isset($consignee['city']) && empty($consignee['city']) ? 0 : intval($consignee['city']);
            $consignee['district'] = !isset($consignee['district']) && empty($consignee['district']) ? 0 : intval($consignee['district']);
            $consignee['street'] = !isset($consignee['street']) && empty($consignee['street']) ? 0 : intval($consignee['street']);

            $country_list = get_regions_steps();
            $province_list = get_regions_steps(1, $consignee['country']);
            $city_list = get_regions_steps(2, $consignee['province']);
            $district_list = get_regions_steps(3, $consignee['city']);

            $sn = 0;
            $this->smarty->assign('country_list', $country_list);
            $this->smarty->assign('province_list', $province_list);
            $this->smarty->assign('city_list', $city_list);
            $this->smarty->assign('district_list', $district_list);
            $this->smarty->assign('consignee', $consignee);
            $this->smarty->assign('sn', $sn);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('brandView', $brandView);
            $this->smarty->assign('ec_shop_bid', $ec_shop_bid);
            $this->smarty->assign('form_action', 'update_shop');
            $result['content'] = $GLOBALS['smarty']->fetch('merchants_bank_dialog.dwt');
            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 查询会员名称
        /*------------------------------------------------------ */
        elseif ($act == 'get_user_name') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $user_name = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);

            /* 获取会员列表信息 */
            $res = Users::where('user_name', 'LIKE', '%' . $user_name . '%')
                ->whereDoesntHaveIn('getMerchantsShopInformation', function ($query) {
                });
            $user_list = BaseRepository::getToArrayGet($res);

            $res = $this->merchantsUsersListManageService->getSearchUserList($user_list);

            clear_cache_files();
            return make_json_result($res);
        } //添加品牌  by kong
        elseif ($act == 'addImg') {
            $result = ['content' => '', 'error' => 0, 'massege' => ''];
            $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;

            $steps_title = $this->merchantsUsersListManageService->getAdminMerchantsStepsTitle($user_id, 'addImg');
            if (!empty($steps_title)) {
                $result['error'] = '2';
                $res = MerchantsShopBrand::where('user_id', $user_id);
                if (empty($user_id)) {
                    $admin_id = get_admin_id();
                    $res = $res->where('admin_id', $admin_id);
                }
                $title['brand_list'] = BaseRepository::getToArrayGet($res);

                $brand_id = '';
                if (!empty($title['brand_list'])) {
                    foreach ($title['brand_list'] as $k => $v) {
                        $brand_id .= $v['bid'] . ",";
                    }
                }
                $title['brand_list'] = get_septs_shop_brand_list($user_id);

                $brand_id = substr($brand_id, 0, strlen($brand_id) - 1);
                $this->smarty->assign("brand_id", $brand_id);
                $this->smarty->assign("title", $title);

                $brand_list = get_septs_shop_brand_list($user_id); //品牌列表

                $html = '';
                foreach ($brand_list as $value) {
                    $html .= '<li>';
                    $html .= '<a href="javascript:;" data-value="' . $value['brandName'] . '" class="ftx-01">' . $value['brandName'] . '</a>';
                    $html .= '</li>';
                }
                $result['brank_html'] = $html;
                $result['content'] = $GLOBALS['smarty']->fetch('merchants_steps_brankType.dwt');
            } else {
                $result['error'] = '1';
                $result['massege'] = $GLOBALS['_LANG']['add_fail'];
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 修改商品排序
        /*------------------------------------------------------ */
        elseif ($act == 'edit_sort_order') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $shop_id = intval($_POST['id']);
            $sort_order = intval($_POST['val']);

            $data = ['sort_order' => $sort_order];
            $res = MerchantsShopInformation::where('shop_id', $shop_id)->update($data);
            if ($res > 0) {
                clear_cache_files();
                return make_json_result($sort_order);
            }
        }

        /*------------------------------------------------------ */
        //-- 初始化商家等级 start
        /*------------------------------------------------------ */
        elseif ($act == 'create_initialize_rank') {
            admin_priv('users_merchants');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['create_seller_grade']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_merchants_users_list'], 'href' => 'merchants_users_list.php?act=list' . '&' . list_link_postfix()]);

            $seller_grade_list = seller_grade_list();
            $record_count = count($seller_grade_list);

            $this->smarty->assign('record_count', $record_count);
            $this->smarty->assign('page', 1);


            return $this->smarty->display('merchants_initialize_rank.dwt');
        }

        /*------------------------------------------------------ */
        //-- 初始化商家等级 end
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_initialize_rank') {
            $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
            $page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;

            $seller_grade_list = seller_grade_list();
            $grade_list = $this->dsc->page_array($page_size, $page, $seller_grade_list);

            $arr = [];

            $add_time = gmtime();
            if ($grade_list && $grade_list['list']) {

                $ru_id = BaseRepository::getKeyPluck($grade_list['list'], 'user_id');
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                foreach ($grade_list['list'] as $key => $row) {
                    $res = MerchantsGrade::where('ru_id', $row['user_id']);
                    $grade_row = BaseRepository::getToArrayFirst($res);

                    if ($grade_row) {
                        $res = SellerGrade::where('id', $grade_row['grade_id']);
                        $seller_grade = BaseRepository::getToArrayFirst($res);
                    } else {
                        $seller_temp = SellerGrade::min('seller_temp');
                        $seller_temp = $seller_temp ? $seller_temp : 0;
                        $res = SellerGrade::where('seller_temp', $seller_temp);
                        $seller_grade = BaseRepository::getToArrayFirst($res);

                        $data = [
                            'ru_id' => $row['user_id'],
                            'grade_id' => $seller_grade['id'],
                            'add_time' => $add_time,
                            'year_num' => 1
                        ];
                        MerchantsGrade::insert($data);
                    }

                    $seller_list[$key]['shop_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';
                    $arr = [
                        'user_id' => $row['user_id'], //商家ID
                        'shop_name' => $seller_list[$key]['shop_name'], //店铺名称
                        'grade_name' => $seller_grade['grade_name'], //等级名称
                    ];
                }
            }

            $result['list'] = $arr;

            $result['page'] = $grade_list['filter']['page'] + 1;
            $result['page_size'] = $grade_list['filter']['page_size'];
            $result['record_count'] = $grade_list['filter']['record_count'];
            $result['page_count'] = $grade_list['filter']['page_count'];

            $result['is_stop'] = 1;
            if ($page > $grade_list['filter']['page_count']) {
                $result['is_stop'] = 0;
            } else {
                $result['filter_page'] = $grade_list['filter']['page'];
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 商家评分 start
        /*------------------------------------------------------ */
        elseif ($act == 'create_seller_grade') {
            admin_priv('users_merchants');

            $this->smarty->assign('menu_select', ['action' => '17_merchants', 'current' => '04_create_seller_grade']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['create_seller_grade']);

            $seller_grade_list = seller_grade_list();
            $record_count = count($seller_grade_list);

            $this->smarty->assign('record_count', $record_count);
            $this->smarty->assign('page', 1);

            return $this->smarty->display('merchants_grade.dwt');
        }

        /*------------------------------------------------------ */
        //-- 商家评分 end
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_seller_grade') {
            $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
            $page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;

            $seller_grade_list = seller_grade_list();
            $grade_list = $this->dsc->page_array($page_size, $page, $seller_grade_list);

            $arr = [];
            if ($grade_list && $grade_list['list']) {

                $ru_id = BaseRepository::getKeyPluck($grade_list['list'], 'user_id');
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                foreach ($grade_list['list'] as $key => $row) {
                    @unlink(storage_public(DATA_DIR . '/sc_file/seller_comment_' . $row['user_id'] . '.php'));

                    $seller_list[$key]['user_id'] = $row['user_id'];
                    $seller_list[$key]['shop_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';
                    $seller_list[$key]['seller_comment'] = $this->commentService->getMerchantsGoodsComment($row['user_id']);

                    $mc_all = isset($seller_list[$key]['seller_comment']['commentRank']['mc_all']) ? $seller_list[$key]['seller_comment']['commentRank']['mc_all'] : 0;

                    $desc = isset($seller_list[$key]['seller_comment']['cmt']['commentRank']['zconments']['score']) ? $seller_list[$key]['seller_comment']['cmt']['commentRank']['zconments']['score'] : 0;
                    $service = isset($seller_list[$key]['seller_comment']['cmt']['commentServer']['zconments']['score']) ? $seller_list[$key]['seller_comment']['cmt']['commentServer']['zconments']['score'] : 0;
                    $delivery = isset($seller_list[$key]['seller_comment']['cmt']['commentDelivery']['zconments']['score']) ? $seller_list[$key]['seller_comment']['cmt']['commentDelivery']['zconments']['score'] : 0;

                    write_static_cache('seller_comment_' . $row['user_id'], $seller_list[$key]);

                    $arr = [
                        'user_id' => $row['user_id'], //商家ID
                        'shop_name' => $seller_list[$key]['shop_name'], //店铺名称
                        'desc' => $desc, //商品描述相符
                        'service' => $service, //卖家服务态度
                        'delivery' => $delivery, //物流发货速度
                        'mc_all' => $mc_all, //订单商品评分数量
                    ];
                }
            }

            $result['list'] = $arr;

            $result['page'] = $grade_list['filter']['page'] + 1;
            $result['page_size'] = $grade_list['filter']['page_size'];
            $result['record_count'] = $grade_list['filter']['record_count'];
            $result['page_count'] = $grade_list['filter']['page_count'];

            $result['is_stop'] = 1;
            if ($page > $grade_list['filter']['page_count']) {
                $result['is_stop'] = 0;
            } else {
                $result['filter_page'] = $grade_list['filter']['page'];
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 店铺信息 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'seller_shopinfo') {
            admin_priv('users_merchants');

            //引入首页语言包
            $this->dscRepository->helpersLang('index', 'admin');

            $this->smarty->assign('lang', $GLOBALS['_LANG']);

            $data = read_static_cache('main_user_str');

            if ($data === false) {
                $this->smarty->assign('is_false', '1');
            } else {
                $this->smarty->assign('is_false', '0');
            }

            //链接基本信息
            $user_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
            $this->smarty->assign('users', get_table_date('merchants_shop_information', "user_id='$user_id'", ['user_id', 'hope_login_name', 'merchants_audit']));
            $this->smarty->assign('menu_select', ['current' => 'seller_shopinfo', 'action' => 'templates', 'action' => 'allot']);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['back'], 'href' => 'merchants_users_list.php?act=list' . '&' . list_link_postfix()]);
            $this->smarty->assign('ru_id', $user_id);

            /*源代码 start*/
            $this->smarty->assign('countries', get_regions());
            $this->smarty->assign('provinces', get_regions(1, 1));

            //获取入驻商家店铺信息 wang 商家入驻
            $res = SellerShopinfo::where('ru_id', $user_id);
            $res = $res->with(['getSellerQrcode']);
            $seller_shop_info = BaseRepository::getToArrayFirst($res);

            $seller_shop_info['qrcode_id'] = '';
            $seller_shop_info['qrcode_thumb'] = '';
            if (isset($seller_shop_info['get_seller_qrcode']) && !empty(isset($seller_shop_info['get_seller_qrcode']))) {
                $seller_shop_info['qrcode_id'] = $seller_shop_info['get_seller_qrcode']['qrcode_id'];
                $seller_shop_info['qrcode_thumb'] = $seller_shop_info['get_seller_qrcode']['qrcode_thumb'];
            }
            $action = 'add';
            if ($seller_shop_info) {
                $action = 'update';
            } else {
                $seller_shop_info = [
                    'logo_thumb' => '',
                    'street_thumb' => '',
                    'brand_thumb' => '',
                    'qrcode_thumb' => '',
                    'notice' => '',
                ];
            }
            $seller_shop_info['notice'] = isset($seller_shop_info['notice']) && !empty($seller_shop_info['notice']) ? $seller_shop_info['notice'] : '';

            $this->smarty->assign('seller_notice', $seller_shop_info['notice']);

            $shipping_list = warehouse_shipping_list();
            $this->smarty->assign('shipping_list', $shipping_list);
            //获取店铺二级域名 by kong
            $domain_name = SellerDomain::where('ru_id', $user_id)->value('domain_name');
            $domain_name = $domain_name ? $domain_name : '';
            $seller_shop_info['domain_name'] = $domain_name;//by kong

            //处理修改数据 by wu start
            $diff_data = get_seller_shopinfo_changelog($user_id);
            $seller_shop_info = array_replace($seller_shop_info, $diff_data);
            //处理修改数据 by wu end

            if ($seller_shop_info) {
                $seller_shop_info = array_replace($seller_shop_info, $diff_data);
                if (isset($seller_shop_info['logo_thumb']) && !empty($seller_shop_info['logo_thumb'])) {
                    $seller_shop_info['logo_thumb'] = str_replace('../', '', $seller_shop_info['logo_thumb']);
                    $seller_shop_info['logo_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['logo_thumb']);
                }
                if (isset($seller_shop_info['street_thumb']) && !empty($seller_shop_info['street_thumb'])) {
                    $seller_shop_info['street_thumb'] = str_replace('../', '', $seller_shop_info['street_thumb']);
                    $seller_shop_info['street_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['street_thumb']);
                }

                if (isset($seller_shop_info['brand_thumb']) && !empty($seller_shop_info['brand_thumb'])) {
                    $seller_shop_info['brand_thumb'] = str_replace('../', '', $seller_shop_info['brand_thumb']);
                    $seller_shop_info['brand_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['brand_thumb']);
                }
                if (isset($seller_shop_info['qrcode_thumb']) && !empty($seller_shop_info['qrcode_thumb'])) {
                    $seller_shop_info['qrcode_thumb'] = str_replace('../', '', $seller_shop_info['qrcode_thumb']);
                    $seller_shop_info['qrcode_thumb'] = $this->dscRepository->getImagePath($seller_shop_info['qrcode_thumb']);
                }
            }
            //处理修改数据 by wu end

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0 && $seller_shop_info) {
                $seller_shop_info['mobile'] = $this->dscRepository->stringToStar($seller_shop_info['mobile']);
                $seller_shop_info['seller_email'] = $this->dscRepository->stringToStar($seller_shop_info['seller_email']);
                $seller_shop_info['kf_tel'] = $this->dscRepository->stringToStar($seller_shop_info['kf_tel']);
            }

            $this->smarty->assign('shop_info', $seller_shop_info);

            $shop_information = $this->merchantCommonService->getShopName($user_id);
            $user_id == 0 ? $shop_information['is_dsc'] = true : $shop_information['is_dsc'] = false;//判断当前商家是平台,还是入驻商家 bylu
            $this->smarty->assign('shop_information', $shop_information);

            $province = isset($seller_shop_info['province']) ? $seller_shop_info['province'] : 0;
            $city = isset($seller_shop_info['city']) ? $seller_shop_info['city'] : 0;
            $this->smarty->assign('cities', get_regions(2, $province));
            $this->smarty->assign('districts', get_regions(3, $city));

            $this->smarty->assign('http', $this->dsc->http());
            $this->smarty->assign('data_op', $action);

            $host = $this->dscRepository->hostDomain();
            $this->smarty->assign('host', $host);

            $country_list = [];
            $cross_warehouse_list = [];
            $is_kj = 0;
            if (CROSS_BORDER === true) { // 跨境多商户
                $is_kj = 1;
                $country_list = app(\App\Custom\CrossBorder\Services\CountryService::class)->countryList();
                $cross_warehouse_list = app(\App\Custom\CrossBorder\Services\CrossWarehouseService::class)->crossWarehouseList();
            }

            $this->smarty->assign('is_kj', $is_kj);
            $this->smarty->assign('country_list', $country_list);
            $this->smarty->assign('cross_warehouse_list', $cross_warehouse_list);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_self_basic_info']);
            return $this->smarty->display('seller_shopinfo.dwt');
        }

        /*------------------------------------------------------ */
        //-- 保存店铺信息 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'save_seller_shopinfo') {

            //基本信息
            $ru_id = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;

            if (empty($ru_id)) {
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back_step'], 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $ru_id];
                return sys_msg($GLOBALS['_LANG']['invalid_data'], 0, $lnk);
            }

            //图片
            $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

            /*源代码 start*/
            $shop_name = empty($_POST['shop_name']) ? '' : addslashes(trim($_POST['shop_name']));
            $shop_title = empty($_POST['shop_title']) ? '' : addslashes(trim($_POST['shop_title']));
            $shop_keyword = empty($_POST['shop_keyword']) ? '' : addslashes(trim($_POST['shop_keyword']));
            $shop_desc = empty($_POST['shop_desc']) ? '' : addslashes(trim($_POST['shop_desc']));
            $shop_country = empty($_POST['shop_country']) ? 0 : intval($_POST['shop_country']);
            $shop_province = empty($_POST['shop_province']) ? 0 : intval($_POST['shop_province']);
            $shop_city = empty($_POST['shop_city']) ? 0 : intval($_POST['shop_city']);
            $shop_district = empty($_POST['shop_district']) ? 0 : intval($_POST['shop_district']);
            $shipping_id = empty($_POST['shipping_id']) ? 0 : intval($_POST['shipping_id']);
            $shop_address = empty($_POST['shop_address']) ? '' : addslashes(trim($_POST['shop_address']));
            $zipcode = request()->input('zipcode', '');     //邮政编码
            $mobile = empty($_POST['mobile']) ? '' : trim($_POST['mobile']); //by wu
            $seller_email = empty($_POST['seller_email']) ? '' : addslashes(trim($_POST['seller_email']));
            $street_desc = empty($_POST['street_desc']) ? '' : addslashes(trim($_POST['street_desc']));
            $kf_qq = empty($_POST['kf_qq']) ? '' : addslashes(trim($_POST['kf_qq']));
            $kf_ww = empty($_POST['kf_ww']) ? '' : addslashes(trim($_POST['kf_ww']));
            $service_url = empty($_POST['service_url']) ? '' : addslashes(trim($_POST['service_url']));

            $kf_touid = empty($_POST['kf_touid']) ? '' : addslashes(trim($_POST['kf_touid'])); //客服账号 bylu
            $kf_appkey = empty($_POST['kf_appkey']) ? 0 : addslashes(trim($_POST['kf_appkey'])); //appkey bylu
            $kf_secretkey = empty($_POST['kf_secretkey']) ? 0 : addslashes(trim($_POST['kf_secretkey'])); //secretkey bylu
            $kf_logo = empty($_POST['kf_logo']) ? 'http://' : addslashes(trim($_POST['kf_logo'])); //头像 bylu
            $kf_welcome_msg = empty($_POST['kf_welcome_msg']) ? '' : addslashes(trim($_POST['kf_welcome_msg'])); //欢迎语 bylu
            $meiqia = empty($_POST['meiqia']) ? '' : addslashes(trim($_POST['meiqia'])); //美洽客服

            $kf_type = empty($_POST['kf_type']) ? 1 : intval($_POST['kf_type']);
            $kf_tel = empty($_POST['kf_tel']) ? '' : addslashes(trim($_POST['kf_tel']));
            $notice = empty($_POST['notice']) ? '' : addslashes(trim($_POST['notice']));
            $data_op = empty($_POST['data_op']) ? '' : $_POST['data_op'];
            $check_sellername = empty($_POST['check_sellername']) ? 0 : intval($_POST['check_sellername']);
            $shop_style = isset($_POST['shop_style']) && !empty($_POST['shop_style']) ? intval($_POST['shop_style']) : 0;
            $domain_name = empty($_POST['domain_name']) ? '' : trim($_POST['domain_name']);
            $templates_mode = empty($_REQUEST['templates_mode']) ? 0 : intval($_REQUEST['templates_mode']);

            $tengxun_key = empty($_POST['tengxun_key']) ? '' : addslashes(trim($_POST['tengxun_key']));
            $longitude = empty($_POST['longitude']) ? '' : addslashes(trim($_POST['longitude']));
            $latitude = empty($_POST['latitude']) ? '' : addslashes(trim($_POST['latitude']));

            $js_appkey = empty($_POST['js_appkey']) ? '' : $_POST['js_appkey']; //扫码appkey
            $js_appsecret = empty($_POST['js_appsecret']) ? '' : $_POST['js_appsecret']; //扫码appsecret

            $print_type = empty($_POST['print_type']) ? 0 : intval($_POST['print_type']); //打印方式
            $kdniao_printer = empty($_POST['kdniao_printer']) ? '' : $_POST['kdniao_printer']; //打印机
            $shop_can_comment = request()->input('shop_can_comment', 1); // 店铺是否可评论

            //判断域名是否存在  by kong
            if (!empty($domain_name)) {
                $res = SellerDomain::where('domain_name', $domain_name)
                    ->where('ru_id', '<>', $ru_id)
                    ->count();
                if ($res > 0) {
                    $lnk[] = ['text' => $GLOBALS['_LANG']['go_back_step'], 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $ru_id];
                    return sys_msg($GLOBALS['_LANG']['domain_existed'], 0, $lnk);
                }
            }
            $seller_domain = [
                'ru_id' => $ru_id,
                'domain_name' => $domain_name,
            ];


            $shop_info = [
                'ru_id' => $ru_id,
                'shop_name' => $shop_name,
                'shop_title' => $shop_title,
                'shop_keyword' => $shop_keyword,
                'shop_desc' => $shop_desc,
                'country' => $shop_country,
                'province' => $shop_province,
                'city' => $shop_city,
                'district' => $shop_district,
                'shipping_id' => $shipping_id,
                'shop_address' => $shop_address,
                'mobile' => $mobile,
                'seller_email' => $seller_email,
                'kf_qq' => $kf_qq,
                'kf_ww' => $kf_ww,
                'service_url' => $service_url,
                'kf_appkey' => $kf_appkey, // bylu
                'kf_secretkey' => $kf_secretkey, // bylu
                'kf_touid' => $kf_touid, // bylu
                'kf_logo' => $kf_logo, // bylu
                'kf_welcome_msg' => $kf_welcome_msg, // bylu
                'meiqia' => $meiqia,
                'kf_type' => $kf_type,
                'kf_tel' => $kf_tel,
                'notice' => $notice,
                'street_desc' => $street_desc,
                'shop_style' => $shop_style,
                'check_sellername' => $check_sellername,
                'templates_mode' => $templates_mode,
                'tengxun_key' => $tengxun_key,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'js_appkey' => $js_appkey, //扫码appkey
                'js_appsecret' => $js_appsecret, //扫码appsecret
                'print_type' => $print_type,
                'kdniao_printer' => $kdniao_printer,
                'shop_can_comment' => $shop_can_comment,
                'zipcode' => $zipcode
            ];

            if (CROSS_BORDER === true) { // 跨境多商户
                $shop_info['cross_country_id'] = isset($_POST['cross_country_id']) && !empty($_POST['cross_country_id']) ? intval($_POST['cross_country_id']) : 0;
                $shop_info['cross_warehouse_id'] = isset($_POST['cross_warehouse_id']) && !empty($_POST['cross_warehouse_id']) ? intval($_POST['cross_warehouse_id']) : 0;
            }

            $res = SellerShopinfo::where('ru_id', $ru_id);
            $res = $res->with(['getSellerQrcode']);
            $store = BaseRepository::getToArrayFirst($res);
            $store['qrcode_thumb'] = '';
            if (isset($store['get_seller_qrcode']) && !empty($store['get_seller_qrcode'])) {
                $store['qrcode_thumb'] = $store['get_seller_qrcode']['qrcode_thumb'];
            }

            $oss_img = [];

            /* 允许上传的文件类型 */
            $allow_file_types = '|GIF|JPG|PNG|BMP|';

            /**
             * 创建目录
             */
            $logo_thumb_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_logo/logo_thumb/');
            if (!file_exists($logo_thumb_path)) {
                make_dir($logo_thumb_path);
            }

            if (!empty($_FILES['logo_thumb'])) {
                $file = $_FILES['logo_thumb'];
                /* 判断用户是否选择了文件 */
                if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none')) {
                    /* 检查上传的文件类型是否合法 */
                    if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
                        return sys_msg(sprintf($GLOBALS['_LANG']['msg_invalid_file'], $file['name']));
                    } else {
                        if ($file['name']) {
                            $ext = explode('.', $file['name']);
                            $ext = array_pop($ext);
                        } else {
                            $ext = "";
                        }

                        $file_name = storage_public(IMAGE_DIR . '/seller_imgs/seller_logo/logo_thumb/logo_thumb' . $ru_id . '.' . $ext);

                        /* 判断是否上传成功 */
                        if (move_upload_file($file['tmp_name'], $file_name)) {
                            $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

                            $logo_thumb = $image->make_thumb($file_name, 120, 120, storage_public(IMAGE_DIR . "/seller_imgs/seller_logo/logo_thumb/"));

                            if ($logo_thumb) {
                                $logo_thumb = str_replace(storage_public(), '', $logo_thumb);

                                $shop_info['logo_thumb'] = $logo_thumb;

                                dsc_unlink($file_name);

                                $oss_img['logo_thumb'] = $logo_thumb;
                            }
                        } else {
                            return sys_msg(sprintf($GLOBALS['_LANG']['msg_upload_failed'], $file['name'], IMAGE_DIR . '/seller_imgs/logo_thumb_' . $ru_id));
                        }
                    }
                }
            }

            $street_thumb = !empty($_FILES['street_thumb']) ? $image->upload_image($_FILES['street_thumb'], 'store_street/street_thumb') : '';  //图片存放地址 -- data/septs_image
            $brand_thumb = !empty($_FILES['brand_thumb']) ? $image->upload_image($_FILES['brand_thumb'], 'store_street/brand_thumb') : '';  //图片存放地址 -- data/septs_image

            $street_thumb = $street_thumb ? str_replace(storage_public(), '', $street_thumb) : '';
            $brand_thumb = $brand_thumb ? str_replace(storage_public(), '', $brand_thumb) : '';

            //$this->dscRepository->getOssAddFile([$street_thumb, $brand_thumb]);

            $oss_img['street_thumb'] = $street_thumb;
            $oss_img['brand_thumb'] = $brand_thumb;

            if ($street_thumb) {
                $shop_info['street_thumb'] = $street_thumb;
            }

            if ($brand_thumb) {
                $shop_info['brand_thumb'] = $brand_thumb;
            }

            //by kong
            $domain_id = SellerDomain::where('ru_id', $ru_id)->count();
            /* 二级域名绑定  by kong  satrt */
            if ($domain_id > 0) {
                SellerDomain::where('ru_id', $ru_id)->update($seller_domain);
            } else {
                SellerDomain::insert($seller_domain);
            }
            /* 二级域名绑定  by kong  end */

            /**
             * 创建目录
             */
            $seller_qrcode_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_qrcode/');
            if (!file_exists($seller_qrcode_path)) {
                make_dir($seller_qrcode_path);
            }

            $qrcode_thumb_path = storage_public(IMAGE_DIR . '/seller_imgs/seller_qrcode/qrcode_thumb/');
            if (!file_exists($qrcode_thumb_path)) {
                make_dir($qrcode_thumb_path);
            }

            //二维码中间logo by wu start
            if (!empty($_FILES['qrcode_thumb'])) {
                $file = $_FILES['qrcode_thumb'];
                /* 判断用户是否选择了文件 */
                if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none')) {
                    /* 检查上传的文件类型是否合法 */
                    if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
                        return sys_msg(sprintf($GLOBALS['_LANG']['msg_invalid_file'], $file['name']));
                    } else {
                        $ext = $file['name'] ? explode('.', $file['name']) : '';
                        $ext = $ext ? array_pop($ext) : '';

                        $file_name = storage_public(IMAGE_DIR . '/seller_imgs/seller_qrcode/qrcode_thumb/qrcode_thumb' . $ru_id . '.' . $ext);
                        /* 判断是否上传成功 */
                        if (move_upload_file($file['tmp_name'], $file_name)) {
                            $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

                            $qrcode_thumb = $image->make_thumb($file_name, 120, 120, storage_public(IMAGE_DIR . "/seller_imgs/seller_qrcode/qrcode_thumb/"));

                            if (!empty($qrcode_thumb)) {
                                $qrcode_thumb = str_replace(storage_public(), '', $qrcode_thumb);

                                $oss_img['qrcode_thumb'] = $qrcode_thumb;

                                if (isset($store['qrcode_thumb']) && $store['qrcode_thumb']) {
                                    $store['qrcode_thumb'] = str_replace(['../'], '', $store['qrcode_thumb']);
                                    dsc_unlink(storage_public($store['qrcode_thumb']));
                                }
                            }

                            /* 保存 */
                            $qrcode_count = SellerQrcode::where('ru_id', $ru_id)->count();
                            if ($qrcode_count > 0) {
                                if (!empty($qrcode_thumb)) {
                                    SellerQrcode::where('ru_id', $ru_id)
                                        ->update([
                                            'qrcode_thumb' => $qrcode_thumb
                                        ]);
                                }
                            } else {
                                SellerQrcode::insert([
                                    'ru_id' => $ru_id,
                                    'qrcode_thumb' => $qrcode_thumb
                                ]);
                            }
                        } else {
                            return sys_msg(sprintf($GLOBALS['_LANG']['msg_upload_failed'], $file['name'], IMAGE_DIR . '/seller_imgs/qrcode_thumb_' . $ru_id));
                        }
                    }
                }
            }
            //二维码中间logo by wu end

            $this->dscRepository->getOssAddFile($oss_img);

            $admin_user = [
                'email' => $seller_email
            ];

            AdminUser::where('user_id', session('admin_id'))->update($admin_user);

            if ($data_op == 'add') {
                if (!$store) {
                    //处理修改数据 by wu start
                    $review_status = empty($_REQUEST['review_status']) ? 1 : intval($_REQUEST['review_status']);
                    $review_content = empty($_REQUEST['review_content']) ? '' : trim($_REQUEST['review_content']);
                    $review_data = ['review_status' => $review_status, 'review_content' => $review_content];
                    if ($review_status == 3) {
                        $diff_data = get_seller_shopinfo_changelog($ru_id);
                        $shop_info = array_replace($shop_info, $diff_data);

                        SellerShopinfo::insert($shop_info);
                        SellerShopinfoChangelog::where('ru_id', $ru_id)->delete();
                    } else {
                        $data = ['id' => null, 'ru_id' => $ru_id];
                        SellerShopinfo::insert($data);
                    }
                    SellerShopinfo::where('ru_id', $ru_id)->update($review_data);
                    //处理修改数据 by wu end
                }

                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back_step'], 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $ru_id];
                return sys_msg($GLOBALS['_LANG']['add_store_info_success'], 0, $lnk);
            } else {
                $res = SellerShopinfo::where('ru_id', $ru_id);
                $seller_shop_info = BaseRepository::getToArrayFirst($res);
                $seller_shop_info['check_sellername'] = $seller_shop_info['check_sellername'] ?? 0;

                if ($seller_shop_info['check_sellername'] != $check_sellername) {
                    $shop_info['shopname_audit'] = 0;
                }

                $oss_del = [];

                if (isset($shop_info['logo_thumb']) && !empty($shop_info['logo_thumb'])) {
                    if (!empty($store['logo_thumb'])) {
                        $oss_del[] = $store['logo_thumb'];
                    }
                    dsc_unlink(storage_public($store['logo_thumb']));
                }

                if (!empty($street_thumb)) {
                    $oss_street_thumb = $store['street_thumb'];
                    if (!empty($oss_street_thumb)) {
                        $oss_del[] = $oss_street_thumb;
                    }

                    $shop_info['street_thumb'] = $street_thumb;
                    dsc_unlink(storage_public($oss_street_thumb));
                }

                if (!empty($brand_thumb)) {
                    $oss_brand_thumb = $store['brand_thumb'];
                    if (!empty($oss_brand_thumb)) {
                        $oss_del[] = $oss_brand_thumb;
                    }

                    $shop_info['brand_thumb'] = $brand_thumb;
                    dsc_unlink(storage_public($oss_brand_thumb));
                }

                $this->dscRepository->getOssDelFile($oss_del);

                //处理修改数据 by wu start
                $review_status = empty($_REQUEST['review_status']) ? 1 : intval($_REQUEST['review_status']);
                $review_content = empty($_REQUEST['review_content']) ? '' : trim($_REQUEST['review_content']);
                $review_data = ['review_status' => $review_status, 'review_content' => $review_content];
                if ($review_status == 3) {
                    $diff_data = get_seller_shopinfo_changelog($ru_id);
                    $shop_info = array_replace($shop_info, $diff_data);
                    SellerShopinfo::where('ru_id', $ru_id)->update($shop_info);

                    SellerShopinfoChangelog::where('ru_id', $ru_id)->delete();
                }
                SellerShopinfo::where('ru_id', $ru_id)->update($review_data);
                //处理修改数据 by wu end
                $Shopinfo_cache_name = 'SellerShopinfo_' . $ru_id;

                cache()->forget($Shopinfo_cache_name);

                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back_step'], 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $ru_id];
                return sys_msg($GLOBALS['_LANG']['update_store_info_success'], 0, $lnk);
            }
            /*源代码 end*/
        }

        /* ------------------------------------------------------ */
        //-- 查看商家店铺信息
        /* ------------------------------------------------------ */
        elseif ($act == 'see_shopinfo') {
            admin_priv('users_merchants');

            $user_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

            //店铺ru_id
            $adminru['ru_id'] = $user_id;
            if ($adminru['ru_id'] == 0) {
                $this->smarty->assign('priv_ru', 1);
            } else {
                $this->smarty->assign('priv_ru', 0);
            }
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['back'], 'href' => 'merchants_users_list.php?act=list' . '&' . list_link_postfix()]);
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            $res = MerchantsShopInformation::where('user_id', $user_id);

            $res = $res->with([
                'getSellerShopinfo' => function ($query) use ($user_id) {
                    $query->where('ru_id', $user_id);
                },
                'getMerchantsStepsFields' => function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                },
                'getUsers' => function ($query) {
                    $query->select('user_id', 'user_name', 'mobile_phone', 'email');
                }
            ]);

            $shop_information = BaseRepository::getToArrayFirst($res);

            if (!empty($shop_information)) {
                $shop_information['is_dsc'] = $adminru['ru_id'] == 0 ? true : false; //判断当前商家是平台,还是入驻商家
                // 申请时间、开店时间、到期时间
                $shop_information['add_time'] = isset($shop_information['add_time']) ? TimeRepository::getLocalDate('Y-m-d H:i:s', $shop_information['add_time']) : '';

                if (isset($shop_information['get_users']) && !empty($shop_information['get_users'])) {
                    $shop_information = collect($shop_information)->merge($shop_information['get_users'])->except('get_users')->all();

                    if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                        $shop_information['mobile_phone'] = $this->dscRepository->stringToStar($shop_information['mobile_phone']);
                        $shop_information['user_name'] = $this->dscRepository->stringToStar($shop_information['user_name']);
                        $shop_information['email'] = $this->dscRepository->stringToStar($shop_information['email']);
                    }
                }

                $seller_shop_info = $shop_information['get_seller_shopinfo'] ?? [];

                // 商家等级申请记录  平台保证金
                $seller_apply_info = SellerApplyInfo::where('ru_id', $user_id)->where('pay_status', 1)
                    ->where('apply_status', 1)
                    ->select('total_amount', 'pay_status', 'fee_num', 'pay_time')
                    ->orderBy('apply_id', 'desc')
                    ->limit(1);
                $seller_apply_info = BaseRepository::getToArrayFirst($seller_apply_info);

                if ($seller_apply_info) {
                    // 是否过期 fee_num 单位：年
                    $seller_apply_info['expire_status'] = TimeRepository::getGmTime() > ($seller_apply_info['fee_num'] * 365 * 24 * 60 * 60 + $seller_apply_info['pay_time']) ? 1 : 0;
                }
                $shop_information['seller_apply_info'] = $seller_apply_info ?? [];

                // 店铺名称
                $shop_information['shop_name'] = $this->merchantCommonService->getShopName($shop_information['user_id'], 1);
                // 主营类目 shop_categoryMain
                $shop_information['main_categories'] = Category::where('cat_id', $shop_information['shop_category_main'])->value('cat_name');

                // 所在地区 + 详细地址
                $region_list = [
                    $seller_shop_info['country'] ?? 1,
                    $seller_shop_info['province'],
                    $seller_shop_info['city'],
                    $seller_shop_info['district'],
                    $seller_shop_info['stree'] ?? '',
                ];
                $res = Region::whereIn('region_id', $region_list)->select('region_name')->get();
                $res = $res ? $res->toArray() : [];
                $region = '';
                foreach ($res as $arr) {
                    $region .= $arr['region_name'];
                }
                $shop_information['region'] = $region;
                // 开户行所在地区 linked_bank_address
                $linked_bank_address = $shop_information['get_merchants_steps_fields']['linked_bank_address'] ?? '';
                $link_region_list = [];
                if (!empty($linked_bank_address)) {
                    $link_region_list = explode(',', $linked_bank_address);
                }
                $link_res = Region::whereIn('region_id', $link_region_list)->select('region_name')->get();
                $link_res = $link_res ? $link_res->toArray() : [];
                $link_region = '';
                foreach ($link_res as $val) {
                    $link_region .= $val['region_name'];
                }
                $shop_information['get_merchants_steps_fields']['linked_bank_address'] = $link_region;

                // 店铺等级
                $grade_info = get_seller_grade($user_id);
                if (isset($grade_info['add_time']) && $grade_info['add_time']) {
                    $shop_information['grade_add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $grade_info['add_time']);
                    $shop_information['grade_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $grade_info['add_time'] + 365 * 24 * 60 * 60 * $grade_info['year_num']);
                }
                $shop_information['grade_img'] = $grade_info['grade_img'] ?? '';
                $shop_information['grade_name'] = $grade_info['grade_name'] ?? '';
            }

            // 店铺评分
            //$merch_cmt = $this->commentService->getMerchantsGoodsComment($user_id); //商家总体评分
            //$this->smarty->assign('merch_cmt', $merch_cmt);

            if (file_exists(MODULES_DIVIDE)) {
                // 商家二级子商户列表
                $seller_divide = \App\Manager\DivideTrace\Services\Seller\SellerDivideService::getList($user_id, ['start' => 0, 'limit' => 5]);
                $this->smarty->assign('seller_divide', $seller_divide['list'] ?? []);
            }

            // 账户资金
            $capital_details = $this->getMerchantAccountLog($shop_information['user_id']);
            $this->smarty->assign('capital_details', $capital_details);

            $is_permer = 0;
            if (PERSONAL_MERCHANTS === true) { // 个人入驻
                $permer = PersonalMerchantsService::permerExists();
                if (!empty($permer)) {
                    $is_permer = 1;
                    $shop_information['is_personal'] = $permer->getPersonal($shop_information['user_id']);
                }
            }
            $this->smarty->assign('is_permer', $is_permer);

            $seller_account = [
                'total_amount' => $this->getSellerTotalAmount($shop_information['user_id']), // 获取指定商家销售总额
                'gain_is_settlement' => app(CommissionService::class)->getSettlementPrice($shop_information['user_id'], 0, 1, 'gain_amount'), //收取已结算佣金金额
                'no_settlement' => app(CommissionService::class)->getSettlementPrice($shop_information['user_id'], 0, 0, 'actual_amount'), //未结算佣金金额
                'seller_money' => $this->getSellerAccount($shop_information['user_id'], 'seller_money'),
                'frozen_money' => $this->getSellerAccount($shop_information['user_id'], 'frozen_money'),
            ];

            $shop_information['logo_thumb'] = $this->dscRepository->getImagePath(isset($shop_information['get_seller_shopinfo']['logo_thumb']) ? $shop_information['get_seller_shopinfo']['logo_thumb'] : '');

            // 合并新语言包
            $GLOBALS['_LANG'] = array_merge($GLOBALS['_LANG'], trans('admin/seller_divide'));
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('shop_information', $shop_information);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_self_basic_info']);
            $this->smarty->assign('seller_account', $seller_account);

            return $this->smarty->display('merchants_users_see_shopinfo.dwt');
        }
        /* ------------------------------------------------------ */
        //-- 商家店铺等级到期时间 短信或邮件提醒
        /* ------------------------------------------------------ */
        elseif ($act == 'ajax_send_message') {
            $check_auth = check_authz_json('users_merchants');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $user_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']); // 商家店铺id
            $type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']); //type 0 短信 1 邮件

            /* 获取用户名和Email地址 */
            $res = Users::where('user_id', $user_id);
            $users = BaseRepository::getToArrayFirst($res);

            $to_email = $users['email'];
            $to_mobile = $users['mobile_phone'];
            $user_name = $users['user_name'];

            // 店铺等级
            $grade_info = get_seller_grade($user_id);
            $grade_end_time = TimeRepository::getLocalDate('Y-m-d H:i:s', $grade_info['add_time'] + 365 * 24 * 60 * 60 * $grade_info['year_num']);

            $shop_name = $GLOBALS['_CFG']['shop_name']; // 平台
            $send_date = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()); // 发送时间

            $send_ok = 1;
            /* 邮件通知处理流程 */
            if ($type == 1 && !empty($to_email)) {
                /* 设置留言回复模板所需要的内容信息 */
                $template = [
                    'template_subject' => '店铺等级到期时间提醒',
                    'is_html' => 1,
                    'template_content' => '<p>亲爱的{{ $user_name }}，你好！</p>
                <p>您的店铺等级到期时间快过期了，到期时间：{{ $grade_end_time }}</p>
                <p>如需继续使用，请您及时续费，以免造成不必要的损失。</p><br/><br/>
                {{ $shop_name }}
                <p>{{ $send_date }}</p>'
                ];

                $this->smarty->assign('user_name', $user_name);
                $this->smarty->assign('grade_end_time', $grade_end_time);
                $this->smarty->assign('shop_name', "<a href='" . url('/') . "'>" . $shop_name . '</a>');
                $this->smarty->assign('send_date', $send_date);

                $content = $this->smarty->fetch('str:' . $template['template_content']);

                /* 发送邮件 */
                if (CommonRepository::sendEmail($user_name, $to_email, $template['template_subject'], $content, $template['is_html'])) {
                    $send_ok = 0;
                } else {
                    $send_ok = 1;
                }
            }
            // 发送短信提醒
            if ($type == 0 && !empty($to_mobile)) {
                //普通订单->短信接口参数
                $pt_smsParams = [
                    'user_name' => $user_name,
                    'username' => $user_name,
                    'grade_end_time' => $grade_end_time,
                    'gradeendtime' => $grade_end_time,
                    'shop_name' => $shop_name,
                    'shopname' => $shop_name,
                    'send_date' => $send_date,
                    'senddate' => $send_date,
                    'mobile_phone' => $to_mobile,
                    'mobilephone' => $to_mobile
                ];

                $send_ok = $this->commonRepository->smsSend($to_mobile, $pt_smsParams, 'sms_seller_grade_time', false);
                $send_ok = ($send_ok === true) ? 0 : 1;
            }

            return make_json_response('', $send_ok);
        }
    }

    /*
    * 获取指定商家销售总额
    */
    private function getSellerTotalAmount($ru_id)
    {
        load_helper('order');
        $total_amount = OrderInfo::selectRaw("SUM(" . order_commission_field('') . ") AS total_amount ")
            ->where('ru_id', $ru_id);

        $total_amount = app(OrderCommonService::class)->orderQuerySelect($total_amount, 'finished');

        $total_amount = $total_amount->value('total_amount');

        $total_amount = $total_amount ? $total_amount : 0;

        return $total_amount > 0 ? $total_amount : 0;
    }

    /*
    * 获取指定商家资金
    */
    private function getSellerAccount($ru_id, $value = 'id')
    {

        $get_value = SellerShopinfo::where('ru_id', $ru_id)->value($value);

        return $get_value ?? 0;
    }

    /**
     * 资金管理日志
     */
    private function getMerchantAccountLog($user_id)
    {

        $res = MerchantsAccountLog::query()->where('user_id', $user_id)->orderBy('log_id', 'DESC')->limit(15);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $val) {
                $res[$k]['change_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['change_time']);
            }
        }

        return $res ?? [];
    }
}
