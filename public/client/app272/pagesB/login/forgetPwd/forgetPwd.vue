<template>
	<view class="container-bwg container-register">
		<div class="login-head"><i class="iconfont icon-back" @click="onClickBack"></i></div>
		<view class="login-form">
			<view class="title">{{type == 0 ? '请输入您要找回的账号' : type == 1 ? '获取验证码' : '设置新密码'}}</view>
			<block v-if="type == 0">
				<view class="input-box">
					<view class="input-box__left">
						<icon class="iconfont icon-wodeguanzhu"></icon>
						<input type="text" class="input" v-model="username" name="username" autocomplete="off" :placeholder="$t('lang.enter_username')" />
						<icon class="iconfont icon-guanbi" @click="username = ''" v-show="username"></icon>
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
				<view class="signup-button">
					<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitStep(type)">获取验证码</button>
				</view>
			</block>
			<block v-else-if="type == 1">
				<view class="forgetpwd-warp">
					<view class="send-yzm">
						<view class="text" :class="{'text-email':curType == 'email'}">{{ curType == 'mobile_phone' ? userinfo.mobile_phone_sign : userinfo.email_sign}}</view>
						<view class="send" @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</view>
						<view class="send" v-else>{{ button_text }}</view>
					</view>
					
					<view class="item" @tap='KeyboarOpen'>
						<password-input :numLng='sms' :plaintext="false"></password-input>
					</view>
					<view class="tip">请输入6位数字验证码</view>
						
					<number-keyboard tabBar ref='KeyboarHid' @input='KeyboarVal' psdLength='6'></number-keyboard>
				</view>
				<view class="signup-button">
					<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitStep(type)">下一步</button>
					<view class="tips">
						<view class="go-register" v-if="curType == 'mobile_phone' && userinfo.is_email == 1" @click="tabClick('email')">邮箱验证</view>
						<view class="go-register" v-if="curType == 'email' && userinfo.is_mobile_phone == 1" @click="tabClick('mobile_phone')">手机验证</view>
					</view>
				</view>
			</block>
			<block v-else-if="type == 2">
				<view class="input-box">
					<view class="input-box__left">
						<icon class="iconfont icon-jiesuo"></icon>
						<input :type="pwd" class="input" v-model="password" name="password" autocomplete="off" :placeholder="$t('lang.enter_password')" />
					</view>
					<view class="input-box__right">
						<icon class="iconfont icon-liulan1" :class="{'active':pwd == 'text'}" @click="handlePwdShow"></icon>
					</view>
				</view>
				<view class="signup-button">
					<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitBtn">设置新密码</button>
				</view>
				<view class="tip">为保障您账户安全，请您不要设置与邮箱密码相同的账户登录密码，谨防诈骗</view>
			</block>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import numberKeyboard from '@/components/number-keyboard/number-keyboard.vue'
	import passwordInput from '@/components/password-input/password-input.vue'
	export default {
		data() {
			return {
				disabled:true,
				pwd:'password',
				username:'',
				imgverifyValue:'',
				sms:'',
				password:'',
				button_text:this.$t('lang.send_again_60'),
				send_again:this.$t('lang.send_again'),
				configData: uni.getStorageSync('configData'),
				button_type:true,
				type:0,
				userinfo:'',
				curType:''
			};
		},
		components:{
			numberKeyboard,
			passwordInput
		},
		computed:{
			...mapState({
				captcha: state => state.common.imgVerify.captcha,
				client: state => state.common.imgVerify.client,
			}),
			isTypeValue(){
				var obj = ''
				if(this.userinfo.is_mobile_phone == 1 && this.userinfo.is_email == 1){
					obj = 'mobile_phone'
				}else{
					if(this.userinfo.is_mobile_phone == 1 || this.userinfo.is_email == 1){
						if(this.userinfo.is_mobile_phone == 1){
							obj = 'mobile_phone'
						}
						if(this.userinfo.is_email == 1){
							obj = 'email'
						}
					}else{
						obj = ''
					}
				}
				return obj
			}
		},
		onLoad(){
			this.clickCaptcha();
		},
		watch:{
			username(){
				this.disabled = this.username ? false : true;
			},
			isTypeValue(){
				this.curType = this.isTypeValue
			},
			password(){
				this.disabled = this.password ? false : true; 
			},
			type(){
				this.sms = ''
			}
		},
		methods:{
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			handlePwdShow() {
				this.pwd = this.pwd == 'password' ? 'text' : 'password';
			},
			sendVerifyCode() {
				let that = this
				if(that.curType == 'mobile_phone'){
					let o = {
						mobile:that.userinfo.mobile_phone,
						send_from:'reset_password'
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
				}else if(that.curType == 'email'){
					uni.request({
						url: this.websiteUrl + '/api/user/reset_email',
						method: 'POST',
						data: {
							email:that.userinfo.email
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
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
							}else{
								
							}
						},
						fail: (res) => {
							console.log(JSON.stringify(res))
						}
					})
				}
				
				this.$refs.KeyboarHid.open();
			},
			submitBtn(){
				if(!(/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,16}$/.test(this.password))){
					uni.showToast({ title: '请输入8-16位含大小写字母、数字组合', icon: "none" });
					return
				}
				
				uni.request({
					url: this.websiteUrl + '/api/user/reset_password',
					method: 'POST',
					data: {
						user_name:this.userinfo.user_name,
						new_password:this.password
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data
						if(data.status == 'success'){
							uni.showToast({ title: '密码修改成功', icon: "none" });
							
							uni.redirectTo({
								url:'../login'
							})
						}else{
							uni.showToast({ title: data.errors.message, icon: "none" });
						}
					},
					fail: (res) => {
						console.log(JSON.stringify(res))
					}
				})
			},
			submitStep(type){
				if(type == 0){
					if(this.username == ''){
						uni.showToast({ title: '用户名不能为空', icon: "none" });
						return false
					}
					
					if(this.imgverifyValue == ''){
						uni.showToast({ title: this.$t('lang.captcha_img_not_null'), icon: "none" });
						return false
					}
					
					uni.request({
						url: this.websiteUrl + '/api/user/forget',
						method: 'POST',
						data: {
							user_name:this.username,
							captcha:this.imgverifyValue,
							client:this.client
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							let data = res.data
							if(data.status == 'success'){
								this.userinfo = data.data;
								this.type = 1;
								this.disabled = true;
							}else{
								uni.showToast({ title: data.errors.message, icon: "none" });
							}
						},
						fail: (res) => {
							console.log(JSON.stringify(res))
						}
					})
				}else if(this.type == 1){
					if(this.sms == ''){
						uni.showToast({ title: '请填写验证码', icon: "none" });
						return false
					}
					
					if(this.curType == 'mobile_phone'){
						uni.request({
							url: this.websiteUrl + '/api/user/verification_sms',
							method: 'POST',
							data: {
								mobile_phone:this.userinfo.mobile_phone,
								code:this.sms
							},
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								let data = res.data
								if(data.status == 'success'){
									this.type = 2;
								}else{
									uni.showToast({ title: data.errors.message, icon: "none" });
								}
							},
							fail: (res) => {
								console.log(JSON.stringify(res))
							}
						})
					}else if(this.curType == 'email'){
						uni.request({
							url: this.websiteUrl + '/api/user/verification_email',
							method: 'POST',
							data: {
								email:this.userinfo.email,
								code:this.sms
							},
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								let data = res.data
								if(data.status == 'success'){
									this.type = 2;
								}else{
									uni.showToast({ title: data.errors.message, icon: "none" });
								}
							},
							fail: (res) => {
								console.log(JSON.stringify(res))
							}
						})
					}
				}
			},
			tabClick(val){
				this.curType = val
			},
			//打开键盘
			KeyboarOpen(e) {
				this.$refs.KeyboarHid.open();
			},
			//输入的值
			KeyboarVal(val) {
				this.sms = val;
				
				this.disabled = false;
			},
			onClickBack(){
				if(this.type > 0){
					this.type --
	
					if(this.type == 0){
						this.$store.dispatch('setImgVerify');
						this.userinfo = '';
						this.username = '';
						this.imgverifyValue = '';
					}
				}else{
					uni.navigateBack({ delta:1 });
				}
			},
		}
	}
</script>

<style lang="scss" scoped>
.container-bwg{
	padding: 20% 30px 0;
	overflow: hidden;
	height: 100vh;
	box-sizing: border-box;
	position: relative;
	
	.login-head{
		position: absolute;
		left: 25px;
		top: 8%;
	}
	
	&.container-register{
		.login-form{
			margin-top: 30%;
		}
	}
	
	.login-form{
		.title{
			font-size: 18px;
			font-weight: 700;
			color: #000;
			margin-bottom: 10%;
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
					margin-right: 0;
					color: #999;
					position: relative;
					padding: 0 0 0 10px;
	
					&.active{
						color: #f92028;
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
					text-align: center;
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
	
	.send-yzm{
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 5%;

		.text{
			font-size: 24px;
			
			&.text-email{
				font-size: 16px;
			}
		}

		.send{
			height: 35px;
			line-height: 35px;
			padding: 0 20px;
			border: 1px solid #dcdcdc;
			border-radius: 18px;
			font-size: 12px;
			color: #999;
			margin-left: 10px;
		}
	}
	
	.tip{
		margin: 15rpx 0 0 0;
		color: #c5c5c5;
		font-size: 26rpx;
		text-align: left;
	}
}
</style>
