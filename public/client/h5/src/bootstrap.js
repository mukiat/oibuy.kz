import Vue from 'vue'
import { localUrl } from './config'
import * as global from './directives/global'
import seamlessScroll from './directives/seamlessScroll'

import colorUpdate from '@/mixins/color-update'
import arrRemove from '@/mixins/arr-remove'
import dateFormat from '@/mixins/date-format'

//axios
import axios from 'axios'

// image lazy load
import VueLazyload from 'vue-lazyload'

/**
 * 未发布状态下，引用本地服务器地址
 */
process.env.NODE_ENV !== 'production' ? Vue.prototype.release = false : Vue.prototype.release = true

/**
 * Directives
 */
Vue.directive('href', global.href)
Vue.directive('box-product-w', global.boxProductW)
Vue.directive('seamless-scroll', seamlessScroll)

if (!Vue.prototype.release) {
  window.ROOT_URL = ''
}

/**
 * 颜色进制转换添加到 String 原型中
 */
String.prototype.colorRgb = colorUpdate.colorRgb

/*
* 去除空格
*/
String.prototype.trims = function () {
  if (this != "" || !this) {
    return this.replace(/\s+/g, "")
  }
}


/**
 * 数组对象新增 remove 原型
 */
Array.prototype.remove = arrRemove.remove

/**
 * Date 对象新增 format 原型
 */
Date.prototype.format = dateFormat.format


//页面刷新清空已有的sessionStorage aPicture
if (!localStorage.opened && localStorage.getItem('aPicture')) {
  localStorage.removeItem('aPicture')
}

if (window.shopInfo.authority) {
  window.apiAuthority = "view"
} else if (window.shopInfo.ruid != 0) {
  window.apiAuthority = "seller"
} else {
  window.apiAuthority = "admin"
}

Vue.use(VueLazyload,{
  preLoad:1.3,
  error:Vue.prototype.release ? `${window.ROOT_URL}/img/default-img.fe203b8.jpg` : require('./assets/img/default-img.jpg'),
  loading:Vue.prototype.release ? `${window.ROOT_URL}/img/default-img.fe203b8.jpg` : require('./assets/img/default-img.jpg'),
  attempt:1,
  filter:{
    webp(listener,options){
      if(!options.supportWebp) return
      listener.src += '?imageView2/2/format/webp'
    }
  }
})