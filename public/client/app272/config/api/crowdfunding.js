import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

//众筹首页
async function getCrowdfunding(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding',o).then(res => {
            reslove(res.data)
        })
    })
}

//众筹列表
async function getCrowdfundingGoods(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/goods',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//众筹详情
async function getCrowdfundingShow(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/show',o).then(res => {
            reslove(res.data)
        })
    })
}

//众筹属性
async function getCrowdfundingProperty(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/property',o).then(res => {
            reslove(res.data)
        })
    })
}
//众筹提交
async function getCrowdfundingCheckout(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/checkout',o).then(res => {
            reslove(res.data)
        })
    })
}
//众筹完成
async function getCrowdfundingDone(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/done',o).then(res => {
            reslove(res.data)
        })
    })
}
//众筹订单
async function getCrowdfundingOrder(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/order',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//众筹订单详情
async function getCrowdfundingDetail(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/detail',o).then(res => {
            reslove(res)
        })
    })
}
//众筹中心
async function getCrowdfundingUser(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/user',o).then(res => {
            reslove(res.data)
        })
    })
}
//微筹中心-我的支持
async function getCrowdfundingBuy(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/crowd_buy',o).then(res => {
            reslove(res.data)
        })
    })
}
//微筹中心-添加关注
async function getCrowdfundingFocus(o) {
    return new Promise((reslove, reject) => {
        if(o.status == 1){

			uni.showModal({
				title:'',
				content:'您确定要取消收藏此商品吗？',
				success: (res) => {
					if(res.confirm){
						uni.request({
							url: webUrl + '/api/crowd_funding/focus',
							method: 'get',
							data: o,
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								if(res.data.status == 'success'){
									reslove(res.data)
								}
							}
						})
					}
				}
			})
        }else{
            request.get(webUrl + '/api/crowd_funding/focus',o).then(res => {
				uni.showToast({
					title:'关注成功',
					icon:'none'
				});
                reslove({
                    data:res.data,
                    id:o.goods_id,
                    status:1
                })
            })
        }
    })
}
//微筹中心-我的关注
async function getCrowdfundingMyFocus(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/my_focus',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//话题表单
function getCrowdfundingTopic(o) {
    return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/crowd_funding/topic',o).then(res => {
            reslove(res.data)
        })
    })
}
//话题列表
async function getCrowdfundingMyFocusList(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/topic_list',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//详情-风险-主页
async function getCrowdfundingProperties(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/properties',o).then(res => {
            reslove(res.data)
        })
    })
}
async function getCrowdfundingBest(o) {
    return new Promise((reslove, reject) => {
        request.get(webUrl + '/api/crowd_funding/crowd_best',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
export default{
    getCrowdfunding,
    getCrowdfundingGoods,
    getCrowdfundingProperty,
    getCrowdfundingShow,
    getCrowdfundingCheckout,
    getCrowdfundingDone,
    getCrowdfundingOrder,
    getCrowdfundingDetail,
    getCrowdfundingUser,
    getCrowdfundingBuy,
    getCrowdfundingFocus,
    getCrowdfundingMyFocus,
    getCrowdfundingTopic,
    getCrowdfundingMyFocusList,
    getCrowdfundingProperties,
    getCrowdfundingBest
}
