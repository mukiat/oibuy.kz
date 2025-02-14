
@if($history_info)

<div class="tbar-panel-main" ectype="tbpl-main">
    <div class="tbar-panel-content" data-height="48" ectype="tbpl-content">
        <div class="history-wrap">
            <ul>

@foreach($history_info as $goods)

                <li>
                    <a href="{{ $goods['url'] }}" class="img-wrap" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="100" height="100" /></a>
                    <a href="{{ $goods['url'] }}" class="add-cart-button" target="_blank">{{ $lang['btn_add_to_cart'] }}</a>
                    <a href="{{ $goods['url'] }}" class="price" target="_blank">
@if($goods['is_promote'])
{{ $goods['promote_price'] }}
@else
{{ $goods['shop_price'] }}
@endif
</a>
                </li>

@endforeach

            </ul>
            <a href="{{ url('/') }}/history_list.php" class="follow-bottom-more">{{ $lang['see_more'] }}>></a>
        </div>
    </div>
</div>

@endif
