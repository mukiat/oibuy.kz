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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/coupons.css') }}" />
</head>

<body>
@include('frontend::library/page_header_coupons')
<div id="content" class="quan_content">
    <div class="quan-main mt20">
        <div class="w1200 w">
            <div class="quan-filter mb30">
                <div class="f-sort">
                    <a href="coupons.php?act=coupons_list"
@if(empty(request()->get('field')))
class="selted"
@endif
 data-param="st" data-id="0">{{ $lang['default'] }}</a>
                    <a href="coupons.php?act=coupons_list&field=cou_end_time"
@if(request()->get('field')=='cou_end_time')
class="selted"
@endif
>{{ $lang['Will_expire'] }}</a>
                    <a href="coupons.php?act=coupons_list&field=cou_money"
@if(request()->get('field')=='cou_money')
class="selted"
@endif
>{{ $lang['max_Face'] }}</a>
                </div>
                <div class="f-types">
                    <a href="javascript:void(0);"
@if(empty(request()->get('type')) || request()->get('type')=='undefined')
class="selted"
@endif
 data-id="3,4"><i></i>{{ $lang['type_all'] }}</a>
                    <a href="javascript:void(0);"
@if(request()->get('type')=='all')
class="selted"
@endif
 data="all"><i></i>{{ $lang['vouchers_all'] }}</a>
                    <a href="javascript:void(0);"
@if(request()->get('type')=='member')
class="selted"
@endif
 data="member"><i></i>{{ $lang['vouchers_user'] }}</a>
                    <a href="javascript:void(0);"
@if(request()->get('type')=='shipping')
class="selted"
@endif
 data="shipping"><i></i>{{ $lang['vouchers_shipping'] }}</a>
                </div>
            </div>
            <div class="quan-list">

@foreach($cou_data as $vo)

                    <div class="quan-item quan-d-item quan-item-acoupon
@if($vo['cou_surplus'] == 0)
 quan-gray-item
@endif
">
                        <div class="q-type">
                            <div class="q-price">

@if($vo['cou_type'] == 5)

                                <i class="icon-my"></i>

@else

                                <em>{{ config('shop.currency_format', '¥') }}</em>
                                <strong class="num">{{ $vo['cou_money'] }}</strong>

@endif


                                <div class="txt" style="float: none;"><div class="typ-txt">{{ $vo['cou_type_name'] }}</div></div>
                                <div class="txt">
                                    <div class="limit"><span class="ftx-06">
@if($vo['cou_man'] > 0)
{{ $lang['full'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}
@else
{{ $lang['unlimited'] }}
@endif
</span></div>
                                </div>
                            </div>
                            <div class="q-range">
                                <div class="range-item"><p title="
@if($vo['cou_goods'])
<p>{{ $lang['permissions_buy'] }}</p>
@else
<p>{{ $lang['category'] }}</p>
@endif
">
                                    {{ $vo['cou_title'] }}
                                </p></div>
                                <div class="range-item">{{ $vo['store_name'] }}</div>
                                <div class="range-item">{{ $vo['cou_start_time_format'] }}-{{ $vo['cou_end_time_format'] }}</div>
                            </div>
                        </div>

@if($user_id && $vo['cou_is_receive'])


@if($vo['is_use'] == 0)


@if($vo['cou_surplus'] == 0)

                        <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                        <div class="q-opbtns"><a href="search.php?cou_id={{ $vo['cou_id'] }}" target="_blank"><b class="semi-circle"></b>{{ $lang['Immediate_use'] }}</a></div>
                        <div class="q-state"><div class="btn-state btn-geted">{{ $lang['receive_hove'] }}</div></div>

@endif



@else


@if($vo['cou_surplus'] == 0)

                        <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                        <div class="q-opbtns"><a href="javascript:;" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif


@else


@if($vo['cou_surplus'] == 0)

                        <div class="q-opbtns"><a href="javascript:;" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

                        <div class="q-opbtns"><a href="javascript:;" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif

                    </div>


@endforeach



                </div>
        </div>
    </div>
    <script type="text/javascript">
        $(".f-types a,.f-service a").click(function(){
            if($(this).hasClass("selted")){
                $(this).removeClass("selted");
            }else{
                $(this).addClass("selted");
            }
        });
    </script>


@if($page_total>1)

    <div class="pages" id="pager">
        <ul>
            <li class="item prev"><a href="coupons.php?{{ $page_url }}&p={{ $prev_page }}"><i class="iconfont icon-left"></i></a></li>

@foreach($page_total2 as $vo)

            <li class="item
@if($page==$vo )
cur
@endif
"><a href=coupons.php?{{ $page_url }}&p={{ $vo }}>{{ $vo }}</a></li>

@endforeach

            <li class="item next"><a href="coupons.php?{{ $page_url }}&p={{ $next_page }}"><i class="iconfont icon-right"></i></a></li>
        </ul>
    </div>

@endif

    <input name="script_name" value="{{ $script_name }}" type="hidden" />
    <input name="cur_url" value="{{ $cur_url }}" type="hidden" />
</div>
@include('frontend::library/page_footer')
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>

<script type="text/javascript">
	$('.f-types a').on('click',function(){
		var index=location.href.indexOf('&type');
		if(index == -1)
			var url = location.href;
		else
			var url = location.href.substr(0,index);

		location.href=url+'&type='+$(this).attr('data')

	});
</script>

</body>
</html>
