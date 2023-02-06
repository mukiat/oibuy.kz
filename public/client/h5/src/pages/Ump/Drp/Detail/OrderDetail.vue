<template>
	<div class="drp-order drp-orderdetail">
		<div class="drp-order-header">
			<div class="money">+{{ drpOrderDetailData.money }}</div>
			<div class="txt">{{drpOrderDetailData.status==1?$t('lang.divided'):$t('lang.undivided')}}</div>
		</div>
		<div class="section">
			<div class="section-item">
				<div class="label">{{$t('lang.order_sn_alt')}}</div>
				<div class="value">{{drpOrderDetailData.order_sn}}</div>
			</div>
			<div class="section-item">
				<div class="label">{{$t('lang.buyer_info')}}</div>
				<div class="value">{{drpOrderDetailData.buy_user_name}}</div>
			</div>
			<div class="section-item">
				<div class="label">{{$t('lang.order_time')}}</div>
				<div class="value">{{drpOrderDetailData.add_time_format}}</div>
			</div>
		</div>
		<div class="section">
			<div class="title">{{$t('lang.order_goods')}}</div>
			<div class="drp-order-list">
				<div class="goods-li">
					<!-- :to="{name:'goods', params:{id:item.goods_id}}" -->
					<div :class="['show', 'bg-color-write', 'li', index > 0 ? 'mt_10' : '']" v-for='(item,index) in drpOrderDetailData.goods_list' :key="index" @click="goDetail(item)">
						<div class="flex_box">
							<div class="left">
								<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
								<img v-else class="img" src="../../../../assets/img/not_goods.png" />
							</div>
							<div class="flex_1 right flex_box fd_column jc_sb">
								<h4 class="f-05 color-3 twolist-hidden">{{item.goods_name}}</h4>
								<div class="dis-box cont">
									<div class="f-06 color-red box-flex">
										<span class="mw_100" v-html="item.goods_price_format"></span>
										<span class="f-03 color-7">×{{item.goods_number}}</span>
									</div>
									
									<div class="togglo_btn f-03 color-7" @click.stop="changeCollapse(index)"><span>{{activeNames.includes(index) ? '收起' : '展开'}}</span> <i :class="['iconfont', activeNames.includes(index) ? 'icon-less' : 'icon-moreunfold', 'ico']"></i></div>
								</div>
							</div>
						</div>
						<van-collapse :border="false" v-model="activeNames">
							<van-collapse-item class="collapse_item_box" :border="false" :is-link="false" :name="index">
								<div class="gap"></div>
								<div class="collapse_content">
									<div class="commission_count">
										<span v-if="drpOrderDetailData.log_type == 0" style="margin-right: 1rem;">{{$t('lang.dis_commission')}}：&nbsp;<span>{{item.dis_commission}}</span></span>
										<span class="ico_wrap">分成层级比例 ({{item.drp_level_format}}) ：&nbsp;<span>{{item.level_per}}</span><span class="ico" @click.stop="show = true">?</span></span>
									</div>
									<ul class="commission_wrap">
										<li class="commission_item">
											<span class="label_box">购买数量</span>
											<span class="value_box color-red">{{item.goods_number}}</span>
										</li>
										<template v-if="drpOrderDetailData.log_type == 0">
											<li class="commission_item">
												<span class="label_box">计佣金额</span>
												<span class="value_box color-red">{{item.drp_goods_price_format}}</span>
											</li>
											<li class="commission_item">
												<span class="label_box">计佣佣金</span>
												<span class="value_box color-red">{{item.drp_money_format}}</span>
											</li>
										</template>
										<li class="commission_item">
											<span class="label_box">获得佣金</span>
											<span class="value_box color-red">{{item.level_money_format}}</span>
										</li>
									</ul>
								</div>
							</van-collapse-item>
						</van-collapse>
					</div>
				</div>
			</div>
			<ul class="info_list">
				<li class="item_wrap">
					<span class="label">商品总额</span>
					<span class="value">{{drpOrderDetailData.total_goods_price_format}}</span>
				</li>
				<li class="item_wrap" v-if="drpOrderDetailData.total_goods_bonus > 0">
					<span class="label">红包</span>
					<span class="value color-red">-{{drpOrderDetailData.total_goods_bonus_format}}</span>
				</li>
				<li class="item_wrap" v-if="drpOrderDetailData.total_goods_favourable > 0">
					<span class="label">折扣</span>
					<span class="value color-red">-{{drpOrderDetailData.total_goods_favourable_format}}</span>
				</li>
				<li class="item_wrap" v-if="drpOrderDetailData.total_goods_coupons > 0">
					<span class="label">优惠券</span>
					<span class="value color-red">-{{drpOrderDetailData.total_goods_coupons_format}}</span>
				</li>
				<li class="item_wrap" v-if="drpOrderDetailData.total_goods_integral_money > 0">
					<span class="label">积分</span>
					<span class="value color-red">-{{drpOrderDetailData.total_goods_integral_money_format}}</span>
				</li>
				<li class="item_wrap" v-if="drpOrderDetailData.total_value_card_discount > 0">
					<span class="label">储值卡折扣</span>
					<span class="value color-red">-{{drpOrderDetailData.total_value_card_discount_format}}</span>
				</li>
			</ul>
		</div>
		<CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
		<van-popup v-model="show">
			<div class="pop_content">
				<header class="pop_header">
					<span>说明</span>
					<div class="pop_close" @click="show = false"><i class="iconfont icon-close size_12"></i></div>
				</header>
				<p class="content">
					1、佣金比例：指商品参与分成的佣金比例；<br />
					2、分成层级比例（X级）：指当前会员所属会员层级的分成比例；<br />
					3、计佣金额：指商品实际计算佣金的金额。去除红包、优惠券、储值卡折扣等折扣均摊后的金额；<br />
					4、计佣佣金：计算：计佣金额 X 商品参与分成的佣金比例；<br />
					5、获得佣金：指当前会员在该笔订单内，每件商品可获得的佣金数。（计算：计佣佣金 X 分成层级比例）；<br />
					6、佣金总和：指当前会员在该笔订单内，所有商品获得的佣金数总和；
				</p>
			</div>
		</van-popup>
	</div>
