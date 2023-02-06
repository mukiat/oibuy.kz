import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

function drpRegister(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/register', o).then(res=>{
			reslove(res)
		})
    })
}
// 开店完成
async function drpRegend(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/regend').then(res=>{
			reslove(res)
		})
    })
}
//分销中心
async function drp(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp').then(res=>{
			reslove(res)
		})
    })
}
//我的微店
async function myShop(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/my_shop').then(res=>{
			reslove(res)
		})
    })
}
//分销商品列表
async function drpGoodsList(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/drp/shop_goods',o).then(res=>{
        	reslove({
        		data:res.data,
        		page:o.page,
        		size:o.size
        	})
        })
    })
}
//提现
async function drpTrans(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/trans', o).then(res=>{
			reslove(res)
		})
    })
}
//提现
async function drpTransferred(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/transferred',o).then(res=>{
			reslove(res)
		})
    })
}
//店铺
async function drpShop(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/shop',o).then(res=>{
			reslove(res)
		})
    })
}
//订单
async function drpOrder(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/order',o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
//订单
async function drpOrderDetail(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/order_detail',o).then(res=>{
			reslove(res)
		})
    })
}
//团队
async function drpTeam(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/team',o).then(res=>{
			reslove({
				data:res.data.team_info,
				page:o.page,
				size:o.size
			})
		})
    })
}
//下级团队
async function drpOfflineUser(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/offline_user',o).then(res=>{
			reslove({
				data:res.data.user_list,
				page:o.page,
				size:o.size
			})
		})
    })
}

//团队详情
async function drpTeamDetail(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/teamdetail',o).then(res=>{
			reslove(res)
		})
    })
}

//排行榜
async function drpRank(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/rank_list',o).then(res=>{
			reslove(res)
		})
    })
}
//drpLog
async function drpLog(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/drp_log',o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
//新手必看
async function drpNews(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/news',o).then(res=>{
			reslove(res)
		})
    })
}
//设置
async function drpSet(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/show',o).then(res=>{
			reslove(res)
		})
    })
}

//头像上传
function drpUpdateAvatar(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/avatar',o).then(res=>{
			reslove(res)
		})
    })
}

//设置
async function drpUpdate(o) {
    return new Promise((reslove, reject) => {
		request.put(webUrl + '/api/drp/update',o).then(res=>{
			reslove(res)
		})
    })
}

//代言分类列表
function getDrpCategoryLists(o){
	let url = '';
	if(o.id){
        url = webUrl  + '/api/drp/cartlist?cat_id=' + o.id
    }else{
        url = webUrl + '/api/drp/cartlist'
    }

	return new Promise((reslove, reject) => {
		request.get(url).then(res=>{
			reslove(res)
		})
	})
}

//代言分类添加
function getDrpCategoryAdd(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/addcart',o).then(res=>{
			if(o.f_id){
				reslove({
					data:res.data,
					id:o.id,
					f_id:o.f_id
				})
			}else{
				reslove({
					data:res.data,
					cur_id:o.cur_id,
					type:o.type
				})
			}
		})
	})
}

//代言商品列表
function getDrpList(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/drpgoods',o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
	})
}

//添加代言商品
function getDrpGoodsAdd(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/drp/addgoods',o).then(res=>{
			reslove({
				data:res.data,
				goods_id:o.goods_id
			})
		})
	})
}


//购买成为微店
function getDrpPurchase() {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/purchase').then(res=>{
			reslove(res)
		})
    })
}

//小程序微信支付
function getDrpWxappPay(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/wxapppurchasepay',o).then(res=>{
			reslove(res)
		})
    })
}

//支付方式
function getDrpPay(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/purchasepay',o).then(res=>{
			reslove(res)
		})
    })
}

//切换支付方式
function getDrpChangePayment(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/drp/changepayment',o).then(res=>{
			reslove(res)
		})
    })
}

//分销会员卡
function getDrpChangeCard(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/drp/drpcard`, o).then(res => {
            reslove(res)
        })
    })
}

//分销会员卡详情
function getDrpRightsCard(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/drp/rightscard`,o).then(res => {
            reslove(res)
        })
    })
}

//分销会员卡权益
function getDrpProtection(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/drp/rightscardlist`,o).then(res => {
            reslove(res)
        })
    })
}

//分销会员申请列表
function getDrpApplyList() {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/drp/application`).then(res => {
            reslove(res)
        })
    })
}

//分销会员申请
function getDrpApply(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/drp/apply`, o).then(res => {
            reslove(res)
        })
    })
}

//分销会员续费
function getDrpRenew(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/drp/renew`, o).then(res => {
            reslove(res)
        })
    })
}

//分销管理-自定义设置数据
function getCustomTextByCode(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/drp/custom_text`, o).then(res => {
            reslove(res)
        })
    })
}

export default {
	drp,
	myShop,
    drpRegister,
    drpRegend,
    drpGoodsList,
    drpTrans,
    drpTransferred,
    drpShop,
    drpOrder,
    drpOrderDetail,
    drpTeam,
    drpOfflineUser,
    drpTeamDetail,
    drpRank,
    drpLog,
    drpNews,
    drpSet,
    drpUpdateAvatar,
    drpUpdate,
	getDrpCategoryLists,
	getDrpCategoryAdd,
	getDrpList,
	getDrpGoodsAdd,
    getDrpPurchase,
	getDrpWxappPay,
    getDrpPay,
    getDrpChangePayment,
	getDrpChangeCard,
	getDrpRightsCard,
	getDrpProtection,
	getDrpApplyList,
	getDrpApply,
	getDrpRenew,
	getCustomTextByCode
}
