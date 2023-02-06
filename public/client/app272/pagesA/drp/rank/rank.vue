<template>
	<view class="drp-rank">
		<view class="header">
			<view class="img"><image :src="imagePath.rankBg" mode="widthFix"></image></view>
			<view class="tit">{{$t('lang.label_your_rank')}}{{drpRankData.rank}} {{$t('lang.ming')}}</view>
		</view>
		<view class="rank-box">
			<view class="title">
				<view class="t1">{{$t('lang.rank_ming')}}</view>
				<view class="t2">{{$t('lang.user')}}ID</view>
				<view class="t3">{{$t('lang.earn_by_sharing')}}</view>
			</view>
			<view class="list" v-for="(item,index) in drpRankData.list" :key="index" @click="teamHref(item.shop_id)">
				<view class="left">
					<view class="icon">
						<image src="../../../static/vip/rank1.png" v-if="item.rank===1"></image>
						<image src="../../../static/vip/rank2.png" v-if="item.rank===2"></image>
						<image src="../../../static/vip/rank3.png" v-if="item.rank===3"></image>
						<view class="text" v-if="item.rank>'3'">{{item.rank}}</view>
					</view>
				</view>
				<view class="con">
					<view class="picture">
						<image :src="item.user_picture" v-if="item.user_picture"></image>
						<image :src="imagePath.userDefaultImg" v-else></image>
					</view>
					<view class="name">{{item.user_name}}</view>
				</view>
				<view class="right">
					<view class="price">{{item.money}}</view>
					<uni-icons type="forward" size="20" color="#cccccc"></uni-icons>
				</view>
			</view>
		</view>
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	export default {
		data() {
			return {
			}
		},
		components:{
			uniIcons,
			dscNotContent,
			dscCommonNav,
		},
		onLoad(e) {
			
		},
		onShow(){
			this.load()
		},
		computed: {
			...mapState({
				drpRankData: state => state.drp.drpRankData,
			})
		},
		methods: {
			load(){
				this.$store.dispatch('setDrpRank')
			},
			teamHref(shop_id){
				uni.navigateTo({
					url:'/pagesA/drp/shop/shop?shop_id='+shop_id
				})
			}
		}
	}
</script>

<style scoped>
.drp-rank{ min-height: 100vh; padding-bottom: 20upx; }
.drp-rank .header{ position: relative; }
.drp-rank .header .img,
.drp-rank .header .img image{ width: 750upx; height: 368upx;}
.drp-rank .header .tit{ position: absolute; bottom: 25%; left: 40upx; font-size: 30upx; color: #FFFFFF;}
.drp-rank .rank-box{ position: relative; z-index: 2; margin: -80upx 20upx 0; background: #FFFFFF; border-radius: 20upx; box-shadow: 0 10upx 20upx 0 rgba(32, 33, 36, 0.1); padding: 20upx;}
.drp-rank .rank-box .title{ display: flex; flex-direction: row; justify-content: center; align-items: center; padding: 20upx 0; color: #666666;}
.drp-rank .rank-box .title .t1{ width: 20%; text-align: center;}
.drp-rank .rank-box .title .t2{ width: 50%; }
.drp-rank .rank-box .title .t3{ flex: 1 1 0%; text-align: center;}
.drp-rank .rank-box .list{ display: flex; flex-direction: row; padding: 20upx 0; border-bottom: 1px solid #f6f6f6;}
.drp-rank .rank-box .list:last-child{ border-bottom: 0;}
.drp-rank .rank-box .list .left{ width: 20%; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.drp-rank .rank-box .list .left .icon,
.drp-rank .rank-box .list .left .icon image{ width: 42upx; height: 42upx; }
.drp-rank .rank-box .list .left .text{ text-align: center; }

.drp-rank .rank-box .list .con{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; width: 50%; }
.drp-rank .rank-box .list .con .picture{ width: 60upx; height: 60upx; margin-right: 20upx; display: flex; flex-direction: row; align-items: center; }
.drp-rank .rank-box .list .con .picture image{ width: 100%; height: 100%; border-radius: 100%; overflow: hidden;}
.drp-rank .rank-box .list .con .name{ font-size: 30upx; flex: 1;}

.drp-rank .rank-box .list .right{flex: 1 1 0%; display: flex; flex-direction: row; justify-content: center; align-items: center; }
.drp-rank .rank-box .list .right .price{ font-size: 30upx; margin-right: 30upx; height: 40upx; line-height:35upx;}
</style>
