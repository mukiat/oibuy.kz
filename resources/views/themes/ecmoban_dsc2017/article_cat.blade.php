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
                <form action="{{ $search_url }}" name="search_form" method="post" class="article_search">
                    <div class="f-search" id="submit-search">
                        <input name="keywords" type="text" id="requirement" value="{{ $search_value }}" class="text" placeholder="{{ $lang['search_placeholder'] }}" />
                        <input name="id" type="hidden" value="{{ $cat_id }}" />
                        <input name="cur_url" id="cur_url" type="hidden" value="" />
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
@if($sys_cat['url'])
{{ $sys_cat['url'] }}
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
        </div>
        <div class="article-main">

@if(!$search_value && $cat_info['show_in_nav'] == 1)

                <div class="am-hd">
                    <h2>{{ $cat_name }}</h2>
                </div>

@endif

            <div class="am-bd">
                <ul class="artilce-list">

@foreach($artciles_list as $key => $article)

                    <li>
                        <h3><a href="{{ $article['url'] }}" title="{{ $article['title'] }}">{{ $article['short_title'] }}</a></h3>
                        <p>{{ $article['description'] }}</p>
                    </li>

@endforeach

                </ul>
            </div>
            @include('frontend::library/pages')
        </div>
    </div>
</div>
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript">
	document.getElementById('cur_url').value = window.location.href;

	$(function(){
		$(".article-side .side-goods").slide({
			effect: 'leftLoop'
		});

		$("[ectype='searchSubmit']").on("click",function(){
			$(this).parents("form").submit();
		});
	});
</script>
</body>
</html>
