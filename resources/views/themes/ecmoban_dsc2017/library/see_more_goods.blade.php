

@if($see_more_goods['goods_list'])

<div class="track_warp">
    <div class="track-tit"><h3>看了又看</h3><span></span></div>
    <div class="track-con">
        <ul>
            
@foreach($see_more_goods['goods_list'] as $goods)

            <li>
                <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}"><img src="{{ $goods['goods_thumb'] }}" width="140" height="140"></a></div>
                <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
                <div class="price">
                    
@if($goods['promote_price'] != '')

                        {{ $goods['promote_price'] }}
                    
@else

                        {{ $goods['shop_price'] }}
                    
@endif
										
                </div>
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
