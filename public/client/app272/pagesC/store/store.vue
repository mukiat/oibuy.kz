<template>
	<view class="store-box">
		<view class="store-header" id="storeHeader" ref="storeHeader">
			<view class="uni-card uni-card-not">
				<view class="uni-list">
					<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="handleRegionShow">
						<view class="uni-list-cell-navigate uni-navigate-right">
							<text class="title">{{$t('lang.label_region_select')}}</text>
							<view class="value" :class="{'gay': !regionSplicFormat}">{{regionSplicFormat ? regionSplicFormat : $t('lang.select')}}</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<scroll-view class="scrollList" scroll-y :style="{height:winHeight + 'px'}">
			<view class="store-list">
				<view class="item" :class="{'active':store_id == item.id, 'disabled': item.is_stocks == 0}" v-for="(item,index) in storeContent"
				 :key="index" @click="storeClick(item.id,item.is_stocks)">
					<view class="flow-have-adr">
						<view class="head uni-flex-common uni-space-between">
							<view class="name">{{ item.stores_name }}<text style="margin-left: 20rpx;" class="uni-blue" v-if="item.distance_format">距您{{item.distance_format.value}}{{item.distance_format.unit}}</text></view>
							<view class="check_the_wiring" @click.stop="checkTheWiring(item)"><text class="iconfont icon-weizhi1"></text> 查看路线</view>
						</view>
						<view class="address">[{{ item.address }} {{ item.stores_address }}]</view>
					</view>
				</view>
			</view>
		</scroll-view>
		<view class="store-footer" id="storeFooter" ref="storeFooter">
			<form @submit="formSubmit">
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell">
							<view class="uni-list-cell-navigate uni-navigate-right">
								<text class="title">{{$t('lang.arrival_time')}}：</text>
								<view class="value">
									<picker mode="date" :value="dataTime" :start="startDate" :end="endDate" @change="bindDateChange">{{dataTime}}</picker>
								</view>
							</view>
						</view>
						<view class="uni-list-cell">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.label_phone_number')}}</text>
								<view class="value">
									<input :placeholder="$t('lang.fill_in_mobile')" type="number" name="mobile" v-model="mobile" />
								</view>
							</view>
						</view>
						<view class="btn-warp">
							<button class="btn btn-close" @click="onClose">{{$t('lang.close')}}</button>
							<button class="btn" formType="submit">{{$t('lang.immediately_private')}}</button>
						</view>
					</view>
				</view>
			</form>
		</view>

		<!--地区选择-->
		<dsc-region :display="regionShow" :regionOptionData="regionData" :isLevel="4" @updateDisplay="getRegionShow" @updateRegionDate="customUpdateRegion" v-if="regionLoading"></dsc-region>
	</view>
</template>

