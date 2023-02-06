<template>
	<view class="uni-page-body" v-if='ru_id'>
		<dsc-search :shopId="ru_id"></dsc-search>
		<dsc-shop-signs :shopId="ru_id"></dsc-shop-signs>
		<dsc-line></dsc-line>
		<block v-for="(item,index) in modulesShop" :key="index">
			<dsc-slide v-if="item.module == 'slide'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-slide>
			<dsc-title v-if="item.module == 'title'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-title>
			<dsc-announcement v-if="item.module == 'announcement'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-announcement>
			<dsc-nav v-if="item.module == 'nav'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-nav>
			<dsc-line v-if="item.module == 'line'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-line>
			<dsc-blank v-if="item.module == 'blank'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-blank>
			<dsc-jigsaw v-if="item.module == 'jigsaw'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-jigsaw>
			<dsc-product v-if="item.module == 'product'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-product>
			<dsc-coupon v-if="item.module == 'coupon'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-coupon>
			<dsc-count-down v-if="item.module == 'count-down'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-count-down>
			<dsc-store v-if="item.module == 'store'" :module="item.data" :preview="false" :modules-index="index" :shopId="ru_id"></dsc-store>
		</block>
		<dsc-shop-menu :shopId="ru_id"></dsc-shop-menu>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		
		<dscCommonNav></dscCommonNav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniNavBar from '@/components/uni-nav-bar.vue'
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	import uniCountdown from "@/components/uni-countdown.vue"
	import uniPopup from '@/components/uni-popup.vue'
	
	// custom components
	import dscSlide from '@/components/visualization/slide/Frontend'
	import dscTitle from '@/components/visualization/title/Frontend'
	import dscAnnouncement from '@/components/visualization/announcement/Frontend'
	import dscNav from '@/components/visualization/nav/Frontend'
	import dscLine from '@/components/visualization/line/Frontend'
	import dscBlank from '@/components/visualization/blank/Frontend'
	import dscJigsaw from '@/components/visualization/jigsaw/Frontend'
	import dscProduct from '@/components/visualization/product/Frontend'
	import dscCoupon from '@/components/visualization/coupon/Frontend'
	import dscCountDown from '@/components/visualization/count-down/Frontend'
	import dscButton from '@/components/visualization/button/Frontend'
	import dscSearch from '@/components/visualization/search/Frontend'
	import dscStore from '@/components/visualization/store/Frontend'
	import dscShopSigns from '@/components/visualization/shop-signs/Frontend'
	import dscShopMenu from '@/components/visualization/shop-menu/Frontend'
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		components: {
			uniNavBar,
			uniIcons,
			uniCountdown,
			uniPopup,
			dscSlide,
			dscTitle,
			dscAnnouncement,
			dscNav,
			dscLine,
			dscBlank,
			dscJigsaw,
			dscProduct,
			dscCoupon,
			dscCountDown,
			dscButton,
			dscSearch,
			dscStore,
			dscShopSigns,
			dscShopMenu,
			dscCommonNav,
		},
		data() {
			return {
				dscLoading:true,
				ru_id:''
			}
		},
		computed: {
		    ...mapState({
				shopInfo: state => state.shop.shopInfo,
		    }),
			modulesShop: {
				get() {
					return this.$store.state.shop.modulesShop
				},
				set(val) {
					this.$store.state.shop.modulesShop = val
				}
			},
		},
		onShareAppMessage(res){
			let share_title = this.shopInfo.rz_shop_name ? this.shopInfo.rz_shop_name : this.$store.state.common.shopConfig.shop_title; 
			return {
			  title: share_title,
			  path: '/pages/shop/shopHome/shopHome?ru_id=' + this.ru_id
			}
		},
		//用户点击右上角分享朋友圈
		onShareTimeline: function() {
			return {
		      title: this.shopInfo.rz_shop_name,
		      query: {
		        key: ""
		      },
			  imageUrl: this.shopInfo.logo_thumb,
		    }
		},
		onLoad(e) {
			this.ru_id = e.ru_id ? e.ru_id : 0;
			
			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				this.ru_id = scene.split('.')[0];
			}
			
			
			//#ifdef MP-WEIXIN
			uni.showShareMenu({
			  withShareTicket: true,
			  menus: ['shareAppMessage', 'shareTimeline']
			})
			//#endif
			
			let referer = uni.getStorageSync('platform').toLowerCase()
			if(uni.getStorageSync('platform') == 'MP-WEIXIN'){
				referer = 'wxapp'
			}
			
			//获取可视化信息
			this.$store.dispatch('setHome',{
				ru_id:this.ru_id,
				type:'store',
				device:referer
			})
		},
		onShow(e) {
			//this.modulesShop = []
		},
		onPullDownRefresh(){
			uni.showLoading({title: this.$t('lang.loading')});
			this.modulesShop = []
			
			this.initData();
		},
		methods: {
			initData(){
				setTimeout(() => {
					this.$store.dispatch('setHome',{
						ru_id:this.ru_id,
						type:'store'
					});
					
					uni.stopPullDownRefresh();
				}, 300);
			},
			back() {
				uni.navigateBack({
					delta: 1
				})
			},
		},
		watch:{
			modulesShop(){
				this.dscLoading = false
			},
			shopInfo(){
				uni.setNavigationBarTitle({
					title: this.shopInfo.rz_shop_name
				});
			}
		}
	}
</script>

<style>
	.uni-page-body{ padding-bottom: 100upx;}
	.uni-page-body .uni-navbar-header .uni-navbar-header-btns:first-child{ width: 60upx; justify-content: center; padding-left: 12upx;}
	.uni-page-body .more-link{ display: flex; flex-direction: row; align-items: center; font-size: 25upx; }
	.uni-page-body .more-link .txt{ padding: 0 10upx 0 20upx; border-left: 1px solid #e3e8ee; line-height: 1.2; margin-left: 10upx;}
	.uni-page-body .more-link .uni-icon{ line-height: 1.8;}
	.uni-page-body .uni-popup-middle{ padding: 0; width: 80%; height: auto; background: none;}
	
	.uni-page-body .input-view .uni-icon{ display: block; }
</style>
