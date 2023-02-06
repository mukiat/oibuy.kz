<template>
	<view class="setmeal_content">
		<block v-for="(item, index) in fittingInfo.comboTab" :key="index">
			<view :class="['goods_module_wrap', index > 0 ? 'mt_20' : '']" v-if="tabList[index] > 0">
				<view class="title_box" @click="toggleTab(item.group_id)">
					<view class="title_left">
						<text>{{item.text}}</text>
					</view>
					<view class="title_right">
						<text :class="['iconfont', currTab == item.group_id ? 'icon-less' : 'icon-moreunfold', 'size_28']"></text>
					</view>
				</view>
				<view v-show="currTab == item.group_id">
					<view class="goods_item u-border-top">
						<view class="clicked_ico">
							<uni-icons type="checkbox-filled" size="20" color="#F91F28" v-if="true"></uni-icons>
							<uni-icons type="circle" size="20" color="#c8c9cc" v-else></uni-icons>
						</view>
						<view class="goods_content">
							<image class="goods_pic" :src="fittingInfo.goods.goods_thumb" v-if="fittingInfo.goods.goods_thumb"></image>
							<image class="goods_pic" src="/static/no_image.jpg" v-else></image>
							<view class="content-r">
								<view class="goods_name u-line-2">{{fittingInfo.goods.goods_name}}</view>
								<currency-price :price="fittingInfo.goods.shop_price || '0.00'"></currency-price>
							</view>
						</view>
					</view>
					<block v-for="(goodsItem,goodsindex) in fittingInfo.fittings" :key="goodsindex">
						<view class="goods_item u-border-top" v-if="item.group_id == goodsItem.group_id" @click="checkboxHandle(index, goodsItem.goods_id, goodsindex)">
							<view class="clicked_ico">
								<uni-icons type="checkbox-filled" size="20" color="#F91F28" v-if="fittingsCheckModel.includes(goodsItem.goods_id)"></uni-icons>
								<uni-icons type="circle" size="20" color="#c8c9cc" v-else></uni-icons>
							</view>
							<view class="goods_content">
								<image class="goods_pic" :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"></image>
								<image class="goods_pic" src="/static/no_image.jpg" v-else></image>
								<view class="content-r">
									<view class="goods_name u-line-2">{{goodsItem.goods_name}}</view>
									<currency-price :price="goodsItem.goods_price || '0.00'"></currency-price>
								</view>
							</view>
						</view>
					</block>
				</view>
			</view>
		</block>
		
		<!-- 底部提交栏 start -->
		<view class="setmeal_submit_bar safe-area-inset-bottom u-border-top">
			<view class="left_price">
				<view class="setmeal_price">{{$t('lang.package_price')}}：<text>{{fittings_minMax}}</text></view>
				<view class="save_price">{{$t('lang.save_money')}}：<text>{{save_minMaxPrice}}</text></view>
			</view>
			<button class="u-reset-button sub_btn red_btn" @click="fittingsAddCart">{{$t('lang.add_cart')}}</button>
		</view>
		<!-- 底部提交栏 end -->
	</view>
</template>

