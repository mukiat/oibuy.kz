<template>
    <section>
        <ul class="product-list" :class="aClass" v-if="data.isStyleSel == '0'">
            <li v-for="(item, index) in oProlist" :key="index">
                <router-link :to="{name:'goods',params:{id:item.goods_id}}" v-if="authority == 'view'" class="a"></router-link>
                <div class="product-cart" v-if="false">
                    <i class="iconfont icon-cart"></i>
                </div>
                <figure>
                    <div class="img-box">
                        <img class="img" src="@/assets/img/default-img.jpg" v-if="preview" />
                        <img class="img" v-lazy="data.isSizeSel == '0' ? item.goods_img : item.goods_thumb" v-else/>
                        <div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
                            <img :src="item.goods_label_suspension.formated_label_image" class="img">
                        </div>
                    </div>
                    <figcaption>
                        <h4 class="twolist-hidden">{{item.title || item.goods_name}}</h4>
                        <p class="remark" v-if="bSale || bStock">
                            <em v-show="bStock">{{$t('lang.label_stock')}}{{ item.stock || item.goods_number }}</em>&nbsp;&nbsp;
                            <em v-show="bSale">{{$t('lang.label_sales_volume')}}<span>{{ item.sale }}</span></em>
                        </p>
                        <currency-price :price="item.shop_price" :size="18" style="margin-top: 5px;"></currency-price>
                        <div class="label-list">
                            <div class="label-img" v-for="(label,labelIndex) in item.goods_label" :key="labelIndex">
                                <a :href="label.label_url ? label.label_url : 'javascript:;'"><img :src="label.formated_label_image" /></a>
                            </div>
                        </div>
                    </figcaption>
                </figure>
            </li>
        </ul>
        <scroll-prolist :prolist="oProlist" :scrollNumber="data.allValue.scrollNumber" :bTitle="bTitle" :preview="preview" v-else-if="data.isStyleSel == '1'"></scroll-prolist>
    </section>
</template>
<script>
// mapActions mapState
import { mapActions, mapState } from 'vuex'
// node library
import qs from 'qs'
// custom components
import ScrollProlist from '../element/ScrollProlist'

// third party components
import { swiper, swiperSlide } from 'vue-awesome-swiper'

//getGoodsSel
import dialogGoods from '@/config/api/dialogGoods'

