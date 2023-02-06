<template>
	<div class="con con_main">
		<div class="flow-checkout" v-if="checkoutDisplay">
			<section class="flow-checkout-item flow-checkout-adr m-b10">
                <van-cell-group>
                    <van-cell :title="consignee_title" :label="consignee_address" icon="location" is-link
                              @click="checkoutAddress"></van-cell>
                </van-cell-group>
            </section>
            <section class="checkout-goods-list" v-for="(item,index) in checkoutInfo.cart_goods" :key="index">
                <section class="section-list">
                    <div class="detail-title">{{ item.shop_name }}</div>
                    <div class="product-list dis-box">
                        <ul class="box-flex">
                            <li v-for="(goodsItem,goodsIndex) in item.goods_list">
                                <div class="product-div">
                                    <div class="product-list-img">
                                        <img class="img" :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb"/>
                                        <img class="img" src="../../../../assets/img/no_image.jpg" v-else>
                                    </div>
                                    <div class="product-info">
                                        <h4>
                                            {{ goodsItem.goods_name }}
                                        </h4>
                                        <div class="price">
                                            <label v-html="goodsItem.total_price_formatted"></label>
                                            <span>x{{ goodsItem.total_number }}</span>
                                        </div>
                                        <div class="p-attr">{{ goodsItem.goods_attr }}</div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>
                <van-cell-group class="van-cell-noright m-top08">
                    <van-cell :title="$t('lang.buyer_message')" class="b-min b-min-t">
                        <div>
                            <van-field v-model="postscriptValue[index]" :placeholder="$t('lang.buyer_message_placeholder')" class="van-cell-ptb0"/>
                        </div>
                    </van-cell>
                </van-cell-group>
            </section>
            <section class="checkout-goods-other">
                <van-cell-group class="van-cell-noright m-top08">
                    <van-cell :title="$t('lang.payment_mode')" v-model="pay_name" is-link @click="paymentSelect"/>
                    <van-cell :title="$t('lang.invoice_info')" class="b-min b-min-t" @click="invoiceSelect" is-link>
                        <div class="box-flex" solt="value">
                            <template v-if="!invoice.invoiceType">
                                <p class="receipt-title">
                                    <template v-if="!invoice.company">
                                        {{ invoice.invoiceTitle }}
                                    </template>
                                    <template v-else>{{ invoice.company_name }}</template>
                                </p>
                                <p class="receipt-name">{{ invoice.invoiceConent }}</p>
                            </template>
                            <template v-else>
                                <p class="receipt-title">{{$t('lang.vat_tax_invoice')}}</p>
                            </template>
                        </div>
                    </van-cell>
                </van-cell-group>
                <van-cell-group class="van-cell-noright m-top08 van-cell-total">
                    <van-cell :title="$t('lang.goods_together')" class="van-cell-title b-min b-min-b">
                        <div>
                            <div class="color-red"><span v-html="total.total_price_formatted"></span></div>
                        </div>
                    </van-cell>
                </van-cell-group>
                <van-cell-group class="van-cell-noright m-top08" v-if="total.use_surplus > 0">
                    <van-cell class="van-cell-title b-min b-min-b">
                        <div slot="title">{{$t('lang.is_use_balance')}}</div>
                        <van-switch v-model="surplusSelf" size="20px" class="fr"/>
                    </van-cell>
                </van-cell-group>
            </section>
            <section class="order-detail-submit order-checkout-submit">
                <van-submit-bar :price="amountPrice * 100" :label="label_text" :button-text="$t('lang.immediate_payment')" :currency="currency" @submit="onSubmit"/>
            </section>
		</div>
		<!--发票-->
        <van-popup v-model="showBase" position="right" :overlay="overlay" class="invoice_show">
            <section class="invoice-warp-box padding-all">
                <div class="header-tit">{{$t('lang.invoice_type')}}</div>
                <ul class="ect-selects">
                    <li class="ect-select" :class="{'active':invoice.invoiceType == false}" @click="incrementHandle(1)">
                        <span>{{$t('lang.plain_invoice')}}</span></li>
                    <template v-if="!invoice.increment">
                        <li class="ect-select btn-box" @click="incrementHandle(2)"><span>{{$t('lang.vat_invoice')}}</span></li>
                    </template>
                    <template v-else>
                        <li class="ect-select" :class="{'active':invoice.invoiceType == true}"
                            @click="incrementHandle(2)"><span>{{$t('lang.vat_invoice')}}</span></li>
                    </template>
                </ul>
                <p>{{$t('lang.checkout_inv_help')}}</p>
            </section>
            <section class="invoice-cont-box" v-if="!invoice.invoiceType">
                <ul class="ect-selects">
                    <li class="ect-select" :class="{'active':invoice.company == false}" @click="invTab(1)">
                        <span>{{$t('lang.person')}}</span></li>
                    <li class="ect-select" :class="{'active':invoice.company == true}" @click="invTab(2)">
                        <span>{{$t('lang.company')}}</span></li>
                </ul>
                <div class="invoice-cont-1" v-if="invoice.company">
                    <van-field :title="$t('lang.label_company_name')" class="my-cell-nobottom" v-model="invoice.company_name" :placeholder="$t('lang.fill_in_company_name')"/>
                    <van-field :label="$t('lang.taxpayer_id_number')" :placeholder="$t('lang.enter_taxpayer_id_number')" v-model="invoice.company_tid"/>
                </div>
                <div class="invoice-cont-2">
                    <div class="header-tit">{{$t('lang.invoice_content')}}</div>
                    <van-radio-group v-model="invoice.invValueRadio" @change="invValueRadioHandle">
                        <van-radio :name="item.id" v-for="(item,index) in invoice.invoiceValue" :key="index">
                            {{item.name}}
                        </van-radio>
                    </van-radio-group>
                </div>
            </section>
            <div class="my-box">
                <div class="ect-button-more">
                    <div class="btn btn-submit" @click="submitBtn">{{$t('lang.confirm')}}</div>
                </div>
            </div>
        </van-popup>

        <!--支付方式-->
        <van-popup class="show-popup-bottom" v-model="paymentShow" position="bottom">
            <div class="goods-show-title padding-all">
                <h3 class="fl">{{$t('lang.payment_mode')}}</h3>
                <i class="iconfont icon-close fr" @click="paymentClose"></i>
            </div>
            <div class="s-g-list-con">
                <div class="select-two">
                    <ul>
                        <li class="ect-select" v-for="(item,index) in payment_method" :key="index"
                            :class="{'active':pay_id == item.pay_id}"
                            @click="payment_method_select(item.pay_id,item.pay_name)">
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

    import {mapState} from 'vuex'
    import {
        Cell,
        CellGroup,
        SubmitBar,
        GoodsAction,
        GoodsActionBigBtn,
        GoodsActionMiniBtn,
        Field,
        Dialog,
        Popup,
        RadioGroup,
        Radio,
        Toast,
        Switch,
    } from 'vant'

    import CommonNav from '@/components/CommonNav'

    export default {
        data() {
            return {
                checkoutDisplay: false,
            	postscriptValue: [],
            	showBase: false,
            	overlay: true,
                invoice: {
                    company: false,
                    company_name: '',
                    company_tid: '',
                    increment: false,
                    invoiceType: false,
                    invoiceTitle: this.$t('lang.person'),
                    invoiceConent: this.$t('lang.no_invoice'),
                    tax_id: 0,
                    invoiceValue: [
                        {
                            id: 1,
                            name: '不开发票'
                        }, {
                            id: 2,
                            name: '明细'
                        }, {
                            id: 3,
                            name: '办公用品'
                        }, {
                            id: 4,
                            name: '电脑耗材'
                        }, {
                            id: 5,
                            name: '耗材'
                        },
                    ],
                    invValueRadio: 1
                },
                paymentShow: false,
                pay_id: null,
                pay_name: '',
                use_surplus_val: 0,
                price: 0,
                rec_type: this.$route.query.rec_type,
                rec_id: JSON.parse(this.$route.query.rec_id),
                currency: '¥',
                label_text: this.$t('lang.label_total_amount_payable'),
            }
        },
        components: {
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [SubmitBar.name]: SubmitBar,
            [GoodsAction.name]: GoodsAction,
            [GoodsActionBigBtn.name]: GoodsActionBigBtn,
            [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
            [Field.name]: Field,
            [Dialog.name]: Dialog,
            [Popup.name]: Popup,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Toast.name]: Toast,
            [Switch.name]: Switch,
            CommonNav
        },
        created() {
            this.checkoutDefault()
        },
        computed: {
        	...mapState({
                checkoutInfo: state => state.other.supplierCheckoutInfo
            }),
            consignee_title() {
                if (this.checkoutInfo.consignee) {
                    return this.checkoutInfo.consignee.consignee + ' ' + this.checkoutInfo.consignee.mobile
                } else {
                    return ''
                }
            },
            consignee_address() {
                if (this.checkoutInfo.consignee) {
                    return this.checkoutInfo.consignee.province_name + this.checkoutInfo.consignee.city_name + this.checkoutInfo.consignee.district_name + this.checkoutInfo.consignee.address
                } else {
                    return ''
                }
            },
            total: {
                get() {
                    return this.checkoutInfo.total ? this.checkoutInfo.total : ''
                },
                set(val) {
                    console.log(val)
                }
            },
            amountPrice: {
                get() {
                    return this.total.total_price ? this.total.total_price : 0
                },
                set(val) {
                    this.price = val
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
            payment_method() {
                return this.checkoutInfo.payment_list ? this.checkoutInfo.payment_list : ''
            },
        	isLogin(){
		    	return localStorage.getItem('token') == null ? false : true
		    },
        	listShow() {
                return this.$store.state.shopping.listShow
            }
        },
        methods: {
            checkoutDefault() {
                if (this.isLogin) {
                    this.$store.dispatch('setSupplierCheckout',{
			        	rec_ids:this.rec_id
			      	})
                } else {
                    let msg = this.$t('lang.login_user_invalid')
                    this.notLogin(msg)
                }
            },
            checkoutAddress() {
                this.$router.push({
                    name: 'address',
                    query: {
                        routerLink: 'supplier-checkout',
                        rec_type: this.rec_type,
                        rec_id: JSON.stringify(this.rec_id),
                    }
                })
            },
            onSubmit() {
            	//发票
                let inv_payee = this.invoice.company ? this.invoice.company_name : this.invoice.invoiceTitle
                let tax_id = this.invoice.invoiceType ? this.invoice.tax_id : this.invoice.company_tid

            	this.$store.dispatch('setSupplierDone', {
            	 	done_cart_value:this.rec_id,
            	 	pay_id: this.pay_id,                                        //支付方式id
            	 	invoice: 0,
                    is_surplus: this.use_surplus_val,                           //是否使用余额
            	 	inv_type: this.invoice.invoiceType ? 1 : 0,                 //发票类型
                    inv_payee: inv_payee,                                       //发票类别、个人/公司
                    tax_id: tax_id ? tax_id : 0,                                //纳税人识别码或者增值税发票id
                    inv_content: this.invoice.invoiceConent,                    //发票内容
            	 	postscript: this.postscriptValue,                           //订单留言
                }).then(res=>{
            	 	Toast(res.msg)
            	 	if(res.code == 0){
            	 		this.$router.push({
            	 			name:'supplier-done',
            	 			query:{
            	 				order_sn:res.main_order_sn
            	 			}
            	 		});
            	 	}
            	})
            },
            notLogin(msg) {
                let url = window.location.href;
                Dialog.confirm({
                    message: msg,
                    className: 'text-center'
                }).then(() => {
                    this.$router.push({
                        name: 'login',
                        query: {
                            redirect: {
                                name: 'cart',
                                url:url
                            }
                        }
                    })
                }).catch(() => {

                })
            },
            invoiceSelect() {
                this.showBase = true
                this.$http.get(`${window.ROOT_URL}api/invoice`).then(res => {
                    if (res.data.data == '') {
                        this.invoice.increment = false
                    } else {
                        this.invoice.increment = true
                        this.invoice.tax_id = res.data.data.id
                    }
                })
            },
            invTab(val) {
                if (val == 1) {
                    this.invoice.company = false
                    this.invoice.company_name = ''
                    this.invoice.company_tid = ''
                } else {
                    this.invoice.company = true
                }
            },
            incrementHandle(val) {
                if (val == 1) {
                    this.invoice.invoiceType = false
                } else {
                    if (this.invoice.increment == true) {
                        this.invoice.invoiceType = true
                    } else {
                        Dialog.confirm({
                            message: this.$t('lang.apply_vat_tax_invoice'),
                            className: 'text-center'
                        }).then(() => {
                            this.$router.push({
                                name: 'invForm'
                            })
                        }).catch(() => {

                        })
                    }
                }
            },
            invValueRadioHandle() {
                this.invoice.invoiceValue.forEach((v) => {
                    if (v.id == this.invoice.invValueRadio) {
                        this.invoice.invoiceConent = v.name
                    }
                })
            },
            submitBtn() {
                if (this.invoice.company) {
                    if (this.invoice.company_name == '') {
                        Toast(this.$t('lang.fill_in_company_name'))
                        return false
                    } else if (this.invoice.company_tid == '') {
                        Toast(this.$t('lang.fill_in_taxpayer_id_number'))
                        return false
                    } else {
                        this.showBase = false
                    }
                } else {
                    this.showBase = false
                }
            },
            paymentSelect() {
                this.paymentShow = true
            },
            paymentClose() {
                this.paymentShow = false
            },
            payment_method_select(id, name) {
                this.pay_id = id
                this.pay_name = name
            },
            onListShow(index) {
                let cur = this.listShow[index] ? false : true
                this.listShow.splice(index, 1, cur)
            }
		},
		watch: {
			checkoutInfo(){
				if (this.checkoutInfo.cart_goods.length == 0) {
                    this.$router.push({
                        name: 'supplier-cart'
                    })

                    return false
                }

                //默认选中在线支付
                if (this.pay_name == '' && this.checkoutInfo.consignee.error != 'address') {
                    this.payment_method.forEach(v => {
                        if (v.pay_code == 'onlinepay') {
                            this.pay_name = v.pay_name
                            this.pay_id = v.pay_id
                        }
                    })
                }

                if (this.checkoutInfo.consignee.error == 'address') {
                    this.checkoutDisplay = false
                    this.$router.push({
                        name: 'addAddressForm',
                        query: {
                            routerLink: 'supplier-checkout',
                            rec_type: this.rec_type,
                            rec_id: JSON.stringify(this.rec_id),
                        }
                    })
                } else {
                    this.checkoutDisplay = true
                }
			}
		}
    }
</script>