<script>
	function getDate(type) {
		const date = new Date();

		let year = date.getFullYear();
		let month = date.getMonth() + 1;
		let day = date.getDate();

		if (type === 'start') {
			year = year - 60;
		} else if (type === 'end') {
			year = year + 2;
		}
		month = month > 9 ? month : '0' + month;
		day = day > 9 ? day : '0' + day;

		return `${year}-${month}-${day}`;
	}

	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscRegion from '@/components/dsc-region.vue';
	
	import universal from '@/common/mixins/universal.js';

	var graceChecker = require("@/common/graceChecker.js");
	export default {
		mixins:[universal],
		data() {
			return {
				rec_id: '',
				goods_id: 0,
				attr_id: 0,
				num: 1,
				mobile: '',
				dataTime: getDate({
					format: true
				}),
				startDate: getDate('start'),
				endDate: getDate('end'),
				storeContent: [],
				store_id: 0,
				navHeight: 0,
				height: 0,
				ru_id: '',
				isSingle:''
			}
		},
		components: {
			uniIcons,
			uniPopup,
			dscCommonNav,
			dscRegion
		},
		computed: {
			winHeight() {
				return uni.getSystemInfoSync().windowHeight - this.height - this.navHeight;
			},
			regionSplicFormat(){
				return this.regionData.province.name + ' ' + this.regionData.city.name + ' ' + this.regionData.district.name;
			}
		},
		methods: {
			storeList() {
				let o = {}
				if (this.rec_id) {
					o = {
						province_id: this.regionData.province.id,
						city_id: this.regionData.city.id,
						district_id: this.regionData.district.id,
						street_id: this.regionData.street.id,
						goods_id: 0,
						rec_id: this.rec_id,
						page: 1,
						size: 10,
						lat:this.regionData && this.regionData.postion ? this.regionData.postion.lat : '',
						lng:this.regionData && this.regionData.postion ? this.regionData.postion.lng : '',
					}
				} else {
					o = {
						province_id: this.regionData.province.id,
						city_id: this.regionData.city.id,
						district_id: this.regionData.district.id,
						street_id: this.regionData.street.id,
						goods_id: this.goods_id,
						spec_arr: this.attr_id,
						page: 1,
						size: 10,
						lat:this.regionData && this.regionData.postion ? this.regionData.postion.lat : '',
						lng:this.regionData && this.regionData.postion ? this.regionData.postion.lng : '',
					}
				}
				
				uni.request({
					url: this.websiteUrl + '/api/offline-store/list',
					method: 'POST',
					data: o,
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.storeContent = res.data.data.list
						this.storeContent.forEach((v, i) => {
							if (i == 0 && v.is_stocks != 0) {
								this.store_id = v.id
								this.ru_id = v.ru_id
							}
						})

						this.mobile = res.data.data.phone ? res.data.data.phone : ''
					}
				})
			},
			bindDateChange: function(e) {
				this.dataTime = e.target.value
			},
			//自定义更新地区信息
			customUpdateRegion(e){
				this.regionData = e;
				
				//更新列表
				this.storeList();
			},
			onClose() {
				if (this.rec_id) {
					uni.reLaunch({
						url: "/pages/cart/cart"
					})
				} else {
					uni.reLaunch({
						url: '/pagesC/goodsDetail/goodsDetail?id=' + this.goods_id
					})
				}
			},
			formSubmit(e) {
				var rule = [{
						name: "mobile",
						checkType: "notnull",
						checkRule: "",
						errorMsg: this.$t('lang.fill_in_mobile')
					},
					{
						name: "mobile",
						checkType: "phoneno",
						checkRule: "",
						errorMsg: this.$t('lang.mobile_not_null')
					},
				];
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				
				if(this.store_id == 0){
					uni.showToast({ title: this.$t('lang.fill_in_store'), icon: 'none' });
					return
				}

				if (checkRes) {
					if (this.$isLogin()) {
						if (this.rec_id) {
							uni.request({
								url: this.websiteUrl + '/api/cart/offline/update',
								method: 'POST',
								data: {
									rec_id: this.rec_id,
									store_id: this.store_id,
									store_mobile: this.mobile,
									take_time: this.dataTime,
									num: ''
								},
								header: {
									'Content-Type': 'application/json',
									'token': uni.getStorageSync('token'),
									'X-Client-Hash': uni.getStorageSync('client_hash')
								},
								success: (res) => {
									let data = res.data.data
									if (data.error == 0) {
										uni.navigateTo({
											url: "/pagesC/checkoutone/checkoutone?rec_type=12&store_id=" + this.store_id + '&ru_id=' + this.ru_id+"&isSingle="+this.isSingle+"&rec_id="+ this.rec_id+"&stor=1"
										})
									} else {
										uni.showToast({
											title: data.msg,
											icon: 'none'
										});
									}
								}
							})
						} else {
							this.$store.dispatch('setStoresCart', {
								goods_id: this.goods_id,
								store_id: this.store_id,
								num: this.num,
								spec: this.attr_id,
								store_mobile: this.mobile,
								take_time: this.dataTime,
								warehouse_id: '0',
								area_id: '0',
								parent_id: '0',
								quick: 1,
								rec_type: 12,
								parent: 0
							}).then(res => {
								if (res.data == true) {
									uni.navigateTo({
										url: "/pagesC/checkoutone/checkoutone?rec_type=12&store_id=" + res.store_id + '&goods_id=' + this.goods_id +'&spec_arr=' + this.attr_id + '&num=' + this.num+"&isSingle="+this.isSingle+"&rec_id="+this.rec_id+"&stor=1"
									})
								} else {
									if (res.data.error == 1) {
										uni.showToast({
											title: res.data.msg,
											icon: 'none'
										});
									} else {
										uni.showToast({
											title: this.$t('lang.private_store_fail'),
											icon: 'none'
										});
									}
								}
							})
						}
					} else {
						uni.showModal({
							content: this.$t('lang.login_user_not'),
							success: (res) => {
								if (res.confirm) {
									uni.navigateTo({
										url: '/pagesB/login/login?delta=1'
									})
								}
							}
						})
					}
				} else {
					uni.showToast({
						title: graceChecker.error,
						icon: "none"
					});
				}
			},
			storeClick(id, is_stocks) {
				if (is_stocks != 0) {
					this.store_id = id
				} else {
					uni.showToast({
						title: this.$t('lang.understock_not_submit'),
						icon: 'none'
					});
				}
			},
			// 查看路线
			checkTheWiring(data) {
				const { address, stores_address, latitude, longitude } = data;
				uni.openLocation({
					latitude: parseFloat(latitude),
					longitude: parseFloat(longitude),
					name: stores_address || '',
					address: address || '',
					success: () => {
						console.log('success');
					},
					fail: (err) => {
						uni.showToast({title: err, icon: 'none'})
					}
				})
			}
		},
		onReady() {
			let that = this
			var view = uni.createSelectorQuery().select("#storeFooter");
			var view2 = uni.createSelectorQuery().select("#storeHeader");
			view.boundingClientRect(data => {
				that.height = data.height
			}).exec();

			view2.boundingClientRect(data => {
				that.navHeight = data.height
			}).exec();
		},
		onLoad(e) {
			this.goods_id = e.id ? e.id : 0;
			this.attr_id = e.attr_id ? e.attr_id : '';
			this.num = e.num ? e.num : 0;
			this.rec_id = e.rec_id ? e.rec_id : '';
			this.ru_id = e.ru_id ? e.ru_id : 0;
			this.isSingle = e.isSingle ? e.isSingle : '';
			this.regionData = this.getRegionData;
			this.storeList();
		},
		watch:{
			regionShow() {
				if (this.regionShow) {
					this.regionLoading = true
				}
			}
		}
	}
