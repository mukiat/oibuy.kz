import Vue from 'vue'
import axios from 'axios'
import qs from 'qs'
import App from './App.vue'
import router from './router'
import store from './store/index'
import bootstrap from './bootstrap'
import wxShare from './filters/wxShare'
import {setCookie, getCookie, delCookie} from './filters/cookie'
import i18n from './locales'
import {Base64} from 'js-base64'
import Exif from 'exif-js'
import CurrencyPrice from '@/components/currency-price'
import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue'
import isApp from './mixins/is-app'

Vue.prototype.$wxShare = wxShare
Vue.prototype.Exif = Exif
Vue.prototype.$cookieStore = {setCookie, getCookie, delCookie}

Vue.use(CurrencyPrice)
Vue.component('dsc-copyright', dscCopyright)

Vue.config.productionTip = false

import {
    Toast
} from 'vant'

/**
 * Vue对象新增 axios 原型
 */
Vue.prototype.$http = axios

let token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

//设置api提交header token
if(localStorage.getItem('token')){
    axios.defaults.headers.common['token'] = localStorage.getItem('token')
}

//设置api提交header 地区id
axios.defaults.headers.common['userRegion'] = Base64.encode(localStorage.getItem('userRegion')) || null

//request拦截器
axios.interceptors.request.use(request => {
    let client_hash = localStorage.getItem('client_hash') || ''
    request.headers['x-client-hash'] = client_hash

    return request
}, error => {
    return Promise.reject(error)
})

//token权限拦截器
axios.interceptors.response.use(response => {
    let client_hash = ''

    for (var k in response.headers) {
        if (k.toLowerCase() === "x-client-hash") {
            client_hash = response.headers[k]
        }
    }
    if (client_hash) {
        localStorage.setItem('client_hash', client_hash);
    }

    if (response.data.status === 'failed') {
        switch (response.data.errors.code) {
            case 12:
                Toast(response.data.errors.message);
                localStorage.removeItem('token');

                if(isApp.isWeixinBrowser()){
                    let url = location.origin + '/mobile/#' + router.currentRoute.fullPath;
                    router.replace({
                        name: 'login',
                        query: {
                            redirect: {
                                url:url
                            }
                        }
                    })
                }else{
                    router.replace({
                        name: 'login',
                        query: {redirect: router.currentRoute.fullPath}
                    })
                }
                break
            case 404:
                Toast("Дерек жоқ");

                router.replace({
                    name: 'home'
                })
                break
            case 502:
                Toast(response.data.errors.message);
                break
            case 506:
                Toast("Тиым салынған сөз бар")
                break
            case 102:
                Toast("Дерек қателігі，қайта кіріңіз");
                localStorage.removeItem('token');

                if(isApp.isWeixinBrowser()){
                    let url = location.origin + '/mobile/#' + route.currentRoute.fullPath;
                    router.replace({
                        name: 'login',
                        query: {
                            redirect: {
                                url:url
                            }
                        }
                    })
                }else{
                    router.replace({
                        name: 'login',
                        query: {redirect: router.currentRoute.fullPath}
                    })
                }
                break
            default:
        }
    }

    return response
}, error => {
    return Promise.reject(error) 
})

new Vue({
    router,
    store,
    i18n,
    render: h => h(App)
}).$mount('#app')
