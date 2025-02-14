
@if($script_name == 0)

<div class="title">
    <h3>{{ $lang['Prompt'] }}</h3>
	<a onclick="$('.ecsc-cart-popup').css({'display':'none'});" title="关闭" class="loading-x">X</a>
</div>
<div class="center_pop_p">
	<div class="ts">{{ $lang['successfully_added_shoping'] }}</div>
    <div class="desc">
        <span>{{ $lang['cart_count'] }}</span>
        <strong>({{ $goods_number }}{{ $lang['jain'] }})</strong>
        <span>{{ $lang['Baby_total_amount'] }}：</span>
        <em class="saleP">{{ config('shop.currency_format', '¥') }}{{ $goods_amount }}</em>
    </div>
</div>

@elseif ($script_name == 1)

<a class="success_close" href="javascript:void(0);" onClick="close_div({{ $goods_id }},'{{ $goods_recommend }}')"></a><p class="addSucess_tip">{{ $lang['cart_baby_success'] }}</p><p class="cart_num">{{ $lang['cart_count'] }}{{ $real_goods_count }}{{ $lang['zhong_boby'] }}({{ $goods_number }}{{ $lang['jian'] }})</p><p class="cart_price">{{ $lang['total_cart'] }}：<span class="cart_priceNum">{{ config('shop.currency_format', '¥') }}{{ $goods_amount }}{{ $lang['yuan'] }}</span></p><a class="cart_account" href="./flow.php">{{ $lang['pay_to_cart'] }}</a>

@endif
