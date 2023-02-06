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
    <div class="content activity-desc-content">
        <div class="w w1200">
            <div class="crumbs-nav">
                <div class="crumbs-nav-main clearfix">
                     @include('frontend::library/ur_here')
                </div>
            </div>
            <div class="activity-desc-main clearfix">
                <div class="acde-left">
                    <div class="product-info clearfix">
                        @include('frontend::library/goods_gallery')
                        <div class="product-wrap">
                            <div class="name">{{ $auction['goods_name'] }}</div>
                            <div class="activity-title">
                                <div class="activity-type"><i class="iconfont icon-time"></i>
@if($auction['status_no'] == 0)
{{ $lang['au_pre_start'] }}
@elseif ($auction['status_no'] == 1)
{{ $lang['underway'] }}
@else
{{ $lang['finished'] }}
@endif
</div>
                                <div class="sk-time-cd">
                                    <div class="sk-cd-tit">
@if($auction['status_no'] == 0)
{{ $lang['end_time'] }}
@elseif ($auction['status_no'] == 1)
{{ $lang['residual_time'] }}
@else
{{ $lang['end_time'] }}
@endif
</div>
                                    <div class="cd-time" ectype="time" data-time="{{ $auction['end_time'] }}">

@if($auction['status_no'] == 0)

                                        {{ $lang['au_pre_start'] }}

@elseif ($auction['status_no'] == 1)

                                        <div class="days">00</div>
                                        <span class="split">:</span>
                                        <div class="hours">00</div>
                                        <span class="split">:</span>
                                        <div class="minutes">00</div>
                                        <span class="split">:</span>
                                        <div class="seconds">00</div>

@else

                                        {{ $auction['bid_time'] }}

@endif

                                    </div>
                                </div>
                            </div>
                            <div class="summary">
                            	<form action="auction.php" method="post" name="ECS_FORMBUY" id="formBid" onsubmit="return auction_view(this)">
                                <div class="summary-price-wrap">
                                    <div class="s-p-w-wrap clearfix">
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['shop_Price_dis'] }}：</div>
                                            <div class="si-warp">
                                                <span class="price original-price">{{ $goods['shop_price_formated'] }}</span>
                                            </div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['current_price_snatch'] }}：</div>
                                            <div class="si-warp">
                                                <span class="price red"><span id="currentPrice" data-price="{{ $auction['current_price'] }}">{{ $auction['formated_current_price'] }}</span></span>
                                                {{ $lang['au_bid_price'] }}<span class="red">{{ $auction_count }}</span>{{ $lang['ci'] }}
                                            </div>
                                        </div>

@if($auction['deposit'] > 0)

                                        <div class="summary-item">
                                            <div class="si-tit">{{ $lang['au_deposit'] }}：</div>
                                            <div class="si-warp">
                                                <span class="price">{{ $auction['formated_deposit'] }}</span>
                                                {{ $lang['deposit_notic'] }}
                                            </div>
                                        </div>

@endif

                                    </div>
                                </div>


@if($specification)

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

@endif



@if($auction['status_no'] == 0)

                                <div class="choose-btns clearfix">
                                    <a href="javascript:void(0);" class="btn-invalid">{{ $lang['not_start'] }}</a>
                                </div>

@elseif ($auction['status_no'] == 1)

                                <div class="summary-count-wp">
                                    <div class="amount-wrap-2">
                                    	<input type="hidden" name="max-price" id="maxPrice" value="
@if($auction['end_price'] > 0)
{{ $auction['end_price'] }}
@else
100000000000000
@endif
" />
                                        <i class="iconfont icon-reduce" onclick="decre()"></i>
                                        <input type="text" name="buy-price" value="{{ $auction['current_price_int'] }}" id="buyPrice">
                                        <i class="iconfont icon-plus" onclick="incre()"></i>
                                    </div>
                                    <span>{{ $lang['min_fare'] }}：<span id="priceLowerOffset" data-amplitude="{{ $auction['amplitude'] }}">{{ $auction['formated_amplitude'] }}</span>
                                </div>
                                <div class="choose-btns clearfix">
                                    <input name="act" type="hidden" value="bid" />
                                    <input name="id" type="hidden" value="{{ $auction['act_id'] }}" />
                                    <input type="submit" value="{{ $lang['au_i_want_bid'] }}" class="button btn-append" />
                                </div>

@else


