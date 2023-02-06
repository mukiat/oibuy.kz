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

<body class="group-buy-css">
	@include('frontend::library/page_header_common')
	<div class="full-main-n">
        <div class="w w1200 relative">
			@include('frontend::library/ur_here')
			@include('frontend::library/goods_merchants_top')
        </div>
    </div>
    <div class="container">
    	<div class="w w1200">
        	<div class="product-info mt20 group-buy">
            	@include('frontend::library/goods_gallery')
                <div class="product-wrap">
                    <form action="group_buy.php?act=buy" method="post" name="ECS_FORMBUY" id="ECS_FORMBUY" >
                	<div class="name">{{ $group_buy['goods_name'] }}</div>

@if($goods['goods_brief'])

                    <div class="newp">{{ $goods['goods_brief'] }}</div>

@endif

                    <div class="activity-title">
                    	<div class="activity-type"><i class="iconfont icon-time"></i>{{ $lang['Group_purchase'] }}</div>
                        <div class="sk-time-cd">
                            <div class="sk-cd-tit">
@if($goods['is_end'] == 1 || $orderG_number > $group_buy['restrict_amount'] || $orderG_number == $group_buy['restrict_amount'])
{{ $lang['end_time'] }}
@else
{{ $lang['residual_time'] }}
@endif
</div>
                            <div class="cd-time
@if(!($goods['is_end'] == 1 || $orderG_number > $group_buy['restrict_amount'] || $orderG_number == $group_buy['restrict_amount']))
time
@endif
" ectype="time" data-time="{{ $group_buy['end_time'] }}">

@if($goods['is_end'] == 1 )

                                    {{ $group_buy['end_time'] }}

@else

                                    <div class="days">00</div>
                                    <span class="split">:</span>
                                    <div class="hours">00</div>
                                    <span class="split">:</span>
                                    <div class="minutes">00</div>
                                    <span class="split">:</span>
                                    <div class="seconds">00</div>

@endif

                            </div>
                        </div>
                    </div>
                    <div class="summary">
                    	<div class="summary-price-wrap">
                        	<div class="s-p-w-wrap">
                                <div class="summary-item si-shop-price">
                                    <div class="si-tit">{{ $lang['group_buy_price'] }}</div>
                                    <div class="si-warp">
                                        <strong class="shop-price">{{ $group_buy['price_ladder']['0']['formated_price'] }}</strong>
                                    </div>
                                </div>
                                <div class="summary-item si-market-price">
                                    <div class="si-tit">{{ $lang['market_price'] }}</div>
                                    <div class="si-warp"><div class="m-price">{{ $group_buy['goods']['market_price'] }}</div></div>
                                </div>
                                <div class="si-info">
                                    <div class="si-cumulative">{{ $lang['Sales_count'] }}<em>{{ $group_buy['valid_goods'] }}</em></div>
                                    <div class="si-cumulative">{{ $lang['Collection_count'] }}<em>{{ $goods['collect_count'] }}</em></div>
                                </div>

@if($two_code)

                                <div class="si-phone-code">
                                    <div class="qrcode-wrap">
                                        <div class="qrcode_tit">{{ $lang['summary_phone'] }}<i class="iconfont icon-qr-code"></i></div>
                                        <div class="qrcode_pop">
                                            <div class="mobile-qrcode"><img src="{{ $weixin_img_url }}" alt="{{ $lang['two_code'] }}" title="{{ $weixin_img_text }}" width="175"></div>
                                        </div>
                                    </div>
                                </div>

@endif


@if($group_buy['deposit'] > 0)

                                <div class="summary-item si-shop-price">
                                    <div class="si-tit">{{ $lang['gb_deposit'] }}</div>
                                    <div class="si-warp">{{ $group_buy['formated_deposit'] }}</div>
                                </div>

@endif

                                <div class="clear"></div>
                            </div>
                        </div>

                        <div class="summary-basic-info">

@if($price_ladder)

                        	<div class="summary-item is-ladder">
                                <div class="si-tit">{{ $lang['staircase_price'] }}</div>
                                <div class="si-warp">
                                	<a href="javascript:void(0);" class="link-red" ectype="view_priceLadder">{{ $lang['view_staircase_price'] }}</a>
                                    <dl class="priceLadder" ectype="priceLadder">
                                    	<dt>
                                        	<span>{{ $lang['number_to'] }}</span>
                                            <span>{{ $lang['price'] }}</span>
                                        </dt>

