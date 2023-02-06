

@if($ad_child)


@foreach($ad_child as $ad)

<a href="{{ $ad['ad_link'] }}"><img src="{{ $ad['ad_code'] }}" alt=""></a>

@endforeach


@endif

				