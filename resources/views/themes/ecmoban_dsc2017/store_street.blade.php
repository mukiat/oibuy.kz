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
    <div class="content">
        <div class="banner street-banner">
            {{-- DSC 提醒您：动态载入store_street_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $store_street_ad]) !!}
        </div>
        <div class="street-main">
            <div class="w w1200">
                <div class="selector gb-selector street-filter-wapper">

@if($categories_pro)

                    <div class="s-line">
                        <div class="s-l-wrap">
                            <div class="s-l-tit"><span>{{ $lang['category'] }}：</span></div>
                            <div class="s-l-value">
                                <div class="s-l-v-list">
                                    <ul>
                                        <li class="curr"><a href="javascript:void(0);"  data-val="0" data-type="search_cat" data-region="6" ectype="street_area">{{ $lang['all_attribute'] }}</a></li>

@foreach($categories_pro as $cat)

                                        <li><a href="javascript:void(0);"  data-val="{{ $cat['id'] }}" data-type="search_cat" data-region="6" ectype="street_area">
@if($cat['cat_alias_name'])
{{ $cat['cat_alias_name'] }}
@else
{{ $cat['name'] }}
@endif
</a></li>

@endforeach

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

@endif


@if($province_list)

                    <div class="s-line">
                        <div class="s-l-wrap">
                            <div class="s-l-tit"><span>{{ $lang['province'] }}：</span></div>
                            <div class="s-l-value">
                                <div class="s-l-v-list">
                                    <ul>
                                        <li
@if(!$store_province)
class="curr"
@endif
><a href="javascript:void(0);" data-val="0" data-type="search_city" data-region="1" ectype="street_area">{{ $lang['all_attribute'] }}</a></li>

@foreach($province_list as $province)

                                        <li
@if($province['region_id'] == $store_province)
class="curr"
@endif
><a href="javascript:void(0);" data-val="{{ $province['region_id'] }}" data-type="search_city" data-region="1" ectype="street_area" >{{ $province['region_name'] }}</a></li>

@endforeach

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="store_city"></div>
                    <div id="store_district"></div>

@endif

                    <input name="store_user" id="res_store_user" value="" type="hidden" />
                    <input name="store_province" id="res_store_province" value="" type="hidden" />
                    <input name="store_city" id="res_store_city" value="" type="hidden" />
                    <input name="store_district" id="res_store_district" value="" type="hidden" />
                    <div class="s-line">
                        <div class="s-l-wrap">
                            <div class="s-l-tit"><span>{{ $lang['sort_order_street'] }}：</span></div>
                            <div class="s-l-value">
                                <div class="mod-list-sort">
                                    <div class="sort-l">
                                        <!--<a href="javascript:void(0);" class="sort-item curr" ectype="seller_sort" data-sort='shop_id' data-order='DESC'>{{ $lang['default'] }}</a>-->
                                        <a href="javascript:void(0);" class="sort-item" ectype="seller_sort" data-sort='sort_order' data-order='DESC'>{{ $lang['index_hot'] }}<i class="iconfont icon-up1"></i></a>
                                        <a href="javascript:void(0);" class="sort-item" ectype="seller_sort" data-sort='sales_volume' data-order='DESC'>{{ $lang['sales_volume'] }}<i class="iconfont icon-up1"></i></a>
                                        <a href="javascript:void(0);" class="sort-item" ectype="seller_sort" data-sort='store_score' data-order='DESC'>{{ $lang['score_street'] }}<i class="iconfont icon-up1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="street-list" ectype="store_shop_list" id="store_shop_list">
					@include('frontend::/library/store_shop_list')
                </div>
                <div class="sellerlist" ectype="pages_ajax" id="pages_ajax">
                	@include('frontend::/library/pages_ajax')
                </div>
            </div>
        </div>
    </div>
    <input name="area_list" value="" type="hidden" />
    <input name="user_id" value="{{ $user_id }}" type="hidden" />
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
	<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/masonry.pkgd.min.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/imagesloaded.pkgd.js') }}"></script>
    <script type="text/javascript">
        function street(){
            if($('.street-list').masonry){
                $('.street-list').masonry('destroy');
            }

			var masonryOptions = {
				columWidth: '.grid-sizer',
				gutter: '.gutter-sizer',
				itemSelector: '.street-list-item',
				percentPosition: true
			}

			var $grid = $('.street-list').masonry( masonryOptions );

			$grid['imagesLoaded']().progress(function() {
				$grid['masonry']();
			});
		}
		street();
    </script>
</body>
</html>