@foreach($price_ladder as $price_key => $rank)

                                        <dd>
                                        	<span>{{ $rank['amount'] }}</span>
                                            <span>{{ $rank['formated_price'] }}</span>
                                        </dd>

@endforeach

                                    </dl>
                                </div>
                            </div>

@endif

                        	<div class="summary-item is-stock">
                            	<div class="si-tit">{{ $lang['distribution'] }}</div>
                                <div class="si-warp">
                                    <span class="initial-area">

@if($adress['city'])

                                            {{ $adress['city'] }}

@else

                                            {{ $basic_info['city'] }}

@endif

                                    </span><span>{{ $lang['zhi'] }}</span>
                                    <div class="store-selector">
                                    	<div class="text-select" id="area_address" ectype="areaSelect"></div>
                                    </div>

                                    <div class="store-warehouse">
                                    	<div class="store-prompt" id="isHas_warehouse_num">{!! $lang['isHas_warehouse_num'] !!}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="summary-item is-service">
                                <div class="si-tit">{{ $lang['service'] }}</div>
                                <div class="si-warp">
                                    <div class="fl">

@if($goods['user_id'] > 0)

                                        {{ $lang['you'] }} <a href="{{ $goods['store_url'] }}" class="link-red" target="_blank">{{ $goods['rz_shop_name'] }}</a> {{ $lang['After_sale_service'] }}

@else

                                        {{ $lang['you'] }} <a href="javascript:void(0)" class="link-red">{{ $goods['rz_shop_name'] }}</a> {{ $lang['After_sale_service'] }}

@endif

                                    </div>
                                    <div class="fl pl10" id="user_area_shipping"></div>
                                </div>
                            </div>

@if($group_buy['restrict_amount'])

                            <div class="summary-item is-xiangou">
                            	<div class="si-tit">{{ $lang['gb_restrict_amount'] }}</div>
                                <div class="si-warp">
                                	<em id="restrict_amount" ectype="restrictNumber" data-value="{{ $group_buy['restrict_amount'] }}">{{ $group_buy['restrict_amount'] }}</em>
                                    <span>
@if($goods['goods_unit'])
{{ $goods['goods_unit'] }}
@elseif ($goods['measure_unit'])
{{ $goods['measure_unit'] }}
@endif
</span>
                                    <span>（{{ $lang['js_languages']['Already_buy'] }}：<em id="orderG_number" ectype="orderGNumber" data-value="{{ $orderG_number ?? 0 }}">{{ $orderG_number ?? 0 }}</em>&nbsp;
@if($goods['goods_unit'])
{{ $goods['goods_unit'] }}
@elseif ($goods['measure_unit'])
{{ $goods['measure_unit'] }}
@endif
）</span>
                                </div>
                            </div>

@endif


@if($group_buy['gift_integral'])

                            <div class="summary-item is-integral">
                            	<div class="si-tit">{{ $lang['gb_gift_integral'] }}</div>
                                <div class="si-warp">
                                	{{ $group_buy['gift_integral'] }}
                                </div>
                            </div>

@endif


@foreach($specification as $spec_key => $spec)


@if($spec['values'])

                            <div class="summary-item is-attr goods_info_attr" ectype="is-attr" data-type="
@if($spec['attr_type'] == 1)
radio
@else
checkeck
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
" date-rev="{{ $value['img_site'] }}" data-name="{{ $value['id'] }}">
                                        <b></b>
                                        <a href="javascript:void(0);">

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
">
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

                            </div>

@endif


@endforeach


                            <div class="summary-item is-number">
                            	<div class="si-tit">{{ $lang['number_to'] }}</div>
                                <div class="si-warp">
                                	<div class="amount-warp">
                                        @if ($min_amount > 0)
                                            <input class="text buy-num" id="quantity" ectype="quantity" value="{{ $min_amount }}" name="number" defaultnumber="{{ $min_amount }}">
                                        @else
                                            <input class="text buy-num" id="quantity" ectype="quantity" value="1" name="number" defaultnumber="1">
                                        @endif
                                        <div class="a-btn">
                                            <a href="javascript:void(0);" class="btn-add" ectype="btnAdd"><i class="iconfont icon-up"></i></a>
                                            <a href="javascript:void(0);" class="btn-reduce btn-disabled" ectype="btnReduce" data-minamount="{{ $min_amount }}"><i class="iconfont icon-down"></i></a>
                                            <input type="hidden" name="perNumber" id="perNumber" ectype="perNumber" value="0">
                                    		<input type="hidden" name="perMinNumber" id="perMinNumber" ectype="perMinNumber" value="1">
                                        </div>
                                    </div>
                                    <span>{{ $lang['goods_inventory'] }}&nbsp;<em id="goods_attr_num" ectype="goods_attr_num"></em>&nbsp;
