<template>
	<view class="team-list-content">
		<view class="hd-nav">
			<view :class="['hd-nav-item', index == current ? 'activ-nav' : '']" v-for="(item, index) in navList" :key="index" @click="tapNav(index)">
				<text>{{item.name}}</text>
				<text :class="['iconfont', 'size_24', index != current ? 'color_ccc' : '', isSort && index == current ? 'icon-arrow-up' : 'icon-arrow-down']" v-if="item.showSort"></text>
			</view>
		</view>
		<view class="team-main">
			<view :class="['team-item', index != 0 ? 'team-margin' : '']" v-for="(item, index) in goodsList" :key="index" @click="linkHref(item)">
				<image class="team-item-img" :src="item.goods_thumb"></image>
				<view class="team-item-right">
					<view class="team-name-box">
						<view class="team-name">{{item.goods_name}}</view>
						<text class="team-attr">单价买{{item.shop_price}}</text>
					</view>
					<view class="item-right-bottom">
						<text class="team-count">{{item.team_num}}人团</text>
						<text class="team-price">{{item.team_price}}</text>
					</view>
				</view>
			</view>
			<view class="loadmore-box" v-if="goodsList.length > 1">{{isOver ? '没有更多了' : '加载中...'}}</view>
			<dsc-not-content v-if="goodsList.length  == 0 && !isLoading"></dsc-not-content>
		</view>
	</view>
</template>

<script>
	import dscNotContent from "@/components/dsc-not-content.vue"
	export default {
		components: {
			dscNotContent
		},
		data() {
			return {
				navList: [{name:'综合', showSort: true}, {name:'新品', showSort: false},{name:'销量', showSort: false},{name:'价格', showSort: true}],
				current: 0,
				size: 10,
				page: 1,
				isSort: false,
				sortKey: 0,
				isOver: false,
				tcId: '',
				brandId: '',
				goodsList: [],
				isLoading: false
			}
		},
		onLoad({id, brand_id}) {
			this.tcId = id || ''
			this.brandId = brand_id || ''
			this.getListData()
		},
		onReachBottom() {
			if (this.isOver) return
			
			this.page = this.page + 1
			
			this.getListData()
		},
		methods: {
			linkHref(item){
				uni.navigateTo({
					url:"/pagesA/team/detail/detail?goods_id="+item.goods_id+"&team_id=0"
				})
			},
			tapNav(i) {
				
				if (i == 0) {
					this.sortKey = 0
					
					if (i == this.current) {
						this.isSort = !this.isSort
					} else {
						this.isSort = false
					}
				} else if (i == 1) {
					if (i == this.current) return
					
					this.sortKey = 1
				} else if (i == 2) {
					if (i == this.current) return
					
					this.sortKey = 2
				} else {
					this.sortKey = 3
					
					if (i == this.current) {
						this.isSort = !this.isSort
					} else {
						this.isSort = false
					}
				}
				 
				this.current = i
				
				this.goodsList = []
				
				this.page = 1
				
				this.isOver = false
				
				this.getListData()
			},
			async getListData() {
				if (this.page == 1) {
					uni.showLoading({
						mask: true,
						title: '加载中'
					})
				}
				this.isLoading = true
				
				let [error, res] = await uni.request({
				    url: this.websiteUrl+'/api/team/goods_list',
				    data: {
				        tc_id: this.tcId,
						brand_id: this.brandId,
				        keyword: '',
				        sort_key: this.sortKey,
				        sort_value: this.isSort ? 'ASC' : 'DESC',
				        size: this.size,
				        page: this.page
				    },
				    header: {
				        'Content-Type': 'application/json',
				        'token': uni.getStorageSync('token'),
				        'X-Client-Hash': uni.getStorageSync('client_hash')
				    }
				})
				if (this.page == 1) uni.hideLoading();
				
				this.isLoading = false
				
				if (res.data.status != 'success') {
					
					switch (res.data.errors.code) {
					    case 12:
					        uni.showToast({
					            title: res.data.errors.message ? res.data.errors.message : '用户未登录',
					            icon: 'none'
					        })
					
					        setTimeout(() => {
					            uni.navigateTo({
					                url: '/pagesB/login/login?delta=1'
					            })
					        }, 1000)
					        break
					    case 102:
					        uni.showToast({
					            title: res.data.errors.message ? res.data.errors.message : '用户数据错误，请重新登录',
					            icon: 'none'
					        })
							
							uni.removeStorageSync("token");
							
							setTimeout(() => {
							    uni.navigateTo({
							        url: '/pagesB/login/login?delta=1'
							    })
							}, 1000)
					        break
					    default:
							uni.showToast({
								title: res.data.errors.message,
								icon: 'none'
							})
					    	break;
					}
				} else {
					this.isOver = this.size > res.data.data.length
					
					this.goodsList = [...this.goodsList, ...res.data.data]
					
				}
			}
		}
	}
</script>

<style scoped>
.team-list-content {
	padding-top: 80rpx;
}
.hd-nav {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	display: flex;
	justify-content: space-between;
	align-items: center;
	background-color: #fff;
	z-index: 3;
}
.hd-nav-item {
	flex: 1;
	display: flex;
	justify-content: center;
	align-items: center;
	height: 80rpx;
}
.iconfont {
	transform: translateY(4rpx);
}
.activ-nav {
	color: #f92028;
}
.team-main {
	padding: 20rpx;
}
.team-item {
	display: flex;
	padding: 20rpx;
	background-color: #fff;
	border-radius: 10rpx;
}
.team-margin {
	margin-top: 20rpx;
}
.team-item-img {
	display: block;
	width: 220rpx;
	height: 220rpx;
}
.team-item-right {
	flex: 1;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	margin-left: 20rpx;
}
.team-name {
	overflow: hidden;
    word-break: break-all;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
	line-height: 1.6;
}
.team-attr {
	font-size: 24rpx;
	color: #999;
}
.item-right-bottom {
	
}
.team-count {
	color: #f92028;
	font-size: 30rpx;
}
.team-price {
	margin-left: 20rpx;
	color: #f92028;
	font-size: 32rpx;
	font-weight: bold;
}
.loadmore-box {
	margin-top: 10rpx;
	text-align: center;
}
</style>
