// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import axios from 'axios'
import App from './App'
// import router from '../router/router'
import bootstrap from '../bootstrap'
import store from '../store/index'
import i18n from '../locales'
import CurrencyPrice from '@/components/currency-price'

/**
 * Vue对象新增 axios 原型
 */
Vue.prototype.$http = axios

Vue.use(CurrencyPrice)

let token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

new Vue({
  // router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
