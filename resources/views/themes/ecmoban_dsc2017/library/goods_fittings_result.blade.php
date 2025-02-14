
<input type="hidden" name="fittings_goods" id="fittings_goods" value="{{ $goods_id }}">
<div class="goods_fittings_main">
    <div class="header">
        <div class="tm-combo-mitem">

@foreach($fittings as $key => $goods_list)


@if($key == 0)

            <div class="tm-img"><a href="{{ $goods_list['url'] }}" title="{{ $goods_list['short_name'] }}" target="_blank" class="combo_goods_{{ $goods_list['goods_id'] }}"><img src="{{ $goods_list['goods_thumb'] }}"></a></div>

@endif


@endforeach

        </div>
        <div class="tm-combo-spliter">+</div>
        <div class="slideBox">
            <div class="bd">
                <ul>

@foreach($fittings_top as $key => $goods_list)

                    <li>
                    	<div class="tm-combo-item tm-combo-item_div">
            				<div class="tm-img">
                            	<a href="javascript:void(0);" title="{{ $goods_list['short_name'] }}" class="combo_goods_{{ $goods_list['goods_id'] }}"><img src="{{ $goods_list['goods_thumb'] }}"></a>
                            </div>
                            <div class="tm-enable
@if($goods_list['selected'] == 1)
 selected
@endif
" rev='{{ $goods_list['group_top'] }}'></div>
                        </div>
                    </li>

@endforeach

				</ul>
            </div>
            <a href="javascript:void(0);" class="prev"><i class="iconfont icon-left"></i></a>
            <a href="javascript:void(0);" class="next"><i class="iconfont icon-right"></i></a>
        </div>
        <div class="tm-combo-info">
            <p class="tm-combo-price">{{ $lang['Match_price'] }}：<s class='fittings_minMax' id='fittings_minMax_top'>{{ $fittings_minMax }}</s></p>
            <p class="tm-save-price">{{ $lang['sheng'] }}<s class='save_minMaxPrice'>{{ $save_minMaxPrice }}</s></p>
            <p class="tm-original-price">{{ $lang['combo_markPrice'] }}：<s class='market_minMax'>{{ $market_minMax }}</s></p>
            <p class="tm-count">{{ $lang['Already_match'] }}<s class='collocation_number'> {{ $collocation_number }} </s>{{ $lang['jian'] }}</p>
        </div>
        <div class="tm-combo-warnning"></div>
    </div>
    <div class="body fitts_body">
        <div class="title">{{ $lang['Package_flow_desc'] }}</div>
        <div class="tm-combo-content">

@foreach($fittings as $key => $goods_list)

            <form action="javascript:void(0);" method="post" name="ECS_FORMBUY_{{ $goods_list['goods_id'] }}" id="ECS_FORMBUY_{{ $goods_list['goods_id'] }}" data-goodsid="{{ $goods_list['goods_id'] }}">
            <div class="tm-combo-item
@if($goods_list['goods_number'] == 0)
hover
@endif
" id="tm-combo-item_{{ $goods_list['goods_id'] }}"
@if($key % 2 == 0)
  style="clear:left;"
@else
  style="clear:none;"
@endif
>
                <div class="tm-img">
                    <a href="{{ $goods_list['url'] }}" title="{{ $goods_list['short_name'] }}" target="_blank" class="combo_goods_{{ $goods_list['goods_id'] }}"><img src="{{ $goods_list['goods_thumb'] }}" width="60" height="60"></a>
                </div>
                <div class="tm-meta" rev="{{ $goods_list['goods_id'] }}">
                    <dl class="tb-stock tm-clear">
                        <dt class="tm-metatit">{{ $lang['goods_name'] }}：</dt>
                        <dd><span class="tm-goods-name">{{ $goods_list['short_name'] }}</span></dd>
                    </dl>

@foreach($goods_list['properties']['spe'] as $spec_key => $spec)


@if($spec['name'])

                    <dl class="tb-prop tm-clear fitt_input">
                        <dt class="tm-metatit">{{ $spec['name'] }}：</dt>
                        <dd>
                            <ul>

@if($spec['is_checked'] > 0)


@foreach($spec['values'] as $val_key => $value)

                                <li
@if($value['combo_checked'] == 1)
 class="selected"
