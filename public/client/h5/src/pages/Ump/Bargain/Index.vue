<template>
    <div class="con ">
        <div class=" con_main bargain">
            <!--轮播图-->
            <van-swipe :autoplay="3000" class="swipe">
                <van-swipe-item v-for="(item, index) in bargainIndexData" :key="index">
                    <a :href="item.link"><img class="img" :src="item.pic" /></a>
                </van-swipe-item>
            </van-swipe>
            <!--商品列表-->
            <div class="goods-li of-hidden" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
                <router-link :to="{name:'bargain-detail',params:{id:item.id}}" class=" li active" v-for='(item,index) in bargainGoodsData' :key="index">
                    <div class="left p-r">
                        <img class="img" :src="item.goods_thumb" />
                        <em class="bargain-tag"><i class="iconfont icon-renshu f-02"></i>{{item.total_num}}{{$t('lang.participation_number')}}</em>
                    </div>
                    <div class="right bg-color-write">
                        <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
                        <div class="f-02 color-9 m-top08">
                            <del>{{$t('lang.original_price')}}<span v-html="item.shop_price"></span></del>
                        </div>
                        <div class="dis-box m-top02">
                            <div class="box-flex f-02 color-9">{{$t('lang.base_price')}}
                                <span class=" f-weight f-06 color-red onelist-hidden" v-html="item.target_price"></span>
                            </div>
                            <div><span class="min-btn tag-gradients-color br-100 color-white f-02">{{$t('lang.up_original_price')}}</span></div>
                        </div>
                    </div>
                </router-link>
            </div>
            <BargainTabbar/>
        </div>
        <CommonNav/>
        <template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template>
    </div>
</template>
<script>
    import { mapState } from 'vuex'

    import {
        Loading,
        Swipe,
        SwipeItem,
        Toast,
        Waterfall
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import BargainTabbar from './Detail/BargainTabbar'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        name: "team",
        components: {
            CommonNav,
            BargainTabbar,
            [Loading.name]: Loading,
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [Toast.name]: Toast,
        },
        directives: {
			WaterfallLower: Waterfall('lower')
		},
        data() {
            return {
                disabled:false,
			    loading:true,
				size:10,
				page:1,
            }
        },
        //初始化加载数据
        created() {
            setTimeout(() => {
                uni.getEnv(function(res){
                    if(res.plus || res.miniprogram){
                        uni.redirectTo({  
                            url:'../../pagesA/bargain/bargain'
                        })
                    }
                })
            },100)
            this.loadingData(this.$store.dispatch('setBargainIndex'))
            //列表
            this.getGoodsList()
        },
        computed: {
            ...mapState({
                bargainIndexData: state => state.bargain.bargainIndexData
            }),
            bargainGoodsData:{
                get(){
                    return this.$store.state.bargain.bargainGoodsData
                },
                set(val){
                    this.$store.state.bargain.bargainGoodsData = val
                }
            }
        },
        mounted() {},
        methods: {
            getGoodsList(page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

                this.$store.dispatch({
                    type: 'setBargainGoods',
                    size: this.size,
                    page: this.page,
                });
            },
            //加载动画
            loadingData(url) {
                let that = this
                Toast.loading({
                    duration: 600,
                    mask: true,
                    message: this.$t('lang.loading')
                }, url)
            },
            loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.bargainGoodsData.length){
						this.page ++
                        this.getGoodsList()
					}
				},200)
			},
        },
        watch:{
			bargainGoodsData(){
				if(this.page * this.size == this.bargainGoodsData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

                this.bargainGoodsData = arrRemove.trimSpace(this.bargainGoodsData)
			}
		}
    };
</script>
