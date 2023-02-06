@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>
<script src="{{ asset('assets/mobile/vendor/common/imitate_select.js') }}" type="text/javascript"></script>

<style>
    /*div+js模仿select效果*/
    .imitate_select{ float: left; position:relative;border: 1px solid #dbdbdb;border-radius: 2px;height: 32px;line-height: 30px; margin-right:10px;font-size: 12px;}
    .imitate_select .cite{ background: #fff url({{ asset('assets/admin/images/xjt.png') }}) right 11px no-repeat; padding: 0 10px; cursor:pointer;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left;}
    .imitate_select ul{ position:absolute; top:28px; left:-1px; background:#fff; width:100%; border:1px solid #dbdbdb; border-radius:0 0 3px 3px; display:none; z-index:199; max-height:280px;}
    .imitate_select ul li{ padding:0 10px; cursor:pointer;}
    .imitate_select ul li:hover{ background:#f5faff;}
    .imitate_select ul li a{ display:block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left; color:#707070;}

    .imitate_select ul li.li_not{ text-align:center;padding: 20px 10px;}
    .imitate_select .upward{ top:inherit; bottom:28px; border-radius:3px 3px 0 0;}
    /*div+js模仿select效果end*/

</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                <!-- 当前位置 -->
                <div class="ecsc-path"><span>{{ __('admin/seller_divide.merchants_base_info') }} - {{ __('admin/seller_divide.seller_divide_apply_list') }}</span></div>

                <div class="tabmenu">
                    <ul class="tab">
                        <li class="active"><a href="{{ route('seller/seller_divide/account_log', ['divide_channel' => $filter['divide_channel']]) }}">{{ __('admin/seller_divide.seller_divide') }}</a></li>
                    </ul>
                </div>

                <div class="wrapper-right of">

                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                        <ul>
                            @foreach(__('admin/seller_divide.seller_divide_tips') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="common-head mt20">

                        <div class="order_state_tab">
                            <a href="{{ route('seller/seller_divide/index') }}" >{{ trans('admin/seller_divide.seller_divide_list') }}</a>
                            <a href="{{ route('seller/seller_divide/apply_list') }}" class="current" >{{ trans('admin/seller_divide.seller_divide_apply_list') }}</a>
                        </div>

                        <div class="fl">
                            <a href="{{ route('seller/seller_divide/sync_apply') }}" class="sc-btn sc-blue-btn fancybox fancybox.iframe"><i class="fa fa-refresh"></i>{{ __('admin/seller_divide.sync_apply') }}</a>
                        </div>

                        <!-- 搜索 -->
                        <div class="search-info">
                            <form action="javascript:search();" method="post" name="searchForm">
                                <div id="status" class="imitate_select select_w145">
                                    <div class="cite">{{ __('admin/seller_divide.divide_channel') }}</div>
                                    <ul>
                                        <li><a href="javascript:;" data-value="1" class="ftx-01">{{ __('admin/seller_divide.divide_channel_1') }}</a></li>
                                    </ul>
                                    <input name="divide_channel" type="hidden" value="0"/>
                                </div>

                                <div class="search-form">
                                    <div class="search-key">
                                        @csrf
                                        <input type="text" name="trade_keywords" class="text nofocus w200" value="{{ $filter['trade_keywords'] ?? '' }}" placeholder="{{ __('admin/seller_divide.search_apply_keywords') }}" autocomplete="off">
                                        <input type="submit" value="" class="submit search_button">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="wrapper-list mt20">
                        <div class="list-div" id="listDiv">

                            @include('seller.seller_divide.library.apply_list_query')

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
        listTable.filter['divide_channel'] = Utils.trim(frm.elements['divide_channel'].value);
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

        // 拒绝原因提示
        $(document).on('mouseenter', '.status-tips', function () {
            layerTips(this);
        }).on('mouseleave', '.status-tips', function () {
            layerTips(this, 1);
        });

        // 提示层
        var tip_index;
        var url = '{{ route('seller/seller_divide/apply_detail') }}';

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
                            time: 0,
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
@include('seller.base.seller_pagefooter')