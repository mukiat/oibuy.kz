<template>
	<view :class="['cart_list', type == 1 ? 'disabled_list' : '']">
		<view class="cart_list_item" v-for="(item,index) in list" :key="index" @click="clickHandle({type: 'active', value: item})">
			<view class="cart_head">
				<view class="card_sn">
					<text>卡号：{{item.value_card_sn}}</text>
					<text v-if="item.vc_dis != 1">{{$t('lang.discount_rate')}}：{{item.vc_dis_format}}</text>
				</view>
				<view class="how_much">{{$t('lang.face_value')}}：{{item.vc_value_money}}元</view>
			</view>
			<view :class="['cart_body', page == 'checkout' ? 'h_11' : '']">
				<view class="money_and_time">
					<text class="balance">{{$t('lang.money')}}{{currency_format}}<text>{{item.use_card_money}}</text></text>
					<text class="past_due">{{$t('lang.expiration_time')}}：{{item.local_end_time}}</text>
				</view>
				<view class="btn_wrap" v-if="page == 'valuecart'">
					<view class="red_btn" @click.stop="clickHandle({type: 'add', value: item})" v-if="item.is_rec == 1 && type == 0">{{$t('lang.recharge')}}</view>
					<view class="status_txt size_14" v-if="item.use_status == 0">{{$t('lang.lost_effectiveness')}}</view>
					<view class="status_txt size_14" v-if="item.use_status == 2">{{$t('lang.have_expired')}}</view>
					<view class="status_txt size_14" v-if="item.use_status == 3">{{$t('lang.have_been_exhausted')}}</view>
					<view class="cart_btn" @click.stop="clickHandle({type: 'detail', value: item})">{{$t('lang.view_usage_records')}}</view>
				</view>
				<view class="disabled_tips" v-if="page == 'checkout' && type == 1">{{$t('lang.bill_no_goods')}}</view>
				<view class="new_store_radio_box" v-if="page == 'checkout' && type == 0 && active == item.vid"><text class="iconfont icon-ok"></text></view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		props: {
			list: {
				type: Array,
				default: () => {
					return []
				}
			},
			type: {
				type: Number,
				default: 0
			},
			active: {
				type: [Number,String],
				default: -1
			},
			page: {
				type: String,
				default: 'valuecart'
			}
		},
		data() {
			return {
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
			}
		},
		methods: {
			clickHandle(res) {
				this.$emit('link', res)
			}
		}
	}
</script>

<style lang="scss" scoped>
	.cart_list {
		.cart_list_item {
			overflow: hidden;
			border: 1px solid #E83C2D;
			border-top: none;
			border-radius: 20rpx;
			margin-bottom: 24rpx;
			.cart_head {
				padding: 12rpx 20rpx;
				color: #fff;
				background-color: #E83C2D;
			}
			.card_sn {
				display: flex;
				justify-content: space-between;
				text:first-child {
					font-size: 32rpx;
					font-weight: 700;
				}
			}
			.how_much {
				font-weight: 700;
			}
			.cart_body {
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				align-items: center;
				height: 300rpx;
				text-align: center;
				color: #E83C2D;
			}
			.h_11 {
				position: relative;
				height: 230rpx;
				.new_store_radio_box {
					position: absolute;
					right: 0;
					bottom: 0;
					border-bottom: 80rpx solid #E83C2D;
					border-left: 80rpx solid transparent;
					.iconfont {
						position: absolute;
						right: 10rpx;
						bottom: -85rpx;
						font-weight: 700;
						color: #fff;
					}
				}
			}
			.money_and_time {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				margin-top: 40rpx;
				.balance {
					font-size: 30rpx;
					text {
						font-size: 40rpx;
						font-weight: 700;
					}
				}
			}
			.btn_wrap {
				display: flex;
				flex-direction: column;
				align-items: center;
				font-size: 20rpx;
				.red_btn {
					padding: 0 60rpx;
					height: 52rpx;
					line-height: 52rpx;
					border-radius: 6rpx;
					font-size: 20rpx;
					color: #fff;
					background-color: #E83C2D;
				}
				.cart_btn {
					padding: 0 20rpx;
					height: 72rpx;
					line-height: 72rpx;
					font-size: 20rpx;
				}
			}
			.disabled_tips  {
				width: 100%;
				height: 52rpx;
				padding: 0 20rpx;
				font-size: 24rpx;
				text-align: left;
				box-sizing: border-box;
			}
		}
	}
	.disabled_list {
		.cart_list_item {
			border-color: #BABABA;
			.cart_head {
				background-color: #BABABA;
			}
			.cart_body {
				color: #BABABA;
				.disabled_tips {
					color: #BABABA;
				}
			}
		}
	}
</style>
