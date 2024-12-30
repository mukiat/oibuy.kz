import axios from 'axios'
import qs from 'qs'
import i18n from '@/locales'
import { Loading, MessageBox, Message } from 'element-ui'

let loadingInstance

function getAlbum() {
    return axios.post(`/${window.apiAuthority}/touch_visual/thumb`,
        qs.stringify({
            type: 'thumb',
            ru_id: window.shopInfo.ruid
        })
    )
}

function getPriture(albumId, o, oF) {
    axios
        .post(`/${window.apiAuthority}/touch_visual/thumb`,
            qs.stringify({
                type: 'img',
                currentPage: o.currentPage,
                pageSize: o.pageSize,
                album_id: albumId,
                ru_id: window.shopInfo.ruid
            })
        )
        .then(res => {
            setTimeout(() => {
                res.data.img.forEach(v => {
                    v.bActive = false
                })
                oF.setDialogPicture({
                    data: res.data,
                    oneOrMore: o.oneOrMore,
                    maxLength: o.maxLength,
                    residueLength: o.residueLength
                })
                loadingInstance.close()
            }, 100)
        })
        .then(res => {
            oF.onOffDialogPicture()
        })
        .catch(err => {
            console.error(err)
        })
}

/**
 * 获取picture数据
 * @param {Object} o - 异步请求相关属性
 * @param {Object} oF - 执行 action 数据保存
 */
function getDialog(o, oF) {
    let albumId = 0
    let albumName = ''
    loadingInstance = Loading.service({ target: 'body', text: i18n.t('lang.loading') })
    if (o.albumId) {
        albumId = o.albumId
        getPriture(albumId, o, oF)
    } else {
        getAlbum()
            .then(res => {
                let aThumb = res.data.thumb
                if (aThumb) {
                    if (aThumb.length > 0) {
                        albumId = aThumb[0].album_id
                        albumName = aThumb[0].name
                    }
                    aThumb.map((v, i) => {
                        i == 0 ? (v.bActive = true) : (v.bActive = false)
                    })
                    oF.setDialogPictureThumb({
                        thumb: aThumb,
                        albumId,
                        albumName
                    })
                    return { albumId: albumId }
                }
            })
            .then(res => {
                loadingInstance.close()
                if (res) {
                    getPriture(res.albumId, o, oF)
                } else {
                    MessageBox.confirm(i18n.t('lang.is_add_no_album'), i18n.t('lang.hint'), {
                        confirmButtonText: i18n.t('lang.confirm'),
                        cancelButtonText: i18n.t('lang.cancel'),
                        type: 'warning'
                    })
                        .then(() => {
                            axios
                                .post(`/${window.apiAuthority}/touch_visual/make_gallery`,
                                    qs.stringify({
                                        ru_id: window.shopInfo.ruid
                                    })
                                )
                                .then(({ data: { album_id } }) => {
                                    Message.info(i18n.t('lang.add_success_1'))
                                })
                        })
                        .catch(err => {
                            console.error(err)
                        })
                }
            })
            .catch(err => {
                console.error(err)
            })
    }
}

export default {
    getDialog
}
