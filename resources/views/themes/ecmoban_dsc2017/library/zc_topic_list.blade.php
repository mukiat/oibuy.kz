
@if($topic_list)


@foreach($topic_list as $topic)

<div class="topicArea" id="topicArea">
	<div class="topicBlock clearfix" id="topicBlock_{{ $topic['topic_id'] }}">
		<div class="head picPr">
			<img src="
@if($topic['user_picture'])
{{ $topic['user_picture'] }}
@else
{{ skin('/images/no-img_mid_.jpg') }}
@endif
" alt="" height="56" width="55">
			<em class="picPrem3"></em>
		</div>
		<div class="topicCont">
			<a name="topicAnchor_{{ $topic['topic_id'] }}" id="topicAnchor_{{ $topic['topic_id'] }}"></a>
			<h6><strong>{{ $topic['user_name'] }}</strong><span class="time">{{ $topic['time_past'] }}</span></h6>
			<p>{{ $topic['topic_content'] }}</p>
			<div class="commentArea">
				<div class="title">
					<a class="replay r-close" href="javascript:open_area(this,{{ $topic['topic_id'] }},1,{{ $topic['topic_id'] }});" id="replyBtn_{{ $topic['topic_id'] }}">{{ $lang['message_type']['6'] }}(<span>{{ $topic['child_topic_num'] }}</span>)</a>
				</div>
				<div class="commentBlock" id="commentBlock__{{ $topic['topic_id'] }}"></div>
				<div id="commentBlockPage__{{ $topic['topic_id'] }}" class="topicmore" type="1" style="display:none;">{{ $lang['zc_see_content'] }}</div>
			</div>
		</div>

@if($topic['child_topic_num']>0)

		<div class="topic-reply">

@foreach($topic['child_topic'] as $child)

			<div class="topic-reply-item">
				<div class="topic-reply-img">
					<img src="
@if($child['user_picture'])
{{ $child['user_picture'] }}
@else
{{ skin('/images/no-img_mid_.jpg') }}
@endif
"/>
				</div>
				<div class="topic-reply-content">
					<p><span class="topic-reply-sp1" id="topic_user_{{ $child['topic_id'] }}">{{ $child['user_name'] }}
@if($child['reply_user'])
 {{ $lang['reply_comment'] }} {{ $child['reply_user'] }}
@endif
：</span>{{ $child['topic_content'] }}</p>
					<p class="topic-reply-sp1">{{ $child['time_past'] }}<a href="javascript:open_area(this,{{ $topic['topic_id'] }},2,{{ $child['topic_id'] }});">{{ $lang['reply_comment'] }}</a></p>
				</div>
			</div>

@endforeach

		</div>

@endif

		<div class="topic-info-area" id="" data-topicid="{{ $topic['topic_id'] }}" data-type="" data-parentid="">
			<textarea onkeyup="check_words_num(this,'checkReplyWord_{{ $topic['topic_id'] }}')"></textarea>
			<p>{{ $lang['input_number_desc'] }}<span id="checkReplyWord_{{ $topic['topic_id'] }}">140</span>{{ $lang['zi_zc'] }}</p>
			<input type="button" value="{{ $lang['submit_goods'] }}" onclick="post_topic(this)"/>
		</div>
	</div>
</div>

@endforeach

<div class="zhoucou_page">
	<ul class="fr mr20">

@if($pager['page_prev'])
<li class="up_page"><a href="javascript:get_topic_list({{ $zcid }},{{ $prev_page }});">{{ $lang['page_prev'] }}</a></li>
@endif


@if($pager['page_count'] > $prev_page)
<li class="page_cur"><a href="javascript:get_topic_list({{ $zcid }},{{ $curr_page }});">{{ $curr_page }}</a></li>
@endif


@if($pager['page_count'] > $curr_page)
<li class="page_default"><a href="javascript:get_topic_list({{ $zcid }},{{ $next_page }});">{{ $next_page }}</a></li>
@endif


@if($pager['page_count'] > $next_page)
<li class="page_default"><a href="javascript:get_topic_list({{ $zcid }},{{ $third_page }});">{{ $third_page }}</a></li>
@endif


@if($pager['page_next'])
<li class="up_page"><a href="javascript:get_topic_list({{ $zcid }},{{ $next_page }});">{{ $lang['page_next'] }}</a></li>
@endif

	</ul>
</div>

@endif
