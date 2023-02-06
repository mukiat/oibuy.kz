@include('admin.base.header')

<div class="warpper">
    <div class="title">{{ __('admin/common.seller') }} - {{  __('admin::merchants_commission.commission_bill_detail') }} {{ __('admin/seller_divide.order_divide_detail') }}</div>
    <div class="content">

        <div class="flexilist of">
            <div class="main-info">
                <div class="switch_info" style="overflow: inherit">
                    <div class="item">
                        <div class="label-t"> {{ trans('admin/order.order_sn') }}：</div>
                        <div class="label_value ">
                            {{ $info['order_sn'] ?? '' }}
                        </div>
                    </div>
                    <div class="item">
                        {{--商户分账单号--}}
                        <div class="label-t"> {{ trans('admin/seller_divide.bill_out_order_no') }}：</div>
                        <div class="label_value ">
                            {{ $info['bill_out_order_no'] ?? '' }}
                        </div>
                    </div>
                    <div class="item">
                        {{--支付交易单号--}}
                        <div class="label-t"> {{ trans('admin/seller_divide.transaction_id') }}：</div>
                        <div class="label_value ">
                            {{ $info['transaction_id'] ?? '' }}
                        </div>
                    </div>
                    <div class="item">
                        {{--微信分账单号--}}
                        <div class="label-t"> {{ trans('admin/seller_divide.divide_order_id') }}：</div>
                        <div class="label_value ">
                            {{ $info['divide_order_id'] ?? '' }}
                        </div>
                    </div>
                    <div class="item">
                        {{--商家分账金额--}}
                        <div class="label-t"> {{ trans('admin/seller_divide.should_amount') }}：</div>
                        <div class="label_value ">
                            {{ $info['should_amount_format'] ?? 0 }}
                        </div>
                    </div>
                    <div class="item">
                        {{--商家分账比例--}}
                        <div class="label-t"> {{ trans('admin/seller_divide.should_proportion') }}：</div>
                        <div class="label_value ">
                            {{ $info['should_proportion_format'] ?? 0 }}
                        </div>
                    </div>
                    <div class="item">
                        {{--分账状态--}}
                        <div class="label-t"> {{ trans('admin/seller_divide.divide_status') }}：</div>
                        <div class="label_value ">
                            {{ $info['status_format'] ?? '' }}
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