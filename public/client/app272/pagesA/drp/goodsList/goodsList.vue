<template>
	<view>
		<!--goodslist-->
		<view class="goods-list">
			<block v-if="drpGoodsList.length > 0">
				<view class="goods-item" v-for="(item,index) in drpGoodsList" :key="index">
					<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
					<view class="goods-right">
						<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
						<view class="plan-box"><view class="price">{{item.shop_price_formated}}</view></view>
						<view class="plan-box">
							<view class="bl" v-if="item.commission == 1">
								<view class="num">{{item.dis_commission}}%</view>
								<view class="cont-tag">{{$t('lang.dis_commission')}}</view>
							</view>
							<view class="btn" :class="{'btn-disabled': item.drp_type}" @click="submitBtn(item.goods_id)">
								{{ item.drp_type == false ? $t('lang.want_represent') : $t('lang.cancel_represent') }}</view>
						</view>
					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		
		<dsc-common-nav></dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniLoadMore from '@/components/uni-load-more.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	export default {
		data() {
			return {
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				page:1,
				size:10,
				dscLoading:true,
				cat_id:0,
				keyword:''
			}
		},
		components: {
			uniLoadMore,
			dscNotContent,
			dscCommonNav,
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.drpGoodsList.length){
				this.page ++
				this.drpGoods()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		computed: {
			drpGoodsList:{
				get(){
					return this.$store.state.drp.dapList
				},
				set(val){
					this.$store.state.drp.dapList = val	
				}
			}
		},
		methods: {
			drpGoods(page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setDrpList',{
					page:this.page,
					size:this.size,
					cat_id: this.cat_id,
					keywords:this.keyword
				})
			},
			submitBtn(id){
				this.$store.dispatch('setDrpGoodsAdd',{
					goods_id: id,
				})
			}
		},
		onLoad(e) {
			this.cat_id = e.id ? e.id : '';
			this.keyword = e.keyword ? e.keyword : '';
			this.drpGoods(1)
		},
		watch:{
			drpGoodsList(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
.bl{ display: flex; flex-direction: row; }
.bl .num{ height: 46upx; line-height: 46upx; border: 2upx solid #f92028; color: #f92028; padding: 0 10upx;border-radius: 6upx 0  0 6upx; font-size: 25upx;}
.bl .cont-tag{height: 50upx; line-height: 50upx; background: #f92028; color: #FFFFFF; border-radius: 0 6upx 6upx 0; padding: 0 10upx;font-size: 25upx;}
</style>
