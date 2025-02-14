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

<link rel="stylesheet" type="application/rss+xml" title="RSS|{{ $page_title }}" href="{{ $feed_url }}" />
@include('frontend::library/js_languages_new')
<link rel="stylesheet" type="text/css" href="{{ skin('css/user.css') }}">
</head>

<body class="third_body">
<div class="header">
    <div class="logo-con w w1200">
        <div class="logo-title">{{ $lang['Bind_Account'] }}</div>
    </div>
</div>
<div class="third_container w w1200">
    <div class="main clearfix">
        <div class="register-tabNav clearfix">
            <div class="r-tab r-tab-cur">
                <span class="icon i-bind"></span>
                <span>{{ $lang['existing'] }}{{ $dwt_shop_name }}{{ $lang['Bind_one'] }}</span>
            </div>
            <div class="r-tab">
                <span class="icon i-reg"></span>
                <span>{{ $lang['No_existing'] }}{{ $dwt_shop_name }}{{ $lang['Bind_one'] }}</span>
            </div>
        </div>
        <div class="reg-tab-con">
            <div class="r-tabCon bind-login-content">
                <div class="account-login-panle">
                    <div class="wellcome-tip">
                        <img src="
@if($info['figureurl_qq_2'])
{{ $info['figureurl_qq_2'] }}
@else
{{ $info['img'] }}
@endif
" width="28" height="28">
                        Hi, {{ $info['name'] }} {{ $lang['Welcome_to'] }}{{ $dwt_shop_name }}
                    </div>
                    <form class="login-form" name="loginForm" method="post" action="user.php?act=oath_register">
                        <div class="login-error-container">
                            <div id="login-error" class="login-error">
                                <span id="login-server-error" class="error"></span>
                            </div>
                        </div>
                        <div class="form-item" id="form-item-account">
                            <label>{{ $lang['user_number_bind'] }}</label>
                            <input type="text" name="username" ectype="input" placeholder="{{ $lang['label_username'] }}" autocomplete="off" />
                            <i class="i-clear"></i>
                        </div>
                        <div class="input-tip"><span id="error-username"></span></div>
                        <div class="form-item">
                            <label>{{ $lang['user_password_bind'] }}</label>
                            <input type="password" style="display:none" autocomplete="off" />
                            <input type="password" name="password" ectype="input" placeholder="{{ $lang['label_password'] }}" autocomplete="off" />
                            <i class="i-clear"></i>
                        </div>
                        <div class="input-tip"><span></span></div>

@if($login_captcha)

                        <div class="form-item form-item-authcode">
                            <label>{{ $lang['Code_bind'] }}</label>
                            <input type="text" name="captcha" ectype="input" placeholder="{{ $lang['captcha_empty'] }}" autocomplete="off" />
                            <img src="captcha_verify.php?captcha=is_login&{{ $rand }}" class="login_captcha" alt="captcha" id="captcha_img" style="vertical-align: middle;cursor: pointer;" onClick="this.src='captcha_verify.php?captcha=is_login&'+Math.random()" width="119" height="52" />
                        </div>

@endif

                        <div class="forget"><a href="user.php?act=get_password" target="_blank">{{ $lang['forget_password'] }}</a></div>
                        <input name="bind_type" type="hidden" value="1" />

                        <input name="type" type="hidden" value="{{ $type }}" />
                        <button type="button" class="btn-register" id="form-bound">{{ $lang['bind_now'] }}</button>
                    @csrf </form>
                </div>
            </div>
            <div class="r-tabCon reg-form">
                <div class="wellcome-tip">
                    <img src="
@if($info['figureurl_qq_2'])
{{ $info['figureurl_qq_2'] }}
@else
{{ $info['img'] }}
@endif
" width="28" height="28"> Hi, {{ $info['name'] }} {{ $lang['Welcome_to'] }}{{ $dwt_shop_name }}
                </div>
                <script type="text/javascript" src="{{ asset('plugins/sms/sms.js') }}"></script>
                <form class="register-form" name="formUser" method="post" action="user.php?act=oath_register">
                    <div class="login-error-container">
                        <div id="login-error" class="login-error">
                            <span id="login-server-error" class="error"></span>
                        </div>
                    </div>
                    <div class="form-item" id="form-item-account">
                        <label>{{ $lang['username_bind'] }}</label>
                        <input type="text" name="username" placeholder="{{ $lang['bind_login_one'] }}" ectype="input" data-valid="username" autocomplete="off" data-default="<i class='i-def'></i>{{ $lang['bind_login_four'] }}" />
                    	<i class="i-status"></i>
                        <div class="suggest-container user-suggest">
                            <li class="disable"><div class="value"><i class="i-error1"></i><span>{{ $lang['msg_un_registered'] }}</span></div></li>
                        </div>
                    </div>
                    <div class="input-tip"><span></span></div>
                    <div class="form-item">
                        <label>{{ $lang['bind_password'] }}</label>
                        <input type="password" style="display:none" autocomplete="off" />
                        <input type="password" name="password" data-valid="password" ectype="input" data-default="<i class='i-def'></i>{{ $lang['bind_login_two'] }}" placeholder="{{ $lang['bind_login_three'] }}" autocomplete="off" />
                    	<i class="i-status"></i>
                    </div>
                    <div class="input-tip"><span></span></div>
                    <div class="form-item">
                        <label>{{ $lang['bind_password2'] }}</label>
                        <input type="password" style="display:none" autocomplete="off" />
                        <input type="password" name="password2" data-valid="password2" ectype="input" data-default="<i class='i-def'></i>{{ $lang['bind_password2_one'] }}" placeholder="{{ $lang['bind_password2_one'] }}" autocomplete="off" />
                    	<i class="i-status"></i>
                    </div>
                    <div class="input-tip"><span></span></div>
                    <div class="form-item">
                        <label>{{ $lang['bind_phone'] }}</label>
                        <input type="text" name="mobile_phone" id="mobile_phone" ectype="input" data-valid="tel" data-default="<i class='i-def'></i>{{ $lang['bind_phone_one'] }}" placeholder="{{ $lang['bind_phone_two'] }}" autocomplete="off" />
                    	<i class="i-status"></i>
                    </div>
                    <div class="input-tip"><span class="error"></span></div>

