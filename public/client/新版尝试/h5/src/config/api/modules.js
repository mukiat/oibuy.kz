import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
    Loading,
    MessageBox,
    Message
} from 'element-ui';

import {
    Toast,
    Dialog
} from 'vant'

async function getModules(o, oF) {
    let loadingInstance = Loading.service({ fullscreen: true, text: i18n.t('lang.loading') })
    axios.post(`${window.ROOT_URL}api/visual/view`, qs.stringify(o)).then(res => {
        let view = res.data
        if (!view.data) {
            loadingInstance.close();
            Toast(i18n.t('lang.abnormal_data'))
            return
        }

        setTimeout(() => {
            oF.getModule({
                moduleData: view.data.data,
                moduleTitle: view.data.title,
                id: o.id,
                title: o.title,
                type: o.type,
                default: view.data.default
            })
            loadingInstance.close()
        }, 100)
    }).catch(err => {
        console.error(err)
    })
}

function saveModules(o) {
    let thisO = {}
    if (o.title && o.title) {
        Object.assign(thisO, {
            title: o.title,
            ru_id: o.ruid,
        })
    }
    if (o.modulesId && o.modules) {
        Object.assign(thisO, {
            id: o.modulesId,
            data: o.modules,
        })
    }
    return axios.post(`/${window.apiAuthority}/touch_visual/save`, qs.stringify(thisO))
}

function tipsSaveModules(o) {
    return new Promise((resolve, reject) => {
        MessageBox.confirm(i18n.t('lang.is_save_tips_current_mod'), i18n.t('lang.hint'), {
            confirmButtonText: i18n.t('lang.save'),
            cancelButtonText: i18n.t('lang.discard'),
            type: 'warning',
        }).then(() => {
            saveModules(o).then(({ data: { error } }) => {
                error == 1 ? reject(false) : resolve(true)
            })
        }).catch(err => {
            reject("messageBox catch")
        })
    })
}

function removeModules(o) {
    return new Promise((resolve, reject) => {
        let msg = i18n.t('lang.delete') + o.title + i18n.t('lang.is_page_cannot_be_restored');

        MessageBox.confirm(msg, i18n.t('lang.hint'), {
            confirmButtonText: i18n.t('lang.confirm'),
            cancelButtonText: i18n.t('lang.cancel'),
            type: 'warning',
        }).then((action) => {
            axios.post(`/${window.apiAuthority}/touch_visual/del`, qs.stringify({
                id: o.id
            })).then(({ data: { error } }) => {
                error == 1 ? reject(false) : resolve(true)
            })
        }).catch((err) => {
            reject("messageBox catch")
        })
    })
}

function updateModulesTitle(o) {
    return axios.post(`/${window.apiAuthority}/touch_visual/title`, qs.stringify({
        id: o.id,
        title: o.title
    }))
}

/* 地区 */
function getRegion(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/misc/region`,{ params:o }).then(res =>{
            if(res.data.length > 0){
                reslove({
                    id:o.region,
                    level:o.level,
                    data:res.data,
                    status:false
                })
            }else{
                reslove({
                    status:true
                })
            }
        }).catch(err => {
        reject(err)
      })
  })
}

/* 图片验证码 */
function getImgVerify(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/misc/captcha`).then(res=>{
            if(res.data.status == 'success'){
                reslove(res.data)
            }else{
                reject(res.data)
            }
        }).catch(err => {
        reject(err)
      })
    })
}

/* 发送短信验证码 */
function getSendVerify(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/misc/sms/send`, qs.stringify(o)).then(res=>{
            if(res.data.status == 'success'){
                if(res.data.data.result.code){
                    alert(res.data.data.result.code)
                }
                reslove(res.data.data.status)
            }else{
                Toast(res.data.errors.message);
            }
        }).catch(err => {
        reject(err)
      })
    })
}

/* 验证填写的短信验证码是否正确 */
function getSmsVerify(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/misc/sms/verify`, qs.stringify(o)).then(res=>{
            reslove(res.data)
        }).catch(err => {
            reject(err)
        })
    })
}

/* 获取位置信息 */
function getPosition(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/misc/position`,{ params:o }).then(res=>{
            reslove(res.data)
        }).catch(err => {
            reject(err)
        })
    })
}

export default {
    getModules,
    saveModules,
    tipsSaveModules,
    removeModules,
    updateModulesTitle,
    getRegion,
    getImgVerify,
    getSendVerify,
    getSmsVerify,
    getPosition
}
