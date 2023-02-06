<template>
	<view class="container-bwg">
		<view class="user-lr-form">
			<view class="title">{{$t('lang.reset_pwd')}}</view>
			<form @submit="formSubmit">
				<view class="item-inputs">
					<view class="item-input">
						<label><text class="iconfont icon-picture"></text></label>
						<input type="text" name="imgverifyValue" v-model="imgverifyValue" :placeholder="$t('lang.captcha_img')" />
						<view class="code-box" @click="clickCaptcha"><image :src="captcha" class="img"></image></view>
					</view>
					<view class="item-input">
						<label><text class="iconfont icon-mobiles"></text></label>
						<input type="text" name="mobile" v-model="mobile" :disabled="isDisabled" :placeholder="mobile_placeholder" />
						<view class="key">
							<text @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</text>
							<text v-else>{{ button_text }}</text>
						</view>
					</view>
					<view class="item-input">
						<label><text class="iconfont icon-key"></text></label>
						<input type="text" name="sms" v-model="sms" maxlength="6" :placeholder="$t('lang.get_sms_code')" />
					</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<button class="btn" :class="[disabled ? 'btn-disabled' : 'btn-red']" formType="submit" :disabled="disabled">{{$t('lang.next_step')}}</button>
				</view>
			</form>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	var graceChecker = require("@/common/graceChecker.js");
	export default {
		data() {
			return {
				imgverifyValue:'',
				sms:'',
				button_text: this.$t('lang.send_again_60'),
				send_again:this.$t('lang.send_again'),
				button_type:true,
				disabled:true,
				isDisabled:true
			};
		},
		components:{
			uniIcons
		},
		computed:{
			...mapState({
				captcha: state => state.common.imgVerify.captcha,
				client: state => state.common.imgVerify.client,
				userInfo: state => state.user.userInfo
			}),
			mobile(){
				return this.userInfo.mobile
			},
			mobile_placeholder(){
				return this.userInfo.mobile ? this.$t('lang.enter_mobile') : '尚未绑定手机号'
			},
			token:{
				get(){
					return this.$store.state.user.token
				},
				set(val){
					this.$store.state.user.token = val
				}
			},
		},
		methods:{
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			sendVerifyCode() {
			    let o = {
			        captcha: this.imgverifyValue,
			        client: this.client,
			        mobile: this.mobile
			    }

				uni.request({
					url: this.websiteUrl + '/api/user/profile/basic',
					method: 'GET',
					data: {
						name:this.mobile
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data
						if(data.status === 'success'){
							this.disabled = false
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
						}else{
							this.disabled = true
							uni.showToast({
								title:this.$t('lang.user_not_exist'),
								icon:'none'
							})
						}
					},
					fail: (res) => {
						console.log(JSON.stringify(res))
					}
				})
			},
			formSubmit(e){
				var rule = [
					{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.captcha_img_not_null')},
					{name:"mobile", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.mobile_not_null')},
					{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
				];

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				if(checkRes){
					uni.navigateTo({
						url:'../reset/reset?mobile='+this.mobile+'&client='+this.client+'&code='+this.sms
					})
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			}
		},
		onLoad(){
			this.clickCaptcha();

			this.$store.dispatch('userProfile');
		}
	}
</script>

<style>
.user-lr-form .item-input label{ width: auto; height: 1.4rem; line-height: 1.4rem; }
.tips{ font-size: 25upx; color: #888888; margin-top: 10upx;}
</style>
