<template>
	<view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate justify-content-fs">
						<text class="title">{{$t('lang.recharge_money')}}</text>
						<view class="value uni-flex-custom">
							<input :placeholder="$t('lang.enter_recharge_money')" type="digit" name="amount" v-model="amount" />
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="field-tips">{{$t('lang.lowest_recharge_money')}}：<text class="uni-red" v-if="buyer_recharge > 0">{{ currency_format }}{{ buyer_recharge }}</text></view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<!-- #ifdef APP-PLUS -->
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="paymentSelect">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.payment_mode')}}</text>
						<view class="value">{{ pay_name }}</view>
					</view>
				</view>
				<!-- #endif -->
				<!-- #ifdef MP-WEIXIN -->
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.payment_mode')}}</text>
						<view class="value">{{ pay_name }}</view>
					</view>
				</view>
				<!-- #endif -->
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate justify-content-fs">
						<text class="title">{{$t('lang.remarks')}}</text>
						<view class="value">
							<textarea :placeholder="$t('lang.select_fill')" name="user_note" v-model="user_note" maxlength="50"></textarea>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-bar btn-bar-fixed">
			<view class="btn btn-red" @click="submitBtn">{{$t('lang.submit_apply')}}</view>
		</view>


		<!--支付方式-->
		<uni-popup :show="paymentShow" type="bottom" v-on:hidePopup="handelClose('payment')">
			<view class="activity-popup">
				<view class="title">
					<view class="txt">{{$t('lang.payment_mode')}}</view>
					<uni-icons type="closeempty" size="36" color="#999999" @click="handelClose('payment')"></uni-icons>
				</view>
				<view class="not-content">
					<view class="select-list">
						<view class="select-item" v-for="(item,index) in payment_method" :key="index" :class="{'active':pay_id == item.pay_id}" @click="payment_method_select(item.pay_id,item.pay_name,item.pay_code)">
							<view class="txt">{{ item.pay_name }}</view>
							<view class="iconfont icon-ok"></view>
						</view>
					</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import uniPopup from '@/components/uni-popup.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	export default {
		components: {
			uniPopup,
			uniIcons
		},
		data() {
			return {
				amount:'',
				user_note:'',
				payment_method:[],
				paymentShow: false,
				pay_id: null,
				pay_name: '',
				pay_code:'',
				buyer_recharge:0,
				submit_btn:'',
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
			};
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/account/deposit/deposit'
			}
		},
		methods:{
			depositInfo(){
				uni.request({
					url:this.websiteUrl + '/api/account/deposit',
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if(res.data.status == 'success'){
							this.payment_method = res.data.data.payment
							//#ifdef MP-WEIXIN
							this.payment_method.forEach(pay=>{
								if(pay.pay_code == 'wxpay'){
									this.pay_id = pay.pay_id;
									this.pay_name = pay.pay_name;
								}
							})
							//#endif
						}
					}
				});
			},
			shopConfig(){
				
				if (uni.getStorageSync('configData')) {
					
					this.buyer_recharge = uni.getStorageSync('configData').buyer_recharge || 0;
				} else {
					uni.request({
						url:this.websiteUrl + '/api/shop/config',
						method:'GET',
						data:{},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: ({data:{data}}) => {
							this.buyer_recharge = data.buyer_recharge
						}
					});
				}
				
			},
			submitBtn(){
				let that = this
				if(Number(this.amount) >= Number(this.buyer_recharge)){
					uni.getStorage({
						key: 'scopeSession',
						complete: (res) => {
							that.accountPay(res.data.openid)
						}
					});
				}else{
					uni.showToast({
						title:this.$t('lang.recharge_money_prompt'),
						icon:'none'
					});
				}
			},
			accountPay(openid){
				let o = '';
				let platform = uni.getStorageSync('platform');
				if(platform === 'MP-WEIXIN'){
					o = {
						amount:this.amount,
						user_note:this.user_note,
						payment_id:this.pay_id,
						surplus_type:0,
						platform:platform,
						openid:openid
					}
				}else if(platform === 'APP'){
					o = {
						amount:this.amount,
						user_note:this.user_note,
						payment_id:this.pay_id,
						surplus_type:0,
						platform:platform
					}
				}
				uni.request({
					url:this.websiteUrl + '/api/account/account',
					method:'POST',
					data: o,
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if(res.data.status == 'success'){
							if(platform === 'MP-WEIXIN'){
								this.weixinPay(res.data.data.pay_button);
							}else if(platform === 'APP'){
								this.payment(res.data.data.pay_button)
							}
						}
					}
				});
			},
			weixinPay(data){
				let paymentData = JSON.parse(data);
				uni.requestPayment({
					timeStamp: paymentData.timeStamp.toString(),
					nonceStr: paymentData.nonceStr,
					package: paymentData.package,
					signType: 'MD5',
					paySign: paymentData.paySign,
					success: (e) => {
						uni.showToast({
							title: this.$t('lang.order_pay_success'),
						});

						uni.reLaunch({
							url:'/pagesB/account/account'
						});
					},
					fail: (e) => {
						console.log(JSON.stringify(e))
					}
				});
			},
			payment(data){
				uni.requestPayment({
					provider: this.pay_code,
					orderInfo: data,
					success: (e) => {
						uni.showToast({
							title: this.$t('lang.order_pay_success'),
						});

						uni.reLaunch({
							url:'/pagesB/account/account'
						});
					},
					fail: (e) => {
						console.log(JSON.stringify(e));
					}
				});
			},
			//展开支付方式
			paymentSelect() {
			    this.paymentShow = true;
			},
			//选择支付方式
			payment_method_select(id, name,code) {
			    this.pay_id = id;
			    this.pay_name = name;
				this.pay_code = code;
				this.paymentShow = false;
			},
			//关闭弹出层
			handelClose(val){
				if(val == 'payment'){
					this.paymentShow = false
				}
			},
		},
		onLoad(){
			this.shopConfig();
		},
		onShow() {
			this.depositInfo();
		}
	}
</script>

<style>
.uni-list-cell-navigate { align-items: flex-start;}
.uni-list-cell-navigate .title{ margin-right: 20upx; color: #000;}
.uni-list-cell-navigate .value{ flex: 1;}
.uni-list-cell-navigate .value textarea{ width:100%; height: 50px; margin-top: 10upx;}
.field-tips{ margin: 0 30upx;}
</style>
