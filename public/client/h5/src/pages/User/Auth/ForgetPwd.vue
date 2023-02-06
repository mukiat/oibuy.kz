<template>
    <div class="container container-register" :style="{'height': docmHeight2 + 'px'}">
        <div class="login-head"><i class="iconfont icon-back" @click="onClickBack"></i></div>
        <div class="login-form">
            <div class="title">{{type == 0 ? 'Аккаунт логиннін енгізіңіз' : type == 1 ? 'Кодты алу' : 'Жаңа пароль қою'}}</div>
            <template v-if="type == 0">
                <div class="input-box">
                    <div class="input-box__left">
                        <i class="iconfont icon-wodeguanzhu"></i>
                        <input type="text" class="input" v-model="username" autocomplete="off" placeholder="Логин/Телефон" />
                        <i class="iconfont icon-guanbi" @click="username = ''" v-show="username"></i>
                    </div>
                </div>
                <div class="input-box">
                    <div class="input-box__left">
                        <i class="iconfont icon-tupian"></i>
                        <input type="text" class="input" v-model="imgverifyValue" autocomplete="off" :placeholder="$t('lang.captcha_img')" />
                    </div>
                    <div class="input-box__right">
                        <img :src="captcha" class="j-verify-img" @click="clickCaptcha" />
                    </div>
                </div>
                <div class="signup-button">
                    <button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitStep(type)">СМС код алу</button>
                </div>
            </template>
            <template v-else-if="type == 1">
                <div class="forgetpwd-warp">
                    <div class="send-yzm">
                        <div class="text" :class="{'text-email':curType == 'email'}">{{ curType == 'mobile_phone' ? userinfo.mobile_phone_sign : userinfo.email_sign}}</div>
                        <div class="send" @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</div>
                        <div class="send" v-else>{{ button_text }}</div>
                    </div>
                    <van-password-input :value="sms" :mask="false" :gutter="10" info="6 орынды санды енгізіңіз" @focus="showKeyboard = true" />
                    <van-number-keyboard :show="showKeyboard" @input="onInput" @delete="onDelete" @blur="showKeyboard = false" @hide="onHide" />
                </div>
                <div class="signup-button">
                    <button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitStep(type)">Келесі</button>
                    <div class="tips">
                        <a class="go-register" @click="tabClick('email')" v-if="curType == 'mobile_phone' && userinfo.is_email == 1">E-mail растау</a>
                        <a class="go-register" @click="tabClick('mobile_phone')" v-if="curType == 'email' && userinfo.is_mobile_phone == 1">Телефон растау</a>
                    </div>
                </div>
            </template>
            <template v-else-if="type == 2">
                <div class="input-box">
                    <div class="input-box__left">
                        <i class="iconfont icon-jiesuo"></i>
                        <input :type="pwd" class="input" v-model="password" autocomplete="off" :placeholder="$t('lang.new_password_notic')" />
                    </div>
                    <div class="input-box__right">
                        <i class="iconfont icon-liulan1" :class="{'active':pwd == 'text'}" @click="handlePwdShow"></i>
                    </div>
                </div>
                <div class="signup-button">
                    <button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitBtn">Жаңа пароль қою</button>
                    <div class="tip">Аккаунт қауіпсіздігі үшін,пошта паролымен бірдей пароль қоймаңыз</div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { 
    Toast, 
    Loading, 
    Popup,
    PasswordInput,
    NumberKeyboard,
} from 'vant'

import isApp from '@/mixins/is-app'
import formProcessing from '@/mixins/form-processing'

