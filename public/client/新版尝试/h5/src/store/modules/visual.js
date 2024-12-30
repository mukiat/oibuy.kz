import {
    VISUAL_STOREIN,
    VISUAL_ADD_COLLECT
} from '../mutation-type.js'

import {
    apiVisual
} from '@/config'

import { Toast } from 'vant'

import i18n from '@/locales'

const state = {
    shopInfo:{}
}

const mutations = {
    [VISUAL_STOREIN](state,o){
        state.shopInfo = o.data[0]
    },
    [VISUAL_ADD_COLLECT](state,o){
        if(o.data.count_gaze == 1){
            Toast(i18n.t('lang.followed'))
        }else{
            Toast(i18n.t('lang.cancel_collection'))
        }
        state.shopInfo.count_gaze = o.data.count_gaze
        state.shopInfo.like_num = o.data.count_gaze == 1 ? state.shopInfo.like_num + 1 : state.shopInfo.like_num - 1
    }
}

const actions = {
    setVisualStorein({commit},o){
        apiVisual.getVisualStorein(o).then(res=>{
            commit(VISUAL_STOREIN, res)
        })
    },
    stVisualAddcollect({commit},o){
        apiVisual.getVisualAddcollect(o).then(res=>{
            commit(VISUAL_ADD_COLLECT, res)
        })
    },
}

export default {
    state,
    mutations,
    actions
}