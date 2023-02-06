<template>
	<scroll-view class="shop_container" scroll-y @scrolltolower="loadMore">
		<block v-if="controlVersion">
			<view class="shopping_menu flex_box">
				<view class="shopping_menu_left flex_box jc_sa ai_center">
					<view class="item" :class="{'active':sort == 'sort'}" @click="filterHandle('hot')">
						<text class="size_30 weight_700 mr10">{{$t('lang.hot_alt')}}</text>
						<text class="iconfont color_ccc size_24" :class="[order == 'DESC' && sort == 'sort' ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
					
					<view class="item" :class="{'active':regionData.city.id > 0}" @click="filterHandle('region')">
						<text class="size_30 weight_700 mr10">{{$t('lang.region')}}</text>
						<text class="iconfont color_ccc size_24 icon-arrow-down"></text>
					</view>
					<view class="item" :class="{'active':sort == 'distance'}" @click="filterHandle('distance')" v-if="isWeixin">
						<text class="size_30 weight_700 mr10">{{$t('lang.distance')}}</text>
						<text class="iconfont color_ccc size_24" :class="[order == 'DESC' && sort == 'distance' ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
				</view>
				<view class="shopping_menu_right flex_box ai_center">
					<view class="search_container flex_box ai_center">
						<input class="search_input" type="text" :placeholder="$t('lang.search_the_store')" v-model="keyword" />
						<view class="search_btn" @click="searchTheStore"><text class="iconfont icon-home-search"></text></view>
					</view>
				</view>
			</view>
			<view class="dsc_shop_nav">
				<scroll-view scroll-x="true" class="scroll_view" v-if="shopCatList.length > 0">
					<view class="scroll_view_item" :class="{'active':cat_id == 0}" @click="shopCatClick(0)">
						<view class="icon">
							<view class="iconfont icon-home-fenlei"></view>
						</view>
						<view class="name text_1 size_24">{{$t('lang.all_categories')}}</view>
					</view>
					<view class="scroll_view_item" :class="{'active':cat_id == item.cat_id}" v-for="(item, index) in shopCatList" :key="index"
					 @click="shopCatClick(item.cat_id)">
						<view class="icon">
							<image :src="item.touch_icon" class="img" v-if="item.touch_icon"></image>
							<image src="../../static/no_image.jpg" class="img" v-else></image>
						</view>
						<view class="name text_1 size_24">{{ item.cat_alias_name }}</view>
					</view>
				</scroll-view>
			</view>
			<view class="store-info">
				<view class="store-list" v-for="(item,index) in shopList" :key="index">
					<view class="header flex_box jc_sb ai_center">
						<view class="header_left flex_box ai_center">
							<view class="g-img" @click="$outerHref('/pages/shop/shopHome/shopHome?ru_id='+item.user_id,'app')">
								<image :src="item.logo_thumb" />
							</view>
							<view class="g-title">
								<view class="size_30 weight_700">{{ item.rz_shop_name }}</view>
								<view class="size_22 color_666">{{$t('lang.collection_one')}}{{ item.count_gaze }}{{$t('lang.collection_two')}}</view>
								<view class="size_22 color_666" v-if="isWeixin">{{$t('lang.distance')}}<text class="color_f0151b">{{ item.distance.value }}{{ item.distance.unit }}</text></view>
							</view>
						</view>
						<view class="flex_box ai_center">
							<view class="into_shop_btn" @click="$outerHref('/pages/shop/shopHome/shopHome?ru_id='+item.user_id,'app')">{{$t('lang.into_shop')}}</view>
							<view :class="[item.is_collect_shop == 1 ? 'attention_btn_active' : 'attention_btn']" @click="collectHandle(item.user_id,item.is_collect_shop)"><text class="iconfont icon-shop-guanzhu" v-if="item.is_collect_shop != 1"></text>{{ item.is_collect_shop != 1 ? $t('lang.attention') : $t('lang.followed')}}</view>
						</view>
					</view>
					<view class="shop_pic" v-if="item.goods.length > 0">
						<scroll-view scroll-x="true" class="shop_scroll_box">
							<view class="product_list_item" v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex" @click="$outerHref('/pagesC/goodsDetail/goodsDetail?id='+goodsItem.goods_id,'app')">
								<view class="goods_img">
									<image :src="goodsItem.goods_thumb" mode="aspectFit" />
								</view>
								<view class="goods_name_price flex_box fd_column ai_center">
									<text class="text_1 color_333 weight_700">{{goodsItem.goods_name}}</text>
									<text class="text_1 shop_price weight_700">{{goodsItem.shop_price}}</text>
								</view>
							</view>
						</scroll-view>
					</view>
				</view>
			</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		
		<!--地区选择-->
		<dsc-region :display="regionShow" :isLevel="4" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></dsc-region>
	</scroll-view>
