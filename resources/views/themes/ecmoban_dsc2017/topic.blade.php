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
<link href="{{ skin('css/preview.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ skin('css/color.css') }}" rel="stylesheet" type="text/css" >
</head>

<body class="topic_visual_body">
@include('frontend::library/page_header_common')
<div class="shop-list-main" ectype="homeWrap">
    {!! $pc_page['out'] !!}

    <div class="lift lift-mode-one lift-hide" ectype="lift" data-type="one" style="z-index:100001">
        <div class="lift-list" ectype="liftList">
        </div>
    </div>
</div>
<input name="warehouse_id" type="hidden" value="{{ $warehouse_id }}">
<input name="area_id" type="hidden" value="{{ $area_id }}">
<input name="merchantId" type="hidden" value="{{ $topic['user_id'] ?? 0 }}">
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
<script type="text/javascript">
	var slideType = $("*[data-mode='lunbo']").find("*[data-type='range']").data("slide");
	var length = $(".shop_banner .bd").find("li").length;
	if(slideType == "roll"){
		slideType = "left";
		$(".shop_banner .bd").find("li").show();
	}

	if(length>1){
		$(".shop_banner").slide({titCell:".hd ul",mainCell:".bd ul",effect:slideType,interTime:5000,delayTime:500,autoPlay:true,autoPage:true,trigger:"click",endFun:function(i,c,s){
			$(window).resize(function(){
				var width = $(window).width();
				s.find(".bd li").css("width",width);
			});
		}});
	}else{
		$(".shop_banner .hd").hide();
	}

	var adv_slideType = $("*[data-mode='advImg1']").find("*[data-type='range']").data("slide");
	var adv_length = $(".adv_module .bd").find("li").length;

	if(adv_slideType == "roll"){
		adv_slideType = "left";
		$(".adv_module .bd").find("li").show();
	}

	if(adv_length>1){
		$(".adv_module").slide({titCell:".hd ul",mainCell:".bd ul",effect:adv_slideType,interTime:5000,delayTime:500,autoPlay:true,autoPage:true,trigger:"click"});
	}else{
		$(".adv_module .hd").hide();
	}

    //楼层二级分类商品切换
	$("*[ectype='floorItem']").slide({titCell:".hd-tags li",mainCell:".floor-tabs-content",titOnClassName:"current"});

	$("*[ectype='floorItem']").slide({titCell:".floor-nav li",mainCell:".floor-tabs-content",titOnClassName:"current"});

	$("*[ectype='floorItem']").slide({titCell:".tt_item_tab li",mainCell:".floor-tabs-content",titOnClassName:"current"});

	//第五套楼层模板
	$(".floor-fd-slide").slide({mainCell:".bd ul",effect:"left",autoPlay:false,autoPage:true,vis:4,scroll:1});

	//第六套楼层模板
	$(".floor-brand").slide({mainCell:".fb-bd ul",effect:"left",pnLoop:true,autoPlay:false,autoPage:true,vis:3,scroll:1,prevCell:".fs_prev",nextCell:".fs_next"});

	//楼层轮播图广告
	$("*[data-purebox='homeFloor']").each(function(index, element) {
		var f_slide_length = $(this).find(".floor-left-slide .bd li").length;
		if(f_slide_length > 1){
			$(element).find(".floor-left-slide").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",interTime:3500,delayTime:500,autoPlay:true,autoPage:true});
		}else{
			$(element).find(".floor-left-slide .hd").hide();
		}
	});

	$(function(){
		//重新加载商品模块
		$("[data-mode='floor']").each(function(){
			var _this = $(this);
			var goods_ids = _this.data("goodsid");
			var warehouse_id = $("input[name='warehouse_id']").val();
			var area_id = $("input[name='area_id']").val();
			if(goods_ids){
				 Ajax.call('ajax_dialog.php?act=getGuessYouLike', 'goods_ids=' + goods_ids + "&warehouse_id=" + warehouse_id + "&area_id=" + area_id + "&type=topic", function(data){
					if(data.content){
						_this.find(".view ul").html(data.content);
					}
				 } , 'POST', 'JSON');
			}
		})
		$("li[ectype='floor_cat_content'].current").each(function(){
			get_homefloor_cat_content(this);
		});

		$("[ectype='identi_floorgoods'].current").each(function(){
			get_homefloor_cat_content(this);
		});

		$.catetopLift();
	});

	//楼层左侧栏悬浮框
	readyLoad();
</script>
</body>
</html>
