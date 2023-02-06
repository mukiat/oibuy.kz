
@if($history_goods)

<div class="g-main g-history">
	<div class="mt">
		<h3>最近浏览</h3>
		<a onclick="clear_history()" class="clear_history ftx-05 fr mt10 mr10" href="javascript:void(0);">{{ $lang['clear'] }}</a>
	</div>
	<div class="mc">
		<div class="mc-warp" id="history_list" ectype="history_mian">
			<ul>
				
@foreach($history_goods as $goods)

                
@if($loop->index < 5)

				<li>
					<div class="p-img"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}"><img src="{{ $goods['goods_thumb'] }}" width="170" height="170"></a></div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['short_name'] }}</a></div>
					<div class="p-lie">
						<div class="p-price">
							
@if($goods['promote_price'] != '')

								{{ $goods['promote_price'] }}
							
@else

								{{ $goods['shop_price'] }}
							
@endif

						</div>
					</div>
				</li>
                
@endif

				
@endforeach

			</ul>
		</div>
	</div>
</div>

@endif
