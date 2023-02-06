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
    @include('frontend::library/page_header_common')
    <div class="content">
        <div class="w w1200">
			@include('frontend::library/ur_here')
            <div class="selector gb-selector">
                <div class="s-line">
                    <div class="s-l-wrap">
                        <div class="s-l-tit"><span>{{ $lang['category'] }}：</span></div>
                        <div class="s-l-value">
                            <div class="s-l-v-list">
                                <ul>
                                    <li
@if($cat_id == 0)
class="curr"
@endif
><a href="group_buy.php?act=list">{{ $lang['all_attribute'] }}</a></li>

@foreach($categories_pro as $cat)

                                    <li
@if($cat_id == $cat['id'])
class="curr"
@endif
><a href="group_buy.php?act=list&sort={{ $pager['search']['sort'] }}&cat_id={{ $cat['id'] }}#group_buy_list">{{ $cat['cat_alias_name'] }}</a></li>

@endforeach

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mod-list-sort">
                <form method="GET" class="sort" name="listform">
                    <div class="sort-l fl">
                    	<div class="sort-t">{{ $lang['sort'] }}：</div>
                        <a href="group_buy.php?act=list&cat_id={{ $cat_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort=act_id&order=
@if($pager['search']['sort'] == 'act_id' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item
@if($pager['search']['sort'] == 'act_id')
curr
@endif
">{{ $lang['default'] }}<i class="iconfont
@if($pager['search']['sort'] == 'act_id' && $pager['search']['order'] == 'DESC')
icon-down1
@else
icon-up1
@endif
"></i></a>
                        <a href="group_buy.php?act=list&cat_id={{ $cat_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort=start_time&order=
@if($pager['search']['sort'] == 'start_time' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item
@if($pager['search']['sort'] == 'start_time')
curr
@endif
">{{ $lang['Newest'] }}<i class="iconfont
@if($pager['search']['sort'] == 'start_time' && $pager['search']['order'] == 'DESC')
icon-down1
@else
icon-up1
@endif
"></i></a>
                        <a href="group_buy.php?act=list&cat_id={{ $cat_id }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['search']['page'] }}&sort=comments_number&order=
@if($pager['search']['sort'] == 'comments_number' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item
@if($pager['search']['sort'] == 'comments_number')
curr
@endif
">{{ $lang['Comment_number'] }}<i class="iconfont
@if($pager['search']['sort'] == 'comments_number' && $pager['search']['order'] == 'DESC')
icon-down1
@else
icon-up1
@endif
"></i></a>
                    </div>
                    <div class="f-search" id="submit-search">
                        <input name="keywords" type="text" class="text" value="{{ $pager['search']['keywords'] }}" placeholder="{{ $lang['Activity_name'] }}" />
                        <a href="javascript:void(0);" class="btn sc-redBg-btn ui-btn-submit">{{ $lang['assign'] }}</a>
                    </div>
                    <input type="hidden" name="act" value="list">
                    <input type="hidden" name="page" value="{{ $pager['page'] }}" />
                    <input type="hidden" name="sort" value="{{ $pager['search']['sort'] }}" />
                    <input type="hidden" name="order" value="{{ $pager['search']['order'] }}" />
                @csrf </form>
            </div>

@if($gb_list)

            <div class="gb-index-list">
                <ul class="clearfix" ectype="items">

@foreach($gb_list as $group_buy)

                    <li class="mod-shadow-card">
                        <a href="{!! $group_buy['url'] !!}" target="_blank" class="img"><img src="{{ $group_buy['goods_thumb'] }}" alt="{{ $group_buy['goods_name'] }}" title="{{ $group_buy['goods_name'] }}"></a>
                        <div class="clearfix">
                            <div class="price">{{ config('shop.currency_format', '¥') }}{{ $group_buy['price_ladder']['0']['price'] }}</div>
                            <div class="man">{{ $group_buy['cur_amount'] }}{{ $lang['people_participate'] }}</div>
                        </div>
                        <a href="{!! $group_buy['url'] !!}" target="_blank" class="name" title="{{ $group_buy['goods_name'] }}">{{ $group_buy['goods_name'] }}</a>
                        <div class="lefttime" data-time='{{ $group_buy['formated_end_date'] }}'>
                            <i class="iconfont icon-time"></i>
                            <span>{{ $lang['residue_time'] }}</span>
                            <span class="days"></span>
                            <em>:</em>
                            <span class="hours"></span>
                            <em>:</em>
                            <span class="minutes"></span>
                            <em>:</em>
                            <span class="seconds"></span>
                        </div>

@if($group_buy['is_end'] == 1)

                        <a href="{!! $group_buy['url'] !!}" class="gb-btn bid_end">{{ $lang['Group_purchase_end'] }}</a>

@else

                        <a href="{!! $group_buy['url'] !!}" class="gb-btn">{{ $lang['Group_purchase_now'] }}</a>

@endif

                    </li>

@endforeach

                </ul>
            </div>


@if($category_load_type)

            <div class="floor-loading goods-floor-loading" ectype="floor-loading"><div class="floor-loading-warp"><img src="{{ skin('images/load/loading.gif') }}"></div></div>

@else

            @include('frontend::library/pages')

@endif



@else

            <div class="no_records no_records_tc">
                <i class="no_icon_two"></i>
                <div class="no_info">
                    <h3>{{ $lang['information_null'] }}</h3>
                </div>
            </div>

@endif

        </div>
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

@if($category_load_type)
<script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
@endif

    <script type="text/javascript">
	$(function(){
		//倒计时
		$.goodsAjaxLoadType = function(){
			$(".lefttime").each(function(){
				$(this).yomi();
			});
		}
		$.goodsAjaxLoadType();


@if($category_load_type)

		var query_string = '{!! $query_string !!}';
		$.itemLoad('.gb-index-list','','',query_string,0);

@endif

	});
    </script>
</body>
</html>
