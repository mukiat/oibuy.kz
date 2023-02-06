<table cellpadding="0" cellspacing="0" border="0">
    <thead>
    <tr>
        <th width="5%">
            <div class="tDiv">{{ __('admin/common.record_id') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/express_manage.express_company') }}</div>
        </th>
        <th width="15%">
            <div class="tDiv">{{ __('admin/express_manage.express_number') }}</div>
        </th>
        <th width="15%">
            <div class="tDiv">{{ __('admin/express_manage.tracking_number') }}</div>
        </th>
        <th width="15%">
            <div class="tDiv">{{ __('admin/express_manage.order_number') }}</div>
        </th>
        <th width="15%">
            <div class="tDiv">{{ __('admin/express_manage.merchant') }}</div>
        </th>
        <th width="12%">
            <div class="tDiv">{{ __('admin/express_manage.delivery_time') }}</div>
        </th>
        <th width="8%">
            <div class="tDiv text-center">{{ __('admin/express_manage.operation') }}</div>
        </th>
    </tr>
    </thead>

    <tbody>
    @if(!$list->isEmpty())
        @foreach($list as $item)
            <tr>
                <td>
                    <div class="tDiv">{{ $item->id }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->express_name }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->express_sn }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->ship_sn }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->order_sn }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->shop_name }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->created_at }}</div>
                </td>
                <td class="handle text-center">
                    <a href="order.php?act=delivery_info&delivery_id={{ $item->delivery_id }}">{{ __('admin/express_manage.detail') }}</a>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td class="no-records" colspan="8">{{ __('admin/common.no_records') }}</td>
        </tr>
    @endif
    </tbody>
    <tfoot>
    <tr>
        <td colspan="12">
            <div class="tDiv">
                @include('admin.base.pageview')
            </div>
        </td>
    </tr>
    </tfoot>
</table>
