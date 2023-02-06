

@if($hot_goods)

<div class="hot-sales">
    <div class="hotsale w1390 w">
        <div class="hatsale-mt">{{ $lang['Popular_recommendation'] }}</div>
        <div class="bd">
            <ul>

@foreach($hot_goods as $goods)


@if($loop->iteration < 5)

                <li
@if($loop->iteration == 4)
 class="last"
@endif
>
                    <div class="item">
                        <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" /></a></div>
                        <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['short_style_name'] }}" target="_blank">{!! $goods['short_style_name'] !!}</a></div>
                        <div class="p-price">

@if($goods['promote_price'] != '')

                                {{ $goods['promote_price'] }}

@else

                                {{ $goods['shop_price'] }}

@endif

                        </div>
                        <div class="p-btn"><a class="btn sc-redBg-btn" href="{{ $goods['url'] }}">{{ $lang['button_buy'] }}</a></div>
                    </div>
                </li>

@endif


@endforeach

            </ul>
            <a href="javascript:void(0);" class="prev"></a>
            <a href="javascript:void(0);" class="next"></a>
        </div>
    </div>
</div>

@endif
