<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />
<meta name="Description" content="{{ $description }}" />
<title>{{ $page_title }}</title>
<link rel="shortcut icon" href="favicon.ico" />
@include('frontend::library/js_languages_new')
<link rel="stylesheet" type="text/css" href="{{ skin('css/user.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}" />
</head>

<body>
@include('frontend::library/page_header_common')
<div class="user-content clearfix">
    <div class="user-side" ectype="userSide">

@if($action != 'default')

        <div class="user-perinfo-ny">
            <div class="profile clearfix">
                <div class="avatar">
                    <a href="user.php" class="u-pic">
                        <img src="
@if($user_default_info['user_picture'])
{{ $user_default_info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif
" alt="">
                    </a>
                </div>
                <div class="name">
                    <h2>{{ $user_default_info['nick_name'] }}</h2>

@if($user_default_info['special_rank'])

                    <div class="">{{ $user_default_info['rank_name'] }}</div>

@else

                    <div class="user-rank user-rank-{{ $user_default_info['rank_sort'] ?? 1 }}">{{ $user_default_info['rank_name'] }}</div>

@endif

                </div>
            </div>
        </div>

@endif


@if($action == 'default')

        <div class="user-mod user-perinfo">
            <div class="profile clearfix">
                <div class="avatar">
                    <a href="user.php?act=profile" class="u-pic">
                        <img src="
@if($info['user_picture'])
{{ $info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif
" alt="">
                    </a>
                </div>
                <div class="name">
                    <h2>{{ $info['nick_name'] }}</h2>

@if($user_default_info['special_rank'])

                        <div class="">{{ $user_default_info['rank_name'] }}</div>

@else

                        <div class="user-rank user-rank-{{ $user_default_info['rank_sort'] ?? 1 }}">{{ $user_default_info['rank_name'] }}</div>

@endif

                </div>
            </div>
            <div class="account">
                <div class="item clearfix">
                    <div class="item-name">{{ $lang['account_information'] }}：</div>
                    <div class="item-main">
                        <b class="integrity"><em style="width: {{ $security_rating['Percentage'] }}%;"></em></b><span>{{ $security_rating['Percentage'] }}%</span>
                    </div>
                </div>
                <div class="item clearfix">
                    <div class="item-name">{{ $lang['account_safe'] }}：</div>
                    <div class="item-main safe">
                        <a href="user.php?act=account_safe" class="iconfont icon-email
@if($validate['email_validate'])
active
@endif
"><span class="tip">
@if($validate['email_validate'])
{{ $lang['email_has_validate'] }}
@else
{{ $lang['email_no_validate'] }}
@endif
</span></a>
                        <a href="user.php?act=account_safe" class="iconfont icon-see
@if($validate['real_id'])
active
@endif
"><span class="tip">
@if($validate['real_id'])
{{ $lang['users_real_complete'] }}
@else
{{ $lang['users_real_no_complete'] }}
@endif
</span></a>
                        <a href="user.php?act=account_safe" class="iconfont icon-password active"><span class="tip">{{ $lang['pwd_set'] }}</span></a>
                        <a href="user.php?act=account_safe" class="iconfont icon-mobile-phone
@if($validate['mobile_phone'])
active
@endif
"><span class="tip">
@if($validate['mobile_phone'])
{{ $lang['mobile_validate'] }}
@else
{{ $lang['mobile_no_validate'] }}
@endif
</span></a>
                    </div>
                </div>
            </div>
        </div>

@endif

        <div class="user-mod">
            @include('frontend::library/user_menu')
        </div>
    </div>
    <div class="user-main" ectype="userMain" data-action="
@if($action == 'default')
default
@else
noDefault
@endif
">

@if($action != 'default')

        <div class="user-crumbs hide">
            @include('frontend::library/ur_here')
        </div>

@endif




@if($action == 'default')

        <ul class="user-index-order-statu clearfix">
            <li>
                <a href="user_order.php?act=order_list&order_type=toBe_pay">
                <div class="circle"><i class="iconfont icon-columns"></i></div>
                <div class="info">
                    <p>{{ $lang['piad_not'] }}</p>
                    <div class="num">{{ $pay_count }}</div>
                </div>
                </a>
            </li>
            <li>
                <a href="user_order.php?act=order_list&order_type=toBe_confirmed">
                <div class="circle"><i class="iconfont icon-truck-alt"></i></div>
                <div class="info">
                    <p>{{ $lang['receipt_not'] }}</p>
                    <div class="num">{{ $to_confirm_order }}</div>
                </div>
                </a>
            </li>
            <li>
                <a href="user_message.php?act=comment_list">
                <div class="circle"><i class="iconfont icon-edit"></i></div>
                <div class="info">
                    <p>{{ $lang['comment_not'] }}</p>
                    <div class="num">{{ $signNum }}</div>
                </div>
                </a>
            </li>
            <li>
                <a href="user_order.php?act=order_list&order_type=toBe_finished">
                <div class="circle"><i class="iconfont icon-complete"></i></div>
                <div class="info">
                    <p>{{ $lang['cs']['102'] }}</p>
                    <div class="num">{{ $to_finished }}</div>
                </div>
                </a>
            </li>
        </ul>
        <ul class="user-index-wallet clearfix">
            <li>
                <div class="words"></div>
                <div class="info info-line">
                    <a href="user.php?act=account_log">{{ $info['surplus'] }}</a>
                </div>
            </li>
            @if ($cfg['use_bonus'] == 1)
            <li>
                <div class="words"></div>
                <div class="info info-line">
                    <a href="user_activity.php?act=bonus">{{ $lang['bonus_user'] }}（<span class="red">{{ $info['bonus_count'] }}</span>）</a>

                </div>
            </li>
            @endif
            <li>
                <div class="words"></div>
                <div class="info">
                    <a href="user_activity.php?act=coupons">{{ $lang['preferential'] }}（<span class="red">{{ $coupons['num'] }}</span>）</a><br>
                    <div class="num"><a href="coupons.php?act=coupons_index" target="_blank" class="line">{{ $lang['voucher'] }}</a></div>
                </div>
            </li>
            <li>
                <div class="words"></div>
                <div class="info info-line">
                    <a href="user.php?act=value_card">{{ $lang['value_card'] }}（<span class="red">{{ $value_card['num'] }}</span>）</a>
                </div>
            </li>
            <li>
                <div class="words"></div>
                <div class="info info-line"><a href="user.php?act=account_log">{{ $info['integral'] }}</a></div>
            </li>
        </ul>
        <div class="user-mod">
            <div class="user-section">
                <div class="user-title">
                    <h2>{{ $lang['label_order'] }}</h2>
                    <a href="user_order.php?act=order_list" class="more">{{ $lang['see_all_order'] }}</a>
                </div>
                <div class="user-index-order-list">

@forelse($order_list as $order)

                    <div class="tr">
                        <div class="td td-goods">

@foreach($order['goods'] as $goods)

@if($loop->count > 1)


@if($goods['og_extension_code'] != 'package_buy')


@if($loop->index < 4)

                                    <div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

@endif


@else

                                    <div class="c-img"><a href="{{ url('package.php') }}"><img src="
@if($goods['goods_thumb'])
storage/data/gallery_album/package_goods_default.jpg
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

@endif


@else


@if($goods['og_extension_code'] != 'package_buy')

                                    <div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

@else

                                    <div class="c-img"><a href="{{ url('package.php') }}"><img src="
@if($goods['goods_thumb'])
storage/data/gallery_album/package_goods_default.jpg
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

@endif

                                <div class="c-info">{{ $goods['goods_name'] }}</div>

@endif


@endforeach



@if($loop->iteration > 4)

                            <div class="ellipsis">....</div>

@endif

                        </div>
                        <div class="td td-price">{{ $order['total_fee'] }}</div>
                        <div class="td td-name">{{ $order['consignee'] }}</div>
                        <div class="td td-statu">{{ $order['order_status'] }}</div>
                        <div class="td td-handle">
                            <a href="user_order.php?act=order_detail&order_id={{ $order['order_id'] }}" class="sc-btn">{{ $lang['order_detail'] }}</a>
                        </div>
                    </div>

@empty

                    <div class="no_records">
                        <i class="no_icon"></i>
                        <div class="no_info no_info_line">
                            <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                            <div class="no_btn">
                                <a href="index.php" class="sc-btn sc-red-btn">{{ $lang['goto_mall'] }}</a>
                            </div>
                        </div>
                    </div>

@endforelse

                </div>
            </div>

            <div class="user-section">
                <div class="user-title">
                    <h2>{{ $lang['Recent_collection'] }}</h2>
                    <a href="user_collect.php?act=collection_list" class="more">{{ $lang['see_all_Collection'] }}</a>
                </div>
                <div class="user-index-collection-list">

@forelse($collection_goods as $goods)

                    <div class="tr">
                        <div class="td td-goods">
                            <a href="{{ $goods['url'] }}" class="img"><img src="{{ $goods['goods_thumb'] }}" alt=""></a>
                            <a href="{{ $goods['url'] }}" class="name">{{ $goods['goods_name'] }}</a>
                        </div>
                        <div class="td td-price">

@if($goods['promote_price'] != '')

                                {{ $goods['promote_price'] }}

@else

                                {{ $goods['shop_price'] }}

@endif

                        </div>
                        <div class="td td-shop">
                            <a href="{{ $goods['shop_url'] }}" class="name">{{ $goods['shop_name'] }}</a>
                            <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  goods_id="{{ $goods['goods_id'] }}" class="iconfont icon-kefu user-shop-kefu"></a>
                        </div>
                        <div class="td td-handle"><a href="{{ $goods['url'] }}" class="sc-btn">{{ $lang['button_buy'] }}</a></div>
                    </div>

@empty

                    <div class="no_records">
                        <i class="no_icon"></i>
                        <div class="no_info">
                            <h3>{{ $lang['new_Collection_store_null'] }}</h3>
                        </div>
                    </div>

@endforelse

                </div>
            </div>

@if($collection_goods)

@endif


@if($guess_goods)

            <div class="user-section">
                <div class="user-title">
                    <h2>{{ $lang['guess_love'] }}</h2>
                </div>
                <ul class="user-goods-list clearfix">

@foreach($guess_goods as $goods)

                    <li>
                        <a href="{{ $goods['url'] }}" class="img"><img src="{{ $goods['goods_thumb'] }}" alt=""></a>
                        <a href="" class="title">{{ $goods['short_name'] }}</a>
                        <div class="price">{{ $goods['shop_price'] }}</div>
                    </li>

@endforeach

                </ul>
            </div>

@endif


@if($helpart_list)

            <div class="user-section">
                <div class="user-title">
                    <h2>{{ $lang['calendar_help'] }}</h2>
                </div>
                <ul class="user-help-list clearfix">

@foreach($helpart_list as $help)

                    <li><a href="{{ $help['url'] }}" class="ftx-05" target="_blank">{{ $help['title'] }}</a></li>

@endforeach

                </ul>
            </div>

@endif

        </div>

@endif





@if($action == 'booking_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_booking'] }}</h2>
            </div>
            <div class="user-booking-list">

@forelse($booking_list as $item)

                <dl class="item">
                    <dt class="item-t">
                        <div class="t-handle">
                            <a href="javascript:void(0);" ectype="goods_del_booking" data-url="user.php?act=act_del_booking&id={{ $item['rec_id'] }}" class="sc-btn">{{ $lang['booking_cancel'] }}</a>
                        </div>
                        <a href="{{ $item['url'] }}" class="t-img"><img src="{{ $item['goods_thumb'] }}" alt=""></a>
                        <div class="t-info">
                            <div class="info-name"><a href="{{ $item['url'] }}">{{ $item['goods_name'] }}</a></div>
                            <p class="info-num">{{ $lang['booking_amount'] }}：<span class="red">{{ $item['goods_number'] }}</span></p>
                        </div>
                    </dt>
                    <dd class="item-c">
                        <div class="fl">{{ $lang['process_desc'] }}：<span class="ftx-05">{{ $item['dispose_note'] }}</span></div>
                        <div class="fr">{{ $item['booking_time'] }}</div>
                    </dd>
                </dl>

@empty

                <div class="no_records">
                    <i class="no_icon"></i>
                    <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
                </div>

@endforelse

            </div>
        </div>

@endif





@if($action == 'add_booking')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_booking'] }}</h2>
            </div>
            <form action="user.php" method="post" class="user-form" name="formBooking" id="formBooking">
                <div class="form-row">
                    <div class="form-label">{{ $lang['booking_goods_name'] }}：</div>
                    <div class="form-value">{{ $info['goods_name'] }}</div>
                </div>
                <div class="form-row">
                    <div class="form-label"><em class="red">*</em>{{ $lang['booking_amount'] }}：</div>
                    <div class="form-value">
                        <input name="number" type="text" value="{{ $info['goods_number'] }}" class="form-input" />
                        <div class="form_prompt"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><em class="red">*</em>{{ $lang['describe'] }}：</div>
                    <div class="form-value">
                    <textarea name="desc" cols="80" rows="20" wrap="virtual" class="textarea">{{ $goods_attr }}{{ $info['goods_desc'] }}</textarea>
                    <div class="form_prompt"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><em class="red">*</em>{{ $lang['contact_username'] }}：</div>
                    <div class="form-value"><input name="linkman" type="text" value="{{ $info['consignee'] }}" size="25"  class="form-input"/><div class="form_prompt"></div></div>
                </div>
                <div class="form-row">
                    <div class="form-label"><em class="red">*</em>{{ $lang['email_address'] }}：</div>
                    <div class="form-value"><input name="email" type="text" value="{{ $info['email'] }}" size="25" class="form-input" /><div class="form_prompt"></div></div>
                </div>
                <div class="form-row">
                    <div class="form-label"><em class="red">*</em>{{ $lang['contact_phone'] }}：</div>
                    <div class="form-value"><input name="tel" type="text" value="{{ $info['tel'] }}" size="25" class="form-input" /><div class="form_prompt"></div></div>
                </div>
                <div class="form-btn-wp">
                    <input name="act" type="hidden" value="act_add_booking" />
                    <input name="id" type="hidden" value="{{ $info['id'] }}" />
                    <input name="rec_id" type="hidden" value="{{ $info['rec_id'] }}" />
                    <input type="button" class="form-btn" value="{{ $lang['submit_booking_goods'] }}" id="submitBtn" />
                    <input type="reset" name="reset" class="form-btn form-btn-gray" value="{{ $lang['button_reset'] }}" />
                </div>
            @csrf </form>
        </div>

@endif





@if($action == 'collection_list' ||  $action == 'store_list'||  $action == 'focus_brand')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_collection'] }}</h2>
                <ul class="tabs">
                    <li
@if($action == 'collection_list')
class="active"
@endif
><a href="user_collect.php?act=collection_list">{{ $lang['Collection_goods'] }}</a></li>
                    <li
@if($action == 'store_list')
class="active"
@endif
><a href="user_collect.php?act=store_list">{{ $lang['store_list'] }}</a></li>
                    <li
@if($action == 'focus_brand')
class="active"
@endif
><a href="user_collect.php?act=focus_brand">{{ $lang['focus_brand'] }}</a></li>
                </ul>
            </div>
            <div class="collection-list-warp clearfix c-tab-box-ajax">

@if($action == 'collection_list')

                @include('frontend::library/collection_goods_list')

@elseif ($action == 'store_list')

                @include('frontend::library/collection_store_list')

@elseif ($action == 'focus_brand')

                @include('frontend::library/collection_brands_list')

@endif

            </div>


@if($count > $size)

            <div class="clearfix" id="pages_ajax">
                <div class="pages">{!! $pager !!}</div>
            </div>

@endif


        </div>

@endif





@if($action == 'focus_brand')



@endif





@if($action == 'message_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_opinion_suggestion'] }}</h2>
            </div>
            <div class="user-message-warp clearfix">
                <form action="user.php" method="post" enctype="multipart/form-data" name="formMsg">
                    <div class="user-items">

