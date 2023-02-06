@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="warpper">
    <div class="title">{{ __('admin/seller_divide.merchants_base_info') }} - {{ __('admin/seller_divide.seller_divide_list') }}</div>
    <div class="content">
        <div class="tabs_info">
            <ul>
                <li class="curr"><a href="{{ route('admin/seller_divide/index', ['divide_channel' => $filter['divide_channel'] ?? 1]) }}">{{ __('admin/seller_divide.seller_divide') }}</a></li>
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
                    <li class="curr"><a href="{{ route('admin/seller_divide/index') }}">{{ __('admin/seller_divide.seller_divide_list') }}</a></li>
                    <li><a href="{{ route('admin/seller_divide/apply_list') }}">{{ __('admin/seller_divide.seller_divide_apply_list') }}</a></li>
                </ul>
            </div>

            <div class="common-head">
                <div class="search">
                    <form action="javascript:search();" method="post" name="searchForm">
                    <div class="input">
                        @csrf
                        <input type="text" name="keywords" class="text nofocus" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('search.search_store') }}" autocomplete="off">
                        <input type="submit" value="" class="btn" style="font-style:normal">
                    </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">

                    @include('admin.seller_divide.library.index_query')

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

        // 删除
        $(document).on("click", ".js-delete", function() {
            var url = $(this).attr("data-href");

            //询问框
            layer.confirm('{{  __('admin/common.confirm_delete') }} ', {
                btn: ['{{ __('admin/common.ok') }}', '{{ __('admin/common.cancel') }}'] //按钮
            }, function () {
                $.post(url, '', function (data) {
                    layer.msg(data.msg);
                    refresh();
                    return false;
                }, 'json');
            });

        });

    });
</script>
@include('admin.base.footer')