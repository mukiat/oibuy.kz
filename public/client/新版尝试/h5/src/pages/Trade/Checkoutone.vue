<template>
	<div class="con con_main">
		<div class="title_on">
			<div class="item" @click="shop_kud">Жеткізу</div>
			<div class="item active_on">Барып алу</div>
		</div>
		<div class="add_title" @click="shop_show" v-if="isSingle == ''">
			<img src="../../assets/img/more_icon.png" />
			<span>{{storepick}}</span>
			<i class="iconfont icon-more"></i>
		</div>
		<div class="flow-checkout" v-if="checkoutDisplay">
			<section class="flow-checkout-item flow-checkout-adr m-b10 quanymagin">
				<van-cell-group>
					<van-cell icon="location" is-link @click="checkoutAdd" v-if="stor==0">
						<template slot="title">
							<div class="tit_jin">Сіз таңдаған аймақта сәйкесті бутик табылмады,аймақ/бутикті ауыстырыңыз немесе тасымал қызметін таңдаңыз.</div>
						</template>
					</van-cell>

					<van-cell icon="location" is-link v-else @click="checkoutAdd">
						<div slot="title">
							<h2 class="f-h-adr-title">{{ storeInfo.stores_name }}</h2>
							<p class="f-04 col-7 m-top02">{{$t('lang.label_service_call')}}{{ storeInfo.stores_tel }}</p>
							<p class="f-04 col-7 m-top02">{{$t('lang.label_business_hours')}}{{ storeInfo.stores_opening_hours }}</p>
							<p class="f-04 col-7 m-top02">{{$t('lang.label_store_address')}}[{{ storeInfo.address }}] {{ storeInfo.stores_address }}</p>
						</div>
					</van-cell>
				</van-cell-group>
			</section>
			
			<!--商品清单-->
			<section class="checkout-goods-list m-b10 quanymagin" v-for="(item,index) in checkoutInfo.goods_list" :key="index">
				<section class="section-list">
					<div class="detail-title">{{ item.shop_name }}</div>
					<van-cell-group class="van-cell-noright m-top08" style="margin-left: -0.4rem;" v-if="item.cross_warehouse_name != ''">
						<van-cell :title="$t('lang.place_of_shipment')" class="b-min">
							<div style="text-align: left; color:#777; padding-left: 1.2rem;">
								{{item.cross_warehouse_name}}
							</div>
						</van-cell>
					</van-cell-group>
					<template v-if="listShow[index]">
						<div class="product-list product-list-max dis-box">
							<ul class="box-flex">
								<li v-for="(goodsItem,goodsIndex) in item.goods" v-if="goodsIndex < 3" :key="goodsIndex" @click="productLink(goodsItem)">
									<div class="p-d-img">
										<div class="p-r">
											<img class="img" :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb">
											<img class="img" src="../../assets/img/no_image.jpg" v-else>
											<em class="activity-tag" v-if="goodsItem.is_gift > 0"><img src="../../assets/img/gift-icon.png" class="img" /></em>
										</div>
									</div>
								</li>
							</ul>
							<!-- 商品清单 -->
							<div class="t-jiantou" @click="onListShow(item.goods[index].ru_id)">
								<span class="user-more">
									<em>{{$t('lang.gong')}} {{ item.goods_count }} {{$t('lang.jian')}}</em>
									<i class="iconfont icon-more"></i>
								</span>
							</div>
						</div>
					</template>
					<template v-else>
						<div class="product-list dis-box">
							<ul class="box-flex">
								<li v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex">
									<div class="product-div" :class="{'package-box':goodsItem.extension_code == 'package_buy'}">
										<div class="product-list-img" @click="productLink(goodsItem)">
											<img class="img" :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb" />
											<img class="img" src="../../assets/img/no_image.jpg" v-else>
											<em class="activity-tag" v-if="goodsItem.is_gift > 0"><img src="../../assets/img/gift-icon.png" class="img" /></em>
											<em class="activity-tag" v-if="goodsItem.extension_code == 'package_buy'"><img src="../../assets/img/package.png"
												 class="img" /></em>
										</div>
										<div class="product-info" @click="productLink(goodsItem)">
											<h4>
												<em class="em-promotion" v-if="rec_type == 1">{{$t('lang.group_buy')}}</em>
												<em class="em-promotion" v-if="rec_type == 2">{{$t('lang.auction')}}</em>
												<em class="em-promotion" v-if="rec_type == 4">{{$t('lang.integral')}}</em>
												<em class="em-promotion" v-if="rec_type == 5">{{$t('lang.presale')}}</em>
												<em class="em-promotion" v-if="rec_type == 6">{{$t('lang.seckill')}}</em>
												<em class="em-promotion" v-if="rec_type == 7">{{$t('lang.team')}}</em>
												<em class="em-promotion" v-if="rec_type == 8">{{$t('lang.bargain')}}</em>
												<em class="em-promotion" v-if="goodsItem.extension_code == 'package_buy'">{{$t('lang.package')}}</em>
												<img class="country_icon" v-if="goodsItem.country_icon != ''" :src="goodsItem.country_icon" />{{ goodsItem.goods_name }}
											</h4>
											<template v-if="goodsItem.extension_code == 'package_buy'">
												<div class="price">
													<div class="color-red" style="font-size:1.5rem;">
														<label>{{$t('lang.label_package_price')}}</label>
														<label v-html="goodsItem.goods_price_format"></label>
													</div>
													<span>x{{goodsItem.package_goods_list && goodsItem.package_goods_list.length}}</span>
												</div>
												<div class="p-attr">
													<span>{{$t('lang.is_discount')}}</span>
													<span v-html="goodsItem.format_package_list_saving"></span>
												</div>
											</template>
											<template v-else>
												<div class="flex-common">
													<div v-html="goodsItem.goods_price_format" class="price"></div>
													<span class="size_15">x{{ goodsItem.goods_number }}</span>
												</div>
												<div class="p-attr" v-if="goodsItem.goods_attr">{{ goodsItem.goods_attr }}</div>
											</template>
										</div>
										<div class="t-jiantou t-jantou-xia" style="margin-top: 0;" @click="onListPackage" v-if="goodsItem.extension_code == 'package_buy'">
											<span class="user-more">
												<i class="iconfont size_14" :class="[listPackageShow ? 'icon-moreunfold' :'icon-more']"></i>
											</span>
										</div>
									</div>
									<template v-if="goodsItem.extension_code == 'package_buy' && listPackageShow">
										<div class="product-div package-list" v-for="packageItem in goodsItem.package_goods_list">
											<div class="product-list-img">
												<img class="img" :src="packageItem.goods_thumb" v-if="packageItem.goods_thumb" />
												<img class="img" src="../../assets/img/no_image.jpg" v-else>
											</div>
											<div class="product-info">
												<h4><img class="country_icon" v-if="goodsItem.country_icon != ''" :src="goodsItem.country_icon" />{{ packageItem.get_goods.goods_name }}</h4>
												<div class="flex-common">
													<div class="price" v-html="packageItem.rank_price_format"></div>
													<span class="size_15">x{{ packageItem.goods_number }}</span>
												</div>
											</div>
										</div>
									</template>
									<van-cell-group class="van-cell-noright" v-if="goodsItem.membership_card_id > 0">
										<van-cell is-link>
											<template slot="title">
												<van-tag color="#ee0a24" size="medium" plain>{{$t('lang.give')}}</van-tag>
												<span style="font-size: 12px; color: #666; margin-left: 5px;">{{$t('lang.song')}}[{{goodsItem.membership_card_name}}]
													{{$t('lang.onezhang')}}</span>
											</template>
											<span style="color: #999;" @click="drpApplyHref(goodsItem.membership_card_id)">{{$t('lang.go_to_kk')}}</span>
										</van-cell>
									</van-cell-group>
								</li>
							</ul>
							<div class="t-jiantou t-jantou-xia" @click="onListShow(index)" v-if="item.goods && item.goods.length > 1">
								<span class="user-more">
									<i class="iconfont icon-moreunfold"></i>
								</span>
							</div>
						</div>

					</template>
				</section>
				
				<van-cell-group class="van-cell-noright m-top08">
					<van-cell :title="$t('lang.shipping_mode')" v-if="store_id == 0 && total.real_goods_count > 0">
						<div v-if="shipping_fee && shipping_fee[index] > 0">
							<div @click="feeHandle(index,item.shipping.default_shipping.shipping_id)" v-if="item.shipping.default_shipping.shipping_id > 0">
								<span>{{ shipping_name[index] }}</span>&nbsp;
								<label class="color-red" v-html="format_shipping_fee[index]"></label>
							</div>
							<div class="color-red" v-else>{{$t('lang.not_shipping')}}</div>
						</div>
						<div v-else>
							<div v-if="item.shipping.default_shipping.shipping_id > 0">
								<span>{{ shipping_name[index] }}</span>&nbsp;
								<label class="color-red">{{$t('lang.pinkage')}}</label>
							</div>
							<div class="color-red" v-else>{{$t('lang.not_shipping')}}</div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.buyer_message')" class="b-min">
						<div>
							<van-field v-model="postscriptValue[index]" maxlength="50" :placeholder="$t('lang.buyer_message_placeholder')" class="van-cell-ptb0" />
						</div>
					</van-cell>
					<van-cell class="b-min b-min-t">
						<div>
							<span>{{$t('lang.gong')}} {{ item.goods_count }} {{$t('lang.total_amount_propmt_alt')}}：</span>
							<label class="color-red" v-html="item.amount"></label>
						</div>
					</van-cell>
				</van-cell-group>
			</section>

			<!--其他清单-->
			<section class="checkout-goods-other quanymagin">
				<van-cell-group class="van-cell-noright">
					<van-cell :title="$t('lang.payment_mode')" v-model="pay_name" is-link @click="paymentSelect" />
					<van-cell :title="$t('lang.invoice_info')" class="b-min " @click="invoiceSelect" :is-link="!checkoutInfo.cross_border"
					 v-if="rec_type != 4 && checkoutInfo.can_invoice > 0">
						<div class="box-flex" solt="value">
							<template v-if="checkoutInfo.cross_border">
								<p class="receipt-title color-red">{{$t('lang.cross_border_no_invoice')}}</p>
							</template>
							<template v-else>
								<template v-if="invoice.invoiceType != 1">
									<!--普通发票和电子发票-->
									<p class="receipt-title">
										<template v-if="invoice.invoiceType == 0">{{$t('lang.plain_invoice')}} - </template>
                                        <template v-else-if="invoice.invoiceType == 2" >Эл.фактура - </template>

										<template v-if="!invoice.company">{{ invoice.invoiceTitle }}</template>
										<template v-else>{{ invoice.company_name }}</template>
									</p>
									<p class="receipt-name">{{ invoice.invoiceConent }}</p>
								</template>
								<template v-else>
									<!--增值税发票-->
									<p class="receipt-title">{{$t('lang.vat_tax_invoice')}}</p>
								</template>
							</template>
						</div>
					</van-cell>

					<!-- 红包 -->
					<van-cell v-model="bonusObject.bonusContent" class="b-min b-min-t" is-link v-if="bonusList.length>0" @click="bonusHandle">
						<template slot="title"><span>{{$t('lang.bonus')}}({{bonusList.length}}{{$t('lang.zhang')}})</span></template>
					</van-cell>
					
					<!--优惠券-->
					<van-cell v-model="couponsObject.couponsContent" class="b-min b-min-t" is-link v-if="couponsList.length>0" @click="couponsHandle">
						<template slot="title"><span>{{$t('lang.coupons')}}({{couponsList.length}}{{$t('lang.zhang')}})</span></template>
					</van-cell>

					<!--储值卡-->
					<van-cell v-model="valueCardObject.valueCardContent" value="" class="b-min b-min-t" is-link v-if="valueCard.length > 0"
					 @click="valueCardHandle">
						<template slot="title"><span>{{$t('lang.value_card')}}({{valueCard.length}}{{$t('lang.zhang')}})</span></template>
					</van-cell>
				</van-cell-group>
				
				<!--跨境-->
				<van-cell-group class="van-cell-noright m-top08" v-if="checkoutInfo.cross_border">
					<van-cell :title="$t('lang.real_name')" class="van-cell-title b-min b-min-b"></van-cell>
					<van-cell :title="$t('lang.truename')" class="b-min b-min-t">
						<div>
							<van-field type="text" v-model="checkout_real_name" :placeholder="$t('lang.fill_in_real_name')" class="van-cell-ptb0"></van-field>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.id_number')" class="b-min b-min-t">
						<div>
							<van-field type="tel" v-model="checkout_real_id" :placeholder="$t('lang.fill_in_id_number')" class="van-cell-ptb0"></van-field>
						</div>
					</van-cell>
					<van-cell class="b-min b-min-t" v-for="(item,index) in checkoutInfo.article_list" :key="index">
						<template slot="title">
							<div class="dis-box">
								<van-checkbox v-model="articleCheck" class="box-flex">{{$t('lang.checkout_help_article')}}</van-checkbox>
								<div class="color-289" @click="clickArticle(index)" v-if="item.title">《{{item.title}}》</div>
								<div @click="clickArticle(index)" v-else>{{$t('lang.article_not')}}</div>
							</div>
						</template>
					</van-cell>
				</van-cell-group>

				<!--vip-->
				<van-cell-group class="van-cell-noright m-top08" v-if="checkoutInfo.use_membership_card > 0 && vipCard.membership_card_discount_price > 0">
					<van-cell class="van-cell-title b-min b-min-b">
						<template slot="title">
							<div class="vip-zk">
								<i class="icon-vip"><img src="../../assets/img/newuser/icon-vip.png" class="img"></i>
								<div class="vip-zk-info">
									<div class="tit">
										<span>{{$t('lang.open_membership')}}</span>
										<label class="color-red">{{ vipCard.membership_card_discount_price_formated }}</label>
										<span>{{$t('lang.yuan')}}</span>
										<div class="i" @click="quanyiClick"><i class="iconfont icon-49"></i></div>
									</div>

									<div class="subtit">{{$t('lang.open_member')}}</div>
								</div>
							</div>
						</template>
						<template solt="value">
							<div class="vip-zk-price" :class="{'active':vipReceiveState}" @click="vipReceive">
								<div class="price color-red" :class="{'mf':!vipCard.membership_card_buy_money > 0}">
									{{vipCard.membership_card_buy_money > 0 ? vipCard.membership_card_buy_money_formated : $t('lang.drp_apply_title_5')}}
								</div>
								<i class="iconfont icon-gouxuan"></i>
							</div>
						</template>
					</van-cell>
				</van-cell-group>

				<!--积分、余额-->
				<van-cell-group class="van-cell-noright m-top08">
					<!--积分-->
					<van-cell v-if="checkoutInfo.allow_use_integral > 0 && checkoutInfo.integral.length > 0">
						<div slot="title">
							<font>{{$t('lang.in_commission')}}</font>
							<label class="color-red">{{ checkoutInfo.integral[0].integral }}</label>
							<font>{{$t('lang.points_deduction')}}</font>
							<label class="color-red" v-html="checkoutInfo.integral[0].integral_money_formated"></label>
						</div>
						<van-switch v-model="integralSelf" size="20px" class="fr" @change="integralSelfHandle" />
					</van-cell>

					<!--余额-->
					<van-cell class="van-cell-title b-min b-min-b" v-if="checkoutInfo.use_surplus > 0 && pay_code == 'onlinepay'">
						<div slot="title">{{$t('lang.is_use_balance')}}</div>
						<van-switch v-model="surplusSelf" size="20px" class="fr" />
					</van-cell>
					<van-cell class="b-min b-min-b" v-if="checkoutInfo.use_surplus > 0 && use_surplus_val > 0 && pay_code == 'onlinepay'">
						<template slot="title">
							<span>{{$t('lang.label_use_balance')}}</span>
						</template>
						<template solt="value">
							<div class="cell-value-flex">
								<div class="input-bor">
									<input type="number" v-model="surplus" min="1" :placeholder="$t('lang.fill_in_use_balance')" :max="checkoutInfo.user_money" @change="surplusSelfHandle"></div>

								<div class="user_money">{{$t('lang.label_sy_use_balance')}}{{ checkoutInfo.user_money_formated }}</div>
							</div>
						</template>
					</van-cell>
				</van-cell-group>

				<van-cell-group class="van-cell-noright m-top08 van-cell-total">
					<van-cell :title="$t('lang.goods_together')" class="van-cell-title b-min b-min-b">
						<div>
							<div class="color-red">
								<template v-if="rec_type == 5">{{$t('lang.label_presale_deposit')}}</template>
								<template v-if="rec_type == 1 && checkoutInfo.is_group_deposit == 1">{{$t('lang.label_group_deposit')}}</template>
								<label v-html="total.goods_price_formated"></label>
							</div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.taxes_dues')" v-if="rateFee > 0">
						<div>
							<em class="color-red">+ {{ currency }}{{ rateFee }}</em>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.goods_tax')" v-if="total.tax > 0">
						<div>
							<div class="color-red">+ <span v-html="total.tax_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.delivery_cost')" v-if="shippingFee > 0 && store_id == 0 && total.real_goods_count > 0">
						<div><em class="color-red">+ {{ currency }}{{ shippingFee }}</em></div>
					</van-cell>
					<van-cell :title="$t('lang.discount')" v-if="total.discount > 0">
						<div>
							<div class="color-red">- <span v-html="total.discount_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.integral')" v-if="total.integral_money > 0">
						<div>
							<div class="color-red">- <span v-html="total.integral_money_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.bonus')" v-if="bonusObject.bonusMoney > 0">
						<div>
							<div class="color-red">- <span v-html="total.bonus_money_formated"></span></div>
						</div>
					</van-cell>
					<template v-if="total.free_shipping_fee > 0">
						<van-cell :title="$t('lang.coupon_tab_3')">
							<div>
								<div class="color-red">- <span v-html="total.free_shipping_fee_formated"></span></div>
							</div>
						</van-cell>
					</template>
					<van-cell :title="$t('lang.coupons')" v-if="couponsObject.couponsMoney > 0">
						<div>
							<div class="color-red">- <span v-html="total.coupons_money_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.value_card_discount')" v-if="valueCardObject.vc_dis < 1">
						<div><em class="color-red">- {{ currency }}{{ valueCardObject.vc_dis_money }}</em></div>
					</van-cell>
					<van-cell :title="$t('lang.value_card')" v-if="valueCardObject.valueCardMoney > 0">
						<div>
							<div class="color-red">- <span v-html="total.card_money_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.membership_card_buy_money')" v-if="vipCard.order_membership_card_id > 0 && vipReceiveState  && vipCard.membership_card_buy_money > 0">
						<div>
							<div class="color-red">+ <span v-html="vipCard.membership_card_buy_money_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.membership_card_discount_price')" v-if="vipCard.order_membership_card_id > 0 && vipReceiveState && vipCard.membership_card_discount_price > 0">
						<div>
							<div class="color-red">- <span v-html="vipCard.membership_card_discount_price_formated"></span></div>
						</div>
					</van-cell>
					<van-cell :title="$t('lang.money')" v-if="total.surplus > 0">
						<div>
							<div class="color-red">- <span v-html="total.surplus_formated"></span></div>
						</div>
					</van-cell>
				</van-cell-group>
			</section>

			<!--提交按钮-->
			<section class="order-detail-submit order-checkout-submit quanymagin">
				<template v-if="rec_type == 4">
					<van-submit-bar :button-text="$t('lang.immediate_payment')" @submit="onSubmit">
						<div class="van-submit-bar-zdy">
							<span class="label-title">{{$t('lang.label_actual_payment')}}</span>
							<em class="color-red">{{ amountPrice }}{{$t('lang.integral')}}<template v-if="shippingFee > 0"> + {{ currency }}{{ shippingFee }}</template></em>
						</div>
					</van-submit-bar>
				</template>
				<template v-else>
					<template v-if="loading">
						<van-submit-bar :price="amountPrice * 100" :label="label_text" button-text="$t('lang.immediate_payment')"
						 :currency="currency" :loading="loading" />
					</template>
					<template v-else>
						<van-submit-bar :price="amountPrice * 100" :label="label_text" :button-text="$t('lang.immediate_payment')"
						 :currency="currency" :disabled="!articleCheck" :loading="loading" @submit="onSubmit" />
					</template>
				</template>
			</section>
		</div>
		
		<!-- 商品清单 -->
		<van-popup class="show-popup-coupon show-popup-common" round position="bottom" v-model="inventory" :overlay="overlay" closeable>
			<div class="title" >
				<strong>Тауар тізімі</strong>
				<div class="title_count">
					<em>{{$t('lang.gong')}} {{inventoryList.goods_count}} {{$t('lang.jian')}}</em>
					<i class="iconfont icon-close" @click="closeInventory"></i>
				</div>
			</div>
			<div class="content">
				<div class="goods_thumb">
					<ul>
						<li class="goods_thumb_li" v-for="(item,index) in inventoryList.goods" :key="index" @click="productLink(item)">
							<div class="goods_thumb_content">
								<div class="goods_thumb_left p-r">
									<img class="img" :src="item.goods_thumb" v-if="item.goods_thumb">
									<img class="img" src="../../assets/img/no_image.jpg" v-else>
									<em class="activity-tag" v-if="item.is_gift > 0"><img src="../../assets/img/gift-icon.png" /></em>
								</div>
								<div class="goods_thumb_right">
									<div class="goods_thumb_right_one twolist-hidden">{{item.goods_name}}</div>
									<div class="goods_thumb_price">
										<span class="goods_thumb_price_red">{{item.goods_price_format}}</span><span>x{{item.goods_number}}</span>
									</div>
									<div class="goods_thumb_right_ci">{{item.goods_attr}}</div>
								</div>
							</div>
						</li>
					</ul>
					<div class="goods_thumb_footer">Тауар бағасына күмәніңіз болса,басып анығын көріңіз.</div>
				</div>
			</div>
		</van-popup>

		<!--发票-->
		<van-popup v-model="showBase" position="right" :overlay="overlay" class="invoice_show">
			<section class="invoice-warp-box padding-all">
				<div class="header-tit">{{$t('lang.invoice_type')}}</div>
				<ul class="ect-selects">
					<li class="ect-select" :class="{'active':invoice.invoiceType == 0}" @click="incrementHandle(0)"><span>{{$t('lang.plain_invoice')}}</span></li>
					<li class="ect-select" :class="{'active':invoice.invoiceType == 2}" @click="incrementHandle(1)" v-if="invoice.is_shouqianba > 0"><span>Эл.фактура</span></li>
					<li class="ect-select" :class="{'active':invoice.invoiceType == 1,'btn-box':!invoice.increment}" @click="incrementHandle(2)"><span>{{$t('lang.vat_invoice')}}</span></li>
				</ul>
				<p>{{$t('lang.checkout_inv_help')}}</p>
			</section>
			<section class="invoice-cont-box" v-if="invoice.invoiceType != 1">
				<ul class="ect-selects">
					<li class="ect-select" :class="{'active':invoice.company == false}" @click="invTab(1)">
						<span>{{$t('lang.person')}}</span></li>
					<li class="ect-select" :class="{'active':invoice.company == true}" @click="invTab(2)">
						<span>{{$t('lang.company')}}</span></li>
				</ul>
				<div class="invoice-cont-1 van-cell-field" v-if="invoice.company">
					<!-- <van-cell :title="$t('lang.label_company_name')" :value="invoice.company_name" is-link class="my-cell-nobottom"
					 @click="companySelect" v-if="invoice.invoiceCompany.length > 0"></van-cell> -->
					<van-field :label="$t('lang.label_company_name')" class="my-cell-nobottom" v-model="invoice.company_name"
					 :placeholder="$t('lang.fill_in_company_name')" />
					<van-field :label="$t('lang.taxpayer_id_number')" :placeholder="$t('lang.enter_taxpayer_id_number')" v-model="invoice.company_tid" />
				</div>
				<div class="invoice-cont-1 m-top08" v-if="invoice.invoiceType == 2">
                    <van-field label="E-mail：" placeholder="Эл.пошта енгізіңіз" class="my-cell-nobottom" v-model="invoice.inv_email"/>
                    <van-field label="Телефон：" placeholder="Телефон нөмірді енгізіңіз" v-model="invoice.inv_mobile"/>
                </div>
				<div class="invoice-cont-2">
					<div class="header-tit">{{$t('lang.invoice_content')}}</div>
					<van-radio-group v-model="invoice.invValueRadio" @change="invValueRadioHandle">
						<van-radio :name="index" v-for="(item,index) in invoiceValue" :key="index">{{item}}</van-radio>
					</van-radio-group>
				</div>
			</section>
			<div class="my-box">
				<div class="ect-button-more">
					<div class="btn btn-submit" @click="submitTaxBtn">{{$t('lang.confirm')}}</div>
				</div>
			</div>
		</van-popup>

		<!--单位发票-->
		<van-popup class="show-popup-bottom" v-model="invoice.companyShow" position="bottom">
			<div class="goods-show-title padding-all">
				<h3 class="fl">{{$t('lang.company_name')}}</h3>
				<i class="iconfont icon-close fr" @click="companyClose"></i>
			</div>
			<div class="s-g-list-con" v-if="invoice.invoice_id">
				<div class="select-two">
					<ul>
						<li class="ect-select" v-for="(item,index) in invoice.invoiceCompany" :key="index" :class="{'active':invoice.invoice_id == item.invoice_id}"
						 @click="invoice_company_select(item.invoice_id,item.inv_payee,item.tax_id)">
							<label class="dis-box">
								<span class="box-flex">{{ item.inv_payee }}</span>
								<i class="iconfont icon-gou"></i>
							</label>
						</li>
					</ul>
				</div>
			</div>
		</van-popup>

		<!--红包-->
		<van-popup class="show-popup-common show-popup-bonuslist" v-model="bonusObject.bonusBase" position="bottom" :overlay="overlay">
			<div class="title">
				<strong>Конверт</strong>
				<i class="iconfont icon-close" @click="closeBonus"></i>
			</div>
			<div class="content">
				<div class="popup-bonus-list">
					<ul class="flow-couon-list bonus-list">
						<li class="new-coupons-box bonus-select-box" :class="{'active':bonusObject.bonusId == item.bonus_id, 'disabled': bonusObject.bonusId != item.bonus_id && bonusDisabled}"
						 @click="bonusActive(item.bonus_id)"
						 v-for="(item,index) in bonusList" :key="index"
						>
							<div class="cont dis-box">
								<div class="bonus-left">
									<div class="img-box">
										<div class="color-red"><strong class="bonus-money">{{ currency }}{{item.type_money}}</strong>
										</div>
									</div>
								</div>
								<div class="box-flex bonus-right">
									<p class="bonus-tit"><strong>{{item.type_name}}</strong></p>
									<p class="bonus-desc">{{ $t('lang.man') }}{{item.min_goods_amount}}{{ $t('lang.price_unit') }}{{ $t('lang.have_access_to') }}</p>
								</div>
							</div>
							<div class="time">{{$t('lang.label_service_life')}}{{item.use_start_date}} {{$t('lang.zhi')}} {{item.use_end_date}}</div>
							<div class="new-store-radio-box"><i class="iconfont icon-gou"></i></div>
						</li>
					</ul>
				</div>
				<div class="footer">
					<div class="btn" @click="submitBonus">{{$t('lang.confirm')}}</div>
				</div>
			</div>
		</van-popup>

		<!--储值卡-->
		<van-popup class="show-popup-common show-popup-valuecard" v-model="valueCardObject.valueCardBase" position="bottom" :overlay="overlay">
			<div class="title">
				<strong>Төлем карта</strong>
				<i class="iconfont icon-close" @click="closeValuecard"></i>
			</div>
			<div class="content">
				<div class="cart_tabs">
					<div :class="['tab_item', index == currTab ? 'active_tab' : '']" v-for="(item, index) in tabs" :key="index" @click="onClickTab(index)">
						{{item}}{{index == currTab ? `(${cardCount})` : ''}}
					</div>
				</div>
				<template v-for="(item, index) in cartList">
					<div class="value-card-list" v-show="currTab == index" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300" :key="index">
						<dsc-value-cart :list="item" page="checkout" :active="valueCardObject.valueCardId" :type="index" @link="goLink"></dsc-value-cart>
						<div class="no_data" v-if="shopEmpty">
							<p>Өкінішті</p>
							<p>{{currTab == 1 ? 'Сізде әлі жарамсыз карта жоқ' : 'Сізде әлі жарамды карта жоқ'}}</p>
						</div>
						<div class="loadmore" v-show="item.length >= size">{{loadmoreStatus}}</div>
					</div>
				</template>
				
				<div class="footer">
					<div class="btn" @click="submitValuecard">{{$t('lang.confirm')}}</div>
				</div>
			</div>
		</van-popup>

		<!--配送方式-->
		<van-popup class="show-popup-bottom" v-model="feeShow" position="bottom">
			<div class="goods-show-title padding-all">
				<h3 class="fl">{{$t('lang.shipping_mode')}}</h3>
				<i class="iconfont icon-close fr" @click="feeclose"></i>
			</div>
			<div class="s-g-list-con">
				<div class="select-two">
					<ul>
						<li class="ect-select" v-for="(item,index) in shippingList" :key="index" :class="{'active':shipping_id[shipping_active_id] == item.shipping_id}"
						 @click="shipping_select(item)">
							<label class="dis-box">
								<span class="box-flex">{{ item.shipping_name }}</span>
								<i class="iconfont icon-gou"></i>
							</label>
						</li>
					</ul>
				</div>
			</div>
		</van-popup>

		<!--支付方式-->
		<van-popup class="show-popup-common payment-method-popup" v-model="paymentShow" position="bottom">
			<div class="title">
				<strong>{{$t('lang.payment_mode')}}</strong>
				<i class="iconfont icon-close fr" @click="paymentClose"></i>
			</div>
			<div class="content">
				<div class="tip" :class="{'tipscroll':tipscroll}">
					<i class="iconfont icon-lingdang"></i>
					<div class="txt">Төлем түрін өзгертсеңіз заказдың төлеу сомасына,заказды жолдауға әсер етуі мүмкін,мұқият болыңыз.</div>
					<i class="iconfont icon-close" @click="tipscroll = false"></i>
				</div>
				<div class="select-tabs">
					<div class="select-list" v-for="(item,index) in payment_method" :key="index" :class="{'active':pay_id == item.pay_id}" @click="payment_method_select(item.pay_id,item.pay_name,item.pay_code)">
					<span>{{ item.pay_name }}</span>
					</div>
				</div>
			</div>
		</van-popup>

		<!--选择店铺-->
		<van-popup class="show-popup-bottom border-top" v-model="shopShow" position="bottom">
			<div class="goods-show-title padding-all">
				<h3 class="fl">Дүкен таңда</h3>
				<i class="iconfont icon-close fr" @click="storeclose"></i>
			</div>
			<div class="s-g-list-con">
				<div class="select-two">
					<ul>
						<li class="ect-select" v-for="(item,index) in actVal" :key="index" :class="{'active': store_ids == item.store_id}"
						 @click="store_method_select(item.store_id,item.store_name,item.user_id,index)">
							<label class="dis-box">
								<img src="../../assets/img/more_icon.png" class="dis_img" />
								<span class="box-flex dis_shop">{{item.store_name}}</span>
								<i class="iconfont icon-gou"></i>
							</label>
						</li>
					</ul>
				</div>
			</div>
		</van-popup>

		<!--支付密码方式-->
		<van-popup class="paypwdShow" v-model="paypwdShow">
			<van-password-input :value="paypwdValue" :info="$t('lang.paypwd_value_info')" @focus="showKeyboard = true" />
			<van-number-keyboard :show="showKeyboard" @input="onInput" @delete="onDelete" @blur="showKeyboard = false" @hide="onHide" />
		</van-popup>

		<!--协议文章-->
		<van-popup class="show-popup-bottom" style="top: 0" v-model="articleShow" position="bottom">
			<div class="goods-show-title padding-all text-all">
				<h3 class="fl">{{showArticle.title}}</h3>
				<i class="iconfont icon-close fr" @click="articleClose"></i>
			</div>
			<div class="s-g-list-con padding-all" style="max-height: none;">
				<div class="t-remark m-b10">{{showArticle.add_time}}</div>
				<div v-html="showArticle.content"></div>
			</div>
		</van-popup>

		<!--权益卡-->
		<van-popup class="quanyiShow" v-model="quanyiShow">
			<div class="quanyi-top">
				<i class="icon-vip"><img src="../../assets/img/newuser/icon-vip.png" class="img"></i>
				<div class="name">{{ vipCard.name }}</div>
				<i class="iconfont icon-close" @click="handelClose"></i>
			</div>
			<div class="quanyi-xian"><span>{{$t('lang.interests')}}</span></div>
			<div class="quanyi-list">
				<div class="item" v-for="(item,index) in vipCard.user_membership_card_rights_list" :key="index">
					<i class="icon"><img :src="item.icon" class="img" /></i>
					<div class="txt">{{item.name}}</div>
				</div>
			</div>
		</van-popup>
		
		<!--优惠券选择-->
		<van-popup class="show-popup-common show-popup-coupon" v-model="couponsObject.couponsBase" position="bottom">
			<div class="title">
				<strong>Купон</strong>
				<i class="iconfont icon-close" @click="closeCoupons"></i>
			</div>
			<div class="content">
				<div class="usable-coupon-number">Жарамды ({{couponsList.length}})</div>
				<div class="usable-coupon-money">{{couponsObject.couponsIdArr && couponsObject.couponsIdArr.length}} купон қолданылды,үнемдеу <em class="color-red">{{ currency }}{{couponsObject.couponsMoney}}</em></div>
				<div class="coupons-list" v-if="shopCouponList">
					<ul v-for="(ruItem,ruIndex) in shopCouponList" :key="ruIndex">
						<li v-for="(item,index) in ruItem.list" :key="index" @click="couponValClick(item.uc_id,item.cou_money,ruIndex)">
							<div class="left">
								<div class="coupon-price">{{item.cou_money_formated}}</div>
								<div class="coupon-desc">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</div>
							</div>
							<div class="right">
								<div class="coupon-tit">
									<em class="tag">{{item.cou_type_name}}</em>
									<span>{{$t('lang.limit')}}{{item.shop_name}}{{$t('lang.usable')}}[{{item.cou_goods_name}}]</span>
								</div>
								<div class="time">{{item.cou_end_time}}</div>
								<div class="checkbox">
									<div class="van-radio">
										<span class="van-radio__icon van-radio__icon--round" :class="[couponsObject.couponsId[ruIndex] && (couponsObject.couponsId[ruIndex].uc_id == item.uc_id) ? 'van-radio__icon--checked' : '']"><i class="van-icon van-icon-success"></i></span>
									</div>
								</div>
							</div>
						</li>
					</ul>
				</div>
				<div class="footer">
					<div class="btn" @click="submitCoupons()">Иа</div>
				</div>
			</div>
		</van-popup>

		<CommonNav></CommonNav>
	</div>
