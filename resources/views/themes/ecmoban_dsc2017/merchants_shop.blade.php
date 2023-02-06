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
<link rel="stylesheet" type="text/css" href="{{ skin('css/suggest.css') }}" />
</head>

<body class="merchants_shop">
	@include('frontend::library/page_header_category')
    <div class="container">
    	<div class="w w1390">

@if($shop_info)

            <div class="shopCon">
                <div class="shopBox">
                    <div class="shopLeft">
                    	<a href="{{ $shop_info['store_url'] }}" target="_blank">
                            <div class="logo"><img src="{{ $basic_info['logo_thumb'] }}" width="95" height="95" /></div>
                            <div class="storeName">{{ $shop_info['shop_name'] }}</div>
                        </a>
                    </div>
                    <div class="shopHeader-info">
                        <p class="dp">{{ $lang['Main_brand'] }}：<span>

@foreach($brand_list as $brand)


@if(!$loop->last)

                                {{ $brand['brand_name'] }},

@else

                                {{ $brand['brand_name'] }}

@endif


@endforeach

                            </span></p>
                        <p>{{ $lang['seat_of'] }}：{!! $address !!}</p>
                    </div>
                    <div class="shopHeader-dsr">
                    	<dl class="dl1">
                            <dt>{{ $lang['Store_score'] }}</dt>
                            <dd>{{ $lang['goods'] }}</dd>
                            <dd>{{ $lang['service'] }}</dd>
                            <dd>{{ $lang['Invalid'] }}</dd>
                        </dl>
                        <dl class="dl2 g-s-parts">
                        	<dt>&nbsp;</dt>
                            <dd>{{ $merch_cmt['cmt']['commentRank']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></dd>
                            <dd>{{ $merch_cmt['cmt']['commentServer']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></dd>
                            <dd>{{ $merch_cmt['cmt']['commentDelivery']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></dd>
                        </dl>
                        <dl class="dl3">
                            <dt>{{ $lang['peer_comparison'] }}</dt>
                            <dd>{{ $merch_cmt['cmt']['commentRank']['zconments']['goodReview'] }}%</dd>
                            <dd>{{ $merch_cmt['cmt']['commentServer']['zconments']['goodReview'] }}%</dd>
                            <dd>{{ $merch_cmt['cmt']['commentDelivery']['zconments']['goodReview'] }}%</dd>
                        </dl>
                    </div>
                    <div class="shopHeader-enter">
                    	<a href="javascript:void(0);" ectype="collect_store" data-value='{"userid":{{ $user_id }},"storeid":{{ $merchant_id }}}' data-url="merchants_store.php?merchant_id={{ $merchant_id }}" class="btn"><i class="iconfont
@if($collect_store > 0)
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i>{{ $lang['follow_store'] }}</a>
                        <a href="{{ $shop_info['store_url'] }}" target="_blank" class="btn">{{ $lang['enter_the_shop'] }}</a>
                    </div>
                </div>
            </div>

@endif

            <div class="filter">
            	<div class="filter-wrap">
                	<div class="filter-sort">
                        <a href="merchants_store_shop.php?id={{ $merchant_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort=goods_id&order=
@if($pager['sort'] == 'goods_id' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="sort-item
@if($pager['sort'] == 'goods_id')
curr
@endif
">{{ $lang['default'] }}<i class="iconfont
@if($pager['sort'] == 'goods_id' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
                        <a href="merchants_store_shop.php?id={{ $merchant_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort=last_update&order=
@if($pager['sort'] == 'last_update' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="sort-item
@if($pager['sort'] == 'last_update')
curr
@endif
">{{ $lang['shelf_time'] }}<i class="iconfont
@if($pager['sort'] == 'last_update' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
                        <a href="merchants_store_shop.php?id={{ $merchant_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort=shop_price&order=
@if($pager['sort'] == 'shop_price' && $pager['order'] == 'ASC')
DESC
@else
ASC
@endif
#goods_list" class="sort-item
@if($pager['sort'] == 'shop_price')
curr
@endif
">{{ $lang['price'] }}<i class="iconfont
@if($pager['sort'] == 'shop_price' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
                    </div>
                    <div class="filter-right crumbTitle">{{ $lang['total'] }}<span>{{ $count }}</span>{{ $lang['Relevant_goods'] }}</div>
              </div>
            </div>

@if($goods_list)

            <div class="g-view w">
                <div class="goods-list goods-list-w1390">
                    <ul class="gl-warp gl-warp-large">

@foreach($goods_list as $goods)


@if($goods['goods_id'])

                        <li class="gl-item">
                            <div class="gl-i-wrap">
                                <div class="p-img"><a href="{{ $goods['goods_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" /></a></div>

@if($goods['pictures'])

                                <div class="sider">
                                    <a href="javascript:void(0);" class="sider-prev"><i class="iconfont icon-left"></i></a>
                                    <ul>

@foreach($goods['pictures'] as $picture)


@if($loop->index < 4)

                                        <li
@if($loop->index == 0)
 class="curr"
@endif
><img src="
@if($picture['thumb_url'])
{{ $picture['thumb_url'] }}
@else
{{ $picture['img_url'] }}
@endif
" width="26" height="26" /></li>

@endif


@endforeach

                                    </ul>
                                    <a href="javascript:void(0);" class="sider-next"><i class="iconfont icon-right"></i></a>
                                </div>

@endif

                                <div class="p-lie">
                                    <div class="p-price">

@if($goods['promote_price'] != '')

                                        {{ $goods['promote_price'] }}

@else

                                        {{ $goods['shop_price'] }}

@endif

                                    </div>
                                    <div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>件</div>
                                </div>
                                <div class="p-name"><a href="{{ $goods['goods_url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
                                <div class="p-store">
                                    <a href="{{ $goods['store_url'] }}" title="{{ $goods['shop_name'] }}" class="store" target="_blank">{{ $goods['shop_name'] }}</a>

                                    <a  id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}" class="p-kefu
@if($goods['user_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>

                                </div>

@if($goods['is_new'] || $goods['is_hot'] || $goods['is_best'])

                                <div class="p-activity">

@if($goods['is_new'])

                                    <span class="tag tac-mn">
                                        <i class="i-left"></i>
                                        {{ $lang['is_new'] }}
                                        <i class="i-right"></i>
                                    </span>

@endif


@if($goods['is_hot'])

                                    <span class="tag tac-mh">
                                        <i class="i-left"></i>
                                        {{ $lang['is_hot'] }}
                                        <i class="i-right"></i>
                                    </span>

@endif


@if($goods['is_best'])

                                    <span class="tag tac-mb">
                                        <i class="i-left"></i>
                                        {{ $lang['is_best'] }}
                                        <i class="i-right"></i>
                                    </span>

@endif

                                </div>

@else

                                <div class="p-activity">&nbsp;</div>

@endif

                                <div class="p-operate">
                                    <a href="javascript:void(0);" id="compareLink">
                                        <input id="{{ $goods['goods_id'] }}" type="checkbox" name="duibi" class="ui-checkbox" onClick="Compare.add(this, {{ $goods['goods_id'] }},'{{ $goods['goods_name'] }}','{{ $goods['type'] }}', '{{ $goods['goods_thumb'] }}', '{{ $goods['shop_price'] }}', '{{ $goods['market_price'] }}')">
                                        <label class="ui-label" for="{{ $goods['goods_id'] }}">{{ $lang['compare'] }}</label>
                                    </a>
                                    <a href="javascript:collect({{ $goods['goods_id'] }});" class="choose-btn-coll
@if($goods['is_collect'])
 selected
@endif
"><i class="iconfont
@if($goods['is_collect'])
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i>{{ $lang['btn_collect'] }}</a>

@if($goods['prod'] == 1)


@if($goods['goods_number'] > 0)

                                        <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem');" rev="{{ $goods['goods_thumb'] }}"data-dialog="addCart_dialog" data-id="" data-divid="addCartLog" data-url="" data-title="{{ $lang['select_attr'] }}" class="addcart">
                                            <i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}
                                        </a>

@else

                                        <a href="javascript:void(0);"  class="addcart"><i class="iconfont icon-carts"></i>{{ $lang['have_no_goods'] }}</a>

@endif


@else

                                        <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem');" class="addcart" rev="{{ $goods['goods_thumb'] }}"><i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}</a>

@endif

                                </div>
                            </div>
                        </li>

@endif


@endforeach

                    </ul>

@if(!$category_load_type)


                    @include('frontend::library/pages')


@endif

                    <div class="clear"></div>
                </div>
                <div id="flyItem" class="fly_item"><img src="" width="40" height="40"></div>
            </div>
            <input type="hidden" value="{{ $region_id ?? 0 }}" id="region_id" name="region_id">
            <input type="hidden" value="{{ $area_id ?? 0 }}" id="area_id" name="area_id">
            <input type="hidden" value="{{ $area_city ?? 0 }}" id="area_city" name="area_city">
            <input name="script_name" value="{{ $script_name }}" type="hidden" />
			<input name="merchant_id" value="{{ $merchant_id }}" type="hidden" />

@else

            <div class="no_records no_records_tc">
            	<i class="no_icon_two"></i>
                <div class="no_info">
            		<h3>{{ $lang['information_null'] }}</h3>
                </div>
            </div>

@endif


@if($store_best_list)

            <div class="p-panel-main guess-love">
            	<div class="ftit ftit-delr"><h3>{{ $lang['guess_love'] }}</h3></div>
                <div class="gl-list clearfix">
                	<ul class="clearfix">

@foreach($store_best_list as $goods)

                    	<li class="opacity_img">
                        	<div class="p-img"><a href="{{ $goods['goods_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="190" height="190"></a></div>
                            <div class="p-price">

@if($goods['promote_price'] != '')

                                    {{ $goods['promote_price'] }}

@else

                                    {{ $goods['shop_price'] }}

@endif

                            </div>
                            <div class="p-name"><a href="{{ $goods['goods_url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
                        	<div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                        </li>

@endforeach

                    </ul>
                </div>
            </div>

@endif

        </div>
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position(['ru_id' => $shop_info['ru_id']]) !!}

	@include('frontend::library/duibi')

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/compare.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
	<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
	<script type="text/javascript">
	$(function(){
		$(".gl-i-wrap").slide({mainCell:".sider ul",effect:"left",pnLoop:false,autoPlay:false,autoPage:true,prevCell:".sider-prev",nextCell:".sider-next",vis:5});

		//对比初始化
		Compare.init();
	});
    </script>
</body>
</html>
