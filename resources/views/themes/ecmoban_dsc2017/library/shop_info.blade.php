
<div class="panel-body">
    <div class="panel-tit"><span>{{ $title['fields_titles'] }}</span></div>
    <div class="list">
        @include('frontend::library/cententFields')
        
        
@if($rs_enabled)

        
        <div class="item">
            <div class="label">
                <em>*</em>
                <span>{{ $lang['belong_region'] }}：</span>
            </div>
            <div class="value">	
                <select name="rs_country_id" class="catselectB" id="selCountries_belong_region" onchange="region.changed(this, 1, 'selProvinces_belong_region')">
                <option value="0">{{ $lang['please_select'] }}{{ $lang['country'] }}</option>
                
@foreach($title['belong_region']['country_list'] as $country)

                <option value="{{ $country['region_id'] }}" 
@if($title['belong_region']['region_level']['0'] == $country['region_id'])
selected
@endif
>{{ $country['region_name'] }}</option>
                
@endforeach

                </select>
                <select name="rs_province_id" class="catselectB" id="selProvinces_belong_region" onchange="region.changed(this, 2, 'selCities_belong_region')">
                <option value="0">{{ $lang['please_select'] }}{{ $lang['province'] }}</option>
                
@foreach($title['belong_region']['province_list'] as $province)

                <option value="{{ $province['region_id'] }}" 
@if($title['belong_region']['region_level']['1'] == $province['region_id'])
selected
@endif
>{{ $province['region_name'] }}</option>
                
@endforeach

                </select>
                <select name="rs_city_id" class="catselectB" id="selCities_belong_region" onchange="region.changed(this, 3, 'selDistricts_belong_region')">
                <option value="0">{{ $lang['please_select'] }}{{ $lang['city'] }}</option>
                
@foreach($title['belong_region']['city_list'] as $city)

                <option value="{{ $city['region_id'] }}" 
@if($title['belong_region']['region_level']['2'] == $city['region_id'])
selected
@endif
>{{ $city['region_name'] }}</option>
                
@endforeach

                </select>
            </div>
        </div>
        
        
@endif

        <div class="item">
            <div class="label">
                <em>*</em>
                <span>{{ $lang['Expected_store_type'] }}：</span>
            </div>
            <div class="value">
                <strong class="red">
@if($title['parentType']['shoprz_type'] == 1)
{!! $lang['flagship_store'] !!}
@elseif ($title['parentType']['shoprz_type'] == 2)
{!! $lang['exclusive_shop'] !!}
@elseif ($title['parentType']['shoprz_type'] == 3)
{!! $lang['franchised_store'] !!}
@endif
</strong>
            </div>
        </div>
        <div class="item">
            <div class="label">
                <span>{{ $lang['Flagship_store_name'] }}：</span>
            </div>
            <div class="value">
                <div class="red">{!! $lang['Flagship_store_name_one'] !!}</div>
            </div>
        </div>
        <div class="item">
            <div class="label">
            	<em>*</em>
                <span>{{ $title['fields_titles'] }}：</span>
            </div>
            <div class="value">
            	<div class="shopTit">{!! $title['titles_annotation'] !!}</div>
                <div class="value_warp">

@if($is_brand > 0)

                	<div class="value_item">
                        <div class="value_label">
                        	<em>*</em>
                            <span>{{ $lang['select_brand_name'] }}:</span>
                        </div>
                        <div class="value_val">
                        	<div class="imitate_select w130 shop_categoryMain" id="shoprz_brandName">
                                <div class="cite"><span>
@if($brand['brandName'])
{{ $brand['brandName'] }}
@else
{{ $lang['Please_select'] }}
@endif
</span><i class="iconfont icon-down"></i></div>
                                <ul>
                                    <li><a href="javascript:void(0);" data-value="">{{ $lang['select_brand_name_two'] }}</a></li>
                                    
@foreach($title['brand_list'] as $brand)

                                    <li><a href="javascript:void(0);" data-value="{{ $brand['brandName'] }}">{{ $brand['brandName'] }}</a></li>
                                    
@endforeach

                                </ul>
                                <input type="hidden" name="ec_shoprz_brandName" value="{{ $brand['brandName'] }}" id="shoprz_brandName_val" />
                            </div>
                        </div>
                    </div>
                
@endif

                    <div class="value_item">
                        <div class="value_label">
                        	<em>*</em>
                            <span>{{ $lang['category_desc_key'] }}:</span>
                        </div>
                        <div class="value_val">
                            <input type="text" name="ec_shop_class_keyWords" size="30" value="{{ $title['parentType']['shop_class_key_words'] }}" class="text" id="shop_class_keyWords">
                        </div>
                    </div>
                    <div class="value_item">
                        <div class="value_label">
                            <span>{{ $lang['select_shop_suffix'] }}:</span>
                        </div>
                        <div class="value_val">
                        	<div class="imitate_select w130 shop_categoryMain" id="shopNameSuffix">
                                <div class="cite"><span>
@if($brand['brandName'])
{{ $brand['brandName'] }}
@else
{{ $lang['Please_select'] }}
@endif
</span><i class="iconfont icon-down"></i></div>
                                <ul>
                                    <li><a href="javascript:void(0);" data-value="0">{{ $lang['Please_select'] }}</a></li>
                                    <li><a href="javascript:void(0);" data-value="{{ $lang['flagship_store'] }}">{{ $lang['flagship_store'] }}</a></li>
                                    <li><a href="javascript:void(0);" data-value="{{ $lang['exclusive_shop'] }}">{{ $lang['exclusive_shop'] }}</a></li>
                                    <li><a href="javascript:void(0);" data-value="{{ $lang['franchised_store'] }}">{{ $lang['franchised_store'] }}</a></li>
                                </ul>
                                <input type="hidden" name="ec_shopNameSuffix" value="{{ $title['parentType']['shop_name_suffix'] }}" id="shopNameSuffix_val" />
                            </div>
                        </div>
                    </div>
                    <div class="value_item">
                        <div class="value_label">
                            <em>*</em>
                            <span>{{ $lang['audit_shop_one'] }}:</span>
                        </div>
                        <div class="value_val">
                            <input type="text" name="ec_rz_shopName" id="rz_shopName" size="30" value="{{ $title['parentType']['rz_shop_name'] }}" class="text required">
                        </div>
                    </div>
                    <div class="value_item">
                        <div class="value_label">
                            <em>*</em>
                            <span>{{ $lang['audit_shop_login'] }}:</span>
                        </div>
                        <div class="value_val">
                            <input type="text" name="ec_hopeLoginName" size="30" value="{{ $title['parentType']['hope_login_name'] }}" class="text required" id="hopeLoginName">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>