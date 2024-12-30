import qs from 'qs'
import axios from 'axios'

import {
    Toast
}from 'vant'

async function auctionIndex(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/auction`,{
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
async function auctionGoods(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/auction/detail`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
async function auctionLog(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/auction/log`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
async function auctionBid(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/auction/bid`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
async function auctionBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/auction/buy`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
export default {
    auctionIndex,
    auctionGoods,
    auctionLog,
    auctionBid,
    auctionBuy
}
