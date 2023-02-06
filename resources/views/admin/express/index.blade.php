@include('admin.base.header')

<div class="warpper">
    <div class="title">{{ lang('admin/common.third_party_service') }} - {{ lang('admin/express.express_list') }}</div>
    <div class="content">
        <div class="tabs_info">
            <ul>
                <li role="presentation" class="curr"><a href="{{ route('admin.express.index') }}">{{ lang('admin/express.express_setting') }}</a></li>
                <li role="presentation" ><a href="../admin/tp_api.php?act=kdniao">{{ lang('admin/express.facesheet_express_setting') }}</a></li>
            </ul>
        </div>

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(lang('admin/express.express_list_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">

            <div class="common-content">
                <div class="list-div">
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <th class="text-center">{{ lang('admin/express.express_name') }}</th>
                            <th class="text-center">{{ lang('admin/express.express_website') }}</th>
                            <th ><div class="tDiv">{{ lang('admin/express.is_use') }}</div></th>
                            <th class="text-center">{{ lang('admin/common.handler') }}</th>
                        </tr>

                        @if (isset($plugins) && !empty($plugins))

                        @foreach($plugins as $key => $val)

                            <tr>
                                <td  class="text-center">{{ $val['name'] }}</td>
                                <td  class="text-center"><a href="{{ $val['website'] ?? '#' }}" target="_blank">{{ $val['website'] ?? '' }}</a></td>
                                <td class="handle">
                                    <div class="tDiv a2">
                                        @if(isset($val['default']) && $val['default'] == 1)
                                            <div class="switch active" onclick="toggle_is_show('{{ $val['code'] }}', 0)">
                                                <div class="circle"></div>
                                            </div>
                                        @else
                                            <div class="switch " onclick="toggle_is_show('{{ $val['code'] }}', 1)" >
                                                <div class="circle"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="handle text-center">
                                    <div class="tDiv a2">

                                        @if(isset($val['enable']) && $val['enable'] == 1)

                                        <a href="{{ route('admin.express.edit', ['code' => $val['code'], 'handler' => 'edit']) }}" class="btn_edit"><i class="fa fa-edit"></i>{{ lang('admin/common.edit') }}</a>

                                        @else

                                        <a href="{{ route('admin.express.edit',  ['code' => $val['code'], 'enable' => 'install']) }}" class="btn_inst" ><i class="sc_icon sc_icon_inst"></i>{{ lang('admin/common.install') }}</a>

                                        @endif


                                    </div>

                                </td>
                            </tr>

                        @endforeach

                        @endif

                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
<script>
    $(document).on("mouseenter", ".list-div tbody td", function () {
        $(this).parents("tr").addClass("tr_bg_blue");
    });

    $(document).on("mouseleave", ".list-div tbody td", function () {
        $(this).parents("tr").removeClass("tr_bg_blue");
    });

    // 切换显示
    function toggle_is_show(code, value)
    {
        if (!code) {
            return false;
        }

        var url = "{{ route('admin.express.update') }}";

        $.post(url, {code:code, default:value}, function (data) {
            window.location.reload();
            return false;
        }, 'json');
    }

</script>

@include('admin.base.footer')