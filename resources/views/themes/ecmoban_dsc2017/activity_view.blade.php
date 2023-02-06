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
        {{-- DSC 提醒您：动态载入activity_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $activity_top_banner]) !!}
        <div class="activity-main">
            <div class="w w1200">
                <div class="activity-main-title">
                    <h2>{{ $activity['act_name'] }}</h2>
                    <p>{{ $lang['consume'] }}{{ $activity['min_amount'] }}{{ $activity['act_type'] }}
@if($activity['actType'] != 0)
{{ $activity['act_type_ext'] }}
@endif
</p>
                </div>
                <ul class="activity-list clearfix">
                    <li class="mod-shadow-card li-col2">
                        <div class="shop-info">
                            <div class="shop-name">
                                <a href="" class="s-name">{{ $activity['shop_name'] }}</a>
                                <a href="" class="kefu"><i class="icon i-kefu"></i></a>
                            </div>
                            <dl>
                                <dt>{{ $lang['activity_time'] }}：</dt>
                                <dd>{{ $activity['start_time'] }} ~ {{ $activity['end_time'] }}</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['activity_type'] }}：</dt>
                                <dd>{{ $activity['act_type'] }}
@if($activity['actType'] != 0)
&nbsp;{{ $activity['act_type_ext'] }}
@endif
</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['max_amount'] }}：</dt>
                                <dd>{{ $activity['min_amount'] }}{{ $lang['money_unit'] }}
@if($activity['max_amount'] > 0)
 ~ {{ $activity['max_amount'] }}
@endif
</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['user_rank'] }}：</dt>
                                <dd>
@foreach($activity['user_rank'] as $rank)
{{ $rank }}&nbsp;
@endforeach
</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['act_range_type'] }}：</dt>
                                <dd>{{ $activity['act_range'] }}

@if($activity['act_range_ext'] && $activity['act_range_type'] != 3)

									<div class="shop-brand">

@foreach($activity['act_range_ext'] as $range_ext)


@if($activity['act_range_type'] == 1)

										<a href="category.php?id={{ $range_ext['id'] }}" target="_blank">{{ $range_ext['name'] }}</a>

@elseif ($activity['act_range_type'] == 2)

										<a href="brand.php?id={{ $range_ext['id'] }}" target="_blank">{{ $range_ext['name'] }}</a>

@endif


@endforeach

									</div>

@endif

								</dd>
                            </dl>
                        </div>
                    </li>
					@include('frontend::/library/activity_goods_list')
                </ul>
				@include('frontend::library/pages')
                <div id="flyItem" class="fly_item"><img src="" width="40" height="40"></div>

@if($activity['gift'])

                <div class="activity-main-title">
                    <h2>{{ $lang['activity_gift'] }}</h2>
                </div>
				<ul class="activity-list clearfix">

@foreach($activity['gift'] as $goods)

                    <li class="mod-shadow-card">
                        <a href="goods.php?id={{ $goods['id'] }}" target="_blank" class="img"><img src="{{ $goods['thumb'] }}" alt="{{ $goods['name'] }}"></a>
                        <a href="goods.php?id={{ $goods['id'] }}" target="_blank" class="name">{{ $goods['name'] }}</a>
                        <div class="price">{{ $goods['price'] }}</div>
                    </li>

@endforeach

				</ul>

@endif

            </div>
        </div>
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
</body>
</html>
