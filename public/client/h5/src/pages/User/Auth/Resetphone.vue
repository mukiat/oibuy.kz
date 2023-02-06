<template>
	<section class="con bg-color-write">
		<div class="user-login-box">
			<ec-form ref="loginForm" class="user-login-form">
				<div class="user-login-head">
					<i class="iconfont icon-back" @click="onClickHome"></i>
					<h1>{{$t('lang.set_phone')}}</h1>
				</div>
				
				<template v-if="step == 1">
				<div class="user-login-ul">
					<ec-form-item prop="sms">
						<div class="item-input dis-box">
							<div class="label">
								<i class="iconfont icon-key"></i>
							</div>
							<div class="value box-flex">
								<ec-input type="text" :disabled="true" v-model="mobile"></ec-input>
							</div>
						</div>
					</ec-form-item>
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
								<!-- <ec-input type="tel" v-model="mobile" :placeholder="mobile_placeholder"></ec-input> -->
								<ec-input type="tel" v-model="sms" :placeholder="$t('lang.get_sms_code')"></ec-input>
							</div>
							<div class="key">
								<label @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</label>
								<label v-else>{{ button_text }}</label>
							</div>
						</div>
					</ec-form-item>
				</div>
			
				<button type="button" class="btn btn-submit border-radius-top05" @click="verifyMobile"> {{$t('lang.next_step')}}  </button>
				</template>
				
				<template v-else>
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
				</template>

			</ec-form>
		</div>
	</section>
</template>
<script>
	import qs from 'qs'
	import {
		mapState
	} from 'vuex'
	import {
		Form,
		Input,
		FormItem
	} from 'element-ui'
	import {
		Toast
	} from 'vant'

	import axios from 'axios'

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
				step:1,
				timer:null,
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
			
			this.getUser()
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
			getUser(){
				this.$http.post(`${window.ROOT_URL}api/user/get-userid`).then(res=>{
					if (res.data.status == 'success') {
						this.mobile = res.data.data.mobile_phone
					} else {
						if (res.data.errors) {
							Toast(data.errors.message)
						}
					}
				})
				
			},
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
						this.timer = setInterval(() => {
							second--
							if (second) {
								this.button_text = this.send_again + '(' + second + 's)'
							} else {
								this.button_type = true
								clearInterval(this.timer);
							}
						}, 1000)
					}
				})

			},
			verifyMobile() {
				// 验证原手机号
				if (!this.checkMobile()) {
					Toast(this.$t('lang.phone_number_format'))
					return false
				}
				
				if (this.sms == '') {
					Toast(this.$t('lang.get_sms_code'))
					return false
				}
				this.$http.post(`${window.ROOT_URL}api/accountsafe/change_mobile`, qs.stringify({
					mobile: this.mobile,
					client: this.client,
					code: this.sms,
					step: 1
				})).then(({
					data: data
				}) => {
					if (data.status == 'success') {
						Toast(data.data.msg)
						
						this.step = 2;
						
						// 重置原手机号、生成新图片验证码
						this.mobile = '';
						this.imgverifyValue = '';
						this.sms = '';
						this.clickCaptcha();
						this.button_text = this.$t('lang.send_again_60');
						this.button_type = true;
						clearInterval(this.timer);
						
					} else {
						if (data.errors) {
							Toast(data.errors.message)
						}
					}
				})
			},
			submitBtn() {
				
				// 绑定新手机号
				if (!this.checkMobile()) {
					Toast(this.$t('lang.phone_number_format'))
					return false
				}

				if (this.sms == '') {
					Toast(this.$t('lang.get_sms_code'))
					return false
				}
				this.$http.post(`${window.ROOT_URL}api/accountsafe/change_mobile`, qs.stringify({
					mobile: this.mobile,
					client: this.client,
					code: this.sms,
					step: 2
				})).then(({
					data: data
				}) => {
					if (data.status == 'success') {
						this.$router.push({
							name: 'user'
						})
					} else {
						if (data.errors) {
							Toast(data.errors.message)
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
