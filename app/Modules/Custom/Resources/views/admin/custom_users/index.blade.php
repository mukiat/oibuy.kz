@extends('custom::admin.layout')

@section('title', $page_title ?? lang('custom::admin/custom.user_logout'))

@push('scripts')
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

@endpush

@section('content')
<div class="wrapper">

    <div class="title">会员注销 - 文章配置</div>
    <div class="content_tips">
        <div class="tabs_info">
            <ul>
                <li class="curr">
                    <a href="{{ route('admin/custom/users/index') }}">文章配置</a>
                </li>
                <li>
                    <a href="{{ route('admin/custom/users/reason') }}">原因列表</a>
                </li>
                <li>
                    <a href="{{ route('admin/custom/users/logout') }}">注销用户</a>
                </li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="main-info">
                <form method="post" action="{{ route('admin/custom/users/index') }}" class="form-horizontal" role="form">
                    <div class="switch_info">
                        <div class="item">
                            <div class="label-t"> 注销文章：</div>
                            <div class="label_value col-md-4">
                                <select name="article_id" id="article_id" class="form-control input-sm w300">
                                    <option value="0">请选择</option>
                                    @if(!empty($article))
                                        @foreach($article as $row)
                                            <option value="{{$row['article_id']}}"
                                            @if ($article_id == $row['article_id'])
                                                selected
                                            @endif
                                                >{{ $row['title'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn">
                                @csrf
                                <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}"/>
                                <input type="submit" name="submit" value="{{ lang('admin/common.button_save') }}" class="button btn-danger bg-red"/>
                            </div>
                        </div>
                    </div>
                </form>
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

        // 删除直播间
        $(document).on('click', ".js-delete-room", function(){
            var url = $(this).attr("data-href");
            //询问框
            layer.confirm('{{ lang('wxapp::admin/wxapp.confirm_delete_room') }}', {
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

        // 获取分享海报
        $('.js-shared-code').on('click', function() {
            var url = $(this).attr("data-href");

            layer.load(2);

            $.post(url, function(res){
                layer.closeAll('loading');

                if (res.data === false) {
                    layer.msg('分享海报获取失败，请稍后再试');
                    return false
                }

                layer.open({
                    type: 1,
                    title: false,
                    skin: 'layui-layer-rim', //加上边框
                    area: ['600px', '800px'],
                    shadeClose: true, //开启遮罩关闭
                    content: '<img src="'+ res.data +'" width="100%" />'
                });
            }, 'json');
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
