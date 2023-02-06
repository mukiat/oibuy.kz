<template>
    <div class="con con_main">
        <div class="inv-form">
            <van-cell-group>
                <van-panel :title="$t('lang.vat_invoice_info')" class="my-bottom"/>
                <van-field :label="$t('lang.label_company_name')" v-model="company_name" :placeholder="$t('lang.enter_company_name')" :disabled="disabled"></van-field>
                <van-field :label="$t('lang.taxpayer_id_number')" v-model="tax_id" type="text" :placeholder="$t('lang.enter_taxpayer_id_number')" :disabled="disabled"></van-field>
                <van-field :label="$t('lang.register_address')" v-model="company_address" :placeholder="$t('lang.enter_register_address')" :disabled="disabled"></van-field>
                <van-field :label="$t('lang.register_tel')" v-model="company_telephone" type="tel" :placeholder="$t('lang.enter_register_tel')" :disabled="disabled"></van-field>
                <van-field :label="$t('lang.bank_of_deposit')" v-model="bank_of_deposit" :placeholder="$t('lang.enter_bank_of_deposit')" :disabled="disabled"></van-field>
                <van-field :label="$t('lang.bank_account')" v-model="bank_account" type="tel" :placeholder="$t('lang.enter_bank_account')" :disabled="disabled"></van-field>
            </van-cell-group>
            <van-cell-group class="m-top10">
                <van-panel :title="$t('lang.bill_consignee_info')" class="my-bottom"/>
                <van-field :label="$t('lang.label_name')" v-model="consignee_name" :placeholder="$t('lang.enter_name')" :disabled="disabled"></van-field>
                <van-field :label="$t('lang.label_mobile')" v-model="consignee_mobile_phone" type="tel" maxlength="11" :placeholder="$t('lang.enter_check_taker_mobile')"
                           :disabled="disabled"></van-field>

                <van-cell :title="$t('lang.label_region')" @click="handelRegionShow" class="van-cell-field" is-link>
                    <template v-if="regionOptionDate.regionSplic">
                        {{ regionOptionDate.regionSplic }}
                    </template>
                    <template v-else>
                        {{$t('lang.please_select_district')}}
                    </template>
                </van-cell>

                <van-field :label="$t('lang.label_address')" v-model="consignee_address" :placeholder="$t('lang.enter_address')" :disabled="disabled"></van-field>
                <van-cell :title="$t('lang.label_audit_status')" class="van-cell-field" v-if="id > 0">
                    <span v-if="audit_status == 0" class="color-red">{{$t('lang.audit_status_01')}}</span>
                    <span v-else-if="audit_status == 1" class="color-red">{{$t('lang.audit_status_02')}}</span>
                    <span v-else-if="audit_status == 2" class="color-red">{{$t('lang.audit_status_03')}}</span>
                </van-cell>
            </van-cell-group>
            <div class="filter-btn">
                <template v-if="id > 0">
                    <div class="dis-box">
                        <van-button type="default" class="box-flex" bottom-action @click="deleteInv">{{$t('lang.delete')}}</van-button>
                        <van-button type="primary" class="box-flex" bottom-action @click="editInv" v-if="disabled == true">{{$t('lang.mod')}}</van-button>
                        <van-button type="primary" class="box-flex" bottom-action @click="submitInv" v-else>{{$t('lang.save')}}</van-button>
                    </div>
                </template>
                <template v-else>
                    <van-button type="primary" bottom-action @click="addInv">{{$t('lang.add')}}</van-button>
                </template>
            </div>
            <Region :display="regionShow" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>
            <CommonNav></CommonNav>
        </div>
    </div>
</template>

