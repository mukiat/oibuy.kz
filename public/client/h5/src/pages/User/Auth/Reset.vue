<template>
    <section class="con bg-color-write">
        <div class="user-login-box">
            <ec-form ref="loginForm" class="user-login-form">
              <div class="user-login-head">
                  <i class="iconfont icon-back" @click="onClickHome"></i>
                  <h1>{{$t('lang.reset_pwd')}}</h1>
              </div>
              <div class="user-login-ul">
                <p class="set-text">{{$t('lang.set_new_pwd')}}</p>
                <ec-form-item prop="resetPwd">
                  <div class="item-input dis-box">
                    <div class="value box-flex">
                      <template v-if="pwdshow === true">
                        <ec-input type="password" size="password" v-model="new_password" :placeholder="$t('lang.new_password_notic')"></ec-input>
                      </template>
                      <template v-else>
                        <ec-input type="input" size="password" v-model="new_password" :placeholder="$t('lang.new_password_notic')"></ec-input>
                      </template>
                    </div>
                    <i class="iconfont icon-liulan1" :class="{'disabled' : pwdshow === true}"
                               @click="handlePwdShow()"></i>
                  </div>
                </ec-form-item>
              </div>
              <button type="button" class="btn btn-submit border-radius-top05" @click="submitBtn">{{$t('lang.confirm_update')}}</button>
            </ec-form>
            <p class="tips">{{$t('lang.reset_prompt_notic')}}</p>
        </div>
    </section>
</template>
<script>
import qs from 'qs'
import {mapState} from 'vuex'
import {Form, Input, FormItem} from 'element-ui'
import {Toast} from 'vant'

import axios from 'axios'

export default {
  name: 'login',
  data() {
    return {
      new_password:'',
      pwdshow: true,
      mobile:this.$route.query.mobile,
      client:this.$route.query.client,
      code:this.$route.query.code
    }
  },
  components: {
    'EcForm': Form,
    'EcFormItem': FormItem,
    'EcInput': Input,
    [Toast.name]: Toast,
  },
  created() {
  },
  computed: {
  },
  methods: {
    handlePwdShow() {
        this.pwdshow = this.pwdshow === true ? false : true;
    },
    submitBtn(){
      this.$http.post(`${window.ROOT_URL}api/user/reset`,qs.stringify({
        password:this.new_password,
        mobile:this.mobile,
        client:this.client,
        code:this.code
      })).then(({data:data}) =>{
        if(data.status == 'success'){
            Toast(this.$t('lang.reset_pwd_success'))
            this.$router.push({name: 'user'})
        }else{
            if(data.errors){
                Toast(data.errors.message)
            }
        }
      })
    },
    onClickHome(){
      this.$router.push({name: 'user'})
    }
  }
}
</script>
