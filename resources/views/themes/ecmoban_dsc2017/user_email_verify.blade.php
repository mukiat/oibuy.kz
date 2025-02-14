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
</head>

<body class="user_email_verify">
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

@if($user_login_logo)
<b style="background:url({{ $login_logo_pic }}) no-repeat;"></b>
@endif

                    </div>
                </div>
                <div class="header-href">
                    <span>{{ $lang['hello'] }}，{{ $info['nick_name'] }}&nbsp;<a href="user.php?act=logout" class="ftx-05">{{ $lang['label_logout'] }}</a></span>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="w w1200">
                <div class="get_pwd_warp">
                    <div class="get_pwd_form">

                        <div class="form form-other">
                            <div class="item item-other">
                                <div class="item-label">&nbsp;</div>
                                <div class="gp-tab">
                                    <ul>
                                        <li class="curr"><i class="iconfont icon-mobile-phone"></i>{{ $lang['email_yanzheng'] }}</li>
                                        <li></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="clear"></div>
                            <div class="gp-content">
                                <div class="gp-warp formPhone" style="display:block;">
                                    <form name="formLogin" action="user.php" method="post" id="checkd_email_code">
                                        <div class="msg_ts">
                                            <div class="error" id="phone_notice"></div>
                                        </div>
                                        <div class="item">
                                            <div class="item-label">{{ $lang['Login_name'] }}：</div>
                                            <div class="item-info item-info-not"><span class="txt-lh mr10">{{ $info['username'] }}</span></div>
                                            <div class="input-tip"><span></span></div>
                                        </div>
                                        <div class="item">
                                            <div class="item-label">{{ $lang['Post'] }}&nbsp;&nbsp;&nbsp;{{ $lang['box'] }}：</div>
                                            <div class="item-info
@if($info['email'])
 item-info-not
@endif
">

@if(!$info['email'])

                                                <input id="userEmail" name="email" type="text" value="" class="text" />

@else

                                                <span class="txt-lh mr10">{{ $info['email'] }}</span>
                                                <input name="email" type="hidden" value="{{ $info['email'] }}" />

@endif

                                            </div>
                                            <div class="input-tip"><span></span></div>
                                        </div>
                                       <div class="item">
                                            <div class="item-label">{{ $lang['comment_captcha'] }}：</div>
                                            <div class="item-info">
                                                <input type="text"  name="code" id="send_code" class="text text-2"  maxlength="6" value="" autocomplete="off"placeholder="{{ $lang['code_number'] }}"/>
                                                <a href="javascript:void(0);"  onclick="sendChangeEmail();" class="sms-btn">{{ $lang['get_verification_code'] }}</a>
                                            </div>
                                            <div class="input-tip"><span></span></div>
                                        </div>

                                        <div class="item item2 item-button">
                                            <input name="act" type="hidden" value="email_send_succeed" />
                                            <input type="button" name="button" value="{{ $lang['email_verify']['button'] }}" class="btn sc-redBg-btn ui-button-lorange">
                                        </div>
                                        <div class="clear"></div>
                                    @csrf </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
	@include('frontend::library/page_footer_flow')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript">
		$(".ui-button-lorange").click(function(){
			var send_code = $("#send_code").val();
			var msg = '';

			if(document.getElementById('userEmail')){
				var email = $("#userEmail").val();
				if (email == '')
				{
					msg += json_languages.verify_email_null + '\n';
				}
				else if (!Utils.isEmail(email))
				{
					msg += json_languages.verify_email_Wrongful + '\n';
				}
			}

			if (send_code == '')
			{
				msg += json_languages.null_captcha_login + '\n';
			}

			else if(send_code.length < 4)
			{
				msg += json_languages.verify_email_code_number + '\n';
			}

			if(msg.length > 0){
				pbDialog(msg,"",0);
			}else{
				Ajax.call( 'ajax_user.php?act=checkd_email_send_code', 'send_code='+send_code, checkd_email_send_code , 'GET', 'JSON');
			}
		});

		function checkd_email_send_code(result){
			if(result == true){
				 $("#checkd_email_code").submit();
			}else{
				pbDialog(json_languages.error_email_login,"",0);
			}
		}

		function sendChangeEmail(){
			var email = '';
			if(document.getElementById('userEmail')){
				email = $("#userEmail").val();
				email = "&email=" + email;
			}

			Ajax.call( 'user.php?act=user_email_send', 'type=1' + email, email_callback , 'GET', 'TEXT', true, true );
		}

		function email_callback(result){
			if( result.replace(/\r\n/g,'') == 'ok' ){
				pbDialog(json_languages.Mailbox_sent,"",0);
			}
		}
    </script>
</body>
</html>
