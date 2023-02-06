

@if($ad_child)

<div class="banner">
    <div class="pre-banner">
        <div class="bd">
            <ul>
            	
@foreach($ad_child as $ad)

                <li style="background-color:{{ $ad['link_color'] }};"><div class="banner-width"><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" /></a></div></li>
                
@endforeach

            </ul>
        </div>
        <div class="hd"><ul></ul></div>
    </div>
</div>

@endif

