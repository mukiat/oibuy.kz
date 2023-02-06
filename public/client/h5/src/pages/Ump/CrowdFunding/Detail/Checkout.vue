<template>
<div class="con">
    <div class="flow-checkout" v-if="crowdCheckoutData">
        <section class="show flow-checkout-item flow-checkout-adr" @click="checkoutAddress">
            <div v-if="crowdCheckoutData.default_address!=''">
            <van-cell-group>
                <van-cell :title="consignee_title" :label="consignee_address" icon="location" is-link></van-cell>
            </van-cell-group>
                </div>
            <div v-else>
              <div class="not-address text-center color-9">{{$t('lang.not_address')}}</div>
            </div>
        </section>
        <div class="goods-li bg-color-write" v-if="cart_goods">
            <router-link :to="{name:'crowdfunding-detail',params:{id:cart_goods.id }}" class="show bg-color-write li">
                <div class="left p-r">
                    <img class="img" :src="cart_goods.title_img" v-if="cart_goods.title_img" />
                    <img class="img" src="../../../../assets/img/not_goods.png" v-else />
                </div>
                <div class="right">
                    <h4 class="f-05 color-3">{{cart_goods.title}}</h4>
                    <div class="goods-num dis-box">
                        <div class="box-flex f-03 color-7">{{$t('lang.label_crowdfunding_fund')}}<em class="color-red" v-html="crowdCheckoutData.cart_goods.formated_price"></em>{{$t('lang.yuan')}}</div>
                        <div class="list f-02 color-9">{{$t('lang.support_number')}}
                            <span class="color-red">{{cart_goods.join_num}}</span>{{$t('lang.ren')}}
                        </div>
                    </div>
                    <div class="ect-progress dis-box">
                        <p class="wrap box-flex">
                            <span class="bar" :style="{width:cart_goods.baifen_bi + '%'}">
                                <i class="color"></i>
                            </span>
                        </p>
                        <p class="txt f-02">{{cart_goods.baifen_bi}}%</p>
                    </div>
                    <div class="goods-cont f-03 color-7">
                        {{cart_goods.content}}
                    </div>
                </div>
            </router-link>
        </div>
        <van-cell-group class="van-cell-noright m-top08">
            <van-cell :title="$t('lang.delivery_cost')">
                <div solt="value"><em class="color-red">{{ shipping_fee }}</em></div>
            </van-cell>
            <van-cell :title="$t('lang.buyer_message')" class="b-min b-min-t">
                <div solt="value">
                    <van-field v-model="value" :placeholder="$t('lang.buyer_message_placeholder')" class="van-cell-ptb0" />
                </div>
            </van-cell>
            <van-cell class="b-min b-min-t">
                <div solt="value"><span>{{$t('lang.gong')}}{{crowdCheckoutData.number}}{{$t('lang.total_amount_propmt_alt')}}：</span> <em class="color-red" v-html="crowdCheckoutData.total.amount_formated"></em></div>
            </van-cell>
        </van-cell-group>
	    <section class="checkout-goods-other" @click="crowsdPay">
	    	<van-cell-group class="van-cell-noright m-top08">
			 <van-cell :title="$t('lang.payment_mode')" v-model="pay_name" is-link />
			</van-cell-group>
	    </section>
        <section>
            <van-cell-group class="van-cell-noright m-top08" v-if="crowdCheckoutData.use_surplus > 0">
                <van-cell class="van-cell-title b-min b-min-b">
                    <div slot="title">{{$t('lang.is_use_balance')}}</div>
                    <van-switch v-model="surplusSelf" size="20px" class="fr"/>
                </van-cell>
            </van-cell-group>
        </section>
	    <section class="order-detail-submit order-checkout-submit">
    		<van-submit-bar :price="amountTotal" :label="$t('lang.label_total_amount_payable')" :button-text="$t('lang.immediate_payment')" @submit="onSubmit" />
    	</section>
        </div>
        <!--支付方式-->
        <van-popup v-model="showBase" position="bottom" class="attr-goods-box crowd-pay">
            <div class="attr-goods-header wallet-bt">
                <div class="dis-box">
                    <div class="box-flex f-05 color-3">{{$t('lang.payment_mode')}}</div>
                    <div @click="closeSku"><i class="iconfont icon-guanbi f-05 color-9"></i></div>
                </div>
            </div>
            <van-radio-group v-model="radio" class="bg-color-write">
                <van-radio :name ='item.pay_id' :class="{'active':pay_id == item.pay_id}" v-for="(item,index) in payment_method" :key="index" @click="payment_method_select(item.pay_id,item.pay_name)">
                    <div class="dis-box detail-scheme bg-color-write li" >
                        <div class="box-flex">{{item.pay_name}}</div>
                        <div class="left-icon"><label><i class="iconfont icon-gou"/></label></div>
                    </div>
                </van-radio>
            </van-radio-group>
        </van-popup>
        <CommonNav :routerName="routerName">
	      	<li slot="aloneNav">
			<router-link :to="{name: 'crowdfunding'}">
					<i class="iconfont icon-shequ2"></i>
					<p>{{$t('lang.square')}}</p>
				</router-link>
			</li>
			<li slot="aloneNav">
				<router-link :to="{name: 'crowdfunding-user'}">
					<i class="iconfont icon-gerenzhongxin"></i>
					<p>{{$t('lang.centre')}}</p>
				</router-link>
			</li>
	    </CommonNav>
  </div>
