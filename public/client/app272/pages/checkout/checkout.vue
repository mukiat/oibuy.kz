<template>
	<view class="container fixed-bottom-padding" v-if="checkoutDisplay">
		<view class="title_on" v-if="shopStore">
			<view class="active_on">{{$t('lang.express_delivery')}}</view>
			<view @click="shop_kud">{{$t('lang.private_store')}}</view>
		</view>
		<view class="flow-checkout-adr bottom_b" @click="checkoutAddress" v-if="total.real_goods_count > 0">
			<view class="adr-content">
				<view class="title" v-if="checkoutInfo.consignee">
					<text class="name">{{ checkoutInfo.consignee.consignee }}</text>
					<text class="mobile">{{ checkoutInfo.consignee.mobile }}</text>
				</view>
				
				<view class="address"><text class="post_lebal_red" v-if="checkoutInfo.consignee.post_mobile">{{$t('lang.community_post')}}</text>{{ consignee_address }}</view>
				<view v-if="checkoutInfo.consignee.post_mobile">{{$t('lang.post_contact_number')}}:{{checkoutInfo.consignee.post_mobile}}</view>
				<view class="address_footer" v-if="checkoutInfo.consignee.nearbyleader > 0">{{checkoutInfo.consignee.leader_id ? $t('lang.selected_community') : $t('lang.near_station')}}</view>
			</view>
			<uni-icons type="forward" size="18" color="#999999"></uni-icons>
		</view>
		<view class="checkout-goods-list" v-for="(item,index) in checkoutInfo.goods_list" :key="index">
			<view class="section-list quanymagin" :style="{'margin-top': total.real_goods_count == 0 ? '20rpx' : '0'}">
				<view class="title">{{ item.shop_name }}</view>
				<view class="uni-list-cell" v-if="item.cross_warehouse_name != ''" style="margin-left: -26rpx;">
					<view class="uni-list-cell-navigate justify-content-fs">
						<text class="title" style="font-size: 24rpx; border: none; padding-top: 10upx; padding-bottom: 10upx; line-height: 30upx;">{{$t('lang.place_of_shipment')}}</text>
						<view class="value" style="font-size: 24rpx; color:#777; margin-left: -20rpx;">
							{{item.cross_warehouse_name}}
						</view>
					</view>
				</view>
				<view class="product-list product-list-max" v-if="listShow[index]">
					<view class="product-items">
						<view class="item" v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex" v-if="goodsIndex < 3" @click="productLink(goodsItem)">
							<view class="product-img">
								<image :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
								<image src="../../static/gift-icon.png" class="icon" v-if="goodsItem.is_gift > 0"></image>
							</view>
						</view>
					</view>
					<view class="product-more" @click="onListShow(item.goods[index].ru_id)">
						<text>{{$t('lang.gong')}} {{ item.goods_count }} {{$t('lang.jian')}}</text>
						<uni-icons type="forward" size="18" color="#999999"></uni-icons>
					</view>
				</view>
				<view class="product-list" v-else>
					<view class="product-items">
						<view v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex" @click="productLink(goodsItem)">
							<view class="item">
								<view class="product-img">
									<image :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
									<image src="../../static/gift-icon.png" class="icon" v-if="goodsItem.is_gift > 0"></image>
								</view>
								<view class="product-info">
									<view class="product-name twolist-hidden">
										<uni-tag :text="$t('lang.group_buy')" size="small" type="error" v-if="rec_type == 1"></uni-tag>
										<uni-tag :text="$t('lang.auction')" size="small" type="error" v-if="rec_type == 2"></uni-tag>
										<uni-tag :text="$t('lang.integral')" size="small" type="error" v-if="rec_type == 4"></uni-tag>
										<uni-tag :text="$t('lang.presale')" size="small" type="error" v-if="rec_type == 5"></uni-tag>
										<uni-tag :text="$t('lang.seckill')" size="small" type="error" v-if="rec_type == 6"></uni-tag>
										<uni-tag :text="$t('lang.team')" size="small" type="error" v-if="rec_type == 7"></uni-tag>
										<uni-tag :text="$t('lang.bargain')" size="small" type="error" v-if="rec_type == 8"></uni-tag>
										<uni-tag :text="$t('lang.package')" size="small" type="error" v-if="rec_type == 11"></uni-tag>
										<text>{{ goodsItem.goods_name }}</text>
									</view>
									<view class="product-row">
										<view class="price">{{ goodsItem.goods_price_format }}</view>
										<view class="number">x{{ goodsItem.goods_number }}</view>
									</view>
									<view class="product-attr">{{ goodsItem.goods_attr }}</view>
								</view>
							</view>
							<view class="lie-list" v-if="goodsItem.membership_card_id > 0">
								<view class="left">
									<uni-tag :text="$t('lang.give')" size="small" type="error"></uni-tag>{{$t('lang.song')}}[{{goodsItem.membership_card_name}}]
									{{$t('lang.onezhang')}}
								</view>
								<view class="right" @click="drpApplyHref(goodsItem.membership_card_id)">{{$t('lang.go_to_kk')}}</view>
							</view>
						</view>
					</view>
					<view class="product-more" v-if="item.goods.length > 1">
						<uni-icons type="arrowdown" size="18" color="#999999" @click="onListShow(index)"></uni-icons>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="uni-list">
					<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-if="store_id == 0 && total.real_goods_count > 0">
						<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge" v-if="shipping_fee && shipping_fee[index] > 0">
							<text class="title">{{$t('lang.shipping_mode')}}</text>
							<view class="value">
								<text v-if="item.shipping.default_shipping.shipping_id > 0" @click="feeHandle(index,item.shipping.default_shipping.shipping_id)">
									<text class="mr10">{{ shipping_name[index] }}</text><text class="uni-red">{{ format_shipping_fee[index] }}</text>
								</text>
								<text class="uni-red" v-else>{{$t('lang.not_shipping')}}</text>
							</view>
						</view>
						<view class="uni-list-cell-navigate" v-else>
							<text class="title">{{$t('lang.shipping_mode')}}</text>
							<view class="value">
								<text v-if="item.shipping.default_shipping.shipping_id > 0">
									<text class="mr10">{{ shipping_name[index] }}</text>
									<text class="uni-red">{{$t('lang.pinkage')}}</text>
								</text>
								<text class="uni-red" v-else>{{$t('lang.not_shipping')}}</text>
							</view>
						</view>
					</view>
					<view class="uni-list-cell">
						<view class="uni-list-cell-navigate justify-content-fs">
							<text class="title">{{$t('lang.buyer_message')}}</text>
							<view class="value">
								<input :placeholder="$t('lang.buyer_message_placeholder')" maxlength="50" v-model="postscriptValue[index]" />
							</view>
						</view>
					</view>
					<view class="uni-list-cell">
						<view class="uni-list-cell-navigate justify-content-fe">
							<view class="value">{{$t('lang.gong')}} {{ item.goods_count }} {{$t('lang.total_amount_propmt_alt')}}：<text
								 class="uni-red">{{ item.amount }}</text></view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="paymentSelect">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.payment_mode')}}</text>
						<view class="value">{{ pay_name }}</view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="invoiceSelect" v-if="rec_type != 4 && checkoutInfo.can_invoice > 0">
					<view class="uni-list-cell-navigate" :class="{'uni-navigate-right uni-navigate-badge': !checkoutInfo.cross_border}">
						<text class="title">{{$t('lang.invoice_info')}}</text>
						<view class="value uni-flex-tongyong">
							<view class="receipt-title" v-if="checkoutInfo.cross_border">
								<text class="uni-red">{{$t('lang.cross_border_no_invoice')}}</text>
							</view>
							<block v-else>
								<view class="receipt-title" v-if="invoice.invoiceType != 1">
									<block v-if="invoice.invoiceType == 0">普通发票</block>
									<block v-else-if="invoice.invoiceType == 2" >电子发票</block>
									<text class="txt">-</text>
									<text v-if="!invoice.company">{{ invoice.invoiceTitle }}</text>
									<text v-else>{{ invoice.company_name }}</text>
								</view>
								<view class="receipt-title" v-else>{{$t('lang.vat_tax_invoice')}}</view>
								<view class="receipt-name" v-if="invoice.invoiceType != 1">{{ invoice.invoiceConent }}</view>
							</block>
						</view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-if="bonusList.length>0" @click="popupHandle('bonus')">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.bonus')}}({{bonusList.length}}{{$t('lang.zhang')}})</text>
						<view class="value">{{ bonusObject.bonusContent }}</view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-if="couponsList.length>0" @click="popupHandle('coupon')">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.coupons')}}({{couponsList.length}}{{$t('lang.zhang')}})</text>
						<view class="value">{{ couponsObject.couponsContent }}</view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-if="valueCard.length>0" @click="popupHandle('valueCard')">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.value_card')}}({{valueCard.length}}{{$t('lang.zhang')}})</text>
						<view class="value">{{ valueCardObject.valueCardContent }}</view>
					</view>
				</view>
			</view>
			<view class="uni-list" v-if="checkoutInfo.cross_border">
				<view class="uni-list-cell uni-list-cell-title">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.real_name')}}</text>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate justify-content-fs">
						<text class="title">{{$t('lang.truename')}}</text>
						<view class="value uni-cell-input">
							<input :placeholder="$t('lang.fill_in_real_name')" v-model="checkout_real_name" />
						</view>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate justify-content-fs">
						<text class="title">{{$t('lang.id_number')}}</text>
						<view class="value uni-cell-input">
							<input :placeholder="$t('lang.fill_in_id_number')" v-model="checkout_real_id" />
						</view>
					</view>
				</view>
				<view class="uni-list-cell checkout_article_list" v-for="(item,index) in checkoutInfo.article_list" :key="index">
					<view class="checkbox" :class="{'checked':articleCheck}">
						<view class="checkbox-icon" @click="onArticleCheck">
							<uni-icons type="checkmarkempty" size="16" color="#ffffff"></uni-icons>
						</view>
						<view class="checkbox-con">
							<text class="txt" @click="onArticleCheck">{{$t('lang.checkout_help_article')}}</text>
							<text class="href" v-if="item.title" @click="clickArticle(index)">《{{item.title}}》</text>
							<text class="href" v-else>{{$t('lang.article_not')}}</text>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-list" v-if="checkoutInfo.use_membership_card > 0 && vipCard.membership_card_discount_price > 0">
				<view class="uni-list-cell uni-list-cell-title">
					<view class="uni-list-cell-navigate uni-list-cell-vipzk">
						<view class="title">
							<view class="vip-zk">
								<view class="vip-img">
									<image src="../../static/vip/icon-vip.png"></image>
								</view>
								<view class="vip-zk-info">
									<view class="tit">
										<text>{{$t('lang.open_membership')}}</text>
										<text class="txt-price">{{ vipCard.membership_card_discount_price_formated }}</text>
										<text>{{$t('lang.yuan')}}</text>
										<text class="iconfont icon-asks" @click="quanyiClick"></text>
									</view>
									<view class="subtit">{{$t('lang.open_member')}}</view>
								</view>
							</view>
						</view>
						<view class="value">
							<view class="vip-zk-price flex_box ai_center" :class="{'active':vipReceiveState}" @click="vipReceive">
								<text class="price" :class="{'mf':!vipCard.membership_card_buy_money > 0}">{{ vipCard.membership_card_buy_money > 0 ? vipCard.membership_card_buy_money_formated : $t('lang.drp_apply_title_5') }}</text>
								<icon class="iconfont icon-gouxuan"></icon>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-list" v-if="(checkoutInfo.allow_use_integral > 0 && checkoutInfo.integral.length > 0) || (checkoutInfo.use_surplus > 0 && pay_code == 'onlinepay') || (checkoutInfo.use_surplus > 0 && use_surplus_val > 0 && pay_code == 'onlinepay')">
				<view class="uni-list-cell uni-list-cell-title" v-if="checkoutInfo.allow_use_integral > 0 && checkoutInfo.integral.length > 0">
					<view class="uni-list-cell-navigate uni-list-cell-switch">
						<view class="title">{{$t('lang.in_commission')}}<text class="uni-red">{{ checkoutInfo.integral[0].integral }}</text>{{$t('lang.points_deduction')}}<text
							 class="uni-red">{{ checkoutInfo.integral[0].integral_money_formated}}</text></view>
						<view class="value">
							<switch :checked="integralSelf" @change="integralSelfHandle" />
						</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title" v-if="checkoutInfo.use_surplus > 0 && pay_code == 'onlinepay'">
					<view class="uni-list-cell-navigate uni-list-cell-switch">
						<text class="title">{{$t('lang.is_use_balance')}}</text>
						<view class="value">
							<switch :checked="surplusSelf" @change="surplusSelfHandle" />
						</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="checkoutInfo.use_surplus > 0 && use_surplus_val > 0 && pay_code == 'onlinepay'">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.label_use_balance')}}</text>
						<view class="value uni-cell-input cell-value-flex">
							<input :placeholder="$t('lang.fill_in_use_balance')" type="digit" v-model="surplus" @blur="surplusvValHandle"
							 class="input-bor" />
							<text class="user_money">{{$t('lang.label_sy_use_balance')}}{{ checkoutInfo.user_money_formated }}</text>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-list">
				<view class="uni-list-cell uni-list-cell-title">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.goods_together')}}</text>
						<view class="value uni-red">{{ total.goods_price_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="rateFee > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.import_tax')}}</text>
						<view class="value uni-red">+ {{ currency }}{{ rateFee }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="shippingFee > 0 && store_id == 0 && total.real_goods_count > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.delivery_cost')}}</text>
						<view class="value uni-red">+ {{ currency }}{{ shippingFee }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="total.discount > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.discount')}}</text>
						<view class="value uni-red">-{{ total.discount_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="total.integral_money > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.integral')}}</text>
						<view class="value uni-red">-{{ total.integral_money_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="bonusObject.bonusMoney > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.bonus')}}</text>
						<view class="value uni-red">-{{ total.bonus_money_formated }}</view>
					</view>
				</view>
				<block>
					<view class="uni-list-cell" v-if="couponsObject.couponsMoney > 0">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.coupons')}}</text>
							<view class="value uni-red">-{{ total.coupons_money_formated }}</view>
						</view>
					</view>
				</block>
				<view class="uni-list-cell" v-if="total.free_shipping_fee > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.coupon_tab_3')}}</text>
						<view class="value uni-red">-{{ total.free_shipping_fee_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="valueCardObject.vc_dis < 1">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.value_card_discount')}}</text>
						<view class="value uni-red">-{{ currency }}{{ valueCardObject.vc_dis_money }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="valueCardObject.valueCardMoney > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.value_card')}}</text>
						<view class="value uni-red">-{{ total.card_money_formated }}</view>
					</view>
				</view>
			
				<view class="uni-list-cell" v-if="vipCard.order_membership_card_id > 0 && vipReceiveState  && vipCard.membership_card_buy_money > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.membership_card_buy_money')}}</text>
						<view class="value uni-red">+{{ vipCard.membership_card_buy_money_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="vipCard.order_membership_card_id > 0 && vipReceiveState && vipCard.membership_card_discount_price > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.membership_card_discount_price')}}</text>
						<view class="value uni-red">-{{ vipCard.membership_card_discount_price_formated }}</view>
					</view>
				</view>
			
				<view class="uni-list-cell" v-if="total.surplus > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.money')}}</text>
						<view class="value uni-red">-{{ total.surplus_formated }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="btn-goods-action">
			<view class="submit-bar-text">
				<text>{{ label_text }}</text>
				<view class="submit-bar-price" v-if="rec_type == 4">{{ amountPrice }}{{$t('lang.integral')}}
					<block v-if="shippingFee > 0"> + {{ currency }}{{ shippingFee }}</block>
				</view>
				<view class="submit-bar-price" v-else>{{ currency }}{{ amountPriceFormated }}</view>
			</view>
			<view class="btn-bar">
				<button class="btn" :class="[disabled ? 'btn-disabled' : 'btn-red']" :disabled="disabled" :loading="loading" @click="onSubmit">{{$t('lang.immediate_payment')}}</button>
			</view>
		</view>
		
		<!-- 商品清单 -->
		<uni-popup :show="inventoryShow" type="bottom" v-on:hidePopup="handelClose('inventory')">
			<view class="show-popup-bottom-common show-popup-qingdan">
				<view class="title">
					<text class="strong">商品清单</text>
					<view class="uni-flex-common">
						<text class="color_999">共 {{inventoryList.goods_count}} 件</text>
						<text class="iconfont icon-close" @click="handelClose('inventory')"></text>
					</view>
				</view>
				<view class="content" :style="{height: `${popHeight.hd}px`}">
					<scroll-view class="commodity" scroll-y="true">
						<view class="commodity_ul">
							<view class="commodity_li" v-for="(item,index) in inventoryList.goods" :key="index" @click="productLink(item)">
								<view class="commodity_left">
									<image :src="item.goods_thumb" mode="aspectFit" class="img"></image>
								</view>
								<view class="commodity_right">
									<view class="commodity_right_one twolist-hidden">{{item.goods_name}}</view>
									<view class="commodity_right_two">
										<text class="uni-red">{{item.goods_price_format}}</text>
										<text>x{{item.goods_number}}</text>
									</view>
									<view class="commodity_right_three" v-if="item.goods_attr !== ''">{{item.goods_attr}}</view>
								</view>
							</view>
						</view>
						<view class="goods_thumb_footer">若对商品价格有所疑问，可点击查看详情</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>
		
		<!--支付方式-->
		<view class="show-popup-bottom-common show-popup-payment">
			<uni-popup :show="paymentShow" type="bottom" mode="fixed" v-on:hidePopup="handelClose('payment')">
				<view class="title">
					<text class="strong">{{$t('lang.payment_mode')}}</text>
					<text class="iconfont icon-close" @click="handelClose('payment')"></text>
				</view>
				<view class="content">
					<view class="tip" :class="{'tipscroll':tipscroll}">
						<text class="iconfont icon-lingdang"></text>
						<text class="txt">修改支付方式可能会影响订单支付金额，提交订单前，请确认好信息</text>
						<text class="iconfont icon-close" @click="tipscroll = false"></text>
					</view>
					<view class="select-tabs">
						<view class="select-list" v-for="(item,index) in payment_method" :key="index" :class="{'active':pay_id == item.pay_id}" @click="payment_method_select(item.pay_id,item.pay_name,item.pay_code)">
							<text>{{ item.pay_name }}</text>
						</view>
					</view>
				</view>
			</uni-popup>
		</view>

		<!--配送方式-->
		<view class="show-popup-bottom-common show-popup-fee">
			<uni-popup :show="feeShow" type="bottom" mode="fixed" v-on:hidePopup="handelClose('fee')">
				<view class="title">
					<text class="strong">{{$t('lang.shipping_mode')}}</text>
					<text class="iconfont icon-close" @click="handelClose('fee')"></text>
				</view>
				<view class="content">
					<scroll-view :scroll-y="true" class="select-list">
						<view class="select-item" v-for="(item,index) in shippingList" :key="index" :class="{'active':shipping_id[shipping_active_id] == item.shipping_id}" @click="shipping_select(item)">
							<view class="txt">{{ item.shipping_name }}</view>
							<view class="iconfont icon-ok" style="padding-right: 0;"></view>
						</view>
					</scroll-view>
				</view>
			</uni-popup>
		</view>
		
		<!--发票-->
		<uni-popup :show="showBase" type="right" v-on:hidePopup="handelClose('inv')">
			<view class="popup-right-show invoice-show">
				<view class="invoice-warp-box">
					<view class="header-title">{{$t('lang.invoice_type')}}</view>
					<view class="selects">
						<view class="select" :class="{'active':invoice.invoiceType == 0}" @click="incrementHandle(0)">{{$t('lang.plain_invoice')}}</view>
						<view class="select" :class="{'active':invoice.invoiceType == 2}" @click="incrementHandle(1)" v-if="invoice.is_shouqianba > 0">电子发票</view>
						<view class="select" :class="{'active':invoice.invoiceType == 1,'btn-box':!invoice.increment}" @click="incrementHandle(2)">{{$t('lang.vat_invoice')}}</view>
					</view>
					<view class="tips">{{$t('lang.checkout_inv_help')}}</view>
				</view>
				<view class="invoice-cont-box" v-if="invoice.invoiceType != 1">
					<view class="selects">
						<view class="select" :class="{'active':invoice.company == false}" @click="invTab(1)">{{$t('lang.person')}}</view>
						<view class="select" :class="{'active':invoice.company == true}" @click="invTab(2)">{{$t('lang.company')}}</view>
					</view>
					<view class="item-inputs" v-if="invoice.company">
						<view class="item-input">
							<label>{{$t('lang.label_company_name')}}</label>
							<input :placeholder="$t('lang.fill_in_company_name')" v-model="invoice.company_name">
						</view>
						<view class="item-input">
							<label>{{$t('lang.taxpayer_id_number')}}</label>
							<input :placeholder="$t('lang.enter_taxpayer_id_number')" v-model="invoice.company_tid">
						</view>
					</view>
					<view class="item-inputs mt20" v-if="invoice.invoiceType == 2">
						<view class="item-input">
							<label>邮箱：</label>
							<input placeholder="请填写邮箱" v-model="invoice.inv_email">
						</view>
						<view class="item-input">
							<label>手机号：</label>
							<input placeholder="请填写手机号码" v-model="invoice.inv_mobile">
						</view>
					</view>
					<view class="invoice-cont">
						<view class="header-title">{{$t('lang.invoice_content')}}</view>
						<view class="list">
							<radio-group @change="invValueRadioHandle">
								<label class="uni-list-cell uni-list-cell-not" v-for="(item,index) in invoiceValue" :key="index">
									<view>
										<radio :value="index" :checked="invoice.invoiceConent == item" color="#f92028" />
									</view>
									<view>{{item}}</view>
								</label>
							</radio-group>
						</view>
					</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<view class="btn btn-red" @click="invoiceConfirm">{{$t('lang.confirm')}}</view>
				</view>
			</view>
		</uni-popup>

		<!--红包-->
		<uni-popup :show="bonusObject.bonusBase" type="bottom" v-on:hidePopup="handelClose('bonus')" >
			<view class="show-popup-bottom-common show-popup-bonus">
				<view class="title">
					<text class="strong">红包</text>
					<text class="iconfont icon-close" @click="handelClose('bonus')"></text>
				</view>
				<view class="content">
					<scroll-view class="scroll-view popup-bonus-list popup-con-filter" scroll-y>
						<view class="bonus-items">
							<view class="bonus-item" v-for="(item,index) in bonusList" :key="index" :class="{'active':bonusObject.bonusId == item.bonus_id}" @click="bonusActive(item.bonus_id)">
								<view class="cont">
									<view class="bonus-left">
										<view class="bonus-money">{{ currency }}{{item.type_money}}</view>
									</view>
									<view class="bonus-right">
										<view class="bonus-tit">{{item.type_name}}</view>
									</view>
								</view>
								<view class="time">{{$t('lang.label_service_life')}}{{item.use_start_date}} {{$t('lang.zhi')}} {{item.use_end_date}}</view>
								<view class="new-store-radio-box">
									<text class="iconfont icon-ok"></text>
								</view>
							</view>
						</view>
					</scroll-view>
					<view class="footer">
						<button type="warn" class="button" @click="submitBonus">{{$t('lang.confirm')}}</button>
					</view>
				</view>
			</view>
		</uni-popup>

		<!-- 储值卡 -->
		<uni-popup :show="valueCardObject.valueCardBase" type="bottom" v-on:hidePopup="handelClose('valueCard')">
			<view class="show-popup-bottom-common show-popup-valuecard" style="height: 80vh;" @touchmove.stop.prevent="moveHandle">
				<view class="title">
					<text class="strong">储值卡</text>
					<text class="iconfont icon-close" @click="handelClose('valueCard')"></text>
				</view>
				<view class="content">
					<view class="cart_tabs">
						<view :class="['tab_item', index == currTab ? 'active_tab' : '']" v-for="(item, index) in tabs" :key="index" @click="onClickTab(index)">
							{{item}}{{index == currTab ? `(${cardCount})` : ''}}
						</view>
					</view>
					<view class="scroll-view-list" v-for="(item, index) in cartList" :key="index" v-show="currTab == index">
						<scroll-view class="scroll-view popup-valuecard-list popup-con-filter" scroll-y :scroll-top="cartScrollTop[index]" @scroll="scrollViewHandle" @scrolltolower="loadMoreHandle">
							<view class="list_wrap">
								<dsc-value-cart :list="item" page="checkout" :active="valueCardObject.valueCardId" :type="index" @link="goLink"></dsc-value-cart>
								<view class="no_data" v-if="shopEmpty">
									<view>很遗憾</view>
									<view>{{currTab == 1 ? '您暂无不可用的储值卡' : '您暂无可使用的储值卡'}}</view>
								</view>
								<view class="loadmore" v-if="item.length >= size">{{loadmoreStatus}}</view>
							</view>
						</scroll-view>
					</view>
					<view class="footer">
						<button type="warn" class="button" @click="submitValuecard">{{$t('lang.confirm')}}</button>
					</view>
				</view>
			</view>
		</uni-popup>

		<!-- 权益卡 -->
		<view class="quanyiShow">
			<uni-popup :show="quanyiShow" v-on:hidePopup="handelClose('quanyi')">
				<view class="quanyi-top">
					<view class="icon-vip">
						<image src="../../static/vip/icon-vip.png" class="img"></image>
					</view>
					<view class="name">{{ vipCard.name }}</view>
					<view class="iconfont icon-close" @click="handelClose('quanyi')"></view>
				</view>
				<view class="quanyi-xian"><text>{{$t('lang.interests')}}</text></view>
				<view class="quanyi-list">
					<view class="item" v-for="(item,index) in vipCard.user_membership_card_rights_list" :key="index">
						<view class="icon">
							<image :src="item.icon" class="img"></image>
						</view>
						<view class="txt">{{item.name}}</view>
					</view>
				</view>
			</uni-popup>
		</view>
		
		<!--优惠券-->
		<uni-popup :show="couponsObject.couponsBase" type="bottom" mode="fixed" v-on:hidePopup="handelClose('coupon')">
			<view class="show-popup-bottom-common show-popup-coupon" style="height: 80vh;" @touchmove.stop.prevent="moveHandle">
				<view class="title">
					<text class="strong">优惠券</text>
					<text class="iconfont icon-close" @click="handelClose('coupon')"></text>
				</view>
				<view class="content">
					<view class="usable-coupon-number">可用优惠券 ({{couponsList.length}})</view>
					<view class="usable-coupon-money">使用优惠券{{couponsObject.couponsIdArr && couponsObject.couponsIdArr.length}}张,共抵扣<text class="uni-red">{{ currency }}{{couponsObject.couponsMoney}}</text></view>
					<scroll-view class="scroll-view" scroll-y v-if="shopCouponList">
						<view class="coupons-list">
							<view class="c-items" v-for="(ruItem,ruIndex) in shopCouponList" :key="ruIndex">
								<view class="c-item" v-for="(item,index) in ruItem.list" :key="index" @click="couponValClick(item.uc_id,item.cou_money,ruIndex)">
									<view class="left">
										<view class="coupon-price">{{item.cou_money_formated}}</view>
										<view class="coupon-desc">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</view>
									</view>
									<view class="right">
										<view class="coupon-tit">
											<uni-tag :text="item.cou_type_name" size="small" type="error"></uni-tag>
											<text class="txt">{{$t('lang.limit')}}{{item.shop_name}}{{$t('lang.usable')}}</text>
											<text>[{{item.cou_goods_name}}]</text>
										</view>
										<view class="time">{{item.cou_end_time}}</view>
										<view class="checkbox" :class="{'checked':couponsObject.couponsId[ruIndex] && (couponsObject.couponsId[ruIndex].uc_id == item.uc_id)}">
											<view class="checkbox-icon">
												<uni-icons type="checkmarkempty" size="16" color="#ffffff"></uni-icons>
											</view>
										</view>
									</view>
								</view>
							</view>
						</view>
					</scroll-view>
					<view class="footer">
						<button type="warn" class="button" @click="submitCoupons()">确定</button>
					</view>
				</view>
			</view>
		</uni-popup>
		
		<!--隐私文章-->
		<uni-popup :show="articleShow" type="bottom" mode="fixed" v-on:hidePopup="articleClose">
			<view class="show-popup-coupon">
				<view class="title">
					<text class="strong">{{showArticle.title}}</text>
					<text class="iconfont icon-close" @click="articleClose"></text>
				</view>
				<view class="article-content">
					<text class="time">{{showArticle.add_time}}</text>
					<rich-text :nodes="showArticle.content"></rich-text>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import uniPopup from '@/components/uni-popup.vue';
	import universal from '@/common/mixins/universal.js';
	import dscValueCart from '@/components/dsc-value-cart/dsc-value-cart.vue';
	let activeTab = 0;
	let timeId = null;
	export default {
		mixins: [universal],
		components: {
			uniIcons,
			uniPopup,
			uniTag,
			dscValueCart
		},
		data() {
			return {
				loading: true,
				disabled: true,
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
					tax_id: 0,
					vat_id: 0,
					invoiceCompany:[],
					user_vat_invoice:'',
					inv_email: '',
					inv_mobile: '',
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
					couponIdsArr:[],
				},
				valueCardObject: {
					valueCardContent: this.$t('lang.no_use_value_card'),
					valueCardBase: false,
					valueCardId: 0,
					valueCardMoney: 0,
					vc_dis: 1,
					vc_dis_money: 0
				},
				rec_type: 0,
				type_id: 0,
				store_id: 0,
				feeShow: false,
				paymentShow: false,
				pay_code: '',
				pay_id: null,
				pay_name: '',
				use_surplus_val: 0,
				use_integral_val: 0,
				price: 0,
				currency: uni.getStorageSync('configData').currency_format || '¥',
				label_text: this.$t('lang.label_total_amount_payable'),
				shippingList: [],
				shipping_active_id: 0,
				shipping_id: [],
				shipping_code: [],
				shipping_type: [],
				shipping_name: [],
				format_shipping_fee: [],
				shipping_fee: [],
				rate_price: [],
				bs_id: 0,
				team_id: 0,
				t_id: 0,
				extension_id: 0, //扩展活动id
				checkout_real_name: '',
				checkout_real_id: '',
				surplus: 0,
				surplus_status: false,
				timer: '',
				rec_id: 0,
				vipReceiveState: false,
				quanyiShow: false,
				shopStore: '',
				isSingle:'',
				articleCheck: true,
				articleShow: false,
				showArticle: {},
				inventoryShow:false,// 商品清单
				inventoryList:[],
				size:10,
				currTab: 0,
				tabs: ['可用卡', '不可用卡'],
				loadmoreStatus: '加载中...',
				shopEmpty: false,
				cartScrollTop: [],
				cartList: [],
				cartPaginated: [],
				valueCartInfo: {},
				tipscroll:false,
				uc_id:0,
				popHeight: {
					hd: 0,
					bd: 0,
					center: 0
				},
				vw: 375,
			};
		},
		computed: {
			...mapState({
				checkoutInfo: state => state.shopping.checkoutInfo,
				checkout_done: state => state.shopping.checkout_done
			}),
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
				if (this.checkoutInfo.goods_list) {
					this.checkoutInfo.goods_list.forEach((v) => {
						v.goods.forEach((res) => {
							rec_ids.push(res.rec_id)
						})
					})
				}
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
			payment_method() {
				return this.checkoutInfo.payment_list ? this.checkoutInfo.payment_list : ''
			},
			total: {
				get() {
					return this.checkoutInfo && this.checkoutInfo.total ? this.checkoutInfo.total : ''
				},
				set(val) {}
			},
			amountPrice: {
				get() {
					let that = this
					let priceStr = ''
					if (that.total) {
						if (that.rec_type == 4) {
							//积分兑换
							that.price = that.total.exchange_integral;
							that.currency = '';

							return that.price
						} else {
							//普通商品价格
							//if (that.price == 0) {
							that.price = that.total.amount
							//}

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
					}
				},
				set(val) {
					this.price = val
				}
			},
			amountPriceFormated() {
				//保留两位小数
				if (this.amountPrice > 0) {
					return this.amountPrice.toFixed(2)
				} else {
					return '0.00'
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
						shippingFeePrice += Number(v)
					})
				}

				return shippingFeePrice
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
			integralSelf() {
				return this.use_integral_val == 0 ? false : true
			},
			integral() {
				let obj = ''
				if (this.checkoutInfo.integral) {
					obj = this.checkoutInfo.integral.length > 0 ? this.checkoutInfo.integral[0].integral : 0
				}
				return obj
			},
			storeInfo() {
				return this.checkoutInfo.store
			},
			listShow() {
				return this.$store.state.shopping.listShow
			},
			invoiceValue() {
				//电子发票只显示明细 invoice.invoiceType == 2
				return this.invoice.invoiceType == 2 ? ['商品明细'] : this.checkoutInfo.invoice_content
			},
			valuecardBg() {
				return this.websiteUrl + '/img/ka_bg.jpg'
			},
			use_paypwd() {
				return this.checkoutInfo.use_paypwd ? this.checkoutInfo.use_paypwd : 0
			},
			use_paypwd_open() {
				return this.checkoutInfo.use_paypwd_open ? this.checkoutInfo.use_paypwd_open : 0
			},
			paypwdValue: {
				get() {
					return this.$store.state.common.trade_pwd
				},
				set(val) {
					this.$store.state.common.trade_pwd = val
				}
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
			moveHandle() {
				// 禁止蒙版下的页面滚动空函数
			},
			async valueCardLoad() {
				let i = this.currTab;
				
				if (this.cartList.length == 0) this.cartList = this.tabs.map(() => []);
				if (this.cartPaginated.length == 0) this.cartPaginated = this.tabs.map(() => 1);
				if (this.cartScrollTop.length == 0) this.cartScrollTop = this.tabs.map(() => 0);
				
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
					this.loadmoreStatus = card_list.length < this.size ? '没有更多了' : '加载中...';
				};
			},
			loadMoreHandle() {
				let i = this.currTab;
				let isMore = this.cartPaginated[i];
				if (isMore > 0) {
					this.loadmoreStatus = '加载中...';
					this.valueCardLoad();
				} else {
					this.loadmoreStatus = '没有更多了';
				}
			},
			onClickTab(i) {
				if (this.currTab == i) return;
				this.currTab = i;
				this.shopEmpty = false;
				this.loadmoreStatus = this.cartList[i].length == this.cardCount ? '没有更多了' : '加载中...';
				if (this.cartList[i].length == 0) this.valueCardLoad();
			},
			goLink(res) {
				if (this.currTab != 0) return;
				const { type, value: { vid } } = res;
				if (type == 'active') this.valueCardActive(vid);
			},
			scrollViewHandle(e) {
				activeTab = this.currTab;
				if (timeId) {
					clearTimeout(timeId);
					return timeId = null;
				}
				setTimeout(() => {
					this.cartScrollTop[activeTab] = e.detail.scrollTop;
				}, 200)
			},
			shop_kud() {
				if (this.ru_ids && this.ru_ids >= 0) {
					uni.redirectTo({
						url: '/pagesC/checkoutone/checkoutone?rec_id=' + this.rec_id + '&stor=1'+ '&ru_id='+ this.ru_ids+ '&rec_type=12&store_id='+ this.store_ids
					})
				} else if (this.goods_id) {
					uni.redirectTo({
						url: '/pagesC/checkoutone/checkoutone?rec_type=12&stor=1&store_id=' + this.store_id + '&goods_id='+ this.goods_id+ '&spec_arr='+this.spec_arr+'&num='+this.num + '&ru_id=' + this.ru_id + '&rec_id=' + this.rec_id+'&isSingle='+this.isSingle
					})
				} else{
					uni.showLoading({
						title: "获取门店列表中"
					});
					
					uni.navigateTo({
						url: '/pagesC/store/store?rec_id=' + this.rec_id + '&stor=0',
						complete:(res)=>{
							uni.hideLoading()
						}
					})
				}
			},
			//默认加载
			checkoutDefault() {
				uni.showLoading({
					title: this.$t('lang.loading')
				});
				if (this.store_id > 0) {
					this.$store.dispatch('setShoppingCheckout', {
						rec_type: this.rec_type,
						store_id: this.store_id,
						leader_id: uni.getStorageSync('leader_id') ? uni.getStorageSync('leader_id') : 0
					})
				} else {
					this.$store.dispatch('setShoppingCheckout', {
						rec_type: this.rec_type,
						type_id: this.type_id,
						team_id: this.team_id,
						leader_id: uni.getStorageSync('leader_id') ? uni.getStorageSync('leader_id') : 0
					})
				}
			},
			//选择收货地址
			checkoutAddress() {
				if (this.checkoutInfo.consignee.nearbyleader > 0) {
					const lngAndLat = {
						lng: this.checkoutInfo.consignee.lng,
						lat: this.checkoutInfo.consignee.lat
					}
					uni.setStorageSync('addressLngLat', JSON.stringify(lngAndLat));
				}
				const nearbyleader = this.checkoutInfo.consignee.nearbyleader || 0;
				uni.navigateTo({
					url: '/pagesB/address/address?type=checkout&nearbyleader=' + nearbyleader
				})
			},
			//发票展开
			invoiceSelect() {
				if (this.checkoutInfo.cross_border) {
					return false
				}

				this.showBase = true
				uni.request({
					url: this.websiteUrl + '/api/invoice',
					method: 'GET',
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data;
						this.invoice.invoiceCompany = data.order_invoice_info;
						this.invoice.user_vat_invoice = data.user_vat_invoice;
						
						//是否显示增值税发票按钮
						this.invoice.increment = this.invoice.user_vat_invoice ? true : false;
					}
				})
			},
			//普通发票 -> 个人单位切换
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
			//普通发票 增值税发票切换
			incrementHandle(val) {
				if (val == 0) {
					// 普通发票
					this.invoice.invoiceType = 0
					this.invoice.vat_id = 0;
				} else if (val == 1) {
					// 电子发票
					this.invoice.invoiceType = 2
					this.invoice.invoiceConent = '商品明细'
				} else if (val == 2) {
					if (this.invoice.increment == true) {
						this.invoice.invoiceType = 1
						this.invoice.vat_id = this.invoice.user_vat_invoice ? this.invoice.user_vat_invoice.id : 0;
					} else {
						uni.showModal({
							content: this.$t('lang.apply_vat_tax_invoice'),
							success: function(res) {
								if (res.confirm) {
									uni.navigateTo({
										url: '/pagesB/invoice/invoice'
									})
								}
							}
						})
					}
				}
			},
			//发票内容
			invValueRadioHandle(e) {
				this.invoiceValue.forEach((v, i) => {
					if (i == e.detail.value) {
						this.invoice.invoiceConent = v
					}
				})
			},
			//发票确认
			invoiceConfirm() {
				if(this.invoice.invoiceType == 2){
					// 电子发票 - 单位
					if (this.invoice.company) {
						if(this.invoice.company_name == '') {
							uni.showToast({ title: this.$t('lang.fill_in_company_name'), icon: 'none' })
							return false
						}else if (this.invoice.company_tid == '') {
							uni.showToast({ title: this.$t('lang.fill_in_taxpayer_id_number'), icon: 'none' })
							return false
						}else if (this.invoice.inv_email == '') {
							uni.showToast({ title: '请填写邮箱', icon: 'none' })
							return false
						}else if(this.invoice.inv_mobile == ''){
							uni.showToast({ title: '请填写手机号码', icon: 'none' })
							return false
						}else{
							this.showBase = false
						}
					}else{
						// 电子发票 -个人
						console.log(this.invoice.inv_mobile)
						if (this.invoice.inv_email == '') {
							uni.showToast({ title: '请填写邮箱', icon: 'none' })
							return false
						}else if(this.invoice.inv_mobile == ''){
							uni.showToast({ title: '请填写手机号码', icon: 'none' })
							return false
						}else{
							this.showBase = false
						}
					}
				}else{
					if (this.invoice.company) {
						if (this.invoice.company_name == '') {
							uni.showToast({ title: this.$t('lang.fill_in_company_name'), icon: 'none' });
							return false
						} else if (this.invoice.company_tid == '') {
							uni.showToast({ title: this.$t('lang.fill_in_taxpayer_id_number'), icon: 'none' });
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
				uni.request({
					url: this.websiteUrl + '/api/trade/changetax',
					method: 'POST',
					data: {
						total: this.total,
						inv_content: this.invoice.invoiceConent
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: ({
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
					}
				})
			},
			//红包选择
			bonusActive(id) {
				if (this.bonusObject.bonusId != id) {
					this.bonusObject.bonusId = id
				} else {
					this.bonusObject.bonusId = 0
				}
			},
			//红包确认
			submitBonus() {
				uni.request({
					url: this.websiteUrl + '/api/trade/changebon',
					method: 'POST',
					data: {
						bonus_id: this.bonusObject.bonusId,
						total: this.total,
						shipping_id: this.shipping_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: ({
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
						this.total.bonus_money = data.bonus_money
						this.amountPrice = data.amount
						this.valueCardObject.valueCardMoney = data.card_money
						this.bonusObject.bonusMoney = data.bonus_money
						this.valueCardObject.vc_dis_money = data.vc_dis_money
						this.valueCardObject.vc_dis = data.vc_dis
						this.valueCardObject.valueCardId = data.value_card_id
					}
				})
				this.bonusObject.bonusBase = false
				this.surplus_status = false
			},
			//优惠券选择
			couponsActive(id) {
				if (this.couponsObject.couponsId != id) {
					this.couponsObject.couponsId = id
				} else {
					this.couponsObject.couponsId = 0
				}
			},
			//优惠券确定
			submitCoupons(uc_id) {
				if(uc_id){
					this.couponsObject.couponsIdArr.push(uc_id);
					this.couponsObject.couponsId[0] = { uc_id:uc_id, money:''}
				}
				
				uni.request({
					url: this.websiteUrl + '/api/trade/changecou',
					method: 'POST',
					data: {
						uc_id: uc_id ? uc_id : this.couponsObject.couponsIdArr,
						total: this.total,
						shipping_id: this.shipping_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: ({
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
						this.valueCardObject.vc_dis_money = data.vc_dis_money
						this.valueCardObject.vc_dis = data.vc_dis
						this.valueCardObject.valueCardId = data.value_card_id
						this.amountPrice = data.amount
					}
				})
				this.couponsObject.couponsBase = false
				this.surplus_status = false
			},
			//储值卡选择
			valueCardActive(id) {
				if (this.valueCardObject.valueCardId != id) {
					this.valueCardObject.valueCardId = id
				} else {
					this.valueCardObject.valueCardId = 0
				}
			},
			//储值卡确认
			submitValuecard() {
				uni.request({
					url: this.websiteUrl + '/api/trade/changecard',
					method: 'POST',
					data: {
						vid: this.valueCardObject.valueCardId,
						total: this.total,
						shipping_id: this.shipping_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: ({
						data: {
							data
						}
					}) => {
						this.$store.dispatch('setCheckoutTotal', data)

						if (data.card_money > 0) {
							this.valueCardObject.valueCardContent = data.card_formated
						} else {
							this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
						}

						this.valueCardObject.vc_dis = data.vc_dis

						this.amountPrice = data.amount
						this.valueCardObject.valueCardMoney = data.card_money
						this.total.card_money = data.card_money
						this.valueCardObject.vc_dis_money = data.vc_dis_money
					}
				})

				this.valueCardObject.valueCardBase = false
				this.surplus_status = false
			},
			//运费展开
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
					consignee: JSON.stringify(shipping_region),
					flow_type: flow_type
				}).then(({
					data: data
				}) => {
					if (data.shipping) {
						this.shippingList = data.shipping
					}
				})
			},
			//选择配送方式
			shipping_select(item) {
				
				this.surplusSelf = false
				
				this.shipping_id.splice(this.shipping_active_id, 1, item.shipping_id)
				this.shipping_code.splice(this.shipping_active_id, 1, item.shipping_code)
				this.shipping_name.splice(this.shipping_active_id, 1, item.shipping_name)
				this.shipping_fee.splice(this.shipping_active_id, 1, item.shipping_fee)
				this.format_shipping_fee.splice(this.shipping_active_id, 1, item.format_shipping_fee)
				this.rate_price.splice(this.shipping_active_id, 1, item.rate_price)

				//关闭弹窗
				this.handelClose('fee');
			},
			//展开支付方式
			paymentSelect() {
				this.paymentShow = true
				
				setTimeout(()=>{
					this.tipscroll = true
				},500)
			},
			//选择支付方式
			payment_method_select(id, name, code) {
				
				this.surplusSelf = false
				
				this.pay_id = id
				this.pay_name = name
				this.pay_code = code

				//关闭弹窗
				this.handelClose('payment');
			},
			//是否使用余额
			surplusSelfHandle(e) {
				this.use_surplus_val = e.detail.value == true ? 1 : 0
			},
			//使用余额
			surplusvValHandle(e) {
				uni.request({
					url: this.websiteUrl + '/api/trade/changesurplus',
					method: 'POST',
					data: {
						total: this.total,
						surplus: this.surplus,
						shipping_id: this.shipping_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data
						this.$store.dispatch('setCheckoutTotal', data)

						if (data.card_money > 0) {
							this.valueCardObject.valueCardContent = data.card_formated
						} else {
							this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
						}

						this.amountPrice = data.amount
						this.valueCardObject.valueCardMoney = data.card_money
						this.total.card_money = data.card_money
						this.total.surplus = data.surplus
						this.total.surplus_formated = data.surplus_formated
						this.surplus = data.surplus
						this.surplus_status = true


						this.valueCardObject.vc_dis = data.vc_dis
						this.valueCardObject.valueCardId = data.value_card_id
					},
					fail: (res) => {
						console.log(JSON.stringify(res))
						uni.hideLoading();
					}
				})
			},
			//是否使用积分
			integralSelfHandle(e) {
				this.use_integral_val = e.detail.value == true ? 1 : 0
				uni.showLoading({
					title: this.$t('lang.loading')
				});
				uni.request({
					url: this.websiteUrl + '/api/trade/changeint',
					method: 'POST',
					data: {
						total: this.total,
						integral_type: this.use_integral_val,
						cart_value: this.cart_id,
						flow_type: this.checkoutInfo.flow_type,
						shipping_id: this.shipping_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data
						this.$store.dispatch('setCheckoutTotal', data)

						if (data.card_money > 0) {
							this.valueCardObject.valueCardContent = data.card_formated

						} else {
							this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
						}

						this.valueCardObject.vc_dis = data.vc_dis
						this.amountPrice = data.amount
						this.valueCardObject.valueCardMoney = data.card_money
						this.total.card_money = data.card_money
						this.valueCardObject.vc_dis_money = data.vc_dis_money
						this.valueCardObject.valueCardId = data.value_card_id
						this.surplusSelf = false

						uni.hideLoading();
					},
					fail: (res) => {
						console.log(JSON.stringify(res))
						uni.hideLoading();
					}
				})
			},
			// 商品清单
			onListShow(i) {
				this.inventoryShow=true
				let newList=this.checkoutInfo.goods_list.find((item,index)=>{
					return item.ru_id==i
				})
				this.inventoryList=newList
				
			},
			//展开弹出层
			popupHandle(val) {
				if (val == 'bonus') {
					this.bonusObject.bonusBase = true
				} else if (val == 'coupon') {
					this.couponsObject.couponsBase = true
				} else if (val == 'valueCard') {
					this.valueCardLoad();
					this.valueCardObject.valueCardBase = true
				}
				this.surplusSelf = false
			},
			//关闭弹出层
			handelClose(val) {
				if (val == 'payment') {
					this.paymentShow = false
				} else if (val == 'fee') {
					this.feeShow = false
				} else if (val == 'bonus') {
					this.bonusObject.bonusBase = false
				} else if (val == 'coupon') {
					this.couponsObject.couponsBase = false
				} else if (val == 'valueCard') {
					this.valueCardObject.valueCardBase = false
					this.valueCardObject.valueCardId = 0
				} else if (val == 'inv') {
					this.showBase = false
				} else if (val == 'quanyi') {
					this.quanyiShow = false
				} else if (val == 'inventory') {
					this.inventoryShow = false
				} 
			},
			onSubmit() {
				let shop_id = []

				this.checkoutInfo.goods_list.forEach((v) => {
					shop_id.push(v.ru_id)
				})

				/* 根据活动rec_type值判断type_id 是什么活动id*/
				if (this.rec_type == 1 || this.rec_type == 2 || this.rec_type == 5 || this.rec_type == 6) {
					this.extension_id = this.type_id
				} else if (this.rec_type == 7) {
					this.t_id = this.type_id
					if (this.pay_code == 'cod' && this.use_surplus_val == 0) {
						uni.showToast({
							title: this.$t('lang.team_not_pay_delivery')
						})
						return false
					}
				} else if (this.rec_type == 8) {
					this.bs_id = this.type_id
				}

				//支付密码
				if ((this.pay_code == 'balance' || this.use_surplus_val > 0 || this.valueCardObject.valueCardId > 0) && this.use_paypwd >
					0 && this.paypwdValue.length < 6) {
					if(this.use_paypwd_open > 0){
						uni.navigateTo({
							url: '/pages/paypwd/paypwd'
						});
					}else{
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.is_open_pwds_go_to'),
							success: (data) => {
								if (data.confirm) {
									uni.navigateTo({
										url: '/pagesB/accountsafe/accountsafe'
									})
								}
							}
						})
					}
					return false
				}

				if (this.checkoutInfo.cross_border) {
					let reg = !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(this.checkout_real_id);
					
					if (this.checkout_real_name == '') {
						uni.showToast({
							title: this.$t('lang.truename_not_null'),
							icon: 'none'
						});
						return false
					} else if (this.checkout_real_id == '') {
						uni.showToast({
							title: this.$t('lang.id_number_not_null'),
							icon: 'none'
						});
						return false
					}  else if(reg){						
						uni.showToast({
							title: this.$t('lang.id_number_format_error'),
							icon: 'none'
						});
						return false
					}
				}

				if (Number(this.surplus) > Number(this.checkoutInfo.user_money)) {
					uni.showToast({
						title: this.$t('lang.use_balance_propmt_1'),
						icon: 'none'
					});
					return false
				}
				
				let media_type = uni.getStorageSync("scene") == 1177 ? 1 : 0;
				
				//点击立即付款
				this.disabled = true
				this.loading = true

				//发票
				let inv_payee = this.invoice.company ? this.invoice.company_name : this.invoice.invoiceTitle
				let tax_id = this.invoice.invoiceType == 1 ? this.invoice.tax_id : this.invoice.company_tid
				let isRecId = this.checkoutInfo.isshipping_list.length > 0 ? this.checkoutInfo.isshipping_list : this.cart_id

				//来源
				let referer = uni.getStorageSync('platform').toLowerCase()
				if (uni.getStorageSync('platform') == 'MP-WEIXIN') {
					referer = 'wxapp'
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
					inv_payee: inv_payee, //发票类别、个人/公司
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
					referer: referer, //订单来源
					order_membership_card_id: this.vipReceiveState ? this.vipCard.order_membership_card_id : 0, //权益卡id
					membership_card_discount_price: this.vipCard.membership_card_discount_price, //购买权益卡折扣
					leader_id: uni.getStorageSync('leader_id') ? uni.getStorageSync('leader_id') : 0,
					media_type: media_type //视频号商品
				}

				if (this.store_id == 0) {
					if (this.checkoutInfo.noshipping_list.length > 0) {
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.noshipping_list_cur_propmt'),
							success: (data) => {
								if (data.confirm) {
									// 支付后支付密码清空
									this.paypwdValue = '';

									this.$store.dispatch('setCheckoutSubmit', o).then((res) => {
										if (res.data) {
											if (res.data.error && res.data.error == 1) {
												uni.hideLoading();
												uni.showToast({
													title: res.data.msg,
													icon: 'none'
												})
											} else {
												uni.reLaunch({
													url: '/pages/done/done?order_sn=' + res.data
												})
											}
										}

										//点击立即付款
										this.disabled = false
										this.loading = false
									})
								} else if (data.cancel) {
									uni.switchTab({
										url: '/pages/cart/cart'
									})

									//点击立即付款
									this.disabled = false
									this.loading = false
								}
							}
						})
					} else if (this.checkoutInfo.isshipping_list.length > 0) {
						// 支付后支付密码清空
						this.paypwdValue = '';
						this.$store.dispatch('setCheckoutSubmit', o).then((res) => {
							if (res.data) {
								if (res.data.error && res.data.error == 1) {
									uni.hideLoading();
									uni.showToast({
										title: res.data.msg,
										icon: 'none'
									})
								} else {
									uni.reLaunch({
										url: '/pages/done/done?order_sn=' + res.data
									})
								}
							}

							//点击立即付款
							this.disabled = false
							this.loading = false
						})
					}
				} else {
					// 支付后支付密码清空
					this.paypwdValue = '';
					this.$store.dispatch('setCheckoutSubmit', o).then((res) => {
						if (res.data) {
							if (res.data.error && res.data.error == 1) {
								uni.hideLoading();
								uni.showToast({
									title: res.data.msg,
									icon: 'none'
								})
							} else {
								uni.reLaunch({
									url: '/pages/done/done?order_sn=' + res.data
								})
							}
						}

						//点击立即付款
						this.disabled = false
						this.loading = false
					})
				}
			},
			productLink(item) {
				if (this.rec_type == 0 && item.extension_code != 'package_buy') {
					uni.navigateTo({ url: "/pagesC/goodsDetail/goodsDetail?id=" + item.goods_id });
				} else if (this.rec_type == 1) {
					uni.navigateTo({ url: "/pagesA/groupbuy/detail/detail?id=" + this.type_id });
				} else if (this.rec_type == 2) {
					uni.navigateTo({ url: "/pagesA/auction/detail/detail?act_id=" + this.type_id });
				} else if (this.rec_type == 4) {
					uni.navigateTo({ url: "/pagesA/exchange/detail/detail?id=" + item.goods_id });
				} else if (this.rec_type == 5) {
					uni.navigateTo({ url: "/pagesA/presale/detail/detail?act_id=" + this.type_id });
				} else if (this.rec_type == 6) {
					uni.navigateTo({url: "/pagesA/seckill/detail/detail?id=" + this.type_id + '&tomorrow=0'});
				} else if (this.rec_type == 7) {
					uni.navigateTo({ url: "/pagesA/team/detail/detail?goods_id=" + item.goods_id });
				} else if (this.rec_type == 8) {
					uni.navigateTo({ url: "/pagesA/bargain/detail/detail?id=" + this.type_id });
				} else if (item.extension_code == 'package_buy') {
					this.$outerHref(this.$websiteUrl + 'package');
				}
			},
			//分销会员卡
			drpApplyHref(id) {
				uni.navigateTo({
					url: '/pagesA/drp/apply/apply?card_id=' + id
				})
			},
			//vip优惠领取
			vipReceive() {
				this.vipReceiveState = !this.vipReceiveState;
				
				uni.showLoading({
					title: this.$t('lang.loading')
				});
				uni.request({
					url: this.websiteUrl + '/api/trade/change_membership_card',
					method: 'POST',
					data: {
						total: this.total,
						order_membership_card_id: this.vipReceiveState ? this.vipCard.order_membership_card_id : 0,
						membership_card_discount_price: this.vipCard.membership_card_discount_price
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data
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
						
						uni.hideLoading();
					},
					fail: (res) => {
						uni.hideLoading();
					}
				})
			},
			quanyiClick() {
				this.quanyiShow = true
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
			onArticleCheck(){
				this.articleCheck = !this.articleCheck;
				
				this.disabled = this.articleCheck ? false : true;
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
		},
		onLoad(e) {
			this.rec_type = e.rec_type ? e.rec_type : 0;
			this.type_id = e.type_id ? e.type_id : 0;
			this.team_id = e.team_id ? e.team_id : 0;
			this.store_id = e.store_id ? e.store_id : 0;
			this.shopStore = e.type ? e.type : '';
			this.rec_id = e.rec_id ? e.rec_id : 0;
			this.store_ids = e.store_ids > 0 ? e.store_ids : 0;
			this.goods_id = e.goods_id ? e.goods_id : 0;
			this.spec_arr = e.attr_id ? e.attr_id : 0;
			this.num = e.num ? e.num : 0;
			this.ru_ids = e.ru_ids ? e.ru_ids : 0;
			this.isSingle = e.isSingle ? e.isSingle : '';
			this.uc_id = e.uc_id ? e.uc_id : 0;
			
			// 支付密码默认清空
			this.paypwdValue = '';
			
			//判断是否安装收钱吧
			if(uni.getStorageSync('configData')){
				this.invoice.is_shouqianba = uni.getStorageSync('configData').is_shouqianba;
			}
		},
		onShow() {
			// #ifdef MP-WEIXIN
			let pages = getCurrentPages();
			let currPage = pages[pages.length - 1];

			//判断是否是从支付密码也返回来的
			if (currPage.data.hope) {
				uni.showLoading({
					title: this.$t('lang.loading')
				});

				this.onSubmit();

				currPage.setData({
					hope: ""
				});
			} else {
				this.checkoutDefault(this.rec_type);
			}
			// #endif

			// #ifdef APP-PLUS
			this.checkoutDefault(this.rec_type);
			// #endif

			this.bonusObject = {
				bonusContent: this.$t('lang.no_use_bonus'),
				bonusBase: false,
				bonusId: 0,
				bonusMoney: 0,
				bonusSn: 0
			}
			this.couponsObject = {
				couponsContent: this.$t('lang.no_use_coupons'),
				couponsBase: false,
				couponsId: [],
				couponsMoney: 0,
				couponsIdArr:[]
			}
			this.valueCardObject = {
				valueCardContent: this.$t('lang.no_use_value_card'),
				valueCardBase: false,
				valueCardId: 0,
				valueCardMoney: 0,
				vc_dis: 1,
				vc_dis_money: 0
			}

			this.use_integral_val = 0
			this.use_surplus_val = 0
		},
		onReady() {
			const { windowHeight, windowWidth } = uni.getSystemInfoSync();
			this.vw = windowWidth;
			this.$set(this.popHeight, 'hd', parseInt(windowHeight * 0.7));
			this.$set(this.popHeight, 'bd', parseInt(windowHeight * 0.7) - uni.upx2px(90));
			this.$set(this.popHeight, 'center', parseInt(windowHeight * 0.7) - uni.upx2px(190));
		},
		onUnload() {
			//离开页面清除
			uni.removeStorageSync('leader_id');
		},
		watch: {
			checkoutInfo() {
				if (this.checkoutInfo.length == 0) {
					uni.switchTab({
						url: '/pages/cart/cart'
					})
					return false
				}

				this.checkoutDisplay = this.checkoutInfo.error || this.checkoutInfo.errors ? false : true

				if (this.checkoutInfo.error) {
					switch (this.checkoutInfo.error) {
						case 'address':
							uni.showModal({
								content: this.$t('lang.goto_add_address'),
								success: (res) => {
									if (res.confirm) {
										uni.navigateTo({
											url: '/pagesB/address/addressEdit'
										})
									} else if (res.cancel) {
										uni.switchTab({
											url: '/pages/cart/cart'
										})
									}
								}
							});
							break
						case 'excess':
							uni.showModal({
								content: this.checkoutInfo.msg,
								showCancel: false,
								confirmText: this.$t('lang.back'),
								success: (res) => {
									if (res.confirm) {
										uni.switchTab({
											url: '/pages/cart/cart'
										})
									}
								}
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
					this.checkout_real_id = this.checkoutInfo.consignee.id_num
					this.checkout_real_name = this.checkoutInfo.consignee.rel_name
				}

				//发票内容
				if (this.checkoutInfo.can_invoice == 1) {
					this.invoice.invoiceConent = this.invoiceValue[0]
				}
				
				// 领券购买
				if(this.uc_id > 0){
					this.submitCoupons(this.uc_id)
				}

				//立即付款可以点击
				this.disabled = false
				this.loading = false
			},
			payment_method() {
				if (this.payment_method == '' && !this.checkoutInfo.error) {
					uni.showToast({
						title: this.$t('lang.payment_method_not_installed'),
						icon: 'none'
					})
					return false
				}
			},
			shipping() {
				this.shipping_id = [];
				this.shipping_code = [];
				this.shipping_name = [];
				this.format_shipping_fee = [];
				this.shipping_fee = [];
				this.shipping_type = [];
				this.rate_price = [];

				//获取默认配送放id,code,type
				this.shipping.forEach((v) => {
					if (v == undefined && this.store_id == 0) {
						this.shipping_name.push('')
						uni.showToast({
							title: this.$t('lang.noshipping_list_propmt'),
							icon: 'none'
						})
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
			address_id() {
				//跨境身份证信息
				if (this.checkoutInfo.consignee) {
					this.checkout_real_id = this.checkoutInfo.consignee.id_num
					this.checkout_real_name = this.checkoutInfo.consignee.rel_name
				}
			},
			surplusSelf() {
				uni.showLoading({ title: this.$t('lang.loading')});
				uni.request({
					url: this.websiteUrl + '/api/trade/changesurplus',
					method: 'POST',
					data: {
						total: this.total,
						surplus: this.surplusSelf ? this.checkoutInfo.user_money : 0,
						shipping_id: this.shipping_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data
						this.$store.dispatch('setCheckoutTotal', data)

						if (data.card_money > 0) {
							this.valueCardObject.valueCardContent = data.card_formated
						} else {
							this.valueCardObject.valueCardContent = this.$t('lang.no_use_value_card')
						}

						this.amountPrice = data.amount
						this.valueCardObject.valueCardMoney = data.card_money
						this.total.card_money = data.card_money
						this.total.surplus = data.surplus
						this.total.surplus_formated = data.surplus_formated
						this.surplus = data.surplus
						this.surplus_status = true
						this.valueCardObject.vc_dis = data.vc_dis
						this.valueCardObject.valueCardId = data.value_card_id

						uni.hideLoading()
					},
					fail: (res) => {
						console.log(JSON.stringify(res))
						uni.hideLoading();
					}
				})
			},
			shopCouponList(){
				this.shopCouponList.forEach(v=>{
					this.couponsObject.couponsId.push({ uc_id:'', cou_money: ''});
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.uni-list-cell-navigate .title {
		margin-right: 20upx;
	}

	.uni-list-cell-title .uni-list-cell-navigate .title {
		color: #000000;
		font-size: 30upx;
		flex: 1;
	}

	.uni-list-cell-title .uni-list-cell-navigate .value {
		font-size: 30upx;
	}

	.item-inputs .item-input input {
		font-size: 25upx;
	}

	/*发票*/
	.invoice-show .header-title {
		font-size: 32upx;
		padding-bottom: 20upx;
		color: #000000;
	}

	.invoice-show .btn-bar {
		margin: 40upx 30upx 0;
	}

	.invoice-warp-box {
		padding: 20upx 30upx;
	}

	.invoice-warp-box {
		border-bottom: 1px solid #f6f6f9;
		background: #FFFFFF;
	}

	.invoice-warp-box .tips {
		font-size: 26upx;
		color: #999999;
		line-height: 1.5;
		margin-top: 20upx;
	}

	.invoice-cont-box .selects {
		padding: 20upx 30upx;
		margin-bottom: 20upx;
		background: #FFFFFF;
	}

	.invoice-cont-box .selects .select {
		padding: 10upx 40upx;
	}

	.invoice-cont {
		background: #FFFFFF;
		margin-top: 20upx;
		padding: 20upx 30upx;
	}

	.invoice-cont .uni-list-cell {
		justify-content: flex-start;
		padding-bottom: 20upx;
		align-items: center;
	}

	.uni-popup-right {
		width: 80%;
	}

	.btn-goods-action .submit-bar-text {
		width: 480upx;
	}

	.cell-value-flex {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
	}

	.cell-value-flex .input-bor {
		flex: none;
		width: 40%;
	}

	.cell-value-flex text {
		font-size: 25upx;
		flex: 1;
		text-align: right;
	}

	.lie-list {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		padding: 20upx 0 0;
		border-top: 1px solid #e5e5e5;
		color: #999;
	}

	.lie-list .left .uni-tag {
		margin-right: 10upx;
	}
	
	.uni-list-cell-vipzk,
	.uni-list-cell-switch{ padding: 22upx; }
	
	.uni-list-cell-vipzk .title{ margin-right: 10upx;}
	
	.uni-list-cell-switch .value{ justify-content: flex-end;}
	
	.vip-zk {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: flex-start;
	}

	.vip-zk .vip-img image {
		width: 60upx;
		height: 60upx;
	}

	.vip-zk .vip-zk-info {
		margin-left: 20upx;
	}

	.vip-zk .vip-zk-info .tit {
		font-size: 28upx;
		color: #333;
		display: flex;
		flex-wrap: wrap;
	}

	.vip-zk .vip-zk-info .tit .txt-price {
		margin: 0 5upx;
		color: #f92028;
	}

	.vip-zk .vip-zk-info .tit .iconfont {
		margin-left: 10upx;
		height: 50upx;
		line-height: 50upx;
	}

	.vip-zk .vip-zk-info .subtit {
		font-size: 25upx;
		color: #999;
	}

	.vip-zk-price {
		height: 100%;
		display: flex;
		flex-direction: row;
		justify-content: flex-end;
		align-items: center;
	}

	.vip-zk-price .price {
		font-size: 28upx;
		margin-right: 10upx;
		color: #f92028;
	}

	.vip-zk-price .mf {
		font-size: 25upx;
	}

	.vip-zk-price .iconfont {
		color: #999;
		/* margin-top: 5upx; */
	}

	.vip-zk-price.active .iconfont {
		color: #f92028;
	}

	.quanyiShow /deep/ .uni-popup-middle {
		width: 70%;
		border-radius: 20upx;
		padding: 20upx;
		height: auto;
	}

	.quanyiShow .quanyi-top {
		text-align: center;
		margin: 20upx 0 0;
		position: relative;
		width: 100%;
	}

	.quanyiShow .quanyi-top .icon-vip {
		width: 80upx;
		height: 80upx;
		display: inline-block;
	}

	.quanyiShow .quanyi-top .name {
		font-size: 28upx;
		font-weight: bold;
		margin-top: 20upx;
	}

	.quanyiShow .quanyi-top .icon-close {
		position: absolute;
		top: -18upx;
		right: 15upx;
	}

	.quanyiShow .quanyi-xian {
		width: 100%;
		text-align: center;
		position: relative;
		margin: 30upx 0;
	}

	.quanyiShow .quanyi-xian text {
		display: inline-block;
		padding: 0 20upx;
		background: #fff;
		overflow: hidden;
		font-size: 25upx;
		color: #999;
		line-height: 40upx;
		height: 40upx;
		position: relative;
		z-index: 3;
	}

	.quanyiShow .quanyi-xian:after {
		content: " ";
		width: 100%;
		height: 1px;
		background: #f0f0f0;
		position: absolute;
		top: 20upx;
		left: 0;
		right: 0;
		z-index: 2
	}

	.quanyiShow .quanyi-list {
		overflow: hidden;
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		width: 100%;
		justify-content: center;
	}

	.quanyiShow .quanyi-list .item {
		width: 33.3%;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin: 20upx 0;
	}

	.quanyiShow .quanyi-list .item .icon {
		width: 100upx;
		height: 100upx;
		border-radius: 50%;
		overflow: hidden;
	}

	.quanyiShow .quanyi-list .item .txt {
		font-size: 25upx;
		color: #666;
		margin-top: 20upx;
	}

	/* 社区驿站 star*/
	.address {
		display: flex;
		align-items: flex-start;
	}

	.post_lebal_red {
		padding: 0 20upx;
		margin-right: 10upx;
		color: white;
		white-space: nowrap;
		background-color: #f44;
	}

	.address_footer {
		font-size: 24upx;
		color: #f92028;
	}

	.title_on {
		display: flex;
		justify-content: space-between;
		background: #ffffff;
		width: 360upx;
		margin: auto;
		border-radius: 30upx;
		margin-bottom: 20upx;
		margin-top: 20upx;
	}

	.title_on view {
		width: 180upx;
		height: 52upx;
		text-align: center;
		line-height: 52upx;
		background: #FFFFFF;
		font-size: 28upx;
		border-radius: 26upx;
	}

	.active_on {
		background: red !important;
		color: #FFFFFF;
	}

	.bottom_b {
		margin: 0 20upx;
		margin-bottom: 20upx;
		border-radius: 20upx;
	}

	.quanymagin {
		margin: 0 20rpx;
		border-radius: 10rpx;
	}

	.uni-card-not {
		margin: 20upx;
		border-radius: 20upx;
		overflow: hidden;
	}
	/* 社区驿站 end*/
	
	/*商品列表*/
	.checkout-goods-list{ margin: 20rpx; border-radius: 20rpx; overflow: hidden; background: #FFFFFF;}
	.checkout-goods-list .product-list-max{ padding-bottom: 20upx !important;}
	.checkout-goods-list .product-list-max .product-items .item{ margin-right: 15upx; margin-bottom: 0;}
	.checkout-goods-list .product-list-max .product-items .item:last-child{ margin-right: 0;}
	.checkout-goods-list .product-list-max .product-items .product-img{ margin: 0;}
	.checkout-goods-list .uni-card,
	.checkout-goods-list .quanymagin{ margin: 0;}
	
	/*底部弹窗*/
	.pop_wrap {
		display: flex;
		flex-direction: column;
		height: 100%;
	}
	.pop_wrap .not-content {
		flex: auto;
		display: flex;
	}
	.pop_wrap .not-content .select-list {
		flex: auto;
		max-height: none;
	}
	.scroll-view-list{
		height: calc(100% - 232rpx);
	}
	.show-popup-valuecard {
		.popup-valuecard-list {
			height: 100%;
			background-color: #fff;
			.no_data {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				height: 600rpx;
				view {
					color: #BABABA;
					&:first-child {
						font-size: 32rpx;
						color: #666;
						margin-bottom: 20rpx;
					}
				}
			}
			.loadmore {
				height: 60rpx;
				text-align: center;
			}
		}
	}
	
	.cart_tabs {
		position: relative;
		display: flex;
		justify-content: space-around;
		align-items: center;
		padding: 25rpx 0;
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
				bottom: -10rpx;
				left: 0;
				width: 100%;
				height: 4rpx;
				background-color: #E83C2D;
			}
		}
	}
	
	.list_wrap {
		padding: 0 40rpx;
		background-color: #fff;
	}
	.loadmore {
		height: 60rpx;
		text-align: center;
	}
</style>
