
<div class="replylist">
    
@foreach($reply_list as $list)

    <div class="item-reply">
        <strong>{{ $list['floor'] }}</strong>
        <div class="reply-list">
            <div class="reply-con">
                <span class="u-name ftx-05">{{ $list['user_name'] }}：</span>
                <span class="u-con">{{ $list['content'] }}</span>
            </div>
            <div class="reply-meta">
                <span class="reply-left fl">{{ $list['add_time'] }}</span>
                <a class="fr ftx-05" href="#reply">{{ $lang['reply_comment'] }}</a>
            </div>
        </div>
    </div>
    
@endforeach

</div>    

@if($reply_count > $reply_size)

<div class="pages26">
    <div class="pages">
        <div class="pages-it">
            {{ $reply_pager }}
        </div>
    </div>
</div>

@endif
