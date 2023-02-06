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
<body class="bonusBody">
    @include('frontend::library/page_header_common')

@if($cou_info)

    <div id="content" class="bonus_content">
        <div class="w w1200">
        	<div class="bonus_warp">
            	<div class="bonus_icon bonus_icon2"></div>
                <div class="bonus_info">
                	<div class="bonus_info_title">
                    	<h1>{{ $cou_info['cou_name'] }}</h1>
                        <span>
@if($cou_info['cou_type'] != 5)
{{ $lang['face_value'] }}：{{ $cou_info['type_money_formatted'] }}
@else
{{ $lang['vouchers_shipping'] }}
@endif
</span>
                    </div>
                	<div class="bonus_info_con">
                    	<p>{{ $lang['min_order_money'] }}：{{ $cou_info['min_goods_amount_formatted'] }}</p>

                        @if ($cou_info['valid_type'] == 2)
                            <p>{{ $lang['receive_start_time'] }}：{{ $cou_info['receive_start_time'] }}</p>
                            <p>{{ $lang['receive_end_time'] }}：{{ $cou_info['receive_end_time'] }}</p>
                        @else
                            <p>{{ $lang['use_start_time'] }}：{{ $cou_info['cou_start_date'] }}</p>
                            <p>{{ $lang['use_end_time'] }}：{{ $cou_info['cou_end_date'] }}</p>
                        @endif


@if($cou_info['usebonus_type'])

                        <p>{{ $lang['general_audience'] }}</p>

@else

                        <p>{{ $lang['only_limit'] }}{{ $cou_info['shop_name'] }}{{ $lang['use'] }}</p>

@endif


@if($cou_info['region_name'])
（{{ $lang['no_mail'] }}：{{ $cou_info['region_name'] }}）
@endif

                    </div>
                    <div class="bonus_info_btn">

@if($exist)

                        <input type="button" value="{{ $lang['already_received'] }}" class="sc-btn sc-btn-disabled btn30 w90 mt10">

@elseif (!$left)

                        <input type="button" value="{{ $lang['have_finished'] }}" class="sc-btn sc-btn-disabled btn30 w90 mt10">

@else

                        <input type="button" value="{{ $lang['receive'] }}" class="sc-btn sc-redBg-btn btn30 w90 mt10 get-coupon" cou_id="{{ request()->get('id') }}" data-coupon="1">

@endif

                    </div>
                </div>
            </div>
        </div>
    </div>

@else

    <div id="content" class="bonus_content">
        <div class="w w1200">
        	<div class="bonus_warp">
            	<div class="bonus_icon"></div>
                <div class="bonus_info">
                	<div class="bonus_notic">{{ $lang['bonus_notic'] }}</div>
                </div>
            </div>
        </div>
    </div>

@endif

    @include('frontend::library/page_footer')
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
</body>
</html>
