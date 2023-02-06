<div class="mc cou-data">
	<div class="quan-list">

@foreach($cou_data as $vo)

		<div class="quan-item quan-d-item quan-item-acoupon
@if($vo['cou_surplus'] == 0)
 quan-gray-item
@endif
">
			<div class="q-type">
					<div class="q-price">
						<em>{{ config('shop.currency_format', '¥') }}</em>
						<strong class="num">{{ $vo['cou_money'] }}</strong>
						<div class="txt" style="float: none;"><div class="typ-txt">{{ $vo['cou_type_name'] }}</div></div>
						<div class="txt">
							<div class="limit"><span class="ftx-06">{{ $lang['full'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}</span></div>
						</div>
					</div>
				<div class="q-range">
					<div class="range-item"><p title="
@if($vo['cou_goods'])
<p>{{ $lang['permissions_buy'] }}</p>
@else
<p>{{ $lang['category'] }}</p>
@endif
">
						{{ $vo['cou_title'] }}
					</p></div>
					<div class="range-item">{{ $vo['store_name'] }}</div>
					<div class="range-item">{{ $vo['cou_start_time_format'] }}-{{ $vo['cou_end_time_format'] }}</div>
				</div>
			</div>

@if(!empty($user_id) && $vo['cou_is_receive'])


@if($vo['is_use']==0)


@if($vo['cou_surplus'] == 0)

						<div class="q-opbtns"><a href="javascript:;" target="_blank" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

						<div class="q-opbtns"><a href="search.php?cou_id={{ $vo['cou_id'] }}" target="_blank"><b class="semi-circle"></b>{{ $lang['Immediate_use'] }}</a></div>
						<div class="q-state"><div class="btn-state btn-geted">{{ $lang['receive_hove'] }}</div></div>

@endif



@else


@if($vo['cou_surplus'] == 0)

					<div class="q-opbtns"><a href="javascript:;" target="_blank" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

					<div class="q-opbtns"><a href="javascript:;" target="_blank" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif


@else


@if($vo['cou_surplus'] == 0)

				<div class="q-opbtns"><a href="javascript:;" target="_blank" class="" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['Take_up'] }}</a></div>

@else

				<div class="q-opbtns"><a href="javascript:;" target="_blank" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><b class="semi-circle"></b>{{ $lang['receive_now'] }}</a></div>

@endif


@endif

		</div>

@endforeach

	</div>
</div>
