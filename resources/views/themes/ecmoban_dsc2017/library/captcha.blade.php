
@if($enabled_captcha)

<div class="item" ectype="captcha">
    <div class="item-info">
        <i class="iconfont icon-security"></i>
        <input type="text" id="captcha" name="captcha" class="text text-2" value="" placeholder="{{ $lang['comment_captcha'] }}" autocomplete="off" />
        <img src="captcha_verify.php?captcha=is_login&{{ $rand }}" class="captcha_img fr" onClick="this.src='captcha_verify.php?captcha=is_login&'+Math.random()">
    </div>
</div>

@endif
