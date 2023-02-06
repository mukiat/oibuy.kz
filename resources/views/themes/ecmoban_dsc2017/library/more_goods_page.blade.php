
@if($type == 'group_buy')


@foreach($gb_list as $group_buy)

<li class="mod-shadow-card">
    <a href="{{ $group_buy['url'] }}" target="_blank" class="img"><img src="{{ $group_buy['goods_thumb'] }}" alt="{{ $group_buy['goods_name'] }}" title="{{ $group_buy['goods_name'] }}"></a>
    <div class="clearfix">
        <div class="price">{{ config('shop.currency_format', '¥') }}{{ $group_buy['price_ladder']['0']['price'] }}</div>
        <div class="man">{{ $group_buy['cur_amount'] }}{{ $lang['people_participate'] }}</div>
    </div>
    <a href="{{ $group_buy['url'] }}" target="_blank" class="name" title="{{ $group_buy['goods_name'] }}">{{ $group_buy['goods_name'] }}</a>
    <div class="lefttime" data-time='{{ $group_buy['formated_end_date'] }}'>
        <i class="iconfont icon-time"></i>
        <span>{{ $lang['residue_time'] }}</span>
        <span class="days"></span>
        <em>:</em>
        <span class="hours"></span>
        <em>:</em>
        <span class="minutes"></span>
        <em>:</em>
        <span class="seconds"></span>
    </div>

@if($group_buy['is_end'] == 1)

    <a href="{{ $group_buy['url'] }}" class="gb-btn bid_end">{{ $lang['Group_purchase_end'] }}</a>

@else

    <a href="{{ $group_buy['url'] }}" class="gb-btn">{{ $lang['Group_purchase_now'] }}</a>

@endif

</li>

@endforeach


@endif


@if($type == 'snatch')


@foreach($snatch_list as $list)

<li class="mod-shadow-card">
    <a href="{{ $list['url'] }}" class="img"><img src="{{ $list['goods_thumb'] }}" alt="{{ $list['snatch_name'] }}"></a>
    <a href="{{ $list['url'] }}" class="name">{{ $list['snatch_name'] }}</a>
    <div class="info">
        <p><em>{{ $lang['current_price'] }}：</em><span class="price">{{ $list['formated_shop_price'] }}</span></p>
        <p class="lefttime" data-time="{{ $list['snatch']['end_time_date'] }}">
            <span>{{ $lang['residual_time'] }}：</span>
            <span class="days">00</span>
            <em>:</em>
            <span class="hours">15</span>
            <em>:</em>
            <span class="minutes">40</span>
            <em>:</em>
            <span class="seconds">10</span>
        </p>
        <p><em>{{ $lang['bid_ci_number'] }}：</em><span>{{ $list['price_list_count'] }}</span></p>
    </div>

@if($list['current_time'] < $list['end_time'] && $list['current_time'] > $list['start_time'] )

    <a href="{{ $list['url'] }}" target="_blank" class="sn-btn"><em></em>{{ $lang['me_bid'] }}<s></s></a>

@elseif ($list['current_time'] >= $list['end_time'] )

    <a href="{{ $list['url'] }}" target="_blank" class="sn-btn bid_end"><em></em>{{ $lang['au_end'] }}<s></s></a>

@else

    <a href="{{ $list['url'] }}" target="_blank" class="sn-btn bid_wait"><em></em>{{ $lang['Wait_au'] }}<s></s></a>

@endif

</li>

@endforeach


@endif


@if($type == 'auction')


@foreach($auction_list as $auction)

<li>
    <a href="{{ $auction['url'] }}" class="img" target="_blank"><img src="{{ $auction['goods_thumb'] }}" alt="{{ $auction['act_name'] }}" title="{{ $auction['act_name'] }}"></a>
    <div class="info">
        <a href="{{ $auction['url'] }}" class="name" target="_blank" title="{{ $auction['goods_name'] }}">{{ $auction['act_name'] }}</a>
        <div class="desc">
            <p>
                <span>{{ $lang['rz_shopName'] }}：</span>
                {{ $auction['rz_shop_name'] }}
            </p>
            <p>
                <span class="fr red">{{ $auction['count'] }}{{ $lang['au_number'] }}</span>
                <span>{{ $lang['residual_time'] }}：</span>
                <span class="
@if($auction['status_no'] == 1)
lefttime
@endif
" data-time="{{ $auction['end_time'] }}">
                    <span class="days">00</span>
                    <em>:</em>
                    <span class="hours">00</span>
                    <em>:</em>
                    <span class="minutes">00</span>
                    <em>:</em>
                    <span class="seconds">00</span>
                </span>
            </p>
            <div class="timebar"><i style="width: 80%;"></i></div>
        </div>
        <div class="btn-wp">
            <div class="price">{{ $auction['formated_start_price'] }}</div>

@if($auction['status_no'] == 1)

            <a href="{{ $auction['url'] }}" target="_blank" class="au-btn"><em></em>{{ $lang['me_bid'] }}<s></s></a>

@elseif ($auction['status_no'] == 2)


@if($auction['is_winner'])

                    <a href="{{ $auction['url'] }}" target="_blank" class="au-btn bid_wait"><em></em>{{ $lang['button_buy'] }}<s></s></a>

@else

                    <a href="{{ $auction['url'] }}" target="_blank" class="au-btn bid_end"><em></em>{{ $lang['au_end'] }}<s></s></a>

@endif


@endif

        </div>
   </div>
</li>

@endforeach


@endif


@if($type == 'exchange')


@foreach($goods_list as $goods)


@if($goods['goods_id'])

        <li class="mod-shadow-card">
            <a href="{{ $goods['url'] }}" target="_blank" class="img"><img src="{{ $goods['goods_thumb'] }}" alt="{{ $goods['name'] }}"></a>
            <div class="clearfix">
                <div class="score">{{ $lang['integral'] }} {{ $goods['exchange_integral'] }}</div>
                <div class="market">{{ $goods['market_price'] }}</div>
            </div>
            <a href="{{ $goods['url'] }}" target="_blank" class="name" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a>
            <div class="already">
                <i class="iconfont icon-package"></i>
                {{ $goods['sales_volume'] ?? 0 }}{{ $lang['People_exchange'] }}
            </div>
            <a href="{{ $goods['url'] }}" class="ex-btn" target="_blank">{{ $lang['Redeem_now'] }}</a>
        </li>

@endif


@endforeach


@endif
