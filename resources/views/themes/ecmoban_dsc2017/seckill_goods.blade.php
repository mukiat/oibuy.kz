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
	<div class="full-main-n">
        <div class="w w1200 relative">
			@include('frontend::library/ur_here')
        </div>
    </div>
    <div class="container">
    	<div class="w w1200">
        	<div class="product-info mt20">
            	@include('frontend::library/seckill_goods_gallery')
                <div class="product-wrap">
                    <form action="seckill.php?act=buy" method="post" name="ECS_FORMBUY" id="ECS_FORMBUY" >
                        @if (!empty($goods['country_icon']) && $cross_border_version)
                        <div class="name" style="font-size: 12px; font-weight: normal; margin-bottom: 3px; color: #7a45e5">
                            <img src="{{$goods['country_icon']}}" width="20px" /> <em style="position: relative; top: 1px;">{{$goods['country_name']}}</em>
                        </div>
                        @endif
                	<div class="name">{{ $goods['goods_name'] }}</div>
                    @if($goods['goods_brief'])
                    <div class="newp">{{ $goods['goods_brief'] }}</div>
                    @endif
                    <div class="activity-title">
                    	<div class="activity-type"><i class="icon icon-promotion"></i>{{ $lang['seckill'] }}</div>
                        <div class="sk-time-cd">
                            <div class="sk-cd-tit">
                                @if($goods['is_end'] && !$goods['status'])
                                {{ $lang['end_time'] }}
                                @elseif (!$goods['is_end'] && $goods['status'])
                                {{ $lang['residual_time'] }}
                                @else
                                {{ $lang['begin_time_soon'] }}
                                @endif
                            </div>
                            <div class="cd-time time" ectype="time" data-time="
                            @if(!$goods['is_end'] && $goods['status'])
                            {{ $goods['formated_end_date'] }}
                            @else
                            {{ $goods['formated_start_date'] }}
                            @endif
                            ">

                                @if($goods['is_end'] && !$goods['status'] )
                                    {{ $goods['formated_end_date'] }}
                                @else
                                    <div class="days hide">00</div>
                                    <span class="split hide">:</span>
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
                                    <div class="si-tit">{{ $lang['seckill_price'] }}</div>
                                    <div class="si-warp">
                                        <strong class="shop-price" id="ECS_SHOPPRICE" ectype="SHOP_PRICE">{{ $goods['formated_sec_price'] }}</strong>
                                    </div>
                                </div>
                                @if($cfg['show_marketprice'])
                                <div class="summary-item si-market-price">
                                    <div class="si-tit">{{ $lang['market_prices'] }}</div>
                                    <div class="si-warp"><div class="m-price" id="ECS_MARKETPRICE">{{ $goods['formated_market_price'] }}</div></div>
                                </div>
                                @endif

                                @if($cross_border_version)
                                    <div class="summary-item si-market-price">
                                        <div class="si-tit">{{ $lang['import_tax'] }}</div>
                                        <div class="si-warp">
                                            <div id="import_tax"></div>
                                        </div>
                                    </div>
                                @endif

                                <div class="si-info">
                                    <div class="si-cumulative">{{ $lang['Sales_count'] }}<em>{{ $goods['sales_volume'] ?? 0 }}</em></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>

                        <div class="summary-basic-info">
                        	<div class="summary-item is-stock">
                            	<div class="si-tit">{{ $lang['Distribution'] }}</div>
                                <div class="si-warp">
                                    <span class="initial-area">

@if($adress['city'])

                                            {{ $adress['city'] }}

@else

                                            {{ $basic_info['city'] }}

