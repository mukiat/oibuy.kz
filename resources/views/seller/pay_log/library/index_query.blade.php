{{--index.blade.php--}}
<table id="list-table" class="ecsc-default-table" style="">
    <thead>
    <tr>
        <th>
            <div class="tDiv">{{ __('admin/order.pay_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/pay_log.trans_id') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/pay_log.trans_time') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/order.order_sn') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/pay_log.order_amount') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/pay_log.shop_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/pay_log.trans_status') }}</div>
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
            <div class="tDiv">{{ $val['pay_name'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['transid'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['pay_time_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv"><a href="{{ url(SELLER_PATH . '/order.php?act=info&order_id='. $val['order_id'] ?? 0) }}">{{ $val['order_sn'] ?? '' }}</a></div>
        </td>
        <td>
            <div class="tDiv">{{ $val['order_amount_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['shop_name'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['trans_status_formated'] ?? '' }}</div>
        </td>

        <td class="handle">
            <div class="tDiv a2">
                <a href="{{ route('seller/pay_log/detail', ['log_id'=> $val['log_id']]) }}" class="btn_trash fancybox fancybox.iframe"><i class="fa fa-eye"></i>{{ __('admin/common.view') }}</a>
            </div>
        </td>
    </tr>

    @endforeach

    @else

    <tbody>
    <tr>
        <td class="no-records" colspan="8">{{ __('admin/common.no_records') }}</td>
    </tr>
    </tbody>

    @endif

</table>

@include('seller.base.seller_pageview')