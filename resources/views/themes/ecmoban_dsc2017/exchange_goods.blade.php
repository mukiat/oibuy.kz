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
			@include('frontend::library/goods_merchants_top')
        </div>
    </div>
	<div class="container">
    	<div class="w w1200">
        	<div class="product-info mt20">
            	@include('frontend::library/goods_gallery')
                <div class="product-wrap">
                <form action="exchange.php?act=buy" method="post" name="ECS_FORMBUY" id="ECS_FORMBUY" onsubmit="return get_exchange();" >
                	<div class="name">{{ $goods['goods_style_name'] }}</div>

@if($goods['goods_brief'])

                    <div class="newp">{{ $goods['goods_brief'] }}</div>

@endif

                    <div class="activity-title">
                    	<div class="activity-type">{{ $lang['exchange_name'] }}</div>
                    </div>
                    <div class="summary">
                    	<div class="summary-price-wrap">
                        	<div class="s-p-w-wrap">
                                <div class="summary-item si-shop-price">
                                    <div class="si-tit">{{ $lang['integral'] }}</div>
                                    <div class="si-warp">
                                        <strong class="shop-price">{{ $goods['exchange_integral'] }}</strong>
                                    </div>
                                </div>
                                <div class="summary-item si-market-price">
                                    <div class="si-tit">{{ $lang['market_price'] }}</div>
                                    <div class="si-warp">

@if($goods['market_integral'])

                                    		{{ $goods['market_integral'] }}&nbsp;{{ $lang['integral'] }}

@else

                                        	{{ $goods['market_price'] }}

@endif

                                    </div>
                                </div>
                                <div class="si-info">
                                    <div class="si-cumulative">{{ $lang['evaluate_count'] }}<em>{{ $goods['comments_number'] }}</em></div>
                                    <div class="si-cumulative">{{ $lang['Sales_count'] }}<em>{{ $goods['sales_volume'] ?? 0 }}</em></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="summary-basic-info">
                        	<div class="summary-item is-stock">
                                <div class="si-tit">{{ $lang['distribution_tit'] }}</div>
                                <div class="si-warp">
                                    <span class="initial-area">

@if($adress['city'])

                                            {{ $adress['city'] }}

@else

                                            {{ $basic_info['city'] }}

@endif

                                    </span>
                                    <span>{{ $lang['zhi'] }}</span>
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
                                        <input class="text buy-num" id="quantity" ectype="quantity" value="1" name="number" defaultnumber="1">
                                        <div class="a-btn">
                                            <a href="javascript:void(0);" class="btn-add" ectype="btnAdd"><i class="iconfont icon-up"></i></a>
                                            <a href="javascript:void(0);" class="btn-reduce btn-disabled" ectype="btnReduce"><i class="iconfont icon-down"></i></a>
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
                        <div class="choose-btns ml60 clearfix">
                             <input type="hidden" value="{{ $goods_id }}" id="good_id" name="goods_id"/>
                             <input type="hidden" value="{{ $user_id }}" id="user_id" name="user_id"/>
                             <input type="hidden" value="{{ $user['payPoints'] }}" name="payPoints" ectype="payPoints" />
                             <input type="hidden" value="{{ $goods['exchange_integral'] }}" name="integral" ectype="exchange_integral" />
                             <input type="hidden" value="" name="goods_spec"/>
                             <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price" ectype="add_shop_price" />
                             <input type="submit" value="{{ $lang['like_exchange'] }}" class="button btn-append " style="display: none;"/>
                        </div>
                    </div>
                @csrf </form>
                </div>

@if($look_top)

                <div class="track">
                	<div class="track_warp">
                    	<div class="track-tit"><h3>{{ $lang['look_and_see'] }}</h3><span></span></div>
                        <div class="track-con">
                            <ul>

@foreach($look_top as $look_top)

                                <li>
                                    <a href="exchange.php?act=view&id={{ $look_top['goods_id'] }}" target="_blank" title="{{ $look_top['goods_name'] }}"><img src="{{ $look_top['goods_thumb'] }}" width="140" height="140"><p class="price">{{ $look_top['goods_name'] }}</p></a>
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

