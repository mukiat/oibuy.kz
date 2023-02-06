<template>
	<div class="container container-register" :style="{'height': docmHeight2 + 'px'}">
		<div class="login-head"><i class="iconfont icon-back" @click="onClickBack"></i></div>
		<div class="login-form">
			<div class="title">Тіркелу</div>
			<div class="input-box">
				<div class="input-box__left">
					<i class="iconfont icon-shouji1"></i>
					<input type="tel" class="input" v-model="mobile" autocomplete="off" :placeholder="$t('lang.enter_mobile')" />
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
			<div class="input-box">
				<div class="input-box__left">
					<i class="iconfont icon-jiesuo"></i>
					<input :type="pwd" class="input" v-model="password" autocomplete="off" :placeholder="$t('lang.enter_password')" />
				</div>
				<div class="input-box__right">
					<i class="iconfont icon-liulan1" :class="{'active':pwd == 'text'}" @click="handlePwdShow"></i>
				</div>
			</div>
			<div class="privacy">
            	<div class="checkbox">
            		<van-checkbox v-model="checked">Оқыдым және келісем</van-checkbox>
            		<span class="privacy-link" @click="privacyShow = true">《{{ privacyActicleTitle ? privacyActicleTitle : 'Құпия шарты' }}》</span>
				</div>
            </div>
			<div class="signup-button">
				<button class="btn btn-lg-red" :class="{'btn-disabled':disabled}" :disabled="disabled" @click="submitBtn">Тіркелу</button>
			</div>
		</div>

		<van-popup class="show-popup-common show-popup-privacy" v-model="privacyShow" position="bottom">
        	<div class="title">
				<strong>Құпия саясаты</strong>
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
	name:'register',
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
			disabled:true,
			pwd:'password',
			password:'',
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
			privacy_button_text:'Оқыдым(6s)',
			timer:null,
			docmHeight2:document.documentElement.clientHeight,
		}
	},
	computed:{
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
	},
	watch:{
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
		            	that.privacy_button_text = 'Оқыдым' + '('+ second +'s)'
		            }else{
		            	that.privacy_button_text = 'Оқыдым'
						that.privacyDisabled = false
						clearInterval(that.timer)
		            }
				},1000)
        	}else{
        		that.privacy_button_text = that.checked ? 'Оқыдым' : 'Оқыдым(6s)'
        		clearInterval(that.timer)
        	}
        },
        token(){
			this.$router.push({ name:'user' });
	    }
	},
	mounted(){
		let self = this

		self.$store.dispatch('setImgVerify');

		//隐私声明
        self.privacyActicle();
	},
	methods:{
		handlePwdShow(){
        	this.pwd = this.pwd === 'password' ? 'text' : 'password'
        },
		async privacyActicle(){
			
			
			let configData = JSON.parse(sessionStorage.getItem('configData')); //获取后台配置
			if(!configData){
				const { data:{ status,data } } = await this.$http.get(`${window.ROOT_URL}api/shop/config`);
				configData = data;
			}
			
        	if(configData){
	        	const { data:{ status,data } } = await this.$http.post(`${window.ROOT_URL}api/article/show`,{id:configData.register_article_id});

	        	if(status == 'success'){
	        		this.privacyActicleContent = data.content;
	        		this.privacyActicleTitle = data.title;	
	        	}else{
	        		Toast('Құпия шарт қойылмаған');
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
		submitBtn(){
			//短信快捷登录
    		let parent_id = localStorage.getItem('parent_id') ? localStorage.getItem('parent_id') : this.$route.query.parent_id ? this.$route.query.parent_id : null;

    		let o = {
				client:this.client,
		        mobile:this.mobile,
		        code:this.sms,
		        pwd:this.password,
		        parent_id:parent_id,
		        allow_login:0
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

			if(this.password == ''){
		        Toast(this.$t('lang.enter_pwd'))
		        return false
		    }
			
			if(!this.checked){				
				Toast(this.$t('lang.check_agreement'))
				return
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
				}else{
					Toast(res.errors.message)
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
            this.$router.go(-1);
        },
	}
}
</script>

<style lang="scss" scoped>
.container{
	background: #fff;
	position: relative;
	overflow: hidden;
	padding: 0 30px;

	&.container-register{
		.login-form{
			margin-top: 30%;
		}
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
					color: #999;
					position: relative;
					padding: 0 0 0 10px;

					&.active{
						color: #f92028;
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