import {
	SHOP_CAT_LIST,
	SHOP_LIST,
	SHOP_DETAIL,
	SHOP_GOODS_LIST,
	GET_HOME_INFO,
	VISUAL_STOREIN,
	VISUAL_ADD_COLLECT
} from '../mutation-type.js'

import { apiShop } from '@/config'

const state = {
	shopCatList:[],
	shopList:[],
	shopDetail:Object,
	shopGoodsList:[],
	modules:[],
	modulesShop:[],
	modulesTopic:[],
	shopInfo:{}
}

const mutations = {
	[GET_HOME_INFO](state,{res,o}){
		if(o.type == 'home'){
			state.modules = res.data
		}else if(o.type == 'store'){
			state.modulesShop = res.data
		}
	},
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
			}
		}
	},
	[SHOP_DETAIL](state,o){
		state.shopDetail = o.data
	},
	[SHOP_GOODS_LIST](state,{o,res}){
		if(o.page == 1){
			state.shopGoodsList = res.data
		}else{
			if(res.data.length != 0){
				for(let i= 0;i<res.data.length;i++){
					state.shopGoodsList.push(res.data[i])
				}
			}
		}
	},
	[VISUAL_STOREIN](state,o){
		state.shopInfo = o.data[0]
	},
	[VISUAL_ADD_COLLECT](state,o){
		if(o.data.count_gaze == 1){
			uni.showToast({ title:'已关注',icon:'none' })
		}else{
			uni.showToast({ title:'取消关注',icon:'none' })
		}
		
		state.shopInfo.count_gaze = o.data.count_gaze
		state.shopInfo.like_num = o.data.count_gaze == 1 ? state.shopInfo.like_num + 1 : state.shopInfo.like_num - 1
	}
}

const actions = {
	setHome({ commit }, o){
		if(o.type){
			apiShop.getHome(o).then(res =>{
				commit(GET_HOME_INFO,{res,o})
			})
		}else{
			return apiShop.getHome(o)
		}
	},
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
	getShopDetailById({ commit }, o){
		return apiShop.getShopDetail(o)
	},
	setShopGoodsList({ commit }, o){
		apiShop.getShopGoodsList(o).then(res => {
			commit(SHOP_GOODS_LIST,{o,res})
		})
	},
	getShopGoodsListById({ commit }, o){
		return apiShop.getShopGoodsList(o)
	},
	setShopMap({ commit }, o){
		return apiShop.getShopMap(o)
	},
	setVisualStorein({commit},o){
		apiShop.getVisualStorein(o).then(res=>{
			commit(VISUAL_STOREIN, res)
		})
	},
	stVisualAddcollect({commit},o){
		apiShop.getVisualAddcollect(o).then(res=>{
			commit(VISUAL_ADD_COLLECT, res)
		})
	},
}

export default {
	state,
	mutations,
	actions
}