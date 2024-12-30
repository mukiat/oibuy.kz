<template>
  <div class="cashier-desk">
	  	<van-cell-group class="van-cell-noright">
	  		<van-cell class="van-cell-title">
	  			{{$t('lang.label_need_pay')}}<em class="color-red">{{ doneinfo.order_amount_formated }}</em>
	  		</van-cell>
	  	</van-cell-group>
		<van-cell-group class="van-cell-noright m-top08">
		  <van-cell :title="$t('lang.online_payment')" class="van-cell-title b-min b-min-b" />
		  <van-radio-group v-model="pay_id" @change='paymentChange'>
			  <van-cell v-for="(item,index) in doneinfo.paymentList" :key="index">
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
		<!-- <template v-else>
			<van-loading type="spinner" />
		</template> -->
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

export default{
	data(){
		return{
			btn:'',
			payState:'',
			callpayState:'',
			callpayStateData:Object,
			doneinfo:Object,
			pay_id:'',
			apply_status:this.$route.query.apply_status ? this.$route.query.apply_status : null,
            membership_card_id:this.$route.query.membership_card_id ? this.$route.query.membership_card_id : 0,
		}
	},
	components:{
		[CellGroup.name]:CellGroup,
		[Cell.name]:Cell,
		[Radio.name]:Radio,
		[RadioGroup.name]:RadioGroup,
		[Toast.name]:Toast,
		[Loading.name]:Loading
	},
	created(){
		this.doneInfo()
	},
	methods:{
		doneInfo(){
			this.$store.dispatch('setDrpPay',{
                membership_card_id:this.membership_card_id
            }).then(res=>{
				this.doneinfo = res.data
			})
		},
		paymentChange(){
			this.$store.dispatch('setDrpChangePayment',{
				pay_id:this.pay_id,
				apply_status:this.apply_status,
                membership_card_id:this.membership_card_id
			}).then((res)=>{
				if (res.status == 'success') {
					if (res.data != false) {
						if(res.data.paycode == 'wxpay'){
							if(res.data.type == 'wxh5'){
								this.btn = '<a class="btn btn-submit" href="'+ res.data.mweb_url +'">'+this.$t('lang.wxcallpay')+'</a>'
							}else{
								this.callpayState = 'wxpay'
								this.btn = ''
								this.callpayStateData = res.data
							}
						}else{
							this.btn = res.data
						}
					}else{
						this.btn = '<a class="btn btn-disabled">'+this.$t('lang.pament_not_config')+'</a>'
					}
				} else {
					Toast(this.$t('lang.pament_select_fail'))
				}
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
                } else {
                    Toast(this.$t('lang.payment_interface_try'))
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
