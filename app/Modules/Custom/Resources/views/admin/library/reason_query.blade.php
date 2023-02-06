{{--reason.blade.php--}}
<table cellpadding="0" cellspacing="0" border="0">
    <thead>
    <tr>
        <th width="80%">
            <div class="tDiv">注销原因名称</div>
        </th>
        <th width="20%">
            <div class="tDiv text-center">{{ lang('admin/common.handler') }}</div>
        </th>
    </tr>
    </thead>

    @if(isset($list) && $list)

        @foreach($list as $key => $val)

            <tr>
                <td>
                    <div class="tDiv">{{ $val['reason_name'] ?? '' }}</div>
                </td>
                <td class="handle text-center">
                    <div class="tDiv a2">
                        <a href="{{ route('admin/custom/users/reason_edit', ['id' => $val['id']]) }}" class="btn_edit"><i class="fa fa-edit"></i>编辑</a>
                        <a href="javascript:;" data-href="{{ route('admin/custom/users/reason_delete', ['id' => $val['id']]) }}" class="btn_trash js-delete-share"><i class="fa fa-trash-o"></i>删除</a>
                    </div>
                </td>
            </tr>

        @endforeach

    @else

        <tbody>
        <tr>
            <td class="no-records" colspan="5">{{ lang('admin/common.no_records') }}</td>
        </tr>
        </tbody>

    @endif

    <tfoot>
    <tr>
        <td colspan="5">
            @include('admin.base.pageview')
        </td>
    </tr>
    </tfoot>
</table>
