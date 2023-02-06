<?php

namespace App\Modules\Web\Controllers;

use App\Models\AdminUser;
use App\Models\MerchantsGrade;
use App\Models\MerchantsPrivilege;
use App\Models\MerchantsShopInformation;
use App\Models\MerchantsStepsFields;
use App\Models\SellerGrade;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\CrossBorder\CrossBorderService;

/**
 * 购物流程
 */
class MerchantsStepsActionController extends InitController
{
    private $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    )
    {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {

        /* ------------------------------------------------------ */
        //-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内        容
        /* ------------------------------------------------------ */

        $user_id = session('user_id', 0);

        $step = htmlspecialchars(trim(request()->input('step', '')));

        $sid = (int)request()->input('sid', 1);
        //协议
        $agreement = (int)request()->input('agreement', 0);
        //KEY传值
        $pid_key = (int)request()->input('pid_key', 0);
        //为空则显示品牌列表，否则添加或编辑品牌信息
        $brandView = htmlspecialchars(trim(request()->input('brandView', '')));
        $brandId = (int)request()->input('brandId', 0);

        $search_brandType = htmlspecialchars(request()->input('search_brandType', ''));
        $searchBrandZhInput = htmlspecialchars(trim(request()->input('searchBrandZhInput', '')));
        $searchBrandZhInput = !empty($searchBrandZhInput) ? addslashes($searchBrandZhInput) : '';
        $searchBrandEnInput = htmlspecialchars(trim(request()->input('searchBrandEnInput', '')));
        $searchBrandEnInput = !empty($searchBrandEnInput) ? addslashes($searchBrandEnInput) : '';

        if (CROSS_BORDER === true) { // 跨境多商户
            // cbec
            $huoyuan = trim(request()->input('huoyuan', ''));
        }

        if ($user_id <= 0) {
            return show_message($GLOBALS['_LANG']['steps_UserLogin'], $GLOBALS['_LANG']['UserLogin'], 'user.php');
        }

        $sf_agreement = MerchantsStepsFields::where('user_id', $user_id)->value('agreement');

        if ($sf_agreement != 1) {
            if ($agreement == 1) {
                $parent = [
                    'user_id' => $user_id,
                    'agreement' => $agreement
                ];

                MerchantsStepsFields::insert($parent);
            }
        } else {
            $shopTime_term = (int)request()->input('shopTime_term', 0);
            if ($pid_key == 2 && $step == 'stepTwo') {
                $parent = [
                    'shopTime_term' => $shopTime_term
                ];
                MerchantsStepsFields::where('user_id', $user_id)->update($parent);
            }

            $process_list = get_root_steps_process_list($sid);
            $process = isset($process_list[$pid_key]) && $process_list[$pid_key] ? $process_list[$pid_key] : '';


            $noWkey = $pid_key - 1;
            $noWprocess = $process_list[$noWkey];
            $form = get_steps_title_insert_form($noWprocess['id']);

            $parent = isset($form['formName']) ? get_setps_form_insert_date($form['formName']) : '';

            $parent['site_process'] = isset($parent['site_process']) && !empty($parent['site_process']) ? addslashes($parent['site_process']) : '';

            MerchantsStepsFields::where('user_id', $user_id)->update($parent);

            if (CROSS_BORDER === true) { // 跨境多商户
                $web = app(CrossBorderService::class)->webExists();

                if (!empty($web)) {
                    $web->updateSource($user_id, $huoyuan);
                }
            }

            if ($step == 'stepTwo') {
                if (!is_array($process)) {
                    $step = 'stepThree';
                    $pid_key = 0;
                } else {
                    $step = 'stepTwo';
                }
            } elseif ($step == 'stepThree') {
                if (!is_array($process)) {
                    $ec_rz_shopName = addslashes(trim(request()->input('ec_rz_shopName', '')));
                    $ec_hopeLoginName = addslashes(trim(request()->input('ec_hopeLoginName', '')));

                    $user = MerchantsShopInformation::where('rz_shop_name', $ec_rz_shopName)->where('user_id', '<>', $user_id)->value('user_id');

                    if ($user) {
                        return show_message($GLOBALS['_LANG']['Settled_Prompt'], $GLOBALS['_LANG']['Return_last_step'], "merchants_steps.php?step=" . $step . "&pid_key=" . $noWkey);
                    } else {
                        MerchantsShopInformation::where('user_id', $user_id)->update(['steps_audit' => 1, 'merchants_audit' => 0]);

                        $step = 'stepSubmit';
                    }

                    $user = AdminUser::where('user_name', $ec_hopeLoginName)->where('ru_id', '<>', $user_id)->value('user_id');

                    if ($user) {
                        return show_message($GLOBALS['_LANG']['Settled_Prompt_name'], $GLOBALS['_LANG']['Return_last_step'], "merchants_steps.php?step=" . $step . "&pid_key=" . $noWkey);
                    } else {
                        MerchantsShopInformation::where('user_id', $user_id)->update(['steps_audit' => 1]);

                        $step = 'stepSubmit';
                        $pid_key = 0;
                    }

                    /**
                     * 设置审核店铺功能
                     */
                    if (in_array(config('shop.seller_review'), [0, 1, 2])) {

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

                            $seller_step_email = config('shop.seller_step_email') ?? 0;
                            $contactEmail = $merchantsStepsFields['contactEmail'] ?? '';
                            if ($seller_step_email == 1 && !empty($contactEmail)) {

                                $shopinfo['seller_email'] = $contactEmail;

                                /* 发送邮件 */
                                $template = get_mail_template('seller_signin');
                                if ($template['template_content'] != '') {
                                    if ($shopinfo['seller_email']) {
                                        $this->smarty->assign('shop_name', $ec_hopeLoginName);
                                        $this->smarty->assign('seller_name', $ec_hopeLoginName);
                                        $this->smarty->assign('seller_psw', $pwd);
                                        $this->smarty->assign('site_name', config('shop.shop_name'));
                                        $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), gmtime()));
                                        $content = $this->smarty->fetch('str:' . $template['template_content']);

                                        CommonRepository::sendEmail($ec_hopeLoginName, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($step)) {
            $step = 'stepOne';
        }

        //操作品牌 start
        $act = '';
        if ($brandView == "brandView") {
            $pid_key -= 1;
        } elseif ($brandView == "add_brand") { //添加新品牌
            if ($brandId > 0) {
                $act .= "&brandId=" . $brandId . '&search_brandType=' . $search_brandType;
            }

            if ($searchBrandZhInput != '') {
                $act .= "&searchBrandZhInput=" . $searchBrandZhInput;
            }

            if ($searchBrandEnInput != '') {
                $act .= "&searchBrandEnInput=" . $searchBrandEnInput;
            }


            $act .= "&brandView=brandView";
        }
        //操作品牌 end

        $steps_site = "merchants_steps.php?step=" . $step . "&pid_key=" . $pid_key . $act;
        $site_process = MerchantsStepsFields::where('user_id', $user_id)->value('site_process');
        $site_process = $site_process ? $site_process : '';

        $strpos = $site_process ? strpos($site_process, $steps_site) : false;
        if ($strpos === false) { //不存在
            if (!empty($site_process)) {
                $site_process .= ',' . $steps_site;
            } else {
                $site_process = $steps_site;
            }

            $other = [
                'steps_site' => $steps_site,
                'site_process' => $site_process
            ];
            MerchantsStepsFields::where('user_id', $user_id)->update($other);
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            $web = app(CrossBorderService::class)->webExists();

            if (!empty($web)) {
                $web->smartyAssign();
            }
        }
        return dsc_header("Location: " . $steps_site . "\n");
    }
}
