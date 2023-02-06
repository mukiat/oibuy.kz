<template>
	<div class="con con_main" v-if="articleDetail">
		<section class="article-main">
			<div class="article-title">
				<h3>{{ articleDetail.title }}</h3>
				<div class="article-author">
					<div class="header-left">
						<div class="avatar"><img :src="articleDetail.shop_logo"></div>
						<div class="author-info" v-if="articleDetail.author">
							<span class="author-name">{{ articleDetail.author }}</span>
						</div>
					</div>
					<div class="header-right">
						<span class="time">{{ articleDetail.amity_time }}</span>
						<span class="dot"></span>
						<span class="comment-number">{{ articleDetail.comment_number }}{{ $t('lang.comment_alt') }}</span>
					</div>
				</div>
			</div>
			<div class="article-con" v-html="articleDetail.content"></div>
			<div class="article-goods" v-if="articleDetail.related_goods">
				<div class="article-goods-item" v-for="(item,index) in articleDetail.related_goods" :key="index" @click="detailHref(item.goods_id)">
					<div class="goods-img"><img :src="item.goods_thumb" class="img" /></div>
					<div class="goods-info">
						<div class="goods-name twolist-hidden">{{item.goods_name}}</div>
						<div class="goods-box">
							<div class="goods-price" v-html="item.shop_price"></div>
							<div class="min-btn tag-gradients-color br-100 color-white f-03">{{$t('lang.to_buy')}}</div>
						</div>
					</div>
				</div>
			</div>
			<div class="fx-deta-box">
				<ul class="dis-box">
					<li class="box-flex">
						<div class="yuan">
							<a href="javascript:void(0);" :class="{'active':is_like == 1}" @click="like">
								<i class="iconfont icon-zan icon-yuan1"></i>
								<p>{{ likenum }}</p>
							</a>
						</div>
					</li>
				</ul>
			</div>
		</section>
		<section class="m-top06" v-if="articleDetail.comment && articleCommentLength">
			<van-cell-group class="my-bottom">
			  <van-cell :title="$t('lang.comment_list')"></van-cell>
			</van-cell-group>
			<div class="comment-info" v-for="(item,index) in articleDetail.comment" :key="index" v-if="item.status > 0">
				<div class="com-left">
					<div class="img-commom"><img :src="item.user_picture" class="img-height"></div>
				</div>
				<div class="com-right">
					<div class="com-r-top dis-box">
						<div class="com-adm-box box-flex"><h4>{{ item.user_name }}</h4></div>
						<div class="com-data-right"><span>{{ item.add_time }}</span></div>
					</div>
					<p class="com-con-m">{{ item.content }}</p>

					<div class="admin-con" v-for="(adminitem,adminindex) in item.reply_content" :key="adminindex">{{$t('lang.admin_reply')}}：{{adminitem.content}}</div>
				</div>
			</div>
			<div class="com-view-more"><a href="javascript:void(0);" @click="commentMore">{{$t('lang.comment_view_more')}}</a></div>
		</section>
		<section class="filter-btn consult-filter-btn">
			<div class="dis-box">
				<div class="box-flex text-all"><input class="j-input-text" type="text" name="comment" :placeholder="$t('lang.comment_publish')" autocomplete="off" v-model="content"></div>
				<template v-if="!disabled">
					<button type="button" class="btn-submit" @click="addActComment">{{$t('lang.send')}}</button>
				</template>
				<template v-else>
					<button type="button" class="btn-submit btn-disabled">{{$t('lang.send')}}</button>
				</template>
			</div>
		</section>

		<!-- 快捷菜单 -->
    <CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'	
import {
	Cell,
	CellGroup,
	Toast,
	Dialog
} from 'vant'

import ProductList from '@/components/ProductList'
import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return{
			content:'',
			routerName:'goods',
			disabled:false
		}
	},
	components:{
		[CellGroup.name] : CellGroup,
		[Cell.name] : Cell,
		[Toast.name] : Toast,
		ProductList,
		CommonNav
	},
	created(){
		this.show()
	},
	computed:{
		...mapState({
			articleDetail: state => state.article.articleDetail,
		}),
		isLogin() {
	    	return localStorage.getItem('token') == null ? false : true
	    },
	    likenum:{
	    	get(){
	    		return this.articleDetail.likenum
	    	},
	    	set(val){
	    		this.articleDetail.likenum = val
	    	}
	    },
	    is_like:{
	    	get(){
	    		return this.articleDetail.is_like
	    	},
	    	set(val){
	    		this.articleDetail.is_like = val
	    	}
	    },
	    articleCommentLength(){
	    	return this.articleDetail.comment.length
	    }
	},
	methods:{
		show(){
			this.$store.dispatch('setArticleDetail',{
				id:this.$route.params.id
			})
		},
		addActComment(){
			if(this.isLogin) {
				if(this.content){
					this.disabled = true
		    		this.$store.dispatch('setActicleComment',{
						id:this.$route.params.id,
						cid:0,
						content:this.content
					}).then(({data})=>{
						if(data == false){
							Toast(this.$t('lang.comment_add_fail'))
						}else{
							Toast(this.$t('lang.comment_add_success'))
							this.show()
						}

						this.content = ''
						this.disabled = false
					})
				}else{
					Toast(this.$t('lang.comment_not_null'))
				}
			}else{
		        let msg = this.$t('lang.login_comment')
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
					path: '/login',
					query: {
						redirect: {
							name: 'articleDetail', 
							params: {id: this.$route.params.id },
							url:url
						}
					}
				})
			}).catch(() => {

			})
	    },
	    commentMore(){
	    	this.$router.push({
	    		name:'articleCommentList',
	    		params:{
	    			id:this.$route.params.id
	    		}
	    	})
	    },
	    like(){
	    	this.$store.dispatch('setActicleCommentLike',{
	    		article_id:this.$route.params.id
	    	}).then(res=>{
	    		if(res.data){
	    			this.likenum = res.data.like_num
	    			this.is_like = res.data.is_like
	    		}
	    	})
	    },
	    detailHref(id){
	    	this.$router.push({
	    		name:'goods',
	    		params:{
	    			id:id
	    		}
	    	})
	    }
	},
	watch:{
		articleDetail(){
      //单独设置微信分享信息
      let that = this
      that.$wxShare.share({
      	title:that.articleDetail.title,
        desc:that.articleDetail.description,
        link:that.articleDetail.url,
        imgUrl:that.articleDetail.file_url
      });

      document.title = that.articleDetail.title ? that.articleDetail.title : that.$t('lang.article_detail_page');
		}
	}
}
</script>
<style scoped>
	.admin-con{ color: #999;
    padding: 5px 10px;
    font-size: 12px;
    background: #f4f4f4;
    border-radius: 5px;
    margin: 10px; }
</style>