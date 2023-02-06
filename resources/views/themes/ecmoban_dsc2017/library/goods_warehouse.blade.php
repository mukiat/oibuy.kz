

@if($show_warehouse)

<div class="text-select" id="dis_warehouse" ectype="areaSelect">
    <div class="tit" id="dis_warehouse_name"><span>{{ $warehouse_name }}</span><i class="iconfont icon-down"></i></div>
	<div class="warehouse" id="warehouse_li" ectype="areaWarp">
    <ul>
        
@foreach($warehouse_list as $warehouse)

            
@if($warehouse['region_name'] != $warehouse_name)

        		<li onclick="warehouse({{ $warehouse['region_id'] }},{{ $goods_id }},'{{ $warehouse_type }}')">{{ $warehouse['region_name'] }}</li>
            
@endif

        
@endforeach

    </ul>
    </div>
</div>

@endif
