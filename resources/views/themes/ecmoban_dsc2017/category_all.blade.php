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
            <div class="crumbs-nav">
                <div class="crumbs-nav-main clearfix">
                    <div class="crumbs-nav-item">
                        @include('frontend::library/ur_here')
                    </div>
                </div>
            </div>
            <div class="catalog-main clearfix">
                <div class="catalog-side">
                    <div class="catalog-menu">
                        <ul class="menu-list">

@foreach($categories_pro as $categories)

                            <li
@if($loop->first)
class="current"
@endif
><a href="javascript:void(0);">
@if($loop->iteration < 10)
0
@endif
{{ $loop->iteration }} {{ $categories['nolinkname'] }}</a></li>

@endforeach

                        </ul>
                        <div class="back-top-wp">
                            <a href="javascript:;" class="back-top" ectype="returnTop">{{ $lang['returnTop'] }}</a>
                        </div>
                    </div>
                </div>
                <div class="catalog-detail">

@foreach($categories_list as $categories)

                    <div class="catalog-item">
                        <h2><a href="{!! $categories['url'] !!}" target="_blank">
@if($loop->iteration < 10)
0
@endif
{{ $loop->iteration }} {!! $categories['name'] !!}</a></h2>

@foreach($categories['cat_list'] as $child)

                        <h3><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a></h3>
                        <ul class="h4 clearfix">

@foreach($child['cat_list'] as $cat)

                            <li><a href="{{ $cat['url'] }}" target="_blank">{{ $cat['cat_name'] }}</a></li>

@endforeach

                        </ul>

@endforeach

                        <div class="catalog-item-ad clearfix">
                            {{-- DSC 提醒您：动态载入category_all_right.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $category_all_right, 'id' => $categories['id'], 'ru_id' => $ru_id]) !!}
                        </div>
                    </div>

@endforeach

                </div>
            </div>
        </div>
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>

<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
	<script type="text/javascript">
        $(function(){
            var items = $(".catalog-menu");
            var top = parseInt(items.offset().top);
            var allFloor = $(".catalog-detail");
            var floors = allFloor.find(".catalog-item");
            var height = parseInt(items.height());
            var IE6 = navigator.userAgent.indexOf("MSIE 6.0")>0;
            var IE7 = navigator.userAgent.indexOf("MSIE 7.0")>0;

            $(window).on('scroll',function(){
                var scrollTop = $(window).scrollTop();
                if(scrollTop>top){
                    items.css({'position':'fixed','top':0});
                }else{
                    items.removeAttr("style");
                }

                for(var i=0;i<floors.length;i++){
                    var floorsTop = floors.eq(i).position().top;
                    if(IE6||IE7){
                        floorsTop = floorsTop.toString();
                        floorsTop = floorsTop.substring(0,floorsTop.length-2);
                    }
                    if(scrollTop>floorsTop){
                        items.find("li").eq(i).addClass("current").siblings().removeClass("current");
                        floors.eq(i).addClass("curr").siblings().removeClass("curr");
                    }
                }
            });

            $(".catalog-menu .menu-list li").on('click',function(){
                var index = $(this).index();
                var top = floors.eq(index).offset().top;
                $("body,html").stop().animate({scrollTop:top});
            });
        })
    </script>
</body>
</html>
