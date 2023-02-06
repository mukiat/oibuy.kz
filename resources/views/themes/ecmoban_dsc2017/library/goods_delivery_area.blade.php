@if($province_row && $city_row)
<div class="tit">
    {{--四个直辖市--}}
    @if ($province_row['region_name'] == $city_row['region_name'] || in_array($province_row['region_id'], ['110000', '120000', '310000', '500000']))
        <span>{{ $city_row['region_name'] }} {{ $district_row['region_name'] }} {{ $street_row['region_name'] ?? '' }}</span>
    @else
        <span>{{ $province_row['region_name'] }} {{ $city_row['region_name'] }} {{ $district_row['region_name'] }} {{ $street_row['region_name'] ?? '' }}</span>
    @endif

    <i class="iconfont icon-down"></i>
</div>
@endif
<div class="area-warp" id="area_list" ectype="areaWarp">
	
@if($consignee_list)

	<div class="stock-add-used">
    	<div class="stock-top"><strong>{{ $lang['Common_address'] }}</strong></div>
        <div class="stock-con">
        	<ul class="area-list-used">
            	
@foreach($consignee_list as $consignee)

            	<li @if($loop->last)class="last"@endif ><a href="javascript:;" onClick="get_region_change('{{ $goods_id }}', '{{ $consignee['province_id'] }}', '{{ $consignee['city_id'] }}', '{{ $consignee['district_id'] }}', '{{ $consignee['street_id'] ?? 0 }}');" title="{{ $consignee['address'] }}" @if($consignee['address_id'] == $address_id) class="selected" @endif>{{ $consignee['consignee'] }}&nbsp;&nbsp;{{ $consignee['region_simple'] ?? '' }}
@if($consignee['address_id'] == $address_id)
&nbsp;&nbsp;({{ $lang['default'] }})
@endif
</a></li>
                
@endforeach

            </ul>
        </div>
    </div>
    
@endif

    <ul class="tab" id="select_area">
        <li onclick="region.selectArea(this, 1);" value="{{ $province_row['region_id'] }}" id="province_li">{{ $province_row['region_name'] }}<i class="sc-icon-right"></i></li>
        <li onclick="region.selectArea(this, 2);" value="{{ $city_row['region_id'] }}" id="city_li">{{ $city_row['region_name'] }}<i class="sc-icon-right"></i></li>
        <li  onclick="region.selectArea(this, 3);" value="{{ $district_row['region_id'] }}" id="district_type">{{ $district_row['region_name'] }}<i class="sc-icon-right"></i></li>
        @if(!empty($street_row['region_id']))
        <li class="curr" onclick="region.selectArea(this, 4);" value="{{ $street_row['region_id'] }}" id="street_type">{{ $street_row['region_name'] }}<i class="sc-icon-right"></i></li>
        @endif
    </ul>
    <div class="tab-content" id="house_list" style="display:none;">
        <ul id="province_list">
@foreach($province_list as $province)
                <li>
                    <a v="{{ $province['region_id'] }}" title="{{ $province['region_name'] }}" 
@if($province['choosable'])
    onclick="region.getRegion({{ $province['region_id'] }}, 2, city_list, this,{{ $user_id }},{{ $merchant_id ?? 0 }});"
    @if($province_row['region_id'] == $province['region_id']) class="selected" @endif
@else
    class="choosable"
@endif
 href="javascript:void(0);">{{ $province['region_name'] }}</a>
                </li>
@endforeach
        </ul>   
    </div>
    <div style="
@if($district_row['region_name'] == '')
display: block;
@else
display: none;
@endif
" class="tab-content" id="city_list_id">
        <ul id="city_list">
@foreach($city_list as $city)
                <li>
                    <a v="{{ $city['region_id'] }}" title="{{ $city['region_name'] }}" 
@if($city['choosable'])
onclick="region.getRegion({{ $city['region_id'] }}, 3, district_list, '{{ $city['region_name'] }}',{{ $user_id }},{{ $merchant_id ?? 0 }});"
                       @if($city_row['region_id'] == $city['region_id']) class="selected" @endif
@else
    class="choosable"
@endif
 href="javascript:void(0);">{{ $city['region_name'] }}</a>  
                </li>
@endforeach
        </ul>
    </div>
    <div class="tab-content" id="district_list_id" style="
    @if($street_row['region_id'] >= '0')
display: none;
@else
display: block;
@endif
">
        <ul id="district_list">              

@foreach($district_list as $district)
                <li>                     
                    <a v="{{ $district['region_id'] }}" title="{{ $district['region_name'] }}"
@if($district['choosable'])
                       onclick="region.getRegion({{ $district['region_id'] }}, 4, street_list, '{{ $district['region_name'] }}',{{ $user_id }},{{ $merchant_id ?? 0 }});"
                       @if($district_row['region_id'] == $district['region_id']) class="selected" @endif
@else
    class="choosable"
@endif
 href="javascript:void(0);" id="district_{{ $district['region_id'] }}">{{ $district['region_name'] }}</a>  
                </li>
            @endforeach
        </ul>
    </div>
    <div class="tab-content" id="street_list_id" style="
    @if($street_row['region_id'] <= '0')
            display: none;
    @else
            display: block;
    @endif
            ">
        <ul id="street_list">
            @foreach($street_list as $street)
                <li>
                    <a v="{{ $street['region_id'] }}" title="{{ $street['region_name'] }}"
                       @if($street['choosable'])
                            onclick="region.changedDis({{ $street['region_id'] }},{{ $user_id }});"
                            @if($street_row['region_id'] == $street['region_id']) class="selected" @endif
                       @else
                            class="choosable"
                       @endif
                       href="javascript:void(0);" id="street_{{ $street['region_id'] }}">{{ $street['region_name'] }}</a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="mod_storage_state">{{ $lang['Distribution_limit'] }}</div>
    <input type="hidden" value="{{ $province_row['region_id'] ?? 0 }}" id="province_id" name="province_region_id">
    <input type="hidden" value="{{ $city_row['region_id'] ?? 0 }}" id="city_id" name="city_region_id">
    <input type="hidden" value="{{ $district_row['region_id'] ?? 0 }}" id="district_id" name="district_region_id">         
    <input type="hidden" value="{{ $street_row['region_id'] ?? 0 }}" id="street_id" name="street_region_id">
    <input type="hidden" value="{{ $merchant_id ?? 0 }}" id="merchantId" name="merchantId">
    <input type="hidden" value="{{ $region_id ?? 0 }}" id="region_id" name="region_id" />
    <input type="hidden" value="{{ $area_id ?? 0 }}" id="area_id" name="area_id" />
    <input type="hidden" value="{{ $area_city ?? 0 }}" id="area_city" name="area_city" />
</div>
  