<template>
	<div class="con con-article">
		<section class="article-main" v-if="wechatMedia">
			<div class="article-title">
				<h3>{{ wechatMedia.title }}</h3>
				<small><time>{{ wechatMedia.add_time }}</time><span style="color:#337ab7; margin-left: 5px;">{{wechatMedia.author}}</span></small>
			</div>
	        <div class="article-con" v-if="wechatMedia.is_show == 1"><img class="img" :src="wechatMedia.file" /></div>
	        <div class="article-con" style="color: #999;" v-if="wechatMedia.digest">{{$t('lang.abstract')}}{{ wechatMedia.digest }}</div>
	        <div class="article-con" v-html="wechatMedia.content"></div>
		</section>
		<template v-else>
			<NotCont :isSpan="bool">
				<span slot="spanCon" class="cont">{{$t('lang.uninstalled_wechat_media')}}</span>
			</NotCont>
		</template>
	</div>
</template>
<script>
import { mapState } from 'vuex'	
import {
	Cell,
	CellGroup,
	Toast
} from 'vant'

import NotCont from '@/components/NotCont'

export default{
	data(){
		return{
			content:'',
			is_like:0,
			bool:false
		}
	},
	components:{
		[CellGroup.name] : CellGroup,
		[Cell.name] : Cell,
		[Toast.name] : Toast,
		NotCont
	},
	created(){
		this.show()
	},
	computed:{
		...mapState({
			wechatMedia: state => state.article.wechatMedia,
		}),
	},
	methods:{
		show(){
			this.$store.dispatch('setWechatMedia',{
				media_id:this.$route.params.id
			})
		},
	},
	watch:{
		wechatMedia(){
			if(this.wechatMedia && this.wechatMedia.link){
				window.location.href = this.wechatMedia.link
			}
		}
	}
}
</script>
<style>
.article-con img{
	max-width: 100%;
	height: auto;
}
</style>