<template>
  <div class="catalog">
    <Search></Search>
    <div class="category" v-show="cateListAll.length" v-cloak>
        <swiper class="cgl" :options="swiperOption" ref="firstCateSwiper">
            <swiper-slide style="height: auto;">
                <ul>
                    <li class="flex flex-vc" :class="{'active': currentFirstIndex == index}" v-for="(item, index) in cateListAll" :key="item.cat_id"
                    @click="bindChangeFirstCate(index,item.cat_id)">
                        <p>{{item.cat_name}}</p>
                    </li>
                </ul>
            </swiper-slide>
        </swiper>
        
        <div class="cate-swiper-container">
            <swiper class="cate-swiper-cgr cgr" :options="swiperOption3" @setTranslate="swiperTouch" ref="wrapSwiper" v-if="cateListSecond && cateListSecond.length > 0 && !loading" >
                <template>
                    <swiper-slide class="items" :style="{'height':'auto','padding-bottom':(touch_catads ? '10.5rem' : '')}" ref="swiperSlideDome" >
                        <div class="adv" v-if="touch_catads">
                            <a :href="touch_catads_url" v-if="touch_catads_url"><img :src="touch_catads" class="img"></a>
                            <img :src="touch_catads" class="img" v-else>
                        </div>
                        <div class="item" v-for="(item,index) in cateListSecond" :key="item.cat_id">
                            <div class="tit flex flex-vc flex-hc">
                                <i class="row"></i>
                                <router-link :to="{ name: 'list', params: { id: item.cat_id }, query:{title:item.cat_name}}">{{item.cat_name}}</router-link>
                            </div>
                            <ul class="flex flex-vc flex-hw">
                                <li class="flex flex-v flex-vc flex-hc" v-for="third in item.child" :key="third.cat_id">
                                    <router-link :to="{ name: 'list', params: { id: third.cat_id }, query:{title:third.cat_name}}">
                                        <img :src="third.touch_icon" v-if="third.touch_icon">
                                        <img src="../../assets/img/no_image.jpg" v-else>
                                        <span>{{third.cat_name}}</span>
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </swiper-slide>
                </template>
            </swiper>
            <van-loading color="black" size="3rem" v-show="loading"/>
        </div>
    </div>
    <ec-tab-down></ec-tab-down>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import EcTabDown from '@/components/visualization/tab-down/Frontend'
import Search from '@/components/Search'

