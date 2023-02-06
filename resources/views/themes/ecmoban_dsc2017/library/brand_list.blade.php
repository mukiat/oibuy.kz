
@foreach($brand_list as $key => $brand)

<li>
	<a href="{!! $brand['url'] !!}" class="img" target="_blank">
    	<img src="
@if($brand['index_img'])
{{ $brand['index_img'] }}
@else
{{ skin('/images/brand_defalut.jpg') }}
@endif
" alt="">
        <div class="brand_desc
@if(!$brand['brand_desc'])
 brand_desc_notic
@endif
">
        	<div class="tit">{{ $lang['brand_desc'] }}</div>
            <div class="info">
@if($brand['brand_desc'])
{{ $brand['brand_desc'] }}
@else
<p>{{ $lang['notime_desc'] }}</p>
@endif
</div>
        </div>
        <div class="mask"></div>
    </a>
	<div class="b-logo">
		<a href="javascript:;" class="follow" data-bid="{{ $brand['brand_id'] }}" ectype="coll_brand">
@if($brand['is_collect'] > 0)
<i class="iconfont icon-zan-alts"></i><span ectype="follow_span">{{ $lang['follow_yes'] }}</span>
@else
<i class="iconfont icon-zan-alt"></i><span ectype="follow_span">{{ $lang['follow'] }}</span>
@endif
</a>
		<img src="{{ $brand['brand_logo'] }}" alt="{{ $brand['brand_name'] }}">
	</div>
	<div class="slogan">{{ $brand['brand_name'] }}</div>
</li>

@endforeach
