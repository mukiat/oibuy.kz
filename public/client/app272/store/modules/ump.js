import {
	SECKILL_TIME,
	SECKILL,
	SECKILL_DETAIL,
	SET_ACTIVITY_INDEX,
	SET_ACTIVITY_SHOW,
	SET_ACTIVITY_GOODS,
	SET_ACTIVITY_COUDAN,
	BONUS_LIST,
	WEB_COUPON,
	PRESALE,
	PRESALE_LIST,
	PRESALE_DETAIL,
	PRESALE_PROPERT,
	SET_EXCHANGE_INDEX,
	SET_EXCHANGE_DETAIL,
	SET_GROUPBUY_INDEX,
	SET_GROUPBUY_DETAIL,
	SET_AUCTION_INDEX,
	SET_AUCTION_GOODS,
	SET_AUCTION_LOG,
	PACKAGE_LIST
} from '../mutation-type.js'

import { apiUmp } from '@/config'

const state = {
	goodsAttrInit:[],
	//秒杀
	seckillTime:'',
	seckillTimeData:{
        list:[],
        banner:[]
    },
	seckillData:[],
    seckillDetailData:{},
	//优惠活动
	activityIndexData:[],
	activityShowsData:Object,
	activityGoodsData:[],
	activityCoudanData:[],
	//红包优惠券
	bonusData:[],
	couponData:[],
	//预售
	presaleData:[],
	presaleListData:[],
	presaleNewList:[],
	presaleDetailData:{
		goods:Object,   
		presale:Object,
	},
	presalePropertData:[],
	presaleBuyData:[],
	//积分商城
	exchangeIndexData:[],
	exchangeDetailData:[],
	//团购
	groupbuyIndexData:{
		get_goods:[]
	},
	groupbuyDetailData:{
		goods:Object,
		price_ladder:[]
	},
	//拍卖
	auctionIndexData:[],
	auctionGoodsData:{
		goods_img:[],
		auction:Object,
		auction_goods:Object,
	},
	auctionLogData:{},
	auctionBidData:[],
	auctionBuyData:[],
	//超值礼包
	packageData:[],
}

