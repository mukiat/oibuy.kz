import {
    SET_CROWD_INDEX,
    SET_CROWD_GOODS,
    SET_CROWD_SHOW,
    SET_CROWD_PROPERTY,
    SET_CROWD_CHECKOUT,
    SET_CROWD_DONE,
    SET_CROWD_ORDER,
    SET_CROWD_DETAIL,
    SET_CROWD_USER,
    SET_CROWD_BUY,
    SET_CROWD_FOCUS,
    SET_CROWD_MY_FOCUS,
    SET_CROWD_TOPIC,
    SET_CROWD_TOPIC_LIST,
    SET_CROWD_BEST,
    SET_CROWD_PROPERTIES
} from '../mutation-type.js'

import {
    apiCrowdfunding
} from '@/config'

const state = {
    status: '',
    page:1,
	size:6,
    crowdIndexData:[],
    crowdGoodsData:[],
    crowdShowData:{
        info:Object,
        user_info:Object,
        goods:{
            goods_arr:[]
        }
    },
    crowdPropertyData:Object,
    crowdCheckoutData:'',
    crowdOrderData:[],
    crowdDetailData:[],
    crowdUserData:[],
    crowdBuyData:[],
    crowdFocusData:[],
    crowdMyFocusData:[],
    crowdTopicData:[],
    crowdTopicListData:[],
    crowdBestData:Object,
    crowdProoertiesData:{
        risk_instruction:Object
    },
    crowdCollectStatue:null,
}
const mutations = {
    [SET_CROWD_INDEX](state, o) {
        state.crowdIndexData = o.data;
    },
    [SET_CROWD_GOODS](state, o) {
        if(o.page == 1){
            state.crowdGoodsData = o.data;
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.crowdGoodsData.push(o.data[i])
    			}
            }else{
                state.crowdGoodsData.push('')
            }
		}
    },
    [SET_CROWD_SHOW](state, o) {
        state.crowdShowData = o.data;
    },
    [SET_CROWD_PROPERTY](state, o) {
        state.crowdPropertyData = o.data;
    },
    [SET_CROWD_CHECKOUT](state, o) {
        state.crowdCheckoutData = o.data;
    },
    [SET_CROWD_ORDER](state, o) {
        if(o.page == 1){
             state.crowdOrderData = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.crowdOrderData.push(o.data[i])
                }
            }else{
                state.crowdOrderData.push('')
            }
        }
    },
    [SET_CROWD_DETAIL](state, o) {
        state.crowdDetailData = o.data
    },
    [SET_CROWD_USER](state, o) {
        state.crowdUserData = o.data
    },
    [SET_CROWD_BUY](state, o) {
        state.crowdBuyData = o.data
    },
    [SET_CROWD_FOCUS](state, o) {
        state.crowdFocusData = o.data
        state.crowdCollectStatue = o.status
    },
    [SET_CROWD_MY_FOCUS](state, o) {
        if(o.page == 1){
             state.crowdMyFocusData = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.crowdMyFocusData.push(o.data[i])
                }
            }else{
                state.crowdMyFocusData.push('')
            }
        }
    },
    [SET_CROWD_TOPIC](state, o) {
        state.crowdTopicData = o.data
    },   
    [SET_CROWD_TOPIC_LIST](state, o) {
        if(o.page == 1){
            state.crowdTopicListData = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.crowdTopicListData.push(o.data[i])
                }
            }else{
                state.crowdTopicListData.push('')
            }
        }
    }, 
    [SET_CROWD_BEST](state, o) {
        if(o.page == 1){
            state.crowdBestData = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.crowdBestData.push(o.data[i])
                }
            }else{
                state.crowdBestData.push('')
            }
        }
    }, 
    [SET_CROWD_PROPERTIES](state, o) {
        state.crowdProoertiesData = o.data;
    }
}
const actions = {
    //众筹首页
    setCrowdfunding({commit}, o) {
        apiCrowdfunding.getCrowdfunding(o).then(res => {   
            commit(SET_CROWD_INDEX, res)
        })
    },
    //众筹列表
    setCrowdfundingGoods({commit}, o) {
        apiCrowdfunding.getCrowdfundingGoods(o).then(res => {   
            commit(SET_CROWD_GOODS, res)
        })
    },
    //众筹详情
    setCrowdfundingShow({commit}, o) {
        apiCrowdfunding.getCrowdfundingShow(o).then(res => {   
            commit(SET_CROWD_SHOW, res)
        })
    },
    //众筹属性
    setCrowdfundingProperty({commit}, o) {
        apiCrowdfunding.getCrowdfundingProperty(o).then(res => {   
            commit(SET_CROWD_PROPERTY, res)
        })
    },
    //众筹提交
    setCrowdfundingCheckout({commit}, o) {
        apiCrowdfunding.getCrowdfundingCheckout(o).then(res => {   
            commit(SET_CROWD_CHECKOUT, res)
        })
    },
    //众筹完成
    setCrowdfundingDone({commit}, o) {
        return apiCrowdfunding.getCrowdfundingDone(o)
    },
    //众筹订单
    setCrowdfundingOrder({commit}, o) {
        apiCrowdfunding.getCrowdfundingOrder(o).then(res => {   
            commit(SET_CROWD_ORDER, res)
        })
    },
    //众筹订单详情
     setCrowdfundingDetail({commit}, o) {
        apiCrowdfunding.getCrowdfundingDetail(o).then(res => {   
            commit(SET_CROWD_DETAIL, res)
        })
    },
    //众筹中心
    setCrowdfundingUser({commit}, o) {
        apiCrowdfunding.getCrowdfundingUser(o).then(res => {   
            commit(SET_CROWD_USER, res)
        })
    },
    //微筹中心-我的支持
    setCrowdfundingBuy({commit}, o) {
        apiCrowdfunding.getCrowdfundingBuy(o).then(res => {   
            commit(SET_CROWD_BUY, res)
        })
    },
    //微筹中心-添加关注
    setCrowdfundingFocus({commit}, o) {
        apiCrowdfunding.getCrowdfundingFocus(o).then(res => {   
            commit(SET_CROWD_FOCUS, res)
        })
    },
    //微筹中心-我的关注
    setCrowdfundingMyFocus({commit}, o) {
        apiCrowdfunding.getCrowdfundingMyFocus(o).then(res => {   
            commit(SET_CROWD_MY_FOCUS, res)
        })
    },
    //话题评论提交表单
    setCrowdfundingMyTopic({commit}, o) {
        return apiCrowdfunding.getCrowdfundingTopic(o)
    },
    //话题列表
    setCrowdfundingMyTopicList({commit}, o) {
        apiCrowdfunding.getCrowdfundingMyFocusList(o).then(res => {   
            commit(SET_CROWD_TOPIC_LIST, res)
        })
    },
    //
    setCrowdfundingBest({commit}, o) {
        apiCrowdfunding.getCrowdfundingBest(o).then(res => {   
            commit(SET_CROWD_BEST, res)
        })
    },
    //详情-风险-主页
    setCrowdfundingProperties({commit}, o) {
        apiCrowdfunding.getCrowdfundingProperties(o).then(res => {   
            commit(SET_CROWD_PROPERTIES, res)
        })
    }, 
}

export default {
    state,
    mutations,
    actions,
}
