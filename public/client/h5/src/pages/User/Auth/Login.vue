<template>
	<div class="container container-login" :style="{'min-height': docmHeight2 + 'px'}">
		<template v-if="!isWeiXinSta">
			<div class="login-head"><i class="iconfont icon-close" @click="onClickBack"></i></div>
			<div class="login-form">
				<div class="logo" v-if="configData"><img :src="configData.wap_logo" class="img" /></div>
				<template v-if="loginMode">
					<div class="input-box">
						<div class="input-box__left">
							<i class="iconfont icon-wodeguanzhu"></i>
							<input type="text" class="input" v-model="username" autocomplete="off" :placeholder="$t('lang.enter_username')" />
							<i class="iconfont icon-guanbi" @click="username = ''" v-show="username"></i>
						</div>
					</div>
					<div class="input-box">
						<div class="input-box__left">
							<i class="iconfont icon-jiesuo"></i>
							<input :type="pwd" class="input" v-model="password" autocomplete="off" :placeholder="$t('lang.enter_password')" />
						</div>
						<div class="input-box__right">
							<i class="iconfont icon-liulan1" :class="{'active':pwd == 'text'}" @click="handlePwdShow"></i>
							<router-link :to="{name:'forgetpwd'}" class="forgetpwd">{{$t('lang.forget_password')}}</router-link>
						</div>
					</div>
				</template>
				<template v-else>
					<div class="input-box">
						<div class="input-box__left">
							<i class="iconfont icon-shouji1"></i>
							<input type="text" class="input" v-model="mobile" autocomplete="off" :placeholder="$t('lang.enter_mobile')" />
							<i class="iconfont icon-guanbi" @click="mobile = ''" v-show="mobile"></i>
						</div>
					</div>
					<div class="input-box">
						<div class="input-box__left">
							<i class="iconfont icon-tupian"></i>
							<input type="text" class="input" v-model="imgverifyValue" autocomplete="off" :placeholder="$t('lang.captcha_img')" />
						</div>
						<div class="input-box__right">
							<img :src="captcha" class="j-verify-img" @click="clickCaptcha" />
						</div>
					</div>
					<div class="input-box">
						<div class="input-box__left">
							<i class="iconfont icon-anquan"></i>
							<input type="tel" class="input" v-model="sms" maxlength="6" autocomplete="off" :placeholder="$t('lang.get_sms_code')" />
						</div>
						<div class="input-box__right">
							<div class="send" @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</div>
                      		<div class="send" v-else>{{ button_text }}</div>
						</div>
					</div>
				</template>
				<div class="privacy">
	            	<div class="checkbox">
	            		<van-checkbox v-model="checked">我已阅读并同意</van-checkbox>
	            		<span class="privacy-link" @click="privacyShow = true">《{{ privacyActicleTitle ? privacyActicleTitle : '隐私协议' }}》</span>
					</div>
	            </div>
				<div class="signup-button">
					<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitBtn">
						{{ loginMode ? '账号密码登录' : '短信快捷登录' }}
					</button>
					<div class="btn btn-bor-red" v-if="shop_reg_closed == 0" @click="loginMode = !loginMode">
						{{ loginMode ? '短信快捷登录' : '账号密码登录' }}
					</div>
					<div class="tips">还没有账号?<router-link :to="{name:'register'}" class="go-register" v-if="shop_reg_closed == 0">立即去注册</router-link></div>
				</div>
			</div>
			<div class="quick-login">
				<template v-if="oauthList && oauthList.length > 0 && oauthHidden && shop_reg_closed == 0">
					<p><span>其他登录方式</span></p>
					<div class="quick-login-items">
		                <a href="javascript:;" @click="thirdPartyLink(item.type)" v-for="(item,index) in oauthList" :key="index"><img :src="thirdPartyImg[index]" class="img">
		                </a>
		            </div>
	            </template>
			</div>
		</template>
		<template v-else>
            <van-loading type="spinner" style="position: absolute; left: 48%;"/>
        </template>

        <van-popup class="show-popup-common show-popup-privacy" v-model="privacyShow" position="bottom">
        	<div class="title">
				<strong>隐私政策</strong>
				<i class="iconfont icon-close" @click="privacyShow = false"></i>
			</div>
			<div class="content">
				<div class="acticle-content" v-html="privacyActicleContent"></div>
				<div class="footer">
					<button class="btn" :class="{'btn-disabled':privacyDisabled}" :disabled="privacyDisabled" @click="privacyShow = false; checked = true">{{privacy_button_text}}</button>
				</div>
			</div>
        </van-popup>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import { Toast, Loading, Popup, Checkbox, CheckboxGroup } from 'vant'

