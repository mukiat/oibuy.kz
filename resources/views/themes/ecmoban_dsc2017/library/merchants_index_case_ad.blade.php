
@if($ad_child)


@foreach($ad_child as $ad)

<div class="item item{{ $loop->iteration }}">
    <div class="item-top">
        <a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}"></a>
    </div>
    <div class="item-bot">
        <div class="tit">{{ $ad['b_title'] }}</div>
        <div class="desc">{{ $ad['s_title'] }}</div>
    </div>
</div>

@endforeach


@endif
