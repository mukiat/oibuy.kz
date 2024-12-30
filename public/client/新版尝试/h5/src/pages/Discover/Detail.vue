<template>
	<div class="con con_main">
		<Goods :data="discoverDetail.link_good"></Goods>
		<div class="com-nav com-nav-detail">
			<div class="com-hd com-header-img-cont">
				<div class="dis-box">
					<div class="com-img"><div class="img-commom"><img :src="discoverDetail.user_picture" class="img-height"></div></div>
					<div class="com-info box-flex"><h4>{{ discoverDetail.user_name }}</h4></div>
					<div class="com-time"><i class="iconfont icon-shijian"></i><span>{{ discoverDetail.add_time }}</span></div>
				</div>
			</div>
			<div class="com-bd">
				<div class="com-bd-title dis-box">
					<em class="em-promotion-1 tm-ns">
						<template v-if="discoverDetail.dis_type == 1">{{$t('lang.tao')}}</template>
						<template v-else-if="discoverDetail.dis_type == 2">{{$t('lang.wen')}}</template>
						<template v-else-if="discoverDetail.dis_type == 3">{{$t('lang.quan')}}</template>
						<template v-else>{{$t('lang.shai')}}</template>
					</em>
					<div class="com-title box-flex"><strong class="ellipsis-one">{{ discoverDetail.dis_title }}</strong></div>
				</div>
				<div class="article-con" v-html="discoverDetail.dis_text"></div>

			</div>
			<div class="com-fd">
				<ul class="dis-box fx-deta-box">
					<li class="box-flex">
						<div class="yuan" @click="onZan(discoverDetail.dis_type,discoverDetail.dis_id)">
							<a href="javascript:void(0);">
								<i class="iconfont icon-zan"></i>
								<p>{{ discoverDetail.like_num }}</p>
							</a>
						</div>
					</li>
					<li class="box-flex" @click="shareHandle(false)">
						<div class="yuan">
							<a href="javascript:void(0);">
								<i class="iconfont icon-fenxiang"></i>
								<p>{{$t('lang.share')}}</p>
							</a>
						</div>
					</li>
				</ul>
			</div>
		</div>
        <div class="comment-list" v-if="discoverDetail.user_comment && discoverDetail.user_comment.length > 0">
        	<div class="dis-box padding-all my-nav-box my-bottom">
	            <h3 class="box-flex">{{$t('lang.comment_list')}}({{discoverDetail.user_comment.length}})</h3>
	        </div>
			<ul class="my-com-max-box">
				<li class="padding-all" v-for="(item,index) in discoverDetail.user_comment" :key="index">
					<div class="com-img-left"><div class="com-left-img"><img :src="item.user_picture" class="img" /></div></div>
					<div class="com-con-right">
						<div class="dis-box">
							<div class="box-flex">
								<div class="com-adm-box" style="padding-top:.2rem">
                                    <h4>{{ item.user_name }}</h4>
                                    <p>{{ item.add_time }}</p>
                                </div>
							</div>
							<div class="not" @click="onQuote(item.dis_id,item.user_name)">
								<div class="com-data-right com-list-1">
                                    <span class="oncle-color">
                                        <span class="my-right1">0<i class="iconfont icon-daipingjia"></i></span>
                                    </span>
                                </div>
							</div>
						</div>
						<p class="com-con-m">{{ item.dis_text }}</p>
						<div class="pl-hf-box padding-all" v-for="hfItem in item.next_com">
                            <p><span>{{ hfItem.user_name}} : </span>{{ hfItem.dis_text }}</p>
                            <div class="x-jiant"></div>
                        </div>
					</div>
				</li>
			</ul>
		</div>
		<div class="filter-btn consult-filter-btn">
			<div class="dis-box">
				<div class="com-left-img">
					<div class="img-commom">
						<img :src="discoverDetail.avatar" class="img-height" v-if="discoverDetail.avatar">
						<img src="../../assets/img/user_default.png" class="img-height" v-else>
					</div>
				</div>
				<div class="text-all box-flex">
					<input class="j-input-text" type="text" name="comment" v-model="comment" :placeholder="placeholder" autocomplete="off" value="">
				</div>
				<button type="button" class="btn-submit" @click="btnSubmit">{{$t('lang.send')}}</button>
			</div>
		</div>
		<div class="van-modal" :class="{'hide':mask === true}" @click="close" style="z-index:1000"></div>
		<div class="bargain-share van-modal" :class="{'hide':shareState === true}" style="z-index:1001">
            <div class="bargain-friends">
                <div class="header f-30 col-3">{{$t('lang.share_hint')}}</div>
                <div class="cont f-24 text-center">{{$t('lang.share_toast_hint')}}</div>
                <div class="footer f-24 col-3" @click="close">{{$t('lang.i_see')}}</div>
            </div>
        </div>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import {
	Toast,
	Dialog
} from 'vant'

import Goods from './components/Goods'
import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return {
			comment:'',
			parent_id:this.$route.query.dis_id,
			dis_type:this.$route.query.dis_type,
			shareActive:false,
			placeholder:this.$t('lang.label_reply_post'),
			quote_id:0,
			shareState: true,
			mask:true
		}
	},
	components:{ 
		Goods,
		CommonNav,
		[Toast.name]:Toast,
		[Dialog.name]:Dialog
	},
	created(){
		this.load()
	},
	computed:{
		...mapState({
			discoverDetail: state => state.discover.discoverDetail
		}),
		like_num:{
			get(){
				return this.$store.state.discover.discoverDetail.like_num
			},
			set(val){
				this.$store.state.discover.discoverDetail.like_num = val
			}
		},
		isLogin() {
            return localStorage.getItem('token') == null ? false : true
        },
	},
	methods:{
		load(){
			this.$store.dispatch('setDiscoverDetail',{
				dis_type:this.$route.query.dis_type,
				dis_id:this.$route.query.dis_id
			})
		},
		onZan(type,id){
			this.$store.dispatch('setDiscoverLike',{
				dis_type:type,
				dis_id:id
			}).then(({data})=>{
				Toast(data.msg)

				this.like_num = data.like_num
			})
		},
		btnSubmit(){
			if(this.isLogin){
				this.$store.dispatch('setDiscoverComment',{
					parent_id:this.parent_id,
					quote_id:this.quote_id,
					dis_text:this.comment,
					reply_type:0,
					dis_type:this.dis_type,
					goods_id:this.discoverDetail.goods_id,
				}).then(({data})=>{
					Toast(data.msg)
					if(data.error == 0){
						this.load()
						this.comment = ''
					}
				})
			}else{
				let msg = this.$t('lang.login_user_not')
                this.notLogin(msg)
			}
		},
		onQuote(id,name){
			this.quote_id = id
			this.placeholder = this.$t('lang.reply') + name +':'
		},
		shareHandle(val){
			this.shareState = val
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
	                		name: 'discoverDetail',
	                		query:{
	                			dis_type:this.$route.query.dis_type,
	                			dis_id:this.$route.query.dis_id
			                },
			                url:url
			            }
			        }
	            })
	        }).catch(() => {

	        })
	    },
	    //关闭蒙板
        close() {
            this.mask = true
            this.shareState = true
        },
	}
}
</script>
<style scoped>
.article-con{
	font-size: 1.2rem;
}
</style>