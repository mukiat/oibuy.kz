<template>
  <div class="shop_container">
    <div class="shopping_menu flex_box">
      <div class="shopping_menu_left flex_box jc_sa ai_center">
        <div class="item" :class="{'active':sort == 'sort'}" @click="filterHandle('hot')">
          <span class="size_15 weight_700">{{$t('lang.hot_alt')}}</span>
          <i class="iconfont color_ccc" :class="[order == 'DESC' && sort == 'sort' ? 'icon-topjiantou' : 'icon-xiajiantou']"></i>
        </div>
        <div class="item" :class="{'active':regionOptionDate.city.id > 0}" @click="filterHandle('region')">
          <span class="size_15 weight_700">{{$t('lang.region')}}</span>
          <i class="iconfont color_ccc icon-xiajiantou"></i>
        </div>
        <div class="item" :class="{'active':sort == 'distance'}" @click="filterHandle('distance')" v-if="isWeixinBrowser">
          <span class="size_15 weight_700">{{$t('lang.distance')}}</span>
          <i class="iconfont color_ccc" :class="[order == 'DESC' && sort == 'distance' ? 'icon-topjiantou' : 'icon-xiajiantou']"></i>
        </div>
      </div>
      <div class="shopping_menu_right flex_box ai_center">
        <div class="search_container flex_box ai_center">
          <input class="size_15" type="text" v-model="keyword" :placeholder="$t('lang.search_the_store')">
          <div @click="searchTheStore"><i class="iconfont icon-home-search size_13"></i></div>
        </div>
      </div>
    </div>
    <div class="dsc_shop_nav" v-if="shopCatList.length > 0">
      <swiper class="scroll_view" :options="swiperOption" ref="mySwiper">
        <swiper-slide class="scroll_view_item">
          <a href="javascript:;" @click="shopCatClick(0)" :class="{'active':cat_id == 0}">
            <div class="icon">
              <i class="iconfont icon-fenlei"></i>
            </div>
            <h5 class="name text_1 size_12">{{$t('lang.all_categories')}}</h5>
          </a>
        </swiper-slide>
        <swiper-slide class="scroll_view_item" v-for="(item, index) in shopCatList" :key="index">
          <a
            href="javascript:void(0)"
            @click="shopCatClick(item.cat_id)"
            :class="{'active':cat_id == item.cat_id}"
          >
            <div class="icon">
              <img :src="item.touch_icon" class="img" v-if="item.cat_icon">
              <img src="../../assets/img/no_image.jpg" alt="" class="img" v-else>
            </div>
            <h5 class="name text_1 size_12">{{ item.cat_alias_name }}</h5>
          </a>
        </swiper-slide>
      </swiper>
    </div>
    <div
      class="store_info"
      v-waterfall-lower="loadMores"
      waterfall-disabled="disabled"
      waterfall-offset="400"
    >
      <div class="store_list" v-for="(item,index) in shopList" :key="index">
        <header class="header flex_box ai_center jc_sb">
          <div class="header_left flex_box ai_center" @click="shopDetail(item.user_id)">
            <div class="img_box">
              <img :src="item.logo_thumb">
            </div>
            <div class="shop_name_box">
              <h3 class="size_15 color_000 weight_700 text_1">{{ item.rz_shop_name }}</h3>
              <p class="size_12 color_666">
                {{$t('lang.collection_one')}}
                <span>{{ item.count_gaze }}</span>
                {{$t('lang.collection_two')}}
              </p>
              <p class="size_12 color_666" style="margin-top: 2px" v-if="isWeixinBrowser">{{$t('lang.distance')}}<span class="color_FD001C"> {{ item.distance.value }}{{ item.distance.unit }} </span></p>
            </div>
          </div>
          <div class="header_right">
            <div class="shop_in size_12 color_fff" @click="shopDetail(item.user_id)">{{$t('lang.into_shop')}}</div>
            <div
              @click="collectHandle(item.user_id,item.is_collect_shop)"
              :class="[item.is_collect_shop == 1 ? 'shop_focus_active' : 'shop_focus', 'size_12']"
            >
              <i class="iconfont icon-shop-guanzhu size_12" v-if="item.is_collect_shop !== 1"></i>{{item.is_collect_shop == 1 ? $t('lang.followed') : $t('lang.attention')}}
            </div>
          </div>
        </header>
        <template v-if="item.goods.length > 0">
          <section class="shop_pic">
            <swiper :options="swiperOption">
              <swiper-slide
                class="product_list_item"
                v-for="(goods,goodsIndex) in item.goods"
                :key="goodsIndex"
              >
                <router-link :to="{ name: 'goods', params: { id: goods.goods_id }}">
                  <div class="goods_img_box">
                    <img class="product-list-img" :src="goods.goods_thumb" v-if="goods.goods_thumb">
                    <img src="../../assets/img/no_image.jpg" alt="" class="product-list-img" v-else>
                  </div>
                  <div class="goods_name_price">
                    <p class="size_14 color_333 text_1">{{ goods.goods_name }}</p>
                    <p class="size_14 color_FD001C text_1 weight_700" v-html="goods.shop_price"></p>
                  </div>
                </router-link>
              </swiper-slide>
            </swiper>
          </section>
        </template>
      </div>
      <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
      
    </div>
    <template v-if="loading">
      <van-loading type="spinner" color="black"/>
    </template>
    <Region :display="regionShow" :isLevel="4" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>
  </div>
