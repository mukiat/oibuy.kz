@include('admin.base.header')

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

<div class="warpper">
    <div class="title">{{ __('admin/common.order_word') }} - {{ __('admin/pay_log.pay_log_list') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(__('admin/pay_log.pay_log_list_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">

            <div class="common-head">
                <div class="refresh m0">
                    <div class="refresh_tit"><a href="javascript:refresh();" ><i class="fa fa-refresh"></i></a> </div>
                    <div class="refresh_span">{{ __('admin/common.refresh_common') }} {{ $page['count'] ?? 0 }} {{ __('admin/common.record') }}</div>
                </div>

                <div class="search">
                    <form action="javascript:search();" method="post" name="searchForm">
                        <div class="select_w140 imitate_select ">
                            <div class="cite">
                                {{ __('admin/common.please_select') }}
                            </div>
                            <ul>
                                <li><a href="javascript:;" data-value="-1">{{ __('admin/pay_log.select_all') }}</a></li>
                                @if(!empty($seller_list))
                                @foreach($seller_list as $key => $val)
                                    <li><a href="javascript:;" data-value="{{ $val['ru_id'] ?? '-1' }}">{{ $val['shop_name'] ?? '' }}</a></li>
                                @endforeach
                                @endif
                            </ul>
                            <input name="ru_id" type="hidden" value="{{ $filter['ru_id'] ?? -1 }}">
                        </div>
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
                        <div class="input">
                            @csrf
                            <input type="text" name="keywords" class="text nofocus" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('admin/pay_log.search_pay_log') }}" autocomplete="off">
                            <input type="submit" value="" class="btn" style="font-style:normal">
                        </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('admin.pay_log.library.index_query')
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
        listTable.filter['ru_id'] = Utils.trim(frm.elements['ru_id'].value);
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
        listTable.filter['ru_id'] = -1;
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
@include('admin.base.footer')