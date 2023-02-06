<template>
	<div class="store_cont-box" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="store_cont_top">
			<div class="region_select">
				<van-cell-group class="van-cell-noleft">
					<van-cell :title="$t('lang.label_region_select')" v-model="regionSplicFormat" @click="handelRegionShow"
					 is-link />
				</van-cell-group>
			</div>
		</div>
		<section class="store_cont_warp">
			<div class="store_list">
				<ul class="new-store-radio" v-if="storeContent.length>0">
					<li :class="{'active':store_id == item.id, 'disabled': item.is_stocks == 0}" v-for="(item,index) in storeContent" :key="index" @click="storeClick(item.id,item.is_stocks)" v-if="item.is_stocks > 0">
						<div class="flow-have-adr padding-all">
							<div class="f-h-adr-title">
								<label>{{ item.stores_name }}<em>距您{{item.distance_format.value}}{{item.distance_format.unit}}</em></label>
								<span><a href="javascript:;" @click="locationMap(item)"><i class="iconfont icon-location"></i>{{$t('lang.view_route')}}</a></span>
							</div>
							<p class="f-h-adr-con t-remark m-top06 store-address-cont">[{{ item.address }} {{ item.stores_address }}]</p>
						</div>
					</li>
				</ul>
				<NotCont v-else></NotCont>
			</div>
		</section>
		<div class="filter-btn store-btn-box">
			<div class="van-cell-noleft2">
				<van-cell :title="$t('lang.arrival_time')" v-model="dataTime" @click="dataShow" is-link />
			</div>
			<van-field :label="$t('lang.phone_number')" type="number" v-model="mobile" :placeholder="$t('lang.fill_in_mobile')" />
			<div class="van-sku-actions">
				<van-button type="default" class="van-button--bottom-action" @click="onClose">{{$t('lang.close')}}</van-button>
				<van-button type="primary" class="van-button--bottom-action" @click="onStorebtn">{{$t('lang.immediately_private')}}</van-button>
			</div>
		</div>
		<Region :display="regionShow" :regionOptionDate="regionOptionDate" :isLevel="4" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>
		<van-popup v-model="show" position="bottom" class="show-popup-bottom show-goods-coupon">
			<van-datetime-picker v-model="currentDate" type="datetime" @confirm="dataConfirm" @cancel="dataCancel" />
		</van-popup>
	</div>
</template>

