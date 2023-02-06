
<div class="crumbs-nav">
	<div class="crumbs-nav-main clearfix">
		 <span><a href="wholesale.php">批发首页</a></span>
		 
@foreach($data['body'] as $cat)

		 <span class="arrow">&gt;</span><span> 
@if($cat['url'])
 <a href="{{ $cat['url'] }}">{{ $cat['cat_name'] }}</a> 
@else
{{ $cat['cat_name'] }}
@endif
</span>
		 
@endforeach

        
@if($data['foot'])

		 <span class="arrow">&gt;</span><span class="finish">{{ $data['foot'] }}</span>
        
@endif

	</div>
</div>
