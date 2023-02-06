<template>
	<view>
		<form @submit="formSubmit">
			<view class="input-list">
				<view class="input-item"><input v-model="bonus.bonus_sn" name="bonus_sn" type="text" :placeholder="$t('lang.bonus_command')" /></view>
				<view class="input-item"><input v-model="bonus.bonus_password" name="bonus_password" type="password" :placeholder="$t('lang.bonus_pwd')" /></view>				
			</view>
			<view class="btn-bar btn-bar-radius">
				<button formType="submit" class="btn btn-red">{{$t('lang.subimt')}}</button>
			</view>
		</form>
	</view>
</template>

<script>
	var graceChecker = require("@/common/graceChecker.js");
	
	export default {
		data() {
			return {
				bonus: {
					bonus_sn: "",
					bonus_password: "",
				}
			};
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/bonus/addBonus/addBonus'
			}
		},
		methods: {
			//数据提交
			formSubmit(e) {
				var rule = [
					{name:"bonus_sn", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.bonus_command_not')},					
					{name:"bonus_password", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.bonus_command_not')},
				];
				
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				
				if(checkRes){
					this.$store.dispatch('setAddBonus',this.bonus).then(res=>{
						uni.showToast({title:res.data.mesg,icon:'none'});
						if(res.data.code == 0){
							uni.navigateTo({
								url:'/pagesB/bonus/bonus'
							})
						}
					})
				} else {
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			}
		},
	}
</script>

<style>
.btn-bar{ margin: 30upx; }
</style>