@endif

                <div class="clear"></div>
            </div>
            <div class="goods-main-layout">
            	<div class="g-m-left">
                	@include('frontend::library/goods_merchants')
                    <div class="g-main g-rank">
                        <div class="mc">
                        	<ul class="mc-tab">
                            	<li class="curr">{{ $lang['is_new'] }}</li>
                                <li>{{ $lang['Recommend'] }}</li>
                                <li>{{ $lang['Selling'] }}</li>
                            </ul>
                        	<div class="mc-content">

                                @include('frontend::library/recommend_new_goods')



                                @include('frontend::library/recommend_best_goods')



                                @include('frontend::library/recommend_hot_goods')

                            </div>
                        </div>
                    </div>

@if($related_goods)

                    <div class="g-main g-history">
                    	<div class="mt">
                        	<h3>{{ $lang['user_love'] }}</h3>
                        </div>
                        <div class="mc">
                        	<div class="mc-warp">
                            	<ul>

@foreach($related_goods as $item)

                                    <li>
                                    	<div class="p-img"><a href="{{ $item['url'] }}" target="_blank"><img src="{{ $item['goods_thumb'] }}" width="170" height="170"></a></div>
                                        <div class="p-lie">
                                        	<div class="p-price">

@if($item['promote_price'] != '')

                                                {{ $item['formated_promote_price'] }}

@else

                                                {{ $item['shop_price'] }}

@endif

                                            </div>
                                            <div class="p-comm"><i class="iconfont icon-comment"></i><div class="p-c-comm">4</div></div>
                                        </div>
                                    </li>

@endforeach

                                </ul>
                            </div>
                        </div>
                    </div>

@endif

                </div>
                <div class="g-m-detail">
                	<div class="gm-tabbox" ectype="gm-tabs">
                    	<ul class="gm-tab">
                        	<li class="curr" ectype="gm-tab-item">{{ $lang['Product_details'] }}</li>

@if($properties)
<li ectype="gm-tab-item-spec">{{ $lang['specification'] }}</li>
@endif

                            <li ectype="gm-tab-item">{{ $lang['user_comment'] }}（<em class="ReviewsCount">{{ $comment_all['allmen'] }}</em>）</li>
                            <li ectype="gm-tab-item">{{ $lang['discuss_user'] }}</li>
                        </ul>
                        <div class="extra"></div>
                        <div class="gm-tab-qp-bort" ectype="qp-bort"></div>
                    </div>
                    <div class="gm-floors" ectype="gm-floors">
                        <div class="gm-f-item gm-f-details" ectype="gm-item">
                            <div class="gm-title">
                                <h3>{{ $lang['Product_details'] }}</h3>
                            </div>
                            {!! $goods['goods_desc'] !!}
                        </div>

@if($properties)

                        <div class="gm-f-item gm-f-parameter" ectype="gm-item" id="product-detail" style="display:none;">
                            <div class="gm-title">
                                <h3>{{ $lang['specification'] }}</h3>
                            </div>
                            <div class="Ptable">

@foreach($properties as $key => $property_group)

                                <div class="Ptable-item">
                                    <h3>{{ $key }}</h3>
                                    <dl>

@foreach($property_group as $property)

                                        <dt>{{ $property['name'] }}</dt>
                                        <dd title="{{ $property['value'] }}">{{ $property['value'] }}</dd>

@endforeach

                                    </dl>
                                </div>

@endforeach

                            </div>
                        </div>

@endif

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
                        <div class="gm-f-item gm-f-tiezi" ectype="gm-item">
                            {{-- DSC 提醒您：动态载入goods_discuss_title.lbi，显示首页分类小广告 --}}
{!! insert_goods_discuss_title(['goods_id' => $goods['goods_id']]) !!}
                            <div class="table" id='discuss_list_ECS_COMMENT'>
                                @include('frontend::library/comments_discuss_list1')
                            </div>
                            <input type="hidden" value="{{ $goods_id }}" id="good_id" name="good_id">
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="rection">
                	<div class="ftit"><h3>{{ $lang['Recent_browse'] }}</h3></div>
                    <ul>

@foreach($history_goods as $item)


