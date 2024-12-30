<template>
  <div class="con" :class="goodsCartList.length > 0 ? 'con_main' : 'con_main_b5'" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    <template v-if="goodsCartList.length > 0">
      <header class="header-address">
        <div class="address-box" @click="handelRegionShow">
          <i class="iconfont icon-location"></i>
          <span>{{ regionSplic }}</span>
        </div>
        <div class="h-edit color-red" @click="onEdit"><template v-if="!batchEdit">{{$t('lang.edit_goods')}}</template><template v-else>{{$t('lang.accomplish')}}</template></div>
      </header>
      <section class="card-goods">
        <div class="card-goods-group" v-for="(storeList,listIndex) in goodsCartList" :key="listIndex">
          <div class="card-shop-head">
            <div class="card-goods__item">
              <div @click="checkShop(checkedShop[listIndex],storeList,listIndex,shopInvalidArr[listIndex])">
                <van-checkbox v-model="checkedShop[listIndex]" :disabled="shopInvalidArr[listIndex]" />
              </div>
              <div class="van-checkbox__con">
              <div class="store_name">
                <template v-if="storeList.store_id >0">
                <strong><router-link :to="{name:'shopHome',params:{ id:storeList.store_id }}">{{ storeList.store_name }}</router-link></strong>
                <i class="iconfont icon-more"></i>
                </template>
                <template v-else>
                  <strong>{{ storeList.store_name }}</strong>
                </template>
                <a href="javascript:;" @click="handleCoupon(storeList.store_id)" class="fr color-red" v-if="storeList.coupuns_num > 0">{{$t('lang.receive_coupon')}}</a>
              </div>
            </div>
            </div>
          </div>
          <div class="card-shop-box">
            <div class="card-act-goods" v-for="(actItem,actIndex) in storeList.new_list" :key="actIndex">
              <div class="cart-act-title dis-box" v-if="actItem.act_type != null">
                <div class="act-left box-flex">
                  <em :class="{'active-zeng':actItem.act_type == 0,'active-zhekou':actItem.act_type == 2}">{{ actItem.act_type_txt }}</em>
                  <span class="ellipsis-one">
                    <!-- 满赠 -->
                    <template v-if="actItem.act_type == 0">
                      <template v-if="actItem.available">
                      {{$t('lang.filled_in')}}{{ actItem.min_amount }} <a href="javascript:;" class="color-red" @click="receiveGift(actItem.act_id,false)">{{$t('lang.get_gift')}}</a>
                      </template>
                      <template v-else>
                      {{$t('lang.shopping_with')}}{{ actItem.min_amount }},{{$t('lang.are_get_gift')}},<a href="javascript:;" class="color-red" @click="receiveGift(actItem.act_id,true)">{{$t('lang.view_gift')}}</a>
                      </template>
                    </template>

                    <!-- 满减 -->
                    <template v-else-if="actItem.act_type == 1">
                      <template v-if="actItem.available">
                        {{$t('lang.filled_in')}}{{ actItem.min_amount }}<i class="color-red">（{{$t('lang.stand_minus')}}{{actItem.act_type_ext_format}}）</i>
                      </template>
                      <template v-else>
                      {{$t('lang.man')}}{{ actItem.min_amount }}{{$t('lang.minus')}}{{ actItem.act_type_ext_format }}
                      </template>
                    </template>

                    <!-- 满折 -->
                    <template v-else>
                      <template v-if="actItem.available">
                        {{$t('lang.filled_in')}}{{ actItem.min_amount }} <i class="color-red">（{{$t('lang.enjoy')}}{{ actItem.act_type_ext_format }}{{$t('lang.percent_off_Discount')}}）</i>
                      </template>
                      <template v-else>
                      {{$t('lang.shopping_with')}}{{ actItem.min_amount }}{{$t('lang.enjoy')}}{{ actItem.act_type_ext }}{{$t('lang.percent_off_Discount')}}
                      </template>
                    </template>
                  </span>
                </div>
                <a href="javascript:;" class="act-more" @click="goCoudan(actItem.act_id)">
                  {{actItem.available ? $t('lang.visit_again') : $t('lang.gather_together')}}
                  <i class="iconfont icon-more"></i>
                </a>
              </div>
              <template v-for="(item,index) in actItem.act_goods_list">
              <div class="card-goods__item">
                <div class="icon" @click="checkGoods(item.rec_id,listIndex,item.is_invalid,item.product_number)">
                  <van-checkbox :name="item.rec_id" :value="checkedGoods[listIndex].includes(item.rec_id)" :disabled="item.is_invalid == 1 || item.product_number == 0" />
                </div>
                <div class="cart-goods-info">
                  <div class="cart-goods-warp">
                    <div class="thumb">
                      <a href="javascript:;" class="img-box" @click="productLink(item)">
                      <img class="img" :src="item.goods_thumb" v-if="item.goods_thumb" />
                      <img class="img" src="../../assets/img/no_image.jpg" v-else>
                      <em class="activity-tag" v-if="item.extension_code == 'package_buy'"><img src="../../assets/img/package.png" /></em>
                      <em class="activity-tag parts-tag" v-if="item.group_id && item.parent_id != 0"><img src="../../assets/img/parts-icon.png" /></em>
                      <!--商品标签-->
                      <div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
                        <img :src="item.goods_label_suspension.formated_label_image" class="img">
                      </div>
                      </a>
                      <template v-if="item.is_invalid == 0 && item.product_number == 0">
                      <div class="mask"></div>
                      <div class="mash-text">{{$t('lang.no_goods')}}</div>
                      </template>
                      <template v-if="item.is_invalid == 1">
                      <div class="mask"></div>
                      <div class="mash-text">{{$t('lang.lost_effectiveness')}}</div>
                      </template>
                      <template v-if="item.xiangou_error > 0 && !(item.is_invalid == 0 && item.product_number == 0)">
                      <div class="mask"></div>
                      <div class="mash-text">{{$t('lang.man_gb_limited')}}</div>
                      </template>
                    </div>
                    <div class="goods-warp">
                      <div class="goods-title onelist-hidden" @click="productLink(item)">
						  <img class="country_icon" v-if="item.country_icon != ''" :src="item.country_icon" />{{ item.goods_name }}
					  </div>
                      <div class="goods-attr" v-if="item.goods_attr">{{ item.goods_attr }}</div>
                      <div class="goods-info">
                        <currency-price :price="item.goods_price" style="box-flex: 1;"></currency-price>
                        <div class="oper">
                          <div class="stepper">
                            <van-stepper
                              v-model="item.goods_number"
                              integer
                              :min="item.is_minimum > 0 ? item.minimum : 1"
                              :max="item.product_number"
                              :disabled="stepDisabled.length > 0 ? (stepDisabled[listIndex][index] ? stepDisabled[listIndex][index].type : false) : false"
                              @change="goodsNumberhandle(item.rec_id,item.goods_number,item.act_id,storeList.store_id,item.isNumberXiangou)"
                              v-if="item.extension_code != 'package_buy'"
                              />
                            <van-stepper
                              v-model="item.goods_number"
                              integer
                              :min="item.is_minimum > 0 ? item.minimum : 1"
                              :max="item.product_number"
                              :disabled="stepDisabled.length > 0 ? (stepDisabled[listIndex][index] ? stepDisabled[listIndex][index].type : false) : false"
                              @change="goodsNumberhandle(item.rec_id,item.goods_number,item.act_id,storeList.store_id)"
                              v-else
                              />
                          </div>
                          <div class="oper-icon">
                            <i class="iconfont icon-icon1 curr" @click="collection(item.goods_id,item.is_collect)" v-if="item.is_collect == 1"></i>
                            <i class="iconfont icon-shoucang" @click="collection(item.goods_id,item.is_collect)" v-else></i>
                            <i class="iconfont icon-delete" @click="deleteCartGoods(item.rec_id)"></i>
                          </div>
                        </div>
                      </div>
					  <div class="goods-info" v-if="item.is_kj == 1">
						  <span style="font-size: 1.2rem; color:#B78D5A;">
							  <em v-if="item.rate_price > 0">{{$t('lang.import_tax')}}：{{item.format_rate_price}}</em>
							  <em v-else>{{$t('lang.import_tax')}}：{{$t('lang.goods_tax_included')}}</em>
						  </span>
					  </div>
                      <div class="goods-other" v-if="actItem.act_type != null">
                        <div class="other-item dis-box" @click="onFavourableList(item.goods_id,item.ru_id,item.act_id,item.rec_id)">
                          <em :class="{'active-zeng':actItem.act_type == 0,'active-zhekou':actItem.act_type == 2}">{{ actItem.act_type_txt }}</em>
                          <span class="ellipsis-one box-flex">{{ actItem.act_name }}</span>
                          <i class="iconfont icon-moreunfold"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="goods-store" v-if="item.store_count > 0">
                    <div class="left">{{$t('lang.support')}}<span class="color-red">{{$t('lang.private_store')}}</span>{{$t('lang.service')}}</div>
                  </div>
                </div>
              </div>
              <!--配件商品-->
              <div class="card-goods__item card-goods__item__outer" v-for="(partsItem,partsIndex) in item.parts" :key="partsIndex">
                <div class="cart-goods-info">
                  <div class="cart-goods-warp">
                    <div class="thumb">
                      <a href="javascript:;" class="img-box" @click="productLink(partsItem)">
                      <img class="img" :src="partsItem.goods_thumb" v-if="partsItem.goods_thumb" />
                      <img class="img" src="../../assets/img/no_image.jpg" v-else>
                      <em class="activity-tag" v-if="partsItem.extension_code == 'package_buy'"><img src="../../assets/img/package.png" /></em>
                      <em class="activity-tag parts-tag" v-if="partsItem.group_id && partsItem.parent_id != 0"><img src="../../assets/img/parts-icon.png" /></em>
                      </a>
                      <template v-if="partsItem.is_invalid == 0 && partsItem.product_number == 0">
                      <div class="mask"></div>
                      <div class="mash-text">{{$t('lang.no_goods')}}</div>
                      </template>
                      <template v-if="partsItem.is_invalid == 1">
                      <div class="mask"></div>
                      <div class="mash-text">{{$t('lang.lost_effectiveness')}}</div>
                      </template>
                      <template v-if="partsItem.xiangou_error > 0">
                      <div class="mask"></div>
                      <div class="mash-text">{{$t('lang.man_gb_limited')}}</div>
                      </template>
                    </div>
                    <div class="goods-warp">
                      <div class="goods-title onelist-hidden" @click="productLink(partsItem)">{{ partsItem.goods_name }}</div>
                      <div class="goods-attr" v-if="partsItem.goods_attr">{{ partsItem.goods_attr }}</div>
                      <div class="goods-info dis-box">
                        <currency-price :price="partsItem.goods_price" style="box-flex: 1;"></currency-price>
                        <div class="oper">
                          <div class="stepper">{{ partsItem.goods_number }}</div>
                         <div class="oper-icon">
                           <i class="iconfont icon-delete" @click="deleteCartGoods(partsItem.rec_id)"></i>
                         </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              </template>
              <!-- 赠品 -->
              <div class="card-goods__item card-goods__item__outer" v-for="(item,index) in actItem.act_cart_gift" :key="item.rec_id">
                <div class="cart-goods-info">
                  <div class="cart-goods-warp">
                    <div class="thumb">
                      <a href="javascript:;" class="img-box" @click="productLink(item)">
                      <img :src="item.goods_thumb" v-if="item.goods_thumb" class="img" />
                      <img class="img" src="../../assets/img/no_image.jpg" v-else>
                      <em class="activity-tag"><img src="../../assets/img/gift-icon.png" /></em>
                      </a>
                    </div>
                    <div class="goods-warp">
                    <div class="goods-title onelist-hidden" slot="title"><router-link :to="{name:'goods',params:{id:item.goods_id}}">{{ item.goods_name }}</router-link></div>
                    <div class="goods-attr" slot="tags" v-if="item.goods_attr">{{ item.goods_attr }}</div>
                    <div class="goods-info dis-box" slot="tags">
                     <div class="price box-flex" v-html="item.goods_price_format"></div>
                     <div class="oper">
                       <div class="stepper stepper_sum">x{{ item.goods_number }}</div>
                       <div class="oper-icon">
                         <template v-if="item.is_collect == 1">
                         <i class="iconfont icon-guanzhu1 curr" @click="collection(item.goods_id,item.is_collect)"></i>
                         </template>
                         <template v-else>
                         <i class="iconfont icon-collection" @click="collection(item.goods_id,item.is_collect)"></i>
                         </template>
                         <i class="iconfont icon-delete" @click="deleteCartGoods(item.rec_id,item.is_gift)"></i>
                       </div>
                     </div>
                    </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="shop-store-cart" v-if="shopStore[listIndex] && shopStore[listIndex][0]">
            <div class="store-btn" @click="shopStoreCart(listIndex)"><i class="iconfont icon-store-alt"></i>{{$t('lang.private_store')}}</div>
          </div>
        </div>
      </section>
      <!-- 全选 -->
      <div class="cart-submit van-submit-bar">
        <div class="van-submit-bar__bar">
          <div class="checkall" @click="checkAll(checkedAllDisabled)">
            <van-checkbox v-model="checkedAll" :disabled="checkedAllDisabled">{{$t('lang.checkd_all')}}</van-checkbox>
          </div>
          <template v-if="!batchEdit">
            <div class="van-submit-bar__text">
              <p>
                <span>{{$t('lang.total_flow')}}：</span>
                <span class="van-submit-bar__price van-submit-bar__price__cur">
                  <currency-price :price="totalPriceTip" :size="18"></currency-price>
                </span>
              </p>
              <p v-if="ratePrice > 0" class="van-submit-bar__sub">({{$t('lang.taxes_dues')}} <span v-html="ratePriceTip"></span>)</p>
              <p v-if="favourablePrice != 0" class="van-submit-bar__sub">({{$t('lang.already_save')}} <span v-html="favourableTip"></span>)</p>
            </div>
            <template v-if="!loading">
              <van-button type="danger" :disabled="!checkedGoodsId.length" @click="onSubmit">
                {{ submitBarText }}
              </van-button>
            </template>
            <template v-else>
              <van-button type="danger" :disabled="true">{{ submitBarText }}</van-button>
            </template>
          </template>
          <van-button type="warning" :disabled="!checkedGoodsId.length" @click="onBatchDelete" v-else>{{$t('lang.batch_delete')}}</van-button>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="flow-no-cart">
        <span class="gwc-icon"><img src="../../assets/img/cart-empty.png" class="img" /></span>
        <h3>{{$t('lang.shopping_cart_notice')}}</h3>
        <div class="card-btn">
          <van-button type="default" plain @click="goHome">{{$t('lang.go_around')}}</van-button>
          <van-button type="default" plain @click="goUser">{{$t('lang.view_follow')}}</van-button>
        </div>
      </div>
    </template>

    <ec-tab-down></ec-tab-down>

    <!--猜你喜欢-->
    <section class="goods-guess text-center" v-if="goodsGuessList">
      <h5 class="title-hrbg"><span>{{$t('lang.guess_love')}}</span><hr></h5>
      <section class="product-list product-list-medium">
        <ProductList :data="goodsGuessList" :routerName="routerName" :productOuter="true"></ProductList>
        <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
        <template v-if="guess_loading">
          <van-loading type="spinner" color="black" />
        </template>
      </section>
    </section>

    <!-- 领取赠品列表 -->
    <van-popup v-model="giftShow" position="bottom" class="show-popup-bottom gift-popup">
      <section class="head">
        <div class="title">
          {{$t('lang.amount_max_available')}}{{ act_type_ext }}{{$t('lang.goods_letter')}}
          <van-button size="small" type="primary" @click="giftChecked">{{$t('lang.subimt')}}</van-button>
        </div>
        <i class="iconfont icon-close fr" @click="handelClose('gift')"></i>
      </section>
      <section class="content">
        <template v-if="act_type_ext > 1">
          <van-checkbox-group v-model="checkGiftArr">
          <div class="card-goods__item" v-for="(item,index) in giftList" :key="item.id">
            <van-checkbox :name="item.id" :disabled="giftDisabled" />
            <div class="van-checkbox__con">
              <van-card class="cart-goods-info">
                <template slot="thumb">
                  <img :src="item.thumb" v-if="item.thumb" />
                  <img class="img" src="../../assets/img/no_image.jpg" v-else>
                </template>
                <div class="goods-title onelist-hidden" slot="title">{{ item.name }}</div>
                <div class="goods-price" slot="tags" v-html="item.price_formated"></div>
              </van-card>
            </div>
          </div>
          </van-checkbox-group>
        </template>
        <template v-else>
          <van-radio-group v-model="checkGift">
          <div class="card-goods__item" v-for="(item,index) in giftList" :key="item.id">
            <van-radio :name="item.id" :disabled="giftDisabled" />
            <div class="van-checkbox__con">
              <van-card class="cart-goods-info">
                <template slot="thumb">
                  <img :src="item.thumb" v-if="item.thumb" />
                  <img class="img" src="../../assets/img/no_image.jpg" v-else>
                </template>
                <div class="goods-title onelist-hidden" slot="title">{{ item.name }}</div>
                <div class="goods-price" slot="tags" v-html="item.price_formated"></div>
              </van-card>
            </div>
          </div>
          </van-radio-group>
        </template>
      </section>
    </van-popup>

    <!-- 促销活动 -->
    <van-popup v-model="favourableShow" position="bottom" class="show-popup-bottom gift-popup favourable-popup">
      <section class="head">
        <div class="title">{{$t('lang.optional_promotions')}}</div>
        <i class="iconfont icon-close fr" @click="handelClose('favourable')"></i>
      </section>
      <div class="content">
        <van-radio-group v-model="checkFavourable">
        <div class="card-goods__item" v-for="(item,index) in favourableList" :key="item.act_id">
          <van-radio :name="item.act_id" @click="onFavourableTab(item.act_id,item.rec_id)">
            <div class="act_item_right">
              <em v-if="item.act_type == 0" class="active-zeng">{{$t('lang.with_a_gift')}}</em>
              <em v-else-if="item.act_type == 1" class="active-jian">{{$t('lang.full_reduction')}}</em>
              <em v-else="item.act_type == 2" class="active-zhekou">{{$t('lang.discount')}}</em>
              <div class="act_name">{{ item.act_name }}</div>
            </div>
          </van-radio>
        </div>
        </van-radio-group>
      </div>
    </van-popup>

    <!-- 优惠券 -->
    <van-popup v-model="couponShow" position="bottom" class="show-popup-bottom show-goods-coupon">
        <section class="goods-show-title padding-all">
            <h3 class="fl">{{$t('lang.receive_coupon')}} ({{ coupuns_num }})</h3>
            <i class="iconfont icon-close fr" @click="handelClose('coupon')"></i>
        </section>
        <swiper class="goods-show-con" :options="swiperOption">
            <swiper-slide>
                <div class="padding-all">
                    <van-loading type="spinner" color="white" v-if="conpouLoading"/>
                    <template v-else>
                      <ul v-if="cartCouponsList && cartCouponsList.length > 0">
                          <li class="new-coupons-box dis-box" v-for="(item,index) in cartCouponsList">
                              <div class="remark-all box-flex">
                                  <div class="q-type">
                                      <div class="b-r-a-price">
                                          <em>{{ currency }}</em>
                                          <strong class="coupons-money">{{ item.cou_money }}</strong>
                                          <div class="couons-text"><span>{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</span></div>
                                      </div>
                                      <div class="b-r-a-con">
                                          <div class="range-item">{{ item.cou_name }}</div>
                                          <div class="range-item">{{ item.cou_start_time }} {{$t('lang.zhi')}} {{ item.cou_end_time
                                              }}<em class="color-red">({{$t('lang.cou_user_receive_hove')}}{{item.cou_user_num}}{{$t('lang.zhang')}})</em>
                                          </div>
                                      </div>
                                  </div>
                                  <b class="semi-circle"></b>
                                  <div class="print-img" v-if="item.cou_is_receive == 1"></div>
                              </div>
                              <a href="javascript:void(0);" class="b-r-a-btn tb-lr-center"
                                 @click="handelReceive(item.cou_id,item.ru_id)">
                                  <template v-if="item.cou_is_receive == 1 && isLogin == true">{{$t('lang.receive_hove')}}</template>
                                  <template v-else>{{$t('lang.receive')}}</template>
                              </a>
                          </li>
                      </ul>
                      <div class="notic-coupons" v-else>{{$t('lang.no_coupon_yet')}}</div>
                    </template>
                </div>
            </swiper-slide>
        </swiper>
    </van-popup>

    <!-- 地区选择 -->
    <Region :display="regionShow" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="customUpdateRegion"></Region>

    <ec-filter-top :scrollState="scrollState"></ec-filter-top>

    <!--初始化loading-->
    <DscLoading :dscLoading="dscLoading"></DscLoading>

    <div class="loading-transparent" v-show="loading">
      <van-loading type="circular" color="#F20E28" />
      <div class="loading-overlay"></div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import {
  Checkbox,
  CheckboxGroup,
  Radio,
  RadioGroup,
  Card,
  SubmitBar,
  Toast,
  Stepper,
  Dialog,
  Button,
  Popup,
  Loading,
  Waterfall
} from 'vant'

import {
  swiper,
  swiperSlide
} from 'vue-awesome-swiper'

import EcTabDown from '@/components/visualization/tab-down/Frontend'
import ProductList from '@/components/ProductList'
import DscLoading from '@/components/DscLoading'
import EcFilterTop from '@/components/visualization/element/FilterTop'
import arrRemove from '@/mixins/arr-remove'
import formProcessing from '@/mixins/form-processing'

export default {
  mixins: [formProcessing],
  components: {
    [Card.name]: Card,
    [Checkbox.name]: Checkbox,
    [CheckboxGroup.name]: CheckboxGroup,
    [Radio.name]: Radio,
    [RadioGroup.name]: RadioGroup,
    [SubmitBar.name]: SubmitBar,
    [Stepper.name]: Stepper,
    [Toast.name]: Toast,
    [Dialog.name]: Dialog,
    [Button.name]: Button,
    [Popup.name]: Popup,
    [Loading.name]: Loading,
    EcTabDown,
    ProductList,
    swiper,
    swiperSlide,
    DscLoading,
    EcFilterTop,
  },
  directives: {
      WaterfallLower: Waterfall('lower')
  },
  data() {
    return {
      routerName:'goods',
      productOuter:true,
      checkedAll:true,
      checkedGoodsId:[],
      totalPrice:0,
      ratePrice:0,
      ratePriceTip:'',
      favourablePrice:0,
      default:true,
      count:0,
      swiperOption: {
        direction: 'vertical',
        slidesPerView: 'auto',
        freeMode: true
      },
      couponShow: false,
      conpouLoading: true,
      giftShow:false,
      giftList:[],
      act_type_ext:0,
      checkGift:'',
      checkGiftArr:[],
      giftDisabled:false,
	  currency: '₸',
      favourableShow:false,
      favourableList:[],
      checkFavourable:'',
      stepDisabled:[],
      shopStore:[],
      coupuns_num:0,
      batchEdit:false,
      loading:true,
      dscLoading:true,
      timer:'',
      page: 1,
      size: 10,
      footerCont:false,
      loading:false,
      disabled:false,
      scrollState:false,
      guess_loading:false,
      shopInvalidArr:[],
      checkedAllDisabled:false,
      isNumberXiangou:false
    }
  },
  created(){
    setTimeout(() => {
      uni.getEnv(function(res){
          if(res.plus || res.miniprogram){
              uni.reLaunch({
                  url:'../../pages/cart/cart'
              })
          }
      })
    },100)
	
	let configData = JSON.parse(sessionStorage.getItem('configData'));
	if(configData){
	  this.currency = configData.currency_format.replace('%s', '');
	}

    if(this.getRegionData){
      this.regionOptionDate = this.getRegionData;
    }

    //购物车商品列表
    this.goodsList()

    //猜你喜欢列表
    this.goodsGuessHandle(1)
  },
  mounted(){
    this.$nextTick(() => {
      window.addEventListener('scroll', this.onScroll)
    })
  },
  computed: {
    ...mapState({
      cartCouponsList: state => state.shopping.cartCouponsList,
      checkedGoods: state => state.shopping.checkedGoods,
      allGoodsListId: state => state.shopping.allGoodsListId
    }),
    goodsGuessList:{
      get() {
        return this.$store.state.shopping.goodsGuessList
      },
      set(val) {
        this.$store.state.shopping.goodsGuessList = val
      }
    },
    goodsCartList:{
      get(){
        return this.$store.state.shopping.goodsCartList
      },
      set(val){
        this.$store.state.shopping.goodsCartList = val
      }
    },
    checkedShop:{
      get(){
        return this.$store.state.shopping.checkedShop
      },
      set(val){
        this.$store.state.shopping.checkedShop = val
      }
    },
    submitBarText() {
      return this.$t('lang.go_checkout') + (this.count ? `(${this.count})` : '')
    },
    isLogin(){
      return localStorage.getItem('token') == null ? false : true
    },
    goodsCollectStatue(){
      return this.$store.state.user.goodsCollectStatue
    },
    totalPriceTip(){
      return this.totalPrice
    },
    favourableTip(){
      return this.favourablePrice
    }
  },
  methods: {
    //购物车列表
    goodsList(){
      this.$store.dispatch('setGoodsCart',{
        warehouse_id:0,
        area_id:0,
		    district_id:this.regionOptionDate.district.id
      })
    },
    //购物车全选
    checkAll(invalid){
      //是否有失效商品
      if(invalid) return

      this.goodsCartList.forEach((v,i)=>{
        this.checkedGoods[i] = []
        if (!this.checkedAll) {
			
			this.checkedShop.splice(i, 1, true)

			v.new_list.forEach((act) => {
				act.act_goods_list.forEach((g) => {
				  this.checkedGoods[i].push(g.rec_id)
				})
			})
        }else{
			this.checkedShop.splice(i, 1, false)
		}
      })

      this.changeGoods()
    },
    //选择店铺
    checkShop(checked,shop,index,shopInvalid){
      //是否有失效商品
      if(shopInvalid) return

	  this.checkedShop.splice(index, 1, !checked)

      this.checkedGoods[index] = []
	  
      if(!checked){
        shop.new_list.forEach((act)=>{
          act.act_goods_list.forEach((g)=>{
            this.checkedGoods[index].push(g.rec_id)
          })
        })
      }

      this.changeGoods()
    },
    //默认进购物车全部勾选商品
    changeDefaultGooods(){
      this.checkAllOper()
      this.default = false
      this.$store.dispatch('setCartDefault',{
        rec_id:this.checkedGoodsId
      }).then(({ data:data })=>{
        this.totalPrice = data.goods_amount  //商品价格
        if(data.cross_border){ //跨境
          this.ratePrice = data.rate_price              //税费
          this.ratePriceTip = data.rate_formated        //税费格式化
        }
        this.favourablePrice = data.discount_formated //购物车优惠价格
        this.count = data.cart_number                 //购物车勾选商品数量

        this.loading = false
      })
    },
    //选择购物车商品
    changeGoods(){
      let slength = []
      let clength = []
      this.checkedGoodsId = []
	  
      this.checkedGoods.forEach(v=>{
        slength.push(v.length)
        v.forEach(i=>{
          this.checkedGoodsId.push(i)
        })
      })

      this.checkAllOper()

      this.loading = true

      //更新购物车商品列表
      this.$store.dispatch('setCartChecked',{
        rec_id:this.checkedGoodsId,
        sel_flag:'cart_sel_flag',
		    district_id:this.regionOptionDate.district.id
      }).then(({data:data})=>{
        this.totalPrice = data.goods_amount        //商品价格
        if(data.cross_border){ //跨境
          this.ratePrice = data.rate_price              //税费
          this.ratePriceTip = data.rate_formated        //税费格式化
        }
        this.favourablePrice = data.discount_formated       //购物车优惠价格
        this.count = data.cart_number                       //购物车勾选商品数量

        if(data.cart_fav_box){                              //数量改变触发优惠活动条件，更新购物车
          this.goodsCartList = data.cart_fav_box
          this.loading = false
        }
      })
    },
    checkGoods(rec_id, index, is_invalid, product_number) {
      let clength = []

      //是否有失效商品
      if (is_invalid == 1 || product_number == 0) return
      
      this.checkedGoods.forEach((v, i) => {
        if (i == index) {
          if (v.indexOf(rec_id) > -1) {
            v.splice(v.indexOf(rec_id), 1)
          } else {
            v.push(rec_id)
          }
        }
      })

      //获取店铺下商品数量
      this.goodsCartList.forEach((v, index) => {
        let arr = []
        v.new_list.forEach((act) => {
          act.act_goods_list.forEach((g) => {
            arr.push(g)
          })
        })

        clength.push(arr.length)
      })

      //商品勾选状态 改变店铺勾选状态
      this.checkedShop[index] = (clength[index] == this.checkedGoods[index].length)

      this.changeGoods();
    },
    //更新购物车全选状态
    checkAllOper(){
      let cAll = 0
      this.checkedShop.forEach(v=>{
        if(!v){
          cAll++
        }
      })
      if(cAll > 0){
        this.checkedAll = false
      }else{
        this.checkedAll = true
      }
    },
    //修改购物车商品数量
    goodsNumberhandle(id,num,act_id,store_id,isNumberXiangou){
      //满限购断掉
      if(isNumberXiangou){
        this.goodsCartList.forEach((v)=>{
          v.new_list.forEach(g=>{
            g.act_goods_list.forEach(i=>{
              if(i.rec_id == id) i.isNumberXiangou = false
            })
          })
        })

        return
      }

      // clearTimeout(this.timer);
      // this.timer = setTimeout(()=>{
        this.stepDisabled.forEach((v,i)=>{
          v.forEach((s,d)=>{
            if(s.id == id){
              this.stepDisabled[i][d].type = true
            }
          })
        })
        this.$store.dispatch('setUpdateNumber',{
          rec_id:id,
          num:num,
		      district_id:this.regionOptionDate.district.id
        }).then(({data:data})=>{
          this.stepDisabled.forEach((v,i)=>{
            v.forEach((s,d)=>{
              if(s.id == id){
                this.stepDisabled[i][d].type = false
              }
            })
          })

          if(data.error == 0){
            this.totalPrice = data.goods_amount //商品价格
            this.favourablePrice = data.discount_formated //购物车优惠价格
            this.count = data.cart_number //购物车勾选商品数量

            if(data.cross_border){  //跨境
              this.ratePrice = data.rate_price              //税费
              this.ratePriceTip = data.rate_formated        //税费格式化
            }

            if(data.cart_fav_box){
              //数量改变触发优惠活动条件，更新购物车
              this.goodsCartList.forEach((v)=>{
                if(v.store_id == store_id){
                  v.new_list.forEach((g,i)=>{
                    if(g.act_id == act_id){
                      v.new_list[i] = data.cart_fav_box
                    }
                  })
                }
              })
            }

            if(data.goods_price_formated) {
              // 更新单价
              this.goodsCartList.forEach((v)=>{
                if(v.store_id == store_id){
                  v.new_list.forEach((g,i)=>{
                    g.act_goods_list.forEach(j=>{
                      if(j.rec_id == id){
                        j.goods_price_format = data.goods_price_formated
                      }
                    })
                  })
                }
              })
            }
          }else{
            this.goodsCartList.forEach((v)=>{
              v.new_list.forEach(g=>{
                g.act_goods_list.forEach(i=>{
                  if(i.rec_id == id){
                    i.goods_number = data.number
                    i.isNumberXiangou = true
                  }
                })
              })
            })

            Toast(data.msg)
          }
        })
      //},1000)
    },
    //删除购物车商品
    deleteCartGoods(val,is_gift){
      if(is_gift){
        this.checkGift = ''
        this.checkGiftArr = []
      }

      Dialog.confirm({
        message:this.$t('lang.confirm_delete_goods')
      }).then(()=>{
        this.$store.dispatch('setCartGoodsDelete',{
          rec_id:val
        }).then(res=>{
          if(res.error == 0){
              Toast.success({
                duration:1000,
                message:this.$t('lang.delete_success')
              })
              this.goodsList()
              this.changeGoods()
          }else{
            Toast({
              duration:1000,
              message:this.$t('lang.delete_fail')
            })
          }
        })
      }).catch(() => {
        // on cancel
      })
    },
    //赠品弹出层
    receiveGift(act_id,type){
      this.giftShow = true
      this.giftDisabled = type
      this.$store.dispatch('setGiftList',{
        act_id:act_id
      }).then(({data:data})=>{
        this.giftList = data.giftlist
        this.act_type_ext = parseInt(data.act_type_ext)
        this.giftList.forEach((v)=>{
          if(v.is_checked){
            if(this.act_type_ext > 1){
              this.checkGiftArr.push(v.id)
            }else{
              this.checkGift = v.id
            }
          }
        })
      })
    },
    //选择赠品
    giftChecked(){
      let act_id = 0
      let ru_id  = 0
      let select_gift
      this.giftList.forEach(v=>{
        act_id = v.act_id
        ru_id = v.ru_id
      })

      if(this.act_type_ext > 1){
        select_gift = this.checkGiftArr
      }else{
        select_gift = this.checkGift
      }

      this.$store.dispatch('setGiftChecked',{
        act_id:act_id,
        ru_id:ru_id,
        select_gift:select_gift
      }).then(({data:data})=>{
        Toast(data.message)
        if(data.error == 0){
          this.goodsList()
          this.giftShow = false
        }
      })
    },
    //优惠活动列表
    onFavourableList(goods_id,ru_id,act_id,rec_id){
      this.$store.dispatch('setFavourable',{
        goods_id:goods_id,
        ru_id:ru_id,
        act_id:act_id,
        rec_id:rec_id
      }).then(({data:data})=>{
        this.favourableList = data.favourable
        this.favourableShow = true
        this.favourableList.forEach((v)=>{
          if(v.is_checked){
            this.checkFavourable = v.act_id
          }
        })
      })
    },
    //选择优惠活动
    onFavourableTab(act_id,rec_id){
      this.$store.dispatch('setChangefav',{
        act_id:act_id,
        rec_id:rec_id
      }).then(({data:data})=>{
        Toast(data.msg)
        if(data.error == 0){
          this.goodsList()
          this.changeDefaultGooods()
          this.favourableShow = false
        }
      })
    },
    //领取优惠券
    handleCoupon(ru_id) {
        this.couponShow = true

        this.goodsCartList.forEach(v=>{
          if(v.store_id == ru_id){
            this.coupuns_num = v.coupuns_num
          }
        })

        this.$store.dispatch('setCoupons', {
            ru_id: ru_id
        }).then(() => {
            this.conpouLoading = false
        })
    },
    //优惠券领取
    handelReceive(val,ru_id) {
        this.$store.dispatch('setGoodsCouponReceive', {
            cou_id: val
        }).then(({data:data}) => {
          Toast({
              message:data.msg,
              duration:1000,
          })

          this.$store.dispatch('setCoupons', {
              ru_id: ru_id
          }).then(() => {
            this.conpouLoading = false
          })
        })
    },
    //凑单
    goCoudan(act_id){
      this.$router.push({
        name:'coudan',
        query:{
          act_id:act_id
        }
      })
    },
    onEdit(){
      this.batchEdit = this.batchEdit ? false : true
    },
    //批量删除
    onBatchDelete(){
      this.deleteCartGoods(this.checkedGoodsId);
      // this.$store.dispatch('setCartBatchDelete',{
      //   rec_id:this.checkedGoodsId
      // }).then(res=>{
      //   Toast(res.msg)
      //   if(res.error == 0){
      //     this.goodsList()
      //   }
      // })
    },
    //关闭活动弹出层
    handelClose(val){
      if(val == 'gift'){
        this.giftShow = false
      }else if(val == 'favourable'){
        this.favourableShow = false
      }else if(val == 'coupon'){
        this.couponShow = false
      }
    },
    //提交购物车到结算页面
    onSubmit() {
      let fald = true
      //验证库存是否不足
      this.goodsCartList.forEach((f) => {
        f.new_list.forEach((act)=>{
          act.act_goods_list.forEach((g)=>{
            if(g.product_number == 0 && g.is_checked == true){
              Toast(g.goods_name+this.$t('lang.understock_not_submit'));
              fald = false
              return false
            }

            if(g.is_invalid == 1 && g.is_checked == true){
              Toast(g.goods_name+this.$t('lang.goods_soldout_not_submit'));
              fald = false
              return false
            }

            if(g.xiangou_error > 0 && g.is_checked == true){
              let msg = ''
              if(g.xiangou_can_buy_num > 0){
                msg = g.goods_name + this.$t('lang.purchase_only') + g.xiangou_can_buy_num + 'тал'
              }else{
                msg = g.goods_name + this.$t('lang.restriction_is_full')
              }
              Toast(msg);
              fald = false
              return false
            }
          })
        })
      })

      if(this.checkedGoodsId.length > 0 && fald){
  		  let rec_id = ''
        let rec_id_first = ''
        let arr = []
  		  this.goodsCartList.forEach((v,i)=>{
          arr[i] = [];
		      v.new_list.forEach((act)=>{
		        act.act_goods_list.forEach(goods=>{
		          if(goods.store_count > 0 && goods.is_checked > 0){
                arr[i].push(goods.rec_id)
		          }
		        })
		      })
  		  })
        
        if(arr.length > 0){
          arr.forEach((v,i)=>{
            if(v.length == 0){
              arr.splice(i,1)
            }
          })

          if(arr[0]){
            arr[0].forEach(v=>{
              rec_id_first += v + ','
            })
          }

          rec_id = rec_id_first.substr(0, rec_id_first.length - 1)
        }

        if(this.isLogin){
          if(rec_id){
  				  this.$router.push({ name:'checkout',query:{rec_id:rec_id,type:true}})
    			}else{
    			  this.$router.push({ name:'checkout'})
    			}
        }else{
          let msg = this.$t('lang.login_user_not')
          this.notLogin(msg)
        }
      }
    },
    //关注购物车商品
    collection(id,status){
      if(this.isLogin){
        this.$store.dispatch('setCollectGoods',{
          goods_id:id,
          status:status
        })
      }else{
        let msg = this.$t('lang.fill_in_user_collect_goods')
        this.notLogin(msg)
      }
    },
    //判断是否登录
    notLogin(msg){
      let url = window.location.href;
      Dialog.confirm({
        message:msg,
        className:'text-center'
      }).then(()=>{
        this.$router.push({
          path: '/login',
          query:{
            redirect: {
              name:'cart',
              url:url
            }
          }
        })
      }).catch(()=>{

      })
    },
    //返回首页
    goHome(){
      this.$router.push({ name:'home'})
    },
    //进入用户中心关注列表
    goUser(){
      this.$router.push({ name:'collectionGoods'})
    },
    //地区
    handelRegionShow(){
      this.regionShow = this.regionShow ? false : true
    },
    //猜你喜欢
    goodsGuessHandle(page){
      if(page){
        this.page = page
        this.size = Number(page) * 10
      }

      this.$store.dispatch('setGoodsGuess',{
        page:this.page,
        size:this.size
      })
    },
    shippingFee(val) {
      this.$store.dispatch('setShippingFee', {
          goods_id: 0,
          position: val
      })
    },
    productLink(item){
      if(item.extension_code == 'package_buy'){
        this.$router.push({name:'package'})
      }else{
        this.$router.push({ name:'goods', params:{id:item.goods_id}})
      }
    },
    shopStoreCart(index){
      let rec_id = ''
      this.goodsCartList.forEach((v,i)=>{
        if(index == i){
          v.new_list.forEach((act)=>{
            act.act_goods_list.forEach(goods=>{
              if(goods.store_count > 0 && goods.is_checked > 0){
                rec_id += goods.rec_id + ','
              }
            })
          })
        }
      })

      rec_id = rec_id.substr(0, rec_id.length - 1)

      if(rec_id.length > 0){
        if(this.isLogin){
          this.$router.push({
            name:'storeGoods',
            query:{
              rec_id:rec_id
            }
          })
        }else{
          let msg = this.$t('lang.login_user_not')
          this.notLogin(msg)
        }
      }else{
        Toast(this.$t('lang.select_store_goods'));
      }
    },
    loadMore(){
      setTimeout(() => {
        this.disabled = true
        if(this.page * this.size == this.goodsGuessList.length){
          this.page ++
          this.goodsGuessHandle() 
        }
      },200)
    },
    onScroll() {
      let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

      if (scrollTop > 800) {
        this.scrollState = true
      } else {
        this.scrollState = false
      }
    },
    customUpdateRegion(e){
      this.regionData = e;
        
      //更新列表
      this.goodsList();
    },
  },
  destroyed() {
    window.removeEventListener("scroll",  this.onScroll);
  },
  watch:{
    goodsGuessList(){
      if(this.page * this.size == this.goodsGuessList.length){
        this.disabled = false
        this.guess_loading = true
      }else{
        this.guess_loading = false
        this.footerCont = this.page > 1 ? true : false
      }

      this.goodsGuessList = arrRemove.trimSpace(this.goodsGuessList)
    },  
    goodsCollectStatue(){
      this.goodsCollectStatue.forEach((v)=>{
        this.goodsCartList.forEach((f) => {
          f.new_list.forEach((act)=>{
            act.act_goods_list.forEach((g)=>{
              if(g.goods_id == v.id){
                g.is_collect = v.status
              }
            })
          })
        })
      })
    },
    goodsCartList(){
      let obj = {}
      let arr = []
      let shopArr = []
      let shopArr2 = []
      let shopState = false
      let shopInvalid = false
      this.dscLoading = false
      if(this.default){
        this.checkedGoodsId = []
        this.checkedGoods.forEach((v,index)=>{
          v.forEach(i=>{
            this.checkedGoodsId.push(i)
          })
        })

        this.allGoodsListId.forEach((v,index)=>{
          arr[index] = []

          v.forEach(i=>{
            obj = {
              id:i,
              type:false
            }

            arr[index].push(obj)
          })
        })

        this.stepDisabled = arr

        //判断店铺下商品是否全部选中
        this.goodsCartList.forEach((v,index)=>{
          v.new_list.forEach((act)=>{
            if(act.act_cart_gift){
              act.act_cart_gift.forEach((gift)=>{
                this.checkedGoodsId.push(gift.rec_id)
              })
            }
          })
        })

        //初始化选中商品
        this.changeDefaultGooods()
      }

      this.goodsCartList.forEach((v,index)=>{
        shopArr[index] = []
        shopState = false
        let cont = 0
        v.new_list.forEach((act)=>{
          act.act_goods_list.forEach(goods=>{
            //判断店铺下商品是否全部选中
            if(goods.store_count > 0){
              shopState = true
            }

            //判断店铺下是否有无效商品
            if(goods.is_invalid > 0){
              cont++
              this.checkedAllDisabled = true
            }
          })
        })
        shopArr[index].push(shopState);
        shopArr2.push(cont > 0 ? true : false);
      })

      //门店自提按钮
      this.shopStore = shopArr

      //店铺下商品是否失效
      this.shopInvalidArr = shopArr2
    },
    regionSplic(){
      let shipping_region = {
        province_id: this.regionOptionDate.province.id,
        city_id: this.regionOptionDate.city.id,
        district_id: this.regionOptionDate.district.id,
        street_id: this.regionOptionDate.street.id
      }

      //运费
      this.shippingFee(shipping_region)
    }
  }
}
</script>

<style scoped>
.van-submit-bar__bar{ min-height: 50px; height: auto }
.goods-store{
  background: #fff;
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
  font-size: 1.2rem;
  padding: 1rem 0;
}

.shop-store-cart {
  display: flex;
  justify-content: flex-end;
  padding: 0 0 0 4rem;
  margin-top: 1rem;
}
.shop-store-cart .store-btn{
    padding: 0 .8rem;
    height: 2.2rem;
    line-height: 2.2rem;
    border:1px solid #f42424;
    background: #fff;
    color: #f42424;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    font-size: 1.2rem;
}
.shop-store-cart .store-btn .iconfont{
  font-size: 14px;
  margin-right: .5rem;
}

.con .commom-nav{
  bottom: 15%;
}

/*loading*/
.loading-transparent{ position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999;}
.loading-transparent /deep/ .van-loading{ z-index: 999; }
.loading-transparent .loading-overlay{ position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 998;}

.country_icon{
	width: 2.4rem;
	padding-right: 0.3rem;
	position: relative;
	top: 0.2rem;
	display: inline-block;
}
</style>
