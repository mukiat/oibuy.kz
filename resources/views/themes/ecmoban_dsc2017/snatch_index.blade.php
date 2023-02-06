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
    <div class="content">
    	<div class="banner exchange-banner">
            <div class="w w1200 relative">
                {{-- DSC 提醒您：动态载入activity_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $activity_top_banner]) !!}
                <div class="snatch-firt">
                    <div class="snatch-f-name">{{ $lang['snatch'] }}</div>
                    <div class="snatch-f-info">
                        <div class="namber">{{ $snatch_goods_num ?? 0 }}</div>
                        <span>{{ $lang['snatch_goods_notic'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="snatch-main">
            <div class="w w1200">
                <div class="snatch-hot">
                    <div class="snatch-t"><h2>{{ $lang['Popular_recommendation'] }}</h2></div>
                    <div class="snatch-hot-slide">
                        <div class="p-left"><a href="javascript:;" class="prev"></a></div>
                        <div class="p-right"><a href="javascript:;" class="next"></a></div>
                        <div class="bd">
                            <ul>

@foreach($hot_goods as $goods)

                                <li>
                                    <a href="{{ $goods['url'] }}" target="_blank" class="img"><img src="
@if($goods['goods_img'])
{{ $goods['goods_img'] }}
@else
{{ $goods['thumb'] }}
@endif
"></a>
                                    <a href="{{ $goods['url'] }}" target="_blank" class="name">{{ $goods['act_name'] }}</a>
                                    <div class="info">
                                        <div class="info-item">
                                            <span>{{ $lang['current_price'] }}：</span>
                                            <span class="price">{{ $goods['formated_shop_price'] }}</span>
                                        </div>
                                        <div class="info-item">
                                            <span>{{ $lang['bid_number'] }}：</span>
                                            <span>{{ $goods['price_list_count'] }}</span>
                                        </div>
                                        <div class="info-item lefttime" data-time="{{ $goods['end_time_date'] }}">
                                            <span>{{ $lang['residual_time'] }}：</span>
                                            <span class="days">00</span>
                                            <em>:</em>
                                            <span class="hours">15</span>
                                            <em>:</em>
                                            <span class="minutes">40</span>
                                            <em>:</em>
                                            <span class="seconds">10</span>
                                        </div>
                                    </div>
                                    <a href="{{ $goods['url'] }}" target="_blank" class="sn-btn">{{ $lang['me_bid'] }}</a>
                                </li>

@endforeach

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="snatch-index-goods">
                    <div class="snatch-t"><h2>{{ $lang['snatch'] }}</h2></div>
                    <div class="snatch-b">
                    	<div class="snatch-list">
                            <ul class="clearfix" ectype="items">

@foreach($snatch_list as $list)

                                <li class="mod-shadow-card">
                                    <a href="{{ $list['url'] }}" class="img"><img src="{{ $list['goods_thumb'] }}" alt="{{ $list['snatch_name'] }}"></a>
                                    <a href="{{ $list['url'] }}" class="name">{{ $list['snatch_name'] }}</a>
                                    <div class="info">
                                        <p><em>{{ $lang['current_price'] }}：</em><span class="price">{{ $list['formated_shop_price'] }}</span></p>
                                        <p class="lefttime" data-time="{{ $list['snatch']['end_time_date'] }}">
                                            <span>{{ $lang['residual_time'] }}：</span>
                                            <span class="days">00</span>
                                            <em>:</em>
                                            <span class="hours">15</span>
                                            <em>:</em>
                                            <span class="minutes">40</span>
                                            <em>:</em>
                                            <span class="seconds">10</span>
                                        </p>
                                        <p><em>{{ $lang['bid_number'] }}：</em><span>{{ $list['price_list_count'] }}</span></p>
                                    </div>

@if($list['current_time'] < $list['end_time'] && $list['current_time'] > $list['start_time'] )

                                    <a href="{{ $list['url'] }}" target="_blank" class="sn-btn"><em></em>{{ $lang['me_bid'] }}<s></s></a>

@elseif ($list['current_time'] >= $list['end_time'] )

                                    <a href="{{ $list['url'] }}" target="_blank" class="sn-btn bid_end"><em></em>{{ $lang['au_end'] }}<s></s></a>

@else

                                    <a href="{{ $list['url'] }}" target="_blank" class="sn-btn bid_wait"><em></em>{{ $lang['Wait_au'] }}<s></s></a>

@endif

                                </li>

@endforeach

                            </ul>
                        </div>
                        <a href="snatch.php?act=list" class="gb-btn-all">{{ $lang['all_snatch'] }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
	$(function(){
		$(".snatch-hot-slide").slide({effect: "left",vis: 3,scroll: 1,autoPage: true,mainCell: ".bd ul"});

		//倒计时
		$(".lefttime").each(function(){
			$(this).yomi();
		});
	});
    </script>
</body>
</html>
