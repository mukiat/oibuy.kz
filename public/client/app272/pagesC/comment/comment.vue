<template>
	<view class="comment_content">
		<!-- tabs start -->
		<view class="wrap_box">
			<view class="title_box">
				<view class="title_left">
					<text>{{commentTotal.total > 0 ? $t('lang.comment') : $t('lang.no_comment')}}</text>
					<text class="comment_count" v-if="commentTotal.total > 0">{{commentTotal.total}}{{$t('lang.tiao')}}</text>
				</view>
				<view class="title_right">
					<text class="color_999" v-if="commentTotal.total > 0">好评度{{commentTotal.good}}</text>
				</view>
			</view>
			<view class="nav_list">
				<view :class="['nav_item', currNav == index ? 'curr_nav' : '', item.tag_name ? 'nav_li' : '']" v-for="(item, index) in commentTabs" :key="index" @click="toggleType(index)">{{item.title}} {{item.count}}</view>
			</view>
		</view>
		<!-- tabs end -->
		
		<!-- 评价列表 start -->
		<view class="mt20">
			<block v-for="(list, listIndex) in goodsCommentList" :key="listIndex">
				<view class="comment-items" v-show="listIndex == currNav">
					<dsc-not-content v-if="shopEmpty"></dsc-not-content>
					<view class="comitem wrap_box" v-for="(item, index) in list" :key="index">
						<view class="item_header">
							<image class="head_l" :src="item.user_picture" v-if="item.user_picture"></image>
							<image class="head_l" src="/static/get_avatar.jpg" v-else></image>
							<view class="head_r">
								<view class="com_name">{{item.user_name}}</view>
								<view class="com_time">
									<view class="rate_wrap">
										<text :class="['iconfont', 'icon-collection-alt', 'size_24', rIndex < item.rank ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></text>
									</view>
									<text class="comment_time">{{ item.add_time }}</text>
								</view>
							</view>
						</view>
						<view class="item_body">
							<view class="comment_con">{{ item.content }}</view>
							<view class="imgs_scroll" v-if="item.comment_img">
								<block v-for="(val, ind) in item.comment_img" :key="ind">
									<image class="com_img" mode="aspectFill" :lazy-load="true" :src="val" @click="previewImg(ind, item.comment_img)" v-if="val"></image>
									<image class="com_img" mode="aspectFill" src="/static/no_image.jpg" v-else></image>
								</block>
							</view>
						</view>
						<view class="item_footer" v-if="item.goods_attr">{{item.goods_attr}}</view>
						<view class="item_body add_comment" v-if="item.add_comment.comment_id">
							<view class="title">用户{{item.add_comment.add_time_humans}}追评</view>
							<view class="comment_con">{{ item.add_comment.content }}</view>
							<view class="imgs_scroll" v-if="item.add_comment.get_comment_img && item.add_comment.get_comment_img.length > 0">
								<block v-for="(img, imgIndex) in item.add_comment.get_comment_img" :key="imgIndex">
									<image class="com_img" mode="aspectFill" :lazy-load="true" :src="img" @click="previewImg(imgIndex, item.add_comment.get_comment_img)" v-if="img"></image>
									<image class="com_img" mode="aspectFill" src="/static/no_image.jpg" v-else></image>
								</block>
							</view>
						</view>
						<view class="reply_content" v-if="item.re_content">
							<view class="re_label">{{$t('lang.admin_reply')}}：</view>
							<view class="re_content">{{item.re_content}}</view>
						</view>
					</view>
				</view>
			</block>
		</view>
		<!-- 评价列表 end -->
		<view class="loading_txt" v-if="goodsCommentList.length > 0 && goodsCommentList[currNav].length >= size">{{ paginated[currNav] == 1 ? $t('lang.loading') : $t('lang.no_more') }}</view>
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
				goodsId: '',
				commentTotal: {},
				goodsCommentList: [],
				paginated: [],
				size: 10,
				currNav: 0,
				shopEmpty: false,
				commentTabs: [
					{
						title: this.$t('lang.all'),
						type: 'all',
						count: 0
					},
					{
						title: this.$t('lang.issue_img'),
						type: 'img',
						count: 0
					},
					{
						title: this.$t('lang.good_comment'),
						type: 'good',
						count: 0
					},
					{
						title: this.$t('lang.medium_comment'),
						type: 'in',
						count: 0
					},
					{
						title: this.$t('lang.negative_comment'),
						type: 'rotten',
						count: 0
					}
				]
			}
		},
		onLoad(opt) {
			this.goodsId = opt.id || '';
			this.getCommentTitle();
		},
		onReachBottom() {
			if (this.paginated[this.currNav] == 1) {
				this.getCommentList();
			}
		},
		methods: {
			showToast(msg = '请稍后重试！') {
				uni.showToast({
					title: msg,
					icon: 'none'
				})
			},
			// 图片预览
			previewImg(i, imgs = []) {
				uni.previewImage({
					current: i,
					urls: imgs
				})
			},
			// 获取标题
			async getCommentTitle() {
				const { data, status } = await this.$store.dispatch('getCommentTotalById', {goods_id: this.goodsId});
				if (status != 'success') return this.showToast();
				this.commentTotal = {
					total: data.all || 0,
					good: parseInt(data.good / data.all * 100) + '%'
				};
				this.commentTabs = this.commentTabs.map(item => {
					item.count = data[item.type];
					return item;
				});
				if (data.comment) {
					const arr = data.comment.map(item => {
						item.type = 'all';
						item.title = item.tag_name;
						return item;
					})
					this.commentTabs = [...arr, ...this.commentTabs];
					this.currNav = arr.length;
				};
				this.getCommentList();
			},
			// 获取商品评论
			async getCommentList() {
				let i = this.currNav;
				
				if (this.goodsCommentList.length == 0) this.goodsCommentList = this.commentTabs.map(() => []);
				if (this.paginated.length == 0) this.paginated = this.commentTabs.map(() => 1);
				
				let page = this.goodsCommentList[i].length / this.size;
				
				page = Math.ceil(page) + 1;
				
				uni.showLoading({
					mask: true,
					title: '加载中'
				});
				
				const { data } = await this.$store.dispatch('getGoodsCommentById', {
					goods_id: this.goodsId,
					rank: this.commentTabs[i].type,
					page: page,
					size: this.size,
					goods_tag: this.commentTabs[i].tag_name || ''
				});
				
				uni.hideLoading();
				
				if (Array.isArray(data)) {
					this.$set(this.goodsCommentList, i, [...this.goodsCommentList[i], ...data]);
					if (data.length < this.size) this.$set(this.paginated, i, 0);
				};
				
				this.shopEmpty = this.goodsCommentList[i].length == 0;
			},
			// 切换评价
			toggleType(i) {
				this.shopEmpty = false;
				if (this.currNav == i) return;
				this.currNav = i;
				
				if (this.goodsCommentList[i].length > 0) return;
				this.getCommentList();
			} 
		}
	}
