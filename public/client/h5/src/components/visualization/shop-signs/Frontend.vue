<template>
    <div class='shop-signs' v-if="shopInfo">
        <header>
            <img class="bg-img" :src="shopInfo.street_thumb" alt="">
            <div class="shop-signs-mask"></div>
        </header>
        <section class="info-nums">
            <div class="info-head">
              <h4><div class="img-box"><img :src="shopInfo.logo_thumb" class="img" alt="" /></div></h4>
              <p>{{$t('lang.already_have')}}<em>{{ shopInfo.like_num }}</em>{{$t('lang.collection_two')}}</p>
            </div>
            <ul>
                <li @click="sAllProductUrl">
                    <span>{{$t('lang.all')}}：<em>{{ shopInfo.total }}</em></span>
                </li>
                <li @click="sNewProductUrl">
                    <span>{{$t('lang.new')}}：<em>{{ shopInfo.new }}</em></span>
                </li>
                <li @click="sPromotePoductUrl">
                    <span>{{$t('lang.promotion')}}：<em>{{ shopInfo.promote }}</em></span>
                </li>
            </ul>
        </section>
        <van-popup v-model="couponShow">
          <div class="coupon-select">
            <div class="li dis-box bg-color-write p-r">
              <div class="left box-flex">
                <div class="dis-box">
                  <div class="">
                    <h4 class="f-weight">
                      <template v-if="couponInfo.cou_type == 5">
                        {{$t('lang.freight_free')}}
                      </template>
                      <template v-else>
                        <template v-if="!couponInfo.order_sn">
                          <template v-if="couponInfo.uc_money > 0">
                            {{ couponInfo.uc_money }}
                          </template>
                          <template v-else>
                            {{couponInfo.cou_money}}
                          </template>
                        </template>
                        <template v-else>
                          {{couponInfo.order_coupons}}
                        </template>
                      </template>
                    </h4>
                  </div>
                  <div class="box-flex p-l1 f-02">
                    <p class="name m-top02">{{couponInfo.cou_type_name}}</p>
                    <div class="color-3">{{$t('lang.man')}}{{couponInfo.cou_man}}{{$t('lang.usable')}}</div>
                  </div>
                </div>
                <div class="f-02 color-9 m-top08">{{couponInfo.cou_title}}</div>
                <div class="f-02 color-9">{{ couponInfo.store_name }}</div>
                <div class="f-02 color-9">{{couponInfo.cou_start_time}}{{$t('lang.zhi')}}{{couponInfo.cou_end_time}}</div>
                <b class="semi-circle"></b>
              </div>
              <img v-if="couponInfo.staic==='1'" class="p-a" src="@/assets/img/coupons-print-end.png" />
              <img v-if="couponInfo.staic==='2'" class="p-a" src="@/assets/img/coupons-print-enddata.png" />
              <template>
                <a href="javascript:;" class="show right text-center f-04" @click="couponLink(couponInfo.cou_id)">{{$t('lang.attention_store')}}<br>{{$t('lang.and_get_the')}}</a>
              </template>
            </div>
          </div>
        </van-popup>
    </div>
</template>

<script>
//node library
import qs from 'qs'

import {
  mapState
} from 'vuex'

// third party components
import { Dialog, Popup, Toast } from 'vant'

