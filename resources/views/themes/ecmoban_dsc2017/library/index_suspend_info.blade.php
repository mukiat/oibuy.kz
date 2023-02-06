

@if($user_id)

	<a href="user.php" class="nick_name">{{ $info['nick_name'] }}</a>

@else

	<a href="user.php">{{ $lang['login'] }}</a>
    
@if($shop_reg_closed != 1)
|
    <a href="user.php?act=register">{{ $lang['register'] }}</a>
    
@endif


@endif


    