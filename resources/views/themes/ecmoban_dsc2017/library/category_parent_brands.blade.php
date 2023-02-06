
@if($defa == 1)


@foreach($brands as $brand)

    <div class="brand-item"><a href="{!! $brand['url'] !!}" target="_blank"><img src="{{ $brand['brand_logo'] }}"></a></div>

@endforeach


@else


@foreach($brands as $brand)

    <li><a href="{!! $brand['url'] !!}" target="_blank"><img src="{{ $brand['brand_logo'] }}" width="112" height="49" /></a></li>

@endforeach


@endif

