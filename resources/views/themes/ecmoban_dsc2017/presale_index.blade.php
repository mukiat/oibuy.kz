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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/presale.css') }}" />
</head>

<body class="show">
@include('frontend::library/page_header_presale')
{{-- DSC 提醒您：动态载入presale_banner.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $presale_banner]) !!}
<div id="content">
    <div class="ecsc-sign w1200 w">
        <h1 class="preSale_title">{{ $lang['Signature_recommendation'] }}</h1>
        <div class="sign-warpper">
        {{-- DSC 提醒您：动态载入presale_banner_small_left.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $presale_banner_small_left]) !!}

        {{-- DSC 提醒您：动态载入presale_banner_small.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $presale_banner_small]) !!}

        {{-- DSC 提醒您：动态载入presale_banner_small_right.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $presale_banner_small_right]) !!}
        </div>
    </div>
    <div class="special-list w1200 pb40 w">

@foreach($pre_cat_goods as $cat_goods)


@if($cat_goods['count_goods'] != 0)

        <div class="special-item">
            <div class="title"><h3>{{ $cat_goods['cat_name'] }}</h3><a href="{{ $cat_goods['cat_url'] }}"><i class="special-icon special-icon-1"></i></a></div>
            <div class="special-product">
                <ul>

@foreach($cat_goods['goods'] as $goods)

                    <li>
                        <div class="s-warp">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" width="255" height="255"/></a></div>
                            <div class="p-price">
                                <span>{{ $goods['format_shop_price'] }}</span>
                                <del>{{ $goods['format_market_price'] }}</del>
                            </div>
                            <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                            <div class="p-info">
                                <div class="p-left">

@if($goods['no_start'])

                                        <div class="time" ectype="time" data-time="{{ $goods['start_time_date'] }}">
                                            {{ $lang['Start_from'] }}<span class="days">00</span>{{ $lang['day'] }}&nbsp;<span class="hours">00</span>:<span class="minutes">00</span>:<span class="seconds">00</span>
                                        </div>

@elseif ($goods['already_over'])

                                        <div class="time" data-time="{{ $goods['start_time_date'] }}">
                                            {{ $lang['has_ended'] }}
                                        </div>

@else

                                        <div class="time" ectype="time" data-time="{{ $goods['end_time_date'] }}">
                                            {{ $lang['Count_down'] }}<span class="days">00</span>{{ $lang['day'] }}&nbsp;<span class="hours">00</span>:<span class="minutes">00</span>:<span class="seconds">00</span>
                                        </div>

@endif

                                    <span class="appointment">{{ $lang['existing'] }}<em>{{ $goods['pre_num'] }}</em>{{ $lang['subscribe_p'] }}</span>
                                </div>
                            	<p>{{ $lang['presale_seller'] }}：<a href="{{ $goods['shop_url'] }}" title="{{ $goods['shop_name'] }}" target="_blank" class="name">{{ $goods['shop_name'] }}</a></p>
                            </div>
                        </div>
                    </li>

@endforeach

                </ul>
            </div>
        </div>

@endif


@endforeach


    </div>
</div>
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
	$(".pre-banner").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",interTime:3500,delayTime:500,autoPlay:true,autoPage:true});
	$(".sign-content").slide({titCell:".hd ul",mainCell:".bd ul",effect:"leftLoop",interTime:3500,delayTime:500,autoPlay:true,pnLoop:true,autoPage:true});

	//倒计时JS
	$(".time").each(function(){
		$(this).yomi();
	});
</script>
</body>
</html>
