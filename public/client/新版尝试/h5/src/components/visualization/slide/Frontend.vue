<template>
    <section class="slide" :class="{'slide-new':bStyleSel == 2}">
        <div class="bg-ellipse" :style="{'background-color':backgroundColor}" v-if="bStyleSel == 2"></div>
        <div :class="bPaginationSel" v-if="!bSeparateShow">
            <swiper class="swiper" :options="swiperOption" ref="slideSwiper" @slideChange="slideChange" >
                <!-- 幻灯内容 -->
                <swiper-slide v-for="(item,index) in data.list" :key="index">
                    <a v-href="{sUrl:item.url,preview:preview}"></a>
                    <span class="desc" v-show="'' != item.desc">{{ item.desc }}</span>
                    <img :src="item.img" class="swiper-lazy" v-if="item.img" />
                    <img src="@/assets/img/default-img.jpg" class="swiper-lazy no-img" v-else/>
                </swiper-slide>
                <!-- 以下控件元素均为可选（使用具名slot来确定并应用一些操作控件元素） -->
                <div class="swiper-pagination" slot="pagination"></div>
            </swiper>
        </div>
        <div v-else class="separat-show" :class="{'separat-show-new':bSeparateSel}">
            <separate-img-list :list="data.list" :isMiniList="bMiniList" :preview="preview" :isList="bList"></separate-img-list>
        </div>
    </section>
</template>

<script>
// custom components
import SeparateImgList from '../element/SeparateImgList'

import {
    mapActions,
    mapState
} from 'vuex'

// third party components
import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default {
    name: 'slide',
    props: ['data', 'preview', 'modulesIndex'],
    components: {
        swiper,
        swiperSlide,
        SeparateImgList
    },
    data() {
        return {
            swiperOption:{
                pagination:{
                    el:'.swiper-pagination',
                },
                slidesPerView:1,
                autoplay:{
                    delay:3000,
                    stopOnLastSlide:false,
                    disableOnInteraction: false
                }
            },
            activeIndex:0
        }
    },
    mounted() {
        if(this.bStyleSel == 2){
            this.$store.dispatch('updateGlobalBgColor', {
                bgColor:this.backgroundColor
            })
        }
    },
    computed: {
        ...mapState({
            topCategoryCatid: state => state.topCategoryCatid,
        }),
        bSeparateShow() {
            return '1' == this.data.isStyleSel ? true : false
        },
        bMiniList() {
            return '1' == this.data.isSizeSel ? true : false
        },
        bList() {
            return 0 != this.data.list.length ? true : false
        },
        bStyleSel(){
            return this.data.isStyleSel
        },
        backgroundColor(){
            return this.data.list.length > 0 ? this.data.list[this.activeIndex].bgColor : ''
        },
        bPaginationSel(){
            let sel = ''

            if(this.data.isPaginationSel == 0){
                sel = 'pagination-left'
            }else if(this.data.isPaginationSel == 1){
                sel = 'pagination-center'
            }else{
                sel = 'pagination-right'
            }

            return sel
        },
        bSeparateSel(){
            return this.data.isSeparateSel == '1' ? true : false
        },
        swiper() {
            return this.$refs.slideSwiper.swiper
        }
    },
    methods:{
        slideChange(){
            if(this.bStyleSel == 2){
                this.activeIndex = this.$refs.slideSwiper.swiper.activeIndex

                this.$store.dispatch('updateGlobalBgColor', {
                    bgColor:this.backgroundColor
                })
            }
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/mixins/common.scss';
.slide .swiper {
    width: 100%;
}

.slide .swiper-slide img {
    width: 100%;
    display: block;
}

.swiper-lazy-preloader {
    margin-top: 1rem;
    width: 1.6rem;
}

.slide .swiper-slide a {
    @include urlAbsolute();
}

.slide .desc {
    position: absolute;
    padding: .4rem;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, .5);
    color: #fff;
}

.swiper-slide {
    background: #f4f5fa;
    @include box-pack(center, center);
    @include direction(center, center)
}

.slide .swiper-slide.no-swiper-slide img,
.slide .swiper-slide img.no-img {
    width: 50%;
    height: auto;
}

/*新版轮播样式*/
.slide-new{
    padding: 0 1rem;
    position: relative;
    overflow: hidden;
}
.slide-new .bg-ellipse{
    border-radius: 30% 30%;
    height: 10rem;
    position: absolute;
    top: -4rem;
    left: 0;
    right: 0;
    z-index: 1;
}
.slide-new .swiper{
    border-radius: 1rem;
}

/*动态指示器样式*/
.swiper-pagination{
    left: 1.5rem;
    right: 1.5rem;
    bottom: 1rem;
    width: calc(100% - 3rem);
}
.swiper-pagination /deep/ .swiper-pagination-bullet{
    margin: 0 3px;
    width: 6px;
    height: 6px;
    background:rgba(0,0,0,.2);
    opacity: 1;
}
.swiper-pagination /deep/ .swiper-pagination-bullet-active{
    background:rgba(0,0,0,.5);
}

.pagination-left .swiper-container-horizontal > .swiper-pagination-bullets{
    text-align: left;
}
.pagination-center .swiper-container-horizontal > .swiper-pagination-bullets{
    text-align: center;
}
.pagination-right .swiper-container-horizontal > .swiper-pagination-bullets{
    text-align: right;
}

/*新版分开显示图片样式*/
.separat-show-new{ padding: 1rem 1rem 0; }
</style>