import qs from 'qs'
export default{
    name:'forgetpwd',
    mixins:[isApp,formProcessing],
    components:{
        [Toast.name]: Toast,
        [Loading.name]: Loading,
        [Popup.name]: Popup,
        [PasswordInput.name]: PasswordInput,
        [NumberKeyboard.name]: NumberKeyboard,
    },
    data(){
        return{
            disabled:true,
            pwd:'password',
            password:'',
            username:'',
            imgverifyValue:'',
            sms:'',
            send_again:this.$t('lang.send_again'),
            button_text:this.$t('lang.send_again_60'),
            button_type:true,
            showKeyboard: true,
            type:0,
            userinfo:'',
            curType:'',
            docmHeight2:document.documentElement.clientHeight,
        }
    },
    computed:{
        captcha(){
            return this.$store.state.imgVerify.captcha
        },
        client(){
            return this.$store.state.imgVerify.client
        },
        isTypeValue(){
            var obj = ''

            if(this.userinfo.is_mobile_phone == 1 && this.userinfo.is_email == 1){
                obj = 'mobile_phone'
            }else{
                if(this.userinfo.is_mobile_phone == 1 || this.userinfo.is_email == 1){
                    if(this.userinfo.is_mobile_phone == 1){
                        obj = 'mobile_phone'
                    }
                    if(this.userinfo.is_email == 1){
                        obj = 'email'
                    }
                }else{
                    obj = ''
                }
            }

            return obj
        }
    },
    watch:{
        username(){
            this.disabled = this.username ? false : true;
        },
        isTypeValue(){
            this.curType = this.isTypeValue
        },
        password(){
            this.disabled = this.password ? false : true; 
        }
    },
    mounted(){
        let self = this

        if(this.type == 0) self.$store.dispatch('setImgVerify');
    },
    methods:{
        handlePwdShow(){
            this.pwd = this.pwd === 'password' ? 'text' : 'password'
        },
        clickCaptcha(){
            this.$store.dispatch('setImgVerify')
        },
        sendVerifyCode(){
            let that = this
            if(that.curType == 'mobile_phone'){
                let o = {
                    mobile:that.userinfo.mobile_phone,
                    send_from:'reset_password'
                }

                that.$store.dispatch('setSendVerify', o).then(res=>{
                    if(res == 'success'){
                        that.button_type = false
                        let second = 60
                        const timer = setInterval(()=>{
                            second --
                            if(second){
                                that.button_text = that.send_again + '('+ second +'s)'
                            }else{
                                that.button_type = true
                                clearInterval(timer)
                            }
                        },1000)
                    }
                })
            }else if(that.curType == 'email'){
                that.$http.post(`${window.ROOT_URL}api/user/reset_email`,qs.stringify({
                    email:that.userinfo.email
                })).then(({data})=>{
                    if(data.status == 'success'){
                        Toast(data.data.msg)

                        that.button_type = false
                        let second = 60
                        const timer = setInterval(()=>{
                            second --
                            if(second){
                                that.button_text = that.send_again + '('+ second +'s)'
                            }else{
                                that.button_type = true
                                second = 60
                                clearInterval(timer)
                            }
                        },1000)
                    }else{
                        Toast(data.errors.message)
                    }
                })
            }
        },
        checkMobile() {
            let rule = /^(\d{10})$/
            if (rule.test(this.mobile)) {
                return true
            } else {
                return false
            }
        },
        submitStep(type){
            if(this.type == 0){
                if(this.username == ''){
                    Toast('Логинды енгізіңіз')
                    return false
                }

                if(this.imgverifyValue == ''){
                    Toast(this.$t('lang.captcha_img'))
                    return false
                }

                this.$http.post(`${window.ROOT_URL}api/user/forget`,qs.stringify({
                    user_name:this.username,
                    captcha:this.imgverifyValue,
                    client:this.client
                })).then(({data})=>{
                    if(data.status == 'success'){
                        this.userinfo = data.data;
                        this.type = 1;
                        this.disabled = true;
                    }else{
                        Toast(data.errors.message)
                    }
                })
            }else if(this.type == 1){
                if(this.sms == ''){
                    Toast('Кодты енгізіңіз')
                    return false
                }
                if(this.curType == 'mobile_phone'){
                    this.$http.post(`${window.ROOT_URL}api/user/verification_sms`,qs.stringify({
                        mobile_phone:this.userinfo.mobile_phone,
                        code:this.sms
                    })).then(({data})=>{
                        if(data.status == 'success'){
                            this.type = 2;
                        }else{
                            Toast(data.errors.message)
                        }
                    })
                }else if(this.curType == 'email'){
                    this.$http.post(`${window.ROOT_URL}api/user/verification_email`,qs.stringify({
                        email:this.userinfo.email,
                        code:this.sms
                    })).then(({data})=>{
                        if(data.status == 'success'){
                            this.type = 2;
                        }else{
                            Toast(data.errors.message)
                        }
                    })
                }
            }
        },
        submitBtn(){
            if(!(/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,16}$/.test(this.password))){
                Toast("8-16 орынды ләтін әріп және сан араластырып қойыңыз");
                return
            }

            this.$http.post(`${window.ROOT_URL}api/user/reset_password`,qs.stringify({
                user_name:this.userinfo.user_name,
                new_password:this.password
            })).then(({data})=>{
                if(data.status == 'success'){
                    Toast('Пароль сәтті өзгертілді');
                    this.$router.push({ name:'login' });
                }else{
                    Toast(data.errors.message)
                }
            })
        },
        onInput(key) {
            this.sms = (this.sms + key).slice(0, 6);

            this.disabled = false;
        },
        onDelete() {
            this.sms = this.sms.slice(0, this.sms.length - 1);
        },
        onHide() {
            this.sms = ''
        },
        tabClick(val){
            this.curType = val
        },
        onClickBack(){
            if(this.type > 0){
                this.type --

                if(this.type == 0){
                    this.$store.dispatch('setImgVerify');
                    this.userinfo = '';
                    this.username = '';
                    this.imgverifyValue = '';
                }
            }else{
                this.$router.push({ name:'login' });
            }
        },
    }
}
</script>

