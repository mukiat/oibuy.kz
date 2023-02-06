<template>
	<view class="container vs-withdraw">
		<view class="withdraw-header">
			<view class="txt">当前可提现佣金：<text class="total">{{ userPromoter.promoter_money_format }}</text></view>
			<view class="withdraw-box">
				<input class="dsc_input" type="digit" v-model="amount" :placeholder="$t('lang.enter_shift_amount')" />
				<button class="txBtn" :loading="isLoading" :disabled="isLoading" @click="formSubmit">提现</button>
			</view>
		</view>
		<view class="withdraw-list">
			<view class="item" v-for="(item,index) in withdrawList" :key="index">
				<view class="row">
					<view class="left">
						<view class="money">{{ item.change_desc }}</view>
						<view class="time">{{ item.add_time }}</view>
					</view>
					<view class="right">
						<view class="text">{{ item.promoter_money_format }}</view>
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
				amount: '',
				withdrawList:[],
				isLoading: false,
				userPromoter:{},
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
			if(this.page * this.size == this.withdrawList.length){
				this.page ++
				this.load()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad() {
			this.load();
			this.getUserPromoter();
		},
		methods:{
			async getUserPromoter(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/info`);

				if(data.error > 0) return
				
				this.userPromoter = data.user_promoter
			},
			async load(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/account/log`,{
					page:this.page,
					size:this.size
				});
				
				if(data.error > 0) return
				
				if(this.page > 1){
					this.withdrawList = [this.withdrawList,...data.list]
				}else{
					this.withdrawList = data.list
				}
			},
			async formSubmit(){
				let inputValue = Number(this.amount)
				if(inputValue > 0){
					this.isLoading = true;
					
					const res = await this.$http.post(`${this.websiteUrl}/api/media/promoter/cash/advance`,{
						promoter_money: this.amount
					});
					
					if(res.data.error > 0){
						uni.showToast({
							title: res.data.msg,
							icon: 'none'
						});
					}
					
					this.isLoading = false;
					
					this.load();
				}else{
					uni.showToast({
						title: '请输入提现金额',
						icon: 'none'
					});
				}
			}
		}
	}
</script>

<style lang="scss" scoped>
	.vs-withdraw{
		height: 100vh;
		background: #FFFFFF;
		padding: 0 40rpx;
		
		.withdraw-header{
			padding-top: 40rpx;
			
			.txt{
				display: flex;
				font-size: 36rpx;
				padding-bottom: 20rpx;
				margin: 0 10rpx;
				
				.total{
					color: #F2041A;
				}
			}
			
			.withdraw-box{
				display: flex;
				flex-direction: row;
				
				.dsc_input{
					flex: auto;
					font-size: 36upx;
					height: 28px;
					padding: 20rpx;
					border: 1px solid #F5F5F5;
					border-radius: 10rpx 0 0 10rpx;
					border-right: 0;
				}
				
				.txBtn{
					background-color: #F2041A;
					color: #FFFFFF;
					padding: 0 30rpx;
					height: 50px;
					display: flex;
					justify-content: center;
					align-items: center;
					font-size: 32rpx;
					border-radius: 0 10rpx 10rpx 0;
				}
			}
		}
	
		.withdraw-list{
			margin-top: 20px;
			
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
						font-size: 34rpx;
						font-weight: 600;
					}
					
					.time{
						color: #999999;
					}
				}
			}
		}
	}
</style>
