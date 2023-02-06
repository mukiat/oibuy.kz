<template>
		<scroll-view class="uni-page-body" scroll-y :scroll-top="scrollTop" :lower-threshold="100" @scrolltolower="loadMore" :class="{'page-top-category-body': topCategoryCatid && topCategoryCatid > 0}" @scroll="onScroll">
			<block v-for="(item,index) in modules" :key="index">
				<dsc-search ref="jump" v-if="item.module == 'search' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id" :scrollFixed="scrollFixed" :isUnread="isUnread" :fristBackgroundColor="fristBackgroundColor"></dsc-search>
				<dsc-slide v-if="item.module == 'slide' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-slide>
				<dsc-title v-if="item.module == 'title' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-title>
				<dsc-announcement v-if="item.module == 'announcement' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id"></dsc-announcement>
				<dsc-nav v-if="item.module == 'nav' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-nav>
				<dsc-line v-if="item.module == 'line' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-line>
				<dsc-blank v-if="item.module == 'blank' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-blank>
				<dsc-jigsaw v-if="item.module == 'jigsaw' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-jigsaw>
				<dsc-product v-if="item.module == 'product' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id" :userId="userId"></dsc-product>
				<dsc-coupon v-if="item.module == 'coupon' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-coupon>
				 
				<dsc-count-down v-if="item.module == 'count-down' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id"></dsc-count-down>
				<dsc-store v-if="item.module == 'store' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-store>
				
				<!-- #ifdef MP-WEIXIN -->
				<dsc-live v-if="item.module == 'live' && item.isShow" :module="item.data" :preview="false" :modules-index="index"
				 :shopId="ru_id"></dsc-live>
				<!-- #endif -->
				
				<!--2020新增可视化组件-->
				<dsc-category-nav ref="jump" v-if="item.module == 'category-nav' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id" :scrollFixed="scrollFixed" :fristBackgroundColor="fristBackgroundColor" @send="clickCategoryNav"></dsc-category-nav>
				<dsc-visual-team v-if="item.module == 'visual-team' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id"></dsc-visual-team>
				<dsc-visual-adv v-if="item.module == 'visual-adv' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id"></dsc-visual-adv>
				<dsc-product-pick v-if="item.module == 'product-pick' && item.isShow" :module="item.data" :preview="false"
				 :modules-index="index" :shopId="ru_id" :scrollPickOpen="scrollPickOpen" :userId="userId" @updateScrollPickOpen="updateScrollPickOpen"></dsc-product-pick>
			</block>

			<!--顶级分类页-->
			<dsc-top-catalog v-if="topCategoryCatid && topCategoryCatid > 0" :topCategoryCatid="topCategoryCatid" :jumpHeight="jumpHeight"></dsc-top-catalog>

			<dsc-loading :dscLoading="dscLoading"></dsc-loading>

			<uni-popup :show="bonusShow" v-on:hidePopup="handelClose('bonus')">
				<view class="close" @click="handelClose('bonus')">
					<image src="../../static/close.png" mode="widthFix" class="img"></image>
				</view>
				<image :src="bonusData.popup_ads" class="img" mode="widthFix" v-if="bonusData && bonusData.popup_ads"></image>
			</uni-popup>

			<uni-popup :show="versionShow" v-on:hidePopup="handelClose('version')" v-if="versionShow">
				<view class="version-popup">
					<view class="version-text">
						<view class="tit">{{$t('lang.new_version_found')}}</view>
						<view class="code">{{ versionData.content.version_id }}</view>
					</view>
					<view class="version-notic">{{$t('lang.new_version_found_tips')}}</view>
					<view class="version-btn">
						<view class="btn" @click="updateAppStore">{{$t('lang.upgrade_now')}}</view>
					</view>
					<view class="close" @click="handelClose('version')">
						<image src="../../static/close.png" mode="widthFix" class="img"></image>
					</view>
					<image :src="imagePath.upgrade" class="upgrade" mode="widthFix"></image>
				</view>
			</uni-popup>

			<uni-popup :show="customerShow" v-on:hidePopup="handelClose('customer')" v-if="customerShow">
				<view class="version-popup">
					<view class="close-on" @click="handelClose('customer')">
						<image src="../../static/close.png" mode="widthFix" class="img"></image>
					</view>
					<image :src="imgShare" class="upgrade" mode="widthFix"></image>
					<view class="save-picture" @click="operator">{{$t('lang.save_picture')}}</view>
				</view>
			</uni-popup>

			<uni-popup :show="mpShareShow" type="bottom" v-on:hidePopup="handelClose('mpshare')">
				<view class="mp-share-warp">
					<view class="lists">
						<view class="list" @click="onGoodsShare">
							<image src="../../static/service/picture.png" mode="widthFix"></image>
							<text>{{$t('lang.generate_sharing_poster')}}</text>
						</view>
						<button class="list" open-type="share">
							<image src="../../static/service/wx.png" mode="widthFix"></image>
							<text>{{$t('lang.share_with_friends')}}</text>
						</button>
					</view>
					<view class="title_on" @click="handelClose('mpshare')">{{$t('lang.cancel')}}</view>
				</view>
			</uni-popup>

			<block v-if="controlVersion">
				<service @flaghanlde="prentflaghanlde" v-if="shopConfig && shopConfig.consult.consult_set_state==1" :shopConfig="shopConfig.consult"></service>
			</block>
			
			<block v-if="click_add">
				<view class="add_new" :style="{'top':CustomBar + 10 +'px'}">
					<view class="add_on">{{$t('lang.remind')}}<text @click="clickon">{{$t('lang.remind1')}}</text></view>
				</view>
			</block>
			
			<!--返回顶部-->
			<dsc-filter-top :scrollState="scrollState" :isScrollView="true" @fileterScrollTop="fileterScrollTop"></dsc-filter-top>

			<!-- <tabbar :curpage="curpage"></tabbar> -->
		</scroll-view>
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
	import dscLive from '@/components/visualization/live/Frontend'
	import dscCategoryNav from '@/components/visualization/category-nav/Frontend.vue'
	import dscVisualTeam from '@/components/visualization/visual-team/Frontend.vue'
	import dscVisualAdv from '@/components/visualization/visual-adv/Frontend.vue'
	import dscProductPick from '@/components/visualization/product-pick/Frontend'
	import service from "@/components/customer-service/customer-service.vue";
	import tabbar from "@/components/tabbar/tabbar.vue";
	import universal from '@/common/mixins/universal.js';

	//新增顶级分类组件
	import dscTopCatalog from '@/components/topcatalog/topcatalog'
	
	//返回顶部
	import dscFilterTop from '@/components/dsc-filter-top'

	export default {
		mixins: [universal],
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
			dscLive,
			dscCategoryNav,
			dscVisualTeam,
			dscVisualAdv,
			dscTopCatalog,
			dscProductPick,
			service,
			tabbar,
			dscFilterTop
		},
		data() {
			return {
				bonusShow: false,
				dscLoading: true,
				curpage: '',
				ru_id: 0,
				versionShow: false,
				mpShareShow: false,
				customerShow: false,
				shopConfig: uni.getStorageSync('configData'),
				imgShare: '',
				versionData: '',
				jumpHeight: 0,
				scrollPickOpen: false,
				scrollFixed: false,
				scrollState: false,
				click_add: false,
				liststatus:[],
				scrollTop:0,
				oldScrollTop:0,
				CustomBar:0,
				HeaderBar:0,
				userId:0
			}
		},
		computed: {
			modules: {
				get() {
					return this.$store.state.shop.modules
				},
				set(val) {
					this.$store.state.shop.modules = val
				}
			},
			bonusData() {
				return this.shopConfig.bonus_ad
			},
			topCategoryCatid() {
				return this.$store.state.topCategoryCatid
			},
			isUnread(){
				let i = 0
				this.liststatus.forEach((res)=>{
					if(res.unread){
						i++
					}
				})
				
				return i > 0 ? true : false
			},
			fristBackgroundColor(){
				let slidemodule = this.modules.find((item,index)=>{
					return item.module == "slide" && item.data.isStyleSel == '2'
				})
				return slidemodule && slidemodule.data.list.length > 0 ? slidemodule.data.list[0].bgColor : '#f34646'
			},
		},
		onShareAppMessage(res) {
			let user_id = uni.getStorageSync('user_id');
			let url = '/pages/index/index';
			if (user_id) {
				url = '/pages/index/index?parent_id=' + user_id
			}

			return {
				title: this.$store.state.common.shopConfig.shop_title,
				path: '/pages/index/index'
			}
		},
		//用户点击右上角分享朋友圈
		onShareTimeline: function() {
			return {
				title: this.$store.state.common.shopConfig.shop_title,
				query: {
					key: ""
				},
			}
		},
		onLoad(e) {
			let parent_id = e.parent_id;
			let user_id = uni.getStorageSync('user_id');

			if (parent_id) {
				if (parent_id != user_id) {
					uni.setStorageSync('parent_id', parent_id)
				}
			}

			// #ifdef APP-PLUS
			this.appUpdate();
			
			//读取本地缓存
			if(!uni.getStorageSync('userRegion') && e.page && e.page == 'launch'){
				this.$store.dispatch('getLocation')
			}
			// #endif

			// #ifdef MP-WEIXIN
			uni.showShareMenu({
				withShareTicket: true,
				menus: ['shareAppMessage', 'shareTimeline']
			})

			if (this.bonusData && this.bonusData.open == 1) {
				this.bonusShow = uni.getStorageSync('bonusShow') !== '' ? uni.getStorageSync('bonusShow') : true;
			}

			if (uni.getStorageSync('program')) {
				this.click_add = false
			} else {
				this.click_add = true
				uni.setStorageSync('program', 1)
			}
			
			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				let parent_id = scene.split('.')[0];
				uni.setStorageSync('parent_id',parent_id);
			}
			
			/* 获取小程序状态栏高度 */
			uni.getSystemInfo({  
			    success: e => {  
			        let StatusBar = e.statusBarHeight;  
			        let rect = wx.getMenuButtonBoundingClientRect();  
					
			        if (e.system.toLowerCase().indexOf('ios') > -1) {  
			            //IOS  
			            this.CustomBar = rect.bottom + (rect.top - e.statusBarHeight) * 2;  
			            this.HeaderBar = this.CustomBar - e.statusBarHeight;  
			        } else {  
			            //安卓  
			            this.HeaderBar = rect.height + (rect.top - e.statusBarHeight) * 2;  
			            this.CustomBar = this.HeaderBar + e.statusBarHeight;  
			        }
			    }  
			});
			// #endif
			
			//首页数据
			this.moduleLoad();
		},
		onShow() {
			let pages = getCurrentPages()
			this.curpage = pages[pages.length - 1].route;
			if(!uni.getStorageSync('configData')){				
				this.modules=[];
				uni.showLoading({
					title: this.$t('lang.loading')
				});
				setTimeout(() => {
					this.moduleLoad();
					uni.stopPullDownRefresh();
				}, 50);
			}

			//消息提示
			this.messageList();
			
			//获取shopConfig
			this.$store.dispatch('setShopConfig');
			this.userId = uni.getStorageSync('user_id') || 0;
			
		},
		onPullDownRefresh() {
			uni.showLoading({
				title: this.$t('lang.loading')
			});
			this.initData();
		},
		methods: {
			async moduleLoad() {
				let referer = uni.getStorageSync('platform').toLowerCase()
				if (uni.getStorageSync('platform') == 'MP-WEIXIN') {
					referer = 'wxapp'
				}

				this.$store.dispatch('setHome', {
					type: 'home',
					device: referer
				});
			},
			operator() {
				let that = this
				uni.downloadFile({
					url:that.imgShare,
					success: function(res) {
						if (res.statusCode === 200){
							uni.saveImageToPhotosAlbum({
								filePath: res.tempFilePath,
								success: function(file) {
									uni.showToast({
										title: that.$t('lang.picture_saved_success'),
										icon: 'none',
										duration: 2200
									});
								},
								fail: function() {
									uni.showModal({
										content:'检测到您没打开获取信息功能权限，是否去设置打开？',
										confirmText: "确认",
										cancelText:'取消',
										success: (res) => {
											if(res.confirm){
												uni.openSetting({
													success: (res) => {
														uni.showToast({
															title: "请重新点击分享保存图片～",
															icon: "none"
														});
													}
												})
											}else{
												uni.showToast({
													title: "保存失败，请打开权限功能重试",
													icon: "none"
												});
											}
										}
									})
								}
							});
						}
					}
				})
			},
			onGoodsShare() {
				this.mpShareShow = false
				this.customerShow = true
			},
			prentflaghanlde(flag) {
				this.imgShare = this.shopConfig.consult.consult_share_img
				// #ifdef MP-WEIXIN
				this.mpShareShow = true
				// #endif
				// #ifdef APP-PLUS
				this.customerShow = true
				// #endif
			},
			clickon() {
				this.click_add = false
			},
			appShare() {
				let shareInfo = {
					href: this.$websiteUrl + 'goods/' + this.goods_id + '?platform=APP',
					title: this.goodsInfo.goods_name,
					summary: this.goodsInfo.goods_brief,
					imageUrl: this.goodsInfo.goods_thumb
				};
				this.shareInfo(shareInfo)
			},
			initData() {
				setTimeout(() => {
					this.moduleLoad();
					uni.stopPullDownRefresh();
				}, 300);
			},
			//关闭Popup
			handelClose(val) {
				if (val == 'bonus') {
					this.bonusShow = false
					uni.setStorageSync('bonusShow', this.bonusShow);
				} else if (val == 'version') {
					this.versionShow = false
				} else if (val == 'customer') {
					this.customerShow = false
				} else {
					this.mpShareShow = false
				}
			},
			back() {
				uni.navigateBack({
					delta: 1
				})
			},
			async appUpdate() {
				const {
					data,
					status
				} = await this.$store.dispatch('setAppUpdate', {
					appid: this.updateAppid
				});

				if (status == 'success') {
					this.versionData = data
				}
			},
			updateAppStore() {
				plus.runtime.openURL(this.versionData.content.download_url)
			},
			clickCategoryNav(e) {
				let height = 0;
				this.$refs.jump.forEach((v) => {
					height += v.height;
				})

				this.jumpHeight = height;
			},
			loadMore() {
				this.scrollPickOpen = true
			},
			updateScrollPickOpen() {
				this.scrollPickOpen = false
			},
			onScroll(e) {
				let height = 0
				if (this.$refs.jump) {
					this.$refs.jump.forEach((v) => {
						height += v.height;
					})
				}
				
				this.scrollFixed = e.detail.scrollTop > height ? true : false
				this.scrollState = e.detail.scrollTop > 800 ? true : false
				
				this.oldScrollTop = e.detail.scrollTop;
			},
			messageList(){
				//顶部是否消息通知
				uni.request({
					url: this.websiteUrl + '/api/chat/sessions',
					method: 'GET',
					data: {
						page: 1,
						size: 10
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							this.liststatus = res.data.data
						}
					}
				})
			},
			fileterScrollTop(e){
				//视图会发生重新渲染
				this.scrollTop = this.oldScrollTop
				
				//当视图渲染结束 重新设置为0
				this.$nextTick(() =>{
					this.scrollTop = 0
				});
			}
		},
		watch: {
			modules() {
				this.dscLoading = false
			},
			versionData() {
				let wginfoStr = uni.getStorageSync('wgtinfo')!="" ? uni.getStorageSync('wgtinfo') : {};
				let wgtinfo = JSON.parse(uni.getStorageSync('wgtinfo'));
				let version = Number(wgtinfo.version.replace(/\./g, ''));
				let version_id = this.versionData.content.version_id ? Number(this.versionData.content.version_id.replace(/\./g, '')) :
					0;
				//当前app版本小于后台设置版本
				if (version < version_id) {
					this.versionShow = true
				} else {
					if (this.bonusData && this.bonusData.open == 1) {
						this.bonusShow = uni.getStorageSync('bonusShow') !== '' ? uni.getStorageSync('bonusShow') : true;
					}
				}
			}
		}
	}
