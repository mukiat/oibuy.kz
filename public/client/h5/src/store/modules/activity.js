import {
    SET_ACTIVITY_INDEX,
    SET_ACTIVITY_SHOW,
    SET_ACTIVITY_GOODS,
    SET_ACTIVITY_COUDAN,
} from '../mutation-type.js'

import {
    apiActivity
} from '@/config'

const state = {
    status: '',
    activityIndexData:[],
    activityShowsData:Object,
    activityGoodsData:[],
    activityCoudanData:[]
}
const mutations = {
    [SET_ACTIVITY_INDEX](state, o) {
        state.activityIndexData = o.data;
    },
    [SET_ACTIVITY_SHOW](state, o) {
        state.activityShowsData = o.data;
    },
    [SET_ACTIVITY_GOODS](state, o) {
        if(o.page == 1){
            state.activityGoodsData = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.activityGoodsData.push(o.data[i])
                }
            }else{
                state.activityGoodsData.push('')
            }
        }
    },
    [SET_ACTIVITY_COUDAN](state, o) {
        state.activityCoudanData = o.data;
    }
}
const actions = {
    setActivityIndex({commit}, o) {
        apiActivity.activityIndex(o).then(res => {   
            commit(SET_ACTIVITY_INDEX, res)
        })
    },
    setActivityShow({commit}, o) {
        apiActivity.activityShow(o).then(res => {   
            commit(SET_ACTIVITY_SHOW, res)
        })
    },
    setActivityGoods({commit}, o) {
        apiActivity.activityGoods(o).then(res => {   
            commit(SET_ACTIVITY_GOODS, res)
        })
    },
    setActivityCoudan({commit}, o) {
        apiActivity.activityCoudan(o).then(res => {   
            commit(SET_ACTIVITY_COUDAN, res)
        })
    }
}
export default {
    state,
    mutations,
    actions,
}
