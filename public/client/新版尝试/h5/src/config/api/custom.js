import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
    Toast,
    Dialog
} from 'vant'

import {
    Loading
} from 'element-ui';


// 查看发票详情
function getInvoiceDetail(o) {
    Toast.loading({duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading')})
    return new Promise((reslove, reject) => {
        axios.get(`/api/shouqianba/invoice/detail`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        })
    })
}

// 换开申请发票详情
function getInvoiceReapplyInfo(o) {
    Toast.loading({duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading')})
    return new Promise((reslove, reject) => {
        axios.get(`/api/shouqianba/invoice/reapplyInfo`, {
            params: o
        }).then(res => {
            reslove(res.data)
            Toast.clear()
        })
    })
}

/* 换开发票提交 */
function invoiceReapply(o) {
    return new Promise((reslove, reject) => {
        axios.post(`/api/shouqianba/invoice/reapply`, qs.stringify(o)).then(res => {
            if (res.data.status == 'success') {
                Toast.success({
                    duration: 1000,
                    forbidClick: true,
                    loadingType: 'spinner',
                    message: i18n.t('lang.submit_success') 
                })
                reslove(res.data)

            } else {
                Toast(i18n.t('lang.submit_fail'));
            }
        }).catch(err => {
            console.error(err)
        })
    })
}

export default {
    getInvoiceDetail,
    getInvoiceReapplyInfo,
    invoiceReapply,
}
