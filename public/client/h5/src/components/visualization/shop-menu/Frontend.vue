<template>
    <footer class="shop-menu" :class="{'preview':preview}">
        <ul :class="{preview:preview}">
            <li>
                <div class="info" @click="sShopAboutUrl">
                  <i class="iconfont icon-dianpu1"></i>
                  <p>{{$t('lang.shop_details')}}</p>
                </div>
            </li>
            <li :class="{'active':bCategorySubmenu}" @click.stop="showCategory">
                <div class="info">
                  <i class="iconfont icon-fenlei1"></i>
                  <p>{{$t('lang.popular_category')}}</p>
                </div>
                <ul class="sub-menu">
                    <li v-for="(item,index) in aCategory" :key="index" @click="getCatUrl(item.cat_id)">
                        {{item.cat_name}}
                    </li>
                </ul>
            </li>
            <li class="kf">
                <a href="javascript:;" @click="onChat(0,ru_id)" v-if="authority == 'view'"></a>
                <div class="info">
                  <i class="iconfont icon-kefu"></i>
                  <p>{{$t('lang.customer_service')}}</p>
                </div>
            </li>
            <li class="sc" @click.stop="isFollow()">
              <div class="info">
                <i class="iconfont icon-shoucang" v-if="!bFollow"></i>
                <i class="iconfont icon-guanzhu" v-else></i>
                <span>{{ sFollow }}</span>
              </div>
            </li>
        </ul>
    </footer>
</template>

<script>
//node library
import qs from 'qs'

import {
  mapState
} from 'vuex'

import {
  Toast,
  Dialog,
} from 'vant'

//mixins
import formProcessing from '@/mixins/form-processing.js'

export default {
  name: 'shop-menu',
  props: ['data', 'preview'],
  mixins: [formProcessing],
  components: {
    [Toast.name]: Toast,
    [Dialog.name]: Dialog,
  },
  data() {
    return {
      bCategorySubmenu: false,
      aCategory: [],
      sKf:'',
      ru_id:this.$route ? this.$route.params.id : 0
    }
  },
  activated() {
    if(window.apiAuthority == 'view'){
      this.$http.post(`${window.ROOT_URL}api/visual/storedown`, qs.stringify({
        ru_id: this.$route.params.id
      })).then(({ data: { data } }) => {
        const {
          shop_category: shopCategory,
          shop_about: shopAbout,
          kf,
        } = data[0]
        this.sKf = kf
        this.aCategory = shopCategory.slice(0,9)
      }).catch(err => {
        console.error(err)
      })
    }
  },
  computed: {
    ...mapState({
      shopInfo: state => state.visual.shopInfo,
    }),
    bFollow(){
      return this.shopInfo.count_gaze == 1 ? true : false
    },
    sFollow(){
      return this.shopInfo.count_gaze != 1 ? this.$t('lang.attention') : this.$t('lang.followed')
    },
    isLogin(){
      return localStorage.getItem('token') == null ? false : true
    },
    authority(){
      return window.apiAuthority
    }
  },
  methods: {
    isFollow() {
      let that = this
      if (!that.preview && window.apiAuthority == 'view') {
        if(that.isLogin){
          that.$store.dispatch('stVisualAddcollect',{
            ru_id: that.ru_id
          })
        }else{
          let msg = this.$t('lang.fill_in_user_collect_shop')
          this.notLogin(msg)
        }
      }
    },
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
    getCatUrl(str) {
      if(this.authority == 'view'){
        this.$router.push({
          name:'shopGoodsList',
          query:{
            ru_id:this.$route.params.id,
            cat_id:str
          }
        })
      }
    },
    showCategory() {
      if(this.authority == 'view'){
        this.bCategorySubmenu = !this.bCategorySubmenu
      }
    },
    sShopAboutUrl(){
      if(this.authority == 'view'){
        this.$router.push({
          name:'shopDetail',
          params:{
            id:this.$route.params.id
          }
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.shop-menu {
    height: 4.4rem;
    ul {
        width: 100%;
        background: #fff;
        border-top: 1px solid $border-color-split;
        li {
            padding: .5rem 0;
            text-align: center;
            border-right: 1px solid $border-color-split;
            font-size: 1.3rem;
            position: relative;
            background: #fff;
            @include flex1-1();
            @include direction(center,center);
            a {
                @include urlAbsolute();
            }
            i.iconfont {
                margin-right: .2rem;
                font-size: 1.8rem;
            }
        }
        li.active {
            .sub-menu {
                transform: translateY(0);
                -ms-transform:translateY(0);   /* IE 9 */
                -moz-transform:translateY(0);  /* Firefox */
                -webkit-transform:translateY(0); /* Safari 和 Chrome */
                -o-transform:translateY(0);  /* Opera */
                /*visibility: visible;
                opacity: 1;*/
                display: block;
                @include transition(all, .2s);
            }
        }

        li.sc{
          background: #f92028;
          color: #fff;
          font-size: 1.5rem;

          .iconfont{
            vertical-align: middle;
            margin-right: 5px;
          }

          .icon-guanzhu{
            font-size: 1.5rem
          }
        }
    }
}

.shop-menu.preview,
.shop-menu ul.preview {
    position: absolute;
    bottom: 0;
    width: 100%;
}

.shop-menu>ul {
    z-index: 10;
    bottom: 0;
    position: fixed;
    @include disFlex();
    .kf a{
        padding:1rem 0;
        font-size:1.46rem;
        color:$text-color;
    }
}

.sub-menu {
    position: absolute;
    bottom: 100%;
    left: 0;
    transform: translateY(100%);
    -ms-transform:translateY(100%);   /* IE 9 */
    -moz-transform:translateY(100%);  /* Firefox */
    -webkit-transform:translateY(100%); /* Safari 和 Chrome */
    -o-transform:translateY(100%);  /* Opera */
    /*visibility: hidden;
    opacity: 0;*/
    @include transition(all, .2s);
    width: 100%;
    /*z-index: -1;*/
    display: none;
    float: inherit;
    padding: 0;
    border-left: 1px solid $border-color-split;
    li {
        font-size: 1.36rem;
        padding: 1rem 0;
        border: none;
        position: relative;
        border-bottom: 1px solid $border-color-split;
        a {
            color: $text-color;
        }

    }

}
</style>
