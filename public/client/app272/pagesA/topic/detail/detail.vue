<template>
	<view class="uni-page-body tabbar-padding-bottom">
		<block v-for="(item,index) in modules" :key="index">
			<dsc-search v-if="item.module == 'search'" :module="item.data" :preview="false" :modules-index="index"></dsc-search>
			<dsc-slide v-if="item.module == 'slide'" :module="item.data" :preview="false" :modules-index="index"></dsc-slide>
			<dsc-title v-if="item.module == 'title'" :module="item.data" :preview="false" :modules-index="index"></dsc-title>
			<dsc-announcement v-if="item.module == 'announcement'" :module="item.data" :preview="false" :modules-index="index"></dsc-announcement>
			<dsc-nav v-if="item.module == 'nav'" :module="item.data" :preview="false" :modules-index="index"></dsc-nav>
			<dsc-line v-if="item.module == 'line'" :module="item.data" :preview="false" :modules-index="index"></dsc-line>
			<dsc-blank v-if="item.module == 'blank'" :module="item.data" :preview="false" :modules-index="index"></dsc-blank>
			<dsc-jigsaw v-if="item.module == 'jigsaw'" :module="item.data" :preview="false" :modules-index="index"></dsc-jigsaw>
			<dsc-product v-if="item.module == 'product'" :module="item.data" :preview="false" :modules-index="index"></dsc-product>
			<dsc-coupon v-if="item.module == 'coupon'" :module="item.data" :preview="false" :modules-index="index"></dsc-coupon>
			<dsc-count-down v-if="item.module == 'count-down'" :module="item.data" :preview="false" :modules-index="index"></dsc-count-down>
			<dsc-store v-if="item.module == 'store'" :module="item.data" :preview="false" :modules-index="index"></dsc-store>
		</block>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
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
		},
		data() {
			return {
				dscLoading:true,
				curpage:'',
				topicId: 0,
				modules:''
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesA/topic/detail/detail?id=' + this.topicId
			}
		},
		onLoad(e) {
			this.topicId = e.id;
		},
		onShow(e) {
			let pages = getCurrentPages()
			this.curpage = pages[pages.length - 1].route

			this.loadTopic()
		},
		onPullDownRefresh(){
			uni.showLoading({title: this.$t('lang.loading')});
			this.modules = []
			this.initData();
		},
		methods: {
			initData(){
				setTimeout(() => {
					this.loadTopic();
					uni.stopPullDownRefresh();
				}, 300);
			},
			loadTopic(){
				let referer = uni.getStorageSync('platform').toLowerCase()
				if(uni.getStorageSync('platform') == 'MP-WEIXIN'){
					referer = 'wxapp'
				}

				uni.request({
					url:this.websiteUrl + '/api/visual/view',
					method:'POST',
					data:{
						id: this.topicId,
					    type: 'topic',
						device: referer
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let m = res.data.data.data
						if(m){
							this.modules = JSON.parse(res.data.data.data)
							
							uni.setNavigationBarTitle({ title: res.data.data.title });
						}else{
							this.modules = []
						}
						uni.hideLoading()
					}
				})
			},
			back() {
				uni.navigateBack({
					delta: 1
				})
			},
		},
		watch:{
			modules(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
	.uni-page-body .uni-navbar-header .uni-navbar-header-btns:first-child{ width: 60upx; justify-content: center; padding-left: 12upx;}
	.uni-page-body .more-link{ display: flex; flex-direction: row; align-items: center; font-size: 25upx; }
	.uni-page-body .more-link .txt{ padding: 0 10upx 0 20upx; border-left: 1px solid #e3e8ee; line-height: 1.2; margin-left: 10upx;}
	.uni-page-body .more-link .uni-icon{ line-height: 1.8;}
	.uni-page-body .uni-popup-middle{ padding: 0; width: 80%; height: auto; background: none;}

	.uni-page-body .input-view .uni-icon{ display: block; }
</style>
