<template>
	<view class="user-recharge">
		<view class="header">
			<view :class="[currentIndex == index ? 'active_tab' : '']" v-for="(item, index) in tabData" :key="index" @click="changeChannel(index)">{{item}}</view>
		</view>
		
		<view class="pay_number">
			<template v-if="currentIndex == 0">
				<image class="pay_logo" src="/static/service/zhifubao.png"></image>
				<input class="pay_field" type="text" v-model="payID" :placeholder="$t('lang.placeholder_alipay')" />
			</template>
			<template v-else-if="currentIndex == 1">
				<image class="pay_logo" src="/static/service/wx.png"></image>
				<input class="pay_field" type="text" v-model="payID" :placeholder="$t('lang.placeholder_wxpay')" />
			</template>
			<template v-else>
				<image class="pay_logo" src="/static/service/unionpay.png"></image>
				<view :class="['pay_field', bank_cur ? '' : 'placeholder_color']" style="line-height: 1.2;">{{ bank_cur ? bank_cur : msg }}</view>
			</template>
		</view>
		
		<view class="get_money">
			<view class="title">{{$t('lang.deposit_money')}}</view>
			<view class="money_wrap">
				<view class="money_field">
					<text class="ico">{{ currency_format }}</text>
					<input class="ipt" type="digit" v-model="amount" />
					<view class="all_money" @click="amount = account">{{$t('lang.all')}}</view>
				</view>
				<view class="max_money">{{$t('lang.withdrawal_balance')}}{{account}}{{$t('lang.yuan')}}</view>
			</view>
			<view class="min_money" v-if="buyer_cash">{{$t('lang.lowest_deposit_money')}}：{{depositMoney}}</view>
		</view>
		
		<view class="submit_tips">{{$t('lang.submit_tips')}}</view>
		
		<view class="unverified" v-if="msg && currentIndex == 2">
			<button class="u-reset-button submit_btn">{{msg}}</button>
			<navigator url="/pagesB/realname/realname" hover-class="none" class="go_auth">{{$t('lang.up_real_name')}}</navigator>
		</view>
		<button class="u-reset-button submit_btn" @click="submitApply" v-else>{{$t('lang.submit_apply')}}</button>
		
		<uni-popups ref="popup" type="center">
			<view class="pop_content">
				<view class="apply_tips">
					<text>{{$t('lang.withdraw_tips')}}\n{{$t('lang.withdraw_tips_2')}}</text>
				</view>
				<view class="btn_wrap">
					<button class="u-reset-button cancel_btn" @click="closePopup">{{$t('lang.cancel')}}</button>
					<button class="u-reset-button confirm_btn" :loading="showLoading" @click="beforeCloseHandle">{{loadingTxt}}</button>
				</view>
			</view>
		</uni-popups>
	</view>
</template>

<script>
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
	export default {
		data() {
			return {
				amount:'',
				account:0,
				bank:[],
				bank_cur:'',
				msg:'',
				buyer_cash:0,
				tabData: [this.$t('lang.alipay'), this.$t('lang.wechat'), this.$t('lang.bank_card')],
				currentIndex: 0,
				payID: '',
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				showLoading: false
			};
		},
		computed: {
			loadingTxt: function () {
				return this.showLoading ? '' : this.$t('lang.confirm_on')
			},
			depositMoney: function () {
				if (this.buyer_cash > 0) return this.currency_format + `${this.buyer_cash}`
				else return this.$t('lang.unlimited')
			}
		},
		components: {
			uniPopups
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/account/raply/raply'
			}
		},
		methods:{
			// 切换提现途径
			changeChannel(i) {
				if (this.currentIndex == i) return;
				if (i == 2) this.raplayInfo();
				this.currentIndex = i;
				this.payID = '';
			},
			// 点击提交申请
			submitApply() {
				if (this.currentIndex == 0 && !this.payID) return uni.showToast({title: this.$t('lang.placeholder_alipay'), icon: 'none'});
				if (this.currentIndex == 1 && !this.payID) return uni.showToast({title: this.$t('lang.placeholder_wxpay'), icon: 'none'});
				if(Number(this.amount) >= Number(this.buyer_cash)){
					if (this.currentIndex == 2) this.submitHandle()
					else this.$refs.popup.open()
				}else{
					uni.showToast({
						title:this.$t('lang.deposit_money_prompt'),
						icon:'none'
					});
				}
			},
			// 弹框回调
			beforeCloseHandle() {
				this.showLoading = true;
				this.submitHandle(true);
			},
			// 取消申请
			closePopup() {
				this.$refs.popup.close()
			},
			// 获取提现金额
			raplayInfo(type = 0){
				uni.request({
					url:this.websiteUrl + '/api/account/reply',
					method:'GET',
					data:{
						withdraw_type: type
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						if(data.code == 0){
							if (type == 0) {
								this.bank = data.bank
								this.bank_cur = this.bank[0].bank_card_org
							}
							this.account = data.surplus_amount
							this.buyer_cash = data.buyer_cash
						}else{
							this.msg = data.msg
						}
					}
				});
			},
			// 提交申请 api
			submitHandle(flag){
				let o = {
					amount:this.amount,
					surplus_type: 1
				}
				if (this.currentIndex == 0) {
					o.withdraw_type = 2
					o.withdraw_user_number = this.payID
				} else if (this.currentIndex == 1) {
					o.withdraw_type = 1
					o.withdraw_user_number = this.payID
				} else {
					o.withdraw_type = 0
				}
				uni.request({
					url:this.websiteUrl + '/api/account/account',
					method:'POST',
					data:o,
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						if (flag) {
							this.showLoading = false
							this.$refs.popup.close()
						}
						uni.showToast({
							title:data.msg,
							icon:'none'
						});
						setTimeout(()=>{
							uni.navigateBack()
						},2000)
					}
				});
			}
		},
		onLoad(){
			this.raplayInfo(1)
		}
	}
