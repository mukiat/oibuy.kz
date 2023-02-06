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
<div class="header header-settled">
    <div class="w w1200">
        <div class="logo">
            <div class="logoImg"><a href="{{ $url_index }}"><img src="
@if($shop_logo)
{{ $shop_logo }}
@else
{{ skin('/images/logo.gif') }}
@endif
" /></a></div>
            <div class="tit">{{ $lang['my_merchants'] }}</div>
        </div>
        <div class="settled-header-right">
            <ul class="settled-nav">
                <li class="curr"><a href="merchants.php">{{ $lang['home'] }}</a></li>
                <li><a href="javascript:void(0);" ectype="merchants_article">{{ $lang['settled_step'] }}</a></li>
                <li><a href="javascript:void(0);" ectype="merchants_article">{{ $lang['settled_agreement'] }}</a></li>
                <li><a href="javascript:void(0);" ectype="merchants_article">{{ $lang['settled_help'] }}</a></li>
                <li><a href="javascript:void(0);" ectype="merchants_article">{{ $lang['understand_more'] }}</a></li>
            </ul>
            <div class="tel">{{ $service_phone }}</div>
        </div>
    </div>
</div>
