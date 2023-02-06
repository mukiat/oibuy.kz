import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

//秒杀首页
function getSeckill(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/seckill', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
	})
}

//秒杀时间段
function getSeckillTime(){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/seckill/time').then(res=>{
			reslove(res)
		})
	})
}

//秒杀详情
function getSeckillDetail(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/seckill/detail', o).then(res=>{
			reslove(res)
		})
	})
}

//秒杀购买
function getSeckillBuy(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/seckill/buy', o).then(res=>{
			reslove(res)
		})
	})
}

//优惠活动首页
function activityIndex(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/activity`).then(res => {
            reslove(res)
        })
    })
}

//优惠活动详情
function activityShow(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/activity/show`,o).then(res => {
            reslove(res)
        })
    })
}

//优惠活动商品
function activityGoods(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/activity/goods`,o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//优惠活动凑单
function activityCoudan(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/activity/coudan`,o).then(res => {
            reslove(res)
        })
    })
}
//红包领取
function getBonus(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/bonus`,o).then(res => {
            reslove({
				data:res.data,
				size:o.size,
				page:o.page
			})
        })
    })
}
function getReceiveBonus(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + `/api/bonus/receive`,o).then(res => {
            reslove(res)
        })
    })
}
//优惠券领取
function getWebCoupon(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/coupon`,o).then(res => {
           reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
		   })
        })
    })
}
function getWebTaskCoupon(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + `/api/coupon/couponsgoods`,o).then(res => {
           reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
		   })
        })
    })
}
//预售
function presale(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/presale`).then(res => {
	        reslove(res)
	    })
	})
}
//预售列表
function presaleList(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/presale/list`,o).then(res => {
	        reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
	        })
	    })
	})
}
//预售新品
function presaleNew(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/presale/new`,o).then(res => {
	        reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
	        })
	    })
	})
}
//预售详情
function presaleDetail(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/presale/detail`,o).then(res => {
	        reslove(res)
	    })
	})
}
//预售属性
function  presalePropert(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/presale/price`,o).then(res => {
	        reslove(res)
	    })
	})
}
//预售购买
function presaleBuy(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/presale/buy`,o).then(res => {
	        reslove(res)
	    })
	})
}

//超级礼包
function packageList(o) {
	return new Promise((reslove, reject) => {
	    request.get(webUrl + `/api/package/list`,o).then(res => {
			reslove({
			   data:res,
			   size:o.size,
			   page:o.page
			})
	    })
	})
}

//积分模块
function exchangeIndex(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/exchange`,o).then(res => {
		    reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
		    })
		})
    })
}
//积分详情
function exchangeDetail(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/exchange/detail`,o).then(res => {
		    reslove(res)
		})
    })
}

//积分提交
function getExchangeBuy(o){
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/exchange/buy`,o).then(res => {
		    reslove(res)
		})
    })
}

//团购模块
function groupbuyIndex(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/group_buy`,o).then(res => {
		    reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
		    })
		})
    })
}
//团购详情
function groupbuyDetail(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/group_buy/detail`,o).then(res => {
		    reslove(res)
		})
    })
}

//团购购买
function groupBuy(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/group_buy/buy`,o).then(res => {
		    reslove(res)
		})
    })
}

//拍卖首页
function auctionIndex(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/auction`,o).then(res => {
		    reslove({
			   data:res.data,
			   size:o.size,
			   page:o.page
		    })
		})
    })
}
//拍卖详情
function auctionGoods(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/auction/detail`,o).then(res => {
		    reslove(res)
		})
    })
}
//拍卖记录
function auctionLog(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/auction/log`,o).then(res => {
		    reslove(res)
		})
    })
}
//拍卖bid
function auctionBid(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/auction/bid`,o).then(res => {
		    reslove(res)
		})
    })
}
//拍卖购买
function auctionBuy(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + `/api/auction/buy`,o).then(res => {
		    reslove(res)
		})
    })
}

export default {
	getSeckill,
	getSeckillTime,
	getSeckillDetail,
	getSeckillBuy,
	activityIndex,
	activityShow,
	activityGoods,
	activityCoudan,
	getBonus,
	getReceiveBonus,
	getWebCoupon,
	getWebTaskCoupon,
	presale,
	presaleList,
	presaleNew,
	presaleDetail,
	presalePropert,
	presaleBuy,
	packageList,
	exchangeIndex,
	exchangeDetail,
	getExchangeBuy,
	groupbuyIndex,
	groupbuyDetail,
	groupBuy,
	auctionIndex,
	auctionGoods,
	auctionLog,
	auctionBid,
	auctionBuy
}
