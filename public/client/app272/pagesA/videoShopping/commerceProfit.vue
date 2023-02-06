<template>
	<view class="drp-info tabbar-padding-bottom">
		<view class="drp-warp">
			<view class="header" :style="{'background-image':'url(' + drpInfoBg + ')'}">
				<view class="header-top">
					<view class="header-img">
						<image :src="userInfo.avatar" v-if="userInfo && userInfo.avatar" class="img"></image>
					</view>
					<view class="header-right">
						<view class="tit">您好，{{ userInfo.name }}</view>
						
						<view class="rank">
							<view class="vip">
								<view class="icon">
									<image src="@/static/videopromoter.png" class="img" mode="widthFix"></image>
								</view>
								<text>视频号推广员</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="section section-money">
				<view class="tit">
					<view>视频号带货收益</view>
				</view>
				<view class="items">
					<view class="item" @click="$outerHref('/pagesA/videoShopping/withdrawalList','app')">
						<text class="txt">{{ userPromoter.promoter_money_format }}</text>
						<text class="span">{{$t('lang.deposit_brokerage')}}</text>
					</view>
					<view class="item" @click="$outerHref('/pagesA/videoShopping/commission','app')">
						<text class="txt">{{ userPromoter.commission_money_format }}</text>
						<text class="span">{{$t('lang.drp_totals')}}</text>
					</view>
					<view class="item" @click="$outerHref('/pagesA/videoShopping/income','app')">
						<text class="txt">{{ userPromoter.day_commission_money_format }}</text>
						<text class="span">{{$t('lang.today_income')}}</text>
					</view>
					<view class="item" @click="$outerHref('/pagesA/videoShopping/salesVolume','app')">
						<text class="txt">{{ userPromoter.goods_amount_format }}</text>
						<text class="span">{{$t('lang.drp_total_amount')}}</text>
					</view>
				</view>
			</view>
		</view>
		<view class="team-box">
			<view class="tit">
				<icon class="row"></icon>
				<text>视频号分享员操作</text>
			</view>
			<view class="items">
				<view class="item item1" @click="$outerHref('/pagesA/videoShopping/profit','app')">
					<view class="num"><image src="../../static/media/media1.png" mode="widthFix" class="img"></image></view>
					<icon class="link"></icon>
					<text class="text">收益明细</text>
				</view>
				<view class="item item2" @click="$outerHref('/pagesA/videoShopping/order','app')">
					<view class="num"><image src="../../static/media/media2.png" mode="widthFix" class="img"></image></view>
					<icon class="link"></icon>
					<text class="text">商品订单</text>
				</view>
				<view class="item item3" @click="$outerHref('/pagesA/videoShopping/goods','app')">
					<view class="num"><image src="../../static/media/media3.png" mode="widthFix" class="img"></image></view>
					<icon class="link"></icon>
					<text class="text">带货商品</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				userInfo: '',
				userPromoter:{}
			}
		},
		computed: {
			drpInfoBg(){
				return this.imagePath.drpInfoBg
			},
		},
		async onShow() {
			this.load();
			
			//用户信息
			const { data } = await this.$store.dispatch('userProfile',{ type:true });
			this.userInfo = data ? data : '';
		},
		methods: {
			async load(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/info`);

				if(data.error > 0) return
				
				this.userPromoter = data.user_promoter;
			}
		}
	}
</script>

<style>
.drp-info{ padding-bottom: 50upx; }
.drp-warp{ padding: 20upx 20upx 120upx 20upx; background: linear-gradient(0deg, #565555, #1c1c1c); }
.drp-warp .tip{ padding: 20upx; font-size: 25upx; background: #3b3b3b; color: #ecd8be; border-radius: 20upx; margin-bottom: 20upx; }
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

.drp-warp .header-bottom{ margin-top: 20upx; padding-top: 20upx; display: flex; justify-content: flex-start; }
.drp-warp .header-bottom.bor{ border-top: 2upx solid #dea35a; }
.drp-warp .header-bottom .item{ background: #dea35a; display: flex; justify-content: center; align-items: center; height: 52upx; padding: 0 30upx; border-radius: 26upx; color: #FFFFFF; font-size: 25upx; margin-right: 20upx;}

.section{ background: #FFFFFF; box-shadow: 0 10upx 30upx 0 rgba(68, 79, 90, 0.11); border-radius: 20upx; margin-bottom: 20upx; overflow: hidden;}
.section .tit{ padding: 10upx 20upx; color: #805223; border-bottom: 2upx solid #E3D6C4; display: flex; flex-direction: row; justify-content: space-between; align-items: center; }
.section .items{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center;}
.section .item{ flex: 1; width: 25%; padding: 25upx 0; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.section .item .txt{ color: #AC8054; font-weight: 500;}
.section .item:first-child .txt{ color: #F2041A;}
.section .item .span{ color: #232323; font-size: 25upx;}

.team-box{ margin: -120upx 20upx; padding: 20upx; background: #FFFFFF; box-shadow: 0 10upx 20upx 0 rgba(95, 95, 95, 0.1); border-radius: 20upx;}
.team-box .tit{ display: flex; justify-content: center; align-items: center; position: relative; height: 60upx; margin: 10upx 0 30upx;}
.team-box .tit .row{ width: 200upx; height: 4upx; background: #000000;}
.team-box .tit text{ font-size: 33upx; font-weight: 600; padding: 0 10upx; background: #FFFFFF; position: absolute;}
.team-box .items{ display: flex; flex-direction: row;}
.team-box .item{ flex: 1 1 0%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #FCF3E7; border-radius: 10upx; margin: 0 10upx; height: 160upx;}
.team-box .item1{ margin-right: 10upx;}
.team-box .item3{ margin-left: 10upx;}
.team-box .item .num{ width: 35rpx; height: 35rpx; margin-bottom: 10rpx; }
.team-box .item .link{ background: linear-gradient(90deg, #ecd8be, #dbb280); width: 35upx; height: 4upx; margin: 10upx 0 15upx; }
.team-box .item .text{ color: #805223; font-size: 25upx;}

.invite_friends_button{ width: 90%; height: 100upx; line-height: 100upx; background: linear-gradient(118deg, #ecd8be, #dbb280); border-radius: 50upx; color: #805223; font-size: 40upx; text-align: center; margin:20upx auto 40upx; font-weight: 600; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.invite_friends_button text{ margin-right: 10upx;}
.invite_friends_button .uni-icon{ height: 100upx; line-height: 110upx; display: block;}

.nav-items{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; flex-wrap: wrap; margin: 145upx 20upx 0upx;}
.nav-items .item{ width: 48.5%; padding: 20upx; margin-bottom: 20upx; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; background: #FFFFFF; box-shadow: 0 10upx 20upx 0 rgba(95, 95, 95, 0.1); border-radius: 10upx; box-sizing: border-box;}
.nav-items .item .icon{ width: 80upx; height: 80upx; margin-right: 20upx;}
.nav-items .item text{ height: 80upx; line-height: 80upx; color: #805223;}

.list-ul{ padding: 20upx; }
.list-ul .li{ padding-bottom: 10upx; }
.list-ul .li:last-child{ padding-bottom: 0; }

.ectouch-notcont{ padding: 100upx 0 150upx; text-align: center;}
.ectouch-notcont .img{ width: 280upx; height: 280upx; margin: 0 auto;}
.ectouch-notcont .img image{ width: 100%;}
.ectouch-notcont .cont{ color: #999999; font-size: 30upx; display: block; flex-direction: row;}

.adv{ width: 720upx; height: 200upx; margin: 0 auto;}
.adv .swiper,
.adv .swiper .swiper-item{ width: 720upx; height: 200upx;}
.adv .swiper .swiper-item image{ width: 720upx; height: 200upx; }

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

