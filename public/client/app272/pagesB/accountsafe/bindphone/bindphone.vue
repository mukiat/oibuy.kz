<template>
	<view class="container-bwg">
		<view class="user-lr-form">
			<view class="title" v-if="step == 1">{{$t('lang.bind_phone')}}</view>
			<view class="title" v-else>{{$t('lang.set_phone')}}</view>
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
				delta:0,
				step:1
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
			},
			isdelta(){
				return this.delta ? Number(this.delta) + 1 : 0
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
					//绑定手机号
					if(this.step == 1){
						uni.request({
							url: this.websiteUrl + '/api/accountsafe/bind_mobile',
							method: 'POST',
							data: {
								mobile:this.mobile,
								client:this.client,
								code:this.sms
							},
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								let data = res.data
								if(data.status === 'success'){
									uni.showToast({
										title:this.$t('lang.reset_phone_success'),
										icon:'none'
									});

									if(this.delta){
										uni.navigateBack({
											delta:1
										})
									}else{
										uni.switchTab({
											url: '/pages/user/user',
										});
									}
								}else{
									if(data.errors){
										uni.showToast({
											title:data.errors.message,
											icon:'none'
										})
									}
								}
							},
							fail: (res) => {
								console.log(JSON.stringify(res))
							}
						})
					}else{
						//修改绑定新手机号
						uni.request({
							url: this.websiteUrl + '/api/accountsafe/change_mobile',
							method: 'POST',
							data: {
								mobile:this.mobile,
								client:this.client,
								code:this.sms,
								step: this.step
							},
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								let data = res.data
								if(data.status === 'success'){
									uni.showToast({
										title:that.$t('lang.edit_success'),
										icon:'none'
									})
									
									uni.switchTab({
										url: '/pages/user/user',
									});
								}else{
									if(data.errors){
										uni.showToast({
											title:data.errors.message,
											icon:'none'
										})
									}
								}
							},
							fail: (res) => {
								console.log(JSON.stringify(res))
							},
						})
					}
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			}
		},
		onLoad(e){
			this.name = e.name
			this.delta = e.delta ? e.delta : 0
			this.step = e.step ? e.step : 1
			this.clickCaptcha();
		}
	}
</script>

<style>
.user-lr-form .item-input label{ width: auto; height: 1.4rem; line-height: 1.4rem; }
.tips{ font-size: 25upx; color: #888888; margin-top: 10upx;}
</style>
