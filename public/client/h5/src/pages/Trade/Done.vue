<template>
	<div class="cashier-desk">
		<template v-if="payState == 1 || (payState == 3 && presale_final_pay == 1)">
	  	<van-cell-group class="van-cell-noright">
	  		<van-cell class="van-cell-title">
	  			{{$t('lang.label_need_pay')}}
	  			<label class="color-red" v-html="amountPrice"></label>
	  		</van-cell>
	  	</van-cell-group>
		<van-cell-group class="van-cell-noright m-top08">
		  <van-cell :title="$t('lang.online_payment')" class="van-cell-title b-min b-min-b" />
		  <van-radio-group v-model="pay_id" @change='paymentChange' :disabled="disabled">
			  <van-cell v-for="(item,index) in pay_list" :key="index">
			  	<div class="dopay-list">
			  		<div class="left-icon">
			  			<img src="../../assets/img/payment/alipay.png" v-if="item.pay_code == 'alipay'" />
			  			<img src="../../assets/img/payment/wxpay.png" v-else-if="item.pay_code == 'wxpay'" />
			  			<img src="../../assets/img/payment/paypal.png" v-else-if="item.pay_code == 'paypal'" />
			  			<img src="../../assets/img/no_image.jpg" v-else />
			  		</div>
			  		<div class="center">
			  			<h4 class="text-left">{{item.pay_name}}</h4>
			  			<div class="zhifu" v-if="item.format_pay_fee">({{$t('lang.service_charge')}}<i>{{item.format_pay_fee}}</i>)</div>
			  		</div>
			  		<van-radio :name="item.pay_id" @click="tan(item.format_pay_fee)"></van-radio>
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
		<template v-else-if="payState == 2 || (payState == 3 && presale_final_pay == 0)">
			<div class="flow-done">
				<template v-if="doneinfo.pay_code == 'cod'">
					<div class="flow-done-con">
						<i class="iconfont icon-qian"></i>
						<p class="flow-done-title">{{$t('lang.amount_payable')}}</p>
						<p class="flow-done-price" v-html="doneinfo.order_amount_format"></p>
					</div>
				</template>
				<template v-else>
					<div class="flow-done-con" v-if="doneinfo.pay_code == 'bank'">
						<i class="iconfont icon-hookring2"></i>
						<p class="flow-done-title">
							订单提交成功
						</p>
					</div>
					<div class="flow-done-con" v-else>
						<i class="iconfont icon-hookring2"></i>
						<p class="flow-done-title">
							{{$t('lang.order_pay_success')}}
							<em class="color-red" v-if="payState == 3">({{$t('lang.partial_payment')}})</em>
						</p>
					</div>
				</template>
				<div class="flow-done-all" v-if="doneinfo.pay_code == 'bank'">
					<div class="padding-all bg-color-write flow-done-id">
						<section class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.label_order')}}</label>
							<span class="box-flex t-goods1 text-right">{{ order_sn }}</span>
						</section>
						<section class="dis-box dis-b">
							<label class="t-remark g-t-temark">应付金额</label>
							<span class="box-flex t-goods1 text-right red">{{ doneinfo.order_amount_format }}</span>
						</section>
						<section class="dis-box dis-b">
							<label class="t-remark g-t-temark">支付方式</label>
							<span class="box-flex t-goods1 text-right red">{{ doneinfo.pay_name }}</span>
						</section>
						<section class="dis-box dis-b" v-for="(item,index) in doneinfo.pay_config" :key="index">
							<label class="t-remark g-t-temark">{{item.name}}</label>
							<span class="box-flex t-goods1 text-right">{{item.value}}</span>
						</section>
					</div>
				</div>
				<div class="flow-done-all" v-else>
					<div class="padding-all bg-color-write flow-done-id">
						<section class="dis-box">
							<label class="t-remark g-t-temark">{{$t('lang.label_order')}}</label>
							<span class="box-flex t-goods1 text-right">{{ order_sn }}</span>
						</section>
					</div>
				</div>
			</div>
			<div class="flow-done-other dis-box">
				<template v-if="doneinfo.extension_code == 'team_buy'">
					<a :href="doneinfo.url" class="btn btn-w-submit m-top10">{{$t('lang.view_team_schedule')}}</a>
				</template>
				<template v-else-if="doneinfo.extension_code == 'crowd_buy'">
					<router-link :to="{name:'crowdfunding-order'}" class="btn btn-w-submit m-top10">{{$t('lang.view_crowdfunding_order')}}</router-link>
				</template>
				<template v-else>
					<router-link :to="{name:'order'}" class="btn btn-w-submit m-top10">{{$t('lang.view_order')}}</router-link>
				</template>
			</div>
		</template>
		<template v-else>
			<van-loading type="spinner" />
		</template>
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
	Dialog,
	Loading
} from 'vant'

