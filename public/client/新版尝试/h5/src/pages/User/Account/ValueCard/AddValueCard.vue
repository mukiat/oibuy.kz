<template>
	<section>
		<div class="drp-register">
			<div class="input-list">
				<van-field class="f-04" v-model="vc_num" clearable :placeholder="$t('lang.value_card_sn')" />
				<van-field class="f-04" :type="isShowPwd ? 'text' : 'password'" v-model="vc_password" :placeholder="$t('lang.value_card_pwd')">
					<div class="right_icon" @click="clickRightIcon" slot="button">
						<i :class="['iconfont', 'icon-liulan1', isShowPwd ? 'color_red' : '']"></i>
					</div>
				</van-field>
			</div>
			<div class="padding-all">
				<van-button class="br-5 f-06" @click="btnSubmit" type="primary" bottom-action>
					<template v-if="deposit == ''">{{ $t('lang.bind_cur_account') }}</template>
					<template v-else>{{ $t('lang.recharge_value_card') }}</template>
				</van-button>
			</div>

		</div>
		<CommonNav></CommonNav>
	</section>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Field,
		Button,
		Toast,
		Icon
	} from 'vant'
	import CommonNav from '@/components/CommonNav'
	export default {
		name: "addValueCard",
		components: {
			[Field.name]: Field,
			[Button.name]: Button,
			[Toast.name]: Toast,
			[Icon.name]: Icon,
			CommonNav
		},
		data() {
			return {
				vc_num:'',
				vc_password:'',
				deposit:this.$route.query.type ? this.$route.query.type : '',
				vc_id:this.$route.query.vc_id ? this.$route.query.vc_id : 0,
				isShowPwd: false
			};
		},
		methods: {
			//数据提交
			btnSubmit() {
				if (this.vc_num != '' && this.vc_password != '') {
					if(this.deposit == ''){
						this.$store.dispatch('setAddValueCard',{
							vc_num:this.vc_num,
							vc_password:this.vc_password
						}).then(res=>{
							Toast(res.data.msg)
							if(res.data.error == 0){
								this.$router.push({
									name:'valueCard'
								})
							}
						})
					}else{
						this.$store.dispatch('setDepositValueCard',{
							vc_num:this.vc_num,
							vc_password:this.vc_password,
							vc_id:this.vc_id
						}).then(res=>{
							Toast(res.data.msg)
							if(res.data.error == 0){
								this.$router.push({
									name:'valueCard'
								})
							}
						})
					}
				} else {
					Toast(this.$t('lang.card_pwd_not_null'))
				}
			},
			clickRightIcon() {
				this.isShowPwd = !this.isShowPwd;
			}
		},

	};
</script>

<style scoped>
.right_icon .icon-liulan1 {
	font-size: 2rem;
}
.color_red {
	color: red;
}
</style>
