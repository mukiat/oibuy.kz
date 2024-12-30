<template>
  <div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    <div class="header-list-goods">
      <Search :mode="mode" :isFilter='isFilter' v-on:getViewSwitch="handleViewSwitch" :shopId="shopId"></Search>
      <FilterTab :filter="filter" :isPopupVisible="isPopupVisible" v-on:getFilter='handleFilter' @setPopupVisible="setPopupVisible"></FilterTab>
    </div>
    <section class="product-list" :class="{'product-list-medium':mode === 'medium'}">
      <ProductList v-if="shopGoodsList" :data="shopGoodsList" :routerName="routerName"></ProductList>
      <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
      <template v-if="loading">
        <van-loading type="spinner" color="black" />
      </template>
    </section>
    <CommonNav></CommonNav>
    <van-popup class="con-filter-warp" v-model="isPopupVisible" position="right">
      <div class="con-filter-div">
        <swiper :options="swiperOption">
          <swiper-slide>
            <van-cell-group>
              <van-cell :title="$t('lang.brand')" @click="selectBrand" is-link>
                {{ filter.brandResultName }}
              </van-cell>
            </van-cell-group>
            <van-cell-group>
              <van-cell :title="$t('lang.shop_category')" @click="selectShopCat" is-link>
                {{ filter.catResultName }}
              </van-cell>
            </van-cell-group>
          </swiper-slide>
        </swiper>
      </div>
      <div class="filterlayer_bottom_buttons">
        <span class="filterlayer_bottom_button bg_1" @click="closeFilter">{{$t('lang.close')}}</span>
        <span class="filterlayer_bottom_button bg_2" @click="submitFilter">{{$t('lang.confirm')}}</span>
      </div>
    </van-popup>

    <van-popup class="sf_layer" v-model="isPopupBrand" position="right">
      <div class="sf_layer_sub_title">
        <strong>{{$t('lang.label_selected_brand')}}</strong>
        <span v-if="filter.brandResultName.length > 0">{{ filter.brandResultName }}</span>
      </div>
      <div class="sf_layer_con">
        <van-checkbox-group v-model="filter.brand_id" @change="onBrandResult">
          <van-checkbox v-for="(item,index) in filter.brandResult" :name="item.brand_id">{{ item.brand_name }}</van-checkbox>
        </van-checkbox-group>
      </div>
      <div class="filterlayer_bottom_buttons">
        <span class="filterlayer_bottom_button bg_1" @click="cancelSelectBrand">{{$t('lang.cancel')}}</span>
        <span class="filterlayer_bottom_button bg_2" @click="confirmSelectBrand">{{$t('lang.confirm')}}</span>
      </div>
    </van-popup>

    <van-popup class="sf_layer" v-model="isPopupCat" position="right">
      <div class="sf_layer_con sf_layer_con_no">
        <van-radio-group v-model="cat_id" @change="onCatResult">
          <van-radio v-for="(item,index) in filter.catResult" :name="item.cat_id">{{ item.cat_name }}</van-radio>
        </van-radio-group>
      </div>
      <div class="filterlayer_bottom_buttons">
        <span class="filterlayer_bottom_button bg_1" @click="cancelSelectCat">{{$t('lang.cancel')}}</span>
        <span class="filterlayer_bottom_button bg_2" @click="confirmSelectCat">{{$t('lang.confirm')}}</span>
      </div>
    </van-popup>
  </div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'

import {
  List,
  Popup,
  Switch,
  Field,
  Cell,
  CellGroup,
  Checkbox,
  CheckboxGroup,
  Radio,
  RadioGroup,
  Waterfall,
  Loading
} from 'vant'

import {
  swiper,
  swiperSlide
} from 'vue-awesome-swiper'

import Search from '@/components/Search'
import FilterTab from '@/components/filter/FilterTab'
import ProductList from '@/components/ProductList'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

