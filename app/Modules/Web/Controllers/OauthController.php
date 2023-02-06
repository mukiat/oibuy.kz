<?php

namespace App\Modules\Web\Controllers;

use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Cart\CartCommonService;
use App\Services\Friend\FriendLinkService;
use App\Services\Navigator\NavigatorService;
use App\Services\User\ConnectUserService;
use App\Services\User\UserCommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class OauthController
 * @package App\Http\Controllers
 */
class OauthController extends InitController
{
    protected $connectUserService;
    protected $friendLinkService;
    protected $userCommonService;
    protected $cartCommonService;
    protected $navigatorService;

    public function __construct(
        ConnectUserService $connectUserService,
        FriendLinkService $friendLinkService,
        UserCommonService $userCommonService,
        CartCommonService $cartCommonService,
        NavigatorService $navigatorService
    )
    {
        $this->connectUserService = $connectUserService;
        $this->friendLinkService = $friendLinkService;
        $this->userCommonService = $userCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->navigatorService = $navigatorService;
    }

    /**
     * 构造函数
     */
    protected function initialize()
    {
        parent::initialize();
        load_helper(['passport']);

        L(lang('user'));
        L(lang('common'));
        $this->assign('lang', L());

        $this->common_info('user');
    }

    /**
     * 授权登录
     *
     * @param Request $request
     * @return array|mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'type' => 'required|string', // 授权登录类型
        ]);
        // 返回错误
        if ($validator->fails()) {
            return show_message($validator->errors()->first(), route('user'), 'error');
        }

        $type = $request->input('type');
        $back_url = $request->input('back_url', '');
        // 会员中心授权管理绑定
        $user_id = $request->input('user_id', 0);

        // 处理url
        $back_url = empty($back_url) ? route('user') : $back_url;
        $back_url = strip_tags(html_out($back_url));
        $back_url = str_replace(["|", "&amp;"], '&', $back_url);

        if ($user_id > 0) {
            $url = route('oauth', ['type' => $type, 'user_id' => $user_id, 'back_url' => $back_url]);
        } else {
            $url = route('oauth', ['type' => $type, 'back_url' => $back_url]);
        }

        $app = $this->getProvider($type);
        if (is_null($app)) {
            return [];
        }

        // 检测是否安装
        if ($app->status($type) == 0) {
            return show_message(lang('user.auth_not_exit'), lang('user.back_up_page'), route('user'), 'error');
        }

        // 授权回调
        $code = $request->get('code', '');
        if (isset($code) && $code != '') {
            if ($res = $app->callback()) {

                // 处理推荐u参数(会员推荐、分销商推荐)
                $up_uid = CommonRepository::getUserAffiliate();  // 获得推荐uid参数
                $res['parent_id'] = $up_uid ?? 0; // 同步推荐分成关系

                if (file_exists(MOBILE_DRP)) {
                    $up_drpid = CommonRepository::getDrpAffiliate();  // 获得分销商uid参数
                    $res['drp_parent_id'] = $up_drpid ?? 0;//同步分销关系
                }

                session(['unionid' => $res['unionid']]);
                session(['oauth_info' => $res]);
                $openid = $res['openid'] ?? '';
                session(['openid' => $openid]);

                // 会员中心授权管理绑定
                if ($user_id > 0 && session('user_id') == $user_id && !empty($res['unionid'])) {
                    $back_url = empty($back_url) ? route('user', ['act' => 'account_bind']) : $back_url;
                    if ($this->UserBind($res, $user_id, $type) === true) {
                        return redirect($back_url);
                    } else {
                        return show_message(lang('user.msg_account_bound'), lang('user.msg_rebound'), $back_url, 'error');
                    }
                } else {
                    // 查询是否绑定授权
                    $connectUser = $this->connectUserService->getConnectUserinfo($res['unionid'], $type);

                    if (empty($connectUser) && $type == 'weixin') {
                        // 查询同一微信开放平台下（unionid 相同） 绑定的授权用户
                        $connectUser = $this->connectUserService->getConnectUserWeixin($res['unionid']);
                    }

                    // 已经绑定过的 授权自动登录
                    if (!empty($connectUser)) {
                        // 已注册用户更新手机号
                        if (empty($connectUser['mobile_phone'])) {
                            return redirect()->route('oauth.bind_register', ['type' => $type, 'back_url' => $back_url]);
                        }

                        // 更新社会化登录用户信息
                        $res['user_id'] = $connectUser['user_id'];
                        $this->connectUserService->updateConnectUser($res, $type);
                        // 更新微信授权用户信息
                        if (file_exists(MOBILE_WECHAT) && $type == 'weixin') {
                            $res['openid'] = session('openid');
                            app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($res, 1); // 1 不更新ect_uid
                        }

                        // 登录
                        $this->doLogin($connectUser['user_name']);

                        return redirect($back_url);
                    }

                    if (!empty(session('unionid')) && session()->has('unionid') || $res['unionid']) {
                        // 注册并验证手机号
                        return redirect()->route('oauth.bind_register', ['type' => $type, 'back_url' => $back_url]);
                    } else {
                        return show_message(lang('user.msg_author_register_error'), lang('user.back_up_page'), route('user'), 'error');
                    }
                }
            } else {
                return show_message(lang('user.msg_authoriza_error'), lang('user.back_up_page'), route('user'), 'error');
            }
        }

        // 授权开始
        return $app->redirect($url);
    }

    /**
     * 微信绑定手机号注册
     *
     * @param Request $request
     * @return mixed
     */
    public function bind_register(Request $request)
    {
        if ($request->isMethod('post')) {
            $mobile = $request->input('mobile', '');
            $sms_code = $request->input('mobile_code', '');
            $type = $request->input('type', '');
            $back_url = $request->input('back_url', '');
            $back_url = empty($back_url) ? route('user') : $back_url;
            $back_url = strip_tags(html_out($back_url));

            if (strpos($back_url, 'http://') === false && strpos($back_url, 'https://') === false) {
                $back_url = $request->getSchemeAndHttpHost() . '/' . $back_url;
            }

            // 验证手机号不能为空
            if (empty($mobile)) {
                return response()->json(['status' => 'n', 'info' => lang('user.bind_mobile_null')]);
            }

            // 验证手机号格式
            if (!CommonRepository::getMatchPhone($mobile)) {
                return response()->json(['status' => 'n', 'info' => lang('user.bind_mobile_error')]);
            }

            // 验证短信验证码
            if (!session()->has('sms_mobile') || !session()->has('sms_mobile_code')) {
                return response()->json(['status' => 'n', 'info' => lang('user.bind_mobile_code_error')]);
            }
            if ($mobile != session('sms_mobile') || $sms_code != session('sms_mobile_code')) {
                return response()->json(['status' => 'n', 'info' => lang('user.bind_mobile_code_error')]);
            }

            $res = session()->get('oauth_info');
            $res['mobile_phone'] = $mobile;

            $type = ($type == 'weixin') ? 'wechat' : $type;// 统一PC与H5参数
            $connectUser = $this->connectUserService->getConnectUserinfo($res['unionid'], $type);

            if (empty($connectUser) && $type == 'wechat') {
                // 查询同一微信开放平台下（unionid 相同） 绑定的授权用户
                $connectUser = $this->connectUserService->getConnectUserWeixin($res['unionid']);
            }

            if (!empty($connectUser)) {
                if (empty($connectUser['mobile_phone'])) {
                    // 更新会员手机号
                    Users::where(['user_id' => $connectUser['user_id']])->update(['mobile_phone' => $mobile]);
                }

                // 更新社会化登录用户信息
                $res['user_id'] = $connectUser['user_id'];
                $this->connectUserService->updateConnectUser($res, $type);

                // 登录
                $this->doLogin($connectUser['user_name']);
                return response()->json(['status' => 'y', 'info' => lang('user.oauth_success'), 'url' => $back_url]);
            } else {
                if (!empty($mobile)) {
                    // 验证此手机号是否已经被绑定
                    $user_connect = $this->connectUserService->checkMobileBind($mobile, $type);
                    if (!empty($user_connect)) {
                        return response()->json(['status' => 'n', 'info' => lang('user.mobile_isbinded'), 'url' => $back_url]);
                    }

                    // 验证会员名或手机号是否已注册
                    $users = $this->connectUserService->checkMobileRegister($mobile);
                    if (!empty($users)) {
                        // 未被其他人绑定授权的 已有会员
                        if (session()->has('sms_mobile') && session()->has('sms_mobile_code') && $mobile == session('sms_mobile') && $sms_code == session('sms_mobile_code')) {
                            if (empty($users['mobile_phone'])) {
                                // 更新会员手机号
                                Users::where(['user_id' => $users['user_id']])->update(['mobile_phone' => $mobile]);
                            }
                            // 更新社会化登录用户信息
                            $res['user_id'] = $users['user_id'];
                            $this->connectUserService->updateConnectUser($res, $type);

                            // 登录
                            $this->doLogin($users['user_name']);

                            return response()->json(['status' => 'y', 'info' => lang('user.oauth_success'), 'url' => $back_url]);
                        }

                        // 该手机号已被注册, 请更换手机号码
                        return response()->json(['status' => 'n', 'info' => lang('user.please_change_mobile')]);
                    }
                }

                // 注册
                $result = $this->doRegister($res, $type);
                if ($result == true) {
                    return response()->json(['status' => 'y', 'info' => lang('user.oauth_success'), 'url' => $back_url]);
                } else {
                    return response()->json(['status' => 'n', 'info' => lang('user.oauth_fail'), 'url' => $back_url]);
                }
            }
        }

        $type = $request->input('type', '');
        $back_url = $request->input('back_url', '');
        $back_url = empty($back_url) ? route('user') : $back_url;
        $back_url = strip_tags(html_out($back_url));

        $oauth_info = session()->get('oauth_info');
        if (empty($oauth_info)) {
            return show_message(lang('user.oauth_fail'), lang('user.back_up_page'), route('user'), 'error');
        }

        $sms_security_code = rand(1000, 9999);
        session([
            'sms_security_code' => $sms_security_code
        ]);
        $this->assign('sms_security_code', $sms_security_code);

        $shop_logo = app(DscRepository::class)->getImagePath(config('shop.shop_logo'));
        $this->assign('shop_logo', $shop_logo);

        $cache_id = md5(serialize($oauth_info));

        $this->assign('oauth_info', $oauth_info);
        $this->assign('type', $type);
        $this->assign('back_url', $back_url);
        $this->assign('sms_signin', config('shop.sms_signin'));
        $this->assign('page_title', lang('user.bind_mobile'));
        return $this->display('oauth_bindregister', $cache_id);
    }

