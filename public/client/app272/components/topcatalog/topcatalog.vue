<template>
	<scroll-view class="top-catalog" scroll-y :lower-threshold="100" @scrolltolower="loadMore" :style="{'height':heightStyle}">
		<block v-if="categoryData">
			<view class="category-secondary" v-if="categoryData.category && categoryData.category.length > 0">
				<view class="item" v-for="(item,index) in categoryData.category" :key="index" @click="$outerHref('/pages/goodslist/goodslist?id=' + item.cat_id,'app')">
					<view class="box"><image :src="item.touch_icon" mode="widthFix" class="img"></image></view>
					<view class="name">{{item.cat_name}}</view>
				</view>
			</view>
			<view class="category-brand" v-if="categoryData.brand && categoryData.brand.length > 0">
				<view class="title">{{$t('lang.brand_selection')}}</view>
				<view class="list">
					<view class="item" v-for="(item,index) in categoryData.brand" :key="index" @click="$outerHref('/pagesC/brand/detail/detail?id='+item.brand_id,'app')">
						<view class="box"><image :src="item.brand_logo" mode="widthFix" class="img"></image></view>
						<view class="name">{{item.brand_name}}</view>
					</view>
				</view>
			</view>
			<view class="product-list-lie">
				<dsc-product-list :list="cateGoodsList" :mode="mode" v-if="cateGoodsList"></dsc-product-list>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="page > 0 && showLoadMore" />
			</view>
		</block>
		<block v-else>
			<uni-load-more :status="loadMoreStatus" :content-text="contentText" />
		</block>
	</scroll-view>
</template>

<script>
import { mapState } from 'vuex'

import dscProductList from '@/components/dsc-product-list.vue'
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';

export default{
	name:'top-catalog',
	props:{
		topCategoryCatid:{
			type:Number,
			default:0
		},
		jumpHeight:{
			type:Number,
			default:0
		}
	},
	components: {
		dscProductList,
		uniLoadMore
	},
	data(){
		return {
			categoryData:'',
			disabled:false,
			loading:true,
			page:1,
			size:10,
			footerCont:false,
			loadMoreStatus:'more',
			contentText: {
				contentdown: this.$t('lang.view_more'),
				contentrefresh: this.$t('lang.loading'),
				contentnomore: this.$t('lang.no_more')
			},
			showLoadMore: false,
			mode:'grid',
		}
	},
	created(){
		this.load();
	},
	mounted(){

	},
	computed:{
		// ...mapState({
		// 	cateGoodsList: state => state.category.cateGoodsList
		// }),
		cateGoodsList:{
			get(){
				return this.$store.state.category.cateGoodsList
			},
			set(val){
				this.$store.state.category.cateGoodsList = val;
			}
		},
		heightStyle(){
			return "calc(100% - " + this.jumpHeight + "px)";
		}
	},
	methods:{
		getGoodsList(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setGoodsList',{
				cat_id:this.topCategoryCatid,
				brand:'',
				warehouse_id:'0',
				area_id:'0',
				min:'',
				max:'',
				filter_attr:'',
				ext:'',
				goods_num:'',
				size:this.size,
				page:this.page,
				sort:'goods_id',
				order:'desc',
				self:'0',
				intro:''
			})
		},
		load(){
			this.categoryData = ''
			this.loadMoreStatus = "loading"
			uni.request({
				url: this.websiteUrl + '/api/visual/visual_second_category',
				method: 'GET',
				data: {
					cat_id:this.topCategoryCatid
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					this.categoryData = res.data.data;
					this.getGoodsList(1);
				},
				fail: (err) => {
					console.error(err)
				}
			})
		},
		loadMore() {
			this.showLoadMore = true
			this.loadMoreStatus = 'loading';
			if(this.page * this.size == this.cateGoodsList.length){
				this.page ++
				this.getGoodsList()
			}else{
				setTimeout(()=>{
					this.loadMoreStatus = "noMore"
					this.showLoadMore = false
				},2000)
				return;
			}
		}
	},
	watch:{
		topCategoryCatid(){
			this.cateGoodsList = [];
			this.load();
		}
	}
}
</script>

<style scoped>
.top-catalog{ height: 100%; box-sizing: border-box;}
.category-secondary{ background-color: #fff; display: flex; flex-direction: row; flex-wrap: wrap; border-radius: 20upx; padding: 20upx; margin: 20upx;}
.category-secondary .item{ width: 20%; }
.category-brand{ background-color: #fff; border-radius: 20upx; margin:0 20upx;}
.category-brand .title{ font-size: 28upx; color: #000; padding: 40upx 40upx 20upx; }
.category-brand .list{ display: flex; flex-direction: row; flex-wrap: wrap; }
.category-brand .list .item{ width: 25%; }
.category-brand .list .item .box{ width: 110upx; }

.item{ display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; margin-bottom: 20upx;}
.name{ font-size: 25upx; }
.box { display: flex; flex-direction: row; justify-content: center; align-items: center; width: 150upx;}
.box .img{ width: 80%; }

.product-list{ padding: 8upx 8upx 0; }
</style>
