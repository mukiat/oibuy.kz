<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />
<meta name="Description" content="{{ $description }}" />

<title>{{ $page_title }}</title>



<link rel="shortcut icon" href="favicon.ico" />
@include('frontend::library/js_languages_new')
<script type="text/javascript">
/*登录、注册、找回密码js语言包*/

@foreach($lang['js_languages']['passport_js'] as $key => $item)

var {{ $key }} = "{!! $item !!}";

@endforeach

</script>
</head>

<body class="bg-ligtGary">

@if($action == 'login')

<div class="login">
    <div class="loginRegister-header">
        <div class="w w1200">
            <div class="logo">
                <div class="logoImg"><a href="{{ $url_index }}" class="logo">
@if($user_login_logo)
<img src="{{ $user_login_logo }}" />
@endif
</a></div>
                <div class="logo-span">

@if($login_logo_pic)
<b style="background:url({{ $login_logo_pic }}) no-repeat;"></b>
@endif

                </div>
            </div>
            <div class="header-href">

@if($shop_reg_closed != 1)

                <span>{{ $lang['not_account_number'] }}<a href="user.php?act=register" class="jump">{{ $lang['Free_registration'] }}</a></span>

@endif

            </div>
        </div>
    </div>
    <div class="container">
        <div class="login-wrap">
            <div class="w w1200">
                <div class="login-form module-static">
                    <div class="coagent">

@if($website_list)

                        <div class="tit"><h3>{{ $lang['Third_party_Lgion'] }}</h3><span></span></div>
                        <div class="coagent-warp">

@foreach($website_list as $website)

                            <a href="oauth?type={{ $website['type'] }}
@if($back_act != '')
&back_url={{ $back_act }}
@endif
" class="{{ $website['type'] }}"><b class="third-party-icon {{ $website['type'] }}-icon"></b></a>

@endforeach

                        </div>

@endif


@if($app_client)

                        <div class="login-switch" ectype="loginSwitch">
                            <i class="iconfont icon-qr-code"></i>
                            <i class="iconfont icon-identity"></i>
                            <div class="triangle"></div>
                        </div>

@endif

                    </div>
                    <div class="login-box">
                        <div class="static-form">
                            <div class="tit"><h3>{{ $lang['account_login'] }}</h3><span></span></div>
                            <div class="msg-wrap">
                                <div class="msg-error" style="display:none">{{ $lang['passport_one'] }}</div>
                            </div>
                            <div class="form">
                                <form name="formLogin" action="user.php" method="post" autocomplete="off" onSubmit="userLogin();return false;">
                                    <div class="item">
                                        <div class="item-info">
                                            <i class="iconfont icon-name"></i>
                                            <input type="text" id="username" name="username" class="text" value="" placeholder="{{ $lang['label_username'] }}" autocomplete="off" />
                                        </div>
                                    </div>
                                    <div class="item" ectype="password">
                                        <div class="item-info">
                                            <i class="iconfont icon-password"></i>
                                            <input type="password" id="nloginpwd" name="password" class="text" value="" placeholder="{{ $lang['label_password'] }}" autocomplete="new-password" />
                                        </div>
                                    </div>
                                    @include('frontend::library/captcha')
                                    <div class="item item-button">
                                        <input type="hidden" name="dsc_token" value="{{ $dsc_token ?? 0 }}" autocomplete="off" />
                                        <input type="hidden" name="act" value="act_login" autocomplete="off" />
                                        <input type="hidden" name="back_act" value="{{ $back_act }}" autocomplete="off" />
                                        <input type="submit" name="submit" id="loginSubmit" value="{{ $lang['signin_now'] }}" class="btn sc-redBg-btn">
                                    </div>
                                    <a href="user.php?act=get_password" class="notpwd gary">{{ $lang['passportforgot_password'] }}</a>
                                @csrf </form>
                            </div>
                        </div>

