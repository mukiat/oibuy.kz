import qs from 'qs'
import axios from 'axios'

async function activityIndex(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/activity`).then(res => {
            reslove(res.data)
        })
    })
}
async function activityShow(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/activity/show`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
async function activityGoods(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/activity/goods`,qs.stringify(o)).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
async function activityCoudan(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/activity/coudan`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
export default {
    activityIndex,
    activityShow,
    activityGoods,
    activityCoudan
}
