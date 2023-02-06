
@if($best_goods)


@foreach($best_goods as $goods)

<li>
	<div class="gs-item">
		<div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" width="166" height="166"/></a></div>
		<div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['short_style_name'] }}">{{ $goods['short_style_name'] }}</a></div>
		<div class="p-price">
			
@if($goods['promote_price'] != '')

				{{ $goods['promote_price'] }}
			
@else

				{{ $goods['shop_price'] }}
			
@endif

		</div>
		<div class="p-num">{{ $lang['sales_volume'] }}{{ $goods['sales_volume'] }}</div>
	</div>
</li>

@endforeach


@endif
