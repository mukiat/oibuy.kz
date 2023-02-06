<template>
	<view class="container-bwg">
		<view class="user-lr-form">
			<view class="title">{{$t('lang.bind_user')}}</view>
			<form @submit="formSubmit">
				<view class="item-inputs">
					<view class="item-input">
						<label><text class="iconfont icon-picture"></text></label>
						<input type="text" name="imgverifyValue" v-model="imgverifyValue" :placeholder="$t('lang.captcha_img')" />
						<view class="code-box" @click="clickCaptcha"><image :src="captcha" class="img"></image></view>
					</view>
					<view class="item-input">
						<label><text class="iconfont icon-mobiles"></text></label>
						<input type="text" name="mobile" v-model="mobile" :placeholder="$t('lang.enter_mobile')" />
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
					<button class="btn btn-red" formType="submit">{{$t('lang.bind_on')}}</button>
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
				mobile:'',
				imgverifyValue:'',
				sms:'',
				name:'',
				button_text: this.$t('lang.send_again_60'),
				send_again:this.$t('lang.send_again'),
				button_type:true,
				disabled:true,
				delta:'',
				params:''
			};
		},
		components:{
			uniIcons
		},
		computed:{
			...mapState({
				captcha: state => state.common.imgVerify.captcha,
				client: state => state.common.imgVerify.client,
			}),
			token:{
				get(){
					return this.$store.state.user.token
				},
				set(val){
					this.$store.state.user.token = val
				}
			}
		},
		methods:{
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			sendVerifyCode() {
				let that = this
			    let o = {
			        captcha: this.imgverifyValue,
			        client: this.client,
			        mobile: this.mobile
			    }
				
				that.$store.dispatch('setSendVerify', o).then(res => {
				    if (res == 'success') {
				        that.button_type = false
				        let second = 60
				        const timer = setInterval(() => {
				            second--
				            if (second) {
				                that.button_text = that.send_again + '(' + second + 's)'
				            } else {
				                that.button_type = true
				                clearInterval(timer);
				            }
				        }, 1000)
				    }
				})
			},
			formSubmit(e){
				let that = this
				var rule = [
					{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.captcha_img_not_null')},
					{name:"mobile", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.mobile_not_null')},
					{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
				];

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				if(checkRes){
					let o = JSON.parse(this.params);
					o.step = 2;
					o.mobile = that.mobile;
					o.client = that.client;
					o.code = that.sms;
					that.$store.dispatch('bindRegister', o).then(bindResult=>{
						if(bindResult.status == 'success'){
							if(bindResult.data.login == 1){
								uni.setStorageSync("token",bindResult.data.token);
								if (this.delta) {
									uni.navigateBack({
										delta:2
									})
								}else{
									uni.switchTab({
										url: '/pages/user/user'
									})
								}
							}
						}else{
							uni.showModal({
								content:bindResult.errors.message
							})
						}
					});
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			}
		},
		onLoad(e){
			this.params = e.params ? e.params : 0;
			this.delta = e.delta ? e.delta : 0;
			this.clickCaptcha();
		}
	}
</script>

<style>
.user-lr-form .item-input label{ width: auto; height: 1.4rem; line-height: 1.4rem; }
.tips{ font-size: 25upx; color: #888888; margin-top: 10upx;}
</style>
