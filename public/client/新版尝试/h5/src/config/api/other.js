import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
    Toast
}from 'vant'

//积分模块
async function exchangeIndex(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/exchange`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//积分详情
async function exchangeDetail(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/exchange/detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}

//积分提交
function getExchangeBuy(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/exchange/buy`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}

//团购模块
async function groupbuyIndex(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/group_buy`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//团购详情
async function groupbuyDetail(o) {
    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/group_buy/detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        })
    })
}

//团购购买
async function groupBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/group_buy/buy`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}


//优惠券 好券集市
async function getWebCoupon(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/coupon`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//优惠券 任务集市
async function getWebTaskCoupon(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/coupon/couponsgoods`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}

//领取红包列表
function getBonus(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bonus`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}

//领取红包
function getReceiveBonus(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/bonus/receive`, qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}

//秒杀
async function seckillTime() {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/seckill/time`).then(res => {
            reslove(res.data)
        })
    })
}
//秒杀列表
async function seckill(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/seckill`, {
            params: o
        }).then(({ data:{ data } }) => {
            reslove({
                data:data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//秒杀详情
async function seckillDetail(o) {
    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/seckill/detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        })
    })
}
async function getSeckillAttrPrice(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/seckill/attr_price`,qs.stringify(o)).then(res =>{
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}
//秒杀购买
async function seckillBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/seckill/buy`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//预售
async function presale(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/presale`).then(res => {
            reslove(res.data)
        })
    })
}
//预售列表
async function presaleList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/presale/list`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//预售列表
async function presaleNew(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/presale/new`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//预售详情
async function presaleDetail(o) {
    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/presale/detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        })
    })
}
//预售属性
async function presalePropert(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/presale/price`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//预售购买
async function presaleBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/presale/buy`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//超级礼包
async function packageList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/package/list`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            });
        })
    })
}
/* 供应链 权限入口 */
async function supplier(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers`).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 首页 */
async function supplierHome(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/show`).then(res => {
            reslove(res.data)
        })
    })
}

/* 供应链 搜索 */
async function supplierSearch(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/search`,{
            params:o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            });
        }).catch(err =>{
            console.error(err)
        })
    })
}

/* 供应链 分类 */
async function supplierCategory() {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/category`).then(res => {
            reslove(res.data)
        }).catch(err =>{
            console.error(err)
        })
    })
}

/* 供应链 限时抢购 */
async function supplierLimitTime() {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/getlimit`).then(res => {
            reslove(res.data)
        }).catch(err =>{
            console.error(err)
        })
    })
}

/* 供应链 楼层 */
async function supplierFloor() {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/catgoods`).then(res => {
            reslove(res.data)
        }).catch(err =>{
            console.error(err)
        })
    })
}

/* 供应链 商品列表 */
async function supplierGoodsList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/goodslist`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            });
        })
    })
}
/* 供应链 商品详情*/
async function supplierGoods(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 加入购物车*/
async function supplierAddtocart(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/addtocart`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 购物车列表*/
async function supplierCart(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/cart`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
async function supplierDone(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/done`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 购物车选中*/
async function checkedSupplier(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/checked`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 购物车更新数量*/
async function supplierUpdateCart(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/updatecart`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 购物车删除*/
async function supplierCartDelete(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/clearcart`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 购物车提交*/
async function supplierFlow(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/flow`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 收银台 */
async function supplierDoneInfo(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/paycheck`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链 收银台 余额支付 */
async function supplierDoneInfoBalance(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/balance`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链支付方式切换 */
async function supplierPayTab(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/change_payment`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
/* 供应链求购列表 */
async function supplierPurchaseList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/purchase/list`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            });
        })
    })
}
/* 供应链求购详情 */
async function supplierPurchaseInfo(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/purchase/info`, {
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}

/* 退换货申请列表 */
function getSupplierRefoundList(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/returnorderlist`,{params: o}).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}

/* 单个订单商品退换货列表 */
function getSupplierOrderRefound(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/goodsorder`,{params: o}).then(res => {
            reslove(res.data)
        })
    })
}

/* 申请退换货 */
function getSupplierApplyRefound(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/applyreturn`,{params: o}).then(res => {
            reslove(res.data)
        })
    })
}

/* 退换货详情 */
function getSupplierReturnDatail(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/returnorderdetail`,{params: o}).then(res => {
            reslove(res.data)
        })
    })
}

/* 供应链店铺首页 */
function getSupplierShopHome(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/supplierhome`,{params: o}).then(res => {
            reslove(res.data)
        })
    })
}

/* 供应链店铺商品列表 */
function getSupplierHomeList(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/homelist`,{params: o}).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}

/* 客服接口 */
function getChat(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/chat`, { params:o }).then(res=>{
            reslove(res.data)
        }).catch(err => {
            reject(err)
        })
    })
}

export default {
    exchangeIndex,
    exchangeDetail,
    getExchangeBuy,
    groupbuyIndex,
    groupbuyDetail,
    groupBuy,
    getWebCoupon,
    getWebTaskCoupon,
    getBonus,
    getReceiveBonus,
    seckillTime,
    seckill,
    seckillDetail,
    seckillBuy,
    presale,
    presaleList,
    presaleNew,
    presaleDetail,
    presalePropert,
    presaleBuy,
    packageList,
    supplier,
    supplierHome,
    supplierSearch,
    supplierCategory,
    supplierLimitTime,
    supplierFloor,
    supplierGoodsList,
    supplierGoods,
    supplierAddtocart,
    supplierCart,
    supplierDone,
    checkedSupplier,
    supplierUpdateCart,
    supplierCartDelete,
    supplierFlow,
    supplierDoneInfo,
    supplierDoneInfoBalance,
    supplierPayTab,
    supplierPurchaseList,
    supplierPurchaseInfo,
    getSupplierRefoundList,
    getSupplierOrderRefound,
    getSupplierApplyRefound,
    getSupplierReturnDatail,
    getSupplierShopHome,
    getSupplierHomeList,
    getChat,
	getSeckillAttrPrice
}
