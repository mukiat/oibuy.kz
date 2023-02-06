@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="warpper">
    <div class="title">{{ __('admin/express_manage.express_manage') }} - {{ __('admin/express_manage.express_delivery_record') }}</div>
    <div class="content">
        <div class="tabs_info">
            <ul>
                <li>
                    <a href="{{ route('admin.express_manage', ['act' => 'company']) }}">{{ __('admin/express_manage.express_company_manage') }}</a>
                </li>
                <li class="curr">
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
                        <div class="input">
                            <input type="text" name="keywords" class="text" value="{{ $filter['keywords'] }}"
                                   placeholder="{{ __('admin/express_manage.delivery_record_placeholder') }}" autocomplete="off">
                            <input type="submit" value="" class="btn search_button">
                        </div>
                    </form>
                </div>
            </div>

            <div class="common-content">
                <div class="list-div" id="listDiv">
                    @include('admin.express_manage.history_list')
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
        listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keywords'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }
</script>

@include('admin.base.footer')