<script>
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	export default {
		components: {
			uniIcons
		},
		data() {
			return {
				id: '',
				fittings_minMax: 0.0,
				save_minMaxPrice: 0.0,
				fittingNames: '',
				fittingsCheckModel:[],
				goodsAttrInit: [],
				currTab: '',
				fittingInfo: {}
			};
		},
		computed: {
			tabList: function () {
				let i = 0, a = 0,arr=[];
				if (!this.fittingInfo.fittings) return arr;
				this.fittingInfo.fittings.forEach(v=>{
					if(v.group_id == 1){
						i++
					}else{
						a++
					}
				});
				
				arr = [i,a]

				return arr
			}
		},
		watch: {
			fittingInfo: 'toggleTab'
		},
		onLoad(opt) {
			this.id = opt.id || '';
			this.getFittingsById();
		},
		methods: {
			getFittingsById() {
				uni.request({
					url:this.websiteUrl + '/api/goods/fittings',
					method:'POST',
					data:{
						goods_id: this.id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data, status, errors}}) => {
						if (status != 'success' || errors) {
							return uni.showToast({
								icon: 'none',
								title: errors.message
							});
						}
						this.fittingInfo = data;
						if (data.goods && data.goods.goods_attr_id) this.goodsAttrInit = data.goods.goods_attr_id.toString().split(',').map(Number);
					},
					fail: (err) => {
						uni.showToast({
							icon: 'none',
							title: err.errMsg
						})
					}
				});
			},
			toggleTab(id) {
				if (id.comboTab) {
					this.fittingNames = this.fittingInfo.comboTab[0].group_id;
					this.currTab = this.fittingNames;
				} else {
					this.currTab = this.currTab == id ? '' : id;
					if (id == this.fittingNames) return;
					this.fittingNames = id;

					this.fittingsCheckModel = [];
					
					let group_name = 'm_goods_' + this.fittingNames;
					let group_id = group_name + '_' + this.id;    
					let spec = '';
					let arr = this.fittingInfo.fittings.filter(item=>{
						return item.group_id == this.fittingNames
					});

					const currGoodsId = arr.length > 0 ? arr[0].goods_id : this.fittingInfo.fittings[0].goods_id;
					uni.showLoading({
					    title: this.$t('lang.loading') + '...',
						mask: true
					});
					this.delcartCombo(currGoodsId,group_id,spec);
				}
			},
			checkboxHandle(ci, id, i) {
				uni.showLoading({
				    title: this.$t('lang.loading') + '...',
					mask: true
				});
				let group_name = 'm_goods_' + this.fittingNames;
				let group_id = group_name + '_' + this.id;    
				let spec = '';
				if (this.fittingsCheckModel.includes(id)) {
					this.fittingsCheckModel = this.fittingsCheckModel.filter(item => item != id);
					this.fittingInfo.fittings.some(item => {
						if (item.id == id) {
							spec = item.goods_attr_id;
							return true;
						}
					});
					this.delcartCombo(id,group_id,spec);
				} else {
					this.fittingsCheckModel.push(id);
					this.fittingInfo.fittings.some(item => {
						if (item.id == id) {
							spec = item.goods_attr_id;
							return true;
						}
					});
					uni.request({
						url:this.websiteUrl + '/api/cart/addToCartCombo',
						method:'POST',
						data:{
							goods_id: id,
							number: 1,
							spec: spec,
							parent_attr: this.goodsAttrInit,
							warehouse_id: 0,
							area_id: 0,
							area_city: 0,
							parent: this.id,
							group_id: group_id,
							add_group:''
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: ({data:{data, status, errors}}) => {
							uni.hideLoading();
							if (status != 'success' || errors) {
								return uni.showToast({
									icon: 'none',
									title: errors.message
								});
							}
							this.save_minMaxPrice = data.save_minMaxPrice
							this.fittings_minMax = data.fittings_minMax
						},
						fail: (err) => {
							uni.hideLoading();
							uni.showToast({
								icon: 'none',
								title: err.errMsg
							})
						}
					});
				}
			},
			delcartCombo(currGoodsId,group_id,spec){
				uni.request({
					url:this.websiteUrl + '/api/cart/delInCartCombo',
					method:'POST',
					data:{
						goods_id: currGoodsId,
						parent: this.id,
						group_id: group_id,
						spec: spec,
						goods_attr: this.goodsAttrInit,
						warehouse_id: 0,
						area_id: 0,
						area_city: 0
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data, status, errors}}) => {
						uni.hideLoading();
						if (status != 'success' || errors) {
							return uni.showToast({
								icon: 'none',
								title: errors.message
							});
						}
						this.save_minMaxPrice = data.save_minMaxPrice
						this.fittings_minMax = data.fittings_minMax
					},
					fail: (err) => {
						uni.hideLoading();
						uni.showToast({
							icon: 'none',
							title: err.errMsg
						})
					}
				});
			},
			fittingsAddCart(){
				uni.showLoading({
				    title: this.$t('lang.loading') + '...',
					mask: true
				});
			    let group_name = 'm_goods_' + this.fittingNames;
			    let group_id = group_name + '_' + this.id;
				uni.request({
					url:this.websiteUrl + '/api/cart/addToCartGroup',
					method:'POST',
					data:{
						group_name:group_name,
						goods_id:this.id,
						warehouse_id:0,
						area_id:0,
						area_city:0,
						number: this.fittingInfo.goods.is_minimum > 0 ? this.fittingInfo.goods.minimum : 1
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data, status}}) => {
						uni.hideLoading();
						if (status != 'success' || data.error !== 0) {
							return uni.showToast({
								icon: 'none',
								title: data.msg
							});
						}
						try{
							if (getApp().globalData.isShowCart) {
								uni.switchTab({
									url: '/pages/cart/cart'
								})
							} else {
								uni.reLaunch({
									url: '/pages/cart/cart'
								})
							}
						}catch(e){
							//TODO handle the exception
							uni.reLaunch({
								url: '/pages/cart/cart'
							})
						}
					},
					fail: (err) => {
						uni.hideLoading();
						uni.showToast({
							icon: 'none',
							title: err.errMsg
						})
					}
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
.setmeal_content {
	padding: 20rpx 20rpx 120rpx;
	.goods_module_wrap {
		border-radius: 22rpx;
		background-color: #fff;
		.title_box {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 20rpx 30rpx;
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
			.drgree_of_praise {
				margin-right: 8rpx;
			}
		}
		.goods_item {
			display: flex;
			align-items: center;
			padding: 20rpx;
			.clicked_ico {
				flex: none;
				height: 20px;
				line-height: 1;
			}
			.goods_content {
				flex: auto;
				display: flex;
			}
			.goods_pic {
				flex: none;
				width: 140rpx;
				height: 140rpx;
				margin: 0 20rpx;
				border-radius: 10rpx;
			}
			.content-r {
				flex: auto;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				.goods_name {
					line-height: 38rpx;
				}
			}
		}
	}
	
	.mt_20 {
		margin-top: 20rpx;
	}
	
	.setmeal_submit_bar {
		position: fixed;
		left: 0;
		bottom: 0;
		width: 100%;
		height: 100rpx;
		display: flex;
		justify-content: space-between;
		align-items: center;
		background-color: #fff;
		z-index: 96;
		.left_price {
			margin-left: 20rpx;
			.setmeal_price {
				display: flex;
				align-items: baseline;
				color: #000;
				text {
					color: #F22E20;
				}
			}
			.save_price {
				display: flex;
				align-items: baseline;
				font-size: 24rpx;
				color: #999;
			}
		}
		.sub_btn {
			height: 80rpx;
			line-height: 80rpx;
			border-radius: 40rpx;
			margin: 0 20rpx;
			padding: 0 30rpx;
			font-size: 30rpx;
			font-weight: 700;
			color: #fff;
		}
		.red_btn {
			background-color: #F91F28;
		}
	}
}
</style>
