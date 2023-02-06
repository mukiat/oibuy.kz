<template>
	<view class="container-bwg">
		<view class="user-lr-form">
			<form @submit="formSubmit">
				<view class="item-inputs">
					<view class="item-input">
						
						<input type="text" name="sms" v-model="sms" placeholder="请输入该账号密码" />
					</view>
					<view class="phone">该手机号已注册会员，请输入密码用以校验登录；如当前手机号绑定会员已有关联关系，将会解除原关联关系重新关联</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<button class="btn btn-red" formType="submit">确定</button>
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
				delta:0,
				sms:'',
				
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
			formSubmit(e){
				let that = this
				uni.request({
					url: this.websiteUrl + '/api/oauth/rebind',
					method: 'POST',
					data: {
						password: this.sms,
						mobile: this.mobile
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
								title:that.$t('lang.reset_phone_success'),
								icon:'none'
							});
							
							if(data.data.login == 1){
								uni.removeStorageSync("token");
								
								uni.setStorage({
									key:'token',
									data:data.data.token,
									success: (res) => {
										//记录user_id
										that.$store.dispatch('setUserId');
										
										if(that.delta > 0){
											uni.navigateBack({
												delta:that.delta
											});
										}else{
											uni.switchTab({
												url:'/pages/user/user'
											});
										}
									}
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
			}
		},
		onLoad(e){
			this.mobile = e.mobile ? e.mobile : ''
			this.delta = e.delta ? e.delta : 0
		}
	}
</script>

<style scoped>
.user-lr-form .item-input label{ width: auto; height: 1.4rem; line-height: 1.4rem; }
.tips{ font-size: 25upx; color: #888888; margin-top: 10upx;}
.phone{
	font-size: 24upx;
	margin-top: 30upx;
	color: #999999;
}
</style>