</script>

<style>
	::-webkit-scrollbar {
		display: none;
	}

	.uni-page-body {
		height: 100%;
	}

	.uni-page-body .uni-navbar-header .uni-navbar-header-btns:first-child {
		width: 48upx;
		justify-content: center;
		padding: 0 0 0 20upx;
	}

	.uni-page-body .more-link {
		display: flex;
		flex-direction: row;
		align-items: center;
		font-size: 25upx;
	}

	.uni-page-body .more-link .txt {
		padding: 0 10upx 0 20upx;
		border-left: 1px solid #e3e8ee;
		line-height: 1.2;
		margin-left: 10upx;
	}

	.uni-page-body .more-link .uni-icon {
		line-height: 1.8;
	}

	.uni-page-body .input-view .uni-icon {
		display: block;
	}

	.uni-page-body .uni-popup-middle {
		padding: 0;
		width: 80%;
		height: auto;
		background: none;
		box-shadow: none;
	}

	.uni-page-body .uni-popup-middle .close,
	.uni-page-body .uni-popup-middle .close-on {
		width: 30upx;
		height: 30upx;
		line-height: 30upx;
		position: absolute;
		top: -32upx;
		right: -10upx;
	}

	.uni-page-body .uni-popup-middle .version-popup {
		position: relative;
		width: 100%;
		height: 100%;
		z-index: 9999;
	}

	.uni-page-body .uni-popup-middle .version-popup .version-text {
		position: absolute;
		top: 15%;
		left: 12%;
		color: #FFFFFF;
		z-index: 2;
	}

	.uni-page-body .uni-popup-middle .version-popup .version-text .tit {
		font-size: 32upx;
	}

	.uni-page-body .uni-popup-middle .version-popup .close {
		top: 15%;
		right: 10%;
		z-index: 2;
	}

	.uni-page-body .uni-popup-middle .version-popup .close-on {
		top: -5%;
		right: 0;
		z-index: 2;
	}

	.uni-page-body .uni-popup-middle .version-popup .save-picture {
		width: 324upx;
		height: 73upx;
		border: 1px solid rgba(255, 255, 255, 1);
		border-radius: 37upx;
		font-size: 28upx;
		font-weight: 400;
		color: rgba(255, 255, 255, 1);
		line-height: 73upx;
		text-align: center;
		margin: auto;
		margin-top: 40upx;
	}

	.uni-page-body .uni-popup-middle .version-popup .upgrade {
		width: 100%;
		position: relative;
		z-index: 1;
	}

	.uni-page-body .uni-popup-middle .version-popup .version-notic {
		position: absolute;
		color: #666666;
		margin: 0 15%;
		top: 48%;
		text-align: center;
		z-index: 2;
	}

	.uni-page-body .uni-popup-middle .version-popup .version-btn {
		position: absolute;
		bottom: 15%;
		width: 100%;
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
		z-index: 2;
	}

	.uni-page-body .uni-popup-middle .version-popup .version-btn .btn {
		width: 60%;
		height: 80upx;
		line-height: 80upx;
		margin: 0 auto;
		background: linear-gradient(to bottom right, #ff6333, #ff4335);
		color: #FFFFFF;
		text-align: center;
		border-radius: 30upx;
	}

	/* 小程序分享 */
	.mp-share-warp .title_on {
		text-align: center;
		border-top: 1upx solid #C8C7CC;
		padding: 8upx;
	}

	.mp-share-warp .lists {
		display: flex;
		flex-direction: row;
		margin: 30upx 0;
	}

	.mp-share-warp .list {
		width: 50%;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		padding: 20upx 0;
		background: #FFFFFF;
	}

	.mp-share-warp .list image {
		width: 100upx;
		height: 100upx;
	}

	.mp-share-warp .list text {
		margin-top: 20upx;
		height: 30upx;
		line-height: 30upx;
		font-size: 28upx;
	}

	.mp-share-warp button.list::after {
		border: none;
		border-radius: 0;
	}

	.mp-share-warp .mp-share-img {
		width: 280px;
		box-shadow: 0 10upx 20upx 0px rgba(61, 52, 75, 0.08);
		margin: 0 auto;
	}

	.mp-share-warp .btn-bar {
		margin: 10px;
	}

	/*顶级分类*/
	.page-top-category-body {
		height: 100%;
	}

	/*拼团*/
	.scroll-view-item .goods-info .currency-price /deep/ .shopping-icon {
		width: 35upx;
		height: 35upx;
		display: inline-block;
		border-radius: 50%;
		margin: 0 5upx 0 0;
		vertical-align: bottom;
	}

	.add_new {
		position: absolute;
		top: 10upx;
		right: 50upx;
		font-size: 24upx;
		border-radius: 10upx;
		padding: 10upx 30upx;
		background-color: rgba(88, 88, 88, .8);
		color: #FFFFFF;
		z-index: 999999;
	}

	.add_on:before {
		content: "";
		width: 0;
		height: 0;
		border-left: 10upx solid transparent;
		border-right: 10upx solid transparent;
		border-bottom: 10upx solid rgba(88, 88, 88, .8);
		position: absolute;
		top: -9upx;
		z-index: 999999;
		right: 80upx;
	}

	.add_on text {
		font-size: 22upx;
		padding-left: 30upx;
		color: red;
	}
</style>
