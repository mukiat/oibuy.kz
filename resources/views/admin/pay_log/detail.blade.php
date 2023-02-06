@include('admin.base.header')

<div class="fancy">
    <div class="title">{{ __('admin/common.order_word') }} - {{ __('admin/pay_log.pay_log_detail') }}</div>
    <div class="content">

        <div class="flexilist of">
            <div class="main-info">

                <div class="switch_info" style="overflow: inherit">

                    <div class="item">
                        <div class="label-t"> {{ __('admin/pay_log.trans_id') }}：</div>
                        <div class="label_value ">
                            {{ $info['pay_trade_data']['transaction_id'] ?? '' }}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label-t"> {{ __('admin/pay_log.trans_time') }}：</div>
                        <div class="label_value ">
                            {{ $info['pay_time_formated'] ?? '' }}
                        </div>
                    </div>

                    <div class="item">
                        <div class="label-t"> {{ __('admin/pay_log.order_amount') }}：</div>
                        <div class="label_value ">
                            {{ $info['order_amount_formated'] ?? 0 }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>
<script type="text/javascript">

    $(function () {


    })
</script>
@include('admin.base.footer')