</script>

<style>
	.uni-card {
		margin: 0;
	}

	.uni-list-cell-navigate {
		justify-content: flex-start;
	}

	.uni-list-cell-navigate .title {
		min-width: 100upx;
		color: #999999;
		margin-right: 15upx;
	}

	.store-footer .uni-list-cell-navigate .value {
		flex: 1;
	}

	.store-footer .uni-list-cell-navigate .value picker {
		width: 100%;
	}

	.scrollList {
		padding: 20upx;
		box-sizing: border-box;
	}

	.store-list .item {
		background: #FFFFFF;
		padding: 20upx;
		box-shadow: 1px 0px 5px rgba(100, 100, 100, 0.2);
		border: 1px solid #fff;
		margin-bottom: 20upx;
	}

	.store-list .item.active {
		border: 1px dashed #ec5151;
		color: #ec5151;
	}

	.store-list .item:last-child {
		margin-bottom: 0;
	}

	.btn-warp {
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
	}

	.btn-warp .btn {
		width: 50%;
		background: #f92028;
		color: #FFFFFF;
		border-radius: 0;
		border: 0;
	}

	.btn-warp .btn-close {
		background: #fba534;
	}
	
	.check_the_wiring {
		padding: 0 6rpx;
		border-radius: 6rpx;
		border: 1px solid #ccc;
		font-size: 24rpx;
		color: #333;
	}
	.check_the_wiring .iconfont {
		font-size: 26rpx;
		color: #333;
		margin-right: 4rpx;
	}
</style>
