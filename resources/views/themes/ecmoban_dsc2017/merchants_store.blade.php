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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/store_css.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/preview.css') }}">
</head>

<body>
	@include('frontend::library/page_header_common')
    <div class="shop-header">
    	@include('frontend::library/merchants_store_top')

@if($head_temp)

        	<div class="hd_main">
            {!! $head_temp !!}
            </div>

@else


@if(!$pc_page['out'])

            <div class="middle">
                <div class="w w1200">
                    <div class="logo-wp">
                        <div class="logo-text">
                            <a href="" class="shop-title">{{ $shop_name }}</a>
                            <p class="shop-intro" title="{{ $basic_info['street_desc'] }}">{{ $basic_info['street_desc'] }}</p>
                        </div>
                    </div>
                    <form method="get" action="{{ $shop_url }}{{ $store_param }}" class="shop-search" name="listform">
                        <input type="text" id="sp-keyword" name="keyword" value="{{ $keyword ?? '' }}" placeholder="{{ $lang['goods_keyword_store'] ?? '' }}" autocomplete="off">
                        <button id="btnShopSearch" type="submit">{{ $lang['search'] }}</button>
                        <input type="hidden" value="{{ $merchant_id }}" id="merchant_id" name="merchant_id">
                        <input type="hidden" value="{{ $cat_id }}" id="cat_id" name="cat_id">
                    @csrf </form>
                    <div class="shop-qrcode">
                        <img src="{{ $seller_qrcode_img }}" alt="" class="img" width="84">
                        <p>{{ $lang['shop_qrcode'] }}</p>
                    </div>
                </div>
            </div>

@endif

            <div class="bottom">
                <div class="w w1200">
                    <div class="shop-head-category">
                        <div class="all-cate"><a href="javascript:;">{{ $lang['all_seller_cat'] }}<i class="iconfont icon-liebiao"></i></a></div>

@if($all_cat_list)

                        <div class="cate-tab-content">

@foreach($all_cat_list as $cat)

                            <dl>
                                <dt><a href="{{ $cat['url'] }}">{{ $cat['cat_name'] }}</a></dt>

@foreach($cat['child_tree'] as $tree)

                                <dd><a href="{{ $tree['url'] }}">{{ $tree['name'] }}</a></dd>

@endforeach

                            </dl>

@endforeach

                        </div>

@endif

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

@endif

    </div>

	<div class="picScroll-left">
		<div class="hd">
			<ul></ul>
		</div>
		<div class="bd">
			{{-- DSC 提醒您：动态载入position_merchantsIn_users.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $adarr, 'id' => $merchant_id]) !!}
		</div>
	</div>

    <div class="seller-list-main">
        <div class="w w1200">

@if($cat_id > 0)


@if($brands['1'] || $price_grade['1'] || $filter_attr_list)

            <div class="selector">
            	<div class="s-line">
                	<div class="s-l-wrap">
                    	<div class="s-l-tit"><span>{{ $lang['search'] }}：</span></div>
                        <div class="s-l-value">
                        	<form method="get" action="{{ $shop_url }}{{ $store_param }}" class="shop-search" name="listform">
                                <input type="text" id="sp-keyword" name="keyword" value="{{ $keyword ?? '' }}" placeholder="{{ $lang['goods_keyword_store'] ?? '' }}" autocomplete="off">
                                <button id="btnShopSearch" type="submit">{{ $lang['search'] }}</button>
                                <input type="hidden" value="{{ $merchant_id }}" id="merchant_id" name="merchant_id">
                                <input type="hidden" value="{{ $cat_id }}" id="cat_id" name="cat_id">
                            @csrf </form>
                        </div>
                    </div>
                </div>

@if($brands['1'])

                <div class="s-line">
                    <div class="s-l-wrap">
                        <div class="s-l-tit"><span>{{ $lang['brand'] }}：</span></div>
                        <div class="s-l-value">
                            <div class="s-l-v-list">
                                <ul>

@foreach($brands as $brand)


@if($brand['selected'])

                                    <li><a href="javascript:;" class="shaixuan">{{ $brand['brand_name'] }}</a></li>

@else

                                    <li><a href="{{ $brand['url'] }}">{{ $brand['brand_name'] }}</a></li>

@endif


@endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="s-l-opt"></div>
                    </div>
                </div>

@endif


@if($price_grade['1'])

                <div class="s-line">
                    <div class="s-l-wrap">
                        <div class="s-l-tit"><span>{{ $lang['price'] }}：</span></div>
                        <div class="s-l-value">
                            <div class="s-l-v-list">
                                <ul>

@foreach($price_grade as $grade)


@if($grade['selected'])

                                        <li><a href="javascript:;" class="shaixuan">{{ $grade['price_range'] }}</a></li>

