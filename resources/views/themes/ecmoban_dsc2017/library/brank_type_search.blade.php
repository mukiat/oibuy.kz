
@if($brand_list)



<div class="bsList">
    <ul>

@foreach($brand_list as $brand)


@if($type)

            	<li id="{{ $brand['brand_id'] }}" rev='{{ $brand['brand_type'] ?? 'p_bran' }}' style="cursor:pointer" class="brandId">{{ $brand['brand_letter'] }}</li>

@else

            	<li id="{{ $brand['brand_id'] }}" rev='{{ $brand['brand_type'] ?? 'p_bran' }}' style="cursor:pointer" class="brandId">{{ $brand['brand_name'] }}</li>

@endif


@endforeach

    </ul>
</div>

@endif
