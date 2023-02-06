<template>
	<view class="shop-list" :class="{'home-store-new': styleSel == 1}">
		<block v-if="styleSel == 0">
			<scroll-view class="scroll-view scroll-view-shop" scroll-x="true" scroll-left="0">
				<navigator :url="'/pages/shop/shopHome/shopHome?ru_id='+item.user_id" hover-class="none" class="scroll-view-item" v-for="(item,index) in storeList" :key="index">
					<view class="imgbox"><image :src="item.street_thumb" mode="widthFix" class="image"></image></view>
					<view class="icon"><image :src="item.logo_thumb" mode="widthFix" class="image"></image></view>
					<view class="info">
						<text class="h2">{{ item.rz_shop_name }}</text>
						<view class="txt">共计<text class="num">{{item.total}}</text> 件商品</view>
					</view>
				</navigator>
			</scroll-view>
		</block>
		<block v-else>
			<view class="floor-header-title" @click="$outerHref('/pages/integration/integration?type=1','app')">
				<view class="title">店铺推荐</view>
				<text class="text">{{spikeDesc}}</text>
				<view class="image"><image src="../../../static/more.png" class="img"></image></view>
			</view>
			<view class="floor-content">
				<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
					<view class="scroll-view-item" v-for="(item,index) in storeList" :key="index" @click="linkHref(item.user_id)">
						<view class="store-box">
							<view class="top"><image :src="item.street_thumb" mode="widthFix" class="img"></image></view>
							<view class="info">
								<view class="logo"><image :src="item.logo_thumb" mode="widthFix" class="img"></image></view>
								<view class="name onelist-hidden">{{ item.rz_shop_name }}</view>
								<view class="desc onelist-hidden">
									<view class="ms">{{$t('lang.sum_to')}} <text>{{ item.goods.length }}</text> {{$t('lang.goods_letter')}}</view>
								</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
			<view class="adv-list">
				<view class="item" v-for="(item,index) in module.list" :key="index">
					<view class="adv-img" @click="link(item)"><image :src="item.img" mode="widthFix" class="img"></image></view>
				</view>
			</view>
		</block>
	</view>
</template>

<script>
import universal from '@/common/mixins/universal.js';
export default{
	mixins:[universal],
	props: ['module', 'preview'],
	data(){
		return {
			storeList: []
		}
	},
	created() {
		uni.request({
			url: this.websiteUrl + '/api/visual/store',
			method: 'POST',
			data: {
				number: this.nNumber < 1 ? 1 : this.nNumber
			},
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				let data = res.data.data

				if (data || data.length > 0) {
				    this.storeList = data
				}else{
				    this.storeList = []
				}
			},
			fail: (err) => {
				console.error(err)
			}
		})
    },
	methods: {
		linkHref(id){
			uni.navigateTo({
				url:'/pages/shop/shopHome/shopHome?ru_id=' + id
			})
		},
		link(item){
			// #ifdef APP-PLUS
			let page = item.appPage ? item.appPage : item.url
			let built = item.appPage ? 'app' : 'undefined'
			// #endif
			
			// #ifdef MP-WEIXIN
			let page = item.appletPage ? item.appletPage : item.url
			let built = item.appletPage ? 'app' : 'undefined'
			// #endif
			
			this.$outerHref(page,built)
		},
    },
    computed: {
		styleSel(){
			return this.module.isStyleSel
		},
        nNumber() {
            return this.module.allValue.number
        },
		spikeDesc(){
			return this.module.allValue.spikeDesc ? this.module.allValue.spikeDesc : '拼着买更实惠'
		}
    }
}
</script>

<style>
.shop-list{ position: relative; margin-top: 20upx;}
.scroll-view-shop{ white-space: nowrap; width: 100%;}
.scroll-view-shop .scroll-view-item{ position:relative; display: inline-block;margin-left: 15upx; width: 402upx; height: 366upx; border-radius: 10upx; background: #ffffff; overflow: hidden;}
.scroll-view-shop .scroll-view-item .imgbox{ width: 100%; height: 200upx; overflow: hidden;}
.scroll-view-shop .scroll-view-item .imgbox .image{ width: 100%;}
.scroll-view-shop .scroll-view-item .icon{ width:100upx; height: 100upx; overflow: hidden; position: absolute; top: 150upx; left: 150upx; z-index: 10;}
.scroll-view-shop .scroll-view-item .icon .image{ width: 100%; height: 100%; border-radius: 50%; box-shadow: 0 1px 6px #ccc; }
.scroll-view-shop .scroll-view-item .info{ width: 100%; height: 166upx; position: absolute; bottom: 0; left: 0; background: #FFFFFF; z-index: 9; text-align: center; box-sizing: border-box; padding: 60upx 20upx 0; line-height: 1.5;}
.scroll-view-shop .scroll-view-item .h2{ display: block; width: 100%; text-align: center; }
.scroll-view-shop .scroll-view-item .txt{ display: block; width: 100%; text-align: center; color: #999999;}
.scroll-view-shop .scroll-view-item .num{ color: #000000; }

/*新版店铺*/
.home-store-new{
	background-color: #fff;
	margin: 20upx 20upx 0;
	border-radius: 20upx;
}
.floor-header-title{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 30upx 20upx;
}
.floor-header-title .title{
    font-size: 36upx;
    color: #000;
    font-weight: 600;
	margin-right: 20upx;
}
.floor-header-title .text{
    color: #888;
    font-size: 30upx;
	margin-right: 20upx;
}
.floor-header-title .image{
	width: 30upx;
	height: 30upx;
	line-height: 30upx;
}
.floor-header-title .image .img{
	width: 100%;
	height: 100%;
}
.floor-content{
    padding: 0 0 0 20upx;
}
.home-store-new .scroll-view{ font-size: 0;}
.home-store-new .scroll-view-item{ display: inline-block; width: 31%; margin-right: 2.5%; border-radius: 20upx; overflow: hidden;}
.home-store-new .scroll-view-item .store-box{ background-color: #f6f6f6; }
.home-store-new .scroll-view-item .store-box .top{ width: 100%; height: 170upx; overflow: hidden;}
.home-store-new .scroll-view-item .store-box .top .img{ height: 100%; }
.home-store-new .scroll-view-item .store-box .info{ position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 50upx 20upx 20upx;}
.home-store-new .scroll-view-item .store-box .info .logo{ width: 70upx; height: 70upx; border-radius: 50%; border:1px solid #fff; position: absolute; top: -35upx;}
.home-store-new .scroll-view-item .store-box .info .logo .img{ border-radius: 50%; }
.home-store-new .scroll-view-item .store-box .info .name{ font-size: 30upx; color: #333; line-height: 1.5; width: 100%; display: block; text-align: center;}
.home-store-new .scroll-view-item .store-box .info .desc{ color: #888; font-size: 25upx; margin-top:5upx; line-height: 1.5; }
.home-store-new .scroll-view-item .store-box .info .desc .ms{ line-height: 1.5; font-size: 25upx;}
.home-store-new .scroll-view-item .store-box .info .desc .text{ color: #333; margin: 0 10upx; }

.home-store-new .adv-list{ padding: 20upx; display: flex; flex-direction: row; justify-content: space-between; flex-wrap: wrap;}
.home-store-new .adv-list .item{ border-radius: 20upx; width: calc(50% - 10upx);}
.home-store-new .adv-list .item .adv-img{ line-height: 0;}
</style>
