import {
    DRP_REGEND,
    DRP,
    DRP_GOODS_LIST,
    DRP_TRANS,
    DRP_SHOP,
    DRP_ORDER,
    DRP_ORDER_DETAIL,
    DRP_TEAM,
    DRP_OFFLINE_USER,
    DRP_TEAM_DETAIL,
    DRP_RANK,
    DRP_LOG,
    DRP_NEWS,
    DRP_SET,
    DRP_SET_UPDATE_AVATAR,
    DRP_LIST,
    DRP_UPDATE_LIST,
    DRP_PURCHASE,
    DRP_CATEGORY_LIST,
    DRP_CATEGORY_ADD,
    DRP_CHANGE_CARD,
    DRP_RIGHTS_CARD,
    DRP_PROTECTION,
    MY_DRP
} from '../mutation-type.js'

import {
    apiDrp
} from '@/config'

import { Toast } from 'vant'

const state = {
    page:1,
	size:10,
    drpRegister: [],
    drpRegendData:Object,
    drpData: {
        shop_info: {}
    },
    drpGoodsList: [],
    drpTransData: [],
    drpShopData: {
        shop_info: {}
    },
    drpOrderData: [],
    drpOrderDetailData: [],
    drpTeamData: [],
    drpOffkineUserData: [],
    drpTeamDetailData: [],
    drpRankData: [],
    drpLogData: [],
    drpNewsData: [],
    drpSetData: [],
    purchaseData:Object,
    cateListAll: [],
    cateListSecond:[],
    drptype:null,
    dapList:[],
    drpRightsCard:{},
    drpChangeCard:{},
    drpProtection:{},
    myDrpData:{
        shop_info: {}
    }
}

