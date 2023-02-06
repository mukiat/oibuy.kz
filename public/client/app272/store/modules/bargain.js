import {
    SET_BARGAIN_INDEX,
    SET_BARGAIN_GOODS,
    SET_BARGAIN_DETAIL,
    SET_BARGAIN_PROPERTY,
    SET_BARGAIN_MYBUY
} from '../mutation-type.js'

import {
    apiBargain
} from '@/config'

const state = {
    status: '',
    bargainIndexData:[],
    bargainGoodsData:[],
    page:1,
	size:10,
    bargainDetailData:{
       goods_info:[],
       bargain_info:[]
    },
    goodsAttrInit:[],
    goodsAttrOper:[],
    addBargain:Object,
    bargainJoin:0,
    bargainBidData:[],
    bargainMyBuyData:[],
}
const mutations = {
    [SET_BARGAIN_INDEX](state, o) {
        state.bargainIndexData = o.data.banner;
    },
    [SET_BARGAIN_GOODS](state, o) {
        if(o.page == 1){
            state.bargainGoodsData = o.data;
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.bargainGoodsData.push(o.data[i])
    			}
            }
		}
    },
    [SET_BARGAIN_DETAIL](state, o) {
        state.bargainDetailData = o.data

        state.goodsAttrInit = []

		for(let i = 0; i < state.bargainDetailData.goods_properties.length; i++){
			for(let j = 0; j < state.bargainDetailData.goods_properties[i].attr_key.length;j++){
				if(state.bargainDetailData.goods_properties[i].attr_key[j].attr_checked == 1){
					state.goodsAttrInit.push(state.bargainDetailData.goods_properties[i].attr_key[j].goods_attr_id)
				}
			}
        }

        state.addBargain =  o.data.goods_info.add_bargain
        state.bargainJoin = o.data.goods_info.bargain_join
    },
    [SET_BARGAIN_PROPERTY](state, o) {
        state.goodsAttrOper = o.data;
    },
    [SET_BARGAIN_MYBUY](state, o) {
        if(o.page == 1){
            state.bargainMyBuyData = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.bargainMyBuyData.push(o.data[i])
                }
            }
        }
    },
}
const actions = {
    setBargainIndex({commit}, o) {
        apiBargain.bargainIndexs(o).then(res => {   
            commit(SET_BARGAIN_INDEX, res)
        })
    },
    setBargainGoods({
        commit
    }, o) {
        apiBargain.bargainGoods(o).then(res => {
            commit(SET_BARGAIN_GOODS, res)
        })
    },
    //砍价详情
    setBargainDetail({
        commit
    }, o) {
        apiBargain.bargainDetail(o).then(res => {
            commit(SET_BARGAIN_DETAIL, res)
        })
    },
    //砍价属性
    setBargainProperty({
        commit
    }, o) {
        apiBargain.bargainProperty(o).then(res => {
            commit(SET_BARGAIN_PROPERTY, res)
        })
    },
    //参与砍价
    setBargainLog({ commit }, o) {
        return apiBargain.bargainLog(o)
    },
    //去砍价
    setBargainBid({ commit }, o) {
        return apiBargain.bargainBid(o)
    },
    //购买
    setBargainBuy({ commit }, o) {
        return apiBargain.bargainBuy(o)
    },
    //我参与的砍价
    setBargainMyBuy({
        commit
    }, o) {
        apiBargain.bargainMyBuy(o).then(res => {
            commit(SET_BARGAIN_MYBUY, res)
        })
    },

}
export default {
    state,
    mutations,
    actions,
}
