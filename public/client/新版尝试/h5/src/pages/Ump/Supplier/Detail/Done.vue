<template>
	<div class="cashier-desk">
		<template v-if="doneinfo.pay_code == 'onlinepay'">
			<van-cell-group class="van-cell-noright">
	  		<van-cell class="van-cell-title">
	  			{{$t('lang.label_need_pay')}}<em class="color-red">{{ doneinfo.order_amount_format }}</em>
	  		</van-cell>
	  	</van-cell-group>
	  	<van-cell-group class="van-cell-noright m-top08">
				<van-cell :title="$t('lang.online_payment')" class="van-cell-title b-min b-min-b" />
			  	<van-radio-group v-model="pay_id" @change='paymentChange' :disabled="disabled">
					<van-cell v-for="(item,index) in pay_list" :key="index">
					  	<div class="dopay-list dis-box">
					  		<div class="left-icon">
					  			<img src="../../../../assets/img/payment/alipay.png" v-if="item.pay_code == 'alipay'" />
					  			<img src="../../../../assets/img/payment/wxpay.png" v-else-if="item.pay_code == 'wxpay'" />
					  			<img src="../../../../assets/img/payment/paypal.png" v-else-if="item.pay_code == 'paypal'" />
					  			<img src="../../../../assets/img/no_image.jpg" v-else />
					  		</div>
					  		<div class="box-flex"><h4 class="text-left">{{item.pay_name}}</h4></div>
					  		<van-radio :name="item.pay_id"></van-radio>
					  	</div>
					</van-cell>
				</van-radio-group>
			</van-cell-group>
			<div class="filter-btn">
				<template v-if="btn == ''">
					<template v-if="callpayState == 'wxpay'">
						<a class="btn btn-submit" href="javascript:;" @click="wxcallpay">{{$t('lang.wxcallpay')}}</a>	
					</template>
					<template v-else>
						<a class="btn btn-disabled">{{$t('lang.fill_in_payment')}}</a>
					</template>
				</template>
				<template v-else>
					<div v-html="btn"></div>
				</template>
			</div>
		</template>
		<template v-else>
			<div class="flow-done">
				<div class="flow-done-con">
					<i class="iconfont icon-hookring2"></i>
					<p class="flow-done-title">{{$t('lang.order_pay_success')}}</p>
				</div>
				<div class="flow-done-all">
					<div class="padding-all bg-color-write flow-done-id">
						<section class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.label_order')}}</label>
							<span class="box-flex t-goods1 text-right">{{ order_sn }}</span>
						</section>
					</div>
				</div>
			</div>
			<div class="flow-done-other dis-box">
				<router-link :to="{name:'supplier-orderlist'}" class="btn btn-w-submit m-top10">{{$t('lang.view_order')}}</router-link>
			</div>
		</template>

		<!--初始化loading-->
    <DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>
<script>
	import { mapState } from 'vuex'

	import {
		Cell,
		CellGroup,
		Radio,
		RadioGroup,
		Toast,
		Loading
	} from 'vant'

	import DscLoading from '@/components/DscLoading'

	export default{
		data(){
			return{
				btn:'',
				order_sn:this.$route.query.order_sn,
				presale_final_pay:this.$route.query.presale_final_pay ? this.$route.query.presale_final_pay : 0,
				callpayState:'',
				callpayStateData:Object,
				disabled:false,
				dscLoading:true,
			}
		},
		components:{
			[CellGroup.name]:CellGroup,
			[Cell.name]:Cell,
			[Radio.name]:Radio,
			[RadioGroup.name]:RadioGroup,
			[Toast.name]:Toast,
			[Loading.name]:Loading,
			DscLoading
		},
		created(){
			this.onload()
		},
		computed:{
			...mapState({
					doneinfo: state => state.other.supplierDoneInfo,
					pay_list: state => state.shopping.pay_list
		    }),
		    pay_id:{
		    	get(){
		    		return this.$store.state.shopping.pay_id
		    	},
		    	set(val){
		    		this.$store.state.shopping.pay_id = val
		    	}
		    }
		},
		methods:{
			onload(){
				if(this.$route.query.pay_code == 'balance'){
					this.$store.dispatch('setSupplierDoneInfoBalance',{
						order_sn:this.order_sn
					})
				}else{
					this.$store.dispatch('setSupplierDoneInfo',{
						order_sn:this.order_sn
					})
				}
			},
			paylist(){
				let o = {
				    order_id:this.doneinfo.order_id,
					support_cod:0,
					pay_code:this.doneinfo.pay_code,
					is_online:this.doneinfo.is_online,
					cod_fee:this.doneinfo.cod_fee
				}
				this.$store.dispatch('setPayList', o)
			},
			paymentChange(){
				let that = this
				let o = {
					order_id:this.doneinfo.order_id,
					pay_id:this.pay_id
				}

				that.btn = '<a class="btn btn-disabled">'+that.$t('lang.loading')+'</a>'
				that.disabled = true
				that.$store.dispatch('setSupplierPayTab',o).then((res)=>{
					if (res.status == 'success') {
						if (res.data != false) {
							if(res.data.paycode == 'wxpay'){
								if(res.data.type == 'wxh5'){
									that.btn = '<a class="btn btn-submit" href="'+ res.data.mweb_url +'">'+that.$t('lang.wxcallpay')+'</a>'
								}else{
									that.callpayState = 'wxpay'
									that.btn = ''
									that.callpayStateData = res.data
								}
							}else{
								that.btn = res.data
							}
						}else{
							that.btn = '<a class="btn btn-disabled">'+that.$t('lang.pament_not_config')+'</a>'
						}
					} else {
						Toast(that.$t('lang.pament_select_fail'))
					}
					that.disabled = false
				})
			},
			jsApiCall(ret) {
	            let that = this
	            let payment = JSON.parse(ret.payment)
	            let success_url = ret.success_url
	            let cancel_url = ret.cancel_url

	            WeixinJSBridge.invoke("getBrandWCPayRequest", payment, function (res) {
	                if (res.err_msg == "get_brand_wcpay_request:ok") {
	                    window.location.href = success_url
	                } else if(res.err_msg == "get_brand_wcpay_request:fail") {
	                    Toast(this.$t('lang.payment_fail'))
	                }
	            })
	        },
	        callpay(ret) {
	            if (typeof WeixinJSBridge == "undefined") {
	                if (document.addEventListener) {
	                    document.addEventListener("WeixinJSBridgeReady", this.jsApiCall(ret), false);
	                } else if (document.attachEvent) {
	                    document.attachEvent("WeixinJSBridgeReady", this.jsApiCall(ret));
	                    document.attachEvent("onWeixinJSBridgeReady", this.jsApiCall(ret));
	                }
	            } else {
	                this.jsApiCall(ret);
	            }
	        },
	        wxcallpay(){
	        	this.callpay(this.callpayStateData)
	        }
		},
		watch:{
			doneinfo(){
				this.dscLoading = false
				if(this.doneinfo.pay_code != 'balance'){
					this.paylist()
				}
			}
		}
	}
</script>
<style lang="scss" scoped>
.dopay-list{
	line-height: 2rem;
	.left-icon{
		margin-right: 1rem;
		img{
			width: 2rem;
			height: 2rem;
		}
	}

	h4{
		line-height: 2rem;
		font-size: 1.5rem;
	}
}
</style>