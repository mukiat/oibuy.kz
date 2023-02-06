<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ lang('admin/common.cp_home') }} - @yield('title') </title>
    {!! global_assets('css', 'wechat', 1, 'mobile') !!}
    <script type="text/javascript">var ROOT_URL = '{{ url('/') }}';</script>
    {!! global_assets('js', 'wechat', 1, 'mobile') !!}

    @if(config('shop.lang') == 'en')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/en.css') }}"/>
    @endif

    <link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}"/>
    <script src="{{ asset('js/calendar/calendar.min.js') }}"></script>

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
        // 操作提示
        $("#explanationZoom").on("click", function () {
            var explanation = $(this).parents(".explanation");
            var width = $(".content_tips").width();
            if ($(this).hasClass("shopUp")) {
                $(this).removeClass("shopUp");
                $(this).attr("title", "{{ lang('admin/common.fold_tips') }}");
                explanation.find(".ex_tit").css("margin-bottom", 10);
                explanation.animate({
                    width: width - 0
                }, 300, function () {
                    $(".explanation").find("ul").show();
                });
            } else {
                $(this).addClass("shopUp");
                $(this).attr("title", "{{ lang('admin/common.relevant_setup_operation') }}");
                explanation.find(".ex_tit").css("margin-bottom", 0);
                explanation.animate({
                    width: "118"
                }, 300);
                explanation.find("ul").hide();
            }
        });

    });
</script>
</body>
</html>
