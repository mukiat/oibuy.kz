<template>
	<view class="activity-datail" v-if="activityShowsData">
		<view class="pic">
			<image :src="activityShowsData.activity_thumb" mode="widthFix" class="img" v-if="activityShowsData.activity_thumb"></image>
			<image src="../../../static/no_image.jpg" mode="widthFix" class="img" v-else></image>
		</view>
		<view class="title">
			<text class="tag tag-gradients-color">{{activityShowsData.act_type}}</text>
			<text class="name">{{activityShowsData.act_name}}</text>
		</view>
		<view class="section">
			<view class="tit">{{$t('lang.activity_rules')}}</view>
			<view class="p">{{$t('lang.label_activity_time')}}<text>{{activityShowsData.start_time}}{{$t('lang.zhi')}}{{activityShowsData.end_time}}</text></view>
			<view class="p">{{$t('lang.label_max_amount')}}<text class="text">{{activityShowsData.max_amount}}</text></view>
			<view class="p">{{$t('lang.label_min_amount')}}<text class="text">{{activityShowsData.min_amount}}</text></view>
			<view class="p">{{$t('lang.label_user_rank')}}<text class="text">{{activityShowsData.user_rank}}</text></view>
			<view class="p">{{$t('lang.label_activity_type')}}<text class="text2">{{activityShowsData.activity_name}}</text></view>
			<view class="p">{{$t('lang.label_act_range_type')}}<text class="text">{{activityShowsData.act_range}}</text></view>
		</view>
		<view class="section">
			<view class="tit">{{$t('lang.activity_goods')}}</view>
			<view class="goods-list">
				<block v-if="activityGoodsData && activityGoodsData.length > 0">
					<view class="goods-item" v-for="(item,index) in activityGoodsData" :key="index" @click="detailClick(item.goods_id)">
						<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
						<view class="goods-right">
							<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
							<view class="plan-box">
								<view class="price">{{item.shop_price_formated}}<text class="market">{{item.market_price_format}}</text></view>
							</view>
						</view>
					</view>
				</block>
				<block v-else>
					<dsc-not-content></dsc-not-content>
				</block>
			</view>
		</view>
		<view class="section section2" v-if="activityShowsData.gift && activityShowsData.gift.length > 0">
			<view class="tit">{{ $t('lang.complimentary') }}</view>
			<view class="goods-list goods-list-lie">
				<view class="goods-item" v-for="(item,index) in activityShowsData.gift" :key="index" @click="detailClick(item.id)">
					<view class="goods-img"><image :src="item.thumb" mode="widthFix" class="img" /></view>
					<view class="goods-info">
						<view class="goods-name twolist-hidden">{{item.name}}</view>
						<view class="price">{{item.price_formated}}</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex';
	
	export default {
		data() {
			return {
				act_id:0,
				page:1,
				size:10,
			}
		},
		computed: {
			...mapState({
				activityShowsData: state => state.ump.activityShowsData
			}),
			activityGoodsData:{
				get(){
					return this.$store.state.ump.activityGoodsData
				},
				set(val){
					this.$store.state.ump.activityGoodsData = val
				}
			}
		},
		methods: {
			activityShow() { //详情
				this.$store.dispatch('setActivityShow',{
					act_id: this.act_id
				})
	
				this.activityGoods()
			},
			activityGoods(page) { //列表
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
	
				this.$store.dispatch('setActivityGoods',{
					act_id: this.act_id,
					page: this.page,
					size: this.size,
					sort: 0,
					order:'desc'
				})
			},
			detailClick(id){
				uni.navigateTo({
					url:"/pagesC/goodsDetail/goodsDetail?id="+id
				})
			}
		},
		onReachBottom(){
			if(this.page * this.size == this.activityGoodsData.length){
				this.page ++
				this.activityGoods()
			}
		},
		onLoad(e){
			this.act_id = e.act_id;
			
			this.activityShow()
		}
	}
</script>

<style>
.activity-datail .pic{ width: 100%; background: #FFFFFF;}
.activity-datail .title{ background: #FFFFFF; display: flex; padding: 20upx; justify-content: flex-start; align-items: center;}
.activity-datail .title .tag{ color: #fff; font-size: 25upx; padding: 5upx 20upx; border-radius: 30upx; margin-right: 20upx;}

.section{ margin-top: 20upx; padding: 20upx; background: #FFFFFF; }
.section .tit{ font-size: 30upx; padding: 0 0 20upx; display: flex; justify-content: flex-start; align-items: center; }
.section .p{ color: #999; line-height: 1.5; }
.section .p text{ font-size: 26upx; }
.section .p .text{ color: #333333; }
.section .p .text2{ color: #f92028; }

.section .goods-list{ margin: 0; }
.section .goods-list .goods-item{ margin-bottom: 10upx; padding: 20upx 0 0 0; }
.section .goods-list .goods-item:last-child{ margin-bottom: 0;}
.section .goods-list .goods-item .price .market{ color: #999; text-decoration: line-through; font-size: 25upx; margin-left: 10upx;}

.section2{ padding: 0; background: none; margin-bottom: 20upx;}
.section2 .tit{ background: #FFFFFF; padding: 20upx;}

.goods-list.goods-list-lie{ display: flex; flex-direction: row; justify-content: space-between; padding: 0 20upx; flex-wrap: wrap; border-radius: 10upx;}
.goods-list.goods-list-lie .goods-item{ width: 50%; padding: 0; margin: 20upx 0 0; background: none; box-sizing: border-box; display: flex; flex-direction: column;}
.goods-list.goods-list-lie .goods-item:nth-child(2n-1){ padding-right: 10upx; }
.goods-list.goods-list-lie .goods-item:nth-child(2n){ padding-left: 10upx; }
.goods-list.goods-list-lie .goods-item .goods-img{ width: 100%; background: #FFFFFF;}
.goods-list.goods-list-lie .goods-item .goods-info{ padding: 10upx; background: #FFFFFF;}
.goods-list.goods-list-lie .goods-item .goods-info .price{ font-size: 30upx; color: #f92028; margin: 10upx 0;}
</style>
