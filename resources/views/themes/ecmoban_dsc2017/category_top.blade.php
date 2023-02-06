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

<body class='
@if($cate_info['top_style_tpl'] == 1)
catetop-cloth
@elseif ($cate_info['top_style_tpl'] == 2)
catetop-jiadian
@elseif ($cate_info['top_style_tpl'] == 3)
catetop-food
@else
catetop-default
@endif
'>
	@include('frontend::library/page_header_category_top')

@if($cate_info['top_style_tpl'] == 1)

	@include('frontend::library/top_style_tpl_1')

@elseif ($cate_info['top_style_tpl'] == 2)

	@include('frontend::library/top_style_tpl_2')

@elseif ($cate_info['top_style_tpl'] == 3)

	@include('frontend::library/top_style_tpl_3')

@else

	@include('frontend::library/top_style_tpl_0')

@endif

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
	<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
	<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>

    <script type="text/javascript">
	$(function(){
		//顶级分类页模板id
		//tpl==0 默认模板、tpl==1 女装模板、tpl==2 家电模板、tpl==3 食品模板
		var tpl = $("input[name='tpl']").val();
		var length = $(".catetop-banner .bd").find("li").length;
		//轮播图
		if(length>1){
			if(tpl == 1){
				$(".catetop-banner").slide({titCell:".cloth-hd ul",mainCell:".bd ul",effect:"fold",interTime:3500,delayTime:500,autoPlay:true,autoPage:true,trigger:"mouseover"});
			}else if(tpl == 3){
				$(".catetop-banner").slide({titCell:".food-hd ul",mainCell:".bd ul",effect:"fold",interTime:3500,delayTime:500,autoPlay:true,autoPage:true,trigger:"mouseover"});
			}else{
				$(".catetop-banner").slide({titCell:".hd ul",mainCell:".bd ul",effect:"fold",interTime:3500,delayTime:500,autoPlay:true,autoPage:true,trigger:"mouseover"});
			}
		}else{
			$(".catetop-banner .hd").hide();
		}

		if(tpl == 1){
			//女装模板 精品大牌
			var length2 = $(".selectbrand-slide .bd").find("li").length;
			if(length2>5){
				$(".selectbrand-slide").slide({mainCell:".bd ul",titCell:".hd ul",effect:"left",pnLoop: false,vis: 5,scroll: 5,autoPage:"<li></li>"});
				$(".selectbrand-slide .prev,.selectbrand-slide .next").show();
			}else{
				$(".selectbrand-slide .prev,.selectbrand-slide .next").hide();
			}
		}else if(tpl == 2){
			$(".hotrecommend").slide({hd:".hr-slide-hd ul",effect:"fold"});
		}else if(tpl == 0){
			$(".toprank").slide({effect:"fold",titCell:".hd ul li"});
			$(".catetop-brand .brand-slide").slide({mainCell: '.bs-bd ul',effect: 'left',vis: 10,scroll: 10,autoPage: true});
			$(".cat-tab-main").slide({mainCell: '.categorys-items',effect: 'left',vis: 10,scroll: 10,autoPage: true});

			var hoverTimer, outTimer,hoverTimer2;
			$(document).on('mouseenter',".cat-tab-main .categorys-item",function(){
				clearTimeout(outTimer);
				var $this = $(this);
				hoverTimer = setTimeout(function(){
					$(".categorys-items-three").find(".categorys-items-layer").eq($this['index']()).show()
				},10);
			});

			$(document).on('mouseleave',".cat-tab-main .categorys-item",function(){
				clearTimeout(hoverTimer);
				var $this = $(this);
				outTimer = setTimeout(function(){
					$(".categorys-items-three").find(".categorys-items-layer").eq($this['index']()).hide()
				},10);
			});
			$(document).on('mouseenter',".categorys-items-layer",function(){
				clearTimeout(outTimer);
				hoverTimer2 = setTimeout(function(){
					$(this).show();
				});
			});
			$(document).on('mouseleave',".categorys-items-layer",function(){
				$(this).hide();
			});

			$.catetopLift();

			if($("input[name='history']").val() == 0){
				$(".lift-history").hide();
			}else{
				$(".lift-history").show();
			}
		}

		//随手购
		if($(".atwillgo-slide .bd").find("li").length > 6){
			$(".atwillgo-slide").slide({mainCell:".bd ul",titCell:".hd ul",effect:"left",pnLoop:false,vis: 6,scroll: 6,autoPage:"<li></li>"});
		}else{
			$(".atwillgo-slide").find(".prev,.next").hide();
		}

		//楼层异步加载封装函数调用
		if(tpl != 0){
			$.catTopLoad(tpl);
		}
	});
    </script>
</body>
</html>
