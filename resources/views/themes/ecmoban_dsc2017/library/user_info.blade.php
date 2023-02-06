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

    <span>Hi，{{ $lang['Welcome_to'] }}{{ $shop_name }}!</span>
    <a href="user.php" class="login-button">{{ $lang['please_login'] }}</a>
    <a href="{{ $login_right_link }}" target="_blank" class="register_button">{{ $login_right }}</a>

@endif

</div>
<input type="hidden" name="user_id" ectype="user_id" value="{{ $user_id ?? 0 }}" />
