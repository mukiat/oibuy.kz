<template>
	<view>
		<form @submit="formSubmit">
			<view class="uni-card uni-card-not">
				<view class="header-title">{{$t('lang.edit_pay_pwd')}}</view>
				<view class="uni-list">
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_phone_number')}}</text>
							<view class="value"><input name="mobile" v-model="mobile" :placeholder="$t('lang.enter_mobile')" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.pic_code')}}</text>
							<view class="value value-items">
								<input :placeholder="$t('lang.enter_pic_code')" name="imgverifyValue" v-model="imgverifyValue">
								<view class="code-box" @click="clickCaptcha"><image :src="captcha" class="img"></image></view>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_sms_code')}}</text>
							<view class="value value-items">
								<input :placeholder="$t('lang.get_sms_code')" maxlength="6" name="sms" v-model="sms">
								<button size="mini" type="warn" @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</button>
								<button size="mini" type="default" v-else>{{ button_text }}</button>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_pay_pwd')}}</text>
							<view class="value"><input name="pwd" :placeholder="$t('lang.enter_pay_pwd')" type="number" v-model="pwd" maxlength="6"></view>
						</view>
					</view>
				</view>
			</view>
			<view class="btn-bar btn-bar-fixed">
				<button class="btn btn-red" type="primary" formType="submit" v-if="paypwd_id > 0">{{$t('lang.mod')}}</button>
				<button class="btn btn-red" type="primary" formType="submit" v-else>{{$t('lang.enabled')}}</button>
			</view>
		</form>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	var graceChecker = require("@/common/graceChecker.js");
	export default {
		data() {
			return {
				mobile:'',
				imgverifyValue: '',
				sms: '',
				button_text: this.$t('lang.send_again_60'),
				send_again:this.$t('lang.send_again'),
				button_type: true,
				button_edit_type : false,
				button_edit: this.$t('lang.subimt'),
				isDisabled:true,
				pwd:'',
				paypwd_id:0,
				is_mobile:0,
				notice:''
			}
		},
		computed: {
			...mapState({
				captcha: state => state.common.imgVerify.captcha,
				client: state => state.common.imgVerify.client,
			})
		},
		methods: {
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			sendVerifyCode() {
			    let o = {
			        captcha: this.imgverifyValue,
			        client: this.client,
			        mobile: this.mobile,
					is_mobile: this.is_mobile,
			    }

			    this.$store.dispatch('setSendVerify', o).then(res => {
			        if (res == 'success') {
			            this.button_type = false
			            let second = 60
			            const timer = setInterval(() => {
			                second--
			                if (second) {
			                    this.button_text = this.send_again+'(' + second + 's)'
			                } else {
			                    this.button_type = true
			                    clearInterval(timer);
			                }
			            }, 1000)
			        }
			    })
			},
			formSubmit(e){
				var rule = [
					{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.captcha_img_not_null')},
					{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
					{name:"mobile", checkType : "phoneno", checkRule:"",  errorMsg:this.$t('lang.mobile_not_null')},
					{name:"pwd", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_not_pwd')},
				];
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);

				if(checkRes){
					if(this.pwd.length < 6){
						uni.showToast({
							title:this.$t('lang.get_six_not_pwd'),
							icon:'none'
						})

						return false
					}

					let o = {
						paypwd_id: this.paypwd_id,
						pay_paypwd: this.pwd,
						client: this.client,
						code: this.sms,
						mobile: this.mobile
					}
					
					uni.request({
						url:this.websiteUrl + '/api/accountsafe/edit_paypwd',
						method:'POST',
						data:o,
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							let data = res.data
							if(data.status == 'success'){
								uni.showToast({ title: data.data.msg, icon: "none" });
								if (data.data.code == 0) {
									setTimeout(()=>{
										uni.navigateBack({
											delta:1
										})
									},1000)
								}
							}else{
								uni.showToast({ title: data.errors.message, icon: "none" });
							}
						}
					})
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			}
		},
		onLoad(){
			this.$store.dispatch('setImgVerify');

			uni.request({
				url:this.websiteUrl + '/api/accountsafe/add_paypwd',
				method:'GET',
				data:{},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					let data = res.data.data
					console.log(data)

					if(data.code == 0){
						if(data.user_info.mobile_phone){
							this.mobile = data.user_info.mobile_phone
							this.isDisabled = true
						}else{
							this.isDisabled = false
							this.is_mobile = 1
						}

						this.paypwd_id = data.users_paypwd.paypwd_id ? data.users_paypwd.paypwd_id : 0
						this.notice = data.notice
					}else{
						this.isDisabled = false
						this.is_mobile = 1

						uni.showToast({
							title:data.msg,
							icon:'none'
						})
					}
				}
			})
		}
	}
</script>

<style>
.header-title{ padding: 20upx 30upx; border-bottom: 1px solid #DDDDDD; font-size: 30upx;}
</style>
