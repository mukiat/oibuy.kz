<ul>
    <li data-id="0" data-name="{{ lang('admin/common.choose_brand') }}" class="blue">{{ lang('admin/common.choose_cancel') }}</li>

    @if(!empty($filter_brand_list))

        @foreach($filter_brand_list as $brand)

        <li data-id="{{ $brand['brand_id'] }}" data-name="{{ $brand['brand_name'] }}">
            <em>{{ $brand['letter'] }}</em>{{ $brand['brand_name'] }}</li>

        @endforeach

    @endif

</ul>