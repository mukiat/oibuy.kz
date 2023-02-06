

@if($recommend_brands)


@if($temp == 'backup_festival_1')

	<ul>
		
@foreach($recommend_brands as $brand)

		<li>
			<div class="brand-img"><a href="{{ $brand['url'] }}" target="_blank"><img src="{{ $brand['brand_logo'] }}"></a></div>
			<div class="brand-mash">
				<div data-bid="{{ $brand['brand_id'] }}" ectype="coll_brand"><i class="iconfont 
@if($brand['is_collect'])
icon-zan-alts
@else
icon-zan-alt
@endif
"></i></div>
				<div class="coupon"><a href="{{ $brand['url'] }}" target="_blank"><span>{{ $lang['follow_number'] }}<em id="collect_count_{{ $brand['brand_id'] }}">{{ $brand['collect_count'] ?? 0 }}</em></span><div class="click-enter">{{ $lang['click_getinfo'] }}</div></div></a></div>
			</div>
		</li>
		
@endforeach

	</ul>
	<input type="hidden" name="user_id" value="{{ $user_id }}">
	<a href="javascript:void(0);" ectype="changeBrand" class="refresh-btn"><i class="iconfont icon-rotate-alt"></i><span>{{ $lang['change_a_lot'] }}</span></a>

@else

<div class="brand-list" id="recommend_brands">
	<ul>
		
@foreach($recommend_brands as $brand)

		<li>
			<div class="brand-img"><a href="{{ $brand['url'] }}" target="_blank"><img src="{{ $brand['brand_logo'] }}"></a></div>
			<div class="brand-mash">
				<div data-bid="{{ $brand['brand_id'] }}" ectype="coll_brand"><i class="iconfont 
@if($brand['is_collect'])
icon-zan-alts
@else
icon-zan-alt
@endif
"></i></div>
				<div class="coupon"><a href="{{ $brand['url'] }}" target="_blank">{{ $lang['follow_number'] }}<br><div id="collect_count_{{ $brand['brand_id'] }}">{{ $brand['collect_count'] ?? 0 }}</div></a></div>
			</div>
		</li>
		
@endforeach

	</ul>
	<input type="hidden" name="user_id" value="{{ $user_id }}">
	<a href="javascript:void(0);" ectype="changeBrand" class="refresh-btn"><i class="iconfont icon-rotate-alt"></i><span>{{ $lang['change_a_lot'] }}</span></a>
</div>

@endif


@endif
