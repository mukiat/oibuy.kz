
@if($has_child == 1)

<div class="cate-layer-left">
    
@if($category_topic)

    <div class="cate_channel">
        
@foreach($category_topic as $topic)

        <a href="{{ $topic['topic_link'] }}" target="_blank">{{ $topic['topic_name'] }}</a>
        
@endforeach

    </div>
    
@endif

    <div class="cate_detail">
        
@foreach($child_tree as $child)

        <dl class="dl_fore{{ $loop->iteration }}">
            <dt><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a></dt>
            <dd>
                
@foreach($child['child_tree'] as $cat)

                <a href="{{ $cat['url'] }}" target="_blank">{{ $cat['name'] }}</a>
                
@endforeach

            </dd>
        </dl>
        
@endforeach


        <div class="item-brands">
            <ul>
                
@foreach($brands as $brand)

                <li class="brand-fore{{ $loop->iteration }}">
                <a href="{{ $brand['url'] }}" target="_blank" title="{{ $brand['brand_name'] }}"><img src="{{ $brand['brand_logo'] }}" width="91" height="40" /></a>
                </li>
                
@endforeach

            </ul>
        </div>
        <div class="item-promotions">
            
@foreach($ad_position as $pkey => $posti)

            <a href="{{ $posti['ad_link'] }}" target="_blank"><img width="{{ $posti['ad_width'] }}" height="{{ $posti['ad_height'] }}" src="{{ $posti['ad_code'] }}" /></a>
            
@endforeach

        </div>
    </div>
</div>
<div class="cate-layer-rihgt">
    <div class="cate-brand">
        
@foreach($brands_ad['brands'] as $brand)

            <div class="img"><a href="{{ $brand['url'] }}" target="_blank" title="{{ $brand['brand_name'] }}"><img src="{{ $brand['brand_logo'] }}" /></a></div>
        
@endforeach

    </div>
    
@if($brands_ad['ad_position'])

    <div class="cate-promotion">
        
@foreach($brands_ad['ad_position'] as $pkey => $posti)

        <a href="{{ $posti['ad_link'] }}" target="_blank"><img width="{{ $posti['ad_width'] }}" height="{{ $posti['ad_height'] }}" src="{{ $posti['ad_code'] }}" /></a>
        
@endforeach

    </div>
    
@endif

</div>

@else

<div class="cate_two_detail">
    <ul>
    
@foreach($child_tree as $child)

    <li class="li_fore li_fore{{ $loop->iteration }}"><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}<i class="icon chicon-right"></i></a></li>
    
@endforeach

    </ul>
</div>

@if($category_topic)

<div class="cate_two_channel">
    <ul>
    
@foreach($category_topic as $topic)

    <li><a href="{{ $topic['topic_link'] }}" target="_blank">{{ $topic['topic_name'] }}</a></li>
    
@endforeach

    </ul>
</div>

@endif


@endif
