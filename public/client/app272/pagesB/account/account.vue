<template>
	<view class="account">
		<view class="purse-header-box">
			<view class="text">{{$t('lang.usable_balance')}}</view>
			<view class="value">
				<view class="money" v-if="account.user_money">{{ account.user_money }}</view>
				<view class="frozen-money" v-if="account.frozen_money">{{$t('lang.frozen_balance')}}：{{ account.frozen_money }}</view>
			</view>
			<view class="user-money-list">
				<navigator url="../bonus/bonus" class="money-item text-left" hover-class="none">{{$t('lang.bonus')}}<text class="user-money-list-item" v-text="account.bonus_count || 0"></text></navigator>
				<navigator url="../valueCard/valueCard" class="money-item text-center" hover-class="none">{{$t('lang.value_card')}}<text class="user-money-list-item" v-text="value_card.num || 0"></text></navigator>
				<view class="money-item text-right">{{$t('lang.integral')}}<text class="user-money-list-item" v-text="account.pay_points || 0"></text></view>
			</view>
		</view>
		<view class="user-pur-box" v-if="account.user_balance_recharge > 0 || account.user_balance_withdrawal > 0">
			<view class="item" @click="clickPay" v-if="account.user_balance_recharge == 1"><image src="../../static/recharge_1.png"></image>{{$t('lang.recharge')}}</view>
			<navigator url="raply/raply" hover-class="none" class="item" v-if="account.user_balance_withdrawal == 1"><image src="../../static/recharge_2.png"></image>{{$t('lang.deposit')}}</navigator>
		</view>
		<view class="my-nav-box">
			<navigator url="detail/detail" hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-detail"></text>{{$t('lang.account_detail')}}</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
			<navigator :url="'integra/integra?type='+account.pay_points "hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-jinqian"></text>我的积分</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
			<navigator url="log/log" hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-partner"></text>{{$t('lang.application_record')}}</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
			<navigator url="../invoice/invoice" hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-invoices-query"></text>{{$t('lang.vat_invoice')}}</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
		</view>
		
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	export default {
		components:{
			uniIcons,
			dscCommonNav
		},
		data() {
			return {
				account:Object,
				value_card:Object
			};
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/account/account'
			}
		},
		methods:{
			clickPay(){
				// #ifdef MP-WEIXIN
				uni.navigateTo({
					url:'/pagesB/account/deposit/deposit'
				})
				// #endif
				
				// #ifdef APP-PLUS
				this.$outerHref(this.$websiteUrl+'user/account/deposit')
				// #endif
			}
		},
		onShow() {
			this.$store.dispatch('setAccount').then(res =>{
				this.account = res.data
				this.value_card = res.data.value_card;
			})
		}
	}
</script>

<style>
.account{ padding: 30upx;}
.account .purse-header-box{ padding: 20upx; width: 100%; box-sizing: border-box; border-radius: 10upx; background-color: #0196fd; box-shadow: 0px 4px 18px rgba(0, 128, 248, 0.4) !important; color: #FFFFFF;}
.account .purse-header-box .value{ display: flex; flex-direction: row; justify-content: space-between; align-items: center;}
.account .purse-header-box .value .money{ font-size: 40upx;}
.account .purse-header-box .value .frozen-money{ padding: 0 20upx; border: 1px solid #80ccff; height: 20px; line-height: 20px; border-radius: 10px; font-size: 25upx;}
.account .purse-header-box .user-money-list{ display: flex; flex-direction: row; margin-top: 50upx;}
.account .purse-header-box .user-money-list .money-item{ flex: 1 1 0%;}
.account .user-pur-box{ padding: 20upx; margin-top: 20upx; background-color: #FFFFFF; display: flex; flex-direction: row; border-radius: 10upx;}
.account .user-pur-box .item{ flex: 1 1 0%; display: flex; flex-direction: row; align-items: center; justify-content: center;}
.account .user-pur-box .item:first-child image{ width: 26px; height: 17px; margin-right: 20upx;}
.account .user-pur-box .item:last-child image{ width: 18px; height: 17px; margin-right: 20upx;}
.account .my-nav-box{ margin-top: 20upx; border-radius: 10upx; background: #FFFFFF;}
.account .my-nav-box .item{ display: flex; padding: 20upx; border-bottom: 1px solid #f6f6f9; justify-content: space-between; align-items: center;}
.account .my-nav-box .item .tit .iconfont{ margin-right: 10upx; font-size: 14px;}
.user-money-list-item {
	margin-left: 20rpx;
}
</style>
