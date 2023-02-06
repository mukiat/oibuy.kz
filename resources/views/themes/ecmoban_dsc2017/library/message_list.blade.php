
@if($msg_lists)

<div class="avatar">
    <img src="{{ skin('images/admin_avatar.png') }}" alt="">
</div>
<div class="message">

@foreach($msg_lists as $key => $msg)

    <div class="item">
        <div class="item-l"><h2>{{ $msg['msg_type'] }}</h2><span class="time">{{ $msg['msg_time'] }}</span></div>
        <div class="item-r">
            <div class="content-item-tit">
                <div class="tit"><span class="ftx-10">{{ $msg['user_name'] }}
@if($msg['user_name'] == '')
{{ $lang['anonymous'] }}
@endif
：</span>
@if($msg['id_value'] > 0)
{{ $lang['feed_user_comment'] }}&nbsp;&nbsp;<a href="{{ $msg['preg_replace'] }}" target="_blank" title="{{ $msg['goods_name'] }}">{{ $msg['goods_name'] }}</a>
@endif
{{ $msg['msg_content'] }}</div>
            </div>

@if($msg['re_content'])

            <div class="hf"><h3>{{ $lang['shopman_reply'] }}：</h3>{{ $msg['re_content'] }}</div>

@endif

        </div>
    </div>

@endforeach

</div>

@endif
