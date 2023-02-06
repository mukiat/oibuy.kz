@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<style>
    .dates_box_top {height: 32px;}
    .dates_bottom {height: auto;}
    .dates_hms { width: auto;}
    .dates_btn {width: auto;}
    .dates_mm_list span {width: auto;}
    .export_order dl {
        margin-bottom: 10px;
        border: none;
    }
    .export_order dt {
        width: auto;
        min-width: 60px;
        text-align: left;
    }
    .export_order dd {
        width: auto;
    }
    .export_order .order_status {
        max-width: 500px;
    }
    .export_order .order_type {
        max-width: 700px;
    }
    .export_order .export_content {
        max-width: 780px;
    }
    .export_order .checkbox_items .checkbox_item {
        display: inline-block;
        width: 100px;
        margin: 0;
    }
    .export_order .export_content .checkbox_item {
        width: 130px;
    }
    .ecsc-form-goods .border_none {
        border: none;
        padding: 0;
    }
    .ecsc-form-goods .border_none .button_info {
        margin: 0;
    }
</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')
        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                @include('seller.base.seller_nave_header_title')
                <div class="wrapper-right of">
                    <div class="pt10"></div>
                    <div class="ecsc-form-goods export_order">
                        <dl>
                            <dt>{{ __('admin/order.order_status') }}：</dt>
                            <dd>
                                <div class="checkbox_items order_status">
                                    @foreach ($order_status as $key => $status)
                                    <div class="checkbox_item">
                                        <input type="radio" class="ui-radio" name="order_status" id="order_status_{{$key}}" value="{{$key}}" @if ($key == 0) checked="true" @endif />
                                        <label for="order_status_{{$key}}" class="ui-radio-label">{{ __('admin/order_export.' . $status) }}</label>
                                    </div>
                                    @endforeach

                                </div>
                            </dd>
                        </dl>

                        <dl>
                            <dt>{{ __('admin/order_export.order_extension') }}：</dt>
                            <dd>
                                <div class="checkbox_items order_type">
                                    @foreach ($extension_code as $key => $extension)
                                        <div class="checkbox_item">
                                            <input type="radio" class="ui-radio" name="extension_code" id="extension_code_{{$key}}" value="{{$extension}}" @if ($key == 0) checked="true" @endif />
                                            <label for="extension_code_{{$key}}" class="ui-radio-label">{{ __('admin/order_export.' . $extension) }}</label>
                                        </div>
                                    @endforeach

                                </div>
                            </dd>
                        </dl>

                        <dl>
                            <dt>{{ __('admin/order_export.order_referer') }}：</dt>
                            <dd>
                                <div class="checkbox_items">
                                    @foreach ($order_referer as $key => $referer)
                                    <div class="checkbox_item">
                                        <input type="radio" class="ui-radio" name="order_referer" id="{{ $referer }}" value="{{ $referer }}" @if ($key == 0) checked="true" @endif />
                                        <label for="{{ $referer }}" class="ui-radio-label">
                                            @if($referer == 'all_referer')
                                                {{ __('admin/order_export.all_referer') }}
                                            @elseif($referer == 'wxapp')
                                                {{ __('admin/order.wxapp') }}
                                            @elseif($referer == 'app')
                                                APP
                                            @else
                                                {{ $referer }}
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </dd>
                        </dl>
                        <dl>
                            <dt>{{ __('admin/order_export.export_order_info') }}：</dt>
                            <dd>
                                <input type="text" name="order_sn" class="text" value=""  placeholder="{{ __('admin/order_export.export_order_info_placeholder') }}" />
                            </dd>
                        </dl>
                        <dl>
                            <dt>{{ __('admin/order_export.order_user') }}：</dt>
                            <dd>
                                <input type="text" name="user_name" class="text" value=""/>
                            </dd>
                        </dl>
                        <dl>
                            <dt>{{ __('admin/order_export.consignee') }}：</dt>
                            <dd>
                                <input type="text" name="consignees" class="text" value=""/>
                            </dd>
                        </dl>
                        <dl>
                            <dt>{{ __('admin/order.order_time') }}：</dt>
                            <dd>
                                <div class="text_time" id="text_time1">
                                    <input name="start_time" type="text" class="text" id="start_time"  readonly="readonly" autocomplete="off" />
                                </div>
                                <span class="bolang">&nbsp;&nbsp;{{ $lang['to'] }}&nbsp;&nbsp;</span></span>
                                <div class="text_time" id="text_time2">
                                    <input name="end_time" type="text" class="text" id="end_time" value="{{ date('Y-m-d H:i:s') }}" readonly="readonly"  autocomplete="off"/>
                                </div>
                                <a href="javascript:setStartTime(-7);" class="bolang line_height_28 js-select-time red" style="margin-left: 14px">{{ __('admin/order_export.latest_7days') }}</a>
                                <a href="javascript:setStartTime(-30);" class="bolang line_height_28 js-select-time" style="margin-left: 14px">{{ __('admin/order_export.latest_30days') }}</a>
                                <a href="javascript:setStartTime(-90);" class="bolang line_height_28 js-select-time" style="margin-left: 14px">{{ __('admin/order_export.latest_3months') }}</a>
                                <a href="javascript:setStartTime(-365);" class="bolang line_height_28 js-select-time" style="margin-left: 14px">{{ __('admin/order_export.latest_1years') }}</a>
                            </dd>
                        </dl>

                        <dl>
                            <dt class="step_label">{{ __('admin/order_export.export_content') }}：</dt>
                            <dd class="step_value">
                                <div class="checkbox_items export_content" ectype="return_type">
                                    @foreach ($field_name as $key => $field)
                                        <div class="checkbox_item export_content_item">
                                            <input type="checkbox" name="field_name" class="ui-checkbox freight" id="{{$field}}" value="{{$field}}" @if ($key == 0) checked="checked" @endif>
                                            <label class="ui-label" for="{{$field}}">{{ __('admin/order_export.' . $field) }}</label>
                                        </div>
                                    @endforeach
                                    <div class="checkbox_item">
                                        <input type="checkbox" name="" class="ui-checkbox freight" id="checkAll">
                                        <label class="ui-label" for="checkAll">{{ __('admin/order_export.check_all') }}</label>
                                    </div>
                                </div>
                            </dd>
                        </dl>

                    </div>

                    <div class="ecsc-form-goods">
                        <dl class="border_none">
                            <dt>&nbsp;</dt>
                            <dd class="button_info w200">
                                <input type="button" onclick="export_order_list(this)" value="{{ __('admin/order_export.export_download') }}" class="sc-btn sc-blueBg-btn btn35" id="submitBtn"/>

                                <a href="{{ route('seller/export_history', ['callback' => urlencode(request()->getRequestUri())]) }}" style="float: right; margin-top: 8px;">{{ __('admin/order_export.view_export_records') }}</a>
                            </dd>
                        </dl>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>


