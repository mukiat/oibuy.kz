

@if($ad_child)

<div class="bd">
	<ul>
		
@foreach($ad_child as $ad)

		<li><div class="banner-width"><a style="background: url({{ $ad['ad_code'] }}) no-repeat;" href="{{ $ad['ad_link'] }}"></a></div></li>
		
@endforeach

	</ul>
</div>
<div class="hd">
    <ul>
        
@foreach($ad_child as $ad)

        <li></li>
        
@endforeach

    </ul>
</div>

@endif
