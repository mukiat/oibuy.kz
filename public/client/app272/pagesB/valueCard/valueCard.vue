<template>
	<view class="value_cart_content">
		<view class="header_wrap">
			<view class="use_price">可用余额 {{ currency_format }}<text>{{valueCartInfo.card_total || 0.00}}</text></view>
		</view>
		<view class="cart_tabs">
			<view :class="['tab_item', index == currTab ? 'active_tab' : '']" v-for="(item, index) in tabs" :key="index" @click="onClickTab(index)">
				{{item}}{{index == currTab ? `(${cardCount})` : ''}}
			</view>
		</view>
		<view v-for="(item, index) in cartList" :key="index">
			<view class="list_wrap" v-show="currTab == index">
				<dsc-value-cart :list="item" :type="index" @link="goLink"></dsc-value-cart>
				<dsc-not-content v-if="shopEmpty"></dsc-not-content>
				<view class="loadmore" v-if="item.length >= size">{{loadmoreStatus}}</view>
			</view>
		</view>
		<view class="btn-bar btn-bar-fixed">
			<view class="btn" @click="onClickBigBtn">{{$t('lang.bind_value_card')}}</view>
		</view>
	</view>
</template>

<script>
	import dscValueCart from '@/components/dsc-value-cart/dsc-value-cart.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				size:10,
				currTab: 0,
				tabs: ['可用卡', '不可用卡'],
				loadmoreStatus: '加载中...',
				shopEmpty: false,
				cartList: [],
				cartPaginated: [],
				valueCartInfo: {},
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
			};
		},
		components:{
			dscValueCart,
			dscNotContent
		},
		computed: {
			valuecardBg(){
				return this.websiteUrl + '/img/ka_bg.jpg'
			},
			cardCount: function () {
				if (this.valueCartInfo.card_list) {
					if (this.currTab == 0) {
						return this.valueCartInfo.use_card_count || 0
					} else {
						return this.valueCartInfo.not_use_card_count || 0
					}
				} else {
					return 0
				}
			}
		},
		onReachBottom() {
			let i = this.currTab;
			let isMore = this.cartPaginated[i];
			if (isMore > 0) {
				this.loadmoreStatus = '加载中...';
				this.valueCardLoad();
			} else {
				this.loadmoreStatus = '没有更多了';
			}
		},
		methods:{
			async valueCardLoad() {
				let i = this.currTab;
				
				if (this.cartList.length == 0) this.cartList = this.tabs.map(() => []);
				if (this.cartPaginated.length == 0) this.cartPaginated = this.tabs.map(() => 1);
				
				let page = this.cartList[i].length / this.size;
				
				page = Math.ceil(page) + 1
				
				const { data } = await this.$store.dispatch('getValueCard', {
					page: page,
					size: this.size,
					use_type: i == 0 ? 1 : 0
				});
				
				const { card_list } = data;
				
				this.valueCartInfo = data;
				
				if (card_list) {
					this.$set(this.cartPaginated, i, card_list.length < this.size ? 0 : 1);
					
					this.$set(this.cartList, i, [...this.cartList[i], ...card_list]);
					
					this.shopEmpty = this.cartList[i].length == 0;
					this.loadmoreStatus = card_list.length < this.size ? '没有更多了' : '加载中...';
				};
			},
			onClickBigBtn(){
				uni.navigateTo({
					url:'../valueCard/add/add'
				})
			},
			onClickTab(i) {
				if (this.currTab == i) return;
				this.currTab = i;
				this.shopEmpty = false;
				if (this.cartList[i].length == 0) this.valueCardLoad();
			},
			goLink(res) {
				const { type, value: { vid } } = res;
				if (type == 'add') uni.navigateTo({url: `../valueCard/add/add?type=deposit&vc_id=${vid}`});
				if (type == 'detail') uni.navigateTo({url: `../valueCard/detail/detail?id=${vid}`});
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/valueCard/valueCard'
			}
		},
		onLoad(){
			this.valueCardLoad()
		}
	}
</script>

<style lang="scss" scoped>
.value_cart_content {
	padding-bottom: 120rpx;
	.header_wrap {
		overflow: hidden;
		position: relative;
		display: flex;
		justify-content: center;
		align-items: center;
		height: 188rpx;
		&:after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);
			width: 125%;
			height: 150%;
			border-radius: 50%;
			background-color: #E83C2D;
		}
		.use_price {
			position: relative;
			color: #fff;
			z-index: 2;
			text {
				font-size: 44rpx;
				font-weight: 700;
			}
		}
	}
	.cart_tabs {
		display: flex;
		justify-content: space-around;
		align-items: center;
		padding: 30rpx 0;
		.active_tab {
			position: relative;
			color: #E83C2D;
			&:after {
				content: '';
				position: absolute;
				bottom: -10rpx;
				left: 0;
				width: 100%;
				height: 4rpx;
				background-color: #E83C2D;
			}
		}
	}
	.list_wrap {
		padding: 0 40rpx;
	}
	.loadmore {
		height: 60rpx;
		text-align: center;
	}
}
</style>
