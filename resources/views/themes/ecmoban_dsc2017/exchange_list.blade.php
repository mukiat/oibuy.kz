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
        <div class="banner exchange-banner">
            <div class="w w1200 relative">
                {{-- DSC 提醒您：动态载入activity_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $activity_top_banner]) !!}
                <div class="exchange-score">

@if($user_id)

                    <div class="u-info">
                        <a href="user.php" class="u-avatar"><img src="
@if($info['user_picture'])
{{ $info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif
" alt=""></a>
                        <div class="u-name"><a href="user.php">{{ $info['username'] }}</a></div>
                    </div>
                    <div class="score-info">
                        <div class="item">
                            <p>{{ $lang['available_integral'] }}</p>
                            <div class="num">{{ $info['pay_points'] }}</div>
                        </div>
                        <div class="item">
                            <p>{{ $lang['balance_alt'] }}</p>
                            <div class="num">{{ $info['user_money'] }}</div>
                        </div>
                    </div>

@else

                    <div class="u-info">
                        <a href="user.php" class="u-avatar"><img src="{{ skin('images/touxiang.jpg') }}" alt=""></a>
                        <div class="u-name"><strong>Hi,{{ $lang['Welcome_to'] }}{{ $shop_name }}</strong></div>
                    </div>
                    <div class="score-info">
                        <a href="user.php" class="login-button">{{ $lang['please_login'] }}</a>
                        <a href="user.php?act=register" class="register_button">{{ $lang['reg_now'] }}</a>
                    </div>

@endif

                </div>
            </div>
        </div>
        <div class="exchange-cate">
            <div class="w w1200">
                <a href="exchange.php"
@if($cat_id == 0)
class="curr"
@endif
>{{ $lang['all'] }}</a><i class="point">·</i>

@foreach($categories_pro as $cat)

                <a
@if($cat_id == $cat['cat_id'])
class="curr"
@endif
 href="exchange.php?sort={{ $pager['search']['sort'] }}&cat_id={{ $cat['cat_id'] }}#exchange_list">
@if($cat['cat_alias_name'])
{{ $cat['cat_alias_name'] }}
@else
{{ $cat['cat_name'] }}
@endif
</a>
@if(!$loop->last)
<i class="point">·</i>
@endif


@endforeach

            </div>
        </div>
        <div class="exchange-main">
            <div class="w w1200">
                <div class="mod-list-sort">
                    <div class="sort-t">{{ $lang['sort'] }}：</div>
                    <div class="sort-l">
                        <a href="exchange.php?sort=goods_id&order=
@if($pager['search']['sort'] == 'goods_id' && $pager['search']['order'] == 'ASC')
DESC
@else
ASC
@endif
#exchange_list" class="sort-item
@if($pager['search']['sort'] == 'goods_id')
 curr
@endif
">{{ $lang['default'] }}<i class="iconfont
@if($pager['search']['sort'] == 'goods_id' && $pager['search']['order'] == 'ASC')
icon-up1
@else
icon-down1
@endif
"></i></a>
                        <a href="exchange.php?sort=sales_volume&order=
@if($pager['search']['sort'] == 'sales_volume' && $pager['search']['order'] == 'ASC')
DESC
@else
ASC
@endif
#exchange_list" class="sort-item
@if($pager['search']['sort'] == 'sales_volume')
curr
@endif
">{{ $lang['exchange_number'] }}<i class="iconfont
@if($pager['search']['sort'] == 'sales_volume' && $pager['search']['order'] == 'ASC')
icon-up1
@else
icon-down1
@endif
"></i></a>
                        <a href="exchange.php?sort=exchange_integral&order=
@if($pager['search']['sort'] == 'exchange_integral' && $pager['search']['order'] == 'ASC')
DESC
@else
ASC
@endif
#exchange_list" class="sort-item
@if($pager['search']['sort'] == 'exchange_integral')
curr
@endif
">{{ $lang['integral_value'] }}<i class="iconfont
@if($pager['search']['sort'] == 'exchange_integral' && $pager['search']['order'] == 'ASC')
icon-up1
@else
icon-down1
@endif
"></i></a>
                    </div>
                </div>

@if($goods_list)

                <div class="exchange-list">
                    <ul class="clearfix" ectype="items">

@foreach($goods_list as $goods)


@if($goods['goods_id'])

                                <li class="mod-shadow-card">
                                    <a  href="{!! $goods['url'] !!}" target="_blank" class="img"><img src="{{ $goods['goods_thumb'] }}" alt="{{ $goods['name'] }}"></a>
                                    <div class="clearfix">
                                        <div class="score">{{ $lang['integral'] }} {{ $goods['exchange_integral'] }}</div>
                                        <div class="market">{{ $goods['market_price'] }}</div>
                                    </div>
                                    <a  href="{!! $goods['url'] !!}" target="_blank" class="name" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a>
                                    <div class="already">
                                        <i class="iconfont icon-package"></i>
                                        {{ $goods['sales_volume'] ?? 0 }}{{ $lang['People_exchange'] }}
                                    </div>
                                    <a href="{!! $goods['url'] !!}" class="ex-btn" target="_blank">{{ $lang['Redeem_now'] }}</a>
                                </li>

@endif


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
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>

@if($category_load_type)

	<script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
	<script type="text/javascript">
	$(function(){
		var query_string = '{!! $query_string !!}';
		$.itemLoad('.exchange-list','','',query_string,0);
	});
    </script>

@endif

</body>
</html>
