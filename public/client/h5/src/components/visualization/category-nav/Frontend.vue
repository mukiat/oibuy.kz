<template>
    <div class='category-nav d_jump'>
        <div class="category-nav-warp" :class="{'position-fixed': scrollFixed}" :style="{'background-color':backgroundColor,'top':topHeight}">
            <swiper class="swiper" :options="swiperOption2" ref="announSwiper">
                <swiper-slide class="swiper-slide" :class="{'active': currentIndex == 0}"><span @click="cateClick(0,0)">{{$t('lang.home')}}</span></swiper-slide>
                <swiper-slide class="swiper-slide" :class="{'active': currentIndex == index+1}" v-for="(item, index) in categoryNavList" :key="index">
                    <span @click="cateClick(index+1,item.cat_id)">{{item.cat_alias_name}}</span>
                </swiper-slide>
            </swiper>
            <div class="category-filter">
                <router-link :to="{name:'catalog'}" v-if="authority == 'view'" class="absolute-link"></router-link>
                <i class="iconfont icon-home-dingjifenlei"></i>
                <span>{{$t('lang.category')}}</span>
            </div>
        </div>
        <div class="seize-seat" v-if="scrollFixed"></div>
    </div>
</template>

<script>
//node library
import qs from 'qs'

// third party components
import Vue from 'vue'
import { swiper, swiperSlide } from 'vue-awesome-swiper'

export default {
    name: 'category-nav',
    props: ['data', 'preview', 'modulesIndex','shopId','scrollFixed','localShowVal','fristBackgroundColor'],
    components: {
        swiper,
        swiperSlide
    },
    data() {
        return {
            swiperOption2:{
                notNextTick: true,
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                slidesPerView: 'auto',
                lazyLoading: true,
            },
            scroll: true,
            currentIndex:0,
            categoryNavList:[],
            topHeight:localStorage.getItem('localShow') === 'true' ? (this.scrollFixed ? '5rem' : '-5rem') : (this.scrollFixed ? '5rem' : '-5rem')
        }
    },
    created() {
        /*顶级分类*/
        if(sessionStorage.getItem('categoryTopNav')){
            this.categoryNavList = JSON.parse(
                sessionStorage.getItem('categoryTopNav')
            )
        }else{
            this.resCategoryCOption1().then(() => {
                sessionStorage.setItem(
                    'categoryTopNav',
                    JSON.stringify(this.categoryNavList)
                )
            })
        }
    },
    mounted() {

    },
    computed: {
        swiper() {
            return this.$refs.announSwiper.swiper
        },
        backgroundColor(){
            return !this.scrollFixed ? this.data.allValue.bgColor : this.fristBackgroundColor
        },
        authority(){
            return window.apiAuthority
        }
    },
    methods:{
        resCategoryCOption1() {
            return this.$http.get(`${window.ROOT_URL}api/visual/visual_category`)
            .then(({ data: { data } }) => {
                this.categoryNavList = data;
            }).catch(err => {
                console.error(err)
            })
        },
        cateClick(index,cat_id){
            this.currentIndex = index;

            this.$store.dispatch('updateIsShow', {
                type: index == 0 ? true : false,
                cat_id: cat_id
            });
        }
    },
    watch:{
        scrollFixed(){
            this.topHeight = localStorage.getItem('localShow') === 'true' ? (this.scrollFixed ? '10rem' : '0') : (this.scrollFixed ? '5rem' : '0')
        },
        localShowVal(){
            if(!this.localShowVal){
                this.topHeight = 0
            }
        }
    }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/css/common/base.scss';
.category-nav-warp,
.seize-seat{ height: 3.6rem; line-height: 3.6rem; font-size: 1.4rem; color: #fff; padding: 0 1rem .5rem; display: flex; flex-direction: row;align-items: center; box-sizing: content-box;}
.swiper{ flex: 1; }
.swiper-slide{ float: left; width: auto; padding-right: 1.5rem; position: relative;}
.swiper-slide:first-child{ padding-left: 0; }
.swiper-slide.active{
    font-weight: 700;
}
.swiper-slide.active:after{
    content: ' ';
    position: absolute;
    border: 2px solid #fff;
    width: 12px;
    height: 6px;
    border-radius: 0 0 50% 50%/0 0 100% 100% ;
    bottom: 0;
    left: calc(50% - 0.75rem - 6px);
    border-top: none;
}
.category-filter{ padding-left: 1rem; box-shadow: -6px 0 4px -4px rgba(0,0,0,.4); height: 2.5rem; line-height: 2.5rem; position: relative; width: 8rem;}
.category-filter .iconfont{ font-size: 1.2rem; margin-right: .5rem;}

.category-nav-warp{
    transition: top .3s ease-out;
    width: 100%;
}
.position-fixed{
    position: fixed;
    z-index: 1999;
    top: 0;
}
</style>
