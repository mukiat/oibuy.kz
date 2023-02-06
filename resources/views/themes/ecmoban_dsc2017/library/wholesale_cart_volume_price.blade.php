
@if($goods['price_model'] == 1)

<div class="lie">
	
@foreach($goods['volume_price'] as $volume)

	
@if(!$loop->last)

	<p
@if(!$volume['is_reached'])
 class="ftx-03"
@endif
><label>{{ $volume['volume_number'] }}-{{ $volume['range_number'] }}件：</label>{{ $volume['volume_price'] }}</p>
	
@else

	<p
@if(!$volume['is_reached'])
 class="ftx-03"
@endif
><label>≥{{ $volume['volume_number'] }}件：</label>{{ $volume['volume_price'] }}</p>
	
@endif

	
@endforeach

</div>

@else

<div class="lie">
	<p
@if(!$goods['is_reached'])
 class="ftx-03"
@endif
><label>≥{{ $goods['moq'] }}件：</label>{{ $goods['goods_price'] }}</p>
</div>

@endif
