<template>
	<view class="container">
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">订单号：</text>
						<view class="value"><input name="order_sn" v-model="order.order_sn" disabled="true"></view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">发票类型：</text>
						<view class="value"><input name="inv_type" v-model="inv_type" disabled="true"></view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">发票内容：</text>
						<view class="value"><input name="inv_content" v-model="inv_content" disabled="true"></view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">发票抬头：</text>
						<view class="value">
							<radio-group @change="invValueRadioHandle">
								<view class="uni-list-cell-flex">
								<label class="uni-list-cell uni-list-cell-not">
									<view><radio value="0" :checked="inv_company == 0" /></view>
									<view>个人</view>
								</label>
								<label class="uni-list-cell uni-list-cell-not">
									<view><radio value="1" :checked="inv_company == 1" /></view>
									<view>单位</view>
								</label>
								</view>
							</radio-group>
						</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="inv_company == 1">
					<view class="uni-list-cell-navigate">
						<text class="title">公司名称：</text>
						<view class="value"><input name="inv_name" v-model="inv_name" placeholder="请填写公司名称"></view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="inv_company == 1">
					<view class="uni-list-cell-navigate">
						<text class="title">纳税人识别号：</text>
						<view class="value"><input name="inv_name" v-model="inv_name" placeholder="请填写税号"></view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">手机：</text>
						<view class="value"><input name="mobile" v-model="mobile" placeholder="请填写手机号码"></view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">邮箱：</text>
						<view class="value"><input name="email" v-model="email" placeholder="请填写邮箱"></view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-bar">
			<button class="btn" type="primary" v-if="apply_status >= 0">已申请</button>
			<button class="btn btn-red" type="primary" @click="onSubmit" v-else>提交</button>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default {
		data() {
			return {
				inv_type: '电子普通发票',
				inv_tid: '',
				inv_content: '商品明细',
				inv_company: 0,
				inv_tax_sn: '',
				inv_name: '',
				mobile: '',
				email: '',
				order_id: ''
			}
		},
		computed: {
		    ...mapState({
		        invoiceReapplyInfo: state => state.custom.invoiceReapplyInfo
		    }),
		    invoiceInfo(){
		        return this.invoiceReapplyInfo.invoiceInfo ? this.invoiceReapplyInfo.invoiceInfo : ''
		    },
		    order(){
		        return this.invoiceReapplyInfo.order
		    },
		    apply_status(){
		        return this.invoiceInfo && this.invoiceInfo.apply_status ? this.invoiceInfo.apply_status : ''
		    },
		    edit_status(){
		        return this.inv_company == 1 && this.inv_name ? 0 : 1;
		    },
		    radio_status(){
		        return this.apply_status >= 0 ? true : false;
		    }
		},
		methods: {
		    reapplyInfo(){
		        this.$store.dispatch('setInvoiceReapplyInfo', {
		            order_id: this.order_id
		        })
		    },
		    onSubmit(){
		        this.$store.dispatch('setInvoiceReapply', {
		            order_id: this.order_id,
		            business_type: this.inv_company,
		            payer_name: this.inv_company == 1 ? this.inv_name : '个人',
		            payer_register_no: this.inv_tax_sn,
		            mobile: this.mobile,
		            email: this.email
		        }).then(res => {
					uni.showToast({ title: res.data.msg, icon: "none" });
					uni.navigateTo({
						url:'/pages/shouqianba/invoiceDetail?order_id=' + this.order_id
					});
		        })
		    },
			invValueRadioHandle(e){
				this.inv_company = e.detail.value;
			}
		},
		onLoad(e){
			console.log(e)
			this.order_id = e.order_id;
			
			this.reapplyInfo();
		},
		watch: {
		    invoiceReapplyInfo(){
		        this.inv_company = this.invoiceInfo ? this.invoiceInfo.invoice_again_data.business_type : this.invoiceReapplyInfo.order.business_type;
		        this.mobile = this.invoiceInfo ? this.invoiceInfo.invoice_again_data.mobile : this.order.mobile;
		        this.email = this.invoiceInfo ? this.invoiceInfo.invoice_again_data.email : this.order.email
		        this.inv_name = this.invoiceInfo ? this.invoiceInfo.invoice_again_data.payer_name : this.order.payer_name;
		        this.inv_tax_sn = this.invoiceInfo ? this.invoiceInfo.invoice_again_data.payer_register_no : this.order.payer_register_no;
		    }
		}
	}
</script>

<style>
	.uni-list-cell-navigate .uni-list-cell-flex{ display: flex; }
	.uni-list-cell-navigate .uni-list-cell{ justify-content: flex-start;}
</style>
