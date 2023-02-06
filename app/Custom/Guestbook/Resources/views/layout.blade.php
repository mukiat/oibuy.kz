<!DOCTYPE html>
<html lang="zh-Hans">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('title') </title>
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('assets/mobile/vendor/bootstrap/css/bootstrap.css') }}" rel="stylesheet">

    <!-- jQuery (Bootstrap 的所有 JavaScript 插件都依赖 jQuery，所以必须放在前边) -->
    <script src="{{ asset('assets/mobile/vendor/common/jquery.min.js') }}"></script>
    <!-- 加载 Bootstrap 的所有 JavaScript 插件。你也可以根据需要只加载单个插件。 -->
    <script src="{{ asset('assets/mobile/vendor/bootstrap/js/bootstrap.min.js') }}"></script>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    {{--头部引入js--}}
    @stack('scripts')

</head>
<body>

{{--正文--}}
@yield('content')

{{--底部引入js--}}
@stack('footer_scripts')

<script type="text/javascript">
    $(function () {
        // 公用js

    });
</script>
</body>
</html>
