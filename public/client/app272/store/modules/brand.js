import {
    BRAND_LIST,
	BRAND_DETAIL,
	BRAND_PRODUCT_LIST,
} from '../mutation-type.js'

import {
    apiBrand
} from '@/config'

const state = {
    brandCategory:[],
	brandNavNums:{
		all_goods:'',
		hot_goods:'',
		new_goods:''
	},
	brandProduct:[],
	brandMsg:{},
	brandList:[],
}
const mutations = {
    [BRAND_DETAIL](state,o){
		state.brandCategory = o.category
		state.brandMsg = o.brand_msg
		state.brandNavNums.all_goods = o.all_goods
		state.brandNavNums.hot_goods = o.hot_goods
		state.brandNavNums.new_goods = o.new_goods
	},
	[BRAND_PRODUCT_LIST](state,o){
		if(o.page == 1){
			state.brandProduct = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.brandProduct.push(o.data[i])
				}
			}	
		}
	},
	[BRAND_LIST](state,o){
		state.brandList = o.data
	}
}
const actions = {
    setBrand({ commit }){
		return apiBrand.getBrand()
	},
	setBrandDetail({ commit }, o){
		apiBrand.getBrandDetail(o).then(res=>{
			commit(BRAND_DETAIL,res)
		})
	},
	setBrandProduct({ commit }, o){
		apiBrand.getBrandProduct(o).then(res=>{
			commit(BRAND_PRODUCT_LIST,res)
		})
	},
	setBrandList({ commit }){
		apiBrand.getBrandList().then(res=>{
			commit(BRAND_LIST,res)
		})
	}
}
export default {
    state,
    mutations,
    actions,
}
