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
    @include('frontend::library/page_header_seckill')
    <div class="content">
		{{-- DSC 提醒您：动态载入seckill_top_ad.lbi，显示首页达人专区小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $seckill_top_ad]) !!}
        <div class="seckill-main">
            <div class="w w1200">
            	<div class="seckill-hot-goods">
                	<div class="seckill-time-tabs" ectype="seckillTab">
                    	<ul>

@foreach($seckill_list as $tb)


@if($loop->index < 5)

                        	<li
@if(!$tb['is_end'] && $tb['status'])
class="curr"
@endif

@if($loop->count == 5)
 style="width:20%;"
@endif
>
                                <strong>
@if($tb['tomorrow'])
{{ $lang['tomorrow'] }}
@endif
{{ $tb['title'] }}</strong>
                                <div class="time" ectype="time" data-time="
@if(!$tb['is_end'] && !$tb['status'])
{{ $tb['begin_time_formated'] }}
@elseif (!$tb['is_end'] && $tb['status'])
{{ $tb['end_time_formated'] }}
@endif
">

@if(!$tb['is_end'] && !$tb['status'])

									<span>{{ $lang['distance_start'] }}</span>
                                    <span class="hours">15</span>
                                    <em>:</em>
                                    <span class="minutes">40</span>
                                    <em>:</em>
                                    <span class="seconds">10</span>

@elseif (!$tb['is_end'] && $tb['status'])

									<span>{{ $lang['distance_end'] }}</span>
                                    <span class="hours">15</span>
                                    <em>:</em>
                                    <span class="minutes">40</span>
                                    <em>:</em>
                                    <span class="seconds">10</span>

@else

									<span>{{ $lang['has_ended'] }}</span>

@endif

                                </div>

@if(!$tb['is_end'] && !$tb['status'])

								<i>{{ $lang['begin_minute'] }}</i>

@elseif (!$tb['is_end'] && $tb['status'])

								<i>{{ $lang['have_in_hand'] }}</i>

@else

								<i>{{ $lang['has_ended'] }}</i>

@endif

                            </li>

@endif


@endforeach

                        </ul>
                    </div>
					<div class="seckill-warp">

@foreach($seckill_list as $tb)


@if($loop->index < 5)

                    	<ul class="gb-index-list clearfix"
@if(!(!$tb['is_end'] && $tb['status']))
style="display:none;"
@endif
>

@foreach($tb['goods'] as $goods)

                        	<li class="mod-shadow-card">
                            	<div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                                <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                <div class="p-lie clearfix">
                                	<div class="p-pirce">{{ $goods['sec_price_formated'] }}</div>
                                    <div class="p-del"><del>{{ $goods['market_price_formated'] }}</del></div>
                                </div>
                                <div class="p-number clearfix">
                                	<span>{{ $lang['Sold'] }}{{ $goods['percent'] }}%</span>
                                    <div class="timebar"><i style="width:{{ $goods['percent'] }}%;"></i></div>
                                </div>

@if(!$tb['is_end'] && !$tb['status'])

                                <a href="javascript:;" ectype="collSecGoods" data-id="{{ $goods['id'] }}" class="btn
@if($goods['is_remind'])
sc-redBg-btn
@else
sc-greenBg-btn
@endif
">

@if($goods['is_remind'])
{{ $lang['cancel_remind_me'] }}
@else
{{ $lang['remind_me'] }}
@endif

                                </a>

@elseif (!$tb['is_end'] && $tb['status'])


@if($goods['sec_num'] <= 0)

                                <a href="javascript:;" class="btn sc-redBg-btn">{{ $lang['over_tobuy'] }}</a>

@else

                                <a href="{{ $goods['url'] }}"  target="_blank" class="btn sc-redBg-btn">
								{{ $lang['button_buy'] }}
								</a>

@endif


@else

                                <a href="{{ $goods['url'] }}"  target="_blank" class="btn sc-redBg-btn">
								{{ $lang['has_ended'] }}
                                </a>

@endif

                            </li>

@endforeach

                        </ul>

@endif


@endforeach

					</div>
                </div>
			@include('frontend::library/recommend_goods')
            </div>
        </div>
    </div>
    @include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
	$(function(){
		$("*[ectype='time']").each(function(){
			$(this).yomi();
		});

		$("*[ectype='seckillTab'] li").on("click",function(){
			var index = $(this).index();
			$(this).addClass("curr").siblings().removeClass("curr");

			$(".seckill-warp").find("ul").eq(index).show().siblings().hide();
		});
	});
    </script>
</body>
</html>
