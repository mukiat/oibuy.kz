

@if($region_list)

<div class="s-line">
<div class="s-l-wrap">
	
@if($region_type == 2)

    <div class="s-l-tit"><span>{{ $lang['city_alt'] }}：</span></div>
    
@else

    <div class="s-l-tit"><span>{{ $lang['region'] }}：</span></div>
    
@endif

    <div class="s-l-value">
    	<div class="s-l-v-list">
            <ul>
            
@foreach($region_list as $list)

            <li><a href="javascript:void(0);" data-val="{{ $list['region_id'] }}" data-type="search_district" data-region="{{ $region_type }}" ectype="street_area">{{ $list['region_name'] }}</a></li>
            
@endforeach

            </ul>
        </div>
    </div>
</div>
</div>

@endif
