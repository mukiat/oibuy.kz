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
    <div class="w w1200 clearfix">
        <div class="article-side">
            <dl class="article-menu">
                <dt class="am-t"><a href="#">{{ $lang['new_article'] }}</a></dt>
                <dd class="am-c">

@foreach($new_article as $article)

                    <div class="menu-item active">
                        <div class="item-hd"><a href="{{ $article['url'] }}"  title="{{ $article['title'] }}">{{ $article['title'] }}</a></div>
                    </div>

@endforeach

                </dd>

            </dl>
        </div>
        <div class="article-main">
            <div class="am-hd">
                <h2>{{ $ur_here }}</h2>
            </div>
            <div class="am-hd">
                {{ $region_name }}{{ $lang['self_advice_city_list'] }}：
                <div class="article-words">

@foreach($self_point as $key => $list)

                    <div><a href="#{{ $list['anchor'] }}" target="_blank">{{ $loop->iteration }}.{{ $list['name'] }}</a></div>

@endforeach

                </div>
            </div>
            <div class="am-bd">
                <div class="article-words">

@foreach($self_point as $key => $list)

                        <h3 id="haili" >{{ $list['name'] }}</h3>
                        <dl class="adr_list">
                            <span style="font-size:12px;">
                                {{ $lang['address'] }}：{{ $list['address'] }}<br>
                                {{ $lang['contact_phone'] }}：{{ $list['mobile'] }} <br>
                                {{ $lang['Arrival_route'] }}：{{ $list['line'] }}
                            </span>
                        </dl>
                        <div class="img"><img src="{{ $list['img_url'] }}" alt="{{ $list['name'] }}"></div>

@endforeach

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
    $(".article-side .side-goods").slide({
        effect: 'leftLoop'
    });
});
</script>
</body>
</html>