export default{
	data(){
		return{
			btn:'',
			order_sn:this.$route.query.order_sn,
			presale_final_pay:this.$route.query.presale_final_pay ? this.$route.query.presale_final_pay : 0,
			payState:'',
			callpayState:'',
			callpayStateData:Object,
			disabled:false,
			pay_id:0
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
		this.onload()
	},
	computed:{
		...mapState({
			doneinfo: state => state.shopping.doneinfo,
			pay_list: state => state.shopping.pay_list
    }),
    amountPrice(){
    	let price = 0
    	if(this.doneinfo.order_amount){
    		price = this.doneinfo.order_amount_format ? this.doneinfo.order_amount_format : 0
    	}

    	return price
    }
	},
	methods:{
		tan(pay_fee){
			if(pay_fee){
				Dialog.alert({
					message: this.$t('lang.commission_reminder') + pay_fee + this.$t('lang.commission_reminderon'),
				})
			}
		},
		onload(){
			if(this.$route.query.pay_code == 'balance'){
				this.$store.dispatch('setDoneInfoBalance',{
					order_sn:this.order_sn
				})
			}else{
				this.$store.dispatch('setDoneInfo',{
					order_sn:this.order_sn
				})
			}
		},
		paylist(){
			let o = {
			    order_id:this.doneinfo.order_id,
				support_cod:this.doneinfo.support_cod,
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

			that.$store.dispatch('setPayTab',o).then((res)=>{
				if (res.status == 'success') {
					if (res.data != false) {
						let button = res.data.button;

                        this.doneinfo.order_amount = res.data.order_amount
                        this.doneinfo.order_amount_format = res.data.order_amount_format

						if(button.paycode == 'wxpay'){
							if(button.type == 'wxh5'){
								that.btn = '<a class="btn btn-submit" href="'+ button.mweb_url +'">'+that.$t('lang.wxcallpay')+'</a>'
							}else{
								that.callpayState = 'wxpay'
								that.btn = ''
								that.callpayStateData = button
							}
						}else{
							that.callpayState = '';
							that.btn = button ? button : that.btn
						}
					}else{
						that.callpayState = '';
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
			this.payState = 0

			if(this.doneinfo.pay_code == 'cod' || this.doneinfo.pay_code == 'balance' || this.doneinfo.order_amount == 0 ||  this.doneinfo.pay_code == 'bank'){
				this.payState = 2
			}else if(this.doneinfo.pay_status == 3){
				if(this.presale_final_pay == 1){
					this.paylist()
				}
				this.payState = 3
			}else{ //在线支付
				this.paylist()
				this.payState = 1
			}
		},
		pay_list(){
			let arr = this.pay_list.filter((item,index)=>{
				return item.selected ? item.pay_id : index == 0
			})
			
			this.pay_id = arr[0].pay_id
		}
	}
}
</script>
<style lang="scss" scoped>
.dopay-list{
	display: flex;
	flex-direction: row;
	justify-content: flex-start;
	align-items: center;
	line-height: 2rem;

	.left-icon{
		margin-right: 1rem;		
		img{
			width: 2rem;
			height: 2rem;
		}
	}

	.center{
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		flex: 1;

		h4{
			line-height: 2rem;
			font-size: 1.5rem;
			margin-right: .8rem;
		}

		.zhifu{
			color: #999;
			font-size: 1.2rem;
			i{
				color: #f92028;
			}
		}
	}
}
.red{
	color: red;
}
.dis-b{
	padding-top: 0.8rem;
}
</style>
