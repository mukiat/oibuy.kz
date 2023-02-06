import {
	USER_LOGIN,
	USER_LOGOUT,
	USER_PROFILE,
	USER_ID,
	UPDATE_USER_PROFILE,
	UPDATE_USER_PROFILE_AVATAR,
	USER_ADDRESS,
	USER_ADDRESS_DEFAULT,
	USER_ADDRESS_DELETE,
	USER_ORDER_LIST,
	USER_ORDER_DETAIL,
	SHOP_COLLECT_LIST,
	SHOP_COLLECT,
	GOODS_COLLECT_LIST,
	GOODS_COLLECT,
	USER_UPDATE_TEXT,
	USER_HISTORY,
	USER_HISTORY_DELETE,
	REFOUND_LIST,
	ORDER_REFOUND,
	APPLY_REFOUND,
	REFOUND_DETAIL,
	USER_COUPON,
	UPLOAD_LIST,
	DELETE_MATERIAl_IMG,
	COMMENT_LIST,
	COMMENT_INFO,
	ARTICLE_HELP,
	USER_BONUS_LIST,
	VALUE_CARD_LIST,
	VALUE_CARD_INFO,
	MERCHANTS_INFO,
	MERCHANTS_GUIDE,
	MERCHANTS_CATEGORY,
	MERCHANTS_CHILD_CATE,
	MERCHANTS_ADD_CATE,
	MERCHANTS_DELETE_CATE,
	MERCHANTS_AUDIT,
} from '../mutation-type.js'

import { apiUser } from '@/config'
import store from '@/store'

const state = {
	status:'',
	token:null,
	userInfo:'',
	addressList:[],
	addressInfo:Object,
	addressId:'',
	shopCollectList:[],
	goodsCollectList:[],
	goodsCollectStatue:[],
	shopCollectStatue:[],
	userOrderList:[],
	userorderCount:Object,
	userorderInfo:[],
	userOrderDetail:[],
	listShow:false,
	historyList:[],
	refoundList:[],
	orderRefound:'',
	applyRefoundDetail:Object,
	refoundDetail:Object,
	couponData:[],
	materialList:[],
	commentList:[],
	commentInfo:Object,
	articleHelpList:[],
	bonusData:[],
	valueCardList:[],
	valueCardInfo:[],
	userId:'',
	merchantsInfo:{
		shop:{},
		steps:{}
	},
	merchantsGuide:Object,
	merchantsCategory:[],
	merchantsChildCate:[],
	merchantsCategoryInfo:Object,
	recordList:[],
	all_checked:false,
	vuex_isApplyStore: false,
}