@endif
 rev="{{ $value['id'] }}" onclick="fitt_changeAtt(this, {{ $goods_list['goods_id'] }}, '{{ $group_rev }}',
@if($key == 0)
1
@else
0
@endif
, $('#fittings_goods').val());">
                                   <b></b>
                                   <a href="javascript:void(0);">

@if($value['img_flie'])

                                    <img width="24" height="24" src="{{ $value['img_flie'] }}" />

@endif

                                    <i>{{ $value['label'] }}</i>
                                    <input id="fitt_spec_value_{{ $value['id'] }}" type="radio" name="fitt_spec_{{ $spec_key }}" class="fitt_spec_value" value="{{ $value['id'] }}"
@if($value['combo_checked'] == 1)
checked="checked"
@endif
 />
                                    </a>
                                </li>

@endforeach


@else


@foreach($spec['values'] as $val_key => $value)

                                <li
@if($value['combo_checked'] == 1)
class="selected"
@endif
 onclick="fitt_changeAtt(this, {{ $goods_list['goods_id'] }}, '{{ $group_rev }}',
@if($key == 0)
1
@else
0
@endif
, $('#fittings_goods').val());">
                                	<b></b>
                                    <a href="javascript:void(0);">
                                    	<i>{{ $value['label'] }}</i>
                                    	<input id="fitt_spec_value_{{ $value['id'] }}" type="radio" name="fitt_spec_{{ $spec_key }}" class="fitt_spec_value" value="{{ $value['id'] }}"
@if($value['combo_checked'] == 1)
checked="checked"
@endif
 />
                                	</a>
                                </li>

@endforeach


@endif

                            </ul>
                        </dd>
                    </dl>

@endif


@endforeach

                    <dl class="tb-stock tm-clear">
                        <dt class="tm-metatit">{{ $lang['goods_inventory'] }}：</dt>
                        <dd>
                        	<span class="tm-stock_{{ $goods_list['goods_id'] }}">{{ $goods_list['goods_number'] }}</span>
                        	<span class="tm-stock_title_{{ $goods_list['goods_id'] }}" style="display:none;"><font style="color:#F00;">({{ $lang['goods_null'] }})</font></span>
                        </dd>
                    </dl>
                </div>
                <div class="tm-notice">{{ $lang['goods_info_select'] }}</div>
                <input name="fitt_jq_{{ $goods_list['goods_id'] }}" class="fitt_jq_{{ $goods_list['goods_id'] }}" value="" type="hidden">
            </div>
            @csrf </form>

@endforeach

        </div>
    </div>
    <div class="footer">
        <div class="tm-buy">
        	<form name="{{ $group }}_result" method="post" action="" onSubmit="return false;">
                <input type="hidden" name="stock" value="{{ $group_number }}" />

@if($group_number > 0)
<span class="mr20">{{ $lang['gb_limited'] }} <em class="red">{{ $group_number }}</em> {{ $lang['tao'] }}</span>
@endif

                <span>{{ $lang['btn_buy'] }}</span>
                <input value="{{ $number ?? 1 }}" style="text-align:center" id="J_SComboAmount_group" name="{{ $group }}_result_number">
                <span>{{ $lang['tao'] }}</span>
                <span class="tm-combo-totalPrice">{{ $lang['Total_flow'] }}&nbsp;&nbsp;<s class='fittings_minMax' id='list_select'>
@if($list_select == 1)
{{ $fittings_minMax }}
@else
{{ $null_money }}
@endif
</s></span>
                <button style="display: none;" class="J_ComboBuy">{{ $lang['confirm_buy'] }}</button>
                <button class="J_ComboAddCart" onClick="addMultiToCart('{{ $group }}', '{{ $goods_id }}', '{{ $warehouse_id }}', '{{ $area_id }}')">{{ $lang['confirm_cart'] }}</button>
            @csrf </form>
        </div>
        <div class="tm-combo-notice">{{ $lang['confirm_cart_two'] }}</span>
    </div>
</div>

<script type="text/javascript">
	//商品搭配 弹窗内 多个商品滚动切换
	$(".slideBox").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"left",autoPlay:false,scroll:1,vis:4,pnLoop:false});
	$(".tm-combo-content").hover(function(){
		$(".tm-combo-content").perfectScrollbar("destroy");
		$(".tm-combo-content").perfectScrollbar();
	});
</script>
