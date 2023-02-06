import {
	SET_TEAM_INDEX,
	SET_TEAM_GOODS,
	SET_TEAM_PROPERTY,
	SET_TEAM_CATE,
	SET_TEAM_DETAIL,
	SET_TEAM_LIST,
	SET_TEAM_RANK,
	SET_TEAM_WAIT,
	SET_TEAM_ORDER,
	SET_TEAM_USER
} from '../mutation-type.js'

import {
	apiTeam
} from '@/config'

const state = {
	teamIndexData: [],
	teamGoodsData: [],
	page: 1,
	size: 10,
	teamDetailData: {
		goods_info: []
	},
	goodsAttrInit: [],
	goodsAttrOper: [],
	teamCateData: [],

	teamListData: [],
	teamRankData: [],
	teamWaitData: {
		teamUser:[],
		team_info:{}
	},
	teamUserData: [],
	teamOrderData: []
}

const mutations = {
	[SET_TEAM_INDEX](state, o) {
		state.teamIndexData = o.data
	},
	[SET_TEAM_GOODS](state, o) {
		if (o.page == 1) {
			state.teamGoodsData = o.data
		} else {
			if (o.data.length != 0) {
				for (let i = 0; i < o.data.length; i++) {
					state.teamGoodsData.push(o.data[i])
				}
			}
		}
	},
	[SET_TEAM_DETAIL](state, o) {
		state.teamDetailData = o.data
		
		state.goodsAttrInit = []
		state.goodsAttrOper = ''
		
		for (let i = 0; i < state.teamDetailData.goods_properties.length; i++) {
			for (let j = 0; j < state.teamDetailData.goods_properties[i].attr_key.length; j++) {
				if (j == 0) {
					state.goodsAttrInit.push(state.teamDetailData.goods_properties[i].attr_key[j].goods_attr_id)
				}
			}
		}
	},
	[SET_TEAM_PROPERTY](state, o) {
		state.goodsAttrOper = o.data
	},
	[SET_TEAM_CATE](state, o) {
		state.teamCateData = o.data
	},
	[SET_TEAM_LIST](state, o) {
		state.teamListData = o.data
	},
	[SET_TEAM_RANK](state, o) {
		if (o.page == 1) {
			state.teamRankData = o.data
		} else {
			if (o.data.length != 0) {
				for (let i = 0; i < o.data.length; i++) {
					state.teamRankData.push(o.data[i])
				}
			}
		}
	},
	[SET_TEAM_WAIT](state, o) {
		state.teamWaitData = o.data
	},
	[SET_TEAM_USER](state, o) {
		state.teamUserData = o.data
	},
	[SET_TEAM_ORDER](state, o) {
		if (o.page == 1) {
			state.teamOrderData = o.data
		} else {
			if (o.data.length != 0) {
				for (let i = 0; i < o.data.length; i++) {
					state.teamOrderData.push(o.data[i])
				}
			}
		}
	}
}

const actions = {
	setTeamIndex({ commit }, o) {
		apiTeam.teamIndexs(o).then(res => {
			commit(SET_TEAM_INDEX, res)
		})
	},
	setTeamGoods({ commit }, o) {
		apiTeam.teamGoods(o).then(res => {
			commit(SET_TEAM_GOODS, res)
		})
	},
	setTeamDetail({ commit }, o) {
		apiTeam.teamDetail(o).then(res => {
			commit(SET_TEAM_DETAIL, res)
		})
	},
	setTeamProperty({ commit }, o) {
		apiTeam.teamProperty(o).then(res => {
			commit(SET_TEAM_PROPERTY, res)
		})
	},
	//拼团加入购物车
	setTeamBuy({ commit }, o) {
		return apiTeam.teamBuy(o)
	},
	setTeamCate({ commit }, o) {
		apiTeam.teamCategories(o).then(res => {
			commit(SET_TEAM_CATE, res)
		})
	},
	setTeamList({ commit }, o) {
		apiTeam.teamList(o).then(res => {
			commit(SET_TEAM_LIST, res)
		})
	},
	setTeamRank({ commit }, o) {
		apiTeam.teamRank(o).then(res => {
			commit(SET_TEAM_RANK, res)
		})
	},
	//等待成团
	setTeamWait({ commit }, o) {
		apiTeam.teamWait(o).then(res => {
			commit(SET_TEAM_WAIT, res)
		})
	},
	//拼团成员
	setTeamUser({ commit }, o) {
		apiTeam.teamUser(o).then(res => {
			commit(SET_TEAM_USER, res)
		})
	},
	//拼团订单
	setTeamOrder({ commit }, o) {
		apiTeam.teamOrder(o).then(res => {
			commit(SET_TEAM_ORDER, res)
		})
	}
}

export default {
	state,
	mutations,
	actions,
}