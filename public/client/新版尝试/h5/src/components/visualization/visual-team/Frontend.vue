<template>
    <div class='visual-team'>
        <div class="floor-header-title relative">
            <h1>{{$t('lang.team_topic')}}</h1>
            <span>{{spikeDesc}}</span>
            <i class="iconfont icon-home-more"></i>
            <router-link :to="{name:'team'}" class="absolute-link" v-if="authority == 'view'"></router-link>
        </div>
        <div class="floor-content">
            <swiper class="scroll-prolist" :options="swiperOption" ref="mySwiper">
                <swiper-slide class="spike-swiper-slide relative" v-for="(item,index) in list" :key="index">
                    <router-link :to="{name:'team-detail',query:{goods_id:item.goods_id,team_id:0}}" class="absolute-link" v-if="authority == 'view'"></router-link>
                    <div class="goods-top">
                        <div class="img-box">
                            <img v-lazy="item.goods_thumb" class="img swiper-lazy" alt="">

                            <div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
                                <img :src="item.goods_label_suspension.formated_label_image" class="img">
                            </div>
                        </div>
                    </div>
                    <div class="goods-info">
                        <div class="goods-name onelist-hidden">{{ item.goods_name }}</div>
                        <currency-price :price="item.team_price" style="margin-top: 5px;">
                            <img src="@/assets/img/shopping-icon.png" class="shopping-icon">
                        </currency-price>
                        <currency-price :price="item.shop_price" :del="true" style="margin-top: 3px;"></currency-price>
                    </div>
                </swiper-slide>
            </swiper>
        </div>
    </div>
</template>

<script>
//node library
import qs from 'qs'

// third party components
import Vue from 'vue'
import { swiper, swiperSlide } from 'vue-awesome-swiper'

//mixins
import frontendGet from '@/mixins/frontend-get'

export default {
    name: 'visual-team',
    props: ['data', 'preview', 'modulesIndex','shopId','show'],
    mixins: [frontendGet],
    components: {
        swiper,
        swiperSlide
    },
    data() {
        return {
            swiperOption: {
                notNextTick: true,
                slidesPerView: 3,
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                lazyLoading: true,
            },
            list:[]
        }
    },
    created() {
        this.$http.get(`${window.ROOT_URL}api/visual/visual_team_goods`).then(({ data:{ data }}) => {
            this.list = data;
        }).catch(err => {
            console.error(err)
        })
    },
    mounted() {

    },
    computed: {
        swiper() {
            return this.$refs.mySwiper.swiper
        },
        spikeDesc() {
            return this.getText({
                dataNext: "allValue",
                attrName: "spikeDesc",
                defaultValue: this.$t('lang.seckill_desc_placeholder')
            })
        },
        nNumber() {
            return this.data.allValue.number
        },
        authority(){
            return window.apiAuthority
        },
    }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/css/common/base.scss';

.visual-team{
    margin: 1rem 1rem 0;
    background-color: #fff;
    border-radius: 1rem;
}
.floor-header-title{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 1.5rem 1rem;
    position: relative;
}
.floor-header-title:after{
    position: absolute;
    content: " ";
    height: 1px;
    background-color: #eee;
    left: 1rem;
    right: 1rem;
    bottom: 0;
}
.floor-header-title h1{
    font-size: 1.8rem;
    color: #000;
    font-weight: 600;
    margin: 0 1rem 0 .5rem;
}
.floor-header-title span{
    color: #888;
    font-size: 1.4rem;
}
.floor-header-title .iconfont{
    color: #F20D23;
    font-size: 1.4rem;
    margin: 0 0 0 5px;
}

.floor-content{
    padding: 1.5rem 0 0 1rem;
}
.spike-swiper-slide{
    padding: 0rem .3rem;
}
.spike-swiper-slide .goods-top{
    position: relative;
}
.spike-swiper-slide .goods-info{
    padding: 1rem .5rem;
    text-align: center;
}
.spike-swiper-slide .goods-info .goods-name{
    font-size: 1.3rem;
    color: #000;
    font-family: $font-family-jd;
    height: 2rem;
    line-height: 2rem;
}
.spike-swiper-slide .goods-info .price{
    font-size: 1.5rem;
    color: #F20D28;
    font-weight: 700;
    margin-top: .4rem;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
}
.spike-swiper-slide .goods-info /deep/ .currency-price .shopping-icon{
    width: 1.8rem;
    height: 1.8rem;
    display: inline-block;
    margin: 0 .5rem 0 0;
    vertical-align: bottom;
}
</style>
