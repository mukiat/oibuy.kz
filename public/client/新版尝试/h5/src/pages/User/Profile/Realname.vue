<template>
    <div class="con bg-color-write">
        <van-cell-group>
            <van-cell is-link @click="uploadCard">
                <template slot="title">
                    <span class="van-cell-text">{{$t('lang.upload_photoid')}}</span>
                    <span v-if="!isCardImg">{{$t('lang.haven_uploaded')}}</span>
                    <span v-else>{{$t('lang.have_already_uploaded')}}</span>
                </template>
            </van-cell>

            <van-field type="text" :label="$t('lang.truename')" v-model="real_name" v-bind:disabled="isDisabled" :placeholder="$t('lang.fill_in_real_name')"></van-field>
            <van-field type="tel" :label="$t('lang.id_number')" v-model="self_num" v-bind:disabled="isDisabled" :placeholder="$t('lang.fill_in_id_number')"></van-field>
            <van-field type="text" :label="$t('lang.bank_name')" v-model="bank_name" v-bind:disabled="isDisabled" :placeholder="$t('lang.fill_in_bank_name')"></van-field>
            <van-field type="tel" :label="$t('lang.credit_card_numbers')" v-model="bank_card" v-bind:disabled="isDisabled" :placeholder="$t('lang.fill_in_card_number')"></van-field>
            <van-field type="text" :label="$t('lang.certification_time')" v-model="add_time" v-if="real_type" readonly></van-field>
            <van-cell v-if="review_status !== ''">
                <template slot="title">
                    <span class="van-cell-text">{{$t('lang.audit_status')}}</span>
                    <span v-if="review_status == 0" class="color-red">{{$t('lang.audit_status_01')}}</span>
                    <span v-else-if="review_status == 1" class="color-red">{{$t('lang.audit_status_02')}}</span>
                    <span v-else-if="review_status == 2" class="color-red">{{$t('lang.audit_status_03')}}</span>
                </template>
            </van-cell>
            <van-cell v-if="review_status == 2">
                <template slot="title">
                    <div style="display: flex;">
                        <span class="van-cell-text">{{$t('lang.audit_review_content')}}</span>
                        <span class="color-red">{{ review_content }}</span>
                    </div>
                </template>
            </van-cell>
            <van-field type="tel" :label="$t('lang.phone_number')" v-model="bank_mobile" maxlength="11" v-bind:disabled="isDisabled" v-if="button_edit_type"></van-field>
            <van-field type="tel" :label="$t('lang.phone_number')" v-model="bank_mobile" maxlength="11" v-bind:disabled="isDisabled" :placeholder="$t('lang.fill_in_mobile')" v-else></van-field>

            <template v-if="button_edit_type == false">
                <van-field v-model="imgverifyValue" center clearable :label="$t('lang.pic_code')" maxlength="4" :placeholder="$t('lang.enter_pic_code')" v-if="captcha">
                    <span class="code-box" slot="button" @click="clickCaptcha"><img :src="captcha"></span>
                </van-field>
                <van-field type="tel" v-model="sms" center clearable :label="$t('lang.sms_code')" :placeholder="$t('lang.get_sms_code')">
                    <van-button slot="button" size="small" type="primary" @click="sendVerifyCode" v-if="button_type">{{$t('lang.send_sms_code')}}
                    </van-button>
                    <van-button slot="button" size="small" type="disabled" v-else>{{ button_text }}</van-button>
                </van-field>
            </template>
        </van-cell-group>
        <div class="ect-button">
            <template v-if="real_id > 0">
                <van-button  type="primary" bottom-action  v-if="button_edit_type" @click="editRealname">{{$t('lang.edit_certification_info')}}</van-button>
                <van-button bottom-action  type="primary" @click="updateRealname" v-else>{{ button_edit }}</van-button>
            </template>
            <template v-else>
                <van-button type="primary" bottom-action @click="addRealname" :disabled="button_add_type">{{$t('lang.consent_certification_confirm')}}</van-button>
            </template>
        </div>
    </div>
</template>

