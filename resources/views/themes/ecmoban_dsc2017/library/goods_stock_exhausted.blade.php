
<div class="tip-box icon-box">
    <span class="warn-icon m-icon"></span>
    <div class="item-fore">
        <h3><strong>{{ $lang['folw_sub_null'] }}</strong></h3>
    </div>
</div>
<div class="goods-items" id="out-skus">

@foreach($goods_list as $goodsRu)


@foreach($goodsRu['new_list'] as $key => $activity)


@if($activity['act_id'] > 0)

        	<div class="goods-suit">
                <div class="goods-suit-tit">
                	<strong>
                    	【{{ $activity['act_type_txt'] }}】

@if($activity['act_type'] == 0)


@if($activity['act_type_ext'] == 0)


@if($activity['available'])

                                	{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} ，{{ $lang['receive_gifts'] }}
@if($activity['cart_favourable_gift_num'] > 0)
，{{ $lang['Already_receive'] }}{{ $activity['cart_favourable_gift_num'] }}{{ $lang['jian'] }}
@endif


@else

                                	{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['cart_goods_one'] }}

@endif


@else


@if($activity['available'])

                                	{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} ，
@if($activity['cart_favourable_gift_num'] > 0)
{{ $lang['cart_goods_two'] }}
@else
{{ $lang['cart_goods_three'] }}
@endif


@else

                                	{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['cart_goods_one'] }}

@endif


@endif


@elseif ($activity['act_type'] == 1)


@if($activity['available'])

                            	{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} ,{{ $lang['been_reduced'] }}{{ $activity['act_type_ext_format'] }}{{ $lang['cart_goods_four'] }}

@else

                            	{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['cart_goods_five'] }}

@endif


@elseif ($activity['act_type'] == 2)


@if($activity['available'])

                            	{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} ，{{ $lang['Already_enjoy'] }}{{ $activity['act_type_ext_format'] }}{{ $lang['percent_off_Discount'] }}

@else

                            	{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['zhekouxianzhi'] }}

@endif


@endif

                    </strong>
                </div>

@foreach($activity['act_cart_gift'] as $goods)

                <div class="goods-item nostock-item">
                    <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="80" height="80"></a></div>
                    <div class="goods-msg">
                    	<div class="p-name">
                        	<em class="s-ico">{{ $lang['largess'] }}</em>
                            <a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a>
                        </div>
                    </div>
                    <div class="p-stock">

@if($goods['attr_number'])


@if($goods['attr_number']  >= $goods['goods_number'])

                            {{ $lang['Have_goods'] }}

@else

                            <span class="ftx-01">{{ $lang['Stock_goods_null'] }}</span>

@endif


@else

                        <span class="ftx-01">{{ $lang['No_goods'] }}</span>

@endif

                    </div>
                </div>

@endforeach



@foreach($activity['act_goods_list'] as $goods)

                <div class="goods-item nostock-item">
                    <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="80" height="80"></a></div>
                    <div class="goods-msg">
                    	<div class="p-name">
                        	<em class="s-ico">{{ $lang['largess'] }}</em>
                            <a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a>
                        </div>
                    </div>
                    <div class="p-stock">

@if($goods['attr_number'])


@if($goods['attr_number']  >= $goods['goods_number'])

                            {{ $lang['Have_goods'] }}

@else

                            <span class="ftx-01">{{ $lang['Stock_goods_null'] }}</span>

@endif


@else

                        <span class="ftx-01">{{ $lang['No_goods'] }}</span>

@endif

                    </div>
                </div>

@endforeach

            </div>

@else


@foreach($activity['act_goods_list'] as $goods)

                <div class="goods-item nostock-item">
                    <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="80" height="80"></a></div>
                    <div class="goods-msg">
                    	<div class="p-name">
                        	<em class="s-ico">{{ $lang['largess'] }}</em>
                            <a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a>
                        </div>
                    </div>
                    <div class="p-stock">

@if($goods['attr_number'])


@if($goods['attr_number']  >= $goods['goods_number'])

                                {{ $lang['Have_goods'] }}

@else

                                <span class="ftx-01">{{ $lang['Stock_goods_null'] }}</span>

@endif


@else

                            <span class="ftx-01">{{ $lang['No_goods'] }}</span>

@endif

                    </div>
                </div>

@endforeach


@endif


@endforeach


@endforeach

</div>

<div class="hide">
<form action="flow.php" method="post" name="stockFormCart">
    <input type="submit" value="{{ $lang['go_pay'] }}" class="submit-btn" name="goPay">
    <input type="hidden" value="checkout" name="step">
    <input type="hidden" value="{{ $store_seller }}" name="store_seller">
    <input type="hidden" value="{{ $store_id }}" name="store_id">
    <input type="hidden" value="1" name="goods_stock_exhausted">
    <input type="hidden" value="{{ $cart_value }}" id="cart_value" name="cart_value">
@csrf </form>
</div>
