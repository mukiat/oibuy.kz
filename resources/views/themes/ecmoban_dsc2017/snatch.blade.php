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
<link rel="stylesheet" type="text/css" href="{{ skin('css/goods_fitting.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/suggest.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}" />
</head>
<body class="bg-ligtGary">

	@include('frontend::library/page_header_common')
    <div class="content activity-desc-content">
        <div class="w w1200">
            @include('frontend::library/ur_here')
            <div class="activity-desc-main clearfix">
                <div class="acde-left">
                    <div class="product-info clearfix">
                        @include('frontend::library/goods_gallery')
                        <div class="product-wrap">
                            <div class="name">{{ $snatch_goods['goods_name'] }}</div>
                            <div class="activity-title">
                                <div class="activity-type"><i class="iconfont icon-time"></i>
@if($myprice['is_end'] == false)
{{ $lang['underway'] }}
@else
{{ $lang['end_time'] }}
@endif
</div>
                                <div class="sk-time-cd">
                                    <div class="sk-cd-tit">{{ $lang['End_pitch'] }}</div>
                                    <div class="cd-time" ectype="time" data-time="{{ $snatch_goods['gmt_end_time'] }}">
                                        <div class="days">00</div>
                                        <span class="split">:</span>
                                        <div class="hours">00</div>
                                        <span class="split">:</span>
                                        <div class="minutes">00</div>
                                        <span class="split">:</span>
                                        <div class="seconds">00</div>
                                    </div>
                                </div>
                            </div>

                            <div class="summary snatch-summary">
                                <div class="summary-price-wrap">
                                    <div class="s-p-w-wrap clearfix">
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['current_price'] }}：</div>
                                            <div class="si-warp">
                                                <span class="price red"><span id="currentPrice" data-price="{{ $snatch_goods['start_price'] }}">{{ $snatch_goods['formated_shop_price'] }}</span></span>
                                            </div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['market_price'] }}：</div>
                                            <div class="si-warp">{{ $snatch_goods['formated_market_price'] }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['Crowd_onlookers'] }}：</div>
                                            <div class="si-warp">{{ $price_list_count }}{{ $lang['ci'] }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['snatch_mechanism'] }}：</div>
                                            <div class="si-warp">{{ $goods['rz_shop_name'] }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['price_extent_snatch'] }}：</div>
                                            <div class="si-warp ftx-09">{{ $snatch_goods['formated_start_price'] }}&nbsp;~&nbsp;{{ $snatch_goods['formated_end_price'] }}
@if($snatch_goods['cost_points'] > 0)
&nbsp;&nbsp;({{ $lang['cost_points_notic'] }}{{ $snatch_goods['cost_points'] }}{{ $lang['integral'] }})
@endif
</div>
                                        </div>
                                    </div>
                                </div>
                            </div>


@if($specification)

                            <form action="javascript:bid()" method="post" name="ECS_FORMBUY" id="ECS_FORMBUY">
                            <div class="summary-basic-info">

@foreach($specification as $spec_key => $spec)


@if($spec['values'])

                                <div class="summary-item is-attr goods_info_attr" ectype="is-attr" data-type="
@if($spec['attr_type'] == 1)
radio
@else
checkbox
@endif
">
                                    <div class="si-tit">{{ $spec['name'] }}</div>

@if($cfg['goodsattr_style'] == 1)

                                    <div class="si-warp">
                                        <ul>

@foreach($spec['values'] as $key => $value)


@if($spec['is_checked'] > 0)

                                            <li class="item
@if($value['checked'] == 1)
 selected
@endif
" data-name="{{ $value['id'] }}">
                                                <b></b>
                                                <a href="
@if($value['img_site'])
{{ $value['img_site'] }}
@else
javascript:void(0);
@endif
">

@if($value['img_flie'])

                                                    <img src="{{ $value['img_flie'] }}" width="24" height="24" />

@endif

                                                    <i>{{ $value['label'] }}</i>
                                                    <input id="spec_value_{{ $value['id'] }}" type="
