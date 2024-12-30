import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
	Toast,
	Dialog
} from 'vant'

/* 加入购物车 */
function getAddCart(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/add`, qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 超值礼包加入购物 */
function getAddPackageCart(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/addpackage`, qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车商品列表 */
function getCartList(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/goodslist`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车商品删除 */
function getCartGoodsDelete(o) {
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/deletecart`, qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车商品批量删除 */
function getCartBatchDelete(o) {
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/clearcart`, qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车默认进来访问 */
function getCartDefault(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/cartvalue`, qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				rec_id:o.rec_id,
				act_id:o.act_id
			})
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车勾选商品 */
function getCartChecked(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/checked`, qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				rec_id:o.rec_id
			})
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车商品数量修改 */
function getUpdateNumber(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/update`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车赠品列表 */
function getGiftList(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/giftlist`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车赠品选择 */
function getGiftChecked(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/addGiftCart`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车促销活动列表 */
function getFavourable(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/getfavourable`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车促销活动切换 */
function getChangefav(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/changefav`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车优惠券 */
function getCoupons(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/getCoupons`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 凑单商品列表 */
function getCoudanGoodsList(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/activity/goods`, qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				size:o.size,
                page:o.page
			})
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 凑单信息 */
function getCoudan(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/activity/coudan`, {params:o}).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 购物车推荐商品 */
function getGoodsGuess(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/goods/goodsguess`, qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				size:o.size,
                page:o.page
			})
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 门店购买 */
function getStoresCart(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/add`, qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				store_id:o.store_id
			})
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 获取商品运费 */
function getShippingFee(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/shipping/goodsshippingfee`, { params:o }).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 获取配送方式 */
function getShipping(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/shipping`, { params:o }).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 结算页面信息 */
function getShoppingCheckout(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/trade/orderinfo`,qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 结算页面提交 */
function getCheckoutSubmit(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/trade/done`,qs.stringify(o)).then(res => {
			if(res.data.status == 'success'){
				reslove(res.data)
			}else{				
				if(res.data.errors.message){
					Toast(res.data.errors.message);
				}else{
					Toast(i18n.t('lang.submit_fail'));
				}
				return
			}
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 在线支付 支付方式列表 */
function getDoneInfo(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/trade/paycheck`,qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 余额支付 */
function getDoneInfoBalance(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/trade/balance`,qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 支付方式列表 */
function getPayList(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/payment/list`,{ params:o }).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 在线支付 支付方式选择 */
function getPayTab(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/payment/change_payment`,{ params:o }).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

export default{
	getAddCart,
	getAddPackageCart,
	getCartList,
	getCartGoodsDelete,
	getCartBatchDelete,
	getCartDefault,
	getCartChecked,
	getUpdateNumber,
	getGiftList,
	getGiftChecked,
	getFavourable,
	getChangefav,
	getCoupons,
	getCoudanGoodsList,
	getCoudan,
	getGoodsGuess,
	getStoresCart,
	getShipping,
	getShippingFee,
	getShoppingCheckout,
	getCheckoutSubmit,
	getDoneInfo,
	getDoneInfoBalance,
	getPayList,
	getPayTab
}
