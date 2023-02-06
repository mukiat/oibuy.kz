<template>
	<view class="container">
		<view class="drp-select">
			<view class="header">
				<view class="header-warp">
					<view class="header-img">
						<image :src="drpTeamDetailData.user_picture" v-if="drpTeamDetailData.user_picture" />
						<image :src="imagePath.userDefaultImg" v-else></image>
					</view>
					<view class="header-flex">
						<view class="shop-name">{{drpTeamDetailData.shop_name}}</view>
						<view class="shop-rank">{{$t('lang.label_setup_time')}}{{drpTeamDetailData.get_drp_shop.create_time}}</view>
					</view>
					<view class="header-set" @click="drpSet(drpTeamDetailData.get_drp_shop.id)"><view class="iconfont icon-dianpu"></view></view>
				</view>
			</view>
			<view class="cont">
				<view class="col">
					<view class="title">{{$t('lang.cumulative')}}</view>
					<view class="num uni-red">{{drpTeamDetailData.sum_money}}</view>
				</view>
				<view class="col">
					<view class="title">{{$t('lang.day_cumulative')}}</view>
					<view class="num">{{drpTeamDetailData.now_money}}</view>
				</view>
			</view>
			<view class="money-bottom" v-if="drpTeamDetailData.parent_id == drpTeamDetailData.top_user_id">
				<button type="warn" @click="drpTeam">{{$t('lang.view_lower_user')}}</button>
			</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	export default {
		data() {
			return {
				user_id:0
			}
		},
		computed: {
			...mapState({
				drpTeamDetailData: state => state.drp.drpTeamDetailData,
			})
		},
		methods: {
			//团队列表
			drpTeam() {
				uni.navigateTo({
					url:'/pagesA/drp/team/team?parent_id=' + this.drpTeamDetailData.user_id
				})
			},
			drpSet(id){
				uni.navigateTo({
					url:'/pagesA/drp/shop/shop?shop_id=' + id + '&parent_id=' + this.drpTeamDetailData.user_id
				})
			}
		},
		onLoad(e){
			this.user_id = e.user_id
			
			this.$store.dispatch('setDrpTeamDetail',{
				user_id: e.user_id
			})
		}
	}
</script>

<style>
.drp-select .header{ background: linear-gradient(#f84d4d, #fe5b64); padding: 40upx 20upx; color: #FFFFFF;}
.drp-select .header-warp{ display: flex; flex-direction: row; align-items: center; }
.drp-select .header-warp .header-img{ width: 140upx; height: 140upx; overflow: hidden; border-radius: 100%; border:2px solid rgba(255,255,255,.6); }
.drp-select .header-warp .header-img image{ width: 100%; height: 100%;}
.drp-select .header-warp .header-flex{ flex: 1 1 0%; padding: 0 20upx;}
.drp-select .header-warp .header-flex .shop-name{ font-size: 32upx;}
.drp-select .header-warp .header-flex .shop-rank{ font-size: 26upx; color: rgba(255,255,255,.9);}
.drp-select .header-warp .header-set{ margin-top: -50upx; width: 68upx; height: 68upx; background: #d24245; border-radius: 100%; display: flex; justify-content: center; align-items: center; }
.drp-select .header-time{ margin-top: 10upx; display: flex; justify-content: flex-end; font-size: 26upx; line-height: 1;}

.cont{ display: flex; flex-direction: row; justify-content: flex-start; padding: 20upx; background: #FFFFFF;}
.cont .col{ display: flex;  flex-direction: column; width: 50%; }
.cont .col .title{ font-size: 30upx; color: #777;}
.cont .col .num{ font-size: 42upx;}

.money-bottom{ padding: 30upx 20upx; }
</style>
