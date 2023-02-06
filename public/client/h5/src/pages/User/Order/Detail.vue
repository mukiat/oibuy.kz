<template>
	<div class="con con_main">
		<div class="flow-checkout">
			<section class="flow-checkout-item flow-checkout-adr" v-if="orderDetail.offline_store && orderDetail.offline_store.stores_name != undefined">
				<van-cell-group>
					<van-cell icon="location">
						<template v-if="orderDetail.offline_store">
							<div slot="title">
								<h2 class="f-h-adr-title">{{ orderDetail.offline_store.stores_name }}</h2>
								<!--  <p class="f-04 col-7 m-top02" v-if="orderDetail.pick_code">{{$t('lang.take_delivery_code')}}：{{ orderDetail.pick_code }}</p> -->
								<p class="f-04 col-7 m-top02">{{$t('lang.service_call')}}：{{ orderDetail.offline_store.stores_tel }}</p>
								<p class="f-04 col-7 m-top02" v-if="orderDetail.take_time">{{$t('lang.store_order_time')}}：{{ orderDetail.take_time }}</p>
								<p class="f-04 col-7 m-top02">{{$t('lang.business_hours')}}：{{ orderDetail.offline_store.stores_opening_hours }}</p>
								<p class="f-04 col-7 m-top02">Бутик адресі：{{ orderDetail.offline_store.stores_address }}</p>
							</div>
						</template>
						<template v-else>
							<template slot="title">
								<div class="address_title">{{ orderDetail.consignee }} {{ orderDetail.mobile }}</div>
								<div class="address_body">
									<div>
										<div class="post_lebal_red" v-if="orderDetail.post_mobile">{{$t('lang.community_post')}}</div>{{orderDetail.address}}
									</div>
									<div v-if="orderDetail.post_mobile">{{$t('lang.post_contact_number')}}: {{orderDetail.post_mobile}}</div>
								</div>
							</template>
						</template>
					</van-cell>
				</van-cell-group>
			</section>
			<section class="flow-checkout-item claim_goods_box" v-if="orderDetail.post_delivery_code">
				<div>{{$t('lang.post_pickup_code')}}</div>
				<div>{{orderDetail.post_delivery_code}}</div>
				<div><span @click="viewQrCode">{{$t('lang.view_qr_code')}}</span></div>
			</section>
			<!-- 门店自提二维码 -->
			<section class="flow-checkout-item claim_goods_box" v-if="orderDetail.pick_code">
				<div>{{$t('lang.take_delivery_code')}}</div>
				<div class="pick"><span class="pick_on">{{orderDetail.pick_code}}</span></div>
				<div><span  @click="viewMnCode">{{$t('lang.view_qr_code')}}</span></div>
			</section>
			<section class="flow-checkout-item user-item">
				<div class="item-hd">
					<div class="head">
						<h3>{{ orderDetail.shop_name }}</h3>
						<span class="kefu"><a href="javascript:;" @click="onChat(0,orderDetail.shop_id)">{{ $t('lang.relation_contact') }}
						<i class="iconfont icon-kefu color-red"></i></a></span>
					</div>
				</div>
				<div class="item-bd">
					<div class="subHead">
						<h4><label>{{$t('lang.order_sn')}}：</label><span>{{ orderDetail.order_sn }}</span></h4>
						<p>
							<span>{{$t('lang.order_time')}}：{{ orderDetail.add_time }} </span>
							<template v-if="orderDetail.failure == 1">
								<em class="em-promotion b-tag">{{$t('lang.team_fail')}}</em>
							</template>
							<em class="em-promotion b-tag" v-if="orderDetail.activity_lang">{{ orderDetail.activity_lang }}</em>
						</p>
					</div>
					<template v-if="!listShow">
						<div class="list-bd-box list-order-box dis-box">
							<div class="box-flex">
								<div class="goods-item" v-for="(goodsItem,goodsIndex) in orderDetail.goods" :key="goodsIndex">
									<div class="dis-box" :class="{'package-box':goodsItem.extension_code == 'package_buy'}">
										<div class="reture-left-img" @click="productLink(goodsItem)">
											<div class="img-box">
												<img :src="goodsItem.goods_thumb" class="img" v-if="goodsItem.goods_thumb">
												<img class="img" src="../../../assets/img/no_image.jpg" v-else>
												<em class="activity-tag" v-if="goodsItem.is_gift"><img src="../../../assets/img/gift-icon.png" class="img" /></em>
												<em class="activity-tag" v-if="goodsItem.parent_id"><img src="../../../assets/img/parts-icon.png" class="img" /></em>
												<em class="activity-tag" v-if="goodsItem.extension_code == 'package_buy'"><img src="../../../assets/img/package.png"
													 class="img" /></em>
											</div>
										</div>
										<div class="reture-right-cont">
											<h4 class="twolist-hidden" @click="productLink(goodsItem)">
												<span class="color-red" v-if="goodsItem.extension_code == 'package_buy'">[{{$t('lang.package_box')}}]</span><img v-if="goodsItem.country_icon != ''" class="country_icon" :src="goodsItem.country_icon" />{{ goodsItem.goods_name }}
											</h4>
											<template v-if="goodsItem.extension_code == 'package_buy'">
												<div class="p-attr">{{$t('lang.is_discount')}}{{ goodsItem.format_package_list_saving }}</div>
												<div class="price order-detail-price">
													<div class="price-left">
														<div class="color-red">
															{{$t('lang.label_package_price')}}
															<label v-html="goodsItem.goods_price_formated"></label>
														</div>
														<span>x{{goodsItem.package_goods_list.length}}</span>
													</div>
													<template v-if="goodsItem.goods_handler_return && goodsItem.is_gift == 0">
														<a href="javascript:void(0)" @click="refoundHandle(goodsItem.rec_id,0)" class="btn-default-new" v-if="goodsItem.is_refound == 0">{{$t('lang.apply_return')}}</a>
														<a href="javascript:void(0)" @click="refoundHandle(goodsItem.ret_id,1)" class="btn-default-new" v-else>{{$t('lang.already_apply_return')}}</a>
													</template>
												</div>
											</template>
											<template v-else>
												<div class="p-attr" v-if="goodsItem.goods_attr">{{ goodsItem.goods_attr }}</div>
												<div class="price order-detail-price">
													<div class="price-left">
														<div class="color-red"><label v-html="goodsItem.goods_price_formated"></label></div>
														<span>x{{goodsItem.goods_number}}</span>
													</div>
													<template v-if="goodsItem.goods_handler_return && goodsItem.is_gift == 0">
														<a href="javascript:void(0)" @click="refoundHandle(goodsItem.rec_id,0)" class="btn-default-new" v-if="goodsItem.is_refound == 0">{{$t('lang.apply_return')}}</a>
														<a href="javascript:void(0)" @click="refoundHandle(goodsItem.ret_id,1)" class="btn-default-new" v-else>{{$t('lang.already_apply_return')}}</a>
													</template>
												</div>
											</template>
											<div class="p-virtual" v-if="goodsItem.virtual_goods && goodsItem.virtual_goods.length > 0">
												<div v-for="virtualItem in goodsItem.virtual_goods">
													<div class="virtual-item">{{$t('lang.virtual_card_number')}}：{{virtualItem.card_sn}}</div>
													<div class="virtual-item">{{$t('lang.virtual_card_pwd')}}：{{virtualItem.card_password}}</div>
												</div>
											</div>
										</div>
										<div class="t-jiantou t-jantou-xia" style="margin-top: 0;" @click="onListPackage" v-if="goodsItem.extension_code == 'package_buy'">
											<span class="user-more">
												<i class="iconfont" :class="[listPackageShow ? 'icon-moreunfold' :'icon-more']"></i>
											</span>
										</div>
									</div>
									<template v-if="goodsItem.extension_code == 'package_buy' && listPackageShow">
										<div class="dis-box package-list" v-for="packageItem in goodsItem.package_goods_list">
											<div class="reture-left-img">
												<div class="img-box">
													<img :src="packageItem.goods_thumb" class="img" v-if="packageItem.goods_thumb">
													<img class="img" src="../../../assets/img/no_image.jpg" v-else>
												</div>
											</div>
											<div class="reture-right-cont">
												<h4 class="twolist-hidden"><img v-if="packageItem.country_icon != ''" class="country_icon" :src="packageItem.country_icon" />{{ packageItem.goods_name }}</h4>
												<div class="price m-top04">
													<div class="color-red fl" v-html="packageItem.rank_price_format"></div>
													<span class="fr">x{{packageItem.goods_number}}</span>
												</div>
											</div>
										</div>
									</template>
								</div>
							</div>
							<!-- <div class="t-jiantou t-jantou-xia" @click="onListShow" v-if="length > 1">
              <span class="user-more">
                <i class="iconfont icon-moreunfold"></i>
              </span>
            </div> -->
						</div>
					</template>
					<template v-else>
						<div class="list-bd-box dis-box">
							<ul class="box-flex">
								<li class="reture-left-img fl" v-for="(goodsItem,goodsIndex) in orderDetail.goods" :key="goodsIndex">
									<router-link :to="{name:'goods',params:{id:goodsItem.goods_id}}">
										<div class="img-box">
											<img :src="goodsItem.goods_thumb" class="img" v-if="goodsItem.goods_thumb">
											<img class="img" src="../../../assets/img/no_image.jpg" v-else>
											<em class="activity-tag" v-if="goodsItem.is_gift"><img src="../../../assets/img/gift-icon.png" class="img" /></em>
											<em class="activity-tag" v-if="goodsItem.parent_id"><img src="../../../assets/img/parts-icon.png" class="img" /></em>
										</div>
									</router-link>
								</li>
							</ul>
							<div class="align-items" @click="onListShow"><span class="user-more"><em>{{$t('lang.gong')}}{{ length }}
										{{$t('lang.kuan')}}</em><i class="iconfont icon-more"></i></span></div>
						</div>
					</template>
				</div>
			</section>
			<section class="flow-checkout-item m-top10" v-if="orderDetail.shipping_id && !orderDetail.offline_store">
				<ul>
					<li>
						<section class="dis-box padding-all">
							<label class="t-remark g-t-temark">{{$t('lang.shipping_mode')}}</label>
							<div class="box-flex text-right f-04">
								<span>{{ orderDetail.shipping_name }}</span>&nbsp;
								<label class="color-red" v-html="orderDetail.shipping_fee_formated"></label>
							</div>
						</section>
					</li>
					<li class="padding-all border-t-common" v-if="orderDetail.postscript && !orderDetail.main_count > 0">
						<div class="box-flex t-remark">
							<p>{{$t('lang.buyer_message')}}：{{ orderDetail.postscript }}</p>
						</div>
					</li>
				</ul>
			</section>
			<section class="flow-checkout-item" v-if="orderDetail.cross_warehouse_name != ''">
				<ul>
					<li>
						<section class="dis-box padding-all">
							<label class="t-remark g-t-temark">{{$t('lang.place_of_shipment')}}</label>
							<div class="box-flex text-right f-04">
								<span style="color: #6C6C6C;">{{ orderDetail.cross_warehouse_name }}</span>
							</div>
						</section>
					</li>
				</ul>
			</section>
			<section class="flow-checkout-item m-top10">
				<ul>
					<li class="dis-box padding-all">
						<label class="t-remark g-t-temark">{{$t('lang.payment_mode')}}</label>
						<div class="box-flex text-right f-04">
							<span>{{ orderDetail.pay_name }}</span>
						</div>
					</li>
					<li class="dis-box padding-all border-t-common" v-if="orderDetail.pay_effective_time">
						<label class="t-remark g-t-temark">{{$t('lang.payment_time')}}</label>
						<div class="box-flex text-right f-04">
							<span class="color-red">
								<count-down :endTime="orderDetail.pay_effective_time" :endText="$t('lang.pay_overtime')"></count-down>
							</span>
						</div>
					</li>
					<li class="padding-all border-t-common" v-if="orderDetail.exchange_goods == 0 && orderDetail.can_invoice > 0">
						<div class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.invoice_info')}}</label>
						</div>
						<div class="box-flex t-remark m-top08">
							<template v-if="!orderDetail.cross_border">
								<template v-if="orderDetail.invoice_type == 1">
									<p>{{$t('lang.label_invoice_content')}}{{$t('lang.vat_invoice')}}</p>
								</template>
								<template v-if="orderDetail.invoice_type == 0">
									<p>{{$t('lang.label_invoice_type')}}{{$t('lang.plain_invoice')}}</p>
									<p>{{$t('lang.label_invoice_company')}}{{ orderDetail.inv_payee }}</p>
									<p>{{$t('lang.label_invoice_content')}}{{ orderDetail.inv_content }}</p>
								</template>
								<template v-if="orderDetail.invoice_type == 2">
		                            <div class="dis-box">
		                                <label class="t-remark g-t-temark">Фактура түрі：Электронды</label>
		                                <div class="box-flex text-right f-04">
		                                    <a href="javascript:void(0)"  @click="invoiceDetail(orderDetail.order_id)"><em class="color-red"> Анығын көру</em></a>
		                                </div>
		                            </div>
		                            <p>{{$t('lang.label_invoice_company')}}：{{ orderDetail.inv_payee }}</p>
		                            <p>{{$t('lang.label_invoice_content')}}：{{ orderDetail.inv_content }}</p>
		                        </template>
							</template>
						</div>
					</li>
					<li class="padding-all border-t-common" v-if="orderDetail.bonus_id > 0">
						<div class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.bonus')}}</label>
							<div class="box-flex text-right f-04">
								<span>{{$t('lang.bonus_amount')}}</span>
								<label class="color-red" v-html="orderDetail.bonus"></label>
							</div>
						</div>
					</li>
					<li class="padding-all border-t-common" v-if="orderDetail.coupons_type > 0">
						<div class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.coupons')}}</label>
							<div class="box-flex text-right f-04">
								<span>{{$t('lang.coupon_amount')}}</span>
								<label class="color-red" v-html="orderDetail.coupons"></label>
							</div>
						</div>
					</li>
					<li class="padding-all border-t-common" v-if="orderDetail.vc_id > 0">
						<div class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.value_card')}}</label>
							<div class="box-flex text-right f-04">
								<span>{{$t('lang.value_card_amount')}}</span>
								<label class="color-red" v-html="orderDetail.card_amount"></label>
							</div>
						</div>
					</li>
					<li class="padding-all border-t-common" v-if="orderDetail.integral > 0 && orderDetail.exchange_goods == 0">
						<div class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.integral')}}</label>
							<div class="box-flex text-right f-04">
								<span>{{$t('lang.integral_deduction_amout')}}</span>
								<label class="color-red" v-html="orderDetail.integral_money"></label>
							</div>
						</div>
					</li>
				</ul>
			</section>
			<section class="flow-checkout-item m-top10">
				<van-cell-group>
					<van-cell :title="$t('lang.goods_amout')" class="van-cell-title b-min b-min-b">
						<div>
							<div class="color-red">
								<template v-if="orderDetail.extension_code == 'group_buy' && orderDetail.is_group_deposit == 1">{{$t('lang.label_group_deposit')}}</template>
								<label v-html="orderDetail.goods_amount_formated"></label>
							</div>
						</div>
					</van-cell>
					<van-cell>
						<template slot="title">
							<ul>
								<li class="of-hidden" v-if="discount > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.goods_favorable')}}</label>
									<span class="color-red fr">-<label v-html="orderDetail.discount_formated"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="!orderDetail.offline_store">
									<label class="t-remark g-t-temark fl">{{$t('lang.shipping_fee')}}</label>
									<span class="color-red fr">+<label v-html="orderDetail.shipping_fee_formated"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.pay_fee > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.pay_fee')}}</label>
									<span class="color-red fr">+<label v-html="orderDetail.pay_fee_formated"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.rate_fee > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.comprehensive_tax')}}</label>
									<span class="color-red fr">+<label v-html="orderDetail.rate"></label></span>
								</li>
								<template v-if="orderDetail.membership_card_id && orderDetail.membership_card_id > 0">
									<li class="of-hidden m-top10">
										<label class="t-remark g-t-temark fl">{{$t('lang.membership_card_buy_money')}}</label>
										<span class="color-red fr">+<label v-html="orderDetail.membership_card_buy_money_formated"></label></span>
									</li>
									<li class="of-hidden m-top10">
										<label class="t-remark g-t-temark fl">{{$t('lang.membership_card_discount_price')}}</label>
										<span class="color-red fr">
											-<label v-html="orderDetail.membership_card_discount_price_formated"></label>
										</span>
									</li>
								</template>
								<li class="of-hidden m-top10" v-if="orderDetail.bonus_id > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.use_bonus')}}</label>
									<span class="color-red fr">-<label v-html="orderDetail.bonus"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.coupons_type > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.use_coupons')}}</label>
									<span class="color-red fr">-<label v-html="orderDetail.coupons"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.vc_id > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.use_value_card')}}</label>
									<span class="color-red fr">-<label v-html="orderDetail.card_amount"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.vc_dis_money > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.value_card_discount')}}</label>
									<span class="color-red fr">-<label v-html="orderDetail.vc_dis_money_formated"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.integral > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.use_integral')}}</label>
									<span style="text-align: right;" class="color-red fr">-<label v-html="orderDetail.integral_money"></label><br />({{orderDetail.integral}}{{$t('lang.integral')}})</span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.surplus > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.use_balance')}}<template v-if="orderDetail.presale_final_pay">({{$t('lang.pay_in_advance')}})</template></label>
									<span class="color-red fr">-<label v-html="orderDetail.surplus_formated"></label></span>
								</li>
								<li class="of-hidden m-top10" v-if="orderDetail.money_paid > 0">
									<label class="t-remark g-t-temark fl">{{$t('lang.pay_in_balance')}}</label>
									<span class="color-red fr">-<label v-html="orderDetail.money_paid_formated"></label></span>
								</li>
							</ul>
						</template>
					</van-cell>
				</van-cell-group>
			</section>
			<section class="flow-checkout-item m-top10" v-if="orderDetail.pay_code == 'bank'">
				<van-cell-group>
					<van-cell>
						<template slot="title">
							<ul>
								<li class="of-hidden" :class="{'m-top10':bankIndex > 0}" v-for="(bankItem,bankIndex) in orderDetail.pay_config" :key="bankIndex" v-if="orderDetail.pay_code=='bank'">
									<label class="t-remark g-t-temark fl">{{bankItem.name}}</label>
									<span class="fr"><label v-html="bankItem.value"></label></span>
								</li>
							</ul>
						</template>
					</van-cell>
				</van-cell-group>
			</section>

			<van-cell-group class="m-top10" v-if="orderDetail.delay === 1">
				<van-cell class="van-cell-title b-min b-min-b">
					<div slot="title" class="lh28">{{$t('lang.delay_in_receiving')}}</div>
					<template v-if="orderDetail.delay_type == 'Өтініш'">
						<van-button size="small" type="primary" @click="delayOrder(orderDetail.order_id)">{{ orderDetail.delay_type }}</van-button>
					</template>
					<template v-else>
						<span class="lh28 color-red">{{ orderDetail.delay_type }}</span>
					</template>
				</van-cell>
			</van-cell-group>
			<section :class="{'order-detail-submit': orderDetail.handler}">
				<div class="van-submit-bar van-order-submit-bar" :class="{'van-submit-bar-w100':(!orderDetail.tracker && orderDetail.handler != 6 && orderDetail.handler != 8) && (orderDetail.pay_code == 'cod' ? true : !totalAmountInt > 0)}">
					<div class="van-submit-bar__bar">
						<div class="van-submit-bar__text van-submit-bar__text_left" v-if="orderDetail.pay_status == 2">
							<span>{{$t('lang.amount_paid')}}：</span>
							<span class="van-submit-bar__price van-submit-bar__price__cur" v-html="orderDetail.realpay_amount_formated"></span>
						</div>
						<div class="van-submit-bar__text van-submit-bar__text_left" v-else>
							<span>{{$t('lang.total_amount_payable')}}：</span>
							<span class="van-submit-bar__price van-submit-bar__price__cur">{{ amountPrice }}</span>
						</div>
						<!--是否是预售订单付款-->
						<template v-if="orderDetail.presale_final_pay >= 0">
							<template v-if="orderDetail.presale_final_pay == 0">
								<van-button type="danger" size="large" v-if="orderDetail.total_amount > 0 && orderDetail.handler != 7 && orderDetail.handler != 8 && orderDetail.is_delete == 0"
								 class="van-button--disabled" disabled>{{ buttonText }}</van-button>
							</template>
							<template v-else>
								<van-button type="danger" size="large" v-if="orderDetail.total_amount > 0 && orderDetail.handler != 7 && orderDetail.handler != 8 && orderDetail.is_delete == 0"
								 @click="onlinepay(orderDetail.order_sn,orderDetail.presale_final_pay)">{{ buttonText }}</van-button>
							</template>
						</template>
						<template v-else>
							<template v-if="orderDetail.pay_code != 'cod' && orderDetail.pay_code != 'bank'">
								<van-button type="danger" size="large" v-if="orderDetail.total_amount > 0 && orderDetail.handler != 7 && orderDetail.handler != 8 && orderDetail.failure == 0 && orderDetail.is_delete == 0"
								 @click="onlinepay(orderDetail.order_sn)">{{ buttonText }}</van-button>
							</template>
						</template>
						<template v-if="orderDetail.handler == 2">
							<van-button type="danger" size="large" :text="$t('lang.received')" @click="receivedOrder(orderDetail.order_id)" />
						</template>
						<template v-if="orderDetail.handler == 4">
							<van-goods-action-big-btn :text="$t('lang.ss_received')" primary />
						</template>
					</div>
				</div>
				<van-goods-action v-if="orderDetail.handler || orderDetail.handler != 2 || !orderDetail.tracker">
					<template v-if="orderDetail.handler == 1">
						<van-goods-action-big-btn :text="$t('lang.cancel_order')" @click="onClickBigBtn" />
					</template>
					<template v-if="orderDetail.handler == 3">
						<van-goods-action-big-btn :text="$t('lang.evaluation_list')" @click="onCommentBtn(orderDetail.order_id)" primary />
					</template>
					<template v-if="orderDetail.handler == 5">
						<van-goods-action-big-btn :text="$t('lang.payment')" primary />
					</template>
					<template v-if="orderDetail.handler == 6 && !orderDetail.tracker">
						<van-goods-action-big-btn :text="$t('lang.confirmed')" primary />
					</template>
					<template v-if="orderDetail.handler == 7">
						<van-goods-action-big-btn :text="$t('lang.canceled')" primary />
					</template>
					<template v-if="orderDetail.handler == 8">
						<van-goods-action-big-btn :text="$t('lang.invalid_order')" primary />
					</template>
					<template v-if="orderDetail.tracker">
						<div class="btn-invoice_no" @click="orderTracking"><a>{{$t('lang.order_tracking')}}</a></div>
					</template>
				</van-goods-action>
			</section>
			<CommonNav></CommonNav>
		</div>
		<van-popup v-model="showMnPopup" class="code">
			<div class="qr_code">
				<div class="qr_code_title">
					{{$t('lang.take_delivery_code')}}<span class="show-m" @click="showMnPopup = false">×</span>
				</div>
				<div class="qr_code_content qr_cod">
					<img :src="qrCodeData.qrcode_url" class="md_code_img" />
					<div class="qr_code_text">
						<p class="pic">{{orderDetail.pick_code}}</p>
						<!-- <p>{{$t('lang.label_tel')}} {{orderDetail.post_mobile}}</p>
						<p>{{$t('lang.post_pickup_location')}}：{{orderDetail.address}}</p> -->
					</div>
				</div>
			</div>
		</van-popup>
		<van-popup v-model="showPopup" class="code">
			<div class="qr_code">
				<div class="qr_code_title">
					{{$t('lang.proof_delivery')}}<span @click="showPopup = false">×</span>
				</div>
				<div class="qr_code_content">
					<img :src="qrCodeData.pick_up_avatar" class="qr_code_img" />
					<div class="qr_code_text">
						<p>{{orderDetail.post_delivery_code}}</p>
						<p>{{$t('lang.label_tel')}} {{orderDetail.post_mobile}}</p>
						<p>{{$t('lang.post_pickup_location')}}：{{orderDetail.address}}</p>
					</div>
				</div>
			</div>
		</van-popup>

		<!--运单号-->
		<van-popup class="show-popup-bottom" v-model="deliveries" position="bottom">
			<div class="goods-show-title padding-all">
				<h3 class="fl">Тасымал ізіне түсу</h3>
				<i class="iconfont icon-close fr" @click="deliveries = false"></i>
			</div>
			<div class="s-g-list-con">
				<div class="select-two">
					<ul>
						<li class="ect-select" v-for="(item,index) in deliveriesList" :key="index" :class="{'active':delivery_sn == item.delivery_sn}"
						 @click="delivery_select(item.delivery_sn)">
							<label class="dis-box">
								<span class="box-flex">{{ item.shipping_name }}<span class="sn">(Трек-код：{{item.invoice_no}})</span></span>
								<i class="iconfont icon-gou"></i>
							</label>
						</li>
					</ul>
				</div>
			</div>
		</van-popup>
	</div>
