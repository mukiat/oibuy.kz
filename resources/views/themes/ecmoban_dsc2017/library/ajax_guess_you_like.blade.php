            <div class="p-panel-main guess-love">
                <div class="ftit ftit-delr"><h3>{{ $lang['guess_love'] }}</h3></div>
                <div class="gl-list clearfix">
                    <ul class="clearfix">
                        
@foreach($guess_goods as $goods)

                        
@if($loop->iteration < 8)

                        <li class="opacity_img">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="190" height="190"></a></div>
                            <div class="p-price">
                                
@if($goods['promote_price'] != '')

                                    {{ $goods['promote_price'] }}
                                
@else

                                    {{ $goods['shop_price'] }}
                                
@endif

                            </div>
                            <div class="p-name">
                                <a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
                                    @if($goods['country_icon'])
                                    <img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />
                                    @endif
                                    {{ $goods['goods_name'] }}
                                </a>
                            </div>
                            <div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                        </li>
                        
@endif

                        
@endforeach

                    </ul>
                </div>
            </div>