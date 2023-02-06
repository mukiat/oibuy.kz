
<div id="consignee">
    <form action="javascript:;" method="get" name="theForm" id="theForm" onsubmit="addUpdate_Consignee(this)">
        <input type="hidden" name="consigneeParam.id" id="consignee_form_id">
        <input type="hidden" name="consigneeParam.type" id="consignee_type">
		<input type="hidden" id="gid" value="{{ $gid }}">
        <div id="name_div" class="list">
            <span class="label">
                <em>
                	*
                </em>
                {{ $lang['Consignee'] }}：
            </span>
            <div class="field">
                <input type="text" value="{{ $consignee['consignee'] }}" maxlength="20" name="consignee" id="consignee_name" class="textbox">
                <span id="consigneeNameNote" class="status error hide">{{ $lang['input_Consignee_name'] }}</span>
            </div>
        </div>
        <div id="area_div" class="list select-address">
            <span class="label"><em>*</em>{{ $lang['Local_area'] }}：</span>
            <div class="field">
                <span id="span_area" class="fl">
                    <select name="country" id="selCountries_{{ $sn }}" onchange="region.changed(this, 1, 'selProvinces_{{ $sn }}')" style="border:1px solid #abadb3;">
                        <option value="0">{{ $please_select }}{{ $name_of_region[0] }}</option>

@foreach($country_list as $country)

                        <option value="{{ $country['region_id'] }}"
@if($consignee['country'] == $country['region_id'])
selected
@endif
>{{ $country['region_name'] }}</option>

@endforeach

                    </select>
                    <select name="province" id="selProvinces_{{ $sn }}" onchange="region.changed(this, 2, 'selCities_{{ $sn }}')" style="border:1px solid #abadb3;">
                        <option value="0">{{ $please_select }}{{ $name_of_region[1] }}</option>

@foreach($province_list as $province)

                        <option value="{{ $province['region_id'] }}"
@if($consignee['province'] == $province['region_id'])
selected
@endif
>{{ $province['region_name'] }}</option>

@endforeach

                    </select>
                    <select name="city" id="selCities_{{ $sn }}" onchange="region.changed(this, 3, 'selDistricts_{{ $sn }}')" style="border:1px solid #abadb3;">
                        <option value="0">{{ $please_select }}{{ $name_of_region[2] }}</option>

@foreach($city_list as $city)

                        <option value="{{ $city['region_id'] }}"
@if($consignee['city'] == $city['region_id'])
selected
@endif
>{{ $city['region_name'] }}</option>

@endforeach

                    </select>
                        <select name="district" id="selDistricts_{{ $sn }}" onchange="region.changed(this, 4, 'selStreets_{{ $sn }}')"
@if(!$district_list)
style="display:none;"
@endif
 style="border:1px solid #abadb3;">
                        <option value="0">{{ $please_select }}{{ $name_of_region[3] }}</option>

@foreach($district_list as $district)

                        <option value="{{ $district['region_id'] }}"
@if($consignee['district'] == $district['region_id'])
selected
@endif
>{{ $district['region_name'] }}</option>

@endforeach

                    </select>
                    <select name="street" id="selStreets_{{ $sn }}" style="width:auto; height:28px; border:1px solid #abadb3;
@if(!$street_list)
display:none;
@endif
">
                        <option value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</option>

@foreach($street_list as $street)

                        <option value="{{ $street['region_id'] }}"
@if($consignee['street'] == $street['region_id'])
selected
@endif
>{{ $street['region_name'] }}</option>

@endforeach

                    </select>
                </span>
                <span id="consigneeEreaNote" class="status error hide"></span>
            </div>
        </div>
        <div id="address_div" class="list full-address">
            <span class="label"><em>*</em>{{ $lang['Local_area'] }}：</span>
            <div class="field">
                <span id="areaNameTxt" class="fl selected-address">
                </span>
                <input type="text" maxlength="50" name="address" value="{{ $consignee['address'] }}" id="consignee_address" class="textbox">
                <span id="consigneeAddressNote" class="status error hide">{{ $lang['input_address_info'] }}</span>
            </div>
        </div>
        <div id="call_div" class="list">
            <span class="label"><em>*</em>{{ $lang['phone_con'] }}：</span>
            <div class="field">
                <div class="phone">
                    <input type="text" maxlength="11" name="mobile" value="{{ $consignee['mobile'] }}" id="consignee_mobile" class="textbox">
                    <span id="consigneeMobileTelNote" class="status error hide">{{ $lang['input_contact'] }}</span>
                </div>
            </div>
        </div>
        <div class="form-btn group" style="background:#fff;">
        	<input name="goods_flow_type" value="{{ $goods_flow_type }}" type="hidden">
            <input name="address_id" value="{{ $consignee['address_id'] }}" type="hidden">
            <input name="onSubmit" value="{{ $lang['con_Preservation'] }}" type="submit" class="btnConsignee">
        </div>
    @csrf </form>