@endif

                                    </span>
									<span>至</span>
                                    <div class="store-selector">
                                    	<div class="text-select" id="area_address" ectype="areaSelect"></div>
                                    </div>
                                    <div class="store-warehouse">
                                    	<div class="store-warehouse-info"></div>
                                        <div id="isHas_warehouse_num" class="store-prompt"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="summary-item is-service">
                            	<div class="si-tit">{{ $lang['deliver_goods'] }}</div>
                                <div class="si-warp">
                                    {{ $goods['cross_warehouse_name']  }}
								</div>
                            </div>

                            <div class="summary-item is-service">
                                <div class="si-tit">{{ $lang['gs_service'] }}</div>
                                <div class="si-warp">
                                    {{ $lang['you'] }} <a href="{{ $goods['store_url'] }}" class="link-red" target="_blank">{{ $goods['rz_shop_name'] }}</a> {{ $lang['After_sale_service'] }}
                                    @if($shippingFee['is_shipping'] != 1)
                                        <span class="gary">{{ $lang['is_shipping_area'] }}</span>
                                    @else
                                        <span class="gary">[ {{ $lang['shipping'] }}：{{ $shippingFee['shipping_fee_formated'] }} ]</span>
                                    @endif
                                </div>
                            </div>


@if($goods['sec_limit'])

                            <div class="summary-item is-xiangou">
                            	<div class="si-tit">{{ $lang['gb_limited'] }}</div>
                                <div class="si-warp">
                                	<em id="restrict_amount" ectype="restrictNumber" data-value="{{ $goods['sec_limit'] }}">{{ $goods['sec_limit'] }}</em>
                                    <span>
@if($goods['goods_unit'])
{{ $goods['goods_unit'] }}
@else
{{ $goods['measure_unit'] }}
@endif
</span>
                                    <span>（{{ $lang['js_languages']['Already_buy'] }}：<em id="orderG_number" ectype="orderGNumber">{{ $orderG_number ?? 0 }}</em>
@if($goods['goods_unit'])
{{ $goods['goods_unit'] }}
@else
{{ $goods['measure_unit'] }}
@endif
）</span>
                                </div>
                            </div>

@endif



@if($specification)


@foreach($specification as $spec_key => $spec)


@if($spec['values'])

                            <div class="summary-item is-attr goods_info_attr" ectype="is-attr"
    @if($spec['attr_type'] == 1)
        data-type="radio"
    @else
        data-type="checkbox"
    @endif
>
                            	<div class="si-tit">{{ $spec['name'] }}</div>

@if($cfg['goodsattr_style'] == 1)

                                <div class="si-warp">
									<ul>

@foreach($spec['values'] as $key => $value)


@if($spec['is_checked'] > 0)

                                    <li class="item @if($value['checked'] == 1) selected @endif" date-rev="{{ $value['img_site'] }}" data-name="{{ $value['id'] }}">
                                        <b></b>
                                        <a href="javascript:void(0);">

                                            @if($value['img_flie']) <img src="{{ $value['img_flie'] }}" width="24" height="24" />@endif

											<i>{{ $value['label'] }}</i>
											<input id="spec_value_{{ $value['id'] }}"
                                                   @if($spec['attr_type'] == 2)
                                                   type="checkbox"
                                                   @else
                                                   type="radio"
                                                   @endif

                                                   @if($spec['attr_type'] == 2)
                                                   data-attrtype="2"
                                                   @else
                                                   data-attrtype="1"
                                                @endif
name="spec_{{ $spec_key }}" value="{{ $value['id'] }}" autocomplete="off" class="hide" />

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

                                    <li class="item @if($key == 0) selected @endif ">
                                        <b></b>
                                        <a href="javascript:void(0);" name="{{ $value['id'] }}" class="noimg">
											<i>{{ $value['label'] }}</i>
											<input id="spec_value_{{ $value['id'] }}"

                                            @if($spec['attr_type'] == 2)
                                                   type="checkbox"
                                            @else
                                                   type="radio"
                                            @endif

                                           @if($spec['attr_type'] == 2)
                                            data-attrtype="2"
                                           @else
                                            data-attrtype="1"
                                           @endif

 name="spec_{{ $spec_key }}" value="{{ $value['id'] }}" autocomplete="off" class="hide" /></a>

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

                                <input type="hidden" name="spec_list" value="{{ $spec_key }}"
                                @if($spec['attr_type'] == 1)
                                    data-spectype="attr-radio"
                                @else
                                    data-spectype="attr-check"
                                @endif
 />
                            </div>

@endif