</template>

<script>
	import { mapState } from 'vuex'
	import dscPopup from '@/components/uni-popup/uni-popup.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	import universal from '@/common/mixins/universal.js';
	import dscRegion from '@/components/dsc-region.vue';
	export default {
		mixins:[universal],
		components: {
			dscPopup,
			dscNotContent,
			dscRegion
		},
		data() {
			return {
				disabled: false,
				shopScore: false,
				size: 10,
				page: 1,
				cat_id: 0,
				sort: 'sort_order',
				order: 'ASC',
				city_id: '',
				lat: '',
				lng: '',
				vh: 0,
				keyword: '',
				indicatorStyle: `height: ${Math.round(uni.getSystemInfoSync().screenWidth/(750/100))}px;`,
				isWeixin:uni.getStorageSync('platform') == 'MP-WEIXIN' ? true : false
			};
		},
		onShareAppMessage(res) {
			return {
				title: this.$store.state.common.shopConfig.shop_title,
				path: '/pages/integration/integration'
			}
		},
		created() {
			let that = this
			this.$store.dispatch('setShopCatList');
			
			this.vh = uni.getSystemInfoSync().windowHeight / 2;
			if (!this.controlVersion) {
				uni.setNavigationBarTitle({
					title: this.$t('lang.list')
				});
			}
			
			// #ifdef APP-PLUS
			this.shopListLoad();
			// #endif
			
			// #ifdef MP-WEIXIN
			uni.getLocation({
				type:'wgs84',
				success:function(res){
					that.lat = res.latitude;
					that.lng = res.longitude;
					that.shopListLoad();
				}
			})
			// #endif
		},
		computed: {
			...mapState({
				shopCatList: state => state.shop.shopCatList,
			}),
			shopList: {
				get() {
					return this.$store.state.shop.shopList
				},
				set(val) {
					this.$store.state.shop.shopList = val
				}
			},
			shopCollectStatue() {
				return this.$store.state.user.shopCollectStatue
			}
		},
		methods: {
			shopListLoad(page, keyword = '') {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setShopList', {
					cat_id: this.cat_id,
					warehouse_id: 0,
					area_id: 0,
					size: this.size,
					page: this.page,
					sort: this.sort,
					order: this.order,
					lat: this.lat,
					lng: this.lng,
					city_id: this.city_id,
					keywords: keyword
				})
			},
			searchTheStore() {
			    if (this.keyword.trim().length > 0) {
				    this.shopListLoad(1, this.keyword);
			    } else {
				   this.city_id = '';
				   this.shopListLoad();
			    }
			},
			shopCatClick(val) {
				this.cat_id = val
				this.shopListLoad()
			},
			filterHandle(val) {
				if (val == 'hot') {
					this.order = this.order == 'ASC' ? 'DESC' : 'ASC'
					this.sort = 'sort'
					this.city_id = ''
					this.shopListLoad()
				} else if (val == 'region') {
					this.handleRegionShow();
					this.order = 'ASC';
				} else if (val == 'distance') {
					this.order = this.order == 'ASC' ? 'DESC' : 'ASC'
					this.sort = 'distance'
					this.shopListLoad()
				} else {
					this.$store.dispatch('setShopMap', {
						lat: this.lat,
						lng: this.lng
					}).then(res => {
						if (res.status == 'success') {
							window.location.href = res.data
						}
					})
				}
			},
			collectHandle(val, status) {
				if (this.$isLogin()) {
					this.$store.dispatch('setCollectShop', {
						ru_id: val,
						status: status
					})
				} else {
					uni.showModal({
						content: this.$t('lang.fill_in_user_collect_shop'),
						success: (res) => {
							if (res.confirm) {
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								})
							}
						}
					})
				}
			},
			loadMore() {
				if (this.page * this.size == this.shopList.length) {
					this.page++
					this.shopListLoad()
				}
			},
			shippingFee(val) {
				this.$store.dispatch("setShippingFee", {
					goods_id: 0,
					position: val,
				});
			},
		},
		watch: {
			shopCollectStatue() {
				this.shopCollectStatue.forEach((v) => {
					this.shopList.forEach((res) => {
						if (res.user_id == v.id) {
							res.is_collect_shop = v.status
							res.count_gaze = v.status == 1 ? res.count_gaze + 1 : res.count_gaze - 1
						}
					})
				})
			},
			regionSplic() {
				let shipping_region = {
					province_id: this.regionData.province.id,
					city_id: this.regionData.city.id,
					district_id: this.regionData.district.id,
					street_id: this.regionData.street.id,
				};
		
				//运费
				this.shippingFee(shipping_region);
			},
			regionShow(){
				if (!this.regionShow) {
					
					this.city_id = this.regionData.city.id;
					
					this.shopListLoad();
				}
			}
		}
	}