export default {
  name: 'shop-signs',
  props: ['data', 'preview'],
  mixins: [],
  components: {
    [Dialog.name]:Dialog,
    [Popup.name]:Popup,
    [Toast.name]:Toast
  },
  data() {
    return {
      ru_id: 0,
      couponShow: false,
      couponInfo:{}
    }
  },
  activated() {
    let that = this

    if(window.apiAuthority == 'view'){
      that.ru_id = that.$route.params.id
    }else{
      that.ru_id = window.shopInfo.ruid
    }

    that.$store.dispatch('setVisualStorein',{
      ru_id: that.ru_id
    })

    if(window.apiAuthority == 'view'){
      // 获取关注店铺优惠券
      this.$http.post(`${window.ROOT_URL}api/shop/coupons`,qs.stringify({
        ru_id: this.ru_id
      })).then(({ data: { data } }) => {
        if(data.length == 1){
          this.couponShow = true;
          this.couponInfo = data[0];
        }
      }).catch(err => {
        console.error(err)
      })
    }
  },
  computed: {
    ...mapState({
      shopInfo: state => state.visual.shopInfo,
    }),
    isLogin(){
      return localStorage.getItem('token') == null ? false : true
    },
    authority(){
      return window.apiAuthority
    }
  },
  methods: {
    notLogin(msg){
      Dialog.confirm({
        message:msg,
        className:'text-center'
      }).then(()=>{
        this.$router.push({
          path: '/login',
          query:{ redirect: {name:'shopHome',params:{ id:this.$route.params.id }}}
        })
      }).catch(()=>{

      })
    },
    sAllProductUrl(){
      if(this.authority == 'view'){
        this.$router.push({
          name:'shopGoodsList',
          query:{
            ru_id:this.$route.params.id
          }
        })
      }
    },
    sNewProductUrl(){
      if(this.authority == 'view'){
        this.$router.push({
          name:'shopGoodsList',
          query:{
            ru_id:this.$route.params.id,
            type:'store_new'
          }
        })
      }
    },
    sPromotePoductUrl(){
      if(this.authority == 'view'){
        this.$router.push({
          name:'shopGoodsList',
          query:{
            ru_id:this.$route.params.id,
            type:'is_promote'
          }
        })
      }
    },
    couponLink(cou_id){
      if(this.isLogin){
        this.$store.dispatch('setGoodsCouponReceive', {
            cou_id: cou_id
        }).then(({data:data}) => {
          Toast({
              message:data.msg,
              duration:1000,
          })

          this.couponShow = false
        })
      }else{
        let msg = this.$t('lang.login_user_not')
        this.notLogin(msg)
      }
    }
  },
  watch:{
    shopInfo(){
      document.title = this.shopInfo.rz_shop_name
  	  if(this.authority == 'view'){
    	  this.$wxShare.share({
    	    title:this.shopInfo.rz_shop_name,
    	    desc:this.shopInfo.shop_desc ? this.shopInfo.shop_desc : '',
    	    link:window.location.href,
    	    imgUrl:this.shopInfo.logo_thumb
    	  })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
@keyframes animatedBird {
    0% {
        top: 0;
    }
    50% {
        top: -100%;
    }
    100% {
        top: 0%;
    }
}
.shop-signs header {
    height: 11.2rem;
    overflow: hidden;
    position: relative;
}

.shop-signs header > img {
    width: 100%;
    height: auto;
}

.shop-signs .bg-img {
    position: absolute;
    animation: animatedBird 20s infinite linear;
}

.shop-signs header figure {
    position: absolute;
    z-index: 10;
    top: 50%;
    transform: translateY(-50%);
    -webkit-transform: translateY(-50%);
    margin-left: 1rem;
}

.shop-signs header figure h4 {
    margin-top: 1rem;
    font-size: 1.4rem;
}

.shop-signs header figure p {
    font-size: 1.1rem;
    margin-top: 0.5rem;
}

.shop-signs header figure img {
    width: 5.6rem;
    height: auto;
    display: block;
    float: left;
}

.shop-signs header figcaption {
    color: #fff;
    float: left;
    margin-left: 0.6rem;
}

.shop-signs header .heart {
    z-index: 10;
    background: $color;
    border-color: $color;
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    -webkit-transform: translateY(-50%);
    color: #fff;
    font-size: 1.2rem;
    height: 2.6rem;
    line-height: 2.6rem;
    padding: 0rem 0.8rem;
    border-radius: 1.2rem;
}

.shop-signs header .heart i {
    font-size: 1.3rem;
}

.shop-signs header .heart.active {
    border: 1px solid #fff;
    background: #fff;
    color: $color;
}

.shop-signs header .shop-signs-mask {
    background: rgba(0, 0, 0, 0.4);
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
}

.shop-signs .info-nums {
    background: #fff;
    position: relative;
    z-index: 99;

    .info-head{
      text-align: center;
      padding-top: 3.5rem;
      h4{
        position: absolute;
        width: 6rem;
        height: 6rem;
        background: #fff;
        overflow: hidden;
        border-radius: 50%;
        left: 50%;
        top: -3rem;
        margin-left: -3rem;
        @include shadow(1px,1px,10px,rgba(0,0,0,.2))

        .img-box{
          width: 100%;
        }
      }
      p{
        font-size: 1.4rem;
        color: #888888;
        em{
          margin: 0 .5rem;
          color: #333;
        }
      }
    }

    ul {
      @include disFlex()
      padding: 1rem 0;

      li{
        @include flex1-1()
        text-align: center;
        font-size: 1.4rem;
        color: #888888;
        position: relative;

        span{
          display: block;

          em{
            color: #333;
          }
        }

        a{
          @include urlAbsolute()
        }
      }
    }
}
.coupon-select {
    margin: 1rem;
    .li {
        margin-bottom: 1rem;
        .left {
            position: relative;
            padding: 1rem;
            h4,
            .name {
                color: #74d2d4
            }
            h4 {
                font-size: 1rem*2.6;

                em{
                    margin: .7rem 0 0;
                    font-family: verdana;
                    font-size: 1.8rem;
                }
            }
        }
        .right {
            width: 1rem*6;
            background: #74d2d4;
            color: #fff;
            padding: 1rem *3.6 0;
        }
    }
    .li.active {
        img.p-a {
            width: 1rem*6;
            height: 1rem * 6;
            right: 1rem*1.1;
            top: 50%;
            margin-top: -1rem*3
        }
        .left {
            position: relative;
            .coupon_cont,
            h4,
            div.color-3,
            .name {
                color: #ccc
            }
        }
        .right {
            background: #ccc;
        }

    }
}
.van-popup{ width: 80%; background: #f5f5f5;}
</style>
