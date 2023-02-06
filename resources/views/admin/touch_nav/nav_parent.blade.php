@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="warpper">
    <div class="title">{{ __('admin/touch_nav.menu_' . $device) }} - {{ __('admin/touch_nav.touch_nav_title') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                <li>{!! __('admin/touch_nav.touch_nav_tips_' . $device) !!}</li>
                @foreach(__('admin/touch_nav.touch_nav_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="tabs_info mt10">
            <ul>
                <li class=""><a href="{{ route('admin/touch_nav/index', ['device' => $device]) }}">{{ __('admin/touch_nav.touch_nav_list') }}</a></li>
                <li class="curr"><a href="{{ route('admin/touch_nav/nav_parent', ['device' => $device]) }}">{{ __('admin/touch_nav.touch_nav_parent') }}</a></li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="fl">
                    <a href="{{ route('admin/touch_nav/nav_parent_edit', ['device' => $device]) }}" class="btn_trash fancybox fancybox.iframe">
                        <div class="fbutton"><div class="add "><span><i class="fa fa-plus"></i>{{ __('admin/touch_nav.add_touch_nav_parent') }}</span></div></div>
                    </a>
                </div>

                <div class="refresh">
                    <div class="refresh_tit"><a href="javascript:refresh();" ><i class="fa fa-refresh"></i></a> </div>
                    <div class="refresh_span">{{ __('admin/common.refresh_common') }} {{ $page['count'] ?? 0 }} {{ __('admin/common.record') }}</div>
                </div>

            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">

                    @include('admin.touch_nav.library.nav_parent_query')

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

    // 修改排序
    function update_vieworder(val, id)
    {
        if (!id) {
            return false;
        }

        var url = "{{ route('admin/touch_nav/update') }}";

        $.post(url, {id:id, vieworder:val}, function (data) {
            refresh();
            return false;
        }, 'json');
    }

    // 切换显示
    function toggle_is_show(id, ifshow)
    {
        if (!id) {
            return false;
        }

        var url = "{{ route('admin/touch_nav/update') }}";

        $.post(url, {id:id, ifshow:ifshow}, function (data) {
            refresh();
            return false;
        }, 'json');
    }

    $(function () {

        // fancybox 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '50%',
            closeBtn: true,
            title: ''
        });

        // 显示
        $(document).on("click", ".js-show", function () {
            var url = $(this).attr("data-href");

            $.post(url, '', function (data) {
                refresh();
                return false;
            }, 'json');

        });

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