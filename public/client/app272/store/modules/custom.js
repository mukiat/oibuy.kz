import {
    SHOUQIANBA_INVOICE_DETAIL,
    SHOUQIANBA_INVOICE_REAPPLY_INFO
} from '../mutation-custom.js'

import {
    apiCustom
} from '@/config'

const state = {
    invoiceDetail:[],
    invoiceReapplyInfo:[],
    invoiceReapply:[]
}

const mutations = {
    [SHOUQIANBA_INVOICE_DETAIL](state,o){
        state.invoiceDetail = o.data
    },
    [SHOUQIANBA_INVOICE_REAPPLY_INFO](state, o){
        state.invoiceReapplyInfo = o.data
    }
}

const actions = {
    // ��Ʊ����
    setInvoiceDetail({ commit }, o){
        apiCustom.getInvoiceDetail(o).then(res=>{
            commit(SHOUQIANBA_INVOICE_DETAIL,res)
        })
    },
    // ����ҳ��
    setInvoiceReapplyInfo({ commit }, o){
        apiCustom.getInvoiceReapplyInfo(o).then(res=>{
            commit(SHOUQIANBA_INVOICE_REAPPLY_INFO,res)
        })
    },
    // �ύ��������
    setInvoiceReapply({ commit }, o){
        return apiCustom.invoiceReapply(o)
    },
}

export default {
    state,
    mutations,
    actions,
}