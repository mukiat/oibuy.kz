
@if($snatch_list['snatch_list'])


@foreach($snatch_list['snatch_list'] as $snatch)


@if($snatch)

<dl class="item">
	<dt class="item-t">
	<div class="t-statu">{{ $snatch['status'] }}</div>
	<div class="t-info">
		<span class="info-item">{{ $lang['act_id'] }}：{{ $snatch['act_id'] }}</span>
		<span class="info-item">{{ $lang['bid_time'] }}：{{ $snatch['bid_time'] }}</span>
	</div>
	<div class="t-price">{{ $lang['bid_price'] }}：{{ $snatch['bid_price'] }}</div>
	</dt>
	<dd class="item-c">
		<div class="c-left">
			<div class="c-goods">
				<div class="c-img"><a href="snatch.php?id={{ $snatch['act_id'] }}" target="_blank" title="{{ $snatch['goods_name'] }}"><img src="{{ $snatch['goods_thumb'] }}" alt=""></a></div>
				<div class="c-info">
					<div class="info-name">{{ $lang['goods_name'] }}：<a href="snatch.php?id={{ $snatch['act_id'] }}" target="_blank" title="{{ $snatch['goods_name'] }}">{{ $snatch['goods_name'] }}</a></div>
				</div>
			</div>
		</div>
		<div class="c-handle">
			<a href="snatch.php?id={{ $snatch['act_id'] }}" target="_blank" class="sc-btn">{{ $lang['snatch_desc'] }}</a>
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



@if($snatch_list['snatch_list'])

<div class="pages pages_warp">{!! $snatch_list['pager'] !!}</div>

@endif