import isApp from '@/mixins/is-app'
import formProcessing from '@/mixins/form-processing'

export default{
	name:'login',
	mixins:[isApp,formProcessing],
	components:{
		[Toast.name]: Toast,
    	[Loading.name]: Loading,
    	[Popup.name]: Popup,
    	[Checkbox.name]: Checkbox,
    	[CheckboxGroup.name]: CheckboxGroup
	},
	data(){
		return{
			loginMode:this.$route.query.loginMode == 'sms' ? false : true,
			shop_reg_closed:null,
			oauthList:[],
			disabled:true,
			isWeiXinSta:false,
			// 账号密码登录
			pwd:'password',
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
			docmHeight2:document.documentElement.clientHeight,
			configData:null,
		}
	},
	computed: {
        ...mapState({
            status: state => state.user.status
        }),
        token:{
			get(){
				return this.$store.state.user.token
			},
			set(val){
				this.$store.state.user.token = val
			}
		},
        captcha(){
			return this.$store.state.imgVerify.captcha
		},
		client(){
			return this.$store.state.imgVerify.client
		},
        thirdPartyImg(){
            let arr = []

            this.oauthList.forEach((v)=>{
                arr.push(require('../../../assets/img/'+v.type+'.png'))
            })

            return arr
        }
    },
    watch:{
    	username(){
    		this.disabled = !this.username
    	},
    	mobile(){
    		this.disabled = !this.mobile
    	},
    	status(val, oldVal) {
            if (val === 'success') {
                this.$router.push(this.$route.query.redirect || '/user')
            }
        },
        docmHeight(){
            if(this.docmHeight >= this.showHeight){
                this.oauthHidden = true
            }else{
                this.oauthHidden = false
            }
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
        }
    },
	mounted(){
		let self = this
        let url = ''
        let redirect = ''

        if(localStorage.getItem('token')) {
            self.$router.push({
                name:'user'
            })
        }else if(isApp.isWeixinBrowser()) {
            self.isWeiXinSta = true
            //如果路由上有带redirect返回目标URL
            if(self.$route.query.redirect && self.$route.query.redirect.url){
                redirect = Base64.encode(self.$route.query.redirect.url)
                url = window.API_URL + '/oauth/code?type=wechat&target_url=' + redirect
            }else{
                url = window.API_URL + '/oauth/code?type=wechat'
            }

            window.location.href = url
        }else{
            //判断是否是ecjiahash
            if(self.$route.query.ecjiahash){
                self.$store.dispatch('userRegister',{
                    ecjiahash:self.$route.query.ecjiahash
                }).then((res)=>{
                    localStorage.setItem('token', res.data)
                    self.token = res.data
                    self.$router.push({
                        path:self.$route.query.redirect
                    })
                })
            }

            //第三方登录
            self.thirdParty()

            //是否开启注册
            self.colseRegister()

            //隐私声明
            self.privacyActicle()
        }

        // 短信快捷登录
        if(!this.loginMode) this.$store.dispatch('setImgVerify');
	},
	methods:{
		thirdParty(){
            this.$http.get(`${window.ROOT_URL}api/user/oauth_list`).then(({data:{ data }})=>{
                this.oauthList = data
            })
        },
        thirdPartyLink(type){
            window.location.href = window.API_URL + '/oauth/code?type=' + type
        },
        handlePwdShow(){
        	this.pwd = this.pwd === 'password' ? 'text' : 'password'
        },
        colseRegister(){
            this.$http.get(`${window.ROOT_URL}api/user/login_config`).then(({data:{ data }})=>{
                this.shop_reg_closed = data.shop_reg_closed
            })
        },
        submitBtn(){
        	if(this.loginMode){
        		// 账号密码登录
	        	if(this.username == ''){
	        		Toast(this.$t('lang.username_notic'))
	                return false
	        	}else if(this.password == ''){
	        		Toast(this.$t('lang.password_notic'))
	                return false
	        	}

	        	if(!this.checked){
	        		Toast("请勾选并同意协议条款")
		        	return false
	        	}

	        	this.$store.dispatch('userLogin',{
	        		username: this.username,
	                password: this.password
	        	})
        	}else{
        		//短信快捷登录
        		let parent_id = localStorage.getItem('parent_id') ? localStorage.getItem('parent_id') : this.$route.query.parent_id ? this.$route.query.parent_id : null;

        		let o = {
					client:this.client,
					mobile:this.mobile,
					code:this.sms,
					parent_id:parent_id,
					allow_login:1
				}

				if(!this.checkMobile()){
					Toast(this.$t('lang.phone_number_format'))
					return false
				}

				if(this.imgverifyValue == ''){
					Toast(this.$t('lang.captcha_img'))
					return false
				}

				if(this.sms == ''){
					Toast(this.$t('lang.get_sms_code_notic'))
					return false
				}

				if(!this.checked){
	        		Toast("请勾选并同意协议条款")
		        	return false
	        	}

				this.$store.dispatch('userRegister', o).then((res)=>{
					if(res.status == 'success'){
						Toast.success({
							duration: 1000,
							forbidClick: true,
							loadingType: 'spinner',
							message: this.$t('lang.login_success')
						})
						
						localStorage.setItem('token', res.data)
						
						this.token = res.data
						this.$store.state.user.status = 'success'
					}else{
						Toast(res.errors.message)
					}
				})
        	}
        },
        async privacyActicle(){
			
			let configData = JSON.parse(sessionStorage.getItem('configData')); //获取后台配置
			if(!configData){
				const { data:{ status,data } } = await this.$http.get(`${window.ROOT_URL}api/shop/config`);
				configData = data;
			}
			
			this.configData = configData;
			
        	if(this.configData){
	        	const { data:{ status,data } } = await this.$http.post(`${window.ROOT_URL}api/article/show`,{id:this.configData.register_article_id});

	        	if(status == 'success'){
	        		this.privacyActicleContent = data.content;
	        		this.privacyActicleTitle = data.title;	
	        	}else{
	        		Toast('未设置隐私协议');
	        	}
        	}
        },
        clickCaptcha(){
        	this.$store.dispatch('setImgVerify')
	    },
	    sendVerifyCode(){
			let that = this
			let o = {
				captcha:this.imgverifyValue,
				client:this.client,
				mobile:this.mobile
			}

			that.$store.dispatch('setSendVerify', o).then(res=>{
				if(res == 'success'){
					that.button_type = false
					let second = 60
					const timer = setInterval(()=>{
						second --
						if(second){
							that.button_text = that.send_again + '('+ second +'s)'
						}else{
							that.button_type = true
							clearInterval(timer)
						}
					},1000)
				}
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
	    onClickBack(){
			this.$router.push({name: 'home'})
	    }
	},
}
</script>

<style lang="scss" scoped>
.container{
	background: #fff;
	position: relative;
	padding: 0 30px;

	&.container-login{
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}
	
	.login-head{
		position: absolute;
		left: 20px;
		top: 2.5%;
	}

	.login-form{
		.logo{
			width: 80px;
			height: 80px;
			border-radius: 50%;
			overflow: hidden;
			margin: 15% auto;
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
					height: 30px;
					line-height: 26px;
					font-size: 20px;

					&.icon-guanbi{
						margin-right: 0;
						font-size: 12px;
						margin-left: 10px;
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

	.quick-login{
		position: relative;
		margin: 10% auto 0;
		bottom: inherit;
		left: inherit;
		width: 90%;

		.quick-login-items{
			margin: 15px 0 20px;
		}

		p{
			font-size: 12px;
			
			span{
				color: #999;
				padding: 0 20px;
			}

			&:after{
				background: #ddd;
			}
		}
	}

	.privacy{
		padding-top: 10px;
		.checkbox{
			display: flex;
			align-items: center;
			justify-content: flex-start;
			font-size: 12px;
			padding-left: 3px;

			/deep/ .van-checkbox__icon{
				line-height: 16px;
				height: 17px;
				
				.van-icon{
					width: 16px;
					height: 16px;
				}
			}

			/deep/ .van-checkbox__label{
				margin-left: 5px;
				line-height: 16px;
			}
			
			.privacy-link{
				color: #4b89dc;
				line-height: 16px;
			}
		}
	}
}
</style>