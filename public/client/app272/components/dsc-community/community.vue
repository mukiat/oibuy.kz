<template>
	<scroll-view class="community_container" :class="{'community_container_list':routerName == 'tab'}" scroll-y @scrolltolower="loadMore">
		<block  v-if="!dscLoading">
			<view :class="['videolist', mode !== 'grid' ? 'video-list-grid' : '']">
				<block v-if="list.length > 0">
					<view class="videolist-item flex_box fd_column jc_sb" v-for="(item,index) in list" :key="index" @click="goDetail(item.comment_id)">
						<view class="img">
							<!-- <view class="video_duration">
								<text class="iconfont icon-find-broadcast size_22 color_fff"></text>
								<text class="duration size_26 color_fff">{{ item.goods_video ? getVideoInfoByUrl(item.goods_video) : '00:00'}}</text>
							</view> -->
							<image :src="item.img" class="image" mode="aspectFill" v-if="item.img"></image>
							<image src="../../static/no_image.jpg" mode="aspectFill" class="image" v-else></image>
						</view>
						<view class="info flex_1 flex_box fd_column jc_sb">
							<text class="name size_30 color_333 text_2 weight_700">{{item.goods_name}}</text>
							<view class="flex_box jc_sb ai_center">
								<view class="left_box flex_box ai_center">
									<view class="owner_portrait">
										<image :src="item.user_picture" class="image"></image>
									</view>
									<text class="size_24 color_666">{{item.user_name}}</text>
								</view>
								<view class="right_box">
									<text class="iconfont icon-find-liulan-alt color_ccc"></text>
									<text class="size_22 color_ccc">{{item.dis_browse_num ? item.dis_browse_num : '0'}}</text>
								</view>
							</view>
						</view>
					</view>
					<text class="dsc_loading_box color_666" v-show="list.length > 4">{{isOver ? $t('lang.no_more') : $t('lang.loading')}}</text>
				</block>
				<block v-else>
					<view style="margin: 0 auto;">
						<dsc-not-content></dsc-not-content>
					</view>
				</block>
			</view>
		</block>
		<block v-if="routerName == 'tab'">
			<uni-load-more status="loading" :type="false" v-if="list.length == 0" />
		</block>
		<dsc-loading :dscLoading="dscLoading" v-else></dsc-loading>
	</scroll-view>
</template>

<script>
import dscNotContent from '@/components/dsc-not-content.vue';
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
export default {
	props: {
		routerName:{
			type:String,
			default:''
		},
		scrollPickOpen:{
			type:[Boolean,String],
			default:''
		}
	},
	components: {
		dscNotContent,
		uniLoadMore
	},
	data() {
		return {
			dscLoading: true,
			list: [],
			mode: 'large',
			isOver: false,
			isLoading: false,
			page: 1,
			size: 10
		}
	},
	created() {
		this.getList();
		uni.$on('lookComment', ({id}) => {
			let i = this.list.findIndex(item => item.comment_id == id);
			this.list[i].dis_browse_num = this.list[i].dis_browse_num + 1;
		});
	},
	beforeDestroy() {
		uni.$off('lookComment');
	},
	methods: {
		getList() {
			this.isLoading = true;
			uni.request({
				url: this.websiteUrl + '/api/discover/find_list',
				method: 'GET',
				data: {
					size: this.size,
					page: this.page
				},
				header: {
					'Content-Type': 'application/json',
					'X-Client-Hash': uni.getStorageSync('client_hash'),
					'token': uni.getStorageSync('token')
				},
				success: ({data: {status, data}}) => {
					this.dscLoading = false;
					this.isLoading = false;
					this.$emit('updateScrollPickOpen2',false)
					if (status == 'success') {
						this.list = [...this.list, ...data];
						if (data.length < this.size) this.isOver = true;
					}
				},
				fail: (err) => {
					this.isLoading = false;
				}
			})
		},
		goDetail(id) {
			uni.navigateTo({
				url: `/pages/comment/commentList?id=${id}`
			})
		},
		loadMore() {
			if (this.isOver || this.isLoading) return;
			this.page++
			this.getList()
		}
	},
	watch:{
		scrollPickOpen(){
			if (this.isOver || this.isLoading) return;
			this.page ++
			this.getList()
		}
	}
}
</script>

<style scoped>
	.community_container {
		width: 100%;
		height: 100%;
	}
	.videolist {
		padding: 20upx 15upx 0;
	}
	.videolist .videolist-item {
		width: 100%;
		height: calc(720upx * 1.582);
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
		height: 684upx;
		background-color: #FFFFFF;
		border-radius: 20upx;
		overflow: hidden;
		margin-bottom: 13upx;
	}

	.videolist-item .img {
		position: relative;
		width: 100%;
		height: calc(100% * 0.695);
		overflow: hidden;
	}

	.video_duration {
		position: absolute;
		top: 26upx;
		left: 16upx;
		width:128upx;
		height:38upx;
		background: rgba(0, 0, 0, .5);
		line-height: 36upx;
		text-align: center;
		border-radius:19upx;
		z-index: 3;

	}
	.duration {
		padding-top: 2upx;
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
	.dsc_loading_box {
		width: 100%;
		padding: 20upx;
		text-align: center;
	}

	.community_container_list .videolist{
		padding: 0;
	}
	.community_container_list .video-list-grid .videolist-item{
		width: 48.5%;
	}
</style>
