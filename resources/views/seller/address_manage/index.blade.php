@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                @include('seller.base.seller_nave_header_title')

                <div class="explanation clear mb20" id="explanation">
                    <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                    <ul>
                       @foreach(__('admin/address_manage.tip_content') as $item)
                            <li>{!! $item !!}</li>
                        @endforeach
                    </ul>
                </div>

                @if(count($list) < 2)
                <div class="common-head mt20">
                    <div class="btn fr mb10">
                        <a class="sc-btn sc-blue-btn" href="{{ route('seller/address_manage', ['act' => 'create']) }}"><i class="fa fa-plus"></i>{{ __('admin/address_manage.create') }}</a>
                    </div>
                </div>
                @endif

                <div class="wrapper-list mt20">
                    <div class="list-div" id="listDiv">

                        <table id="list-table" class="ecsc-default-table" style="">
                            <thead>
                            <tr>
                                <th width="3%" class="sign">
                                    <div class="tDiv">
                                        <input type="checkbox" name="all_list" class="checkbox" id="all_list"/>
                                        <label for="all_list" class="checkbox_stars"></label>
                                    </div>
                                </th>
                                <th width="5%">
                                    <div class="tDiv">{{ __('admin/address_manage.serial_number') }}</div>
                                </th>
                                <th width="15%">
                                    <div class="tDiv">{{ __('admin/address_manage.contact') }}</div>
                                </th>
                                <th>
                                    <div class="tDiv">{{ __('admin/address_manage.address') }}</div>
                                </th>
                                <th width="15%">
                                    <div class="tDiv">{{ __('admin/address_manage.mobile') }}</div>
                                </th>
                                <th width="10%">
                                    <div class="tDiv">{{ __('admin/address_manage.zipcode') }}</div>
                                </th>
                                <th width="15%">
                                    <div class="tDiv">{{ __('admin/address_manage.address_type') }}</div>
                                </th>
                                <th width="15%" class="handle">{{ __('admin/common.handler') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $key => $item)
                                <tr>
                                    <td class="sign">
                                        <div class="tDiv">
                                            <input type="checkbox" name="checkboxes[]" value="{{ $item['id'] }}"
                                                   class="checkbox"
                                                   id="checkbox_{{ $item['id'] }}"/>
                                            <label for="checkbox_{{ $item['id'] }}" class="checkbox_stars"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $key + 1 }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $item['contact'] }}</div>
                                    </td>
                                    <td>
                                        <div
                                            class="tDiv">{{ $item['province'] }}{{ $item['city'] }}{{ $item['district'] }}{{ $item['address'] }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $item['mobile'] }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $item['zip_code'] }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ __('admin/address_manage.type.' . $item['type']) }}</div>
                                    </td>
                                    <td class="handle">
                                        <div class="tDiv ht_tdiv">
                                            <a href="{{ route('seller/address_manage', ['act' => 'edit', 'id' => $item['id']]) }}"
                                               class="btn_edit">
                                                <i class="fa fa-edit"></i>{{ __('admin/common.edit') }}</a>
                                            <a href="javascript:;"
                                               onclick="event.preventDefault(); document.getElementById('logout-form{{$key}}').submit();"
                                               class="btn_trash"><i class="fa fa-trash-o"></i>{{ __('admin/common.drop') }}
                                            </a>
                                            <form id="logout-form{{$key}}" method="post"
                                                  action="{{ route('seller/address_manage', ['act' => 'delete', 'id' => $item['id']]) }}">
                                                @csrf
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="no-records" colspan="20">{{ __('admin/common.no_records') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="10">
                                    <div class="tDiv">
                                        <div class="tfoot_btninfo">
                                            <div class="shenhe">
                                                <input type="submit" onclick="confirm_batch()" ectype="btnSubmit"
                                                       value="{{ __('admin/common.batch_delete') }}"
                                                       class="sc-btn btn_disabled" disabled="true">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    // 全选切换效果
    $(document).on("click", "input[name='all_list']", function () {
        if ($(this).prop("checked") == true) {
            $(".list-div").find("input[type='checkbox']:not(:disabled)").prop("checked", true);
            $(".list-div").find("input[type='checkbox']").parents("tr").addClass("tr_bg_org");
        } else {
            $(".list-div").find("input[type='checkbox']:not(:disabled)").prop("checked", false);
            $(".list-div").find("input[type='checkbox']").parents("tr").removeClass("tr_bg_org");
        }

        btnSubmit();
    });

    // 单选切换效果
    $(document).on("click", ".sign .checkbox", function () {
        if ($(this).is(":checked")) {
            $(this).parents("tr").addClass("tr_bg_org");
        } else {
            $(this).parents("tr").removeClass("tr_bg_org");
        }

        btnSubmit();
    });

    // 禁用启用提交按钮
    function btnSubmit() {
        var length = $(".list-div").find("input[name='checkboxes[]']:checked").length;

        if ($("#listDiv *[ectype='btnSubmit']").length > 0) {
            if (length > 0) {
                $("#listDiv *[ectype='btnSubmit']").removeClass("btn_disabled");
                $("#listDiv *[ectype='btnSubmit']").attr("disabled", false);
            } else {
                $("#listDiv *[ectype='btnSubmit']").addClass("btn_disabled");
                $("#listDiv *[ectype='btnSubmit']").attr("disabled", true);
            }
        }
    }

    function confirm_batch() {
        //选中记录
        var ids = new Array();
        $("input[name='checkboxes[]']:checked").each(function(){
            ids.push($(this).val());
        })

        if (ids) {
            $.post("{{ route('seller/address_manage', ['act' => 'delete']) }}", {
                id: ids,
            }, function () {
                window.location.href = '{{ route('seller/address_manage', ['act' => 'list']) }}';
            });
        }

        return false;
    }
</script>
@include('seller.base.seller_pagefooter')
