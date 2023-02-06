
<div class="lift-channel clearfix" id="guessYouLike">
	<div class="ftit"><h3>{{ $lang['not_see_enough'] }}</h3></div>
	<ul>
		
@foreach($guess_goods as $goods)

		<li class="opacity_img">
			<a href="{{ $goods['url'] }}">
				<div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
				<div class="p-name" title="{{ $goods['short_name'] }}">{{ $goods['short_name'] }}</div>
				<div class="p-price">
					<div class="shop-price">{{ $goods['shop_price'] }}</div>
					<div class="original-price">{{ $goods['market_price'] }}</div>
				</div>
			</a>
		</li>
		
@endforeach

	</ul>
</div>