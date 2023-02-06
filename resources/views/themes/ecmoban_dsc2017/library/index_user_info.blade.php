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

            <span>Hi，{{ $lang['Welcome_to'] }}{{ $shop_name }}!</span>
            <a href="user.php" class="login-button">{{ $lang['please_login'] }}</a>
            <a href="{{ $login_right_link }}" target="_blank" class="register_button">{{ $login_right }}</a>

@endif

    </div>

@if($index_article_cat)

    <div class="vip-item">
        <div class="tit">

@foreach($index_article_cat as $key => $cat)

            <a href="javascript:void(0);" class="tab_head_item">{{ $cat['cat']['name'] }}</a>

@endforeach

        </div>
        <div class="con">

@foreach($index_article_cat as $cat)

            <ul
@if(!$loop->first)
style="display:none;"
@endif
>

@foreach($cat['arr'] as $article)

                <li><a href="{{ $article['url'] }}" target="_blank">{{ $article['title'] }}</a></li>

@endforeach

            </ul>

@endforeach

        </div>
    </div>

@endif

    <div class="vip-item">
        <div class="tit">{{ $lang['vip_entrance'] }}</div>
        <div class="kj_con">
            <div class="item item_1">
                <a href="history_list.php" target="_blank">
                    <i class="iconfont icon-browse"></i>
                    <span>{{ $lang['my_history_list'] }}</span>
                </a>
            </div>
            <div class="item item_2">
                <a href="user_collect.php?act=collection_list" target="_blank">
                    <i class="iconfont icon-zan-alt"></i>
                    <span>{{ $lang['my_collection_list'] }}</span>
                </a>
            </div>
            <div class="item item_3">
                <a href="user_order.php?act=order_list" target="_blank">
                    <i class="iconfont icon-order"></i>
                    <span>{{ $lang['my_order_list'] }}</span>
                </a>
            </div>
            <div class="item item_4">
                <a href="user.php?act=account_safe" target="_blank">
                    <i class="iconfont icon-password-alt"></i>
                    <span>{{ $lang['my_account_safe'] }}</span>
                </a>
            </div>
            <div class="item item_5">
                <a href="user.php?act=affiliate" target="_blank">
                    <i class="iconfont icon-share-alt"></i>
                    <span>{{ $lang['my_affiliate'] }}</span>
                </a>
            </div>
            <div class="item item_6">
                <a href="merchants.php" target="_blank">
                    <i class="iconfont icon-settled"></i>
                    <span>{{ $lang['my_merchants'] }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
