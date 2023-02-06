import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

// 查看发票详情
function getInvoiceDetail(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/shouqianba/invoice/detail',o).then(res=>{
			reslove(res)
		})
	})
}

// 换开申请发票详情
function getInvoiceReapplyInfo(o) {
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/shouqianba/invoice/reapplyInfo',o).then(res=>{
			reslove(res)
		})
	})
}

/* 换开发票提交 */
function invoiceReapply(o) {
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/shouqianba/invoice/reapply',o).then(res=>{
			reslove(res)
		})
	})
}

export default{
	getInvoiceDetail,
	getInvoiceReapplyInfo,
	invoiceReapply,
}