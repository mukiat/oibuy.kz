@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>
<script src="{{ asset('assets/mobile/vendor/common/imitate_select.js') }}" type="text/javascript"></script>

<style>
    /*div+js模仿select效果*/
    .imitate_select{ float: left; position:relative;border: 1px solid #dbdbdb;border-radius: 2px;height: 32px; margin-right:10px;font-size: 12px;}
    .imitate_select .cite{ background: #fff url({{ asset('assets/admin/images/xjt.png') }}) right 11px no-repeat; padding: 0 10px; cursor:pointer;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left;}
    .imitate_select ul{ position:absolute; top:28px; left:-1px; background:#fff; width:100%; border:1px solid #dbdbdb; border-radius:0 0 3px 3px; display:none; z-index:199; max-height:280px;overflow: auto;}
    .imitate_select ul li{ padding:0 10px; cursor:pointer;}
    .imitate_select ul li:hover{ background:#f5faff;}
    .imitate_select ul li a{ display:block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left; color:#707070;}
    /*div+js模仿select效果end*/
</style>

<div class="warpper">
    <div class="title">{{ __('admin/common.seller') }} - {{ __('admin/seller_divide.seller_account') }}</div>
    <div class="content">
        <div class="tabs_info">
            <ul>
                <li class="curr"><a href="{{ route('admin/seller_divide/account_log', ['divide_channel' => $filter['divide_channel']]) }}">{{ __('admin/seller_divide.seller_divide') }}</a></li>
            </ul>
        </div>

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(__('admin/seller_divide.seller_account_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">
            <div class="tabs_info mt10">
                <ul>
                    <li @if(isset($filter['audit'])&& $filter['audit'] == -1) class="curr" @endif><a href="{{ route('admin/seller_divide/account_log') }}">{{ __('admin/seller_divide.account_list') }}</a></li>
                    <li @if(isset($filter['audit'])&& $filter['audit'] == 0) class="curr" @endif><a href="{{ route('admin/seller_divide/account_log', ['audit' => 0]) }}">{{ __('admin/seller_divide.account_log_audit') }}</a></li>
                </ul>
            </div>

            <div class="common-head">
                <div class="fl">
                    {{--同步提现状态--}}
                    <a href="{{ route('admin/seller_divide/sync_account_log', ['ru_id' => $ru_id, 'divide_channel' => $filter['divide_channel']]) }}" class="fancybox_sync_account fancybox.iframe">
                        <div class="fbutton"><div class="add "><span><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.sync_account_log') }}</span></div></div>
                    </a>
                </div>
                <div class="refresh">
                    <div class="refresh_tit"><a href="javascript:refresh();" ><i class="fa fa-refresh"></i></a> </div>
                    <div class="refresh_span">{{ __('admin/common.refresh_common') }} {{ $page['count'] ?? 0 }} {{ __('admin/common.record') }}</div>
                </div>

                <div class="search">
                    <form action="javascript:search();" method="post" name="searchForm">
                        <div class="input mr10">
                        <input type="text" name="search_shop_name" class="text nofocus mr0" value="{{ $filter['shop_name'] ?? '' }}" placeholder="{{ __('admin/seller_divide.shop_name') }}" autocomplete="off">
                        </div>
                        <div class="select_w140 imitate_select ">
                            <div class="cite">
                                {{ __('admin/common.please_select') }}{{ __('admin/seller_divide.status') }}
                            </div>
                            <ul>
                                <li><a href="javascript:;" data-value="">{{ __('admin/common.please_select') }}{{ __('admin/seller_divide.status') }}</a></li>
                                <li><a href="javascript:;" data-value="CREATE_SUCCESS">{{ __('admin/seller_divide.status_0') }}</a></li>
                                <li><a href="javascript:;" data-value="SUCCESS">{{ __('admin/seller_divide.status_1') }}</a></li>
                                <li><a href="javascript:;" data-value="FAIL">{{ __('admin/seller_divide.status_2') }}</a></li>
                                <li><a href="javascript:;" data-value="REFUND">{{ __('admin/seller_divide.status_3') }}</a></li>
                                <li><a href="javascript:;" data-value="CLOSE">{{ __('admin/seller_divide.status_4') }}</a></li>
                                <li><a href="javascript:;" data-value="INIT">{{ __('admin/seller_divide.status_5') }}</a></li>
                            </ul>
                            <input name="status" type="hidden" value="">
                        </div>
                        <div class="input">
                            <input type="text" name="keywords" class="text nofocus" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('admin/seller_divide.account_log_search') }}" autocomplete="off">
                            @csrf
                            <input type="submit" value="" class="btn" style="font-style:normal">
                        </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">

                    @include('admin.seller_divide.library.account_log_query')

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
        listTable.filter['search_shop_name'] = Utils.trim(frm.elements['search_shop_name'].value);
        listTable.filter['keywords'] = Utils.trim(frm.elements['keywords'].value);
        listTable.filter['status'] = Utils.trim(frm.elements['status'].value);
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

        // 拒绝原因提示
        $(document).on('mouseenter', '.status-tips', function () {
            layerTips(this);
        }).on('mouseleave', '.status-tips', function () {
            layerTips(this, 1);
        });

        // 提示层
        var tip_index;
        var url = '{{ route('admin/seller_divide/account_log_detail') }}';

        var layerTips = function (obj, close) {
            if (!close) {
                var id = $(obj).attr('data-id');

                var append = '';
                $.post(url, {id:id, field:'reason'}, function(data){
                    if (data.status == 1 && data.content != '') {

                        var reason = data.content.reason;

                        append = '<div class="item" ><span class="label_value">' + reason + '</span>' + '</div>';

                        var html = '<div class="check-tips ">' + append + '</div>';

                        tip_index = layer.tips(html, obj, {
                            tipsMore: true,
                            time: 0,
                            tips:[3,'#707070'],
                            area: ['200px'], // tips 宽度200px 高度自动
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
        $(".fancybox_sync_account").fancybox({
            afterClose: function() {
                console.log('Closed!');
                window.location.reload(); // 弹窗关闭 重新加载页面
            },
            width: '80%',
            height: '30%',
            closeBtn: true,
            closeClick: false, // 禁止通过点击背景关闭窗口
            title: '',
            helpers: {
                overlay : {closeClick: false} // prevents closing when clicking OUTSIDE fancybox
            }
        });

        // 查看详情 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: 'auto',
            closeBtn: true,
            title: ''
        });

        // 重试
        $(document).on("click", ".js-retry", function() {
            var url = $(this).attr("data-href");

            $.post(url, '', function (data) {
                layer.msg(data.msg);
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