<template>
	<scroll-view class="list" scroll-y @scrolltolower="loadMore">
		<block v-if="controlVersion">
			<view :class="['videolist', mode == 'grid' ? 'video-list-grid' : '']" v-if="!dscLoading">
				<block v-if="list.length > 0">
					<view class="videolist-item flex_box fd_column jc_sb" v-for="(item,index) in list" :key="index" @click="showPopup(item.goods_id, index)">
						<view class="img">
							<view class="video_duration flex_box jc_center ai_center">
								<text class="iconfont icon-play1 color_fff"></text>
								<!-- <text class="duration size_26 color_fff">{{ item.goods_video ? getVideoInfoByUrl(item.goods_video) : '00:00'}}</text> -->
							</view>
							<image :src="item.goods_thumb" class="image" v-if="item.goods_thumb"></image>
							<image src="../../static/no_image.jpg" class="image" v-else></image>
						</view>
						<view class="info flex_1 flex_box fd_column jc_sb">
							<text class="name size_30 color_333 text_2 weight_700">{{item.goods_name}}</text>
							<view class="flex_box jc_sb ai_center">
								<view class="left_box flex_box ai_center">
									<view class="owner_portrait">
										<image :src="item.logo_thumb" class="image" v-if="item.logo_thumb"></image>
										<image src="../../static/no_image.jpg" class="image" v-else></image>
									</view>
									<text class="size_24 color_666">{{item.shop_name}}</text>
								</view>
								<view class="right_box">
									<text class="iconfont icon-find-liulan-alt color_ccc"></text>
									<text class="size_22 color_ccc">{{item.look_num ? item.look_num : '0'}}</text>
								</view>
							</view>
						</view>
					</view>
				</block>
				<block v-else>
					<dsc-not-content></dsc-not-content>
				</block>
			</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</scroll-view>
</template>

<script>
	import {
		mapState
	} from 'vuex'

	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniDrawer from '@/components/uni-drawer.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';

	import universal from '@/common/mixins/universal.js';

	export default {
		props: ["paly"],
		mixins: [universal],
		components: {
			uniIcons,
			uniDrawer,
			uniPopup,
			dscCommonNav,
			dscNotContent
		},
		data() {
			return {
				loading: true,
				mode: 'large',
				page: 1,
				size: 10,
				cou_id: 0,
				placeholder: this.$t('lang.enter_search_keywords'),
				dscLoading: true,
				list: [],
				popupInfo: null
			};
		},
		onShareAppMessage(res) {
			if (res.from === 'button') { // 来自页面内分享按钮
				return {
					title: this.popupInfo.goods_name,
					path: '/pagesC/goodsDetail/goodsDetail?id=' + this.popupInfo.goods_id
				}
			} else {
				return {
					title: this.$store.state.common.shopConfig.shop_title,
					path: '/pages/goodslist/videoList'
				}
			}
		},
		created() {
			this.getList(1);
			this.videoContext = uni.createVideoContext('movie');

			if (!this.controlVersion) {
				uni.setNavigationBarTitle({
					title: this.$t('lang.list')
				});
			}
		},
		methods: {
			getList(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				uni.request({
					url: this.websiteUrl + '/api/goods/goodsvideo',
					method: 'POST',
					data: {
						size: this.size,
						page: this.page,
						sort: 'goods_id',
						order: 'desc'
					},
					header: {
						'Content-Type': 'application/json',
						'X-Client-Hash': uni.getStorageSync('client_hash'),
						'token': uni.getStorageSync('token')
					},
					success: (res) => {
						let data = res.data.data
						if (!page) {
							this.list = this.list.concat(data);
						} else {
							this.list = data;
						}
						if (this.list.length >= 4) {
							this.mode = 'grid'
						} else {
							this.mode = 'large'
						}
						this.dscLoading = false
					},
					fail: (err) => {
						console.error(err)
					}
				})
			},
			getVideoInfoByUrl(videoUrl) {
				let videoDuration = '00:00';
				// uni.getVideoInfo({
				// 	src: videoUrl,
				// 	success: (res) => {
				// 		videoDuration = res.duration;
				// 	},
				// 	fail: (err) => {
				// 		uni.showToast({
				// 			title: err.errMsg,
				// 			icon: 'none'
				// 		})
				// 	}
				// })
				return videoDuration
			},
			showPopup(goods_id, index) {
				if (goods_id) this.lookVideo(goods_id, index);
				uni.navigateTo({
					url: '/pages/video/detail?goods_id=' + goods_id
				})
			},
			lookVideo(id, index) {
				  uni.request({
				  	url: this.websiteUrl + '/api/goods/videolooknum',
				  	method: 'GET',
				  	data: {
				  		goods_id: id
				  	},
				  	header: {
				  		'Content-Type': 'application/json',
				  		'X-Client-Hash': uni.getStorageSync('client_hash'),
				  		'token': uni.getStorageSync('token')
				  	},
				  	success: ({data: {status, data}}) => {
				  		if (status != 'success') return;
				  		this.list[index].look_num = data;
				  	},
				  	fail: (err) => {
				  		console.error(err)
				  	}
				  });
			},
			loadMore() {
				if (this.page * this.size == this.list.length) {
					this.page++
					this.getList()
				}
			},
		}
	}
