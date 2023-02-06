<template>
	<view class="brand">
		<view class="brand-list-box">
			<view class="header">
				<view class="shop-top">
					<view class="b-img"><image :src="brandMsg.brand_logo" mode="widthFix"></image></view>
					<view class="b-con">
						<text class="name">{{brandMsg.brand_name}}</text>
						<text class="txt">{{$t('lang.owned_all')}} {{brandMsg.goods_count}} {{$t('lang.goods_letter')}}</text>
					</view>
				</view>
				<image :src="imagePath.shoppingInfoB" class="bg" mode="widthFix"></image>
			</view>
		</view>
		<scroll-view id="tab-bar" class="uni-swiper-tab" scroll-x :scroll-left="scrollLeft">
			<view v-for="(item,index) in brandCategory" :key="index" class="swiper-tab-list" :class="cat_id == item.cat_id ? 'active' : ''" @click="tabCategory(item.cat_id)">{{item.cat_name}}</view>
		</scroll-view>
		<view class="shopping-list-nums">
			<view class="item" @click="tabNavNums()">
				<view class="num">{{ brandNavNums.all_goods }}</view>
				<text>{{$t('lang.all_goods')}}</text>
			</view>
			<view class="item" @click="tabNavNums('new')">
				<view class="num">{{ brandNavNums.new_goods }}</view>
				<text>{{$t('lang.new')}}</text>
			</view>
			<view class="item" @click="tabNavNums('hot')">
				<view class="num">{{ brandNavNums.hot_goods }}</view>
				<text>{{$t('lang.hot')}}</text>
			</view>
		</view>
		<view class="product-list-lie" v-if="!dscLoading">
			<dsc-product-list :list="brandProduct" :mode="mode" :productOuter="productOuter" v-if="brandProduct"></dsc-product-list>
		</view>
		<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import dscProductList from '@/components/dsc-product-list.vue'
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		data() {
			return {
				brand_id:0,
				cat_id:0,
				type:'',
				page:1,
				size:10,
				mode:'grid',
				productOuter:true,
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				dscLoading:true,
			}
		},
		components:{
			dscProductList,
			dscCommonNav
		},
		computed:{
			...mapState({
				brandCategory: state => state.brand.brandCategory,
				brandMsg: state => state.brand.brandMsg,
				brandNavNums: state => state.brand.brandNavNums
			}),
			brandProduct:{
				get(){
					return this.$store.state.brand.brandProduct
				},
				set(val){
					this.$store.state.brand.brandProduct = val
				}
			}
		},
		methods: {
			brandDetail(){
				this.$store.dispatch('setBrandDetail',{
					brand_id:this.brand_id,
					cat:this.cat_id
				})
			},
			brandProductList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setBrandProduct',{
					brand_id:this.brand_id,
					cat:this.cat_id,
					sort:'',
					type:this.type,
					size:this.size,
					page:this.page
				})
			},
			tabCategory(cat_id){
				this.cat_id = cat_id
				this.brandProductList(1)
				this.brandDetail()
			},
			tabNavNums(val){
				this.type = val
				this.brandProductList(1)
			},
		},
		onLoad(e){
			this.brand_id = e.id ? e.id : 0;
			
			this.brandDetail()
			this.brandProductList(1)
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			if(this.page * this.size == this.brandProduct.length){
				this.page ++
				this.brandProductList()
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesC/brand/detail/detail?id='+this.brand_id
			}
		},
		watch:{
			brandProduct(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
.brand-list-box{ padding: 0;}
.uni-swiper-tab{ background: #FFFFFF;}
.uni-swiper-tab .swiper-tab-list.active{ color: #f92028;}
.shopping-list-nums{ background: #FFFFFF; display: flex; flex-direction: row; }
.shopping-list-nums .item{ display: flex; flex: 1; width: 33.3%; flex-direction: column; justify-content: center; align-items: center; padding: 10upx 0;}
.shopping-list-nums .item .num{ font-size: 36upx;}
.shopping-list-nums .item text{ font-size: 25upx; color: #999999;}
</style>
