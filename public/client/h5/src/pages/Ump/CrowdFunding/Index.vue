<template>
    <div class="cro_funing con_main" >
        <div class="search-nav">
            <div class="search dis-box">
              <div class="f-04 box-flex">
                <input class="f-03" type="text" v-model="value" :placeholder="$t('lang.goods_search')" autocomplete="off">
              </div>
              <div class="icon-search-box" @click="onSearch"><i class="iconfont icon-sousuo1 f-05 color-7"/></div>
            </div>
        </div>
        <!--分类-->
        <div class="dis-box tab-common nav f-05 bg-color-write text-center">
            <div class="box-flex">
                <div class="item" :class="{'active':filter === 'sort'}" @click="handleFilter('sort')">
                    <span>{{$t('lang.comprehensive_rank')}}</span>
                    <i class="iconfont icon-xiajiantou"></i>
                </div>
            </div>
            <div class="box-flex">
                <div class="item" :class="{'active':filter === 'all'}" @click="handleFilter('all')">
                    <span>{{$t('lang.all_goods')}}</span>
                    <i class="iconfont icon-xiajiantou"></i>
                </div>
            </div>
            <div class="item item-icon" @click="listModeClick">
                <template v-if="listMode === 'active'">
                    <i class="iconfont icon-pailie"></i>
                </template>
                <template v-else>
                    <i class="iconfont icon-viewlist"></i>
                </template>
            </div>
        </div>
        <!--导航-->
        <div class="tabCon  bg-color-write" :class="{'active':tabMode === 'active'}">
            <div class="cont-max-height" v-show="filter == 'sort'">
               <van-radio-group v-model="radio" class="padding-all bg-color-write">
                      <van-radio v-for="(item,index) in filterSort" :key="index" :name ='1' :class="{'active':filter_sort === item.cat_id}">
                            <span @click="sortingClick(item)">{{item.cat_name}}</span>
                      </van-radio>
                </van-radio-group>
            </div>
            <div class="cont-max-height" v-show="filter == 'all'">
                <van-radio-group v-model="radio" class="padding-all bg-color-write">
                      <van-radio :name ='0' :class="{'active':cur_id == 0}" >
                            <span @click="navCheck(0)"> {{$t('lang.all')}}</span>
                      </van-radio>
                      <van-radio :name ='item.cat_id' :class="{'active':cur_id == item.cat_id}"  v-for="(item,index) in crowdIndexData" :key="index">
                            <span @click="navCheck(item)"> {{item.cat_name}}</span>
                      </van-radio>
                </van-radio-group>
            </div>
            <div class="foorer-nav" @click="bgClick">
                <i class="iconfont icon-Collapse j-text-all"></i>
            </div>
        </div>
        <!--商品列表-->
        <div class="goods-li" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300" v-if="crowdGoodsData!=''">
            <router-link :to="{name:'crowdfunding-detail', params:{id:item.id}}" class="show bg-color-write li" :class="{'active':listMode === 'active'}" v-for='(item,index) in crowdGoodsData' :key="index">
                <div class="left p-r">
                    <img class="img" :src="item.title_img" v-if="item.title_img" />
                    <img class="img" src="../../../assets/img/not_goods.png" v-else />
                    <div class="raise-cate-tag"><i class="iconfont icon-gerenzhongxin icon-geren"></i>{{item.join_num}}</div>
                </div>
                <div class="right">
                    <h4 class="f-05 color-3">{{item.title}}</h4>
                    <div class="goods-num">
                        <div class="list f-02 color-9">{{$t('lang.support_number')}}
                            <span class="color-red">{{item.join_num}}</span>{{$t('lang.ren')}}
                        </div>
                        <div class="list f-02 color-9">{{$t('lang.time_remaining')}}{{item.shenyu_time}}{{$t('lang.tian')}}</div>
                    </div>
                    <div class="ect-progress dis-box">
                        <p class="wrap box-flex">
                            <span class="bar" :style="{width:item.baifen_bi + '%'}">
                                <i class="color"></i>
                            </span>
                        </p>
                        <p class="txt f-02">{{item.baifen_bi}}%</p>
                    </div>
                    <div class="goods-cont">
                        <div class="list f-02 color-9">{{$t('lang.label_has_crowdfunding')}}
                            <span class="color-red">{{item.join_money}}</span>
                        </div>
                        <div class="list f-02 color-9">{{$t('lang.label_target')}}
                            <span class="color-red">{{item.amount}}</span>
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
        <CommonNav :routerName="routerName">
          <li slot="aloneNav">
    				  <router-link :to="{name: 'crowdfunding'}">
    						<i class="iconfont icon-shequ2"></i>
    						<p>{{$t('lang.square')}}</p>
    					</router-link>
    			</li>
  				<li slot="aloneNav">
  					<router-link :to="{name: 'crowdfunding-user'}">
  						<i class="iconfont icon-gerenzhongxin"></i>
  						<p>{{$t('lang.centre')}}</p>
  					</router-link>
  				</li>
  	    </CommonNav>
        <!--遮罩层-->
        <div class="bg-cont" :class="{'active':tabMode === 'active'}" @click="bgClick"></div>
        <CroTabbar/>
    </div>
