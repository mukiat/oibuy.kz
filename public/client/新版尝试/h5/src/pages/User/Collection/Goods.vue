<template>
  <div class="collection" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    <template v-if="goodsCollectList != ''">
      <div class="product-list">
        <ul>
          <li v-for="(item,index) in goodsCollectList" :key="index" :class="[item.on_sale ? '' : 'out-stock']">
            <div class="product-div">
              <div class="product-list-img">
                <router-link :to="{ name: 'goods', params: { id: item.goods_id }}">
                  <img class="img" :src="item.goods_thumb" v-if="item.goods_thumb">
                  <img class="img" src="../../../assets/img/no_image.jpg" v-else>
                  <p class="image_tag" v-show="!item.on_sale">{{$t('lang.sold_out')}}</p>
                </router-link>
              </div>
              <div class="product-info">
                <h4><router-link :to="{ name: 'goods', params: { id: item.goods_id }}">{{ item.goods_name }}</router-link></h4>
                <div class="price"><em v-html="item.shop_price"></em></div>
                <div class="non-price">{{$t('lang.no_quotation')}}</div>
                <em class="a-accessories-clear" @click="collectHandle(item.goods_id)"><i class="iconfont icon-delete fr"></i></em>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </template>
    <template v-else>
      <NotCont />
    </template>
    <CommonNav></CommonNav>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import {
    Waterfall,
} from 'vant'

import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
		return {
      disabled:false,
      loading:true,
      page:1,
      size:10,
		}
	},
  directives: {
    WaterfallLower: Waterfall('lower')
  },
  components:{
    NotCont,
    CommonNav,
    Waterfall,
  },
  created(){
    this.setGoodsList()
  },
  computed:{
    goodsCollectList:{
      get(){
          return this.$store.state.user.goodsCollectList
      },
      set(val){
          this.$store.state.user.goodsCollectList = val
      }
    }
  },
  methods:{
    setGoodsList(page){
        if(page){
            this.page = page
            this.size = Number(page) * 10
        }
        this.$store.dispatch('setCollectGoodsList',{
          page:this.page,
          size:this.size,
        })
    },
    collectHandle(val){
      this.$store.dispatch('setCollectGoods',{
        goods_id:val,
        status:1
      })
    },
    loadMore(){
      setTimeout(() => {
        this.disabled = true
        if(this.page * this.size == this.goodsCollectList.length){
          this.page ++
          this.setGoodsList() 
        }
      },200);
    },
  },
  watch:{
    goodsCollectList(){
      if(this.page * this.size == this.goodsCollectList.length){
          this.disabled = false
          this.loading = true
      }else{
          this.loading = false
      }
      this.goodsCollectList = arrRemove.trimSpace(this.goodsCollectList)
    }
  }
}
</script>