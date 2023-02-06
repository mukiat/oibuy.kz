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

<body class="package-content">
    @include('frontend::library/page_header_common')
    <div class="content">
        <div class="w w1200">
            @include('frontend::library/ur_here')

@if($list)

            <ul class="package-list clearfix">

@foreach($list as $val)

                <li class="list-item">
                    <div class="cover">
                        <img src="{{ $val['activity_thumb'] }}" alt="">
                        <span class="intro">{{ $val['act_name'] }}</span>
                    </div>
                    <div class="info">
                        <div class="info-p clearfix">
                            <div class="fl">{{ $lang['package_number'] }}：<b class="red">{{ $val['package_number'] ?? 1 }}件</b></div>
                            <div class="fr">{{ $lang['package_time'] }}：{{ $val['start_time'] }} ～ {{ $val['end_time'] }}</div>
                        </div>
                        <div class="info-slide clearfix">
                            <a href="javascript:;" class="prev"></a>
                            <a href="javascript:;" class="next"></a>
                            <div class="bd">
                                <ul>

@foreach($val['goods_list'] as $goods)

                                    <li
@if($loop->first)
 class="g_first"
@endif

@if($loop->last)
 class="g_last"
@endif
>
                                        <a href="{{ $goods['url'] }}" class="img" target="_blank"><img src="{{ $goods['goods_thumb'] }}" alt="{{ $goods['goods_name'] }}"></a>
                                        <div class="price">{{ $goods['rank_price'] }} X {{ $goods['goods_number'] }}</div>
                                    </li>

@endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="info-btn-wp">
                            <div class="fl">
                                <span class="price">{{ $val['package_price'] }}</span>
                                <del>{{ $val['subtotal'] }}</del>
                            </div>
                            <div class="fr">
                                <a href="javascript:addPackageToCart({{ $val['act_id'] }})" class="pack-btn">{{ $lang['button_buy'] }} &gt;</a>
                                <span class="red">{{ $lang['sheng'] }}{{ $val['saving'] }}</span>
                            </div>
                        </div>
                    </div>
                </li>

@endforeach

            </ul>

@else

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info">
                        <h3>{{ $lang['information_null'] }}</h3>
                    </div>
                </div>

@endif

        </div>
    </div>
    <div class="hidden">
    <input name="confirm_type" id="confirm_type" type="hidden" value="3" />
    <input name="warehouse_id" id="warehouse_id" type="hidden" value="{{ $region_id }}" />
    <input name="area_id" id="area_id" type="hidden" value="{{ $area_id }}" />
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position(['ru_id' => $goods['user_id']]) !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
	$(function(){
		//礼包商品左右滚动
		$(".package-list .info-slide").slide({effect: "left",vis: 4,scroll: 4,autoPage: true,mainCell: ".bd ul"});
	});
    </script>
</body>
</html>
