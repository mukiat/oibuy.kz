<template>
	<view class="drp-order drp_order_detail">
		<view class="drp-order-header">
			<view class="money" v-if="drpOrderDetailData.money">+{{ drpOrderDetailData.money }}</view>
			<view class="txt">{{ drpOrderDetailData.status == 1 ? $t('lang.divided'): $t('lang.undivided') }}</view>
		</view>
		<view class="section">
			<view class="section-item">
				<view class="label">{{$t('lang.order_sn_alt')}}</view>
				<view class="value">{{drpOrderDetailData.order_sn}}</view>
			</view>
			<view class="section-item">
				<view class="label">{{$t('lang.buyer_info')}}</view>
				<view class="value">{{drpOrderDetailData.buy_user_name}}</view>
			</view>
			<view class="section-item">
				<view class="label">{{$t('lang.order_time')}}</view>
				<view class="value">{{drpOrderDetailData.add_time_format}}</view>
			</view>
		</view>
		<view class="section">
			<view class="title">{{$t('lang.order_goods')}}</view>
			<view class="drp-order-list" v-for="(goods,goodsIndex) in drpOrderDetailData.goods_list" :key="goodsIndex" @click="goDetail(goods)">
				<view :class="['goods-list',goodsIndex > 0 ? 'mt_10' : '']">
					<view class="left"><image :src="goods.goods_thumb" mode="widthFix"></image></view>
					<view class="right flex_box fd_column jc_sb">
						<view class="name twolist-hidden">{{goods.goods_name}}</view>
						<view class="out">
							<view class="price uni-red">{{goods.goods_price_format}}<text class="number ml_10">×{{goods.goods_number}}</text></view>
							<view class="togglo_btn" @click.stop="changeCollapse(goodsIndex)">
								<text>{{activeNames.includes(goodsIndex) ? '收起' : '展开'}}</text>
								<text :class="['iconfont', activeNames.includes(goodsIndex) ? 'icon-less' : 'icon-moreunfold']"></text>
							</view>
						</view>
					</view>
				</view>
				<view :class="['collapse_wrap', activeNames.includes(goodsIndex) ? '' : 'collapse_hide']">
					<view class="gap"></view>
					<view class="collapse_box">
						<view class="commission_count">
							<view class="dis_commission size_24" v-if="drpOrderDetailData.log_type == 0">{{$t('lang.dis_commission')}}：&nbsp;<text class="size_24">{{goods.dis_commission}}</text></view>
							<view class="dsc_lable size_24">分成层级比例 ({{goods.drp_level_format}}) ：&nbsp;<view class="size_24">{{goods.level_per}}</view><view class="ico" @click.stop="openPop">?</view></view>
						</view>
						<view class="commission_wrap">
							<view class="commission_item">
								<text class="label_box size_24">购买数量</text>
								<text class="value_box uni-red size_24">{{goods.goods_number}}</text>
							</view>
							<template v-if="drpOrderDetailData.log_type == 0">
								<view class="commission_item">
									<text class="label_box size_24">计佣金额</text>
									<text class="value_box uni-red size_24">{{goods.drp_goods_price_format}}</text>
								</view>
								<view class="commission_item">
									<text class="label_box size_24">计佣佣金</text>
									<text class="value_box uni-red size_24">{{goods.drp_money_format}}</text>
								</view>
							</template>
							<view class="commission_item">
								<text class="label_box size_24">获得佣金</text>
								<text class="value_box uni-red size_24">{{goods.level_money_format}}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="info_list">
				<view class="item_wrap">
					<text class="label">商品总额</text>
					<text class="value">{{drpOrderDetailData.total_goods_price_format}}</text>
				</view>
				<view class="item_wrap" v-if="drpOrderDetailData.total_goods_bonus > 0">
					<text class="label">红包</text>
					<text class="value">-{{drpOrderDetailData.total_goods_bonus_format}}</text>
				</view>
				<view class="item_wrap" v-if="drpOrderDetailData.total_goods_favourable > 0">
					<text class="label">折扣</text>
					<text class="value uni-red">-{{drpOrderDetailData.total_goods_favourable_format}}</text>
				</view>
				<view class="item_wrap" v-if="drpOrderDetailData.total_goods_coupons > 0">
					<text class="label">优惠券</text>
					<text class="value uni-red">-{{drpOrderDetailData.total_goods_coupons_format}}</text>
				</view>
				<view class="item_wrap" v-if="drpOrderDetailData.total_goods_integral_money > 0">
					<text class="label">积分</text>
					<text class="value uni-red">-{{drpOrderDetailData.total_goods_integral_money_format}}</text>
				</view>
				<view class="item_wrap" v-if="drpOrderDetailData.total_value_card_discount > 0">
					<text class="label">储值卡折扣</text>
					<text class="value uni-red">-{{drpOrderDetailData.total_value_card_discount_format}}</text>
				</view>
			</view>
			<!-- <view class="section-item">
				<view class="label">{{$t('lang.get_commission')}}</view>
				<view class="value" v-if="drpOrderDetailData.money_format">{{drpOrderDetailData.money_format}}</view>
			</view> -->
		</view>
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
		<uni-popups ref="popup" type="center">
			<view class="pop_content">
				<view class="pop_header">
					<text class="pop_title">说明</text>
					<image class="close_img" src="/static/close1.png" @click="closePop"></image>
				</view>
				<view class="main_content">
					<text>1、佣金比例：指商品参与分成的佣金比例；</text>
					<text>2、分成层级比例（X级）：指当前会员所属会员层级的分成比例；</text>
					<text>3、计佣金额：指商品实际计算佣金的金额。去除红包、优惠券、储值卡折扣等折扣均摊后的金额；</text>
					<text>4、计佣佣金：计算：计佣金额 X 商品参与分成的佣金比例；</text>
					<text>5、获得佣金：指当前会员在该笔订单内，每件商品可获得的佣金数。（计算：计佣佣金 X 分成层级比例）；</text>
					<text>6、佣金总和：指当前会员在该笔订单内，所有商品获得的佣金数总和；</text>
				</view>
			</view>
		</uni-popups>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';

	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';

	export default {
		data() {
			return {
				log_id:0,
				activeNames: []
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent,
			uniPopups
		},
		onLoad(e) {
			this.log_id = e.log_id;
			this.load();
		},
		computed: {
			...mapState({
				drpOrderDetailData: state => state.drp.drpOrderDetailData,
			})
		},
		methods: {
			load(){
				this.$store.dispatch('setDrpOrderDetail',{
					log_id:this.log_id
				})
			},
			openPop() {
				this.$refs.popup.open()
			},
			closePop() {
				this.$refs.popup.close()
			},
			changeCollapse(i) {
				if (this.activeNames.includes(i)) return this.activeNames = this.activeNames.filter(item => item != i)
				else this.activeNames.push(i);
			},
			goDetail(data) {
				uni.navigateTo({
					url: `/pagesC/goodsDetail/goodsDetail?id=${data.goods_id}`
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
.drp-order-header{ text-align: center; background-color: #FFFFFF; padding: 60upx 0; }
.drp-order-header .money{ font-size: 60upx; font-weight: 700; color: #000; }
.drp-order-header .txt{ color: #f92028; font-size: 32upx; }
.section{ margin-bottom: 20upx; background-color: #fff; padding: 0 30upx 30upx;}
.section .section-item{ display: flex; flex-direction: row; justify-content: space-between; font-size: 28upx; padding-bottom: 30upx}
.section .section-item:last-child{ padding-bottom: 0; }
.section .title{ font-size: 32upx; color: #333; padding: 24upx 0; border-bottom: 2upx solid #F9F9F9; margin-bottom: 30upx}
.section .drp-order-list{ padding-bottom: 30upx; border-bottom: 2upx solid #999; margin-bottom: 20upx;}

.goods-list{ display: flex; flex-direction: row; margin-bottom: 0;}
.goods-list .left{ width: 150upx; height: 150upx; border: 1px solid #f4f4f4;}
.goods-list .left image{ width: 100%; }
.goods-list .right{ display: flex; flex: 1 1 0%; flex-direction: column; margin-left: 20upx; }
.goods-list .right .name{ font-size: 28upx; color: #333333; line-height: 1.5;}
.goods-list .right .out{ display: flex; justify-content: space-between; align-items: center; margin-top: 20upx;}
.goods-list .right .out .number{ color: #999999; font-size: 25upx;}
.ml_10 {
	margin-left: 20upx;
}
.mt_10 {
	margin-top: 20upx;
}
.info_list {
	margin-top: 10rpx;
	padding-top: 20rpx;
	border-top: 2upx solid #F9F9F9;
	.item_wrap {
		display: flex;
		justify-content: space-between;
		align-items: baseline;
		height: 60rpx;
	}
}
.gap {
	height: 36rpx;
}
.drp_order_detail {
	.drp-order-list {
		border: none;
		margin-bottom: 0;
		padding-bottom: 20rpx;
	}
	.togglo_btn {
		padding-left: 30rpx;
		text {
			font-size: 26rpx;
			color: #777;
		}
		.iconfont {
			margin-left: 10rpx;
		}
	}
	.pop_content {
		overflow: hidden;
		width: 675rpx;
		border-radius: 12rpx;
		background-color: #fff;
		.pop_header {
			position: relative;
			padding: 0 30rpx;
			text-align: center;
			height: 90rpx;
			line-height: 90rpx;
			.icon-find-fanhui {
				position: absolute;
				top: 50%;
				left: 30rpx;
				transform: translateY(-50%);
				font-size: 28rpx;
			}
			.pop_title {
				font-size: 32rpx;
				font-weight: 700;
				line-height: 90rpx;
				color: #282828;
			}
			.close_img {
				position: absolute;
				top: 50%;
				right: 30rpx;
				transform: translateY(-50%);
				width: 44rpx;
				height: 44rpx;
			}
		}
		.main_content {
			display: flex;
			flex-direction: column;
			padding: 0 30rpx 30rpx;
			background-color: #fff;
		}
	}
	.collapse_wrap {
		overflow: hidden;
		height: 254rpx;
		transition: height .5s;
	}
	.collapse_hide {
		height: 0;
		transition: height .5s;
	}
	.collapse_box {
		border-radius: 20rpx;
		background-color: #F9F9F9;
		.commission_count {
			display: flex;
			align-items: center;
			padding: 24rpx 24rpx 0;
		}
		.dis_commission {
			display: flex;
			align-items: center;
			margin-right: 20rpx;
		}
		.dsc_lable {
			position: relative;
			display: flex;
			align-items: center;
			.ico {
				position: absolute;
				top: 50%;
				right: -50rpx;
				transform: translateY(-50%);
				width: 36rpx;
				height: 36rpx;
				border-radius: 50%;
				line-height: 36rpx;
				text-align: center;
				font-size: 24rpx;
				color: #fff;
				background-color: #FEA402;
			}
		}
		.commission_wrap {
			display: flex;
			justify-content: space-between;
			padding: 24rpx;
			.commission_item {
				flex: auto;
				display: flex;
				flex-direction: column;
				align-items: center;
				color: #333;
				.value_box {
					margin-top: 20rpx;
				}
			}
		}
	}
}
</style>
