
@foreach($ad_child as $ad)

<div class="home-brand-adv slide_lr_info"><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" class="slide_lr_img"></a></div>

@endforeach

