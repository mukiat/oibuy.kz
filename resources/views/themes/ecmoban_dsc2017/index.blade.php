<!doctype html>
<html lang="zh-Hans">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="renderer" content="webkit">
    <meta name="application-name" content="{{ APPNAME . ' ' .  VERSION }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="{{ config('shop.shop_keywords') }}"/>
    <meta name="description" content="{{ config('shop.shop_desc') }}"/>

    <title>{{ $page_title }}</title>

    <link rel="shortcut icon" href="favicon.ico" />
    @include('frontend::library/js_languages_new')
</head>
<body>
	@include('frontend::library/page_header_common')
    <div class="content" ectype="lazyDscWarp">
    	<div class="banner home-banner">
        	<div class="bd">
				@include('frontend::library/index_ad')
            </div>
            <div class="hd">
            	<ul></ul>
            </div>
            <div class="vip-outcon">
            	{{-- DSC 提醒您：动态载入index_user_info.lbi，显示首页分类小广告 --}}
{!! insert_index_user_info() !!}
            </div>
        </div>
        <div class="channel-content" ectype="channel">
        {{-- DSC 提醒您：动态载入seckill_goods_list.lbi，显示首页分类小广告 --}}
{!! insert_index_seckill_goods() !!}
		{{-- DSC 提醒您：动态载入index_ad_cat.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $rec_cat]) !!}
		@include('frontend::library/index_brand_ad')
        </div>
        <div class="floor-content" ectype="goods_cat_level">

@include('frontend::library/cat_goods')
@include('frontend::library/cat_goods')
@include('frontend::library/cat_goods')
@include('frontend::library/cat_goods')

		</div>
        <div class="floor-loading" ectype="floor-loading"><div class="floor-loading-warp"><img src="{{ skin('images/load/loading.gif') }}"></div></div>
        <div class="other-content">
            {{-- DSC 提醒您：动态载入expert_field.lbi，显示首页达人专区小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $expert_field]) !!}
            {{-- DSC 提醒您：动态载入recommend_merchants.lbi，显示首页推荐店铺小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $recommend_merchants]) !!}
            @include('frontend::library/guess_you_like')
        </div>


        <div class="lift lift-mode-{{ $floor_nav_type ?? one }} lift-hide" ectype="lift" data-type="{{ $floor_nav_type ?? one }}" style="z-index:100001">
            <div class="lift-list" ectype="liftList">
                <div class="lift-item lift-h-seckill lift-item-first" ectype="liftItem" data-target="#h-seckill"><span>{{ $lang['lift_seckill'] }}</span><i class="lift-arrow"></i></div>
                <div class="lift-item lift-h-need lift-item-current" ectype="liftItem" data-target="#h-need"><span>{{ $lang['lift_need'] }}</span><i class="lift-arrow"></i></div>
                <div class="lift-item" ectype="liftItem" data-target="#h-brand"><span>{{ $lang['lift_brand'] }}</span><i class="lift-arrow"></i></div>

@foreach($floor_data as $key => $data)

                <div class="lift-item lift-floor-item" ectype="liftItem"><span>{{ $data['name'] }}</span><i class="lift-arrow"></i></div>

@endforeach

                <div class="lift-item lift-master" ectype="liftItem" data-target="#master"><span>{{ $lang['lift_master'] }}</span><i class="lift-arrow"></i></div>
                <div class="lift-item lift-storeRec" ectype="liftItem" data-target="#storeRec"><span>{{ $lang['lift_storeRec'] }}</span><i class="lift-arrow"></i></div>
                <div class="lift-item" ectype="liftItem" data-target="#guessYouLike"><span>{{ $lang['lift_guessYouLike'] }}</span><i class="lift-arrow"></i></div>
                <div class="lift-item lift-item-top" ectype="liftItem">
