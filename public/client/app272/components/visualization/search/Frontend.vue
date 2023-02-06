<template>
	<view class="search_container">
		<view class="search" :class="{'search-not-home':bSuspend,'position-fixed':scrollFixed}" :style="{backgroundColor: bSuspend ? nBackgroundColor : sBgColor}">
			<block v-if="shopId === 0">
			<!-- #ifdef APP-PLUS -->
			<!-- 这里是状态栏 -->
			<view class="status_bar"></view>
			<view class="logo_wrap">
				<image :src="logo" style="height: 64rpx;" mode="heightFix" @click="linkTo"></image>
				<uni-icons type="scan" size="24" :color="nFontColor" @click="scanClick"></uni-icons>
			</view>
			<!-- #endif -->
			<!-- #ifdef MP-WEIXIN -->
			<image :src="logo"
				:style="{height: menuButtonInfo.height + 'px', margin: `${menuButtonInfo.top}px 0 0 ${vw - menuButtonInfo.right}px`}"
				mode="heightFix" @click="linkTo"></image>
			<!-- #endif -->
			</block>
			<view class="input_main">
				<view class="input_view" @click="searchFocus">
					<text class="placeholder_text" :style="placeholderStyle">{{searchValue}}</text>
					<uni-icons type="search" size="18" :color="tFontColor"></uni-icons>
				</view>
				
				<block v-if="isLogin===true">
					<view class="message" v-if="bMessage">
						<text class="txt" v-if="isUnread"></text>
						<uni-icons type="chat" size="24" :color="nFontColor" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile"></uni-icons>
						<uni-icons type="chat" size="24" :color="nFontColor"  @click="getUserProfile('/pagesB/messagelist/messagelist')" v-else></uni-icons>
					</view>
				</block>
				<block v-else>
					<view class="message" v-if="bMessage">
						<text class="txt" v-if="isUnread"></text>
						<uni-icons type="chat" size="24" :color="nFontColor" @click="message"></uni-icons>
					</view>
				</block>
			</view>
			<view class="mask" v-if="bSuspend"></view>
		</view>
		<view class="seize-seat" v-if="scrollFixed" :style="{'height':height + 'px'}"></view>
		
		<!-- 会员登录获取手机号码 start -->
		<uni-popup :show="getPhoneNumberShow" class="phone_box"  :animation="true">
			<view class="pop_content pop_phone_number">
				<view class="pop_header" style="padding:0;">					
					<text class="pop_title">{{$t('lang.Authorized_Phone_Number')}}</text>
				</view>
				<view class="pop_phone_content">
					<view class="logo">
					  <image src="../../../static/logo.png" class="img"></image>
					</view>
					<text>{{$t('lang.user_bind_phone')}}</text>
				</view>
				<view class="btn">					
					<button type="primary"  style="border-radius: 20px;height: 70rpx; line-height: 70rpx;font-size:16px;" open-type="getPhoneNumber" @getphonenumber="getPhoneNumber" >{{$t('lang.confirm_on')}}</button>
				</view>
			</view>
		</uni-popup>
		<!-- 会员登录获取手机号码 end -->
	</view>
</template>

