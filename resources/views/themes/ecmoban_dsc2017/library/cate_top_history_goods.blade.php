
@if($history_goods)

<div class="atwillgo" id="atwillgo">
	<div class="awg-hd">
		<h2>{{ $lang['Browsing_record'] }}</h2>
	</div>
	<div class="awg-bd">
		<div class="atwillgo-slide">
			<a href="javascript:;" class="prev"><i class="iconfont icon-left"></i></a>
			<a href="javascript:;" class="next"><i class="iconfont icon-right"></i></a>
			<div class="hd">
				<ul></ul>
			</div>
			<div class="bd">
				<ul>
					
@foreach($history_count as $hi)

					
@foreach($hi as $goods)

					<li>
						<div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['goods_thumb'] }}" alt=""></a></div>
						<div class="p-price">
                            
@if($goods['promote_price'] != '')

                                {{ $goods['promote_price'] }}
                            
@else

                                {{ $goods['shop_price'] }}
                            
@endif

						</div>
						<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['short_name'] }}">{{ $goods['short_name'] }}</a></div>
						<div class="p-btn"><a href="{{ $goods['url'] }}">{{ $lang['View_details'] }}</a></div>
					</li>
					
@endforeach

					
@endforeach

				</ul>
			</div>
		</div>
	</div>
</div>
<input type="hidden" name="history" value="1">

@else

<input type="hidden" name="history" value="0">

@endif