export default {
    name: 'product',
    props: ['data', 'preview', 'shopId'],
    // mixins: [getGoodsSel],
    components: {
        swiper,
        swiperSlide,
        ScrollProlist
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
            prolist: []
        }
    },
    created() {},
    mounted() {
      setTimeout(() => {
        if (this.selectGoodsId.length > 0) {
          dialogGoods.getGoodsSel({
            number: this.selectGoodsId.length,
            selectGoodsId: this.selectGoodsId
          })
          .then(({ data: { data } }) => {
            this.prolist = data
          })
        } else {
          if(this.authority != 'view'){
            this.$http.post(`/${window.apiAuthority}/touch_visual/product`,
              qs.stringify({
                number: this.nNumber < 1 ? 1 : this.nNumber,
                type: this.moduleSel,
                ru_id: this.shopId,
                cat_id: this.catId,
                brand_id: this.brandSelect
              })
            )
            .then(({ data: { data } }) => {
              data && data.length > 0 ? (this.prolist = data) : (this.prolist = [])
            }).catch(err => {
              console.error(err)
            })
          }else{
            this.$http.post(`${window.ROOT_URL}api/visual/product`,
              qs.stringify({
                number: this.nNumber < 1 ? 1 : this.nNumber,
                type: this.moduleSel,
                ru_id: this.shopId,
                cat_id: this.catId,
                brand_id: this.brandSelect
              })
            )
            .then(({ data: { data } }) => {
              data && data.length > 0 ? (this.prolist = data) : (this.prolist = [])
            }).catch(err => {
              console.error(err)
            })
          }
        }
      }, 100)
    },
    methods: {},
    computed: {
        selectGoodsId() {
            return this.data.allValue.selectGoodsId || []
        },
        brandSelect() {
            return this.data.allValue.brandSelect
        },
        catId() {
            let arr = [],
                len = 0
            this.data.allValue.categorySOption
                ? (arr = this.data.allValue.categorySOption.split(','))
                : (arr = [])
            len = arr.length
            return arr[len - 1]
        },
        oProlist() {
            if (this.preview && this.data.isStyleSel == '0') {
                return this.previewProlist
            } else {
                return this.prolist
            }
        },
        bPreview() {
            return this.preview
        },
        bStock() {
            return this.data.tagSelList.indexOf('0') != -1 ? true : false
        },
        bSale() {
            return this.data.tagSelList.indexOf('1') != -1 ? true : false
        },
        bTitle() {
            return this.data.tagSelList.indexOf('2') != -1 ? true : false
        },
        nNumber() {
            return this.data.allValue.number
        },
        moduleSel() {
            let sModulesSel = this.data.isModuleSel
            let sReturn = ''
            switch (sModulesSel) {
                case '0':
                    sReturn = 'best'
                    break
                case '1':
                    sReturn = 'new'
                    break
                case '2':
                    sReturn = 'hot'
                    break
                case '3':
                    sReturn = ''
                    break
                default:
                    sReturn = ''
                    break
            }
            return sReturn
        },
        aClass() {
            let sSizeSel = this.data.isSizeSel,
                arr = []
            if (this.preview) arr.push('preview')
            switch (sSizeSel) {
                case '0':
                    arr.push('big')
                    break
                case '2':
                    arr.push('small')
                    break
                default:
                    break
            }
            return arr
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
.product-list {
    overflow: hidden;
	padding: 2px;
}

.product-list.small.preview h4,
.product-list.big h4 {
    height: inherit;
}

.product-list li {
    width: 48%;
    float: left;
    position: relative;
	margin:1%;
	border-radius: 5px;
}

.product-list li .a {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 10;
}

.product-list li .product-cart {
    position: absolute;
    right: 0.7rem;
    bottom: 0.7rem;
    width: 2.2rem;
    height: 2.2rem;
    line-height: 2.2rem;
    background: #ec5151;
    text-align: center;
    border-radius: 100%;
    color: #fff;
    z-index: 3;
}

.product-list li .product-cart i {
    font-size: 1.5rem;
}

.product-list figure {
    background: #fff;
    padding: 0.8rem;
	border-radius: 5px;
    box-sizing: border-box;
}

.product-list figure img {
    width: 100%;
    height: auto;
}

.product-list figure h4 {
    font-size: 1.4rem;
    height: 3.8rem;
    line-height: 1.9rem;
    font-weight: normal;
    @include ellipses();
}

.product-list figcaption {
    margin-top: 0.8rem;
}

.product-list figure p {
    margin-top: 0.4rem;
}

.product-list figure .price em {
    color: #ec5151;
    font-size: 1.7rem;
    display: block;
    i {
        font-size: 1.3rem;
        font-style: normal;
    }
}

.product-list figure .remark em {
    font-size: 1.3rem;
    color: #888;
}

.product-list.big li,
.product-list.small li {
    width: 100%;
    overflow: hidden;
    padding: 1%;
    margin: 0;
}

.product-list.small li figure {
    width: 100%;
    @include box-pack(center, start);
    @include direction(center, flex-start);
}

.product-list.small li figure figcaption {
    width: 60%;
}

.product-list.small li h4 {
    width: 100%;
    height: inherit;
    white-space: nowrap;
}

.product-list.small li .img-box{
    padding-top: 0;
}

.product-list.small li img {
    width: 9.2rem;
    padding: 0.2rem;
    border: 1px solid #eee;
    margin-right: 0.8rem;
    height: auto;
    position: static;
}

.product-list.small li figcaption {
    margin-top: 0;
    @include flex1-1();
}

/*新版样式*/
.product-new{
    margin: 1rem 1rem 0;
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
    margin-top: .5rem;
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
    padding: 1.5rem;
}
.product-new .goods-list .item .info-box .goods-name{
    font-size: 1.5rem;
    color: #000;
    font-weight: 500;
    min-height: 4.2rem;
}
.product-new .goods-list .item .info-box .goods-price{
    font-size: 1.8rem;
    color: #F20D23;
    font-weight: 600;
    margin-top: .5rem;
}

.product-list .label-list{ overflow: hidden; height: 16px; margin-top: 5px;}
.product-list .label-list .label-img{ height: 16px; margin: 0 .5rem 0 0;}
.product-list .label-list .label-img:last-child{ margin-right: 0; }
.product-list .label-list .label-img img{ width: auto; height: 100%; margin: 0; padding: 0; border: 0;}
</style>
