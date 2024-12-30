<template>
    <div class="con con_main" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <div class="wholesale-list">
            <div class="article-nav dis-box">
                <swiper class="article-nav-items box-flex" :options="swiperOption">
                    <swiper-slide class="article-nav-item">
                        <a href="javascript:void(0)" @click="handleCate(0)" :class="{'active':active == 0}">{{$t('lang.all')}}</a>
                    </swiper-slide>
                    <swiper-slide class="article-nav-item" v-for="(item, index) in supplierCategory" :key="index">
                        <a href="javascript:void(0)" @click="handleCate(item.cat_id)" :class="{'active':active == item.cat_id}">{{ item.cat_name }}</a>
                    </swiper-slide>
                </swiper>
            </div>
            
            <div class="goods-li of-hidden" v-if="supplierGoodsList && supplierGoodsList.length > 0">
                <router-link :to="{name:'supplier-detail',params:{id:item.goods_id}}" class="li active" v-for='(item,index) in supplierGoodsList' :key="index" @click="detailClick(item)">
                    <div class="left">
                        <img class="img" :src="item.goods_thumb" />
                    </div>
                    <div class="right bg-color-write">
                        <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
                        <div class="box-flex f-06 color-red m-top08"> <em v-html="(item.price_model===0?item.goods_price:item.shop_price)"></em>
                            <span class="f-01 color-9">/{{item.goods_unit}}</span>
                        </div>
                        <div class="dis-box m-top06">
                            <div class="box-flex f-02 color-9">{{$t('lang.label_volume_number')}}
                                <span class="color-red" v-if="item.price_model===1">{{item.volume_number}}</span>
                                <span class="color-red" v-if="item.price_model===0">{{item.moq}}</span>
                            </div>
                            <div class="box-flex f-02 color-9 text-right">{{$t('lang.label_trading_volume')}}
                                <span>{{item.goods_sale}}</span>
                            </div>
                        </div>
                    </div>
                </router-link>
            </div>
            <div v-else>
                <NotCont/>
            </div>
            <template v-if="loading">
                <van-loading type="spinner" color="black" />
            </template>
        </div>
        <!--底部导航-->
        <WhoTabbar/>
        <CommonNav :routerName="routerName"></CommonNav>
    </div>
</template>
<script>
    import { mapState } from 'vuex'

    import {
        Tab,
        Tabs,
        Waterfall,
        Loading
    } from 'vant'

    import {
        swiper,
        swiperSlide
    } from "vue-awesome-swiper"

    import NotCont from '@/components/NotCont'
    import CommonNav from '@/components/CommonNav'
    import arrRemove from '@/mixins/arr-remove'
    import WhoTabbar from './WhoTabbar'
    
    export default {
        name: "wholesale-list",
        components: {
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Loading.name]: Loading,
            CommonNav,
            NotCont,
            WhoTabbar,
            swiper,
            swiperSlide,
        },
        directives: {
            WaterfallLower: Waterfall('lower')
        },
        data() {
            return {
                active: 0,
                cat_id:this.$route.query.cat_id ? this.$route.query.cat_id : 0,
                page:1,
                size:10,
                loading:true,
                disabled:false,
                swiperOption:{
                    notNextTick: true,
                    watchSlidesProgress: true,
                    watchSlidesVisibility: true,
                    slidesPerView: 'auto',
                    lazyLoading: true,
                },
                routerName:'supplier'
            }
        },
		created() {
            this.active = this.cat_id > 0 ? this.cat_id : -1
            this.$store.dispatch('setSupplierCategory');
            this.goodsList();
		},
		computed: {
			...mapState({
                supplierCategory: state => state.other.supplierData.category,
			}),
            supplierGoodsList:{
                get(){
                    return this.$store.state.other.supplierGoodsList
                },
                set(val){
                    this.$store.state.other.supplierGoodsList = val
                }
            }
		},
        methods: {
            goodsList(page){
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

				this.$store.dispatch('setSupplierGoodsList',{
                    page: this.page,
                    size: this.size,
                    cat_id: this.cat_id,
                })
            },
            handleCate(cat_id){
                this.active = cat_id;
                this.cat_id = cat_id;
                this.goodsList(1);
            },
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.supplierGoodsList.length){
                        this.page ++
                        this.goodsList() 
                    }
                },200)
            }
        },
        watch:{
            supplierGoodsList(){
                if(this.page * this.size == this.supplierGoodsList.length){
                    this.disabled = false
                    this.loading = true
                }else{
                    this.loading = false
                }

                this.supplierGoodsList = arrRemove.trimSpace(this.supplierGoodsList)
            },
        }
    };
</script>
<style scoped>
.wholesale-list .goods-li{ padding-top: 3.5rem; }
</style>