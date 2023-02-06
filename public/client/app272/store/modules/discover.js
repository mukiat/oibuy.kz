import {
	DISCOVER_LIST,
	DISCOVER_SHOW,
	DISCOVER_MY_INFO,
	DISCOVER_DETAIL,
	DISCOVER_MY_LIST
} from '../mutation-type.js'

import {
    apiDiscover
} from '@/config'

const state = {
	discoverList:[],
	discoverShow:'',
	discoverMyInfo:'',
	discoverDetail:[],
	discoverMyList:[]
}

const mutations = {
	[DISCOVER_LIST](state,o){
		if(o.page == 1){
			state.discoverList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.discoverList.push(o.data[i])
				}
			}else{
				state.discoverList.push('')
			}
		}
	},
	[DISCOVER_SHOW](state,o){
		state.discoverShow = o
	},
	[DISCOVER_MY_INFO](state,o){
		state.discoverMyInfo = o.data
	},
	[DISCOVER_DETAIL](state,o){
		state.discoverDetail = o
	},
	[DISCOVER_MY_LIST](state,o){
		if(o.page == 1){
			state.discoverMyList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.discoverMyList.push(o.data[i])
				}
			}else{
				state.discoverMyList.push('')
			}
		}
	}
}

const actions = {
	setDiscoverIndex({ commit }){
		return apiDiscover.getDiscoverIndex()
	},
	setDiscoverList({ commit }, o){
		apiDiscover.getDiscoverList(o).then(res=>{
			commit(DISCOVER_LIST,res)
		})
	},
	setDiscoverShow({ commit }, o){
		apiDiscover.getDiscoverShow(o).then(res=>{
			commit(DISCOVER_SHOW,res)
		})
	},
	setDiscoverCreate({ commit }, o){
		return apiDiscover.getDiscoverCreate(o)
	},
	setDiscoverDetail({ commit }, o){
		apiDiscover.getDiscoverDetail(o).then(res=>{
			commit(DISCOVER_DETAIL,res)
		})
	},
	setDiscoverCommentList({ commit }, o){
		return apiDiscover.getDiscoverCommentList(o)
	},
	setDiscoverComment({ commit }, o){
		return apiDiscover.getDiscoverComment(o)
	},
	setDiscoverMy({ commit }, o){
		apiDiscover.getDiscoverMy(o).then(res=>{
			commit(DISCOVER_MY_INFO,res)
		})
	},
	setDiscoverMyList({ commit }, o){
		apiDiscover.getDiscoverMyList(o).then(res=>{
			
			commit(DISCOVER_MY_LIST,res)
		})
	},
	setDiscoverReply({ commit }, o){
		apiDiscover.getDiscoverReply(o).then(res=>{
			console.log(res)
		})
	},
	setDiscoverLike({ commit }, o){
		return apiDiscover.getDiscoverLike(o)
	},
	setDiscoverDelete({ commit }, o){
		return apiDiscover.getDiscoverDelete(o)
	}
}

export default{
	state,
    mutations,
    actions,
}
