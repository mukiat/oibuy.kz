<template>
	<view class="content">
		<swiper class="swiper" 
		:autoplay="autoplay" 
		:duration="duration"
		@change="bannerfun"
		>
			<swiper-item v-for="(item, index) in list" :key="index">
				<view class="swiper-item">
					<view class="swiper-item-img"><image :src="item.ad_code" mode="widthFix"></image></view>
				</view>
				<view class="experience" @tap="launchFlag()" v-if="index+1 == list.length">{{experience}}</view>
			</swiper-item>
		</swiper>
		
		<!-- 指示点 -->
		<view class="instruct-view" v-if="list.length > 1">
			<block v-for="(item, index) in list.length" :key="index">
				<view class="instruct" :class="{ active: index == num }"></view>
			</block>
		</view>
		
		<view class="skip" @click="skipClick" v-if="autoplay">
			<view>{{$t('lang.skip')}}</view>
			<uni-icons type="arrowright" size="18" color="#FFF9F8"></uni-icons>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				autoplay: false,
				duration: 500,
				jumpover: this.$t('lang.skip'),
				experience: this.$t('lang.experience'),
				list:[],
				num: 0,
			}
		},
		mounted() {
			this.adv();
		},
		methods: {
			async adv(){
				const {data,status} = await this.$store.dispatch('setSplashAdPosition',{
					type:true
				});
				
				if(data.total > 0){
					this.list = data.list;
				}else{
					this.launchFlag();
				}
			},
			launchFlag: function(){
				/**
				 * 向本地存储中设置launchFlag的值，即启动标识；
				 */
				uni.setStorage({
					key: 'launchFlag',
					data: true,
				});
				
				uni.switchTab({
					url: '/pages/index/index'
				});
			},
			// 滑块事件
			bannerfun(e) {
				this.num = e.detail.current;
			},
			skipClick(){
				
			}
		}
	}
</script>
<style lang="scss">
	page,
	.content{
		width: 100%;
		height: 100%;
		background-size: 100% auto ;
		padding: 0;
	}
	.swiper{
		width: 100%;
		height: 100%;
		background: #FFFFFF;
	}
	.swiper-item {
		width: 100%;
		height: 100%;
		text-align: center;
		position: relative;
		display: flex;
		/* justify-content: center; */
		align-items:flex-end;
		flex-direction:column-reverse
	}
	.swiper-item-img{
		width: 100%;
		height: 100%;
		margin: 0 auto;
	}
	.swiper-item-img image{
		width: 100%;
	}
	
	.jump-over{
		position: absolute;
		height: 60upx;
		line-height: 60upx;
		padding: 0 40upx;
		border-radius: 30upx;
		font-size: 32upx;
		color: #454343;
		border: 1px solid #454343;
		z-index: 999;
	}
	.jump-over{
		right: 45upx;
		top: 125upx;
	}
	.experience{
		position: absolute;
		bottom: 15%;
		left: 38%;
		color: #FFFFFF;
		box-sizing: border-box;
		padding: 15rpx 35rpx;
		background: #f92028;
		border-radius: 4rpx;
	}
	
	/* 指示点 */
	.instruct-view {
		display: flex;
		justify-content:center;
		position: relative;
		bottom: 10%;
	
		.instruct {
			height: 20rpx;
			width: 20rpx;
			border-radius: 50%;
			margin: 0 10rpx;
			background: rgba(#000, 0.2);
			
			&.active {
				background: #000 !important;
			}
		}
	}
	.skip{
		position: absolute;
		top: 40px;
		right: 10px;
		padding: 3px 10px 3px 20px;
		background:rgba(153,153,153,.9);
		color: #FFF9F8;
		border-radius: 20px;
		display: flex;
		flex-direction: row;
		align-items: center;
	}
	.skip .uni-icon{
		vertical-align: middle;
		margin-top: 1px;
	}
</style>
