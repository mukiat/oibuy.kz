<template>
	<view class="has_nav_container">
		<block v-if="controlVersion">
			<dsc-nav-bar :nav-index="navIndex" :list="list" @change-index="changeIndex" v-if="list.length > 0"></dsc-nav-bar>
			<view class="main">
				<swiper class="dsc_swiper" :duration="300" :current="navIndex" :disable-touch="true" @change="change" @transition="transitionHandle">
					<swiper-item v-for="(item,index) in list" :key="index">
						<dsc-community v-if="item.nav_key == 'community'" />
						<shop v-if="item.nav_key == 'shop'" />
						<videoList :paly="paly" v-if="item.nav_key == 'video'" />
						<!-- #ifdef MP-WEIXIN -->
						<dsc-live v-if="item.nav_key == 'live'" />
						<!-- #endif -->
					</swiper-item>
				</swiper>
			</view>
		</block>
		<block v-else>
			<search></search>
		</block>
	</view>
</template>

<script>
	import navBar from '@/components/dsc-nav-bar/nav-bar.vue';
	import community from '@/components/dsc-community/community.vue';
	import dscLive from '@/components/dsc-live/DscLive.vue';
	import shop from '@/pages/shop/shop.vue';
	import videoList from '@/pages/video/list.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import search from '@/pages/search/search.vue';
	
	export default {
		data() {
			return {
				navIndex:0,
				list:[],
				LatelyKeyword:[],
				navTranslateX: 0,
				paly:false
			}
		},
		components:{
			'dsc-nav-bar': navBar,
			'dsc-community': community,
			'dsc-live': dscLive,
			shop,
			videoList,
			dscNotContent,
			search
		},
		methods: {
			// 点击导航改变索引 
			changeIndex(i) {
				this.navIndex = i;
				//全局变量integration赋值
				getApp().globalData.integration = i;
			},
			onColse(){
				this.LatelyKeyword = [];
			},
			change(e){
				let index = e.target.current;
				this.navIndex = index;
			},
			transitionHandle(e) {
				this.navTranslateX = e.detail.dx;
			},
			onTabList(){
				let referer = uni.getStorageSync('platform').toLowerCase()
				if (uni.getStorageSync('platform') == 'MP-WEIXIN') {
					referer = 'wxapp'
				}
				
				uni.request({
					url:this.websiteUrl + '/api/shop/page-nav',
					method:'GET',
					data: {
						device:referer
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token')
					},
					success: (res) => {
						let data = res.data;
						if(data.status == "success"){
							this.list = data.data
						}
					}
				});
			}
		},
		onLoad(e) {
			this.navIndex = e.type ? e.type : getApp().globalData.integration;
		},
		onShow() {
			this.LatelyKeyword = uni.getStorageSync('LatelyKeyword') ? uni.getStorageSync('LatelyKeyword') : [];
			this.navIndex = getApp().globalData.integration;
			
			if(this.navIndex == 2){
				this.paly = !this.paly
			}
			
			if(this.controlVersion){
				this.onTabList();
			}else{
				this.list = []
			}
		},
		onHide(){
			if(this.navIndex == 2 && this.controlVersion){
				this.paly = true;
			}
		}
	}
</script>

<style scoped>
.has_nav_container {
	height: 100%;
}
.main {
	height: calc(100% - 100upx);
}
.dsc_swiper {
	height: 100%;
}
</style>
