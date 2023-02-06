{{--account_log.blade.php--}}
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.account_out_request_no') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.business_type') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.shop_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.create_time') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.amount') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.status') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/common.handler') }}</div>
        </th>
    </tr>
    </thead>

    @if(!empty($list))

    @foreach($list as $val)

    <tr>
        <td>
            <div class="tDiv">{{ $val['out_request_no'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['business_type_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['shop_name'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['create_time_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['amount_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">
            @if(isset($val['audit'])&& $val['audit'] == 0)
                {{--审核状态--}}
                 <em>{{ $val['audit_formated'] ?? '' }}</em>
            @else
                {{-- 提现状态、失败原因--}}
                @if(isset($val['status']) && in_array($val['status'], ['FAIL', 'REFUND', 'CLOSE']))
                    <em class="color-red">{{ $val['status_formated'] ?? '' }}</em>
                    （<i class="status-tips" data-id="{{ $val['id'] }}">{{ __('admin/seller_divide.view_reason') }}</i>）
                @else
                    <em>{{ $val['status_formated'] ?? '' }}</em>
                @endif

            @endif
            </div>
        </td>

        <td class="handle">
            <div class="tDiv a2">
                @if(isset($filter['audit'])&& $filter['audit'] == 0)
                    {{--审核--}}
                    <a href="{{ route('admin/seller_divide/account_log_update', ['handler' => 'check', 'id' => $val['id']]) }}" class="btn_edit fancybox fancybox.iframe"><i class="fa fa-edit"></i>{{ __('admin/common.check') }}</a>

                @else
                    {{--查看--}}
                    <a href="{{ route('admin/seller_divide/account_log_detail', ['id' => $val['id'], 'divide_channel' => $val['divide_channel']]) }}" class="btn_edit fancybox fancybox.iframe"><i class="fa fa-eye"></i>{{ __('admin/common.view') }}</a>

                    {{--业务单已创建 需重试--}}
                    @if(isset($val['audit'])&& $val['audit'] == 1 && isset($val['status']) && $val['status'] == 'INIT')
                        <a href="javascript:;" data-href="{{ route('admin/seller_divide/account_log_update', ['handler' => 'retry', 'id' => $val['id'], 'ru_id' => $val['ru_id'], 'divide_channel' => $val['divide_channel']]) }}" class="btn_edit js-retry"><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.retry') }}</a>
                    @endif

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

    <tfoot>
    <tr>
        <td colspan="7">
            @include('admin.base.pageview')
        </td>
    </tr>
    </tfoot>

</table>