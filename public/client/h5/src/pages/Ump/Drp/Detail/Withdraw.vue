<template>
	<div class="drp-withdraw balance_transfer_out">
		<template v-if="drpTransData.withdraw_switch == 0">
			<div class="padding-all bg-color-write ">
				<div class="f-04 color-9">转出到余额</div>
				<van-field class="f-04" v-model="amount" clearable :label="currency" :placeholder="$t('lang.enter_shift_amount')" />
				<p class="title f-03 color-9">{{$t('lang.deposit_brokerage')}}{{drpTransData.max_money}},{{$t('lang.min_transfer_amount')}}{{drpTransData.min_money}}{{$t('lang.yuan')}}</p>
			</div>
			<div class="withdraw-btn">
				<van-button class="br-5 f-06 m-top10" :disabled="!btnDisabled" @click="withdrawMoney" type="primary" bottom-action>{{ $t('lang.confirm_transfer') }}</van-button>
			</div>
		</template>
		<template v-else> 
			<van-tabs v-model="active">
				<van-tab title="转出到余额">
					<div class="padding-all bg-color-write ">
						<!-- <div class="f-04 color-9">{{$t('lang.contribution_roll_out')}}</div> -->
						<van-field class="f-04" v-model="amount" clearable :label="currency" :placeholder="$t('lang.enter_shift_amount')" />
						<p class="title f-03 color-9">{{$t('lang.deposit_brokerage')}}{{drpTransData.max_money}},{{$t('lang.min_transfer_amount')}}{{drpTransData.min_money}}{{$t('lang.yuan')}}</p>
					</div>
					<div class="withdraw-btn">
						<van-button class="br-5 f-06 m-top10" :disabled="!btnDisabled" @click="withdrawMoney" type="primary" bottom-action>{{ $t('lang.confirm_transfer') }}</van-button>
					</div>
				</van-tab>
				<van-tab title="转出到微信" v-if="drpTransData.openid">
					<div class="padding-all bg-color-write ">
						<van-field class="f-04" v-model="weiXinMinNum" clearable :label="currency" :placeholder="$t('lang.enter_shift_amount')" />
						<p class="title f-03 color-9">{{$t('lang.deposit_brokerage')}}{{drpTransData.max_money}},{{$t('lang.min_transfer_amount')}}{{ drpTransData.min_money > 1 ? drpTransData.min_money : 1 }}{{$t('lang.yuan')}}</p>
					</div>
					<div class="withdraw-btn">
						<van-button class="br-5 f-06 m-top10" :disabled="!btnDisabled" @click="withdrawMoneyToWeiXin" type="primary" bottom-action>申请</van-button>
					</div>
				</van-tab>
				<van-tab title="转出到银行卡" v-if="drpTransData.is_show_wxpay_bank > 0">
					<p class="tip-fee">每笔按付款金额收取手续费，按金额0.1%收取，最低1元，最高25元</p>
					<van-cell-group>
						<van-field v-model="payeeName" label="姓名" placeholder="银行卡开户姓名" />
					</van-cell-group>
					<van-cell-group>
						<van-cell is-link @click="showPopup">
							<template #title>
								<span class="custom-title">银行</span>
								<span class="custom-value" v-text="bankName"></span>
							</template>
						</van-cell>
					</van-cell-group>
					<van-cell-group>
						<van-field v-model="payeeNunber" label="卡号" placeholder="收款人银行卡号" />
					</van-cell-group>
					<van-cell-group></van-cell-group>
					<div class="padding-all bg-color-write border_top">
						<van-field class="f-04" v-model="bankMinNum" clearable :label="currency" :placeholder="$t('lang.enter_shift_amount')" />
						<p class="title f-03 color-9">{{$t('lang.deposit_brokerage')}}{{drpTransData.max_money}},{{$t('lang.min_transfer_amount')}}{{ drpTransData.min_money > 1 ? drpTransData.min_money : 1 }}{{$t('lang.yuan')}}</p>
					</div>
					<div class="withdraw-btn">
						<van-button class="br-5 f-06 m-top10" :disabled="!btnDisabled" @click="withdrawMoneyToBank" type="primary" bottom-action>{{ $t('lang.confirm_transfer') }}</van-button>
					</div>
				</van-tab>
			</van-tabs>
		</template>
		
		<CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
		<van-popup v-model="isShowPopup" position="bottom" :style="{ height: '30%' }">
			<van-picker
			title="请选择银行"
			show-toolbar
			value-key="bank_name"
			:columns="drpTransData.bank_list"
			@confirm="onConfirm"
			@cancel="onCancel"
			/>
		</van-popup>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import CommonNav from '@/components/CommonNav'
	import {
		Cell,
		CellGroup,
		Field,
		Button,
		Toast,
		Dialog,
		Tab,
		Tabs,
		Popup,
		Picker
	} from 'vant'
	export default {
		name: "drp-withdraw",
		components: {
			CommonNav,
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
			[Field.name]: Field,
			[Button.name]: Button,
			[Toast.name]: Toast,
			[Dialog.name]: Dialog,
			[Tab.name]: Tab,
			[Tabs.name]: Tabs,
			[Popup.name]: Popup,
			[Picker.name]: Picker
		},
		data() {
			return {
				active: 0,
				amount: '',
				payeeName: '',
				payeeNunber: '',
				routerName:'drp',
				currency:'￥',
				btnDisabled:true,
				isShowPopup: false,
				bankCode: '',
				bankName: '请选择银行',
				bankMinNum: null,
				weiXinMinNum: null
			};
		},
		//初始化加载数据
		created() {
			Toast.loading({
				duration: 500,
				mask: true,
				message: this.$t('lang.loading')
			}, this.$store.dispatch('setDrpTrans'));
			
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if(configData){
			  this.currency = configData.currency_format.replace('%s', '');
			}
		},
		computed: {
			...mapState({
				drpTransData: state => state.drp.drpTransData,
			})
		},
		methods: {
			onConfirm(value, index) {
				this.bankCode = value.bank_code;
				this.bankName = value.bank_name;
				this.isShowPopup = false
			},
			onCancel() {
				this.isShowPopup = false
			},
			showPopup() {
				this.isShowPopup = true
			},
			withdrawMoneyToBank() {
				let minMoney = Number(this.$store.state.drp.drpTransData.min_money)
				let maxMoney = Number(this.$store.state.drp.drpTransData.max_money)
				let num = minMoney < 1 ? 1 : minMoney;
				if (!this.payeeName.trim()) return Toast('银行卡开户姓名不能为空')
				if (!this.bankCode.trim()) return Toast('请选择银行')
				if (!this.payeeNunber.trim()) return Toast('收款人银行卡号不能为空')
				if (this.bankMinNum < num) {
					return Toast(this.$t('lang.amount_cannot_less') + num + this.$t('lang.yuan'))
				} else if (this.bankMinNum > maxMoney) {
					return Toast(this.$t('lang.amount_exceeds_max_limit'))
				}
				let queryData = {
					deposit_type: 2,
					amount: this.bankMinNum,
					enc_bank_no: this.payeeNunber,
					enc_true_name: this.payeeName,
					bank_code: this.bankCode
				};
				
				this.submitHandle(queryData)

			},
			withdrawMoneyToWeiXin() {
				let minMoney = Number(this.$store.state.drp.drpTransData.min_money)
				let maxMoney = Number(this.$store.state.drp.drpTransData.max_money)
				let num = minMoney < 1 ? 1 : minMoney;
				if (this.weiXinMinNum < num) {
					return Toast(this.$t('lang.amount_cannot_less') + num + this.$t('lang.yuan'))
				} else if (this.weiXinMinNum > maxMoney) {
					return Toast(this.$t('lang.amount_exceeds_max_limit'))
				}
				
				let queryData = {
					deposit_type: 1,
					amount: this.weiXinMinNum
				};
				this.submitHandle(queryData)
			},
			withdrawMoney() {
				
				let that = this
				let minMoney = Number(this.$store.state.drp.drpTransData.min_money)
				let maxMoney = Number(this.$store.state.drp.drpTransData.max_money)
				let inputValue = Number(this.amount)
				
				if (inputValue < minMoney) {
					Toast(this.$t('lang.amount_cannot_less') + minMoney + this.$t('lang.yuan'))
				} else if (inputValue > maxMoney) {
					Toast(this.$t('lang.amount_exceeds_max_limit'))
				} else {
					
					let queryData = {
						amount: inputValue,
						deposit_type: 0
					};
				this.submitHandle(queryData)
					
				}
					
			},
			submitHandle(queryInfo) {
				this.btnDisabled = false;
				this.$store.dispatch('setDrpTransferred', queryInfo).then(res=>{
						
					this.btnDisabled = true
					
					if(res.status == 'success'){
		
						if (res.data.error == 0) {
							Toast.success(res.data.msg);
							
							setTimeout(()=>{
								this.$router.push({
									name:'drp-withdraw-log'
								})
							},2000)
						} else {
							Toast(res.data.msg);
						}
					}else{
						Toast(this.$t('lang.deposit_fail'));
					}
				})
			}
		}
	};
</script>

<style scoped>
.custom-title {
	margin-right: 1.2rem;
}
.custom-title,
.custom-value {
	font-size: 1.4rem;
}
.tip-fee {
    background: #1989fa;
    color: #fff;
    padding: 10px 15px;
}
</style>>
