<template>
	<view>
		<view class="service">
			<!-- #ifdef MP-WEIXIN -->
			<!-- <button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="service_item btn-cantact">
				<image :src="shopConfig.custom_jump_logo" mode="widthFix" class="image"></image>
				<text class="text"></text>
				<text class="text"></text>
			</button> -->
			<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="service_item btn-cantact">
				<image :src="shopConfig.kefu_logo" class="image"></image>
			</button>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="service_item" @click="changeNav(1)">
				<image :src="shopConfig.custom_jump_logo" class="image"></image>
				<text class="text"></text>
				<text class="text"></text>
			</view>
			<view class="service_item" @click="changeNav(2)">
				<image :src="shopConfig.kefu_logo" class="image"></image>
			</view>
			<!-- #endif -->
			<view class="service_item" @click="changeNav(3)">
				<image src="/static/service/03-share.png" class="image"></image>
			</view>
		</view>
	</view>
</template>

<script>
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins: [universal],
		props: ['shopConfig'],
		data() {
			return {
				curIndex: 0,
			}
		},
		methods: {
			changeNav(index) {
				if (index == 3) {
					this.$emit('flaghanlde', true)
				}else if(index == 1){
					uni.navigateTo({
						url:'/pagesC/web-view/web-view?url=' + encodeURIComponent(this.shopConfig.custom_jump_url) + '&isDirect=true'
					})
				}else{
					if(this.shopConfig.consult_kefu_type == 1){
						uni.navigateTo({
							url:'/pagesC/web-view/web-view?url=' + encodeURIComponent(this.shopConfig.consult_kefu_url) + '&isDirect=true'
						})
					}else{
						this.onChat(0,0)
					}
				}
			}
		},
	}
</script>

<style>
	.service {
		position: fixed;
		bottom: 150rpx;
		right: 30upx;
		color: #6e6d6b;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		z-index: 10;
	}

	.service_item {
		flex: 1;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		font-size: 26upx;
		position: relative;
	}

	.service_item {
		position: relative;
	}

	.service_item .image {
		width: 116upx;
		height: 116upx;
		padding: 8upx;
		z-index: 0;
	}

	@keyframes living {
		0% {
			transform: scale(1);
			opacity: 0.5;
		}

		50% {
			transform: scale(1.5);
			opacity: 0;
			/*圆形放大的同时，透明度逐渐减小为0*/
		}

		100% {
			transform: scale(1);
			opacity: 0.5;
		}
	}

	.service_item .text {
		position: absolute;
		width: 105upx;
		height: 105upx;
		left: 15.8upx;
		bottom: 20upx;
		background: red;
		border-radius: 50%;
		-webkit-animation: living 3s linear infinite;
		z-index: -1;
	}

	.service_item .text:nth-child(2) {
		-webkit-animation-delay: 1.5s;
		/*第二个text动画延迟1.5秒*/
	}

	.btn-cantact {
		background-color: transparent !important;
		border: 0 !important;
		border-radius: 0 !important;
		padding: 0 !important;
		overflow: visible !important;
	}

	.btn-cantact {
		background-color: inherit !important;
		font-size: 0 !important;
	}

	.btn-cantact::after {
		border-radius: 0 !important;
		border: 0 !important;
		width: 100% !important;
		height: 100% !important;
		background: transparent !important;
	}
</style>
