{{--logout.blade.php--}}
<table cellpadding="0" cellspacing="0" border="0">
    <thead>
    <tr>
        <th width="20%">
            <div class="tDiv">用户名</div>
        </th>
        <th width="15%">
            <div class="tDiv"> 用户昵称</div>
        </th>
        <th width="15%">
            <div class="tDiv">手机号</div>
        </th>
        <th width="25%">
            <div class="tDiv">注销原因</div>
        </th>
        <th width="15%">
            <div class="tDiv">注销时间</div>
        </th>
        <th width="10%">
            <div class="tDiv text-center">{{ lang('admin/common.handler') }}</div>
        </th>
    </tr>
    </thead>

    @if(isset($list) && $list)

        @foreach($list as $key => $val)

            <tr>
                <td>
                    <div class="tDiv">{{ $val['user_name'] ?? '' }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $val['nick_name'] ?? '' }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $val['mobile'] ?? '' }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $val['logout_reason'] ?? '' }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $val['create_time'] ?? '' }}</div>
                </td>
                <td class="handle text-center">
                    N/A
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