<style lang="scss" scoped>
.container{
    background: #fff;
    position: relative;
    overflow: hidden;
    padding: 0 30px;

    &.container-register{
        .login-form{
            margin-top: 30%;
        }
    }

    .login-head{
        position: absolute;
        left: 20px;
        top: 2.5%;
    }

    .login-form{
        .logo{
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin: 15% auto;
        }

        .title{
            font-size: 18px;
            font-weight: 700;
            color: #000;

            margin-bottom: 10%;     
        }

        .input-box{
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: 30px;
            line-height: 30px;
            padding: 5px 0;
            box-sizing: content-box;
            border-bottom: 1px solid #dcdcdc;
            margin-bottom: 5%;

            &__left{
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: flex-start;

                .iconfont{
                    margin-right: 10px;
                    color: #666;
                    height: 30px;
                    line-height: 26px;
                    font-size: 20px;

                    &.icon-guanbi{
                        margin-right: 0;
                        font-size: 12px;
                        margin-left: 10px;
                    }
                }

                .input{
                    flex: 1;
                    width: 100%;
                }
            }

            &__right{
                display: flex;
                justify-content: flex-start;
                align-items: center;


                .iconfont{
                    flex: 1;
                    color: #999;
                    position: relative;
                    padding: 0 10px 0 10px;

                    &.active{
                        color: #f92028;
                    }
                }

                .forgetpwd{
                    color: #4b89dc;
                    font-size: 12px;
                    display: block;
                }

                .send{
                    height: 30px;
                    line-height: 30px;
                    padding: 0 12px;
                    border: 1px solid #dcdcdc;
                    border-radius: 15px;
                    font-size: 12px;
                    color: #999;
                    margin-left: 10px;
                }
            }
        }

        .signup-button{
            margin-top: 10%;

            .btn{
                height: 40px;
                padding: 0;
                border-radius: 20px;
                line-height: 40px;
                margin-bottom: 5%;

                &.btn-bor-red{
                    border: 1px solid #f92028;
                    color: #f92028;
                    font-weight: 700;
                }

                &.btn-disabled{
                    cursor: not-allowed;
                    opacity: 0.4;
                }
            }

            .tips{
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 12px;
                color: #333;

                .go-register{
                    margin-left: 5px;
                    color: #f92028;
                }
            }
        }
    }

    .send-yzm{
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 5%;

        .text{
            font-size: 24px;

            &.text-email{
                font-size: 16px;
            }
        }

        .send{
            height: 35px;
            line-height: 35px;
            padding: 0 20px;
            border: 1px solid #dcdcdc;
            border-radius: 18px;
            font-size: 12px;
            color: #999;
            margin-left: 10px;
            text-align: center;
        }
    }

    .tip{
        color: #c5c5c5;
        text-align: left;
    }
}
</style>