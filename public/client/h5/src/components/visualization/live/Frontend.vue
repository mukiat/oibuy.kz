<template>
    <section class="slide">
        <div class="separat-show">
            <separate-img-list :list="data.list" :isMiniList="bMiniList" :preview="preview" :isList="bList"></separate-img-list>
        </div>
    </section>
</template>

<script>
// custom components
import SeparateImgList from '../element/SeparateImgList'

// third party components
import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default {
    name: 'live',
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
                    disableOnInteraction: false
                },
                loop:true
            }
        }
    },
    mounted() {
        
    },
    computed: {
        bSeparateShow() {
            return '1' == this.data.isStyleSel ? true : false
        },
        bMiniList() {
            return '1' == this.data.isSizeSel ? true : false
        },
        bList() {
            return 0 != this.data.list.length ? true : false
        },
        swiper() {
          
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
</style>