@endforeach


@endif


                            <div class="summary-item is-number">
                            	<div class="si-tit">{{ $lang['gs_number'] }}</div>
                                <div class="si-warp">
                                	<div class="amount-warp">
                                        <input class="text buy-num" id="quantity" ectype="quantity" value="1" name="number" defaultnumber="1">
                                        <div class="a-btn">
                                            <a href="javascript:void(0);" class="btn-add" ectype="btnAdd"><i class="iconfont icon-up"></i></a>
                                            <a href="javascript:void(0);" class="btn-reduce btn-disabled" ectype="btnReduce"><i class="iconfont icon-down"></i></a>
                                            <input type="hidden" name="perNumber" id="perNumber" ectype="perNumber" value="0">
                                            <input type="hidden" name="perMinNumber" id="perMinNumber" ectype="perMinNumber" value="1">
                                        </div>
                                    </div>
                                    <span>{{ $lang['goods_inventory'] }}&nbsp;<em id="goods_attr_num" ectype="goods_attr_num"></em></span>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="choose-btns ml60 clearfix" style="display: none;">

@if($goods['is_end'] && !$goods['status'])

                            <a href="javascript:void(0);" class="btn-invalid">{{ $lang['seckill_end'] }}</a>

@elseif ($goods['sec_num'] <= 0 && $goods['status'])

                            <a href="javascript:void(0);" class="btn-invalid">{{ $lang['over_tobuy'] }}</a>

@elseif (!$goods['is_end'] && !$goods['status'])

                            <a href="javascript:void(0);" class="btn-invalid">{{ $lang['comming_soon'] }}</a>

@else

                            <a href="javascript:void(0);" class="btn-append" id="btn-click" ectype="btn-seckill">{{ $lang['seckill_now'] }}</a>

@endif

                        </div>
                    </div>
                    <input type="hidden" value="" name="goods_spec" id="goods_spec" />
                    <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price" ectype="add_shop_price" />
                    <input type="hidden" value="{{ $goods['goods_id'] }}" id="good_id" name="good_id">
                    <input type="hidden" value="{{ $seckill_id }}" name="sec_goods_id" />
                    <input type="hidden" value="{{ $goods['sec_limit'] }}" name="restrictShop" ectype="restrictShop" />
                    @csrf </form>
                </div>
                <div class="track">
                	<div class="track_warp">
                    	<div class="track-tit"><h3>{{ $lang['see_to_see'] }}</h3><span></span></div>
                        <div class="track-con">
                        	<ul>

@foreach($look_top as $look_top)

                                <li>
                                	<div class="p-img"><a href="{{ $look_top['url'] }}" target="_blank"><img src="{{ $look_top['goods_thumb'] }}" width="140" height="140"></a></div>
                                    <div class="p-name"><a href="{{ $look_top['url'] }}" target="_blank" title="{{ $look_top['goods_name'] }}">{{ $look_top['goods_name'] }}</a></div>
                                    <div class="price">

@if($look_top['promote_price'] != '')

                                            {{ $look_top['promote_price'] }}

@else

                                            {{ $look_top['shop_price'] }}

@endif

                                    </div>
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
                        	<h3>{{ $lang['merchant_rec'] }}</h3>
                        </div>
                        <div class="mc">
                        	<div class="mc-warp">
                            	<ul>

@foreach($merchant_seckill_goods as $merchant_seckill_goods)

                                        <li>
                                            <div class="p-img"><a href="{{ $merchant_seckill_goods['url'] }}" target="_blank"><img src="{{ $merchant_seckill_goods['goods_thumb'] }}" width="130" height="130"></a></div>
                                            <div class="p-name"><a href="{{ $merchant_seckill_goods['url'] }}" title="{{ $merchant_seckill_goods['goods_name'] }}" target="_blank">{{ $merchant_seckill_goods['goods_name'] }}</a></div>
                                            <div class="p-price">{{ $merchant_seckill_goods['act_name'] }}</div>
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
                        <div class="gm-floors" ectype="gm-floors">
                    	<div class="gm-f-item gm-f-parameter" ectype="gm-item">
                            <dl class="goods-para">
                                <dd class="column"><span>{{ $lang['goods_name'] }}：{{ $goods['goods_name'] }}</span></dd>
                                <dd class="column"><span>{{ $lang['Commodity_number'] }}：{{ $goods['goods_sn'] }}</span></dd>
                                <dd class="column"><span>{{ $lang['seller_store'] }}：<a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" target="_blank">{{ $goods['rz_shop_name'] }}</a></span></dd>
                            </dl>

