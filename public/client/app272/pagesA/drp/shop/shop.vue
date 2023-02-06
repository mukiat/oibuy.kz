<template>
	<view class="container">
		<view class="header">
			<view class="bg-img">
				<image :src="drpShopData.shop_info.shop_img" mode="widthFix" v-if="drpShopData.shop_info.shop_img"></image>
				<view class="model-box"></view>
				<!-- #ifdef MP-WEIXIN -->
				<button open-type="share" class="share" :data-shopid="drpShopData.shop_info.id" :data-userid="drpShopData.shop_info.user_id">分享</button>
				<!-- #endif -->
			</view>
			<view class="cont">
				<view class="left">
					<image :src="drpShopData.shop_info.shop_portrait" v-if="drpShopData.shop_info.shop_portrait"></image>
					<image :src="imagePath.userDefaultImg" v-else></image>
				</view>
				<view class="right">
					<view class="tit">{{drpShopData.shop_info.shop_name}}</view>
				</view>
			</view>
		</view>
		<view class="tabs">
			<view class="item" :class="{'active':status === 1}" @click="tabNav(1)">
				<view class="iconfont icon-pailie"></view>
				<view class="tit">{{$t('lang.all')}}</view>
			</view>
			<view class="item" :class="{'active':status === 2}" @click="tabNav(2)">
				<view class="iconfont icon-remenzhuanti"></view>
				<view class="tit">{{$t('lang.new')}}</view>
			</view>
			<view class="item" :class="{'active':status === 3}" @click="tabNav(3)">
				<view class="iconfont icon-cuxiao1"></view>
				<view class="tit">{{$t('lang.promotion')}}</view>
			</view>
		</view>
		<view class="product-list-lie">
			<dsc-product-list :list="drpGoodsList" :routerName="routerName" v-if="drpGoodsList"></dsc-product-list>
		</view>
		<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="showLoadMore" />
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	
	import dscProductList from '@/components/dsc-product-list.vue'
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		data() {
			return {
				filtrateShow: false,
				status:1,
				model: 0,
				shop_id:0,
				parent_id:0,
				active:0,
				size:10,
				page:1,
				shop_user_id:0,
				dscLoading:true,
				routerName:"drp",
				showLoadMore:true,
				loadMoreStatus:'more',				
				contentText: {
					contentdown: this.$t('lang.view_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
			}
		},
		components:{
			uniIcons,
			uniLoadMore,
			dscProductList,
			dscCommonNav,
			dscNotContent
		},
		onUnload(){
			this.loadMoreStatus = 'more';
		},
		onReachBottom(){
			this.loadMoreStatus = 'loading';
			if(this.page * this.size == this.drpGoodsList.length){
				this.page ++
				this.drpGoods()
			}else{
				this.loadMoreStatus = "noMore"
				return;
			}
		},
		onLoad(e) {
			this.shop_id = e.shop_id;
			this.parent_id = e.parent_id;
			
			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				this.shop_id = scene.split('.')[0];
				this.parent_id = scene.split('.')[1];
				uni.setStorageSync('parent_id',this.parent_id);
			}
		},
		onShow(){
			if(this.parent_id > 0){
				uni.setStorage({
					key:'parent_id',
					data:this.parent_id
				})
			}
			this.drpShop();
		},
		onShareAppMessage(res){
			if (res.from === 'button') {// 来自页面内分享按钮
				return {
				  title: this.$t('lang.this_is_my_drp'),
				  path: '/pagesA/drp/shop/shop?shop_id='+res.target.dataset.shopid+'&parent_id='+res.target.dataset.userid
				}
			}else{
				return {
				  title: this.$t('lang.this_is_my_drp'),
				  path: '/pagesA/drp/shop/shop?shop_id='+this.shop_id+'&parent_id='+ this.parent_id
				}
			}
		},
		onNavigationBarButtonTap(e){
			if(e.type == 'share'){
				let shareInfo = {
					href:this.$websiteUrl + 'drp/shop?shop_id=' + this.drpShopData.shop_info.id + '&platform=APP',
					title:this.$t('lang.this_is_my_drp'),
					summary:'',
					imageUrl:this.drpShopData.shop_info.shop_img
				};
				
				this.shareInfo(shareInfo)
			}
		},
		computed: {
			...mapState({
				drpShopData: state => state.drp.drpShopData,
			}),
			drpGoodsList:{
				get(){
					return this.$store.state.drp.drpGoodsList
				},
				set(val){
					this.$store.state.drp.drpGoodsList = val
				}
			}
		},
		methods: {
			drpShop(){
				this.$store.dispatch('setDrpShop',{
					shop_id:this.shop_id
				})
			},
			//商品列表
			drpGoods(status) {
				this.$store.dispatch('setDrpGoodsList',{
					id: this.shop_id,
					uid: this.shop_user_id,
					size: this.size,
					page: this.page,
					status: this.status,
					model: this.model
				});
			},
			tabNav(val){
				this.status = val
				this.drpGoods()
			},
		},
		watch:{
			drpShopData(){
				this.model = this.drpShopData.shop_info.type
				this.shop_user_id = this.drpShopData.shop_info.user_id
				this.drpGoods();
				this.dscLoading = false
				this.showLoadMore = false
			}
		}
	}
</script>

<style scoped>
.header{ position: relative;}
.header .bg-img{ width: 100%; height: 250upx;position: relative; overflow: hidden;}
.header .bg-img image{ width: 100%;}
.header .bg-img .share{ background: linear-gradient(to right, #FF010C, #FF7E0C); border-radius: 10px; padding: 0 25upx; font-size: 25upx; color: #ffffff; position: absolute; bottom: 20upx; right: 20upx; line-height: 2; z-index: 99;}
.header .model-box{ background: rgba(0, 0, 0, 0.4); position: absolute; right: 0; left: 0; top: 0; bottom: 0;}
.header .cont{ padding: 20upx; left: 0; right: 0; bottom: -80upx; position: absolute; display: flex; }
.header .cont .left{ width: 120upx; height: 120upx; border-radius: 10upx; overflow: hidden;}
.header .cont .left image{ width: 100%; height: 100%;}
.header .cont .right{ margin-left: 20upx; }
.header .cont .right .tit{ color: #FFFFFF; font-size: 30upx;}
.tabs{ display: flex; flex-direction: row; justify-content: center; align-items: center; padding: 80upx 0 20upx; background: #FFFFFF;}
.tabs .item{ width: 33%; text-align: center;}
.tabs .item .iconfont{ font-size: 35upx; line-height: 1.5; }
.tabs .item .tit{ font-size: 30upx; }
.tabs .item.active{ color: #f92028;}
</style>