const mutations = {
    //注册完成
    [DRP_REGEND](state, o) {
        state.drpRegendData = o.data;
    },
    //代言中心
    [DRP](state, o) {
        state.drpData = o.data;
    },
    //代言中心
    [DRP_GOODS_LIST](state, o) {
        if(o.page == 1){
            state.drpGoodsList = o.data
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.drpGoodsList.push(o.data[i])
    			}
            }else{
                 state.drpGoodsList.push('')      
            }
		}
    },
    //提现
    [DRP_TRANS](state, o) {
        state.drpTransData = o.data;
    },
    //店铺
    [DRP_SHOP](state, o) {
        state.drpShopData = o.data;
    },
    //订单
    [DRP_ORDER](state, o) {
        if(o.page == 1){
            state.drpOrderData = o.data;
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.drpOrderData.push(o.data[i])
    			}
            }else{
                state.drpOrderData.push('')
            }
		}
    },
    //订单详情
    [DRP_ORDER_DETAIL](state, o) {
        state.drpOrderDetailData = o.data
    },
    //团队
    [DRP_TEAM](state, o) {
        state.drpTeamData = o.data
    },
    //下级会员
    [DRP_OFFLINE_USER](state, o) {
        state.drpOffkineUserData = o.data
    },
    //团队详情
    [DRP_TEAM_DETAIL](state, o) {
        state.drpTeamDetailData = o.data
    },
    //排行榜
    [DRP_RANK](state, o) {
        state.drpRankData = o.data
    },
    //佣金明细
    [DRP_LOG](state, o) {
        if(o.page == 1){
            state.drpLogData = o.data
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.drpLogData.push(o.data[i])
    			}
            }else{
                state.drpLogData.push('')
            }
		}
    },
    //新手必看
    [DRP_NEWS](state, o) {
        state.drpNewsData = o.data;
    },
    //设置
    [DRP_SET](state, o) {
        state.drpSetData = o.data.shop_info;
    },
    [DRP_SET_UPDATE_AVATAR](state,o){
        state.drpSetData = o.data.shop_info
        Toast.clear();
    },
    [DRP_PURCHASE](state,o){
        state.purchaseData = o.data
    },
    [DRP_CATEGORY_LIST](state,o){
        if(o.id){
            state.cateListSecond = o.data.data;
            state.drptype = o.data.type;
        }else{
            state.cateListAll = o.data;
        }
    },
    [DRP_CATEGORY_ADD](state,o){
        if(o.f_id){
            let fale = false
            state.cateListSecond.forEach(res=>{
                if(res.cat_id == o.f_id){
                    res.child.forEach(result=>{
                        if(result.cat_id == o.id){
                            result.drp_type = !result.drp_type
                        }
                        if(result.drp_type == false){
                            fale = true
                        }
                    })

                    res.drp_type = !fale
                }
            })
        }else{
            let type = o.type == 1 ? true : false;
            state.cateListSecond.forEach(res=>{
                if(res.cat_id == o.cur_id){
                    res.drp_type = type

                    res.child.forEach(result=>{
                        result.drp_type = type
                    })
                }
            })
        }
    },
    [DRP_LIST](state,o){
        state.dapList = o.data
    },
    [DRP_UPDATE_LIST](state,o){
        state.dapList.forEach(res=>{
            if(res.goods_id == o.goods_id){
                res.drp_type = !res.drp_type
            }
        })
    },
    [DRP_CHANGE_CARD](state,o){
        state.drpChangeCard = o.data
    },
    [DRP_RIGHTS_CARD](state,o){
        state.drpRightsCard = o.data.info
    },
    [DRP_PROTECTION](state,o){
        state.drpProtection = o.data.list
    },
    [MY_DRP](state,o){
        state.myDrpData = o.data;
    }
}
const actions = {
   // 开店
    setDrpRegister({
        commit
    }, o) {
        return apiDrp.drpRegister(o)
    },
    // 开店完成
    setDrpRegend({
        commit
    }, o) {
        apiDrp.drpRegend(o).then(res => {
            commit(DRP_REGEND, res)
        })
    },
    // 分销中心
    setDrp({
        commit
    }, o) {
        apiDrp.drp(o).then(res => {
            commit(DRP, res)
        })
    },
    // 我的微店
    setMyDrp({ commit }, o){
        apiDrp.myShop(o).then(res=>{
            commit(MY_DRP, res)
        })
    },
    // 商品列表
    setDrpGoodsList({
        commit
    }, o) {
        apiDrp.drpGoodsList(o).then(res => {
            commit(DRP_GOODS_LIST, res)
        })
    },
    // 提现
    setDrpTrans({
        commit
    }, o) {
        apiDrp.drpTrans(o).then(res => {
            commit(DRP_TRANS, res)
        })
    },
    // 提现转入余额
    setDrpTransferred({
        commit
    }, o) {
        return apiDrp.drpTransferred(o)
    },
    // 店铺
    setDrpShop({
        commit
    }, o) {
        apiDrp.drpShop(o).then(res => {
            commit(DRP_SHOP, res)
        })
    },
    // 订单
    setDrpOrder({
        commit
    }, o) {
        apiDrp.drpOrder(o).then(res => {
            commit(DRP_ORDER, res)
        })
    },
    // 订单详情
    setDrpOrderDetail({
        commit
    }, o) {
        apiDrp.drpOrderDetail(o).then(res => {
            commit(DRP_ORDER_DETAIL, res)
        })
    },
    // 我的团队
    setDrpTeam({
        commit
    }, o) {
        apiDrp.drpTeam(o).then(res => {
            commit(DRP_TEAM, res)
        })
    },
    // 下级会员
    setDrpOfflineUser({
        commit
    }, o) {
        apiDrp.drpOfflineUser(o).then(res => {
            commit(DRP_OFFLINE_USER, res)
        })
    },
    // 团队详情
    setDrpTeamDetail({
        commit
    }, o) {
        apiDrp.drpTeamDetail(o).then(res => {
            commit(DRP_TEAM_DETAIL, res)
        })
    },
    // 团队排行榜
    setDrpRank({
        commit
    }, o) {
        apiDrp.drpRank(o).then(res => {
            commit(DRP_RANK, res)
        })
    },
    // 佣金明细
    setDrpLog({
        commit
    }, o) {
        apiDrp.drpLog(o).then(res => {
            commit(DRP_LOG, res)
        })
    },
    // 新手比看
    setDrpNews({
        commit
    }, o) {
        apiDrp.drpNews(o).then(res => {
            commit(DRP_NEWS, res)
        })
    },
    // 设置
    setDrpSet({
        commit
    }, o) {
        apiDrp.drpSet(o).then(res => {
            commit(DRP_SET, res)
        })
    },
    //修改头像
    setDrpUpdateAvatar({ commit }, o){
        apiDrp.drpUpdateAvatar(o).then(res =>{
            commit(DRP_SET_UPDATE_AVATAR, res)
        })
    },
    // 更新
    setDrpUpdate({ commit }, o) {
        return apiDrp.drpUpdate(o)
    },
    setDrpCategoryLists({commit}, o){
        apiDrp.getDrpCategoryLists(o,{
            getCategoryList(o){
                commit(DRP_CATEGORY_LIST, o)
            }
        })
    },
    setDrpCategoryAdd({commit}, o){
        apiDrp.getDrpCategoryAdd(o).then(res => {
            commit(DRP_CATEGORY_ADD, res)
        })
    },
    setDrpList({ commit }, o){
        apiDrp.getDrpList(o).then(res=>{
            commit(DRP_LIST, res)
        })
    },
    setDrpGoodsAdd({ commit }, o){
        apiDrp.getDrpGoodsAdd(o).then(res=>{
             commit(DRP_UPDATE_LIST, res)
        })
    },
    setDrpPurchase({ commit }, o){
        apiDrp.getDrpPurchase().then(res=>{
            commit(DRP_PURCHASE, res)
        })
    },
    setDrpPay({ commit }, o){
        return apiDrp.getDrpPay(o)
    },
    setDrpChangePayment({ commit }, o){
        return apiDrp.getDrpChangePayment(o)
    },
    setDrpChangeCard({commit},o){
        apiDrp.getDrpChangeCard(o).then(res=>{
            commit(DRP_CHANGE_CARD, res)
        })
    },
    setDrpRightsCard({commit},o){
        apiDrp.getDrpRightsCard(o).then(res=>{
            commit(DRP_RIGHTS_CARD,res)
        })
    },
    setDrpProtection({commit},o){
        apiDrp.getDrpProtection(o).then(res=>{
            commit(DRP_PROTECTION,res)
        })
    }
}
export default {
    state,
    mutations,
    actions,
}
