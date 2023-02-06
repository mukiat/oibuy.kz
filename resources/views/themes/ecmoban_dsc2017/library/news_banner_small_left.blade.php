

@if($ad_child)


@foreach($ad_child as $ad)

<a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" />
@if($ad['b_title'])
<p>{{ $ad['b_title'] }}</p>
@endif
</a>

@endforeach


@endif
