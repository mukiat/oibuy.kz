<template>
	<view class="container">
		<template v-if="drpTransData.withdraw_switch == 0">
			<view class="drp-withdraw">
				<view class="uni-form-item uni-column">
					<view class="uni-input flex_box ai_center">
						<text class="dsc_lable">{{ currency_format }}</text>
						<input class="dsc_input" type="digit" v-model="amount" :placeholder="$t('lang.enter_shift_amount')" />
					</view>
					<view class="sub">{{$t('lang.max_transfer_amount')}}<block v-if="drpTransData.max_money">{{drpTransData.max_money}}</block>,{{$t('lang.min_transfer_amount')}}<block v-if="drpTransData.min_money">{{drpTransData.min_money}}</block>{{$t('lang.yuan')}}</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<button class="tixian_btn" type="warn" :loading="isLoading" :disabled="isLoading" @click="formSubmit">{{$t('lang.confirm')}}</button>
				</view>
			</view>
		</template>
		<template v-else> 
			<dsc-nav-bar :nav-index="navIndex" :list="list" @change-index="changeIndex"></dsc-nav-bar>
			<view class="main">
				<swiper class="dsc_swiper" :duration="300" :current="navIndex" :disable-touch="true" @change="change" >
					<swiper-item>
						<view class="drp-withdraw">
							<view class="uni-form-item uni-column">
								<view class="uni-input flex_box ai_center">
									<text class="dsc_lable">{{ currency_format }}</text>
									<input class="dsc_input" type="digit" v-model="amount" :placeholder="$t('lang.enter_shift_amount')" />
								</view>
								<view class="sub">{{$t('lang.max_transfer_amount')}}<block v-if="drpTransData.max_money">{{drpTransData.max_money}}</block>,{{$t('lang.min_transfer_amount')}}<block v-if="drpTransData.min_money">{{drpTransData.min_money}}</block>{{$t('lang.yuan')}}</view>
							</view>
							<view class="btn-bar btn-bar-radius">
								<button class="tixian_btn" type="warn" :loading="isLoading" :disabled="isLoading" @click="formSubmit">{{$t('lang.confirm')}}</button>
							</view>
						</view>
					</swiper-item>
					<swiper-item v-if="drpTransData.openid">
						<view class="drp-withdraw">
							<view class="uni-form-item uni-column">
								<view class="uni-input flex_box ai_center">
									<text class="dsc_lable">{{ currency_format }}</text>
									<input class="dsc_input" type="digit" v-model="weiXinMinNum" :placeholder="$t('lang.enter_shift_amount')" />
								</view>
								<view class="sub">{{$t('lang.max_transfer_amount')}}<block v-if="drpTransData.max_money">{{drpTransData.max_money}}</block>,{{$t('lang.min_transfer_amount')}}<block v-if="drpTransData.min_money">{{drpTransData.min_money > 1 ? drpTransData.min_money : 1}}</block>{{$t('lang.yuan')}}</view>
							</view>
							<view class="btn-bar btn-bar-radius">
								<button class="tixian_btn" type="warn" :loading="isLoading" :disabled="isLoading" @click="withdrawMoneyToWeiXin">申请</button>
							</view>
						</view>
					</swiper-item>
					<swiper-item v-if="drpTransData.is_show_wxpay_bank > 0">
						<view class="tip-fee">每笔按付款金额收取手续费，按金额0.1%收取，最低1元，最高25元</view>
						<view class="drp-withdraw">
							<view class="field_box">
								<text>姓名</text>
								<input class="dsc_field" type="text" v-model="payeeName" placeholder="银行卡开户姓名" />
							</view>
							<view class="field_box">
								<text>银行</text>
								<view class="dsc_field selece_bank" @click="showPopup">
									<text class="bankName" :style="{color: bankCode ? '#333' : '#888'}" v-text="bankName"></text>
								</view>
							</view>
							<view class="field_box">
								<text>卡号</text>
								<input class="dsc_field" type="number" v-model="payeeNunber" placeholder="收款人银行卡号" />
							</view>
							<view class="uni-form-item uni-column">
								<view class="uni-input flex_box ai_center">
									<text class="dsc_lable">&yen;</text>
									<input class="dsc_input" type="digit" v-model="bankMinNum" :placeholder="$t('lang.enter_shift_amount')" />
								</view>
								<view class="sub">{{$t('lang.max_transfer_amount')}}<block v-if="drpTransData.max_money">{{drpTransData.max_money}}</block>,{{$t('lang.min_transfer_amount')}}<block v-if="drpTransData.min_money">{{drpTransData.min_money > 1 ? drpTransData.min_money : 1}}</block>{{$t('lang.yuan')}}</view>
							</view>
							<view class="btn-bar btn-bar-radius">
								<button class="tixian_btn" type="warn" :loading="isLoading" :disabled="isLoading" @click="withdrawMoneyToBank">{{$t('lang.confirm')}}</button>
							</view>
						</view>
					</swiper-item>
				</swiper>
			</view>
		</template>
		
		<dsc-popup ref="pickerView" type="bottom">
			<view class="picker_view_main" :style="{height: `${vh}px`}">
				<view class="picker_view_menu">
					<view class="cancel_picker_btn" @click="cancelPicker">取消</view>
					<view class="confirm_picker_btn" @click="confirmPicker">确定</view>
				</view>
				<picker-view class="picker_view_content" :indicator-style="indicatorStyle" :value="pickerValue" @change="bindChange">
				    <picker-view-column>
				        <view class="picker_item" v-for="(item,index) in drpTransData.bank_list" :key="index">{{item.bank_name}}</view>
				    </picker-view-column>
				</picker-view>
			</view>
		</dsc-popup>
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniCollapse from '@/components/uni-collapse/uni-collapse.vue'
	import uniCollapseItem from '@/components/uni-collapse-item/uni-collapse-item.vue'
	
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import navBar from '@/components/dsc-nav-bar/nav-bar.vue';
	import dscPopup from '@/components/uni-popup/uni-popup.vue';
	
	export default {
		data() {
			return {
				amount: '',
				vh: 0,
				navIndex:0,
				isLoading: false,
				list:[
					{'nav_name': '转出到余额'}
				],
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				isShowPopup: false,
				payeeName: '',
				payeeNunber: '',
				bankCode: '',
				bankName: '请选择银行',
				bankMinNum: null,
				weiXinMinNum: null,
				pickerValue: [],
				indicatorStyle: `height: ${Math.round(uni.getSystemInfoSync().screenWidth/(750/100))}px;`
			}
		},
		components:{
			'dsc-nav-bar': navBar,
			uniIcons,
			uniCollapse,
			uniCollapseItem,
			dscPopup,
			dscCommonNav,
			dscNotContent
		},
		onLoad() {
			
			this.vh = uni.getSystemInfoSync().windowHeight / 2;
			this.load();
		},
		computed: {
			...mapState({
				drpTransData: state => state.drp.drpTransData,
			})
		},
		watch: {
			drpTransData: {
				handler: function (val, oldVal) {
					
					let flag = Object.prototype.toString.call(val) === '[object Object]'
					
					if (flag) {
						if (val.openid) {
							this.list = [...this.list, {'nav_name':'转出到微信'}];
						} 
						if (val.is_show_wxpay_bank > 0) {
							this.list = [...this.list, {'nav_name':'转出到银行卡'}];
						}
						
					}
				},
				deep: true
			}
		},
		methods: {
			// 点击导航改变索引
			changeIndex(i) {
				this.navIndex = i;
			},
			change(e){
				let index = e.target.current;
				this.navIndex = index;
			},
			load(){
				let platform = uni.getStorageSync('platform')
				
				this.$store.dispatch('setDrpTrans',{platform: platform});
			},
			dscToast(str) {
				uni.showToast({
					title: str,
					icon: 'none'
				});
			},
			showPopup() {
				this.$refs.pickerView.open();
			},
			cancelPicker() {
				this.$refs.pickerView.close();
			},
			confirmPicker() {
				let i = 0;
				
				if (this.pickerValue.length > 0) i = this.pickerValue[0];
				
				this.bankCode = this.drpTransData.bank_list[i].bank_code;
				this.bankName = this.drpTransData.bank_list[i].bank_name;
				
				this.$refs.pickerView.close();
			},
			bindChange(e) {
				const val = e.detail.value;
				this.pickerValue = val;
			},
			formSubmit(e){
				let minMoney = Number(this.drpTransData.min_money)
				let maxMoney = Number(this.drpTransData.max_money)
				let inputValue = Number(this.amount)
				
				if (inputValue < minMoney) {
					uni.showToast({
						title: this.$t('lang.amount_cannot_less') + minMoney + this.$t('lang.yuan'),
						icon: 'none'
					});
				} else if (inputValue > maxMoney) {
					uni.showToast({
						title: this.$t('lang.amount_exceeds_max_limit'),
						icon: 'none'
					});
				} else {
					let queryData = {
						amount: inputValue,
						deposit_type: 0
					};
					this.submitHandle(queryData)
				}
			},
			withdrawMoneyToBank() {
				let minMoney = Number(this.drpTransData.min_money)
				let maxMoney = Number(this.drpTransData.max_money)
				let num = minMoney < 1 ? 1 : minMoney;
				if (!this.payeeName.trim()) return this.dscToast('银行卡开户姓名不能为空')
				if (!this.bankCode.trim()) return this.dscToast('请选择银行')
				if (!this.payeeNunber.trim()) return this.dscToast('收款人银行卡号不能为空')
				if (this.bankMinNum < num) {
					return this.dscToast(this.$t('lang.amount_cannot_less') + num + this.$t('lang.yuan'))
				} else if (this.bankMinNum > maxMoney) {
					return this.dscToast(this.$t('lang.amount_exceeds_max_limit'))
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
				let minMoney = Number(this.drpTransData.min_money)
				let maxMoney = Number(this.drpTransData.max_money)
				let num = minMoney < 1 ? 1 : minMoney;
				if (this.weiXinMinNum < num) {
					return this.dscToast(this.$t('lang.amount_cannot_less') + num + this.$t('lang.yuan'))
				} else if (this.weiXinMinNum > maxMoney) {
					return this.dscToast(this.$t('lang.amount_exceeds_max_limit'))
				}
				
				let queryData = {
					deposit_type: 1,
					amount: this.weiXinMinNum
				};
				this.submitHandle(queryData)
			},
			submitHandle(queryInfo) {
				
				this.isLoading = true;
				
				this.$store.dispatch('setDrpTransferred', queryInfo).then(res=>{
					this.isLoading = false;
					if(res.status == 'success'){
						uni.showToast({
							title:res.data.msg,
							icon:'none'
						});
						
						if(res.data.error == 0){
							setTimeout(()=>{
								uni.navigateTo({
									url:'../withdrawLog/withdrawLog'
								})
							},3000);
						}
					}else{
						uni.showToast({
							title: this.$t('lang.deposit_fail'),
							icon:'none'
						});
					}
				})
			}
		}
	}
</script>

<style scoped>
.container {
	height: 100%;
}
.drp-withdraw{ background: #FFFFFF; }
.drp-withdraw .title{ font-size: 35upx; }
.drp-withdraw .sub{ padding: 0 25upx; color: #999999;}
.drp-withdraw .btn-bar{ margin: 25upx; }
.main {
	height: calc(100% - 100upx);
}
.dsc_swiper {
	height: 100%;
}
.tip-fee {
	font-size: 22upx;
	text-align: center;
	color: #fff;
	padding: 20upx 0;
    background-color: #1989fa;
}
.field_box {
	display: flex;
	align-items: center;
	padding: 25upx;
	border-bottom: 1upx solid #f4f4f4;
}
.dsc_field {
	flex: auto;
	margin-left: 30upx;
}
.selece_bank {
	position: relative;
}
.selece_bank::after {
	position: absolute;
	display: inline-block;
	content: '';
	top: 50%;
	right: 0;
	transform: translateY(-50%);
	transform: rotate(45deg);
	width: 16upx;
	height: 16upx;
	border-top: 2upx solid #888;
	border-right: 2upx solid #888;
}
.bankName {
	color: #888;
}
.picker_view_main {
		width: 100%;
		background-color: #fff;
	}
	
	.picker_view_menu {
		display: flex;
		justify-content: space-between;
		align-items: center;
		top: 0;
		left: 0;
		width: 100%;
		height: 100upx;
		background-color: #fff;
		z-index: 99999;
		/* border-bottom: 1px solid #EEEEEE; */
	}
	
	.cancel_picker_btn,
	.confirm_picker_btn {
		width: 160upx;
		height: 100upx;
		line-height: 100upx;
		text-align: center;
		font-size: 30upx;
	}
	
	.cancel_picker_btn {
		color: #9A9A9A;
	}
	
	.confirm_picker_btn {
		color: #FA2A2A;
	}
	
	.picker_view_content {
		width: 100%;
		height: calc(100% - 100upx);
	}
	
	.picker_item {
		display: flex;
		justify-content: center;
		align-items: center;
		text-align: center;
	}
	.dsc_lable {
		font-size: 44upx;
		margin-right: 30upx;
	}
	.dsc_input {
		flex: auto;
		font-size: 40upx;
	}
	.tixian_btn {
		width: 100%;
	}
</style>
