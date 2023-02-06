
@if($best_goods)

<div class="goods-spread">
    <a href="javascript:void(0);" class="g-stop" ectype="gstop"><i class="iconfont icon-right"></i></a>
    <div class="gs-warp">
        <div class="gs-tit">{{ $lang['tuiguang_goods'] }}</div>
        <ul class="gs-list">
            
@foreach($best_goods as $goods)

                <li class="opacity_img">
                <div class="">
                        <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" width="190" height="190"></a></div>
                        <div class="p-price">
                        
@if($goods['promote_price'] != '')

                            {{ $goods['promote_price'] }}
                        
@else

                            {{ $goods['shop_price'] }}
                        
@endif

                        </div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['short_style_name'] }}">{{ $goods['short_style_name'] }}</a></div>
                    <div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                </div>
            </li>
            
@endforeach

        </ul>
    </div>
</div>

@endif
