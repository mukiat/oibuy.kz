import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

// 首页
function getHome(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/app/home', o).then(res=>{
			uni.hideLoading();
			reslove(res)
		})
	})
}

//店铺街分类列表
function getShopCatList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/shop/catlist',o).then(res=>{
			reslove(res)
		})
	})
}

//店铺街分类下店铺列表
function getShopList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/shop/catshoplist',o).then(res=>{
			reslove({
				data:res.data,
				size:o.size,
				page:o.page
			})
		})
	})
}

//店铺详情
function getShopDetail(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/shop/shopdetail',o).then(res=>{
			reslove(res)
		})
	})
}

//店铺商品列表
function getShopGoodsList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/shop/shopgoodslist',o).then(res=>{
			reslove(res)
		})
	})
}

//店铺map
function getShopMap(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/shop/map',o).then(res=>{
			reslove(res.data)
		})
	})
}

function getVisualStorein(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/visual/storein',o).then(res=>{
			reslove(res)
		})
	})
}

function getVisualAddcollect(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/visual/addcollect',o).then(res=>{
			reslove(res)
		})
	})
}

export default{
	getHome,
	getShopCatList,
	getShopList,
	getShopDetail,
	getShopGoodsList,
	getShopMap,
	getVisualStorein,
	getVisualAddcollect
}
