<template>
	<div class="con" id="article" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<template v-if="articleCateList && articleCateList.length > 0">
			<div class="article-nav dis-box">
				<swiper class="article-nav-items box-flex" :options="swiperOption" ref="mySwiper">
					<swiper-slide class="article-nav-item"><a href="javascript:void(0)" @click="handleArticle(0)" :class="{'active':cat_id == 0}">{{$t('lang.all_article')}}</a></swiper-slide>
					<swiper-slide class="article-nav-item" v-for="(item, index) in articleCateList" :key="index"><a href="javascript:void(0)" @click="handleArticle(item.cat_id)" :class="{'active':cat_id == item.cat_id}">{{ item.cat_name }}</a></swiper-slide>
				</swiper>
				<div class="article-nav-select">
					<i class="iconfont icon-moreunfold" @click="handlePopup"></i>
				</div>
			</div>
			<div class="article-info">
				<template v-if="articleList && articleList.length > 0">
					<div class="article-list" v-for="(item,index) in articleList" :key="index">
						<div class="dis-box">
							<div class="article-left box-flex">
								<h3><router-link :to="{'name':'articleDetail',params:{id:item.id}}">{{item.title}}</router-link></h3>
                                <template v-if="item.file_url">
                                    <img :src="item.file_url">
                                </template>
                                <template v-else>
                                    <template v-if="item.content_img_list && item.content_img_list.length > 0">
                                        <img v-for="(img) in item.content_img_list" :src="img">
                                    </template>
                                </template>
								<div class="article-foot dis-box">
									<div class="article-left-cont"><em>{{ item.author }}</em></div>
									<div class="box-flex article-right-cont">
										<em><i class="iconfont icon-shijian"></i>{{ item.amity_time }}</em>
										<em><i class="iconfont icon-liulan"></i>{{ item.click }}</em>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
	                <template v-if="loading">
	                    <van-loading type="spinner" color="black" />
	                </template>
				</template>
				<template v-else>
					<NotCont></NotCont>
				</template>
			</div>
		</template>
		<template v-else>
			<NotCont></NotCont>
		</template>
		<van-popup v-model="show" position="top" :overlay="true" class="article-popup">
		  <ul class="shopping-abs">
		  	<li v-for="(item, index) in articleCateList" :class="{'active':cat_id == item.cat_id}" :key="index" @click="handleArticle(item.cat_id)">{{ item.cat_name }}</li>
		  </ul>
		</van-popup>
		<CommonNav></CommonNav>
	</div>
</template>
<script>
import { mapState } from 'vuex'
import {
    Waterfall,
    Loading,
    Dialog,
    Toast,
    Popup
} from 'vant'

import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default{
	data(){
		return {
			disabled:false,
            loading:true,
            page:1,
            size:10,
            footerCont:false,
			swiperOption:{
				notNextTick: true,
		        watchSlidesProgress: true,
		        watchSlidesVisibility: true,
		        slidesPerView: 'auto',
		        lazyLoading: true,
			},
			show:false,
			cat_id:this.$route.query.cat_id ? this.$route.query.cat_id : 0
		}
	},
	directives: {
        WaterfallLower: Waterfall('lower')
    },
	components:{
        swiper,
        swiperSlide,
        NotCont,
        CommonNav,
	    [Popup.name]:Popup,
        [Dialog.name]: Dialog,
        [Loading.name]: Loading,
	},
	created(){
		this.$store.dispatch('setArticleCate',{
			cat_id:0
		})

		this.handlearticleList()
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
		}
	},
	methods:{
		handlePopup(){
			this.show = this.show == true ? false : true
		},
		handlearticleList(page){
            this.show = false
            
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
		handleArticle(val){
			if(val != 0){
				this.$router.push({
					name:'article',
					query:{
						cat_id:val
					}
				})
			}else{
				this.$router.push({
					name:'article'
				})
			}
		},
		loadMore(){
            setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.articleList.length){
                    this.page ++
                    this.handlearticleList()
                }
            },200);
        },
	},
	watch:{
		articleList(){
			if(this.page * this.size == this.articleList.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
                this.footerCont = this.page > 1 ? true : false
            }
            this.articleList = arrRemove.trimSpace(this.articleList)
		},
        '$route'(to,from){
            let cat_id = to.query.cat_id ? to.query.cat_id : 0

            this.cat_id = cat_id
            this.handlearticleList(1)
        }
	}
}
</script>
