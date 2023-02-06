<template>
	<div class="user-recharge">
		<ul class="header">
			<li :class="[currentIndex == index ? 'active_tab' : '']" v-for="(item, index) in tabData" :key="index" @click="changeChannel(index)">{{item}}</li>
		</ul>
		
		<section class="pay_number">
			<template v-if="currentIndex == 0">
				<img class="pay_logo" src="../../../assets/img/zhifubao.png" />
				<input class="pay_field" type="text" v-model="payID" :placeholder="$t('lang.placeholder_alipay')" />
			</template>
			<template v-else-if="currentIndex == 1">
				<img class="pay_logo" src="../../../assets/img/weixin.png" />
				<input class="pay_field" type="text" v-model="payID" :placeholder="$t('lang.placeholder_wxpay')" />
			</template>
			<template v-else>
				<img class="pay_logo" src="../../../assets/img/payment/unionpay.png" />
				<div :class="['pay_field', bank_cur ? '' : 'placeholder_color']" style="line-height: 1.2;">{{ bank_cur ? bank_cur : msg }}</div>
			</template>
		</section>
		
		<section class="get_money">
			<div class="title">{{$t('lang.deposit_money')}}</div>
			<div class="money_wrap">
				<div class="money_field">
					<span class="ico">{{ currency }}</span>
					<input class="ipt" type="text" v-model="amount" />
					<div class="all_money" @click="amount = account">{{$t('lang.all')}}</div>
				</div>
				<p class="max_money color-9">{{$t('lang.withdrawal_balance')}}{{account}}{{$t('lang.yuan')}}</p>
			</div>
			<div class="min_money color-9" v-if="buyer_cash">{{$t('lang.lowest_deposit_money')}}：{{depositMoney}}</div>
		</section>
		
		<p class="submit_tips">{{$t('lang.submit_tips')}}</p>
		
		<section class="unverified" v-if="msg && currentIndex == 2">
			<button class="submit_btn">{{msg}}</button>
			<router-link :to="{name:'realname'}" class="go_auth">{{$t('lang.up_real_name')}}</router-link>
		</section>
		<button class="submit_btn" @click="submitApply" v-else>{{$t('lang.submit_apply')}}</button>
		
		<van-dialog
		  v-model="showDialog"
		  show-cancel-button
		  :before-close="beforeCloseHandle"
		>
		  <div class="dialog_content">{{$t('lang.withdraw_tips')}}<br>{{$t('lang.withdraw_tips_2')}}</div>
		</van-dialog>
		
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import qs from 'qs'
import Vue from 'vue'
import {
	Popup,
	Toast,
	Dialog
} from 'vant'

Vue.use(Dialog);

import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return {
			amount:'',
			account:0,
			bank:[],
			bank_cur:'',
			currency:'¥',
			msg:'',
			buyer_cash:0,
			tabData: [this.$t('lang.alipay'), this.$t('lang.wechat'), this.$t('lang.bank_card')],
			currentIndex: 0,
			payID: '',
			showDialog: false
		}
	},
	computed: {
		depositMoney: function () {
			if (this.buyer_cash > 0) return this.currency + `${this.buyer_cash}`
			else return this.$t('lang.unlimited')
		}
	},
	components:{
		[Popup.name]:Popup,
		[Toast.name]:Toast,
		CommonNav
	},
	created(){
		let configData = JSON.parse(sessionStorage.getItem('configData'));
		if (configData) {
			this.currency = configData.currency_format.replace('%s', '');
		}
		
	    this.raplayInfo(1)
	},
	methods:{
		// 切换提现途径
		changeChannel(i) {
			if (this.currentIndex == i) return;
			if (i == 2) this.raplayInfo();
			this.currentIndex = i;
			this.payID = '';
		},
		// 点击提交申请
		submitApply() {
			if (this.currentIndex == 0 && !this.payID) return Toast(this.$t('lang.placeholder_alipay'));
			if (this.currentIndex == 1 && !this.payID) return Toast(this.$t('lang.placeholder_wxpay'));
			if(Number(this.amount) >= Number(this.buyer_cash)){
				if (this.currentIndex == 2) this.submitHandle()
				else this.showDialog = true;
			}else{
				Toast(this.$t('lang.deposit_money_prompt'))
			}
		},
		// 弹框回调
		beforeCloseHandle(action, done) {
			if (action === 'confirm') {
				this.submitHandle(done)
			} else {
				done();
			}
		},
		// 获取提现金额
		raplayInfo(type = 0){
			this.$http.get(`${window.ROOT_URL}api/account/reply?withdraw_type=${type}`).then(({data:{data}}) =>{
		     	if(data.code == 0){
		     		if (type == 0) {
						this.bank = data.bank
						this.bank_cur = this.bank[0].bank_card_org
					}
		     		this.account = data.surplus_amount
					this.buyer_cash = data.buyer_cash
		     	}else{
		     		this.msg = data.msg
		     	}
		    })
		},
		// 提交申请 api
		submitHandle(done = null){
			let o = {
				amount:this.amount,
				surplus_type: 1
			}
			if (this.currentIndex == 0) {
				o.withdraw_type = 2
				o.withdraw_user_number = this.payID
			} else if (this.currentIndex == 1) {
				o.withdraw_type = 1
				o.withdraw_user_number = this.payID
			} else {
				o.withdraw_type = 0
			}
			this.$http.post(`${window.ROOT_URL}api/account/account`,qs.stringify(o)).then(res => {
				done && done()
				Toast(res.data.data.msg)
				setTimeout(()=>{
					// this.$router.push({
			  //   		name:'account'
			  //   	})
					this.$router.go(-1)
				},2000)
			})
		}
	}
}
</script>

