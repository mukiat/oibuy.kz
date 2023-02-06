@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="warpper">
    <div class="title">{{ __('admin/express_manage.express_manage') }} - {{ __('admin/express_manage.express_company_manage') }}</div>
    <div class="content">
        <div class="tabs_info">
            <ul>
                <li class="curr">
                    <a href="{{ route('admin.express_manage', ['act' => 'company']) }}">{{ __('admin/express_manage.express_company_manage') }}</a>
                </li>
                <li >
                    <a href="{{ route('admin.express_manage', ['act' => 'history']) }}">{{ __('admin/express_manage.express_delivery_record') }}</a>
                </li>
            </ul>
        </div>
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i>
                <h4>{{ __('admin/common.operating_hints') }}</h4>
                <span id="explanationZoom" title="Tip"></span>
            </div>
            <ul>
                @foreach(__('admin/express_manage.tip_content') as $item)
                    <li>{!! $item !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="search">
                    <form action="javascript:search();" name="searchForm" method="post" role="search">
                        @csrf
                        <div class="select fl pr10 pl10">
                            <select class="form-control input-sm font12 " name="status">
                                <option value="-1">{{ __('admin/express_manage.initiate_mode') }}</option>
                                <option value="1" @if($filter['status'] == 1) selected @endif>{{ __('admin/express_manage.on') }}</option>
                                <option value="0" @if($filter['status'] == 0) selected @endif>{{ __('admin/express_manage.off') }}</option>
                            </select>
                        </div>
                        <div class="input">
                            <input type="text" name="keywords" class="text" value="{{ $filter['keywords'] }}"
                                   placeholder="{{ __('admin/express_manage.please_enter_the_name') }}" autocomplete="off">
                            <input type="submit" value="" class="btn search_button">
                        </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('admin.express_manage.company_list')
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

    // 搜索
    function search() {
        listTable.filter['status'] = Utils.trim(document.forms['searchForm'].elements['status'].value);
        listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keywords'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    // 全选切换效果
    $(document).on("click", "input[name='all_list']", function () {
        if ($(this).prop("checked") == true) {
            $(".list-div").find("input[type='checkbox']:not(:disabled)").prop("checked", true);
            $(".list-div").find("input[type='checkbox']").parents("tr").addClass("tr_bg_org");
        } else {
            $(".list-div").find("input[type='checkbox']:not(:disabled)").prop("checked", false);
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
        var length = $(".list-div").find("input[name='checkboxes[]']:checked").length;

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

    // 切换启用、禁用状态
    function toggle_is_show(obj, id, status) {
        var url = '{{ route('admin.express_manage', ['act' => 'company_toggle']) }}';
        $.post(url, {id:id, status:status}, function () {
            listTable.loadList();
            return false;
        }, 'json');
    }

    function confirm_batch() {
        //选中记录
        var ids = new Array();
        $("input[name='checkboxes[]']:checked").each(function(){
            ids.push($(this).val());
        })
        var status = $('select[name="statusHandler"]').val();

        if (ids) {
            $.post("{{ route('admin.express_manage', ['act' => 'company_toggle']) }}", {
                id: ids, status:status
            }, function () {
                console.log(status)
                listTable.loadList();
            }, 'json');
        }

        return false;
    }
</script>

@include('admin.base.footer')
