<template>
    <div class="home-store" :class="{'home-store-new': styleSel == 1}">
        <template v-if="styleSel == 0">
            <swiper class="store-swiper" :options="swiperOption" ref="mySwiper">
                <swiper-slide class="store-swiper-slide" v-for="(item ,index) in storeList" :key="index">
                    <router-link :to="{name:'shopHome',params:{id:item.user_id}}" v-if="authority == 'view'"></router-link>
                    <div class="box">
                        <h4><img v-lazy="item.logo_thumb" alt=""></h4>
                        <div v-box-product-w="{bSizeSel:'0',preview,type:'store'}" class="img-box" v-lazy:background-image="item.street_thumb" :style="'background: center 0%; background-repeat:no-repeat; background-size: 116%;'">
                        </div>
                        <div class="box_info">
                            <h2>{{ item.rz_shop_name }}</h2>
                            <span>{{$t('lang.sum_to')}} <em>{{ item.goods.length }}</em> {{$t('lang.goods_letter')}}</span>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
        </template>
        <template v-else>
            <div class="floor-header-title relative">
                <h1>店铺推荐</h1>
                <span>{{spikeDesc}}</span>
                <i class="iconfont icon-home-more"></i>
                <router-link :to="{name:'integration',query:{type:1}}" class="absolute-link" v-if="authority == 'view'"></router-link>
            </div>
            <swiper class="store-swiper" :options="swiperOption" ref="mySwiper">
                <swiper-slide class="store-swiper-slide" v-for="(item ,index) in storeList" :key="index">
                    <router-link :to="{name:'shopHome',params:{id:item.user_id}}" v-if="authority == 'view'"></router-link>
                    <div class="store-box">
                        <div class="top"><img v-lazy="item.street_thumb" class="img" alt=""></div>
                        <div class="info">
                            <div class="logo"><img v-lazy="item.logo_thumb" class="img" alt=""></div>
                            <div class="name onelist-hidden">{{ item.rz_shop_name }}</div>
                            <div class="desc onelist-hidden">
                                <span>{{$t('lang.sum_to')}} <em>{{ item.goods.length }}</em> {{$t('lang.goods_letter')}}</span>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
            <div class="adv-list">
                <div class="item" v-for="(item,index) in data.list" :key="index">
                    <div class="adv-img"><a v-href="{sUrl:item.url,preview:preview}"><img :src="item.img" class="img" /></a></div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
//node library
import qs from 'qs'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

//mixins
import frontendGet from '@/mixins/frontend-get'

export default {
    name: "store",
    props: ['data', 'preview'],
    mixins: [frontendGet],
    components: {
        swiper,
        swiperSlide,
    },
    data() {
        return {
            swiperOption: {
                notNextTick: true,
                slidesPerView: 1.8
            },
            storeList: []
        }
    },
    created() {
        this.$http.post(`${window.ROOT_URL}api/visual/store`, qs.stringify({
            number: this.nNumber < 1 ? 1 : this.nNumber
        })).then(({ data: { data } }) => {
            if (data || data.length > 0) {
                this.storeList = data
            }else{
                this.storeList = []
            }
        }).catch(err => {
            console.error(err)
        })

        if(this.styleSel == 1){
            this.swiperOption.slidesPerView = 'auto';
        }
    },
    methods: {
        getShopUrl(id) {
            return ``
        },
    },
    computed: {
        styleSel: {
            get() {
                return this.data.isStyleSel ? this.data.isStyleSel : 0
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
            }
        },
        nNumber() {
            return this.data.allValue.number
        },
        spikeDesc() {
            return this.getText({
                dataNext: "allValue",
                attrName: "spikeDesc",
                defaultValue: '更多品质好店'
            })
        },
        authority(){
            return window.apiAuthority
        }
    }

}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
@import '../../../assets/css/common/base.scss';
.home-store .store-swiper{
    background: none;
}
.store-swiper .swiper-slide{
    background-color: #fff;
}
.store-swiper .box{
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    margin-left: .8rem;
}

.store-swiper-slide{
    a{
        display: block;
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 999;
    }
}

.store-swiper .swiper-slide{
    background: none;
}

.store-swiper h4{
    position: absolute;
    width: 25%;
    border-radius: 50%;
    background: #fff;
    overflow: hidden;
    border:1px solid #f5f5f5;
    bottom:30%;
    left: 38%;
    z-index: 9;
    img{
        width:100%;
    }
}

.box_info{
    background-color: #fff;
    width: 100%;
    position: absolute;
    bottom: 0;
    height: 40%;
    text-align: center;

    h2{
        margin-top: 2.5rem;
        font-size: 1.6rem;
        color: #000;
    }
    span{
        color: #888;
        font-size: 1.4rem;
        margin-top: .5rem;
        display: block;
        em{
            color: #000;
        }
    }
}

/* 新版样式 */
.home-store-new{
    background-color: #fff;
    margin: 1rem 1rem 0;
}

.floor-header-title{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 1.5rem 1rem;
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
    margin: 2px 0 0 5px;
}

/*新版店铺*/
.home-store-new{ border-radius: 1rem; }
.home-store-new .store-swiper{ padding: 0 0 0 1rem; }
.home-store-new .store-swiper-slide{ width: 31%; margin-right: 2.5%; border-radius: 1rem; overflow: hidden;}
.home-store-new .store-swiper-slide .store-box{ background-color: #f6f6f6; }
.home-store-new .store-swiper-slide .store-box .top{ width: 100%; height: 8.5rem !important; overflow: hidden;}
.home-store-new .store-swiper-slide .store-box .top .img{ height: 100%; }
.home-store-new .store-swiper-slide .store-box .info{ position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem .5rem 2rem;}
.home-store-new .store-swiper-slide .store-box .info .logo{ width: 3.5rem; height: 3.5rem; border-radius: 50%; border:1px solid #fff; position: absolute; top: -1.8rem;}
.home-store-new .store-swiper-slide .store-box .info .logo .img{ border-radius: 50%; }
.home-store-new .store-swiper-slide .store-box .info .name{ font-size: 1.4rem; color: #333; height: 2rem; line-height: 2rem;}
.home-store-new .store-swiper-slide .store-box .info .desc{ color: #888; font-size: 1.2rem; margin-top: .5rem;}
.home-store-new .store-swiper-slide .store-box .info .desc em{ color: #333; margin: 0 .1rem; }

.home-store-new .adv-list{ padding: 1rem; display: flex; flex-direction: row; justify-content: space-between; flex-wrap: wrap;}
.home-store-new .adv-list .item{ border-radius: 1rem; width: calc(50% - .5rem);}
</style>
