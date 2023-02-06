@include('admin.base.header')
<script src="{{ asset('assets/mobile/vendor/common/imitate_select.js') }}" type="text/javascript"></script>

<style>
    /*按父容器宽度自动缩放，并且保持图片原本的长宽比*/
    .img-equal {
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
    }
</style>
<div class="warpper">
    <div class="title"><a href="{{ route('admin/seller_divide/apply_list') }}" class="s-back">{{ __('common.back') }}</a> {{ __('admin/seller_divide.merchants_base_info') }} - {{ __('admin/seller_divide.seller_divide_detail') }}</div>
    <div class="content">

        <div class="flexilist">
            <div class="main-info">

                    <div class="switch_info" style="overflow: inherit">

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.divide_channel')  }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio"  class="ui-radio evnet_show" id="divide_channel_1" value="1" @if(!empty($apply_info['divide_channel']) && $apply_info['divide_channel'] == 1) checked @endif />
                                        <label for="divide_channel_1" class="ui-radio-label @if(!empty($apply_info['divide_channel']) && $apply_info['divide_channel'] == 1) active @endif ">{{ __('admin/seller_divide.divide_channel_1') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{--主体类型--}}
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.organization_type') }}：</div>
                            <div class="label_value ">
                                <div style="width:299px;">
                                    <select class="form-control input-sm w300" disabled >
                                        {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                        <option @if(!empty($apply_info['organization_type']) && $apply_info['organization_type'] == 2) selected @endif value="2">{{ __('admin/seller_divide.organization_type_2') }}</option>
                                        <option @if(!empty($apply_info['organization_type']) && $apply_info['organization_type'] == 4) selected @endif value="4">{{ __('admin/seller_divide.organization_type_4') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{--营业执照--}}
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.business_license_copy') }}：</div>
                            <div class="label_value ">
                                <div class="type-file-box">
                                    <img class="img-equal" src="{{ $apply_info['business_license_info']['business_license_copy'] ?? '' }}" >
                                    <span class="show">
                                        <a href="#business_license_copy" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                            <i class="fa fa-picture-o"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.business_license_number') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['business_license_number'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.merchant_name') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['merchant_name'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.legal_person') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['legal_person'] ?? '' }}"/>
                            </div>
                        </div>
                        {{--<div class="item">--}}
                            {{--<div class="label-t">{{ __('admin/seller_divide.company_address') }}：</div>--}}
                            {{--<div class="label_value ">--}}
                                {{--<input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['company_address'] ?? '' }}"/>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="item">--}}
                            {{--<div class="label-t">{{ __('admin/seller_divide.business_time') }}：</div>--}}
                            {{--<div class="label_value ">--}}
                                {{--<input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['business_license_info']['business_time'] ?? '' }}"/>--}}
                            {{--</div>--}}
                        {{--</div>--}}

                        {{--法人证件--}}
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.id_doc_type') }}：</div>
                            <div class="label_value ">
                                <div style="width:299px;">
                                    <select class="form-control input-sm w300" disabled >
                                        {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                        <option @if(!empty($apply_info['id_doc_type']) && $apply_info['id_doc_type'] == 'IDENTIFICATION_TYPE_MAINLAND_IDCARD') selected @endif value="IDENTIFICATION_TYPE_MAINLAND_IDCARD">{{ __('admin/seller_divide.id_doc_type_1') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.id_card_copy') }}：</div>
                            <div class="label_value ">
                                <div class="type-file-box">
                                    <img class="img-equal" src="{{ $apply_info['id_card_info']['id_card_copy'] ?? '' }}" >
                                    <span class="show">
                                        <a href="#id_card_copy" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                            <i class="fa fa-picture-o"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.id_card_national') }}：</div>
                            <div class="label_value ">
                                <div class="type-file-box">
                                    <img class="img-equal" src="{{ $apply_info['id_card_info']['id_card_national'] ?? '' }}" >
                                    <span class="show">
                                        <a href="#id_card_national" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                            <i class="fa fa-picture-o"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.id_card_name') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['id_card_info']['id_card_name'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.id_card_number') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['id_card_info']['id_card_number'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.id_card_valid_time') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['id_card_info']['id_card_valid_time'] ?? '' }}"/>
                            </div>
                        </div>

                        {{--结算银行账户--}}
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.need_account_info') }}：</div>
                            <div class="label_value col-md-10">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" class="ui-radio evnet_show" id="value_need_1" value="1" @if(!empty($apply_info['need_account_info']) && $apply_info['need_account_info'] == '1') checked @endif  >
                                        <label for="value_need_1" class="ui-radio-label @if(!empty($apply_info['need_account_info']) && $apply_info['need_account_info'] == '1') active @endif ">{{ __('admin/common.yes') }}</label>
                                    </div>
                                    {{--<div class="checkbox_item">--}}
                                        {{--<input type="radio" name="data[need_account_info]" class="ui-radio evnet_show" id="value_need_0" value="0">--}}
                                        {{--<label for="value_need_0" class="ui-radio-label ">{{ __('admin/common.no') }}</label>--}}
                                    {{--</div>--}}
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.bank_account_type') }}：</div>
                            <div class="label_value ">
                                <div style="width:299px;">
                                    <select class="form-control input-sm w300" disabled>
                                        {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                        <option @if(!empty($apply_info['account_info']) && $apply_info['account_info']['bank_account_type'] == '74') selected @endif value="74">{{ __('admin/seller_divide.bank_account_type_74') }}</option>
                                        <option @if(!empty($apply_info['account_info']) && $apply_info['account_info']['bank_account_type'] == '75') selected @endif value="75">{{ __('admin/seller_divide.bank_account_type_75') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.account_bank') }}：</div>
                            <div class="label_value ">
                                <div style="width:299px;">
                                    <select class="form-control input-sm" disabled id="account_bank">
                                        @if(!empty($bank_list))
                                            @foreach($bank_list as $val)
                                                <option @if(!empty($apply_info['account_info']) && $apply_info['account_info']['account_bank'] == $val['bank_name']) selected @endif value="{{ $val['bank_name'] }}">{{ $val['bank_name'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.account_name') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['account_info']['account_name'] ?? '' }}"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.bank_address') }}：</div>
                            <div class="label_value ">
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
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.bank_name') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['account_info']['bank_name'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.bank_branch_id') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['account_info']['bank_branch_id'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.account_number') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['account_info']['account_number'] ?? '' }}"/>
                            </div>
                        </div>

                        {{--超级管理员信息--}}
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.contact_type') }}：</div>
                            <div class="label_value ">
                                <div style="width:299px;">
                                    <select class="form-control input-sm w300" disabled>
                                        {{--<option value="">{{ __('admin/common.select_please') }}</option>--}}
                                        <option @if(!empty($apply_info['contact_info']) && $apply_info['contact_info']['contact_type'] == '65') selected @endif value="65">{{ __('admin/seller_divide.contact_type_65') }}</option>
                                        <option @if(!empty($apply_info['contact_info']) && $apply_info['contact_info']['contact_type'] == '66') selected @endif value="66">{{ __('admin/seller_divide.contact_type_66') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.contact_name') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['contact_info']['contact_name'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.contact_id_card_number') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['contact_info']['contact_id_card_number'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.mobile_phone') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['contact_info']['mobile_phone'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/seller_divide.contact_email') }}：</div>
                            <div class="label_value ">
                                <input type="email" readonly class="form-control input-sm w300" value="{{ $apply_info['contact_info']['contact_email'] ?? '' }}"/>
                            </div>
                        </div>

                        {{--店铺信息--}}
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.store_name') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['sales_scene_info']['store_name'] ?? '' }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.store_url') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w500" value="{{ $apply_info['sales_scene_info']['store_url'] ?? '' }}"/>
                            </div>
                        </div>

                        {{--商户简称--}}
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.merchant_shortname') }}：</div>
                            <div class="label_value ">
                                <input type="text" readonly class="form-control input-sm w300" value="{{ $apply_info['merchant_shortname'] ?? '' }}"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value ">
                                <div class="info_btn">
                                    @if(!empty($apply_info['applyment_state']) && $apply_info['applyment_state'] == 'FINISH')
                                        <button type="button" disabled class="btn btn-success">{{ $apply_info['applyment_state_desc'] ?? '' }}</button>
                                        @else
                                        <button type="button" disabled class="btn btn-danger">{{ $apply_info['applyment_state_desc'] ?? '' }}</button>
                                        @if (isset($apply_info['applyment_state']) && $apply_info['applyment_state'] == 'REJECTED')
                                        {{--驳回原因详情--}}
                                        （<i class="status-tips" data-id="{{ $apply_info['id'] ?? 0 }}">{{ __('admin/seller_divide.audit_detail') }}</i>）
                                        @endif
                                    @endif
                                </div>
                            </div>
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

        // fancybox 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '50%',
            closeBtn: true,
            title: ''
        });

        // 拒绝原因提示
        $(document).on('mouseenter', '.status-tips', function () {
            layerTips(this);
        }).on('mouseleave', '.status-tips', function () {
            layerTips(this, 1);
        });

        // 提示层
        var tip_index;
        var url = '{{ route('admin/seller_divide/apply_detail') }}';

        var layerTips = function (obj, close) {
            if (!close) {
                var id = $(obj).attr('data-id');
                var append = '';

                $.post(url, {id:id, field:'audit_detail'}, function(data){
                    if (data.status == 1 && data.content != '') {

                        var audit_detail = data.content.audit_detail;

                        var len = audit_detail.length;
                        for(var j = 0; j < len; j++) {
                            var param_name = audit_detail[j]['param_name'];
                            var reject_reason = audit_detail[j]['reject_reason'];
                            append += '<div class="item" >' + '<span class="label-t">' + param_name + '：</span>' +
                                '<span class="label_value">' + reject_reason + '</span>' + '</div>';
                        }
                        var html = '<div class="check-tips ">' + append + '</div>';

                        tip_index = layer.tips(html, obj, {
                            tipsMore: true,
                            time: 0,
                            tips:[3,'#707070'],
                            area: ['500px'], // tips 宽度500px 高度自动
                        });
                    }
                    return false;
                }, 'json');
            } else {
                //layer.closeAll();
                layer.close(tip_index);
            }
        }

    })
</script>

@include('admin.base.footer')