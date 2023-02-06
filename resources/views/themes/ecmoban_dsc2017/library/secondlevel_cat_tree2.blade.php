
<div id="sp-category">
    <div class="mt"><h1>{{ $lang['Classification_selection'] }}</h1></div>
    <div class="mc">
        
@foreach($categories_pro2 as $cat)

        <dl>
            <dt><s class="icon"></s>
             
@if($cat['category_link'] == 1)

            {{ $cat['name'] }}
            
@else

            <a class="cate_name
@if($cat['id'] == $category)
 current
@endif
">{{ $cat['name'] }}</a>
            
@endif

            </dt>
            
@foreach($cat['cat_id'] as $child)

            <dd><a href="{{ $child['url'] }}" 
@if($child['id'] == $category)
class="current"
@endif
>{{ $child['name'] }}</a></dd>
            
@endforeach

        </dl>
       
@endforeach

    </div>
</div>
<script type="text/javascript">
//店铺分类展开
$("#sp-category dt").click(function(){
	if($(this).parent().hasClass("open")){
		$(this).parent().removeClass("open");
	}else{
		$(this).parent().addClass("open");
		$(this).parent().siblings().removeClass("open");
	}
});
</script>
