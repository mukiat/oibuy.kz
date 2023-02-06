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

<body class="bg-ligtGary">
@include('frontend::library/page_header_common')
<div class="content article-content">
	<div class="article-search-hd mb20">
    	<div class="w w1200">
            <div class="hd-tit">{{ $lang['article_help_center'] }}</div>
            <div class="hd-search">
                <form action="article_cat.php?article_id={{ $article_id }}" name="search_form" method="post" class="article_search">
                    <div class="f-search" id="submit-search">
                        <input name="keywords" type="text" id="requirement" value="{{ $search_value }}" class="text" placeholder="{{ $lang['search_placeholder'] }}" />
                        <input name="id" type="hidden" value="{{ $cat_id }}" />
                        <input name="cur_url" id="cur_url" type="hidden" value="article_cat.php" />
                        <a href="javascript:void(0);" class="ui-btn-submit" ectype="searchSubmit"><i class="iconfont icon-search"></i>{{ $lang['search'] }}</a>
                    </div>
                @csrf </form>
            </div>
        </div>
    </div>
    <div class="w w1200 clearfix">
        <div class="article-side">
            <dl class="article-menu">
                <dt class="am-t"><a href="javascript:void(0);">{{ $lang['article_cat_list'] }}</a></dt>
                <dd class="am-c">

@foreach($sys_categories as $sys_cat)

                    <div class="menu-item active">

                        <div class="item-hd"><a href="
@if($sys_child_cat['url'])
{{ $sys_child_cat['url'] }}
@else
javascript:void(0);
@endif
">{{ $sys_cat['name'] }}</a><i class="iconfont icon-down"></i></div>

@if($sys_cat['children'])

                                <ul class="item-bd">

@foreach($sys_cat['children'] as $key => $sys_child_cat)


                                        <li>


@if($sys_child_cat['children'])

                                                <div class="item-hd">
                                                    <a href="javascript:void(0)">{{ $sys_child_cat['name'] }}</a>
                                                    <i class="iconfont icon-down"></i>
                                                </div>
                                                <ul class="item-bd">

@foreach($sys_child_cat['children'] as $sys_c_c_cat)

                                                        <li><a href="{{ $sys_c_c_cat['url'] }}">{{ $sys_c_c_cat['name'] }}</a></li>

@endforeach

                                                </ul>

@else

                                                <a href="{{ $sys_child_cat['url'] }}">{{ $sys_child_cat['name'] }}</a>

@endif

                                        </li>

@endforeach

                                </ul>

@endif

                        </div>

@endforeach

                </dd>
                <dd class="am-c">

@foreach($custom_categories as $custom_cat)

                    <div class="menu-item active">
                        <div class="item-hd"><a href="{{ $custom_cat['url'] }}">{{ $custom_cat['name'] }}</a><i class="iconfont icon-up"></i></div>

@if($custom_cat['children'])

                        <ul class="item-bd" style="display:none;">

@foreach($custom_cat['children'] as $custom_child_cat)

                            <li>


@if($custom_child_cat['children'])

                                    <div class="item-hd">
                                        <a  href="javascript:void(0)">{{ $custom_child_cat['name'] }}</a>
                                        <i class="iconfont icon-down"></i>
                                    </div>
                                    <ul class="item-bd">

@foreach($custom_child_cat['children'] as $sys_c_c_cat)

                                            <li><a href="{{ $sys_c_c_cat['url'] }}">{{ $sys_c_c_cat['name'] }}</a></li>

@endforeach

                                    </ul>

@else

                                    <a href="{{ $custom_child_cat['url'] }}">{{ $custom_child_cat['name'] }}</a>

@endif

                            </li>

@endforeach

                        </ul>

@endif

                    </div>

@endforeach

                </dd>
            </dl>

@if($related_goods)

            <div class="side-goods">
                <a href="javascript:;" class="prev"><span class="iconfont icon-left"></span></a>
                <a href="javascript:;" class="next"><span class="iconfont icon-right"></span></a>
                <div class="bd">

@foreach($related_goods as $related_goods)

                    <div class="item">
                        <div class="p-img"><a href="{{ $related_goods['url'] }}" target="_blank"><img src="{{ $related_goods['goods_img'] }}" alt=""></a></div>
                        <div class="p-name"><a href="{{ $related_goods['url'] }}" title="{{ $related_goods['goods_name'] }}" target="_blank">{{ $related_goods['goods_name'] }}</a></div>
                        <div class="p-price">{{ $related_goods['shop_price'] }}</div>
                    </div>

@endforeach

                </div>
            </div>

@endif

        </div>
        <div class="article-main">
            <div class="am-hd">
                <h2>{{ $article['title'] }}</h2>
                @include('frontend::library/ur_here')
            </div>
            <div class="am-bd">
                <div class="article-words">

@if($article['content'] )

                    {!! $article['content'] !!}

@endif


@if($article['open_type'] == 2 || $article['open_type'] == 1)
<br />
                    <div><a href="{{ $article['file_url'] }}" target="_blank">{{ $lang['relative_file'] }}</a></div>

@endif

                </div>

@if($article['content'] )

				@include('frontend::library/article_comments')

@endif

                <div class="more_article">
                    <span class="art_prev">

@if($prev_article)

                        {{ $lang['prev_article'] }}：<a href="{{ $prev_article['url'] }}" >{{ $prev_article['title'] }}</a>

@endif

                    </span>
                    <span class="art_next">

@if($next_article)

                        {{ $lang['next_article'] }}：<a href="{{ $next_article['url'] }}" >{{ $next_article['title'] }}</a>

@endif

                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript">
$(function(){
    $("[ectype='searchSubmit']").on("click",function(){
            $(this).parents("form").submit();
        });
    $(".article-side .side-goods").slide({
        effect: 'leftLoop'
    });
});
</script>
</body>
</html>
