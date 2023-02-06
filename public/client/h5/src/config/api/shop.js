import qs from 'qs'
import axios from 'axios'

//店铺街分类列表
function getShopCatList(o){
	return new Promise((reslove,reject) => {
		axios.post(`${window.ROOT_URL}api/shop/catlist`).then(res => {
			reslove(res.data)
		}).catch(err => {
			reject(err)
		})
	})
}

//店铺街分类下店铺列表
function getShopList(o){
	return new Promise((reslove,reject) => {
		axios.post(`${window.ROOT_URL}api/shop/catshoplist`,qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				size:o.size,
        		page:o.page
			})
		}).catch(err => {
			reject(err)
		})
	})
}

//店铺详情
function getShopDetail(o){
	return new Promise((reslove,reject) => {
		axios.post(`${window.ROOT_URL}api/shop/shopdetail`,qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(err => {
			reject(err)
		})
	})
}

//店铺商品列表
function getShopGoodsList(o){
	return new Promise((reslove,reject) => {
		axios.post(`${window.ROOT_URL}api/shop/shopgoodslist`,qs.stringify(o)).then(res => {
			reslove({
				data:res.data.data,
				size:o.size,
        		page:o.page
			})
		}).catch(err => {
			reject(err)
		})
	})
}

//店铺map
function getShopMap(o){
	return new Promise((reslove,reject) => {
		axios.get(`${window.ROOT_URL}api/shop/map`,{ params:o }).then(res => {
			reslove(res.data)
		}).catch(err => {
			reject(err)
		})
	})
}


export default{
	getShopCatList,
	getShopList,
	getShopDetail,
	getShopGoodsList,
	getShopMap
}
