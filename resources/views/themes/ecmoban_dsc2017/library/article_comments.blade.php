
@if($article_comment)

<div class="com-list-main">

@foreach($article_comment as $comment)

<div class="com-list-item" id="comment_show">
	<div class="com-user-name">
		<div class="user-ico">

@if($comment['user_picture'])

			<img src="{{ $comment['user_picture'] }}" width="50" height="50">

@else

			<img src="{{ skin('images/touxiang.jpg') }}" width="50" height="50" />

@endif

		</div>
		<div class="user-txt">{{ $comment['username'] }}</div>
	</div>
	<div class="com-item-warp">
		<div class="ciw-top">
			<div class="ciw-actor-info">

@foreach($comment['goods_tag'] as $tag)


@if($tag['txt'])

				<span>{{ $tag['txt'] }}</span>

@endif


@endforeach

			</div>
			<div class="ciw-time">{{ $comment['add_time'] }}</div>
		</div>


@if($comment['content'] && $comment['status'] == 1)

		<div class="ciw-content">
			<div class="com-warp">
				<div class="com-txt">{!! html_out($comment['content']) !!}</div>
				<div class="com-operate">
                	<div class="com-operate-warp">
                        <a href="javascript:void(0);" class="nice comment_nice
@if($comment['useful'] > 0)
 selected
@endif
" data-commentid="{{ $comment['id'] }}" data-idvalue="{{ $comment['id_value'] }}"><i class="iconfont icon-thumb"></i><em class='reply-nice{{ $comment['id'] }}'>{{ $comment['useful'] }}</em></a>
                    </div>
				</div>
			</div>

@if($comment['re_content'] && $comment['re_status'] == 1)

            <div class="reply_info">
                <div class="item"><em>{{ $lang['shop_names'] }}：</em>{!! html_out($comment['re_content']) !!}</div>
            </div>

@endif

		</div>

@endif

	</div>
</div>

@endforeach

</div>

@endif

<div class="discuss-left">
<form method="post" action="article.php" id="theFrom">
<div class="review-form" id="doPost" name="doPost">
	<div class="r-u-name">
		<div class="u-ico"><img src="
@if($user_id)

@if($user_info['user_picture'])
{{ $user_info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif

@else
{{ skin('/images/avatar.png') }}
@endif
"></div>
		<span>{{ $lang['article_comment'] }}</span>
	</div>
	<div class="item">
		<div class="item-label"><em class="red">*</em>&nbsp;{{ $lang['content'] }}：</div>
		<div class="item-value">
			<textarea class="textarea" id="test_content" name="content"></textarea>
			<div class="form_prompt"></div>
		</div>
	</div>
	<div class="item">
		<div class="item-label">&nbsp;</div>
		<div class="item-value">
			<input type="hidden" name="act" value="add_comment" />
			<input type="hidden" name="article_id" value="{{ $id }}" />
			<input type="button" class="btn sc-redBg-btn" ectype="submit" value="{{ $lang['publish'] }}">
		</div>
	</div>
</div>
@csrf </form>
</div>

@if($count > $size)

<div class="pages26">
    <div class="pages">
        <div class="pages-it">
            {!! $pager !!}
        </div>
    </div>
</div>

@endif

<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
	$(function(){
		$('.comment_nice').click(function(){
			var T = $(this);
			var comment_id = T.data('commentid');
			var goods_id = T.data('idvalue');
			var type = 'comment';

			Ajax.call('comment.php', 'act=add_useful&id=' + comment_id + '&goods_id=' + goods_id + '&type=' + type, niceResponse, 'GET', 'JSON');
		});
	});

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

	$(document).on("click","[ectype='submit']",function(){
		var user_id = $("input[name='user_id']").val();
		var article_id = $("input[name='article_id']").val();
		//判断是否登录
		if(user_id == 0){
			var back_url = "article.php?id=" + article_id;
			$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
			return false;
		}

		var content = $("#test_content").val();
		if(!content){
			var message = "{{ $lang['comment_not_null'] }}";
			pbDialog(message,"",0);
			return false;
		}else{
			Ajax.call('article.php', 'act=add_comment&content=' + content + '&article_id=' + article_id, function(data){
				if(data.error){
					pbDialog(data.message,"",0);
				}else{
					pbDialog(data.message,"",1,'','',58,true,function(){location.reload();});
				}
			}, 'GET', 'JSON');
		}
	})

</script>