</div>
<script type="text/javascript">
function addUpdate_Consignee(frm){
	var info_return = 0;
	var csg = new Object;

	csg.consignee 		= frm.elements['consignee'].value;
	csg.country 		= frm.elements['country'].value;
	csg.province 		= frm.elements['province'].value;
	csg.city 			= frm.elements['city'].value;
	csg.district 		= frm.elements['district'].value;
	csg.street 			= frm.elements['street'].value;
	csg.address 		= frm.elements['address'].value;
	csg.mobile 			= frm.elements['mobile'].value;
	csg.address_id 		= frm.elements['address_id'].value;

	if(csg.consignee == ''){
		return_address_judge('consigneeNameNote', 0);
		info_return = 1;
	}else if(csg.country == 0){

		return_address_judge('consigneeEreaNote', 0);
		$('#consigneeEreaNote').html(json_languages.select_consigne);
		info_return = 1;

	}else if(csg.province == 0){

		return_address_judge('consigneeEreaNote', 0);
		$('#consigneeEreaNote').html(json_languages.Province);
		info_return = 1;

	}else if(csg.city == 0){

		return_address_judge('consigneeEreaNote', 0);
		$('#consigneeEreaNote').html(json_languages.City);
		info_return = 1;

	}else if(!$('#selDistricts_').is(":hidden")){

		if(csg.district == 0){
			return_address_judge('consigneeEreaNote', 0);
			$('#consigneeEreaNote').html(json_languages.District);
			info_return = 1;
		}else{
			if(!$('#selStreets_').is(":hidden")){
				if(csg.street == 0){
					return_address_judge('consigneeEreaNote', 0);
					$('#consigneeEreaNote').html(json_languages.Street);
					info_return = 1;
				}else{
					info_return = 2;
				}
			}else{
				info_return = 2;
			}
		}
	}else{
		info_return = 2;
	}

	if(info_return == 2){
		if(csg.address == ''){
			return_address_judge('consigneeNameNote', 1);
			return_address_judge('consigneeEreaNote', 1);

			return_address_judge('consigneeAddressNote', 0);
			info_return = 1;
		}else if(csg.mobile == ''){
			return_address_judge('consigneeAddressNote', 1);

			return_address_judge('consigneeMobileTelNote', 0);
			info_return = 1;
		}else{
			if(!Utils.isTel(csg.mobile) || csg.mobile.length != 11){
				return_address_judge('consigneeMobileTelNote', 0);
				$('#consigneeMobileTelNote').html("&nbsp;"+json_languages.consignee_legitimate_phone);
				info_return = 1;
			}else{
				info_return = 3;
			}
		}
	}

	if(info_return == 3){
		return_address_judge('consigneeMobileTelNote', 1);
		$('#emailNote').html(" ");
		info_return = 0;
	}

	if(info_return == 0){
		var gid = $('#gid').attr('value');
		Ajax.call('crowdfunding.php', 'act=insert_Consignee&csg=' + $.toJSON(csg) + '&gid=' + gid , addUpdate_ConsigneeResponse, 'POST', 'JSON');
	}

	return false;
}

function addUpdate_ConsigneeResponse(result){
	if(result.once){
		window.location.href='crowdfunding.php?act=checkout&gid=' + result.gid;
	}else{
		$('#consignee-addr').html(result.content);

		$('#zcDig').remove();

		$('#pb-mask').remove();
	}
}

function return_address_judge(obj, type){
	if(type == 0){
		$('#'+obj).removeClass("hide").addClass("show");
	}else if(type == 1){
		$('#'+obj).removeClass("show").addClass("hide");
	}
}
</script>
