<template>
	<view class="container-bwg">
		<view class="user-lr-form">
			<view class="title">{{$t('lang.reset_pwd')}}</view>
			<form @submit="formSubmit">
			<view class="item-inputs">
				<view>{{$t('lang.set_new_pwd')}}</view>
				<view class="item-input">
					<input :type="pwd" name="new_password" :placeholder="$t('lang.new_password_notic')" />
					<view class="icon" @click="handlePwdShow">
						<uni-icons :color="pwdColor" type="eye" size='18'></uni-icons>
					</view>
				</view>
			</view>
			<view class="btn-bar btn-bar-radius">
				<button class="btn btn-red" formType="submit">{{$t('lang.confirm_update')}}</button>
			</view>
			</form>
			<view class="tips">{{$t('lang.reset_prompt_notic')}}</view>
		</view>
	</view>
</template>

<script>
	import uniIcons from '@/components/uni-icons/uni-icons.vue';

	var graceChecker = require("@/common/graceChecker.js");
	export default {
		data() {
			return {
				pwd: 'password',
				pwdColor: '#bdbdbd',
				new_password:'',
				mobile:'',
				client:'',
				code:'',
			}
		},
		components: {
			uniIcons,
		},
		onLoad(e){
			this.mobile = e.mobile;
			this.client = e.client;
			this.code = e.code;
		},
		methods: {
			handlePwdShow() {
				this.pwd = this.pwd == 'password' ? 'text' : 'password';
				this.pwdColor = this.pwd == 'password' ? '#bdbdbd' : '#f92028';
			},
			formSubmit(e) {
				var rule = [
					{name: "new_password",checkType: "notnull",checkRule: "",errorMsg: this.$t('lang.password_notic')},
					{name: "new_password",checkType: "length6",checkRule: "",errorMsg: this.$t('lang.new_password_not_length6')},
				];
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				if (checkRes) {
					uni.request({
						url: this.websiteUrl + '/api/user/reset',
						method: 'POST',
						data: {
							password:e.detail.value.new_password,
							mobile:this.mobile,
							client:this.client,
							code:this.code
						},
						header: {
							'Content-Type': 'application/json',
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							let data = res.data
							if(data.status === 'success'){
								uni.showToast({
									title:this.$t('lang.reset_pwd_success'),
									icon:'none'
								});
								uni.switchTab({
									url: '/pages/user/user',
								});
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
				} else {
					uni.showToast({
						title: graceChecker.error,
						icon: "none"
					});
				}
			},
		}
	}
</script>

<style>

</style>
