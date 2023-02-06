
@if($ad_child)


@foreach($ad_child as $key => $ad)

<div class="banner" style="background:{{ $ad['link_color'] }};">
    <div class="w w1200 relative">
        <div class="act-banner-wp">
            <a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" style="max-width:{{ $ad['ad_width'] }}px; max-height:{{ $ad['ad_height'] }}px;"></a>
        </div>
    </div>
</div>

@endforeach


@endif
