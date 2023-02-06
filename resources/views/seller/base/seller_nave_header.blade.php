<div class="ecsc-head-layout ">
    <!-- 顶部 -->
    <div class="wrapper">
        <div class="admin-logo">
            <a href="{{ url('seller') . '/' }}">
                <div class="t">
                    @if (!empty($seller_logo))
                        <img src="{{ $seller_logo ?? '' }}" class="logo"/>
                    @else
                        <img src="{{ asset('assets/seller/images/seller_logo.png') }}" class="logo"/>
                    @endif
                    <h1>{{ lang('seller/common.cp_home') }}</h1>
                </div>
                @if(config('shop.lang') != 'en')
                    <div class="en"><img src="{{ asset('assets/seller/images/en.png') }}"/></div>
                @endif
            </a>
        </div>
        <div class="ecsc-nav">
            <ul class="ecsc-nav-ul">
                <li
                        @if(isset($menu_select['action']) && !$menu_select['action'])
                        class="current"
                        @endif
                >
                    <a href="{{ url('seller') . '/' }}">{{ lang('seller/common.00_home') }}</a>
                    <div class="arrow"></div>
                </li>

                @if($seller_menu)

                    @foreach($seller_menu as $key=>$menu)
                        @if ($key <= 7)
                            @if($menu['url'])

                                <li  ectype="li"
                                        @if(isset($menu['action']) && isset($menu_select['action']) && $menu['action'] == $menu_select['action'])
                                        class="current"
                                        @endif
                                >
                                    <a href="{{ str_replace('../', url('seller') . '/', $menu['url'])  }}">{{ $menu['label'] }}</a>
                                    <div class="arrow"></div>
                                </li>

                            @endif
                        @endif
                    @endforeach

                    @if (count($seller_menu > 7))
                        <li class="gengduo" ectype="moreLi">
                            <a href="javascript:void(0);">{{ lang('seller/common.more') }}</a>
                            <div class="xian"></div>
                            <div class="more-warp">
                                @foreach($seller_menu as $key=>$menu)
                                    @if ($key > 7)
                                        <dd @if(isset($menu_select['action']) && $menu_select['action'] == $menu['action']) class="current" @endif>
                                            <a href="{{ str_replace('../', url('seller') . '/', $menu['url'])  }}">{{ $menu['label'] }}</a>
                                        </dd>
                                    @endif
                                @endforeach
                            </div>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
        <div class="ecsc-admin">
            <div class="avatar">
                <form action="{{ url('seller/index.php') }}" method="post" enctype="multipart/form-data" runat="server">
                    @csrf
                    <input type="hidden" name="act" value="upload_store_img">
                    <input type="file" name="img">
                </form>
                <a href="javascript:void(0);"><img src="
                    @if(isset($seller_info) && $seller_info['admin_user_img'])
                    {{ $seller_info['admin_user_img'] }}
                    @else
                    {{ asset('assets/seller/images/tx.png') }}
                    @endif
                            "></a>
            </div>
            <dl>
                <dt>
                    <span>{{ $seller_name ?? '' }}</span>

                    @if($privilege_seller == 1)
                        <a href="{{ url('seller/privilege.php?act=modif') }}" class="modif"><i
                                    class="icon icon-edit"></i></a>
                    @endif

                </dt>
                <dd>
                    <span><i class="sc_icon sc_icon_seller"></i><a class=""
                                                                   href="{{ url('merchants_store.php?merchant_id='.$ru_id) }}"
                                                                   target="_blank">{{ lang('seller/common.19_merchants_store') }}</a></span>
                    <span><i class="sc_icon sc_icon_set"></i><a class=""
                                                                href="{{ url('seller/index.php?act=clear_cache') }}">{{ lang('seller/common.clear_cache') }}</a></span>
                    <span><i class="sc_icon sc_icon_out"></i><a class=""
                                                                href="{{ url('seller/privilege.php?act=logout') }}">{{ lang('common.logout') }}</a></span>
                </dd>
            </dl>
        </div>
    </div>

</div>

<script type="text/javascript" src="{{ __TPL__ }}/js/seller.js"></script>