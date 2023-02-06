<template>
	<view class="container">
		<view class="goods-list">
			<block v-if="drpGoodsList && drpGoodsList.length > 0">
				<view class="goods-item" v-for="(item,index) in drpGoodsList" :key="index" @click="detailClick(item.goods_id)">
					<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
					<view class="goods-right">
						<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
						<view class="plan-box"><view class="price">{{item.shop_price_formated}}</view></view>
						<view class="plan-box" v-if="item.commission == 1 && item.commission_money">
							<view class="commission-tag">
								<view class="num">{{$t('lang.commission_money')}}</view>
								<view class="cont-tag">{{item.commission_money_formated}}</view>
							</view>
						</view>
					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
			<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="showLoadMore" />
		</view>
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.drp_center')}}</text>
			</navigator>
		</dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	export default {
		data() {
			return {
				shop_id:0,
				user_id:0,
				status:1,
				model:0,
				size:10,
				page:1,
				dscLoading:true,
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
			dscCommonNav,
			dscNotContent
		},
		onLoad(e) {
			this.shop_id = e.shop_id;
			this.user_id = e.parent_id;
			this.model = e.model;
		},
		onShow(){
			this.drpGoods();
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
		computed: {
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
			drpGoods() {
				this.$store.dispatch('setDrpGoodsList',{
					id: this.shop_id,
					uid: this.user_id,
					page: this.page,
					size: this.size,
					status: this.status,
					model: this.model
				})
			},
			detailClick(id){
				uni.navigateTo({
					url:'/pagesC/goodsDetail/goodsDetail?id='+ id + '&parent_id=' + this.user_id
				})
			}
		},
		watch:{
			drpGoodsList(){
				this.dscLoading = false
				if(this.page * this.size > this.drpGoodsList.length * this.page){
					this.showLoadMore = false
				}
			}
		}
	}
</script>

<style scoped>
.section-list .list{ padding: 20upx; display: flex; flex-direction: row; justify-content: center; align-items: center; background-color: #FFFFFF; margin-top: 20upx;}
.section-list .list .left{ width: 120upx; margin-right: 20upx; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.section-list .list .left image{ width: 100%; }
.section-list .list .con{ flex: 1 1 0%;}
.section-list .list .con .name{ font-size: 28upx; }
.section-list .list .con .time{ font-size: 25upx; color: #999999;}
.section-list .list .right{ display: flex; flex-direction: row; justify-content: center; align-items: center;}
.section-list .list .right .price{ color: #f92028; font-size: 24upx;}
</style>