<script>
	import qs from 'qs'
	import {
		Cell,
		CellGroup,
		Field,
		Button,
		Popup,
		DatetimePicker,
		Toast,
		Dialog,
		Waterfall
	} from 'vant'

	import NotCont from '@/components/NotCont'

	import format from '@/mixins/date-format'
	import formProcessing from '@/mixins/form-processing'

	export default {
		mixins: [formProcessing],
		data() {
			return {
				show: false,
				storeContent: [],
				mobile: '',
				dataTime: '',
				minHour: 10,
				maxHour: 20,
				minDate: new Date(),
				maxDate: new Date(2019, 10, 1),
				currentDate: new Date(),
				store_id: 0,
				ru_id: this.$route.query.ru_id,
				rec_id: this.$route.query.rec_id ? this.$route.query.rec_id : '',
				isSingle: this.$route.query.isSingle ? this.$route.query.isSingle : '',
				disabled:false,
				page:1,
				size:10,
				footerCont:false,
				loading:true,
			}
		},
		directives: {
    	WaterfallLower: Waterfall('lower')
		},
		components: {
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
			[Field.name]: Field,
			[Button.name]: Button,
			[Popup.name]: Popup,
			[DatetimePicker.name]: DatetimePicker,
			[Toast.name]: Toast,
			[Dialog.name]: Dialog,
			NotCont
		},
		created() {
			let o = {}

			if(this.getRegionData){
      			this.regionOptionDate = this.getRegionData;
				if (this.rec_id) {
					o = {
						province_id: this.regionOptionDate.province.id,
						city_id: this.regionOptionDate.city.id,
						district_id: this.regionOptionDate.district.id,
						street_id: this.regionOptionDate.street.id,
						goods_id: 0,
						rec_id: this.rec_id,
						page: this.page,
						size: this.size,
						lat: this.getRegionData.postion ? this.getRegionData.postion.lat : '',
						lng: this.getRegionData.postion ? this.getRegionData.postion.lng : '',
					}
				} else {
					o = {
						province_id: this.regionOptionDate.province.id,
						city_id: this.regionOptionDate.city.id,
						district_id: this.regionOptionDate.district.id,
						street_id: this.regionOptionDate.street.id,
						goods_id: this.$route.query.id,
						spec_arr: this.$route.query.attr_id,
						num: this.$route.query.num,
						page: this.page,
						size: this.size,
						lat: this.getRegionData.postion ? this.getRegionData.postion.lat : '',
						lng: this.getRegionData.postion ? this.getRegionData.postion.lng : '',
					}
				}
				this.storeList(o)
			}
		},
		mounted() {
			this.dataTime = format.formatDate(this.minDate)
		},
		computed: {
			isLogin() {
				return localStorage.getItem('token') == null ? false : true
			},
			regionSplicFormat(){
				return this.regionOptionDate.province.name + ' ' + this.regionOptionDate.city.name + ' ' + this.regionOptionDate.district.name;
			}
		},
		methods: {
			storeList(o) {
				this.$http.post(`${window.ROOT_URL}api/offline-store/list`, qs.stringify(o)).then(({
					data
				}) => {
					if(this.page > 1){
						data.data.list.forEach(v=>{
							this.storeContent.push(v)
						})
					}else{
						this.storeContent = data.data.list
					}
					
					this.storeContent.forEach((v, i) => {
						if (i == 0 && v.is_stocks != 0) {
							this.store_id = v.id
							this.ru_id = v.ru_id
						}
					})
					this.mobile = data.data.phone ? data.data.phone : ''
				})
			},
			storeClick(id, is_stocks) {
				if (is_stocks != 0) {
					this.store_id = id
				} else {
					Toast(this.$t('lang.understock'))
				}
			},
			handelRegionShow() {
				this.store_id = 0;
				this.regionShow = this.regionShow ? false : true
			},
			dataShow() {
				this.show = true
			},
			onClose() {
				if (this.rec_id) {
					this.$router.push({
						name: 'cart'
					})
				} else {
					this.$router.push({
						name: 'goods',
						params: {
							id: this.$route.query.id
						}
					})
				}
			},
			onStorebtn() {
				if (!this.checkMobile()) {
					Toast(this.$t('lang.mobile_not_null'))
					return false
				} else if (this.dataTime == '') {
					Toast(this.$t('lang.fill_in_arrival_time'))
					return false
				} else if (this.store_id == 0) {
					Toast(this.$t('lang.fill_in_store'))
					return false
				}
				
				if (this.isLogin) {
					if (this.rec_id) {
						this.$http.post(`${window.ROOT_URL}api/cart/offline/update`, qs.stringify({
							rec_id: this.rec_id,
							store_id: this.store_id,
							store_mobile: this.mobile,
							take_time: this.dataTime,
							num: ''
						})).then(({
							data
						}) => {
							if (data.data.error == 0) {
								this.$router.push({
									name: 'checkoutone',
									query: {
										stor: 1,
										ru_id: this.ru_id,
										rec_type: 12,
										store_id: this.store_id,
										isSingle:this.isSingle,
										rec_id:this.rec_id
									}
								})
							} else {
								Toast(data.data.msg)
							}
						})
					} else {
						this.$store.dispatch('setStoresCart', {
							goods_id: this.$route.query.id,
							store_id: this.store_id,
							num: this.$route.query.num,
							spec: this.$route.query.attr_id,
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
								this.$router.push({
									name: 'checkoutone',
									query: {
										stor: 1,
										ru_id: this.ru_id,
										rec_type: 12,
										store_id: res.store_id,
										goods_id: this.$route.query.id,
										spec_arr: this.$route.query.attr_id,
										num: this.$route.query.num,
										isSingle:this.isSingle,
										rec_id:this.rec_id
									}
								})
							} else {
								if (res.data.error == 1) {
									Toast(res.data.msg)
								} else {
									Toast(this.$t('lang.private_store_fail'))
								}
							}
						})
					}
				} else {
					let msg = this.$t('lang.login_user_invalid')
					this.notLogin(msg)
				}
			},
			dataConfirm() {
				this.dataTime = format.formatDate(this.currentDate)
				this.show = false
			},
			dataCancel() {
				this.show = false
			},
			//手机验证
			checkMobile() {
				let rule = /^(\d{10})$/
				if (rule.test(this.mobile)) {
					return true
				} else {
					return false
				}
			},
			mapRange(lat, lng) {
				this.$store.dispatch('setShopMap', {
					lat: lat,
					lng: lng
				}).then(res => {
					if (res.status == 'success') {
						window.location.href = res.data
					} else {
						Toast(this.$t('lang.locate_failure'))
					}
				})
			},
			notLogin(msg) {
				let url = window.location.href;
				let query;

				if (this.rec_id) {
					query = {
						rec_id: this.rec_id
					}
				} else {
					query = {
						id: this.$route.query.id,
						attr_id: this.$route.query.attr_id,
						num: this.$route.query.num
					}
				}

				Dialog.confirm({
					message: msg,
					className: 'text-center'
				}).then(() => {
					this.$router.push({
						name: 'login',
						query: {
							redirect: {
								name: 'storeGoods',
								query: query,
								url: url
							}
						}
					})
				}).catch(() => {

				})
			},
			loadMore(){
				setTimeout(() => {
					let o = {}
					this.disabled = true
			    	if(this.page * this.size == this.storeContent.length){
			  			this.page ++
			  			if (this.rec_id) {
								o = {
									province_id: this.regionOptionDate.province.id,
									city_id: this.regionOptionDate.city.id,
									district_id: this.regionOptionDate.district.id,
									street_id: this.regionOptionDate.street.id,
									goods_id: 0,
									rec_id: this.rec_id,
									page: this.page,
									size: this.size,
									lat: this.getRegionData.postion ? this.getRegionData.postion.lat : '',
									lng: this.getRegionData.postion ? this.getRegionData.postion.lng : '',
								}
							} else {
								o = {
									province_id: this.regionOptionDate.province.id,
									city_id: this.regionOptionDate.city.id,
									district_id: this.regionOptionDate.district.id,
									street_id: this.regionOptionDate.street.id,
									goods_id: this.$route.query.id,
									spec_arr: this.$route.query.attr_id,
									num: this.$route.query.num,
									page: this.page,
									size: this.size,
									lat: this.getRegionData.postion ? this.getRegionData.postion.lat : '',
									lng: this.getRegionData.postion ? this.getRegionData.postion.lng : '',
								}
							}
			  			this.storeList(o)
			  		}
				},200)
	    },
	    //查看地图定位
	    locationMap(item){
	    	let address = item.address + item.stores_address;
	    	address = address.replace(/\s*/g,"");

	    	this.$http.get(`${window.ROOT_URL}/api/misc/address2location`,{ params:{
	    		address:address.replace(/\s*/g,"")
	    	}}).then(({data})=>{
	    		if(data.status == 'success'){
	    			let location = data.data;
	    			let str = location.lat + ',' + location.lng
	    			let url = 'https://mapapi.qq.com/web/mapComponents/locationCluster/v/index.html?type=1&keyword=' + address + '&center=' + str + '&radius=1000&key=' + window.sTenKey + '&referer=myapp';
	    			window.location.href = url;
	    		}else{
	    			Toast(data.message);
	    		}
	    	})
	    }
		},
		watch: {			
			regionShow() {
				let o = {}

				if (!this.regionShow) {
					if (this.rec_id) {
						o = {
							province_id: this.regionOptionDate.province.id,
							city_id: this.regionOptionDate.city.id,
							district_id: this.regionOptionDate.district.id,
							street_id: this.regionOptionDate.street.id,
							goods_id: 0,
							rec_id: this.rec_id,
							page: 1,
							size: 10,
							lat: this.getRegionData.postion ? this.getRegionData.postion.lat : '',
							lng: this.getRegionData.postion ? this.getRegionData.postion.lng : '',
						}
					} else {
						o = {
							province_id: this.regionOptionDate.province.id,
							city_id: this.regionOptionDate.city.id,
							district_id: this.regionOptionDate.district.id,
							street_id: this.regionOptionDate.street.id,
							goods_id: this.$route.query.id,
							spec_arr: this.$route.query.attr_id,
							num: this.$route.query.num,
							page: 1,
							size: 10,
							lat: this.getRegionData.postion ? this.getRegionData.postion.lat : '',
							lng: this.getRegionData.postion ? this.getRegionData.postion.lng : '',
						}
					}

					this.storeList(o)
				}
			},
			storeContent(){
				this.dscLoading = false
				if(this.page * this.size == this.storeContent.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
					this.footerCont = this.page > 1 ? true : false
				}
			},
		}
	}
</script>
<style>
	.van-sku-actions {
		display: flex;
		flex-direction: row;
	}
</style>