</script>

<style lang="scss" scoped>
.comment_content {
	padding: 20rpx;
	.wrap_box {
		border-radius: 22rpx;
		background-color: #fff;
	}
	.mt20 {
		margin-top: 20rpx;
	}
	.title_box {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 30rpx;
		.title_left {
			position: relative;
			font-weight: 700;
			padding-left: 16rpx;
			&:before {
				position: absolute;
				top: 50%;
				left: 0;
				transform: translateY(-50%);
				content: '';
				width: 6rpx;
				height: 32rpx;
				background: linear-gradient(180deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
			}
			.comment_count {
				margin-left: 30rpx;
				font-weight: normal;
			}
		}
	}
	.nav_list {
		display: flex;
		flex-wrap: wrap;
		padding: 0 10rpx 0 30rpx;
		.nav_item {
			padding: 0 30rpx;
			height: 56rpx;
			margin: 0 20rpx 30rpx 0;
			line-height: 56rpx;
			text-align: center;
			border-radius: 28rpx;
			border: 1px solid #FDF0EF;
			color: #000;
			background-color: #FDF0EF;
			box-sizing: border-box;
		}
	
		.nav_li {
			border-color: #ddd;
			background-color: #fff;
		}
		.curr_nav {
			color: #F91F28;
			border-color: #FDF0EF;
			background-color: #FDF0EF;
		}
	}
	.comment-items {
		.comitem {
			padding: 30rpx 0;
			&:nth-child(n + 2) {
				margin-top: 20rpx;
			}
		}
		.item_header {
			display: flex;
			align-items: center;
			padding: 0 30rpx;
			.head_l {
				flex: none;
				width: 68rpx;
				height: 68rpx;
				border-radius: 50%;
			}
			.head_r {
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				height: 68rpx;
				margin-left: 20rpx;
			}
			.com_name {
				line-height: 1;
			}
			.com_time {
				display: flex;
				justify-content: space-between;
				.comment_time {
					color: #999;
					line-height: 1;
				}
			}
			.rate_wrap {
				line-height: 1;
				.icon-collection-alt {
					margin-right: 10rpx;
					line-height: 1;
					color: #DDD;
				}
				.color_red {
					color: #E93B3D;
				}
			}
		}
		.item_body {
			padding-top: 30rpx;
			.comment_con {
				margin: 0 30rpx;
				line-height: 40rpx;
			}
			.imgs_scroll {
				display: flex;
				flex-wrap: wrap;
				padding: 30rpx 15rpx 0;
				.com_img {
					width: 29.33%;
					height: 200rpx;
					margin: 0 2%;
					border-radius: 10rpx;
					&:nth-child(n + 4) {
						margin-top: 30rpx;
					}
				}
			}
		}
		.item_footer {
			margin: 20rpx 30rpx 0;
			line-height: 40rpx;
			word-break:break-all;
			display:-webkit-box;
			-webkit-line-clamp:2;
			-webkit-box-orient:vertical;
			font-size: 26rpx;
			overflow:hidden;
			color: #999;
		}
		.reply_content {
			display: flex;
			align-items: baseline;
			padding: 20rpx 30rpx;
			margin: 30rpx 30rpx 0;
			border-radius: 10rpx;
			background-color: #F2F2F2;
			.re_label {
				flex: none;
			}
			.re_content {
				flex: auto;
				line-height: 40rpx;
			}
		}
	}
	.loading_txt {
		margin-top: 20rpx;
		text-align: center;
	}
	.add_comment {
		padding-top: 0!important;
		.title {
			margin: 16rpx 30rpx 0;
			font-weight: bold;
		}
		.comment_con {
			padding-top: 16rpx!important;
		}
	}
}
</style>
