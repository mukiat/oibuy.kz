<div class="mui-mbar-tabs">
	<div class="quick_link_mian" data-userid="{{ $user_id }}">
        <div class="quick_links_panel">
            <div id="quick_links" class="quick_links">
            	<ul>
                    <li>
                        <a href="user.php"><i class="setting"></i></a>
                        <div class="ibar_login_box status_login">
                            <div class="avatar_box">
                                <p class="avatar_imgbox">

@if($info['user_picture'])

                                    <img src="{{ $info['user_picture'] }}" width="100" height="100" />

@else

                                    <img src="{{ skin('images/touxiang.jpg') }}" width="100" height="100"/>

@endif

                                </p>
                                <ul class="user_info">
                                    <li>{{ $lang['username'] }}：{{ $info['nick_name'] ?? $lang['temporary_no'] }}</li>
                                    <li>{{ $lang['level_pos'] }}：{{ $info['rank_name'] ?? $lang['temporary_no'] }}</li>
                                </ul>
                            </div>
                            <div class="login_btnbox">
                                <a href="{{ url('/') }}/user_order.php?act=order_list" class="login_order">{{ $lang['order_list'] }}</a>
                                <a href="{{ url('/') }}/user_collect.php?act=collection_list" class="login_favorite">{{ $lang['label_collection'] }}</a>
                            </div>
                            <i class="icon_arrow_white"></i>
                        </div>
                    </li>

                    <li id="shopCart">
                        <a href="javascript:void(0);" class="cart_list">
                            <i class="message"></i>
                            <div class="span">{{ $lang['cat_list'] }}</div>
                            <span class="cart_num">{{ $cart_info['number'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="mpbtn_order"><i class="chongzhi"></i></a>
                        <div class="mp_tooltip">
                            <font id="mpbtn_money" style="font-size:12px; cursor:pointer;">{{ $lang['order_list'] }}</font>
                            <i class="icon_arrow_right_black"></i>
                        </div>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="mpbtn_yhq"><i class="yhq"></i></a>
                        <div class="mp_tooltip">
                            <font id="mpbtn_money" style="font-size:12px; cursor:pointer;">{{ $lang['preferential'] }}</font>
                            <i class="icon_arrow_right_black"></i>
                        </div>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="mpbtn_total"><i class="view"></i></a>
                        <div class="mp_tooltip" style=" visibility:hidden;">
                            <font id="mpbtn_myMoney" style="font-size:12px; cursor:pointer;">{{ $lang['My_assets'] }}</font>
                            <i class="icon_arrow_right_black"></i>
                        </div>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="mpbtn_history"><i class="zuji"></i></a>
                        <div class="mp_tooltip">
                            <font id="mpbtn_histroy" style="font-size:12px; cursor:pointer;">{{ $lang['My_footprint'] }}</font>
                            <i class="icon_arrow_right_black"></i>
                        </div>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="mpbtn_collection"><i class="wdsc"></i></a>
                        <div class="mp_tooltip">
                            <font id="mpbtn_wdsc" style="font-size:12px; cursor:pointer;">{{ $lang['label_collection'] }}</font>
                            <i class="icon_arrow_right_black"></i>
                        </div>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="mpbtn_email"><i class="email"></i></a>
                        <div class="mp_tooltip">
                            <font id="mpbtn_email" style="font-size:12px; cursor:pointer;">{{ $lang['Email_subscription'] }}</font>
                            <i class="icon_arrow_right_black"></i>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="quick_toggle">
            	<ul>
                    <li>
                        <a id="IM" IM_type="dsc" onclick="openWin(this)" href="javascript:void(0);" ><i class="kfzx"></i></a>
                        <div class="mp_tooltip">{{ $lang['Customer_service_center'] }}<i class="icon_arrow_right_black"></i></div>

                    </li>
                    <li class="returnTop">
                        <a href="javascript:void(0);" class="return_top"><i class="top"></i></a>
                    </li>
                </ul>

            </div>
        </div>
        <div id="quick_links_pop" class="quick_links_pop"></div>
    </div>
</div>
<div class="email_sub">
	<div class="attached_bg"></div>
	<div class="w1200">
        <div class="email_sub_btn">
            <input type="input" id="user_email" name="user_email" autocomplete="off" placeholder="{{ $lang['email_posi'] }}">
            <a href="javascript:void(0);" onClick="add_email_list();" class="emp_btn">{{ $lang['email_list_ok'] }}</a>
            <a href="javascript:void(0);" onClick="cancel_email_list();" class="emp_btn emp_cancel_btn">{{ $lang['email_list_cancel'] }}</a>
        </div>
    </div>
</div>
