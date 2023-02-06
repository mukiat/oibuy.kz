<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />

@if($brand['brand_desc'])

<meta name="Description" content="{{ $brand['brand_desc'] }}" />

@else

<meta name="Description" content="{{ $description }}" />

@endif


<title>{{ $page_title }}</title>



<link rel="shortcut icon" href="favicon.ico" />
@include('frontend::library/js_languages_new')
</head>

<body>
    @include('frontend::library/page_header_common')
    <div class="content">
        <div class="brand-home-top"
@if($brand['brand_bg'])
    style="background:url({{ $brand['brand_bg'] }}) center center no-repeat;"
@else
    style="background:url({{ skin('images/brand_cat_bg.jpg') }}) center center no-repeat;"
@endif
>
        	<div class="w w1200">
                <div class="attention-rate">
                    <div class="brand-logo"><img src="{{ $brand['brand_logo'] }}" style="max-width: 200px; max-height: 88px;"></div>
                    <div class="follow-info">
                        <span class="follow-sum"><em id="collect_count">{{ $brand['collect_count'] }}</em>人&nbsp;&nbsp;{{ $lang['follow'] }}</span>
                        <div class="go-follow" data-bid="{{ $brand_id }}" ectype="coll_brand">
@if($brand['is_collect'] > 0)
<i class="iconfont icon-zan-alts"></i><span ectype="follow_span">{{ $lang['follow_yes'] }}</span>
@else
<i class="iconfont icon-zan-alt"></i><span ectype="follow_span">{{ $lang['follow'] }}</span>
@endif
</div>
                    </div>
                </div>
                <div class="brand-cate-info">
                	<div class="title">
                    	<h3>{{ $lang['brand_desc'] }}</h3>
                    </div>
                    <div class="brand_desc" title="{{ $brand['brand_desc'] }}">
@if($brand['brand_desc'])
{{ $brand['brand_desc'] }}
@else
{{ $lang['brand_desc_notic'] }}
@endif
</div>
                </div>
            </div>
        </div>
        <div class="brand-main">
            <div class="w w1200" ectype="goodslist">
            	<div class="brand-section">
                    <div class="bl-title"><h2>{{ $lang['goods_list'] }}<i></i></h2>
@if($cat_id)
<a href="category.php?id={{ $cat_id }}&brand={{ $brand_id }}" class="more ftx-05">{{ $lang['see_more'] }}></a>
@endif
</div>
                    <div class="bl-content">
                        <div class="bd">
                            <ul>

@forelse($goods_list as $goods)


@if($loop->iteration <= 100)

                                <li>
                                    <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                                    <div class="p-price">{{ $goods['shop_price'] }}</div>
                                    <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
                                </li>

@endif


@empty

                                <li class="notic">
                                	<div class="no_records no_records_tc">
                                        <i class="no_icon_two"></i>
                                        <div class="no_info">
                                            <h3>{{ $lang['brand_goods_notic'] }}</h3>
                                        </div>
                                    </div>
                                </li>

@endforelse

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('frontend::library/pages')
		<input type="hidden" name="user_id" value="{{ $user_id ?? 0 }}">
		<input type="hidden" name="brand_id" value="{{ $brand_id ?? 0 }}">
    </div>
	{{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
	<script type="text/javascript">
	var length = $(".best-list .bd ul").find("li").length;
	if(length>1){
		$(".best-list").slide({mainCell: '.bd ul',titCell: '.hd ul',effect: 'left',pnLoop: false,vis: 5,scroll: 5,autoPage: '<li></li>'});
	}
	</script>
</body>
</html>
