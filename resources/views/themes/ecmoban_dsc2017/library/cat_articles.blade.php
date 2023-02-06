
<ul>
	
@foreach($articles as $article_item)

    <li><a href="{{ $article_item['url'] }}" target="_blank" title="{{ $article_item['title'] }}">{{ $article_item['short_title'] }}</a>&nbsp;[{{ $article_item['add_time'] }}]</li>
    
@endforeach

</ul>