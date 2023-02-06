<div class="marBanner">
    
@foreach($ad_child as $child)

        <a href="{{ $child['ad_link'] }}" target="_blank"><img src="{{ $child['ad_code'] }}" width="{{ $child['ad_width'] }}" height="{{ $child['ad_height'] }}" alt="" /></a>
    
@endforeach
                             	
</div>
