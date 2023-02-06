<template>
    <section>
        <div class="product-new" ref="productnew" :style="{'min-height':minHeight}">
            <ul class="tabs">
                <li v-for="(item,index) in tabs" :key="index" :class="{'active':filter == index}" @click="tabClick(index)">
                    <strong>{{item.tit}}</strong>
                    <span>{{item.txt}}</span>
                </li>
            </ul>
            <template v-if="filter != 1">
                <template v-if="!dscLoading">
                    <template v-if="prolist.length > 0">
                        <div class="goods-list">
                            <div class="item relative" v-for="(item, index) in prolist" :key="index">
                                <router-link :to="{name:'goods',params:{id:item.goods_id}}" class="absolute-link" v-if="authority == 'view'"></router-link>
                                <div class="img-box">
                                    <img class="img" src="@/assets/img/default-img.jpg" v-if="preview" />
                                    <img class="img" :src="item.goods_thumb" v-else/>

                                    <div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
                                        <img :src="item.goods_label_suspension.formated_label_image" class="img">
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="goods-name twolist-hidden"><img v-if="item.country_icon != ''" class="country_icon" :src="item.country_icon" />{{item.title || item.goods_name}}</div>
                                    <currency-price :price="item.shop_price" :size="18" style="margin-top: 8px;"></currency-price>
                                    <div class="label-list" v-if="item.goods_label.length > 0">
                                        <div class="label-img" v-for="(label,labelIndex) in item.goods_label" :key="labelIndex">
                                            <a :href="label.label_url ? label.label_url : 'javascript:;'"><img :src="label.formated_label_image" /></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
                        <template v-if="loading">
                            <van-loading type="spinner" color="black" />
                        </template>
                    </template>
                    <NotCont v-else></NotCont>
                </template>
                <van-loading type="spinner" color="black" size="3rem" v-else />
            </template>
            <dsc-community routerName="tab" v-else></dsc-community>
        </div>
    </section>
</template>
<script>
// node library
import qs from 'qs'

import {
    mapState
} from 'vuex'

// third party components
import { swiper, swiperSlide } from 'vue-awesome-swiper'

import {
    Loading
} from 'vant'

import community from '@/components/dsc-community/community.vue';
import NotCont from "@/components/NotCont";
import arrRemove from '@/mixins/arr-remove'

