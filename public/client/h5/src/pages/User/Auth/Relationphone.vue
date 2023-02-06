<template>
	<!--暂已废弃-->
	<section class="con bg-color-write">
		<div class="user-login-box">
			<ec-form ref="loginForm" class="user-login-form">
				<div class="user-login-head">
					<!-- <i class="iconfont icon-back" @click="onClickHome"></i> -->
					<h1></h1>
				</div>
				<div class="user-login-ul">
					<ec-form-item prop="sms">
						<div class="item-input dis-box">
							<div class="value box-flex">
								<ec-input type="text" v-model="sms" placeholder="请输该账号密码"></ec-input>
							</div>
						</div>
					</ec-form-item>
					<div class="phone">{{$t('lang.relationphone_notic')}}</div>
				</div>
				<template>
					<button type="button" class="btn btn-submit border-radius-top05" @click="submitBtn">确定</button>
				</template>
			</ec-form>
		</div>
	</section>
</template>
<script>
	import qs from 'qs'
	import {
		mapState
	} from 'vuex'
	import {
		Form,
		Input,
		FormItem
	} from 'element-ui'
	import {
		Toast
	} from 'vant'
	import {
		Dialog
	} from 'vant';
	import axios from 'axios'

	export default {
		name: 'login',
		data() {
			return {
				sms: '',
				mobile: this.$route.query.mobile ? this.$route.query.mobile : '',
				url: this.$route.query.url ? this.$route.query.url : '',
			}
		},
		components: {
			'EcForm': Form,
			'EcFormItem': FormItem,
			'EcInput': Input,
			[Toast.name]: Toast,
		},
		created() {

		},
		methods: {
			submitBtn() {
				if (this.sms == '') {
					Toast(this.$t('lang.notic_new_pwd'))
					return false
				}
				this.$http.post(`${window.ROOT_URL}api/oauth/rebind`, qs.stringify({
					password: this.sms,
					mobile: this.mobile
				})).then(({
					data: data
				}) => {
					if (data.status == 'success') {
						Toast(this.$t('lang.reset_phone_success'))
						if(data.data.login == 1){
							localStorage.removeItem('token')
							localStorage.setItem('token', data.data.token);

							//记录user_id
							this.$store.dispatch('setUserId');
							
							if (this.url) {
								window.location.href = this.url
							} else {
								this.$router.push({
									name: 'user'
								})
							}
						}
					} else {
						Toast(data.errors.message)
					}
				})
			},

		}
	}
</script>
<style scoped="">
	.phone {
		margin-top: 1.5rem;
		font-size: 1.2rem;
		color: #999;
	}
</style>