</template>

<script>
	import qs from 'qs'

	import { mapState } from 'vuex'

	import {
		Checkbox,
		CheckboxGroup,
		Cell,
		CellGroup,
		SubmitBar,
		GoodsAction,
		GoodsActionBigBtn,
		GoodsActionMiniBtn,
		Field,
		Dialog,
		Popup,
		RadioGroup,
		Radio,
		Toast,
		Switch,
		PasswordInput,
		NumberKeyboard,
		Tag,
		Waterfall
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	
	import dscValueCart from '@/components/dsc-value-cart/dsc-value-cart.vue'

	export default {
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				loading: false,
				checkoutDisplay: false,
				postscriptValue: [],
				showBase: false,
				overlay: true,
				invoice: {
					company: false,
					company_name: '',
					company_tid: '',
					increment: false,
					invoiceType: 0,
					invoiceTitle: this.$t('lang.person'),
					invoiceConent: this.$t('lang.no_invoice'),
					invoiceCompany: [],
					tax_id: 0,
					invValueRadio: 0,
					companyShow: false,
					invoice_id: null,
					vat_id: 0,
					is_shouqianba:0
				},
				bonusObject: {
					bonusContent: this.$t('lang.no_use_bonus'),
					bonusBase: false,
					bonusId: 0,
					bonusMoney: 0,
					bonusSn: 0
				},
				couponsObject: {
					couponsContent: this.$t('lang.no_use_coupons'),
					couponsBase: false,
					couponsId: [],
					couponsMoney: 0,
					couponsIdArr: []
				},
				valueCardObject: {
					valueCardContent: this.$t('lang.no_use_value_card'),
					valueCardBase: false,
					valueCardId: 0,
					valueCardMoney: 0,
					vc_dis: 1,
					vc_dis_money: 0
				},
				bonusDisabled: false,
				couponsDisabled: false,
				feeShow: false,
				paymentShow: false,
				pay_code: '',
				pay_id: null,
				pay_name: '',
				shippingList: [],
				shipping_active_id: 0,
				use_surplus_val: 0,
				use_integral_val: 0,
				price: 0,
				rec_type: this.$route.query.rec_type ? this.$route.query.rec_type : 0,
				type_id: this.$route.query.type_id ? this.$route.query.type_id : 0,
				store_id: this.$route.query.store_id > 0 ? this.$route.query.store_id : 0,
				store_ids: this.$route.query.store_ids > 0 ? this.$route.query.store_ids : 0,
				rec_id: this.$route.query.rec_id ? this.$route.query.rec_id : '',
				ru_id: this.$route.query.ru_id,
				goods_id: this.$route.query.goods_id ? this.$route.query.goods_id : '',
				store_act: 0,
				store_type: '',
				store_mobile: 0,
				take_time: '',
				bs_id: 0,
				team_id: this.$route.query.team_id,
				t_id: 0,
				extension_id: 0, //扩展活动id
				currency: '₸',
				label_text: this.$t('lang.label_total_amount_payable'),
				shipping_id: [],
				shipping_code: [],
				shipping_type: [],
				shipping_name: [],
				format_shipping_fee: [],
				shipping_fee: [],
				rate_price: [],
				checkout_real_name: '',
				checkout_real_id: '',
				paypwdShow: false,
				paypwdValue: '',
				showKeyboard: true,
				articleCheck: true,
				articleShow: false,
				showArticle: {},
				listPackageShow: false,
				surplus: '',
				surplus_status: false,
				timer: '',
				vipReceiveState: false,
				quanyiShow: false,
				back: '',
				storepick: '',
				stor: this.$route.query.stor ? this.$route.query.stor : 0,
				shopShow: false,
				actVal: [],
				isSingle: this.$route.query.isSingle ? this.$route.query.isSingle : '',
				inventory:false ,// 商品清单
				inventoryList:[],
				disabled: false,
				isLoading:true,
				size:10,
				currTab: 0,
				tabs: ['Жарамды', 'Жарамсыз'],
				loadmoreStatus: 'Жүктеу...',
				shopEmpty: false,
				cartList: [],
				cartPaginated: [],
				valueCartInfo: {},
				tipscroll:false,
				uc_id: this.$route.query.uc_id ? this.$route.query.uc_id : 0,
				invoiceData:{}
			}
		},
		components: {
			[Checkbox.name]: Checkbox,
			[CheckboxGroup.name]: CheckboxGroup,
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
			[SubmitBar.name]: SubmitBar,
			[GoodsAction.name]: GoodsAction,
			[GoodsActionBigBtn.name]: GoodsActionBigBtn,
			[GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
			[Field.name]: Field,
			[Dialog.name]: Dialog,
			[Popup.name]: Popup,
			[RadioGroup.name]: RadioGroup,
			[Radio.name]: Radio,
			[Toast.name]: Toast,
			[Switch.name]: Switch,
			[PasswordInput.name]: PasswordInput,
			[NumberKeyboard.name]: NumberKeyboard,
			[Tag.name]: Tag,
			CommonNav,
			dscValueCart
		},
		created() {
			let that = this
			let url = ''
			if (that.rec_type > 0) {
				url = '../../pages/checkout/checkout?rec_type=' + that.rec_type + '&type_id=' + that.type_id
			} else {
				url = '../../pages/checkout/checkout'
			}

			setTimeout(() => {
				uni.getEnv(function(res) {
					if (res.plus || res.miniprogram) {
						uni.redirectTo({
							url: url
						})
					}
				})
			}, 100)
			
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if (configData) {
				this.currency = configData.currency_format.replace('%s', '');
			}

			this.checkoutDefault()

			//购物车商品列表
			if(!this.isSingle){
				this.goodsList()
			}
		},
		computed: {
			...mapState({
				checkoutInfo: state => state.shopping.checkoutInfo,
				checkout_done: state => state.shopping.checkout_done,
			}),
			goodsCartList: {
				get() {
					return this.$store.state.shopping.goodsCartList
				},
				set(val) {
					this.$store.state.shopping.goodsCartList = val
				}
			},
			consignee_title() {
				if (this.checkoutInfo.consignee) {
					return this.checkoutInfo.consignee.consignee + ' ' + this.checkoutInfo.consignee.mobile
				} else {
					return ''
				}
			},
			consignee_address() {
				if (this.checkoutInfo.consignee) {
					return this.checkoutInfo.consignee.province_name + this.checkoutInfo.consignee.city_name + this.checkoutInfo.consignee.district_name + this.checkoutInfo.consignee.street_name + this.checkoutInfo.consignee.address
				} else {
					return ''
				}
			},
			address_id() {
				if (this.checkoutInfo.consignee) {
					return this.checkoutInfo.consignee.address_id ? this.checkoutInfo.consignee.address_id : ''
				} else {
					return ''
				}
			},
			cart_id() {
				let rec_ids = []
				this.checkoutInfo.goods_list.forEach((v) => {
					v.goods.forEach((res) => {
						rec_ids.push(res.rec_id)
					})
				})

				return rec_ids
			},
			bonusList() {
				return this.checkoutInfo.bonus_list ? this.checkoutInfo.bonus_list : 0
			},
			couponsList() {
				return this.checkoutInfo.coupons_list ? this.checkoutInfo.coupons_list : 0
			},
			valueCard() {
				return this.checkoutInfo.value_card ? this.checkoutInfo.value_card : 0
			},
			isLogin() {
				return localStorage.getItem('token') == null ? false : true
			},
			payment_method() {
				return this.checkoutInfo.payment_list ? this.checkoutInfo.payment_list : ''
			},
			total: {
				get() {
					return this.checkoutInfo.total ? this.checkoutInfo.total : ''
				},
				set(val) {
					console.log(val)
				}
			},
			amountPrice: {
				get() {
					let priceStr = ''
					if (this.rec_type == 4) {
						//积分兑换
						this.price = this.total.exchange_integral
						this.currency = ''

						return this.price
					} else {
						//普通商品价格
						if (this.price == 0) {
							this.price = this.total.amount
						}
						
						if ((this.surplusSelf && this.surplus > 0) || this.surplus_status === true) {
							priceStr = this.total.amount
						} else {
							priceStr = this.price

							if (this.shippingFee && this.total.real_goods_count > 0) {
								if(this.total.card_money > 0 && this.price >= this.shippingFee){
									priceStr = this.price + this.shippingFee
								} else if (this.total.card_money == 0){
									priceStr = this.price + this.shippingFee
								}
							}
						}

						//计算税费
						if(this.rateFee){
							priceStr = priceStr + this.rateFee
						}

						if(priceStr > 0 && this.total.free_shipping_fee > 0){
							priceStr = priceStr - this.total.free_shipping_fee
						}
						
						return Number(priceStr)
					}
				},
				set(val) {
					this.price = val
				}
			},
			shipping() {
				let arr = []
				if (this.checkoutInfo.goods_list) {
					this.checkoutInfo.goods_list.forEach((v) => {
						arr.push(v.shipping.default_shipping)
					})
				}

				return arr
			},
			shippingFee() {
				let shippingFeePrice = 0
				if (this.shipping_fee && this.store_id == 0) {
					this.shipping_fee.forEach(v => {
						shippingFeePrice += Number(v) * 100
					})
				}
				
				return shippingFeePrice / 100
			},
			rateFee() {
				let ratePrice = 0
				if (this.rate_price && this.store_id == 0) {
					this.rate_price.forEach(v => {
						ratePrice += Number(v)
					})
				}
				return Number(parseFloat(ratePrice).toFixed(2));
			},
			surplusSelf: {
				get() {
					return this.use_surplus_val == 0 ? false : true
				},
				set(val) {
					this.use_surplus_val = val == true ? 1 : 0
				}
			},
			integralSelf: {
				get() {
					return this.use_integral_val == 0 ? false : true
				},
				set(val) {
					this.use_integral_val = val == true ? 1 : 0
				}
			},
			integral() {
				return this.checkoutInfo.integral.length > 0 ? this.checkoutInfo.integral[0].integral : 0
			},
			storeInfo() {
				return this.checkoutInfo.store
			},
			storeCart() {
				return this.checkoutInfo.store_cart
			},
			listShow() {
				return this.$store.state.shopping.listShow
			},
			invoiceValue() {
				//电子发票只显示明细 invoice.invoiceType == 2
				return this.invoice.invoiceType == 2 ? ['Деталы'] : this.checkoutInfo.invoice_content
			},
			use_paypwd() {
				return this.checkoutInfo.use_paypwd ? this.checkoutInfo.use_paypwd : 0
			},
			use_paypwd_open(){
				return this.checkoutInfo.use_paypwd_open ? this.checkoutInfo.use_paypwd_open : 0
			},
			vipCard() {
				return this.checkoutInfo.use_membership_card > 0 ? this.checkoutInfo.membership_card_info : ''
			},
			//优惠券按店铺分割
			shopCouponList(){
				let dataArr = [];

				if(this.couponsList.length > 0){
					this.couponsList.map((mapItem)=>{
						if (dataArr.length == 0) {
				            dataArr.push({ ru_id: mapItem.ru_id, list: [mapItem] })
				        } else {
				            let res = dataArr.some((item) => {
				              //判断相同的店铺，有就添加到当前项
				              if (item.ru_id == mapItem.ru_id) {
				                item.list.push(mapItem)
				                return true
				              }
				            })
				            if (!res) {
				              //如果没找相同的部门添加一个新对象
				              dataArr.push({ ru_id: mapItem.ru_id, list: [mapItem] })
				            }
				        }
					})
				}
				
				return dataArr
			},
			cardCount: function () {
				if (this.valueCartInfo.card_list) {
					if (this.currTab == 0) {
						return this.valueCartInfo.use_card_count || 0
					} else {
						return this.valueCartInfo.not_use_card_count || 0
					}
				} else {
					return 0
				}
			}
		},
		methods: {
			async valueCardLoad() {
				this.isLoading = true;
				let i = this.currTab;
				
				if (this.cartList.length == 0) this.cartList = this.tabs.map(() => []);
				if (this.cartPaginated.length == 0) this.cartPaginated = this.tabs.map(() => 1);
				
				let page = this.cartList[i].length / this.size;
				
				page = Math.ceil(page) + 1
				
				const { data } = await this.$store.dispatch('getValueCard', {
					page: page,
					size: this.size,
					rec_id: this.checkoutInfo.total.rec_list.join(),
					use_type: i == 0 ? 1 : 0
				});
				
				const { card_list } = data;
				
				this.valueCartInfo = data;
				
				if (card_list) {
					this.$set(this.cartPaginated, i, card_list.length < this.size ? 0 : 1);
					
					this.$set(this.cartList, i, [...this.cartList[i], ...card_list]);
					
					this.shopEmpty = this.cartList[i].length == 0;
					this.loadmoreStatus = card_list.length < this.size ? 'Басқа жоқ' : 'Жүктеуде...';
				};
				
				this.$nextTick(() => {
					this.disabled = false;
					this.isLoading = false;
				})
			},
			onClickTab(i) {
				if (this.currTab == i) return;
				this.currTab = i;
				this.shopEmpty = false;
				this.loadmoreStatus = this.cartList[i].length == this.cardCount ? 'Басқа жоқ' : 'Жүктеуде...';
				if (this.cartList[i].length == 0) this.valueCardLoad();
			},
			loadMore(){
				if (this.isLoading) return;
				this.disabled = true;
				//瀑布流分页
				setTimeout(() => {
					let i = this.currTab;
					let isMore = this.cartPaginated[i];
					if (isMore > 0) {
						this.loadmoreStatus = 'Жүктеуде...';
						this.valueCardLoad();
					} else {
						this.loadmoreStatus = 'Басқа жоқ';
					}
				},200)
			},
			goLink(res) {
				if (this.currTab != 0) return;
				const { type, value: { vid } } = res;
				if (type == 'active') this.valueCardActive(vid);
			},
			shop_kud() {
			   if(this.goods_id){
				   this.$router.push({
				   	name: 'checkout',
				   	query:{
				   		type:true,
				   		num: this.$route.query.num,
				   		spec_arr: this.$route.query.spec_arr,
				   		goods_id: this.goods_id,
				   		store_id: this.store_id,
				   		rec_id:this.rec_id,
				   		ru_id:this.ru_id,
				   		isSingle:this.isSingle
				   	}
				   })
			   }else{
				   this.$router.push({
				   	name: 'checkout',
				   	query:{
				   		type:true,
				   		rec_id:this.rec_id,
				   		ru_ids:this.ru_id,
				   		store_ids:this.store_id
				   	}
				   })
			   }
			},
			//购物车列表
			goodsList() {
				this.$store.dispatch('setGoodsCart', {
					warehouse_id: 0,
					area_id: 0
				})
			},
			shop_show() {
				this.shopShow = true
			},
			checkoutDefault() {
				if (this.isLogin) {
					if (this.store_id > 0) {
						this.$store.dispatch('setShoppingCheckout', {
							rec_type: this.rec_type,
							store_id: this.store_id,
							leader_id: this.$route.query.leader_id || 0
						})
					} else {
						this.$store.dispatch('setShoppingCheckout', {
							rec_type: this.rec_type,
							type_id: this.type_id,
							team_id: this.team_id,
							leader_id: this.$route.query.leader_id || 0
						})
					}
				} else {
					let msg = this.$t('lang.login_user_invalid')
					this.notLogin(msg)
				}

				let configData = JSON.parse(sessionStorage.getItem('configData'));
				if (configData) {
					this.currency = configData.currency_format.replace('%s', '');
					this.invoice.is_shouqianba = configData.is_shouqianba;
				}

			},
			checkoutAdd() {
				if (this.isLogin) {
					if (this.rec_id) {
						this.$router.push({
							name: 'storeGoods',
							query: {
								rec_id: this.rec_id,
								ru_id: this.ru_id
							}
						})
					}else if(this.goods_id){
						this.$router.push({
							name: 'storeGoods',
							query: {
								id: this.goods_id,
								spec_arr: this.$route.query.attr_id,
								num: this.$route.query.num,
							}
						})
					}else {
						Toast(this.$t('lang.select_store_goods'));
					}
				}else {
					let msg = this.$t('lang.login_user_not')
					this.notLogin(msg)
				}
			},
			checkoutAddress() {
				if (this.checkoutInfo.consignee.nearbyleader > 0) {
					const lngAndLat = {
						lng: this.checkoutInfo.consignee.lng,
						lat: this.checkoutInfo.consignee.lat
					}
					sessionStorage.setItem('addressLngLat', JSON.stringify(lngAndLat))
				}

				let value = {
					routerLink: 'checkout'
				}

				if (this.$route.query) {
					value = {
						routerLink: 'checkout',
						rec_type: this.rec_type,
						type_id: this.type_id,
						team_id: this.team_id,
						nearbyleader: this.checkoutInfo.consignee.nearbyleader || 0
					}
				}

				this.$router.push({
					name: 'address',
					query: value
				})
			},
			onSubmit() {
				let shop_id = []
				let url = window.location.href

				this.checkoutInfo.goods_list.forEach((v) => {
					shop_id.push(v.ru_id)
				})

				/* 根据活动rec_type值判断type_id 是什么活动id*/
				if (this.rec_type == 1 || this.rec_type == 2 || this.rec_type == 5 || this.rec_type == 6) {
					this.extension_id = this.type_id
				} else if (this.rec_type == 7) {
					this.t_id = this.type_id

					if (this.pay_code == 'cod' && this.use_surplus_val == 0) {
						Toast(this.$t('lang.team_not_pay_delivery'))
						return false
					}
				} else if (this.rec_type == 8) {
					this.bs_id = this.type_id
				}

				//支付密码判断
				if ((this.pay_code == 'balance' || this.use_surplus_val > 0 || this.valueCardObject.valueCardId > 0) && this.use_paypwd > 0 && this.paypwdValue.length < 6) {
					if(this.use_paypwd_open > 0){
						this.paypwdShow = true
						this.showKeyboard = true
					}else{
						Dialog.confirm({
			              	message: 'Төлем паролы қосылмады,қосу керек бе？',
			              	className: 'text-center'
				        }).then(() => {
				            this.$router.push({
				            	name: 'accountsafe'
				            })
				        }).catch(() => {
				          	
				        })
					}
					return false
				}
				

				if (this.checkoutInfo.cross_border) {
					let reg = !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(this.checkout_real_id);

					if (this.checkout_real_name == '') {
						Toast(this.$t('lang.truename_not_null'));
						return false
					} else if (this.checkout_real_id == '') {
						Toast(this.$t('lang.id_number_not_null'));
						return false
					} else if(reg){
						Toast(this.$t('lang.id_number_format_error'));
						return false
					}
				}

				if (Number(this.surplus) > Number(this.checkoutInfo.user_money)) {
					Toast(this.$t('lang.use_balance_propmt_1'))
					return false
				}
				
				this.loading = true

				//发票
				let inv_payee = this.invoice.company ? this.invoice.company_name : this.invoice.invoiceTitle
				let tax_id = this.invoice.invoiceType == 1 ? this.invoice.tax_id : this.invoice.company_tid
				let isRecId = this.checkoutInfo.isshipping_list

				if(isRecId.length == 0){
					Toast(this.$t('lang.not_shipping'))

					setTimeout(() => {
						this.$router.push({
							name: 'cart'
						})
					}, 600)
					
					return
				}
				
				let o = {
					cart_value: isRecId, //购物车id
					flow_type: this.checkoutInfo.flow_type, //购物类型
					store_id: this.store_id, //门店id
					store_type: '', //门店类型、自营/商家
					store_mobile: this.store_mobile, //门店电话
					take_time: this.take_time, //门店提货时间
					pay_type: 1, //支付方式类型
					pay_id: this.pay_id, //支付方式id
					ru_id: shop_id, //商家id
					shipping: this.shipping_id, //配送方式
					shipping_code: this.shipping_code, //配送方式code
					shipping_type: this.shipping_type, //是否是上门自取
					point_id: 0, //自提编号
					shipping_dateStr: 0, //自提时间
					is_surplus: this.use_surplus_val, //是否使用余额
					surplus: this.surplus, //使用余额值
					use_integral: this.use_integral_val, //是否使用积分
					integral: this.integral, //本单可使用积分
					bonus_id: this.bonusObject.bonusId, //红包id
					bonus: this.bonusObject.bonusMoney, //红包金额
					bonus_sn: this.bonusObject.bonusSn, //红包编号
					uc_id: this.couponsObject.couponsIdArr, //优惠券id
					coupons: this.couponsObject.couponsMoney, //优惠券金额
					vc_id: this.valueCardObject.valueCardId, //储值卡id
					use_value_card: this.valueCardObject.valueCardMoney, //储值卡金额
					vc_dis_money: this.valueCardObject.vc_dis_money,
					goods_amount: this.amountPrice, //商品总价
					discount: 0, //优惠金额
					how_oos: 0, //缺货处理
					postscript: this.postscriptValue, //订单留言
					invoice: 0,
					inv_type: this.invoice.invoiceType, //发票类型
					inv_payee: inv_payee, //发票类别名称
					tax_id: tax_id, //纳税人识别码
					inv_content: this.invoice.invoiceConent, //发票内容
					need_inv: 0,
					card_message: '', //贺卡信息
					tax: 0, //税
					pack: 0,
					card: 0,
					vat_id: this.invoice.vat_id, //增值税发票id
					need_insure: 0,
					bs_id: this.bs_id, //砍价id
					t_id: this.t_id, //拼团活动id
					team_id: this.team_id, //拼团开团活动id
					extension_id: this.extension_id, //扩展活动id
					rel_name: this.checkout_real_name, //身份证姓名
					id_num: this.checkout_real_id, //身份证号
					address_id: this.address_id, //收货地址id
					pay_pwd: this.paypwdValue, //支付密码
					referer: 'H5', //订单来源
					order_membership_card_id: this.vipReceiveState ? this.vipCard.order_membership_card_id : 0, //权益卡id
					membership_card_discount_price: this.vipCard.membership_card_discount_price, //购买权益卡折扣
					leader_id: this.$route.query.leader_id || 0
				}

				//判断是否是门店自提
				if (this.store_id > 0) {
					//判断是否支持配送方式
					if (this.checkoutInfo.noshipping_list.length > 0) {
						Dialog.confirm({
							message: this.$t('lang.noshipping_list_cur_propmt'),
							className: 'text-center'
						}).then(() => {
							this.$store.dispatch('setCheckoutSubmit', o).then((res) => {
								if (res.data.error == 1) {
									Toast(res.data.msg)
									this.loading = false
								} else {
									Toast.success({
										duration: 1000,
										forbidClick: true,
										loadingType: 'spinner',
										message: this.$t('lang.submit_success')
									})
									this.$router.push({
										name: 'done',
										query: {
											order_sn: res.data
										}
									})
								}
							})
						}).catch(() => {
							this.$router.push({
								name: 'cart'
							})
						})
					} else if (this.checkoutInfo.isshipping_list.length > 0) {
						this.$store.dispatch('setCheckoutSubmit', o).then(res => {
							if (res.data.error == 1) {
								if (res.data.url) {
									Dialog.confirm({
										message: res.data.msg,
										className: 'text-center'
									}).then(() => {
										window.location.href = res.data.url
									})
								} else {
									Toast(res.data.msg);
								}

								this.loading = false
								this.paypwdValue = ''
							} else {
								Toast.success({
									duration: 1000,
									forbidClick: true,
									loadingType: 'spinner',
									message: this.$t('lang.submit_success')
								})
								this.$router.push({
									name: 'done',
									query: {
										order_sn: res.data
									}
								})
							}
						})
					}
				}
			},
			notLogin(msg) {
				let url = window.location.href;
				Dialog.confirm({
					message: msg,
					className: 'text-center'
				}).then(() => {
					this.$router.push({
						name: 'login',
						query: {
							redirect: {
								name: 'cart',
								url: url
							}
						}
					})
				}).catch(() => {

				})
			},
			invoiceSelect() {
				if (this.checkoutInfo.cross_border) {
					return false
				}

				this.showBase = true
				this.$http.get(`${window.ROOT_URL}api/invoice`).then(res => {
					this.invoiceData = res.data.data;
					this.invoice.invoiceCompany = this.invoiceData.order_invoice_info;

					if (this.invoiceData.user_vat_invoice == '') {
						this.invoice.increment = false
					} else {
						this.invoice.increment = true
					}
				})
			},
			invTab(val) {
				if (val == 1) {
					this.invoice.company = false
					this.invoice.company_name = ''
					this.invoice.company_tid = ''
				} else {
					this.invoice.company = true
					this.invoice.vat_id = 0
					if (this.invoice.invoiceCompany.length > 0) {
						this.invoice.invoice_id = this.invoice.invoiceCompany[0].invoice_id
						this.invoice.company_name = this.invoice.invoiceCompany[0].inv_payee
						this.invoice.company_tid = this.invoice.invoiceCompany[0].tax_id
					}
				}
			},
			companySelect() {
				this.invoice.companyShow = true
			},
			companyClose() {
				this.invoice.companyShow = false
			},
			invoice_company_select(id, name, tax_id) {
				this.invoice.invoice_id = id
				this.invoice.company_name = name
				this.invoice.company_tid = tax_id
			},
			incrementHandle(val) {
				if (val == 0) {
					// 普通发票
					this.invoice.invoiceType = 0
					this.invoice.vat_id = 0;
				} else if (val == 1) {
					// 电子发票
                    this.invoice.invoiceType = 2
                    this.invoice.invoiceConent = 'Деталы'
				} else if (val == 2) {
					// 增值税发票
					if (this.invoice.increment == true) {
						this.invoice.invoiceType = 1
						this.invoice.vat_id = this.invoiceData.user_vat_invoice ? this.invoiceData.user_vat_invoice.id : 0
					} else {
						Dialog.confirm({
							message: this.$t('lang.apply_vat_tax_invoice'),
							className: 'text-center'
						}).then(() => {
							this.$router.push({
								name: 'invForm'
							})
						}).catch(() => {

						})
					}
				}
			},
			invValueRadioHandle() {
				this.invoiceValue.forEach((v, i) => {
					if (i == this.invoice.invValueRadio) {
						this.invoice.invoiceConent = v
					}
				})
			},
			submitTaxBtn() {
				if(this.invoice.invoiceType == 2){
					// 电子发票 - 单位
                    if (this.invoice.company) {
                        if(this.invoice.company_name == '') {
                            Toast(this.$t('lang.fill_in_company_name'))
                            return false
                        }else if (this.invoice.company_tid == '') {
                            Toast(this.$t('lang.fill_in_taxpayer_id_number'))
                            return false
                        }else if (this.invoice.inv_email == '') {
                            Toast('Эл.пошта енгізіңіз')
                            return false
                        }else if(this.invoice.inv_mobile == ''){
                            Toast('Тел.нөмір енгізіңіз')
                            return false
                        }else{
                            this.showBase = false
                        }
                    }else{
                        // 电子发票 -个人
                        if (this.invoice.inv_email == '') {
                            Toast('Эл.пошта енгізу')
                            return false
                        }else if(!this.checkMobile(this.invoice.inv_mobile)){
                            Toast('Тел.нөмір енгізіңіз')
                            return false
                        }else{
                            this.showBase = false
                        }
                    }
				}else{
					// 普通发票 - 单位
					if (this.invoice.company) {
						if (this.invoice.company_name == '') {
							Toast(this.$t('lang.fill_in_company_name'))
							return false
						} else if (this.invoice.company_tid == '') {
							Toast(this.$t('lang.fill_in_taxpayer_id_number'))
							return false
						} else {
							this.showBase = false
						}
					} else {
						// 普通发票 - 个人
						this.showBase = false
					}
				}

				this.getChangetax();
			},
			getChangetax(){
				this.$http.post(`${window.ROOT_URL}api/trade/changetax`, qs.stringify({
					total: this.total,
					inv_content: this.invoice.invoiceConent
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)
					this.amountPrice = data.amount
					this.ratePrice = data.rate_price
					
					this.valueCardObject.valueCardMoney = data.card_money
					if (data.card_money > 0) {
						this.valueCardObject.valueCardContent = data.card_money_formated
					} else {
						this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
					}
					
					if (data.coupons_money > 0) {
						this.couponsObject.couponsContent = data.coupons_money_formated
					} else {
						this.couponsObject.couponsContent = this.$t('lang.no_use_coupons')
					}
					
					if (data.bonus_money > 0) {
						this.bonusObject.bonusContent = data.bonus_money_formated
					} else {
						this.bonusObject.bonusContent = this.$t('lang.no_use_bonus')
					}
					
					this.total.integral_money = data.integral_money
					this.valueCardObject.vc_dis_money = data.vc_dis_money
					this.valueCardObject.vc_dis = data.vc_dis
					
					this.valueCardObject.valueCardId = data.value_card_id
					
					this.surplusSelf = false
				})
			},
			bonusHandle() {
				this.bonusObject.bonusBase = true
				this.surplusSelf = false
			},
			bonusActive(id) {
				if (this.bonusObject.bonusId != id) {
					if (!this.bonusDisabled) {
						this.bonusObject.bonusId = id
					}
				} else {
					this.bonusObject.bonusId = 0
				}
			},
			closeBonus() {
				this.bonusObject.bonusBase = false
			},
			submitBonus() {
				this.$http.post(`${window.ROOT_URL}api/trade/changebon`, qs.stringify({
					bonus_id: this.bonusObject.bonusId,
					total: this.total,
					shipping_id: this.shipping_id
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)
					if (data.bonus_money > 0) {
						this.bonusObject.bonusContent = data.bonus_money_formated
					} else {
						this.bonusObject.bonusContent = this.$t('lang.no_use_bonus')
					}

					if (data.card_money > 0) {
						this.valueCardObject.valueCardContent = data.card_money_formated
					} else {
						this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
					}

					this.total.bonus_money = data.bonus_money
					this.amountPrice = data.amount
					this.ratePrice = data.rate_price
					this.valueCardObject.valueCardMoney = data.card_money
					this.bonusObject.bonusMoney = data.bonus_money
					this.valueCardObject.vc_dis_money = data.vc_dis_money
					this.valueCardObject.vc_dis = data.vc_dis
					this.valueCardObject.valueCardId = data.value_card_id
					if (data.check_type !== undefined) {
						this.toggleDisabled("bonus", data.check_type)
					}
				})
				this.bonusObject.bonusBase = false
				this.surplus_status = false
			},
			couponsHandle() {
				this.couponsObject.couponsBase = true
				this.surplusSelf = false
			},
			couponsActive(id) {
				if (this.couponsObject.couponsId != id) {
					if (!this.couponsDisabled) {
						this.couponsObject.couponsId = id
					}
				} else {
					this.couponsObject.couponsId = 0
				}
			},
			closeCoupons() {
				this.couponsObject.couponsBase = false
			},
			submitCoupons(uc_id) {
				if(uc_id){
					this.couponsObject.couponsIdArr.push(uc_id);
					this.couponsObject.couponsId[0] = {
						uc_id:uc_id,
						money:''
					}
				}
				this.$http.post(`${window.ROOT_URL}api/trade/changecou`, qs.stringify({
					uc_id: uc_id ? uc_id : this.couponsObject.couponsIdArr,
					total: this.total,
					shipping_id: this.shipping_id
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)

					if (data.free_shipping_fee > 0) {
						this.couponsObject.couponsContent = this.$t('lang.coupon_tab_3');
					}
					
					if (data.coupons_money > 0) {
						this.couponsObject.couponsContent = data.coupons_money_formated
					} else {
						this.couponsObject.couponsContent = this.$t('lang.no_use_coupons')
					}

					if (data.card_money > 0) {
						this.valueCardObject.valueCardContent = data.card_money_formated
					} else {
						this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
					}

					this.total.coupons_money = data.coupons_money
					this.couponsObject.couponsMoney = data.coupons_money
					this.valueCardObject.valueCardMoney = data.card_money
					this.amountPrice = data.amount
					this.ratePrice = data.rate_price
					this.valueCardObject.vc_dis_money = data.vc_dis_money
					this.valueCardObject.vc_dis = data.vc_dis
					this.valueCardObject.valueCardId = data.value_card_id
					if (data.check_type !== undefined) {
						this.toggleDisabled("coupons", data.check_type)
					}
				})

				this.couponsObject.couponsBase = false
				this.surplus_status = false
			},
			toggleDisabled(type, state) {
				if (type == 'bonus') {
					if (state == 1) {
						this.couponsDisabled = true
					} else {
						this.couponsDisabled = false
					}
				} else if (type == 'coupons') {
					if (state == 1) {
						this.bonusDisabled = true
					} else {
						this.bonusDisabled = false
					}
				}
			},
			valueCardHandle() {
				this.valueCardObject.valueCardBase = true
				this.surplusSelf = false
				
				if (this.cartList.length == 0) this.valueCardLoad();
			},
			valueCardActive(id) {
				if (this.valueCardObject.valueCardId != id) {
					this.valueCardObject.valueCardId = id
				} else {
					this.valueCardObject.valueCardId = 0
				}
			},
			closeValuecard() {
				this.valueCardObject.valueCardBase = false
				this.valueCardObject.valueCardId = 0
			},
			submitValuecard() {
				this.$http.post(`${window.ROOT_URL}api/trade/changecard`, qs.stringify({
					vid: this.valueCardObject.valueCardId,
					total: this.total,
					shipping_id: this.shipping_id
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)

					if (data.card_money > 0) {
						this.valueCardObject.valueCardContent = data.card_money_formated
					} else {
						this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
					}

					this.valueCardObject.vc_dis = data.vc_dis

					this.amountPrice = data.amount
					this.ratePrice = data.rate_price
					this.valueCardObject.valueCardMoney = data.card_money
					this.total.card_money = data.card_money
					this.valueCardObject.vc_dis_money = data.vc_dis_money

				})

				this.valueCardObject.valueCardBase = false
				this.surplus_status = false
			},
			feeHandle(val, id) {
				let ru_id = 0
				let rec_ids = ''
				let flow_type = this.checkoutInfo.flow_type
				let shipping_region = {
					country: 1,
					province: this.checkoutInfo.consignee.province,
					city: this.checkoutInfo.consignee.city,
					district: this.checkoutInfo.consignee.district,
					street: this.checkoutInfo.consignee.street
				}

				this.feeShow = true
				this.shipping_active_id = val
				this.checkoutInfo.goods_list[val].goods.forEach((v) => {
					rec_ids += v.rec_id + ','
					ru_id = v.ru_id
				})

				rec_ids = rec_ids.substr(0, rec_ids.length - 1)

				this.$store.dispatch('setShipping', {
					rec_ids: rec_ids,
					ru_id: ru_id,
					consignee: shipping_region,
					flow_type: flow_type
				}).then((res) => {
					if (res.shipping) {
						this.shippingList = res.shipping
					}
				})
			},
			feeclose() {
				this.feeShow = false
			},
			shipping_select(item) {
				this.surplusSelf = false
				
				this.shipping_id.splice(this.shipping_active_id, 1, item.shipping_id)
				this.shipping_code.splice(this.shipping_active_id, 1, item.shipping_code)
				this.shipping_name.splice(this.shipping_active_id, 1, item.shipping_name)
				this.shipping_fee.splice(this.shipping_active_id, 1, item.shipping_fee)
				this.format_shipping_fee.splice(this.shipping_active_id, 1, item.format_shipping_fee)
				this.rate_price.splice(this.shipping_active_id, 1, item.rate_price)
				
				this.feeclose();
			},
			paymentSelect() {
				this.paymentShow = true

				setTimeout(()=>{
					this.tipscroll = true
				},500)
			},
			paymentClose() {
				this.paymentShow = false
			},
			storeclose() {
				this.shopShow = false
			},
			payment_method_select(id, name, code) {
				
				this.surplusSelf = false
				
				this.pay_id = id
				this.pay_name = name
				this.pay_code = code

				this.paymentClose();
			},
			store_method_select(id, name, user_id, index) {
				if(id != this.ru_id){
					let rec_id = ''
					this.goodsCartList.forEach((v, i) => {
						if (v.store_id == id) {
							v.new_list.forEach((act) => {
								act.act_goods_list.forEach(goods => {
									if (goods.store_count > 0) {
										rec_id += goods.rec_id + ','
									}
								})
							})
						}
					})

					this.rec_id = rec_id.substr(0, rec_id.length - 1)

					this.$router.push({
						name: 'storeGoods',
						query: {
							rec_id: this.rec_id,
							ru_id: id
						}
					})
				}
			},
			integralSelfHandle() {
				this.$http.post(`${window.ROOT_URL}api/trade/changeint`, qs.stringify({
					total: this.total,
					integral_type: this.use_integral_val,
					cart_value: this.cart_id,
					flow_type: this.checkoutInfo.flow_type,
					shipping_id: this.shipping_id
				})).then(({
					data: {
						data
					}
				}) => {
					if (data) {
						this.$store.dispatch('setCheckoutTotal', data)
						this.amountPrice = data.amount
						this.ratePrice = data.rate_price
						this.total.integral = data.integral

						this.valueCardObject.valueCardMoney = data.card_money
						if (data.card_money > 0) {
							this.valueCardObject.valueCardContent = data.card_money_formated
						} else {
							this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
						}

						this.total.integral_money = data.integral_money
						this.valueCardObject.vc_dis_money = data.vc_dis_money
						this.valueCardObject.vc_dis = data.vc_dis

						this.valueCardObject.valueCardId = data.value_card_id
						this.surplusSelf = false
					}
				})
			},
			surplusSelfHandle() {
				if (this.surplus < 0) {
					Toast('Ережеге сай мән жазыңыз');
					return false
				}

				this.$http.post(`${window.ROOT_URL}api/trade/changesurplus`, qs.stringify({
					total: this.total,
					surplus: this.surplus,
					shipping_id: this.shipping_id
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)
					this.amountPrice = data.amount
					this.ratePrice = data.rate_price
					this.total.surplus = data.surplus
					this.total.surplus_formated = data.surplus_formated
					this.surplus = data.surplus
					this.surplus_status = true

					this.valueCardObject.valueCardMoney = data.card_money
					this.valueCardObject.vc_dis = data.vc_dis
					this.valueCardObject.valueCardId = data.value_card_id
					if (data.card_money > 0) {
						this.valueCardObject.valueCardContent = data.card_money_formated
					}else{
						this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
					}
				})
			},
			onListShow(i) {
				this.inventory=true
				let newList=this.checkoutInfo.goods_list.find((item,index)=>{
					return item.ru_id==i
				})
				this.inventoryList=newList
			},
			closeInventory(){
				this.inventory=false
			},
			onListPackage() {
				this.listPackageShow = this.listPackageShow ? false : true
			},
			onInput(key) {
				this.paypwdValue = (this.paypwdValue + key).slice(0, 6);
				if (this.paypwdValue.length == 6) {
					this.paypwdShow = false
					this.onSubmit();
				}
			},
			onDelete() {
				this.paypwdValue = this.paypwdValue.slice(0, this.paypwdValue.length - 1);
			},
			onHide() {
				this.paypwdValue = ''
			},
			clickArticle(index) {
				if (index != undefined) {
					this.showArticle = this.checkoutInfo.article_list[index];
					this.articleShow = true;
				}
			},
			articleClose() {
				this.articleShow = false;
			},
			productLink(item) {
				if (this.rec_type == 0 && item.extension_code != 'package_buy') { //普通商品
					this.$router.push({
						name: 'goods',
						params: {
							id: item.goods_id
						}
					})
				} else if (this.rec_type == 1) { //团购商品
					this.$router.push({
						name: 'groupbuy-detail',
						params: {
							group_buy_id: this.type_id
						}
					})
				} else if (this.rec_type == 2) { //拍卖商品
					this.$router.push({
						name: 'auction-detail',
						params: {
							act_id: this.type_id
						}
					})
				} else if (this.rec_type == 4) { //积分兑换商品
					this.$router.push({
						name: 'exchange-detail',
						params: {
							id: item.goods_id
						}
					})
				} else if (this.rec_type == 5) { //预售商品
					this.$router.push({
						name: 'presale-detail',
						params: {
							act_id: this.type_id
						}
					})
				} else if (this.rec_type == 6) { //秒杀商品
					this.$router.push({
						name: 'seckill-detail',
						query: {
							seckill_id: this.type_id,
							tomorrow: 0
						}
					})
				} else if (this.rec_type == 7) { //拼团商品
					this.$router.push({
						name: 'team-detail',
						query: {
							goods_id: item.goods_id,
							team_id: this.team_id
						}
					})
				} else if (this.rec_type == 8) { //砍价商品
					this.$router.push({
						name: 'bargain-detail',
						params: {
							id: this.type_id
						}
					})
				} else if (item.extension_code == 'package_buy') { //超值礼包
					this.$router.push({
						name: 'package'
					})
				}
			},
			//分销会员卡
			drpApplyHref(id) {
				this.$router.push({
					name: 'drp-apply',
					query: {
						card_id: id
					}
				})
			},
			//vip优惠领取
			vipReceive() {
				this.vipReceiveState = !this.vipReceiveState;
				this.$http.post(`${window.ROOT_URL}api/trade/change_membership_card`, qs.stringify({
					total: this.total,
					order_membership_card_id: this.vipReceiveState ? this.vipCard.order_membership_card_id : 0,
					membership_card_discount_price: this.vipCard.membership_card_discount_price
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)
					this.amountPrice = data.amount
					this.ratePrice = data.rate_price

					this.total.membership_card_buy_money = data.membership_card_buy_money
					this.total.membership_card_buy_money_formated = data.membership_card_buy_money_formated
					this.total.membership_card_discount_price = data.membership_card_discount_price
					this.total.membership_card_discount_price_formated = data.membership_card_discount_price_formated

					this.vipCard.membership_card_buy_money = data.membership_card_buy_money
					this.vipCard.membership_card_buy_money_formated = data.membership_card_buy_money_formated
					this.vipCard.membership_card_discount_price = data.membership_card_discount_price
					this.vipCard.membership_card_discount_price_formated = data.membership_card_discount_price_formated

					this.surplusSelf = false
				})
			},
			quanyiClick() {
				this.quanyiShow = true
			},
			handelClose() {
				this.quanyiShow = false
			},
			//优惠券选择
			couponValClick(id,money,index){
				this.couponsObject.couponsIdArr = [];
				this.couponsObject.couponsMoney = 0;
				this.couponsObject.couponsId[index].uc_id = this.couponsObject.couponsId[index].uc_id != id ? id : '';
				this.couponsObject.couponsId[index].cou_money = money;
				this.couponsObject.couponsId.forEach(v=>{
					if(v.uc_id){
						this.couponsObject.couponsIdArr.push(v.uc_id);
						this.couponsObject.couponsMoney += Number(v.cou_money);
					}
				})
			},
			checkMobile(mobile) {
                let rule = /^(1[3-9])\d{9}$/
                if (rule.test(mobile)) {
                    return true
                } else {
                    return false
                }
            }
		},
		watch: {
			goodsCartList: function(val, oldVal) {
				this.rec_id = ''
				this.actVal = []
				this.goodsCartList.forEach((v, i) => {

					v.new_list.forEach((act) => {
						act.act_goods_list.forEach(goods => {
							if (goods.store_count > 0 && goods.is_checked > 0) {
								this.actVal.push(v)
							}
						})
					})
				})

				var arr = [];
				this.actVal.forEach(function(index) { //遍历数组
					if (arr.indexOf(index) == -1) {
						arr.push(index); //是否在新数组中查找到元素，没有则返回-1，push此元素
					}
				});

				this.actVal = arr

				if (this.actVal.length > 0) {
					const arr = this.actVal[0]
					if (this.ru_id && this.ru_id >= 0) {
						this.goodsCartList.forEach((v, i) => {
							v.new_list.forEach((act) => {
								act.act_goods_list.forEach(goods => {
									if (goods.store_count > 0 && goods.ru_id == this.ru_id && goods.is_checked > 0) {
										this.storepick = goods.store_name
										this.store_ids = goods.store_id
										this.rec_id += goods.rec_id + ','
									}
								})
							})
						})
					} else if (this.goods_id) {
						this.goodsCartList.forEach((v, i) => {
							v.new_list.forEach((act) => {
								act.act_goods_list.forEach(goods => {
									if (goods.store_count > 0 && goods.goods_id == this.goods_id) {
										this.storepick = goods.store_name
										this.store_ids = goods.store_id
										this.goods_id = goods.goods_id
									}
								})
							})
						})
					} else {
						this.storepick = this.actVal[0].store_name
						this.store_ids = this.actVal[0].store_id
						arr.new_list.forEach((item) => {

							item.act_goods_list.forEach(goods => {

								if (goods.store_count > 0) {
									this.rec_id += goods.rec_id + ','

								}
							})
						})
					}
				}
				this.rec_id = this.rec_id.substr(0, this.rec_id.length - 1)
			},
			checkoutInfo() {
				if (this.checkoutInfo.length == 0) {
					this.$router.replace({
						name: 'cart'
					})

					return false
				}

				this.checkoutDisplay = this.checkoutInfo.error ? false : true

				if (this.checkoutInfo.error) {
					switch (this.checkoutInfo.error) {
						case 'address':
							if (this.$route.query) {
								this.$router.push({
									name: 'addAddressForm',
									query: {
										routerLink: 'checkout',
										entrance: 'first',
										rec_type: this.rec_type,
										type_id: this.type_id,
										team_id: this.team_id,
										back: this.back
									}
								})
							} else {
								this.$router.push({
									name: 'addAddressForm',
									query: {
										routerLink: 'checkout',
										entrance: 'first',
										back: this.back
									}
								})
							}
							break
						case 'excess':
							Dialog.alert({
								message: this.checkoutInfo.msg,
								className: 'text-center',
								confirmButtonText: this.$t('lang.back_cart')
							}).then(() => {
								this.$router.push({
									name: 'cart',
								})
							})
							break
					}

					return false
				}

				//默认选中在线支付
				if (this.pay_name == '') {
					this.payment_method.forEach(v => {
						if (v.pay_code == 'onlinepay') {
							this.pay_name = v.pay_name
							this.pay_id = v.pay_id
							this.pay_code = v.pay_code
						}
					})
				}

				//是否是门店商品
				if (this.storeCart) {
					this.store_type = ''
					this.store_mobile = this.storeCart.store_mobile
					this.take_time = this.storeCart.take_time
				}

				//跨境身份证信息
				if (this.checkoutInfo.consignee) {
					this.checkout_real_id = this.checkoutInfo.consignee.id_num ? this.checkoutInfo.consignee.id_num : ''
					this.checkout_real_name = this.checkoutInfo.consignee.rel_name ? this.checkoutInfo.consignee.rel_name : ''
				}

				//发票内容
				if (this.checkoutInfo.can_invoice == 1) {
					this.invoice.invoiceConent = this.invoiceValue[0]
				}

				//领券购买
				if(this.uc_id > 0){
					this.submitCoupons(this.uc_id)
				}
			},
			payment_method() {
				if (this.payment_method == '' && !this.checkoutInfo.error) {
					Toast(this.$t('lang.payment_method_not_installed'))
					return false
				}
			},
			surplusSelf() {
				Toast.loading({
					message: 'Жүктеуде...',
					forbidClick: true,
					loadingType: 'spinner',
					duration: 0,
					mask: true,
					forbidClick: true
				});

				this.$http.post(`${window.ROOT_URL}api/trade/changesurplus`, qs.stringify({
					total: this.total,
					surplus: this.surplusSelf ? this.checkoutInfo.user_money : 0,
					shipping_id: this.shipping_id
				})).then(({
					data: {
						data
					}
				}) => {
					this.$store.dispatch('setCheckoutTotal', data)
					this.amountPrice = data.amount
					this.ratePrice = data.rate_price
					this.total.surplus = data.surplus
					this.total.surplus_formated = data.surplus_formated
					this.surplus = data.surplus
					this.surplus_status = true
					this.valueCardObject.vc_dis = data.vc_dis
					this.valueCardObject.valueCardMoney = data.card_money
					this.valueCardObject.valueCardId = data.value_card_id
					if (data.card_money > 0) {
						this.valueCardObject.valueCardContent = data.card_money_formated
					}else {
						this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
					}

					Toast.clear();
				})
			},
			shipping() {
				//获取默认配送放id,code,type
				this.shipping.forEach((v) => {
					if (v == undefined && this.store_id == 0) {
						this.shipping_name.push('')
						Toast(this.$t('lang.noshipping_list_propmt'))
						return false
					}

					this.shipping_id.push(v.shipping_id)
					this.shipping_code.push(v.shipping_code)
					this.shipping_name.push(v.shipping_name)
					this.format_shipping_fee.push(v.format_shipping_fee)
					this.shipping_fee.push(v.shipping_fee)
					this.shipping_type.push(0)
					this.rate_price.push(v.rate_price)
				})
			},
			shopCouponList(){
				this.shopCouponList.forEach(v=>{
					this.couponsObject.couponsId.push({ uc_id:'', cou_money: ''});
				})
			}
		},
		beforeRouteEnter(to, form, next) {
			next(vm => {
				vm.back = form.fullPath
			})
		}
	}
