@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/list_table_jquery.js') }}"></script>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                @include('seller.base.seller_nave_header_title')

                <div class="tabmenu">
                    <ul class="tab">
                        <li @if(isset($type) && $type == 0) class="active" @endif><a href="{{ route('seller/goodslabel/list', ['type' => 0]) }}">{{ __('admin/goods_label.label_type_0') }}</a></li>
                        <li @if(isset($type) && $type == 1) class="active" @endif><a href="{{ route('seller/goodslabel/list', ['type' => 1]) }}">{{ __('admin/goods_label.label_type_1') }}</a></li>
                    </ul>
                </div>

                <div class="explanation clear mb20" id="explanation">
                    <div class="ex_tit"><i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4></div>
                    <ul>
                        @if($type == 0)
                            <li>{!! __('admin/goods_label.label_notice_0_seller.0') !!}</li>
                        @endif
                        @if($type == 1)
                            <li>{!! __('admin/goods_label.label_notice_1_seller.0') !!}</li>
                            <li>{!! __('admin/goods_label.label_notice_1.4') !!}</li>
                            <li>{!! __('admin/goods_label.label_notice_1.5') !!}</li>
                        @endif
                    </ul>
                </div>

                <div class="common-head mt20">

                    <div class="search-info">
                        <form action="javascript:search();" method="post" name="searchForm">
                            <div class="search-form">
                                <div class="search-key">
                                    @csrf
                                    <input type="text" name="keywords" class="text" value="{{ $filter['keywords'] ?? '' }}" placeholder="{{ __('admin/goods_label.label_name') }}" autocomplete="off">
                                    <input type="submit" value="" class="submit search_button">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="wrapper-list mt20">
                    <div class="list-div" id="listDiv">

                        @include('seller.goodslabel.library.list_query')

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
        listTable.filter['page'] = 1;
        listTable.loadList();
    }


</script>
@include('seller.base.seller_pagefooter')