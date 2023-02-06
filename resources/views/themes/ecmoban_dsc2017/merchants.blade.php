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

<body class="merchants bg-ligtGary">
@include('frontend::library/page_header_merchants')
<div class="container settled-container">
    {{-- DSC 提醒您：merchants_index_top_ad.lbi，入驻首页头部小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $merchants_index_top]) !!}
    <div class="sett-section s-section-step">
        <div class="w w1200">
            <div class="sett-title">
                <div class="zw-tit">
                    <h3>{{ $lang['merchants_step'] }}</h3>
                    <span class="line"></span>
                </div>
                <span class="yw-tit">ADVANCE REGISTRATION PROCESS</span>
            </div>
            <div class="sett-warp">
                <div class="item item-one">
                    <div class="item-i"><i></i></div>
                    <div class="tit">1 {{ $lang['sett_step_one'] }}</div>
                    <span>{{ $lang['sett_step_one_tit'] }}</span>
                    <span>{{ $lang['sett_step_one_desc'] }}</span>
                </div>
                <em class="item-jt"></em>
                <div class="item item-two">
                    <div class="item-i"><i></i></div>
                    <div class="tit">2 {{ $lang['sett_step_two'] }}</div>
                    <span>{{ $lang['sett_step_two_tit'] }}</span>
                    <span>{{ $lang['sett_step_two_desc'] }}</span>
                </div>
                <em class="item-jt"></em>
                <div class="item item-three">
                    <div class="item-i"><i></i></div>
                    <div class="tit">3 {{ $lang['sett_step_three'] }}</div>
                    <span>{{ $lang['sett_step_three_tit'] }}</span>
                    <span>{{ $lang['sett_step_three_desc'] }}</span>
                </div>
                <em class="item-jt"></em>
                <div class="item item-four">
                    <div class="item-i"><i></i></div>
                    <div class="tit">4 {{ $lang['sett_step_four'] }}</div>
                    <span>{{ $lang['sett_step_four_tit'] }}</span>
                    <span>{{ $lang['sett_step_four_desc'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="sett-section s-section-cate">
        <div class="w w1200">
            <div class="sett-title">
                <div class="zw-tit">
                    <h3>{{ $lang['hot_recruit'] }}</h3>
                    <span class="line"></span>
                </div>
                <span class="yw-tit">BUSINESS CATEGORY</span>
            </div>
            <div class="sett-warp">

@foreach($categories_pro as $cat)

                {{-- DSC 提醒您：merchants_index_category_ad.lbi，入驻首页类目小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $merchants_index_category_ad, 'id' => $cat['id']]) !!}

@endforeach

            </div>
        </div>
    </div>
    <div class="sett-section s-section-case">
        <div class="w w1200">
            <div class="sett-title">
                <div class="zw-tit">
                    <h3>{{ $lang['success_case'] }}</h3>
                    <span class="line"></span>
                </div>
                <span class="yw-tit">SUCCESSFUL CASE</span>
            </div>
            <div class="sett-warp">
                {{-- DSC 提醒您：merchants_index_case_ad.lbi，入驻首页类目小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $merchants_index_case_ad]) !!}
            </div>
        </div>
    </div>
    <div class="sett-section s-section-help">
        <div class="w w1200">
            <div class="sett-title">
                <div class="zw-tit">
                    <h3>{{ $lang['common_problem'] }}</h3>
                    <span class="line"></span>
                </div>
                <span class="yw-tit">COMMON PROBLEM</span>
            </div>
            <div class="sett-warp">

@foreach($articles_imp as $art)

                <div class="item item-
@if($loop->iteration % 2 == 0)
left
@else
right
@endif
">
                    <div class="number">0{{ $loop->iteration }}</div>
                    <div class="info">
                        <div class="name">
                            <div class="tit"><a href="article.php?id={{ $art['article_id'] }}" target="_blank">{{ $art['title'] }}</a></div>
                            <div class="desc">{{ $art['description'] }}</div>
                        </div>
                    </div>
                </div>

@endforeach

            </div>
        </div>
    </div>
</div>
@include('frontend::library/page_footer_flow')
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
</body>
</html>

