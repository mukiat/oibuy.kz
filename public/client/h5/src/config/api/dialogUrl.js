import axios from 'axios'
import Vue from 'vue'
import qs from 'qs'
import i18n from '@/locales'
import {
    Loading
} from 'element-ui'


/**
* 将数据进行格式化实现table tree 效果
* @param {*} formatData 
*/
let son = []
function formatDialogUrl(url, id = 0, lev = 0) {
    for (let item of url) {
        let isTrue = true
        let cat_id = item.cat_id
        if (item.parent_id == id) {
            Vue.set(item, "lev", lev)
            son.push(item)
            formatDialogUrl(url, item.cat_id, lev + 1)
        }
    }
    return son
}

/**
 * 获取url数据
 * @param {Object} o - 异步请求相关属性
 * @param {Object} oF - 执行 action 数据保存 /admin/touch_visual/index
 */
function getDialog(o, oF) {
    if (o.type != "custom") {
        let loadingInstance = Loading.service({ target: "body", text: i18n.t('lang.loading') });
        axios.post(`/${window.apiAuthority}/touch_visual/geturl`, qs.stringify({
            type: o.type,
            ru_id:window.shopInfo.ruid,
            currentPage: o.currentPage,
            pageSize: o.pageSize,
        })).then(res => {
            setTimeout(() => {
                let formatData = formatDialogUrl(res.data.url)
                res.data.url = formatData
                oF.setDialogUrl(res.data)
                son = []
                loadingInstance.close()
            }, 100)
        }).then(res => {
            oF.onOffDialogUrl()
        }).catch(err => {
            console.error(err)
        })
    } else {
         oF.onOffDialogUrl()
    }

}

export default {
    getDialog
}