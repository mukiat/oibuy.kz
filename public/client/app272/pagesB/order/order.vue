<template>
	<view :class="orderInfo.isRegisterDrpShop == 0 && orderInfo.is_drp == 1 ? 'container-tab-bar container-tab-bar-top' : 'container-tab-bar'">
		<view class="tab-bar">
			<view v-for="(tab,index) in tabBars" :key="index" :class="['tab-item',status==index ? 'active' : '']" @click="orderStatusHandle(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<view class="fx-adv" @click="$outerHref('/pagesA/drp/register/register', $isLogin())" v-if="shopConfig.is_show_drp == 1 && orderInfo.isRegisterDrpShop == 0 && orderInfo.is_drp == 1"><image src="../../static/fx.jpg" mode="widthFix"></image></view>
		<view class="section-list">
			<block v-if="orderList && orderList.length > 0">
			<view class="user-item user-order-item" v-for="(item,index) in orderList" :key="index">
				<view class="item-hd">
					<view class="shop-name">{{item.shop_name}}</view>
					<view class="order-status uni-red">{{item.order_status}}</view>
				</view>
				<view class="item-bd">
					<view class="subHead">
						<view class="item">
							<view class="tit">{{$t('lang.order_sn')}}：</view>
							<view class="value"><navigator :url="'../orderDetail/orderDetail?id='+item.order_id" hover-class="none">{{ item.order_sn }}</navigator></view>
						</view>
						<view class="item">
							<view class="tit">{{$t('lang.order_time')}}：</view>
							<view class="value time">
								<view class="add_time">{{ item.add_time }}</view>
								<uni-tag :text="item.activity_lang" size="small" type="error" v-if="item.activity_lang != ''"></uni-tag>
							</view>
						</view>
					</view>
					<view class="product-list product-list-max product-list-scroll" v-if="item.order_goods.length > 1">
						<view class="product-items">
							<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
								<view class="item" v-for="(goodsItem,goodsIndex) in item.order_goods" :key="goodsIndex">
									<navigator :url="'../orderDetail/orderDetail?id='+item.order_id" hover-class="none">
										<view class="product-img">
											<image :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
											<image src="../../static/gift-icon.png" class="icon" v-if="goodsItem.is_gift > 0"></image>
										</view>
									</navigator>
								</view>
							</scroll-view>
						</view>
						<view class="product-more">
							<navigator :url="'../orderDetail/orderDetail?id='+item.order_id" hover-class="none">
								<text>{{$t('lang.gong')}} {{ item.order_goods_num }} {{$t('lang.kuan')}}</text>
								<text class="iconfont icon-more"></text>
							</navigator>
						</view>
					</view>
					<view class="product-list" v-else>
						<view class="product-items">
							<navigator :url="'../orderDetail/orderDetail?id='+item.order_id" hover-class="none">
							<view class="item" v-for="(goodsItem,goodsIndex) in item.order_goods" :key="goodsIndex">
								<view class="product-img">
									<image :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
									<image src="../../static/gift-icon.png" class="icon" v-if="goodsItem.is_gift > 0"></image>
								</view>
								<view class="product-info">
									<view class="product-name twolist-hidden"><image class="country_icon" :src="goodsItem.country_icon" :lazy-load="true" v-if="goodsItem.country_icon"></image>{{ goodsItem.goods_name }}</view>
								</view>
							</view>
							</navigator>
						</view>
						<view class="product-more">
							<navigator :url="'../orderDetail/orderDetail?id='+item.order_id" hover-class="none">
							<text>{{$t('lang.gong')}}  {{ item.order_goods_num }}{{$t('lang.kuan')}}</text>
							<uni-icons type="forward" size="18" color="#999999"></uni-icons>
							</navigator>
						</view>
					</view>
					<view class="list-item-box">{{$t('lang.gong')}} {{item.order_goods_num}}{{$t('lang.total_amount_propmt')}}：<text class="uni-red">{{ item.total_amount_formated }}</text></view>
				</view>
				<view class="item-fd">
					<view class="btn-bar-min">
						<view class="btn" @click="invoiceDetail(item.order_id)" v-if="item.invoice_see == true && item.invoice_type == 2">查看发票</view>
						<view class="btn" @click="delayOrder(item.order_id)" v-if="item.delay === 1">{{$t('lang.delay_in_receiving')}}</view>
						<view class="btn" @click="delOrder(item.order_id)" v-if="item.order_del > 0">{{$t('lang.delete_order')}}</view>
						<view class="btn" @click="delRestore(item.order_id)" v-if="item.is_restore === 1">{{$t('lang.restore_order')}}</view>
						<view class="btn" @click="refoundHandle(item.order_id)" v-if="item.handler_return && item.extension_code != 'package_buy'">{{$t('lang.apply_return')}}</view>
						<view class="btn" @click="cancelOrder(item.order_id)" v-if="item.handler === 1  && item.is_restore === 0">{{$t('lang.cancel_order')}}</view>
						<view class="btn" @click="receivedOrder(item.order_id)" v-else-if="item.handler === 2  && item.is_restore === 0">{{$t('lang.received')}}</view>
						<view class="btn" @click="onlinepay(item.order_sn,item.pay_code)" v-if="item.online_pay && item.failure == 0 && item.is_restore === 0">{{$t('lang.immediate_pay')}}</view>
						<block v-if="item.order_status == '已完成'">
							<view class="btn" @click="commentOrder(item.order_id)" v-if="item.is_comment == 0 && item.shop_can_comment > 0">{{$t('lang.ping_ja')}}</view>
							<view class="btn" @click="buyAgain(item.order_id)" v-if="item.is_store_order == 0">{{$t('lang.buy_again')}}</view>
						</block>
					</view>
				</view>
			</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		<dsc-common-nav></dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	export default {
		components: {
			uniTag,
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		data() {
			return {
				tabBars:[this.$t('lang.suo_ding'),this.$t('lang.order_status_01'),this.$t('lang.order_status_03'),this.$t('lang.ss_received'),this.$t('lang.order_status_11')],
				disabled:false,
				loading:true,
				page:1,
				size:10,
				status:0,
				type:'type',
				dscLoading:true,
				shopConfig: uni.getStorageSync('configData'),
			};
		},
		computed:{
			...mapState({
				orderCount: state => state.user.userorderCount,
				orderInfo: state => state.user.userorderInfo
			}),
			orderList:{
				get(){
					return this.$store.state.user.userOrderList
				},
				set(val){
					this.$store.state.user.userOrderList = val
				}
			},
		},
		methods:{
			//订单列表
			setOrderList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setOrderList',{
					page:this.page,
					size:this.size,
					status:this.status,
					type:this.type
				})
			},
			//订单切换
			orderStatusHandle(index){
				this.status = index
				this.setOrderList(1)
			},
			//立即支付
			onlinepay(id,code){
				uni.navigateTo({
					url:'/pages/done/done?order_sn=' + id,
				})
			},
			//申请退换货
			refoundHandle(id){
				uni.navigateTo({
					url:'/pagesB/afterSales/afterSales?id=' + id,
				})
			},
			//删除订单
			delOrder(id){
				this.$store.dispatch('setDeleteOrder',{
					order_id:id
				}).then(res=>{
					if(res.data == true){
						uni.showToast({
							title:this.$t('lang.order_delete_success'),
							icon:'none'
						});
						this.setOrderList(); 
					}
				})
			},
			//订单还原
			delRestore(id){
				this.$store.dispatch('setRestoreOrder',{
					order_id:id
				}).then(res=>{
					if(res.data == true){
						uni.showToast({
							title:this.$t('lang.suo_ding_1'),
							icon:'none'
						});
						this.setOrderList(1)
					}
				})
			},
			//取消订单
			cancelOrder(id){
				this.$store.dispatch('setOrderHandler',{
					order_id:id
				}).then(res=>{
					if(res.data == true){
						uni.showToast({
							title:this.$t('lang.order_cancel'),
							icon:'none'
						});
						this.setOrderList();
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
						this.setOrderList();  
					}
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
						this.setOrderList()
					}
				})
			},
			commentOrder(id){
				uni.navigateTo({
					url:'../comment/comment?order_id='+id
				})
			},
			//再次购买
			buyAgain(id){
				this.$store.dispatch('setbuyAgain',{
					id:id
				}).then(res=>{
					if(res.data.error == 0){
						if(res.data.cant_buy_goods.length == 0){
							uni.switchTab({
								url:'/pages/cart/cart'
							});
						}else{
							uni.showModal({
								content:this.$t('lang.order_buy_again_propmt'),
								cancelText:this.$t('lang.go_cart'),
								confirmText:this.$t('lang.stay_on_page'),
								success:(res)=>{
									if(res.cancel){
										uni.switchTab({
											url:'/pages/cart/cart'
										});
									}
								}
							})
						}
					}else{
						uni.showToast({
							title:this.$t('lang.parameter_error'),
							icon:'none'
						});
						this.setOrderList(1)
					}
				})
			},
			invoiceDetail(id){
				uni.navigateTo({
					url:'/pagesC/shouqianba/invoiceDetail?order_id=' + id
				})
			}
		},
		onLoad(e){
			if(e.tab){
				this.orderStatusHandle(e.tab)
			}
		},
		onShow(){
			this.setOrderList(1)
		},
		onReachBottom(){
			if(this.page * this.size == this.orderList.length){
				this.page ++
				this.setOrderList()
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/order/order'
			}
		},
		watch:{
			orderList(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
.section-list{ margin-top: 20upx;}
.product-list .product-info{ display: flex; align-items: center;}
.product-list .product-more{ display: flex; align-items: center;}
.add_time{padding-right: 15upx;}

.btn-bar-min{ padding: 0 20rpx; }
.btn-bar-min .btn{ margin-top:20rpx; margin-bottom: 20rpx; }

.country_icon{
	width: 43rpx;
	height: 30rpx;
	padding-right: 7rpx;
	position: relative;
	top: 5rpx;
}

.fx-adv{
	width: 100%;
	line-height: 0;
	font-size: 0;
	position: fixed;
	top: 45px;
	z-index: 99;
}
.fx-adv image{
	width: 100%;
}

.container-tab-bar-top{
	padding-top: 123px;
}
</style>
