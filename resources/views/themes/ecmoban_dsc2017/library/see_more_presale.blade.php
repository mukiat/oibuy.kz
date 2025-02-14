

@if($look_top)

<div class="track_warp">
    <div class="track-tit"><h3>{{ $lang['look_and_see'] }}</h3><span></span></div>
    <div class="track-con">
        <ul>
            
@foreach($look_top as $look_top)

            <li>
                <div class="p-img"><a href="{{ $look_top['url'] }}" target="_blank" title="{{ $look_top['goods_name'] }}"><img src="{{ $look_top['goods_thumb'] }}" width="140" height="140"></a></div>
                <div class="p-name"><a href="{{ $look_top['url'] }}" target="_blank" title="{{ $look_top['goods_name'] }}">{{ $look_top['goods_name'] }}</a></div>
                <div class="price">{{ $look_top['shop_price'] }}</div>
            </li>
            
@endforeach

        </ul>
    </div>
    <div class="track-more">
        <a href="javascript:void(0);" class="sprite-up"><i class="iconfont icon-up"></i></a>
        <a href="javascript:void(0);" class="sprite-down"><i class="iconfont icon-down"></i></a>
    </div>
</div>

@endif