@if($auction['is_winner'])

                                    <div class="choose p-choose-wrap mt20">
                                        <div class="me_grab">{{ $lang['au_is_winner'] }}</div>
                                        <div class="me_bid">
                                            <span>{{ $lang['nin_want_bid'] }}：</span>
                                            <div class="p-price">{{ $auction['last_bid']['bid_price'] }}</div>
                                        </div>
                                        <div class="choose-btns clearfix">
                                            <input name="act" type="hidden" value="buy" />
                                            <input name="id" type="hidden" value="{{ $auction['act_id'] }}" />
                                            <input name="buy" type="submit" class="button btn-append" value="{{ $lang['button_buy'] }}" />
                                        </div>
                                    </div>

@else

                                    <div class="choose-btns clearfix">
                                        <a href="javascript:void(0);" class="btn-invalid">{{ $lang['has_ended'] }}</a>
                                    </div>

@endif


@endif


                                	<input type="hidden" value="" name="goods_attr_id" id="goods_attr_id" />
                                	<input type="hidden" value="{{ $goods_id ?? 0 }}" name="goods_id" />
                                    <input type="hidden" value="{{ $region_id ?? 0 }}" name="region_id" />
                                    <input type="hidden" value="{{ $area_id ?? 0 }}" name="area_id" />
                                    <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price" ectype="add_shop_price" />
                                @csrf </form>
                                <div class="summary-item summary-row-2">
                                    <div class="si-tit">{{ $lang['au_start_price_two'] }}：</div>
                                    <div class="si-warp">{{ $auction['formated_start_price'] }}</div>
                                    <div class="si-tit">{{ $lang['au_mechanism'] }}：</div>
                                    <div class="si-warp">{{ $goods['rz_shop_name'] }}</div>

@if($auction['end_price'] > 0)

                                    <div class="si-tit">{{ $lang['au_end_price_two'] }}：</div>
                                    <div class="si-warp">{{ $auction['formated_end_price'] }}</div>

@endif

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="auction-progress clearfxi">
                        <div class="apt">{{ $lang['auction_step'] }}</div>
                        <div class="apb">
                            <ul>
                                <li class="bt-info-tips">
                                	01.{{ $lang['auction_01'] }}<i class="iconfont icon-arrow-right-alt"></i>
                                    <div class="tips">
                                    	<div class="sprite-arrow"></div>
                                        <div class="content">{{ $lang['auction_notic_01'] }}</div>
                                    </div>
                                </li>
                                <li class="bt-info-tips">
                                	02.{{ $lang['auction_02'] }}<i class="iconfont icon-arrow-right-alt"></i>
                                	<div class="tips">
                                    	<div class="sprite-arrow"></div>
                                        <div class="content">{{ $lang['auction_notic_02'] }}</div>
                                    </div>
                                </li>
                                <li class="bt-info-tips">
                                	03.{{ $lang['auction_03'] }}<i class="iconfont icon-arrow-right-alt"></i>
                                	<div class="tips">
                                    	<div class="sprite-arrow"></div>
                                        <div class="content">{{ $lang['auction_notic_03'] }}</div>
                                    </div>
                                </li>
                                <li class="bt-info-tips">
                                	04.{{ $lang['auction_04'] }}<i class="iconfont icon-arrow-right-alt"></i>
                                    <div class="tips">
                                    	<div class="sprite-arrow"></div>
                                        <div class="content">{{ $lang['auction_notic_04'] }}</div>
                                    </div>
                                </li>
                                <li class="bt-info-tips">
                                	05.{{ $lang['auction_05'] }}
                                    <div class="tips">
                                    	<div class="sprite-arrow"></div>
                                        <div class="content">{{ $lang['auction_notic_05'] }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="activity-desc-detail" id="detail-slide">
                        <div class="hd">
                            <ul>
                                <li>{{ $lang['auction_introduce'] }}</li>
                                <li>{{ $lang['auction_record'] }}</li>
                                <li>{{ $lang['service_guarantee'] }}</li>
                                <li>{{ $lang['auction_strategy'] }}</li>
                            </ul>
                        </div>
                        <div class="bd">
                            <div class="bd-item">
                                <div class="desc-detail-title">{{ $lang['auction_introduce'] }}</div>
                                <div class="desc-detail-content">

@if($auction['act_desc'])

                                    	{!! $auction['act_desc'] !!}

@else

                                    	<div class="no_records">
                                        	<i class="no_icon_two"></i>
                                            <div class="no_info">
                                            	<h3>{{ $lang['auction_introduce_notic'] }}</h3>
                                            </div>
                                        </div>

@endif

                                </div>
                            </div>
                            <div class="bd-item" id="price_list_count" style="display:none;">
                                @include('frontend::library/snatch_price')
                            </div>
                            <div class="bd-item clearfix" style="display:none;">
                                <div class="desc-detail-content">

@if($auction['act_promise'])

                                    {!! $auction['act_promise'] !!}

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

@if($auction['act_ensure'])

                                    {!! $auction['act_ensure'] !!}

@else

                                    <div class="no_records">
                                        <i class="no_icon_two"></i>
                                        <div class="no_info">
                                            <h3>{{ $lang['auction_strategy_notic'] }}</h3>
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
<div class="org-logo"><img src="{{ $goods['shopinfo']['brand_thumb'] }}" alt=""></div>
@endif

                        <p><span>{{ $lang['au_mechanism'] }}：</span>{{ $goods['rz_shop_name'] }}</p>
                        <div class="clearfix">
                            <a id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}" class="a-kefu"><i class="icon i-kefu"></i><span>{{ $lang['con_cus_service'] }}</span></a>
                        </div>
                    </div>
                    <div id="records-list">
                        @include('frontend::library/snatch')
                    </div>
                    <div class="acde-right-title">{{ $lang['rec_au'] }}</div>
                    <ul class="side-goods">

