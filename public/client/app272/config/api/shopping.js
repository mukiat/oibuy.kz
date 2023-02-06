import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

/* 加入购物车 */
function getAddCart(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/add',o).then(res=>{
			reslove(res)
		})
	})
}

/* 超值礼包加入购物 */
function getAddPackageCart(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/addpackage',o).then(res=>{
			reslove(res.data)
		})
	})
}

/* 购物车商品列表 */
function getCartList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/goodslist',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车商品删除 */
function getCartGoodsDelete(o) {
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/deletecart',o).then(res=>{
			reslove(res.data)
		})
	})
}

/* 购物车商品批量删除 */
function getCartBatchDelete(o) {
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/clearcart',o).then(res=>{
			reslove(res.data)
		})
	})
}

/* 购物车默认进来访问 */
function getCartDefault(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/cartvalue',o).then(res=>{
			reslove({
				data:res.data,
				rec_id:o.rec_id,
				act_id:o.act_id
			})
		})
	})
}

/* 购物车勾选商品 */
function getCartChecked(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/checked',o).then(res=>{
			reslove({
				data:res.data,
				rec_id:o.rec_id
			})
		})
	})
}

/* 购物车商品数量修改 */
function getUpdateNumber(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/update',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车赠品列表 */
function getGiftList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/giftlist',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车赠品选择 */
function getGiftChecked(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/addGiftCart',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车促销活动列表 */
function getFavourable(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/getfavourable',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车促销活动切换 */
function getChangefav(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/changefav',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车优惠券 */
function getCoupons(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/getCoupons',o).then(res=>{
			reslove(res)
		})
	})
}

/* 凑单商品列表 */
function getCoudanGoodsList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/activity/goods',o).then(res=>{
			reslove({
				data:res.data,
				size:o.size,
				page:o.page
			})
		})
	})
}

/* 凑单信息 */
function getCoudan(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/activity/coudan',o).then(res=>{
			reslove(res)
		})
	})
}

/* 购物车推荐商品 */
function getGoodsGuess(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/goods/goodsguess',o).then(res=>{
			reslove({
				data:res.data,
				size:o.size,
				page:o.page
			})
		})
	})
}

/* 门店购买 */
function getStoresCart(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/cart/add',o).then(res=>{
			reslove({
				data:res.data,
				store_id:o.store_id
			})
		})
	})
}

/* 获取配送方式 */
function getShipping(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/shipping',o).then(res=>{
			reslove(res)
		})
	})
}

/* 获取商品运费 */
function getShippingFee(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/shipping/goodsshippingfee',o).then(res=>{
			reslove(res)
		})
	})
}

/* 结算页面信息 */
function getShoppingCheckout(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/trade/orderinfo',o).then(res=>{
			reslove(res.data)
		})
	})
}

/* 结算页面提交 */
function getCheckoutSubmit(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/trade/done',o).then(res=>{
			if(res.status == 'success'){
				if(res.data.error == 1){
					reslove(res)
				}else{
					uni.showToast({
						title:'提交成功',
						icon:'success'
					})
					reslove(res)
				}
			}else{
				uni.showToast({
					title: res.errors.message || '提交失败',
					icon:'none'
				})
			}
		})
	})
}

/* 在线支付 支付方式列表 */
function getDoneInfo(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/trade/paycheck',o).then(res=>{
			reslove(res.data)
		})
	})
}

/* 余额支付 */
function getDoneInfoBalance(o){

}

/* 支付方式列表 */
function getPayList(o){

}

/* 在线支付 支付方式选择 */
function getPayTab(o){

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
