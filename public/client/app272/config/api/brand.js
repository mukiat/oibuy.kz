import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

/* 品牌首页 */
function getBrand(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/brand',o).then(res=>{
			reslove(res.data)
		})
    })
}
/* 品牌详情 */
function getBrandDetail(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/brand/detail', o).then(res=>{
			reslove(res.data)
		})
    })
}
/* 品牌商品 */
function getBrandProduct(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/brand/goodslist', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
/* 品牌列表 */
function getBrandList(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/brand/brandlist', o).then(res=>{
			reslove(res)
		})
    })
}

export default {
	getBrand,
	getBrandList,
	getBrandDetail,
	getBrandProduct
}
