<template>
    <div class="con drp-protection">
        <swiper class="gallery-thumbs" :options="swiperOptionThumbs" ref="swiperThumbs">
            <swiper-slide v-for="(item,index) in protectionList.list" :key="index">
                <div class="icon"><div class="img-box"><img :src="item.icon" class="img" /></div></div>
                <div class="text">{{item.name}}</div>
            </swiper-slide>
        </swiper>
        <swiper class="gallery-top" :options="swiperOptionTop" ref="swiperTop">
            <swiper-slide v-for="(item,index) in protectionList.list" :key="index">
                <div class="protection-con" :style="{height:winHeight + 'px'}">
                    <div class="title">{{item.name}}</div>
                    <div class="value">
                        <div class="tit">{{$t('lang.vip_protection')}}</div>
                        <div class="text">{{item.description}}</div>
                        <div class="tit">{{$t('lang.trigger_content')}}</div>
                        <div class="text">{{item.rights_configure_format}}</div>
                        <template v-if="item.code == 'customer' && item.rights_configure[0].value">
                            <div class="tit">{{$t('lang.equity_rule')}}</div>
                            <div class="text">{{item.rights_configure[0].label}}:<a style="color: #007aff;" :href="'tel:'+ item.rights_configure[0].value">{{item.rights_configure[0].value}}</a></div>
                        </template>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
    </div>
</template>

<script>
import qs from 'qs'
import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

import { mapState } from 'vuex'

let vm = null

export default{
    data(){
        return{
            swiperOptionTop: {
                slidesPerView: "auto",
                centeredSlides: true,
                watchSlidesProgress: true,
                paginationClickable: true,
                on:{
                    init(){
                        this.slideTo(vm.index, 0, false);
                    }
                }
            },
            swiperOptionThumbs: {
              slidesPerView: 4,
              centeredSlides: true,
            },
            winHeight:0,
            rank_id:this.$route.query.rank_id ? this.$route.query.rank_id : '',
            index:this.$route.query.index ? this.$route.query.index : 0,
            protectionList:[]
        }
    },
    components:{
        swiper,
        swiperSlide
    },
    computed:{

    },
    created(){
        vm = this;

        this.$http.post(`${window.ROOT_URL}api/user/rank_rights_list`,qs.stringify({
            rank_id: this.rank_id
        })).then(res=>{
            this.protectionList = res.data.data;
        })
    },
    mounted() {
      this.$nextTick(() => {
        const swiperTop = this.$refs.swiperTop.swiper
        const swiperThumbs = this.$refs.swiperThumbs.swiper
        swiperTop.controller.control = swiperThumbs
        swiperThumbs.controller.control = swiperTop

        this.winHeight = (window.innerHeight * .6);
      })
    },
    methods:{

    }
}
</script>

<style>
.drp-protection{
    background: #151515;
}
.swiper-wrapper {
    margin-top:10px
}
.gallery-top .swiper-slide {
    width:80%;
    -webkit-transform-style:preserve-3d;
    -moz-transform-style:preserve-3d;
    -ms-transform-style:preserve-3d;
    transform-style:preserve-3d;
}
.gallery-top .swiper-slide-next,.gallery-top .swiper-slide-prev{
    opacity: 0.5;
    transform: scale(.8) !important;
}
.gallery-top .swiper-slide-next{
    position: relative;
    left: -13%
}
.gallery-top .swiper-slide-prev{
    position: relative;
    right: -13%
}
.gallery-top .swiper-slide-active:before{
    position: absolute;
    content: " ";
    width:0;
    height:0;
    border-right:8px solid transparent;
    border-left:8px solid transparent;
    border-bottom:10px solid white;
    top: -10px;
    left: 50%;
    margin-left: -4px;
}
.gallery-top .swiper-slide .protection-con{
    width: 80%;
    overflow-y: auto;
    margin:0 auto;
    padding: 2rem;
    background-color: #fff;
    border-radius: .5rem;
    font-size: 1.2rem;
}
.gallery-top .swiper-slide .protection-con .title{
    font-size: 2rem;
    font-weight: 700;
    color: #000;
    text-align: center;
}
.gallery-top .swiper-slide .protection-con .value{}
.gallery-top .swiper-slide .protection-con .tit{
    font-size: 1.4rem;
    font-weight: 700;
    margin: 1.5rem 0 .5rem;
}
.gallery-top .swiper-slide .protection-con .text{
    line-height: 1.8;
}

.gallery-thumbs{
    position: relative;
    margin: 3rem 0;
}
.gallery-thumbs .swiper-slide{
    display: flex;
    color: #fff;
    font-size: 1.2rem;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-right: 2rem;
}
.gallery-thumbs .swiper-slide .icon{
    width: 80%;
    height: 80%;
    margin: 0 auto 1rem;
}
.gallery-thumbs .swiper-slide .icon .img{
    border-radius: 50%;
}
</style>
