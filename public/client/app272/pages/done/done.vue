<template>
	<view>
		<block v-if="doneinfo.pay_code == 'cod' || doneinfo.pay_code == 'balance' || doneinfo.order_amount == 0 || doneinfo.pay_code == 'bank'">
			<view class="flow-done">
				<view class="flow-done-con">
					<block v-if="doneinfo.pay_code == 'cod'">
						<view class="iconfont icon-qian"></view>
						<view class="flow-done-title">{{$t('lang.amount_payable')}}</view>
						<view class="flow-done-price">{{ doneinfo.order_amount_format }}</view>
					</block>
					<block v-else>
						<block v-if="doneinfo.pay_code == 'bank'">
							<view class="iconfont icon-hookring2"></view>
							<view class="flow-done-title">订单提交成功</view>
						</block>
						<block v-else>
							<view class="iconfont icon-hookring2"></view>
							<view class="flow-done-title">{{$t('lang.order_pay_success')}}</view>
						</block>
					</block>
				</view>
				<view class="flow-done-all">
					<block v-if="doneinfo.pay_code == 'bank'">
					<view class="uni-card uni-card-not" v-for="(item,index) in doneinfo.pay_config" :key="index">
						<view class="uni-list">
							<view class="uni-list-cell">
								<view class="uni-list-cell-navigate">
									<text class="title">{{item.name}}</text>
									<view class="value">{{item.value}}</view>
								</view>
							</view>
						</view>
					</view>
					</block>
					<view class="uni-card uni-card-not">
						<view class="uni-list">
							<view class="uni-list-cell">
								<view class="uni-list-cell-navigate">
									<text class="title">{{$t('lang.label_order')}}</text>
									<view class="value">{{ order_sn }}</view>
								</view>
							</view>
						</view>
					</view>
					<view class="btn-bar">
						<block v-if="doneinfo.extension_code == 'team_buy'">
							<navigator :url="'/pagesA/team/wait/wait?team_id=' + doneinfo.team_id" class="btn btn-white">{{$t('lang.view_team_schedule')}}</navigator>
						</block>
						<block v-else-if="doneinfo.extension_code == 'crowd_buy'">
							<navigator url='/pagesA/crowdfunding/user/order' class="btn btn-white">{{$t('lang.view_crowdfunding_order')}}</navigator>
						</block>
						<block v-else>
							<navigator url='/pagesB/order/order' class="btn btn-white">{{$t('lang.view_order')}}</navigator>
							<text class="btn btn-white" style="background-color: #FEF3E4;" @click="$outerHref('/pagesA/drp/register/register', $isLogin())" v-if="shopConfig.is_show_drp == 1">成为分销商</text>
						</block>
					</view>
				</view>
			</view>
		</block>
		<block v-else>
			<view class="cashier-desk">
				<!-- #ifdef APP-PLUS -->
				<view class="header-title">{{$t('lang.label_need_pay')}}<view class="price">{{ doneinfo.order_amount_format }}</view></view>
				<view class="cashier-content">
					<view class="title">{{$t('lang.fill_in_payment')}}</view>
					<view class="uni-list">
						<radio-group @change="radioChange">
							<label class="uni-list-cell uni-list-cell-pd" v-for="(item, index) in payment_list" :key="index">
								<view>{{item.name}}</view>
								<view v-if="item.format_pay_fee" @click="tan(item.format_pay_fee)">({{$t('lang.service_charge')}}<icon class="uni-red">{{item.format_pay_fee}}</icon>)</view>
								<view><radio :value="item.id" color="#f92028" /></view>
							</label>
						</radio-group>
					</view>
				</view>
				<view class="btn">
					<block v-if="currentPayment == 'alipay'">
						<button type="warn" class="page-body-button" @click="paymentSubimt">{{$t('lang.alipay_payment')}}</button>
					</block>
					<block v-else-if="currentPayment == 'wxpay'">
						<button type="warn" class="page-body-button" @click="paymentSubimt">{{$t('lang.wxpay_payment')}}</button>
					</block>
					<block v-else-if="currentPayment == 'baidu'">
						<button type="warn" class="page-body-button" @click="paymentSubimt">{{$t('lang.baidu_payment')}}</button>
					</block>
					<block v-else-if="currentPayment == 'appleiap'">
						<button type="warn" class="page-body-button" @click="paymentSubimt">{{$t('lang.appleiap_payment')}}</button>
					</block>
					<block v-else>
						<button type="warn" disabled="true">{{$t('lang.fill_in_payment')}}</button>
					</block>
				</view>
				<!-- #endif -->
				<!-- #ifdef MP-WEIXIN -->
				<!-- <form @submit="weixinPay" report-submit="true"> -->
				<view class="weixin-content">
					<view class="weixin-icon"><image src="../../static/weixin-pay.png" class="img" /></view>
					<view class="weixin-price">
						<view class="text">{{$t('lang.need_to_pay')}}</view>
						<view class="price">{{ doneinfo.order_amount_format }}</view>
					</view>
					<view class="weixin-pay-btn">
						<button type="primary" @click="subscribePay" :disabled="disabled" class="page-body-button">{{$t('lang.wxpay_payment')}}</button>
					</view>
				</view>
				<!-- </form> -->
				<!-- #endif -->
			</view>
		</block>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import {
		mapState
	} from 'vuex'

	import dscCommonNav from '@/components/dsc-common-nav.vue';
	export default {
		data() {
			return {
				order_sn: '',			//订单编号
				payment_list: [],		//第三方支付方式列表
				currentPayment: '',		//当前选择的第三方支付方式
				orderInfo: '',			//订单信息
				scopeSessions: {},
				dscLoading:true,
				disabled:false,
				shopConfig: uni.getStorageSync('configData'),
				wx_template_id:'',
			};
		},
		components:{
			dscCommonNav
		},
		computed: {
			...mapState({
				doneinfo: state => state.shopping.doneinfo
			}),
		},
		methods: {
			radioChange(e) {
				let that = this
				that.currentPayment = e.detail.value;
				that.getOrderInfo(e.detail.value, that.doneinfo.order_sn)
			},
			getTemplate(code) {
				let that = this
				// 小程序订阅消息 模板列表
				uni.request({
					url: this.websiteUrl + '/api/wxapp/get_template',
					method: 'POST',
					data: {
						code: code,
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							let result = res.data.data
							if (result.length > 0) {
								that.wx_template_id = result[0]['wx_template_id'] || ''
							}
						}
					}
				})
			},
			subscribePay() {
				let that = this;
				if(this.doneinfo.extension_code && this.doneinfo.extension_code == 'team_buy' && this.wx_template_id){
					// 小程序订阅消息 弹窗
					uni.requestSubscribeMessage({
					  tmplIds: [this.wx_template_id],
					  async success (res) {
						console.log('success',JSON.stringify(res))
				
						that.weixinPay();
					  }
					});
				} else {
					this.weixinPay();
				}
			},
			weixinPay(e){
				let that = this
				//读取本地缓存scopeSession
				uni.getStorage({
					key: 'scopeSession',
					complete: (res) => {
						that.disabled = true
						uni.request({
							url: this.websiteUrl + '/api/payment/change_app_payment',
							method: 'GET',
							data: {
								platform: uni.getStorageSync('platform'),
								order_sn: this.doneinfo.order_sn,
								pay_code: 'wxpay',
								openid:res.data.openid,
								//formId:e.detail.formId
							},
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								let paymentData = JSON.parse(res.data.data.button);
								if(paymentData){
									let media_type = uni.getStorageSync("scene") == 1177 ? 1 : 0;
									if(media_type){
										uni.requestOrderPayment({
											timeStamp: paymentData.timeStamp.toString(),
											nonceStr: paymentData.nonceStr,
											package: paymentData.package,
											signType: paymentData.signType,
											paySign: paymentData.paySign,
											orderInfo: paymentData.orderInfo,
											success: (e) => {
												console.log(JSON.stringify(e))
												uni.showLoading({
												    title: this.$t('lang.pay_success'),
													mask:true
												});
												if(this.doneinfo.extension_code == 'team_buy'){
													uni.redirectTo({
														url:'/pagesA/team/order/order',
														complete: (res) => {
															uni.hideLoading()
														}
													})
												}else{
													uni.redirectTo({
														url:'/pagesB/order/order',
														complete: (res) => {
															uni.hideLoading()
														}
													});
												}
											},
											fail: (e) => {
												console.log(JSON.stringify(e))
											},
											complete: (e) => {
												that.disabled = false
											}
										});
									}else{
										uni.requestPayment({
											timeStamp: paymentData.timeStamp.toString(),
											nonceStr: paymentData.nonceStr,
											package: paymentData.package,
											signType: paymentData.signType,
											paySign: paymentData.paySign,
											success: (e) => {
												uni.showLoading({
													title: this.$t('lang.pay_success'),
													mask:true
												});
												if(this.doneinfo.extension_code == 'team_buy'){
													uni.redirectTo({
														url:'/pagesA/team/order/order',
														complete: (res) => {
															uni.hideLoading()
														}
													})
												}else{
													uni.redirectTo({
														url:'/pagesB/order/order',
														complete: (res) => {
															uni.hideLoading()
														}
													});
												}
											},
											fail: (e) => {
												console.log(JSON.stringify(e))
											},
											complete: (e) => {
												that.disabled = false
											}
										});
									}
								}
							},
							fail: (res) => {
								console.log(JSON.stringify(res))
							}
						})
					}
				});
			},
			paymentSubimt() {
				let orderInfo = this.orderInfo.replace('\\', '');
				uni.requestPayment({
					provider: this.currentPayment,
					orderInfo: orderInfo,
					success: (e) => {
						uni.showLoading({
						    title: this.$t('lang.pay_success'),
							mask:true
						});

						if(this.doneinfo.extension_code == 'team_buy'){
							uni.redirectTo({
								url:'/pagesA/team/order/order',
								complete: (res) => {
									uni.hideLoading()
								}
							})
						}else{
							uni.redirectTo({
								url:'/pagesB/order/order',
								complete: (res) => {
									uni.hideLoading()
								}
							});
						}
					},
					fail: (e) => {
						console.log(JSON.stringify(e))
					}
				});
			},
			getOrderInfo(pay_code, order_sn) {
				uni.request({
					url: this.websiteUrl + '/api/payment/change_app_payment',
					method: 'GET',
					data: {
						platform: uni.getStorageSync('platform'),
						order_sn: order_sn,
						pay_code: pay_code
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						console.log('trade/paycheck request success,11')
						console.log(JSON.stringify(res.data))

						this.orderInfo = res.data.data.button;
					},
					fail: (res) => {
						console.log('trade/paycheck request fail')
						console.log(JSON.stringify(res))
					}
				})
			},
			tan(pay_fee){
				if(pay_fee){
					uni.showToast({
						title:this.$t('lang.commission_reminder') + pay_fee + this.$t('lang.commission_reminderon'),
						icon:'none'
					})
				}
			}
		},
		onLoad(e) {
			this.order_sn = e.order_sn;
			this.$store.dispatch('setDoneInfo', {
				order_sn: this.order_sn
			});

			//获取支付方式
			// #ifdef APP-PLUS
			uni.getProvider({
				service: 'payment',
				success: (res) => {
					let providerList = [];
					res.provider.map((value) => {
                        switch (value) {
                            case 'alipay':
                                providerList.push({
                                    name: this.$t('lang.alipay'),
                                    id: value
                                });
                                break;
                            case 'wxpay':
                                providerList.push({
                                    name: this.$t('lang.wxpay'),
                                    id: value
                                });
                                break;
                            default:
                                break;
                        }
                    });

                    this.payment_list = providerList;
				}
			});
			// #endif
			
			// #ifdef MP-WEIXIN
			// 获取小程序订阅消息 拼团进度通知 模板id
			this.getTemplate('5008');
			// #endif
		},
		watch:{
			doneinfo(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
	.cashier-desk {}

	.cashier-desk .header-title {
		background: #FFFFFF;
		display: flex;
		flex-direction: row;
		justify-content: flex-end;
		align-items: center;
		font-size: 32upx;
		padding: 10upx 20upx;
	}

	.cashier-desk .header-title .price {
		color: #f92028;
		font-size: 40upx;
	}

	.cashier-desk .btn {
		position: fixed;
		margin: 0 60upx 50upx;
		bottom: 0;
		left: 0;
		right: 0;
	}

	.cashier-desk .btn button {
		padding: 0;
		font-size: 32upx;
		color: #fff;
		border-radius: 50upx;
		border: 0 !important;
	}

	.cashier-desk .btn button::after {
		content: " ";
		width: 0;
		height: 0;
		position: absolute;
		top: 0;
		left: 0;
		border: none;
		-webkit-transform: scale(0);
		transform: scale(0);
		-webkit-transform-origin: 0 0;
		transform-origin: 0 0;
		box-sizing: border-box;
		border-radius: 0;
	}

	.cashier-content {
		background: #FFFFFF;
		margin-top: 20upx;
	}

	.cashier-content .title {
		padding: 20upx;
		font-size: 32upx;
	}

	.flow-done {
		padding-top: 60px;
	}

	.flow-done-con {
		display: flex;
		flex-direction: column;
		justify-content: center;
		text-align: center;
		align-items: center;
	}

	.flow-done-con .iconfont {
		font-size: 80px;
		line-height: normal;
	}

	.flow-done-con .icon-qian {
		color: #EFCE0C;
	}

	.flow-done-con .icon-hookring2 {
		color: #3ec074;
	}

	.flow-done-con .flow-done-title {
		font-size: 18px;
		margin-top: 20upx;
		color: #444444;
	}
	.flow-done-con .flow-done-price{
		font-size: 28px;
		margin-top: 20upx;
		color: #444444;
	}

	.flow-done-all {
		margin-top: 30px;
	}

	.flow-done-all .uni-list-cell-navigate .title,
	.flow-done-all .uni-list-cell-navigate .value {
		font-size: 16px;
	}

	.flow-done-all .btn-bar {
		margin: 20px 10px;
	}

	.flow-done-all .btn-bar .btn {
		box-shadow: none;
		margin: 0 10px;
		border-radius: 30px;
	}

	.weixin-content{ display: flex; justify-content: center; flex-direction: column; text-align: center; margin: 0 60upx; padding: 150upx 0;}
	.weixin-content .weixin-icon{ width: 120upx; height: 120upx; margin: 30upx auto;}
	.weixin-content .weixin-price .text{ font-size: 40upx; }
	.weixin-content .weixin-price .price{ color: #f92028; font-size: 48upx; margin-top: 20upx;}
	.weixin-content .weixin-pay-btn button { padding: 0;font-size: 32upx;color: #fff;border-radius: 50upx;border: 0 !important; margin-top: 50upx;}
</style>
