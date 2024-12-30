<template>
	<div v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<TabNav> 
	    	<template slot="tabItem">
	        <ul>
	          <li :class="{'active':status == 0}" @click="orderStatusHandle(0)">{{$t('lang.purchase_note')}}</li>
	          <li :class="{'active':status == 1}" @click="orderStatusHandle(1)">{{$t('lang.supplier_return')}}</li>
	        </ul>
	      </template>
	    </TabNav>
		<section class="store_info">
			<template v-if="orderList && orderList.length > 0">
				<div class="user-item user-order-item" v-for="(item,index) in orderList" :key="index">
					<div class="item-hd">
		    			<div class="head">
		    				<h3>{{ item.shop_name }}</h3>
		    				<em>{{ item.order_status }}</em>
		    			</div>	
		    		</div>
		    		<div class="item-bd">
		    			<div class="subHead">
		    				<h4><label>{{$t('lang.order_sn')}}：</label><span>{{ item.order_sn }}</span></h4>
		    				<p>
				                <span>{{$t('lang.order_time')}}：{{ item.order_time }} </span>
				            </p>
		    			</div>
			            <template v-if="item.order_goods">
		  	    			<div class="list-bd-box list-order-box" v-for="(goodsItem,goodsIndex) in item.order_goods" :key="goodsIndex">
		  	    				<ul class="dis-box">
	    							<li class="reture-left-img"><div class="img-box"><img :src="goodsItem.goods_thumb" class="img"></div></li>
	    							<li class="reture-right-cont">
	    								<h4 class="twolist-hidden">{{ goodsItem.goods_name }}</h4>
	    								<div class="p-attr">{{ goodsItem.goods_attr }}</div>
	    							</li>
	                  				<li class="reture-right-san">x{{ goodsItem.goods_number }}</li>
		    					</ul>
		  	    			</div>
			            </template>
			            <div class="list-supplier-info">
			            	<p><span>{{$t('lang.contact')}}</span><em>{{item.consignee}}</em></p>
			            	<p><span>{{$t('lang.contact_way')}}</span><em>{{item.mobile}}</em></p>
			            	<p><span>{{$t('lang.shipping_address')}}</span><em>{{item.address}}</em></p>
			            	<p><span>{{$t('lang.label_invoice_type')}}</span><em v-if="item.inv_type == 0">{{$t('lang.plain_invoice')}}</em><em v-else>{{$t('lang.vat_tax_invoice')}}</em></p>
			            	<p><span>{{$t('lang.label_invoice_company')}}</span><em>{{item.inv_payee}}</em></p>
			            	<p v-if="item.tax_id"><span>{{$t('lang.taxpayer_id_number')}}</span><em>{{item.tax_id}}</em></p>
			            	<p><span>{{$t('lang.label_invoice_content')}}</span><em>{{item.inv_content}}</em></p>
			            	<p><span>{{$t('lang.label_payment_mode')}}</span><em class="color-red">{{item.pay_name}}</em></p>
			            	<p><span>{{$t('lang.label_shipping_mode')}}</span><em class="color-red">{{$t('lang.merchant_shipping')}}</em></p>
			            	<p><span>{{$t('lang.label_payment_status')}}</span><em class="color-red" v-if="item.pay_status == 0">{{$t('lang.order_status_06')}}</em><em class="color-red" v-else-if="item.pay_status == 2">{{$t('lang.order_status_07')}}</em></p>
			            </div>
				    	<div class="list-item-box">{{$t('lang.gong')}}{{item.order_goods_num}}{{$t('lang.total_amount_propmt_alt')}}：<span v-html="item.total_fee" class="color-red"></span></div>
			    	</div>
			    	<div class="item-fd">
		    			<h4></h4>
		    			<div class="ect-button-more">
		    				<a href="javascript:void(0)" @click="onlinepay(item.order_sn,item.pay_code)" class="btn-default-new" v-if="item.pay_status != 2">{{$t('lang.immediate_pay')}}</a>
		    				<a href="javascript:void(0)" @click="refoundHandle(item.order_id)" class="btn-default-new"v-if="item.status != 0">{{$t('lang.apply_return_alt')}}</a>
				        	<a href="javascript:void(0)" @click="delayOrder(item.order_id)" class="btn-default-new" v-if="item.status == 0">{{$t('lang.setup_complete')}}</a>
				        	<a href="javascript:void(0)" class="btn-default-new" v-else>{{$t('lang.ss_received')}}</a>
						</div>
		    		</div>
				</div>
			</template>
			<template v-else>
		    	<NotCont></NotCont>
		    </template>
		    <template v-if="loading">
	        	<van-loading type="spinner" color="black" />
	      	</template>
		</section>
		<CommonNav :routerName="routerName"></CommonNav>
	</div>
</template>
<script>
	import { mapState } from 'vuex'

	import {
	    Waterfall,
	    Loading,
	    Toast,
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import TabNav from '@/components/user/TabNav'
	import arrRemove from '@/mixins/arr-remove'

	export default{
		data(){
			return {
	            disabled:false,
	            loading:true,
	            page:1,
	            size:10,
	            status:0,
	            type:'type',
	            affirmorder:false,
	            routerName:'supplier',
			}
		},
		directives: {
	        WaterfallLower: Waterfall('lower')
	    },
		components:{
		    TabNav,
		    CommonNav,
		    NotCont,
		    [Loading.name]: Loading,
		},
	    created(){
	        this.setSupplierOrderList()
	    },
	    computed:{
	        orderList:{
	            get(){
	                return this.$store.state.user.supplierOrderList
	            },
	            set(val){
	                this.$store.state.user.supplierOrderList = val
	            }
	        }
	    },
	    methods:{
	    	setSupplierOrderList(page){
	    		if(page){
	                this.page = page
	                this.size = Number(page) * 10
	            }

	            this.$store.dispatch('setSupplierOrderList',{
	                page:this.page,
	                size:this.size
	            })
	    	},
	    	loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.orderList.length){
						this.page ++
						this.setSupplierOrderList() 
					}
				},200);
	        },
	        delayOrder(order_id){
	        	this.$store.dispatch('setSupplierAffirmorder',{
	                order_id:order_id,
	            }).then(res=>{
	            	Toast(res.data.msg)
	            	if(res.status == 'success'){
	            		this.setSupplierOrderList();
	            	}
	            })
	        },
	        refoundHandle(order_id){
	        	this.$router.push({
					name:'supplier-returnindex',
					query:{
						id:order_id
					}
				})
	        },
	        orderStatusHandle(val){
	            if(val == 1){
		            this.$router.push({
						name:'supplier-returnindex'
					})
				}
	        },
	        //立即支付
	        onlinepay(id,code){
	        	if(code == 'balance'){
	        		this.$router.push({
                name:'supplier-done',
                query:{
                  order_sn:id,
                  pay_code:'balance'
                }
            	})
            }else{
	            this.$router.push({
                name:'supplier-done',
                query:{
                    order_sn:id
                }
              })
	          }
	        },
	    },
	    watch:{
	    	orderList(){
	            if(this.page * this.size == this.orderList.length){
	                this.disabled = false
	                this.loading = true
	            }else{
	                this.loading = false
	            }
	            this.orderList = arrRemove.trimSpace(this.orderList)
	        }
	    }
	}
</script>
<style scoped>
	.user-order-item .item-bd .reture-right-cont h4{ margin-top: 1rem; }
</style>