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
<div id="content" class="quan_content">
    <div class="quan-main mt20">
        <div class="w1200 w">
            <div class="task-list">

@foreach($cou_goods as $vo)

                <div class="quan-task-item task-doing"
@if($vo['is_overtime'] == 1)
 onclick="window.location.href='search.php?cou_id={{ $vo['cou_id'] }}'"
@endif
>
                    <div class="p-img">
                        <a href="javascript:;" target="_blank"><img style="width: 200px;height: 200px;" src="{{ $vo['cou_ok_goods_name']['0']['goods_thumb'] }}" alt="{{ $vo['cou_name'] }}"></a>
                    </div>
                    <div class="task-rcol">
                        <div class="p-name"><a href="javascript:;" target="_blank">{{ $vo['cou_name'] }}</a></div>
						<div class="range-item">{{ $vo['store_name'] }}</div>
                        <div class="p-ad"><i class="i1"></i><i class="i2"></i>{{ $lang['Top_up_coupons'] }}</div>
                        <div class="p-price">
                            <em>{{ config('shop.currency_format', '¥') }}</em>
                            <strong class="num">{{ $vo['cou_money'] }}</strong>
                        </div>
                        <div class="task-time">
                            <b></b>
                            <div class="time" ectype="time" data-time="{{ $vo['cou_end_time_format'] }}">

@if($vo['is_overtime'] == 1)

                                <span>{{ $lang['remaining'] }}</span><span class="days">00</span>{{ $lang['day'] }}<span class="hours">00</span>{{ $lang['hour_two'] }}<span class="minutes">00</span>{{ $lang['minute'] }}<span class="seconds">00</span>{{ $lang['seconds'] }}

@else

                                <span>{{ $lang['has_ended'] }}</span>

@endif

                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>

@endforeach

            </div>
        </div>
    </div>

@if($page_total>1)

    <div class="pages" id="pager">
        <ul>
            <li class="item prev"><a href="coupons.php?{{ $page_url }}&p={{ $prev_page }}"><i class="iconfont icon-left"></i></a></li>

@foreach($page_total2 as $vo)

            <li class="item
@if($page==$vo )
cur
@endif
"><a href=coupons.php?{{ $page_url }}&p={{ $vo }}>{{ $vo }}</a></li>

@endforeach

            <li class="item next"><a href="coupons.php?{{ $page_url }}&p={{ $next_page }}"><i class="iconfont icon-right"></i></a></li>
        </ul>
    </div>

@endif

    <input name="script_name" value="{{ $script_name }}" type="hidden" />
	<input name="cur_url" value="{{ $cur_url }}" type="hidden" />
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
$(".time").each(function(){
	$(this).yomi();
});
</script>
</body>
</html>
