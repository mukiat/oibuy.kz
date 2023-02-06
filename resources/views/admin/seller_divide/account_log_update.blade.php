@include('admin.base.header')

<style>
    .detail .item .label-t {color:#707070;}
    .detail .item .label_value {color:#333;}
</style>

<div class="fancy">
    <div class="title">{{ __('admin/common.seller') }} - {{ __('admin/seller_divide.account_log_update') }}</div>
    <div class="content">

        <div class="flexilist of">
            <div class="main-info">
                <form action="{{ route('admin/seller_divide/account_log_update') }}" method="post" class="form-horizontal" role="form" onsubmit="return false;">
                <div class="switch_info detail" style="overflow: inherit">
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.shop_name') }}：</div>
                        <div class="label_value ">{{ $info['shop_name'] ?? '' }}</div>
                    </div>
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.transfer_out_account') }}：</div>
                        <div class="label_value ">{{ $info['transfer_out_account'] ?? '' }}</div>
                    </div>
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.transfer_in_account') }}：</div>
                        <div class="label_value ">{{ $info['transfer_in_account'] ?? '' }}</div>
                    </div>
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.account_out_request_no') }}：</div>
                        <div class="label_value ">{{ $info['out_request_no'] ?? '' }}</div>
                    </div>
                    @if(!empty($info['withdraw_id']))
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.withdraw_id') }}：</div>
                        <div class="label_value ">{{ $info['withdraw_id'] ?? '' }}</div>
                    </div>
                    @endif
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.create_time') }}：</div>
                        <div class="label_value ">{{ $info['create_time_formated'] ?? '' }}</div>
                    </div>
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.business_type') }}：</div>
                        <div class="label_value ">{{ $info['business_type_formated'] ?? '' }}</div>
                    </div>
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.amount') }}：</div>
                        <div class="label_value ">{{ $info['amount_formated'] ?? '' }}</div>
                    </div>

                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.audit') }}：</div>
                        <div class="label_value ">
                            <div class="checkbox_items">
                                <div class="checkbox_item">
                                    <input type="radio" name="data[audit]" class="ui-radio evnet_show" id="value_117_1" value="0" checked>
                                    <label for="value_117_1" class="ui-radio-label active ">{{ __('admin/common.not_audited') }}</label>
                                </div>
                                <div class="checkbox_item">
                                    <input type="radio" name="data[audit]" class="ui-radio evnet_show" id="value_117_0" value="1">
                                    <label for="value_117_0" class="ui-radio-label ">{{ __('admin/common.audited_adopt') }}</label>
                                </div>
                            </div>
                            <div class="notice">{{ __('admin/seller_divide.fund_withdraw_notice') }}</div>
                        </div>
                    </div>

                    <div class="item">
                        <div class="label-t">&nbsp;</div>
                        <div class="label_value ">
                            <div class="info_btn">
                                @csrf
                                <input type="hidden" name="id" value="{{ $info['id'] ?? 0 }}"/>
                                <input type="hidden" name="ru_id" value="{{ $info['ru_id'] ?? 0 }}"/>
                                <input type="hidden" name="divide_channel" value="{{ $info['divide_channel'] ?? 0 }}"/>
                                <input type="hidden" name="handler" value="{{ $handler ?? '' }}"/>
                                <input type="submit" value="{{ __('admin/common.button_submit') }}" class="button btn-danger bg-red fn"/>
                            </div>
                        </div>
                    </div>

                </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">

    $(function () {

        // 提交
        $(".form-horizontal").submit(function () {

            var ajax_data = $(this).serialize();

            var audit = $('input[name="data[audit]"]:checked').val();
            if (audit == 1) {
                // 审核通过 询问框
                layer.confirm('{{  __('admin/seller_divide.confirm_audit') }}', {
                    btn: ['{{ __('admin/common.ok') }}', '{{ __('admin/common.cancel') }}'] //按钮
                }, function () {
                    // 确定提交

                    $.post("{{ route('admin/seller_divide/account_log_update') }}", ajax_data, function (data) {
                        layer.msg(data.msg);
                        if (data.error == 0) {
                            $('input[type="submit"]').attr('disabled', true);
                            setTimeout(function(){
                                // 关闭弹窗
                                parent.$.fancybox.close();
                                window.parent.location = data.url;
                            }, 1000);
                        }
                        return false;
                    }, 'json');

                });
            } else {
                parent.$.fancybox.close();
            }
        });

    })
</script>
@include('admin.base.footer')