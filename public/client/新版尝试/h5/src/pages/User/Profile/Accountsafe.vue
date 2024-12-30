<template>
	<div class="user_profile_safe">
		<header>{{$t('lang.account_safe_propmt')}}</header>
		<section>
			<van-cell-group>
			  <van-cell is-link @click="linkHref('forget','reset')">
			  	<template slot="title">
			  		<div class="user_profile_safe_list">
			  			<i class="iconfont icon-zhaohuimima safe-icon"></i>
			  			<h4>{{$t('lang.edit_login_pwd')}}</h4>
			  			<p>{{$t('lang.edit_login_pwd_propmt')}}</p>
			  		</div>
			  	</template>
			  </van-cell>
			  <van-cell is-link @click="linkHref('paypwd')">
			  	<template slot="title">
			  		<div class="user_profile_safe_list" :class="{'active':accountsafe.users_paypwd == 1}">
			  			<i class="iconfont icon-zhaohuimima safe-icon"></i>
			  			<template v-if="accountsafe.users_paypwd == 1">
				  			<h4>{{$t('lang.edit_pay_pwd')}}</h4>
				  			<p>{{$t('lang.open_pay_pwd_propmt')}}</p>
			  			</template>
			  			<template v-else>
				  			<h4>{{$t('lang.open_pay_pwd')}}</h4>
				  			<p>{{$t('lang.open_pay_pwd_propmt')}}</p>
			  			</template>
			  		</div>
			  	</template>
			  </van-cell><van-cell is-link @click="linkHref('bindphone','reset')" v-if="accountsafe.mobile_phone==''">
			  	<template slot="title">
			  		<div class="user_profile_safe_list">
			  			<i class="iconfont icon-zhaohuimima safe-icon"></i>
			  			<h4>{{$t('lang.bind_phone')}}</h4>
			  			<p>{{$t('lang.open_pwds2')}}</p>
			  		</div>
			  	</template>
			  </van-cell>
			  </van-cell >
				  <van-cell is-link @click="linkHref('resetphone')" v-else>
			  	<template slot="title">
			  		<div class="user_profile_safe_list">
			  			<i class="iconfont icon-zhaohuimima safe-icon"></i>
			  			<h4>{{$t('lang.set_phone')}}</h4>
			  			<p>{{$t('lang.open_pwds2')}}</p>
			  		</div>
			  	</template>
			  </van-cell>
			  <van-cell is-link @click="linkHref('operationlog','reset')">
			  	<template slot="title">
			  		<div class="user_profile_safe_list">
			  			<i class="iconfont icon-zhaohuimima safe-icon"></i>
			  			<h4>{{$t('lang.operation_log')}}</h4>
			  			<p>{{$t('lang.open_pwds4')}}</p>
			  		</div>
			  	</template>
			  </van-cell>
			</van-cell-group>
			<!--<van-cell-group class="m-top10">
				<van-cell is-link>
					<template slot="title">
						<div class="user_profile_safe_list">
							<i class="iconfont icon-disanfang01 safe-icon"></i>
							<h4>{{$t('lang.auto_manage')}}</h4>
					  		<p>{{$t('lang.thirdparty_auto_manage')}}</p>
				  		</div>
					</template>
				</van-cell>
			</van-cell-group> -->
		</section>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import {
	Cell,
	CellGroup,
	Icon
} from 'vant'
import CommonNav from '@/components/CommonNav'
export default{
	data(){
		return{
			accountsafe:{
				is_connect_user:'',
				is_validated:'',
				mobile_phone:'',
				users_paypwd:''
			}
		}
	},
	components: {
		[Cell.name]: Cell,
		[CellGroup.name]: CellGroup,
		[Icon.name]: Icon,
		CommonNav
	},
	created() {
		this.$http.get(`${window.ROOT_URL}api/accountsafe`).then(res => {
    		this.accountsafe = res.data.data;
			// this.accountsafe.mobile_phone='18555116521'
			// console.log(this.accountsafe.mobile_phone=='',54555)
    	})
	},
	methods:{

		linkHref(name,reset){
			this.$router.push({
				name: name,
				query:{
					type:reset
				}
			})
		}
	}
}
</script>
