<style>
	.add_comment {
		padding: 5px 0;
		border-top: 1px dotted #dcdcdc;
	}
	.add_comment .add_time {
		color: #8c8c8c;
	}
	.add_comment .content {
		padding: 10px 0;
		font-size: 14px;
		color: #222;
	}
</style>
@if($comments)

@foreach($comments as $comment)

<div class="com-list-item" id="comment_show">
	<div class="com-user-name">
		<div class="user-ico">

@if($comment['user_picture'])

			<img src="{{ $comment['user_picture'] }}" width="50" height="50">

@else

			<img src="{{ skin('/images/touxiang.jpg') }}" width="50" height="50" />

@endif

		</div>
		<div class="user-txt">{{ $comment['username'] }}</div>
	</div>
	<div class="com-item-warp">
		<div class="ciw-top">
			<div class="grade-star"><span class="grade-star-bg"
@if($comment['rank'] == 1)
 style="width:20%"
@elseif ($comment['rank'] == 2)
 style="width:40%"
@elseif ($comment['rank'] == 3)
 style="width:60%"
@elseif ($comment['rank'] == 4)
 style="width:80%"
@elseif ($comment['rank'] == 5)
 style="width:100%"
@endif
></span></div>
			<div class="ciw-actor-info">

@foreach($comment['goods_tag'] as $tag)


@if($tag['txt'])

				<span><e>{{ $tag['txt'] }}</e></span>

@endif


@endforeach

			</div>
			<div class="ciw-time">{{ $comment['add_time'] }}</div>
		</div>
		<div class="ciw-content">
			<div class="com-warp">
				<div class="com-txt" style ="overflow:hidden"> {!! html_out($comment['content']) !!}</div>
				<div class="com-operate">
                	<div class="com-operate-warp">
                        <a href="javascript:void(0);" class="nice comment_nice
@if($comment['useful'] > 0)
 selected
@endif
" data-commentid="{{ $comment['id'] }}" data-idvalue="{{ $comment['id_value'] }}"><i class="iconfont icon-thumb"></i><em class='reply-nice{{ $comment['id'] }}'>{{ $comment['useful'] }}</em></a>
                    </div>
				</div>
                <div class="comment-content reply-textarea hide" id="reply-textarea{{ $comment['id'] }}">
                    <div class="reply-arrow"><b class="layer"></b></div>
                    <div class="inner">
                        <textarea class="reply-input" name="reply_content{{ $comment['id'] }}" id="reply_content{{ $comment['id'] }}" placeholder="{{ $comment['username'] }}："></textarea>
                    </div>
                    <div class="btnbox">
                        <span id="reply-error{{ $comment['id'] }}"></span>
                        <input name="comment_goods{{ $comment['id'] }}" id="comment_goods{{ $comment['id'] }}" type="hidden" value="{{ $comment['id_value'] }}">
                        <input name="comment_user{{ $comment['id'] }}" id="comment_user{{ $comment['id'] }}" type="hidden" value="{{ $comment['user_id'] }}">
                        <a href="javascript:void(0);" class="btn sc-redBg-btn btn25 mt10 mr0" ectype="replySubmit" data-value="{{ $comment['id'] }}">{{ $lang['submit_goods'] }}</a>
                    </div>
                </div>

@if($comment['img_list'])

				<div class="com-imgs">
					<div class="p-imgs-warp">
						<div class="p-thumb-img">
							<ul>

@foreach($comment['img_list'] as $key => $img)

								<li data-src="{{ $img['comment_img'] }}" data-count="{{ $loop->iteration }}"><a href="javascript:void(0);"><img src="{{ $img['comment_img'] }}"></a></li>

@endforeach

							</ul>
						</div>
						<div class="p-view-img" style="display:none;">
							<img src="">
							<a href="javascript:void(0);" class="p-prev"><i></i></a>
							<a href="javascript:void(0);" class="p-next"><i></i></a>
						</div>
					</div>
				</div>