import {
    Loading
} from 'vant'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default {
    name: 'catalog',
    components: {
        EcTabDown,
        Search,
        swiper,
        swiperSlide,
        [Loading.name]:Loading
    },
    data() {
        return {
            currentFirstIndex: 0,
            swiperOption: {
                direction: 'vertical',
                slidesPerView: 'auto',
                freeMode: true,
            },
            swiperOption2: {
                direction: 'vertical',
            },
            swiperOption3: {
                direction: 'vertical',
                slidesPerView: 'auto',
                freeMode: true,
                freeModeMomentumBounce: false,
                freeModeMomentumVelocityRatio: 0.5,
            },
            timer: null,
            isReady: true,
            leg:0,
            touch_catads:'',
            touch_catads_url:'',
            cat_id:0,
            loading: true,
            tabIndex: 0
        }
    },
    created(){
        let storage = JSON.parse(sessionStorage.getItem('categoryOption'));
        if(storage){
            this.tabIndex = this.currentFirstIndex = storage.index
            this.cat_id = storage.index
        }

        this.$store.dispatch('setCategoryList',{
            index:this.currentFirstIndex
        })
    },
    mounted(){
    },
    computed:{
        ...mapState({
            cateListAll: state => state.category.cateListAll
        }),
        cateListSecond:{
            get(){
                return this.$store.state.category.cateListSecond
            },
            set(val){
                this.$store.state.category.cateListSecond = val
            }
        },
        firstCateSwiper(){
            return this.$refs.firstCateSwiper.swiper
        },
        wrapSwiper(){
            return this.$refs.wrapSwiper.swiper
        }
    },
    methods:{
        swiperTouch(translate) {
            let swipeMain = this.$refs.swiperSlideDome
            if (!swipeMain) {
                return
            }
            let swipeMainHeight = parseInt(this.$refs.swiperSlideDome.$el.scrollHeight)
            let documentHeight = parseInt(document.documentElement.scrollHeight || document.body.scrollHeight)
            let translateHeight = null
            if (swipeMainHeight > documentHeight) {
                translateHeight = swipeMainHeight - (documentHeight - (documentHeight / 7))
            } else {
                translateHeight = documentHeight / 7
            }

            if (Math.abs(translate) > translateHeight) {
                this.manualTriggerCate(this.tabIndex + 1)
            } else if (translate > (documentHeight / 8)) {
                this.manualTriggerCate(this.tabIndex - 1)
            }
        },
        manualTriggerCate(index) {
            if (index < 0 || index > this.cateListAll.length) {
                return
            }
            for(let i=0; i<this.cateListAll.length; i++) {
                if (i === index) {
                    this.bindChangeFirstCate(index, this.cateListAll[i].cat_id)
                    break
                }
            }
        },
        bindChangeFirstCate(index,cat_id){
            this.tabIndex = index
            this.loading = true
            this.cat_id = cat_id
            this.currentFirstIndex = index

            let obj = {
                index:index,
                id:cat_id
            }

            sessionStorage.setItem('categoryOption',JSON.stringify(obj))

            this.$store.dispatch('setCategoryList',{
                index:index,
                id:cat_id
            })
        },
        transitionStart(){
            if(!this.timer){
                this.timer = setTimeout(()=>{
                    this.timer = null
                    if(this.isReady && this.wrapSwiper.isBeginning && this.wrapSwiper.swipeDirection == 'prev'){
                        if(this.currentFirstIndex > 0){
                            this.currentFirstIndex -= 1
                        }else{
                            this.currentFirstIndex = 0
                        }
                        this.cateListAll.forEach((v,i)=>{
                            if(this.currentFirstIndex == i){
                                this.bindChangeFirstCate(this.currentFirstIndex,v.cat_id)
                                this.wrapSwiper.slideTo(0)
                            }
                        })
                    }

                    if(this.isReady && this.wrapSwiper.isEnd && this.wrapSwiper.swipeDirection == 'next'){
                        this.currentFirstIndex += 1
                        this.cateListAll.forEach((v,i)=>{
                            if(this.currentFirstIndex == i){
                                this.bindChangeFirstCate(this.currentFirstIndex,v.cat_id)
                                this.wrapSwiper.slideTo(0)
                            }
                        })
                    }
                })
            }
        },
        handelTouchCatads(){
            this.cateListAll.forEach(v=>{
                if(v.cat_id == this.cat_id){
                    this.touch_catads = v.touch_catads
                    this.touch_catads_url = v.touch_catads_url
                }
            })
        }
    },
    watch:{
        cateListAll(){
            this.cat_id = this.cateListAll[this.currentFirstIndex].cat_id;

            this.$store.dispatch('setCategoryList',{
                id:this.cat_id
            })

            this.handelTouchCatads()
        },
        cat_id(){
            this.handelTouchCatads()
        },
        cateListSecond(){
            this.loading = false
        }
    }
}
</script>

<style lang="scss" >
.cate-swiper-container {
    position: absolute;
    left: 8.5rem;
    right: 0;
    top: 5rem;
    bottom: 0;
    background: #fff;
    overflow: hidden;
    margin-bottom: 5rem;
    .cate-swiper-cgr {
        position: absolute;
        right: 0;
        left: 0;
        top: 0;
        bottom: 0;
        background: #fff;
        overflow: hidden;
        margin-bottom: 0;
    }
}
</style>