@else

                                        <li><a href="{{ $grade['url'] }}">{{ $grade['price_range'] }}</a></li>

@endif


@endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="s-l-opt"></div>
                    </div>
                </div>

@endif


@foreach($filter_attr_list as $key => $filter_attr)

                <div class="s-line">
                    <div class="s-l-wrap">
                        <div class="s-l-tit"><span>{{ $filter_attr['filter_attr_name'] }}：</span></div>
                        <div class="s-l-value">
                            <div class="s-l-v-list">
                                <ul>

@foreach($filter_attr['attr_list'] as $attr)


@if($attr['selected'])

                                        <li><a  href="javascript:;" class="shaixuan">{{ $attr['attr_value'] }}</a></li>

@else

                                        <li><a href="{{ $attr['url'] }}">{{ $attr['attr_value'] }}</a></li>

@endif


@endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="s-l-opt"></div>
                    </div>
                </div>

@endforeach

            </div>

@endif


@endif

            <div class="clearfix mt15">

@if($cat_store_list || $goods_hot || $basic_info['notice'])

                <div class="shop-list-side">

@if($cat_store_list)

                    <div class="side-box">
                        <h2>{{ $lang['seller_cat'] }}</h2>
                        <div class="side-menu">

@foreach($cat_store_list as $cat)

                            <dl class="j-slideToggle-wp
@if($loop->iteration == 1)
 active
@endif
">
                                <dt class="menu-level-1"><a href="{{ $cat['url'] }}">{{ $cat['cat_name'] }}</a>
@if($cat['child_tree'])
<i class="iconfont icon-xia j-slideToggle-handle"></i>
@endif
</dt>

@if($cat['child_tree'])

                                <dd class="j-slideToggle-con"
@if($loop->iteration == 1)
style="display: block;"
@endif
>

@foreach($cat['child_tree'] as $catchild)

                                    <p><a href="{{ $catchild['url'] }}">{{ $catchild['name'] }}</a></p>

@endforeach

                                </dd>

@endif

                            </dl>

@endforeach

                        </div>
                    </div>

@endif


@if($goods_hot)

                    <div class="side-box">
                        <h2>{{ $lang['hot_goods'] }}</h2>
                        <div class="side-recommend-hot">
                            <ul>

@foreach($goods_hot as $key => $goods)


@if($loop->iteration < 5)

                                <li>
                                    <div class="p-img"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" alt=""></a></div>
                                    <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                </li>

@endif


@endforeach

                            </ul>
                        </div>
                    </div>

@endif


@if($basic_info['notice'])

                    <div class="side-news">
                        <h2>{{ $lang['seller_notice'] }}</h2>
                        <ul>
                            <li><a href="javascript:;" style="cursor:default;">{{ $basic_info['notice'] }}</a></li>
                        </ul>
                    </div>

@endif

                </div>

@endif

                <div class="shop-list-view"
@if(!$cat_store_list && !$goods_hot && !$basic_info['notice'])
style="width:1200px"
@endif
>
                    <div class="filter">
                        <div class="filter-wrap">
                            <div class="filter-sort">
                                <a href="{{ $script_name }}.php?id={{ $category }}&keyword={{ $keyword ?? '' }}&merchant_id={{ $merchant_id ?? '' }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=goods_id&order=
