<template>
	<view class="container-bwg container-register">
		<div class="login-head"><i class="iconfont icon-back" @click="onClickBack"></i></div>
		<view class="login-form">
			<view class="title">{{$t('lang.register')}}</view>
			<form @submit="formSubmit">
				<view class="input-box">
					<view class="input-box__left">
						<icon class="iconfont icon-shouji1"></icon>
						<input type="tel" class="input" v-model="mobile" name="mobile" autocomplete="off" :placeholder="$t('lang.enter_mobile')" />
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
				<view class="input-box">
					<view class="input-box__left">
						<icon class="iconfont icon-jiesuo"></icon>
						<input :type="pwd" class="input" v-model="password" name="password" autocomplete="off" :placeholder="$t('lang.enter_password')" />
					</view>
					<view class="input-box__right">
						<icon class="iconfont icon-liulan1" :class="{'active':pwd == 'text'}" @click="handlePwdShow"></icon>
					</view>
				</view>
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
					<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" form-type="submit">{{$t('lang.register')}}</button>
				</view>
			</form>
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
						<button type="warn" class="btn" :class="{'btn-disabled':privacyDisabled}" :disabled="privacyDisabled" @click="privacyShow = false; checked = true">{{privacy_button_text}}</button>
					</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import jyfParser from "@/components/jyf-parser/jyf-parser";
	
	var graceChecker = require("@/common/graceChecker.js");
	export default {
		data() {
			return {
				pwd: 'password',
				mobile:'',
				imgverifyValue:'',
				sms:'',
				password:'',
				button_text: this.$t('lang.send_again_60'),
				send_again:this.$t('lang.send_again'),
				button_type:true,
				register_article_id:'',
				parent_id:uni.getStorageSync('user_id') ? uni.getStorageSync('user_id') : 0,
				disabled:true,
				// 隐私协议
				configData: '',
				checked:false,
				privacyShow:false,
				privacyActicleContent:'',
				privacyActicleTitle:'',
				privacyDisabled:true,
				privacy_button_text:'我已阅读(6s)',
				timer:null,
			};
		},
		components:{
			uniIcons,
			uniPopup,
			jyfParser
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
		watch:{
			mobile(){
				this.disabled = !this.mobile
			},
			privacyShow(){
				let that = this
				let second = 6;
				
				if(that.privacyShow) that.privacyActicle();
				
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
				if(!this.loginMode) this.$store.dispatch('setImgVerify');
			},
		},
		onLoad(){
			this.clickCaptcha();

			// 注册协议
			this.shopConfig();
			
			// 隐私声明
			this.privacyActicle();
		},
		methods:{
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			handlePwdShow() {
				this.pwd = this.pwd == 'password' ? 'text' : 'password';
			},
			sendVerifyCode() {
			    let o = {
			        captcha: this.imgverifyValue,
			        client: this.client,
			        mobile: this.mobile,
					act:'register'
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
			formSubmit(e){
				var rule = [
					{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.captcha_img_not_null')},
					{name:"mobile", checkType : "notnull", checkRule:"",  errorMsg: this.$t('lang.mobile_not_null')},
					{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
					{name:"password", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.password_notic')},
					{name:"password", checkType : "length6", checkRule:"",  errorMsg:this.$t('lang.new_password_not_length6')},
				];

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				
				if(checkRes){
					if(!this.checked){
						uni.showToast({ title:'请勾选并同意协议条款', icon:'none' });
						return
					}
					
					this.$store.dispatch('userRegister',{
						client:this.client,
						mobile:this.mobile,
						code:this.sms,
						pwd:this.password,
						parent_id:this.parent_id,
						allow_login:0
					}).then(res=>{
						if(res.status == 'success'){
							uni.showToast({
								title:this.$t('lang.register_success'),
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
			async privacyActicle(){
				if(this.configData){
					uni.request({
						url:`${this.websiteUrl}/api/article/show`,
						method:'POST',
						data:{
							id:this.register_article_id || this.configData.register_article_id
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
			onClickBack(){
				uni.navigateBack({ delta:1 });
			}
		},
	}
</script>

<style lang="scss" scoped>
.container-bwg{
	padding: 20% 30px 0;
	overflow: hidden;
	height: 100vh;
	box-sizing: border-box;
	
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
	
	.privacy{
		display: flex;
		justify-content: flex-start;
		align-items: center;
		padding-top: 10px;
		
		.checkbox{
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
</style>
