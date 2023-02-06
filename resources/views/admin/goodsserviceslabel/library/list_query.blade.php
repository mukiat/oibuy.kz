{{--list.blade.php--}}
<table cellspacing="0" cellpadding="0" border="0">
    <thead>
    <tr>
        <th width="3%" class="sign">
            <div class="tDiv">
                <input type="checkbox" class="checkbox" name="all_list" id="all_list"/>
                <label for="all_list" class="checkbox_stars"></label>
            </div>
        </th>
        <th @if($type == 1) width="10%" @else width="20%" @endif>
            <div class="tDiv">{{ __('admin/goods_services_label.label_name') }}</div>
        </th>
        <th width="10%">
            <div class="tDiv">{{ __('admin/goods_services_label.bind_goods_number') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/goods_services_label.label_image') }}</div>
        </th>
        @if($type == 1)
        <th width="10%">
            <div class="tDiv">{{ __('admin/goods_services_label.start_time') }}</div>
        </th>
        <th width="10%">
            <div class="tDiv">{{ __('admin/goods_services_label.end_time') }}</div>
        </th>
        @endif
        <th width="10%">
            <div class="tDiv">{{ __('admin/goods_services_label.merchant_use') }}</div>
        </th>
        <th width="10%">
            <div class="tDiv">{{ __('admin/goods_services_label.status') }}</div>
        </th>
        <th width="10%">
            <div class="tDiv">{{ __('admin/goods_services_label.sort') }}</div>
        </th>
        <th width="20%">
            <div class="tDiv">{{ __('admin/common.handler') }}</div>
        </th>
    </tr>
    </thead>
    @forelse ($label_list as $key => $label)
        <tr>
            <td class="sign">
                <div class="tDiv">
                    @if ($label['label_code'] != 'no_reason_return')
                    <input type="checkbox" class="checkbox" id="checkbox_{{ $label['id'] }}" name="id[]" value="{{ $label['id'] }}">
                    <label for="checkbox_{{ $label['id'] }}" class="checkbox_stars"></label>
                    @endif
                </div>
            </td>
            <td>
                <div class="tDiv">
                    {{ $label['label_name'] }}
                </div>
            </td>
            <td>
                <div class="tDiv">
                    {{ $label['bind_goods_number'] ?? 0 }}
                </div>
            </td>
            <td>
                <div class="tDiv">
                    <img src="{{ $label['label_image'] }}" height="30"/>
                </div>
            </td>

            @if($type == 1)
            <td>
                <div class="tDiv">
                    {{ $label['start_time_formated'] ?? 0 }}
                </div>
            </td>
            <td>
                <div class="tDiv">
                    {{ $label['end_time_formated'] ?? 0 }}
                </div>
            </td>
            @endif
            <td>
                <div class="tDiv">
                    @if($label['merchant_use'] == 1)
                        <span class="green">{{ __('admin/common.yes') }}</span>
                    @else
                        <span class="red">{{ __('admin/common.no') }}</span>
                    @endif
                </div>
            </td>
            <td>
            @if ($label['label_code'] == 'no_reason_return')
                <div class="tDiv">
                    <img src="{{ asset('assets/admin/images/yes.png') }}" width="14" height="14" alt="YES" />
                </div>
            @else
            @if(isset($label['status']) && $label['status'] == 1)
                <div class="tDiv">
                    <div class="fl switch active" onclick="toggle_is_show('{{ $label['id'] }}', this)" title="{{ __('admin/goods_services_label.use') }}">
                        <input type="hidden" value="0" name="">
                        <div class="circle"></div>
                    </div>
                </div>
            @else
                <div class="tDiv">
                    <div class="fl switch" onclick="toggle_is_show('{{ $label['id'] }}', this)" title="{{ __('admin/goods_services_label.no_use') }}">
                        <input type="hidden" value="1" name="">
                        <div class="circle"></div>
                    </div>
                </div>
            @endif
            @endif
            </td>
            <td>
                <div class="tDiv">{{ $label['sort'] }}</div>
            </td>
            <td class="handle">
                <div class="tDiv a3">
                    <a href="{{ route('admin/goodsserviceslabel/update', ['id' => $label['id'], 'type' => $label['type']]) }}" class="btn_edit"><i class="fa fa-edit"></i>{{ __('admin/common.edit') }}</a>
                    @if ($label['label_code'] != 'no_reason_return')
                    <a href="{{ route('admin/goodsserviceslabel/bind_goods', ['label_id' => $label['id'], 'type' => $label['type']]) }}" class="btn_edit"><i class="fa fa-edit"></i>{{ __('admin/goods_services_label.goods_add_label') }}</a>
                    <a href="javascript:;" ectype="drop" class="btn_trash" data-id="{{ $label['id'] }}"><i class="fa fa-trash-o"></i>{{ __('admin/common.drop') }}</a>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td class="no-records" @if($type == 1) colspan="10" @else colspan="8" @endif>{{ __('admin/common.no_records') }}</td>
        </tr>
    @endforelse
    <tfoot>
    <tr>
        <td colspan="4">
            <div class="tDiv of">
                <div class="tfoot_btninfo">
                    @csrf
                    <input type="button" name="use" class="button bg-green btn_disabled batch" value="{{ __('admin/goods_services_label.batch_use') }}" disabled="disabled" ectype='btnSubmit'>
                    <input type="button" name="no_use" class="button bg-green btn_disabled batch" value="{{ __('admin/goods_services_label.batch_no_use') }}" disabled="disabled" ectype='btnSubmit'>
                    <input type="button" name="drop" class="button bg-green btn_disabled batch" value="{{ __('admin/goods_services_label.batch_drop') }}" disabled="disabled" ectype='btnSubmit'>
                </div>
            </div>
        </td>
        <td @if($type == 1) colspan="6" @else colspan="4" @endif>
            @include('admin.base.pageview')
        </td>
    </tr>
    </tfoot>
</table>