<script>
    import qs from 'qs'

    import {
        Cell,
        CellGroup,
        Button,
        Popup,
        Field,
        Uploader,
        Icon,
        Toast
    } from 'vant'

    export default {
        data() {
            return {
                real_type: false,
                real_id: 0,
                real_name: '',
                self_num: '',
                bank_name: '',
                bank_card: '',
                review_content:'',
                front_of_id_card: 0,
                reverse_of_id_card: 0,
                add_time: '',
                review_status: '',
                bank_mobile: '',
                imgverifyValue: '',
                sms: '',
                button_text: this.$t('lang.send_again_60'),
                send_again:this.$t('lang.send_again'),
                button_type: true,
                button_edit_type : true,
                button_add_type: false,
                button_edit: this.$t('lang.subimt'),
                isDisabled:true,
            }
        },
        components: {
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Button.name]: Button,
            [Popup.name]: Popup,
            [Field.name]: Field,
            [Uploader.name]: Uploader,
            [Icon.name]: Icon,
            [Toast.name]: Toast,
        },
        created() {
            this.realnameLoad()
        },
        computed: {
            isCardImg() {
                return this.front_of_id_card != 0 && this.reverse_of_id_card != 0 ? true : false
            },
            captcha() {
                return this.$store.state.imgVerify.captcha
            },
            client() {
                return this.$store.state.imgVerify.client
            }
        },
        methods: {
            realnameLoad(val){
                this.$http.get(`${window.ROOT_URL}api/realname`).then(res => {
                    let data = res.data.data
                    if (data != '') {
                        this.real_type = true
                        this.real_id = data.real_id
                        this.real_name = data.real_name
                        this.self_num = data.self_num
                        this.bank_name = data.bank_name
                        this.bank_card = data.bank_card
                        this.bank_mobile = data.bank_mobile
                        this.front_of_id_card = this.$route.query.front_of_id_card != undefined ? this.$route.query.front_of_id_card : data.front_of_id_card
                        this.reverse_of_id_card = this.$route.query.front_of_id_card != undefined ? this.$route.query.reverse_of_id_card : data.reverse_of_id_card
                        this.review_status = data.review_status
                        this.add_time = data.add_time
                        this.review_content = data.review_content
                    }

                    if(data == '' && val != 'add'){
                        this.isDisabled = false
                        this.button_edit_type = false
                    }else{
                        this.isDisabled = true
                        this.button_edit_type = true
                    }
                })

                if (this.$route.query.front_of_id_card != undefined && this.$route.query.front_of_id_card != undefined) {
                    this.front_of_id_card = this.$route.query.front_of_id_card
                    this.reverse_of_id_card = this.$route.query.reverse_of_id_card
                }

                this.$store.dispatch('setImgVerify')
            },
            uploadCard() {
                if (this.button_edit_type == true) {
                    Toast(this.$t('lang.edit_state_photoid'))
                    return false
                }
                this.$router.push({name: 'uploadcard'})
            },
            addRealname() {
                let o = {
                    real_id: this.real_id,
                    real_name: this.real_name,
                    self_num: this.self_num,
                    bank_name: this.bank_name,
                    bank_card: this.bank_card,
                    bank_mobile: this.bank_mobile,
                    front_of_id_card: this.front_of_id_card,
                    reverse_of_id_card: this.reverse_of_id_card
                }

                if(this.verification()){
                    this.button_add_type = true
                    this.$store.dispatch('setSmsVerify', {
                        client: this.client,
                        code: this.sms,
                        mobile:this.bank_mobile
                    }).then(res => {
                        if (res.status == 'success') {
                            this.$http.post(`${window.ROOT_URL}api/realname/store`, qs.stringify(o)).then(sRes => {
                                if (sRes.data.status == 'success') {
                                    Toast.loading({
                                        duration: 1000,
                                        message: this.$t('lang.certification_submit_audit')
                                    })

                                    this.realnameLoad('add')
                                } else {
                                    Toast(sRes.data.errors.message)
                                }
                            })
                        }else{
                            Toast(res.errors.message)
                        }
                    })
                }
            },
            editRealname() {
                // 改变可修改
                this.isDisabled = false
                this.button_edit_type = false
            },
            updateRealname() {
                let o = {
                    real_id: this.real_id,
                    real_name: this.real_name,
                    self_num: this.self_num,
                    bank_name: this.bank_name,
                    bank_card: this.bank_card,
                    bank_mobile: this.bank_mobile,
                    front_of_id_card: this.front_of_id_card,
                    reverse_of_id_card: this.reverse_of_id_card
                }

                if(this.verification()){
                     this.$store.dispatch('setSmsVerify', {
                        client: this.client,
                        code: this.sms,
                        mobile:this.bank_mobile
                    }).then(res => {
                        if (res.status == 'success') {
                           this.$http.put(`${window.ROOT_URL}api/realname/update`, qs.stringify(o)).then(sRes => {
                                if (sRes.data.status == 'success') {
                                    Toast(this.$t('lang.edit_submit_audit'))
                                    this.button_edit_type = true
                                } else {
                                    Toast(sRes.data.errors.message)
                                }
                                
                                this.realnameLoad()
                            })
                        }else{
                            Toast(res.errors.message)
                        }
                    })
                }
            },
            clickCaptcha() {
                this.$store.dispatch('setImgVerify')
            },
            sendVerifyCode() {
                let o = {
                    captcha: this.imgverifyValue,
                    client: this.client,
                    mobile: this.bank_mobile
                }

                this.$store.dispatch('setSendVerify', o).then(res => {
                    if (res == 'success') {
                        this.button_type = false
                        let second = 60
                        const timer = setInterval(() => {
                            second--
                            if (second) {
                                this.button_text = this.send_again + '('+ second +'s)'
                            } else {
                                this.button_type = true
                                clearInterval(timer);
                            }
                        }, 1000)
                    }
                })
            },
            verification(){
                let fald = true
                let rule = /^(\d{10})$/

                if(!this.isCardImg){
                    Toast(this.$t('lang.please_upload_photoid'))
                    fald = false
                }else if(this.real_name == ''){
                    Toast(this.$t('lang.truename_not_null'))
                    fald = false
                }else if(this.self_num == ''){
                    Toast(this.$t('lang.id_number_not_null'))
                    fald = false
                }else if(this.bank_name == ''){
                    Toast(this.$t('lang.bank_name_not_null'))
                    fald = false
                }else if(this.bank_card == ''){
                    Toast(this.$t('lang.card_number_not_null'))
                    fald = false
                }else if(!rule.test(this.bank_mobile)){
                    Toast(this.$t('lang.mobile_not_null'))
                    fald = false
                }else if(this.imgverifyValue == ''){
                    Toast(this.$t('lang.captcha_img_not_null'))
                    fald = false
                }else if(this.sms == ''){
                    Toast(this.$t('lang.get_sms_code_notic'))
                    fald = false
                }else{
                    fald = true
                }

                return fald
            }
        }
    }
</script>
