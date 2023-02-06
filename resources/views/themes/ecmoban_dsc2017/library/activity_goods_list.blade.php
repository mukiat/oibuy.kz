

@if($activity['goods_list'])


@foreach($activity['goods_list'] as $goods)

<li class="mod-shadow-card">
	<a href="goods.php?id={{ $goods['goods_id'] }}" target="_blank" class="img"><img src="{{ $goods['goods_thumb'] }}" alt="{{ $goods['goods_name'] }}"></a>
	<a href="goods.php?id={{ $goods['goods_id'] }}" target="_blank" class="name">{{ $goods['goods_name'] }}</a>
	<div class="price">
	
@if($goods['promote_price'] != '')

		{{ $goods['promote_price'] }}
	
@else

		{{ $goods['shop_price'] }}
	
@endif
	
	</div>
	<a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem')"  rev="{{ $goods['goods_thumb'] }}" data-dialog="addCart_dialog" data-id="" data-divid="addCartLog" data-url="" data-title="{{ $lang['select_attr'] }}"  class="act-btn">{{ $common_lang['collect_to_flow'] }}</a>
</li>

@endforeach


@endif
