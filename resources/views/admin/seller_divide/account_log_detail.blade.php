@include('admin.base.header')

<style>
    .detail .item .label-t {color:#707070;}
    .detail .item .label_value {color:#333;}
</style>

<div class="fancy">
    <div class="title">{{ __('admin/common.seller') }} - {{ __('admin/seller_divide.account_log_detail') }}</div>
    <div class="content">

        <div class="flexilist of">
            <div class="main-info">
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
                        <div class="label-t"> {{ __('admin/seller_divide.status') }}：</div>
                        <div class="label_value ">
                            @if(isset($info['audit'])&& $info['audit'] == 0)
                                {{ $info['audit_formated'] ?? '' }}
                            @else
                                {{ $info['status_formated'] ?? '' }}
                            @endif
                        </div>
                    </div>

                    @if(!empty($info['reason']))
                    <div class="item">
                        <div class="label-t"> {{ __('admin/seller_divide.reason') }}：</div>
                        <div class="label_value ">{{ $info['reason'] ?? '' }}</div>
                    </div>
                    @endif

                    @if(!empty($info['update_time_formated']))
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.update_time') }}：</div>
                            <div class="label_value ">{{ $info['update_time_formated'] ?? '' }}</div>
                        </div>
                    @endif

                    <div class="item">
                        <div class="label-t">&nbsp;</div>
                        <div class="label_value ">
                            <div class="info_btn">
                                <input type="button" onclick="close_fancybox();" value="{{ __('admin/common.button_submit') }}" class="button btn-danger bg-red fn"/>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">

    function close_fancybox(){
        parent.$.fancybox.close();
    }

    $(function () {


    })
</script>
@include('admin.base.footer')