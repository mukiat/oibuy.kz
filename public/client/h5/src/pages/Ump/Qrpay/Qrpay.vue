<template>
    <div class="paycode" v-if="qrcode">
        <van-row class="text-center m-top20">
            <div class="m-top10">
                <div class="icon"><i class="iconfont icon-xinshoulibao color-red"/></div>
                <span class="f-04" v-if="qrcode.type == '0'">{{ seller }}</span>
                <span class="m-top20 price-title" v-else>{{currency}}{{ qrcode.amount }}</span>
            </div>
        </van-row>
        <van-row class="m-top20 f-05" v-if="qrcode.type == '1'">
            <van-cell-group>
                <van-cell :title="$t('lang.label_collection_reason')">{{ qrcode.qrpay_name }}</van-cell>
            </van-cell-group>
        </van-row>
        <van-row class="m-top20" v-else>
            <van-cell-group>
                <van-cell :title="$t('lang.payment_amount')" icon="edit"></van-cell>
                <currency-input v-model="qrcode.amount" type="tel"></currency-input>
            </van-cell-group>
        </van-row>
        <van-row class="m-top20">
            <van-col offset="2" span="20">
                <van-button type="primary" block @click="doPay">{{$t('lang.immediate_pay')}}</van-button>
            </van-col>
        </van-row>
    </div>
</template>
<script>
    import {mapState} from 'vuex'
    import currencyInput from '@/components/Currency'
    import qs from 'qs'
    import axios from 'axios'

    import isApp from '@/mixins/is-app'

    import {
        Cell,
        CellGroup,
        Row,
        Col,
        Toast,
        Button
    } from 'vant'

    export default {
        name: "qrpay",
        minxins:[isApp],
        components: {
            'currency-input': currencyInput,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Row.name]: Row,
            [Col.name]: Col,
            [Button.name]: Button,
        },
        data() {
            return {
				currency:'￥',
                seller: '',
                locked: false,
                qrpay_id: 0,
                isGray: true,
                isReadonly: false,
                qrcode: ''
            };
        },
        //初始化加载数据
        created() {
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if(configData){
			  this.currency = configData.currency_format.replace('%s', '');
			}
			
            this.qrpay_id = this.$route.query.id || 0;

            // 微信内需要先登录
            if(isApp.isWeixinBrowser()) {
                if (this.isLogin) {
                    this.getQrcodeDetail(this.qrpay_id)
                } else {
                    // 登录后返回
                    this.$router.push({
                        path: '/login',
                        query:{ redirect: {name:'qrpay', params:{ id:this.qrpay_id }}}
                    })
                }
            } else {
                this.getQrcodeDetail(this.qrpay_id)
            }
        },
        computed: {
            isLogin(){
                return localStorage.getItem('token') == null ? false : true
            }
        },
        methods: {
            doPay() {
                let that = this
                let amount = parseFloat(that.qrcode.amount)
                if (isNaN(amount) || amount == 0) {
                    Toast({
                        message: this.$t('lang.enter_pay_in_balance'),
                        forbidClick: true
                    })
                    return;
                }

                if (that.locked == false) {
                    that.locked = true

                    axios.post(`${window.ROOT_URL}api/qrpay/pay`, qs.stringify({
                        id: that.qrpay_id,
                        amount: that.qrcode.amount,
                        _ajax: 1
                    })).then(function (response) {
                        that.locked = false
                        let res = response.data
                        if (res.error > 0) {
                            alert(res.message)
                        }
                        // just do it
                        if (res.data.paycode == 'alipay') {
                            window.location.href = res.data.payment
                        } else if (res.data.paycode == 'wxpay') {
                            that.callpay(JSON.parse(res.data.payment))
                        }
                    }).catch(function (error) {
                        console.log(error);
                    });

                }
            },
            getQrcodeDetail(id) {
                let that = this
                axios.get(`${window.ROOT_URL}api/qrpay?id=` + parseInt(id), {
                    params: {_ajax: 1}
                }).then(function (response) {
                    let res = response.data
                    if (res.error > 0) {
                        alert(res.message)
                    }
                    that.seller = res.data.seller;
                    that.qrcode = res.data.qrcode;

                    //设置title
                    document.title = that.qrcode.qrpay_name;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            jsApiCall(ret) {
                let that = this
                WeixinJSBridge.invoke("getBrandWCPayRequest", ret, function (res) {
                    if (res.err_msg == "get_brand_wcpay_request:ok") {
                        window.location.href = window.ROOT_URL + '/mobile#/respond?type=qrpay&status=1&id=' + that.qrpay_id;
                    } else {
                        window.location.href = window.ROOT_URL + '/mobile#/respond?type=qrpay&status=0&id=' + that.qrpay_id;
                    }
                })
            },
            callpay(ret) {
                if (typeof WeixinJSBridge == "undefined") {
                    if (document.addEventListener) {
                        document.addEventListener("WeixinJSBridgeReady", this.jsApiCall(ret), false);
                    } else if (document.attachEvent) {
                        document.attachEvent("WeixinJSBridgeReady", this.jsApiCall(ret));
                        document.attachEvent("onWeixinJSBridgeReady", this.jsApiCall(ret));
                    }
                } else {
                    this.jsApiCall(ret);
                }
            }
        },

    };
</script>
<style scoped>
    .van-button--primary {
        color: #fff;
        background-color: #4b0 !important;
        border: 1px solid #4b0 !important;
    }

    .paycode .icon i {
        margin-top: 5rem;
        font-size: 5rem;
        padding: 1rem;
        display: block;
    }

    .m-top20 {
        margin-top: 2.5rem
    }

    .paycode .price-title {
        font-size: 3rem
    }
</style>
