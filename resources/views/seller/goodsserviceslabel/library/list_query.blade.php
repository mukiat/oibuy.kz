{{--list.blade.php--}}
<table id="list-table" class="ecsc-default-table" style="">
    <thead>
    <tr>
        <th width="15%"><div class="tDiv">{{ __('admin/goods_services_label.label_name') }}</div></th>
        <th width="35%"><div class="tDiv">{{ __('admin/goods_services_label.label_explain') }}</div></th>
        <th width="10%"><div class="tDiv">{{ __('admin/goods_services_label.bind_goods_number') }}</div></th>
        <th width="10%"><div class="tDiv">{{ __('admin/goods_services_label.label_image') }}</div></th>
        <th width="10%"><div class="tDiv">{{ __('admin/goods_services_label.sort') }}</div></th>
        <th width="20%">
            <div class="tDiv">{{ __('admin/common.handler') }}</div>
        </th>
    </thead>

    <tbody>
    @forelse ($label_list as $key=>$label)
        <tr>
            <td>
                <div class="tDiv">
                    {{ $label['label_name'] }}
                    @if($label['label_url'])
                        <a href="{{ $label['label_url'] }}" target="_blank">[{{ __('admin/goods_services_label.see_url') }}]</a>
                    @endif
                </div>
            </td>
            <td>
                <div class="tDiv">
                    {{ $label['label_explain'] ?? '' }}
                </div>
            </td>
            <td>
                <div class="tDiv">
                    {{ $label['bind_goods_number'] ?? 0 }}
                </div>
            </td>
            <td>
                <div class="tDiv">
                    <img src="{{ $label['label_image'] }}" height="30" />
                </div>
            </td>
            <td>
                <div class="tDiv">
                    {{ $label['sort'] }}
                </div>
            </td>

            <td class="handle">
                @if ($label['label_code'] != 'no_reason_return')
                <div class="tDiv a2">
                    <a href="{{ route('seller/goodsserviceslabel/bind_goods', ['label_id' => $label['id'], 'type' => $label['type']]) }}" class="btn_edit "><i class="fa fa-edit"></i>{{ __('admin/goods_services_label.goods_add_label') }}</a>
                </div>
                @endif
            </td>

        </tr>
    @empty

        <tr>
            <td class="no-records" @if($type == 1) colspan="7" @else colspan="4" @endif>{{ __('admin/common.no_records') }}</td>
        </tr>

    @endforelse
    </tbody>
</table>

@include('seller.base.seller_pageview')
