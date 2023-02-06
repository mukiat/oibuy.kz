
<div class="change-shop">
    <div class="change-shop-city-box">
        <div class="city-box-tit">{{ $lang['checked_city'] }}：<span class="change-shop-city">
            <em>
            
@foreach($area_position_list as $key => $list)

            
@if($key == 0)

                {{ $list['area_info'] }}
            
@endif

            
@endforeach

            </em>
            <i class="triangle-down"></i>
        </span>
        </div>
        <div class="city-box-info">
            <p class="city-hot">
                <span>{{ $lang['hot_city'] }}：</span>
                <a href="javascript:void(0);" data-level="1" data-id="25" data-name="{{ $lang['shanghai'] }}" class="city-item">{{ $lang['shanghai'] }}</a>
                <a href="javascript:void(0);" data-level="1" data-id="2" data-name="{{ $lang['beijing'] }}" class="city-item">{{ $lang['beijing'] }}</a>
                <a href="javascript:void(0);" data-level="1" data-id="32" data-name="{{ $lang['chongqing'] }}" class="city-item">{{ $lang['chongqing'] }}</a>
                <a href="javascript:void(0);" data-level="2" data-id="220" data-name="{{ $lang['nanjing'] }}" class="city-item">{{ $lang['nanjing'] }}</a>
            </p>
            <div class="city-tab">
                <a href="javascript:void(0);" data-level="1" data-id="{{ $province }}" data-name="{{ $province_name }}" class="city-item curr">{{ $province_name }}</a>
                <a href="javascript:void(0);" data-level="2" data-id="{{ $city_top }}" data-name="{{ $city_name }}" class="city-item">{{ $city_name }}</a>
                <a href="javascript:void(0);" data-level="3" data-id="{{ $district_top }}" data-name="{{ $district_name }}" class="city-item ">{{ $district_name }}</a>
            </div>
            <div class="city-box">
                
@foreach($provinces as $list)

                <a href="javascript:void(0);" data-level="1" data-id="{{ $list['region_id'] }}" data-name="{{ $list['region_name'] }}" class="city-item">{{ $list['region_name'] }}
@if($list['store_count'] == 1)
<i></i>
@endif
</a>
                
@endforeach

            </div>
        </div>
    </div>
    <div class="select-shop">
        <div class="select-shop-box">
        	
@foreach($area_position_list as $key => $list)

                
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
                        <input type="hidden" name="store_id" value='{{ $list['id'] }}'/>
                    </div>
                
@endif

			
@endforeach

        </div>
    </div>
</div>
    