

@if($guess_store)

<div class="store-channel mb60">
    <div class="ftit"><h3>{{ $lang['recommended_store'] }}</h3></div>
    <div class="rec-store-list">
        
@foreach($guess_store as $store)

            <div class="rec-store-item opacity_img">
            <a href="{{ $store['store_url'] }}">
                <div class="p-img"><img src="{{ $store['street_thumb'] }}"></div>
                <div class="info">
                    <div class="s-logo"><div class="img"><img src="{{ $store['brand_thumb'] }}" width="113" height="55"></div></div>
                    <div class="s-title">
                            <div class="tit">{{ $store['shop_name'] }}</div>
                        <div class="ui-tit">{{ $lang['recommended_store_notic'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        
@endforeach

    </div>
</div>

@endif