@if($goods['goods_unit'])
{{ $goods['goods_unit'] }}
@else
{{ $goods['measure_unit'] }}
@endif
</span>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="choose-btns ml60 clearfix" style="display: none;">

@if($goods['is_end'] == 1 || ($orderG_number > $group_buy['restrict_amount'] && $group_buy['restrict_amount'] > 0) || ($orderG_number == $group_buy['restrict_amount'] &&  $group_buy['restrict_amount'] > 0))

                            <a href="javascript:void(0);" class="btn-invalid">{{ $lang['Group_purchase_end'] }}</a>

@else

                            <a href="javascript:void(0);" class="btn-append" ectype="btn-group-buy" data-minamount="{{ $min_amount }}">{{ $lang['Group_purchase_now'] }}{{ $lang['shopping'] }}</a>

@endif

                        </div>
                    </div>
                    <input type="hidden" value="" name="goods_attr_id" id="goods_attr_id" />
                    <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price" ectype="add_shop_price" />
                    <input type="hidden" value="{{ $goods['goods_id'] }}" id="good_id" name="good_id">
                    <input type="hidden" value="{{ $group_buy['group_buy_id'] }}" name="group_buy_id" />
                        <input type="hidden" value="
@if($goods['is_end'] == 1 || ($orderG_number > $group_buy['restrict_amount'] && $group_buy['restrict_amount'] > 0) || ($orderG_number == $group_buy['restrict_amount'] &&  $group_buy['restrict_amount'] > 0))
1
@else
0
@endif
" name="group_is_end" />
                        <input type="hidden" value="" name="goods_spec" id="goods_spec"/>
                    <input type="hidden" value="{{ $group_buy['restrict_amount'] }}" name="restrictShop" ectype="restrictShop" />
                    @csrf </form>
                </div>

                <div class="track">
                	<div class="track_warp">
                    	<div class="track-tit"><h3>{{ $lang['see_to_see'] }}</h3><span></span></div>
                        <div class="track-con">
                        	<ul>

@foreach($look_top as $look_top)

                                <li>
                                    <a href="group_buy.php?act=view&id={{ $look_top['act_id'] }}" target="_blank"><img src="{{ $look_top['goods_thumb'] }}" width="140" height="140"><p class="price">{{ $look_top['ext_info']['cur_price'] }}</p></a>
                                </li>

@endforeach

                            </ul>
                        </div>
                        <div class="track-more">
                        	<a href="javascript:void(0);" class="sprite-up"><i class="iconfont icon-up"></i></a>
                            <a href="javascript:void(0);" class="sprite-down"><i class="iconfont icon-down"></i></a>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="goods-main-layout">
            	<div class="g-m-left">
                	@include('frontend::library/goods_merchants')
                    <div class="g-main g-recommend">
                    	<div class="mt">
                        	<h3>{{ $lang['other_group_buy'] }}</h3>
                        </div>
                        <div class="mc">
                        	<div class="mc-warp">
                            	<ul>

@foreach($merchant_group_goods as $merchant_group_goods)

                                        <li>
                                            <div class="p-img"><a href="group_buy.php?act=view&id={{ $merchant_group_goods['act_id'] }}" target="_blank"><img src="{{ $merchant_group_goods['goods_thumb'] }}" width="130" height="130"></a></div>
                                            <div class="p-name"><a href="group_buy.php?act=view&id={{ $merchant_group_goods['act_id'] }}" title="{{ $merchant_group_goods['act_name'] }}" target="_blank">{{ $merchant_group_goods['act_name'] }}</a></div>
                                            <div class="p-price">{{ $merchant_group_goods['shop_price'] }}</div>
                                        </li>

@endforeach

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="g-main g-recommend">
                    	<div class="mt">
                        	<h3>{{ $lang['Buy_and_buy'] }}</h3>
                        </div>
                        <div class="mc">
                        	<div class="mc-warp">
                            	<ul>

@foreach($buy_top as $buy_top)

                                    <li>
                                        <div class="p-img"><a href="group_buy.php?act=view&id={{ $buy_top['act_id'] }}" target="_blank"><img src="{{ $buy_top['goods_thumb'] }}" width="130" height="130"></a></div>
                                        <div class="p-name"><a href="group_buy.php?act=view&id={{ $buy_top['act_id'] }}" title="{{ $buy_top['goods_name'] }}" target="_blank">{{ $buy_top['goods_name'] }}</a></div>
                                        <div class="p-price">{{ $buy_top['ext_info']['cur_price'] }}</div>
                                    </li>

