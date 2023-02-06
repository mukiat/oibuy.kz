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

<div class="warpper">
    <div class="title"><a href="{{ route('admin/goodslabel/list', ['type' => $type]) }}" class="s-back">{{ __('admin/common.back') }}</a>{{ __('admin/goods_label.goods') }} - {{ __('admin/goods_label.bind_goods') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @foreach(__('admin/goods_label.bind_goods_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist">

            <div class="common-head">
                <div class="fl">
                    <a href="{!! route('admin/goodslabel/select_goods', ['label_id' => $label_id, 'type' => $type]) !!}" class="fancybox fancybox.iframe">
                        <div class="fbutton">
                            <div class="add " title="{{ __('admin/common.select_goods') }}"><span><i class="fa fa-plus"></i>{{ __('admin/common.select_goods') }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="search">
                    <form action="javascript:search();" method="post" name="searchForm">
                        <div class="select_w140 imitate_select ">
                            <div class="cite">
                                {{ __('admin/common.please_select') }}
                            </div>
                            <ul>
                                <li><a href="javascript:;" data-value="-1">{{ __('admin/goods_label.shop_keywords') }}</a></li>
                                @if(!empty($seller_list))
                                    @foreach($seller_list as $key => $val)
                                        <li><a href="javascript:;" data-value="{{ $val['ru_id'] ?? '-1' }}">{{ $val['shop_name'] ?? '' }}</a></li>
                                    @endforeach
                                @endif
                            </ul>
                            <input name="ru_id" type="hidden" value="{{ $filter['ru_id'] ?? -1 }}">
                        </div>
                        <div class="input">
                            @csrf
                            <input type="text" name="goods_keywords" class="text nofocus" value="{{ $filter['goods_keywords'] ?? '' }}" placeholder="{{ __('admin/goods_label.goods_keywords') }}" autocomplete="off">
                            <input type="submit" value="" class="btn" style="font-style:normal">
                        </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('admin.goodslabel.library.bind_goods_query')
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
        listTable.filter['ru_id'] = Utils.trim(frm.elements['ru_id'].value);
        listTable.filter['goods_keywords'] = Utils.trim(frm.elements['goods_keywords'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    /**
     * 刷新
     */
    function refresh()
    {
        listTable.filter['ru_id'] = -1;
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
                $.post("{{ route('admin/goodslabel/unbind_goods', ['label_id' => $label_id]) }}", {
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
@include('admin.base.footer')