<template>
	<view class="presale">
		<block v-if="presaleData">
			<view class="presale-list safe-area-inset-bottom" v-for="(item,index) in presaleData" :key="index" v-if="item.goods != ''">
				<view class="title">
					<view class="text">{{ item.cat_name }}</view>
					<view class="more" @click="$outerHref('/pagesA/presale/list/list?cat_id=' + item.cat_id,'app')">{{$t('lang.more')}}<uni-icons type="forward" size="18" color="#999999"></uni-icons></view>
				</view>
				<view class="goods-list goods-list-lie">
					<view class="goods-item" v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex" @click="detailHref(goodsItem)">
						<view class="goods-img"><image :src="goodsItem.goods_thumb" class="img" mode="widthFix" /></view>
						<view class="goods-info">
							<view class="goods-name twolist-hidden">{{goodsItem.goods_name}}</view>
							<view class="plan-box">
								<view class="price">{{goodsItem.format_shop_price}}</view>
								<text class="market">{{goodsItem.format_market_price}}</text>
							</view>
						</view>
						<view class="already-over" v-if="goodsItem.already_over == 1"><image src="../../static/preslae-end.png" class="img"></image></view>
					</view>
				</view>
			</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		<uni-popup :show="tabbar.showBase" type="bottom" v-on:hidePopup="handleClose">
			<view class="presale-category">
				<view class="list" v-for="(item,index) in presaleData" :key="index" @click="catHref(item.cat_id)">{{item.cat_name}}</view>
			</view>
		</uni-popup>
		
		<dsc-tabbar :tabbar="tabbar" @setPopupVisible="setPopupVisible"></dsc-tabbar>
		
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscTabbar from '@/components/dsc-tabbar.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	
	export default {
		data() {
			return {
				tabbar:{
					type:'presale',
					curpage:'',
					showBase:false
				},
			}
		},
		components: {
			dscNotContent,
			dscCommonNav,
			dscTabbar,
			uniIcons,
			uniPopup
		},
		computed: {
			...mapState({
				presaleData: state => state.ump.presaleData,
			})
		},
		methods: {
			catHref(id){
				uni.navigateTo({
					url:"list/list?cat_id="+id
				})
			},
			setPopupVisible(val){
				this.tabbar.showBase = val;
			},
			handleClose(){
				this.tabbar.showBase = false;
			},
			detailHref(item){
				uni.navigateTo({
					url:'/pagesA/presale/detail/detail?act_id='+item.act_id
				})
			}
		},
		onLoad(){
			let pages = getCurrentPages()
			this.tabbar.curpage = pages[pages.length - 1].route
			
			this.$store.dispatch('setPresale')
		}
	}
</script>

<style>
.presale{ padding-bottom: 120upx; }
.presale-list .title{ background: #FFFFFF; display: flex; justify-content: space-between; align-items: center; padding: 20upx; }
.presale-list .title .text{ font-size: 30upx; color: #333333; }
.presale-list .title .more{ display: flex; justify-content: flex-end; align-items: center; color: #99999;}
.presale-list .title .more .uni-icon{ display: block; }
.presale-list .goods-list .plan-box{ display: flex; justify-content: flex-start;}
.presale-list .goods-list .plan-box .market{ text-decoration: line-through; font-size: 25upx; margin-left: 10upx; }

.presale-category .list{ padding: 15upx 0; border-bottom: 2upx solid #f4f4f4; color: #333;}

.goods-item{ position: relative; }
.already-over{ width: 100upx; height: 100upx; position: absolute; bottom: 10upx; right: 20upx; z-index: 9;}
</style>
