<template>
    <section class="con bg-color-write">
        <div class="user-login-box">
            <ec-form ref="loginForm" class="user-login-form">
              <div class="user-login-head">
                  <i class="iconfont icon-back" @click="onClickHome"></i>
                  <h1 v-if="type">{{$t('lang.reset_password')}}</h1>
                  <h1 v-else>{{$t('lang.forget_password')}}</h1>
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
                      <ec-input type="tel" v-model="mobile" :disabled="isDisabled" :placeholder="mobile_placeholder"></ec-input>
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
              <template v-if="state">
                <button type="button" class="btn btn-submit border-radius-top05" @click="submitBtn">{{$t('lang.next_step')}}</button>
              </template>
              <template v-else>
                <button type="button" class="btn btn-submit border-radius-top05 btn-disabled">{{ userInfo.mobile ? $t('lang.next_step') : 'Тел нөмір байланбаған,өзгерте алмайсыз.' }}</button>
              </template>
            </ec-form>
        </div>
    </section>
</template>
<script>
import {mapState} from 'vuex'
import {Form, Input, FormItem} from 'element-ui'
import {Toast} from 'vant'

import axios from 'axios'

export default {
  name: 'login',
  data() {
    return {
      imgverifyValue:'',
      sms:'',
      button_text:this.$t('lang.send_again_60'),
      send_again:this.$t('lang.send_again'),
      button_type:true,
      state:false,
      type:this.$route.query.type ? this.$route.query.type : '',
      isDisabled:true
    }
  },
  components: {
    'EcForm': Form,
    'EcFormItem': FormItem,
    'EcInput': Input,
    [Toast.name]: Toast,
  },
  created() {
    this.$store.dispatch('setImgVerify')

    this.$store.dispatch('userProfile');
  },
  computed: {
    ...mapState({
      token: state => state.user.token,
      userInfo: state => state.user.userInfo
    }),
    mobile(){
      return this.userInfo.mobile
    },
    mobile_placeholder(){
      return this.userInfo.mobile ? this.$t('lang.enter_mobile') : 'Телефон байланбаған'
    },
    captcha(){
      return this.$store.state.imgVerify.captcha
    },
    client(){
      return this.$store.state.imgVerify.client
    }
  },
  methods: {
    clickCaptcha(){
      this.$store.dispatch('setImgVerify')
    },
    sendVerifyCode(){
      let o = {
        captcha:this.imgverifyValue,
        client:this.client,
        mobile:this.mobile
      }

      if(!this.checkMobile()){
        Toast(this.$t('lang.phone_number_format'))
        return false
      }

      this.$http.get(`${window.ROOT_URL}api/user/profile/basic`,{params:{
        name:this.mobile
      }}).then(({data:data})=>{
        if(data.status === 'success'){
          this.state = true
          this.$store.dispatch('setSendVerify', o).then(res=>{
            if(res == 'success'){
              this.button_type = false
              let second = 60
              const timer = setInterval(()=>{
                second --
                if(second){
                  this.button_text = this.send_again + '('+ second +'s)'
                }else{
                  this.button_type = true
                  clearInterval(timer)
                }
              },1000)
            }
          })
        }else{
          Toast(this.$t('lang.user_not_exist'))
        }
      })
    },
    submitBtn(){
      if(!this.checkMobile()){
        Toast(this.$t('lang.phone_number_format'))
        return false
      }

      if(this.sms == ''){
        Toast(this.$t('lang.get_sms_code'))
        return false
      }

      this.$router.push({
      	name:'reset',
        query:{
          mobile:this.mobile,
          client:this.client,
          code:this.sms
        }
      })
    },
    onClickHome(){
      this.$router.push({name: 'user'})
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
    token(){
      this.$router.push({
        name:'user'
      })
    }
  }
}
</script>
