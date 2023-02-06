<template>
	<view class="container">
		<view class="goods-list mt20" v-if="goodsList && goodsList.length > 0">
			<view class="goods-item" v-for="(item,index) in goodsList" :key="index">
				<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
				<view class="goods-right">
					<view>
						<view class="goodsname twolist-hidden">{{ item.goods_name }}</view>
						<view class="kucun">库存 {{ item.goods_number }}</view>
					</view>
					<view class="plan-box"><view class="price">{{ item.shop_price }}</view></view>
				</view>
			</view>
			<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
		</view>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default{
		components: {
			dscNotContent
		},
		data(){
			return{
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				disabled:false,
				loading:true,
				page:1,
				size:10,
				status:0,
				dscLoading:true,
				goodsList:[],
			}
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.goodsList.length){
				this.page ++
				this.getGoodsList()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad(e){},
		onShow(){
			this.getGoodsList();
		},
		methods:{
			async getGoodsList(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/goods/list`,{
					page: this.page,
					size: this.size
				});
				console.log(data)
				if(data.error > 0) return
				
				if(this.page > 1){
					this.goodsList = [...this.goodsList, data.list]
				}else{
					this.goodsList = data.list
				}
			},
		}
	}
</script>

<style>
	.goods-list .goods-item{ background: none; border-bottom: 1px solid #F5F5F5; padding: 30rpx 20rpx;}
	.goods-list .goods-item .goods-left,
	.goods-list .goods-item .goods-left image{border-radius: 20rpx;}
	.goods-list .goods-item .kucun{ color: #999999; }
	.goods-list .goods-item .plan-box .price{ font-size: 32rpx; margin-bottom: 10px;}
	.goods-list .goods-item .goods-right{ display: flex; flex-flow: column; justify-content: space-between; flex: 1;}
	.goods-list .goods-item .goods-right .goodsname{ font-size: 32rpx;}
</style>
