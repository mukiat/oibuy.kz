<div>
    <div class="user-form foreg-form">
        <form action="gift_gard.php" method="post" name="theForm" id="theForm">
        	<div class="form-row">
            	<div class="form-label"><span class="red">*</span>{{ $lang['Recipient_name'] }}：</div>
                <div class="form-value"><input name="consignee" type="text" class="form-input" id="consignee_{{ $sn }}" value="" /></div>
                <span class="ftx-01 hide consignee_error">{{ $lang['Consignee_info_null'] }}</span>
            </div>
            <div class="form-row">
                <div class="form-label form-label-lh"><span class="red">*</span>{{ $lang['receipt_address'] }}：</div>
                <div class="form-value" ectype="regionLinkage">
                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selCountries_{{ $sn }}">
                        <dt>
                            <span class="txt" ectype="txt">{{ $lang['please_select'] }}</span>
                            <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                        </dt>
                        <dd ectype="layer">

@foreach($country_list as $country)

                            <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                        </dd>
                    </dl>
                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selProvinces_">
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
                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selCities_">
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
                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selDistricts_" style="display:none;">
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
                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selStreets_" style="display:none;">
                        <dt>
                            <span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[3] }}</span>
                            <input type="hidden" value="{{ $consignee['street'] }}" name="street">
                        </dt>
                        <dd ectype="layer">
                            <div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                            <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                        </dd>
                    </dl>
                    <span id="consigneeEreaNote" class="status error hide"></span>
                </div>
            </div>
            <div class="form-row">
            	<div class="form-label"><span class="red">*</span>{{ $lang['Detailed_address'] }}：</div>
                <div class="form-value">
                	<input name="address" type="text" class="form-input form-input-long" size="30"  id="desc_address_{{ $sn }}" value=""/>
                    <span class="ftx-01 hide address_error">{{ $lang['Detailed_address_null'] }}</span>
                </div>
            </div>
            <div class="form-row">
            	<div class="form-label"><span class="red">*</span>{{ $lang['phone_con'] }}：</div>
                <div class="form-value">
                	<input name="mobile" type="text" class="form-input" id="mobile_{{ $sn }}" value="" />
                    <span class="ftx-01 hide phone_error">{{ $lang['Contact_number_null'] }}</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-label">{{ $lang['con_email'] }}：</div>
                <div class="form-value">
                    <input type="text" class="form-input" name="email" value="" id="consignee_email">
                </div>
            </div>
            <!-- <div class="form-row">
            	<div class="form-label">{{ $lang['shipping_time'] }}：</div>
                <div class="form-value">
                	<input name="shipping_time" type="text" class="form-input" id="shipping_time" value="" />
                </div>
            </div> -->
            <input type="hidden" name="act" value="check_take" />
            <input type="hidden" name="goods_id" id="goods_val_id" value="{{ $goods_id ?? 0 }}" />
        @csrf </form>
    </div>
</div>
