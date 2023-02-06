import Vue from 'vue'
import Vuex from 'vuex'
import actions from './actions'
import mutations from './mutations'
import getters from './getters'
import dashboard from './modules/dashboard'
import dialogUrl from './modules/dialog-url'
import dialogPicture from './modules/dialog-picture'
import dialogGoods from './modules/dialog-goods'

import user from './modules/user'
import category from './modules/category'
import goods from './modules/goods'
import shopping from './modules/shopping'
import shop from './modules/shop'
import brand from './modules/brand'

import drp from './modules/drp'
import team from './modules/team'
import bargain from './modules/bargain'
import auction from './modules/auction'
import activity from './modules/activity'
import other from './modules/other'
import crowdfunding from './modules/crowdfunding'
import article from './modules/article'
import discover from './modules/discover'
import common from './modules/common'

import visual from './modules/visual'

import custom from './modules/custom'

//语言包
import i18n from '@/locales'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production' //发布模式不使用严格模式
const store = new Vuex.Store({
    //strict: debug,
    state: {
        region:{
            data:{
                provinceData:[],
                cityDate:[],
                districtDate:[],
                streetDate:[]
            },
            id:1,
            level:1,
            status:false
        },
        userRegion:{},
        imgVerify:{
            captcha:'',
            client:''
        },
        bonusData:{},
        modules: [],
        modulesShop:[],
        modulesTopic:[],
        pageList: [],
        customPageList: [],
        pageSetting: {
            ruid: -1,
            modulesId: -1,
            type: "",
            default: "",
            title:""
        },
        shopInfo: {
            searchStoreData: {
                allValue: {
                    searchValue: "",
                    bgColor: "",
                    tenKey: ""
                },
                isSuspendSel: "0",
                isPositionSel: "0",
                isLogoSel: "1",
                isMessageSel: "0"
            },
            lineData: {
                allValue: {
                    height: 4,
                    bgColor: ""
                }
            },
            titleData: {
                allValue: {
                    title: i18n.t('lang.product_list_alt'),
                    fitTitle: "",
                    dateTime: "",
                    author: "",
                    bgColor: ""
                },
                list: [
                    {   
                        url: ``,
                        urlCatetory: "",
                        urlName: "",
                        desc: i18n.t('lang.more')
                    }
                ],
                isDate: "0",
                isStyleSel: "2",
                isPositionSel: "0"
            },
            productData: {
                allValue: {
                    number: 10,
                    scrollNumber: 3,
                    selectGoodsId:[],
                    categorySOption:'',
                    brandSelect:''
                },
                isStyleSel: "0",
                isSizeSel: "1",
                tagSelList: [
                    "0",
                    "1",
                    "2"
                ],
                isModuleSel: "0"
            }
        },
        supplierLoading:false,
        position:0,
        topCategoryCatid:0,
        scrollPickOpen:false
    },
    actions,
    mutations,
    modules: {
        namespaced: true,
        dashboard,
        dialogUrl,
        dialogPicture,
        dialogGoods,
        user,
        category,
        goods,
        shopping,
        shop,
        brand,
        drp,
        team,
        bargain,
        auction,
        activity,
        other,
        crowdfunding,
        article,
        discover,
        common,
        visual,
        custom
    }
})


let aHot = [
    './actions',
    './mutations',
    './getters',
    './modules/dashboard',
    './modules/dialog-url',
    './modules/dialog-picture',
    './modules/dialog-goods',
    './modules/user',
    './modules/category',
    './modules/goods',
    './modules/shopping',
    './modules/shop',
    './modules/brand',
]


if (module.hot) {
    // 使 actions 和 mutations 成为可热重载模块
    module.hot.accept(aHot, () => {
      // 加载新模块 
      store.hotUpdate({
        actions: require('./actions'),
        mutations: require('./mutations'),
        getters: require('./getters'),
        modules: {
          dashboard:require('./modules/dashboard'),
          dialogUrl:require('./modules/dialog-url'),
          dialogPicture:require('./modules/dialog-picture'),
          dialogGoods:require('./modules/dialog-goods'),
        }
      })
    })
  }

  export default store