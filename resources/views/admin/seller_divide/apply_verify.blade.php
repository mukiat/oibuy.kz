@include('admin.base.header')

{{--汇款验证--}}
@if($handler == 'verify')
<style>
    .item_left, .item_right {width:49%;float:left;}
    .main-info .item .label-t {width:25% !important;}
</style>
<div class="fancy">
    <div class="title">{{  __('admin/seller_divide.seller_divide') }} - {{ __('admin/seller_divide.apply_verify') }}</div>
    <div class="content pt10">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @foreach(__('admin/seller_divide.apply_verify_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist of">
            <div class="main-info">
                <div class="switch_info" style="overflow: inherit">
                    <div class="item_left" >

                        <div class="item_title">
                            <div class="vertical"></div>
                            <div class="f15">{{ __('admin/seller_divide.account_verify') }}</div>
                        </div>

                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_account_name') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['account_name'] ?? '' }}
                            </div>
                        </div>
                        @if(!empty($apply_info['account_validation']['account_no']))
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_account_no') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['account_no'] ?? '' }}
                                <div class="notice">{{ __('admin/seller_divide.verify_account_no_notice') }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_pay_amount') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['pay_amount'] ?? 0 }}
                                <div class="notice">{{ __('admin/seller_divide.verify_pay_amount_notice') }}</div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_destination_account_number') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['destination_account_number'] ?? '' }}
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_destination_account_name') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['destination_account_name'] ?? '' }}
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_destination_account_bank') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['destination_account_bank'] ?? '' }}
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_city') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['city'] ?? '' }}
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_remark') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['remark'] ?? '' }}
                                <div class="notice">{{ __('admin/seller_divide.verify_remark_notice') }}</div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"> {{ __('admin/seller_divide.verify_deadline') }}：</div>
                            <div class="label_value ">
                                {{ $apply_info['account_validation']['deadline'] ?? '' }}
                            </div>
                        </div>
                    </div>

                    @if(!empty($apply_info['legal_validation_url']))
                    <div class="item_right pl10" >
                        <div class="item_title">
                            <div class="vertical"></div>
                            <div class="f15">{{ __('admin/seller_divide.qrcode_verify') }}</div>
                        </div>

                        <div class="item" style="padding-top: 50px;">
                            <div class="label-t" style="width:15% !important;">&nbsp;</div>
                            <div class="label_value text-center">
                                <img src="{{ route('qrcode', ['code_url' => $apply_info['legal_validation_url'], 't' => time()]) }}" />

                                <div class="notice">{{ __('admin/seller_divide.legal_validation_url_notice') }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>
@endif

{{--签约--}}
@if($handler == 'sign')

<div class="fancy">
    <div class="title">{{  __('admin/seller_divide.seller_divide') }} - {{ __('admin/seller_divide.apply_sign') }}</div>
    <div class="content pt10">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @foreach(__('admin/seller_divide.apply_sign_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist of">
            <div class="main-info">
                <div class="switch_info" style="overflow: inherit">

                    @if(!empty($apply_info['sign_url']))
                        <div class="item_title">
                            <div class="vertical"></div>
                            <div class="f15">{{ __('admin/seller_divide.apply_sign') }}</div>
                        </div>

                        <div class="item" style="padding-top: 50px;">
                            <div class="label-t" >&nbsp;</div>
                            <div class="label_value text-center ">
                                <img src="{{ route('qrcode', ['code_url' => $apply_info['sign_url'], 't' => time()]) }}" />

                                <div class="notice">{{ __('admin/seller_divide.sign_url_notice') }}</div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

@endif

<script type="text/javascript">

    $(function () {


    })
</script>
@include('admin.base.footer')