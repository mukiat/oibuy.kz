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
        <div class="act-banner">{{-- DSC 提醒您：动态载入activity_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $activity_top_banner]) !!}</div>
        <div class="auction-cate">
            <div class="w w1200">
                <a href="auction.php?cat_top_id=0"
@if($cat_top_id == 0)
class="curr"
@endif
>{{ $lang['all_auction'] }}</a>

@foreach($cat_top_list as $cat)

                <a href="auction.php?cat_top_id={{ $cat['cat_id'] }}"
@if($cat_top_id == $cat['cat_id'])
class="curr"
@endif
>{{ $cat['cat_alias_name'] }}</a>

@endforeach

            </div>
        </div>
        <div class="auction-main">
            <div class="w w1200">
                <div class="mod-list-sort">
                    <div class="sort-t">{{ $lang['sort'] }}：</div>
                    <div class="sort-l">
                        <a href="auction.php?
@if($cat_top_id != 0)
cat_top_id={{ $cat_top_id }}&
@endif
sort=act_id&order=
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
                        <a href="auction.php?
@if($cat_top_id != 0)
cat_top_id={{ $cat_top_id }}&
@endif
sort=start_time&order=
@if($pager['search']['sort'] == 'start_time' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item
@if($pager['search']['sort'] == 'start_time')
curr
@endif
">{{ $lang['start_time'] }}<i class="iconfont
@if($pager['search']['sort'] == 'start_time' && $pager['search']['order'] == 'DESC')
icon-down1
@else
icon-up1
@endif
"></i></a>
                        <a href="auction.php?
@if($cat_top_id != 0)
cat_top_id={{ $cat_top_id }}&
@endif
sort=end_time&order=
@if($pager['search']['sort'] == 'end_time' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item
@if($pager['search']['sort'] == 'end_time')
curr
@endif
">{{ $lang['coming_end'] }}<i class="iconfont
@if($pager['search']['sort'] == 'end_time' && $pager['search']['order'] == 'DESC')
icon-down1
@else
icon-up1
@endif
"></i></a>
                    </div>
                    <form method="GET" class="sort" name="listform">
                        <div class="f-search" id="submit-search">
                            <input name="keywords" type="text" class="text" value="{{ $pager['search']['keywords'] }}" placeholder="{{ $lang['goods_name'] }}" />
                            <a href="javascript:void(0);" class="btn sc-redBg-btn ui-btn-submit">{{ $lang['assign'] }}</a>
                        </div>
                        <input type="hidden" name="cat_top_id" value="{{ $cat_top_id ?? 0 }}" />
                        <input type="hidden" name="page" value="{{ $pager['page'] }}" />
                        <input type="hidden" name="sort" value="{{ $pager['search']['sort'] }}" />
                        <input type="hidden" name="order" value="{{ $pager['search']['order'] }}" />
                    @csrf </form>
                </div>
                <div class="auction-list">

@if($auction_list)

                    <ul class="clearfix" ectype="items">

@foreach($auction_list as $auction)

                        <li>
                            <a href="{!! $auction['url'] !!}" class="img" target="_blank"><img src="{{ $auction['goods_thumb'] }}" alt="{{ $auction['act_name'] }}" title="{{ $auction['act_name'] }}"></a>
                            <div class="info">
                                <a href="{!! $auction['url'] !!}" class="name" target="_blank" title="{{ $auction['goods_name'] }}">{{ $auction['act_name'] }}</a>
                                <div class="desc">
                                    <p>
                                        <span>{{ $lang['rz_shopName'] }}：</span>
                                        {{ $auction['rz_shop_name'] }}
                                    </p>
                                    <p>
                                        <span class="fr red">{{ $auction['count'] }}{{ $lang['au_number'] }}</span>
                                        <span>{{ $lang['residual_time'] }}：</span>
                                        <span class="
@if($auction['status_no'] == 1)
lefttime
@endif
" data-time="{{ $auction['end_time'] }}">
                                            <span class="days">00</span>
                                            <em>:</em>
                                            <span class="hours">00</span>
                                            <em>:</em>
                                            <span class="minutes">00</span>
                                            <em>:</em>
                                            <span class="seconds">00</span>
                                        </span>
                                    </p>
                                    <div class="timebar"><i style="width: 80%;"></i></div>
                                </div>
                                <div class="btn-wp">
                                    <div class="price">{{ $auction['formated_start_price'] }}</div>

@if($auction['status_no'] == 1)

                                    <a href="{!! $auction['url'] !!}" target="_blank" class="au-btn"><em></em>{{ $lang['me_bid'] }}<s></s></a>

@elseif ($auction['status_no'] == 2)


@if($auction['is_winner'])

                                            <a href="{!! $auction['url'] !!}" target="_blank" class="au-btn bid_wait"><em></em>{{ $lang['button_buy'] }}<s></s></a>

@else

                                            <a href="{!! $auction['url'] !!}" target="_blank" class="au-btn bid_end"><em></em>{{ $lang['au_end'] }}<s></s></a>

@endif


@endif

                                </div>
                           </div>
                        </li>

@endforeach

                    </ul>


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
        </div>
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
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
		$(".lefttime").each(function(){
			$(this).yomi();
		});


@if($category_load_type)

		var query_string = '{!! $query_string !!}';
		$.itemLoad('.auction-list','','',query_string,0);

@endif

	});
    </script>
</body>
</html>