@if($register_captcha)

                    <div class="form-item form-item-authcode">
                        <label>{{ $lang['Code_bind'] }}</label>
                        <input type="text" name="captcha" id="captcha" ectype="input" data-valid="captcha" data-default="<i class='i-def'></i>{{ $lang['Code_bind_one'] }}" placeholder="{{ $lang['captcha_empty'] }}" autocomplete="off" />
                        <img src="captcha_verify.php?captcha=is_register_phone&{{ $rand }}" alt="captcha" id="authcode_img" class="is_register_email" style="vertical-align: middle;cursor: pointer;" onClick="this.src='captcha_verify.php?captcha=is_register_phone&'+Math.random()" width="119" height="52" />
                    </div>
                    <div class="input-tip"><span></span></div>
                    <input name="is_captcha" type="hidden" value="1" />

@else

                    <input name="is_captcha" type="hidden" value="0" />

@endif


@if($sms_register)

                    <div class="form-item form-item-telyzm">
                        <label>{{ $lang['bindMobile_code'] }}</label>
                        <input type="text" name="mobile_code" data-valid="tel_code" ectype="input" data-default="<i class='i-def'></i>{{ $lang['bindMobile_code_null'] }}" placeholder="{{ $lang['bindMobile_code_null'] }}" autocomplete="off" />
                        <a id="zphone" class="btn-phonecode">{{ $lang['get_bindMobile_code'] }}</a>
                    </div>
                    <div class="input-tip"><span></span></div>
                    <input name="is_code" type="hidden" value="1" />

@else

                    <input name="is_code" type="hidden" value="0" />

@endif

                    <div class="form-agreen">
                        <div><input type="checkbox" name="agreen" checked="">{{ $lang['agreed_bind'] }}<a href="#">《{{ $dwt_shop_name }}{{ $lang['protocol_bind'] }}》</a></div>
                        <div class="input-tip">
                            <span></span>
                        </div>
                    </div>
                    <div>
                    	<input type="hidden" name="flag" id="flag" value='register' />
                        <input type="hidden" name="seccode" id="seccode" value="{{ $sms_security_code }}" />
                        <input name="bind_type" type="hidden" value="2" />

                        <input name="type" type="hidden" value="{{ $type }}" />
                    	<button type="button" class="btn-register" id="form-register">{{ $lang['reg_now'] }}</button>
                    </div>
                @csrf </form>
            </div>
        </div>
    </div>
</div>
@include('frontend::library/page_footer_flow')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/Validform.js') }}"></script>
<script type="text/javascript">
$(".register-tabNav .r-tab").click(function(){
	var index = $(this).index();
	$(this).addClass("r-tab-cur").siblings().removeClass("r-tab-cur");
	$(".reg-tab-con").find(".r-tabCon").eq(index).show().siblings().hide();
});

$.Validform(".register-form",".input-tip",'#form-register');

$("#form-bound").click(function(){
	var form = $(".login-form");
	var loginerror = form.find(".login-error");
	var form_item = form.find(".form-item");
	var fold = true;
	var a;
	var b = 0;

	var username = $("form[name='loginForm'] :input[name='username']").val();
	var password = $("form[name='loginForm'] :input[name='password']").val();
	var captcha = $("form[name='loginForm'] :input[name='captcha']").val();

	for(var i=0;i<form_item.length;i++){
		var val = form_item.find("*[ectype='input']").eq(i).val();
		if(val == ""){
			loginerror.show();
			form_item.eq(i).addClass("form-item-error");
			if(fold){
				if(i==0){
					loginerror.find(".error").html('<i class="i-error" style="margin-top: 3px;"></i><span>'+json_languages.user_name_bind+'</span>');
					fold = false;
				}else if(i == 1){
					loginerror.find(".error").html('<i class="i-error" style="margin-top: 3px;"></i><span>'+json_languages.password_null+'</span>');
					fold = false;
				}else{

@if($register_captcha)

					loginerror.find(".error").html('<i class="i-error" style="margin-top: 3px;"></i><span>'+json_languages.null_captcha_login+'</span>');
					fold = false;

@endif

				}
			}
		}else{
			form_item.eq(i).removeClass("form-item-error");
			b++;
		}
	}


@if($login_captcha)

	a = 3;

@else

	a = 2;

@endif


	if(b == a){
		Ajax.call('ajax_user.php?act=is_user', 'username=' + username + '&password=' + password, function(data){
			if (data.result == "false")
			{
				loginerror.show();
				loginerror.find(".error").html('<i class="i-error" style="margin-top: 3px;"></i><span>'+json_languages.user_namepassword_bind+'</span>');
			}else{

@if($login_captcha)

				Ajax.call('ajax_user.php?act=is_login_captcha', 'captcha=' + captcha, function(res){
					if (res.result == "false"){
						loginerror.show();
						loginerror.find(".error").html('<i class="i-error" style="margin-top: 3px;"></i><span>' + res.message + '</span>');
					}else{
						$(".login-form").submit();
					}
				} , 'GET', 'JSON', true, true);

@else

				$(".login-form").submit();

@endif

			}
		} , 'GET', 'JSON', true, true);
	}
});
</script>
</body>
</html>
