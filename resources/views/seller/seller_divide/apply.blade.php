@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')
<script src="{{ asset('assets/mobile/vendor/common/imitate_select.js') }}" type="text/javascript"></script>

<style>
    /*div+js模仿select效果*/
    .imitate_select{ float: left; position:relative;border: 1px solid #dbdbdb;border-radius: 2px;height: 32px;line-height: 30px; margin-right:10px;font-size: 12px;}
    .imitate_select .cite{ background: #fff url({{ asset('assets/admin/images/xjt.png') }}) right 11px no-repeat; padding: 0 10px; cursor:pointer;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left;}
    .imitate_select ul{ position:absolute; top:28px; left:-1px; background:#fff; width:100%; border:1px solid #dbdbdb; border-radius:0 0 3px 3px; display:none; z-index:199; max-height:280px;overflow: auto}
    .imitate_select ul li{ padding:0 10px; cursor:pointer;}
    .imitate_select ul li:hover{ background:#f5faff;}
    .imitate_select ul li a{ display:block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left; color:#707070;}

    .imitate_select ul li.li_not{ text-align:center;padding: 20px 10px;}
    .imitate_select .upward{ top:inherit; bottom:28px; border-radius:3px 3px 0 0;}
    /*div+js模仿select效果end*/

    .ecsc-form-goods dt { width: 18%}
    .ecsc-form-goods dd { width: 80%}
</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                {{--当前位置--}}
                <div class="ecsc-path"><span>{{ __('admin/seller_divide.merchants_base_info') }} - {{ __('admin/seller_divide.seller_divide_apply') }}</span></div>

                @include('seller.base.seller_menu_tab')

                <div class="btn fr">
                    <a href="{{ route('seller/seller_divide/apply_list') }}" class="sc-btn sc-blue-btn"><i class="fa fa-reply"></i>{{ __('admin/seller_divide.seller_divide_apply_list') }}</a>
                </div>
                <div class="clear"></div>

                <div class="wrapper-right of">

                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                        <ul>
                            @foreach(__('admin/seller_divide.seller_divide_apply_tips') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="ecsc-form-goods">
                        <form action="{{ route('seller/seller_divide/apply') }}" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
                            <div class="wrapper-list border1">
                                <dl>
                                    <dt class="label-t">{{ __('admin/seller_divide.divide_channel')  }}：</dt>
                                    <dd class="label_value ">
                                        <div class="checkbox_items">
                                            <div class="checkbox_item">
                                                <input type="radio" name="data[divide_channel]" class="ui-radio evnet_show" id="divide_channel_1" value="1" checked />
                                                <label for="divide_channel_1" class="ui-radio-label active ">{{ __('admin/seller_divide.divide_channel_1') }}</label>
                                            </div>
                                        </div>
                                    </dd>
                                </dl>

                                {{--主体类型--}}
                                <dl>
                                    <dt class="label-t">{{ __('admin/seller_divide.organization_type') }}：</dt>
                                    <dd class="label_value ">
                                        <div style="width:299px;">
                                            <select name="data[organization_type]" class="form-control input-sm w300">
                                                {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                                <option @if(!empty($apply_info['organization_type']) && $apply_info['organization_type'] == 2) selected @endif value="2">{{ __('admin/seller_divide.organization_type_2') }}</option>
                                                <option @if(!empty($apply_info['organization_type']) && $apply_info['organization_type'] == 4) selected @endif value="4">{{ __('admin/seller_divide.organization_type_4') }}</option>
                                            </select>
                                        </div>
                                    </dd>
                                </dl>

                                {{--营业执照--}}
                                <dl>
                                    <dt class="label-t">{{ __('admin/seller_divide.business_license_copy') }}：</dt>
                                    <dd class="label_value ">
                                        <div class="type-file-box">
                                            <input type="button" id="button" class="type-file-button" value="{{ __('admin/order.upload_file') }}">
                                            <input type="file" class="type-file-file" name="business_license_copy" size="30" data-state="imgfile" >
                                            <span class="show">
                                                <a href="#business_license_copy" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                                    <i class="fa fa-picture-o"></i>
                                                </a>
                                            </span>
                                            <input type="text" name="business_license_copy_path" class="type-file-text hide" value="{{ $apply_info['business_license_info']['business_license_copy'] ?? '' }}" readonly style="display:none">
                                        </div>
                                        <div class="notice">{{ __('admin/seller_divide.business_license_copy_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.business_license_number') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[business_license_number]" class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['business_license_number'] ?? '' }}"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.merchant_name') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[merchant_name]" class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['merchant_name'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.merchant_name_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.legal_person') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[legal_person]" class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['legal_person'] ?? '' }}"/>
                                    </dd>
                                </dl>
                                {{--<dl>--}}
                                    {{--<dt class="label-t">{{ __('admin/seller_divide.company_address') }}：</dt>--}}
                                    {{--<dd class="label_value ">--}}
                                        {{--<input type="text" name="data[company_address]" class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['company_address'] ?? '' }}"/>--}}
                                        {{--<div class="notice">{{ __('admin/seller_divide.company_address_notice') }}</div>--}}
                                    {{--</dd>--}}
                                {{--</dl>--}}
                                {{--<dl>--}}
                                    {{--<dt class="label-t">{{ __('admin/seller_divide.business_time') }}：</dt>--}}
                                    {{--<dd class="label_value ">--}}
                                        {{--<input type="text" name="data[business_time]" class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['business_time'] ?? '' }}"/>--}}
                                        {{--<div class="notice">{{ __('admin/seller_divide.business_time_notice') }}</div>--}}
                                    {{--</dd>--}}
                                {{--</dl>--}}

                                {{--法人证件--}}
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.id_doc_type') }}：</dt>
                                    <dd class="label_value ">
                                        <div style="width:299px;">
                                            <select name="data[id_doc_type]" class="form-control input-sm w300">
                                                {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                                <option @if(!empty($apply_info['id_doc_type']) && $apply_info['id_doc_type'] == 'IDENTIFICATION_TYPE_MAINLAND_IDCARD') selected @endif value="IDENTIFICATION_TYPE_MAINLAND_IDCARD">{{ __('admin/seller_divide.id_doc_type_1') }}</option>
                                            </select>
                                        </div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.id_card_copy') }}：</dt>
                                    <dd class="label_value ">
                                        <div class="type-file-box">
                                            <input type="button" id="button" class="type-file-button" value="{{ __('admin/order.upload_file') }}">
                                            <input type="file" class="type-file-file" name="id_card_copy" size="30" data-state="imgfile" >
                                            <span class="show">
                                        <a href="#id_card_copy" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                            <i class="fa fa-picture-o"></i>
                                        </a>
                                    </span>
                                            <input type="text" name="id_card_copy_path" class="type-file-text hide" value="{{ $apply_info['id_card_info']['id_card_copy'] ?? '' }}" readonly  style="display:none">
                                        </div>
                                        <div class="notice">{{ __('admin/seller_divide.id_card_copy_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.id_card_national') }}：</dt>
                                    <dd class="label_value ">
                                        <div class="type-file-box">
                                            <input type="button" id="button" class="type-file-button" value="{{ __('admin/order.upload_file') }}">
                                            <input type="file" class="type-file-file" name="id_card_national" size="30" data-state="imgfile" >
                                            <span class="show">
                                                <a href="#id_card_national" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                                    <i class="fa fa-picture-o"></i>
                                                </a>
                                            </span>
                                            <input type="text" name="id_card_national_path" class="type-file-text hide" value="{{ $apply_info['id_card_info']['id_card_national'] ?? '' }}" readonly style="display:none">
                                        </div>
                                        <div class="notice">{{ __('admin/seller_divide.id_card_national_notice') }}</div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.id_card_name') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[id_card_name]" class="form-control input-sm w300" value="{{ $apply_info['id_card_info']['id_card_name'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.id_card_name_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.id_card_number') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[id_card_number]" class="form-control input-sm w300" value="{{ $apply_info['id_card_info']['id_card_number'] ?? '' }}"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.id_card_valid_time') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[id_card_valid_time]" class="form-control input-sm w300" value="{{ $apply_info['id_card_info']['id_card_valid_time'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.id_card_valid_time_notice') }}</div>
                                    </dd>
                                </dl>

                                {{--结算银行账户--}}
                                <dl>
                                    <dt class="label-t">{{ __('admin/seller_divide.need_account_info') }}：</dt>
                                    <dd class="label_value col-md-10">
                                        <div class="checkbox_items">
                                            <div class="checkbox_item">
                                                <input type="radio" name="data[need_account_info]" class="ui-radio evnet_show" id="value_need_1" value="1" checked>
                                                <label for="value_need_1" class="ui-radio-label active  ">{{ __('admin/common.yes') }}</label>
                                            </div>
                                            {{--<div class="checkbox_item">--}}
                                            {{--<input type="radio" name="data[need_account_info]" class="ui-radio evnet_show" id="value_need_0" value="0">--}}
                                            {{--<label for="value_need_0" class="ui-radio-label ">{{ __('admin/common.no') }}</label>--}}
                                            {{--</div>--}}
                                        </div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.bank_account_type') }}：</dt>
                                    <dd class="label_value ">
                                        <div style="width:299px;">
                                            <select name="data[bank_account_type]" class="form-control input-sm w300">
                                                {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                                <option @if(!empty($apply_info['account_info']) && $apply_info['account_info']['bank_account_type'] == '74') selected @endif value="74">{{ __('admin/seller_divide.bank_account_type_74') }}</option>
                                                <option @if(!empty($apply_info['account_info']) && $apply_info['account_info']['bank_account_type'] == '75') selected @endif value="75">{{ __('admin/seller_divide.bank_account_type_75') }}</option>
                                            </select>
                                        </div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.account_bank') }}：</dt>
                                    <dd class="label_value ">
                                        <div style="width:299px;">
                                            <select name="data[account_bank]" class="form-control input-sm" id="account_bank">
                                                <option value="">{{ __('admin/common.select_please') }}</option>
                                                @if(!empty($bank_list))
                                                    @foreach($bank_list as $val)
                                                        <option @if(!empty($apply_info['account_info']) && $apply_info['account_info']['account_bank'] == $val['bank_name']) selected @endif value="{{ $val['bank_name'] }}">{{ $val['bank_name'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="notice">{{ __('admin/seller_divide.account_bank_notice') }}</div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.account_name') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[account_name]" class="form-control input-sm w300" value="{{ $apply_info['account_info']['account_name'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.account_name_notice') }}</div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.bank_address') }}：</dt>
                                    <dd class="label_value ">
                                        <div class="level_linkage">
                                            <div class="fl">
                                                <div style='display:none;' class="ui-dropdown">
                                                    <input type="hidden" value="1">
                                                </div>
                                                <div class="ui-dropdown smartdropdown alien">
                                                    <input type="hidden" value="{{ $apply_info['account_info']['bank_address_code_province'] ?? '' }}" name="province" id="selProvinces">
                                                    <div class="txt">{{ __('admin/common.province') }}</div>
                                                    <i class="down u-dropdown-icon"></i>
                                                    <div class="options clearfix" style="max-height:300px;">
                                                        @if(!empty($province_list))
                                                            @foreach($province_list as $val)
                                                                <span class="liv" data-text="{{ $val['region_name'] ?? '' }}" data-type="2"  data-value="{{ $list['region_id'] ?? '' }}">{{ $val['region_name'] ?? '' }}</span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                                <div id="dlCity" class="ui-dropdown smartdropdown alien">
                                                    <input type="hidden" value="{{ $apply_info['account_info']['bank_address_code_city'] ?? '' }}" name="city" id="selCities">
                                                    <div class="txt">{{ __('admin/common.city') }}</div>
                                                    <i class="down u-dropdown-icon"></i>
                                                    <div class="options clearfix" style="max-height:300px;">
                                                        @if(!empty($city_list))
                                                            @foreach($city_list as $val)
                                                                <span class="liv" data-text="{{ $val['region_name'] ?? '' }}" data-type="3"  data-value="{{ $list['region_id'] ?? '' }}">{{ $val['region_name'] ?? '' }}</span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                                <div id="dlRegion" class="ui-dropdown smartdropdown alien">
                                                    <input type="hidden" value="{{ $apply_info['account_info']['bank_address_code'] ?? '' }}" name="district" id="selDistricts">
                                                    <div class="txt">{{ __('admin/common.area_alt') }}</div>
                                                    <i class="down u-dropdown-icon"></i>
                                                    <div class="options clearfix" style="max-height:300px;">
                                                        @if(!empty($district_list))
                                                            @foreach($district_list as $val)
                                                                <span class="liv" data-text="{{ $val['region_name'] ?? '' }}" data-type="4"  data-value="{{ $list['region_id'] ?? '' }}">{{ $val['region_name'] ?? '' }}</span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t">{{ __('admin/seller_divide.bank_name') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[bank_name]" class="form-control input-sm w300" value="{{ $apply_info['account_info']['bank_name'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.bank_name_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t">{{ __('admin/seller_divide.bank_branch_id') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[bank_branch_id]" class="form-control input-sm w300" value="{{ $apply_info['account_info']['bank_branch_id'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.bank_branch_id_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.account_number') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[account_number]" class="form-control input-sm w300" value="{{ $apply_info['account_info']['account_number'] ?? '' }}"/>
                                    </dd>
                                </dl>

                                {{--超级管理员信息--}}
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.contact_type') }}：</dt>
                                    <dd class="label_value ">
                                        <div style="width:299px;">
                                            <select name="data[contact_type]" class="form-control input-sm w300">
                                                {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                                <option @if(!empty($apply_info['contact_info']) && $apply_info['contact_info']['contact_type'] == '65') selected @endif value="65">{{ __('admin/seller_divide.contact_type_65') }}</option>
                                                <option @if(!empty($apply_info['contact_info']) && $apply_info['contact_info']['contact_type'] == '66') selected @endif value="66">{{ __('admin/seller_divide.contact_type_66') }}</option>
                                            </select>
                                        </div>
                                        <div class="notice">{{ __('admin/seller_divide.contact_type_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.contact_name') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[contact_name]" class="form-control input-sm w300" value="{{ $apply_info['contact_info']['contact_name'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.contact_name_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.contact_id_card_number') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[contact_id_card_number]" class="form-control input-sm w300" value="{{ $apply_info['contact_info']['contact_id_card_number'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.contact_id_card_number_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.mobile_phone') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[mobile_phone]" class="form-control input-sm w300" value="{{ $apply_info['contact_info']['mobile_phone'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.mobile_phone_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em>{{ __('admin/seller_divide.contact_email') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="email" name="data[contact_email]" class="form-control input-sm w300" value="{{ $apply_info['contact_info']['contact_email'] ?? '' }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.contact_email_notice') }}</div>
                                    </dd>
                                </dl>

                                {{--店铺信息--}}
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.store_name') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[store_name]" class="form-control input-sm w300" value="{{ $apply_info['sales_scene_info']['store_name'] ?? '' }}"/>
                                    </dd>
                                </dl>
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.store_url') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[store_url]" class="form-control input-sm w500" value="{{ $apply_info['sales_scene_info']['store_url'] ?? url('merchants_store.php?merchant_id=' . $ru_id) }}"/>
                                        <div class="notice">{{ __('admin/seller_divide.store_url_notice') }}</div>
                                    </dd>
                                </dl>

                                {{--商户简称--}}
                                <dl>
                                    <dt class="label-t"><em class="color-red"> * </em> {{ __('admin/seller_divide.merchant_shortname') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="text" name="data[merchant_shortname]" class="form-control input-sm w300" value="{{ $apply_info['merchant_shortname'] ?? '' }}"/>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t">&nbsp;</dt>
                                    <dd class="label_value ">
                                        <div class="info_btn">
                                            @csrf
                                            <input type="hidden" name="ru_id" value="{{ $ru_id ?? 0 }}"/>
                                            <input type="hidden" name="handler" value="{{ $handler ?? '' }}"/>
                                            <input type="hidden" name="out_request_no" value="{{ $apply_info['out_request_no'] ?? '' }}"/>
                                            <input type="submit" value="{{ __('admin/common.button_submit') }}" class="sc-btn sc-blueBg-btn btn35"/>
                                        </div>
                                    </dd>
                                </dl>

                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>

    </div>


    {{--图片预览--}}
    <div class="panel panel-default" style="display: none;" id="business_license_copy">
        <div class="panel-body">
            <img src="{{ $apply_info['business_license_info']['business_license_copy'] ?? '' }}" class="business_license_copy"/>
        </div>
    </div>
    <div class="panel panel-default" style="display: none;" id="id_card_copy">
        <div class="panel-body">
            <img src="{{ $apply_info['id_card_info']['id_card_copy'] ?? '' }}" class="id_card_copy"/>
        </div>
    </div>
    <div class="panel panel-default" style="display: none;" id="id_card_national">
        <div class="panel-body">
            <img src="{{ $apply_info['id_card_info']['id_card_national'] ?? '' }}" class="id_card_national"/>
        </div>
    </div>

</div>
<script type="text/javascript" src="{{ asset('assets/admin/js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/vendor/region/region.js') }}"></script>
<script type="text/javascript">

    $.levelLink();//地区三级联动


    $(function () {

        //file移动上去的js
        $(".type-file-box").hover(function () {
            $(this).addClass("hover");
        }, function () {
            $(this).removeClass("hover");
        });

        // fancybox 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '50%',
            closeBtn: true,
            title: ''
        });

        // 上传图片预览
        $("input[name=business_license_copy]").change(function (event) {
            // 根据这个 <input> 获取文件的 HTML5 js 对象
            var files = event.target.files, file;
            if (files && files.length > 0) {
                // 获取目前上传的文件
                file = files[0];

                // 那么我们可以做一下诸如文件大小校验的动作
                if (file.size > 1024 * 1024 * 2) {
                    layer.msg('{{ __('file.file_size_limit') }}');
                    return false;
                }

                // 预览图片
                var reader = new FileReader();
                // 将文件以Data URL形式进行读入页面
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    $(".business_license_copy").attr("src", this.result);
                };
            }
        });

        $("input[name=id_card_copy]").change(function (event) {
            // 根据这个 <input> 获取文件的 HTML5 js 对象
            var files = event.target.files, file;
            if (files && files.length > 0) {
                // 获取目前上传的文件
                file = files[0];

                // 那么我们可以做一下诸如文件大小校验的动作
                if (file.size > 1024 * 1024 * 2) {
                    layer.msg('{{ __('file.file_size_limit') }}');
                    return false;
                }

                // 预览图片
                var reader = new FileReader();
                // 将文件以Data URL形式进行读入页面
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    $(".id_card_copy").attr("src", this.result);
                };
            }
        });

        $("input[name=id_card_national]").change(function (event) {
            // 根据这个 <input> 获取文件的 HTML5 js 对象
            var files = event.target.files, file;
            if (files && files.length > 0) {
                // 获取目前上传的文件
                file = files[0];

                // 那么我们可以做一下诸如文件大小校验的动作
                if (file.size > 1024 * 1024 * 2) {
                    layer.msg('{{ __('file.file_size_limit') }}');
                    return false;
                }

                // 预览图片
                var reader = new FileReader();
                // 将文件以Data URL形式进行读入页面
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    $(".id_card_national").attr("src", this.result);
                };
            }
        });

        $(".form-horizontal").submit(function () {

            var account_bank = $('#account_bank').val();
            if (!account_bank) {
                layer.msg('{{ __('admin/seller_divide.account_bank_required') }}');
                return false;
            }

            var city = $('input[name="city"]').val();
            if (!city) {
                layer.msg('{{ __('admin/seller_divide.bank_address_required') }}');
                return false;
            }

            var organization_type = $('input[name="data[organization_type]"]').val();
            if (organization_type != '2401' || organization_type != '2500') {
                // 主体类型为“小微商户/个人卖家”可选填，其他主体需必填 超级管理员邮箱必填
                var contact_email = $('input[name="data[contact_email]"]').val();
                if (!contact_email) {
                    layer.msg('{{ __('admin/seller_divide.contact_email_required') }}');
                    return false;
                }
            }

            var store_name = $('input[name="data[store_name]"]').val();
            if (!store_name) {
                layer.msg('{{ __('admin/seller_divide.store_name_required') }}');
                return false;
            }

            $('input[type="submit"]').attr('disabled', true);
        });
    })
</script>
@include('seller.base.seller_pagefooter')