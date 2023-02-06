<div class="grid-sizer"></div>
<div class="gutter-sizer"></div>

@if($store_shop_list)


@foreach($store_shop_list as $shop)

<div class="street-list-item">
    <a href="{{ $shop['shop_url'] }}" target="_blank" class="cover"><img src="{{ $shop['street_thumb'] }}" alt="{{ $shop['shopName'] }}"></a>
    <div class="info">
        <a href="javascript:void(0);" ectype="collect_store" data-value='{"userid":{{ $user_id }},"storeid":{{ $shop['ru_id'] }}}' data-url="store_street.php" class="s-follow
@if($shop['collect_store'] != 0)
 selected
@endif
"><i class="iconfont
@if($shop['collect_store'] != 0)
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i></a>
        <div class="s-logo"><a href="{{ $shop['shop_url'] }}" target="_blank"><img src="{{ $shop['brand_thumb'] }}" alt="{{ $shop['shopName'] }}"></a></div>
        <div class="s-name"><a href="{{ $shop['shop_url'] }}" target="_blank" class="name">{{ $shop['shopName'] }}</a></div>
        
@if($shop['self_run'])

        <div class="seller-sr">{{ $lang['platform_self'] }}</div>
        
@endif

        <p>
@if($shop['shoprz_brand_name'])
{{ $lang['main_brand'] }}： {{ $shop['shoprz_brand_name'] }}
@else
&nbsp;
@endif
</p>
        <p>
@if($shop['grade_img'])
{{ $lang['seller_Grade'] }}： <!--<span class="shop-level-icon level-1"></span>--><img src="{{ $shop['grade_img'] }}" title="{{ $shop['grade_name'] }}" width="20"/>
@else
&nbsp;
@endif
</p>
    </div>
</div>

@endforeach


@endif
