import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
    Toast
}from 'vant'

function drpRegister(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/register`, qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
// 开店完成
async function drpRegend(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/regend`).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//分销中心
async function drp(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp`).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//我的微店
async function myShop(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/my_shop`).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//分销商品列表
async function drpGoodsList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/shop_goods`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        }).catch(err => {
            console.error(err)
        })
    })
}
//提现
async function drpTrans(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/trans`).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//提现
async function drpTransferred(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/transferred`, qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
//店铺
async function drpShop(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/shop`, { params: o }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//订单
async function drpOrder(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/order`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        }).catch(err => {
            console.error(err)
        })
    })
}
//订单
async function drpOrderDetail(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/order_detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//团队
async function drpTeam(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/team`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//下级团队
async function drpOfflineUser(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/offline_user`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//团队详情
async function drpTeamDetail(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/teamdetail`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//排行榜
async function drpRank(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/rank_list`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//drpLog
async function drpLog(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/drp_log`, {
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        }).catch(err => {
            console.error(err)
        })
    })
}
//新手必看
async function drpNews(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/news`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//设置
async function drpSet(o) {
    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/show`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        }).catch(err => {
            console.error(err)
        })
    })
}

//头像上传
function drpUpdateAvatar(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/avatar`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//设置
async function drpUpdate(o) {
    return new Promise((reslove, reject) => {
        axios.put(`${window.ROOT_URL}api/drp/update`, qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//代言分类列表
function getDrpCategoryLists(o,oF){
    let url = '';

    if(o.id){
        url = `${window.ROOT_URL}api/drp/cartlist?cat_id=` + o.id
    }else{
        url = `${window.ROOT_URL}api/drp/cartlist`
    }
    axios.get(url).then(res => {
        if(res.data.status ==='success'){
            oF.getCategoryList({
                data : res.data.data,
                index : o.index,
                id:o.id
            })
        }
    }).catch(err => {
        console.log(err);
    })
}

//代言分类添加
function getDrpCategoryAdd(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/addcart`,{params: o}).then(res => {
            if(o.f_id){
                reslove({
                    data:res.data.data,
                    id:o.id,
                    f_id:o.f_id
                })
            }else{
                reslove({
                    data:res.data.data,
                    cur_id:o.cur_id,
                    type:o.type
                })
            }
        }).catch(err => {
            console.error(err)
        })
    })
}

//代言商品列表
function getDrpList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/drpgoods`,{params:o}).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//添加代言商品
function getDrpGoodsAdd(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/addgoods`,{params:o}).then(res => {
            reslove({
                data:res.data.data,
                goods_id:o.goods_id
            })
        }).catch(err => {
            console.error(err)
        })
    })
}

//购买成为微店
function getDrpPurchase() {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/purchase`).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//支付方式
function getDrpPay(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/purchasepay`, qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//切换支付方式
function getDrpChangePayment(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/changepayment`, qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//切换支付方式
function getDrpChangeCard(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/drp/drpcard`, qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//分销会员卡详情
function getDrpRightsCard(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/rightscard`,{params:o}).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//分销会员卡权益
function getDrpProtection(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/drp/rightscardlist`,{params:o}).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}


export default {
    drpRegister,
    drpRegend,
    drp,
    myShop,
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
    getDrpPay,
    getDrpChangePayment,
    getDrpChangeCard,
    getDrpRightsCard,
    getDrpProtection
}
