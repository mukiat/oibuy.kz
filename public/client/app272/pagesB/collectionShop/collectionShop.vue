<template>
	<view>
		<block v-if="shopCollectList.length>0">
		<view class="shop">
			<view class="shop-top" v-for="(item,index) in shopCollectList" :key="index">
				<view class="left" @click="$outerHref('/pages/shop/shopHome/shopHome?ru_id='+item.ru_id,'app')">
					<image :src="item.shop_logo"></image>
					<view class="info">
						<view class="name uin-ellipsis">{{ item.shoprz_brand_name }}{{ item.shop_name_suffix }}</view>
						<view class="out">{{ item.count_store }}{{$t('lang.collection_two')}}</view>
					</view>
				</view>
				<view class="right">
					<text class="btn btn-red" @click="collectHandle(item.shop_id)">{{$t('lang.cancel_collection')}}</text>
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
	import dscNotContent from '@/components/dsc-not-content.vue';
	import universal from '@/common/mixins/universal.js';
	export default {
		mixins:[universal],
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
			  path: '/pagesB/collectionShop/collectionShop'
			}
		},
		computed:{
			shopCollectList:{
				get(){
					return this.$store.state.user.shopCollectList
				},
				set(val){
					this.$store.state.user.shopCollectList = val
				}
			}
		},
		methods:{
			setShopList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				this.$store.dispatch('setCollectShopList',{
					page:this.page,
					size:this.size,
				})
			},
			collectHandle(val){
				this.$store.dispatch('setCollectShop',{
					ru_id:val,
					status:1
				})
			},
		},
		onLoad(){
			this.setShopList()
		},
		onReachBottom(){
			if(this.page * this.size == this.shopCollectList.length){
				this.page ++
				this.setShopList()
			}
		}
	}
</script>

<style>
.shop{ display: flex; flex-direction: column;}
.shop-top{ padding: 20upx; display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 20upx; background: #FFFFFF;}
.shop-top .left{ flex:1; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; }
.shop-top .left image{ width: 105upx; height: 105upx;}
.shop-top .left .info{ margin-left: 20upx;}
.shop-top .left .info view{ line-height: 1.6; }
.shop-top .left .info .name{ width: 100%; font-weight: 700;}
.shop-top .left .info .out{ color: #999999;}
.shop-top .right{ display: flex; flex-direction: row; }
.shop-top .right .btn{ padding: 5upx 20upx; border: 1px solid #ddd; margin-right: 10upx;}
.shop-top .right .btn:last-child{ margin-right: 0;}

.shop-score{ display: flex; flex-direction: row; align-items: center; padding: 0 20upx;}
.shop-score view{ flex: 1; justify-content: center; margin-top: 10upx;}
.shop-score .tit{ color: #999999; margin-right: 20upx;}
.shop-score .score{ color: #f92028;}
</style>
