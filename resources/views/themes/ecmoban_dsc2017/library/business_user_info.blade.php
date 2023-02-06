<div class="vip-con">
    <div class="avatar">
        <a href="user.php?act=profile"><img src="
@if($user_id)

@if($info['user_picture'])
{{ $info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif

@else
{{ skin('/images/avatar.png') }}
@endif
"></a>
    </div>
    <div class="login-info">

@if($user_id)

            <span>Hi，
@if($info['nick_name'])
{{ $info['nick_name'] }}
@else
{{ $lang['Welcome_to'] }}{{ $shop_name }}!
@endif
</span>
            <a href="user.php" class="login-button login-success">{{ $lang['user_center'] }}</a>

@else

            <span>{{ $lang['welcome_to'] }}{{ $shop_name }}!</span>
            <a href="user.php" class="login-button">{{ $lang['please_login'] }}</a>
            <a href="merchants.php" target="_blank" class="register_button">{{ $lang['register_button'] }}</a>

@endif

    </div>
    <div class="vip-item">
        <div class="tit">
@foreach($wholesale_article_cat as $key => $cat)

            <a href="javascript:void(0);" class="tab_head_item">{{ $cat['cat']['name'] }}</a>

@endforeach

        </div>
        <div class="con">
            <ul>

@foreach($purchase as $list)

                <li><a href="{{ $list['url'] }}" target="_blank">{{ $list['subject'] }}</a></li>

@endforeach

            </ul>

@foreach($wholesale_article_cat as $cat)

            <ul style="display:none;">

@foreach($cat['arr'] as $article)

                <li><a href="{{ $article['url'] }}" target="_blank">{{ $article['title'] }}</a></li>

@endforeach

            </ul>

@endforeach

        </div>
    </div>
</div>
