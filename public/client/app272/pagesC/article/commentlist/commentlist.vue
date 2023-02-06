<template>
	<view class="comment">
		<view class="title">{{$t('lang.comment_list')}}</view>
		<view class="comment-info" v-for="(item,index) in articleCommentList" :key="index" v-if="item.status > 0">
			<view class="comment-left"><image :src="item.user_picture" mode="widthFix"></image></view>
			<view class="comment-right">
				<view class="com-r-top">
					<view class="com-adm-box">{{ item.user_name }}</view>
					<view class="com-data-right">{{ item.add_time }}</view>
				</view>
				<view class="com-con">{{ item.content }}</view>
				<view class="admin-con" v-for="(adminitem,adminindex) in item.reply_content" :key="adminindex">{{$t('lang.admin_reply')}}：{{adminitem.content}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniLoadMore from '@/components/uni-load-more.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	export default {
		data() {
			return {
				page:1,
				size:10,
				id:0,
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
			}
		},
		components: {
			uniLoadMore,
			dscNotContent,
			dscCommonNav,
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.articleCommentList.length){
				this.page ++
				this.commentList()
			}else{
				this.loadMoreText = this.$t('lang.contentnomore')
				return;
			}
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
		methods: {
			commentList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
	
				this.$store.dispatch('setActicleCommentList',{
					article_id:this.id,
					page:this.page,
					size:this.size
				})
			},
		},
		onLoad(e){
			this.id = e.id ? e.id : 0
			
			this.commentList()
		}
	}
</script>

<style>
.comment{ margin-top: 20upx; background: #FFFFFF; }
.comment .title{ padding: 20upx; border-bottom: 2upx solid #F6F6F9; }
.comment .comment-info{ padding: 20upx; border-bottom: 2upx solid #F6F6F9; display: flex; flex-direction: row; }
.comment .comment-info .comment-left{ width: 120upx; height: 120upx; }
.comment .comment-info .comment-left image{ width: 120upx; height: 120upx; border-radius: 50%;}
.comment .comment-info .comment-right{ margin-left: 40upx; flex: 1; display: flex; flex-direction: column;}
.comment .comment-info .comment-right .com-r-top{ display: flex; flex-direction: row; justify-content: space-between;}
.comment .comment-info .comment-right .com-r-top .com-adm-box{ color: #607fa6;}
.comment .comment-info .comment-right .com-r-top .com-data-right{ font-size: 25upx; color: #999999;}
.comment .comment-info .comment-right .com-con{ word-break: break-word; }
.comment .comment-info .comment-right .admin-con{ color: #999999; padding: 5upx 20upx; background: #f4f4f4; border-radius: 10upx; margin: 10upx 0; font-size: 25upx; }
.comment .com-view-more{ padding: 20upx 0; display: flex; justify-content: center; align-items: center;}
</style>
