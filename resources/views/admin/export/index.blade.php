@include('admin.base.header')
<div class="warpper">
    <div class="title">
        <a href="{{ $callback ?? '' }}" class="s-back">Back</a>
        {{ __('admin/order_export.title.' . $type) }} - {{ __('admin/order_export.title.' . $type) }}{{ __('admin/order_export.sub_title') }}</div>

    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="Tip"></span>
            </div>
            <ul>
                @foreach(__('admin/order_export.tip_content') as $item)
                <li>{!! $item !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="fl pr10">
                    <a href="javascript:window.location.reload();" class="btn_trash js-sync-transfer">
                        <div class="fbutton">
                            <div class="add "><span><i class="fa fa-refresh"></i>
                                {{ __('admin/order_export.refresh') }}
                                </span></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <thead>
                        <tr>
                            <th>
                                <div class="tDiv">{{ __('admin/order_export.file_name') }}</div>
                            </th>
                            <th width="15%">
                                <div class="tDiv">{{ __('admin/order_export.file_type') }}</div>
                            </th>
                            <th width="20%">
                                <div class="tDiv">{{ __('admin/order_export.file_status') }}</div>
                            </th>
                            <th width="20%">
                                <div class="tDiv">{{ __('admin/order_export.created_at') }}</div>
                            </th>
                            <th width="20%">
                                <div class="tDiv text-center">{{ __('admin/order_export.handle') }}</div>
                            </th>
                        </tr>
                        </thead>

                        <tbody>
                        @if(!$export_history->isEmpty())
                        @foreach($export_history as $item)
                            <tr>
                                <td>
                                    <div class="tDiv">{{ $item->file_name }}</div>
                                </td>
                                <td>
                                    <div class="tDiv">{{ $item->file_type }}</div>
                                </td>
                                <td>
                                    <div class="tDiv">
                                        @if($item->deleted_at)
                                            {{ __('admin/order_export.file_expired') }}
                                        @elseif($item->updated_at)
                                            <span style="color: green;">
                                                {{ __('admin/order_export.file_generated') }}
                                            </span>
                                        @else
                                            {{ __('admin/order_export.file_in_generated') }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="tDiv">{{ $item->created_at }}</div>
                                </td>
                                <td class="handle text-center">
                                    <div class="tDiv">
                                        @if($item->deleted_at)
                                            -
                                        @elseif($item->updated_at)
                                            <a href="{{ route('admin/export_history/download', ['id' => $item->id]) }}"
                                               onclick="event.preventDefault();
                                                   document.getElementById('history-download-form-{{ $item->id }}').submit();">
                                                <i class="fa fa-download"></i>{{ __('admin/order_export.download') }}
                                            </a>

                                            <a href="javascript:;" data-href="{{ route('admin/export_history/delete', ['id' => $item->id]) }}" class="btn_trash history-delete"><i class="fa fa-trash-o"></i>{{ __('admin/common.delete') }}</a>

                                            <form method="post" id="history-download-form-{{ $item->id }}"
                                                  action="{{ route('admin/export_history/download', ['id' => $item->id]) }}">
                                                @csrf
                                            </form>

                                        @else
                                            -
                                        @endif
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
                    </table>

                    <div style="text-align: center; margin-top: 12px">
                        {{ $export_history->appends(['type' => request()->get('type'), 'callback' => request()->get('callback')])->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script type="text/javascript">

    $(function () {

        // 删除
        $(document).on("click", ".history-delete", function() {
            var url = $(this).attr("data-href");

            $.post(url, '', function (data) {
                layer.msg(data.msg);
                window.location.reload();
                return false;
            }, 'json');

        });

    });
</script>

@include('admin.base.footer')