export default{
  data(){
    return{
      disabled:false,
      loading:true,
      mode:'medium',
      filter:{
        sort:'goods_id',
        order:'desc',
        promote:'0',
        brand_id:[],
        brandResult:[],
        brandResultName:'',
        catResult:[],
        catResultName:'',
      },
      isFilter:true,
      isPopupVisible:false,
      isPopupBrand:false,
      isPopupCat:false,
      swiperOption:{
        direction: 'vertical',
        slidesPerView: 'auto',
        freeMode: true
      },
      routerName:'goods',
      cat_id:0,
      page:1,
      size:10,
      footerCont:false,
      shopId:this.$route.query.ru_id ? this.$route.query.ru_id : 0,
    }
  },
  directives: {
    WaterfallLower: Waterfall('lower')
  },
  components:{
    Search,
    FilterTab,
    ProductList,
    swiper,
    swiperSlide,
    CommonNav,
    [List.name] : List,
    [Field.name] : Field,
    [Popup.name] : Popup,
    [Switch.name] : Switch,
    [Cell.name] : Cell,
    [CellGroup.name] : CellGroup,
    [Radio.name] : Radio,
    [RadioGroup.name] : RadioGroup,
    [Checkbox.name] : Checkbox,
    [CheckboxGroup.name] : CheckboxGroup,
    [Loading.name] : Loading
  },
  created(){
    if(this.$route.query.cat_id){
      this.cat_id = this.$route.query.cat_id
    }

    this.getGoodsList()
  },
  computed:{
    shopGoodsList:{
        get(){
            return this.$store.state.shop.shopGoodsList
        },
        set(val){
            this.$store.state.shop.shopGoodsList = val
        }
    }
  },
  methods: {
    getGoodsList(page){
      if(page){
        this.page = page
        this.size = Number(page) * 10
      }

      if(this.filter.promote == 1){
        this.filter.sort = 'promote'
      }

      this.$store.dispatch('setShopGoodsList',{
        keywords:this.$route.query.keywords,
        brand_id:this.filter.brand_id,
        store_id:this.shopId,
        cat_id:this.cat_id,
        warehouse_id:'0',
        area_id:'0',
        size:this.size,
        page:this.page,
        sort:this.filter.sort,
        order:this.filter.order,
        type:this.$route.query.type
      })
    },
    handleViewSwitch(val){
      this.mode = val;
    },
    handleFilter(o){
      this.filter.sort = o.sort;
      this.filter.order = o.order;

      this.getGoodsList(1);
    },
    setPopupVisible(val){
      this.isPopupVisible = val;
    },
    selectBrand(){
      this.isPopupBrand = this.isPopupBrand == false ? true : false;

      this.$http.post(`${window.ROOT_URL}api/catalog/brandlist`,qs.stringify({
        keywords:this.$route.query.keywords,
        cat_id:this.cat_id,
        ru_id:this.$route.query.ru_id
      })).then(({data:{ data }})=>{
        if(data.length > 0){
          this.filter.brandResult = data
        }
      })
    },
    selectShopCat(){
      this.isPopupCat = this.isPopupCat == false ? true : false;
      this.$http.post(`${window.ROOT_URL}api/catalog/shopcat`,qs.stringify({
        ru_id:this.$route.query.ru_id
      })).then(({data:{ data }})=>{
        if(data.length > 0){
          this.filter.catResult = data
        }
      })
    },
    closeFilter(){
      this.isPopupVisible = false
    },
    submitFilter(){
      this.getGoodsList();
      this.isPopupVisible = false
    },
    onBrandResult(){
      let arr = ''
      this.filter.brand_id.forEach((v) =>{
        this.filter.brandResult.forEach((i)=>{
          if(v == i.brand_id){
            arr+=i.brand_name + ','
          }
        })
      })

      this.filter.brandResultName = arr
    },
    cancelSelectBrand(){
      this.filter.brand_id = []
      this.filter.brandResultName = ''
      this.isPopupBrand = false
    },
    confirmSelectBrand(){
      this.isPopupBrand = false
    },
    onCatResult(){
      let str = ''

      this.filter.catResult.forEach((i)=>{
        if(this.cat_id == i.cat_id){
          str = i.cat_name
        }
      })

     this.filter.catResultName = str
    },
    cancelSelectCat(){
      this.isPopupCat = false
    },
    confirmSelectCat(){
      this.isPopupCat = false
    },
    loadMore(){
      setTimeout(() => {
        this.disabled = true
        if(this.page * this.size == this.shopGoodsList.length){
          this.page ++
          this.getGoodsList()
        }
      },200)
    }
  },
  watch:{
    shopGoodsList(){
      if(this.page * this.size == this.shopGoodsList.length){
        this.disabled = false
        this.loading = true
      }else{
        this.loading = false
        this.footerCont = this.page > 1 ? true : false
      }
      this.shopGoodsList = arrRemove.trimSpace(this.shopGoodsList)
    },
    isPopupVisible(){
      if(this.isPopupVisible == false){
        this.filter.goods_num = '0'
        this.filter.promote = '0'
        this.filter.brand_id = []
      }
    }
  }
}
</script>
