<template>
	<view v-if="loading">
		<view class="user-detail">
			<view class="product-list uni-card" v-if="goodsList">
				<view class="product-items">
					<view class="item" v-for="(goodsInfo,index) in goodsList" :key="index">
						<view class="product-img">
							<image class="img" :src="goodsInfo.goods_img"></image>
						</view>
						<view class="product-info">
							<view class="product-name twolist-hidden">{{ goodsInfo.goods_name }}</view>
							<view class="product-row">
								<view class="price">{{ goodsInfo.shop_price_formated }}</view>
								<view class="number">x{{goodsInfo.goods_number}}</view>
							</view>
							<view class="price" v-if="goodsInfo.goods_coupons > 0">- {{ goodsInfo.formated_goods_coupons }}</view>
							<view class="price" v-if="goodsInfo.goods_bonus > 0">- {{ goodsInfo.formated_goods_bonus }}</view>
							<view class="price" v-if="goodsInfo.goods_favourable > 0">- {{ goodsInfo.formated_goods_favourable }}</view>
							<view class="price" v-if="goodsInfo.value_card_discountformated_goods_coupons > 0">- {{ goodsInfo.formated_value_card_discount }}</view>
							<view class="price" v-if="goodsInfo.goods_coupons > 0 || goodsInfo.goods_bonus > 0 || goodsInfo.goods_favourable > 0 || goodsInfo.value_card_discount > 0">{{ $t('lang.return_total') }}：{{ goodsInfo.formated_should_return }}</view>
							<view class="p-t-remark m-top04">{{ goodsInfo.attr_name }}</view>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="padding-all">
					<text class="color-red">{{$t('lang.reminder')}}：</text>
					<text class="f-03 col-6">
						<text>{{$t('lang.reminder_one')}}</text>
						<text class="color-red" v-if="goodsList">{{ goodsList[0].shop_name }}</text>
						<text>{{$t('lang.reminder_two')}}</text>
					</text>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="padding-all">
					<view class="h4">{{$t('lang.service_type')}}<text class="dot color-red">*</text></view>
					<view class="select-one-1">
						<view class="ect-selects">
							<view class="ect-select" :class="{'active':item.cause == retrun_cause_id}" v-for="(item,index) in goods_cause"
							 @click="causeSelect(item.cause)" :key="index"><text>{{ item.lang }}</text></view>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="padding-all">
					<view class="h4">{{$t('lang.return_reason')}}<text class="dot color-red">*</text></view>
					<view class="select-one-1">
						<view class="select-one-1">
							<view class="ect-selects">
								<view class="ect-select" :class="{'active':item.cause_id == causeSelected}" v-for="(item,index) in parent_cause"
								 @click="causeSelect2(item.cause_id)" :key="index"><text>{{ item.cause_name }}</text></view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not" v-if="shippingStatus && showReturnNumber">
				<view class="padding-all">
					<view class="h4">{{$t('lang.return_number')}}<text class="dot color-red">*</text></view>
					<view class="select-one-1">
						<view class="uni-number-min">
							<uni-number-box :value="value" :min="1" :max="applyRefoundDetail.return_goods_num" :step="1" :disabled="applyRefoundDetail.return_goods_num < 2" @change="bindChange"></uni-number-box>
						</view>
						<view class="oper-icon"></view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="padding-all">
					<view class="h4">{{$t('lang.problem_desc')}}<text class="dot color-red">*</text></view>
					<view class="value"><textarea :placeholder="$t('lang.problem_desc')" v-model="return_brief"></textarea></view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="padding-all">
					<view class="h4">{{$t('lang.application_credentials')}}</view>
					<view class="select-one-1">
						<view class="uni-list-cell uni-list-cell-last">
							<view class="uni-list-cell-db">{{$t('lang.has_test_report')}}</view>
							<switch :checked="checked" @change="switch2Change" />
						</view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="padding-all">
					<view class="h4">{{$t('lang.pic_info')}}</view>
					<view class="goods-info-img-box" v-if="materialList.length > 0">
						<view class="goods-info-img" v-for="(item,index) in materialList" :key="index">
							<image :src="item"></image>
							<i class="iconfont icon-delete" @click="deleteImg(index)"></i>
						</view>
					</view>
					<view class="user-return-img" @tap="chooseImage('apply')">
						<i class="i-jia"></i>
						<text>{{$t('lang.pic_voucher')}}</text>
					</view>
					<p class="f-03 col-7 m-top06"> {{$t('lang.pic_prompt_notic_one')}}<br>{{ $t('lang.pic_prompt_notic_two') }}{{ returnPictures }}{{ $t('lang.pic_prompt_notic_two2') }}</p>
				</view>
			</view>
			<block v-if="consignee">
				<view class="uni-card uni-card-not" v-if="retrun_cause_id != '' && (retrun_cause_id == 0 || retrun_cause_id == 2)">
					<view class="padding-all">
						<view class="h4">{{$t('lang.profile')}}<text class="dot color-red">*</text></view>
						<view class="uni-list address-info-show">
							<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
								<view class="uni-list-cell-navigate">
									<text class="title">{{$t('lang.consignee')}}</text>
									<view class="value"><input :placeholder="$t('lang.enter_consignee')" v-model="addressee"></view>
								</view>
							</view>
							<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
								<view class="uni-list-cell-navigate">
									<text class="title">{{$t('lang.phone_number')}}</text>
									<view class="value"><input :placeholder="$t('lang.enter_mobile')" v-model="mobile"></view>
								</view>
							</view>
							<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" @click="handleRegionShow">
								<view class="uni-list-cell-navigate uni-navigate-right">
									<text class="title">{{ $t('lang.region_alt') }}</text>
									<view class="value" :class="{'gay': !regionSplic}">{{ regionSplic ? regionSplic : $t('lang.select') }}</view>
								</view>
							</view>
							<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
								<view class="uni-list-cell-navigate">
									<text class="title">{{$t('lang.label_address')}}</text>
									<view class="value"><input :placeholder="$t('lang.enter_address')" v-model="address"></view>
								</view>
							</view>
						</view>
					</view>
				</view>
				<mpvue-picker :themeColor="themeColor" ref="mpvuePicker" :mode="mode" :deepLength="deepLength" :pickerValueDefault="pickerValueDefault"
				 @onConfirm="onConfirm($event,curRegion)" @onCancel="onCancel" :pickerValueArray="pickerValueArray"></mpvue-picker>
			</block>
			<view class="uni-card uni-card-not">
				<van-field v-model="return_remark" label="留言" :placeholder="$t('lang.enter_message')" type="textarea" class="my-bottom" />
			</view>
			<view class="padding-all">
				<view class="h4 m-b10">{{$t('lang.service_note')}}</view>
				<p class="f-03 col-9">{{$t('lang.return_explain_1')}}</p>
				<p class="f-03 col-9 m-top04">{{$t('lang.return_explain_2')}}</p>
				<p class="f-03 col-9 m-top04">{{$t('lang.return_explain_3')}}</p>
				<p class="f-03 col-9 m-top04">{{$t('lang.return_explain_4')}}</p>
			</view>
			<view class="btn-bar btn-bar-fixed" style="background-color: #FFFFFF;">
				<view class="btn btn-red" @click="submitBtn">{{$t('lang.submit_apply')}}</view>
			</view>
		</view>
		<dsc-common-nav></dsc-common-nav>
		
		<!--地区选择-->
		<dsc-region :display="regionShow" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="customUpdateRegion" v-if="regionLoading"></dsc-region>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniNumberBox from "@/components/uni-number-box.vue";
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscRegion from '@/components/dsc-region.vue';
	
	import universal from '@/common/mixins/universal.js';
	
	import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
	import { compressImage } from '@/common/compressImage.js'
	export default {
		mixins:[universal],
		components: {
			dscCommonNav,
			dscNotContent,
			uniNumberBox,
			dscRegion
		},
		data() {
			return {
				rec_id: 0,
				value: 1, //商品数量
				checked: false, //是否有检测报告
				return_brief: '', //退货问题描述
				retrun_cause_id: '', //退换货服务类型
				return_remark: '', //退换货留言
				causeSelected: '', //退换货原因
				materialList: [],
				imageSrc: '',
				loading:false
			};
		},
		computed: {
			...mapState({
				applyRefoundDetail: state => state.user.applyRefoundDetail,
				addressList: state => state.user.addressList
			}),
			goodsList() {
				return this.applyRefoundDetail.goods_list ? this.applyRefoundDetail.goods_list : false
			},
			consignee() {
				return this.applyRefoundDetail.consignee
			},
			goods_cause() {
				return this.applyRefoundDetail.goods_cause
			},
			parent_cause() {
				return this.applyRefoundDetail.parent_cause
			},
			shippingStatus() {
				return this.applyRefoundDetail.order ? this.applyRefoundDetail.order.shipping_status : 0
			},
			showReturnNumber(){
			    return this.applyRefoundDetail.show_return_number ? this.applyRefoundDetail.show_return_number : 0
			},
			addressee: {
				get() {
					return this.applyRefoundDetail.consignee ? this.applyRefoundDetail.consignee.consignee : ''
				},
				set(val) {
					this.applyRefoundDetail.consignee.consignee = val
				}
			},
			mobile: {
				get() {
					return this.applyRefoundDetail.consignee ? this.applyRefoundDetail.consignee.mobile : ''
				},
				set(val) {
					this.applyRefoundDetail.consignee.mobile = val
				}
			},
			address: {
				get() {
					return this.applyRefoundDetail.consignee ? this.applyRefoundDetail.consignee.address : ''
				},
				set(val) {
					this.applyRefoundDetail.consignee.address = val
				}
			},
			returnGoodsNum() {
				return this.applyRefoundDetail.order && this.applyRefoundDetail.order.shipping_status == 0 ? this.applyRefoundDetail
					.return_goods_num : this.value
			},
			returnPictures(){
				return this.applyRefoundDetail.return_pictures ? this.applyRefoundDetail.return_pictures : 5
			}
		},
		methods: {
			causeSelect(id) {
				this.retrun_cause_id = id
			},
			causeSelect2(id) {
				this.causeSelected = id
			},
			submitBtn() {
				let o = {
					rec_id: this.rec_id,
					last_option: String(this.causeSelected),
					return_remark: this.return_remark,
					return_brief: this.return_brief,
					chargeoff_status: String(this.applyRefoundDetail.order.chargeoff_status),
					return_type: Number(this.retrun_cause_id),
					return_images: this.materialList,
					return_number: this.value,
					addressee: this.addressee,
					mobile: this.mobile,
					code: this.email,
					return_address: this.address,
					province_region_id: this.regionData.province.id,
					city_region_id: this.regionData.city.id,
					district_region_id: this.regionData.district.id,
					street: this.regionData.street ? this.regionData.street.id : 0
				}
				
				if (!this.retrun_cause_id) {
					uni.showModal({
						title: this.$t('lang.hint'),
						content: this.$t('lang.fill_in_service_type')
					});
					return false
				} else if (this.causeSelected == 0) {
					uni.showModal({
						title: this.$t('lang.hint'),
						content: this.$t('lang.fill_in_return_reason')
					});
					return false
				} else if (!this.return_brief) {
					uni.showModal({
						title: this.$t('lang.hint'),
						content: this.$t('lang.fill_in_problem_desc')
					});
					return false
				}
				if (o.return_type == 0 || o.return_type == 2) {
					if (!o.province_region_id) {
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.select_city_3')
						});
						return false
					} else if (!o.city_region_id) {
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.select_city_2')
						});
						return false
					} else if (!o.district_region_id) {
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.select_city_1')
						});
						return false
					} else if (!o.street) {
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.select_city')
						});
						return false
					} else if (!o.addressee) {
						uni.showModal({
							title: this.$t('lang.hint'),
							content: this.$t('lang.tiang_di')
						});
						return false
					}
				}
				uni.request({
					url: this.websiteUrl + '/api/refound/submit_return',
					method: 'POST',
					data: o,
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							uni.showToast({
								title: res.data.data.msg,
								icon: "none"
							})
							if (res.data.data.code == 0) {
								this.returnApply()
							}
						}
					}
				});
			},
			deleteImg(val) {
				let that = this
				uni.showModal({
					title: this.$t('lang.hint'),
					content: this.$t('lang.confirm_remove_pic'),
					success(res) {
						if (res.confirm) {
							that.materialList.splice(val, 1)
						}
					}
				})
			},
			returnApply() {
				setTimeout(() => {
					uni.navigateTo({
						url: '/pagesB/afterSales/afterSales',
					})
				}, 1000)
			},
			switch2Change(e) {
				this.checked = e.target.value
			},
			chooseImage(val) {
				let that = this
				uni.chooseImage({
					count: 1,
					sizeType: ['original', 'compressed'],
					sourceType: ['album', 'camera'],
					success: (res) => {
						that.imageSrc = res.tempFilePaths[0];

						if (that.materialList.length + 1 > this.returnPictures) {
							uni.showModal({
								title: this.$t('lang.hint'),
								content: this.$t('lang.return_max_pic_prompt') + this.returnPictures + this.$t('lang.return_max_pic_prompt2')
							});
							return false;
						}

						// #ifdef APP-PLUS
						that.uploadImage(val);
						// #endif

						// #ifdef MP-WEIXIN
						let size = res.tempFiles[0].size; //上传图片大小
						let maxSize = 1024 * 1024 * 2; //最大可上传2mb
						if (size > maxSize) {
							uni.compressImage({
								src: res.tempFilePaths[0],
								quality: 10,
								success: (result) => {
									that.imageSrc = result.tempFilePath;
									that.uploadImage(val);
								},
								fail: (result) => {
									console.log(result)
								}
							})
						} else {
							that.uploadImage(val);
						}
						// #endif
					}
				})
			},
			async uploadImage(val) {
				let that = this;
				uni.showLoading({
					mask: true,
					title: this.$t('lang.shang_chu')
				});

				//app压缩图片
				// #ifdef APP-PLUS
				that.imageSrc = await compressImage(that.imageSrc);
				// #endif
				
				pathToBase64(that.imageSrc).then(base64 => {
					that.$store.dispatch('setMaterial', {
						file: {
							content: base64
						},
						type: val
					}).then(data => {
						if (data.status == 'success') {
							uni.hideLoading();
							that.materialList.push(data.data[0]);
						}
					})
				}).catch(error => {
					console.error(error, 5);
				});
			},
			bindChange(e) {
				this.value = e
			},
			customUpdateRegion(e){
				this.regionData = e;
			}
		},
		onLoad(e) {
			this.rec_id = e.rec_id
			this.$store.dispatch('setApplyRefound', {
				rec_id: e.rec_id,
				order_id: e.order_id
			})
			
			this.regionData = this.getRegionData;
		},
		watch: {
			regionShow() {
				if (this.regionShow) {
					this.regionLoading = true
				}
			},
			applyRefoundDetail(){
				this.value = this.applyRefoundDetail.return_goods_num;
				
				/* 已申请跳转到申请列表页 */
				if(this.applyRefoundDetail.msg){
					uni.showToast({
						title: this.applyRefoundDetail.msg,
						icon: "none"
					})
					setTimeout(() => {
						uni.redirectTo({ url:'/pagesB/order/order' });
					},1000)
				}else{
					this.loading = true
				}
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

	.col-7 {
		color: #777;
	}

	.m-top04 {
		margin-top: 10upx;
	}

	.m-top06 {
		margin-top: 16upx;
	}

	.f-03 {
		font-size: 24upx;
	}

	.fr {
		float: right;
	}

	.padding-all {
		padding: 20upx 20upx;
	}

	.user-detail {
		padding-bottom: 150upx;
	}

	.uni-card {
		.h4 {
			color: #777;
			margin-bottom: 20upx;

			.dot {
				margin-left: 10upx;
			}
		}
	}

	.select-one-1 {
		background: #fff;
	}

	.ect-selects .ect-select {
		margin: 0 6upx;
		display: inline-block;
		position: relative;
		font-size: 28upx;
		margin-right: 10upx;
	}

	.ect-selects .ect-select text {
		padding: 10upx 24upx;
		display: block;
		text-align: center;
		border: 1upx solid #efefef;
		border-radius: 4upx;
		color: #666;
	}

	.ect-selects .ect-select.active text {
		border-color: #f92028;
		color: #f92028;
	}

	.user-return-img {
		border: 2upx dashed #ccc;
		width: 200upx;
		height: 200upx;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;

		.i-jia {
			width: 100upx;
			height: 100upx;
			position: relative;

			&:before,
			&:after {
				position: absolute;
				content: '';
				width: 6upx;
				height: 100upx;
				background: #ddd;
				left: 0;
				top: 0;
				margin: auto;
				right: 0;
				bottom: 0;
			}

			&:after {
				transform: rotate(90deg);
			}
		}

		text {
			display: block;
			text-align: center;
			padding-top: 10upx;
			font-size: 24upx;
			color: #999;
		}
	}

	.uni-list-cell:after {
		left: 0;
	}

	.uni-card .address-info-show .uni-list-cell-navigate {
		padding: 0;
	}

	.uni-card .address-info-show .uni-list-cell-navigate .title {
		padding: 20upx 30upx;
		min-width: 100upx;
	}

	.uni-card .address-info-show .uni-list-cell-navigate .value text {
		width: 100%;
	}

	.goods-info-img-box {
		.goods-info-img {
			float: left;
			position: relative;
			width: 200upx;
			height: 200upx;
			border: 1px solid #ccc;
			margin: 0 20upx 20upx 0;

			image {
				position: absolute;
				width: 100%;
				height: 100%;
				left: 0;
				top: 0;
			}

			.iconfont {
				position: absolute;
				right: 3upx;
				top: 3upx;
				z-index: 3;
				color: #f00;
				text-shadow: 0 0 3upx rgba(0, 0, 0, .3)
			}
		}
	}
</style>
