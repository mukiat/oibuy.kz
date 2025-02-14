
@if($collection_brands)

<div class="collection-list-warp clearfix">
	<ul class="brand-ul">

@foreach($collection_brands as $collection_brands)

		<li>
			<div class="c-brand-left">
				<div class="b-logo"><a href="brandn.php?act=cat&id={{ $collection_brands['brand_id'] }}" target="_blank"><img src="{{ $collection_brands['brand_logo'] }}" style="max-width: 200px; max-height: 88px"></a></div>
				<div class="b-name"><a href="brandn.php?act=cat&id={{ $collection_brands['brand_id'] }}" target="_blank">{{ $collection_brands['brand_name'] }}</a></div>
				<div class="b-btn">
					<a href="brandn.php?act=cat&id={{ $collection_brands['brand_id'] }}" target="_blank" class="sc-btn">{{ $lang['Enter_brand_page'] }}</a>
				</div>
				<div class="p-oper"><a href="javascript:void(0)" class="btn btn-10" data-dialog="goods_collect_dialog" data-url="brandn.php?act=cancel&amp;id={{ $collection_brands['brand_id'] }}&amp;mbid={{ $collection_brands['ru_id'] }}&amp;user_id={{ $user_id }}&amp;type=1" data-divid="delete_brand_collect" data-goodsid="0">{{ $lang['Cancel_attention'] }}</a></div>
			</div>
			<div class="c-brand-right">

@if($collection_brands['brand_goods'])

                    <ul>

@foreach($collection_brands['brand_goods'] as $brand_goods)


@if($loop->iteration <5)

                        <li>
                            <div class="p-img"><a href="{{ $brand_goods['url'] }}" target="_blank"><img src="{{ $brand_goods['goods_thumb'] }}"></a></div>
                            <div class="p-price">{{ $brand_goods['shop_price'] }}</div>
                        </li>

@endif


@endforeach

                    </ul>

@else

                	<div class="no_records no_records_min">
                        <i class="no_icon no_icon_two"></i>
                        <div class="no_info"><h3>{{ $lang['no_brand_goods'] }}</h3></div>
                    </div>

@endif

			</div>
		</li>

@endforeach

	</ul>
</div>

@else

<div class="no_records">
	<i class="no_icon"></i>
    <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
</div>

@endif