@if($spec['attr_type'] == 2)
checkbox
@else
radio
@endif
" data-attrtype="
@if($spec['attr_type'] == 2)
2
@else
1
@endif
" name="spec_{{ $spec_key }}" value="{{ $value['id'] }}" autocomplete="off" class="hide" />

@if($value['checked'] == 1)

                                                    <script type="text/javascript">
                                                        $(function(){
                                                            $("#spec_value_{{ $value['id'] }}").prop("checked", true);
                                                        });
                                                    </script>

@endif

                                                </a>
                                            </li>

@else

                                            <li class="item
@if($key == 0)
 selected
@endif
" data-name="{{ $value['id'] }}">
                                                <b></b>
                                                <a href="javascript:void(0);" name="{{ $value['id'] }}" class="noimg">
                                                    <i>{{ $value['label'] }}</i>
                                                    <input id="spec_value_{{ $value['id'] }}" type="
@if($spec['attr_type'] == 2)
checkbox
@else
radio
@endif
" data-attrtype="
@if($spec['attr_type'] == 2)
2
@else
1
@endif
" name="spec_{{ $spec_key }}" value="{{ $value['id'] }}" autocomplete="off" class="hide" /></a>

@if($key == 0)

                                                    <script type="text/javascript">
                                                        $(function(){
                                                            $("#spec_value_{{ $value['id'] }}").prop("checked", true);
                                                        });
                                                    </script>

@endif

                                                </a>
                                            </li>

@endif


@endforeach

                                        </ul>
                                    </div>

@else

                                    ...

@endif

                                    <input type="hidden" name="spec_list" value="{{ $spec_key }}" data-spectype="
@if($spec['attr_type'] == 1)
attr-radio
@else
attr-check
@endif
" />
                                </div>

@endif


@endforeach

                            </div>
                            @csrf </form>

@endif


                            <div class="snatch-summary">

@if($myprice['is_end'] == false)

                                <form action="javascript:bid()" method="post" name="formBid" id="formBid">
                                    <div class="summary-count-wp">
                                        <div class="fp-l" style="display:none">{{ $lang['min_fare'] }}：<em>{{ config('shop.currency_format', '¥') }}<span id="priceLowerOffset" data-amplitude="1.00">1.00</span></em></div>
                                        <div class="amount-wrap-2">
                                            <input type="hidden" name="max-price" id="maxPrice" value="{{ $snatch_goods['end_price'] }}" />
                                            <i class="iconfont icon-reduce" onclick="decre()"></i>
                                            <input type="text" name="buy-price"  value="{{ $snatch_goods['start_price'] }}" id="buyPrice" />
                                            <i class="iconfont icon-plus" onclick="incre()"></i>
                                        </div>
                                    </div>
                                    <div class="choose-btns clearfix">
                                        <input type="hidden" name="act" value="buy" />
                                        <input type="hidden" name="id" value="{{ $id }}" />
                                        <input type="hidden" name="snatch_id" value="{{ $id }}" />
                                        <a href="javascript:bid();" class="btn-append">{{ $lang['me_bid'] }}</a>
                                    </div>

                                    <input type="hidden" value="" name="goods_attr_id" id="goods_attr_id" />
                                	<input type="hidden" value="{{ $goods_id ?? 0 }}" name="goods_id" />
                                    <input type="hidden" value="{{ $region_id ?? 0 }}" name="region_id" />
                                    <input type="hidden" value="{{ $area_id ?? 0 }}" name="area_id" />
                                    <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price" ectype="add_shop_price" />
                                @csrf </form>

@else


