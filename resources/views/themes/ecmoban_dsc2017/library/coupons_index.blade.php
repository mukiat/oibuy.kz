
<div id="g-slider">
    <div id="g-scroll">
        <div class="silder-panel">
            <ul>
                
@foreach($ad_child as $ad)

                <li class="silder-item" style="background:{{ $ad['link_color'] }};"><div class="w1200 w"><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" /></a></div></li>
                
@endforeach

            </ul>
        </div>
        <div class="num-ctrl"><ul></ul></div>
    </div>
</div>