</template>

<script>
import { mapState } from "vuex";

import { List, Dialog, Waterfall, Loading, Toast } from "vant";

import { swiper, swiperSlide } from "vue-awesome-swiper";

import ShopHeader from "@/components/shop/ShopHeader";
import arrRemove from "@/mixins/arr-remove";
import isApp from '@/mixins/is-app'
import formProcessing from '@/mixins/form-processing'

export default {
  mixins:[isApp,formProcessing],
  data() {
    return {
      swiperOption: {
        notNextTick: true,
        watchSlidesProgress: true,
        watchSlidesVisibility: true,
        slidesPerView: "auto",
        lazyLoading: true,
      },
      disabled: false,
      loading: true,
      shopScore: false,
      size: 10,
      page: 1,
      cat_id: 0,
      sort: "sort_order",
      order: "ASC",
      lat: "",
      lng: "",
      footerCont: false,
      keyword: ''
    };
  },
  components: {
    swiper,
    swiperSlide,
    ShopHeader,
    [Dialog.name]: Dialog,
    [List.name]: List,
    [Loading.name]: Loading,
    [Toast.name]: Toast,
  },
  directives: {
    WaterfallLower: Waterfall("lower"),
    WaterfallUpper: Waterfall("upper"),
  },
  created() {
    let that = this;

    that.$store.dispatch("setShopCatList");

    if(this.isWeixinBrowser){
      wx.getLocation({
        type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
        success: function (res) {
          that.lat = res.latitude; // 纬度，浮点数，范围为90 ~ -90
          that.lng = res.longitude; // 经度，浮点数，范围为180 ~ -180。
          that.shopListLoad();
        }
      })
    }else{
      that.shopListLoad();
    }

    //navigator.geolocation.getCurrentPosition(that.showPosition, that.showErr);
  },
  computed: {
    ...mapState({
      shopCatList: (state) => state.shop.shopCatList,
    }),
    shopList: {
      get() {
        return this.$store.state.shop.shopList;
      },
      set(val) {
        this.$store.state.shop.shopList = val;
      },
    },
    isLogin() {
      return localStorage.getItem("token") == null ? false : true;
    },
    shopCollectStatue() {
      return this.$store.state.user.shopCollectStatue;
    },
    isWeixinBrowser(){
      return isApp.isWeixinBrowser()
    },
  },
  methods: {
    shopListLoad(page, keyword = '') {
      if (page) {
        this.page = page;
        this.size = Number(page) * 10;
      }

      this.$store.dispatch("setShopList", {
        cat_id: this.cat_id,
        warehouse_id: 0,
        area_id: 0,
        size: this.size,
        page: this.page,
        sort: this.sort,
        order: this.order,
        lat: this.lat,
        lng: this.lng,
        city_id: this.regionOptionDate.city.id,
        keywords: keyword
      });
    },
    searchTheStore() {
      if (this.keyword.trim().length > 0) {
        this.shopListLoad(1, this.keyword);
      } else {
        Toast(this.$t('lang.shop_name_not_null'))
      }
    },
    shopCatClick(val) {
      this.cat_id = val;
      this.shopListLoad();
    },
    filterHandle(val) {
      if (val == "hot") {
        this.order = this.order == "ASC" ? "DESC" : "ASC";
        this.sort = "sort";
        this.shopListLoad();
      } else if (val == "region") {
        this.handelRegionShow();
        this.order = "ASC";
        this.sort = "";
      } else if (val == "distance") {
        this.order = this.order == "ASC" ? "DESC" : "ASC";
        this.sort = "distance";
        this.shopListLoad();
      } else {
        this.$store
          .dispatch("setShopMap", {
            lat: this.lat,
            lng: this.lng,
          })
          .then((res) => {
            if (res.status == "success") {
              window.location.href = res.data;
            } else {
              Toast(this.$t("lang.locate_failure"));
            }
          });
      }
    },
    handelRegionShow() {
      this.regionShow = this.regionShow ? false : true;
    },
    loadMores() {
      setTimeout(() => {
        this.disabled = true;
        if (this.page * this.size == this.shopList.length) {
          this.page++;
          this.shopListLoad();
        }
      }, 200);
    },
    shopDetail(val) {
      this.$router.push({
        name: "shopHome",
        params: {
          id: val,
        },
      });
    },
    collectHandle(val, status) {
      if (this.isLogin) {
        this.$store.dispatch("setCollectShop", {
          ru_id: val,
          status: status,
        });
      } else {
        let msg = this.$t("lang.fill_in_user_collect_goods");
        this.notLogin(msg);
      }
    },
    notLogin(msg) {
      Dialog.confirm({
        message: msg,
        className: "text-center",
      })
        .then(() => {
          this.$router.push({
            path: "/login",
            query: { redirect: { name: "integration", query: { type: 1 } } },
          });
        })
        .catch(() => {});
    },
    showPosition(postion) {
      let that = this;
      that.lat = postion.coords.latitude;
      that.lng = postion.coords.longitude;

      this.shopListLoad();
    },
    showErr(err) {
      let that = this;
      that.lat = 31.23037;
      that.lng = 121.4737;

      this.shopListLoad();
    },
    shippingFee(val) {
      this.$store.dispatch("setShippingFee", {
        goods_id: 0,
        position: val,
      });
    },
  },
  watch: {
    shopList() {
      if (this.page * this.size == this.shopList.length) {
        this.disabled = false;
        this.loading = true;
      } else {
        this.loading = false;
        this.footerCont = this.page > 1 ? true : false;
      }
      this.shopList = arrRemove.trimSpace(this.shopList);
    },
    shopCollectStatue() {
      this.shopCollectStatue.forEach((v) => {
        this.shopList.forEach((res) => {
          if (res.user_id == v.id) {
            res.is_collect_shop = v.status;
            res.count_gaze =
              v.status == 1 ? res.count_gaze + 1 : res.count_gaze - 1;
          }
        });
      });
    },
    regionShow() {
      if (!this.regionShow) {
        this.shopListLoad();
      }
    },
    regionSplic() {
      let shipping_region = {
        province_id: this.regionOptionDate.province.id,
        city_id: this.regionOptionDate.city.id,
        district_id: this.regionOptionDate.district.id,
        street_id: this.regionOptionDate.street.id,
      };

      //运费
      this.shippingFee(shipping_region);
    },
    order(){
      console.log(this.order)
    }
  },
};
</script>

