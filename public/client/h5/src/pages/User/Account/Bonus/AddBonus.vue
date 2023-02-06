<template>
	<section class="drp-register">
		<div class="input-list">
			<van-field class="f-04" v-model="bonus.bonus_sn" clearable :placeholder="$t('lang.bonus_command')" />
			<van-field class="f-04" type="password" v-model="bonus.bonus_password" clearable :placeholder="$t('lang.bonus_pwd')" />
		</div>
		<div class="padding-all">
			<van-button class="br-5 f-06" @click="bonusSubmit" type="primary" bottom-action>{{ $t('lang.subimt') }}</van-button>
		</div>
		<CommonNav></CommonNav>
	</section>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Field,
		Button,
		Toast
	} from 'vant'
	import CommonNav from '@/components/CommonNav'
	export default {
		name: "addbonus",
		components: {
			[Field.name]: Field,
			[Button.name]: Button,
			[Toast.name]: Toast,
			CommonNav
		},
		data() {
			return {
				bonus: {
					bonus_sn: "",
					bonus_password: "",
				}
			};
		},
		methods: {
			//数据提交
			bonusSubmit() {
				if (this.bonus.bonus_sn != '' && this.bonus.bonus_password != '') {
					this.$store.dispatch('setAddBonus', this.bonus).then(res=>{
						Toast(res.data.mesg)
						if(res.data.code == 0){
							this.$router.push({
								name:'bonus'
							})
						}
					})
				} else {
					Toast(this.$t('lang.command_pwd_not_null'))
				}
			}
		},

	};
</script>
