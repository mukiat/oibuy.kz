<template>
	<view class="article">
		<view class="uni-tab-bar">
			<scroll-view id="tab-bar" class="uni-swiper-tab" scroll-x :scroll-left="scrollLeft">
				<view class="swiper-tab-list" :class="cat_id == 0 ? 'active' : ''" @click="handleArticle(0)">{{$t('lang.all_categories')}}</view>
				<view v-for="(item,index) in articleCateList" :key="index" class="swiper-tab-list" :class="cat_id == item.cat_id ? 'active' : ''" @click="handleArticle(item.cat_id)">{{item.cat_name}}</view>
			</scroll-view>
			<view class="article-box">
				<block v-if="articleList && articleList.length > 0">
					<view class="article-list" v-for="(item,index) in articleList" :key="index" @click="$outerHref('/pagesC/article/detail/detail?id='+item.id,'app')">
						<view class="title">{{item.title}}</view>
						<view class="imglist" v-if="item.content_img_list && item.content_img_list.length > 0">
							<view class="li" v-for="(imgList,imgIndex) in item.content_img_list"><image :src="imgList" mode="widthFix"></image></view>
						</view>
						<view class="foot">
							<view class="author">{{item.author}}</view>
							<view class="option">
								<view class="click">{{ item.click }}{{$t('lang.ge_view')}}</view>
								<view class="time">{{ item.amity_time }}</view>
							</view>
						</view>
					</view>
					<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
				</block>
				<block v-else>
					<dsc-not-content></dsc-not-content>
				</block>
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
				cat_id:0,
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
			if(this.page * this.size == this.articleList.length){
				this.page ++
				this.handlearticleList()
			}else{
				this.loadMoreText = this.$t('lang.contentnomore')
				return;
			}
		},
		methods: {
			handlearticleList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setArticleList',{
					cat_id:this.cat_id,
					keywords:'',
					page:this.page,
					size:this.size
				})
			},
			handleArticle(id){
				if(this.cat_id != id){
					this.cat_id = id;
					this.handlearticleList(1);
				}
			}
		},
		computed:{
			...mapState({
				articleCateList: state => state.article.articleCateList,
			}),
			articleList:{
				get(){
					return this.$store.state.article.articleList
				},
				set(val){
					this.$store.state.article.articleList = val
				}
			},
		},
		onLoad(e){
			this.cat_id = e.cat_id ? e.cat_id : 0;
			
			this.$store.dispatch('setArticleCate',{
				cat_id:0
			})
			
			this.handlearticleList(1)
		}
	}
</script>

<style>
.uni-tab-bar .uni-swiper-tab{ position: fixed; top: 0; left: 0; right: 0; background: #FFFFFF; }
.uni-tab-bar .article-box{ padding-top: 100upx;}
.uni-loadmore{ padding: 30upx 0;}
</style>
