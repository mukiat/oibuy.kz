@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<style>
    .detail .label-t {color:#707070;}
    .detail .label_value {color:#333;}
</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                {{--当前位置--}}
                <div class="ecsc-path"><span>{{ __('admin/common.seller') }} - {{ __('admin/seller_divide.account_log_apply') }}</span></div>

                <div class="wrapper-right of">
                    <div class="ecsc-form-goods">
                        <form action="{{ route('seller/seller_divide/account_log_apply') }}" method="post" class="form-horizontal" role="form" onsubmit="return false;">
                            <div class="wrapper-list border1 detail">
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.seller_sub_mchid') }}：</dt>
                                    <dd class="label_value txtline">
                                        {{ $amount_info['sub_mchid'] ?? '' }}
                                        <em class="ml10"><a class="sc-btn sc-blue-btn js_account_amount_query" href="javascript:;" ><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.account_amount_query') }}</a></em>
                                    </dd>
                                </dl>
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.available_amount') }}：</dt>
                                    <dd class="label_value txtline"><em class="color-red font14">{{ $amount_info['available_amount_formated'] ?? 0 }}</em></dd>
                                </dl>
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.account_name') }}：</dt>
                                    <dd class="label_value txtline">
                                        {{ $apply_info['account_info']['account_name'] ?? '' }}
                                        <div class="notice">{{ __('admin/seller_divide.withdraw_account_name_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.account_number') }}：</dt>
                                    <dd class="label_value txtline">
                                        {{ $apply_info['account_info']['account_number'] ?? '' }}
                                        <div class="notice">{{ __('admin/seller_divide.withdraw_account_number_notice') }}</div>
                                    </dd>
                                </dl>
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.account_bank') }}：</dt>
                                    <dd class="label_value txtline">
                                        {{ $apply_info['account_info']['account_bank'] ?? '' }}
                                        <div class="notice">{{ __('admin/seller_divide.withdraw_account_bank_notice') }}</div>
                                    </dd>
                                </dl>
                                @if(!empty($apply_info['account_info']['bank_branch_id']))
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.bank_branch_id') }}：</dt>
                                    <dd class="label_value txtline  ">{{ $apply_info['account_info']['bank_branch_id'] ?? '' }}</dd>
                                </dl>
                                @endif
                                <dl >
                                    <dt class="label-t"> {{ __('admin/seller_divide.amount') }}：</dt>
                                    <dd class="label_value ">
                                        <input type="number" name="amount" class="form-control input-sm w300" min="0" step="0.01" />
                                        <div class="notice">{{ __('admin/seller_divide.apply_amount_notice') }}</div>
                                    </dd>
                                </dl>

                                <dl >
                                    <dt class="label-t">&nbsp;</dt>
                                    <dd class="label_value ">
                                        <div class="info_btn">
                                            @csrf
                                            <input type="hidden" name="divide_channel" value="{{ $divide_channel ?? 0 }}"/>
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
</div>
<script type="text/javascript">

    $(function () {

        // 实时余额查询
        $(document).on("click", ".js_account_amount_query", function() {
            var url = '{{ route('seller/seller_divide/account_log_query') }}';

            var divide_channel = "{{ $divide_channel ?? 1 }}";
            var sub_mchid = "{{ $amount_info['sub_mchid'] ?? '' }}";

            $.post(url, {divide_channel: divide_channel, sub_mchid: sub_mchid}, function (data) {
                layer.msg(data.msg);
                setTimeout(function(){
                    window.location.reload();
                }, 1000);
                return false;
            }, 'json');

        });

        // 提交
        $(".form-horizontal").submit(function () {

            var amount = $('input[name="amount"]').val();
            if (!amount) {
                layer.msg('{{ __('admin/seller_divide.apply_amount_required') }}');
                return false;
            }

            amount = parseFloat(amount);

            var available_amount = '{{ $amount_info['available_amount'] ?? 0 }}';
            available_amount = parseFloat(available_amount);
            if (amount > available_amount) {
                layer.msg('{{ __('admin/seller_divide.max_apply_amount') }}');
                return false;
            }

            var ajax_data = $(this).serialize();

            $.post('{{ route('seller/seller_divide/account_log_apply') }}', ajax_data, function (data) {
                layer.msg(data.msg);
                if (data.error == 0) {
                    $('input[type="submit"]').attr('disabled', true);
                    if (data.url) {
                        setTimeout(function(){
                            window.location.href = data.url;
                        }, 1000);
                    }
                    window.location.reload();
                }
                return false;
            }, 'json');
        });

    })
</script>
@include('seller.base.seller_pagefooter')