</script>
<style scoped>
	.list {
		width: 100%;
		height: 100%;
	}

	.fab {
		position: fixed;
		right: 20px;
		bottom: 10px;
		z-index: 10;
		color: #FFFFFF;
	}

	.fab-item {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		margin-bottom: 10px;
	}

	.fab-item .fab-image {
		width: 30px;
		height: 30px;
		line-height: 32px;
		border-radius: 100%;
		background: rgba(255, 255, 255, .7);
		display: flex;
		justify-content: center;
	}

	.fab-item .text {
		margin-top: 10px;
		line-height: 1;
	}

	/* #ifdef MP-WEIXIN */
	.fab {
		bottom: 50px;
	}

	/* #endif */

	.button-fab-item.fab-item {
		background: none !important;
	}

	.button-fab-item.fab-item {
		border: 0 !important;
		border-radius: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		font-size: 28upx !important;
	}

	.button-fab-item.fab-item::after {
		border-radius: 0 !important;
		border: 0 !important;
		background: none !important;
	}

	.button-fab-item.fab-item.button-hover {
		background: none !important;
	}

	.secrch-warp .input-text .search-check {
		width: 92upx;
		height: 92upx;
		top: 0;
		right: 0;
	}

	.secrch-warp .input-text .search-check .iconfont {
		position: absolute;
		left: 50%;
		top: 50%;
		transform: translate(-50%, -50%);
	}

	.videolist {
		padding: 20upx 15upx 10upx;
	}
	.videolist .videolist-item {
		background-color: #FFFFFF;
		border-radius: 20upx;
		overflow: hidden;
		margin-bottom: 20upx;
	}

	.video-list-grid {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
	}

	.video-list-grid .videolist-item {
		width: 354upx;
		height: 560upx;
		background-color: #FFFFFF;
		border-radius: 20upx;
		overflow: hidden;
		margin-bottom: 13upx;
	}

	.videolist-item .img {
		position: relative;
		width: 720upx;
		height: 720upx;
	}

	.video-list-grid .videolist-item .img {
		position: relative;
		width: 354upx;
		height: 354upx;
	}

	.video_duration {
		position: absolute;
		top: 26upx;
		left: 16upx;
		width:38upx;
		height:38upx;
		line-height: 1;
		background: rgba(0, 0, 0, .5);
		text-align: center;
		border-radius:50%;
		z-index: 3;
	}

	.video_duration .icon-play1 {
		font-size: 12upx;
	}

	.video_duration .icon-find-broadcast {
		margin-right: 6upx;
	}

	.videolist-item .img .image {
		width: 100%;
		height: 100%;
	}

	.videolist-item .info {
		padding: 26upx 20upx 30upx;
	}

	.videolist-item .info .name {
		line-height: 1.4;
		margin-bottom: 15upx;
	}

	.left_box .owner_portrait {
		width: 44upx;
		height: 44upx;
		margin-right: 15upx;
	}

	.left_box .owner_portrait .image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.right_box .icon-find-liulan-alt {
		font-size: 14upx;
		vertical-align: middle;
		margin-right: 8upx;
	}



</style>
