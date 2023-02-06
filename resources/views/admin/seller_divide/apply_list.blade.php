@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="warpper">
    <div class="title">{{ __('admin/seller_divide.merchants_base_info') }} - {{ __('admin/seller_divide.seller_divide_apply_list') }}</div>
    <div class="content">
        <div class="tabs_info">
            <ul>
                <li class="curr"><a href="{{ route('admin/seller_divide/apply_list', ['divide_channel' => $filter['divide_channel'] ?? 1]) }}">{{ __('admin/seller_divide.seller_divide') }}</a></li>
            </ul>
        </div>

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(__('admin/seller_divide.seller_divide_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">
            <div class="tabs_info mt10">
                <ul>
                    <li ><a href="{{ route('admin/seller_divide/index') }}">{{ __('admin/seller_divide.seller_divide_list') }}</a></li>
                    <li class="curr"><a href="{{ route('admin/seller_divide/apply_list') }}">{{ __('admin/seller_divide.seller_divide_apply_list') }}</a></li>
                </ul>
            </div>

            <div class="common-head">
                <div class="fl">
                    <a href="{{ route('admin/seller_divide/sync_apply') }}" class="fancybox fancybox.iframe">
                        <div class="fbutton"><div class="add "><span><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.sync_apply') }}</span></div></div>
                    </a>
                </div>

                <div class="search">
                    <form action="javascript:search();" method="post" name="searchForm">
                    <div class="input">
                        @csrf
                        <input type="text" name="keywords" class="text nofocus" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('search.search_store') }}" autocomplete="off">
                        <input type="text" name="trade_keywords" class="text nofocus w200" value="{{ $filter['trade_keywords'] ?? '' }}" placeholder="{{ __('admin/seller_divide.search_apply_keywords') }}" autocomplete="off">
                        <input type="submit" value="" class="btn" style="font-style:normal">
                    </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">

                    @include('admin.seller_divide.library.apply_list_query')

                </div>

            </div>
        </div>

    </div>

</div>

<script type="text/javascript">

    // 筛选 排序
    listTable.recordCount = '{{ $page['count'] ?? 0 }}';// 总共记录数
    listTable.pageCount = '{{ $page['page_count'] ?? 1 }}';// 总共几页

    @if (isset($filter) && !empty($filter))

    @foreach($filter as $key => $item)
        listTable.filter.{{ $key }} = '{{ $item }}';
    @endforeach

    @endif

    /**
     * 搜索
     */
    function search()
    {
        var frm = document.forms['searchForm'];
        listTable.filter['keywords'] = Utils.trim(frm.elements['keywords'].value);
        listTable.filter['trade_keywords'] = Utils.trim(frm.elements['trade_keywords'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    /**
     * 刷新
     */
    function refresh()
    {
        listTable.filter['page'] = 1;
        listTable.loadList();
    }


    $(function () {

        // 拒绝原因点击提示
        $(document).on('click', '.status-tips', function () {
            layerTips(this);
        });

        // 提示层
        var tip_index;
        var url = '{{ route('admin/seller_divide/apply_detail') }}';

        var layerTips = function (obj, close) {
            if (!close) {
                var id = $(obj).attr('data-id');

                var append = '';
                $.post(url, {id:id, field:'audit_detail'}, function(data){
                    if (data.status == 1 && data.content != '') {

                        var audit_detail = data.content.audit_detail;

                        var len = audit_detail.length;
                        for(var j = 0; j < len; j++) {
                            var param_name = audit_detail[j]['param_name'];
                            var reject_reason = audit_detail[j]['reject_reason'];
                            append += '<div class="item" >' + '<span class="label-t">' + param_name + '：</span>' +
                                    '<span class="label_value">' + reject_reason + '</span>' + '</div>';
                        }
                        var html = '<div class="check-tips ">' + append + '</div>';

                        tip_index = layer.tips(html, obj, {
                            tipsMore: true,
                            time: 5000,
                            tips:[3,'#707070'],
                            area: ['500px'], // tips 宽度500px 高度自动
                        });
                    }
                    return false;
                }, 'json');
            } else {
                //layer.closeAll();
                layer.close(tip_index);
            }
        }


        // 更新申请状态弹出框
        $(".fancybox").fancybox({
            afterClose : function() {
                console.log('Closed!');
                window.location.reload(); // 弹窗关闭 重新加载页面
            },
            width		: '80%',
            height		: '30%',
            closeBtn	: true,
            closeClick  : false, // 禁止通过点击背景关闭窗口
            title       : '',
            helpers     : {
                overlay : {closeClick: false} // prevents closing when clicking OUTSIDE fancybox
            }
        });

    });
</script>
@include('admin.base.footer')