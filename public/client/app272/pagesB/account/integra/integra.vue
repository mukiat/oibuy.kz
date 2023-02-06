<template>
	<view class="main">
		<block v-if="accountlog_list.length > 0">
		<view class="main_box">
			<view class="integral_t">{{$t('lang.current_shop')}}<text>{{type}}</text>{{$t('lang.current_shop1')}}</view>
			<view class="integral_n">{{$t('lang.current_shop2')}}</view>
		</view>
		<view class="integral_title">{{$t('lang.current_shop3')}}</view>
		<view class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
			<view class="card-div">
			 <view>{{ item.short_change_desc }}</view>
			</view>
			<view class="growth_time">
					<view class="time">{{ item.change_time }}</view>
				     <view class="uni-red" v-if="item.type=='+'">{{item.type}}<text>{{ item.pay_points }}</text></view>
					  <view class="uni-red green" v-else>{{item.type}}<text>{{ item.pay_points }}</text></view>
			</view>
		</view>
		</block>
		<block v-else-if="!isLoading">
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				accountlog_list: [],
				type:'',
				page:1,
				size:10,
				isLoading: true
			};
		},
		components: {
			dscNotContent
		},
		onLoad(e) {
			this.type=e.type
			this.setRefoundList(1)

		},
		methods:{
			//列表
			setRefoundList(page) {
				this.isLoading = true;
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
			uni.request({
				url:this.websiteUrl + '/api/account/paypoints',
				method:'GET',
				data:{
					type:'pay_points',
					page:this.page,
					size:this.size
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					this.isLoading = false;
					if(res.data.status == 'success'){
						if (this.page == 1) {
							this.accountlog_list = res.data.data
						}else{
							this.accountlog_list = this.accountlog_list.concat(res.data.data); //将数据拼接在一起
						}

					}
				}
			})


			},
		},
		onReachBottom() {
			if (this.page * this.size == this.accountlog_list.length) {
				this.page++
				this.setRefoundList()
			}
		},
		onShareAppMessage(res) {
			return {
				title: this.$store.state.common.shopConfig.shop_title,
				path: '/pagesB/account/integra/integra'
			}
		},
	}
</script>

<style scoped>
	.main{
		height: 100%;
		background: #FFFFFF;
	}
	.growth_time{
		display: flex;
		justify-content: space-between;
	}
	.main_box{
		 background:linear-gradient(to right,#6A7ED5,#83A9F1,#83A9F1,#6A7ED5);
		 padding-top: 50rpx;
		 padding-bottom: 30rpx;
	}
	.integral_t{
		text-align: center;
		color: #FFFFFF;

	}
	.integral_t text{
		font-size: 35rpx;
		font-weight: 500;

	}
	.integral_n{
		font-size: 24rpx;
		text-align: center;
		color: #fff;
		margin-top: 20rpx;

	}
	.integral_title{
		font-size: 28upx;
		text-align: center;
		margin:0 30upx;
		padding: 20upx;
		border-bottom: 1px solid #f6f6f9;

	}
	.ny-item{
		padding:20upx 30upx;
		margin:0 20upx;
		border-bottom: 1px solid #f6f6f9;
	}
	.ny-item view{
		margin-top: 6upx;
	}
	.time_price{
		display: flex;
		justify-content: space-between;
	}
	.red{
		color: red;
		font-weight: 600;
	}
	.green{
		color: #33CC66;
		font-weight: 600;
	}
</style>
