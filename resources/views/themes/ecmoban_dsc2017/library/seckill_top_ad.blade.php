
@if($ad_child)

<div class="banner seckill-banner">
	
@foreach($ad_child as $ad)

	<a href="{{ $ad['ad_link'] }}" style="background:url({{ $ad['ad_code'] }}) center center no-repeat; width:100%; height:190px;"></a>
	
@endforeach

</div>

@endif
