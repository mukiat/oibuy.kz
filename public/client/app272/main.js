import Vue from 'vue'
import App from './App'

import store from './store'
import * as localConfig from './config/local/config'

import outerHref from '@/common/outerHref'
import request from '@/common/request.js'
import isLogin from '@/common/mixins/isLogin'
import universal from '@/common/mixins/universal'
import dateFormat from '@/common/mixins/date-format'
import dscLoading from '@/components/dsc-loading.vue'
import i18n from '@/common/lang/index.js'
import CurrencyPrice from '@/components/currency-price/CurrencyPrice.vue'

Vue.mixin(universal)
Vue.config.productionTip = false

Vue.component('currency-price',CurrencyPrice)

Vue.prototype.$outerHref = outerHref
Vue.prototype.$isLogin = isLogin
Vue.prototype.$http = request
Vue.prototype.$formatDateTime = dateFormat.formatDateTime
Vue.prototype.$getCurDate = dateFormat.getCurDate
Vue.prototype.$customBar = 88

Vue.prototype.$store = store

Vue.prototype.websiteUrl = localConfig.websiteUrl
Vue.prototype.$websiteUrl = localConfig.websiteUrl + '/mobile/#/'
Vue.prototype._i18n = i18n

//小程序直播插件appid
Vue.prototype.liveAppid = localConfig.liveAppid

//app升级后台生成的appid
Vue.prototype.updateAppid = localConfig.updateAppid

Vue.component('dsc-loading',dscLoading)

/**
 * Date 对象新增 format 原型
 */
Date.prototype.format = dateFormat.format

App.mpType = 'app'

const app = new Vue({
	store,
	i18n,
    ...App
})
app.$mount()
