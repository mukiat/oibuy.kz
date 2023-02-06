<template>
	<view class="container comment_content">
		<view class="product_list">
			
			<view class="tabs">
				<view :class="['tab_item', activeTab == 0 ? 'active_tab' : '']" @click="switchTab(0)">待评价<text class="await_count">{{ signNum && activeTab == 0 ? ' · ' + signNum : '' }}</text></view>
				<view :class="['tab_item', activeTab == 1 ? 'active_tab' : '']" @click="switchTab(1)">已评价{{showEvaluate ? '/追评' : ''}}</view>
			</view>
			
			<template v-if="activeTab == 0">
				<view :class="['product_item', index > 0 ? 'u-border-top' : '']" v-for="(item,index) in commentList" :key="index" @click="goLink(`/pagesC/goodsDetail/goodsDetail?id=${item.goods_id}`)">
					<view class="g_title">{{item.shop_name}}</view>
					<view class="goods_info">
						<image class="goods_img" :src="item.goods_thumb"></image>
						<view class="goods_name">
							<view class="text_2">{{ item.goods_name }}</view>
							<view class="btns" v-if="item.can_evaluate">
								<view class="evaluate_btn" @click.stop="goLink(`../commentDetail/commentDetail?id=${item.rec_id}&type=0`)">评价</view>
							</view>
						</view>
					</view>
				</view>
				<template v-if="commentList.length == 0">
					<dsc-not-content></dsc-not-content>
				</template>
			</template>
			
			<template v-if="activeTab == 1">
				<view :class="['product_item', 'have_evaluation', index == 0 ? 'border_top_0' : '']" v-for="(item,index) in commentList" :key="index" @click="goLink(`/pagesC/goodsDetail/goodsDetail?id=${item.goods_id}`)">
					<view class="g_title">{{item.shop_name}}</view>
					<view class="goods_info">
						<image class="goods_img" :src="item.goods_thumb"></image>
						<view class="goods_name">
							<view class="text_1">{{ item.goods_name }}</view>
							<view class="rate_wrap">
								<text>评分</text>
								<text :class="['iconfont', 'icon-collection-alt', 'size_24', rIndex < item.comment_rank ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></text>
							</view>
						</view>
					</view>
					<block v-for="(comItem,comIndex) in item.comment" :key="comIndex">
						<view class="pro_content" v-if="comItem.add_comment_id == 0">
							<text class="no_comment">{{comItem.content}}</text>
							<view class="img_list" v-if="comItem.comment_img_list.length">
								<image class="goods_img" v-for="(imgItem,imgIndex) in comItem.comment_img_list" :key="imgIndex" :src="imgItem.comment_img"></image>
							</view>
							<view class="btn_wrap" v-if="item.can_add_evaluate">
								<view class="additional_review" @click.stop="goLink(`../commentDetail/commentDetail?id=${item.rec_id}&type=1`)">追评</view>
							</view>
						</view>
						<view class="pro_content u-border-top" v-if="comItem.add_comment_id > 0">
							<view class="title">追评</view>
							<text class="no_comment">{{comItem.content}}</text>
							<view class="img_list" v-if="comItem.comment_img_list.length">
								<image class="goods_img" v-for="(imgItem,imgIndex) in comItem.comment_img_list" :key="imgIndex" :src="imgItem.comment_img"></image>
							</view>
						</view>
					</block>
				</view>
				<template v-if="commentList.length == 0">
					<dsc-not-content></dsc-not-content>
				</template>
			</template>
		</view>
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	export default {
		data() {
			return {
				sign:0,
				signNum: '',
				page:1,
				size:10,
				order_id:0,
				activeTab: 0,
				showEvaluate: 0
			};
		},
		components:{
			dscNotContent,
			dscCommonNav
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/comment/comment'
			}
		},
		computed:{
			commentList:{
				get(){
					return this.$store.state.user.commentList
				},
				set(val){
					this.$store.state.user.commentList = val  
				}
			}
		},
		methods:{
			// 切换tab
			switchTab(i) {
				if (this.activeTab == i) return;
				this.page = 1;
				this.activeTab = i;
			},
			// 页面跳转
			goLink(url) {
				uni.navigateTo({url})
			},
			commentListHandle(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setCommentList',{
					sign:this.activeTab,
					page:this.page,
					size:this.size,
					id:this.order_id
				})
				uni.request({
					url: this.websiteUrl + '/api/comment/order_goods_title',
					method: 'POST',
					data: {
						id:this.order_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.signNum = res.data.data.signNum0 || ''
						this.showEvaluate = res.data.data.add_evaluate || 0
					}
				})
				
			}
		},
		onLoad(e){
			this.order_id = e.order_id || 0;
			this.activeTab = e.have || 0;
			this.commentListHandle()
		},
		onReachBottom(){
			if(this.page * this.size == this.commentList.length){
				this.page ++
				this.commentListHandle()
			}
		},
		watch: {
			activeTab: 'commentListHandle'
		}
	}
