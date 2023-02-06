<template>
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(tab,index) in drpOrderTabs" :key="index" :class="['tab-item',active == index ? 'active' : '']" @click="orderStatusHandle(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<view class="section-list">
			<block v-if="drpOrderData && drpOrderData.length > 0">
				<view class="drp-order-list" v-for="(item,index) in drpOrderData" :key="index">
					<view class="order-box">
						<view class="order-header">
							<view class="name">{{$t('lang.label_buyer')}}{{item.buy_user_name}}</view>
							<view class="uni-red">{{ item.status == 0 ? $t('lang.not_into') : $t('lang.has_been_divide')}}</view>
						</view>
						<view class="order-sn border_bottom">
                            <view v-if="item.log_type == 0 || item.log_type == 2">
                                {{$t('lang.label_order')}}<text>{{item.order_sn}}</text>
                            </view>

							<view>{{item.add_time_format}}</view>
						</view>
					</view>
                    <block v-if="item.log_type == 0 || item.log_type == 2">
                        <block v-for="(goods,goodsIndex) in item.goods_list" :key="goodsIndex">
							<view :class="['goods-list', goodsIndex > 0 ? 'border_top_none' : 'border_top']" @click="clickHref(item.log_id)">
							    <view class="left"><image :src="goods.goods_thumb" mode="widthFix"></image></view>
							    <view class="right flex_box fd_column jc_sb">
							        <view class="name twolist-hidden">{{goods.goods_name}}</view>
							        <view class="out">
							            <text class="size_26" v-if="item.log_type == 0">{{$t('lang.dis_commission')}}：<text class="size_26">{{goods.dis_commission}}</text></text>
							            <text class="size_26">分成层级比例 ({{goods.drp_level_format}})：<text class="size_26">{{goods.level_per}}</text></text>
							        </view>
							    </view>
							</view>
							<view class="commission_wrap border_bottom">
								<view class="commission_item">
									<text class="label_box">购买数量</text>
									<text class="value_box uni-red">{{goods.goods_number}}</text>
								</view>
								<template v-if="item.log_type == 0">
									<view class="commission_item">
										<text class="label_box">计佣金额</text>
										<text class="value_box uni-red">{{goods.drp_goods_price_format}}</text>
									</view>
									<view class="commission_item">
										<text class="label_box">计佣佣金</text>
										<text class="value_box uni-red">{{goods.drp_money_format}}</text>
									</view>
								</template>
								<view class="commission_item">
									<text class="label_box">获得佣金</text>
									<text class="value_box uni-red">{{goods.level_money_format}}</text>
								</view>
							</view>
						</block>
                    </block>

                    <block v-else-if="item.log_type == 1 || item.log_type == 3">
                        <view class="goods-list" v-for="(goods,goodsIndex) in item.goods_list" :key="goodsIndex">
                            <view class="left"><image :src="goods.goods_thumb" mode="widthFix"></image></view>
                            <view class="right flex_box fd_column jc_sb">
                                <view class="name">{{goods.goods_name}}</view>
                                <view class="out">
                                    <view class="price uni-red">{{goods.goods_price_format}}<text class="number ml_10">×{{goods.goods_number}}</text></view>
									<text>{{$t('lang.dis_commission')}} ({{goods.drp_level_format}}) ：<text class="uni-red">{{goods.level_per}}</text></text>
                                </view>
                            </view>
                        </view>
                    </block>

					<view class="commission_all">
						<view>佣金总和 <view class="ico" @click="openPop">?</view></view><view>{{item.money_format}}</view>
					</view>
				</view>
				<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.drp_center')}}</text>
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
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				tabBars:[this.$t('lang.all'), this.$t('lang.has_been_divide'), this.$t('lang.not_into')],
				active:0,
				status:2,
				size:10,
				page:1,
				type:'order',
				drpOrderTabs: []
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent,
			uniPopups
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.drpOrderData.length){
				this.page ++
				this.orderList()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad(e) {
			this.type = e.type ? e.type : 'order';
		},
		async onShow() {
			await this.getCustomTextByCode();
			this.orderList(1)
		},
		computed: {
			drpOrderData:{
				get(){
					return this.$store.state.drp.drpOrderData
				},
				set(val){
					this.$store.state.drp.drpOrderData = val
				}
			}
		},
		methods: {
			orderList(page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setDrpOrder',{
					type: this.type,
					page: this.page,
					size: this.size,
					status: this.status
				})
			},
			orderStatusHandle(index){
				this.active = index

				if(index == 0){
					this.status = 2
				}else if(index == 2){
					this.status = 0
				}else{
					this.status = 1
				}
				this.orderList(1)
			},
            clickHref(id){
                uni.navigateTo({
                    url:'/pagesA/drp/orderDetail/orderDetail?log_id='+id
                });
            },
			// 分销管理-自定义设置数据
			async getCustomTextByCode() {
				const {data: { page_drp_order }, status} = await this.$store.dispatch('getCustomText',{code: 'page_drp_order'});
			    if (status == 'success') {
			        let pageDrpOrder = page_drp_order || {};
					let isSetTab = ['all', 'already_separate', 'wait_separate'];
					this.drpOrderTabs = isSetTab.map((item, index) => pageDrpOrder[item] || this.tabBars[index]);
			    }
			},
			openPop() {
				console.log('456');
				this.$refs.popup.open()
			},
			closePop() {
				this.$refs.popup.close()
			}
		}
	}
</script>

<style lang="scss" scoped>
.drp-order-list{ background: #FFFFFF; margin-top: 20upx;}
.drp-order-list .order-box{ padding: 0 30upx;}
.drp-order-list .order-box .order-header{ padding: 20upx 0; display: flex; flex-direction: row; justify-content: space-between; align-items: center; border-bottom: 1px solid #f4f4f4;}
.drp-order-list .order-box .order-sn{ padding: 20upx 0;}
.drp-order-list .order-box .order-sn view{ color: #777777; line-height: 1.6; font-size: 26upx;}
.drp-order-list .order-box .order-sn text{ color: #333333;}

.goods-list{ padding: 20upx; border-top: 1px solid #f4f4f4; border-bottom: 1px solid #f4f4f4; display: flex; flex-direction: row; margin-bottom: 0;}
.goods-list .left{ width: 150upx; height: 150upx; border: 1px solid #f4f4f4;}
.goods-list .left image{ width: 100%; }
.goods-list .right{ display: flex; flex: 1 1 0%; flex-direction: column; margin-left: 20upx; }
.goods-list .right .name{ font-size: 28upx; color: #333333; line-height: 1.5;}
.goods-list .right .out{ display: flex; justify-content: space-between; align-items: center; margin-top: 20upx;}
.goods-list .right .out .number{ color: #999999; font-size: 25upx;}

.text-right{ padding: 20upx 30upx; color: #777777;}
.ml_10 {
	margin-left: 20upx;
}
.border_top {
    border-top: 1upx solid #f4f4f4!important;
}
.border_bottom {
    border-bottom: 1upx solid #f4f4f4!important;
}
.border_top_none {
      border-top: none!important;
}
.container-tab-bar {
	.section-list {
		.goods-list {
			padding: 20rpx 30rpx;
			border: none!important;
		}
	}
	.commission_wrap {
		display: flex;
		margin: 0 24rpx;
		padding: 24rpx 0;
		border-radius: 20rpx;
		background-color: #F9F9F9;
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
	.commission_all {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 30rpx;
		view {
			font-weight: bold;
			color: #333;
			&:nth-child(1) {
				position: relative;
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
}
</style>