</script>
<style lang="scss" scoped>
	.flow-checkout{
		margin-top: 1rem;
	}
	
	.product-list .package-box {
		display: flex;
		align-items: center;
	}

	.product-list .package-box .product-info {
		margin: 0 0 0 1rem;
		flex: 1;
	}

	.product-list .package-box .t-jantou-xia {
		padding: 0 1rem 0 1.5rem;
	}

	.product-list .package-box .t-jantou-xia .user-more {
		padding: 0;
	}

	.product-list .package-box .t-jantou-xia .user-more .iconfont {
		position: static;
	}

	.cell-value-flex {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
	}

	.user_money {
		display: flex;
		flex-direction: row;
		justify-content: flex-end;
		align-items: center;
		font-size: 1.2rem;
	}

	.vip-zk {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: flex-start;
		line-height: 1.2;
	}

	.vip-zk .icon-vip {
		width: 2rem;
		height: 2rem;
	}

	.vip-zk .vip-zk-info {
		margin-left: .5rem;
	}

	.vip-zk .vip-zk-info .tit {
		font-size: 1.4rem;
		color: #333;
		display: flex;
	}

	.vip-zk .vip-zk-info .tit .label {
		margin: 0 .5rem;
	}

	.vip-zk .vip-zk-info .tit i {
		margin-left: .5rem;
	}

	.vip-zk .vip-zk-info .subtit {
		font-size: 1.2rem;
		color: #999;
	}

	.vip-zk .vip-zk-info .price {
		font-size: 1.2rem;
	}

	.vip-zk-price {
		height: 100%;
		display: flex;
		flex-direction: row;
		justify-content: flex-end;
		align-items: center;
	}

	.vip-zk-price em {
		font-size: 1.6rem;
		margin-right: .5rem;
	}

	.vip-zk-price .mf {
		font-size: 1.2rem;
	}

	.vip-zk-price .iconfont {
		color: #999;
	}

	.vip-zk-price.active .iconfont {
		color: #f92028;
	}

	.vip-zk-price .price{ margin-right: .5rem; }

	.quanyiShow {
		width: 80%;
		border-radius: 1rem;
		padding: 1rem;
	}

	.quanyiShow .quanyi-top {
		text-align: center;
		margin: 1rem 0 0;
		position: relative;
	}

	.quanyiShow .quanyi-top .icon-vip {
		width: 4rem;
		height: 4rem;
		display: inline-block;
	}

	.quanyiShow .quanyi-top .name {
		font-size: 1.6rem;
		font-weight: bold;
		margin-top: 1rem;
	}

	.quanyiShow .quanyi-top .icon-close {
		position: absolute;
		top: 0;
		right: 1rem;
	}

	.quanyiShow .quanyi-xian {
		text-align: center;
		position: relative;
		margin: 1.5rem 0;
	}

	.quanyiShow .quanyi-xian span {
		display: inline-block;
		padding: 0 1rem;
		background: #fff;
		overflow: hidden;
		font-size: 1.2rem;
		color: #999;
		line-height: 2rem;
		height: 2rem;
		position: relative;
		z-index: 3;
	}

	.quanyiShow .quanyi-xian:after {
		content: " ";
		width: 100%;
		height: 1px;
		background: #f0f0f0;
		position: absolute;
		top: 1rem;
		left: 0;
		right: 0;
		z-index: 2
	}

	.quanyiShow .quanyi-list {
		overflow: hidden;
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
	}

	.quanyiShow .quanyi-list .item {
		width: 33.3%;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin: 1rem 0;
	}

	.quanyiShow .quanyi-list .item .icon {
		width: 5rem;
		height: 5rem;
		border-radius: 50%;
		overflow: hidden;
	}

	.quanyiShow .quanyi-list .item .txt {
		font-size: 1.2rem;
		color: #666;
		margin-top: 1rem;
	}

	.quanymagin {
		margin-right: 0.8rem;
		margin-left: 0.8rem;
		/* border-radius: 1rem; */
	}

	.border-top {
		border-top-left-radius: 1rem;
		border-top-right-radius: 1rem;
	}

	.border-bottom {
		border-bottom-left-radius: 1rem;
		border-bottom-right-radius: 1rem;
	}

	.title_on {
		display: flex;
		justify-content: space-between;
		background: #ffffff;
		width: 18rem;
		margin: auto;
		border-radius: 1.5rem;
		margin-top: 1rem;
	}

	.title_on .item {
		width: 9rem;
		height: 2.6rem;
		text-align: center;
		line-height: 2.6rem;
		background: #FFFFFF;
		color: #000;
		font-size: 1.4rem;
		border-radius: 1.3rem;
	}

	.title_on .active_on {
		background: red !important;
		color: #FFFFFF;
	}

	.tit_jin {
		font-size: 1.2rem;
		color: #FFCC00;
	}

	.add_title {
		display: flex;
		padding: 1.1rem;
		font-size: 1.6rem;
	}

	.add_title img {
		width: 2.5rem;
		height: 2.5rem;
		margin-right: 0.5rem;
	}

	.margin_10 {
		margin-right: 10px !important;
	}

	.margin_10 span {
		color: red !important;
	}

	.add_title i {
		line-height: 2.5rem;
		margin-left: 2rem;
	}

	.dis-box .dis_img {
		width: 3rem;
		height: 3rem;
		border-radius: 50%;
	}

	.dis-box .dis_shop {
		line-height: 3rem;
		padding-left: 0.5rem;
	}
	
	/* 商品清单 */
	.product-list-img img {
		width: 9rem;
		height: 9.2rem;
	}
	
	/* 商品清单吸顶条 */
	.invent{
		height: 5rem;
		border: 1px solid red;
	}
	
	.goods_thumb{
		padding: 1rem 2rem 0;
		overflow-y: auto;
		height: 100%;
	}
	
	.goods_thumb_li{
		border-bottom: 1px solid #f4f4f4;
		padding-bottom: 2rem;
		margin-bottom: 2rem;
	}

	.goods_thumb_li:last-child{
		margin-bottom: 0;
	}

	.goods_thumb_last .goods_thumb_li:last-child{
		padding-bottom: 0;
		border: 0;
	}
	
	.goods_thumb_content{
		display: flex;
	}
	
	.goods_thumb_left .img{
		border-radius: 1rem;
		width: 9rem;
		height: 9rem;
	}
	
	.goods_thumb_right{
		flex: 1;
		margin-left: 1rem;
	}
	
	.goods_thumb_price{
		display: flex; 
		justify-content: space-between; 
		padding: 1rem 0 .5rem 0;
	}
	
	.goods_thumb_price_red{
		color: #FF0000;
		font-weight: 700;
	}
	
	.goods_thumb_right_one{
		color: #333333;
	}
	
	.goods_thumb_right_ci{
		color: #999999;
		font-size: 1.2rem;
	}
	
	.goods_thumb_right_ci span{
		margin-right: 2rem;
	}
	
	.title_count{
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	.title_count em{
		margin-right: 1.5rem;
		color: #707070;
	}
	
	.goods_thumb_footer{
		 padding: 1.5rem 0; 
		 color: #999999;  
		 text-align: center;
	}
	.show-popup-valuecard {
		.value-card-list {
			height: calc(100% - 11rem);
			padding: 0 2rem;
			.no_data {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				height: 100%;
				p {
					color: #BABABA;
					&:first-child {
						font-size: 1.6rem;
						color: #666;
						margin-bottom: 1rem;
					}
				}
			}
			.loadmore {
				height: 3rem;
				text-align: center;
			}
		}
	}
	.cart_tabs {
		position: relative;
		display: flex;
		justify-content: space-around;
		align-items: center;
		padding: 1.5rem 0;
		&:before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 1px;
			box-shadow: 0px 1px 3px #eee;
		}
		.active_tab {
			position: relative;
			color: #E83C2D;
			&:after {
				content: '';
				position: absolute;
				bottom: -0.5rem;
				left: 0;
				width: 100%;
				height: 0.2rem;
				background-color: #E83C2D;
			}
		}
	}
	
	.em-promotion{
		margin-right: 0.3rem;
		position: relative;
		top: -0.15rem;
	}
	
	.country_icon{
		width: 2.4rem;
		padding-right: 0.3rem;
		position: relative;
		top: 0.2rem;
		display: inline-block;
	}
</style>
