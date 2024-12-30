<template>
	<div class="com-nav-footer ect-tabbar">
		<div class="com-list-footer dis-box">
			<router-link :to="{name:'discover'}" class="box-flex" :class="{'active':type == 'index'}">
				<p><i class="iconfont icon-medal tm-icon-size"></i></p>
				<p>{{$t('lang.discover_home')}}</p>
			</router-link>
			<a href="javascript:;" class="box-flex j-community-btn p-r" @click="onPutDiscover" v-if="!myMode">
                <div class="com-footer-btn">
                    <span></span>
                    <em></em>
                    <label><i class="iconfont icon-jia"></i></label>
                </div>
            </a>
			<a href="javascript:;" class="box-flex" @click="onMyDiscover" :class="{'active':type == 'my'}">
				<p><i class="iconfont icon-geren tm-icon-size"></i></p>
				<p>{{$t('lang.my_post')}}</p>
			</a>
		</div>
	</div>
</template>

<script>
import { 
  Toast,
  Dialog,
} from 'vant'

export default{
	props:{
		mode:{
			type:Boolean,
			Default:false
		},
		type:{
			type:String,
			Default:''
		}
	},
	data(){
		return {
			myMode:this.mode
		}
	},
	components:{
		[Toast.name]: Toast,
		[Dialog.name]: Dialog
	},
	computed:{
		isLogin() {
            return localStorage.getItem('token') == null ? false : true
        },
	},
	methods:{
		onMyDiscover(){
			if(this.isLogin){
				this.$router.push({
					name:'myDiscover'
				})
			}else{
				let msg = this.$t('lang.login_user_not')
                this.notLogin(msg)
			}
		},
		onPutDiscover(){
			if(this.isLogin){
				this.myMode = this.myMode === false ? true : false
				this.$emit('getState',this.myMode)
			}else{
				let msg = this.$t('lang.login_user_not')
                this.notLogin(msg)
			}
		},
		notLogin(msg) {
			let url = window.location.href;
	        Dialog.confirm({
	            message: msg,
	            className: 'text-center'
	        }).then(() => {
	            this.$router.push({
	                name: 'login',
	                query: {
	                	redirect: {
	                		name: 'discover',
	                		url: url
	                	}
	                }
	            })
	        }).catch(() => {

	        })
	    },
	}
}
</script>