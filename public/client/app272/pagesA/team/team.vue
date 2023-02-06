<template>
	<view class="uni-tab-bar fixed-bottom-padding">
		<scroll-view id="tab-bar" class="uni-swiper-tab" scroll-x :scroll-left="scrollLeft">
			<view class="swiper-tab-list" :class="{'active' : tc_id == 0}" @click="tapTab(0)">{{$t('lang.all')}}</view>
			<view v-for="(tab,index) in tabBars" :key="index"
				:class="['swiper-tab-list',tc_id == tab.tc_id ? 'active' : '']" @click="tapTab(tab.tc_id)">{{tab.name}}
			</view>
		</scroll-view>
		<view class="wapper">
			<block v-if="tc_id === 0">
				<view class="swiper_warp"
					v-if="teamCateData.team_categories_child && teamCateData.team_categories_child.length > 0">
					<swiper :class="[rowNumber.childRow]"
						:indicator-dots="false" :interval="3000" :duration="1000">
						<swiper-item v-for="(categoriesChilds, categoriesChildsIndex) in categoriesChild"
							:key="categoriesChildsIndex">
							<view class="sub_nav">
								<view class="sub_nav_item" v-for="(item,index) in categoriesChilds" :key="index"
									@click="onChildsTab(item.tc_id)">
									<image class="nav_img" :src="item.tc_img" mode="widthFix"></image>
									<view class="nav_txt">{{item.name}}</view>
								</view>
							</view>
						</swiper-item>
					</swiper>
				</view>
				<view class="brand_choice swiper_warp"
					v-if="teamCateData.team_categories_brand && teamCateData.team_categories_brand.length > 0">
					<text class="title">品牌精选</text>
					<swiper :class="[rowNumber.brandRow]"
						:indicator-dots="false" :interval="3000" :duration="1000">
						<swiper-item v-for="(categoriesBrandItem, categoriesBrandIndex) in categoriesBrand"
							:key="categoriesBrandIndex">
							<view class="sub_nav">
								<view class="sub_nav_item" v-for="(item,index) in categoriesBrandItem" :key="index"
									@click="goList(item)">
									<image class="nav_img" :src="item.brand_logo" mode="widthFix"></image>
									<view class="nav_txt">{{item.brand_name}}</view>
								</view>
							</view>
						</swiper-item>
					</swiper>
				</view>
			</block>
			<block v-else>
				<block v-if="teamCateData">
					<view class="swiper_warp"
						v-if="teamCateData.team_categories_child && teamCateData.team_categories_child.length > 0">
						<swiper :class="[rowNumber.childRow]"
							:indicator-dots="false" :interval="3000" :duration="1000">
							<swiper-item v-for="(categoriesChilds, categoriesChildsIndex) in categoriesChild"
								:key="categoriesChildsIndex">
								<view class="sub_nav">
									<view class="sub_nav_item" v-for="(item,index) in categoriesChilds" :key="index"
										@click="linkHref(item)">
										<image class="nav_img" :src="item.tc_img" mode="widthFix"></image>
										<view class="nav_txt">{{item.name}}</view>
									</view>
								</view>
							</swiper-item>
						</swiper>
					</view>
					<view class="brand_choice swiper_warp"
						v-if="teamCateData.team_categories_brand && teamCateData.team_categories_brand.length > 0">
						<text class="title">品牌精选</text>
						<swiper :class="[rowNumber.brandRow]"
							:indicator-dots="false" :interval="3000" :duration="1000">
							<swiper-item v-for="(categoriesBrandItem, categoriesBrandIndex) in categoriesBrand"
								:key="categoriesBrandIndex">
								<view class="sub_nav">
									<view class="sub_nav_item" v-for="(item,index) in categoriesBrandItem" :key="index"
										@click="goList(item)">
										<image class="nav_img" :src="item.brand_logo" mode="widthFix"></image>
										<view class="nav_txt">{{item.brand_name}}</view>
									</view>
								</view>
							</swiper-item>
						</swiper>
					</view>
				</block>
			</block>
			<!--goodslist-->
			<view class="goods-list">
				<block v-if="teamGoodsData && teamGoodsData.length > 0">
					<view class="goods-item" v-for="(item,index) in teamGoodsData" :key="index"
						@click="detailClick(item)">
						<view class="goods-left">
							<image :src="item.goods_thumb" class="img" />
						</view>
						<view class="goods-right">
							<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
							<view class="plan-box">
								<view class="shop-price">{{$t('lang.single_purchase_price')}}{{item.shop_price}}</view>
							</view>
							<view class="plan-box">
								<view class="num">{{item.team_num}}{{$t('lang.one_group')}}</view>
								<view class="price">{{item.team_price}}</view>
								<view class="btn">{{$t('lang.up_group')}}</view>
							</view>
						</view>
					</view>
				</block>
				<block v-else>
					<dsc-not-content></dsc-not-content>
				</block>
			</view>

			<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
		</view>

		<dsc-tabbar :tabbar="tabbar"></dsc-tabbar>

		<dsc-common-nav></dsc-common-nav>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import {
		mapState
	} from 'vuex'
	import uniLoadMore from '@/components/uni-load-more.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscTabbar from '@/components/dsc-tabbar.vue';

	export default {
		data() {
			return {
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				autoplay: true,
				interval: 5000,
				duration: 500,
				scrollLeft: 0,
				tc_id: 0,
				page: 1,
				size: 10,
				dscLoading: true,
				tabbar: {
					type: 'team',
					index: 0,
					curpage: ''
				},
			}
		},
		components: {
			uniLoadMore,
			dscNotContent,
			dscCommonNav,
			dscTabbar
		},
		onUnload() {
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom() {
			this.showLoadMore = true
			if (this.page * this.size == this.teamGoodsData.length) {
				this.page++
				this.getGoodsList()
			} else {
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onShareAppMessage(res) {
			return {
				title: this.$t('lang.spell_group_home_page'),
				path: '/pagesA/team/team'
			}
		},
		computed: {
			...mapState({
				teamIndexData: state => state.team.teamIndexData,
				teamCateData: state => state.team.teamCateData,
			}),
			teamGoodsData: {
				get() {
					return this.$store.state.team.teamGoodsData
				},
				set(val) {
					this.$store.state.team.teamGoodsData = val
				}
			},
			tabBars() {
				return this.teamIndexData.team_categories
			},
			categoriesChild() {
				const list = this.teamCateData.team_categories_child || []

				if (list.length > 10) {
					const childs = []
					for (var i = 0; i < list.length; i += 10) {
						childs.push(list.slice(i, i + 10))
					}
					return childs
				} else {
					return [list]
				}
			},
			categoriesBrand() {
				const list = this.teamCateData.team_categories_brand || []

				if (list.length > 10) {
					const childs = []
					for (var i = 0; i < list.length; i += 10) {
						childs.push(list.slice(i, i + 10))
					}
					return childs
				} else {
					return [list]
				}
			},
			rowNumber() {
				return {
					childRow: this.teamCateData.team_categories_child && this.teamCateData.team_categories_child.length > 5 ? 'row_2' : 'row_1',
					brandRow: this.teamCateData.team_categories_brand && this.teamCateData.team_categories_brand.length > 5 ? 'row_2' : 'row_1'
				}
			}
		},
		methods: {
			teamNav() {
				let platform = 'H5';

				// #ifdef MP-WEIXIN
				platform = 'MP-WEIXIN';
				// #endif

				this.$store.dispatch('setTeamIndex', {
					platform: platform
				});

				this.tapTab(0)
			},
			tapTab(id) {
				this.dscLoading = true;
				this.tc_id = id;
				this.$store.dispatch('setTeamCate', {
					tc_id: this.tc_id
				});
				this.getGoodsList();
			},
			getGoodsList() {
				this.$store.dispatch('setTeamGoods', {
					size: this.size,
					page: this.page,
					tc_id: this.tc_id
				});
			},
			detailClick(item) {
				uni.navigateTo({
					url: '/pagesA/team/detail/detail?goods_id=' + item.goods_id + '&team_id=0'
				})
			},
			linkHref(item) {
				uni.navigateTo({
					url: `/pagesA/team/list/list?id=${item.tc_id}`
				})
			},
			goList(item) {
				uni.navigateTo({
					url: `/pagesA/team/list/list?id=${this.tc_id}&brand_id=${item.brand_id}`
				})
			},
			onChildsTab(id){
				this.tc_id = id;
				if(id > 0){
					this.tapTab(id);
				}
			}
		},
		onLoad() {
			let pages = getCurrentPages()
			this.tabbar.curpage = pages[pages.length - 1].route

			this.teamNav();
		},
		watch: {
			teamIndexData() {
				this.dscLoading = false
			},
			teamCateData() {
				this.dscLoading = false
			}
		}
	}
</script>

<style scoped>
	.uni-swiper-tab {
		background: #FFFFFF;
		z-index: 9999;
		position: fixed;
		top: 0;
	}

	.wapper {
		padding: 100rpx 16rpx 0;
		padding-bottom: env(safe-area-inset-bottom);
	}

	.adv {
		display: flex;
		flex-direction: row;
		line-height: none;
	}

	.col {
		line-height: none;
		font-size: 0;
	}

	.col image {
		width: 100%;
	}

	.banner-bottom .col {
		width: 50%;
	}

	.flex-ban {
		flex-wrap: wrap;
		margin-top: 20upx;
	}

	.flex-ban .col {
		width: 50%;
		border-right: 1px solid #f4f4f4;
		box-sizing: border-box;
	}

	.section {
		margin-top: 20upx;
		background-color: #FFFFFF;
	}

	.section .title {
		padding: 20upx 30upx;
		border-bottom: 1px solid #f4f4f4;
	}

	.section .con {
		display: flex;
		flex-direction: row;
	}

	.section .con .adv {
		width: 50%;
	}

	.section .con .adv .col {
		width: 100%;
	}

	.section .con .right {
		flex-direction: column;
	}

	.section .con .right .col {
		border-bottom: 1px solid #f4f4f4;
		border-left: 1px solid #f4f4f4;
	}

	.goods-list {
		overflow: hidden;
		margin-top: 16rpx;
		border-radius: 10rpx;
	}

	.nav {
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
		background-color: #ffffff;
		padding: 20upx;
	}

	.nav .item {
		width: 25%;
		padding: 20upx;
		text-align: center;
	}

	.nav .item .icon {
		width: 100%;
		font-size: 0;
	}

	.nav .item .icon image {
		width: 100%;
		max-height: 69px;
	}

	.nav .item .txt {
		color: #333333;
		margin-top: 5upx;
	}

	.hot_bottom_swiper {
		height: 120px;
	}

	.hot_bottom_swiper image {
		height: 120px;
	}

	/* 二级导航 start */
	.brand_choice {
		padding-top: 20rpx;
		border-radius: 10rpx;
		background-color: #fff;
	}

	.brand_choice .title {
		padding: 0 20rpx 4rpx;
		line-height: 1.1;
		font-weight: 700;
	}

	.brand_choice .row_1 {
		height: 140rpx;
	}

	.brand_choice .row_2 {
		height: 260rpx;
	}

	.brand_choice .sub_nav_item {
		height: 120rpx;
	}

	.brand_choice .sub_nav_item .nav_img {
		height: 50rpx;
		border-radius: 0;
	}

	.swiper_warp {
		margin-top: 16rpx;
	}

	.row_1 {
		height: 200rpx;
	}

	.row_2 {
		height: 380rpx;
	}

	.sub_nav {
		overflow: hidden;
		display: flex;
		flex-wrap: wrap;
		padding-top: 20rpx;
		border-radius: 10rpx;
		background-color: #fff;
	}

	.sub_nav_item {
		width: 20%;
		height: 180rpx;
		padding: 0 16rpx 20rpx;
		box-sizing: border-box;
	}

	.sub_nav_item .nav_img {
		display: block;
		width: 100%;
		height: calc((750rpx - 32rpx) / 5 - 32rpx);
		border-radius: 50%;
	}

	.sub_nav_item .nav_txt {
		font-size: 26rpx;
		text-align: center;
		color: #999;
	}

	/* 二级导航 end */
</style>
