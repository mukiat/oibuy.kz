

@if($ad_child)

<div class="floor-new">
<ul>
	
@foreach($ad_child as $ad)

    <li 
@if($loop->last)
 class="last"
@endif
>
    	<div class="new-left">
    		<a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" style=" max-width:{{ $ad['ad_width'] }}px; max-height:{{ $ad['ad_height'] }}px; text-align:center; float:none;"></a>
        </div>
    </li>
    
@endforeach

</ul>
</div>

@endif
