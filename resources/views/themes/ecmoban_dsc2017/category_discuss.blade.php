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

<body>
	@include('frontend::library/page_header_common')
	<div class="full-main-n">
        <div class="w w1200 relative">
			@include('frontend::library/ur_here')
			@include('frontend::library/goods_merchants_top')
        </div>
    </div>
    <div class="container">
    	<div class="w w1200">
        	<div class="discuss-warp">
                <div class="discuss-left">
                    <div class="d-title"><h1>{{ $lang['discuss_user'] }}</h1></div>
                    <div class="review-info" >
                        <div class="review-tabs">
                            <a href="javascript:void(0);" class="dis_type curr" rev="0">{{ $lang['all_post'] }}(<em>{{ $all_count }}</em>)<i></i></a>

@if($shop_can_comment > 0)

                            <a href="javascript:void(0);" class="dis_type" rev="4">{{ $lang['sunburn_port'] }}(<em>{{ $s_count }}</em>)<i></i></a>

@endif

                            <a href="javascript:void(0);" class="dis_type" rev="1">{{ $lang['discuss_post'] }}(<em>{{ $t_count }}</em>)<i></i></a>
                            <a href="javascript:void(0);" class="dis_type" rev="2">{{ $lang['interlocution_post'] }}(<em>{{ $w_count }}</em>)<i></i></a>
                            <a href="javascript:void(0);" class="dis_type" rev="3">{{ $lang['circle_post'] }}(<em>{{ $q_count }}</em>)<i></i></a>
                        </div>
                        <div class="discuss-list" id="discuss_list_ECS_COMMENT">
							@include('frontend::library/comments_discuss_list2')
                        </div>
                    </div>
					<form method="post" action="single_sun.php" name="dis_theForm" id="theFrom" enctype="multipart/form-data">
                    <div class="review-form" id="doPost" name="doPost">
                        <div class="r-u-name">
                            <div class="u-ico"><img src="
@if($user_id)

@if($user_info['user_picture'])
{{ $user_info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif

@else
{{ skin('/images/avatar.png') }}
@endif
"></div>
                            <span>{{ $lang['publish_top'] }}</span>
                        </div>
                        <div class="item">
                            <div class="item-label item-label2"><em class="red">*</em>&nbsp;{{ $lang['types'] }}：</div>
                            <div class="item-value">
                                <div class="radio-item">
                                    <input type="radio" checked name="referenceType" class="ui-radio" id="referenceType1" value="1" autocomplete="off">
                                    <label for="referenceType1" class="ui-radio-label">{{ $lang['discuss_post'] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="referenceType" class="ui-radio" id="referenceType2" value="2" autocomplete="off">
                                    <label for="referenceType2" class="ui-radio-label">{{ $lang['interlocution_post'] }}</label>
                                </div>

@if($shop_can_comment > 0)

                                <div class="radio-item">
                                    <input type="radio" name="referenceType" class="ui-radio" id="referenceType3" value="3" autocomplete="off">
                                    <label for="referenceType3" class="ui-radio-label">{{ $lang['circle_post'] }}</label>
                                </div>

@endif

                                <div class="radio-item">
                                    <input type="radio" name="referenceType" class="ui-radio" id="referenceType4" value="4" autocomplete="off">
                                    <label for="referenceType4" class="ui-radio-label">{{ $lang['sunburn_port'] }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item-label"><em class="red">*</em>&nbsp;{{ $lang['message_title'] }}：</div>
                            <div class="item-value">
                                <input type="text" class="text" id="commentTitle" name="commentTitle">
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item-label"><em class="red">*</em>&nbsp;{{ $lang['content'] }}：</div>
                            <div class="item-value">
                                <textarea class="textarea" id="test_content" name="content"></textarea>
                                <div class="form_prompt"></div>
                            </div>
                        </div>

@if($enabled_captcha)

                        <div class="item">
                            <div class="item-label">{{ $lang['comment_captcha'] }}：</div>
                            <div class="item-value">
                            	<div class="captcha_input">
                                    <input type="text" class="text w100" id="captcha" name="captcha">
                                    <img src="captcha_verify.php?captcha=is_common&{{ $rand }}" alt="captcha" class="captcha_img" onClick="this.src='captcha_verify.php?captcha=is_common&'+Math.random()" data-key="captcha_common" />
                                </div>
								<div class="form_prompt"></div>
                            </div>
                        </div>

@endif

                        <div class="item">
                            <div class="item-label">&nbsp;</div>
                            <div class="item-value">
								<input type="hidden" name="act" value="add_discuss" />
								<input type="hidden" name="good_id" value="{{ $goods_id }}" />
								<input type="hidden" name="user_id" id="user_id" value="{{ $user_id }}" />
                                <input type="button" class="btn sc-redBg-btn" ectype="submitBtn" value="{{ $lang['publish'] }}">
                            </div>
                        </div>
                    </div>
					@csrf </form>
                </div>
				@include('frontend::library/discuss_right')
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
<script type="text/javascript" src="{{ asset('js/lib_ecmobanFunc.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
	<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
	<script type="text/javascript">
	$(function(){
		$("*[ectype='submitBtn']").click(function(){
			var sub_Form = $("form[name='dis_theForm']"),
				user_id = sub_Form.find("input[name='user_id']").val(),
				goods_id = sub_Form.find("input[name='good_id']").val();

			//判断用户是否登录
			if(user_id <= 0){
				var back_url = "category_discuss.php?id=" + goods_id;
				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
				return false;
			}else{
				if($("#theFrom").valid()){
                    $("#theFrom").submit();
				}
			}
		});

		$('#theFrom').validate({
			errorPlacement:function(error, element){
				var error_div = element.parents('div.item-value').find('div.form_prompt');
				error_div.html("").append(error);
			},
			ignore:".ignore",
			rules : {
				commentTitle : {
					required : true,
					minlength: 2,
					maxlength: 50
				},
				content:{
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
				commentTitle : {
					required : "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_not'] }}",
					minlength: "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_xz'] }}",
					maxlength: "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_xz'] }}"
				},
				content : {
					required : "<i class='iconfont icon-info-sign'></i> {{ $lang['content_not'] }}"
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

		//晒单贴调整到评论列表
		$("#referenceType4").click(function(){
			location.href = "user_message.php?act=comment_list";
		});
	});
	</script>
</body>
</html>
