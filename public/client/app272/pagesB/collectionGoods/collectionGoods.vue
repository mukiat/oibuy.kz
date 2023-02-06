<template>
	<view class="collection">
		<block v-if="goodsCollectList.length>0">
		<view class="section-list">
			<view class="product-list">
				<view class="product-items">
					<view class="item" v-for="(item,index) in goodsCollectList" :key="index">
						<navigator class="product-img" :url="'/pagesC/goodsDetail/goodsDetail?id='+item.goods_id" hover-class="none">
							<image :src="item.goods_thumb" v-if="item.goods_thumb"></image>
						</navigator>
						<view class="product-info">
							<navigator :url="'/pagesC/goodsDetail/goodsDetail?id='+item.goods_id" hover-class="none" class="product-name twolist-hidden">{{ item.goods_name }}</navigator>
							<view class="product-row">
								<view class="price">{{ item.shop_price }}</view>
							</view>
							<view class="product-row-fr"><view class="iconfont icon-delete" @click="collectHandle(item.goods_id)"></view></view>
						</view>
					</view>
				</view>
			</view>
		</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				page:1,
				size:10,
			};
		},
		components:{
			dscNotContent
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/collectionGoods/collectionGoods'
			}
		},
		computed:{
			goodsCollectList:{
				get(){
					return this.$store.state.user.goodsCollectList
				},
				set(val){
					this.$store.state.user.goodsCollectList = val
				}
			}
		},
		methods:{
			setGoodslist(){
				this.$store.dispatch('setCollectGoodsList',{
					page:this.page,
					size:this.size,
				})
			},
			collectHandle(val){
				this.$store.dispatch('setCollectGoods',{
					goods_id:val,
					status:1
				})
			},
		},
		onLoad(){
			this.setGoodslist()
		},
		onReachBottom(){
			if(this.page * this.size == this.goodsCollectList.length){
				this.page ++
				this.setGoodslist()
			}
		}
	}
</script>

<style>

</style>