<style scoped>
.shop_container {
  /* padding-bottom: 5rem; */
}

.shopping_menu {
  margin-top: 1px;
  background: #ffffff;
}

.shopping_menu_left {
  width: 60%;
  height: 5.9rem;
}
.shopping_menu_left .item{ 
  position: relative;
  padding-right: 18px;
}
.shopping_menu_left .iconfont {
  font-size: 1.2rem;
  position: absolute;
  right: 0;
  top: 3px;
}

.shopping_menu_left .item.active,
.shopping_menu_left .item.active .iconfont{
  color: #f92028;
}

.shopping_menu_right {
  width: 40%;
  height: 5.9rem;
  padding: 0 1.5rem 0 0;
  box-sizing: border-box;
}

.search_container {
  height: 3rem;
  width: 100%;
  padding-left: 1.2rem;
  padding-right: .5rem;
  border-radius: 1.5rem;
  border: 1px solid rgba(238, 238, 238, 1);
}

.search_container input {
  width: 60%;
  flex: auto;
}

.search_container input::-webkit-input-placeholder { 
/* WebKit browsers */ 
color: #ccc; 
} 
.search_container input:-moz-placeholder { 
/* Mozilla Firefox 4 to 18 */ 
color: #ccc; 
} 
.search_container input::-moz-placeholder { 
/* Mozilla Firefox 19+ */ 
color: #ccc; 
} 
.search_container input:-ms-input-placeholder { 
/* Internet Explorer 10+ */ 
color: #ccc; 
}

