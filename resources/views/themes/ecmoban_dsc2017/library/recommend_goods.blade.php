
<div class="seckill-all">
	<div class="title"><img src="{{ skin('/images/seckill_title_bg.png') }}"></div>
	<div class="seckill-warp">
		<ul class="gb-index-list clearfix">
		
@foreach($guess_goods as $goods)

			<li class="mod-shadow-card">
				<div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['goods_thumb'] }}"></a></div>
				<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}">{!! $goods['goods_name'] !!}</a></div>
				<div class="p-lie clearfix">
					<div class="p-pirce">{{ $goods['sec_price_formated'] }}</div>
					<div class="p-del"><del>{{ $goods['market_price_formated'] }}</del></div>
				</div>
				<div class="p-number clearfix">
					<span>{{ $lang['sold_alt'] }}{{ $goods['percent'] }}%</span>
					<div class="timebar"><i style="width:{{ $goods['percent'] }}%;"></i></div>
				</div>				
				<a href="{{ $goods['url'] }}" class="btn sc-redBg-btn">{{ $lang['button_buy'] }}</a>
			</li>
		
@endforeach

		</ul>
	</div>
</div>