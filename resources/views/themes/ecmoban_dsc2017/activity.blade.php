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
    <div class="content">
        <div class="activity-index-main">
            <div class="w w1200">
                <div class="activity-index-filter" ectype="actFilter">
                    <a href="javascript:;" data-acttype="-1" class="curr">{{ $lang['all_activity'] }}</a>
                    <a href="javascript:;" data-acttype="0">{{ $lang['activity_gift'] }}</a>
                    <a href="javascript:;" data-acttype="1">{{ $lang['activity_reduction'] }}</a>
                    <a href="javascript:;" data-acttype="2">{{ $lang['activity_discount'] }}</a>
                </div>
                <ul class="activity-index-list clearfix" ectype="actList">

@foreach($activity_list as $key => $list)


@foreach($list['activity_list'] as $akey => $activity)

						<li class="mod-shadow-card" data-acttype="{{ $activity['actType'] }}">
							<span class="tag
@if($activity['actType'] == 0)
tag-zp
@elseif ($activity['actType'] == 1)
tag-jm
@elseif ($activity['actType'] == 2)
tag-zk
@endif
">{{ $list['activity_name'] }}</span>
                            <div class="img">
                                <a href="activity.php?act=view&act_id={{ $activity['act_id'] }}">
                                    <img src="
@if($activity['activity_thumb'])
{{ $activity['activity_thumb'] }}
@else
images/noactivity.png
@endif
" alt="">
                                </a>
                            </div>
							<div class="title">{{ $activity['act_name'] }}</div>
							<div class="time">{{ $activity['start_time'] }} ~ {{ $activity['end_time'] }}</div>
							<div class="discount">{{ $lang['consume'] }}<span class="red">{{ $activity['min_amount'] }}</span>{{ $activity['act_type'] }}
@if($activity['actType'] != 0)
<span class="red">{{ $activity['act_type_ext'] }}</span>
@endif
</div>
							<a href="activity.php?act=view&act_id={{ $activity['act_id'] }}" class="acti-btn">{{ $lang['special_field'] }}<i class="iconfont icon-right"></i></a>
						</li>

@endforeach


@endforeach

                </ul>
                <div class="no_records" style="display:none">
                    <i class="no_icon_two"></i>
                    <div class="no_info no_info_line">
                        <h3>{{ $lang['information_null'] }}</h3>
                        <div class="no_btn">
                            <a href="index.php" class="btn sc-redBg-btn">{{ $lang['back_home'] }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
</body>
</html>
