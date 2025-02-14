<div class="tbar-panel-main" ectype="tbpl-main">
    <div class="tbar-panel-content" data-height="48" ectype="tbpl-content">
    	<div class="s-list">
        	<div class="s-asset">
                <span class="s-first-child">{{ $lang['balance'] }}<em class="s-balance-num">{{ config('shop.currency_format', '¥') }}{{ $user_info['user_money'] }}</em></span>
                <span>{{ $lang['integral'] }}<em class="s-beans-num">{{ $user_info['integral'] }}</em></span>
                <span class="s-last-child">{{ $lang['bonus'] }}<em class="s-coupon-num">{{ $user_info['bouns_num'] }}</em></span>
            </div>
            <ul class="s-li-con">

@foreach($user_info['bouns_list'] as $bouns)

            	<li class="s-coupon s-current">
                	<div class="s-quota">
@if($bouns['min_goods_amount'])
<span class="s-desc">{{ $lang['man'] }}<em>{{ $bouns['min_goods_amount'] }}</em>{{ $lang['Use'] }}</span>
@endif
<span class="s-num">{{ config('shop.currency_format', '¥') }}<em>{{ $bouns['bouns_amount'] }}</em></span></div>
                    <div class="s-info"><p class="s-text">{{ $lang['Credit_Card_Number'] }}：<em>{{ $bouns['bonus_sn'] }}</em></p><p class="s-time">{{ $lang['valid_time'] }}：<em>{{ $bouns['use_startdate'] }} - {{ $bouns['use_enddate'] }}</em></p></div>
                </li>

@endforeach

            </ul>
            <a href="{{ url('/') }}/user.php?act=account_log" class="follow-bottom-more">{{ $lang['see_more'] }}>></a>
        </div>
    </div>
</div>
