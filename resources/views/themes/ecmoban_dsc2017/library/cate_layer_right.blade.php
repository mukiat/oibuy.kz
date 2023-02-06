
@if($ad_child)

<div class="cate-ad">

@foreach($ad_child as $ad)

    <a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" alt=""></a>

@endforeach

</div>

@endif

