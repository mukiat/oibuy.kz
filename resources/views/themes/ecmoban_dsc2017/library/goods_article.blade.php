
@if($goods_article_list)

<div class="g-main">
	<div class="mt">
		<h3>{{ $lang['article_releate'] }}</h3>
	</div>
	<div class="mc">
		<div class="mc-warp">
			<div class="items">
				
@foreach($goods_article_list as $article)

				<div class="item"><a href="{{ $article['url'] }}" title="{{ $article['title'] }}">{{ $article['short_title'] }}</a></div>
				
@endforeach

			</div>
		</div>
	</div>
</div>

@endif
