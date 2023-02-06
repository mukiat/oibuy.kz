<template>
	<view class="fixed-bottom-padding">
		<form @submit="formSubmit">
			<view class="uni-card uni-card-not">
				<view class="header-title">{{$t('lang.vat_invoice_info')}}</view>
				<view class="uni-list">
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_company_name')}}</text>
							<view class="value"><input :placeholder="$t('lang.git_dan_name')" name="company_name" v-model="company_name" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.taxpayer_id_number')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_taxpayer_id_number')" name="tax_id" v-model="tax_id" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.register_address')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_register_address')" name="company_address" v-model="company_address" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.register_tel')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_register_tel')" name="company_telephone" v-model="company_telephone" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.bank_of_deposit')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_bank_of_deposit')" name="bank_of_deposit" v-model="bank_of_deposit" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.bank_account')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_bank_account')" name="bank_account" v-model="bank_account" :disabled="isDisabled"></view>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="header-title">{{$t('lang.bill_consignee_info')}}</view>
				<view class="uni-list">
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_name')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_name')" name="consignee_name" v-model="consignee_name" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_mobile')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_check_taker_mobile')" name="consignee_mobile_phone" v-model="consignee_mobile_phone" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" @click="handleRegion">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.region_alt')}}</text>
							<view class="value">
								<block v-if="regionData.province.id">{{ regionData.province.name }}</block>
								<block v-if="regionData.city.id">{{ regionData.city.name }}</block>
								<block v-if="regionData.district.id">{{ regionData.district.name }}</block>
								<block v-if="regionData.street.id">{{ regionData.street.name }}</block>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.address_alt')}}</text>
							<view class="value"><input :placeholder="$t('lang.enter_address')" name="consignee_address" v-model="consignee_address" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="id > 0">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.audit_status')}}</text>
							<view class="value">
								<view class="uni-red" v-if="audit_status == 0">{{$t('lang.audit_status_01')}}</view>
								<view class="uni-red" v-else-if="audit_status == 1">{{$t('lang.audit_status_02')}}</view>
								<view class="uni-red" v-else-if="audit_status == 2">{{$t('lang.audit_status_03')}}</view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="btn-bar btn-bar-fixed">
				<block v-if="id > 0">
					<view class="btn btn-org" @click="deleteInv">{{$t('lang.delete')}}</view>
					<view class="btn btn-red" @click="editInv" v-if="isDisabled == true">{{$t('lang.mod')}}</view>
					<button formType="submit" type="primary" class="btn btn-red" v-else>{{$t('lang.save')}}</button>
				</block>
				<block v-else>
					<button formType="submit" type="primary" class="btn btn-red">{{$t('lang.add')}}</button>
				</block>
			</view>
		</form>

		<!--地区选择-->
		<dsc-region :display="regionShow" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate" v-if="regionLoading"></dsc-region>
	</view>
</template>

