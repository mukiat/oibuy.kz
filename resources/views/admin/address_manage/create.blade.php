@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="warpper">
    <div class="title">
        {{ __('admin/address_manage.address_manage') }} - {{ __('admin/address_manage.create_address') }}
    </div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i>
                <h4>{{ __('admin/common.operating_hints') }}</h4>
                <span id="explanationZoom" title="Tip"></span>
            </div>
            <ul>
                @foreach(__('admin/address_manage.create_tip_content') as $item)
                    <li>{!! $item !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist">
            <div class="main-info of">
                <form action="{{ route('address_manage.store') }}" method="post" class="form-horizontal">
                    @csrf
                    <div class="switch_info of">
                        <div class="item">
                            <div class="label-t">
                                <em class="color-red">*</em> {{ __('admin/address_manage.contact') }}：
                            </div>
                            <div class="label_value">
                                <input type="text" name="contact" class="text"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">
                                <em class="color-red">*</em> {{ __('admin/address_manage.mobile') }}：
                            </div>
                            <div class="label_value">
                                <input type="text" name="mobile" class="text"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">
                                <em class="color-red">*</em> {{ __('admin/address_manage.location') }}：
                            </div>
                            <div class="label_value">
                                <div class="level_linkage">
                                    <div class="fl">
                                        <div style='display:none;' class="ui-dropdown">
                                            <input type="hidden" value="1">
                                        </div>
                                        <div class="ui-dropdown smartdropdown alien">
                                            <input type="hidden" name="province_id" id="selProvinces">
                                            <div class="txt">{{ __('admin/common.province') }}</div>
                                            <i class="down u-dropdown-icon"></i>
                                            <div class="options clearfix" style="max-height:300px;">
                                                @if(!empty($province_list))
                                                    @foreach($province_list as $val)
                                                        <span class="liv" data-text="{{ $val['region_name'] ?? '' }}"
                                                              data-type="2"
                                                              data-value="{{ $val['region_id'] ?? '' }}">{{ $val['region_name'] ?? '' }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        <div id="dlCity" class="ui-dropdown smartdropdown alien">
                                            <input type="hidden" name="city_id" id="selCities">
                                            <div class="txt">{{ __('admin/common.city') }}</div>
                                            <i class="down u-dropdown-icon"></i>
                                            <div class="options clearfix" style="max-height:300px;">
                                                @if(!empty($city_list))
                                                    @foreach($city_list as $val)
                                                        <span class="liv" data-text="{{ $val['region_name'] ?? '' }}"
                                                              data-type="3"
                                                              data-value="{{ $val['region_id'] ?? '' }}">{{ $val['region_name'] ?? '' }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        <div id="dlRegion" class="ui-dropdown smartdropdown alien">
                                            <input type="hidden" name="district_id" id="selDistricts">
                                            <div class="txt">{{ __('admin/common.area_alt') }}</div>
                                            <i class="down u-dropdown-icon"></i>
                                            <div class="options clearfix" style="max-height:300px;">
                                                @if(!empty($district_list))
                                                    @foreach($district_list as $val)
                                                        <span class="liv" data-text="{{ $val['region_name'] ?? '' }}"
                                                              data-type="4"
                                                              data-value="{{ $val['region_id'] ?? '' }}">{{ $val['region_name'] ?? '' }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">
                                <em class="color-red">*</em> {{ __('admin/address_manage.address') }}：
                            </div>
                            <div class="label_value">
                                <input type="text" name="address" class="text"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">
                                {{ __('admin/address_manage.zipcode') }}：
                            </div>
                            <div class="label_value">
                                <input type="text" name="zip_code" class="text"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">
                                <em class="color-red">*</em> {{ __('admin/address_manage.setting') }}：
                            </div>
                            <div class="label_value">
                                <div class="checkbox_items" ectype="return_type">
                                    @foreach(__('admin/address_manage.type') as $key => $type)
                                        <div class="checkbox_item">
                                            <input type="checkbox" name="type[]" id="return_type_{{ $key }}" value="{{ $key }}">
                                            <label class="checkbox_stars" for="return_type_{{ $key }}">{{ $type }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn">
                                <input type="submit" value="{{ __('admin/common.button_submit') }}"
                                       class="button btn-danger bg-red"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('assets/mobile/vendor/region/region.js') }}"></script>
<script type="text/javascript">
    //地区三级联动
    $.levelLink();

    @foreach($shop_address as $address)
        $('#return_type_{{ $address['type'] }}').parent().hide();
    @endforeach
</script>

@include('admin.base.footer')