@endif

        <div class="com_goods_attr fl">@if($comment['goods_attr'])[{{ $comment['goods_attr'] }}]@endif</div>
		
	</div>

	{{--追评--}}
	@if($comment['add_comment'])
	<div class="add_comment">
		<div class="add_time">[{{ __('comment.user_add_time_humans', ['times' => $comment['add_comment']['add_time_humans']]) }}]</div>
		<div class="content">{!! html_out($comment['add_comment']['content'] ?? '') !!}</div>
		<div class="p-imgs-warp">
			<div class="p-thumb-img" style="margin-bottom: 0;">
				<ul>
					@foreach($comment['add_comment']['get_comment_img'] as $key => $img)
					<li data-src="{{ $img['comment_img'] }}" data-count="{{ $loop->iteration }}"><a href="javascript:void(0);"><img src="{{ $img['comment_img'] }}"></a></li>
					@endforeach
				</ul>
			</div>
			<div class="p-view-img" style="display:none; margin-top: 12px;">
				<img src="">
				<a href="javascript:void(0);" class="p-prev"><i></i></a>
				<a href="javascript:void(0);" class="p-next"><i></i></a>
			</div>
		</div>
	</div>
	@endif
			
@if($comment['re_content'])

			<div class="reply_info">
				<div class="item"><em>{{ $comment['shop_name'] }}：</em>{!! html_out($comment['re_content']) !!}</div>
			</div>

@endif

			<div class="reply_info comment-reply{{ $comment['id'] }}" id="reply_comment_ECS_COMMENT{{ $comment['id'] }}">

@foreach($comment['reply_list'] as $reply)

				<div class="item">
                    <em>{{ $reply['user_name'] }}：</em>
                    <span class="words"> {!! html_out($reply['content']) !!} </span>
                    <span class="time fr">&#12288;{{ $reply['add_time'] }}</span>
				</div>

@endforeach


@if($comment['reply_count'] > $comment['reply_size'])

				<div class="pages26">
					<div class="pages">
						<div class="pages-it">
							{{ $comment['reply_pager'] }}
						</div>
					</div>
				</div>

@endif

			</div>
		</div>
	</div>
</div>

@endforeach


@else

<div class="no_records no_comments_qt">
    <i class="no_icon no_icon_three"></i>
    <span class="block">{{ $lang['not_evaluate'] }}</span>
</div>

@endif



@if($count > $size)

<div class="pages26">
    <div class="pages">
        <div class="pages-it">
            {!! $pager !!}
        </div>
    </div>
</div>

@endif

<script type="text/javascript">
	$(function(){
		$("*[ectype='replySubmit']").click(function(){
			var T = $(this);
			var comment_id = T.data("value");
			var reply_content = $("#reply_content" + comment_id).val();
			var user_id = $("#comment_user" + comment_id).val();
			var goods_id = $("#comment_goods" + comment_id).val();

			if(reply_content == ''){
				$("#reply-error" + comment_id).html(json_languages.please_message_input);
				return false;
			}

			Ajax.call('comment.php', 'act=comment_reply&comment_id=' + comment_id + '&reply_content=' + reply_content + '&goods_id=' + goods_id + '&user_id=' + user_id, commentReplyResponse, 'POST', 'JSON');
		});

		$('.comment_nice').click(function(){
			var T = $(this);
			var comment_id = T.data('commentid');
			var goods_id = T.data('idvalue');
			var type = 'comment';

			Ajax.call('comment.php', 'act=add_useful&id=' + comment_id + '&goods_id=' + goods_id + '&type=' + type, niceResponse, 'GET', 'JSON');
		});
	});

	function commentReplyResponse(res){
		if(res.err_no == 1){
			var back_url = res.url;
			$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
			return false;
		}else if(res.err_no == 2){
			$("#reply-error" + res.comment_id).html(json_languages.been_evaluated);
		}else{
			$("#reply-error" + res.comment_id).html(json_languages.Add_success);
			$("#reply_content" + res.comment_id).val('');
			$("#reply-textarea" + res.comment_id).addClass('hide');
            $(".reply-count").addClass('red');
		}
		$(".comment-reply" + res.comment_id).html(res.content);
		$(".reply-count" + res.comment_id).html(res.reply_count);
	}

	function niceResponse(res){
		if(res.err_no == 1){
			var back_url = res.url;
			$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
			return false;
		}else if(res.err_no == 0){
			$(".reply-nice" + res.id).html(res.useful);
            $(".comment_nice").addClass("selected");
		}
	}
</script>
