import qs from 'qs'
import axios from 'axios'

function getGoodsInfo(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/goods/show`, qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

function getGoodsCouponList(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/coupon/goods`,{ params:o }).then(res =>{
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

function getGoodsCouponReceive(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/coupon/receive`,qs.stringify(o)).then(res =>{
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

function getGoodsAttrOper(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/goods/attrprice`,qs.stringify(o)).then(res =>{
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

function getGoodsComment(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/comment/goods`, qs.stringify(o)).then(res => {
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

/* 分享生成分享图片 */
function getGoodsShare(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/goods/shareposter`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 商品组合购买，配件列表 */
function getFitting(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/goods/fittings`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 组合套餐价格 */
function getFittingPrice(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/goods/fittingprice`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 选中配件 */
function getAddToCartCombo(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/addToCartCombo`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 取消配件 */
function getDelInCartCombo(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/delInCartCombo`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}

/* 配件加入购物车 */
function getAddToCartGroup(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/cart/addToCartGroup`, qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err =>{
			console.error(err)
		})
	})
}


export default {
	getGoodsInfo,
	getGoodsCouponList,
	getGoodsCouponReceive,
	getGoodsAttrOper,
	getGoodsComment,
	getGoodsShare,
	getFitting,
	getFittingPrice,
	getAddToCartCombo,
	getDelInCartCombo,
	getAddToCartGroup
}
