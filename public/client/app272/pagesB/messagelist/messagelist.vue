<template>
	<view class="container">
		<block v-if="sessions.length > 0">
		<view class="cart-goods-item" v-for="(item,index) in sessions" :key="index">
			<scroll-view scroll-x="true" scroll-y="true" class="scroll-view-G" :scroll-left="scrollLeft">
				<view class="scroll-view-item scroll-view-item-left" @click="onChats(item.goods_id, item.shop_id,item.uuid)">
					<view class="cart-goods-info" style="flex: 1 1 0%;">
						<view class="goods-img">
							<i v-if="item.unread==true"></i>
							<image :src="item.shop_logo" class="image"></image>
						</view>
						<view class="cart-goods-contents">
							<view class="cart-goods-tit">
								<view class="goods-title">{{item.shop_name}}</view>
								<view class="goods-time">{{item.last_time}}</view>
							</view>
							<view class="goods-infos">{{item.last_message}}</view>
						</view>
					</view>
				</view>
				<view class="scroll-view-item scroll-view-item-right" @click="deleteCartGoods(item.id)">
					<view>{{$t('lang.delete')}}</view>
				</view>
			</scroll-view>
		</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		
		components: {
			dscNotContent
		},
		data() {
			return {
				scrollLeft: 0,
				sessions: '',
				dscLoading: true
			};
		},
		onReachBottom() {
			if (this.page * this.size == this.accountlog_list.length) {
				this.page++
				this.goodsList()
			}
		},
		methods: {
			onChats(id,shop_id,uuid){
				uni.request({
					url: this.websiteUrl + '/api/chat/session/mark',
					method: 'POST',
					data: {
						uuid:uuid
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							this.onChat(id,shop_id)
						}
					}
				})
			},
			//客服列表
			goodsList(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				uni.request({
					url: this.websiteUrl + '/api/chat/sessions',
					method: 'GET',
					data: {
						page: 1,
						size: 10
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							this.sessions = res.data.data
						}
					}
				})

			},
			deleteCartGoods(id) {
				uni.request({
					url: this.websiteUrl + '/api/chat/session/destroy',
					method: 'POST',
					data: {
						id: id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							uni.showToast({
								title: "删除成功",
								icon: "success"
							});
							this.goodsList()
						}
					}
				})
			},
		},
		onLoad(e) {
			this.goodsList();
		},
		watch:{
			sessions(){
				this.dscLoading = false
			}
		}
	}
</script>

<style scoped>
	/* .container{ padding-bottom: 300upx;} */

	.card-goods-group {
		margin-bottom: 20upx;
	}

	.card-shop-head {
		display: flex;
		flex-direction: row;
		padding: 0 20upx 20upx;
		justify-content: space-between;
	}

	.card-shop-head .head-left,
	.card-shop-head .head-right {
		display: flex;
		flex-direction: row;
	}


	.checkbox-icon .uni-icon {
		display: block;
	}

	.card-shop-box {
		padding: 0 20upx;
	}

	.card-act-goods {
		display: flex;
		flex-direction: column;
		margin-bottom: 20upx;
	}

	.card-act-goods:last-child {
		margin-bottom: 0;
	}

	.cart-act-title {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		padding: 0 0 20upx 60upx;
	}

	.cart-act-title .txt {
		display: flex;
		flex-direction: row;
		align-items: center;
		color: #333333;
	}

	.cart-act-title .txt .em-icon {
		margin-right: 20upx;
	}

	.cart-act-title .txt .act-name view {
		font-size: 25upx;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}

	.cart-act-title .more {
		display: flex;
		flex-direction: row;
		color: #f92028;
		align-items: center;
	}

	.cart-goods-item {
		display: flex;
		flex-direction: row;
		overflow: hidden;
		width: 100%;
		margin-top: 20upx;
	}

	.cart-goods-item .checkbox-con {
		border-bottom: 1px solid #E5E5E5;
	}

	.cart-goods-item:last-child .checkbox-con {
		border-bottom: 0;
	}

	.cart-goods-info {
		background-color: #FFFFFF;
		padding: 30upx 20upx;
		display: flex;
		flex-direction: row;
		margin: 0 10upx;
		border-radius: 10upx;

	}

	.cart-goods-info .goods-img {
		width: 80upx;
		height: 80upx;
		border-radius: 10upx;
		position: relative;
		/* border: 1px solid #666666; */
	}
    .cart-goods-info .goods-img i{
		    background: red;
		    height: 12upx;
		    width: 12upx;
		    position: absolute;
			right: -10upx;
			top: -10upx;
		    z-index: 99;
		    border-radius: 50%;
	}
	.cart-goods-info .image {
		width: 80upx;
		height: 80upx;
		border-radius: 10upx;
	}


	.cart-goods-contents {
		overflow: hidden;
		width: 100%;
		margin-left: 20upx;
	}

	.cart-goods-tit {
		display: flex;
		justify-content: space-between;
	}

	.goods-title {
		font-size: 28upx;
	}

	.goods-time {
		color: #999999;
		font-size: 25rpx;
	}


	.goods-infos {
		overflow: hidden;
		box-sizing: border-box;
		text-overflow: ellipsis;
		-webkit-line-clamp: 1;
		-webkit-box-orient: vertical;
		word-break: break-all;
		line-height: 32upx;
		padding-right: 200upx;
		color: #999999;
		font-size: 25rpx;

	}

	.scroll-view-G {
		display: flex;
		align-items: center;
		width: 100%;
		white-space: nowrap;
		overflow: hidden;
	}

	.scroll-view-G ::-webkit-scrollbar {
		display: none;
		width: 0 !important;
		height: 0 !important;
		-webkit-appearance: none;
		background: transparent;
	}

	.scroll-view-item {
		width: 100%;
		height: 100%;
		display: inline-block;
		vertical-align: middle;
		box-sizing: border-box;
	}

	.scroll-view-item-right {
		width: 20%;
		margin-left: 5px;
		height: 100%;
		line-height: 100%;
		text-align: center;
		background: #f92028;
		color: #FFFFFF;
		position: relative;
	}

	.scroll-view-item-right view {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	
	::-webkit-scrollbar{
		display: none;
	}
</style>
