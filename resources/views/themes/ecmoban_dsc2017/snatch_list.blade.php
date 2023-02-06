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
                        <form method="GET"  class="sort" name="listform">
                            <div class="s-l-tit"><span>{{ $lang['sort'] }}：</span></div>
                            <div class="s-l-value">
                                <div class="mod-list-sort">
                                    <div class="sort-l">
                                        <a href="snatch.php?act=list&sort=snatch_id&order=
@if($pager['search']['sort'] == 'snatch_id' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item <!--
@if($pager['search']['sort'] == 'snatch_id')
curr
@endif
">{{ $lang['default'] }}<i class="iconfont
@if($pager['search']['sort'] == 'snatch_id' && $pager['search']['order'] == 'DESC')
icon-down1
@else
icon-up1
@endif
"></i></a>
                                        <a href="snatch.php?act=list&sort=start_time&order=
@if($pager['search']['sort'] == 'start_time' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item <!--
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
                                        <a href="snatch.php?act=list&sort=end_time&order=
@if($pager['search']['sort'] == 'end_time' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif
" class="sort-item <!--
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
                                    <div class="f-search mr10" id="submit-search">
                                        <input name="keywords" type="text" class="text" value="{{ $pager['search']['keywords'] }}" placeholder="{{ $lang['goods_name'] }}" />
                                        <a href="javascript:void(0);" class="btn sc-redBg-btn ui-btn-submit">{{ $lang['assign'] }}</a>
                                    </div>
                                    <input type="hidden" name="act" value="list" />
                                    <input type="hidden" name="page" value="{{ $pager['page'] }}" />
                                    <input type="hidden" name="sort" value="{{ $pager['search']['sort'] }}" />
                                    <input type="hidden" name="order" value="{{ $pager['search']['order'] }}" />
                                </div>
                            </div>
                        @csrf </form>
                    </div>
                </div>
            </div>
            <div class="snatch-list-main">

@if($snatch_list)

                <div class="snatch-list">
                    <ul class="clearfix" ectype="items">

@foreach($snatch_list as $list)

                        <li class="mod-shadow-card">
                            <a href="{{ $list['url'] }}" class="img"><img src="{{ $list['goods_thumb'] }}" alt="{{ $list['snatch_name'] }}"></a>
                            <a href="{{ $list['url'] }}" class="name">{{ $list['snatch_name'] }}</a>
                            <div class="info">
                                <p><em>{{ $lang['current_price'] }}：</em><span class="price">{{ $list['formated_shop_price'] }}</span></p>
                                <p class="lefttime" data-time="{{ $list['snatch']['end_time_date'] }}">
                                    <span>{{ $lang['residual_time'] }}：</span>
                                    <span class="days">00</span>
                                    <em>:</em>
                                    <span class="hours">15</span>
                                    <em>:</em>
                                    <span class="minutes">40</span>
                                    <em>:</em>
                                    <span class="seconds">10</span>
                                </p>
                                <p><em>{{ $lang['bid_number'] }}：</em><span>{{ $list['price_list_count'] }}</span></p>
                            </div>

@if($list['current_time'] < $list['end_time'] && $list['current_time'] > $list['start_time'] )

                            <a href="{{ $list['url'] }}" target="_blank" class="sn-btn"><em></em>{{ $lang['me_bid'] }}<s></s></a>

@elseif ($list['current_time'] >= $list['end_time'] )

                            <a href="{{ $list['url'] }}" target="_blank" class="sn-btn bid_end"><em></em>{{ $lang['au_end'] }}<s></s></a>

@else

                            <a href="{{ $list['url'] }}" target="_blank" class="sn-btn bid_wait"><em></em>{{ $lang['Wait_au'] }}<s></s></a>

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

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info">
                        <h3>{{ $lang['information_null'] }}</h3>
                    </div>
                </div>

@endif

            </div>
        </div>
    </div>

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
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

		$(".snatch-hot-slide").slide({
			effect: "leftLoop",
			vis: 3,
			scroll: 1,
			autoPage: true,
			mainCell: ".bd ul"
		});


@if($category_load_type)

		var query_string = '{!! $query_string !!}';
		$.itemLoad('.snatch-list','','',query_string,0);

@endif

	});
    </script>
</body>
</html>
