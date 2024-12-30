<template>
    <div class="con con_main">
        <div class="wholesale-box">
            <!--轮播图-->
            <div class="banner p-r">
                <van-swipe :autoplay="3000" class="swipe">
                    <van-swipe-item v-for="(item, index) in supplierBanner" :key="index">
                        <a :href="item.link">
                            <img class="img" :src="item.pic" />
                        </a>
                    </van-swipe-item>
                </van-swipe>
                <div class="who-search dis-box p-a">
                    <div class="left-icon" @click="cateClick">
                        <i class="iconfont icon-menu j-wholesale-cate j-show-div"></i>
                    </div>
                    <div class="box-flex">
                        <van-search v-model="value" :placeholder="$t('lang.enter_keyword')" show-action style="background:none">
                            <div slot="action" @click="onSearch">{{$t('lang.search')}}</div>
                        </van-search>
                    </div>
                </div>
            </div>
            <!--限时采购-->
            <template v-if="supplierLimitTime.length > 0">
            <van-cell-group class="m-top08">
                <van-cell class="f-05" :title="$t('lang.time_purchase')" />
            </van-cell-group>
            <div class="padding-all swiper-x-box bg-color-write">
                <swiper class="swiper" :options="swiperOption" ref="mySwiper">
                    <swiper-slide v-for="(item, index) in supplierLimitTime" :key="index">
                        <router-link :to="{name:'supplier-detail',params:{id:item.goods_id}}">
                            <div class="img-box">
                                <img class="img" :src="item.goods_img" />
                            </div>
                            <h4 class="twolist-hidden f-04 color-3 m-top08"> {{ item.goods_name }}</h4>
                            <div class="f-05 color-red m-top06" v-if="item.volume_price"><label v-html="item.volume_price"></label><span class="f-01 color-9">/{{item.goods_unit}}</span></div>
                            <div class="f-05 color-red m-top06" v-else><label v-html="item.goods_price"></label><span class="f-01 color-9">/{{item.goods_unit}}</span></div>
                            <p class="f-02 color-9">{{$t('lang.label_volume_number')}}<span class="color-red">{{item.volume_number}}</span></p>
                            <p class="f-02 color-9">{{$t('lang.time_remaining')}}<span class="color-red">{{item.remaining_time}}{{$t('lang.tian')}}</span></p>
                        </router-link>
                    </swiper-slide>
                </swiper>
            </div>
            </template>
            <section v-for="(item,index) in supplierCatFloor" :key="index" v-if="item.goods != ''">
                <van-cell-group class="m-top08">
                    <van-cell :title="item.cat_name" is-link :value="$t('lang.more')" @click="catelist(item.cat_id)" />
                </van-cell-group>
                <div class="goods-li of-hidden">
                     <router-link :to="{name:'supplier-detail',params:{id:goodsItem.goods_id}}" class="bg-color-write li active" v-for='(goodsItem,goodsIndex) in item.goods' :key="goodsIndex">
                        <div class="left">
                            <img class="img" :src="goodsItem.goods_img" />
                        </div>
                        <div class="right bg-color-write">
                            <h4 class="f-05 color-3 twolist-hidden">{{goodsItem.goods_name}}</h4>
                            <div class="cont">
                                <div class="f-06 color-red box-flex" v-if="goodsItem.volume_price">
                                    <label v-html="goodsItem.volume_price"></label>
                                    <span class="f-01 color-9">/{{goodsItem.goods_unit}}</span>
                                </div>
                                <div class="f-06 color-red box-flex" v-else>
                                    <label v-html="goodsItem.goods_price"></label>
                                    <span class="f-01 color-9">/{{goodsItem.goods_unit}}</span>
                                </div>
                                <div class="f-02 color-9">{{$t('lang.label_volume_number')}}
                                    <span class="color-red" v-if="goodsItem.volume_number">{{goodsItem.volume_number}}</span>
                                    <span class="color-red" v-else>{{goodsItem.moq}}</span>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>
            </section>
            <!--分类列表-->
            <van-popup v-model="cateShow" position="right" class="popup-category">
                <ul>
                    <li class="dis-box f-05" v-for="(item,index) in supplierCategory" :key="index" @click="catelist(item.cat_id)">
                        <div class="icon">
                            <i class="iconfont who-icon" :class="'icon-' + item.style_icon"></i>
                        </div>
                        <div class="box-flex">{{item.name}}</div>
                    </li>
                </ul>
            </van-popup>
            <!--底部导航-->
            <WhoTabbar/>
        </div>
        <CommonNav :routerName="routerName"></CommonNav>

        <DscLoading :dscLoading="supplierLoading">
            <div class="text" slot="text">
                <p>{{$t('lang.supplier_authority_propmt')}}<router-link :to="{name:'home'}" class="color-289">{{$t('lang.home_back')}}</router-link></p>
            </div>
        </DscLoading>
    </div>
</template>
<script>
    import { mapState } from 'vuex'

    import {
        Swipe,
        SwipeItem,
        Search,
        Cell,
        CellGroup,
        Popup,
        Toast
    } from 'vant'

    import {
        swiper,
        swiperSlide
    } from "vue-awesome-swiper"

    import CommonNav from '@/components/CommonNav'
    import WhoTabbar from './Detail/WhoTabbar'
    import NotCont from '@/components/NotCont'
    import DscLoading from '@/components/DscLoading'

    export default {
        name: "wholesale",
        components: {
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [Search.name]: Search,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Popup.name]: Popup,
            [Toast.name]: Toast,
            CommonNav,
            WhoTabbar,
            NotCont,
            swiper,
            swiperSlide,
            DscLoading
        },
        data() {
            return {
                //分类列表弹出层
                cateShow: false,
                //搜索默认值
                value: '',
                //轮播图滑动
                swiperOption: {
                    scrollbarHide: true,
                    slidesPerView: 'auto',
                    centeredSlides: false,
                    grabCursor: true,
                    pagination: '.swiper-pagination',
                    autoplay: 2500,
                },
                routerName:'supplier'
            };
        },
        //初始化加载数据
		created() {
			this.getLoad();
        },
		computed: {
			...mapState({
                supplierLoading: state => state.supplierLoading,
				supplierCategory: state => state.other.supplierData.category,
                supplierLimitTime: state => state.other.supplierData.limitTime,
                supplierCatFloor: state => state.other.supplierData.catFloor,
                supplierBanner: state => state.other.supplierData.banner,
			})
		},
        methods: {
            getLoad(){
                this.$store.dispatch('setSupplierHome');
            },
            onSearch() {
                this.$router.push({
                    name:'supplier-search',
                    query:{
                        keywords:this.value
                    }
                })
            },
            cateClick() {
                this.cateShow = !this.cateShow
            },
            catelist(id){
                this.$router.push({
                    name:'supplier-list',
                    query:{
                        cat_id:id
                    }
                })
            }
        },
        watch:{
        }
    };
</script>
