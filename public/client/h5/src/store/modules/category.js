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
	[SET_CATEGORY_LIST](state,o){
		if(o.id){
			state.cateListSecond = o.data;
		}else{
			state.cateListAll = o.data;
		}
	},
	[SET_CATEGORY_INFO](state,o){
		console.log(o)
	},
	[SET_GOODS_LIST](state,o){
		if(o.page == 1){
			state.cateGoodsList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.cateGoodsList.push(o.data[i])
				}
			}else{
				state.cateGoodsList.push('')
			}
		}
	},
}

const actions = {
	setCategoryList({ commit }, o){
		apiCategory.getCategoryLists(o,{
			getCategoryList(o){
				commit(SET_CATEGORY_LIST, o)
			}
		})
	},
	setCategoryInfo({ commit }, o){
		apiCategory.getCategoryInfo(o).then(res => {
			commit(SET_CATEGORY_INFO, res)
		})
	},
	setGoodsList({ commit }, o){
		apiCategory.getGoodsLists(o,{
			getGoodsList(o){
				commit(SET_GOODS_LIST, o)
			}
		})
	}
}

export default{
	state,
	mutations,
	actions
}