</template>

<script>
import { mapState } from 'vuex'

import { 
  Search, 
  Row, 
  Col, 
  RadioGroup, 
  Radio,
  Loading,
  Toast,
  Waterfall
} from 'vant'

import CommonNav from '@/components/CommonNav'    
import CroTabbar from './Detail/CroTabbar'
import NotCont from '@/components/NotCont'
import arrRemove from '@/mixins/arr-remove'

export default {
  name: "auction",
  data() {
    return {
      routerName:'crowd_funding',
      filter_sort:'id',
      filterSort:[
        {
          cat_id:'id',
          cat_name:this.$t('lang.comprehensive_rank')
        },
        {
          cat_id:'new',
          cat_name:this.$t('lang.crowdfunding_new')
        },
        {
          cat_id:'amount',
          cat_name:this.$t('lang.crowdfunding_amount')
        },
        {
          cat_id:'join_num',
          cat_name:this.$t('lang.crowdfunding_join_num')
        }
      ],
      cur_id:0,
      radio: '1',
      listMode: "active", //模式列表模式
      tabMode: "",
      filter: "", //默认选中值
      value: "", //搜索默认值
      disabled:false,
      loading:true,
      size:10,
      page:1,
    };
  },
  components: {
    CroTabbar,
    CommonNav,
    NotCont,
    [Search.name]: Search,
    [Row.name]: Row,
    [Col.name]: Col,
    [RadioGroup.name]: RadioGroup,
    [Radio.name]: Radio,
    [Loading.name]: Loading,
    [Toast.name]: Toast,
  },
  directives: {
		WaterfallLower: Waterfall('lower')
	},
  //初始化加载数据
  created() {
   this.$store.dispatch('setCrowdfunding')
   this.crowdGoods(this.filter_sort,this.cur_id,this.value)
  },
  computed: {
    ...mapState({
      crowdIndexData: state => state.crowdfunding.crowdIndexData
    }),
    crowdGoodsData:{
      get(){
        return this.$store.state.crowdfunding.crowdGoodsData
      },
      set(val){
        this.$store.state.crowdfunding.crowdGoodsData = val
      }
    }
  },
  methods: {
    //综合排序
    navCheck(item){
      this.page=1  
      if(item === 0){
        this.cur_id = item
      }else{
        this.cur_id = item.cat_id
      }
      this.crowdGoods(this.filter_sort,this.cur_id,this.value)
      this.tabMode = this.tabMode === "active" ? "" : "active"
    },
    //全部商品
    sortingClick(item){
      this.page = 1   
      this.filter_sort = item.cat_id
      this.crowdGoods(this.filter_sort,this.cur_id,this.value)
      this.tabMode = this.tabMode === "active" ? "" : "active"
    },
    onSearch() {
      let value = this.value;
      this.crowdGoods(this.filter_sort,this.cur_id,this.value)
    },
    crowdGoods(status,cat_id,value) {
      let crowdGoods = {
        status: status,
        cat_id:cat_id,
        keyword: value,
        page:this.page,
        size:this.size
      }
      this.$store.dispatch('setCrowdfundingGoods',crowdGoods)
    },
    loadMore(){
        setTimeout(() => {
            this.disabled = true
            if(this.page * this.size == this.crowdGoodsData.length){
                this.page ++
                this.crowdGoods(this.filter_sort,this.cur_id,this.value)
            }
        },200)
    },
    //菜单模式切换
    handleFilter(val) {
      this.filter = val
      this.tabMode = this.tabMode === "" ? "active" : "active"
    },
    //商品列表模式
    listModeClick() {
      this.listMode = this.listMode === "active" ? "" : "active"
      this.tabMode = this.tabMode === "active" ? "" : ""
    },
    //菜单显示影藏
    bgClick() {
      this.tabMode = this.tabMode === "active" ? "" : "active"
    },
  },
  watch:{
    crowdGoodsData(){
			if(this.page * this.size == this.crowdGoodsData.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
			}

      this.crowdGoodsData = arrRemove.trimSpace(this.crowdGoodsData)
		}
	}
}
</script>
