<div class="site-nav" id="site-nav">
    <div class="w w1200">
        <div class="fl">
            {{-- DSC 提醒您：根据用户id来调用header_region_style.lbi显示不同的界面  --}}
{!! insert_header_region() !!}
            <div class="txt-info" id="ECS_MEMBERZONE">
                {{-- DSC 提醒您：根据用户id来调用member_info.lbi显示不同的界面  --}}
{!! insert_member_info() !!}
            </div>
        </div>
        <ul class="quick-menu fr">

@if($navigator_list['top'])


@foreach($navigator_list['top'] as $key => $nav)


@if($loop->index < 4)

            <li>
                <div class="dt"><a href="{{ $nav['url'] }}"
@if($nav['opennew'])
target="_blank"
@endif
>{{ $nav['name'] }}</a></div>
            </li>
            <li class="spacer"></li>

@endif


@endforeach


@endif


@if($navigator_list['top'])

            <li class="li_dorpdown" data-ectype="dorpdown">
                <div class="dt dsc-cm">{{ $lang['Site_navigation'] }}<i class="iconfont icon-down"></i></div>
                <div class="dd dorpdown-layer">
                    <dl class="fore1">
                        <dt>{{ $lang['characteristic_theme'] }}</dt>
                        <dd>

@foreach($top_cat_list as $key => $topc_cats)


@if($loop->index < 3)

                                    <div class="item"><a href="{{ $topc_cats['url'] }}" target="_blank">{{ $topc_cats['cat_alias_name'] }}</a></div>

@endif


@endforeach

                        </dd>
                    </dl>
                    <dl class="fore2">
                        <dt>{{ $lang['modules_txt_promo'] }}</dt>
                        <dd>

@foreach($navigator_list['top'] as $key => $nav)


@if($loop->index >= 4)

                                    <div class="item"><a href="{{ $nav['url'] }}"
@if($nav['opennew'])
 target="_blank"
@endif
>{{ $nav['name'] }}</a></div>

@endif


@endforeach

                        </dd>
                    </dl>
                </div>
            </li>

@endif

        </ul>
    </div>
</div>
<div class="header header-cart">
    <div class="w w1200">
        <div class="logo">
            <div class="logoImg"><a href="{{ $url_index }}"><img src="
@if($shop_logo)
{{ $shop_logo }}
@else
{{ skin('/images/logo.gif') }}
@endif
" /></a></div>
            <div class="tit">
@if($step == "cart")
{{ $lang['cat_list'] }}（<em ectype="cartNum">0</em>）
@else
{{ $lang['settlement_page'] }}
@endif
</div>
        </div>

@if($step == "cart")

        <div class="dsc-search">
            <div class="form">
                <form id="searchForm" name="searchForm" method="post" action="search.php" onSubmit="return checkSearchForm(this)" class="search-form">
                    <input autocomplete="off" onKeyUp="lookup(this.value);" name="keywords" type="text" id="keyword" value="
@if($search_keywords)
{{ $search_keywords }}
@else
{!! insert_rand_keyword() !!}
@endif
" class="search-text"/>

                    <input type="hidden" value="0" name="search_goods_id" />

                    <input type="hidden" name="store_search_cmt" value="{{ $search_type ?? 0 }}">
                    <button type="submit" class="button button-icon"><i></i></button>
                @csrf </form>

                <div class="suggestions_box" id="suggestions" style="display:none;">
                    <div class="suggestions_list" id="auto_suggestions_list">
                        &nbsp;
                    </div>
                </div>

            </div>
        </div>

@else

        <div class="cart-stepflex">
            <div class="cart-step-item cur">
                <span>1.{{ $lang['my_cart'] }}</span>
                <i class="iconfont icon-arrow-right-alt"></i>
            </div>
            <div class="cart-step-item @if($step == 'checkout') curr @elseif ($step == 'done' || $step == 'order_reload' || $step == 'pay_success')  cur @endif ">
                <span>2.{{ $lang['fillin_order_info'] }}</span>
                <i class="iconfont icon-arrow-right-alt"></i>
            </div>
            <div class="cart-step-item @if($step == 'done' || $step == 'order_reload' || $step == 'pay_success') curr @endif">
                <span>3.{{ $lang['submit_order_success'] }}</span>
            </div>
        </div>

@endif

    </div>
</div>
