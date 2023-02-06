@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>
<script src="{{ asset('assets/mobile/vendor/common/imitate_select.js') }}" type="text/javascript"></script>

<style>
    /*div+js模仿select效果*/
    .imitate_select{ float: left; position:relative;border: 1px solid #dbdbdb;border-radius: 2px;height: 32px;line-height: 30px; margin-right:10px;font-size: 12px;}
    .imitate_select .cite{ background: #fff url({{ asset('assets/admin/images/xjt.png') }}) right 11px no-repeat; padding: 0 10px; cursor:pointer;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left;}
    .imitate_select ul{ position:absolute; top:28px; left:-1px; background:#fff; width:100%; border:1px solid #dbdbdb; border-radius:0 0 3px 3px; display:none; z-index:199; max-height:280px;overflow: hidden;}
    .imitate_select ul li{ padding:0 10px; cursor:pointer;}
    .imitate_select ul li:hover{ background:#f5faff;}
    .imitate_select ul li a{ display:block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left; color:#707070;}

    .imitate_select ul li.li_not{ text-align:center;padding: 20px 10px;}
    .imitate_select .upward{ top:inherit; bottom:28px; border-radius:3px 3px 0 0;}
    /*div+js模仿select效果end*/
</style>

<div class="warpper">
    <div class="title">{{ __('admin/goods_label.goods') }} - {{ __('admin/goods_label.goods_label') }}</div>
    <div class="content">
        <div class="tabs_info ">
            <ul>
                <li @if(isset($type) && $type == 0) class="curr" @endif><a href="{{ route('admin/goodslabel/list', ['type' => 0]) }}">{{ __('admin/goods_label.label_type_0') }}</a></li>
                <li @if(isset($type) && $type == 1) class="curr" @endif><a href="{{ route('admin/goodslabel/list', ['type' => 1]) }}">{{ __('admin/goods_label.label_type_1') }}</a></li>
            </ul>
        </div>

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(__('admin/goods_label.label_notice_'. $type) as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="fl">
                    <a href="{{ route('admin/goodslabel/update', ['type' => $type]) }}">
                        <div class="fbutton">
                            <div class="add " title="{{ __('admin/goods_label.add_label') }}"><span><i class="fa fa-plus"></i>{{ __('admin/goods_label.add_label') }}</span>
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
                                <li><a href="javascript:;" data-value="-1">{{ __('admin/goods_label.status') }}</a></li>
                                <li><a href="javascript:;" data-value="1">{{ __('admin/goods_label.use') }}</a></li>
                                <li><a href="javascript:;" data-value="0">{{ __('admin/goods_label.no_use') }}</a></li>

                            </ul>
                            <input name="status" type="hidden" value="{{ $filter['status'] ?? -1 }}">
                        </div>
                        <div class="input">
                            @csrf
                            <input type="text" name="keywords" class="text" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('admin/goods_label.label_name') }}" autocomplete="off">
                            <input type="submit" value="" class="btn search_button">
                        </div>
                    </form>
                </div>
            </div>
            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('admin.goodslabel.library.list_query')
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
        listTable.filter['keywords'] = '';
        listTable.filter['status'] = -1;
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    $(function () {
        // 全选切换效果
        $(document).on("click", "input[name='all_list']", function () {
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
            var length = $(".list-div").find("input[name='id[]']:checked").length;

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
    });

    // 切换启用状态
    function toggle_is_show(id, obj) {
        if (!id) {
            return false;
        }

        var obj = $(obj);
        val = (obj.attr('class').match(/active/i)) ? 0 : 1;

        $.post("{{ route('admin/goodslabel/update_status') }}", {id: id, val: val}, function (data) {
            return false;
        }, 'json');

        if (obj.hasClass('active')) {
            obj.removeClass('active');
        } else {
            obj.addClass('active');
        }
    }

    // 批量操作 启用、禁用、删除
    $(document).on('click', '.batch', function () {

        var handler = $(this).attr("name");

        if (!handler) {
            return false;
        }

        //选中记录
        var ids = new Array();
        $("input[name='id[]']:checked").each(function(){
            ids.push($(this).val());
        })

        if (ids) {
            // 批量删除确认
            if (handler == 'drop') {
                //询问框
                layer.confirm('{{  __('admin/goods_label.batch_drop_notice') }} ', {
                    btn: ['{{ __('admin/common.ok') }}', '{{ __('admin/common.cancel') }}'] //按钮
                }, function () {
                    $.post("{{ route('admin/goodslabel/batch') }}", {handler:handler, id: ids}, function (data) {
                        layer.msg(data.msg);
                        if (data.error == 0) {
                            refresh();
                        }
                        return false;
                    }, 'json');
                });
            } else {
                $.post("{{ route('admin/goodslabel/batch') }}", {handler:handler, id: ids}, function (data) {
                    layer.msg(data.msg);
                    if (data.error == 0) {
                        refresh();
                    }
                    return false;
                }, 'json');
            }
        }
        return false;
    });

    // 删除
    $(document).on('click', "a[ectype='drop']", function () {

        var id = $(this).data('id');

        //询问框
        layer.confirm('{{  __('admin/goods_label.batch_drop_notice') }} ', {
            btn: ['{{ __('admin/common.ok') }}', '{{ __('admin/common.cancel') }}'] //按钮
        }, function () {
            $.post("{{ route('admin/goodslabel/drop') }}", {id: id}, function (data) {
                layer.msg(data.msg);
                if (data.error == 0) {
                    refresh();
                }
                return false;
            }, 'json');
        });

        return false;
    });
</script>

@include('admin.base.footer')
