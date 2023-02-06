

@if($ad_child)

    
@if($floor_style_tpl == 1)

        
@foreach($ad_child as $ad)

        <div class="f-r-m-item
@if($loop->iteration == 1)
 f-r-m-i-double
@endif
">
            <a href="{{ $ad['ad_link'] }}" target="_blank">
                <div class="title">
                    <h3>{{ $ad['b_title'] }}</h3>
                    <span>{{ $ad['s_title'] }}</span>
                </div>
                <img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}">
            </a>
        </div>
        
@endforeach

    
@elseif ($floor_style_tpl == 2)

        
@foreach($ad_child as $ad)

        <div class="f-r-m-item
@if($loop->iteration == 2)
 f-r-m-i-double
@endif
">
            <a href="{{ $ad['ad_link'] }}" target="_blank">
                <div class="title">
                    <h3>{{ $ad['b_title'] }}</h3>
                    <span>{{ $ad['s_title'] }}</span>
                </div>
                <img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}">
            </a>
        </div>
        
@endforeach

    
@elseif ($floor_style_tpl == 3)

        
@foreach($ad_child as $ad)

        <div class="f-r-m-item
@if($loop->iteration == 4)
 f-r-m-i-double
@endif
">
            <a href="{{ $ad['ad_link'] }}" target="_blank">
                <div class="title">
                    <h3>{{ $ad['b_title'] }}</h3>
                    <span>{{ $ad['s_title'] }}</span>
                </div>
                <img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}">
            </a>
        </div>
        
@endforeach

    
@else

        
@foreach($ad_child as $ad)

        <div class="f-r-m-item
@if($loop->iteration == 5)
 f-r-m-i-double
@endif
">
            <a href="{{ $ad['ad_link'] }}" target="_blank">
                <div class="title">
                    <h3>{{ $ad['b_title'] }}{{ $goods_cat['floor_style_tpl'] }}</h3>
                    <span>{{ $ad['s_title'] }}</span>
                </div>
                <img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}">
            </a>
        </div>
        
@endforeach

    
@endif
	

@endif
