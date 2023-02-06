    <div class="tit">
        
@foreach($index_article_cat as $key => $cat)

        <a href="javascript:void(0);" class="tab_head_item
@if(!$loop->first)
 
@endif
">{{ $cat['cat']['name'] }}</a>
        
@endforeach

    </div>
    <div class="con">
        
@foreach($index_article_cat as $cat)

        <ul 
@if(!$loop->first)
style="display:none;"
@endif
>
            
@foreach($cat['arr'] as $article)

            <li><a href="{{ $article['url'] }}" target="_blank">{{ $article['title'] }}</a></li>
            
@endforeach

        </ul>
        
@endforeach

    </div>