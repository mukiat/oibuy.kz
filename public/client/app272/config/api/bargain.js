import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

async function bargainIndexs(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain').then(res=>{
			reslove(res)
		})
    })
}
async function bargainGoods(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain/goods', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
async function bargainDetail(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain/detail', o).then(res=>{
			reslove(res)
		})
    })
}
//砍价属性
async function bargainProperty(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/bargain/property',o).then(res=>{
			reslove(res)
		})
    })
}
//参与砍价
async function bargainLog(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain/log', o).then(res=>{
			reslove(res)
		})
    })
}
//去砍价
async function bargainBid(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain/bid', o).then(res=>{
			reslove(res)
		})
    })
}
//购买
async function bargainBuy(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain/buy', o).then(res=>{
			reslove(res)
		})
    })
}
//我参与的砍价
async function bargainMyBuy(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/bargain/my_buy', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}

export default {
    bargainIndexs,
    bargainGoods,
    bargainDetail,
    bargainProperty,
    bargainLog,
    bargainBid,
    bargainBuy,
    bargainMyBuy
}
