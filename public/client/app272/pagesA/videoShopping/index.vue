<template>
	<view class="video-shopping">
		<view class="video-banner">
			<view class="image-view">
				<image src="../../static/videoshangc.png" mode="widthFix" class="img"></image>
				<view class="ju">
					<view class="btn btn-wz-red" @click="addPromoter" v-if="is_promoter == 0">申请成为推广员</view>
					<view class="btn" v-else-if="is_promoter == 1">你已成为视频号推广员</view>
					<view class="btn" v-else>推广已被注销</view>
				</view>
			</view>
		</view>
		<view class="user-info">
			<block v-if="islogin">
				<view class="left">
					<view class="avatar"><image :src="userInfo.avatar" v-if="userInfo && userInfo.avatar" class="img"></image></view>
					<view class="txt">{{ userInfo.name }}</view>
				</view>
				<view class="btn-bar btn-bar-radius" @click="$outerHref('/pagesA/videoShopping/commerceProfit','app')" v-if="is_promoter == 1">
					<view class="btn btn-red">带货收益</view>
				</view>
			</block>
			<block v-else>
				<view class="left">
					<text>登录后可申请成为分享员带货</text>
				</view>
				<view class="btn-bar btn-bar-radius" @click="$outerHref('/pagesB/login/login?delta=1','app')">
					<view class="btn btn-red">登录</view>
				</view>
			</block>
		</view>
		<view class="goods-list" v-for="(item,index) in goodsList" :key="index">
			<view class="goods-item">
				<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
				<view class="goods-right">
					<view>
						<view class="goods-name twolist-hidden">{{ item.goods_name }}</view>
						<view class="plan-box">
							<view class="price">{{ item.shop_price }}</view>
							<view class="num">月售 {{ item.sales_volume }}</view>
						</view>
					</view>
					<view class="zhuan-box">
						<text>赚</text>
						<view class="qian">{{ item.order_commission_format }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
	</view>
</template>

<script>
	export default{
		data() {
			return {
				islogin:null,
				userInfo:'',
				goodsList:[],
				is_promoter: 0,
				wxapp_name: '',
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
			if(this.page * this.size == this.goodsList.length){
				this.page ++
				this.load()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		async onShow() {
			this.islogin = this.$isLogin();
			
			if(this.islogin){
				//用户信息
				const { data } = await this.$store.dispatch('userProfile',{ type:true });
				this.userInfo = data ? data : ''
				
				this.load();
			}
		},
		methods:{
			async load(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/goods/list`,{
					page: this.page,
					size: this.size
				});

				if(data.error > 0) return
				
				if(this.page > 1){
					this.goodsList = [this.goodsList,...data.goods_list]
				}else{
					this.goodsList = data.goods_list
				}
				
				this.is_promoter = data.is_promoter
				this.wxapp_name = data.wxapp_name
			},
			addPromoter(){
				if(!this.islogin){
					uni.navigateTo({
						url:'/pagesB/login/login?delta=1'
					});
					
					return false;
				}
				
				uni.showModal({
					content: '申请成为' + this.wxapp_name + '推广员',
					success: res => {
						if (res.confirm) {
							this.$http.post(`${this.websiteUrl}/api/media/promoter/user`).then(res => {
	
								if(res.data.error == 2){
									uni.navigateTo({
										url:'/pagesA/videoShopping/commerceProfit'
									});
									
									return false;
								}else if(res.data.error == 999){
									uni.navigateTo({
										url:'/pagesB/login/login?delta=1'
									});
									
									return false;
								}
								
								uni.showToast({
									icon:'none',
									title: res.data.message
								})
							})
						}
					}
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
.video-shopping{
	background: #F5F5F5;
	overflow: hidden;
	.video-banner{
		position: relative;
		font-size: 0;
		.image-view{
			width: 100%;
			font-size: 0;
		}
		
		.ju{
			display: flex;
			justify-content: center;
			position: absolute;
			bottom: 20%;
			left: 0;
			right: 0;
		}
		
		.btn{
			min-width: 100px;
			padding: 0 20px;
			background-color: #FFFFFF;
			border-radius: 5px;
			color: #000;
			text-align: center;
			
			&.btn-wz-red{
				color: #F0151B;
			}
		}
	}
	
	.user-info{
		padding: 20rpx 40rpx;
		background-color: #FFFFFF;
		display: flex;
		align-items: center;
		justify-content: space-between;
		
		.left{
			flex: 1;
			display: flex;
			.avatar{
				width: 30px;
				height: 30px;
				border-radius: 50%;
				overflow: hidden;
				margin-right: 10px;
			}
		}
		
		.btn-bar{
			flex: inherit;
			.btn{
				height: 36px;
				line-height: 36px;
				width: 80px;
				font-size: 28rpx;
			}
		}
	}
	
	.goods-list{
		margin: 20rpx;
		
		.goods-item{
			border-radius: 10rpx;
			margin-bottom: 20rpx;
			.goods-left,
			.goods-left .img{ width: 250upx; height: 250upx;}
			
			.goods-right {
				display: flex;
				flex-flow: column;
				justify-content: space-between;
				.goods-name{ font-size: 30upx;}
			}
			
			&:last-child{
				margin-bottom: 0;
			}
			
			.plan-box{
				justify-content: flex-start;
				margin-top: 10rpx;
				
				.price{
					font-size: 32rpx;
					font-weight: 700;
					margin-right: 20rpx;
				}
				
				.num{
					font-size: 26rpx;
				}
			}
			
			.zhuan-box{
				display: flex;
				flex-direction: row;
				align-items: baseline;
				color: rgb(244,163,72);
				
				text{
					font-size: 25rpx;
					margin-right: 10rpx;
				}
				
				.qian{
					font-size: 32rpx;
				}
			}
		}
	}
}
</style>