<script>
	import { mapState } from 'vuex';
	import uniPopup from '@/components/uni-popup.vue';
	import universal from '@/common/mixins/universal.js';
	import dscRegion from '@/components/dsc-region.vue';
	
	var graceChecker = require("@/common/graceChecker.js");

	export default {
		mixins:[universal],
		data() {
			return {
				isDisabled: true,
				id: '',
                audit_status: '',
                company_name: '',
                tax_id: '',
                company_address: '',
                company_telephone: '',
                bank_of_deposit: '',
                bank_account: '',
                consignee_name: '',
                consignee_mobile_phone: '',
                consignee_address: '',
				deepLength: 1,
				regionShow:false,
				regionLoading:false,
			};
		},
		components:{
			uniPopup,
			dscRegion
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/invoice/invoice'
			}
		},
		computed:{
			
		},
		watch:{
			regionShow() {
				if (this.regionShow) {
					this.regionLoading = true
				}
			},
		},
		methods:{
			editInv(){
				this.isDisabled = false
			},
			formSubmit(e){
				var rule = [
					{name:"company_name", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.git_dan_name')},
					{name:"tax_id", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.fill_in_taxpayer_id_number')},
					{name:"company_address", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.fill_in_register_address')},
					{name:"company_telephone", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.fill_in_register_tel')},
					{name:"bank_of_deposit", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.fill_in_bank_of_deposit')},
					{name:"consignee_name", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.fill_in_check_taker_name')},
					{name:"consignee_mobile_phone", checkType : "phoneno", checkRule:"",  errorMsg:this.$t('lang.fill_in_check_taker_mobile')},
					{name:"consignee_address", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.fill_in_address')},
				];

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);

				if(checkRes){
					let o = {
						id: this.id,
						company_name: this.company_name,
						tax_id: this.tax_id,
						company_address: this.company_address,
						company_telephone: this.company_telephone,
						bank_of_deposit: this.bank_of_deposit,
						bank_account: this.bank_account,
						consignee_name: this.consignee_name,
						consignee_mobile_phone: this.consignee_mobile_phone,
						consignee_address: this.consignee_address,
						country: 1,
						province: this.regionData.province.id,
						city: this.regionData.city.id,
						district: this.regionData.district.id,
						street: this.regionData.street.id,
						invoice_type:1
					}

					if(this.id > 0){
						uni.request({
							url:this.websiteUrl + '/api/invoice/update',
							method:'PUT',
							data:o,
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								if (res.data.status == 'success') {
									uni.showToast({ title: this.$t('lang.edit_submit_success'), icon: "success" });

									setTimeout(()=>{
										uni.navigateTo({ url:'../account/account' });
									},1000)
								} else {
									uni.showToast({ title: this.$t('lang.edit_fail_fill_again'), icon: "none" });
								}
							}
						});
					}else{
						uni.request({
							url:this.websiteUrl + '/api/invoice/store',
							method:'POST',
							data:{
								id: this.id,
								company_name: this.company_name,
								tax_id: this.tax_id,
								company_address: this.company_address,
								company_telephone: this.company_telephone,
								bank_of_deposit: this.bank_of_deposit,
								bank_account: this.bank_account,
								consignee_name: this.consignee_name,
								consignee_mobile_phone: this.consignee_mobile_phone,
								consignee_address: this.consignee_address,
								country: 1,
								province: this.regionData.province.id,
								city: this.regionData.city.id,
								district: this.regionData.district.id,
								street: this.regionData.street.id,
								invoice_type:1
							},
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								if (res.data.status == 'success') {
									uni.showToast({ title: this.$t('lang.add_vat_invoice_success'), icon: "none" });

									setTimeout(()=>{
										uni.navigateTo({ url:'../account/account' });
									},1000)
								} else {
									uni.showToast({ title: this.$t('lang.add_vat_invoice_fail'), icon: "none" });
								}
							}
						});
					}
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			},
			deleteInv(){
				uni.request({
					url:this.websiteUrl + '/api/invoice/destroy',
					method:'DELETE',
					data:{
						invoice_type:1,
						id:this.id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							uni.showToast({ title: this.$t('lang.delete_success'), icon: "success" });
							setTimeout(()=>{
								uni.navigateTo({ url:'../account/account' });
							},1000)
						} else {
							uni.showToast({ title: this.$t('lang.delete_fail'), icon: "none" });
						}
					}
				});
			},
			handleRegion(){
				this.regionShow = true
			},
		},
		onLoad(){
			//默认地区
			this.regionData = this.getRegionData;
			
			uni.request({
				url:this.websiteUrl + '/api/invoice',
				method:'GET',
				data:{},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					let data = res.data.data.user_vat_invoice;
					if (data != '') {
					    this.id = data.id
					    this.audit_status = data.audit_status
					    this.company_name = data.company_name
					    this.tax_id = data.tax_id
					    this.company_address = data.company_address
					    this.company_telephone = data.company_telephone
					    this.bank_of_deposit = data.bank_of_deposit
					    this.bank_account = data.bank_account
					    this.consignee_name = data.consignee_name
					    this.consignee_mobile_phone = data.consignee_mobile_phone
					    this.consignee_address = data.consignee_address

						this.regionData.province.id = data.province ? data.province : ''
						this.regionData.province.name = data.province_name ? data.province_name : ''
						this.regionData.city.id = data.city ? data.city : ''
						this.regionData.city.name = data.city_name ? data.city_name : ''
						this.regionData.district.id = data.district ? data.district : ''
						this.regionData.district.name = data.district_name ? data.district_name : ''
						this.regionData.street.id = data.street ? data.street : ''
						this.regionData.street.name = data.street_name ? data.street_name : this.$t('lang.select')
					} else {
					    this.isDisabled = false
					}
				},
			});
		}
	}
</script>

<style>
.header-title{ padding: 20upx 30upx; border-bottom: 1px solid #DDDDDD; font-size: 30upx;}
</style>