const mutations = {
	// 秒杀
	[SECKILL](state, o) {
	    if(o.page == 1){
	        state.seckillData = o.data
		}else{
	        if(o.data.length != 0){
	  			for(let i= 0;i<o.data.length;i++){
	  				state.seckillData.push(o.data[i])
	  			}
	        }
		}
	
	    state.seckillTime = state.seckillData[0]
	},
    [SECKILL_TIME](state, o) {
        state.seckillTimeData = o.data
    },
    [SECKILL_DETAIL](state, o) {
        state.seckillDetailData = o.data
    },
	[SET_ACTIVITY_INDEX](state, o) {
		state.activityIndexData = o.data;
	},
	[SET_ACTIVITY_SHOW](state, o) {
		state.activityShowsData = o.data;
	},
	[SET_ACTIVITY_GOODS](state, o) {
		if(o.page == 1){
			state.activityGoodsData = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.activityGoodsData.push(o.data[i])
				}
			}
		}
	},
	[SET_ACTIVITY_COUDAN](state, o) {
		state.activityCoudanData = o.data;
	},
	//红包自行领取
	[BONUS_LIST](state,o){
		if(o.page == 1){
			state.bonusData = o.data;
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.bonusData.push(o.data[i])
				}
			}
		}
	},
	/*优惠券*/
	[WEB_COUPON](state, o) {
	  if(o.page == 1){
			state.couponData = o.data;
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.couponData.push(o.data[i])
				}
			}
		}
	},
	/* 预售 */
	[PRESALE](state, o) {
		state.presaleData = o.data
	},
	[PRESALE_LIST](state, o) {
		if(o.page == 1){
			state.presaleListData = o.data.pre_goods
		}else{
			if(o.page == 1){
				for(let i= 0;i<o.data.pre_goods.length;i++){
					state.presaleListData.push(o.data.pre_goods[i])
				}
			}
		}
	},
	[PRESALE_DETAIL](state, o) {
		state.presaleDetailData = o.data

		state.goodsAttrInit = []

		for(let i = 0; i < state.presaleDetailData.goods.attr.length; i++){
			for(let j = 0; j < state.presaleDetailData.goods.attr[i].attr_key.length;j++){
				if(j == 0){
					state.goodsAttrInit.push(state.presaleDetailData.goods.attr[i].attr_key[j].goods_attr_id)
				}
			}
		}
	},
	[PRESALE_PROPERT](state, o) {
		state.presalePropertData = o.data
	},
	/*超级礼包*/
	[PACKAGE_LIST](state, o) {
		if(o.page == 1){
			state.packageData = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.packageData.push(o.data[i])
				}
			}else{
				state.packageData.push('')
			}
		}
	},
	// 积分商城
	[SET_EXCHANGE_INDEX](state, o) {
		if(o.page == 1){
			state.exchangeIndexData = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.exchangeIndexData.push(o.data[i])
				}
			}else{
				state.exchangeIndexData.push('')
			}
		}
	},
	[SET_EXCHANGE_DETAIL](state, o) {
		state.exchangeDetailData = o.data

		state.goodsAttrInit = []

		for(let i = 0; i < state.exchangeDetailData.exchange_goods_attr.length; i++){
			for(let j = 0; j < state.exchangeDetailData.exchange_goods_attr[i].attr_key.length;j++){
				if(j == 0){
					state.goodsAttrInit.push(state.exchangeDetailData.exchange_goods_attr[i].attr_key[j].goods_attr_id)
				}
			}
		}
	},
	// 团购
	[SET_GROUPBUY_INDEX](state, o) {
	  if(o.page == 1){
			state.groupbuyIndexData = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.groupbuyIndexData.push(o.data[i])
				}
			}else{
				state.groupbuyIndexData.push('')
			}
		}
	},
	[SET_GROUPBUY_DETAIL](state, o) {
		state.groupbuyDetailData = o.data

		state.goodsAttrInit = []

		for(let i = 0; i < state.groupbuyDetailData.goods.group_buy_attr.length; i++){
			for(let j = 0; j < state.groupbuyDetailData.goods.group_buy_attr[i].attr_key.length;j++){
				if(j == 0){
					state.goodsAttrInit.push(state.groupbuyDetailData.goods.group_buy_attr[i].attr_key[j].goods_attr_id)
				}
			}
		}
	},
	//拍卖
	[SET_AUCTION_INDEX](state, o) {
		if(o.page == 1){
			state.auctionIndexData = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.auctionIndexData.push(o.data[i])
				}
			}else{
				state.auctionIndexData.push('')
			}
		}
	},
	[SET_AUCTION_GOODS](state, o) {
		state.auctionGoodsData = o.data;
	},
	[SET_AUCTION_LOG](state, o) {
		state.auctionLogData = o.data;
	},
}

