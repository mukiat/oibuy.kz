@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                {{--当前位置--}}
                <div class="ecsc-path">
                    <span>{{ __('admin/address_manage.address_manage') }} - {{ __('admin/address_manage.create_address') }}</span>
                </div>

                <div class="wrapper-right of">

                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4>
                        </div>
                        <ul>
                            @foreach(__('admin/address_manage.create_tip_content') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="ecsc-form-goods">
                        <form action="{{ route('seller/address_manage', ['act' => 'store']) }}" method="post" class="form-horizontal">
                            @csrf
                            <div class="switch_info of">
                                <dl>
                                    <dt class="label-t">
                                        <em class="color-red">*</em> {{ __('admin/address_manage.contact') }}：
                                    </dt>
                                    <dd class="label_value">
                                        <input type="text" name="contact" class="text"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t">
                                        <em class="color-red">*</em> {{ __('admin/address_manage.mobile') }}：
                                    </dt>
                                    <dd class="label_value">
                                        <input type="text" name="mobile" class="text"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t">
                                        <em class="color-red">*</em> {{ __('admin/address_manage.location') }}：
                                    </dt>
                                    <dd class="label_value">
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
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t">
                                        <em class="color-red">*</em> {{ __('admin/address_manage.address') }}：
                                    </dt>
                                    <dd class="label_value">
                                        <input type="text" name="address" class="text"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t">
                                        {{ __('admin/address_manage.zipcode') }}：
                                    </dt>
                                    <dd class="label_value">
                                        <input type="text" name="zip_code" class="text"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t">
                                        <em class="color-red">*</em> {{ __('admin/address_manage.setting') }}：
                                    </dt>
                                    <dd class="label_value">
                                        <div class="checkbox_items" ectype="return_type">
                                            @foreach(__('admin/address_manage.type') as $key => $type)
                                                <div class="checkbox_item">
                                                    <input type="checkbox" name="type[]" id="return_type_{{ $key }}" value="{{ $key }}">
                                                    <label class="checkbox_stars" for="return_type_{{ $key }}">{{ $type }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t">&nbsp;</dt>
                                    <dd class="label_value info_btn">
                                        <input type="submit" value="{{ __('admin/common.button_submit') }}"
                                               class="sc-btn sc-blueBg-btn btn35"/>
                                    </dd>
                                </dl>
                            </div>
                        </form>
                    </div>
                </div>
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
@include('seller.base.seller_pagefooter')
