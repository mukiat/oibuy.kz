import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

function getGoodsInfo(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/goods/show',o).then(res=>{
			reslove(res)
		})
	})
}

function getGoodsCouponList(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/coupon/goods',o).then(res=>{
			reslove(res)
		})
	})
}

function getGoodsCouponReceive(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/coupon/receive',o).then(res=>{
			reslove(res)
		})
	})
}

function getGoodsAttrOper(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/goods/attrprice',o).then(res=>{
			reslove(res)
		})
	})
}

function getGoodsComment(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/comment/goods',o).then(res=>{
			reslove(res)
		})
	})
}

/* 分享生成分享图片 */
function getGoodsShare(o){
	return new Promise((reslove, reject) => {
		if(o.platform == 'MP-WEIXIN'){
			request.post(webUrl + '/api/wxapp/shareposter',o).then(res=>{
				reslove(res)
			})
		}else{
			request.post(webUrl + '/api/goods/shareposter',o).then(res=>{
				reslove(res)
			})
		}
	})
}

/* 商品组合购买，配件列表 */
function getFitting(o){

}

/* 组合套餐价格 */
function getFittingPrice(o){

}

/* 选中配件 */
function getAddToCartCombo(o){

}

/* 取消配件 */
function getDelInCartCombo(o){

}

/* 配件加入购物车 */
function getAddToCartGroup(o){

}

function getCommentTotal(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/comment/title',o).then(res=>{
			reslove(res)
		})
	})
}

function getDiscoverList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/discover/commentlist',o).then(res=>{
			reslove(res)
		})
	})
}

/* 关联商品 */
function getLinkGoods(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/goods/linkgoods',o).then(res=>{
			reslove(res)
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
	getAddToCartGroup,
	getCommentTotal,
	getDiscoverList,
	getLinkGoods
}
