<template>
	<view class="container sv-container">
		<view class="sv-header">
			今日收入：<text class="total" v-if="day_commission_money">{{ day_commission_money }}</text>
		</view>
		<view class="sv-list">
			<view class="item" v-for="(item,index) in commissionList" :key="index">
				<view class="image">
					<image :src="item.goods_thumb" class="img" mode="widthFix"></image>
				</view>
				<view class="sv-item-con">
					<view class="left">
						<view class="change-desc onelist-hidden">{{ item.order_sn }}</view>
						<view class="time">{{ item.add_time }}</view>
					</view>
					<view class="right">
						<view class="money">{{ item.goods_amount_format }}</view>
						<view class="time">提成 {{ item.commission_money_format }}</view>
					</view>
				</view>
			</view>
			<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
		</view>
	</view>
</template>

<script>
	export default{
		data(){
			return{
				day_commission_money:'',
				commissionList:[],
				page: 1,
				size: 10,
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
			}
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.commissionList.length){
				this.page ++
				this.load()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad() {
			this.load();
		},
		methods:{
			async load(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/day/income`,{
					page:this.page,
					size:this.size
				});

				if(data.error > 0) return
				
				if(this.page > 1){
					this.commissionList = [this.commissionList,...data.list]
				}else{
					this.commissionList = data.list
				}
				
				this.day_commission_money = data.day_commission_money
			}
		}
	}
</script>

<style lang="scss" scoped>
.sv-container{
	height: 100vh;
	background: #FFFFFF;
	
	.sv-header{
		background-color: #FFFFFF;
		font-size: 36rpx;
		padding: 20px 30px;
		
		.total{
			color: #F2041A;
		}
	}
	
	.sv-list{
		.item{
			display: flex;
			flex-direction: row;
			padding: 20rpx 0;
			margin: 0 30rpx;
			border-bottom: 1px solid #F5F5F5;
			
			.image{
				width: 60px;
				height: 60px;
				
				.img{
					border-radius: 10rpx;
				}
			}
			
			.sv-item-con{
				display: flex;
				justify-content: space-between;
				padding-left: 20rpx;
				
				.left{
					width: 70%;
					
					.change-desc{
						font-size: 32rpx;
					}
				}
				
				.right{
					flex: 1;
					text-align: right;
					
					.money{
						font-size: 32rpx;
						font-weight: 600;
					}
				}
				
				.time{
					color: #999999;
				}
			}
		}
	}
}
</style>