@endforeach

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="g-m-detail">
                	<div class="gm-tabbox" ectype="gm-tabs">
                    	<ul class="gm-tab">
                            <li ectype="gm-tab-item" class="curr">{{ $lang['details_order'] }}</li>
                            <li ectype="gm-tab-item">{{ $lang['introduce_pic'] }}</li>
                            <li ectype="gm-tab-item">{{ $lang['evaluate_user'] }}</li>
                        </ul>
                        <div class="extra"></div>
                        <div class="gm-tab-qp-bort" ectype="qp-bort"></div>
                    </div>
                    <div class="gm-floors" ectype="gm-floors">
                    	<div class="gm-f-item gm-f-parameter" ectype="gm-item">
                        	<dl class="goods-para">
                                <dd class="column"><span>{{ $lang['goods_name'] }}：{{ $goods['goods_name'] }}</span></dd>
                                <dd class="column"><span>{{ $lang['Commodity_number'] }}：{{ $goods['goods_sn'] }}</span></dd>
                                <dd class="column"><span>{{ $lang['seller_store'] }}：<a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" target="_blank">{{ $goods['rz_shop_name'] }}</a></span></dd>

@if($cfg['show_goodsweight'])

                                <dd class="column"><span>{{ $lang['weight'] }}：{{ $goods['goods_weight'] }}</span></dd>

@endif


@if($cfg['show_addtime'])

                                <dd class="column"><span>{{ $lang['add_time'] }}{{ $goods['add_time'] }}</span></dd>

@endif

                            </dl>
                        	<dl class="goods-para mt10">

@foreach($properties as $key => $property_group)


@foreach($property_group as $property)

                                    <dd class="column"><span title="{{ $property['value'] }}">{{ $property['name'] }}：{{ $property['value'] }}</span></dd>

@endforeach


@endforeach

                                <div class="clear"></div>
                                {{ $group_buy['group_buy_desc'] }}
                            </dl>
                        </div>
                        <div class="gm-f-item gm-f-details" ectype="gm-item">
                        	<div class="gm-title">
                            	<h3>{{ $lang['introduce_pic'] }}</h3>
                            </div>
                            <p>{!! $group_buy['goods_desc'] !!}</p>
                        </div>
                        <div class="gm-f-item gm-f-comment" ectype="gm-item">
                        	<div class="gm-title">
                            	<h3>{{ $lang['comment_sunburn'] }}</h3>
								{{-- DSC 提醒您：动态载入goods_comment_title.lbi，显示首页分类小广告 --}}
{!! insert_goods_comment_title(['goods_id' => $goods['goods_id']]) !!}
                            </div>
                            <div class="gm-warp">
                            	<div class="praise-rate-warp">
                                	<div class="rate">
                                    	<strong>{{ $comment_all['goodReview'] }}</strong>
                                        <span class="rate-span">
                                        	<span class="tit">{{ $lang['Rate_praise'] }}</span>
                                            <span class="bf">%</span>
                                        </span>
                                    </div>
                                    <div class="actor-new">
                                    	<dl>

@foreach($goods['impression_list'] as $tag)

											<dd>{{ $tag['txt'] }}({{ $tag['num'] }})</dd>

@endforeach

                                        </dl>
                                    </div>
                                </div>
								<div class="com-list-main">
								@include('frontend::library/comments')
								</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>

@if($history_goods)

                <div class="rection">
                	<div class="ftit"><h3>{{ $lang['guess_love'] }}</h3></div>
                    <ul>

@foreach($history_goods as $item)


@if($loop->iteration <= 5)

                        <li>
                            <div class="p-img"><a href="{{ $item['url'] }}" target="_blank"><img src="{{ $item['goods_thumb'] }}" width="232" height="232"></a></div>
                            <div class="p-name"><a href="{{ $item['url'] }}" title="{{ $item['short_name'] }}" target="_blank">{{ $item['short_name'] }}</a></div>
                            <div class="p-price">

@if($item['promote_price'])

                                {{ $item['formated_promote_price'] }}

@else

                                {{ $item['shop_price'] }}

@endif

                            </div>
                        </li>

@endif


@endforeach

                    </ul>
                </div>