<style lang="scss" scoped>
.user-recharge {
	.header {
		display: flex;
		justify-content: space-around;
		align-items: center;
		height: 6rem;
		background-color: #fff;
		li {
			font-size: 1.6rem;
		}
		.active_tab {
			position: relative;
			color: red;
			&::after {
				content: '';
				position: absolute;
				left: 50%;
				bottom: -0.8rem;
				transform: translateX(-50%);
				width: 100%;
				height: 0.3rem;
				background-color: red;
			}
		}
	}
	.pay_number {
		display: flex;
		align-items: center;
		height: 5rem;
		margin: 0.5rem;
		padding: 0 1rem;
		background-color: #fff;
		.pay_logo {
			width: 3rem;
			height: 3rem;
		}
		.pay_field {
			flex: auto;
			margin-left: 2rem;
		}
		.placeholder_color {
			color: #777;
		}
	}
	.get_money {
		margin: 0 0.5rem;
		padding: 1rem;
		background-color: #fff;
		.money_wrap {
			display: flex;
			flex-direction: column;
			justify-content: flex-end;
			height: 8rem;
		}
		.money_field {
			display: flex;
			align-items: center;
			font-size: 1.8rem;
			.ipt {
				flex: auto;
				margin: 0 0.5rem;
			}
		}
		.all_money {
			font-size: 1.4rem;
			color: #62B3FF;
		}
		.max_money {
			padding: 0.4rem 0 1rem;
			font-size: 1.2rem;
			text-align: right;
		}
		.min_money {
			position: relative;
			font-size: 1.2rem;
			padding-top: 1rem;
			&::after {
				/* #ifndef APP-NVUE */
				content: ' ';
				/* #endif */
				position: absolute;
				left: 0;
				top: 0;
				pointer-events: none;
				box-sizing: border-box;
				-webkit-transform-origin: 0 0;
				transform-origin: 0 0;
				// 多加0.1%，能解决有时候边框缺失的问题
				width: 199.8%;
				height: 199.7%;
				transform: scale(0.5, 0.5);
				border: 0 solid #EDEDED;
				border-top-width: 1px;
				z-index: 2;
			}
		}
	}
	.submit_tips {
		height: 3.6rem;
		line-height: 3.6rem;
		margin: 0 0.5rem;
		font-size: 1.2rem;
		color: red;
	}
	.submit_btn {
		width: 96%;
		height: 4.4rem;
		line-height: 4.4rem;
		border-radius: 2.2rem;
		margin: 8rem 2% 2rem;
		font-size: 1.6rem;
		color: #fff;
		background-color: red;
	}
	.unverified {
		display: flex;
		flex-direction: column;
		align-items: center;
		.submit_btn {
			background-color: #999;
		}
		.go_auth {
			font-size: 1.2rem;
			color: #62B3FF;
		}
	}
	.dialog_content {
		font-size: 1.4rem;
		padding: 2.5rem;
		text-align: left;
	}
}	
</style>