</script>

<style lang="scss" scoped>
.comment_content {
	padding: 20rpx 0;
	
	.u-border-top {
		position: relative;
		&::after {
			content: ' ';
			position: absolute;
			left: 0;
			top: 0;
			pointer-events: none;
			box-sizing: border-box;
			-webkit-transform-origin: 0 0;
			transform-origin: 0 0;
			// 多加0.1%，能解决有时候边框缺失的问题
			width: 199.8%;
			height: 199.7%;
			transform: scale(0.5, 0.5);
			border: 0 solid #e4e7ed;
			border-top-width: 1px;
			z-index: 2;
		}
	}
	
	.product_list {
		overflow: hidden;
		border-radius: 20rpx;
	}
	.tabs {
		display: flex;
		align-items: center;
		justify-content: space-around;
		height: 100rpx;
		background-color: #fff;
		.tab_item {
			position: relative;
			font-size: 30rpx;
			.await_count {
				position: absolute;
				right: -50rpx;
				top: 50%;
				transform: translateY(-50%);
			}
		}
		.active_tab {
			font-weight: 700;
			&:after {
				content: '';
				position: absolute;
				left: 50%;
				bottom: -12rpx;
				transform: translateX(-50%);
				width: 100%;
				height: 6rpx;
				background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
			}
		}
	}
	.product_item {
		padding: 20rpx 0 40rpx;
		background-color: #fff;
		.g_title {
			font-size: 30rpx;
			font-weight: bold;
			line-height: 1;
			padding: 20rpx;
		}
		.goods_info {
			display: flex;
			padding: 0 20rpx;
			.goods_img {
				flex: none;
				width: 180rpx;
				height: 180rpx;
			}
			.goods_name {
				flex: auto;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				margin-left: 20rpx;
				.text_2 {
					line-height: 38rpx;
				}
				.btns {
					display: flex;
					justify-content: flex-end;
				}
				.evaluate_btn {
					padding: 0 30rpx;
					height: 48rpx;
					line-height: 48rpx;
					border-radius: 26rpx;
					text-align: center;
					font-size: 30rpx;
					color: #f92028;
					border: 1px solid #f92028;
				}
			}
		}
	}
	.have_evaluation {
		border-radius: 20rpx;
		margin-bottom: 20rpx;
		.goods_info {
			.goods_img {
				width: 120rpx;
				height: 120rpx;
			}
			.goods_name {
				overflow: hidden;
				justify-content: flex-start!important;
			}
		}
		.rate_wrap {
			margin-top: 10rpx;
			line-height: 1;
			.icon-collection-alt {
				margin-left: 10rpx;
				line-height: 1;
				color: #DDD;
			}
			.color_red {
				color: #E93B3D;
			}
		}
		.pro_content {
			padding: 20rpx;
			.title {
				font-weight: bold;
			}
			.btn_wrap {
				display: flex;
				justify-content: flex-end;
				margin-top: 20rpx;
				.additional_review {
					padding: 0 30rpx;
					height: 48rpx;
					line-height: 48rpx;
					border-radius: 26rpx;
					text-align: center;
					font-size: 30rpx;
					border: 1px solid #ccc;
				}
			}
			.img_list {
				display: flex;
				flex-wrap: wrap;
				.goods_img {
					width: 124rpx;
					height: 124rpx;
					margin: 16rpx 16rpx 0 0;
					border-radius: 10rpx;
				}
			}
		}
	}
	.border_top_0 {
		border-top-left-radius: 0;
		border-top-right-radius: 0;
	}
}
</style>
