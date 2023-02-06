<template>
    <div>
        <!-- <van-cell-group>
            <van-field
                    v-model="mobile"
                    required
                    clearable
                    type="tel"
                    maxlength="11"
                    :label="$t('lang.phone_number')"
                    :placeholder="$t('lang.enter_mobile')"
            />
            <van-field v-model="imgverifyValue" type="tel" required center clearable :label="$t('lang.pic_code')" maxlength="4" :placeholder="$t('lang.captcha_img')" v-if="captcha">
                <span class="code-box" slot="button" @click="clickCaptcha"><img :src="captcha"></span>
            </van-field>
            <van-field
                    v-model="code"
                    required
                    center
                    clearable
                    type="tel"
                    :label="$t('lang.sms_code')"
                    :placeholder="$t('lang.get_sms_code')"
                    maxlength="6"
            >
                <template v-if="button_type">
                    <van-button slot="button" size="small" type="primary" @click="sendVerifyCode">{{$t('lang.send_sms_code')}}</van-button>
                </template>
                <template v-else><van-button slot="button" size="small">{{ button_text }}</van-button></template>
            </van-field>
        </van-cell-group>

        <van-row style="margin: 15px 0;" type="flex" justify="center">
            <van-col span="22">
                <van-button type="primary" size="large" @click="register">{{$t('lang.confirm')}}</van-button>
            </van-col>
        </van-row> -->
    </div>
</template>

<script>
    import {Row, Col, Field, CellGroup, Button, Toast, Dialog, Popup} from 'vant'
    import { Base64 } from 'js-base64'
    window.API_URL = window.API_URL || '/api'

    export default {
        name: 'callback',
        data() {
            return {
                mobile: '', // 手机号码
                code: '', // 短信验证码
                token: '', // 用户token
                isLoading: false,
                imgverifyValue: '', // 验证码
		        type: '', // 社会化登录类型
                unionid: '', // 用户unionid
                button_text:this.$t('lang.send_again_60'),
                send_again:this.$t('lang.send_again'),
                button_type:true,
                target_url:''
            }
        },
        components: {
            [Row.name]: Row,
            [Col.name]: Col,
            [Button.name]: Button,
            [CellGroup.name]: CellGroup,
            [Field.name]: Field,
            [Popup.name]: Popup,
        },
        computed:{
            captcha() {
                return this.$store.state.imgVerify.captcha
            },
            client() {
                return this.$store.state.imgVerify.client
            }
        },
        created() {
            let self = this
            self.isLoading = true
            self.fetchData()

            self.$store.dispatch('setImgVerify')
        },
        methods: {
            fetchData() {
                let self = this
                let params = self.$route.query
                if(!params.parent_id){
                    let parent_id = localStorage.getItem('parent_id') ? localStorage.getItem('parent_id') : null
                    if(parent_id !== null){
                        eval("params.parent_id" + "='" + parent_id + "'")
                    }
                }

                self.$store.dispatch('userCallback', params).then(res=>{
                    if(res.status === 'failed'){
                        Toast.fail({
                            duration:1000,
                            message: self.$t('lang.authorization_fail_notic')
                        })

                        setTimeout(()=>{
                            self.$router.push({name: 'login'})
                        },1000)
                    }else{
                        if(res.data.login == 1){
                            self.target_url = Base64.decode(res.data.url);
                            localStorage.setItem('token', res.data.token);
                            window.location.href = self.target_url;
                        }else{
                            let o = {
                                type:res.data.type,
                                unionid:res.data.unionid,
                                platform:'',
                                target_url:res.data.url,
                                parent_id:params.parent_id ? params.parent_id : 0
                            }
                            //是否去绑定会员
                            if(res.data.code == 42201){
                                Dialog.confirm({
                                    message: res.data.msg,
                                    className: 'text-center',
                                    cancelButtonText:'跳过',
                                    confirmButtonText:'去绑定'
                                }).then(() => {
                                    self.$router.push({
                                        name: 'binduser',
                                        query: {
                                            params:JSON.stringify(o)
                                        }
                                    })
                                }).catch(() => {
                                    //跳过重新请求callback
                                    o.step = 0;
                                    self.$store.dispatch('bindRegister',o).then(catchRes=>{
                                        if(catchRes.status === 'failed'){
                                            Toast.fail({
                                                duration:1000,
                                                message: self.$t('lang.authorization_fail_notic')
                                            })

                                            setTimeout(()=>{
                                                self.$router.push({name: 'login'})
                                            },1000)
                                        }else{
                                            self.target_url = Base64.decode(catchRes.data.url);
                                            localStorage.setItem('token', catchRes.data.token);
                                            window.location.href = self.target_url;
                                        }
                                    })
                                })
                            }
                        }
                    }

                    self.isLoading = false
                })
            },
            sendVerifyCode(){
                let self = this
                let o = {
                    captcha: self.imgverifyValue,
                    client: self.client,
                    mobile: self.mobile
                }

                self.$store.dispatch('setSendVerify', o).then(res => {
                    if (res == 'success') {
                        self.button_type = false
                        let second = 60
                        const timer = setInterval(() => {
                            second--
                            if (second) {
                                self.button_text = self.send_again + '('+ second +'s)'
                            } else {
                                this.button_type = true
                                clearInterval(timer);
                            }
                        }, 1000)
                    }else{
                        Toast.fail({
                            duration:1000,
                            message: res.errors.message
                        })
                        self.button_type = true
                        self.clickCaptcha()
                        return false
                    }
                })
            },
            register() {
                let self = this
                let parent_id = localStorage.getItem('parent_id') ? localStorage.getItem('parent_id') : this.$route.query.parent_id ? this.$route.query.parent_id : null

                let o = {
                    client: self.client,
                    mobile: self.mobile,
                    code: self.code,
                    type: self.type,
                    unionid: self.unionid,
                    parent_id:parent_id
                }

                if(!self.checkMobile()){
                    Toast(self.$t('lang.phone_number_format'))
                    return false
                }

                if(self.client == ''){
                    Toast(self.$t('lang.captcha_img'))
                    return false
                }

                if(self.code == ''){
                    Toast(self.$t('lang.get_sms_code_notic'))
                    return false
                }

                self.$store.dispatch('userRegister', o).then((res)=>{
                    if(res.status == 'success'){
                        self.$store.dispatch('userFastLogin', {
                            token: res.data,
                            status: res.status
                        });
                        window.location.href = self.target_url
                    }else{
                        Toast(res.errors.message)
                    }
                })
            },
            clickCaptcha() {
                this.$store.dispatch('setImgVerify')
            },
            checkMobile() {
                let rule = /^(\d{10})$/
                if (rule.test(this.mobile)) {
                    return true
                } else {
                    return false
                }
            }
        },
        watch: {
            'isLoading': function () {
                let self = this
                if (self.isLoading) {
                    Toast.loading({
                        mask: true,
                        message: self.$t('lang.loading')
                    })
                } else {
                    Toast.clear()
                }
            }
        }
    }
</script>
<style>
.verify .van-cell__value{ border:1px solid #dddddd; }
</style>