</script>

<style scoped>
	.shop_container {
		height: 100%;
	}

	.shopping_menu {
		margin-top: 1px;
		background: #FFFFFF;
	}

	.shopping_menu_left {
		height: 118upx;
		width: 60%;
	}
	
	.shopping_menu_left .item.active,
	.shopping_menu_left .item.active .iconfont{
		color: #f92028;
	}

	.shopping_menu_right {
		padding: 0 30upx 0 0;
		width: 40%;
	}

	.search_container {
		height: 60upx;
		width: 100%;
		padding-left: 24upx;
		padding-right: 14upx;
		border-radius: 30upx;
		border: 1upx solid rgba(238, 238, 238, 1);
	}
	.search_input {
		width: 80%;
	}
	.search_btn {
		flex: auto;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.dsc_shop_nav {
		margin: 20upx 0 20upx 15upx;
	}

	.scroll_view {
		white-space: nowrap;
		width: 100%;
		display: flex;
		flex-direction: row;
	}

	.scroll_view_item {
		display: inline-block;
		width: 148upx;
		height: 148upx;
		background: rgba(255, 255, 255, 1);
		box-shadow: 0upx 5upx 20upx 0upx rgba(108, 108, 108, 0.1);
		border-radius: 5upx;
		text-align: center;
		overflow: hidden;
		vertical-align: top;
		margin-right: 10upx;
	}

	.scroll_view_item .icon {
		width: 80upx;
		height: 80upx;
		overflow: hidden;
		display: flex;
		justify-content: center;
		align-items: center;
		margin: 8upx auto;
	}

	.scroll_view_item .icon .iconfont {
		height: 80upx;
		line-height: 100upx;
		font-size: 51upx;
		color: #6C6C6C;
	}

	.scroll_view_item.active .name {
		color: #f92028;
	}


	.store-info {
		position: relative;
		padding: 0 15upx;
	}

	.store-info .store-list {
		margin-bottom: 20upx;
		border-radius: 20upx;
		background: #FFFFFF;
	}

	.store-info .store-list .header {
		padding: 30upx 22upx;
		border: none;
	}

	.store-info .store-list .header .g-img {
		width: 68upx;
		height: 68upx;
		border: 1px solid #EEEEEE;
		border-radius: 50%;
		overflow: hidden;
	}

	.store-info .g-img image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.store-info .header .g-title {
		margin-left: 10upx;
	}

	.store-info .store-list .header .g-title view {
		line-height: 1;
		padding: 5upx 0;
	}

	.into_shop_btn {
		width: 114upx;
		height: 50upx;
		font-size: 24upx;
		text-align: center;
		line-height: 50upx;
		background: linear-gradient(90deg, rgba(250, 41, 41, 1), rgba(255, 88, 61, 1));
		border-radius: 25upx;
		color: #FFFFFF;
		margin-right: 10upx;
		box-sizing: border-box;
	}

	.attention_btn_active {
		width: 114upx;
		height: 50upx;
		text-align: center;
		background: #BBBBBB;
		border-radius: 25upx;
		font-size: 24upx;
		color: #FFFFFF;
		line-height: 50upx;
	}

	.attention_btn {
		width: 114upx;
		height: 50upx;
		font-size: 24upx;
		text-align: center;
		color: #FA2929;
		border: 2upx solid #FA2929;
		/* margin-bottom: 10upx; */
		border-radius: 25upx;
		line-height: 46upx;
		box-sizing: border-box;
	}

	.attention_btn .icon-shop-guanzhu {
		font-size: 24upx;
		color: #FA2929;
		margin-right: 4upx;
	}






	/* .store-info .store-list .header .g-title .distance {
		color: #f92028;
	}

	.store-info .store-list .header .g-btn {
		margin: 0 20upx;
	}

	.store-info .store-list .header .g-btn .text {
		padding: 4upx 16upx;
		border: 1px solid #f92028;
		color: #f92028;
		border-radius: 30upx;
		font-size: 14px;
	}

	.store-info .store-list .header .g-btn.active .text {
		color: #ccc;
		border-color: #ddd;
	} */

	.shop_pic {
		padding: 0 0 34upx 20upx;
	}
	.shop_scroll_box {
		white-space: nowrap;
		width: 100%;
		display: flex;
		flex-direction: row;
	}
 	.product_list_item {
		display: inline-block;
		width:202upx;
		margin-right: 20upx;
	}

	.shop_pic .product_list_item .goods_img {
		display: inline-block;
		width:202upx;
		height:202upx;
		border:1px solid #EEEEEE;
		border-radius:20upx;
		overflow: hidden;
	}
	.product_list_item .goods_img image {
		width: 100%;
		height: 100%;
		border-radius:20upx;
	}
	.goods_name_price {
		padding: 0 23upx;
	}
	.goods_name_price text {
		font-size: 28upx;
		text-align: center;
	}
	.shop_pic .product_list_item .shop_price {
		color: #FD001C;
	}
	.shop_pic .shop_price .shop_price_icon {
		font-size: 20upx;
		margin-right: 4upx;
	}
	
	
	
	.picker_view_main {
		width: 100%;
		background-color: #fff;
	}
	
	.picker_view_menu {
		display: flex;
		justify-content: space-between;
		align-items: center;
		top: 0;
		left: 0;
		width: 100%;
		height: 100upx;
		background-color: #fff;
		z-index: 99999;
		/* border-bottom: 1px solid #EEEEEE; */
	}
	
	.cancel_picker_btn,
	.confirm_picker_btn {
		width: 160upx;
		height: 100upx;
		line-height: 100upx;
		text-align: center;
		font-size: 30upx;
	}
	
	.cancel_picker_btn {
		color: #9A9A9A;
	}
	
	.confirm_picker_btn {
		color: #FA2A2A;
	}
	
	.picker_view_content {
		width: 100%;
		height: calc(100% - 100upx);
	}
	
	.picker_item {
		display: flex;
		justify-content: center;
		align-items: center;
		text-align: center;
	}
</style>