</template>
<script>
	import {
		Toast,
		Collapse,
		CollapseItem,
		Popup
	} from 'vant'
	import { mapState } from 'vuex'
	import CommonNav from '@/components/CommonNav'
	export default {
		name: "drp-orderdetail",
		components: {
			CommonNav,
			[Toast.name]: Toast,
			[Collapse.name]: Collapse,
			[CollapseItem.name]: CollapseItem,
			[Popup.name]: Popup
		},
		data() {
			return {
				routerName:'drp',
				//默认参数
				parameterData: {
					log_id: '',
				},
				activeNames: [],
				show: false
			};
		},
		computed: {
			...mapState({
				drpOrderDetailData: state => state.drp.drpOrderDetailData,
			})
		},
		mounted() {
			let that = this
			// 取到路由带过来的参数
			let log_id = that.$route.params.order_id;
			that.parameterData.log_id = log_id
			that.loading(that.$store.dispatch('setDrpOrderDetail', that.parameterData))
		},
		methods: {
			loading(url) {
				let that = this
				Toast.loading({
					duration: 500,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
			},
			changeCollapse(i) {
				if (this.activeNames.includes(i)) return this.activeNames = this.activeNames.filter(item => item != i)
				else this.activeNames.push(i);
			},
			goDetail(data) {
				this.$router.push({name:'goods', params:{id:data.goods_id}})
			}
		}
	};
</script>
<style lang="scss" scoped>
.drp-order-header{ text-align: center; background-color: #fff; padding: 30px 0;}
.drp-order-header .money{ font-size: 30px; font-weight: 700; color: #000; }
.drp-order-header .txt{ color: #f92028; font-size: 16px; }
.section{ margin-bottom: 10px; background-color: #fff; padding: 0 15px 15px;}
.section .section-item{ display: flex; flex-direction: row; justify-content: space-between; font-size: 14px; padding-bottom: 15px}
.section .section-item:last-child{ padding-bottom: 0; }
.section .title{ font-size: 16px; color: #333; padding: 12px 0; border-bottom: 0.1rem solid #F9F9F9; margin-bottom: 15px}
.section .goods-li .li{ padding: 0; }
.section .drp-order-list{ padding-bottom: 5px; border-bottom: 1px solid #F9F9F9; margin-bottom: 15px;}
.mw_100 {
	margin-right: 1rem;
}
.mt_10 {
	margin-top: 1rem;
}
.goods-li .li {
	border-top: none;
}
.info_list {
	.item_wrap {
		display: flex;
		justify-content: space-between;
		align-items: baseline;
		height: 2.8rem;
		font-size: 1.3rem;
	}
}
.gap {
	height: 1.8rem;
}
.van-popup {
	width: 90%;
	border-radius: 0.6rem;
}
.pop_content {
	padding: 1.35rem 0;
	.pop_header {
		position: relative;
		margin: 0 1.35rem 1.35rem;
		font-size: 1.6rem;
		text-align: center;
		color: #282828;
		font-weight: 700;
		.pop_close {
			position: absolute;
			bottom: 0;
			right: 0.5rem;
		}
	}
	.content {
		padding: 0 1.35rem;
	}
}
.togglo_btn {
	display: flex;
	align-items: center;
	.ico {
		font-size: 1.2rem;
		margin-left: 0.5rem;
	}
}
.collapse_content {
	font-size: 1.2rem;
	border-radius: 1rem;
	background-color: #F9F9F9;
	.commission_count {
		padding: 1.2rem 1.2rem 0;
		.ico_wrap {
			position: relative;
			.ico {
				position: absolute;
				top: 50%;
				right: -2.5rem;
				transform: translateY(-50%);
				width: 1.8rem;
				height: 1.8rem;
				border-radius: 50%;
				line-height: 1.8rem;
				text-align: center;
				font-size: 1.2rem;
				color: #fff;
				background-color: #FEA402;
			}
		}
	}
	.commission_wrap {
		display: flex;
		justify-content: space-between;
		padding: 1.2rem;
		.commission_item {
			flex: auto;
			display: flex;
			flex-direction: column;
			align-items: center;
			color: #333;
			.value_box {
				margin-top: 1rem;
			}
		}
	}
}
.commission_all {
	display: flex;
	justify-content: flex-end;
	align-items: baseline;
	font-size: 1.3rem;
	.value {
		font-size: 1.6rem;
		font-weight: bold;
	}
}
</style>
