

@if($bonus)

<div class="items">

@foreach($bonus['available_list'] as $item)

 <div class="item
@if($loop->iteration % 3 == 0)
 item-last
@endif
">
    <div class="b-price">{{ $item['type_money'] }}</div>
    <div class="b-i-bot">
        <p>卡号：{{ $item['bonus_sn'] ?? N/A }} - 订单限额：{{ $item['min_goods_amount'] }}</p>
        <p>{{ $item['use_startdate'] }} ~ {{ $item['use_enddate'] }}</p>
    </div>
    <i class="i-soon">
@if($item['usebonus_type'])
{{ $lang['general_audience'] }}
@else
{{ $item['shop_name'] }}
@endif
</i>
</div>

@endforeach

</div>

@else
                                
<div class="no_records">
    <i class="no_icon_two"></i>
    <div class="no_info">
        <h3>{{ $lang['no_bonus_end'] }}</h3>
    </div>
</div>

@endif


<div class="pager_tech pages26">
    
@if($bonus['record_count'] > 1)
<div class="pages"><div class="pages-it">{{ $bonus['paper'] }}</div></div>
@endif

</div>