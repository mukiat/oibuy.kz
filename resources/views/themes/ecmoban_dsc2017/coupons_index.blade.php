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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/coupons.css') }}" />
</head>

<body>
@include('frontend::library/page_header_coupons')
{{-- DSC 提醒您：动态载入coupons_index.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $coupons_index, 'id' => 0]) !!}
<div id="content" class="quan_content">
    <div class="quan-main">
        <div class="gray-wrap">
            <div class="w1200 w">
                <div class="quan-seckill">
                    <div class="mt">
                        <h3><b class="seckill-icon"></b>{{ $lang['Coupon_kill'] }}</h3>
                    </div>
                    <div class="mc cou-seckill">
                        <div class="ui-switchable-panel-main">
                            <div class="ui-switchable-panel">
                                <div class="seckill-list">

@foreach($seckill as $vo)

                                    <div class="quan-sk-item
@if($vo['cou_surplus'] == 0)
 quan-gray-sk-item
@endif
">
                                        <div class="sk-img"><img width="130px" height="130px" src="{{ $vo['cou_goods_name']['0']['goods_thumb'] }}" alt="{{ $lang['pic_kill_goods'] }}"></div>
                                        <div class="q-type">
                                            <div class="q-price">
                                                <em>{{ config('shop.currency_format', '¥') }}</em>
                                                <strong class="num">{{ $vo['cou_money'] }}</strong>
                                                <div class="txt"><div class="typ-txt">{{ $vo['cou_type_name'] }}</div></div>
                                            </div>
                                            <div class="limit"><span class="quota">
