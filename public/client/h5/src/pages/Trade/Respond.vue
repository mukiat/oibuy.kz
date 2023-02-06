<template>
	<div class="message">
		<div class="flow-done-con">
			<template v-if="msgObj.msg_type == 0">
			<i class="iconfont icon-hookring2"></i>
			</template>
			<template v-else-if="msgObj.msg_type == 1">
			<i class="iconfont icon-guanbi"></i>
			</template>
			<template v-else="msgObj.msg_type == 2">
			<i class="iconfont icon-warning"></i>
			</template>
			<p>{{ msgObj.msg }}</p>
		</div>
		<div class="padding-all" style="margin-top: 3rem;">
			<template v-if="msgObj.msg_type == 0">
				<a :href="msgObj.url" class="btn btn-submit box-flex br-5 min-two-btn">
                <template v-if="msgObj.extension_code == 'team_buy'">{{$t('lang.view_team_schedule')}}</template>
				<template v-else-if="msgObj.extension_code == 'drp_buy'">{{$t('lang.view_drp')}}</template>
                <template v-else-if="msgObj.extension_code == 'crowd_buy'">{{$t('lang.view_crowdfunding_order')}}</template>
                <template v-else>{{$t('lang.view_order')}}</template>
                </a>
			</template>
			<template v-else>
				<router-link :to="{name:'home'}" class="btn btn-submit box-flex br-5 min-two-btn">{{$t('lang.home_back')}}</router-link>
			</template>
		</div>
	</div>
</template>

<script>
import {
    Loading
} from 'element-ui';

import {
    Dialog
} from 'vant';

export default{
	data(){
		return{
			msgObj:''
		}
	},
	created(){
        let type = this.$route.query.type || ''
		this.init(type)
	},
    methods: {
        init(type){
            let url = this.$route.query
            let loadingInstance = Loading.service({ fullscreen: true, background: 'rgba(255,255,255,1)' , text: "Жүктеуде……" })
            if (type && type == 'qrpay') {
                // 收款码同步通知
                this.$http.get(`${window.ROOT_URL}api/qrpay/callback`,{ params:url }).then(res=>{
                    if(res.data.status == 'success'){
                        this.msgObj = res.data.data
                    }

                    loadingInstance.close()
                })

                setTimeout(() => {
                    window.location.href = this.msgObj.url
                },3000)
            } else {
                if (type && type == 'wxh5') {
                    let msg = this.$t('lang.is_please_confirm_wxcallpay')
                    this.wxh5(msg)
                } else {
                    this.$http.get(`${window.ROOT_URL}api/payment/callback`,{ params:url }).then(res=>{
                        if(res.data.status == 'success'){
                            this.msgObj = res.data.data
                        }
                        loadingInstance.close()
                    })

                    setTimeout(() => {
                       window.location.href = this.msgObj.url
                    },3000)
                }
            }
        },
	    wxh5(msg) {
            Dialog.confirm({
                message: msg,
                className: 'text-center',
                confirmButtonText : this.$t('lang.completed_payment'),
                cancelButtonText : this.$t('lang.payment_problems'),
            }).then(() => {
                // 确定
                let url = this.$route.query
                this.$http.get(`${window.ROOT_URL}api/payment/callback`,{ params:url }).then(res=>{
                    if(res.data.status == 'success' && res.data.data.is_wxh5 == 1){
                        window.location.href = res.data.data.repond_url
                    }
                })
            }).catch(() => {
                // 取消
                let url = this.$route.query
                this.$http.get(`${window.ROOT_URL}api/payment/callback`,{ params:url }).then(res=>{
                    if(res.data.status == 'success' && res.data.data.is_wxh5 == 1){
                        window.location.href = res.data.data.repond_url
                    }
                })

            })
		}
	},
    watch:{
        '$route'(to,from){
            this.init()
        }
    }
}
</script>
