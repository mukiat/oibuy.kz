
@if($auction_list['auction_list'])


@foreach($auction_list['auction_list'] as $auction)


@if($auction)

<dl class="item">
	<dt class="item-t">
	<div class="t-statu">{{ $auction['status'] }}</div>
	<div class="t-info">
		<span class="info-item">{{ $lang['act_id'] }}：{{ $auction['act_id'] }}</span>
		<span class="info-item">{{ $lang['bid_time'] }}：{{ $auction['bid_time'] }}</span>
	</div>
	<div class="t-price">{{ $lang['bid_price'] }}：{{ $auction['bid_price'] }}</div>
	</dt>
	<dd class="item-c">
		<div class="c-left">
			<div class="c-goods">
				<div class="c-img"><a href="auction.php?act=view&id={{ $auction['act_id'] }}" target="_blank" title="{{ $auction['goods_name'] }}"><img src="{{ $auction['goods_thumb'] }}" alt=""></a></div>
				<div class="c-info">
					<div class="info-name">{{ $lang['goods_name'] }}：<a href="auction.php?act=view&id={{ $auction['act_id'] }}" target="_blank" title="{{ $auction['goods_name'] }}">{{ $auction['goods_name'] }}</a></div>
				</div>
			</div>
		</div>
		<div class="c-handle">
			<a href="auction.php?act=view&id={{ $auction['act_id'] }}" target="_blank" class="sc-btn">{{ $lang['auction_desc'] }}</a>
		</div>
	</dd>
</dl>

@endif


@endforeach


@else

<div class="no_records">
	<i class="no_icon_two"></i>
	<div class="no_info no_info_line">
		<h3>{{ $lang['not_data'] }}</h3>
	</div>
</div>

@endif



@if($auction_list['auction_list'])

<div class="pages pages_warp">{!! $auction_list['pager'] !!}</div>

@endif
