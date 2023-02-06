<template>
	<view class="container commission">
		<view class="commission-header">
			当前累计佣金：<text class="total" v-if="commissionMoney">{{ commissionMoney }}</text>
		</view>
		<view class="withdraw-list">
			<view class="item" v-for="(item,index) in commissionList" :key="index">
				<view class="row">
					<view class="money">{{ item.goods_amount_format }}</view>
					<view class="text">{{ item.change_desc }}</view>
				</view>
				<view class="row">
					<view class="time">{{ item.add_time }}</view>
					<view class="uni-red">提成{{ item.commission_money_format }}</view>
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
				amount: '',
				commissionMoney:'',
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
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/commission/log`,{
					page:this.page,
					size:this.size
				});
				if(data.error > 0) return
				
				if(this.page > 1){
					this.commissionList = [this.commissionList,...data.list]
				}else{
					this.commissionList = data.list
				}
				
				this.commissionMoney = data.commission_money
			}
		}
	}
</script>

<style lang="scss" scoped>
.commission{
	height: 100vh;
	background: #FFFFFF;
	
	.commission-header{
		background-color: #fff1c8;
		font-size: 36rpx;
		padding: 20px 30px;
		
		.total{
			color: #F2041A;
		}
	}
	
	.withdraw-list{
		margin-top: 20px;
		padding: 0 20px;
		
		.item{
			border-bottom: 1px solid #F5F5F5;
			padding: 20rpx 0;
			
			.row{
				display: flex;
				justify-content: space-between;
				align-items: center;
				
				.money{
					font-size: 34rpx;
					font-weight: 600;
				}
				
				.text{
					font-size: 28rpx;
				}
				
				.time{
					color: #999999;
				}
			}
		}
	}
}
</style>
