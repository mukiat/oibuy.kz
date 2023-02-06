<template>
    <view class="shop-signs" v-if="shopInfo">
		<view class="header">
			<image class="bg-img" :src="shopInfo.street_thumb" mode="widthFix" alt=""></image>
			<view class="shop-signs-mask"></view>
		</view>
		<view class="info-nums">
			<view class="info-head">
				<view class="icon"><image :src="shopInfo.logo_thumb" class="img"></image></view>
				<text v-if="shopInfo.like_num">已有 {{shopInfo.like_num}} 人关注</text>
			</view>
			<view class="info-items">
				<view class="item" @click="$outerHref('/pages/shop/shopGoods/shopGoods?ru_id='+shopId,'app')">全部：<text class="text">{{ shopInfo.total }}</text></view>
				<view class="item" @click="$outerHref('/pages/shop/shopGoods/shopGoods?ru_id='+shopId+'&type=store_new','app')">新品：<text class="text">{{ shopInfo.new }}</text></view>
				<view class="item" @click="$outerHref('/pages/shop/shopGoods/shopGoods?ru_id='+shopId+'&type=is_promote','app')">促销：<text class="text">{{ shopInfo.promote }}</text></view>
			</view>
		</view>
	</view>
</template>

<script>
import { mapState } from 'vuex'

export default{
	props: ['shopId'],
	data(){
		return {
			couponShow: false,
			couponInfo: {}
		}
	},
	created(){
		let that = this
		that.$store.dispatch('setVisualStorein',{
			ru_id: that.shopId
		})
	},
	computed: {
	    ...mapState({
			shopInfo: state => state.shop.shopInfo,
	    })
	},
	methods:{
		
	}
}
</script>

<style>
	@keyframes animatedBird {
	    0% {
	        top: 0;
	    }
	    50% {
	        top: -100%;
	    }
	    100% {
	        top: 0%;
	    }
	}
	
	.shop-signs .header{ width: 100%; height: 250upx; overflow: hidden; position: relative; }
	.shop-signs .header .shop-signs-mask{ background: rgba(0,0,0,.4); position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1; }
	.shop-signs .header .bg-img{ width: 100%; height: auto; position: absolute; animation: animatedBird 20s infinite linear; }
	.shop-signs .info-nums{ background: #FFFFFF; position: relative; z-index: 9; }
	.shop-signs .info-nums .info-head{ text-align: center; padding-top: 80upx;}
	.shop-signs .info-nums .info-head .icon{ position: absolute; width: 120upx; height: 120upx; background: #FFFFFF; border-radius: 50%; box-shadow: 1px 1px 10px rgba(0,0,0,.2); overflow: hidden; top: -60upx; left: 50%; margin-left: -60upx;}
	.shop-signs .info-nums .info-head .icon .img{ border-radius: 50%; position: relative; left: 0; }
	.shop-signs .info-nums .info-items{ display: flex; justify-content: center; align-items: center; padding: 20upx 0; color: #888888;}
	.shop-signs .info-nums .info-items .item{ width: 33.3%; display: flex; justify-content: center; align-items: center;}
	.shop-signs .info-nums .info-items .item .text{ color: #333333; }
</style>
