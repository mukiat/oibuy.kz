import {
	SET_CATEGORY_INFO,
	SET_CATEGORY_LIST,
	SET_GOODS_LIST
} from '../mutation-type.js'

import { apiCategory } from '@/config'

const state = {
	cateListAll: [],
	cateListSecond:[],
	cateGoodsList:[],
}

const mutations = {
	[SET_CATEGORY_LIST](state,{o,res}){
		if(o.id){
			state.cateListSecond = res.data;
		}else{
			state.cateListAll = res.data;
		}
	},
	[SET_CATEGORY_INFO](state,o){
		console.log(o)
	},
	[SET_GOODS_LIST](state,{o,res}){
		if(o.page == 1){
			state.cateGoodsList = res.data
		}else{
			if(res.data.length != 0){
				for(let i= 0;i<res.data.length;i++){
					state.cateGoodsList.push(res.data[i])
				}
			}else{
				//state.cateGoodsList.push('')
			}
		}
	},
}

const actions = {
	setCategoryList({ commit }, o){
		apiCategory.getCategoryList(o).then(res =>{
			commit(SET_CATEGORY_LIST,{o,res})
		})
	},
	setCategoryInfo({ commit }, o){
		apiCategory.getCategoryInfo(o).then(res => {
			commit(SET_CATEGORY_INFO, res)
		})
	},
	setGoodsList({ commit }, o){
		if(o.type){
			return apiCategory.getGoodsList(o)
		}else{
			apiCategory.getGoodsList(o).then(res => {
				commit(SET_GOODS_LIST, {o,res})
			})
		}
	}
}

export default{
	state,
	mutations,
	actions
}
