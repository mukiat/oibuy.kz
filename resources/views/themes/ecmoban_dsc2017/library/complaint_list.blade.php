<div class="dispute-content" ectype="dispute-content">
    <div class="user-order-list user-dispute-list">

@forelse($orders as $order)

        <dl class="item">
            <dt class="item-t">

@if($is_complaint == 1)
<div class="t-statu">{{ $lang['complaint_state'][$order['complaint_state']] }}
@if($order['has_talk'] == 1)
<span class="red">--{{ $lang['unread_information'] }}</span>
@endif
</div>
@endif

                <div class="t-info">
                    <span class="info-item">{{ $lang['order_number'] }}：{{ $order['order_sn'] }}</span>
                    <span class="info-item">{{ $order['order_time'] }}</span>
                    <span class="info-item">{{ $order['consignee'] }}</span>
                    <span class="info-item">
                        <a href="{{ $order['shop_url'] }}" class="user-shop-link">{{ $order['shop_name'] }}</a>

						<a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $order['ru_id'] }}"  class="iconfont icon-kefu user-shop-kefu"></a>

                    </span>
                </div>
				<div class="t-price">{{ $order['total_fee'] }}</div>
            </dt>
            <dd class="item-c">
                <div class="c-left">

@foreach($order['order_goods'] as $goods)

                    <div class="c-goods">
						<div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

                        <div class="c-info">
							<div class="o-info-lm">

@if($goods['extension_code'] == 'package_buy')

								{{ $goods['goods_name'] }}
								<span class="red">{{ $lang['remark_package'] }}</span>

@else

								<a href="{{ $goods['url'] }}" class="info-name" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a>

@if($goods['trade_id'])
<a href="user_order.php?act=trade&tradeId={{ $goods['trade_id'] }}&snapshot=true" class="trade_snapshot" target="_blank">[{{ $lang['trade_snapshot'] }}]</a>
@endif


@endif

							</div>
							<div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
						</div>
                    </div>

@endforeach

                </div>
                <div class="c-handle">

@if($is_complaint == 0)

                    <a href="user_order.php?act=complaint_apply&order_id={{ $order['order_id'] }}" class="sc-btn">{{ $lang['apply_transaction_disputes'] }}</a>

@else

					<a href="user_order.php?act=complaint_apply&complaint_id={{ $order['is_complaint'] }}" class="sc-btn">{{ $lang['View_details'] }}</a>

@if($order['complaint_state'] == 4)

					<a href="javascript:void();" ectype="del_compalint" data-id="{{ $order['is_complaint'] }}" class="sc-btn">{{ $lang['drop'] }}</a>

@endif


@endif

				</div>
            </dd>
        </dl>

@empty

		<div class="no_records">
			<i class="no_icon"></i>
			<div class="no_info">
				<h3>
					{{ $no_records }}
				</h3>
			</div>
		</div>

@endforelse

    </div>
    @include('frontend::library/pages')
</div>
