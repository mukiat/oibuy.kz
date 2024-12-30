<template>
    <div class='visual-adv' :class="[bStyleSel == 0 ? 'visual-adv-swiper' : 'visual-adv-lie']" :style="{'background-color':backgroundColor}">
        <div class="bg-img relative">
            <a v-href="{sUrl:data.allValue.url,preview:preview}" class="absolute-link"></a>
            <img v-lazy="data.allValue.titleImg" class="img" alt="" v-if="data.allValue.titleImg">
            <img src="@/assets/img/default-img.jpg" class="img" alt="" v-else>
        </div>
        <div class="adv-goods-list">
            <template v-if="bStyleSel == 0">
                <swiper class="scroll-prolist" :options="swiperOption" ref="mySwiper">
                    <swiper-slide class="swiper-slide" v-for="(item,index) in previewProlist" :key="index">
                        <div class="goods-top" @click="link(item)">
                            <div class="img-box">
                                <img class="img" src="@/assets/img/default-img.jpg" v-if="preview" />
                                <img class="img" v-lazy="item.goods_thumb" v-else/>
                                <div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
                                    <img :src="item.goods_label_suspension.formated_label_image" class="img">
                                </div>
                            </div>
                        </div>
                        <div class="goods-info" @click="link(item)">
                            <div class="goods-name twolist-hidden">{{item.title || item.goods_name}}</div>
                            <currency-price :price="item.shop_price"></currency-price>
                        </div>
                    </swiper-slide>
                </swiper>
            </template>
            <template v-else>
                <div class="scroll-prolist">
                    <div class="swiper-slide" v-for="(item,index) in previewProlist" :key="index" @click="link(item)">
                        <div class="goods-top">
                            <div class="img-box">
                                <img class="img" src="@/assets/img/default-img.jpg" v-if="preview" />
                                <img class="img" v-lazy="item.goods_thumb" v-else/>

                                <div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
                                    <img :src="item.goods_label_suspension.formated_label_image" class="img">
                                </div>
                            </div>
                        </div>
                        <div class="goods-info">
                            <div class="goods-name twolist-hidden">{{item.title || item.goods_name}}</div>
                            <currency-price :price="item.shop_price"></currency-price>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
// mapActions mapState
import { mapActions, mapState } from 'vuex'

//node library
import qs from 'qs'

// third party components
import Vue from 'vue'
import { swiper, swiperSlide } from 'vue-awesome-swiper'

//mixins
import frontendGet from '@/mixins/frontend-get'

//getGoodsSel
import dialogGoods from '@/config/api/dialogGoods'

export default {
    name: 'visual-adv',
    props: ['data', 'preview', 'modulesIndex','shopId'],
    mixins: [frontendGet],
    components: {
        swiper,
        swiperSlide
    },
    data() {
        return {
            swiperOption: {
                notNextTick: true,
                slidesPerView: 'auto',
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                lazyLoading: true,
            },
            previewProlist: [
                {
                    title: 'Бірінші',
                    sale: '0',
                    stock: '0',
                    price: '23800₸',
                    marketPrice: '41300₸'
                },
                {
                    title: 'Екінші',
                    sale: '0',
                    stock: '0',
                    price: '3800₸',
                    marketPrice: '4300₸'
                },
                {
                    title: 'Үшінші',
                    sale: '0',
                    stock: '0',
                    price: '3800₸',
                    marketPrice: '4300₸'
                }
            ]
        }
    },
    created() {
        
    },
    mounted() {
        if (this.selectGoodsId.length > 0) {
            dialogGoods.getGoodsSel({
                number: this.selectGoodsId.length,
                selectGoodsId: this.selectGoodsId
            }).then(({ data: { data } }) => {
                this.previewProlist = data
            })
        }
    },
    computed: {
        swiper() {
            return this.$refs.mySwiper.swiper
        },
        selectGoodsId() {
            return this.data.allValue.selectGoodsId || []
        },
        bStyleSel(){
            return this.data.isStyleSel
        },
        nNumber() {
            return this.data.allValue.number
        },
        backgroundColor(){
            return this.data.allValue.bgColor
        },
        authority(){
            return window.apiAuthority
        },
    },
    methods:{
        link(item){
            this.$router.push({
                name:'goods',
                params:{
                    id:item.goods_id
                }
            })
        }
    }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/css/common/base.scss';
.visual-adv{
    padding: 1rem 1rem 0 1rem;
    position: relative;
}
.visual-adv .bg-img .img{
    border-radius: 1rem;
}

.adv-goods-list{
    margin-top: -20%;
    padding: 0 0 0 .5rem;
    overflow: hidden;
    position: relative;
    z-index: 12;
}
.adv-goods-list .scroll-prolist{
    overflow: visible;
}
.adv-goods-list .swiper-slide{
    display: inline-block;
    width: 30%;
    margin-right: 1.5%;
    background-color: #fff;
    overflow: hidden;
    border-radius: 1rem;
}
.adv-goods-list .swiper-slide .goods-top{ width: 100%; }
.adv-goods-list .swiper-slide .goods-info{
    padding: 1rem .8rem;
}
.adv-goods-list .swiper-slide .goods-info .goods-name{
    font-size: 1.3rem;
    color: #000;
    min-height: 3.4rem;
    line-height: 1.7rem;
    font-family: $font-family-jd;
}
.adv-goods-list .swiper-slide .goods-info /deep/ .currency-price{
    margin-top: .2rem;
}
.visual-adv-swiper .adv-goods-list .swiper-slide:last-child{
    margin-right: 0;
}

.visual-adv-lie{
    margin:1rem 1rem 0 1rem;
    border-radius: 1rem;
    padding:0 0 2rem;
}
.visual-adv-lie .adv-goods-list{
    padding: 0;
}
.visual-adv-lie .adv-goods-list .swiper-slide{
    margin-bottom: 1.5%;
}
.visual-adv-lie .adv-goods-list .swiper-slide:nth-child(3n){
    margin-right: 0;
}
.visual-adv-lie .adv-goods-list .swiper-slide:nth-child(3n + 1){
    margin-left: 3.5%;
}
</style>