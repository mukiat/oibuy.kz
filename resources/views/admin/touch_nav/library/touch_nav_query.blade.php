{{--index.blade.php--}}
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>
            <div class="tDiv">{{ __('admin/touch_nav.touch_nav_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/touch_nav.touch_nav_parent') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/touch_nav.touch_nav_url') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin::touch_navigator.item_ifshow') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin::touch_navigator.item_vieworder') }}</div>
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
                    <div class="tDiv">{{ $val['name'] }}</div>
                </td>
                <td>
                    <div class="tDiv"><a class="color-red" href="{{ route('admin/touch_nav/index', ['device' => $device, 'parent_id' => $val['parent_id'] ?? 0]) }}">{{ $val['parent_name'] ?? '' }}</a></div>
                </td>
                <td>
                    <div class="tDiv">{{ $val['url'] }}</div>
                </td>
                <td class="handle">
                    <div class="tDiv a2">
                        @if(isset($val['ifshow']) && $val['ifshow'] == 1)
                            <div class="switch active" onclick="toggle_is_show('{{ $val['id'] }}', 0)">
                                <div class="circle"></div>
                            </div>
                        @else
                            <div class="switch " onclick="toggle_is_show('{{ $val['id'] }}', 1)" >
                                <div class="circle"></div>
                            </div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="pt5">
                        <input type="text" class="form-control input-sm w60 text-center" value="{{ $val['vieworder'] ?? 0 }}" onblur="update_vieworder(this.value,'{{ $val['id'] }}')" />
                    </div>
                </td>

                <td class="handle">
                    <div class="tDiv a2">
                        <a href="{{ route('admin/touch_nav/edit', ['device' => $device, 'id'=> $val['id']]) }}" class="btn_edit"><i class="fa fa-edit"></i>{{ __('admin/common.edit') }}</a>
                        <a href="javascript:;" data-href="{{ route('admin/touch_nav/delete', ['id'=> $val['id']]) }}" class="btn_trash js-delete"><i class="fa fa-trash-o"></i>{{ __('admin/common.delete') }}</a>
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