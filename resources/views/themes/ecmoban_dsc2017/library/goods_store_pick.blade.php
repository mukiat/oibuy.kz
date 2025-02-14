
<div class="items">
    <div class="item">
        <div class="label">{{ $lang['select_store_info'] }}</div>
        <div class="value replaceStore">
        	
@forelse($area_position_list as $key => $list)

            
@if($key == 0)

            <div class="shop-info">
                <h3>
                    <b>{{ $list['stores_name'] }}</b>
                    <span class="xianhuo">
@if($list['goods_number'] > 10)
{{ $lang['sufficient'] }}
@else
{{ $lang['only_leave'] }}{{ $list['goods_number'] }}{{ $lang['jian'] }}
@endif
</span>
                    <a href="javascript:void(0);" class="select" ectype="storeSelect"><i class="icon icon-refresh"></i>{{ $lang['change_choice'] }}</a>
                </h3>
                <p>{{ $lang['address'] }}：{{ $list['stores_address'] }}</p>
                <p>{{ $lang['sales_hotline'] }}：{{ $list['stores_tel'] }}</p>
                <p>{{ $lang['working_time'] }}：{{ $list['stores_opening_hours'] }}</p>
                <input type="hidden" name="store_id" value="{{ $list['id'] }}"/>
            </div>
                
            
@endif

            
@empty
  
            <div class="shop-info">
                <h3>
                    <b>{{ $lang['change_choice_desc'] }}</b>
                    <a href="javascript:void(0);" class="select" ectype="storeSelect"><i class="icon icon-refresh"></i>{{ $lang['change_choice'] }}</a>
                </h3>
            </div>
            
@endforelse

        </div>
    </div>
    <div class="item">
        <div class="label">{{ $lang['time_shop'] }}</div>
        <div class="value">
            <div class="text_time">
                <input id="end_time" name="end_time" type="text" class="text" readonly value="{{ $take_time }}">
                <em>{{ $lang['take_time_desc'] }}</em>
            </div>
        </div>
    </div>
    <div class="item">
        <div class="label">{{ $lang['phone_con'] }}</div>
        <div class="value">
            <input type="text" class="text" name='store_mobile' value="{{ $mobile_phone }}" placeholder="{{ $lang['store_take_mobile'] }}">
            <em>{{ $lang['store_take_mobile_one'] }}</em>
        </div>
    </div>
</div>
<script type="text/javascript">

//日期选择插件调用start sunle
var opts1 = {
	'targetId':'end_time',
	'triggerId':['end_time'],
	'alignId':'end_time',
	'zIndex':999999,
	'position':'fixed',
	'format':'-',
        'min':'{{ $now_time }}' //最小时间
}

xvDate(opts1);
//日期选择插件调用end sunle
</script>