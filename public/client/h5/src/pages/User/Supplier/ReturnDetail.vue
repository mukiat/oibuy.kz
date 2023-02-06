<template>
  <div class="con_main">
    <section class="section-list">
    	<div class="detail-title">{{$t('lang.return_apply_title')}}</div>
    	<div class="bg-color-write">
            <div class="product-list product-list-small" v-if="refoundDetail">
              <ul>
                <li>
                  <div class="product-div">
                    <div class="product-list-img"><img class="img" :src="refoundDetail.goods_thumb"></div>
                    <div class="product-info">
                      <h4>{{ refoundDetail.goods_name }}</h4>
                      <div class="p-attr">{{ refoundDetail.goods_attr}}</div>
                      <div class="price">
                        <label v-html="refoundDetail.goods_price"></label>
                        <span>x{{refoundDetail.return_number}}</span>
                      </div>
                      <div class="p-t-remark" v-if="refoundDetail.get_order_return">{{ refoundDetail.get_order_return.attr_val }}</div>
                      </div>
                  </div>
                </li>
              </ul>
            </div>
        </div>
    </section>
    <section class="section-list">
    	<div class="detail-title">{{$t('lang.detail_info')}}</div>
    	<ul class="user-refound-box">
    		<li>
    			<div>{{$t('lang.return_sn_user')}}:</div>
    			<div class="value color-red">{{refoundDetail.return_sn}}</div>
    		</li>
    		<li>
    			<label>{{$t('lang.apply_time')}}:</label>
    			<div class="value color-red">{{refoundDetail.apply_time}}</div>
    		</li>
    		<li>
    			<label>{{$t('lang.service_type')}}:</label>
    			<div class="value color-red">
                    <template v-if="refoundDetail.return_type == 0">
                    {{$t('lang.order_return_type_0')}}
                    </template>
                    <template v-else-if="refoundDetail.return_type == 1">
                    {{$t('lang.order_return_type_1')}}
                    </template>
                    <template v-else-if="refoundDetail.return_type == 2">
                    {{$t('lang.order_return_type_2')}}
                    </template>
                    <template v-else="refoundDetail.return_type == 3">
                    {{$t('lang.order_return_type_3')}}
                    </template>
                </div>
    		</li>
    	</ul>
    	<div class="detail-title m-top10">{{ $t('lang.order_status') }}</div>
    	<ul class="user-refound-box">
    		<li>
    			<label>{{ $t('lang.order_status') }}:</label>
    			<div class="value color-red">{{refoundDetail.return_status}} - {{refoundDetail.refound_status}}</div>
    		</li>
    		<li>
    			<label>{{ $t('lang.problem_desc') }}:</label>
    			<div class="value color-red">{{refoundDetail.return_brief}}</div>
    		</li>
            <template v-if="refoundDetail.return_status == 6">
            <li>
                <label>{{ $t('lang.refusal_cause') }}:</label>
                <div class="value color-red">{{refoundDetail.action_note}}</div>
            </li>
            </template>
            <template v-else>
            <li>
                <label>{{ $t('lang.return_reason') }}:</label>
                <div class="value color-red">{{refoundDetail.return_cause}}</div>
            </li>
            </template>
            <template v-if="refoundDetail.return_shipping_fee > 0 ">
            <li>
                <label>{{ $t('lang.return_shipping') }}:</label>
                <div class="value"><div class="price"><span v-html="refoundDetail.formated_return_shipping_fee"></span></div></div>
            </li>
            </template>
    		<template v-if="refoundDetail.return_type == 1 || refoundDetail.return_type == 3">
    		<li>
    			<label>{{ $t('lang.amount_return') }}:</label>
    			<div class="value"><div class="price"><span v-html="refoundDetail.formated_should_return"></span></div></div>
    		</li>
            </template>
    	</ul>
        <template v-if="refoundDetail.img_list && refoundDetail.img_list.length > 0">
        <ul class="user-refound-box b-color-f m-top10">
            <li class="dis-box">
                <div>{{$t('lang.voucher_pic')}}:</div>
                <div class="box-flex"><p class="col-3 text-right"> </p></div>
            </li>
            <div class="goods-evaluation-page b-color-f tab-con refound-list-box">
                <div class="my-gallery" data-pswp-uid="5">
                    <figure v-for="(item,index) in refoundDetail.img_list" :key="index">
                        <div><a :href="item.img_file" data-size="640x640"><img class="img" :src="item.img_file"></a></div>
                    </figure>
                </div>
                <div class="refound-list-box-bg">
                    <div class="goods-list-close position-abo"><i class="iconfont icon-guanbi2 text-r"></i></div>
                </div>
            </div>
        </ul>
        </template>
    	<ul class="user-refound-box m-top10">
    		<li>
    			<label>{{$t('lang.consignee')}}:</label>
    			<div class="value">{{refoundDetail.addressee}}</div>
    		</li>
    		<li>
    			<label>{{$t('lang.phone_number')}}:</label>
    			<div class="value">{{refoundDetail.phone}}</div>
    		</li>
    		<li>
    			<label>{{$t('lang.address_alt')}}:</label>
    			<div class="value">{{refoundDetail.address_detail}}</div>
    		</li>
    	</ul>
        <template v-if="refoundDetail.agree_apply == 1 && refoundDetail.return_type != 3">
            <template v-if="refoundDetail.back_shipp_shipping">
                <div class="detail-title m-top10">{{$t('lang.express_info')}} <span class="help color-red">({{$t('lang.user_sent')}})</span></div>
                <ul class="user-refound-box b-color-f m-top04">
                    <li class="dis-box">
                        <div>{{$t('lang.express_company')}}:</div>
                        <div class="box-flex"><p class="col-3 text-right">{{refoundDetail.back_shipp_shipping}}</p></div>
                    </li>
                    <li class="dis-box">
                        <div>{{$t('lang.courier_sz')}}:</div>
                        <div class="box-flex"><p class="col-3 text-right">{{refoundDetail.back_invoice_no}}</p></div>
                    </li>
                    <li class="dis-box" v-if="refoundDetail.back_invoice_no_btn">
                        <div class="box-flex">
                            <p class="col-3 text-right n-refound-btn">
                                <a :href="refoundDetail.back_invoice_no_btn" class="btn-default-new current">{{$t('lang.order_tracking')}}</a>
                            </p>
                        </div>
                    </li>
                </ul>
            </template>
            <template v-else>
                <div class="detail-title m-top10">{{$t('lang.express_info')}} <span class="help color-red">({{ $t('lang.fill_in_express_info') }})</span></div>
                <van-cell-group class="van-cell-noright m-top08">
                    <van-cell :title="$t('lang.label_express_company')">
                        <div class="select-one-1">
                            <select class="select form-control parent_cause_select" v-model="shippingSelect">
                                <option v-for="item in shipping_list" :value="item.shipping_id">{{ item.shipping_name }}</option>
                                <option value="999">{{$t('lang.outer_express')}}</option>
                            </select>
                        </div>
                    </van-cell>
                    <van-cell :title="$t('lang.label_outer_express')" v-if="shippingSelect == 999">
                        <van-field :placeholder="$t('lang.fill_in_express_company')" v-model="other_express" class="van-cell-ptb0" />
                    </van-cell>
                    <van-cell :title="$t('lang.label_courier_sz')">
                        <van-field :placeholder="$t('lang.fill_in_courier_sz')" v-model="express_sn" class="van-cell-ptb0" />
                    </van-cell>
                    <div class="filter-btn">
                        <div class="btn btn-submit" @click="submitBtn">{{$t('lang.subimt')}}</div>
                    </div>
                </van-cell-group>
            </template>

            <template v-if="refoundDetail.out_shipp_shipping">
                <div class="detail-title m-top10">{{$t('lang.express_info')}} <span class="help color-red">({{$t('lang.seller_shipping')}})</span></div>
                <ul class="user-refound-box b-color-f m-top04">
                    <li class="dis-box">
                        <div>{{$t('lang.express_company')}}:</div>
                        <div class="box-flex"><p class="col-3 text-right">{{refoundDetail.out_shipp_shipping}}</p></div>
                    </li>
                    <li class="dis-box">
                        <div>{{$t('lang.courier_sz')}}:</div>
                        <div class="box-flex"><p class="col-3 text-right">{{refoundDetail.out_invoice_no}}</p></div>
                    </li>
                    <li class="dis-box" v-if="refoundDetail.out_invoice_no_btn">
                        <div class="box-flex">
                            <p class="col-3 text-right n-refound-btn">
                                <a :href="refoundDetail.out_invoice_no_btn" class="btn-default-new current">{{$t('lang.order_tracking')}}</a>
                            </p>
                        </div>
                    </li>
                </ul>
            </template>

        </template>
    </section>
    <template v-if="refoundDetail.status == 3">
    <div class="filter-btn">
        <div class="btn btn-submit" @click="receivedOrder">{{$t('lang.received')}}</div>
    </div>
    </template>
    <template v-else-if="refoundDetail.status == 4 || refoundDetail.status == 1">
    <div class="filter-btn">
        <div class="btn btn-submit">{{$t('lang.ss_received')}}</div>
    </div>
    </template>
    <template v-else-if="refoundDetail.status == 6">
    <div class="filter-btn">
        <div class="btn btn-submit">{{$t('lang.denied')}}</div>
    </div>
    </template>
    <template v-else-if="refoundDetail.agree_apply == 0">
    <div class="filter-btn">
	   <div class="btn btn-submit">{{$t('lang.to_be_agreed')}}</div>
    </div>
    </template>
    <CommonNav></CommonNav>
  </div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'

