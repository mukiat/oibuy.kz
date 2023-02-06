

@if($ad_child)

<div class="sign-content">
    <div class="bd">
        <ul>
        
@foreach($ad_child as $ad)

            <li><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" /></a></li>
        
@endforeach

        </ul>
    </div>
    <div class="hd"><ul></ul></div>
    <a href="javascript:void(0);" class="prev"></a>
    <a href="javascript:void(0);" class="next"></a>
</div>

@endif

