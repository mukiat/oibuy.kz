<template>
  <div class='home'>
    <ec-search :preview="false" :data="searchStoreData" :shop-id="shop_id"></ec-search>
    <ec-shop-signs :preview="false"></ec-shop-signs>
    <ec-line :preview="false" :data="lineData"></ec-line>
    <component v-for="(item,index) in modules" :key="index" :is="'ec-'+item.module" :data="item.data" :preview="false" :modules-index="index" :shop-id="shop_id">
      {{ item }}
    </component>
    <ec-shop-menu :preview="false"></ec-shop-menu>
    <CommonNav></CommonNav>
  </div>
</template>

<script>
//node library
import url from 'url'
import qs from 'qs'

// mapActions mapState
import {
  mapActions,
  mapState
} from 'vuex'

// custom components
import EcSlide from '@/components/visualization/slide/Frontend'
import EcTitle from '@/components/visualization/title/Frontend'
import EcAnnouncement from '@/components/visualization/announcement/Frontend'
import EcNav from '@/components/visualization/nav/Frontend'
import EcLine from '@/components/visualization/line/Frontend'
import EcBlank from '@/components/visualization/blank/Frontend'
import EcJigsaw from '@/components/visualization/jigsaw/Frontend'
import EcProduct from '@/components/visualization/product/Frontend'
import EcCoupon from '@/components/visualization/coupon/Frontend'
import EcCountDown from '@/components/visualization/count-down/Frontend'
import EcButton from '@/components/visualization/button/Frontend'
import EcSearch from '@/components/visualization/search/Frontend'
import EcStore from '@/components/visualization/store/Frontend'
import EcShopSigns from '@/components/visualization/shop-signs/Frontend'
import EcShopMenu from '@/components/visualization/shop-menu/Frontend'
import EcTabDown from '@/components/visualization/tab-down/Frontend'
import EcFilterTop from '@/components/visualization/element/FilterTop'
import AppDown from '@/components/visualization/element/AppDown'
import CommonNav from '@/components/CommonNav'

// third party components
import {
  Button
} from 'element-ui'

import {
  Toast,
  Popup
} from 'vant'

//data-conversion
import { conversion } from '@/assets/js/data-conversion'
import { log } from 'util'

export default {
  name: 'home',
  components: {
    "EcButton": Button,
    EcSlide,
    EcTitle,
    EcAnnouncement,
    EcNav,
    EcLine,
    EcBlank,
    EcJigsaw,
    EcProduct,
    EcCoupon,
    EcCountDown,
    EcButton,
    EcSearch,
    EcStore,
    EcShopSigns,
    EcShopMenu,
    EcTabDown,
    EcFilterTop,
    AppDown,
    CommonNav,
    [Popup.name]: Popup,
  },
  data() {
    return {
      share:'',
      shop_title:'',
      shop_id:this.$route.params.id,
      scrollTop: 0,
      docTitle: ''
    }
  },
  created() {
    this.init(this.shop_id)
  },
  activated () {
    if (this.docTitle) {
      document.title = this.docTitle
    }

    if (this.scrollTop > 0) {
      window.scrollTo({
        top: this.scrollTop,
        behavior: "instant"
      })
    }
    var passiveSupported = false;

		try {
			var options = Object.defineProperty({}, "passive", {
				get: function() {
				 passiveSupported = true;
				}
			});

			window.addEventListener('scroll', this.handleScroll, passiveSupported ? { passive: true } : false);
		} catch(err) {
			window.addEventListener('scroll', this.handleScroll);
		}
  },
  deactivated () {
      window.removeEventListener('scroll', this.handleScroll);
  },
  beforeRouteLeave (to, from, next) {
    this.docTitle = document.title;
    next()
  },
  methods: {
    handleScroll(e) {
			this.scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
		},
    init(id){
      this.getModule({
        ru_id:id,
        type:'store',
        device: window.shopInfo.device  // device 设备  h5 app wxapp
      })
    },
    setModulesType(){
      let newModulesType = {
        type:window.shopInfo.type,
        name:'store'
      }

      localStorage.setItem('modulesType',JSON.stringify(newModulesType))
    },
    getModule(o) {
      this.modules = []
      this.$http.post(`${window.ROOT_URL}api/visual/default`, qs.stringify(o)).then(({ data }) => {
        if(data.data){
          this.$store.dispatch('setModuleInfo', {
            id: data.data,
            type:o.type,
            device: window.shopInfo.device  // device 设备  h5 app wxapp
          })
        }else{
          Toast({
            message:data.errors.message,
            duration:1000
          });
          this.$router.push({
            name:'home'
          })
        }
      })
    }
  },
  computed: {
    ...mapState({
      searchStoreData: state => state.shopInfo.searchStoreData,
      lineData: state => state.shopInfo.lineData,
      titleData: state => state.shopInfo.titleData,
      productData: state => state.shopInfo.productData,
    }),
    modules:{
      get(){
        return this.$store.state.modulesShop
      },
      set(val){
        this.$store.state.modulesShop = val
      }
    }
  },
  watch: {
    '$route'(to, from) {
      if(to.name == 'shopHome' && to.params.id != this.shop_id){
        this.init(to.params.id)
        this.shop_id = to.params.id
      }
    },
  }
}

</script>
<style lang="scss" scoped>
</style>
