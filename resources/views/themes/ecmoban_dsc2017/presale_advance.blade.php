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

<body class=" show">
@include('frontend::library/page_header_presale')
{{-- DSCMALL presale_banner_advance.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $presale_banner_advance]) !!}

<div class="preSale-filter">
    <div id="filter">
        <div class="filter-section-wrapper mb-component mt-component w1200 mt20 w">
            <div class="component-filter component-filter-category">
                <div class="filter-label-list">
                    <div class="label">{{ $lang['category'] }}：</div>

                    <div class="filter-quanbu
@if($pager['cat_id'] == 0 )
 selected
@endif
"><a href="presale.php?act=advance&cat_id=0&status={{ $pager['status'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&order={{ $pager['order'] }}">{{ $lang['all_attribute'] }}</a></div>
                    <ul class="inline-block-list">

@foreach($pre_category as $category)

                        <li
@if($pager['cat_id'] == $category['cat_id'] )
class="selected"
@endif
><a href="presale.php?act=advance&cat_id={{ $category['cat_id'] }}&status={{ $pager['status'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}">{{ $category['cat_name'] }}</a></li>

@endforeach

                    </ul>
                </div>
                <div class="filter-label-list">
                    <div class="label">{{ $lang['array_order'] }}：</div>
                    <div class="filter-quanbu
@if($pager['sort'] == 'act_id')
selected
@endif
"><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status={{ $pager['status'] }}&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&order={{ $pager['order'] }}">{{ $lang['default'] }}</a></div>
                    <ul class="inline-block-list">
                        <li
@if($pager['sort'] == 'shop_price')
class="selected"
@endif
><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status={{ $pager['status'] }}&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&sort=shop_price&order={{ $pager['order'] }}">{{ $lang['price'] }}</a></li>
                        <li
@if($pager['sort'] == 'start_time')
class="selected"
@endif
><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status={{ $pager['status'] }}&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&sort=start_time&order={{ $pager['order'] }}">{{ $lang['is_new'] }}</a></li>
                    </ul>
                </div>
                <div class="filter-label-list">
                    <div class="label">{{ $lang['au_bid_status'] }}：</div>
                    <div class="filter-quanbu
@if($pager['status'] == 0 )
selected
@endif
"><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status=0&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}">{{ $lang['all_attribute'] }}</a></div>
                    <ul class="inline-block-list">
                        <li
@if($pager['status'] == 1 )
class="selected"
@endif
><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status=1&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}">{{ $lang['begin_minute'] }}</a></li>
                        <li
@if($pager['status'] == 2 )
class="selected"
@endif
><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status=2&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}">{{ $lang['Appointment'] }}</a></li>
                        <li
@if($pager['status'] == 3 )
class="selected"
@endif
><a href="presale.php?act=advance&cat_id={{ $pager['cat_id'] }}&status=3&price_min={{ $pager['price_min'] }}&price_max={{ $pager['price_max'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}">{{ $lang['has_ended'] }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="content">
    <div class="w1200 pb40 w">
        <div class="special-item">
            <div class="special-product">

@if($goods)

                <ul>

@foreach($goods as $goods)

                    <li>
                        <div class="s-warp">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" width="255" height="255"/></a></div>
                            <div class="p-price">
                                <span><em>{{ config('shop.currency_format', '¥') }}</em>{{ $goods['shop_price'] }}</span>
                                <del><em>{{ config('shop.currency_format', '¥') }}</em>{{ $goods['market_price'] }}</del>
                            </div>
                            <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                            <div class="p-info">
                                <div class="p-left">

@if($goods['no_start'])

                                        <div class="time" ectype="time" data-time="{{ $goods['start_time_date'] }}">
                                            {{ $lang['Start_from'] }}<span class="days">00</span>{{ $lang['day'] }}&nbsp;<span class="hours">01</span>:<span class="minutes">56</span>:<span class="seconds">23</span>
                                        </div>

@elseif ($goods['already_over'])

                                        <div class="time" data-time="{{ $goods['start_time_date'] }}">
                                            {{ $lang['has_ended'] }}
                                        </div>

@else

                                        <div class="time" ectype="time" data-time="{{ $goods['end_time_date'] }}">
                                            {{ $lang['Count_down'] }}<span class="days">00</span>{{ $lang['day'] }}&nbsp;<span class="hours">01</span>:<span class="minutes">56</span>:<span class="seconds">23</span>
                                        </div>

@endif

                                    <span class="appointment">{{ $lang['existing'] }}<em>{{ $goods['pre_num'] }}</em>{{ $lang['subscribe_p'] }}</span>
                                </div>
                            </div>
                        </div>
                    </li>

@endforeach

                </ul>

@else

                <div class="no_records no_records_tc">
                    <i class="no_icon_two"></i>
                    <div class="no_info no_info_line">
                        <h3>{{ $lang['information_null'] }}</h3>
                        <div class="no_btn">
                            <a href="index.php" class="btn sc-redBg-btn">{{ $lang['back_home'] }}</a>
                        </div>
                    </div>
                </div>

@endif

            </div>
        </div>
    </div>
</div>
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
	//倒计时JS
	$(".time").each(function(){
		$(this).yomi();
	});

	var length = $(".pre-banner .bd ul").find("li").length;
	if(length>1){
		$(".pre-banner").slide({titCell:".hd ul",mainCell:".bd ul",effect:"top",interTime:3500,delayTime:500,autoPlay:true,autoPage:true});
	}else{
		$(".pre-banner .hd ul").hide();
	}
</script>
</body>
</html>
