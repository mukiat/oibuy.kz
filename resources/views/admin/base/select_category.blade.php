<div class="select-top">
    <a href="javascript:;" class="categoryTop" data-cid="0" data-cname="" data-diff="0" data-show='{{ $cat_type_show ?? 0 }}' data-seller='{{ $user_id ?? 0 }}' data-table='category'>{{ lang('admin/common.choose_again') }}</a>

    @if(!empty($filter_category_navigation))

        @foreach($filter_category_navigation as $navigation)

        &gt <a href="javascript:;" class="categoryOne" data-cid="{{ $navigation['cat_id'] }}" data-cname="{{ $navigation['cat_name'] }}" data-diff="0" data-url='' data-show='{{ $cat_type_show ?? 0 }}' data-seller='{{ $user_id ?? 0 }}' data-table='category' >{{ $navigation['cat_name'] }}</a>

        @endforeach

    @else

        &gt <span>{{ lang('admin/common.please_category') }}</span>

    @endif

</div>
<div class="select-list">
    <ul>

        @if(!empty($filter_category_list))

            @foreach($filter_category_list as $category)

            <li data-cid="{{ $category['cat_id'] }}" data-cname="{{ $category['cat_name'] }}" data-diff="0"  @if(isset($category['is_selected']) && $category['is_selected'])class="blue" @endif data-url='' data-show='{{ $cat_type_show ?? 0 }}' data-seller='{{ $user_id ?? 0 }}' data-table='category'><em>
                    @if($filter_category_level == 1)
                        Ⅰ
                    @elseif($filter_category_level == 2)
                        Ⅱ
                    @elseif($filter_category_level == 3)
                        Ⅲ
                    @else
                        Ⅰ
                    @endif
                </em>{{ $category['cat_name'] }}</li>

            @endforeach

        @endif

    </ul>
</div>