@foreach($hot_goods as $goods)

                        <li>
                            <div class="p-img">
                                <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" title="{{ $goods['short_style_name'] }}" /></a>
                            </div>
                            <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['short_style_name'] }}">{{ $goods['short_style_name'] }}</a></div>
                            <div class="p-price">{{ $goods['auction']['formated_start_price'] }}</div>
                        </li>

@endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position(['ru_id' => $goods['user_id']]) !!}

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/magiczoomplus.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>

    <script type="text/javascript">

	//全局变量
	var goods_id = {{ $goods_id ?? 0 }};
	var goodsId = {{ $goods_id ?? 0 }};
	var add_shop_price = $("*[ectype='add_shop_price']").val();

	$(function(){
		changePrice(1);

		if(add_shop_price == 0){
			$(":input[name='goods_attr_id']").val('');
		}
	});

	//价格加减
	increase();

	//商品相册小图滚动
	$(".spec-list").slide({mainCell:".spec-items ul",effect:"left",trigger:"click",pnLoop:false,autoPage:true,scroll:1,vis:5,prevCell:".spec-prev",nextCell:".spec-next"});

	/*倒计时*/
	$(".cd-time").each(function(){
		$(this).yomi();
	});

	//拍卖详情切换
	$("#detail-slide").slide({titCell:".hd li",mainCell:".bd",titOnClassName:"on",trigger: "click"});

    //by kong start
	function auction_view(){
		var user_id = Number({{ $user_id }});
		//判断登录
		if(user_id > 0){
			var buyPrice=$("#buyPrice").val();//写入价格
			var qp_price = Number({{ $auction['start_price'] ?? 0 }});
			var current_price = Number({{ $auction['current_price'] ?? 0 }});//当前价格
			var amplitude = Number({{ $auction['amplitude'] ?? 0 }});//加价幅度
			var all_price= amplitude + current_price;
			var is_winner = "{{ $auction['is_winner'] ?? 0 }}";
			var deposit= Number({{ $auction['deposit'] ?? 0 }});
			var user_money = Number({{ $user['user_money'] ?? 0 }});

			if({{ $auction['end_price'] }})
			{
				var end_price = "{{ $auction['end_price'] }}";
			}
			else
			{
				var end_price = 0;
			}

			//判断出价
			if(is_winner > 0){
				return true;
			}else{
				if(buyPrice < all_price && qp_price != buyPrice){
					var message = "{{ $lang['au_your_lowest_price_wen'] }}" + all_price + "!";

					pbDialog(message,"",0);
					return false;
				}else{

@foreach($auction_log as $item)

					@if($loop->index == 0)
						var top_user_id = {{ $item['user_id'] }};
						if(end_price > 0)
						{
							if(top_user_id == user_id && buyPrice < end_price){
								var message = "{{ $lang['au_bid_repeat_user'] }}";

								pbDialog(message,"",0);
								return false;
							}
						}
						else
						{
							if(top_user_id == user_id){
								var message = "{{ $lang['au_bid_repeat_user'] }}";

								pbDialog(message,"",0);
								return false;
							}
						}
					@endif

@endforeach


				    //判断保证金
					if(deposit > 0){
						//判断可用资金
						if(user_money < deposit){
							var message = "{{ $lang['au_user_money_short'] }}";

							pbDialog(message,"",0,'','',40);
							return false;
						}
					}
				}
			}

			return true;
		}else{
			var back_url = "auction.php?act=view&id=" + {{ $auction['act_id'] }};
			$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
			return false;
		}
	}
	//by kong end

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

				pbDialog(json_languages.out_of_stock_notic," ",0,450,80,50);
			}else{
				$(".btn-append").show();
			}
		}
	}
   </script>
</body>
</html>
