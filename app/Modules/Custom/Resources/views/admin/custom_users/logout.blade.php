@extends('custom::admin.layout')

@section('title', $page_title ?? lang('custom::admin/custom.user_logout'))

@push('scripts')
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

@endpush

@section('content')
<div class="wrapper">

    <div class="title">会员注销 - 注销用户</div>
    <div class="content_tips">
        <div class="tabs_info">
            <ul>
                <li>
                    <a href="{{ route('admin/custom/users/index') }}">文章配置</a>
                </li>
                <li>
                    <a href="{{ route('admin/custom/users/reason') }}">原因列表</a>
                </li>
                <li class="curr">
                    <a href="{{ route('admin/custom/users/logout') }}">注销用户</a>
                </li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('custom::admin.library.logout_query')
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('footer_scripts')
<script type="text/javascript">

    // 筛选 排序 搜索
    listTable.recordCount = '{{ $page['count'] ?? 0 }}';// 总共记录数
    listTable.pageCount = '{{ $page['page_count'] ?? 1 }}';// 总共几页

    @if(!empty($filter))

    @foreach($filter as $key => $item)
        listTable.filter.{{ $key }} = '{{ $item }}';
    @endforeach

    @endif

    /**
     * 搜索
     */
    function search()
    {
        listTable.filter['search_keywords'] = Utils.trim(document.forms['searchForm'].elements['search_keywords'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    $(function () {
        // 弹出框
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
@endpush
