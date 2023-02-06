
@if($temp == 'replaceStore')

    
@forelse($area_position_list as $key => $list)

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
    
@empty
  
    <div class="shop-info">
        <h3>
            <b>{{ $lang['change_choice_desc'] }}</b>
            <a href="javascript:void(0);" class="select" ectype="storeSelect"><i class="icon icon-refresh"></i>{{ $lang['change_choice'] }}</a>
        </h3>
    </div>
    
@endforelse


@else

    <div class="select-shop-box">
        
@forelse($area_position_list as $key => $list)

        
@if($list['stores_name'])

            <div class="shop-info shop-info-item 
@if($key == 0)
active
@endif
">
                <h3><b>{{ $list['stores_name'] }}</b><span class="xianhuo">
@if($list['goods_number'] > 10)
{{ $lang['sufficient'] }}
@else
{{ $lang['only_leave'] }}{{ $list['goods_number'] }}{{ $lang['jian'] }}
@endif
</span></h3>
                <p>{{ $lang['address'] }}：{{ $list['stores_address'] }}</p>
                <p>{{ $lang['sales_hotline'] }}：{{ $list['stores_tel'] }}</p>
                <p>{{ $lang['working_time'] }}：{{ $list['stores_opening_hours'] }}</p>
                <input type="hidden" name="store_id" value="{{ $list['id'] }}"/>
            </div>
        
@endif

        
@empty

        <div class="notic_store">{{ $lang['notic_store'] }}</div>
        
@endforelse

    </div>

@endif
