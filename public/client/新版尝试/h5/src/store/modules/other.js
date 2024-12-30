import {
  SET_EXCHANGE_INDEX,
  SET_EXCHANGE_DETAIL,
  SET_GROUPBUY_INDEX,
  SET_GROUPBUY_DETAIL,
  WEB_COUPON,
  SECKILL_TIME,
  SECKILL,
  SECKILL_DETAIL,
  PRESALE,
  PRESALE_LIST,
  PRESALE_DETAIL,
  PRESALE_PROPERT,
  PACKAGE_LIST,
  SUPPLIER,
  SUPPLIER_HOME,
  SUPPLIER_CATEGORY,
  SUPPLIER_SEARCH_LIST,
  SUPPLIER_LIMIT_TIME,
  SUPPLIER_FLOOR,
  SUPPLIER_GOODS_LIST,
  SUPPLIER_DETAIL,
  SUPPLIER_CART,
  SUPPLIER_CHECKOUT,
  SUPPLIER_UPDATE_CART,
  SUPPLIER_CART_DELETE,
  SUPPLIER_DONE_INFO,
  SUPPLIER_PURCHASE_LIST,
  SUPPLIER_PURCHASE_INFO,
  UPDETA_CHECK_SUPPLIER,
  SUPPLIER_REFOUND_LIST,
  SUPPLIER_ORDER_REFOUND,
  SUPPLIER_APPLY_REFOUND,
  SUPPLIER_REFOUND_DETAIL,
  SUPPLIER_SHOP_HOME,
  SUPPLIER_HOME_LIST,
  BONUS_LIST,
  SECKILL_ATTR
} from '../mutation-type.js'

import Vue from 'vue'

import {
  apiOther
} from '@/config'

import {
    Toast
} from 'vant'

