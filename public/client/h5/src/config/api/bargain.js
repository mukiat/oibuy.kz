import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
    Toast,
    Dialog
} from 'vant'

async function bargainIndexs(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bargain`).then(res => {
            reslove(res.data)
        })
    })
}
async function bargainGoods(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bargain/goods`,{
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
async function bargainDetail(o) {
    return new Promise((reslove, reject) => {
        Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
        axios.get(`${window.ROOT_URL}api/bargain/detail`,{
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        })
    })
}
//砍价属性
async function bargainProperty(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/bargain/property`, qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//参与砍价
async function bargainLog(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bargain/log`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//去砍价
async function bargainBid(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bargain/bid`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//购买
async function bargainBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bargain/buy`, {
            params: o
        }).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}
//我参与的砍价
async function bargainMyBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bargain/my_buy`, {
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
    bargainIndexs,
    bargainGoods,
    bargainDetail,
    bargainProperty,
    bargainLog,
    bargainBid,
    bargainBuy,
    bargainMyBuy
}
