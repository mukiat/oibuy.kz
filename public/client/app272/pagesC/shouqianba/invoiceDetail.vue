<template>
	<view class="container">
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">订单编号：</text>
						<view class="value">{{ invoiceDetail.client_sn }}</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">发票类型：</text>
						<view class="value receipt">
							<view class="receipt-title">{{ invoiceDetail.inv_type }}</view>
							<!-- <view class="receipt-name uni-red" @click="invoicePdf(invoiceDetail.file_path)">查看发票</view> -->
						</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">发票抬头：</text>
						<view class="value">{{ order_inv_payee }}</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="order_tax_id != ''">
					<view class="uni-list-cell-navigate">
						<text class="title">纳税人识别号：</text>
						<view class="value">{{ order_tax_id }}</view>
					</view>
				</view>
				<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
					<view class="uni-list-cell-navigate">
						<text class="title">发票内容：</text>
						<view class="value">{{ invoiceDetail.inv_content }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-bar">
			<button class="btn btn-red" type="primary" @click="reapply(order_id)">换开申请</button>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	export default {
		data(){
			return{
				order_id:'',
				order_sn:'',
				inv_type:'',
		        inv_payee:'',
		        tax_id:'',
				inv_content:'商品明细',
			}
		},
		components:{
			
		},
		computed:{
		    ...mapState({
		    	invoiceDetail: state => state.custom.invoiceDetail
		    }),
		    order_inv_payee(){
		        // 发票抬头
		        return this.invoiceDetail.get_order ? this.invoiceDetail.get_order.inv_payee : '';
		    },
		    order_tax_id(){
		        // 纳税人识别号
		        return this.invoiceDetail.get_order ? this.invoiceDetail.get_order.tax_id : '';
		    }
		},
		methods:{
		    invoiceLoad(){
		      this.$store.dispatch('setInvoiceDetail',{
		        order_id:this.order_id
		      })
		    },
			reapply(id){
				uni.navigateTo({
					url:'./invoiceReapply?order_id=' + id
				})
			},
			// 预览发票pdf
		    invoicePdf(file_path){
		        //let pdfView = `${window.ROOT_URL}` + 'assets/shouqianba/js/pdf/web/viewer.html';
		        // 将pdf文件 通过api返回文件流
		        //let path = `${window.ROOT_URL}` + '/api/shouqianba/fileView' + '?file_path=' + file_path;
				//window.open(pdfView + "?file=" + encodeURIComponent(path))
			}
		},
		onLoad(e){
			this.order_id = e.order_id;
			this.invoiceLoad()
		}
	}
</script>

<style>
	.uni-list-cell-navigate .receipt{
		flex-direction: column;
		align-items: flex-end;
	}
	.uni-list-cell-navigate .receipt .txt{
		margin: 0 10upx;
	}
</style>
