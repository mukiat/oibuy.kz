<template>
	<div class="con con_main">
		<div class="flow-checkout">
			<section class="flow-checkout-item">
				<van-cell-group class="van-cell-noleft" v-if="invoiceDetail">
					<van-cell title="发票详情" class="van-cell-title b-min b-min-b"></van-cell>
					<van-cell title="订单编号：" >{{ invoiceDetail.client_sn }}</van-cell>
					<van-cell title="发票类型：" >

						<div class="dis-box" v-if="invoiceDetail.inv_type">
							<label class="t-remark g-t-temark">{{ invoiceDetail.inv_type }}</label>
							<div class="box-flex text-right f-04">
								<a href="javascript:void(0)"  @click="invoicePdf(invoiceDetail.file_path)"><em class="color-red"> 查看发票</em></a>
							</div>
						</div>

					</van-cell>

					<van-cell title="发票抬头：" >{{ order_inv_payee }}</van-cell>
					<template v-if="order_tax_id != ''">
						<van-cell title="纳税人识别号：" >{{ order_tax_id }}</van-cell>
					</template>
					<van-cell title="发票内容：">{{ invoiceDetail.inv_content }}</van-cell>
				</van-cell-group>
			</section>
			<section>
				<div class="van-submit-bar van-order-submit-bar">
					<div class="van-submit-bar__bar">
						<van-goods-action-big-btn text="换开申请" primary  @click="reapply(invoiceDetail.order_id)"/>
					</div>
				</div>
			</section>
		</div>
	</div>
</template>

<script>
	import { mapState } from 'vuex'

	import {
		Cell,
		CellGroup,
		SubmitBar,
		GoodsAction,
	  	GoodsActionBigBtn,
	  	GoodsActionMiniBtn,
	} from 'vant'

	export default{
		data(){
			return{
				order_sn:'',
				inv_type:'',
                inv_payee:'',
                tax_id:'',
				inv_content:'商品明细',
			}
		},
		components:{
			[Cell.name] : Cell,
			[CellGroup.name] : CellGroup,
			[SubmitBar.name] : SubmitBar,
			[GoodsAction.name] : GoodsAction,
			[GoodsActionBigBtn.name] : GoodsActionBigBtn,
			[GoodsActionMiniBtn.name] : GoodsActionMiniBtn,
		},
		created(){
            this.onLoad()
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
            onLoad(){
              this.$store.dispatch('setInvoiceDetail',{
                order_id:this.$route.params.order_id
              })
            },
			reapply(id){
                this.$router.push({
                    name:'invoiceReapply',
                    query:{
                        order_id:id
                    }
                })
			},
			// 预览发票pdf
            invoicePdf(file_path){
                let pdfView = `${window.ROOT_URL}` + 'assets/shouqianba/js/pdf/web/viewer.html';
                // 将pdf文件 通过api返回文件流
                let path = `${window.ROOT_URL}` + '/api/shouqianba/fileView' + '?file_path=' + file_path;
				window.open(pdfView + "?file=" + encodeURIComponent(path))
			}
		}
	}
</script>

<style>
	
</style>