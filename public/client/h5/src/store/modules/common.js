import { apiCommon } from '@/config'

import {
  SHOP_CONFIG
} from '../mutation-type.js'

const state = {

}

const mutations = {

}

const actions = {
  //获取shopconfig
  setShopConfig({ commit },o){
    if(o && o.type){
      return apiCommon.getShopConfig()
    }else{
      apiCommon.getShopConfig().then(res=>{
        commit(SHOP_CONFIG,{res,o})
      })
    }
  },
}

export default {
    state,
    mutations,
    actions,
}