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

<body class="bg-ligtGary">
	@include('frontend::library/page_header_common')
    <div class="shop-header">
         @include('frontend::library/merchants_store_top')
        <div class="bottom">
            <div class="w w1200">
                <div class="shop-head-category">
                    <div class="all-cate"><a href="javascript:;">{{ $lang['all_seller_cat'] }}<i class="iconfont icon-liebiao"></i></a></div>
                    <div class="cate-tab-content">

@foreach($cat_store_list as $cat)


@if($loop->iteration<6)

                        <dl>
                            <dt><a href="{{ $cat['url'] }}">{{ $cat['cat_name'] }}</a></dt>

@foreach($cat['child_tree'] as $tree)

                            <a href="{{ $tree['url'] }}">{{ $tree['name'] }}</a>

@endforeach

                        </dl>

@endif


@endforeach

                    </div>
                </div>
                <div class="shop-nav">
                    <ul>
                        <li>
                            <a href="{{ $merchants_url }}"
@if($cat_id == 0)
class="current"
@endif
>{{ $lang['Shop_home'] }}</a>
                        </li>

@foreach($store_category as $key => $category)


@if($loop->iteration<6)

                        <li class="s_box_id">
                            <a href="{{ $category['url'] }}"
@if($category['opennew'] == 1)
 target="_blank"
@endif
>{{ $category['cat_name'] }}</a>
                        </li>

@endif


@endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="shop-banner">
        <div class="w w1200">
            <div class="bd">
                <ul>

@foreach($banner_list as $ad)

                    <li><a href="{{ $ad['img_link'] }}" target="_blank" style="background:url({{ $ad['img_url'] }}) no-repeat center 0px;"></a></li>

@endforeach

                </ul>
            </div>
            <div class="hd"><ul></ul></div>
        </div>
    </div>

    <div class="shop-home">
        <div class="w1200 w">

            <div class="under-banner">
            	{{-- DSC 提醒您：动态载入merchants_index.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $merchants_index]) !!}
            </div>

            <div class="shop-floor-wp">

@forelse($get_cat_goods as $top_goods)


@if($top_goods['goods'])

                <div class="shop-floor">
                    <div class="f-hd"><h2>{{ $top_goods['cat_name'] }}</h2></div>
                    <div class="f-bd">
                        <ul class="list clearfix">
                            <li class="first">{{-- DSC 提醒您：动态载merchants_index_flow.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $merchants_index_flow, 'id' => $top_goods['cat_id']]) !!}</li>

@foreach($top_goods['goods'] as $goods)

                            <li class="goods-item">
                                <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                                <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['short_name'] }}" target="_blank">{{ $goods['short_name'] }}</a></div>
                                <div class="p-price">

@if($goods['promote_price'])

                                	{{ $goods['promote_price'] }}

@else

                                    {{ $goods['shop_price'] }}

@endif

                                </div>
                            </li>

@endforeach

                        </ul>
                    </div>
                </div>

@endif


@empty

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info">
                        <h3>{{ $lang['information_null'] }}</h3>
                    </div>
                </div>

@endforelse



@if($goods_hot)

                <div class="shop-floor">
                    <div class="f-hd"><h2>热卖商品</h2></div>
                    <div class="f-bd">
                        <ul class="list clearfix">
                            <li class="first"><a href=""><img src="{{ skin('images/shop/f.jpg') }}" alt=""></a></li>

@foreach($goods_hot as $key => $goods)


@if($loop->iteration<9)

                            <li class="goods-item">
                                <div class="p-img"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" alt=""></a></div>
                                <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                <div class="p-price">

@if($goods['promote_price'])

                                		{{ $goods['promote_price'] }}

@else

                                    	{{ $goods['shop_price'] }}

@endif

                                </div>
                            </li>

@endif


@endforeach

                        </ul>
                    </div>
                </div>

@endif



@if($promotion_goods)

                <div class="shop-floor">
                    <div class="f-hd"><h2>{{ $lang['promotion_goods'] }}</h2></div>
                    <div class="f-bd">
                        <ul class="list clearfix">
                            <li class="first"><a href=""><img src="{{ skin('images/shop/f.jpg') }}" alt=""></a></li>

@foreach($promotion_goods as $promotion_goods)


@if($loop->iteration<9)

                            <li class="goods-item">
                                <div class="p-img"><a href="{{ $promotion_goods['url'] }}" title="{{ $promotion_goods['name'] }}" target="_blank"><img src="{{ $promotion_goods['thumb'] }}" alt=""></a></div>
                                <div class="p-name"><a href="{{ $promotion_goods['url'] }}" title="{{ $promotion_goods['name'] }}" target="_blank">{{ $promotion_goods['name'] }}</a></div>
                                <div class="p-price">

@if($promotion_goods['promote_price'])

                                    	{{ $promotion_goods['promote_price'] }}

@else

                                    	{{ $promotion_goods['shop_price'] }}

@endif

                                </div>
                            </li>

@endif


@endforeach

                        </ul>
                    </div>
                </div>

@endif

            </div>

        </div>
    </div>
    @include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
	$(function(){
		$(".shop-banner").slide({
			effect: 'leftLoop',
			mainCell: '.bd ul',
			titCell: '.hd ul',
			autoPage: true
		})
	})
    </script>
</body>
</html>
