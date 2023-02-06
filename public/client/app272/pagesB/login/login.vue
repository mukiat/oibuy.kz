<template>
	<view class="container-bwg">
		<!-- #ifdef MP-WEIXIN -->
		<view class="login-section">
			<view class="header">
				<image :src="scopeUserInfo.avatarUrl" class="img" v-if="scopeUserInfo.avatarUrl"></image>
				<image src="../../static/logo.png" class="img" v-else></image>
			</view>
			<view class="user-name">
				<block v-if="scopeUserInfo.nickName">{{ scopeUserInfo.nickName }}</block>
			</view>
			<view class="cont">
				<block v-if="scope == 'userInfo'">{{$t('lang.weixin_login_user_info')}}</block>
				<block v-if="scope == 'phoneNumber'">{{$t('lang.weixin_login_phone_number')}}</block>
			</view>
			<view class="btn">
				<block v-if="scope == 'userInfo'">
					<button type="primary" class="page-body-button" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">{{$t('lang.weixin_empower')}}</button>
					<button type="primary" class="page-body-button" @click="getUserProfile" v-else>{{$t('lang.weixin_empower')}}</button>
				</block>
				<button type="primary" class="page-body-button" open-type="getPhoneNumber" @getphonenumber="getPhoneNumber" v-if="scope == 'phoneNumber'">{{$t('lang.one_tongbu_login')}}</button>
			</view>
		</view>
		<!-- #endif -->

		<!-- #ifdef APP-PLUS -->
		<view class="container-login" :style="{'min-height':height + 'px'}">
			<div class="login-head"><i class="iconfont icon-close" @click="onClickBack"></i></div>
			<view class="login-form">
				<view class="logo" v-if="configData">
					<image :src="configData.wap_logo" class="img" mode="widthFix"></image>
				</view>
				<form @submit="formSubmit">
					<block v-if="loginMode">
						<view class="input-box">
							<view class="input-box__left">
								<icon class="iconfont icon-wodeguanzhu"></icon>
								<input type="text" class="input" v-model="username" name="username" autocomplete="off" :placeholder="$t('lang.enter_username')" />
								<icon class="iconfont icon-guanbi" @click="username = ''" v-show="username"></icon>
							</view>
						</view>
						<view class="input-box">
							<view class="input-box__left">
								<icon class="iconfont icon-jiesuo"></icon>
								<input :type="pwd" class="input" v-model="password" name="password" autocomplete="off" :placeholder="$t('lang.enter_password')" />
							</view>
							<view class="input-box__right">
								<icon class="iconfont icon-liulan1" :class="{'active':pwd == 'text'}" @click="handlePwdShow"></icon>
								<navigator url="../login/forgetPwd/forgetPwd" hover-class="none" class="forgetpwd">{{$t('lang.forget_password')}}</navigator>
							</view>
						</view>
					</block>
					<block v-else>
						<view class="input-box">
							<view class="input-box__left">
								<icon class="iconfont icon-shouji1"></icon>
								<input type="text" class="input" v-model="mobile" name="mobile" autocomplete="off" :placeholder="$t('lang.enter_mobile')" />
								<icon class="iconfont icon-guanbi" @click="mobile = ''" v-show="mobile"></icon>
							</view>
						</view>
						<view class="input-box">
							<view class="input-box__left">
								<icon class="iconfont icon-tupian"></icon>
								<input type="text" name="imgverifyValue" v-model="imgverifyValue" :placeholder="$t('lang.captcha_img')" />
							</view>
							<view class="input-box__right">
								<view class="code-box" @click="clickCaptcha"><image :src="captcha" class="img"></image></view>
							</view>
						</view>
						<view class="input-box">
							<view class="input-box__left">
								<icon class="iconfont icon-anquan"></icon>
								<input type="tel" class="input" v-model="sms" name="sms" maxlength="6" autocomplete="off" :placeholder="$t('lang.get_sms_code')" />
							</view>
							<view class="input-box__right">
								<view class="send" @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</view>
								<view class="send" v-else>{{ button_text }}</view>
							</view>
						</view>
					</block>
					<view class="privacy">
						<view class="checkbox" :class="{'checked':checked}">
							<view class="checkbox-icon" @click="checked = !checked">
								<uni-icons type="checkmarkempty" size="14" color="#ffffff"></uni-icons>
							</view>
							<view class="checkbox-con">
								<text class="txt" @click="checked = !checked">我已阅读并同意</text>
								<text class="href" @click="privacyShow = true">《隐私协议》</text>
							</view>
						</view>
					</view>
					<view class="signup-button">
						<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" form-type="submit">
							{{ loginMode ? '账号密码登录' : '短信快捷登录' }}
						</button>
						<button class="btn btn-bor-red" v-if="shop_reg_closed == 0" @click="loginMode = !loginMode">
							{{ loginMode ? '短信快捷登录' : '账号密码登录' }}
						</button>
						<view class="tips">还没有账号?<navigator url="../register/register" hover-class="none" class="go-register" v-if="shop_reg_closed == 0">立即去注册</navigator></view>
					</view>
				</form>
			</view>
			
			<view class="bottom">
				<!--第三方登录-->
				<view class="quick-login" v-if="oauthList && oauthList.length > 0 && footerTop > 0">
					<view class="title"><text>{{$t('lang.thirdparty_login')}}</text></view>
					<view class="quick-login-items">
						<view class="quick-login-item" v-for="(item,index) in oauthList" :key="index" @click="thirdPartyLink(item)">
							<image :src="thirdPartyImg[index]"></image>
						</view>
					</view>
				</view>
			</view>
		</view>

		<uni-popup :show="privacyShow" type="bottom" v-on:hidePopup="privacyShow = false">
			<view class="show-popup-bottom-common show-popup-privacy" style="height: 60vh;">
				<view class="title">
					<text class="strong">隐私政策</text>
					<text class="iconfont icon-close" @click="privacyShow = false"></text>
				</view>
				<view class="content">
					<view class="article-content">
						<jyf-parser :html="privacyActicleContent"></jyf-parser>
					</view>
					<view class="footer">
						<button type="warn" class="btn" :class="{'btn-disabled':privacyDisabled}" :disabled="privacyDisabled" @click="privacyShow = false; checked = true">{{ privacy_button_text }}</button>
					</view>
				</view>
			</view>
		</uni-popup>
		<!-- #endif -->
		
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import {
		mapState,
		mapMutations
	} from 'vuex'

	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import jyfParser from "@/components/jyf-parser/jyf-parser";

	var graceChecker = require("@/common/graceChecker.js");

	export default {
		data() {
			return {
				providerList: [],
				pwd: 'password',
				scopeUserInfo: {},
				scope: 'userInfo',
				scopeSessions: {},
				oauthList: [],
				clientHight: 0,
				footerHight: 0,
				delta: '',
				parent_id: uni.getStorageSync('parent_id') ? uni.getStorageSync('parent_id') : 0,
				isWXInstalled:false,
				system: '', // 系统版本
				platform: '',   // 平台
				loginMode:false,
				register_article_id: uni.getStorageSync('configData').register_article_id,
				configData: uni.getStorageSync('configData'),
				shop_reg_closed:null,
				disabled:true,
				// 账号密码登录
				username:'',
				password:'',
				// 短信快捷登录
				mobile:'',
				imgverifyValue:'',
				sms:'',
				send_again:this.$t('lang.send_again'),
				button_text:this.$t('lang.send_again_60'),
				button_type:true,
				// 隐私协议
				checked:false,
				privacyShow:false,
				privacyActicleContent:'',
				privacyActicleTitle:'',
				privacyDisabled:true,
				privacy_button_text:'我已阅读(6s)',
				timer:null,
				height:uni.getSystemInfoSync().windowHeight,
				canIUseProfile:false
			}
		},
		components: {
			uniIcons,
			uniPopup,
			dscCommonNav,
			jyfParser
		},
		computed: {
			...mapState({
				phoneNumber: state => state.common.phoneNumber,
				captcha: state => state.common.imgVerify.captcha,
				client: state => state.common.imgVerify.client,
			}),
			thirdPartyImg() {
				let arr = []
				this.oauthList.forEach((v) => {
					arr.push('/static/oauth/' + v + '.png')
				})
				return arr
			},
			footerTop() {
				return this.clientHight - this.footerHight
			}
		},
		onLoad(e) {
			this.delta = e.delta ? e.delta : ''
			this.loginMode = e.loginMode == 'sms' ? false : true

			//#ifdef MP-WEIXIN
			uni.removeStorage({
				key: 'scopeSession',
				success: (res) => {
					this.scopeSession();
				}
			})
			
			// 判断小程序是否存在getUserProfile
			if(uni.getUserProfile) this.canIUseProfile = true;
			
			//#endif

			// #ifdef APP-PLUS
			// 先判断 系统版本
			uni.getSystemInfo({
				success: (res) => {
					this.system = res.system
					this.platform = res.platform
				}
			});

			uni.getProvider({
				service: 'oauth',
				success: (res) => {
					if(this.platform=='ios'){
						this.oauthList = ['weixin','apple']; // res.provider;
					}else{
						this.oauthList = ['weixin']; // res.provider;
					}
				}
			});

			if(uni.getSystemInfoSync().platform == 'ios'){
				var WXApi = plus.ios.import("WXApi");
				var isWXInstalled = WXApi.isWXAppInstalled();
				this.isWXInstalled = isWXInstalled
			}
			
			// 注册协议
			if(!this.configData) this.shopConfig();
			
			// 是否开启注册
			this.colseRegister();
			
			// 短信快捷登录
			if(!this.loginMode) this.$store.dispatch('setImgVerify');
			
			// 隐私声明
			this.privacyActicle();
			
			// #endif
		},
		onReady() {
			this.getClientHight();
		},
		watch:{
			username(){
				this.disabled = !this.username
			},
			mobile(){
				this.disabled = !this.mobile
			},
			privacyShow(){
				let that = this
				let second = 6;
	
				//if(that.privacyShow) that.privacyActicle();
	
				if(that.privacyShow && !that.checked){
					that.privacyDisabled = true
					that.timer = setInterval(()=>{
						second --
						if(second){
							that.privacy_button_text = '我已阅读' + '('+ second +'s)'
						}else{
							that.privacy_button_text = '我已阅读'
							that.privacyDisabled = false
							clearInterval(that.timer)
						}
					},1000)
				}else{
					that.privacy_button_text = that.checked ? '我已阅读' : '我已阅读(6s)'
					clearInterval(that.timer)
				}
			},
			loginMode(){
				if(!this.loginMode){
					this.$store.dispatch('setImgVerify'); 
					this.username = '';
				}else{ 
					this.mobile = '';
				}
			},
		},
		methods: {
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			getClientHight() {
				let that = this;
				uni.getSystemInfo({
					success(res) {
						that.clientHight = res.screenHeight;
						that.footerHight = that.clientHight * 0.3;
					}
				})
			}, 
			handlePwdShow() {
				this.pwd = this.pwd == 'password' ? 'text' : 'password';
			},
			scopeSession() {
				uni.login({
					provider: 'weixin',
					success: (res) => {
						this.$store.dispatch('getScopeAppSession', {
							code: res.code
						}).then(sessionRes => {
							uni.setStorage({
								key: 'scopeSession',
								data: sessionRes.data
							});

							this.scopeSessions = sessionRes.data;
						})
					},
					fail: (res) => {
						uni.showToast({
							title: res.errMsg,
							icon: 'none'
						});
					}
				});
			},
			getUserProfile(e){
				uni.getUserProfile({
					desc : '用于完善用户资料',  
					lang : 'zh_CN',  
					success: (profileRes) => {
						uni.setStorage({ key: 'scopeUserInfo', data: profileRes.userInfo });
						this.scopeUserInfo = profileRes.userInfo;
						
						this.scope = 'phoneNumber';
					},
					fail: (infoRes) => {
						uni.showToast({ title: '获取用户资料授权失败，请重新操作', icon: 'none' });
					},
				});
			},
			getUserInfo(e) {
				uni.getUserInfo({
					provider: 'weixin',
					success: (infoRes) => {
						uni.getStorage({
							key: 'scopeSession',
							success: (session) => {
								this.$store.dispatch('getScopeUserInfo', {
									sessionKey: session.data.session_key,
									iv: infoRes.iv,
									encryptData: infoRes.encryptedData
								}).then(res => {
									this.scopeSessions = {
										openid: res.data.openId,
										unionid: res.data.unionId,
										session_key: session.data.session_key,
										expires_in: session.data.expires_in
									}
								})
							}
						});
						
						uni.setStorage({ key: 'scopeUserInfo', data: infoRes.userInfo });
						this.scopeUserInfo = infoRes.userInfo;
						this.scope = 'phoneNumber';
					},
					fail: (infoRes) => {
						uni.showToast({
							title: this.$t('lang.authorization_fail_notic'),
							icon: 'none'
						});
					}
				})
			},
			getPhoneNumber(e) {
				//读取本地scopeSession
				uni.getStorage({
					key: 'scopeSession',
					complete: (res) => {
						this.$store.dispatch('getScopePhoneNumber', {
							sessionKey: res.data.session_key,
							iv: e.detail.iv,
							encryptData: e.detail.encryptedData
						}).then(obj => {
							if (obj.status == 'success') {
								let o = {}
								
								if (this.delta) {
									o = {
										scopeSessions: this.scopeSessions,
										phoneNumber: obj.data.phoneNumber,
										userInfo: this.scopeUserInfo,
										delta: this.delta,
										parent_id: this.parent_id,
										platform: uni.getStorageSync('platform'),
									}
								} else {
									o = {
										scopeSessions: this.scopeSessions,
										phoneNumber: obj.data.phoneNumber,
										userInfo: this.scopeUserInfo,
										parent_id: this.parent_id,
										platform: uni.getStorageSync('platform'),
									}
								}
								
								this.$store.dispatch('getScopeAppLogin', o);
							}
						});
					}
				});
			},
			formSubmit(e) {
				if(this.loginMode){
					// 账号密码登录
					var rule = [{
							name: "username",
							checkType: "notnull",
							checkRule: "",
							errorMsg: this.$t('lang.username_notic')
						},
						{
							name: "password",
							checkType: "notnull",
							checkRule: "",
							errorMsg: this.$t('lang.password_notic')
						},
					];
				}else{
					//短信快捷登录
					var rule = [
						{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.captcha_img_not_null')},
						{name:"mobile", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.mobile_not_null')},
						{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
					];
				}
				
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				
				if (checkRes) {
					if(!this.checked){
						uni.showToast({ title:'请勾选并同意协议条款', icon:'none' });
						return
					}
					
					if(this.loginMode){
						// 账号密码登录
						var o = {
							username: e.detail.value.username,
							password: e.detail.value.password,
							delta: this.delta ? this.delta : ''
						}
						this.$store.dispatch('userLogin', o)
					}else{
						//短信快捷登录
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
												uni.switchTab({ url:'/pages/user/user' });
											}
										});
									}
								});
							}else{
								uni.showToast({ title:res.errors.message, icon:'none' })
							}
						})
					}
				} else {
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			},
			thirdPartyLink(value) {
				uni.login({
					provider: value,
					success: (res) => {
						uni.showLoading({title: this.$t('lang.synchronous_login')});
						uni.getUserInfo({
							provider: value,
							success: (infoRes) => {
								uni.setStorage({
									key: 'scopeUserInfo',
									data: infoRes.userInfo
								});

								// 验证授权用户
								let data = {
									userInfo:infoRes.userInfo,
									type:value,
									platform:uni.getStorageSync('platform')
								}
								
								this.$store.dispatch("userCallback",data).then(result=>{
									uni.hideLoading();
									if(result.status == 'success'){
										if (result.data.login === 1) {
											uni.setStorageSync("token",result.data.token)
											if (this.delta) {
												uni.navigateBack({
													delta:1
												})
											}else{
												uni.switchTab({
													url: '/pages/user/user'
												})
											}
										}else{
											let o = {
												type:result.data.type,
												unionid:result.data.unionid,
												platform:uni.getStorageSync('platform')
											}
											//是否去绑定会员
											if(result.data.code == 42201){
												uni.showModal({
													content:result.data.msg,
													cancelText:'跳过',
													confirmText:'去绑定',
													success: (modelResult) => {
														if(modelResult.confirm){
															uni.navigateTo({
																url:'/pagesB/login/bindUser/bindUser?params='+ JSON.stringify(o) + '&delta=' + this.delta
															})
														}else if(modelResult.cancel){
															//添加跳过参数
															o.step = 0;
															uni.showLoading({title: this.$t('lang.login_loading')});
															this.$store.dispatch('bindRegister', o).then(bindResult=>{
																uni.hideLoading();
																if(bindResult.status == 'success'){
																	if(bindResult.data.login === 1){
																		uni.setStorageSync("token",bindResult.data.token);
																		if (this.delta) {
																			uni.navigateBack({
																				delta:1
																			})
																		}else{
																			uni.switchTab({
																				url: '/pages/user/user'
																			})
																		}
																	}
																}else{
																	uni.showModal({
																		content:bindResult.errors.message || 'error'
																	})
																}
															});
														}
													}
												})
											}
										}
									}else{
										uni.showToast({
											title: result.errors.message || 'error',
											icon: 'none'
										});
									}
								})
							},
							fail: (infoRes) => {
								uni.showToast({
									title: this.$t('lang.authorization_fail_notic'),
									icon: 'none'
								});
							}
						})
					},
					fail:(res)=>{
						console.log(res)
					}
				})
			},
			shopConfig(){
				uni.request({
					url:`${this.websiteUrl}/api/shop/config`,
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						this.configData = data;
						
						this.register_article_id = data.register_article_id;
					}
				});
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
			},
			colseRegister(){
				uni.request({
					url:`${this.websiteUrl}/api/user/login_config`,
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						this.shop_reg_closed = data.shop_reg_closed
					}
				});
			},
			async privacyActicle(){
				if(this.configData){
					uni.request({
						url:`${this.websiteUrl}/api/article/show`,
						method:'POST',
						data:{
							id:this.register_article_id
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash': uni.getStorageSync('client_hash')
						},
						success: ({ data }) => {
							if(data.status == 'success'){
								this.privacyActicleContent = data.data.content;
								this.privacyActicleTitle = data.data.title;	
							}else{
								uni.showToast({ title: '未设置隐私协议', icon:"none" });
							}
						}
					});
				}
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
			onClickBack(){
				uni.switchTab({
					url:'/pages/index/index'
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
/* app端样式 */
.container-login{
	background: #fff;
	position: relative;
	padding: 20% 30px 0;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	box-sizing: border-box;
	
	.login-head{
		position: absolute;
		left: 30px;
		top: 8%;
	}
	
	.login-form{
		.logo{
			width: 150rpx;
			height: 150rpx;
			overflow: hidden;
			border-radius: 50%;
			margin: 10% auto;
		}
		
		.input-box{
			display: flex;
			align-items: center;
			justify-content: flex-start;
			height: 30px;
			line-height: 30px;
			padding: 5px 0;
			box-sizing: content-box;
			border-bottom: 1px solid #dcdcdc;
			margin-bottom: 5%;

			&__left{
				flex: 1;
				display: flex;
				align-items: center;
				justify-content: flex-start;

				.iconfont{
					margin-right: 10px;
					color: #666;
					font-size: 20px;
					
					&.icon-guanbi{
						margin-right: 0;
						font-size: 12px;
						margin-left: 10px;
						line-height: 32px;
					}
				}

				.input{
					flex: 1;
					width: 100%;
				}
			}

			&__right{
				display: flex;
				justify-content: flex-start;
				align-items: center;

				.iconfont{
					flex: 1;
					margin-right: 10px;
					color: #999;
					position: relative;
					padding: 0 10px 0 10px;

					&.active{
						color: #f92028;
					}

					&:after{
						content:'';
						position: absolute;
						width: 1.5px;
						right: 0;
						top: 5px;
						bottom: 5px;
						background: #ddd;
					}
				}
				
				.code-box{
					width: 80px;
					height: 30px;
				}

				.forgetpwd{
					color: #4b89dc;
					font-size: 12px;
					display: block;
				}

				.send{
					height: 30px;
					line-height: 30px;
					padding: 0 12px;
					border: 1px solid #dcdcdc;
					border-radius: 15px;
					font-size: 12px;
					color: #999;
					margin-left: 10px;
				}
			}
		}

		.signup-button{
			margin-top: 10%;

			.btn{
				height: 40px;
				padding: 0;
				border-radius: 20px;
				line-height: 40px;
				margin-bottom: 5%;
				font-size: 28rpx;
				color: #FFFFFF;
				background: #FFFFFF;
				
				&.btn-lg-red{
					background: linear-gradient(-88deg, #ff4f2e, #f91f28);
				}

				&.btn-bor-red{
					border: 1px solid #f92028;
					color: #f92028;
					font-weight: 700;
				}

				&.btn-disabled{
					cursor: not-allowed;
					opacity: 0.4;
				}
			}

			.tips{
				display: flex;
				justify-content: center;
				align-items: center;
				font-size: 12px;
				color: #333;

				.go-register{
					margin-left: 5px;
					color: #f92028;
				}
			}
		}
	}
	
	.bottom{
		 padding-bottom: env(safe-area-inset-bottom);
	}
	
	.quick-login{
		position: relative;
		margin: 10% auto 0;
		width: 90%;
		
		.title {
			font-size: 28rpx;
			position: relative;
			text-align: center;
			
			text {
				position: relative;
				background: #FFFFFF;
				z-index: 2;
				padding: 0 20px;
				color: #999;
			}
			
			&:after {
				content: "";
				position: absolute;
				z-index: 1;
				pointer-events: none;
				background-color: #dddddd;
				height: 1px;
				left: 0;
				right: 0;
				top: 50%;
				z-index: 0;
			}
		}

		.quick-login-items{
			margin: 15px 0 20px;
			display: flex;
			justify-content: center;
			align-items: center;
			
			.quick-login-item {
				margin-right: 15px;
				line-height: 0;
				
				image {
					width: 100upx;
					height: 100upx;
				}
				
				&:last-child{
					margin-right: 0;
				}
			}
		}
	}
	
	.privacy{
		display: flex;
		justify-content: flex-start;
		align-items: center;
		padding-top: 10px;
		
		.checkbox{
			margin-right: 0;
			
			.checkbox-icon{ margin: 1px 5px 0 0; width:28rpx; height: 28rpx;}
			.txt{ color: #666666; font-size: 25rpx;}
			.href{ color: #4b89dc; font-size: 25rpx;}
		}
	}
}

/* 隐私协议 */
.show-popup-privacy{
	.title{
		position: relative;
		justify-content: center;
		
		.iconfont{
			position: absolute;
			right: 30rpx;
		}
	}
	.article-content{
		height: calc(100% - 50px);
		padding: 0 20rpx;
		overflow-y: auto;
	}
	
	.footer{
		.btn{
			height: 40px;
			padding: 0;
			border-radius: 20px;
			line-height: 40px;
			background: linear-gradient(-88deg, #ff4f2e, #f91f28);
			font-size: 28rpx;
			
			&.btn-disabled{
				cursor: not-allowed;
				opacity: 0.4;
			}
		}
	}
}

/* 小程序登录 */
.login-section {
	padding: 40% 0 0;
	
	.header {
		width: 160upx;
		height: 160upx;
		border-radius: 100%;
		overflow: hidden;
		margin: 0 auto 10upx;
		border: 3px solid #eee;
	}
	
	.user-name {
		text-align: center;
		font-size: 32upx;
		margin: 0 60upx 0;
		padding-bottom: 50upx;
		
		&:before {
			content: " ";
			position: absolute;
			left: 0;
			bottom: 0;
			right: 0;
			height: 1px;
			border-bottom: 1px solid #e5e5e5;
			color: #e5e5e5;
			-webkit-transform-origin: 0 0;
			transform-origin: 0 0;
			-webkit-transform: scaleY(0.5);
			transform: scaleY(0.5);
			z-index: 1;
		}
	}
	
	.cont {
		padding: 40upx 60upx;
		font-size: 30upx;
		color: #999;
		text-align: center
	}
	
	.btn {
		margin: 30upx 60upx 50upx 60upx;
		
		button {
			padding: 0;
			font-size: 32upx;
			color: #fff;
			border-radius: 50upx;
			border: 0 !important;
			
			&::after {
				content: " ";
				width: 0;
				height: 0;
				position: absolute;
				top: 0;
				left: 0;
				border: none;
				-webkit-transform: scale(0);
				transform: scale(0);
				-webkit-transform-origin: 0 0;
				transform-origin: 0 0;
				box-sizing: border-box;
				border-radius: 0;
			}
		}
	}
}
</style>
