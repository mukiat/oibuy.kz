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
    @include('frontend::library/page_header_group')
    <div class="content">
        <div class="act-banner">{{-- DSC 提醒您：动态载入activity_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $activity_top_banner]) !!}</div>
        <div class="gb-crazy">
            <div class="w w1200">
                <div class="crazy-hd">
                    <h2>{{ $lang['insane_group_buy'] }}</h2>
                </div>
                <div class="crazy-bd">
                    <ul class="crazy-list clearfix">

@foreach($new_list as $group_buy)

                        <li class="mod-shadow-card">
                            <a href="{!! $group_buy['url'] !!}" target="_blank" class="img"><img src="{{ $group_buy['goods_thumb'] }}" alt="{{ $group_buy['goods_name'] }}" title="{{ $group_buy['goods_name'] }}"></a>
                            <p class="price">{{ $group_buy['price_ladder']['0']['formated_price'] }}</p>
                            <a href="{!! $group_buy['url'] !!}" target="_blank" class="name" title="{{ $group_buy['goods_name'] }}">{{ $group_buy['goods_name'] }}</a>
                            <div class="lefttime" data-time='{{ $group_buy['formated_end_date'] }}'>
                                <i class="iconfont icon-time"></i>
                                <span>{{ $lang['residue_time'] }}</span>
                                <span class="days"></span>
                                <em>:</em>
                                <span class="hours"></span>
                                <em>:</em>
                                <span class="minutes"></span>
                                <em>:</em>
                                <span class="seconds"></span>
                            </div>

@if($group_buy['is_end'] == 1)

                            <a href="{!! $group_buy['url'] !!}" class="crazy-btn bid_end">{{ $lang['Group_purchase_end'] }}</a>

@else

                            <a href="{!! $group_buy['url'] !!}" class="crazy-btn">{{ $lang['Group_purchase_now'] }}</a>

@endif

                        </li>

@endforeach

                    </ul>
                </div>
            </div>
        </div>
        <div class="gb-index-main w w1200">
            <h2>{{ $lang['hot_group_buy'] }}</h2>
            <div class="gb-index-list">
                <ul class="clearfix" ectype="items">

@foreach($hot_list as $group_buy)

                    <li class="mod-shadow-card">
                        <a href="{!! $group_buy['url'] !!}" target="_blank" class="img"><img src="{{ $group_buy['goods_thumb'] }}" alt="{{ $group_buy['goods_name'] }}" title="{{ $group_buy['goods_name'] }}"></a>
                        <div class="clearfix">
                            <div class="price">{{ config('shop.currency_format', '¥') }}{{ $group_buy['price_ladder']['0']['price'] }}</div>
                            <div class="man">{{ $group_buy['cur_amount'] }}{{ $lang['people_participate'] }}</div>
                        </div>
                        <a href="{!! $group_buy['url'] !!}" target="_blank" class="name" title="{{ $group_buy['goods_name'] }}">{{ $group_buy['goods_name'] }}</a>
                        <div class="lefttime" data-time='{{ $group_buy['formated_end_date'] }}'>
                            <i class="iconfont icon-time"></i>
                            <span>{{ $lang['residue_time'] }}</span>
                            <span class="days"></span>
                            <em>:</em>
                            <span class="hours"></span>
                            <em>:</em>
                            <span class="minutes"></span>
                            <em>:</em>
                            <span class="seconds"></span>
                        </div>

@if($group_buy['is_end'] == 1)

                        <a href="{!! $group_buy['url'] !!}" class="gb-btn bid_end">{{ $lang['Group_purchase_end'] }}</a>

@else

                        <a href="{!! $group_buy['url'] !!}" class="gb-btn">{{ $lang['Group_purchase_now'] }}</a>

@endif

                    </li>

@endforeach

                </ul>
            </div>
            <a href="group_buy.php?act=list" class="gb-btn-all">{{ $lang['all_group_buy'] }}</a>
        </div>
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
	$(function(){
		//倒计时
		$(".lefttime").each(function(){
			$(this).yomi();
		});
	});
    </script>
</body>
</html>
