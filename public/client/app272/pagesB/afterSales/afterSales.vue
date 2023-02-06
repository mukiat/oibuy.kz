<template>
	<view class="section-list">
		<block v-if="order_id == 0">
			<block v-if="refoundList && refoundList.length > 0">
				<section class="user-item" v-for="(itemList,index) in refoundList" :key="index">
					<view class="item-hd">
						<view class="subHead">
							<view class="h4">
								<text class="label">{{$t('lang.return_sn_user')}}：</text>
								<text class="span">{{ itemList.return_sn }}</text>
							</view>
							<view class="p">
								<text class="label">{{$t('lang.apply_time')}}： </text>
								<text class="m-r05">{{ itemList.apply_time }}</text>
								<text class="color-red m-r05" v-if="itemList.agree_apply == 0">{{$t('lang.to_be_agreed')}}</text>
								<text class="color-red m-r05" v-else-if="itemList.agree_apply == 1">{{$t('lang.has_agreed_to')}}</text>
								<text class="color-red m-r05" v-else>{{$t('lang.denied')}}</text>
								<text class="color-red">{{ itemList.reimburse_status }}</text>
							</view>
						</view>
					</view>
					<view class="flow-checkout-pro">
						<view class="item-bd">
							<view class="list-bd-box">
								<view class="dis-box">
									<view class="reture-left-img">
										<view class="img-box">
											<image :src="itemList.goods_thumb" class="img" v-if="itemList.goods_thumb"></image>
										</view>
									</view>
									<view class="reture-right-cont">
										<text class="tit twolist-hidden">{{ itemList.goods_name }}</text>
										<view class="reture-footer">
											<view class="price">
												<text class="span" v-if="itemList.get_return_goods">{{$t('lang.number')}}×{{ itemList.get_return_goods.return_number }}</text>
											</view>
											<view class="fr">
												<block v-if="itemList.refound_cancel">
													<view class="btn-default-new" @click="cancelRefound(itemList.ret_id)">{{$t('lang.cancel_request')}}</view>
												</block>
												<block v-if="itemList.activation_type">
													<view class="btn-default-new uni-center" @click="applyRefoundjihuo(itemList.ret_id)">{{$t('lang.activate')}}</view>
												</block>
												<view class="btn-default-new" @click="applyRefoundView(itemList.ret_id)">{{$t('lang.view_detail')}}</view>
											</view>
										</view>
									</view>
								</view>
							</view>
						</view>
					</view>
				</section>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</block>
		<block v-else>
			<section class="user-item" v-if="orderRefound">
				<view class="item-hd">
					<view class="subHead">
						<h4>
							<label>{{$t('lang.order_sn')}}：</label>
							<span>{{ orderRefound.order_sn }}</span>
						</h4>
						<p>
							<span>{{$t('lang.apply_time')}}： </span>
							<span class="m-r05">{{ orderRefound.formated_add_time }}</span>
						</p>
					</view>
				</view>
				<view class="flow-checkout-pro">
					<view class="item-bd">
						<checkbox-group @change="checkboxChange">
							<view class="list-bd-box" v-for="(item,index) in orderRefound.goods" :key="index">
								<view class="dis-box">
									<view class="reture-checkbox">
										<checkbox :value="item.rec_id" :checked="item.checked" :disabled="orderRefound.all_refound == 1 || item.is_refound == 1 || (item.goods_cause && item.goods_cause.length == 0) || item.extension_code == 'package_buy' " />
									</view>
									<view class="reture-left-img">
										<view class="img-box">
											<img :src="item.goods_thumb" class="img" v-if="item.goods_thumb" />
										</view>
									</view>
									<view class="reture-right-cont">
										<text class="tit onelist-hidden">{{ item.goods_name }}</text>
										<view class="price">
											<text class="color-red">{{ item.goods_price }}</text>
											<span>×{{ item.goods_number }}</span>
										</view>
										<view class="reture-footer">
											<view class="goods-cause color-red" v-if="item.goods_cause_formated != '' ">{{ item.goods_cause_formated }}</view>
											<view class="goods-operation">
												<block v-if="item.is_refound">
													<span class="color-red f-06">{{$t('lang.applied')}}</span>
												</block>
												<block v-else>
													<block v-if="(item.goods_cause && item.goods_cause.length == 0) || item.extension_code == 'package_buy'">
														<view class="btn-default-new disabled">{{$t('lang.apply_return')}}</view>
													</block>
													<block v-else>
														<view v-if="orderRefound.all_refound != 1" class="btn-default-new" @click="applyRefound(item.rec_id,orderRefound.order_id)">{{$t('lang.apply_return')}}</view>
													</block>
												</block>
											</view>
										</view>
									</view>
								</view>
							</view>
						</checkbox-group>
					</view>
					<view class="btn-bar">
						<block v-if="orderRefound.all_refound == 1">
							<button type="warn" @click="onApply('all')">{{$t('lang.oud_apply_return_alt')}}</button>
						</block>
						<block v-else>
							<button type="warn" :disabled="btndisabled" @click="onApply">{{$t('lang.oud_apply_return_alt')}}</button>
						</block>
					</view>
				</view>
			</section>
		</block>
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import {
		mapState
	} from 'vuex'
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		components: {
			dscCommonNav,
			dscNotContent
		},
		data() {
			return {
				order_id: 0,
				page: 1,
				size: 10,
				btndisabled: true,
				rec_id: ''
			};
		},
		computed: {
			...mapState({
				orderRefound: state => state.user.orderRefound
			}),
			refoundList: {
				get() {
					return this.$store.state.user.refoundList
				},
				set(val) {
					this.$store.state.user.refoundList = val
				}
			}
		},
		methods: {
			//列表
			setRefoundList(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				if (this.order_id == 0) {
					this.$store.dispatch('setRefoundList', {
						page: 1,
						size: 10,
						order_id: this.order_id
					})
				} else {
					this.$store.dispatch('setOrderRefound', {
						order_id: this.order_id
					})
				}
			},
			onApply(type) {
				let rec_id;
				if (type == 'all') {
					let rec_all = this.orderRefound.goods.map(function(val, index) {
						return val.rec_id
					});
					rec_id = rec_all.join(',');
				} else {
					rec_id = this.rec_id
				}

				//判断结尾是否是','
				if (rec_id[rec_id.length - 1] == ',') {
					rec_id = rec_id.substring(0, rec_id.length - 1);
				}

				this.applyRefound(rec_id, this.order_id)
			},
			applyRefound(rec_id, order_id) {
				uni.navigateTo({
					url: '/pagesB/afterSales/applyReturn/applyReturn?rec_id=' + rec_id + '&order_id=' + order_id,
				})
			},
			applyRefoundView(ret_id) {
				uni.navigateTo({
					url: '/pagesB/afterSales/detail/detail?ret_id=' + ret_id,
				})
			},
			applyRefoundjihuo(ret_id) {
				let that = this;
				uni.showModal({
					title: that.$t('lang.hint'),
					content: that.$t('lang.return_order_activate'),
					success(res) {
						if (res.confirm) {
							uni.request({
								url: that.websiteUrl + '/api/refound/active_return_order',
								method: 'POST',
								data: {
									ret_id: ret_id
								},
								header: {
									'Content-Type': 'application/json',
									'token': uni.getStorageSync('token'),
									'X-Client-Hash':uni.getStorageSync('client_hash')
								},
								success: (res) => {
									if (res.data.status == 'success') {
										if (res.data.data.code == 0) {
											uni.showModal({
												title: that.$t('lang.hint'),
												content: that.$t('lang.return_order_activate')
											});
											that.setRefoundList()
										} else {
											uni.showModal({
												title: that.$t('lang.hint'),
												content: res.data.data.msg
											});
										}
									}
								}
							});
						}
					}
				});
			},
			cancelRefound(ret_id) {
				let that = this;
				uni.showModal({
					title: that.$t('lang.hint'),
					content: that.$t('lang.confirm_cancel_request'),
					success(res) {
						if (res.confirm) {
							uni.request({
								url: that.websiteUrl + '/api/refound/cancel',
								method: 'POST',
								data: {
									ret_id: ret_id
								},
								header: {
									'Content-Type': 'application/json',
									'token': uni.getStorageSync('token'),
									'X-Client-Hash':uni.getStorageSync('client_hash')
								},
								success: (res) => {
									if (res.data.status == 'success') {
										if (res.data.data.code == 0) {
											uni.showModal({
												title: that.$t('lang.hint'),
												content: that.$t('lang.return_order_cancel')
											});
											that.setRefoundList()
										} else {
											uni.showModal({
												title: that.$t('lang.hint'),
												content: res.data.data.msg
											});
										}
									}
								}
							});
						}
					}
				})
			},
			checkboxChange(e) {
				let arr = ''
				this.orderRefound.goods.forEach(i => {
					e.detail.value.forEach(v => {
						if (v == i.rec_id) {
							arr += i.rec_id + ','
						}
					})
				})

				this.rec_id = arr
			}
		},
		onLoad(e) {
			this.order_id = e.id ? e.id : 0;
			this.setRefoundList(1)
		},
		onReachBottom() {
			if (this.page * this.size == this.refoundList.length) {
				this.page++
				this.setRefoundList()
			}
		},
		watch: {
			rec_id() {
				this.btndisabled = this.rec_id ? false : true
			}
		}
	}