<script>
    import qs from 'qs'
    import {
        Cell,
        CellGroup,
        Field,
        Panel,
        Button,
        Toast
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import formProcessing from '@/mixins/form-processing'

    export default {
        mixins: [formProcessing],
        data() {
            return {
                disabled: true,
                id: '',
                audit_status: '',
                company_name: '',
                tax_id: '',
                company_address: '',
                company_telephone: '',
                bank_of_deposit: '',
                bank_account: '',
                consignee_name: '',
                consignee_mobile_phone: '',
                consignee_address: ''
            }
        },
        components: {
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Field.name]: Field,
            [Panel.name]: Panel,
            [Button.name]: Button,
            [Toast.name]: Toast,
            CommonNav
        },
        created() {
            this.$http.get(`${window.ROOT_URL}api/invoice`).then(res => {
                let data = res.data.data.user_vat_invoice
                if (data != '') {
                    this.id = data.id
                    this.audit_status = data.audit_status
                    this.company_name = data.company_name
                    this.tax_id = data.tax_id
                    this.company_address = data.company_address
                    this.company_telephone = data.company_telephone
                    this.bank_of_deposit = data.bank_of_deposit
                    this.bank_account = data.bank_account
                    this.consignee_name = data.consignee_name
                    this.consignee_mobile_phone = data.consignee_mobile_phone
                    this.consignee_address = data.consignee_address
                    this.regionOptionDate.province.id = data.province
                    this.regionOptionDate.city.id = data.city
                    this.regionOptionDate.district.id = data.district
                    this.regionOptionDate.street.id = data.street
                    this.regionOptionDate.regionSplic = data.region
                } else {
                    this.disabled = false
                }
            })
        },
        methods: {
            handelRegionShow() {
                this.regionShow = this.regionShow ? false : true
                this.$store.dispatch('setRegion', {
                    region: 1,
                    level: 1
                })
            },
            addInv() {
                let rule = /^(\d{10})$/
                let o = {
                    id: this.id,
                    company_name: this.company_name,
                    tax_id: this.tax_id,
                    company_address: this.company_address,
                    company_telephone: this.company_telephone,
                    bank_of_deposit: this.bank_of_deposit,
                    bank_account: this.bank_account,
                    consignee_name: this.consignee_name,
                    consignee_mobile_phone: this.consignee_mobile_phone,
                    consignee_address: this.consignee_address,
                    country: 1,
                    province: this.regionOptionDate.province.id,
                    city: this.regionOptionDate.city.id,
                    district: this.regionOptionDate.district.id,
                    street: this.regionOptionDate.street.id,
                    invoice_type:1
                }

                if(this.company_name == ''){
                    Toast(this.$t('lang.fill_in_company_name'))
                    return false
                }else if(this.tax_id == ''){
                    Toast(this.$t('lang.fill_in_taxpayer_id_number'))
                    return false
                }else if(this.company_address == ''){
                    Toast(this.$t('lang.fill_in_register_address'))
                    return false
                }else if(this.company_telephone == ''){
                    Toast(this.$t('lang.fill_in_register_tel'))
                    return false
                }else if(this.bank_of_deposit == ''){
                    Toast(this.$t('lang.fill_in_bank_of_deposit'))
                    return false
                }else if(this.consignee_name == ''){
                    Toast(this.$t('lang.fill_in_check_taker_name'))
                    return false
                }else if(!rule.test(this.consignee_mobile_phone)){
                    Toast(this.$t('lang.fill_in_check_taker_mobile'))
                    return false
                }else if(this.consignee_address == ''){
                    Toast(this.$t('lang.fill_in_address'))
                    return false
                }else if(this.province == 0){
                    Toast(this.$t('lang.fill_in_province'))
                    return false
                }else if(this.city == 0){
                    Toast(this.$t('lang.fill_in_city'))
                    return false
                }else if(this.district == 0){
                    Toast(this.$t('lang.fill_in_county'))
                    return false
                }

                this.$http.post(`${window.ROOT_URL}api/invoice/store`, qs.stringify(o)).then(res => {
                    if (res.data.status == 'success') {
                        Toast.loading({
                            duration: 1000,
                            message: this.$t('lang.add_vat_invoice_success')
                        }, this.$router.push({name: 'account'}))
                    } else {
                        Toast(this.$t('lang.add_vat_invoice_fail'))
                    }
                })
            },
            editInv() {
                this.disabled = false
            },
            submitInv() {
                let o = {
                    id: this.id,
                    company_name: this.company_name,
                    tax_id: this.tax_id,
                    company_address: this.company_address,
                    company_telephone: this.company_telephone,
                    bank_of_deposit: this.bank_of_deposit,
                    bank_account: this.bank_account,
                    consignee_name: this.consignee_name,
                    consignee_mobile_phone: this.consignee_mobile_phone,
                    consignee_address: this.consignee_address,
                    province: this.regionOptionDate.province.id,
                    city: this.regionOptionDate.city.id,
                    district: this.regionOptionDate.district.id,
                    street: this.regionOptionDate.street.id,
                    invoice_type:1
                }

                this.$http.put(`${window.ROOT_URL}api/invoice/update`, qs.stringify(o)).then(res => {
                    if (res.data.status == 'success') {
                        Toast.loading({
                            duration: 1000,
                            message: this.$t('lang.edit_submit_success')
                        }, this.$router.push({name: 'account'}))
                    } else {
                        Toast(this.$t('lang.edit_fail_fill_again'))
                    }
                })
            },
            deleteInv() {
                let o = {
                    invoice_type:1,
                    id: this.id
                }

                this.$http.delete(`${window.ROOT_URL}api/invoice/destroy`, {params: o}).then(res => {
                    if (res.data.status == 'success') {
                        Toast.loading({
                            duration: 1000,
                            message: this.$t('lang.delete_success')
                        }, this.$router.push({name: 'account'}))
                    } else {
                        Toast(this.$t('lang.delete_fail'))
                    }
                })
            }
        }
    }
</script>
