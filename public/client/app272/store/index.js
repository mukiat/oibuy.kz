import Vue from 'vue'
import Vuex from 'vuex'

import common from './modules/common'
import user from './modules/user'
import category from './modules/category'
import goods from './modules/goods'
import shop from './modules/shop'
import shopping from './modules/shopping'
import ump from './modules/ump'
import team from './modules/team'
import bargain from './modules/bargain.js'
import article from './modules/article.js'
import drp from './modules/drp.js'
import brand from './modules/brand.js'
import discover from './modules/discover.js'
import crowdfunding from './modules/crowdfunding.js'
import kefu from './modules/kefu.js'
import custom from './modules/custom.js'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		outerHrefType:false,
		splashType:true,
		topCategoryCatid:0
	},
	mutations: {
		toggleHrefType(state, o){
			state.outerHrefType = o
		},
		toggleSplashType(state, o){
			state.splashType = o
		}
	},
	actions: {
		
	},
	modules:{
		common,
		user,
		category,
		goods,
		shop,
		shopping,
		team,
		ump,
		drp,
		bargain,
		article,
		brand,
		discover,
		crowdfunding,
		kefu,
		custom
	}
})

export default store