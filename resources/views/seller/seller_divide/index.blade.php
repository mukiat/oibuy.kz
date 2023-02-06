@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>
<style>

    .add_sub_dialog {
        /*position: absolute;*/
        margin:0 auto;
        width: 360px;
        text-align: left;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px #ccc;
        overflow: hidden;
    }

    .dialog_item { width: 100%; height: 30px; line-height: 30px; float: left; margin-bottom: 10px;}
    .dialog_item .label {width: 30%;height: 30px; line-height: 30px; float: left;text-align: right;padding-right: 8px;color: #333}
    .dialog_item .label_value { width: 68%;float: left;}

</style>
<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                <!-- 当前位置 -->
                <div class="ecsc-path"><span>{{ __('admin/seller_divide.merchants_base_info') }} - {{ __('admin/seller_divide.seller_divide_list') }}</span></div>

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

                        <div class="order_state_tab" style="width:50%">
                            <a href="{{ route('seller/seller_divide/index') }}" class="current">{{ trans('admin/seller_divide.seller_divide_list') }}</a>
                            <a href="{{ route('seller/seller_divide/apply_list') }}" >{{ trans('admin/seller_divide.seller_divide_apply_list') }}</a>
                        </div>

                        <div class="fr mb20">
                            {{--添加二级商户--}}
                            <a class="sc-btn sc-blue-btn  js-add-sub-mchid" href="javascript:;" ><i class="fa fa-plus"></i>{{ __('admin/seller_divide.seller_add_sub_mchid') }}</a>
                            @if(!empty($apply_info))
                                {{--查看二级商户进件申请--}}
                                <a class="sc-btn sc-blue-btn" href="{{ route('seller/seller_divide/apply_detail', ['divide_channel' => $filter['divide_channel']]) }}"><i class="fa fa-eye"></i>{{ __('admin/seller_divide.seller_divide_detail') }}</a>

                            @else
                                {{--二级商户进件申请--}}
                                <a class="sc-btn sc-blue-btn" href="{{ route('seller/seller_divide/apply', ['divide_channel' => $filter['divide_channel']]) }}"><i class="fa fa-plus"></i>{{ __('admin/seller_divide.seller_divide_apply') }}</a>
                            @endif

                        </div>

                    </div>

                    <div class="wrapper-list mt20">

                        <div class="list-div" id="listDiv">

                            @include('seller.seller_divide.library.index_query')

                        </div>

                    </div>
                    
                </div>                
            </div>
        </div>

    </div>
</div>

<div class="add_sub_dialog hide" >
    <div class="fancy">
        <div class="dialog_item mt10">
            <div class="label">{{ __('admin/seller_divide.divide_channel') }}：</div>
            <div class="label_value">
                <div class="checkbox_items">
                    <div class="checkbox_item">
                        <input type="radio" name="divide_channel" id="divide_channel_1" class="clicktype" checked="true" value="1" > {{ __('admin/seller_divide.divide_channel_1') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="dialog_item">
            <div class="label">{{ __('admin/seller_divide.seller_sub_mchid') }}：</div>
            <div class="label_value">
                <input type="hidden" name="ru_id" value="{{ $ru_id ?? 0 }}" />
                <input type="text" name="seller_sub_mchid" id="seller_sub_mchid" class="form-control input-sm w300" autocomplete="off"/>
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


    $(function () {

        // 添加二级商户号
        $(document).on("click", ".js-add-sub-mchid", function() {
            var url = '{{ route('seller/seller_divide/update') }}';

            var that = $(this);

            layer.open({
                type: 1,
                title: "{{ __('admin/seller_divide.seller_add_sub_mchid') }}",
                closeBtn: false,
                area: ['520px', 'auto'],
                shade: 0.8,
                id: 'LAY_layuipro', //设定一个id，防止重复弹出
                resize: false,
                btn: ['{{ __('admin/common.ok') }}', '{{ __('admin/common.cancel') }}'],
                btnAlign: 'c',
                moveType: 1, //拖拽模式，0或者1
                content: $('.add_sub_dialog').html(),
                success: function (layero) {
                    //var btn = layero.find('.layui-layer-btn');
                    //btn.find('.layui-layer-btn0').attr({href: url});
                },
                yes: function (index, layero) {

                    var ru_id = layero.find('input[name="ru_id"]').val();
                    var divide_channel = layero.find('input[name="divide_channel"]:checked').val();
                    var seller_sub_mchid = layero.find('input[name="seller_sub_mchid"]').val();

                    if (!divide_channel) {
                        layer.msg('{{ __('admin/seller_divide.divide_channel_required') }}');
                        return false;
                    }
                    if (!seller_sub_mchid) {
                        layer.msg('{{ __('admin/seller_divide.seller_sub_mchid_required') }}');
                        return false;
                    }

                    var data = {
                        divide_channel:divide_channel,
                        sub_mchid:seller_sub_mchid
                    }

                    $.post(url, {handler:'add',ru_id:ru_id,data:data}, function (result) {
                        layer.msg(result.msg);
                        if (result.error == 0) {
                            if (result.url) {
                                setTimeout(function () {
                                    window.location.href = result.url;
                                }, 1500);
                            } else {
                                window.location.reload();
                            }
                        }
                        return false;
                    }, 'json');
                }
            });

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