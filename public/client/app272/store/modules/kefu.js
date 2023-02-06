import {
	KEFU_SINGLE_CHAT_LIST,
	UPDATE_KEFU_CHAT_LIST,
	KEFU_LOGIN
} from '../mutation-type.js'

import { apiKefu } from '@/config'

const state = {
	kefuChatListData:[],
}

const mutations = {
	// 进入聊天页面
	[KEFU_LOGIN](state, o) {
	    state.kefuLoginData = o
	},
	// 聊天列表
    [KEFU_SINGLE_CHAT_LIST](state, {o,res}) {
		if(o.page == 1){
			state.kefuChatListData = res.reverse();
		}else{
			for(let i= 0;i<res.length;i++){
				state.kefuChatListData.unshift(res[i])
			}
		}
    },
	[UPDATE_KEFU_CHAT_LIST](state,o){
		state.kefuChatListData.push(o)
	}
}

const actions = {		
	// 进入聊天页面
	setKefuLogin({ commit }, o) {
	    return apiKefu.getKefuLogin(o);
	},
    // 聊天历史记录
    setKefuSingleChatList({ commit }, o) {
		if(o.type == true){
			return apiKefu.getKefuSingleChatList(o)
		}else{	
			apiKefu.getKefuSingleChatList(o).then(res => {			
				commit(KEFU_SINGLE_CHAT_LIST, {o,res})
			})
		}
    },
	// 发送消息
	setTransMessage({ commit }, o){
		commit(UPDATE_KEFU_CHAT_LIST,o)
	},
	// 发送图片
	setSendImage({ commit }, o){
		return apiKefu.getSendImage(o)
	},
}

export default{
	state,
	mutations,
	actions
}