const state = {
    status: '',
    page:1,
	size:5,
    exchangeIndexData:[],
    exchangeDetailData:[],
    groupbuyIndexData:{
        get_goods:[]
    },
    groupbuyDetailData:{
        goods:Object,
        price_ladder:[]
    },
    bonusData:[],
    couponData:[],
    seckillTimeData:{
        list:[],
        banner:[]
    },
    timeArr:[],
    firstId:'',
    firstStatus:true,
	oodsAttrOper:Object,
	goodsAttrOper: {},
    seckillData:[],
    seckillTime:'',
    seckillDetailData:[],
    seckillBuyData:[],
    presaleData:[],
    presaleListData:[],
    presaleDetailData:'',
    goodsAttrInit:[],
    presalePropertData:[],
    presaleBuyData:[],
    packageData:[],
    supplierData:{
        banner:[],
        category:[],
        limitTime:[],
        catFloor:[]
    },
    supplierGoodsList:[],
    supplierGoodsDetail:{},
    supplierGoodsAttrInit:[],
    supplierCartList:[],
    supplierCheckedGoods:[],
    supplierShopLength:[],
    supplierCheckoutInfo:'',
    supplierDoneInfo:'',
    supplierPayList:'',
    supplierPurchaseList:[],
    supplierPurchaseInfo:'',
    supplierRefoundList:[],
    supplierOrderRefound:'',
    supplierApplyRefoundDetail:Object,
    supplierRefoundDetail:Object,
    supplierShopDetail:Object,
    supplierShopGoodsList:[]
}
const mutations = {
    /*积分商城*/
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
    /*团购*/
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
    
    /*优惠券*/
    [WEB_COUPON](state, o) {
      if(o.page == 1){
            state.couponData = o.data;
  		}else{
            if(o.data.length != 0){
      			for(let i= 0;i<o.data.length;i++){
      				state.couponData.push(o.data[i])
      			}
            }else{
                state.couponData.push('')
            }
  		}
    },
    /*秒杀*/
    [SECKILL_TIME](state, o) {
        state.firstId = 0
        state.seckillTimeData = o.data

        for(let i in state.seckillTimeData.list){
            state.timeArr.push(state.seckillTimeData.list[i].id)
        }
        state.firstStatus = state.seckillTimeData.list[0].status
        state.firstId = state.timeArr[0]
    },
	[SECKILL_ATTR](state,o){
		state.goodsAttrOper = o.data
	},
    [SECKILL](state, o) {
        if(o.page == 1){
            state.seckillData = o.data
  		}else{
            if(o.data.length != 0){
      			for(let i= 0;i<o.data.length;i++){
      				state.seckillData.push(o.data[i])
      			}
            }else{
                state.seckillData.push('')
            }
  		}

        state.seckillTime = state.seckillData[0]
    },
    [SECKILL_DETAIL](state, o) {
        state.seckillDetailData = o.data

        state.goodsAttrInit = []

        for(let i = 0; i < state.seckillDetailData.attr.length; i++){
  			for(let j = 0; j < state.seckillDetailData.attr[i].attr_key.length;j++){
  				if(j == 0){
  					state.goodsAttrInit.push(state.seckillDetailData.attr[i].attr_key[j].goods_attr_id)
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
            if(o.data.pre_goods.length != 0){
      			for(let i= 0;i<o.data.pre_goods.length;i++){
      				state.presaleListData.push(o.data.pre_goods[i])
      			}
            }else{
               state.presaleListData.push('')        
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
    [SUPPLIER_HOME](state, o){
        state.supplierData.category = o.data.wholesale_cat;
        state.supplierData.limitTime = o.data.wholesale_limit
        state.supplierData.catFloor = o.data.wholesale_list
        state.supplierData.banner = o.data.banner
    },
    // 供应链搜索
    [SUPPLIER_SEARCH_LIST](state,o){
        if(o.page == 1){
            state.supplierGoodsList = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.supplierGoodsList.push(o.data[i])
                }
            }else{
                state.supplierGoodsList.push('')
            }
        }
    },
    // 供应链分类
    [SUPPLIER_CATEGORY](state,o){
        state.supplierData.category = o.data
    },
    // 供应链限时抢购
    [SUPPLIER_LIMIT_TIME](state,o){
        state.supplierData.limitTime = o.data
    },
    // 供应链首页楼层
    [SUPPLIER_FLOOR](state,o){
        state.supplierData.catFloor = o.data  
    },
    // 供应链列表
    [SUPPLIER_GOODS_LIST](state, o) {
        if(o.page == 1){
            state.supplierGoodsList = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.supplierGoodsList.push(o.data[i])
                }
            }else{
                state.supplierGoodsList.push('')
            }
        }
    },
    // 供应链详情
    [SUPPLIER_DETAIL](state, o) {
        state.supplierGoodsDetail = o.data

        state.supplierGoodsAttrInit = []

        if(state.supplierGoodsDetail.properties.spe.length > 1){
            let spe = state.supplierGoodsDetail.properties.spe;
            let arr = spe.slice(0,-1);
            arr.forEach(s=>{
                s.values.forEach((v,i)=>{
                    if(i === 0){
                        state.supplierGoodsAttrInit.push(v.id)
                    }
                })
            })
        }
    },
    // 供应链购物车列表
    [SUPPLIER_CART](state, o) {
        state.supplierCartList = o.data
        let arr = []
        state.supplierCartList.forEach((v,index)=>{
            arr[index] = []
            v.goods_list.forEach((goods,goodsIndex)=>{
                goods.list.forEach((attr,attrIndex)=>{
                    if(attr.is_checked == 1){
                        arr[index].push(attr.rec_id)
                    }
                })
            })
            Vue.set(state.supplierCheckedGoods, index, arr[index])
            state.supplierShopLength.push(state.supplierCheckedGoods[index].length)
        })
    },
    // 供应链购物车勾选
    [UPDETA_CHECK_SUPPLIER](state, o){
        if(!o.checked){
            Vue.set(state.supplierCheckedGoods, o.index, [])
        }else{
            let arr = []
            state.supplierCartList.forEach((v,index)=>{
                if(index == o.index){
                    v.goods_list.forEach((goods,goodsIndex)=>{
                        goods.list.forEach((attr,attrIndex)=>{
                            arr.push(attr.rec_id);
                        })
                    })
                }
            });
            Vue.set(state.supplierCheckedGoods, o.index, arr)
        }
    },
    //结算页
    [SUPPLIER_CHECKOUT](state, o){
        if(!o.error){
             state.supplierCheckoutInfo = o.data

            // o.goods_list.forEach((v)=>{
            //     if(v.goods.length > 1){
            //         state.listShow.push(true)
            //     }else{
            //         state.listShow.push(false)
            //     }
            // })
        }else{
            state.checkoutInfo = o.error
        }
    },
    [SUPPLIER_CART_DELETE](state, o){
        Toast(o.data.message)
        if(o.data.error == 0){
            state.supplierCartList = o.data.cart_info
        }
    },
    // 收银台详情
    [SUPPLIER_DONE_INFO](state, o) {
        state.supplierDoneInfo = o.data
    },
    // 供应链求购列表
    [SUPPLIER_PURCHASE_LIST](state, o) {
        if(o.page == 1){
            state.supplierPurchaseList = o.data
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.supplierPurchaseList.push(o.data[i])
                }
            }else{
                state.supplierPurchaseList.push('')
            }
        }
    },
    [SUPPLIER_PURCHASE_INFO](state,o){
        state.supplierPurchaseInfo = o.data
    },
    [SUPPLIER_REFOUND_LIST](state,o){
        if(o.page == 1){
            state.supplierRefoundList = o.data;
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.supplierRefoundList.push(o.data[i])
                }
            }else{
                state.supplierRefoundList.push('')
            }
        }
    },
    [SUPPLIER_ORDER_REFOUND](state,o){
        state.supplierOrderRefound = o.data
    },
    [SUPPLIER_APPLY_REFOUND](state,o){
        state.supplierApplyRefoundDetail = o.data
    },
    [SUPPLIER_REFOUND_DETAIL](state,o){
        state.supplierRefoundDetail = o.data
    },
    [SUPPLIER_SHOP_HOME](state,o){
        state.supplierShopDetail = o.data
    },
    [SUPPLIER_HOME_LIST](state,o){
        if(o.page == 1){
            state.supplierShopGoodsList = o.data;
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.supplierShopGoodsList.push(o.data[i])
                }
            }else{
                state.supplierShopGoodsList.push('')
            }
        }
    },
    /*红包*/
    [BONUS_LIST](state, o) {
        if(o.page == 1){
            state.bonusData = o.data;
        }else{
            if(o.data.length != 0){
                for(let i= 0;i<o.data.length;i++){
                    state.bonusData.push(o.data[i])
                }
            }else{
                state.bonusData.push('')
            }
        }
    },
}
const actions = {
    /**积分商城 */
    setExchangeIndex({commit}, o) {
        apiOther.exchangeIndex(o).then(res => {   
            commit(SET_EXCHANGE_INDEX, res)
        })
    },
    /**积分详情 */
    setExchangeDetail({commit}, o) {
        apiOther.exchangeDetail(o).then(res => {   
            commit(SET_EXCHANGE_DETAIL, res)
        })
    },
    /* 积分buy */
    setExchangeBuy({commit}, o) {
        return apiOther.getExchangeBuy(o)
    },
    /**团购 */
    setGroupbuyIndex({commit}, o) {
        apiOther.groupbuyIndex(o).then(res => {   
            commit(SET_GROUPBUY_INDEX, res)
        })
    },
    /**团购详情 */
    setGroupbuyDetail({commit}, o) {
        apiOther.groupbuyDetail(o).then(res => {   
            commit(SET_GROUPBUY_DETAIL, res)
        })
    },
    /* 团购buy */
    setGroupBuy({commit}, o) {
        return apiOther.groupBuy(o)
    },
    // 优惠券
    setWebCoupon({commit}, o) {
        apiOther.getWebCoupon(o).then(res => {
            commit(WEB_COUPON, res)
        })
    },
    // 优惠券 任务集市
    setWebTaskCoupon({ commit }, o){
        apiOther.getWebTaskCoupon(o).then(res => {
            commit(WEB_COUPON, res)
        })
    },
    setBonus({ commit }, o){
        apiOther.getBonus(o).then(res => {
            commit(BONUS_LIST, res)
        })
    },
    receiveBonus({ commit }, o){
        return apiOther.getReceiveBonus(o)
    },
    // 秒杀首页头部tabs时间
    setSeckillTime({commit}) {
        apiOther.seckillTime().then(res => {
            commit(SECKILL_TIME, res)
        })
    },
    // 秒杀某个时间段商品列表
    setSeckill({commit}, o) {
        apiOther.seckill(o).then(res => {
            commit(SECKILL, res)
        })
    },
    // 秒杀详情
    setSeckillDetail({commit}, o) {
        apiOther.seckillDetail(o).then(res => {
            commit(SECKILL_DETAIL, res)
        })
    },
	setSeckillAttrPrice({ commit }, o){
		apiOther.getSeckillAttrPrice(o).then(res =>{
			commit(SECKILL_ATTR,res)
		})
	},
    // 秒杀购买
    setSeckillBuy({commit}, o) {
        return apiOther.seckillBuy(o)
    },
    // 预售
    setPresale({commit}, o) {
        apiOther.presale(o).then(res => {
            commit(PRESALE, res)
        })
    },
    // 预售列表
    setPresaleList({commit}, o) {
        apiOther.presaleList(o).then(res => {
            commit(PRESALE_LIST, res)
        })
    },
    // 预售新品
    setPresaleNew({commit}, o) {
        apiOther.presaleNew(o).then(res => {
            commit(PRESALE_LIST, res)
        })
    },
    // 预售详情
    setPresaleDetail({commit}, o) {
        apiOther.presaleDetail(o).then(res => {
            commit(PRESALE_DETAIL, res)
        })
    },
    // 预售属性
    setPresalePropert({commit}, o) {
        apiOther.presalePropert(o).then(res => {
            commit(PRESALE_PROPERT, res)
        })
    },
    // 预售购买
    setPresaleBuy({commit}, o) {
        return apiOther.presaleBuy(o)
    },
    // 超级礼包
    setPackageList({commit}, o) {
        apiOther.packageList(o).then(res => {
            commit(PACKAGE_LIST, res)
        })
    },
    // 供应链入口
    setSupplier({commit}, o) {
        return apiOther.supplier(o)
    },
    //供应链首页
    setSupplierHome({commit}, o){
        apiOther.supplierHome(o).then(res => {
            commit(SUPPLIER_HOME, res)
        })
    },
    // 供应链搜索
    setSupplierSearch({commit}, o) {
        apiOther.supplierSearch(o).then(res => {
            commit(SUPPLIER_SEARCH_LIST, res)
        })
    },
    // 供应链分类
    setSupplierCategory({commit}){
        apiOther.supplierCategory().then(res => {
            commit(SUPPLIER_CATEGORY,res)
        })
    },
    // 供应链限时抢购
    setSupplierLimitTime({commit}){
        apiOther.supplierLimitTime().then(res => {
            commit(SUPPLIER_LIMIT_TIME,res)
        })
    },
    setSupplierFloor({commit}){
        apiOther.supplierFloor().then(res => {
            commit(SUPPLIER_FLOOR,res)
        })
    },
    // 供应链列表
    setSupplierGoodsList({commit}, o) {
        apiOther.supplierGoodsList(o).then(res => {
            commit(SUPPLIER_GOODS_LIST, res)
        })
    },
    // 供应链详情
    setSupplierDetail({commit}, o) {
        apiOther.supplierGoods(o).then(res => {
            commit(SUPPLIER_DETAIL, res)
        })
    },
    setUpdetaCheckSupplier({commit}, o){
        commit(UPDETA_CHECK_SUPPLIER, o)
    },
    setCheckedSupplier({commit},o){
        return apiOther.checkedSupplier(o)
    },
    // 加入供应链购物车
    setSupplierAddtocart({commit}, o) {
        return apiOther.supplierAddtocart(o)
    },
    setSupplierCart({commit}, o) {
        apiOther.supplierCart(o).then(res => {
            commit(SUPPLIER_CART, res)
        })
    },
    setSupplierUpdatecart({commit}, o) {
        return apiOther.supplierUpdateCart(o)
    },
    setSupplierCartDelete({commit}, o){
        apiOther.supplierCartDelete(o).then(res=>{
            commit(SUPPLIER_CART_DELETE,res)
        })
    },
    setSupplierFlows({commit}, o){
        return apiOther.supplierFlow(o)
    },
    setSupplierCheckout({commit},o){
        apiOther.supplierFlow(o).then(res=>{
            commit(SUPPLIER_CHECKOUT, res)
        })  
    },
    // 提交
    setSupplierDone({commit}, o) {
        return apiOther.supplierDone(o)
    },
    setSupplierDoneInfo({commit}, o) {
        apiOther.supplierDoneInfo(o).then(res => {
            commit(SUPPLIER_DONE_INFO, res)
        })
    },
    setSupplierDoneInfoBalance({commit}, o) {
        apiOther.supplierDoneInfoBalance(o).then(res => {
            commit(SUPPLIER_DONE_INFO, res)
        })
    },
    setSupplierPayTab({commit}, o) {
        return apiOther.supplierPayTab(o)
    },
    setSupplierPurchaseList({commit}, o) {
        apiOther.supplierPurchaseList(o).then(res => {
            commit(SUPPLIER_PURCHASE_LIST, res)
        })
    },
    setSupplierPurchaseInfo({commit}, o){
        apiOther.supplierPurchaseInfo(o).then(res => {
            commit(SUPPLIER_PURCHASE_INFO,res )
        })
    },
    setSupplierRefoundList({ commit }, o){
        apiOther.getSupplierRefoundList(o).then(res=>{
            commit(SUPPLIER_REFOUND_LIST, res)
        })
    },
    setSupplierOrderRefound({ commit }, o){
        apiOther.getSupplierOrderRefound(o).then(res=>{
            commit(SUPPLIER_ORDER_REFOUND, res)
        })
    },
    setSupplierApplyRefound({ commit }, o){
        apiOther.getSupplierApplyRefound(o).then(res=>{
            commit(SUPPLIER_APPLY_REFOUND,res)
        })
    },
    setSupplierReturnDatail({ commit }, o){
        apiOther.getSupplierReturnDatail(o).then(res=>{
            commit(SUPPLIER_REFOUND_DETAIL,res)
        })
    },
    setSupplierShopHome({ commit }, o){
        apiOther.getSupplierShopHome(o).then(res=>{
            commit(SUPPLIER_SHOP_HOME,res)
        })
    },
    setSupplierHomeList({ commit }, o){
        apiOther.getSupplierHomeList(o).then(res=>{
            commit(SUPPLIER_HOME_LIST,res)
        })
    },
    //客服
    setChat({commit}, o){
        return apiOther.getChat(o)
    }
}
export default {
    state,
    mutations,
    actions,
}
