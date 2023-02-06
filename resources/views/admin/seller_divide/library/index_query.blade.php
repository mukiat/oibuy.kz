{{--index.blade.php--}}
<table class="table table-striped table-hover">
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
                {{--<a href="{{ route('admin/seller_divide/index', ['id'=> $val['id']]) }}" class="btn_edit"><i class="fa fa-eye"></i>{{ __('admin/common.view') }}</a>--}}
            </div>
        </td>
    </tr>

    @endforeach

    @else

    <tbody>
    <tr>
        <td class="no-records" colspan="6">{{ lang('admin/common.no_records') }}</td>
    </tr>
    </tbody>

    @endif

    <tfoot>
    <tr>
        <td colspan="6">
            @include('admin.base.pageview')
        </td>
    </tr>
    </tfoot>

</table>