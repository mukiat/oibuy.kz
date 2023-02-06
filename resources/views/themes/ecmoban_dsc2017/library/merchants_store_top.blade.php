<div class="top">
    <div class="w w1200 relative">
        <div class="g-d-store-info">
            <div class="item">
                <a href="{{ $merchants_url }}" class="s-name" target="_blank">{{ $shop_name }}</a>
                <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $merchant_id }}" class="s-a-kefu">
                <i class="icon i-kefu"></i>
                </a>
            </div>
            <div class="item dsc-store-item">
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
					<div class="g-s-other">

@if($basic_info['license_fileImg'])

						<div class="lie">
							<div class="label">{{ $lang['certificate_info'] }}：</div>
							<div class="value"><a href="merchants_store.php?merchant_id={{ $merchant_id }}&act=merchants_licence"><img src="{{ skin('images/licence.png') }}" alt="{{ $lang['certificate_info'] }}" class="licence"></a></div>
						</div>

@endif

						<div class="lie">
							<div class="label">{{ $lang['customer_service_tel'] }}：</div>
							<div class="value">
@if($basic_info['kf_tel'])
{{ $basic_info['kf_tel'] }}
@else
{{ $lang['temporary_no'] }}
@endif
</div>
						</div>
					</div>
                    <div class="store-href">
                        <a href="{{ $merchants_url }}" class="store-home"><i class="iconfont icon-home-store"></i>{{ $lang['Go_to_store'] }}</a>
                    </div>
                </div>
            </div>
            <div class="item">
                <a href="javascript:void(0);" ectype="collect_store" data-type="store" data-value='{"userid":{{ $user_id }},"storeid":{{ $merchant_id }}}' data-url="merchants_store.php?merchant_id={{ $merchant_id }}" class="s-follow
@if($collect_store > 0)
 selected
@endif
"><span>
@if($collect_store > 0)
{{ $lang['follow_yes'] }}
@else
{{ $lang['follow_store'] }}
@endif
</span><i class="iconfont
@if($collect_store > 0)
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i></a>
            </div>
            <div class="item gd-shop-qrcode">
            	<div class="mobile_txt">{{ $lang['mobile_mall'] }}</div>
                <div class="mobile_pop">
                	<div class="mobile_pop_item"><img src="{{ $seller_qrcode_img }}" alt="" class="img"></div>
                </div>
            </div>
        </div>
    </div>
</div>
