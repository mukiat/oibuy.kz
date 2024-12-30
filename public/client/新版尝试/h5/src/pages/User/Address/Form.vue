<template>
	<div class="con bg-color-write">
		<template v-if="!isImportState">
	  	<div class="flow-consignee">
		    <div class="text-all dis-box">
		    	<label>{{ $t('lang.consignee') }}<em>*</em></label>
		    	<div class="input-text">
		    		<ec-input type="text" v-model="consignee" :placeholder="$t('lang.enter_consignee')"></ec-input>
		    		<i class="iconfont icon-guanbi1 close-common"></i>
		    	</div>
		    </div>
		    <div class="text-all dis-box">
		    	<label>{{ $t('lang.phone_number') }}<em>*</em></label>
		    	<div class="input-text">
		    		<ec-input type="tel" v-model="mobile" :placeholder="$t('lang.enter_contact_number')"></ec-input>
		    		<i class="iconfont icon-guanbi1 close-common"></i>
		    	</div>
		    </div>
		    <div class="text-all dis-box">
		    	<label>{{ $t('lang.zipcode') }}</label>
		    	<div class="input-text">
		    		<ec-input type="tel" v-model="zipcode" :placeholder="$t('lang.enter_contact_zipcode')"></ec-input>
		    		<i class="iconfont icon-guanbi1 close-common"></i>
		    	</div>
		    </div>
		    <div class="text-all dis-box">
		    	<label>{{ $t('lang.region_alt') }}<em>*</em></label>
		    	<div class="input-text">
		    		<div class="address-box" @click="handelRegionShow">
			    		<span class="text-all-span" :class="{'gay': !regionSplic}">{{ regionSplic ? regionSplic : $t('lang.select') }}</span>
			    		<span class="user-more"><i class="iconfont icon-more"></i></span>
			    	</div>
		    	</div>
		    </div>
		    <div class="text-all">
		    	<label>{{ $t('lang.detail_info') }}<em>*</em></label>
		    	<div class="input-text">
		    		<ec-input type="text" v-model="address" :placeholder="$t('lang.enter_address')"></ec-input>
		    		<i class="iconfont icon-guanbi1 close-common"></i>
		    	</div>
		    </div>
		    <div class="ect-button-more">
		    	<a href="javascript:;" class="btn btn-submit" @click="submitBtn">{{ $t('lang.save') }}</a>
		    	<a href="javascript:;" class="btn btn-wximport" v-if="isWeiXin && !wximport" @click="wxAddress">{{ $t('lang.import_wx_address') }}</a>
		    </div>
	  	</div>
  	
		<Region :display="regionShow" :regionOptionDate="regionOptionDate" :isStorage="false" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate" v-if="!dscLoading || !id"></Region>

		<!--初始化loading-->
    	<DscLoading :dscLoading="dscLoading" v-if="id"></DscLoading>

		<!-- 快捷菜单 -->
    	<CommonNav></CommonNav>
    	</template>
    	<template v-else>
    		<van-loading type="spinner" style="position: absolute; left: 48%;"/>
    	</template>
	</div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'
import { Input } from 'element-ui'

import { 
	Popup,
	Picker,
	Toast,
	Loading
} from 'vant'

import CommonNav from '@/components/CommonNav'
import DscLoading from '@/components/DscLoading'

