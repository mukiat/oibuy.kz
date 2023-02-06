import {
	SHOP_CAT_LIST,
	SHOP_LIST,
	SHOP_DETAIL,
	SHOP_GOODS_LIST
} from '../mutation-type.js'

import { apiShop } from '@/config'

const state = {
	shopCatList:[],
	shopList:[],
	shopDetail:Object,
	shopGoodsList:[]
}

const mutations = {
	[SHOP_CAT_LIST](state,o){
		state.shopCatList = o.data
	},
	[SHOP_LIST](state,o){
		if(o.page == 1){
			state.shopList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.shopList.push(o.data[i])
				}
			}else{
				state.shopList.push('')		
			}
		}
	},
	[SHOP_DETAIL](state,o){
		state.shopDetail = o.data
	},
	[SHOP_GOODS_LIST](state,o){
		if(o.page == 1){
			state.shopGoodsList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.shopGoodsList.push(o.data[i])
				}
			}else{
				state.shopGoodsList.push('')		
			}
		}
	}
}

const actions = {
	setShopCatList({ commit }, o){
		apiShop.getShopCatList(o).then(res => {
			commit(SHOP_CAT_LIST,res)
		})
	},
	setShopList({ commit }, o){
		apiShop.getShopList(o).then(res => {
			commit(SHOP_LIST,res)
		})
	},
	setShopDetail({ commit }, o){
		apiShop.getShopDetail(o).then(res => {
			commit(SHOP_DETAIL,res)
		})
	},
	setShopGoodsList({ commit }, o){
		apiShop.getShopGoodsList(o).then(res => {
			commit(SHOP_GOODS_LIST,res)
		})
	},
	setShopMap({ commit }, o){
		return apiShop.getShopMap(o)
	}
}

export default {
	state,
	mutations,
	actions
}