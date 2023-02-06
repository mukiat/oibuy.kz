<template>
	<view class="container fixed-bottom-padding">
		<view class="flow-checkout-adr flow-store-adr" v-if="orderDetail.offline_store && orderDetail.offline_store.stores_name != undefined ">
			<uni-icons type="location" size="28" color="#999999"></uni-icons>
			<view class="store-content">
				<view class="tit">{{ orderDetail.offline_store.stores_name }}</view>
				<!-- <text v-if="orderDetail.pick_code">{{$t('lang.take_delivery_code')}}: {{ orderDetail.pick_code }}</text> -->				
				<text v-if="orderDetail.take_time">{{$t('lang.store_order_time')}}：{{ orderDetail.take_time }}</text>
				<text>{{$t('lang.service_call')}}: {{ orderDetail.offline_store.stores_tel }}</text>
				<text>{{$t('lang.business_hours')}}: {{ orderDetail.offline_store.stores_opening_hours }}</text>
				<text>{{$t('lang.label_store_address')}}{{ orderDetail.offline_store.stores_address }}</text>
			</view>
		</view>
		<view class="flow-checkout-adr" v-else>
			<view class="adr-content">
				<view class="title">
					<text class="name">{{ orderDetail.consignee }}</text>
					<text class="mobile">{{ orderDetail.mobile }}</text>
				</view>
				<view class="address"><text class="post_lebal_red" v-if="orderDetail.post_mobile">{{$t('lang.community_post')}}</text>{{ orderDetail.address }}</view>
				<view v-if="orderDetail.post_mobile">{{$t('lang.post_contact_number')}}: {{orderDetail.post_mobile}}</view>
			</view>
		</view>
		<view class="flow-checkout-item claim_goods_box" v-if="orderDetail.post_delivery_code">
			<view>{{$t('lang.post_pickup_code')}}</view>
			<view>{{orderDetail.post_delivery_code}}</view>
			<view><text @click="viewQrCode">{{$t('lang.view_qr_code')}}</text></view>
		</view>
		<view class="flow-checkout-item claim_goods_box" v-if="orderDetail.pick_code">
			<view>{{$t('lang.take_delivery_code')}}</view>
			<view>{{orderDetail.pick_code}}</view>
			<view><text @click="viewMnCode">{{$t('lang.view_qr_code')}}</text></view>
		</view>
		<view class="section-list">
			<view class="user-item">
				<view class="item-hd">
					<view class="shop-name">{{orderDetail.shop_name}}</view>
					<!-- #ifdef MP-WEIXIN -->
					<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="kefu kefu-cantact" v-if="wxappChat > 0">
						<text>{{$t('lang.relation_contact')}}</text>
						<text class="iconfont icon-kefu uni-red"></text>
					</button>
					<view class="kefu" @click="onChat(0,orderDetail.shop_id)" v-else>
						<text>{{$t('lang.relation_contact')}}</text>
						<text class="iconfont icon-kefu uni-red"></text>
					</view>
					<!-- #endif -->
					<!-- #ifndef MP-WEIXIN -->
					<view class="kefu" @click="onChat(0,orderDetail.shop_id)">
						<text>{{$t('lang.relation_contact')}}</text>
						<text class="iconfont icon-kefu uni-red"></text>
					</view>
					<!-- #endif -->
				</view>
				<view class="item-bd">
					<view class="subHead">
						<view class="item">
							<view class="tit">{{$t('lang.order_sn')}}：</view>
							<view class="value">{{ orderDetail.order_sn }}</view>
						</view>
						<view class="item">
							<view class="tit">{{$t('lang.order_time')}}：</view>
							<view class="value time mr10">{{ orderDetail.add_time }}</view>
							<view class="tag">
								<block v-if="orderDetail.team_id > 0">
									<block v-if="orderDetail.failure != 1">{{$t('lang.team_order')}}</block>
									<block v-else>{{$t('lang.team_fail')}}</block>
								</block>
								<uni-tag :text="orderDetail.activity_lang" size="small" type="error" v-if="orderDetail.activity_lang != ''"></uni-tag>
							</view>
						</view>
					</view>
					<view class="product-list product-list-max" v-if="listShow">
						<view class="product-items">
							<view class="item" hover-class="none" v-for="(goodsItem,goodsIndex) in orderDetail.goods" :key="goodsIndex" @click="productLink(goodsItem)">
								<view class="product-img">
									<image :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
									<image src="../../static/gift-icon.png" class="icon" v-if="goodsItem.is_gift > 0"></image>
									<image src="../../static/parts-icon.png" class="icon" v-if="goodsItem.parent_id > 0"></image>
								</view>
							</view>
						</view>
						<view class="product-more" @click="onListShow">
							<text>{{$t('lang.gong')}} {{ length }} {{$t('lang.kuan')}}</text>
							<uni-icons type="forward" size="18" color="#999999"></uni-icons>
						</view>
					</view>
					<view class="product-list" v-else>
						<view class="product-items">
							<view class="item" v-for="(goodsItem,goodsIndex) in orderDetail.goods" :key="goodsIndex">
								<view class="product-img" @click="productLink(goodsItem)">
								<image :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
								<image src="../../static/gift-icon.png" class="icon" v-if="goodsItem.is_gift > 0"></image>
								<image src="../../static/parts-icon.png" class="icon" v-if="goodsItem.parent_id > 0"></image>
								</view>
								<view class="product-info">
									<view class="product-name twolist-hidden" @click="productLink(goodsItem)"><image class="country_icon" :src="goodsItem.country_icon" :lazy-load="true" v-if="goodsItem.country_icon"></image>{{ goodsItem.goods_name }}</view>
									<view class="product-attr">{{ goodsItem.goods_attr }}</view>
									<view class="product-row">
										<view class="price">{{ goodsItem.goods_price_formated }}</view>
										<view class="number">x{{ goodsItem.goods_number }}</view>
										<view class="btn-bar-min" v-if="goodsItem.goods_handler_return && goodsItem.is_gift == 0">
											<view class="btn" @click="refoundHandle(goodsItem.rec_id,0)" v-if="goodsItem.is_refound == 0">{{$t('lang.apply_return')}}</view>
											<view class="btn" @click="refoundHandle(goodsItem.ret_id,1)" v-else>{{$t('lang.already_apply_return')}}</view>
										</view>
									</view>
									<view class="p-virtual" v-if="goodsItem.virtual_goods && goodsItem.virtual_goods.length > 0">
										<view v-for="(virtualItem,virtualIndex) in goodsItem.virtual_goods" :key="virtualIndex">
											<view class="virtual-item">{{$t('lang.virtual_card_number')}}：<text class="link" @click="virtualLink(virtualItem.card_sn)">{{virtualItem.card_sn}}</text><text class="copy" @click="copyCard(virtualItem.card_sn)">复制</text></view>
											<view class="virtual-item">{{$t('lang.virtual_card_pwd')}}：{{virtualItem.card_password}}<text class="copy" @click="copyCard(virtualItem.card_password)">复制</text></view>
										</view>
									</view>
								</view>
							</view>
						</view>
						<!-- <view class="product-more" @click="onListShow" v-if="length > 1"><uni-icons type="arrowdown" size="18" color="#999999"></uni-icons></view> -->
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not" v-if="orderDetail.shipping_id && !orderDetail.offline_store">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.shipping_mode')}}</text>
						<view class="value">
							<text class="mr10">{{ orderDetail.shipping_name }}</text><text class="uni-red">{{ orderDetail.shipping_fee_formated }}</text>
						</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-custom" v-if="orderDetail.postscript && !orderDetail.main_count > 0">
					<view class="uni-list-cell-navigate">
						<view class="value">
							<view>{{$t('lang.buyer_message')}}：{{ orderDetail.postscript }}</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not" v-if="orderDetail.cross_warehouse_name != ''" style="margin-top: -20rpx;">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.place_of_shipment')}}</text>
						<view class="value">
							<text style="color: #6C6C6C;">{{ orderDetail.cross_warehouse_name }}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.payment_mode')}}</text>
						<view class="value">{{ orderDetail.pay_name }}</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-custom" v-if="orderDetail.exchange_goods == 0 && orderDetail.can_invoice > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.invoice_info')}}</text>
						<view class="value">
							<block v-if="orderDetail.invoice_type == 1">
								<view>{{$t('lang.label_invoice_content')}} {{$t('lang.vat_tax_invoice')}}</view>
							</block>
							<block v-if="orderDetail.invoice_type == 0">
								<view>{{$t('lang.label_invoice_company')}}{{ orderDetail.inv_payee }}</view>
								<view>{{$t('lang.label_invoice_content')}}{{ orderDetail.inv_content }}</view>
							</block>
							<block v-if="orderDetail.invoice_type == 2">
								<view class="lie">
									<view class="text">发票类型：电子普通发票</view>
									<view class="more uni-red" @click="invoiceDetail(orderDetail.order_id)">查看发票详情</view>
								</view>
								<view>发票抬头：{{ orderDetail.inv_payee }}</view>
								<view>发票内容：{{ orderDetail.inv_content }}</view>
							</block>
						</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.bonus_id > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.bonus')}}</text>
						<view class="value">
							<text class="mr10">{{$t('lang.bonus_amount')}}</text><text class="uni-red">{{ orderDetail.bonus }}</text>
						</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.coupons_type > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.coupons')}}</text>
						<view class="value">
							<text class="mr10">{{$t('lang.coupon_amount')}}</text><text class="uni-red">{{ orderDetail.coupons }}</text>
						</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.vc_id > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.value_card')}}</text>
						<view class="value">
							<text class="mr10">{{$t('lang.value_card_amount')}}</text><text class="uni-red">{{ orderDetail.card_amount }}</text>
						</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.integral > 0 && orderDetail.exchange_goods == 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.integral')}}</text>
						<view class="value">
							<text class="mr10">{{$t('lang.integral_deduction_amout')}}</text><text class="uni-red">{{ orderDetail.integral_money }}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell uni-list-cell-title">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.goods_amout')}}</text>
						<view class="value uni-red">{{ orderDetail.goods_amount_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="discount > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.goods_favorable')}}</text>
						<view class="value uni-red">-{{ orderDetail.discount_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="!orderDetail.offline_store">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.shipping_fee')}}</text>
						<view class="value uni-red">+{{ orderDetail.shipping_fee_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.rate_fee > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.comprehensive_tax')}}</text>
						<view class="value uni-red">+{{ orderDetail.rate }}</view>
					</view>
				</view>
				<block v-if="orderDetail.membership_card_id && orderDetail.membership_card_id > 0">
					<view class="uni-list-cell">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.membership_card_buy_money')}}</text>
							<view class="value uni-red">+{{ orderDetail.membership_card_buy_money_formated }}</view>
						</view>
					</view>
					<view class="uni-list-cell">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.membership_card_discount_price')}}</text>
							<view class="value uni-red">-{{ orderDetail.membership_card_discount_price_formated }}</view>
						</view>
					</view>
				</block>
				<view class="uni-list-cell" v-if="orderDetail.bonus_id > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.use_bonus')}}</text>
						<view class="value uni-red">-{{ orderDetail.bonus }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.coupons_type > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.use_coupons')}}</text>
						<view class="value uni-red">-{{ orderDetail.coupons }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.vc_id > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.use_value_card')}}</text>
						<view class="value uni-red">-{{ orderDetail.card_amount }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.vc_dis_money > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.value_card_discount')}}</text>
						<view class="value uni-red">-{{ orderDetail.vc_dis_money_formated }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.integral > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.use_integral')}}</text>
						<view class="value uni-red">
							<text style="text-align: right;">-{{ orderDetail.integral_money }}\n({{orderDetail.integral}}{{$t('lang.integral')}})</text>
						</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.surplus > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.use_balance')}}<block v-if="orderDetail.presale_final_pay">(支付定金)</block></text>
						<view class="value uni-red">-{{ orderDetail.surplus_formated  }}</view>
					</view>
				</view>
				<view class="uni-list-cell" v-if="orderDetail.money_paid > 0">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.pay_in_balance')}}</text>
						<view class="value uni-red">-{{ orderDetail.money_paid_formated }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<!--银行卡信息-->
		<view class="uni-card uni-card-not" v-if="orderDetail.pay_code == 'bank'">
			<view class="uni-list">
				<view class="uni-list-cell" v-for="(bankItem,bankIndex) in orderDetail.pay_config" :key="bankIndex">
					<view class="uni-list-cell-navigate">
						<text class="title">{{bankItem.name}}</text>
						<view class="value uni-red">{{ bankItem.value }}</view>
					</view>
				</view>
			</view>
		</view>
		
		<view class="uni-card uni-card-not" v-if="orderDetail.delay === 1">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.delay_in_receiving')}}</text>
						<view class="value">
							<block v-if="orderDetail.delay_type == '申请'">
								<button type="warn" size="mini" @click="delayOrder(orderDetail.order_id)">{{ orderDetail.delay_type }}</button>
							</block>
							<block v-else>
								<text class="uni-red">{{ orderDetail.delay_type }}</text>
							</block>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-goods-action" :class="{'btn-goods-action-custom':(totalAmountInt > 0 || orderDetail.tracker) && !orderDetail.pay_config && orderDetail.handler != 8}">
			<view class="submit-bar-text" v-if="orderDetail.pay_status == 2">
				<text>{{$t('lang.suo_money')}}：</text>
				<view class="submit-bar-price">{{ orderDetail.realpay_amount_formated }}</view>
			</view>
			<view class="submit-bar-text" v-else>
			    <text>{{$t('lang.label_total_amount_payable')}}</text>
			    <view class="submit-bar-price">{{ amountPrice }}</view>
			</view>
			<view class="btn-bar">
				<block v-if="orderDetail.pay_code != 'cod' && orderDetail.pay_code != 'bank'">
					<view class="btn btn-red" v-if="orderDetail.total_amount > 0 && orderDetail.handler != 7 && orderDetail.handler != 8 && orderDetail.failure == 0" @click="onlinepay(orderDetail.order_sn)">{{ buttonText }}</view>
				</block>
				<block v-if="orderDetail.handler == 2"><view class="btn btn-red" @click="receivedOrder(orderDetail.order_id)">{{$t('lang.received')}}</view></block>
				<block v-if="orderDetail.handler == 4"><view class="btn btn-red">{{$t('lang.ss_received')}}</view></block>

				<block v-if="orderDetail.handler == 1"><view class="btn btn-org" @click="onClickBigBtn(orderDetail.order_id)">{{$t('lang.cancel_order')}}</view></block>
				<block v-if="orderDetail.handler == 3"><view class="btn btn-org" @click="onCommentBtn(orderDetail.order_id)">{{$t('lang.ping_ja')}}</view></block>
				<block v-if="orderDetail.handler == 5"><view class="btn btn-org">{{$t('lang.payment')}}</view></block>
				<block v-if="orderDetail.handler == 6"><view class="btn btn-org">{{$t('lang.confirmed')}}</view></block>
				<block v-if="orderDetail.handler == 7"><view class="btn btn-org">{{$t('lang.canceled')}}</view></block>
				<block v-if="orderDetail.handler == 8"><view class="btn btn-org">{{$t('lang.invalid_order')}}</view></block>
				<block v-if="orderDetail.tracker"><view class="btn btn-org" @click="orderTracking">{{$t('lang.order_tracking')}}</view></block>
			</view>
		</view>
		<!-- 门店二维码弹框 -->
		<view class="popup_box" v-if="showMnPopup">
			<view class="qr_code_box">
				<view class="qr_code">
					<view class="qr_code_title">
						{{$t('lang.take_delivery_code')}}<text @click="showMnPopup = false">×</text>
					</view>
					<view class="qr_code_content">
						<!-- <image :src="qrCodeData.qrcode_url" class="qr_code_img" ></image> -->
						<view class="qr_code_img_new">
							<canvas canvas-id="qrcode"   style="height: 180px; width: 180px;" />
						</view>
						<view class="qr_code_text">
							<view class="pic">{{orderDetail.pick_code}}</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<!-- 二维码弹框 -->
		<view class="popup_box" v-if="showPopup">
			<view class="qr_code_box">
				<view class="qr_code">
					<view class="qr_code_title">
						{{$t('lang.proof_delivery')}}<text @click="showPopup = false">×</text>
					</view>
					<view class="qr_code_content">
						<image :src="qrCodeData.pick_up_avatar" class="qr_code_img"></image>
						<view class="qr_code_text">
							<view>{{orderDetail.post_delivery_code}}</view>
							<view>{{$t('lang.label_tel')}} {{orderDetail.post_mobile}}</view>
							<view>{{$t('lang.post_pickup_location')}}：{{orderDetail.address}}</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		
		<!--支付方式-->
		<uni-popup :show="deliveries" type="bottom" mode="fixed" v-on:hidePopup="handelClose('deliveries')">
			<view class="activity-popup">
				<view class="title">
					<view class="txt">选择快递跟踪</view>
					<uni-icons type="closeempty" size="36" color="#999999" @click="deliveries = false"></uni-icons>
				</view>
				<view class="not-content">
					<scroll-view :scroll-y="true" class="select-list">
						<view class="select-item" v-for="(item,index) in deliveriesList" :key="index" :class="{'active':delivery_sn == item.delivery_sn}"
						 @click="delivery_select(item.delivery_sn)">
							<view class="txt">{{ item.shipping_name }}<text class="sn">(运单号：{{item.invoice_no}})</text></view>
							<view class="iconfont icon-ok"></view>
						</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import universal from '@/common/mixins/universal.js';
	import uQRCode from '@/common/uqrcode.js'

	export default {
		mixins:[universal],
		components:{
			uniTag,
			uniIcons
		},
		data() {
			return {
				length: 0,
				order_id: 0,
				buttonText: this.$t('lang.immediate_payment'),
				showPopup: false,
				showMnPopup: false,
				qrCodeData: {},
				deliveries:false,
				elivery_sn:'',
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				//微信小程序客服
				wxappChat:uni.getStorageSync("configData").wxapp_chat || 0
			};
		},
		computed:{
			...mapState({
				orderDetail: state => state.user.userOrderDetail
			}),
			goods(){
				return this.orderDetail.goods
			},
			discount(){
				return Number(this.orderDetail.discount)
			},
			amountPrice(){
				let price = 0
				if(this.orderDetail.total_amount){
					price = this.orderDetail.total_amount ? this.orderDetail.total_amount : this.orderDetail.goods_amount
				}

				return this.currency_format + price
			},
			totalAmountInt(){
			  return Number(this.orderDetail.total_amount)
			},
			listShow:{
				get(){
					return false
				  //return this.$store.state.user.listShow
				},
				set(val){
				  this.$store.state.user.listShow = val
				}
			},
			pay_code(){
				return this.orderDetail.pay_code ? this.orderDetail.pay_code : 'balance'
			},
			deliveriesList(){
				return this.orderDetail.deliveries
			}
		},
		methods:{
			orderLoad(id){
				this.$store.dispatch('setOrderDetail',{
					order_id:id
				});
			},
			onListShow(){
				this.listShow = this.listShow ? false : true;
			},
			refoundHandle(id,type){
				if(type == 0){
					uni.navigateTo({
						url: '/pagesB/afterSales/applyReturn/applyReturn?rec_id=' + id + '&order_id=' + this.order_id,
					})
				}else{
					uni.navigateTo({
						url: '/pagesB/afterSales/detail/detail?ret_id=' + id,
					})
				}
			},
			//取消订单
			onClickBigBtn(id){
				this.$store.dispatch('setOrderHandler',{
					order_id:id
				}).then(res=>{
					if(res.data == true){
						uni.showToast({
							title:this.$t('lang.order_cancel'),
							icon:'none'
						})
						this.orderLoad(id);
					}
				})
			},
			//延迟收货
			delayOrder(id){
				this.$store.dispatch('setDelayOrder',{
					order_id:id
				}).then(res=>{
					uni.showToast({
						title:res.data.msg,
						icon:'none'
					});

					if(res.data.error == 0){
						this.orderLoad(id);
					}
				})
			},
			//晒单评论
			onCommentBtn(id){
				uni.navigateTo({
					url:'../comment/comment?order_id='+id
				})
			},
			//确认收货
			receivedOrder(id){
				this.$store.dispatch('setReceivedOrder',{
					order_id:id
				}).then(res=>{
					if(res.data == true){
						uni.showToast({
							title:this.$t('lang.order_confirm_receipt'),
							icon:'none'
						});
						this.orderLoad(id);
					}
				})
			},
			//立即支付
			onlinepay(id,pay){
				uni.navigateTo({
					url:'/pages/done/done?order_sn=' + id
				})
			},
			//二维码生成
			async creatQrCode() {
				let pick_code = this.orderDetail.pick_code;
				let text = JSON.stringify({"pick_code":pick_code});
				await uQRCode.make({
					canvasId: 'qrcode',
					componentInstance: this,
					text: text,
					size: 180,
					margin: 10,
					backgroundColor: '#ffffff',
					foregroundColor: '#000000',
					fileType: 'jpg',
					errorCorrectLevel: uQRCode.errorCorrectLevel.H
				})
			},
			//门店查看二维码
			async viewMnCode() {
				this.showMnPopup = true
				this.creatQrCode();
				
				
				
			
			},
			// 查看二维码viewMnCode
			async viewQrCode() {
			  if (Object.keys(this.qrCodeData).length > 0) {
			    this.showPopup = true;
			  } else {
			    const {data, status} = await this.$store.dispatch('getQrCode',{order_id: this.orderDetail.order_id});
				console.log('data', data, status)
			    if (status !== 'success') {
					return uni.showToast({
						title:this.$t('lang.post_server_busy'),
						icon:'none'
					});
				}
			    this.qrCodeData = data;
			    this.showPopup = true;
			  }
			},
			productLink(item){
				let extension_id = this.orderDetail.extension_id ? this.orderDetail.extension_id : 0;
				
				//超值礼包
				if(item.extension_code == 'package_buy'){
					this.$outerHref(this.$websiteUrl + 'package')
					return
				}
				
				switch(this.orderDetail.extension_code){
					case 'presale':
						uni.navigateTo({ url: "/pagesA/presale/detail/detail?act_id=" + extension_id });
						break
					case 'auction':
						uni.navigateTo({ url: "/pagesA/auction/detail/detail?act_id=" + extension_id });
						break
					case 'bargain_buy':
						uni.navigateTo({ url: "/pagesA/bargain/detail/detail?id=" + extension_id });
						break
					case 'exchange_goods':
						uni.navigateTo({ url: "/pagesA/exchange/detail/detail?id=" + item.goods_id });
						break
					case 'group_buy':
						uni.navigateTo({ url: "/pagesA/groupbuy/detail/detail?id=" + extension_id });
						break
					case 'team_buy':
						uni.navigateTo({ url: "/pagesA/team/detail/detail?goods_id=" + item.goods_id + '&team_id=0' });
						break
					case 'seckill':
						uni.navigateTo({ url: "/pagesA/seckill/detail/detail?id=" + extension_id + '&tomorrow=0' });
						break
					default:
						uni.navigateTo({ url: "/pagesC/goodsDetail/goodsDetail?id=" + item.goods_id });
						break
				}
			},
			//物流跟踪 start
			orderTracking(){
				if(this.deliveriesList.length > 0){
					if(this.deliveriesList.length > 1){
						this.deliveries = true
					}else{
						// #ifdef APP-PLUS
						this.$outerHref(`${this.websiteUrl}/tracker?delivery_sn=${this.deliveriesList[0].delivery_sn}`);
						// #endif
						
						// #ifdef MP-WEIXIN
						uni.navigateTo({
							url:'/pagesC/tracker/tracker?delivery_sn=' + this.deliveriesList[0].delivery_sn
						})
						// #endif
					}
				}else{
					uni.showToast({title:this.$t('lang.deliveries_sn'),icon:'none'});
				}
			},
			delivery_select(delivery_sn){
				this.delivery_sn = delivery_sn;

				// #ifdef APP-PLUS
				this.$outerHref(`${this.websiteUrl}/tracker?delivery_sn=${delivery_sn}`);
				// #endif
				
				// #ifdef MP-WEIXIN
				uni.navigateTo({
					url:'/pagesC/tracker/tracker?delivery_sn=' + delivery_sn
				})
				// #endif
				
				this.deliveries = false
			},
			//物流跟踪 end
			virtualLink(url){
				let newUrl = url.indexOf("http");
				
				if(newUrl === 0) this.$outerHref(url);
			},
			copyCard(code){
				uni.setClipboardData({ data: code });
			},
			invoiceDetail(id){
				uni.navigateTo({
					url:'/pagesC/shouqianba/invoiceDetail?order_id=' + id
				})
			}
		},
		onLoad(e){
			this.order_id = e.id;
		},
		onShow(){
			this.orderLoad(this.order_id)
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/orderDetail/orderDetail'
			}
		},
		watch:{
			goods(){
				this.length = this.goods.length
			}
		}
	}
</script>

<style>
.uni-list-cell-custom .uni-list-cell-navigate{ display: flex; flex-direction: column;}
.uni-list-cell-custom .uni-list-cell-navigate .title{ width: 100%; }
.uni-list-cell-custom .uni-list-cell-navigate .value{ width: 100%; display: flex; color: #999999; flex-direction: column;}
.uni-list-cell-custom .uni-list-cell-navigate .value view{ width: 100%;}

.uni-list-cell-title .uni-list-cell-navigate .title{ color: #000000; font-size: 30upx; flex: 1;}
.uni-list-cell-title .uni-list-cell-navigate .value{ font-size: 30upx; }
.uni-list-cell-navigate {
	align-items: baseline;
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
.claim_goods_box {
  margin: 20upx 0;
  background-color: transparent;
}
.claim_goods_box view {
  text-align: center;
  color: #f44;
  background-color: #fff;
}
.claim_goods_box view:nth-child(1) {
  font-size: 28upx;
  text-align: left;
  color: #666;
  padding: 24upx 20upx 20upx;
  border-bottom: 1px solid #f0f0f0;
}
.claim_goods_box view:nth-child(2) {
  font-size: 36upx;
  padding-top: 20upx;
}
.claim_goods_box view:nth-child(3) {
  padding: 20upx 0;
}
.claim_goods_box text {
  padding: 4upx 20upx;
  border: 1px solid #f44;
}
/* 二维码 弹框 star */
.popup_box {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0,0,0,.3);
	z-index: 100;
}
.qr_code_box{
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
}
.qr_code {
  position: relative;
  box-sizing: border-box;
  text-align: center;
  padding: 0 20upx;
  background-color: #fff;
  border-radius: 20upx;
}
.qr_code_title {
  font-size: 30upx;
  font-weight: 700;
  padding: 40upx 0 24upx;
 /* border-bottom: 1px solid #ccc; */
}
.qr_code_title text {
  position: absolute;
  display: block;
  padding: 20upx;
  top: 0;
  right: 0;
}
.qr_code_content {
  text-align: left;
  line-height: 1.8;
  padding: 30upx 60upx;
  color: #333;
}
.qr_code_img {
  width: 400upx;
  height: 400upx;
}
.qr_code_img_new {
  width: 180px;
  height: 180px;
}
.qr_code_text view:first-child {
  text-align: center;
}
/* 二维码 弹框 end */
/* 社区驿站 end*/

.product-items .item .product-info .product-row .btn-bar-min {
	padding: 0;
}

.pic {
	font-size: 28upx;
	color: red;
	font-weight: 700;
}

.p-virtual .virtual-item{ color: #999; font-size: 25rpx;}
.p-virtual .virtual-item text{ color: #3EB1FA;}
.p-virtual .virtual-item .copy{ margin-left: 10rpx;}

.country_icon{
	width: 43rpx;
	height: 30rpx;
	padding-right: 7rpx;
	position: relative;
	top: 5rpx;
}
</style>
