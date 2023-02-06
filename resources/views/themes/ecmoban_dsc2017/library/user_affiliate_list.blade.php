
@if($sales_reward)

	<div class="affiliate-list-warp clearfix">
		<table class="user-table user-table-baitiao">
		<colgroup>
			<col width="214">
			<col width="214">
			<col width="214">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th class="tc">{{ $lang['order_number'] }}</th>
				<th>{{ $lang['order_addtime'] }}</th>
				<th>{{ $lang['obtain_commission'] }}</th>
				<th>{{ $lang['affiliate_status'] }}</th>
				<th>{{ $lang['operation'] }}</th>
			</tr>
		</thead>
		<tbody>

@foreach($sales_reward as $val)

			<tr>
				<td class="tc">{{ $val['order_sn'] }}</td>
				<td class="tc">{{ $val['add_time'] }}</td>
				<td class="tc">{{ $val['money'] }}</td>
				<td class="tc">{{ $lang['divided_into_state'][$val['is_separate']] }}</td>
				<td class="tc">
					<a href="user.php?act=sales_reward_detail&log_id={{ $val['log_id'] }}" class="sc-btn">{{ $lang['to_view'] }}</a>
				</td>
			</tr>

@endforeach

		</tbody>
	</table>
	</div>

@else

<div class="no_records">
	<i class="no_icon_two"></i>
	<div class="no_info no_info_line">
		<h3>{{ $lang['not_data'] }}</h3>
	</div>
</div>

@endif



@if($sales_reward && $page_count > 1)

<div class="pages pages_warp">{!! $pager !!}</div>

@endif


