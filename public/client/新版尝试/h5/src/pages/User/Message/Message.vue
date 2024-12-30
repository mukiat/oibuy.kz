<template>
	<div class="user-message">
		<div class="user-consult">
			<template v-if="list && list.length > 0">
				<div v-for="(item,index) in list" :key="index">
					<section class="cons-list dis-box">
						<div class="box-flex m-top02">
							<div class="cons-admin text-right"><span>{{ item.msg_time }}</span></div>
							<div class="fr m-top04">
								<div class="cons-cont-1">{{ item.msg_content }}</div>
							</div>
						</div>
						<div class="cons-head-img-box cons-head-img-boxr">
							<img :src="info.user_picture" v-if="info.user_picture" />
							<img src="../../../assets/img/user_default.png" v-else />
						</div>
					</section>
					<section class="cons-list dis-box" v-if="item.re_msg_content">
				        <div class="cons-head-img-box text-center"><i class="iconfont icon-kefu c-kefu-size "></i></div>
				        <div class="box-flex m-top02">
				            <div class="cons-admin">{{item.re_user_name}}<span>{{item.re_msg_time}}</span></div>
				            <div class="fl">
				                <div class="cons-cont m-top04">{{item.re_msg_content}}</div>
				            </div>
				        </div>
		    		</section>
				</div>
			</template>
			<template v-else>
				<NotCont></NotCont>
			</template>
		</div>
		<van-submit-bar :button-text="$t('lang.submit')" @submit="onSubmit">
			<van-field v-model="msg_title" :placeholder="$t('lang.message_placeholder')" @focus="onFocus" @blur="onBlur" slot="default" />
		</van-submit-bar>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import qs from 'qs'
import {
	Field,
	SubmitBar,
	Toast,
} from 'vant'

import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'

export default{
	data(){
		return{
			msg_title:'',
			info:'',
			list:[],
		}
	},
	components:{
		[Field.name]: Field,
		[SubmitBar.name]: SubmitBar,
		[Toast.name]: Toast,
		NotCont,
		CommonNav
	},
	created(){
		this.default()
	},
	methods:{
		default(){
			let o = {
				page:1,
				size:10
			}
			this.$http.get(`${window.ROOT_URL}api/feedback`,{ params: o}).then(res=>{
				this.info = res.data.data.info
				this.list = res.data.data.message_list
			})
		},
		onSubmit(){
			let o = {
				msg_title:this.msg_title
			}
			if(this.msg_title != ''){
				var that = this;
				this.$http.post(`${window.ROOT_URL}api/feedback/create`,qs.stringify(o)).then(res=>{
					if(res.data.status=='success'){
						Toast.success({
							message:this.$t('lang.add_success'),
							duration:1000,
							mask: true,
						})
						that.msg_title = "";
						setTimeout(() => {
							this.default()
						},1000)
					}
				})
			}else{
				Toast({
					message:this.$t('lang.message_not_null'),
					duration:1000
				})
			}
		},
		onFocus(){
			document.body.scrollTop = document.body.scrollHeight;
		},
		onBlur(){
			window.scroll(0,document.body.scrollHeight)
		}
	}
}
</script>
