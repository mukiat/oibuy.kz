{{--index.blade.php--}}
<table id="list-table" class="ecsc-default-table" style="">
    <thead>
    <tr>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.shop_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.out_request_no') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.applyment_id') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.apply_time') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.applyment_state') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.seller_sub_mchid') }}</div>
        </th>
        <th width="20%">
            <div class="tDiv">{{ __('admin/common.handler') }}</div>
        </th>
    </tr>
    </thead>

    @if(!empty($list))

    @foreach($list as $val)

    <tr>
        <td>
            <div class="tDiv">{{ $val['shop_name'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['out_request_no'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['applyment_id'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['apply_time_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">
                @if (!empty($val['applyment_state']) && $val['applyment_state'] == 'REJECTED')
                <em class="color-red">{{ $val['applyment_state_desc'] ?? '' }}</em>
                 {{--驳回原因详情--}}
                <br>（<i class="status-tips" data-id="{{ $val['id'] }}">{{ __('admin/seller_divide.audit_detail') }}</i>）
                @elseif (!empty($val['applyment_state']) && $val['applyment_state'] == 'ACCOUNT_NEED_VERIFY')
                    <em class="color-red">{{ $val['applyment_state_desc'] ?? '' }}</em>
                    {{--汇款账户验签--}}
                    <br><a class="fancybox fancybox.iframe" href="{{ route('seller/seller_divide/apply_verify', ['handler' => 'verify', 'id' => $val['id']]) }}">{{ __('admin/seller_divide.need_verify') }}</a>
                @else
                    {{ $val['applyment_state_desc'] ?? '' }}
                    @if (!empty($val['applyment_state']) && $val['applyment_state'] == 'NEED_SIGN')
                        {{--待签约--}}
                        <br><a class="color-289 fancybox fancybox.iframe" href="{{ route('seller/seller_divide/apply_verify', ['handler' => 'sign', 'id' => $val['id']]) }}">{{ __('admin/seller_divide.need_sign') }}</a>
                    @endif
                @endif
            </div>
        </td>
        <td>
            <div class="tDiv">{{ $val['sub_mchid'] ?? '' }}</div>
        </td>
        <td class="handle">
            <div class="tDiv a2">
                @if(!empty($val['applyment_id']))
                    {{--驳回重新申请--}}
                    @if (!empty($val['applyment_state']) && $val['applyment_state'] == 'REJECTED')
                        <a href="{{ route('seller/seller_divide/apply', ['handler' => 'repeat', 'ru_id'=> $val['ru_id'], 'divide_channel' => $val['divide_channel']]) }}" class="btn_edit"><i class="fa fa-plus"></i>{{ __('admin/seller_divide.repeat_apply') }}</a>
                    @endif
                    {{--查看--}}
                    <a href="{{ route('seller/seller_divide/apply_detail', ['ru_id'=> $val['ru_id'], 'divide_channel' => $val['divide_channel']]) }}" class="btn_edit"><i class="fa fa-eye"></i>{{ __('admin/common.view') }}</a>
                @else
                <a href="{{ route('seller/seller_divide/apply', ['ru_id'=> $val['ru_id'], 'divide_channel' => $val['divide_channel']]) }}" class="btn_edit"><i class="fa fa-plus"></i>{{ __('admin/seller_divide.apply') }}</a>
                @endif
            </div>
        </td>
    </tr>

    @endforeach

    @else

    <tbody>
    <tr>
        <td class="no-records" colspan="7">{{ __('admin/common.no_records') }}</td>
    </tr>
    </tbody>

    @endif

</table>

@include('seller.base.seller_pageview')