@endif

            </div>
        </div>
    </div>

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
	//商品详情悬浮框
	goods_desc_floor();

	//商品相册小图滚动
	$(".spec-list").slide({mainCell:".spec-items ul",effect:"left",trigger:"click",pnLoop:false,autoPage:true,scroll:1,vis:5,prevCell:".spec-prev",nextCell:".spec-next"});

	//右侧看了又看上下滚动
	$(".track_warp").slide({mainCell:".track-con ul",effect:"top",pnLoop:false,autoPlay:false,autoPage:true,prevCell:".sprite-up",nextCell:".sprite-down",vis:3});

	//商品搭配切换
	$(".combo-inner").slide({titCell:".tab-nav li",mainCell:".tab-content",titOnClassName:"curr",trigger:"click"});

	//商品搭配 多个商品滚动切换
	$(".combo-items").slide({mainCell:".combo-items-warp ul",effect:"left",pnLoop:false,autoPlay:false,autoPage:true,prevCell:".o-prev",nextCell:".o-next",vis:4});

	//左侧新品 热销 推荐排行切换
	$(".g-rank").slide({titCell:".mc-tab li",mainCell:".mc-content",titOnClassName:"curr",trigger:"click"});

	/*团购倒计时*/
	$("*[ectype='time']").each(function(){
		$(this).yomi();
	});
    </script>
    <script type="text/javascript">
    	var goods_id = {{ $goods['goods_id'] }};
		var goodsId = {{ $goods['goods_id'] }};
		var isReturn = false;
		var add_shop_price = $("*[ectype='add_shop_price']").val();

		$(function(){
			if(add_shop_price == 0){
				$(":input[name='goods_attr_id']").val('');
                $(":input[name='goods_spec']").val('');
			}
		});

		/**
		 * 点选可选属性或改变数量时修改商品价格的函数
		 */
		function changePrice(type)
		{

			var qty = 1;
			var goods_attr_id = '';
			var goods_attr = '';
			var attr_id = '';
			var attr = '';

		   var region_id = $(":input[name='region_id']").val();
			var area_id = $(":input[name='area_id']").val();

			goods_attr_id = getSelectedAttributes(document.forms['ECS_FORMBUY']);
			$("#goods_attr_id").val(goods_attr_id);
            $("#goods_spec").val(goods_attr_id);

			if(type != 1){
				if(add_shop_price == 0){
					attr_id = getSelectedAttributesGroup(document.forms['ECS_FORMBUY']);
					goods_attr = '&goods_attr=' + attr_id;
				}
				Ajax.call('group_buy.php', 'act=price&id=' + goodsId + '&attr=' + goods_attr_id + goods_attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id, changePriceResponse, 'GET', 'JSON');
			}else{
				attr = '&attr=' + goods_attr_id;
				Ajax.call('group_buy.php', 'act=price&id=' + goodsId + attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&type=' + type, changePriceResponse, 'GET', 'JSON');
			}
		}

		/**
		 * 接收返回的信息
		 */
		function changePriceResponse(res)
		{
		  if (res.err_msg.length > 0)
		  {
			pbDialog(res.err_msg,"",0);
		  }
		  else
		  {

              @if ($min_amount > 0)
                  document.forms['ECS_FORMBUY'].elements['number'].value = {{ $min_amount }};
              @else
                  document.forms['ECS_FORMBUY'].elements['number'].value = res.qty;
              @endif

			//ecmoban模板堂 --zhuo satrt
			if (document.getElementById('goods_attr_num'))
			{
				$("*[ectype='goods_attr_num']").html(res.attr_number);
		  		$("*[ectype='perNumber']").val(res.attr_number);

				if(res.err_no == 2){
					$('#isHas_warehouse_num').html(json_languages.shiping_prompt);
				}else{
					if (document.getElementById('isHas_warehouse_num')){
						var isHas;
						if(res.attr_number > 0){
							isHas = '<strong>'+json_languages.Have_goods+'</strong>，'+json_languages.Deliver_back_order;
						}else{
							isHas = '<strong>'+json_languages.No_goods+'</strong>，'+ json_languages.goods_over;
						}

						$('#isHas_warehouse_num').html(isHas);
					}
				}

                //加载完成后才能操作，解决未加载完成就能加入购物车bug
                $(".choose-btns").show();
			}
		  }
		  if(res.type == 1){
			quantity();
		  }
		}
    </script>
    {{-- DSC 提醒您：动态载入goods_delivery_area_js.lbi，显示首页分类小广告 --}}
{!! insert_goods_delivery_area_js(['area' => $area]) !!}
</body>
</html>
