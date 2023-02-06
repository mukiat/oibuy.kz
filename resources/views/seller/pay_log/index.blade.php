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
                {{--当前位置--}}
                <div class="ecsc-path"><span>{{ __('admin/common.order_word') }} - {{ __('admin/pay_log.pay_log_list') }}</span></div>

                <div class="wrapper-right of">
                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                        <ul>
                            @foreach(__('admin/pay_log.pay_log_list_tips') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="common-head mt20">

                        <div class="search-info">
                            <form action="javascript:search();" method="post" name="searchForm">
                                <div class="select_w140 imitate_select ">
                                    <div class="cite">
                                        {{ __('admin/common.please_select') }}
                                    </div>
                                    <ul>
                                        <li><a href="javascript:;" data-value="0">{{ __('admin/pay_log.select_payment') }}</a></li>
                                        @foreach($payment_list as $key => $val)
                                            <li><a href="javascript:;" data-value="{{ $val['pay_id'] }}">{{ $val['pay_name'] ?? '' }}</a></li>
                                        @endforeach
                                    </ul>
                                    <input name="pay_id" type="hidden" value="{{ $filter['pay_id'] ?? 0 }}">
                                </div>
                                <div class="search-form">
                                    <div class="search-key">
                                        @csrf
                                        <input type="text" name="keywords" class="text nofocus" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('admin/pay_log.search_pay_log') }}" autocomplete="off">
                                        <input type="submit" value="" class="submit search_button">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="wrapper-list mt20">
                        <div class="list-div" id="listDiv">
                            @include('seller.pay_log.library.index_query')
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
        listTable.filter['pay_id'] = Utils.trim(frm.elements['pay_id'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    /**
     * 刷新
     */
    function refresh()
    {
        listTable.filter['keywords'] = '';
        listTable.filter['pay_id'] = 0;
        listTable.filter['page'] = 1;
        listTable.loadList();
    }


    $(function () {

        // fancybox 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '50%',
            closeBtn: true,
            title: ''
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