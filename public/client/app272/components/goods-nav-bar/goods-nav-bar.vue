<template>
	<view class="nav_bar" :style="{backgroundColor: rgba}">
		<!-- #ifdef APP-PLUS -->
		<view class="status_bar">
			<!-- 这里是状态栏 -->
		</view>
		<view class="nav_list">
			<view class="nav_l icon_wrap" :style="{backgroundColor: navIconRgba, color: navOpacity == 1 ? '#000' : '#fff'}" @click="goBack">
				<text class="iconfont icon-find-fanhui"></text>
			</view>
			<view class="nav_c" :style="{opacity: navOpacity}">
				<view class="nav_item">{{title}}</view>
			</view>
			<view class="nav_r">
				
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP-WEIXIN -->
		<view class="wx_nav_bar" :style="{paddingTop: `${menuButtonInfo.top - 4}px`, height: `${menuButtonInfo.height + 8}px`}">
			<view class="nav_l icon_wrap" :style="{backgroundColor: navIconRgba, color: navOpacity == 1 ? '#000' : '#fff'}" @click="goBack">
				<text class="iconfont icon-find-fanhui"></text>
			</view>
			<view class="nav_c" :style="{opacity: navOpacity}">
				<view class="nav_item">{{title}}</view>
			</view>
			<view class="nav_r">
				
			</view>
		</view>
		<!-- #endif -->
	</view>
</template>

<script>
	export default {
		name:"goods-nav-bar",
		props: {
			rgba: {
				type: String,
				default: 'rgba(0,0,0,0)'
			},
			navIconRgba: {
				type: String,
				default: 'rgba(0,0,0,0.4)'
			},
			navOpacity: {
				type: [Number, String],
				default: 0
			},
			title: {
				type: String,
				default: '商品详情'
			}
		},
		data() {
			return {
				vw: 375,
				menuButtonInfo: {}
			};
		},
		created() {
			// #ifdef MP-WEIXIN
			this.menuButtonInfo = uni.getMenuButtonBoundingClientRect();
			// #endif
		},
		methods: {
			goBack() {
				uni.navigateBack()
			}
		}
	}
</script>

<style lang="scss" scoped>
/* 导航 start */
	.nav_bar {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		background-color: rgba(0,0,0,0);
		z-index: 96;
		.status_bar {
			height: var(--status-bar-height);
			width: 100%;
		}
		.wx_nav_bar {
			display: flex;
			justify-content: space-between;
			align-items: center;
			width: 100%;
		}
		.nav_list {
			display: flex;
			justify-content: space-between;
			align-items: center;
			height: 44px;
			.iconfont {
				padding-top: 2px;
				font-size: 14px;
			}
		}
		.icon_wrap {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 26px;
			height: 26px;
			border-radius: 50%;
			color: #fff;
			background-color: rgba(0,0,0,0.4);
		}
		.nav_l {
			width: 26px;
			height: 26px;
			margin-left: 10px;
			.iconfont {
				padding-top: 1px;
				font-size: 14px;
			}
		}
		.nav_c {
			display: flex;
			justify-content: space-between;
			align-items: center;
			opacity: 0;
			.nav_item {
				margin: 0 7px;
				font-size: 15px;
			}
			.active_nav {
				position: relative;
				font-size: 16px;
				font-weight: 700;
				&:after {
					content: '';
					position: absolute;
					bottom: 0;
					left: 0;
					width: 100%;
					height: 3px;
					background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
				}
			}
		}
		.nav_r {
			display: flex;
			justify-content: space-between;
			align-items: center;
			min-width: 26px;
			height: 26px;
			margin-right: 10px;
			.icon_wrap:last-child {
				position: relative;
				margin-left: 10px;
				.shortcut {
					position: absolute;
					right: 0;
					top: 44px;
					display: flex;
					flex-direction: column;
					border-radius: 12rpx;
					background-color: rgba(255,255,255, 0.95);
					z-index: 101;
					&:before {
						content: '';
						position: absolute;
						right: 26rpx;
						top: -26rpx;
						transform: translateX(50%);
						width: 0;
						height: 0;
						line-height: 0;
						font-size: 0;
						border: 14rpx solid transparent;
						border-bottom-color: rgba(255,255,255, 0.95);
					} 
				}
				.shortcut_item {
					width: 290rpx;
					height: 95rpx;
					line-height: 95rpx;
					padding: 0 36rpx;
					font-size: 30rpx;
					text-align: left;
					color: #000;
					box-sizing: border-box;
					.iconfont {
						margin-right: 12rpx;
						font-size: 44rpx;
						color: #000!important;
						vertical-align: -6rpx;
					}
				}
			}
		}
	}
	/* 导航 end */
</style>