const mutations = {
	[USER_LOGIN](state,o){
		state.token = o.data;
		
		//存本地token
		uni.setStorage({
			key:'token',
			data:o.data,
			success: (res) => {
				//记录user_id
				store.dispatch('setUserId');
				
				if(o.delta == 1){
					uni.navigateBack({
						delta:1
					});
				}else if(o.delta == 'index'){
					uni.switchTab({
						url:'/pages/index/index'
					});
				}else{
					uni.switchTab({ 
						url:'/pages/user/user'
					});
				}
			}
		});
		
		//获取购物车数量
		store.dispatch('setCommonCartNumber');
	},
	[USER_LOGOUT](state,o){
		state.token = '';
		
		//清除本地token
		uni.removeStorage({
			key:'token',
			success: (res) => {
				uni.reLaunch({
					url:'/pagesB/login/login'
				})
			}
		})
	},
	[USER_PROFILE](state,o){
		if(o.status == 'success'){
			state.userInfo = o.data
		}else{
			state.token = '';
			uni.removeStorageSync("token");
		}
	},
	[UPDATE_USER_PROFILE](state,o){
		if(o.status == "success"){
			state.userInfo = o.data;
			uni.showToast({
				title:'修改成功！',
				icon:'success'
			})
		}else{
			uni.showToast({
				title:'修改失败！',
				icon:'none'
			})
		}
	},
	[UPDATE_USER_PROFILE_AVATAR](state,o){
		state.userInfo.avatar = o.data.avatar
		uni.hideLoading();
	},
	[USER_ADDRESS](state,o){
		state.addressList = o.data

		for(let i = 0;i < state.addressList.length;i++){
			if(state.addressList[i].is_checked == 1){
				state.addressId = state.addressList[i].id
			}
		}
	},
	[USER_ADDRESS_DEFAULT](state,o){
		state.addressId = o.address_id
	},
	[USER_ADDRESS_DELETE](state,o){
		for(let i = 0;i < state.addressList.length;i++){
			if(state.addressList[i].id == o.address_id) {
		        state.addressList.splice(i, 1)
		        break
			}
		}
		uni.hideLoading();
	},
	[SHOP_COLLECT_LIST](state,o){
		if(o.page == 1){
			state.shopCollectList = o.data;
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.shopCollectList.push(o.data[i])
				}
			}
		}
	},
	[SHOP_COLLECT](state,o){
		state.shopCollectStatue = []
		
		for(let i = 0; i < state.shopCollectList.length; i++){
			if(state.shopCollectList[i].ru_id == o.ru_id){
				state.shopCollectList.splice(i, 1)
				break
			}
		}
		
		let obj = {
			id:o.ru_id,
			status:o.status
		}

		// if(state.shopCollectStatue.length > 0){
		// 	for(let i = 0;i<state.shopCollectStatue.length;i++){
		// 		if(state.shopCollectStatue[i].id == o.ru_id){
		// 			state.shopCollectStatue.splice(i, 1)
		// 			state.shopCollectStatue.push(obj)
		// 		}else{
		// 			state.shopCollectStatue.push(obj)
		// 		}
		// 	}
		// }else{
			state.shopCollectStatue.push(obj)
		//}
	},
	[GOODS_COLLECT_LIST](state,o){
		if(o.page == 1){
    		state.goodsCollectList = o.data;
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.goodsCollectList.push(o.data[i])
				}
			}
		}
	},
	[GOODS_COLLECT](state,o){
		for(let i = 0; i < state.goodsCollectList.length; i++){
			if(state.goodsCollectList[i].goods_id == o.goods_id){
				state.goodsCollectList.splice(i, 1)
				break
			}
		}

		let obj = {
			id:o.goods_id,
			status:o.status
		}

		if(state.goodsCollectStatue.length > 0){
			for(let i = 0;i<state.goodsCollectStatue.length;i++){
				if(state.goodsCollectStatue[i].id == o.goods_id){
					state.goodsCollectStatue.splice(i, 1)
					state.goodsCollectStatue.push(obj)
				}else{
					state.goodsCollectStatue.push(obj)
				}
			}
		}else{
			state.goodsCollectStatue.push(obj)
		}
	},
	[USER_ORDER_LIST](state,o){
		state.userorderCount = o.data.count;
		state.userorderInfo = o.data.info;
		if(o.page == 1){
			state.userOrderList = o.data.list
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.list.length;i++){
					state.userOrderList.push(o.data.list[i])
				}
			}
		}
	},
	[USER_ORDER_DETAIL](state,o){
		state.userOrderDetail = o.data
		if(o.data.goods.length > 1){
			state.listShow = true
		}else{
			state.listShow = false
		}
	},
	[USER_UPDATE_TEXT](state, o){
		state.userInfo[o.type] = o.val
	},
	[USER_HISTORY](state,o){
		state.historyList = o
	},
	[USER_HISTORY_DELETE](state,{res,o}){
		if(res.data == 200){
			state.historyList = []
		}else if(res.data == 300){
			for(let i = 0; i < state.historyList.length; i++){
				if(state.historyList[i].id == o.id){
					state.historyList.splice(i, 1)
					break
				}
			}
		}
	},
	[REFOUND_LIST](state,o){
		if(o.page == 1){
    		state.refoundList = o.data;
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.refoundList.push(o.data[i])
				}
			}
		}
	},
	[ORDER_REFOUND](state,o){
		state.orderRefound = o.data
	},
	[APPLY_REFOUND](state,o){
		state.applyRefoundDetail = o.data
	},
	[REFOUND_DETAIL](state,o){
		state.refoundDetail = o.data
	},
	[USER_COUPON](state,o){
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
	[UPLOAD_LIST](state,o){
		if(state.materialList.length > 0 && o.data.length > 0){
			for(let i= 0;i<o.data.length;i++){
				state.materialList.push(o.data[i])
			}
		}else{
			state.materialList = o.data
		}
	},
	[DELETE_MATERIAl_IMG](state,o){
		state.materialList.splice(o.index, 1)
	},
	[COMMENT_LIST](state,o){
		if (o.page == 1) state.commentList = o.data
		else state.commentList = [...state.commentList, ...o.data]
	},
	[COMMENT_INFO](state,o){
		state.commentInfo = o.data
	},
	[ARTICLE_HELP](state,o){
		state.articleHelpList = o.data
	},
	/*红包*/
    [USER_BONUS_LIST](state, o) {
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
    [VALUE_CARD_LIST](state, o){
    	if(o.page == 1){
            state.valueCardList = o.data
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.valueCardList.push(o.data[i])
    			}
            }
		}
    },
    [VALUE_CARD_INFO](state,o){
    	state.valueCardInfo = o.data
    },
	[USER_ID](state,o){
		let user_id = o.data.id;
		state.userId = user_id;
		
		uni.setStorageSync('user_id',user_id);
		uni.setStorageSync('bindMobilePhone',o.data.mobile_phone);
	},
	[MERCHANTS_INFO](state,o){
		state.merchantsInfo = o.data
	},
	[MERCHANTS_GUIDE](state,o){
		state.merchantsGuide = o.data
	},
	[MERCHANTS_CATEGORY](state,o){
		state.merchantsCategory = o.data.category
		if (o.data.shop) {
			const { merchants_audit, steps_audit } = o.data.shop;
			state.vuex_isApplyStore = merchants_audit != 2 && steps_audit != 0
		} else {
			state.vuex_isApplyStore = false
		}
	},
	[MERCHANTS_CHILD_CATE](state,o){
		state.merchantsChildCate = o.data.childCate
	},
	[MERCHANTS_ADD_CATE](state,res){
		const arr = [];
		
		if (res.status == 'success') {
			res.data.category_info.forEach(item => {
				if (!arr.includes(item.parent_name)) {
					arr.push(item.parent_name)
				}
			})
		}
		
		state.recordList = arr
	},
	[MERCHANTS_DELETE_CATE](state,{o,res}){
		let curIndex
		if(res.data.code == 0){
			state.recordList.forEach((v,i)=>{
				if(i == o.index){
					v.child.forEach((child,index)=>{
						if(child.ct_id == res.data.ct_id){
							curIndex = index
						}
					})
				}
			})
		}

		state.recordList[o.index].child.splice(curIndex,1)
	},
	[MERCHANTS_AUDIT](state,o){
		Vue.set(state.merchantsInfo,'shop',o.data.shop)
	},
}

