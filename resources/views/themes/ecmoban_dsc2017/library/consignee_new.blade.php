<div class="user-form foreg-form">
    <form action="javascript:;" method="get" name="theForm" id="theForm" class="user-form">
        <div class="form-row">
            <div class="form-label"><span class="red">*</span>{{ $lang['Consignee'] }}：</div>
            <div class="form-value">
                <input type="text" class="form-input" value="{{ $consignee['consignee'] }}" maxlength="20" name="consignee" id="consignee_name">
                <div class="form_prompt"></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-label"><span class="red">*</span>{{ $lang['phone_con'] }}：</div>
            <div class="form-value">
                <input type="text" class="form-input" maxlength="11" name="mobile" value="{{ $consignee['mobile'] }}" id="consignee_mobile">
                <span class="fl">{{ $lang['Fixed_telephone'] }}：</span>
                <input type="text" class="form-input" maxlength="20" value="{{ $consignee['tel'] }}" name="tel" id="consignee_phone">
                <div class="form_prompt"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-label form-label-lh"><span class="red">*</span>{{ $lang['Local_area'] }}：</div>
            <div class="form-value" ectype="regionLinkage">
                <dl class="mod-select mod-select-small fl" ectype="smartdropdown" id="selCountries_">
                    <dt>
                    	<span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[0] }}</span>
                        <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                    </dt>
                    <dd ectype="layer">

@foreach($country_list as $country)

                        <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                    </dd>
                </dl>
                <dl class="mod-select mod-select-small fl" ectype="smartdropdown" id="selProvinces_">
                    <dt>
                        <span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[1] }}</span>
                        <input type="hidden" value="{{ $consignee['province'] }}" name="province">
                    </dt>
                    <dd ectype="layer">
                        <div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                        <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                    </dd>
                </dl>
                <dl class="mod-select mod-select-small fl" ectype="smartdropdown" id="selCities_">
                    <dt>
                        <span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[2] }}</span>
                        <input type="hidden" value="{{ $consignee['city'] }}" name="city">
                    </dt>
                    <dd ectype="layer">
                        <div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                        <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                    </dd>
                </dl>
                <dl class="mod-select mod-select-small fl" ectype="smartdropdown" id="selDistricts_" style="display:none;">
                    <dt>
                        <span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[3] }}</span>
                        <input type="hidden" value="{{ $consignee['district'] }}" name="district">
                    </dt>
                    <dd ectype="layer">
                        <div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

                        <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                    </dd>
                </dl>
                <dl class="mod-select mod-select-small fl" ectype="smartdropdown" id="selStreets_" style="display:none;">
                    <dt>
                        <span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[3] }}</span>
                        <input type="hidden" value="{{ $consignee['street'] }}" name="street" class="ignore">
                    </dt>
                    <dd ectype="layer">
                        <div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                        <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                    </dd>
                </dl>
                <div class="form_prompt lh40"></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-label"><span class="red">*</span>{{ $lang['address_info'] }}：</div>
            <div class="form-value">
                <input type="text" class="form-input form-input-long" name="address" value="{{ $consignee['address'] }}" id="consignee_address">
                <div class="form_prompt"></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-label">{{ $lang['con_email'] }}：</div>
            <div class="form-value">
                <input type="text" class="form-input" name="email" value="{{ $consignee['email'] }}" id="consignee_email">
                <div class="form_prompt"></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-label">{{ $lang['Zip_code'] }}：</div>
            <div class="form-value">
                <input type="text" class="form-input" name="zipcode" value="{{ $consignee['zipcode'] }}" id="consignee_zipcode">
                <span id="consigneeZipcodeNote" class="status error"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-label">{{ $lang['con_sign_building'] }}：</div>
            <div class="form-value"><input type="text" class="form-input fl" value="{{ $consignee['sign_building'] }}" name="sign_building" id="consignee_sign_building"><div class="notic">{{ $lang['sign_building_desc'] }}&nbsp;&nbsp;</div></div>
            <span id="consigneeAliasNote" class="status error hide">{{ $lang['inputcon_sign_building'] }}</span>
        </div>
        <div class="form-row">
            <div class="form-label">{{ $lang['deliver_goods_time'] }}：</div>
            <div class="form-value">
                <input type="text" class="form-input" value="{{ $consignee['best_time'] }}" name="best_time" id="consignee_best_time">
                <span id="consigneeBestTimeNote" class="status error"></span>
            </div>
        </div>
        <input name="goods_flow_type" value="{{ $goods_flow_type }}" type="hidden">
        <input name="address_id" value="{{ $consignee['address_id'] }}" type="hidden">
    @csrf </form>
</div>
