@include('admin.app.pageheader')

<div class="wrapper">
    <div class="title"><a href="{{ route('admin/app/client_manage') }}" class="s-back"></a>{{ lang('admin/app.client_push_list') }} - {{ $client_name }}</div>
    <div class="content_tips">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span></div>
            <ul>
                <li>{{ lang('admin/app.client_product_list_li_0') }}</li>
                <li>{{ lang('admin/app.client_product_list_li_1') }}</li>
                <li>{{ lang('admin/app.client_product_list_li_2') }}</li>
                <li>{{ lang('admin/app.client_product_list_li_3') }}</li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="fl">
                    <a href="{{ route('admin/app/client_product_info', ['client_id' => $client_id]) }}">
                        <div class="fbutton">
                            <div class="add" title="{{ lang('admin/app.add_product') }}"><span><i
                                            class="fa fa-plus"></i>{{ lang('admin/app.add_product') }}</span></div>
                        </div>
                    </a>
                </div>
                <div class="fl ml10">
                    <a href="{{ route('admin/app/client_product', ['client_id' => $client_id]) }}" class="fancybox fancybox.iframe">
                        <div class="fbutton">
                            <div class="edit" title="{{ lang('admin/app.edit_client') }}">{{ lang('admin/app.edit_client') }}</span></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-2 fr">
                    <select name="client_id"  onchange="jumpTo(this)" class="form-control input-sm">
                        <option value="">{{ lang('admin/app.client_manage_name') }}</option>
                        @forelse ($client_list as $k => $row)
                        <option value="{{ route('admin/app/client_product_list', ['client_id' => $row['id']]) }}">{{ $row['name'] }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <thead>
                        <tr>
                            <th width="10%">
                                <div class="tDiv">{{ lang('admin/app.version_code') }}</div>
                            </th>
                            <th width="30%">
                                <div class="tDiv">{{ lang('admin/app.update_desc') }}</div>
                            </th>
                            <th width="10%">
                                <div class="tDiv">{{ lang('admin/app.update_time') }}</div>
                            </th>
                            <th width="20%">
                                <div class="tDiv">{{ lang('admin/app.download_url') }}</div>
                            </th>
                            <th width="10%">
                                <div class="tDiv">{{ lang('admin/app.is_show') }}</div>
                            </th>
                            <th width="20%" class="handle">{{ lang('admin/app.handler') }}</th>
                        </tr>
                        </thead>
                            <tbody>
                            @forelse ($client_product_list as $key => $list)

                                <tr>
                                    <td>
                                        <div class="tDiv">{{ $list['version_id'] }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $list['update_desc'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $list['update_time'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">{{ $list['download_url'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="tDiv">
                                            <div class="switch fl ml10 @if($list['is_show']) active @endif"
                                             onclick="listTable.switchBt(this, 'change_show', {{ $list['id'] }})">
                                                <div class="circle"></div>
                                            </div>
                                            <input type="hidden" value="0" name="">
                                        </div>
                                    </td>
                                    <td class="handle">
                                        <div class="tDiv a3">
                                            <a href="{{ route('admin/app/client_product_info', ['product_id' => $list['id'], 'client_id' => $list['client_id']]) }}" title="{{ lang('admin/app.edit') }}" class="btn_edit"><i class="fa fa-edit"></i>{{ lang('admin/app.edit') }}</a>
                                            <a href="javascript:;" title="{{ lang('admin/app.delete') }}" class="btn_trash delete-p-info" data-href="{{ route('admin/app/del_client_product', ['product_id' => $list['id'], 'client_id' => $list['client_id']]) }}"><i class="fa fa-trash-o"></i>{{ lang('admin/app.delete') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty

                            <tr>
                                <td class="no-records" colspan="6">{{ lang('admin/app.no_records') }}</td>
                            </tr>

                            @endforelse

                            </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="6">
                                @include('admin.base.pageview')
                            </td>
                        </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    $(function () {
        //弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '60%',
            closeBtn: true,
            title: ''
        });
    });

    function jumpTo(obj)
    {
        var url = $(obj).val(); 
        if (url) {
            window.location.href = url;
        }
    }


    // 删除客户端产品
    $(".delete-p-info").click(function () {
        var url = $(this).attr("data-href");
        //询问框
        layer.confirm('{{ lang('admin/common.confirm_delete') }}', {
            btn: ['{{ lang('admin/common.ok') }}', '{{ lang('admin/common.cancel') }}'] //按钮
        }, function () {
            $.post(url, '', function (data) {
                layer.msg(data.msg);
                if (data.error == 0) {
                    if (data.url) {
                        window.location.href = data.url;
                    } else {
                        window.location.reload();
                    }
                }
                return false;
            }, 'json');
        });
    });
</script>
@include('admin.base.footer')
