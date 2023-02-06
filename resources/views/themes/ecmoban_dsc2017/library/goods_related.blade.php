

@if($related_goods)

<div class="g-main g-recommend">
	<div class="mt">
		<h3>{{ $lang['releate_goods'] }}</h3>
	</div>
	<div class="mc">
		<div class="mc-warp">
			<ul>
				
@foreach($related_goods as $goods)

				<li>
					<div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="170" height="170"></a></div>
					<div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['short_name'] }}</a></div>
					<div class="p-price">
						
@if($goods['promote_price'] != '')

						{{ $goods['formated_promote_price'] }}
						
@else

						{{ $goods['shop_price'] }}
						
@endif
					
					</div>
				</li>
				
@endforeach

			</ul>
		</div>
	</div>
</div>

@endif