</template>

<script>
	import {
		mapState
	} from 'vuex'
	import {
		Cell,
		CellGroup,
		SubmitBar,
		GoodsAction,
		GoodsActionBigBtn,
		GoodsActionMiniBtn,
		Toast,
		Button,
		Popup
	} from 'vant'

	import ProductList from '@/components/user/ProductList'
	import CommonNav from '@/components/CommonNav'
	import formProcessing from '@/mixins/form-processing'
	import CountDown from '@/components/CountDown'

	export default {
		mixins: [formProcessing],
		data() {
			return {
				loading: true,
				length: 0,
				currency: '₸',
				buttonText: this.$t('lang.immediate_payment'),
				listPackageShow: false,
				showPopup: false,
				qrCodeData: {},
				showMnPopup: false,
				deliveries:false,
				delivery_sn:''
			}
		},
		components: {
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
			[SubmitBar.name]: SubmitBar,
			[GoodsAction.name]: GoodsAction,
			[GoodsActionBigBtn.name]: GoodsActionBigBtn,
			[GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
			[Button.name]: Button,
			[Popup.name]: Popup,
			ProductList,
			CommonNav,
			CountDown
		},
		created() {
			this.onLoad()
		},
		computed: {
			...mapState({
				orderDetail: state => state.user.userOrderDetail
			}),
			goods() {
				return this.orderDetail.goods
			},
			discount() {
				return Number(this.orderDetail.discount)
			},
			amountPrice() {
				let price = 0
				if (this.orderDetail.total_amount) {
					price = this.orderDetail.total_amount ? this.orderDetail.total_amount : this.orderDetail.goods_amount
				}

				return this.currency + price
			},
			totalAmountInt() {
				return Number(this.orderDetail.total_amount)
			},
			listShow: {
				get() {
					return false
					//return this.$store.state.user.listShow
				},
				set(val) {
					this.$store.state.user.listShow = val
				}
			},
			pay_code() {
				return this.orderDetail.pay_code ? this.orderDetail.pay_code : 'balance'
			},
			deliveriesList(){
				return this.orderDetail.deliveries
			}
		},
		methods: {
			onLoad() {
				this.$store.dispatch('setOrderDetail', {
					order_id: this.$route.params.id
				})

				let configData = JSON.parse(sessionStorage.getItem('configData'));
				if (configData) {
					this.currency = configData.currency_format.replace('%s', '');
				}
			},
			onSubmit() {
				this.$router.push({
					name: 'done',
					query: {
						order_sn: this.orderDetail.order_sn
					}
				})
			},
			onClickBigBtn() {
				this.$store.dispatch('setOrderHandler', {
					order_id: this.$route.params.id
				}).then(res => {
					if (res.data == true) {
						Toast(this.$t('lang.order_cancel'))
						this.onLoad()
					}
				})
			},
			//申请退换货
			refoundHandle(id, type) {
				if (type == 0) {
					this.$router.push({
						name: 'rpplyReturn',
						query: {
							rec_id: id,
							order_id: this.orderDetail.order_id
						}
					})
				} else {
					this.$router.push({
						name: 'refoundDetail',
						query: {
							ret_id: id
						}
					})
				}
			},
			//延迟收货
			delayOrder(id) {
				this.$store.dispatch('setDelayOrder', {
					order_id: id
				}).then(res => {
					Toast(res.data.msg)
					if (res.data.error == 0) {
						this.onLoad()
					}
				})
			},
			//晒单评论
			onCommentBtn(id) {
				this.router.push({
					name: 'commentDetail',
					params: {
						id: id
					}
				})
			},
			//确认收货
			receivedOrder(id) {
				this.$store.dispatch('setReceivedOrder', {
					order_id: id
				}).then(res => {
					if (res.data == true) {
						Toast(this.$t('lang.order_confirm_receipt'))
						this.onLoad()
					}
				})
			},
			//立即支付
			onlinepay(id, pay) {
				if (this.pay_code == 'balance') {
					this.$router.push({
						name: 'done',
						query: {
							order_sn: id,
							pay_code: 'balance'
						}
					})
				} else {
					if (pay) {
						this.$router.push({
							name: 'done',
							query: {
								order_sn: id,
								presale_final_pay: pay
							}
						})
					} else {
						this.$router.push({
							name: 'done',
							query: {
								order_sn: id
							}
						})
					}
				}
			},
			onListShow() {
				this.listShow = this.listShow ? false : true
			},
			onListPackage() {
				this.listPackageShow = this.listPackageShow ? false : true
			},
			productLink(item) {
				let extension_id = this.orderDetail.extension_id ? this.orderDetail.extension_id : 0;

				//超值礼包
				if(item.extension_code == 'package_buy'){
					this.$router.push({name: 'package'});
					return
				}

				switch(this.orderDetail.extension_code){
					case 'presale':
						this.$router.push({ name: 'presale-detail', params: { act_id: extension_id }});
						break
					case 'auction':
						this.$router.push({ name: 'auction-detail', params: { act_id: extension_id }});
						break
					case 'bargain_buy':
						this.$router.push({ name: 'bargain-detail', params: { id: extension_id }});
						break
					case 'exchange_goods':
						this.$router.push({ name: 'exchange-detail', params: { id: item.goods_id }});
						break
					case 'group_buy':
						this.$router.push({ name: 'groupbuy-detail', params: { group_buy_id: extension_id }});
						break
					case 'team_buy':
						this.$router.push({ name: 'team-detail', query: { goods_id: item.goods_id,team_id:0 }});
						break
					case 'seckill':
						this.$router.push({ name: 'seckill-detail', query: { seckill_id: extension_id,tomorrow:0 }});
						break
					default:
						this.$router.push({name: 'goods',params: {id: item.goods_id}});
						break
				}
			},
			// 门店查看二维码
			async viewMnCode() {
				let o = {
					pick_code:this.orderDetail.pick_code
				}
				const {
					data: {
						data,
						status
					}
				} = await this.$http.get(`${window.ROOT_URL}api/qrcode/qrcodeurl`, {
					params: {
						info: JSON.stringify(o)
					}
				});
				if (status !== 'success') return Toast(this.$t('lang.post_server_busy'));
				this.qrCodeData = data;
				this.showMnPopup = true;

			},
			// 查看二维码
			async viewQrCode() {
				if (Object.keys(this.qrCodeData).length > 0) {
					this.showPopup = true;
				} else {
					const {
						data: {
							data,
							status
						}
					} = await this.$http.get(`${window.ROOT_URL}api/cgroup/order/userPostcode`, {
						params: {
							order_id: this.orderDetail.order_id
						}
					});
					if (status !== 'success') return Toast(this.$t('lang.post_server_busy'));
					this.qrCodeData = data;
					this.showPopup = true;
				}
			},
			orderTracking(){
				if(this.deliveriesList.length > 0){
					if(this.deliveriesList.length > 1){
						this.deliveries = true
					}else{
						if(window.ROOT_URL){
							window.location.href = `${window.ROOT_URL}tracker?delivery_sn=${this.deliveriesList[0].delivery_sn}`
						}else{
							Toast(this.$t('lang.prototype_notic'));
						}
					}
				}else{
					Toast(this.$t('lang.deliveries_sn'));
				}
			},
			delivery_select(delivery_sn){
				this.delivery_sn = delivery_sn
				if(window.ROOT_URL){
					window.location.href = `${window.ROOT_URL}tracker?delivery_sn=${delivery_sn}`
				}else{
					Toast(this.$t('lang.prototype_notic'));
				}

				this.deliveries = false
			},
			// 查看电子发票
		    invoiceDetail(id){
		        this.$router.push({
		            name:'invoiceDetail',
		            params: {
		                order_id: id
		            }
		        })
		    }
		},
		watch: {
			goods() {
				this.length = this.goods.length
			}
		}
	}
</script>
<style scoped>
	.goods-item .dis-box {
		margin-bottom: 1rem;
	}

	.goods-item .package-box,
	.goods-item .package-list {
		padding-bottom: .5rem;
		border-bottom: 1px solid #f0f0f0;
	}

	.goods-item .dis-box:last-child {
		margin-bottom: 0;
		border-bottom: 0;
		padding-bottom: 0;
	}

	.claim_goods_box {
		margin: 1rem 0;
		background-color: transparent;
	}

	.claim_goods_box div {
		text-align: center;
		color: #f44;
		background-color: #fff;
	}

	.claim_goods_box div:nth-child(1) {
		font-size: 1.4rem;
		text-align: left;
		color: #666;
		padding: 1.2rem 1rem 1rem;
		margin-bottom: 1px;
	}

	.claim_goods_box div:nth-child(2) {
		font-size: 1.8rem;
		padding-top: 1rem;
	}

	.claim_goods_box div:nth-child(3) {
		padding: 1rem 0;
	}

	.claim_goods_box span {
		padding: 0.3rem 1rem;
		border: 1px solid #f44;
		border-radius: 5px;
	}
  .claim_tit{
	  font-size: 1.5rem;
  }
	.code {
		border-radius: 1rem;
	}

	.qr_code {
		position: relative;
		box-sizing: border-box;
		text-align: center;
		padding: 0 1rem;
		background-color: #fff;
	}

	.qr_code_title {
		font-size: 1.4rem;
		font-weight: 700;
		padding: 2rem 0 1.2rem;
		/* border-bottom: 1px solid #ccc; */
	}

	.qr_code_title span {
		position: absolute;
		display: block;
		padding: 1rem;
		top: 0;
		right: 0;
	}

	.qr_code_content {
		text-align: left;
		line-height: 1.8;
		padding: 1.5rem 3rem;
	}

	.qr_code_img {
		width: 20rem;
		height: 20rem;
	}
     .md_code_img{
		 width: 15rem;
		 height: 15rem;
	 }
	.qr_code_text p:first-child {
		text-align: center;
	}

	.claim_goods_box .pick {
		padding-top: 1rem;
		padding-bottom: 1rem;
	}
    .show-m{
		font-size: 20px;
		color: #cecece;
	}
	.qr_cod{
		padding: 1.5rem 2.5rem;
		padding-top: 0;
	}
	.pick_on {
		border:none !important;
		padding: 2rem;
		border-radius: 5px;
		font-size: 15px;
	}
	.pic{
		font-size: 1.4rem;
		color: red;
		font-weight: 700;
	}
	
	.country_icon{
		width: 2.4rem;
		padding-right: 0.3rem;
		position: relative;
		top: 0.2rem;
		display: inline-block;
	}
</style>
