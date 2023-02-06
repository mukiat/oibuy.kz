
@foreach($reply_list as $reply)

<div class="item">                                          
	<em>{{ $reply['user_name'] }}：</em>
	<span class="words">{{ $reply['content'] }}</span>
	<span class="time fr">&#12288;{{ $reply['add_time'] }}</span>                                              
</div>

@endforeach


@if($reply_count > $reply_size)

<div class="pages26">
	<div class="pages">
		<div class="pages-it">
			{{ $reply_pager }}
		</div>
	</div>
</div>

@endif

