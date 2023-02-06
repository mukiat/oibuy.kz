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
<link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}" />

@if($action == 'profile')

<link rel="stylesheet" type="text/css" href="{{ asset('js/amazeui/amazeui.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('js/cropper/cropper.css') }}" />

@endif

</head>

<body>
@include('frontend::library/page_header_common')
<div class="user-content clearfix">
    <div class="user-side" ectype="userSide">
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
        <div class="user-mod">
            @include('frontend::library/user_menu')
        </div>
    </div>
    <div class="user-main" ectype="userMain" data-action="noDefault">
        <div class="user-crumbs hide">
            @include('frontend::library/ur_here')
        </div>


@if($action == 'users_log')

        <div class="user-mod">
            <div class="user-account-warp">
                <div class="user-title">
                    <h2>{{ $lang['users_log'] }}</h2>
					<ul class="tabs">
                        <li><a href="user.php?act=profile">{{ $lang['profile'] }}</a></li>
                        <li class="active"><a href="user.php?act=users_log">{{ $lang['users_log'] }}</a></li>
                    </ul>
                </div>
                <div class="account-main account-not">
					<div class="account-open-list">
                        <table class="user-table user-table-account">
                            <colgroup>
                                <col width="140">
                                <col width="120">
                                <col width="120">
                                <col width="140">
                                <col width="140">
                                <col width="100">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>{{ $lang['lab_card_id'] }}</th>
                                    <th class="tl">{{ $lang['process_time'] }}</th>
                                    <th class="tl">{{ $lang['change_type'] }}</th>
                                    <th class="tl">{{ $lang['ip_address'] }}</th>
                                    <th class="tl">{{ $lang['change_city'] }}</th>
                                    <th>{{ $lang['logon_service'] }}</th>
                                </tr>
                            </thead>
                            <tbody>

@forelse($user_log as $item)

                                <tr>
                                    <td>{{ $item['log_id'] }}</td>
                                    <td>
									<p>{{ $item['change_time'] }}</p>

@if($item['admin_name'])
<p>{{ $item['admin_name'] }}</p>
@endif

									</td>
                                    <td>{{ $lang['change_type_user'][$item['change_type']] }}</td>
                                    <td>{{ $item['ip_address'] }}</td>
                                    <td>{{ $item['change_city'] }}</td>
                                    <td>
                                        {{ $item['logon_service'] }}
                                    </td>
                                </tr>

@empty

                                <tr>
                                    <td colspan="6">{{ $lang['account_log_empty'] }}</td>
                                </tr>

@endforelse

                            </tbody>
                        </table>
						@include('frontend::library/pages')
                    </div>

                </div>
            </div>
        </div>

@endif



@if($action == 'profile')

        <div class="user-mod user-profile">
            <div class="user-title">
                <h2>{{ $lang['profile'] }}</h2>
				<ul class="tabs">
                    <li class="active"><a href="user.php?act=profile">{{ $lang['profile'] }}</a></li>
                    <li><a href="user.php?act=users_log">{{ $lang['users_log'] }}</a></li>
                </ul>
            </div>
            <div class="user-profile-info">
                <div class="user-items">
                    <form name="formEdit" action="user.php" method="post">
                        <div class="item">
                            <div class="label">{{ $lang['username'] }}：</div>
                            <div class="value"><span class="txt-lh2">{{ $profile['user_name'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['nick_name'] }}：</div>
                            <div class="value">
                                <input name="nick_name" type="text" value="{{ $profile['nick_name'] }}" class="text text-1" onkeyup="updateNick(this.value);" />
                                <div class="notic"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['Birthday_user'] }}：</div>
                            <div class="value">
                                <div id="birthdayYear" class="imitate_select w90 mr10">
                                    <div class="cite"><span>
@if($profile['year'])
{{ $profile['year'] }}
@else
{{ $lang['please_select'] }}
@endif
</span><i class="iconfont icon-down"></i></div>
                                    <ul>

@foreach($select_date['year'] as $year)

                                        <li><a href="javascript:void(0);" data-value="{{ $year }}">{{ $year }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name="birthdayYear" value="{{ $profile['year'] }}" id="birthdayYear_val"/>
                                </div>
                                <div class="imitate_select w90 mr10">
                                    <div class="cite"><span>
@if($profile['month'])
{{ $profile['month'] }}
@else
{{ $lang['please_select'] }}
@endif
</span><i class="iconfont icon-down"></i></div>
                                    <ul>

@foreach($select_date['month'] as $month)

                                        <li><a href="javascript:void(0);" data-value="{{ $month }}">{{ $month }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name="birthdayMonth" value="{{ $profile['month'] }}"/>
                                </div>
                                <div class="imitate_select w90">
                                    <div class="cite"><span>
@if($profile['day'])
{{ $profile['day'] }}
@else
{{ $lang['please_select'] }}
@endif
</span><i class="iconfont icon-down"></i></div>
                                    <ul>

@foreach($select_date['day'] as $data)

                                        <li><a href="javascript:void(0);" data-value="{{ $data }}">{{ $data }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name="birthdayDay" value="{{ $profile['day'] }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['sex_user'] }}：</div>
                            <div class="value">
                                <div class="radio-item">
                                    <input type="radio"
@if($profile['sex'] == 1)
 checked="checked"
@endif
 id="sex-1" name="sex" value="1"class="ui-radio" >
                                    <label for="sex-1" class="ui-radio-label">{{ $lang['male'] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio"
@if($profile['sex'] == 2)
 checked="checked"
@endif
id="sex-2" name="sex" value="2"class="ui-radio" >
                                    <label for="sex-2" class="ui-radio-label">{{ $lang['female'] }}</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio"
@if($profile['sex'] == 0)
 checked="checked"
@endif
 id="sex-3" name="sex" value="0" class="ui-radio">
                                    <label for="sex-3" class="ui-radio-label">{{ $lang['secrecy'] }}</label>
                                </div>
                            </div>
                        </div>

@if($profile['email'] != "")

                        <div class="item">
                            <div class="label">{{ $lang['email_user'] }}：</div>
                            <div class="value">
                                <span class="txt-lh2 fl" id="profile_email">{{ $profile['email'] }}</span>

@if($profile['is_validate'] == 0)

                                <span class="txt-lh2 ml10"><a href="user.php?act=account_safe&type=change_email" class="ftx-05">{{ $lang['Immediately_verify'] }}</a></span>

@else

                                <span class="succeed"><i></i></span>

@endif

                            </div>
                        </div>

@endif


@if($sms_register)

                        <div class="item">
                            <div class="label">{{ $lang['mobile'] }}：</div>
                            <div class="value">

@if($profile['mobile_phone'] != "")

                                <span class="txt-lh2">{{ $profile['mobile_phone'] }}</span>

@else

                                <span class="txt-lh2">
                                    <a id="zphone" class="zphone ftx-05 ml5"
@if($profile['mobile_phone'] == '')
href="user.php?act=account_safe&type=change_phone"
@else
style="cursor:default;"
@endif
>{{ $lang['Immediately_verify'] }}</a>
                                </span>

@endif

                            </div>
                        </div>

@endif



@foreach($extend_info_list as $field)


@if($field['id'] == 6)

                        <div class="item">
                            <div class="label">{{ $lang['passwd_question'] }}：</div>
                            <div class="value">
                                <div class="imitate_select w200">
                                    <div class="cite"><span>
@if($profile['passwd_question_cn'])
{{ $profile['passwd_question_cn'] }}
@else
{{ $lang['please_select'] }}
@endif
</span><i class="iconfont icon-down"></i></div>
                                    <ul>
                                        <li><a href="javascript:void(0);" data-value="0">{{ $lang['sel_question'] }}</a></li>

@foreach($passwd_questions as $key => $data)

                                        <li><a href="javascript:void(0);" data-value="{{ $key }}">{{ $data }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name="sel_question" value="{{ $profile['passwd_question'] }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"
@if($field['is_need'])
id="passwd_quesetion"
@endif
>{{ $lang['passwd_answer'] }}：</div>
                            <div class="value">
                                <input name="passwd_answer" type="text" size="25" class="text text-1" maxlengt='20' value="{{ $profile['passwd_answer'] }}"/>
                            </div>
                        </div>

@else


@if($field['reg_field_name'] != $lang['mobile'])

                        <div class="item mr10">
                            <div class="label"
@if($field['is_need'])
 id="extend_field{{ $field['id'] }}i"
@endif
 >{{ $field['reg_field_name'] }}：</div>
                            <div class="value">
                                <input name="extend_field{{ $field['id'] }}" id="extend_field{{ $field['id'] }}" type="text" class="text text-1" value="{{ $field['content'] }}"/>
                            </div>
                        </div>

@endif


@endif


@endforeach

                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input type="hidden" name="seccode" id="seccode" value="{{ $sms_security_code }}" />
                                <input id="flag" type="hidden" value="register" />
                                <input name="act" type="hidden" value="act_edit_profile" />
                                <a href="javascript:void(0);" class="sc-btn sc-redBg-btn" ectype="submit">{{ $lang['confirm_edit'] }}</a>
                            </div>
                        </div>
                    @csrf </form>
                </div>
                <div class="avatar_change" ectype="upImgTouch">

@if($profile['user_picture'])

                    <img class="am-circle" src="{{ $profile['user_picture'] }}?&'+Math.random();" width="120" height="120">

@else

                    <img class="am-circle" src="{{ skin('images/touxiang.jpg') }}" width="120" height="120">

@endif

                    <a href="javascript:void(0);" class="changeavatar"><div class="shadeDiv"></div><span>{{ $lang['Modify_Avatar'] }}</span></a>
                </div>
            </div>
        </div>


        <div class="am-modal" ectype="docMdal">
            <div class="am-modal-dialog">
                <div class="am-modal-hd">
                    <label>{{ $lang['Modify_Avatar'] }}</label>
                    <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                </div>
                <div class="am-modal-bd">
                    <div class="am-g am-fl">
                        <div class="am-form-group am-form-file">
                            <div class="am-fl">
                                <button type="button" class="am-btn am-btn-default"><i class="iconfont icon-cloud-upload-alt"></i> {{ $lang['select_upload_file'] }}</button>
                            </div>
                            <input type="file" ectype="fileImage" id="inputImage">
                        </div>
                    </div>
                    <div class="am-g am-fl" >
                        <div class="up-pre-before" ectype="up-pre-before"><img alt="" src="" id="image" ectype="image"></div>
                        <div class="up-pre-after"></div>
                    </div>
                    <div class="am-g am-fl">
                        <div class="up-control-btns">
                            <span class="iconfont icon-rotate-left" ectype="rotate" data-type="left"></span>
                            <span class="iconfont icon-rotate-right" ectype="rotate" data-type="right"></span>
                            <span class="iconfont icon-gou" ectype="upBtnOk" id="up-btn-ok" url="ajax_user.php?act=upload_user_picture"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="am-modal am-modal-alert" ectype="modalAlert">
            <div class="am-modal-dialog">
                <div class="am-modal-bd" ectype="alertContent"></div>
                <div class="am-modal-footer"><span class="am-modal-btn">{{ $lang['assign'] }}</span></div>
            </div>
        </div>

@endif





@if($action == 'account_safe')

        <div class="user-mod">

@if($type == 'default')

            <div class="user-title">
                <h2>{{ $lang['account_safe'] }}</h2>
                <div class="user-safe-level level-{{ $security_rating['count'] }}">
                    <div class="level-word">{{ $security_rating['count_info'] }}</div>
                    <div class="level-progress"></div>
                    <div class="level-tip" id="promptInfo">{{ $lang['Security_level_desc'] }}</div>
                </div>
            </div>
            <div class="user-safe-list">
                <div class="item">
                    <div class="item-icon"><i class="iconfont icon-password-alt"></i></div>
                    <div class="item-info">
                        <div class="info-name">{{ $lang['password_user'] }}</div>
                        <p><span class="ftx-01">{{ $lang['password_user_desc'] }}</span></p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=change_password" class="handle-btn">{{ $lang['modify'] }}</a></div>
                </div>
                <div class="item">
                    <div class="item-icon
@if($validate['email_validate'])
 item-icon-green
@else
 item-icon-red
@endif
"><i class="iconfont icon-email"></i></div>

@if($validate['email_validate'])

                    <div class="item-info">
                        <div class="info-name">{{ $lang['email_yanzheng'] }}</div>
                        <p>
                            <span class="ftx-03">{{ $lang['email_yanzheng_you'] }}：</span>
                            <strong class="ftx-06" id="email">{{ $validate['email'] }}</strong>
                        </p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=change_email" class="handle-btn">{{ $lang['modify'] }}</a></div>

@else

                    <div class="item-info">
                        <div class="info-name">{{ $lang['email_yanzheng'] }}</div>
                        <p>{{ $lang['Email_authent_desc'] }}</p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=change_email" class="handle-btn handle-btn-red">{{ $lang['Immediately_verify'] }}</a></div>

@endif

                </div>
                <div class="item">
                    <div class="item-icon
@if($validate['mobile_phone'])
 item-icon-green
@else
 item-icon-red
@endif
"><i class="iconfont icon-mobile-phone"></i></div>

@if($validate['mobile_phone'])

                        <div class="item-info">
                            <div class="info-name">{{ $lang['phone_yanzheng'] }}</div>
                            <p>{{ $lang['phone_authent_desc'] }}</p>
                        </div>
                        <div class="item-handle"><a href="user.php?act=account_safe&type=change_phone" class="handle-btn">{{ $lang['modify'] }}</a></div>

@else

                        <div class="item-info">
                            <div class="info-name">{{ $lang['phone_yanzheng'] }}</div>
                            <p>{{ $lang['phone_authent_desc_one'] }}</p>
                        </div>
                        <div class="item-handle"><a href="user.php?act=account_safe&type=change_phone" class="handle-btn handle-btn-red">{{ $lang['Immediately_verify'] }}</a></div>

@endif

                </div>
                <div class="item">
                    <div class="item-icon
@if($validate['paypwd_id'])
 item-icon-green
@else
 item-icon-red
@endif
"><i class="iconfont icon-password-alt"></i></div>

@if($validate['paypwd_id'])

                    <div class="item-info">
                        <div class="info-name">{{ $lang['pay_password'] }}</div>
                        <p>
                        <span>{{ $lang['Degree_of_safety'] }}：</span>
                            <i id="cla" class="safe-rank04"></i>
                            <span class="ftx-03">{{ $lang['Degree_of_safety_desc'] }}</span>
                        </p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=payment_password&step=zero" class="handle-btn">{{ $lang['pay_password_manage'] }}</a></div>

@else

                    <div class="item-info">
                        <div class="info-name">{{ $lang['pay_password'] }}</div>
                        <p>{{ $lang['Safety_renzheng_desc'] }}</p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=payment_password" class="handle-btn handle-btn-red">{{ $lang['Enable_now'] }}</a></div>

@endif

                </div>
                <div class="item">
                    <div class="item-icon
@if($validate['real_id'])
 item-icon-green
@else
 item-icon-red
@endif
"><i class="iconfont icon-identity"></i></div>

@if($validate['real_id'])

                    <div class="item-info">
                        <div class="info-name">{{ $lang['16_users_real'] }}</div>
                        <p>
                            <span class="ftx-03">{{ $lang['16_users_real_info'] }}：</span>
                            <strong class="ftx-06">{{ $validate['real_name'] }}</strong>
                            <strong class="ftx-06">{{ $validate['bank_card'] }}</strong>
                        </p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=real_name" class="handle-btn">{{ $lang['view'] }}</a></div>

@else

                    <div class="item-info">
                        <div class="info-name">{{ $lang['16_users_real'] }}</div>
                        <p>{{ $lang['16_users_real_desc'] }}</p>
                    </div>
                    <div class="item-handle"><a href="user.php?act=account_safe&type=real_name" class="handle-btn handle-btn-red">{{ $lang['Safety_now'] }}</a></div>

@endif

                </div>
            </div>

@endif



@if($type == 'change_password')

            <div class="type">
                <div class="user-title">
                    <h2>{{ $lang['edit_password_login'] }}</h2>
                </div>

                <div class="stepflex">
                    <dl class="first
@if($step == 'first')
 doing
@elseif ($step == 'second' || $step == 'last')
 done
@endif
">
                        <dt class="s-num">1</dt>
                        <dd class="s-text">{{ $lang['Verify_identity'] }}<s></s><b></b></dd>
                        <dd></dd>
                    </dl>
                    <dl class="normal
@if($step == 'second')
 doing
@elseif ($step == 'last')
 done
@endif
">
                        <dt class="s-num">2</dt>
                        <dd class="s-text">{{ $lang['edit_password_login'] }}<s></s><b></b></dd>
                    </dl>
                    <dl class="last
@if($step == 'last')
 doing
@endif
">

@if($step == 'last')

                        <dt class="s-num">3</dt>

@else

                        <dt class="s-num">&nbsp;</dt>

@endif

                        <dd class="s-text">{{ $lang['complete'] }}<s></s><b></b></dd>
                    </dl>
                </div>

@if($step == 'first')

                <div class="form">
                    <div class="security-warp">

@if($sign == 'mobile')

                        <form name="formUser" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['Verify_phone_in'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input" id="mobile_phone" name="mobile_phone" type="text" value="{{ $user_info['mobile_phone'] }}" readonly />

@if($validate_info['is_validated'] && $validate_info['email'])

                                    <b><a href="user.php?act=account_safe&type=change_password&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif



@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_password&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif

									<div class="form_prompt" id="phone_notice"></div>

                                </div>
                            </div>


@if($enabled_captcha == 1)

							<div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="mobile_captcha" id="mobile_captcha" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">{{ $lang['bindMobile_code'] }}：</div>
                                <div class="form-value">
                                    <input class="itxt" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" type="hidden">
                                    <input name="sms_value" id="sms_value" type="hidden" value="sms_code" class="text text-2" />
									<input name="seKey" id="seKey" type="hidden" value="change_password_f" class="text text-2" />
                                    <input class="form-input" type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled">
                                    <a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                    <div class="form_prompt" id="phone_notice"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">

                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="change_password" name="type">
                                    <input type="hidden" value="second" name="step">

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['button_submit'] }}" id="submitMobile" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>

@elseif ($sign == 'email')

                        <form name="change_email_s" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['Verified_mailbox'] }}：</div>
                                <div class="form-value">
                                    <span>{{ $user_info['email'] }}</span>
                                    <input type="hidden" name="change_email" id="change_email" value="{{ $user_info['email'] }}" />
                                    <input type="hidden" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" />

@if($validate_info['mobile_phone'])

                                    <b><a href="user.php?act=account_safe&type=change_password&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif



@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_password&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif

                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <a id="sendMail" href="javascript:;" data-type="change_pwd" ectype="submitBtn" class="form-btn form-btn-line">{{ $lang['send_verify_email'] }}</a>
                                </div>
                            </div>
                        @csrf </form>

@elseif ($sign == 'paypwd')

                        <form name="formUserPwd" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['input_pay_password'] }}：</div>
                                <div class="form-value">
                                    <input type="password" style="display:none" autocomplete="off"/>
                                    <input class="form-input" type="password" autocomplete="off" name="pay_password" tabindex="1" id="pay_password">


@if($validate_info['is_validated'] && $validate_info['email'])

                                    <b><a href="user.php?act=account_safe&type=change_password&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif



@if($validate_info['mobile_phone'])

                                    <b><a href="user.php?act=account_safe&type=change_password&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif


                                    <div class="form_prompt" id="pay_password_notice"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="paypwd" name="sign">

                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="change_password" name="type">
                                    <input type="hidden" value="second" name="step">

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['submit_goods'] }}" id="submitCode" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>

@endif

                    </div>
                </div>

@if($sign == 'validate_mail_ok')

                <div class="user-pwd-prompt user-prompt-ok">
                    <div class="fore">
                        <h3><span class="ftx-02">{{ $lang['send_email_in'] }}：</span>{{ $user_info['email'] }}&nbsp;&nbsp;</h3>
                        <div class="ftx-03">{{ $lang['send_email_desc_one'] }}</div>
                        <div class="tc mt20"><a class="sc-btn btn35" href="javascript:history.back();">{{ $lang['send_email_desc_two'] }}</a></div>
                    </div>
                </div>

@endif


@elseif ($step == 'second')

                <div class="form">
                	<div class="security-warp">
                        <form class="user-form user-form-safe" action="user.php" name="change_password_s" method="POST" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['new_login_password'] }}：</div>
                                <div class="form-value">
                                	<input type="password" style="display:none" autocomplete="off" />
                                	<input type="password" name="new_password" autocomplete="off" id="password" class="form-input">
                                    <div class="form_prompt" id="new_password_error"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">{{ $lang['input_password_again'] }}：</div>
                                <div class="form-value">
                                	<input type="password" style="display:none" autocomplete="off" />
                                	<input class="form-input" type="password" name="re_new_password" autocomplete="off" id="re_new_password">
                                    <div class="form_prompt" id="rePassword_error"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input type="hidden" name="act" value="account_safe" />
                                    <input type="hidden" name="type" value="change_password" />
                                    <input type="hidden" name="step" value="last" />

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['button_submit'] }}" id="submitSecond" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>
                	</div>
                </div>

@elseif ($step == 'last')

                <div class="user-pwd-prompt user-prompt-ok">
                    <div class="fore">
                        <h3><i class="ok"></i>{{ $lang['security_rating'] }}</h3>
                        <div class="u-safe">{{ $lang['security_rating_one'] }}：<strong class="rank-text ftx-04">{{ $security_rating['count_info'] }}</strong></div>
                        <div class="ftx-03">{{ $lang['security_rating_two'] }}&nbsp;<a href="user.php?act=account_safe" class="ftx-05">{{ $lang['Security_Center'] }}</a>&nbsp;{{ $lang['security_rating_three'] }}</div>
                    </div>
                </div>

@endif

            </div>


@elseif ($type == 'change_email')

            <div class="type">
                <div class="user-title">
                    <h2>{{ $lang['Mailbox_Management'] }}</h2>
                </div>

                <div class="stepflex">
                    <dl class="first
@if($step == 'first')
doing
@elseif ($step == 'second' || $step == 'last' || $step == 'second_email_verify')
done
@endif
">
                        <dt class="s-num">1</dt>
                        <dd class="s-text">{{ $lang['Verify_identity'] }}<s></s><b></b></dd>
                        <dd></dd>
                    </dl>
                    <dl class="normal
@if($step == 'second' || $step == 'second_email_verify')
doing
@elseif ($step == 'last')
done
@endif
">
                        <dt class="s-num">2</dt>

@if($validate_info['is_validated'])

                        <dd class="s-text">{{ $lang['edit_email'] }}<s></s><b></b></dd>

@else

                        <dd class="s-text">{{ $lang['Verify_mailbox'] }}<s></s><b></b></dd>

@endif

                    </dl>
                    <dl class="last
@if($step == 'last')
doing
@endif
">

@if($step == 'last')

                        <dt class="s-num">3</dt>

@else

                        <dt class="s-num">&nbsp;</dt>

@endif

                        <dd class="s-text">{{ $lang['complete'] }}<s></s><b></b></dd>
                    </dl>
                </div>


@if($step == 'first')

                <div class="form">
                    <div class="security-warp">

@if($sign == 'mobile')

                        <form name="formUser" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['Verify_phone_in'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input" id="mobile_phone" name="mobile_phone" type="text" value="{{ $user_info['mobile_phone'] }}" readonly />

@if($validate_info['is_validated'] && $validate_info['email'])

                                    <b><a href="user.php?act=account_safe&type=change_email&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_email&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif


                                    <div class="form_prompt" id="phone_notice"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="mobile_captcha" id="mobile_captcha" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">{{ $lang['bindMobile_code'] }}：</div>
                                <div class="form-value">
                                    <input class="itxt" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" type="hidden">
                                    <input name="sms_value" id="sms_value" type="hidden" value="sms_code" class="text text-2" />
                                    <input name="seKey" id="seKey" type="hidden" value="change_password_f" class="text text-2" />
                                    <input class="form-input" type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled"><a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                    <div class="form_prompt" id="phone_notice"></div>
                                </div>
                            </div>


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="mobile" name="sign">

                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="change_email" name="type">
                                    <input type="hidden" value="second" name="step">

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['button_submit'] }}" id="submitMobile" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>

@elseif ($sign == 'email')

                        <form name="change_email_s" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['Verified_mailbox'] }}：</div>
                                <div class="form-value">
                                    <span class="fl mr10">{{ $user_info['email'] }}</span>
                                    <input type="hidden" name="change_email" id="change_email" value="{{ $user_info['email'] }}" />
                                    <input type="hidden" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" />

@if($validate_info['mobile_phone'])

                                    <b><a href="user.php?act=account_safe&type=change_email&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_email&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif

                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">

@if($is_validated)

                                    <a id="sendMail" href="javascript:;" data-type="change_mail" ectype="submitBtn" class="form-btn form-btn-line">{{ $lang['send_verify_email'] }}</a>

@else

                                    <input name="is_validated" value="{{ $is_validated ?? 0 }}" type="hidden">
                                    <a id="sendMail" href="javascript:;" data-type="edit_mail" ectype="submitBtn" class="form-btn form-btn-line">{{ $lang['send_verify_email'] }}</a>

@endif

                                </div>
                            </div>
                        @csrf </form>

@elseif ($sign == 'paypwd')

                        <form name="formUserPwd" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['input_pay_password'] }}：</div>
                                <div class="form-value">
                                    <input type="password" style="display:none" autocomplete="off" />
                                    <input class="form-input" type="password" name="pay_password" autocomplete="off" tabindex="1" id="pay_password">

@if($validate_info['is_validated'] && $validate_info['email'])

                                    <b><a href="user.php?act=account_safe&type=change_email&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_email&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif

                                    <div class="form_prompt" id="pay_password_notice"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="paypwd" name="sign">

                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="change_email" name="type">
                                    <input type="hidden" value="second" name="step">

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['submit_goods'] }}" id="submitCode" ectype="submitBtn">
                            	</div>
                            </div>
                        @csrf </form>

@endif

                    </div>

@if($sign == 'validate_mail_ok')

                    <div class="user-pwd-prompt user-prompt-ok">
                        <div class="fore">
                            <h3><span class="ftx-02">{{ $lang['send_email_in'] }}：</span>{{ $user_info['email'] }}&nbsp;&nbsp;</h3>
                            <div class="ftx-03">{{ $lang['send_email_desc_one'] }}</div>
                            <a class="sc-btn btn35 mt30" href="javascript:history.back();">{{ $lang['send_email_desc_two'] }}</a>
                        </div>
                    </div>

@endif

                </div>

@elseif ($step == 'second')

                <div class="form">

@if($sign == 'edit_email_ok')

                    <div class="user-pwd-prompt user-prompt-ok">
                        <div class="fore">
                            <h3>{{ $lang['send_email_in'] }}：<span class="txt">{{ $validate_new_mail }}&nbsp;&nbsp;</span></h3>
                            <div class="ftx-01">{{ $lang['send_email_desc_three'] }}</div>
                            <div class="ftx-03">{{ $lang['send_email_desc_one'] }}</div>
                            <a class="sc-btn btn35 mt30" href="javascript:history.back();">{{ $lang['send_email_desc_two'] }}</a>
                        </div>
                    </div>

@else

                    <div class="security-warp">
                        <form name="change_email_s" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['website_email'] }}：</div>
                                <div class="form-value">
                                	<input class="form-input" type="text" name="change_email" id="change_email" value="{{ $user_email }}">
                                  	<div class="form_prompt"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                        		<div class="form-label">&nbsp;</div>
                            	<div class="form-value form-value-btn">
                                	<a id="sendMail" href="javascript:;" data-type="edit_mail" ectype="submitBtn" class="form-btn form-btn-line">{{ $lang['send_verify_email'] }}</a>
                            	</div>
                            </div>
                        @csrf </form>
                    </div>

@endif

                </div>

@elseif ($step == 'last')

                <div class="user-pwd-prompt user-prompt-ok">
                    <div class="fore">

@if($sign == 'editmail_ok')


@if($validated == 1)

                        <h3 class="ftx-02"><i class="ok"></i>{{ $lang['edit_email_desc_two'] }}</h3>

@else

                        <h3 class="ftx-02"><i class="ok"></i>{{ $lang['edit_email_desc_one'] }}</h3>

@endif


@else

                        <h3 class="ftx-02"><i class="ok"></i>{{ $lang['edit_email_desc_two'] }}</h3>

@endif

                        <div class="u-safe">{{ $lang['security_rating_one'] }}：<strong class="rank-text ftx-04">{{ $security_rating['count_info'] }}</strong></div>
                        <div class="ftx-03">{{ $lang['security_rating_two'] }}&nbsp;<a href="user.php?act=account_safe" class="ftx-05">{{ $lang['Security_Center'] }}</a>&nbsp;{{ $lang['security_rating_three'] }}</div>
                    </div>
                </div>

@endif

            </div>


@elseif ($type == 'change_phone')

            <div class="type">
                <div class="user-title">
                    <h2>{{ $lang['phone_Management'] }}</h2>
                </div>

                <div class="stepflex">
                    <dl class="first
@if($step == 'first')
doing
@elseif ($step == 'second' || $step == 'last')
done
@endif
">
                        <dt class="s-num">1</dt>
                        <dd class="s-text">{{ $lang['Verify_identity'] }}<s></s><b></b></dd>
                        <dd></dd>
                    </dl>
                    <dl class="normal
@if($step == 'second')
doing
@elseif ($step == 'last')
done
@endif
">
                        <dt class="s-num">2</dt>

@if($validate_info['mobile_phone'])

                        <dd class="s-text">{{ $lang['phone_edit'] }}<s></s><b></b></dd>

@else

                        <dd class="s-text">{{ $lang['Verify_phone'] }}<s></s><b></b></dd>

@endif

                    </dl>
                    <dl class="last
@if($step == 'last')
doing
@endif
">

@if($step == 'last')

                        <dt class="s-num">3</dt>

@else

                        <dt class="s-num">&nbsp;</dt>

@endif


                        <dd class="s-text">{{ $lang['complete'] }}<s></s><b></b></dd>
                    </dl>
                </div>


@if($step == 'first')

                <div class="form">
                    <div class="security-warp">

@if($sign == 'mobile')

                        <form name="formUser" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['Verify_phone_in'] }}：</div>
                                <div class="form-value">

@if($validate_info['mobile_phone'])

                                    <input class="form-input" id="mobile_phone" name="mobile_phone" type="text" value="{{ $user_info['mobile_phone'] }}" readonly />

@else

                                    <input class="form-input" id="mobile_phone" name="mobile_phone" type="text" value="{{ $user_info['mobile_phone'] }}" />

@endif


@if($validate_info['is_validated'] && $validate_info['email'])

                                    <b><a href="user.php?act=account_safe&type=change_phone&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_phone&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif


                                    <div class="form_prompt" id="phone_notice"></div>

                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="mobile_captcha" id="mobile_captcha" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">{{ $lang['bindMobile_code'] }}：</div>
                                <div class="form-value">

@if($validate_info['mobile_phone'])

                                    <input class="itxt" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" type="hidden">

@endif

                                    <input name="sms_value" id="sms_value" type="hidden" value="sms_code" class="text text-2" />
                                    <input name="seKey" id="seKey" type="hidden" value="change_password_f" class="text text-2" />
                                    <input class="form-input" type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled">
                                    <a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                    <div class="form_prompt" id="phone_notice"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="mobile" name="sign">

                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="change_phone" name="type">
                                    <input type="hidden" value="second" name="step">

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['button_submit'] }}" id="submitMobile" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>

@elseif ($sign == 'email')

                        <form name="change_email_s" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['Verified_mailbox'] }}：</div>
                                <div class="form-value">
                                    <span class="fl mr10">{{ $user_info['email'] }}</span>
                                    <input type="hidden" name="change_email" id="change_email" value="{{ $user_info['email'] }}" />
                                    <input type="hidden" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" />

@if($validate_info['mobile_phone'])

                                    <b><a href="user.php?act=account_safe&type=change_phone&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                    <b><a href="user.php?act=account_safe&type=change_phone&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif

                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                	<a id="sendMail" href="javascript:;" data-type="change_mobile" ectype="submitBtn" class="form-btn form-btn-line">{{ $lang['send_verify_email'] }}</a>
                                </div>
                            </div>
                        @csrf </form>

@elseif ($sign == 'paypwd')

                        <form name="formUserPwd" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['input_pay_password'] }}：</div>
                                <div class="form-value">
                                    <input type="password" style="display:none" autocomplete="off" />
                                    <input class="form-input" type="password" name="pay_password" autocomplete="off" tabindex="1" id="pay_password">

@if($validate_info['is_validated'] && $validate_info['email'])

                                    <b><a href="user.php?act=account_safe&type=change_phone&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif



@if($validate_info['mobile_phone'])

                                    <b><a href="user.php?act=account_safe&type=change_phone&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif

                                    <label class="error" id="pay_password_notice"><i></i></label>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

                            <div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <label class="error" id="code_notice"><i></i></label>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="paypwd" name="sign">

                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="change_phone" name="type">
                                    <input type="hidden" value="second" name="step">

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['submit_goods'] }}" id="submitCode" ectype="submitBtn">
                            	</div>
                            </div>
                        @csrf </form>

@endif

                    </div>


@if($sign == 'validate_mail_ok')

                    <div class="user-pwd-prompt user-prompt-ok">
                        <s class="icon-succ02 m-icon"></s>
                        <div class="fore">
                            <h3><span class="ftx-02">{{ $lang['send_email_in'] }}：</span>{{ $user_info['email'] }}&nbsp;&nbsp;</h3>
                            <div class="ftx-03">{{ $lang['send_email_desc_one'] }}</div>
                            <a class="sc-btn btn35 mt30" href="javascript:history.back();">{{ $lang['send_email_desc_two'] }}</a>
                        </div>
                    </div>

@endif

                </div>

@elseif ($step == 'second')

                <div class="form">
                    <div class="security-warp">
                        <form name="formUser" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['label_mobile'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input" type="text" name="mobile_phone" tabindex="1" id="mobile_phone" value="
@if($mobile_phone)
{{ $mobile_phone }}
@endif
">
                                    <div class="form_prompt" id="phone_notice"></div>
                                </div>
                            </div>


@if($enabled_captcha == 1)

							<div class="form-row">
                                <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="form-value">
                                    <input class="form-input authCode txt-4" name="mobile_captcha" id="mobile_captcha" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


                            <div class="form-row">
                                <div class="form-label">{{ $lang['bindMobile_code'] }}：</div>
                                <div class="form-value">
                                    <input name="sms_value" id="sms_value" type="hidden" value="sms_code" class="text text-2" />
                                    <input class="form-input" type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled">
                                    <a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                    <div class="form_prompt" id="phone_notice"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">

@if($validate_info['mobile_phone'])

                                    <input id="carry" type="hidden" value="insert" name="carry">

@else

                                    <input id="carry" type="hidden" value="update" name="carry">

@endif

                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
									<input name="seKey" id="seKey" type="hidden" value="change_password_f" class="text text-2" />
                                    <input type="hidden" name="act" value="account_safe" />
                                    <input type="hidden" name="type" value="change_phone" />
                                    <input type="hidden" name="step" value="last" />

                                    <input type="submit" class="form-btn form-btn-line" value="{{ $lang['lang_crowd_next_step'] }}" id="submitMobile" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>
                    </div>
                </div>

@elseif ($step == 'last')

                <div class="user-pwd-prompt user-prompt-ok">
                    <div class="fore">
                        <h3><i class="ok"></i>{{ $lang['bind_phone_user'] }}</h3>
                        <div class="u-safe">{{ $lang['security_rating_one'] }}：<strong class="rank-text ftx-02">{{ $security_rating['count_info'] }}</strong></div>
                        <div class="ftx-03">{{ $lang['security_rating_two'] }}&nbsp;<a href="user.php?act=account_safe" class="ftx-05">{{ $lang['Security_Center'] }}</a>&nbsp;{{ $lang['security_rating_three'] }}</div>
                    </div>
                </div>

@endif

            </div>


@elseif ($type == 'payment_password')

            <div class="type">
                <div class="user-title">
                    <h2>{{ $lang['pay_password_Management'] }}</h2>
                </div>

@if($step != 'zero')

                <div class="stepflex">
                    <dl class="first
@if($step == 'first')
 doing
@elseif ($step == 'second' || $step == 'last')
 done
@endif
">
                        <dt class="s-num">1</dt>
                        <dd class="s-text">{{ $lang['Verify_identity'] }}<s></s><b></b></dd>
                        <dd></dd>
                    </dl>
                    <dl class="normal
@if($step == 'second')
 doing
@elseif ($step == 'last')
 done
@endif
">
                        <dt class="s-num">2</dt>

@if($validate_info['pay_password'])

                        <dd class="s-text">{{ $lang['edit_pay_password'] }}<s></s><b></b></dd>

@else

                        <dd class="s-text">
@if($password_type == 1)
{{ $lang['reset_pay_password'] }}
@else
{{ $lang['Enable_pay_password'] }}
@endif
<s></s><b></b></dd>

@endif

                    </dl>

                    <dl class="last
@if($step == 'last')
 doing
@endif
">

@if($step == 'last')

                        <dt class="s-num">3</dt>

@else

                        <dt class="s-num">&nbsp;</dt>

@endif

                        <dd class="s-text">{{ $lang['complete'] }}<s></s><b></b></dd>
                    </dl>
                </div>

@endif



@if($step == 'zero')

                <div class="user-pwd-prompt user-prompt-ok">
                    <div class="fore">
                        <h3 class="pt80 pb20"><i class="ok"></i>{{ $lang['pay_password_Prompt'] }}</h3>
                        <div class="ftx-03">
                            <a href="user.php?act=account_safe&type=payment_password&sign=paypwd" class="sc-btn btn35">{{ $lang['edit_pay_password'] }}</a>
                            <a href="user.php?act=account_safe&type=payment_password&sign=mobile&password_type=1" class="sc-btn btn35">{{ $lang['forgot_paypassword'] }}</a>
                        </div>
                    </div>
                </div>

@endif



@if($step == 'first')

                <div class="form">
                    <div class="security-warp">

@if($sign == 'mobile')

                    <form name="formUser" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                        <div class="form-row">
                            <div class="form-label">{{ $lang['Verify_phone_in'] }}：</div>
                            <div class="form-value">
								<input class="form-input" id="mobile_phone" name="mobile_phone" type="text" value="{{ $user_info['mobile_phone'] }}" readonly />


@if($validate_info['is_validated'] && $validate_info['email'])

                                <b><a href="user.php?act=account_safe&type=payment_password&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                <b><a href="user.php?act=account_safe&type=payment_password&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif

								<div class="form_prompt" id="phone_notice"></div>
                            </div>
                        </div>


@if($enabled_captcha == 1)

						<div class="form-row">
                            <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                            <div class="form-value">
                                <input class="form-input authCode txt-4" name="mobile_captcha" id="mobile_captcha" type="text" value="">
                                <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                <div class="form_prompt" id="code_notice"></div>
                            </div>
                        </div>

@endif


                        <div class="form-row">
                            <div class="form-label">{{ $lang['bindMobile_code'] }}：</div>
                            <div class="form-value">
                                <input class="itxt" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" type="hidden">
                                <input name="sms_value" id="sms_value" type="hidden" value="sms_code" class="text text-2" />
								<input name="seKey" id="seKey" type="hidden" value="change_password_f" class="text text-2" />
                                <input class="form-input" type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled"><a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                <div class="form_prompt" id="phone_notice"></div>
                            </div>
                        </div>


                        <div class="form-row">
                        	<div class="form-label">&nbsp;</div>
                            <div class="form-value form-value-btn">
                                <input id="flag" type="hidden" value="change_password_f" name="flag">
                                <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                <input type="hidden" value="account_safe" name="act">
                                <input type="hidden" value="payment_password" name="type">
                                <input type="hidden" value="second" name="step">
                                <input type="hidden" value="{{ $password_type ?? 0 }}" name="password_type">
                                <input type="submit" class="form-btn form-btn-line" value="{{ $lang['button_submit'] }}" id="submitMobile" ectype="submitBtn">
                        	</div>
                        </div>
                    @csrf </form>

@elseif ($sign == 'email')

                    <form name="change_email_s" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                        <div class="form-row">
                            <div class="form-label">{{ $lang['Verified_mailbox'] }}：</div>
                            <div class="form-value">
                                <span class="fl mr10">{{ $user_info['email'] }}</span>
                                <input type="hidden" name="change_email" id="change_email" value="{{ $user_info['email'] }}" />
                                <input type="hidden" name="mobile_phone" id="mobile_phone" value="{{ $user_info['mobile_phone'] }}" />

@if($validate_info['mobile_phone'])

                                <b><a href="user.php?act=account_safe&type=payment_password&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                <b><a href="user.php?act=account_safe&type=payment_password&sign=paypwd" class="ftx-14">{{ $lang['Verify_password_in'] }}</a></b>

@endif

                            </div>
                        </div>


@if($enabled_captcha == 1)

                        <div class="form-row">
                            <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                            <div class="form-value">
                                <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                <div class="form_prompt" id="code_notice"></div>
                            </div>
                        </div>

@endif


                        <div class="form-row">
                        	<div class="form-label">&nbsp;</div>
                            <div class="form-value form-value-btn">
                            	<a id="sendMail" href="javascript:;" data-type="change_paypwd" ectype="submitBtn" class="form-btn form-btn-line">{{ $lang['send_verify_email'] }}</a>
                            </div>
                        </div>
                    @csrf </form>

@elseif ($sign == 'paypwd')

                    <form name="formUserPwd" method="post" action="user.php" class="user-form user-form-safe" ectype="user_security_from">
                        <div class="form-row">
                            <div class="form-label">{{ $lang['input_pay_password'] }}：</div>
                            <div class="form-value">
                                <input type="password" style="display:none" autocomplete="off" />
                                <input class="form-input" type="password" name="pay_password" autocomplete="off" tabindex="1" id="pay_password">

@if($validate_info['is_validated'] && $validate_info['email'])

                                <b><a href="user.php?act=account_safe&type=payment_password&sign=email" class="ftx-14">{{ $lang['Verify_email_in'] }}</a></b>

@endif


@if($validate_info['pay_password'])

                                <b><a href="user.php?act=account_safe&type=payment_password&sign=mobile" class="ftx-14">{{ $lang['Verify_phone_user'] }}</a></b>

@endif

                                <div class="form_prompt" id="pay_password_notice"></div>
                            </div>
                        </div>


@if($enabled_captcha == 1)

                        <div class="form-row">
                            <div class="form-label">{{ $lang['comment_captcha'] }}：</div>
                            <div class="form-value">
                                <input class="form-input authCode txt-4" name="authCode" id="authCode" type="text" value="">
                                <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                <div class="form_prompt" id="code_notice"></div>
                            </div>
                        </div>

@endif


                        <div class="form-row">
                        	<div class="form-label">&nbsp;</div>
                            <div class="form-value form-value-btn">
                                <input id="flag" type="hidden" value="change_password_f" name="flag">
                                <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                <input type="hidden" value="paypwd" name="sign">

                                <input type="hidden" value="account_safe" name="act">
                                <input type="hidden" value="payment_password" name="type">
                                <input type="hidden" value="second" name="step">

                                <input type="submit" class="form-btn form-btn-line" value="{{ $lang['submit_goods'] }}" id="submitCode" ectype="submitBtn">
                            </div>
                        </div>
                    @csrf </form>

@endif

                    </div>


@if($sign == 'validate_mail_ok')

                    <div class="user-pwd-prompt user-prompt-ok">
                        <s class="icon-succ02 m-icon"></s>
                        <div class="fore">
                            <h3><span class="ftx-02">{{ $lang['send_email_in'] }}：</span>{{ $user_info['email'] }}&nbsp;&nbsp;</h3>
                            <div class="ftx-03">{{ $lang['send_email_desc_one'] }}</div>
                            <a class="sc-btn btn35 mt30" href="javascript:history.back();">{{ $lang['send_email_desc_two'] }}</a>
                        </div>
                    </div>

@endif

                </div>

@elseif ($step == 'second')

                <div class="form">
                	<div class="security-warp">
                        <form class="user-form user-form-safe" action="user.php" name="payment_password" method="POST" ectype="user_security_from">
                            <div class="form-row">
                                <div class="form-label">{{ $lang['pay_password'] }}：</div>
                                <div class="form-value">
                                    <input type="password" style="display:none" autocomplete="off" />
                                    <input class="form-input" type="password" name="new_password" autocomplete="off" id="password">
                                    <div class="form_prompt" id="new_password_error"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">{{ $lang['confirm_pay_password'] }}：</div>
                                <div class="form-value">
                                	<input type="password" style="display:none" autocomplete="off"/>
                                	<input type="password" name="re_new_password" autocomplete="off" id="re_new_password" class="form-input">
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="form-row ftx-04 mb0">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value"><div class="lh25">{{ $lang['Enable_pay_password_desc'] }}</div></div>
                            </div>
                            <!--<div class="form-rowm mt0">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value">
                                    <div class="fl">
                                        <input type="checkbox"
@if($user_paypwd['user_surplus'])
 checked="checked"
@endif
 name="user_surplus" id="user_surplus" value="1" class="ui-checkbox">
                                        <label class="ui-label" for="user_surplus">{{ $lang['balance_pay'] }}</label>
                                    </div>
                                </div>
                            </div>-->
                            <div class="form-row">
                                <div class="form-label">&nbsp;</div>
                                <div class="form-value form-value-btn">
                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="payment_password" name="type">
                                    <input type="hidden" value="last" name="step">
                                    <input type="hidden" value="{{ $password_type ?? 0 }}" name="password_type">
                                    <input type="submit" value="{{ $lang['submit_goods'] }}" class="form-btn form-btn-line" id="submitSecond" ectype="submitBtn">
                                </div>
                            </div>
                        @csrf </form>
                	</div>
                </div>

@elseif ($step == 'last')

                <div class="user-pwd-prompt user-prompt-ok">
                    <div class="fore">
                        <h3><i class="ok"></i>{{ $lang['Enable_pay_password_notice'] }}</h3>
                        <div class="u-safe">{{ $lang['security_rating_one'] }}：<strong class="rank-text ftx-04">{{ $security_rating['count_info'] }}</strong></div>
                        <div class="ftx-03">{{ $lang['security_rating_two'] }}&nbsp;<a href="user.php?act=account_safe" class="ftx-05">{{ $lang['Security_Center'] }}</a>&nbsp;{{ $lang['security_rating_three'] }}</div>
                    </div>
                </div>

@endif

            </div>


@elseif ($type == 'real_name')

            <div class="type">
                <div class="user-title">
                    <h2>{{ $lang['16_users_real'] }}</h2>
                </div>

@if($step == 'first')

                <div class="account-main account-bind">
                    <div class="user-items">
                        <form name="real_name" method="post" action="user.php" enctype="multipart/form-data" class="user-form user-form-safe">
                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['Real_name'] }}：</div>
                                <div class="value">
                                    <input class="text text-1" type="text" id="real_name" name="real_name" value="{{ $real_user['real_name'] }}">
                                    <div id="span-notify-holderName" class="notic">{{ $lang['Real_name_notice'] }}</div>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['number_ID'] }}：</div>
                                <div class="value">
                                    <input class="text text-5" type="text" id="self_num" name="self_num" value="{{ $real_user['self_num'] }}">
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['bank'] }}：</div>
                                <div class="value">
                                    <input class="text text-1" type="text" id="bank_name" name="bank_name" value="{{ $real_user['bank_name'] }}">
                                    <div class="notic">{{ $lang['bank_name_notic'] }}</div>
                                  	<div class="form_prompt"></div>
                                </div>
                            </div>

                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['bank_card'] }}：</div>
                                <div class="value">
                                    <input class="text text-5" type="text" id="bank_card" value="{{ $real_user['bank_card'] }}" name="bank_card" ectype="bank_card">
                                    <div class="form_prompt"></div>
                                </div>
                            </div>

                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['front_of_id_card'] }}：</div>
                                <div class="value">
                                	<div class="type-file-box">
                                        <input type="button" name="button" id="button" class="type-file-button">
                                        <input type="file" name="front_of_id_card" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($real_user['front_of_id_card'])
<span class="show"><a href="{{ $real_user['front_of_id_card'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $real_user['front_of_id_card'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                        <input type="text" name="textfile_zheng" class="type-file-text" id="textfile_zheng" value="{{ $real_user['front_of_id_card'] }}" readonly>
                                    </div>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['reverse_of_id_card'] }}：</div>
                                <div class="value">
                                	<div class="type-file-box">
                                        <input type="button" name="button" id="button" class="type-file-button">
                                        <input type="file" name="reverse_of_id_card" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($real_user['reverse_of_id_card'])
<span class="show"><a href="{{ $real_user['reverse_of_id_card'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $real_user['reverse_of_id_card'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                        <input type="text" name="textfile_fan" class="type-file-text" id="textfile_fan" value="{{ $real_user['reverse_of_id_card'] }}" readonly>
                                    </div>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['label_mobile'] }}：</div>
                                <div class="value">
                                    <input class="text text-1" type="text" id="mobile_phone" name="mobile_phone" value="{{ $real_user['bank_mobile'] }}">
                                    <div class="form_prompt"></div>
                                </div>
                            </div>

@if($enabled_captcha == 1)

                            <div class="item">
                                <div class="label">{{ $lang['comment_captcha'] }}：</div>
                                <div class="value">
                                    <input class="form-input authCode txt-4" name="captcha" id="captcha" type="text" value="">
                                    <img src="captcha_verify.php?captcha=change_password_f&{{ $rand }}" alt="captcha" id="captcha_img" class="fl mr10" onClick="this.src='captcha_verify.php?captcha=change_password_f&'+Math.random()" width="100" height="40" />
                                    <div class="form_prompt" id="code_notice"></div>
                                </div>
                            </div>

@endif


@if($enabled_sms_signin)

                            <div class="item">
                                <div class="label">{{ $lang['bindMobile_code'] }}：</div>
                                <div class="value">
                                    <div class="sm-input">
                                        <input name="sms_value" id="sms_value" type="hidden" value="sms_code" />
                                        <input type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled">

                                        <a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                    </div>
                                    <div class="form_prompt"id="phone_notice"></div>
                                </div>
                            </div>

@endif

                            <div class="item item-button">
                                <div class="label">&nbsp;</div>
                                <div class="value">
                                    <input id="flag" type="hidden" value="change_password_f" name="flag">
                                    <input name="seKey" id="seKey" type="hidden" value="change_password_f" class="text text-2" />
                                    <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                    <input type="hidden" value="account_safe" name="act">
                                    <input type="hidden" value="real_name" name="type">
                                    <input type="hidden" value="second" name="step">
                                    <input type="hidden" value="{{ $operate }}" name="operate">
                                    <input type="submit" id="authSubmit" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_goods'] }}">
                                </div>
                            </div>
                        @csrf </form>
                    </div>
                </div>

@endif



@if($step == 'realname_ok')

                <div class="account-main account-bind">
                    <div class="user-items">
                        <div class="item">
                            <div class="label">{{ $lang['Real_name'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $real_user['real_name'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['number_ID'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $real_user['self_num'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['bank'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $real_user['bank_name'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['bank_card'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $real_user['bank_card'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['label_mobile'] }}：</div>
                            <div class="value">
                                <span class="txt-lh">{{ $real_user['bank_mobile'] }}</span>
                                <a href="user.php?act=account_safe&type=change_phone" class="ftx-05 ml10">{{ $lang['modify'] }}</a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['front_of_id_card'] }}：</div>
                            <div class="value">
                                <a href="{{ $real_user['front_of_id_card'] }}" target="_blank" class="nyroModal fl mt5">
                                	<i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $real_user['front_of_id_card'] }}>')" onmouseout="toolTip()"></i>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['reverse_of_id_card'] }}：</div>
                            <div class="value">
                                <a href="{{ $real_user['reverse_of_id_card'] }}" target="_blank" class="nyroModal fl mt5">
                                	<i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $real_user['reverse_of_id_card'] }}>')" onmouseout="toolTip()"></i>
                                </a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['Verify_time'] }}：</div>
                            <div class="value">
                                <span class="txt-lh">{{ $real_user['validate_time'] }}</span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['adopt_status'] }}：</div>
                            <div class="value">
                                <span class="txt-lh red">
@if($real_user['review_status'] == 1)
{{ $lang['is_confirm']['1'] }}
@elseif ($real_user['review_status'] == 2)
{{ $lang['is_confirm']['2'] }}
@else
{{ $lang['is_confirm']['0'] }}
@endif
</span>
                            </div>
                        </div>


@if($real_user['review_content'] && $real_user['review_status'] == 2)

                        <div class="item">
                            <div class="label">{{ $lang['audit_statement'] }}：</div>
                            <div class="value">
                                <span class="txt-lh red">{{ $real_user['review_content'] }}</span>
                            </div>
                        </div>

@endif


                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <a href="{{ $edit_user }}" class="sc-btn sc-redBg-btn">{{ $lang['edit_real_complete'] }}</a>
                            </div>
                        </div>
                    </div>
                </div>

@endif

            </div>

@endif

        </div>

@endif





@if($action == 'order_list' || $action == 'order_recycle')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['order_list'] }}</h2>
                <ul class="tabs">
                    <li
@if($order_type != 'toBe_confirmed' && $order_type != 'toBe_pay' && $order_type != 'toBe_confirmed' && $order_type != 'toBe_finished')
class="active"
@endif
><a href="user_order.php?act=order_list">{{ $lang['all_order'] }}({{ $allorders }})</a></li>
                    <li class="user-count1
@if($order_type == 'toBe_pay')
active
@endif
" onclick="get_OrderSearch('payId', '{{ $action }}', 'toBe_pay','','.user-count1')">
                    <a href="javascript:void(0);">{{ $lang['cs']['100'] }}({{ $pay_count }})</a>
                    <input name="toBe_pay" id="payId" type="hidden" value="100" />
                    </li>
                    <li class="user-count2
@if($order_type == 'toBe_confirmed')
active
@endif
" onclick="get_OrderSearch('to_confirm_order', '{{ $action }}', 'toBe_confirmed','','.user-count2')">
                    <a href="javascript:void(0);">{{ $lang['receipt_not'] }}({{ $to_confirm_order }})</a>
                    <input name="to_confirm_order" id="to_confirm_order" type="hidden" value="103" />
                    </li>
                    <li class="user-count4
@if($order_type == 'toBe_finished')
active
@endif
" onclick="get_OrderSearch('to_finished', '{{ $action }}', 'toBe_finished','','.user-count4')">
                    <a href="javascript:void(0);">{{ $lang['cs']['102'] }}({{ $to_finished }})</a>
                    <input name="to_finished" id="to_finished" type="hidden" value="102" />
                    </li>
                </ul>

@if($action == 'order_list')

                <a href="user_order.php?act=order_recycle" class="more">{{ $lang['Order_recycling_station'] }}</a>

@else

                <a href="user_order.php?act=order_list" class="more">{{ $lang['order_list'] }}</a>

@endif

            </div>
            <div class="user-list-title clearfix">
                <div class="user-list-filter">
                    <div id="order_status" class="imitate_select w120 mr10">
                        <div class="cite"><span>{{ $lang['all_status'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li id="order_status_-1"><a href="javascript:void(0);" data-idtxt="status_list" data-action="{{ $action }}" data-type="order_status" data-id="-1" data-value='-1'>{{ $lang['all_status'] }}</a></li>

@foreach($status_list as $key => $list)

                            <li id="order_status_{{ $key }}"><a href="javascript:void(0);" data-idtxt="status_list" data-action="{{ $action }}" data-type="order_status" data-id="{{ $key }}" data-value='{{ $key }}'>{{ $list }}</a></li>

@endforeach

                        </ul>
                        <input name="order_status_list" type="hidden" value="-1" id="order_status_val">
                    </div>
                    <div id="dateTime" class="imitate_select w120">
                        <div class="cite"><span>{{ $lang['all_time'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li id="time_allDate"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="allDate" data-value="allDate">{{ $lang['all_time'] }}</a></li>
                            <li id="time_today"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="today" data-value="today">{{ $lang['Today'] }}</a></li>
                            <li id="time_three_today"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="three_today" data-value="three_today">{{ $lang['three_today'] }}</a></li>
                            <li id="time_aweek"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="aweek" data-value="aweek">{{ $lang['aweek'] }}</a></li>
                            <li id="time_thismonth"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="thismonth" data-value="thismonth">{{ $lang['thismonth'] }}</a></li>
                        </ul>
                        <input name="order_submitDate" type="hidden" value="allDate" id="dateTime_val">
                    </div>
                </div>
                <div class="user-list-search">
                    <input type="text" id="ip_keyword" class="text" onkeydown="javascript:if(event.keyCode==13) get_OrderSearch('ip_keyword');" onblur="if (this.value=='') this.value=this.defaultValue; this.style.color='#999'" onfocus="if (this.value==this.defaultValue)this.value='';this.style.color='#666'" placeholder="{{ $lang['search_oreder_user'] }}" name="" style="color:#999;"/>
                    <button type="button" onclick="get_OrderSearch('ip_keyword', '{{ $action }}', 'text')"><i class="iconfont icon-search"></i></button>
                </div>
            </div>
            <div id="user_order_list">

@if(!$order_type)

            @include('frontend::library/user_order_list')

@endif

            </div>
        </div>

@endif





@if($action == 'order_detail' || $action == 'auction_order_detail')

        <div class="user-mod user-order-detail">
            <div class="user-title">
                <h2>{{ $lang['order_detail'] }}</h2>
                <a href="user_message.php?act=message_list&is_order=1&order_id={{ $order['order_id'] }}" class="more">{{ $lang['type']['0'] }}<em>&nbsp;&nbsp;(&nbsp;{{ $feedback_num }}&nbsp;)</em></a>
            </div>
            <div class="order-detail-statu">
                <div class="statu-left">
                    <p>

@if($order['order_status'] == 4 || $order['order_status'] == 7)

                    {{ $order['order_status_desc'] }}{{ $lang['your_order'] }}

@elseif (($order['order_status'] == 2 || $order['order_status'] == 3) && $order['pay_status'] == 0 && $order['shipping_status'] == 0)

                    {{ $order['order_status_desc'] }}{{ $lang['your_order'] }}

@elseif ($order['order_status'] == 1 && $order['pay_status'] == 0 && $order['shipping_status'] == 0)

                    {{ $order['shipping_status_desc'] }}{{ $lang['your_order'] }}

@else

                    {{ $order['pay_status_desc'] }}{{ $lang['your_order'] }}

@endif

                    <span style="color: deepskyblue;">
@if($is_baitiao)

@if($stages_info['is_stages'])
【{{ $lang['Ious_staging'] }}】
@else
【{{ $lang['baitiao_order'] }}】
@endif

@endif
</span>
                    </p>
                    <p>{{ $lang['order_number'] }}：{{ $order['order_sn'] }} </p>
                    @if($order['activity_lang'] != '')
                    <div class="user-order-list" style="margin: 0px; width: 80px; overflow: hidden; float: none;">
                        <div class="item-t-qb" style="padding: 0px;">
                            <em class="em-promotion em-promotion-1">{{ $order['activity_lang'] }}</em>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="statu-right mt10">

@if($order['affirm_received'])

                    <a href="{{ $order['affirm_received'] }}" onclick="if (!confirm('{{ $lang['confirm_received'] }}')) return false;" class="sc-btn sc-blue-btn">{{ $lang['received'] }}</a>

@endif

                </div>
            </div>

            <ul class="order-detail-progress progress-{{ $order['order_tracking'] }}">
                <li>{{ $lang['order_addtime'] }}<div class="tip">{{ $order['formated_add_time'] }}</div></li>
                <li>{{ $lang['pay'] }}<div class="tip">
@if($order['pay_time'])
{{ $order['pay_time'] }}
@endif
</div></li>
                <li>{{ $lang['deliver_goods'] }}<div class="tip">
@if($order['shipping_time'])
{{ $order['shipping_time'] }}
@endif
</div></li>
                <li>{{ $lang['collect_goods'] }}<div class="tip">
@if($order['confirm_take_time'])
{{ $order['confirm_take_time'] }}
@endif
</div></li>
                <li>{{ $lang['complete'] }}<div class="tip"></div></li>
            </ul>
            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['consignee_info'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['consignee'] }}：</div>
                        <div class="item-value">{{ $order['consignee'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['label_mobile'] }}：</div>
                        <div class="item-value">{{ $order['mobile'] }}</div>
                    </div>
                    <div class="info-item
@if($order['leader_id'] > 0)
hide
@endif
">
                        <div class="item-label">{{ $lang['label_address'] }}：</div>
                        <div class="item-value">{{ $order['region'] }}&nbsp;{{ $order['address'] }}</div>
                    </div>
                </div>
            </div>


@if($order['store_id'])

            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['offline_store_information'] }}</h2></div>
                <div class="info-content">

@if($order['pick_code'] && $order['pay_status'] == 2)

                    <div class="info-item">
                        <div class="item-label">{{ $lang['pick_code'] }}：</div>
                        <div class="item-value red">{{ $order['pick_code'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['tpnd_time'] }}：</div>
                        <div class="item-value">{{ $order['take_time'] }}</div>
                    </div>

@endif



@if($offline_store)

                    <div class="info-item">
                        <div class="item-label">{{ $lang['stores_name'] }}：</div>
                        <div class="item-value">{{ $offline_store['stores_name'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['contact_phone'] }}：</div>
                        <div class="item-value">{{ $offline_store['stores_tel'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['user_address'] }}：</div>
                        <div class="item-value">{{ $offline_store['province'] }}&nbsp;{{ $offline_store['city'] }}&nbsp;{{ $offline_store['district'] }}&nbsp;{{ $offline_store['stores_address'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['stores_opening_hours'] }}：</div>
                        <div class="item-value">{{ $offline_store['stores_opening_hours'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['stores_traffic_line'] }}：</div>
                        <div class="item-value">{{ $offline_store['stores_traffic_line'] }}</div>
                    </div>

@if($offline_store['stores_img'])

                    <div class="info-item">
                        <div class="item-label">{{ $lang['stores_img'] }}：</div>
                        <div class="item-value"><a href="{{ $offline_store['stores_img'] }}" class="nyroModal ftx-05">{{ $lang['view_image'] }}</a></div>
                    </div>

@endif


@endif

                </div>
            </div>

@endif



@if($order['extension_code'] != 'wholesale')

            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['pay_info'] }}</h2></div>

@if(!$seckill_status || $order['pay_time'])

                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['payment_method'] }}：</div>
                        <div class="item-value">{{ $order['pay_name'] }}</div>

@if($order['pay_code'] == 'bank')

                        <div class="item-value ftx-08">({!! $order['pay_desc'] !!})</div>

@endif

                        </div>

@if($is_presale)


@if($settle_status > 0)


@if($is_onlinepay)


@if($payment_list)


@if($order['show_pay'] == 1)

                                <div class="info-item">
                                    <div class="item-label lh30">{{ $lang['pay_noline'] }}：</div>
                                    <div class="item-value">
                                        <form name="payment" method="post" action="user.php">
                                        <select name="pay_id">

@foreach($payment_list as $payment)

                                          <option value="{{ $payment['pay_id'] }}">
                                          {{ $payment['pay_name'] }}({{ $lang['pay_fee'] }}:{{ $payment['format_pay_fee'] }})
                                          </option>

@endforeach

                                        </select>
                                        <input type="hidden" name="act" value="act_edit_payment" />
                                        <input type="hidden" name="order_id" value="{{ $order['order_id'] }}" />
                                        <input type="submit" name="Submit" class="btn sc-redBg-btn btn30 ml5" value="{{ $lang['button_submit'] }}" />
                                        @csrf </form>
                                    </div>
                  				</div>

@endif


@endif



@if($allow_edit_surplus)


@if($order['show_pay'] == 1)

                                <div class="info-item">
                                    <div class="item-label lh30">{{ $lang['use_surplus'] }}：</div>
                                    <div class="item-value">
                                    <form action="user.php" method="post" name="formFee" id="formFee" class="formfee" onsubmit="return paymentForm(this)">

@if($open_pay_password)

                                        <label>{{ $lang['pay_password'] }}：</label>
                                        <input type="password" style="display:none" autocomplete="off" />
                                        <input name="pay_pwd" id="pay_pwd_val" type="password" autocomplete="off" class="text text-2" size="20" />

@endif

                                        <input name="surplus" id='ECS_SURPLUS' type="text" size="8" value="{{ $order['order_amount'] ?? 0 }}" class="text text-4 mr5"/>
                                        <input type="submit" name="Submit" class="btn sc-redBg-btn btn30 ml5" value="{{ $lang['button_submit'] }}" class="btn sc-redBg-btn btn30" />
                                        <span>{!! $max_surplus !!}</span>
                                        <input type="hidden" name="act" value="act_edit_surplus" />
                                        <input type="hidden" name="pay_status" value="{{ $order['extension_code'] }}" />
                                        <input type="hidden" name="order_id" value="{{ request()->get('order_id') }}" />
                                    @csrf </form>
                                    <script type="text/javascript">
                                    function paymentForm(frm){

@if($open_pay_password)

                                        var pwd_erorr = 0;
                                        var pay_pwd = $("#pay_pwd_val").val();
                                        $.ajax({
                                            type:"post",
                                            url:"ajax_user.php?act=pay_pwd",
                                            data:'pay_pwd=' + pay_pwd,
                                            dataType: 'json',
                                            async : false, //设置为同步操作就可以给全局变量赋值成功
                                            success:function(result){
                                                pwd_erorr = result.error;
                                            }
                                        });

                                        if(pwd_erorr == 1 || pay_pwd == '' || pay_pwd == 0){
                                            pbDialog(json_languages.pay_password_packup_null,"",0);
                                            return false;
                                        }else if(pwd_erorr == 2){
                                            pbDialog(json_languages.pay_password_packup_error,"",0);
                                            return false;
                                        }

@endif


                                        var surplus = $("#ECS_SURPLUS").val();
                                        if(surplus == '' || surplus == 0){
                                            pbDialog(json_languages.surplus_null,"",0);
                                            return false;
                                        }else{
											if(!/^[0-9]+\.?[0-9]*$/.test(surplus)){
												pbDialog(json_languages.surplus_isnumber,"",0);
                                                return false;
											}
                                        }
                                    }
                                    </script>
                                    </div>
                                </div>

@endif


@endif


@endif


@elseif ($settle_status < 0)

                        <div class="info-item">
                            <div class="item-label">{{ $lang['pay_prompt'] }}：</div>
                            <div class="item-value">{{ $lang['end_pay_time'] }}：<span class="ftx-01">{{ $pay_end_time }}</span>！</div>
                        </div>

@endif

                        <div class="info-item">
                            <div class="item-label">{{ $lang['pay_prompt'] }}：</div>
                            <div class="item-value">{{ $lang['pay_end_one'] }}<span style='color:red'>{{ $pay_start_time }}</span >{{ $lang['zhi'] }}<span style='color:red'>{{ $pay_end_time }}</span>{{ $lang['pay_end_two'] }}</div>
                        </div>

@else


@if($is_onlinepay)


@if($payment_list)


@if($order['show_pay'] == 1)

                                <div class="info-item">
                                    <div class="item-label lh30">{{ $lang['pay_noline'] }}：</div>
                                    <div class="item-value">
                                        <form name="payment" method="post" action="user.php">
                                            <select name="pay_id" class="select">

@foreach($payment_list as $payment)

                                              <option value="{{ $payment['pay_id'] }}">
                                              {{ $payment['pay_name'] }}({{ $lang['pay_fee'] }}:{{ $payment['format_pay_fee'] }})
                                              </option>

@endforeach

                                            </select>
                                            <input type="hidden" name="act" value="act_edit_payment" />
                                            <input type="hidden" name="order_id" value="{{ $order['order_id'] }}" />
                                            <input type="submit" name="Submit" class="btn sc-redBg-btn btn30 ml5" value="{{ $lang['button_submit'] }}" />
                                        @csrf </form>
                                    </div>
                                </div>

@if($allow_edit_surplus)

                                <div class="info-item">
                                    <div class="item-label lh30">{{ $lang['use_surplus'] }}：</div>
                                    <div class="item-value">
                                        <form action="user.php" method="post" name="formFee" id="formFee" class="formfee" onsubmit="return paymentForm(this)">

@if($open_pay_password)

                                            <label>{{ $lang['pay_password'] }}：</label>
                                            <input type="password"   style="display:none"/>
                                            <input name="pay_pwd" id="pay_pwd_val" type="password" class="text text-2" size="20" />

@endif

                                            <input name="surplus" id='ECS_SURPLUS' type="text" size="8" value="{{ $order['order_amount'] ?? 0 }}" class="text text-4 mr5" />
                                            <input type="submit" name="Submit" class="btn sc-redBg-btn btn30" value="{{ $lang['button_submit'] }}" />
                                            <span>{!! $max_surplus !!}</span>
                                            <input type="hidden" name="act" value="act_edit_surplus" />
                                            <input type="hidden" name="pay_status" value="{{ $order['extension_code'] }}" />
                                            <input type="hidden" name="order_id" value="{{ request()->get('order_id') }}" />
                                        @csrf </form>
                                    </div>
                                  <script type="text/javascript">
                                    function paymentForm(frm){

@if($open_pay_password)

                                        var pwd_erorr = 0;
                                        var pay_pwd = $("#pay_pwd_val").val();
                                        $.ajax({
                                            type:"post",
                                            url:"ajax_user.php?act=pay_pwd",
                                            data:'pay_pwd=' + pay_pwd,
                                            dataType: 'json',
                                            async : false, //设置为同步操作就可以给全局变量赋值成功
                                            success:function(result){
                                                pwd_erorr = result.error;
                                            }
                                        });

                                        if(pwd_erorr == 1 || pay_pwd == '' || pay_pwd == 0){
                                            pbDialog(json_languages.pay_password_packup_null,"",0);
                                            return false;
                                        }else if(pwd_erorr == 2){
                                            pbDialog(json_languages.pay_password_packup_error,"",0);
                                            return false;
                                        }

@endif


                                        var surplus = $("#ECS_SURPLUS").val();
                                        if(surplus == '' || surplus == 0){
                                            pbDialog(json_languages.surplus_null,"",0);
                                            return false;
                                        }else{
											if(!/^[0-9]+\.?[0-9]*$/.test(surplus)){
												pbDialog(json_languages.surplus_isnumber,"",0);
                                                return false;
											}
                                        }
                                    }
                                    </script>
                                </div>

@endif


@endif


@endif


@endif


@endif


                    <div class="info-item">
                        <div class="item-label lh30">{{ $lang['detail_pay_status'] }}：</div>
                        <div class="item-value lh30">
                            {{ $order['pay_status_desc'] }}
@if($order['order_amount'] > 0 && $order['pay_online'])

                            <div class="or-btn">{!! $order['pay_online'] !!}</div>

@else

    @if($order['pay_code']=='chunsejinrong' && $order['pay_status']==0)

        <div class="or-btn">
            <div class="alipay" style="text-align:center">
            <input onclick="get_user_chunsejinrong();" value="{{ $lang['ious_pay'] }}" type="button">
            </div>
        </div>
        <script type="text/javascript">
            function get_user_chunsejinrong(){
                location.href = "flow.php?step=done&act=chunsejinrong&order_sn={{ $order['order_sn'] }}";
            }
        </script>

    @endif

@endif

                        </div>
                    </div>

            @if($order['pay_code'] == 'bank' && $order['pay_status'] == 0)
            <div class="info-item">
                <!-- 上传汇款凭证 -->
                <div class="item-label lh30"> &nbsp;</div>
                <div class="item-value lh30">
                    <div class="upload-pay-document-btn" >
                        <div class="bank" style="text-align:center">

                        @if($order['pay_document'])
                        <a class="button" href="{{ $order['pay_document'] }}" target="_blank" >{{ __('order.see_pay_document') }}</a>
                        @else
                        <input type="button" id="upload_pay_document" value="{{ __('order.upload_pay_document') }}" >
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif


@if($order['pay_effective_time'] >0)

                    <div class="info-item">
                        <div class="item-label">{{ $lang['pay_time_alt'] }}：</div>
                        <div class="item-value">
                            <div class="time" ectype="time" data-time="{{ $order['pay_effective_time'] }}">
                                <span></span>
                                <span class="days red">00</span>
                                <em>:</em>
                                <span class="hours red">00</span>
                                <em>:</em>
                                <span class="minutes red">00</span>
                                <em>:</em>
                                <span class="seconds red">00</span>
                            </div>
                        </div>
                    </div>

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
                    <script type="text/javascript">
                        $("*[ectype='time']").each(function(){

                            $(this).yomi();
                            // 倒计时为0，刷新页面
                            var $this = $(this);
                            var i = 0;
                            var inter = setInterval(function(){
                                var days = parseInt($this['find'](".days").text());
                                var hours = parseInt($this['find'](".hours").text());
                                var minutes = parseInt($this['find'](".minutes").text());
                                var seconds = parseInt($this['find'](".seconds").text());
                                if(days == 0 && hours == 0 && minutes == 0 && seconds == 0){
                                    clearInterval(inter)
                                    if(i != 0){
                                        location.reload()
                                    }
                                    return false;
                                }
                                i++
                            },1000)
                        });
                    </script>

@endif


@if($order['pay_time'])

                    <div class="info-item">
                        <div class="item-label">{{ $lang['pay_time_alt'] }}：</div>
                        <div class="item-value">{{ $order['pay_time'] }}</div>
                    </div>

@endif

                </div>

@else

                <div class="statu-left">
                    <p class="red">{{ $lang['order_prompt_notic'] }}</p>
                </div>

@endif

            </div>

@endif



@if($order['pay_code'] == 'bank')

            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['remittance_info'] }}</h2></div>
                <div class="info-content">

@foreach($order['pay_config'] as $bankinfo)

                    <div class="info-item">
                        <div class="item-label">{{ $bankinfo['name'] }}：</div>
                        <div class="item-value">{{ $bankinfo['value'] }}</div>
                    </div>

@endforeach

                </div>
            </div>

@endif


            <div class="user-info-list">
                <div class="info-title">
                    <h2>{{ $lang['shipping_info'] }}</h2>
                    @if($order['dsc_shipping_status'] == 1 && $open_order_delay == 1 && $order['allow_order_delay'] == 1)
                        <a href="javascript:void(0);" class="sc-btn sc-blue-btn ml10" data-id="{{ $order['order_id'] }}" id="sbumit_order_delay">{{ $lang['order_delayed'] }}</a>
                        @if($order['user_order_delay_num'] > 0)
                        (已申请延迟收货)
                        @endif
                    @endif

                </div>
                <div class="info-content">
                    <div class="info-item
@if($order['shipping_name'] == '')
hide
@endif
">
                        <div class="item-label">{{ $lang['shipping'] }}：</div>

@if($order['store_id'])

						<div class="item-value" id="shipping_name_{{ $order['order_id'] }}">{{ $lang['self_lift'] }}</div>

@else

						<div class="item-value" id="shipping_name_{{ $order['order_id'] }}">{{ $order['shipping_name'] }}
@if($order['point'])
<a class="ml10" href="javascript:;" ectype="btn-selfPoint">{{ $lang['view_self_lift'] }}</a>
@endif
</div>

@endif

                        <input type="hidden" id="shipping_name_{{ $order['order_id'] }}_code" value="{{ $order['shipping_code_name'] }}">
                        <input type="hidden" id="order_id" value="{{ $order['order_id'] }}">

                    </div>

@if($order['invoice_no'])

                    <div class="info-item">
                        <div class="item-label">{{ $lang['Waybill_number'] }}：</div>
                        <div class="item-value" id="invoice_no_{{ $order['order_id'] }}">
							<ul ectype="invoice_no_list">

@foreach($order['invoice_no_list'] as $invoice_no_item)

								<li data-invoice_no="{{ $invoice_no_item }}">
									<span>{{ $invoice_no_item }}</span>

@if($order['invoice_no_count'] > 1)
<a href="javascript:view_logistics_info('{{ $order['order_id'] }}', '{{ $invoice_no_item }}');" class="ml10">{{ $lang['view_logistics'] }}</a>
@endif

								</li>

@endforeach

							</ul>
						</div>
                    </div>

@endif

                    <div class="info-item">
                        <div class="item-label">{{ $lang['detail_shipping_status'] }}：</div>
                        <div class="item-value">{{ $order['shipping_status_desc'] }}</div>
                    </div>

@if($order['shipping_time'])

                    <div class="info-item">
                        <div class="item-label">{{ $lang['shipping_date'] }}：</div>
                        <div class="item-value">{{ $order['shipping_time'] }}</div>
                    </div>

@endif



@if($order['dsc_shipping_status'] == 1)

					<div class="info-item">
						<div class="item-label">{{ $lang['received'] }}：</div>
						<div class="item-value">
							<div class="time" ectype="time" data-time="{{ $order['auto_delivery_time'] }}">
								<span></span>
								<span class="days red">00</span>
								<em>:</em>
								<span class="hours red">00</span>
								<em>:</em>
								<span class="minutes red">00</span>
								<em>:</em>
								<span class="seconds red">00</span>
							</div>
						</div>
					</div>

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
					<script type="text/javascript">
					$("*[ectype='time']").each(function(){
						$(this).yomi();
					});
					</script>

@endif


@if($order['best_time'])

                    <div class="info-item">
                        <div class="item-label">{{ $lang['deliver_time'] }}：</div>
                        <div class="item-value">{{ $order['best_time'] }}</div>
                    </div>

@endif
                    @if(!empty($order['cross_warehouse_name']))
                    <div class="info-item">
                        <div class="item-label">{{ $lang['delivery_warehouse'] }}：</div>
                        <div class="item-value">
                            {{ $order['cross_warehouse_name'] }}
                        </div>
                    </div>
                    @endif

@if($order['point'])

					<div id="selfPoint" style="display:none;">
						<div class="user-info-list">
							<div class="info-content">
								<div class="info-item">
									<div class="item-label">{{ $lang['pay_name'] }}：</div>
									<div class="item-value">{{ $order['point']['name'] }}</div>
								</div>
								<div class="info-item">
									<div class="item-label">{{ $lang['contact_username'] }}：</div>
									<div class="item-value">{{ $order['point']['user_name'] }}</div>
								</div>
								<div class="info-item">
									<div class="item-label">{{ $lang['contact_phone'] }}：</div>
									<div class="item-value">{{ $order['point']['mobile'] }}</div>
								</div>
								<div class="info-item">
									<div class="item-label">{{ $lang['user_address'] }}：</div>
									<div class="item-value">{{ $order['point']['address'] }}</div>
								</div>
								<div class="info-item">
									<div class="item-label">{{ $lang['line_of_arrival'] }}：</div>
									<div class="item-value">{{ $order['point']['line'] }}</div>
								</div>
								<div class="info-item">
									<div class="item-label">{{ $lang['self_lift_time'] }}：</div>
									<div class="item-value">{{ $order['point']['pickDate'] }}</div>
								</div>
							</div>
						</div>
					</div>
				    <script type="text/javascript">
						//点击自提信息弹出框
						$(document).on('click', "a[ectype='btn-selfPoint']", function(){
							var content = $("#selfPoint").html();
							pb({
								id:divId,
								title:'{{ $lang['since_some_info'] }}',
								width:400,
								height:250,
								content:content,
								drag:false,
								foot:false,
							});
						});
					</script>

@endif

                </div>
            </div>
            <div class="user-info-list
@if($order['leader_id'] == 0)
hide
@endif
">
                <div class="info-title"><h2>{{ $lang['community_post_info'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item
@if(!$order['post_delivery_code'])
hide
@endif
" >
                        <div class="item-label">{{ $lang['post_delivery_code'] }}：</div>
                        <div class="item-value red">{{ $order['post_delivery_code'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['post_address'] }}：</div>
                        <div class="item-value">{{ $order['region'] }}&nbsp;{{ $order['address'] }}</div>
                    </div>
                    <div class="info-item ">
                        <div class="item-label">{{ $lang['label_post_mobile'] }}：</div>
                        <div class="item-value">{{ $order['post_mobile'] }}</div>
                    </div>
                </div>
            </div>

@if($order['invoice_no'])


@if($order['invoice_no_count'] == 1)

                    <div class="user-info-list">
                        <div class="info-title"><h2>{{ $lang['logistics_tracking'] }}</h2></div>
                        <div class="info-content">
                            <div class="logistics-items" id="retData_{{ $order['order_id'] }}"></div>
                        </div>
                    </div>

@endif

          	<script type="text/javascript">
                $(function(){
                    var expressid = $("#shipping_name_" + {{ $order['order_id'] }} + "_code").val();
                    var order_id = $("#order_id").val();
                    //var expressno = $("#invoice_no_" + {{ $order['order_id'] }}).html();
                    var expressno = $("[ectype='invoice_no_list']").find("li:first").data("invoice_no");

                    //var expressno = '765671340310';
                    $("#retData_" + {{ $order['order_id'] }}).html("<center>"+json_languages.logistics_tracking_in+"</center>");

                    $.ajax({
                        url: "tracker_shipping",
                        type: "get",
                        data:'act=asyn&type=' + expressid + '&postid=' + expressno +'&order_id=' + order_id,
                        success: function(data,textStatus){
                            $("#retData_" + {{ $order['order_id'] }}).html(data.content);
                        },
                         error: function(o){
                        }
                    });
                });

				//查看物流
				function view_logistics_info(order_id, invoice_no){
                    Ajax.call("get_ajax_content.php?act=view_logistics_info", 'order_id=' + order_id + "&invoice_no=" + invoice_no, function (result) {
                        pb({
                            id:'logistics_info_dialog',
                            title:'{{ $lang['search_logistics'] }}',
                            width:1200,
                            height:800,
                            content:'',
                            drag:false,
                            foot:false,
                        });

                        //插入物流信息
                        $.ajax({
                            url: "tracker_shipping",
                            type: "get",
                            data:'act=asyn&type=' + result.expressid + '&postid=' + result.expressno + '&order_id=' + order_id,
                            success: function(data){
                                $("#logistics_info_dialog .pb-ct").html(data.content);
                            },
                            error: function(o){
                            }
                        });
                    }, 'POST', 'JSON');

				}
            </script>

@endif


@if($can_invoice)

            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['Invoice_information'] }}</h2></div>

@if($order['invoice_type'] == 0)

                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['invoice_type'] }}：</div>
                        <div class="item-value">
@if($order['extension_code'] != 'exchange_goods')
{{ $lang['need_invoice'][$order['invoice_type']] }}
@else
{{ $lang['wu'] }}
@endif
</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['invoice_content'] }}：</div>
                        <div class="item-value">
@if($order['extension_code'] != 'exchange_goods')
{{ $order['inv_content'] }}
@else
{{ $lang['wu'] }}
@endif
</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['invoice_title'] }}：</div>
                        <div class="item-value">
@if($order['extension_code'] != 'exchange_goods')
{{ $order['inv_payee'] }}
@else
{{ $lang['wu'] }}
@endif
</div>
                    </div>

@if($order['tax_id'])

					<div class="info-item">
                        <div class="item-label">{{ $lang['tax_id'] }}：</div>
                        <div class="item-value">{{ $order['tax_id'] }}</div>
                    </div>

@endif

                </div>

@else

				<div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['invoice_type'] }}：</div>
                        <div class="item-value">{{ $lang['need_invoice'][$order['invoice_type']] }}</div>
                    </div>
					<div class="info-item">
                        <div class="item-label">{{ $lang['adopt_status'] }}：</div>
                        <div class="item-value">

@if($vat_info['audit_status'] == 0)

								{{ $lang['not_audited'] }}

@elseif ($vat_info['audit_status'] == 1)

								{{ $lang['audited_adopt'] }}

@elseif ($vat_info['audit_status'] == 2)

								{{ $lang['audited_not_adopt'] }}

@endif

						</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['label_company_name'] }}：</div>
                        <div class="item-value">{{ $vat_info['company_name'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['tax_id'] }}：</div>
                        <div class="item-value">{{ $vat_info['tax_id'] }}</div>
                    </div>
                </div>

@endif

            </div>

@endif


@if($order['postscript'])

             <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['Order_note_user'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['Order_note_user'] }}：</div>
                        <div class="item-value">{{ $order['postscript'] }}</div>
                    </div>
                </div>
            </div>

@endif

            <div class="pt10 pb10">
                <a
                @if($order['ru_id'] > 0)
                    href="merchants_store.php?merchant_id={{ $order['ru_id'] }}" target="_blank"
                @else
                    href="javascript:void(0);"
                @endif
                 class="user-shop-link">{{ $order['shop_name'] }}</a>
                <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $order['ru_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="iconfont icon-kefu user-shop-kefu"></a>

@if($order['return_url'])
<a href="{!! $order['return_url'] !!}" class="more">{{ $lang['return_apply'] }}</a>
@endif

            </div>

@if($order['is_zc_order'] == 1)

            <table class="user-table user-table-detail-goods">
                <colgroup>
                    <col width="320">
                    <col width="110">
                    <col width="120">
                    <col width="95">
                    <col width="90">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>{{ $lang['zc_project_name'] }}</th>
                        <th class="tl">{{ $lang['start_time'] }}</th>
                        <th class="tl">{{ $lang['end_time'] }}</th>
                        <th class="tl">{{ $lang['zc_project_raise_money'] }}</th>
                        <th class="tl">{{ $lang['zc_goods_price'] }}</th>
                        <th class="tl">{{ $lang['freight'] }}</th>
                        <th>{{ $lang['ws_subtotal'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="product-item first">
                                <div class="p-img fl"><a href="crowdfunding.php?act=detail&id={{ $zc_goods_info['pid'] }}" target="_blank"><img src="{{ $zc_goods_info['title_img'] }}" width="55" height="55"></a></div>
                                <div class="p-name fl ml10">
                                <a href="crowdfunding.php?act=detail&id={{ $zc_goods_info['pid'] }}" class="ftx-07" target="_blank">{{ $zc_goods_info['title'] }}</a>
                                </div>
                            </div>
                        </td>
                        <td>{{ $zc_goods_info['start_time'] }}</td>
                        <td>{{ $zc_goods_info['end_time'] }}</td>
                        <td>{{ $zc_goods_info['formated_amount'] }}</td>
                        <td>{{ $zc_goods_info['formated_price'] }}</td>
                        <td>{{ $zc_goods_info['formated_shipping_fee'] }}</td>
                        <td>{{ $zc_goods_info['formated_price'] }}</td>
                    </tr>
                </tbody>
            </table>

@else

            <table class="user-table user-table-detail-goods">
                <colgroup>
                    <col width="210">
                    <col width="110">
                    <col width="120">
                    <col width="95">
                    <col width="90">
                    <col>
					<col width="110">
                </colgroup>
                <thead>
                    <tr>
                        <th width="40%">{{ $lang['goods'] }}</th>
                        <th width="20%" class="tl">{{ $lang['goods_attr'] }}</th>
                        <th width="10%" class="tl">{{ $lang['user_goods_sn'] }}</th>
                        <th width="10%" class="tl">{{ $lang['delivery_warehouse'] }}</th>
                        <th width="10%" class="tl">{{ $lang['unit_price_user'] }}</th>
                        <th width="10%">{{ $lang['ws_subtotal'] }}</th>
                    </tr>
                </thead>
                <tbody>

@foreach($goods_list as $goods)

                    <tr>
                        <td>

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                            <a href="{!! $goods['url'] !!}" class="img"><img src="{{ $goods['goods_thumb'] }}" alt=""></a>

@endif


@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                            <a href="{!! $goods['url'] !!}" class="name"
@if($goods['is_real'] == 0 && $goods['virtual_info'])
 style="margin-top:7px;"
@endif
>
                                @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                {{ $goods['goods_name'] }}
                            </a>
                            <p class="fl ftx-05">{{ $lang['give_integral'] }}：{{ $goods['give_integral'] }}</p>

@if($goods['parent_id'] > 0)

                            <p class="fl ftx-01">（{{ $lang['accessories'] }}）</p>

@elseif ($goods['is_gift'])

                            <p class="fl ftx-01">（{{ $lang['largess'] }}）</p>

@endif


@elseif ($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                        <a href="javascript:void(0)" onclick="setSuitShow({{ $goods['goods_id'] }})" class="name"
@if($goods['is_real'] == 0 && $goods['virtual_info'])
 style="margin-top:7px;"
@endif
>
                            @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                            {{ $goods['goods_name'] }}<span class="red">{{ $lang['remark_package'] }}<i class="iconfont icon-down"></i></span>
                        </a>
                            <div class="suit" id="suit_{{ $goods['goods_id'] }}">

@foreach($goods['package_goods_list'] as $package_goods_list)

                                  <a href="goods.php?id={{ $package_goods_list['goods_id'] }}" target="_blank">
                                    <img src="{{ $package_goods_list['goods_thumb'] }}" />
                                  </a>

@endforeach

                            </div>

@endif


@if($goods['is_real'] == 0 && $goods['virtual_info'])

                            <div id="virtual_div_{{ $goods['goods_id'] }}" class="virtual_div">

                                <div id="show_virtual_info_{{ $goods['goods_id'] }}" class="virtual_title">{{ $lang['virtual_goods_info'] }}</div>

                                <div id="virtual_info_{{ $goods['goods_id'] }}" class="virtual_info">

@foreach($goods['virtual_info'] as $virtual)

                                    <div class="virtual_info_item">
                                        <div class="v_arrow"><em></em><span></span></div>
                                        <p>{{ $lang['card_sn'] }}：{{ $virtual['card_sn'] }}</p>
                                        <p>{{ $lang['card_password'] }}：{{ $virtual['card_password'] }}</p>
                                        <p>{{ $lang['end_date'] }}：{{ $virtual['end_date'] }}</p>
                                    </div>

@endforeach

                                </div>

                            </div>

@endif

                        </td>
                        <td>{!! nl2br($goods['goods_attr']) !!}</td>
                        <td>{{ $goods['goods_sn'] }}</td>
                        <td>{{ $goods['warehouse_name'] }}</td>
                        <td><p>{{ $goods['goods_price'] }}</p><p>×{{ $goods['goods_number'] }}</p></td>
                        <td>
                            <p class="ftx-01">{{ $goods['subtotal'] }}</p>

@if($goods['dis_amount'] > 0)

                            <p class="ftx-05">({{ $lang['Discount_user'] }}：{{ $goods['discount_amount'] }})</p>

@endif

                        </td>
                    </tr>

@endforeach

                </tbody>
            </table>

@endif

            <div class="user-order-detail-total">

@if($goods_list_count > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['order_goods_number'] }}：</dt>
                    <dd class="total-value">{{ $goods_list_count }}{{ $lang['jian'] }}</dd>
                </dl>

@endif

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['shopping_money'] }}：</dt>
                    <dd class="total-value">
@if($order['extension_code'] == "group_buy" && $order['is_group_deposit'] == 1)
{{ $lang['gb_deposit'] }}
@endif
{{ $order['formated_goods_amount'] }}</dd>
                </dl>

@if($order['discount'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['discount'] }}：</dt>
                    <dd class="total-value">-  {{ $order['formated_discount'] }}</dd>
                </dl>

@endif


@if($order['tax'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['tax'] }}：</dt>
                    <dd class="total-value">+  {{ $order['formated_tax'] }}</dd>
                </dl>

@endif


@if($cross_border_version && $order['rate_fee'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['general_tax'] }}：</dt>
                    <dd class="total-value">+  {{ $order['formated_rate_fee'] }}</dd>
                </dl>

@endif


@if($order['insure_fee'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['insure_fee'] }}：</dt>
                    <dd class="total-value">+  {{ $order['formated_insure_fee'] }}</dd>
                </dl>

@endif


@if($order['pay_fee'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['pay_fee'] }}：</dt>
                    <dd class="total-value">+  {{ $order['formated_pay_fee'] }}</dd>
                </dl>

@endif


@if($order['pack_fee'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['pack_fee'] }}：</dt>
                    <dd class="total-value">+  {{ $order['formated_pack_fee'] }}</dd>
                </dl>

@endif


@if($order['card_fee'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['card_fee'] }}：</dt>
                    <dd class="total-value">+ {{ $order['formated_card_fee'] }}</dd>
                </dl>

@endif


@if($order['shipping_fee'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['shipping_fee'] }}：</dt>
                    <dd class="total-value">+ {{ $order['formated_shipping_fee'] }}</dd>
                </dl>

@endif


@if($order['use_val'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['use_value_card'] }}：</dt>
                    <dd class="total-value">- {{ $order['formated_value_card'] }}</dd>
                </dl>

@endif

    @if($order['vc_dis_money'] > 0)

        <dl class="total-row">
            <dt class="total-label">{{ $lang['value_card_dis'] }}：</dt>
            <dd class="total-value">- {{ $order['formated_vc_dis_money'] }}</dd>
        </dl>


@endif


@if($order['surplus'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['use_surplus'] }}：</dt>
                    <dd class="total-value">- {{ $order['formated_surplus'] }}</dd>
                </dl>

@endif


@if($order['integral'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ __('user.integral_offset') }}：</dt>
                    <dd class="total-value">- {{ $order['formated_integral_money'] }}
                        <br><em class="red">（{{ $order['integral'] }} {{ __('user.points') }}）</em>
                    </dd>
                </dl>

@endif


@if($order['bonus'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['use_bonus'] }}：</dt>
                    <dd class="total-value">- {{ $order['formated_bonus'] }}</dd>
                </dl>

@endif


@if($order['coupons'] > 0)

                <dl class="total-row">
                    <dt class="total-label">{{ $lang['preferential'] }}：</dt>
                    <dd class="total-value">- {{ $order['formated_coupons'] }}</dd>
                </dl>

@endif

                <dl class="total-row">
                    <dt class="total-label">

@if($order['order_amount'] > 0 && $order['extension_code'] == 'presale' && $order['pay_status'] == 0)

                    	{{ $lang['Deposit_user'] }}：

@else


@if($order['pay_status'] == 2)


@if($order['order_amount'] < 0)
{{ $lang['should_back_amount'] }}：
@else
{{ $lang['user_money_paid'] }}：
@endif


@else

                            {{ $lang['user_order_amount'] }}：

@endif


@endif

                    </dt>
                    <dd class="total-value">
@if($order['pay_status'] == 2)
{{ $order['formated_realpay_amount'] }}
@else
{{ $order['formated_order_amount'] }}
@endif
</dd>
                </dl>
            </div>
        </div>
        <script type="text/javascript">
        var timer;
        function checkOrder(){
            var log_id = '{{ $order['log_id'] ?? 0 }}';
            var pay_code = '{{ $order['pay_code'] ?? 0 }}';

            if(pay_code == 'wxpay'){
                var url = "user_order.php?act=checkorder&log_id=" + log_id+ "&pay_code=" + pay_code;
                $.get(url, {}, function(data){
                    //已付款
                    if(data.code == 1){
                        clearTimeout(timer);
                        location.href = "user_order.php?act=order_detail&order_id="+{{ $order['order_id'] }};
                    }
                },'json');
                timer = setTimeout("checkOrder()", 5000);
            }
        }
        checkOrder();
        </script>

@endif




@if($action == 'return_detail')

<style>
    /*文字两端对齐 */
    .user-info-list .info-item .text-align-justify {
        display: inline-block;
        text-align:justify;
        text-align-last:justify;
        width: 60px;
    }
</style>
        <form id="returnInfo" name="returnInfo" method="post" action="user_order.php" onsubmit="return shipping_sub(this)">
            <div class="user-mod user-order-detail">
                <div class="user-title mb0">
                    <h2>{{ $lang['detailed_info'] }}</h2>
                </div>
                <div class="user-info-list b-t-0">
                    <div class="info-title"><h2>{{ $lang['detailed_info'] }}</h2></div>
                    <div class="info-content">
                        <div class="info-item">
                            <div class="item-label"><em class="text-align-justify">{{ $lang['order_number'] }}</em>：</div>
                            <div class="item-value">{{ $goods['order_sn'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label"><em class="text-align-justify">{{ $lang['return_sn'] }}</em>：</div>
                            <div class="item-value">{{ $goods['return_sn'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label"><em class="text-align-justify">{{ $lang['apply_time'] }}</em>：</div>
                            <div class="item-value">{{ $goods['apply_time'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label"><em class="text-align-justify">{{ $lang['return_type_user'] }}</em>：</div>
                            <div class="item-value">
@if($goods['return_type'] == 0)
{{ $lang['order_return_type']['0'] }}
@elseif ($goods['return_type'] == 1)
{{ $lang['order_return_type']['1'] }}
@elseif ($goods['return_type'] == 2)
{{ $lang['order_return_type']['2'] }}
@elseif ($goods['return_type'] == 3)
{{ $lang['order_return_type']['3'] }}
@endif
</div>
                        </div>
                    </div>
                </div>
                <div class="user-info-list">
                    <div class="info-title"><h2>{{ $lang['order_status'] }}</h2></div>
                    <div class="info-content">
                        <div class="info-item">
                            <div class="item-label">{{ $lang['order_status'] }}：</div>
                            <div class="item-value">{{ $goods['return_status'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['back_cause'] }}：</div>
                            <div class="item-value">{{ $goods['return_cause'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['problem_desc'] }}：</div>
                            <div class="item-value">{{ $goods['return_brief'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['order_status'] }}：</div>
                            <div class="item-value ftx-01">
@if($goods['return_status1'] < 0)
{{ $lang['only_return_money'] }}
@else
{{ $lang['rf'][$goods['return_status1']] }}
@endif
-{{ $lang['ff'][$goods['refound_status1']] }}</div>
                        </div>

@if($goods['action_note'])

                        <div class="info-item">
                            <div class="item-label">{{ $lang['after_service_desc'] }}：</div>
                            <div class="item-value">{{ $goods['action_note'] }}</div>
                        </div>

@endif

                    </div>
                </div>

                @if(!empty($address) && $goods['agree_apply'] == 1)
                <div class="user-info-list">
                    <div class="info-title"><h2>{{ $lang['return_address'] }}</h2></div>
                    <div class="info-content">
                        <div class="info-item">
                            <div class="item-label">{{ $lang['contact_username'] }}：</div>
                            <div class="item-value">{{ $address['contact'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['Contact_address'] }}：</div>
                            <div class="item-value">{{ $address['province'].$address['city'].$address['district'].$address['address'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['contact_phone'] }}：</div>
                            <div class="item-value">{{ $address['mobile'] }}</div>
                        </div>
                    </div>
                </div>
                @endif

@if($goods['return_type'] == 2)

                <div class="user-info-list">
                    <div class="info-title"><h2>{{ $lang['progress_return'] }}</h2></div>
                    <div class="user-order-list">
                        <dl class="item relative" data-orderid="{{ $goods['order_id'] }}">
                            <dt class="item-t b-b-0">
                                <div class="t-statu">
@if($goods['return_status1'] == 4)
{{ $lang['is_confirmed'] }}
@else
{{ $lang['zc_ss'] }}
@endif
</div>
                                <div class="t-info">
                                    <span class="info-item">{{ $lang['order_number'] }}：{{ $goods['order_sn'] }}</span>
                                    <span class="info-item">{{ $lang['Waybill'] }}：
@if($goods['out_invoice_no'] != "")
{{ $goods['out_invoice_no'] }}
@else
&nbsp;
@endif
</span>
                                    <span class="info-item">{{ $lang['Logistics_company'] }}：
@if($goods['out_invoice_no'] != "")
{{ $goods['out_shipp_shipping'] }}
@else
&nbsp;
@endif
</span>
                                </div>
                                <div class="t-right" ectype="track-packages-btn">
                                    <a href="javascript:void(0)" class="sc-btn"><i class="iconfont icon-truck"></i>{{ $lang['track'] }}</a>
                                    <div class="comment-box" ectype="track-packages-info">
                                        <i class="box-i"></i>
                                        <div class="conmment-box-content">
                                            <div class="cont" id="seller_deliveryInfo">

                                            </div>
                                        </div>
                                    </div>
                                    <span id="invoice_no_{{ $goods['order_id'] }}" style="display:none">{{ $goods['out_invoice_no'] }}</span>
                                    <span id="shipping_name_{{ $goods['order_id'] }}" style="display:none">{{ $goods['out_shipp_shipping'] }}</span><span id="shipping_name_{{ $goods['order_id'] }}" style="display:none">{{ $goods['out_shipp_shipping'] }}</span>
                                    <span id="shipping_code_{{ $goods['order_id'] }}" style="display:none">{{ $goods['out_shipp_code'] }}</span>
                                </div>
                            </dt>
                        </dl>
                    </div>
                </div>

@if($goods['out_invoice_no'])

                <script language="javascript">
                    $("#seller_deliveryInfo").html("<center>"+json_languages.logistics_tracking_in+"</center>");
                    if(document.getElementById("shipping_name_{{ $goods['order_id'] }}"))
                    {
                        var expressid = document.getElementById("shipping_name_{{ $goods['order_id'] }}").innerHTML;
                        var expressno = document.getElementById("invoice_no_{{ $goods['order_id'] }}").innerHTML;
                        var expresscode = document.getElementById("shipping_code_{{ $goods['order_id'] }}").innerHTML;
                    }
                    $.ajax({
                        url: "ajax_user.php?act=query_express",
                        type: "post",
                        data:'com=' + expresscode + '&nu=' + expressno,
                        success: function(data,textStatus){
                            $("#seller_deliveryInfo").html(data);
                        },
                         error: function(o){
                        }
                    });
                </script>

@endif


@endif


@if($return_info)

                <div class="user-info-list">
                    <div class="info-title"><h2>{{ $lang['seller_custome_info'] }}</h2></div>
                    <div class="info-content">
                        <div class="info-item">
                            <div class="item-label">{{ $lang['contact_username'] }}：</div>
                            <div class="item-value">{{ $return_info['linkMan'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['contact_phone'] }}：</div>
                            <div class="item-value">{{ $return_info['mobile'] }}</div>
                        </div>
                        <div class="info-item">
                            <div class="item-label">{{ $lang['Contact_address'] }}：</div>
                            <div class="item-value">{{ $return_info['province'] }}{{ $return_info['city'] }}{{ $return_info['area'] }}{{ $return_info['address'] }}</div>
                        </div>
                    </div>
                </div>

@endif


@if($goods['agree_apply'] && $goods['return_type'] != 3)


                <div class="user-info-list">
                    <div class="info-title">
                        <h2>{{ $lang['Express_info'] }}</h2>
                    </div>

@if($goods['back_shipp_shipping'])

                    <div class="express_list">
                        <div class="ex_tit">{{ $lang['User_sent'] }}</div>
                        <div class="ex_con">
                            <div class="ex_item">
                                <span>{{ $lang['Express_company'] }}：</span>
                                <span class="blue">{{ $goods['back_shipp_shipping'] }}</span>
                            </div>
                            <div class="ex_item">
                                <span>{{ $lang['courier_sz'] }}：</span>
                                <span class="blue">{{ $goods['back_invoice_no'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="blank"></div>

@else

                    <div class="express_list">
                        <div class="ex_tit lh26">{{ $lang['Express_info_two'] }}</div>
                        <div class="ex_con">
                            <div class="ex_item">
                                <span>{{ $lang['Express_company'] }}：</span>
                                <select name="express_name" onchange="select_express(this)" class="select">
                                    <option value="0">{{ $lang['select_Express_company'] }}</option>

@foreach($shipping_list as $shipping)

                                    <option value="{{ $shipping['shipping_id'] }}">{{ $shipping['shipping_name'] }}</option>

@endforeach

                                    <option value="999">{{ $lang['Other'] }}</option>
                                </select>
                                <input type="text" name="other_express" id="other_express" class="text ml10"  style="display:none"/>
                            </div>
                            <div class="ex_item">
                                <span>{{ $lang['courier_sz'] }}：</span>
                                <input type="text" name="express_sn" class="text text-2"/>
                            </div>
                            <input type="submit" class="btn sc-redBg-btn btn30 ml75" value="{{ $lang['button_submit'] }}"/>
                            <input type="hidden" name="act" value="edit_express">
                            <input type="hidden" name="order_id" value="{{ $goods['order_id'] }}" />
                            <input type="hidden" name="ret_id" value="{{ $goods['ret_id'] }}" />
                        </div>
                    </div>

@endif


@if($goods['out_shipp_shipping'])

                    <div class="express_list">
                        <div class="ex_tit">{{ $lang['seller_shipping'] }}</div>
                        <div class="ex_con">
                            <div class="ex_item">
                                <span>{{ $lang['Express_company'] }}：</span>
                                <span class="blue">{{ $goods['out_shipp_shipping'] }}</span>
                            </div>
                            <div class="ex_item">
                                <span>{{ $lang['courier_sz'] }}：</span>
                                <span class="blue">{{ $goods['out_invoice_no'] }}</span>
                            </div>
                        </div>
                    </div>

@endif

                </div>

@endif


@if($goods['img_list'])

                <div class="user-info-list">
                    <div class="info-title"><h2>{{ $lang['pic_voucher'] }}</h2></div>
                    <div class="info-content">
                        <div class="info-item">

@foreach($goods['img_list'] as $img)

                            <a href="{{ $img['img_file'] }}" class="" target="_blank"><img src="{{ $img['img_file'] }}" width="100" height="100" style="border:1px #ccc solid; padding:2px;" /></a>

@endforeach

                        </div>
                    </div>
                </div>

@endif

                <div class="user-table-list">
                    <div class="pt10 pb10">
                    <a href="{{ $goods['shop_url'] }}" class="user-shop-link">{{ $goods['shop_name'] }}</a>
                        <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $goods['ru_id'] }}"  class="iconfont icon-kefu user-shop-kefu"></a>
                    </div>
                    <table class="user-table user-table-detail-goods">
                        <colgroup>
                            <col width="320">
                            <col width="110">
                            <col width="120">
                            <col width="95">
                            <col width="90">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>{{ $lang['goods'] }}</th>
                                <th class="tl">{{ $lang['goods_attr'] }}</th>
                                <th class="tl">{{ $lang['unit_price_user'] }}</th>
                                <th class="tl" width="10%">{{ $lang['number_to'] }}</th>
                                <th width="18%">{{ $lang['ws_subtotal'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <a href="{{ $goods['url'] }}" class="img"><img src="{{ $goods['goods_thumb'] }}" alt=""></a>
                                    <a href="{{ $goods['url'] }}" class="name">{{ $goods['goods_name'] }}</a>
                                </td>
                                <td>{!! nl2br($goods['goods_attr']) !!}</td>
                                <td>{{ $goods['goods_price'] }}</td>
                                <td>x{{ $goods['return_number'] }}</td>
                                <td>
                                    <p>{{ $goods['should_return'] }}</p>


									@if($goods['refound_status1'] != 1)


										@if($goods['goods_coupons'] > 0)

										<p class="red">- {{ $goods['formated_goods_coupons'] }}[{{ $lang['coupons'] }}]</p>

										@endif


										@if($goods['goods_bonus'] > 0)

										<p class="red">- {{ $goods['formated_goods_bonus'] }}[{{ $lang['bonus'] }}]</p>

										@endif


										@if($goods['goods_favourable'] > 0)

										<p class="red">- {{ $goods['formated_goods_favourable'] }}[{{ $lang['discount'] }}]</p>

										@endif

										@if($goods['value_card_discount'] > 0)

										<p class="red">- {{ $goods['formated_value_card_discount'] }}[{{ $lang['value_card_discount'] }}]</p>

										@endif


									@endif

                                </td>
                            </tr>
                        </tbody>
                    </table>

@if($goods['return_type'] == 1 || $goods['return_type'] == 3)


@if($goods['refound_status1'] == 1)


                        <div class="user-order-detail-total">
                            <dl class="total-row" style=" margin-bottom:0px;">
                                <dt class="total-label">{{ $lang['amount_return'] }}：</dt>
                                <dd class="total-value" style="font-size:14px; font-weight:normal; color:#999">{{ $goods['formated_should_return'] }}</dd>
                            </dl>
                        </div>

@if($goods['return_shipping_fee'] > 0)

                        <div class="user-order-detail-total"
@if($goods['return_shipping_fee'] > 0)
style=" margin-top:0px;"
@endif
>
                            <dl class="total-row" style="
@if($goods['return_shipping_fee'] > 0)
margin-top:0px;
@endif
 margin-bottom:0px;">
                                <dt class="total-label">{{ $lang['return_shipping'] }}：</dt>
                                <dd class="total-value" style="font-size:14px; font-weight:normal; color:#999"> + {{ $goods['formated_return_shipping_fee'] }}</dd>
                            </dl>
                        </div>

@endif


@if($goods['goods_coupons'] > 0)

                        <div class="user-order-detail-total" style=" margin-top:0px;">
                            <dl class="total-row" style="margin-top:0px; margin-bottom:0px;">
                                <dt class="total-label">{{ $lang['goods_coupons'] }}：</dt>
                                <dd class="total-value" style="font-size:14px; font-weight:normal; color:#999"> - {{ $goods['formated_goods_coupons'] }}</dd>
                            </dl>
                        </div>

@endif


@if($goods['goods_bonus'] > 0)

                        <div class="user-order-detail-total" style=" margin-top:0px;">
                            <dl class="total-row" style="margin-top:0px; margin-bottom:0px;">
                                <dt class="total-label">{{ $lang['goods_bonus'] }}：</dt>
                                <dd class="total-value" style="font-size:14px; font-weight:normal; color:#999"> - {{ $goods['formated_goods_bonus'] }}</dd>
                            </dl>
                        </div>

@endif


@if($goods['goods_favourable'] > 0)

                        <div class="user-order-detail-total" style=" margin-top:0px;">
                            <dl class="total-row" style="margin-top:0px; margin-bottom:0px;">
                                <dt class="total-label">{{ $lang['goods_favourable'] }}：</dt>
                                <dd class="total-value" style="font-size:14px; font-weight:normal; color:#999"> - {{ $goods['formated_goods_favourable'] }}</dd>
                            </dl>
                        </div>

@endif

                        <div class="user-order-detail-total" style=" margin-top:0px;">
                            <dl class="total-row" style="margin-top:0px;">
                                <dt class="total-label">{{ $lang['return_total'] }}：</dt>
                                <dd class="total-value" style="font-size:18px; font-weight:normal">{{ $goods['formated_return_amount'] }}</dd>
                            </dl>
                        </div>

@else

                        <div class="user-order-detail-total">
                            <dl class="total-row">
                                <dt class="total-label">{{ $lang['amount_return'] }}：</dt>
                                <dd class="total-value">{{ $goods['formated_should_return'] }}</dd>
                            </dl>
                        </div>

@endif


@endif

                </div>
            </div>
        @csrf </form>
        <script>
            function select_express(obj){
                var val = obj.value;
                if( val == '999'){
                    $$('other_express').style.display='inline';
                }else{
                    $$('other_express').style.display='none';
                }

            }
            /*编辑快递*/
            function shipping_sub( frm ){
                var shipping = frm['express_name'].value;
                if( shipping <= 0 ){
                    alert(json_languages.select_Express_company);
                    return false;
                }
                if( shipping =='999'){
                    if( frm['other_express'].value==''){
                        alert(json_languages.Express_companyname_null);
                        return false;
                    }
                }
                var express_sn = frm['express_sn'].value;
                if( express_sn ==''){
                    alert(json_languages.courier_sz_nul);
                    return false;
                }
            }
        </script>

@endif





@if($action == 'address_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_address'] }}</h2>
                <h3>{{ $lang['add_consignee_one'] }}<span class="red count_consignee">{{ $count_consignee }}</span>{{ $lang['add_consignee_two'] }}<span class="red">{{ $address_count }}</span>个</h3>
            </div>
            <ul class="cosignee-list">

@if($count_consignee < $address_count + 1)

                <li>
                    <div class="consignee-inner">
                        <a href="user_address.php?act=address" class="consignee-add"  data-dialog="dialog" data-divid="newAddress" data-id="" data-name="{{ $lang['consignee_new'] }}">
                            <i class="iconfont icon-add-quer"></i>
                            <p>{{ $lang['consignee_new'] }}</p>
                        </a>
                    </div>
                </li>

@endif


@if($new_consignee_list)


@foreach($new_consignee_list as $sn => $consignee)

                <li class="consignee_list_{{ $consignee['address_id'] }}">
                    <div class="consignee-inner">
                        <div class="head">
                            <div class="handle">

@if($consignee['address_id'] == $address && $address > 0)

@else
<a href="javascript:makeAddressAllDefault({{ $consignee['address_id'] }});">{{ $lang['default_consignee_to'] }}</a>
@endif

                                <a href="user_address.php?act=address&aid={{ $consignee['address_id'] }}">{{ $lang['modify'] }}</a>


@if($consignee['address_id'] == $address && $address > 0)

@else
<a href="javascript:alertDelAddressDiag({{ $consignee['address_id'] }});">{{ $lang['drop'] }}</a>
@endif

                            </div>
                            <div class="title">
                                <span class="name">{{ $consignee['consignee'] }}</span>
                                <span class="province">

@if($consignee['district_name'])

                                    {{ $consignee['district_name'] }}

@if($consignee['street_name'])

                                    &nbsp;{{ $consignee['street_name'] }}

@endif


@else

                                    {{ $consignee['city_name'] }}

@endif

                                </span>

@if($consignee['address_id'] == $address && $address > 0)
<span class="tag">{{ $lang['default_address'] }}</span>
@endif

                            </div>
                        </div>
                        <div class="body">
                            <p>{{ $consignee['mobile'] }}</p>
                            <p>{{ $consignee['region'] }}</p>
                        </div>
                    </div>
                </li>

@endforeach


@endif

            </ul>
        </div>

@endif





@if($action == 'address')

        <div class="user-mod">
            <div class="user-title">
                <h2>
@if($address_id == 0)
{{ $lang['Newly'] }}
@else
{{ $lang['edit'] }}
@endif
{{ $lang['consignee_info'] }}</h2>
            </div>
            <div class="user-form">
                <form action="user_address.php" method="post" class="user-address-form" name="theForm">
                    <div class="form-row">
                        <div class="form-label"><span class="red">*</span>{{ $lang['consignee'] }}：</div>
                        <div class="form-value"><input type="text" name="consignee" value="{{ $consignee['consignee'] }}" class="form-input"><div class="form_prompt"></div></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><span class="red">*</span>{{ $lang['label_mobile'] }}：</div>
                        <div class="form-value">
                            <input type="text" name="mobile" value="{{ $consignee['mobile'] }}" class="form-input">
                            <span class="fl">{{ $lang['label_tel'] }}：</span>
                            <input type="text" name="tel" value="{{ $consignee['tel'] }}" class="form-input">
                            <div class="form_prompt"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-label form-label-lh"><span class="red">*</span>{{ $lang['Local_area'] }}：</div>
                        <div class="form-value" ectype="regionLinkage">
                            <dl class="mod-select mod-select-small fl" ectype="smartdropdown">
                                <dt>
                                    <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
                                    <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                                </dt>
                                <dd ectype="layer">

@foreach($country_list as $country)

                                    <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                                </dd>
                            </dl>
                            <dl class="mod-select mod-select-small fl" ectype="smartdropdown">
                                <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span><input type="hidden" value="{{ $consignee['province'] }}" ectype="ragionItem" name="province"></dt>
                                <dd ectype="layer">
                                    <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                                    <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                </dd>
                            </dl>
                            <dl class="mod-select mod-select-small fl" ectype="smartdropdown">
                                <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span><input type="hidden" value="{{ $consignee['city'] }}" name="city" ></dt>
                                <dd ectype="layer">
                                    <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                                    <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                </dd>
                            </dl>
                            <dl class="mod-select mod-select-small fl" ectype="smartdropdown" style="display:none">
                                <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['district'] }}" name="district"></dt>
                                <dd ectype="layer">
                                    <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

                                    <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                                </dd>
                            </dl>
                            <dl class="mod-select mod-select-small fl" ectype="smartdropdown" style="display:none">
                                <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['street'] }}" name="street" class="ignore"></dt>
                                <dd ectype="layer">
                                    <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                                    <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                                </dd>
                            </dl>
                            <div class="form_prompt"></div>
                        </div>
                        <span id="consigneeEreaNote" class="status error hide"></span>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><span class="red">*</span>{{ $lang['detailed_address'] }}：</div>
                        <div class="form-value"><input type="text" name="address" value="{{ $consignee['address'] }}" class="form-input form-input-long"><div class="form_prompt"></div></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><span class="red">&nbsp;&nbsp;</span>{{ $lang['email_reset'] }}：</div>
                        <div class="form-value"><input type="text" name="email" value="{{ $consignee['email'] }}" class="form-input"><div class="form_prompt"></div></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><span class="red">&nbsp;&nbsp;</span>{{ $lang['postalcode'] }}：</div>
                        <div class="form-value"><input type="text" name="zipcode" value="{{ $consignee['zipcode'] }}" class="form-input"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><span class="red">&nbsp;&nbsp;</span>{{ $lang['sign_building'] }}：</div>
                        <div class="form-value"><input type="text"  name="sign_building" value="{{ $consignee['sign_building'] }}" class="form-input"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><span class="red">&nbsp;&nbsp;</span>{{ $lang['default_address'] }}：</div>
                        <div class="form-value">
                            <div class="fl">
                                <input type="checkbox"
@if($default_address > 0 && $consignee['address_id'] == $default_address)
checked
@endif
 name="default" id="default_address" value="1" class="ui-checkbox">
                                <label class="ui-label" for="default_address">{{ $lang['yes'] }}</label>
                            </div>

                        </div>
                    </div>
                    <div class="form-btn-wp">
                        <input type="button" name="button" class="form-btn" value="{{ $lang['submit_address'] }}" id="submitAddress"/>
                        <input type="reset" name="reset" class="form-btn form-btn-gray" value="{{ $lang['reset'] }}">
                        <input type="hidden" name="address_id" value="{{ $consignee['address_id'] }}" />
                        <input type="hidden" name="act" value="act_edit_address" />
                        <input type="hidden" name="count_consignee" value="{{ $count_consignee }}" />
                    </div>
                @csrf </form>
            </div>
        </div>

@endif





@if($action == 'return_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_return'] }}</h2>
                <h3>{{ $lang['youhave'] }}<span class="red">{{ $pager['record_count'] }}</span>{{ $lang['return_goods_user'] }}</h3>
            </div>
            <div id="return_list">
                @include('frontend::library/user_return_order_list')
            </div>

@if($orders)

				@include('frontend::library/pages')

@endif

        </div>

@endif





@if($action == 'goods_order' )

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['return_policy_apply'] }}</h2>
            </div>
			<form method="post" action="user_order.php" name="theFrom" id="theFrom">
				<div class="uaer-return-app clearfix">

@if($package_buy)

					<div class="no_records">
						<i class="no_icon_two"></i>
						<div class="no_info"><h3>{{ $lang['package_buy_notic'] }}</h3></div>
					</div>

@else


@forelse($goods_list as $goods)

					<div class="tl-item">
						<div class="item-t">
							<div class="t-goods">
								<div class="t-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

										<a href="{{ $goods['url'] }}" target="_blank" class="f6"><img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"></a>

@if($goods['parent_id'] > 0)

										<span style="color:#FF0000">（{{ $lang['accessories'] }}）</span>

@elseif ($goods['is_gift'])

										<span style="color:#FF0000">（{{ $lang['largess'] }}）</span>

@endif


@elseif ($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

										<a href="javascript:void(0)" onclick="setSuitShow({{ $goods['goods_id'] }})" class="f6"><img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"><span style="color:#FF0000;">{{ $lang['remark_package'] }}</span></a>
										<div id="suit_{{ $goods['goods_id'] }}" style="display:none">

@foreach($goods['package_goods_list'] as $package_goods_list)

											<a href="{{ $package_goods_list['url'] }}" target="_blank" class="f6"><img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"></a><br />

@endforeach

										</div>

@endif

								</div>
								<div class="t-info">
									<div class="info-name"><a href="{{ $goods['url'] }}" class="ftx-03" target="_blank">{{ $goods['goods_name'] }}</a></div>
									<div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
									<div class="info-attr">
                                    @if($goods['goods_attr'])
                                    {!! $goods['goods_attr'] !!}
                                    @else
                                    {{ $lang['nothing'] }}
                                    @endif
                                    </div>
								</div>
							</div>
							<div class="t-statu">
                            {{--支持退换货且未退款--}}
                            @if(isset($goods['goods_handler_return']) && $goods['goods_handler_return'] == 1 && $goods['is_refound'] == 0)
                                 <a href="user_order.php?act=apply_return&rec_id={{ $goods['rec_id'] }}&order_id={{ $order_id }}" class="sc-btn">{{ $lang['applied'] }}</a>
                            @endif

                            @if(isset($goods['is_refound']) && $goods['is_refound'] == 1)
                            {{--已退款查看详情--}}
                            <span class="ftx-01 fs14">
                                <a href="user_order.php?act=return_detail&ret_id={{ $goods['ret_id'] }}&order_id={{ $goods['order_id'] }}" class="sc-btn">{{ $lang['Have_applied'] }}</a>
                                </span>
                            @endif
							</div>
						</div>
						<div class="item-f">
                            {{--支持退换货且未退款--}}
                            @if(isset($goods['goods_handler_return']) && $goods['goods_handler_return'] == 1 && $goods['is_refound'] == 0)
							<div class="f-left">
								<div class="checkbox">
									<input type="checkbox" name="checkboxes[]" value="{{ $goods['rec_id'] }}" class="ui-checkbox" id="checkbox_{{ $goods['rec_id'] }}" />
									<label for="checkbox_{{ $goods['rec_id'] }}" class="ui-label"></label>
								</div>
							</div>
                            @endif

							<div class="f-right">
                            	<span class="red mr50">
                                @if($goods['goods_cause'])
                                {{ $lang['only_support'] }}：
                                @foreach($goods['goods_cause'] as $cause)

                                @if(!$loop->last)
                                {{ $cause['lang'] }}、
                                @else
                                {{ $cause['lang'] }}
                                @endif

                                @endforeach

                                @else
                                {{ $lang['no_support_return'] }}
                                @endif
                                </span>
                            	<span>{{ $lang['shopping_money'] }}
                                @if($order['extension_code'] == "group_buy")
                                {{ $lang['gb_deposit'] }}
                                @endif
                                ：{{ $goods['subtotal'] }}</span>
                            </div>
						</div>
					</div>

@empty

					<div class="no_records">
						<i class="no_icon_two"></i>
						<div class="no_info"><h3>{{ $lang['order_not_notic'] }}</h3></div>
					</div>

@endforelse


@if($goods['goods_cause'])

                    <div class="tl-tfoor">
                        <div class="checkbox">
                            <input type="checkbox" name="all_list" class="ui-checkbox" id="checkbox_stars" />
                            <label for="checkbox_stars" class="ui-label">{{ $lang['check_all'] }}</label>
                        </div>
                        <a href="javascript:;" class="sc-btn" ectype="submitBtn" >{{ $lang['batch_applied'] }}</a>
                        <input type="hidden" name="act" value="batch_applied">
                        <input type="hidden" name="order_id" value="{{ $order_id }}">
                    </div>

@endif


@endif

				</div>
			@csrf </form>
        </div>

@endif





@if($action == 'service_detail')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['service_note'] }}</h2>
                <a href="javascript:history.go(-1);" class="more">{{ $lang['back'] }}</a>
            </div>
            <table class="user-table user-table-detail-goods">
                <colgroup>
                    <col width="170">
                    <col width="350">
                    <col width="110">
                    <col width="110">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th class="tl">{{ $lang['Return_type'] }}</th>
                        <th class="tl">{{ $lang['Return_reason'] }}</th>
                        <th>{{ $lang['Return_one'] }}</th>
                        <th>{{ $lang['Return_two'] }}</th>
                        <th>{{ $lang['Return_three'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $lang['Performance_fault'] }}</td>
                        <td>{{ $lang['Performance_fault_one'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $lang['Missing_parts'] }}</td>
                        <td>{{ $lang['Missing_parts_one'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['no'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $lang['Logistics_loss'] }}</td>
                        <td>{{ $lang['Logistics_loss_one'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['no'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $lang['Inconsistent_goods'] }}</td>
                        <td>{{ $lang['Inconsistent_goods_one'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['yes'] }}</td>
                        <td class="tc">{{ $lang['no'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $lang['Buy_wrong'] }}</td>
                        <td>{{ $lang['Buy_wrong_one'] }}</td>
                        <td class="tc">{{ $lang['Buy_wrong_two'] }}</td>
                        <td class="tc">{{ $lang['no'] }}</td>
                        <td class="tc">{{ $lang['no'] }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="user-prompt mt20">
                <div class="info">
                    {!! $lang['Return_Explain'] !!}
                </div>
            </div>
        </div>

@endif





@if($action == 'apply_return')

        <div class="user-mod user_apply_return">
            <div class="user-title">
                <h2>{{ $lang['return_list'] }}</h2>
                <a href="user_order.php?act=service_detail" class="more">{{ $lang['service_note'] }}</a>
            </div>
            <div class="uaer-return-app clearfix">
                <div class="tl-item">
                    <div class="item-t b-b-0">
                        <div class="t-goods">
                            <div class="t-img">

@if($goods_return['goods_id'] > 0 && $goods_return['extension_code'] != 'package_buy')

                                    <a href="{{ $goods_return['url'] }}" target="_blank" class="f6">
                                        <img width="55" height="55" src="{{ $goods_return['goods_thumb'] }}" title="{{ $goods_return['goods_name'] }}">
                                    </a>

@if($goods_return['parent_id'] > 0)

                                    <span class="red">（{{ $lang['accessories'] }}）</span>

@elseif ($goods_return['is_gift'])

                                    <span class="red">（{{ $lang['largess'] }}）</span>

@endif


@elseif ($goods_return['goods_id'] > 0 && $goods_return['extension_code'] == 'package_buy')

                                    <a href="javascript:void(0)" onclick="setSuitShow({{ $goods_return['goods_id'] }})" class="f6">
                                        <img width="55" height="55" src="{{ $goods_return['goods_thumb'] }}" title="{{ $goods_return['goods_name'] }}"><span style="color:#FF0000;">{{ $lang['remark_package'] }}</span>
                                    </a>
                                    <div id="suit_{{ $goods_return['goods_id'] }}" style="display:none">

@foreach($goods_return['package_goods_list'] as $package_goods_list)

                                            <a href="{{ $package_goods_list['url'] }}" target="_blank" class="f6">
                                                <img width="55" height="55" src="{{ $package_goods_list['goods_thumb'] }}" title="{{ $package_goods_list['goods_name'] }}">
                                            </a>

@endforeach

                                    </div>

@endif

                            </div>
                            <div class="t-info">
                                <div class="info-name"><a href="goods.php?id={{ $goods_return['goods_id'] }}" class="ftx-03" target="_blank">{{ $goods_return['goods_name'] }}</a></div>
                                <div class="info-price"><b>{{ $goods_return['goods_price'] }}</b><i>×</i><span>{{ $goods_return['goods_number'] }}</span></div>
                                <div class="info-attr">
@if($goods_return['goods_attr'])
{!! $goods_return['goods_attr'] !!}
@else
{{ $lang['nothing'] }}
@endif
</div>
                            </div>
                        </div>
                        <div class="t-statu">

							@if($goods_return['goods_bonus'] > 0 || $goods_return['goods_coupons'] > 0 || $goods_return['goods_favourable'] > 0 || $goods_return['value_card_discount'] > 0)

							<p class="fs14">{{ $goods_return['subtotal'] }}</p>


							@if($goods_return['goods_coupons'] > 0)

                            <p class="fs14 red">- {{ $goods_return['format_goods_coupons'] }}[{{ $lang['coupons'] }}]</p>

							@endif



							@if($goods_return['goods_bonus'] > 0)

                            <p class="fs14 red">- {{ $goods_return['format_goods_bonus'] }}[{{ $lang['bonus'] }}]</p>

							@endif



							@if($goods_return['goods_favourable'] > 0)

                            <p class="fs14 red">- {{ $goods_return['format_goods_favourable'] }}[{{ $lang['discount'] }}]</p>

							@endif

							@if($goods_return['value_card_discount'] > 0)

                            <p class="fs14 red">- {{ $goods_return['format_value_card_discount'] }}[{{ $lang['value_card_discount'] }}]</p>

							@endif


                            <p class="fs14 red">= {{ $goods_return['format_actual_return'] }}</p>

						@else

							{{ $goods_return['subtotal'] }}

						@endif

						</div>
                    </div>
                </div>
            </div>
            <div class="applyReturnForm">
                <form id="formReturn" name="formReturn" method="post" action="user_order.php" onsubmit="return check_sub()">
                    <div class="return_ts">
                        <em class="fl">* {{ $lang['reminder'] }}：</em>
                        <div class="fl">{{ $lang['reminder_one'] }}&nbsp;<em>{{ $goods_return['user_name'] }}</em>&nbsp;{{ $lang['reminder_two'] }}</div>
                    </div>
                    <div class="form">

@if($goods_cause)

                        <div class="item item1 first">
                            <div class="label"><em>*</em>{{ $lang['Service_type'] }}：</div>
                            <div class="value value-checkbox">


@foreach($goods_cause as $goods)

                                	<div class="value-item
@if($goods['is_checked'] == 1)
selected
@endif
">
                                        <input type="radio" id="service-{{ $goods['cause'] }}" name="return_type" value="{{ $goods['cause'] }}" class="ui-radio" autocomplete="off"
@if($goods['is_checked'] == 1)
checked
@endif
 />
                                        <label for="service-{{ $goods['cause'] }}" class="ui-radio-label">{{ $goods['lang'] }}</label>
                                    </div>

@endforeach


                                <input name="return_rec_id" value="{{ $goods_return['rec_id'] }}" type="hidden" />
                                <input name="return_g_id" value="{{ $goods_return['goods_id'] }}" type="hidden" />
                                <input name="return_g_number" value="{{ $goods_return['goods_number'] }}" type="hidden" />
                            </div>
                            <div class="lists" ectype="return_lists">
                                <div class="return_div" ectype="return_div">
                                    <div class="type_box1" name="type_maintain" id="maintain_{{ $goods_return['rec_id'] }}">
                                        <div class="type_item">{{ $lang['Repair_number'] }}：</div>
                                        <div class="type_con">
                                            <a onclick="buyNumber.minus(this, 1)" href="javascript:;" id="decrease" class="plus_minus"><i class="iconfont icon-reduce"></i></a>
                                            <input type="text" class="return_num" defaultnumber="{{ $goods_return['goods_number'] }}" value="{{ $goods_return['goods_number'] }}" id="maintain_{{ $goods_return['rec_id'] }}" name="maintain_number" onblur="check_return_num(this,{{ $goods_return['goods_number'] }} ,{{ $goods_return['rec_id'] }}, 1)"/>
                                            <a onclick="buyNumber.plus(this, 1)" href="javascript:;" id="increase" class="plus_minus"><i class="iconfont icon-plus"></i></a>
                                            <div class="return_sm">({{ $lang['Repair_one'] }}<span>{{ $goods_return['goods_number'] }}</span>{{ $lang['Repair_two'] }}<span id="maintain_span_{{ $goods_return['rec_id'] }}" name="maintain">{{ $goods_return['goods_number'] }}</span>{{ $lang['jian'] }})</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="return_div" ectype="return_div" style="display:none;">
                                    <div class="type_box1" name="type_box1" id="returnid_{{ $goods_return['rec_id'] }}">
                                        <div class="type_item">{{ $lang['return_number'] }}：</div>
                                        <div class="type_con">
                                            <a onclick="buyNumber.minus(this, 1)" href="javascript:;" id="decrease" class="plus_minus"><i class="iconfont icon-reduce"></i></a>
                                            <input type="text" class="return_num" defaultnumber="{{ $goods_return['goods_number'] }}" value="{{ $goods_return['goods_number'] }}" id="returnid_{{ $goods_return['rec_id'] }}" name="return_num" onblur="check_return_num(this,{{ $goods_return['goods_number'] }} ,{{ $goods_return['rec_id'] }}, 2)" />
                                            <a onclick="buyNumber.plus(this, 1)" href="javascript:;" id="increase" class="plus_minus"><i class="iconfont icon-plus"></i></a>
                                            <div class="return_sm">({{ $lang['return_one'] }}<span>{{ $goods_return['goods_number'] }}</span>{{ $lang['return_two'] }}<span id="retired_{{ $goods_return['rec_id'] }}" name="retired">{{ $goods_return['goods_number'] }}</span>{{ $lang['jian'] }})</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="return_div" ectype="return_div" style="display:none;">
                                    <div class="type_box1" name="type_box1" id="changeid_{{ $goods_return['rec_id'] }}">
                                        <div class="type_item">{{ $lang['return_one'] }}：</div>
                                        <div class="type_con">
                                            <a onclick="buyNumber.minus(this, 1)" href="javascript:;" id="decrease" class="plus_minus"><i class="iconfont icon-reduce"></i></a>
                                            <input type="text" class="return_num" defaultnumber="{{ $goods_return['goods_number'] }}" value="{{ $goods_return['goods_number'] }}" id="returnid_{{ $goods_return['rec_id'] }}" name="back_return_num" onblur="check_return_num(this,{{ $goods_return['goods_number'] }} ,{{ $goods_return['rec_id'] }}, 2)" />
                                            <a onclick="buyNumber.plus(this, 1)" href="javascript:;" id="increase" class="plus_minus"><i class="iconfont icon-plus"></i></a>
                                            <div class="return_sm">({{ $lang['return_one'] }}<span>{{ $goods_return['goods_number'] }}</span>{{ $lang['return_two'] }}<span id="retired_{{ $goods_return['rec_id'] }}" name="retired">{{ $goods_return['goods_number'] }}</span>{{ $lang['jian'] }})</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item item1">
                            <div class="label">{{ $lang['Application_credentials'] }}：</div>
                            <div class="value">
                                 <input name="credentials" type="checkbox" value="" class="ui-checkbox" id="credentials"/>
                                 <label for="credentials" class="ui-label">{{ $lang['has_Test_Report'] }}</label>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em>*</em>{{ $lang['return_reason'] }}：</div>
                            <div class="value">
                                <span class="cause_select">
                                <select name="parent_id" id="cause_{{ $goods_return['rec_id'] }}" onchange="selectCause(this.value ,{{ $goods_return['rec_id'] }})">
                                <option value="0">{{ $lang['please_select'] }}</option>
                                {!! $cause_list !!}
                                </select>
                                </span>
                                <span id="children_{{ $goods_return['rec_id'] }}" class="cause_select"></span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em>*</em>{{ $lang['problem_desc'] }}：</div>
                            <div class="value"><textarea cols="40" class="text_desc" rows="4" name="return_brief"></textarea></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['pic_info'] }}：</div>
                            <div class="value">
                                <div class="upload_img">
                                    <div class="SWFUpload"><input type="button" id="uploadbutton" class="button" value="" data-upload_type="goods"/></div>
									{!! __('user.pic_info_one', ['pic_count' => config('shop.return_pictures')]) !!}
                                    <div class="up_img return_images"
@if(!$img_list)
 style="display:none;"
@endif
>
                                        <div class="mscoll">
                                            <a id="scollUp" class="mleft prev"><i class="iconfont icon-left"></i></a>
                                            <div class="mslist">
                                                <ul class="img-list-li">

@foreach($img_list as $img)

                                                    <li>
                                                        <a href="{{ $img['img_file'] }}" target="_blank"><img class="err-product" width="60" height="60" src="{{ $img['img_file'] }}" /></a>
                                                    </li>

@endforeach

                                                </ul>
                                            </div>
                                            <a id="scollDown" class="mright next"><i class="iconfont icon-right"></i></a>
                                        </div>
                                        <a class="apply_goods_return clear_pictures" href="javascript:void(0);">{{ $lang['Empty_picture'] }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div ectype="return-type">
                            <div class="item">
                                <div class="label"><em>*</em>{{ $lang['label_address'] }}：</div>
                                <div class="value">
                                    <div class="address" ectype="regionLinkage">
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
                                                <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                                            </dt>
                                            <dd ectype="layer">

@foreach($country_list as $country)

                                                <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span><input type="hidden" value="{{ $consignee['province'] }}" ectype="ragionItem"name="province"></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                                                <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span><input type="hidden" value="{{ $consignee['city'] }}" name="city" ></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                                                <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['district'] }}" name="district"></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

                                                <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['street'] }}" name="street"></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                                                <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="address_xq">
                                        <input type="text" class="text_item"  name="return_address" value="{{ $consignee['address'] }}" size="42"/>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em>*</em>{{ $lang['Contact_name'] }}：</div>
                                <div class="value">
                                    <input type="text" class="text_item"  name="addressee" value="{{ $consignee['consignee'] }}" size="42"/>
                                    <input type="hidden" name="hid1" value="{{ $consignee['consignee'] }}"/>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em>*</em>{{ $lang['label_mobile'] }}：</div>
                                <div class="value">
                                    <input type="text" class="text_item"  name="mobile" value="{{ $consignee['mobile'] }}" size="42"/>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['email_user'] }}：</div>
                                <div class="value">
                                    <input type="text" class="text_item" name="code" value="{{ $consignee['zipcode'] }}" size="42"/>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['type']['0'] }}：</div>
                            <div class="value">
                                <textarea cols="40" class="text_area" rows="4" name="return_remark"></textarea>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                            	<input type="hidden" name="chargeoff_status" value="{{ $order['chargeoff_status'] }}" />
                                <input type="submit" value="{{ $lang['submit_goods'] }}" class="sc-btn btn30 sc-redBg-btn" />
                                <input type="hidden" name="act" value="submit_return" />
                                <input type="hidden" name="rec_id" value="{{ $goods_return['rec_id'] }}" />
                            </div>
                        </div>

@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info"><h3>{{ $lang['no_support_return'] }}</h3></div>
                        </div>

@endif

                    </div>
                @csrf </form>
            </div>
        </div>

@endif





@if($action == 'batch_applied')

        <div class="user-mod user_apply_return">
        <form id="formReturn" name="formReturn" method="post" action="user_order.php" onsubmit="return check_sub()">
            <div class="user-title">
                <h2>{{ $lang['return_list'] }}</h2>
                <a href="user_order.php?act=service_detail" class="more">{{ $lang['service_note'] }}</a>
            </div>
            <div class="uaer-return-app clearfix">

@foreach($goods_return as $goods)

                <div class="tl-item">
                    <div class="item-t b-b-0">
                        <div class="t-goods">
                            <div class="t-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                    <a href="{{ $goods['url'] }}" target="_blank" class="f6">
                                        <img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}">
                                    </a>

@if($goods['parent_id'] > 0)

                                    <span class="red">（{{ $lang['accessories'] }}）</span>

@elseif ($goods['is_gift'])

                                    <span class="red">（{{ $lang['largess'] }}）</span>

@endif


@elseif ($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                                    <a href="javascript:void(0)" onclick="setSuitShow({{ $goods['goods_id'] }})" class="f6">
                                        <img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"><span style="color:#FF0000;">{{ $lang['remark_package'] }}</span>
                                    </a>
                                    <div id="suit_{{ $goods['goods_id'] }}" style="display:none">

@foreach($goods['package_goods_list'] as $package_goods_list)

                                            <a href="{{ $package_goods_list['url'] }}" target="_blank" class="f6">
                                                <img width="55" height="55" src="{{ $package_goods_list['goods_thumb'] }}" title="{{ $package_goods_list['goods_name'] }}">
                                            </a>

@endforeach

                                    </div>

@endif

                            </div>
                            <div class="t-info">
                                <div class="info-name"><a href="goods.php?id={{ $goods['goods_id'] }}" class="ftx-03" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                <div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
                                <div class="info-attr">
@if($goods['goods_attr'])
{!! $goods['goods_attr'] !!}
@else
{{ $lang['nothing'] }}
@endif
</div>
                            </div>
                        </div>
                        <div class="t-statu">{{ $goods['subtotal'] }}</div>
						<input name="return_rec_id[{{ $goods['rec_id'] }}]" value="{{ $goods['rec_id'] }}" type="hidden" />
						<input name="return_g_id[{{ $goods['rec_id'] }}]" value="{{ $goods['goods_id'] }}" type="hidden" />
						<input name="return_g_number[{{ $goods['rec_id'] }}]" value="{{ $goods['goods_number'] }}" type="hidden" />
                    </div>
                </div>

@endforeach

            </div>
            <div class="applyReturnForm">
				<div class="return_ts">
					<em class="fl">* {{ $lang['reminder'] }}：</em>
					<div class="fl">{{ $lang['reminder_one'] }}&nbsp;<em>{{ $shop_name }}</em>&nbsp;{{ $lang['reminder_two'] }}</div>
				</div>
				<div class="form">

@if($goods_cause)

				<div class="item item1 first">
					<div class="label"><em>*</em>{{ $lang['Service_type'] }}：</div>
					<div class="value value-checkbox">

@foreach($goods_cause as $goods)

						<div class="value-item
@if($goods['is_checked'] == 1)
selected
@endif
">
							<input type="radio" id="service-{{ $goods['cause'] }}" name="return_type" value="{{ $goods['cause'] }}" class="ui-radio" autocomplete="off"
@if($goods['is_checked'] == 1)
checked
@endif
 />
							<label for="service-{{ $goods['cause'] }}" class="ui-radio-label">{{ $goods['lang'] }}</label>
						</div>

@endforeach

					</div>
				</div>
				<div class="item item1">
					<div class="label">{{ $lang['Application_credentials'] }}：</div>
					<div class="value">
						 <input name="credentials" type="checkbox" value="" class="ui-checkbox" id="credentials"/>
						 <label for="credentials" class="ui-label">{{ $lang['has_Test_Report'] }}</label>
					</div>
				</div>
                <div class="item">
                    <div class="label"><em>*</em>{{ $lang['return_reason'] }}：</div>
                    <div class="value">
                        <span class="cause_select">
                        <select name="parent_id" id="cause_{{ $goods_return['rec_id'] }}" onchange="selectCause(this.value ,{{ $goods_return['rec_id'] }})">
                            <option value="0">{{ $lang['please_select'] }}</option>
                            {!! $cause_list !!}
                        </select>
                        </span>
                        <span id="children_{{ $goods_return['rec_id'] }}" class="cause_select"></span>
                    </div>
                </div>
				<div class="item">
					<div class="label"><em>*</em>{{ $lang['problem_desc'] }}：</div>
					<div class="value"><textarea cols="40" class="text_desc" rows="4" name="return_brief"></textarea></div>
				</div>
				<div class="item">
					<div class="label">{{ $lang['pic_info'] }}：</div>
					<div class="value">
						<div class="upload_img">
							<div class="SWFUpload"><input type="button" id="uploadbutton" class="button" value="" data-upload_type="goods"/></div>
							{!! __('user.pic_info_one', ['pic_count' => config('shop.return_pictures')]) !!}
							<div class="up_img return_images"
@if(!$img_list)
 style="display:none;"
@endif
>
								<div class="mscoll">
									<a id="scollUp" class="mleft prev"><i class="iconfont icon-left"></i></a>
									<div class="mslist">
										<ul class="img-list-li">

@foreach($img_list as $img)

											<li>
												<a href="{{ $img['img_file'] }}" target="_blank"><img class="err-product" width="60" height="60" src="{{ $img['img_file'] }}" /></a>
											</li>

@endforeach

										</ul>
									</div>
									<a id="scollDown" class="mright next"><i class="iconfont icon-right"></i></a>
								</div>
								<a class="apply_goods_return clear_pictures" href="javascript:void(0);">{{ $lang['Empty_picture'] }}</a>
							</div>
						</div>
					</div>
				</div>
				<div ectype="return-type">
					<div class="item">
						<div class="label"><em>*</em>{{ $lang['label_address'] }}：</div>
						<div class="value">
							<div class="address" ectype="regionLinkage">
								<dl class="mod-select mod-select-small" ectype="smartdropdown">
									<dt>
										<span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
										<input type="hidden" value="{{ $consignee['country'] }}" name="country">
									</dt>
									<dd ectype="layer">

@foreach($country_list as $country)

										<div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown">
									<dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span><input type="hidden" value="{{ $consignee['province'] }}" ectype="ragionItem"name="province"></dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

										<div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown">
									<dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span><input type="hidden" value="{{ $consignee['city'] }}" name="city" ></dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

										<div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
									<dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['district'] }}" name="district"></dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

										<div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
									<dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['street'] }}" name="street"></dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

										<div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
							</div>
							<div class="address_xq">
								<input type="text" class="text_item"  name="return_address" value="{{ $consignee['address'] }}" size="42"/>
							</div>
						</div>
					</div>
					<div class="item">
						<div class="label"><em>*</em>{{ $lang['Contact_name'] }}：</div>
						<div class="value">
							<input type="text" class="text_item"  name="addressee" value="{{ $consignee['consignee'] }}" size="42"/>
							<input type="hidden" name="hid1" value="{{ $consignee['consignee'] }}"/>
						</div>
					</div>
					<div class="item">
						<div class="label"><em>*</em>{{ $lang['label_mobile'] }}：</div>
						<div class="value">
							<input type="text" class="text_item"  name="mobile" value="{{ $consignee['mobile'] }}" size="42"/>
						</div>
					</div>
					<div class="item">
						<div class="label">{{ $lang['email_user'] }}：</div>
						<div class="value">
							<input type="text" class="text_item" name="code" value="{{ $consignee['zipcode'] }}" size="42"/>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="label">{{ $lang['type']['0'] }}：</div>
					<div class="value">
						<textarea cols="40" class="text_area" rows="4" name="return_remark"></textarea>
					</div>
				</div>
				<div class="item">
					<div class="label">&nbsp;</div>
					<div class="value">
						<input type="hidden" name="chargeoff_status" value="{{ $order['chargeoff_status'] }}" />
						<input type="submit" value="{{ $lang['submit_goods'] }}" class="sc-btn btn30 sc-redBg-btn" />
						<input type="hidden" name="act" value="submit_batch_return" />
					</div>
				</div>

@else

				<div class="no_records">
					<i class="no_icon_two"></i>
					<div class="no_info"><h3>{{ $lang['no_support_return'] }}</h3></div>
				</div>

@endif

				</div>
			</div>
		@csrf </form>
        </div>

@endif






@if($action == 'auction_list')

		<div class="user-mod">
			<div class="user-title">
                <h2>{{ $lang['my_auction'] }}</h2>
                <ul class="tabs">
					<li
@if($action == 'auction_list')
class="active"
@endif
><a href="user_activity.php?act=auction_list">{{ $lang['auction_list'] }}</a></li>
                    <li
@if($action == 'auction')
class="active"
@endif
><a href="user_activity.php?act=auction">{{ $lang['auction_order'] }}</a></li>
                </ul>
            </div>
			<div class="user-title">
                <ul class="tabs">
                    <li class="active"><a href="">{{ $lang['auction_all'] }}({{ $all_auction }})</a></li>
                    <li class="user-count3" onclick="get_AuctionSearch('is_going', '{{ $action }}', 'is_going','','.user-count3')">
						<a href="javascript:void(0);">{{ $lang['auction_staues']['1'] }}({{ $is_going }})</a>
						<input name="is_going" id="is_going" type="hidden" value="1" />
                    </li>
                    <li class="user-count1" onclick="get_AuctionSearch('is_finished', '{{ $action }}', 'is_finished','','.user-count1')">
						<a href="javascript:void(0);">{{ $lang['auction_staues']['3'] }}({{ $is_finished }})</a>
						<input name="is_finished" id="is_finished" type="hidden" value="3" />
                    </li>
                </ul>
            </div>
			<div class="comment-list-warp clearfix">
                <div class="user-order-list" id="user-auction-list">
					@include('frontend::library/user_auction_list')
                </div>
            </div>
		</div>

@endif





@if($action == 'auction' || $action == 'auction_order_recycle')

        <div class="user-mod">
			<div class="user-title">
                <h2>{{ $lang['my_auction'] }}</h2>
                <ul class="tabs">
					<li
@if($action == 'auction_list')
class="active"
@endif
><a href="user_activity.php?act=auction_list">{{ $lang['auction_list'] }}</a></li>
                    <li
@if($action == 'auction')
class="active"
@endif
><a href="user_activity.php?act=auction">{{ $lang['auction_order'] }}</a></li>
                </ul>
            </div>
            <div class="user-title">
                <ul class="tabs">
                    <li class="active"><a href="">{{ $lang['all_order'] }}({{ $allorders }})</a></li>
                    <li class="user-count3" onclick="get_OrderSearch('to_unconfirmed', '{{ $action }}', 'toBe_unconfirmed','','.user-count3')">
                    <a href="javascript:void(0);">{{ $lang['cs']['0'] }}({{ $unconfirmed }})</a>
                    <input name="to_unconfirmed" id="to_unconfirmed" type="hidden" value="0" />
                    </li>
                    <li class="user-count1" onclick="get_OrderSearch('payId', '{{ $action }}', 'toBe_pay','','.user-count1')">
                    <a href="javascript:void(0);">{{ $lang['cs']['100'] }}({{ $pay_count }})</a>
                    <input name="toBe_pay" id="payId" type="hidden" value="100" />
                    </li>
                    <li class="user-count2" onclick="get_OrderSearch('to_confirm_order', '{{ $action }}', 'toBe_confirmed','','.user-count2')">
                    <a href="javascript:void(0);">{{ $lang['receipt_not'] }}({{ $to_confirm_order }})</a>
                    <input name="to_confirm_order" id="to_confirm_order" type="hidden" value="103" />
                    </li>
                    <li class="user-count4" onclick="get_OrderSearch('to_finished', '{{ $action }}', 'toBe_finished','','.user-count4')">
                    <a href="javascript:void(0);">{{ $lang['cs']['102'] }}({{ $to_finished }})</a>
                    <input name="to_finished" id="to_finished" type="hidden" value="102" />
                    </li>
                </ul>
            </div>
            <div class="user-list-title clearfix">
                <div class="user-list-filter">
                    <div id="order_status" class="imitate_select w120 mr10">
                        <div class="cite"><span>{{ $lang['all_status'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li id="order_status_-1"><a href="javascript:void(0);" data-idtxt="status_list" data-action="{{ $action }}" data-type="order_status" data-id="-1" data-value='-1'>{{ $lang['all_status'] }}</a></li>

@foreach($status_list as $key => $list)

                            <li id="order_status_{{ $key }}"><a href="javascript:void(0);" data-idtxt="status_list" data-action="{{ $action }}" data-type="order_status" data-id="{{ $key }}" data-value='{{ $key }}'>{{ $list }}</a></li>

@endforeach

                        </ul>
                        <input name="order_status_list" type="hidden" value="-1" id="order_status_val">
                    </div>
                    <div id="dateTime" class="imitate_select w120">
                        <div class="cite"><span>{{ $lang['all_time'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li id="time_allDate"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="allDate" data-value="allDate">{{ $lang['all_time'] }}</a></li>
                            <li id="time_today"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="today" data-value="today">{{ $lang['Today'] }}</a></li>
                            <li id="time_three_today"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="three_today" data-value="three_today">{{ $lang['three_today'] }}</a></li>
                            <li id="time_aweek"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="aweek" data-value="aweek">{{ $lang['aweek'] }}</a></li>
                            <li id="time_thismonth"><a href="javascript:void(0);" data-idtxt="submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="thismonth" data-value="thismonth">{{ $lang['thismonth'] }}</a></li>
                        </ul>
                        <input name="order_submitDate" type="hidden" value="allDate" id="dateTime_val">
                    </div>
                </div>
                <div class="user-list-search">
                    <input type="text" id="ip_keyword" class="text" onkeydown="javascript:if(event.keyCode==13) get_OrderSearch('ip_keyword');" onblur="if (this.value=='') this.value=this.defaultValue; this.style.color='#999'" onfocus="if (this.value==this.defaultValue)this.value='';this.style.color='#666'" placeholder="{{ $lang['search_oreder_user'] }}" name="" style="color:#999;"/>
                    <button type="button" onclick="get_OrderSearch('ip_keyword', '{{ $action }}', 'text')"><i class="iconfont icon-search"></i></button>
                </div>
            </div>
            <div id="user_order_list">

@if(!$order_type)

            @include('frontend::library/user_order_list')

@endif

            </div>
        </div>

@endif





@if($action == 'snatch_list')

		<div class="user-mod">
			<div class="user-title">
				<h2>{{ $lang['my_snatch'] }}</h2>
                <ul class="tabs">
                    <li class="active"><a href="">{{ $lang['snatch_all'] }}({{ $all_snatch ?? 0 }})</a></li>
                    <li class="user-count3" onclick="get_SnatchSearch('is_going', '{{ $action }}', 'is_going','','.user-count3')">
						<a href="javascript:void(0);">{{ $lang['auction_staues']['1'] }}({{ $is_going }})</a>
						<input name="is_going" id="is_going" type="hidden" value="1" />
                    </li>
                    <li class="user-count1" onclick="get_SnatchSearch('is_finished', '{{ $action }}', 'is_finished','','.user-count1')">
						<a href="javascript:void(0);">{{ $lang['auction_staues']['3'] }}({{ $is_finished }})</a>
						<input name="is_finished" id="is_finished" type="hidden" value="3" />
                    </li>
                </ul>
            </div>
			<div class="comment-list-warp clearfix">
                <div class="user-order-list" id="user-snatch-list">
					@include('frontend::library/user_snatch_list')
                </div>
            </div>
		</div>

@endif





@if($action == 'coupons')

        <div class="user-mod" ectype="tabSection">
            <div class="user-title">
                <h2>{{ $lang['Coupon_list'] }}</h2>
                <ul class="tabs" ectype="tabs">
                    <li class="active"><a href="javascript:void(0);">{{ $lang['not_use'] }}({{ $no_use_count }})</a></li>
                    <li><a href="javascript:void(0);">{{ $lang['had_use'] }}({{ $yes_use_count }})</a></li>
                    <li><a href="javascript:void(0);">{{ $lang['overdue'] }}({{ $yes_time_count }})</a></li>
                    <li><a href="javascript:void(0);">{{ $lang['About_expire'] }}({{ $no_time_count }})</a></li>
                </ul>
            </div>
            <div class="user-coupons-warp" ectype="tabContent">
                <div id="coupons_list0" class="coupons_content_list">
                    <div class="coupons-items">

@if($no_use)


@foreach($no_use as $vo)

                        <div class="coupons-item
@if($loop->iteration % 2 == 0)
coupons-item-double
@endif
" >
                            <div class="c-type">
                                <i class="i-left"></i>
                                <div class="c-t-cont">
                                    <div class="c-t-c-top">

@if($vo['cou_type'] != 5)
<strong><em>{{ config('shop.currency_format', '¥') }}</em>
@if($vo['order_sn'] != '')
{{ $vo['order_coupons'] }}
@else

@if($vo['uc_money'] != 0)
{{ $vo['uc_money'] }}
@else
{{ $vo['cou_money'] }}
@endif

@endif
</strong>
@else
<i class="icon-my"></i>
@endif

                                        <span>{{ $lang['Consumption_full'] }}{{ $vo['cou_man'] }}{{ $lang['yuan'] }}&nbsp;{{ $lang['keyong'] }}</span>
                                    </div>
                                    <div class="c-t-c-bot">
                                        <p>{{ $lang['Platform_limit'] }}：
@if($vo['store_name'])
{{ $vo['store_name'] }}
@else
{{ $lang['whole_platform'] }}
@endif
</p>
                                        <p>{{ $lang['range_bonus'] }}：[{{ $vo['cou_type_name'] }}][{{ $vo['cou_goods_name'] }}]</p>
                                        <p>{{ $vo['cou_start_time_date'] }} ~ {{ $vo['cou_end_time_date'] }}</p>
                                    </div>
                                </div>

@if($will_passed)

                                <i class="i-right"></i>
                                <i class="i-soon">{{ $lang['About_expire'] }}</i>

@endif

                            </div>

@if($is_used)

                            <div class="c-msg">
                                <span class="not">{{ $lang['had_use'] }}</span>
                            </div>

@else

                            <div class="c-msg">
                                <a href="search.php?cou_id={{ $vo['cou_id'] }}&user_cou=1">
                                    <span class="lj">{{ $lang['Immediate_use'] }}</span>
                                    <i class="iconfont icon-down"></i>
                                </a>
                            </div>

@endif

                        </div>

@endforeach


@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info"><h3>{{ $lang['no_coupons_keyong'] }}</h3></div>
                        </div>

@endif

                    </div>
                </div>
                <div id="coupons_list1" style="display: none;" class="coupons_content_list">
                    <div class="coupons-items coupons-item-disabled">

@if($yes_use)


@foreach($yes_use as $vo)

                        <div class="coupons-item">
                            <div class="c-type">
                                <i class="i-left"></i>
                                <div class="c-t-cont">
                                    <div class="c-t-c-top">

@if($vo['cou_type'] != 5)
<strong><em>{{ config('shop.currency_format', '¥') }}</em>
@if($vo['order_sn'] != '')
{{ $vo['order_coupons'] }}
@else

@if($vo['uc_money'] != 0)
{{ $vo['uc_money'] }}
@else
{{ $vo['cou_money'] }}
@endif

@endif
</strong>
@else
<i class="icon-my"></i>
@endif

                                        <span>{{ $lang['Consumption_full'] }}{{ $vo['cou_man'] }}{{ $lang['yuan'] }}&nbsp;{{ $lang['keyong'] }}</span>
                                    </div>
                                    <div class="c-t-c-bot">
                                        <p>{{ $lang['Platform_limit'] }}：
@if($vo['store_name'])
{{ $lang['xian'] }}{{ $vo['store_name'] }}
@else
{{ $lang['whole_platform'] }}
@endif
</p>
                                        <p>{{ $lang['range_bonus'] }}：[{{ $vo['cou_type_name'] }}][{{ $vo['cou_goods_name'] }}]</p>
                                        <p>{{ $vo['cou_start_time_date'] }} ~ {{ $vo['cou_end_time_date'] }}</p>
                                    </div>
                                </div>

@if($will_passed)

                                <i class="i-right"></i>
                                <i class="i-soon">{{ $lang['About_expire'] }}</i>

@endif

                            </div>

@if($is_used)

                            <div class="c-msg">
                                <span class="not">{{ $lang['had_use'] }}</span>
                            </div>

@else

                            <div class="c-msg">
                                <span class="not">{{ $lang['had_use'] }}</span>
                            </div>

@endif

                        </div>

@endforeach


@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info"><h3>{{ $lang['no_coupons_use'] }}</h3></div>
                        </div>

@endif

                    </div>
                </div>
                <div id="coupons_list2" style="display: none;" class="coupons_content_list">
                    <div class="coupons-items">

@if($yes_time)


@foreach($yes_time as $vo)

                        <div class="coupons-item coupons-item-disabled" >
                            <div class="c-type">
                                <i class="i-left"></i>
                                <div class="c-t-cont">
                                    <div class="c-t-c-top">

@if($vo['cou_type'] != 5)
<strong><em>{{ config('shop.currency_format', '¥') }}</em>
@if($vo['order_sn'] != '')
{{ $vo['order_coupons'] }}
@else

@if($vo['uc_money'] != 0)
{{ $vo['uc_money'] }}
@else
{{ $vo['cou_money'] }}
@endif

@endif
</strong>
@else
<i class="icon-my"></i>
@endif

                                        <span>{{ $lang['Consumption_full'] }}{{ $vo['cou_man'] }}{{ $lang['yuan'] }}&nbsp;{{ $lang['keyong'] }}</span>
                                    </div>
                                    <div class="c-t-c-bot">
                                        <p>{{ $lang['Platform_limit'] }}：
@if($vo['store_name'])
{{ $lang['xian'] }}{{ $vo['store_name'] }}
@else
{{ $lang['whole_platform'] }}
@endif
</p>
                                        <p>{{ $lang['range_bonus'] }}：[{{ $vo['cou_type_name'] }}][{{ $vo['cou_goods_name'] }}]</p>
                                        <p>{{ $vo['cou_start_time_date'] }} ~ {{ $vo['cou_end_time_date'] }}</p>
                                    </div>
                                </div>

@if($will_passed)

                                <i class="i-right"></i>
                                <i class="i-soon">{{ $lang['About_expire'] }}</i>

@endif

                            </div>

@if($is_used)

                            <div class="c-msg">
                                <span class="not">{{ $lang['had_use'] }}</span>
                            </div>

@else

                            <div class="c-msg">
                                <span class="not">{{ $lang['overdue'] }}</span>
                            </div>

@endif

                        </div>

@endforeach


@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info"><h3>{{ $lang['no_coupons_over'] }}</h3></div>
                        </div>

@endif

                    </div>
                </div>
                <div id="coupons_list3" style="display: none;" class="coupons_content_list">
                    <div class="coupons-items
@if($loop->iteration % 2 == 0)
coupons-item-double
@endif
">

@if($no_time)


@foreach($no_time as $vo)

                        <div class="coupons-item
@if($loop->iteration % 2 == 0)
coupons-item-double
@endif
" >
                            <div class="c-type">
                                <i class="i-left"></i>
                                <div class="c-t-cont">
                                    <div class="c-t-c-top">

@if($vo['cou_type'] != 5)
<strong><em>{{ config('shop.currency_format', '¥') }}</em>
@if($vo['order_sn'] != '')
{{ $vo['order_coupons'] }}
@else

@if($vo['uc_money'] != 0)
{{ $vo['uc_money'] }}
@else
{{ $vo['cou_money'] }}
@endif

@endif
</strong>
@else
<i class="icon-my"></i>
@endif

                                        <span>{{ $lang['Consumption_full'] }}{{ $vo['cou_man'] }}{{ $lang['yuan'] }}&nbsp;{{ $lang['keyong'] }}</span>
                                    </div>
                                    <div class="c-t-c-bot">
                                        <p>{{ $lang['Platform_limit'] }}：
@if($vo['store_name'])
{{ $lang['xian'] }}{{ $vo['store_name'] }}
@else
{{ $lang['whole_platform'] }}
@endif
</p>
                                        <p>{{ $lang['range_bonus'] }}：[{{ $vo['cou_type_name'] }}][{{ $vo['cou_goods_name'] }}]</p>
                                        <p>{{ $vo['cou_start_time_date'] }} ~ {{ $vo['cou_end_time_date'] }}</p>
                                    </div>
                                </div>

@if($will_passed)

                                <i class="i-right"></i>
                                <i class="i-soon">{{ $lang['About_expire'] }}</i>

@endif

                            </div>

@if($is_used)

                            <div class="c-msg">
                                <span class="not">{{ $lang['had_use'] }}</span>
                            </div>

@else

                            <div class="c-msg">
                                <a href="search.php?cou_id={{ $vo['cou_id'] }}&user_cou=1">
                                    <span class="lj">{{ $lang['Immediate_use'] }}</span>
                                    <i class="iconfont icon-down"></i>
                                </a>
                            </div>

@endif

                        </div>

@endforeach


@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info"><h3>{{ $lang['no_coupons_soon_over'] }}</h3></div>
                        </div>

@endif

                    </div>
                </div>
            </div>
        </div>

@endif





@if($action == 'track_packages')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_track_packages'] }}</h2>
            </div>
            <div class="user-order-list user-trackpack">

@forelse($orders as $key => $item)

                <dl class="item" data-orderId='{{ $item['order_id'] }}'>
                    <dt class="item-t">
                        <div class="t-statu">
@if($item['shipping_status'] != "")
{{ $item['shipping_status'] }}
@else
&nbsp;
@endif
</div>
                        <div class="t-info">
                            <span class="info-item">{{ $lang['order_number'] }}：{{ $item['order_sn'] }}</span>
                            <span class="info-item">{{ $lang['Waybill_number'] }}：
@if($item['invoice_no'] != "")
{{ $item['invoice_no'] }}
@else
&nbsp;
@endif
</span>
                            <span class="info-item">
@if($item['shipping_name'] != "")
{{ $item['shipping_name'] }}
@else
&nbsp;
@endif
</span>
                        </div>
                        <div class="t-right">{{ $item['formated_shipping_time'] }}</div>
                    </dt>
                    <dd class="item-c relative">
                        <div class="c-left">

@foreach($item['goods'] as $goods)

                            <div class="c-goods" ectype="c-goods"
@if($loop->index > 2)
 style="display:none;"
@endif
>
                                <div class="c-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['goods_thumb'] }}" alt=""></a></div>
                                <div class="c-info">
                                    <div class="info-name"><a href="{{ $goods['url'] }}">{{ $goods['goods_name'] }}</a></div>
                                    <div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
                                </div>
                            </div>
@php
$goods_count = $loop->iteration;
@endphp

@endforeach


@if($goods_count > 3)

                            <span class="ellipsis">......</span>
                            <a href="javascript:void(0);" class="order-prolist-more" ectype="opm">{{ $lang['see_more'] }}︾</a>

@endif

                        </div>
                        <div class="c-handle" ectype="track-packages-btn">
                            <a href="javascript:void(0)" class="sc-btn"><i class="iconfont icon-truck"></i>{{ $lang['Track'] }}</a>
                            <div class="comment-box" ectype="track-packages-info">
                                <i class="box-i"></i>

@if($item['invoice_no'])

                                <div class="conmment-box-content">
                                    <div class="cont" id="retData_{{ $item['order_id'] }}">

                                    </div>
                                </div>

@endif

                            </div>
                        </div>
                        <span id="invoice_no_{{ $item['order_id'] }}" style="display:none">{{ $item['invoice_no'] }}</span>
                        <span id="shipping_name_{{ $item['order_id'] }}" style="display:none">{{ $item['express_name_code'] }}</span>
                    </dd>
                </dl>

@empty

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
                </div>

@endforelse

            </div>
        </div>

@endif





@if($action == 'account_log' || $action == 'account_detail' || $action == 'account_paypoints' || $action == 'account_rankpoints')

        <div class="user-mod">
            <div class="user-account-warp">

                <div class="user-title">
                    <h2>{{ $lang['account_log'] }}</h2>
                </div>
                <div class="account-main account-not">
                    <div class="account-price"><strong>{{ $lang['account_balance'] }}：</strong><span class="a-price">{{ $info['surplus'] }}</span></div>
                    <div class="account-btn">
                        @if($user_balance_recharge == 1)
                            <a href="user.php?act=account_deposit" class="sc-btn sc-redBg-btn">{{ $lang['online_recharge'] }}</a>
                        @endif

@if($user_balance_withdrawal == 1)

                        <a href="user.php?act=
@if($validate_info['real_name'])
account_raply
@else
account_safe&type=real_name&step=first
@endif
" class="sc-btn">{{ $lang['application_withdrawal'] }}</a>

@endif

                    </div>

                    <div class="a-m-bot">
                        <div class="user-frame-items">
                            @if ($cfg['use_bonus'] == 1)
                            <div class="item">
								<a href="user_activity.php?act=bonus">
									<span>{{ $lang['label_bonus'] }}</span>
									<span class="b-price">{{ $info['bonus_count'] }}</span>
								</a>
                            </div>
                            @endif
                            <div class="item">
								<a href="user_activity.php?act=coupons">
									<span>{{ $lang['label_coupons'] }}</span>
									<span class="b-price">{{ $coupons['num'] }}</span>
								</a>
                            </div>
                            <div class="item">
								<a href="user.php?act=value_card">
									<span>{{ $lang['label_value_card'] }}</span>
									<span class="b-price">{{ $value_card['num'] }}</span>
								</a>
                            </div>
                            <div class="item">
								<span>{{ $lang['consume_integral'] }}</span>
								<span class="b-price">{{ $info['integral'] }}</span>
                            </div>
                        </div>
                    </div>

@if($action == 'account_log')

                    <div class="user-prompt mt50">
                        <div class="tit"><span>{{ $lang['account_log_notic_title'] }}</span><i class="iconfont icon-down"></i></div>
                        <div class="info">
                            {!! $lang['account_log_notic'] !!}
                        </div>
                    </div>

@endif

                    <div class="user-title mt30">
                        <ul class="tabs">
                            <li
@if($action == 'account_log')
class="active"
@endif
><a href="user.php?act=account_log">{{ $lang['05_seller_account_log'] }}</a></li>
                            <li
@if($action == 'account_detail')
class="active"
@endif
><a href="user.php?act=account_detail">{{ $lang['account_detail'] }}</a></li>
                            <li
@if($action == 'account_paypoints')
class="active"
@endif
><a href="user.php?act=account_paypoints">{{ $lang['consume_integral'] }}</a></li>
                            <li
@if($action == 'account_rankpoints')
class="active"
@endif
><a href="user.php?act=account_rankpoints">{{ $lang['rank_integral'] }}</a></li>
                        </ul>
                    </div>

@if($action == 'account_log')

                    <div class="account-open-list">
                        <table class="user-table user-table-account">
                            <colgroup>
                                <col width="140">
                                <col width="120">
                                <col width="120">
                                <col width="140">
                                <col width="140">
                                <col width="100">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>{{ $lang['info'] }}</th>
                                    <th class="tl">{{ $lang['money'] }}</th>
                                    <th class="tl">{{ $lang['payment_method'] }}</th>
                                    <th class="tl">{{ $lang['process_notic'] }}</th>
                                    <th class="tl">{{ $lang['admin_notic'] }}</th>
                                    <th>{{ $lang['is_paid'] }}</th>
                                    <th>{{ $lang['handle'] }}</th>
                                </tr>
                            </thead>
                            <tbody>

@if($account_log)


@foreach($account_log as $item)

                            <tr>
                                <td>{{ $item['add_time'] }}<br><div class="ftx-01">{{ $item['type'] }}</div></td>
                                <td>{{ $item['amount'] }}
@if($item['deposit_fee'] != 0)
<br/><span class='red'>({{ $lang['pay_fee'] }}：{{ $item['deposit_fee'] }})</span>
@endif
</td>
                                <td>{{ $item['payment'] ?? N/A }}</td>
                                <td>{{ $item['short_user_note'] }}</td>
                                <td>{{ $item['short_admin_note'] }}</td>
                                <td class="tc account_pay">
                                    {{ $item['pay_status'] }}
                                </td>
                                <td>

@if($item['handle'])

                                        <p>
                                        <a class="sc_btnt_ype" href="user.php?act=account_complaint&id={{ $item['id'] }}">

@if($item['complaint_imges'])

                                            {{ $lang['is_complaint'] }}

@else

                                            {{ $lang['complaint'] }}

@endif

                                        </a>
                                        </p>
                                        <p>
                                        {!! $item['handle'] !!}

@if(($item['is_paid'] == 0 && $item['process_type'] == 1) || $item['handle'])

                                        <a href="user.php?act=cancel&id={{ $item['id'] }}" onclick="if (!confirm('{{ $lang['confirm_remove_account'] }}')) return false;">{{ $lang['is_cancel'] }}</a>

@endif

                                        </p>

@else

                                        {!! $lang['null_handle'] !!}

@endif

                                </td>
                            </tr>

@endforeach


@else

                            <tr>
                                <td colspan="7">{{ $lang['account_log_empty'] }}</td>
                            </tr>

@endif

                            </tbody>
                        </table>
                    </div>
                    @include('frontend::library/pages')

@endif



@if($action == 'account_detail')

                    <div class="account-open-list">
                        <table class="user-table user-table-account">
                            <colgroup>
                                <col width="140">
                                <col width="120">
                                <col width="120">
                                <col width="160">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>{{ $lang['process_time'] }}</th>
                                    <th class="tl">{{ $lang['surplus_pro_type'] }}</th>
                                    <th class="tl">{{ $lang['money'] }}</th>
                                    <th class="tl tc">{{ $lang['change_desc'] }}</th>
                                </tr>
                            </thead>
                            <tbody>

@forelse($account_log as $item)

                                <tr>
                                    <td>{{ $item['change_time'] }}</td>
                                    <td>{{ $item['type'] }}</td>
                                    <td>{{ $item['amount'] }}
@if($item['deposit_fee'] != 0)
<br/><span class='red'>({{ $lang['pay_fee'] }}：{{ $item['deposit_fee'] }})</span>
@endif
</td>
                                    <td>{{ $item['short_change_desc'] }}</td>
                                </tr>

@empty

                                <tr>
                                    <td colspan="6">{{ $lang['account_log_empty'] }}</td>
                                </tr>

@endforelse

                            </tbody>
                        </table>
                    </div>
                    @include('frontend::library/pages')

@endif



@if($action == 'account_paypoints')

                    <div class="account-open-list">
                        <table class="user-table user-table-account">
                            <colgroup>
                                <col width="140">
                                <col width="120">
                                <col width="120">
                                <col width="160">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>{{ $lang['process_time'] }}</th>
                                    <th class="tl">{{ $lang['surplus_pro_type'] }}</th>
                                    <th class="tl">{{ $lang['points'] }}</th>
                                    <th class="tl tc">{{ $lang['change_desc'] }}</th>
                                </tr>
                            </thead>
                            <tbody>

@forelse($account_log as $item)

                                <tr>
                                    <td>{{ $item['change_time'] }}</td>
                                    <td>{{ $item['type'] }}</td>
                                    <td>{{ $item['pay_points'] }}</td>
                                    <td>{{ $item['short_change_desc'] }}</td>
                                </tr>

@empty

                                <tr>
                                    <td colspan="6">{{ $lang['account_log_empty'] }}</td>
                                </tr>

@endforelse

                            </tbody>
                        </table>
                    </div>
                    @include('frontend::library/pages')

@endif

                </div>


@if($action == 'account_rankpoints')

                <div class="account-open-list">
                    <table class="user-table user-table-account">
                        <colgroup>
                            <col width="140">
                            <col width="120">
                            <col width="120">
                            <col width="160">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>{{ $lang['process_time'] }}</th>
                                <th class="tl">{{ $lang['surplus_pro_type'] }}</th>
                                <th class="tl">{{ $lang['points'] }}</th>
                                <th class="tl tc">{{ $lang['change_desc'] }}</th>
                            </tr>
                        </thead>
                        <tbody>

@forelse($account_log as $item)

                            <tr>
                                <td>{{ $item['change_time'] }}</td>
                                <td>{{ $item['type'] }}</td>
                                <td>{{ $item['rank_points'] }}</td>
                                <td>{{ $item['short_change_desc'] }}</td>
                            </tr>

@empty

                            <tr>
                                <td colspan="6">{{ $lang['account_log_empty'] }}</td>
                            </tr>

@endforelse

                        </tbody>
                    </table>
                </div>
				@include('frontend::library/pages')

@endif

          </div>
		</div>

@endif



@if($action == "account_complaint")

        <div class="user-mod">
        	<div class="type">
                <div class="user-title">
                    <h2>{{ $lang['account_appeal'] }}</h2>
                </div>
                <div class="account-main account-bind">
                    <div class="user-items">
                        <form name="account_complaint" method="post" action="user.php" enctype="multipart/form-data" class="user-form user-form-safe">
                            <div class="item">
                                <div class="label">{{ $lang['account_time'] }}：</div>
                                <div class="value">
                                	<span class="txt-lh">{{ $user_account['add_time'] }}</span>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['deposit_money'] }}：</div>
                                <div class="value">
                                	<span class="txt-lh">{{ $user_account['amount'] }}</span>
                                </div>
                            </div>

                            <div class="item">
                                <div class="label">{{ $lang['payment'] }}：</div>
                                <div class="value">
                                	<span class="txt-lh">{{ $user_account['payment'] }}</span>
                                </div>
                            </div>

                            <div class="item">
                                <div class="label"><em class="red">*</em>{{ $lang['appeal_credential'] }}：</div>
                                <div class="value">
                                	<div class="type-file-box">

@if($user_account['complaint_imges'])

                                        <span class="show"><a href="{{ $user_account['complaint_imges'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $user_account['complaint_imges'] }}>')" onmouseout="toolTip()"></i></a></span>

@else

                                        <input type="button" name="button" id="button" class="type-file-button">
                                        <input type="file" name="complaint_imges" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">
                                        <input type="text" name="textfile_zheng" class="type-file-text" id="textfile_zheng" value="" readonly>

@endif

                                    </div>
                                    <div class="form_prompt"></div>
                                </div>
                            </div>

                            <div class="item">
                                <div class="label">{{ $lang['appeal_explain'] }}：</div>
                                <div class="value">
                                	<span class="txt-lh">
										<textarea name="complaint_details" class="text text-2" style="height:100px; line-height:20px; padding-top:3px; padding-bottom:3px; font-size:12px;">{{ $user_account['complaint_details'] }}</textarea>
                                    </span>
                                </div>
                            </div>


@if(!$user_account['complaint_imges'])

                                <div class="item item-button">
                                    <div class="label">&nbsp;</div>
                                    <div class="value">
                                        <input type="hidden" value="{{ $id }}" name="id">
                                        <input type="hidden" value="{{ $operate }}" name="act">
                                        <input type="submit" id="authSubmit" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_goods'] }}">
                                    </div>
                                </div>

@endif

                        @csrf </form>
                    </div>
                </div>
            </div>
        </div>

@endif



@if($action == "account_deposit")

        <div class="user-mod">
            <div class="user-account-warp">
                <div class="user-title">
                    <h2>{{ $lang['online_recharge'] }}</h2>
                </div>
                <form name="formSurplus" method="post" action="user.php" id="formSurplus">
                <div class="account-main account-deposit">
                    <div class="user-items">
                        <div class="item">
                            <div class="label">{{ $lang['account_balance'] }}：</div>
                            <div class="value"><span class="txt-lh ftx-01">{{ $user_info['surplus'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['surplus_type_0'] }}：</div>
                            <div class="value">
                                <input type="text" name="amount" class="text text-1" />
                                <span class="ts">{{ $lang['yuan'] }}
@if($buyer_recharge > 0)
{{ $lang['user_recharge_notic'] }}{{ $buyer_recharge }}{{ $lang['unit_yuan'] }}
@endif
</span>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['payment'] }}：</div>
                            <div class="value">

@foreach($payment as $key => $list)

                                <div class="radio-item">
                                    <input type="radio" name="payment_id" class="ui-radio" value="{{ $list['pay_id'] }}" id="payment-remittance_{{ $list['pay_id'] }}"
@if($key == 0)
checked
@endif
 />
                                    <label for="payment-remittance_{{ $list['pay_id'] }}" class="ui-radio-label">{{ $list['pay_name'] }}</label>
                                </div>

@endforeach

                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['process_notic'] }}：</div>
                            <div class="value"><textarea name="user_note" class="textarea"></textarea></div>
                        </div>
                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input type="hidden" name="surplus_type" value="0" />
                                <input type="hidden" name="rec_id" value="{{ $order['id'] ?? 0 }}" />
                                <input type="hidden" name="act" value="act_account" />
                                <input type="button" class="sc-btn sc-redBg-btn" id="submitBtn" value="{{ $lang['Determine_Recharge'] }}" />
                                <input type="button" id="goback_account_log" class="sc-btn sc-redBg-btn" value="{{ $lang['back_up_page'] }}">
                            </div>
                        </div>
                    </div>
                </div>
                @csrf </form>
            </div>
        </div>

@endif



@if($action == "account_raply")

        <div class="user-mod">
            <div class="user-account-warp">
                <div class="user-title">
                    <h2>{{ $lang['application_withdrawal'] }}</h2>
                </div>
                <form name="formSurplus" method="post" action="user.php" id="formSurplus">
                    <div class="user-items">
                        <div class="item">
                            <div class="label">{{ $lang['Real_name'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $validate_info['real_name'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['Current_balance_label'] }}：</div>
                            <div class="value"><span class="txt-lh ftx-01">{{ $user_info['surplus'] }}{{ $lang['renmingni'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['bank'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $validate_info['bank_name'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['bank_card'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $validate_info['bank_card'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['repay_money'] }}：</div>
                            <div class="value">
                                <input type="text" name="amount" class="text text-2" ectype="deposit_amout"/>
                                <span class="ts">{{ $lang['yuan'] }}{{ $lang['renmingni'] }}
@if($buyer_cash > 0)
{{ $lang['forward_total_lowest'] }}{{ $buyer_cash }}{{ $lang['yuan'] }}
@endif
</span>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
						 <div class="item hide" ectype="deposit_fee">
                            <div class="label">{{ $lang['forward_pay_fee'] }}：</div>
                            <div class="value"><span class="txt-lh" ectype="deposit_fee_value"></span><input type="hidden" value="" name="deposit_money" /></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['process_notic'] }}：</div>
                            <div class="value">
                                <textarea name="user_note" class="text text-2" style="height:100px; line-height:20px; padding-top:3px; padding-bottom:3px;"></textarea>
                            </div>
                        </div>


@if($enabled_sms_signin)

                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_mobile'] }}：</div>
                            <div class="value">
                                <span class="txt-lh ftx-01">{{ $validate_info['bank_mobile'] }}</span>
                                <input class="text text-1" type="hidden" id="mobile_phone" name="mobile_phone" value="{{ $validate_info['bank_mobile'] }}">
                                <span id="span-phone-modify" class="error-text" style="display: none;">{{ $lang['label_mobile_input'] }}</span>
                                <div class="form_prompt"></div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['bindMobile_code'] }}：</div>
                            <div class="value">
                                <div class="sm-input">
                                    <input name="sms_value" id="sms_value" type="hidden" value="sms_code" />
                                    <input type="text" name="mobile_code" tabindex="1" id="mobile_code" />
                                    <a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                </div>
                                <div class="form_prompt" id="phone_notice"></div>
                            </div>
                        </div>

@endif


                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input id="flag" type="hidden" value="account_raply" name="flag">
                                <input id="seccode" type="hidden" value="{{ $sms_security_code }}" name="seccode">
                                <input type="hidden" name="surplus_type" value="1" />
                                <input type="hidden" name="act" value="act_account" />
                                <input type="hidden" name="sc_guid" value="{{ $sc_guid }}">
                                <input type="hidden" name="sc_rand" value="{{ $sc_rand }}">
								<input type="hidden" value="{{ $deposit_fee }}" name="deposit_fee" />
                                <input type="hidden" value="{{ $buyer_cash }}" name="buyer_cash" />
                                <input type="button" class="sc-btn sc-redBg-btn" value="{{ $lang['application_withdrawal'] }}" id="submitBtn" />
                                <input type="reset" class="sc-btn sc-redBg-btn" value="{{ $lang['Reset_Form'] }}" />
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                    </div>
                @csrf </form>
            </div>
        </div>

@endif



@if($action == "act_account")

        <div class="user-mod">
            <div class="user-title">
                <h3>{{ $lang['Recharge_info'] }}</h3>
            </div>
            <table class="user-table user-table-valueCard">
                <colgroup>
                    <col width="200">
                    <col width="150">
                    <col width="150">
                    <col width="120">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th class="tc">{{ $lang['deposit_money'] }}</th>
                        <th>{{ $lang['payment'] }}</th>
                        <th>{{ $lang['pay_fee'] }}</th>
                        <th>{{ $lang['handle'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tc">{{ $amount }}</td>
                        <td class="tc">{{ $payment['pay_name'] }}</td>
                        <td class="tc">{{ $pay_fee }}</td>
                        <td class="tc"><div class="or-btn">
@if($payment['pay_button'] != "")
{!! $payment['pay_button'] !!}
@else
&nbsp;
@endif
</div></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="td td_bf">
                            <h1>{{ $lang['describe'] }}：</h1>
                            <span>{{ $payment['pay_desc'] }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
            var timer;
            function checkOrder(){
                var log_id = '{{ $order['log_id'] ?? 0 }}';
                var pay_code = '{{ $payment['pay_code'] ?? 0 }}';

                if(pay_code == 'wxpay'){
                    var url = "user_order.php?act=checkorder&log_id=" + log_id+ "&pay_code=" + pay_code;
                    $.get(url, {}, function(data){
                        //已付款
                        if(data.code == 1){
                            clearTimeout(timer);
                            location.href = "user.php?act=account_log";
                        }
                    },'json');
                    timer = setTimeout("checkOrder()", 5000);
                }
            }
            checkOrder();
        </script>

@endif





@if($action == 'baitiao' || $action == 'baitiao_pay_log')

        <div class="user-mod">
                <div class="user-title width_860 fl">
                    <h2>{{ $lang['baitiao'] }}</h2>
                </div>
                <div class="user-baitiao-info width_860 fl">
                    <div>
                        <div class="u-b-top fl">
                            <strong class="u-b-t-tit">{{ $lang['Pending_payment'] }}：</strong>
                            <span><em id="number">{{ $repay_bt['numbers'] }}</em>{{ $lang['zhang'] }}</span>
                        </div>
                        <div class="u-b-top fl">
                            <strong class="u-b-t-tit">{{ $lang['already_amount'] }}：</strong>
                            <span><em id="price">{{ $repay_bt['format_already_amount'] ?? 0 }}</em></span>
                        </div>
                        <div class="u-b-top fl">
                            <strong class="u-b-t-tit">{{ $lang['stay_pay'] }}：</strong>
                            <span><em id="price">{{ $repay_bt['format_stay_pay'] ?? 0 }}</em></span>
                        </div>
                    </div>

                    <div class="u-b-bot fl">
                        <div class="user-frame-items">
                            <div class="item">
                                <span>{{ $lang['bt_Total_amount'] }}</span>
                                <span class="b-price">{{ $bt_info['amount'] }}</span>
                            </div>
                            <div class="item">
                                <span>{{ $lang['Surplus_baitiao'] }}</span>
                                <span class="b-price">{{ $repay_bt['balance'] ?? 0 }}</span>
                            </div>
                            <div class="item">
                                <span>{{ $lang['also_day'] }}
@if($bt_info['over_repay_trem'])
（{{ $lang['also_delay'] }}）
@endif
</span>
                                <span class="b-price">{{ $bt_info['repay_term'] }}&nbsp;{{ $lang['day'] }}
@if($bt_info['over_repay_trem'])
（{{ $bt_info['over_repay_trem'] }}&nbsp;{{ $lang['day'] }}）
@endif
</span>
                            </div>
                            <div class="item">
                                <span>{{ $lang['amount_paid'] }}</span>
                                <span class="b-price">{{ $repay_bt['stay_pay'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="user-title mt30 width_860 fl">
                    <ul class="tabs">
                        <li
@if($action == 'baitiao')
class="active"
@endif
><a href="user_baitiao.php?act=baitiao">{{ $lang['Transaction_detail'] }}</a></li>
                        <li
@if($action == 'baitiao_pay_log')
class="active"
@endif
><a href="user_baitiao.php?act=baitiao_pay_log">{{ $lang['repayment_record'] }}</a></li>
                    </ul>
                </div>


@if($action == 'baitiao')

                <div class="user-baitiao-list">
                    <table class="user-table user-table-baitiao">
                        <colgroup>
                            <col width="200">
                            <col width="140">
                            <col width="140">
                            <col width="140">
                            <col width="125">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>{{ $lang['baitiao_order'] }}</th>
                                <th>{{ $lang['Consumer_account_day'] }}</th>
                                <th>{{ $lang['label_bt_one'] }}</th>
                                <th>{{ $lang['label_bt_two'] }}</th>
                                <th>{{ $lang['also_amount'] }}</th>
                                <th>{{ $lang['operation'] }}</th>
                            </tr>
                        </thead>
                        <tbody>

@foreach($bt_logs as $bt_log)

                            <tr>
                                <td>
                                    <a href="user_order.php?act=order_detail&order_id={{ $bt_log['order_id'] }}" target="_blank" class="ftx-05">
                                    {{ $bt_log['order_sn'] }}
                                    </a>

                                    <br/><a href="user_baitiao.php?act=baitiao_pay_log&log_id={{ $bt_log['log_id'] }}" class="sc-btn">{{ $lang['view_repayment_record'] }}</a>
                                </td>
                                <td class="tc">
                                    {{ $bt_log['use_date'] }}

@if($bt_log['is_stages'])

                                        ({{ $lang['by_stages'] }})

@endif

                                </td>
                                <td class="tc">
                                    {{ $bt_log['repay_date'] }}

@if($bt_log['is_stages'])

                                        <br/>{{ $bt_log['yes_num'] }}{{ $lang['stage'] }}/{{ $bt_log['stages_total'] }}{{ $lang['stage'] }}

@endif

                                </td>
                                <td class="tc">
@if($bt_log['repayed_date'])
{{ $bt_log['repayed_date'] }}
@endif
</td>
                                <td class="tc">

@if($bt_log['order_amount'])


@if($bt_log['is_stages'])

                                        {{ $bt_log['stages_one_price'] }}{{ $lang['element'] }}/{{ $lang['stage'] }}

@else

                                        {{ $bt_log['order_amount'] }}

@endif


@endif

                                </td>
                                <td class="tc">

@if($bt_log['is_refund'] == 1)

                                        <span class="ftx-03">{{ $lang['refound'] }}</span>

@elseif ($bt_log['is_repay'] == 1)

                                        <span class="ftx-01">{{ $lang['Has_paid_off'] }}</span>

@else

                                        <a href="user_baitiao.php?act=repay_bt&order_id={{ $bt_log['order_id'] }}&pay_id={{ $bt_log['pay_id'] }}&stages_num={{ $bt_log['stages_num'] ?? 0 }}" class="sc-btn" target="_blank">{{ $lang['repayment'] }}</a>

@endif

                                </td>
                            </tr>

@endforeach

                        </tbody>
                    </table>
                </div>
                @include('frontend::library/pages')

@endif



@if($action == 'baitiao_pay_log')

                <div class="user-baitiao-list">
                    <table class="user-table user-table-baitiao">
                        <colgroup>
                            <col width="100">
                            <col width="200">
                            <col width="140">
                            <col width="140">
                            <col width="165">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>{{ $lang['record_id'] }}</th>
                                <th>{{ $lang['order_number'] }}</th>
                                <th>{{ $lang['dijiqi'] }}</th>
                                <th>{{ $lang['also_amount'] }}</th>
                                <th>{{ $lang['also_state'] }}</th>
                                <th>{{ $lang['also_time'] }}</th>
                            </tr>
                        </thead>
                        <tbody>

@foreach($pay_list as $list)

                            <tr>
                                <td>{{ $list['id'] }}</td>
                                <td class="tc">
                                    {{ $list['order_sn'] }}
                                </td>
                                <td class="tc">
                                    {{ $list['stages_num'] }}/{{ $lang['qi'] }}
                                </td>
                                <td class="tc">{{ $list['stages_price'] }}</td>
                                <td class="tc">

@if($list['is_pay'])

                                        {{ $lang['also_pay']['is_pay'] }}

@else


@if($list['is_refund'])

                                            {{ $lang['refound'] }}

@else

                                            {{ $lang['also_pay']['not_pay'] }}

@endif


@endif

                                </td>
                                <td class="tc">

@if($list['pay_time'])

                                {{ $list['pay_time'] }}

@else


@if($list['is_refund'])

                                        {{ $lang['refound'] }}

@else

                                        <a href="user_baitiao.php?act=repay_bt&order_id={{ $list['order_id'] }}&pay_id={{ $list['pay_id'] }}&stages_num={{ $list['stages_num'] ?? 0 }}" class="sc-btn" target="_blank">{{ $lang['repayment'] }}</a>

@endif


@endif

                                </td>
                            </tr>

@endforeach

                        </tbody>
                    </table>
                </div>
                @include('frontend::library/pages')

@endif

         </div>

@endif





@if($action == 'repay_bt')

        <div class="user-mod">
            <div class="user-item-temp">
                <div class="user-title">
                    <h2>{{ $lang['repay_bt'] }}</h2>
                </div>
                <div class="user-baitiao-hk">
                    <div class="user-items">
                    	<div class="item">
                            <div class="label">{{ $lang['order_number'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $order['order_sn'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['label_bt_one'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $stages_info['repay_date'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['dijiqi'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $stages_num ?? 0 }}/{{ $lang['qi'] }}</span></div>
                        </div>

@if($stages_info['is_stages'] == 1)

                        <div class="item">
                            <div class="label">{{ $lang['Number_periods'] }}：</div>
                            <div class="value"><span class="txt-lh">{{ $stages_info['yes_num'] }}{{ $lang['stage'] }}/{{ $stages_info['stages_total'] }}{{ $lang['stage'] }}</span></div>
                        </div>

@endif


@if($stages_info['is_stages'] == 1)

                        <div class="item">
                            <div class="label">{{ $lang['rate'] }}：</div>
                            <div class="value"><span class="txt-lh">
@if($stages_info['stages_total'] == 1)
0%
@else
{{ $stages_rate }}%
@endif
</span></div>
                        </div>

@endif

                        <div class="item">
                            <div class="label">{{ $lang['amount_payable'] }}：</div>
                            <div class="value">

@if($stages_info['is_stages'] == 1)

                                <span class="ftx-01 txt-lh">{{ $stages_info['format_stages_one_price'] }}</span>

@else

                                <span class="ftx-01 txt-lh">{{ $order['order_amount'] }}</span>

@endif

                            </div>
                        </div>

@if($payment_list)

                        <form name="payment" method="post" action="user_baitiao.php" >
                        <div class="item">
                            <div class="label">{{ $lang['payment'] }}：</div>
                            <div class="value mt5">
                                <select name="pay_id" class="select fl">

@foreach($payment_list as $payment)

                                    <option value="{{ $payment['pay_id'] }}">
                                        {{ $payment['pay_name'] }}({{ $lang['pay_fee'] }}:{{ $payment['format_pay_fee'] }})
                                    </option>

@endforeach

                                </select>
                                <input type="hidden" name="act" value="repay_bt" />
                                <input type="hidden" name="order_id" value="{{ $order['order_id'] }}" />
                                <input type="hidden" name="stages_num" value="{{ $stages_num ?? 0 }}" />
                                <input type="submit" name="Submit" class="btn sc-redBg-btn btn30 fl ml10" value="{{ $lang['button_submit'] }}" />
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="value"><div class="pay_btn">{{ $order['pay_online'] }}</div></div>
                        </div>
                        @csrf </form>

@endif

                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
		var timer;
		function checkOrder(){
			var baitiao_id = '{{ $stages_info['baitiao_id'] ?? 0 }}';
			var log_id = '{{ $stages_info['log_id'] ?? 0 }}';
			var stages_num = '{{ $stages_num ?? 0 }}';
			var pay_code = '{{ $payment_info['pay_code'] ?? 0 }}';

			if(pay_code == 'wxpay'){
				var url = "user_order.php?act=checkorder&baitiao_id=" +baitiao_id+ "&log_id=" + log_id+ "&stages_num=" + stages_num + "&pay_code=" + pay_code;
				$.get(url, {}, function(data){
					//已付款
					if(data.code == 1){
						clearTimeout(timer);
						location.href = "user_baitiao.php?act=baitiao";
					}
				},'json');
				timer = setTimeout("checkOrder()", 5000);
			}

		}
        checkOrder();
        </script>

@endif





@if($action == 'merchants_upgrade' )

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['store_grade_list'] }}</h2>
				<ul class="tabs">
                    <li class="active"><a href="user.php?act=merchants_upgrade">{{ $lang['store_grade_list'] }}</a></li>
                    <li><a href="user.php?act=merchants_upgrade_log">{{ $lang['merchants_upgrade_log'] }}</a></li>
                </ul>
            </div>
            <div class="user-shop-warp">
                <table class="user-table user-table-shop">
                    <colgroup>
                        <col width="150">
                        <col width="80">
                        <col width="80">
                        <col width="150">
                        <col width="230">
                        <col width="80">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>{{ $lang['grade_name'] }}</th>
                            <th>{{ $lang['good_number'] }}</th>
                            <th>{{ $lang['temp_number'] }}</th>
                            <th>{{ $lang['grade_introduce'] }}</th>
                            <th>{{ $lang['entry_criteria'] }}</th>
                            <th>{{ $lang['grade_img'] }}</th>
                            <th>{{ $lang['handle'] }}</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($seller_grade as $item)

                         <tr>
                            <td><i class="user-rank user-rank-{{ $loop->iteration }}"></i><span class="user-rank-span">{{ $item['grade_name'] }}</span></td>
                            <td class="tc">{{ $item['goods_sun'] }}个</td>
                            <td class="tc">{{ $item['seller_temp'] }}个</td>
                            <td class="tc">{{ $item['grade_introduce'] }}</td>
                            <td class="tc">{{ $item['entry_criteria'] }}</td>
                            <td class="tc">
@if($item['grade_img'])
<a href="{{ $item['grade_img'] }}"  title="{{ $lang['grade_img'] }}" target="_blank"><img src="{{ $item['grade_img'] }}" width="30" height="30"></a>
@endif
</td>
                            <td class="tc">

@if($item['id'] == $grade_id)

                                    <a href="javascript:void(0);" class="sc-btn sc-btn-disabled">{{ $lang['now_grade'] }}</a>

@else


@if($is_expiry)

                                    <a href="user.php?act=application_grade&grade_id={{ $item['id'] }}" class="sc-btn">{{ $lang['once'] }}</a>

@else

                                    <span>{{ $lang['not_to_apply'] }}</span>

@endif


@endif

                            </td>
                        </tr>

@endforeach

                    </tbody>
                </table>
            </div>
        </div>
        <script type="text/javascript">
        $(function(){
			checkOrder();
		});

		var timer;
		function checkOrder(){
			var baitiao_id = '{{ $stages_info['baitiao_id'] ?? 0 }}';
			var log_id = '{{ $stages_info['log_id'] ?? 0 }}';
			var stages_num = '{{ $stages_num ?? 0 }}';
			var pay_code = '{{ $payment_info['pay_code'] ?? 0 }}';

			if(pay_code == 'wxpay'){
				var url = "user_order.php?act=checkorder&baitiao_id=" +baitiao_id+ "&log_id=" + log_id+ "&stages_num=" + stages_num + "&pay_code=" + pay_code;
				$.get(url, {}, function(data){
					//已付款
					if(data.code == 1){
						clearTimeout(timer);
						location.href = "user_baitiao.php?act=baitiao";
					}
				},'json');
				timer = setTimeout("checkOrder()", 5000);
			}

		}
        </script>

@endif





@if($action == 'merchants_upgrade_log' )

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['store_grade_list'] }}</h2>
				<ul class="tabs">
                    <li><a href="user.php?act=merchants_upgrade">{{ $lang['store_grade_list'] }}</a></li>
                    <li class="active"><a href="user.php?act=merchants_upgrade_log">{{ $lang['merchants_upgrade_log'] }}</a></li>
                </ul>
            </div>
            <div class="user-shop-warp">
                <table class="user-table user-table-shop">
                    <colgroup>
                        <col width="150">
                        <col width="90">
                        <col width="80">
                        <col width="120">
                        <col width="150">
						<col width="100">
                        <col width="80">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>{{ $lang['lab_card_id'] }}</th>
                            <th>{{ $lang['affiliate_lever'] }}</th>
                            <th>{{ $lang['money'] }}</th>
							<th>{{ $lang['payment'] }}</th>
							<th>{{ $lang['apply_time'] }}</th>
							<th>{{ $lang['apply_status'] }}</th>
                            <th>{{ $lang['handle'] }}</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($merchants_upgrade_log as $list)

                         <tr>
                            <td><a href='merchants_upgrade.php?act=edit&apply_id={{ $list['apply_id'] }}&grade_id={{ $list['grade_id'] }}'>{{ $list['apply_sn'] }}</a></td>
                            <td class="tc">{{ $list['grade_name'] }}</td>
                            <td class="tc">{{ $list['total_amount'] }}</td>
                            <td class="tc">{{ $list['pay_name'] }}</td>
                            <td class="tc">{{ $list['add_time'] }}</td>
                            <td class="tc">{{ $list['status_paid'] }}<br>{{ $list['status_apply'] }}</td>
                            <td class="tc">
                                <a href="user.php?act=application_grade_edit&apply_id={{ $list['apply_id'] }}&grade_id={{ $list['grade_id'] }}" class="sc-btn
@if($list['apply_status'] == 3 || $list['pay_status'] == 0)
 mr0
@endif
" title="
@if($list['apply_status'] == 0 && $list['pay_status'] == 0)
{{ $lang['edit'] }}
@else
{{ $lang['view'] }}
@endif
">
@if($list['apply_status'] == 0 && $list['pay_status'] == 0)
{{ $lang['edit'] }}
@else
{{ $lang['view'] }}
@endif
</a>

@if($list['apply_status'] == 3 || $list['pay_status'] == 0)

                                <a href="user.php?act=remove_apply_info&id={{ $list['apply_id'] }}" title="{{ $lang['drop'] }}" class="sc-btn mt10">{{ $lang['drop'] }}</a>

@endif

                            </td>
                        </tr>

@endforeach

                    </tbody>
                </table>
            </div>
        </div>

@endif





@if($action == 'application_grade' || $action == 'application_grade_edit')

        <div class="user-mod user-profile">
            <form action="user.php" method="post" name="grade_theForm"  enctype="multipart/form-data">
                <div class="user-account-warp">

@if($seller_apply_info['apply_status'] != 1)

                    <div class="user-title">
                        <h2>{{ $lang['examine_info'] }}</h2>
                        <a href="user.php?act=merchants_upgrade" class="more">{{ $lang['back'] }}</a>
                    </div>
                    <div class="user-grade-warp">
                        <div class="user-items">
                        	<div class="item">
                            	<div class="label">{{ $lang['apply_seller_Grade'] }}：</div>
                                <div class="value"><span class="txt-lh">{{ $grade_name }}</span></div>
                            </div>

@if($action == 'application_grade_edit')

                            <div class="item">
                                <div class="label">{{ $lang['adopt_status'] }}：</div>
                                <div class="value">

@if($seller_apply_info['apply_status'] == 0)

                                    <span class="txt-lh red">{{ $lang['not_audited'] }}</span>

@elseif ($seller_apply_info['apply_status'] == 2)

                                    <span class="txt-lh red">{{ $lang['audited_not_adopt'] }}</span>

@elseif ($seller_apply_info['apply_status'] == 3)

                                    <span class="txt-lh red">{{ $lang['invalid_input'] }}</span>

@endif

                                </div>
                            </div>

@endif


@if($seller_apply_info['apply_status'] == 2 || $seller_apply_info['apply_status'] == 3)

                            <div class="item">
                                <div class="label">{{ $lang['not_adopt_reason'] }}：</div>
                                <div class="value"><span class="txt-lh">{{ $seller_apply_info['reply_seller'] }}</span></div>
                            </div>
							<div class="item">
                                <div class="label">{{ $lang['Prompt'] }}：</div>
                                <div class="value">

@if($seller_apply_info['apply_status'] == 2)

                                	<span class="txt-lh red">{{ $lang['not_adopt_reason_Prompt']['0'] }}</span>

@elseif ($seller_apply_info['apply_status'] == 3)

                                    <span class="txt-lh red">{{ $lang['not_adopt_reason_Prompt']['1'] }}</span>

@endif

                                </div>
                            </div>

@endif

                        </div>
                    </div>

@endif


@if($seller_grade)

                    <div class="user-title">
                        <h2>{{ $lang['grade_info'] }}</h2>

@if($seller_apply_info['apply_status'] == 1)
<a href="user.php?act=merchants_upgrade" class="more">{{ $lang['back'] }}</a>
@endif

                    </div>
                    <div class="user-grade-warp">
                        <div class="user-items">
                            <div class="item">
                                <div class="label">{{ $lang['now_grade'] }}：</div>
                                <div class="value"><span class="txt-lh">{{ $seller_grade['grade_name'] }}</span></div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['in_time'] }}：</div>
                                <div class="value">
                                    <span class="txt-lh">{{ $seller_grade['addtime'] }}</span>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['end_time'] }}：</div>
                                <div class="value">
                                    <span class="txt-lh">{{ $seller_grade['end_time'] }}</span>
                                </div>
                            </div>

@if($seller_grade['refund_price'] > 0  || $seller_apply_info['refund_price'] > 0)

                            <div class="item">
                                <div class="label">{{ $lang['refund_grade'] }}：</div>
                                <div class="value">
                                    <span class="txt-lh total"><em>
@if($seller_apply_info['refund_price'])
{{ $seller_apply_info['format_refund_price'] }}
@else
{{ $seller_grade['format_refund_price'] }}
@endif
</em></span>
                                    <input type="hidden" value="
@if($seller_apply_info['refund_price'])
{{ $seller_apply_info['refund_price'] }}
@else
{{ $seller_grade['refund_price'] }}
@endif
" name='refund_price'/>
                                </div>
                            </div>

@endif

                        </div>
					</div>

@endif



@foreach($entry_criteriat_info as $info)

@if($info['child'])

                    <div class="user-title">
                        <h2>{{ $info['criteria_name'] }}</h2>
                    </div>
                    <div class="user-grade-warp">
                        <div class="user-items">

@foreach($info['child'] as $child)

                            <div class="item">
                                <div class="label"><i class="red">
@if($child['is_mandatory'] == 1)
*&nbsp;
@endif
</i>{{ $child['criteria_name'] }}：</div>

@if($child['type'] == 'text')

                                <div class="value">
                                    <input type="text" name='value[{{ $child['id'] }}]' class="text text-1" value="{{ $apply_criteria[$child['id']] }}"/>
                                    <div class="notic value[{{ $child['id'] }}]"></div>
                                </div>

@elseif ($child['type'] == 'select')

                                <div class="value">
                                    <div class="imitate_select">
                                        <div class="cite"><span>{{ $lang['please_select'] }}</span><i class="iconfont icon-down"></i></div>
                                        <ul>
                                            <li><a href="javascript:void(0);" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</a></li>

@foreach($child['option_value'] as $value)

                                            <li><a href="javascript:void(0);" data-value="{{ $value }}">{{ $value }}</a></li>

@endforeach

                                        </ul>
                                        <input name='value[{{ $child['id'] }}]' type="hidden" value="{{ $apply_criteria[$child['id']] }}">
                                    </div>
                                    <div class="notic value[{{ $child['id'] }}]"></div>
                                </div>

@elseif ($child['type'] == 'textarea')

                                <div class="value">
                                    <textarea class="textarea textarea2" name='value[{{ $child['id'] }}]'>{{ $apply_criteria[$child['id']] }}</textarea>
                                    <div class="notic value[{{ $child['id'] }}]"></div>
                                </div>

@elseif ($child['type'] == 'file')

                                <div class="value">
                                    <div class="value-file">
                                        <input class="btn fl" value="{{ $lang['Select_file'] }}" type="button">

@if($apply_criteria[$child['id']])

                                        <a href="{{ $apply_criteria[$child['id']] }}" class="nyroModal fl ml10 mt3"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $apply_criteria[$child['id']] }}>')" onmouseout="toolTip()"></i></a>

@endif

                                        <input name="" id="value[{{ $child['id'] }}]" class="txt fl" type="text">
                                        <input name="value[{{ $child['id'] }}]" class="file fl" id="fileField" size="28" onchange="document.getElementById('value[{{ $child['id'] }}]').value=this.value" type="file" value="{{ $apply_criteria[$child['id']] }}">
                                        <input type="hidden" value="{{ $child['id'] }}" name='file_id[]'>
										<input type="hidden" value="{{ $apply_criteria[$child['id']] }}" name='file_url[{{ $child['id'] }}]'/>
                                        <div class="notic value[{{ $child['id'] }}]"></div>
                                    </div>
                                </div>

@elseif ($child['type'] == 'charge' &&  $child['charge'] > 0)

                                <div class="value">
                                    <span class="total txt-lh red">{{ $child['format_charge'] }}/{{ $lang['year'] }}</span>
                                </div>

@endif

                            </div>

@endforeach

                        </div>
                    </div>

@endif


@endforeach



@if($entry_criteriat_charge['count_charge'] > 0)

                    <div class="user-title">
                        <h2>{{ $lang['information_count'] }}</h2>
                    </div>
                    <div class="user-grade-warp">
                        <div class="user-items">
                            <div class="item">
                                <div class="label">{{ $lang['settled_down'] }}：</div>
                                <div class="value">
                                    <div class="amount-warp mt5">
                                        <input class="buy-num" id="quantity" value="
@if($seller_apply_info['fee_num'])
{{ $seller_apply_info['fee_num'] }}
@else
1
@endif
" name="fee_num" defaultnumber="1" onkeyup="add_charge('keyup')">
                                        <div class="a-btn">
                                            <a href="javascript:void(0);" class="btn-add" onclick="add_charge('add')"><i class="iconfont icon-up"></i></a>
                                            <a href="javascript:void(0);" class="btn-reduce" onclick="add_charge('reduce')"><i class="iconfont icon-down"></i></a>
                                        </div>
                                        <input type="hidden" value="{{ $entry_criteriat_charge['count_charge'] }}" name='count_charge'>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['payment'] }}：</div>
                                <div class="value pay_id_erro">

@foreach($pay as $pay)


@if($pay['pay_id'] != 1)


@if($pay['pay_code'] != 'chunsejinrong' && $pay['pay_code'] != 'alipay_bank' && $pay['pay_code'] != 'onlinepay')

                                            <div class="radio-item">
                                                <input type="radio"
@if($seller_apply_info['pay_id'] == $pay['pay_id'])
checked
@else

@if($loop->index == 1)
checked
@endif

@endif
 name="pay_id" class="ui-radio" value="{{ $pay['pay_id'] }}" id="payment_{{ $pay['pay_id'] }}">
                                                <label for="payment_{{ $pay['pay_id'] }}" class="ui-radio-label">{{ $pay['pay_name'] }}</label>
                                            </div>

@endif

@endif

@endforeach

                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['label_total_user'] }}：</div>
                                <div class="value">
                                        <span class="total txt-lh red"><em id="count_charge">{{ $entry_criteriat_charge['format_count_charge'] }}</em></span>
                                </div>
                            </div>

@if($seller_apply_info['pay_online'] && $seller_apply_info['pay_status'] == 0 && $seller_apply_info['apply_status'] == 0)

                            <div class="item">
                                <div class="label">&nbsp;</div>
                                <div class="value">
                                    <span class="or-btn">{{ $seller_apply_info['pay_online'] }}</span>
                                </div>
                            </div>

@endif

                        </div>
                    </div>

@endif

                    <div class="user-grade-warp">
                        <div class="user-items">
                            <div class="item item-button">
                                <div class="label">&nbsp;</div>
                                <div class="value">
                                    <input type="hidden" name="all_count_charge"  value="{{ $seller_apply_info['total_amount'] }}"/>
                                    <input type="hidden" name="apply_id"  value="{{ $seller_apply_info['apply_id'] }}">
                                    <input type="hidden" name="apply_sn"  value="{{ $seller_apply_info['apply_sn'] }}">
                                    <input type="hidden" name="grade_id" value="{{ $grade_id }}"/>
                                    <input type="hidden" name="act"  value="{{ $act }}"/>
                                    <input type="hidden" name="no_cumulative_price"  value="{{ $entry_criteriat_charge['no_cumulative_price'] }}">

@if($seller_apply_info['pay_status'] == 0 && $seller_apply_info['apply_status'] == 0)

                                    <a href="javascript:void(0);" class="sc-btn sc-redBg-btn" ectype="submit">{{ $lang['submit_request'] }}</a>

@else

									<a href="user.php?act=merchants_upgrade_log" class="sc-btn sc-redBg-btn">{{ $lang['back'] }}</a>

@endif


									<input type="hidden" name="sc_guid"  value="{{ $sc_guid ?? 0 }}"/>
									<input type="hidden" name="sc_rand"  value="{{ $sc_rand ?? 0 }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @csrf </form>
        </div>

@endif



@if($action == 'confirm_inventory'  || $action == 'update_submit' || $action == 'grade_load')

		<div class="user-mod">
			<div class="user-title">
                <h2>{{ $lang['Settlement'] }}</h2>
            </div>
			<div class="invent-box">
				<div class="sku">{{ $lang['order_number'] }}：{{ $order['order_sn'] }}</div>
				<div class="extra">{{ $lang['Select_payment'] }}: <strong>{{ $payment['pay_name'] }}
@if($pay_fee > 0)
，{{ $lang['Fee_for_user'] }}：{{ $pay_fee }}
@endif
</strong>，{{ $lang['payment_amount_user'] }}：<strong>{{ $amount }}</strong></div>

@if($payment['pay_code'] != 'bank')

				<div class="or-btn">{{ $payment['pay_button'] }}</div>

@endif

			</div>
		</div>

@endif





@if($action == 'bonus')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['bonus'] }}</h2>
            </div>
            <div class="user-bonus-warp">
                <form action="" method="post">
                <div class="user-items">
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['bonus_card_number'] }}：</div>
                        <div class="value">
                            <input type="text" name="bonus_sn" class="text text-2 txt_input_cardnum">
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['bonus_pwd'] }}：</div>
                        <div class="value">
                            <input type="text" name="bonus_password" class="text text-2 txt_input_cardpw">
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['comment_captcha'] }}：</div>
                        <div class="value">
                            <div class="sm-input">
                                <input type="text" size="4" name="captcha" class="captcha_input">
                                <img src="captcha_verify.php?captcha=is_bonus&width=100&height=32&font_size=14&{{ $rand }}" onClick="this.src='captcha_verify.php?captcha=is_bonus&width=100&height=28&font_size=14&'+Math.random()"  name="captcha" class="captcha_img">
                            </div>
                        </div>
                    </div>
                    <div class="item item-button">
                        <div class="label"></div>
                        <div class="value">
                            <a href="javascript:void(0);" class="sc-btn sc-redBg-btn"  id="bind_btn" onclick="new_addBonus();">{{ $lang['Bind_current_account'] }}</a>
                        </div>
                    </div>
                </div>
                @csrf </form>
            </div>
            <div class="user-bonus-temp" ectype="tabSection">
                <div class="user-title">
                    <ul class="tabs" ectype="tabs">
                        <li id="bound_giftcard" class="active"><a href="javascript:void(0);">{{ $lang['keyong'] }}({{ $bonus['record_count'] }})</a></li>
                        <li id="upcoming_giftcard"><a href="javascript:void(0);">{{ $lang['About_expire'] }}({{ $bonus1['record_count'] }})</a></li>
                        <li id="over_giftcard"><a href="javascript:void(0);">{{ $lang['have_been_exhausted'] }}({{ $bonus2['record_count'] }})</a></li>
                        <li id="invalid_giftcard"><a href="javascript:void(0);">{{ $lang['overdue'] }}({{ $bonus3['record_count'] }})</a></li>
                    </ul>
                </div>
                <div class="user-bonus-info" ectype="tabContent">
                    <div id="gift_card_list_1">

@if($bonus['available_list'])

                        <div class="items">

@foreach($bonus['available_list'] as $item)

                            <div class="item
@if($loop->iteration % 3 == 0)
 item-last
@endif
">
                                <div class="b-price">{{ $item['type_money'] }}</div>
                                <div class="b-i-bot">
                                    <div class="storeName">{{ $item['shop_name'] }}</div>
                                    <p>{{ $lang['card_number_label'] }}：{{ $item['bonus_sn'] ?? N/A }} - {{ $lang['order_limit'] }}：{{ $item['min_goods_amount'] }}</p>
                                    <p>{{ $item['use_startdate'] }} ~ {{ $item['use_enddate'] }}</p>
                                </div>

@if($item['usebonus_type'])
<i class="i-soon">{{ $lang['general_audience'] }}</i>
@endif

                            </div>

@endforeach

                        </div>

@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info">
                                <h3>{{ $lang['no_bonus_keyong'] }}</h3>
                            </div>
                        </div>

@endif


@if($bonus['record_count'] > $size)
<div class="pages"><div class="pages-it">{{ $bonus['paper'] }}</div></div>
@endif

                    </div>
                    <div id="gift_card_list_2" style=" display:none">

@if($bonus1['expire_list'])

                        <div class="items">

@foreach($bonus1['expire_list'] as $item)

                        <div class="item
@if($loop->iteration % 3 == 0)
 item-last
@endif
">
                            <div class="b-price">{{ $item['type_money'] }}</div>
                            <div class="b-i-bot">
                                <div class="storeName">{{ $item['shop_name'] }}</div>
                                <p>{{ $lang['card_number_label'] }}：{{ $item['bonus_sn'] ?? N/A }} - {{ $lang['order_limit'] }}：{{ $item['min_goods_amount'] }}</p>
                                <p>{{ $item['use_startdate'] }} ~ {{ $item['use_enddate'] }}</p>
                            </div>
                            <i class="i-soon">{{ $lang['About_expire'] }}</i>
                        </div>

@endforeach

                        </div>

@else

                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info">
                            <h3>{{ $lang['no_bonus_daoqi'] }}</h3>
                        </div>
                    </div>

@endif

                    </div>
                    <div id="gift_card_list_3" style=" display:none">

@if($bonus2['useup_list'])

                        <div class="items">

@foreach($bonus2['useup_list'] as $item)

                        <div class="item item-disabled
@if($loop->iteration % 3 == 0)
 item-last
@endif
">
                            <div class="b-price">{{ $item['type_money'] }}</div>
                            <div class="b-i-bot">
                                <div class="storeName">{{ $item['shop_name'] }}</div>
                                <p>{{ $lang['card_number_label'] }}：{{ $item['bonus_sn'] ?? N/A }} - {{ $lang['order_limit'] }}：{{ $item['min_goods_amount'] }}</p>
                                <p>{{ $item['use_startdate'] }} ~ {{ $item['use_enddate'] }}</p>
                            </div>
                            <i class="i-soon">{{ $lang['had_use'] }}</i>
                        </div>

@endforeach

                        </div>

@else

                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info">
                            <h3>{{ $lang['no_bonus_end'] }}</h3>
                        </div>
                    </div>

@endif

                    </div>
                    <div id="gift_card_list_4" style=" display:none">

@if($bonus3['Invalid_list'])

                        <div class="items">

@foreach($bonus3['Invalid_list'] as $item)

                            <div class="item item-disabled
@if($loop->iteration % 3 == 0)
 item-last
@endif
">
                                <div class="b-price">{{ $item['type_money'] }}</div>
                                <div class="b-i-bot">
                                    <div class="storeName">{{ $item['shop_name'] }}</div>
                                    <p>{{ $lang['card_number_label'] }}：{{ $item['bonus_sn'] ?? N/A }} - {{ $lang['order_limit'] }}：{{ $item['min_goods_amount'] }}</p>
                                    <p>{{ $item['use_startdate'] }} ~ {{ $item['use_enddate'] }}</p>
                                </div>
                                <i class="i-soon">{{ $lang['overdue'] }}</i>
                            </div>

@endforeach

                        </div>

@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info">
                                <h3>{{ $lang['no_bonus_end'] }}</h3>
                            </div>
                        </div>

@endif

                    </div>
                </div>
            </div>
            <div class="user-prompt mt20">
                <div class="tit"><span>{{ $lang['bonus'] }}{{ $lang['reminder'] }}</span><i class="iconfont icon-down"></i></div>
                <div class="info">
                    {!! $lang['bonus_info'] !!}
                </div>
            </div>
        </div>

@endif





@if($action == 'value_card')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['bound_stored_card'] }}</h2>
            </div>
            <div class="value-card-warp">
                <div class="user-items">
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['stored_card_sn'] }}：</div>
                        <div class="value">
                            <input type="text" name="card_sn" class="text text-2 txt_input_cardnum">
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['stored_card_pwd'] }}：</div>
                        <div class="value">
                            <input type="text" name="card_password" class="text text-2 txt_input_cardpw">
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['comment_captcha'] }}：</div>
                        <div class="value">
                            <div class="sm-input">
                                <input type="text" name="captcha" class="captcha_input" size="4">
                                <img src="captcha_verify.php?captcha=is_value_card&width=100&height=32&font_size=14&{{ $rand }}" onClick="this.src='captcha_verify.php?captcha=is_value_card&width=100&height=28&font_size=14&'+Math.random()" class="captcha_img">
                            </div>
                        </div>
                    </div>
                    <div class="item item-button">
                        <div class="label"></div>
                        <div class="value">
                            <a href="javascript:void(0);" id="bind_btn" class="sc-btn sc-redBg-btn" ectype="submitVC">{{ $lang['Bind_current_account'] }}</a>
							<input type="hidden" name="user_id" id="user_id" value="{{ $user_id }}" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="my_cart_wrap">
                <div class="user-title">
                    <h2>{{ $lang['value_card_list'] }}</h2>
                </div>
                <div class="cart_header">
                    <ul>
                        <li><span>{{ $lang['use_card_money'] }}</span><span class="num_box"><span class="size_18 color_red">{{ $user_card['card_total'] }}</span>{{ $lang['element'] }}</span></li>
                        <li><span>{{ $lang['use_card_number'] }}</span><span class="num_box"><span class="size_18 color_red">{{ $user_card['use_card_count'] }}</span>{{ $lang['zhang'] }}</span></li>
                        <li><span>{{ $lang['have_been_exhausted']  }}</span><span class="num_box"><span class="size_18 color_red">{{ $user_card['use_card_null'] }}</span>{{ $lang['zhang'] }}</span></li>
                        <li><span>{{ $lang['have_invalid'] }}</span><span class="num_box"><span class="size_18 color_red">{{ $user_card['card_invalid_count'] }}</span>{{ $lang['zhang'] }}</span></li>
                        <li><span>{{ $lang['have_end_time'] }}</span><span class="num_box"><span class="size_18 color_red">{{ $user_card['card_end_time_count'] }}</span>{{ $lang['zhang'] }}</span></li>
                    </ul>
                </div>
                <div class="cart_body">
                    <div class="tabs">
                        <div class="tab @if($use_type == 1) current_tab @endif">
							<a href="user.php?act=value_card&use_type=1" class="@if($use_type == 1) red @endif">
							{{ $lang['use_card_number'] }}({{ $user_card['use_card_count'] }})
							</a>
						</div>
                        <div class="tab @if($use_type == 0) current_tab @endif">
							<a href="user.php?act=value_card&use_type=0" class="@if($use_type == 0) red @endif">
							{{ $lang['not_use_card_number'] }}({{ $user_card['not_use_card_count'] }})
							</a>
						</div>
                    </div>
                    <ul class="list_cart" @if($use_type == 0) style="display: none;" @endif>
                        @if ($user_card['use_card_page_list']['data'])
                        @foreach($user_card['use_card_page_list']['data'] as $key=>$row)
                        <li class="item_cart">
                            <div class="cart_h">
                                <div class="cart_sn">{{ $lang['card_number_label'] }}：{{ $row['value_card_sn'] }}</div>
                                <div class="cart_price"><span>{{ $lang['card_balance'] }}：</span><span class="money">{{ $row['card_money'] }}</span></div>
                            </div>
                            <div class="cart_b">
                                <div class="cart_info_item">
                                    <span>{{ $lang['Expiration_time'] }}：</span>
                                    <span>{{ $row['local_end_time'] }}</span>
                                </div>
                                <div class="cart_info_item">
                                    <span>{{ $lang['face_value_label'] }}：</span>
                                    <span>{{ $row['vc_value'] }}</span>
                                </div>
                                <div class="cart_info_item">
                                    <span>{{ $lang['discount_percent'] }}：</span>
                                    <span>{{ $row['vc_dis_format'] }}</span>
                                </div>
                                @if($row['is_rec'] == 1)
                                <a href="user.php?act=to_paid&vid={{ $row['vid'] }}" class="top_up cart_btn">{{ $lang['to_paid'] }}</a>
                                @endif
                                <a href="user.php?act=value_card_info&vid={{ $row['vid'] }}" class="view_log cart_btn">{{ $lang['look_user_log'] }}</a>
                            </div>
                        </li>
                            @endforeach
                            @else
                            <div class="no_records">
                                <i class="no_icon_two"></i>
                                <div class="no_info">
                                    <h3>{{ $lang['user']['value_card']['no_records'] }}</h3>
                                </div>
                            </div>
                            @endif
                    </ul>
                    <ul class="list_cart list_cart_disabled" @if($use_type == 1) style="display: none;" @endif>
                        @if ($user_card['not_use_card_page_list']['data'])
                            @foreach($user_card['not_use_card_page_list']['data'] as $key=>$row)
                        <li class="item_cart">
                            <div class="cart_h">
                                <div class="cart_sn">{{ $lang['card_number_label'] }}： {{ $row['value_card_sn'] }}</div>
                                <div class="cart_price"><span>{{ $lang['card_balance'] }}：</span><span class="money">{{ $row['card_money'] }}</span></div>
                            </div>
                            <div class="cart_b">
                                <div class="cart_info_item">
                                    <span>{{ $lang['Expiration_time'] }}：</span>
                                    <span>{{ $row['local_end_time'] }}</span>
                                </div>
                                <div class="cart_info_item">
                                    <span>{{ $lang['face_value_label'] }}：</span>
                                    <span>{{ $row['vc_value'] }}</span>
                                </div>
                                <div class="cart_info_item">
                                    <span>{{ $lang['discount_percent'] }}：</span>
                                    <span>{{ $row['vc_dis_format'] }}</span>
                                </div>
                                <a href="javascript:;" class="cart_btn">
                                    @if ($row['use_status'] == 3)
                                        {{ $lang['have_been_exhausted'] }}
                                        @elseif($row['use_status'] == 2)
                                        {{ $lang['have_end_time'] }}
                                        @else
                                        {{ $lang['have_invalid'] }}
                                        @endif
                                </a>
                                <a href="user.php?act=value_card_info&vid={{ $row['vid'] }}" class="view_log cart_btn">{{ $lang['look_user_log'] }}</a>
                            </div>
                        </li>
                            @endforeach
                        @else
                            <div class="no_records">
                                <i class="no_icon_two"></i>
                                <div class="no_info">
                                    <h3>{{ $lang['user']['value_card']['no_records'] }}</h3>
                                </div>
                            </div>
                        @endif
                    </ul>
                </div>

                <div class="cart_body">

                    <!-- 可用卡 -->
                    <div class="tc list_page_card" @if($use_type == 0) style="display: none;" @endif>
                        <div class="pages" id="pager">
                            @if(!empty($user_card['use_card_page_list']['prev_page_url']))
                            <div class="item prev"><a href="{{ $user_card['use_card_page_list']['prev_page_url'] }}"><i class="iconfont icon-left"></i></a></div>
                            @else
                                <div class="item prev"><a href="#none"><i class="iconfont icon-left"></i></a></div>
                            @endif

                            @for($i=1; $i <= $user_card['use_card_page_list']['last_page']; $i++)
								@if (($i < $user_card['use_card_page_list']['current_page'] && $i > ($user_card['use_card_page_list']['current_page'] - 4)) || ($user_card['use_card_page_list']['current_page'] == $i) || ($i > $user_card['use_card_page_list']['current_page'] && $i < ($user_card['use_card_page_list']['current_page'] + 4)))
								<div class="item @if ($user_card['use_card_page_list']['current_page'] == $i) cur @endif">
									@if ($user_card['use_card_page_list']['current_page'] == $i)
									<a href="#none">{{ $i }}</a>
									@else
									<a href="{{ $user_card['use_card_page_list']['path'] }}&page={{ $i }}">{{ $i }}</a>
									@endif
								</div>
								@endif
                            @endfor

                            @if(!empty($user_card['use_card_page_list']['next_page_url']))
								@if($user_card['use_card_page_list']['last_page'] > 4)
									<div class="item"><a href="#none">...</a></div>
								@endif
                            <div class="item next"><a href="{{ $user_card['use_card_page_list']['next_page_url'] }}"><i class="iconfont icon-right"></i></a></div>
                                @else
                                <div class="item next"><a href="#none"><i class="iconfont icon-right"></i></a></div>
                            @endif
                        </div>
                    </div>

                    <!-- 不可用卡 -->
                    <div class="tc list_page_card" @if($use_type == 1) style="display: none;" @endif>
                        <div class="pages" id="pager">
                            @if(!empty($user_card['not_use_card_page_list']['prev_page_url']))
                            <div class="item prev"><a href="{{ $user_card['not_use_card_page_list']['prev_page_url'] }}"><i class="iconfont icon-left"></i></a></div>
                            @else
                                <div class="item prev"><a href="#none"><i class="iconfont icon-left"></i></a></div>
                            @endif

                            @for($i=1; $i <= $user_card['not_use_card_page_list']['last_page']; $i++)
								@if (($i < $user_card['not_use_card_page_list']['current_page'] && $i > ($user_card['not_use_card_page_list']['current_page'] - 4)) || ($user_card['not_use_card_page_list']['current_page'] == $i) || ($i > $user_card['not_use_card_page_list']['current_page'] && $i < ($user_card['not_use_card_page_list']['current_page'] + 4)))
								<div class="item @if ($user_card['not_use_card_page_list']['current_page'] == $i) cur @endif">
									@if ($user_card['not_use_card_page_list']['current_page'] == $i)
									<a href="#none">{{ $i }}</a>
									@else
									<a href="{{ $user_card['not_use_card_page_list']['path'] }}&page={{ $i }}">{{ $i }}</a>
									@endif
								</div>
								@endif
                            @endfor

                            @if(!empty($user_card['not_use_card_page_list']['next_page_url']))
								@if($user_card['not_use_card_page_list']['last_page'] > 4)
									<div class="item"><a href="#none">...</a></div>
								@endif
                            <div class="item next"><a href="{{ $user_card['not_use_card_page_list']['next_page_url'] }}"><i class="iconfont icon-right"></i></a></div>
                                @else
                                <div class="item next"><a href="#none"><i class="iconfont icon-right"></i></a></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-prompt mt50">
                <div class="tit"><span>{{ $lang['value_card'] }}{{ $lang['reminder'] }}</span><i class="iconfont icon-down"></i></div>
                <div class="info">
                    <p>1、{{ $lang['card_desc']['one'] }}</p>
                    <p>2、{{ $lang['card_desc']['two'] }}</p>
                </div>
            </div>
        </div>

@endif





@if($action == 'to_paid')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['value_card_filling'] }}</h2>
            </div>
            <div class="value-card-warp">
                <div class="user-items">
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['rechargeable_card_sn'] }}：</div>
                        <div class="value">
                            <input type="text" name="pay_card_sn" class="text text-2 txt_input_cardnum2">
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['rechargeable_card_pwd'] }}：</div>
                        <div class="value">
                            <input type="text" name="pay_card_password" class="text text-2 txt_input_cardpw2">
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['comment_captcha'] }}：</div>
                        <div class="value">
                            <div class="sm-input">
                                <input type="text" name="captcha" class="captcha_input captcha_error2" size="4">
                                <img src="captcha_verify.php?captcha=is_pay_card&width=100&height=32&font_size=14&{{ $rand }}" onClick="this.src='captcha_verify.php?captcha=is_pay_card&width=100&height=28&font_size=14&'+Math.random()" class="captcha_img">
                            </div>
                        </div>
                    </div>
                    <div class="item item-button">
                        <div class="label"></div>
                        <div class="value">
                            <a href="javascript:void(0);" class="sc-btn sc-redBg-btn" ectype="submitVC">{{ $lang['recharge'] }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endif





@if($action == 'value_card_info')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['value_card_info'] }}</h2>
                <h3 class="value_warp" ectype="vcard_warp">
                    <span class="fl mr5">{!! $explain !!}</span>

@if($rz_shopNames)

                    <div class="vcard_info" ectype='value_see'>
                        <span class="red">({{ $lang['view'] }})</span>
                        <div class="value_shop" ectype='value_shop'>

@foreach($rz_shopNames as $list)

                              <a href="{{ $list['shop_url'] }}" target="_blank">{{ $list['shop_name'] }}</a>
@if(!$loop->last)
,
@endif


@endforeach

                        </div>
                    </div>

@endif

                </h3>
            </div>
            <table class="user-table user-table-valueCard">
                <colgroup>
                    <col width="200">
                    <col width="150">
                    <col width="150">
                    <col width="120">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th class="tc">{{ $lang['lab_card_id'] }}</th>
                        <th>{{ $lang['order_number'] }}</th>
                        <th>{{ $lang['Use_the_amount_of'] }}</th>
                        <th>{{ $lang['deposit_money'] }}</th>
                        <th>{{ $lang['use_time'] }}</th>
                    </tr>
                </thead>
                <tbody>

@if($value_card_info)


@foreach($value_card_info as $item)

                    <tr>
                        <td class="tc">{{ $item['rid'] }}</td>
                        <td class="tc">{{ $item['order_sn'] ?? N/A }}</td>
                        <td class="tc">{{ $item['use_val'] ?? N/A }}</td>
                        <td class="tc">{{ $item['add_val'] ?? N/A }}</td>
                        <td class="tc"><p class="date">{{ $item['record_time'] }}</p></td>
                    </tr>

@endforeach


@else

                    <tr><td colspan="5" class="td td_bf">{{ $lang['no_use_record'] }}</td></tr>

@endif

                </tbody>
            </table>
        </div>
        <script type="text/javascript">
            function specGoods(){
                var goods_ids = '{{ $goods_ids }}';
                location.href = "search.php?search_type=0&goods_ids="+goods_ids;
            }
        </script>

@endif





@if($action == 'account_bind')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['bind_login'] }}</h2>
            </div>
            <div class="account-bind-warp clearfix">

@if($insert['qq_install'] == 1)

                <div class="account-bind-item">
                    <i class="ab-icon ab-icon-qq"></i>
                    <span>{{ $lang['bind_qq'] }}</span>
                    <div class="b-btn">

@if($qq_info)

                            <a data-title="{{ $lang['Un_bind'] }}" data-id="{{ $qq_info['id'] }}" data-divid="qq_remove" data-dialog="oathdialog" href="javascript:void(0);" class="qq-remove sc-btn" data-username="{{ $info['username'] }}"  data-identity="{{ $info['username'] }}">{{ $lang['Un_bind'] }}</a>

@else

                            <a href="oauth?type=qq&user_id={{ $user_id }}
@if($back_act != '')
&back_url={{ $back_act }}
@endif
" class="sc-btn">{{ $lang['add_bind'] }}</a>

@endif

                    </div>
                    <div class="b-type"><i class="iconfont
@if($qq_info)
 icon-ok
@else
 icon-info-sign
@endif
"></i>
@if($qq_info)
{{ $lang['Bound'] }}
@else
{{ $lang['not_Bound'] }}
@endif
</div>
                </div>

@endif



@if($insert['weibo_install'] == 1)

                <div class="account-bind-item">
                    <i class="ab-icon ab-icon-weibo"></i>
                    <span>{{ $lang['weibo_one'] }}</span>
                    <div class="b-btn">

@if($weibo_info)

                    	<a data-title="{{ $lang['Un_bind'] }}" data-id="{{ $weibo_info['id'] }}" data-divid="weixin_remove" data-dialog="oathdialog" href="javascript:void(0);" class="weibo-remove sc-btn" data-username="{{ $info['username'] }}"  data-identity="{{ $info['username'] }}">{{ $lang['Un_bind'] }}</a>

@else

                    	<a href="oauth?type=weibo&user_id={{ $user_id }}
@if($back_act != '')
&back_url={{ $back_act }}
@endif
" class="sc-btn">{{ $lang['add_bind'] }}</a>

@endif

                    </div>
                    <div class="b-type"><i class="iconfont
@if($weibo_info)
 icon-ok
@else
 icon-info-sign
@endif
"></i>
@if($weibo_info)
{{ $lang['Bound'] }}
@else
{{ $lang['not_Bound'] }}
@endif
</div>
                </div>

@endif



@if($insert['wechat_install'] == 1)

                <div class="account-bind-item">
                    <i class="ab-icon ab-icon-wechat"></i>
                    <span>{{ $lang['weixin_one'] }}</span>
                    <div class="b-btn">

@if($weixin_info)

                    	<a data-title="{{ $lang['Un_bind'] }}" data-id="{{ $weixin_info['id'] }}" data-divid="weibo_remove" data-dialog="oathdialog" href="javascript:void(0);" class="weixin-remove sc-btn" data-username="{{ $info['username'] }}"  data-identity="{{ $info['username'] }}">{{ $lang['Un_bind'] }}</a>

@else

                    	<a href="oauth?type=weixin&user_id={{ $user_id }}
@if($back_act != '')
&back_url={{ $back_act }}
@endif
" class="sc-btn">{{ $lang['add_bind'] }}</a>

@endif

                    </div>
                    <div class="b-type"><i class="iconfont
@if($weixin_info)
 icon-ok
@else
 icon-info-sign
@endif
"></i>
@if($weixin_info)
{{ $lang['Bound'] }}
@else
{{ $lang['not_Bound'] }}
@endif
</div>
                </div>

@endif

            </div>
        </div>

@endif





@if($action == 'crowdfunding')

        <div class="user-mod" ectype="tabSection">
            <div class="user-title">
                <h2>{{ $lang['crowdfunding'] }}</h2>
                <ul class="tabs zc_btn" ectype="tabs">
                    <li class="active"><a href="javascript:void(0);">{{ $lang['I_support'] }}</a></li>
                    <li><a href="javascript:void(0);">{{ $lang['I_concerned'] }}</a></li>
                </ul>
            </div>
            <div class="crowdfunding-warp">
                <div class="crowdfunding-main zc_my_main" ectype="tabContent">
                    <div class="my_box">

@if($zc_support_list)

                        <div class="cdf-tab ui-tab">
                            <a href="javascript:void(0);" data-val="1" class="curr">{{ $lang['all_attribute'] }}</a>
                            <a href="javascript:void(0);" data-val="2">{{ $lang['Already_paid'] }}</a>
                            <a href="javascript:void(0);" data-val="3">{{ $lang['not_paid'] }}</a>
                        </div>

@endif

                        <div class="pay_item">

@if($zc_support_list)

                            <div class="crowdfunding-list">
                                <table class="user-table user-table-cdf">
                                    <colgroup>
                                        <col width="350">
                                        <col width="120">
                                        <col width="80">
                                        <col width="80">
                                        <col width="80">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>{{ $lang['Project_info'] }}</th>
                                            <th>{{ $lang['order_number'] }}</th>
                                            <th>{{ $lang['is_confirmed'] }}</th>
                                            <th>{{ $lang['zc_raise'] }}</th>
                                            <th>{{ $lang['residual_time'] }}</th>
                                            <th>{{ $lang['handle'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

@foreach($zc_support_list as $vo)

                                        <tr>
                                            <td>
                                                <div class="cdf-goods">
                                                    <div class="cdf-img"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}"><img src="{{ $vo['title_img'] }}"></a></div>
                                                    <div class="cdf-info">
                                                        <div class="cdf-name"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}">{{ $vo['title'] }}</a>
@if($vo['surplus_time'] != 0)
<span class="hand">{{ $lang['zc_in'] }}</span>
@else
<span>{{ $lang['has_ended'] }}</span>
@endif
</div>
                                                        <div class="cdf-lie"><span class="p-price">{{ $vo['format_goods_amount'] }}</span> x 1</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tc">{{ $vo['order_sn'] }}</td>
                                            <td class="tc">{{ $vo['complete'] }}%</td>
                                            <td class="tc">{{ $vo['format_join_money'] }}</td>
                                            <td class="tc">{{ $vo['surplus_time'] }} {{ $lang['day'] }}</td>
                                            <td class="tc">

@if($vo['surplus_time'] != 0 && $vo['pay_status'] == 0)

                                                    <a target="_blank" href="user_order.php?act=order_detail&order_id={{ $vo['order_id'] }}" class="sc-btn">{{ $lang['go_pay'] }}</a>

@elseif ($vo['surplus_time'] != 0 && $vo['pay_status'] == 1)

                                                    <p>{{ $lang['cs']['1'] }}</p>

@elseif ($vo['surplus_time'] != 0 && $vo['pay_status'] == 2)

                                                    <p>{{ $lang['ps']['2'] }}</p>

@elseif ($vo['surplus_time'] == 0 && $vo['pay_status'] == 2)

                                                    <p>{{ $lang['ps']['2'] }}</p>
                                                    <p>{{ $lang['has_ended'] }}</p>

@else

                                                    <p>{{ $lang['has_ended'] }}</p>

@endif



@if($vo['shipping_status'] == 0)

                                                <p>{{ $lang['ss']['0'] }}</p>

@elseif ($vo['shipping_status'] == 5)

                                                <p>{{ $lang['zc_ss'] }}</p>

@elseif ($vo['shipping_status'] == 1)

                                                <p>{{ $lang['ss']['1'] }}</p>
                                                <a target="_blank" class="sc-btn" href="user_order.php?act=affirm_received&order_id={{ $vo['order_id'] }}&action=crowdfunding" onclick="if (!confirm('{{ $lang['confirm_received'] }}')) return false;">{{ $lang['received'] }}</a>

@elseif ($vo['shipping_status'] == 2)

                                                <p>{{ $lang['Received_goods'] }}</p>

@endif

                                            </td>
                                        </tr>

@endforeach

                                    </tbody>
                                </table>
                            </div>

@else

                                <div class="no_records">
                                    <i class="no_icon_two"></i>
                                    <div class="no_info no_info_line">
                                        <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                                        <div class="no_btn"><a href="crowdfunding.php" class="sc-btn">{{ $lang['zc_home'] }}</a></div>
                                    </div>
                                </div>

@endif

                        </div>
                        <div class="pay_item" style="display: none;">

@if($zc_support_list_yes_pay)

                            <div class="crowdfunding-list">
                                <table class="user-table user-table-cdf">
                                    <colgroup>
                                        <col width="350">
                                        <col width="120">
                                        <col width="80">
                                        <col width="80">
                                        <col width="80">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>{{ $lang['Project_info'] }}</th>
                                            <th>{{ $lang['order_number'] }}</th>
                                            <th>{{ $lang['is_confirmed'] }}</th>
                                            <th>{{ $lang['zc_raise'] }}</th>
                                            <th>{{ $lang['residual_time'] }}</th>
                                            <th>{{ $lang['handle'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

@foreach($zc_support_list_yes_pay as $vo)

                                        <tr>
                                            <td>
                                                <div class="cdf-goods">
                                                    <div class="cdf-img"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}" target="_blank"><img src="{{ $vo['title_img'] }}"></a></div>
                                                    <div class="cdf-info">
                                                        <div class="cdf-name"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}" target="_blank">{{ $vo['title'] }}</a>
@if($vo['surplus_time'] != 0)
<span class="hand">{{ $lang['zc_in'] }}</span>
@else
<span>{{ $lang['has_ended'] }}</span>
@endif
</div>
                                                        <div class="cdf-lie"><span class="p-price">{{ $vo['format_goods_amount'] }}</span> x 1</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tc">{{ $vo['order_sn'] }}</td>
                                            <td class="tc">{{ $vo['complete'] }}%</td>
                                            <td class="tc">{{ $vo['format_join_money'] }}</td>
                                            <td class="tc">{{ $vo['surplus_time'] }} {{ $lang['day'] }}</td>
                                            <td class="tc">

@if($vo['surplus_time'] != 0 && $vo['pay_status'] == 0)

                                                    <a target="_blank" href="user_order.php?act=order_detail&order_id={{ $vo['order_id'] }}" class="sc-btn">{{ $lang['go_pay'] }}</a>

@elseif ($vo['surplus_time'] != 0 && $vo['pay_status'] == 1)

                                                    <p>{{ $lang['cs']['1'] }}</p>

@elseif ($vo['surplus_time'] != 0 && $vo['pay_status'] == 2)

                                                    <p>{{ $lang['ps']['2'] }}</p>

@elseif ($vo['surplus_time'] == 0 && $vo['pay_status'] == 2)

                                                    <p>{{ $lang['ps']['2'] }}</p>
                                                    <p>{{ $lang['has_ended'] }}</p>

@else

                                                    <p>{{ $lang['has_ended'] }}</p>

@endif


@if($vo['shipping_status'] == 0)

                                                <p>{{ $lang['ss']['0'] }}</p>

@elseif ($vo['shipping_status'] == 5)

                                                <p>{{ $lang['zc_ss'] }}</p>

@elseif ($vo['shipping_status'] == 1)

                                                <p>{{ $lang['ss']['1'] }}</p>
                                                <a target="_blank" class="sc-btn" href="user_order.php?act=affirm_received&order_id={{ $vo['order_id'] }}" onclick="if (!confirm('{{ $lang['confirm_received'] }}')) return false;">{{ $lang['received'] }}</a>

@elseif ($vo['shipping_status'] == 2)

                                                <p>{{ $lang['Received_goods'] }}</p>

@endif

                                            </td>
                                        </tr>

@endforeach

                                    </tbody>
                                </table>
                            </div>

@else

                                <div class="no_records">
                                    <i class="no_icon_two"></i>
                                    <div class="no_info no_info_line">
                                        <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                                        <div class="no_btn"><a href="crowdfunding.php" class="sc-btn">{{ $lang['zc_home'] }}</a></div>
                                    </div>
                                </div>

@endif

                        </div>
                        <div class="pay_item" style="display: none;">

@if($zc_support_list_no_pay)

                            <div class="crowdfunding-list">
                                <table class="user-table user-table-cdf">
                                    <colgroup>
                                        <col width="350">
                                        <col width="120">
                                        <col width="80">
                                        <col width="80">
                                        <col width="80">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>{{ $lang['Project_info'] }}</th>
                                            <th>{{ $lang['order_number'] }}</th>
                                            <th>{{ $lang['is_confirmed'] }}</th>
                                            <th>{{ $lang['zc_raise'] }}</th>
                                            <th>{{ $lang['residual_time'] }}</th>
                                            <th>{{ $lang['handle'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

@foreach($zc_support_list_no_pay as $vo)

                                        <tr>
                                            <td>
                                                <div class="cdf-goods">
                                                    <div class="cdf-img"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}" target="_blank"><img src="{{ $vo['title_img'] }}"></a></div>
                                                    <div class="cdf-info">
                                                        <div class="cdf-name"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}" target="_blank">{{ $vo['title'] }}</a>
@if($vo['surplus_time'] != 0)
<span class="hand">{{ $lang['zc_in'] }}</span>
@else
<span>{{ $lang['has_ended'] }}</span>
@endif
</div>
                                                        <div class="cdf-lie"><span class="p-price">{{ $vo['format_goods_amount'] }}</span> x 1</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tc">{{ $vo['order_sn'] }}</td>
                                            <td class="tc">{{ $vo['complete'] }}%</td>
                                            <td class="tc">{{ $vo['format_join_money'] }}</td>
                                            <td class="tc">{{ $vo['surplus_time'] }} {{ $lang['day'] }}</td>
                                            <td class="tc">

@if($vo['surplus_time'] != 0 && $vo['pay_status'] == 0)

                                                    <a target="_blank" href="user_order.php?act=order_detail&order_id={{ $vo['order_id'] }}" class="sc-btn">{{ $lang['go_pay'] }}</a>

@elseif ($vo['surplus_time'] != 0 && $vo['pay_status'] == 1)

                                                    <span>{{ $lang['cs']['1'] }}</span>

@elseif ($vo['surplus_time'] != 0 && $vo['pay_status'] == 2)

                                                    <span>{{ $lang['ps']['2'] }}</span>

@elseif ($vo['surplus_time'] == 0 && $vo['pay_status'] == 2)

                                                    <span>{{ $lang['ps']['2'] }}</span><br/>
                                                    <span>{{ $lang['has_ended'] }}</span>

@else

                                                    <span>{{ $lang['has_ended'] }}</span>

@endif


@if($vo['shipping_status'] == 0)

                                                <span>{{ $lang['ss']['0'] }}</span>

@elseif ($vo['shipping_status'] == 5)

                                                <span>{{ $lang['zc_ss'] }}</span>

@elseif ($vo['shipping_status'] == 1)

                                                <span>{{ $lang['ss']['1'] }}</span>
                                                <a target="_blank" class="sc-btn" href="user_order.php?act=affirm_received&order_id={{ $vo['order_id'] }}" onclick="if (!confirm('{{ $lang['confirm_received'] }}')) return false;">{{ $lang['received'] }}</a>

@elseif ($vo['shipping_status'] == 2)

                                                <span>{{ $lang['Received_goods'] }}</span>

@endif

                                            </td>
                                        </tr>

@endforeach

                                    </tbody>
                                </table>
                            </div>

@else

                            <div class="no_records">
                                <i class="no_icon_two"></i>
                                <div class="no_info no_info_line">
                                    <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                                    <div class="no_btn"><a href="crowdfunding.php" class="sc-btn">{{ $lang['zc_home'] }}</a></div>
                                </div>
                            </div>

@endif

                        </div>
                    </div>
                    <div class="my_box" style="display:none;">

@if(!empty($zc_focus_list[0]))

                            <div class="crowdfunding-list">
                                <table class="user-table user-table-cdf">
                                    <colgroup>
                                        <col width="350">
                                        <col width="100">
                                        <col width="100">
                                        <col width="100">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>{{ $lang['Project_info'] }}</th>
                                            <th>{{ $lang['zc_number'] }}</th>
                                            <th>{{ $lang['gz_number'] }}</th>
                                            <th>{{ $lang['handle'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

@foreach($zc_focus_list as $vo)

                                        <tr>
                                            <td>
                                                <div class="cdf-goods">
                                                    <div class="cdf-img"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}" target="_blank"><img src="{{ $vo['title_img'] }}"></a></div>
                                                    <div class="cdf-info">
                                                        <div class="cdf-name"><a href="crowdfunding.php?act=detail&id={{ $vo['id'] }}" target="_blank">{{ $vo['title'] }}</a>
@if($vo['surplus_time'] != 0)
<span class="hand">{{ $lang['zc_in'] }}</span>
@else
<span>{{ $lang['has_ended'] }}</span>
@endif
</div>
                                                        <div class="cdf-lie"><span class="p-price">{{ $vo['format_join_money'] }}</span> x 1</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tc">{{ $vo['zhichi_num'] ?? 0 }}</td>
                                            <td class="tc">{{ $vo['focus_num'] ?? 0 }}</td>
                                            <td class="tc">

@if($vo['surplus_time'] > 0)

                                               <a target="_blank" class="sc-btn" href="crowdfunding.php?act=detail&id={{ $vo['id'] }}">{{ $lang['To_support'] }}</a>
                                               <br/>
                                               <a href="javascript:void(0);" class="sc-btn" data-dialog="zc_focus_dialog" data-divid="zc_focus_dialog" data-url="user_crowdfund.php?act=delete_zc_focus&rec_id={{ $vo['id'] }}">{{ $lang['no_attention'] }}</a>

@else

                                               <span>{{ $lang['has_ended'] }}</span>

@endif

                                            </td>
                                        </tr>

@endforeach

                                    </tbody>
                                </table>
                            </div>

@else

                            <div class="no_records">
                                <i class="no_icon_two"></i>
                                <div class="no_info no_info_line">
                                    <h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3>
                                    <div class="no_btn"><a href="crowdfunding.php" class="sc-btn">{{ $lang['zc_home'] }}</a></div>
                                </div>
                            </div>

@endif

                    </div>
                </div>
            </div>
        </div>

@endif





@if($action == 'wholesale_buy')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['my_purchase_order'] }}</h2>
            </div>
            <div class="user-list-title clearfix">
                <div class="user-list-filter">
                    <div class="imitate_select w120 mr10" id="wholesale_order_status">
                        <div class="cite"><span>{{ $lang['all_status'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li id="wholesale_order_status_-1"><a href="javascript:void(0);" data-idtxt="wholesale_status_list" data-action="{{ $action }}" data-type="order_status" data-id="-1" data-value='-1'>{{ $lang['all_status'] }}</a></li>
                            <li id="wholesale_order_status_0"><a href="javascript:void(0);" data-idtxt="wholesale_status_list" data-action="{{ $action }}" data-type="order_status" data-id="0" data-value='0'>{{ $lang['no_confirmed'] }}</a></li>
                            <li id="wholesale_order_status_1"><a href="javascript:void(0);" data-idtxt="wholesale_status_list" data-action="{{ $action }}" data-type="order_status" data-id="1" data-value='1'>{{ $lang['is_confirmed'] }}</a></li>
                        </ul>
                        <input name="wholesale_order_status_list" type="hidden" value="-1" id="wholesale_order_status_val">
                    </div>
                    <div class="imitate_select w120" id="DateTime">
                        <div class="cite"><span>{{ $lang['all_time'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li id="time_allDate"><a href="javascript:void(0);" data-idtxt="wholesale_submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="allDate" data-value="allDate">{{ $lang['all_time'] }}</a></li>
                            <li id="time_today"><a href="javascript:void(0);" data-idtxt="wholesale_submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="today" data-value="today">{{ $lang['Today'] }}</a></li>
                            <li id="time_three_today"><a href="javascript:void(0);" data-idtxt="wholesale_submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="three_today" data-value="three_today">{{ $lang['three_today'] }}</a></li>
                            <li id="time_aweek"><a href="javascript:void(0);" data-idtxt="wholesale_submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="aweek" data-value="aweek">{{ $lang['aweek'] }}</a></li>
                            <li id="time_thismonth"><a href="javascript:void(0);" data-idtxt="wholesale_submitDate" data-action="{{ $action }}" data-type="dateTime" data-id="thismonth" data-value="thismonth">{{ $lang['thismonth'] }}</a></li>
                        </ul>
                        <input name="wholesale_order_submitDate" type="hidden" value="allDate" id="wholesale_dateTime_val">
                    </div>
                </div>
                <div class="user-list-search">
                    <input type="text" id="ip_keyword" class="text" onkeydown="javascript:if(event.keyCode==13) get_WholesaleOrderSearch('ip_keyword', 'order_list', 'text');" onblur="if (this.value=='') this.value=this.defaultValue; this.style.color='#999'" onfocus="if (this.value==this.defaultValue)this.value='';this.style.color='#666'" placeholder="{{ $lang['search_oreder_user'] }}" name="" style="color: rgb(153, 153, 153);">
                    <button type="button" onclick="get_WholesaleOrderSearch('ip_keyword', 'order_list', 'text')"><i class="iconfont icon-search"></i></button>
                </div>
            </div>
            <div id="user_order_list">
            @include('frontend::library/user_wholesale_order_list')
            </div>
        </div>
        <script type="text/javascript">
            //删除
            function delete_wholesale_order(order_id){
                var order = new Object();
                order.order_id   = order_id;
				pbDialog("{{ $lang['is_confirmed_who_buy'] }}","",0,"","","",true,function(){
					Ajax.call('user_wholesale.php?act=delete_wholesale_order', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
				});
            }
            //查询
            function get_WholesaleOrderSearch(idTxt, action, type, t, c){

                var keyword, status_keyword, date_keyword;
                var order = new Object();
                if(t){
                    keyword = $(t).data('id');

                    if(idTxt == 'wholesale_status_list'){
                        $("input[name='wholesale_order_status_list']").val(keyword);
                    }else if(idTxt == 'wholesale_submitDate'){
                        $("input[name='wholesale_order_submitDate']").val(keyword);
                    }

                }else{
                    keyword = document.getElementById(idTxt).value;
                }

                //by sun
                if(c){
                    $(c).addClass("active").siblings().removeClass("active");
                }
                //by sun end

                if(idTxt == 'submitDate'){
                    var status_keyword = $("input[name='wholesale_order_status_list']").val();
                    order.status_keyword   = status_keyword;
                }else if(idTxt == 'status_list'){
                    var date_keyword = $("input[name='wholesale_order_submitDate']").val();
                    order.date_keyword   = date_keyword;
                }

                order.idTxt   = idTxt;
                order.keyword   = keyword;
                order.action   = action;
                order.type   = type;

                Ajax.call('user_wholesale.php?act=wholesale_order_to_query', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
            }

            function orderResponse(result){
                if(result.error == 0){
                    $("#user_order_list").html(result.content);
                }

				$(".user-purchase .item").each(function(){
					var height_l = $(this).find(".itemc-left").height();
					var height_r = $(this).find(".itemc-right").height();

					if(height_l < height_r){
						$(this).find(".itemc-right").addClass("borderLeft");
					}else if(height_l > height_r){
						$(this).find(".itemc-left").addClass("borderRight");
					}else{
						$(this).find(".itemc-left").addClass("borderRight");
					}
				});
            }
        </script>

@endif



@if($action == 'wholesale_purchase')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['want_buy_order'] }}</h2>
                <ul class="tabs" ectype="review_status">
                    <li
@if(request()->get('review_status') == '')
class="active"
@endif
 data-value=""><a href="javascript:void(0);">{{ $lang['all_status'] }}({{ $review_status_array['-1'] }})</a></li>
                    <li
@if(request()->get('review_status') == '1')
class="active"
@endif
 data-value="1"><a href="javascript:void(0);">{{ $lang['is_confirm']['1'] }}({{ $review_status_array['1'] }})</a></li>
                    <li
@if(request()->get('review_status') == '2')
class="active"
@endif
 data-value="2"><a href="javascript:void(0);">{{ $lang['is_confirm']['2'] }}({{ $review_status_array['2'] }})</a></li>
                    <li
@if(request()->get('review_status') == '0')
class="active"
@endif
 data-value="0"><a href="javascript:void(0);">{{ $lang['is_confirm']['0'] }}({{ $review_status_array['0'] }})</a></li>
                </ul>
            </div>
            <div class="user-list-title clearfix">
                <div class="user-list-filter user-wantbut-filter">
                    <div class="text_time" id="text_time1">
                        <input type="text" class="text mr0" name="start_date" id="start_date" value="{{ request()->get('start_date') }}" placeholder="{{ $lang['select_date'] }}" autocomplete="off" readonly>
                    </div>
                    <span class="bolang">&nbsp;&nbsp;~&nbsp;&nbsp;</span>
                    <div class="text_time" id="text_time2">
                        <input type="text" class="text mr0" name="end_date" id="end_date" value="{{ request()->get('end_date') }}" placeholder="{{ $lang['select_date'] }}" autocomplete="off" readonly>
                    </div>
                </div>
                <div class="user-list-search user-list-search-wantbut">
                    <input type="hidden" name="review_status" value="{{ request()->get('review_status') }}">
                    <input type="text" id="ip_keyword" class="text" placeholder="{{ $lang['wholesale_purchase_placeholder'] }}" name="keyword" value="{{ request()->get('keyword') }}" onkeydown="javascript:if(event.keyCode==13) purchase_search();" style="color: rgb(153, 153, 153);">
                    <button type="button" onclick="purchase_search()">{{ $lang['search'] }}</button>
                </div>
            </div>
            <div class="user-wantbut-list">
                <table class="user-table">
                    <colgroup>
                        <col width="300">
                        <col width="300">
                        <col width="140">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>{{ $lang['want_to_buy_goods'] }}</th>
                            <th>{{ $lang['want_to_buy_time'] }}</th>
                            <th>{{ $lang['adopt_status'] }}</th>
                            <th>{{ $lang['handle'] }}</th>
                        </tr>
                    </thead>
                    <tbody>

@forelse($purchase_list as $purchase)

                        <tr>
                            <td>
                                <div class="p-goods">
                                    <div class="p-img"><a href="wholesale_purchase.php?act=info&purchase_id={{ $purchase['purchase_id'] }}" target="_blank"><img src="
@if($purchase['img'])
{{ $purchase['img'] }}
@else
{{ skin('/images/brand_defalut.jpg') }}
@endif
"></a></div>
                                    <div class="p-info">
                                        <div class="p-name"><a href="wholesale_purchase.php?act=info&purchase_id={{ $purchase['purchase_id'] }}" title="{{ $purchase['subject'] }}" target="_blank">{{ $purchase['subject'] }}</a></div>
                                        <div class="p-num">{{ $lang['want_to_buy_num'] }}：{{ $purchase['goods_number'] }}{{ $lang['jian'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="p-time">
                                    <p>{{ $purchase['add_time_complete'] }} 〜 {{ $purchase['end_time_complete'] }}</p>
                                    <p>{{ $lang['residual'] }}{{ $purchase['left_day'] }}{{ $lang['day'] }}</p>
                                </div>
                            </td>
                            <td>
                                <div class="p-state">
                                    <h3 class="ftx-07">{{ $lang['review_status'][$purchase['review_status']] }}</h3>
                                </div>
                            </td>
                            <td>
                                <a href="user_wholesale.php?act=purchase_info&purchase_id={{ $purchase['purchase_id'] }}" class="sc-btn">{{ $lang['view'] }}</a>
                                <a href="user_wholesale.php?act=purchase_delete&purchase_id={{ $purchase['purchase_id'] }}" class="sc-btn">{{ $lang['drop'] }}</a>
                            </td>
                        </tr>

@empty

                        <tr><td colspan="4">{{ $lang['wholesale_purchase_null'] }}</td></tr>

@endforelse

                    </tbody>
                </table>
                @include('frontend::library/pages')
            </div>
        </div>
        <script type="text/javascript">
        $("[ectype='review_status'] li").click(function(){
			var review_status = $(this).data('value');
			$("input[name='review_status']").val(review_status);
			purchase_search();
        })

        function purchase_search(){
			query_string = "";
			var start_date = $("input[name='start_date']").val();
			var end_date = $("input[name='end_date']").val();
			var keyword = $("input[name='keyword']").val();
			var review_status = $("input[name='review_status']").val();
			if(start_date){query_string += "&start_date="+start_date}
			if(end_date){query_string += "&end_date="+end_date}
			if(keyword){query_string += "&keyword="+keyword}
			if(review_status){query_string += "&review_status="+review_status}
			location.href = 'user_wholesale.php?act=wholesale_purchase'+query_string;
        }
        </script>

@endif



@if($action == 'purchase_info')

        <div class="user-mod">
            <form name="formEdit" action="user_wholesale.php" method="post">
            	<div class="user-title">
                    <h2>{{ $lang['want_buy_order_desc'] }}</h2>
                </div>
                <div class="user-wantbuy-content">
                    <div class="title">
                    	<h3>{{ $lang['review_status'][$purchase_info['review_status']] }}－{{ $lang['purchase_status'][$purchase_info['status']] }}</h3>
                        <span>{{ $lang['wholesale_purchase_residual'] }}<em>{{ $purchase_info['left_day'] }}</em>{{ $lang['day'] }}</span>
                    </div>
                    <div class="user-wantbuy-table">
                    	<div class="tit">{{ $purchase_info['subject'] }}<em>{{ $lang['purchase_type'][$purchase_info['type']] }}</em></div>
                        <table class="user-table">
                            <colgroup>
                                <col width="180">
                                <col width="150">
                                <col width="100">
                                <col width="100">
                                <col>
                            </colgroup>
                            <thead>
                            	<tr>
                                    <th>{{ $lang['purchase_name'] }}</th>
                                    <th>{{ $lang['purchase_cat'] }}</th>
                                    <th>{{ $lang['purchase_num'] }}</th>
                                    <th>{{ $lang['purchase_price'] }}</th>
                                    <th>{{ $lang['purchase_remarks'] }}</th>
                                </tr>
                            </thead>
                            <tbody>

@foreach($purchase_info['goods_list'] as $goods)

                            	<tr>
                                    <td>{{ $goods['goods_name'] }}</td>
                                    <td><div class="tDiv">{{ $goods['cat_name'] }}</div></td>
                                    <td>{{ $goods['goods_number'] }}{{ $lang['jian'] }}</td>
                                    <td>{{ $goods['goods_price'] }}{{ $lang['yuan'] }}</td>
                                    <td>
                                    	<div class="t-desc"><span>{{ $goods['remarks'] }}</span></div>
                                        <div class="t-images">
                                            <div class="t-images-info">
                                                <ul>

@foreach($goods['goods_img'] as $img)

                                                    <li><img src="{{ $img }}"><a href="{{ $img }}" class="nyroModal"><i class="iconfont icon-search"></i></a></li>

@endforeach

                                                </ul>
                                            </div>
                                            <a href="javascript:void(0);" class="prev"><i class="iconfont icon-left"></i></a>
                                            <a href="javascript:void(0);" class="next"><i class="iconfont icon-right"></i></a>
                                        </div>
                                    </td>
                                </tr>

@endforeach

                            </tbody>
                            <tfoot>
                            	<tr>
                                    <td colspan="5">
                                    	<div class="tfoot-left">
                                            <div class="lie">
                                            	<div class="label">{{ $lang['contact_username'] }}：</div>
                                                <div class="value">{{ $purchase_info['contact_name'] }} {{ $lang['contact_gender'][$purchase_info['contact_gender']] }}</div>
                                            </div>
                                            <div class="lie">
                                            	<div class="label">{{ $lang['contact_phone'] }}：</div>
                                                <div class="value">{{ $purchase_info['contact_phone'] }}</div>
                                            </div>
                                            <div class="lie">
                                            	<div class="label">{{ $lang['email_reset'] }}：</div>
                                                <div class="value">{{ $purchase_info['contact_email'] }}</div>
                                            </div>
                                        </div>
                                        <div class="tfoot-right">
                                            <div class="lie">
                                            	<div class="label">{{ $lang['Invoice_information'] }}：</div>
                                                <div class="value">{{ $lang['need_invoice'][$purchase_info['need_invoice']] }}
@if($purchase_info['invoice_tax_rate'])
{{ $lang['tax_rate'] }}{{ $purchase_info['invoice_tax_rate'] }}
@endif
</div>
                                            </div>
                                            <div class="lie">
                                            	<div class="label">{{ $lang['label_address'] }}：</div>
                                                <div class="value">{{ $purchase_info['consignee_region'] }} {{ $purchase_info['consignee_address'] }}</div>
                                            </div>
                                            <div class="lie">
                                            	<div class="label">{{ $lang['label_supplement_explain'] }}：</div>
                                                <div class="value">{{ $purchase_info['description'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="user-title">
                    <h2>{{ $lang['supplier_info'] }}</h2>
                </div>
                <div class="user-supplier-info">
                    <div class="user-items">

@if($purchase_info['status'] != 1)

                    	<div class="item">
                            <div class="label">{{ $lang['corporate_name'] }}：</div>
                            <div class="value"><input type="text" name="supplier_company_name" value="{{ $purchase_info['supplier_company_name'] }}" class="text"></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['contact_phone'] }}：</div>
                            <div class="value"><input type="text" name="supplier_contact_phone" value="{{ $purchase_info['supplier_contact_phone'] }}" class="text"></div>
                        </div>

@else

						<div class="item">
                            <div class="label">{{ $lang['corporate_name'] }}：</div>
                            <div class="value"><span class="lh30">{{ $purchase_info['supplier_company_name'] }}</span></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['contact_phone'] }}：</div>
                            <div class="value"><span class="lh30">{{ $purchase_info['supplier_contact_phone'] }}</span></div>
                        </div>

@endif


@if($purchase_info['status'] != 1)

                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input type="hidden" name="purchase_id" value="{{ $purchase_info['purchase_id'] }}">
                                <input type="hidden" name="act" value="purchase_edit">
                            	<input type="submit" class="sc-btn sc-redBg-btn" name="submit" value="{{ $lang['purchase_order'] }}">
                            </div>
                        </div>

@endif

                    </div>
                </div>
            @csrf </form>
        </div>

@endif





@if($action == 'apply_suppliers')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['supplier_entry'] }}</h2>
            </div>
            <div class="account-main account-bind">
                <div class="user-items">

@if(!$is_applied)

                    <form name="apply_suppliers" method="post" action="user.php" enctype="multipart/form-data" class="user-form user-form-safe">
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['Real_name'] }}：</div>
                            <div class="value">
                                <input class="text text-1" type="text" id="real_name" name="real_name" value="{{ $supplier['real_name'] }}">
                                <div id="span-notify-holderName" class="notic">{{ $lang['Real_name_notice'] }}</div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['number_ID'] }}：</div>
                            <div class="value">
                                <input class="text text-5" type="text" id="self_num" name="self_num" value="{{ $supplier['self_num'] }}">
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['corporate_name'] }}：</div>
                            <div class="value">
                                <input class="text text-1" type="text" id="company_name" name="company_name" value="{{ $supplier['company_name'] }}">
                                <div class="notic"></div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['company_address'] }}：</div>
                            <div class="value">
                                <input class="text text-5" type="text" id="company_address" value="{{ $supplier['company_address'] }}" name="company_address" ectype="company_address">
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['select_region'] }}：</div>
                            <div class="value">
                                <dl class="mod-select mod-select-small fl" ectype="smartdropdown">
                                    <dt>
                                        <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
                                        <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                                    </dt>
                                    <dd ectype="layer">

@foreach($country_list as $country)

                                        <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <dl class="mod-select mod-select-small fl" ectype="smartdropdown">
                                    <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span><input type="hidden" value="{{ $consignee['province'] }}" ectype="ragionItem" name="province"></dt>
                                    <dd ectype="layer">
                                        <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                                        <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <dl class="mod-select mod-select-small fl" ectype="smartdropdown">
                                    <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span><input type="hidden" value="{{ $consignee['city'] }}" name="city" ></dt>
                                    <dd ectype="layer">
                                        <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                                        <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['cat_name'] }}：</div>
                            <div class="value">
                                <div id="first_cate" class="imitate_select w200 mr10">
                                    <div class="cite"><span>{{ $lang['please_select'] }}</span><i class="iconfont icon-down"></i></div>
                                    <ul>

@foreach($first_cate as $cate)

                                        <li><a href="javascript:void(0);" data-value="{{ $cate['cat_id'] }}">{{ $cate['cat_name'] }}</a></li>

@endforeach

                                    </ul>
                                    <input type="hidden" name="first_cate" value="0" id="first_cate_val"/>
                                </div>
                                <div class="category_checkbox_list">
                                    <div class="checkbox_items" id="steps_re_span">
                                    </div>
                                    <div class="m-category-handle">
                                        <div class="checkbox_item" style="width:auto;">
                                            <input type="checkbox" name="all_list" class="ui-checkbox" id="allList" />
                                            <label for="allList" class="ui-label">{{ $lang['check_all_back'] }}</label>
                                        </div>
                                        <a href="javascript:void(0);" class="btn btn30 btn_blue" onclick="selectChildCate_cheked()" >{{ $lang['add'] }}</a>
                                    </div>
                                </div>
                                <div class="list-div" id="detailCategoryTable">
                                    <table class="m-table mt20" >
                                        <thead>
                                            <tr>
                                                <th width="10%">{{ $lang['snumber'] }}</th>
                                                <th width="35%">{{ $langone_level_directorysnumber }}</th>
                                                <th width="35%">{{ $lang['two_level_directory'] }}</th>
                                                <th width="20%">{{ $lang['handler'] }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

@forelse($category_info as $k => $category)

                                            <tr>
                                                <td align="center">
                                                    <p>
                                                        <span class="index">{{ $k }}</span>
                                                        <input type="hidden" value="{{ $category['cat_id'] }}" name="cat_id[]" class="cId">
                                                    </p>
                                                </td>
                                                <td align="center" >
                                                    <p>
                                                        <input type="hidden" value="{{ $category['parent_name'] }}" name="parent_name[]" class="cl1Name">
                                                        {{ $category['parent_name'] }}
                                                    </p>
                                                </td>
                                                <td align="center">
                                                    <p>
                                                        <input type="hidden" value="{{ $category['cat_name'] }}" name="cat_name[]" class="cl2Name">
                                                        {{ $category['cat_name'] }}
                                                    </p>
                                                </td>
                                                <td><div class="tDiv"><a class="btn_trash" href="javascript:void(0);" onClick="deleteChildCate({{ $category['ct_id'] }})"><i class="icon icon-trash"></i>{{ $lang['drop'] }}</a></div></td>
                                            </tr>

@empty

                                            <tr><td colspan='4'>{{ $lang['temporary_not_cat'] }}</td></tr>

@endforelse

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['front_of_id_card'] }}：</div>
                            <div class="value">
                                <div class="type-file-box">
                                    <input type="button" name="button" id="button" class="type-file-button">
                                    <input type="file" name="front_of_id_card" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($supplier['front_of_id_card'])
<span class="show"><a href="{{ $supplier['front_of_id_card'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $supplier['front_of_id_card'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                    <input type="text" name="textfile_zheng" class="type-file-text" id="textfile_zheng" value="{{ $supplier['front_of_id_card'] }}" readonly>
                                </div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['reverse_of_id_card'] }}：</div>
                            <div class="value">
                                <div class="type-file-box">
                                    <input type="button" name="button" id="button" class="type-file-button">
                                    <input type="file" name="reverse_of_id_card" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($supplier['reverse_of_id_card'])
<span class="show"><a href="{{ $supplier['reverse_of_id_card'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $supplier['reverse_of_id_card'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                    <input type="text" name="textfile_fan" class="type-file-text" id="textfile_fan" value="{{ $supplier['reverse_of_id_card'] }}" readonly>
                                </div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['business_license'] }}：</div>
                            <div class="value">
                                <div class="type-file-box">
                                    <input type="button" name="button" id="button" class="type-file-button">
                                    <input type="file" name="license_fileImg" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($supplier['license_fileImg'])
<span class="show"><a href="{{ $supplier['license_fileImg'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $supplier['license_fileImg'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                    <input type="text" name="textfile_zheng" class="type-file-text" id="textfile_zheng" value="{{ $supplier['license_fileImg'] }}" readonly>
                                </div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['institutional_code_certificate'] }}：</div>
                            <div class="value">
                                <div class="type-file-box">
                                    <input type="button" name="button" id="button" class="type-file-button">
                                    <input type="file" name="organization_fileImg" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($supplier['organization_fileImg'])
<span class="show"><a href="{{ $supplier['organization_fileImg'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $supplier['organization_fileImg'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                    <input type="text" name="textfile_zheng" class="type-file-text" id="textfile_zheng" value="{{ $supplier['organization_fileImg'] }}" readonly>
                                </div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['account_opening_permit'] }}：</div>
                            <div class="value">
                                <div class="type-file-box">
                                    <input type="button" name="button" id="button" class="type-file-button">
                                    <input type="file" name="linked_bank_fileImg" class="type-file-file" size="30" data-state="imgfile" hidefocus="true" value="">

@if($supplier['linked_bank_fileImg'])
<span class="show"><a href="{{ $supplier['linked_bank_fileImg'] }}" class="nyroModal"><i class="iconfont icon-picture" onmouseover="toolTip('<img src={{ $supplier['linked_bank_fileImg'] }}>')" onmouseout="toolTip()"></i></a></span>
@endif

                                    <input type="text" name="textfile_zheng" class="type-file-text" id="textfile_zheng" value="{{ $supplier['linked_bank_fileImg'] }}" readonly>
                                </div>
                                <div class="form_prompt"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em class="red">*</em>{{ $lang['label_mobile'] }}：</div>
                            <div class="value">
                                <input class="text text-1" type="text" id="mobile_phone" name="mobile_phone" value="{{ $supplier['bank_mobile'] }}">
                                <div class="form_prompt"></div>
                            </div>
                        </div>

@if($enabled_sms_signin)

                        <div class="item">
                            <div class="label">{{ $lang['bindMobile_code'] }}：</div>
                            <div class="value">
                                <div class="sm-input">
                                    <input name="sms_value" id="sms_value" type="hidden" value="sms_code" />
                                    <input type="text" name="mobile_code" tabindex="1" id="mobile_code" disabled="disabled">
                                    <a href="javascript:sendSms()" id="zphone" class="sms-btn">{{ $lang['getMobile_code'] }}</a>
                                </div>
                                <div class="form_prompt"id="phone_notice"></div>
                            </div>
                        </div>

@endif

                        <div class="item item-button">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input id="flag" type="hidden" value="change_password_f" name="flag">
                                <input type="hidden" value="supplier_info" name="act">
                                <input type="button" id="authSubmit" class="sc-btn sc-redBg-btn" value="{{ $lang['submit_goods'] }}">
                            </div>
                        </div>
                    @csrf </form>

@else

                    {{ $lang['submit_apply'] }}

@endif

                </div>
            </div>
        </div>

@endif







@if($action == 'wholesale_batch_applied')

        <div class="user-mod user_apply_return">
            <form id="formReturn" name="formReturn" method="post" action="user_wholesale.php" onsubmit="return check_sub()">
            <div class="user-title">
                <h2>{{ $lang['return_list'] }}</h2>
                <a href="user_order.php?act=service_detail" class="more">{{ $lang['service_note'] }}</a>
            </div>
            <div class="uaer-return-app clearfix">

@foreach($goods_return as $goods)

                <div class="tl-item">
                    <div class="item-t b-b-0">
                        <div class="t-goods">
                            <div class="t-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                    <a href="{{ $goods['url'] }}" target="_blank" class="f6">
                                        <img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}">
                                    </a>

@if($goods['parent_id'] > 0)

                                    <span class="red">（{{ $lang['accessories'] }}）</span>

@elseif ($goods['is_gift'])

                                    <span class="red">（{{ $lang['largess'] }}）</span>

@endif


@elseif ($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                                    <a href="javascript:void(0)" onclick="setSuitShow({{ $goods['goods_id'] }})" class="f6">
                                        <img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"><span style="color:#FF0000;">{{ $lang['remark_package'] }}</span>
                                    </a>
                                    <div id="suit_{{ $goods['goods_id'] }}" style="display:none">

@foreach($goods['package_goods_list'] as $package_goods_list)

                                            <a href="{{ $package_goods_list['url'] }}" target="_blank" class="f6">
                                                <img width="55" height="55" src="{{ $package_goods_list['goods_thumb'] }}" title="{{ $package_goods_list['goods_name'] }}">
                                            </a>

@endforeach

                                    </div>

@endif

                            </div>
                            <div class="t-info">
                                <div class="info-name"><a href="wholesale_goods.php?id={{ $goods['goods_id'] }}" class="ftx-03" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                <div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
                                <div class="info-attr">
@if($goods['goods_attr'])
{!! $goods['goods_attr'] !!}
@else
{{ $lang['nothing'] }}
@endif
</div>
                            </div>
                        </div>
                        <div class="t-statu">{{ $goods['subtotal'] }}</div>
                        <input name="return_rec_id[{{ $goods['rec_id'] }}]" value="{{ $goods['rec_id'] }}" type="hidden" />
                        <input name="return_g_id[{{ $goods['rec_id'] }}]" value="{{ $goods['goods_id'] }}" type="hidden" />
                        <input name="return_g_number[{{ $goods['rec_id'] }}]" value="{{ $goods['goods_number'] }}" type="hidden" />
                    </div>
                </div>

@endforeach

            </div>
            <div class="applyReturnForm">
                <div class="return_ts">
                    <em class="fl">* {{ $lang['reminder'] }}：</em>
                    <div class="fl">{{ $lang['wholesale_reminder_one'] }}&nbsp;<em>{{ $suppliers_name }}</em>&nbsp;{{ $lang['reminder_two'] }}</div>
                </div>
                <div class="form">

@if($goods_cause)

                <div class="item item1 first">
                    <div class="label"><em>*</em>{{ $lang['Service_type'] }}：</div>
                    <div class="value value-checkbox">

@foreach($goods_cause as $goods)

                        <div class="value-item
@if($goods['is_checked'] == 1)
selected
@endif
">
                            <input type="radio" id="service-{{ $goods['cause'] }}" name="return_type" value="{{ $goods['cause'] }}" class="ui-radio" autocomplete="off"
@if($goods['is_checked'] == 1)
checked
@endif
 />
                            <label for="service-{{ $goods['cause'] }}" class="ui-radio-label">{{ $goods['lang'] }}</label>
                        </div>

@endforeach

                    </div>
                </div>
                <div class="item item1">
                    <div class="label">{{ $lang['Application_credentials'] }}：</div>
                    <div class="value">
                         <input name="credentials" type="checkbox" value="" class="ui-checkbox" id="credentials"/>
                         <label for="credentials" class="ui-label">{{ $lang['has_Test_Report'] }}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="label"><em>*</em>{{ $lang['problem_desc'] }}：</div>
                    <div class="value"><textarea cols="40" class="text_desc" rows="4" name="return_brief"></textarea></div>
                </div>
                <div class="item">
                    <div class="label">{{ $lang['pic_info'] }}：</div>
                    <div class="value">
                        <div class="upload_img">
                            <div class="SWFUpload"><input type="button" id="uploadbutton" class="button" value="" data-upload_type="wholesale_goods"/></div>
                            {!! __('user.pic_info_one', ['pic_count' => config('shop.return_pictures')]) !!}
                            <div class="up_img return_images"
@if(!$img_list)
 style="display:none;"
@endif
>
                                <div class="mscoll">
                                    <a id="scollUp" class="mleft prev"><i class="iconfont icon-left"></i></a>
                                    <div class="mslist">
                                        <ul class="img-list-li">

@foreach($img_list as $img)

                                            <li>
                                                <a href="{{ $img['img_file'] }}" target="_blank"><img class="err-product" width="60" height="60" src="{{ $img['img_file'] }}" /></a>
                                            </li>

@endforeach

                                        </ul>
                                    </div>
                                    <a id="scollDown" class="mright next"><i class="iconfont icon-right"></i></a>
                                </div>
                                <a class="apply_goods_return clear_pictures" href="javascript:void(0);">{{ $lang['Empty_picture'] }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div ectype="return-type">
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['label_address'] }}：</div>
                        <div class="value">
                            <div class="address" ectype="regionLinkage">
                                <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                    <dt>
                                        <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
                                        <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                                    </dt>
                                    <dd ectype="layer">

@foreach($country_list as $country)

                                        <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                    <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span><input type="hidden" value="{{ $consignee['province'] }}" ectype="ragionItem"name="province"></dt>
                                    <dd ectype="layer">
                                        <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                                        <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                    <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span><input type="hidden" value="{{ $consignee['city'] }}" name="city" ></dt>
                                    <dd ectype="layer">
                                        <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                                        <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                    <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['district'] }}" name="district"></dt>
                                    <dd ectype="layer">
                                        <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

                                        <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                                <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                    <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['street'] }}" name="street"></dt>
                                    <dd ectype="layer">
                                        <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                                        <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                                    </dd>
                                </dl>
                            </div>
                            <div class="address_xq">
                                <input type="text" class="text_item"  name="return_address" value="{{ $consignee['address'] }}" size="42"/>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['Contact_name'] }}：</div>
                        <div class="value">
                            <input type="text" class="text_item"  name="addressee" value="{{ $consignee['consignee'] }}" size="42"/>
                            <input type="hidden" name="hid1" value="{{ $consignee['consignee'] }}"/>
                        </div>
                    </div>
                    <div class="item">
                        <div class="label"><em>*</em>{{ $lang['label_mobile'] }}：</div>
                        <div class="value">
                            <input type="text" class="text_item"  name="mobile" value="{{ $consignee['mobile'] }}" size="42"/>
                        </div>
                    </div>
                    <div class="item">
                        <div class="label">{{ $lang['email_user'] }}：</div>
                        <div class="value">
                            <input type="text" class="text_item" name="code" value="{{ $consignee['zipcode'] }}" size="42"/>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="label">{{ $lang['type']['0'] }}：</div>
                    <div class="value">
                        <textarea cols="40" class="text_area" rows="4" name="return_remark"></textarea>
                    </div>
                </div>
                <div class="item">
                    <div class="label">&nbsp;</div>
                    <div class="value">
                        <input type="hidden" name="chargeoff_status" value="{{ $order['chargeoff_status'] }}" />
                        <input type="submit" value="{{ $lang['submit_goods'] }}" class="sc-btn btn30 sc-redBg-btn" />
                        <input type="hidden" name="act" value="wholesale_submit_batch_return" />
                    </div>
                </div>

@else

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info"><h3>{{ $lang['no_support_return'] }}</h3></div>
                </div>

@endif

                </div>
            </div>
        @csrf </form>
        </div>

@endif





@if($action == 'wholesale_return_list')

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['label_return'] }}</h2>
                <h3>{{ $lang['youhave'] }}<span class="red">{{ $pager['record_count'] }}</span>{{ $lang['return_goods_user'] }}</h3>
            </div>
            <div id="return_list">
                @include('frontend::library/user_wholesale_return_order_list')
            </div>

@if($orders)

                @include('frontend::library/pages')

@endif

        </div>

@endif





@if($action == 'wholesale_return_detail')

        <form id="returnInfo" name="returnInfo" method="post" action="user_wholesale.php" onsubmit="return shipping_sub(this)">
        <div class="user-mod user-order-detail">
            <div class="user-title mb0">
                <h2>{{ $lang['detailed_info'] }}</h2>
            </div>
            <div class="user-info-list b-t-0">
                <div class="info-title"><h2>{{ $lang['detailed_info'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['return_sn'] }}：</div>
                        <div class="item-value">{{ $goods['return_sn'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['apply_time'] }}：</div>
                        <div class="item-value">{{ $goods['apply_time'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['return_type_user'] }}：</div>
                        <div class="item-value">
@if($goods['return_type'] == 0)
{{ $lang['order_return_type']['0'] }}
@elseif ($goods['return_type'] == 1)
{{ $lang['order_return_type']['1'] }}
@elseif ($goods['return_type'] == 2)
{{ $lang['order_return_type']['2'] }}
@elseif ($goods['return_type'] == 3)
{{ $lang['order_return_type']['3'] }}
@endif
</div>
                    </div>
                </div>
            </div>
            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['order_status'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['order_status'] }}：</div>
                        <div class="item-value">{{ $goods['return_status'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['back_cause'] }}：</div>
                        <div class="item-value">{{ $goods['return_brief'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['order_status'] }}：</div>
                        <div class="item-value ftx-01">
@if($goods['return_status1'] < 0)
{{ $lang['only_return_money'] }}
@else
{{ $lang['rf'][$goods['return_status1']] }}
@endif
-{{ $lang['ff'][$goods['refound_status1']] }}</div>
                    </div>

@if($goods['action_note'])

                    <div class="info-item">
                        <div class="item-label">{{ $lang['after_service_desc'] }}：</div>
                        <div class="item-value">{{ $goods['action_note'] }}</div>
                    </div>

@endif

                </div>
            </div>
            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['contact_username'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item">
                        <div class="item-label">{{ $lang['contact_username'] }}：</div>
                        <div class="item-value">{{ $goods['addressee'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['Contact_address'] }}：</div>
                        <div class="item-value">{{ $goods['address_detail'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="item-label">{{ $lang['contact_phone'] }}：</div>
                        <div class="item-value">{{ $goods['phone'] }}</div>
                    </div>
                </div>
            </div>

@if($goods['return_type'] == 2)

            <div class="user-info-list">
                <div class="info-title"><h2>{{ $lang['progress_return'] }}</h2></div>
                <div class="user-order-list">
                    <dl class="item relative">
                        <dt class="item-t b-b-0">
                            <div class="t-statu">
@if($goods['return_status1'] == 4)
{{ $lang['is_confirmed'] }}
@else
{{ $lang['zc_ss'] }}
@endif
</div>
                            <div class="t-info">
                                <span class="info-item">{{ $lang['order_number'] }}：{{ $goods['order_sn'] }}</span>
                                <span class="info-item">{{ $lang['Waybill'] }}：
@if($goods['out_invoice_no'] != "")
{{ $goods['out_invoice_no'] }}
@else
&nbsp;
@endif
</span>
                                <span class="info-item">{{ $lang['Logistics_company'] }}：
@if($goods['out_invoice_no'] != "")
{{ $goods['out_shipp_shipping'] }}
@else
&nbsp;
@endif
</span>
                            </div>
                            <div class="t-right" ectype="track-packages-btn">
                                <a href="javascript:void(0)" class="sc-btn"><i class="iconfont icon-truck"></i>{{ $lang['Track'] }}</a>
                                <div class="comment-box" ectype="track-packages-info">
                                    <i class="box-i"></i>
                                    <div class="conmment-box-content">
                                        <div class="cont" id="seller_deliveryInfo">

                                        </div>
                                    </div>
                                </div>
                                <span id="invoice_no_{{ $goods['order_id'] }}" style="display:none">{{ $goods['out_invoice_no'] }}</span>
                                <span id="shipping_name_{{ $goods['order_id'] }}" style="display:none">{{ $goods['out_shipp_shipping'] }}</span>
                            </div>
                        </dt>
                    </dl>
                </div>
            </div>

@if($goods['out_invoice_no'])

            <script language="javascript">
            $("#seller_deliveryInfo").html("<center>"+json_languages.logistics_tracking_in+"</center>");
            if(document.getElementById("shipping_name_{{ $goods['order_id'] }}"))
            {
                var expressid = document.getElementById("shipping_name_{{ $goods['order_id'] }}").innerHTML;
                var expressno = document.getElementById("invoice_no_{{ $goods['order_id'] }}").innerHTML;
            }
            $.ajax({
                url: "user.php?act=express",
                type: "post",
                data:'com=' + expressid + '&nu=' + expressno,
                success: function(data,textStatus){
                    $("#seller_deliveryInfo").html(data);
                },
                 error: function(o){
                }
            });
            </script>

@endif


@endif



@if($goods['agree_apply'] && $goods['return_type'] != 3)


            <div class="user-info-list">
                <div class="info-title">
                    <h2>{{ $lang['Express_info'] }}</h2>
                </div>

@if($goods['back_shipp_shipping'])

                <div class="express_list">
                    <div class="ex_tit">{{ $lang['User_sent'] }}</div>
                    <div class="ex_con">
                        <div class="ex_item">
                            <span>{{ $lang['Express_company'] }}：</span>
                            <span class="blue">{{ $goods['back_shipp_shipping'] }}</span>
                        </div>
                        <div class="ex_item">
                            <span>{{ $lang['courier_sz'] }}：</span>
                            <span class="blue">{{ $goods['back_invoice_no'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="blank"></div>

@else

                <div class="express_list">
                    <div class="ex_tit lh26">{{ $lang['Express_info_two'] }}</div>
                    <div class="ex_con">
                        <div class="ex_item">
                            <span>{{ $lang['Express_company'] }}：</span>
                            <select name="express_name" onchange="select_express(this)" class="select">
                                <option value="0">{{ $lang['select_Express_company'] }}</option>
@foreach($shipping_list as $shipping)

                                <option value="{{ $shipping['shipping_id'] }}">{{ $shipping['shipping_name'] }}</option>
@endforeach

                                <option value="999">{{ $lang['Other'] }}</option>
                            </select>
                            <input type="text" name="other_express" id="other_express" class="text ml10"  style="display:none"/>
                        </div>
                        <div class="ex_item">
                            <span>{{ $lang['courier_sz'] }}：</span>
                            <input type="text" name="express_sn" class="text text-2"/>
                        </div>
                        <input type="submit" class="btn sc-redBg-btn btn30 ml75" value="{{ $lang['button_submit'] }}"/>
                        <input type="hidden" name="act" value="wholesale_edit_express">
                        <input type="hidden" name="order_id" value="{{ $goods['order_id'] }}" />
                        <input type="hidden" name="ret_id" value="{{ $goods['ret_id'] }}" />
                    </div>
                </div>

@endif


@if($goods['out_shipp_shipping'])

                <div class="express_list">
                    <div class="ex_tit">{{ $lang['seller_shipping'] }}</div>
                    <div class="ex_con">
                        <div class="ex_item">
                            <span>{{ $lang['Express_company'] }}：</span>
                            <span class="blue">{{ $goods['out_shipp_shipping'] }}</span>
                        </div>
                        <div class="ex_item">
                            <span>{{ $lang['courier_sz'] }}：</span>
                            <span class="blue">{{ $goods['out_invoice_no'] }}</span>
                        </div>
                    </div>
                </div>

@endif

            </div>

@endif

            <div class="user-table-list">
                <div class="pt10 pb10">
                <a href="{{ $goods['shop_url'] }}" class="user-shop-link">{{ $goods['suppliers_name'] }}</a>
                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $goods['ru_id'] }}"  class="iconfont icon-kefu user-shop-kefu"></a>
                </div>
                <table class="user-table user-table-detail-goods">
                    <colgroup>
                        <col width="320">
                        <col width="110">
                        <col width="120">
                        <col width="95">
                        <col width="90">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>{{ $lang['goods'] }}</th>
                            <th class="tl">{{ $lang['goods_attr'] }}</th>
                            <th class="tl">{{ $lang['unit_price_user'] }}</th>
                            <th class="tl">{{ $lang['number_to'] }}</th>
                            <th>{{ $lang['ws_subtotal'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <a href="{{ $goods['url'] }}" class="img"><img src="{{ $goods['goods_thumb'] }}" alt=""></a>
                                <a href="{{ $goods['url'] }}" class="name">{{ $goods['goods_name'] }}</a>
                            </td>
                            <td>{!! nl2br($goods['goods_attr']) !!}</td>
                            <td>{{ $goods['goods_price'] }}</td>
                            <td>x{{ $goods['return_number'] }}</td>
                            <td>{{ $goods['should_return'] }}</td>
                        </tr>
                    </tbody>
                </table>

@if($goods['return_type'] == 1 || $goods['return_type'] == 3)


@if($goods['return_shipping_fee'] && $goods['refound_status1'] == 1)

                    <div class="user-order-detail-total" style="margin-bottom:0px;">
                        <dl class="total-row" style="margin-top:0px; margin-bottom:0px;">
                            <dt class="total-label">{{ $lang['amount_return'] }}：</dt>
                            <dd class="total-value" style="font-size:14px; font-weight:normal; color:#999"> + {{ $goods['formated_should_return'] }}</dd>
                        </dl>
                    </div>
                    <div class="user-order-detail-total" style="margin-top:0px;">
                        <dl class="total-row" style="margin-top:0px;">
                            <dt class="total-label">{{ $lang['return_total'] }}：</dt>
                            <dd class="total-value" style="font-size:18px; font-weight:normal">{{ $goods['formated_return_amount'] }}</dd>
                        </dl>
                    </div>

@else

                    <div class="user-order-detail-total">
                        <dl class="total-row">
                            <dt class="total-label">{{ $lang['amount_return'] }}：</dt>
                            <dd class="total-value">{{ $goods['should_return'] }}</dd>
                        </dl>
                    </div>

@endif


@endif

            </div>

@if($goods['img_list'])

            <div class="user-info-list b-t-0">
                <div class="info-title"><h2>{{ $lang['pic_voucher'] }}</h2></div>
                <div class="info-content">
                    <div class="info-item info-item-100">

@foreach($goods['img_list'] as $img)

                        <a href="{{ $img['img_file'] }}" target="_blank"><img src="{{ $img['img_file'] }}" width="100" height="100" style="border:1px #ccc solid; padding:2px;" /></a>

@endforeach

                    </div>
                </div>
            </div>

@endif


        </div>
        @csrf </form>
        <script>
            function select_express(obj){
                var val = obj.value;
                if( val == '999'){
                    $$('other_express').style.display='inline';
                }else{
                    $$('other_express').style.display='none';
                }

            }
            /*编辑快递*/
            function shipping_sub( frm ){
                var shipping = frm['express_name'].value;
                if( shipping <= 0 ){
                    alert(json_languages.select_Express_company);
                    return false;
                }
                if( shipping =='999'){
                    if( frm['other_express'].value==''){
                        alert(json_languages.Express_companyname_null);
                        return false;
                    }
                }
                var express_sn = frm['express_sn'].value;
                if( express_sn ==''){
                    alert(json_languages.courier_sz_nul);
                    return false;
                }
            }
        </script>

@endif





@if($action == 'wholesale_goods_order' )

        <div class="user-mod">
            <div class="user-title">
                <h2>{{ $lang['return_policy_apply'] }}</h2>
            </div>
            <form method="post" action="user_wholesale.php" name="theFrom" id="theFrom">
                <div class="uaer-return-app clearfix">

@if($package_buy)

                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info"><h3>{{ $lang['package_buy_notic'] }}</h3></div>
                    </div>

@else


@forelse($goods_list as $goods)

                    <div class="tl-item">
                        <div class="item-t">
                            <div class="t-goods">
                                <div class="t-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                        <a href="{{ $goods['url'] }}" target="_blank" class="f6"><img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"></a>

@if($goods['parent_id'] > 0)

                                        <span style="color:#FF0000">（{{ $lang['accessories'] }}）</span>

@elseif ($goods['is_gift'])

                                        <span style="color:#FF0000">（{{ $lang['largess'] }}）</span>

@endif


@elseif ($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                                        <a href="javascript:void(0)" onclick="setSuitShow({{ $goods['goods_id'] }})" class="f6"><img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"><span style="color:#FF0000;">{{ $lang['remark_package'] }}</span></a>
                                        <div id="suit_{{ $goods['goods_id'] }}" style="display:none">

@foreach($goods['package_goods_list'] as $package_goods_list)

                                                <a href="{{ $package_goods_list['url'] }}" target="_blank" class="f6"><img width="55" height="55" src="{{ $goods['goods_thumb'] }}" title="{{ $goods['goods_name'] }}"></a><br />

@endforeach

                                        </div>

@endif

                                </div>
                                <div class="t-info">
                                    <div class="info-name"><a href="{{ $goods['url'] }}" class="ftx-03" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                    <div class="info-price"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>
                                    <div class="info-attr">
@if($goods['goods_attr'])
{!! $goods['goods_attr'] !!}
@else
{{ $lang['nothing'] }}
@endif
</div>
                                </div>
                            </div>
                            <div class="t-statu">

@if($goods['is_refound'] > 0)

                                <span class="ftx-01 fs14">{{ $lang['Have_applied'] }}</span>

@else

                                <a href="user_wholesale.php?act=wholesale_apply_return&rec_id={{ $goods['rec_id'] }}&order_id={{ $order_id }}" class="sc-btn">{{ $lang['applied'] }}</a>

@endif

                            </div>
                        </div>
                        <div class="item-f">
                            <div class="f-left">
                                <div class="checkbox">
                                    <input type="checkbox" name="checkboxes[]" value="{{ $goods['rec_id'] }}" class="ui-checkbox" id="checkbox_{{ $goods['rec_id'] }}" />
                                    <label for="checkbox_{{ $goods['rec_id'] }}" class="ui-label"></label>
                                </div>
                            </div>
                            <div class="f-right">
                                <span class="red mr50">
@if($goods['goods_cause'])
{{ $lang['only_support'] }}：
@foreach($goods['goods_cause'] as $cause)

@if(!$loop->last)
{{ $cause['lang'] }}、
@else
{{ $cause['lang'] }}
@endif

@endforeach

@else
{{ $lang['no_support_return'] }}
@endif
</span>
                                <span>{{ $lang['shopping_money'] }}
@if($order['extension_code'] == "group_buy")
{{ $lang['gb_deposit'] }}
@endif
：{{ $goods['subtotal'] }}</span>
                            </div>
                        </div>
                    </div>

@empty

                    <div class="no_records">
                        <i class="no_icon_two"></i>
                        <div class="no_info"><h3>{{ $lang['order_not_notic'] }}</h3></div>
                    </div>

@endforelse

                    <div class="tl-tfoor">
                        <div class="checkbox">
                            <input type="checkbox" name="all_list" class="ui-checkbox" id="checkbox_stars" />
                            <label for="checkbox_stars" class="ui-label">{{ $lang['all'] }}</label>
                        </div>
                        <a href="javascript:;" class="sc-btn" ectype="submitBtn" >{{ $lang['batch_applied'] }}</a>
                        <input type="hidden" name="act" value="wholesale_batch_applied">
                        <input type="hidden" name="order_id" value="{{ $order_id }}">
                    </div>

@endif

                </div>
            @csrf </form>
        </div>

@endif





@if($action == 'wholesale_apply_return')

        <div class="user-mod user_apply_return">
            <div class="user-title">
                <h2>{{ $lang['return_list'] }}</h2>
                <a href="user_order.php?act=service_detail" class="more">{{ $lang['service_note'] }}</a>
            </div>
            <div class="uaer-return-app clearfix">
                <div class="tl-item">
                    <div class="item-t b-b-0">
                        <div class="t-goods">
                            <div class="t-img">

@if($goods_return['goods_id'] > 0 && $goods_return['extension_code'] != 'package_buy')

                                    <a href="{{ $goods_return['url'] }}" target="_blank" class="f6">
                                        <img width="55" height="55" src="{{ $goods_return['goods_thumb'] }}" title="{{ $goods_return['goods_name'] }}">
                                    </a>

@if($goods_return['parent_id'] > 0)

                                    <span class="red">（{{ $lang['accessories'] }}）</span>

@elseif ($goods_return['is_gift'])

                                    <span class="red">（{{ $lang['largess'] }}）</span>

@endif


@elseif ($goods_return['goods_id'] > 0 && $goods_return['extension_code'] == 'package_buy')

                                    <a href="javascript:void(0)" onclick="setSuitShow({{ $goods_return['goods_id'] }})" class="f6">
                                        <img width="55" height="55" src="{{ $goods_return['goods_thumb'] }}" title="{{ $goods_return['goods_name'] }}"><span style="color:#FF0000;">{{ $lang['remark_package'] }}</span>
                                    </a>
                                    <div id="suit_{{ $goods_return['goods_id'] }}" style="display:none">

@foreach($goods_return['package_goods_list'] as $package_goods_list)

                                            <a href="{{ $package_goods_list['url'] }}" target="_blank" class="f6">
                                                <img width="55" height="55" src="{{ $package_goods_list['goods_thumb'] }}" title="{{ $package_goods_list['goods_name'] }}">
                                            </a>

@endforeach

                                    </div>

@endif

                            </div>
                            <div class="t-info">
                                <div class="info-name"><a href="{{ $goods_return['url'] }}" class="ftx-03" target="_blank">{{ $goods_return['goods_name'] }}</a></div>
                                <div class="info-price"><b>{{ $goods_return['goods_price'] }}</b><i>×</i><span>{{ $goods_return['goods_number'] }}</span></div>
                                <div class="info-attr">
@if($goods_return['goods_attr'])
{!! $goods_return['goods_attr'] !!}
@else
{{ $lang['nothing'] }}
@endif
</div>
                            </div>
                        </div>
                        <div class="t-statu">{{ $goods_return['subtotal'] }}</div>
                    </div>
                </div>
            </div>
            <div class="applyReturnForm">
                <form id="formReturn" name="formReturn" method="post" action="user_wholesale.php" onsubmit="return check_sub()">
                    <div class="return_ts">
                        <em class="fl">* {{ $lang['reminder'] }}：</em>
                        <div class="fl">{{ $lang['wholesale_reminder_one'] }}&nbsp;<em>{{ $goods_return['suppliers_name'] }}</em>&nbsp;{{ $lang['reminder_two'] }}</div>
                    </div>
                    <div class="form">

@if($goods_cause)

                        <div class="item item1 first">
                            <div class="label"><em>*</em>{{ $lang['Service_type'] }}：</div>
                            <div class="value value-checkbox">

@foreach($goods_cause as $goods)


@if($order['shipping_status'] == 1 || ($order['shipping_status'] != 1 && $goods['cause'] == 3))

                                    <div class="value-item
@if($goods['is_checked'] == 1)
selected
@endif
">
                                        <input type="radio" id="service-{{ $goods['cause'] }}" name="return_type" value="{{ $goods['cause'] }}" class="ui-radio" autocomplete="off"
@if($goods['is_checked'] == 1)
checked
@endif
 />
                                        <label for="service-{{ $goods['cause'] }}" class="ui-radio-label">{{ $goods['lang'] }}</label>
                                    </div>
@endif

@endforeach

                                <input name="return_rec_id" value="{{ $goods_return['rec_id'] }}" type="hidden" />
                                <input name="return_g_id" value="{{ $goods_return['goods_id'] }}" type="hidden" />
                                <input name="return_g_number" value="{{ $goods_return['goods_number'] }}" type="hidden" />
                            </div>
                            <div class="lists" ectype="return_lists">
                                <div class="return_div" ectype="return_div">
                                    <div class="type_box1" name="type_maintain" id="maintain_{{ $goods_return['rec_id'] }}">
                                        <div class="type_item">{{ $lang['Repair_number'] }}：</div>
                                        <div class="type_con">
                                            <a onclick="buyNumber.minus(this, 1)" href="javascript:;" id="decrease" class="plus_minus"><i class="iconfont icon-reduce"></i></a>
                                            <input type="text" class="return_num" defaultnumber="1" value="1" id="maintain_{{ $goods_return['rec_id'] }}" name="maintain_number" onblur="check_return_num(this,{{ $goods_return['goods_number'] }} ,{{ $goods_return['rec_id'] }}, 1)"/>
                                            <a onclick="buyNumber.plus(this, 1)" href="javascript:;" id="increase" class="plus_minus"><i class="iconfont icon-plus"></i></a>
                                            <div class="return_sm">({{ $lang['Repair_one'] }}<span>{{ $goods_return['goods_number'] }}</span>{{ $lang['Repair_two'] }}<span id="maintain_span_{{ $goods_return['rec_id'] }}" name="maintain">1</span>{{ $lang['jian'] }})</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="return_div" ectype="return_div" style="display:none;">
                                    <div class="type_box1" name="type_box1" id="returnid_{{ $goods_return['rec_id'] }}">
                                        <div class="type_item">{{ $lang['return_number'] }}：</div>
                                        <div class="type_con">
                                            <a onclick="buyNumber.minus(this, 1)" href="javascript:;" id="decrease" class="plus_minus"><i class="iconfont icon-reduce"></i></a>
                                            <input type="text" class="return_num" defaultnumber="1" value="1" id="returnid_{{ $goods_return['rec_id'] }}" name="return_num" onblur="check_return_num(this,{{ $goods_return['goods_number'] }} ,{{ $goods_return['rec_id'] }}, 2)" />
                                            <a onclick="buyNumber.plus(this, 1)" href="javascript:;" id="increase" class="plus_minus"><i class="iconfont icon-plus"></i></a>
                                            <div class="return_sm">({{ $lang['return_one'] }}<span>{{ $goods_return['goods_number'] }}</span>{{ $lang['return_two'] }}<span id="retired_{{ $goods_return['rec_id'] }}" name="retired">1</span>{{ $lang['jian'] }})</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="return_div" ectype="return_div" style="display:none;">
                                    <div class="spec_div" id="spec_{{ $goods_return['rec_id'] }}" style="display:none"></div>
                                    <div class="spec_list" id="splist_{{ $goods_return['rec_id'] }}" style="display:none"></div>
                                    <div name="type_box2" class="type_box2" id="changeid_{{ $goods_return['rec_id'] }}">
                                        <div class="return_sm">({{ $lang['return_one'] }}<span>{{ $goods_return['goods_number'] }}</span>{{ $lang['change_two'] }}<span id="retired1_{{ $goods_return['rec_id'] }}">1</span>{{ $lang['jian'] }})</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item item1">
                            <div class="label">{{ $lang['Application_credentials'] }}：</div>
                            <div class="value">
                                 <input name="credentials" type="checkbox" value="" class="ui-checkbox" id="credentials"/>
                                 <label for="credentials" class="ui-label">{{ $lang['has_Test_Report'] }}</label>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em>*</em>{{ $lang['return_reason'] }}：</div>
                            <div class="value">
                                <span class="cause_select">
                                <select name="parent_id" id="cause_{{ $goods_return['rec_id'] }}" onchange="selectCause(this.value ,{{ $goods_return['rec_id'] }})">
                                <option value="0">{{ $lang['please_select'] }}</option>
                                {!! $cause_list !!}
                                </select>
                                </span>
                                <span id="children_{{ $goods_return['rec_id'] }}" class="cause_select"></span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label"><em>*</em>{{ $lang['problem_desc'] }}：</div>
                            <div class="value"><textarea cols="40" class="text_desc" rows="4" name="return_brief"></textarea></div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['pic_info'] }}：</div>
                            <div class="value">
                                <div class="upload_img">
                                    <div class="SWFUpload"><input type="button" id="uploadbutton" class="button" value="" data-upload_type="wholesale_goods"/></div>
                                    {!! __('user.pic_info_one', ['pic_count' => config('shop.return_pictures')]) !!}
                                    <div class="up_img return_images"
@if(!$img_list)
 style="display:none;"
@endif
>
                                        <div class="mscoll">
                                            <a id="scollUp" class="mleft prev"><i class="iconfont icon-left"></i></a>
                                            <div class="mslist">
                                                <ul class="img-list-li">

@foreach($img_list as $img)

                                                    <li>
                                                        <a href="{{ $img['img_file'] }}" target="_blank"><img class="err-product" width="60" height="60" src="{{ $img['img_file'] }}" /></a>
                                                    </li>

@endforeach

                                                </ul>
                                            </div>
                                            <a id="scollDown" class="mright next"><i class="iconfont icon-right"></i></a>
                                        </div>
                                        <a class="apply_goods_return clear_pictures" href="javascript:void(0);">{{ $lang['Empty_picture'] }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div ectype="return-type">
                            <div class="item">
                                <div class="label"><em>*</em>{{ $lang['label_address'] }}：</div>
                                <div class="value">
                                    <div class="address" ectype="regionLinkage">
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt>
                                                <span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[0] }}</span>
                                                <input type="hidden" value="{{ $consignee['country'] }}" name="country">
                                            </dt>
                                            <dd ectype="layer">

@foreach($country_list as $country)

                                                <div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</span><input type="hidden" value="{{ $consignee['province'] }}" ectype="ragionItem"name="province"></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

                                                <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</span><input type="hidden" value="{{ $consignee['city'] }}" name="city" ></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

                                                <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['district'] }}" name="district"></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

                                                <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                        <dl class="mod-select mod-select-small" ectype="smartdropdown" style="display:none">
                                            <dt><span class="txt" ectype="txt">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</span><input type="hidden" value="{{ $consignee['street'] }}" name="street"></dt>
                                            <dd ectype="layer">
                                                <div class="option" data-value="0">{{ $lang['please_select'] }}{{ $name_of_region[3] }}</div>

@foreach($street_list as $street)

                                                <div class="option" data-value="{{ $street['region_id'] }}" data-type="5" data-text="{{ $street['region_name'] }}" ectype="ragionItem">{{ $street['region_name'] }}</div>

@endforeach

                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="address_xq">
                                        <input type="text" class="text_item"  name="return_address" value="{{ $consignee['address'] }}" size="42"/>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em>*</em>{{ $lang['Contact_name'] }}：</div>
                                <div class="value">
                                    <input type="text" class="text_item"  name="addressee" value="{{ $consignee['consignee'] }}" size="42"/>
                                    <input type="hidden" name="hid1" value="{{ $consignee['consignee'] }}"/>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label"><em>*</em>{{ $lang['label_mobile'] }}：</div>
                                <div class="value">
                                    <input type="text" class="text_item"  name="mobile" value="{{ $consignee['mobile'] }}" size="42"/>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{{ $lang['email_user'] }}：</div>
                                <div class="value">
                                    <input type="text" class="text_item" name="code" value="{{ $consignee['zipcode'] }}" size="42"/>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">{{ $lang['type']['0'] }}：</div>
                            <div class="value">
                                <textarea cols="40" class="text_area" rows="4" name="return_remark"></textarea>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="value">
                                <input type="hidden" name="chargeoff_status" value="{{ $order['chargeoff_status'] }}" />
                                <input type="submit" value="{{ $lang['submit_goods'] }}" class="sc-btn btn30 sc-redBg-btn" />
                                <input type="hidden" name="act" value="wholesale_submit_return" />
                                <input type="hidden" name="rec_id" value="{{ $goods_return['rec_id'] }}" />
                            </div>
                        </div>

@else

                        <div class="no_records">
                            <i class="no_icon_two"></i>
                            <div class="no_info"><h3>{{ $lang['no_support_return'] }}</h3></div>
                        </div>

@endif

                    </div>
                @csrf </form>
            </div>
        </div>

@endif




    </div>
</div>

@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/sms/sms.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.nyroModal.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ url('calendar.php') }}"></script>
<script type="text/javascript" src="{{ asset('js/plupload.full.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/lib_ecmobanFunc.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/user.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.picTip.js') }}"></script>

@if($action == 'profile')

<script type="text/javascript" src="{{ asset('js/amazeui/amazeui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cropper/cropper.min.js') }}"></script>

@endif

<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/region.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>

<script type="text/javascript">
$(function(){
	//tab切换
	$("*[ectype='tabSection']").slide({titCell:"*[ectype='tabs'] li",mainCell:"*[ectype='tabContent']",trigger:"click",titOnClassName:"active"});

	//查看图片
	$(".nyroModal").nyroModal();

	if(document.getElementById("seccode")){
		$("#seccode").val({{ $sms_security_code ?? 0 }});
	}

	//微信扫码弹出框
	$("[data-type='wxpay']").on("click",function(){
		var content = $("#wxpay_dialog").html();
		pb({
			id: "scanCode",
			title: "{{ $lang['wxpay_pay'] }}",
			width: 716,
			content: content,
			drag: true,
			foot: false,
			cl_cBtn: false,
			cBtn: false
		});
	});

	//优惠券列表数量过多时添加滚动轴
	$(".coupons-items").perfectScrollbar("destroy");
	$(".coupons-items").perfectScrollbar();
});

@if($action == 'address')


@if($address_id > 0)
$.levelLink();
@else
$.levelLink(1);
@endif


@endif

</script>



@if($action == 'snatch_list')

<script type="text/javascript">
	function get_SnatchSearch(idTxt, action, type, t, c){
		var keyword, status_keyword, date_keyword;
		var snatch = new Object();
		if(t){
			keyword = $(t).data('id');

			if(idTxt == 'status_list'){
				$("input[name='order_status_list']").val(keyword);
			}else if(idTxt == 'submitDate'){
				$("input[name='order_submitDate']").val(keyword);
			}
		}else{
			keyword = document.getElementById(idTxt).value;
		}

		if(c){
			$(c).addClass("active").siblings().removeClass("active");
		}

		if(idTxt == 'submitDate'){
			var status_keyword = $("input[name='order_status_list']").val();
			snatch.status_keyword = status_keyword;
		}else if(idTxt == 'status_list'){
			var date_keyword = $("input[name='order_submitDate']").val();
			snatch.date_keyword = date_keyword;
		}

		snatch.idTxt = idTxt;
		snatch.keyword = keyword;
		snatch.action = action;
		snatch.type = type;

		Ajax.call('user_activity.php?act=snatch_to_query', 'snatch=' + $.toJSON(snatch), auctionResponse, 'POST', 'JSON');
	}

	function auctionResponse(result){
		if(result.error == 0){
			$("#user-snatch-list").html(result.content);
		}
	}
</script>

@endif



@if($action == 'auction_list')

<script type="text/javascript">
	function get_AuctionSearch(idTxt, action, type, t, c){
		var keyword, status_keyword, date_keyword;
		var auction = new Object();
		if(t){
			keyword = $(t).data('id');

			if(idTxt == 'status_list'){
				$("input[name='order_status_list']").val(keyword);
			}else if(idTxt == 'submitDate'){
				$("input[name='order_submitDate']").val(keyword);
			}
		}else{
			keyword = document.getElementById(idTxt).value;
		}

		if(c){
			$(c).addClass("active").siblings().removeClass("active");
		}

		if(idTxt == 'submitDate'){
			var status_keyword = $("input[name='order_status_list']").val();
			auction.status_keyword = status_keyword;
		}else if(idTxt == 'status_list'){
			var date_keyword = $("input[name='order_submitDate']").val();
			auction.date_keyword = date_keyword;
		}

		auction.idTxt = idTxt;
		auction.keyword = keyword;
		auction.action = action;
		auction.type = type;

		Ajax.call('user_activity.php?act=auction_to_query', 'auction=' + $.toJSON(auction), auctionResponse, 'POST', 'JSON');
	}

	function auctionResponse(result){
		if(result.error == 0){
			$("#user-auction-list").html(result.content);
		}
	}
</script>

@endif



@if($action == 'order_detail')
<!-- 上传汇款凭证 -->
@if($order['pay_code'] == 'bank' && $order['pay_status'] == 0)
<script type="text/javascript">
        // 上传订单支付凭证图片
        var order_id = $("input[name='order_id']").val();
        var user_id = $("input[name='user_id']").val();

        var uploader_pay = new plupload.Uploader({//创建实例的构造方法
            runtimes: 'html5,flash,silverlight,html4', //上传插件初始化选用那种方式的优先级顺序
            browse_button: 'upload_pay_document', // 上传按钮
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "ajax_user.php?act=upload_pay_document&user_id="+user_id+"&order_id="+order_id, //远程上传地址
            filters: {
                max_file_size: '2mb', //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
                mime_types: [//允许文件上传类型
                    {title: "files", extensions: "jpg,jpeg,png,gif,pdf"}
                ]
            },
            multi_selection: false, //true:ctrl多文件上传, false 单文件上传
            init: {
                FilesAdded: function(up, files) { //文件上传前

                    uploader_pay.start();
                },
                FileUploaded: function(up, file, info) { //文件上传成功的时候触发

                    if(info.status == 200 ){
                        data = $.parseJSON(info.response);
                        console.log(data);

                        pbDialog(data.err_msg,"",0);

                        window.location.reload();
                    }

                },
                Error: function(up, err) { //上传出错的时候触发
                    alert(err.message);
                }
            }
        });
        uploader_pay.init();
</script>

@endif
@endif



@if($action == 'order_list' || $action == 'order_recycle' || $action == 'auction')

<script type="text/javascript">

@if($order_type)

	$(function(){
		if({{ $order_where }} == 1){
			get_OrderSearch('to_unconfirmed', '{{ $action }}', 'toBe_unconfirmed')
		}else if({{ $order_where }} == 2){
			get_OrderSearch('payId', '{{ $action }}', 'toBe_pay')
		}else if({{ $order_where }} == 3){
			get_OrderSearch('to_confirm_order', '{{ $action }}', 'toBe_confirmed')
		}else if({{ $order_where }} == 4){
			get_OrderSearch('to_finished', '{{ $action }}', 'toBe_finished')
		}
	});

@endif


	//删除、还原、永久性删除订单 start
	function get_order_delete_restore(action, order_id){
		var order = new Object();
		var firm;

		if(action == 'delete'){
			firm = json_languages.operation_order_one;
		}else if(action == 'restore'){
			firm = json_languages.operation_order_two;
		}else if(action == 'thorough'){
			firm = json_languages.operation_order_three;
		}

		order.order_id = order_id;
		order.action  = action;

@if($action == 'auction')

		order.act = 'auction';

@else

		order.act = 'order_list';

@endif


		if(confirm(firm)){
			Ajax.call('user_order.php?act=order_delete_restore', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
		}
	}

	//删除、还原、永久性删除订单 end

    function buy_again(order_id){
        var order_id = order_id;

        Ajax.call('ajax_user.php?act=buyagain', 'order_id=' + order_id, buyagainResponse, 'POST', 'JSON');
    }

    function buyagainResponse(result){
        if(result.error === 1){
           alert(result.msg);
        }else{
            if(result.cant_buy_goods.length > 0){
                pbDialog("{{ $lang['add_other_goods'] }}","",0,490,"","",true,function(){
                    window.location.href = 'flow.php'
                });
            }

            if(result.cant_buy_goods.length === 0){
                window.location.href = 'flow.php'
            }
        }

    }

	//by wang 修改过
	function get_OrderSearch(idTxt, action, type, t, c){
		var keyword, status_keyword, date_keyword;
		var order = new Object();
		if(t){
			keyword = $(t).data('id');

			if(idTxt == 'status_list'){
				$("input[name='order_status_list']").val(keyword);
			}else if(idTxt == 'submitDate'){
				$("input[name='order_submitDate']").val(keyword);
			}
		}else{
			keyword = document.getElementById(idTxt).value;
		}

		if(c){
			$(c).addClass("active").siblings().removeClass("active");
		}

		if(idTxt == 'submitDate'){
			var status_keyword = $("input[name='order_status_list']").val();
			order.status_keyword = status_keyword;
		}else if(idTxt == 'status_list'){
			var date_keyword = $("input[name='order_submitDate']").val();
			order.date_keyword = date_keyword;
		}

		order.idTxt = idTxt;
		order.keyword = keyword;
		order.action = action;
		order.type = type;

		Ajax.call('user_order.php?act=order_to_query', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
	}

	function orderResponse(result){
		if(result.error == 0){
			$("#user_order_list").html(result.content);
		}
	}
</script>

@endif



@if($action == 'address_list')

<script type="text/javascript">
	function alertDelAddressDiag(address_id){
		pbDialog(json_languages.delete_consignee,"",0,490,"","",true,function(){
			Ajax.call('user_address.php?act=ajax_del_address', 'address_id=' + address_id, delAddressResponse, 'GET', 'JSON');
		});
	}

	function delAddressResponse(res){
	    $('.user-title h3 span.count_consignee').html(res.count);
		$(".consignee_list_"+res.address_id).remove();
	}

	//设置默认收获地址
	function makeAddressAllDefault(address_id){
		pbDialog(json_languages.default_consignee,"",0,490,"","",true,function(){
			Ajax.call('user_address.php?act=ajax_make_address', 'address_id=' + address_id, makeAddressResponse, 'GET', 'JSON');
		});
	}

	function makeAddressResponse(res){
		location.reload();
	}
</script>

@endif



@if($action == 'bonus')

<script type="text/javascript">
	function new_addBonus(){
		var bns = new Object;

		bns.bonus_sn = $(".txt_input_cardnum").val();
		bns.password = $(".txt_input_cardpw").val();
		bns.captcha = $(":input[name='captcha']").val();

		if(bns.bonus_sn == ''){
			message = json_languages.card_number_null;
		}else if(bns.password == ''){
			message = json_languages.Real_name_password_null;
		}else if(bns.captcha == ''){
			message = json_languages.null_captcha_login;
		}

		if(bns.bonus_sn != '' && bns.password != '' && bns.captcha != ''){
			Ajax.call('ajax_user.php', 'act=act_add_bonus&bns=' + $.toJSON(bns), function(data){
				if(data.error == 2){
					pbDialog(data.message,"",0,"","","",true,function(){
						location.href = "user.php";
					});
				}else if(data.error == 3){
					pbDialog(data.message,"",0);
				}else if(data.error == 0){
					pbDialog(data.message,"",1,"","","",true,function(){
						location.href = "user_activity.php?act=bonus";
					});
				}else{
					pbDialog(data.message,"",0);
				}
			}, 'POST', 'JSON');
		}else{
			pbDialog(message,"",0);
		}
	}
</script>

@endif



@if($action == 'order_list' || $action == 'auction' || $action == 'order_recycle')

<script type="text/javascript">
	$.divselect("#order_status","#order_status_val",function(){
		var order    = new Object();
		var key		 = $("#order_status_val").val();
		var date_keyword = $("input[name='order_submitDate']").val();

		order.idTxt   	= $("#order_status_"+key+" a").attr('data-idTxt');
		order.keyword   = $("#order_status_"+key+" a").attr('data-id');
		order.action  	= $("#order_status_"+key+" a").attr('data-action');
		order.type   	= $("#order_status_"+key+" a").attr('data-type');
		order.date_keyword   = date_keyword;

		Ajax.call('user_order.php?act=order_to_query', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
	});

	$.divselect("#dateTime","#dateTime_val",function(){
		var order    = new Object();
		var key		 = $("#dateTime_val").val();
		var status_keyword = $("input[name='order_status_list']").val();

		order.idTxt   	= $("#time_"+key+" a").attr('data-idTxt');
		order.keyword   = $("#time_"+key+" a").attr('data-id');
		order.action  	= $("#time_"+key+" a").attr('data-action');
		order.type   	= $("#time_"+key+" a").attr('data-type');
		order.status_keyword   = status_keyword;

		Ajax.call('user_order.php?act=order_to_query', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
	});
</script>

@endif



@if($action == 'wholesale_buy')

<script type="text/javascript">
	$.divselect("#wholesale_order_status","#wholesale_order_status_val",function(){
		var order    = new Object();
		var key		 = $("#wholesale_order_status_val").val();
		var date_keyword = $("input[name='wholesale_order_submitDate']").val();

		order.idTxt   	= $("#wholesale_order_status_"+key+" a").attr('data-idTxt');
		order.keyword   = $("#wholesale_order_status_"+key+" a").attr('data-id');
		order.action  	= $("#wholesale_order_status_"+key+" a").attr('data-action');
		order.type   	= $("#wholesale_order_status_"+key+" a").attr('data-type');
		order.date_keyword   = date_keyword;

		Ajax.call('user_wholesale.php?act=wholesale_order_to_query', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
	});

	$.divselect("#DateTime","#wholesale_dateTime_val",function(){
		var order    = new Object();
		var key		 = $("#wholesale_dateTime_val").val();
		var status_keyword = $("input[name='wholesale_order_submitDate']").val();
		order.idTxt   	= $("#time_"+key+" a").attr('data-idTxt');
		order.keyword   = $("#time_"+key+" a").attr('data-id');
		order.action  	= $("#time_"+key+" a").attr('data-action');
		order.type   	= $("#time_"+key+" a").attr('data-type');
		order.status_keyword   = status_keyword;

		Ajax.call('user_wholesale.php?act=wholesale_order_to_query', 'order=' + $.toJSON(order), orderResponse, 'POST', 'JSON');
	});

	$(document).on("click","*[ectype='userWhoConfirm']",function(){
		var orderid = $(this).data("orderid");
		pbDialog("{{ $lang['true_order_ss_received'] }}","",0,"","","",true,function(){
			location.href="user_wholesale.php?act=wholesale_affirm_received&order_id="+ orderid;
		});
	});
</script>

@endif



@if($action == "account_deposit")

<script type="text/javascript">
$(function(){
	/* 返回实名认证页面 */
	$("#goback_account_log").click(function(){
		location.href = "user.php?act=account_log";
	});

	$("#submitBtn").click(function(){
		if($("#formSurplus").valid()){
			//防止表单重复提交
			if(checkSubmit() == true){
				$(this).parents("form").submit();
			}
			return false;
		}
	});

	$('#formSurplus').validate({
		errorPlacement:function(error, element){
			var error_div = element.parents('div.value').find('div.form_prompt');
			//element.parents('div.label_value').find(".notic").hide();
			error_div.append(error);
		},
		rules : {
			amount : {
				required : true,
				number:true,
				min : {{ $buyer_recharge }}
			}
		},
		messages : {
			amount : {
				required : json_languages.recharge_amount_null,
				number:json_languages.recharge_amount_wrongful,
				min:json_languages.recharge_amount_lower+{{ $buyer_recharge }}+"{{ $lang['yuan'] }}"
			}
		}
	});

});
</script>

@endif



@if($action == 'account_raply')

<script type="text/javascript">
	$(function(){
		$("#submitBtn").click(function(){
			if($("#formSurplus").valid()){
				//防止表单重复提交
				if(checkSubmit() == true){
					$(this).parents("form").submit();
				}
				return false;
			}
		});

		$('#formSurplus').validate({
			errorPlacement:function(error, element){
				var error_div = element.parents('div.value').find('div.form_prompt');
				//element.parents('div.label_value').find(".notic").hide();
				error_div.append(error);
			},
			rules : {
				amount : {
					required : true,
					number:true,
                    min : {{ $buyer_cash }},
					max : {{ $user_info['user_money'] }}
				},
				mobile_code:{
					required : true,
					remote : {
						cache: false,
						async:false,
						type:'POST',
						url:'ajax_user.php?act=code_notice',
						data:{
							mobile_code:function(){
								return $("input[name='mobile_code']").val();
							}
						}
					}
				}
			},
			messages : {
				amount : {
					required : json_languages.amount_of_cash_null,
					number:json_languages.amount_of_cash_wrongful,
                    min:json_languages.amount_of_cash_min+{{ $buyer_cash }}+"{{ $lang['yuan'] }}",
					max:json_languages.amount_of_cash_max+{{ $user_info['user_money'] }}+"{{ $lang['yuan'] }}"
				},
				mobile_code : {
					required : json_languages.mobile_code_null,
					remote:json_languages.mobile_code_incorrect
				}
			}
		});
	});
</script>

@endif



@if($action == 'wholesale_purchase')

<script type="text/javascript">
	$(".t-images").slide({"mainCell":".t-images-info ul",effect:"left",trigger:"click",pnLoop:false,autoPage:true,vis:2,scroll:1});

	var opts1 = {
		'targetId':'start_date',//时间写入对象的id
		'triggerId':['start_date'],//触发事件的对象id
		'alignId':'text_time1',//日历对齐对象
		'format':'-',//时间格式 默认'YYYY-MM-DD HH:MM:SS'
		'min':'' //最小时间
	},opts2 = {
		'targetId':'end_date',
		'triggerId':['end_date'],
		'alignId':'text_time2',
		'format':'-',
		'min':''
	}
	xvDate(opts1);
	xvDate(opts2);
</script>

@endif



@if($action == 'want_buy')

<script type="text/javascript">
	$(".t-images").slide({"mainCell":".t-images-info ul",effect:"left",trigger:"click",pnLoop:false,autoPage:true,vis:2,scroll:1});

	var opts1 = {
		'targetId':'wantbut_start_date',//时间写入对象的id
		'triggerId':['wantbut_start_date'],//触发事件的对象id
		'alignId':'text_time1',//日历对齐对象
		'format':'-',//时间格式 默认'YYYY-MM-DD HH:MM:SS'
		'min':'' //最小时间
	},opts2 = {
		'targetId':'wantbut_end_date',
		'triggerId':['wantbut_end_date'],
		'alignId':'text_time2',
		'format':'-',
		'min':''
	}
	xvDate(opts1);
	xvDate(opts2);
</script>

@endif



@if($action == 'profile')

<script type="text/javascript">

@if($profile['user_picture'])

	var user_img='{{ $profile['user_picture'] }}?&'+Math.random();

@else

	var user_img="{{ skin('images/touxiang.jpg') }}";

@endif


	$("*[ectype='up-pre-before']").find("img").attr('src',user_img)
	$("*[ectype='upImgTouch'] img").attr('src',user_img);

    //修改用户昵称
    function updateNick(val){
        console.log(val)
        var length = getStringLen(val);

        if(length > 20){
            $("[ectype='submit']").addClass("sc-btn-disabled").removeClass("sc-redBg-btn");
        }else{
            $("[ectype='submit']").removeClass("sc-btn-disabled").addClass("sc-redBg-btn");
        }
    }

    function getStringLen(str){
        let i,len,code;
        if(str == null || str == '') return 0
        len = str.length;

        for(i = 0; i < len; i++){
            code = str.charCodeAt(i);
            if(code > 255){
                len++;
            }
        }
        return len;
    }

	//提交修改个人信息表单
	$("a[ectype='submit']").on("click",function(){
        if(!$(this).hasClass("sc-btn-disabled")){
		  $("form[name='formEdit']").submit();
        }else{
            pbDialog(json_languages.max_nick_name,"",0);
        }
	});

	//上传头像
	$(function(){
		//初始化
		var $image = $("*[ectype='image']"),//图片对象
			$inputImage = $("*[ectype='fileImage']"),//上传file按钮
			$modalDoc = $("*[ectype='docMdal']");//上传弹出层
			$modalLoad = $("*[ectype='modalLoad']"),//加载层
			$modalAlert = $("*[ectype='modalAlert']"),//提示层
			$alertContent = $modalAlert['find']("*[ectype='alertContent']");//提示层内容

		var	URL = window.URL || window.webkitURL;
		var	blobURL;

		$image['cropper']({
			aspectRatio:"1",
			autoCropArea:0.8,
			preview:".up-pre-after"
		});

		//弹出上传图片栏
		$("*[ectype='upImgTouch']").click(function(){
			$modalDoc['modal']({width:'600px'});
		});

		//上传图片
		if(URL){
			$inputImage['change'](function(){
				var files = this.files;
				var file;

				if(files && files.length){
				   file = files[0];

				   if(/^image\/\w+$/.test(file.type)){
						blobURL = URL.createObjectURL(file);
						$image['one']('built.cropper',function(){
						   URL.revokeObjectURL(blobURL);
						}).cropper('reset').cropper('replace', blobURL);
						//$inputImage['val']('');
					}else{
						window.alert('{{ $lang['select_img_file'] }}');
					}
				}

				// Amazi UI 上传文件显示代码
				var fileNames = '';
				$.each(this.files,function(){
					fileNames += '<span class="am-badge">' + this.name + '</span>';
				});

				$('#file-list').html(fileNames);
			});
		}else{
			$inputImage['prop']('disabled', true).parent().addClass('disabled');
		}

		//绑定上传事件
		$("*[ectype='upBtnOk']").on('click',function(){
			var img_src = $image['attr']("src");
			var url = $(this).attr("url");
			var canvas = $image['cropper']('getCroppedCanvas');
			var data = canvas.toDataURL(); //转成base64

			//判断上传图片是否为空
			if(img_src == ""){
				//提示层赋值后，弹出提示
				$alertContent['html']("<div class='error'>{{ $lang['not_select_img_file'] }}</div>");
				$modalAlert['modal']();
				return false;
			}

			$.ajax({
				url:url,
				dataType:"json",
				type: "POST",
				data: {"image":data.toString()},
				success: function(data,textStatus){
					//提示层赋值后，弹出提示
					$alertContent['html']("<div class='success'>" + data.result + "</div>");
					$modalAlert['modal']();

					if(data.error == "ok"){
						$("*[ectype='upImgTouch'] img").attr("src",data.file+'?&'+Math.random());

						var img_name = data.file.split('/')[2];

					}
				},
				error: function(){
					//提示层赋值后，弹出提示
					$alertContent['html']("<div class='error'>{{ $lang['update_file_error'] }}</div>");
					$modalAlert['modal']();
				}
			});
		});

		//图片旋转
		$("*[ectype='rotate']").on("click",function(){
			var type = $(this).data("type")
				val = 0;

			if(type == "left"){
				val = -90;
			}else{
				val = 90
			}

			if($image['attr']("src") != ""){
				$image['cropper']('rotate', val);
			}else{
				//提示层赋值后，弹出提示
				$alertContent['html']("<div class='error'>{{ $lang['select_img_file'] }}</div>");
				$modalAlert['modal']();
			}
		});

		$(".am-modal-btn").click(function(){
			$modalDoc['modal']();
		});
	});
</script>

@endif



@if($action == 'return_list')

<script type="text/javascript">
	//删除已完成退换货订单 start
	function get_order_delete_return(order_id){
		var order = new Object();
		order.order_id   = order_id;
		var firm = json_languages.operation_order_one;
		if(confirm(firm)){
			Ajax.call('user_order.php?act=order_delete_return', 'order=' + $.toJSON(order),function(result){
				if(result.error == 0){
					$("#return_list").html(result.content);
					$(".red").text(result.pager.record_count);
				}
			}, 'POST', 'JSON');
		}
	}
</script>

@endif



@if($action == 'wholesale_return_list')

<script type="text/javascript">
    //删除已完成退换货订单 start
    function get_wholesale_order_delete_return(order_id){
        var order = new Object();
        order.order_id   = order_id;
        var firm = json_languages.operation_order_one;
        if(confirm(firm)){
            Ajax.call('user_wholesale.php?act=wholesale_order_delete_return', 'order=' + $.toJSON(order),function(result){
                if(result.error == 0){
                    $("#return_list").html(result.content);
                    $(".red").text(result.pager.record_count);
                }
            }, 'POST', 'JSON');
        }
    }
</script>

@endif



@if($action == 'apply_return' || $action == 'wholesale_apply_return')


@if($goods_return['rec_id'])

<script type="text/javascript">
//退换货上传图片，支持多图片选择上传
var uploader_gallery = new plupload.Uploader({//创建实例的构造方法
	runtimes: 'html5,flash,silverlight,html4', //上传插件初始化选用那种方式的优先级顺序
	browse_button: 'uploadbutton', // 上传按钮
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
	url: "return_images.php?act=ajax_return_images&rec_id={{ $goods_return['rec_id'] }}&userId={{ $user_id }}&sessid={{ $sessid }}&upload_type="+$("#uploadbutton").data('upload_type'), //远程上传地址
	filters: {
		max_file_size: '2mb', //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
		mime_types: [//允许文件上传类型
			{title: "files", extensions: "bmp,gif,jpg,png,jpeg"}
		]
	},
	multi_selection: true, //true:ctrl多文件上传, false 单文件上传
	init: {
	   FilesAdded: function(up, files) { //文件上传前
			var len = $(".img-list-li li").length;
			plupload.each(files, function(file){ //遍历文件
				len ++;
			});
			if(len > 10){
				pbDialog(json_languages.comment_img_number,"",0);
			}else{
				//开始上传单张循环上传
				submitBtn();
			}
		},
		FileUploaded: function(up, file, info) { //文件上传成功的时候触发

			var str_eval = eval;
			var data = str_eval("(" + info.response + ")");
			if(data.error == 2){
				pbDialog(json_languages.login_error,"",0);
				return;
			}else{
				$(".mslist").html(data.content);
				$(".return_images").show();
				mscoll();
			}
		},
		UploadComplete:function(up,file){//所有文件上传成功时触发

		},
		Error: function(up, err) { //上传出错的时候触发
			pbDialog(err.message,"",0);
		}
	}
});
uploader_gallery.init();

$(function(){
	if($("input[name='return_type']").val() != 2){

		$("*[ectype='return-type']").hide();
	}
	$("input[name='return_type']").on("click",function(){
		var val = $(this).val(),
			list = $("*[ectype='return_lists']"),
			div = list.find("*[ectype='return_div']");

		div.eq(val).show().siblings().hide();

		if(val == 0){
			$("*[ectype='return-type']").show();
		}else if(val == 1){
			$("*[ectype='return-type']").hide();
		}else if(val == 2){
			$("*[ectype='return-type']").show();
		}else{
			$("*[ectype='return-type']").hide();
			div.hide();
		}
	});

	$("input[name='return_type']:first").click(); //默认退换货服务类型第一个选中点击

	//清空上传的图片
	var rec_id = $("input[name='return_rec_id']").val();

	$('.clear_pictures').click(function(){
		Ajax.call('return_images.php?act=clear_pictures&upload_type='+$("#uploadbutton").data('upload_type'), '&rec_id=' + rec_id,function(res){
			$('.mslist').html(res.content);
			$('.return_images').hide();
		}, 'POST', 'JSON');
	});
});

function submitBtn(){
	uploader_gallery.setOption("multipart_params");//设置传参
	uploader_gallery.start();//开始控件
};

/**选择退换货原因*/
function selectCause(val, rec_id) {
	if (val > 0) {
		Ajax.call('user_order.php?act=ajax_select_cause', 'c_id=' + val + '&rec_id=' + rec_id,function(res){
			rec_id = res.rec_id;
			$('#children_' + rec_id).html(res.option);
		}, 'POST', 'JSON');
	}
	else {
		$('#children_' + rec_id).html("");
	}
}

/**维修退货数量控制 **/
function check_return_num(obj, num, rec_id, type) {
	if ($(obj).val() > num) {
		$(obj).val(num);
	}
	if ($(obj).val() <= 0) {
		$(obj).val(1);
	}
	var val = $(obj).val();

	if(type == 1){
		$('#maintain_span_' + rec_id).html(val);
	}else if(type == 2){
		$('#retired_' + rec_id).html(val);
	}
}

/**换货数量控制 **/
function check_attr_num(obj, num, rec_id) {
	var val = $(obj).val();
	if (val > num) {
		val = num;
		$(obj).val(num);
	}
	if (val <= 0) {
		val = 1;
		$(obj).val(1);
	}

	$('#retired1_' + rec_id).html(val);
}

/**选择商品属性*/
function setChange(id, t, attr_id) {
	$(t).addClass("cattsel").siblings().removeClass("cattsel");

	$('#attr_' + attr_id).val(id);
}

/**退换货数量变化*/
var buyNumber = {
	maxNumber : 100,
	minNumber : 1,
	rec_id : $("input[name='rec_id']").val(),

	defaultNumber : function(inputName){
		var defaultnumber = $("input[name='" +inputName+ "']").attr('defaultnumber');
		defaultnumber = parseInt(defaultnumber)
		if(defaultnumber < 1){
			defaultnumber = 1;
		}
		return defaultnumber;
	},

	goodNumber : function(type, inputName, lastSpan, num){
		if(typeof(num) == 'number' && type == 2){
			lastSpan.html(num);
			return $("input[name='" +inputName+ "']").val(num);
		}else{
			return parseInt($("input[name='" +inputName+ "']").val());
		}
	},
	plus : function(obj, obj_type){
		var input_name = $(obj).parent().find('input').attr('name');

		if(obj_type == 1){
			var lastSpan = $(obj).parent().find('span').last();
		}else if(obj_type == 2){
			var lastSpan = $('#retired1_' + buyNumber.rec_id);
		}

		var num = buyNumber.goodNumber(1, input_name) + buyNumber.defaultNumber(input_name);
		var return_number = $("input[name='return_g_number']").val();

		if(num > return_number){
			pbDialog(json_languages.change_two +return_number+ json_languages.jian,"",0);
			num = return_number;
		}

		if(num <= buyNumber.maxNumber){
			buyNumber.goodNumber(2, input_name, lastSpan, num);
		}
	},
	minus : function(obj, obj_type){
		var input_name = $(obj).parent().find('input').attr('name');

		if(obj_type == 1){
			var lastSpan = $(obj).parent().find('span').last();
		}else if(obj_type == 2){
			var lastSpan = $('#retired1_' + buyNumber.rec_id);
		}

		var num = buyNumber.goodNumber(1, input_name) - buyNumber.defaultNumber(input_name);

		if(num >= buyNumber.minNumber){
			buyNumber.goodNumber(2, input_name, lastSpan, num);
		}
	}
}

/**大于5张图片 左右显示滚动切换*/
function mscoll(){
	$(".mscoll").slide({mainCell:".mslist ul",effect:"left",pnLoop:false,autoPlay:false,autoPage:true,scroll:1,vis:5});
}
mscoll();

/**提交验证表单*/
function check_sub() {
	var frm = $('#formReturn');
	var rec_id = $("input[name='return_rec_id']").val();

	if($("#cause_" + rec_id).val() == 0){
		pbDialog(json_languages.return_one,"",0);
		return false;
	}else{
		if($("#last_option_" + rec_id).val() == 0){
			pbDialog(json_languages.return_two,"",0);
			return false;
		}
	}

	if($(".text_desc").val() == ''){
		pbDialog(json_languages.return_three,"",0);
		return false;
	}

	var selCountries = $('#selCountries_0').val();
	var selProvinces = $('#selProvinces_0').val();
	var selCities = $('#selCities_0').val();

	if(selCountries == 0){
		pbDialog(json_languages.return_four,"",0);
		return false;
	}else if(selProvinces == 0){
		pbDialog(json_languages.selProvinces,"",0);
		return false;
	}else if(selCities == 0){
		pbDialog(json_languages.selCities,"",0);
		return false;
	}else{
		var selDistricts = $('#selDistricts_0');
		if(selDistricts.attr('style') != 'display:none'){
			if(selDistricts.val() == 0){
				pbDialog(json_languages.selDistricts,"",0);
				return false;
			}
		}
	}

	if($("input[name='return_address']").val() == ''){
		pbDialog(json_languages.address_empty,"",0);
		return false;
	}

	if($("input[name='addressee']").val() == ''){
		pbDialog(json_languages.Contact_name_empty,"",0);
		return false;
	}

	if($("input[name='mobile']").val() != ''){
		if($("input[name='mobile']").val().length != 11){
			pbDialog(json_languages.phone_format_error,"",0);
			return false;
		}
	}else{
		pbDialog(json_languages.msg_phone_blank,"",0);
		return false;
	}

	frm.action = frm.action + '?act=submit_return';
	frm.submit();
	return true;
}

$.levelLink();
</script>

@endif


@endif



@if($action == 'batch_applied' || $action == 'wholesale_batch_applied')

<script type="text/javascript">
//退换货上传图片，支持多图片选择上传
var uploader_gallery = new plupload.Uploader({//创建实例的构造方法
	runtimes: 'html5,flash,silverlight,html4', //上传插件初始化选用那种方式的优先级顺序
	browse_button: 'uploadbutton', // 上传按钮
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
	url: "return_images.php?act=ajax_return_images&rec_ids={{ $rec_ids }}&userId={{ $user_id }}&sessid={{ $sessid }}&upload_type="+$("#uploadbutton").data('upload_type'), //远程上传地址
	filters: {
		max_file_size: '2mb', //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
		mime_types: [//允许文件上传类型
			{title: "files", extensions: "bmp,gif,jpg,png,jpeg"}
		]
	},
	multi_selection: true, //true:ctrl多文件上传, false 单文件上传
	init: {
	   FilesAdded: function(up, files) { //文件上传前
			var len = $(".img-list-li li").length;
			plupload.each(files, function(file){ //遍历文件
				len ++;
			});
			if(len > 10){
				pbDialog(json_languages.comment_img_number,"",0);
			}else{
				//开始上传单张循环上传
				submitBtn();
			}
		},
		FileUploaded: function(up, file, info) { //文件上传成功的时候触发
			var str_eval = eval;
			var data = str_eval("(" + info.response + ")");
			if(data.error == 2){
				pbDialog(json_languages.login_error,"",0);
				return;
			}else{
				$(".mslist").html(data.content);
				$(".return_images").show();
				mscoll();
			}
		},
		UploadComplete:function(up,file){//所有文件上传成功时触发

		},
		Error: function(up, err) { //上传出错的时候触发
			pbDialog(err.message,"",0);
		}
	}
});
uploader_gallery.init();

$(function(){
	if($("input[name='return_type']").val() != 2){
		$("*[ectype='return-type']").hide();
	}
	$("input[name='return_type']").on("click",function(){
		var val = $(this).val(),
			list = $("*[ectype='return_lists']"),
			div = list.find("*[ectype='return_div']");

		div.eq(val).show().siblings().hide();

		if(val == 0){
			$("*[ectype='return-type']").show();
		}else if(val == 1){
			$("*[ectype='return-type']").hide();
		}else if(val == 2){
			$("*[ectype='return-type']").show();
		}else{
			$("*[ectype='return-type']").hide();
			div.hide();
		}
	});


	//清空上传的图片
	$('.clear_pictures').click(function(){
		Ajax.call('return_images.php?act=clear_pictures&upload_type='+$("#uploadbutton").data('upload_type'), '&rec_ids={{ $rec_ids }}',function(res){
			$('.mslist').html(res.content);
			$('.return_images').hide();
		}, 'POST', 'JSON');
	});
});

function submitBtn(){
	uploader_gallery.setOption("multipart_params");//设置传参
	uploader_gallery.start();//开始控件
};

/**选择退换货原因*/
function selectCause(val, rec_id) {
	if (val > 0) {
		Ajax.call('user_order.php?act=ajax_select_cause', 'c_id=' + val + '&rec_id=' + rec_id,function(res){
			rec_id = res.rec_id;
			$('#children_' + rec_id).html(res.option);
		}, 'POST', 'JSON');
	}
	else {
		$('#children_' + rec_id).html("");
	}
}

/**大于5张图片 左右显示滚动切换*/
function mscoll(){
	$(".mscoll").slide({mainCell:".mslist ul",effect:"left",pnLoop:false,autoPlay:false,autoPage:true,scroll:1,vis:5});
}
mscoll();

/**提交验证表单*/
function check_sub() {
	var frm = $('#formReturn');

	if($(".text_desc").val() == ''){
		pbDialog(json_languages.return_three,"",0);
		return false;
	}

	var selCountries = $('#selCountries_0').val();
	var selProvinces = $('#selProvinces_0').val();
	var selCities = $('#selCities_0').val();

	if(selCountries == 0){
		pbDialog(json_languages.return_four,"",0);
		return false;
	}else if(selProvinces == 0){
		pbDialog(json_languages.selProvinces,"",0);
		return false;
	}else if(selCities == 0){
		pbDialog(json_languages.selCities,"",0);
		return false;
	}else{
		var selDistricts = $('#selDistricts_0');
		if(selDistricts.attr('style') != 'display:none'){
			if(selDistricts.val() == 0){
				pbDialog(json_languages.selDistricts,"",0);
				return false;
			}
		}
	}

	if($("input[name='return_address']").val() == ''){
		pbDialog(json_languages.address_empty,"",0);
		return false;
	}

	if($("input[name='addressee']").val() == ''){
		pbDialog(json_languages.Contact_name_empty,"",0);
		return false;
	}

	if($("input[name='mobile']").val() != ''){
		if($("input[name='mobile']").val().length != 11){
			pbDialog(json_languages.phone_format_error,"",0);
			return false;
		}
	}else{
		pbDialog(json_languages.msg_phone_blank,"",0);
		return false;
	}

	frm.action = frm.action + '?act=submit_batch_return';
	frm.submit();
	return true;
}

$.levelLink();
</script>

@endif



@if($action == 'goods_order' || $action == 'wholesale_goods_order')

<script type="text/javascript">
	$("*[ectype='submitBtn']").click(function(){
		$("#theFrom").submit();
	});
</script>

@endif



@if($action == 'track_packages')


@if($orders)

<script language="javascript">
	// $(".user-trackpack .item").on("click",function(){
	// 	var id = $(this).data('orderid');
	// 	var expressid = $("#shipping_name_"+id).html();
	// 	var expressno = $("#invoice_no_"+id).html();

	// 	//$("#retData_"+id).html("<center>"+logistics_tracking_in+"</center>");
	// 	$.ajax({
	// 		url: "ajax_user.php?act=query_express",
	// 		type: "post",
	// 		data:'com=' + expressid + '&nu=' + expressno,
	// 		success: function(data,textStatus){
	// 			$("#retData_"+id).html(data);
	// 		},
	// 		 error: function(o){
	// 		}
	// 	});
	// });
</script>

@endif


@endif



@if($action == 'application_grade' || $action == 'application_grade_edit')

<script language="JavaScript">
   $("a[ectype='submit']").on("click",function(){
		var frm  = $("form[name='grade_theForm']");
		var count_charge = frm.find("input[name='all_count_charge']").val();
		var msg = '';

@foreach($entry_criteriat_info as $info)

@if($info['child'])


@foreach($info['child'] as $child)


@if($child['is_mandatory'] == 1 && $child['type'] != 'charge')


							var name="value[{{ $child['id'] }}]";
							var prompt  = "{{ $child['criteria_name'] }}"+json_languages.cannot_null
							var a  = frm.find("input[name='"+name+"']").val();
							var error = frm.find("input[name='"+name+"']").nextAll('.notic');

@if($child['type'] == 'file' && $action == 'application_grade_edit')

								a = '1';

@endif

							if(a.length == 0){
							   error.html(prompt);
							   msg += prompt + '\n';
							}


@endif


@endforeach


@endif


@endforeach


		if(count_charge > 0){
			var prompt = '<div class="notic">' + json_languages.select_payment_pls + '</div>';
			 var pay_id  = frm.find("input[name='pay_id']").val();
			 if(pay_id.length == 0){
				 $(".pay_id_erro").append(prompt);
				  msg += prompt + '\n';
			 }
		}
		if (msg.length > 0){
			return false;
		}else{
			frm.submit();
		}
	});

	window.onload = function()
	{
		add_charge();
	}

	/*年限操作发生事件*/
	function add_charge(i){
		var re = /^[1-9]+[0-9]*]*$/; //大于0整数数字
		var all_count_charge = document.forms['grade_theForm'].elements['all_count_charge'];
		var price_one = document.forms['grade_theForm'].elements['count_charge'].value;
		var num = document.forms['grade_theForm'].elements['fee_num'];
		var count = document.getElementById('count_charge');
        var no_cumulative_price = document.forms['grade_theForm'].elements['no_cumulative_price'].value;
		if(i == 'reduce'){
			if(num.value == 1){
				pbDialog(json_languages.settled_down_lt1,"",0);
			}else{
				if(num.value > 1){
					price_one = Number(price_one) - Number(no_cumulative_price);
				}
				num.value = parseInt(num.value)-1;
				all_count_charge.value = count.innerHTML = Number(count.innerHTML) - Number(price_one);
			}
		}else if(i == 'reset'){
			 all_count_charge.value = count.innerHTML = Number(price_one);
		}else if(i == 'add'){
			num.value = parseInt(num.value)+1;
			if(num.value > 1){
				price_one = Number(price_one) - Number(no_cumulative_price);
			}
			all_count_charge.value  = count.innerHTML = Number(count.innerHTML) + Number(price_one);
		}else if(i == 'keyup'){
			if(!re.test(num.value)){
				pbDialog(json_languages.Wrongful_input,"",0);
			}else{
				price_one = Number(price_one) - Number(no_cumulative_price);
				all_count_charge.value =  count.innerHTML = parseInt(num.value) * Number(price_one) + Number(no_cumulative_price);
			}
		}else{
			price_one = Number(price_one) - Number(no_cumulative_price);
            all_count_charge.value =  count.innerHTML = parseInt(num.value) * Number(price_one) + Number(no_cumulative_price);
		}
	}
</script>

@endif



@if($action == 'value_card')

<script type="text/javascript">
$("*[ectype='submitVC']").on("click",function(){

	new_addVC();

});
function new_addVC(){
	var vc = new Object;
	var message;

	vc.value_card_sn = $(".txt_input_cardnum").val();
	vc.password = $(".txt_input_cardpw").val();
	vc.captcha = $(":input[name='captcha']").val();

	if(vc.value_card_sn == ''){
		message = json_languages.card_number_null;
	}else if(vc.password == ''){
		message = json_languages.Real_name_password_null;
	}else if(vc.captcha == ''){
		message = json_languages.null_captcha_login;
	}

	if(vc.value_card_sn != '' && vc.password != '' && vc.captcha != ''){
		Ajax.call('ajax_user.php', 'act=add_value_card&vc=' + $.toJSON(vc), function(data){
			if(data.error == 2){
				var back_url = "user.php?act=value_card";
				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
			}else if(data.error == 3){
				pbDialog(data.message,"",0);
			}else if(data.error == 0){
				pbDialog(data.message,"",1,"","","",true,function(){
					 location.href = "user.php?act=value_card";
				});
			}else{
				pbDialog(data.message,"",0,"","",70);
			}
		}, 'POST', 'JSON');
	}else{
		pbDialog(message,"",0);
	}
}

function to_pay(vid){
	Ajax.call("get_ajax_content.php?act=to_pay_card&vid="+vid, '', function(data){
		pb({
			id:"storeDialogBody",
			title:"{{ $lang['value_card_filling'] }}",
			width:450,
			height:250,
			content:data.content, 	//调取内容
			drag:false,
			foot:false
		});
	}, 'POST','JSON');

}
function remove_bind(vid){
	Ajax.call("get_ajax_content.php?act=remove_bind&vid="+vid, '', function(data){
		pb({
			id:"storeDialogBody",
			title:"{{ $lang['value_card_unwrap'] }}",
			width:450,
			height:250,
			content:data.content, 	//调取内容
			drag:false,
			foot:false
		});
	}, 'POST','JSON');

}
</script>

@endif



@if($action == 'to_paid')

<script type="text/javascript">
$("*[ectype='submitVC']").on("click",function(){
	new_addPC();
});
function new_addPC(){
	var pc = new Object;
	var message;

	pc.pay_card_sn = $(".txt_input_cardnum2").val();
	pc.vid = {{ $vid }};
	pc.password = $(".txt_input_cardpw2").val();
	pc.captcha = $(".captcha_error2").val();

	if(pc.pay_card_sn == ''){
		message = json_languages.card_number_null;
	}else if(pc.password == ''){
		message = json_languages.Real_name_password_null;
	}else if(pc.captcha == ''){
		message = json_languages.null_captcha_login;
	}

	if(pc.pay_card_sn != '' && pc.password != '' && pc.captcha != ''){
		Ajax.call('ajax_user.php', 'act=use_pay_card&pc=' + $.toJSON(pc), function(data){
			if(data.error == 2){
				pbDialog(data.message,"",0,"","","",true,function(){
					location.href = "user.php";
				});
			}else if(data.error == 3){
				pbDialog(data.message,"",0);
			}else if(data.error == 0){
				pbDialog(data.message,"",1,"","","",true,function(){
					 location.href = "user.php?act=value_card";
				});
			}else{
				pbDialog(data.message,"",0,"","",70);
			}
		}, 'POST', 'JSON');
	}else{
		pbDialog(message,"",0);
	}
}
</script>

@endif



@if($action == 'crowdfunding')

<script type="text/javascript">
$(function(){
    $(document).on("click","*[data-dialog='zc_focus_dialog']",function(){
        var divId = $(this).data('divid');
        var url = $(this).data('url');
        var result = json_languages.cancel_zc;
        var content = '<div class="tip-box icon-box">' +'<span class="warn-icon m-icon"></span>' + '<div class="item-fore">' +'<h3 class="rem ftx-04">'+result+'</h3>' +'</div>' +'</div>';
        pb({
            id:divId,
            title:json_languages.no_attention,
            width:455,
            height:58,
            ok_title:json_languages.determine, 	//按钮名称
            cl_title:json_languages.cancel, 	//按钮名称
            content:content, 	//调取内容
            drag:false,
            foot:true,
            onOk:function(){
                location.href = url;
            }
        });

        $('#' + divId + ' .tip-box .pb-ok').addClass('color_df3134');
    });

	$('.ui-tab a').click(function(){
		var index = $(this).data('val');
		$(this).addClass('curr').siblings("a").removeClass('curr');
		$(this).parents(".my_box").find(".pay_item").eq(index-1).show().siblings(".pay_item").hide();
	});
});
</script>

@endif



@if($action == 'account_bind')

<script type="text/javascript">
//购物车弹出框效果
$(document).on("click","*[data-dialog='oathdialog']",function(){

	var title = json_languages.Un_bind;
	var id = $(this).data('id');
	var username = $(this).data('username');
	var identity = $(this).data('identity');

	var title_h3 = json_languages.bind_user_one + title + identity + json_languages.account_bind_fuor + '？';
	var title_span = json_languages.account_bind_five+'{{ $dwt_shop_name }}'+ json_languages.account_bind_fuor + '：' + username + json_languages.registered +'.';

	pbDialog(title_h3,title_span,0,455,58,80,true,function(){
		Ajax.call('user.php?act=oath_remove', 'id=' + id + '&identity=' + identity, oath_removeResponse, 'POST','JSON');
	});
});

function oath_removeResponse(result){
	location.reload();
}
</script>

@endif



@if($action == 'account_safe')

<script type="text/javascript">
	/* 账号安全 使用手机验证、支付密码验证、使用邮箱验证、修改登录密码 提交表单 */
	$("*[ectype='submitBtn']").click(function(){
		var formName = $(this).parents("*[ectype='user_security_from']").attr("name");

		$("form[name='"+formName+"']").validate({
			errorPlacement:function(error, element){
				var error_div = element.parents('div.form-value').find('div.form_prompt');
				error_div.html("").append(error);
			},
			ignore : ""
		});

		//所有账户安全验证方式 验证码验证通用
        $("input[name='authCode']").rules("add",{
            required : true,
            remote : {
                cache: false,
                async:false,
                type:'POST',
                url:'user.php?act=account_safe&type=change_password&verify=authcode',
                data:{
                    authCode:function(){
                        return $("input[name='authCode']").val();
                    }
                }
            },
            messages : {
                required : json_languages.null_captcha_login,
                remote : json_languages.null_captcha_error
            }
        });



		//修改登录密码和修改支付密码 第二步 填写新密码验证
		if(formName == "change_password_s" || formName == "payment_password"){
			var newpass = "",
				re_newpass = "",
				re_newpass_yz = "";

			if(formName == "change_password_s"){
				newpass = json_languages.new_login_pwd_null;
				re_newpass = json_languages.new_login_pwd_error;
				re_newpass_yz = json_languages.secondary_login_pwd_error;
			}else{
				newpass = json_languages.new_pay_pwd_null;
				re_newpass = json_languages.new_pay_pwd_error;
				re_newpass_yz = json_languages.secondary_pay_pwd_error;
			}

			//新密码
			$("input[name='new_password']").rules("add",{
				required : true,
				messages : {
					required : newpass
				}
			});

			//确认新密码
			$("input[name='re_new_password']").rules("add",{
				required : true,
				equalTo : "#password",
				messages : {
					required : re_newpass,
					equalTo : re_newpass_yz
				}
			});
		}

		/* 账号安全 第一步 使用手机验证 提交表单验证 */
		if(formName == "formUser"){
			//手机号码
			$("input[name='mobile_phone']").rules("add",{
				required : true,
				isMobile : true,
				messages : {
					required : json_languages.msg_phone_blank,
					isMobile : json_languages.msg_phone_not
				}
			});

			//手机短信验证码
			$("input[name='mobile_code']").rules("add",{
				required : true,
				remote : {
					cache: false,
					async:false,
					type:'POST',
					url:'user.php?act=account_safe&type=change_password&verify=mobilecode',
					data:{
						mobile_code:function(){
							return $("input[name='mobile_code']").val();
						}
					}
				},
				messages : {
					required : json_languages.null_captcha_login_phone,
					remote : json_languages.error_captcha_login_phone
				}
			});
		}

		/* 账号安全 第一步 使用支付密码验证 提交表单验证 */
		if(formName == "formUserPwd"){
			//支付密码
			$("input[name='pay_password']").rules("add",{
				required : true,
				remote : {
					cache: false,
					async:false,
					type:'POST',
					url:'user.php?act=account_safe&type=change_password&verify=pay_pwd',
					data:{
						payPwd:function(){
							return $("input[name='pay_password']").val();
						}
					}
				},
				messages : {
					required : json_languages.pay_password_packup_null,
					remote : json_languages.pay_password_packup_error
				}
			});
		}

		/* 账号安全 第一步 使用邮箱验证 提交表单验证 */
		if(formName == "change_email_s"){
			//支付密码
			$("input[name='change_email']").rules("add",{
				required : true,
				email : true,
				messages : {
					required : json_languages.null_email_user,
					email:json_languages.email_error
				}
			});
		}

		if(formName == "change_email_s"){
			/* 账号安全 使用邮箱验证通过 提交表单 */
			if($("form[name='"+formName+"']").valid()){
				var type = $(this).data("type");
				sendChangeEmail(type);
			}
		}else{
			/* 账号安全 非邮箱验证通过 提交表单 */
			if($("form[name='"+formName+"']").valid()){
				$("form[name='"+formName+"']").submit();
			}
		}
	});

	/* 账户安全 实名认证 */
	$("#authSubmit").click(function(){
		if($("form[name='real_name']").valid()){
			$("form[name='real_name']").submit();
		}
	});

	$("form[name='real_name']").validate({
		errorPlacement:function(error, element){
			var error_div = element.parents('div.value').find('div.form_prompt');
			element.parents('div.value').find(".notic").hide();
			error_div.append(error);
		},
		ignore:"",
		rules : {
			real_name : {
				required : true
			},
			self_num:{
				required : true,
				isIdCardNo:true
			},
			bank_name:{
				required : true
			},
			bank_card:{
				required : true
			},
			textfile_zheng:{
				required : true
			},
			textfile_fan:{
				required : true
			},
			mobile_phone:{
				required : true,
				isMobile : true
			},
			mobile_code:{
				required : true,
				remote : {
					cache: false,
					async:false,
					type:'POST',
					url:'user.php?act=account_safe&type=change_password&verify=mobilecode',
					data:{
						mobile_code:function(){
							return $("input[name='mobile_code']").val();
						}
					}
				}
			}
		},
		messages : {
			real_name : {
				required : json_languages.Real_name_null
			},
			self_num:{
				required : json_languages.number_ID_null,
				isIdCardNo:json_languages.number_ID_error
			},
			bank_name:{
				required : json_languages.bank_of_deposit_null
			},
			bank_card:{
				required : json_languages.bank_number_null
			},
			textfile_zheng:{
				required : json_languages.front_pic_null
			},
			textfile_fan:{
				required : json_languages.reverse_pic_null
			},
			mobile_phone : {
				required : json_languages.msg_phone_blank,
				isMobile : json_languages.msg_phone_not,
			},
			mobile_code : {
				required : json_languages.null_captcha_login_phone,
				remote : json_languages.error_captcha_login_phone
			}
		}
	});

	/* 账号安全 使用邮箱验证 使用发送邮箱 */
	function sendChangeEmail(mail_type){
		var authCode = $("input[name='authCode']").val(), 			//验证码
			mail_address = $("input[name='change_email']").val(),	//邮箱
			mail_address_data = '',									//ajax请求数据
			mail_url = '',											//ajax请求地址
			location_href_url = '';									//回调地址

		var isValidatedSize = $("input[name='is_validated']").size(),
			isValidated = '';

		if(mail_type == 'change_pwd'){
			mail_url = "user.php?is_ajax=1&act=account_safe&type=change_email&step=second_email_verify&mail_type=change_pwd";
			location_href_url = "user.php?act=account_safe&type=change_password&step=first&sign=validate_mail_ok";
		}else if(mail_type == 'change_mail'){
			mail_url = "user.php?is_ajax=1&act=account_safe&type=change_email&step=second_email_verify&mail_type=change_mail";
			location_href_url = "user.php?act=account_safe&type=change_email&step=first&sign=validate_mail_ok";
		}else if(mail_type == 'change_mobile'){
			mail_url = "user.php?is_ajax=1&act=account_safe&type=change_email&step=second_email_verify&mail_type=change_mobile";
			location_href_url = "user.php?act=account_safe&type=change_phone&step=first&sign=validate_mail_ok";
		}else if(mail_type == 'change_paypwd'){
			mail_url = "user.php?is_ajax=1&act=account_safe&type=change_email&step=second_email_verify&mail_type=change_paypwd";
			location_href_url = "user.php?act=account_safe&type=payment_password&step=first&sign=validate_mail_ok";
		}else if(mail_type == 'validate_mail'){
			mail_url = "user.php?is_ajax=1&act=account_safe&type=change_email&step=second_email_verify&mail_type=validate_mail";
			location_href_url = "user.php?act=account_safe&type=change_email&step=first&sign=validate_mail_ok";
		}else if(mail_type == 'edit_mail'){
			// 更改邮箱
			if(isValidatedSize == 1){
				isValidated = "&validated=1";
			}

			mail_url = "user.php?is_ajax=1&act=account_safe&type=change_email&step=second_email_verify&mail_type=edit_mail" + isValidated;
			location_href_url = "user.php?act=account_safe&type=change_email&step=second&sign=edit_email_ok";
		}

		//发送验证邮件
		$.ajax({
			cache: false,
			async: false,
			type: 'POST',
			data: { mail_address_data: mail_address },
			url: mail_url,
			dataType:'json',
			success: function (result) {
				if(result.error){
					pbDialog(result.message,"",0);

					return false;
				}else{
					window.location.href=location_href_url;
				}
			}
		});
	}
</script>

@endif


@if($action == 'address')

<script type="text/javascript">
	$("#submitAddress").on("click",function(){
		if($(this).parents("form[name='theForm']").valid()){
            if(checkSubmit() == true){
                $(this).parents("form[name='theForm']").submit();
            }
            return false;
        }
	});

	$("form[name='theForm']").validate({
		errorPlacement:function(error, element){
			var error_div = element.parents('div.form-value').find('div.form_prompt');
			element.parents('div.form-value').find(".notic").hide();
			error_div.html("").append(error);
		},
		ignore:".ignore",
		rules : {
			consignee : {
				required : true
			},
			mobile : {
				required : true,
				isMobile : true
			},
			province : {
				min : 1
			},
			city : {
				min : 1
			},
			district : {
				min : 1
			},
			street : {
				min : 1
			},
			address : {
				required : true
			}
		},
		messages : {
			consignee : {
				required : json_languages.common.address_empty
			},
			mobile : {
				required : json_languages.common.msg_phone_blank,
				isMobile : json_languages.common.phone_format_error
			},
			province : {
				min : json_languages.common.Province
			},
			city : {
				min : json_languages.common.City
			},
			district : {
				min : json_languages.common.District
			},
			street : {
				min : json_languages.common.Street
			},
			address : {
				required : json_languages.common.Detailed_address_null
			}
		}
	});
</script>

@endif


@if($action == 'apply_suppliers')

<script type="text/javascript">
    /* 账户安全 实名认证 */
    $("#authSubmit").click(function(){
        if($("form[name='apply_suppliers']").valid()){
            if(checkSubmit() == true){
                $("form[name='apply_suppliers']").submit();
            }
            return false
        }
    });

    $("form[name='apply_suppliers']").validate({
        errorPlacement:function(error, element){
            var error_div = element.parents('div.value').find('div.form_prompt');
            element.parents('div.value').find(".notic").hide();
            error_div.append(error);
        },
        ignore:".ignore",
        rules : {
            real_name : {
                required : true
            },
            self_num:{
                required : true,
                isIdCardNo:true
            },
            company_name:{
                required : true
            },
            company_address:{
                required : true
            },
            city:{
                required : true,
                min : 1
            },
            textfile_zheng:{
                required : true
            },
            textfile_fan:{
                required : true
            },
            mobile_phone:{
                required : true,
                isMobile : true
            },
            mobile_code:{
                required : true,
                remote : {
                    cache: false,
                    async:false,
                    type:'POST',
                    url:'user.php?act=account_safe&type=change_password&verify=mobilecode',
                    data:{
                        mobile_code:function(){
                            return $("input[name='mobile_code']").val();
                        }
                    }
                }
            }
        },
        messages : {
            real_name : {
                required : json_languages.Real_name_null
            },
            self_num:{
                required : json_languages.number_ID_null,
                isIdCardNo:json_languages.number_ID_error
            },
            company_name:{
                required : json_languages.company_name_notic,
            },
            company_address:{
                required : json_languages.company_address_notic,
            },
            city:{
                required : json_languages.enter_region,
                min : json_languages.enter_region
            },
            textfile_zheng:{
                required : json_languages.front_pic_null
            },
            textfile_fan:{
                required : json_languages.reverse_pic_null
            },
            mobile_phone : {
                required : json_languages.msg_phone_blank,
                isMobile : json_languages.msg_phone_not,
            },
            mobile_code : {
                required : json_languages.null_captcha_login_phone,
                remote : json_languages.error_captcha_login_phone
            }
        }
    });

    $.levelLink();//三级联动

    $.divselect("#first_cate","#first_cate_val",function(){
        val = $("#first_cate_val").val();
        var filter = new Object;
        filter.cat_id  = val;
        Ajax.call('user.php?is_ajax=1&act=addChildCate', filter, responseSelectChildCate, 'GET', 'JSON');
    })

    function responseSelectChildCate(result){

        var steps = document.getElementById('steps_re_span');

        if(result.error == 0){
            steps.innerHTML = result.content;
        }
    }
</script>

@endif

</body>
</html>