</script>

<style lang="scss" scoped>
.user-recharge {
	.header {
		display: flex;
		justify-content: space-around;
		align-items: center;
		height: 120rpx;
		background-color: #fff;
		view {
			font-size: 32rpx;
			line-height: 42rpx;
		}
		.active_tab {
			position: relative;
			color: red;
			&::after {
				content: '';
				position: absolute;
				left: 50%;
				bottom: -16rpx;
				transform: translateX(-50%);
				width: 100%;
				height: 6rpx;
				background-color: red;
			}
		}
	}
	.pay_number {
		display: flex;
		align-items: center;
		height: 100rpx;
		margin: 10rpx;
		padding: 0 20rpx;
		background-color: #fff;
		.pay_logo {
			width: 60rpx;
			height: 60rpx;
		}
		.pay_field {
			flex: auto;
			margin-left: 40rpx;
		}
		.placeholder_color {
			color: #999;
		}
	}
	.get_money {
		margin: 0 10rpx;
		padding: 20rpx;
		background-color: #fff;
		.money_wrap {
			display: flex;
			flex-direction: column;
			justify-content: flex-end;
			height: 140rpx;
		}
		.money_field {
			display: flex;
			align-items: center;
			font-size: 36rpx;
			.ico {
				line-height: 1;
			}
			.ipt {
				flex: auto;
				margin: 0 10rpx;
				font-size: 36rpx;
			}
		}
		.all_money {
			font-size: 28rpx;
			line-height: 38rpx;
			color: #62B3FF;
		}
		.max_money {
			padding: 6rpx 0 20rpx;
			font-size: 24rpx;
			line-height: 32rpx;
			text-align: right;
			font-size: 24rpx;
			color: #999;
		}
		.min_money {
			position: relative;
			font-size: 24rpx;
			padding-top: 20rpx;
			color: #999;
			&::after {
				/* #ifndef APP-NVUE */
				content: ' ';
				/* #endif */
				position: absolute;
				left: 0;
				top: 0;
				pointer-events: none;
				box-sizing: border-box;
				-webkit-transform-origin: 0 0;
				transform-origin: 0 0;
				// 多加0.1%，能解决有时候边框缺失的问题
				width: 199.8%;
				height: 199.7%;
				transform: scale(0.5, 0.5);
				border: 0 solid #EDEDED;
				border-top-width: 1px;
				z-index: 2;
			}
		}
	}
	.submit_tips {
		height: 72rpx;
		line-height: 72rpx;
		margin: 0 10rpx;
		padding: 0 20rpx;
		font-size: 24rpx;
		color: red;
	}
	.submit_btn {
		width: 96%;
		height: 88rpx;
		line-height: 88rpx;
		border-radius: 44rpx;
		margin: 160rpx 2% 40rpx;
		font-size: 32rpx;
		color: #fff;
		background-color: red;
	}
	.unverified {
		display: flex;
		flex-direction: column;
		align-items: center;
		.submit_btn {
			background-color: #999;
		}
		.go_auth {
			font-size: 24rpx;
			color: #62B3FF;
		}
	}
	.pop_content {
		width: 638rpx;
		border-radius: 8rpx;
		background-color: #fff;
		.apply_tips {
			position: relative;
			padding: 50rpx;
			line-height: 1.4;
			&::after {
				/* #ifndef APP-NVUE */
				content: ' ';
				/* #endif */
				position: absolute;
				left: 0;
				top: 0;
				pointer-events: none;
				box-sizing: border-box;
				-webkit-transform-origin: 0 0;
				transform-origin: 0 0;
				// 多加0.1%，能解决有时候边框缺失的问题
				width: 199.8%;
				height: 199.7%;
				transform: scale(0.5, 0.5);
				border: 0 solid #EDEDED;
				border-bottom-width: 1px;
				z-index: 2;
			}
		}
		.btn_wrap {
			display: flex;
			height: 100rpx;
			.cancel_btn {
				flex: 1;
				line-height: 100rpx;
				font-size: 32rpx;
				color: #999;
			}
			.confirm_btn {
				position: relative;
				flex: 1;
				line-height: 100rpx;
				font-size: 32rpx;
				color: red;
				&::after {
					/* #ifndef APP-NVUE */
					content: ' ';
					/* #endif */
					position: absolute;
					left: 0;
					top: 0;
					pointer-events: none;
					box-sizing: border-box;
					-webkit-transform-origin: 0 0;
					transform-origin: 0 0;
					// 多加0.1%，能解决有时候边框缺失的问题
					width: 199.8%;
					height: 199.7%;
					transform: scale(0.5, 0.5);
					border: 0 solid #EDEDED;
					border-left-width: 1px;
					z-index: 2;
				}
			}
		}
	}
}
.uni-list-cell-navigate { align-items: flex-start;}
.uni-list-cell-navigate .title{ margin-right: 20upx; color: #000;}
.uni-list-cell-navigate .value textarea{ width:240px; height: 50px; margin-top: 10upx;}
.field-tips{ margin: 0 30upx;}
.realname{ display: flex; flex-direction: row; justify-content: flex-end; color: #007AFF; padding: 0 30upx;}
</style>