<script>
	import uniNavBar from '@/components/uni-nav-bar.vue'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import universal from '@/common/mixins/universal.js';

	export default {
		name: 'search',
		props: ["module", "preview", 'shopId', 'scrollFixed', 'isUnread', 'fristBackgroundColor'],
		mixins: [universal],
		data() {
			return {
				scopeUserInfo: {},
				scope: 'userInfo',
				scopeSessions: {},
				canIUseProfile:false,
				getPhoneNumberShow:false,
				isLoginData:false,
				leftState: false,
				nFontColor: '#FFFFFF',
				nBackgroundColor: 'inherit',
				tFontColor: '#999',
				fixed: true,
				titleNView: false,
				statusBar: false,
				height: 0,
				menuButtonInfo: {},
				logo: '',
				logoLink: '',
				vw: 375
			}
		},
		components: {
			uniNavBar,
			uniIcons,
			uniPopup
		},
		created() {
			const {
				windowWidth
			} = uni.getSystemInfoSync();
			this.vw = windowWidth;
			// #ifdef APP-PLUS
			this.logo = uni.getStorageSync('configData').app_top_img || ''
			this.logoLink = uni.getStorageSync('configData').app_top_url || ''
			if (this.shopId == 0) {
				this.leftState = true;
				this.nBackgroundColor = "#f34646";
				this.titleNView = true;
				this.statusBar = true
			}
			// #endif

			// #ifdef MP-WEIXIN
			this.logo = uni.getStorageSync('configData').wxapp_top_img || ''
			this.logoLink = uni.getStorageSync('configData').wxapp_top_url || ''
			this.statusBar = true
			this.menuButtonInfo = uni.getMenuButtonBoundingClientRect();
			// #endif

		},
		mounted() {
			let _this = this;
			_this.$nextTick(() => {
				let info1 = uni.createSelectorQuery().in(this).select(".search");
				setTimeout(() => {
					info1.boundingClientRect(function(data) {
						if (data) {
							_this.height = data.height
							getApp().globalData.navigationBarHeight = data.height
						}
					}).exec();
				}, 500)
			})
		},
		computed: {
			
			isLogin(){
				let isLogin = uni.getStorageSync('bindMobilePhone') && this.$isLogin() ? false : true;
				//#ifdef MP-WEIXIN
				if(isLogin){					
					this.scopeSession();					
					// 判断小程序是否存在getUserProfile
					if(uni.getUserProfile) this.canIUseProfile = true;
				}
				//#endif	
							
				//#ifdef APP-PLUS
				isLogin = false;
				//#endif			
							
				return isLogin
			},
			
			searchValue() {
				if (this.shopId == 0) {
					return this.getText({
						dataNext: "allValue",
						attrName: "searchValue",
						defaultValue: '输入搜索关键词'
					})
				} else {
					return '输入搜索关键词'
				}
			},
			sBgColor() {
				if (this.shopId == 0) {
					if (!this.scrollFixed) {
						return this.getText({
							dataNext: "allValue",
							attrName: "bgColor",
							defaultValue: "#f34646"
						})
					} else {
						return this.fristBackgroundColor
					}
				} else {
					return this.fristBackgroundColor
				}
			},
			bMessage() {
				let isMessageSel = this.module ? this.module.isMessageSel : 0
				return isMessageSel == 0 && this.shopId == 0 ? true : false
			},
			bSuspend() {
				return this.shopId == 0 ? false : true
			},
			placeholderStyle() {
				return "color:" + this.tFontColor
			}
		},
		methods: {
			
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
						});						
					},
					fail: (res) => {
						uni.showToast({
							title: res.errMsg,
							icon: 'none'
						});
					}
				});
			},
			getUserProfile(toUrl){
				
				//优化兼容已登录则跳转相应地址
				if(this.$isLogin()){
					
					if(toUrl == ''){
						uni.redirectTo({
							url: '/pagesB/profile/profile'
						});
					}else{
						uni.redirectTo({
							url: toUrl
						});
					}
					
					return false;
				}
				
				uni.getUserProfile({
					desc : this.$t('lang.user_login_desc'),  
					lang : 'zh_CN',  
					success: (profileRes) => {
						uni.setStorage({ key: 'scopeUserInfo', data: profileRes.userInfo });
						this.scopeUserInfo = profileRes.userInfo;						
						this.scope = 'phoneNumber';
						this.getPhoneNumberShow = true;
						
						//跳转链接
						uni.setStorage({
							key: 'scopeUrl',
							data: toUrl
						});
					},
					fail: (infoRes) => {
						uni.showToast({ title: this.$t('lang.user_login_fail'), icon: 'none' });
					},
				});
			},
			getUserInfo(e) {				
				uni.getUserInfo({
					provider: 'weixin',
					success: (infoRes) => {
						console.log(infoRes);
						let session = {data:this.scopeSessions};
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
				let res = {data:this.scopeSessions};
				this.getPhoneNumberShow = false;
				this.$store.dispatch('getScopePhoneNumber', {
					sessionKey: res.data.session_key,
					iv: e.detail.iv,
					encryptData: e.detail.encryptedData
				}).then(obj => {				
					if (obj.status == 'success') {
						let o = {}
						
						if (this.delta) {
							o = {
								type:"user",
								scopeSessions: this.scopeSessions,
								phoneNumber: obj.data.phoneNumber,
								userInfo: this.scopeUserInfo,
								delta: this.delta,
								parent_id: this.parent_id,
								platform: uni.getStorageSync('platform'),
							}
						} else {
							o = {
								type:"user",
								scopeSessions: this.scopeSessions,
								phoneNumber: obj.data.phoneNumber,
								userInfo: this.scopeUserInfo,
								parent_id: this.parent_id,
								platform: uni.getStorageSync('platform'),
							}
						}						
						this.$store.dispatch('getScopeAppLogin', o).then(()=>{
							setTimeout(()=>{
								
								let scopeUrl = uni.getStorageSync('scopeUrl');
								
								if(scopeUrl == ''){
									
								}else{
									uni.redirectTo({
										url: scopeUrl
									});
								}
								
							},1000)
						});
					}
				});
			},
			
			linkTo() {
				if (this.logoLink) {
					if (this.logoLink.startsWith('http')) {
						this.$outerHref(this.logoLink)
					} else {
						this.$outerHref(this.logoLink, 'app')
					}
				}
			},
			message() {
				if (this.$isLogin()) {
					uni.navigateTo({
						url: '/pagesB/messagelist/messagelist'
					})
				} else {
					uni.showModal({
						content: "您需要登录会员!",
						success: (res) => {
							if (res.confirm) {
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								})
							}
						}
					})
				}
			},
			searchFocus() {
				//全局变量integration赋值
				//getApp().globalData.integration = 0;
				uni.navigateTo({
					url: '/pages/search/search?shopId=' + this.shopId
				});
			},
			scanClick() {
				uni.scanCode({
					success: (res) => {
						if (this.$isLogin()) {
							let result = JSON.parse(res.result);
							let sid = result.sid;
							let url = result.url;
							let timestamp = Date.parse(new Date()) / 1000;
							uni.request({
								url: this.websiteUrl + '/api/appqrcode/scancode',
								method: 'POST',
								header: {
									'Content-Type': 'application/json',
									'token': uni.getStorageSync('token'),
									'X-Client-Hash': uni.getStorageSync('client_hash')
								},
								data: {
									sid: sid,
									login_time: timestamp
								},
								success: (res) => {
									if (sid) {
										uni.navigateTo({
											url: '/pagesC/scan/scan?sid=' + sid + '&url=' +
												url
										});
									} else {
										uni.showToast({
											title: '请扫描正确的二维码',
											icon: 'none'
										})
									}
								},
								fail: (res) => {
									console.log(res, 2)
								}
							})
						} else {
							uni.navigateTo({
								url: '/pagesB/login/login?delta=1'
							})
						}
					}
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.search_container {
		.status_bar {
			height: var(--status-bar-height);
			width: 100%;
		}

		.input_main {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 16rpx 20rpx;
			/* #ifdef APP-PLUS */
			padding-top: 10rpx;
			/* #endif */
			line-height: 0;
		}

		.input_view {
			flex: auto;
			display: flex;
			justify-content: space-between;
			align-items: center;
			height: 60rpx;
			padding: 0 30rpx;
			border-radius: 30rpx;
			color: #999;
			background-color: #fff;
			.placeholder_text {
				font-size: 24rpx;
			}
		}

		.logo_wrap {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 0 24rpx 0 14rpx;
		}
	}

	/* 以下是原有样式 */
	.search-not-home{ 
		position: fixed; 
		left: 0; 
		right: 0; 
		top: 0; 
		z-index: 99;
	}
	
	.search-not-home .mask {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background: linear-gradient(180deg, rgba(0, 0, 0, .8) 0, transparent);
		z-index: -1;
	}

	.position-fixed {
		position: fixed;
		top: 0;
		z-index: 998;
		width: 100%;
	}
	
	.message{
		position: relative;
		margin-left: 20rpx;
	}
	
	.message .txt{
		background: #fff;
		height: 10upx;
		width: 10upx;
		position: absolute;
		right:-8upx;
		top: 10upx;
		z-index: 99;
		border-radius: 50%;
	}
	.pop_content {
		overflow: hidden;
		width: 675rpx;
		border-radius: 12rpx;
		background-color: #fff;
	}
	
	.pop_content .pop_header{
		text-align: center;
	}
	.pop_content .pop_header .pop_title {
		font-size: 32rpx;
		font-weight: 700;
		line-height: 90rpx;
		color: #282828;
	}
	
	.pop_phone_number{
		position: relative;
		width: 420rpx;
		height: 400rpx;
	}
	.pop_phone_number .pop_header{
		width: 100%;
		border-bottom: 1px solid #ECECEC;
	}
	.pop_phone_number .logo{
		padding-top:20rpx;
		width: 100rpx;
		height: 100rpx;
		margin: 0 auto;
	}
	.pop_phone_number .logo .img{
		border-radius: 50%;
	}
	.pop_phone_number .pop_phone_content{
		margin: 0 auto;
		text-align: center;
		line-height: 45px;	
	}
	.pop_phone_number .btn{
		margin: 0 auto;
		width: 70%;
	}
</style>