    /**
     * 设置成登录状态
     *
     * @param $username
     * @throws \Exception
     */
    protected function doLogin($username)
    {
        $GLOBALS['user']->set_session($username);
        $GLOBALS['user']->set_cookie($username);
        $this->userCommonService->updateUserInfo();
        $this->cartCommonService->recalculatePriceCart();
    }

    /**
     * 授权注册
     * @param array $res
     * @param string $type
     * @return bool
     */
    protected function doRegister($res = [], $type = '')
    {
        if (empty($res)) {
            return false;
        }

        $username = StrRepository::generate_username($type, $res['unionid']);
        $password = session()->has('sms_mobile_code') ? session('sms_mobile_code') : StrRepository::random(6) . mt_rand(10, 99); // 默认短信验证码为密码
        $email = '';//$username . '@qq.com';
        $extends = [
            'nick_name' => !empty($res['nickname']) ? $res['nickname'] : '',
            'sex' => !empty($res['sex']) ? $res['sex'] : 0,
            'user_picture' => !empty($res['headimgurl']) ? $res['headimgurl'] : '',
            'mobile_phone' => !empty($res['mobile_phone']) ? $res['mobile_phone'] : '',
        ];
        // 微信通粉丝 保存的推荐参数信息
        if (file_exists(MOBILE_WECHAT)) {
            $wechat_user = \App\Modules\Wechat\Models\WechatUser::select('drp_parent_id', 'parent_id')->where(['unionid' => $res['unionid']])->first();
            $wechat_user = $wechat_user ? $wechat_user->toArray() : [];
            if (!empty($wechat_user)) {
                if (file_exists(MOBILE_DRP)) {
                    $res['drp_parent_id'] = $wechat_user['drp_parent_id'] > 0 ? $wechat_user['drp_parent_id'] : 0;
                }
                $res['parent_id'] = $wechat_user['parent_id'] > 0 ? $wechat_user['parent_id'] : 0;
            }
        }
        // 普通用户
        if (file_exists(MOBILE_DRP) && isset($res['drp_parent_id']) && $res['drp_parent_id'] > 0) {
            $extends['drp_parent_id'] = $res['drp_parent_id'] > 0 ? $res['drp_parent_id'] : 0;
        }
        if (isset($res['parent_id']) && $res['parent_id'] > 0) {
            $extends['parent_id'] = $res['parent_id'] > 0 ? $res['parent_id'] : 0;
        }

        // 查询是否绑定
        $type = ($type == 'weixin') ? 'wechat' : $type;// 统一PC与H5参数
        $connectUser = $this->connectUserService->getConnectUserinfo($res['unionid'], $type);

        if (empty($connectUser) && $type == 'wechat') {
            // 查询同一微信开放平台下（unionid 相同） 绑定的授权用户
            $connectUser = $this->connectUserService->getConnectUserWeixin($res['unionid']);
        }

        if (empty($connectUser)) {
            if (register($username, $password, $email, $extends) !== false) {
                // 获取新的session
                $new_user = [
                    'user_id' => session('user_id', 0),
                    'user_rank' => session('user_rank', 0),
                    'discount' => session('discount', 1)
                ];
                session($new_user);
                // 更新社会化登录用户信息
                $res['user_id'] = session('user_id');
                $this->connectUserService->updateConnectUser($res, $type);

                // 更新用户头像与昵称信息
                $this->userCommonService->updateUsers($res);

                // 更新微信用户信息
                if (file_exists(MOBILE_WECHAT) && $type == 'wechat') {
                    $res['openid'] = session('openid');
                    $res['from'] = 2; // 2 微信扫码授权注册
                    app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($res);
                }

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 会员中心授权管理绑定帐号(自动)
     *
     * @param array $oauthUser
     * @param int $user_id
     * @param string $type
     * @return bool
     */
    protected function UserBind($oauthUser = [], $user_id = 0, $type = '')
    {
        if (empty($oauthUser) || empty($type)) {
            return false;
        }

        // 查询users用户是否存在
        $users = Users::select('user_id', 'user_name')->where('user_id', $user_id)->first();
        if ($users && !empty($oauthUser['unionid'])) {
            $type = ($type == 'weixin') ? 'wechat' : $type;// 统一PC与H5参数

            // 查询users用户是否被其他人绑定
            $connect_user_id = $this->connectUserService->checkConnectUserId($oauthUser['unionid'], $type);
            if ($connect_user_id > 0 && $connect_user_id != $users->user_id) {
                return false;
            }

            // 更新社会化登录用户信息
            $oauthUser['user_id'] = $user_id;
            $this->connectUserService->updateConnectUser($oauthUser, $type);
            // 更新微信粉丝信息
            if (file_exists(MOBILE_WECHAT) && $type == 'wechat') {
                app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser, 1); // 1 不更新ect_uid
            }

            // 重新登录
            $this->doLogin($users->user_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回实例
     *
     * @param string $type
     * @return \App\Plugins\Connect\Wechat\Wechat
     */
    protected function getProvider($type = '')
    {
        $type = StrRepository::studly($type);

        $provider = 'App\\Plugins\\Connect\\' . $type . '\\' . $type;

        if (!class_exists($provider)) {
            return null;
        }

        return new $provider();
    }

    /**
     * 底部共用信息
     *
     * @param string $filename 对应语言包文件名
     * @throws \Exception
     */
    protected function common_info($filename = '')
    {
        $this->assign('dwt_shop_name', config('shop.shop_name'));
        $this->assign('user_id', session('user_id'));

        // js提示语言文件
        $file_languages = (isset($GLOBALS['_LANG']['js_languages'][$filename]) && is_array($GLOBALS['_LANG']['js_languages'][$filename])) ? $GLOBALS['_LANG']['js_languages'][$filename] : [];
        $merge_js_languages = array_merge($GLOBALS['_LANG']['js_languages']['common'], $file_languages);
        $json_languages = json_encode($merge_js_languages);
        $this->assign('json_languages', $json_languages);

        //自定义导航栏
        $navigator_list = $this->navigatorService->getNavigator();
        $this->assign('navigator_list', $navigator_list);

        // ICP 备案信息
        $icp_number = config('shop.icp_number');
        $this->assign('icp_number', $icp_number);

        $icp_file = config('shop.icp_file');
        $this->assign('icp_file', $icp_file);

        // 友情连接
        $links = $this->friendLinkService->getIndexGetLinks();
        $this->assign('img_links', $links['img']);
        $this->assign('txt_links', $links['txt']);
    }
}
