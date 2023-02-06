<template>
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(tab,index) in tabBars" :key="index" :class="['tab-item',status==index ? 'active' : '']" @click="orderStatusHandle(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<view class="section-list" v-if="orderList && orderList.length > 0">
			<view class="video-goods-item" v-for="(item,index) in orderList" :key="index">
				<view class="head">
					<view class="shop-name">{{item.shop_name}}</view>
					<view class="order-status">{{ item.status }}</view>
				</view>
				<view class="item-bd">
					<view class="subHead1">
						<view class="item1">
							<view class="tit">{{$t('lang.order_sn')}}：</view>
							<view class="value">{{ item.order_sn }}</view>
						</view>
						<view class="item1">
							<view class="tit">{{$t('lang.order_time')}}：</view>
							<view class="value time">
								<view class="add_time">{{ item.add_time }}</view>
								<uni-tag :text="item.activity_lang" size="small" type="error" v-if="item.activity_lang != ''"></uni-tag>
							</view>
						</view>
					</view>
					<view class="top" v-for="(goodsItem,goodsIndex) in item.goods_list" :key="goodsIndex">
						<view class="left">
							<view class="image-box"><image :src="goodsItem.goods_thumb" class="img" /></view>
						</view>
						<view class="right">
							<view class="right-box-left">
								<view class="name onelist-hidden">{{ goodsItem.goods_name }}</view>
								<view class="attr" v-if="goodsItem.goods_attr">{{ goodsItem.goods_attr }}</view>
							</view>
							<view class="right-box-right">
								<view class="price">{{ goodsItem.goods_price }}</view>
								<view class="num">x{{ goodsItem.goods_number }}</view>
							</view>
						</view>
					</view>
					<view class="gong">共{{ item.goods_list.length }}件，实付款<text>{{ item.order_amount_format }}</text></view>
				</view>
				<!-- <view class="item-fd">08/07 10:22</view> -->
			</view>
			<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
		</view>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	export default {
		components: {
			uniTag,
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		data() {
			return {
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				tabBars:['全部','待发货','已发货'],
				disabled:false,
				loading:true,
				page:1,
				size:10,
				status:0,
				dscLoading:true,
				shopConfig: uni.getStorageSync('configData'),
				orderList:[]
			};
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.orderList.length){
				this.page ++
				this.getOrderList()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad(e){
			this.status = e.status ? e.status : 0;
		},
		onShow(){
			this.getOrderList();
		},
		methods:{
			async getOrderList(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/user/order`,{
					status: this.status,
					page: this.page,
					size: this.size
				});
				console.log(data)
				if(data.error > 0) return
				
				if(this.page > 1){
					this.orderList = [...this.orderList, data.list]
				}else{
					this.orderList = data.list
				}
			},
			orderStatusHandle(index){
				this.status = index;
				this.getOrderList();
			}
		}
	}
</script>

<style lang="scss" scoped>
	.video-goods-item{
		margin: 20rpx;
		background-color: #FFFFFF;
		border-radius: 10rpx;
		
		.head{
			padding: 20rpx;
			display: flex;
			align-items: center;
			justify-content: space-between;
			border-bottom: 1px solid #F5F5F5;
			
			.shop-name{
				font-size: 30rpx;
			}
			.order-status{
				color: #F0151B;
				font-size: 30rpx;
			}
		}
		
		.item-bd{
			display: flex;
			flex-direction: column;
			border-bottom: 1px solid #F5F5F5;
			
			.subHead1{
				display: flex;
				flex-direction: column;
				border-bottom: 1px solid #f0f0f0;
				padding: 20rpx;
				
				.item1{ display: flex; flex-direction: row; justify-content: flex-start;}
				
				.tit,
				.time{ color: #999999; font-size: 26upx; display: flex; align-items: center;}
			}
			
			.top{
				display: flex;
				padding: 20rpx 20rpx 0;
				
				.left{
					.image-box{
						width: 80px;
						height: 80px;
					}
				}
				
				.right{
					flex: 1;
					margin-left: 20rpx;
					display: flex;
					justify-content: space-between;
					align-items: flex-start;
					
					.right-box-left{
						display: flex;
						flex-direction: column;
						
						.name{
							font-size: 32rpx;
						}
						
						.attr{
							color: #999999;
						}
					}
					
					.right-box-right{
						display: flex;
						flex-direction: column;
						justify-content: flex-end;
						text-align: right;
						
						.price{
							font-size: 32rpx;
							font-weight: 700;
						}
						
						.num{
							color: #999999;
						}
					}
				}
			}
			.gong{
				text-align: right;
				padding: 20rpx;
				font-size: 30rpx;
				
				text{
					font-weight: 700;
					font-size: 30rpx;
					margin-left: 10rpx;
				}
			}
		}
		
		.item-fd{
			padding: 20rpx;
			color: #999999;
		}
	}
</style>
