<template>
    <div class="con con_main">
        <div class="flow-checkout">
            <section class="flow-checkout-item" v-if="order">
                <van-cell-group class="van-cell-noleft">
                    <van-cell title="Чек алмасу" class="van-cell-title b-min b-min-b"></van-cell>
                    <van-cell title="Заказ：" v-model="order.order_sn"></van-cell>
                    <van-cell title="Типі：" v-model="inv_type"></van-cell>
                    <van-cell title="Мазмұны：" v-model="inv_content"></van-cell>
                    <van-cell title="Тақырып：">
                        <van-radio-group class="radio-list" v-model="inv_company" :disabled="radio_status">
                            <van-radio name="0" class="radio-item">Жеке</van-radio>
                            <van-radio name="1" class="radio-item">Заңды</van-radio>
                        </van-radio-group>
                    </van-cell>
                    <!--<van-cell title="Компания：" v-model="inv_name" v-if="edit_status == 1"></van-cell>-->
                    <van-cell title="Компания：" v-if="inv_company == 1">
                        <van-field v-model="inv_name" placeholder="Компания атын енгізіңіз" class="van-cell-ptb0"/>
                    </van-cell>
                    <van-cell title="БСН：" v-if="inv_company == 1">
                        <van-field v-model="inv_tax_sn" placeholder="БСН енгізіңіз" class="van-cell-ptb0"/>
                    </van-cell>
                    <van-cell title="Телефон：">
                        <van-field v-model="mobile" placeholder="Телефон нөмір енгізіңіз" class="van-cell-ptb0"/>
                    </van-cell>
                    <van-cell title="E-mail：">
                        <van-field v-model="email" placeholder="Эл.пошта енгізіңіз" class="van-cell-ptb0"/>
                    </van-cell>
                </van-cell-group>
            </section>
            <section>
                <div class="van-submit-bar van-order-submit-bar">
                    <div class="van-submit-bar__bar">
                        <van-goods-action-big-btn text="Жолданған" v-if="apply_status >= 0"/>
                        <van-goods-action-big-btn text="Жолдау" primary @click="onSubmit" v-else/>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    import {
        Radio,
        RadioGroup,
        Cell,
        CellGroup,
        SubmitBar,
        GoodsAction,
        GoodsActionBigBtn,
        GoodsActionMiniBtn,
        Field,
        Toast
    } from 'vant'

    export default{
        data(){
            return {
                inv_type: 'Эл.фактура',
                inv_tid: '',
                inv_content: 'Деталы',
                inv_company: 0,
                inv_tax_sn: '',
                inv_name: '',
                mobile: '',
                email: '',
                order_id: this.$route.query.order_id
            }
        },
        components: {
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [SubmitBar.name]: SubmitBar,
            [GoodsAction.name]: GoodsAction,
            [GoodsActionBigBtn.name]: GoodsActionBigBtn,
            [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Field.name]: Field,
            [Toast.name]: Toast,
        },
        created(){
            this.onLoad()
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
            onLoad(){
                this.$store.dispatch('setInvoiceReapplyInfo', {
                    order_id: this.order_id
                })
            },
            onSubmit(){
                this.$store.dispatch('setInvoiceReapply', {
                    order_id: this.order_id,
                    business_type: this.inv_company,
                    payer_name: this.inv_company == 1 ? this.inv_name : 'Жеке',
                    payer_register_no: this.inv_tax_sn,
                    mobile: this.mobile,
                    email: this.email
                }).then(res => {
                    Toast(res.data.msg)
                    this.$router.push({
                        name: 'invoiceDetail',
                        params: {
                            order_id: this.order_id
                        }
                    })
                })
            }
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
    .radio-list .radio-item {
        float: left;
        margin-right: 1rem;
    }
</style>