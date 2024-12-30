<template>
	<div class="user-recharge">
		<van-field v-model="amount" type="digit" :label="$t('lang.recharge_money')" :placeholder="$t('lang.enter_recharge_money')" />
		<div class="field-tips">{{$t('lang.lowest_recharge_money')}}：<em class="color-red" v-if="buyer_recharge > 0">{{currency}}{{ buyer_recharge }}</em><em class="color-red" v-else-if="buyer_recharge == 0">{{$t('lang.unlimited')}}</em></div>
		<van-field v-model="user_note" :label="$t('lang.remarks')" type="textarea" :placeholder="$t('lang.select_fill')" />
		<van-cell-group class="m-top10">
		  <van-cell :title="$t('lang.recharge_type')" :value="pay_name" is-link @click="modeHandle" />
		</van-cell-group>
		<div class="padding-all">
			<template v-if="submit_btn">
				<div v-html="submit_btn"></div>
			</template>
			<template v-else>
				<button class="btn btn-submit border-radius-top05" @click="submitBtn">{{$t('lang.submit_apply')}}</button>
			</template>
		</div>

		<van-popup class="show-popup-bottom" v-model="show" position="bottom">
			<div class="goods-show-title padding-all">
				<h3 class="fl">{{$t('lang.recharge_type')}}</h3>
				<i class="iconfont icon-close fr" @click="close"></i>
			</div>
			<div class="s-g-list-con">
				<div class="select-two">
					<ul>
						<li class="ect-select" v-for="(item,index) in payment_method" :key="index" :class="{'active':pay_id == item.pay_id}" @click="payment_method_select(item.pay_id,item.pay_name)">
							<label class="dis-box">
								<span class="box-flex">{{ item.pay_name }}</span>
								<i class="iconfont icon-gou"></i>
							</label>
						</li>
					</ul>
				</div>
			</div>
		</van-popup>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import qs from 'qs'

import {
	Field,
	Cell,
	CellGroup,
	Popup,
	Toast
} from 'vant'

import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return {
			currency:'₸',
			show:false,
			amount:'',
			user_note:'',
			payment_method:[],
			pay_name:'',
			pay_id:0,
			buyer_recharge:0,
			submit_btn:''
		}
	},
	components:{
		[Field.name]:Field,
		[Cell.name]:Cell,
		[CellGroup.name]:CellGroup,
		[Popup.name]:Popup,
		[Toast.name]:Toast,
		CommonNav
	},
	created(){
		let configData = JSON.parse(sessionStorage.getItem('configData'));
		if(configData){
		  this.currency = configData.currency_format.replace('%s', '');
		}
		
		this.depositInfo()

		this.shopConfig()
	},
	methods:{
		depositInfo(){
			this.$http.get(`${window.ROOT_URL}api/account/deposit`).then(res =>{
				if(res.data.status == 'success'){
			 		this.payment_method = res.data.data.payment
			 	}
			})
		},
		modeHandle(){
			this.show = true
		},
		payment_method_select(id,name){
			this.pay_id = id
			this.pay_name = name
			this.show = false
		},
		close(){
			this.show = false
		},
		shopConfig(){
			this.$http.get(`${window.ROOT_URL}api/shop/config`).then(({data:{data}})=>{
				this.buyer_recharge = data.buyer_recharge
			})
		},
		submitBtn(){
			let o = {
				amount:this.amount,
				user_note:this.user_note,
				payment_id:this.pay_id,
				surplus_type:0,
			}

			if(this.amount > 1000000){
				Toast('Максимум толтыру шегі 1 млн теңге')
				return false;
			}

			if(Number(this.amount) >= Number(this.buyer_recharge)){
				this.$http.post(`${window.ROOT_URL}api/account/account`,qs.stringify(o)).then(({data:{data}}) => {
					if(data.code == 0){
						if(data.pay_button.paycode == 'wxpay'){
							if(data.pay_button.type == 'wxh5'){
								window.location.href = data.pay_button.mweb_url
							}else{
								this.callpay(data.pay_button)
							}
						}else{
							this.submit_btn = data.pay_button
						}
					}else{
			    		Toast(data.msg)
			    	}
		    	})
			}else{
		    	Toast(this.$t('lang.recharge_money_prompt'))
		    }
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
	}
}
</script>
