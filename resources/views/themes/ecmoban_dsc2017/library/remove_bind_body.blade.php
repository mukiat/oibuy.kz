	<div class="items">
		<form name="formUser" action="user.php?act=removeBind_success" method="post" >
			<div class="item">
				<div class="label w120">{{ $lang['label_mobile'] }}：</div>
				<div class="value">
                	<strong id="mobileSpan" class="ftx-un">

@if($user_info['mobile_phone'])

						{{ $user_info['mobile_phone'] }}

@else

                    	<a href="user.php?act=account_safe&type=change_phone" style="font-size:12px; background:#ec5151;border-radius:5px; padding:5px 10px; color:#fff">{{ $lang['Immediately_verify'] }}</a>

@endif

                    </strong>
					<input id="mobile_phone" name="mobile" type="hidden" class="text" value="{{ $user_info['mobile_phone'] }}" />
					<label class="error" id="phone_notice"><i></i></label>
				</div>
			</div>
			<div class="item">
				<div class="label w120">{{ $lang['comment_captcha'] }}：</div>
				<div class="value">
					<input type="text" autocomplete="off" id="mobile_code" class="text" name="mobile_code"/>
					<input name="sms_value" id="sms_value" type="hidden" value="sms_code" />
					<label class="error" id="phone_captcha"><i></i></label>
                    <a href="javascript:sendSms()" id="zphone" class="btn-10 ml10" style="height:28px; line-height:28px;"><s></s>{{ $lang['get_verification_code_user'] }}</a>
				</div>
			</div>
			<div class="item">
				<div class="label w120">&nbsp;</div>
				<div class="value">
					<input id="flag" type="hidden" value="change_password_f" name="flag">
					<input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
					<a href="javascript:void(0);" id="bind_btn" class="btn btn-org" style="float:left;" onclick="removeBind();">{{ $lang['Un_bind'] }}</a>
				</div>
			</div>
		@csrf </form>
	</div>
<script>
 $("#mobile_phone").focus(function(){
	$("#mobile_code").parents(".item").removeClass("mobile_code");
	$("#zphone").attr("onclick","sendSms();");
  });
function removeBind(){

	var mobile_code  = $('#mobile_code').val() ? $('#mobile_code').val() : ''; // 手机验证码
	var msg = "";
	var vid = {{ $vid }};

	// 手机验证码
	if(mobile_code == '')
	{
		$("#phone_captcha").removeClass().addClass("error");
		$("#phone_captcha").html("<i></i>"+"短信验证码不能为空");
		msg += '短信验证码不能为空' + '\n';
	}
	else
	{
		//验证短信是否正确
		$.ajax({
		   cache: false,
		   async: false,
		   type: 'POST',
		   data: { mobile_code: mobile_code },
		   url: "user.php?act=verify_mobilecode&vid="+ vid,
		   success: function (res) {
				var result = eval("("+res+")");
				if (result.error)
				{
					$("#phone_captcha").removeClass().addClass("error");
					$("#phone_captcha").html("<i></i>"+result.message);
					msg += result.message + '\n';

					return false;
				}
				else
				{
					$("#phone_captcha").removeClass().addClass("error");
					$("#phone_captcha").html("<i></i>");
					location.href='user.php?act=value_card';
				}
			}
		  });
	}
	//alert(msg)
	if (msg.length > 0)
	{
		return false;
	}else
	{
		return true;
	}
}
</script>