const actions = {
	// 秒杀时间段
    setSeckillTime({commit}) {
        apiUmp.getSeckillTime().then(res => {
            commit(SECKILL_TIME, res)
        })
    },
    // 秒杀某个时间段商品列表
    setSeckill({commit}, o) {
        apiUmp.getSeckill(o).then(res => {
            commit(SECKILL, res)
        })
    },
    // 秒杀详情
    setSeckillDetail({commit}, o) {
        apiUmp.getSeckillDetail(o).then(res => {
            commit(SECKILL_DETAIL, res)
        })
    },
    // 秒杀购买
    setSeckillBuy({commit}, o) {
        return apiUmp.getSeckillBuy(o)
    },
	//优惠活动-首页
	setActivityIndex({commit}, o) {
		apiUmp.activityIndex(o).then(res => {   
			commit(SET_ACTIVITY_INDEX, res)
		})
	},
	//优惠活动-首页
	setActivityShow({commit}, o) {
		apiUmp.activityShow(o).then(res => {   
			commit(SET_ACTIVITY_SHOW, res)
		})
	},
	//优惠活动-商品
	setActivityGoods({commit}, o) {
		apiUmp.activityGoods(o).then(res => {   
			commit(SET_ACTIVITY_GOODS, res)
		})
	},
	//优惠活动-凑单
	setActivityCoudan({commit}, o) {
		apiUmp.activityCoudan(o).then(res => {   
			commit(SET_ACTIVITY_COUDAN, res)
		})
	},
	//红包自行领取列表
	setBonus({ commit }, o){
		apiUmp.getBonus(o).then(res => {
			commit(BONUS_LIST, res)
		})
	},
	receiveBonus({ commit }, o){
		return apiUmp.getReceiveBonus(o)
	},
	// 优惠券
	setWebCoupon({commit}, o) {
		apiUmp.getWebCoupon(o).then(res => {
			commit(WEB_COUPON, res)
		})
	},
	// 优惠券 任务集市
	setWebTaskCoupon({ commit }, o){
		apiUmp.getWebTaskCoupon(o).then(res => {
			commit(WEB_COUPON, res)
		})
	},
	// 预售
	setPresale({commit}, o) {
		apiUmp.presale(o).then(res => {
			commit(PRESALE, res)
		})
	},
	// 预售列表
	setPresaleList({commit}, o) {
		apiUmp.presaleList(o).then(res => {
			commit(PRESALE_LIST, res)
		})
	},
	// 预售列表
	setPresaleNew({commit}, o) {
		apiUmp.presaleNew(o).then(res => {
			commit(PRESALE_LIST, res)
		})
	},
	// 预售详情
	setPresaleDetail({commit}, o) {
		apiUmp.presaleDetail(o).then(res => {
			commit(PRESALE_DETAIL, res)
		})
	},
	// 预售属性
	setPresalePropert({commit}, o) {
		apiUmp.presalePropert(o).then(res => {
			commit(PRESALE_PROPERT, res)
		})
	},
	// 预售购买
	setPresaleBuy({commit}, o) {
		return apiUmp.presaleBuy(o)
	},
	// 超级礼包
	setPackageList({commit}, o) {
		apiUmp.packageList(o).then(res => {
			commit(PACKAGE_LIST, res)
		})
	},
	// 积分商城
	setExchangeIndex({commit}, o) {
		apiUmp.exchangeIndex(o).then(res => {   
			commit(SET_EXCHANGE_INDEX, res)
		})
	},
	// 积分详情
	setExchangeDetail({commit}, o) {
		apiUmp.exchangeDetail(o).then(res => {   
			commit(SET_EXCHANGE_DETAIL, res)
		})
	},
	// 积分buy
	setExchangeBuy({commit}, o) {
		return apiUmp.getExchangeBuy(o)
	},
	// 团购
	setGroupbuyIndex({commit}, o) {
		apiUmp.groupbuyIndex(o).then(res => {   
			commit(SET_GROUPBUY_INDEX, res)
		})
	},
	// 团购详情
	setGroupbuyDetail({commit}, o) {
		apiUmp.groupbuyDetail(o).then(res => {   
			commit(SET_GROUPBUY_DETAIL, res)
		})
	},
	// 团购buy
	setGroupBuy({commit}, o) {
		return apiUmp.groupBuy(o)
	},
	// 拍卖首页
	setAuctionIndex({commit}, o) {
		apiUmp.auctionIndex(o).then(res => {   
			commit(SET_AUCTION_INDEX, res)
		})
	},
	// 拍卖商品详情
	setAuctionGoods({commit}, o) {
		apiUmp.auctionGoods(o).then(res => {   
			commit(SET_AUCTION_GOODS, res)
		})
	},
	// 拍卖记录
	setAuctionLog({commit}, o) {
		apiUmp.auctionLog(o).then(res => {   
			commit(SET_AUCTION_LOG, res)
		})
	},
	// 拍卖bid
	setAuctionBid({commit}, o) {
		return apiUmp.auctionBid(o)
	},
	// 拍卖buy
	setAuctionBuy({commit}, o) {
		return apiUmp.auctionBuy(o)
	},
}

export default{
	state,
	mutations,
	actions
}