@if($loop->iteration <= 5)

                    	<li>
                        	<div class="p-img"><a href="{{ $item['url'] }}" target="_blank"><img src="{{ $item['goods_thumb'] }}" width="232" height="232"></a></div>
                            <div class="p-name"><a href="{{ $item['url'] }}" target="_blank">{{ $item['short_name'] }}</a></div>
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
            </div>
        </div>
    </div>

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/magiczoomplus.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
		//商品详情悬浮框
		goods_desc_floor();

		//右侧看了又看上下滚动
		$(".track_warp").slide({mainCell:".track-con ul",effect:"top",pnLoop:false,autoPlay:false,autoPage:true,prevCell:".sprite-up",nextCell:".sprite-down",vis:3});

		//左侧新品 热销 推荐排行切换
		$(".g-rank").slide({titCell:".mc-tab li",mainCell:".mc-content",titOnClassName:"curr"});

		//积分商品js
        var goodsId = {{ $goods['goods_id'] }};
        var goods_id = {{ $goods['goods_id'] }};
		var isReturn = false;

		var add_shop_price = $("*[ectype='add_shop_price']").val();

        /**
         * 点选可选属性或改变数量时修改商品价格的函数
         */
        function changePrice(type){
			var qty = $("*[ectype='quantity']").val();
			var goods_attr_id = '';
			var goods_attr = '';
			var attr_id = '';
			var attr = '';
			var region_id = $(":input[name='region_id']").val();
		  	var area_id = $(":input[name='area_id']").val();

			if(!region_id){
			   region_id = {{ $region_id ?? 0 }};
		    }

		    if(!area_id){
			   area_id = {{ $area_id ?? 0 }};
		    }

			goods_attr_id = getSelectedAttributes(document.forms['ECS_FORMBUY']);

			if(type != 1){
				if(add_shop_price == 0){
					attr_id = getSelectedAttributesGroup(document.forms['ECS_FORMBUY']);
					goods_attr = '&goods_attr=' + attr_id;
				}
				Ajax.call('goods.php', 'act=price&id=' + goodsId + '&attr=' + goods_attr_id + goods_attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id, changePriceResponse, 'GET', 'JSON');
			}else{
				attr = '&attr=' + goods_attr_id;
				Ajax.call('goods.php', 'act=price&id=' + goodsId + attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&type=' + type, changePriceResponse, 'GET', 'JSON');
			}
		}
        /**
         * 接收返回的信息
         */
        function changePriceResponse(res)
        {
          if (res.err_msg.length > 0)
          {
            var message = res.err_msg;

			pbDialog(message,"",0);
          }
          else
          {

            document.forms['ECS_FORMBUY'].elements['number'].value = res.qty;

            //ecmoban模板堂 --zhuo satrt
            if (document.getElementById('goods_attr_num')){
			  	$("*[ectype='goods_attr_num']").html(res.attr_number);
		  		$("*[ectype='perNumber']").val(res.attr_number);
		  		if(res.attr_number > 0){
                    $('input.btn-append').show();
                }
            }

            if(res.err_no == 2){
                $('#isHas_warehouse_num').html(shiping_prompt);
            }else{
                if (document.getElementById('isHas_warehouse_num')){
                  var isHas;
                  if(res.attr_number > 0){
                      $('#sold_out').remove();
					  $('input.btn-append').show();
                      isHas = '<strong>'+json_languages.Have_goods+'</strong><i style="font-size:12px; font-weight:normal">，'+json_languages.Deliver_back_order+'</i>';
                  }else{
                      isHas = '<strong>'+json_languages.No_goods+'</strong>，'+json_languages.goods_over;
                        $('input.btn-append').hide();
                        if(!document.getElementById('sold_out')){
                            $('.choose-btns').append('<a id="sold_out" class="btn-invalid" href="javascript:;">暂时缺货</a>')
                        }


@if($goods['review_status'] >= 3)

                            if(!document.getElementById('quehuo')){
                                $('div#compareLink').before('<a id="quehuo" href="javascript:addToCart({{ $goods['goods_id'] }});"></a>');
                            }

@endif

                  }
                  document.getElementById('isHas_warehouse_num').innerHTML = isHas;
                }
            }
            //ecmoban模板堂 --zhuo end
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