.search_container div {
  height: 100%;
  flex: auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.dsc_shop_nav {
  margin: 1rem 0 1rem 0.8rem;
}

.scroll_view {
  white-space: nowrap;
  width: 100%;
  display: flex;
  flex-direction: row;
}

.scroll_view_item {
  width: 20vw;
  height: 20vw;
  background: rgba(255, 255, 255, 1);
  box-shadow: 0px 0.3rem 1rem 0px rgba(108, 108, 108, 0.15);
  border-radius: 0.3rem;
  text-align: center;
  overflow: hidden;
  vertical-align: top;
  margin-right: 0.5rem;
}

.scroll_view_item a {
  display: inline-block;
  width: 100%;
  height: 100%;
}

.scroll_view_item .icon {
  width: 10.66vw;
  height: 10.66vw;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 1.6vw auto;
}

.scroll_view_item .icon .img {
  width: 100%;
  height: 100%;
}

.scroll_view_item .icon .iconfont {
  width: 4rem;
  height: 4rem;
  line-height: 4rem;
  font-size: 2.5rem;
  color: #ccc;
}
.scroll_view_item .name {
  text-align: center;
}
.scroll_view_item .active .name {
  color: #f92028;
}

.store_info {
  position: relative;
  padding: 0 0.8rem;
}

.store_info .store_list {
  margin-bottom: 1rem;
  border-radius: 1rem;
  background: #ffffff;
  overflow: hidden;
}

.store_info .store_list .header {
  padding: 1.5rem 1.1rem;
}

.img_box {
  width: 3.4rem;
  height: 3.4rem;
  background: rgba(255, 255, 255, 1);
  border: 1px solid rgba(238, 238, 238, 1);
  border-radius: 50%;
  margin-right: 0.6rem;
}
.img_box img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.header_right {
  display: flex;
  height: 6.66vw;
  line-height: 6.66vw;
}
.header_right div {
  width: 15.2vw;
  height: 100%;
  text-align: center;
  border-radius: 3.33vw;
}
.shop_in {
  background: linear-gradient(
    90deg,
    rgba(250, 41, 41, 1),
    rgba(255, 88, 61, 1)
  );
}
.shop_focus_active {
  margin-left: 0.5rem;
  color: #fff;
  background: rgba(187, 187, 187, 1);
}
.shop_focus {
  box-sizing: border-box;
  color: #fa2929;
  line-height: 6.1vw;
  border: 1px solid #fa2929;
  margin-left: 0.5rem;
  background-color: #fff;
}
.header_right .iconfont {
  /* margin-right: 0.3rem; */
}

.shop_pic {
  padding: 0 0 1.8rem 1rem;
}
.product_list_item {
  width: 26.93vw;
  margin-right: 1rem;
  box-sizing: border-box;
}
.goods_img_box {
  height: 26.93vw;
  border: 0.1rem solid #eeeeee;
  border-radius: 1rem;
  overflow: hidden;
}
.goods_img_box img {
  width: 100%;
  height: 100%;
}
.goods_name_price {
  text-align: center;
  line-height: 1;
  padding: 0 .8rem;
}
.goods_name_price p:first-child {
  margin: 2.6vw 0 2vw;
}

.distance_active .shopping_menu_left .item.active .icon-xiajiantou::before,
.position_active .shopping_menu_left .item.active .icon-xiajiantou::before{
  transform: rotate(180deg);
}
</style>

