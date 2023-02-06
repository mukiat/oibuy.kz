<template>
	<view class="copyright_container" v-if="configData">
		<image class="copyright_img" :src="configData.copyright_img" mode="heightFix" v-if="configData.copyright_img"></image>
		<text class="copyright_text" v-if="configData.copyright_text_mobile">{{configData.copyright_text_mobile}}</text>
	</view>
</template>

<script>
	export default {
		name:"dsc-copyright",
		data() {
			return {
				copyrightInfo: {},
				configData: uni.getStorageSync('configData')
			};
		},
		created() {
			if(!this.configData) this.shopConfig();
		},
		methods:{
			shopConfig(){
				uni.request({
					url:`${this.websiteUrl}/api/shop/config`,
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						this.configData = data;
					}
				});
			},
		}
	}
</script>

<style lang="scss">
.copyright_container {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 20rpx 0;
	.copyright_img {
		height: 40rpx;
	}
	.copyright_text {
		width: 80%;
		font-size: 24rpx;
		color: #999;
		margin-top: 16rpx;
		text-align: center;
		line-height: 1.5;
	}
}
</style>
