<template>
	<view class="container">
		<view class="article-main">
			<view class="content" v-if="articleDesc">
				<rich-text :nodes="articleDesc"></rich-text>
			</view>
		</view>
		<navigator url="./reason" hover-class="none">
		<view class="btn-goods-action">
			<view class="btn-bar">
				<view class="btn btn-red">确定注销</view>
			</view>
		</view>
		</navigator>
	</view>
</template>

<script>
	import { mapState } from 'vuex'	
	
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import uniPopup from '@/components/uni-popup.vue'
	
	import * as localConfig from '@/config/local/config.js'
	import request from '@/common/request.js'
	
	const webUrl = localConfig.websiteUrl
	
	export default {
		data() {
			return {
				id:0,
				content:'',
				is_like:0,
				routerName:'goods',
				disabled:false,
				showState:true,
				textareaShow:false,
				focus:false
			}
		},
		components: {
			dscCommonNav
		},
		computed:{
			...mapState({
				articleDetail: state => state.article.articleDetail,
			}),
			articleDesc(){
				let result = this.articleDetail.content;
				const reg = /style\s*=(['\"\s]?)[^'\"]*?\1/gi;
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');
				const regex3 = new RegExp('<h1', 'gi');
				const regex4 = new RegExp('<h2', 'gi');
				const regex5 = new RegExp('<h3', 'gi');
				const regex6 = new RegExp('<h4', 'gi');
				
				if(result){
					result = result.replace(reg, '');
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex2, '<p style="margin:0;padding:0;color:#222;text-align:justify;list-style-position:inside;list-style-type:square;font-size:16px;line-height:1.76;margin-top:15px;word-break:break-word;"');
					result = result.replace(regex3, '<h1 style="font-size: 16px;border-left-width: 3px;border-left: solid #ed4040;padding-left: 6px;line-height: 28px;margin: 20px 0;font-weight: bold;"');
					result = result.replace(regex4, '<h2 style="font-size: 16px;border-left-width: 3px;border-left: solid #ed4040;padding-left: 6px;line-height: 28px;margin: 20px 0;font-weight: bold;"');
					result = result.replace(regex5, '<h3 style="font-size: 16px;border-left-width: 3px;border-left: solid #ed4040;padding-left: 6px;line-height: 28px;margin: 20px 0;font-weight: bold;"');
					result = result.replace(regex6, '<h4 style="font-size: 16px;border-left-width: 3px;border-left: solid #ed4040;padding-left: 6px;line-height: 28px;margin: 20px 0;font-weight: bold;"');
				}
				return result
			}
		},
		methods: {
			article(){
				let that = this;
				request.post(webUrl + '/api/custom/user/article').then(res=>{
							if (res.data) {
								that.id = res.data.article_id;
								that.show();
							}
						})
			},
			show(){
				this.$store.dispatch('setArticleDetail',{
					id:this.id
				})
			}
		},
		onLoad(e){
			this.article();
		}
	}
</script>

<style scoped>
.container{ background-color: #FFFFFF; padding-bottom: 120upx;}

.article-main { background: #FFFFFF; overflow: hidden; padding-top: 20upx;}
.article-main .header{ display: flex; flex-direction: column; padding: 0 20upx;}
.article-main .header .title{ font-size: 40upx; color: #333333; font-weight: bold;}
.article-main .header .article-author{ display: flex; justify-content: space-between; margin-top: 24upx;}
.article-main .header .article-author .header-left{ display: flex; align-items: center; }
.article-main .header .article-author .header-left .avatar{ width: 80upx; height: 80upx; border-radius: 80upx; margin: auto; display: inline-block; margin-right: 20upx;}
.article-main .header .article-author .header-left .avatar image{ width: 100%; height: 100%; border-radius: inherit;}
.article-main .header .article-author .header-left .author-info{ display: flex; flex-direction: column; justify-content: flex-start;}
.article-main .header .article-author .header-left .author-name{ font-size: 30upx; font-weight: 700; color: #222; line-height: 40upx;}
.article-main .header .article-author .header-left .author-time{ font-size: 25upx; color: #999999; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; line-height: 40upx;}
.article-main .header .article-author .header-left .author-time .dot{ width: 6upx; height: 6upx; border-radius: 50%; background-color: #999999; margin: 0 10upx; }
.article-main .header .article-author .header-right{ display: flex; align-items: center;}

.article-main .content{ padding: 0 20upx 20upx;}
.article-main .fx-deta-box{ margin: 20upx 0;}
.article-main .fx-deta-box .yuan{ width: 120upx; height: 120upx; border:2upx solid #F6F6F9; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto; line-height: 1.5; }
.article-main .fx-deta-box .yuan text{ color: #999999; font-size: 25upx;}
.article-main .fx-deta-box:active .yuan,
.article-main .fx-deta-box .yuan text,
.article-main .fx-deta-box .yuan .iconfont{ color: #f92028;}
</style>
