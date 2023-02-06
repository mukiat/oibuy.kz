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
</head>

<body>
	@include('frontend::library/page_header_common')
    <div class="container trade_snapshot">
    	<div class="w w1200">
        	<div class="product-info">
				<div class="preview">
					@include('frontend::library/goods_gallery')
				</div>
                <div class="product-wrap
@if($goods['user_id'])
 product-wrap-min
@endif
">
                	<div class="name">{{ $goods['goods_style_name'] }}</div>

@if($goods['goods_brief'])

                    <div class="newp">{{ $goods['goods_brief'] }}</div>

@endif

                    <div class="summary">
                        <div class="summary-basic-info">
                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['goods_name'] }}</div>
                                <div class="si-warp">
								{{ $goods['goods_name'] }}
                                </div>
                            </div>
                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['Commodity_number'] }}</div>
                                <div class="si-warp">
								{{ $goods['goods_sn'] }}
                                </div>
                            </div>

@if($goods['goods_attr'])

                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['attr'] }}</div>
                                <div class="si-warp">
								{{ $goods['goods_attr'] }}
                                </div>
                            </div>

@endif

                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['price'] }}</div>
                                <div class="si-warp">
								{{ $goods['shop_price'] }}
                                </div>
                            </div>
                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['number_to'] }}</div>
                                <div class="si-warp">
								{{ $goods['goods_number'] }}
                                </div>
                            </div>
                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['freight_p'] }}</div>
                                <div class="si-warp">
								{{ $goods['shipping_fee'] }}
                                </div>
                            </div>
                            <div class="summary-item is-service">
                            	<div class="si-tit">{{ $lang['service'] }}</div>
                                <div class="si-warp">
									{{ $lang['you'] }} <a href="{{ $goods['shop_url'] }}" class="link-red" target="_blank">{{ $goods['rz_shop_name'] }}</a> {{ $lang['After_sale_service'] }}
								</div>
                            </div>
                            <div class="summary-item">
                            	<div class="si-tit">{{ $lang['snapshot_time'] }}</div>
                                <div class="si-warp">
									{{ $goods['snapshot_time'] }}
								</div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>

@if($goods['ru_id'])

				<div class="seller-pop">
					<div class="seller-info">
						<a href="{{ $goods['shop_url'] }}" title="{{ $goods['rz_shop_name'] }}" target="_blank" class="name">{{ $goods['rz_shop_name'] }}</a>
						<a id="IM"  onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}"><i class="icon i-kefu"></i></a>
					</div>
					<div class="seller-pop-box">
						<div class="s-score">
							<span class="score-icon"><span class="score-icon-bg" style="width:{{ $merch_cmt['cmt']['all_zconments']['allReview'] }}%;"></span></span>
							<span>{{ $merch_cmt['cmt']['all_zconments']['score'] }}</span>
							<i class="iconfont icon-down"></i>
						</div>
						<div class="g-s-parts">
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
							</div>
						</div>
					</div>
					<div class="seller-tel"><i class="iconfont icon-tel"></i>{{ $shop_information['kf_tel'] }}</div>
				</div>

@endif

                <div class="clear"></div>
            </div>
			@include('frontend::library/goods_fittings')
            <div class="goods-main-layout">
                <div class="g-m-detail">
                	<div class="gm-tabbox" ectype="gm-tabs">
                    	<ul class="gm-tab">
                            <li>{{ $lang['specification'] }}</li>
							<li>{{ $lang['description'] }}</li>
                        </ul>
                        <div class="gm-tab-qp-bort" ectype="qp-bort"></div>
                    </div>
                    <div class="gm-floors" ectype="gm-floors">
                    	<div class="gm-f-item gm-f-parameter" ectype="gm-item">
                            <dl class="goods-para">
                                <dd class="column"><span>{{ $lang['goods_name'] }}：{{ $goods['goods_name'] }}</span></dd>
                                <dd class="column"><span>{{ $lang['Commodity_number'] }}：{{ $goods['goods_sn'] }}</span></dd>
                                <dd class="column"><span>{{ $lang['seller_store'] }}：<a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" target="_blank">{{ $goods['rz_shop_name'] }}</a></span></dd>

@if($cfg['show_goodsweight'])

                                <dd class="column"><span>{{ $lang['weight'] }}：{{ $goods['goods_weight'] }}</span></dd>

@endif


@if($cfg['show_addtime'])

                                <dd class="column"><span>{{ $lang['add_time'] }}{{ $goods['add_time'] }}</span></dd>

@endif

                            </dl>


@if($properties)

                        	<dl class="goods-para">

@foreach($properties as $key => $property_group)


@foreach($property_group as $property)

                                <dd class="column"><span title="{{ $property['value'] }}">{{ $property['name'] }}：{{ $property['value'] }}</span></dd>

@endforeach


@endforeach

                            </dl>

@endif


@if($extend_info)

							<dl class="goods-para">

@foreach($extend_info as $key => $info)

								<dd class="column"><span title="{{ $info }}">{{ $key }}：{{ $info }}</span></dd>

@endforeach

							</dl>

@endif

                        </div>
                        <div class="gm-f-item gm-f-details" ectype="gm-item">
                        	<div class="gm-title">
                            	<h3>{{ $lang['description'] }}</h3>
                            </div>
                            {!! $goods['goods_desc'] !!}
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    @include('frontend::library/page_footer')
</body>
</html>
