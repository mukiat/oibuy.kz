<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<section>
			<van-cell-group class="my-bottom">
				<van-cell :title="$t('lang.comment_list')" size="large"></van-cell>
			</van-cell-group>
			<template v-if="articleCommentList">
				<div class="comment-info" v-for="(item,index) in articleCommentList" :key="index" v-if="item.status > 0">
					<div class="com-left">
						<div class="img-commom"><img :src="item.user_picture" class="img-height"></div>
					</div>
					<div class="com-right">
						<div class="com-r-top dis-box">
							<div class="com-adm-box box-flex"><h4>{{ item.user_name }}</h4></div>
							<div class="com-data-right"><span>{{ item.add_time }}</span></div>
						</div>
						<p class="com-con-m">{{ item.content }}</p>
					</div>
				</div>
				<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
				<template v-if="loading">
					<van-loading type="spinner" color="black" />
				</template>
			</template>
		</section>
	</div>	
</template>

<script>
import { mapState } from 'vuex'
import {
	Cell,
	CellGroup,
	Waterfall,
	Loading
} from 'vant'

import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
		return{
			disabled:false,
			loading:true,
			page:1,
			size:10,
			footerCont:false
		}
	},
	directives: {
    	WaterfallLower: Waterfall('lower')
	},
	components:{
		[Cell.name]:Cell,
		[CellGroup.name]:CellGroup,
		[Loading.name] : Loading
	},
	created(){
		this.commentList()
	},
	computed: {
		articleCommentList:{
			get(){
				return this.$store.state.article.articleCommentList
			},
			set(val){
				this.$store.state.article.articleCommentList = val	
			}
		}
  	},
	methods:{
		commentList(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setActicleCommentList',{
				article_id:this.$route.params.id,
				page:this.page,
				size:this.size
			})
		},
	    loadMore(){
			setTimeout(() => {
				this.disabled = true
		    	if(this.page * this.size == this.articleCommentList.length){
		  			this.page ++
		  			this.commentList()	
		  		}
	    	},200)
	    }
	},
	watch:{
		articleCommentList(){
	  		if(this.page * this.size == this.articleCommentList.length){
	  			this.disabled = false
	  			this.loading = true
	  		}else{
	  			this.loading = false
	  			this.footerCont = this.page > 1 ? true : false
	  		}

	  		this.articleCommentList = arrRemove.trimSpace(this.articleCommentList)
	  	},
	}
}
</script>