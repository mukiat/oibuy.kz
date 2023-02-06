import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

function getCategoryList(o){
	let url = '';
	if(o.id){
        url = webUrl  + '/api/catalog/list/' + o.id
    }else{
        url = webUrl + '/api/catalog/list'
    }

	return new Promise((reslove, reject) => {
		request.get(url).then(res=>{
			reslove(res)
		})
	})
}

function getCategoryInfo(o){
}

function getGoodsList(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/catalog/goodslist',o).then(res=>{
			reslove(res)
		})
	})
}

export default{
	getCategoryList,
	getCategoryInfo,
    getGoodsList
}
