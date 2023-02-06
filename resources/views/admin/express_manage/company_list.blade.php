<table cellpadding="0" cellspacing="0" border="0">
    <thead>
    <tr>
        <th width="3%" class="sign">
            <div class="tDiv"><input type="checkbox" name="all_list" class="checkbox" id="all_list"/>
                <label for="all_list" class="checkbox_stars"></label></div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/express_manage.express_company') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/express_manage.express_type') }}</div>
        </th>
        <th width="20%">
            <div class="tDiv">{{ __('admin/express_manage.initial') }}</div>
        </th>
        <th width="8%">
            <div class="tDiv">{{ __('admin/express_manage.on') }}</div>
        </th>
    </tr>
    </thead>

    <tbody>
    @if(!$list->isEmpty())
        @foreach($list as $item)
            <tr>
                <td class="sign">
                    <div class="tDiv">
                        <input type="checkbox" class="checkbox" value="{{ $item->id }}" id="checkbox_{{ $item->id }}"
                               name="checkboxes[]">
                        <label for="checkbox_{{ $item->id }}" class="checkbox_stars  "></label>
                    </div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->name }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->type }}</div>
                </td>
                <td>
                    <div class="tDiv">{{ $item->code }}</div>
                </td>
                <td class="handle text-center">
                    <div class="switch @if($item->status) active @endif  "
                         onclick="javascript:toggle_is_show(this, '{{ $item->id }}', {{ $item->status }})"
                         title="{{ lang('drp::admin/drp.already_disabled') }}">
                        <div class="circle"></div>
                    </div>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td class="no-records" colspan="5">{{ __('admin/common.no_records') }}</td>
        </tr>
    @endif
    </tbody>
    <tfoot>
    <tr>
        <td colspan="12">
            <div class="tDiv">
                <div class="tfoot_btninfo fl">
                    <input type="hidden" name="act" value="batch"/>

                    <!-- 操作类型 start -->
                    <div class="imitate_select select_w120">
                        <select class="form-control input-sm font12" name="statusHandler">
                            <optgroup label="{{ __('admin/common.select_please')}}">
                                <option value="1">{{ __('admin/express_manage.on') }}</option>
                                <option value="0">{{ __('admin/express_manage.off') }}</option>
                            </optgroup>
                        </select>
                    </div>
                    <!-- 操作类型  end -->

                    <input type="submit" onclick="confirm_batch()" ectype="btnSubmit" value="{{ __('admin/common.ok')}}"
                           class="button bg-green btn_disabled" disabled="true">
                </div>

                @include('admin.base.pageview')
            </div>
        </td>
    </tr>
    </tfoot>
</table>