export default {
    name: 'product',
    props: ['data', 'preview', 'shopId'],
    // mixins: [getGoodsSel],
    components: {
        swiper,
        swiperSlide,
        NotCont,
        'dsc-community': community,
        [Loading.name]:Loading
    },
    render(createElement, context) {
        return createElement()
    },
    data() {
        return {
            previewProlist: [
                {
                    title: '第一张图片',
                    sale: '0',
                    stock: '0',
                    price: '¥238.00',
                    marketPrice: '¥413.00'
                },
                {
                    title: '第二张图片',
                    sale: '0',
                    stock: '0',
                    price: '¥38.00',
                    marketPrice: '¥43.00'
                }
            ],
            prolist: [],
            filter:0,
            page:1,
            size:10,
            type:'is_best',
            tabs:[
                {
                    tit:this.$t('lang.drp_apply_goods_label'),
                    txt:this.$t('lang.pick_tab_1'),
                },
                {
                    tit:this.$t('lang.community'),
                    txt:this.$t('lang.pick_tab_2'),
                },
                {
                    tit:this.$t('lang.new'),
                    txt:this.$t('lang.pick_tab_3'),
                },
                {
                    tit:this.$t('lang.best_sellers'),
                    txt:this.$t('lang.pick_tab_4'),
                },
            ],
            loading:false,
            footerCont:false,
            dscLoading:false,
            minHeight:''
        }
    },
    created() {},
    mounted() {
        this.getGoodsList(1);
    },
    methods: {
        tabClick(index){
            this.prolist = [];
            this.filter = index;
            this.dscLoading = true;

            this.minHeight = '750px';

            if(index == 1){
                setTimeout(()=>{
                    this.minHeight = '0';
                },3000)
                return
            }else if(index == 0){
                this.type = 'is_best'
            }else if(index == 2){
                this.type = 'is_new'
            }else if(index == 3){
                this.type = 'is_hot'
            }

            this.getGoodsList(1)
        },
        getGoodsList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            this.$http.get(`${window.ROOT_URL}api/goods/type_list`,{ params:{
                page:this.page,
                size:this.size,
                type:this.type
            }}).then(({ data: { data } }) => {
                if(this.page > 0){
                    this.prolist = this.prolist.concat(data);
                }else{
                    this.prolist = data
                }
                this.$store.dispatch('updateScrollPickOpen', {
                    type:false
                })
                this.minHeight = '0';
                this.dscLoading = false
            }).catch(err => {
              console.error(err)
            })
        }
    },
    computed: {
        ...mapState({
            scrollPickOpen: state => state.scrollPickOpen,
            topCategoryCatid: state => state.topCategoryCatid,
        }),
        authority(){
            return window.apiAuthority
        }
    },
    watch:{
        scrollPickOpen(){
            if(this.topCategoryCatid > 0){
                this.$store.dispatch('updateScrollPickOpen', {
                    type:false
                });
                return
            }

            if(this.scrollPickOpen){
                if(this.page * this.size == this.prolist.length){
                    this.page ++
                    this.getGoodsList()
                }
            }
        },
        prolist(){
            if(this.page * this.size == this.prolist.length){
                this.loading = true
            }else{
                this.loading = false
                this.footerCont = this.page > 1 ? true : false
            }

            this.prolist = arrRemove.trimSpace(this.prolist)
        },
    }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
@import '../../../assets/css/common/base.scss';
/*新版样式*/
.product-new{
    margin: 1rem 1rem 0;
    min-height: 250px;
}
.product-new .tabs{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: 1rem 0;
}
.product-new .tabs li{
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
}
.product-new .tabs li strong{
    font-size: 1.6rem;
    color: #333;
}
.product-new .tabs li span{
    display: inline-block;
    font-size: 1.2rem;
    color: #888;
    border-radius: 1rem;
    padding: .1rem .8rem;
    margin-top: .2rem;
}
.product-new .tabs li.active span{
    background:linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1));
    color: #fff;
}
.product-new .tabs li:after{
    content: ' ';
    position: absolute;
    height: 80%;
    width: 1px;
    right: 0;
    top: 10%;
    background-color: #ccc;
}
.product-new .tabs li:last-child:after{
    height: 0;
}
.product-new .goods-list{
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}
.product-new .goods-list .item{
    width: 48.5%;
    background-color: #fff;
    border-radius: 1rem;
    overflow: hidden;
    margin-bottom: 2.5%;
}
.product-new .goods-list .item .info-box{
    padding: 1rem;
}
.product-new .goods-list .item .info-box .goods-name{
    font-size: 1.5rem;
    color: #000;
    font-weight: 500;
    min-height: 4.2rem;
    line-height: 2.1rem;
    font-family: $font-family-jd;
}
.product-new .goods-list .item .info-box .goods-price{
    font-size: 1.8rem;
    color: #F20D23;
    font-weight: 500;
    margin-top: .8rem;
}
.product-new .goods-list .item .info-box .goods-price /deep/ em{
    font-size: 1.4rem;
    margin-right: .1rem;
}
.info-box .label-list{ overflow: hidden; margin-top: .5rem;}
.info-box .label-list .label-img{ height: 16px; margin: 0 .5rem 0 0; float: left;}
.info-box .label-list .label-img:last-child{ margin-right: 0;}
.info-box .label-list .label-img img{ height: 100%; width: auto; }

.country_icon{
	width: 2.4rem;
	padding-right: 0.3rem;
	position: relative;
	top: 0.2rem;
	display: inline-block;
}
</style>
