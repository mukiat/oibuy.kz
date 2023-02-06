<template>
  <div class="catalog">
    <section class="secrch">
        <div class="secrch-warp">
            <div class="back" @click="onClickLeft"><i class="iconfont icon-back"></i></div>
            <div class="input-text">
                <input class="j-input-text" type="search" name="keyword" autocomplete="off" v-model="keyword" @keypress="search">
                <label class="search-check" @click="onSearch"><i class="iconfont icon-search"></i></label>
            </div>
        </div>
    </section>
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
        <swiper class="cgr" :options="swiperOption3" ref="wrapSwiper">
            <template v-if="cateListSecond && cateListSecond.length > 0">
                <swiper-slide class="items" style="height: auto; padding-bottom:6.5rem;">
                    <div class="adv" v-if="touch_catads">
                        <a href="javascript:;"><img :src="touch_catads" class="img"></a>
                    </div>
                    <div class="item" v-for="(item,index) in cateListSecond" :key="item.cat_id">
                        <div class="tit flex flex-vc flex-hc">
                            <i class="row"></i>
                            <a href="javascript:;" v-if="drptype == 1"><label class="ect-radio" :class="{'active':item.drp_type}" @click="fcatCheck(item.cat_id,item.drp_type)"><i class="iconfont icon-gou"></i></label>{{item.cat_name}}</a>
                            <router-link :to="{ name: 'drp-drplist', query: { id: item.cat_id }}" v-else>{{item.cat_name}}</router-link>
                        </div>
                        <ul class="flex flex-vc flex-hw">
                            <template v-if="drptype == 1">
                                <li class="flex flex-v flex-vc flex-hc" v-for="third in item.child" :key="third.cat_id" @click="catCheck(item.cat_id,third.cat_id)">
	                                <label class="ect-radio" :class="{'active':third.drp_type}"><i class="iconfont icon-gou"></i></label>
	                                <img :src="third.touch_icon" v-if="third.touch_icon">
	                                <img src="../../../../assets/img/no_image.jpg" v-else>
	                                <span>{{third.cat_name}}</span>
                                </li>
                            </template>
                            <template v-else>
                                <li class="flex flex-v flex-vc flex-hc" v-for="third in item.child" :key="third.cat_id">
                                    <router-link :to="{ name: 'drp-drplist', query: { id: third.cat_id }}">
                                        <img :src="third.touch_icon" v-if="third.touch_icon">
                                        <img src="../../../../assets/img/no_image.jpg" v-else>
                                        <span>{{third.cat_name}}</span>
                                    </router-link>
                                </li>
                            </template>
                        </ul>
                    </div>
                </swiper-slide>
            </template>
            <van-loading type="spinner" color="black" size="3rem" />
        </swiper>
    </div>
    <CommonNav :routerName="routerName">
	     <li slot="aloneNav">
			<router-link :to="{name: 'drp'}">
				<i class="iconfont icon-fenxiao"></i>
				<p>{{$t('lang.drp_center')}}</p>
			</router-link>
		</li>
	</CommonNav>
    <ec-tab-down></ec-tab-down>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import EcTabDown from '@/components/visualization/tab-down/Frontend'
import Search from '@/components/Search'

import CommonNav from '@/components/CommonNav'

import {
    Loading,
    Toast
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
        CommonNav,
        [Toast.name]:Toast,
        [Loading.name]:Loading
    },
    data() {
        return {
        	routerName:'drp',
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
            cat_id:0,
            keyword:''
        }
    },
    created(){
        this.$store.dispatch('setDrpCategoryLists',{
            index:this.currentFirstIndex
        })
    },
    mounted(){
    },
    computed:{
        ...mapState({
            cateListAll: state => state.drp.cateListAll
        }),
        cateListSecond:{
            get(){
                return this.$store.state.drp.cateListSecond
            },
            set(val){
                this.$store.state.drp.cateListSecond = val
            }
        },
        drptype(){
        	return this.$store.state.drp.drptype
        },
        firstCateSwiper(){
            return this.$refs.firstCateSwiper.swiper
        },
        wrapSwiper(){
            return this.$refs.wrapSwiper.swiper
        }
    },
    methods:{
        bindChangeFirstCate(index,cat_id){
            this.cat_id = cat_id
            this.currentFirstIndex = index
            this.cateListSecond = []
            this.$store.dispatch('setDrpCategoryLists',{
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
                }
            })
        },
        catCheck(f_cat_id,cat_id){
        	this.$store.dispatch('setDrpCategoryAdd',{
        		id:cat_id,
        		f_id:f_cat_id,
        		type:0
        	})
        },
        fcatCheck(cat_id,drptype){
        	let arr = [];
        	let type = drptype == true ? 2 : 1;

        	this.cateListSecond.forEach(res=>{
        		if(res.cat_id == cat_id){
        			res.child.forEach(result=>{
        				arr.push(result.cat_id)
        			})
        		}
        	})

        	//if(arr.length > 0){
	        	this.$store.dispatch('setDrpCategoryAdd',{
	        		id:arr,
	        		cur_id:cat_id,
	        		type:type
	        	})
        	//}
        },
        onClickLeft(){
            this.$router.go(-1);
        },
        search(event){
            if (event.keyCode == 13) {
                event.preventDefault(); //禁止默认事件（默认是换行）
                this.keyword = event.target.value;
                this.onSearch();
            }
        },
        onSearch(){
            if(this.drptype == 2){
                this.$router.push({
                    name:'drp-drplist',
                    query:{
                        keyword:this.keyword
                    }
                })
            }else{
                Toast("只有商品模式可以搜索")
            }
        }
    },
    watch:{
        cateListAll(){
            this.cat_id = this.cateListAll[this.currentFirstIndex].cat_id;

            this.$store.dispatch('setDrpCategoryLists',{
                id:this.cat_id
            })

            this.handelTouchCatads()
        },
        cat_id(){
            this.handelTouchCatads()
        }
    }
}
</script>
<style scoped>
.cgr .items .item .tit{ margin: 1.1rem 0 2.2rem; }
.cgr ul{ overflow: visible; }
.cgr ul li{ position: relative; }
.cgr ul li .ect-radio{ position: absolute; margin: 0; top: -5px; left: 0; }
</style>