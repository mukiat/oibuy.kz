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

<body class="bg-ligtGary">
    @include('frontend::library/page_header_common')
    <div class="content">
        <div class="feedback-main">
            <div class="w w1200">
                <h2 class="feedback-title">{{ $lang['feedback'] }}</h2>
                <div class="feedback-tip clearfix">
                    <div class="avatar">
                        <img src="{{ skin('images/admin_avatar.png') }}" alt="">
                        <p>{{ $lang['admin'] }}</p>
                    </div>
                    <div class="message">
                        <p>{{ $lang['message_user'] }}： </p>
                        <p>{{ $lang['message_reply'] }}</p>
                    </div>
                </div>
                <div class="feedback-write feedback-tip-two clearfix">
                	@include('frontend::library/message_list')
                </div>
				<div class="feedback-write mt30 clearfix">
					<div class="ratelist-content">
					@include('frontend::library/pages')
					</div>
				</div>
                <div class="feedback-write clearfix">
                    <div class="avatar">
                        <img src="
@if($user_id)

@if($user_info['user_picture'])
{{ $user_info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif

@else
{{ skin('/images/avatar.png') }}
@endif
" alt="">
                    </div>
                    <div class="message">
                        <form action="message.php" method="post" name="formMsg" class="feedback-form">
                            <div class="form-row">
                                <div class="ff-hd"><span class="red">*</span>{{ $lang['message_board_type'] }}</div>
                                <div class="ff-bd clearfix">

@foreach($lang['message_type'] as $key => $item)


@if($key <= 4)

                                    <div class="radio-item">
                                        <input type="radio" name="msg_type" id="msg_type-{{ $key }}" class="ui-radio"
@if($loop->first)
checked="checked"
@endif
 value="{{ $key }}">
                                        <label for="msg_type-{{ $key }}" class="ui-radio-label">{{ $item }}</label>
                                    </div>

@endif


@endforeach

                                </div>
                            </div>
                            <div class="form-row">
                                <div class="ff-hd"><span class="red">*</span>{{ $lang['message_title'] }}</div>
                                <div class="ff-bd clearfix">
                                    <input type="text" name="msg_title" class="text" placeholder="">
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="ff-hd"><span class="red">*</span>{{ $lang['message_notic'] }}：</div>
                                <div class="ff-bd clearfix">
                                    <textarea name="msg_content" id="" cols="30" rows="10" class="textarea"></textarea>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="ff-hd"><span class="red">*</span>{{ $lang['message_tel'] }}：</div>
                                <div class="ff-bd clearfix">
                                    <input type="text" name="user_email" class="text" placeholder="{{ $lang['con_email'] }}">
                                    <div class="form_prompt"></div>
                                </div>
                            </div>

@if($enabled_captcha)

                            <div class="form-row">
                                <div class="ff-hd"><span class="red">*</span>{{ $lang['comment_captcha'] }}：</div>
                                <div class="ff-bd clearfix">
                                    <div class="captcha_input">
                                        <input type="text" class="text w100" id="captcha" name="captcha">
                                        <img src="captcha_verify.php?captcha=is_common&{{ $rand }}" alt="captcha" class="captcha_img" onClick="this.src='captcha_verify.php?captcha=is_common&'+Math.random()" data-key="captcha_common" />
                                    </div>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>

@endif

                            <div class="form-row">
                            	<input type="submit" name="submit" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_goods'] }}" id="btnSubmit" />
                                <!--<a href="javascript:$('form[name=formMsg]').submit();" class="sc-btn sc-redBg-btn">提交</a>-->
                            	<input type="hidden" name="act" value="act_add_message" />
                            </div>
                        @csrf </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
	{{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
	<script type="text/javascript">

@foreach($lang['message_board_js'] as $key => $item)

	var {{ $key }} = "{!! $item !!}";

@endforeach



	/**
	 * 提交留言信息
	*/
	$(function(){
		$("#btnSubmit").on("click",function(){
			//判断用户是否登录
			if(user_id <= 0){

@if($rewrite)

				var back_url = "message.html";

@else

				var back_url = "message.php";

@endif

				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
				return false;
			}else{
				if($("form[name='formMsg']").valid()){
					$("form[name='formMsg']").submit();
				}
			}
		});

		$("form[name='formMsg']").validate({
			errorPlacement:function(error, element){
				var error_div = element.parents('div.form-row').find('div.form_prompt');
				error_div.html("").append(error);
			},
			ignore:".ignore",
			rules : {
				msg_title : {
					required : true,
					minlength: 2,
					maxlength: 50
				},
				msg_content:{
					required : true
				},
				user_email:{
					required : true,
					email : true
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
				msg_title : {
					required : "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_not'] }}",
					minlength: "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_xz'] }}",
					maxlength: "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_xz'] }}"
				},
				msg_content : {
					required : "<i class='iconfont icon-info-sign'></i> {{ $lang['content_not'] }}"
				},
				user_email:{
					required : "<i class='iconfont icon-info-sign'></i> " + msg_empty_email,
					email : "<i class='iconfont icon-info-sign'></i> " + msg_error_email
				}

@if($enabled_captcha)

				,captcha:{
					required : "<i class='iconfont icon-info-sign'></i> " + json_languages.common.captcha_not,
					maxlength: "<i class='iconfont icon-info-sign'></i> " + json_languages.common.captcha_xz,
					remote : "<i class='iconfont icon-info-sign'></i> " + json_languages.common.captcha_cw
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

	/**
	 * 首页分类树 头部
	 */
	function cat_tree_2(){
	  Ajax.call('message.php', 'act=cat_tree_two', cat_tree_2Response, 'GET', 'JSON');
	}

	/**
	 * 接收返回的信息
	 */
	function cat_tree_2Response(res)
	{
		$('.category_tree_2').html(res.content);
	}
	</script>
</body>
</html>