import {
    Cell,
    CellGroup,
    Field,
    Button,
    Dialog,
    Toast
} from 'vant'

import ProductList from '@/components/user/ProductList'
import CommonNav from '@/components/CommonNav'
export default{
	data(){
		return {
            shippingSelect:999,
            other_express:'',
            express_sn:''
        }
	},
    components:{
        [Cell.name]:Cell,
        [CellGroup.name]:CellGroup,
        [Field.name]:Field,
        [Button.name]:Button,
        [Dialog.name]:Dialog,
        [Toast.name]:Toast,
        ProductList,
        CommonNav
    },
    created(){
        this.refoundLoad()
    },
    computed:{
        ...mapState({
            refoundDetail: state => state.other.supplierRefoundDetail
        }),
        shipping_list(){
            return this.refoundDetail.shipping_list ? this.refoundDetail.shipping_list : []
        }
    },
    methods:{
        refoundLoad(){
            this.$store.dispatch('setSupplierReturnDatail',{
                ret_id:this.$route.query.ret_id
            })
        },
        receivedOrder(){
            Dialog.confirm({
                message: this.$t('lang.confirm_received'),
                className: 'text-center'
            }).then(() => {
                this.$http.post(`${window.ROOT_URL}api/refound/affirm_receive`,qs.stringify({
                    ret_id:this.$route.query.ret_id
                })).then(({data:{data}})=>{
                    Toast(data.msg)
                    if(data.code == 0){
                        this.refoundLoad()
                    }
                })
            }).catch(() => {

            })
        },
        submitBtn(){
            if(this.other_express ==''){
                Toast(this.$t('lang.fill_in_express_company'))
                return false
            }else if(this.express_sn == ''){
                Toast(this.$t('lang.fill_in_courier_sz'))
                return false
            }

            let o ={
                shipping_id:this.shippingSelect,
                express_name:this.other_express,
                express_sn:this.express_sn,
                order_id:this.refoundDetail.order_id,
                ret_id:this.refoundDetail.ret_id
            }

            this.$http.post(`${window.ROOT_URL}api/refound/edit_express`,qs.stringify(o)).then(({data:{data}})=>{
                Toast(data.msg)
                if(data.code == 0){
                    this.refoundLoad()
                }
            })
        }
    },
    watch:{
        shippingSelect(){
            if(this.shipping_list.length > 0){
                this.shipping_list.forEach(res=>{
                    if(res.shipping_id == this.shippingSelect){
                        this.other_express = res.shipping_name
                    }
                })
            }
        }
    }
}
</script>
