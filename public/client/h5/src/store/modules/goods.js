import {
	SET_GOODS_INFO,
	SET_GOODSATTR_OPER,
	GOODS_COUPON_LIST,
	GOODS_COMMENT,
	FITTING_LIST,
	FITTING_PRICE_DATA
} from '../mutation-type.js'

import { apiGoods } from '@/config'

const state = {
	goodsInfo:Object,
	goodsAttrInit:[],
	goodsAttrOper:Object,
	goodsCouponList:[],
	goodsComment:[],
	fittingInfo:{
		comboTab:[],
		fittings:[],
		goods:Object
	},
	fittingPriceData:Object
}

const mutations = {
	[SET_GOODS_INFO](state,o){
		state.goodsInfo = o

		state.goodsAttrInit = []
		state.goodsAttrOper = ''

		for(let i = 0; i < state.goodsInfo.attr.length; i++){
			for(let j = 0; j < state.goodsInfo.attr[i].attr_key.length;j++){
				if(state.goodsInfo.attr[i].attr_key[j].attr_checked == 1){
					state.goodsAttrInit.push(state.goodsInfo.attr[i].attr_key[j].goods_attr_id)
				}
			}
		}
	},
	[GOODS_COUPON_LIST](state,o){
		state.goodsCouponList = o.data
	},
	[SET_GOODSATTR_OPER](state,o){
		state.goodsAttrOper = o.data
	},
	[GOODS_COMMENT](state,o){
		if(o.page == 1){
    		state.goodsComment = o.data;
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.goodsComment.push(o.data[i])
				}
			}else{
				state.goodsComment.push('')
			}
		}
	},
	[FITTING_LIST](state,o){
		state.fittingInfo = o.data
	},
	[FITTING_PRICE_DATA](state,o){
		state.fittingPriceData = o.data
	}
}

const actions = {
	setGoodsInfo({ commit }, o){
		apiGoods.getGoodsInfo(o).then(res => {
			commit(SET_GOODS_INFO,res)
		})
	},
	setGoodsCouponList({ commit }, o){
		apiGoods.getGoodsCouponList(o).then(res =>{
			commit(GOODS_COUPON_LIST,res)
		})
	},
	setGoodsCouponReceive({ commit }, o){
		return apiGoods.getGoodsCouponReceive(o)
	},
	setGoodsAttrOper({ commit }, o){
		apiGoods.getGoodsAttrOper(o).then(res =>{
			commit(SET_GOODSATTR_OPER,res)
		})
	},
	setGoodsComment({ commit }, o){
		apiGoods.getGoodsComment(o).then(res =>{
			commit(GOODS_COMMENT,res)
		})
	},
	getGoodsCommentById({ commit }, o){
		return apiGoods.getGoodsComment(o)
	},
	setGoodsShare({ commit }, o){
		return apiGoods.getGoodsShare(o)
	},
	setFitting({ commit }, o){
		apiGoods.getFitting(o).then(res => {
			commit(FITTING_LIST,res)
		})
	},
	setFittingPrice({ commit }, o){
		apiGoods.getFittingPrice(o).then(res =>{
			commit(FITTING_PRICE_DATA,res)
		})
	},
	setAddToCartCombo({ commit }, o){
		return apiGoods.getAddToCartCombo(o)
	},
	setDelInCartCombo({ commit }, o){
		return apiGoods.getDelInCartCombo(o)
	},
	setAddToCartGroup({ commit }, o){
		return apiGoods.getAddToCartGroup(o)
	}
}

export default {
	state,
	mutations,
	actions
}