@if($result['order_count'] == 0 && $result['user_id'] == $user_id)

                                    <div class="summary-count-wp">
                                        <div class="me_grab">{{ $lang['snatch_desc'] }}</div>
                                        <div class="me_bid">
                                            <span>{{ $lang['nin_want_bid'] }}：</span>
                                            <div class="p-price">{{ $result['formated_bid_price'] }}</div>
                                        </div>
                                    </div>
                                    <div class="choose-btns clearfix">
                                        <form name="buy" action="snatch.php" method="post" name="formBid" id="formBid">
                                            <input type="hidden" name="act" value="buy" />
                                            <input type="hidden" name="id" value="{{ $id }}" />
                                            <input type="submit" name="bug" class="sc-btn sc-redBg-btn btn35 w120 mt20" value="{{ $lang['go_pay'] }}" />

                                            <input type="hidden" value="" name="goods_attr_id" id="goods_attr_id" />
                                            <input type="hidden" value="{{ $goods_id ?? 0 }}" name="goods_id" />
                                            <input type="hidden" value="{{ $region_id ?? 0 }}" name="region_id" />
                                            <input type="hidden" value="{{ $area_id ?? 0 }}" name="area_id" />
                                            <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price" ectype="add_shop_price" />
                                        @csrf </form>
                                    </div>

@else

                                    <div class="choose-btns clearfix">
                                        <a href="javascript:void(0);" class="btn-invalid">{{ $lang['has_ended'] }}</a>
                                    </div>

@endif


@endif

                            </div>
                        </div>
                    </div>
                    <div class="auction-progress clearfxi">
                        <div class="apt">{{ $lang['bidding_process'] }}</div>
                        <div class="apb">
                            <ul>
                                <li>01.{{ $lang['apb_01'] }}<i class="iconfont icon-arrow-right-alt"></i></li>
                                <li>02.{{ $lang['apb_02'] }}<i class="iconfont icon-arrow-right-alt"></i></li>
                                <li>03.{{ $lang['apb_03'] }}<i class="iconfont icon-arrow-right-alt"></i></li>
                                <li>04.{{ $lang['apb_04'] }}<i class="iconfont icon-arrow-right-alt"></i></li>
                                <li>05.{{ $lang['apb_05'] }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="activity-desc-detail" id="detail-slide">
                        <div class="hd">
                            <ul>
                                <li>{{ $lang['The_Indiana'] }}</li>
                                <li>{{ $lang['snatch_Record'] }}</li>
                                <li>{{ $lang['service_guarantee'] }}</li>
                                <li>{{ $lang['Indiana_snatch'] }}</li>
                            </ul>
                        </div>
                        <div class="bd">
                            <div class="bd-item">
                                <div class="desc-detail-title">{{ $lang['The_Indiana'] }}</div>
                                <div class="desc-detail-content">

@if($snatch_goods['desc'])

                                    	{!! $snatch_goods['desc'] !!}

@else

                                    	<div class="no_records">
                                        	<i class="no_icon_two"></i>
                                            <div class="no_info">
                                            	<h3>{{ $lang['snatch_detail_notic'] }}</h3>
                                            </div>
                                        </div>

@endif

                                </div>
                            </div>
                            <div class="bd-item" id="ECS_PRICE_LIST" style="display:none;">
                                @include('frontend::library/snatch_price')
                            </div>
                            <div class="bd-item clearfix" style="display:none;">
                                <div class="desc-detail-content">

@if($snatch_goods['act_promise'])

                                	{!! $snatch_goods['act_promise'] !!}

@else

                                	<div class="no_records">
                                        <i class="no_icon_two"></i>
                                        <div class="no_info">
                                            <h3>{{ $lang['service_guarantee_notic'] }}</h3>
                                        </div>
                                    </div>

@endif

                                </div>
                            </div>
                            <div class="bd-item clearfix" style="display:none;">
                                <div class="desc-detail-content">

@if($snatch_goods['act_ensure'])

                                	{!! $snatch_goods['act_ensure'] !!}

@else

                                	<div class="no_records">
                                        <i class="no_icon_two"></i>
                                        <div class="no_info">
                                            <h3>{{ $lang['Indiana_snatch_notic'] }}</h3>
                                        </div>
                                    </div>

@endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="acde-right">
                    <div class="org-box">

@if($goods['shopinfo']['brand_thumb'])
<div class="org-logo"><img src="{{ $goods['shopinfo']['brand_thumb'] }}"></div>
@endif

                        <p><span>{{ $lang['snatch_mechanism'] }}：</span>{{ $goods['rz_shop_name'] }}</p>
                        <div class="clearfix">
                            <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  goods_id="{{ $goods['goods_id'] }}" class="a-kefu"><i class="icon i-kefu"></i><span>{{ $lang['con_cus_service'] }}</span></a>
                        </div>
                    </div>
                    <div id="records-list">
                        @include('frontend::library/snatch')
                    </div>
                    <div class="acde-right-title">{{ $lang['Recommended_Indiana'] }}</div>
                    <ul class="side-goods">

@foreach($hot_goods as $goods)

                        <li>
                            <div class="p-img">
                                <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" alt="{{ $goods['short_style_name'] }}"/></a>
                            </div>
                            <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['short_style_name'] }}">{{ $goods['short_style_name'] }}</a></div>
                            <div class="p-price"><em>{{ config('shop.currency_format', '¥') }}</em>{{ $goods['auction']['start_price'] }}</div>
                        </li>

@endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>
    @include('frontend::library/goods_fittings_cnt')


    @include('frontend::library/duibi')


    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position(['ru_id' => $goods['user_id']]) !!}

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/warehouse_area.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/magiczoomplus.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ url('/') }}/calendar.php?lang={{ $cfg_lang }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>

    <script type="text/javascript">

	//全局变量
	var goods_id = {{ $goods_id ?? 0 }};
	var goodsId = {{ $goods_id ?? 0 }};
	var add_shop_price = $("*[ectype='add_shop_price']").val();

	$(function(){
		changePrice(1);
	});

	//价格加减
	increase();

	//商品相册小图滚动
	$(".spec-list").slide({mainCell:".spec-items ul",effect:"left",trigger:"click",pnLoop:false,autoPage:true,scroll:1,vis:5,prevCell:".spec-prev",nextCell:".spec-next"});

	//夺宝详情切换
	$("#detail-slide").slide({titCell:".hd li",mainCell:".bd",titOnClassName:"on",trigger: "click"});

	/*团购倒计时*/
	$(".cd-time").each(function(){
		$(this).yomi();
	});

	/* 点击可选属性或改变数量时修改商品价格的函数 */
	function changePrice(type){
		var qty = 1;
		var goods_attr_id = '';
		var goods_attr = '';
		var attr_id = '';
		var attr = '';
		var region_id = $(":input[name='region_id']").val();
		var area_id = $(":input[name='area_id']").val();

		if(document.forms['ECS_FORMBUY']){
			goods_attr_id = getSelectedAttributes(document.forms['ECS_FORMBUY']);
			$("#goods_attr_id").val(goods_attr_id);
		}


		if(type != 1){
			if(add_shop_price == 0){
				if(document.forms['ECS_FORMBUY']){
					attr_id = getSelectedAttributesGroup(document.forms['ECS_FORMBUY']);
					goods_attr = '&goods_attr=' + attr_id;
				}
			}
			Ajax.call('goods.php', 'act=price&id=' + goodsId + '&attr=' + goods_attr_id + goods_attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id, changePriceResponse, 'GET', 'JSON');
		}else{
			attr = '&attr=' + goods_attr_id;
			Ajax.call('goods.php', 'act=price&id=' + goodsId + attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&type=' + type, changePriceResponse, 'GET', 'JSON');
		}
	}

	/* 接收返回的信息 回调函数 */
	function changePriceResponse(res){
		if(res.err_msg.length > 0){
			pbDialog(res.err_msg," ",0,450,80,50);
		}else{

			if(res.attr_number <= 0){

				$(".btn-append").hide();

				pbDialog(json_languages.buy_error," ",0,450,80,50);
			}else{
				$(".btn-append").show();
			}
		}
	}
    </script>
</body>
</html>
