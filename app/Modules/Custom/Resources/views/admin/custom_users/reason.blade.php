@extends('custom::admin.layout')

@section('title', $page_title ?? lang('custom::admin/custom.user_logout'))

@push('scripts')
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

@endpush

@section('content')
<div class="wrapper">

    <div class="title">会员注销 - 注销原因</div>
    <div class="content_tips">
        <div class="tabs_info">
            <ul>
                <li>
                    <a href="{{ route('admin/custom/users/index') }}">文章配置</a>
                </li>
                <li class="curr">
                    <a href="{{ route('admin/custom/users/reason') }}">原因列表</a>
                </li>
                <li>
                    <a href="{{ route('admin/custom/users/logout') }}">注销用户</a>
                </li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="fl">
                    <a href="{{ route('admin/custom/users/reason_edit') }}" class="">
                        <div class="fbutton"><div class="add "><span><i class="fa fa-plus"></i>添加原因</span></div></div>
                    </a>
                </div>
            </div>
            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('custom::admin.library.reason_query')
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

        // 删除分享码
        $(document).on('click', ".js-delete-share", function(){
            var url = $(this).attr("data-href");
            //询问框
            layer.confirm('确定要删除吗？', {
                btn: ['{{ lang('admin/common.ok') }}', '{{ lang('admin/common.cancel')}}'] //按钮
            }, function(){
                $.post(url, '', function(data){
                    layer.msg(data.msg);
                    if (data.error == 0 ) {
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