@if($pager['sort'] == 'goods_id' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="
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
                                <a href="{{ $script_name }}.php?id={{ $category }}&keyword={{ $keyword ?? '' }}&merchant_id={{ $merchant_id ?? '' }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=sales_volume&order=
@if($pager['sort'] == 'sales_volume' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="
@if($pager['sort'] == 'sales_volume')
curr
@endif
">{{ $lang['sales_volume'] }}<i class="iconfont
@if($pager['sort'] == 'sales_volume' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
                                <a href="{{ $script_name }}.php?id={{ $category }}&keyword={{ $keyword ?? '' }}&merchant_id={{ $merchant_id ?? '' }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=shop_price&order=
@if($pager['sort'] == 'shop_price' && $pager['order'] == 'ASC')
DESC
@else
ASC
@endif
#goods_list" class="
@if($pager['sort'] == 'shop_price')
curr
@endif
">{{ $lang['price'] }}<i class="iconfont
@if($pager['sort'] == 'shop_price' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></i></a>
                                <a href="{{ $script_name }}.php?id={{ $category }}&keyword={{ $keyword ?? '' }}&merchant_id={{ $merchant_id ?? '' }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=last_update&order=
@if($pager['sort'] == 'last_update' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="
@if($pager['sort'] == 'last_update')
curr
@endif
">{{ $lang['New'] }}<i class="iconfont
@if($pager['sort'] == 'last_update' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
                            </div>
                            <div class="search-inshop">
                                <form method="get" action="{{ $shop_url }}{{ $store_param }}" name="listform">
                                    <div class="fl">
                                        <input id="sp-price" name="price_min" value="{{ $price_min }}" placeholder="￥" type="text" class="text inshop-p-s"> -
                                        <input id="sp-price1" name="price_max" value="{{ $price_max }}" placeholder="￥" type="text" class="text inshop-p-e">
                					</div>
                                    <input id="btnShopSearch" type="submit" class="sc-btn sc-redBg-btn" value="{{ $lang['search'] }}">
                                    <input type="hidden" value="{{ $merchant_id }}" id="merchant_id" name="merchant_id">
                                    <input type="hidden" value="{{ $cat_id }}" id="cat_id" name="cat_id">
                                @csrf </form>
                            </div>
                        </div>
                    </div>
                    <div class="g-view w">
                        <div class="goods-list" ectype="gMain"
@if(!$cat_store_list && !$goods_hot && !$basic_info['notice'])
 style="width:1230px; margin-left:-10px;"
@endif
>
                            <ul class="gl-warp gl-warp-large">

@forelse($goods_list as $key => $goods)

                                <li class="gl-item">
                                    <div class="gl-i-wrap">
                                        <div class="p-img"><a target="_blank" href="{{ $goods['url'] }}"><img class="item_hd_{{ $goods['goods_id'] }}" alt="{{ $goods['goods_name'] }}" src="{{ $goods['goods_img'] }}"/></a></div>
                                        <div class="sider">

@if($goods['goods_id'])


@if($goods['pictures'])

                                                <ul>

@foreach($goods['pictures'] as $picture)


@if($loop->index < 6)

                                                    <li
@if($loop->index == 0)
 class="curr"
@endif
 data="hd_{{ $goods['goods_id'] }}"><a href="javascript:void(0);"><img src="
@if($picture['thumb_url'])
{{ $picture['thumb_url'] }}
@else
{{ $picture['img_url'] }}
@endif
" alt="{{ $goods['goods_name'] }}" width="26" height="26" title="{{ $goods['goods_name'] }}" /></a></li>

@endif


@endforeach

                                                </ul>

@endif


@endif

                                        </div>
                                        <div class="p-lie">
                                            <div class="p-price">

@if($goods['promote_price'] != '')

                                                {{ $goods['promote_price'] }}

@else

                                                {{ $goods['shop_price'] }}

@endif

                                            </div>
                                            <div class="p-num">{{ $lang['sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                                        </div>
                                        <div class="p-name"><a target="_blank" title="{{ $goods['goods_name'] }}" href="{{ $goods['url'] }}">{{ $goods['goods_name'] }}</a></div>
                                    </div>
                                </li>

@empty

                                <div class="no_records">
                                    <i class="no_icon_two"></i>
                                    <div class="no_info">
                                        <h3>{{ $lang['information_null'] }}</h3>
                                    </div>
                                </div>

@endforelse

                            </ul>
                        </div>
						<div class="clear"></div>
						<div class="pages">
							@include('frontend::library/pages')
						</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="{{ $merchant_id }}" id="merchantId" class="merchantId" name="merchantId">
    @include('frontend::library/page_footer')

@if($is_jsonp)

    <script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/compare.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js//cart_common.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>

@else


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/compare.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>

@endif

    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
        $(function(){
            //导航添加链接
            var href = window.location.href
            $(".store_nav_right li").each(function(){
                var url = $(this).data('url');
                if(url == href){
                    $(this).find('a').addClass('nav_hover').siblings().find('a').removeClass("nav_hover");
                }
            })
            //点击切换下拉模块
            function slideToggle(){
                var wp = $(".j-slideToggle-wp");
                wp.each(function(i,el){
                    var $this = $(el);
                    var handle = $this['find'](".j-slideToggle-handle");
                    var con = $this['find'](".j-slideToggle-con");
                    handle.click(function(){
                        con.slideToggle();
                        $this['toggleClass']("active");
                    })
                })
            }
            slideToggle();

			$(document).on("mouseover", ".all_box", function () {
				var all_cats_tcc = $(".all_cats_tcc").html();
				all_cats_tcc = $.trim(all_cats_tcc);

				if(all_cats_tcc == ''){
					var merchant_id = $("#merchantId").val();
					Ajax.call('{{ url("/") }}/ajax_category.php', 'act=cat_store_list&merchant_id=' + merchant_id, cat_store_listResponse, 'GET', 'JSON');
				}
			});

			function cat_store_listResponse(data){
				$(".all_cats_tcc").html(data.content);
			}
        })
    </script>
</body>
</html>
