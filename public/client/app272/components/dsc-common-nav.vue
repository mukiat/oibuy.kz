<template>
	<view>
		<view class="commom-nav" :class="{'show':show}">
			<view class="left" @click="handelNav">
				<view class="nav-icon">
					<block v-if="!show">
					<view class="iconfont icon-left-arrow"></view>
					</block>
					<block v-else>
					<view class="iconfont icon-right-arrow"></view>
					</block>
					<text class="text">{{$t('lang.quick_navigation')}}</text>
				</view>
			</view>
			<view class="right">
				<view class="nav-cont">
					<view class="nav-item" @click="navigaterNav('home')">
						<view class="iconfont icon-home"></view>
						<text class="text">{{$t('lang.home')}}</text>
					</view>
					<view class="nav-item" @click="navigaterNav('search')">
						<view class="iconfont icon-search-alt"></view>
						<text class="text">{{$t('lang.search')}}</text>
					</view>
					<view class="nav-item" @click="navigaterNav('category')">
						<view class="iconfont icon-category-alt"></view>
						<text class="text">{{$t('lang.category')}}</text>
					</view>
					<view class="nav-item" @click="navigaterNav('cart')">
						<view class="iconfont icon-cart-alt"></view>
						<text class="text">{{$t('lang.cart')}}</text>
					</view>
					<view class="nav-item" @click="navigaterNav('user')">
						<view class="iconfont icon-user-portrait"></view>
						<text class="text">{{$t('lang.my')}}</text>
					</view>
					<slot name="right"></slot>
				</view>
			</view>
		</view>
		<view class="common-show" :class="{'show':show}" @click="handelShow"></view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show:false
			};
		},
		methods:{
			handelNav(){
				this.show = this.show ? false : true;
			},
			handelShow(){
				this.show = false
			},
			navigaterNav(val){
				if(val == 'home'){
					uni.switchTab({
						url:'/pages/index/index',
					})
				}else if(val == 'search'){
					uni.navigateTo({
						url:'/pages/search/search',
					})
				}else if(val == 'category'){
					uni.switchTab({
						url:'/pages/category/category',
					})
				}else if(val == 'cart'){
					uni.switchTab({
						url:'/pages/cart/cart',
					})
				}else if(val == 'user'){
					uni.switchTab({
						url:'/pages/user/user',
					})
				}
			}
		}
	}
</script>

<style>
.commom-nav{ position: fixed; top: 66%; width: 82%; right: -68%; z-index: 999; display: flex; transition:all .5s;}
.commom-nav .left{ display: flex; justify-content: center; align-items: center;}
.commom-nav .left .nav-icon{ display: flex; flex-direction: row; justify-content: center; align-items: center; color: #FFFFFF; width: 100upx; background: rgba(0,0,0,.7); padding:15upx 5upx; border-radius: 10upx 0 0 10upx;}
.commom-nav .left .nav-icon .iconfont{ flex: 1; margin: 0 5upx; font-size: 14px;}
.commom-nav .left .nav-icon .text{ line-height: 1.5; font-size: 25upx;}
.commom-nav .right{ background: #FFFFFF; border-radius: 10upx 0 0 10upx; padding: 20upx; width: 100%;}
.commom-nav .right .nav-cont{ display: flex; flex-direction: row; flex-wrap: wrap;}
.commom-nav .right .nav-cont .nav-item{ width: 25%; display: flex; flex-direction: column; justify-content: center; align-items: center; color: #666666; padding: 10upx 0 15upx;}
.commom-nav .right .nav-cont .nav-item .iconfont{ font-size: 18px; line-height: 1.2; margin-bottom: 5upx;}
.commom-nav .right .nav-cont .nav-item .text{ font-size: 25upx;}
.common-show{ position: fixed; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,.4); z-index: 11; display: none;}

.commom-nav.show{ right: 0;}
.common-show.show{ display: block;}
</style>