@if($app_client)

                        <div class="quick-form" style="display: none;">
                            <div class="tit"><h3>{{ $lang['quick_login'] }}</h3><span></span></div>
                            <div class="mc">
                                <div class="qrcode-main">
                                    <div class="qrcode-error" style="display: none;">
                                        <div class="qrcode-error-mask"></div>
                                        <p>{{ $lang['code_invalid'] }}</p>
                                        <a href="javascript:;" class="refresh-btn" ectype="refreshBtn">{{ $lang['refresh'] }}</a>
                                    </div>
                                    <div class="qrcode-img"><img src="{{ skin('images/loadGoods.gif') }}" /></div>
                                    <div class="qrcode-panel">
                                        <span>{{ $lang['open'] }}<a href="javascript:;">{{ $dwt_shop_name }}App</a></span>
                                        <span>{{ $lang['code_scan'] }}</span>
                                    </div>
                                </div>
                                <div class="qrcode-succ" style="display: none;">
                                    <span class="succ-icon"></span>
                                    <div class="item-fore">
                                        <h3>{{ $lang['code_scan_success'] }}</h3>
                                        <span>{{ $lang['code_scan_success_notic'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

@endif

                    </div>
                </div>
            </div>
            {{-- DSC 提醒您：动态载入login_banner.lbi，登陆页广告位 --}}
{!! insert_get_adv_child(['ad_arr' => $login_banner]) !!}
        </div>
    </div>
</div>

@endif



@if($action == 'register')

<div class="register">
    <div class="loginRegister-header">
        <div class="w w1200">
            <div class="logo">
                <div class="logoImg"><a href="{{ $url_index }}" class="logo">
@if($user_login_logo)
<img src="{{ $user_login_logo }}" />
@endif
</a></div>
                <div class="logo-span">

@if($login_logo_pic)
<b style="background:url({{ $login_logo_pic }}) no-repeat;"></b>
@endif

                </div>
            </div>
            <div class="header-href">
                <span>{{ $lang['label_registered'] }}<a href="user.php" class="jump">{{ $lang['login_here'] }}</a></span>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="w w1200">
            <div class="register-wrap">
                <div class="register-adv">
                {{-- DSC 提醒您：动态载入login_banner.lbi，注册页左侧广告 --}}
{!! insert_get_adv(['logo_name' => $regist_banner]) !!}
                </div>
                <div class="register-form">
                    <div class="form form-other">
                        <form action="user.php" method="post" name="formUser">
                            <div class="item" id="phone_yz">
                                <div class="item-label">{{ $lang['bind_phone'] }}</div>
                                <div class="item-info">
                                    <input type="text" name="mobile_phone" id="mobile_phone" class="text" placeholder="{{ $lang['label_mobile_input'] }}" value="" autocomplete="off" />
                                </div>
                                <div class="input-tip"><label id="mobile_notice"></label></div>
                            </div>


@if($enabled_captcha)

                            <div class="item">
                                <div class="item-label">{{ $lang['Code_bind'] }}</div>
                                <div class="item-info">
                                    <input type="text" id="captcha" name="captcha" class="text text-2 fl" placeholder="{{ $lang['label_captcha_input'] }}" value="" autocomplete="off" />
                                    <img src="captcha_verify.php?captcha=is_register_phone&{{ $rand }}" class="captcha_img fl" onClick="this.src='captcha_verify.php?captcha=is_register_phone&'+Math.random()">
                                </div>
                                <div class="input-tip"><label id="captcha_notice"></label></div>
                            </div>

@endif



@if($cfg['sms_signin'] == 1)

                            <div class="item" id="code_mobile">
                                <div class="item-label">{{ $lang['bindMobile_code'] }}</div>
                                <div class="item-info">
                                    <input type="text" id="sms" name="mobile_code" class="text text-2" placeholder="{{ $lang['bind_mobile_code_null'] }}" value="" autocomplete="off" />
                                    <a href="javascript:sendSms();" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                </div>
                                <div class="input-tip"><label id="phone_notice"></label></div>
                            </div>

@endif


                            <div class="item">
                                <div class="item-label">{{ $lang['bind_password'] }}</div>
                                <div class="item-info">
                                    <input type="password" name="password" id="pwd" class="text" placeholder="{{ $lang['bind_password_null'] }}" value="" autocomplete="off" />
                                </div>
                                <div class="input-tip"></div>
                            </div>
                            <div class="item">
                                <div class="item-label">{{ $lang['bind_password2'] }}</div>
                                <div class="item-info">
                                    <input type="password" name="confirm_password" id="pwdRepeat" class="text" placeholder="{{ $lang['bind_password_again_null'] }}" value="" autocomplete="off" />
                                </div>
                                <div class="input-tip"></div>
                            </div>


@foreach($extend_info_list as $field)


@if($field['id'] == 6)

                            <div class="item" style="overflow:visible;">
                                <div class="item-label">{{ $lang['Prompt_problem'] }}</div>
                                <div class="item-info" style=" border:0;">
                                    <div id="divselect" class="divselect">
                                      <div class="cite"><span>{{ $lang['passwd_question'] }}</span></div>
                                      <ul>

@foreach($passwd_questions as $key => $val)

                                         <li><a href="javascript:;" data-value="{{ $key }}">{{ $val }}</a></li>

@endforeach

                                      </ul>
                                      <input name="sel_question" type="hidden" value="" id="passwd_quesetion" autocomplete="off">
                                    </div>
                                </div>
                                <div class="input-tip"></div>
                            </div>
                            <div class="item">
                                <div class="item-label">{{ $lang['passwd_answer_useer'] }}</div>
                                <div class="item-info">
                                    <input type="text" name="passwd_answer" maxlength="20" class="text" value="" placeholder="{{ $lang['passwd_answer'] }}" autocomplete="off" />
                                </div>
                                <div class="input-tip"></div>
                            </div>

@else


@if($field['reg_field_name'] != $lang['mobile'])

                            <div class="item">
                                <div class="item-label">{{ $field['reg_field_name'] }}</div>
                                <div class="item-info">
                                    <input name="extend_field{{ $field['id'] }}" id="extend_field{{ $field['id'] }}" type="text" maxlength="35" class="text"
@if($field['is_need'])
 required data-msg="<i class='iconfont icon-minus-sign'></i>{{ $lang['field_is_write'] }}"
@endif
 autocomplete="off" />
                                </div>
                                <div class="input-tip"><span class="extend_field{{ $field['id'] }}"></span></div>
                            </div>

@endif


@endif


@endforeach


                            <div class="item item2">
                                <div class="item-checkbox">
                                    <input type="checkbox" id="clause2" class="ui-solid-checkbox" checked="checked" value="1" name="mobileagreement">
                                    <label class="ui-solid-label" for="clause2">{{ $lang['agreed_bind'] }}<a href="{{ $register_article_id }}" class="ftx-05" target="_blank">《{{ $dwt_shop_name }}{{ $lang['protocol_bind'] }}》</a></label>
                                </div>
                                <div class="input-tip"></div>
                            </div>
                            <div class="item item2 item-button">
                                <input type="hidden" name="flag" id="flag" value="register" />
                                <input name="register_type" type="hidden" value="1" />
                                <input type="hidden" name="seccode" id="seccode" value="{{ $sms_security_code }}" />
                                <input name="act" type="hidden" value="act_register" />
                                <input name="register_mode" type="hidden" value="1" />
                                <input id="phone_code_callback" type="hidden" value="0" />
                                <input id="phone_captcha_verification" type="hidden" value="" />
                                <input id="phone_verification" type="hidden" value="0" />
                                <input type="hidden" name="back_act" value="{{ $back_act }}" />
                                <input type="submit" id="registsubmit" name="Submit" maxlength="8" class="btn sc-redBg-btn" value="{{ $lang['register_now'] }}"/>
                            </div>
                        @csrf </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif



@if($action == 'get_password')

<div class="get_pwd">
    <div class="loginRegister-header">
        <div class="w w1200">
            <div class="logo">
                <div class="logoImg"><a href="{{ $url_index }}" class="logo">
@if($user_login_logo)
<img src="{{ $user_login_logo }}" />
@endif
</a></div>
                <div class="logo-span">

@if($login_logo_pic)
<b style="background:url({{ $login_logo_pic }}) no-repeat;"></b>
@endif

                </div>
            </div>
            <div class="header-href">
                <span>{{ $lang['label_registered'] }}<a href="user.php" class="jump">{{ $lang['login_here'] }}</a></span>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="w w1200">
            <div class="get_pwd_warp">
                <div class="get_pwd_form">

                    <div class="form form-other" ectype="formWarp">
                        <div class="item item-other">
                            <div class="item-label">&nbsp;</div>
                            <div class="gp-tab
@foreach($extend_info_list as $field)

@if($field['id'] == 6)
 gptabThree
@endif

@endforeach
" ectype="gpTab">
                                <ul>

@if($enabled_sms_signin == 1)

                                    <li class="curr"><i class="iconfont icon-mobile-phone"></i><span>{{ $lang['mobile_retrieve'] }}</span></li>
                                    <li><i class="iconfont icon-email"></i><span>{{ $lang['email_retrieve'] }}</span></li>

@else

                                    <li class="curr"><i class="iconfont icon-email"></i><span>{{ $lang['email_retrieve'] }}</span></li>

@endif


@foreach($extend_info_list as $field)

@if($field['id'] == 6)
<li ectype="gptabLast"><i class="iconfont icon-icon02"></i><span>{{ $lang['Regist_problem'] }}</span></li>
@endif

@endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="gp-content" ectype="gpContent">

@if($enabled_sms_signin == 1)

                            <div class="gp-warp formPhone" style="display:block;">
                                <form action="user.php" method="post" name="getPhonePassword" ectype="form">
                                    <div class="item">
                                        <div class="item-label">{{ $lang['bind_phone'] }}</div>
                                        <div class="item-info"><input type="text" name="mobile_phone" id="mobile_phone" class="text" autocomplete="off" placeholder="{{ $lang['bind_mobile_regist'] }}" /></div>
                                        <div class="input-tip" id="phone_notice"><label id="mobile_notice"></label></div>
                                    </div>

                                    <div class="item">
                                        <div class="item-label">{{ $lang['Code_bind'] }}</div>
                                        <div class="item-info">
                                            <input type="hidden" name="seKey" value="get_phone_password" autocomplete="off" />
                                            <input type="text" id="mobile_captcha" name="captcha" class="text text-2 fl" value="" maxlength="6"  placeholder="{{ $lang['comment_captcha'] }}" autocomplete="off" />
                                            <img src="captcha_verify.php?captcha=is_get_phone_password&{{ $rand }}" alt="captcha" name="img_captcha" onClick="this.src='captcha_verify.php?captcha=is_get_phone_password&'+Math.random()" data-key="get_phone_password" class="captcha_img fl">
                                        	<span class="fr lh30 red">{{ $lang['captcha_notic'] }}</span>
                                        </div>
                                        <div class="input-tip"><label id="captcha_notice"></label></div>
                                    </div>

                                    <div class="item">
                                        <div class="item-label">{{ $lang['bindMobile_code'] }}</div>
                                        <div class="item-info phone_code">
                                            <input name="sms_value" id="sms_value" type="hidden" value="sms_find_signin" autocomplete="off" />
                                            <input type="text" id="sms" name="mobile_code" class="text text-2"  maxlength="6" value="" autocomplete="off" placeholder="{{ $lang['msg_mobile_code'] }}"/>
                                            <a href="javascript:sendSms();" id="zphone" class="sms-btn">{{ $lang['get_verification_code'] }}</a>
                                        </div>
                                        <div class="input-tip"></div>
                                    </div>
                                    <div class="item item2 item-button">
                                        <input type="hidden" name="flag" id="flag" value="forget" />
                                        <input type="hidden" name="seccode" id="seccode" value="{{ $sms_security_code }}" />
                                        <input type="hidden" name="act" value="get_pwd_mobile" />
                                        <input type="submit" name="submit" id="get-phone-submit" value="{{ $lang['submit'] }}" class="btn sc-redBg-btn" ectype="submitBtn">
                                    </div>
                                @csrf </form>
                            </div>

@endif

                            <div class="gp-warp formEmail"
@if($enabled_sms_signin == 0)
style="display:block;"
@endif
>
                                <form action="user.php" method="post" name="getEmailPassword" ectype="form">
                                    <div class="item">
                                        <div class="item-label">{{ $lang['username_bind'] }}</div>
                                        <div class="item-info"><input type="text" name="user_name" class="text" placeholder="{{ $lang['username'] }}" autocomplete="off" /></div>
                                        <div class="input-tip"></div>
                                    </div>
                                    <div class="item">
                                        <div class="item-label">{{ $lang['email_account'] }}</div>
                                        <div class="item-info"><input type="text" name="email" id="email" class="text" placeholder="{{ $lang['email_reset'] }}" autocomplete="off" /></div>
                                        <div class="input-tip"></div>
                                    </div>


@if($enabled_captcha)

                                    <div class="item">
                                        <div class="item-label">{{ $lang['Code_bind'] }}</div>
                                        <div class="item-info">
                                            <input type="text" id="captcha" name="captcha" class="text text-2 fl" value="" maxlength="6"  placeholder="{{ $lang['comment_captcha'] }}" autocomplete="off" />
                                            <img src="captcha_verify.php?captcha=is_get_password&{{ $rand }}" alt="captcha" name="img_captcha" onClick="this.src='captcha_verify.php?captcha=is_get_password&'+Math.random()" data-key="get_password" class="captcha_img fl">
                                        	<span class="fr lh30 red">{{ $lang['captcha_notic'] }}</span>
                                        </div>
                                        <div class="input-tip"></div>
                                    </div>

@endif

                                    <div class="item item2 item-button">
                                        <input type="hidden" name="act" value="send_pwd_email" />
                                        <input type="hidden" id="captcha_verification" name="captcha_verification" value="" />
                                        <input type="hidden" id="email_enabled_captcha" value="{{ $enabled_captcha }}" />
                                        <input type="submit" name="submit" id="get-phone-submit" value="{{ $lang['submit'] }}" class="btn sc-redBg-btn" ectype="submitBtn">
                                    </div>
                                @csrf </form>
                            </div>

                            <div class="gp-warp formWenti" ectype="gpwarpLast" style="display:none;">
                                <form action="user.php" method="post" name="getWentiPassword" ectype="form">
                                <div class="item">
                                	<div class="item-label">{{ $lang['username_bind'] }}</div>
                                    <div class="item-info"><input name="user_name" type="text" class="text" value="" placeholder="{{ $lang['username'] }}" autocomplete="off" /></div>
                                    <div class="input-tip"></div>
                                </div>

@foreach($extend_info_list as $field)


@if($field['id'] == 6)

                                <div class="item">
                                	<div class="item-label">{{ $lang['Prompt_problem'] }}：</div>
                                    <div class="fl">
                                        <div id="divselect" class="divselect">
                                          <div class="cite"><span>{{ $lang['passwd_question'] }}</span></div>
                                          <ul>

@foreach($passwd_questions as $key => $val)

                                             <li><a href="javascript:;" data-value="{{ $key }}">{{ $val }}</a></li>

@endforeach

                                          </ul>
                                          <input name="sel_question" type="hidden" value="" id="passwd_quesetion" >
                                        </div>
                                        <input name="is_passwd_questions" type="hidden" value="1"  />
                                    </div>
                                    <div class="input-tip"></div>
                                </div>
                                <div class="item">
                                	<div class="item-label">{{ $lang['passwd_answer_useer'] }}：</div>
                                    <div class="item-info">
                                        <input name="passwd_answer" type="text" size="25" class="text" maxlengt='20' placeholder="{{ $lang['passwd_answer'] }}" autocomplete="off" />
                                    </div>
                                    <div class="input-tip"></div>
                                </div>

@endif


@endforeach


@if($enabled_captcha)

                                <div class="item">
                                    <div class="item-label">{{ $lang['Code_bind'] }}</div>
                                    <div class="item-info">
                                        <input type="hidden" name="seKey" value="get_password" autocomplete="off" />
                                        <input type="text" id="mobile_captcha" name="captcha" class="text text-2 fl" value="" maxlength="6"  placeholder="{{ $lang['comment_captcha'] }}" autocomplete="off" />
                                        <img src="captcha_verify.php?captcha=get_pwd_question&{{ $rand }}" alt="captcha" name="img_captcha" onClick="this.src='captcha_verify.php?captcha=get_pwd_question&'+Math.random()" data-key="psw_question" class="captcha_img fl">
                                        <span class="fr lh30 red">{{ $lang['captcha_notic'] }}</span>
                                    </div>
                                    <div class="input-tip"></div>
                                </div>

@endif

                                <div class="item item2 item-button">
                                    <input type="hidden" name="act" value="check_answer"  />
                                    <input type="submit" name="submit" value="{{ $lang['submit'] }}" class="btn sc-redBg-btn" ectype="submitBtn">
                                </div>
                                @csrf </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif



@if($action == 'reset_password')

<div class="get_pwd">
    <div class="loginRegister-header">
        <div class="w w1200">
            <div class="logo">
                <div class="logoImg"><a href="{{ $url_index }}" class="logo">
@if($user_login_logo)
<img src="{{ $user_login_logo }}" />
@endif
</a></div>
                <div class="logo-span">

@if($login_logo_pic)
<b style="background:url({{ $login_logo_pic }}) no-repeat;"></b>
@endif

                </div>
            </div>
            <div class="header-href">
                <span>{{ $lang['label_registered'] }}<a href="user.php" class="jump">{{ $lang['login_here'] }}</a></span>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="w w1200">
            <div class="get_pwd_warp">
                <div class="get_pwd_form">

                    <div class="form form-other" >
                        <form action="user.php" method="post" name="getPassword2" ectype="form">
                            <div class="item item-other">
                                <div class="item-label">&nbsp;</div>
                                <div class="gp-tit"><i class="iconfont icon-password"></i>{{ $lang['reset_password'] }}</div>
                            </div>
                            <div class="gp-content">
                                <div class="gp-warp" style="display:block;">
                                    <div class="item">
                                        <div class="item-label">{{ $lang['bind_password'] }}</div>
                                        <div class="item-info">
                                            <input name="new_password" type="password" id="pwd" class="text" autocomplete="off" placeholder="{{ $lang['new_password'] }}"/>
                                        </div>
                                        <div class="input-tip"></div>
                                    </div>
                                    <div class="item">
                                        <div class="item-label">{{ $lang['bind_password2'] }}</div>
                                        <div class="item-info">
                                            <input name="confirm_password" type="password" id="pwdRepeat" class="text" autocomplete="off" placeholder="{{ $lang['confirm_password'] }}" />
                                        </div>
                                        <div class="input-tip"></div>
                                    </div>

@if($enabled_captcha)

                                    <div class="item">
                                        <div class="item-label">{{ $lang['Code_bind'] }}</div>
                                        <div class="item-info">
                                            <input type="text" id="captcha" name="captcha" class="text text-2 fl" value="" autocomplete="off" maxlength="6"  placeholder="{{ $lang['comment_captcha'] }}"/>
                                            <img src="captcha_verify.php?captcha=is_get_password&{{ $rand }}" alt="captcha" name="img_captcha" onClick="this.src='captcha_verify.php?captcha=is_get_password&'+Math.random()" class="captcha_img fl" data-key="get_password">
                                        	<span class="fr lh30 red">{{ $lang['captcha_notic'] }}</span>
                                        </div>
                                        <div class="input-tip"></div>
                                    </div>

@endif

                                    <div class="item item2 item-button">
                                        <input type="hidden" name="act" value="act_edit_password" />
                                        <input type="hidden" name="code" value="{{ $code }}" />
                                        <input type="submit" name="submit" id="get-phone-submit" value="{{ $lang['submit'] }}" class="btn sc-redBg-btn" ectype="submitBtn">
                                    </div>
                                </div>
                            </div>
                        @csrf </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

@include('frontend::library/page_footer_flow')

<script type="text/javascript" src="{{ asset('js/base64.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/user.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/user_register.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/sms/sms.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript" src="{{ asset('/js/lib_ecmobanFunc.js') }}"></script>
<script type="text/javascript">
$(function(){

    $("*[ectype='form']").submit(function(){
        //防止表单重复提交
        if(checkSubmit() == true){
            $(this).submit();
        }else{
            return false
        }
    });


    if(document.getElementById("seccode")){
		$("#seccode").val({{ $sms_security_code ?? 0 }});
	}


@if($action == 'get_password')

	//找回密码方式切换
	$("*[ectype='formWarp']").slide({titCell:"*[ectype='gpTab'] li",mainCell:"*[ectype='gpContent']",effect:"fade",trigger:"click",titOnClassName:"curr"});

@endif



	//注册问题下拉
	$.divselect("#divselect","#passwd_quesetion");

    //扫码登录
    $("[ectype='loginSwitch']").on('click',function(){
        if($(this).parents('.login-form').hasClass('module-static')){
            $(this).parents('.login-form').removeClass('module-static').addClass('module-quick');

            $('.login-box .quick-form').show().siblings('.static-form').hide();

@if($app_client)

            qrcode();

@endif

        }else{
            $(this).parents('.login-form').removeClass('module-quick').addClass('module-static');
            $('.login-box .static-form').show().siblings('.quick-form').hide()
        }
    })

@if($app_client)

    //扫码登录 二维码生成
    function qrcode(){
        $.ajax({
            url:'appqrcode/getqrcode',
            type:'post',
            success:function(res){
                $(".qrcode-img img").attr('src',res);
            },
            error:function(res){
                console.log(res)
            }
        })
    }
    var timer = '';
    var getTimer = '';
    function qrcodeInterval(){
        var i = 0;
        timer = setInterval(function(){
            i++;

            if(i > 60){
                $('.qrcode-main').show().siblings('.qrcode-succ').hide();
                $(".qrcode-error").show();
                clearInterval(getTimer);
                return false
            }
        },1000)
    }

    qrcode();
    qrcodeInterval();
    gettingInterval();

    $("[ectype='refreshBtn']").on('click',function(){
        clearInterval(timer);
        qrcode();
        qrcodeInterval();
        gettingInterval();
        $(".qrcode-error").hide();
    })

    function gettingInterval(){
        getTimer = setInterval(function(){
            $.ajax({
                url:'appqrcode/getting',
                type:'post',
                async: false,
                success:function(res){
                    if(res.error == 2){
                        window.location.href="user.php"
                    }else if(res.error == 1){
                        $('.qrcode-main').hide().siblings('.qrcode-succ').show();
                    }else if(res.error == 3){
                        $('.qrcode-main').show().siblings('.qrcode-succ').hide();
                        $(".qrcode-error").show().find('p').text('{{ $lang['login_fail'] }}');
                    }
                },
                error:function(res){
                    clearInterval(getTimer);
                }
            })
        },3000)
    }

@endif

});
</script>
</body>
</html>

