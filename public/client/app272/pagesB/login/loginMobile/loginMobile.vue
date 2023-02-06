<template>
	<view class="container-bwg">
		<view class="user-lr-form">
			<view class="title">{{$t('lang.sms_code_login')}}</view>
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
							<text @click="sendVerifyCode" v-if="button_type">获取验证码</text>
							<text v-else>{{ button_text }}</text>
						</view>
					</view>
					<view class="item-input">
						<label><text class="iconfont icon-key"></text></label>
						<input type="text" name="sms" v-model="sms" maxlength="6" :placeholder="$t('lang.get_sms_code')" />
					</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<button class="btn btn-red" formType="submit">{{$t('lang.login_immediately')}}</button>
				</view>
				<view class="checkbox" :class="{'checked':privacyCheck}">
					<view class="checkbox-icon" @click="privacyCheck = !privacyCheck">
						<uni-icons type="checkmarkempty" size="14" color="#ffffff"></uni-icons>
					</view>
					<view class="checkbox-con">
						<view class="tips">{{$t('lang.register_privacy_read')}}<text @click="linkHref">《{{$t('lang.register_user_agreement')}}》</text></view>
					</view>
				</view>
				<navigator url="/pagesB/login/login" class="list-new" hover-class="none">{{$t('lang.account_pwd_login')}}<uni-icons color="#f92028" type="arrowright" size='20'></uni-icons></navigator>
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
				button_text: this.$t('lang.send_again_60'),
				send_again:this.$t('lang.send_again'),
				button_type:true,
				register_article_id:6,
				articleShow:false,
				articleDetail:'',
				parent_id:uni.getStorageSync('user_id') ? uni.getStorageSync('user_id') : 0,
				privacyCheck:false
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
			shopConfig(){
				uni.request({
					url:this.websiteUrl + '/api/shop/config',
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						if(data.register_article_id){
							this.register_article_id = data.register_article_id
						}
					}
				});
			},
			formSubmit(e){
				var rule = [
					{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.captcha_img_not_null')},
					{name:"mobile", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.mobile_not_null')},
					{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
				];
				
				if(!this.privacyCheck){
					uni.showToast({ title: this.$t('lang.register_privacy_read_prompt'), icon: "none" });
					return
				}

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				if(checkRes){
					this.$store.dispatch('userRegister',{
						client:this.client,
						mobile:this.mobile,
						code:this.sms,
						parent_id:this.parent_id,
						allow_login:1
					}).then(res=>{
						if(res.status == 'success'){
							uni.showToast({
								title:this.$t('lang.login_success'),
								success: (data) => {
									this.token = res.data;
									uni.setStorage({
										key:'token',
										data:res.data,
										success: (res) => {
											uni.switchTab({
												url:'/pages/user/user'
											});
										}
									});
								}
							});
						}else{
							uni.showToast({
								title:res.errors.message,
								icon:'none'
							})
						}
					})
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			},
			linkHref(){
				if(this.register_article_id){
					uni.navigateTo({
						url:'/pagesC/article/detail/detail?id=' + this.register_article_id + '&show=false'
					})
				}else{
					uni.showToast({
						title: this.$t('lang.set_privacy_policy'),
						icon:"none"
					})
				}
			}
		},
		onLoad(){
			this.clickCaptcha();
			this.shopConfig();
		}
	}
</script>

<style>
.user-lr-form .item-input label{ width: auto; height: 1.4rem; line-height: 1.4rem; }
.tips{ font-size: 25upx; color: #888888; margin-top: 10upx;}
.tips text{ color: #4b89dc;}
</style>
