<template>
	<view class="giftcard-address">
		<form @submit="formSubmit">
			<view class="uni-card uni-card-not">
				<view class="header-title">{{$t('lang.address')}}</view>
				<view class="uni-list">
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.consignee')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_consignee')" name="consignee" v-model="consignee"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_mobile2')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_contact_phone')" name="mobile" v-model="mobile"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" @click="handleRegionShow">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.region_alt')}}</text>
							<view class="value" :class="{'gay': !regionSplic}">{{ regionSplic ? regionSplic : $t('lang.select') }}</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.address_alt')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_address')" name="address" v-model="address"></view>
						</view>
					</view>
				</view>
			</view>
			<view class="btn-bar btn-bar-fixed">
				<button formType="submit" type="primary" class="btn btn-red">{{$t('lang.save')}}</button>
			</view>
		</form>
		
		<!--地区选择-->
		<dsc-region :display="regionShow" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></dsc-region>
	</view>
</template>

<script>
	import { mapState } from 'vuex';
	import universal from '@/common/mixins/universal.js';
	import dscRegion from '@/components/dsc-region.vue';
	
	var graceChecker = require("@/common/graceChecker.js");
	export default {
		mixins:[universal],
		data() {
			return {
				goods_id:'',
				consignee:'',
				mobile:'',
				email:'',
				address:'',
				deepLength: 1,
			}
		},
		components:{
			dscRegion
		},
		computed:{
		},
		methods: {
			formSubmit(e){
				var rule = [
					{name:"consignee", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.consignee_not_null')},
					{name:"mobile", checkType : "phoneno", checkRule:"",  errorMsg: this.$t('lang.mobile_not_null')},
					{name:"address", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.address_not_null')},
				];

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);

				if(checkRes){
					if(this.regionData.province.id == ''){
						uni.showToast({title: this.$t('lang.cannot_be_empty_1'), icon:"none"});
						return false
					}

					if(this.regionData.city.id == ''){
						uni.showToast({title: this.$t('lang.cannot_be_empty_2'), icon:"none"});
						return false
					}

					if(this.regionData.district.id == ''){
						uni.showToast({title: this.$t('lang.cannot_be_empty_3'), icon:"none"});
						return false
					}

					if(this.regionData.street.id == ''){
						uni.showToast({title: this.$t('lang.streets_must_not_empty'), icon:"none"});
						return false
					}

					uni.showLoading({title: this.$t('lang.loading')});

					uni.request({
						url:this.websiteUrl + '/api/gift_gard/check_take',
						data:{
							goods_id:this.goods_id,
							consignee:this.consignee,
							mobile:this.mobile,
							country:1,
							province:this.regionData.province.id,
							city:this.regionData.city.id,
							district:this.regionData.district.id,
							street:this.regionData.street.id,
							address:this.address
						},
						method:'GET',
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							uni.hideLoading();

							let data = res.data
							uni.showToast({
								title:data.data.msg,
								icon:'none'
							});
							if(data.data.error == 0){
								setTimeout(()=>{
									uni.redirectTo({
										url:'/pagesA/giftcard/order/order'
									})
								},500)
							}
						},
					});
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			}
		},
		onLoad(e){
			this.goods_id = e.id;
			
			this.regionData = this.getRegionData;
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
.header-title{ padding: 20upx 30upx; border-bottom: 1px solid #DDDDDD; font-size: 30upx; }
.uni-list-cell::after{ left: 0;}
</style>
