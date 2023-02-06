<template>
    <section class="con bg-color-write">
        <div class="user-login-box">
            <ec-form ref="loginForm" class="user-login-form">
              <div class="user-login-head">
                  <i class="iconfont icon-back" @click="onClickHome"></i>
                  <h1>{{$t('lang.sms_code_login')}}</h1>
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
                      <ec-input type="tel" v-model="mobile" :placeholder="$t('lang.enter_mobile')"></ec-input>
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
                      <ec-input type="tel" v-model="sms" maxlength="6" :placeholder="$t('lang.get_sms_code')"></ec-input>
                    </div>
                  </div>
                </ec-form-item>
              </div>
              <button type="button" class="btn btn-submit border-radius-top05" @click="submitBtn">{{$t('lang.login_immediately')}}</button>
            </ec-form>
            <p class="tips">{{$t('lang.register_prompt_notic')}}<a href="javascript:;" @click="articleHref">{{$t('lang.register_prompt_1')}}</a></p>
            <div class="user-login-list">
                <router-link :to="{name:'login'}" class="list-new">{{$t('lang.account_pwd_login')}}<i class="iconfont icon-more"></i></router-link>
            </div>
        </div>

        <van-popup v-model="articleShow" class="shareImg" overlay-class="shareImg-overlay">
            <div class="content" v-if="articleDetail">
              <div v-html="articleDetail"></div>
              <div class="btn btn-submit" @click="submitArticle">{{$t('lang.confirm')}}</div>
            </div>
            <div class="content not-content" v-else>{{$t('lang.article_not_content')}}</div>
        </van-popup>
    </section>
</template>
<script>
import { mapState } from 'vuex'
import { Form, Input, FormItem , Dialog} from 'element-ui'
import { Toast , Popup} from 'vant'

import axios from 'axios'

export default {
  name: 'login',
  data() {
    return {
      mobile:'',
      imgverifyValue:'',
      sms:'',
      button_text:this.$t('lang.send_again_60'),
      send_again:this.$t('lang.send_again'),
      button_type:true,
      register_article_id:6,
      articleShow:false,
      articleDetail:''
    }
  },
  components: {
    'EcForm': Form,
    'EcFormItem': FormItem,
    'EcInput': Input,
    [Toast.name]: Toast,
    [Dialog.name]: Dialog,
    [Popup.name]: Popup
  },
  created() {
    this.$store.dispatch('setImgVerify')
    this.shopConfig()
  },
  computed: {
    token:{
      get(){
        return this.$store.state.user.token
      },
      set(val){
        this.$store.state.user.token = val
      }
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
      let that = this
      let o = {
        captcha:this.imgverifyValue,
        client:this.client,
        mobile:this.mobile
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
    },
    submitBtn(){
      let parent_id = localStorage.getItem('parent_id') ? localStorage.getItem('parent_id') : this.$route.query.parent_id ? this.$route.query.parent_id : null

      let o = {
        client:this.client,
        mobile:this.mobile,
        code:this.sms,
        parent_id:parent_id,
        allow_login:1
      }

      if(!this.checkMobile()){
        Toast(this.$t('lang.phone_number_format'))
        return false
      }

      if(this.imgverifyValue == ''){
        Toast(this.$t('lang.captcha_img'))
        return false
      }

      if(this.sms == ''){
        Toast(this.$t('lang.get_sms_code_notic'))
        return false
      }

      this.$store.dispatch('userRegister', o).then((res)=>{
        if(res.status == 'success'){
          Toast.success({
            duration: 1000,
            forbidClick: true,
            loadingType: 'spinner',
            message: this.$t('lang.login_success')
          })
          localStorage.setItem('token', res.data)
          this.token = res.data
        }else{
          Toast(res.errors.message)
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
    shopConfig(){
      this.$http.get(`${window.ROOT_URL}api/shop/config`).then(({data:{data}})=>{
        if(data.register_article_id){
          this.register_article_id = data.register_article_id
        }
      })
    },
    articleHref(){
      this.$store.dispatch('setArticleDetail2',{
        id:this.register_article_id
      }).then(res=>{
        if(res.status == 'success'){
          this.articleDetail = res.data.content
          this.articleShow = true
        }else{
          Toast(this.$t('lang.article_set_not'))
        }
      })
    },
    submitArticle(){
      this.articleShow = false
    }
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
