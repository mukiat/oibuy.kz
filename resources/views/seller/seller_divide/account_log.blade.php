@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

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

    .table-responsive .account_info { height: 100px; font-size: 16px;}
    .table-responsive .account_info p{ line-height: 40px;}
    .table-responsive .border0 { border:0;}
    .table-responsive .border_left { border-left:1px solid #eee}
    .table-responsive .border_right { border-right:1px solid #eee}
    .pl80 {padding-left: 80px !important;}
</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                {{--当前位置--}}
                <div class="ecsc-path"><span>{{ __('admin/common.seller') }} - {{ __('admin/seller_divide.seller_account') }}</span></div>

                <div class="tabmenu">
                    <ul class="tab">
                        <li class="active"><a href="{{ route('seller/seller_divide/account_log', ['divide_channel' => $filter['divide_channel']]) }}">{{ __('admin/seller_divide.seller_divide') }}</a></li>
                    </ul>
                </div>

                <div class="wrapper-right of">
                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                        <ul>
                            @foreach(__('admin/seller_divide.seller_account_tips') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="table-responsive mt20">
                        <div class="fr mb20">
                            <a class="sc-btn sc-blue-btn  js_account_amount_query" href="javascript:;" ><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.account_amount_query') }}</a>
                        </div>

                        <table class="table">
                            <tr>
                                <td class="pl80 border0" >
                                    <div class="account_info border_right">
                                        <p class="font16">{{ __('admin/seller_divide.seller_sub_mchid') }}</p>
                                        <p class="font18 color-42">{{ $amount_info['sub_mchid'] ?? '' }}</p>
                                    </div>
                                </td>
                                <td class="pl80 border0" >
                                    <div class="account_info border_right">
                                        <p class="font16">{{ __('admin/seller_divide.available_amount') }} ({{ __('admin/common.yuan') }}) <i class="fa fa-question-circle" aria-hidden="true" id="available_amount_tips"></i></p>
                                        <p class="font18 color-42">{{ $amount_info['available_amount'] ?? 0 }}</p>
                                        <p><a class="font12 color-289" href="{{ route('seller/seller_divide/account_log_apply', ['divide_channel' => $filter['divide_channel']]) }}">{{ __('admin/seller_divide.business_type_0') }}</a></p>
                                    </div>
                                </td>
                                <td class="pl80 border0" >
                                    <div class="account_info">
                                        <p class="font16">{{ __('admin/seller_divide.pending_amount') }} ({{ __('admin/common.yuan') }}) <i class="fa fa-question-circle" aria-hidden="true" id="pending_amount_tips"></i></p>
                                        <p class="font18 color-42">{{ $amount_info['pending_amount'] ?? 0 }}</p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="common-head mt20">
                        {{--同步提现状态--}}
                        <div class="fl">
                            <a href="{{ route('seller/seller_divide/sync_account_log', ['divide_channel' => $filter['divide_channel']]) }}" class="sc-btn sc-red-btn  fancybox_sync_account fancybox.iframe"><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.sync_account_log') }}
                            </a>
                        </div>

                        <!-- 搜索 -->
                        <div class="search-info">
                            <form action="javascript:search();" method="post" name="searchForm">
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

                                <div class="search-form">
                                    <div class="search-key">
                                        <input type="text" name="keywords" class="text nofocus" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('admin/seller_divide.account_log_search') }}" autocomplete="off">
                                        @csrf
                                        <input type="submit" value="" class="submit search_button">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="wrapper-list mt20">
                        <div class="list-div" id="listDiv">

                            @include('seller.seller_divide.library.account_log_query')

                        </div>
                    </div>

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

        // 可用余额提示
        var available_amount_tips;
        $(document).on('mouseenter', '#available_amount_tips', function () {
            availableAmountLayerTips(this);
        }).on('mouseleave', '#available_amount_tips', function () {
            availableAmountLayerTips(this, 1);
        });

        var availableAmountLayerTips = function (obj, close) {
            if (!close) {
                var html = '{{ __('admin/seller_divide.available_amount_tips') }}';
                available_amount_tips = layer.tips(html, obj, {
                    tipsMore: true,
                    time: 0,
                    tips:[3,'#707070'],
                    area: ['200px'], // tips 宽度200px 高度自动
                });
            } else {
                layer.close(available_amount_tips);
            }
        }


        // 不可用余额提示
        var pending_amount_tips;
        $(document).on('mouseenter', '#pending_amount_tips', function () {
            pendingAmountLayerTips(this);
        }).on('mouseleave', '#pending_amount_tips', function () {
            pendingAmountLayerTips(this, 1);
        });

        var pendingAmountLayerTips = function (obj, close) {
            if (!close) {
                var html = '{{ __('admin/seller_divide.pending_amount_tips') }}';
                pending_amount_tips = layer.tips(html, obj, {
                    tipsMore: true,
                    time: 0,
                    tips:[3,'#707070'],
                    area: ['200px'], // tips 宽度200px 高度自动
                });
            } else {
                layer.close(pending_amount_tips);
            }
        }

        // 拒绝原因提示
        $(document).on('mouseenter', '.status-tips', function () {
            layerTips(this);
        }).on('mouseleave', '.status-tips', function () {
            layerTips(this, 1);
        });

        // 提示层
        var tip_index;
        var url = '{{ route('seller/seller_divide/account_log_detail') }}';

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


        // 实时余额查询
        $(document).on("click", ".js_account_amount_query", function() {
            var url = '{{ route('seller/seller_divide/account_log_query') }}';

            var divide_channel = "{{ $filter['divide_channel'] ?? 1 }}";
            var sub_mchid = "{{ $amount_info['sub_mchid'] ?? '' }}";

            $.post(url, {divide_channel: divide_channel, sub_mchid: sub_mchid}, function (data) {
                layer.msg(data.msg);
                setTimeout(function(){
                    window.location.reload();
                }, 1000);
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
@include('seller.base.seller_pagefooter')