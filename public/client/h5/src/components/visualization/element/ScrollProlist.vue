<template>
    <swiper class="scroll-prolist" :class="{'scroll-prolist-seckill':listType == 'seckill'}" :options="swiperOption" ref="mySwiper">
        <template v-if="listType == 'seckill'">
            <swiper-slide class="spike-swiper-slide" v-for="(item,index) in prolist" :key="item.id">
                <router-link :to="{name:'seckill-detail',query:{seckill_id:item.id,tomorrow:0}}" v-if="authority == 'view'"></router-link>
                <div class="goods-top">
                    <div class="img-box">
                        <template v-if="index > 3">
                             <img v-lazy="item.goods_thumb || item.goods_img" class="img swiper-lazy" alt="">
                        </template>
                        <template v-else>
                            <img v-lazy="item.goods_thumb || item.goods_img" class="img swiper-lazy" alt="">
                        </template>
                    </div>
                    <div class="tagicon"><img src="@/assets/img/seckill-tag.png" class="img" alt=""></div>
                </div>
                <div class="goods-info">
                    <div class="goods-name onelist-hidden"><img v-if="item.country_icon != ''" class="country_icon" :src="item.country_icon" />{{ item.title || item.goods_name }}</div>
                    <currency-price :price="item.sec_price" style="margin-top: 6px;"></currency-price>
                    <currency-price :price="item.market_price" :del="true" style="margin-top: 2px;"></currency-price>
                </div>
            </swiper-slide>
        </template>
        <template v-else>
            <swiper-slide class="spike-swiper-slide" v-for="(item,index) in prolist" :key="item.id">
                <a :href="item.url"></a>
                <div v-box-product-w="{bSizeSel:true,preview}" class="img-box">
                    <template v-if="index > 3">
                         <img v-lazy="item.goods_thumb || item.goods_img" class="img swiper-lazy" alt="">
                    </template>
                    <template v-else>
                        <img v-lazy="item.goods_thumb || item.goods_img" class="img swiper-lazy" alt="">
                    </template>
                </div>
                <h4 v-if="bTitle" class="twolist-hidden">{{ item.title || item.goods_name }}</h4>
                <currency-price :price="item.shop_price" style="margin-top: 5px;"></currency-price>
                <span class="rebate-price">{{item.rebate_price && item.rebate_price !== '0' ? '返:' + item.rebate_price : ''}}</span>
                <currency-price :price="item.market_price" :del="true" style="margin-top: 2px;"></currency-price>
            </swiper-slide>
        </template>
    </swiper>
</template>

<script>
import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default {
    name: "scroll-prolist",
    props: {
        prolist: {},
        scrollNumber: {
            default: 3.4
        },
        bTitle: {},
        preview: {},
        listType:{
            type:String,
            default:''
        }
    },
    components: {
        swiper,
        swiperSlide,
    },
    data() {
        return {
            swiperOption: {
                notNextTick: true,
                slidesPerView: 3.4,
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                lazyLoading: true,
            },
        }
    },
    created() {
        this.swiperOption.slidesPerView = this.scrollNumber
    },
    computed: {
        authority(){
            return window.apiAuthority
        }
    },
    methods:{

    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
@import '../../../assets/css/common/base.scss';
.onelist-hidden {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}
.scroll-prolist {
    background: #fff;
}

.spike-swiper-slide {
    min-height: 4rem;
}

.spike-swiper-slide .price,
.spike-swiper-slide del {
    text-align: center;
    display: block;
}


.scroll-prolist {
    padding: 0 .4rem;
}

.spike-swiper {
    margin-top: -1px;
    border-top: 1px solid $body-background;
}

.spike-swiper-slide {
    text-align: center;
    padding: 1rem .4rem;
    a {
        @include urlAbsolute();
        z-index: 1;
    }
}

.spike-swiper-slide:last-child {
    padding-right: .4rem;
}

.spike-swiper-slide img {
    display: block;
    width: 100%;
}

.spike-swiper-slide h4 {
    font-size: 1.2rem;
    margin-top: .2rem;
    font-weight: 400;
    line-height: 1.5rem;
    height: 3rem;
    @include ellipses();
}

.spike-swiper-slide .price {
    color: $color;
    font-size: 1.4rem;
}
.spike-swiper-slide .rebate-price{
    color: $color;
    font-size:1.3rem;
}
.spike-swiper-slide del {
    color: $subsidiary-color;
    font-size: 1.1rem;
}

/*新版秒杀样式*/
.scroll-prolist-seckill{
    padding: 1rem;
}
.scroll-prolist-seckill .spike-swiper-slide{
    padding: .5rem .3rem;
}
.scroll-prolist-seckill .spike-swiper-slide .goods-top{
    position: relative;
}
.scroll-prolist-seckill .spike-swiper-slide .goods-top .tagicon{
    position: absolute;
    top: 0;
    left: 0;
    width: 5rem
}
.scroll-prolist-seckill .spike-swiper-slide .goods-info{
    padding: 1rem .5rem;
}
.scroll-prolist-seckill .spike-swiper-slide .goods-info .goods-name{ 
    font-size: 1.3rem;
    color: #000;
    font-family: $font-family-jd;
}

.scroll-prolist-seckill .spike-swiper-slide .goods-info .country_icon{
	width: 2.4rem;
	padding-right: 0.3rem;
	position: relative;
	top: 0.2rem;
	display: inline-block;
}
</style>