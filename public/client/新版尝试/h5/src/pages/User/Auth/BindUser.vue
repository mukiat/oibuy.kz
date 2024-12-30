<template>
  <section class="con bg-color-write">
    <div class="user-login-box">
      <ec-form ref="loginForm" class="user-login-form">
        <div class="user-login-head">
          <i class="iconfont icon-back" @click="onClickHome"></i>
          <h1>{{$t('lang.bind_user')}}</h1>
        </div>
        <div class="user-login-ul">
          <ec-form-item prop="imgverify">
            <div class="item-input dis-box">
              <div class="label">
                <i class="iconfont icon-pic"></i>
              </div>
              <div class="value box-flex">
                <ec-input type="text" v-model="imgverifyValue" :placeholder="$t('lang.captcha_img')"></ec-input>
              </div>
              <div class="key">
                <img :src="captcha" class="j-verify-img" @click="clickCaptcha" />
              </div>
            </div>
          </ec-form-item>
          <ec-form-item prop="mobile">
            <div class="item-input dis-box">
              <div class="label">
                <i class="iconfont icon-mobiles"></i>
              </div>
              <div class="value box-flex">
                <ec-input type="tel" v-model="mobile" :placeholder="mobile_placeholder"></ec-input>
              </div>
              <div class="key">
                <label @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</label>
                <label v-else>{{ button_text }}</label>
              </div>
            </div>
          </ec-form-item>
          <ec-form-item prop="sms">
            <div class="item-input dis-box">
              <div class="label">
                <i class="iconfont icon-key"></i>
              </div>
              <div class="value box-flex">
                <ec-input type="tel" v-model="sms" :placeholder="$t('lang.get_sms_code')"></ec-input>
              </div>
            </div>
          </ec-form-item>
        </div>
        <button type="button" class="btn btn-submit border-radius-top05" @click="submitBtn">{{$t('lang.bind_on')}}</button>
      </ec-form>
    </div>
  </section>
</template>
<script>
  import qs from 'qs'
  import { mapState } from 'vuex'

  import {
    Form,
    Input,
    FormItem
  } from 'element-ui'

  import {
    Dialog,
    Toast
  } from 'vant'

  export default {
    name: 'login',
    data() {
      return {
        imgverifyValue: '',
        sms: '',
        mobile: '',
        button_text: this.$t('lang.send_again_60'),
        send_again:this.$t('lang.send_again'),
        button_type: true,
        type: this.$route.query.type ? this.$route.query.type : '',
        url: this.$route.query.url ? this.$route.query.url : '',
        params:this.$route.query.params ? this.$route.query.params : '',
      }
    },
    components: {
      'EcForm': Form,
      'EcFormItem': FormItem,
      'EcInput': Input,
      [Toast.name]: Toast,
      [Dialog.name]: Dialog,
    },
    created() {
      this.$store.dispatch('setImgVerify')
    },
    computed: {
      mobile_placeholder() {
        return this.$t('lang.enter_mobile')
      },
      captcha() {
        return this.$store.state.imgVerify.captcha
      },
      client() {
        return this.$store.state.imgVerify.client
      }
    },
    methods: {
      clickCaptcha() {
        this.$store.dispatch('setImgVerify')
      },
      sendVerifyCode() {
        let o = {
          captcha: this.imgverifyValue,
          client: this.client,
          mobile: this.mobile
        }
        if (!this.checkMobile()) {
          Toast(this.$t('lang.phone_number_format'))
          return false
        }
        this.$store.dispatch('setSendVerify', o).then(res => {
          if (res == 'success') {
            this.button_type = false
            let second = 60
            const timer = setInterval(() => {
              second--
              if (second) {
                this.button_text = this.send_again + '(' + second + 's)'
              } else {
                this.button_type = true
                clearInterval(timer);
              }
            }, 1000)
          }
        })
      },
      submitBtn() {
        let self = this;
        if (!self.checkMobile()) {
          Toast(self.$t('lang.phone_number_format'))
          return false
        }

        if (self.sms == '') {
          Toast(self.$t('lang.get_sms_code'))
          return false
        }

        //参数解析
        let o = JSON.parse(self.params);
        o.step = 2;
        o.mobile = self.mobile;
        o.client = self.client;
        o.code = self.sms;

        //绑定会员
        self.$store.dispatch('bindRegister', o).then(res=>{
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
            }
          }
        })
      },
      onClickHome() {
        this.$router.push({
          name: 'accountsafe'
        })
      },
      checkMobile() {
        let rule = /^(\d{10})$/
        if (rule.test(this.mobile)) {
          return true
        } else {
          return false
        }
      },
    },
    watch: {

    }
  }
</script>
