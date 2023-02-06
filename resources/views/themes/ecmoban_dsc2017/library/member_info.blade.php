

@if($user_info)

<span>{{ $lang['hello'] }} &nbsp;<a href="{{ $user }}">&nbsp;</a></span>
<span>，{{ $lang['Welcome_to'] }}&nbsp;<a alt="{{ $lang['home'] }}" title="{{ $lang['home'] }}" href="{{ url('/') }}">{{ $shop_name }}</a></span>
<span>[<a href="{{ $logout }}">{{ $lang['user_logout'] }}</a>]</span>

@else

	<a href="{{ $user }}" class="link-login red">{{ $lang['please_login'] }}</a>

@if($shop_reg_closed != 1)

    <a href="{{ $register }}" class="link-regist">{{ $lang['zhuce'] }}</a>

@endif


@endif

