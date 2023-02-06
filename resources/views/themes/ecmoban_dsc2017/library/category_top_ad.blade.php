

@if($ad_child)

<div class="category_adv w1390">
	
@foreach($ad_child as $key => $ad)

	<div 
@if($key % 2)
class="left"
@else
class="right"
@endif
><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" style="max-width:{{ $ad['ad_width'] }}px; max-height:{{ $ad['ad_height'] }}px;"/></a></div>
    
@endforeach

</div>

@endif
