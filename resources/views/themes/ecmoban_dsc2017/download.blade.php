<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0,viewport-fit=cover">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{ $page_title ?? config('shop.shop_name')  }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="{{ config('shop.shop_keywords') }}"/>
    <meta name="description" content="{{ config('shop.shop_desc') }}"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}"/>

    @include('http.js_languages_new')

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <style>
        .cont-wrapper {
            width: 100%;
            height: auto;
            min-height: 700px;
            vertical-align: middle;
            text-align: center;
        }
        .cont-wrapper img {
            width: 100%;
            margin: 0 auto;
        }
    </style>

</head>
<body>

<div class="header">
    <div class="w w1200">
        <div class="logo">
            <div class="logoImg"><a href="{{ url('/') }}"><img src="@if(!empty($shop_logo)) {{ $shop_logo }} @else {{ skin('/images/logo.gif') }}@endif" /></a></div>
        </div>
    </div>
</div>

<div class="container">
    <div class="w cont-wrapper">
        <img src="{{ $download_img ?? '' }}" >
    </div>
</div>

<div class="footer " style="margin-top: 0;">
    <div class="footer-new-bot">
        <div class="w w1200">
            @if(config('shop.icp_number'))
                <p>
                    <span>{{ __('common.icp_number') }}:</span><a href="http://beian.miit.gov.cn/" target="_blank">{{ config('shop.icp_number', '') }}</a>
                </p>
            @endif

            <p>{!! $copyright !!}</p>

            @if(config('shop.icp_file') && config('shop.icp_number'))
                <p class="copyright_auth"><a href="http://beian.miit.gov.cn/" target="_blank"><img src="{{ config('shop.icp_file') }}" title="{{ config('shop.icp_number', '') }}" width="36" height="36"/></a></p>
            @endif

            @if(config('shop.stats_code'))
                <p style="display:none;">{!! html_out(config('shop.stats_code', '')) !!}</p>
            @endif

        </div>
    </div>
</div>
</body>
</html>
