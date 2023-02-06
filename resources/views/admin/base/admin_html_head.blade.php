<!DOCTYPE html>
<html lang="zh-Hans">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page_title ?? lang('admin/common.cp_home') }} @if(!empty($ur_here)) - {{ $ur_here }} @endif</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />

    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/admin/css/main.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/admin/css/iconfont.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/admin/css/font-awesome.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/admin/css/purebox.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/js/jquery-ui/jquery-ui.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/js/spectrum-master/spectrum.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/js/perfect-scrollbar/perfect-scrollbar.min.css') }}" />

    @if(config('shop.lang') == 'en')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/en.css') }}" />
    @endif

    <script type="text/javascript" src="{{ asset('/js/jquery-1.12.4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery.json.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/transport_jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/utils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery.form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery.nyroModal.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery.validation.min.js ') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery.cookie.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/lib_ecmobanFunc.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/jquery-ui/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/admin/js/common.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/admin/js/listtable.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/admin/js/listtable_pb.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/admin/js/dsc_admin2.0.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/admin/js/jquery.bgColorSelector.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/admin/js/jquery.SuperSlide.2.1.1.js') }}"></script>

    <script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }});
    </script>
    <link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}" />
    <script src="{{ asset('js/calendar/calendar.min.js') }}"></script>
</head>

<body class="iframe_body">
