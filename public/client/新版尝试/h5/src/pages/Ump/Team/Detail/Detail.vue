<template>
  <div class="con_main">
    <div class="goods">
      <!--轮播图-->
      <van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
          <van-swipe-item v-for="(item, index) in teamDetailData.goods_img" :key="index" v-if="teamDetailData.goods_img.length>0">
              <img class="imgalt" :src="item" />
          </van-swipe-item>
          <van-swipe-item v-else><img :src="goodsInfo.goods_img" class="imgalt"></van-swipe-item>
      </van-swipe>
      <!--title-->
      <div class="cont-box">
          <div class="title bg-color-write f-06 color-3 padding-all">
              <div class="flex-common ai-start">
                <div class="flex1">
                  <van-tag type="danger" class="m-r05 f-03" v-if="goodsInfo.user_id == 0">{{$t('lang.self_support')}}</van-tag>
                  <span class="f-05 color-3 f-weight">{{goodsInfo.goods_name}}</span>
                </div>
                <div class="drp-share" @click="onGoodsShare">
                  <i class="iconfont icon-share"></i>
                  <span>{{$t('lang.share')}}</span>
                </div>
              </div>
              <div class="color-red f-03 m-top04">{{teamDetailData.title_subtitle}}</div>
              <div class="goods-price f-06 color-red m-top06" v-html="goodsInfo.team_price"></div>
              <div class="f-03 color-9">{{$t('lang.label_shop_price')}}
                  <del v-html="goodsInfo.shop_price"></del>
              </div>
              <div class="dis-box color-9 f-03 m-top06">
                  <div class="box-flex">{{$t('lang.sales_volume')}}{{goodsInfo.sales_volume}}</div>
                  <div class="box-flex">
                      <p class="text-right">{{$t('lang.stock')}}{{goodsInfo.goods_number}} тал</p>
                  </div>
              </div>
          </div>
          <!--服务-->
          <div class="goods_service" @click="goodsService">
            <van-row class=" padding-all bg-color-write f-03">
              <van-col span="6">
                <i class="iconfont icon-iconfontzhizuobiaozhun10 color-red"></i>
                <span>{{$t('lang.goods_service_1')}}</span>
              </van-col>
              <van-col span="6">
                <i class="iconfont icon-wuliu32pt color-red"></i>
                <span>{{$t('lang.goods_service_2')}}</span>
              </van-col>
              <van-col span="6">
                <i class="iconfont icon-02 color-red"></i>
                <span>{{$t('lang.goods_service_3')}}</span>
              </van-col>
              <van-col span="6">
                <p class="text-right right-jiantou">
                  <i class="iconfont icon-more f-06 color-7"></i>
                </p>
              </van-col>
            </van-row>
          </div>
          <!--服务弹框-->
          <van-actionsheet class="auction-box" v-model="serviceShow" :title="$t('lang.service_note')">
              <ul>
                  <li class="padding-all">
                    <p class="dis-box">
                          <label class="box-flex">
                              <i class="iconfont icon-iconfontzhizuobiaozhun10 color-red f-06"></i>
                              <span class="p-l05 f-06 color-3">{{$t('lang.goods_service_1')}}</span>
                          </label>
                      </p>
                    <p class="fontdize-3 color-7 m-top04">{{$t('lang.service_note_propmt_2')}}</p>
                  </li>
                  <li class="b-color-f padding-all">
                      <p class="dis-box">
                          <label class="box-flex">
                              <i class="iconfont icon-wuliu32pt color-red f-06"></i>
                              <span class="p-l05 f-06 color-3">{{$t('lang.goods_service_2')}}</span>
                          </label>
                      </p>
                      <p class="fontdize-3 color-7 m-top04">{{$t('lang.service_note_propmt_5')}}</p>
                  </li>
                  <li class="b-color-f padding-all">
                      <p class="dis-box">
                          <label class="box-flex">
                              <i class="iconfont icon-02 color-red f-06"></i>
                              <span class="p-l05 f-06 color-3">{{$t('lang.goods_service_3')}}</span>
                          </label>
                      </p>
                      <p class="fontdize-3 color-7 m-top04">{{$t('lang.service_note_propmt_1')}}</p>
                  </li>
              </ul>
          </van-actionsheet>
      </div>
      <!--拼团规则-->
      <div class="team-rule bg-color-write m-top10">
        <div class="title  f-04 color-3">{{$t('lang.team_rule')}}</div>
        <div class="dis-box fontsize-04 text-c color-7 p-r m-top10">
            <div class="box-flex p-r">
                <label class="num-left p-a f-04 text-center bg-color-write">1</label>
                <p class="text-center">{{$t('lang.team_rule_tit_1')}}</p>
                <div class="n-list-xian-first p-a"></div>
            </div>
            <div class="box-flex p-r">
                <label class="num-left p-a f-04 text-center bg-color-write">2</label>
                <p class="text-center">{{$t('lang.team_rule_tit_2')}}</p>
                <div class="n-list-xian p-a"></div>
            </div>
            <div class="box-flex p-r">
                <label class="num-left p-a f-04 text-center bg-color-write">3</label>
                <p class="text-center">{{$t('lang.team_rule_tit_3')}}</p>
                <div class="n-list-xian p-a"></div>
            </div>
            <div class="box-flex p-r">
                <label class="num-left p-a f-04 text-center bg-color-write">4</label>
                <p class="text-center">{{$t('lang.team_rule_tit_4')}}</p>
                <div class="n-list-xian-last p-a"></div>
            </div>
        </div>
        <div class="team-jiantou p-r" @click="goodsRule">
            <i class="iconfont icon-moreunfold p-a color-9"></i>
        </div>
        <!--拼团规则弹框-->
        <van-actionsheet class="auction-box" v-model="ruleShow" :title="$t('lang.team_rule')">
          <div class="padding-all">
            <div class="f-03 color-7">{{$t('lang.team_rule_con_1')}}</div>
            <div class="f-03 color-7 m-top06">{{$t('lang.team_rule_con_2')}}</div>
            <div class="f-03 color-7 m-top06">{{$t('lang.team_rule_con_3')}}</div>
            <div class="f-03 color-7 m-top06">{{$t('lang.team_rule_con_4')}}</div>
          </div>
        </van-actionsheet>
      </div>
      <div class="padding-all bg-color-write" v-if="teamDetailData.team_log!=''">
	      <p class="f-04 color-9">{{$t('lang.team_propmt_1')}}</p>
        <div class="dis-box group-list-box m-top12" v-for="(item,index) in teamDetailData.team_log" :key="index">
            <div class="left-box">
                <div class="left-img">
                  <img  class="img" :src="item.user_picture" v-if="item.user_picture" />
                  <img  class="img" src="../../../../assets/img/user_default.png" />
                </div>
            </div>
            <div class="box-flex list-two">
            <ul class="goods-time-box">
                <li><h4 class="f-05 color-3 onelist-hidden">{{ item.user_name }}</h4></li>
                <li><div class="box-flex f-04 list-cont onelist-hidden text-center">{{$t('lang.short')}}<em class="color-red">{{item.surplus}}</em>{{$t('lang.one_cheng_group')}}</div></li>
                </ul>
                <div class="dis-box color-9 m-top02 f-02" v-if="item.start_time!=undefined && item.end_time!=undefined">
                   {{$t('lang.residue')}}
                  <count-down :endTime="item.end_time" :endText="$t('lang.activity_end')"></count-down>
                  {{$t('lang.end')}}
                </div>
            </div>
            <p class="right-btn btn-submit f-05" @click="onTeamCheckout(item.team_id)">{{$t('lang.to_tuxedo')}}<i class="iconfont icon-more f-06"></i></p>
        </div>
      </div>
      <van-cell-group class="m-top08 common-cell">
        <van-cell class="f-04" :title="$t('lang.team_rec')" />
      </van-cell-group>
      <div class="padding-all swiper-x-box bg-color-write">
        <swiper class="swiper" :options="swiperOption" ref="mySwiper">
            <swiper-slide v-for="(item, index) in teamDetailData.new_goods" :key="index">
              <router-link :to="{name:'team-detail',query:{goods_id:item.goods_id,team_id:0}}">
                <div class="img-box">
                  <img class="img" :src="item.goods_thumb" />
                </div>
                <h4 class="twolist-hidden f-04 color-3 m-top08"> {{ item.goods_name }}</h4>
                <div class="f-05 color-red m-top06" v-html="item.team_price"></div>
              </router-link>
            </swiper-slide>
        </swiper>
      </div>
      <!--详情-->
      <van-tabs v-model="active" class="m-top08 goods-detail">
        <van-tab v-for="(item,index) in tabs" :title="item" :key="index">
          <template v-if="index == 0">    
            <div class="padding-all bg-color-write goods-detail" v-html="goodsDesc" v-if="goodsDesc"></div>
            <NotCont v-else />
          </template>
          <template v-else>
            <NotCont />
          </template>
        </van-tab>
      </van-tabs>
    </div>
    <van-goods-action class="two-line-btn">
      <van-goods-action-mini-btn icon="chat" :text="$t('lang.customer_service')" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)" />
      <van-goods-action-mini-btn :icon="collect_icon" :text="$t('lang.collect')" :class="{'curr':is_collect == 1}" @click="collection" />
      <van-goods-action-mini-btn icon="cart" :info="cart_number" :to="{name:'cart'}">
        {{$t('lang.cart')}}
        <span class="add_num" :class="{'show':addCartClass == true}" id="popone">+1</span>
      </van-goods-action-mini-btn>
      <van-goods-action-big-btn @click="onSku">
        <h4 class="f-06">{{$t('lang.buy_separately')}}</h4>
        <p class="f-03 m-top02" v-html="goodsInfo.shop_price"></p>
      </van-goods-action-big-btn>
      <template v-if="team_id > 0">
        <van-goods-action-big-btn primary @click="onTeamSku">{{$t('lang.to_tuxedo')}}</van-goods-action-big-btn>
      </template>
      <template v-else>
      <van-goods-action-big-btn primary @click="onTeamSku">
        <h4 class="f-06">{{goodsInfo.team_num}}{{$t('lang.one_group')}}</h4>
        <p class="f-03 m-top02" v-html="goodsInfo.team_price"></p>
      </van-goods-action-big-btn>
      </template>
    </van-goods-action>

    <!--普通购买弹框-->
    <van-popup v-model="showBase" position="bottom" class="attr-goods-box">
			<div class="attr-goods-header">
				<div class="attr-img">
					<img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img">
					<img :src="goodsInfo.goods_thumb" class="img" v-else>
				</div>
        <template v-if="attr">
  				<div class="attr-info">
  					<div class="attr-price m-top02" v-html="goodsAttrOper.spec_price_formated"></div>
            <div class="attr-stock f-03">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</div>
            <div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }} тал</div>
  				</div>
        </template>
        <template v-else>
          <div class="attr-info">
            <div class="f-05 twolist-hidden">{{goodsInfo.goods_name}}</div>
            <div class="attr-price m-top02" v-html="goodsAttrOper.spec_price_formated"></div>
            <div class="attr-stock f-03">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</div>
          </div>
        </template>
				<i class="iconfont icon-close" @click="closeSku"></i>
			</div>
			<div class="attr-goods-content">
				<van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index" @change="changeAttr">
					<div class="sku-tit">{{ item.attr_name }}</div>
					<div class="sku-list">
						<div v-for="(option,listIndex) in item.attr_key" :key="listIndex">
				  		    <van-radio class="option f-03" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}</van-radio>
				    	</div>
					</div>
				</van-radio-group>
			</div>
			<div class="attr-goods-number dis-box">
				<span class="tit box-flex f-03">{{$t('lang.number')}}</span>
				<div class="stepper">
					<van-stepper 
					v-model="num"
				  integer
				  :min="1"
				  :max="goodsAttrOper.stock"
				  />
				</div>
			</div>
			<div class="van-sku-actions">
        <template v-if="stock">
           <van-button type="default" class="van-button--bottom-action" @click="onBuyClicked">{{$t('lang.button_buy')}}</van-button>
           <van-button type="primary" class="van-button--bottom-action" @click="onAddCartClicked(0)">{{$t('lang.add_cart')}}</van-button>
        </template>
        <template v-else>
            <van-goods-action-big-btn :text="$t('lang.understock')" class="van-button--disabled" disabled/>
        </template>
			</div>
    </van-popup>

    <!--拼团弹窗-->
    <van-popup v-model="showTeam" position="bottom" class="attr-goods-box">
			<div class="attr-goods-header">
				<div class="attr-img">
					<img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img">
					<img :src="goodsInfo.goods_thumb" class="img" v-else>
				</div>
        <template v-if="attr">
          <div class="attr-info">
            <div class="attr-price" v-html="goodsAttrOper.goods_price_formated"></div>
            <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}<em class="color-red p-l05">({{$t('lang.gb_limited')}}:{{goodsInfo.astrict_num}})</em></div>
            <div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }} тал</div>
          </div>
        </template>
        <template v-else>
          <div class="attr-info">
            <div class="attr-name twolist-hidden">{{ goodsInfo.goods_name }}</div>  
            <div class="attr-price">{{ goodsAttrOper.goods_price_formated }}</div>
            <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}<em class="color-red p-l05">({{$t('lang.gb_limited')}}:{{goodsInfo.astrict_num}})</em></div>
          </div>
        </template>
				<i class="iconfont icon-close" @click="closeSku"></i>
			</div>
			<div class="attr-goods-content">
				<van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index" @change="changeAttr">
					<div class="sku-tit">{{ item.attr_name }}</div>
					<div class="sku-list">
						<div v-for="(option,listIndex) in item.attr_key" :key="listIndex">
			  		    <van-radio class="option f-03" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}</van-radio>
			    	</div>
					</div>
				</van-radio-group>
			</div>
			<div class="attr-goods-number dis-box">
				<span class="tit box-flex f-03">{{$t('lang.number')}}</span>
				<div class="stepper">
					<van-stepper 
					v-model="num"
				  integer
				  :min="1"
				  :max="goodsInfo.astrict_num"
				  />
				</div>
			</div>
			<div class="van-sku-actions">
			    <template v-if="stock">
				    <van-button type="primary" class="van-button--bottom-action" @click="onTeamClicked">{{$t('lang.confirm')}}</van-button>
			    </template>
          <template v-else>
              <van-goods-action-big-btn :text="$t('lang.understock')" class="van-button--disabled" disabled/>
          </template>
			</div>
    </van-popup>

    <!--分享海报-->
    <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
      <img :src="shareImg" v-if="shareImg" class="img" />
      <span v-else>{{$t('lang.error_generating_image')}}</span>
    </van-popup>

    <CommonNav :routerName="routerName"></CommonNav>

    <!-- <div class="shopping-prompt ts-2 active" v-if="isGuide" @click="clickGuide"><img src="../../../assets/img/liulanqi.png" /></div> -->
  </div>
