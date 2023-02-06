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

<body>
	@include('frontend::library/page_header_common')
    <div class="w w1200">
    	<div class="crumbs-nav">
            <div class="crumbs-nav-main clearfix">
                <div class="crumbs-nav-item">
                    <span>{{ $lang['all_attribute'] }}</span>
                    <span class="arrow">></span>

@if($display == 'list')

                    <span>{{ $lang['total'] }}{{ $shop_count }}{{ $lang['home_shop'] }}</span>

@else

                    <span>{{ $lang['total'] }}{{ $shop_count }}{{ $lang['jian_goods'] }}</span>

@endif

                </div>
            </div>
        </div>
    </div>
    <div class="container">
    	<div class="w w1200">
            <div class="filter">
            	<div class="filter-wrap">
                	<div class="filter-left">
                    	<div class="styles">
                            <ul class="items" ectype="fsortTab">
                                <li class="item
@if($display == 'list' && $sort == 'shop_id')
current
@endif
" data-type="store"><a href="search.php?keywords={{ $search_keywords }}&category={{ $category }}&store_search_cmt={{ $search_type }}&sort=shop_id&order={{ $order }}&display=list" title="{{ $lang['seller_store'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-store-alt"></span>{{ $lang['seller_store'] }}</a></li>
                                <li class="item
@if($display == 'grid')
current
@endif
" data-type="large"><a href="search.php?keywords={{ $search_keywords }}&category={{ $category }}&store_search_cmt={{ $search_type }}&sort={{ $sort }}&order={{ $order }}&display=grid" title="{{ $lang['big_pic'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-switch-grid"></span>{{ $lang['big_pic'] }}</a></li>
                                <li class="item
@if($display == 'text')
current
@endif
" data-type="samll"><a href="search.php?keywords={{ $search_keywords }}&category={{ $category }}&store_search_cmt={{ $search_type }}&sort={{ $sort }}&order={{ $order }}&display=text" title="{{ $lang['Small_pic'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-switch-list"></span>{{ $lang['Small_pic'] }}</a></li>
                            </ul>
                        </div>
                    </div>

@if($display != 'list')

                    <div class="filter-right">
                    	<div class="button-page">
                        	<span class="pageState"><span>{{ $page }}</span>/{{ $pager['page_count'] }}</span>
                            <a
@if($pager['page_next'])

@else
style="color:#666;"
@endif
 href="
@if($pager['page_next'])
{!! $pager['page_next'] !!}
@else
javascript:void(0);
@endif
"><i class="iconfont icon-left"></i></a>
                            <a
@if($pager['page_prev'])

@else
style="color:#666;"
@endif
 href="
@if($pager['page_prev'])
{!! $pager['page_prev'] !!}
@else
javascript:void(0);
@endif
"><i class="iconfont icon-right"></i></a>
                        </div>
                    </div>

@endif

                </div>
            </div>

@if($display == 'list')

            <div class="store-shop-list" id="store_shop_list">
            	<div class="ss-warp">

@forelse($store_shop_list as $key => $shop)

                    <div class="ss-item">
                        <div class="ss-info">
                            <div class="ss-i-info">
                                <div class="ss-i-top">
                                    <div class="logo"><a href="{{ $shop['shop_url'] }}"><img src="{{ $shop['logo_thumb'] }}"></a></div>
                                    <div class="r-info">
                                        <div class="ss-tit">{{ $shop['shopName'] }}

                                            <a  id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $shop['ru_id'] }}" class="p-kefu
@if($shop['ru_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>

										</div>

@if($shop['self_run'])

										<div class="seller-sr">{{ $lang['self_run'] }}</div>

@endif

										<div class="ss-desc">
                                            <p>{{ $lang['Main_brand'] }}：

@foreach($shop['brand_list'] as $brand)


@if(!$loop->last)

                                                    {{ $brand['brand_name'] }},

@else

                                                    {{ $brand['brand_name'] }}

@endif


@endforeach

                                            </p>
                                            <p>{{ $lang['seat_of'] }}：{!! $shop['address'] !!}</p>
                                        </div>
                                        <div class="ss-btn">
                                            <div class="fl mr5"><a href="{{ $shop['shop_url'] }}" class="btn">{{ $lang['enter_the_shop'] }}</a></div>
                                            <div class="fl" id="shop_button_{{ $shop['ru_id'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ss-i-bottom">
                                    <div class="ss-contrast">
                                        <div class="ssc-top">
                                            <span class="col1">{{ $lang['Store_score'] }}</span>
                                            <span class="col2">{{ $lang['goods'] }}</span>
                                            <span class="col3">{{ $lang['service'] }}</span>
                                            <span class="col4">{{ $lang['Deliver_goods'] }}</span>
                                        </div>
                                        <div class="ssc-content">
                                            <span class="col1">&nbsp;</span>
                                            <span class="col2">{{ $shop['merch_cmt']['cmt']['commentRank']['zconments']['score'] }}</span>
                                            <span class="col3">{{ $shop['merch_cmt']['cmt']['commentServer']['zconments']['score'] }}</span>
                                            <span class="col4">{{ $shop['merch_cmt']['cmt']['commentDelivery']['zconments']['score'] }}</span>
                                       </div>
                                        <div class="ssc-bottom">
                                            <span class="col1">{{ $lang['peer_comparison'] }}</span>
                                            <span class="col2">{{ $shop['merch_cmt']['cmt']['commentRank']['zconments']['goodReview'] }}%<i class="iconfont icon-arrow-
@if($shop['merch_cmt']['cmt']['commentRank']['zconments']['is_status'] == 1)
up
@elseif ($shop['merch_cmt']['cmt']['commentRank']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                                            <span class="col3">{{ $shop['merch_cmt']['cmt']['commentServer']['zconments']['goodReview'] }}%<i class="iconfont icon-arrow-
@if($shop['merch_cmt']['cmt']['commentServer']['zconments']['is_status'] == 1)
up
@elseif ($shop['merch_cmt']['cmt']['commentServer']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                                            <span class="col4">{{ $shop['merch_cmt']['cmt']['commentDelivery']['zconments']['goodReview'] }}%<i class="iconfont icon-arrow-
@if($shop['merch_cmt']['cmt']['commentDelivery']['zconments']['is_status'] == 1)
up
@elseif ($shop['merch_cmt']['cmt']['commentDelivery']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ss-i-goods" id="shop_goods_list_{{ $shop['ru_id'] }}">

                            </div>
                        </div>
                        <div class="s-more">
                            <span class="sm-wrap"><a href="{{ $shop['store_shop_url'] }}" target="_blank">{{ $lang['More_options'] }}<i class="iconfont icon-down"></i></a></span>
                        </div>
                    </div>

@empty

                    <div class="no_records no_records_1200">
                        <i class="no_icon_two"></i>
                        <div class="no_info">
                            <h3>{{ $lang['information_null'] }}</h3>
                        </div>
                    </div>

@endforelse

                </div>

@if($count > $size)

                <div class="w1200 pagePtb">
                    <div class="pages">
                        {!! $pager !!}
                    </div>
                </div>

@endif

            </div>

@else

            <div class="g-view w">
                <div class="store-shop-list">
                    <div class="goods-list" ectype="gMain">

@if($display == 'grid')

                            <ul class="gl-warp gl-warp-large">

@foreach($shop_goods_list as $goods)


@if($goods['goods_id'])

                                <li class="gl-item">
                                    <div class="gl-i-wrap">
                                        <div class="p-img"><a href="{{ $goods['goods_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" /></a></div>

@if($goods['pictures'])

                                        <div class="sider">
                                            <ul>

@foreach($goods['pictures'] as $picture)


@if($loop->index < 6)

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
                                            <div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                                        </div>
                                        <div class="p-name"><a href="{{ $goods['goods_url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                        <div class="p-store">
                                            <a href="{{ $goods['shop_url'] }}" title="{{ $goods['shop_name'] }}" class="store" target="_blank">{{ $goods['shop_name'] }}</a>

                                            <a  id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}" class="p-kefu"><i class="iconfont icon-kefu"></i></a>

                                        </div>

@if($goods['is_new'] || $goods['is_hot'] || $goods['is_best'] || $goods['is_shipping'] || $goods['self_run'])

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


@if($goods['is_shipping'])

                                                <span class="tag tac-by">
                                                <i class="i-left"></i>
                                                {{ $lang['Free_shipping'] }}
                                                <i class="i-right"></i>
                                            </span>

@endif


@if($goods['self_run'])

                                                <span class="tag tac-sr">
                                                <i class="i-left"></i>
                                                {{ $lang['self_run'] }}
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

                                                <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem2');" rev="{{ $goods['goods_thumb'] }}" data-dialog="addCart_dialog" data-id="" data-divid="addCartLog" data-url="" data-title="{{ $lang['select_attr'] }}" class="addcart">
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
                            @include('frontend::library/pages')
                        	<div id="flyItem" class="fly_item"><img src="" width="40" height="40"></div>

@elseif ($display == 'text')

                            <ul class="gl-warp gl-warp-large">

@foreach($shop_goods_list as $goods)


@if($goods['goods_id'])

                                <li class="gl-h-item
@if($loop->iteration % 2 != 0)
item_bg
@endif
">
                                    <div class="gl-i-wrap">
                                        <div class="col col-1">
                                            <div class="p-img"><a href="{{ $goods['goods_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" /></a></div>
                                            <div class="p-right">
												<div class="p-name fl"><a href="{{ $goods['goods_url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
@if($goods['self_run'])
<div class="seller-sr fl">{{ $lang['self_run'] }}</div>&nbsp;&nbsp;
@endif
{{ $goods['goods_name'] }}</a></div>
                                                <div class="p-lie">
                                                    <div class="p-num">{{ $lang['sales_volume'] }}：{{ $goods['sales_volume'] }}</div>
                                                    <div class="p-comm">{{ $lang['comments_rank'] }}：{{ $goods['cmt_count'] }} +</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col col-2">
                                            <div class="p-store">
                                                <a href="{{ $goods['store_url'] }}" title="{{ $goods['shop_name'] }}" target="_blank">{{ $goods['shop_name'] }}</a>

                                                <a  id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}" class="p-kefu"><i class="iconfont icon-kefu"></i></a>

                                            </div>
                                        </div>
                                        <div class="col col-3">
                                            <div class="p-price">
                                                <div class="shop-price">

@if($goods['promote_price'] != '')

                                                        {{ $goods['promote_price'] }}

@else

                                                        {{ $goods['shop_price'] }}

@endif

                                                </div>
                                                <div class="original-price">{{ $goods['market_price'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col col-4">
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

                                                <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem2');" rev="{{ $goods['goods_thumb'] }}" data-dialog="addCart_dialog" data-id="" data-divid="addCartLog" data-url="" data-title="{{ $lang['select_attr'] }}" class="addcart">
                                                    <i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}
                                                </a>

@else

                                                <a href="javascript:void(0);"  class="addcart"><i class="iconfont icon-carts"></i>{{ $lang['have_no_goods'] }}</a>

@endif


@else

                                            <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem2');" class="addcart" rev="{{ $goods['goods_thumb'] }}"><i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}</a>

@endif

                                            </div>
                                        </div>
                                    </div>
                                </li>

@endif


@endforeach

                            </ul>
                            @include('frontend::library/pages')
                        	<div id="flyItem2" class="fly_item2"><img src="" width="40" height="40"></div>

@endif


                        <div class="clear"></div>
                    </div>
                </div>
            </div>

@endif

			{{-- DSC 提醒您：动态载入recommend_merchants.lbi，显示首页推荐店铺小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $recommend_merchants]) !!}
        </div>
    </div>


    @include('frontend::library/duibi')


    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    <input name="script_name" value="{{ $script_name }}" type="hidden" />
	<input name="cur_url" value="{{ $cur_url }}" type="hidden" />
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


@if($store_shop_list)


@foreach($store_shop_list as $shop)

		    shop_list({{ $shop['ru_id'] }});

@endforeach


@endif

	});
    </script>
</body>
</html>