@if($order_info)

                        <div class="item">
                            <div class="label">{{ $lang['order_number'] }}：</div>
                            <div class="value value-checkbox">
                                <div class="txt-lh"><a href="{{ $order_info['url'] }}"><img src="{{ skin('images/note.gif') }}" style="margin-top:-3px;"/>&nbsp;&nbsp;{{ $order_info['order_sn'] }}</a></div>
                            </div>
                        </div>
                        <input name="msg_type" type="hidden" value="5" />
                        <input name="order_id" type="hidden" value="{{ $order_info['order_id'] }}" />

@else

                        <div class="item">
                            <div class="label"><em class="red">*</em>&nbsp;{{ $lang['message_type'] }}：</div>
                            <div class="value">
                                <div class="radio-item">
                                    <input type="radio" checked="" name="msg_type" class="ui-radio" id="checkbox-message" value="0">
                                    <label for="checkbox-message" class="ui-radio-label">{{ $lang['type'][0] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="msg_type" class="ui-radio" id="checkbox-complaint" value="1">
                                    <label for="checkbox-complaint" class="ui-radio-label">{{ $lang['type'][1] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="msg_type" class="ui-radio" id="checkbox-ask" value="2">
                                    <label for="checkbox-ask" class="ui-radio-label">{{ $lang['type'][2] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="msg_type" class="ui-radio" id="checkbox-customer" value="3">
                                    <label for="checkbox-customer" class="ui-radio-label">{{ $lang['type'][3] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="msg_type" class="ui-radio" id="checkbox-buy" value="4">
                                    <label for="checkbox-buy" class="ui-radio-label">{{ $lang['type'][4] }}</label>
                                </div>
                            </div>
                        </div>

@endif

                        <div class="item">
                            <div class="label"><em class="red">*</em>&nbsp;{{ $lang['message_title'] }}：</div>
                            <div class="value">
                                <input type="text" name="msg_title" class="text text-2">
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>&nbsp;{{ $lang['message_content'] }}：</div>
                            <div class="value">
                                <textarea name="msg_content" class="textarea textarea2"></textarea>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <div class="value-file">
                                    <input type="button" class="sc-btn sc-redBg-btn" value="{{ $lang['Select_file'] }}">
                                    <input type="text" name="textfield" id="textfield" class="txt">
                                    <input type="file" name="message_img" class="file" id="fileField" size="28" onchange="document.getElementById('textfield').value=this.value">
                                </div>
                                <span class="remind">{{ $lang['message_type_list'] }}{{ $lang['message_remind'] }}{{ $upload_size_limit }}。</span>
                            </div>
                        </div>

@if($enabled_captcha)

                        <div class="item">
                            <div class="label"><em class="red">*</em>&nbsp;{{ $lang['comment_captcha'] }}：</div>
                            <div class="item-value">
                                <div class="captcha_input">
                                    <input type="text" class="text w100" id="captcha" name="captcha">
                                    <img src="captcha_verify.php?captcha=is_common&{{ $rand }}" alt="captcha" class="captcha_img" onClick="this.src='captcha_verify.php?captcha=is_common&'+Math.random()" data-key="captcha_common" />
                                </div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>

@endif

                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">

@if($is_order == 1)

                                <input type="hidden" name="is_order" value="1" />

@endif

                                <input type="hidden" name="act" value="act_add_message" />
                                <input type="button" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_goods'] }}" id="pingjia_form">
                            </div>
                        </div>
                    </div>
                @csrf </form>
            </div>

            <div class="user-title">
                <h2>{{ $lang['label_message'] }}</h2>
                <ul class="tabs">
                    <li
@if($is_order == 0)
class="active"
@endif
><a href="user_message.php?act=message_list">{{ $lang['general_message'] }}</a></li>

<li
@if($is_order == 1)
class="active"
@endif
><a href="user_message.php?act=message_list&is_order=1">{{ $lang['order_message'] }}</a></li>

                </ul>
            </div>
            <div class="user-message-list">

@forelse($message_list as $key => $message)

                <div class="m-item
@if($loop->last)
 last
@endif
">
                    <div class="u-ico"><img src="
@if($user_id)

@if($user_info['user_picture'])
{{ $user_info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif

@else
{{ skin('/images/avatar.png') }}
@endif
"></div>
                    <div class="ud-right">
                        <div class="m-tit">
                            <h3>{{ $message['msg_title'] }}</h3>
                            <span>

@if($message['msg_type'] == $lang['label_message'] || $message['msg_type'] == $lang['label_complaint'] || $message['msg_type'] == $lang['label_enquiry'] || $message['msg_type'] == $lang['label_custome'] || $message['msg_type'] == $lang['label_buy'])

                                {{ $message['msg_type'] }}

@else

                                {{ $lang['your_message'] }}

@endif

                            </span>

@if($is_order == 1)
<div class="fl ml5">{{ $lang['order_number'] }}:{{ $message['order_sn'] }}</div>
@endif

                            <div class="fr">

@if($is_order == 1)

                                    <a href="user_message.php?act=del_msg&amp;id={{ $key }}&amp;is_order=1&amp;order_id={{ $message['order_id'] }}" onclick="if (!confirm('{{ $lang['confirm_remove_msg'] }}')) return false;" class="ftx-05 fr ml0">{{ $lang['drop'] }}</a>

@else

                                    <a href="user_message.php?act=del_msg&amp;id={{ $key }}&amp;order_id={{ $message['order_id'] }}" onclick="if (!confirm('{{ $lang['confirm_remove_msg'] }}')) return false;" class="ftx-05 fr ml0">{{ $lang['drop'] }}</a>

@endif

                                <em class="mr10">{{ $message['msg_time'] }}</em>
                            </div>
                        </div>
                        <div class="txt">{{ $message['msg_content'] }}</div>

@if($message['message_img'])

                        <div class="fr">
                            <a href="{{ $message['message_img'] }}"
@if($message['message_type'] == 1)
class="nyroModal"
@endif
>{{ $lang['view_upload_file'] }}</a>
                        </div>

@endif


@if($message['re_msg_content'])

                        <div class="txt">
                            <a  target="_bank" class="ftx-03" >{{ $lang['shopman_reply'] }}</a> ({{ $message['re_msg_time'] }})<br />
                            {{ $message['re_msg_content'] }}
                        </div>

@endif

                    </div>
                </div>
@empty
                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info no_info_line">
                        <h3>{{ $lang['not_data'] }}</h3>
                    </div>
                </div>
@endforelse

            </div>
            @include('frontend::library/pages')

        </div>

@endif

@if($action == 'notification')

         <style>
                              .my_msg_list_view {
                      position: relative;
                      background-color: #fff;
                      padding: 24px 16px;
                     padding-top: 0;
                  }

                  .my_msg_list_box {
                      border: 0;
                      margin: 0;
                      padding: 0;
                      box-sizing: border-box;
                  }

                  .my_msg_list_con {
                      border: 0;
                      margin: 0;
                      padding: 0;
                      box-sizing: border-box;
                  }

                  .my_msg_list_title {

                      font-size: 14px;
                      color: #8c8c8c;

                  }
                  .data-time{
                      color: #8c8c8c;
                  }
                  .fr {
                      float: right !important;
                      display: inline-block;
                  }

                  .line {
                      display: inline-block;
                      height: 12px;
                      margin: 2px 12px;
                      border-right: 1px solid #979797;
                  }

                  .msg_list {
                     /* padding: 15px;*/
                      /* min-height: 370px; */
                      box-sizing: border-box;
                  }

                  .msg_list_ul {
                      font-size: 14px;
                      vertical-align: baseline;
                      margin: 0;
                      padding: 0;
                  }

                  .msg_list_ul_li {
                      position: relative;
                      padding: 16px;
                      border-bottom: 1px solid rgba(235, 235, 235, 1);
                  }

                  .msg_list_ul_li input {
                      position: absolute;
                      top: 0;
                      bottom: 0;
                      margin: auto 0;
                      z-index: 999999;
                  }

                  .my_msg_list_title {
                      position: relative;
                      padding:15px 16px;
                      /* border-bottom: 1px solid #e0e0e0; */
                  }

                  .my_msg_list_title input {
                      position: absolute;
                      top: 0;
                      bottom: 0;
                      margin: auto 0;
                      margin-bottom: 3px;
                  }

                  .msg_info_box {
                      width: 70%;
                      margin-left: 30px;
                      color: #8c8c8c;
                      display: inline-block;
                      white-space: nowrap;
                      overflow: hidden;
                      text-overflow: ellipsis;
                      vertical-align: middle;
                  }

                  .msg_title {
                      display: inline-block;
                      width: 85%;
                      white-space: nowrap;
                      overflow: hidden;
                      text-overflow: ellipsis;
                      vertical-align: middle;
                  }

                  .options-f {
                      width: 20%;
                      height: 20px;
                      margin: 0;
                      /* margin-top: -20px; */
                      padding: 0;
                  }

                  .msg_delete {
                      width: 10px;
                      height: 10px;
                      float: right;
                  }

                  input[type="checkbox"] {
                      width: 11px;
                      height: 11px;
                      display: inline-block;
                      text-align: center;
                      vertical-align: middle;
                      line-height: 10px;
                      position: relative;
                  }

                  input[type="checkbox"]::before {
                      content: "";
                      position: absolute;
                      top: 0;
                      left: 0;
                      background: #fff;
                      width: 100%;
                      height: 100%;
                      border: 1px solid #d2d2d2
                  }
                  .rd{
                      position: absolute;
                      width: 8px;
                      height: 8px;
                      top: 25px;
                      left: 40px;
                      border-radius: 50%;
                      background-color: red;
                  }
                  input[type="checkbox"]:checked::before {
                      content: "\2713";
                      background-color: #fff;
                      position: absolute;
                      top: 0;
                      left: 0;
                      width: 100%;
                      border: 1px solid #d2d2d2;
                      color: #8c8c8c;
                      font-size: 11px;
                      font-weight: bold;
                  }
                  .yidu{
                     font-size: 12px;
                  /* padding: 1px 5px; */
                     border: 1px solid #B2B5BD;
                     color: #2a2a3a;
                     margin-left: 10px;
                     background: #FFFFFF;
                     width: 70px;
                     height: 26px;
                     border-radius: 3px;
                  }
                      button[disabled]{

                      border: 1px solid #DFE1E7;

                      background-color: #F5F5F7;

                      color:#BABDC4;

                  }

                      .msg_list_ul_li div.price{ display: none;}
                      .msg_list_ul_li:hover  div.price > .content {width:60px;height: 20px;position: absolute;top: 0;right: 50px;bottom: 0;margin: auto 0;}
                      .msg_list_ul_li:hover  div.price > .content img{width: 20px;height: 20px;margin-right: 3px;margin-left: 3px;}
                      .msg_list_ul_li:hover  div.price{ width: 100%; height: 100%; display: block; background: #F4F5F6; opacity: .5; -moz-opacity: .5; filter:alpha(opacity=50); position: absolute; top: 0; left: 0;cursor:pointer;}
                      .content .iconfont{
                        padding-left: 10px
                      }
                      .details{
                        width: 70%;
                        height: 100%;
                      }
              </style>
                          <div class="user-mod" id="app">
                           <div class="user-title">
                            <h2>{{ $lang['notification'] }}</h2>
                           </div>
                              <div class="my_msg_list_view" v-if="sessions.length>0">
                                  <div class="my_msg_list_box">
                                      <div class="my_msg_list_con">
                                          <div class="msg_list">
                                              <ul class="msg_list_ul">
                                                  <li class="msg_list_ul_li" v-for="(item,index) in sessions" :key="index">
                                                      <input type="checkbox" :checked="sportsIds.indexOf(item.id)>-1" @click="checkOne(item.id,item.uuid)" />
                                                      <i class="rd" v-if="item.unread==true"></i>
                                                      <span class="msg_info_box">
                                                          <span class="msg_title" v-html="item.last_message"></span>
                                                      </span>
                                                      <div class="fr options_info options-f">
                                                          <span class="data-time" v-html="item.last_time"></span>
                                                      </div>
                                                      <div class="price">
                                                          <div @click="jump(item.goods_id,item.shop_id,item.uuid)" class="details"></div>
                                                          <div class="content">
                                                             <i class="iconfont iconfont icon-gou" @click="onChats(item.uuid)" v-if="item.unread==true"></i>
                                                             <i class="iconfont iconfont icon-remove-alt" @click="delect(item.id)"></i>
                                                          </div>
                                                      </div>
                                                  </li>

                                              </ul>
                                          </div>
                                          <div class="my_msg_list_title">
                                              <input type="checkbox" id="quanxuan" @click="checkAll()" />
                                              <button class="yidu" @click="onChats()" :disabled="!sportsIds.length>0">已读</button>
                                              <button class="yidu" @click="delect()" :disabled="!sportsIds.length>0">删除</button>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                            <div class="no_records" v-if="isRouterAlive">
                               <i class="no_icon"></i>
                             <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
                          </div>
                          </div>
                          <script src="{{ asset('/js/vue.min.js') }}"></script>
                          <script>
              var token = '{{ $token }}';
              var vm = new Vue({
                  el: '#app',
                  data() {
                      return {
                          isCheckAll: false,
                          sessions: [],
                          sportsIds:[],
                          isRouterAlive:false,
                          uuids:[]
                      }
                  },
                  created() {
                      this.getlist()
                  },
                  methods: {
                      jump(goods_id,shop_id,uuid){
                        var that=this
                         that.uuids=[]
                         that.uuids.push(uuid);
                         that.onChats();
                         window.location.href="{{ url('/') }}/online.php?act=service&goods_id="+goods_id+"&ru_id="+shop_id;

                      },
                      getlist() {
                        var that=this
                          $.ajax({
                              url: "{{ url('/api/chat/sessions') }}",
                              method: 'GET',
                              dataType: "json", //返回格式为json
                              headers: {
                                  'token': token,
                                  'Content-Type': 'application/json'
                              },
                              data: {
                                  page: 1,
                                  size: 10
                              },
                              success: function(res) {
                                  that.sessions = res.data
                                  if(that.sessions.length>0){
                                    that.isRouterAlive=false

                                  }else{
                                    that.isRouterAlive=true
                                  }
                              },
                              error: function(err) {
                                  console.log(err);
                              }
                          })

                      },
                        onChats(uuid) {
                        var that=this
                         if(uuid){
                            that.uuids=[]
                            that.uuids.push(uuid);
                         }
                         let id = ''

                         if(that.uuids.length > 1){
                            id = that.uuids.join(',')
                         } else {
                            id = that.uuids[0]
                         }
                          $.ajax({
                              url: "{{ url('/api/chat/session/mark') }}",
                              method: 'POST',
                              dataType: "json", //返回格式为json
                              headers: {
                                  'token': token,
                                  'Content-Type': 'application/x-www-form-urlencoded'
                              },
                              data: {
                                  uuid: id
                              },
                              success: function(res) {
                                  that.getlist()
                                  // this.sessions = res.data.data
                              },
                              error: function(err) {
                                  console.log(err);
                              }
                          })

                      },
                        delect(id) {
                        var that=this
                       if(id){
                         that.sportsIds=[]
                         that.sportsIds.push(id);
                         }
                          let ids = ''

                         if(that.uuids.length > 1){
                            ids = that.sportsIds.join(',')
                         } else {
                            ids = that.sportsIds[0]
                         }
                          $.ajax({
                              url: "{{ url('/api/chat/session/destroy') }}",
                              method: 'POST',
                              dataType: "json", //返回格式为json
                              headers: {
                                  'token': token,
                                  'Content-Type': 'application/x-www-form-urlencoded'
                              },
                              data: {
                                   id: ids
                                   // id: 1
                              },
                              success: function(res) {
                                  that.getlist()
                                  // this.sessions = res.data.data
                              },
                              error: function(err) {
                                  console.log(err);
                              }
                          })

                      },
                         checkAll: function() {
                              var that=this
                               that.isCheckAll = !that.isCheckAll;
                              if (that.isCheckAll) {
                                  that.sportsIds = []
                                   that.uuids=[]
                                  for (var i = 0; i < that.sessions.length; i++) {
                                       that.sportsIds.push(that.sessions[i].id);
                                       that.uuids.push(that.sessions[i].uuid);
                                  }
                              } else {
                               that.sportsIds = []
                               that.uuids=[]
                              }
                          },
                          checkOne: function(sportsId,uuid) {
                            var that=this
                              let idindex = that.sportsIds.indexOf(sportsId);
                              if (idindex >= 0) {
                                  //如果以包含了该id,则去除(变为非选中状态)
                                  that.sportsIds.splice(idindex, 1);
                                  that.uuids.splice(idindex, 1);
                              } else {
                                  that.sportsIds.push(sportsId);
                                   that.uuids.push(uuid);
                              }
                          }
                  }

              })

              </script>

@endif





@if($action == 'affiliate')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['affiliate_codetype_step'] }}</h2>
            </div>
            <div class="affiliate-step-warp clearfix">
                    <div class="a-items">
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-1"></i>
                            <span>1 {{ $lang['affiliate_step']['0'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-2"></i>
                            <span>2 {{ $lang['affiliate_step']['1'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-3"></i>
                            <span>3 {{ $lang['affiliate_step']['2'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-4"></i>
                            <span>4 {{ $lang['affiliate_step']['3'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="affiliate-mode">
                    <div class="mode-item">
                            <div class="tit mt10 mb30">
                            <span class="type">{{ $lang['way_one'] }}</span>
                            <strong>{{ $lang['invitation_link'] }}</strong>
                            <strong>{{ $lang['invitation_link_notic'] }}</strong>
                        </div>
                        <div class="cont">
                            <div class="aff-form">
                             <div id="clip_container" class="relative">
                                   <a href="javascript:void(0);" class="sc-btn sc-redBg-btn" id="clip_button">{{ $lang['code_copy'] }}</a>
                                </div>
                                <textarea class="aff-textarea" name="" id="affTextarea" value='{{ $shopurl }}?u={{ $userid }}'>{{ $shopurl }}?u={{ $userid }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="user-title">
                <h2>{{ $lang['affiliate_member'] }}</h2>
            </div>
            <div class="affiliate-list-warp clearfix">
                    <table class="user-table user-table-baitiao">
                    <colgroup>
                        <col width="214">
                        <col width="214">
                        <col width="214">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="tc">{{ $lang['affiliate_lever'] }}</th>
                            <th>{{ $lang['affiliate_num'] }}</th>
                            <th>{{ $lang['level_point'] }}（%）</th>
                            <th>{{ $lang['level_money'] }}（%）</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($affdb as $level => $val)

                        <tr>
                            <td class="tc">{{ $level }}</td>
                            <td class="tc">{{ $val['num'] }}</td>
                            <td class="tc">{{ $val['point'] }}</td>
                            <td class="tc">{{ $val['money'] }}</td>
                        </tr>

@endforeach

                    </tbody>
                </table>
            </div>

@if($logdb)

            <div class="user-title">
                <h2>{{ $lang['Split_rule'] }}</h2>
            </div>
            <div class="affiliate-list-warp clearfix">
                    <table class="user-table user-table-baitiao">
                    <colgroup>
                        <col width="214">
                        <col width="214">
                        <col width="214">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="tc">{{ $lang['order_number'] }}</th>
                            <th>{{ $lang['affiliate_money'] }}</th>
                            <th>{{ $lang['affiliate_point'] }}</th>
                            <th>{{ $lang['affiliate_mode'] }}</th>
                            <th>{{ $lang['affiliate_status'] }}</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($logdb as $val)

                        <tr>
                            <td class="tc">{{ $val['order_sn'] }}</td>
                            <td class="tc">{{ $val['money'] }}</td>
                            <td class="tc">{{ $val['point'] }}</td>
                            <td class="tc">
@if($val['separate_type'] == 1 || $val['separate_type'] === 0)
{{ $lang['affiliate_type'][$val['separate_type']] }}
@else
{{ $lang['affiliate_type'][$affiliate_type] }}
@endif
</td>
                            <td class="tc">{{ $lang['affiliate_stats'][$val['is_separate']] }}</td>
                        </tr>

@endforeach

                    </tbody>
                </table>
            </div>
            @include('frontend::library/pages')

@endif

        </div>

@endif





@if($action == 'drp_affiliate')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['my_recommended'] }}</h2>
            </div>
            <div class="affiliate-step-warp clearfix">
            <div class="tips-one btn25">
            <i>提示:</i>两种推广方式，你可根据自己需求，自己选择一种方式进行推广。
            <a href="javascript:void(0);" class="login-btn">邀请流程>></a>
            </div>
                   <!--  <div class="a-items">
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-1"></i>
                            <span>1 {{ $lang['affiliate_step']['0'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-2"></i>
                            <span>2 {{ $lang['affiliate_step']['1'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-3"></i>
                            <span>3 {{ $lang['affiliate_step']['2'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-4"></i>
                            <span>4 {{ $lang['affiliate_step']['3'] }}</span>
                        </div>
                    </div>
                </div> -->
                <div class="affiliate-mode">
                    <div class="mode-item">
                        <div class="tit mt10">
                            <span class="type">{{ $lang['way_one'] }}</span>
                            <strong>{{ $lang['download_qrcode'] }}</strong>
                            <strong>{{ $lang['download_qrcode_strong'] }}</strong>
                        </div>
                    </div>
                </div>
                <div class="cont">
                    <div class="c-img">
                    <img src="{{ $qrcode }}" alt="">
                    </div>
                    <div id="clip_container" class="recommend">
                       <a class="sc-btn sc-redBg-btn"  href="{{ $qrcode }}" download="">{{ $lang['download_qrcode'] }}</a>
                    </div>
                </div>

                <div class="affiliate-mode">
                    <div class="mode-item">
                            <div class="tit mt10 mb30">
                            <span class="type">{{ $lang['way_tow'] }}</span>
                            <strong>{{ $lang['url_copy'] }}</strong>
                            <strong>{{ $lang['url_copy_strong'] }}</strong>
                        </div>
                        <div class="cont">
                            <div class="aff-form">
                             <div id="clip_container" class="relative">
                                    <a href="javascript:void(0);" class="sc-btn sc-redBg-btn" id="clip_button">{{ $lang['url_copy'] }}</a>
                                </div>
                                <textarea class="aff-textarea" name="" id="affTextarea" value='{{ $shopurl }}'>{{ $shopurl }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="user-title">
                <h2>{{ $lang['affiliate_member'] }}</h2>
            </div>
            <div class="account-open-list">
                <div class="user-frame-items">
                    <div class="af_item">
                        <span>{{ $lang['invited_friends'] }}</span>
                        <span class="b-price">{{ $user_count['user_child_num'] }}</span>
                    </div>
                    <div class="af_item">
                        <span>{{ $lang['total_amount'] }}</span>
                        <span class="b-price">{{ $user_count['total_drp_order_amount'] }}</span>
                    </div>
                    <div class="af_item">
                        <span>{{ $lang['today_total'] }}</span>
                        <span class="b-price">{{ $user_count['today_drp_log_money'] }}</span>
                    </div>
                    <div class="af_item">
                        <span>{{ $lang['total_commission'] }}</span>
                        <span class="b-price">{{ $user_count['total_drp_log_money'] }}</span>
                    </div>
                    <div class="af_item">
                        <span>{{ $lang['withdrawal_commission'] }}</span>
                        <span class="b-price">{{ $user_count['shop_money'] }}</span>
                    </div>
                </div>
            </div>

            <div class="user-title mt30">
                <h2>{{ $lang['reward_subsidiary'] }}</h2>
            </div>
            <div class="user-title">
                <ul class="tabs">
                    <li class="active"><a href="user.php?act=drp_affiliate">{{ $lang['Registration_incentives'] }}</a></li>

@if($is_drp > 0)

                    <li class="user-count3" onclick="user_drp_order('.user-count3')">
                        <a href="javascript:void(0);">{{ $lang['sales_bonus'] }}</a>
                        <input name="is_going" id="is_going" type="hidden" value="1">
                    </li>

@if($is_vip > 0)

                    <li class="user-count1" onclick="user_drp_card_order('.user-count1')">
                        <a href="javascript:void(0);">{{ $lang['card_rewards'] }}</a>
                        <input name="is_finished" id="is_finished" type="hidden" value="3">
                    </li>

@endif


@endif

                </ul>
            </div>


            <div id="user-affiliate-list">

@if($logdb)

                    <div class="affiliate-list-warp clearfix">
                            <table class="user-table user-table-baitiao">
                            <colgroup>
                                <col width="214">
                                <col width="214">
                                <col width="214">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="tc">{{ $lang['user_info'] }}</th>
                                    <th>{{ $lang['order_number'] }}</th>
                                    <th>{{ $lang['obtain_commission'] }}</th>
                                    <th>{{ $lang['affiliate_status'] }}</th>
                                </tr>
                            </thead>
                            <tbody>

@foreach($logdb as $val)

                                <tr>
                                    <td class="tc">
                                        <a href="#" class="img t-img"><img src="{{ $val['user_picture'] }}" alt=""></a>
                                        <a href="#" class="name">{{ $val['user_name'] }}</a>

@if($val['is_vip'] ==1 )

                                            <text class="namevip">VIP</text>

@endif

                                    </td>
                                    <td class="tc">{{ $val['order_sn'] }}</td>
                                    <td class="tc">{{ $val['money'] }}</td>
                                    <td class="tc">{{ $lang['affiliate_stats'][$val['is_separate']] }}</td>
                                </tr>

@endforeach

                            </tbody>
                        </table>
                    </div>
                    @include('frontend::library/pages')

@else

                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info no_info_line">
                            <h3>{{ $lang['not_data'] }}</h3>
                        </div>
                    </div>

@endif

            </div>
            <div class="popup-content" style="display: none;">
             <a href="javascript:void (0);" class="close"></a>
              <div class="user-modpop">
                <div class="user-title">
                <h2>邀请流程</h2>
                 </div>
                <div class="affiliate-step-warp clearfix">
                    <div class="a-items">
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-1"></i>
                            <span>1 {{ $lang['affiliate_step']['0'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-2"></i>
                            <span>2 {{ $lang['affiliate_step']['1'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-3"></i>
                            <span>3 {{ $lang['affiliate_step']['2'] }}</span>
                        </div>
                        <i class="iconfont icon-right"></i>
                    </div>
                    <div class="a-item">
                            <div class="a-item-info">
                            <i class="item-icon item-icon-4"></i>
                            <span>4 {{ $lang['affiliate_step']['3'] }}</span>
                        </div>
                    </div>
                </div>
             </div>
             </div>
              </div>

<div class="login-bg" style="display: none;"></div>


            <script type="text/javascript">
             $(function () {
             $(".login-btn").on("click", function () {
             $(".popup-content").show();
             $(".login-bg").show();
              });
             $(".login-bg, .close").on("click", function () {
              $(".login-bg").hide();
              $(".popup-content").hide();
               });
              });
            function user_drp_order(c){
                if(c){
                        $(c).addClass("active").siblings().removeClass("active");
                    }
                    Ajax.call('user.php?act=user_drp_order', '', auctionResponse, 'POST', 'JSON');

                }
                function user_drp_orderPage(page){
                    Ajax.call('user.php?act=user_drp_order', 'page=' + page, auctionResponse, 'POST', 'JSON');
                }
                function user_drp_card_order(c){
                    if(c){
                        $(c).addClass("active").siblings().removeClass("active");
                    }
                    Ajax.call('user.php?act=user_drp_card_order', '', auctionResponse, 'POST', 'JSON');
                }
                function user_drp_card_orderPage(page){
                    Ajax.call('user.php?act=user_drp_card_order', 'page=' + page, auctionResponse, 'POST', 'JSON');
                }

                function auctionResponse(result){
                    if(result.error == 0){
                        $("#user-affiliate-list").html(result.content);
                    }
                }
            </script>

        </div>

@endif


@if($action == 'sales_reward_detail')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['reward_details'] }}</h2>
            </div>
            <div class="affiliate-step-warp clearfix">
                <div class="user-order-listone">
                    <dl class="item-on">
                        <dt class="item-t-qb">
                            <div class="t-statu">
                                <span class="t-statu-name" id="ss_received_9">{{ $lang['divided_into_state'][$order_info['is_separate']] }}</span>
                            </div>
                            <div class="t-info">
                                <span class="info-item">{{ $lang['order_number'] }}：
                                    @if($order_info['log_type'] == 0)
                                        <a href="user_order.php?act=order_detail&order_id={{ $order_info['order_id'] }}">{{ $order_info['order_sn'] }}</a>
                                    @else
                                        {{ $order_info['order_sn'] }}
                                    @endif
                                </span>
                            </div>
                            <div class="t-price">
                            {{ __('user.obtain_commission') }}：<span class="totalvalue">{{ $order_info['money_format'] }}</span>
                            </div>
                        </dt>
                    </dl>
                </div>

                <div class="user-info-listone">
                    <div class="info-content">
                        <div class="info-item">
                            <div class="item-label">{{ $lang['Buy_information'] }}：</div>
                            <div class="item-value">{{ $order_info['buy_user_name'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['order_addtime'] }}：</div>
                            <div class="item-value">{{ $order_info['add_time_format'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt10 pb10">
                    <a href="{{ $order_info['shop_url'] ?? '' }}" class="user-shop-linkone">{{ $order_info['shop_name'] }}</a>
                </div>

                <table class="user-table user-table-detail-goods">
                    <thead>
                        <tr>
                            <th width="35%">{{ __('user.goods') }}</th>
                            <th class="tc">{{ __('user.good_number') }}</th>
                            <th class="tc">{{ __('user.drp_goods_price') }}</th>
                            @if($order_info['log_type'] == 0)
                            <th class="tc">{{ __('user.commission_ratio') }}</th>
                            <th class="tc">{{ __('user.drp_money') }}</th>
                            @endif
                            <th class="tc">{{ __('user.level_per') }}</th>
                            <th width="15%" class="tc">{{ __('user.obtain_commission') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach($order_info['goods_list'] as $val)

                    <tr>
                        <td>
                        @if($order_info['log_type'] == 0 || $order_info['log_type'] == 2)
                        <a href="{{ $val['goods_url'] }}" class="img"><img src="{{ $val['goods_thumb'] }}" alt=""></a>
                        <a href="{{ $val['goods_url'] }}" class="name">{{ $val['goods_name'] }}</a>
                        @else
                        <a href="#" class="img"><img src="{{ $val['goods_thumb'] }}" alt=""></a>
                        <a href="#" class="name">{{ $val['goods_name'] }}</a>
                        @endif
                        </td>
                        <td><div class="tc">{{ $val['goods_number'] }}</div></td>
                        <td><div class="tc">{{ $val['drp_goods_price_format'] }}</div></td>
                        @if($order_info['log_type'] == 0)
                        <td><div class="tc">{{ $val['dis_commission'] }}</div></td>
                        <td><div class="tc ftx-01">{{ $val['drp_money_format'] }}</div></td>
                        @endif
                        <td><div class="tc">{{ $val['level_per'] }}（{{ $val['drp_level_format'] }}）</div></td>
                        <td><div class="tc ftx-01">{{ $val['level_money_format'] }}</div></td>
                    </tr>

                    @endforeach

                    </tbody>
                </table>

                <div class="user-order-detail-total">
                    <dl class="total-row">
                        <dt class="total-label">{{ __('user.total_goods_price') }}：</dt>
                        <dd class="total-value">{{ $order_info['total_goods_price_format'] ?? 0 }}</dd>
                    </dl>
                    @if(isset($order_info['total_goods_favourable']) && $order_info['total_goods_favourable'] > 0)
                        <dl class="total-row">
                            <dt class="total-label">{{ __('user.goods_favourable') }}：</dt>
                            <dd class="total-value ftx-01"> - {{ $order_info['total_goods_favourable_format'] ?? 0 }}</dd>
                        </dl>
                    @endif
                    @if(isset($order_info['total_goods_bonus']) && $order_info['total_goods_bonus'] > 0)
                        <dl class="total-row">
                            <dt class="total-label">{{ __('user.goods_bonus') }}：</dt>
                            <dd class="total-value ftx-01"> - {{ $order_info['total_goods_bonus_format'] ?? 0 }}</dd>
                        </dl>
                    @endif
                    @if(isset($order_info['total_goods_coupons']) && $order_info['total_goods_coupons'] > 0)
                        <dl class="total-row">
                            <dt class="total-label">{{ __('user.goods_coupons') }}：</dt>
                            <dd class="total-value ftx-01"> - {{ $order_info['total_goods_coupons_format'] ?? 0 }}</dd>
                        </dl>
                    @endif
                    @if(isset($order_info['total_goods_integral_money']) && $order_info['total_goods_integral_money'] > 0)
                        <dl class="total-row">
                            <dt class="total-label">{{ __('user.integral_offset') }}：</dt>
                            <dd class="total-value ftx-01"> - {{ $order_info['goods_integral_money_format'] ?? 0 }}</dd>
                        </dl>
                    @endif
                    @if(isset($order_info['total_value_card_discount']) && $order_info['total_value_card_discount'] > 0)
                        <dl class="total-row">
                            <dt class="total-label">{{ __('user.value_card_discount') }}：</dt>
                            <dd class="total-value ftx-01"> - {{ $order_info['total_value_card_discount_format'] ?? 0 }}</dd>
                        </dl>
                    @endif
                    <dl class="total-row hide">
                        <dt class="total-label">{{ __('user.total_drp_goods_price') }}：</dt>
                        <dd class="total-value ftx-01">{{ $order_info['total_drp_goods_price_format'] ?? 0 }}</dd>
                    </dl>
                </div>

            </div>
        </div>

@endif



@if($action == 'comment_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ __('user.comment_list') }}</h2>
                <ul class="tabs">
                    <li @if($sign == 0)class="active"@endif><a href="user_message.php?act=comment_list">{{ __('user.stay_evaluate_goods') }}({{ $signNum0 ?? 0 }})</a></li>
                    <li @if($sign == 1)class="active"@endif>
                        <a href="user_message.php?act=comment_list&sign=1">{{ __('user.already_evaluated') }}@if(config('shop.add_evaluate', 0) == 1)/{{ __('user.stay_add_evaluated') }}@endif
                            ({{ $signNum1 ?? 0 }})
                        </a>
                    </li>
                </ul>
            </div>
            <div class="comment-list-warp clearfix">
                <div class="user-order-list comment_list">

                @if($comment_list)

                    @foreach($comment_list as $item)

                        <dl class="item">
                            <dt class="item-t">
                                <div class="t-statu">{{ $item['shop_name'] }}</div>
                                <div class="t-price order_detail_btn"><a href="user_order.php?act=order_detail&order_id={{ $item['order_id'] }}" target="_blank" >{{ __('user.order_detail') }}</a></div>
                            </dt>
                            <dd class="item-c content_wrap">
                                <div class="c-left">
                                    <div class="c-goods">
                                        <div class="c-img"><a href="{{ $item['goods_url'] }}" target="_blank" title="{{ $item['goods_name'] }}"><img src="{{ $item['goods_thumb'] }}" alt=""></a></div>
                                        <div class="c-info">
                                            <div class="info-name"><a href="{{ $item['goods_url'] }}" target="_blank" title="{{ $item['goods_name'] }}">{{ $item['goods_name'] }}</a></div>
                                            @if($sign == 0)
                                            <div class="info-price"><b>{{ $item['goods_price_formated'] }}</b><i>×</i><span>{{ $item['goods_number'] }}</span></div>
                                            <div class="info-attr">
                                                @if($item['goods_attr'])
                                                [{{ $item['goods_attr'] }}]
                                                @endif
                                            </div>
                                            @elseif ($sign == 1)
                                            <div class="info_rate">
                                                <span class="info_rate_label">{{ __('user.score') }}</span>
                                                @for ($i = 0; $i < 5; $i++)
                                                    <i class="iconfont icon-collection-alt @if($item['comment_rank'] && ($i + 1 <= $item['comment_rank'])) color_red @endif"></i>
                                                @endfor
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($sign == 0)
                                    {{--去评价--}}
                                    @if(isset($item['can_evaluate']) && $item['can_evaluate'] == 1)
                                        <div class="c-handle "><a href="javascript:void(0);" class="sc-btn comment_btn" ectype="btn-comment" data-foot="true" data-recid="{{ $item['rec_id'] }}" data-sign="{{ $sign }}" data-is-add-evaluate="0"  >{{ __('user.click_review') }}</a></div>
                                    @endif
                                @endif
                            </dd>

                            @if ($sign == 1)
                            <dd class="comment_content clearfix">
                                {{--首次评价内容--}}
                                @if($item['comment']['0'])
                                <div class="comment_main">
                                    <p>{{ $item['comment']['0']['content'] ?? '' }}</p>
                                    <ul class="img_list clearfix">
                                        @foreach ($item['comment']['0']['comment_img_list'] as $img)
                                            <li><img src="{{ $img['comment_img'] }}" alt=""></li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                {{--追评--}}
                                @if(isset($item['can_add_evaluate']) && $item['can_add_evaluate'] == 1)
                                <div class="c-handle">
                                    <a href="javascript:void(0);" class="sc-btn comment_btn" ectype="btn-comment" data-foot="true" data-recid="{{ $item['rec_id'] }}" data-sign="{{ $sign }}" data-is-add-evaluate="1">{{ __('user.continue_add_evaluated') }}</a>
                                </div>
                                @endif
                            </dd>
                                {{--追评内容--}}
                                @if(config('shop.add_evaluate', 0) == 1 && $item['comment']['1'])
                                <dd class="comment_content" style="margin: 0 20px; padding: 14px 0;">
                                    <h3 class="title">{{ __('user.add_evaluated_content') }}</h3>
                                    <p>{{ $item['comment']['1']['content'] ?? '' }}</p>
                                    <ul class="img_list clearfix">
                                        @foreach ($item['comment']['1']['comment_img_list'] as $img)
                                            <li><img src="{{ $img['comment_img'] }}" alt=""></li>
                                        @endforeach
                                    </ul>
                                </dd>
                                @endif

                            @endif

                        </dl>

                    @endforeach
                    <script type="text/javascript">
                        @foreach($lang['cmt_lang'] as $key => $item)
                         var {{ $key }} = "{!! $item !!}";
                        @endforeach

                        @if($sign == 0) $("input[name='comment_rank']").val(0); @endif
                    </script>
                @else
                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info no_info_line">
                            @if($sign == 0)
                            <h3>{{ __('user.user.comment_list_0.no_records') }}</h3>
                            <div class="no_btn"><a href="index.php" class="sc-btn">{{ $lang['goto_mall'] }}</a></div>
                            @elseif ($sign == 1)
                            <h3>{{ __('user.user.comment_list_1.no_records') }}</h3>
                            <div class="no_btn"><a href="user_message.php?act=comment_list" class="sc-btn">{{ $lang['all_comment_list'] }}</a></div>
                            @elseif ($sign == 2)
                            <h3>{{ __('user.user.comment_list_2.no_records') }}</h3>
                            <div class="no_btn"><a href="user_message.php?act=comment_list" class="sc-btn">{{ $lang['all_comment_list'] }}</a></div>
                            @endif
                        </div>
                    </div>
                @endif

                </div>

                @if($comment_list)

                @include('frontend::library/pages')

                @endif

            </div>
        </div>

@endif





@if($action == 'commented_view')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['comment_list'] }}</h2>
            </div>
            <div class="comment-list-warp clearfix">
                <div class="user-order-list">

@if($order_goods)

@foreach($order_goods as $item)

                    <dl class="item">
                        <dt class="item-t">
                        <div class="t-statu">
                            @if($sign == 0)
                            {{ $lang['comment_not'] }}
                            @elseif ($sign == 1)
                            {{ $lang['stay_add_file'] }}
                            @else
                            {{ $lang['Already_evaluated'] }}
                            @endif
                        </div>
                        </dt>
                        <dd class="item-c">
                            <div class="c-left">
                                <div class="c-goods">
                                    <div class="c-img"><a href="goods.php?id={{ $item['goods_id'] }}" target="_blank" title="{{ $item['goods_name'] }}"><img src="{{ $item['goods_thumb'] }}" alt=""></a></div>
                                    <div class="c-info">
                                        <div class="info-name"><a href="goods.php?id={{ $item['goods_id'] }}" target="_blank" title="{{ $item['goods_name'] }}">{{ $item['goods_name'] }}</a></div>
                                        <div class="info-attr">@if($item['goods_attr']) [{{ $item['goods_attr'] }}] @endif</div>
                                        <div class="info-price"><b>{{ $item['goods_price'] }}</b><i>×</i><span>{{ $item['goods_number'] }}</span></div>
                                    </div>
                                </div>
                            </div>


                            <div class="c-handle">
                                @if($sign == 0)
                                    {{--去评价--}}
                                    @if(isset($item['can_evaluate']) && $item['can_evaluate'] == 1)
                                        <a href="javascript:void(0);" class="sc-btn comment_btn" ectype="btn-comment" data-foot="true" data-recid="{{ $item['rec_id'] }}" data-sign="{{ $sign }}" data-is-add-evaluate="0"  >{{ __('user.click_review') }}</a>
                                    @endif

                                @elseif ($sign == 1)
                                    {{--去追评--}}
                                    @if(isset($item['can_add_evaluate']) && $item['can_add_evaluate'] == 1)
                                        <a href="javascript:void(0);" class="sc-btn comment_btn" ectype="btn-comment" data-foot="true" data-recid="{{ $item['rec_id'] }}" data-sign="{{ $sign }}" data-is-add-evaluate="1" >{{ __('user.continue_add_evaluated') }}</a>
                                    @endif
                                @endif

                                <a href="user_order.php?act=order_detail&order_id={{ $item['order_id'] }}" target="_blank" class="sc-btn">{{ $lang['order_detail'] }}</a>
                            </div>
                        </dd>
                    </dl>

@endforeach

                    <script type="text/javascript">

                    @foreach($lang['cmt_lang'] as $key => $item)

                        var {{ $key }} = "{!! $item !!}";

                    @endforeach


                    @if($sign == 0)

                    $("input[name='comment_rank']").val(0);

                    @endif

                    </script>

@else

                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info">
                            <h3>{{ $lang['order_Prompt'] }}</h3>
                        </div>
                    </div>

@endif

                </div>

            </div>
        </div>

@endif




@if($action == 'take_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['take_list'] }}</h2>
            </div>
            <div class="take-list-warp clearfix">

@if($take_list)


@foreach($take_list as $take)

                <div class="tl-item">
                    <div class="item-t">
                            <div class="t-goods">
                            <div class="t-img"><a href="goods.php?id={{ $take['goods_id'] }}" target="_blank"><img src="
@if($take['goods_thumb'])
{{ $take['goods_thumb'] }}
@endif
"></a></div>
                            <div class="t-info">
                                <div class="info-name"><a href="goods.php?id={{ $take['goods_id'] }}" class="ftx-03" target="_blank">{{ $take['goods_name'] }}</a></div>
                                <div class="info-sku">{{ $lang['card_number_label'] }}：{{ $take['gift_sn'] }}</div>
                            </div>
                        </div>
                        <div class="t-statu">
@if($take['status'] == 1)
{{ $lang['cs']['101'] }}
@elseif ($take['status'] == 2)
<a onclick="if (confirm('{{ $lang['confirm_received'] }}'))location.href='user.php?act=confim_goods&take_id={{ $take['gift_gard_id'] }}'" class="sc-btn">{{ $lang['received'] }}</a>
@else
{{ $lang['complete'] }}
@endif
</div>
                    </div>
                    <div class="item-f">
                        <div class="f-left">{{ $lang['gift_address'] }}：{{ $take['address'] }}</div>
                        <div class="f-right">{{ $take['user_time'] }}</div>
                    </div>
                </div>

@endforeach


@else

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info">
                        <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                    </div>
                </div>

@endif

            </div>
        </div>

@endif





@if($action == 'complaint_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['transaction_disputes'] }}</h2>
                <ul class="tabs" ectype="dispute-tabs">
                    <li
@if($is_complaint == 0)
class="active"
@endif
><a href="user_order.php?act=complaint_list">{{ $lang['may_apply_order'] }}</a></li>
                    <li
@if($is_complaint == 1)
class="active"
@endif
><a href="user_order.php?act=complaint_list&is_complaint=1">{{ $lang['already_apply_order'] }}</a></li>
                </ul>
            </div>
            <div class="user-list-title clearfix">
                <div class="user-list-search">
                    <input type="text" id="complaint_keyword" class="text" placeholder="{{ $lang['order_number'] }}" name="" style="color:#999;"/>
                    <button type="button" ectype="keyword"><i class="iconfont icon-search"></i></button>
                </div>
            </div>
            <div id="complaint_list">
                <div class="dispute-content" ectype="dispute-content">
                    <div class="user-order-list user-dispute-list">

@forelse($orders as $order)

                        <dl class="item">
                            <dt class="item-t">

@if($is_complaint == 1)
<div class="t-statu">{{ $lang['complaint_state'][$order['complaint_state']] }}
@if($order['has_talk'] == 1)
<span class="red">--{{ $lang['unread_information'] }}</span>
@endif
</div>
@endif

                                <div class="t-info">
                                    <span class="info-item">{{ $lang['order_number'] }}：{{ $order['order_sn'] }}</span>
                                    <span class="info-item">{{ $order['order_time'] }}</span>
                                    <span class="info-item">{{ $order['consignee'] }}</span>
                                    <span class="info-item">
                                        <a href="{{ $order['shop_url'] }}" class="user-shop-link">{{ $order['shop_name'] }}</a>
                                        <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $order['ru_id'] }}"  class="iconfont icon-kefu user-shop-kefu"></a>
                                    </span>
                                </div>
                                <div class="t-price">{{ $order['total_fee'] }}</div>
                            </dt>
                            <dd class="item-c">
                                <div class="c-left">

@foreach($order['order_goods'] as $goods)

                                    <div class="c-goods">
                                        <div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

                                        <div class="c-info">
                                            <div class="o-info-lm">

@if($goods['extension_code'] == 'package_buy')

                                                {{ $goods['goods_name'] }}
                                                <span class="red">{{ $lang['remark_package'] }}</span>

@else

                                                <a href="{{ $goods['url'] }}" class="info-name" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a>

@if($goods['trade_id'])
<a href="user_order.php?act=trade&tradeId={{ $goods['trade_id'] }}&snapshot=true" class="trade_snapshot" target="_blank">[{{ $lang['trade_snapshot'] }}]</a>
@endif


@endif

                                            </div>
                                            <div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
                                        </div>
                                    </div>

@endforeach

                                </div>
                                <div class="c-handle">

@if($is_complaint == 0)

                                    <a href="user_order.php?act=complaint_apply&order_id={{ $order['order_id'] }}" class="sc-btn">{{ $lang['apply_transaction_disputes'] }}</a>

@else

                                    <a href="user_order.php?act=complaint_apply&complaint_id={{ $order['is_complaint'] }}" class="sc-btn">{{ $lang['View_details'] }}</a>

@if($order['complaint_state'] == 4)

                                    <a href="javascript:void();" ectype="del_compalint" data-id="{{ $order['is_complaint'] }}" class="sc-btn">{{ $lang['drop'] }}</a>

@endif


@endif

                                </div>
                            </dd>
                        </dl>

@empty

                        <div class="no_records">
                            <i class="no_icon"></i>
                            <div class="no_info">
                                <h3>
                                    {{ $no_records }}
                                </h3>
                            </div>
                        </div>

@endforelse

                    </div>
                    @include('frontend::library/pages')
                </div>
            </div>
        </div>
        <script type="text/javascript">
         $("*[ectype='del_compalint']").on("click", function () {
             var _this = $(this);
             var id = _this.data("id");
             if(confirm("{{ $lang['confirm_delect'] }}")){
                 Ajax.call('ajax_user.php?act=del_compalint', 'compalint_id=' + id, function(data){
                     if(data.error == 1){
                        pbDialog(data.message,"",0);
                     }else{
                        _this.parents(".item").remove();
                     }
                 }, 'POST', 'JSON');
             }
         })

         $("*[ectype='keyword']").on("click", function () {
             var is_complaint = {{ $is_complaint }};
             var keyword = $("#complaint_keyword").val();
             Ajax.call('user_order.php?act=complaint_list','is_ajax=1&is_complaint=' + is_complaint + '&keyword=' + keyword, function(data){
                if(data.error == 1){
                    pbDialog(data.message,"",0);
                }else{
                    $("#complaint_list").html(data.content);
                }
             }, 'POST', 'JSON');
     })
        </script>

@endif





@if($action == 'complaint_apply')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['apply_disputes_order'] }}</h2>
            </div>

@if($complaint_id != 0)

            <div class="view-dis-order">
                <div class="ii-section iis-state">
                    <div class="stepflex">
                        <dl class="first active"><dt></dt><dd>{{ $lang['complaint_state']['0'] }}</dd></dl>
                        <dl
@if($complaint_info['complaint_state'] > 0)
class="active"
@endif
><dt></dt><dd>{{ $lang['complaint_state']['1'] }}</dd></dl>
                        <dl
@if($complaint_info['complaint_state'] > 1)
class="active"
@endif
><dt></dt><dd>{{ $lang['complaint_state']['2'] }}</dd></dl>
                        <dl
@if($complaint_info['complaint_state'] > 2)
class="active"
@endif
><dt></dt><dd>{{ $lang['complaint_state']['3'] }}</dd></dl>
                        <dl class="last
@if($complaint_info['complaint_state'] == 4)
active
@endif
"><dt></dt><dd>{{ $lang['complaint_state']['4'] }}</dd></dl>
                    </div>

@if($complaint_info['complaint_state'] != 4)

                    <div class="iis-state-warp">
                        <i class="icon icon-iis-1"></i>
                        <div class="iis-state-info">
                            <div class="tit">{{ $lang['complaint_state_notic'] }}</div>
                            <div class="iis-btn">
                                <a href="user_order.php?act=arbitration&complaint_id={{ $complaint_id }}&complaint_state=4" class="sc-btn">{{ $lang['arbitration'] }}</a>
                                <a href="user_order.php?act=complaint_apply&complaint_id={{ $complaint_id }}" class="sc-btn">{{ $lang['complaint_apply'] }}</a>
                            </div>
                        </div>
                    </div>

@endif

                </div>
            </div>

@endif

            <div class="dis-order-apply">
                <div class="user-order-list">
                    <dl class="item">
                        <dt class="item-t">
                            <div class="t-info">
                                <span class="info-item">{{ $lang['order_number'] }}：{{ $order['order_sn'] }}</span>
                                <span class="info-item">{{ $order['order_time'] }}</span>
                                <span class="info-item">{{ $order['consignee'] }}</span>
                                <span class="info-item">
                                    <a href="{{ $order['shop_url'] }}" class="user-shop-link">{{ $order['shop_name'] }}</a>
                                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $order['ru_id'] }}"  class="iconfont icon-kefu user-shop-kefu"></a>
                                </span>
                            </div>
                            <div class="t-price">{{ $order['total_fee'] }}</div>
                        </dt>
                        <dd class="item-c">
                            <div class="c-left">

@foreach($order['order_goods'] as $goods)

                                <div class="c-goods">
                                    <div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>
                                    <div class="c-info">
                                        <div class="o-info-lm">

@if($goods['extension_code'] == 'package_buy')

                                            {{ $goods['goods_name'] }}
                                            <span class="red">{{ $lang['remark_package'] }}</span>

@else

                                            <a href="{{ $goods['url'] }}" class="info-name" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a>

@if($goods['trade_id'])
<a href="user_order.php?act=trade&tradeId={{ $goods['trade_id'] }}&snapshot=true" class="trade_snapshot" target="_blank">[{{ $lang['trade_snapshot'] }}]</a>
@endif


@endif

                                        </div>
                                        <div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
                                    </div>
                                </div>

@endforeach

                            </div>
                        </dd>
                    </dl>
                </div>
                <div class="ii-section complaint_apply">
                <form action="user_order.php" method="post" enctype="multipart/form-data" name="formMsg" id="reportForm" >
                    <div class="user-title">

@if($complaint_info['complaint_state'] > 1)

                        <h2>{{ $lang['appeal_info'] }}</h2>

@else

                        <h2>{{ $lang['complaint_info'] }}</h2>

@endif

                    </div>
                    <div class="user-items">
                        <div class="item">
                            <div class="label">
@if($complaint_id == 0)
<em class="red">*</em>
@endif
{{ $lang['complaint_title'] }}：</div>
                            <div class="value">

@if($complaint_id == 0)

                                <div class="imitate_select w200">
                                    <div class="cite"><span>{{ $lang['please_select'] }}</span><i class="iconfont icon-down"></i></div>
                                    <ul>
                                        <li><a href="javascript:void(0);" data-value="0" ectype='check_title'>{{ $lang['please_select'] }}</a></li>

@foreach($complaint_title as $title)

                                        <li><a href="javascript:void(0);" data-value="{{ $title['title_id'] }}" ectype='check_title'>{{ $title['title_name'] }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name='title_id' value='0'>
                                </div>
                                <span class="form_prompt ml10">{{ $lang['complaint_title_null'] }}</span>

@else

                                <span class="txt-lh ftx-07">{{ $complaint_info['title_name'] }}</span>

@endif

                            </div>
                        </div>
                        <div class="item">
                            <div class="label">
@if($complaint_id == 0)
<em class="red">*</em>
@endif
{{ $lang['problem_desc'] }}：</div>
                            <div class="value">

@if($complaint_id == 0)

                                <textarea name="complaint_content" class="textarea"></textarea>
                                <div class="form_prompt"></div>
                                <div class="fl red lh20 mt10">{{ $lang['complaint_content_notic'] }}</div>

@else

                                <span class="txt-lh ftx-07">{{ $complaint_info['complaint_content'] }}</span>

@endif

                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['evidence_upload'] }}：</div>
                            <div class="value">

@if($complaint_id == 0)

                                <div class="sc-btn fl" id="uploadbutton">{{ $lang['upload_img'] }}</div>

@endif

                                <div class="upload-img-box">
                                    <div class="img-lists">
                                        <ul class="img-list-ul" ectype="imglist">

@if($img_list)


@foreach($img_list as $list)

                                            <li><a href="{{ $list['comment_img'] }}" target="_blank"><img width="78" height="78" alt="" src="{{ $list['comment_img'] }}"></a>
@if($report_id == 0)
<i class="iconfont icon-cha" ectype="compimg-remove" data-imgid="{{ $list['id'] }}"></i>
@endif
</li>

@endforeach


@endif

                                        </ul>
                                    </div>
                                </div>

@if($complaint_id == 0)

                                <div class="clear"></div>
                                <div class="fl lh20 mt10">{{ $lang['evidence_upload_notic'] }}</div>

@endif

                            </div>
                        </div>

@if($complaint_id != 0)

                        <div class="item">
                            <div class="label">{{ $lang['complaint_time'] }}：</div>
                            <div class="value">
                                <span class="txt-lh ftx-07">{{ $complaint_info['add_time'] }}</span>
                            </div>
                        </div>

@endif


@if($complaint_info['complaint_state'] > 0)


                        <div class="item">
                            <div class="label">{{ $lang['complaint_handle_time'] }}：</div>
                            <div class="value">
                                <span class="txt-lh ftx-07">{{ $complaint_info['complaint_handle_time'] }}</span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['handle_user'] }}：</div>
                            <div class="value">
                                <span class="txt-lh ftx-07">{{ $complaint_info['handle_user'] }}</span>
                            </div>
                        </div>

@endif



@if($complaint_info['complaint_state'] > 1)


@if($complaint_info['ru_id'] > 0)

                        <div class="item">
                            <div class="label">{{ $lang['appeal_content'] }}：</div>
                            <div class="value">
                                <span class="txt-lh ftx-07">{{ $complaint_info['appeal_messg'] }}</span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['appeal_content'] }}：</div>
                            <div class="value">
                                <div class="upload-img-box">
                                    <div class="img-lists">
                                        <ul class="img-list-ul">

@forelse($complaint_info['appeal_img'] as $list)

                                            <li><a href="{{ $list['img_file'] }}" target="_blank"><img width="78" height="78" alt="" src="{{ $list['img_file'] }}"></a></li>

@empty

                                            {{ $lang['not_img'] }}

@endforelse

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['complaint_time'] }}：</div>
                            <div class="value"><span class="txt-lh ftx-07">{{ $complaint_info['appeal_time'] }}</span></div>
                        </div>

@endif

                        <div class="item">
                            <div class="label">{{ $lang['talk_record'] }}：</div>
                            <div class="value">
                                <div class="talk_list" ectype="talk_list">
                                    <div class="talk_list_info">
                                    @include('frontend::library/talk_list')
                                    </div>
                                </div>
                            </div>
                        </div>

@if($complaint_info['complaint_state'] != 4)

                        <div class="item">
                            <div class="label">{{ $lang['talk_release'] }}：</div>
                            <div class="value">
                                <textarea name="talk_content" class="textarea mb10"></textarea>
                                <a href="javascript:;" class="sc-btn sc-redBg-btn" ectype="talk_release" data-type="0">{{ $lang['talk_release'] }}</a>
                                <a href="javascript:;" class="sc-btn sc-redBg-btn" ectype="talk_release" data-type="1">{{ $lang['talk_refresh'] }}</a>
                            </div>
                        </div>

@endif


@endif


@if($complaint_info['complaint_state'] == 4)

                        <div class="item">
                            <div class="label">{{ $lang['end_handle_messg'] }}：</div>
                            <div class="value"><span class="txt-lh ftx-07">{{ $complaint_info['end_handle_messg'] }}</span></div>
                        </div>

@if($complaint_info['end_handle_time'])

                            <div class="item">
                                <div class="label">{{ $lang['end_handle_time'] }}：</div>
                                <div class="value"><span class="txt-lh ftx-07">{{ $complaint_info['end_handle_time'] }}</span></div>
                            </div>

@endif


@if($complaint_info['end_handle_user'])

                            <div class="item">
                                <div class="label">{{ $lang['handle_user'] }}：</div>
                                <div class="value"><span class="txt-lh ftx-07">{{ $complaint_info['end_handle_user'] }}</span></div>
                            </div>

@endif


@endif

                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input name="complaint_id" type="hidden" value="{{ $complaint_info['complaint_id'] }}">
                                <input name="order_id" type="hidden" value="{{ $order_id }}">

@if($complaint_id == 0)

                                <input name='act' type='hidden' value='complaint_submit'>
                                <input type="button" id="submitBtn" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_confirm'] }}">

@else

                                <a href="user_order.php?act=complaint_list&is_complaint=1" class="sc-btn sc-redBg-btn"> {{ $lang['back'] }} </a>

@endif


@if($complaint_info['complaint_state'] == 2)

                                <input name='act' type='hidden' value='arbitration'>
                                <input type="submit" name="submit" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_arbitration'] }}">

@endif

                            </div>
                        </div>
                    </div>
                @csrf </form>
                </div>
            </div>
        </div>
        <script type="text/javascript">

@if($complaint_info['complaint_state'] == 2  || $complaint_info['complaint_state'] == 3)

        startCheckStalk();

@endif

        //检查谈话
        function startCheckStalk(){
          window.setInterval("Checktalk()", 3000);
        }

        function Checktalk(){
            var complaint_id = $("input[name='complaint_id']").val();
            Ajax.call('ajax_user.php', "act=talk_release&complaint_id=" +  complaint_id + "&type=1", function(data){
                $("[ectype='talk_list'] .talk_list_info").html(data.content);
                var height = $("*[ectype='talk_list'] .talk_list_info").height();
                $("[ectype='talk_list']").scrollTop(height);
            }, 'POST', 'JSON');
        }

        $("*[ectype='check_title']").on("click", function () {
            var _this = $(this);
            var val = _this.data("value");
            if(val == 0){
                _this.parents(".value").find('.form_prompt').html("{{ $lang['complaint_title_null'] }}");
            }else{
                Ajax.call('ajax_user.php?act=complaint_title_desc', 'title_id=' + val, function(data){
                    if(data.error == 1){
                      pbDialog(data.message,"",0);
                    }else{
                      _this.parents(".value").find('.form_prompt').html(data.content);
                    }
                }, 'POST', 'JSON');
            }
        })

        $(document).on('click','*[ectype="talk_release"]',function(){
            var _this = $(this);
            var type = _this.data('type');
            var talk_content = $("textarea[name='talk_content']").val();
            var complaint_id = $("input[name='complaint_id']").val();
            var talk_id = _this.data('id');
            var back = true;
            if(type == 0 && talk_content == ''){
                back = false;
            }
            if(back){
                Ajax.call('ajax_user.php', "act=talk_release&talk_content=" +  talk_content + "&complaint_id=" + complaint_id + "&type=" + type + "&talk_id=" + talk_id, function(data){
                    $("[ectype='talk_list'] .talk_list_info").html(data.content);
                    var height = $("*[ectype='talk_list'] .talk_list_info").height();
                    $("[ectype='talk_list']").scrollTop(height);
                    $("textarea[name='talk_content']").val('');
                }, 'POST', 'JSON');
            }else{
                pbDialog(json_languages.talk_content_null,"",0);
            }
        })
        </script>

@endif





@if($action == 'illegal_report')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['illegal_report'] }}
@if($report_time)
--<span class="red">{{ $lang['malice_report'] }}{{ $lang['malice_report_end'] }}{{ $report_time }}
@endif
</span></h2>
            </div>
            <div class="illegal-report-content">
                <div class="user-order-list">

@forelse($goods_report as $report)

                    <dl class="item">
                        <dt class="item-t">
                            <div class="t-statu">
@if($report['report_state'] == 0)
{{ $lang['not_dispose'] }}
@elseif ($report['report_state'] == 2)
{{ $lang['buyer_cancel'] }}
@else

@if($report['handle_type'] == 1)
{{ $lang['ineffective_report'] }}
@elseif ($report['handle_type'] == 2)
{{ $lang['malicious_report'] }}
@else
{{ $lang['effective_report'] }}
@endif

@endif
</div>
                            <div class="t-info">
                                <span class="info-item">{{ $lang['report_type'] }}：<em class="red">{{ $report['type_name'] }}</em></span>
                                <span class="info-item">{{ $lang['report_zhuti'] }}：<em class="red">{{ $report['title_name'] }}</em></span>
                            </div>
                            <div class="fr">{{ $report['add_time'] }}</div>
                        </dt>
                        <dd class="item-c">
                            <div class="c-left">
                                <div class="c-goods">
                                    <div class="c-img"><a href="{{ $report['url'] }}" target="_blank"><img src="{{ $report['goods_image'] }}" alt="{{ $report['goods_name'] }}"></a></div>
                                    <div class="c-info">
                                        <div class="info-name"><a href="{{ $report['url'] }}" target="_blank">{{ $report['goods_name'] }}</a></div>
                                        <div class="info-store">{{ $lang['business'] }}：<span class="ftx-06">{{ $report['shop_name'] }}</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="c-handle">
                                <a href="user.php?act=goods_report&report_id={{ $report['report_id'] }}" class="sc-btn">{{ $lang['view'] }}</a>

@if($report['report_state'] == 0)
<a href="#" class="sc-btn" ectype="cancel_report" data-type="1" data-state="2" data-id="{{ $report['report_id'] }}">{{ $lang['is_cancel'] }}</a>
@endif


@if($report['handle_type'] > 0 || $report['report_state'] == 2)
<a href="#" class="sc-btn" ectype="cancel_report" data-type="1" data-state="3" data-id="{{ $report['report_id'] }}">{{ $lang['drop'] }}</a>
@endif

                            </div>
                        </dd>
                    </dl>

@empty

                    <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info">
                        <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                    </div>
                </div>

@endforelse

                </div>
            </div>
        </div>
        @include('frontend::library/pages')


@endif



@if($action == 'goods_report')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['illegal_report'] }}</h2>
            </div>
            <div class="view-illegal-report">
                <div class="ii-section iis-state">

@if($goods_report_info['report_state'] != 2)

                    <div class="stepflex">
                        <dl class="first active"><dt></dt><dd>{{ $lang['report_step_one'] }}</dd></dl>
                        <dl
@if($report_id > 0)
class="active"
@endif
><dt></dt><dd>{{ $lang['report_step_two'] }}</dd></dl>
                        <dl class="last
@if($goods_report_info['handle_type'] > 0)
 active
@endif
"><dt></dt><dd>{{ $lang['report_step_three'] }}</dd></dl>
                    </div>

@else

                    <div class="iis-state-warp">
                        <i class="icon icon-iis-4"></i>
                        <div class="iis-state-info">
                            <div class="tit tit60 ftx-01">{{ $lang['user_report_notic'] }}</div>
                        </div>
                    </div>

@endif

                </div>
                <form action="user.php" method="post" enctype="multipart/form-data" name="formMsg" id="reportForm" >
                    <div class="ii-section">
                        <div class="user-items">
                            <div class="item">
                                <div class="label">{{ $lang['report_seller'] }}：</div>
                                <div class="value"><span class="txt-lh ftx-06">{{ $goods_info['shop_name'] }}</span></div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['related_goods'] }}：</div>
                                <div class="value">
                                    <div class="p-product">
                                        <div class="img"><a href="{{ $goods_info['url'] }}" target="_blank"><img src="{{ $goods_info['goods_thumb'] }}" alt="{{ $goods_info['goods_name'] }}"></a></div>
                                        <div class="name"><a href="{{ $goods_info['url'] }}" target="_blank">{{ $goods_info['goods_name'] }}</a></div>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">
@if($report_id == 0)
<em class="red">*</em>
@endif
{{ $lang['report_type'] }}：</div>
                                <div class="value">

@if($report_id == 0)


@foreach($report_type as $type)

                                        <div class="radio-item">
                                            <input type="radio" name="type_id" class="ui-radio" value="{{ $type['type_id'] }}" id="type_{{ $type['type_id'] }}"
@if($loop->first)
checked="checked"
@endif
 ectype='check_title'>
                                            <label for="type_{{ $type['type_id'] }}" class="ui-radio-label">{{ $type['type_name'] }}</label>
                                        </div>

@endforeach


@else

                                        <span class="txt-lh ftx-06">{{ $goods_report_info['type_name'] }}：{{ $goods_report_info['type_desc'] }}</span>

@endif

                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">
@if($report_id == 0)
<em class="red">*</em>
@endif
{{ $lang['report_zhuti'] }}：</div>
                                <div class="value">

@if($report_id == 0)

                                        <div class="imitate_select w200">
                                            <div class="cite"><span>{{ $lang['Please_select'] }}</span><i class="iconfont icon-down"></i></div>
                                            <ul ectype='report_title'>
                                                <li><a href="javascript:void(0);" data-value="">{{ $lang['Please_select'] }}</a></li>

@if($report_title)


@foreach($report_title as $title)

                                                <li><a href="javascript:void(0);" data-value="{{ $title['title_id'] }}">{{ $title['title_name'] }}</a></li>

@endforeach


@endif

                                            </ul>
                                            <input type="hidden" name='title_id' value=''>
                                        </div>
                                        <div class="form_prompt"></div>

@else

                                        <span class="txt-lh ftx-06">{{ $goods_report_info['title_name'] }}</span>

@endif

                                </div>
                            </div>
                            <div class="item">
                                <div class="label">
@if($report_id == 0)
<em class="red">*</em>
@endif
{{ $lang['report_content'] }}：</div>
                                <div class="value">

@if($report_id == 0)

                                        <textarea name="inform_content" class="textarea"></textarea>
                                        <div class="form_prompt"></div>

@else

                                        <span class="txt-lh ftx-06">{{ $goods_report_info['inform_content'] }}</span>

@endif

                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['evidence_upload'] }}：</div>
                                <div class="value">

@if($report_id == 0)
<div class="sc-btn fl mt5" id='uploadbutton'>{{ $lang['upload_img'] }}</div>
@endif

                                    <div class="upload-img-box">
                                        <div class="img-lists">
                                            <ul class="img-list-ul" ectype="imglist">

@if($img_list)


@foreach($img_list as $list)

                                                <li><img width="78" height="78" alt="" src="{{ $list['comment_img'] }}">
@if($report_id == 0)
<i class="iconfont icon-cha" ectype="reimg-remove" data-imgid="{{ $list['id'] }}"></i>
@endif
</li>

@endforeach


@endif

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

@if($goods_report_info['handle_type'] > 0)

                            <div class="item">
                                <div class="label">{{ $lang['handle_type'] }}：</div>
                                <div class="value">
                                        <span class="txt-lh ftx-06">{{ $lang['handle_type_desc'][$goods_report_info['handle_type']] }}</span>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['handle_message'] }}：</div>
                                <div class="value">
                                        <span class="txt-lh ftx-06">{{ $goods_report_info['handle_message'] }}</span>
                                </div>
                            </div>

@endif

                            <div class="item item-button">
                                <div class="label">&nbsp;</div>
                                <div class="value">

@if($report_id == 0)

                                    <input name='goods_id' type='hidden' value='{{ $goods_info['goods_id'] }}'>
                                    <input name='goods_name' type='hidden' value='{{ $goods_info['goods_name'] }}'>
                                    <input name='goods_image' type='hidden' value='{{ $goods_info['goods_thumb'] }}'>
                                    <input name='act' type='hidden' value='goods_report_submit'>
                                    <input type="button" name="button" id="submitBtn" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_confirm'] }}">

@else


@if($goods_report_info['report_state'] == 0)

                                            <input type="button" name="button"  class="sc-btn sc-redBg-btn" value="{{ $lang['cancel_report'] }}" ectype="cancel_report" data-type="0" data-state="2" data-id="{{ $goods_report_info['report_id'] }}">

@endif


@if($goods_report_info['handle_type'] > 0 || $goods_report_info['report_state'] == 2)

                                            <input type="button" class="sc-btn sc-redBg-btn" value="{{ $lang['drop'] }}" ectype="cancel_report" data-type="0" data-state="3" data-id="{{ $goods_report_info['report_id'] }}">

@endif


@endif

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="user-prompt mt20">
                        <div class="tit"><span>{{ $lang['user_report_title'] }}</span><i class="iconfont icon-down"></i></div>
                        <div class="info">
                            {!!  $lang['user_report_prompt'] !!}
                        </div>
                    </div>
                @csrf </form>
            </div>
        </div>
        <script type="text/javascript">
             $("*[ectype='check_title']").on("click", function () {
                 var _this = $(this);
                 var val = _this.val();
                 Ajax.call('ajax_user.php?act=checked_report_title', 'type_id=' + val, function(data){
                     var report_f = $("*[ectype='report_title']").parents(".imitate_select");
                     report_f.find("input[name='title_id']").val('0')
                     report_f.find(".cite").find("span").html("{{ $lang['Please_select'] }}");
                     $("*[ectype='report_title']").html(data);
                 }, 'POST', 'JSON');
             })

        </script>

@endif





@if($action == 'invoice')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['my_invoice'] }}</h2>
                <ul class="tabs">
                    <li class="active"><a href="user.php?act=invoice">{{ $lang['order_invoice_state'] }}</a></li>
                    <li><a href="user.php?act=vat_invoice_info">{{ $lang['increment_invoice_info'] }}</a></li>
                </ul>
            </div>
            <div id="user_inv_list">
                @include('frontend::library/user_inv_list')
            </div>
        </div>

@endif



@if($action == 'vat_invoice_info')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['my_invoice'] }}</h2>
                <ul class="tabs" ectype="invoice-tabs">
                    <li><a href="user.php?act=invoice">{{ $lang['order_invoice_state'] }}</a></li>
                    <li  class="active"><a href="user.php?act=vat_invoice_info">{{ $lang['increment_invoice_info'] }}</a></li>
                </ul>
            </div>
            <div>
                <div class="increment_invoice_info">

@if($submitted)

                    <div class="ii-section iis-state">
                        <div class="iis-state-warp">
                            <i class="icon icon-iis-
@if($audit_status == 0)
1
@elseif ($audit_status == 1)
3
@elseif ($audit_status == 2)
2
@endif
"></i>
                            <div class="iis-state-info">
                                <div class="tit">
@if($audit_status == 0)
{{ $lang['audit_status_0'] }}
@elseif ($audit_status == 1)
{{ $lang['audit_status_1'] }}
@elseif ($audit_status == 2)
{{ $lang['audit_status_2'] }}
@endif
</div>
                                <div class="iis-btn">
                                    <a href="user.php?act=vat_update&vat_id={{ $vat_id }}" class="sc-btn">{{ $lang['modify'] }}</a>
                                    <a href="user.php?act=vat_remove&vat_id={{ $vat_id }}" class="sc-btn">{{ $lang['drop'] }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ii-section iis-aptitude">
                        <div class="title">{{ $lang['aptitude_info'] }}</div>
                        <div class="info">
                            <p>{{ $lang['label_company_name'] }}：{{ $vat_info['company_name'] }}</p>
                            <p>{{ $lang['label_tax_id'] }}：{{ $vat_info['tax_id'] }}</p>
                            <p>{{ $lang['label_company_address'] }}：{{ $vat_info['company_address'] }}</p>
                            <p>{{ $lang['label_company_telephone'] }}：{{ $vat_info['company_telephone'] }}</p>
                            <p>{{ $lang['label_bank_of_deposit'] }}：{{ $vat_info['bank_of_deposit'] }}</p>
                            <p>{{ $lang['label_bank_account'] }}：{{ $vat_info['bank_account'] }}</p>
                        </div>
                    </div>
                    <div class="ii-section iis-ticket last-child">
                        <div class="title">{{ $lang['receipt_info'] }}

@if($vat_info['consignee_name'] || $vat_info['consignee_address'] || $vat_info['consignee_mobile_phone'] || $vat_info['consignee_province'])

                        <a href="user.php?act=vat_consignee&vat_id={{ $vat_info['id'] }}" class="ftx-01">{{ $lang['modify'] }}</a>

@endif

                        </div>
                        <div class="info">

@if(!$vat_info['consignee_name'] && !$vat_info['consignee_address'] && !$vat_info['consignee_mobile_phone'] && !$vat_info['consignee_province'])

                            <p>{{ $lang['label_receipt_set'] }}：<a href="user.php?act=vat_consignee&vat_id={{ $vat_info['id'] }}" class="sc-btn">{{ $lang['set'] }}</a></p>

@else

                            <p>{{ $lang['label_vat_name'] }}：{{ $vat_info['consignee_name'] }}</p>
                            <p>{{ $lang['label_vat_phone'] }}：{{ $vat_info['consignee_mobile_phone'] }}</p>
                            <p>{{ $lang['label_vat_region'] }}：{{ $vat_info['vat_region'] }}</p>
                            <p>{{ $lang['label_vat_address'] }}：{{ $vat_info['consignee_address'] }}</p>

@endif

                        </div>
                    </div>
                    <div class="user-prompt mt50">
                        <div class="tit"><span>{{ $lang['matters_need_attention'] }}</span><i class="iconfont icon-down"></i></div>
                        <div class="info">
                            {!! $lang['vat_prompt'] !!}
                        </div>
                    </div>

@else

                    <form action="user.php" method="post" name="inv_form" ectype="inv_form">
                    <div class="user-items">
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_company_name'] }}：</div>
                            <div class="value"><input type="text" value="{{ $vat_info['company_name'] }}" name="company_name" class="text"><div class="notic">{{ $lang['label_company_name_notic'] }}</div><div class="form_prompt"></div></div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_company_address'] }}：</div>
                            <div class="value"><input type="text" value="{{ $vat_info['company_address'] }}" name="company_address" class="text"><div class="notic">{{ $lang['label_company_address_notic'] }}</div><div class="form_prompt"></div></div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_tax_id'] }}：</div>
                            <div class="value"><input type="text" value="{{ $vat_info['tax_id'] }}" name="tax_id" class="text"><div class="notic">{{ $lang['label_tax_id_notic'] }}</div><div class="form_prompt"></div></div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_company_telephone'] }}：</div>
                            <div class="value"><input type="text" value="{{ $vat_info['company_telephone'] }}" name="company_telephone" class="text"><div class="notic">{{ $lang['label_company_telephone_notic'] }}</div><div class="form_prompt"></div></div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_bank_of_deposit'] }}：</div>
                            <div class="value"><input type="text" value="{{ $vat_info['bank_of_deposit'] }}" name="bank_of_deposit" class="text"><div class="notic">{{ $lang['label_bank_of_deposit_notic'] }}</div><div class="form_prompt"></div></div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_bank_account'] }}：</div>
                            <div class="value"><input type="text" value="{{ $vat_info['bank_account'] }}" name="bank_account" ectype="bank_card" class="text"><div class="notic">{{ $lang['label_bank_account_notic'] }}</div><div class="form_prompt"><label class="error" ectype="bname"></label></div></div>
                        </div>
                        <div class="item mb0">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <div class="checkbox-item lh30">
                                    <input type="checkbox" name="have_read" checked="checked" value='1' class="ui-checkbox" id="invoice_checkbox">
                                    <label for="invoice_checkbox" class="ui-label">{{ $lang['invoice_checkbox'] }}<a href="article.php?id=56" target="_blank">{{ $lang['invoice_checkbox_notic'] }}</a></label>
                                </div>
                                <div class="form_prompt" id="have_read"></div>
                            </div>
                        </div>
                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input type="button" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_goods'] }}" ectype="submitBtn">
                                <input type="reset" class="sc-btn" name="reset" value="{{ $lang['reset_alt'] }}">
                                <input type="hidden" name="act" value="{{ $edit ?? vat_insert }}">
                                <input type="hidden" name="status" value="{{ $status ?? insert }}">
                                <input type="hidden" name="vat_id" value="{{ $vat_id }}">
                            </div>
                        </div>
                    </div>
                    @csrf </form>

@endif

                </div>
            </div>
        </div>

@endif



@if($action == 'vat_consignee')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['my_invoice'] }}</h2>
                <ul class="tabs" ectype="invoice-tabs">
                    <li><a href="user.php?act=invoice">{{ $lang['order_invoice_state'] }}</a></li>
                    <li  class="active"><a href="user.php?act=vat_invoice_info">{{ $lang['increment_invoice_info'] }}</a></li>
                    <li><a href="javascript:void(0);">{{ $lang['invoice_help'] }}</a></li>
                </ul>
            </div>
            <div>
                <div class="increment_invoice_info">
                    <div class="ii-section iis-state">
                        <div class="iis-state-warp">

@if($audit_status == 0)

                            <i class="icon icon-iis-1"></i>
                            <div class="iis-state-info">
                                <div class="tit">{{ $lang['audit_status_0'] }}</div>

@elseif ($audit_status == 1)

                            <i class="icon icon-iis-3"></i>
                            <div class="iis-state-info">
                                <div class="tit">{{ $lang['audit_status_1'] }}</div>

@elseif ($audit_status == 2)

                            <i class="icon icon-iis-2"></i>
                            <div class="iis-state-info">
                                <div class="tit">{{ $lang['audit_status_2'] }}</div>

@endif

                                <div class="iis-btn">
                                    <a href="user.php?act=vat_update&vat_id={{ $vat_id }}" class="sc-btn">{{ $lang['modify'] }}</a>
                                    <a href="user.php?act=vat_remove&vat_id={{ $vat_id }}" class="sc-btn">{{ $lang['drop'] }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ii-section iis-aptitude">
                        <div class="title">{{ $lang['aptitude_info'] }}</div>
                        <div class="info">
                            <p>{{ $lang['label_company_name'] }}：{{ $vat_info['company_name'] }}</p>
                            <p>{{ $lang['label_tax_id'] }}：{{ $vat_info['tax_id'] }}</p>
                            <p>{{ $lang['label_company_telephone'] }}：{{ $vat_info['company_telephone'] }}</p>
                            <p>{{ $lang['label_bank_of_deposit'] }}：{{ $vat_info['bank_of_deposit'] }}</p>
                            <p>{{ $lang['label_bank_account'] }}：{{ $vat_info['bank_account'] }}</p>
                        </div>
                    </div>
                    <div class="ii-section iis-ticket last-child">
                        <div class="title">{{ $lang['receipt_info'] }}</div>
                        <div class="info">
                            <form action="user.php" method="post" name="vat_form" id="vat_form">
                            <div class="user-items">
                                <div class="item">
                                    <div class="label">{{ $lang['label_vat_name'] }}：</div>
                                    <div class="value"><input type="text" value="{{ $vat_info['consignee_name'] }}" name="consignee_name" class="text"></div>
                                </div>
                                <div class="item">
                                    <div class="label">{{ $lang['label_vat_phone'] }}：</div>
                                    <div class="value"><input type="text" value="{{ $vat_info['consignee_mobile_phone'] }}" name="consignee_mobile_phone" class="text"></div>
                                </div>
                                <div class="item">
                                    <div class="label">{{ $lang['label_vat_region'] }}：</div>
                                    <div class="form-value" ectype="regionLinkage">
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
                                                <input type="hidden" value="{{ $vat_info['country'] }}" name="country">
                                            </dt>
                                            <dd ectype="layer">

@foreach($country_list as $country)

                                                <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span>
                                                <input type="hidden" value="{{ $vat_info['province'] }}" ectype="ragionItem" name="province">
                                            </dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                                                <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span>
                                                <input type="hidden" value="{{ $vat_info['city'] }}" name="city" >
                                            </dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                                                <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span>
                                                <input type="hidden" value="{{ $vat_info['district'] }}" name="district">
                                            </dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

                                                <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span>
                                                <input type="hidden" value="{{ $vat_info['street'] }}" name="street">
                                            </dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                                                <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="label">{{ $lang['label_vat_address'] }}：</div>
                                    <div class="value"><input type="text" value="{{ $vat_info['consignee_address'] }}" name="consignee_address" class="text"></div>
                                </div>
                                <div class="item item-button">
                                    <div class="label">&nbsp;</div>
                                    <div class="value">
                                        <input type="hidden" name="vat_id" value="{{ $vat_info['id'] }}">
                                        <input type="hidden" name="act" value="vat_consignee">
                                        <input type="hidden" name="status" value="update">
                                        <input type="submit" class="sc-btn sc-redBg-btn" name="submit" value="提交">
                                        <a href="user.php?act=vat_invoice_info"><input type="button" class="sc-btn" name="cannel" value="取消"></a>
                                    </div>
                                </div>
                            </div>
                            @csrf </form>
                        </div>
                    </div>
                    <div class="user-prompt mt50">
                        <div class="tit"><span>{{ $lang['matters_need_attention'] }}</span><i class="iconfont icon-down"></i></div>
                        <div class="info">
                            {!! $lang['vat_prompt'] !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endif


     </div>
</div>

@include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.nyroModal.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>

@if($action == 'comment_list' || $action == 'commented_view' || $action == 'goods_report' || $action == 'complaint_apply')

<script type="text/javascript" src="{{ asset('js/plupload.full.min.js') }}"></script>

@endif

<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
//复制粘贴邀请链接
$("#clip_button").click(function(){
    var val = $("#affTextarea").val();

    copyTextToClipboard(val);
});

$(function(){
    $(".nyroModal").nyroModal();
});


@if($action == 'store_list')

$(".shop-right").slide({mainCell:".shop-bd ul",effect:"left",pnLoop:false,autoPlay:false,autoPage:true,prevCell:".prev",nextCell:".next",vis:4});

@endif

</script>


@if($action == 'add_booking')

<script type="text/javascript">
$(function(){
    $("#submitBtn").click(function(){
        if($("#formBooking").valid()){
            $("#formBooking").submit();
        }
    });
    $('#formBooking').validate({
        errorPlacement:function(error, element){
            var error_div = element.parents('div.form-value').find('div.form_prompt');
            //element.parents('div.label_value').find(".notic").hide();
            error_div.append(error);
        },
        rules : {
                number : {
                    required : true
                },
                desc : {
                    required : true
                },
                linkman : {
                    required : true
                },
                email : {
                    required : true,
                    email : true
                },
                tel : {
                    required : true
                }

            },
            messages : {
                number : {
                    required : json_languages.number_null
                },
                desc : {
                    required : json_languages.booking_number_null
                },
                linkman : {
                    required : json_languages.booking_contacts_null
                },
                email : {
                    required : json_languages.null_email_goods,
                    email : json_languages.email_error
                },
                tel : {
                    required : json_languages.login_phone_packup_one
                }
            }
    });
});
</script>

@endif



@if($action == 'goods_report' || $action == 'complaint_apply')

<script type="text/javascript">
        var url = '';

@if($action == 'goods_report')

        url = "ajax_user.php?act=ajax_report_img&goods_id={{ $goods_info['goods_id'] }}&sessid={{ $sessid }}";

@else

        url = "ajax_user.php?act=complaint_img&order_id={{ $order_id }}&sessid={{ $sessid }}";

@endif

        var uploader_gallery = new plupload.Uploader({//创建实例的构造方法
            runtimes: 'html5,flash,silverlight,html4', //上传插件初始化选用那种方式的优先级顺序
            browse_button: 'uploadbutton', // 上传按钮
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url, //远程上传地址
            filters: {
                max_file_size: '2mb', //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
                mime_types: [//允许文件上传类型
                    {title: "files", extensions: "bmp,gif,jpg,png,jpeg"}
                ]
            },
            multi_selection: true, //true:ctrl多文件上传, false 单文件上传
            init: {
               FilesAdded: function(up, files) { //文件上传前
                   var len = $("*[ectype='imglist'] li").length;
                    plupload.each(files, function(file){
                        //遍历文件
                        len ++;
                    });
                    if(len > 5){
                        pbDialog(json_languages.max_file_five_img,"",0);
                    }else{
                        submitBtn();
                    }
                },
                FileUploaded: function(up, file, info) { //文件上传成功的时候触发

                    var str_eval = eval;
                    var data = str_eval("(" + info.response + ")");
                    if(data.error > 0){
                        pbDialog(data.message,"",0);
                        return;
                    }else{
                        $("*[ectype='imglist']").html(data.content);
                    }
                },
                UploadComplete:function(up,file){
                    //所有文件上传成功时触发
                },
                Error: function(up, err){
                    //上传出错的时候触发
                    pbDialog(err.message,"",0);
                }
            }
        });

        uploader_gallery.init();

        function submitBtn(){
            //设置传参
            uploader_gallery.setOption("multipart_params");
            //开始控件
            uploader_gallery.start();
        };


@if($action == 'complaint_apply')

        var height = $("*[ectype='talk_list'] .talk_list_info").height();
        $("[ectype='talk_list']").scrollTop(height);

        $("[ectype='talk_list']").perfectScrollbar("destroy");
        $("[ectype='talk_list']").perfectScrollbar();

        $(document).on("click","*[ ectype='compimg-remove']",function(){
            var $this = $(this);
            var re_imgId = $this['data']("imgid");
            var order_id = $("input[name='order_id']").val();
            Ajax.call('ajax_user.php?act=del_reportpic', 're_imgId=' + re_imgId + '&order_id='+order_id + "&complaint=1", function(data){
                if(data.error > 0){
                    pbDialog(data.message,"",0);
                }else{
                    $("*[ectype='imglist']").html(data.content);
                }
            }, 'POST', 'JSON');
        });
        $("#submitBtn").click(function(){
            if($("#reportForm").valid()){
                $("#reportForm").submit();
            }
        });
        $('#reportForm').validate({
            errorPlacement:function(error, element){
                var error_div = element.parents('div.value').find('div.form_prompt');
                //element.parents('div.label_value').find(".notic").hide();
                error_div.append(error);
            },
            ignore : "",
            rules : {
                title_id : {
                    required : true
                },
                complaint_content : {
                    required : true
                }
            },
            messages : {
                title_id : {
                    required : '{{ $lang['complaint_title_null'] }}'
                },
                complaint_content : {
                    required : '{{ $lang['inform_content_null'] }}'
                }
            }
        });

@endif



@if($action == 'goods_report')

        //删除图片
        $(document).on("click","*[ ectype='reimg-remove']",function(){
            var $this = $(this);
            var re_imgId = $this['data']("imgid");
            var goods_id = $("input[name='goods_id']").val();
            Ajax.call('ajax_user.php?act=del_reportpic', 're_imgId=' + re_imgId + '&goods_id='+goods_id, function(data){
                if(data.error > 0){
                    pbDialog(data.message,"",0);
                }else{
                    $("*[ectype='imglist']").html(data.content);
                }
            }, 'POST', 'JSON');
        });

        $("#submitBtn").click(function(){
            if($("#reportForm").valid()){
                $("#reportForm").submit();
            }
        });
        $('#reportForm').validate({
            errorPlacement:function(error, element){
                var error_div = element.parents('div.value').find('div.form_prompt');
                //element.parents('div.label_value').find(".notic").hide();
                error_div.append(error);
            },
            ignore : "",
            rules : {
                title_id : {
                    required : true
                },
                type_id : {
                    required : true
                },
                inform_content : {
                    required : true
                }
            },
            messages : {
                title_id : {
                    required : '{{ $lang['title_null'] }}'
                },
                type_id : {
                    required : '{{ $lang['type_null'] }}'
                },
                inform_content : {
                    required : '{{ $lang['inform_content_null'] }}'
                }
            }
        });

@endif

</script>

@endif



@if($action == 'vat_consignee')

<script type="text/javascript" src="{{ skin('js/region.js') }}"></script>
<script type="application/javascript">
//地区三级联动

@if($vat_info && $vat_info['province'] > 0)

$.levelLink();

@else

$.levelLink(1);

@endif

</script>

@endif



@if($action == 'vat_invoice_info')

<script type="text/javascript" src="{{ skin('js/region.js') }}"></script>
<script type="text/javascript">

//地区三级联动

@if($vat_info && $vat_info['province'] > 0)

$.levelLink();

@else

$.levelLink(1);

@endif


$("*[ectype='submitBtn']").click(function(){
    frm  = document.forms['inv_form'];
    if(!frm.elements['have_read'].checked){
        $("#have_read").html("<i>{{ $lang['invoice_checkbox_first'] }}</i>");
        return false;
    };

    if($("*[ectype='inv_form']").valid()){
        $("*[ectype='inv_form']").submit();
    }
});
$("*[ectype='inv_form']").validate({
    errorPlacement:function(error, element){
        var error_div = element.parents('div.value').find('div.form_prompt');
        element.parents('div.value').find(".notic").hide();
        error_div.append(error);
    },
    ignore : "",
    rules : {
        company_name : {
            required : true
        },
        tax_id : {
            required : true,
            minlength : 15
        },
        company_address : {
            required : true
        },
        company_telephone : {
            required : true
        },
        bank_of_deposit : {
            required : true
        },
        bank_account : {
            required : true,
            number : true
        }
    },
    messages : {
        company_name : {
            required : json_languages.company_name_null
        },
        tax_id : {
            required : json_languages.tax_id_null,
            minlength: json_languages.tax_id_error
        },
        company_address : {
            required : json_languages.company_address_null
        },
        company_telephone : {
            required : json_languages.company_telephone_null
        },
        bank_of_deposit : {
            required : json_languages.bank_of_deposit_null
        },
        bank_account : {
            required : json_languages.bank_account_null,
            number : json_languages.bank_account_error
        }
    }
});
</script>

@endif



@if($action == 'message_list')

<script type="text/javascript">
$(function(){
    $("#pingjia_form").on("click",function(){
        if($("form[name='formMsg']").valid()){
            $("form[name='formMsg']").submit();
        }
    });

    $("form[name='formMsg']").validate({
        errorPlacement:function(error, element){
            var error_div = element.parents('div.item').find('div.form_prompt');
            error_div.html("").append(error);
        },
        ignore:".ignore",
        rules : {
            msg_title : {
                required : true,
                minlength: 2,
                maxlength: 50
            },
            msg_content:{
                required : true
            },
            user_email:{
                required : true,
                email : true
            }

@if($enabled_captcha)

            ,captcha:{
                required : true,
                maxlength : 4,
                remote : {
                    cache: false,
                    async:false,
                    type:'POST',
                    url:'ajax_dialog.php?act=ajax_captcha&seKey='+$("input[name='captcha']").siblings(".captcha_img").data("key"),
                    data:{
                        captcha:function(){
                            return $("input[name='captcha']").val();
                        }
                    },
                    dataFilter:function(data,type){
                        if(data == "false"){
                            $("input[name='captcha']").siblings(".captcha_img").click();
                        }
                        return data;
                    }
                }
            }

@endif

        },
        messages : {
            msg_title : {
                required : "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_not'] }}",
                minlength: "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_xz'] }}",
                maxlength: "<i class='iconfont icon-info-sign'></i> {{ $lang['commentTitle_xz'] }}"
            },
            msg_content : {
                required : "<i class='iconfont icon-info-sign'></i> {{ $lang['content_not'] }}"
            },
            user_email:{
                required : "<i class='iconfont icon-info-sign'></i> " + json_languages.null_email_goods,
                email : "<i class='iconfont icon-info-sign'></i> " + json_languages.email_error
            }

@if($enabled_captcha)

            ,captcha:{
                required : "<i class='iconfont icon-info-sign'></i> " + json_languages.common.captcha_not,
                maxlength: "<i class='iconfont icon-info-sign'></i> " + json_languages.common.captcha_xz,
                remote : "<i class='iconfont icon-info-sign'></i> " + json_languages.common.captcha_cw
            }

@endif

        },
        success:function(label){
            label.removeClass().addClass("succeed").html("<i></i>");
        },
        onkeyup:function(element,event){
            var name = $(element).attr("name");
            if(name == "captcha"){
                //不可去除，当是验证码输入必须失去焦点才可以验证（错误刷新验证码）
                return true;
            }else{
                $(element).valid();
            }
        }
    });
});
</script>

@endif

</body>
</html>
