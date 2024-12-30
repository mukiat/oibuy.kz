import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
    Toast
}from 'vant'

async function teamIndexs(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team`).then(res => {
            reslove(res.data)
        })
    })
}
async function teamGoods(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/goods`, {
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
//拼团属性
async function teamProperty(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/property`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//拼团加入购物车
async function teamBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/team_buy`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
async function teamCategories(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/categories`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
async function teamDetail(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/detail`, {
            params: o
        }).then(({data:{data}}) => {
            reslove({
                data:data
            })
        }).catch(err => {
            console.error(err)
        })
    })
}
async function teamList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/goods_list`, {
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
async function teamRank(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/team_ranking`, {
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
//等待成团
async function teamWait(o) {
    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/team_wait`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        }).catch(err => {
            console.error(err)
        })
    })
}
//拼团成员
async function teamUser(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/team_user`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//我的拼团
async function teamOrder(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/team/team_order`, {
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