@if($properties)

                        	<dl class="goods-para">

@foreach($properties as $key => $property_group)


@foreach($property_group as $property)

                                <dd class="column"><span title="{{ $property['value'] }}">{{ $property['name'] }}：{{ $property['value'] }}</span></dd>

@endforeach


@endforeach

                            </dl>

@endif


@if($extend_info)

							<dl class="goods-para">

@foreach($extend_info as $key => $info)

								<dd class="column"><span title="{{ $info }}">{{ $key }}：{{ $info }}</span></dd>

@endforeach

							</dl>

@endif

                        </div>
                        <div class="gm-f-item gm-f-details" ectype="gm-item">
                        	<div class="gm-title">
                            	<h3>{{ $lang['description'] }}</h3>
                            </div>
                            {!! $goods['goods_desc'] !!}
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
<script type="text/javascript" src="{{ asset('js/warehouse.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/magiczoomplus.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>

	<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>

    <script type="text/javascript">
	//商品详情悬浮框
	goods_desc_floor();

	//商品相册小图滚动
	$(".spec-list").slide({mainCell:".spec-items ul",effect:"left",trigger:"click",pnLoop:false,autoPage:true,scroll:1,vis:5,prevCell:".spec-prev",nextCell:".spec-next"});

	//右侧看了又看上下滚动
	$(".track_warp").slide({mainCell:".track-con ul",effect:"top",pnLoop:false,autoPlay:false,autoPage:true,prevCell:".sprite-up",nextCell:".sprite-down",vis:3});

	/*团购倒计时*/
	$(".time").each(function(){
		$(this).yomi();
	});
    </script>
    <script type="text/javascript">
	var goods_id = '{{ $goods['goods_id'] ?? 0 }}';
    var goodsId = '{{ $goods['goods_id'] ?? 0 }}'
	var seckill_id = '{{ $seckill_id }}';
	var isReturn = false;
	var add_shop_price = $("*[ectype='add_shop_price']").val();

	$(function(){
		if(add_shop_price == 0){
			$(":input[name='goods_spec']").val('');
		}
	});

	/**
	 * 点选可选属性或改变数量时修改商品价格的函数
	 */
	function changePrice(type)
	{
        var qty = $("*[ectype='quantity']").val();
        var goods_attr_id = '';
		var goods_spec = '';
		var goods_attr = '';
		var attr_id = '';
		var attr = '';

	    var region_id = $(":input[name='region_id']").val();
		var area_id = $(":input[name='area_id']").val();
		var area_city = $(":input[name='area_city']").val();

		goods_attr_id = getSelectedAttributes(document.forms['ECS_FORMBUY']);

		$("#goods_spec").val(goods_attr_id);

		if(type != 1){
            if(add_shop_price == 0){
                attr_id = getSelectedAttributesGroup(document.forms['ECS_FORMBUY']);
                goods_attr = '&goods_attr=' + attr_id;
            }
			Ajax.call('seckill.php', 'act=price&id=' + seckill_id  + '&goods_id=' + goods_id + '&attr=' + goods_attr_id + goods_attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&area_city=' + area_city, changePriceResponse, 'GET', 'JSON');
		}else{
			attr = '&attr=' + goods_attr_id;
			Ajax.call('seckill.php', 'act=price&id=' + seckill_id + '&goods_id=' + goods_id + attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&area_city=' + area_city + '&type=' + type, changePriceResponse, 'GET', 'JSON');
		}
	}

	/**
	 * 接收返回的信息
	 */
	function changePriceResponse(res)
	{
	  if (res.err_msg.length > 0) {
          pbDialog(res.err_msg, " ", 0, 450, 80, 50);
	  } else {

		document.forms['ECS_FORMBUY'].elements['number'].value = res.qty;

		if (document.getElementById('goods_attr_num')) {
			$("*[ectype='goods_attr_num']").html(res.attr_number);
		  	$("*[ectype='perNumber']").val(res.attr_number);

			var attr_number = Number(res.attr_number);
			if(attr_number > 0){
				 isHas = '<strong>'+json_languages.Have_goods+'</strong>，'+json_languages.Deliver_back_order;
		  	}else{
				 isHas = '<strong>'+json_languages.No_goods+'</strong>，'+json_languages.goods_over;
		  	}

			$("#isHas_warehouse_num").html(isHas);

            //加载完成后才能操作，解决未加载完成就能加入购物车bug
            $(".choose-btns").show();
		}

          if ($("#ECS_SHOPPRICE").length > 0) {
              //市场价
              if ($("#ECS_MARKETPRICE").length > 0) {
                  $("#ECS_MARKETPRICE").html(res.result_market);
              }

              //秒杀商品属性价格
              $("*[ectype='SHOP_PRICE']").html(res.spec_price_formated);
          }

          //秒杀商品限购数量
          $("*[ectype='restrictNumber']").html(res.sec_limit);
          $("*[ectype='restrictNumber']").data("value", res.sec_limit);

          // 秒杀属性禁止选择
          if (res.spec_disable == 1) {
              $('#btn-click').removeAttr('ectype');
              $('#btn-click').removeClass('btn-append').addClass('btn-invalid');
          } else {
              $('#btn-click').attr("ectype", 'btn-seckill');
              $('#btn-click').removeClass('btn-invalid').addClass('btn-append');
          }

          @if($cross_border_version)
          //进口税
          if ($("#import_tax").length > 0) {
              $("#import_tax").html(res.formated_goods_rate);
          }
          @endif
	  }
	  if(res.type == 1){
		quantity();
	  }
	}

    //未登录秒杀弹出登录框
    $("*[ectype='btn-seckill']").on('click',function(){

        if ($(this).attr('ectype') == undefined || $(this).attr('ectype') == 'undefined') {
            console.log($(this).attr('ectype'), 'disabled')
            return false;
        }

		var user_id = Number({{ $user_id }});
		var quantity = Number($("#quantity").val());
		var goods_number = Number( $('#goods_attr_num').html());
		var restrict_amount = $('#restrict_amount').html();
		var err = true;
		var cssType = true;
		if(user_id > 0){
			//限购
			if(restrict_amount > 0){
				var orderG_number = {{ $orderG_number }} + quantity;
				if({{ $orderG_number }} > 0 && {{ $orderG_number }} >= restrict_amount){
					message = json_languages.Already_buy+'{{ $orderG_number }}'+json_languages.Already_buy_two;
					quantity = 1;
					err = false;
					cssType = false;
				}else if({{ $orderG_number }} > 0 && orderG_number > restrict_amount){
					var buy_num = restrict_amount - {{ $orderG_number }};
					message = json_languages.Already_buy+'{{ $orderG_number }}'+json_languages.Already_buy_three + buy_num + json_languages.jian;
					quantity = buy_num;
					err = false;
				}else if(quantity > restrict_amount){
					  message = json_languages.Purchase_quantity;
					  quantity = restrict_amount;
					  err = false;
				}

				if(err == false){
					pbDialog(message,"",0);
					return false;
				}
			}

			if(goods_number == 0 || quantity > goods_number){
				pbDialog(json_languages.Stock_goods_null,"",0,450,80,50);
				return false;
			}else{
				$("form[name='ECS_FORMBUY']").submit();
			}
		}else{
			var back_url = "seckill.php?act=view&id=" + {{ $seckill_id }};
			$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
			return false;
		}
    });
    </script>
    {{-- DSC 提醒您：动态载入goods_delivery_area_js.lbi，显示首页分类小广告 --}}
{!! insert_goods_delivery_area_js(['area' => $area]) !!}
</body>
</html>
