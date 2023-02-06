{{--index.blade.php--}}
<table id="list-table" class="ecsc-default-table" style="">
    <thead>
    <tr>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.shop_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.seller_sub_mchid') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.divide_channel') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.bind_time') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/seller_divide.bind_way') }}</div>
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
            <div class="tDiv">{{ $val['shop_name'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['sub_mchid'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['divide_channel_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['add_time_formated'] ?? '' }}</div>
        </td>
        <td>
            <div class="tDiv">{{ $val['add_way_formated'] ?? '' }}</div>
        </td>

        <td class="handle">
            <div class="tDiv a2">
                {{--<a href="{{ route('seller/seller_divide/index', ['id'=> $val['id']]) }}" class="btn_edit"><i class="fa fa-eye"></i>{{ __('admin/common.view') }}</a>--}}
            </div>
        </td>
    </tr>

    @endforeach

    @else

    <tbody>
    <tr>
        <td class="no-records" colspan="6">{{ __('admin/common.no_records') }}</td>
    </tr>
    </tbody>

    @endif

</table>

@include('seller.base.seller_pageview')