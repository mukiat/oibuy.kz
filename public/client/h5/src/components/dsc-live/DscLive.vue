<template>
	<scroll-view class="live_container" scroll-y="true">
		<view class="flex_box fw_w jc_sb live_list">
			<view class="live_item" v-for="(live, index) in [1,2,3,4,5,]" :key="index">
				<image src="https://x.dscmall.cn/storage/images/201703/thumb_img/0_thumb_G_1490169030833.jpg" class="live_bg" mode="aspectFill"></image>
				<view class="live_item_bgc"></view>
				<view class="status_views flex_box ai_center">
					<view :class="['live_status', 'size_20', 'color_fff', liveStatus(index).class]"><text :class="['iconfont', liveStatus(index).icon, 'size_20', 'color_fff']"></text>{{liveStatus(index).text}}</view>
					<text class="views_number size_20 color_fff">8890 адам</text>
				</view>
				<view class="live_info">
					<image class="live_logo" src="https://x.dscmall.cn/storage/images/201703/thumb_img/0_thumb_G_1490169030833.jpg"></image>
					<text class="size_20 color_fe live_user">Bvlgari</text>
					<view class="live_explain">
						<text class="size_34 text_2 color_fff">Наурыз мейрамына ең арзан жеңілдіктер</text>
					</view>
				</view>
			</view>
		</view>
	</scroll-view>
</template>

<script>
	export default {
		data() {
			return {}
		},
		methods: {
			liveStatus(status) {
				let live_status = {
					class: '',
					icon: '',
					text: ''
				};
				if (status == 0) {
					live_status.class = 'advance_notice';
					live_status.icon = 'icon-find-live-time';
					live_status.text = 'Трейлер';
				} else if (status == 1) {
					live_status.class = 'live_ing';
					live_status.icon = 'icon-home-live-ing';
					live_status.text = 'LIVE';
				} else {
					live_status.class = 'playback';
					live_status.icon = 'icon-home-live-back';
					live_status.text = 'Қайта';
				}
				return live_status
			}
		}
	}
</script>

<style scoped>
	.live_container {
		width: 100%;
		height: 100%;
	}

	.live_list {
		padding: 14upx 16upx;
	}

	.live_item {
		position: relative;
		width: 352upx;
		height: 475upx;
		margin: 6upx 0;
		border-radius: 10upx;
		overflow: hidden;
	}

	.live_bg {
		width: 100%;
		height: 100%;
	}

	.live_item_bgc {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, .2);
		border-radius: 10px;
	}

	.status_views {
		position: absolute;
		top: 20upx;
		left: 22upx;
		height: 44upx;
		background: rgba(0, 0, 0, .4);
		border-radius: 22upx;
		overflow: hidden;
	}

	.live_status {
		height: 44upx;
		line-height: 44upx;
		padding: 0 12upx;
		border-radius: 22upx;
	}
	
	.live_ing {
		background: linear-gradient(-90deg, rgba(255, 59, 82, 1), rgba(239, 18, 22, 1));
	}
	.advance_notice {
		background:linear-gradient(-90deg,rgba(8,174,117,1),rgba(64,207,131,1));
	}
	.playback {
		background:linear-gradient(90deg,rgba(133,159,183,1),rgba(109,129,165,1));
	}

	.live_status .iconfont {
		margin-right: 12upx;
	}

	.views_number {
		padding: 0 20upx 0 10upx;
	}

	.live_info {
		position: absolute;
		bottom: 0;
		left: 0;
		width: 352upx;
		height: 155upx;
		background: rgba(0, 0, 0, .5);
		border-radius: 0 0 10upx 10upx;
		line-height: 1;
	}

	.live_logo {
		position: absolute;
		left: 23upx;
		top: 0;
		transform: translateY(-50%);
		width: 62upx;
		height: 62upx;
		border-radius: 50%;
	}

	.live_user {
		line-height: 1;
		margin-left: 96upx;
	}
	
	.live_explain {
		margin: 20upx 23upx 0;
	}
	.live_explain text {
		line-height: 1.2;
	}
</style>