<script type="text/javascript">
    // 导出内容  全选事件
    $(function () {
        // 设置默认的起始时间
        setStartTime(-7);

        $('#checkAll').on('click', function () {
            $('.export_content_item input').prop('checked', this.checked);
            $('#order_sn').prop('checked', true);
        });
        $('.export_content_item input').on('click', function (event) {
            if ($(this).attr('id') == 'order_sn' && !$(this).prop('checked')) return event.preventDefault();
            var len = $('.export_content_item input:checkbox:checked').length;
            var fieldLen = '{{ count($field_name)}}';
            if (len == fieldLen) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        })
    });

    //导出订单列表
    function export_order_list(e) {
        e.setAttribute('disabled', 'disabled');

        var order_status = $("input[name='order_status']:checked").val();
        var extension_code = $("input[name='extension_code']:checked").val();
        var order_referer = $("input[name='order_referer']:checked").val();
        var order_sn = $("input[name='order_sn']").val();
        var user_name = $("input[name='user_name']").val();
        var consignee = $("input[name='consignees']").val();
        var start_time = $("input[name='start_time']").val();
        var end_time = $("input[name='end_time']").val();

        var obj = document.getElementsByName("field_name");
        var field_name = [];
        for (k in obj) {
            if (obj[k].checked)
                field_name.push(obj[k].value);
        }

        var args = "order_status=" + order_status + "&extension_code=" + extension_code + "&order_referer=" + order_referer + "&order_sn=" + order_sn + "&user_name=" + user_name + "&consignee=" + consignee + "&start_time=" + start_time + "&end_time=" + end_time + "&field_name=" + field_name;

        $.post("{{ route('seller/order/export') }}", args, function () {
            window.location.href = "{{ route('seller/export_history', ['callback' => urlencode(request()->getRequestUri())]) }}";
        }, 'json');
    }

    //时间插件
    var opts1 = {
        'targetId': 'start_time',
        'triggerId': ['start_time'],
        'alignId': 'text_time1',
        'format': '-',
        'hms': 'on'
    }, opts2 = {
        'targetId': 'end_time',
        'triggerId': ['end_time'],
        'alignId': 'text_time2',
        'format': '-',
        'hms': 'on'
    }

    xvDate(opts1);
    xvDate(opts2);

    function setStartTime(v) {
        $("#start_time").val(getDay(v) + ' 00:00:00');
    }

    $('.js-select-time').click(function(){
        //单独a标签点击添加class
        $(this).addClass("red").siblings().removeClass("red");
    });

    function getDay(day){
        var today = new Date();
        var targetday_milliseconds=today.getTime() + 1000*60*60*24*day;
        today.setTime(targetday_milliseconds); //注意，这行是关键代码
        var tYear = today.getFullYear();
        var tMonth = today.getMonth();
        var tDate = today.getDate();

        tMonth = doHandleMonth(tMonth + 1);
        tDate = doHandleMonth(tDate);
        return tYear+"-"+tMonth+"-"+tDate;
    }

    function doHandleMonth(month){
        var m = month;
        if(month.toString().length == 1){
            m = "0" + month;
        }
        return m;
    }
</script>

@include('seller.base.seller_pagefooter')
