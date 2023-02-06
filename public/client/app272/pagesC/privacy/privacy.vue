<template>
	<view class="privacy">
		<uni-status-bar v-if="statusBar"></uni-status-bar>
		<view class="content">
			<view class="title">{{$t('lang.privacy_01')}}</view>
			<scroll-view scroll-y :style="{height:winHeight + 'px'}">
				<rich-text :nodes="articleDesc" @click="clickHref"></rich-text>
			</scroll-view>
		</view>
		<view class="btn-bar privacy-btn">
			<view class="btn btn-disabled" @click="no">{{$t('lang.disagree')}}</view>
			<view class="btn btn-red" @click="ok">{{$t('lang.agree')}}</view>
		</view>
		
		<uni-popup :show="show" type="middle" v-on:hidePopup="handelClose">
			<view class="icon"><icon class="iconfont icon-yanzheng"></icon></view>
			<view class="text">{{$t('lang.privacy_02')}}</view>
			<view class="bottom" @click="handelClose">{{$t('lang.privacy_03')}}</view>
		</uni-popup>
	</view>
</template>

<script>
	import { mapState } from 'vuex';
	import uniPopup from '@/components/uni-popup.vue';
	import uniStatusBar from '@/components/uni-status-bar.vue';
	
	export default {
		data() {
			return {
				winHeight: 0,
				show:false,
				statusBar:true
			}
		},
		components:{
			uniPopup,
			uniStatusBar
		},
		onLoad(){
			this.winHeight = uni.getSystemInfoSync().windowHeight - 200;
			
			this.load();
		},
		computed:{
			...mapState({
				articleDetail: state => state.article.articleDetail,
			}),
			articleDesc(){
				let result = this.articleDetail.content;
				// const reg = /style\s*=(['\"\s]?)[^'\"]*?\1/gi;
				// const regex = new RegExp('<img', 'gi');
				// const regex2 = new RegExp('<p', 'gi');
				// const regex3 = new RegExp('<a', 'gi');
				// if(result){
				// 	result = result.replace(reg, '');
				// 	result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
				// 	result = result.replace(regex2, '<p style="margin:0;padding:0;"');
				// 	//result = result.replace(regex3, '<a style="color:#0053f9;" title="privacy" href="https://www.baidu.com"');
				// }
				return result
			},
		},
		methods: {
			async load(){
				let roles = await this.$store.dispatch('setShopConfig',{type:true});
				let id = roles.data.privacy.article_id;
				this.$store.dispatch('setArticleDetail',{id:id});
			},
			async no(){
				// this.show = true;
				// let configData = await this.$store.dispatch('setShopConfig',{type:true});
				// let configPrivacy = configData.data.privacy; //文章id和版本号
				// uni.setStorageSync('privacy',JSON.stringify(configPrivacy));
				
				// #ifdef APP-PLUS  
				plus.runtime.quit();  
				// #endif
			},
			async ok(){
				let configData = await this.$store.dispatch('setShopConfig',{type:true});
				let configPrivacy = configData.data.privacy; //文章id和版本号
				uni.setStorageSync('privacy',JSON.stringify(configPrivacy));
				
				let roles = await this.$store.dispatch('setSplashAdPosition',{type:true});
				let splash = uni.getStorageSync('splash');
				
				if(roles.data.total == 0 && splash == ''){
					uni.reLaunch({url:"/pages/index/index"})
				}else{
					uni.redirectTo({url:"/pagesC/splash/splash"})
				}
			},
			handelClose(){
				this.show = false;
			},
			clickHref(){
				uni.navigateTo({
					url:'/pagesC/article/detail/detail?id=' + uni.getStorageSync('configData').privacy + '&show=false'
				})
			}
		}
	}
</script>

<style>
.privacy{ height: 100vh; background: #FFFFFF; padding: 0 30upx; overflow: hidden;}
.privacy .title{ height: 60upx; font-size: 40upx; color: #333; text-align: center; padding:30upx 0;}

.privacy .privacy-btn{ position: fixed; bottom: 100upx; left: 20upx; right: 20upx; padding-bottom: env(safe-area-inset-bottom);}
.privacy .privacy-btn .btn{ margin: 0 20upx; height:70upx; line-height:70upx; font-size:25upx; }

.privacy .uni-popup-middle{ height:auto; padding:0; text-align: center;}
.privacy .uni-popup-middle .icon{ font-size:80upx; color:#f92028; height: 80upx; line-height: 80upx;}
.privacy .uni-popup-middle .text{ padding:10upx 30upx 30upx; font-size: 25upx; text-align: center; color: #666666;}
.privacy .uni-popup-middle .bottom{ background:#f6f6f9; color:#f92028; width: 100%; text-align: center; font-size: 25upx; padding: 10upx 0; border-radius: 0 0 10upx 10upx;}
</style>
