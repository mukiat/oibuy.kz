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
    	<div class="banner seckill-cat-banner"><div class="w w1200"><span>{{ $cat_alias_name }}</span></div></div>
        <div class="seckill-main">
            <div class="w w1200">
            	<div class="seckill-hot-goods">
					<div class="seckill-warp">

@foreach($seckill_list as $tb)


@if($tb['goods'])

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

                                <a href="javascript:;"
@if(!$goods['is_remind'])
ectype="collSecGoods"
@endif
 data-id="{{ $goods['id'] }}" class="btn
@if($goods['is_remind'])
sc-redBg-btn
@else
sc-greenBg-btn
@endif
">

@if($goods['is_remind'])
{{ $lang['remind_goods_existed'] }}
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

@else

                        <div class="no_records no_records_tc"
@if(!(!$tb['is_end'] && $tb['status']))
 style="display:none;"
@endif
>
                            <i class="no_icon_two"></i>
                            <div class="no_info no_info_line">
                                <h3>{{ $lang['cat_not_null'] }}</h3>
                                <div class="no_btn">
                                    <a href="index.php" class="btn sc-redBg-btn">{{ $lang['back_home'] }}</a>
                                </div>
                            </div>
                        </div>

@endif


@endforeach

					</div>
                </div>
			@include('frontend::library/seckill_comming_soon')
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
