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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/store_css.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/preview.css') }}">
</head>

<body>
	@include('frontend::library/page_header_common')
    <div class="container">
    	<div class="w w1200">
        	<div class="mallInfo mt20">
            	<div class="item">
                	<div class="label">{{ $lang['licence_seller'] }}：</div>
                    <div class="value">
                        <a href="{{ $merchants_url }}" class="ftx-05" target="_blank">{{ $shop_name }}</a>
                    </div>
                </div>

@if($grade_info['grade_img'])

                <div class="item">
                	<div class="label">{{ $lang['seller_aptitude'] }}：</div>
                    <div class="value">
@if($grade_info['grade_img'])
<img src='{{ $grade_info['grade_img'] }}' title='{{ $grade_info['grade_name'] }}' class="grade_img">
@else
{{ $lang['no_rank'] }}
@endif
</div>
                </div>

@endif

                <div class="item">
                	<div class="label">{{ $lang['composite_score'] }}：</div>
                    <div class="value dsc-store-item">
                        <div class="s-score">
                            <span class="score-icon"><span class="score-icon-bg" style="width:{{ $merch_cmt['cmt']['all_zconments']['allReview'] }}%;"></span></span>
                            <span>{{ $merch_cmt['cmt']['all_zconments']['score'] }}</span>
                            <i class="iconfont icon-down"></i>
                        </div>
                        <div class="s-score-info">
                            <div class="s-cover"></div>
                            <div class="g-s-parts">
                                <div class="parts-tit">
                                    <span class="col1">{{ $lang['Detailed_score'] }}</span>
                                    <span class="col2">&nbsp;</span>
                                    <span class="col3">{{ $lang['industry_compare'] }}</span>
                                </div>
                                <div class="parts-item parts-goods">
                                    <span class="col1">{{ $lang['evaluation_single'] }}</span>
                                    <span class="col2
@if($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 1)
ftx-02
@elseif ($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 2)
average
@else
ftx-01
@endif
">{{ $merch_cmt['cmt']['commentRank']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                                    <span class="col3">{{ $merch_cmt['cmt']['commentRank']['zconments']['up_down'] }}%</span>
                                </div>
                                <div class="parts-item parts-goods">
                                    <span class="col1">{{ $lang['service_attitude'] }}</span>
                                    <span class="col2
@if($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 1)
ftx-02
@elseif ($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 2)
average
@else
ftx-01
@endif
">{{ $merch_cmt['cmt']['commentServer']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                                    <span class="col3">{{ $merch_cmt['cmt']['commentServer']['zconments']['up_down'] }}%</span>
                                </div>
                                <div class="parts-item parts-goods">
                                    <span class="col1">{{ $lang['delivery_speed'] }}</span>
                                    <span class="col2
@if($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 1)
ftx-02
@elseif ($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 2)
average
@else
ftx-01
@endif
">{{ $merch_cmt['cmt']['commentDelivery']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                                    <span class="col3">{{ $merch_cmt['cmt']['commentDelivery']['zconments']['up_down'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ratingMore mt20">
            	<div class="rm_title">{{ $lang['rm_title'] }}</div>
                <ul>
                	<li><h2>{{ $lang['rm_tit'] }}</h2></li>
                    <li><span class="noMargin">{{ $lang['rm_wd_info_zz'] }}：</span></li>
                    <li>
                    	<div class="label">{{ $lang['companyName'] }}：</div>
                        <div class="value">{{ $basic_info['companyName'] }}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['business_license_id'] }}：</div>
                        <div class="value">{{ $basic_info['business_license_id'] }}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['legal_person'] }}：</div>
                        <div class="value">{{ $basic_info['legal_person'] }}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['license_comp_adress'] }}：</div>
                        <div class="value">{{ $basic_info['license_comp_adress'] }}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['registered_capital'] }}：</div>
                        <div class="value">{{ $basic_info['registered_capital'] }}万元</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['business_term'] }}：</div>
                        <div class="value">{{ $basic_info['business_term'] }}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['busines_scope'] }}：</div>
                        <div class="value">{{ $basic_info['busines_scope'] }}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['company_located'] }}：</div>
                        <div class="value">{!! $basic_info['company_located'] !!}</div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['shop_name'] }}：</div>
                        <div class="value">
                            <a href="{{ $merchants_url }}" class="ftx-05" target="_blank">{{ $shop_name }}</a>
                        </div>
                    </li>
                    <li>
                    	<div class="label">{{ $lang['merchants_url'] }}：</div>
                        <div class="value"><a class='ftx-05' href='{{ $merchants_url }}'target="_blank">{{ $merchants_url }}</a></div>
                    </li>
                    <li><h2>{{ $lang['rm_prompt_info'] }}</h2></li>
                    <li><span class="noMargin">{{ $lang['rm_prompt_help'] }}：</span></li>
                    <li class="qualification-item"><img class="qualification-img" data-deg="0"
@if($basic_info['license_fileImg'])
src="{{ $basic_info['license_fileImg'] }}"
@else
src="{{ skin('images/licence.png') }}"
@endif
></li>
                </ul>
            </div>
        </div>
    </div>

    <input type="hidden" value="{{ $merchant_id }}" id="merchantId" class="merchantId" name="merchantId">
    @include('frontend::library/page_footer')
</body>
</html>
