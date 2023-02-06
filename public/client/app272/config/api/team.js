import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

function teamIndexs(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team', o).then(res=>{
			reslove(res)
		})
    })
}
function teamGoods(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/goods', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
//拼团属性
function teamProperty(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/property',o).then(res=>{
			reslove(res)
		})
    })
}
//拼团加入购物车
function teamBuy(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/team_buy',o).then(res=>{
			reslove(res)
		})
    })
}
function teamCategories(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/categories',o).then(res=>{
			reslove(res)
		})
    })
}
function teamDetail(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/detail',o).then(res=>{
			reslove(res)
		})
    })
}
function teamList(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/goods_list',o).then(res=>{
			reslove(res)
		})
    })
}
function teamRank(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/team_ranking',o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
//等待成团
function teamWait(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/team_wait',o).then(res=>{
			reslove(res)
		})
    })
}
//拼团成员
function teamUser(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/team_user',o).then(res=>{
			reslove(res)
		})
    })
}
//我的拼团
function teamOrder(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/team/team_order',o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
export default {
    teamIndexs,
    teamGoods,
    teamProperty,
    teamBuy,
    teamCategories,
    teamDetail,
    teamList,
    teamRank,
    teamOrder,
    teamWait,
    teamUser
}
