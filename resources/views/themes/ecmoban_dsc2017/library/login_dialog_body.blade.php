<div class="login-wrap">

    <div class="login-form">

@if($website_list)

    	<div class="coagent">
            <div class="tit"><h3>{{ $lang['Third_party_Lgion'] }}</h3><span></span></div>
            <div class="coagent-warp">

@foreach($website_list as $website)

                    <a href="oauth?type={{ $website['type'] }}@if($back_act != '')&back_url={{ $back_act }}@endif" class="{{ $website['type'] }}"><b class="third-party-icon {{ $website['type'] }}-icon"></b></a>

@endforeach

            </div>
        </div>

@endif

        <div class="login-box">
            <div class="tit"><h3>{{ $lang['account_login'] }}</h3><span></span></div>
            <div class="msg-wrap"></div>
            <div class="form">
            	<form name="formLogin" action="{{ url('ajax_user.php') }}" method="post" onSubmit="userLogin();return false;">
                	<div class="item">
                        <div class="item-info">
                            <i class="iconfont icon-name"></i>
                            <input type="text" id="loginname" name="username" class="text" value="" placeholder="{{ $lang['label_username'] }}" />
                        </div>
                    </div>
                    <div class="item">
                        <div class="item-info">
                            <i class="iconfont icon-password"></i>
                            <input type="password" style="display:none" autocomplete="off"/>
                            <input type="password" id="nloginpwd" name="password" autocomplete="off" value="" class="text" placeholder="{{ $lang['label_password'] }}" />
                        </div>
                    </div>

@if($enabled_captcha)

                    <div class="item">
                        <div class="item-info">
                            <i class="iconfont icon-security"></i>
                            <input type="text" id="captcha" name="captcha" value="" class="text text-2 fl" placeholder="{{ $lang['comment_captcha'] }}" />
                            <img src="{{ url('/') }}/captcha_verify.php?captcha=is_login&{{ $rand }}" alt="captcha" class="captcha_img fl" onClick="this.src='{{ url("/") }}/captcha_verify.php?captcha=is_login&'+Math.random()" />
                        </div>
                    </div>

@endif

                    <div class="item">
                        <input id="remember" name="remember" type="checkbox" class="ui-checkbox">
                        <label for="remember" class="ui-label">{{ $user_lang['remember'] }}</label>
                    </div>
                    <div class="item item-button">
                    	<input type="hidden" name="dsc_token" value="{{ $dsc_token ?? 0 }}" />
                        <input type="hidden" name="act" value="act_login" />
                        <input type="hidden" name="back_act" value="{{ $back_act }}" />
                        <input type="submit" name="submit" value="{{ $lang['signin_now'] }}" class="btn sc-redBg-btn" />
                    </div>
                    <div class="lie">
                    	<a href="user.php?act=get_password" class="notpwd gary fl" target="_blank">{{ $lang['passportforgot_password'] }}</a>

@if($shop_reg_closed != 1)
<a href="user.php?act=register" class="notpwd red fr" target="_blank">{{ $lang['Free_registration'] }}</a>
@endif

                    </div>
                @csrf </form>
            </div>
    	</div>
    </div>
    <script src="{{ asset('/js/base64.min.js') }}"></script>
    <script type="text/javascript">

@foreach($user_lang['passport_js'] as $k => $lang)

	var {{ $k }}="{{ $lang }}";

@endforeach

		/* *
		 * 会员登录
		*/
		function userLogin()
		{
			var frm = $("form[name='formLogin']");
			var username = frm.find("input[name='username']");
			var password = frm.find("input[name='password']");
			var captcha = frm.find("input[name='captcha']");
			var dsc_token = frm.find("input[name='dsc_token']");
			var error = frm.find(".msg-error");
			var msg = '';

			if(username.val()==""){
				error.show();
				username.parents(".item").addClass("item-error");
				msg += username_empty;
				showMesInfo(msg);
				return false;
			}

			if(password.val()==""){
				error.show();
				password.parents(".item").addClass("item-error");
				msg += password_empty;
				showMesInfo(msg);
				return false;
			}

			if(captcha.val()==""){
				error.show();
				captcha.parents(".item").addClass("item-error");
				msg += captcha_empty;
				showMesInfo(msg);
				return false;
			}
			var back_act=frm.find("input[name='back_act']").val();
			var base64password = Base64.encode(password.val());


@if($is_jsonp)

			Ajax.call( 'crossDomain?act=act_login', 'username=' + username.val()+'&pwcode='+base64password+'&dsc_token='+dsc_token.val()+'&captcha='+captcha.val()+'&back_act='+back_act, return_login , 'POST', 'JSON');

@else

			Ajax.call( 'ajax_user.php?act=act_login', 'username=' + username.val()+'&pwcode='+base64password+'&dsc_token='+dsc_token.val()+'&captcha='+captcha.val()+'&back_act='+back_act, return_login , 'POST', 'JSON');

@endif

		}

		function return_login(result)
		{
			if(result.error > 0)
			{
				showMesInfo(result.message);
				$('.captcha_img').click();
			}
			else
			{
				if(result.ucdata){
					$("body").append(result.ucdata)
				}
				location.href=result.url;
			}
		}

		function showMesInfo(msg) {
			$('.login-wrap .msg-wrap').empty();
			var info = '<div class="msg-error"><b></b>' + msg + '</div>';
			$('.login-wrap .msg-wrap').append(info);
		}
	</script>
</div>
