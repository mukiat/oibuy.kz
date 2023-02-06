<template>
	<view class="drp-info tabbar-padding-bottom" v-if="affiliate_info">
		<view class="drp-warp">
			<view class="header" :style="{'background-image':'url(' + drpInfoBg + ')'}">
				<view class="header-top">
					<view class="header-img"><image :src="affiliate_info.avatar" class="img" /></view>
					<view class="header-right">
						<view class="tit">{{affiliate_info.name}}</view>
						<view class="rank">
							<view class="vip">
								<text>{{affiliate_info.user_rank_name}}</text>
							</view>
							<navigator url="" hover-class="none" v-if="affiliate_info.is_drp > 0" class="more">{{$t('lang.open_vip')}}<uni-icons type="forward" size="18" color="#666666"></uni-icons></navigator>
						</view>
					</view>
				</view>
			</view>
			<view class="section protection" v-if="affiliate_info.user_rank_rights_list && affiliate_info.user_rank_rights_list.length > 0">
				<view class="tit">
					<view>{{$t('lang.enjoy_equity')}}</view>
					<view class="more" @click="protectionHref(0)">{{$t('lang.more')}}<uni-icons type="forward" size="18" color="#805223"></uni-icons></view>
				</view>
				<view class="value">
					<view class="item-list" v-for="(item,index) in affiliate_info.user_rank_rights_list" :key="index" @click="protectionHref(index)">
						<view class="icon"><image :src="item.icon" class="img" /></view>
						<view class="text">{{item.name}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="affiliate-items">
			<view class="section section-money">
				<view class="tit">
					<view>{{$t('lang.my_money')}}</view>
				</view>
				<view class="items">
					<view class="item">
						<text class="txt">{{affiliate_info.user_money}}</text>
						<text class="span">{{$t('lang.is_deposit_money')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{affiliate_info.user_total_order_amount}}</text>
						<text class="span">{{$t('lang.cumulative_commission')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{affiliate_info.user_today_affiliate_money}}</text>
						<text class="span">{{$t('lang.today_income')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{affiliate_info.user_total_affiliate_money}}</text>
						<text class="span">{{$t('lang.drp_total_amount')}}</text>
					</view>
				</view>
				<view class="invite_friends_button" @click="inviteFriends">
					<text>{{$t('lang.team_rule_tit_3')}}</text>
					<uni-icons type="arrowright" color="#805223" size="24"></uni-icons>
				</view>
			</view>
		</view>
		<view class="nav-items">
			<navigator url="../team/team?type=award" class="item">
				<view class="icon"><image :src="imagePath.infoIcon1" class="img" mode="widthFix"></image></view>
				<text>{{$t('lang.registration_award')}}</text>
			</navigator>
			<navigator url="../team/team?type=team" class="item">
				<view class="icon"><image :src="imagePath.infoIcon2" class="img" mode="widthFix"></image></view>
				<text>{{$t('lang.my_team_alt')}}</text>
			</navigator>
		</view>
	</view>
</template>

<script>
	import request from '@/common/request.js'
	export default {
		data() {
			return {
				affiliate_info:''
			}
		},
		computed:{
			drpInfoBg(){
				return this.imagePath.drpInfoBg
			},
		},
		methods: {
			affiliateInfo(){
				request.post(`${this.websiteUrl}/api/user/affiliate_info`).then(res=>{
					if(res.status == 'success'){
						this.affiliate_info = res.data;
					}
				})
			},
			inviteFriends(){
				uni.navigateTo({
					url:'../affiliate'
				})
			},
			protectionHref(index){
				uni.navigateTo({
					url:'../protection/protection?rank_id='+this.affiliate_info.user_rank+'&index='+index
				})
			}
		},
		onShow() {
			this.affiliateInfo();
		}
	}
</script>

<style>
.drp-info{ padding-bottom: 50upx; }
.drp-warp{ padding: 20upx 20upx 120upx 20upx; background: linear-gradient(0deg, #565555, #1c1c1c); }
.drp-warp .header{padding: 20upx; background-size: 100% 100%; border-radius: 20upx; margin-bottom: 20upx;}
.drp-warp .header-top{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; }
.drp-warp .header-top .header-img{ width: 120upx; height: 120upx; overflow: hidden; border-radius: 100%; border:5upx solid rgba(255,255,255,.3); }
.drp-warp .header-top .header-right{ flex: 1; margin-left: 20upx; }
.drp-warp .header-top .header-right .rank{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; }
.drp-warp .header-top .header-right .vip{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; background: linear-gradient(0deg, #151515, #2a2724); padding: 0 25upx 0 20upx; border-radius: 50upx; margin-top: 5upx;}
.drp-warp .header-top .header-right .time{ font-size: 25upx; color: #666666;}
.drp-warp .header-top .header-right .rank .more{ color: #666666;}
.drp-warp .header-top .header-right .vip .icon{ width: 40upx; height: 40upx; margin-right: 10upx;}
.drp-warp .header-top .header-right .vip text{ color: #E3C49E;}

.affiliate-items{ margin: -120upx 20upx; }

.section{ background: #FFFFFF; box-shadow: 0 10upx 30upx 0 rgba(68, 79, 90, 0.11); border-radius: 20upx; margin-bottom: 20upx; overflow: hidden;}
.section .tit{ padding: 10upx 20upx; color: #805223; border-bottom: 2upx solid #E3D6C4; display: flex; flex-direction: row; justify-content: space-between; align-items: center; }
.section .items{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center;}
.section .item{ flex: 1; width: 25%; padding: 25upx 0; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.section .item .txt{ color: #AC8054; font-weight: 500;}
.section .item:first-child .txt{ color: #F2041A;}
.section .item .span{ color: #232323; font-size: 25upx;}

.invite_friends_button{ width: 90%; height: 100upx; line-height: 100upx; background: linear-gradient(118deg, #ecd8be, #dbb280); border-radius: 50upx; color: #805223; font-size: 40upx; text-align: center; margin:20upx auto 40upx; font-weight: 600; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.invite_friends_button text{ margin-right: 10upx;}
.invite_friends_button .uni-icon{ height: 100upx; line-height: 110upx; display: block;}

.nav-items{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; flex-wrap: wrap; margin: 145upx 20upx 0upx;}
.nav-items .item{ width: 48.5%; padding: 20upx; margin-bottom: 20upx; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; background: #FFFFFF; box-shadow: 0 10upx 20upx 0 rgba(95, 95, 95, 0.1); border-radius: 10upx; box-sizing: border-box;}
.nav-items .item .icon{ width: 80upx; height: 80upx; margin-right: 20upx;}
.nav-items .item text{ height: 80upx; line-height: 80upx; color: #805223;}

.protection .value{
	border-radius: 20upx;
	display: flex;
	flex-direction: row;
	padding: 20upx 20upx 0 20upx;
	font-size: 24upx;
	flex-wrap: wrap;
}
.protection .value .item-list{
	width: 25%;
	text-align: center;
	margin-bottom: 20upx;
	box-sizing: border-box;
}
.protection .value .item-list .icon{
	width: 100%;
	border-radius: 50%;
	width: 130upx;
	height: 130upx;
	margin: 0 auto;
}
.protection .value .item-list .icon .img{ border-radius: 50%; }
.protection .value .item-list .text{
	margin-top: 10upx;
	width: 100%;
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
</style>