@if($floor_nav_type == "one")
<i class="iconfont icon-returntop"></i>
@else
TOP<i class="iconfont icon-top-alt"></i>
@endif
</div>
            </div>
        </div>

        <div class="attached-search-container" ectype="suspColumn">
            <div class="w w1200">
                <div class="categorys site-mast">
                    <div class="categorys-type"><a href="{{ $url_categoryall }}" target="_blank">{{ $lang['all_goods_cat'] }}</a></div>
                    <div class="categorys-tab-content">
                        {!! insert_category_tree_nav(['cat_model' => $nav_cat_model, 'cat_num' => $nav_cat_num]) !!}
                    </div>
                </div>
                <div class="mall-search">
                   <div class="dsc-search">
                        <div class="form">
                            <form id="searchForm" name="searchForm" method="post" action="search.php" onSubmit="return checkSearchForm(this)" class="search-form">
                                <input autocomplete="off" name="keywords" type="text" id="keyword2" value="
@if($search_keywords)
{{ $search_keywords }}
@else
{!! insert_rand_keyword() !!}
@endif
" class="search-text"/>

                                <input type="hidden" value="0" name="search_goods_id" />

                                <input type="hidden" name="store_search_cmt" value="{{ $search_type ?? 0 }}">
                                <button type="submit" class="button button-goods" onclick="checkstore_search_cmt(0)">{{ $lang['serch_goods'] }}</button>
                                <button type="submit" class="button button-store" onclick="checkstore_search_cmt(1)">{{ $lang['serch_shop'] }}</button>
                            @csrf </form>
                        </div>
                    </div>
                </div>
                <div class="suspend-login">
                    {{-- DSC 提醒您：动态载入index_suspend_info.lbi，显示首页首页悬浮登录入口 --}}
{!! insert_index_suspend_info() !!}
                </div>
                <div class="shopCart" id="ECS_CARTINFO">
                    {{-- DSC 提醒您：根据用户id来调用cart_info.lbi显示不同的界面  --}}
{!! insert_cart_info() !!}
                </div>
            </div>
        </div>
    </div>

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}

    @include('frontend::library/page_footer')

@if($cfg_bonus_adv == 1)

        {{-- DSC 提醒您：动态载入bonushome_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $bonushome]) !!}

@endif


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
	/*首页轮播*/
	var length = $(".banner .bd").find("li").length;
	if(length>1){
		$(".banner").slide({titCell:".hd ul",mainCell:".bd ul",effect:"fold",interTime:3500,delayTime:500,autoPlay:true,autoPage:true,trigger:"click",endFun:function(i,c,s){
			$(window).resize(function(){
				var width = $(window).width();
				s.find(".bd li").css("width",width);
			});
		}});
	}else{
		$(".banner .hd").hide();
	}
	$(".vip-item").slide({titCell:".tit a",mainCell:".con"});
	$(".seckill-channel").slide({mainCell:".box-bd ul",effect:"leftLoop",autoPlay:true,autoPage:true,interTime:5000,delayTime:500,vis:5,scroll:1,trigger:"click"});

	function load_js_content(key){
		var Floor = $("#floor_" + key);
		Floor.slide({titCell:".hd-tags li",mainCell:".floor-tabs-content",titOnClassName:"current"});
		Floor.find(".floor-left-slide").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",interTime:3500,delayTime:500,autoPlay:true,autoPage:true});
	}
	$("*[ectype='time']").each(function(){
		$(this).yomi();
	});

	//页面刷新自动返回顶部
	$("body,html").animate({scrollTop:0},50);

	$(function(){
		//判断首页那些广告位是否存在，处理左侧悬浮楼层栏
		var index_ad_cat = $("input[name='index_ad_cat']").val();

		if(index_ad_cat == 0){
			$(".lift-h-need").hide();
		}else{
			$(".lift-h-need").show();
		}

		//秒杀活动
		var seckill_goods = $("input[name='seckill_goods']").val();
		if(seckill_goods == 0){
			$(".lift-h-seckill").hide();
		}else{
			$(".lift-h-seckill").show();
		}
	});

	//楼层异步加载封装函数调用
	$.homefloorLoad();

	$(window).scroll(function(){
		var scrollTop = $(document).scrollTop();
		var navTop = $("*[ectype='channel']").offset().top;  //by yanxin

		if(scrollTop>navTop){
			$("*[ectype='suspColumn']").addClass("show");
		}else{
			$("*[ectype='suspColumn']").removeClass("show");
		}
	});

	//去掉悬浮框 我的购物车
	$(".attached-search-container .shopCart-con a span").text("");
    </script>
</body>
</html>