</script>
<style lang="scss" scoped>
	@mixin box() {
		display: flex;
	}

	@mixin box-flex() {
		flex: 1;
		display: block !important;
		width: 100%;
	}

	.dis-box {
		@include box();
	}

	.color-red {
		color: #f92028;
	}

	.fr {
		float: right;
	}

	.m-r05 {
		margin-right: 10upx;
	}

	.btn-default-new {
		display: inline-block;
		padding: 6upx 16upx;
		font-size: 28upx;
		width: 120upx;
		border: 1upx solid #ccc;
		border-radius: 5upx;
		color: #333;
		margin-left: 10upx;
	}

	.btn-default-new.disabled {
		background-color: #fff;
		border: 1upx solid #ccc;
		color: #ccc;
	}

	.user-item .item-bd {
		padding: 28upx 26upx;
		border-top: 1upx solid #f0f0f0;
	}

	.user-item .subHead .h4 .label,
	.user-item .subHead .p .label {
		font-size: 28upx;
		color: #999;
		font-weight: 400;
	}

	.user-item .subHead p span {
		height: 40upx;
		line-height: 40upx;
		display: inline-block;
		float: left;
	}

	.reture-checkbox {
		display: flex;
		justify-content: center;
		align-items: center;
		margin-right: 10upx;
	}

	.reture-left-img {
		width: 140upx;
		margin: 0 12upx 12upx 0;

		.img-box {
			position: relative;
			display: block;
			padding-top: 100%;

			.img {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}

			span {
				position: absolute;
				font-size: 28upx;
				left: 1px;
				right: 0;
				bottom: 0;
				padding: 8upx 0;
				text-align: center;
				display: block;
				background: rgba(0, 0, 0, 0.6);
				color: #fff;
			}
		}
	}

	.reture-right-cont {
		margin-left: 8upx;
		@include box-flex();

		.tit {
			font-size: 30upx;
			color: #444;
		}

		.p-attr {
			font-size: 26upx;
			color: #999;
			margin-top: 10upx;
		}

		.reture-footer {
			margin-top: 5upx;
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			align-items: center;
		}

		.p-virtual {
			margin-top: 8upx;

			.virtual-item {
				color: #999;
				line-height: 1.5
			}
		}

		.price .span {
			color: #999;
			font-size: 28upx;
		}
	}

	.reture-right-san {
		margin: 66upx 0 0 10upx;
	}

	.product-list {
		.product-div {
			padding: 26upx 0 0;
		}
	}

	.btn-bar {
		padding: 40upx 0;
	}

	.btn-bar button {
		width: 90%;
	}
</style>