import formProcessing from '@/mixins/form-processing'
export default{
	mixins: [formProcessing],
	data(){
		return{
			consignee:'',
			mobile:'',
			address:'',
			zipcode:'',
			wximport:this.$route.query.wximport ? this.$route.query.wximport : false,
			back:this.$route.query.back,
			dscLoading:true,
			id:this.$route.params.id ? this.$route.params.id : 0,
			isImportState:this.wximport
		}
	},
	components:{
		CommonNav,
		DscLoading,
		'EcInput':Input,
		[Popup.name]:Popup,
		[Picker.name]:Picker,
		[Toast.name]:Toast,
		[Loading.name]:Loading,
	},
	created(){
		let that = this

		if(this.id){
			that.addressInfoInt(this.id)
		}else{
			if(that.wximport){
				that.wxOpenAddress();
			}
		}

		document.title = that.wximport ? this.$t('lang.import_address') : this.$t('lang.add_address');
	},
	mounted(){
	  //   if (window.history && window.history.pushState && this.back) {
			// // 向历史记录中插入了当前页
			// history.pushState(null, null, document.URL);
			// window.addEventListener('popstate', this.goBack, false);
	  //   }
  	},
	destroyed(){
	//	window.removeEventListener('popstate', this.cancel, false);
	},
	computed:{
		...mapState({
			addressInfo : state => state.user.addressInfo
		}),
		isAddress(){
			return this.$route.params.id ? true : false
		},
	},
	methods:{
		goBack(){
			this.$router.replace({ path: this.back })
		},
		addressInfoInt(id){
			this.$store.dispatch('userAddressInfo',{
				address_id:id
			}).then(res=>{
				if(res.data){
					this.consignee = res.data.consignee
					this.mobile = res.data.mobile
					this.address = res.data.address
					this.zipcode = res.zipcode

					this.regionOptionDate.province.id = res.data.province
					this.regionOptionDate.province.name = res.data.province_name
					this.regionOptionDate.city.id = res.data.city
					this.regionOptionDate.city.name = res.data.city_name
					this.regionOptionDate.district.id = res.data.district
					this.regionOptionDate.district.name = res.data.district_name
					this.regionOptionDate.street.id = res.data.street
					this.regionOptionDate.street.name = res.data.street_name
					this.regionOptionDate.regionSplic = res.data.region

					this.dscLoading = false
				}
			})
		},
		handelRegionShow(){
			this.regionShow = this.regionShow ? false : true
		},
		submitBtn(){
			let address_id = 0
			if(this.isAddress){
				address_id = this.$route.params.id
			}

			if(this.consignee == ''){
				Toast(this.$t('lang.consignee_not_null'))
				return false
			} 
			
			if(!this.checkMobile()){
				Toast(this.$t('lang.phone_number_format'))
				return false
			}

			if(this.address == ''){
				Toast(this.$t('lang.address_not_null'))
				return false
			}

			this.$store.dispatch('userAddressAdd',{
				address_id:address_id,
				consignee:this.consignee,
				mobile:this.mobile,
				address:this.address,
				country:1,
				province:this.regionOptionDate.province.id,
				city:this.regionOptionDate.city.id,
				district:this.regionOptionDate.district.id,
				street:this.regionOptionDate.street.id,
				zipcode:this.zipcode
			}).then(res => {
				if(res.status == 'success'){
					Toast.success({
						duration: 1000,
					  	message: this.$t('lang.save_success')
					})

					if(this.$route.query.routerLink){
						if(this.$route.query.entrance && this.$route.query.entrance == 'first'){
							// if(this.$route.query.rec_type){
							// 	if(this.$route.query.rec_type == 'supplier'){
							// 		this.$router.push({
							// 			name:this.$route.query.routerLink,
							// 			query:{
							// 				rec_type:this.$route.query.rec_type,
							// 				rec_id:this.$route.query.rec_id,
	 					// 				}
							// 		})
							// 	}else{
							// 		this.$router.push({
							// 			name:this.$route.query.routerLink,
							// 			query:{
							// 				rec_type:this.$route.query.rec_type,
							// 				type_id:this.$route.query.type_id,
							// 				team_id:this.$route.query.team_id
							// 			}
							// 		})
							// 	}
							// }else{
							// 	if(this.$route.query.routerLink == 'crowdfunding-checkout'){
							// 		this.$router.push({
							// 			name:this.$route.query.routerLink,
							// 			query:{
							// 				pid:this.$route.query.pid,
							// 				id:this.$route.query.id,
							// 				number:this.$route.query.number,
							// 			}
							// 		})
							// 	}else{
							// 		this.$router.push({
							// 			name:this.$route.query.routerLink,
							// 		})
							// 	}
							// }
							this.$router.go(-1);
						}else{
							this.$router.push({
								name: 'address',
								query: this.$route.query
							})
						}
					}else{
						this.$router.push({name: 'address'});
					}
				}else{
					Toast({
						duration: 1000,
					  	message: this.$t('lang.save_fail')
					})
				}
			})
		},
	    checkMobile() {
	        let rule = /^(\d{10})$/
	        if (rule.test(this.mobile)) {
	            return true
	        } else {
	            return false
	        }
	    },
	    shippingFee(val) {
	      this.$store.dispatch('setShippingFee', {
	          goods_id: 0,
	          position: val
	      })
	    },
	    wxAddress(){
			this.wxOpenAddress();
		},
		wxOpenAddress(){
			let that = this
			wx.openAddress({
				success: function (res) {
					that.$store.dispatch('userWxImportAddress',{
						provinceName:res.provinceName,
						cityName:res.cityName,
						countyName:res.countryName,
						detailInfo:res.detailInfo,
						telNumber:res.telNumber,
						userName:res.userName
					}).then(result => {
						if(result.status == 'success'){
							that.consignee = result.data.consignee ? result.data.consignee : ''
							that.mobile = result.data.mobile ? result.data.mobile : ''
							that.address = result.data.address ? result.data.address : ''
							that.regionOptionDate.province.id = result.data.province ? result.data.province : ''
							that.regionOptionDate.city.id = result.data.city ? result.data.city : ''
							that.regionOptionDate.district.id = result.data.district ? result.data.district : ''
							that.regionOptionDate.street.id = result.data.street ? result.data.street : '';
							that.regionOptionDate.regionSplic = result.data.region ? result.data.region : ''

							this.isImportState = false
						}
					})
				},
				cancel:function(res){
					console.log(JSON.stringify(res))
					this.$router.go(-1);
				}
			})
		}
	},
	watch:{
		regionSplic(){
			let shipping_region = {
				province_id: this.regionOptionDate.province.id,
				city_id: this.regionOptionDate.city.id,
				district_id: this.regionOptionDate.district.id,
				street_id: this.regionOptionDate.street.id
			}

			//运费
			this.shippingFee(shipping_region)
		}
	}
}
</script>
<style scoped>
.btn-wximport{ color: #fff;background: #21ba45; margin-top: 20px; }
.gay{ color:#c0c4cc; }
</style>