@if($vo['cou_man'] > 0)
{{ $lang['full'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}
@else
{{ $lang['unlimited'] }}
@endif
</span></div>
                                            <div class="q-range">
                                                <div class="range-item" title="{{ $vo['cou_title'] }}">
                                                    {{ $vo['cou_title'] }}
                                                </div>
                                                <div class="range-item">{{ $vo['store_name'] }}</div>
                                            </div>
                                        </div>
                                        <div class="q-opbtns">
                                            <b class="semi-circle"></b>

@if($vo['cou_surplus'] == 0)

                                            <div class="btn-state btn-getend"></div>
                                            <a href="javascript:void(0);" class="q-btn"><span class="txt">{{ $lang['Activities_end'] }}</span><b></b></a>

@else

                                            <div class="canvas-qcode-box">
                                                <div class="canvas-box">
                                                	<div class="canvas" data-per="{{ $vo['cou_surplus'] }}">

@if(!empty($user_id) && $vo['cou_is_receive'] == 1)

														<div class="btn-state btn-geted">{{ $lang['receive_hove'] }}</div>

@else

                                                        <div class="canvas_wrap">
                                                            <div class="circle">
                                                                <div class="circle_item circle_left"></div>
                                                                <div class="circle_item circle_right wth0"></div>
                                                            </div>
                                                            <div class="canvas_num"><span>{{ $lang['remaining'] }}<br /><i>{{ $vo['cou_surplus'] }}</i>%</span></div>
                                                        </div>

@endif

                                                    </div>
                                                    <a href="
@if($vo['cou_is_receive'] == 1)
search.php?cou_id={{ $vo['cou_id'] }}
@else
javascript:void(0);
@endif
" class="q-btn
@if(!empty($user_id) && $vo['cou_is_receive'] == 1)

@else
get-coupon
@endif
" cou_id="{{ $vo['cou_id'] }}"><span class="txt">
@if($vo['cou_is_receive'] == 1)
{{ $lang['Immediate_use'] }}
@else
{{ $lang['receive_now'] }}
@endif
</span><b></b></a>
                                                    <a href="#none" class="qcode-btn"><b></b></a>
                                                </div>
                                            </div>

@endif

                                        </div>
                                    </div>

@endforeach

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="w1200 w">
            <div class="quan-mod quan-task">
                <div class="mt">
                    <h3><b class="task-icon"></b>{{ $lang['redemption_task'] }}</h3>
                    <div class="slogan s1">{{ $lang['coupons_prompt'] }}</div>
                    <div class="extra-r"><a target="_blank" href="coupons.php?act=coupons_goods" class="more">{{ $lang['more'] }} &gt;</a></div>
                    <div class="line"></div>
                </div>
                <div class="mc">
                    <div class="task-list">

@foreach($cou_goods as $vo)

                        <div class="quan-task-item task-doing">
                            <div class="p-img">
                                <a href="search.php?cou_id={{ $vo['cou_id'] }}" target="_blank"><img src="{{ $vo['cou_ok_goods_name']['0']['goods_thumb'] }}" width="240" height="240" alt="{{ $vo['cou_name'] }}"></a>
                            </div>
                            <div class="task-rcol">
                                <div class="p-name"><a href="search.php?cou_id={{ $vo['cou_id'] }}" target="_blank">{{ $vo['cou_name'] }}</a></div>
								<div class="range-item">{{ $vo['store_name'] }}</div>
                                <div class="p-ad"><i class="i1"></i><i class="i2"></i>{{ $lang['Top_up_coupons'] }}</div>
                                <div class="p-price">
                                    <em>{{ config('shop.currency_format', '¥') }}</em>
                                    <strong class="num">{{ $vo['cou_money'] }}</strong>
                                </div>
                                <div class="task-time">
                                    <b class="fl_b"></b>
                                    <div class="cd-time fl" ectype="time" data-time="{{ $vo['cou_end_time_format'] }}">
                                        <span>{{ $lang['remaining'] }}</span><span class="days">00</span><span class="split">{{ $lang['day'] }}</span><span class="hours"></span><span class="split">{{ $lang['hour_two'] }}</span><span class="minutes"></span><span class="split">{{ $lang['minute'] }}</span><span class="seconds"></span><span class="split">{{ $lang['seconds'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>

@endforeach

                    </div>
                </div>
            </div>
            <div class="quan-mod">
                <div class="mt">
                    <h3><b class="find-icon"></b>{{ $lang['Good_coupon_market'] }}</h3>
                    <div class="slogan s2">{{ $lang['always_you'] }}</div>
                    <div class="extra-r"><a target="_blank" href="coupons.php?act=coupons_list" class="more">{{ $lang['more'] }} &gt;</a></div>
                    <div class="line"></div>
                </div>
                <div class="mc cou-data">
                    <div class="quan-list">

@foreach($cou_data as $vo)

                        <div class="quan-item quan-d-item quan-item-acoupon
@if($vo['cou_surplus'] == 0)
 quan-gray-item
@endif
">

                            <div class="q-type">
                                    <div class="q-price">
                                        <em>{{ config('shop.currency_format', '¥') }}</em>
                                        <strong class="num">{{ $vo['cou_money'] }}</strong>
                                        <div class="txt" style="float: none;"><div class="typ-txt">{{ $vo['cou_type_name'] }}</div></div>
                                        <div class="txt">
                                            <div class="limit"><span class="ftx-06">
@if($vo['cou_man'] > 0)
{{ $lang['full'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}
@else
{{ $lang['unlimited'] }}
@endif
</span></div>
                                        </div>
                                    </div>
                                <div class="q-range">
                                    <div class="range-item"><p title="
@if($vo['cou_goods'])
<p>{{ $lang['permissions_buy'] }}</p>
@else
<p>{{ $lang['category'] }}</p>
@endif
">
                                        {{ $vo['cou_title'] }}
                                    </p></div>
                                    <div class="range-item">{{ $vo['store_name'] }}</div>
                                    <div class="range-item">{{ $vo['cou_start_time_format'] }}-{{ $vo['cou_end_time_format'] }}</div>
                                </div>
                            </div>

@if(!empty($user_id) && $vo['cou_is_receive'])


@if($vo['is_use']==0)


@if($vo['cou_surplus'] == 0)

                                        <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                                        <div class="q-opbtns"><a href="search.php?cou_id={{ $vo['cou_id'] }}" target="_blank"><b class="semi-circle"></b>{{ $lang['Immediate_use'] }}</a></div>
                                        <div class="q-state"><div class="btn-state btn-geted">{{ $lang['receive_hove'] }}</div></div>

@endif



@else


@if($vo['cou_surplus'] == 0)

                                    <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                                    <div class="q-opbtns"><a href="javascript:;" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif


@else


@if($vo['cou_surplus'] == 0)

                                <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                                <div class="q-opbtns"><a href="javascript:;" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif

                        </div>

@endforeach

                    </div>
                </div>
            </div>
            <div class="quan-mod">
                <div class="mt">
                    <h3><b class="find-icon"></b>{{ $lang['free_shen_pay'] }}</h3>
                    <div class="slogan s2">{{ $lang['always_you'] }}</div>
                    <div class="extra-r"><a target="_blank" href="coupons.php?act=coupons_list&type=shipping" class="more">{{ $lang['more'] }} &gt;</a></div>
                    <div class="line"></div>
                </div>
                <div class="mc cou_shipping">
                    <div class="quan-list">

@foreach($cou_shipping as $vo)

                        <div class="quan-item quan-d-item quan-item-acoupon
@if($vo['cou_surplus'] == 0)
 quan-gray-item
@endif
">
                            <div class="q-type">
                                <div class="q-price">
                                    <i class="icon-my"></i>
                                    <div class="txt" style="float: none;"><div class="typ-txt">{{ $vo['cou_type_name'] }}</div></div>
                                    <div class="txt">
                                        <div class="limit"><span class="ftx-06">
@if($vo['cou_man'] > 0)
{{ $lang['full'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}
@else
{{ $lang['unlimited'] }}
@endif
</span></div>
                                    </div>
                                </div>
                                <div class="q-range">
                                    <div class="range-item"><p title="
@if($vo['cou_goods'])
<p>{{ $lang['permissions_buy'] }}</p>
@else
<p>{{ $lang['category'] }}</p>
@endif
">
                                        {{ $vo['cou_title'] }}
                                    </p></div>
                                    <div class="range-item">{{ $vo['store_name'] }}</div>
                                    <div class="range-item">{{ $vo['cou_start_time_format'] }}-{{ $vo['cou_end_time_format'] }}</div>
                                </div>
                            </div>

@if(!empty($user_id) && $vo['cou_is_receive'])


@if($vo['is_use']==0)


@if($vo['cou_surplus'] == 0)

                                        <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                                        <div class="q-opbtns"><a href="search.php?cou_id={{ $vo['cou_id'] }}" target="_blank"><b class="semi-circle"></b>{{ $lang['Immediate_use'] }}</a></div>
                                        <div class="q-state"><div class="btn-state btn-geted">{{ $lang['receive_hove'] }}</div></div>

@endif



@else


@if($vo['cou_surplus'] == 0)

                                    <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                                    <div class="q-opbtns"><a href="javascript:;" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif


@else


@if($vo['cou_surplus'] == 0)

                                <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                                <div class="q-opbtns"><a href="javascript:;" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif

                        </div>

@endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
	//首页轮播广告
	var length = $(".silder-panel ul").find("li").length;
	if(length > 1){
		$("#g-scroll").slide({titCell:".num-ctrl ul",mainCell:".silder-panel ul",effect:"left",autoPlay:true,autoPage:true,interTime:3500,delayTime:500});
	}

	//优惠券秒杀切换
	$(".seckill-tab li").hover(function(){
		$(this).addClass("curr").siblings().removeClass("curr");
		var index = $(this).index();

		$(".ui-switchable-panel-main").find(".ui-switchable-panel").eq(index).show().siblings().hide();
	});

	$(".canvas").each(function(){
		var per = 100 - $(this).data("per");
		if(per>50){
			$(this).find('.circle').addClass('clip-auto');
			$(this).find('.circle_right').removeClass('wth0');
		}
		$(this).find(".circle_left").css("-webkit-transform","rotate("+(18/5)*per+"deg)");
	});

	$(".cd-time").each(function(){
		$(this).yomi();
	});
</script>
</body>
</html>