</template>
<script>
import { mapState } from 'vuex'
import {
  Swipe,
  SwipeItem,
  GoodsAction,
  GoodsActionBigBtn,
  GoodsActionMiniBtn,
  Actionsheet,
  Cell,
  CellGroup,
  Tab,
  Tabs,
  Row,
  Col,
  Tag,
  Toast,
  Popup,
  RadioGroup,
  Radio,
  Stepper,
  Button,
  Dialog
} from "vant"

import {
  swiper,
  swiperSlide
} from 'vue-awesome-swiper'

import CountDown from '@/components/CountDown'
import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'
import formProcessing from '@/mixins/form-processing'

export default {
  mixins: [formProcessing],
  name: "team-detail",
  components: {
    CommonNav,
    NotCont,
    CountDown,
    swiper,
    swiperSlide,
    [Swipe.name]: Swipe,
    [SwipeItem.name]: SwipeItem,
    [GoodsAction.name]: GoodsAction,
    [GoodsActionBigBtn.name]: GoodsActionBigBtn,
    [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
    [Actionsheet.name]: Actionsheet,
    [Cell.name]: Cell,
    [CellGroup.name]: CellGroup,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
    [Row.name]: Row,
    [Col.name]: Col,
    [Tag.name]: Tag,
    [Toast.name]: Toast,
    [Popup.name]: Popup,
    [RadioGroup.name] : RadioGroup,
    [Radio.name] : Radio,
    [Stepper.name] : Stepper,
    [Button.name] : Button,
    [Dialog.name] : Dialog,
  },
  data() {
    return {
      routerName:'team',
      active: 2,
      num:1,
      tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
      showBase:false,//普通弹框
      showTeam:false,//拼团弹框
      showDetail:false,
      showTteamDetail: false,
      ruleShow: false, //拼团规则
      serviceShow: false, //服务
      //轮播图滑动
      swiperOption: {
        scrollbarHide: true,
        slidesPerView: 'auto',
        centeredSlides: false,
        grabCursor: true,
        autoplay: 2500,
      },
      addCartClass:false,
      goods_id: this.$route.query.goods_id,
      team_id: this.$route.query.team_id ? this.$route.query.team_id : 0,
      platform: this.$route.query.platform ? this.$route.query.platform : '',
      shareImgShow:false,
      shareImg:''
    }
  },
  created() {
    let that = this
    setTimeout(() => {
      uni.getEnv(function(res){
          if(res.plus || res.miniprogram){
              uni.redirectTo({
                  url:'../../pagesA/team/detail/detail?team_id='+that.team_id+'&goods_id='+that.goods_id
              })
          }
      })
    },100)
    this.loadGoodsInfo(this.goods_id);
  },
  mounted(){
    // let url = window.location.href

    // if(this.platform == 'APP'){
    //     if(isApp.isWeixinBrowser()){
    //         this.isGuide = true
    //     }else{
    //         Dialog.confirm({
    //             message:'Бұл беттен кетіп,"'+ window.pageTitle + '" өтісіз',
    //             confirmButtonText:'Рұқсат',
    //             className: 'text-center'
    //         }).then(() => {
    //             window.location.href = "dscmall://startapp?page="+url
    //         }).catch(() => {
    //         // on cancel
    //         });
    //     }
    // }
  },
  computed: {
    ...mapState({
      teamDetailData: state => state.team.teamDetailData
    }),
    goodsInfo(){
      return this.teamDetailData.goods_info
    },
    goodsDesc(){
      return this.goodsInfo.desc_mobile ? this.goodsInfo.desc_mobile : this.goodsInfo.goods_desc
    },
    isLogin(){
      return localStorage.getItem('token') == null ? false : true
    },
    goodsCollectStatue(){
      return this.$store.state.user.goodsCollectStatue
    },
    attr(){
      return this.teamDetailData.goods_properties    //商品属性
    },
    goodsAttrInit(){
      return this.$store.state.team.goodsAttrInit    //商品属性id
    },
    goodsAttrOper(){
      return this.$store.state.team.goodsAttrOper
    },
    stock() {
        return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
    },
    is_collect:{
      get(){
        return this.goodsInfo.is_collect
      },
      set(val){
        this.goodsInfo.is_collect = val
      }
    },
    collect_icon(){
      return this.is_collect == 1 ? 'like' : 'like-o'
    },
    cart_number:{
      get(){
        return this.goodsInfo.cart_number
      },
      set(val){
        this.goodsInfo.cart_number = val
      }
    },
  },
  methods: {
    loadGoodsInfo(goods_id){
      this.$store.dispatch('setTeamDetail',{
        goods_id: goods_id,
        team_id: this.team_id
      })
    },
    onSku(){
      //立即购买
      this.showBase = true
      this.changeAttr()
    },
    closeSku(){
      //关闭属性弹框
      this.showBase = false;
      this.showTeam = false;
    },
    changeAttr(){
      //属性选择
      this.$store.dispatch('setTeamProperty',{
        goods_id: this.goods_id,
        num: this.num,
        attr_id:this.goodsAttrInit,
        warehouse_id: 0,
        area_id: 0
      })
    },
    onAddCartClicked(type){
      this.addCartClass = false

      let newAttr = []

      if (this.attr.length > 0) {
        newAttr = this.goodsAttrInit
      }

      this.$store.dispatch('setAddCart',{
        goods_id:this.goods_id,
        num:this.num,
        spec:newAttr,
        warehouse_id:'0',
        area_id:'0',
        parent_id:'0',
        rec_type:type
      }).then(res => {
        if(res == true){
          if(type == 10){
            this.$router.push({
              name:'checkout',
              query:{
                rec_type:type
              }
            })
          }else{
            this.addCartClass = true
            Toast.success({
              duration:1000,
              message:this.$t('lang.added_to_cart')
            })
            this.cart_number = parseInt(this.cart_number) + 1
            this.closeSku()
          }
        }
      })
    },
    onBuyClicked(){
      //立即购买
      this.onAddCartClicked(10)
    },
    onTeamSku(){
      //拼团弹框
      this.showTeam = true
      this.changeAttr()
    },
    //确定购买
    onTeamClicked() {
      let newAttr = []

      if (this.attr.length > 0) {
        newAttr = this.goodsAttrInit
      }

      if(this.isLogin){
        this.$store.dispatch('setTeamBuy',{
          goods_id:this.goods_id,
          team_id:this.team_id,
          t_id:this.goodsInfo.id,
          num:this.num,
          attr_id:newAttr
        }).then(({data})=>{
          if(data.error == 0){
            this.$router.push({
              name:'checkout',
              query:{
                rec_type:data.rec_type,
                type_id:data.t_id,
                team_id:data.team_id
              }
            })
          }else{
            Toast(data.msg)
          }
        })
      }else{
        let msg = this.$t('lang.login_user_not')
        this.notLogin(msg)
      }
    },
    goodsRule() {
      //拼团规则
      this.ruleShow = !this.ruleShow
    },
    goodsService() {
      //服务
      this.serviceShow = !this.serviceShow
    },
    //参团
    onTeamCheckout(id){
      this.team_id = id
      this.onTeamSku()
    },
    collection(){
      //收藏
      if(this.isLogin){
        this.$store.dispatch('setCollectGoods',{
          goods_id:this.goodsInfo.goods_id,
          status:this.is_collect
        })
      }else{
        let msg = this.$t('lang.fill_in_user_collect_goods')
        this.notLogin(msg)
      }
    },
    notLogin(msg){
      let url = window.location.href;
      //未登录提示
      Dialog.confirm({
        message:msg,
        className:'text-center'
      }).then(()=>{
        this.$router.push({
          path: '/login',
          query:{ 
            redirect: {
              name:'team-detail',
              query:{ 
                goods_id: this.goods_id,
                team_id: this.team_id
              },
              url:url
            }
          }
        })
      }).catch(()=>{

      })
    },
    //分享海报
    onGoodsShare(){
        if (this.isLogin) {
            Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
            let price = this.goodsInfo.team_price
            this.$store.dispatch('setGoodsShare',{
                goods_id:this.goodsInfo.goods_id,
                price: price,
                share_type:0,
                extension_code:'team',
                code_url:window.location.href,
                 thumb:this.teamDetailData.goods_img[0] || this.goodsInfo.goods_img
            }).then(res => {
                if(res.status == 'success'){
                    this.shareImg = res.data
                    this.shareImgShow = true
                    Toast.clear()
                }
            })
        } else {
            let msg = this.$t('lang.login_user_not')
            this.notLogin(msg)
        }
    }
  },
  watch:{
    '$route'(to, from) {
      this.dscLoading = true
      this.goods_id = to.query.goods_id
      this.loadGoodsInfo(to.query.goods_id)
    },
    teamDetailData(){
      let that = this
      if(that.teamDetailData.error == 1){
        Toast({
          duration: 0,
          mask: true,
          forbidClick: true,
          message:that.teamDetailData.msg
        });

        setTimeout(function() {
          that.$router.push({
            name:'team'
          })
        }, 1500);
      }
    },
    goodsCollectStatue(){
      //关注跟踪变化
      this.goodsCollectStatue.forEach((v)=>{
        if(v.id == this.teamDetailData.goods_info.goods_id){
          this.is_collect = v.status
        }
      })
    },
    goodsInfo(){
      //单独设置微信分享信息
      this.$wxShare.share({
        title:this.goodsInfo.goods_name,
        desc:this.goodsInfo.team_desc,
        link:`${window.ROOT_URL}mobile#/team/detail?goods_id=` + this.goods_id +'&team_id=' + this.team_id,
        imgUrl:this.goodsInfo.goods_thumb
      })
    },
    showTeam(){
      if(this.showTeam == false){
        this.team_id = this.$route.query.team_id
      }
    }
  }
}
</script>
