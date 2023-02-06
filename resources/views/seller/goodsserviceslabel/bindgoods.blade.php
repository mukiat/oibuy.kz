@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<style>

    .list-div .img {
        float: left;
        width: 68px;
        height: 68px;
    }
    .list-div .goods-info-left {
        height: 68px;
        line-height: 68px;
        padding: 0 10px;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                {{--当前位置--}}
                <div class="ecsc-path"><span>{{ __('admin/goods_services_label.goods') }} - {{ __('admin/goods_services_label.bind_goods') }}</span></div>

                <div class="tabmenu">
                    <ul class="tab">
                        <li class="active" ><a href="javascript:;">{{ __('admin/goods_services_label.bind_goods') }}</a></li>
                    </ul>
                </div>

                <div class="btn fr mb10">
                    <a class="sc-btn sc-blue-btn" href="{{ route('seller/goodsserviceslabel/list', ['type' => 1]) }}"><i class="fa fa-reply"></i>{{ __('admin/goods_services_label.goods_services_label') }}</a>
                </div>
                <div class="clear"></div>

                <div class="wrapper-right of">
                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                        <ul>
                            @foreach(__('admin/goods_services_label.services_add_label') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="common-head mt20">

                        <div class="fl">

                            <a  href="{!! route('seller/goodsserviceslabel/select_goods', ['label_id' => $label_id, 'type' => $type]) !!}" class="sc-btn sc-blue-btn  fancybox fancybox.iframe"  ><i class="fa fa-plus"></i>{{ __('admin/common.select_goods') }}</a>
                        </div>

                        <div class="search-info">
                            <form action="javascript:search();" method="post" name="searchForm">
                                <div class="search-form">
                                    <div class="search-key">
                                        @csrf
                                        <input type="text" name="goods_keywords" class="text nofocus" value="{{ $filter['goods_keywords'] ?? '' }}" placeholder="{{ __('admin/goods_services_label.goods_keywords') }}" autocomplete="off">
                                        <input type="submit" value="" class="submit search_button">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="wrapper-list mt20">
                        <div class="list-div" id="listDiv">
                            @include('seller.goodsserviceslabel.library.bind_goods_query')
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
        listTable.filter['goods_keywords'] = Utils.trim(frm.elements['goods_keywords'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    /**
     * 刷新
     */
    function refresh()
    {
        listTable.filter['goods_keywords'] = '';
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    // 批量删除
    function confirm_batch() {

        //选中记录
        var ids = new Array();
        $("input[name='goods_id[]']:checked").each(function(){
            ids.push($(this).val());
        })

        if (ids) {
            //询问框
            layer.confirm('{{  __('admin/common.confirm_delete') }} ', {
                btn: ['{{ __('admin/common.ok') }}', '{{ __('admin/common.cancel') }}'] //按钮
            }, function () {
                $.post("{{ route('seller/goodsserviceslabel/unbind_goods', ['label_id' => $label_id]) }}", {
                    goods_id: ids,
                }, function (data) {
                    layer.msg(data.msg);
                    if (data.error == 0) {
                        refresh();
                    }
                    return false;
                }, 'json');
            });
        }

        return false;
    }


    $(function () {

        // 全选切换效果
        $(document).on("click", 'input[name="all_list"]', function () {
            if ($(this).prop("checked") == true) {
                $(".list-div").find("input[type='checkbox']").prop("checked", true);
                $(".list-div").find("input[type='checkbox']").parents("tr").addClass("tr_bg_org");
            } else {
                $(".list-div").find("input[type='checkbox']").prop("checked", false);
                $(".list-div").find("input[type='checkbox']").parents("tr").removeClass("tr_bg_org");
            }

            btnSubmit();
        });

        // 单选切换效果
        $(document).on("click", ".sign .checkbox", function () {
            if ($(this).is(":checked")) {
                $(this).parents("tr").addClass("tr_bg_org");
            } else {
                $(this).parents("tr").removeClass("tr_bg_org");
            }

            btnSubmit();
        });

        // 禁用启用提交按钮
        function btnSubmit() {
            var length = $(".list-div").find("input[name='goods_id[]']:checked").length;

            if ($("#listDiv *[ectype='btnSubmit']").length > 0) {
                if (length > 0) {
                    $("#listDiv *[ectype='btnSubmit']").removeClass("btn_disabled");
                    $("#listDiv *[ectype='btnSubmit']").attr("disabled", false);
                } else {
                    $("#listDiv *[ectype='btnSubmit']").addClass("btn_disabled");
                    $("#listDiv *[ectype='btnSubmit']").attr("disabled", true);
                }
            }
        }


        // fancybox 弹出框
        $(".fancybox").fancybox({
            afterClose: function () {
                refresh(); // 弹窗关闭 重新加载页面
            },
            width: '80%',
            height: '80%',
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
                    if (data.error == 0) {
                        refresh();
                    }
                    return false;
                }, 'json');
            });

        });

    });
</script>
@include('seller.base.seller_pagefooter')