</template>

<script>
import { mapState} from 'vuex'
import {
    Cell,
    CellGroup,
    SubmitBar,
    GoodsAction,
    GoodsActionBigBtn,
    GoodsActionMiniBtn,
    Field,
    Popup,
    RadioGroup, 
    Radio,
    Switch,
    Toast
} from 'vant'

import CommonNav from '@/components/CommonNav' 

export default{
    data() {
        return {
            routerName:'crowd_funding',
            pay_name:'',
            cur_id:1,   
            value:'',
            radio:1,
            apart:'apart',
            showBase: false,
            use_surplus_val:0,
            pay_id: null,
        }
    },
	components:{
        CommonNav,
        [Cell.name] : Cell,
        [CellGroup.name] : CellGroup,
        [SubmitBar.name] : SubmitBar,
        [GoodsAction.name] : GoodsAction,
        [GoodsActionBigBtn.name] : GoodsActionBigBtn,
        [GoodsActionMiniBtn.name] : GoodsActionMiniBtn,
        [Field.name] : Field,
        [Popup.name]: Popup,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Switch.name]: Switch,
        [Toast.name]: Toast
    },
    //初始化加载数据
    created() {
        this.checkoutDefault()
    },
    computed: {
        ...mapState({
            crowdCheckoutData: state => state.crowdfunding.crowdCheckoutData,
        }),
        consignee_title(){
            if(this.crowdCheckoutData.default_address){
                return this.crowdCheckoutData.default_address.consignee +' '+ this.crowdCheckoutData.default_address.mobile
            }else{
                return ''
            }
        },
        consignee_address(){
            if(this.crowdCheckoutData.default_address){
                return this.crowdCheckoutData.default_address.province+this.crowdCheckoutData.default_address.city+this.crowdCheckoutData.default_address.district+this.crowdCheckoutData.default_address.address
            }else{
                return ''
            }
        },
        surplusSelf: {
            get() {
                return this.use_surplus_val == 0 ? false : true
            },
            set(val) {
                this.use_surplus_val = val == true ? 1 : 0
            }
        },
        cart_goods(){
            return this.crowdCheckoutData.cart_goods
        },
        order(){
            return this.crowdCheckoutData.order
        },
        total(){
            return this.crowdCheckoutData.total
        },
        amountTotal(){
            return this.crowdCheckoutData.total.amount * 100
        },
        payment_method() {
            return this.crowdCheckoutData.payment_list ? this.crowdCheckoutData.payment_list : []
        },
        shipping_fee(){
            return this.total.shipping_fee != 0 ? this.total.shipping_fee : this.$t('lang.free_shipping')
        }
    },
    methods:{
        checkoutDefault(){
            this.$store.dispatch('setCrowdfundingCheckout',{
                pid: this.$route.query.pid,
                id:this.$route.query.id,
                number: this.$route.query.number
            })
        },
        crowsdPay() {
            //属性弹框
            this.showBase = true
        },
        closeSku() {
            //关闭属性弹框
            this.showBase = false;
        },
        payment_method_select(id, name) {
            this.pay_id = id
            this.pay_name = name
        },
        checkoutAddress() {
            let value = {
                routerLink: 'crowdfunding-checkout'
            }

            if (this.$route.query) {
                value = {
                    routerLink: 'crowdfunding-checkout',
                    pid: this.$route.query.pid,
                    id: this.$route.query.id,
                    number:this.$route.query.number
                }
            }

            this.$router.push({
                name: 'address',
                query: value
            })
        },
        onSubmit(){
            this.$store.dispatch('setCrowdfundingDone',{
                pid: this.$route.query.pid,
                id:this.$route.query.id,
                number: this.$route.query.number,
                pay_id:this.pay_id,
                is_surplus: this.use_surplus_val,
            }).then(res=>{
                if(res.data.error == 1){
                    Toast(res.data.msg)
                }else{
                    this.$router.push({
                        name: 'done',
                        query: {
                            order_sn: res.data
                        }
                    })
                }
            })
        },
    },
    watch:{
        crowdCheckoutData(){
            //默认选中在线支付
            if (this.pay_name == '' && this.crowdCheckoutData.error != 'address') {
                this.payment_method.forEach(v => {
                    if (v.pay_code == 'onlinepay') {
                        this.pay_name = v.pay_name
                        this.pay_id = v.pay_id
                    }
                })
            }

            if (this.crowdCheckoutData.error == 'address') {
                this.$router.push({
                    name: 'addAddressForm',
                    query: {
                        routerLink: 'crowdfunding-checkout',
                        entrance:'first',
                        pid: this.$route.query.pid,
                        id:this.$route.query.id,
                        number: this.$route.query.number
                    }
                })
            }
        },
        payment_method() {
            if (this.payment_method == '') {
                Toast(this.$t('lang.payment_method_not_installed'))
                return false
            }
        },
    }
}
</script>
