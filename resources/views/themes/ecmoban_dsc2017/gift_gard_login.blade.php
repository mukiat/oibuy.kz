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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/gift.css') }}" />
</head>
<body>
@include('frontend::library/page_header_common')
<div class="ecsc-breadcrumb w1200 w">
    @include('frontend::library/ur_here')
</div>
<div class="w1200 w">
    <div class="usBox clearfix">
        <div class="usBox_1 fl">
            <form name="formGift" action="gift_gard.php" method="post" autocomplete="off" id="gift_gard_form">
                <div class="items">
                	<div class="item">
                    	<div class="label">{{ $lang['gift_gard_number'] }}：</div>
                        <div class="value"><input name="gift_card" id="gift_card" type="text" size="20" class="text" /><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<div class="label">{{ $lang['gift_gard_password'] }}：</div>
                        <div class="value"><input name="gift_pwd" id="gift_pwd" type="password" autocomplete="new-password" size="20" class="text"/><div class="form_prompt"></div></div>
                    </div>

@if($enabled_captcha)

                    <div class="item">
                    	<div class="label">{{ $lang['comment_captcha'] }}：</div>
                        <div class="value">
                        	<div class="captcha_input">
                            	<input name="captcha" id="captcha" type="text" size="20" class="text" />
                            	<img src="captcha_verify.php?captcha=is_common&{{ $rand }}" alt="captcha" class="captcha_img" onClick="this.src='captcha_verify.php?captcha=is_common&'+Math.random()" data-key="captcha_common" />
                            </div>
                            <div class="form_prompt"></div>
                        </div>
                    </div>

@endif

                    <div class="item">
                    	<div class="label">&nbsp;</div>
                        <div class="value">
                        	<input type="hidden" name="act" value="check_gift" />
                            <input type="hidden" name="back_act" value="{{ $back_act }}" />
                            <input type="button" value="{{ $lang['submit'] }}" class="us_Submit" ectype="submitBtn"/>
                        </div>
                    </div>
                </div>
            @csrf </form>
        </div>
        <div class="usTxt"><img src="{{ skin('images/gift_gard.png') }}" width="360"/></div>
    </div>
</div>

@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
	$(function(){
		$("*[ectype='submitBtn']").click(function(){
			var user_id = '{{ $user_id }}';

			//判断用户是否登录
			if(user_id > 0){
				if($("#gift_gard_form").valid()){
    			 $("#gift_gard_form").submit();
				}
			}else{
				var back_url = "gift_gard.php";
				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
				return false;
			}
		});

		$('#gift_gard_form').validate({
			errorPlacement:function(error, element){
				var error_div = element.parents('div.value').find('div.form_prompt');
				error_div.html("").append(error);
			},
			rules : {
				gift_card : {
					required : true
				},
				gift_pwd:{
					required : true
				}

@if($enabled_captcha)

				,captcha:{
					required : true,
					maxlength : 4,
					remote : {
						cache: false,
						async:false,
						type:'POST',
						url:'ajax_dialog.php?act=ajax_captcha&seKey='+$("input[name='captcha']").siblings(".captcha_img").data("key"),
						data:{
							captcha:function(){
								return $("input[name='captcha']").val();
							}
						},
						dataFilter:function(data,type){
							if(data == "false"){
								$("input[name='captcha']").siblings(".captcha_img").click();
							}
							return data;
						}
					}
				}

@endif

			},
			messages : {
				gift_card : {
					required : json_languages.gift_gard_number_null
				},
				gift_pwd : {
					required : json_languages.gift_gard_password_null
				}

@if($enabled_captcha)

				,captcha:{
					required : json_languages.common.captcha_not,
					maxlength: json_languages.common.captcha_xz,
					remote : json_languages.common.captcha_cw
				}

@endif

			},
			success:function(label){
				label.removeClass().addClass("succeed").html("<i></i>");
			},
			onkeyup:function(element,event){
				var name = $(element).attr("name");
				if(name == "captcha"){
					//不可去除，当是验证码输入必须失去焦点才可以验证（错误刷新验证码）
					return true;
				}else{
					$(element).valid();
				}
			}
		});
	});
</script>
</body>
</html>


