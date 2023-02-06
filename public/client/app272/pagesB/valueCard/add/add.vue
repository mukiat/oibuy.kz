<template>
	<view>
		<form @submit="formSubmit">
			<view class="input-list">
				<view class="input-item"><input name="vc_num" type="text" :placeholder="$t('lang.value_card_sn_1')" /></view>
				<view class="input-item flex_box ai_center">
					<input class="flex_1" name="vc_password" v-model="vc_password" type="password" :placeholder="$t('lang.value_card_pwd_1')" v-if="inputType == 'password'" />
					<input class="flex_1" name="vc_password" v-model="vc_password" type="text" :placeholder="$t('lang.value_card_pwd_1')" v-else />
					<view class="right_icon flex_box jc_center ai_center" @click="clickRightIcon">
						<uni-icons :color="pwdColor" type="eye" size='18'></uni-icons>
					</view>
				</view>				
			</view>
			<view class="btn-bar btn-bar-radius">
				<button formType="submit" class="btn btn-red">
					<block v-if="type=='deposit'">{{$t('lang.recharge_value_card')}}</block>
					<block v-else>{{$t('lang.bind_value_card')}}</block>
				</button>
			</view>
		</form>
	</view>
</template>

<script>
	var graceChecker = require("@/common/graceChecker.js");
	
	export default {
		data() {
			return {
				vc_num:'',
				vc_password:'',
				type:'',
				vc_id:'',
				inputType: 'password',
				pwdColor: '#bdbdbd',
			};
		},
		methods: {
			//数据提交
			formSubmit(e) {
				var rule = [
					{name:"vc_num", checkType : "notnull", checkRule:"",  errorMsg:"储值卡卡号不能为空"},					
					{name:"vc_password", checkType : "notnull", checkRule:"",  errorMsg:"储值卡密码不能为空"},
				];
				
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				
				if(checkRes){
					if(this.type == 'deposit'){
						this.$store.dispatch('setDepositValueCard',{
							vc_num:e.detail.value.vc_num,
							vc_password:e.detail.value.vc_password,
							vc_id:this.vc_id
						}).then(res=>{
							uni.showToast({ title: res.data.msg, icon: "none" });
							if(res.data.error == 0){
								setTimeout(()=>{
									uni.redirectTo({
										url:'/pagesB/valueCard/valueCard'
									})
								},1000)
							}
						})
					}else{
						this.$store.dispatch('setAddValueCard',{
							vc_num:e.detail.value.vc_num,
							vc_password:e.detail.value.vc_password
						}).then(res=>{
							uni.showToast({ title: res.data.msg, icon: "none" });
							if(res.data.error == 0){
								setTimeout(()=>{
									uni.redirectTo({
										url:'/pagesB/valueCard/valueCard'
									})
								},1000)
							}
						})
					}
				} else {
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			},
			clickRightIcon() {
				this.inputType = this.inputType == 'password' ? 'text' : 'password';
				this.pwdColor = this.inputType == 'password' ? '#bdbdbd' : '#f92028';
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/valueCard/add/add'
			}
		},
		onLoad(e){
			if(e){
				this.type = e.type;
				this.vc_id = e.vc_id
			}
		}
	}
</script>

<style>
.btn-bar{ margin: 30upx; }
.right_icon {
	width: 10%;
}
.color_red {
	color: red;
}
</style>