const actions = {
	userLogin({ commit }, o){
		apiUser.getLogins(o).then(res=>{
			commit(USER_LOGIN,res)
		})
	},
	userFastLogin({commit}, o){
  		commit(USER_LOGIN, o)
	},
	userRegister({ commit }, o){
		return apiUser.getRegister(o)
	},
	userCallback({commit}, o){
		return apiUser.getCallback(o)
	},
	bindRegister({commit}, o){
		return apiUser.getBindRegister(o)
	},
	userLogout({ commit }, o){
		commit(USER_LOGOUT, o)
	},
	setUserId({ commit }, o){
		if(o && o.type){
			return apiUser.getUserid()
		}else{
			apiUser.getUserid().then(res =>{
				commit(USER_ID, res)
			})
		}
	},
	userProfile({ commit }, o){
		if(o && o.type){
			return apiUser.getProfiles(o)
		}else{
			apiUser.getProfiles(o).then(res =>{
				commit(USER_PROFILE, res)
			})
		}
	},
	updateProfile({ commit }, o){
		apiUser.getUpdateProfile(o).then(res =>{
			commit(UPDATE_USER_PROFILE, res)
		})
	},
	userUpdateAvatar({ commit }, o){
		apiUser.getUpdateAvatar(o).then(res =>{
			commit(UPDATE_USER_PROFILE_AVATAR, res)
		})
	},
	userAddress({ commit }, o){
		apiUser.getAddress(o).then(res =>{
			commit(USER_ADDRESS, res)
		})
	},
	getuserAddressList({ commit }, o){
		return apiUser.getAddress(o)
	},
	userAddressDefault({ commit },o){
		apiUser.getAddressDefault(o).then(res =>{
			commit(USER_ADDRESS_DEFAULT,res)
		})
	},
	userAddressDelete({ commit },o){
		apiUser.getAddressDelete(o).then(res =>{
			commit(USER_ADDRESS_DELETE,res)
		})
	},
	userAddressInfo({ commit },o){
		return apiUser.getAddressInfo(o)
	},
	userWxImportAddress({ commit },o){
		return apiUser.getwxImportAddress(o)
	},
	setChangeConsignee({ commit },o){
		return apiUser.getChangeConsignee(o)
	},
	getPostList({ commit }, o) {
		return apiUser.getPostList(o)
	},
	getQrCode({ commit }, o) {
		return apiUser.getQrCode(o)
	},
	async userAddressAdd({ commit },o){
		return apiUser.getAddressAdd(o)
	},
	setCollectShopList({ commit },o){
		apiUser.getCollectShopList(o).then(res=>{
			commit(SHOP_COLLECT_LIST,res)
		})
	},
	setCollectShop({ commit }, o){
		apiUser.getCollectShop(o).then(res=>{
			commit(SHOP_COLLECT,res)
		})
	},
	followCollectShopById({ commit }, o){
		return apiUser.getCollectShop(o)
	},
	setCollectGoodsList({ commit }, o){
		apiUser.getCollectGoodsList(o).then(res=>{
			commit(GOODS_COLLECT_LIST,res)
		})
	},
	setCollectGoods({ commit }, o){
		apiUser.getCollectGoods(o).then(res=>{
			commit(GOODS_COLLECT,res)
		})
	},
	collectgoodsById({ commit }, o) {
		return apiUser.getCollectGoods(o)
	},
	setOrderList({ commit }, o){
		apiUser.getOrderList(o).then(res=>{
			commit(USER_ORDER_LIST,res)
		})
	},
	setOrderDetail({ commit }, o){
		apiUser.getOrderDetail(o).then(res=>{
			commit(USER_ORDER_DETAIL,res)
		})
	},
	setOrderHandler({ commit }, o){
		return apiUser.getOrderHandler(o)
	},
	setReceivedOrder({ commit }, o){
		return apiUser.getReceivedOrder(o)
	},
	setbuyAgain({ commit }, o){
		return apiUser.getbuyAgain(o)
	},
	setDeleteOrder({ commit }, o){
		return apiUser.getDeleteOrder(o)
	},
	setRestoreOrder({ commit }, o){
		return apiUser.getRestoreOrder(o)
	},
	setDelayOrder({ commit }, o){
		return apiUser.getDelayOrder(o)
	},
	setHistoryAdd({ commit }, o){
		return apiUser.getHistoryAdd(o)
	},
	setHistory({ commit }, o){
		apiUser.getHistory(o).then(res=>{
			commit(USER_HISTORY,res)
		})
	},
	async setHistoryDelete({ commit }, o){
		apiUser.getHistoryDelete(o).then(res=>{
			commit(USER_HISTORY_DELETE,{res,o})
		})
	},
	userUpdateText({ commit }, o){
		commit(USER_UPDATE_TEXT, o)
	},
	setRefoundList({ commit }, o){
		apiUser.getRefoundList(o).then(res=>{
			commit(REFOUND_LIST, res)
		})
	},
	setOrderRefound({ commit }, o){
		apiUser.getOrderRefound(o).then(res=>{
			commit(ORDER_REFOUND, res)
		})
	},
	setApplyRefound({ commit }, o){
		apiUser.getApplyRefound(o).then(res=>{
			commit(APPLY_REFOUND,res)
		})
	},
	setReturnDatail({ commit }, o){
		apiUser.getReturnDatail(o).then(res=>{
			commit(REFOUND_DETAIL,res)
		})
	},
	setUserCoupon({ commit }, o){
		apiUser.getUserCoupon(o).then(res=>{
			commit(USER_COUPON,res)
		})
	},
	setMaterial({ commit }, o){
		if(o.type){
			return apiUser.getMaterial(o)
		}else{
			apiUser.getMaterial(o).then(res=>{
				commit(UPLOAD_LIST,res)
			})
		}
	},
	setDeleteImg({ commit }, o){
		commit(DELETE_MATERIAl_IMG,o)
	},
	setCommentList({ commit }, o){
		apiUser.getCommentList(o).then(res=>{
			commit(COMMENT_LIST,res)
		})
	},
	setAddgoodscomment({ commit }, o){
		return apiUser.getAddgoodscomment(o)
	},
	setAddcomment({ commit }, o){
		apiUser.getAddcomment(o).then(res=>{
			commit(COMMENT_INFO,res)
		})
	},
  	setArticleHelp({ commit },o){
	    apiUser.getArticleHelp(o).then(res=>{
	    	commit(ARTICLE_HELP,res)
	    })
	},
	setAccount({ commit }){
		return apiUser.getAccount()
	},
	 // 红包
    setBonusList({commit}, o) {
        apiUser.bonusList(o).then(res => {
            commit(USER_BONUS_LIST, res)
        })
    },
    // 添加红包
    setAddBonus({commit}, o) {
        return apiUser.addBonus(o)
    },
    //储值卡列表
    setValueCard({ commit }, o){
    	apiUser.valueCardList(o).then(res => {
            commit(VALUE_CARD_LIST, res)
        })
    },
	getValueCard({ commit }, o){
		return apiUser.valueCardList(o)
	},
    //储值卡领取
    setAddValueCard({ commit }, o){
    	return apiUser.addValueCard(o)
    },
    //储值卡详情
    setValueCardInfo({ commit }, o){
    	apiUser.valueCardInfo(o).then(res => {
            commit(VALUE_CARD_INFO, res)
        })
    },
    //充值储值卡
    setDepositValueCard({ commit }, o){
    	return apiUser.getDepositValueCard(o)
    },
	//手机商家入驻
	setMerchants({ commit },o){
		apiUser.getMerchants(o).then(res =>{
			commit(MERCHANTS_INFO, res)
		})
	},
	getMerchantsHandle({ commit },o){
		return apiUser.getMerchants(o)
	},
	setMerchantsGuide({ commit }){
		apiUser.getMerchantsGuide().then(res =>{
			commit(MERCHANTS_GUIDE, res)
		})
	},
	getMerchantsGuideHandle(){
		return apiUser.getMerchantsGuide()
	},
	getMerchantsInfo({ commit },o){
		return apiUser.getMerchantsInfo(o)
	},
	setMerchantsAgree({ commit }, o){
		return apiUser.getMerchantsAgree(o)
	},
	setMerchantsAgreePersonal({ commit }, o){
		return apiUser.getMerchantsAgreePersonal(o)
	},
	setMerchantsShop({ commit }){
		apiUser.getMerchantsShop().then(res=>{
			commit(MERCHANTS_CATEGORY, res)
		})
	},
	setMerchantsChildCate({commit},o){
		apiUser.getMerchantsChildCate(o).then(res=>{
			commit(MERCHANTS_CHILD_CATE, res)
		})
	},
	setMerchantsAddCate({commit},o){
		// apiUser.getMerchantsAddCate(o).then(res =>{
		// 	commit(MERCHANTS_ADD_CATE, res)
		// })
		return apiUser.getMerchantsAddCate(o)
	},
	setMerchantsDeleteCate({commit},o){
		apiUser.getMerchantsDeleteCate(o).then(res=>{
			commit(MERCHANTS_DELETE_CATE, {o,res})
		})
	},
	setMerchantsAddShop({commit},o){
		return apiUser.getMerchantsAddShop(o)
	},
	setMerchantsAudit({commit}){
		apiUser.getMerchantsAudit().then(res=>{
			commit(MERCHANTS_AUDIT, res)
		})
	},
	getMerchantsAuditHandle() {
		return apiUser.getMerchantsAudit()
	},
}

const getters = {
	
}

export default {
	state,
	mutations,
	actions,
	getters
}